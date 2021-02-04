<?php
	session_start();
     require_once("../xajax.inc.php");
     $xajax = new xajax();
     $g     = "";

     $g->objResponse = new xajaxResponse();
     
	include_once('../lib/library.php');
	include_once('../lib/dbconfig.php');
	include_once('../lib/connect.php');
	include_once('../var/system.conf.php');

	include_once('xajax__hope.lib.php');

	function galert($m)
	{
			global $g;
			$g->objResponse->addAlert($m);
	}
	$xajax->registerFunction('bondposting');
	function bondposting($form) 
	{
		global $ADMIN, $SYSCONF;
		//set_timeout(0);		
		$mcutoff_date = mdy2ymd($form['cutoff_date']);
		$rate = $form['rate'];
		$admin_id = $ADMIN['admin_id'];
		$ok=1;
		$newposting = 0;
		
		$trial_account_id = ''; //fill-in specific account_id
		include_once('accountbalance.php');
	
		if ($ADMIN['admin_id'] == '')
		{
			galert('Your LOGIN Account has Expired. Please Log-In again before processing.');
			return done();
		}
		if ($rate*1 == '0')
		{
			galert("No Interest Rate Specified...");
			return done();
		}	
		if ($mcutoff_date=='--')
		{
			galert('No Dates Provided. Please Check.');
			return done();
		}
		
		$q = "select * from account where account_type_id='3' and enable='Y'"; //guarantors
		$qr = @pg_query($q);
		if (!$qr)
		{
			galert(pg_errormessage().$q);
			return done();
		}
		$ctr=0;
		while ($r = @pg_fetch_object($qr))
		{
				$aBond = bondBalance($r->account_id);
				if ($aBond['balance'] < 0.05) continue;
				$ctr++;
				
				$q = "select * from bondledger 
							where 
								type='I' and 
								account_id='$r->account_id' and
								date != '$mcutoff_date' and  
								enable='Y' 
							order by 
								date desc 
							offset 0 limit 1";
				$qqr = @pg_query($q);
				
				if (!$qqr)
				{
					galert(pg_errormessage().$q);
				}
				else
				{
					if (@pg_num_rows($qqr) == 0)
					{
						$q = "select * from bondledger 
									where 
										enable='Y' and 
										account_id='$r->account_id' and
										date!='$mcutoff_date'  
									order by 
										date desc 
									offset 0 limit 1";
						$qqr = @pg_query($q);
					}
					$rr = @pg_fetch_object($qqr);
					
					$d = "select '$mcutoff_date' - date '$rr->date' as interval";
					$qd = @pg_query($d);
					if (!$qd)
					{
						galert(pg_errormessage().$d);
					}
					$rd = @pg_fetch_object($qd);
					
					$interest = round(( $rate / 36500 ) * $rd->interval * $aBond['balance'],2);
					
					$q = "select * from bondledger
								where
									enable='Y' and
									account_id='$r->account_id' and
									type='I' and
									date = '$mcutoff_date'";
					$qqr = @pg_query($q);
					if (!$qqr)
					{
						galert(pg_errormessage().$q);
						return done();
					}
					
					if (@pg_num_rows($qqr)>0)
					{
						$ri = @pg_fetch_object($qqr);
						$q = "update bondledger set 
									date='$mcutoff_date', 
									debit='$interest', 
									enable='Y'
								where
									bondledger_id = '$ri->bondledger_id'";
						$qi = @pg_query($q);
						if (!$qi)
						{
							galert(pg_errormessage().$q);
							return done();
						}
						
					}
					elseif ($interest > 0.05)
					{

						
						$q = "insert into bondledger (account_id, date, type, debit, admin_id, enable)
								values
										('$r->account_id', '$mcutoff_date', 'I', '$interest',
										'$admin_id', 'Y')";
						$qi = @pg_query($q);
						if (!$qi)
						{
							galert(pg_errormessage().$q);
							return done();
						}
					}
					
				}
		}
		
		$q = "select * from accountpost where enable='Y' and type='B' and cutoff_date='$mcutoff_date'";
		$qr = @pg_query($q);
		if (!$qr)
		{
			galert(pg_errormessage().$q);
			return done();
		}
		if (@pg_num_rows($qr) > 0)
		{
			$r = @pg_fetch_object($qr);
			$audit = $r->audit.'; Updated by: '.$ADMIN['username'].' on '.date('m/d/Y g:ia');
			$q = "update accountpost set audit = '$audit' where accountpost_id = '$r->accountpost_id'";
			$qr = @pg_query($q);
			if (!$qr)
			{
				galert('Error Update Account Post '.pg_errormessage().$q);
				return done();
			}
			
		}
		else
		{
			$audit = 'Posted by: ['.$admin_id.']'.$ADMIN['name'].' on '.date('m/d/Y g:ia').';';
			//$mdate = date('Y-m-d');
			if ($trial_account_id == '')
			{
				$q = "insert into accountpost (date, cutoff_date, admin_id, audit, type, enable)
						values ('$mdate', '$mcutoff_date', '$admin_id', '$audit', 'B' ,'Y')";
				$qr = @pg_query($q);
				if (!$qr)
				{
					galert('Error Insert Account Post '.pg_errormessage().$q);
					return done();
				}
			}				
		}
		
		galert('Done Processing...('.$ctr .') guarantor/s...');
		return done();
	}
	
	$xajax->registerFunction('posting');
	function posting($form) 
	{
		global $ADMIN, $SYSCONF;
		//set_timeout(0);		
		$mcutoff_date = mdy2ymd($form['cutoff_date']);
		$mgrace_date = mdy2ymd($form['grace_date']);
		$mdate = mdy2ymd($form['date']);
		
		$account_class_id = $form['account_class_id'];
		$admin_id = $ADMIN['admin_id'];
		if ($account_class_id == '') $account_class_id = 0;
		$ok=1;
		$newposting = 0;
		
		$trial_account_id = ''; //'5843'; //fill-in specific account_id
		
	
		if ($ADMIN['admin_id'] == '')
		{
			galert('Your LOGIN Account has Expired. Please Log-In again before processing.');
			return done();
		}	
		if ($mcutoff_date > $mgrace_date)
		{
			galert('Cutoff Date is Greater than Grace Period. Please Check Dates Provided');
			return done();
		}
		if ($mcutoff_date=='--' or  $mgrace_date=='--')
		{
			galert('No Dates Provided. Please Check.');
			return done();
		}
		
/*
		$q = "delete from accountledger where date='$mgrace_date' and type='I'";
		if ($trial_account_id != '')
		{
			$q .= " and account_id ='$trial_account_id'";
		}
		@pg_query($q);
*/		
		$q = "select * 
					from 
						accountpost 
					where
						type='A' and  
						cutoff_date='$mcutoff_date' and
						grace_date='$mgrace_date' and
						account_class_id='$account_class_id'";
		$qr = @pg_query($q);
		
		if (!$qr)
		{
			galert('Error Querying Account Post Table'.$q);
			return done();
		}
		if (@pg_num_rows($qr) == 0)
		{
			$q = "update accountledger set 
							last_debit_balance = debit_balance,
							last_credit_balance = credit_balance";
			if ($trial_account_id != '')
			{
				$q .= " where account_id ='$trial_account_id'";
			}
			
			$qr = @pg_query($q);
			//galert('Updated New Running Balance. Press OK to continue.');
			$newposting = 1;
		}
		else
		{
			$q = "update accountledger set debit_balance=last_debit_balance, credit_balance=last_credit_balance";
			if ($trial_account_id != '')
			{
				$q .= " where account_id ='$trial_account_id'";
			}
			$qr = @pg_query($q);
			//return done();
			//galert('Updated Running Balance for Re-Posting. Press OK to continue.');
			$newposting = 0;
		}
		if (!$qr)
		{
			galert('Error Updating Previous Runnning Balances'.pg_errormessage().$q);
			return done();
		}

		$q = "select 
								accountledger.account_id, 
								sum(accountledger.credit_balance) as credit_balance
							from 
								accountledger,
								account
							where 
								account.account_id = accountledger.account_id and 
								accountledger.status!='C'  and
								accountledger.enable='Y'";
		if ($mgrace_date != '')
		{
			$q .= " and accountledger.date<='$mgrace_date'";
		}
		if ($account_class_id > '0')
		{
			$q .= " and account.account_class_id  = '$account_class_id'";
		}
		if ($trial_account_id != '')
		{
			$q .= " and account.account_id = '$trial_account_id'";
		}
		$q .= " group by 
						accountledger.account_id"; //, accountledger.date, invoice";
		
		$qr = @pg_query($q);
		if (!$qr)
		{
			$ok=0;
			$message .= @pg_errormessage();
		}
	
		if ($ok)
		{
			//-- iterate payment
			include_once('account.postpayment.php');
			
			while ($r = @pg_fetch_object($qr))
			{
				if ($r->credit_balance <= '0') continue;
				
				$aPost = postPayment($r->account_id, $r->credit_balance, $mcutoff_date, $mgrace_date);
				
				if ($aPost['Ok'] == 0)
				{
					$message .= 'Error Posting Payment: '.$aPost['message'];
					$ok = 0;
					break;
				}
		/*		
				elseif ($r->date > '2006-12-26') //just to recalculate rewards points from said date
				{
					$q = "select * from reward where invoice = '$r->invoice'";
					$qrc = @pg_query($q);
					if (@pg_num_rows($qrc) == '0')
					{
						if ($SYSCONF['CHG_GRC_POINT'] > 0)
						{
							$reward_grocery = round($aPost['applied_grocery']/$SYSCONF['CHG_GRC_POINT'],2);
							$reward_drygood = round($aPost['applied_drygood']/$SYSCONF['CHG_DRY_POINT'],2);
						}
						else
						{
							$reward_grocery =  0;
							$reward_drygood = 0;
						}
						$reward_total = $reward_grocery + $reward_drygood;
						if ($reward_total > 0)
						{
							$q = "insert into reward (sales_header_id, invoice, type, date,account_id,
	         	            	  points_in, amount_in, points_out, amount_out, terminal)
									values ('0','$r->invoice', '1','$r->date','$r->account_id', 
					  				   '$reward_total','$r->credit_balance','0', '0', '".$SYSCONF['TERMINAL']."')";
							$qrr = @pg_query($q);
							if (!$qrr)
							{
								galert(pg_errormessage().$q);
								return done();	
							}
						}	

					}
				}
		*/
				$q = "update accountledger set 
								credit_balance = '0'  
							where 
								account_id = '$r->account_id'";
				if ($mgrace_date != '')
				{
						$q .= " and accountledger.date<='$mgrace_date'";
				}
				$qp = @pg_query($q);
				if (!$qp)
				{

					$message .= 'Error Posting Credit Balance '.@pg_errormessage().$q;
					$ok = 0;
					break;
				}
				
				if ($aPost['credit_balance'] > '0')
				{
					$aPost = calcAdvancePay($r->account_id, $aPost['credit_balance']);
					if ($aPost['Ok'] == 0)
					{
						$message .= 'Error Posting Advance Payment : '.$aPost['message'];
						$ok = 0;
						break;
					}
				}
	
				
			}

		}	
		if ($ok == '0')
		{
			galert($message);
			return done();
		}
		
		//return done();
		
		
		//proccessing interest

		$q = "select * 
					from 
						accountledger,
						account,
						account_class
					where 
						account.account_id = accountledger.account_id and
						account_class.account_class_id = account.account_class_id and 
						accountledger.debit_balance > 0 and
						accountledger.enable='Y' and
						accountledger.status!='C' and  
						accountledger.date <= '$mcutoff_date'";
						
		if ($account_class_id > '0')
		{
			$q .= " and account.account_class_id = '$account_class_id'";
		} 
		if ($trial_account_id != '')
		{
			$q .= " and account.account_id = '$trial_account_id'";
		}
		
		$q .= " order by 
						accountledger.account_id";



		$qi = @pg_query($q);
		if (!$qi)
		{
			$message .= pg_errormessage().$q;
			galert($message);
			return done();
		}
		$maccount_id='';
		$interest = 0;
	

		while ($ri = @pg_fetch_object($qi))
		{
			
			if ($ri->account_id != $maccount_id)
			{

				if ($maccount_id != '')
				{
					if ($newposting == 1)
					{
							//new cutoff posting 
							if ($interest > '0.05')
							{
								$q = "insert into accountledger (account_id, date, type, debit, debit_balance, last_debit_balance, admin_id, status)
											values ('$maccount_id', '$mdate','I','$interest', '$interest', '$interest', '$admin_id', 'S')";
								$qip = @pg_query($q);
								if (!$qip)
								{
									$message .= "Error inserting interest ".$q;
									galert($message);
									return done();
								}
							}
					}
					else
					{
						//reposting
						$q = "select * 
									from 
										accountledger 
									where 
										account_id='$maccount_id' and 
										type='I' and 
										date='$mdate'";
						$qip = @pg_query($q);
					
					
						if (@pg_num_rows($qip) == 0)
						{
							if ($interest > '0.05')
							{
								$q = "insert into accountledger (account_id, date, type, debit, debit_balance,last_debit_balance, admin_id, status )
											values ('$maccount_id', '$mdate','I','$interest', '$interest', '$interest', '$admin_id', 'S')";
								$qip = @pg_query($q);
								if (!$qip)
								{
									$message .= "Error inserting interest ".$q;
									galert($message);
									return done();
								}
							}
						}
						else
						{
							$rip = @pg_fetch_object($qip);
							$aid = $rip->accountledger_id;
							$q = " update accountledger  set 
											debit='$interest', 
											debit_balance='$interest' ,
											last_debit_balance='$interest',
											status='S',
											enable='Y' 
										where 
											accountledger_id='$aid'";
							$qip = @pg_query($q);
							if (!$qip)
							{
								$message .= "Error updating interest ".$q;
								galert($message);
								return done();
							}
						}
					}
				} //end of inserting interest
				
				$maccount_id = $ri->account_id;
				$interest =0;
			}
			
			$grocery_period_due = $drygood_period_due = $period_due = 0;
			$grocery_date_due = $drygood_date_due = '';
			//$counts = ($grocery_period >= $drygood_period  ? $grocery_period : $drygood_period);

			//computes for date due of grocery purchase
			if ($ri->grocery_cutoff1 == '0')
			{
				$interval = $ri->grocery_term + $ri->grocery_grace;
				$d = "select date '$ri->date' + integer '$interval' as date_due";
				$qd = @pg_query($d);
				if (!$qd)
				{
					$message .= pg_errormessage().$d;
					galert($message);
					return done();
				}
				else
				{
					$rd = @pg_fetch_object($qd);
					$date_due = $rd->date_due;
				}
				
			}
			else
			{
				//$date_due = $ri->date;
				$d = explode('-', $ri->date);
				if ($d[2] >= $ri->grocery_cutoff1)
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
					$date_due = $yr.'-'.$mo.'-'.$ri->grocery_cutoff1;
				}
				else
				{
					$mo = $d[1];
					$date_due = $d[0].'-'.$mo.'-'.$ri->grocery_cutoff1;
				}

			}
			$grocery_date_due = $date_due;
			

			//computes for date due of drygood purchase
			if ($ri->drygood_cutoff1 == '0')
			{
				$interval = $ri->drygood_term + $ri->drygood_grace;
				$d = "select date '$ri->date' + integer '$interval' as date_due";
				$qd = @pg_query($d);
				if (!$qd)
				{
					$message .= pg_errormessage().$d;
				}
				else
				{
					$rd = @pg_fetch_object($qd);
					$date_due = $rd->date_due;
				}
				
			}
			else
			{
				//$date_due = $ri->date;
				$d = explode('-', $ri->date);
				if ($d[2] >= $ri->drygood_cutoff1)
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
					$date_due = $yr.'-'.$mo.'-'.$ri->drygood_cutoff1;
				}
				else
				{
					$mo = $d[1];
					$date_due = $d[0].'-'.$mo.'-'.$ri->drygood_cutoff1;
				}

			}
			$drygood_date_due = $date_due;

			//computes actual due date
			if (in_array($ri->type, array('S','I')))
			{
				$grocery_date_due = $mcutoff_date;
				$grocery_period_due = 3; 
			}
			else
			{
				//grocery period due
				if (substr($grocery_date_due,0,4) == substr($mcutoff_date,0,4))
				{
					if ($grocery_date_due <= $mcutoff_date)
					{
						$grocery_period_due = (substr($mcutoff_date,5,2) - substr($grocery_date_due,5,2))*1 + 1;
					}
					else
					{
						$grocery_period_due = 0;
					}
				}
				else
				{
					if ($grocery_date_due < $mcutoff_date)
					{
						$grocery_period_due = 12*(substr($mcutoff_date,0,4) - substr($grocery_date_due,0,4)) - (1*(substr($grocery_date_due,5,2)) - (substr($mcutoff_date,5,2))*1) + 1;
					}
					else
					{
						$grocery_period_due = 0;
					}
				}

				//drygoods period due
				if (substr($drygood_date_due,0,4) == substr($mcutoff_date,0,4))
				{
					if ($drygood_date_due <= $mcutoff_date)
					{
						$drygood_period_due = (substr($mcutoff_date,5,2) - substr($drygood_date_due,5,2))*1 + 1;
					}
					else
					{
						$drygood_period_due = 0;
					}
				}
				else
				{
					if ($drygood_date_due < $mcutoff_date)
					{
						$drygood_period_due = 12*(substr($mcutoff_date,0,4) - substr($drygood_date_due,0,4)) - (1*(substr($drygood_date_due,5,2)) - (substr($mcutoff_date,5,2))*1) + 1;
					}
					else
					{
						$drygood_period_due = 0;
					}
				}

			}	

			if ($drygood_period_due == '0' && $grocery_period_due == '0') continue;
			
			$grocery_period = round($ri->grocery_term/$ri->grocery_interval,0);
			$drygood_period = round($ri->drygood_term/$ri->drygood_interval,0);
			
			$debit_balance = $ri->debit_balance;
			$payment_made = $ri->debit - $ri->debit_balance;
			//$message .= "PM $payment_made debit $ri->debit debit balance $ri->debit_balance";
			if (in_array($ri->type, array('S','I')))
			{
				$grocery_due = $debit_balance;
				$interest += $grocery_due * $ri->grocery_interest /100;
				$debit_balance = 0;
			}
			elseif ($grocery_period_due <= $grocery_period)
			{
				for ($ci=1;$ci<=$grocery_period_due;$ci++)
				{
					$grocery_due = $ri->grocery_debit/$grocery_period;

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
					if ($grocery_period_due ==  $ci)
					{
						$interest += $grocery_due * $ri->grocery_surcharge /100;
					}
					else
					{
						$interest += $grocery_due * $ri->grocery_interest /100;
					}
				}
			}
			else
			{
				if ($debit_balance <= $ri->grocery_debit)
				{
					$grocery_due = $debit_balance;
					$debit_balance = 0;
				}
				else
				{
					$grocery_due = $ri->grocery_debit;
					$debit_balance -= $grocery_due;
				}
				$interest += $grocery_due * $ri->grocery_interest /100;
			}
			
			
			if ($drygood_period_due <= $drygood_period)
			{
				$drygood_due = $ri->drygood_debit/$drygood_period;
				for ($ci=1;$ci<=$drygood_period_due;$ci++)
				{
					$drygood_due = $ri->drygood_debit/$drygood_period;

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
					if ($drygood_period_due ==  $ci)
					{
						$interest += $drygood_due * $ri->drygood_surcharge /100;
					}
					else
					{
						$interest += $drygood_due * $ri->drygood_interest /100;
					}
				}
			}
			else
			{
				if ($debit_balance <= $ri->drygood_debit)
				{
					$drygood_due = $debit_balance;
					$debit_balance = 0;
				}
				else
				{
					$drygood_due = $ri->drygood_debit;
					$debit_balance -= $drygood_due;
				}
				$interest += $drygood_due * $ri->drygood_interest /100;
			}	
					
			if ($trial_account_id !='')
			{
					$message .= "<br>ACCOUNT $maccount_id PERIOD DUE ".$period_due."datedue $date_due  gp $grocery_period dp $drygood_period gd $grocery_due dd $drygood_due di $drygood_interest interest $interest db $ri->debit_balance";
			}

		} // end of interest interation

				

//		if ($interest > '0')
//		{
			$q = "select * 
					from 
						accountledger 
					where 
						account_id='$maccount_id' and 
						type='I' and 
						date='$mdate'";
			$qip = @pg_query($q);
			if (!$qip)
			{
				galert($message);
				return done();
			}
			if (@pg_num_rows($qip) == 0)
			{
				if ($interest > 0.05)
				{
					$q = "insert into accountledger (account_id, date, type, debit, debit_balance,last_debit_balance, admin_id, status)
								values ('$maccount_id', '$mdate','I','$interest', '$interest','$interest', '$admin_id', 'S')";
					$qip = @pg_query($q);
					if (!$qip)
					{
						$message .= "Error inserting interest ".$q;
						galert($message);
						return done();
					}
				}
			}
			else
			{
				$rip = @pg_fetch_object($qip);
				$aid = $rip->accountledger_id;
				$q = " update accountledger  set 
								date='$mgrace_date', 
								debit='$interest', 
								debit_balance='$interest', 
								last_debit_balance='$interest',
								status='S',
								enable='Y'
							where 
								accountledger_id='$aid'";
				//$message = $q;
				$qip = @pg_query($q);
				if (!$qip)
				{
					$message .= "Error inserting interest ".$q;
					galert($message);
					return done();
				}
			}
//		}
		
		
		if ($ok == '1')
		{
				// $now = date('Y-m-d');
				
				$q = "select * from accountpost where type='A' and cutoff_date = '$mcutoff_date' and enable='Y'";
				$qr = @pg_query($q);
				if (!$qr)
				{
					$message .= @pg_errormessage().$q;
				}
				if (@pg_num_rows($qr) == 0)
				{
					$audit = 'Posted by: ['.$admin_id.']'.$ADMIN['name'].' on '.date('m/d/Y g:ia').';';
					$q = "insert into accountpost (date, cutoff_date, grace_date, 
											account_class_id, admin_id, type, audit, enable)
								values ('$mdate', '$mcutoff_date', '$mgrace_date', 
											'$account_class_id', '$admin_id', 'A', '$audit', 'Y')";
				 	$qr= @pg_query($q);
					if (!$qr)
					{
						$message .= @pg_errormessage().$q;
						galert($message);
					}
					else
					{
						$message .= "<br><br>Posting Recorded";
					}
				}
				else
				{
					$r = @pg_fetch_object($qr);
					$audit = $r->audit;
					$audit .= 'Updated by: ['.$admin_id.']'.$ADMIN['name'].' on '.date('m/d/Y g:ia').';';
					$q = "update accountpost set audit = '$audit' where accountpost_id= '$r->accountpost_id'";
				 	$qr = @pg_query($q);
					if (!$qr)
					{
						$message .= @pg_errormessage().$q;
						galert($message);
					}
					else
					{
						$message .= "<br><br>Posting Table Updated";
					}
					
				}
			  glayer('message.layer','Finished Posting A/R Transactions...'.$message);
		}
		else
		{
			  glayer('message.layer',$message);
		}
		return done();
	}
     //$xajax->debugOn();
$xajax->processRequests();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<head>
<title>HiTech IMS - Systems Administration Module</title>
<?php $xajax->printJavascript('../'); // output	the xajax javascript. This must	be called between the head tags	?>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<STYLE TYPE="text/css">
<!--
	A:link  {text-decoration: none;}
	A:hover {text-decoration:;font-weight: bold; }
	A:active {text-decoration: none; }
	A:visited {text-decoration: none; }
	A:visited:active {text-decoration: underline;}
	A:visited:hover {text-decoration: underline;}
	div.cats{position: absolute;right: 10;top: 80;}	

  .normal { background-color: #CCCCCC ;  color:#000000 }
  .highlight { background-color: #FFFFFF; color:#000000; font-weight:bold; }
-->  
</STYLE>
<script language="javascript">
function wait($message)
{
	xajax.$('message.layer').innerHTML = '';
	xajax.$('wait.layer').style.display = 'block';
	xajax.$('wait.layer').innerHTML = $message+"<br><img src='../graphics/wait.gif'>";
	return;
}
</script>
</head>

<body bgcolor="#EFEFEF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<table width="100%" border="0" bgcolor="#FFFFFF">
  <tr>
    <td width="76%"><img src="../graphics/logo.jpg" width="350" height="70"></td>
    <td width="24%" align="center"><font size="2">User: 
      <?= $ADMIN['name'];?>
      [ <a accesskey="L" href="../?p=logout">Logout</a> ]<br>
      <?= date('F d,Y');?>
      </font><br>
      <?=($SYSCONF['DATABASE'] !=''? 'DB: '.$SYSCONF['DATABASE'] : '');?>
	</td>
  </tr>
</table>
<table width="100%" border="0" cellpadding="1" cellspacing="1" bgcolor="#999999">
  <tr bgcolor="#CCCCCC"> 
    <td width="9%" height="19" align="center" onMouseOver="this.className='highlight'" onMouseOut="this.className='normal'"> 
      <a accesskey="Z" href="?p=account"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Accounts</font></a></td>
    <td width="9%" align="center"  onMouseOver="this.className='highlight'" onMouseOut="this.className='normal'"> 
     <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href="?p=guarantor">Guarantor</a></font></td>
    <td align="center"  onMouseOver="this.className='highlight'" onMouseOut="this.className='normal'"> 
      <font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=account.menu.posting">Posting</a></font></td>
    <td width="8%" align="center"  onMouseOver="this.className='highlight'" onMouseOut="this.className='normal'">
	<font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href="?p=account.menu.reward">Rewards</a></font></td>
    <td width="8%" align="center"  onMouseOver="this.className='highlight'" onMouseOut="this.className='normal'">
	<font size="2" face="Verdana, Arial, Helvetica, sans-serif">  <a href="?p=account.menu.report">Reports</a></font></td>
    <td width="8%" align="center"  onMouseOver="this.className='highlight'" onMouseOut="this.className='normal'">
	<font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=account.menu.setup">Setup</a></font></td>
    <td width="8%" align="center"  onMouseOver="this.className='highlight'" onMouseOut="this.className='normal'">
	<font size="2" face="Verdana, Arial, Helvetica, sans-serif">Password</font></td>
    <td width="7%" align="center"  onMouseOver="this.className='highlight'" onMouseOut="this.className='normal'">
	<font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a  accesskey="S" href="../?">Home</a></font></td>
    <td width="27%">&nbsp;</td>
  </tr>
</table>
<?php

include_once('../lib/library.php');
include_once('../lib/dbconfig.php');
include_once('../lib/connect.php');
include_once('../var/system.conf.php');
include_once('../lib/library.js.php');

if ($p != '')
{
	include_once("$p.php");
}


?>
  <div id="message.layer" align="center"></div>
  <div id="wait.layer" align="center"></div>
</body>
</html>
