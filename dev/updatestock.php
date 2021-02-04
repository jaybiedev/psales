	$DBDOMAIN='localhost';
	$DBNAME='lec';
	$DBUSERNAME='pgsql';
	$DBPASSWORD='123';
	$DBCONNECT = "host=$DBDOMAIN port=5432  dbname=$DBNAME user=$DBUSERNAME password=$DBPASSWORD";

	$DBCONN = pg_Connect($DBCONNECT) or die("Can't connect to server...");

	$q = "select * from tmp_stock";
	$qr = pg_query($q);
	
	while ($r = pg_fetch_object($qr))
	{
		$account_id = 0;
		$category_id = 0;
			
		$q = "select * from account where account_code = '$r->account_code'";
		$qqr = pg_query($q);
		$rr = pg_fetch_object($qqr);
		$account_id = $rr->account_id;
		
		$q = "select * from category where category_code = '$r->category_code'";
		$qqr = pg_query($q);
		$rr = pg_fetch_object($qqr);
		$category_id = $rr->category_id;

		if ($account_id == '') $account_id = 0;
		if ($category_id == '') $category_id = 0;
		
		$q =  "select * from stock where barcode = '$r->barcode'";
		$qqr = pg_query($q);
		if (pg_num_rows() == 0)
		{
			if ($r->fraction3 == 0)
			{
				$fraction3 = 1;
			}
			else
			{
				$fraction3 = $r->fraction3;
			}
			$cost3 = $r->cost1*$fraction3;
			
			$q = "insert into stock set
							barcode='$r->barcode',
							stock_description = '$r->stock_description',
							stock = '$r->stock',
							cost1 = '$r->cost1',
							price1 = '$r->price1',
							fraction3 = '$fraction3',
							cost3 = '$cost3',
							unit1 = '$r->unit1',
							category_id = '$category_id',
							account_id = '$account_id'";
							
			echo $q;
			//pg_query($q); //when not found....
		}
		else
		{
			$rr = pg_fetch_object($qqr);
			$sid = $rr->stock_id;
			$q = "update stock set ";
			if ($rr->cost1 == '')
			{
				$q .= ", cost1 = '$rr->cost1 ";
			}
			if ($rr->supplier_id == '')
			{
				$q .= ", account_id = '$account_id ";
			}
			if ($rr->category_id == '')
			{
				$q .= ", category_id = '$category_id ";
			}
			$q .= " where stock_id = '$sid'";
			
			echo $q ;
			print_r($rr);
			//pg_query($q); //when found
					
		}
	}