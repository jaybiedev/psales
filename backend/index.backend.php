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

		function galert($m)
		{
			global $g;
			$g->objResponse->addAlert($m);
		}

	$xajax->registerFunction('inventoryforward');
	function inventoryforward($form) 
	{	
		$year_from = $form['year_from'];
		$year_to = $form['year_to'];
		
		$mdate_from = $year_from.'-01-01';
		$mdate_to = $year_to.'-01-01';
		
		$tables = currTables($mdate_from);
		$sales_header_from = $tables['sales_header'];
		$sales_detail_from = $tables['sales_detail'];
		$sales_tender_from = $tables['sales_tender'];
		$stockledger_from = $tables['stockledger'];
		
		$tables = currTables($mdate_to);
		$sales_header_to = $tables['sales_header'];
		$sales_detail_to = $tables['sales_detail'];
		$sales_tender_to = $tables['sales_tender'];
		$stockledger_to = $tables['stockledger'];
		
		galert('From Stockledger '.$stockledger_from.' To '.$stockledger_to);		
		return done();
	}

	$xajax->registerFunction('postcost');
	function postcost($form) 
	{
		$q= "select distinct(stock_id) as stock_id from e2008.sales_detail as sd";
		$qr = @pg_query($q);
		if (!$qr)
		{
			galert(pg_errormessage().$q);
			return done();
		}
		while ($r = @pg_fetch_object($qr))
		{
			if (!$r->stock_id) continue;
			$qq = "select cost1 from stock where stock_id ='$r->stock_id'";
			$qqr = @pg_query($qq);

			if (!$qqr)
			{
				galert(pg_errormessage().$qq);
				break;
			}
			$rr = @pg_fetch_object($qqr);
			if (!$rr->cost1) continue;
			$qu = "update e2008.sales_detail set cost1='$rr->cost1' where stock_id = '$r->stock_id'";
			$qur = @pg_query($qu);

			if (!$qur)
			{
				galert(pg_errormessage().$qu);
				break;
			}
			
			
		}
		galert('Done...');
		return done();
	}

	$xajax->registerFunction('process_eoday');
	function process_eoday($form) 
	{
		$from_date = $form['from_date'];
		$to_date = $form['to_date'];
		
		if ($from_date == '')
		{
			glayer('message.layer','No date specified...'.$form['from_date']);
			return done();
		}
		$mfrom_date = mdy2ymd($from_date);
		if ($to_date != '')
		{
			if ($from_date > $to_date)
			{
				glayer('message.layer', 'Invalid Date Coverage...');
				return done();
			}
			$mto_date = mdy2ymd($to_date);
		}
		else
		{
			$mto_date = '';
		}

		include_once('../backend/eoday.php');
		$aPost = eoday($mfrom_date, $mto_date);
		
		if ($aPost['Ok'] == '0')
		{
			//glayer('message.layer',$aPost['message']);
			galert($aPost['message']);
		}
		else
		{
			galert('End of Day Posting Finished...');
		}
		return done();
	}
	
	$xajax->registerFunction('export_category');
	function export_category($form) 
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
		$tables = currTables($mdate);
		$sales_header = $tables['sales_header'];
		$sales_detail = $tables['sales_detail'];
		$sales_tender = $tables['sales_tender'];

		
		$q = "select 
				sum($sales_detail.qty) as qty,
				sum($sales_detail.amount) as amount,
				substr(category.category_code,1,2) as category_code
			from
				stock,
				$sales_header,
				$sales_detail,
				category
			where
				stock.stock_id=$sales_detail.stock_id and
				stock.category_id=category.category_id and 
				$sales_header.sales_header_id=$sales_detail.sales_header_id and
				$sales_header.status!='V' and
				$sales_header.date='$mdate' 
			group by
				substr(category.category_code,1,2)";
				
		$qr = @pg_query($q);
		if (!$qr)
		{
			glayer('message.layer','Error querying...'.pg_errormessage().$q);
			return done();
		}
		if (@pg_num_rows($qr) <= 0)
		{
			glayer('message.layer','No DATA Found for this transaction date...');
			return done();
    }
		
		$d = explode('-',$mdate);
		$d1 = substr($d[0],2,2).$d[1].$d[2];
		
		while ($r = @pg_fetch_object($qr))
		{
			$q = "select category from category where substr(category_code,1,2) = '$r->category_code' order by category_code";
			$qqr = @pg_query($q);
			$rr = @pg_fetch_object($qqr);

			$ln .= adjustSize($rr->category,25).';'.adjustRight(intval($r->qty),8).';'.adjustRight($r->amount,12)."\n";
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


	$xajax->registerFunction('account_download');
	function account_download($form) 
	{
		global $SYSCONF;
		$date = $form['date'];
		$filename = $form['filename'];
		$account_type_id = $form['account_type_id'];
			
		if ($date == '')
		{
			galert('No date specified...'.$form['date']);
			return done();
		}
		
		if ($filename == '')
		{
			galert('No Filename specified...'.$form['filename']);
			return done();
		}
		$mdate = mdy2ymd($date);
		
		$q = "select * 
					from 
						account 
					where 
						date_applied >= '$mdate' and 
						branch_id='".$SYSCONF['BRANCH_ID']."'";
		if ($account_type_id != '')
		{
			$q .= " and account_type_id = '$account_type_id'";
		}
		
		$qr = @pg_query($q);
		if (!$qr)
		{
			galert(pg_errormessage().$q);
			return done();
		}

		$details = '';
		$fields = array('account_id','account_code','cardno','cardname','account','account_status',
							'date_applied','date_expiry','date_birth','gender','civil_status','telno',
							'guarantor_id','credit_limit','bond','remarks',
							'account_class_id','account_type_id','address','branch_id');
		$c=0;
		while ($r = @pg_fetch_assoc($qr))
		{
			for ($c = 0;$c<count($fields);$c++)
			{
				if ($c>0)
				{
					$details .= '||';
				}
				$details .= $r[$fields[$c]];
			}
			$details .="\n";
		}		

		$fl = "../ics/".$filename;
		
  		$handle = @fopen($fl,"w+");
  		if (!$handle)
  		{
  			galert('Cannot create file...'.$fl);
  			return done();
  		}
  		
  		$w = @fwrite($handle,$details);
  		if (!$w)
  		{
  			galert('Cannot write into file...'.$fl);
  			return done();
  		}
  		fclose($handle);

		if (file_exists($fl))
		{
  			$t = "<table align=\"center\">";
  		
  			$t .= "<tr><td><a href='$fl'><h3>$filename</a></h3></td></tr>";
  			$t .="</table>";
  		
		 	glayer('grid.layer','Finished Downloading Account Info...Please Right Click File (Save Target) To Download...'."\n".$t);
		}
		else
		{
			galert('Unable to create File for Account Info into Target Destination '.$s);
		}
		glayer('wait.layer','');
		return done();
		
	}
	
	$xajax->registerFunction('saveAccountUpload');
	function saveAccountUpload($form)
	{
		global $aRecs;
		
		$good_ctr = $bad_ctr = $ctr = 0;
		$badcontent = $goodcontent = '';
		foreach ($aRecs as $temp)
		{
				$ctr++;
			 	$account_code = $temp['account_code'];
			 	$account_id = $temp['account_id'];
			 	
			 	$q0 = "insert into account (";
			 	$q1 = " values ( ";

				$c=0;
				foreach ($temp as $key => $value)
				{
					$value = addslashes($value);
					if ($value == ''&& in_array($key,array('account_class_id','account_type_id','credit_limit','branch_id')))
					{
						$value = 0;
					}
					if ($c>0)
					{
						$q0 .= ',';
						$q1 .= ',';
					}
					$q0 .= $key;
					$q1 .= "'$value'";
					$c++;
			 	}
			 	$q0 .= ")";
			 	$q1 .= ")";
			 	$q = $q0.$q1;
			 	
			 	$qr = @pg_query($q);
			 	if ($qr && @pg_affected_rows($qr)>0)
			 	{
					$goodctr++;
					$account_type = lookUpTableReturnValue('x','account_type','account_type_id','account_type',$temp['account_type_id']); 
					$goodcontents .= "<tr  class=\"gridRow\"><td align='right'>$goodctr.</td>
								<td><input type='checkbox' name='delete[]' id='g$ctr' value='$ctr'></td>
								<td>".$temp['account_code']."</td>
								<td>".$temp['account']."</td>
								<td>$account_type</td>
								</tr>";
				}
				else
				{
					if (!$qr)
					{
						$error = pg_errormessage().$q;
					}
					else
					{
						$error = 'NOT ADDED';
					}
					$badctr++;
					$account_type = lookUpTableReturnValue('x','account_type','account_type_id','account_type',$temp['account_type_id']); 
					$badcontents .= "<tr class=\"gridRow\"><td align='right'>$badctr.</td>
								<td><input type='checkbox' name='delete[]' id='b$ctr' value='$ctr'></td>
								<td>".$temp['account_code']."</td>
								<td>".$temp['account']."</td>
								<td>".$error."</td>
								</tr>";

				}

			
		}		
		$contents = "<table width='100%' cellpadding='0' cellspacing='0'>";
		$contents .= "<tr><td colspan='5' bgColor='#DADADA'>Good Records</td></tr>";
		$contents .= $goodcontents;
		
		$contents .= "<tr><td colspan='5' bgColor='#FF9999'>Bad Records</td></tr>";
		$contents .= $badcontents;
			
		$contents .="</table>";
		glayer('grid',$contents);
	
		galert($goodctr ." Good Information and ".$badctr." Bad Information ('NOT Saved')\n     Please Check Information ");		
		return done();
	}

	$xajax->registerFunction('accountUpload');
	function accountUpload($form) 
	{
		global $SYSCONF, $aRecs;

			$filename = $form['filename'];

		if ($filename == '')
		{
			galert('No Filename specified...'.$form['filename']);
			return done();
		}

		$fields = array('account_id','account_code','cardno','cardname','account','account_status',
							'date_applied','date_expiry','date_birth','gender','civil_status','telno',
							'guarantor_id','credit_limit','bond','remarks',
							'account_class_id','account_type_id','address','branch_id');


			
		if (!file_exists($filename))
		{
			galert('File [ '.$filename.' ] Does NOT exists...');
		}
		else
		{
			$fo = @fopen($filename,'r');
			$contents = '';
			$aRecs = null;
		  	while (!feof($fo)) 
		  	{
  				$contents = fgets($fo, 8192);
  				if (strlen($contents) < 15) continue;

  				$temp = explode('||',$contents);
				$dummy = null;
				$dummy = array();
				for($c=0;$c<count($fields);$c++)
				{
					$dummy[$fields[$c]] = htmlentities($temp[$c]);
			 	}
			 	
				$aRecs[] = $dummy;
			}		
			$contents = "<table width='100%' cellpadding='0' cellspacing='0'>";
			$ctr=0;
			foreach ($aRecs as $temp)
			{
				$ctr++;
				$account_type = lookUpTableReturnValue('x','account_type','account_type_id','account_type',$temp['account_type_id']); 
				$contents .= "<tr class=\"gridRow\"><td align='right'>$ctr.</td>
								<td><input type='checkbox' name='delete[]' id='d$ctr' value='$ctr'></td>
								<td>".$temp['account_code']."</td>
								<td>".$temp['account']."</td>
								<td>$account_type</td>
								</tr>";
			}
			$contents .="</table>";
			glayer('grid',$contents);
		}
		return done();
}
	$xajax->registerFunction('download');
	function download($form) 
	{
		$date = $form['date'];
		$filename = $form['filename'];
		
		if ($date == '')
		{
			glayer('grid.layer','No date specified...'.$form['date']);
			return done();
		}
		
		if ($filename == '')
		{
			glayer('grid.layer','No Filename specified...'.$form['filename']);
			return done();
		}
		$mdate = mdy2ymd($date);
		$tables = currTables($mdate);
		$sales_header = $tables['sales_header'];
		$sales_detail = $tables['sales_detail'];
		$sales_tender = $tables['sales_tender'];

		
		$q = "select 
				stock.barcode,
				$sales_detail.qty,
				$sales_detail.price,
				$sales_detail.amount,
				stock.ccategory,
				stock.category_id
				
			from
				stock,
				$sales_header,
				$sales_detail
			where
				stock.stock_id=$sales_detail.stock_id and
				$sales_header.sales_header_id=$sales_detail.sales_header_id and
				$sales_header.status!='V' and
				$sales_header.date='$mdate'";
				
//				invoice= '00482525' and terminal='29'";
			
		$qr = @pg_query($q);
		if (!$qr)
		{
			glayer('grid.layer','Error querying...'.pg_errormessage());
			return done();
		}
		if (@pg_num_rows($qr) <= 0)
		{
			glayer('grid.layer','No DATA Found for this transaction date...');
			return done();
    }
		
		$d = explode('-',$mdate);
		$d1 = substr($d[0],2,2).$d[1].$d[2];
		$ln = "\"H\",\"POS MACHS\",\"$d1\",1,\"       \",\"                     \",0"."\n";
		
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
			$qty = round($r->qty,3);	
			$ln .= "\"D\",".
				"\"".adjustSize($r->barcode,12)."\",".
				"\"".adjustSize($category_code,4)."\",".
				"\"A2\",".
				"\"".space(10)."\",".
				$qty.",".
				"\"".space(10)."\",".
				$r->amount.",".
				$r->price."\n";
				
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
	$xajax->registerFunction('databackup');
	function databackup($form) 
	{
		
		$date = $form['date'];
		$filename = "/data/databackup/".$form['filename'];
		
		if ($filename == '')
		{
			glayer('message.layer','No Filename specified...'.$form['filename']);
			return done();
		}
		else
		{
			
			//$s = system("pg_dump -d la > $filename -U pgsql");
			$s = system("databackup.sh");
		}
		if (file_exists($filename))
		{
  			$t = "<table align=\"center\">";
  		
  			$t .= "<tr><td><a href='$fl'><h3>$filename</a></h3></td></tr>";
  			$t .="</table>";
  		
	 		glayer('grid.layer',$t);
					
		 	glayer('message.layer','Finished Processing data backup...Please Click File To Download...');
		}
		else
		{
			glayer('message.layer','Unable to create Backup Data into Target Destination '.$s);
		}
		return done();
	}
      //$xajax->debugOn();
$xajax->processRequests();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<head>
<title>M1 Point of Sale System - Systems Administration Module</title>
<?php $xajax->printJavascript('../'); // output	the xajax javascript. This must	be called between the head tags	?>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<STYLE TYPE="text/css">
<!--
	A:link  {text-decoration: none;}
	A:hover {text-decoration:;font-weight: bold;}
	A:active {text-decoration: none;}
	A:visited {text-decoration: none;}
	A:visited:active {text-decoration: underline; }
	A:visited:hover {text-decoration: underline;}
	div.cats{position: absolute;right: 10;top: 80;}	

  .normal { background-color: #CCCCCC ; font-weight:; color:#000000 }
  .highlight { background-color: #FFFFFF; color:#FFFFFF; font-weight:bold; }
  
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
	xajax.$('wait.layer').style.display = 'block';
	xajax.$('wait.layer').innerHTML = $message+"<br><img src='../graphics/wait.gif'>";
	return;
}

</script>
<link rel="stylesheet" href="../css/bubble-tooltip.css" media="screen">
<script type="text/javascript" src="../js/bubble-tooltip.js"></script>


</head>
<html>
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
    <td width="38%" align="center"><font size="2">User: 
      <?= $ADMIN['name'];?>
      [<a href="../?p=logout"> Logout</a> ]<br>
      <?= date('F d,Y');?>
     <br>
      System Configuration<?=($SYSCONF['DATABASE'] !=''? 'DB: '.$SYSCONF['DATABASE'] : '');?>  </font>
	</td>
  </tr>
</table>
<table width="100%" border="0" cellpadding="1" cellspacing="1" bgcolor="#999999">
  <tr bgcolor="#CCCCCC"   background="../graphics/header.gif"> 
    <td width="9%" height="19" align="center"> 
      <a accesskey="T" href="?p=tender" class="altLink"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Tender</font></a></td>
    <td width="9%" align="center"> 
      <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a accesskey="A" href="?p=area" class="altLink">Area</a></font></td>
    <td width="9%" align="center"> 
      <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a accesskey="T" href="?p=discount" class="altLink">Discount</a></font></td>
    <td width="9%" align="center"> 
      <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a accesskey="M" href="?p=terminal" class="altLink">Terminals</a></font></td>
    <td width="9%" align="center"> 
      <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a accesskey="U" href="?p=backend.menu.admin" class="altLink">Users</a></font></td>
    <td width="9%" align="center"> 
      <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a accesskey="C" href="?p=sysconfig" class="altLink">SysConfig</a></font></td>
    <td width="9%" align="center"> 
      <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a accesskey="R" href="?p=backend.menu.report" class="altLink">Reports</a></font></td>
    <td width="9%" align="center"> 
      <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a accesskey="R" href="?p=backend.menu.utils" class="altLink">Utilities</a></font></td>
    <td width="9%" align="center"> 
      <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a accesskey="D" href="?p=backend.menu.setup" class="altLink">Setup</a></font></td>
    <td width="9%" align="center"> 
      <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a  accesskey="H" href="../?" class="altLink">Home</a></font></td>
    <td width="10%">&nbsp;</td>
  </tr>
</table>
<?php
include_once('../lib/library.js.php');

if ($SYSCONF['IP'] == '' || $ADMIN['sessionid'] == '')
{
	echo "<script>window.location='../'</script>";
	exit;
}

if (!in_array($ADMIN['usergroup'], array('A')))
{
	echo "<div align=center><h2>Permission Denied...</h2></div>";
	exit;
}
if ($p != '')
{
	include_once("$p.php");
}
?>
  <div id="message.layer" align="center" style="position:absolute; top:35%; left:25%; background-color=#FFCCC"></div>
  <div id="wait.layer" align="center"  style="position:absolute;left: 40%; top: 50%; background-color: #CCCCCC; layer-background-color: #FFFFFF; border: 1px none #000000;"></div>

</body>
</html>
