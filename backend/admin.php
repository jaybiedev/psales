<script>
function checkAll()
{
	module_id = form1.module_id.value;
	if (module_id == 0)
	{
		alert("No Module Selected...Please Select Module First...");
		return false;
	}
	document.getElementById('madd').checked=1;
	document.getElementById('medit').checked='1';
	document.getElementById('mdelete').checked='1';
	document.getElementById('mview').checked='1';
	return false;
}
</script>
<?

if (!chkRights2('users','mview',$ADMIN['admin_id']))
{
	message('Access does not allow in this area...');
	exit;
}

if (!session_is_registered('aadmin'))
{
	session_register('aadmin');
	$aadmin = null;
	$aadmin = array();
}
if (!session_is_registered('iadmin'))
{
	session_register('iadmin');
	$iadmin = null;
	$iadmin = array();
}


$fields_header = array('name','username','usergroup','mpassword','mpassword_confirm','enable','date_expire','branch_id');
$fields_detail = array('module_id','madd','medit','mdelete','mview');
if (!in_array($p1, array(null,'Edit','Print','Load')))
{
	for ($c=0;$c<count($fields_header);$c++)
	{
		if (substr($fields_header[$c],0,4) == 'date')
		{
			$aadmin[$fields_header[$c]] = mdy2ymd($_REQUEST[$fields_header[$c]]);
		}
		else
		{
			$aadmin[$fields_header[$c]] = $_REQUEST[$fields_header[$c]];
		}
	}	

	for ($c=0;$c<count($fields_detail);$c++)
	{
		$iadmin[$fields_detail[$c]] = $_REQUEST[$fields_detail[$c]];
	}	
}


if ($p1 == 'Add User')
{
	$aadmin=null;
	$aadmin = array();
	$iadmin=null;
	$iadmin = array();
	
}
elseif ($p1 == 'Load' && $id == '')
{
	message("Nothing to edit...");
}
elseif ($p1 == 'Load')
{
	
	$q = "select * from admin where admin_id='$id'";
	$qr = @pg_query($q) or message(pg_errormessage().$q);
	if (@pg_num_rows($qr) == 0)
	{
		message("User NOT found...");
	}
	else
	{
		$r = @pg_fetch_assoc($qr);
		$aadmin = $r;
	}	

}
elseif ($p1 == 'Edit' && $id!='')
{
	$q = "select adminrights_id, admin_id, module_id, madd as nadd, medit as nedit, mdelete as ndelete, mview as nview
				 from 
				 	adminrights 
				where 
					adminrights_id = '$id'";
	$qr = @pg_query($q) or message(pg_errormessage().$q);
	if (@pg_num_rows($qr)>0)
	{
		$r = @pg_fetch_assoc($qr);
		if ($r['admin_id'] != $aadmin['admin_id'])
		{
			message(" Rights does not belong to the user specified...");
		}
		else
		{
			$iadmin = null;
			$iadmin = array();
			$_add =md5('Y'.$aadmin['admin_id']."100".$r['module']);
			$_edit = md5('Y'.$aadmin['admin_id']."250".$r['module']);
			$_delete = md5('Y'.$aadmin['admin_id']."400".$r['module']);
			$_view = md5('Y'.$aadmin['admin_id']."550".$r['module']);
			
			if ($_add == $r['nadd'])
			{
				$r['madd'] ='Y';
			}
			if ($_edit== $r['nedit'])
			{
				$r['medit'] ='Y';
			}
			if ($_delete== $r['ndelete'])
			{
				$r['mdelete'] ='Y';
			}
			if ($_view== $r['nview'])
			{
				$r['mview'] ='Y';
			}

			$iadmin = $r;
		}
	}
	else
	{
		message(" Item NOT found...");
	}
}
elseif ($p1 == 'Save User' && ($aadmin['name']=='' || $aadmin['username'] == ''))
{
	message('Please Provider Name and Username...');
}
elseif ($p1 == 'Save User' && $aadmin['admin_id']=='' && $aadmin['mpassword']!= $aadmin['mpassword_confirm'])
{
	message('Password Does Not Match...');
}
elseif ($p1 == 'Save User' && $aadmin['admin_id'] == '')
{

	$mpasswd = md5($aadmin['mpassword']);

	$q = "insert into admin (name, username, usergroup, mpassword, date_expire, branch_id, enable)
				values ('".$aadmin['name']."','".$aadmin['username']."','".$aadmin['usergroup']."',
						'$mpasswd', '".$aadmin['date_expire']."', '".$aadmin['branch_id']."','".$aadmin['enable']."')";
	$qr = @pg_query($q) or message(pg_errormessage().$q);

	if ($qr)
	{
			$q = "select currval('admin_admin_id_seq')" ;
			$qr = @pg_query($q) or message(pg_errormessage());
			$r = @pg_fetch_object($qr);

			if ($r)
			{
				$aadmin['admin_id'] = $r->currval;
				message("User ".$aadmin['admin_id']." Successfully Created...Please Add User Rights...");

				$module='admin';
				$msg = 'Inserted on '.date('m/d/Y g:ia').' by: '.$ADMIN['username'];
				audit($module, $q, $ADMIN['admin_id'], $msg, $aadmin['admin_id']);
		
				include_once('admin.defaultrights.php');
				defaultRights($aadmin['admin_id'], $aadmin['usergroup']);
			}
	}		
}
elseif ($p1 == 'Save User' && $aadmin['admin_id'] != '')
{

	$q = "update admin set name='".$aadmin['name']."',
							username='".$aadmin['username']."',
							usergroup = '".$aadmin['usergroup']."',
							branch_id = '".$aadmin['branch_id']."',
							date_expire = '".$aadmin['date_expire']."',
							enable='".$aadmin['enable']."'
					where
							admin_id='".$aadmin['admin_id']."'";

	$qr = @pg_query($q) or message(pg_errormessage());
	if ($qr)
	{
		$module='admin';
		$msg = 'Updated on '.date('m/d/Y g:ia').' by: '.$ADMIN['username'];
		audit($module, $q, $ADMIN['admin_id'], $msg, $aadmin['admin_id']);
		message("User data updated...");
	}
								
							
}
elseif ($p1 == 'Ok' && $aadmin['admin_id'] == '')
{
	message('No user selected... Or save user data first...');
}
elseif ($p1 == 'Ok' && $iadmin['module_id'] == '')
{
	message('No Module Selected...');
}
elseif ($p1 == 'Ok')
{

		$iadmin['module'] = lookUpTableReturnValue('x','module','module_id','module',$iadmin['module_id']);
		$_add =md5($iadmin['madd'].$aadmin['admin_id']."100".$iadmin['module']);
		$_edit = md5($iadmin['medit'].$aadmin['admin_id']."250".$iadmin['module']);
		$_delete = md5($iadmin['mdelete'].$aadmin['admin_id']."400".$iadmin['module']);
		$_view = md5($iadmin['mview'].$aadmin['admin_id']."550".$iadmin['module']);
		if ($iadmin['adminrights_id'] == '')
		{
			$q = "select * from adminrights where admin_id='".$aadmin['admin_id']."' and module_id='".$iadmin['module_id']."'";
			$qr = @pg_query($q) or message(pg_errormessage());
			if (@pg_num_rows($qr)>0)
			{
				$r = @pg_fetch_object($qr);
				$iadmin['adminrights_id']=$r->adminrights_id;
			}
		}	
		
		if ($iadmin['adminrights_id'] != '') 
		{
			$q="update adminrights set
						module_id = '".$iadmin['module_id']."',
						madd='$_add',
						medit='$_edit',
						mdelete='$_delete',
						mview='$_view'
					where
						adminrights_id='".$iadmin['adminrights_id']."'";
			$qr = @pg_query($q) or message(pg_errormessage().$q);
		}
		else
		{
			$q = "insert into adminrights (admin_id, module_id, madd, medit, mdelete, mview)
						values ( '".$aadmin['admin_id']."', '".$iadmin['module_id']."',
						 '$_add', '$_edit', '$_delete', '$_view')";
			$qr = @pg_query($q) or message(pg_errormessage());
			
			$q = "select currval('adminrights_adminrights_id_seq')" ;
			$r = fetch_object($q);

			$iadmin['adminrights_id'] = $r->currval;
		}
		if ($qr)
		{
			$module='admin';
			$msg = 'Updated '.$iadmin['module'].' Rights on '.date('m/d/Y g:ia').' by: '.$ADMIN['username'];
			audit($module, $q, $ADMIN['admin_id'], $msg, $iadmin['adminrights_id'] );

			$iadmin = null;
			$iadmin = array();
		}
		
}
?>
<br>
<form name="form1" id="form1" method="post" action="?p=admin">
  <table width="70%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr> 
      <td>Search 
        <input type="text" name="textfield"> <input name="p1" type="submit" id="p1" value="Go">
        <input name="p1" type="button" id="p1" value="Browse" onClick="window.location='?p=admin.browse'">
        <input name="p1" type="submit" id="p1" value="Add User">
        <input name="p122" type="button" id="p1222" value="Change Password" onClick="window.location='?p=admin.cp'">
        <input type="button" name="Submit23" value="Close" onClick="window.location='?p='"></td>
    </tr>
    <tr> 
      <td><hr></td>
    </tr>
  </table>
  <table width="70%" border="0" align="center" cellpadding="1" cellspacing="1" bgcolor="#EFEFEF">
    <tr bgcolor="#CCCCCC"> 
      <td colspan="2" background="../graphics/table0_horizontal.PNG"> <strong><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;<img src="../graphics/post_discussion.gif" width="20" height="20"> 
        Administation :: User Data Entry</font></strong></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">User Id</font></td>
      <td> 
        <?= $aadmin['admin_id'];?>
      </td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td width="23%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Name </font></td>
      <td width="77%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="name" type="text" id="name" value="<?= $aadmin['name'];?>" size="40"  onKeypress="if(event.keyCode==13) {document.getElementById('username').focus();return false;}">
        </font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Username</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="username" type="text" id="username" value="<?= $aadmin['username'];?>" size="40"  onKeypress="if(event.keyCode==13) {form1.usergroup.focus();return false;}">
        </font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Group</font></td>
      <td> 
        <?= lookUpTable2('usergroup','adminusergroup','usergroup','adminusergroup',$aadmin['usergroup']);?>
        <? //= lookUpAssoc('usergroup',array('Loan Staff'=>'L','Payment/Collection'=>'C','Supervisor'=>'S','Administrator'=>'A'),$aadmin['usergroup']);?>
      </td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Branch</font></td>
      <td>
        <select name="branch_id" id="branch_id">
		<option value="0">All Branches</option>
		<?
			$q = "select * from branch where enable='Y' order by branch";
			$qr = @pg_query($q);
			while ($r = @pg_fetch_object($qr))
			{
				if ($aadmin['branch_id'] == $r->branch_id)
				{
					echo "<option value='$r->branch_id' selected>$r->branch</option>";
				}
				else
				{
					echo "<option value='$r->branch_id'>$r->branch</option>";
				}
			}
		?>
		</select>
      </td>
    </tr>
    <?
	if ($aadmin['admin_id'] == '')
	{
	?>
    <tr bgcolor="#FFFFFF"> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Password</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="mpassword" type="password" id="mpassword" value="<?= $aadmin['mpassword'];?>"  onKeypress="if(event.keyCode==13) {form1.mpassword_confirm.focus();return false;}">
        </font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Confirm 
        Password</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="mpassword_confirm" type="password" id="mpassword_confirm" value="<?= $aadmin['mpassword_confirm'];?>"  onKeypress="if(event.keyCode==13) {form1.date_expire.focus();return false;}">
        </font></td>
    </tr>
    <?
	}
	?>
    <tr bgcolor="#FFFFFF"> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Account 
        Expiry</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="date_expire" type="text" id="date_expire" value="<?= ymd2mdy($aadmin['date_expire']);?>"  onKeypress="if(event.keyCode==13) {form1.saveuser.focus();return false;}">
        (mm/dd/yyyy) *Blank if never expires</font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> Enabled/Active</font></td>
      <td> 
        <?= lookUpAssoc('enable',array('Yes'=>'Y','No'=>'N'), $aadmin['enable']);?>
      </td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="2"><input name="p1" type="submit" id="saveuser" value="Save User"></td>
    </tr>
  </table>
  <br>
  <table width="70%" border="0" align="center" cellpadding="1" cellspacing="1" bgcolor="#EFEFEF">
    <tr bgcolor="#0099FF"> 
      <td colspan="4"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>User 
        Rights</strong></font></td>
    </tr>
    <tr bgcolor="#EFEFEF"> 
      <td colspan="4" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Module</strong>:: 
        <?= lookUpTable2('module_id','module','module_id','description',$iadmin['module_id']);?>
        <input type="checkbox" name="madd" id="madd" value="Y"  <?=(chkRights2(lookUpTableReturnValue('x','module','module_id','module',$iadmin['module_id']),'madd',$aadmin['admin_id']) ? 'checked' : '');?>>
        Add 
        <input type="checkbox" name="medit" id="medit" value="Y"  <?=(chkRights2(lookUpTableReturnValue('x','module','module_id','module',$iadmin['module_id']),'medit',$aadmin['admin_id']) ? 'checked' : '');?>>
        Edit 
        <input type="checkbox" name="mdelete" id="mdelete" value="Y"  <?=(chkRights2(lookUpTableReturnValue('x','module','module_id','module',$iadmin['module_id']),'mdelete',$aadmin['admin_id']) ? 'checked' : '');?>>
        Delete 
        <input type="checkbox" name="mview" id="mview" value="Y"  <?=(chkRights2(lookUpTableReturnValue('x','module','module_id','module',$iadmin['module_id']),'mview',$aadmin['admin_id']) ? 'checked' : '');?>>
        View</font> &raquo;&raquo; <img src="../graphics/vb_bullet.gif" width="15" height="15" onClick="checkAll()" alt="Check All ">
        <input name="p1" type="submit" id="p1" value="Ok"></td>
    </tr>
    <tr bgcolor="#CCCCCC"> 
      <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
      <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Module</font></strong></td>
      <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Rights</font></strong></td>
      <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Enable</font></strong></td>
    </tr>
	<?
	if ($aadmin['admin_id'] != '')
	{
			$q = "select module.module, module.description, adminrights.module_id, 
						adminrights.enable, adminrights_id,
						madd, mview, medit, mdelete
					from 
						adminrights, 
						module 
					where 
						module.module_id=adminrights.module_id and
						adminrights.admin_id='".$aadmin['admin_id']."'
					order by
						module.description";
		$qr = @pg_query($q) or message(pg_errormessage().$q);
		$c=0;
		while ($r = @pg_fetch_object($qr))
		{
			$c++;
			$rights='';
			$_add =md5('Y'.$aadmin['admin_id']."100".$r->module);
			$_edit = md5('Y'.$aadmin['admin_id']."250".$r->module);
			$_delete = md5('Y'.$aadmin['admin_id']."400".$r->module);
			$_view = md5('Y'.$aadmin['admin_id']."550".$r->module);
			
			if ($_add == $r->madd)
			{
				$rights = 'Add, ';
			}
			if ($_edit== $r->medit)
			{
				$rights .= 'Edit, ';
			}
			if ($_delete== $r->mdelete)
			{
				$rights .= 'Delete, ';
			}
			if ($_view== $r->mview)
			{
				$rights .= 'View, ';
			}
			
	?>
    <tr bgcolor="#FFFFFF"> 
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><?= $c;?>.</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href='?p=admin&p1=Edit&id=<?=$r->adminrights_id;?>'><?= $r->description;?></a></font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><?= $rights;?></font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><?=($r->enable ? 'Yes':'No');?></font></td>
    </tr>
	<?
		}
	}
	?>
  </table>
</form>
