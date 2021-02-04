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
	$mfrom_date = mdy2ymd($from_date);
	$mto_date = mdy2ymd($to_date);
	
	$tables = currTables($mto_date);
	$sales_header = $tables['sales_header'];
	$sales_detail = $tables['sales_detail'];
	$sales_tender = $tables['sales_tender'];


	$q = "select 
					*
		from 
			account
		where 
				enable ='Y'  and
				account_type_id='5' ";
	if ($account_class_id != '')
	{
		$q .= " and account.account_class_id = '$account_class_id'";
	}			
				
	if ($sort == 'A')
	{
		$q .= "	order by account ";
	}
	if ($sort == 'C')
	{
		$q .= "	order by cardno ";
	}
	if ($sort == 'G')
	{
		$q .= "	order by guarantor_id ";
	}
	
	$qr = @pg_query($q) or message1(pg_errormessage());

	$header .= "\n\n";
	$header .= center($SYSCONF['BUSINESS_NAME'],80)."\n";
	$header .= center("LIST OF ACTIVE  CUSTOMER ACCOUNTS",80)."\n";
	$header .= center("AS OF ".date('m/d/Y'),80)."\n\n";
	$header .= "  #        Account                   CardNo     TelNo.         Address  	\n";
	$header .= "-------  ------------------------- ---------- --------------- ------------------------------\n";
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
		$details.= ' '.adjustRight($ctr,4).'.   '.
					adjustSize($r->account,25).' '.
					adjustSize($r->cardno,10).' '.
					adjustSize($r->telno,15).'  '.
					adjustSize($r->address,35).'  '.
					"\n";
					
		if ($lc>55 && $p1 == 'Print Draft')
		{
			$details1 .= $header.$details;
			$details .= "<eject>";
			doPrint($header.$details);
			$details = '';
			$lc=8;
		}			
	}

	$details .= "-------  ------------------------- ---------- --------------- ------------------------------\n";
	$details .= space(5).adjustSize('TOTAL COUNT :'.$ctr,25)."\n";
	$details .= "-------  ------------------------- ---------- --------------- ------------------------------\n";
	
	
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
        <td colspan="3"><font color="#FFFFFF" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong><img src="../graphics/redlist.gif" width="16" height="17"> 
          List Customer Accounts</strong></font></td>
      </tr>
      <tr> 
        <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Sort<br>
          <?=lookUpAssoc('sort',array('Alphabetical'=>'A','Card No'=>'C','Group By Guarantor'=>'G'),$sort);?>
          </font></td>
        <td width="17%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Account 
          Classification</font><br> <font size="2" color="#000000"> 
          <select name='account_class_id' style="width:240px">
            <option value="">All Accounts</option>
            <?
			$q = "select * from account_class where enable='Y' order by account_class";
			$qr = @pg_query($q);
			while ($r = @pg_fetch_object($qr))
			{
				if ($r->account_class_id == $account_class_id)
				{
					echo "<option value=$r->account_class_id selected>$r->account_class</option>";
				}
				else
				{
					echo "<option value=$r->account_class_id >$r->account_class</option>";
				}
			}
			?>
          </select>
          </font> </td>
        <td width="63%" nowrap><br> <input name="p1" type="submit" id="p1" value="Go"> 
        </td>
      </tr>
      <tr background="../graphics/table0_horizontal.PNG"> 
        <td colspan="3"><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>&nbsp;<img src="../graphics/bluelist.gif"> 
          List Customer Accounts Print Preview</strong></font><font  color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
          </strong></font></td>
      </tr>
      <tr> 
        <td colspan="3"><textarea name="print_area" cols="110" rows="18" readonly wrap="OFF"><?= $details1;?></textarea></td>
      </tr>
    </table>
    <input name="p1" type="submit" id="p1" value="Print Draft" >
    <input name="p12" type="button" id="p12" value="Print"  onClick="printIframe(print_area)" >
  </div>
</form>

<iframe name="printit" id="printit" style="width:0px;height:0px;"></iframe>
