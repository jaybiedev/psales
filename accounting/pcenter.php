<script>
function vChk(t)
{
	var id = t.id
	var n = id.substring(1)
	document.getElementById('m'+n).checked =1;
}
</script>
<?
$href = '?p=pcenter';

if (!chkRights2("pcenter","mview",$ADMIN['adminId']))
{
	message("You have no permission in this area...");
	exit;
}

if (!session_is_registered('apcenter'))
{
	session_register('apcenter');
	$apcenter=array();
}

/*
if ($p1=="Save Checked" && !chkRights2("pcenter","madd",$ADMIN['adminId']))
	{
		message("You have no permission to modify or add...");
	}
*/	
if ($p1=="Save Checked")
{

	$ctr=0;
	while ($ctr < count($mark))
	{
		$c = $mark[$ctr]-1;
		if ($pcenter[$c]!='')
		{
			if ($pcenter_id[$c] == '')
			{
				$q = "insert into pcenter (pcenter, pcenter_code, enable)
							values ('".$pcenter[$c]."','".$pcenter_code[$c]."','".$enable[$c]."')";

				$qr = @pg_query($q) or message (pg_errormessage());
			}
			else
			{
				pg_query("update pcenter set
						enable='".$enable[$c]."',
						pcenter_code='".$pcenter_code[$c]."',
						pcenter='".$pcenter[$c]."'
					where
						pcenter_id='".$pcenter_id[$c]."'") or message (pg_errormessage());
			}			
		}
		$ctr++;
	} 
	$apcenter['status']='SAVED';
}
?>
<form name="form1" method="post" action="">
  <table width="1%" border="0" cellspacing="1" cellpadding="2" align="center">
    <tr bgcolor="#C8D7E6"> 
      <td height="27" colspan="8" background="../graphics/table_horizontal.PNG"  ><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><b>Setup 
        Profit Center<a name="top"></a></b></font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="8" nowrap><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Find 
        <input type="text" name="xSearch" value="<?= $xSearch;?>">
        <input type="submit" name="p1" value="Go">
        Insert 
        <select name="insertcount">
          <option value="5">5</option>
          <option value="10">10</option>
          <option value="15">15</option>
          <option value="20">20</option>
        </select>
        <input type="submit" name="p1" value="Insert">
        <input type="submit" name="p1" value="List">
        <input type="button" name="p1" value="Close" onClick="window.location='?p='">
        </font></td>
    </tr>
    <tr bgcolor="#E9E9E9"> 
      <td width="4%"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">#</font></b></td>
      <td nowrap><a href="<?=$href.'&sort=pcenter&start=$start&xSearch=$xSearch';?>"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Profit 
        Center</font></b></a></td>
      <td nowrap><a href="<?=$href.'&sort=pcenter&start=$start&xSearch=$xSearch';?>"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">pcenter_code</font></b></a></td>
      <td nowrap><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        <input type="hidden" name="sort" size="5" value="<?= $sort;?>">
        </font><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Enabled</font></b><b></b></td>
    </tr>
    <?
	if ($p1=='Insert')
	{
		$apcenter['status']='INSERT';
		$c=0;
		while ($c < $insertcount)
		{
			$c++;
	?>
    <tr bgcolor="#FFFFFF"> 
      <td align=right nowrap> <font size=1> 
        <?= $c;?>
        <input type="checkbox" name="mark[]" value="<?= $c;?>" id="<?= 'm'.$c;?>">
        <input name="pcenter_id[]" type="hidden" id="pcenter_id[]" size="5">
        </font> </td>
      <td> <input type="text" name="pcenter[]" size="40"  onChange="vChk(this)" id="<?='t'.$c;?>"> </td>
      <td><input type="text" name="pcenter_code[]" size="40"  onChange="vChk(this)" id="<?='t'.$c;?>"></td>
      <td> 
        <?= lookUpAssoc('enable[]',array("Yes"=>"Y","No"=>"N"),'');?>
      </td>
    </tr>
    <?
		}
		?>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="6" height="2" valign="bottom"> <input type="submit" name="p1" value="Save Checked"> 
      </td>
    </tr>
    <tr bgcolor="#E9E9E9"> 
      <td colspan="4" height="26"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Saved 
        Categories</font></td>
    </tr>
    <?
	} //if insert
	else
	{
		$apcenter['status']='LIST';
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
	$q = "select * from pcenter";
	if ($xSearch != '')
	{
		$q .= " where pcenterlike '$xSearch%' ";
	}
	
	if ($sort == '' || $sort=='pcenter')
	{
		$sort = 'pcenter';
	}
	$q .= " order by $sort offset $start limit 20";

	$qr = pg_query($q) or die (pg_errormessage());
	$ctr = $c;
	while ($r = pg_fetch_object($qr))
	{
		$ctr++;
		
	?>
    <tr  bgcolor=<?= ($r->enable=='N')? '#FFCCCC' :'#FFFFFF';?>> 
      <td  align=right nowrap><font size=1> 
        <input type="hidden" name="pcenter_id[]" size="5" value="<?= $r->pcenter_id;?>">
        <? 
	  echo "$ctr."; 
	  if ($p1!='Insert')
	  {
         echo "<input type='checkbox' name='mark[]' value='$ctr' id='m$ctr'>";
	  }
	  ?>
        </font> </td>
      <td> <input name="pcenter[]" type="text" id="<?='t'.$ctr;?>"  onChange="vChk(this)" value="<?= $r->pcenter;?>" size="40"> 
      </td>
      <td><input name="pcenter_code[]" type="text" id="<?='a'.$ctr;?>" onChange="vChk(this)" value="<?= $r->pcenter_code;?>" size="40"></td>
      <td> 
        <?= lookUpAssoc('enable[]',array("Yes"=>"Y","No"=>"N"),$r->enable);?>
      </td>
    </tr>
    <?
	}
	?>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="3" nowrap><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        <input type="submit" name="p1" value="Save Checked">
        <a href="#top">Go Top</a></font> </td>
      <td align=right nowrap> 
        <?
	  	echo "<img src='graphics/redarrow_left.gif'><a href='?p=pcenter&p1=Previous&start=$start&sort=$sort&xSearch=$xSearch'> Previous</a>";
		?>
        <b>|</b> 
        <?
	  	echo "<a href='?p=pcenter&p1=Next&start=$start&sort=$sort&xSearch=$xSearch'> Next </a><img src='graphics/redarrow_right.gif'> ";
		?>
      </td>
    </tr>
  </table>
</form>
