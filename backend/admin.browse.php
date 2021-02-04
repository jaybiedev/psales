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
		<?= lookUpAssoc('show',array('Show All'=>'','Show Enabled Only'=>'E','Show Disabled Only'=>'D'),$show);?>
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
      <td colspan="7" background="../graphics/table0_horizontal.PNG"><strong><font color="#FFFFFF">Browse 
        Users</font></strong></td>
    </tr>
    <tr bgcolor="#CCCCCC"> 
      <td width="5%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
      <td width="24%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Name</font></strong></td>
      <td width="15%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Username</font></strong></td>
      <td width="16%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Branch</font></strong></td>
      <td width="18%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Group</font></strong></td>
      <td width="11%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Enable</font></strong></td>
      <td width="11%">&nbsp;</td>
    </tr>
    <?
		$q = "select * from admin  where 1=1 and enable = 'Y' ";
		if ($show == 'E')
		{
			$q .= " and enable='Y' ";
		}
		elseif ($show == 'D')
		{
			$q .= " and enable='N' ";
		}
		$q .= " order by usergroup, name";
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
			elseif ($r->date_expire != '' && $r->date_expire!='--' && mdy2ymd($r->date_expire)<date('Y-m-d'))
			{
				$bgColor='#FFCC99';
			}
			else
			{
				$bgColor='#FFFFFF';
			}
			if ($r->branch_id == 0)
			{	
				$branch = 'All Branches';
			}
			else
			{
				$branch =lookUpTableReturnValue('x','branch','branch_id','branch',$r->branch_id);
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
        <?= $branch;?>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= lookUpTableReturnValue('x','adminusergroup','usergroup','adminusergroup',$r->usergroup);?>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= ($r->enable=='Y' ? 'Yes' : 'No');?>
        </font></td>
      <td><font size="1" face="Verdana, Arial, Helvetica, sans-serif"> 
	  <a href="?p=admin&p1=Load&id=<?=$r->admin_id;?>">Edit</a>
		<?
		if(chkRights2('password','medit',$ADMIN['admin_id']))
		{
			echo " | <a href='?p=admin.pwd&id=$r->admin_id'>Pwd</a>";
		}
		?>
	  </font></td>
    </tr>
    <?
		}
	?>
  </table>
</form>
