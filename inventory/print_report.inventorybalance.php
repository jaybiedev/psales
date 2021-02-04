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
include_once('../lib/lib.inventory.php');
include_once('../lib/lib.salvio.php');
include_once('stockbalance.php');

$account_id       = $_REQUEST['account_id'];
$from_category_id = $_REQUEST['from_category_id'];
$to_category_id   = $_REQUEST['to_category_id'];
$sort             = $_REQUEST['sort'];
$date             = $_REQUEST['date'];
$incZero          = $_REQUEST['incZero'];

$mdate = lib::mdy2ymd($from_date);

$include_concessionaire_check_box = ( $include_concessionaire_check_box ) ? 1 : 0;

?>
<?
if ($p1=='Go' || $p1=='Print Draft' || 1)
{
	if ($from_date == '')
	{
		$from_date = date('m/d/Y');
	}	

	$mdate = lib::mdy2ymd($_REQUEST['from_date']);
	$from_category_id = $_REQUEST['from_category_id'];
	$to_category_id = $_REQUEST['to_category_id'];

	$from_category_code  = $to_category_code = '';
	if ($from_category_id != '')
	{
		$from_category_code = lookUpTableReturnValue('x','category','category_id','category_code',$from_category_id);
	}
	if ($to_category_id != '')
	{
		$to_category_code = lookUpTableReturnValue('x','category','category_id','category_code',$to_category_id);
	}

	$from_len = strlen($from_category_code);
	$to_len = strlen($to_category_code);	

	$supplier_code_length = strlen($from_supplier_code) >= strlen($to_supplier_code) ? strlen($to_supplier_code) : strlen($from_supplier_code);
	

	
	$q = "select *
			
		from 
			stock,
			category,
			account
		where 
			stock.category_id = category.category_id and 
			stock.account_id = account.account_id and
			stock.inventory='Y' and
			stock.enable='Y' ";
	if ($account_id != '')
	{
		$q .= " and account.account_id='$account_id'";
	}

	if ( !empty( $from_supplier_code ) && 
		!empty( $to_supplier_code ) ){

		$q .= " 
			and substr(account.account_code, 1, $supplier_code_length) >= '$from_supplier_code' 
			and substr(account.account_code, 1, $supplier_code_length) <= '$to_supplier_code'";

		/*$q .= " and account.account between '$from_supplier_code' and '$to_supplier_code'";*/

	} else if( !empty( $from_supplier_code ) &&
		empty( $to_supplier_code ) ){

		$q .= " and account.account_code = '$from_supplier_code'";

	} else if ( !empty( $to_supplier_code ) && empty( $from_supplier_code ) ){

		$q .= " and account.account_code = '$to_supplier_code'";

	}

	if ($from_category_code != '')
	{
		$q .= " and substr(category.category_code,1,$from_len)>='$from_category_code'";
	}		
	if ($to_category_id != '')
	{
		$q .= " and substr(category.category_code,1,$to_len)<='$to_category_code'";
	}		

	/*
	Do not incldue Conssionaires if 'include_concessionaire_check_box' is not checked
	*/

	if( !$include_concessionaire_check_box ){
		$q .= " and account.account_type_id != '8'";
	}

	$q .= " order by account.account_code, category.category_code, $sort ";
	

	$qr = @pg_query($q) or message(pg_errormessage().$q);
	?>
    <table style="width:100%;">
    	<tr>
        	<td colspan="7" style="text-align:center;"><?=$SYSCONF['BUSINESS_NAME']?><br />INVENTORY BALANCE REPORT<br />As of Date <?=$from_date?></td>
        </tr>
    	<tr>
        	<th>#</th>
            <th>BARCODE</th>
            <th>ITEM DESCRIPTION</th>
            <th style="width:5%;">U/C</th>
            <th style="width:10%;" colspan='2'>BALANCE</th>
            <th style="width:5%;">COST</th>
            <th style="width:5%;">INV.COST</th>
        </tr>
		<tr>
			<th></td>
            <th></th>
            <th></th>
            <th></th>
            <th>CASES</th>
			<th>UNITS</th>
            <th></th>
            <th></th>
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
		
		#$stkled = stockBalance($r->stock_id,'', $mdate);
		#$stkled = getStockBalance($r->stock_id,'', $mdate);
		
		/*echo "{$r->stock_id}";
		print_r($stkled);
		echo "<br>";*/

		#$balance_qty = $stkled['balance_qty'];
		#$average_cost = $stkled['average_cost'];
		
		$balance_qty = $stkled['balance_qty'] = Inventory::getCurrentBalance($r->stock_id,$mdate);
		
		if ($balance_qty <=0 && $incZero=='N') continue;
		if ($maccount_id != $r->account_id) {
			if ($maccount_id != 'x~') {
				echo "<tr>";
				echo "<td colspan='3' style='text-align:right; font-weight:bolder;'>$sctr Item/s</td>";
				echo "<td colspan='4' style='text-align:right; font-weight:bolder;'>SUB-TOTAL:</td>";
				echo "<td style='text-align:right; font-weight:bolder; border-top:1px solid #000;'>$subtotal_cost</td>";
				echo "</tr>";
			}						
			
			echo "<tr>";
			echo "<td colspan='8' style='text-align:left; font-weight:bolder; padding:5px 0px;'> [".lookUpTableReturnValue('x','account','account_id','account_code',$r->account_id)."] ".lookUpTableReturnValue('x','account','account_id','account',$r->account_id)."</td>";
			echo "</tr>";
			
			$maccount_id=$r->account_id;
			$lc++;
			$subtotal_cost = $sctr = 0;
		}

		if ($mcategory_id != $r->category_id) {			
			echo "<tr>";
			echo "<td colspan='8' style='text-align:left; font-weight:bolder; padding:5px 0px; '>".lookUpTableReturnValue('x','category','category_id','category',$r->category_id)."</td>";
			echo "</tr>";
			$mcategory_id=$r->category_id;
			$lc++;			
		}
		

		$ctr++;
		$sctr++;	

		if ($r->fraction3 == '1' or $r->fraction3 == '0') {
			$lcase = '0';
			$lunits = $stkled['balance_qty'];
		} else {
			$lcase = intval($stkled['balance_qty']/$r->fraction3);
			$lunits = $stkled['balance_qty'] - $lcase*$r->fraction3; 
		}
		
	
		echo "<tr>";
		echo "<td>".$ctr."</td>";
		echo "<td>".$r->barcode."</td>";
		echo "<td style='text-align:left;'>".$r->stock."</td>";
		echo "<td style='text-align:left;'>".$r->fraction3."</td>";
		echo "<td style='text-align:right;' nowrap=\"nowrap\">".number_format($lcase,2,'.',',')."</td>";
		echo "<td style='text-align:right;' nowrap=\"nowrap\">".number_format($lunits,2,'.',',')."</td>";
		echo "<td style='text-align:right;'>".number_format($r->cost1,2,'.',',')."</td>";
		echo "<td style='text-align:right;'>".number_format($r->cost1 * $balance_qty,2,'.',',')."</td>";
		echo "</tr>";
		
		$total_cost    += $r->cost1 * $balance_qty;
		$subtotal_cost += $r->cost1 * $balance_qty;
		
		

	}
	if ($maccount_id != $r->account_id)
	{
		echo "<tr>";
		echo "<td colspan='3' style='text-align:right; font-weight:bolder;'>$sctr Item/s</td>";
		echo "<td colspan='4' style='text-align:right; font-weight:bolder;'>SUB-TOTAL:</td>";
		echo "<td style='text-align:right; font-weight:bolder; border-top:1px solid #000;'>".number_format($subtotal_cost,2,'.',',')."</td>";
		echo "</tr>";
	}
	
	echo "<tr>";
	echo "<td colspan='4' style='text-align:right; font-weight:bolder; border-top:1px solid #000; border-bottom:1px solid #000;'>$ctr Item/s</td>";
	echo "<td colspan='4' style='text-align:right; font-weight:bolder; border-top:1px solid #000; border-bottom:1px solid #000;'>GRAND TOTAL: ".number_format($total_cost,2,'.',',')."</td>";
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
