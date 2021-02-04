<?
if (!session_is_registered('SYSCONF'))
{
	session_register('SYSCONF');
	$SYSCONF = null;
	$SYSCONF = array();
}
if ($p1 == 'Save Changes')
{
	$fields = array('BUSINESS_NAME','BUSINESS_ADDR','BUSINESS_TEL','BUSINESS_TIN',
			'BUSINESS_REG',	'BUSINESS_LESSEE', 'RECEIPT_PRINT', 'RECEIPT_FOOTER1', 'RECEIPT_FOOTER2',
			'LOGO_FILE', 'REG_SERIAL_NO','DBNAME','DBDOMAIN', 'DB_ENGINE','TAXRATE', 'PROPRIETOR',
			'DEDUCT_SSS','DEDUCT_PHIC','DEDUCT_TAX','DEDUCT_PAGIBIG');

	for ($c=0;$c<count($fields);$c++)
	{
		$SYSCONF[$fields[$c]] = $_REQUEST[$fields[$c]];
		
		$q = "select * from sysconfig where sysconfig='".$fields[$c]."'";
		$qr = @pg_query($q) or message('Error querying...');
		if (@pg_num_rows($qr) != 0)
		{
			$r = @pg_fetch_object($qr);
			$q = "update sysconfig set 
						sysconfig='".$fields[$c]."', 
						value='".$SYSCONF[$fields[$c]]."'
					where
						id='$r->id'";
			$qr = pg_query($q) or message("Error updating system configuration...".pg_errormessage());
		}
		else
		{
			$q = "insert into sysconfig (sysconfig,value)
			       values ('".$fields[$c]."','".$SYSCONF[$fields[$c]]."')";
			$qr = @pg_query($q) or message("Error updating system configuration...".pg_errormessage());
		}
	}
	if ($qr)
	{
		message(" System configuration updated...");
	}
	
}
?>
<br>
<div align="center"><font size="5" face="Times New Roman, Times, serif"><strong>System 
  Configuration </strong></font> </div>
<form name="form1" method="post" action="">
  <div align="center">
    <table width="50%" border="0" cellpadding="0" cellspacing="1">
      <tr> 
        <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Business 
          Name</font></td>
        <td> <input name="BUSINESS_NAME" type="text" value="<?= stripslashes($SYSCONF['BUSINESS_NAME']);?>" size="40"> 
        </td>
      </tr>
      <tr> 
        <td width="22%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Business 
          Address</font></td>
        <td width="78%"> <input name="BUSINESS_ADDR" type="text" value="<?= $SYSCONF['BUSINESS_ADDR'];?>" size="40"> 
        </td>
      </tr>
      <tr> 
        <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Business 
          Telephone</font></td>
        <td> <input name="BUSINESS_TEL" type="text" value="<?= $SYSCONF['BUSINESS_TEL'];?>" size="40"> 
        </td>
      </tr>
      <tr> 
        <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">TIN</font></td>
        <td> <input name="BUSINESS_TIN" type="text" value="<?= $SYSCONF['BUSINESS_TIN'];?>" size="40"> 
        </td>
      </tr>
      <!-- <tr bgcolor="#FFFFFF"> 
        <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">BIR 
          REGISTRATION</font></td>
        <td> 
          <input name="BUSINESS_REG" type="text" value="<?= $SYSCONF['BUSINESS_REG'];?>" size="40">
        </td>
      </tr>
      <tr bgcolor="#FFFFFF"> 
        <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Lessee 
          Number</font></td>
        <td> 
          <input name="BUSINESS_LESSEE" type="text" value="<?= $SYSCONF['BUSINESS_LESSEE'];?>" size="40">
        </td>
      </tr>
      <tr bgcolor="#FFFFFF"> 
        <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Logo 
          File</font></td>
        <td> 
          <input name="LOGO_FILE" type="text" value="<?=$SYSCONF['LOGO_FILE'];?>" size="40">
        </td>
      </tr> -->
      <tr> 
        <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Proprietor</font></td>
        <td><input name="PROPRIETOR" type="text" value="<?= $SYSCONF['PROPRIETOR'];?>" size="40"></td>
      </tr>
      <tr> 
        <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Registered 
          System Key</font></td>
        <td> <input name="REG_SERIAL_NO" type="text" readOnly value="<?= substr($SYSCONF['REG_SERIAL_NO'],0,13);?>" size="40"> 
        </td>
      </tr>
      <tr> 
        <td><font size="2" face="Verdana, Arial,Helvetica, sans-serif">Active 
          Database Used</font></td>
        <td> <input name="DBNAME" type="text" value="<?= $SYSCONF['DBNAME'];?>" size="53" maxlength="40"> 
        </td>
      </tr>
      <tr> 
        <td><font size="2" face="Verdana, Arial,Helvetica, sans-serif">Database 
          Host </font></td>
        <td> <input name="DBDOMAIN" type="text" value="<?= $SYSCONF['DBDOMAIN'];?>" size="53" maxlength="40"> 
        </td>
      </tr>
      <tr> 
        <td><font size="2" face="Verdana, Arial,Helvetica, sans-serif">Database 
          Engine</font></td>
        <td> 
          <?= lookUpAssoc('DB_ENGINE',array('PostgreSQL'=>'pgsql','MyISAM'=>'myisam','MyInnoDB'=>'myinnodb'),$SYSCONF['DB_ENGINE']);?>
        </td>
      </tr>
      <?
		if (!chkRights2("program","madd",$ADMIN['admin_id']) or 1)
		{
		?>
      <tr> 
        <td colspan="2"><hr></td>
      </tr>
      <tr> 
        <td><font size="2" face="Verdana, Arial,Helvetica, sans-serif">Deduct 
          SSS On</font></td>
        <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
          <?= lookUpAssoc('DEDUCT_SSS',array('Manual'=>'M','Every Payday'=>'E','Cutoff 1'=>'1','Cutoff  2'=>'2','Cutoff 3'=>'3','Cutoff 4'=>'4'),$SYSCONF['DEDUCT_SSS']);?>
          </font></td>
      </tr>
      <tr> 
        <td><font size="2" face="Verdana, Arial,Helvetica, sans-serif">Deduct 
          PHIC On</font></td>
        <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
          <?= lookUpAssoc('DEDUCT_PHIC',array('Manual'=>'M','Every Payday'=>'E','Cutoff 1'=>'1','Cutoff  2'=>'2','Cutoff 3'=>'3','Cutoff 4'=>'4'),$SYSCONF['DEDUCT_PHIC']);?>
          </font></td>
      </tr>
      <tr> 
        <td><font size="2" face="Verdana, Arial,Helvetica, sans-serif">Deduct 
          WTax On</font></td>
        <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
          <?= lookUpAssoc('DEDUCT_TAX',array('Manual'=>'M','Every Payday'=>'E','Cutoff 1'=>'1','Cutoff  2'=>'2','Cutoff 3'=>'3','Cutoff 4'=>'4'),$SYSCONF['DEDUCT_TAX']);?>
          </font></td>
      </tr>
      <tr> 
        <td><font size="2" face="Verdana, Arial,Helvetica, sans-serif">Deduct 
          Pag-Ibig On</font></td>
        <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
          <?= lookUpAssoc('DEDUCT_PAGIBIG',array('Manual'=>'M','Every Payday'=>'E','Cutoff 1'=>'1','Cutoff  2'=>'2','Cutoff 3'=>'3','Cutoff 4'=>'4'),$SYSCONF['DEDUCT_PAGIBIG']);?>
          </font></td>
      </tr>
      <tr> 
        <td colspan="2">&nbsp;</td>
      </tr>
      <?
	  }
	  ?>
    </table>
    <input type="submit" name="p1" value="Save Changes">
  </div>
</form>
