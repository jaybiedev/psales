<?
include_once('../lib/library.php');
include_once('../lib/dbconfig.php');
include_once('../lib/connect.php');
include_once('../var/system.conf.php');
include_once('../lib/library.js.php');

$business_name = lookUpTableReturnValue('x','sysconfig','sysconfig','value','BUSINESS_NAME');


$from_date	= mdy2ymd($_REQUEST['from_date']);
$to_date	= mdy2ymd($_REQUEST['to_date']);
$account_id	= $_REQUEST['account_id'];

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>STOCKS RECEIVING DETAIL</title>
<style type="text/css">
	*{ font-family:Arial, Helvetica, sans-serif; font-size:11px;}
	table{
		border-collapse:collapse;
	}
	table td{
		padding:3px;	
	}
	table tr:first-child td,.summary td{
		border-top:1px solid #000;
		border-bottom:1px solid #000;	
		font-weight:bold;
	}
	table td:nth-child(n+7){
		text-align:right;	
	}
	.header{
		font-weight:bold;
	}
</style>
<script type="text/javascript">
function printPage() { print(); } //Must be present for Iframe printing
</script>
</head>
<body>
	<div style="font-weight:bold;">
    	<?
		echo "
			$business_name <br>
			SRR HISTORY DETAIL <br>
			".date("m/d/Y",strtotime($from_date))." - ".date("m/d/Y",strtotime($to_date))."
		";
        ?>
    </div>
	<table style="width:100%;">
    	<tr>
        	<td>DATE</td>
            <td>SRR#</td>
            <td>INVOICE</td>
            <td>PO#</td>
            <td>SUPPLIER</td>
       		<td>ITEM</td>     
            <td>CASE QTY</td>
            <td>CASE PRICE</td>
            <td>UNIT QTY</td>
            <td>UNIT PRICE</td>
            
            <td>AMOUNT</td>
       	</tr>
            
		<?
        $q = "
            select 
                date, h.rr_header_id, invoice, po_header_id, h.account_id, terms, stock, d.case_qty, d.unit_qty, d.cost1, d.cost3, amount
            from 
                rr_header as h, rr_detail as d, stock as p
            where 
				h.rr_header_id = d.rr_header_id
			and	d.stock_id = p.stock_id
		";
        if (!empty($from_date) && !empty($to_date)){
            $q .= " and date between '$from_date' and '$to_date'";
        }
		
		if (!empty($account_id)){
            $q .= " and h.account_id = '$account_id'";
        }

        $q .= " order by date asc";
		
        $result = pg_query($q) or message(pg_errormessage());
		
		$t_case_qty = $t_cost1 = $t_unit_qty = $t_cost3 = $t_amount = 0;
		
        while( $r = pg_fetch_assoc($result) ){
			
			$t_case_qty += $r['case_qty'];
			$t_cost1 	+= $r['cost1'];
			$t_unit_qty += $r['unit_qty'];
			$t_cost3 	+= $r['cost3'];
			$t_amount 	+= $r['amount'];
				
            echo "
				<tr>
					<td>".ymd2mdy($r['date'])."</td>
					<td>".str_pad($r['rr_header_id'],7,0,STR_PAD_LEFT)."</td>
					<td>".$r['invoice']."</td>
					<td>".str_pad($r['po_header_id'],7,0,STR_PAD_LEFT)."</td>
					<td>".lookUpTableReturnValue('x','account','account_id','account',$r['account_id'])."</td>
					
					<td>$r[stock]</td>
					
					<td>".number_format($r['case_qty'],2)."</td>
					<td>".number_format($r['cost1'],2)."</td>
					<td>".number_format($r['unit_qty'],2)."</td>
					<td>".number_format($r['cost3'],2)."</td>
					<td>".number_format($r['amount'],2)."</td>
				</tr>
			";
        }
		echo "
			<tr class='summary'>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				
				<td>".number_format($t_case_qty,2)."</td>
				<td>".number_format($t_cost1,2)."</td>
				<td>".number_format($t_unit_qty,2)."</td>
				<td>".number_format($t_cost3,2)."</td>
				<td>".number_format($t_amount,2)."</td>
			</tr>
		";
		
        ?>
  	</table>
</body>
</html>