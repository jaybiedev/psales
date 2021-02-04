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

	if (!session_is_registered('aSUPPLIER') || $renewsupplier==1)
	{
		renewSupplier();
	}
	if (!session_is_registered('aCATEGORY') || $renewcategory==1)
	{
		renewCategory();
	}


function renewSupplier()
{
global $aSUPPLIER;
		if (!session_is_registered('aSUPPLIER'))
		{
			session_register("aSUPPLIER");
		}
		$aSUPPLIER = null;
		$aSUPPLIER = array();
		$q = "select account_id, account_code, account from account 
				where account_type_id in ('1','8') and enable='Y'
				order by account_code, account";
		$qr = @pg_query($q) or die (pg_errormessage());
		while ($r = @pg_fetch_assoc($qr))
		{
			$aSUPPLIER[] = $r;
		}
		
return;
}

function renewCategory()
{
global $aCATEGORY;
		if (!session_is_registered('aCATEGORY'))
		{
			session_register("aCATEGORY");
		}
		$aCATEGORY = null;
		$aCATEGORY = array();
		$q = "select category_id, category_code, category from category 
				where enable='Y'
				order by category_code, category";
		$qr = @pg_query($q) or die (pg_errormessage());
		while ($r = @pg_fetch_assoc($qr))
		{
			$aCATEGORY[] = $r;
		}
		
return;
}
?>
