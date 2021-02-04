<?
if (!chkRights2('raffle','mview',$ADMIN['admin_id']))
{
	message('You have [ NO ] permission to access Setup Raffle ...');
	exit;
}

		if (!isset($_SESSION['aRaffle'])) 
		{ 
   			$_SESSION['aRaffle'] = array(); 
			$aRaffle = null;
			$aRaffle= array();
		} 

$fields = array('raffle', 'date_from', 'date_to', 'raffle_modulu', 'autoprint', 'enable');
						
if (!in_array($p1,array(null,'Edit','Load')))
{
	for ($c=0;$c<count($fields);$c++)
	{
		if (substr($fields[$c],0,4) == 'date')
		{
			$aRaffle[$fields[$c]] = mdy2ymd($_REQUEST[$fields[$c]]);
		}
		else
		{
			$aRaffle[$fields[$c]] = $_REQUEST[$fields[$c]];
		}
		if ($aRaffle[$fields[$c]] == '' && !in_array($fields[$c], array('raffle','date_from','date_to','enable')))
		{
			$aRaffle[$fields[$c]] = 0;
		}
	}
}


if ($p1== 'Ok' && ($aRaffle['date_from'] == '--' || $aRaffle['date_to'] == '--'))
{
	message1(" No Date(s) specified...");
}
elseif ($p1 == 'Ok' && $aRaffle['raffle_modulu']*1 == '0')
{
	message1(" No Points Modulu specified...");
}
elseif ($p1 == 'Edit' && $id!='')
{
	$q = "select * from raffle where raffle_id ='$id'";
	$qr =@pg_query($q) or message1(pg_errormessage().$q);
	if ($qr)
	{
		$aRaffle = null;
		$aRaffle = array();
		$aRaffle = @pg_fetch_assoc($qr);
	}
}
elseif ($p1== 'Ok')
{

	if ($aRaffle['raffle_id'] == '')
	{
		$q = "select * from raffle where date_from = '".$aRaffle['date_from']."'";
		$qr = @pg_query($q) or message1(pg_errormessage().$q);
		if (@pg_num_rows($qr) > 0)
		{
			message1('<br> Date Range Conflicts with Another Raffle Period....<br><br>');
		}
		else
		{
			$q = "insert into raffle (raffle, date_from, date_to, raffle_modulu, autoprint, enable,admin_id) 
								values ('".$aRaffle['raffle']."', '".$aRaffle['date_from']."', '".$aRaffle['date_to']."', 
								'".$aRaffle['raffle_modulu']."', '".$aRaffle['enable']."', '".$aRaffle['autoprint']."','".$ADMIN['admin_id']."') ";
			$qr = @pg_query($q) or message1(pg_errormesage().$q);
		}

	}
	else
	{
		$q = " update raffle set raffle='".$aRaffle['raffle']."', 
						date_from='".$aRaffle['date_from']."', 
						date_to='".$aRaffle['date_to']."', 
						raffle_modulu='".$aRaffle['raffle_modulu']."', 
						autoprint='".$aRaffle['autoprint']."',
						enable='".$aRaffle['enable']."', 
						admin_id='".$ADMIN['admin_id']."'
					where
						raffle_id='".$aRaffle['raffle_id']."'";
			$qr = @pg_query($q) or message1(pg_errormesage().$q);

	}
	$aRaffle=null;
	$aRaffle=array();
}
?> 
<form name="f1"  id="f1" method="post" action="">
  <table width="80%" border="0" cellspacing="1" cellpadding="1">
    <tr background="../graphics/table_horizontal.PNG"> 
      <td colspan="10"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Setup 
        E-Raffle</font></td>
    </tr>
    <tr> 
      <td colspan="2"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Raffle 
        Description<br>
        <input name="raffle" type="text" id="raffle" value="<?= $aRaffle['raffle'];?>">
        </font></td>
      <td width="13%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">From<br>
        <b> 
        <input name="date_from" type="text" id="date_from" value="<?= ymd2mdy($aRaffle['date_from']);?>" size="10" maxlength="10" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" onKeypress="if(event.keyCode==13) {document.getElementById('invoice').focus();return false;}">
        <font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"><img src="../graphics/dwn-arrow-grn.gif" width="12" height="12" onClick="popUpCalendar(this, f1.date_from, 'mm/dd/yyyy')"></font> 
        </b> </font></td>
      <td width="13%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">To<br>
        <b> 
        <input name="date_to" type="text" id="date_to" value="<?= ymd2mdy($aRaffle['date_to']);?>" size="10" maxlength="10" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" onKeypress="if(event.keyCode==13) {document.getElementById('invoice').focus();return false;}">
        <font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"><img src="../graphics/dwn-arrow-grn.gif" width="12" height="12" onClick="popUpCalendar(this, f1.date_to, 'mm/dd/yyyy')"></font> 
        </b> </font></td>
      <td width="14%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Points/Ticket<br>
        <input name="raffle_modulu" type="text" id="raffle_modulu" value="<?= $aRaffle['raffle_modulu'];?>" size="6" maxlength="6">
        <br>
        </font></td>
      <td width="5%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Print<br>
        <?=lookUpAssoc('autoprint',array('Yes'=>'Y','No'=>'N'),$aRaffle['autoprint']);?>
        </font></td>
      <td colspan="4"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Enable<br>
        <?=lookUpAssoc('enable',array('Yes'=>'Y','No'=>'N'),$aRaffle['enable']);?>
        <input name="p1" type="submit" id="p1" value="Ok">
        </font></td>
    </tr>
    <tr bgcolor="#CCCCCC"> 
      <td colspan="10"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Saved 
        Items</font></td>
    </tr>
    <?
	$q = "select * from raffle  order by date_from desc ";
	$qr = @pg_query($q) or message(pg_errormessage().$q);
	$c=0;
	while ($r = @pg_fetch_object($qr))
	{
		$c++;
		
		$qq = "select sum(points_convert) as points_convert, sum(raffle_count) as raffle_count
						from
							raffleledger
						where
							raffle_id ='$r->raffle_id' and
							enable='Y'";
		$qqr = @pg_query($qq) or message1(pg_errormessage().$qq);
		$rr = @pg_fetch_object($qqr);
	?>
    <tr bgColor=<?=($c%2==1 ? '#FFFFFF' : '#EFEFEF');?>> 
      <td width="5%" align="right" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $c;?>
        . </font></td>
      <td width="25%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=raffle.setup&p1=Edit&id=<?= $r->raffle_id;?>"> 
        <?=$r->raffle;?>
        </a></font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= ymd2mdy($r->date_from);?>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= ymd2mdy($r->date_to);?>
        </font></td>
      <td align="center" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $r->raffle_modulu;?>
        </font></td>
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= ($r->autoprint == 'Y' ?'Prints Coupon':'No Printing');?>
        </font></td>
      <td width="6%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <?= $r->enable;?>
        </font></td>
      <td width="7%">&nbsp;<font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
        </font></td>
      <td width="7%" nowrap>&nbsp;<font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $rr->points_convert.' Pts ';?>
        </font></td>
      <td width="5%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $rr->raffle_count.'  Coupons ';?>
        </font></td>
    </tr>
    <?
	}
	?>
  </table>
</form>
