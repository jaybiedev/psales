<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Lopue's Department Stores</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<table width="100%75%" border="0" bgcolor="#FFFFFF">
  <tr>
    <td><img src="graphics/logo.jpg" width="600" height="120"></td>
  </tr>
</table>

<?
	if (!session_is_registered('ADMIN'))
	{
		session_register('ADMIN');
		$ADMIN =null;
		$ADMIN =array();
	}
	include_once('lib/library.php');
	include_once('lib/dbconfig.php');
	include_once('lib/connect.php');

	
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
			$p='';
		}
	}
	elseif ($ADMIN['sessionid'] == null)	
	{
		message("Security Check. Please Login Again...1");
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
	if ($p == 'logout')
	{
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
?>
<div align="center">
  <p>User : 
    <?=$ADMIN['name'];?>
    [ Logout ] <br>
    Today is : 
    <?= date('F d, Y');?>
  </p>
  <p>&nbsp;</p>
</div>
<table width="50%" border="0" align="center" cellpadding="5" cellspacing="0" bgcolor="#EFEFEF">
  <tr bgcolor="#FFFFFF"> 
    <td width="33%"><div align="center"><a href="cashier/"><img src="graphics/order2.jpg" width="70" height="70"><br>
        Cashier</a></div></td>
    <td width="34%"><div align="center"><a href="inventory"><img src="graphics/canned.jpg" width="70" height="70"><br>
        Inventory</a></div></td>
    <td width="33%"><div align="center"><a href="credit/"><img src="graphics/invoice.jpg" width="70" height="70"><br>
        Credit</a></div></td>
  </tr>
  <tr bgcolor="#FFFFFF"> 
    <td><div align="center"><a href="misc/"><img src="graphics/run.jpg" width="67" height="62"><br>
        Misc</a></div></td>
    <td><div align="center"><a href="backend/"><img src="graphics/query.jpg" width="67" height="67"><br>
        System</a></div></td>
    <td><div align="center"><a href="?p=logout"><img src="graphics/door02.gif" width="67" height="67"><br>
        Log-Out</a></div></td>
  </tr>
</table>
<br><br><br><br>
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
