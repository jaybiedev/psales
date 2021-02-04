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
	include_once('xajax.accounts.php');

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

		.altLink:link  {text-decoration: none; color: #FFFFFF;}
		.altLink:hover {text-decoration:;font-weight: bold; color: #FFFFFF;}
		.altLink:active {text-decoration: none; color: #EFEFEF;}
		.altLink:visited {text-decoration: none; color: #FFFFFF;}
		.altLink:visited:active {text-decoration: none; color: #cc0000;}
		.altLink:visited:hover {text-decoration: none; color: #FFFFFF;}


-->  
</STYLE>
<link rel="stylesheet" href="../css/bubble-tooltip.css" media="screen">
<script type="text/javascript" src="../js/bubble-tooltip.js"></script>

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
<div id="bubble_tooltip">
	<div class="bubble_top"><span></span></div>
	
  <div class="bubble_middle"><span id="bubble_tooltip_content">Content is comming 
    here as you probably can see.Content is comming here as you probably can see.</span></div>
	
  <div class="bubble_bottom"></div>
</div>

<table width="100%" border="0" bgcolor="#FFFFFF">
  <tr>
    <td width="50%"><img src="../graphics/logo.jpg"  height="50"></td>
    <td width="50%" align="center"><font size="2">User: 
      <?= $ADMIN['name'];?>
      [ <a accesskey="L" href="../?p=logout">Logout</a> ]<br>
      <?= date('F d,Y');?>
      <br>
      Accounts Receivables <?=($SYSCONF['DATABASE'] !=''? 'DB: '.$SYSCONF['DATABASE'] : '').' - '.$SYSCONF['BRANCH'];?> </font>
	</td>
  </tr>
</table>
<table width="100%" border="0" cellpadding="1" cellspacing="1" bgcolor="#999999">
  <tr bgcolor="#CCCCCC"  background="../graphics/header.gif" > 
    <td width="10%" height="19" align="center"> 
      <a accesskey="Z" href="?p=account"   class="altLink"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Accounts</font></a></td>
    <td width="10%" align="center"> 
     <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href="?p=guarantor"   class="altLink">Guarantor</a></font></td>
    <td width="10%"  align="center"> 
      <font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=account.menu.posting"  class="altLink">Posting</a></font></td>
    <td width="10%" align="center">
	<font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href="?p=account.menu.reward"   class="altLink">Rewards</a></font></td>
    <td width="10%" align="center">
	<font size="2" face="Verdana, Arial, Helvetica, sans-serif">  <a href="?p=account.menu.report" class="altLink">Reports</a></font></td>
    <td width="10%" align="center">
	<font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=account.menu.setup" class="altLink">Setup</a></font></td>
    <td width="10%" align="center">
	<font size="2" face="Verdana, Arial, Helvetica, sans-serif">Password</font></td>
    <td width="10%" align="center">
	<font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a  accesskey="S" href="../?" class="altLink">Home</a></font></td>
    <td width="20%">&nbsp;</td>
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
<div name="wait.layer" id="wait.layer"  style="position:absolute;left: 40%; top: 50%; background-color: #CCCCCC; layer-background-color: #FFFFFF; border: 1px none #000000;"></div>
</body>
</html>
