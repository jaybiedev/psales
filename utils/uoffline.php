<?
function openServer()
{
	$DBDOMAIN='10.0.0.1';
	$DBNAME='lec';
	$DBUSERNAME='pgsql';
	$DBPASSWORD='';
	$DBCONNECT = "host=$DBDOMAIN port=5432  dbname=$DBNAME user=$DBUSERNAME password=$DBPASSWORD";
	$DBCONN = pg_Connect($DBCONNECT); // or die("Can't connect to server...");
	if (!$DBCONN)
	{
		return 0;
	}
	else
	{
		return 1;
	}
}

function openLocal()
{
	$DBDOMAIN='localhost';
	$DBNAME='lec';
	$DBUSERNAME='jays';
	$DBPASSWORD='jnb2000';
	$DBCONNECT = "host=$DBDOMAIN port=5432  dbname=$DBNAME user=$DBUSERNAME password=$DBPASSWORD";
	$DBCONN = pg_Connect($DBCONNECT); // or die("Can't connect to server...");
	if (!$DBCONN)
	{
		return 0;
	}
	else
	{
		return 1;
	}
}

require_once('library.utils.php');


$downloadall = 0; // set to zero to filter with lastdownload date.
$lastdownload = '';

$afile = null;
$afile = array();

$temp = null;
$temp = array();
$temp['table'] = 'stock';
$temp['sqlfile'] = 'stock.sql';
$temp['updated'] = 'Y';
$temp['fields'] = array('barcode','stock_id','stock','price1','price2','price3','fraction2','fraction3','unit1','unit2','unit3', 
		'account_id','category_id','promo_price1','date1_promo','date2_promo','netitem','taxable','date_encoded','enable');
$afile[]=$temp;

$temp = null;
$temp = array();
$temp['table'] = 'admin';
$temp['sqlfile'] = 'admin.sql';
$temp['updated'] = 'N';
$temp['fields'] = array('admin_id','username','name','enable');
$afile[]=$temp;

$temp = null;
$temp = array();
$temp['table'] = 'adminrights';
$temp['updated'] = 'N';
$temp['sqlfile'] = 'adminrights.sql';
$temp['fields'] = array('admin_id','module_id','madd','medit','mview','mdelete','enable');
$afile[]=$temp;

$temp = null;
$temp = array();
$temp['table'] = 'module';
$temp['updated'] = 'N';
$temp['sqlfile'] = 'module.sql';
$temp['fields'] = array('module_id','module','description','enable');
$afile[]=$temp;

$temp = null;
$temp = array();
$temp['table'] = 'tender';
$temp['updated'] = 'N';
$temp['sqlfile'] = 'tender.sql';
$temp['fields'] = array('tender_id','tender_code','tender','enable');
$afile[]=$temp;

$temp = null;
$temp = array();
$temp['table'] = 'terminal';
$temp['updated'] = 'N';
$temp['sqlfile'] = 'terminal.sql';
$temp['fields'] = array('terminal_id','ip','definition','value','terminal','enable');
$afile[]=$temp;


$errfile = 'OFFLDOWN.ERR';

//system("rm $stockfilegz");

// -- check for local last update
if (!openLocal())
{
	echo "Problem Openning Local Database...";
 	writefile($m,0,$errfile);
 	exit;
}
echo "LOCAL IS OK...";

$aOFFL = null;
$aOFFL = array();
/*
$q = "select value1 as date from cache where type='OFFLINEDATA' order by date desc offset 0 limit 1";
$qr = @pg_query($q) or die(pg_errormessage());
$r = @pg_fetch_object($qr);
if ($r)
{
  $lastdownload = $r->date;
}
*/

if (file_exists("offline.conf"))
{
	$handle =  @fopen("offline.conf",r);
	do
	{
		$data = fgets($handle, 2048);
		if (strlen($data) == 0) {
			break;
		}
		$aValues =explode('=',$data);
		$aOFFL[chop($aValues[0])] = chop(ltrim($aValues[1]));
	}
	while (true);
}
$lastdownload = $aOFFL['lastdownload'];

// -- open server
if (!openServer())
{
	echo "Problem Openning Server Database...";
 	writefile($m,0,$errfile);
 	exit;
}

echo "SERVER IS OK...";


//--stock master
$fld = array('stock_id','barcode','stock','price1','price2','price3','fraction2','fraction3','unit1','unit2','unit3', 
		'account_id','category_id','promo_price1','date1_promo','date2_promo','netitem','taxable','date_encoded');

$fc = 0;
foreach ($afile as $fileinfo)
{
	$fc++;
	$fld = $fileinfo['fields'];
	$q = "select ";
	for ($c=0;$c<count($fld);$c++)
	{
		if ($c>0) $q .= ",";
		$q .= $fld[$c];
	}
	$q .= " from ".$fileinfo['table'];
	if ($fileinfo['updated'] == 'Y' and $downloadall != 1)
	{
		$q .= " where enable='Y'";
		if ($lastdownload != '')
		{
			$q .= " and date_updated>='$lastdownload' and date_updated!=''";
		}
	}
	//$q .= "offset 0 limit 5";

	$qr = @pg_query($q) or die (pg_errormessage());


	if (!$qr)
	{
  	    		$m = "\nProgram: uoffline.php Table: ".$fileinfo['table']." Date:".date('m/d/Y g:ia')."\n"."Unable to query stock ".pg_errormessage().$q;
      	 	writefile($m,0,$errfile);
	}

	$fl = @fopen($fileinfo['sqlfile'],'w+');
	if (!$fl)
	{
     		$m = "\nProgram: uoffline.php Table: ".$fileinfo['table']." Date : ".date('m/d/Y g:ia')."\n"."Unable to open file $stockfile $fl";
  		 	writefile($m,0,$errfile);
	}

	while ($r = @pg_fetch_assoc($qr))
	{
		$details = '';
		for ($c=0;$c<count($fld);$c++)
		{
			if ($c>0) $details .= "||";
			$details .= chop($r[$fld[$c]]);
		}	

		$details .= "\n";
		$fw = @fwrite($fl, $details);
		if (!$fw)
		{
       		$m = "\nProgram: uoffline.php Table: ".$fileinfo['table']."Date : ".date('m/d/Y g:ia')."\n"."Unable to write to file $stockfile $fw";
       	 	writefile($m,0,$errfile);
		}
	}

	fclose($fl);
}


//--- finish downloading to text file updated stock master records
//--- switch back to local database
if (!openLocal())
{
	echo "Problem Openning Local Database...";
 	writefile($m,0,$errfile);
 	exit;
}


$fc = 0;
foreach ($afile as $fileinfo)
{
	$fc++;
	$fld = $fileinfo['fields'];
   if (!file_exists($fileinfo['sqlfile']))
   { 
	  $m = "\nProgram: uoffline Table: ".$fileinfo['table']." Date : ".date('m/d/Y g:ia')."\n"."File $stockfile NOT found.";
	  writefile($m,0,$errfile);
  }
  $handle = @fopen($fileinfo['sqlfile'],'r');
  if (!$handle)
  {
  	  $m = "\nProgram: uoffline.php  Table: ".$fileinfo['table']." Date : ".date('m/d/Y g:ia')."\n"."Unable to create file $stockfile $handle";
	  writefile($m,0,$errfile);
  }


  do
  {
	  $data = fgets($handle, 2048);
	  if (strlen($data) == 0) {
	    	break;
	  }
	  $aValues =explode('||',$data);

	  $pkeyfield = $fld[0];
	  $pkeyvalue = chop($aValues[0]);
	  $enablevalue = chop($aValues[count($fld)-1]);

	  $qs = "select ".$pkeyfield." from ".$fileinfo['table'].
	  			"  where ".$pkeyfield." = '$pkeyvalue'";
	 
	  $qr = @pg_query($qs); // or die (pg_errormessage().$q);
	  if (@pg_num_rows($qr) == 0)
	  {
	  	  if ($enablevalue == 'N') continue;
	  	  $q1 = '';
		  $q2 = 'values (';
		  $q = "insert into ".$fileinfo['table']." (";
		  for ($c=0;$c<count($fld);$c++)
		  {
			  if ($c>0) 
			  {
				  $q1 .= ",";
				  $q2 .= ",";
			  }
			  $value =trim(addslashes(htmlSpecialChars($aValues[$c])));
			  if (in_array($fld[$c],array('account_id','category_id','promo_price1','price1','price2','price3','fraction2','fraction3')) && $value == '')
			  {
			  	  $value = 0;
			  }
			
			  $q1 .= $fld[$c];
			  $q2 .= "'$value'";
		  }
		  $q1 .= ")";
		  $q2 .= ")";
	  	  $q .= $q1;		
	  	  $q .= $q2;

		  $qi = @pg_query($q);
		  if (!$qi)
		  {
			  //echo $qs." $stock_id<br>";
			  $m = "\nProgram: uoffline.php Table: ".$fileinfo['table']." Date : ".date('m/d/Y g:ia')."\n".pg_errormessage().$q;
			  writefile($m,0,$errfile);
			
		  }
	  } 	
	  elseif ($qr)
	  {
		  $q = "update ".$fileinfo['table']."  set ";
		  for ($c=0;$c<count($fld);$c++)
		  {
			  if ($c>0) $q .= ",";
			  $value =trim(addslashes($aValues[$c]));
			  if (in_array($fld[$c],array('account_id','category_id','promo_price1','price1','price2','price3','fraction2','fraction3')) && $value == '')
			  {
				  $value = 0;
			  }
			  $q .= $fld[$c]."='$value'";
		  }
		  $q .= " where ".$pkeyfield." = '$pkeyvalue'";
		  $qi = @pg_query($q);
		  if (!$qi)
		  {
			  $m = "\nProgram: uoffline.php Table : ".$fileinfo['table']." Date : ".date('m/d/Y g:ia')."\n".pg_errormessage().$q;
			  writefile($m,0,$errfile);

		  }
	  }
	//exit;
  }
  while (true);

  fclose($handle);
}
//end of uploading


$newdownload = date('Y-m-d');

$m = 'lastdownload='.$newdownload;
writefile($m,1,'offline.conf');

/*
if ($lastdownload == '')
{
	$q = "insert into cache (type, value1) values ('OFFLINEDATA','$newdownload')";
	$qr = @pg_query($q);
	if (!$qr)
	{
       		$m = "\nProgram: uoffline.php Date : ".date('m/d/Y g:ia')."\n"."Unable to add cache for file $stockfile ".pg_errormessage().$q;
        	writefile($m,0,$errfile);
	}
}
else
{
	$q = "update cache set value1= '$newdownload' where type='OFFLINEDATA'";
	$qr = @pg_query($q);
	if (!$qr)
	{
       		$m = "\nProgram: offldown.php Date : ".date('m/d/Y g:ia')."\n"."Unable to update cache for file $stockfile ".pg_errormessage().$q;
        	writefile($m,0,$errfile);
	}
}
*/
?>
