<?
	$DBDOMAIN='localhost';
	$DBNAME='grocer';
	$DBUSERNAME='pgsql';
	$DBPASSWORD='123';
	$DBCONNECT = "host=$DBDOMAIN port=5432  dbname=$DBNAME user=$DBUSERNAME password=$DBPASSWORD";
 	$DBCONN = pg_Connect($DBCONNECT) or die("Can't connect to server...");


	$q = "select * from sales_header where date='2006-10-13'";
	$qr = pg_query($q);
	$difference = 0;
	while ($r = pg_fetch_object($qr))
	{
		$q = "select sum(amount) as amount from sales_detail where sales_header_id = '$r->sales_header_id'";
		$qqr = pg_query($q);
		$rr = pg_fetch_object($qqr);
		if ($rr->amount != $r->net_amount)
		{
			echo "--- NOT EQUAL --- Invoice $r->invoice Id: $r->sales_header_id Amount: $r->net_amount Difference : ".($r->net_amount - $rr->amount)."</br>";
			$q = "select * from sales_detail where sales_header_id = '$r->sales_header_id'";
			$qqqr = pg_query($q);
			$amt = 0;
			while ($rrr = pg_fetch_object($qqqr))
			{
				$amt += $rrr->amount;
				echo $rrr->barcode.'  '.$rrr->qty.'   '.$rrr->amount.'  '.$amt."</br>";
			}
			$difference += $r->net_amount - $rr->amount;
			$q = "update sales_header set net_amount='$amt' where sales_header_id='$r->sales_header_id'";
			pg_query($q);

		}
		else
		{
			echo "*** EQUAL --- Invoice $r->invoice </br>";
		}
	}

	echo "Total Difference : ".$difference;
?>
