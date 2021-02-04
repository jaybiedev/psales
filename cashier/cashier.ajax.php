<?php
ob_start();
session_start();

include_once('../lib/library.php');
include_once('../lib/dbconfig.php');
include_once('../lib/connect.php');
include_once('../var/system.conf.php');

include_once('../lib/lib.salvio.php');

date_default_timezone_set("Asia/Manila");	
if( !empty($_REQUEST['action']) ) call_user_func($_REQUEST['action'], $_REQUEST['username'], $_REQUEST['password']);


function checkCredentials($username, $password){
	
	$sql = "
		select 
			*
		from 
			admin 
		where			
			username = '".$username."'
		and mpassword = '".md5($password)."'
		and usergroup in ('A','S')
		and enable = 'Y'
	";

	$result = pg_query($sql);
	
	if ( pg_num_rows($result) == 1 ) {

		$r_admin = pg_fetch_assoc($result);

		$sql = "
			insert into
				mul_qty_audit
			(audit_date, audit_time, admin_id, terminal, remark)
				values
			('".date("Y-m-d")."', '".date("H:i:s")."', '$r_admin[admin_id]', '$form_data[terminal]', '$r_admin[name] changed quantity at terminal $form_data[terminal] on ".date("D, d M Y g:i:s a")."')
		";

		pg_query($sql);

		/* credentials valid */
		echo 1;
	} else {
		echo 0;
	}
}

?>