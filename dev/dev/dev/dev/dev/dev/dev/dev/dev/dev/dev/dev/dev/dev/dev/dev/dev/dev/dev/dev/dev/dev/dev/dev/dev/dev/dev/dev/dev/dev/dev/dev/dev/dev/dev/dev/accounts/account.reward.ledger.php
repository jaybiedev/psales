
<br>
<?
if (!session_is_registered('aRewardLedger'))
{
	session_register('aRewardLedger');
	$aRewardLedger = null;
	$aRewardLedger = array();
}
if ($p1 == 'selectAccount' && $id != '')
{
	$q = "select * from account where account_id='$id'";
	$qr = @pg_query($q) or message(pg_errormessage());
	$r = @pg_fetch_assoc($qr);
	$aRewardLedger = $r;
}
elseif ($p1 == 'Delete')
{
	$mark = $_REQUEST['mark'];
	$ids = implode(',',$mark);
	$q = " update reward set status='C' where reward_id in ($ids)";
	$qr = @pg_query($q) or message(pg_errormessage());
	if ($qr)
	{
		message1(@pg_affected_rows($qr)." Rewards Entries Successfully Deleted ");
	}
}
?>
<form name="f1" id="f1" method="post" action="">
  <table width="80%" border="0" align="center">
    <tr>
      <td>Rewards Ledger 
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
		$aRewardLedger = $r;
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
      <td><a href="?p=account.reward.ledger&p1=selectAccount&id=<?= $r->account_id;?>"> 
	  <font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $r->cardno;?>
         </font></a></td>
      <td><a href="?p=account.reward.ledger&p1=selectAccount&id=<?= $r->account_id;?>"> 
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
  
  if ($aRewardLedger['account_id'] != '')
  {
	?>
  <table width="80%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr> 
      <td width="12%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Card 
        No.</font></td>
      <td width="47%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $aRewardLedger['cardno'];?>
        </font></td>
      <td width="41%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Account 
        No. 
        <?= $aRewardLedger['account_code'];?>
        </font></td>
    </tr>
    <tr> 
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Card Holder</font></td>
      <td colspan="2"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $aRewardLedger['account'];?>
        </font></td>
    </tr>
  </table>
  <table width="80%" border="0" align="center" cellpadding="2" cellspacing="1" bgcolor="#EFEFEF">
    <tr align="center" bgcolor="#CCCCCC"> 
      <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Date</font></strong></td>
      <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Invoice</font></strong></td>
      <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">AmountIn</font></strong></td>
      <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Points 
        In</font></strong></td>
      <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">AmountOut</font></strong></td>
      <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Points 
        Out</font></strong></td>
    </tr>
    <?
   	$q = "select * from reward where account_id='".$aRewardLedger['account_id']."'  and status!='C' order by date";
	$qr = @pg_query($q) or message(pg_errormessage());
	$ctr=0;
	$total_in = $total_out = $total_amount_in= $total_amount_out = 0;
	while ($r = @pg_fetch_object($qr))
	{
	
		$ctr++;
		if ($ctr%2 == 0) $bgColor='#FFFFFF';
		else $bgColor='#EFEFEF';
		
		$total_in += $r->points_in;
		$total_out += $r->points_out;
		$total_amount_in += $r->amount_in;
		$total_amount_out += $r->amount_out;
  ?>
    <tr  bgColor="<?= $bgColor;?>" onMouseOut="bgColor='<?= $bgColor;?>'" onMouseOver="bgColor='#FFFFCC'"> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input type="checkbox" name="mark[]" value="<?=$r->reward_id;?>">
        <?= ymd2mdy($r->date);?>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $r->invoice;?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= number_format($r->amount_in,2);?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $r->points_in;?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= number_format($r->amount_out,2);?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= number_format($r->points_out,2);?>
        </font></td>
    </tr>
    <?
		}
	?>
    <tr> 
      <td colspan="2"><input type="button" name="p1" value="Delete Checked" onClick="if (confirm('Are you sure to DELETE checked items?')){document.getElementById('f1').action='?p=account.reward.ledger&p1=Delete';document.getElementById('f1').submit();}">
        Available Points : <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <strong>
        <?= $total_in - $total_out;?>
        </strong> </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <?= number_format($total_amount_in,2);?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $total_in;?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= number_format($total_amount_out,2);?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= number_format($total_out,2);?>
        </font></td>
    </tr>
  </table>

	<?
	//show ledger
  }
  ?>
  

</form>
