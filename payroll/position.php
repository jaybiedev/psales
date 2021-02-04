<script>
function vChk(t)
{
	var id = t.id
	var n = id.substring(1)

	document.getElementById('m'+n).checked =1;
}
</script>
<?
$href = '?p=position';
require_once('../lib/dbconfig.php');
require_once('../lib/connect.php');

if (!chkRights2("position","mview",$ADMIN['admin_id']))
{
	message("You have no permission in this area...");
	exit;
}

if (!session_is_registered('aposition'))
{
	session_register('aposition');
	$aposition=array();
}


if ($p1=="Save Checked" && !chkRights2("position","madd",$ADMIN['admin_id']))
	{
		message("You have no permission to modify or add...");
	}
if ($p1=="Save Checked")
{

	$ctr=0;
	while ($ctr < count($mark))
	{
		$c = $mark[$ctr]-1;
		if ($position[$c]!='')
		{
			if ($position_id[$c] == '')
			{
				$q = "insert into position (enable, position)
					values
						('".$enable[$c]."','".$position[$c]."')";
				$qr = pg_exec($q) or message (pg_errormessage());
			}
			else
			{
				pg_exec("update position set
						enable='".$enable[$c]."',
						position='".$position[$c]."'
					where
						position_id='".$position_id[$c]."'") or message (pg_errormessage());
			}			
		}
		$ctr++;
	} 
	$aposition['status']='SAVED';
}
?><br>
<form name="form1" method="post" action="">
  <table width="60%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr> 
      <td nowrap colspan="4">
	  <font face="Verdana, Arial, Helvetica, sans-serif" size="2">Find 
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
        <hr color="#CC3300">
        </td>
    </tr>
    <tr bgcolor="#C8D7E6"> 
      <td colspan="4" height="27"  background="../graphics/table_horizontal.PNG" ><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><b> 
        <img src="../graphics/mail.gif" width="16" height="17"> <font color="#EFEFEF">Position 
        Setup</font> <a name="top"></a></b></font></td>
    </tr>
    <tr bgcolor="#E9E9E9"> 
      <td width="8%"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">#</font></b></td>
      <td width="20%" nowrap><a href="<?=$href.'&sort=position&start=$start&xSearch=$xSearch';?>"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Position</font></b></a></td>
      <td nowrap><a href="<?=$href.'&sort=position_code&start=$start&xSearch=$xSearch';?>"><b></b></a></td>
      <td nowrap><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        <input type="hidden" name="sort" size="5" value="<?= $sort;?>">
        </font><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Enabled</font></b><b></b></td>
    </tr>
    <?
	if ($p1=='Insert')
	{
		$aposition['status']='INSERT';
		$c=0;
		while ($c < $insertcount)
		{
			$c++;
	?>
    <tr bgcolor="#FFFFFF" onMouseOver="bgColor='#FFFFCC'" onMouseOut="bgColor='#FFFFFF'"> 
      <td align=right nowrap> <font size=1> 
        <?= $c;?>
        <input type="checkbox" name="mark[]" value="<?= $c;?>" id="<?= 'm'.$c;?>">
        <input name="position_id[]" type="hidden" id="position_id[]" size="5">
        </font> </td>
      <td> <input type="text" name="position[]" size="30"  onChange="vChk(this)" id="<?='t'.$c;?>"> 
      </td>
      <td >&nbsp; </td>
      <td> 
        <?= lookUpAssoc('enable[]',array("Yes"=>"Y","No"=>"N"),'');?>
      </td>
    </tr>
    <?
		}
		?>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="4" height="2" valign="bottom">&nbsp;</td>
    </tr>
    <tr bgcolor="#E9E9E9"> 
      <td colspan="4" height="26"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Saved 
        Positions</font></td>
    </tr>
    <?
	} //if insert
	else
	{
		$aposition['status']='LIST';
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
	$q = "select * from position ";
	if ($xSearch != '')
	{
		$q .= " where position like '$xSearch%' ";
	}
	
	if ($sort == '' || $sort=='position')
	{
		$sort = 'position';
	}
	$q .= " order by $sort " ; // limit $start,10";

	$qr = pg_exec($q) or die (pg_errormessage());
	$ctr = $c;
	while ($r = pg_fetch_object($qr))
	{
		$ctr++;
		
	?>
    <tr  bgcolor=<?= ($r->enable=='N')? '#FFCCCC' :'#FFFFFF';?>> 
      <td  align=right nowrap><font size=1> 
        <input type="hidden" name="position_id[]" size="5" value="<?= $r->position_id;?>">
        <? 
	  echo "$ctr."; 
	  if ($p1!='Insert')
	  {
         echo "<input type='checkbox' name='mark[]' value='$ctr' id='m$ctr'>";
	  }
	  ?>
        </font> </td>
      <td> <input name="position[]" type="text" id="<?='t'.$ctr;?>"  onChange="vChk(this)" value="<?= $r->position;?>" size="30"> 
      </td>
      <td>&nbsp; </td>
      <td> 
        <?= lookUpAssoc('enable[]',array("Yes"=>"Y","No"=>"N"),$r->enable);?>
      </td>
    </tr>
    <?
	}
	?>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="4"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        <input type="submit" name="p1" value="Save Checked">
        </font> </td>
    </tr>
  </table>
</form>
<div align="center">
<?
	echo "<img src='../graphics/redarrow_left.gif'><a href='?p=position&p1=Previous&start=$start&sort=$sort&xSearch=$xSearch'> Previous</a>";
	echo " <b> | </b>";
	echo "<a href='?p=position&p1=Next&start=$start&sort=$sort&xSearch=$xSearch'> Next </a><img src='../graphics/redarrow_right.gif'> ";
?>	
</div>
