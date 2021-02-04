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

	include_once('../lib/xajax__hope.lib.php');
	
	//--on xajax.inventory
	$xajax->registerFunction('porder_searchaccount');
	$xajax->registerFunction('porder_supplier_select_id');
	$xajax->registerFunction('vBarcode');
	$xajax->registerFunction('genBarcode');
	$xajax->registerFunction('pc_insert');
	$xajax->registerFunction('vAcctno');
	$xajax->registerFunction('pc_grid');
	$xajax->registerFunction('pc_print');
	$xajax->registerFunction('select_sid');
	$xajax->registerFunction('pc_search');
	$xajax->registerFunction('pc_save');
	$xajax->registerFunction('download');
	$xajax->registerFunction('checkRRInvoice');
	$xajax->registerFunction('autoCompleteAccount');
	$xajax->registerFunction('hidelayer');
	$xajax->registerFunction('stocktransferVPNUpload');

	$xajax->registerFunction('rr_subtotal');
	$xajax->registerFunction('rrLoad');
	$xajax->registerFunction('rrEdit');
	$xajax->registerFunction('rrOk');
	$xajax->registerFunction('rrDelete');
	$xajax->registerFunction('rrSearchPO');


	$xajax->registerFunction('por_subtotal');
	$xajax->registerFunction('porLoad');
	$xajax->registerFunction('porEdit');
	$xajax->registerFunction('porOk');
	$xajax->registerFunction('porDelete');
	$xajax->registerFunction('porCheckRR');

	include_once('xajax.inventory.php');
	include_once('xajax.receiving.php');
	include_once('xajax.poreturn.php');


      //$xajax->debugOn();
$xajax->processRequests();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<head>
<title>Hope Perfect Sales Inventory Module</title>
<?php $xajax->printJavascript('../'); // output	the xajax javascript. This must	be called between the head tags	?>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<STYLE TYPE="text/css">
<!--
	A:link  {text-decoration: none;}
	A:hover {text-decoration:;font-weight: bold;}
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

<script>
<!---
function wait($message)
{
	xajax.$('message.layer').innerHTML = '';
	xajax.$('wait.layer').style.display = 'block';
	xajax.$('wait.layer').innerHTML = "<font size='2'>"+$message+"</font><br><img src='../graphics/wait.gif'>";
	return;
}
function focusIt($e)
{
	document.getElementById($e).focus();
}

-->
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
    <td width="62%"><img src="../graphics/logo.jpg"  height="50"></td>
    <td width="38%" align="center" nowrap="nowrap"><font size="2">User: 
      <?= $ADMIN['name'].'@'.$SYSCONF['TERMINAL'];?>
      [ <a  accesskey="L" href="../?p=logout">Logout</a> ]<br>
      <?= date('F d,Y');?>
     <br>
      Inventory Module <?=($SYSCONF['DATABASE'] !=''? 'DB: '.$SYSCONF['DATABASE'] : '').' -'.$SYSCONF['BRANCH'];?> </font>
    </td>
  </tr>
</table>
<table width="100%" border="0" cellpadding="1" cellspacing="1" bgcolor="#999999">
  <tr bgcolor="#CCCCCC"  background="../graphics/header.gif"> 
    <td width="8%" height="19" align="center"> 
      <a accesskey="S" href="?p=stock" class="altLink"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Stocks</font></a></td>
    <td width="8%" align="center"> 
      <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href="?p=menu.barcode"  class="altLink">Barcode</a></font></td>
    <td width="8%" align="center"> 
      <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href="?p=menu.purchase" class="altLink">Purchasing</a></font></td>
    <td width="8%" align="center" > 
      <font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=receiving" class="altLink">Receiving</a></font></td>
    <td width="8%" align="center" ><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=menu.other"  class="altLink">Other</a></font></td>
    <td width="8%" align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <a href="?p=changeprice"  class="altLink">ChangePrice</a></font></td>
    <td width="8%" align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <a accesskey="R" href="?p=menu.report"  class="altLink">Reports</a></font></td>
    <td width="8%" align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <a accesskey="E" href="?p=menu.setup"  class="altLink">Setup</a></font></td>
    <td width="8%" align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <a  accesskey="H" href="../?"  class="altLink">Home</a></font></td>
    <td width="8%" align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <a  accesskey="L" href="../?p=logout"  class="altLink">Logout</a></font></td>
    <td width="10%" >&nbsp;</td>
  </tr>
</table>
<?php
if ($ADMIN['sessionid'] == '' || $SYSCONF['IP']=='')
{
	echo "<script>window.location='../'</script>";
	exit;
}
include_once('../lib/library.php');
include_once('../lib/dbconfig.php');
include_once('../lib/connect.php');
include_once('../var/system.conf.php');
include_once('../lib/library.js.php');

if ($p != '')
{
	include_once("$p.php");
}
else
{
	include_once('plusearch.php');
}
?>
  <div id="message.layer" align="center" style="position:virtual; top:0%; left:25%; background-color=#FFFCCC; z-index=10"></div>
  <div id="wait.layer" style="position:absolute;left: 40%; top: 50%; background-color: #CCCCCC; layer-background-color: #FFFFFF; border: 1px none #000000;"></div>

</body>
</html>
