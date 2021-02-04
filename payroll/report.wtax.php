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

if ($from_date == '') $from_date = substr($SYSCONF['PAYROLL_PERIOD'],0,10);
if ($to_date == '') $to_date = substr($SYSCONF['PAYROLL_PERIOD'],11,10);

if (($p1=='Go' || $p1=='Print Draft') )
{
	$date = date('m/d/Y');
	$from_date = $_REQUEST['from_date'];
	$to_date = $_REQUEST['to_date'];
	$mfrom_date = mdy2ymd($from_date);
	$mto_date = mdy2ymd($to_date);
	
	if ($collate == '1')
	{
		$based = 'Whole Month';
	}
	else
	{
		$based = $from_date.' To '.$to_date;
	}
	$pid = rangePayrollPeriod($mfrom_date, $mto_date);

			
	$q = "select 
			sum(total_tax) as total_tax,
			ph.paymast_id,
			elast,
			efirst,
			tin 
		from 
			payroll_header as ph,
			paymast
		where
			paymast.paymast_id = ph.paymast_id and
			ph.total_tax<>0 and 
			ph.payroll_period_id in ($pid)";
			
	if ($branch_id == '')
	{
		$subtitle .= " ALL BRANCHES ";
	}
	else
	{
		$subtitle .= strtoupper(lookUpTableReturnValue('x','branch','branch_id','branch',$branch_id)).'  BRANCH';
		$q .= " and paymast.branch_id = '$branch_id'";
	}
	if ($rank== '')
	{
		$subtitle .= ' ALL RANKS';
	}
	elseif ($rank == 'R')
	{
		$q .= " and rank = 'R'";
		$subtitle .= ' RANK  AND FILE';
	}
	elseif ($rank == 'S')
	{
		$q .= " and rank = 'S'";
		$subtitle .= ' SUPERVISORS';
	}
			
	$q .="	group by
			ph.paymast_id,
			elast,
			efirst,
			tin";

	if ($p1 == 'Print Draft') $details .= "<small3>";

	$header .= "\n\n";
	$header .= center($SYSCONF['BUSINESS_NAME'],80)."\n";
	$header .= center('TAX WITHELD SUMMARY',80)."\n";
	$header .=  center($subtitle,80)."\n";
	$header .= center('Payroll Period :'.$based,80)."\n";
	$header .= center('Printed: '.date('m/d/Y g:ia'),80)."\n\n";
	$header .= "---- ---------------------------------------- ------------------ -------------- \n";
	$header .= "      EMPLOYEE NAME                                TIN #             AMOUNT      \n";
	$header .= "---- ---------------------------------------- ------------------ -------------- \n";
	$details = $details1 = '';
	$details1 = $header;
	$ctr = $total_amount = 0;
	$lc=8;
	while ($r = @pg_fetch_object($qr))
	{
		$ctr++;
			
		$details .= adjustRight($ctr,3).'. '.
					adjustSize($r->elast.', '.$r->efirst,40).' '.
					adjustSize($r->tin,18).' '.
					adjustRight(number_format($r->total_tax,2),14)."\n";
		$lc++;
		
		$total_total_tax += $r->total_tax;
		
		if ($lc>55 && $p1 == 'Print Draft')
		{
			doPrint($header.$details."<eject>");
			$lc=8;
			$details1 .= $header.$details;
			$details = '';
		}
	}
	$details .= "---- ---------------------------------------- ------------------ --------------\n";
	$details .= space(45).' '.
					adjustSize('TOTALS: ',15).'    '.
					adjustRight(number_format($total_total_tax,2),14)."\n";
	$details .= "---- ---------------------------------------- ------------------ --------------\n";

	$details1 .= $details;
	if ($p1=='Print Draft')
	{
		$details .= "<eject>";
		doPrint($header.$details);
	}	
}
?>	
<br>
<form name="form1" method="post" action="">
  <div align="center"> 
    <table width="85%" border="0" align="center" cellpadding="1" cellspacing="1">
      <tr> 
        <td height="27" colspan="4" background="../graphics/table_horizontal.PNG">&nbsp; 
          <font color="#EFEFEF" size="2" face="Verdana, Arial, Helvetica, sans-serif">.:: 
          <strong>Tax Withheld Summary</strong></font></td>
      </tr>
      <tr> 
        <td width="11%" height="27" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          From<br>
          <strong> </strong> 
          <input name="from_date" type="text" id="from_date" value="<?= $from_date;?>" size="10">
          <img src="../graphics/dwn-arrow-grn.gif" onclick="popUpCalendar(this, f1.from_date, 'mm/dd/yyyy')"> 
          <strong> </strong> </font></td>
        <td width="11%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">To<br>
          <strong> </strong> 
          <input name="to_date" type="text" id="to_date" value="<?= $to_date;?>" size="10">
          <strong> </strong> <img src="../graphics/dwn-arrow-grn.gif" onclick="popUpCalendar(this, f1.to_date, 'mm/dd/yyyy')"> 
          </font></td>
        <td width="21%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Branch 
          <br>
          <select name="branch_id"  id="branch_id"  style="width:150">
            <option value=''>All Branches</option>
            <?
			$q = "select * from branch where enable='Y' order by branch";
			$qr = @pg_query($q) or message(pg_errormessage().$q);
			while ($r = @pg_fetch_object($qr))
			{
				if ($r->branch_id == $branch_id)
				{
					echo "<option value=$r->branch_id selected>$r->branch</option>";
				}
				else
				{
					echo "<option value=$r->branch_id>$r->branch</option>";
				}	
			}
			?>
          </select>
          </font></td>
        <td width="57%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Rank<br>
          <?= lookUpAssoc('rank',array('All Ranks'=>'','Rank & File'=>'R', 'Supervisor'=>'S'), $rank);?>
          <input name="p1" type="submit" id="p1" value="Go">
          </font></td>
      </tr>
      <tr> 
      <tr> 
        <td height="27" colspan="4"><table width="100%" border="0" cellspacing="0" cellpadding="2">
            <tr bgcolor="#000033"> 
              <td nowrap bgcolor="#DADADA"><font color="#666666" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
                <img src="../graphics/bluelist.gif" width="16" height="17"> Report 
                Preview</strong></font></td>
            </tr>
          </table></td>
      </tr>
      <tr> 
        <td colspan="4" valign="top" bgcolor="#FFFFFF"> <textarea name="print_area" cols="90" rows="20"  wrap="off" readonly><?= $details1;?></textarea> 
        </td>
      </tr>
    </table>
    <input name="p1" type="submit" id="p1" value="Print Draft" >
    <input name="p12" type="button" id="p12" value="Print"  onClick="printIframe(print_area)" >
  </div>
</form>

<iframe name="printit" id="printit" style="width:0px;height:0px;"></iframe>
