<?
function lookUpPayPeriod($name,$value)
{
	
	echo "<select name=$name><option value=''>Select Payroll Period</option>";
	$q = "select * from payroll_period order by period1";
	$qr =@pg_query($q) or message(pg_errormessage());
	
	while ($r = pg_fetch_object($qr))
	{
		if ($r->payroll_period_id == $value )
		{
			echo "<option value=$r->payroll_period_id selected>".ymd2mdy($r->period1).'-'.ymd2mdy($r->period2)." ($r->days days)</option>";
		}
		else
		{
			echo "<option value=$r->payroll_period_id>".ymd2mdy($r->period1).'-'.ymd2mdy($r->period2)." ($r->days days)</option>";
		}	
	}
	echo "</select>";

}
function lookUpPayPeriodReturnValue($name, $value)
{
	$q = "select * from payroll_period where payroll_period_id='$value'";
	$qr = @pg_query($q) or message(pg_errormessage());
	$R = @pg_fetch_object($qr);
	$period = ymd2mdy($R->period1).'-'.ymd2mdy($R->period2)." ($R->days)";
	return $period;
}

function rangePayrollPeriod($from, $to)
{
	$q = "select payroll_period_id
			 from 
			 	payroll_period where enable='Y' and 
			 	period1>='$from' and period2<='$to'";
			 	
	$qr = @pg_query($q) or message1(pg_errormessage().$q);
	
	$pid = '';
	while ($r = @pg_fetch_object($qr))
	{
		if (strlen($pid)>0) $pid.=',';
		$pid .= $r->payroll_period_id;
	}
	
	return $pid;
}

function sumPeriodic($date_from, $date_to,$array_paymast_id)
{
	$asum = null;
	$asum = array();
	
	$q = "select * 
				from
					 payroll_period 
				where
					period1>='$date_from' and
					period2<='$date_to' and
					enable='Y'";
	
	$qr = @pg_query($q) or message(pg_errormessage().$q);
	while ($r = @pg_fetch_object($qr))
	{

		$qq = "select * 	
						from 
							payroll_header as ph
						where 
							ph.status!='C' and 
							ph.payroll_period_id = '$r->payroll_period_id'";
		if ($array_paymast_id != '')
		{ 
			$qq .=" and	ph.paymast_id in ($array_paymast_id)";
		}
			
		$qpr = @pg_query($qq) or message1(pg_errormessage().$q);
			
		if (@pg_num_rows($qpr) == 0) continue;
		 
		
		while($rp = @pg_fetch_object($qpr))
		{	
			$array_found = $array_index = 0;
			foreach ($asum as $temp)
			{
					if ($temp['paymast_id'] == $rp->paymast_id)
					{
						$dummy = $temp;
						$array_found = 1;
						break;
					}
					$array_index++;
			}
			if ($array_found == 0)
			{
					$dummy = null;
					$dummy = array();
					$dummy['paymast_id'] = $rp->paymast_id;
			}
			
			$payroll_header_id = $rp->payroll_header_id;
			$dummy['accu_sss'] += $rp->total_sss;
			$dummy['accu_tax'] += $rp->total_tax;
			$dummy['accu_phic'] += $rp->total_phic;
			$dummy['accu_pagibig'] += $rp->total_pagibig;
			$dummy['accu_totalbasic'] += $r->total_basic;
			$dummy['accu_basic'] += $rp->total_basic;
			$dummy['accu_income'] += $rp->total_income;
			$dummy['accu_deduction'] += $rp->total_deduction;
			$dummy['accu_netincome'] += $rp->total_netincome;
			
			$dummy['accu_sssbasis'] += $rp->basic;
			$dummy['accu_taxbasis'] += $rp->basic;
			$dummy['accu_phicbasis'] +=  $rp->basic;
			$dummy['accu_pagibigbasis'] += $rp->basic;


			$q = "select * from payroll_detail 
						where payroll_header_id = '$payroll_header_id' and enable='Y'";
			$qqr = @pg_query($q) or message(pg_errormessage().$q);
			
			while ($rr = @pg_fetch_object($qqr))
			{

				if ($rr->type == 'I')
				{
					$dummy['accu_grossincome'] += $rr->amount;
					$table = 'income_type';
					$fld_id = 'income_type_id';
					$type_code ='income_code';
					
					$amount = $rr->amount;
				}
				else
				{
					$table = 'deduction_type';
					$fld_id = 'deduction_type_id';
					$type_code ='deduction_code';

					$amount = $rr->amount * -1;
				}
					
				$qi = "select * from $table where $fld_id = '$rr->type_id'";
				$qir = @pg_query($qi) or message1(pg_errormessage());
				$ri = @pg_fetch_object($qir);
				if ($ri->sss == 'Y')
				{
					$dummy['accu_sssbasis'] += $amount;
				}
				if ($ri->tax  == 'Y')
				{
					$dummy['accu_taxbasis'] += $amount;
				}
				if ($ri->phic == 'Y')
				{
					$dummy['accu_phicbasis'] += $amount;
				}	
			}				

		
					
			//if ($dummy['paymast_id'] == '46') echo "amt ".$dummy['accu_sssbasis'];
			if ($array_found == 0)
			{
				$asum[] = $dummy;
			}
			else
			{
				$asum[$array_index] = $dummy;
			}
		}
	}
	return $asum;
}

function sumPayroll($aPM, $pp_id)
{
/*
	//-- summarizes the payroll transaction for the month for recomputation of SSS, PHIC,TAX,
	//-- example: if it is the second cutoff, this will compute for the income and deductions of 
	//-- the previous cutoff(s)
	//-- where $aPM = array that contains employee info and header of the payroll transaction
	//-- $pp_id = current payroll period id
*/
	 
	$q = "select * from payroll_period where payroll_period_id = '$pp_id'";
	$qr = @pg_query($q) or message(pg_errormessage());
	$r = @pg_fetch_object($qr);
	$num = $r->num;
	$num2 = $r->num2;
	$days = $r->days;
	$month = $r->month;
	$year = $r->year;
	$schedule =  $r->schedule;	

	$aPM['accu_sssbasis'] = $aPM['accu_taxbasis'] = $aPM['accu_phicbasis'] = $aPM['accu_pagibigbasis'] =0;
	$aPM['accu_grossincome'] = $aPM['accu_netincome'] = $aPM['accu_income'] = $aPM['accu_deduction'] = 0;
	$aPM['accu_basic'] = $aPM['accu_sss'] = $aPM['accu_tax'] = $aPM['accu_phic'] = $aPM['accu_pagibig'] = 0;

	if ($num <= 1)
	{
		return true;
	}
	else
	{
		$q = "select * from payroll_period 
						where 
							schedule='$schedule' and
							month = '$month' and
							year = '$year' and  
							num<'$num' and 
							enable='Y'";
		$qr = @pg_query($q) or message1(pg_errormessage());

		while ($r = @pg_fetch_object($qr))
		{


				$qq = "select * 	
								from 
									payroll_header as ph 
								where 
									ph.status!='C' and
									ph.payroll_period_id = '$r->payroll_period_id' and 
									ph.paymast_id = '".$aPM['paymast_id']."'";
				$qqr = @pg_query($qq) or message1(pg_errormessage());
				$rr = @pg_fetch_object($qqr);
			

				$aPM['accu_sssbasis'] += $rr->basic; //$aPM['accu_basic'];
				$aPM['accu_taxbasis'] +=  $rr->basic; //$aPM['accu_basic'];
				$aPM['accu_phicbasis'] +=   $rr->basic; //$aPM['accu_basic'];
				$aPM['accu_pagibigbasis'] += $rr->basic; // $aPM['accu_basic'];

				$aPM['accu_sss'] += $rr->total_sss;
				$aPM['accu_tax'] += $rr->total_tax;
				$aPM['accu_phic'] += $rr->total_phic;
				$aPM['accu_pagibig'] += $rr->total_pagibig;
				$aPM['accu_totalbasic'] += $rr->total_basic;
				$aPM['accu_basic'] += $rr->basic;
				$aPM['accu_income'] += $rr->total_income;
				$aPM['accu_deduction'] += $rr->total_deduction;
				$aPM['accu_netincome'] += $rr->total_netincome;
					

				$qd = "select * 	
								from 
									payroll_detail as pd 
								where 
									pd.enable='Y' and
									pd.payroll_header_id = '$rr->payroll_header_id'"; 
									
				$qdr = @pg_query($qd) or message1(pg_errormessage().$qd);

				while ($rd = @pg_fetch_object($qdr))
				{
					if ($rd->type == 'I')
					{
						$aPM['accu_grossincome'] += $rd->amount;
						$table = 'income_type';
						$fld_id = 'income_type_id';
						$type_code ='income_code';
					
						$amount = $rd->amount;
					}
					else
					{
						$table = 'deduction_type';
						$fld_id = 'deduction_type_id';
						$type_code ='deduction_code';

						$amount = $rd->amount * -1;
					}
							
					$qi = "select * from $table where $fld_id = '$rd->type_id'";
					$qir = @pg_query($qi) or message1(pg_errormessage());
					$ri = @pg_fetch_object($qir);
				
				
					if ($ri->sss == 'Y')
					{
						$aPM['accu_sssbasis'] += $amount;
					}
					if ($ri->tax  == 'Y')
					{
						$aPM['accu_taxbasis'] += $amount;
					}
					if ($ri->phic == 'Y')
					{
						$aPM['accu_phicbasis'] += $amount;
					}
					
				}				
		}
		//print_r($aPM);
	}
	return $aPM;
}

function computeDeduction($aPM)
{
	//--computes for the standard deductions SSS, PHIC, TAX, PabIbig
	//-- where $aPM - array that contains the payroll transaction header. 

	$sss_basis = $aPM['sss_basis'] + $aPM['accu_sssbasis'];
	$phic_basis = $aPM['phic_basis'] + $aPM['accu_phicbasis'];
	$tax_basis = $aPM['tax_basis'] + $aPM['accu_taxbasis'];
	$pagibig_basis = $aPM['pagibig_basis'] + $aPM['accu_pagibigbasis'];

	
	if ($aPM['sssw'] == '1')
	{
		$q = "select * from ssstable where income_from<='$sss_basis' order by income_to desc offset 0 limit 1";
		$qr = @pg_query($q) or message1(pg_errormessage().$q);
		$r = @pg_fetch_object($qr);

		$aPM['total_sss'] = $r->employee - $aPM['accu_sss'];
		
	}
	else
	{
		$aPM['total_sss'] = 0.00;
	}
	$aPM['total_deduction'] += $aPM['total_sss'];

	if ($aPM['phicw'] == '1')
	{
		$q = "select * from phictable where income_from<='$phic_basis'  order by income_to desc offset 0 limit 1";
		$qr = @pg_query($q) or message1(pg_errormessage().$q);
		$r = @pg_fetch_object($qr);
		
		$aPM['total_phic'] = $r->employee - $aPM['accu_phic'];
	
	}
	else
	{
		$aPM['total_phic'] = 0.00;
	}
	$aPM['total_deduction'] += $aPM['total_phic'];

	if ($aPM['taxw'] == '1')
	{
		$q = "select * from wtaxtable where taxcode='".$aPM['taxcode']."' and income_from<='$tax_basis'  order by income_to desc offset 0 limit 1";
		$qr = @pg_query($q) or message1(pg_errormessage().$q);
		$r = @pg_fetch_object($qr);
		$aPM['total_tax'] = ($r->basic_deduction + ($tax_basis - $r->income_from)*$r->percent_add) - $aPM['accu_tax'];

	}
	else
	{
		$aPM['total_tax'] = 0.00;
	}
	$aPM['total_deduction'] += $aPM['total_tax'];

	if ($aPM['pagibigw'] == '1')
	{
	
		//$aPM['total_pagibig'] = $pagibig_basis*.02  - $aPM['accu_pagibig']; //2% fixed rate for pagibig
		$aPM['total_pagibig']=50;
		if ($aPM['total_pagibig'] > 100)
		{
			if ($aPM['accu_pagibig'] < 100)
			{
				$aPM['total_pagibig'] = 100 - $aPM['accu_pagibig'];
			}
			else
			{
				$aPM['total_pagibig'] = 0;
			}
		}
	}
	else
	{
		$aPM['total_pagibig']=0.00;
	}
	$aPM['total_deduction'] += $aPM['total_tax'];

	$aPM['net_income']  = $aPM['total_income'] - $aPM['total_deduction'];


	return $aPM;
	
}

?>