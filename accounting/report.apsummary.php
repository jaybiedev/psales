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

if ($date == '') $date=date('m/d/Y');
if ($p1=='Go' || $p1=='Print Draft')
{
	$mdate = mdy2ymd($date);
	
	$q = "select *
		from 
			account
		where
			account_type_id in ('1','8')
		order by
			account";

	$qr = @pg_query($q) or message(pg_errormessage());
	$header = center($SYSCONF['BUSINESS_NAME'],80)."\n";
	$header .= center('SUMMARY OF ACCOUNTS PAYABLE AS OF '.$date,80)."\n";
	$header .= center('Printed '.date('m/d/Y g:ia'),80)."\n\n";
	$header .= str_repeat('-',100)."\n";
	$header .= "    Supplier        		Address                                  Tel No.        Balance\n";
	$header .= str_repeat('=',100)."\n";
	$details = $details1 = '';
	$details1 = $header;
	$total_amount = $total_net = $total_discount =0;
	$subdivision_id='';
	$ctr=0;
	
//	include_once('accountbalance.php');
	while ($r = @pg_fetch_object($qr))
	{
//		$aBal= supplierBalance($r->account_id);
		if ($aBal['balance'] <= 0 && $show == 'B') continue;
				
		$ctr++;	
		$details .= adjustRight($ctr,3).'. '.adjustSize($r->account,25).' '.
					adjustSize(substr($r->address,0,38),38).' '.
					adjustSize($r->telno,15).'  '.
					adjustRight(number_format($aBal['balance'],2),12)."\n";
		$total_amount+= $aBal['balance'];
	}
	$details .= str_repeat('-',100)."\n";
	$details .= adjustSize('***** TOTALS *****',84).
				adjustRight(number_format($total_amount,2),15)."\n";
	$details .= str_repeat('-',100)."\n";
	$details1 .= $details;
	if ($p1=='Print Draft')
	{
		doPrint($details1);
	}	
}
if ($date == '') $date=date('m/d/Y');	
?>	
<form name="form1" method="post" action="">
  <div align="center">
    <table width="90%" border="0">
	<tr>
        <td height="21" background="../graphics/table_horizontal.PNG"><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Payable 
          Ledger</strong></font></td>
      </tr>
      <tr>
        <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Payables 
          As of</font> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          <input name="date" type="text" id="date"  onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?=$date;?>" size="10">
          </font><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><img src="../graphics/dwn-arrow-grn.gif" onclick="popUpCalendar(this, form1.date, 'mm/dd/yyyy')"></font>
		  <?= lookUpAssoc('show',array('Balance Only'=>'B','All Accounts'=>'A'),$show);?> 
          <input name="p1" type="submit" id="p1" value="Go">
          <hr color="#990000"> </td>
      </tr>
      <tr> 
        <td height="27"><table width="100%" border="0" cellspacing="0" cellpadding="2">
            <tr bgcolor="#000033"> 
              <td nowrap bgcolor="#DADADA"><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
                <img src="../graphics/bluelist.gif" width="16" height="17"> <font color="#333333">Payables 
                Balance Report Preview</font></strong></font> </td>
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
