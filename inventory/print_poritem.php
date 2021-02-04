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
	}
</style>
<script type="text/javascript">
	function printPage() { print(); } //Must be present for Iframe printing
</script>


<?
$mto_date 	= mdy2ymd($_REQUEST['to_date']);
$mfrom_date = mdy2ymd($_REQUEST['from_date']);
$c_id 		= $_REQUEST['c_id'];
?>
<div colspan="6" style="text-align:center;"><?=$SYSCONF['BUSINESS_NAME']?><br />POR / ITEM<br />As of <?=$mfrom_date?> to <?=$mto_date?></div>
<?
$q = "select 
			*
	 from 
			stock
	where 
			stock_id='$c_id'";
$qr = @pg_query($q) or message1('Error Querying Stock Master...'.pg_errormessage());
$r = @pg_fetch_object($qr);

if ($r->stock_description != '') $stock = $r->stock_description;
else $stock = $r->stock;

$aPORRep =null;	
$aPORRep = array();
$q = "select 
			por_header.account_id,
			account.account,
			por_header.por_header_id,
			por_header.rr_header_id,
			por_header.disc1,
			por_header.disc1_type,	
			por_header.disc2,
			por_header.disc2_type,	
			por_header.disc3,
			por_header.disc3_type,	
			por_header.tax_add,
			por_detail.freight_case,
			por_header.date,
			por_detail.cost1, 
			por_detail.cost2,
			por_detail.cost3,
			por_detail.case_qty,
			por_detail.unit_qty,
			stock.fraction3,
			stock.markup,
			'POR' as type
		from
			por_header,
			por_detail,
			stock,
			account
		where
			por_header.por_header_id = por_detail.por_header_id and 
			account.account_id = por_header.account_id and
			stock.stock_id=por_detail.stock_id and 
			por_header.status != 'C' and
			por_header.date>='$mfrom_date' and
			por_header.date<='$mto_date' and 
			por_detail.stock_id = '$r->stock_id'";
			
	$qqr = @pg_query($q) or message1("Error Querying SRR History....".pg_errormessage().$q);		
	
	while ($rr = @pg_fetch_assoc($qqr))
	{
		$aPORRep[] = $rr;
	}



	$atemp = null;
	$atemp = array();
	foreach ($aPORRep as $temp)
	{
		$temp1=$temp['date'];
		$atemp[]=$temp1;
	}
	
	if (count($atemp) > 0)
	{
		asort($atemp);
		$atemp1= array_reverse($atemp,true);
		$atemp = $atemp1;
		reset($atemp);
	}

?>
<div align="center"> 
<table width="90%" border="0" cellpadding="0" cellspacing="1">
  <tr > 
	<td height="22" colspan="14" >Item [ 
	  <?= $r->barcode;?>
	  ] 
	  <?= $stock;?>
	</td>
  </tr>
  <!--<tr background="../graphics/table_horizontal.PNG"> 
	<td height="22" colspan="13" nowrap bgcolor="#000033" background="../graphics/table_horizontal.PNG"><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
	  <img src="../graphics/bluelist.gif" width="16" height="17"> Report Preview</strong></font></td>
  </tr> -->
  <tr> 
	<th width="7%" height="20" align="center" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></th>
	<th width="8%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Date</font></th>
	<th width="4%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">POR. 
	  No.</font></th>
	<th width="4%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">SRR 
	  No. </font></th>
	<th width="8%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Invoice</font></th>
	<th width="30%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Particulars</font></th>
	<th width="8%" align="center" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Case<br>
	  Cost</font></th>
	<th width="8%" align="center" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Unit<br>
	  Cost</font></th>
	<th width="8%" align="center" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Discount</font></th>
	<th width="8%" align="center" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Case<br>
	  Freight</font></th>
	<th width="15%" align="center" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Quantity</font></th>
  </tr>
  <?
  $c=0;

while (list ($key, $val) = each ($atemp))
{
	$temp=$aPORRep[$key];
  
	$c++;
	$disc = '';
	$net_cost3 = $temp['cost3'];
	if ($temp['disc1'] > '0')
	{
		if($temp['disc1_type'] == 'P')
		{
			$disc = $temp['disc1'].'% ;';
			$net_cost3 = $net_cost3*(1 - $temp['disc1']/100);
		}
	}
	if ($temp['disc2'] > '0')
	{
		if($temp['disc2_type'] == 'P')
		{
			$disc .= $temp['disc2'].'% ;';
			$net_cost3 = $net_cost3*(1 - $temp['disc2']/100);
		}
	}
	if ($temp['disc3'] > '0')
	{
		if($temp['disc3_type'] == 'P')
		{
			$disc .= $temp['disc3'].'% ;';
			$net_cost3 = $net_cost3*(1 - $temp['disc3']/100);
		}
	}

	if ($temp['tax_add'] > 0)
	{
		$net_cost3 = $net_cost3*(1 + $temp['tax_add']/100);
	}		
	$net_cost3 += $temp['freight_case'];

	if ($temp['fraction3'] == 0) $fraction3 = 1;
	else $fraction3 = $temp['fraction3'];
	$net_cost1 = $net_cost3/$fraction3;

	if ($temp['newprice1'] == '')
	{
		$mprice1 = round2($net_cost1*(1 + $temp['markup']/100));	
	}
	else
	{
		$mprice1 = $temp['newprice1'];
	}
	
	if ($temp['type'] == 'CP')
	{
		$bgColor1 = '#DFFFFF';
		$bgColor2 = '#FFFF99';
	}
	else
	{
		$bgColor1 = '#FFFFFF';
		$bgColor2 = '#FFFFCC';
	}
	
	$q = "select invoice from rr_header where rr_header_id = '".$temp['rr_header_id']."'";
	$qrr = @pg_query($q);
	$rr = @pg_fetch_object($qrr);
	$invoice = $rr->invoice;
	?>
  <tr bgcolor="<?= $bgColor1;?>" onMouseOver="bgColor='<?= $bgColor2;?>'" onMouseOut="bgColor='<?= $bgColor1;?>'"> 
	<td style="text-align:center;"> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
	  <?= $c;?>
	  .</font></td>
	<td nowrap> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
	  <?= ymd2mdy($temp['date']);?>
	  </font></td>
	<td nowrap> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
	  <?= str_pad($temp['por_header_id'],8,'0',STR_PAD_LEFT);?>
	  </font></td>
	<td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
	  <?= str_pad($temp['rr_header_id'],8,'0',STR_PAD_LEFT);?>
	  </font></td>
	<td nowrap> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
	  <?= $invoice;?>
	  </font></td>
	<td nowrap> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
	  <?= ($temp['type'] == 'CP' ? 'CP:' : '').$temp['account'];?>
	  </font></td>
	<td align="right" nowrap> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
	  <?= number_format($temp['cost3'],2);?>
	  </font></td>
	<td align="right" nowrap> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
	  <?= number_format($temp['cost1'],2);?>
	  </font></td>
	<td align="right" nowrap> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
	  <?= $disc;?>
	  </font></td>
	<td align="right" nowrap> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
	  <?= number_format($temp['freight_case'],2);?>
	  </font></td>
	<td align="center" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
	  <?= $temp['case_qty']. ' : '.$temp['unit_qty'];?>
	  &nbsp;</font></td>
  </tr>
  <?
  }
  ?>
</table>