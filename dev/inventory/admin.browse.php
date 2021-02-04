<?

	if (!chkRights2('users','mview',$ADMIN['admin_id']))
	{
		echo "<script>window.location='?p=admin.cp'</script>";
		exit;
	}
	
	function userGroup($g)
	{
		if ($g == 'A')
			return 'Administrator';
		elseif ($g == 'C')
			return 'Cashier';
		elseif ($g == 'E')
			return 'Encoder';
		elseif ($g == 'S')
			return 'Supervisor';
		else
			return 'Invalid Group';
			
	}
?>
<form name="form1" method="post" action="">
  <table width="80%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr>
      <td>Search 
        <input type="text" name="textfield">
        <input name="p1" type="submit" id="p1" value="Go">
        <input name="p12" type="button" id="p12" value="Add User" onClick="window.location='?p=admin'">
        <input name="p122" type="button" id="p122" value="Change Password" onClick="window.location='?p=admin.cp'"> 
        <input type="button" name="Submit23" value="Close" onClick="window.location='?p='"></td>
    </tr>
    <tr>
      <td><hr></td>
    </tr>
  </table>
  <table width="80%" border="0" align="center" cellpadding="1" cellspacing="1" bgcolor="#EFEFEF">
    <tr bgcolor="#CCCCCC"> 
      <td colspan="6" background="graphics/table0_horizontal.PNG"><strong><font color="#FFFFFF">Browse 
        Users</font></strong></td>
    </tr>
    <tr bgcolor="#CCCCCC"> 
      <td width="5%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
      <td width="24%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Name</font></strong></td>
      <td width="31%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Username</font></strong></td>
      <td width="18%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Group</font></strong></td>
      <td width="11%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Enable</font></strong></td>
      <td width="11%">&nbsp;</td>
    </tr>
    <?
		$q = "select * from admin order by usergroup, name";
		$qr = @pg_query($q);
		if (!$qr && pg_errno()=='1054')
		{
			 message(pg_errormessage()." Inform Administrator, Add coulumn [usergroup]");
		}
		$c=0;
		while ($r = @pg_fetch_object($qr))
		{
			$c++;
			if ($r->enable == 'N') 
			{
				$bgColor='#FFCCCC';
			}	
			else
			{
				$bgColor='#FFFFFF';
			}
	?>
    <tr bgcolor="<?= $bgColor;?>" onMouseOver="bgColor='#E9E9E9'" onMouseOut="bgColor='<?=$bgColor;?>'"> 
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $c;?>
        . </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $r->name;?>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $r->username;?>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= userGroup($r->usergroup);?>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= ($r->enable=='Y' ? 'Yes' : 'No');?>
        </font></td>
      <td><a href="?p=admin&p1=Load&id=<?=$r->admin_id;?>"><font size="1">Edit</font></a></td>
    </tr>
    <?
		}
	?>
  </table>
</form>
