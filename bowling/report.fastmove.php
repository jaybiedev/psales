<?
if (!chkRights2('salesreports','mview',$ADMIN['admin_id']))
{
	message('You are NOT allowed to view Reports...');
	exit;
}
if ($p1 == '')
{
	$from_month = 1;
	$to_month = date('m');
	$year = date('Y');
	$top = '50';
}	

?> 
<form name='fd' id='fd' method='post' action=''>
  <table width="80%" border="0" align="center" cellpadding="0" cellspacing="2">
    <tr bgcolor="#EFEFEF" background="../graphics/table0_horizontal.PNG"> 
      <td height="24" colspan="6"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <strong> <img src="../graphics/bluelist.gif" width="16" height="17"><font color="#FFFFCC"> 
        Invntory Fast Moving Report</font></strong></font></td>
    </tr>
    <tr bgcolor="#EFEFEF"> 
      <td width="10%" align="center" nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">From 
        Month </font></td>
      <td width="13%" nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        To</font></td>
      <td width="4%" nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">Year</font></td>
      <td width="4%" nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">Top</font></td>
      <td width="7%" nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">Sort</font></td>
      <td width="62%" nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">Dept</font></td>
    </tr>
    <tr> 
      <td nowrap> 
        <?= lookUpMonth('from_month',$from_month);?>
      </td>
      <td nowrap> 
        <?= lookUpMonth('to_month',$to_month);?>
      </td>
      <td nowrap><input name="year" type="text" id="year" value="<?= $year;?>" size="5"></td>
      <td nowrap><input name="top" type="text" id="top" value="<?= $top;?>" size="5"></td>
      <td nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= lookUpAssoc('sort',array('Sales Amount'=>'amount',"Quantity Sold"=>'qty'),$sort);?>
        </font></td>
      <td nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <?= lookUpAssoc('dept',array('All'=>'', 'Grocery'=>'G',"Dry Goods"=>'D'),$dept);?>
        <input name="p12" type="button" id="p123" value="Go" onCLick="wait('Please wait. Processing data...');xajax_fastmove(xajax.getFormValues('fd'));">
        </font></td>
    </tr>
    <tr> 
      <td colspan="6"><hr color="#993300" size="1"></td>
    </tr>
  </table>
  <table width="80%" border="0" cellspacing="1" cellpadding="1" height="1%" bgcolor="#DADADA" align="center">
    <tr bgcolor="#333366"> 
      <td height="26" bgcolor="#DADADA"> <font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"><b>Report 
        Fast Moving Preview &nbsp; </b> 
        <?
		if (file_exists($reportfile))
		{
			echo "| <a href=$reportfile>Download</a>";
		}
		?>
        </font></td>
    </tr>
    <tr> 
      <td valign="top" bgcolor="#FFFFFF" align="center"> 
	  <?
	  if ($p1= 'Go' && in_array($g1, array('bar','pie')))
	  {
	  	echo "<IFRAME SRC='graph.customersales.php' name='print_area' TITLE='Sales' WIDTH=750 HEIGHT=370 FRAMEBORDER=0></IFRAME>";
	  }
	  else
	  {
		 echo "<textarea name='print_area'  id='print_area' cols='97' rows='20' wrap='OFF'>$details2</textarea>";
		}	  
		?>
	  </td>
    </tr>
  </table>
<div align=center>
    <input name="p1" type="submit" id="p1" value="Print Draft">
    <input name="p122" type="button" id="p122" value="Print"  onClick="printIframe(print_area)" >
  </div>
</form>

<iframe name="printit" id="printit" style="width:0px;height:0px;"></iframe>
