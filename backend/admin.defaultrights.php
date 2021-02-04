<?
function defaultRights($admin_id, $usergroup)
{
	if ($admin_id == '0') return false;
	$q = "select * from admindefault where enable='Y' and usergroup = '$usergroup'";
	$qr = @pg_query($q) or message('Error Posting Default Rights...'.@pg_errormessage());
	
	while ($r = @pg_fetch_object($qr))
	{
		$module = lookUpTableReturnValue('x','module','module_id','module',$r->module_id);
		$_add =md5($r->madd.$admin_id."100".$module);
		$_edit = md5($r->medit.$admin_id."250".$module);
		$_delete = md5($r->mdelete.$admin_id."400".$module);
		$_view = md5($r->mview.$admin_id."550".$module);


		$qq = "insert into adminrights (admin_id, module_id, madd, medit, mdelete, mview)
						values ( '$admin_id', '$r->module_id',
						 '$_add', '$_edit', '$_delete', '$_view')";
		$qqr = @pg_query($qq) or message(pg_errormessage().$qq);
	}
	return true;
}