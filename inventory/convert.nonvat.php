<?
	$q = "select * from tmp_nonvat";
	$qr = @pg_query($q) or message(pg_errormessage());

	while ($r = @pg_fetch_object($qr))
	{
		$q = "update stock set taxable='N' where barcode='$r->barcode'";
		$qqr = @pg_query($q) or message(pg_errormessage());
		if (pg_affected_rows($qqr)==0)
		{
			message("No item affected for category ".$r->barcode);
		}
		else
		{
			message ("Ok");
		}
	}
?>
