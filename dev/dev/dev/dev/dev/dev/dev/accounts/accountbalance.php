<?
function bondBalance($aid)
{
		$aBond = null;
		$aBond = array();
		$Q = "select 
           			sum(debit) as debit, 
           			sum(credit) as credit 
        			from 
           			bondledger
        			where 
        				enable='Y' and 
           			account_id='$aid'";
         
      $QR = @pg_query($Q);  			
     	$R = @pg_fetch_object($QR);
     	$aBond['debit'] = $R->debit;
     	$aBond['credit'] = $R->credit;
     	$aBond['balance'] = $R->debit - $R->credit;
		return $aBond;
}
function rewardBalance($aid)
{
		$aReward = null;
		$aReward = array();
		$Q = "select 
           			sum(points_in) as points_in, 
           			sum(points_out) as points_out 
        			from 
           			reward 
        			where 
        				status!='C' and 
           			account_id='$aid'";
         
      $QR = @pg_query($Q) or message(pg_errormessage());  			
     	$R = @pg_fetch_object($QR);
     	$aReward['points_in'] = $R->points_in;
     	$aReward['points_out'] = $R->points_out;
     	$aReward['points_balance'] = $R->points_in - $R->points_out;
		return $aReward;
}

function supplierBalance($aid)
{
  $aBal = null;
  $aBal = array();

  $Q = "select * from account where account_id='$aid'";
  $QR = @pg_query($Q)  or db_error();
  $R = @pg_fetch_assoc($QR);
  
	$debit = $credit = $account_balance = 0;
	$q = "select sum(net_amount) as debit from rr_header, tender  
            where 
              tender.tender_id=rr_header.tender_id and
              tender.tender_type!='C' and
              status!='C' and account_id='".$R['account_id']."'";
	$rr = fetch_object($q);
	$debit = $rr->debit;

	$q = "select sum(net_amount) as debit from poreturn_header where status!='C' and account_id='".$R['account_id']."'";
	$rr = fetch_object($q);
	$debit -= $rr->debit;

	$q = "select sum(net_credit) as credit from payment_header where account_id='".$R['account_id']."' and status!='C'";
	$rr = fetch_object($q);
	$credit = $rr->credit;

	$account_balance = $debit - $credit;
  $aBal['debit'] = $debit;
  $aBal['credit'] = $credit;
  $aBal['balance'] = $account_balance;
  
  return $aBal;
}

function customerBalance($aid)
{
  $aBal = null;
  $aBal = array();

  $Q = "select * from account where account_id='$aid'";
  $QR = @pg_query($Q)  or db_error();
  $R = @pg_fetch_assoc($QR);
  
	$debit = $credit = $account_balance = 0;
	
  $q = "select sum(debit) as debit, sum(credit) as credit
        from 
          accountledger
        where
          account_id='$aid' and 
          enable='Y' and
          status!='C'";
	
  $aBal = @fetch_assoc($q);
  $aBal['balance'] = $aBal['debit'] - $aBal['credit'];

  $q = "select type
        from 
          accountledger
        where
          account_id='$aid' and 
          enable='Y' and
          status!='C'
        order by
        date desc offset 0 limit 1";
  $R = @fetch_assoc($q);
  
  if ($R['type'] == 'I')
  {
  	$aBal['overdue'] = 1;
  }
  return $aBal;
}

function customerDue($aid, $mcutoff_date)
{

  $aBal = null;
  $aBal = array();

		$q = "select 
			accountledger.invoice,
			accountledger.debit_balance,
			accountledger.credit_balance,
			accountledger.grocery_debit,
			accountledger.drygood_debit,
			accountledger.debit,
			accountledger.credit,
			accountledger.date,
			accountledger.type,
			account_class.grocery_interval,
			account_class.grocery_term,
			account_class.drygood_interval,
			account_class.drygood_term,
			account_class.grocery_cutoff1,
			account_class.grocery_cutoff2,
			account_class.grocery_cutoff3,
			account_class.grocery_cutoff4,
			account_class.drygood_cutoff1,
			account_class.drygood_cutoff2,
			account_class.drygood_cutoff3,
			account_class.drygood_cutoff4,
			account_class.grocery_grace,
			account_class.drygood_grace
		from 
			accountledger,
			account,
			account_class 
		where 
			account.account_id=accountledger.account_id and
			account_class.account_class_id = account.account_class_id and 
			accountledger.enable='Y' and 
			accountledger.account_id='$aid'
		order by
			date";

		$qqr = @pg_query($q) or message(pg_errormessage());
		
		while ($rr = @pg_fetch_object($qqr))
	   {
			$grocery_due = $drygood_due = $interest = 0;
	   	if (in_array($rr->type, array('I','S')))
			{
//				$interest += $rr->debit - $rr->credit;
				$interest += $rr->debit_balance - $rr->credit_balance;
			}
			else
			{
			
				if ($rr->grocery_cutoff1 == '0')
				{
					$interval = $rr->grocery_term; // + $rr->grocery_grace;
					$d = "select date '$rr->date' + integer '$interval' as date_due";
					$qd = @pg_query($d);
					if (!$qd)
					{
						message(pg_errormessage($qd).$d);
					}
					else
					{
						$rd = @pg_fetch_object($qd);
						$date_due = $rd->date_due;
					}
				}
				else
				{
					$d = explode('-', $rr->date);
					if ($d[2] >= $rr->grocery_cutoff1)
					{
						if ($d[1] < 12)
						{
							$mo = $d[1]+1;
							$yr = $d[0];
						}
						else
						{
							$mo = '01';
							$yr = $d[0]+1;
						}
						if (strlen($mo)== '1') $mo = '0'.$mo;
						$date_due = $yr.'-'.$mo.'-'.$rr->grocery_cutoff1;
					}
					else
					{
						$mo = $d[1];
						$date_due = $d[0].'-'.$mo.'-'.$rr->grocery_cutoff1;
					}
				}
			
				if (in_array($rr->type, array('S','I')))
				{
					$date_due = $mcutoff_date;
					$period_due = 2; 
				}
				elseif (substr($date_due,0,4) == substr($mcutoff_date,0,4))
				{
				
						if ($date_due <= $mcutoff_date)
						{
							$period_due = (substr($mcutoff_date,5,2) - substr($date_due,5,2))*1+1 ;
						}
						else
						{
							$period_due = 0;
						}
				}
				else
				{
						if ($date_due < $mcutoff_date)
						{
							$period_due = 12*(substr($mcutoff_date,0,4) - substr($date_due,0,4)) - (1*(substr($date_due,5,2)) - (substr($mcutoff_date,5,2))*1) +1;
						}
						else
						{
							$period_due = 0;
						}
				}
				if ($period_due == '0') 
				{
					$aBal['balance'] += ($rr->debit - $rr->credit);
//					$aBal['balance'] += ($rr->debit_balance - $rr->credit_balance);
					continue;
				}
				$grocery_period = round($rr->grocery_term/$rr->grocery_interval,0);
				$drygood_period = round($rr->drygood_term/$rr->drygood_interval,0);
				$debit_balance = $rr->debit_balance;
				$payment_made = $rr->debit - $debit_balance;

				if ($period_due < $grocery_period)
				{
					$grocery_due = $period_due*($rr->grocery_debit/$grocery_period);
					if ($grocery_due >= $payment_made)
					{
						$grocery_due -=  $payment_made;
						$payment_made = 0;
					}
					else
					{
						$payment_made -= $grocery_due;
						$grocery_due = 0;
					}
					
				}
				else
				{
					if ($debit_balance <= $rr->grocery_debit)
					{
						$grocery_due = $debit_balance;
						$debit_balance = 0;
					}
					else
					{
						$grocery_due = $rr->grocery_debit;
						$debit_balance -= $grocery_due;
					}
				}
				
				if ($period_due <= $drygood_period)
				{
					$drygood_due = $period_due*($rr->drygood_debit/$drygood_period);
					if ($drygood_due >= $payment_made)
					{
						$drygood_due -=  $payment_made;
						$payment_made = 0;
					}
					else
					{
						$payment_made -= $drygood_due;
						$drygood_due = 0;
					}
				
				}
				else
				{
					if ($debit_balance <= $rr->drygood_debit)
					{
						$drygood_due = $debit_balance;
						$debit_balance = 0;
					}
					else
					{
						$drygood_due = $rr->drygood_debit;
						$debit_balance -= $drygood_due;
					}
				}			
			}
			$aBal['interest_due'] += $interest;
			$aBal['principal_due'] += ($drygood_due + $grocery_due);
			//$aBal['balance'] += ($rr->debit_balance - $rr->credit_balance);
			$aBal['balance'] += ($rr->debit - $rr->credit);
		if ($aid == '5906x')
		{
			echo "<br> invoice $rr->invoice dd $date_due pd $period_due gd $grocery_due dd $drygood_due<br>";
			print_r($aBal); 
		}
			
		}
		
		$aBal['total_due'] = $aBal['principal_due'] + $aBal['interest_due'];
		
		return $aBal;
}
?>
