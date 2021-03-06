<? // temporarily suspend operation
//	exit;


/*
This program should run on the database server.
This will download updated data, storing it to an .SQL text file
The .SQL file will later be downloaded to the cashier/workstation using wget (debian)
	and an uploader program shall upload the .SQL to workstation local database.
*/
require_once('library.utils.php');


$errfile = 'OFFLDOWN.ERR';
$stockfile = 'oflstock.sql';
$stockfilegz = 'oflstock.sql.tar.gz';

if (false !== file_exists($stockfilegz))
{
	system("rm $stockfilegz");
}
$q = "select value1 as date from cache where type='OFFLINEDATA' order by date desc offset 0 limit 1";
$qr = @pg_query($q) or die(pg_errormessage());
$r = @pg_fetch_object($qr);
$lastdownload = $r->date;
$downloadall = 0; // set to zero to filter with lastdownload date.

//--stock master
$fld = array('stock_id','barcode','stock','price1','price2','price3','fraction2','fraction3','unit1','unit2','unit3', 
		'account_id','category_id','promo_price1','date1_promo','date2_promo','netitem','taxable','date_encoded');

$q = "select ";
for ($c=0;$c<count($fld);$c++)
{
	if ($c>0) $q .= ",";
	$q .= $fld[$c];
}

$q .= " from 
		stock 
	where 
		enable='Y'";
if ($lastdownload != '' and $downloadall != 1)
{
	$q .= " and date_updated>='$lastdownload'";
}
$q .= " and date_encoded>'2006'";

$q .= "offset 0 limit 5"; //for testing -- remove for actual

$qr = @pg_query($q) or die (pg_errormessage());

//echo "num ".pg_num_rows($qr);exit;
if (!$qr)
{
      		$m = "\nProgram: offldown.php Date : ".date('m/d/Y g:ia')."\n"."Unable to query stock ".pg_errormessage().$q;
      	 	writefile($m,0,'OFFLDOWN.ERR');
}

$fl = @fopen("$stockfile",'w+');
if (!$fl)
{
      		$m = "\nProgram: offldown.php Date : ".date('m/d/Y g:ia')."\n"."Unable to open file $stockfile $fl";
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
       		$m = "\nProgram: offldown.php Date : ".date('m/d/Y g:ia')."\n"."Unable to write to file $stockfile $fw";
       	 	writefile($m,0,$errfile);
	}
}

fclose($fl);
$gz = @system("tar -czf $stockfilegz $stockfile");
if (!$gz)
{
        $m = "\nProgram: offldown.php Date : ".date('m/d/Y g:ia')."\n"."Unable to compress  file $stockfilegz $gz";
    	writefile($m,0,$errfile);

}

$newdownload = date('Y-m-d');
if ($lastdownload == '')
{
	$q = "insert into cache (type, value1) values ('OFFLINEDATA','$newdownload')";
	$qr = @pg_query($q);
	if (!$qr)
	{
       		$m = "\nProgram: offldown.php Date : ".date('m/d/Y g:ia')."\n"."Unable to add cache for file $stockfile ".pg_errormessage().$q;
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
?>
