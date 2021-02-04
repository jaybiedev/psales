 <script language="JavaScript" type="text/JavaScript">
<!--
function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);
//-->
</script>
<?

if ($to_date == '') $to_date=yesterday();
if ($from_date == '') $from_date=yesterday();
if ($account_type_id == '' && $p1 == '') $account_type_id = 5;

if ($p1=='Go' || $p1=='Print Draft')
{
	$from_date = mdy2ymd($from_date);
	$to_date = mdy2ymd($to_date);
	
	$tables = currTables($from_date);
	$sales_header = $tables['sales_header'];
	$sales_detail = $tables['sales_detail'];
	$sales_tender = $tables['sales_tender'];

	$q = "select 
						terminal,
						sum(service_charge) as service_charge
					from
						$sales_header
					where
						date >= '$from_date' and
						date<= '$to_date' and
						$sales_header.status!='V' 
					group by
						terminal
					order by 
						terminal";
						
			
	$qr = @pg_query($q) or message1(pg_errormessage());

	$header .= "\n\n";
	$header .= strtoupper($SYSCONF['BUSINESS_NAME'])."\n";
	$header .= "SALES SERVICE CHARGE REPORT"."\n";
	$header .= "Trans.Date:".ymd2mdy($from_date).' To '.ymd2mdy($to_date)."\n";
	$header .= "Printed: ".date('m/d/Y')."\n\n";
	$header .= " Terminal     Area            Amount \n";
	$header .= " ---- ---------------- ---------------\n";
	$details = $details1 = '';
	$total_amount =0;
	$lc=8;
	$ctr=0;

	while ($r = pg_fetch_object($qr))
	{
		$lc++;
		$ctr++;
		$term = terminal($r->terminal);
		$area = lookUpTableReturnValue('x', 'area', 'area_id','area',$term['AREA_ID']);
		$details.= 	' '.adjustSize($r->terminal,3).'  '.
					adjustSize($area,20).'  '.
					adjustRight(number_format($r->service_charge,2),10)."\n";
			
		$total_service_charge += $r->service_charge;
		if ($lc>55 && $p1 == 'Print Draft')
		{
			$details1 .= $header.$details;
			nPrinter($header.$details, $SYSCONF['REPORT_PRINTER_TYPE'], $SYSCONF['REPORT_PRINTER_DEST']);
			$lc=8;
		}			
	}

	$details .= "----  ---------------- ---------------\n";
	$details .= space(1).adjustSize('TOTAL COUNT : ' .$ctr,27).
					adjustRight(number_format($total_service_charge,2),10)."\n";
	$details .= "----  ---------------- ---------------\n";
	
	$details1 .= $header.$details;
	if ($p1=='Print Draft' )
	{
		$details .= "<eject>";
		nPrinter($header.$details, $SYSCONF['RECEIPT_PRINTER_TYPE'], $SYSCONF['RECEIPT_PRINTER_DEST']);
	}	
	elseif ($p1 == 'Print')
	{
	   	echo "<textarea name='print_area' cols='90' rows='18' readonly wrap='OFF'>$details</textarea></script>";
		echo "<iframe name='printit' id='printit' style='width:0px;height:0px;'></iframe>";
		echo "<script>printIframe(print_area)</script>";
	}
}	
?>	
<form name="form1" method="post" action="">
  <div align="center">
    <table width="75%" border="0">
      <tr background="../graphics/table0_horizontal.PNG"> 
        <td colspan="2"><font color="#FFFFFF" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong><img src="../graphics/redlist.gif" width="16" height="17"> 
          Service Charge Terminal Report</strong></font></td>
      </tr>
      <tr> 
        <td width="10%" nowrap><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
          Dates From<br>
          </font><font size="3" color="#000000"><font face="Verdana, Arial, Helvetica, sans-serif"><strong><font size="2"> 
          <input name="from_date" type="text" id="from_date"  onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= ymd2mdy($from_date);?>" size="8">
          </font><font size="2" color="#000000"><img src="../graphics/dwn-arrow-grn.gif" onClick="popUpCalendar(this, form1.from_date, 'mm/dd/yyyy')"></font><font size="2"> 
          </font></strong></font></font><font size="2">&nbsp; </font></font></font></td>
        <td nowrap><font size="2" color="#000000"><font face="Verdana, Arial, Helvetica, sans-serif">To<br>
          </font> 
          <input type="text" name="to_date" value="<?= ymd2mdy($to_date);?>" size="8"  onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')">
          </font><font size="3" color="#000000"><font face="Verdana, Arial, Helvetica, sans-serif"><strong><font size="2" color="#000000"><img src="../graphics/dwn-arrow-grn.gif" onClick="popUpCalendar(this, form1.to_date, 'mm/dd/yyyy')"></font></strong></font></font><font size="2" color="#000000"></strong> 
          </font>
<input name="p1" type="submit" id="p1" value="Go"> 
        </td>
      </tr>
      <tr> 
        <td colspan="2"> <hr color="red" size="1"> </td>
      </tr>
      <tr bgcolor="#C4D0DF"> 
        <td colspan="2"><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>&nbsp;<img src="../graphics/bluelist.gif"> 
          Service Charge Terminal Report Preview</strong></font><font  color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
          </strong></font></td>
      </tr>
      <tr> 
        <td colspan="2"><textarea name="print_area" cols="110" rows="18" readonly wrap="OFF"><?= $details1;?></textarea></td>
      </tr>
    </table>
    <input name="p1" type="submit" id="p1" value="Print Draft" >
    <input name="p12" type="button" id="p12" value="Print"  onClick="printIframe(print_area)" >
  </div>
</form>

<iframe name="printit" id="printit" style="width:0px;height:0px;"></iframe>
