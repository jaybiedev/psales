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
			enable='Y' ";

	if ($account_type_id != '')
	{
		$q .= " and account_type_id = '$account_type_id'";
	}			
	$q .= " order by account";
	
	$qr = @pg_query($q) or message(pg_errormessage());
	$header .= "\n\n";
	$header .= center($SYSCONF['BUSINESS_NAME'],80)."\n";
	$header .= center('LIST OF ACCOUNTS',80)."\n";
	$header .= center($from_date.' To '.$to_date,80)."\n\n";
	$header .= "  #        Account                                    Telephone	\n";
	$header .= "-------  ------------------------------------------ ------------------\n";
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
					adjustSize($r->telno,20).'  '.
					"\n";
					
		if ($lc>55 && $p1 == 'Print Draft')
		{
			$details1 .= $header.$details;
			$details .= "<eject>";
			doPrint($header.$details);
			$lc=8;
		}			
	}

	$details .= "-------  ------------------------------------------ ----------- --------\n";
	$details .= space(5).adjustSize('TOTAL COUNT :'.$ctr,25)."\n";
	$details .= "-------  ------------------------------------------ ----------- --------\n";
	
	
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
    <table width="80%" border="0">
      <tr>
        <td><font size="3" color="#000000">Account Type
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
          <input name="p1" type="submit" id="p1" value="Go">
          </font>
          <hr color="red">
        </td>
      </tr>
    </table>
    <table width="1%" border="0" align="center" cellpadding="0" cellspacing="1">
      <tr> 
        <td height="27" bgcolor="#330066"> <font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          <strong> &nbsp;<img src="../graphics/bluelist.gif">Accounts Print Preview</strong></font><font  color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
          </strong></font></td>
      </tr>
      <tr> 
        <td valign="top" bgcolor="#FFFFFF">
	   <textarea name="print_area" cols="90" rows="18" readonly wrap="OFF"><?= $details1;?></textarea>
        </td>
      </tr>
    </table>
    <input name="p1" type="submit" id="p1" value="Print Draft" >
    <input name="p12" type="button" id="p12" value="Print"  onClick="printIframe(print_area)" >
  </div>
</form>

<iframe name="printit" id="printit" style="width:0px;height:0px;"></iframe>
