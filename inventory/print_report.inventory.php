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

$account_id 	= $_REQUEST['account_id'];
$category_id 	= $_REQUEST['category_id'];
$sort 			= $_REQUEST['sort'];

?>
<?
if ($from_date == '')
{
	$from_date = date('m/d/Y');
}	
if ($p1=='Go' || $p1=='Print Draft' || 1)
{
	if ($from_date == '')
	{
		$from_date = date('m/d/Y');
	}	
	$mdate = mdy2ymd($from_date);
	
	$q = "select 
			*
		from 
			stock
		where 
			enable='Y' and inventory = 'Y' ";
	if ($account_id != '')
	{
		$q .= " and account_id='$account_id'";
	}
	if ($category_id != '')
	{
		$q .= " and category_id='$category_id'";
	}
	$q .= " order by account_id, category_id, $sort ";
	$qr = @pg_query($q) or message(pg_errormessage());
	$header = "\n\n";
	$header .= center($SYSCONF['BUSINESS_NAME'],80)."\n";
	$header .= center('PRICE LISTING REPORT',80)."\n";
	$header .= center('As of Date '.$from_date,80)."\n\n";
	$header .= "---- ---------------------------------------------- ---------------- ----- ----- ------------\n";
	$header .= "      Item Description                               Bar Code        Unit  U/C      UnPrice   \n";
	$header .= "---- ---------------------------------------------- ---------------- ----- ----- ------------\n";

	?>
    <table style="width:100%;">
    	<tr>
        	<td colspan="6" style="text-align:center;"><?=$SYSCONF['BUSINESS_NAME']?><br />PRICE LISTING REPORT<br />As of Date <?=$from_date?></td>
        </tr>
    	<tr>
        	<th>#</td>
            <th>ITEM DESCRIPTION</th>
            <th>BARCODE</th>
            <th style="width:10%;">UNIT</th>
            <th style="width:5%;">U/C</th>
            <th style="width:5%;">COST</th>
            <th style="width:10%;">U.PRICE</th>
        </tr>
    <?
	
	if ($p1 == 'Print Draft')
	{
		doPrint("<small3>");
	}
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
				$details .= "\n";
				echo "<tr></tr>";
				$lc = $lc+1;
			}						
			$details .= "Producer/Supplier: ".adjustSize(lookUpTableReturnValue('x','account','account_id','account',$r->account_id),25)."\n";
			
			echo "<tr>";
			echo "<td colspan=\"6\"> Producer/Supplier: ".lookUpTableReturnValue('x','account','account_id','account',$r->account_id)."</td>";
			echo "</tr>";
			$maccount_id=$r->account_id;
			$lc++;
			$subtotal_cost = $sctr = 0;
		}
		if ($mcategory_id != $r->category_id)
		{
			$details .= "\n  ".adjustSize(lookUpTableReturnValue('x','category','category_id','category',$r->category_id),25)."\n";
			$mcategory_id=$r->category_id;
			$lc++;
			
			echo "<tr>";
			echo "<td colspan='6' style='margin-left:20px; font-weight:bolder; padding:5px 0px;'>".lookUpTableReturnValue('x','category','category_id','category',$r->category_id)."</td>";
			echo "</tr>";
		}
		
		$ctr++;
		$sctr++;	
		
		if ($r->stock_description != '') $stock_description=$r->stock_description;
		else $stock_description = $r->stock;

		$details .= adjustRight($ctr,4).'. '.
					adjustSize(substr($stock_description,0,45),45).' '.
					adjustSize($r->barcode,16).' '.
					adjustSize($r->unit1,5).' '.
					adjustSize($r->fraction3,5).' '.
					adjustRight(number_format($r->price1,2),10)."\n";
					
		echo "<tr>";
		echo "<td>".$ctr."</td>";
		echo "<td>".$stock_description."</td>";
		echo "<td style='text-align:center;'>".$r->barcode."</td>";
		echo "<td style='text-align:center;'>".$r->unit1."</td>";
		echo "<td style='text-align:center;'>".$r->fraction3."</td>";
		echo "<td style='text-align:center;'>".$r->cost1."</td>";
		echo "<td style='text-align:right;'>".number_format($r->price1,2)."</td>";
		echo "</tr>";
		
		$total_cost += $r->cost1 * $balance_qty;
		$subtotal_cost += $r->cost1 * $balance_qty;
		
		if (strlen($stock_description)>45)
		{
			$details .= space(14).adjustSize(substr($stock_descsription,46,45),45)."\n";
			$lc++;
		}			
		$lc++;

		if ($lc>55)
		{
			$details1 .= $header.$details;
		?>
			<tr>
				<td colspan="6" style="text-align:center;"><?=$SYSCONF['BUSINESS_NAME']?><br />PRICE LISTING REPORT<br />As of Date <?=$from_date?></td>
			</tr>
			<tr>
        	<th>#</td>
            <th>ITEM DESCRIPTION</th>
            <th>BARCODE</th>
            <th style="width:10%;">UNIT</th>
            <th style="width:5%;">U/C</th>
            <th style="width:5%;">COST</th>
            <th style="width:10%;">U.PRICE</th>
        </tr>
		<?
			if ($p1 == 'Print Draft')
			{
				doPrint($header.$details."<eject>");
			}
			$lc=8;
			$details = '';
		}

	}
	echo "<tr>";
	echo "<td colspan='7' style='text-align:right; font-weight:bolder; border-top:1px solid #000;'>GRAND TOTAL: $ctr</td>";
	echo "</tr>";
	echo "</table>";
	
	$details .= "---- ---------------------------------------------- ---------------- ----- ----- ------------\n";
	$details1 .= $header.$details;
	if ($p1=='Print Draft')
	{
		doPrint($header.$details);
	}	
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
