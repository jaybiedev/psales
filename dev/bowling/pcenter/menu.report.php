<?
	if (!chkRights2('managementinquiry','mview',$ADMIN['admin_id']))
	{
		message('You are NOT allowed to view Reports...');
		exit;
	}

?>
<br>
<table width="60%" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#EFEFEF">
  <tr align="center" bgcolor="#FFFFFF"> 
    <td width="4" height="30" background="../graphics/table0_upper_left.PNG"></td>
    <td background="../graphics/table0_horizontal.PNG"><font color="#FFFFFF" size="4"><strong>Reports 
      Submenu</strong></font></td>
    <td background="../graphics/table0_upper_right.PNG" width="5"></td>
  </tr>
  <tr valign="top"> 
    <td  bgcolor="#75AAD5" height="135px"></td>
    <td bgColor="#FFFFFF"> <table width="100%" height="125%" border="0" cellpadding="3" cellspacing="1" bgcolor="#EFEFEF">
        <tr valign="middle" bgcolor="#FFFFFF"> 
          <td width="33%" height="32" align="center" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
            <a href="?p=report.taudit">Transaction Audit</a></font></td>
          <td width="33%" align="center" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=report.dailyrecon">Daily 
            Reconciliation </a> </font></td>
          <td width="33%" align="center" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
            <a href="?p=../backend/cashier.invoice">Invoices</a></font></td>
        </tr>
        <tr valign="middle" bgcolor="#FFFFFF"> 
          <td width="33%" height="34" align="center" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=report.itemsales">Item 
            Sales </a></font></td>
          <td width="33%" align="center" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=report.dailysales.online">Daily 
            Sales</a></font></td>
          <td width="33%" align="center" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'">&nbsp;</td>
        </tr>
        <tr valign="middle" bgcolor="#FFFFFF"> 
          <td height="34" align="center" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'">&nbsp;</td>
          <td align="center" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=report.fastmove">Fast 
            Moving </a></font></td>
          <td align="center" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'">&nbsp;</td>
        </tr>
        <tr valign="middle" bgcolor="#FFFFFF"> 
          <td height="34" align="center" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=report.monthlydept.online">Monthly 
            Sales</a></font></td>
          <td align="center" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;</font></td>
          <td align="center" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'">&nbsp;</td>
        </tr>
        <tr valign="middle" bgcolor="#FFFFFF"> 
          <td height="34" align="center" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=report.annualsales.online">Annual 
            Sales</a>&nbsp;</font></td>
          <td align="center" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=report.grossprofit">Gross 
            Profit </a> &nbsp;</font></td>
          <td align="center" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'">&nbsp;</td>
        </tr>
      </table></td>
    <td bgcolor="#75AAD5"></td>
  </tr>
  <tr align="center"> 
    <td bgcolor="#75AAD5" height="1"> </td>
    <td colspan="4"  bgcolor="#75AAD5"></td>
  </tr>
</table>
