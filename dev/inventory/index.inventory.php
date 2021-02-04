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


	$xajax->registerFunction('download');
	function download($form) 
	{
		
		$date = $form['date'];
		$filename = $form['filename'];
		
		if ($date == '')
		{
			glayer('message.layer','No date specified...'.$form['date']);
			return done();
		}
		
		if ($filename == '')
		{
			glayer('message.layer','No Filename specified...'.$form['filename']);
			return done();
		}
		$mdate = mdy2ymd($date);
		
		$q = "select 
				stock.barcode,
				sales_detail.qty,
				sales_detail.price,
				sales_detail.amount,
				stock.ccategory,
				stock.category_id
				
			from
				stock,
				sales_header,
				sales_detail
			where
				stock.stock_id=sales_detail.stock_id and
				sales_header.sales_header_id=sales_detail.sales_header_id and
				sales_header.status!='V' and
				sales_header.date='$mdate'";
			
		$qr = @pg_query($q);
		if (!$qr)
		{
			glayer('message.layer','Error querying...'.pg_errormessage());
			return done();
		}
		if (@pg_num_rows($qr) <= 0)
		{
			glayer('message.layer','No DATA Found for this transaction date...');
			return done();
    }
		
		$d = explode('-',$mdate);
		$d1 = substr($d[0],2,2).$d[1].$d[2];
		$ln = "\"H\",\"POS MACHS\", \"$d1\", 1,\"       \",0"."\n";
		
		while ($r = @pg_fetch_object($qr))
		{
			if ($r->category_id > 0)
			{
				$q = "select category_code from category where category_id = '$r->category_id'";
				$qqr = @pg_query($q);
				$rr = @pg_fetch_object($qqr);
				$category_code = $rr->category_code;
			}
			else
			{
				$category_code = $r->ccategory;
			}	
			$ln .= "\"D\",".
				"\"".adjustSize($r->barcode,12)."\",".
				"\"".adjustSize($category_code,4)."\",".
				"\"A2\",".
				"\"".space(10)."\",".
				number_format($r->qty,2).",".
				"\"".space(10)."\",".
				number_format($r->amount,2).",".
				number_format($r->price,2)."\n";
				
		}
		
		$fl = "../ics/".$filename;
		
  		$handle = @fopen($fl,"w+");
  		if (!$handle)
  		{
  			glayer('message.layer','Cannot create file...'.$fl);
  			return done();
  		}
  		
  		$w = @fwrite($handle,$ln);
  		if (!$w)
  		{
  			glayer('message.layer','Cannot write into file...'.$fl);
  			return done();
  		}
  		fclose($handle);
  			
  		$t = "<table align=\"center\">";
  		
  		$t .= "<tr><td><a href='$fl'><h3>$filename</a></h3></td></tr>";
  		$t .="</table>";
  		
	 	 glayer('grid.layer',$t);
					
		  glayer('message.layer','Finished Processing data...Please Click File To Download...Date: '.$mdate);

		return done();
	}

      //$xajax->debugOn();
$xajax->processRequests();
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Hope Integrated Systems</title>
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
-->  
</STYLE>
</head>

<body bgcolor="#EFEFEF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<table width="100%" border="0" bgcolor="#FFFFFF">
  <tr>
    <td width="82%"><img src="../graphics/logo.jpg" width="350" height="60"></td>
    <td width="18%" align="center"><font size="2">User: 
      <?= $ADMIN['name'];?>
      [ <a  accesskey="L" href="../?p=logout">Logout</a> ]<br>
      <?= date('F d,Y');?>
      </font><br>
      <?=($SYSCONF['DATABASE'] !=''? 'DB: '.$SYSCONF['DATABASE'] : '');?>
    </td>
  </tr>
</table>
<table width="100%" border="0" cellpadding="1" cellspacing="1" bgcolor="#999999">
  <tr bgcolor="#CCCCCC"> 
    <td width="8%" height="19" align="center" onMouseOver="this.className='highlight'" onMouseOut="this.className='normal'"> 
      <a accesskey="S" href="?p=stock"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Stocks</font></a></td>
    <td width="8%" align="center"  onMouseOver="this.className='highlight'" onMouseOut="this.className='normal'"> 
      <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href="?p=barcode.print">Barcode</a></font></td>
    <td width="8%" align="center"  onMouseOver="this.className='highlight'" onMouseOut="this.className='normal'"> 
      <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href="?p=porder">Purchase</a></font></td>
    <td width="8%" align="center"  onMouseOver="this.className='highlight'" onMouseOut="this.className='normal'"> 
      <font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=receiving">Receiving</a></font></td>
    <td width="8%" align="center"  onMouseOver="this.className='highlight'" onMouseOut="this.className='normal'"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Returns</font></td>
    <td width="8%" align="center"  onMouseOver="this.className='highlight'" onMouseOut="this.className='normal'"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      Adjustment</font></td>
    <td width="8%" align="center"  onMouseOver="this.className='highlight'" onMouseOut="this.className='normal'"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <a accesskey="R" href="?p=menu.report">Reports</a></font></td>
    <td width="8%" align="center"  onMouseOver="this.className='highlight'" onMouseOut="this.className='normal'"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <a accesskey="E" href="?p=menu.setup">Setup</a></font></td>
    <td width="8%" align="center"  onMouseOver="this.className='highlight'" onMouseOut="this.className='normal'"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <a  accesskey="H" href="../?">Home</a></font></td>
    <td width="8%" align="center"  onMouseOver="this.className='highlight'" onMouseOut="this.className='normal'"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <a  accesskey="L" href="../?p=logout">Logout</a></font></td>
    <td width="10%">&nbsp;</td>
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
?>
</body>
</html>
