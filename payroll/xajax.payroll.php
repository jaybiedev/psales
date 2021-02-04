<?
function paymastLevel($form)
{
	$level_id = $form['level_id'];
	if ($level_id == '' or $level_id== '0')
	{
		return done();
	}
	$q= "select * from payroll.level where level_id = '$level_id'";
	$qr = @pg_query($q);
	if (!$qr)
	{
		galert('Error querying level...'.pg_errormessage().$q);
		return done();
	}
	
	if (@pg_num_rows($qr)==0)
	{
		galert(' Salary Level NOT found...');
	}
	else
	{
		
		$r = @pg_fetch_object($qr);
		gset('ratem',$r->ratem);
		gset('pay_category',$r->pay_category);
		gset('adwr',$r->adwr);
		gset('hourly',$r->hourly);
	}
	
	return done();
}

function viewmemo($id)
{
	$q = "select * from payroll.memo where memo_id = '$id'";
	$qr = @pg_query($q);
	if (!$qr)
	{
		galert('Error Querying Memo '.pg_errormessage().$q);
		return done();
	}
	if (@pg_num_rows($qr) == 0)
	{
		galert(' Memo Selected NOT found...');
		return done();
	}
	$r = @pg_fetch_object($qr);
	gset('memo',$r->memo);
	return done();
}

function payroll_posting_process($pid)
{
	global $ADMIN;
	$return = 1;

	if (!chkRights3("payrollposting","madd",$ADMIN['admin_id']))
	{
		galert("You have no permission in this area...");
		return -10;
	}
	$q= "select * from payroll.payroll_period where payroll_period_id = '$pid'";
	$qr = @pg_query($q);
	if (!$qr )
	{
		galert("Error Selecting Payroll Period Posting...");
		return -11;
	}
	$r = @pg_fetch_object($qr);
	$audit = $r->audit; 
	if ($r->post == 'Y')
	{
		return 0;
	}		
	begin();
	$q = "select 
					ph.paymast_id,
					pd.type_id as deduction_type_id,
					pd.amount
				from 
					payroll.payroll_header as ph,
					payroll.payroll_detail as pd 
				where 
					ph.payroll_header_id = pd.payroll_header_id and 
					ph.payroll_period_id = '$pid' and 
					ph.status!='C' and
					pd.enable='Y' and
					pd.type='D'";
		//			and paymast_id = '120'";
	$qr = @pg_query($q);
	
	if (!$qr)
	{
		galert(pg_errormessage().$q);
		$return = -1;
	}
	else
	{
		while ($r = @pg_fetch_object($qr))
		{
			$q = "select * 
						from 
							payroll.payrollcharge
						where 
							enable='Y' and 
							paymast_id = '$r->paymast_id' and
							deduction_type_id = '$r->deduction_type_id' and 
							balance>'0'
						order by
							date";
			$qqr = @pg_query($q);
			if (!$qqr)
			{
				galert(' 1 '.pg_errormessage().' Sql:'.$q);
				
				$return = -2;
				break;
			}	
			else
			{
				$deduction_amount = $r->amount;

				while ($rr = @pg_fetch_object($qqr))
				{
					
					if ($deduction_amount >= $rr->balance)
					{
						$deduction_amount -= $rr->balance;
						$deduct = $rr->deduct + $rr->balance;
						$balance = 0;
					}
					else
					{
						$balance = $rr->balance - $deduction_amount;
						$deduct = $rr->deduct + $deduction_amount;
						$deduction_amount = 0;
						
					}
					$qu = "update payroll.payrollcharge set 
									deduct='$deduct',
									balance='$balance' 
							where payrollcharge_id = '$rr->payrollcharge_id'";
					$qur = @pg_query($qu);
					if (!$qur )
					{
						galert("Error Updating Payroll Charges...".pg_errormessage());
						$return= -3;
						break;
					}
					if ($deduction_amount <= 0)
					{
						break;
					}
				}
			}//endif
		}
		
		if ($return > 0)
		{
				$audit = $audit . 'Posted by '.$ADMIN['username'].' on '.date('m/d/Y g:ia').';';
				$q = "update payroll.payroll_period set post='Y', audit = '$audit'
							 where payroll_period_id = '$pid'";
				$qr = @pg_query($q);
				if (!$qr )
				{
					galert("Error Updating Payroll Period Posting...".pg_errormessage().$q);
					return -5;
					break;
				}
				

		}
	}
	if ($return > 0)
	{
		commit();
	}
	else
	{
		rollback();
	}
	return $return;
}

function payroll_unpost_process($pid)
{
	global $ADMIN;
	$return = 1;

	if (!chkRights3("payrollposting","madd",$ADMIN['admin_id']))
	{
		galert("You have no permission in this area...");
		return -10;
	}
	$q= "select * from payroll.payroll_period where payroll_period_id = '$pid'";
	$qr = @pg_query($q);
	if (!$qr )
	{
		galert("Error Selecting Payroll Period Posting...");
		return -11;
	}
	$r = @pg_fetch_object($qr);
	$audit = $r->audit; 
	begin();
	$audit = $audit . 'UNPosted by '.$ADMIN['username'].' on '.date('m/d/Y g:ia').';';
	$q = "update payroll.payroll_period set post='N', audit = '$audit'
				 where payroll_period_id = '$pid'";
	$qr = @pg_query($q);
	if (!$qr )
	{
		galert("Error Updating Payroll Period Posting...".pg_errormessage().$q);
		return -5;
		break;
	}
	else
	{
		commit();
	}
	return $return;				
}

function payroll_recalc_account_process($paymast_id, $deduction_type_id)
{
	global $ADMIN;
	$return = 1;

	if (!chkRights3("payrollposting","madd",$ADMIN['admin_id']))
	{
		galert("You have no permission in this area...");
		return -10;
		
	}
	$q = "update payroll.payrollcharge
				set
					balance=(credit-debit),
					deduct=0
				where
					enable='Y'";

			if ($paymast_id != '')
			{
				$q .= " and payroll.payrollcharge.paymast_id = '$paymast_id'";
			}		

			if ($deduction_type_id != '')
			{
				$q .= " and payroll.payrollcharge.deduction_type_id = '$deduction_type_id'";
			}		

	$qr = @pg_query($q);
	
	if (!$qr)
	{
		galert(pg_errormessage().$q);
		$return = -1;
	}
	else
	{
		$q = "select 
					ph.paymast_id,
					pd.type_id as deduction_type_id,
					sum(pd.amount) as amount
					
				from 
					payroll.payroll_header as ph,
					payroll.payroll_detail as pd,
					payroll.payroll_period as pp 
				where 
					pp.payroll_period_id = ph.payroll_period_id and 
					ph.payroll_header_id = pd.payroll_header_id and 
					pp.post='Y' and 
					ph.status!='C' and
					pd.enable='Y' and
					pd.type='D'";

			if ($paymast_id != '')
			{
				$q .= " and ph.paymast_id = '$paymast_id'";
			}		

			if ($deduction_type_id != '')
			{
				$q .= " and pd.type_id = '$deduction_type_id'";
			}		
			$q .= " group by ph.paymast_id, pd.type_id, pd.amount";
			
		$qr = @pg_query($q);
	
		if (!$qr)
		{
			galert(pg_errormessage().$q);
			$return = -1;
		}
		else
		{
			while ($r = @pg_fetch_object($qr))
			{

				$q = "select * 
						from 
							payroll.payrollcharge
						where 
							enable='Y' and
							deduction_type_id = '$r->deduction_type_id' and
							paymast_id = '$r->paymast_id' and  
							balance>0 
						order by date";
			
				$qqr = @pg_query($q);
				if (!$qqr)
				{
					galert(pg_errormessage().$q);
					$return = -2;
					break;
				}	
				else
				{
					$deduction_amount = $r->amount;
					
					while ($rr = @pg_fetch_object($qqr))
					{
					
						if ($deduction_amount >= $rr->balance)
						{
							$deduction_amount -= $rr->balance;
							$deduct = $rr->deduct + $rr->balance;
							$balance = 0;
						}
						else
						{
							$balance = $rr->balance - $deduction_amount;
							$deduct = $rr->deduct + $deduction_amount;
							$deduction_amount = 0;
							
						}
						$qu = "update payroll.payrollcharge set 
											deduct='$deduct',
											balance='$balance' 
									where 
											payrollcharge_id = '$rr->payrollcharge_id'";
						$qur = @pg_query($qu);
						if (!$qur )
						{
							galert("Error Updating Payroll Charges...".pg_errormessage());
							$return= -3;
							break;
						}
						if ($deduction_amount <= 0)
						{
							break;
						}
					}
				}//endif
			}
		}
	}	
	return $return;
}


?>