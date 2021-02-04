<br>
<table width="60%" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#EFEFEF">
  <tr align="center" bgcolor="#FFFFFF"> 
    <td background="../graphics/table0_upper_left.PNG" width="4"></td>
    <td background="../graphics/table0_horizontal.PNG"><font color="#FFFFFF" size="5"><strong>Accounting 
      Report Submenu</strong></font></td>
    <td background="../graphics/table0_upper_right.PNG" width="5"></td>
  </tr>
  <tr valign="top">
     <td  bgcolor="#75AAD5" height="135px"></td>
 <td bgColor="#FFFFFF">
  <table width="100%" height="125%" cellpadding="3" cellspacing="1" bgcolor="#EFEFEF">
        <tr align="center" valign="middle" bgcolor="#FFFFFF"> 
          <td width="33%" height="32" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=report.payableledger">Payable 
            Ledger </a> </font></td>
          <td width="33%" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=report.apsummary">Payable 
            Summary </a></font></td>
          <td width="33%" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'">&nbsp;</td>
        </tr>
        <tr align="center" valign="middle" bgcolor="#FFFFFF"> 
          <td width="33%" height="34" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;<a href="?p=report.gllist">GL 
            Listing</a></font></td>
          <td width="33%" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=report.journal">Journal 
            Listing</a></font></td>
          <td width="33%" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=report.journal">Journal 
            Summary </a></font></td>
        </tr>
        <tr align="center" valign="middle" bgcolor="#FFFFFF"> 
          <td width="33%" height="31" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=reportform.browse">Setup 
            Report Form</a></font></td>
          <td width="33%" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'">&nbsp;</td>
          <td width="33%" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'">&nbsp;</td>
        </tr>
       <tr align="center" valign="middle" bgcolor="#FFFFFF"> 
 		<?
		$q = "select * from rf_header where enable='Y'";
		$qr = @pg_query($q);
		while ($r = @pg_fetch_object($qr))
		{
		?>
          <td width="33%" height="28" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'">
		  		<?=$r->reportform;?></td>
          <td width="33%" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'">&nbsp;</td>
          <td width="33%" onMouseOver="bgColor='#FFFFCC'" OnMouseOut="bgColor='#FFFFFF'">&nbsp;</td>
 		<?
		}
		?>
        </tr>
     </table>
  </td>
  <td bgcolor="#75AAD5"></td>
  </tr>
  <tr align="center"> 
    <td bgcolor="#75AAD5" height="1"> </td>
    <td colspan="4"  bgcolor="#75AAD5"></td>
  </tr>
</table>
