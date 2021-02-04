<?
$q = "update accountledger set credit_balance = credit, debit_balance=debit ";
$qr = @pg_query($q) or message(pg_errormessage().$q);

$q = "select account_id, sum(credit) as credit from accountledger 
               where credit>'0' and enable='Y'  
               		and date<'2007-04-05'
               group by account_id";
               
$qr = @pg_query($q) or message(pg_errormessage().$q);


while ($r = @pg_fetch_object($qr))
{
	$qa = "select * from account, account_class 
				where 
					account_class.account_class_id = account.account_class_id and 
					account.account_id = '$r->account_id'";
					
	$qra = @pg_query($qa) or message1(pg_errormessage());
	$ra = @pg_fetch_object($qra);
	if ($ra->grocery_interval*1 == '0')
	{
		$grocery_period = 1;
	}
	else
	{
		$grocery_period = round($ra->grocery_term/$ra->grocery_interval,0);
	}
	if ($ra->drygod_interval*1 == '0')
	{
		$drygood_period = 1;
	}
	else
	{
		$drygood_period = round($ra->drygood_term/$ra->drygood_interval,0);
	}
	
	$qq = "select accountledger_id, type, grocery_debit, drygood_debit, debit, date from accountledger 
	         where account_id = '$r->account_id' and enable='Y' and debit>'0' order by date ";
	$qqr = @pg_query($qq) or message(pg_errormessage().$qq);
	$credit = $r->credit;
	
	//echo "credit balance ".$credit;	
	$total_debit = 0;
	while ($rr = @pg_fetch_object($qqr))
	{
		$debit = 0;
		if ($rr->date < '2007-02-27' || in_array($rr->type, array('S','I')))
		{
			$debit = $rr->debit;
		}
		else  // ($rr->date >= '2007-02-27')
		{
			if ($grocery_period  <= 1)
			{
				$debit += $rr->grocery_debit; 
			}
			else
			{
				$debit += $rr->debit/$grocery_period;
			}

			if ($drygood_period  <= 1)
			{
				$debit += $rr->debit - $rr->grocery_debit; //$rr->drygood_debit; 
			}
			else
			{
				$debit += ($rr->debit - $rr->grocery_debit)/$drygood_period;
			}
		}
		$total_debit += $debit;	
		if ($credit >= $debit)
		{
			$debit_balance = $rr->debit - $debit;
			$qqq = "update accountledger set debit_balance='$debit_balance' where accountledger_id = '$rr->accountledger_id'";
			@pg_query($qqq) or message(pg_errormessage().$qqq);
			$credit -= $debit;
		}
		else
		{
			$debit_balance = $rr->debit - $credit;
			$qqq = "update accountledger set debit_balance='$debit_balance' where accountledger_id = '$rr->accountledger_id'";
			@pg_query($qqq) or message(pg_errormessage().$qqq);
			$credit = 0;
		}
		//echo $qqq.' credit balanace '.$credit.'<br>';
		if ($credit <= '0') break;
	}
	echo " <br>total debit ".$total_debit." credit balance $credit ";
	$qu = "update accountledger set credit_balance = '0' where account_id = '$r->account_id' and credit>'0'";
	$qru = @pg_query($qu) or message(pg_errormessage().$qu);
	//echo " credit balance $credit update credit affected : ".@pg_affected_rows($qru);
	
	if ($credit > 0)
	{
			$qc = "select * from accountledger where credit>'0' and enable='Y' order by date desc ";
			$qrc = @pg_query($qc) or message(pg_errormessage());
			while ($rc = @pg_fetch_object($qrc))
			{
				if ($rc->credit >= $credit)
				{
					$qu = "update accountledger set credit_balance='$credit' where accountledger_id = '$rc->accountledger_id'";
					@pg_query($qu) or message1(pg_errormessage().$qu);
					$credit=0;	
				}
				else
				{
					$credit_balance = $rc->credit - $credit;
					$credit -= $rc->credit;	
					$qu = "update accountledger set credit_balance='$credit_balance' where accountledger_id = '$rc->accountledger_id'";
					@pg_query($qu) or message1(pg_errormessage().$qu);
					
				}
				if ($credit <= '0') break;
			}
			
	}	
}

$q = "update accountledger set last_debit_balance = debit_balance, last_credit_balance=credit_balance";
@pg_query($q) or message1(pg_errormessage().$q);

?>