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

	if ($SYSCONF['NUM']  == 1)
	{
		$from_date = substr($SYSCONF['PAYROLL_PERIOD'],0,10);
	}
	else
	{
		$month = $SYSCONF['MONTH'];
		$q = "select * from payroll_period where month = '$month' and year = '".$SYSCONF['YEAR']."' and enable='Y' order by period1 offset 0 limit 1";
		$qr = @pg_query($q);
		$r = @pg_fetch_object($qr);
		$from_date = ymd2mdy($r->period1);
	}
}
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
			ph.paymast_id,
			elast,
			efirst,
			pagibigid,
			sum(total_pagibig) as total_pagibig,
			sum(net_income) as net_income,
			sum(total_income) as total_income,
			department_id,
			fixed_sss,
			fixed_phic,
			fixed_wtax,
			fixed_pagibig,
			fixed_ssse,
			fixed_phice,
			fixed_wtaxe,
			fixed_pagibige
			
		from 
			payroll_header as ph,
			paymast
		where
			paymast.paymast_id = ph.paymast_id and
			ph.total_pagibig<>0 and 
			ph.status!='C' and 
			ph.payroll_period_id in ($pid) ";
		
	if ($branch_id == '')
	{
		$subtitle .= " ALL BRANCHES ";
	}
	else
	{
		$subtitle .= strtoupper(lookUpTableReturnValue('x','branch','branch_id','branch',$branch_id)).'  BRANCH';
		$q .= " and paymast.branch_id = '$branch_id'";
	}
	if ($department_id != '')
	{
		$q .= " and department_id = '$department_id'";
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
			pagibigid,
			department_id,
			fixed_sss,
			fixed_phic,
			fixed_wtax,
			fixed_pagibig,
			fixed_ssse,
			fixed_phice,
			fixed_wtaxe,
			fixed_pagibige
		order by
			department_id, upper(elast), efirst";
	
	$qr = @pg_query($q) or message(pg_errormessage().$q);

	if ($p1 == 'Print Draft') $header .= "<small3>";

	$header .= "\n\n";
	$header .= center($SYSCONF['BUSINESS_NAME'],120)."\n";
	$header .= center('PAG-IBIG REPORT SUMMARY',120)."\n";
	$header .=  center($subtitle,120)."\n";
	$header .= center('Payroll Period :'.$based,120)."\n";
	$header .= center('Printed: '.date('m/d/Y g:ia'),120)."\n\n";
	$header .= "---- ---------------------------------------- --------------- -------------- ------------ ------------ --------------\n";
	$header .= "      EMPLOYEE NAME                              PAG-IBIG#     GROSS INCOME    EMPLOYEE     EMPLOYER       TOTAL \n";
	$header .= "---- ---------------------------------------- --------------- -------------- ------------ ------------ --------------\n";
	$details = $details1 = '';
	$details1 = $header;
	$ctr = $total_amount = 0;
	$lc=8;
	while ($r = @pg_fetch_object($qr))
	{
		if ($mdepartment_id != $r->department_id )
		{
			$details .= "\nDEPARTMENT : ".strtoupper(@lookUpTableReturnValue('x','department','department_id','department',$r->department_id))."\n";
			$mdepartment_id = $r->department_id;
		}
		$ctr++;
		$income = $r->total_income;
		if ($income == '') $income=0;
		if ($show != 'D')
		{
			//$total_pagibig = $income * 0.02;
			$total_pagibig = 100;
			$accu_pagibig = $total_pagibig;
			$accu_pagibig = $r->total_pagibig;
			if ($total_pagibig > 100) $total_pagibig = 100;
		}			
		else
		{
			$total_pagibig = $r->total_pagibig;
		}	
		
		if ($r->fixed_pagibige*1 == '0')
		{
			if ($total_pagibig > 100) $employer = 100;
			else	$employer = $total_pagibig;
		}
		else
		{
			$employer = $r->fixed_pagibige;
		}

		$details .= adjustRight($ctr,3).'. '.
					adjustSize($r->elast.', '.$r->efirst,40).' '.
					adjustSize($r->pagibigid,15).' '.
					adjustRight(number_format($income,2),14).' '.
					adjustRight(number_format($total_pagibig,2),12).' '.
					adjustRight(number_format($employer,2),12).' '.
					adjustRight(number_format($total_pagibig+$employer,2),14);
		if ($show == 'V')
		{
			$details .= adjustRight(number_format($accu_pagibig,2),10).' '.
							adjustRight(number_format($total_pagibig - $accu_pagibig,2),10)."\n";
		}
		else
		{
			$details .= "\n";
		}
		$lc++;
		
		$total_total_pagibig += $total_pagibig;
		$total_income += $income;
		$total_employer += $employer;
		$total_ecc += $rr->ecc;
		
		if ($lc>55 && $p1 == 'Print Draft')
		{
			doPrint($header.$details."<eject>");
			$lc=8;
			$details1 .= $header.$details;
			$details = '';
		}
	}
	$details .= "---- ---------------------------------------- --------------- -------------- ------------ ------------ --------------\n";
	$details .= space(45).' '.
					adjustSize('TOTALS: ',15).' '.
					adjustRight(number_format($total_income,2),14).' '.
					adjustRight(number_format($total_total_pagibig,2),12).' '.
					adjustRight(number_format($total_employer,2),12).' '.
					adjustRight(number_format($total_total_pagibig+$total_employer,2),14)."\n";
	$details .= "---- ---------------------------------------- --------------- -------------- ------------ ------------ --------------\n";

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
    <table width="85%" border="0" align="center" cellpadding="1" cellspacing="1">
      <tr> 
        <td height="27" colspan="6" background="../graphics/table_horizontal.PNG">&nbsp; 
          <strong><font color="#F3F7F9" size="2" face="Verdana, Arial, Helvetica, sans-serif">:: 
          PAGIBIG Summary Report :: </font></strong></td>
      </tr>
      <tr> 
        <td width="9%" height="27" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          From<br>
          <strong> </strong> 
          <input name="from_date" type="text" id="from_date" value="<?= $from_date;?>" size="10">
          <img src="../graphics/dwn-arrow-grn.gif" onclick="popUpCalendar(this, f1.from_date, 'mm/dd/yyyy')"> 
          <strong> </strong> </font></td>
        <td width="9%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">To<br>
          <strong> </strong> 
          <input name="to_date" type="text" id="to_date" value="<?= $to_date;?>" size="10">
          <strong> </strong> <img src="../graphics/dwn-arrow-grn.gif" onclick="popUpCalendar(this, f1.to_date, 'mm/dd/yyyy')"> 
          </font></td>
        <td width="17%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Branch 
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
        <td width="17%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Department</font><br> 
          <select name="department_id" id="department_id" style="width:150">
            <option value=''>All Departments</option>
            <?
			$q = "select * from department order by department";
			$qr = @pg_query($q) or message(pg_errormessage().$q);
			while ($r = @pg_fetch_object($qr))
			{
				if ($department_id == $r->department_id)
				{
					echo "<option value=$r->department_id selected>$r->department</option>";
				}
				else
				{
					echo "<option value=$r->department_id>$r->department</option>";
				}	
			}
			?>
          </select></td>
        <td width="2%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Rank<br>
          <?= lookUpAssoc('rank',array('All Ranks'=>'','Rank & File'=>'R', 'Supervisor'=>'S'), $rank);?>
          </font></td>
        <td width="46%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          Show<br>
          <?= lookUpAssoc('show',array('Deducted'=>'D','As Per Table'=>'T', 'Variance'=>'V'), $show);?>
          <input name="p1" type="submit" id="p14" value="Go">
          </font></td>
      </tr>
      <tr> 
        <td height="27" colspan="6"><table width="100%" border="0" cellspacing="0" cellpadding="2">
            <tr bgcolor="#000033"> 
              <td nowrap bgcolor="#DADADA"><font color="#666666" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
                <img src="../graphics/bluelist.gif" width="16" height="17"> PAGIBIG 
                Report Preview</strong></font></td>
            </tr>
          </table></td>
      </tr>
      <tr> 
        <td colspan="6" valign="top" bgcolor="#FFFFFF"> <textarea name="print_area" cols="120" rows="20"  wrap="off" readonly><?= $details1;?></textarea> 
        </td>
      </tr>
    </table>
    <input name="p1" type="submit" id="p1" value="Print Draft" >
    <input name="p12" type="button" id="p12" value="Print"  onClick="printIframe(print_area)" >
  </div>
</form>

<iframe name="printit" id="printit" style="width:0px;height:0px;"></iframe>
