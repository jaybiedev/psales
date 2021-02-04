<?
	if (!session_is_registered('SYSCONF'))
	{
		session_register('SYSCONF');
		$SYSCONF = null;
		$SYSCONF = array();
	}

	$qr = pg_query("select * from sysconfig");
	while ($r = pg_fetch_object($qr))
	{
		$SYSCONF[$r->sysconfig]=$r->value;
	}
	
	$SYSCONF['IP'] = $_SERVER['REMOTE_ADDR'];
	
	$qr = pg_query("select * from terminal where ip='".$SYSCONF['IP']."'");
	while ($r = pg_fetch_assoc($qr))
	{
			$SYSCONF[$r['definition']] = $r['value'];
	}
	
	
?>
