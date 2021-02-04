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

	if ($SYSCONF['IP'] == '' || $ADMIN['sessionid'] == '')
	{
		echo "<script>window.location='../'</script>";
		exit;
	}

	$xajax->registerFunction('grossprofit');
	function grossprofit($form) 
	{
		global  $SYSCONF;
		
		$mfrom_date = mdy2ymd($form['from_date']);
		$mto_date = mdy2ymd($form['to_date']);
		$dept = $form['dept'];

		$tables = currTables($mfrom_date);
		$sales_header = $tables['sales_header'];
		$sales_detail = $tables['sales_detail'];
		$sales_tender = $tables['sales_tender'];

		$header .="\n\n\n";
		$header .= center($SYSCONF['BUSINESS_NAME'],80)."\n";

		$header .= center('REPORT On GROSS PROFIT/LOSS SALES '.strtoupper($PCENTER),80)."\n";
		$header .= center('Transaction Date from '.ymd2mdy($mfrom_date).' to '.ymd2mdy($mto_date),80)."\n";
		$header .= center('Printed '.date('m/d/Y').' '.$ADMIN['username'],80)."\n\n";
		$header .= '                                                         Sales    Net Sales     Total       Profit/'."\n";
		$header .= '                Supplier                                 Qty       Amount      Cost         (Loss)'."\n";
		$header .= '--------------- -------------------------------------  -------- ------------ ------------ ------------'."\n";
		$page = 1;
		$lc = 10;
		$details = '';
		$details2 = '';

		
		$q = "select 
					sum(qty) as sales_qty,	
					sum(amount) as sales_amount,	
					stock.account_id,
					account.account,
					account.account_code,
					sum(nif(rd.cost1 is null, stock.cost1*qty, rd.cost1*rd.qty)) as cost
				from
					$sales_detail as rd,
					$sales_header as rh,
					stock,
					category,
					account
				where
					rh.sales_header_id=rd.sales_header_id and
					stock.stock_id=rd.stock_id and
					category.category_id= stock.category_id and 
					account.account_id = stock.account_id and 
					rh.date >= '$mfrom_date' and
					rh.date <= '$mto_date' and
					(rh.status != 'V' and rh.status !='C')";
				
		$q .= "	group by 
						stock.account_id ,
						stock.account_id,
						account.account,
						account.account_code
					order by account.account_code";	
		
		$qr = @pg_query($q);
		if (!$qr) 
		{
				glayer('message.layer','QUERY ERRROR : '.pg_errormessage(). $q);
				return done();
		}

		
		$total_amount = 0;
		$total_discount = 0;
		$ctr = 0;
		while ($r = pg_fetch_object($qr))
		{

			$net_sales_qty = $r->sales_qty - $r->return_qty;
			$net_sales_amount = $r->sales_amount - $r->return_amount;
			//$cost = $net_sales_qty * $r->cost;
			
			$cost = $r->cost;
			$profit = $net_sales_amount - $cost;
			
			$ctr++;
			$details .= adjustSize($r->account_code,15).'  '.
						adjustSize($r->account,35).'  '.
						adjustRight(number_format($net_sales_qty,0),8).' '.
						adjustRight(number_format($net_sales_amount,2),12).' '.
						adjustRight(number_format($cost,2),12).' '.
						adjustRight(number_format($profit,2),12).' '."\n";
			$total_net_sales  += $net_sales_amount;
			$total_cost += $cost;
			$total_profit += $profit;			
			$lc++;
		}
		$details .= '-----------------------------------------------------  -------- ------------ ------------ ------------'."\n";
		$details .= adjustSize(' Total Items  '.$ctr,42).space(21).
						adjustRight(number_format($total_net_sales,2),12).' '.
						adjustRight(number_format($total_cost,2),12).' '.
						adjustRight(number_format($total_profit,2),12).' '."\n";
		$details .= '=====================================================  ======== ============ ============ ============'."\n";
		$details2 .= $header.$details;
		
		gset('print_area', $details2);	
		return done();
	}
	
	$xajax->registerFunction('fastmove');
	function fastmove($form) 
	{
		global $SYSCONF;
		$from_month = $form['from_month'];
		$to_month = $form['to_month'];
		$year = $form['year'];
		$top = $form['top'];
		$sort = $form['sort'];
		$dept = $form['dept'];
		
		if (strlen($from_month) == 1) $from_month =  rtrim('0'.$from_month);
		if (strlen($to_month) == 1) $to_month = rtrim('0'.$to_month);
		

		$msd = $year.'-'.$from_month.'01';
		$tables = currTables($msd);
		$sales_header = $tables['sales_header'];
		$sales_detail = $tables['sales_detail'];
		$sales_tender = $tables['sales_tender'];

		$header ="\n";
		$header .= $SYSCONF['BUSINESS_NAME']."\n";
		$header .= $SYSCONF['BUSINESS_ADDR']."\n";
		$header .= 'STOCKS FAST MOVING REPORT TOP '.$top   ."\n";
		$header .= '   #  Inventory Item                                                    Items       Amount'."\n";
		$header .= '----- ---- ---------------------------------------------------------- -------- --------------'."\n";
		$page = 1;
		$lc = 10;
		$details = '';
		$details2 = '';
		
		$q = "select 
					sum(amount) as amount,
					stock.stock,
					stock.barcode, 
					sum(stock.fraction3 * rd.qty) as qty
			 from
					$sales_header as rh,
					$sales_detail as rd,
					stock,
					category
				where
					stock.stock_id= rd.stock_id and 
					category.category_id = stock.category_id and 
					rh.sales_header_id=rd.sales_header_id and 
					substring(date,1,4) = '$year' and
					substring(date,6,2) >= '$from_month' and
					substring(date,6,2) <= '$to_month' and
					(rh.status != 'V' and rh.status !='C')";
 
 		if ($dept != '')
		{
			$q .= " and category.department = '$dept'";
		}
		$q .= "	group by 
						stock,
						stock.barcode,
						rd.stock_id
					order by
						$sort  desc ";
		if ($top != '')
		{
			$q .= " offset 0 limit $top ";
		}

		$qr = @pg_query($q) or message(pg_errormessage());

		$total_amount = 0;
		$total_qty = 0;
		$ctr = 0;
		
		while ($r = pg_fetch_object($qr))
		{
			
			if (intval($r->qty) != $r->qty)
			{
				$cqty = number_format($r->qty,3);
			}
			else
			{
				$cqty = number_format($r->qty,0);
			}
			$ctr++;
			$details .= adjustRight($ctr,4).'. '.
						adjustSize($r->barcode,15).' '.
						adjustSize(substr($r->stock,0,35),45).' '.
						adjustRight($cqty,10).' '.
						adjustRight(number_format($r->amount,2),14)."\n";
			$total_amount += $r->amount;
			$total_qty += $r->qty;			
			$lc++;
			if ($p1 == 'Print Draft' && $lc > 58)
			{
				//$details .= "Page ".$page."<eject>\n\n";
				doPrint($header.$details);
				$lc=10;
				$page++;
				$details2 .= $header.$details;
				$details = '';
			}
		}
		$details .= '---------------------------------------------------------------------------------------------'."\n";
		$details .= adjustSize($ctr.' Total ',26).space(38).
						adjustRight(number_format($total_qty,0),12).' '.
						adjustRight(number_format($total_amount,2),16)."\n";
		$details .= '============================================================================================='."\n";
		$details2 .= $header.$details;
		gset('print_area', $details2);
		if ($p1 == 'Print Draft' or $g1 == 'Print Draft')
		{
			//$details .= "Page ".$page."<eject>\n\n";
			nPrinter($header.$details, $SYSCONF['REPORT_PRINTER_TYPE'], $SYSCONF['REPORT_PRINTER_DEST']);
			$page++;
		}
		/*		
				$aip = explode('.',$_SERVER['REMOTE_ADDR']);
				
				$reportfile= 'reports/REPORT'.$aip[3].'.txt';
				$fo = fopen($reportfile,'w+');
				if (!fwrite($fo, $details2))
				{
					 message("Unable to create report file...");
				}
			  glayer('message.layer','Finished Processing data...');
		*/
		return done();
	}
$xajax->processRequests();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<head>
<title>Hope Integrated Systems</title>
<?php $xajax->printJavascript('../'); // output	the xajax javascript. This must	be called between the head tags	?>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<STYLE TYPE="text/css">
<!--
	A:link  {text-decoration: none;}
	A:hover {text-decoration:;font-weight: bold;}
	A:active {text-decoration: none; }
	A:visited {text-decoration: none;  }
	A:visited:active {text-decoration: underline; }
	A:visited:hover {text-decoration: underline; }
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
<script language="javascript">
function wait($message)
{
	
	xajax.$('message.layer').innerHTML = '';
	xajax.$('wait.layer').style.position= 'absolute';
	xajax.$('wait.layer').style.position= 'absolute';
	xajax.$('wait.layer').style.top= '250px';
		xajax.$('wait.layer').style.left= '40%';
	xajax.$('wait.layer').style.display = 'block';
	xajax.$('wait.layer').innerHTML = $message+"<br><img src='../graphics/wait.gif'>";
	return;
}
</script>
<link rel="stylesheet" href="../css/bubble-tooltip.css" media="screen">
<script type="text/javascript" src="../js/bubble-tooltip.js"></script>

</head>

<body bgcolor="#EFEFEF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<div id="bubble_tooltip">
	<div class="bubble_top"><span></span></div>
	
  <div class="bubble_middle"><span id="bubble_tooltip_content">Content is comming 
    here as you probably can see.Content is comming here as you probably can see.</span></div>
	
  <div class="bubble_bottom"></div>
</div>


<div name="wait.layer" id="wait.layer"  style="position:absolute;left: 40%; top: 50%; background-color: #CCCCCC; layer-background-color: #FFFFFF; border: 1px none #000000;"></div>
<div name="message.layer" id="message.layer"></div>

<table width="100%" border="0" bgcolor="#FFFFFF">
  <tr>
    <td width="68%"><img src="../graphics/logo.jpg" height="50"></td>
    <td width="32%" align="center"><font size="2">User: 
      <?= $ADMIN['name'];?>
      [ <a accesskey="L" href="../?p=logout">Logout</a> ]<br>
	<?= date('F d,Y');?>
	<br>
      Management Module <?=($SYSCONF['DATABASE'] !=''? 'DB: '.$SYSCONF['DATABASE'] : '').' -'.$SYSCONF['BRANCH'];?> </font>
	</td>
  </tr>
</table>
<table width="100%" border="0" cellpadding="1" cellspacing="1" bgcolor="#999999">
  <tr bgcolor="#CCCCCC"   background="../graphics/header.gif"> 
    <td width="12%" height="19" align="center" > 
      <a accesskey="Z" href="?p=menu.recon" class="altLink"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Reconciliation</font></a></td>
    <td width="12%" align="center"   >
	<font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=menu.inquiry" class="altLink">Inquiry</a></font></td>
    <td width="12%" align="center"    ><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <a  accesskey="S" href="?p=../backend/terminal" class="altLink">Setup</a></font></td>
    <td width="12%" align="center"   >
	<font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <a  accesskey="H" href="?" class="altLink">Close</a></font></td>
    <td width="12%" align="center"   >
	<font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a  accesskey="H" href="../?" class="altLink">Home</a></font></td>
    <td width="40%"   >&nbsp;</td>
  </tr>
</table>
<?php

	include_once('../lib/library.js.php');

	if ($SYSCONF['IP'] == '' || $ADMIN['sessionid'] == '')
	{
		echo "<script>window.location='../'</script>";
		exit;
	}

	if  (!chkRights2('management','mview',$ADMIN['admin_id']) and $ADMIN['usergroup'] == 'A')
	{
		echo "<div align=center><h2>Permission Denied...</h2></div>";
		exit;
	}

	if ($p != '')
	{
		include_once("$p.php");
		exit;
	}
	
	if (file_exists('home.management.php'))
	{
		include_once('home.management.php');
	}
?>

  <div id="message.layer" align="center"></div>
  <div id="wait.layer" align="center"  style="background-color:'#FFCC00'"></div>
</body>
</html>
