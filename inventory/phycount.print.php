<div id="printLayer" name="printLayer"  style="position:absolute; width:400px; height:100px; z-index:1; left: 25%; top:40%; display:none;"> 
  <table width="100%" height="100%" border="0" align="center" cellpadding="0" cellspacing="0" background="../graphics/table0_horizontal.PNG">
    <tr> 
      <td width="1%"><img src="../graphics/table0_upper_left.PNG" width="6" height="31"></td>
      <td width="10%"  height="31"align="left" background="../graphics/table0_horizontal.PNG"><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><b>Printing</b></font></td>
      <td width="80%" height="31" align="right" background="../graphics/table0_horizontal.PNG">&nbsp; 
      </td>
      <td width="9%" align="right"><img src="../graphics/table_close.PNG" onClick="document.getElementById('printLayer').style.display='none'" ><img src="../graphics/table0_upper_right.PNG" width="6" height="30"></td>
    </tr>
    <tr valign="top" bgcolor="#A4B9DB"> 
      <td ></td>
      <td bgcolor="#A4B9DB" >&nbsp;</td>
      <td bgcolor="#A4B9DB" ><br> <input name="printbutton" type="radio" value="total" checked>
        Overall Total Only <br> <input type="radio" name="printbutton" value="category_id">
        Group by Category <br> <input type="radio" name="printbutton" value='all'>
        All Items detailed <br>
        <br> <input name="outputbutton" type="radio" value="screen" checked>
        Screen<br> <input type="radio" name="outputbutton" value="printer">
        Printer</td>
      <td >&nbsp;</td>
    </tr></tr>
    <tr valign="top" bgcolor="#A4B9DB"> 
      <td ></td>
      <td colspan="2" align="center" bgcolor="#A4B9DB" > 
        <input name="p1"  id="print" type="button" value="Print" onCLick="wait('Please wait. Printing...');xajax_pc_print(xajax.getFormValues('f1'));"> 
        <input name="p1"  id="close"  type="button" value="Close" onClick="document.getElementById('printLayer').style.display='none'"></td>
      <td >&nbsp;</td>
    </tr>
    <tr > 
      <td height="3" colspan="4"  background="../graphics/table0_vertical.PNG" >
      </td>
    </tr>
  </table>
  </div>