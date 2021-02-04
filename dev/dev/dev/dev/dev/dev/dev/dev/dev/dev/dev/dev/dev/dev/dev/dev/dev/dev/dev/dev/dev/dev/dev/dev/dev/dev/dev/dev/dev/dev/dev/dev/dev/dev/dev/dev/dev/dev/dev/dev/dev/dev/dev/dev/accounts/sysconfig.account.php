<?
if (!session_is_registered('SYSCONF'))
{
	session_register('SYSCONF');
	$SYSCONF = null;
	$SYSCONF = array();
}
if ($p1 == 'Save Changes')
{
	$fields = array('CASH_GRC_POINT','CASH_DRY_POINT','CHG_GRC_POINT','CHG_DRY_POINT','BANK_GRC_POINT','BANK_DRY_POINT','MINIMUM_POINTS','VALUE_PER_POINT','REWARD_EXPIRY');

	for ($c=0;$c<count($fields);$c++)
	{
		$SYSCONF[$fields[$c]] = $_REQUEST[$fields[$c]];
		
		$q = "select * from sysconfig where sysconfig='".$fields[$c]."'";
		$qr = pg_query($q) or message('Error querying...');
		if (pg_num_rows($qr) != 0)
		{
			$r = pg_fetch_object($qr);
			$q = "update sysconfig set 
						sysconfig='".$fields[$c]."', 
						value='".$SYSCONF[$fields[$c]]."'
					where
						sysconfig_id='$r->sysconfig_id'";
			$qr = pg_query($q) or message("Error updating system configuration...".pg_errormessage());
		}
		else
		{
			$q = "insert into sysconfig (sysconfig,value)
			       values ('".$fields[$c]."','".$SYSCONF[$fields[$c]]."')";
			$qr = pg_query($q) or message("Error updating system configuration...".pg_error());
		}
	}
	if ($qr)
	{
		message(" System configuration updated...");
	}
	
}
?>
<br>
<div align="center"><font size="5" face="Times New Roman, Times, serif"><strong>Rewards 
  Configuration</strong></font> </div>
<form name="form1" method="post" action="">
  <div align="center">
    <table width="50%" border="0" cellpadding="3" cellspacing="1">
      <tr bgcolor="#CCCCCC"> 
        <td colspan="2" nowrap><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Cash 
          Sales </font></strong> </td>
      </tr>
      <tr> 
        <td width="22%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Grocery 
          Amount/Point</font></td>
        <td width="78%"> <input name="CASH_GRC_POINT" type="text" value="<?= $SYSCONF['CASH_GRC_POINT'];?>" size="40"> 
        </td>
      </tr>
      <tr> 
        <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Dry 
          Goods Amount/Point</font></td>
        <td> <input name="CASH_DRY_POINT" type="text" value="<?= $SYSCONF['CASH_DRY_POINT'];?>" size="40"> 
        </td>
      </tr>
      <tr bgcolor="#CCCCCC"> 
        <td colspan="2" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Bankcard 
          Sales </strong></font></td>
      </tr>
      <tr> 
        <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Grocery 
          Amount/Point</font></td>
        <td> <input name="BANK_GRC_POINT" type="text" value="<?= $SYSCONF['BANK_GRC_POINT'];?>" size="40"> 
        </td>
      </tr>
      <tr> 
        <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Dry 
          Goods Amount/Point</font></td>
        <td><input name="BANK_DRY_POINT" type="text" value="<?= $SYSCONF['BANK_DRY_POINT'];?>" size="40"></td>
      </tr>
      <tr bgcolor="#CCCCCC"> 
        <td colspan="2" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Charge 
          Sales (Upon Payment)</strong></font> </td>
      </tr>
      <tr> 
        <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Grocery 
          Amount/Point</font></td>
        <td> <input name="CHG_GRC_POINT" type="text" value="<?= $SYSCONF['CHG_GRC_POINT'];?>" size="40"> 
        </td>
      </tr>
      <tr> 
        <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Dry 
          Goods Amount/Point</font></td>
        <td><input name="CHG_DRY_POINT" type="text" value="<?= $SYSCONF['CHG_DRY_POINT'];?>" size="40"></td>
      </tr>
      <tr bgcolor="#CCCCCC"> 
        <td colspan="2" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Other 
          Info </strong></font></td>
      </tr>
      <tr> 
        <td valign="top"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Minimum 
          Points </font></td>
        <td><input name="MINIMUM_POINTS" type="text" value="<?= $SYSCONF['MINIMUM_POINTS'];?>" size="40"> 
        </td>
      </tr>
      <tr> 
        <td><font size="2" face="Verdana, Arial,Helvetica, sans-serif">Peso Value/Point</font></td>
        <td><input name="VALUE_PER_POINT" type="text" value="<?= $SYSCONF['VALUE_PER_POINT'];?>" size="40"> 
        </td>
      </tr>
      <tr> 
        <td><font size="2" face="Verdana, Arial,Helvetica, sans-serif">Dormant 
          Expires (days)</font></td>
        <td> <input name="REWARD_EXPIRY" type="text" value="<?= $SYSCONF['REWARD_EXPIRY'];?>" size="40"> 
        </td>
      </tr>
      <?
		if (!chkRights2("program","madd",$ADMIN['admin_id']) or 1)
		{
		?>
      <tr> 
        <td colspan="2"><hr></td>
      </tr>
      <?
	  }
	  ?>
    </table>
    <input type="submit" name="p1" value="Save Changes">
  </div>
</form>
