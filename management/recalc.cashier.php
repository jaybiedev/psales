<?php

	include_once('../lib/library.php');
	include_once('../lib/dbconfig.php');
	include_once('../lib/connect.php');
	include_once('../var/system.conf.php');
	
	$sales_header = 'sh_2007';
	$sales_detail = 'sd_2007';
	
	$q = " select * from $sales_header where status!='V' and date>'2007-01-14'";//" offset 0 limit 100";
	$qr = @query($q);
  if (!$qr)
  {
   message('Error : '. pg_errormessage().$qr);
  }
	while ($r = @pg_fetch_object($qr))
	{
	 $qq = "select * from $sales_detail where sales_header_id = '$r->sales_header_id'";
	 $qqr = @pg_query($qq) or message(pg_errormessage().$q);
	 if (@pg_num_rows($qqr) == '0' && $qqr)
	 {
	   echo "Id $r->sales_header_id Invoice $r->invoice Date $r->date Amount $r->gross_amount Net $r->net_amount<br>";
   }
	 
  }
