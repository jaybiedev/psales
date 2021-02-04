<?
function round2($num)
{
	$n = round($num,2)*100;
	$l = substr($n,-1);
	if ($l<3)
	{
		$l = 0;
		$n1 = substr($n,0,strlen($n)-1).$l;
		$n2 = $n1/100;
	}
	elseif ($l<8) 
	{
		$l = 5;
		$n1 = substr($n,0,strlen($n)-1).$l;
		$n2 = $n1/100;
	}
	else
	{
		$c = substr($n,-2);
		$l = (substr($c,0,1)+1)*10;
		
		$ll = $l/100;
		$n2 = substr($n,0,strlen($n)-2)+$ll;
	}
	
	return $n2;
	
}

function readBarcode($b)
{
	global $SYSCONF;
	
	$barcode = '';
	if ($SYSCONF['STRIPLEADINGZERO']=='Y')
	{
		$prefix = substr($b,0,1);
		if ($SYSCONF['BARCODEPREFIX'] == strtoupper($prefix))
		{
			$b1 = substr($b,1,strlen($b)-1);
			$b = ltrim($b1,'\0');
		}
		else
		{
			$b1=$b;	
		}
		$barcode=$b;
	}
	else
	{
		$barcode = $b;
	}
	return $barcode;
}

function sysconf($sysconfig)
{
	global $SYSCONF;
	if ($SYSCONF['BUSINESS_NAME'] == '') return '';
	$str='';
	foreach ($SYSCONF as $systmp)
	{
		if ($systmp['sysconfig'] == $sysconfig)
		{
			$str = $systmp['value'];
			break;
		}
	}
	
	return $str;
}

function terminal($terminal)
{
	$term = null;
	$term = array();
	$Q = "select * from terminal where value='$terminal' and definition='TERMINAL'";
	$QR = @pg_query($Q);
	if (pg_num_rows($QR) > 0)
	{

		$R = @pg_fetch_object($QR);
		$Q = "select * from terminal where ip='$R->ip'";
		$QR = @pg_query($Q);
		while ($R = @pg_fetch_assoc($QR))
		{
			
			$term[$R['definition']] = $R['value'];
		}
	}
	return $term;
}
function audit($module, $sql, $admin_id, $remark, $row_id)
{
	$d = date('Y-m-d');
	$Q = "insert into audit 
					(date, module,  admin_id, remark, row_id)
				values
					('$d','$module', '$admin_id', '$remark', '$row_id')";
	$QR = @pg_query($Q);		

	if (!$QR && (pg_result_error() == '1064' or pg_result_error() == '1146'))
	{
		$c = "CREATE TABLE audit (
				  audit_id bigint(20) NOT NULL AUTO_INCREMENT,
				  date date,
				  module varchar(25) collate latin1_general_ci default NULL,
				  dbsql blob,
				  admin_id int(11) default NULL,
				  remark blob,
				  row_id bigint(20) default NULL,
				  PRIMARY KEY  (audit_id),
				  KEY module (module,row_id))";
		@pg_query($c);	
		$QR = @pg_query($Q);
	}	

	if (!$QR)
	{
		return 0;
	}		
	else
	{
		return true;
	}	
}

function number_format2($n,$d)
{
	if ($n == 0)
		return '';
	else
		return number_format($n,$d);
}

function doPrint($pln)
{
	$serverPort	= $_SERVER['REMOTE_ADDR'];	
	$fp = fsockopen("udp://$serverPort", 5003, $errno, $errstr, 10) or die("Can't connect");
	 if (!$fp) {
	     echo "$errstr ($errno)<br>\n";
	 }
	 else
	 {	
		fputs($fp,$pln);
		fputs($fp,"eof");
        	fclose ($fp);
        	delay(300);
	}
}
function nPrinter($pln, $printType=NULL, $dest=NULL)
{
	global $SYSCONF;

	if ($printType == '')
	{
		$printType=$SYSCONF['REPORT_PRINTER_TYPE'];
	}
	if ($dest == '')
	{
		$dest=$SYSCONF['REPORT_PRINTER_DEST'];
	}
	if ($dest == '')
	{
		$dest=$_SERVER['REMOTE_ADDR'];
	}
	
	if ($printType=='NONE' || $printType=='')
		return;

	$rmsg='';
	if ($printType == 'UDP DRAFT'|| $printType == 'DRAFT')
	{
		//$serverPort	= $_SERVER['REMOTE_ADDR'];	
		$fp = @fsockopen("udp://$dest", 5003, $errno, $errstr, 10) or die("Can't connect");
		 if (!$fp) {
		     echo "$errstr ($errno)<br>\n";
		 }
		 else
		 {	
			fputs($fp,$pln);
			fputs($fp,"eof");
	        	fclose ($fp);
	        	sleep(1);
		}
	}
	elseif ($printType=='LINUX LP Printer')
	{
		if ($pln == '<drawer>') $pln = chr(27)."p0";
		if ($pln == '<cutterm>') $pln = chr(27)."m";

	   $m = "tcp://$dest";
		$fp = @fsockopen("tcp://$dest", 5003, $errno, $errstr, 10);
		if (!$fp) 
		{
		 	$rmsg = "No socket connection to printer.";
		}
		else
		{	
			fputs($fp,$pln);
	      fclose ($fp);
	      if (strlen($pln)>100)
	      {
	      	sleep(1);
	      }

		}
  }
	elseif ($printType=='LINUX LP Printer -- LOCAL')
	{
		$file ="/tmp/".rand();
		$fl = fopen($file,"w+");
		if (!$fl)
		{
			$rmsg="Unable To Open Temporary Printing File...";
		}
		else
		{
		    if (fwrite($fl, $pln) === FALSE) 
		    {
		    	fclose($fl);
			    $rmsg="Unable To Find [ $dest ] Printing Device...";
		    }
		    else
		    {
		    	fclose($fl);
		    	/*
		    	if (!is_null($dest))
		    	{
		    		system("lp -d $dest $file",$msg);
		    		//system("lp -d $dest $file");
		    	}
		    	else
		    	{
		    		system("lp $file");
		    	}	
		    	system("rm $file");
		    	*/
		    }	
		    
		}
	}
	elseif ($printType=='GRAPHICS')
	{
		echo "<form name='form1'>";
		echo "<input type='hidden' name='print_area' cols='110' rows='25' readonly value='$pln'>";
		echo "</form>";
		echo "<script>printIframe(form1.print_area)</script>";
	}
	elseif ($printType == 'PHP Printer(DRAFT)')
	{
		$handle = printer_open($dest);
		if (!handle)
		{
			$rmsg="Unable to Open Port...";
			echo "Error Printing...".$dest;
			exit;
		}
		else
		{
			printer_set_option($handle, PRINTER_MODE, raw);
			if (!@printer_write($handle, $pln))
			{
				$rmsg="Unable to Write To Port...".$handle;
				//@printer_write($handle, $pln);
				echo "Error Printing...".$dest;
				exit;
			}
			else
			{
				$rmsg = "";
			}
			printer_close($handle);
		}	
	}	
	else //if ($printType == 'PHP Printer(TEXT)') //graphics
	{
		$handle = printer_open($dest);
		if (!handle)
		{
			$rmsg="Unable to Open Port...";
		}
		else
		{
			@printer_set_option($handle, PRINTER_MODE, text);
			if (!@printer_write($handle, $pln))
			{
				$rmsg="Unable to Write To Port...".$handle;
				printer_write($handle, $pln);
			}
			else
			{
				$rmsg = "Printed";
			}
			printer_close($handle);
		}	
	}	
	return $rmsg;
}


function space($sp)
{
	$s = str_repeat(" ",$sp);
	return $s;
}

function adjustSize($s, $size)
{
	if (strlen($s) > $size)
	{
		$s = substr($s,0,$size);
	}
	else
	{
		$s = str_pad($s,$size);
	}
	
	return $s;
}


function center($s,$size)
{
	$s = str_pad($s,$size," ",STR_PAD_BOTH);
	return $s;
}

function adjustRight($s,$size)
{
	$s = str_pad($s,$size," ",STR_PAD_LEFT);
	return $s;
}

function udate($ymd)
{
	$mdy = ymd2mdy($ymd);
	$ud  = substr($mdy,0,6).substr($mdy,8,10);
	return $ud;
}


function ymd2mdy($ymd)
{
	$a = explode("-",$ymd);
	$sd = "$a[1]/$a[2]/$a[0]";
	if ($sd == '//') $sd='';
	return $sd;
}

function mdy2ymd($mdy)
{
	$a = explode("/",$mdy);
	return "$a[2]-$a[0]-$a[1]";
}
function dateDiff($d1,$d2)
{
	// returns the number of days between $d2 and $d1
	//use date format mm/dd/yyyy

	if ($d1=='' || $d1 == '00/00/0000' || $d2=='' || $d2 == '00/00/0000') return 0;
	$ad1 = explode('/',$d1);
	$ad2 = explode('/',$d2);
	
	$month = $ad1[0];
	$year = $ad1[2];

	$days= 0;
	while (true)
	{

		if ($month>$ad2[0] && $year >= $ad2[2])
		{
			break;
		}
		elseif ($month == $ad1[0]&& $ad1[0]==$ad2[0] && $year == $ad2[2])
		{
			$day += $ad2[0] - $ad1[0];
			if ($day < 0) $day=0;
			$days += $day;
			
		}
		elseif ($month == $ad2[0] && $year == $ad2[2])
		{
			$days += $ad2[1];
		}
		else
		{
			$days += date('d',mktime(0,0,0,$month+1,0,$year));
		}
		
		$month++;
		if ($month>12)
		{
			$month=1;
			$year++;
		}
	
	}
	return $days;
}


function message($message)
{
?>
 <div align="center" id="messageLayer"style="position:absolute; width:70%; height:25; z-index:100; overflow: auto; left: 10%; top: 95%; background-color: #FFFFFF; layer-background-color: #FFFFFF; border: 1px solid #CCCC00;" onClick="this.style.visibility='hidden'">
 <font color="#FF0000" face="Geneva, Arial, Helvetica, san-serif" size="2"><b><?=$message;?></b>
  </font> </div>
 <?
}

function message1($message)
{
?>
 <div align="center"><font color="#FF0000" face="Geneva, Arial, Helvetica, san-serif" size="2"><b><?=$message;?></b>
  </font> </div>
 <?
}

function msgBox($message)
{
	echo "<script>  alert('$message')</script>";
}

function chkRights2($module,$rights, $admin_id)
{
  global $ADMIN;
  $validate_string=" ";

  if ($ADMIN['usergroup']=='A')
  {
  	return true;
  }
  if ($rights=="madd") 
  	$validate_string=md5("Y".$admin_id."100".$module);
  elseif ($rights=="medit") 
  	$validate_string=md5("Y".$admin_id."250".$module);
  elseif ($rights=="mdelete") 
  	$validate_string=md5("Y".$admin_id."400".$module);
  elseif ($rights=="mview") 
  	$validate_string=md5("Y".$admin_id."550".$module);

 $Q = "select * from adminrights, module 
 	where 
		module.module_id=adminrights.module_id and 
 		module.module='$module' and 
 		$rights='$validate_string' and 
 		admin_id='$admin_id'";
 
 $Qr = pg_query($Q);
 if ($Qr && pg_num_rows($Qr)>0)
 	return true;
 else
 	return false;
}

function chkRights($rights)
{
 global $admin;
 $rights = strtoupper($rights);
 return (strpos(strtoupper($admin->rights),$rights) !== false);
}
function begin()
{
  if ($SYSCONF['DB_ENGINE'] = 'pgsql')
  {
  	$_qr = pg_query("begin transaction");
  }
  elseif ($SYSCONF['DB_ENGINE'] = 'myinnodb')
  {
  	$_qr = mysql_query("begin transaction");
  }
  elseif ($SYSCONF['DB_ENGINE'] = 'myisam')
  {
    $_qr =1;
  } 
	return $_qr;
}
function rollback()
{
  global $SYSCONF;
  if ($SYSCONF['DB_ENGINE'] = 'pgsql')
  {
  	$_qr = pg_query("rollback transaction");
  }
  elseif ($SYSCONF['DB_ENGINE'] = 'myinnodb')
  {
  	$_qr = mysql_query("rollback transaction");
  }
  elseif ($SYSCONF['DB_ENGINE'] = 'myisam')
  {
    $_qr =1;
  } 
	return $_qr;
}
function commit()
{
  global $SYSCONF;
  if ($SYSCONF['DB_ENGINE'] = 'pgsql')
  {
  	$_qr = pg_query("commit transaction");
  }
  elseif ($SYSCONF['DB_ENGINE'] = 'myinnodb')
  {
  	$_qr = mysql_query("commit transaction");
  }
  elseif ($SYSCONF['DB_ENGINE'] = 'myisam')
  {
    $_qr =1;
  } 
	return $_qr;
}
function query($str)
{
  global $SYSCONF;
  if ($SYSCONF['DB_ENGINE'] = 'pgsql')
  {
	 $_qr = @pg_query($str);
	}
  elseif ($SYSCONF['DB_ENGINE'] = 'myisam' || $SYSCONF['DB_ENGINE'] = 'myinnodb')
  {
	 $_qr = @mysql_query($str);
  }	
  if (!$_qr)
	 return 0;
  else
	 return $_qr;
}

function fetch_assoc($q)
 {
  global $SYSCONF;
  if ($SYSCONF['DB_ENGINE'] = 'pgsql')
  {
     $qR = @pg_query($q);
 	  $R = @pg_fetch_assoc($qR);
 	}  
 	elseif (substr($SYSCONF['DB_ENGINE'],0,2)=='my')
  {
     $qR = @mysql_query($q) or message("Error query. ".mysql_error());
 	  $R = @mysql_fetch_assoc($qR);
  }  
 	return $R;
 }

 function fetch_object($q)
 {
  global $SYSCONF;
  if ($SYSCONF['DB_ENGINE'] = 'pgsql')
  {
    $qR = @pg_query($q) or message("Error query. ".pg_errormessage());
 	  $R = @pg_fetch_object($qR);
 	}  
 	elseif (substr($SYSCONF['DB_ENGINE'],0,2)=='my')
  {
    $qR = @mysql_query($q) or message("Error query. ".mysql_error());
 	  $R = @mysql_fetch_object($qR);
  }  
 	return $R;
 }
 function qr_object($qR)
 {
 //* qR is already a resource object
  global $SYSCONF;
  if ($SYSCONF['DB_ENGINE'] = 'pgsql')
  {
 	  $R = @pg_fetch_object($qR);
 	}  
 	elseif (substr($SYSCONF['DB_ENGINE'],0,2)=='my')
  {
   	  $R = @mysql_fetch_object($qR);
  }  
 	return $R;
 }
function qr_assoc($qR)
 {
  //* $qR is already a resource object
  global $SYSCONF;
  if ($SYSCONF['DB_ENGINE'] = 'pgsql')
  {
 	  $R = @pg_fetch_assoc($qR);
 	}  
 	elseif (substr($SYSCONF['DB_ENGINE'],0,2)=='my')
  {
 	  $R = @mysql_fetch_assoc($qR);
  }  
 	return $R;
 }

function db_error()
{
  global $SYSCONF;
  if ($SYSCONF['DB_ENGINE'] = 'pgsql')
  {
    $e = pg_errormessage();
 	}  
 	elseif (substr($SYSCONF['DB_ENGINE'],0,2)=='my')
  {
    $e = mysql_error();
  }  
  return $e;
}

function db_insert_id($table)
{
  global $SYSCONF;
  $id='';
  if ($SYSCONF['DB_ENGINE'] = 'pgsql')
  {
  	  $t = explode('.',$table);
	  if (count($t)>1)
	  {
	  	$schema=$t[0];
	  	$tablename = $t[1];
		$seqname = $t[1];
	  }
	  else
	  {
	  	$schema='public';
	  	$tablename = $t[0];
		$seqname = $t[0];
	  }
	  
	  if (in_array($tablename, array('sales_header','sales_detail','sales_tender')))
	  {
	  	$tables = $SYSCONF['tables'];
		$schematablename = $tables[$table];	
  	  	$t = explode('.',$schematablename);
	  	if (count($t)>1)
	  	{
	  		$schema=$t[0];
	  		$seqname = $tablename;
	  		$tablename = $t[1];
	  	}
	  	else
	  	{
	  		$schema='public';
	  		$seqname = $tablename;
	  		$tablename = $schematablename;
	  	}
	  }
      $seq = $schema.'.'.$tablename.'_'.$seqname.'_id_seq';  //::text';
	  
	   //galert($seq);return;
      $Q = "select currval('".$seq."'::text)";
		$QR = pg_query($Q) or die (pg_errormessage());
		$R = pg_fetch_object($QR);
		$id = $R->currval;
  }
 	elseif (substr($SYSCONF['DB_ENGINE'],0,2)=='my')
  {
    $id = mysql_insert_id();
  }  
  return $id;
}

function lookUpTableReturnValue($name,$table,$keyfield,$valuefield,$value)
 {
  global $o;
  $str = "\n\t<select name=\"$name\" style=\"border:1px solid black\" >";
  $q = "select * from $table where $keyfield = '$value'";
  $qR = @pg_query($q);
  if (@pg_num_rows($qR) == 0)
  {
  	return "No Record";
  }
  else
  {
  	$r = pg_fetch_assoc($qR);
  	return $r[$valuefield];
  }
 }

 function lookUpAssoc($name,$arr,$value)
 {
  $str = "\n\t<select name=\"$name\" style=\"border:1px solid black\">";
  $ctr = count($arr);
  while (list ($key, $val) = each ($arr))
  {
   if ($val == $value)
   {
    $str .= "\n\t\t<option value=\"$val\" selected>$key</option>";
   }
   else
   {
    $str .= "\n\t\t<option value=\"$val\">$key</option>";
   }
  }
  $str .= "\n\t</select>";
  return $str;
 }

?>
