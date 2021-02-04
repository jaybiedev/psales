<?
	if (!session_is_registered('SYSCONF'))
	{
		session_register('SYSCONF');
		$SYSCONF = null;
		$SYSCONF = array();

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

		$SYSCONF['SORT_CATEGORY'] = "category_code, category";
		$SYSCONF['SORT_ACCOUNT'] = "cardno";
		$SYSCONF['SORT_SUPPLIER'] = "account_code, account";
		if ($SYSCONF['BRANCH_ID'] != '')
		{
			$q = "select * from branch where branch_id = '".$SYSCONF['BRANCH_ID']."'";
			$qr  = @pg_query($q);
			$r = @pg_fetch_object($qr);
			$SYSCONF['BRANCH'] = $r->branch;
		}
		if (file_exists("./var/sysconfig.conf"))
		{
			$handle =  @fopen("./var/sysconfig.conf",r);
			do
			{
				$data = fgets($handle, 2048);
				if (strlen($data) == 0) {
					break;
				}
				$aValues =explode('=',$data);
				if (substr(chop($aValues),0,2) != 'rem')
				{
					$SYSCONF[chop($aValues[0])] = chop($aValues[1]);
				}
			}
			while (true);
		}
	}
//		print_r($SYSCONF);

	if (!session_is_registered('aSUPPLIER') || $renewsupplier==1)
	{

		session_register("aSUPPLIER");
		$aSUPPLIER = null;
		$aSUPPLIER = array();
		renewSupplier();
	}
	if (!session_is_registered('aCATEGORY') || $renewcategory==1)
	{
		session_register("aCATEGORY");
		$aCATEGORY = null;
		$aCATEGORY = array();
		renewCategory();
	}

function renewSupplier()
{
	global $SYSCONF, $aSUPPLIER;
		$aSUPPLIER = null;
		$aSUPPLIER = array();
		$q = "select account_id, account_code, account from account 
				where account_type_id in ('1','8') and enable='Y'
				order by ".$SYSCONF['SORT_SUPPLIER'];
		$qr = @pg_query($q) or die (pg_errormessage());
		while ($r = @pg_fetch_assoc($qr))
		{
			$aSUPPLIER[] = $r;
		}
		
	return;
}

function renewCategory()
{
	global $SYSCONF, $aCATEGORY;
		$aCATEGORY = null;
		$aCATEGORY = array();
		$q = "select category_id, category_code, category from category 
				where enable='Y'
				order by ".$SYSCONF['SORT_CATEGORY'];
				
		$qr = @pg_query($q) or die (pg_errormessage());
		while ($r = @pg_fetch_assoc($qr))
		{
			$aCATEGORY[] = $r;
		}
		
	return;
}
?>
