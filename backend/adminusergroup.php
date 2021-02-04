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

$href = '?p=adminusergroup';

if (!session_is_registered('aadminusergroup'))
{
	session_register('aadminusergroup');
	$aadminusergroup=array();
}

if ($p1=="Close")
{
	session_unregister('aadminusergroup');
	echo "<script> window.location='index.php' </script>";
}
if ($p1=="Save Checked" && $aadminusergroup['status']=='INSERT' && !chkRights2("admin","madd",$ADMIN['admin_id'] ))
{
	message("You have no permission to add data in this module...");

}
elseif ($p1=="Save Checked" && $aadminusergroup['status']=='INSERT')
{
	$c=0;
	while ($c < count($mark))
	{
		if ($adminusergroup[$c]!='')
		{
			if ($usergroup[$c] == '') $usergroup[$c] = 0;
			if ($adminusergroup[$c] == '') $adminusergroup[$c] = 0;
			if ($fee[$c] == '') $fee[$c] = 0;
			
			$q = "insert into adminusergroup (adminusergroup, usergroup, enable)
					values ('".$adminusergroup[$c]."','".$usergroup[$c]."',
							 '".$enable[$c]."')";
			@pg_query($q) or die (pg_errormessage().$q);
		}
		$c++;
	} 
	$aadminusergroup['status']='SAVED';
}
elseif ($p1=="Save Checked" && $aadminusergroup['status']=='LIST' && !chkRights2("admin","madd",$ADMIN['admin_id'] ))
{
	message("You have no permission to add data in this module...");

}
elseif ($p1=="Save Checked" && $aadminusergroup['status']=='LIST')
{
	$c=0;

	while ($c < count($mark))
	{
		$index = $mark[$c]-1;
		if ($adminusergroup[$index]!='')
		{
			@pg_query("update adminusergroup set 
						enable='".$enable[$index]."',
						adminusergroup='".$adminusergroup[$index]."',
						usergroup = '".$usergroup[$index]."'
					where adminusergroup_id='".$adminusergroup_id[$index]."'") or die (pg_errormessage());
		}
		$c++;
	} 
	$aadminusergroup['status']='SAVED';
}
?><br>
<form name="form1" method="post" action="">
  <table width="50%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr> 
      <td nowrap><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Find 
        <input type="text" name="xSearch2" value="<?= $xSearch;?>">
        <input name="p1" type="submit" id="p1" value="Go">
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
        </font> <hr color="#CC3300"></td>
    </tr>
  </table>
  <table width="39%" border="0" cellspacing="1" cellpadding="2" align="center">
    <tr bgcolor="#C8D7E6" background="../graphics/table0_horizontal.PNG"> 
      <td height="31" colspan="4"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><b><font color="#EFEFEF">User 
        Groups Table Setup </font><a name="top"></a></b></font></td>
    </tr>
    <tr bgcolor="#E9E9E9"> 
      <td width="3%"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">#</font></b></td>
      <td width="18%"><a href="<?=$href.'&sort=adminusergroup';?>"><b><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">User 
        Group</font></b></a></td>
      <td><a href="<?=$href.'&sort=adminusergroup';?>"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Code 
        (1) </font></b></a><b></b></td>
      <td width="13%"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        <input type="hidden" name="sort" size="5" value="<?= $sort;?>">
        </font><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Enable</font></b></td>
    </tr>
    <?
	if ($p1=='Insert')
	{
		$aadminusergroup['status']='INSERT';
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
      <td width="18%"> <input type="text" name="adminusergroup[]" size="15" id="<?= 'F'.$c;?>"  onChange="vChk(this)" style="text-align:default"> 
      </td>
      <td><input name="usergroup[]" type="text" id="<?= 'T'.$c;?>"   onChange="vChk(this)" size="1" maxlength="1"> 
      </td>
      <td width="13%"> <select name="enable[]"  id="<?= 'A'.$c;?>"  onChange="vChk(this)">
          <option value="Y">Yes</option>
          <option value="N">No</option>
        </select> </td>
    </tr>
    <?
		}
		?>
    <tr bgcolor="#FFFFFF"> 
      <td colspan=4 height="2" valign="bottom">&nbsp;</td>
    </tr>
    <tr bgcolor="#E9E9E9"> 
      <td colspan=4 height="26"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Saved 
        Table Range</font></td>
    </tr>
    <?
	} //if insert
	else
	{
		$aadminusergroup['status']='LIST';
		$c=0;
	}
	
	$q = "select * from adminusergroup  where 1=1 ";
	
	if ($sort == '' || $sort=='adminusergroup')
	{
		$sort = 'adminusergroup';
	}
	$q .= " order by $sort ";

	$qr = pg_query($q) or die (pg_error());
	$ctr = $c;
	while ($r = pg_fetch_object($qr))
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
        <input type="hidden" name="adminusergroup_id[]" size="5" value="<?= $r->adminusergroup_id;?>">
        <? 
	  echo "$ctr."; 
	  if ($p1!='Insert')
	  {
         echo "<input type='checkbox' name='mark[]' value='$ctr' id='m$ctr'>";
	  }
	  ?>
        </font> </td>
      <td width="18%"> <input name="adminusergroup[]" type="text"  value="<?= $r->adminusergroup;?>" size="15"  id="<?= 'F'.$ctr;?>"  onChange="vChk(this)"   style="text-align:default"> 
      </td>
      <td> <input name="usergroup[]" type="text"  id="<?= 'T'.$ctr;?>"  onChange="vChk(this)" value="<?= $r->usergroup;?>" size="1" maxlength="1"> 
      </td>
      <td width="13%"> <select name="enable[]"  id="<?= 'A'.$ctr;?>"  onChange="vChk(this)">
          <option value="Y"  <?= ($r->enable=='Y' ? 'Selected' :'');?>>Yes</option>
          <option value="N"  <?= ($r->enable !='Y' ? 'Selected' :'');?>>No</option>
        </select> </td>
    </tr>
    <?
	}
	?>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="4"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        <input type="submit" name="p1" value="Save Checked">
        <a href="#top">Go Top</a></font></td>
    </tr>
  </table>
</form>
