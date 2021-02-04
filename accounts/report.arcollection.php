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

if ($to_date == '') $to_date=ymd2mdy(yesterday());
if ($from_date == '') $from_date=ymd2mdy(yesterday()); //addDate($to_date,-30);
if ($account_type_id == '' && $p1 == '') $account_type_id = 5;

if ($p1=='Go' || $p1=='Print Draft')
{
	$mfrom_date = mdy2ymd($from_date);
	$mto_date = mdy2ymd($to_date);

	$q = "select 
						*
					from
						accountledger,
						account
					where
						account.account_id=accountledger.account_id and
						date >= '$mfrom_date' and
						date<= '$mto_date' and
						accountledger.status='S'  and
						accountledger.type='P'";
			if ($account_type_id != '')
			{
				$q .= " and account.account_type_id = '$account_type_id'";
			}
			$q .=" order by account.cardno ";
						
			
	$qr = @pg_query($q) or message1(pg_errormessage());

	$header .= "\n\n";
	$header .= center($SYSCONF['BUSINESS_NAME'],80)."\n";
	$header .= center("A/R COLLECTION",80)."\n";
	$header .= center($from_date.' To '.$to_date,80)."\n\n";
	$header .= "  #       Card No.    Account                   Reference    Amount     Remark \n";
	$header .= "-------  ---------- --------------------------- ---------- ------------ ----- \n";
	$details = $details1 = '';
	$total_grocery = $total_drygoods = $total_amount =0;
	$subtotal  = 0;
	$lc=8;
	$ctr=0;

	while ($r = pg_fetch_object($qr))
	{
		$lc++;
		$ctr++;
		$details.= ' '.adjustRight($ctr,4).'.   '.
					adjustSize($r->cardno,10).'  '.
					adjustSize($r->account,25).'  '.
					adjustSize($r->invoice,10).'  '.
					adjustRight(number_format($r->credit,2),11).'  '.
					adjustRight($r->dept,1).	"\n";
			
		$total_amount  += $r->credit;
		if ($lc>55 && $p1 == 'Print Draft')
		{
			$details1 .= $header.$details;
			writefile($header.$details,$page);
			$details .= "<eject>";
			nPrinter($header.$details, $SYSCONF['REPORT_PRINTER_TYPE'], $SYSCONF['REPORT_PRINTER_DEST']);
			sleep(5);
			$lc=8;
			$details = '';
			$page++;
		}			
	}

	$details .= "-------  ---------- --------------------------- ---------- ------------ ----- \n";
	$details .= space(9).adjustSize('TOTAL COUNT :' .$ctr,50).
						adjustRight(number_format($total_amount,2),12)."\n";
	$details .= "-------  ---------- --------------------------- ---------- ------------ ----- \n";
	
//	$details .= "Total Grocery  : ". adjustRight(number_format($total_grocery,2),12)."\n";
//	$details .= "Total Drygoods : ". adjustRight(number_format($total_drygoods,2),12)."\n\n\n\n";
	$details1 .= $header.$details;
	if ($p1=='Print Draft')
	{
		writefile($header.$details);
		$details .= "<eject>";
		nPrinter($header.$details, $SYSCONF['REPORT_PRINTER_TYPE'], $SYSCONF['REPORT_PRINTER_DEST']);
	}	
	elseif ($p1 == 'Print')
	{
	   	echo "<textarea name='print_area' cols='90' rows='18' readonly wrap='OFF'>$details1</textarea></script>";
		echo "<iframe name='printit' id='printit' style='width:0px;height:0px;'></iframe>";
		echo "<script>printIframe(print_area)</script>";
	}
}	
?>	
<form name="form1" method="post" action="">
  <div align="center">
    <table width="75%" border="0">
      <tr background="../graphics/table0_horizontal.PNG"> 
        <td colspan="4"><font color="#FFFFFF" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong><img src="../graphics/redlist.gif" width="16" height="17"> 
          A/R Collection Report</strong></font></td>
      </tr>
      <tr> 
        <td width="10%" nowrap><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
          Dates From<br>
          </font><font size="3" color="#000000"><font face="Verdana, Arial, Helvetica, sans-serif"><strong><font size="2"> 
          <input name="from_date" type="text" id="from_date"  onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $from_date;?>" size="8">
          </font><font size="2" color="#000000"><img src="../graphics/dwn-arrow-grn.gif" onClick="popUpCalendar(this, form1.from_date, 'mm/dd/yyyy')"></font><font size="2"> 
          </font></strong></font></font><font size="2">&nbsp; </font></font></font></td>
        <td width="10%" nowrap><font size="2" color="#000000"><font face="Verdana, Arial, Helvetica, sans-serif">To<br>
          </font> 
          <input type="text" name="to_date" value="<?= $to_date;?>" size="8"  onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')"></strong> 
          </font><font size="3" color="#000000"><font face="Verdana, Arial, Helvetica, sans-serif"><strong><font size="3" color="#000000"><font face="Verdana, Arial, Helvetica, sans-serif"><strong><font size="2" color="#000000"><img src="../graphics/dwn-arrow-grn.gif" onClick="popUpCalendar(this, form1.to_date, 'mm/dd/yyyy')"></font></strong></font></font></strong></font></font><font face="Verdana, Arial, Helvetica, sans-serif"><strong> 
          </strong></font></font><font face="Verdana, Arial, Helvetica, sans-serif"><strong> 
          </strong></font></font></td>
        <td width="17%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Account 
          Type</font><br> <font size="2" color="#000000"> 
          <select name='account_type_id'>
            <option>All Accounts</option>
            <?
			$q = "select * from account_type where enable='Y' order by account_type";
			$qr = @pg_query($q);
			while ($r = @pg_fetch_object($qr))
			{
				if ($r->account_type_id == $account_type_id)
				{
					echo "<option value=$r->account_type_id selected>$r->account_type</option>";
				}
				else
				{
					echo "<option value=$r->account_type_id>$r->account_type</option>";
				}
			}
			?>
          </select>
          </font> </td>
        <td width="63%" nowrap><br> <input name="p1" type="submit" id="p1" value="Go">        </td>
      </tr>
      <tr> 
        <td colspan="4"> <hr color="red" size="1"> </td>
      </tr>
      <tr bgcolor="#C4D0DF"> 
        <td colspan="4"><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>&nbsp;<img src="../graphics/bluelist.gif"> 
          A/R   Collection
          Print Preview</strong></font></td>
      </tr>
      <tr>
        <td colspan="4"><textarea name="print_area" cols="100" rows="18" readonly wrap="OFF"><?= $details1;?></textarea></td>
      </tr>
    </table>
    <input name="p1" type="submit" id="p1" value="Print Draft" >
    <input name="p12" type="button" id="p12" value="Print"  onClick="printIframe(print_area)" >
  </div>
</form>

<iframe name="printit" id="printit" style="width:0px;height:0px;"></iframe>
