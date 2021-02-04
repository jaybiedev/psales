<?
function checkDigit9($code)
{
	$ok=0;
	$sum=0;
	$cd='';
	
	for ($c=0;($c<strlen($code));$c++)
	{
		if ($c == (strlen($code)-1))
		{
			$cd = substr($code,$c,1);
		}
		else
		{
			$sum += substr($code,$c,1);
		}
	}
	
	if ($cd == ($sum%7))
	{
		$ok = 1;
	}
	return $ok;
}

function genCheckDigit9($code)
{
	$pf = '';
	for ($c=0;($c<strlen($code));$c++)
	{
		if (substr($code,$c,1)!= '0')
		{
			break;
		}
		else
		{
			$pf .= substr($code,$c,1);
		}
	}
	
	for ($c=0;($c<strlen($code));$c++)
	{
		$sum += substr($code,$c,1);
	}
	$cd = $sum%7;
	$code .= $cd;
//	$code = $pf.$code;

	return $code;
}
function level($level)
{
	$str='';
	if ($level == '0')
		$str = 'Main';
	elseif ($level == '9')
		$str = 'Detail';
	else
		$str = 'Sub'.$level;
	return $str;
}
function mclassReturn($mclass) {
 	if ($mclass=='I') return 'Income';
 	elseif ($mclass=='E') return 'Expense';
	elseif ($mclass=='A') return 'Assest';
 	elseif ($mclass=='L') return 'Liabilities';
 	elseif ($mclass=='R') return 'Retained Earnings';
 	else  return 'Invalid';
 }
 

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

function writefile($text, $new=false, $file=null)
{
	if ($file == '')
	{
	  $aip = explode('.',$_SERVER['REMOTE_ADDR']);
	  $file= '/prog/log/DOC'.$aip[3].'.txt';
	}
	if ($new == 1)
	{
   	$fo = @fopen($file,'w+');
   }
   else
   {
   	$fo = @fopen($file,'a+');
   }
   //echo " file : $file new :$new text : $text <br>";
	$w = @fwrite($fo, $text);
	@fclose($fo);
	return $w;
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
function numWord($num)
{
	$Ones[1] = "ONE";
	$Ones[2] = "TWO";
	$Ones[3] = "THREE";
	$Ones[4] = "FOUR";
	$Ones[5] = "FIVE";
	$Ones[6] = "SIX";
	$Ones[7] = "SEVEN";
	$Ones[8] = "EIGHT";
	$Ones[9] = "NINE";
	$Ones[10] = "TEN";
	$Ones[11] = "ELEVEN";
	$Ones[12] = "TWELVE";
	$Ones[13] = "THIRTEEN";
	$Ones[14] = "FOURTEEN";
	$Ones[15] = "FIFTEEN";
	$Ones[16] = "SIXTEEN";
	$Ones[17] = "SEVENTEEN";
	$Ones[18] = "EIGHTEEN";
	$Ones[19] = "NINETEEN";
	$Tens[1] = "TEN";
	$Tens[2] = "TWENTY";
	$Tens[3] = "THIRTY";
	$Tens[4] = "FORTY";
	$Tens[5] = "FIFTY";
	$Tens[6] = "SIXTY";
	$Tens[7] = "SEVENTY";
	$Tens[8] = "EIGHTY";
	$Tens[9] = "NINETY";

	$tn=0;
	$wrdn='';
	if ($num >= 1000000)
	{
	  $tn = intval($num/1000000);
	  if (tn>=1)
	  {
	    $tnh = intval($tn/100);
	    if (tnh >= 1)
	    {
	      $wrdn = $Ones[$tnh].' HUNDRED';
	    }  
	    $tno = $tn-($tnh*100);
	    $tnt = intval($tno/10);
	    if  ($tnt>1)
	    {
	       $wrdn = $wrdn + ' ' + $Tens[$tnt];
	       $nn   = $tno-$tnt*10;
	       if (nn>=1)
	       {
	         $wrdn .=  ' ' . $Ones[$nn];
	       }
	    }   
	    elseif ($tnt==1)
	    {
	       $wrdn .=  ' ' . $Ones[$tno];
	    }
	    elseif (tno>0)
	    {
	       $wrdn .=  ' ' . $Ones[$tno];
	    }   
	    $wrdn=$wrdn+' MILLION';
	  }
	}
	$nm = $num-$tn*1000000;
	
	if ($nm >= 1000)
	{
	  $tn = intval($nm/1000);

	  if ($tn>=1)
	  {
	    $tnh = intval($tn/100);
	    if ($tnh >= 1)
	    {
	      $wrdn .= ' '.$Ones[$tnh].' HUNDRED';
	    }
	    
	    $tno = $tn-($tnh*100);
	    $tnt = intval($tno/10);

	    if ($tnt>1)
	    {
	       $wrdn .= ' ' . $Tens[$tnt] ;
	       $nn   = $tno-$tnt*10;
	       if ($nn>=1)
	       {
	         $wrdn .= ' ' .$Ones[$nn];
	       }
	    }    
	    elseif ($tnt==1)
	    {
	       $wrdn .= $Ones[$tno];
	    }  
	    elseif ($tno>0)
	    {
	       $wrdn .=  ' ' . $Ones[$tno];
	    }
	    
	    $wrdn .= ' THOUSAND';

	  }
	}
	
	$tnm = $nm-$tn*1000;
	$tnh = intval($tnm/100);
	if ($tnh >= 1)
	{
	  $wrdn .= ' '.$Ones[$tnh].' HUNDRED';
	}  
	
	$tno = $tnm-($tnh*100);
	$tnt = intval($tno/10);
	
	if ($tnt>1)
	{
	    $wrdn .=  ' ' . $Tens[$tnt];
	    $nn   = $tno-$tnt*10;
	    if ($nn>=1)
	    {
	      $wrdn .= ' ' . $Ones[$nn];
	    }  
	}    
	elseif ($tnt==1)
	{
	    $wrdn .= ' '. $Ones[$tno];
	}    
	elseif (intval($tno)>0)
	{
	    $wrdn .= ' ' . $Ones[$tno];
	}    
	
	$cnts=($nm-intval($nm))*100;
	if ($cnts != 0)
	{
	  if (strlen($wrdn)<2)
	  {
	    $wrdn .= ltrim(substr($cnts,0,2)).'/100';
	  }  
	  $wrdn .= ' AND '.ltrim(substr($cnts,0,2)).'/100';
	}
return $wrdn;
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
function currTables($date)
{
	global $SYSCONF;
	
	$year = substr($date,0,4);
	$schema = "e$year"; 			//-- schema by year
	if (substr($date,0,4) < '2007')
	{
		$sales_header = 'sales_header';
		$sales_detail = 'sales_detail';
		$sales_tender = 'sales_tender';
		$stockledger  = 'stockledger';
	}
	elseif ($_SERVER['REMOTE_ADDR']=='127.0.0.1' && !file_exists('/jaybie.conf'))
	{
		$sales_header = 'offline.sales_header';
		$sales_detail = 'offline.sales_detail';
		$sales_tender = 'offline.sales_tender';
		$stockledger  = 'offline.stockledger';
	}
	elseif ($year < '2008')
	{
		$sales_header = 'sh_'.$year;
		$sales_detail = 'sd_'.$year;
		$sales_tender = 'st_'.$year;
		$stockledger  = 'sl_'.$year;
	}
	else
	{
		$sales_header = "$schema.sales_header";
		$sales_detail = "$schema.sales_detail";
		$sales_tender = "$schema.sales_tender";
		$stockledger  = "$schema.stockledger";
	}
	$aTables = null;
	$aTables = array();
	
	if ($SYSCONF['PCENTER'] != '')
	{
		//-- special tables for other profit center-- bowling
		$aTables['stock_table'] = 'stock'.$SYSCONF['PCENTER'];
		$aTables['sales_header'] = 'sh_'.$SYSCONF['PCENTER'];
		$aTables['sales_tender'] = 'st_'.$SYSCONF['PCENTER'];
		$aTables['sales_detail'] = 'sd_'.$SYSCONF['PCENTER'];
		$aTables['stockledger'] = 'sl_'.$SYSCONF['PCENTER'];	
	}
	else
	{
		$aTables['stock_table'] = 'stock';
		$aTables['sales_header'] = $sales_header;
		$aTables['sales_tender'] = $sales_tender;
		$aTables['sales_detail'] = $sales_detail;
		$aTables['stockledger'] = $stockledger;	
	}
	
	return $aTables;
}
function audit($module, $sql, $admin_id, $remark, $row_id)
{
	$d = date('Y-m-d');
	
	$dbsql = addslashes($sql);
	$Q = "insert into audit 
					(date, module,  admin_id, remark, row_id, dbsql)
				values
					('$d','$module', '$admin_id', '$remark', '$row_id','$dbsql')";
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
function transaction_type($t)
{
	$str='';
	if ($t=='C')
		$str = 'Cash';
	elseif ($t=='H')
		$str = 'Account';
	elseif ($t=='B')
		$str = 'Bank Card';
	elseif ($t=='R')
		$str = 'Sales Return';
	return $str;
		
}
function status($t)
{
	$str='';
	if ($t=='S')
		$str = 'Saved';
	elseif ($t=='P')
		$str = 'Printed';
	elseif ($t=='C')
		$str = 'Cancelled';
	elseif ($t=='D')
		$str = 'Closed';
	elseif ($t=='V')
		$str = 'Voided';
	elseif ($t=='T')
		$str = 'Partial';
	elseif ($t=='R')
		$str = 'Returned';
	elseif ($t=='E')
		$str = 'Served';
	elseif ($t=='M')
		$str = 'Modified';
	elseif ($t=='A')
		$str = 'Released';
	elseif ($t=='U')
		$str = 'Suspend';
	elseif ($t=='O')
		$str = 'Posted';
	elseif ($t=='Y')
		$str = 'Paid';
	return $str;
		
}
function check_status($t)
{
	$str='';
	if ($t=='P')
		$str = 'PDC';
	elseif ($t=='C')
		$str = 'Cleared';
	elseif ($t=='X')
		$str = 'Cancelled';
	elseif ($t=='')
		$str = 'Invalid';
	return $str;
		
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
	if ($printType=='UDP DRAFT' || $printType == 'DRAFT')
	{
		 $mtry=0;
		 while (true)
		 {
		 	$fp = fsockopen("udp://$dest", 5003, $errno, $errstr, 30) or die("Can't connect");
		 	if ($mtry>50 || $fp)
		 	{
		 		break;
		 	}
		 }
		 if (!$fp) {
		     echo "Socket Connection Error $errstr ($errno) after $mtry tries<br>\n";
		 }
		 else
		 {	
			fwrite($fp,$pln);
			fwrite($fp,"eof");
	      fclose ($fp);
		}
		
  }	
  elseif ($printType == 'HTTP DRAFT')
	{
		 	$fp = fsockopen("udp://$dest", 5003, $errno, $errstr, 30) or die("Can't connect");
			if (!$fp) 
			{
		     echo "Socket Connection Error $errstr ($errno) after $mtry tries<br>\n";
		 	}
		 	else
		 	{	
				fwrite($fp,$pln);
	      	fclose ($fp);
	      }
		
	}
  elseif ($printType == 'TCP DRAFT')
	{
	   $m = "tcp://$dest";
	   
		$mtry=0;	   
	   while (true)
	   {
			 $fp = @fsockopen("tcp://$dest", 5003, $errno, $errstr, 30);
		 	if ($fp || $mtry>100) 
		 	{
		 		break;
		 	}
		 	//echo " Try...";
		 	$mtry++;
		} 	

		 if (!$fp) {
		     message1($m." $errstr ($errno) after $mtry tries...<br>");
		 }
		 else
		 {	
			   fputs($fp,$pln);
	        	fclose ($fp);
	      //  	echo "<pre>$pln</pre>";
		 }
	}

	elseif ($printType=='LINUX LP Printer')
	{
	   $m = "tcp://$dest";
		$fp = @fsockopen("tcp://$dest", 5003, $errno, $errstr, 10);
		if (!$fp) {
		    message($m." $errstr ($errno)<br>\n");
		}
		else
		{	
			   fwrite($fp,$pln);
	        	fclose ($fp);
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
function delay($d)
{
	$c=0;
	while ($c < $d)
	{
		$e=0;
		while ($e< $d)
		{
			$e++;
		}
		$c++;
	}
	return;
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
function timeElapse($t1, $t2)
{
	
	$a1 = explode(':',$t1);
	$a2 = explode(':',$t2);
	$hr = intval($a2[0]) - intval($a1[0]);
	if ($a2[1] > $a1[1])
		$min .= intval($a2[1]) - intval($a1[1]);
	else
	{
		$hr--;
		$min .= intval($a2[1])+ 60 - $a1[1];
	}	
	$elapse = $hr.":".$min;
	return $elapse;
	
}
function m2c($time)
{
	//military to civilian time
	$time_string = explode(':',$time);
	if (intval($time_string[0]) < 12)
	{
		$new_time = $time_string[0].':'.$time_string[1].'a';
	}
	elseif (intval($time_string[0]) == 12)
	{
		$new_time = $time_string[0].':'.$time_string[1].'n';
	}
	else
	{
		$hrs = intval($time_string[0])-12;
		$new_time = $hrs.':'.$time_string[1].'p';
	}
	return $new_time;
}
function cMonth($month)
{
	$m=date("F", mktime(0, 0, 0, $month, 1, 2000));
	return $m;
}
function lookUpMonth($name,$value, $notreadOnly=true)
 {
 	
  $arr = array("Select"=>"0","January"=>"1","February"=>"2","March"=>"3",
  		"April"=>"4","May"=>"5","June"=>"6","July"=>"7",
		"August"=>"8","September"=>"9","October"=>"10",
		"November"=>"11","December"=>"12");
 		
  $str = "\n\t<select name=\"$name\" id=\"$name\" style=\"border:1px solid black\">";
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
function redirect($amessage)
{
  echo  "<SCRIPT language=JavaScript>
    <!-- Begin
     window.location = '?$amessage'";
    // End -->
    echo  "</SCRIPT>";
  exit;
}
function addDate($d,$a)
{
	//$d - date in mm/dd/yyyy format
	//$a - add $a number of days
	//$tomorrow  = mktime(0, 0, 0, date("m")  , date("d")+1, date("Y"));

	$d1 = explode('/',$d);
	$d2 = date('m/d/Y',mktime(0, 0, 0, $d1[0]  , $d1[1]+$a, $d1[2]));
	return $d2;
} 
function exitMessage($message)
{
?>
 <div align="center"><font color="#FF0000" face="Geneva, Arial, Helvetica, san-serif"><?=$message;?>
  </font> </div>
 <?
 exit();
}
function checkBox($name, $default, $enable)
 {
 	if ($default == 1) $checked = 'checked';
 	else $checked = '';
 	
 	if ($enable == 1) $enable= '';
 	else $enable = 'disabled';
 	
 	echo "<input type='checkbox' name='$name' id='$name'  value='1' $checked $enable>";
 }
function vLogic($l)
{
	if ($l == true)
		return 'Yes';
	else
		return 'No';
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
  if ($admin_id == '') $admin_id ='0';
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
 
 $Qr = @pg_query($Q);
 if ($Qr && pg_num_rows($Qr)>0)
 	return true;
 else
 	return false;
}

function chkRights3($module,$rights, $admin_id)
{
  global $ADMIN;
  if ($admin_id == '') $admin_id ='0';
  $validate_string=" ";

  if ($ADMIN['usergroup']=='A' || $ADMIN['admin_id'] == '1')
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
 
 $Qr = @pg_query($Q);
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

 function fetch_object($qR)
 {
  global $SYSCONF;
  if ($SYSCONF['DB_ENGINE'] = 'pgsql')
  {
   // $qR = @pg_query($q) or message("Error query. ".pg_errormessage());
 	  $R = @pg_fetch_object($qR);
 	}  
 	elseif (substr($SYSCONF['DB_ENGINE'],0,2)=='my')
  {
    //$qR = @mysql_query($q) or message("Error query. ".mysql_error());
 	  $R = @mysql_fetch_object($qR);
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

function db_num_rows($qr)
{
  global $SYSCONF;
  $e='';
  if ($SYSCONF['DB_ENGINE'] = 'pgsql')
  {
    $e = pg_num_rows($qr);
 	}  
 	elseif (substr($SYSCONF['DB_ENGINE'],0,2)=='my')
  {
    $e = mysql_num_rows($qr);
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
	  if (in_array($tablename, array('sales_header','sales_detail','sales_tender','stockledger')))
	  {
	  	$tables = $SYSCONF['tables'];
		$schematablename = $tables[$table];	
//  	galert('st '.$schematablename);

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
	  
//	  galert('sys '.$tables['stockledger'].' table '.$schematablename.' seq'.$seq);return;
      $Q = "select currval('".$seq."'::text)";
		$QR = @pg_query($Q) or die (pg_errormessage());
	
		$R 	= @pg_fetch_object($QR);
		$id = $R->currval;
  }
 	elseif (substr($SYSCONF['DB_ENGINE'],0,2)=='my')
  {
    $id = mysql_insert_id();
  }  
  return $id;
}


function pg_insert_id($table)
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
		$tablename = $tables[$table];	
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


function chkMenuRights($rights)
{
 global $admin;
 $rights = strtoupper($rights);
 return (strpos(strtoupper($admin->menu),$rights) !== false);
}

 function textField($name,$size,$default,$password)
 {
  if ($password == null)
 {
   echo "<input name='$name' size='$size' value='$default'>";
 }
 else
 {
  echo "<input type=password name='$name' size='$size' value='$default'>";
 }
 }

 function textArea($name,$rows, $cols, $default)
 {
  echo "<textarea name='$name' rows='$rows' cols='$cols'>$default</textarea>";
 }

 function lookUpTable($name,$table,$keyfield,$valuefield,$value,$notreadOnly = true)
 {
  global $o;
  $str = "\n\t<select name=\"$name\" style=\"border:1px solid black\" ";
  if (!$notreadOnly)  $str .= " disabled";
  $str .= ">";
  $q = "select * from $table order by $valuefield";
  $qR = pg_query($q,$o);
  if (pg_num_rows($qR) == 0)
  {
  	return "No record for table $table...";
  }
  while ($row = pg_fetch_assoc($qR))
  {
   if ($row[$keyfield] == $value)
   {
    $str .= "\n\t\t<option value=\"$row[$keyfield]\" selected>$row[$valuefield]</option>";
   }
   else
   {
    $str .= "\n\t\t<option value=\"$row[$keyfield]\">$row[$valuefield]</option>";
   }
  }
  $str .= "\n\t</select>";
  return $str;
 }

 function lookUpTable2($name,$table,$keyfield,$valuefield,$value,$notreadOnly = true)
 {
  global $o;
  $str = "\n\t<select name=\"$name\" style=\"border:1px solid black\" ";
  if (!$notreadOnly)  $str .= " disabled";
  $str .= ">";
  $q = "select * from $table order by $valuefield";
  $qR = @pg_query($q);
  if (@pg_num_rows($qR) == 0)
  {
  	return "No record for table $table...";
  }
  //added to display select item
  $str .= "\n\t\t<option value=''>-- Select $table</option>";
  while ($row = pg_fetch_assoc($qR))
  {
   if ($row[$keyfield] == $value)
   {
    $str .= "\n\t\t<option value=\"$row[$keyfield]\" selected>$row[$valuefield]</option>";
   }
   else
   {
    $str .= "\n\t\t<option value=\"$row[$keyfield]\">$row[$valuefield]</option>";
   }
  }
  $str .= "\n\t</select>";
  return $str;
 }

 function getFieldSize($table,$field)
 {
 	$q = "select * from $table";
	$qr = pg_query($q) or die(pg_errormessage());
	
	$num_fields = pg_num_fields($qr) or die(pg_errormessage()); 	
	
	for ($i = 0; $i < $num_fields; $i++)
	{
			$fieldName = pg_field_name($qr,$i);
			$fieldType = pg_field_type($qr,$i);
			$fieldLen = pg_field_len($qr,$i);
			
			if ($fieldName == $field)
			{
				return $fieldLen;
			}
	}		
	echo "\nField $field is not found in table $table...\n";
	return 0;
 }

function lookUpTableReturn($name,$table,$keyfield,$valuefield,$value)
 {
  global $o;
  $str = "\n\t<select name=\"$name\" style=\"border:1px solid black\" >";
  $q = "select * from $table where $keyfield = '$value'";
  $qR = @pg_query($q,$o);
  //echo $value;
  if (@pg_num_rows($qR) == 0)
  {
  	return "No Record";
  }
  else
  {
  	$r = pg_fetch_assoc($qR);
  	return $r[$valuefield]."[$value]";
  }
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

 function lookUpArray($name,$arr,$value,$notreadOnly=true)
 {
  $str = "\n\t<select name=\"$name\" style=\"border:1px solid black\"";
  
  if (!$notreadOnly) $str .= " disabled";
  
  $str .= ">";
  $ctr = count($arr);
  for($i = 0; $i < $ctr; $i++)
  {
   if ($arr[$i] == $value)
   {
    $str .= "\n\t\t<option value=\"$arr[$i]\" selected>$arr[$i]</option>";
   }
   else
   {
    $str .= "\n\t\t<option value=\"$arr[$i]\">$arr[$i]</option>";
   }
  }
  $str .= "\n\t</select>";
  return $str;
 }

 function lookUpAssoc($name,$arr,$value)
 {
  $str = "\n\t<select name=\"$name\" id=\"$name\">";
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
 
 function tableToArray($table, $field)
 {
  global $o;
  $q = "select * from $table order by $field";
  $qR = pg_query($q,$o);
  $arr = array();
  while ($r = pg_fetch_assoc($qR))
  {
   $arr[] = $r[$field];
  }
  return $arr;
 }
function yesterday()
{
  $d =date('Y-m-d',mktime(0, 0, 0, date("m")  , date("d")-1, date("Y")));
  
  return $d;
}
function tomorrow()
{
  $d =date('Y-m-d',mktime(0, 0, 0, date("m")  , date("d")+1, date("Y")));
  return $d;
}
?>
