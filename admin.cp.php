<?
if ($ADMIN['admin_id'] == '')
{
	message('Access Denied...');
	exit;
}
if ($p1 == 'Change Password')
{
	if (md5($oldpassword) != $ADMIN['mpassword'])
	{
		message("Incorrect Old Password.");
	}
	else
	{
		if ($mpassword != $mpassword_confirm)
		{
			message("Password does NOT match...");
		}
		else
		{
			$newpassword = md5($mpassword);
			$q = "update admin set mpassword='$newpassword' where admin_id='".$ADMIN['admin_id']."'";
			$qr = @pg_query($q) or message(pg_errormessage());
			if ($qr)
			{
				$module='admin';
				$msg = 'Changed Password of '.$ADMIN['username'];
				audit($module, $q, $ADMIN['admin_id'], $msg, $ADMIN['admin_id']);
				
				$ADMIN['mpassword'] = $newpassword;
				message("Change of Password Successful...");
			}
		}
	}
}	
?>
<form name="form1" method="post" action="">
  <table width="60%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr> 
      <td colspan="2"><font size="5"><strong>Change Password <br>&nbsp;
        </strong></font></td>
    </tr>
    <tr> 
      <td>User Id</td>
      <td>
        <?= $ADMIN['admin_id'];?>
      </td>
    </tr>
    <tr> 
      <td width="26%">Name</td>
      <td width="74%"> 
        <?= $ADMIN['name'];?>
      </td>
    </tr>
    <tr> 
      <td>Username</td>
      <td> 
        <?= $ADMIN['username'];?>
      </td>
    </tr>
    <tr> 
      <td>Old Password</td>
      <td><input name="oldpassword" type="password" id="oldpassword"   onKeypress="if(event.keyCode==13) {document.getElementById('mpassword').focus();return false;}"></td>
    </tr>
    <tr> 
      <td>New Password</td>
      <td><input name="mpassword" type="password" id="mpassword"   onKeypress="if(event.keyCode==13) {document.getElementById('mpassword_confirm').focus();return false;}"></td>
    </tr>
    <tr> 
      <td>Confirm New Password</td>
      <td><input name="mpassword_confirm" type="password" id="mpassword_confirm"></td>
    </tr>
    <tr> 
      <td colspan="2">&nbsp;</td>
    </tr>
    <tr> 
      <td colspan="2"><input name="p1" type="submit" id="p1" value="Change Password"> 
        <input name="p12" type="button" id="p12" value="Cancel" onClick="window.location='?p='">
        <input name="p13" type="button" id="p13" value="Close" onClick="window.location='?p='"></td>
    </tr>
  </table>
</form>
<script>document.getElementById('oldpassword').focus()</script>