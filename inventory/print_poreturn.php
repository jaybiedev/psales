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
		
	table.content th{
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
$por_header_id	= $_REQUEST['id'];
$result = @pg_query("select * from por_header where por_header_id = '$por_header_id'");
$r = $aPOR =  @pg_fetch_assoc($result);

$account_code = lookUpTableReturnValue('','account','account_id','account_code',$r['account_id']);
$account = lookUpTableReturnValue('','account','account_id','account',$r['account_id']);
$address = lookUpTableReturnValue('','account','account_id','address',$r['account_id']);
$internal_reference = $r['internal_reference'];

$rr_header_id = lookUpTableReturnValue('','por_header','por_header_id','rr_header_id',$por_header_id);
$date_received = lookUpTableReturnValue('','rr_header','rr_header_id','date',$rr_header_id);
$date_received = ($date_received != "No Record")?ymd2mdy($date_received):"No Record";
$invoice_date = lookUpTableReturnValue('','por_header','por_header_id','invoice_date',$por_header_id);
$invoice_date = (($invoice_date != "No Record"))?ymd2mdy($invoice_date ):"No Record";
$invoice = lookUpTableReturnValue('','por_header','por_header_id','reference',$por_header_id);

$gross_amount		= $r['gross_amount'];
$discount_amount	= $r['discount_amount'];
$net_amount			= $r['net_amount'];
$freight_amount		= $r['freight_amount'];
$tax_amount			= $r['tax_amount'];

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
        <td colspan="4">PURCHASE RETURN</td>
    </tr>
    <tr>
    	<td width="80">POR No.</td>
        <td width="80">:<?=str_pad($por_header_id,8,0,STR_PAD_LEFT)?></td>
		<td width="50">Inv. #</td>
        <td>:<?=$invoice?></td>
    </tr>
    <tr>
    	<td>SRR No.</td>
        <td>:<?=str_pad($rr_header_id,8,0,STR_PAD_LEFT)?></td>
        <td>Inv Date</td>
        <td>:<?=$invoice_date?></td>
    </tr>
    <tr>
        <td nowrap>Date Received</td>
        <td>:<?=$date_received?></td>
        <td nowrap>Internal Refernce</td>
        <td>:<?=$internal_reference?></td>
    </tr>
    </tr>
</table>
<table class="content" cellpadding="1">
	<tr>
    	<th width="24%">Returned Quantity</th>
        <th width="11%">Barcode</th>
        <th >Item Description</th>
        <!--<th width="11%" style="text-align:right;">UnPrice</th>-->
        <th width="10%" style="text-align:right;">UnCost</th>
        <th width="9%" style="text-align:right;">Amount</th>
    </tr>	
    <?
	$result = @pg_query("select * from por_detail as d where por_header_id = '$por_header_id'");
	while($r = @pg_fetch_assoc($result)){
		$stock_id = $r['stock_id'];
		$barcode	= $r['barcode'];
		$stock = lookUpTableReturnValue('','stock','stock_id','stock',$stock_id);
		$unit1 = lookUpTableReturnValue('','stock','stock_id','unit1',$stock_id);
		$unit3 = lookUpTableReturnValue('','stock','stock_id','unit3',$stock_id);
		
		$price1 = lookUpTableReturnValue('x','stock','stock_id','price1',$stock_id);
		$price3 = lookUpTableReturnValue('x','stock','stock_id','price3',$stock_id);
		
		$unit_qty	= $r['unit_qty'];
		$case_qty	= $r['case_qty'];
		
		$cost1 	= $r['cost1'];
		$cost3	= $r['cost3'];
		$amount	= $r['amount'];

		$qty_display = NULL;
		if( $unit_qty > 0 )	{
			$qty_display = number_format(0-$unit_qty,2).' '.$unit1;
		} 
		if( $case_qty > 0) {
			$qty_display.= ' '.	number_format(0-$case_qty,2).' '.$unit3;
		}
		
		$cost_display = ($unit_qty > 0) ? $cost1 : $cost3;
		$price_display = ($unit_qty > 0) ? $price1 : $price3;
		
		
    ?>
    	<tr>
        	<td><?=$qty_display?></td>
            <td><?=$barcode?></td>
            <td><?=$stock?></td>
         <!-- <td style="text-align:right;"><?=number_format($price_display,2,'.',',')?></td>-->
          <td style="text-align:right;"><?=number_format($cost_display,2,'.',',')?></td>
          <td style="text-align:right;"><?=number_format(0-$amount,2,'.',',')?></td>
        </tr>
    <? } ?>
    
        <tr class="border-top">
            <td colspan="4" style="text-align:right;">GROSS AMOUNT</td>
          <td style="text-align:right;"><?=number_format(0-$gross_amount,2,'.',',')?></td>
        </tr>
        <tr>
            <td colspan="4" style="text-align:right;">DISCOUNT</td>
          <td style="text-align:right;"><?=number_format(0-$discount_amount,2,'.',',')?></td>
        </tr>
        <tr>
            <td colspan="4" style="text-align:right;">TAX AMOUNT</td>
          <td style="text-align:right;"><?=number_format(0-$tax_amount,2,'.',',')?></td>
        </tr>
        <tr>
            <td colspan="4" style="text-align:right;">SUB-TOTAL</td>
          <td style="text-align:right;"><?=number_format(0-($gross_amount-$discount_amount+$tax_amount),2,'.',',')?></td>
        </tr>
        <tr>
            <td colspan="4" style="text-align:right;">FREIGHT AMOUNT</td>
          <td style="text-align:right;"><?=number_format(0-$freight_amount,2,'.',',')?></td>
        </tr>
        <tr>
            <td colspan="4" style="text-align:right;">NET AMOUNT</td>
          <td style="text-align:right;"><?=number_format(0-$net_amount,2,'.',',')?></td>
        </tr>
</table>
<div>
    Remarks: <br/>
    <?=nl2br($aPOR['remark'])?>
</div> 

<table cellspacing="0" cellpadding="5" align="center" width="98%" style="border:none; text-align:center; margin-top:10px;" class="summary">
    <tr>
        <td>Prepared by:<p>
		<?=$ADMIN['name']?><br /><?=date('m/d/Y g:ia')?></p></td>
        <td>Checked By:<p>
            <input type="text" class="line_bottom" /><br>&nbsp;</p></td>
        <td>Approved By:<p>
            <input type="text" class="line_bottom" /><br>&nbsp;</p></td>
        <td>Approved for Payment:<p>
        <input type="text" class="line_bottom" /><br>&nbsp;</p></td>
    </tr>
</table>

