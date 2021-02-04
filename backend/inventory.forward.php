<?
if ($year_from == '') $year_from = date('Y')-1;
if ($year_to == '') $year_to = date('Y');
?>
<br>
<form name="fd" id="fd" method="post" action="">
  <table width="80%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr bgcolor="#EFEFEF" background="../graphics/table0_horizontal.PNG"> 
      <td width="0%" height="24" background="../graphics/table_left.PNG">&nbsp;</td>
      <td width="0%" background="../graphics/table_horizontal.PNG">&nbsp;</td>
      <td width="100%" background="../graphics/table_horizontal.PNG"><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Forward 
        Inventory Balances</strong></font></td>
      <td width="0%" background="../graphics/table_right.PNG">&nbsp;</td>
    </tr>
    <tr> 
      <td colspan="4" nowrap><table width="100%%" border="0" cellpadding="0" cellspacing="1" bgcolor="#DDE5F3">
          <tr bgcolor="#DDE5F3"> 
            <td width="18%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">From 
              Year </font></td>
            <td width="82%"><font  color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>
              <input name="year_from" type="text" id="year_from"   value="<?= $year_from;?>" size="9">
              </strong></font></td>
          </tr>
          <tr bgcolor="#DDE5F3"> 
            <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">To 
              Year </font></td>
            <td><font  color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              <input name="year_to" type="text" id="year_to"   value="<?= $year_to;?>" size="9">
              </strong></font></td>
          </tr>
          <tr bgcolor="#DDE5F3"> 
            <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Action</font></td>
            <td> 
              <input name="p1" type="button" id="p14" value="Go" onCLick="wait('Please wait. Forwarding Balances. This will take few minutes...');xajax_inventoryforward(xajax.getFormValues('fd'));"> 
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
