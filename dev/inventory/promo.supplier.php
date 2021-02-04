<script>
function vFrom(o)
{
	if (o == 'date')
	{
		if (document.getElementById('date_to').value == '')
		{
			document.getElementById('date_to').value = document.getElementById('date_from').value
		}
	}	
	else if (o == 'barcode')
	{
		if (document.getElementById('barcode_to').value == '')
		{
			document.getElementById('barcode_to').value = document.getElementById('barcode_from').value
		}
	}	
	else if (o == 'category_id')
	{
		if (document.getElementById('category_id_to').value == '')
		{
			document.getElementById('category_id_to').value = document.getElementById('category_id_from').value
		}
	}	
	return false
}
</script>
<?
if (!session_is_registered('aPS'))
{
	session_register('aPS');
	$aPS = null;
	$aPS = array();
}

$fields = array('account_id','date_from', 'date_to','include_net', 'category_id_from', 'category_id_to', 'sdisc' ,'cdisc');

if (!in_array($p1 , array('Load','Edit')))
{
	for ($c = 0; $c<count($fields);$c++)
	{
		if (substr($fields[$c],0,4) == 'date')
		{
			$aPS[$fields[$c]] = mdy2ymd($_REQUEST[$fields[$c]]);
		}
		else
		{
			$aPS[$fields[$c]] = $_REQUEST[$fields[$c]];
			if ($aPS[$fields[$c]] == '')
			{
				$aPS[$fields[$c]] = 0;
			}
		}
	}
	if ($aPS['category_id_from']  > 0)
	{
		$aPS['category_code_from'] = lookUpTableReturnValue('x','category','category_id', 'category_code', $aPS['category_id_from']);
	}
	else
	{
		$aPS['category_code_from'] = '';
	}
	if ($aPS['category_id_to']  > 0)
	{
		$aPS['category_code_to'] = lookUpTableReturnValue('x','category','category_id', 'category_code', $aPS['category_id_to']);
	}
	else
	{
		$aPS['category_code_to'] = '';
	}

}
if ($p1 == 'Edit' && $id != '')
{
	$aPS = null;
	$aPS = array();
	$q = "select * from promo_header where promo_header_id = '$id'";
	$qr = @pg_query($q) or message1(pg_errormessage());
	$r = @pg_fetch_assoc($qr);
	$aPS = $r;
}
elseif ($p1 == 'Ok')
{
	$generated=date('Y-m-d');
	
	if ($aPS['promo_header_id'] == '')
	{
		$q = "insert into promo_header (admin_id, generated, all_items, category_code_from, category_code_to  ";
		$qq .= ") values ('".$ADMIN['admin_id']."', '$generated' , 'Y', '".$aPS['category_code_from']."', '".$aPS['category_code_to']."'";
		for ($c=0;$c<count($fields);$c++)
		{
			$q .= ",".$fields[$c];
			$qq .= ",'".$aPS[$fields[$c]]."'";
		}
		$q .= $qq.")";
		$qr = @pg_query($q) or message1(pg_errormessage());
		if ($qr && @pg_affected_rows($qr) > 0)
		{
			message1 ("Supplier Promotional Saved...");
		}
	}
	else
	{
		$q = "update promo_header set  all_items = 'Y', category_code_from='".$aPS['category_code_from']."',  category_code_to='".$aPS['category_code_to']."'";
		for ($c=0;$c<count($fields);$c++)
		{
				$q .= ",".$fields[$c]."='".$aPS[$fields[$c]]."'";
		}
		$q .= " where promo_header_id = '".$aPS['promo_header_id']."'";
		$qr = @pg_query($q) or message1(pg_errormessage());
		if ($qr && @pg_affected_rows($qr) > 0)
		{
			message1 ("Supplier Promotional Updated...");
		}
	}
	$aPS = null;
	$aPS = array();
	$aPS['include_net'] = 'N';

}
elseif ($p1 == 'ConfirmDisable')
{
	$ids = implode(',',$mark);
	$q = "update promo_header set enable='N' where promo_header_id in ($ids)";
	pg_query($q);
}
elseif ($p1 == 'ConfirmEnable')
{
	$ids = implode(',',$mark);
	$q = "update promo_header set enable='Y' where promo_header_id in ($ids)";
	@pg_query($q);
}

?>
<form name="f1" id="f1" method="post" action="">
  <table width="97%" border="0" align="center" cellpadding="0" cellspacing="1">
    <tr bgcolor="#C7E9E3"> 
      <td colspan="4"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <strong>::: Promotional By Supplier</strong></font></td>
    </tr>
    <tr> 
      <td width="16%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Supplier 
        Code From</font></td>
      <td width="34%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <select name='account_id' id='account_id' style="width:250px"  onKeypress="if(event.keyCode==13) {document.getElementById('date_from').focus();return false;}">
          <option value=''>Select Supplier -- </option>
          <?
  		foreach ($aSUPPLIER as $stemp)
		{
			if ($stemp['account_id'] == $aPS['account_id'])
			{
				echo "<option value=".$stemp['account_id']." selected>".substr($stemp['account_code'],0,6)." ".$stemp['account']."</option>";
			}
			else
			{
				echo "<option value=".$stemp['account_id'].">".substr($stemp['account_code'],0,6)." ".$stemp['account']."</option>";
			}
		}
		?>
        </select>
        </font></td>
      <td width="15%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Category 
        From</font></td>
      <td width="35%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <select name='category_id_from' id="category_id_from" style="width:235px"  onKeypress="if(event.keyCode==13) {document.getElementById('category_id_to').focus();return false;}"  onBlur="vFrom('category_id')">
          <option value=''>All Categories -- </option>
          <?
		foreach ($aCATEGORY as $ctemp)
		{
			if ($ctemp['category_id'] == $aPS['category_id_from'])
			{
				echo "<option value=".$ctemp['category_id']." selected>".substr($ctemp['category_code'],0,6)." ".$ctemp['category']."</option>";
			}
			else
			{
				echo "<option value=".$ctemp['category_id']." >".substr($ctemp['category_code'],0,6)." ".$ctemp['category']."</option>";
			}
		}
		?>
        </select>
        </font></td>
    </tr>
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Promo Date 
        From</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="date_from" type="text" id="date_from" value="<?= ymd2mdy($aPS['date_from']);?>" size="10" onBlur="IsValidDate(this,'MM/dd/yyyy');vFrom('date')" onKeyUp="setDate(this,'MM/dd/yyyy','en')"  onKeypress="if(event.keyCode==13) {document.getElementById('date_to').focus();return false;}">
        <img src="../graphics/dwn-arrow-grn.gif" onclick="popUpCalendar(this, f1.date_from, 'mm/dd/yyyy')"> 
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Category 
        To</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <select name='category_id_to' id="category_id_to" style="width:235px"  onKeypress="if(event.keyCode==13) {document.getElementById('sdisc').focus();return false;}" >
          <option value=''>All Categories -- </option>
          <?
		foreach ($aCATEGORY as $ctemp)
		{
			if ($ctemp['category_id'] == $aPS['category_id_to'])
			{
				echo "<option value=".$ctemp['category_id']." selected>".substr($ctemp['category_code'],0,6)." ".$ctemp['category']."</option>";
			}
			else
			{
				echo "<option value=".$ctemp['category_id']." >".substr($ctemp['category_code'],0,6)." ".$ctemp['category']."</option>";
			}
		}
		?>
        </select>
        </font></td>
    </tr>
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Promo Date 
        To</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="date_to" type="text" id="date_to" value="<?= ymd2mdy($aPS['date_to']);?>" size="10" onBlur="IsValidDate(this,'MM/dd/yyyy');" onKeyUp="setDate(this,'MM/dd/yyyy','en')"  onKeypress="if(event.keyCode==13) {document.getElementById('category_id_from').focus();return false;}">
        <img src="../graphics/dwn-arrow-grn.gif" onclick="popUpCalendar(this, f1.date_to, 'mm/dd/yyyy')"> 
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Supplier 
        Discount</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="sdisc" type="text" id="sdisc"  onKeypress="if(event.keyCode==13) {document.getElementById('cdisc').focus();return false;}" value="<?= $aPS['sdisc'];?>" size="7">
        </font></td>
    </tr>
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Include 
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= looKUpAssoc('include_net', array('Regular Price'=>'N','Net Items'=>'Y','All Items'=>'A'), $aPS['include_net']);?>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Company 
        Discount</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="cdisc" type="text" id="cdisc"  onKeypress="if(event.keyCode==13) {document.getElementById('Ok').focus();return false;}" value="<?= $aPS['cdisc'];?>" size="7">
        <input name="p1" type="submit" id="Ok" value="Ok">
        </font></td>
    </tr>
  </table>  
  <table width="97%" border="0" align="center" cellpadding="1" cellspacing="1" bgcolor="#EFEFEF">
    <tr background="../graphics/table0_horizontal.PNG"> 
      <td colspan="12"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Detail 
        Entries</strong></font></td>
    </tr>
    <tr bgcolor="#DADADA"> 
      <td width="6%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
      <td width="4%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Code</font></strong></td>
      <td width="33%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Supplier 
        name </font></strong></td>
      <td colspan="2"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Category</font></strong></td>
      <td width="4%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">From</font></strong></td>
      <td width="5%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">To</font></strong></td>
      <td width="6%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">%SDisc</font></strong></td>
      <td width="6%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">%CDisc</font></strong></td>
      <td width="6%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Include</font></strong></td>
      <td width="7%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Encoded</font></strong></td>
      <td width="5%" align="center">&nbsp;</td>
    </tr>
    <?
	$today = date('Y-m-d');
	$q = "select 
						promo_header.generated,
						promo_header.promo_header_id,
						promo_header.date_from,
						promo_header.date_to,
						promo_header.sdisc,
						promo_header.cdisc,
						promo_header.account_id, 
						promo_header.include_net,
						promo_header.category_id_from,
						promo_header.category_id_to,
						promo_header.category_code_from,
						promo_header.category_code_to,
						promo_header.enable,
						admin.username,
						account.account_code,
						account.account
							 
				from 
						promo_header, 
						account, 
						admin
				 where
				 		account.account_id = promo_header.account_id and 
						admin.admin_id = promo_header.admin_id and 
						date_to >= '$today' and
						all_items = 'Y'
				order by
						date_from";
	$qr = @pg_query($q) or message (pg_errormessage());
	
	$ctr=0;
	while ($r = @pg_fetch_object($qr))
	{
		$ctr++;
	?>
    <tr bgColor="<?= ($r->enable== 'N' ? '#FFCCFF' : '#FFFFFF');?>"> 
      <td align="right" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $ctr;?>
        . 
        <input type="checkbox" name="mark[]" value="<?= $r->promo_header_id;?>">
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href="?p=promo.supplier&p1=Edit&id=<?=$r->promo_header_id;?>"> 
        <?= $r->account_code;?>
        </a> </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=promo.supplier&p1=Edit&id=<?=$r->promo_header_id;?>"> 
        <?= $r->account;?>
        </a></font></td>
      <td width="6%" align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=promo.supplier&p1=Edit&id=<?=$r->promo_header_id;?>">
        <?= $r->category_code_from;?>
        </a></font></td>
      <td width="5%" align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=promo.supplier&p1=Edit&id=<?=$r->promo_header_id;?>">
        <?= $r->category_code_to;?>
        </a></font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= ymd2mdy($r->date_from);?>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= ymd2mdy($r->date_to);?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= round($r->sdisc,0);?>
        % </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= round($r->cdisc,0);?>
        % </font></td>
      <td align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= ($r->include_net == 'N'? 'Reg.'  : ($r->include_net == 'Y' ? 'Net'  : 'All'));?>
        </font></td>
      <td><font size="2"> 
        <?= $r->username;?>
        </font></td>
      <td> <font size="1"> 
        <?= ($r->enable == 'N'  ?  'Disabled' :  "<a href='?p=promo.supplier&p1=displayitem&id=$r->promo_header_id'>Browse</a>");?>
        </font></td>
    </tr>
    <?
	}
	?>
    <tr> 
      <td colspan="12"><input name="p1" type="button" value="Disable Checked" onClick="if (confirm('Are you sure to DISABLE checked items?') ) {document.getElementById('f1').action = '?p=promo.supplier&p1=ConfirmDisable'; document.getElementById('f1').submit()}"> 
        <input name="p1" type="button" id="p1" value="Enable Checked"  onClick="if (confirm('Are you sure to enable checked items?') ) {document.getElementById('f1').action = '?p=promo.supplier&p1=ConfirmEnable'; document.getElementById('f1').submit()}"></td>
    </tr>
  </table>
  <?
  	if ($p1 == 'displayitem')
	{
		include_once('promo.supplier.item.php');
	}
  ?>
</form>
