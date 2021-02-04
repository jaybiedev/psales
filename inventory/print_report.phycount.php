<?
require_once(dirname(__FILE__).'/../lib/lib.salvio.php');
?>

<style type="text/css">
	*{ font-family:Arial, Helvetica, sans-serif; font-size:11px;}
	table{
		border-collapse:collapse;
	}
		
	table th{
		border-top:1px solid #000;
		border-bottom:1px solid #000;	
	}
</style>
<script type="text/javascript">
	function printPage() { print(); } //Must be present for Iframe printing
</script>
<?
include_once('../lib/library.php');
include_once('../lib/dbconfig.php');
include_once('../lib/connect.php');
include_once('../var/system.conf.php');
include_once('../lib/library.js.php');
include_once('stockbalance.php');

$arg_list = array("account_id","category_id","from_date","to_date","sort","show","condition","mvalue","combineqty");

/*foreach($arg_list as $arg){
	$$arg = $_REQUEST[$arg];
	echo $$arg." <br>";
}
*/
if ($from_date == '')
{
	$from_date = date('m/d/Y');
	$to_date = date('m/d/Y');
}
?>
<?
if ($p1=='Go' || $p1=='Print Draft' || 1)
{
	@include_once('stockbalance.php');
	$mfrom_date   = mdy2ymd($_REQUEST['from_date']);
	$mto_date     = mdy2ymd($_REQUEST['to_date']);
	
	$tables       = currTables($mfrom_date);
	$sales_header = $tables['sales_header'];
	$sales_detail = $tables['sales_detail'];
	$sales_tender = $tables['sales_tender'];
	$stockledger  = $tables['stockledger'];

	//$stockledger = "e2013.stockledger";

	$q = "select 
			sum(sl.case_qty) as case_qty,
			sum(sl.unit_qty) as unit_qty,
			sl.cost3,
			stock.stock,
			stock.fraction3,
			stock.barcode,
			stock.stock_id,
			stock.account_id,
			stock.category_id
		from 
			stock,
			$stockledger as sl,
			category as c
		where 
			sl.stock_id = stock.stock_id and 
			stock.category_id = c.category_id and
			stock.enable='Y' and
			sl.date>='$mfrom_date' and
			sl.date<='$mto_date'";
	
	if ( !empty($from_category_id) )
	{
		$from_category_code = lib::getAttribute('category','category_id',$from_category_id,'category_code');
		$from_len           = strlen($from_category_code);
		$q                  .= " and substr(c.category_code,1,$from_len) >= '$from_category_code'";
	}		
	if ( !empty($to_category_id) )
	{
		$to_category_code = lib::getAttribute('category','category_id',$to_category_id,'category_code');
		$to_len           = strlen($to_category_code);		
		$q                .= " and substr(c.category_code,1,$to_len) <= '$to_category_code'";
	}	
	
	if ($show == 'nouc')
	{
		if ($mvalue == '') 
		{
			message("No Value Specified...");
			$mvalue =1;
			$q .= " and stock.fraction3 ".$condition." '$mvalue'";
		}
		else
		{
			
			$q .= " and stock.fraction3 ".$condition." '$mvalue'";
		}
	}
	elseif ($show == 'spcost')
	{
		if ($mvalue == '') 
		{
			message("No Value Specified...");
		}
		else
		{
			$q .= " and sl.cost3 ".$condition." '$mvalue'";
		}
	}
	elseif ($show == 'spcaseqty')
	{
		if ($mvalue == '') 
		{
			message("No Value Specified...");
		}
		else
		{
			$q .= " and case_qty ".$condition." '$mvalue'";
		}
	}
	elseif ($show == 'spunitqty')
	{
		if ($mvalue == '') 
		{
			message("No Value Specified...");
		}
		else
		{
			$q .= " and unit_qty ".$condition." '$mvalue'";
		}
	}
	$q .= " group by 					
					sl.cost3,
					stock.stock,
					stock.fraction3,
					stock.barcode,
					stock.stock_id,
					stock.account_id,
					stock.category_id ";

	$q .= " order by account_id, category_id, $sort ";

	#echo $q;
	$qr = @pg_query($q) or message1(pg_errormessage());
	?>
    <table style="width:100%;">
    	<tr>
        	<td colspan="7" style="text-align:center;"><?=$SYSCONF['BUSINESS_NAME']?><br />PHYSICAL COUNT REPORT<br />From <?=$from_date?> to <?=$to_date?></td>
        </tr>
    	<tr>
        	<th>#</td>
            <th>ITEM DESCRIPTION</th>
            <th>BARCODE</th>
            <th style="width:5%;">U/C</th>
            <th style="width:5%;">CASES</th>
            <th style="width:5%;">CsCOST</th>
            <th style="width:5%;">AMOUNT</th>
        </tr>
    <?
	
	$details = $details1 = '';
	$ctr=$total_cost = 0;
	$maccount_id = $mcategory_id = 'x~';
	$lc=8;
	$mcount=$subtotal_cost=$scount=0;
	while ($r = @pg_fetch_object($qr))
	{
		if ($maccount_id != $r->account_id)
		{
			if ($maccount_id != 'x~')
			{
				echo "<tr>";
				echo "<td colspan='5' style='text-align:right; font-weight:bolder;'>$sctr Item/s</td>";
				echo "<td style='text-align:right; font-weight:bolder;'></td>";
				echo "<td style='text-align:right; font-weight:bolder; border-top:1px solid #000;'>".number_format($stotal_amount,2,'.',',')."</td>";
				echo "</tr>";
			}						
			
			echo "<tr>";
			echo "<td colspan='7' style='text-align:left; font-weight:bolder; padding:5px 0px;'>SUPPLIER : ".lib::getAttribute('account','account_id',$r->account_id,'account_code')." ".lookUpTableReturnValue('x','account','account_id','account',$r->account_id)."</td>";
			echo "</tr>";
			
			$maccount_id=$r->account_id;
			$lc++;
			$subtotal_cost = $sctr = $stotal_amount = 0;
		}
		if ($mcategory_id != $r->category_id)
		{
			
			echo "<tr>";
			echo "<td colspan='7' style='text-align:left; font-weight:bolder; padding:5px 0px; '>".lookUpTableReturnValue('x','category','category_id','category',$r->category_id)."</td>";
			echo "</tr>";
			$mcategory_id=$r->category_id;
			$lc++;			
		}
		
		$ctr++;
		$sctr++;	
		
		if ($combineqty == 'Y')
		{
			if ($r->fraction3 == '1' or $r->fraction3 == '0')
			{
				$bacase = '0';
				$bunits = $r->case_qty + $r->unit_qty;
				$total_cost = $bunits*$r->cost3;
			}
			else
			{
				$qty=$r->case_qty * $r->fraction3 + $r->unit_qty;		
				$bacase = intval($qty/$r->fraction3);
				$bunits = $qty - $bacase*$r->fraction3; 
				$total_cost = $bacase*$r->cost3 + $bunits*($r->cost3/$r->fraction3);
			}
		}
		else
		{
			$bacase = $r->case_qty;
			$bunits = $r->unit_qty;
			$total_cost = $bacase*$r->cost3 + $bunits*($r->cost3/$r->fraction3);

		}		
		
	
		echo "<tr>";
		echo "<td>".$ctr."</td>";
		echo "<td>".$r->stock."</td>";
		echo "<td>".$r->barcode."</td>";
		echo "<td style='text-align:left;'>".$r->fraction3."</td>";
		echo "<td style='text-align:left; nowrap=\"nowrap\" '>$bacase : $bunits</td>";
		echo "<td style='text-align:right;'>".number_format($r->cost3,2,'.',',')."</td>";
		echo "<td style='text-align:right;'>".number_format($total_cost,2,'.',',')."</td>";
		echo "</tr>";
		
		$total_amount += $total_cost;
		$stotal_amount += $total_cost;		

	}
	if ($maccount_id != $r->account_id)
	{
		echo "<tr>";
		echo "<td colspan='5' style='text-align:right; font-weight:bolder;'>$sctr Item/s</td>";
		echo "<td style='text-align:right; font-weight:bolder;'></td>";
		echo "<td style='text-align:right; font-weight:bolder; border-top:1px solid #000;'>".number_format($stotal_amount,2,'.',',')."</td>";
		echo "</tr>";
	}
	
	echo "<tr>";
	echo "<td colspan='5' style='text-align:right; font-weight:bolder;'>$ctr Item/s</td>";
	echo "<td style='text-align:right; font-weight:bolder;'></td>";
	echo "<td style='text-align:right; font-weight:bolder; border-top:1px solid #000;'>".number_format($total_amount,2,'.',',')."</td>";
	echo "</tr>";
	
	echo "</table>";
	
	
}
else
{
	$incZero = 1;
}
if ($from_date == '') $from_date=date('m/d/Y');	
?>	
<?
//echo $details1;
?>
