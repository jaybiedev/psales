<?
/*	while (file_get_contents("/prog/ics/"))
	{
		echo "x";
	}
*/

	if ($date == '') $date=ymd2mdy(yesterday());
	if ($filename == '') $filename = 'FILE.TXT';
	
?>
<br>
<form name="fd" id="fd" method="post" action="">
  <table width="80%" border="0" align="center" cellpadding="1" cellspacing="1">
    <tr bgcolor="#EFEFEF" background="../graphics/table0_horizontal.PNG"> 
      <td width="26%" nowrap><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><img src="../graphics/waiting.gif" width="16" height="16">Category 
        Sales download</font></strong></td>
      <td width="6%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Date</font></strong></td>
      <td width="68%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Filename</font></strong></td>
    </tr>
    <tr> 
      <td nowrap>&nbsp;</td>
      <td nowrap><font  color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
        <input name="date" type="text" id="date"  onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $date;?>" size="9">
        </strong></font><img src="../graphics/dwn-arrow-grn.gif" onclick="popUpCalendar(this, f1.date, 'mm/dd/yyyy')"> 
      </td>
      <td nowrap><input name="filename" type="text" id="filename" value="<?= $filename;?>">
        <input name="p1" type="button" id="p1" value="Go" onCLick="wait('Please wait. Processing data...');xajax_export_category(xajax.getFormValues('fd'));"> 
        <input name="p12" type="button" id="p122" value="Close" onClick="window.location='?p'"> 
      </td>
    </tr>
  </table>
  <table width="80%" border="0" align="center">
    <tr bgcolor="#003366"> 
      <td width="10%"><strong><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
      <td width="18%"><strong><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif">Date</font></strong></td>
      <td width="36%"><strong><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif">Filename</font></strong></td>
      <td width="36%">&nbsp;</td>
    </tr>
    <tr> 
      <td colspan="4"><div id="grid.layer"></div></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
  </table>
 </form>
