<?
if ($p1 == 'Go')
{
	$barcode = $_REQUEST['barcode'];
	$q = "select * from stock where barcode='$barcode'";
	$qr = @pg_query($q) or message(pg_errormessage());
	if (@pg_num_rows($qr) == 0)
	{
		message1('Barcode NOT Found...');
	}
	else
	{
		$r = @pg_fetch_object($qr);
		$stock = $r->stock;
		$price1 = $r->price1;
	}
} //printing
?> 
<form name='form1' method='post' action=''>
  <table width="80%" border="0" align="center" cellpadding="0" cellspacing="2">
    <tr bgcolor="#EFEFEF"> 
      <td height="24" colspan="2" 
background="../graphics/table0_horizontal.PNG"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <strong> <img src="../graphics/bluelist.gif" width="16" height="17"><font color="#FFFFCC">Barcode 
        Printing</font></strong></font></td>
    </tr>
    <tr> 
      <td nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        Barcode 
        <input name="barcode" type="text" id="barcode" value="<?= $barcode;?>" size="16" onBlur="print_area.document.getElementById('bar').value=barcode.value">
        Copies </font> <font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="copies" type="text" id="copies" value="<?= $copies;?>" size="5"  onBlur="print_area.document.getElementById('copies').value=copies.value">
        <input name="p1" type="button" id="p1" value="Go" onClick="print_area.document.getElementById('f2').submit()"></td>
      <td width="54%" nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"><?= $stock;?>
        </font></td>
    </tr>
    <tr> 
      <td colspan="2"><hr color="#993300" size="1"></td>
    </tr>
  </table>
  <table width="80%" border="0" cellspacing="1" cellpadding="1" height="1%" bgcolor="#DADADA" align="center">
    <tr bgcolor="#333366"> 
      <td height="26" bgcolor="#DADADA"> <font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"><b>Barcode 
        Print Preview</b></font></td>
    </tr>
    <tr> 
      <td valign="top" bgcolor="#FFFFFF" align="center"> 
	  <?
	  if ($p1= 'Go')
	  {
	  	echo "<IFRAME SRC='barcode.print.gen.php' name='print_area' id='print_area' TITLE='Barcode Printing' WIDTH=750 HEIGHT=370 FRAMEBORDER=0></IFRAME>";
	  }
	?>
	  </td>
    </tr>
  </table>
<div align=center>
    <input name="p1" type="button" id="p1" value="Print Draft"  onClick="print_area.print()" >
    <input name="p122" type="button" id="p122" value="Print"  onClick="printIframe('print_area')" >
  </div>
</form>

<iframe name="printit" id="printit" style="width:0px;height:0px;"></iframe>
