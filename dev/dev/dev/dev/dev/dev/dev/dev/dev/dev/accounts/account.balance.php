<table width="90%%" border="0" cellspacing="3" cellpadding="0">
  <tr> 
    <td width="22%" nowrap bgcolor="#EFEFEF"><font size="4">Card Expiry</font></td>
    <td width="16%" align="right" bgcolor="#EFEFEF"><strong><font size="4"> 
      <?= ymd2mdy($aaccount['date_expiry']);?>
      </font></strong></td>
    <td width="62%">&nbsp;</td>
  </tr>
  <tr> 
    <td nowrap><font size="4">As of (Last Posting)</font></td>
    <td align="right"><strong><font size="4">
      <?= ymd2mdy($aaccount['date_posted']);?>
      </font></strong></td>
    <td>&nbsp;</td>
  </tr>
  <tr> 
    <td nowrap bgcolor="#EFEFEF"><font size="4">Total Account</font></td>
    <td align="right" bgcolor="#EFEFEF"><strong><font size="4"> 
      <?= number_format($aBal['balance'],2);?>
      </font></strong></td>
    <td>&nbsp;</td>
  </tr>
  <tr> 
    <td nowrap><font size="4">Total Due</font></td>
    <td align="right"> <strong><font size="5"> 
      <?= number_format($aBal['balance'],2);?>
      </font></strong></td>
    <td>&nbsp;</td>
  </tr>
  <tr> 
    <td bgcolor="#EFEFEF"><font size="4">Credit Limit</font></td>
    <td align="right" bgcolor="#EFEFEF"><strong><font size="5"> 
      <?= number_format( $aaccount['credit_limit'],2);?>
      </font></strong></td>
    <td>&nbsp;</td>
  </tr>
  <tr> 
    <td nowrap><font size="4">Allowable Balance</font></td>
    <td align="right"><strong><font size="5"> 
      <?= number_format($aaccount['credit_limit'] - $aBal['balance'],2);?>
      </font></strong></td>
    <td>&nbsp;</td>
  </tr>
</table>
