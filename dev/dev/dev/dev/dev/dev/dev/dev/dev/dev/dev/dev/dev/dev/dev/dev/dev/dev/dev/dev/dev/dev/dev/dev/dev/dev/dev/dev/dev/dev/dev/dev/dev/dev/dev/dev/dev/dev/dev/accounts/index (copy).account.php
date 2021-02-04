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
	
	$xajax->registerFunction('posting');
	function posting($form) 
	{
		global $ADMIN, $SYSCONF;
		
		$mcutoff_date = mdy2ymd($form['cutoff_date']);
		$mgrace_date = mdy2ymd($form['grace_date']);
		$account_class_id = $form['account_class_id'];
		
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
		
		if ($account_class_id == '') $account_class_id = 0;
		$ok=1;
		
		$trial_account_id = ''; //fill-in specific account_id
		

		$q = "delete from accountledger where date='$mgrace_date' and type='I'";
		@pg_query($q);
		
		$q = "select * 
					from 
						accountpost 
					where 
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
			$qr = @pg_query($q);
			//galert('Updated New Running Balance. Press OK to continue.');
		}
		else
		{
			$q = "update accountledger set debit_balance=last_debit_balance, credit_balance=last_credit_balance";
			$qr = @pg_query($q);
			//galert('Updated Running Balance for Re-Posting. Press OK to continue.');
		}
		if (!$qr)
		{
			galert('Error Updating Previous Runnning Balances');
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
								accountledger.enable='Y' ";
		if ($account_class_id > '0')
		{
			$q .= " and account.account_class_id  = '$account_class_id'";
		}
		if ($trial_account_id != '')
		{
			$q .= " and account.account_id = '$trial_account_id'";
		}
		$q .= " group by 
						accountledger.account_id";
		
		$qr = @pg_query($q);
		if (!$qr)
		{
			$ok=0;
			$message = @pg_errormessage();
		}
	
	
		//-- iterate payment
		while ($r = @pg_fetch_object($qr))
		{
			$total_due = $grocery_due = $drygood_due = $debit_balance = $credit_balance = 0;
			$period_due = 0;
		
			$q = "select * 
						from 
							accountledger,
							account,
							account_class
						where 
							account.account_id = accountledger.account_id and
							account_class.account_class_id = account.account_class_id and 
							accountledger.account_id = '$r->account_id' and 
							accountledger.status!='C' and 
							accountledger.enable='Y' and 
							accountledger.debit_balance!='0' 
						order by 
							accountledger.date";
							
			$qqr = @pg_query($q) ;
			if (!$qqr)
			{
				$ok=0;
				$message =  "Error 1 : ".@pg_errormessage($qqr);
				break;
			}
			
			$credit_balance = $r->credit_balance;
			//-- iterate charges by card holder
			begin();

			$cc=0;
			while ($ri = @pg_fetch_object($qqr))
			{
			//********
				$period_due = 0;
				
				if ($ri->grocery_cutoff1 == '0')
				{
					$interval = $ri->grocery_term + $ri->grocery_grace;
					$d = "select date '$ri->date' + integer '$interval' as date_due";
					$qd = @pg_query($d);
					if (!$qd)
					{
						$message .= pg_errormessage($qd).$d;
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
			
				if (in_array($ri->type, array('S','I')))
				{
					$date_due = $mcutoff_date;
					$period_due = 2; 
				}
				elseif (substr($date_due,0,4) == substr($mcutoff_date,0,4))
				{
					if ($date_due <= $mcutoff_date)
					{
						$period_due = (substr($mcutoff_date,5,2) - substr($date_due,5,2))*1 + 1;
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
						$period_due = 12*(substr($mcutoff_date,0,4) - substr($date_due,0,4)) - (1*(substr($date_due,5,2)) - (substr($mcutoff_date,5,2))*1);
					}
					else
					{
						$period_due = 0;
					}
				}	

				if ($period_due == '0') $period_due = 1;
				$grocery_period = round($ri->grocery_term/$ri->grocery_interval,0);
				$drygood_period = round($ri->drygood_term/$ri->drygood_interval,0);
			
				if ($period_due <= $grocery_period)
				{
					$grocery_due = $period_due*($ri->grocery_debit/$grocery_period);
				}
				else
				{
					$grocery_due = $ri->grocery_debit;
				}
				if ($period_due <= $drygood_period)
				{
					$drygood_due = $period_due*($ri->drygood_debit/$drygood_period);
				}
				else
				{
					$drygood_due = $ri->drygood_debit;
				}			

				//********
				$total_due = $grocery_due + $drygood_due;
				if ($credit_balance >= $total_due )
				{
					$credit_balance -= $total_due;
					$debit_balance = $ri->debit_balance - $total_due;
				}
				else
				{
					$debit_balance = $ri->debit_balance - $credit_balance;
					$credit_balance = 0;
				}
				$mm .= " cbal  $credit_balance debit ".$ri->debit_balance ." bal $debit_balance \n";			
				$q = "update accountledger set debit_balance = '$debit_balance' where accountledger_id ='$ri->accountledger_id'";
				$qu = @pg_query($q);
				if (!$qu)
				{
					$ok=0;
					$message = "Error 2 : ".@pg_errormessage($qu);
					break;
				}
				if ($credit_balance <= 0)
				{
					break;
				}
			} //end of $ri
			
			if ($ok =='1')
			{
				$q = "update accountledger set credit_balance = '0'  where account_id = '$r->account_id'";
				$qp = @pg_query($q);
				if (!$qp)
				{
					$ok = 0;
				}
			}

			if ($ok  == '1')
			{
				commit();
				
				//processing over/advance payment
				$bailout = 0;
				while ($credit_balance > 0)
				{
					$bailout++;
					$q = "select * 
								from 
									accountledger 
								where 
									account_id = '$r->account_id' and 
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
						$message = "Error 3 : ".@pg_errormessage();
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
										account_id='$r->account_id'
									order by
										date desc 
									offset 0 limit 1";
						$qb = @pg_query($q) ;
						if (!$qb)
						{
							galert(@pg_errormessage().$q);
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
										account_id='$r->account_id'
									order by
										date desc 
									offset 0 limit 1";
							$qb = @pg_query($q) ;
							if (!$qb)
							{
								galert(@pg_errormessage().$q);
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
								galert(@pg_errormessage().$q);
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
								galert(@pg_errormessage().$q);
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
								galert(@pg_errormessage().$q);
							}
						}
					}
				} //end while advance payment
				
			}
			else
			{
				rollback();
			}
		}
		//galert($mm);
		//if ($message != '')
		//{
		//	galert($message);
		//}
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
						// and accountledger.account_id='1129'
		
//		$message .= $q;
//		$q = "select * from accountledger where type='T' and debit_balance > 0 and date <= '$mcutoff_date'  and account_id = '37' order by account_id";
		$qi = @pg_query($q);
		if (!$qr)
		{
			$message .= pg_errormessage().$q;
		}
		$maccount_id='';
		$interest = 0;

		while ($ri = @pg_fetch_object($qi))
		{
			if ($ri->account_id != $maccount_id)
			{

				/*if ($maccount_id =='190')// || $maccount_id == '132')
				{
					$message .= "<br>ACCOUNT $maccount_id PERIOD DUE ".$period_due."datedue $date_due  gp $grocery_period dp $drygood_period gd $grocery_due dd $drygood_due gdebir $ri->grocery_debit  dgdebit $ri->drygood_debit dgdue $drygood_due dgsc ".$aInfo['drygood_surcharge']." interest $interest";
				}
				*/
				if ($maccount_id != '' && $interest > '0')
				{
					$q = "select * 
								from 
									accountledger 
								where 
									account_id='$maccount_id and 
									type='I' and 
									date='$mgrace_date'";
					$qip = @pg_query($q);
					if (@pg_num_rows($qip) == 0)
					{
						$q = "insert into accountledger (account_id, date, type, debit, debit_balance, admin_id, status)
										values ('$maccount_id', '$mgrace_date','I','$interest', '$interest', '".$ADMIN['admin_id']."', 'S')";
						$qip = @pg_query($q);
						if (!$qip)
						{
							$message .= "Error inserting interest ".$q;
						}
					}
					else
					{
						$rip = @pg_fetch_object($qip);
						$aid = $rip->accountledger_id;
						$q = " update accountledger  set debit='$interest', debit_balance='$interest' where accountledger_id='$aid'";
						$qip = @pg_query($q);
						if (!$qip)
						{
							$message .= "Error updating interest ".$q;
						}
					}
				} //end of inserting interest
				
				$maccount_id = $ri->account_id;
				$interest =0;
			}
			
			$period_due = 0;
			//$counts = ($grocery_period >= $drygood_period  ? $grocery_period : $drygood_period);

			if ($ri->grocery_cutoff1 == '0')
			{
				$interval = $ri->grocery_term + $ri->grocery_grace;
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
			
			if (in_array($ri->type, array('S','I')))
			{
				$date_due = $mcutoff_date;
				$period_due = 3; 
			}
			elseif (substr($date_due,0,4) == substr($mcutoff_date,0,4))
			{
					if ($date_due <= $mcutoff_date)
					{
						$period_due = (substr($mcutoff_date,5,2) - substr($date_due,5,2))*1 + 1;
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
						$period_due = 12*(substr($mcutoff_date,0,4) - substr($date_due,0,4)) - (1*(substr($date_due,5,2)) - (substr($mcutoff_date,5,2))*1);
					}
					else
					{
						$period_due = 0;
					}
			}	

			if ($period_due == '0') continue;
			
			$grocery_period = round($ri->grocery_term/$ri->grocery_interval,0);
			$drygood_period = round($ri->drygood_term/$ri->drygood_interval,0);
			
			$debit_balance = $ri->debit_balance;
			$payment_made = $ri->debit - $ri->debit_balance;
			//$message .= "PM $payment_made debit $ri->debit debit balance $ri->debit_balance";
			if (in_array($ri->type, array('S','I')))
			{
				$grocery_due = $debit_balance;
				$debit_balance = 0;
			}
			elseif ($period_due <= $grocery_period)
			{
				$grocery_due = $period_due*($ri->grocery_debit/$grocery_period);
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
			if ($period_due <= $drygood_period)
			{
				$drygood_due = $period_due*($ri->drygood_debit/$drygood_period);
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
			//if ($debit_balance > 0) $drygood_due += $debit_balance; //interests					
			if ($period_due == 1)
			{
				$interest += $grocery_due* $ri->grocery_surcharge /100 + $drygood_due* $ri->drygood_surcharge /100;
			}
			else
			{
				$interest +=($grocery_due* $ri->grocery_interest /100) + ($drygood_due* $ri->drygood_interest /100);
				$drygood_interest = $ri->drygood_interest;
			}

			if ($trial_account_id !='')
			{
					$message .= "<br>ACCOUNT $maccount_id PERIOD DUE ".$period_due."datedue $date_due  gp $grocery_period dp $drygood_period gd $grocery_due dd $drygood_due di $drygood_interest interest $interest db $ri->debit_balance";
			}

		} // end of interest interation

				

		if ($interest > '0')
		{
			$q = "select * 
					from 
						accountledger 
					where 
						account_id='$maccount_id' and 
						type='I' and 
						date='$mgrace_date'";
			$qip = @pg_query($q);
			if (@pg_num_rows($qip) == 0)
			{
				$q = "insert into accountledger (account_id, date, type, debit, debit_balance, admin_id, status)
								values ('$maccount_id', '$mgrace_date','I','$interest', '$interest', '".$ADMIN['admin_id']."', 'S')";
				$qip = @pg_query($q);
				if (!$qip)
				{
					$message .= "Error inserting interest ".$q;
				}
			}
			else
			{
				$rip = @pg_fetch_object($qip);
				$aid = $rip->accountledger_id;
				$q = " update accountledger  set date='$mgrace_date', debit='$interest', debit_balance='$interest' where accountledger_id='$aid'";
				//$message = $q;
				$qip = @pg_query($q);
				if (!$qip)
				{
					$message .= "Error inserting interest ".$q;
				}
			}
		}
		
		
		if ($ok == '1')
		{
				$now = date('Y-m-d');
				
				$q = "select * from accountpost where cutoff_date = '$mcutoff_date' and enable='Y'";
				$qr = @pg_query($q);
				if (!$qr)
				{
					$message .= @pg_errormessage().$q;
				}
				if (@pg_num_rows($qr) == 0)
				{
					$audit = 'Posted by: ['.$ADMIN['admin_id'].']'.$ADMIN['name'].' on '.date('m/d/Y g:ia').';';
					$q = "insert into accountpost (date, cutoff_date, grace_date, 
											account_class_id, admin_id, audit, enable)
								values ('$now', '$mcutoff_date', '$mgrace_date', 
											'$account_class_id', '".$ADMIN['admin_id']."', '$audit', 'Y')";
				 	$qr= @pg_query($q);
					if (!$qr)
					{
						$message .= @pg_errormessage().$q;
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
					$audit .= 'Updated by: ['.$ADMIN['admin_id'].']'.$ADMIN['name'].' on '.date('m/d/Y g:ia').';';
					$q = "update accountpost set audit = '$audit' where accountpost_id= '$r->accountpost_id'";
				 	$qr = @pg_query($q);
					if (!$qr)
					{
						$message .= @pg_errormessage().$q;
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
    <td width="24%" align="center">User: <?= $ADMIN['name'];?> [ <a accesskey="L" href="../?p=logout">Logout</a> ]<br>
	<?= date('F d,Y');?><br><?=($SYSCONF['DATABASE'] !=''? 'DB: '.$SYSCONF['DATABASE'] : '');?>
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
