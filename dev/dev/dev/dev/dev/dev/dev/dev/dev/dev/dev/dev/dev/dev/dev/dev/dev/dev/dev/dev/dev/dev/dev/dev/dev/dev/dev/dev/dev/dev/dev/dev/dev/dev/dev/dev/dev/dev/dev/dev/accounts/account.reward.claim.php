<script>
function vAmt()
{
	var amount_claim = document.getElementById('amount_claim').value;
	var points_balance = document.getElementById('points_balance').value;
	var value_per_point = document.getElementById('value_per_point').value;
	var points_required = 0;
	
	points_required = amount_claim / value_per_point;
	
	if (points_required > points_balance)
	{
		alert('Points Required ('+points_required+') Exceeds Available Points ('+points_balance+')')
	}
	else
	{
		document.getElementById('points_claim').value = points_required;
	}
	return false;
}

</script>
<br>
<?
if (!session_is_registered('aRewardClaim'))
{
	session_register('aRewardClaim');
	$aRewardClaim = null;
	$aRewardClaim = array();
}
$atype = array('Claim (Cash)'=>'4','Claim (Item)'=>'5');


if ($p1 == 'selectAccount' && $id != '')
{
	$q = "select * from account where account_id='$id'";
	$qr = @pg_query($q) or message(pg_errormessage());
	$r = @pg_fetch_assoc($qr);
	$aRewardClaim = $r;
}
elseif ($p1 == 'Save Claim' && $_REQUEST['points_claim']>0 && $_REQUEST['amount_claim']>0 )
{

		$aRewardClaim['amount_claim'] = $_REQUEST['amount_claim'];
		$aRewardClaim['points_claim'] = $_REQUEST['points_claim'];
		$aRewardClaim['invoice'] = $_REQUEST['invoice'];
		$aRewardClaim['type'] = $_REQUEST['type'];
		
		$date = date('Y-m-d');
		$aRewardClaim['date']=$date;

		if  ($aRewardClaim['reward_id'] == ''  )
		{
			$q = "insert into reward (date, invoice, type, amount_out, account_id, points_out, admin_id)
					values  ('".$aRewardClaim['date']."',
						'".$aRewardClaim['invoice']."',
						'".$aRewardClaim['type']."',
						'".$aRewardClaim['amount_claim']."',
						'".$aRewardClaim['account_id']."',
						'".$aRewardClaim['points_claim']."',
						'".$ADMIN['admin_id']."')";
			$qr = @pg_query($q);
			if (!$qr)
			{
				message1(pg_errormessage());			
			} 
			else
			{
				$id = @pg_insert_id('reward');
				$aRewardClaim['reward_id'] = $id;
				message1('Rewards Claim Saved...');
			}
		}
		else
		{
			$q = "update reward set
							account_id = '".$aRewardClaim['reward_id']."',
							points_out = '".$aRewardClaim['points_out']."',
							amount_out = '".$aRewardClaim['amount_claim']."',
							type = '".$aRewardClaim['type']."'
						where
							reward_id = '".$aRewardClaim['reward_id']."'";
			$qr = @pg_query($q);
			if (!$qr)
			{
				message1(pg_errormessage());			
			} 
			else
			{
				message1("Rewards Claim Transaction Updated...");
			}
		}
}
elseif ($p1 == 'Print' && $aRewardClaim['reward_id'] == '')
{
	message("Save Transaction Before Printing Please...");
}
elseif ($p1 == 'Print')
{
	if ($aRewardClaim['type'] == '4')
	{
		$type = 'Cash';
	}
	if ($aRewardClaim['type'] == '5')
	{
		$type = 'Item';
	}
	if ($aRewardClaim['type'] == '6')
	{
		$type = 'Gift Certificate';
	}
	
	$details = '';
	$details .= strtoupper($SYSCONF['BUSINESS_NAME'])."\n";
	$details .= strtoupper($SYSCONF['BUSINESS_ADDR'])."\n\n";
	$details .= "BONUS CLAIM\n";
	$details .= "Terminal  :".$SYSCONF['TERMINAL']."    Record Id :".str_pad($aRewardClaim['reward_id'],9,'0',str_pad_left)."\n";
	$details .= "Printed by:".lookUpTableReturnValue('x','admin','admin_id','name',$ADMIN['admin_id'])."\n";
	$details .= space(11).date('n/d/Y g:ia')."\n";
	$details .= str_repeat('-',39)."\n";
	$details .= "Card No: ".$aRewardClaim['cardno']."\n";
	$details .= "Account: ".$aRewardClaim['account']."\n";
	$details .= "Date   : ".ymd2mdy($aRewardClaim['date'])."\n";
	$details .= "Type   : ".$type."\n";
	$details .= "Amount : ".number_format($aRewardClaim['amount_claim'],2)."\n";
	$details .= str_repeat('-',39)."\n";
	$details .= "For One-Time Payment Only\n\n";
	$details .= str_repeat('_',strlen($aRewardClaim['account']))."\n";
	$details .= "Account: ".$aRewardClaim['account']."\n\n\n\n";
	
//	echo "<pre>$details</pre>";
	nPrinter($details, $SYSCONF['RECEIPT_PRINTER_TYPE'], $SYSCONF['RECEIPT_PRINTER_DEST']);
}
?>
<form name="form1" method="post" action="">
  <table width="80%" border="0" align="center">
    <tr>
      <td>Rewards Redemption 
        <input name="xSearch" type="text" id="xSearch" value="<?= $xSearch;?>">
        <?= lookUpAssoc('searchby',array('Card No.'=>'cardno','Name'=>'account'),$searchby);?>
        <input name="p1" type="submit" id="p1" value="Go">
        <hr> </td>
    </tr>
  </table>
  <?
  if ($p1 == 'Go' && $xSearch != '')
  {
  	$q = "select * from account where enable='Y' ";
	if ($searchby == 'cardno')
	{
		$q .= " and cardno = '$xSearch'";
	}
	else
	{
		$q .= " and account ilike '$xSearch%'";
	}

	$qr = @pg_query($q) or message(pg_errormessage());
	if (@pg_num_rows($qr) == 1)
	{
		$r = @pg_fetch_assoc($qr);
		$aRewardClaim = $r;
	}
	else
	{
		?>
  <table width="75%" border="0" align="center" cellpadding="2" cellspacing="1" bgcolor="#FFFFFF">
    <tr bgcolor="#339966" > 
      <td width="11%" height="21"><strong><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
      <td width="18%"><strong><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif">Card 
        No.</font></strong></td>
      <td width="71%"><strong><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif">Account 
        Name</font></strong></td>
    </tr>
    <?
		$ctr=0;
		while ($r = @pg_fetch_object($qr))
		{
			$ctr++;
			if ($ctr%2 == 0) $bgColor='#EFEFEF';
			else $bgColor='#FFFFFF';
			?>
    <tr onMouseOver="bgColor='#FFFFCC'" onMouseOut="bgColor='<?=$bgColor;?>'" bgColor="<?=$bgColor;?>"> 
      <td height="23" align="right" nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $ctr;?>
        .</font></td>
      <td><a href="?p=account.reward.claim&p1=selectAccount&id=<?= $r->account_id;?>"> 
	  <font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $r->cardno;?>
         </font></a></td>
      <td><a href="?p=account.reward.claim&p1=selectAccount&id=<?= $r->account_id;?>"> 
	  <font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $r->account;?>
        </font></a></td>
    </tr>
    <?			
		}
		?>
  </table>
		 <?
		exit;
	}
  }
  
  if ($aRewardClaim['account_id'] != '')
  {
  	$q = "select sum(points_in) as points_in, sum(points_out) as points_out from reward where account_id='".$aRewardClaim['account_id']."'";
	$qr = @pg_query($q) or message(pg_errormessage());
	$r = @pg_fetch_assoc($qr);
	if ($r)
	{
		$aRewardClaim += $r;
		$aRewardClaim['points_balance'] = $aRewardClaim['points_in'] - $aRewardClaim['points_out'];
	}
	
	if ( $aRewardClaim['points_in'] < $SYSCONF['MINIMUM_POINTS'])
	{
		message1("Cannot make Rewards Claim. Total Points have NOT reach minimum of   ".$SYSCONF['MINIMUM_POINTS']);
	}	
  }
  ?>
  <table width="75%" border="0" align="center">
    <tr bgcolor="#CCCCCC"> 
      <td colspan="2"><strong>Rewards Information</strong></td>
    </tr>
    <tr> 
      <td>Card No.</td>
      <td><input name="textfield4" type="text" value="<?= $aRewardClaim['cardno'];?>" size="40" readOnly ></td>
    </tr>
    <tr> 
      <td>Account No.</td>
      <td><input name="textfield42" type="text" value="<?= $aRewardClaim['account_code'];?>" size="40" readOnly ></td>
    </tr>
    <tr> 
      <td>Card Holder</td>
      <td><input name="textfield43" type="text" value="<?= $aRewardClaim['account'];?>" size="40" readOnly ></td>
    </tr>
    <tr> 
      <td width="21%">Total Points Earned</td>
      <td width="79%"><input name="textfield" type="text" value="<?= $aRewardClaim['points_in'];?>" size="10" readOnly style="text-align:right"></td>
    </tr>
    <tr> 
      <td>Total Points Claimed</td>
      <td><input name="textfield2" type="text" value="<?= $aRewardClaim['points_out'];?>" size="10" readOnly style="text-align:right"></td>
    </tr>
    <tr> 
      <td>Total Points Available</td>
      <td><input name="points_balance"  id="points_balance" type="text" value="<?= $aRewardClaim['points_balance'];?>" size="10" readOnly style="text-align:right"></td>
    </tr>
    <tr bgcolor="#CCCCCC"> 
      <td colspan="2"><strong>Desired Claim</strong></td>
    </tr>
    <tr> 
      <td>Type</td>
      <td><?= lookUpAssoc('type', array('Claim (Cash)'=>'4','Claim (Gift Certificate)'=>'6','Claim (Item)'=>'5'),$aRewardClaim['type']);?>
    </tr>
    <tr> 
    <tr> 
      <td>Amount</td>
      <td><input name="amount_claim" type="text" id="amount_claim" style="text-align:right" value="<?= $aRewardClaim['amount_claim'];?>" size="10" onKeypress="if(event.keyCode==13) {document.getElementById('SaveClaim').focus();return false;}" onBlur="vAmt()"></td>
    </tr>
    <tr> 
      <td>Equivalent Points</td>
      <td><input name="points_claim" type="text" id="points_claim" style="text-align:right" value="<?= $aRewardClaim['points_claim'];?>" size="10" readOnly></td>
    </tr>
    <tr> 
      <td>&nbsp;</td>
      <td><input name="value_per_point" type="hidden" id="value_per_point" style="text-align:right" value="<?= $SYSCONF['VALUE_PER_POINT'];?>" size="10" readOnly></td>
    </tr>
    <tr> 
      <td colspan="2"><input name="p1" type="submit" id="SaveClaim" value="Save Claim">
        <input name="p1" type="submit" id="p1" value="Print"></td>
    </tr>
  </table>

</form>
