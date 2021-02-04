  <table width="100%" border="0" cellspacing="1" cellpadding="1" bgcolor="#EFEFEF">
  <tr> 
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Expiry 
      Date </font></td>
    <td><input name="date_expiry" type="text" id="date_expiry" value="<?= ymd2mdy($aaccount['date_expiry']);?>" size="12" maxlength="12" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" onKeypress="if(event.keyCode==13) {document.getElementById('lifetime').focus();return false;}">
      <font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"><img src="../graphics/arrow_down.jpg" onClick="popUpCalendar(this, f1.date_expiry, 'mm/dd/yyyy')"></font><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <input name="lifetime" type="checkbox" value="Y" <?=($aaccount['lifetime']=='Y' ? 'checked': '');?> id="lifetime2" onKeypress="if(event.keyCode==13) {document.getElementById('terms').focus();return false;}">
      Life Time </font></td>
    <td><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Type</font></td>
    <td><select name="account_type_id"  style="width:250px">
        <option value=''>Account Type</option>
        <?
	  	$q = "select * from account_type where enable='Y' order by account_type";
		$qr = @pg_query($q);
		while ($r=@pg_fetch_object($qr))
		{
			if ($aaccount['account_type_id'] == $r->account_type_id)
			{
				echo "<option value=$r->account_type_id selected>$r->account_type</option>";	
			}
			else
			{
				echo "<option value=$r->account_type_id>$r->account_type</option>";	
			}
		}	
	  ?>
      </select></td>
  </tr>
  <tr> 
    <td width="15%"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Bond</font></td>
    <td width="32%"> <font size="2" face="Verdana, Arial, Helvetica, sans-serif">
      <input type="text" name="bond" size="12" value="<?= $aaccount['bond'];?>"  style="text-align:right">
      </font> </td>
    <td width="16%"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Classification</font></td>
    <td width="37%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <select name="account_class_id"  id="account_class_id" style="width:250px">
        <option value='0'>Account Classification</option>
        <?
	  	$q = "select * from account_class where enable='Y' order by account_class";
		$qr = @pg_query($q);
		while ($r=@pg_fetch_object($qr))
		{
			if ($aaccount['account_class_id'] == $r->account_class_id)
			{
				echo "<option value=\"$r->account_class_id\" selected>$r->account_class</option>";	
			}
			else
			{
				echo "<option value=\"$r->account_class_id\">$r->account_class</option>";	
			}
		}	
	  ?>
      </select>
      </font> </td>
  </tr>
  <tr> 
    <td width="15%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Credit 
      Limit</font></td>
    <td width="32%"><input name="credit_limit" type="text" id="credit_limit2" value="<?= $aaccount['credit_limit'];?>" size="12" maxlength="12" style="text-align:right"></td>
    <td width="16%"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Guarantor</font></td>
    <td width="37%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <select name="guarantor_id"  style="width:250px">
        <option value='0'>Select Guarantor</option>
        <option value='0'>No Guarantor</option>
        <?
	  	$q = "select * from account where enable='Y' and account_type_id='3' order by account_code";
		$qr = @pg_query($q);
		while ($r=@pg_fetch_object($qr))
		{
			if ($aaccount['guarantor_id'] == $r->account_id)
			{
				echo "<option value=$r->account_id selected>$r->account_code  $r->account</option>";	
			}
			else
			{
				echo "<option value=$r->account_id>$r->account_code  $r->account</option>";	
			}
		}	
	  ?>
      </select>
      </font> </td>
  </tr>
  <tr> 
    <td width="15%">&nbsp;</td>
    <td width="32%">&nbsp;</td>
    <td width="16%"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Zero 
      Rated </font></td>
    <td width="37%"><?= lookUpAssoc('zero_rated', array('No'=>'N', 'Yes'=>'Y'), $aaccount['zero_rated']);?></td>
  </tr>
  <tr> 
    <td colspan="2"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;<strong>Account 
      Status</strong></font></td>
    <td colspan="2"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Rewards 
      Points</strong></font></td>
  </tr>
  <tr> 
    <td width="15%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Total 
      Purchases</font></td>
    <td width="32%"><input name="total_purchase" type="text" id="total_purchase" value="P <?= number_format($aaccount['total_purchase'],2);?>" size="15" maxlength="15" style="text-align:right; border:0; padding:0px 0px 0px;font-weight:bold" readOnly ></td>
    <td width="16%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Total 
      Points</font></td>
    <td width="37%"><input name="points_in" type="text" id="points_in" value="<?= number_format($aaccount['points_in'],2);?>" size="15" maxlength="15" style="text-align:right; border:0; padding:0;font-weight:bold" readOnly ></td>
  </tr>
  <tr> 
    <td width="15%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Ending 
      Balance</font></td>
    <td width="32%"><input name="total_purchase2" type="text" id="total_purchase2" value="<?= number_format($aaccount['total_balance'],2);?>" size="15" maxlength="15" style="text-align:right; border:0; padding:0px 0px 0px;font-weight:bold" readOnly ></td>
    <td width="16%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Total 
      Claimed</font></td>
    <td width="37%"><input name="points_out" type="text" id="points_out" value="<?= number_format($aaccount['points_out'],2);?>" size="15" maxlength="15" style="text-align:right; border:0; padding:0px 0px 0px;font-weight:bold" readOnly ></td>
  </tr>
  <tr> 
    <td width="15%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Current 
      Due</font></td>
    <td width="32%"><input name="total_purchase3" type="text" id="total_purchase3" value="<?= number_format($aaccount['total_current'],2);?>" size="15" maxlength="15" style="text-align:right; border:0; padding:0px 0px 0px;font-weight:bold" readOnly > 
    </td>
    <td width="16%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Total 
      Unclaimed</font></td>
    <td width="37%"><input name="points_balance" type="text" id="points_balance" value="<?= number_format($aaccount['points_unclaimed'],2);?>" size="15" maxlength="15" style="text-align:right; border:0; padding:0px 0px 0px;font-weight:bold" readOnly ></td>
  </tr>
  <tr> 
    <td width="15%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Total 
      Due</font></td>
    <td width="32%"><input name="total_purchase4" type="text" id="total_purchase4" value="<?= number_format($aaccount['total_due'],2);?>" size="15" maxlength="15" style="text-align:right; border:0; padding:0px 0px 0px;font-weight:bold" readOnly ></td>
    <td width="16%"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">&nbsp;</font></td>
    <td width="37%">&nbsp;</td>
  </tr>
  <tr> 
    <td width="15%">&nbsp;</td>
    <td width="32%">&nbsp;</td>
    <td width="16%">&nbsp;</td>
    <td width="37%">&nbsp;</td>
  </tr>
  <tr> 
    <td></td>
    <td></td>
    <td width="16%">&nbsp;</td>
    <td width="37%">&nbsp;</td>
  </tr>
  <tr> 
    <td colspan="4">&nbsp;</td>
  </tr>
</table>
