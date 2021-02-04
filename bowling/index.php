<body bgcolor="#EFEFEF"><img src="../graphics/logo.jpg"  width="300" height="60">
<hr size="1">

<a href="?p=stockpcenter.browse">Products</a>  |  <a href="pcenter.cashier.php">Cashiering</a> |
 Inventory | <a href="?p=menu.report">Reports</a> | <a href="?">Home</a> |  <a href="?p=logout">Logout</a>
<? 
if (!session_is_registered('ADMIN'))
{
	session_register('ADMIN');
	$ADMIN = null;
	$ADMIN = array();
}

include_once('../lib/library.php');
include_once('../lib/dbconfig.php');
include_once('../lib/connect.php');
include_once('../var/system.conf.php');
include_once('../lib/library.js.php');

$SYSCONF['PCENTER'] = 'bowling';
$tables = $SYSCONF['tables'];
$stocktable = $tables['stock_table'];
$sales_header = $tables['sales_header'];
$sales_detail = $tables['sales_detail'];
$sales_tender = $tables['sales_tender'];


if ($p== 'logout')
{
	session_unset();
	message1("User Successfully Logged Out...");
}

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
		elseif (pg_num_rows($qr) == 0)
		{
			message('Session expired.  Log-In again...');
			include_once('login.php');
			exit;
		}

}
if (!chkRights2($SYSCONF['PCENTER'],'mview',$ADMIN['admin_id']))
{
		message1("You have NO permission for module : ".strtoupper($SYSCONF['PCENTER']));
		exit;
}


if ($p != '')
{

	if (file_exists("$p.php"))
	{
		include_once("$p.php");
		exit;
	}
	else
	{
		message1("Module NOT found...");
	}
}
else
{
	echo "<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>";	
	echo "<div align='center'>";
	
	echo "<img src='../graphics/php-small-whitee.gif'><img src='../graphics/elephantSmall.gif'>";
	echo "<img src='../graphics/worm_in_hole.gif'><br>";
	echo "<font size='2'>Developed by: Jared O. Santibanez, ECE, MT</font>";
	echo "</div>";
}

?>
