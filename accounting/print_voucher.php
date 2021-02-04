<?
include_once('../lib/library.php');
include_once('../lib/dbconfig.php');
include_once('../lib/connect.php');
include_once('../var/system.conf.php');
include_once('../lib/library.js.php');

?>

<?

function getReferences($gltran_header_id,$type="rr"){
	$rr_reference = array();
	$result = @pg_query("select * from gltran_reference where gltran_header_id = '$gltran_header_id' and header = 'rr_header_id'");
	$net = 0;
	while($r = @pg_fetch_assoc($result)){
		$header_id 	= $r['header_id'];
		$header		= $r['header'];
		
		$tmp = array();
		
		$reference = "SRR# ".str_pad($header_id,7,0,STR_PAD_LEFT);
		$net_amount = lookUpTableReturnValue('','rr_header','rr_header_id','net_amount',$header_id);
		$date = lookUpTableReturnValue('','rr_header','rr_header_id','date',$header_id);
		$invoice = lookUpTableReturnValue('','rr_header','rr_header_id','invoice',$header_id);
		
		$net += $net_amount;
		
		$tmp['reference'] = $reference;
		$tmp['date'] = date("m/d/Y",strtotime($date));
		$tmp['net_amount'] = $net_amount;
		$tmp['invoice'] = $invoice;
		$rr_reference[] = $tmp;
	}
	
	$por_reference = array();
	$result = @pg_query("select * from gltran_reference where gltran_header_id = '$gltran_header_id' and header = 'por_header_id'");
	while($r = @pg_fetch_assoc($result)){
		$header_id 	= $r['header_id'];
		$header		= $r['header'];
		
		$tmp = array();
		
		$reference = "POR# ".str_pad($header_id,7,0,STR_PAD_LEFT);
		$net_amount = lookUpTableReturnValue('','por_header','por_header_id','net_amount',$header_id);
		$date = lookUpTableReturnValue('','por_header','por_header_id','date',$header_id);
		$invoice = lookUpTableReturnValue('','por_header','por_header_id','reference',$header_id);
		
		$net -= $net_amount;
		
		
		$tmp['reference'] = $reference;
		$tmp['date'] = date("m/d/Y",strtotime($date));
		$tmp['net_amount'] = $net_amount;
		$tmp['invoice'] = $invoice;
		$por_reference[] = $tmp;
	}
	
	if($type == "rr"){
		return $rr_reference;	
	}else if($type == "por"){
		return $por_reference;	
	}else{
		return $net;	
	}
}

?>
<style type="text/css">
	*{ font-family:Arial, Helvetica, sans-serif; font-size:11px; letter-spacing: 6px;}
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
$gltran_header_id	= $_REQUEST['id'];
$result = @pg_query("select * from gltran_header where gltran_header_id = '$gltran_header_id'");
$r = @pg_fetch_assoc($result);
$date = $r['date'];

$account_code = lookUpTableReturnValue('','account','account_id','account_code',$r['account_id']);
$account = lookUpTableReturnValue('','account','account_id','account',$r['account_id']);
$address = lookUpTableReturnValue('','account','account_id','address',$r['account_id']);
$terms = lookUpTableReturnValue('','account','account_id','terms',$r['account_id']);


/**
echo "<pre>";
print_r($r);
echo "</pre>";/**/

?>
<div style="text-align:center; margin-bottom:20px;">
	<?=$SYSCONF['BUSINESS_NAME']?><br />
    DISBURSEMENT VOUCHER
</div>
<table cellpadding="1" style="width:49%; display:inline-table;">
	<tr>
    	<td>PAYEE:</td>
        <td>****<?=$account?>****</td>
        <td id="payment_amount" style="font-weight:bold;">P <?=number_format(getReferences($gltran_header_id,"net"),2,'.',',')?></td>
    </tr>
</table>

<table cellpadding="1" style="width:49%; display:inline-table; margin-bottom:10px;">
	<tr>
    	<td style="text-align: right;">VOUCHER #:</td>
        <td><?=str_pad($gltran_header_id,7,0,STR_PAD_LEFT)?></td>
    </tr>
    <tr>
    	<td style="text-align: right;">DATE:</td>
        <td><?=date("m/d/Y",strtotime($date))?></td>
    </tr>
    <tr>
    	<td style="text-align: right;">CHECK #:</td>
        <td><?=$r['mcheck']?></td>
    </tr>
    <tr>
    	<td style="text-align: right;">CHECK DATE:</td>
        <td><?=date("m/d/Y",strtotime($r['date_check']))?></td>
    </tr>
</table>


<table cellpadding="1">
	
	<? 
	$rr_net_amount = $por_net_amount = 0;
	foreach( getReferences($gltran_header_id,"rr") as $r ){ 
		$rr_net_amount += $r['net_amount'];
	?>
	<tr>
    	<td><?=$r['reference']?></td>
		<td><?=$r['invoice']?></td>
        <td><?=$r['date']?></td>
        <td style="width:20%;"></td>
        <td style="width:20%; text-align:right;"><?=number_format($r['net_amount'],2,'.',',')?></td>
    </tr>
    <? } ?>
    <tr>
    	<td></td>
        <td></td>
        <td></td>
		<td></td>
        <td style="border-top:1px solid #000; font-weight:bold; text-align:right;"><?=number_format($rr_net_amount,2,'.',',')?></td>
    </tr>
    
    <? 
	$a = getReferences($gltran_header_id,"por");
	if(!empty($a)){
	foreach( getReferences($gltran_header_id,"por") as $r ){ 
		$por_net_amount += $r['net_amount'];
	?>
	<tr>
    	<td><?=$r['reference']?></td>
		<td><?=$r['invoice']?></td>
        <td><?=$r['date']?></td>
        <td style="width:20%; text-align:right;"><?=number_format(0-$r['net_amount'],2,'.',',')?></td>
        <td style="width:20%;"></td>
    </tr>
    <? } ?>
    <tr>
    	<td></td>
        <td></td>
		<td></td>
        <td style="text-align:right;">LESS : DEBIT MEMO TOTAL</td>
        <td style="font-weight:bold; text-align:right;"><?=number_format(0-$por_net_amount,2,'.',',')?></td>
    </tr>
	<? } ?>
    
    <tr>
    	<td></td>
        <td></td>
		<td></td>
        <td></td>
        <td style="border-bottom:3px double #000; font-weight:bold; text-align:right;"><?=number_format($rr_net_amount - $por_net_amount,2,'.',',')?></td>
    </tr>
</table>

<table cellspacing="0" cellpadding="5" align="center" width="98%" style="border:none; text-align:center; margin-top:10px;" class="summary">
    <tr>
        <td>Prepared by:<p>
		<?=$ADMIN['name']?><br /><?=date('m/d/Y g:ia')?></p></td>
        
        <td>Approved By:<p>
            <input type="text" class="line_bottom" /><br>&nbsp;</p></td>
        <td>Received By:<p>
        <input type="text" class="line_bottom" /><br>&nbsp;</p></td>
    </tr>
</table>

