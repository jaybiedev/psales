<?
/*	while (file_get_contents("/prog/ics/"))
	{
		echo "x";
	}
*/
	$filename = 'PAY_'.$SYSCONF['BRANCH_CODE'].'.TXT';
	if ($date == '') $date=ymd2mdy(yesterday());
	
?>
<br>
<form name="fd" id="fd" method="post" action="">
  <table width="65%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr bgcolor="#EFEFEF" background="../graphics/table0_horizontal.PNG"> 
      <td width="0%" height="24" background="../graphics/table_left.PNG">&nbsp;</td>
      <td width="0%" background="../graphics/table_horizontal.PNG">&nbsp;</td>
      <td width="100%" background="../graphics/table_horizontal.PNG"><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Stock 
        Transfer Download</strong></font> </td>
      <td width="0%" background="../graphics/table_right.PNG">&nbsp;</td>
    </tr>
    <tr> 
      <td colspan="4" nowrap><table width="100%%" border="0" cellpadding="0" cellspacing="1" bgcolor="#DDE5F3">
          <tr bgcolor="#DDE5F3"> 
            <td width="18%">&nbsp;</td>
            <td width="82%">&nbsp;</td>
          </tr>
          <tr bgcolor="#DDE5F3"> 
            <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Date 
              From </font></td>
            <td><font  color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              <input name="date" type="text" id="date2"  onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $date;?>" size="9">
              <img src="../graphics/dwn-arrow-grn.gif" onclick="popUpCalendar(this, f1.date, 'mm/dd/yyyy')"> 
              </strong></font></td>
          </tr>
          <tr bgcolor="#DDE5F3"> 
            <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Filename</font></td>
            <td><input name="filename" type="text" id="filename2" value="<?= $filename;?>" size="50">
              <input name="p1" type="button" id="p14" value="Go" onCLick="wait('Please wait. Downloading Payment Entry...');xajax_paymentDownload(xajax.getFormValues('fd'));"> 
              <input name="p12" type="button" id="p125" value="Close" onClick="window.location='?p'"></td>
          </tr>
          <tr bgcolor="#DDE5F3"> 
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
          <tr bgcolor="#DDE5F3"> 
            <td colspan="2" align="center">&nbsp;</td>
          </tr>
          <tr bgcolor="#DDE5F3"> 
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
        </table></td>
    </tr>
  </table>
  <div id="grid.layer" align="center"></div>
  </form>
