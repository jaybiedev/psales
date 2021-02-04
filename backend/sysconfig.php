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
			'BUSINESS_REG',	'BUSINESS_LESSEE', 'RECEIPT_PRINT', 'CHARGE_FOOTER1', 'RECEIPT_FOOTER1', 'RECEIPT_FOOTER2',
			'LOGO_FILE', 'REG_SERIAL_NO','DBNAME','DBDOMAIN', 'DB_ENGINE','TAXRATE', 'PROPRIETOR', 'ACCREDITATION','BRANCH_ID',
			'USE_MEDIUM_PRICE','USE_CASE_PRICE','REWARD_GROCERY_PURCHASE','ECO_BAG', 'RECEIPT_PROMO_FOOTER','RECEIPT_PROMO_HEADER');

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
  <div align="center">
    <font size="5" face="Times New Roman, Times, serif">
      <strong>System Configuration </strong>
    </font>
  </div>
  <form name="form1" method="post" action="">
    <div align="center">
      <table width="65%" border="0" cellpadding="0" cellspacing="1">
        <tr>
          <td nowrap>
            <font size="2" face="Verdana, Arial, Helvetica, sans-serif">Business Name
            </font>
          </td>
          <td align="left">
            <input name="BUSINESS_NAME" type="text" value="<?= stripslashes($SYSCONF['BUSINESS_NAME']);?>" size="40">
          </td>
        </tr>
        <tr>
          <td width="22%" nowrap>
            <font size="2" face="Verdana, Arial, Helvetica, sans-serif">Business Address
            </font>
          </td>
          <td width="78%" align="left">
            <input name="BUSINESS_ADDR" type="text" value="<?= $SYSCONF['BUSINESS_ADDR'];?>" size="40">
          </td>
        </tr>
        <tr>
          <td nowrap>
            <font size="2" face="Verdana, Arial, Helvetica, sans-serif">Business Telephone
            </font>
          </td>
          <td align="left">
            <input name="BUSINESS_TEL" type="text" value="<?= $SYSCONF['BUSINESS_TEL'];?>" size="40">
          </td>
        </tr>
        <tr>
          <td nowrap>
            <font size="2" face="Verdana, Arial, Helvetica, sans-serif">TIN</font>
          </td>
          <td align="left">
            <input name="BUSINESS_TIN" type="text" value="<?= $SYSCONF['BUSINESS_TIN'];?>" size="40">
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
          <td nowrap>
            <font size="2" face="Verdana, Arial, Helvetica, sans-serif">Proprietor</font>
          </td>
          <td align="left">
            <input name="PROPRIETOR" type="text" value="<?= $SYSCONF['PROPRIETOR'];?>" size="40">
          </td>
        </tr>
        <tr>
          <td nowrap>
            <font size="2" face="Verdana, Arial, Helvetica, sans-serif">Branch</font>
          </td>
          <td align="left">
            <select id="BRANCH_ID" name="BRANCH_ID" style="width:325px">
              <?
			$q = "select * from branch where enable='Y'";
			$qr = @pg_query($q);
			while ($r = @pg_fetch_object($qr))
			{
				if ($SYSCONF['BRANCH_ID'] == $r->branch_id)
				{
					echo "<option value='$r->branch_id' selected>$r->branch</option>";
				}
				else
				{
					echo "<option value='$r->branch_id'>$r->branch</option>";
				}
			}
		?>
            </select>
          </td>
        </tr>
        <tr>
          <td nowrap>
            <font size="2" face="Verdana, Arial, Helvetica, sans-serif">BIR Accreditation No. </font>
          </td>
          <td align="left">
            <input name="ACCREDITATION" type="text" value="<?= substr($SYSCONF['ACCREDITATION'],0,30);?>" size="40">
          </td>
        </tr>
        <tr>
          <td nowrap>
            <font size="2" face="Verdana, Arial, Helvetica, sans-serif">Tax Rate </font>
          </td>
          <td align="left">
            <input name="TAXRATE" type="text" value="<?= $SYSCONF['TAXRATE'];?>" size="40">
          </td>
        </tr>
        <tr>
          <td nowrap>
            <font size="2" face="Verdana, Arial,Helvetica, sans-serif">Cash Invoice Footer Line1</font>
          </td>
          <td align="left">
            <input name="RECEIPT_FOOTER1" type="text" value="<?= $SYSCONF['RECEIPT_FOOTER1'];?>" size="53" maxlength="40">
          </td>
        </tr>
        <tr>
          <td nowrap>
            <font size="2" face="Verdana, Arial,Helvetica, sans-serif">Charge Invoice Footer Line1</font>
          </td>
          <td align="left">
            <input name="CHARGE_FOOTER1" type="text" value="<?= $SYSCONF['CHARGE_FOOTER1'];?>" size="53" maxlength="40">
          </td>
        </tr>
        <tr>
          <td valign="top">
            <font size="2" face="Verdana, Arial,Helvetica, sans-serif">Receipt Footer Line2</font>
          </td>
          <td align="left">
            <textarea name="RECEIPT_FOOTER2" cols="40"><?= $SYSCONF['RECEIPT_FOOTER2'];?></textarea>
          </td>
        </tr>
        <tr>
          <td>
            <font size="2" face="Verdana, Arial,Helvetica, sans-serif">Active Database Used</font>
          </td>
          <td align="left">
            <input name="DBNAME" type="text" value="<?= $SYSCONF['DBNAME'];?>" size="53" maxlength="40">
          </td>
        </tr>
        <tr>
          <td>
            <font size="2" face="Verdana, Arial,Helvetica, sans-serif">Database Host </font>
          </td>
          <td align="left">
            <input name="DBDOMAIN" type="text" value="<?= $SYSCONF['DBDOMAIN'];?>" size="53" maxlength="40">
          </td>
        </tr>
        <tr>
          <td>
            <font size="2" face="Verdana, Arial,Helvetica, sans-serif">Database Engine
            </font>
          </td>
          <td align="left">
            <?= lookUpAssoc('DB_ENGINE',array('PostgreSQL'=>'pgsql','MyISAM'=>'myisam','MyInnoDB'=>'myinnodb'),$SYSCONF['DB_ENGINE']);?>
          </td>
        </tr>
        <?
		if (!chkRights2("program","madd",$ADMIN['admin_id']) or 1)
		{
		?>
          <tr>
            <td colspan="2">
              <hr>
            </td>
          </tr>
          <tr>
            <td>
              <font size="2" face="Verdana, Arial,Helvetica, sans-serif">Use Case Price in POS</font>
            </td>
            <td align="left">
              <?= lookUpAssoc('USE_CASE_PRICE',array('Yes'=>'Y','No'=>'N'),$SYSCONF['USE_CASE_PRICE']);?>
            </td>
          </tr>
          <tr>
            <td>
              <font size="2" face="Verdana, Arial,Helvetica, sans-serif">Use Medium Price in POS</font>
            </td>
            <td align="left">
              <?= lookUpAssoc('USE_MEDIUM_PRICE',array('Yes'=>'Y','No'=>'N'),$SYSCONF['USE_MEDIUM_PRICE']);?>
            </td>
          </tr>
          <?
	  }
	  ?>
            <tr>
              <td colspan="2">
                <hr>
              </td>
            </tr>
            <tr>
              <td nowrap>
                <font size="2" face="Verdana, Arial,Helvetica, sans-serif">Amount/ (1)Reward Point </font>
              </td>
              <td align="left">
                <input name="REWARD_GROCERY_PURCHASE" type="text" id="REWARD_GROCERY_PURCHASE" value="<?= $SYSCONF['REWARD_GROCERY_PURCHASE'];?>"
                  size="40">
              </td>
            </tr>
            <tr>
              <td nowrap>
                <font size="2" face="Verdana, Arial,Helvetica, sans-serif">
                  ECO BAG AMOUNT </font>
              </td>
              <td align="left">
                <input name="ECO_BAG" type="text" id="ECO_BAG" value="<?= $SYSCONF['ECO_BAG'];?>" size="40">
              </td>
            </tr>
            <tr>
              <td valign="top">
                <font size="2" face="Verdana, Arial,Helvetica, sans-serif">Receipt Promo Header</font>
              </td>
              <td align="left">
                <textarea name="RECEIPT_PROMO_HEADER" cols="40"><?= $SYSCONF['RECEIPT_PROMO_HEADER'];?></textarea>
              </td>
            </tr>
            <tr>
              <td valign="top">
                <font size="2" face="Verdana, Arial,Helvetica, sans-serif">Receipt Promo Footer</font>
              </td>
              <td align="left">
                <textarea name="RECEIPT_PROMO_FOOTER" cols="40"><?= $SYSCONF['RECEIPT_PROMO_FOOTER'];?></textarea>
              </td>
            </tr>
            
            <tr>
              <td colspan="2" nowrap>
                <hr>
              </td>
            </tr>
      </table>
      <input type="submit" name="p1" value="Save Changes">
    </div>
  </form>