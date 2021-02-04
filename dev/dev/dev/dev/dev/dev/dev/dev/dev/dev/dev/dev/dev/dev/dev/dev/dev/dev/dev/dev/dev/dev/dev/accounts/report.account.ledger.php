
<br>
<?
if (!session_is_registered('aLedger'))
{
	session_register('aLedger');
	$aLedger = null;
	$aLedger = array();
}
if ($p1 == 'selectAccount' && $id != '')
{
	$q = "select * from account where account_id='$id'";
	$qr = @pg_query($q) or message(pg_errormessage());
	$r = @pg_fetch_assoc($qr);
	$aLedger = $r;
}

?>
<form name="form1" method="post" action="">
  <table width="80%" border="0" align="center">
    <tr>
      <td>Search Card Holder
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
		$aLedger = $r;
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
      <td><a href="?p=report.account.ledger&p1=selectAccount&id=<?= $r->account_id;?>"> 
	  <font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $r->cardno;?>
         </font></a></td>
      <td><a href="?p=report.account.ledger&p1=selectAccount&id=<?= $r->account_id;?>"> 
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
  
  if ($aLedger['account_id'] != '')
  {
	?>
  <table width="80%" border="0" align="center">
    <tr> 
      <td width="12%">Card No.</td>
      <td width="47%"><?= $aLedger['cardno'];?></td>
      <td width="41%">Account No. <?= $aLedger['account_code'];?></td>
    </tr>
    <tr> 
      <td>Card Holder</td>
      <td colspan="2"><?= $aLedger['account'];?></td>
    </tr>
  </table>
  <table width="80%" border="0" align="center" cellpadding="2" cellspacing="1" bgcolor="#EFEFEF">
    <tr bgcolor="#CCCCCC"> 
      <td width="5%" rowspan="2"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Date</font></strong></td>
      <td width="7%" rowspan="2"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Invoice</font></strong></td>
      <td width="27%" rowspan="2"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Type</font></strong></td>
      <td colspan="2" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Add</font></strong></td>
      <td colspan="2" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Less</font></strong></td>
      <td width="15%" rowspan="2" align="center" valign="middle"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Balance</font></strong></td>
    </tr>
    <tr bgcolor="#CCCCCC"> 
      <td width="10%" align="center" nowrap><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        </font></strong></td>
      <td width="13%" align="center" nowrap><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Amount 
        In</font></strong></td>
      <td width="12%" align="center" nowrap><strong></strong></td>
      <td width="11%" align="center" nowrap><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Amount 
        Out </font></strong></td>
    </tr>
    <?
   	$q = "select * from reward where account_id='".$aLedger['account_id']."' order by date";
	$qr = @pg_query($q) or message(pg_errormessage());
	$ctr=0;
	$total_in = $total_out = 0;
	while ($r = @pg_fetch_object($qr))
	{
	
		$ctr++;
		if ($ctr%2 == 0) $bgColor='#FFFFFF';
		else $bgColor='#EFEFEF';
		
		$total_in += $r->points_in;
		$total_out += $r->points_out;
		
		$balance_points = $total_in - $total_out;

		if ($r->type == '1')
			$ctype = 'POS';
		elseif ($r->type=='3')
			$ctype = 'Encoded';
		elseif ($r->type == '2')
			$ctype = 'POS Claim';
		elseif ($r->type == '4')
			$ctype = 'Cash Claim';
		elseif ($r->type == '6')
			$ctype = 'Item Claim';
		elseif ($r->type == '8')
			$ctype = 'GC Claim';
		
  ?>
    <tr  bgColor="<?= $bgColor;?>" onMouseOut="bgColor='<?= $bgColor;?>'" onMouseOver="bgColor='#FFFFCC'"> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= ymd2mdy($r->date);?>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $r->invoice;?>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $ctype;?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= number_format2($r->amount_in,2);?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= number_format2($r->amount_out,2);?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <?= $balance_points;?>
        </font></td>
    </tr>
    <?
		}
	?>
    <tr bgcolor="#CCCCCC"> 
      <td>&nbsp;</td>
      <td colspan="2"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
        </font></td>
      <td align="right"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        </font></strong></td>
      <td align="right"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
        </font></strong></td>
      <td align="right"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        </font></strong></td>
      <td align="right">&nbsp;</td>
      <td align="right">&nbsp;</td>
    </tr>
  </table>

	<?
	//show ledger
  }
  ?>
  

</form>
