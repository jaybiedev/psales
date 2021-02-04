<?
/*
This program should run on the workstation.
This will upload  updated data .SQL text file
Which was created by the server (offldown.php)

Download data using (debian) :
   sudo wget http://lec.markone.com/utils/offline.sql.gz

   sudo gunzip oflstock.sql.gz
CRITICAL WARNING:  The sequence of $fld array must be exactly the same as the one defined by offldown.php
*/
require_once('/media/hda3/files/library.utils.php');

$offlinefilegz = "/media/hda3/files/offline.sql.gz";
$stockfile = "/media/hda3/files/oflstock.sql";
$adminfile = "/media/hda3/files/admin.sql";
$adminrightsfile = "/media/hda3/files/adminrights.sql";
$tenderfile = "/media/hda3/files/tender.sql";
$promoheaderfile = "/media/hda3/files/promoheader.sql";
$promodetailfile = "/media/hda3/files/promodetail.sql";
$categoryfile = "/media/hda3/files/category.sql";
$errfile = "/media/hda3/files/tmp/OFFLUP.ERR";

if (file_exists("$offlinefilegz"))
{
	$gz = system ("sudo gunzip $offlinefilegz");
	if (!$rm)
	{
		$m = "\nProgram: offlup.php Date : ".date('m/d/Y g:ia')."\n"."Unable to unzip  file $offlinefilegz $gz";
		writefile($m,0,'$errfile');
		
	}
}

//-- upload stock master
if (!file_exists("$stockfile"))
{
	$m = "\nProgram: offlup.php Date : ".date('m/d/Y g:ia')."\n"."File $stockfile NOT found.";
	writefile($m,0,'$errfile');
}


$handle = @fopen("$stockfile",'r');
if (!$handle)
{
	$m = "\nProgram: offlup.php Date : ".date('m/d/Y g:ia')."\n"."Unable to create file $stockfile $handle";
	writefile($m,0,'$errfile');
}

$fld = array('stock_id','barcode','stock','price1','price2','price3','fraction2','fraction3','unit1','unit2','unit3',
                'account_id','category_id','promo_price1','date1_promo','date2_promo','netitem','taxable','date_encoded');


do
{
	$data = fread($handle, 2048);
	if (strlen($data) == 0) {
		break;
	}
	$aValues =explode('||',$data);
	
	$stock_id = chop($aValues[0]);
	$barcode = chop($aValues[1]);

	$qs = "select barcode from stock  where barcode='$barcode'";
	$qr = @pg_query($qs); // or die (pg_errormessage().$q);
	if (@pg_num_rows($qr) == 0)
	{
		$q1 = '';
		$q2 = 'values (';
		$q = "insert into stock (";
		for ($c=0;$c<count($fld);$c++)
		{
			if ($c>0) 
			{
				$q1 .= ",";
				$q2 .= ",";
			}
			$value =trim(addslashes(htmlSpecialChars($aValues[$c])));
			if (in_array($fld[$c],array('account_id','category_id','promo_price1','price1','price2','price3')) && $value == '')
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
			$m = "\nProgram: offlup.php Date : ".date('m/d/Y g:ia')."\n".pg_errormessage().$q;
			writefile($m,0,'$errfile');
			
		}
	} 	
	elseif ($qr)
	{
		$q = "update stock set ";
		for ($c=0;$c<count($fld);$c++)
		{
			if ($c>0) $q .= ",";
			$value =trim(addslashes($aValues[$c]));
			if (in_array($fld[$c],array('account_id','category_id','promo_price1','price1','price2','price3')) && $value == '')
			{
				$value = 0;
			}
			$q .= $fld[$c]."='$value'";
		}
		$q .= " where stock_id = '$stock_id'";
		$qi = @pg_query($q);
		if (!$qi)
		{
			$m = "\nProgram: offlup.php Date : ".date('m/d/Y g:ia')."\n".pg_errormessage().$q;
			writefile($m,0,'$errfile');

		}
	}
	//echo $q;
	//exit;
}
while (true);

fclose($handle);
?>
