$fl=@fopen("inv.txt",r);
$contents = ""; 
do { 
   $data = fread($handle, 8192); 
   if (strlen($data) == 0) { 
       break; 
   } 
   
   $afld = explode(";",$data);
   $barcode = $afld[1];
   $stock = $afld[2];
   $price1 = $afld[1];
   $category_code = $afld[4];

   $q = "select *
		from stock
		where
			barcode = '$barcode'";
   $qr = @pg_query($q) or die (pg_errormessage().$q);
   if (@pg_num_rows($qr) > 0)
   {
	$q = "update stock set price1='$price1' where barcode='$barcode'";
	@pg_query($q) or die (pg_errormessage().$q);
   }
   else
   {
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
	@pg_query($q);

   }
} while(true); 
fclose ($handle)
