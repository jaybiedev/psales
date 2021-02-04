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
	include_once('payslip.php');
	$q = "select * 
				from 
					paymast, 
					payroll_header 
				where
					paymast.paymast_id = payroll_header.paymast_id and
					payroll_header.status!='C' and 
					payroll_header.payroll_period_id='$payroll_period_id'";

	if ($paymast_id != '')
	{
		$q .= " and paymast.paymast_id='$paymast_id'";
	}
	else
	{
		if ($department_id != '')
		{
			$q .= " and paymast.department_id='$department_id'";
		}
		if ($section_id != '')
		{
			$q .= " and paymast.section_id='$section_id'";
		}
		if ($branch_id != '')
		{
			$q .= " and paymast.branch_id='$branch_id'";
		}
		if ($rank != '')
		{
			$q .= " and paymast.rank='$rank'";
		}
	}
	if ($sort == 'A')
	{
		$q .= " order by paymast.elast, paymast.efirst ";
	}
	elseif ($sort == 'G')
	{
		$q .= " order by paymast.department_id, paymast.section_id, paymast.elast, paymast.efirst ";
	}
	
	$qr = @pg_query($q) or message(pg_errormessage().$q);

	$pcnt=0;
	while ($r = @pg_fetch_assoc($qr))
	{
		$temp = $r;
		$temp['department'] = lookUpTableReturnValue('x','department','department_id','department',$r['department_id']);
		$temp['branch'] = lookUpTableReturnValue('x','branch','branch_id','branch',$r['branch_id']);

		$pcnt++;
		if ($p1 == 'Go')
		{
			$details = '';
			$details1 .= printPayslip($temp,'Screen',true);
		}
		else
		{
			//$xx++;
			if ($pcnt >2) 
			{
				$eject =true;
				$pcnt = 0;
			}
			else
			{
				$eject = false;
			}
			printPayslip($temp,'Printer',$eject);
			//if ($xx > 6) exit;
		}	
	}
}
?>	
<form name="form1" method="post" action="">
  <div align="center">
    <table width="90%" border="0" cellspacing="0" cellpadding="2">
      <tr>
        <td><font  size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; </font> 
          <table width="90%" border="0" align="center" cellpadding="1" cellspacing="1" bgcolor="#EFEFEF">
            <tr> 
              <td height="22" colspan="4"  background="../graphics/table_horizontal.PNG" bgcolor="#CCCCCC" ><font  size="2" face="Verdana, Arial, Helvetica, sans-serif">.::<strong><font color="#EFEFEF"> 
                Employee Payslip Generation</font></strong></font></td>
            </tr>
            <tr> 
              <td width="16%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Payroll 
                Period<br>
                </font></td>
              <td width="19%" nowrap><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
                <font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>
                <?= lookUpPayPeriod('payroll_period_id',$payroll_period_id);?>
                </strong></font> </strong></font> </strong></td>
              <td width="8%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Sorting</font></td>
              <td width="57%"> 
                <?= lookUpAssoc('sort',array('Alphabetical'=>'A','Grouped by Department'=>'G'),$sort);?>
              </td>
            </tr>
            <tr> 
              <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Department</font></td>
              <td nowrap><select name="department_id" id="department_id" style="width:150">
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
                </select> </td>
              <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Rank</font></td>
              <td nowrap>
                <?= lookUpAssoc('rank',array('All Ranks'=>'','Rank & File'=>'R', 'Supervisor'=>'S'), $rank);?>
              </td>
            </tr>
            <tr> 
              <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Branch</font></td>
              <td nowrap><select name="branch_id"  id="branch_id"  style="width:150">
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
              </td>
              <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
                Employee</font></td>
              <td nowrap><select name="paymast_id">
                  <option value=''>All Employees</option>
                  <?
			$q = "select * from paymast where enable='Y' order by elast,efirst ";
			$qr = @pg_query($q) or message(pg_errormessage().$q);
			while ($r = @pg_fetch_object($qr))
			{
				if ($r->paymast_id == $paymast_id)
				{
					echo "<option value=$r->paymast_id selected>$r->elast, $r->efirst</option>";
				}
				else
				{
					echo "<option value=$r->paymast_id>$r->elast, $r->efirst</option>";
				}	
			}
			?>
                </select>
                <input name="p1" type="submit" id="p1" value="Go"></td>
            </tr>
            <tr bgcolor="#DADADA" > 
              <td colspan="4" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Report 
                Preview</font></td>
            </tr>
            <tr> 
              <td colspan="4" nowrap bgcolor="#EFEFEF"><textarea name="print_area" cols="100" rows="20"  wrap="off" readonly><?= $details1;?></textarea></td>
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
