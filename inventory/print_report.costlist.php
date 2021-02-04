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

$account_id 	= $_REQUEST['account_id'];
$category_id 	= $_REQUEST['category_id'];
$sort 			= $_REQUEST['sort'];
$from_date		= $_REQUEST['from_date'];
$condition		= $_REQUEST['condition'];
$mvalue			= $_REQUEST['mvalue'];

#echo "$account_id,$category_id,$sort,$condition,$mvalue";

?>
<?
if ($p1=='Go' || $p1=='Print Draft' || 1)
{
	if ($from_date == '')
	{
		$from_date = date('m/d/Y');
	}	
	$mdate = mdy2ymd($from_date);
	
	$q = "select *
			
		from 
			stock
		where 
			enable='Y' ";
	if ($account_id != '')
	{
		$q .= " and account_id='$account_id'";
	}
	if ($category_id != '')
	{
		$q .= " and category_id='$category_id'";
	}
	if ($condition != '')
	{
		$q .= " and cost1 $condition '$mvalue' ";
	}
	$q .= " order by account_id, category_id, $sort ";

	$qr = @pg_query($q) or message(pg_errormessage());
	?>
    <table style="width:100%;">
    	<tr>
        	<td colspan="7" style="text-align:center;"><?=$SYSCONF['BUSINESS_NAME']?><br />COST LISTING REPORT<br />As of Date <?=$from_date?></td>
        </tr>
    	<tr>
        	<th>#</td>
            <th>ITEM DESCRIPTION</th>
            <th>BARCODE</th>
            <th>UNIT</th>
            <th style="width:5%;">U/C</th>
            <th style="width:5%;">UnCOST</th>
        </tr>
    <?
	
	$details = $details1 = '';
	$ctr=$total_cost = 0;
	$maccount_id = $mcategory_id = 'x~';
	$lc=8;
	$mcount=$subtotal_cost=$scount=0;
	while ($r = @pg_fetch_object($qr))
	{
		
		$stkled = stockBalance($r->stock_id,'', $mdate);
		/*echo "{$r->stock_id}";
		print_r($stkled);
		echo "<br>";*/
		$balance_qty = $stkled['balance_qty'];
		$average_cost = $stkled['average_cost'];
		
		if ($balance_qty <=0 && $incZero=='N') continue;
		if ($maccount_id != $r->account_id)
		{
			if ($maccount_id != 'x~')
			{
				/*echo "<tr>";
				echo "<td colspan='4' style='text-align:right; font-weight:bolder;'>$sctr Item/s</td>";
				echo "<td colspan='2' style='text-align:right; font-weight:bolder;'>SUB-TOTAL:</td>";
				echo "<td style='text-align:right; font-weight:bolder; border-top:1px solid #000;'>$subtotal_cost</td>";
				echo "</tr>";*/
			}						
			
			echo "<tr>";
			echo "<td colspan='7' style='text-align:left; font-weight:bolder; padding:5px 0px;'>".lookUpTableReturnValue('x','account','account_id','account',$r->account_id)."</td>";
			echo "</tr>";
			
			$maccount_id=$r->account_id;
			$lc++;
			$subtotal_cost = $sctr = 0;
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
		
		if ($r->stock_description != '') $stock_description=$r->stock_description;
		else $stock_description = $r->stock;
		
	
		echo "<tr>";
		echo "<td>".$ctr."</td>";
		echo "<td>".$stock_description."</td>";
		echo "<td>".$r->barcode."</td>";
		echo "<td style='text-align:left;'>".$r->unit1."</td>";
		echo "<td style='text-align:left;'>".$r->fraction3."</td>";
		echo "<td style='text-align:right;'>".number_format($r->cost1,2,'.',',')."</td>";
		echo "</tr>";

	}
	/*if ($maccount_id != $r->account_id)
	{
		echo "<tr>";
		echo "<td colspan='4' style='text-align:right; font-weight:bolder;'>$sctr Item/s</td>";
		echo "<td colspan='2' style='text-align:right; font-weight:bolder;'>SUB-TOTAL:</td>";
		echo "<td style='text-align:right; font-weight:bolder; border-top:1px solid #000;'>$subtotal_cost</td>";
		echo "</tr>";
	}*/
	
	/*echo "<tr>";
	echo "<td colspan='4' style='text-align:right; font-weight:bolder; border-top:1px solid #000; border-bottom:1px solid #000;'>$ctr Item/s</td>";
	echo "<td colspan='3' style='text-align:right; font-weight:bolder; border-top:1px solid #000; border-bottom:1px solid #000;'>GRAND TOTAL: $total_cost</td>";
	echo "</tr>";*/
	
	
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
