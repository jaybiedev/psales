<?
include_once('../lib/library.php');
include_once('../lib/dbconfig.php');
include_once('../lib/connect.php');
include_once('../var/system.conf.php');
include_once('../lib/library.js.php');
include_once('stockbalance.php');
require_once(dirname(__FILE__).'/../lib/lib.salvio.php');
require_once(dirname(__FILE__).'/../lib/lib.inventory.php');

$stock_id	= $_REQUEST['stock_id'];
$to_date 	= mdy2ymd($_REQUEST['to_date']);
$from_date 	= mdy2ymd($_REQUEST['from_date']);

$result = @pg_query("select * from stock where stock_id = '$stock_id' ");
$bin = @pg_fetch_assoc($result);

$fraction3 = $bin['fraction3'];
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>JOB ORDER</title>
<script>
function printPage() { print(); } //Must be present for Iframe printing
</script>
<style type="text/css">
	*{
		font-family:Arial, Helvetica, sans-serif;
		font-size:12px;	
	}
	table th, table td{
		padding:0px 10px;
	}
	table th{
		border-top:1px solid #000;
		border-bottom:1px solid #000;
	}	
</style>	
</head>
<body>
<div class="container">
	
     <div><!--Start of Form-->
     	
     	<div style="font-weight:bolder; text-align:center;">
        	<?=$SYSCONF['BUSINESS_NAME']?><br />
        	STOCK CARD <br />
            As of <?=date("m/d/Y",strtotime($from_date))?> to <?=date("m/d/Y",strtotime($to_date))?>
        </div>


		<div>
            <table>
            	<tr>
                	<td>Product: </td>
                    <td colspan="7">[ <?=$bin['stock_id']?> ] <?=$bin['stock']?></td>
                </tr>
                <tr>
                	<td>Item Code:</td>
                    <td><?=$bin['barcode']?></td>
                    <td>U/C:</td>
                    <td><?=$bin['fraction3']?>'s</td>
                    <td>Last Cost:</td>
                    <td><?=number_format($bin['cost1'],2,'.',',')?></td>
                    <td>SRP:</td>
                    <td><?=number_format($bin['price1'],2,'.',',')?></td>
                </tr>
            </table>
        </div>
        <div class="content" >
        	<table cellspacing="0" cellpadding="3" style="width:100%;">
            	<tr>
                	<th>Date</th>
                  <th>Reference</th>
                  <th>Type</th>
                  <th>Particulars</th>
                  <th>IN</th>
                  <th>OUT</th>
                  <th>BALANCE</th>
                </tr>
                
                <? 
				$data         = array();				
				$balance_date = $from_date;

				/* Get Balance Forwarded Only if minus date is  minus 1 day is December 31*/	
				$balance = Inventory::getCurrentBalance($stock_id,lib::minusOneDay($from_date));					
				
				
				
				/*$balance      = $bal['balance_qty']; #beg_qty*/
				
				?>
                <tr>
                	<td colspan="6">Beginning Balance</td>
                    <td style="text-align:right;"><?=number_format($balance,2,'.',',')?></td>
                </tr>
                
                <?
				#RECEIVING
				
				$q = "select 
						h.date,
						h.rr_header_id,
						h.account_id,
						d.case_qty,
						d.unit_qty
					from 
						rr_header as h,
						rr_detail as d
					where
						h.rr_header_id=d.rr_header_id 
					and
						d.stock_id='$stock_id' 
					and
						h.status!='C'
				";
				if ($from_date != '')
				{
					$q .= " and date >= '$from_date'";
				}
				if ($to_date != '')
				{
					$q .= " and date <= '$to_date'";
				}
			
				$result=@pg_query($q) or die(pg_result_error());
				while($r=@pg_fetch_assoc($result)):
					
					$data[]= array(
						'date' => $r['date'],
						'type' => 'RR',
						'reference' => str_pad($r['rr_header_id'],8,0,STR_PAD_LEFT),
						'particulars' => lookUpTableReturnValue('x','account','account_id','account',$r['account_id']),
						'qtyin' => ($r['case_qty'] * $fraction3) + $r['unit_qty'],
						'qtyout' => 0
					);
				?>
                <?php
				endwhile;
				
				?>
                
                <?
				#SALES
				$tables = currTables($from_date);
				$sales_header = $tables['sales_header'];
				$sales_detail = $tables['sales_detail'];


				$q = "select 
							quantity,
							date,
							'SALES' as particulars,
							date as reference
					from 
							sales
					where
						stock_id='$stock_id'
				";
						
				if ($from_date != '') {
					  $q .= " and date >= '$from_date'";
				}

				if ($to_date != '') {
					  $q .= " and date <= '$to_date'";
				}

				
				
				$result=@pg_query($q);
				while($r=@pg_fetch_assoc($result)):					
					
					$data[]= array(
						'date' => date("Y-m-d",strtotime($r['date'])),
						'type' => 'Sales',
						'reference' => lib::ymd2mdy($r['date']),
						'particulars' => $particulars,
						'qtyin' => 0,
						'qtyout' => $r['quantity']
					);
				?>
                <?php
				endwhile;
				?>
             
                
                <?php
				/*****************
				PURCHASE RETURNS
				*****************/
				
				$q = "
					select 
						case_qty,
						unit_qty,
						por_header.por_header_id,
						por_header.date,
						account_id
					from 
						por_header,
						por_detail
					where
						por_header.por_header_id=por_detail.por_header_id and
						por_detail.stock_id='$stock_id' and
						not (por_header.status in ('C','V'))
				";
				if ($from_date != '')
				{
					$q .= " and date >= '$from_date'";
				}
				if ($to_date != '')
				{
				  $q .= " and date<= '$to_date'";
				}
				
				$result=@pg_query($q);
				while($r=@pg_fetch_assoc($result)):
					
					$data[]= array(
						'date' => $r['date'],
						'type' => 'POR',
						'reference' => str_pad($r['por_header_id'],8,0,STR_PAD_LEFT),
						'particulars' => lookUpTableReturnValue('x','account','account_id','account',$r['account_id']),
						'qtyin' => 0,
						'qtyout' => ($r['case_qty'] * $fraction3) + $r['unit_qty']
					);
					
				?>
                <?php
				endwhile;
				?>
                
                <?php
				/*****************
				Stock Transfers
				*****************/
				
				$q = "
					select 
						case_qty,
						unit_qty,
						stocktransfer_header.stocktransfer_header_id,
						stocktransfer_header.date,
						branch_id_to
					from 
						stocktransfer_header,
						stocktransfer_detail
					where
						stocktransfer_header.stocktransfer_header_id=stocktransfer_detail.stocktransfer_header_id and
						stocktransfer_detail.stock_id='$stock_id' and
						not (stocktransfer_header.status in ('C','V'))
				";
				if ($from_date != '')
				{
					$q .= " and date >= '$from_date'";
				}
				if ($to_date != '')
				{
				  $q .= " and date<= '$to_date'";
				}
				
				$result=@pg_query($q);
				while($r=@pg_fetch_assoc($result)):
					
					$data[]= array(
						'date' => $r['date'],
						'type' => 'TRANSFER',
						'reference' => str_pad($r['stocktransfer_header_id'],8,0,STR_PAD_LEFT),
						'particulars' => lookUpTableReturnValue('x','branch','branch_id','branch',$r['branch_id_to']),
						'qtyin' => 0,
						'qtyout' => ($r['case_qty'] * $fraction3) + $r['unit_qty']
					);
					
				?>
                <?php
				endwhile;
				?>
                
                <?
				#INVENTORY ADJUSTMENTS
				
				$q = "
					select 	
						case_qty,
						unit_qty,
						invadjust_header.invadjust_header_id,
						date,
						remark
					from 
						invadjust_header,
						invadjust_detail
					where
						invadjust_header.invadjust_header_id=invadjust_detail.invadjust_header_id and
						invadjust_detail.stock_id='$stock_id' and
						invadjust_header.branch_id='1' and 
						invadjust_header.status!='C'";
				if ($from_date != ''){
				  $q .= " and date >= '$from_date'";
				}
				if ($to_date != '') {
				  $q .= " and date <= '$to_date'";
				}
				?>

                <?php
				$result=@pg_query($q);
				while($r=@pg_fetch_assoc($result)):
					$case_qty 	= $r['case_qty'];
					$unit_qty	= $r['unit_qty'];
					
					$qty  =  ( $case_qty * $fraction3 ) + $unit_qty;
					
					if($qty >= 0){
						$data[]= array(
							'date' => $r['date'],
							'type' => 'ADJ',
							'reference' => str_pad($r['invadjust_header_id'],8,0,STR_PAD_LEFT),
							'particulars' => $r['remark'],
							'qtyin' => $qty,
							'qtyout' => 0
						);
					}else{
						$data[]= array(
							'date' => $r['date'],
							'type' => 'ADJ',
							'reference' => str_pad($r['invadjust_header_id'],8,0,STR_PAD_LEFT),
							'particulars' => $r['remark'],
							'qtyin' => 0,
							'qtyout' => abs($qty)
						);
					}
				?>
                <?php
				endwhile;
				?>
                
				<?	
				$date=array();
				if($data):
					foreach ($data as $key => $row) {
						$date[]  = $row['date'];
					}
					array_multisort($date, SORT_ASC, $data);
					
					foreach ($data as $key => $row):
					$balance+=$row['qtyin'];
					$balance-=$row['qtyout'];
					?>
						<tr>
							<td><?=date("m/d/Y",strtotime($row['date']))?></td>
                            <td><?=$row['reference']?></td>
							<td><?=$row['type']?></td>
                            <td><?=$row['particulars']?></td>
							<td style="text-align:right;"><?=number_format($row['qtyin'],2,'.',',')?></td>
							<td style="text-align:right;"><?=number_format($row['qtyout'],2,'.',',')?></td>
							<td style="text-align:right;"><?=number_format($balance,2,'.',',')?></td>
						</tr>
					<?php
					endforeach;
				endif;
				
				?>
                    
            </table>            
        </div><!--End of content-->
    </div><!--End of Form-->

</div>
</body>
</html>