<?
/*	while (file_get_contents("/prog/ics/"))
	{
		echo "x";
	}
*/

	if ($date == '') $date=ymd2mdy(yesterday());
	if ($filename == '') $filename = 'ICSBATCH.DAT';
	
?>
<br>
<form name="fd" id="fd" method="post" action="">
  <table width="80%" border="0" align="center" cellpadding="1" cellspacing="0">
    <tr bgcolor="#EFEFEF"> 
      <td width="1%" background="../graphics/table_left.PNG">&nbsp;</td>
      <td colspan="2" width="99%" background="../graphics/table_horizontal.PNG">
	  <strong><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif">ICS 
        Batch download</font></strong></td>
      <td width="1%" background="../graphics/table_right.PNG">&nbsp;</td>
    </tr>
    <tr> 
      <td  width="1%"><strong></strong></td>
      <td width="15%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Date</font></strong></td>
      <td width="85%">
        <input name="date" type="text" id="date"  onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $date;?>" size="9">
        <img src="../graphics/dwn-arrow-grn.gif" onclick="popUpCalendar(this, f1.date, 'mm/dd/yyyy')"> 
      </td>
      <td  width="1%"><strong></strong></td>
    </tr>
    <tr> 
      <td  width="1%"><strong></strong></td>
      <td  width="15%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Filename</font></strong></td>
      <td  width="85%"><input name="filename" type="text" id="filename" value="<?= $filename;?>"> 
        <input name="p1" type="button" id="p12" value="Go" onCLick="wait('Please wait. Processing data...');xajax_download(xajax.getFormValues('fd'));"> 
        <input name="p12" type="button" id="p123" value="Close" onClick="window.location='?p'"> 
      </td>
       <td  width="1%"><strong></strong></td>
   </tr>
  </table><br>
  <table width="80%" border="0" align="center">
    <tr bgcolor="#003366"> 
      <td width="10%"><strong><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
      <td width="18%"><strong><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif">Date</font></strong></td>
      <td width="36%"><strong><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif">Filename</font></strong></td>
      <td width="36%">&nbsp;</td>
    </tr>
    <tr> 
      <td colspan="4"><div id="grid.layer" style="position:absoulte"></div></td>
     
    </tr>
  </table>
 </form>
