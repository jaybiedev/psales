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
	table{
		border-collapse:collapse;
		width:100%;
	}
		
	table th{
		border-top:1px solid #000;
		border-bottom:1px solid #000;	
		text-align:left;
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
	.border-top td{
		border-top:1px solid #000;	
	}

</style>
<script type="text/javascript">
	function printPage() { print(); } //Must be present for Iframe printing
</script>
<?
$invadjust_header_id	= $_REQUEST['id'];
$result = @pg_query("select * from invadjust_header where invadjust_header_id = '$invadjust_header_id'");
$r = @pg_fetch_assoc($result);
$invadjust_header_id = $r['invadjust_header_id'];
$date = $r['date'];

$gross_amount		= $r['gross_amount'];
$discount_amount	= $r['discount_amount'];
$net_amount			= $r['net_amount'];
$freight_amount		= $r['freight_amount'];
$tax_amount			= $r['tax_amount'];

/*
echo "<pre>";
print_r($r);
echo "</pre>";
*/

?>
<table>
	
	<tr>
    	<td width="40%"><?=$SYSCONF['BUSINESS_NAME']?></td>
       <td colspan="3">INVENTORY ADJUSTMENTS</td>
        

    </tr>
    <tr>
    	<td><?=$SYSCONF['BUSINESS_ADDR']?></td>
        <td width="50">ST No.</td>
        <td width="80">:<?=str_pad($invadjust_header_id,8,0,STR_PAD_LEFT)?></td>
		<td nowrap width="80">Date</td>
        <td>:<?=$date?></td>
    </tr>
</table>
<table>
	<tr>
        <th width="10%">Barcode</th>
        <th>Item Description</th>
        <th width="10%">Qty</th>
    </tr>	
    <?
	$result = @pg_query("select * from invadjust_detail as d where invadjust_header_id = '$invadjust_header_id'");
	while($r = @pg_fetch_assoc($result)){
		$stock_id = $r['stock_id'];
		$stock = lookUpTableReturnValue('','stock','stock_id','stock',$stock_id);
		$unit1 = lookUpTableReturnValue('','stock','stock_id','unit1',$stock_id);
		$unit3 = lookUpTableReturnValue('','stock','stock_id','unit3',$stock_id);
		
		$barcode = lookUpTableReturnValue('x','stock','stock_id','barcode',$stock_id);
		$price1 = lookUpTableReturnValue('x','stock','stock_id','price1',$stock_id);
		
		$unit_qty	= ($r['unit_qty'])?$r['unit_qty']:0;
		$case_qty	= ($r['case_qty'])?$r['case_qty']:0;
    ?>
    	<tr>
            <td><?=$barcode?></td>
            <td><?=$stock?></td>
          	<td style="text-align:left;" nowrap="nowrap"><?="$case_qty : $unit_qty"?></td>
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
       <!-- <td>Approved for Payment:<p>
        <input type="text" class="line_bottom" /><br>&nbsp;</p></td> -->
    </tr>
</table>

