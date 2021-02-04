<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Perfect Sales Integrated System</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body bgColor='#EFEFEF'>
<table width="100%75%" border="0" bgcolor="#FFFFFF">
  <tr>
    <td><img src="graphics/logo.jpg" width="400" height="90"></td>
  </tr>
</table>

<?
	if (!session_is_registered('ADMIN'))
	{
		session_register('ADMIN');
		$ADMIN =null;
		$ADMIN =array();
	}
	
	if ($p1 == 'SwDB' && $SYSCONF['DATABASE'] == '')
	{
		$SYSCONF['DATABASE'] = 'dev';
	}
	elseif ($p1 == 'SwDB' && $SYSCONF['DATABASE'] != '')
	{
		$SYSCONF['DATABASE'] = '';
	}

	include_once('lib/library.php');
	include_once('lib/dbconfig.php');
	include_once('lib/connect.php');
	include_once('var/system.conf.php');
	
	if ($p1 == 'Login')
	{
		include_once('lib/authenticate.php');
		$a = authenticate($username, $mpassword);
		if (!$a)
		{
			message('Username / Password combination does NOT match. Access Denied.');
			include_once('./lib/login.php');
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
		include_once("./lib/login.php");
		exit;
	}	
	elseif ($ADMIN['sessionid'] != null)
	{
		$q = "select * from admin where sessionid = '".$ADMIN['sessionid']."'";
		$qr = @pg_query($q);
		if (!$qr)
		{
			message('Problem validating user login...');
			include_once('./lib/login.php');
			exit;
		}
		elseif (pg_num_rows($qr) == 0)
		{
			message('Session expired.  Log-In again...');
			include_once('./lib/login.php');
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
		require_once('./lib/login.php');
		exit;
	}
	elseif ($p != '')
	{
		include_once("$p.php");
	}
	$term = terminal($SYSCONF['TERMINAL']);
?>
<div align="center">
  <p>User : 
    <?=$ADMIN['name'];?>
    @Terminal: <?= $SYSCONF['TERMINAL'].'.'.$term['DESCRIPTION'];?> <br>
    Today is : 
    <?= date('F d, Y');?>
  </p>
  <p><h3>Press [ F11 ] To View FULL SCREEN</h3></p>
</div>
<table width="50%" border="0" align="center" cellpadding="5" cellspacing="0" bgcolor="#EFEFEF">
  <tr bgcolor="#FFFFFF"> 
    <td width="33%"><div align="center"><a accesskey='C' href="cashier/"><img src="graphics/order2.jpg" width="70" height="70"><br>
        Cashier</a></div></td>
    <td width="34%"><div align="center"><a accesskey='I' href="inventory"><img src="graphics/canned.jpg" width="70" height="70"><br>
        Inventory</a></div></td>
    <td width="33%"><div align="center"><a accesskey='A' href="accounts/"><img src="graphics/invoice.jpg" width="70" height="70"><br>
        Accounts</a></div></td>
  </tr>
  <tr bgcolor="#FFFFFF"> 
    <td><div align="center"><a accesskey='M' href="misc/"><img src="graphics/run.jpg" width="67" height="62"><br>
        Misc</a></div></td>
    <td><div align="center"><a accesskey='S' href="backend/"><img src="graphics/query.jpg" width="67" height="67"><br>
        System</a></div></td>
    <td><div align="center"><a accesskey='L' href="?p=logout"><img src="graphics/door02.gif" width="67" height="67"><br>
        Log-Out</a></div></td>
  </tr>
</table>
<br><div align='center'>
<?
if ($ADMIN['usergroup'] == 'A')
{
	if ($SYSCONF['DATABASE'] != '')
	{
		echo "Using Temporary Data</br>";
		echo "<a href='?p=&p1=SwDB'>Switch To Live DataBase</a></div>";
	}
	else
	{
		echo "Using LIVE Data</br>";
		echo "<a href='?p=&p1=SwDB'>Switch To Temporary/Test DataBase</a></div>";
	}
}
?></div>
<br><br><br>
<div align="center" valign=bottom><img src="graphics/worm_in_hole.gif" width="23" height="33"> 
  <br>
  <img src="graphics/elephantSmall.gif" width="50" height="50"> <img src="graphics/php-small-white.gif" width="88" height="31"><img src="graphics/mysql-powered-by.JPG" width="50" height="50"><br>
  <strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Hope ePOS 
  Inventory Management System</font></strong><br>
  <em><font size="2">Developed by: Jared O. Santibañez, ECE, MT </font> </em> 
  <font size="2"><br>
  email: <a href="mailto:%20jay_565@yahoo.com">jay_565@yahoo.com</a><br>
  </font> </div>

</body>
</html>
