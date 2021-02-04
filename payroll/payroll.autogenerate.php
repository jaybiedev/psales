<?
if (!session_is_registered('aAuto'))
{
	session_register('aAuto');
	$aAuto = null;
	$aAuto = array();
}

$branch_id = $_REQUEST['branch_id'];
$department_id = $_REQUEST['department_id'];


if ($p1 == 'Proceed Generate' && $payroll_period_id == '')
{
	message1("No Payroll Period Specified....");
}
elseif ($p1 == 'Proceed Generate')
{
	$q = "select * from payroll_period where payroll_period_id = '$payroll_period_id'";
	$qr = @pg_query($q) or message1(pg_errormessage());
	$r = @pg_fetch_object($qr);
	if ($r->post == 'Y')
	{
		message1("Transactions for this payroll period has already been posted");
		exit;
	}
	$num = $r->num;
	$num2 = $r->num2;
	$days  = $r->days;
	$schedule = $r->schedule;
	
	$q = "select * from paymast where enable = 'Y'";
	if ($branch_id != '')
	{
		$q .= " and branch_id = '$branch_id'";
	}
	if ($department_id != '')
	{
		$q .= " and department_id = '$department_id'";
	}
	$qr = @pg_query($q) or message1(pg_errormessage());
	
	$aAuto = null;
	$aAuto = array();
	
	while ($r = @pg_fetch_assoc($qr))
	{	
		$dummy = null;
		$dummy = array();
		
		$dummy = $r;
		
		$qq = "select * from payroll_header where paymast_id = '".$dummy['paymast_id']."' and payroll_period_id = '$payroll_period_id'";
		$qqr = @pg_query($qq) or message1(pg_errormessage());
		$rr = @pg_fetch_object($qqr);
		$dummy['payroll_header_id'] = $rr->payroll_header_id;
		
		$dummy['num']  = $num;
		$dummy['days'] = $days;
		$dummy['payroll_period_id'] = $payroll_period_id;
		$dummy['schedule'] = $schedule;
		
		if ($num>1)
		{
			$a = sumPayroll($dummy, $payroll_period_id) ;
			$avars = array('accu_sssbasis','accu_taxbasis','accu_phicbasis','accu_pagibigbasis','accu_grossincome','accu_netincome','accu_income',
								'accu_deduction','accu_basic','accu_sss','accu_tax','accu_phic','accu_pagibig');
		
			for ($c=0;$c<count($avars);$c++)
			{
				$dummy[$avars[$c]] = $a[$avars[$c]];
			}
		}

		if (in_array($dummy['pay_category'] , array('1','3'))) //-- monthly rate
		{
			$basic = $dummy['ratem'] / $num2;
			$dummy['basic'] = $basic;
		}
		elseif (in_array($dummy['pay_category'] , array('2','4'))) //--daily rate
		{
			$basic = $dummy['adwr']*$days;
			$dummy['basic'] = $basic;
		}
		
		$dummy['total_basic'] = $dummy['total_income'] =$dummy['sss_basis'] =$dummy['phic_basis']  =$dummy['tax_basis'] =$dummy['pagibig_basis']= $dummy['basic'];

		$a = computeDeduction($dummy);
		$dummy['total_sss'] = $a['total_sss'];
		$dummy['total_phic'] = $a['total_phic'];
		$dummy['total_pagibig'] = $a['total_pagibig'];
		$dummy['total_tax'] = $a['total_tax'];
		$dummy['total_deduction'] = $aPT['total_tax'] + $aPT['total_sss'] + $aPT['total_phic'] + $aPT['total_pagibig'];


		$dummy['total_deduction'] = $dummy['total_sss'] + $dummy['total_phic'] + $dummy['total_tax'] + $dummy['total_pagibig'];
		$dummy['net_income'] = $dummy['total_income'] - $dummy['total_deduction'];
		
		
		if ($dummy['net_income'] > '0') 
		{
			$aAuto[] = $dummy;	
		}
	
	}
}
elseif ($p1 == 'Save')
{
	$aAuto['date_entry'] = date('Y-m-d');
	$fields = array('paymast_id','payroll_period_id','date_entry','total_income','total_deduction', 'net_income', 'total_basic', 'basic',
						'total_sss', 'total_tax','total_phic', 	'total_pagibig','actual_days');
	$cc=0;
	foreach ($aAuto as $temp)
	{
		$dummy = $temp;
		$dummy['actual_days'] =  $temp['days'];
		$dummy['date_entry'] = date('Y-m-d');	
		if ($temp['payroll_header_id'] == '')
		{
			$q = 'insert into payroll_header (';
			$q1 = 'values (';
			for ($c=0;$c<count($fields);$c++)
			{
				$item = $fields[$c];
				if ($dummy[$item] == '')
				{
					$dummy[$item] = 0;
				}
				if ($c > 0)
				{
					$q .= ',';
					$q1 .= ',';
				}
				$q .= $item;
				$q1 .= "'".$dummy[$item]."'";
			}
			$q .= ') '.$q1.')';

			$qr= @pg_query($q) or message1 (pg_errormessage().$q);
			if ($qr && pg_affected_rows($qr)>0) 
			{
				$qr = query("select currval('payroll_header_payroll_header_id_seq'::text)");
				$r = @pg_fetch_object($qr);
				
				$dummy["payroll_header_id"]= $r->currval;
				$aAuto[$cc] = $dummy;
			}
			else 
			{
				message("WARNING!! Error saving  generated  data....");
			}	
				
		}
		else
		{
		}
		$cc++;
	}
}
?>
<br>
<form name="f1" id="f1" method="post" action="">
  <table width="80%" border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#EFEFEF">
    <tr> 
      <td height="21" colspan="4" background="../graphics/table0_horizontal.PNG"> 
        <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> :: Auto Generate 
        Payroll Entries :: </font></td>
    </tr>
    <tr valign="top" bgcolor="#FFFFFF"> 
      <td width="13%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Branch<br>
        <select name="branch_id" id="branch_id">
          <option value="">All Branches</option>
          <?
	$q = "select * from branch where enable='Y' order by branch";
	$qr = @pg_query($q);
	while ($r = @pg_fetch_object($qr))
	{
		if ($r->branch_id == $branch_id)
		{
			echo "<option value='$r->branch_id' selected>$r->branch_code $r->branch</option>";
		}
		else
		{
			echo "<option value='$r->branch_id'>$r->branch_code $r->branch</option>";
		}
	}
	?>
        </select>
        </font> </td>
      <td colspan="3"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Department<br>
        <select name="department_id" id="department_id">
          <option value="">All Departments</option>
          <?
	$q = "select * from department where enable='Y' order by department";
	$qr = @pg_query($q);
	while ($r = @pg_fetch_object($qr))
	{
		if ($r->department_id == $department_id)
		{
			echo "<option value=$r->department_id selected>$r->department_code $r->department</option>";
		}
		else
		{
			echo "<option value=$r->department_id>$r->department_code $r->department</option>";
		}
	}
	?>
        </select>
        <?= lookUpPayPeriod('payroll_period_id',$SYSCONF['PAYROLL_PERIOD_ID']);?>
        </font> </td>
    </tr>
    <tr valign="top" bgcolor="#EFEFEF"> 
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input type="checkbox" name="checkbox" value="checkbox">
        Basic <br>
        </font></td>
      <td width="19%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input type="checkbox" name="checkbox2" value="checkbox">
        SSS<br>
        <input type="checkbox" name="checkbox22" value="checkbox">
        PHIC </font></td>
      <td width="34%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input type="checkbox" name="checkbox23" value="checkbox">
        Withholding Tax</font><br> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input type="checkbox" name="checkbox24" value="checkbox">
        PagIbig </font></td>
      <td width="34%" valign="bottom" nowrap><input name="p1" type="submit" id="p1" value="Proceed Generate"></td>
    </tr>
    <tr valign="top" bgcolor="#EFEFEF"> 
      <td colspan="4" nowrap bgcolor="#FFFFFF">&nbsp;</td>
    </tr>
  </table>
	
  <table width="80%%" border="0" cellspacing="1" cellpadding="0" align="center">
    <tr bgcolor="#EFEFEF"> 
      <td width="3%" align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></td>
      <td width="10%" align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Idno</font></td>
      <td width="27%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Employee</font></td>
      <td width="10%" align="center" valign="middle"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Basic</font></td>
      <td width="10%" align="center" valign="middle"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">SSS</font></td>
      <td width="10%" align="center" valign="middle"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">PHIC</font></td>
      <td width="10%" align="center" valign="middle"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">WTax</font></td>
      <td width="10%" align="center" valign="middle"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">PagIbig</font></td>
      <td width="10%" align="center" valign="middle"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Net</font></td>
    </tr>
    <?
	$ctr=0;
	foreach ($aAuto as $temp)
	{
		$ctr++;
	?>
    <tr> 
      <td height="19" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?=$ctr;?>
        <input type="checkbox" name="mark[]" value="<?= $temp['paymast_id'];?>">
        . </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $temp['idno'];?>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $temp['elast'].', ',$temp['efirst'];?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= number_format($temp['basic'],2);?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= number_format($temp['total_sss'],2);?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= number_format($temp['total_phic'],2);?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= number_format($temp['total_tax'],2);?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= number_format($temp['total_pagibig'],2);?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= number_format($temp['net_income'],2);?>
        </font></td>
    </tr>
    <?
	}
	?>
    <tr> 
      <td colspan="6"><table width="1%" border="0" cellpadding="1" cellspacing="1" bgcolor="#CCCCCC">
          <tr bgcolor="#FFFFFF"> 
            <td nowrap width="3%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              <input type="image" src="../graphics/save.jpg" alt="Save This Form" width="57" height="15" id="Save" onClick="document.getElementById('f1').action='?p=payroll.autogenerate&p1=Save';document.getElementById('f1').submit();" name="Save"  accesskey="S">
              </strong></font></td>
            <td nowrap width="46%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              <input type="image" src="../graphics/print.jpg" alt="Print This Claim Form"  name="Print" id="Print" onClick="document.getElementById('f1').action='?p=payroll.autogenerate&p1=Print';document.getElementById('f1').submit();">
              </strong></font></td>
            <!--<td nowrap width="26%"> <input name="Cancel" id="Cancel" type="image" src="../graphics/cancel.jpg" onClick="vSubmit(this)" alt="Cancel This Entry" width="77" height="20"></td>-->
            <td nowrap width="25%"> <input type='image' name="Cancel" id="Cancel" onClick="vSubmit(this)"  src="../graphics/cancel.jpg" alt="Cancel Form" width="77" height="20"> 
            <td nowrap width="25%"> <input type='image' name="New" id="New" onClick="vSubmit(this)"  src="../graphics/new.jpg" alt="New Claim Form" width="63" height="20"> 
            </td>
          </tr>
        </table></td>
      <td colspan="3" align="center"><input name="p1" type="submit" id="p1" value="Delete Checked"></td>
    </tr>
  </table>
</form>
