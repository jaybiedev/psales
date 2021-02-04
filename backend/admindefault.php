<script>
function vChk(t)
{
	var id = t.id
	var n = id.substring(1)

	document.getElementById('m'+n).checked =1;

}
</script>
<?
if (!chkRights2("admin","mview",$ADMIN['admin_id'] ) )
{
	message("You have no permission in this module...");
	exit;
}


$href = '?p=admindefault';

if (!session_is_registered('aadmindefault'))
{
	session_register('aadmindefault');
	$aadmindefault=array();
}

if ($p1=="Close")
{
	session_unregister('aadmindefault');
	echo "<script> window.location='index.php' </script>";
}
if ($p1=="Save Checked" && $usergroup=='')
{
	message("No User Group Selected...");
}
elseif ($p1=="Save Checked" && $aadmindefault['status']=='INSERT' && !chkRights2("admin","madd",$ADMIN['admin_id'] ) )
{
	message("No permission to ADD data in this module...");
}
elseif ($p1=="Save Checked" && $aadmindefault['status']=='LIST' && !chkRights2("admin","medit",$ADMIN['admin_id'] ) )
{
	message("No permission to EDIT data in this module...");
}
elseif ($p1=="Save Checked" && $aadmindefault['status']=='INSERT')
{
	$c=0;
//	print_r($module_id);
//	echo "here ";exit;
	while ($c < count($mark))
	{
		if ($module_id[$c]!='')
		{
			if ($module_id[$c] == '') $module_id[$c] = 0;
			
			$q = "insert into admindefault (usergroup, module_id, madd, medit, mdelete, mview, enable)
					values ('$usergroup','".$module_id[$c]."','".$madd[$c]."',
							 '".$medit[$c]."','".$mdelete[$c]."','".$mview[$c]."','".$enable[$c]."')";
			@pg_query($q) or die (pg_errormessage().$q);
		}
		$c++;
	} 
	$aadmindefault['status']='SAVED';
}
elseif ($p1=="Save Checked" && $aadmindefault['status']=='LIST')
{
	$c=0;

	while ($c < count($mark))
	{
		$index = $mark[$c]-1;
		if ($module_id[$index]!='')
		{
			@pg_query("update admindefault set 
						enable='".$enable[$index]."',
						usergroup='$usergroup',
						module_id='".$module_id[$index]."',
						madd = '".$madd[$index]."',
						medit='".$medit[$index]."',
						mdelete='".$mdelete[$index]."',
						mview='".$mview[$index]."'
					where admindefault_id='".$admindefault_id[$index]."'") or die (pg_errormessage());
		}
		$c++;
	} 
	$aadmindefault['status']='SAVED';
}
?><br>
<form name="form1" id="form1" method="post" action="">
  <table width="70%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr> 
      <td><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Usergroup 
	  <select id="usergroup" name="usergroup" onChange="document.getElementById('go').click()">
	  <?
	  $q= "select * from adminusergroup where enable='Y' order by adminusergroup";
	  $qr = @pg_query($q);
	  
	  while ($r = @pg_fetch_object($qr))
	  {
	  	if ($r->usergroup == $usergroup)
		{
	  		echo "<option value=$r->usergroup selected>$r->adminusergroup ($r->usergroup)</option>";
		}
		else
		{
	  		echo "<option value=$r->usergroup>$r->adminusergroup ($r->usergroup)</option>";
		}
	  }
	  ?>
	  </select>
        <input name="p1" type="submit" id="go" value="Go">
        Insert 
        <select name="insertcount">
          <option value="5">5</option>
          <option value="10">10</option>
          <option value="15">15</option>
          <option value="20">20</option>
        </select>
        <input name="p1" type="submit" id="p1" value="Insert">
        <input name="p1" type="submit" id="p1" value="List">
        <input name="p1" type="button" id="p1" onClick="window.location='?p='" value="Close">
        </font> 
        <hr color="#CC3300"></td>
    </tr>
  </table>
  <table width="39%" border="0" cellspacing="1" cellpadding="2" align="center">
    <tr bgcolor="#C8D7E6" background="../graphics/table0_horizontal.PNG"> 
      <td height="31" colspan="7"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><b><font color="#EFEFEF">User 
        Default Rights Table Setup </font><a name="top"></a></b></font></td>
    </tr>
    <tr bgcolor="#E9E9E9"> 
      <td width="3%"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">#</font></b></td>
      <td width="18%"><a href="<?=$href.'&sort=module_id';?>"><b><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">Module</font></b></a></td>
      <td width="21%"><a href="<?=$href.'&sort=module_id';?>"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Add</font></b></a></td>
      <td><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Edit</font></b></td>
      <td><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Delete</font></b></td>
      <td><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">View</font></b></td>
      <td width="13%"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        <input type="hidden" name="sort" size="5" value="<?= $sort;?>">
        </font><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Enable</font></b></td>
    </tr>
    <?
	if ($p1=='Insert')
	{
		$aadmindefault['status']='INSERT';
		$c=0;
		while ($c < $insertcount)
		{
			$c++;
	?>
    <tr bgcolor="#FFFFFF"> 
      <td width="3%" align=right nowrap> <font size=1> 
        <?= $c;?>
        <input type="checkbox" name="mark[]" value="<?= $c;?>" id="m<?= $c;?>">
        </font> </td>
      <td width="18%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= lookUpTable2('module_id[]','module','module_id','description','');?>
        </font> </td>
      <td width="21%"><select name="madd[]" id="<?= 'A'.$c;?>"  onChange="vChk(this)">
          <option value="N">No</option>
          <option value="Y">Yes</option>
        </select></td>
      <td><select name="medit[]"  id="<?= 'A'.$c;?>"  onChange="vChk(this)">
          <option value="N">No</option>
          <option value="Y">Yes</option>
        </select> </td>
      <td><select name="mdelete[]"  id="<?= 'A'.$c;?>"  onChange="vChk(this)">
          <option value="N">No</option>
          <option value="Y">Yes</option>
        </select></td>
      <td><select name="mview[]"  id="<?= 'A'.$c;?>"   onChange="vChk(this)">
          <option value="N">No</option>
          <option value="Y">Yes</option>
        </select></td>
      <td width="13%"> <select name="enable[]"  id="<?= 'A'.$c;?>"  onChange="vChk(this)">
          <option value="Y">Yes</option>
          <option value="N">No</option>
        </select> </td>
    </tr>
    <?
		}
		?>
    <tr bgcolor="#FFFFFF"> 
      <td colspan=7 height="2" valign="bottom">&nbsp;</td>
    </tr>
    <tr bgcolor="#E9E9E9"> 
      <td colspan=7 height="26"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Saved 
        User Group Rights</font></td>
    </tr>
    <?
	} //if insert
	else
	{
		$aadmindefault['status']='LIST';
		$c=0;
	}

	$q = "select * from admindefault  where usergroup='$usergroup'";
	
	if ($sort == '' || $sort=='module_id')
	{
		$sort = 'module_id';
	}
	$q .= " order by $sort ";

	$qr = @pg_query($q) or message (pg_errormessage());
	$ctr = $c;
	while ($r = @pg_fetch_object($qr))
	{
		$ctr++;
		if ($ctr %10 == 0)
		{
			echo "<tr bgcolor='#FFFFFF'> 
				 	<td colspan=10><input type='submit' name='p1' value='Save Checked'> <a href='#top'><font face='Verdana' size=2>Top</font></a></td></tr>";

		}
	?>
    <tr  bgcolor=<?= ($r->enable=='N')? '#FFCCCC' :'#FFFFFF';?>> 
      <td width="3%" align=right nowrap><font size=1> 
        <input type="hidden" name="admindefault_id[]" size="5" value="<?= $r->admindefault_id;?>">
        <? 
	  echo "$ctr."; 
	  if ($p1!='Insert')
	  {
         echo "<input type='checkbox' name='mark[]' value='$ctr' id='m$ctr'>";
	  }
	  ?>
        </font> </td>
      <td width="18%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= lookUpTable2('module_id[]','module','module_id','description',$r->module_id);?>
        </font> </td>
      <td width="21%"><select name="madd[]"   id="<?= 'A'.$ctr;?>"  onChange="vChk(this)">
          <option value="Y"  <?= ($r->madd=='Y' ? 'Selected' :'');?>>Yes</option>
          <option value="N"  <?= ($r->madd!='Y' ? 'Selected' :'');?>>No</option>
        </select> </td>
      <td><select name="medit[]"   id="<?= 'A'.$c;?>"   onChange="vChk(this)">
          <option value="Y"  <?= ($r->medit=='Y' ? 'Selected' :'');?>>Yes</option>
          <option value="N"  <?= ($r->medit!='Y' ? 'Selected' :'');?>>No</option>
        </select> </td>
      <td><select name="mdelete[]"    id="<?= 'A'.$c;?>"   onChange="vChk(this)">
          <option value="Y"  <?= ($r->mdelete=='Y' ? 'Selected' :'');?>>Yes</option>
          <option value="N"  <?= ($r->mdelete!='Y' ? 'Selected' :'');?>>No</option>
        </select></td>
      <td><select name="mview[]"   id="<?= 'A'.$c;?>"  onChange="vChk(this)">
          <option value="Y"  <?= ($r->mview=='Y' ? 'Selected' :'');?>>Yes</option>
          <option value="N"  <?= ($r->mview!='Y' ? 'Selected' :'');?>>No</option>
        </select></td>
      <td width="13%"> <select name="enable[]"  id="<?= 'A'.$ctr;?>"  onChange="vChk(this)">
          <option value="Y"  <?= ($r->enable ? 'Selected' :'');?>>Y</option>
          <option value="N"  <?= (!$r->enable ? 'Selected' :'');?>>N</option>
        </select> </td>
    </tr>
    <?
	}
	?>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="7"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        <input type="submit" name="p1" value="Save Checked">
        <a href="#top">Go Top</a></font></td>
    </tr>
  </table>
</form>
