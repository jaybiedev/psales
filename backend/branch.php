<script>
function vChk(t)
{
	var id = t.id
	var n = id.substring(1)
	var mid = eval("this.form1.m"+n)
	mid.checked = true
}
</script>
<?
$href = '?p=branch';

if (!chkRights2("branch","mview",$ADMIN['admin_id']))
{
	message("You have no permission in this area...");
	exit;
}

if (!session_is_registered('abranch'))
{
	session_register('abranch');
	$abranch=array();
}


if ($p1=="Save Checked" && !chkRights2("branch","madd",$ADMIN['admin_id']))
	{
		message("You have no permission to modify or add...");
	}
if ($p1=="Save Checked")
{

	$ctr=0;
	while ($ctr < count($mark))
	{
		$c = $mark[$ctr]-1;
		if ($branch[$c]!='')
		{
			if ($init_balance[$c] == '') $init_balance[$c] = 0;

			if ($branch_id[$c] == '')
			{
				$q = "insert into branch (enable, branch, branch_code, branch_address)
					values
						('".$enable[$c]."','".$branch[$c]."','".$branch_code[$c]."','".$branch_address[$c]."')";
				$qr = @pg_exec($q) or message (pg_errormessage());
			}
			else
			{
				$q = "update branch set
						enable='".$enable[$c]."',
						branch='".$branch[$c]."',
						branch_code='".$branch_code[$c]."',
						branch_address = '".$branch_address[$c]."'
					where
						branch_id='".$branch_id[$c]."'";
					
				@pg_query($q) or message (pg_errormessage());
			}			
		}
		$ctr++;
	} 
	$abranch['status']='SAVED';
}
?><br>
<form name="form1" method="post" action="">
  <table width="75%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr> 
      <td nowrap><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Find 
        <input type="text" name="xSearch" value="<?= $xSearch;?>">
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
        </font>
        <hr color="#CC3300"></td>
    </tr>
  </table>
  <table width="75%" border="0" cellspacing="1" cellpadding="1" bgcolor="#EFEFEF" align="center">
    <tr bgcolor="#C8D7E6"> 
      <td height="27" colspan="5" background="../graphics/table0_horizontal.PNG"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><b> 
        <img src="../graphics/mail.gif" width="16" height="17"> Branch Location 
        Setup <a name="top"></a></b></font></td>
    </tr>
    <tr bgcolor="#E9E9E9"> 
      <td width="8%"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">#</font></b></td>
      <td width="10%" nowrap><a href="<?=$href.'&sort=branch&start=$start&xSearch=$xSearch';?>"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Branch</font></b></a></td>
      <td width="10%" nowrap><a href="<?=$href.'&sort=branch_code&start=$start&xSearch=$xSearch';?>"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Code</font></b></a></td>
      <td nowrap><a href="<?=$href.'&sort=branch_address&start=$start&xSearch=$xSearch';?>"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Address</font></b></a></td>
      <td nowrap><b></b><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        <input type="hidden" name="sort" size="5" value="<?= $sort;?>">
        </font><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Enabled</font></b><b></b></td>
    </tr>
    <?
	if ($p1=='Insert')
	{
		$abranch['status']='INSERT';
		$c=0;
		while ($c < $insertcount)
		{
			$c++;
	?>
    <tr bgcolor="#FFFFFF" onMouseOver="bgColor='#FFFFCC'" onMouseOut="bgColor='#FFFFFF'"> 
      <td align=right nowrap> <font size=1> 
        <?= $c;?>
        <input type="checkbox" name="mark[]" value="<?= $c;?>" id="<?= 'm'.$c;?>">
        <input name="branch_id[]" type="hidden" id="branch_id[]" size="5">
        </font> </td>
      <td> <input type="text" name="branch[]" size="30"  onChange="vChk(this)" id="<?='t'.$c;?>"> 
      </td>
      <td><input type="text" name="branch_code[]" size="5"  onChange="vChk(this)" id="<?='d'.$c;?>"></td>
      <td ><input name="branch_address[]2" type="text" id="<?='k'.$c;?>"  onChange="vChk(this)" size="40" maxlength="40"> 
      </td>
      <td > 
        <?= lookUpAssoc('enable[]',array("Yes"=>"t","No"=>"f"),'');?>
      </td>
    </tr>
    <?
		}
		?>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="5" height="2" valign="bottom">&nbsp;</td>
    </tr>
    <tr bgcolor="#E9E9E9"> 
      <td colspan="5" height="26"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Saved 
        Categories</font></td>
    </tr>
    <?
	} //if insert
	else
	{
		$abranch['status']='LIST';
		$c=0;
	}
	if ($p1=='List') 
	{
		$start=0;
		$xSearch='';
	}	
	if ($start == '') $start=0;
	if ($p1=='Next') $start = $start + 10;
	if ($p1=='Previous') $start = $start - 10;
	if ($start < 0) $start=0;	
	$q = "select * from branch ";
	if ($xSearch != '')
	{
		$q .= " where branch like '$xSearch%' ";
	}
	
	if ($sort == '' || $sort=='branch')
	{
		$sort = 'branch';
	}
	$q .= " order by $sort offset $start limit 10";

	$qr = pg_exec($q) or die (pg_errormessage());
	$ctr = $c;
	while ($r = pg_fetch_object($qr))
	{
		$ctr++;
		
	?>
    <tr  bgcolor=<?= ($r->enable=='N')? '#FFCCCC' :'#FFFFFF';?>> 
      <td  align=right nowrap><font size=1> 
        <input type="hidden" name="branch_id[]" size="5" value="<?= $r->branch_id;?>">
        <? 
	  echo "$ctr."; 
	  if ($p1!='Insert')
	  {
         echo "<input type='checkbox' name='mark[]' value='$ctr' id='m$ctr'>";
	  }
	  ?>
        </font> </td>
      <td> <input name="branch[]" type="text" id="<?='t'.$ctr;?>"  onChange="vChk(this)" value="<?= $r->branch;?>" size="30"> 
      </td>
      <td><input name="branch_code[]" type="text"  id="<?='d'.$ctr;?>"  onChange="vChk(this)" value="<?= $r->branch_code;?>" size="5"></td>
      <td><input name="branch_address[]" type="text" id="<?='k'.$ctr;?>"  onChange="vChk(this)" value="<?=$r->branch_address;?>" size="40" maxlength="40"> 
      </td>
      <td> 
        <?= lookUpAssoc('enable[]',array("Yes"=>"t","No"=>"f"),$r->enable);?>
      </td>
    </tr>
    <?
	}
	?>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="5"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        <input type="submit" name="p1" value="Save Checked">
        </font> </td>
    </tr>
  </table>
</form>
<div align="center">
<?
	echo "<img src='../graphics/redarrow_left.gif'><a href='?p=branch&p1=Previous&start=$start&sort=$sort&xSearch=$xSearch'> Previous</a>";
	echo " <b> | </b>";
	echo "<a href='?p=branch&p1=Next&start=$start&sort=$sort&xSearch=$xSearch'> Next </a><img src='../graphics/redarrow_right.gif'> ";
?>	
</div>
