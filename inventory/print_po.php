<?
include_once('../lib/library.php');
include_once('../lib/dbconfig.php');
include_once('../lib/connect.php');
include_once('../var/system.conf.php');
include_once('../lib/library.js.php');

include_once("inventory.func.php");
?>
<style type="text/css">
	*{ font-family:Arial, Helvetica, sans-serif; font-size:11px;}
	table.content{
		border-collapse:collapse;
		width:100%;
	}
			
	table.content td{
		padding:0px 5px;
	}
	table.content th{
		border-top:1px solid #000;
		border-bottom:1px solid #000;	
		text-align:left;
		padding:0px 5px;
	}
	.line_bottom {
		border-bottom-width: 1px;
		border-bottom-style: solid;
		border-bottom-color: #000000;
		border-left: 0px;
		border-right: 0px;
		border-top: 0px;
		width:120px;
	}

</style>
<script type="text/javascript">
	function printPage() { print(); } //Must be present for Iframe printing
</script>
<?
$po_header_id	= $_REQUEST['id'];
$result = @pg_query("select * from po_header where po_header_id = '$po_header_id'");
$r = @pg_fetch_assoc($result);

$account_code = lookUpTableReturnValue('','account','account_id','account_code',$r['account_id']);
$account = lookUpTableReturnValue('','account','account_id','account',$r['account_id']);
$address = lookUpTableReturnValue('','account','account_id','address',$r['account_id']);
$terms = lookUpTableReturnValue('','account','account_id','terms',$r['account_id']);

/**
echo "<pre>";
print_r($r);
echo "</pre>";/**/

?>
<table style="display:inline-table; margin-left:10px;"> 
	<tr>
    	<td><?=$SYSCONF['BUSINESS_NAME']?></td>
    </tr>
    <tr>
    	<td><?=$SYSCONF['BUSINESS_ADDR']?></td>
        
    </tr>
    <tr>
    	<td>[<?=$account_code?>] <?=$account?></td>
    </tr>
    <tr>
    	<td><?=$address?></td>
    </tr>
</table>
<table style="display:inline-table;">
	 <tr>
        <td colspan="4">PURCHASE ORDER</td>
    </tr>
    <tr>
    	<td>PO No.</td>
        <td>:<?=str_pad($po_header_id,8,0,STR_PAD_LEFT)?></td>
	</tr>
    <tr>
    	<td>Date</td>
        <td><?=ymd2mdy($r['date'])?></td>
    </tr>
    <tr>
    	<td>Terms</td>
        <td><?=$terms?></td>
    </tr>
    <tr>
    	<td>Status</td>
        <td><?=status($r['status'])?></td>
    </tr>
</table>
<table class="content" cellpadding="1">
	<tr>
    	<th style="text-align:right; width:10%;" nowrap>Ordered Quantity</th>
	<th style="width:10%;">Unit</th>
        <th style="width:20%;">Item Code</th>
        <th>Item Description</th>
    </tr>	
    <?
	$result = @pg_query("select * from po_detail as d where po_header_id = '$po_header_id'");
	while($r = @pg_fetch_assoc($result)){
		$stock_id = $r['stock_id'];
		$barcode	= $r['barcode'];
		$stock = lookUpTableReturnValue('','stock','stock_id','stock',$stock_id);
		$unit1 = lookUpTableReturnValue('','stock','stock_id','unit1',$stock_id);
		$unit3 = lookUpTableReturnValue('','stock','stock_id','unit3',$stock_id);
		$unit_qty	= $r['unit_qty'];
		$case_qty	= $r['case_qty'];

		$qty_display = NULL;
		if( $unit_qty > 0 )	{
			$qty_display = number_format($unit_qty,2).' '.$unit1;
			$quantity = number_format($unit_qty,2);
			$unit = $unit1;
		} 
		if( $case_qty > 0) {
			$qty_display.= ' '.	number_format($case_qty,2).' '.$unit3;
			$quantity = number_format($case_qty,2);
			$unit = $unit3;
		}
		
    ?>
    	<tr>
            <td style="text-align:right;"><?=$quantity?></td>
	     <td><?=$unit?></td>
            <td><?=$barcode?></td>
            <td><?=$stock?></td>
        </tr>
    <? } ?>
</table>

<table cellspacing="0" cellpadding="5" align="center" width="98%" style="border:none; text-align:center; margin-top:10px;" class="summary">
    <tr>
        <td>Prepared by:<p>
		<?=$ADMIN['name']?><br /><?=date('m/d/Y g:ia')?></p></td>
        <td>Checked By:<p>
            <input type="text" class="line_bottom" /><br>&nbsp;</p></td>
        <td>Approved By:<p>
            <input type="text" class="line_bottom" /><br>&nbsp;</p></td>
    </tr>
</table>

