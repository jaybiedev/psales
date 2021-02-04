<?
if (!chkRights2('salesreports','mview',$ADMIN['admin_id']))
{
	message('You are NOT allowed to view Reports...');
	exit;
}
	if ($p1=="") 
	{
		$from_date = date("m/d/Y");
		$to_date = date("m/d/Y");
		$q = "select date from sales_header order by date desc offset 0 limit 1";
		$qr = pg_query($q) or die (pg_errormessage());
		if (pg_num_rows($qr) > 0)
		{
			$r = pg_fetch_object($qr);
			$sd = ymd2mdy($r->date);
			$ed = ymd2mdy($r->date);
		}	
	}
	
		
?> 
<form name='fd' id="fd" method='post' action=''>
  <table width="80%" border="0" align="center">
    <tr> 
      <td background="../graphics/table0_horizontal.PNG" bgcolor="#333366"><font color="#FFFFFF" size="2" face="Verdana, Arial, Helvetica, sans-serif"><b>Gross 
        Profit Report</b></font></td>
    </tr>
    <tr> 
      <td><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">Date 
        From 
        <input type="text" name="from_date" value="<?= $from_date;?>" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" size="8">
        <img src="../graphics/arrow_down.jpg" onClick="popUpCalendar(this, form1.from_date, 'mm/dd/yyyy')"> 
        To 
        <input type="text" name="to_date" value="<?= $to_date;?>" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" size="8">
        <img src="../graphics/arrow_down.jpg" onClick="popUpCalendar(this, form1.to_date, 'mm/dd/yyyy')"> 
        <input name="p12" type="button" id="p123" value="Go" onCLick="wait('Please wait. Processing data...');xajax_grossprofit(xajax.getFormValues('fd'));">
        &nbsp; </font> 
        <hr color="#993300"></td>
    </tr>
    <tr> 
      <td bgcolor="#DADADA"><font color="#FFFFFF" size="2" face="Verdana, Arial, Helvetica, sans-serif"><b><font color="#000033">Report 
        Gross Profit Preview</font> </b> 
        <?
		if (file_exists($reportfile))
		{
			echo "| <a href=$reportfile>Download</a>";
		}
		?>
        </font></td>
    </tr>
    <tr>
      <td><textarea name="print_area" id="print_area" cols="110" rows="20" wrap="OFF"><?= $details2;?></textarea></td>
    </tr>
  </table>
  <div align=center>
    <input name="p1" type="submit" id="p1" value="Print Draft" >
    <input name="p122" type="button" id="p122" value="Print"  onClick="printIframe(print_area)" >
  </div>
</form>

<iframe name="printit" id="printit" style="width:0px;height:0px;"></iframe>
