  <table width="100%" border="0" cellspacing="1" cellpadding="0" bgcolor="#EFEFEF">
    <tr> 
      <td width="4%" bgcolor="#CCCCCC"><b><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></b></td>
      <td width="10%" bgcolor="#CCCCCC"><b><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Date</font></b></td>
      <td width="11%" bgcolor="#CCCCCC"><b><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Reference</font></b></td>
      <td width="4%" bgcolor="#CCCCCC"><b><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Type</font></b></td>
      
    <td width="12%" align="center" bgcolor="#CCCCCC"><b><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Debit</font></b></td>
    <td width="12%" align="center" bgcolor="#CCCCCC"><b><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Credit</font></b></td>
      <td width="10%" align="center" bgcolor="#CCCCCC"><b><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Balance</font></b></td>
      <td width="37%" bgcolor="#CCCCCC"><b><font size="2" face="Verdana, Arial, Helvetica, sans-serif">User</font></b></td>
    </tr>
	<?
		if ($aaccount['account_id'] != '')
		{
			$q = "select * from accountledger 	
					where
						account_id ='".$aaccount['account_id']."' and
						enable = 'Y' 
					order by
						date, type";
			$qr = @pg_query($q) or message(pg_errormessage());
		}
		$ctr=$balance=0;
		while ($temp = @pg_fetch_assoc($qr))
		{
			$ctr++;
			$balance += $temp['debit'] - $temp['credit'];
			if ($ctr%2 == '0')
			{
				$bgColor = '#EFEEF9';
			}	
			else
			{
				$bgColor = '#FFFFFF';
			}		
	?>	
    <tr bgcolor="<?= $bgColor;?>"> 
      <td width="4%" align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?=$ctr;?>
        . </font></td>
      <td width="10%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><?= ymd2mdy($temp['date']);?></font></td>
      <td width="11%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><?= $temp['invoice'];?></font></td>
      <td width="4%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><?= $temp['type'];?></font></td>
      
    <td width="12%" align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
      <?= number_format2($temp['debit'],2);?>
      </font></td>
    <td width="12%" align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= number_format2($temp['credit'],2);?>
      </font></td>
      <td width="10%" align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><?= number_format($balance,2);?></font></td>
      <td width="37%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;&nbsp;<?= lookUpTableReturnValue('x', 'admin', 'admin_id', 'name', $temp['admin_id']);?></font></td>
    </tr>
		<?
		}
		?>
    <tr> 
      <td width="4%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;</font></td>
      <td width="10%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;</font></td>
      <td width="11%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;</font></td>
      <td width="4%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;</font></td>
      <td width="12%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;</font></td>
      <td width="12%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;</font></td>
      <td width="10%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;</font></td>
      <td width="37%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;</font></td>
    </tr>
  </table>
