<?
	session_start();
     require_once("../xajax.inc.php");
     $xajax = new xajax();
     $g     = "";

     $g->objResponse = new xajaxResponse();
     
	include_once("../lib/library.php");
	require_once("../lib/dbconfig.php");
	include_once('../lib/connect.php');

	include_once('xajax__hope.lib.php');
	include_once('xajax.payroll.php');

		function galert($m)
		{
			global $g;
			$g->objResponse->addAlert($m);
		}

	$xajax->registerFunction('viewmemo');
	$xajax->registerFunction('payroll_posting');
	$xajax->registerFunction('paymastLevel');
	function payroll_posting($form) 
	{
		$pid = $form['mark'];
		if ($pid == '')
		{
			galert("No Payroll Period Selected...");
		}
		else
		{
			$pc = payroll_posting_process($pid);
			if ($pc>0)
			{
				glayer('message.layer', '<br><br>Finished Posting...');
			}
			elseif ($pc ==  0)
			{
				galert("Payroll Transactions Already Posted...");
			}
			else
			{
				galert("Problem Posting...");
				glayer('message.layer', '<br><br>Problem Posting...');

			}
		}		
		return done();
	}

	$xajax->registerFunction('payroll_unpost');
	function payroll_unpost($form) 
	{
		$pid = $form['mark'];
		if ($pid == '')
		{
			galert("No Payroll Period Selected...");
		}
		else
		{
			$pc = payroll_unpost_process($pid);
			if ($pc>0)
			{
				glayer('message.layer', '<br><br>Finished UN-Posting...');
			}
			else
			{
				galert("Error UnPosting Payroll Transaction...");
			}
		}
		
		return done();
	}

	$xajax->registerFunction('payroll_recalc_account');
	function payroll_recalc_account($form) 
	{
		$paymast_id = $form['paymast_id'];
		$deduction_type_id = $form['deduction_type_id'];
		$pc = payroll_recalc_account_process($paymast_id, $deduction_type_id);
		if ($pc>0)
		{
			glayer('message.layer', '<br><br>Finished Recalculating Accounts...');
		}
		else
		{
			galert("Error Re-Calculating Payroll Accounts...");
		}
		return done();
	}
$xajax->processRequests();
include_once("../lib/library.js.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<head>
<title>Hope Payroll Module</title>
<?php $xajax->printJavascript('../'); // output	the xajax javascript. This must	be called between the head tags	?>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="javascript">
function wait($message)
{
	xajax.$('message.layer').innerHTML = '';
	xajax.$('wait.layer').style.display = 'block';
	xajax.$('wait.layer').innerHTML = $message+"<br><img src='../graphics/wait.gif'>";
	return;
}
</script>

<body bgcolor="#EFEFEF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<?
	if (!isset($_SESSION['ADMIN']))
	{
		$_SESSION['ADMIN'] = null;
		$_SESSION['ADMIN'] = array();
	}

	include_once("../lib/library.php");
	require_once("../lib/dbconfig.php");
	include_once('../lib/connect.php');
	if (!chkRights3("payrollmodule","mview",$ADMIN['admin_id']) && $ADMIN['admin_id'] != '1')
	{
//print_r($ADMIN);
		message("You have no permission in this area...");
		exit;
	}

	$q = "set search_path to public,payroll";
	@pg_query($q);
	include_once("menu.payroll.php");
	include_once('payroll.lib.php');
	include_once('config.payroll.php');

	if ($p1 == 'Login')
	{
		include_once('../lib/authenticate.php');
		$a = authenticate($username, $mpassword);
		if (!$a)
		{
			message('Username / Password combination does NOT match. Access Denied.');
			include_once('login.php');
			exit;
		}
		else
		{
			
			if ($SYSCONF['CASHIERING'] == 'Y')
			{
				include_once('checkcache.php');
				echo "<script>window.location='./cashier/'</script>";
				exit;
			}
			$p='';
		}
	}
	elseif ($ADMIN['sessionid'] == null)	
	{
	
		message("Security Check. Please Login Again...(Session Check)");
		include_once("login.php");
		exit;
	}	
	elseif ($ADMIN['sessionid'] != null)
	{
		$q = "select * from admin where sessionid = '".$ADMIN['sessionid']."'";
		$qr = @pg_query($q);
		if (!$qr)
		{
			message('Problem validating user login...');
			include_once('login.php');
			exit;
		}
		elseif (@pg_num_rows($qr) == 0)
		{
	
			message('Session expired.  Log-In again...');
			include_once('login.php');
			exit;
		}

	}
	//print_r($SYSCONF);
	if ($p == 'logout')
	{
    $date_out = date('Y-m-d g:ia');
    $q = "update userlog set 
    				date_out='$date_out' 
    			where 
    				userlog_id='".$ADMIN['userlog_id']."' and 
    				admin_id='".$ADMIN['admin_id']."'";

    $qr = @pg_query($q) or message(pg_errormessage());

		$ADMIN=null;
		session_unset();

		message("User has sucessfully logged Out.");
		require_once('login.php');
		exit;
	}

	if ($p != null) 
	{
		if (file_exists("$p.php"))
		{
			include_once("$p.php");	
		}
		else
		{
			message($p." file does not exists...");
			include_once("home.htm");
		}
	}
	else {	 
		include_once("home.htm");
	}
?>
  <div id="message.layer" align="center" style="position:absolute; top:35%; left:25%; background-color=#FFCCC"></div>
  <div id="wait.layer" align="center"></div>

</body>
</html>
