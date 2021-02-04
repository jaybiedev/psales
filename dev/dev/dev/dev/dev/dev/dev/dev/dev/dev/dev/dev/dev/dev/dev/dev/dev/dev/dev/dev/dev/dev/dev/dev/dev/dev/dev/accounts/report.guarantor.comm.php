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
if ($from_date == '')
{
	$from_date = date('m/d/Y');
}	
if ($p1=='Go' || $p1=='Print Draft')
{
	if ($from_date == '')
	{
		$from_date = date('m/d/Y');
	}	
	$mdate = mdy2ymd($from_date);
	
	$q = "select *
			
		from 
			account
		where 
			account_type_id='3' and 
			enable='Y' ";
	if ($account_id != '')
	{
		$q .= " and account_id='$account_id'";
	}
	$q .= "order by  $sort ";
	$qr = pg_query($q) or message(pg_error());

	$header = "\n\n";
	$header .= center($SYSCONF['BUSINESS_NAME'],130)."\n";
	$header .= center('GUARANTOR COMMISSION REPORT',130)."\n";
	$header .= center('As of Date '.$from_date,130)."\n\n";
	$header .= "----- ---------- ------------------------------- ------------- ------------- ------------- ------------- -------------\n";
	$header .= "        Code     Guarantor Name                   CreditLimit  CreditBalance  BondDeposit    Commission     Interest \n";
	$header .= "----- ---------- ------------------------------- ------------- ------------- ------------- ------------- -------------\n";

	if ($p1 == 'Print Draft')
	{
		doPrint("<small3>");
	}
	$details = $details1 = '';
	$ctr=$total_cost = 0;
	$maccount_id = $mcategory_id = 'x~';
	$lc=8;
	$mcount=$subtotal_cost=$scount=0;
	while ($r = pg_fetch_object($qr))
	{
		$ctr++;

		$details .= adjustRight($ctr,4).'. '.
					adjustSize($r->account_code,10).' '.
					adjustSize(substr($r->account,0,30),30).' '.
					adjustRight(number_format($r->credit_limit,0),13).' '.
					adjustRight(number_format($r->credit_balance,2),13).' '.
					adjustRight(number_format($r->bond,2),13).' '.
					adjustRight(number_format($r->commission,2),13).' '.
					adjustRight(number_format($r->interest,2),13)."\n";
		$lc++;
		
		$total_limit += $r->credit_limit;
		$total_balance += $r->credit_balance;
		$total_commission+= $r->commission;
		$total_interest += $r->interest;

		if ($lc>55)
		{
			$details1 .= $header.$details;
			if ($p1 == 'Print Draft')
			{
				doPrint($header.$details."<eject>");
			}
			$lc=8;
			$details = '';
		}

	}
	$details  .= "----- ---------- ------------------------------- ------------- ------------- ------------- ------------- -------------\n";
	$details .= space(6). adjustSize($ctr.' Item/s',28).adjustSize('Grand Total :',14).
						adjustRight(number_format($total_limit,0),13).' '.
						adjustRight(number_format($total_balance,2),13).' '.
						adjustRight(number_format($total_bond,2),13).' '.
						adjustRight(number_format($total_commission,2),13).' '.
						adjustRight(number_format($total_interest,2),13)."\n";

	$details  .= "----- ---------- ------------------------------- ------------- ------------- ------------- ------------- -------------\n";
	$details1 .= $header.$details;
	if ($p1=='Print Draft')
	{
		doPrint($header.$details);
	}	
}
else
{
	$incZero = 1;
}
if ($from_date == '') $from_date=date('m/d/Y');	
?>	
<form name="form1" method="post" action="">
  <div align="center">
    <table width="80%" border="0" cellspacing="1" cellpadding="0" bgcolor="#FFFFFF">
      <tr bgcolor="#EFEFEF"> 
        <td width="17%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Guarantor 
          Commission</strong> </font></td>
        <td width="5%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          Sort</font></td>
        <td width="7%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
          </font><font color="#000000">&nbsp;</font> <font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">As 
          of</font></td>
        <td width="71%" nowrap>&nbsp;</td>
      </tr>
      <tr> 
        <td><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
          </font><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
          </font></td>
        <td width="5%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          <?=lookUpAssoc('sort',array('Guarantor Code'=>'account_code','Name'=>'account_code'),$sort);?>
          </font> </td>
        <td width="7%" nowrap align="center"><input name="from_date" type="text" id="from_date" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $from_date;?>" size="8"> 
          <img src="../graphics/arrow_down.jpg" onClick="popUpCalendar(this, form1.from_date, 'mm/dd/yyyy')"> 
        </td>
        <td width="71%" nowrap>&nbsp; <input name="p1" type="submit" id="p1" value="Go"> 
        </td>
      </tr>
    </table>
    <table width="1%" border="0" align="center" cellpadding="2" cellspacing="1">
      <tr> 
        <td height="27"><table width="100%" border="0" cellspacing="0" cellpadding="2">
            <tr bgcolor="#000033"> 
              <td nowrap><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
                <img src="../graphics/bluelist.gif" width="16" height="17">Guarantor 
                Commissions Preview</strong></font> </td>
            </tr>
          </table></td>
      </tr>
      <tr> 
        <td valign="top" bgcolor="#FFFFFF">
	   <textarea name="print_area" cols="110" rows="20"  wrap="off" readonly><?= $details1;?></textarea>
        </td>
      </tr>
    </table>
    <input name="p1" type="submit" id="p1" value="Print Draft" >
    <input name="p12" type="button" id="p12" value="Print"  onClick="printIframe(print_area)" >
  </div>
</form>

<iframe name="printit" id="printit" style="width:0px;height:0px;"></iframe>
