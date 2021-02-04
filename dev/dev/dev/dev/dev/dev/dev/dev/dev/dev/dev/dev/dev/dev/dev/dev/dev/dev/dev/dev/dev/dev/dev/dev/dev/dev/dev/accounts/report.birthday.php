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

if ($to_date == '') $to_date=date('m/d/Y');
if ($from_date == '') $from_date=addDate($to_date,-30);
if ($account_type_id == '' && $p1 == '') $account_type_id = 5;

if ($p1=='Go' || $p1=='Print Draft')
{
	$mfrom_date = substr(mdy2ymd($from_date),5,5);
	$mto_date = substr(mdy2ymd($to_date),5,5);

	$q = "select 
			*
		from 
			account
		where 
			substr(date_birth,6,5)>='$mfrom_date' and 
			substr(date_birth,6,5)<='$mto_date' and
			enable='Y' ";
	if ($account_type_id != '')
	{
		$q .= " and account_type_id = '$account_type_id'";
	}			
				
	$q .= " order by substr(date_birth,6,5), cardno";
	
	$qr = pg_query($q) or message(pg_errormessage());
	$header .= "\n\n";
	$header .= center($SYSCONF['BUSINESS_NAME'],80)."\n";
	$header .= center('LIST OF BIRTHDAY CELEBRANTS',80)."\n";
	$header .= center($from_date.' To '.$to_date,80)."\n\n";
	$header .= "  #        Account                                  CardNo.    Date  	\n";
	$header .= "-------  ------------------------------------------ -------- ----------- --------\n";
	$details = $details1 = '';
	$total_amount =0;
	$subtotal  = 0;
	$lc=8;
	$ctr=0;
	$this_year = date('Y');
	while ($r = pg_fetch_object($qr))
	{
		$lc++;
		$ctr++;
		$age = $this_year - substr($r->date_birth,0,4);
		$details.= ' '.adjustRight($ctr,4).'.   '.
					adjustSize($r->account,40).'   '.
					adjustSize($r->cardno,8).' '.
					adjustSize(ymd2mdy($r->date_birth),10).'  '.
					adjustRight($age,5).' '.
					"\n";
					
		if ($lc>55 && $p1 == 'Print Draft')
		{
			$details1 .= $header.$details;
			$details .= "<eject>";
			doPrint($header.$details);
			$lc=8;
		}			
	}

	$details .= "-------  ------------------------------------------ -------- ----------- --------\n";
	$details .= space(5).adjustSize('TOTAL COUNT :'.$ctr,25)."\n";
	$details .= "-------  ------------------------------------------ -------- ----------- --------\n";
	
	
	$details1 .= $header.$details;
	if ($p1=='Print Draft')
	{
		$details .= "<eject>";
		doPrint($header.$details);
	}	
}	
?>	
<form name="form1" method="post" action="">
  <div align="center"> 
    <table width="75%" border="0">
      <tr background="../graphics/table0_horizontal.PNG"> 
        <td colspan="4"><font color="#FFFFFF" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong><img src="../graphics/redlist.gif" width="16" height="17"> 
          List of Birthday Celebrants</strong></font></td>
      </tr>
      <tr> 
        <td width="10%" nowrap><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
          Dates From<br>
          </font><font size="3" color="#000000"><font face="Verdana, Arial, Helvetica, sans-serif"><strong><font size="2"> 
          <input name="from_date" type="text" id="from_date"  onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $from_date;?>" size="8">
          </font><font size="2" color="#000000"><img src="../graphics/dwn-arrow-grn.gif" onClick="popUpCalendar(this, form1.from_date, 'mm/dd/yyyy')"></font><font size="2"> 
          </font></strong></font></font><font size="2">&nbsp; </font></td>
        <td width="10%" nowrap><font size="2" color="#000000"><font face="Verdana, Arial, Helvetica, sans-serif">To<br>
          </font> 
          <input type="text" name="to_date" value="<?= $to_date;?>" size="8"  onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')">
          </font><font size="3" color="#000000"><font face="Verdana, Arial, Helvetica, sans-serif"><strong><font size="3" color="#000000"><font face="Verdana, Arial, Helvetica, sans-serif"><strong><font size="2" color="#000000"><img src="../graphics/dwn-arrow-grn.gif" onClick="popUpCalendar(this, form1.to_date, 'mm/dd/yyyy')"></font></strong></font></font></strong></font></font><font face="Verdana, Arial, Helvetica, sans-serif"><strong> 
          </strong></font><font face="Verdana, Arial, Helvetica, sans-serif"><strong> 
          </strong></font></td>
        <td width="17%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Account 
          Type</font><br> <font size="2" color="#000000"> 
          <select name='account_type_id'>
            <option>Select Account Type</option>
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
        <td width="63%" nowrap><br> <input name="p1" type="submit" id="p1" value="Go"> 
        </td>
      </tr>
      <tr> 
        <td colspan="4"> <hr color="red" size="1"> </td>
      </tr>
      <tr background="../graphics/table0_horizontal.PNG"> 
        <td colspan="4"><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>&nbsp;<img src="../graphics/bluelist.gif">Birthday 
          Celebrants Print Preview</strong></font><font  color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
          </strong></font></td>
      </tr>
      <tr>
        <td colspan="4"><textarea name="print_area" cols="92" rows="18" readonly wrap="OFF"><?= $details1;?></textarea></td>
      </tr>
    </table>
    <input name="p1" type="submit" id="p1" value="Print Draft" >
    <input name="p12" type="button" id="p12" value="Print"  onClick="printIframe(print_area)" >
  </div>
</form>

<iframe name="printit" id="printit" style="width:0px;height:0px;"></iframe>
