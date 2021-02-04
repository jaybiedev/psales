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
<div colspan="6" style="text-align:center;"><?=$SYSCONF['BUSINESS_NAME']?><br />SRR / ITEM<br />As of <?=$mfrom_date?> to <?=$mto_date?></div>
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

$aSrrRep =null;	
$aSrrRep = array();
$q = "select 
	rr_header.account_id,
	account.account,
	rr_header.rr_header_id,
	rr_header.disc1,
	rr_header.disc1_type,	
	rr_header.disc2,
	rr_header.disc2_type,	
	rr_header.disc3,
	rr_header.disc3_type,	
	rr_header.tax_add,
	rr_detail.freight_case,
	rr_header.date,
	rr_detail.cost1, 
	rr_detail.cost2,
	rr_detail.cost3,
	rr_detail.case_qty,
	rr_detail.unit_qty,
	stock.fraction3,
	stock.markup,
	'RR' as type
from
	rr_header,
	rr_detail,
	stock,
	account
where
	rr_header.rr_header_id = rr_detail.rr_header_id and 
	account.account_id = rr_header.account_id and
	stock.stock_id=rr_detail.stock_id and 
	rr_header.status != 'C' and
	rr_header.date>='$mfrom_date' and
	rr_header.date<='$mto_date' and 
	rr_detail.stock_id = '$r->stock_id'";
	
$qqr = @pg_query($q) or message1("Error Querying SRR History....".pg_errormessage().$q);		

while ($rr = @pg_fetch_assoc($qqr))
{
$aSrrRep[] = $rr;
}

$q = "select 
	cp_header.account_id,
	cp_header.cp_header_id as rr_header_id,
	cp_header.date,
	cp_header.admin_id,
	cp_header.reference,
	cp_detail.cost1, 
	cp_detail.oldprice1,
	cp_detail.newprice1,
	stock.fraction3,
	stock.markup,
	'CP' as type,
	admin.name as account
from
	cp_header,
	cp_detail,
	stock,
	admin
where
	cp_header.cp_header_id = cp_detail.cp_header_id and 
	admin.admin_id = cp_header.admin_id and
	stock.stock_id=cp_detail.stock_id and 
	cp_header.status != 'C' and
	cp_header.date>='$mfrom_date' and
	cp_header.date<='$mto_date' and 
	cp_detail.stock_id = '$r->stock_id' ";
	
$qqr = @pg_query($q) or message1("Error Querying Change Price History....".pg_errormessage().$q);		

while ($rr = @pg_fetch_assoc($qqr))
{
	$aSrrRep[] = $rr;
}

$atemp = null;
$atemp = array();
foreach ($aSrrRep as $temp)
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
<table width="90%" border="0" cellpadding="0" cellspacing="1">
  <tr > 
    <td colspan="14" >Item [ 
      <?= $r->barcode;?>
      ] 
      <?= $stock;?>
    </td>
  </tr>
  <!--<tr> 
    <td colspan="14" nowrap ><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
      <img src="../graphics/bluelist.gif" width="16" height="17"> Report Preview</strong></font></td>
  </tr> -->
  <tr> 
    <th width="3%" height="20" rowspan="2" align="center" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></th>
    <th width="6%" rowspan="2" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Date</font></th>
    <th width="6%" rowspan="2" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Ref. 
      No.</font></th>
    <th width="20%" rowspan="2" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Particulars</font></th>
    <th width="7%" rowspan="2" align="center" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Case<br>
      Cost</font></th>
    <th width="7%" rowspan="2" align="center" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Unit<br>
      Cost</font></th>
    <th width="7%" rowspan="2" align="center" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Discount</font></th>
    <th width="7%" rowspan="2" align="center" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Case<br>
      Freight</font></th>
    <th width="7%" rowspan="2" align="center" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Quantity</font></th>
    <th width="7%" rowspan="2" align="center" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Net 
      <br>
      CaseCost</font></th>
    <th width="7%" rowspan="2" align="center" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Net<br>
      UnitCost</font></th>
    <th width="4%" rowspan="2" align="center" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Markup<br>
      (%) </font></th>
    <th width="12%" colspan="2" align="center" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">SRP</font></th>
  </tr>
  <tr> 
    <th align="center" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Unit</font></th>
    <th align="center" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Case</font></th>
  </tr>
<?
$c=0;

while (list ($key, $val) = each ($atemp))
{
	$temp=$aSrrRep[$key];
  
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
	?>
  <tr bgcolor="<?= $bgColor1;?>" onMouseOver="bgColor='<?= $bgColor2;?>'" onMouseOut="bgColor='<?= $bgColor1;?>'"> 
	<td nowrap style="text-align:center;"> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
	  <?= $c;?>
	  .</font></td>
	<td nowrap> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
	  <?= ymd2mdy($temp['date']);?>
	  </font></td>
	<td nowrap> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
	  <?= str_pad($temp['rr_header_id'],8,'0',STR_PAD_LEFT);?>
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
	<td align="right" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
	  <?= number_format($net_cost3,2);?>
	  </font></td>
	<td align="right" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
	  <?= number_format($net_cost1,2);?>
	  </font></td>
	<td align="right" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
	  <?= $r->markup;?>
	  </font></td>
	<td align="right" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
	  <?= number_format($mprice1,2);?>
	  </font></td>
	<td align="right" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
	  <?= number_format($mprice1*$fraction3,2);?>
	  </font></td>
  </tr>
  <?
  }
  ?>
</table>