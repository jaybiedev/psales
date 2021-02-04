	$DBDOMAIN='localhost';
	$DBNAME='lec';
	$DBUSERNAME='pgsql';
	$DBPASSWORD='123';
	$DBCONNECT = "host=$DBDOMAIN port=5432  dbname=$DBNAME user=$DBUSERNAME password=$DBPASSWORD";

	$DBCONN = pg_Connect($DBCONNECT) or die("Can't connect to server...");

	$q = "select * from account where account_type_id= '1'";
	$qr = pg_query($q);
	
	while ($r = pg_fetch_object($qr))
	{
		$q = "update stock set caccount_code='$r->account_code' where account_id='$r->account_id'";
		$qqr = pg_query($q);
		
	}