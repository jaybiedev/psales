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

if ($payroll_period_id == '') $payroll_period_id =$SYSCONF['PAYROLL_PERIOD_ID'];

if (($p1=='Go' || $p1=='Print Draft') && $payroll_period_id!= '')
{
	$payroll_period_id = $_REQUEST['payroll_period_id'];
	if ($payroll_period_id == '') $payroll_period_id =$SYSCONF['PAYROLL_PERIOD_ID'];
	
	$header = "";
	if ($p1 == 'Print Draft') $header .= "<small3>";
	$header .= $SYSCONF['BUSINESS_NAME']."\n";
	$header .= $SYSCONF['BUSINESS_ADDR']."\n";
	
	$header .= "CHARGES SUMMARY REPORT \n";
	$header .= "PAYROLL PERIOD: ".lookUpPayPeriodReturnValue('x',$payroll_period_id)."\n";
	$header .= "Printed : ".date('m/d/Y g:ia')."\n";
	$title = "";
	$titleln="";
	$titleln .= "---- ------- -------------------------  ";
	$title .= "  #   Id No. Employee  Name             ";
	
	$aCols = null;
	$aCols = array();
	$mseqn = '';
	$q = "select * from reportform where reportform='CHARGESUM'  and enable='Y' order by type desc, seqn";
	$qr = @pg_query($q) or message(pg_errormessage());

	while ($r = @pg_fetch_assoc($qr))
	{
		if ($mseqn != $r['seqn'])
		{
			if ($r['title1'] != '') 
				$r['column'] = $r['title1'];
			elseif ($r['title1'] != '') 
				$r['column'] = $r['title2'];
			else
				$r['column'] = count($aCols);
				
			$aCols[] = $r;
		}
		
		$mlong = $r['long'];
		if ($mlong == '') $mlong=10;
		$titleln .= str_repeat('-',$mlong).' ';
		$title .= center(substr($r['title1'],0,$mlong),$mlong).' ';
	}

		$s = array();
		$s['title1'] = 'stotal';
		$s['column'] = 'stotal';
		$s['long'] = 12;
		$s['type'] = "D";
		$aCols[] = $s;
		$titleln .= str_repeat('-',12).'';
		$title .= center(substr('Total Deduct',0,12),12).'';
	
	$titleln .= "\n";
	$title .= "\n";
	$header .= $titleln;
	$header .= $title;
	$header .= $titleln;
	$q = "select * 
				from 
					paymast
				where
					enable='Y' ";
	if ($department_id != '')
	{
		$q .= " and department_id='$department_id'";
	}
	if ($section_id != '')
	{
		$q .= " and section_id='$section_id'";
	}
	if ($paymast_id != '')
	{
		$q .= " and paymast_id='$paymast_id'";
	}
	if ($branch_id != '')
	{
		$q .= " and branch_id='$branch_id'";
	}

	if ($sort == 'A' || $sort == '')
	{
		$q .= " order by paymast.elast, paymast.efirst ";
	}
	elseif ($sort == 'G')
	{
		$q .= " order by branch_id,paymast.department_id, paymast.section_id, paymast.elast, paymast.efirst ";
	}
	$qr = @pg_query($q) or message(pg_errormessage().$q);

	$aName = null;
	$aName = array();
	while ($r = @pg_fetch_assoc($qr))
	{
		$aName[] = $r;
	}
	
	$c=0;
	
	
	$q = "select 
					*
				from 
					payrollcharge as ph,
					deduction_type
				where
					deduction_type.deduction_type_id = ph.deduction_type_id and 
					ph.enable='Y'
				order by
					ph.paymast_id";

	$qr = @pg_query($q) or message1(pg_errormessage().$q);
	$mpaymast_id = '';
	$key = '';
	while ($r = @pg_fetch_assoc($qr))
	{
		if ($mpaymast_id != $r['paymast_id'] )
		{
			if ($key != '0')
			{
				$aName[$key] = $dummy;
			}
			$dummy = null;
			$dummy = array();
			$key=0;
			foreach ($aName as $temp)
			{
				if ($temp['paymast_id'] == $r['paymast_id'])
				{
					$dummy=$temp;
					break;
				}
				$key++;
			}
			$mpaymast_id = $r['paymast_id'];
		}
		foreach ($aCols as $temp)
		{
//			if ($temp['type'] != $type) continue;
			$acode = explode(';', $temp['reportform_code']);	
			if (in_array($rr[$code_fld], $acode))
			{
				$dummy[$temp['column']] += $r['credit'] - $r['debit'];		
				$dummy['stotal'] += $r['credit'] - $r['debit'];		
			}
		}
	}

	$aName[$key] = $dummy;
	

	
	foreach ($aName as $temp)
	{
		if ($temp['paymast_id'] == '') continue;

		$stotal = 0;
		$pln='';
		foreach ($aCols as $temp1)
		{
		
			if ($temp1['column'] == '') continue;
			if ($temp1['long'] == '0') $long = 10;else $long=$temp1['long'];

			$amt = 0;
			$amt = $temp[$temp1['column']];
			$pln .= adjustRight(number_format2($amt,2),$long).' ';
			$aTotal[$temp1['column']] += $amt; //$temp[$temp1['column']];
			$stotal += $amt;
		}
		$pln .= "\n";
		if ($stotal == '0') continue;
		$c++;
		$details .= adjustRight($c,3).'. '.
						adjustSize($temp['idnum'],7).' '.
						adjustSize($temp['elast'].', '.$temp['efirst'],25).' ';
		$details .= $pln;
		$lc++;
		if ($lc>55 && $p1 == 'Print Draft')
		{
			doPrint($header.$details."<eject>");
			$lc=8;
			$details1 .= $header.$details;
			$details = '';
		}

	}
	
	$details .= $titleln;
	$details .= '  '.adjustSize($c.' Item/s      GRAND TOTAL -- >' ,35).'  ';

	foreach ($aCols as $temp1)
	{
		if ($temp1['column'] == '') continue;
		if ($temp1['long'] == '0') $long = 10;else $long=$temp1['long'];
		
		$details .= adjustRight(number_format2($aTotal[$temp1['column']],2),$long).' ';
	}
	$details .= "\n\n";
	$details .= $titleln;
	$details1 .=  $header.$details;

	if ($p1 == 'Print Draft')
	{
		doPrint($header.$details."<eject>");
		$lc=8;
	}

}
?>	
<form name="form1" method="post" action="">
  <div align="center">
    <table width="90%" border="0" cellspacing="0" cellpadding="2">
      <tr>
        <td><font  size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; </font> 
          <table width="80%" border="0" align="center" cellpadding="1" cellspacing="1">
            <tr bgcolor="#CCCCCC" background="../graphics/table_horizontal.PNG"> 
              <td height="23" colspan="4"><font color="#F3F7F9"  size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
                :: Charges Summary Report ::</strong></font></td>
            </tr>
            <tr> 
              <td width="14%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Payroll 
                Period<br>
                <strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
                <?= lookUpPayPeriod('payroll_period_id',$payroll_period_id);?>
                </strong></font></strong> <br>
                </font></td>
              <td width="18%" nowrap> <font size="2" face="Verdana, Arial, Helvetica, sans-serif">Branch</font><br> 
                <select name="branch_id"  id="select"  style="width:150">
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
                </select> <br> </td>
              <td width="18%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Department</font><br> 
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
              <td width="50%"><input name="p1" type="submit" id="p1" value="Go"></td>
            </tr>
            <tr > 
              <td colspan="4" nowrap bgcolor="#DADADA"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Report 
                Preview | <a href="?p=setup.chargesummary">Edit Format</a></font></td>
            </tr>
            <tr> 
              <td colspan="4" nowrap bgcolor="#EFEFEF"><textarea name="print_area" cols="120" rows="20"  wrap="off" readonly><?= $details1;?></textarea></td>
            </tr>
          </table>
          
        </td>
      </tr>
    </table>
    <input name="p1" type="submit" id="p1" value="Print Draft" >
    <input name="p12" type="button" id="p12" value="Print"  onClick="printIframe(print_area)" >
  </div>
</form>

<iframe name="printit" id="printit" style="width:0px;height:0px;"></iframe>
