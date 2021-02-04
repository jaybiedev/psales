<?
$DBDOMAIN='localhost';
$DBNAME='la';
$DBUSERNAME='pgsql';
$DBPASSWORD='';
$DBCONNECT = "host=$DBDOMAIN port=5432  dbname=$DBNAME user=$DBUSERNAME password=$DBPASSWORD";
$DBCONN = pg_Connect($DBCONNECT) or die("Can't connect to server...");


$handle=@fopen("INV.TXT",r);
$contents = "";
$c=0; 
do { 

   $c++;

   $data = fgets($handle, 2048); 
   if (strlen($data) == 0) { 
       break; 
   } 
//   if ($c < 9) continue;
//   if ($c>15) break;

   $afld = explode(",",$data);
   $barcode = $afld[0];
   $stock = $afld[1];
   $price1 = $afld[13]*1;
   $category_code = chop($afld[2]);

   echo "Item $c Barcode $barcode \n";
   $q = "select *
		from stock
		where
			barcode = '$barcode'";
   $qr = @pg_query($q) or die (pg_errormessage().$q);
   if (@pg_num_rows($qr) > 0)
   {
	$updated++;
	$q = "update stock set price1='$price1' where barcode='$barcode'";
	
	// @pg_query($q) or die (pg_errormessage().$q);
   }
   else
   {
	$new++;
	$q = "select * from category where category_code='$category_code'";
	$qc = @pg_query($q) or die (pg_errormessage().$q);
	if (@pg_num_rows($qc) > 0)
	{
		$rc= @pg_fetch_object($qc);
		$category_id = $rc->category_id;
	}
	else
	{
		$category_id = 0;
	}
	$q = "insert into stock (barcode, stock, price1,category_id)
			vaues ('$barcode','$stock','$price1','$category_id')";
	//@pg_query($q);

   }
//	echo $q;
} while(true); 
fclose ($handle);
echo "\n\n\n";
echo "Updated : ".$updated."\n";
echo "New/Added : ".$new;
?>
