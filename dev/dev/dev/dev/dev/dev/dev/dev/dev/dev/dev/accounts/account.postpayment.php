<?
function postPayment($aid = null, $credit_balance, $mcutoff_date = null, $mgrace_date = null )
{
		
		$aPost = null;
		$aPost = array();
		if ($credit_balance == '0')
		{
			$aPost['Ok'] = 0;
			$aPost['message'] = 'No Credit Balance';	
			return $aPost;
		}
		
		$aPost['Ok'] = 1;

		$applied_drygood = $applied_grocery = 0;
		
		$total_due = $grocery_due = $drygood_due = 0;
		$period_due = 0;
		
		$q = "select * 
						from 
							accountledger,
							account,
							account_class
						where 
							account.account_id = accountledger.account_id and
							account_class.account_class_id = account.account_class_id and 
							accountledger.account_id = '$aid' and 
							accountledger.status!='C' and 
							accountledger.enable='Y' and 
							accountledger.debit_balance!='0'";
		if ($mcutoff_date != '')
		{
			$q .= " and accountledger.date<='$mcutoff_date'";
		}
		$q .= " order by accountledger.date";
							
		//$message .= $q;							
		$qqr = @pg_query($q) ;
		
		if (!$qqr)
		{
				$ok=0;
				$aPost['Ok'] = 0;
				$message .= @pg_errormessage().$q;
				break;
		}
			
		//-- iterate charges by card holder			
		//begin();

		$cc=0;
		while ($ri = @pg_fetch_object($qqr))
		{
			
			$period_due = 0;
			if ($ri->grocery_cutoff1 == '0')
			{
				$interval = $ri->grocery_term + $ri->grocery_grace;
				$d = "select date '$ri->date' + integer '$interval' as date_due";
				$qd = @pg_query($d);
				if (!$qd)
				{
					$aPost['Ok'] = 0;
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
					$aPost['Ok'] = 0;
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

			if ($drygood_period_due == '0' && $grocery_period_due == '0') 
			{
				$grocery_period_due = 1;
				$drygood_period_due = 1;
				//continue;
			}
			
			//$message .= ' gpd '.$grocery_period_due.' dpd '.$drygood_period_due.' cb '.$credit_balance;
			$grocery_period = round($ri->grocery_term/$ri->grocery_interval,0);
			$drygood_period = round($ri->drygood_term/$ri->drygood_interval,0);
			
			$debit_balance = $ri->debit_balance;
			$payment_made = $ri->debit - $ri->debit_balance;

			if (in_array($ri->type, array('S','I')))
			{
				$grocery_due = $debit_balance;
				$debit_balance = 0;
			}
			elseif ($grocery_period_due <= $grocery_period)
			{
				$grocery_due = $grocery_period_due*($ri->grocery_debit/$grocery_period);
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
			}
			if ($drygood_period_due <= $drygood_period)
			{
				$drygood_due = $drygood_period_due*($ri->drygood_debit/$drygood_period);
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
			}	
			//********
			$total_due = $grocery_due + $drygood_due;
			
			//$message .= " gd $grocery_due  dd $drygood_due  td $total_due ";
			if ($credit_balance >= $total_due )
			{
					$credit_balance -= $total_due;
					$debit_balance = $ri->debit_balance - $total_due;
					
					$applied_grocery += $grocery_due;
					$applied_drygood += $drygood_due;
			}
			else
			{
					$gamt = 0;
					if ($credit_balance >=  $grocery_due)
					{
						$gamt = $grocery_due;
					}
					else
					{
						$gamt = $credit_balance;
					}
					$applied_grocery += $gamt;
					$applied_drygood += ($credit_balance - $gamt);
				
					$debit_balance = $ri->debit_balance - $credit_balance;
					$credit_balance = 0;
			}
							
			$q = "update accountledger set debit_balance = '$debit_balance' where accountledger_id ='$ri->accountledger_id'";
			$qu = @pg_query($q);
			if (!$qu)
			{
					$aPost['Ok'] = 0;
					$ok = 0;
					$message = @pg_errormessage().$q;
					break;
			}
			if ($credit_balance <= 0)
			{
					break;
			}
	
		}	

		$aPost['message'] = $message;
		$aPost['credit_balance'] = $credit_balance;
		$aPost['applied_drygood'] = $applied_drygood;
		$aPost['applied_grocery'] = $applied_grocery;
		
		return $aPost;
}

function calcAdvancePay($aid, $credit_balance)
{
		$aPost = null;
		$aPost = array();
		$aPost['Ok'] =  1;
				
		//processing over/advance payment
		$bailout = 0;
		while ($credit_balance > 0)
		{
					$bailout++;
					$q = "select * 
								from 
									accountledger 
								where 
									account_id = '$aid' and 
									type='P'  and 
									credit_balance = '0' and 
									status!='C' and
									enable='Y'
								order by 
									date desc 
								offset 0 limit 1";
					$qp = @pg_query($q);
					if (!$qp)
					{
						$aPost['Ok'] = 0;
						$ok = 0;
						$message = @pg_errormessage().$q;
						break;
					}
					elseif (@pg_num_rows($qp) == '0' or $bailout>100)
					{
						$q = "select * 
									from 
										accountledger 
									where
										status!='C' and  
										enable='Y' and 
										type='P' and 
										account_id='$aid'
									order by
										date desc 
									offset 0 limit 1";
						$qb = @pg_query($q) ;
						if (!$qb)
						{
								$aPost['Ok'] = 0;
								$message = @pg_errormessage().$q;
								break;
						}
						elseif (@pg_num_rows($qb)==0)
						{
							$q = "select * 
									from 
										accountledger 
									where
										status!='C' and  
										credit!='0' and 
										enable='Y' and 
										account_id='$aid'
									order by
										date desc 
									offset 0 limit 1";
							$qb = @pg_query($q) ;
							if (!$qb)
							{
								$aPost['Ok'] = 0;
								$message = @pg_errormessage().$q;
								break;
							}
						}
						if (@pg_num_rows($qb)>0)
						{
							$rb = @pg_fetch_object($qb);
							$id = $rb->accountledger_id;
							$credit_balance += $rb->credit_balance;
							$q = "update accountledger set
										credit_balance = '$credit_balance'
									where
										accountledger_id = '$id'"; 

							$qb = @pg_query($q) ;
							if (!$qb)
							{
								$aPost['Ok'] = 0;
								$message = @pg_errormessage().$q;
								break;
							}
						}
						$message .= "\n\nBailout on OverPayment Account ".$r->account_id;
						break;
					}
					else
					{
						$rp = @pg_fetch_object($qp);
						if ($rp->credit >=  $credit_balance)
						{
							$q = "update accountledger set 
										credit_balance = '$credit_balance' 
									where 
										accountledger_id = '$rp->accountledger_id'";
							
							$qp = @pg_query($q);
							if (!$qp)
							{
								$aPost['Ok'] = 0;
								$message = @pg_errormessage().$q;
							}
							$credit_balance = 0;
							break;						
						}
						else
						{
							$credit = $rp->credit;
							$credit_balance -= $credit;
							$q = "update accountledger set 
										credit_balance = '$credit' 
									where 
										accountledger_id = '$rp->accountledger_id'";
							$qp = @pg_query($q);
							if (!$qp)
							{
								$aPost['Ok'] = 0;
								$message = @pg_errormessage().$q;
								break;
							}
						}
					}
		} //end while advance payment
	
		$aPost['message'] = $message;			
		return $aPost;
}
?>