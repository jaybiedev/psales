<?
	$DBDOMAIN='localhost';
	$DBNAME='lec';
	$DBUSERNAME='pgsql';
	$DBPASSWORD='123';
	$DBCONNECT = "host=$DBDOMAIN port=5432  dbname=$DBNAME user=$DBUSERNAME password=$DBPASSWORD";

	$DBCONN = pg_Connect($DBCONNECT) or die("Can't connect to server...");

	$q = "select * from tmp_account order by account_type_id, account_code, cardno, ";
	$qr = pg_query($q);
	
	while ($r = pg_fetch_object($qr))
	{
		$q = "insert into account 
					(account_code, 
					cardno, 
					account,
					account_type_id,)
				values
					('$r->account_code','$r->cardno','$r->account','$r->account_type_id',
					
	}
?>