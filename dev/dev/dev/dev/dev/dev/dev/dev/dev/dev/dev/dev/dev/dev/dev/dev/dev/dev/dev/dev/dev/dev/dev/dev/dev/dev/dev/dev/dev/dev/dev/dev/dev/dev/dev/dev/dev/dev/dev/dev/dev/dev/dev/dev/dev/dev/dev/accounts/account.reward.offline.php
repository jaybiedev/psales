<script>
function vAmt(t)
{
	var amt = twoDecimals(t.value);
	t.value = amt;
}
</script>
<STYLE TYPE="text/css">
<!--
	.altText {
	background-color: #FFFFFF;
	font-family: verdana;
	border: #CCCCCC 1px solid;
	font-size: 12px;
	color: #000000
	} 
	
	.altNum {
	background-color: #FFFFFF;
	font-family: verdana;
	border: #CCCCCC 1px solid;
	font-size: 12px;
	color: #000000;
	text-align:right
	} 
	
	.altTextArea {
	background-color: #FFFFFF;
	font-family: verdana;
	border: #CCCCCC 1px solid;
	font-size: 12px;
	color: #000000
	} 
	
	SELECT {
	background-color: #FFFFFF;
	font-family: verdana;
	border: #CCCCCC 1px solid;
	font-size: 12px;
	color: #000000
	} 			

	.altBtn {
	background-color: #CFCFCF;
	font-family: verdana;
	border: #B1B1B1 1px solid;
	font-size: 11px;
	font-weight: bold;
	padding: 2px;
	margin: 0px;
	color: #1F016D
	} 
-->
</STYLE>

<?
if (!session_is_registered('aReward'))
{
	session_register('aReward');
	$aReward = null;
	$aReward = array();
}

$fields = array('date','invoice','terminal','tender_id','amount_in','account_id','cardno', 'reward_id');
if (!in_array($p1, array('','Load','Edit')))
{
	$aReward = null;
	$aReward = array();
	for ($c=0;$c<count($fields);$c++)
	{
		if (substr($fields[$c],0,4) =='date')
		{
			$aReward[$fields[$c]] = mdy2ymd($_REQUEST[$fields[$c]]);
		}
		else
		{
			$aReward[$fields[$c]] = $_REQUEST[$fields[$c]];
		}	
		if (in_array($fields[$c],array('tender_id','amount_in','account_id','points_in'))  && $aReward[$fields[$c]] == '')
		{
			$aReward[$fields[$c]] = 0;
		}
	}
	
	$aReward['invoice'] = str_pad($aReward['invoice'],8,'0',str_pad_left);	

}

if ($p1 == 'Edit' && $id != '')
{
	$q = "select * from reward where reward_id = '$id'";
	$qr = @pg_query($q) or message(pg_errormessage());
	$r = @pg_fetch_assoc($qr);
	$aReward = null;
	$aReward = array();
	$aReward = $r;
	
	if ($aReward['account_id'] > '0')
	{
		$q = "select account_id, account_code, cardno, cardname from account where account_id = '".$r['account_id']."'";
		$qr = @pg_query($q) or message1(pg_errormessage());
		$r = @pg_fetch_assoc($qr);
		if ($r)
		{
			$aReward += $r;
		}
	}
}
elseif ($p1 == 'Ok' && in_array($aReward['tender_id'], array(null,0)))
{
	message("NO Mode of Payment (Tender Type)...");
}
elseif ($p1 == 'Ok' && in_array($aReward['account_id'], array('','0')))
{
	message("NO Member Account Specified...");
}
elseif ($p1 == 'Ok' && in_array($aReward['amount_in'], array('','0')))
{
	message("NO Points Earned...");
}
elseif ($p1 == 'Ok')
{
	$ok=1;
	$q = "select * from tender where tender_id='".$aReward['tender_id']."'";
	$qr = @pg_query($q) or message(pg_errormessage());
	$r = @pg_fetch_object($qr);
	$aReward['tender_type'] = $r->tender_type;
	
	$points_in = $reward_grocery = $reward_dry = 0;
	
	$term = terminal($aReward['terminal']);
	if (count($term) == 0)
	{
		message1("Terminal Number NOT Found...");
		$ok = 0;
	}
    if (in_array($aReward['tender_type'], array('C','K')))
	{
	  //-cash and check rewards calculations
	  if ($term['AREA_ID'] == '1')
	  {
		$reward_grocery = intval($aReward['amount_in']/$SYSCONF['CASH_GRC_POINT']);
	  }
	  else
	  {
		$reward_dry = intval($aReward['amount_in']/$SYSCONF['CASH_DRY_POINT']);
	  }
	}
	elseif (in_array($aReward['tender_type'], array('B')))
	{
	  //-bankcards rewards calculations
	  if ($term['AREA_ID'] == '1')
	  {
		$reward_grocery = round($aReward['amount_in']/$SYSCONF['CHG_GRC_POINT'],2);
	  }
	  else
	  {
		$reward_dry = round($aReward['amount_in']/$SYSCONF['CHG_DRY_POINT'],2);
	  }
	}
	$points_in = intval($reward_grocery + $reward_dry);
/*
	if ($points_in < 1)
	{
		message1("Invoice Amount did NOT Reach Rewards Point Minimum. NO Points Generated... ");
		$ok=0;
	}
*/
	
	if ($aReward['reward_id'] < '0' && $ok== 1)
	{
		$q = "insert into reward (date, invoice, type, terminal, tender_id, amount_in, account_id, points_in, admin_id)
					values  ('".$aReward['date']."',
						'".$aReward['invoice']."',
						'2',
						'".$aReward['terminal']."',
						'".$aReward['tender_id']."',
						'".$aReward['amount_in']."',
						'".$aReward['account_id']."',
						'$points_in', 
						'".$ADMIN['admin_id']."')";
		$qr = @pg_query($q);
		if (!$qr)
		{
		 message1(pg_errormessage());			
		} 
	}
	elseif ($aReward['reward_id'] > 0 && $ok == 1)
	{
		$q = "update reward set
					date = '".$aReward['date']."',
					invoice ='".$aReward['invoice']."',
					terminal ='".$aReward['terminal']."',
					tender_id ='".$aReward['tender_id']."',
					amount_in ='".$aReward['amount_in']."',
					account_id ='".$aReward['account_id']."',
					points_in ='$points_in'
				where
					reward_id = '".$aReward['reward_id']."'";
		$qr = @pg_query($q) or message(pg_errormessage());			
	
	}
	$aReward = null;
	$aReward = array();

}
elseif ($p1 == 'DeleteConfirm')
{
	$al = implode(',',$delete);
	
	$q = " update reward set status='C' where reward_id in ($al)";
	$qr = @pg_query($q) or message(pg_errormessage());
	if ($qr)
	{
		message1(@pg_affected_rows($qr)." Reward Entries Successfully Deleted ");
	}
}
$aReward['date'] = date('Y-m-d');

?>
<br><form name="f1" id="f1" method="post" action="">
  <table width="90%" border="0" align="center" cellpadding="1" cellspacing="1" bgcolor="#FFFFFF">
    <tr> 
      <td colspan="8" background="../graphics/table0_horizontal.PNG"><font color="#FFFFFF" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        &nbsp;<strong><img src="../graphics/redlist.gif" width="16" height="17"> 
        Rewards Points Entry -Offline</strong></font> </td>
    </tr>
    <tr bgcolor="#EFEFEF"> 
      <td width="6%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Date</font><br> 
        <input name="date" type="text" class="altText" id="date" onKeypress="if(event.keyCode==13) {document.getElementById('invoice').focus();return false;}" value="<?= ymd2mdy($aReward['date']);?>" size="10"> 
      </td>
      <td width="5%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Invoice</font><br> 
        <input name="invoice" type="text"class="altText" id="invoice" onKeypress="if(event.keyCode==13) {document.getElementById('terminal').focus();return false;}" value="<?= $aReward['invoice'];?>" size="8"> 
      </td>
      <td width="3%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Term</font><br> 
        <input name="terminal" type="text"class="altText" id="terminal" onKeypress="if(event.keyCode==13) {document.getElementById('tender_id').focus();return false;}" value="<?= $aReward['terminal'];?>" size="3"> 
      </td>
      <td width="17%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Tender 
        </font> <select name="tender_id" id="tender_id" style="width:200px"  onkeypress="if(event.keyCode==13) {document.getElementById('amount').focus();return false;}">
          <option value=''>Tender Type</option>
          <?
		  	$q = "select * from tender where enable='Y' and tender_type!='A' order by tender_type";
			$qr = @pg_query($q);
			while ($r = @pg_fetch_object($qr))
			{
				if ($aReward['tender_id'] == $r->tender_id)
				{
					echo "<option value='$r->tender_id' selected>$r->tender</option>";
				}
				else
				{
					echo "<option value='$r->tender_id'>$r->tender</option>";
				}	
			}
		  ?>
        </select> </td>
      <td width="5%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Amount</font><br> 
        <input name="amount_in" type="text" id="amount_in" class="altText" style="text-align:right" onBlur="vAmt(this)" onKeypress="if(event.keyCode==13) {document.getElementById('cardno').focus();return false;}" value="<?= $aReward['amount_in'];?>" size="9"> 
      </td>
      <td colspan="3" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        Member </font><br> <input name="cardno" class="altText" type="text" id="cardno" onKeypress="if(event.keyCode==13) {document.getElementById('account_id').focus();return false;}" value="<?= $aReward['cardno'];?>" size="8" onBlur="f1.action='?p=account.reward.offline&p1=searchCardno';f1.submit()"> 
        <input name="p1" type="submit" id="p1" value="Search"> 
		<select name="account_id" id="account_id" style="width:150px" onKeypress="if(event.keyCode==13) {document.getElementById('p1').focus();return false;}">
          <option value=''>Account</option>
          <?
		  	$q = "select * from account where enable='Y'";
			if ($p1 == 'Search')
			{
				$q .= " and account ilike '$cardno%'";
				$focus = 'account_id';
			}
			elseif ($p1 == 'searchCardno')
			{
				$q .= " and cardno = '$cardno'";
				$focus = 'Ok';
			}
			elseif ($p1 == 'Edit' && $aReward['account_id'] > '0')
			{
				$q .= " and account_id = '".$aReward['account_id']."'";
				$focus = 'Ok';
			}
			else
			{
				$q .= " and 0 ";
			}

			$q .= " order by account";
			$qr = @pg_query($q);
			while ($r = @pg_fetch_object($qr))
			{
				if ($r->account_id == $aReward['account_id'] || $r->cardno == $aReward['cardno'])
				{
					echo "<option value = '$r->account_id' selected>".stripslashes($r->account)."</option>";
				}
				else
				{	
	
					echo "<option value = '$r->account_id'>".stripslashes($r->account)."</option>";
				}	
			}
		  ?>
        </select> <strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="p1" type="submit" id="Ok" value="Ok">
        <input name="reward_id" type="hidden" id="reward_id" style="text-align:right" value="<?= $aReward['reward_id'];?>" size="5" readOnly>
        </font></strong></td>
    </tr>
    <tr bgcolor="#E0E0E0"> 
      <td colspan="8"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Recorded 
        Transactions</strong></font></td>
    </tr>
    <?
		$q = "select * from reward where date='".$aReward['date']."' and tender_id>0 order by reward_id desc ";
		$qr = @pg_query($q) or message(pg_errormessage());

		while ($r = @pg_fetch_object($qr))
		{
			$ctr++;
			if ($r->status == 'C')
			{
				$bgColor = '#FFCCFF';
			}
			else
			{
				$bgColor = '#EFEFEF';
			}
	?>
	
    <tr bgcolor="<?= $bgColor;?>" onMouseOver="bgColor='#FFFFCC'" onMouseOut="bgColor='<?= $bgColor;?>'"> 
      <td align="right" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?=$ctr;?>
        . 
        <input name="delete[]" type="checkbox" id="delete[]" value="<?= $r->reward_id;?>">
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href="?p=account.reward.offline&p1=Edit&id=<?=$r->reward_id;?>"> 
        <?= $r->invoice;?>
        </a></font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $r->terminal;?>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= lookUpTableReturnValue('x','tender','tender_id','tender',$r->tender_id);?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= number_format($r->amount_in,2);?>
        </font></td>
      <td width="28%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= lookUpTableReturnValue('x','account','account_id','account',$r->account_id);?>
        </font></td>
      <td width="18%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= number_format($r->points_in,2);?>
        </font></td>
      <td width="18%"> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= ($r->status == 'C' ? 'Cancelled' : 'Saved');?>
        </font></td>
    </tr>
    <?
		}
	?>
    <tr bgcolor="#EFEFEF" onMouseOver="bgColor='#FFFFCC'" onMouseOut="bgColor='#EFEFEF'"> 
      <td colspan="8" nowrap><input name="p1" type="button" id="p1" value="Delete Checked" onClick="if(confirm('Are you sure to delete Entries?')){document.getElementById('f1').action='?p=account.reward.offline&p1=DeleteConfirm';document.getElementById('f1').submit()}"></td>
    </tr>
  </table>
</form>
<?
if ($focus != '')
{
	echo "<script>document.getElementById('$focus').focus()</script>";
}
else
{
	echo "<script>document.getElementById('invoice').focus()</script>";
}
?>