<?
	$q = "select * from category";
	$qr = @pg_query($q) or message(pg_errormessage());

	while ($r = @pg_fetch_object($qr))
	{
		$q = "update stock set category_id='$r->category_id' where ccategory='$r->category_code'";
		$qqr = @pg_query($q) or message(pg_errormessage());
		if (pg_affected_rows($qqr)==0)
		{
			message("No item affected for category ".$r->category_code.' '.$r->category);
		}
	}
?>
