<?
/*
	if ($p == "logout")
	{
		session_unset();
		message("User has logged out successfully");
	}	

*/
	$KURUKOKUK.='123';
	$cake = "select * from sysconfig where sysconfig='REG_SERIAL_NO'";
	
  	$DBCONN = pg_Connect($DBCONNECT) or die("Can't connect to server...");

	$R=fetch_object($cake);
	$REG_SERIAL_NO = $R->value;
	if (!in_array(substr($REG_SERIAL_NO,0,13),we(1)))
	{
		$REG_SERIAL_NO = we(2);
		pg_query("update sysconfig set value='$REG_SERIAL_NO' where sysconfig='REG_SERIAL_NO'") or die (mysql_error());
		
	}	

?>
