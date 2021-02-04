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
$rr_header_id	= $_REQUEST['id'];
$result = @pg_query("select * from rr_header where rr_header_id = '$rr_header_id'");
$r = $aRR = @pg_fetch_assoc($result);
$po_header_id = $r['po_header_id'];
$invoice = $r['invoice'];
$date = $r['date'];
$invoice_date = $r['invoice_date'];


$account_code = lookUpTableReturnValue('','account','account_id','account_code',$r['account_id']);
$account      = lookUpTableReturnValue('','account','account_id','account',$r['account_id']);
$address      = lookUpTableReturnValue('','account','account_id','address',$r['account_id']);
$terms        = lookUpTableReturnValue('','account','account_id','terms',$r['account_id']);




$gross_amount		= $r['gross_amount'];
$discount_amount	= $r['discount_amount'];
$net_amount			= $r['net_amount'];
$freight_amount		= $r['freight_amount'];
$tax_amount			= $r['tax_amount'];
$received_by		= $r['receivedby'];


/*echo "<pre>";
print_r($r);
echo "</pre>";*/

?>
<table>
	
	<tr>
    	<td width="40%"><?=$SYSCONF['BUSINESS_NAME']?></td>
       <td colspan="3">STOCKS RECEIVING REPORT</td>
        

    </tr>
    <tr>
    	<td><?=$SYSCONF['BUSINESS_ADDR']?></td>
        <td width="50">SRR No.</td>
        <td width="80">:<?=str_pad($rr_header_id,8,0,STR_PAD_LEFT)?></td>
	 <td nowrap width="80">Date Received</td>
        <td>:<?=$date?></td>
    </tr>
    <tr>
    	<td>&nbsp;</td>
        <td>PO No.</td>
        <td>:<?=str_pad($po_header_id,8,0,STR_PAD_LEFT)?></td>
	 <td>Terms</td>
        <td>:<?=$terms?></td>
    </tr>
    <tr>
    	<td>[<?=$account_code?>] <?=$account?></td>
        <td nowrap>Inv No.</td>
        <td>:<?=$invoice?></td>
	<td>Invoice Date</td>
        <td>:<?=$invoice_date?></td>

    </tr>
    <tr>
    	<td><?=$address?></td>        
    </tr>
</table>
<table cellpadding="1">
	<tr>
    	<th width="10%">Received Quantity</th>
        <th width="11%">Barcode</th>
        <th>Item Description</th>
        <th width="11%" style="text-align:right;">UnPrice</th>
        <th width="10%" style="text-align:right;">UnCost</th>
        <th width="9%" style="text-align:right;">Amount</th>
    </tr>	
    <?
	$result = @pg_query("select * from rr_detail as d where rr_header_id = '$rr_header_id'");
	while($r = @pg_fetch_assoc($result)){
		$stock_id = $r['stock_id'];
		$barcode	= $r['barcode'];
		$stock = lookUpTableReturnValue('','stock','stock_id','stock',$stock_id);
		$unit1 = lookUpTableReturnValue('','stock','stock_id','unit1',$stock_id);
		$unit3 = lookUpTableReturnValue('','stock','stock_id','unit3',$stock_id);
		
		$price1 = lookUpTableReturnValue('x','stock','stock_id','price1',$stock_id);
		
		$unit_qty	= $r['unit_qty'];
		$case_qty	= $r['case_qty'];
		
		$cost1 	= $r['cost1'];
		$cost3	= $r['cost3'];
		$amount	= $r['amount'];

        $cost = $r['unit_qty'] > $r['case_qty'] ? $r['cost1'] : $r['cost3'];

		$qty_display = NULL;
		if( $unit_qty > 0 )	{
			$qty_display = number_format($unit_qty,2).' '.$unit1;
		} 
		if( $case_qty > 0) {
			$qty_display.= ' '.	number_format($case_qty,2).' '.$unit3;
		}
		
    ?>
    	<tr>
        	<td><?=$qty_display?></td>
            <td><?=$barcode?></td>
            <td><?=$stock?></td>
          <td style="text-align:right;"><?=number_format($price1,2,'.',',')?></td>
          <td style="text-align:right;"><?=number_format($cost,2,'.',',')?></td>
          <td style="text-align:right;"><?=number_format($amount,2,'.',',')?></td>
        </tr>
    <? } ?>
    
        <tr class="border-top">
        	<td>&nbsp;</td>
              <td colspan="4" style="text-align:right;">GROSS AMOUNT</td>
          <td style="text-align:right;"><?=number_format($gross_amount,2,'.',',')?></td>
        </tr>
        <tr>
        	<td>&nbsp;</td>
              <td colspan="4" style="text-align:right;">DISCOUNT</td>
          <td style="text-align:right;"><?=number_format($discount_amount,2,'.',',')?></td>
        </tr>
        <tr>
        	<td>&nbsp;</td>
            <td colspan="4" style="text-align:right;" >TAX AMOUNT</td>
          <td style="text-align:right;"><?=number_format($tax_amount,2,'.',',')?></td>
        </tr>
        <tr>
        	<td>&nbsp;</td>
            <td colspan="4" style="text-align:right;" >SUB-TOTAL</td>
            <?
            if( $aRR['tax_exclusive'] ){
                $subtotal = $gross_amount + $tax_amount - $discount_amount;
            } else {
                $subtotal = $gross_amount - $discount_amount;
            }
            ?>
            <td style="text-align:right;"><?=number_format($subtotal,2,'.',',')?></td>
        </tr>
        <tr>
        	<td>&nbsp;</td>
              <td colspan="4" style="text-align:right;">FREIGHT AMOUNT</td>
          <td style="text-align:right;"><?=number_format($freight_amount,2,'.',',')?></td>
        </tr>
        <tr>
        	<td>&nbsp;</td>
              <td colspan="4" style="text-align:right;" >NET AMOUNT</td>
          <td style="text-align:right;"><?=number_format($net_amount,2,'.',',')?></td>
        </tr>
</table>

<div>
    Remarks : <br/>
    <?=nl2br($aRR['remark'])?>
</div>

<table cellspacing="0" cellpadding="5" align="center" width="98%" style="border:none; text-align:center; margin-top:10px;" class="summary">
    <tr>
        <td>Prepared by:<p>
		<?=$ADMIN['name']?><br /><?=date('m/d/Y g:ia')?></p></td>
        <td>Received By:<p>
            <input type="text" class="line_bottom" value='<?=$received_by?>' /><br>&nbsp;</p></td>
        <td>Approved By:<p>
            <input type="text" class="line_bottom" /><br>&nbsp;</p></td>
        <td>Approved for Payment:<p>
        <input type="text" class="line_bottom" /><br>&nbsp;</p></td>
    </tr>
</table>

