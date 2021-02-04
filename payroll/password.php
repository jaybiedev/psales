<html>
<head>
<title>Password</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script>
function vPass2() {
	if (this.form2.newpass1.value != this.form2.newpass2.value) {
		alert("Please verify new password.")
	}	
}

</script>
</head>

<body bgcolor="#FFFFFF" text="#000000">
<? 
//echo "p2. $p2";
include_once("connect.php");
/*
if ($p2=="" or $p2=="Add New User") {
	if (!chkRights2("users","madd",$admin->adminId)) {
		if (!chkRights2("users","medit",$admin->adminId)) {
			message("You don't have priveledge in this area..");	
			exit;
		}
		else {
			$p2="Change Password";
		}	
	}	
}
*/
if ($p2=="") {

?>
<form name="form1" method="post" action="?p=password">
  <table width="75%" border="0" align="center" cellpadding="1" cellspacing="1" bgcolor="#FFFFFF">
    <tr> 
      <td colspan="2" height="28"><div align="center"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><b><font color="#000000" size="5" face="Times New Roman, Times, serif">Password 
          Setup<br><hr></font></b></font></div></td>
    </tr>
    <tr> 
      <td width="45%" align="right"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Username</font></td>
      <td width="55%"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">
        <input type="text" readonly name="textfield" size="30" value="<?= $admin->username;?>">
        </font></td>
    </tr>
    <tr> 
      <td colspan="2"> <div align="center"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
          <input type="submit" name="p2" value="Change Password">
          <input type="submit" name="p2" value="Add New User">
          <input type="submit" name="p2" value="List Users">
          </font></div></td>
    </tr>
  </table>
</form>
<?
	} //if p2==""
	
	if ($p2=="Go Change My Password") {
	
		$verifyoldpass = md5($oldpass);
		if ($verifyoldpass != $admin->mpassword) message(" Old Password does not match. Please verify");
		else {
	
			$mpassword = md5($newpass1);
			$q = "update admin set mpassword='$mpassword' where adminId='$admin->adminId'";
			$qr = mysql_query($q) or die (mysql_error());
			if ($qr) message("Password Changed.");
		}
	}
	if ($p2=="Change Password") {
?>
<form name="form2" method="post" action="?p=password">
  <table width="50%" border="0" align="center" cellpadding="2" cellspacing="1" bgcolor="#FFFFFF">
    <tr> 
      <td height="33" colspan="2" align=center><font face="Times New Roman, Times, serif" size="5"><b>Change 
        Password<br><hr></b></font></td>
    </tr>
    <tr> 
      <td width="26%" align="center"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">User 
        Name</font></td>
      <td width="74%"> <font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        <input type="text" readonly name="textfield2" size="30" value="<?= $admin->username;?>">
        </font></td>
    </tr>
    <tr> 
      <td width="26%" nowrap align="center"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Old 
        Password</font></td>
      <td width="74%" nowrap> <font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        <input type="password" name="oldpass">
        </font></td>
    </tr>
    <tr> 
      <td width="26%" nowrap align="center"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">New 
        Password</font></td>
      <td width="74%" nowrap> <font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        <input type="password" name="newpass1">
        </font></td>
    </tr>
    <tr> 
      <td width="26%" nowrap align="center"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Confirm 
        New Password</font></td>
      <td width="74%" nowrap> <font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        <input type="password" name="newpass2" onBlur="vPass2()">
        </font></td>
    </tr>
    <tr> 
      <td colspan="2" align="center"> <font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        <input type="submit" name="p2" value="Go Change My Password">
        <input type="button" name="Submit4" value="Close" onClick="window.location='index.php'">
        </font></td>
    </tr>
  </table>
</form>
<?
	} //p2==change password
	//echo "here $p2 $adminId";
	if ($p2=="Toggle User") {
		if (!chkRights2("users","medit",$admin->adminId)) {
			if (!chkRights2("users","mdelete",$admin->adminId)) {
				message("You don't have priveledge in this area..");	
				exit;
			}	
		}
		if ($enable=="N"){
			mysql_query("update admin set enable='' where adminId='$adminId'") or die (mysql_error());
		}
		else {
			mysql_query("update admin set enable='N' where adminId='$adminId'") or die (mysql_error());
		}
		if ($backto!="") {
			$p2=$backto;
			//echo "<script> window.location='index.php?p=menu&p1=password&p2=$backto' </script>";
		}
	}
	if ($p2=="Create User" && $adminId==null) {
		if ($mpassword == $mpassword2 && $name != null && $username!=null &&$mpassword!=null) {
			$mpass = md5($mpassword);

			$q = "insert into admin set name='$name', username='$username', mpassword='$mpass'";
			mysql_query($q);
			$q = "select LAST_INSERT_ID() as adminId from admin";
			$qr = mysql_query($q) or die (mysql_error());
			$r = mysql_fetch_object($qr);
			$adminId = $r->adminId;
			message("User $username no. $adminId has been created. Please provide user rights...");
			$p2="Add New User";
		}	
		else {
			if ($name == null) message("Please provide NAME...");
			elseif ($username == null) message("Please provide USER NAME...");
			elseif ($mpassword == null) message("Please provide PASSWORD...");
			else message("Password does not match, please confirm...");
			$p2="Add New User";
		}	
		
	}
	elseif ($p2=="Create User" && $adminId!=null) message("User has already been added...");
	//echo "p2 $p2 ";
	if ($p2=="Post Rights" && $adminId!=null) {
		if (!chkRights2("users","madd",$admin->adminId)) {
			if (!chkRights2("users","medit",$admin->adminId)) {
				message("You don't have priveledge in this area..");	
				exit;
			}	
		}
		//encryption
		//echo "addd   $mAdd $mEdit  $mDelete  $mView"  ;
		$row_id=0;
		$madd =md5($mAdd.$adminId."100".$module);
		$medit = md5($mEdit.$adminId."250".$module);
		$mdelete = md5($mDelete.$adminId."400".$module);
		$mview = md5($mView.$adminId."550".$module);
		$qr = mysql_query("select * from adminrights where module='$module' and adminId='$adminId'") or die (mysql_error());
		if (mysql_num_rows($qr) != 0) {
			$rc = mysql_fetch_object($qr);
			$row_id=$rc->row_id;
			mysql_query("update adminrights set module='$module',adminId='$adminId',
				madd='$madd',medit='$medit',mdelete='$mdelete',mview='$mview', enable='' 
				where row_id='$row_id'") 
				or die (mysql_error());
		}
		else {
			mysql_query("insert into adminrights set module='$module',adminId='$adminId',
				madd='$madd',medit='$medit',mdelete='$mdelete',mview='$mview'") 
				or die (mysql_error());
		}
		$p2="Add New User";
	}
	elseif ($p2=="Post Rights" && $adminId=="") {
		message("User not created....");
		$p2="Add New User";
	}
	if ($p2=="Delete") {
		?>
<?
	}
	if ($p2=="Toggle Rights") {
		if (!chkRights2("users","medit",$admin->adminId)) {
			message("You don't have priveledge in this area..");	
			exit;
		}
		if ($enable=="N") {
			$qd=mysql_query("update adminrights set enable='' where row_id='$row_id'") or die (mysql_error());
		}	
		else {
			$qd=mysql_query("update adminrights set enable='N' where row_id='$row_id'") or die (mysql_error());
		}	
		if (!$qd) message("Failed to delete record....");
		else ("User rights deleted...");
		$p2="Add New User";
	}
	if ($p2=="Add New User") {
	?>
<form name="form3" method="post" action="?p=password">
  <table width="75%" border="0" align="center" cellspacing="1" bgcolor="#FFFFFF">
    <tr bgcolor="#CCCCCC"> 
      <td colspan="3" height="25"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><b>Add 
        User</b></font></td>
    </tr>
    <tr bgcolor="#E9E9E9"> 
      <td width="39%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        Name</font></td>
      <td width="61%" colspan="2"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input type="text" name="name" size="30" value="<?= $name;?>">
        <input type="hidden" name="adminId" size="5" value="<?= $adminId;?>">
        </font></td>
    </tr>
    <tr bgcolor="#E9E9E9"> 
      <td width="39%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">User 
        Name</font></td>
      <td width="61%" colspan="2"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input type="text" name="username" size="30" value="<?= $username;?>">
        </font></td>
    </tr>
    <?
	 if ($adminId == "") {
	?>
    <tr bgcolor="#E9E9E9"> 
      <td width="39%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Password</font></td>
      <td width="61%" colspan="2"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input type="password" name="mpassword">
        </font></td>
    </tr>
    <tr bgcolor="#E9E9E9"> 
      <td width="39%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Confirm 
        Password </font></td>
      <td width="61%" colspan="2"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input type="password" name="mpassword2">
        </font></td>
    </tr>
    <tr bgcolor="#E9E9E9"> 
      <td colspan="3"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;</font><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input type="submit" name="p2" value="Create User">
        <input type="submit" name="p2" value="Close">
        </font></td>
    </tr>
    <?
	}
	else {
		$q = "select * from adminrights where adminId='$adminId'";
		$qr = mysql_query($q) or die (mysql_error());
		if (mysql_num_rows($qr)==0) $message="No user rights installed";
	?>
    <tr bgcolor="#E9E9E9"> 
      <td colspan="3">&nbsp;</td>
    </tr>
    <tr bgcolor="#CCCCCC"> 
      <td colspan="3"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><b>User 
        Rights </b></font></td>
    </tr>
    <tr bgcolor="#E9E9E9"> 
      <td colspan="3" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Menu 
        Rights 
        <select name="module">
          <option value="transaction">Transaction</option>
          <option value="paymast">Employee Master</option>
          <option value="setupfiles">Setup Files</option>
          <option value="incometype">Income Types</option>
          <option value="deductiontype">Deduction Types</option>
          <option value="reports">Reports</option>
          <option value="configuration">Configuration</option>
          <option value="setup">Setup</option>
          <option value="users">Users</option>
        </select>
        </font><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input type="checkbox" name="mAdd" value="Y">
        Add 
        <input type="checkbox" name="mEdit" value="Y">
        Edit 
        <input type="checkbox" name="mDelete" value="Y">
        Delete 
        <input type="checkbox" name="mView" value="Y">
        View 
        <input type="submit" name="p2" value="Post Rights">
        </font></td>
    </tr>
    <?
	} //if adminId
	?>
    <tr bgcolor="#E9E9E9"> 
      <td colspan="3" nowrap>&nbsp;</td>
    </tr>
    <tr bgcolor="#CCCCCC"> 
      <td colspan="3" nowrap><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><b>Installed 
        User Rights 
        <?=$message;?>
        </b></font></td>
    </tr>
    <?
	$ctr =0;
	while ($r = mysql_fetch_object($qr)) {
		$user_rights="";
		$ctr++;
		$madd =md5("Y".$adminId."100".$r->module);
		$medit = md5("Y".$adminId."250".$r->module);
		$mdelete = md5("Y".$adminId."400".$r->module);
		$mview = md5("Y".$adminId."550".$r->module);
		if ($r->madd==$madd) $user_rights ="Add ";
		if ($r->medit==$medit) $user_rights .="Edit ";
		if ($r->mdelete==$mdelete) $user_rights .="Delete ";
		if ($r->mview==$mview) $user_rights .="View ";
	?>
    <tr bgcolor="#E9E9E9"> 
      <td width="39%" nowrap> 
        <?= $r->module;?>
        <font face="Verdana, Arial, Helvetica, sans-serif" size="2">&nbsp; </font></td>
      <td width="30%" nowrap> 
        <?= $user_rights;?>
      </td>
      <td width="31%" nowrap><font face="Verdana, Arial, Helvetica, sans-serif" size="2">
	 	 <a href="index.php?p=menu&p1=password&p2=Toggle Rights&enable=<?= $r->enable;?>&row_id=<?= $r->row_id;?>&adminId=<?=$adminId;?>&name=<?=$name;?>&username=<?=$username;?>"><? echo ($r->enable=="N" ?"Enable":"Disable");?></a>
	  </font></td>
    </tr>
    <?
	}
	?>
  </table>
</form>
	<?
}
if ($p2=="List Users") {
		if ($hide==1) {
			$q="select * from admin where !(enable='N')";
		}
		else {
			$q="select * from admin";
		}	
		$qr=mysql_query($q) or die (mysql_error());
		
	?>
<table width="75%" border="0" align="center" cellpadding="3" cellspacing="1" bgcolor="#C8D7E6">
  <tr bgcolor="#0099CC"> 
    <td colspan="5" height="27"> 
      <p><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><b>List of 
        Users</b></font></p>
    </td>
  </tr>
  <tr bgcolor="#CCCCCC"> 
    <td height="19" width="7%"><b></b></td>
    <td height="19" width="48%"><b><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Name</font></b></td>
    <td height="19" bgcolor="#CCCCCC"><b><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Username</font></b><b></b></td>
    <td height="19" bgcolor="#CCCCCC" colspan="2"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><b> 
      <a href="index.php?p=menu&p1=password&p2=List%20Users&hide=<?=($hide==1 ? 0 :1);?>">
      <?= ($hide==1 ? "Show All" : "Hide Disabled");?>
      </a></b></font></td>
  </tr>
  <?
  $ctr =0;
  while ($r = mysql_fetch_object($qr)) {
  	$ctr++;
  ?>
  <tr bgcolor=<?= ($ctr%2==0 ? "#FFFFFF" :"#E9E9E9");?>> 
    <td width="7%"> 
      <div align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $ctr;?>.
        </font></div>
    </td>
    <td width="48%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= $r->name;?>
      </font></td>
    <td width="26%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= $r->username;?>
      </font></td>
    <td width="9%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="index.php?p=menu&p1=password&p2=Toggle User&backto=List Users&adminId=<?= $r->adminId;?>&enable=<?= $r->enable;?>"> 
      <?= ($r->enable=='N'? "Enable" :"Disable");?>
      </a> </font></td>
    <td width="10%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=password&p2=Add%20New%20User&adminId=<?=$r->adminId;?>&name=<?=$r->name;?>&username=<?=$r->username;?>">Modify</a></font></td>
  </tr>
  <?
  }
  ?>
</table>
<?
	}
?>
</body>
</html>
