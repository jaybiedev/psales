<?
/*	while (file_get_contents("/prog/ics/"))
	{
		echo "x";
	}
*/

	if ($date == '') $date=ymd2mdy(yesterday());
	if ($filename == '') $filename = 'lecdata.dmp';
	
?>
<br>
<form name="fd" id="fd" method="post" action="">
  <table width="80%" border="0" align="center" cellpadding="1" cellspacing="1">
    <tr bgcolor="#EFEFEF" background="../graphics/table0_horizontal.PNG"> 
      <td width="22%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Process 
        DataBackup</font></strong></td>
      <td><strong></strong><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Filename</font></strong></td>
    </tr>
    <tr> 
      <td nowrap>&nbsp;</td>
      <td nowrap><font  color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
        </strong></font> <input name="filename" type="text" id="filename" value="<?= $filename;?>"> 
        <input name="p1" type="button" id="p1" value="Go" onCLick="wait('Please wait. Processing data backup...');xajax_databackup(xajax.getFormValues('fd'));"> 
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
