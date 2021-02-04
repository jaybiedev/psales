<STYLE TYPE="text/css">
<!--
	.altText {
	background-color: #FFFFFF;
	font-family: verdana;
	border: #CCCCCC 1px solid;
	font-size: 12px;
	color: #000000
	} 
	
	.altNum {
	background-color: #FFFFFF;
	font-family: verdana;
	border: #CCCCCC 1px solid;
	font-size: 12px;
	color: #000000;
	text-align:right
	} 
	
	.altTextArea {
	background-color: #FFFFFF;
	font-family: verdana;
	border: #CCCCCC 1px solid;
	font-size: 12px;
	color: #000000
	} 
	
	SELECT {
	background-color: #FFFFFF;
	font-family: verdana;
	border: #CCCCCC 1px solid;
	font-size: 12px;
	color: #000000
	} 			

	.altBtn {
	background-color: #CFCFCF;
	font-family: verdana;
	font-size: 11px;
	padding: 1px;
	margin: 0px;
	color: #1F016D
	} 
-->
</STYLE>
 <script language="JavaScript" type="text/JavaScript">
<!--
function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);

function vInc(source)
{
	document.getElementById('f1').action="?p=payroll.transaction&p1=selectIncome&source="+source;
	document.getElementById('f1').submit();
}
function vICompute()
{
	var basis = document.getElementById('income_basis').value;
	var rate = document.getElementById('income_rate').value;
	var qty = document.getElementById('income_qty').value;
	var income_code = document.getElementById('income_code').value;
	var amount = 0;
	var basis_amount = 0;

	if (basis == 'D' && income_code == 'B1')
	{
		basis_amount = document.getElementById('tenureallowance').value;

	}
	else if (basis == 'D')
	{
		basis_amount = document.getElementById('adwr').value;
	}
	else if (basis == 'T')
	{
		basis_amount = document.getElementById('tenureallowance').value;
	}
	else if (basis == 'H')
	{
		basis_amount = document.getElementById('hourly').value;
	}
	else if (basis == 'M')
	{
		basis_amount = document.getElementById('ratem').value;
	}

	if (rate != '' && rate != '0')
	{
		basis_amount = basis_amount * rate
	}
	amount = twoDecimals(basis_amount*qty,2);
	document.getElementById('income_amount').value = amount;
	return false;

}

function vDed(src)
{
	document.getElementById('f1').action="?p=payroll.transaction&p1=selectDeduct&source="+src;
	document.getElementById('f1').submit();
}
function vDCompute()
{
	var basis = document.getElementById('deduction_basis').value;
	var rate = document.getElementById('deduction_rate').value;
	var qty = document.getElementById('deduction_qty').value;
	var basis_amount = 0;
	var amount = 0;
	if (basis == 'D')
	{
		basis_amount = document.getElementById('adwr').value;
	}
	else if (basis == 'T')
	{
		basis_amount = document.getElementById('tenureallowance').value;
	}
	else if (basis == 'H')
	{
		basis_amount = document.getElementById('hourly').value;
	}
	else if (basis == 'M')
	{
		basis_amount = document.getElementById('ratem').value;
	}
	else if (basis == 'L')
	{
		qty=1;
		basis_amount = document.getElementById('deduction_amount').value;
	}
	if (rate != '' && rate != '0')
	{
		basis_amount = basis_amount * rate;
	}

	amount = twoDecimals(basis_amount*qty,2);
	document.getElementById('deduction_amount').value = amount;
	return false;
}
function vCompute()
{
	return true;
	
	f1.total_deduction.value = f1.sub_deduction.value*1 + f1.total_sss.value*1 + f1.total_phic.value*1 + f1.total_tax.value*1 + f1.total_pagibig.value*1;
	f1.net_income.value = f1.total_income.value*1 - f1.total_deduction.value*1 ;
}
//-->
</script>
<?

if (!session_is_registered('aPT'))
{
	session_register('aPT');
	$aPT =null;
	$aPT = array();
}
if (!session_is_registered('iPTI'))
{
	session_register('iPTI');
	$iPTI =null;
	$iPTI = array();
}
if (!session_is_registered('iPTD'))
{
	session_register('iPTD');
	$iPTD =null;
	$iPTD = array();
}
if (!session_is_registered('dPTD'))
{
	session_register('dPTD');
	$dPTD =null;
	$dPTD = array();
}
if (!session_is_registered('dPTI'))
{
	session_register('dPTI');
	$dPTI =null;
	$dPTI = array();
}

function calcSum()
{
	global $aPT, $SYSCONF;
	$a = sumPayroll($aPT, $SYSCONF['PAYROLL_PERIOD_ID']);

	$avars = array('accu_sssbasis','accu_taxbasis','accu_phicbasis','accu_pagibigbasis','accu_grossincome','accu_netincome','accu_income',
						'accu_deduction','accu_basic','accu_sss','accu_tax','accu_phic','accu_pagibig');

	for ($c=0;$c<count($avars);$c++)
	{
		$aPT[$avars[$c]] = $a[$avars[$c]];
	}

	return;
}

function vCompute()
{
	global $aPT, $iPTI, $iPTD, $SYSCONF;
	//print_r($aPT);
	$aPT['total_income'] = 0;
	$aPT['total_basic'] = $aPT['basic'];
	$aPT['total_deduction'] = $aPT['net_income'] = $aPT['total_sss'] = $aPT['total_phic'] = $aPT['total_wtax'] = $aPT['total_pagibig'] = 0;
	$aPT['subtotal_income'] = $aPT['subtotal_deduction'] = 0;
	$aPT['sss_basis'] = $aPT['tax_basis'] = $aPT['pagibig_basis'] = $aPT['phic_basis'] = $aPT['basic'];
	foreach ($iPTI as $temp)
	{
		if (substr($temp['income_code'],0,1) == 'A')
		{
			$aPT['total_basic'] += $temp['income_amount'];
		}
		if ($temp['sss'] == 'Y')
		{
			$aPT['sss_basis'] += $temp['income_amount'];
		}
		if ($temp['tax'] == 'Y')
		{
			$aPT['tax_basis'] += $temp['income_amount'];
		}
		if ($temp['phic'] == 'Y')
		{
			$aPT['phic_basis'] += $temp['income_amount'];
		}
		$aPT['total_income'] += $temp['income_amount'];
		$aPT['subtotal_income'] += $temp['income_amount'];

	}

	foreach ($iPTD as $temp)
	{
		if (substr($temp['deduction_code'],0,1) == 'A')
		{
			$aPT['total_basic'] -= $temp['deduction_amount'];
		}
		$q = "select * from deduction_type where deduction_type_id = '".$temp['deduction_type_id']."'";
		$qqr = @pg_query($q);
		$rr = pg_fetch_assoc($qqr);

		if ($rr['sss'] == 'Y')
		{
			$aPT['sss_basis'] -= $temp['deduction_amount'];
		}
		if ($rr['tax'] == 'Y')
		{
			$aPT['pagibig_basis'] -= $temp['deduction_amount'];
			$aPT['tax_basis'] -= $temp['deduction_amount'];
		}
		if ($rr['phic'] == 'Y')
		{

			$aPT['phic_basis'] -= $temp['deduction_amount'];

		}

		$aPT['total_deduction'] += $temp['deduction_amount'];
		$aPT['subtotal_deduction'] += $temp['deduction_amount'];
	}


	$aPT['sss_basis'] = $aPT['sss_basis']*($SYSCONF['NUM2']/$SYSCONF['NUM']);
	$aPT['phic_basis'] = $aPT['phic_basis']*($SYSCONF['NUM2']/$SYSCONF['NUM']);
	$aPT['tax_basis'] = $aPT['tax_basis']*($SYSCONF['NUM2']/$SYSCONF['NUM']);
	
	$a = computeDeduction($aPT);

	$aPT['total_sss'] = ($a['total_sss'] )*($SYSCONF['NUM']/$SYSCONF['NUM2']) - $aPT['accu_sss'];
	$aPT['total_phic'] = $a['total_phic']*($SYSCONF['NUM']/$SYSCONF['NUM2']) - $aPT['accu_phic'];
	$aPT['total_tax'] = $a['total_tax']*($SYSCONF['NUM']/$SYSCONF['NUM2'])- $aPT['accu_tax'];
	$aPT['total_pagibig'] = $a['total_pagibig']*($SYSCONF['NUM']/$SYSCONF['NUM2'])- $aPT['accu_pagibig'];
	
	$aPT['total_deduction'] += $aPT['total_tax'] + $aPT['total_sss'] + $aPT['total_phic'] + $aPT['total_pagibig'];
	$aPT['net_income'] = $aPT['total_income'] - $aPT['total_deduction'];
	

	$avars = array('total_sss','total_tax', 'total_pagibig','total_income','total_deduction','net_income', 'total_phic','basic','total_basic');
	for ($c=0;$c<count($avars);$c++)
	{
		$aPT[$avars[$c]] = number_format($aPT[$avars[$c]],2,'.','');
	}

	return;
}

$p1 = $_REQUEST['p1'];

$fields_header = array('basic', 'total_basic','total_sss','total_tax','total_phic','total_pagibig','total_deduction','total_income','net_income','actual_days');

if (!in_array($p1,array('','editincome','editdeduction','deleteincome','deletededuction','Load','New','Search','Next','Previous')))
{
	for ($c=0;$c<count($fields_header);$c++)
	{
		$aPT[$fields_header[$c]] = $_REQUEST[$fields_header[$c]];
		
		if ($aPT[$fields_header[$c]] == '')
		{
			$aPT[$fields_header[$c]]=0;
		}
	}
	if ($aPT['payroll_period_id'] == '')
	{
		$aPT['payroll_period_id'] = $PAYROLL_PERIOD_ID;
	}
	if ($aPT['date_entry'] == '')
	{
		$aPT['date_entry'] = date('Y-m-d');
	}
	
}


if ($p1 == 'Next')
{
	$q = "select paymast.paymast_id from paymast, payroll_header 
						where
							paymast.paymast_id = payroll_header.paymast_id and
							paymast.elast>'".$aPT['elast']."' and 
							payroll_header.payroll_period_id =  '".$SYSCONF['PAYROLL_PERIOD_ID']."'
						order by
							elast, efirst  offset 0 limit 1";

	$qr = @pg_query($q) or message(pg_errormessage().$q);
	if (@pg_num_rows($qr) == 0)
	{
		message("End of File...");
	}
	else
	{
		$r = @pg_fetch_object($qr);
		$p1 ="selectPaymastId";
		$id = $r->paymast_id;
	}
}
elseif ($p1 == 'Previous')
{
	$q = "select paymast.paymast_id from paymast, payroll_header 
						where
							paymast.paymast_id = payroll_header.paymast_id and
							paymast.elast<'".$aPT['elast']."' and 
							payroll_header.payroll_period_id =  '".$SYSCONF['PAYROLL_PERIOD_ID']."'
						order by
							elast desc offset 0 limit 1";

	$qr = @pg_query($q) or message(pg_errormessage().$q);
	if (@pg_num_rows($qr) == 0)
	{
		message("Beginning  of File...");
	}
	else
	{
		$r = @pg_fetch_object($qr);
		$p1 ="selectPaymastId";
		$id = $r->paymast_id;
	}
}
if ($p1 == 'New' or $p1 == 'Add New')
{
	$aPT=null;
	$aPT=array();

	$iPTI=null;
	$iPTI=array();

	$iPTD=null;
	$iPTD=array();

	$dPTI=null;
	$dPTI=array();

	$dPTD=null;
	$dPTD=array();
	

	$aPT['payroll_period_id'] = $PAYROLL_PERIOD_ID;
	$aPT['date_entry'] = date('Y-m-d');
	$focus = 'idno';
}
elseif ($p1 == 'Ok' && $_REQUEST['income_type_id']!='')
{
	$aPT['status'] = 'M';
	$fields = array('income_code','income_type_id','income_qty','income_amount');
	for ($c=0;$c<count($fields);$c++)
	{
		$dPTI[$fields[$c]] = $_REQUEST[$fields[$c]];
		if ($dPTI[$fields[$c]] == '')
		{
			$dPTI[$fields[$c]] = 0;
		}
	}

	$q = "select * from income_type where income_type_id='".$dPTI['income_type_id']."'";
	$qr = @pg_query($q) or message(pg_errormessage());
	if ($qr == 0)
	{
		message('Income Specified not found...');
	}
	else
	{

		$r = pg_fetch_assoc($qr);
		$dPTI += $r;
		

		$c = $fnd = 0;
		foreach ($iPTI as $temp)
		{
			if ($temp['income_type_id'] == $dPTI['income_type_id'])
			{
				$dummy = $temp;
				$dummy['income_qty'] = $dPTI['income_qty'];
				$dummy['income_amount'] = $dPTI['income_amount'];
				$iPTI[$c] = $dummy;
				$fnd=1;
				break;
			}
			$c++;
		}
		if ($fnd == 0)
		{
			$iPTI[] = $dPTI;
		}
		$dPTI = null;
		$dPTI=array();
	}		
	vCompute();
	$focus =  'income_code';

}
elseif ($p1 == 'update')
{
		if (in_array($aPT['pay_category'], array('1','3')))
		{
			$aPT['basic'] = number_format($aPT['ratem']/$SYSCONF['NUM'],2,'.', '');
		}
		elseif (in_array($aPT['pay_category'], array('2','4','6')))
		{
			$aPT['basic'] = number_format($aPT['adwr']* $aPT['actual_days'],2,'.','');
		}
		calcSum();
	vCompute();

}
elseif ($p1 == 'selectIncome')
{
	$dPTI['income_type_id'] = $_REQUEST['income_type_id'];
	$dPTI['income_code'] = $_REQUEST['income_code'];
	if ($source == 'code')
	{
		$q = "select * from income_type where income_code='".$dPTI['income_code']."'";
	}
	else
	{
		$q = "select * from income_type where income_type_id='".$dPTI['income_type_id']."'";
	}
	$qr = @pg_query($q) or message1(pg_errormessage());
	if (@pg_num_rows($qr) > 0)
	{
		$r = @pg_fetch_assoc($qr);
		$dPTI['income_code'] = $r['income_code'];
		$dPTI['income_type_id'] = $r['income_type_id'];
		$dPTI['income_basis'] = $r['basis'];
		$dPTI['income_rate'] = $r['rate'];
		$focus="income_qty";
	}
	else
	{
		message("Income Type NOT Found...");
		$focus="income_code";
	}

}
elseif ($p1 == 'selectDeduct')
{
	$dPTD['deduction_type_id'] = $_REQUEST['deduction_type_id'];
	$dPTD['deduction_code'] = $_REQUEST['deduction_code'];
	
	if ($source == 'id')
	{
		$q = "select * from deduction_type where deduction_type_id='".$dPTD['deduction_type_id']."'";
	}
	else
	{
		$q = "select * from deduction_type where deduction_code='".$dPTD['deduction_code']."'";
	}
	$qr = @pg_query($q) or message1(pg_errormessage());
	if (@pg_num_rows($qr) > 0)
	{
		$r = @pg_fetch_assoc($qr);
		$dPTD['deduction_type_id'] = $r['deduction_type_id'];
		$dPTD['deduction_code'] = $r['deduction_code'];
		$dPTD['deduction_basis'] = $r['basis'];
		$dPTD['deduction_rate'] = $r['rate'];
		$focus="deduction_qty";
		
		if ($dPTD['deduction_basis'] == 'L')
		{
			include_once('accountbalance.php');
			$b = employeeBalance($aPT['paymast_id'], $dPTD['deduction_type_id'], $SYSCONF['PERIOD2']);

			if ($b)
			{
				$dPTD['deduction_amount'] = $b['ammort'];
			}
		}
	}
	else
	{
		message("Deduction Type NOT Found...");
		$focus = 'deduction_code';
	}
}
elseif ($p1 == 'Cancel')
{
	if ($aPT['payroll_header_id'] != '')
	{
		$q = "update payroll_header set status = 'C' where payroll_header_id = '".$aPT['payroll_header_id']."'";
		$qr = @pg_query($q);
		if ($qr)
		{
			message("Transaction CANCELLED");
		}
	}	
}
elseif ($p1 == 'Save' && $SYSCONF['POST'] == 'Y')
{
	message1("<br> Payroll Transaction Already POSTED... NO updating allowed...");
}
elseif ($p1 == 'Save')
{
	vCompute();
//	$focus =  'income_code';

	begin();
	$ok=true;
	if ($aPT['payroll_header_id'] == '')
	{
		$audit = 'Entry: '.$ADMIN['username'].':'.date('m/d/Y').';';
		$q = "insert into payroll_header (paymast_id,payroll_period_id,date_entry,
						total_income,total_deduction, net_income, total_basic, basic,
						total_sss, total_tax,total_phic, 
						total_pagibig, admin_id,audit,status)
					values
						('".$aPT['paymast_id']."','".$aPT['payroll_period_id']."','".$aPT['date_entry']."',
						'".$aPT['total_income']."','".$aPT['total_deduction']."','".$aPT['net_income']."',
						'".$aPT['total_basic']."', '".$aPT['basic']."',
						'".$aPT['total_sss']."','".$aPT['total_tax']."','".$aPT['total_phic']."',
						'".$aPT['total_pagibig']."','".$ADMIN['admin_id']."','$audit','S')";
						
		$qr = @pg_query($q) or message1(pg_errormessage().$q);
		if (!$qr || pg_affected_rows($qr) == 0)
		{
			$ok=false;
		}
		else
		{
				$qid = query("select currval('payroll_header_payroll_header_id_seq'::text)");
				$rid = pg_fetch_object($qid);
				$aPT['payroll_header_id'] = $rid->currval;
				message('Transaction saved...');
				$aPT['status']='S';
		}

	}
	else
	{
			$audit = $aPT['audit'].'update:'.$ADMIN['username'].':'.date('m/d/Y').';';
			$q = "update payroll_header set 
						total_income = '".$aPT['total_income']."',
						total_deduction = '".$aPT['total_deduction']."',
						net_income = '".$aPT['net_income']."',
						basic = '".$aPT['basic']."',
						total_basic = '".$aPT['total_basic']."',
						total_sss = '".$aPT['total_sss']."',
						total_tax = '".$aPT['total_tax']."',
						total_phic = '".$aPT['total_phic']."',
						total_pagibig = '".$aPT['total_pagibig']."',
						status = 'S',
						actual_days = '".$aPT['actual_days']."',
						audit = '$audit'
					where
						payroll_header_id='".$aPT['payroll_header_id']."'";
			$qr = @pg_query($q) or message1(pg_errormessage().$q);
			if (!$qr || pg_affected_rows($qr) == 0)
			{
				$ok=false;
			}
			else
			{
				message('Transaction Updated...');
				$aPT['status']='S';
			}
				
	}
	if ($ok == true)
	{
		commit();
	}
	else
	{
		rollback();
		if ($aPT['payroll_header_id'] != '')
		{
			message('Unable to update payroll header...');
		}
		else
		{
			message('Unable to save transaction...');
		}
	}
	if ($aPT['payroll_header_id'] != '')
	{
		//save income details
		$cc=0;
		foreach ($iPTI as $temp)
		{
			if ($temp['payroll_detail_id'] == '')
			{
				begin();
				$q = "insert into payroll_detail (payroll_header_id,type,qty,amount,type_id)
						values
							('".$aPT['payroll_header_id']."','I',
							'".$temp['income_qty']."','".$temp['income_amount']."',
							'".$temp['income_type_id']."')";
				$qr = @pg_query($q) or message(pg_errormessage().$q);
				
				if ($qr && pg_affected_rows($qr)>0)
				{
					commit();
					$dummy = $temp;
					$qid = query("select currval('payroll_detail_payroll_detail_id_seq'::text)");
					$rid = pg_fetch_object($qid);
					$dummy['payroll_detail_id'] = $rid->currval;
					$iPTI[$cc] = $dummy;
				}
				else
				{
					rollback();
					message('Unable to save details...');
				}	
			}
			else
			{
				$q = "update payroll_detail set
						qty = '".$temp['income_qty']."',
						amount = '".$temp['income_amount']."',
						type_id = '".$temp['income_type_id']."'
					where
						payroll_detail_id='".$temp['payroll_detail_id']."'";
				$qr = @pg_query($q) or message(pg_errormessage());
			}
			$cc++;
		}

		//save deduction details
		$cc=0;
		foreach ($iPTD as $temp)
		{
			if ($temp['payroll_detail_id'] == '')
			{
				begin();
				$q = "insert into payroll_detail (payroll_header_id,type,qty,amount,type_id)
						values
							('".$aPT['payroll_header_id']."','D',
							'".$temp['deduction_qty']."',
							'".$temp['deduction_amount']."',
							'".$temp['deduction_type_id']."')";
				$qr = @pg_query($q) or message(pg_errormessage().$q);
				
				if ($qr && pg_affected_rows($qr)>0)
				{
					commit();
					$dummy = $temp;
					$qid = query("select currval('payroll_detail_payroll_detail_id_seq'::text)");
					$rid = pg_fetch_object($qid);
					$dummy['payroll_detail_id'] = $rid->currval;
					$iPTD[$cc] = $dummy;
				}
				else
				{
					rollback();
					message('Unable to save details...');
				}	
			}
			else
			{
				$q = "update payroll_detail set
						qty = '".$temp['deduction_qty']."',
						amount = '".$temp['deduction_amount']."',
						type_id = '".$temp['deduction_type_id']."'
					where
						payroll_detail_id='".$temp['payroll_detail_id']."'";
				$qr = @pg_query($q) or message(pg_errormessage());
			}
			$cc++;
		}
		
		//deduction
		
	}
}
elseif ($p1 == 'Print' && $aPT['status']!= 'S')
{
	message('Payroll Transaction must be Saved before Printing...');
}
elseif ($p1 == 'Print')
{
	include_once('payslip.php');
	printPayslip($aPT['paymast_id'], $aPT['payroll_header_id'],'Printer',true);
}
elseif ($p1 == 'ok' && $deduction_type_id!='')
{
	$aPT['status'] = 'M';

	$fields = array('deduction_type_id','deduction_qty','deduction_amount');
	for ($c=0;$c<count($fields);$c++)
	{
		$dPTD[$fields[$c]] = $_REQUEST[$fields[$c]];
		if ($dPTD[$fields[$c]] == '')
		{
			$dPTD[$fields[$c]] = 0;
		}
	}
	
	$q = "select * from deduction_type where deduction_type_id='".$dPTD['deduction_type_id']."'";
	$qr = @pg_query($q) or message(pg_errormessage());
	if ($qr == 0)
	{
		message('deduction Specified not found...');
	}
	else
	{
		$r = pg_fetch_assoc($qr);
		$dPTD += $r;

		$c = $fnd = 0;
		foreach ($iPTD as $temp)
		{
			if ($temp['deduction_type_id'] == $dPTD['deduction_type_id'])
			{
				$dummy = $temp;
				$dummy['deduction_qty'] = $dPTD['deduction_qty'];
				$dummy['deduction_amount'] = $dPTD['deduction_amount'];
				$iPTD[$c] = $dummy;
				$fnd=1;
				break;
			}
			$c++;
		}
		if ($fnd == 0)
		{
			$iPTD[] = $dPTD;
		}
		$dPTD = null;
		$dPTD=array();
	}		
	vCompute();
	$focus = 'deduction_code';
}
elseif ($p1 == 'deleteincome' && $ctr!='')
{
	$cc=0;
	$newArray = array();
	$cdel = '';
	foreach ($iPTI as $temp)
	{
		$cc++;
		if ($ctr == $cc)
		{
			if ($temp['payroll_detail_id']!='')
			{
				$cdel = 'income_type_id='.$temp['type_id'].' amount:'.$temp['income_amount'];
				$q="delete from payroll_detail where payroll_detail_id='".$temp['payroll_detail_id']."'";
				@pg_query($q) or message(pg_errormessage());
			}
		}
		else
		{
			$newArray[]=$temp;
		}
	}
	$iPTI = $newArray;
	vCompute();

	if ($aPT['payroll_header_id'] != '')
	{
		$audit = $aPT['audit'].'delete:'.$cdel.' by:'.$ADMIN['username'].':'.date('m/d/Y').';';
		$q = "update payroll_header set 
						total_income = '".$aPT['total_income']."',
						total_deduction = '".$aPT['total_deduction']."',
						net_income = '".$aPT['net_income']."',
						basic = '".$aPT['basic']."',
						total_basic = '".$aPT['total_basic']."',
						total_sss = '".$aPT['total_sss']."',
						total_tax = '".$aPT['total_tax']."',
						total_phic = '".$aPT['total_phic']."',
						total_pagibig = '".$aPT['total_pagibig']."',
						status = 'S',
						actual_days = '".$aPT['actual_days']."',
						audit = '$audit'
					where
						payroll_header_id='".$aPT['payroll_header_id']."'";
		$qr = @pg_query($q) or message1(pg_errormessage().$q);
		
	}
	$focus =  'income_code';
}
elseif ($p1 == 'editincome' && $ctr!='')
{
	$cc=0;
	foreach ($iPTI as $temp)
	{
		$cc++;
		if ($ctr == $cc)
		{
			$dPTI = $temp;
			break;
		}
	}
	$focus =  'income_code';
}
elseif ($p1 == 'editdeduction' && $ctr!='')
{
	$cc=0;
	foreach ($iPTD as $temp)
	{
		$cc++;
		if ($ctr == $cc)
		{
			$dPTD = $temp;
			break;
		}
	}
	$focus =  'deduction_code';
}

elseif ($p1 == 'deletededuction' && $ctr!='')
{
	$cc=0;
	$newArray = array();
	$cdel = '';
	foreach ($iPTD as $temp)
	{
		$cc++;
		if ($ctr == $cc)
		{
			if ($temp['payroll_detail_id']!='')
			{
				$cdel = 'deduction_type_id='.$temp['type_id'].' amount:'.$temp['income_amount'];
				$q="delete from payroll_detail where payroll_detail_id='".$temp['payroll_detail_id']."'";
				@pg_query($q) or message(pg_errormessage());
			}
		}
		else
		{
			$newArray[]=$temp;
		}
	}
	$iPTD = $newArray;
	vCompute();

	if ($aPT['payroll_header_id'] != '')
	{
		$audit = $aPT['audit'].'delete:'.$cdel.' by:'.$ADMIN['username'].':'.date('m/d/Y').';';
		$q = "update payroll_header set 
						total_income = '".$aPT['total_income']."',
						total_deduction = '".$aPT['total_deduction']."',
						net_income = '".$aPT['net_income']."',
						basic = '".$aPT['basic']."',
						total_basic = '".$aPT['total_basic']."',
						total_sss = '".$aPT['total_sss']."',
						total_tax = '".$aPT['total_tax']."',
						total_phic = '".$aPT['total_phic']."',
						total_pagibig = '".$aPT['total_pagibig']."',
						status = 'S',
						actual_days = '".$aPT['actual_days']."',
						audit = '$audit'
					where
						payroll_header_id='".$aPT['payroll_header_id']."'";
		$qr = @pg_query($q) or message1(pg_errormessage().$q);

	}
	$focus =  'income_code';

}

elseif ($p1 == 'Load')
{
	if ($SYSCONF['POST'] == 'Y')
	{
		message1("<br>Payroll Transaction Already Posted...Updates will NOT be saved...");
	}
	$aPT=null;
	$aPT=array();

	$iPTD=null;
	$iPTD=array();

	$iPTI=null;
	$iPTI=array();

	$dPTD=null;
	$dPTD=array();

	$dPTI=null;
	$dPTI=array();
}
elseif ($p1 == 'selectPaymastId' && $id != '')
{
	$aPT=null;
	$aPT=array();

	$iPTD=null;
	$iPTD=array();

	$iPTI=null;
	$iPTI=array();

	$q = "select * from payroll_header where status != 'C' and paymast_id ='$id' and payroll_period_id='".$SYSCONF['PAYROLL_PERIOD_ID']."'";
	$qr = @pg_query($q) or message(pg_errormessage().$q);

	if (@pg_num_rows($qr)  > 0)
	{
		$r = @pg_fetch_object($qr);
		$p1 = 'Load';
		$id = $r->payroll_header_id;
	}
	else
	{
		$q = "select * from paymast where paymast_id='$id'";
	
		$r = fetch_assoc($q);
		$aPT = $r;
		$aPT['employee'] = $aPT['elast'].', '.$aPT['efirst'];
		$aPT['actual_days'] = $SYSCONF['DAYS'];
		
		if (in_array($aPT['pay_category'], array('1','3')))
		{
			$aPT['basic'] = number_format($aPT['ratem']/$SYSCONF['NUM'],2,'.', '');
		}
		elseif (in_array($aPT['pay_category'], array('2','4','6')))
		{
			$aPT['basic'] = number_format($aPT['adwr']* $aPT['actual_days'],2,'.','');
		}
		
		include_once('accountbalance.php');
		$q = "select * from deduction_type where enable='Y' and basis = 'L'";
		$qqr = @pg_query($q) or message1(pg_errormessage());
		while ($rr =@pg_fetch_assoc($qqr))
		{
			$aBal = employeeBalance($aPT['paymast_id'], $rr['deduction_type_id'] , $SYSCONF['PERIOD2']);
			if ($aBal['ammort'] > 0)
			{
				$dummy = null;
				$dummy = array();
				$dummy['deduction_type_id'] = $rr['deduction_type_id'];
				$dummy['deduction_code'] = $rr['deduction_code'];
				$dummy['deduction_type'] = $rr['deduction_type'];
				$dummy['deduction_qty'] = '0';
				$dummy['deduction_amount'] = $aBal['ammort'];
				$iPTD[] = $dummy;
			}
		}
		
		
		calcSum();
		vCompute();
		$focus = "income_code";
	}	
}


if ($p1 == 'Load' && $id != '')
{
	$q = "select * from payroll_header where  status != 'C' and payroll_header_id='$id'";
	$r = fetch_assoc($q);

	$aPT = $r;
	$q = "select * from paymast where paymast_id='".$aPT['paymast_id']."'";
	$r = fetch_assoc($q);
	$aPT += $r;
	$aPT['employee'] = $aPT['elast'].', '.$aPT['efirst'];

	$q = "select 
					type_id as income_type_id,
					payroll_detail_id,
					qty  as income_qty,
					amount as income_amount,
					income_type,
					income_code,
					basis as income_basis,
					rate as income_rate,
					sss,
					phic,
					tax

				from 
					payroll_detail, income_type
				where
					income_type.income_type_id=payroll_detail.type_id and
					payroll_header_id='$id' and
					type='I'";
	$qr = @pg_query($q) or message(pg_errormessage());
	while ($r = pg_fetch_assoc($qr))
	{
		$iPTI[] = $r;
	}
	
	$q = "select 
					type_id as deduction_type_id,
					payroll_detail_id,
					qty  as deduction_qty,
					amount as deduction_amount,
					deduction_type,
					deduction_code,
					basis as deduction_basis,
					rate as deduction_rate,
					sss,
					phic,
					tax
				from 
					payroll_detail, deduction_type
				where
					deduction_type.deduction_type_id=payroll_detail.type_id and
					payroll_header_id='$id' and
					type='D'";
	$qr = @pg_query($q) or message(pg_errormessage());
	while ($r = pg_fetch_assoc($qr))
	{
		$iPTD[] = $r;
	}
	
	calcSum();
	vCompute();	
	
}

?><br>
<form  id="f1" name="f1" method="post" action="">
  <table width="99%" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF">
    <tr> 
      <td colspan="8"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Find 
        <input name="xSearch" type="text" id="xSearch" value="<?= $xSearch;?>" class="altText">
        <?= lookUpPayPeriod('payroll_period_id',$payroll_period_id);?>
        <input name="button" type="button" id="button" value="Go" onClick="window.location='?p=payroll.transaction.browse&p1=Go&xSearch='+f1.xSearch.value" class="altBtn">
        <input name="p1" type="submit" id="p1" value="New" class="altBtn">
        <input type="button" name="Submit22" value="Browse" onClick="window.location='?p=payroll.transaction.browse&p1=Browse'"  class="altBtn">
        </font> <hr color="#993300"> </td>
    </tr>
    <tr> 
      <td width="50%" colspan="4"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Employee 
        <input name="xSearch" type="text" id="idno" value="<?= $xSearch;?>" size="8" class="altText"  onKeypress="if(event.keyCode==13) {document.getElementById('search').focus();return false;}">
        <input name="p1" type="submit" id="search" value="Search"  class="altBtn">
        <input name="employee" type="text" readonly id="employee" value="<?= $aPT['employee'];?>" size="30" class="altText">
        </font></td>
      <td  colspan="4" width="50%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Payroll 
        Period: <strong> 
        <?=$SYSCONF['PAYROLL_PERIOD'];?>
        </strong>Days
        <input name="actual_days" type="text" id="actual_days" value="<?= $aPT['actual_days'];?>" size="4" class="altText"  onKeypress="if(event.keyCode==13) {document.getElementById('income_code').focus();return false;}" onChange="document.getElementById('f1').action='?p=payroll.transaction&p1=update';document.getElementById('f1').submit()">
        </font></td>
    </tr>
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; SSS: 
        <strong> 
        <?= vLogic($aPT['sssw']);?>
        </strong></font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">PHIC:<strong> 
        <?= vLogic($aPT['phicw']);?>
        </strong></font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">PagIbig:<strong> 
        <?= vLogic($aPT['pagibigw']);?>
        </strong></font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">WTax:<strong> 
        <?= vLogic($aPT['taxw']);?>
        </strong></font></td>
      <td>
            <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Monthly: 
              <strong><input type="text" readOnly name="ratem" id="ratem" value="<?= $aPT['ratem'];?>" size="5"  style="border: #CCCCCC 0px solid; text-align:right;font-weight:bold;s font-color:#000099"> 
              </strong></font></td>
            
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Daily: <strong> 
        <input type="text" readOnly name="adwr" id="adwr" value="<?= $aPT['adwr'];?>" size="5"  style="border: #CCCCCC 0px solid; text-align:right;font-weight:bold;s font-color:#000099">
        /
<input type="text" readOnly name="tenureallowance" id="tenureallowance" value="<?= $aPT['tenureallowance'];?>" size="5"  style="border: #CCCCCC 0px solid; text-align:right;font-weight:bold;s font-color:#000099">
        </strong></font></td>
            
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Hourly: 
        <strong> 
        <input type="text" readOnly name="hourly" id="hourly" value="<?= $aPT['hourly'];?>" size="5"  style="border: #CCCCCC 0px solid; text-align:right;font-weight:bold;s font-color:#000099">
        </strong></font></td>
          </tr>
      </tr>
 
        </table></td>
    </tr>
  </table>
  <?
  if ($p1 == 'Search')
  {
  ?>
  <hr color="#993300" align="center" width="97%">	
  <table width="80%" border="0" align="center" cellpadding="2" cellspacing="1" bgcolor="#EFEFEF">
    <tr bgcolor="#FFCCFF"> 
      <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
      <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Id 
        No</font></strong></td>
      <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Last 
        Name</font></strong></td>
      <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">First 
        Name</font></strong></td>
      <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Department</font></strong></td>
    </tr>
	<?
		$q = "select * from paymast where enable='Y' and (elast ||', '|| efirst) ilike '$xSearch%' order by elast, efirst";

		$qr = @pg_query($q) or message(pg_errormessage().$q);
		$ctr=0;
		while ($r = pg_fetch_object($qr))
		{
			$ctr++;
	?>
    <tr bgcolor="#FFFFFF" onMouseOver="bgColor='#FFFFCC'" onMouseOut="bgColor='#FFFFFF'"> 
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><?=$ctr;?>.</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><?= $r->idnum;?></font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
	  <a href="?p=payroll.transaction&p1=selectPaymastId&id=<?=$r->paymast_id;?>">
	  <?= $r->elast;?></a></font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><?= $r->efirst;?></font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><?= lookUpTableReturnValue('x','department','department_id','department',$r->department_id);?></font></td>
    </tr>
	<?
		}
	?>
  </table>
	<br>
  <?
  }
  ?>
  <table width="99%" border="1" align="center" cellpadding="0" cellspacing="1" bgcolor="#FFFFFF">
    <tr bgcolor="#EFEFEF"> 
      <td colspan="3" width="50%"><table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr> 
            <td colspan="4"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
              &nbsp; Income<br>
              &nbsp; 
              <input name="income_code" type="text" id="income_code" value="<?= $dPTI['income_code'];?>" size="4" class="altText" onChange="vInc('code')"  onKeypress="if(event.keyCode == 13){document.getElementById('income_type_id').focus(); return false;}">
              <select name="income_type_id" id="income_type_id" style="width:150" onChange="vInc('id')"  onKeypress="if(event.keyCode == 13){document.getElementById('income_qty').focus(); return false;}">
                <option value="">Choose Income</option>
                <?
			  $q = "select * from income_type where enable='Y' order by income_code";
			  $qr = @pg_query($q);
			  while ($r = @pg_fetch_object($qr))
			  {
			  	if ($dPTI['income_type_id'] == $r->income_type_id)
				{
			  		echo "<option value=$r->income_type_id selected>$r->income_code $r->income_type</option>";
				}
				else
				{
			  		echo "<option value=$r->income_type_id>$r->income_code $r->income_type</option>";
				}
			  }
			  ?>
              </select>
              <input name="income_qty" type="text" id="income_qty" value="<?= $dPTI['income_qty'];?>" size="6" class="altNum"  onKeypress="if(event.keyCode==13) {document.getElementById('income_amount').focus();return false;}" onBlur="vICompute()">
              <input name="income_basis" type="hidden" id="income_basis" value="<?= $dPTI['income_basis'];?>" size="6">
              <input name="income_amount" type="text" id="income_amount" value="<?= $dPTI['income_amount'];?>" size="8" class="altNum"  onKeypress="if(event.keyCode==13) {document.getElementById('Ok').focus();return false;}">
              <input name="p1" type="submit" id="Ok" value="Ok"  class="altBtn">
              <input name="income_rate" type="hidden" id="income_rate" value="<?= $dPTI['income_rate'];?>" size="6">
              </font></td>
          </tr>
          <tr bgcolor="#D7D7D7"> 
            <td width="7%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
            <td width="43%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Income 
              Description</font></strong></td>
            <td width="25%" align="center" bgcolor="#D7D7D7"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Qty</font></strong></td>
            <td width="25%" align="center" bgcolor="#D7D7D7"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Amount</font></strong></td>
          </tr>
          <tr bgcolor="#FFFFFF"> 
            <td height="240" colspan="4" valign="top">
			<?
			include_once('payroll.transaction.income.php');
			?>
            </td>
          </tr>
        </table></td>
      <td colspan="3"><table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr> 
            <td colspan="4"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;Deduction<br>
              &nbsp; 
              <input name="deduction_code" type="text" id="deduction_code" value="<?= $dPTD['deduction_code'];?>" size="4"  class="altText"  onChange="vDed('code')" onKeypress="if(event.keyCode == 13){document.getElementById('deduction_type_id').focus(); return false;}">
              <select name="deduction_type_id" id="deduction_type_id" style="width:150" onChange="vDed('id')">
                <option value="">Choose Deduction</option>
                <?
			  $q = "select * from deduction_type where enable='Y' order by deduction_code";
			  $qr = @pg_query($q);
			  while ($r = @pg_fetch_object($qr))
			  {
			  	if ($dPTD['deduction_type_id'] == $r->deduction_type_id)
				{
			  		echo "<option value=$r->deduction_type_id selected>$r->deduction_code $r->deduction_type</option>";
				}
				else
				{
			  		echo "<option value=$r->deduction_type_id>$r->deduction_code $r->deduction_type</option>";
				}
			  }
			  ?>
              </select>
              <input name="deduction_basis" type="hidden" id="deduction_basis" value="<?= $dPTD['deduction_basis'];?>" size="6">
              <input name="deduction_qty" type="text" id="deduction_qty" value="<?= $dPTD['deduction_qty'];?>" size="6"  class="altNum"  onKeypress="if(event.keyCode==13) {document.getElementById('deduction_amount').focus();return false;}" onBlur="vDCompute();">
              <input name="deduction_amount" type="text" id="deduction_amount" value="<?= $dPTD['deduction_amount'];?>" size="8"  class="altNum"  onKeypress="if(event.keyCode==13) {document.getElementById('ok').focus();return false;}">
              <input name="p1" type="submit" id="ok" value="ok"  class="altBtn">
              <input name="deduction_rate" type="hidden" id="deduction_rate" value="<?= $dPTI['deduction_rate'];?>" size="6">
              </font></td>
          </tr>
          <tr bgcolor="#D7D7D7"> 
            <td width="7%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
            <td width="43%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Deduction 
              Description</font></strong></td>
            <td width="25%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Qty</font></strong></td>
            <td width="25%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Amount</font></strong></td>
          </tr>
          <tr bgcolor="#FFFFFF"> 
            <td height="240" colspan="4" valign="top"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
              <?
			include_once('payroll.transaction.deduction.php');
			?>
              </font></td>
          </tr>
        </table></td>
    </tr>
	</table>
	
  <table width="99%" border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#FFFFFF">
    <tr bgcolor="#EFEFEF"> 
      <td width="50%" height="32" align="right"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <?= ($aPT['status'] == 'C'?'*** CANCELLED ****  ':'');?>
        Income Sub Total &nbsp&nbsp;&nbsp;&nbsp;&nbsp;</font></strong><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= number_format($aPT['subtotal_income'],2);?>
        &nbsp; </font></strong></td>
      <td width="50%" align="right"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= ($aPT['status'] == 'C'?'*** CANCELLED ****  ':'');?>
        Deduction SubTotal &nbsp&nbsp;&nbsp;&nbsp;&nbsp;</font></strong><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
        <?= number_format($aPT['subtotal_deduction'],2);?>
        &nbsp; </font></strong></td>
    </tr>
    <tr bgcolor="#EFEFEF"> 
      <td colspan="2"> <table width="100%" border="0" cellpadding="0" cellspacing="0">
          <tr> 
            <td width="12%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Basic</font></td>
            <td width="12%"><input name="basic" type="text" id="basic" value="<?= $aPT['basic'];?>" size="10" readOnly  class="altNum"></td>
            <td width="13%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">SSS 
              Contribution</font></td>
            <td width="13%"><input name="total_sss" type="text" id="total_sss" value="<?= $aPT['total_sss'];?>" size="10" readOnly class="altNum"></td>
            <td width="12%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;</font><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Tax 
              Withheld</font></td>
            <td width="12%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
              <input name="total_tax" type="text" id="total_tax" value="<?= $aPT['total_tax'];?>" size="10" readOnly class="altNum">
              </font></td>
            <td width="11%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
              Total Income</font></td>
            <td width="11%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
              <input name="total_income" type="text" id="total_income"  readOnly value="<?= $aPT['total_income'];?>" size="10" class="altNum">
              </font></td>
          <tr bgcolor="#EFEFEF"> 
            <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Total 
              Basic</font></td>
            <td><input name="total_basic" type="text" id="total_basic" value="<?= $aPT['total_basic'];?>" size="10" readOnly  class="altNum"></td>
            <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">PHIC 
              Contribution</font></td>
            <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
              <input name="total_phic" type="text" id="total_phic" value="<?= $aPT['total_phic'];?>" size="10" readOnly class="altNum">
              </font></td>
            <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;</font><font size="2" face="Verdana, Arial, Helvetica, sans-serif">PagIbig 
              Contribution</font></td>
            <td><input name="total_pagibig" type="text" id="total_pagibig" value="<?= $aPT['total_pagibig'];?>" size="10" readOnly class="altNum"></td>
            <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Total 
              Deduction</font></td>
            <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
              <input name="total_deduction" type="text" id="total_deduction" value="<?= $aPT['total_deduction'];?>" size="10" readonly class="altNum">
              </font></td>
          </tr>
          <tr bgcolor="#EFEFEF"> 
            <td>&nbsp;</td>
            <td> <font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
              </font></td>
            <td nowrap>&nbsp;</td>
            <td>&nbsp;</td>
            <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;</font></td>
            <td>&nbsp;</td>
            <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Net 
              Income </font></td>
            <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
              <input name="net_income" type="text" id="net_income" value="<?= $aPT['net_income'];?>" size="10" readonly class="altNum">
              </font></td>
          </tr>
        </table></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td bgcolor="#EFEFEF"><table width="1%" border="0" cellpadding="1" cellspacing="1" bgcolor="#CCCCCC">
          <tr bgcolor="#FFFFFF"> 
            <td nowrap width="3%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              <input type="image" src="../graphics/save.jpg" alt="Save This Form" width="57" height="15" id="Save" onClick="document.getElementById('f1').action='?p=payroll.transaction&p1=Save';document.getElementById('f1').submit();" name="Save"  accesskey="S">
              </strong></font></td>
            <td nowrap width="46%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              <input type="image" src="../graphics/print.jpg" alt="Print This Claim Form"  name="Print" id="Print" onClick="document.getElementById('f1').action='?p=payroll.transaction&p1=Print';document.getElementById('f1').submit();">
              </strong></font></td>
            <!--<td nowrap width="26%"> <input name="Cancel" id="Cancel" type="image" src="../graphics/cancel.jpg" onClick="vSubmit(this)" alt="Cancel This Entry" width="77" height="20"></td>-->
            <td nowrap width="25%"> <input type='image' name="Cancel" id="Cancel" onClick="if (confirm('Are you sure to CANCEL this Entry?')){document.getElementById('f1').action='?p=payroll.transaction&p1=Cancel';document.getElementById('f1').submit()};"  src="../graphics/cancel.jpg" alt="Cancel Form" width="77" height="20"> 
            <td nowrap width="25%"> <input type='image' name="New" id="New" onClick="document.getElementById('f1').action='?p=payroll.transaction&p1=New';document.getElementById('f1').submit();"  src="../graphics/new.jpg" alt="New Claim Form" width="63" height="20"> 
            </td>
          </tr>
        </table> </td>
      <td bgcolor="#EFEFEF"><font size="2"><a href="?p=payroll.transaction&p1=Previous">&lt;&lt; 
        Previous</a> | <a href="?p=payroll.transaction&p1=Next">Next &gt;&gt;</a></font></td>
    </tr>
  </table>
</form>
<?

if ($focus != '')
{
	echo "<script>document.getElementById('$focus').focus();</script>";
}
?>