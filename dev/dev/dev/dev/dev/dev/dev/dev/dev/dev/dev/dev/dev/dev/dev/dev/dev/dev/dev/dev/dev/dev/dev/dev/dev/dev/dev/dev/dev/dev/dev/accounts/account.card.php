  <table width="100%" border="0" cellspacing="1" cellpadding="1" bgcolor="#EFEFEF">
    <tr bgcolor="#FFFFFF"> 
      <td colspan="4">
        <table width="90%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td width="11%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Renew<br>
              <input name="date_renew" type="text" id="date_expiry" value="<?= ymd2mdy($aaccount['date_renew']);?>" size="12" maxlength="12" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')">
              <img src="../graphics/arrow_down.jpg" onClick="popUpCalendar(this, f1.date_expiry, 'mm/dd/yyyy')"></font> &nbsp;
            </td>
            <td width="88%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Remarks 
              <br>
              <input type="text" name="renew_remarks" value="<?= $aaccount['renew_remarks'];?>" size="60">
              <input type="submit" name="p1" value="Renew">
              </font></td>
            <td width="1%">&nbsp;</td>
          </tr>
        </table>
      </td>
    </tr>
    <tr bgcolor="#CCCCCC"> 
      <td width="15%"><b><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Expiry 
        Date </font></b></td>
      <td width="32%"> <b><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Date 
        Renewed </font></b></td>
      <td width="16%"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Encoder</font></b></td>
      <td width="37%"> <b><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Remarks</font></b></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td width="15%">&nbsp;</td>
      <td width="32%">&nbsp;</td>
      <td width="16%">&nbsp;</td>
      <td width="37%">&nbsp;</td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td width="15%">&nbsp;</td>
      <td width="32%">&nbsp;</td>
      <td width="16%">&nbsp;</td>
      <td width="37%">&nbsp;</td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td width="15%">&nbsp;</td>
      <td width="32%">&nbsp;</td>
      <td width="16%">&nbsp;</td>
      <td width="37%">&nbsp;</td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td width="15%">&nbsp;</td>
      <td width="32%">&nbsp;</td>
      <td width="16%">&nbsp;</td>
      <td width="37%">&nbsp;</td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td width="15%">&nbsp;</td>
      <td width="32%">&nbsp;</td>
      <td width="16%">&nbsp;</td>
      <td width="37%">&nbsp;</td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td width="15%">&nbsp;</td>
      <td width="32%">&nbsp;</td>
      <td width="16%">&nbsp;</td>
      <td width="37%">&nbsp;</td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td width="15%">&nbsp;</td>
      <td width="32%">&nbsp;</td>
      <td width="16%">&nbsp;</td>
      <td width="37%">&nbsp;</td>
    </tr>
  </table>
