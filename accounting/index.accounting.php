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

	$xajax->registerFunction('okReportForm');
	$xajax->registerFunction('loadReportForm');
	$xajax->registerFunction('editReportForm');
	$xajax->registerFunction('saveReportForm');
	$xajax->registerFunction('deleteReportForm');
	$xajax->registerFunction('upReportForm');
	$xajax->registerFunction('downReportForm');
	$xajax->registerFunction('searchGchartReportForm');
	$xajax->registerFunction('selectGchartReportForm');
	
	//$xajax->debugOn();
	include_once('xajax.accounting.php');


	$xajax->registerFunction('vBarcode');
	function vBarcode($form)
	{
		global $ADMIN, $astock;
		$barcode = $form['barcode'];
		$q .= "select * from stock where barcode = '$barcode'";
		$qr = @pg_query($q);
		$fnd = 0;
		if (!$qr)
		{
			galert("Error Verifying Barcode...".$q);
		}			
		if (@pg_num_rows($qr)>0)
		{
			$r = @pg_fetch_object($qr);
			if ($r->stock_id != $astock['stock_id'])
			{
				galert("WARNING: ".$ADMIN['name'].", Barcode Already Exist for: \n".$r->stock);
				$fnd=1;
			}
		}
		if ($fnd == 1)
		{
			gset('stock','');
			gset('stock_description','');
			$focus = "document.getElementById('barcode').focus()";
		}
		else
		{
			$focus = "document.getElementById('casecode').focus()";
		}			
		gscript($focus);
		return done();		
	}
	
	$xajax->registerFunction('grid');
	function grid($key)
	{
			global $aPHYC;

			$details = '';
			$tmp_table = $aPHYC['tmp_table'];
			$ctr = $aPHYC['ctr'];

			if ($key == 'DOWN')
			{
				$aPHYC['start'] += 14;
				$ctr-=1;
			}
			else
			{
				$ctr -= 29;
				$aPHYC['start'] -= 14;
				if ($aPHYC['start'] < 0) 
				{
					$aPHYC['start'] = 0;
				}
				if ($ctr<0) $ctr=0;
			}
			$q = "select 	tmp.stock_id,
								tmp.case_qty,
								tmp.unit_qty,
								tmp.cost3,
								tmp.stockledger_id, 
								stock.stock, 
								stock.stock_description, 
								stock.barcode,
								stock.unit1,
								stock.fraction3
				from 
								$tmp_table as tmp,
								stock
				where
								stock.stock_id = tmp.stock_id";
			
			if ($sortby == '' or $sortby == 'barcode')
			{
				$q .= " order by	stock.barcode";
			}
			else
			{
				$q .= " order by stock.stock ";
			}
			$q .= " offset ".$aPHYC['start']." limit 15";
			$qr = @pg_query($q) or message1(pg_errormessage());
			
			while ($temp = @pg_fetch_assoc($qr))
			{

				$ctr++;

				if ($temp['stock_description'] == '')
				{
					$stock = $temp['stock'];
				}
				else
				{
					$stock=$temp['stock_description'];
				}
				$previous_id = $ctr-1;
				$next_id = $ctr+1;
				

				$href = "javascript:SL('".$temp['stock_id']."','".$temp['barcode']."','".addslashes($stock)."','".$temp['case_qty']."','".$temp['unit_qty']."','".$temp['cost3']."','".$ctr."')";
				//$href = "javascript:v($ctr)";
				
				$details .= "<a href=\"$href\">".adjustRight($ctr,7).'. '.
								"<input type='checkbox' name='delete[]' id='a".$temp['stock_id']."' value = '".$temp['stockledger_id']."' >".
								adjustSize($temp['barcode'],16).' '.
								adjustSize($stock,48).'  '.
								adjustSize($temp['fraction3'],4).' '.
								adjustRight($temp['case_qty'],9).' '.
								adjustRight($temp['unit_qty'],9).' '.
								adjustRight(number_format($temp['cost3'],2),10).' '.
								"</a>\n";
			}
			
			$aPHYC['ctr'] = $ctr;
			glayer(gridLayer,"<pre>".$details."</pre>");
			return done();	
	}

	$xajax->registerFunction('pc_select_id');
	function pc_select_id($sid) 
	{
			//--select from browse
			
			$q = "select *
					from
						stock
					where
						stock_id='$sid' and
						enable='Y'";

			$qr = @pg_query($q);
			if (!$qr)
			{
				glalert("Error Query:". pg_errormessage());
			}
			elseif (@pg_num_rows($qr) == 0)
			{
				galert(" Item NOT found...");
			}
			else
			{
				$r = @pg_fetch_assoc($qr);
				$asid = $r;
				$found = 1;
			}
			if ($found == 1)
			{
				pc_select($asid);
			}
						
		$focus = "document.getElementById('icase_qty').focus()";
		gscript($focus);
		return done();
	}

	$xajax->registerFunction('pc_print');
	function pc_print($form) 
	{
		global $aPHYC;
		hide_layer('printLayer');
			$total_cost= 0;
			$table = $aPHYC['tmp_table'];
			
			$q = "select 
							sum(tmp.cost3*tmp.case_qty + (tmp.cost3/stock.fraction3) * tmp.unit_qty) as total_cost
					from 
							$table as tmp,
							stock
					where
							stock.stock_id =tmp.stock_id";
			$qr = @pg_query($q);
			
			$r = @pg_fetch_object($qr);
							 
			$message = "<br><br><br>&nbsp; &nbsp; Total Cost of Inventory for this entry <br>&nbsp;".
							" &nbsp; &nbsp; (as of this Period) is : ".number_format($r->total_cost,2)."\n\n";
							
			show_layer('browsePLULayer');
			glayer('innerPLULayer',$message);
		return done();
	}
	
	$xajax->registerFunction('pc_search');
	function pc_search($form) 
	{
		$searchby = $form['searchby'];
		$searchkey = $form['searchkey'];

		$found = 0;
		if ($searchby == 'stock')
		{
			pc_searchStock($searchkey);
		}
		else
		{
			$q = "select *
					from
						stock
					where
						$searchby='$searchkey' and
						enable='Y'";

			$qr = @pg_query($q);
			if (!$qr)
			{
				glalert("Error Query:". pg_errormessage());
			}
			elseif (@pg_num_rows($qr) == 0)
			{
				galert(" Item NOT found...");
			}
			else
			{
				$r = @pg_fetch_assoc($qr);
				$asid = $r;
				$found = 1;
			}
			if ($found == 1)
			{
				pc_select($asid);
			}
		}
		return done();
	}

	//$xajax->registerFunction('pc_searchStock');
	function pc_searchStock($searchkey) 
	{
        	$m = "<table width=\"99%\" height=\"1%\" border=\"0\" align=\"center\" cellpadding=\"2\" cellspacing=\"1\" bgcolor=\"#EFEFEF\">
	        	  <tr> 
	            <td width=\"9%\"><strong><font size=\"2\" face=\"Verdana, Arial, Helvetica, sans-serif\">#</font></strong></td>
    	          <td width=\"20%\"><strong><font size=\"2\" face=\"Verdana, Arial, Helvetica, sans-serif\">Barcode</font></strong></td>
        	      <td width=\"71%\"><strong><font size=\"2\" face=\"Verdana, Arial, Helvetica, sans-serif\">Item 
            	    Description </font></strong></td>
          	</tr>";
		  	$q = "select * from stock where stock ilike '%$searchkey%' and 
  					enable='Y' order by stock_description  offset 0 limit 35";

  			$qr = @pg_query($q) ;
			if (!$qr)
			{
				galert(pg_errormessage());
			}
			elseif (@pg_num_rows($qr) == 0)
			{
				galert(" Search NOT found...");
				return;
			}
	  		$ctr=0;
  			while ($r = @pg_fetch_object($qr))
  			{
  				$ctr++;
				$href ="javascript: wait('Loading data...');vs('$r->stock_id')";
				if ($r->stock_description != '')
				{
					$stock = $r->stock_description;
				}
				else
				{
					$stock = $r->stock;
				}
          
		  		$m .= "<tr bgcolor=\"#FFFFFF\" onMouseOver=\"bgColor='#FFFFCC'\" onMouseOut=\"bgColor='#FFFFFF'\"> 
		  				<td align=\"right\"><font size=\"2\" face=\"Verdana, Arial, Helvetica, sans-serif\"> 
             			$ctr. </font></td>
            			<td><font size=\"2\" face=\"Verdana, Arial, Helvetica, sans-serif\"> <a href=\" $href\"> 
              			$r->barcode</a></font></td>
              			<td><font size=\"2\" face=\"Verdana, Arial, Helvetica, sans-serif\"> 
                		<a href=\"$href\"> $stock</a> </font></td>
	          		</tr>";

	  		}
     		$m .= "   </table>";
			show_layer('browsePLULayer');
	 		glayer('innerPLULayer',$m);
	}
	
	$xajax->registerFunction('pc_select');
	function pc_select($asid) 
	{
		global $aPHYC;
		hide_layer('browsePLULayer');

		$c=0;
		$sid = $asid['stock_id'];
		$found = 0;
		$q = "select 
						tmp.stockledger_id,
						tmp.stock_id,
						tmp.case_qty,
						tmp.unit_qty,
						tmp.cost3,
						stock.stock,
						stock.barcode,
						stock.stock_description,
						stock.fraction3,
						stock.unit1,
						stock.unit3
				 from ".
				 		$aPHYC['tmp_table']." as tmp ,
				 		stock
				  where 
				  		stock.stock_id = tmp.stock_id and
				  		tmp.stock_id = '$sid'";
		$qr = @pg_query($q);
		if (!$qr) galert("Error querying details...".pg_errormessage().$q);
		if (@pg_num_rows($qr) > 0)
		{
		
				$temp = @pg_fetch_assoc($qr);
				$ctr = $temp['stockledger_id'];
				$stock = $temp['stock'];
				if ($temp['stock_description'] != '')
				{
					$stock = $temp['stock_description'];
				}
				$searchkey = $temp['barcode']; 
				$stock_id = $temp['stock_id'];
				$icase_qty = $temp['case_qty'] ;
				$iunit_qty = $temp['unit_qty'] ;
				$icost3 = $temp['cost3'];

		}
		else
		{
			$ctr='';
			$stock = $asid['stock'];
			if (strlen(trim($asid['stock_description'])) > 10)
			{
				$stock = $asid['stock_description'];
			}

			$searchkey = $asid['barcode']; 
			$stock_id = $asid['stock_id'];
			$icase_qty = $asid['case_qty'] ;
			$iunit_qty = $asid['unit_qty'] ;
			$icost3 = $asid['cost3']*1;
			
			if ($icost3 == 0 && $asid['fraction3']<=1)
			{
				$icost3 = $asid['cost1']*1;
			}
			
		}

		gset('ctr', $c);
		gset('searchkey', $searchkey); 
		gset('stock_id', $stock_id);
		gset('stock', $stock) ;
		gset('icase_qty', $icase_qty) ;
		gset('iunit_qty', $iunit_qty) ;
		gset('icost3', $icost3) ;
		
	}

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
<script>
<!---
function wait($message)
{
	xajax.$('message.layer').innerHTML = '';
	xajax.$('wait.layer').style.display = 'block';
	xajax.$('wait.layer').innerHTML = $message+"<br><img src='../graphics/wait.gif'>";
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
<table width="100%" border="0" bgcolor="#FFFFFF">
  <tr>
    <td width="61%"><img src="../graphics/logo.jpg"></td>
    <td width="39%" align="center"><font size="2">User: 
      <?= $ADMIN['name'];?>
      [ <a  accesskey="L" href="../?p=logout">Logout</a> ]<br>
      <?= date('F d,Y');?>
      <br>
      Accounting Module <?=($SYSCONF['DATABASE'] !=''? 'DB: '.$SYSCONF['DATABASE'] : '').' - '.$SYSCONF['BRANCH'];?></font>
    </td>
  </tr>
</table>
<table width="100%" border="0" cellpadding="1" cellspacing="1" bgcolor="#999999">
  <tr bgcolor="#CCCCCC" background="../graphics/header.gif"> 
    <td width="8%" height="19" align="center" nowrap="nowrap"> 
      <a accesskey="S" href="?p=accounting.menu.file"  class="altLink">
      <font size="2" face="Verdana, Arial, Helvetica, sans-serif">Files</font></a></td>
    <td width="8%" align="center"> 
      <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <a href="?p=accounting.menu.ap"  class="altLink">Payables</a></font></td>
    <td width="8%" align="center"> 
      <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <a href="?p=gltran"  class="altLink">Journal 
      Entry </a></font></td>
    <td width="8%" align="center"> 
    <font size="2" face="Verdana, Arial, Helvetica, sans-serif">
      <a accesskey="R" href="?p=accounting.menu.report" class="altLink">Reports</a></font></td>
    <td width="8%" align="center" > 
        <font size="2" face="Verdana, Arial, Helvetica, sans-serif">
      <a accesskey="E" href="?p=menu.setup"  class="altLink">
      Setup</a></font></td>
    <td width="8%" align="center" > 
    <font size="2" face="Verdana, Arial, Helvetica, sans-serif">
      <a  accesskey="H" href="../?"  class="altLink">Home</a></font></td>
    <td width="8%" align="center" >
    <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <a  accesskey="L" href="../?p=logout"  class="altLink">Logout</a></font></td>
    <td width="20%">&nbsp;</td>
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
	if (file_exists("$p.php"))
	{
		include_once("$p.php");
	}
	else
	{
		message1("Module Does NOT exists...");
	}
}
else
{
	if (file_exists('home.php'))
	{
		include_once('home.php');
	}
}
?>
  <div id="message.layer" align="center" style="position:virtual; top:0%; left:25%; background-color=#FFFCCC; z-index=10"></div>
  <div id="wait.layer" align="center"></div>

</body>
</html>
