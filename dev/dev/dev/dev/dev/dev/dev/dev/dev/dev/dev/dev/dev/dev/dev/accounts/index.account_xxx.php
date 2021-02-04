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


	$xajax->registerFunction('posting');
	function posting($form) 
	{
		global $ADMIN, $SYSCONF;
		
		$mcutoff_date = mdy2ymd($form['cutoff_date']);
		$mgrace_date = mdy2ymd($form['grace_date']);
		$ok=1;

	
		$q = "delete from accountledger where date='$mgrace_date' and type='I'";
		@pg_query($q);

		$q = "select * from account and account_id = '1129'";
		$qr = @pg_query($q);
		while ($r = @pg_fetch_object($qr))
		{

			$payment_made = $interest = 0;
			$maccount_id = $r->account_id;

			$q = "select account_id, sum(credit_balance) as credit_balance 
							from 
								accountledger 
							where 
								status!='C'  and
								enable='Y' and 
								accountledger.account_id = '$r->account_id'
							group by 
								account_id";
			$qr = @pg_query($q);
			if (!$qr)
			{
				$ok=0;
				$message = pg_errormessage($qr);
			}
		
			$rp = @pg_fetch_object($qr)
			$payment_made = $rp->credit_balance;
		
		
			$q = "select 
						* 
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
						accountledger.date <= '$mcutoff_date' and 
						accountledger.account_id='$r->account_id'
					order by 
						accountledger.account_id";
			$qi = @pg_query($q);
			if (!$qr)
			{
				$message .= pg_errormessage($qi).$q;
			}

			while ($ri = @pg_fetch_object($qi))
			{
			
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

				if ($period_due == '0') continue;
			
				$grocery_period = round($ri->grocery_term/$ri->grocery_interval,0);
				$drygood_period = round($ri->drygood_term/$ri->drygood_interval,0);
			
				$debit_balance = $ri->debit_balance;
				if ($period_due <= $grocery_period)
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

				if ($maccount_id =='1129')// || $maccount_id == '132')
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
				
				$q = "select * from accountpost where cutoff_date = '$mcutoff_date'";
				$qr = @pg_query($q);
				if (@pg_num_rows($qr) == 0)
				{
					$audit = 'Posted by: ['.$ADMIN['admin_id'].']'.$ADMIN['name'].' on '.date('m/d/Y g:ia').';';
					$q = "insert into accountpost (date, cutoff_date, grace_date, admin_id, audit, enable)
								values ('$now', '$mcutoff_date', '$mgrace_date', '".$ADMIN['admin_id']."', '$audit', 'Y')";
				 	@pg_query($q);
				}
				else
				{
					$r = @pg_fetch_object($qr);
					$audit = $r->audit;
					$audit .= 'Updated by: ['.$ADMIN['admin_id'].']'.$ADMIN['name'].' on '.date('m/d/Y g:ia').';';
					$q = "update accountpost set audit = '$audit' where accountpost_id= '$r->accountpost_id'";
				 	@pg_query($q);
					
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
