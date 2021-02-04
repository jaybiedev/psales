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

	$q = "select 
			ph.paymast_id,
			ph.reference,
			idnum,
			elast,
			efirst,
			ph.deduction_type_id,
			deduction_type,
			(credit-debit) as total_charge
			
		from 
			payrollcharge as ph,
			paymast,
			deduction_type
		where
			paymast.paymast_id = ph.paymast_id and
			deduction_type.deduction_type_id=ph.deduction_type_id and 
			ph.date>='$mfrom_date' and
			ph.date<='$mto_date'";
		
	if ($deduction_type_id == '')
	{
		$subtitle .= 'ALL CHARGES';
	}
	else
	{
		$q .= " and ph.deduction_type_id = '$deduction_type_id'";
		$subtitle .= lookUpTableReturnValue('x','deduction_type','deduction_type_id','deduction_type',$deduction_type_id)."\n";
	}
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

	$q .="	order by department_id, section_id, elast, efirst";
	
	$qr = @pg_query($q) or message(pg_errormessage().$q);

	if ($p1 == 'Print Draft') $details .= "<small3>";

	$header .= "\n\n";
	$header .= center($SYSCONF['BUSINESS_NAME'],80)."\n";
	$header .= center('CHARGES LISTING FOR THE PERIOD',80)."\n";
	$header .=  center($subtitle,80)."\n";
	$header .= center('Payroll Period :'.$based,80)."\n";
	$header .= center('Printed: '.date('m/d/Y g:ia'),80)."\n\n";
	$header .= "---- ---------------------------------------- --------------- --------------------- ------------\n";
	$header .= "      EMPLOYEE NAME                              REFERENCE     PARTICULARS              AMOUNT \n";
	$header .= "---- ---------------------------------------- --------------- --------------------- ------------\n";
	$details = $details1 = '';
	$details1 = $header;
	$ctr = $total_amount = 0;
	$lc=8;
	while ($r = @pg_fetch_object($qr))
	{
		$ctr++;
		$income = $r->net_income;
		if ($income == '') $income=0;
		$q = "select * from phictable where income_from <= '$income' and income_to >= '$income'";
		$rr = fetch_object($q);
			
		$details .= adjustRight($ctr,3).'. '.
					adjustSize($r->elast.', '.$r->efirst,40).' '.
					adjustSize($r->reference,15).' '.
					adjustSize($r->deduction_type,20).' '.
					adjustRight(number_format($r->total_charge,2),12)."\n";
		$lc++;
		
		$total_total_charge += $r->total_charge;
		
		if ($lc>55 && $p1 == 'Print Draft')
		{
			doPrint($header.$details."<eject>");
			$lc=8;
			$details1 .= $header.$details;
			$details = '';
		}
	}
	$details .= "---- ---------------------------------------- --------------- --------------------- ------------\n";
	$details .= space(5).adjustSize($ctr.' Items(s)',40).' '.
					adjustSize('TOTALS: ',15).'  '.
					space(20).
					adjustRight(number_format($total_total_charge,2),12)."\n";
	$details .= "---- ---------------------------------------- --------------- --------------------- ------------\n";

	$details1 .= $details;
	if ($p1=='Print Draft')
	{
		$details .= "<eject>";
		doPrint($header.$details);
	}	
}
?>	
<br>
<form name="f1" id="f1" method="post" action="">
  <div align="center"> 
    <table width="75%" border="0" align="center" cellpadding="1" cellspacing="1">
      <tr> 
        <td height="22" colspan="5" background="../graphics/table_horizontal.PNG">&nbsp; 
          <font color="#EFEFEF" size="2" face="Verdana, Arial, Helvetica, sans-serif">.::
		  <strong> Charges Listing</strong></font></td>
      </tr>
      <tr> 
        <td width="11%" height="27" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          From<br>
          <strong> </strong> 
          <input name="from_date" type="text" id="from_date" value="<?= $from_date;?>" size="10">
          <img src="../graphics/dwn-arrow-grn.gif" onclick="popUpCalendar(this, f1.from_date, 'mm/dd/yyyy')"> 
          <strong> </strong> </font></td>
        <td width="5%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">To<br>
          <strong> </strong> 
          <input name="to_date" type="text" id="to_date" value="<?= $to_date;?>" size="10">
          <strong> </strong> <img src="../graphics/dwn-arrow-grn.gif" onclick="popUpCalendar(this, f1.to_date, 'mm/dd/yyyy')"> 
          </font></td>
        <td width="6%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Deduction<br>
          <select name="deduction_type_id"  id="deduction_type_id"  style="width:150">
            <option value=''>All Charges</option>
            <?
			$q = "select * from deduction_type where enable='Y' order by deduction_type";
			$qr = @pg_query($q) or message(pg_errormessage().$q);
			while ($r = @pg_fetch_object($qr))
			{
				if ($r->deduction_type_id == $deduction_type_id)
				{
					echo "<option value=$r->deduction_type_id selected>$r->deduction_type</option>";
				}
				else
				{
					echo "<option value=$r->deduction_type_id>$r->deduction_type</option>";
				}	
			}
			?>
          </select>
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
        <td height="27" colspan="5"><table width="100%" border="0" cellspacing="0" cellpadding="2">
            <tr bgcolor="#000033"> 
              <td nowrap bgcolor="#DADADA"><font color="#666666" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
                <img src="../graphics/bluelist.gif" width="16" height="17"> Charges 
                Listing Preview</strong></font></td>
            </tr>
          </table></td>
      </tr>
      <tr> 
        <td colspan="5" valign="top" bgcolor="#FFFFFF"> <textarea name="print_area" cols="110" rows="20"  wrap="off" readonly><?= $details1;?></textarea> 
        </td>
      </tr>
    </table>
    <input name="p1" type="submit" id="p1" value="Print Draft" >
    <input name="p12" type="button" id="p12" value="Print"  onClick="printIframe(print_area)" >
  </div>
</form>

<iframe name="printit" id="printit" style="width:0px;height:0px;"></iframe>
