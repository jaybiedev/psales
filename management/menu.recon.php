<?
	if (!chkRights2('reconciliation','mview',$ADMIN['admin_id']))
	{
		message('You are NOT allowed to view Reports...');
		exit;
	}

?>
<br>
<table width="60%" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#EFEFEF">
  <tr align="center" bgcolor="#FFFFFF"> 
    <td background="../graphics/table0_upper_left.PNG" width="4"></td>
    <td background="../graphics/table0_horizontal.PNG"><font color="#FFFFFF" size="5"><strong>Reconciliation 
      Submenu</strong></font></td>
    <td background="../graphics/table0_upper_right.PNG" width="5"></td>
  </tr>
  <tr valign="top"> 
    <td  bgcolor="#75AAD5" height="135px"></td>
    <td bgColor="#FFFFFF"> <table width="100%" height="125%" cellpadding="3" cellspacing="1" bgcolor="#EFEFEF">
        <tr align="center" valign="middle" bgcolor="#FFFFFF"> 
          <td width="33%" height="32" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
            <a accesskey="Z" href="?p=report.zread"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Z-Reading</font></a></font></td>
          <td width="33%" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
            <a accesskey="A" href="?p=report.taudit"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Audit</font></a></font></td>
          <td width="33%" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
            <a  accesskey="C" href="?p=report.category">Category</a></font></td>
        </tr>
        <tr align="center" valign="middle" bgcolor="#FFFFFF"> 
          <!-- <td width="33%" height="34" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
            <a accesskey="R" href="?p=report.dailyrecon"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Daily 
            Reconciliation</font></a></font></td> -->
          <td width="33%" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=report.servicecharge">Service 
            Charge</a></font></td>
          <td width="33%" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a accesskey='U' href="?p=report.hourly">Hourly</a></font></td>
        </tr>
      </table></td>
    <td bgcolor="#75AAD5"></td>
  </tr>
  <tr align="center"> 
    <td bgcolor="#75AAD5" height="1"> </td>
    <td colspan="4"  bgcolor="#75AAD5"></td>
  </tr>
</table>
