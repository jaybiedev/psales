<?
function numform($num){
	return number_format($num,2,'.',',');
}
?>

<style type="text/css">

	*{ font-family:Arial, Helvetica, sans-serif; font-size:11px;}
	table{
		border-collapse:collapse;
		width:100%;
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
include_once('../lib/lib.inventory.php');
include_once('stockbalance.php');

$account_id 	= $_REQUEST['account_id'];
$category_id 	= $_REQUEST['category_id'];
$sort 			= $_REQUEST['sort'];
$from_date 		= $_REQUEST['from_date'];
$to_date 		= $_REQUEST['to_date'];

$mfrom_date = mdy2ymd($from_date);
$mto_date	= mdy2ymd($to_date);



?>
<?
if ($p1=='Go' || $p1=='Print Draft' || 1)
{
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
	
	?>
    <div style="text-align:left;">
		<?=$SYSCONF['BUSINESS_NAME']?><br />INVENTORY LEDGER REPORT<br />As of Date <?=$from_date?> to <?=$to_date?>    
    </div>
    
    <table style="width:100%;">
    	<tr>
            <th style="text-align:left;">#</td>
            <th style="text-align:left;">ITEM DESCRIPTION</th>
            <th style="text-align:left;">BARCODE</th>
            <th style="text-align:left;">U/C</th>
            <th colspan="2" style="text-align:center;">BEG QTY</th>
            <th colspan="2" style="text-align:center;">RECD QTY</th>
            <th colspan="2" style="text-align:center;">POR QTY</th>
            <th colspan="2" style="text-align:center;">TRANSFER QTY</th>
            <th colspan="2" style="text-align:center;">SOLD QTY</th>
            <th colspan="2" style="text-align:center;">ADJ QTY</th>
            <th colspan="2" style="text-align:center;">BAL QTY</th>
        </tr>
        <tr>
        	<th></td>
            <th></th>
            <th></th>
            <th></th>
            <th>CASES</th>
            <th>UNITS</th>
            <th>CASES</th>
            <th>UNITS</th>
            <th>CASES</th>
            <th>UNITS</th>
            <th>CASES</th>
            <th>UNITS</th>
            <th>CASES</th>
            <th>UNITS</th>
            <th>CASES</th>
            <th>UNITS</th>
            <th>CASES</th>
            <th>UNITS</th>
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
				$details .= "\n";
				echo "<tr></tr>";
				$lc = $lc+1;
			}						
			
			echo "<tr>";
			echo "<td colspan=\"11\"> Producer/Supplier: [".lookUpTableReturnValue('x','account','account_id','account_code',$r->account_id)."] ".lookUpTableReturnValue('x','account','account_id','account',$r->account_id)."</td>";
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
			echo "<td colspan='11' style='margin-left:20px; font-weight:bolder; padding:5px 0px;'>".lookUpTableReturnValue('x','category','category_id','category',$r->category_id)."</td>";
			echo "</tr>";
		}
		
		/*$stkled = getStockBalance($r->stock_id,$mfrom_date, $mto_date);		
		
		$beg_qty 				= $stkled['beg_qty'];
		$rr_qty					= $stkled['rr_qty'];
		$adj_qty		 		= $stkled['adj_qty'];
		$sales_qty			 	= $stkled['sales_qty'];
		$por_qty		 		= $stkled['por_qty'];
		$transfer_qty			= $stkled['transfer_qty'];
		$as_of_date_qty			= $stkled['as_of_date_qty'];*/
	
	

		$arr_inventory = Inventory::getStockBalance($r->stock_id,$mfrom_date,$mto_date);	

		$beg_qty 				= $arr_inventory['beg_qty'];
		$beg_qty      = Inventory::getCurrentBalance($r->stock_id,lib::minusOneDay($from_date));				

		$rr_qty					= $arr_inventory['rr_qty'];
		$adj_qty		 		= $arr_inventory['adj_qty'];
		$sales_qty			 	= $arr_inventory['sales_qty'];
		$por_qty		 		= $arr_inventory['por_qty'];
		$transfer_qty			= $arr_inventory['transfer_qty'];
		$as_of_date_qty			= $arr_inventory['as_of_date_qty'];

		
		if ($r->fraction3 == '1' or $r->fraction3 == '0') {
			$beg_case = '0';
			$beg_units = $beg_qty;

			$rr_case = '0';
			$rr_units = $rr_qty;

			$adj_case = '0';
			$adj_units = $adj_qty;

			$sales_case = '0';
			$sales_units = $sales_qty;
			
			$por_case = '0';
			$por_units = $por_qty;
			
			$transfer_case = '0';
			$transfer_units = $transfer_qty;
			
			$as_of_date_case = '0';
			$as_of_date_units = $as_of_date_qty;
		} else {
			$beg_case = intval($beg_qty/$r->fraction3);
			$beg_units = $beg_qty - $beg_case*$r->fraction3; 
			
			$rr_case = intval($rr_qty/$r->fraction3);
			$rr_units = $rr_qty - $rr_case*$r->fraction3; 
			
			$adj_case = intval($adj_qty/$r->fraction3);
			$adj_units = $adj_qty - $adj_case*$r->fraction3; 
			
			$sales_case = intval($sales_qty/$r->fraction3);
			$sales_units = $sales_qty - $sales_case*$r->fraction3; 
			
			$por_case = intval($por_qty/$r->fraction3);
			$por_units = $por_qty - $por_case*$r->fraction3; 
			
			$transfer_case = intval($transfer_qty/$r->fraction3);
			$transfer_units = $transfer_qty - $transfer_case*$r->fraction3; 
			
			$as_of_date_case = intval($as_of_date_qty/$r->fraction3);
			$as_of_date_units = $as_of_date_qty - $as_of_date_case*$r->fraction3; 
		}
		
		$ctr++;
		$sctr++;	
		
		if ($r->stock_description != '') $stock_description=$r->stock_description;
		else $stock_description = $r->stock;
					
		echo "<tr>";
		echo "<td>".$ctr."</td>";
		echo "<td>".$stock_description."</td>";
		echo "<td style='text-align:left;'>".$r->barcode."</td>";
		echo "<td style='text-align:left;'>".$r->fraction3."</td>";
		
		echo "<td style='text-align:right;'>".numform($beg_case)."</td>";
		echo "<td style='text-align:right;'>".numform($beg_units)."</td>";
		
		echo "<td style='text-align:right;'>".numform($rr_case)."</td>";
		echo "<td style='text-align:right;'>".numform($rr_units)."</td>";
		
		echo "<td style='text-align:right;'>".numform($por_case)."</td>";
		echo "<td style='text-align:right;'>".numform($por_units)."</td>";
		
		echo "<td style='text-align:right;'>".numform($transfer_case)."</td>";
		echo "<td style='text-align:right;'>".numform($transfer_units)."</td>";
		
		echo "<td style='text-align:right;'>".numform($sales_case)."</td>";
		echo "<td style='text-align:right;'>".numform($sales_units)."</td>";
		
		echo "<td style='text-align:right;'>".numform($adj_case)."</td>";
		echo "<td style='text-align:right;'>".numform($adj_units)."</td>";
		
		echo "<td style='text-align:right;'>".numform($as_of_date_case)."</td>";
		echo "<td style='text-align:right;'>".numform($as_of_date_units)."</td>";
	
		echo "</tr>";
		
		$total_cost += $r->cost1 * $balance_qty;
		$subtotal_cost += $r->cost1 * $balance_qty;
		
		$lc++;

		if ($lc>55)
		{
		?>
			<tr>
				<td colspan="11" style="text-align:center;"><?=$SYSCONF['BUSINESS_NAME']?><br />INVENTORY LEDGER REPORT<br />As of Date <?=$from_date?> to <?=$to_date?></td>
			</tr>
			<tr>
                <th style="text-align:left;">#</td>
                <th style="text-align:left;">ITEM DESCRIPTION</th>
                <th style="text-align:left;">BARCODE</th>
                <th style="text-align:left;">U/C</th>
                <th colspan="2" style="text-align:center;">BEG QTY</th>
                <th colspan="2" style="text-align:center;">RECD QTY</th>
                <th colspan="2" style="text-align:center;">POR QTY</th>
                <th colspan="2" style="text-align:center;">TRANSFER QTY</th>
                <th colspan="2" style="text-align:center;">SOLD QTY</th>
                <th colspan="2" style="text-align:center;">ADJ QTY</th>
                <th colspan="2" style="text-align:center;">BAL QTY</th>
            </tr>
            <tr>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th>CASES</th>
                <th>UNITS</th>
                <th>CASES</th>
                <th>UNITS</th>
                <th>CASES</th>
                <th>UNITS</th>
                <th>CASES</th>
                <th>UNITS</th>
                <th>CASES</th>
                <th>UNITS</th>
                <th>CASES</th>
                <th>UNITS</th>
                <th>CASES</th>
                <th>UNITS</th>
            </tr>
		<?
			$lc=8;
			$details = '';
		}

	}
	echo "<tr>";
	echo "<td colspan='18' style='text-align:right; font-weight:bolder; border-top:1px solid #000;'>GRAND TOTAL: $ctr</td>";
	echo "</tr>";
	echo "</table>";
	
	$details .= "---- ---------------------------------------------- ---------------- ----- ----- ------------\n";
	$details1 .= $header.$details;
	if ($p1=='Print Draft')
	{
		#doPrint($header.$details);
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
