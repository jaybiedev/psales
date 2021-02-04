<?
	if (!chkRights2('managementinquiry','mview',$ADMIN['admin_id']))
	{
		message('You are NOT allowed to view Reports...');
		exit;
	}

?>
<br>
<table width="75%" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#EFEFEF">
  <tr align="center" bgcolor="#FFFFFF"> 
    <td width="4" height="30" background="../graphics/table0_upper_left.PNG"></td>
    <td background="../graphics/table0_horizontal.PNG"><font color="#FFFFFF" size="4"><strong>Mangement 
      Inquiry Submenu</strong></font></td>
    <td background="../graphics/table0_upper_right.PNG" width="5"></td>
  </tr>
  <tr valign="top"> 
    <td  bgcolor="#75AAD5" height="135px"></td>
    <td bgColor="#FFFFFF"> <table width="100%" height="125%" border="0" cellpadding="3" cellspacing="1" bgcolor="#EFEFEF">
        <tr valign="middle" bgcolor="#FFFFFF"> 
          <td width="33%" height="32" align="center" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
            <a href="?p=../backend/cashier.monitor">Cashier Monitor</a></font></td>
          <td width="33%" align="center" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
            <a href="?p=../backend/cashier.invoice">Invoices</a></font></td>
          <td width="33%" align="center" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
            <a href="?p=../backend/report.shiftreading">Shift Reading</a></font></td>
        </tr>
        <tr valign="middle" bgcolor="#FFFFFF"> 
          <td width="33%" height="34" align="center" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=report.hourly">Hourly 
            Sales</a></font></td>
          <td width="33%" align="center" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=report.dailysales">Daily 
            Sales/Month</a></font></td>
          <td width="33%" align="center" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=report.changeprice">Changed 
            Price </a></font></td>
        </tr>
        <tr valign="middle" bgcolor="#FFFFFF"> 
          <td height="34" align="center" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=report.hourlydept">Hourly 
            Sales by Dept</a> </font></td>
          <td align="center" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=report.fastmove">Fast 
            Moving </a></font></td>
          <td align="center" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=report.category">Daily 
            Category Sales</a> </font></td>
        </tr>
        <tr valign="middle" bgcolor="#FFFFFF"> 
          <td height="34" align="center" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=report.monthly">Monthly 
            Total Sales</a></font></td>
          <td align="center" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=report.monthlycost">Monthly 
            Cost of Sales by Dept</a></font></td>
          <td align="center" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;<a href="?p=report.category.month">Monthly 
            Category Sales</a> </font></td>
        </tr>
        <tr valign="middle" bgcolor="#FFFFFF"> 
          <td height="34" align="center" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=report.monthlydept">Monthly 
            Sales by Dept</a></font></td>
          <td align="center" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'">&nbsp;</td>
          <td align="center" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=report.categorycost">Cost 
            of Sales Per Category</a></font></td>
        </tr>
        <tr valign="middle" bgcolor="#FFFFFF"> 
          <td height="34" align="center" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=report.annualsales">Annual 
            Sales</a>&nbsp;</font></td>
          <td align="center" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=report.grossprofit">Gross 
            Profit </a> &nbsp;</font></td>
          <td align="center" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'">&nbsp;</td>
        </tr>
        <tr valign="middle" bgcolor="#D7E3EE"> 
          <td height="27" colspan="3" > 
           <img src="../graphics/bluelist.gif" width="16" height="17"> 
            &nbsp; <font face="Verdana, Arial, Helvetica, sans-serif" size="2">RealTime 
            (Takes Longer) Reports</font></td>
        </tr>
        <tr valign="middle" bgcolor="#FFFFFF"> 
          <td height="34" align="center" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=report.dailysales.online">Daily 
            Sales</a></font></td>
          <td align="center" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=report.monthlydept.online">Monthly 
            Sales</a></font></td>
          <td align="center" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=report.annualsales.online">Annual 
            Sales</a>&nbsp;</font></td>
        </tr>
      </table></td>
    <td bgcolor="#75AAD5"></td>
  </tr>
  <tr align="center"> 
    <td bgcolor="#75AAD5" height="1"> </td>
    <td colspan="4"  bgcolor="#75AAD5"></td>
  </tr>
</table>
