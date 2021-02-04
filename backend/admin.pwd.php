<form name="form1" method="post" action="">
<?
if (!chkRights2('password','medit',$ADMIN['admin_id']))
{
	message1("<br>[ ACCESS DENIED. ]</b>");
	exit;
}
if ($p1 == '')
{
	if ($id == '')
	{
		message1("<br>[ NO User Specified.... ]</b>");
		exit;
	}

	$q = "select * from admin where admin_id = '$id'";
	$qr = @pg_query($q) or die (pg_errormessage());
	$r = @pg_fetch_object($qr);

}
elseif ($p1 == 'Change Password' )
{
	$id = $_REQUEST['admin_id'];
	$mpassword = $_REQUEST['mpassword'];
	$username = $_REQUEST['username'];
	if ($id == '')
	{
		message1("[ NO User Specified... ] ");
		exit;
	}

	if ($mpassword == '')
	{
		message1("[ NO Password Specified... ] ");
		exit;
	}
	$new = md5($mpassword);

	$q = "update admin set mpassword='$new' , sessionid='' where admin_id= '$id' and username='$username'";
	$qr = @pg_query($q) or die (pg_errormessage());
	if ($qr && @pg_affected_rows($qr)>0)
	{
		message1("<br>[ Password Changed... ] </br>");
		exit;
	}
	else
	{
		message1("<br>[ An Error Occurred in Updating User Data.  Unable to change Password. ]<br>".pg_errormessage());
	}
}
?>
  <table width="90%%" border="0" cellspacing="0" cellpadding="0">
    <tr background="../graphics/table_horizontal.PNG"> 
      <td colspan="2"><font color="#CCCCCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
        &nbsp;<img src="../graphics/team_wksp.gif"> User Passowrd Administration</strong></font></td>
    </tr>
    <tr> 
      <td width="18%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Name</font></td>
      <td width="82%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="textfield2" type="text" value="<?= $r->name;?>" readonly="">
        </font></td>
    </tr>
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Username</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="username" type="text" id="username" value="<?= $r->username;?>" readonly="">
        </font></td>
    </tr>
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Branch</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <input name="textfield222" type="text" value="<?= lookUpTableReturnValue('x','branch','branch_id','branch',$r->branch_id);?>" readonly="">
        </font></td>
    </tr>
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">New Password</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="mpassword" type="password" id="mpassword">
        <input  name="admin_id" type="hidden" value="<?=$id;?>">
        </font></td>
    </tr>
    <tr> 
      <td colspan="2"><input name="p12" type="button" id="p12" value="Cancel" onClick="window.location='?p='">
        <input name="p1" type="submit" id="p1" value="Change Password"></td>
    </tr>
  </table>
</form>
