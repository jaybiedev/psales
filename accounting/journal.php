<script>
function vChk(t)
{
	var id = t.id
	var n = id.substring(1)
	document.getElementById('m'+n).checked =1;
}
</script>
<?
$href = '?p=journal';

if (!session_is_registered('ajournal'))
{
	session_register('ajournal');
	$ajournal=array();
}

/*
if ($p1=="Save Checked" && !chkRights2("journal","madd",$ADMIN['adminId']))
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
		if ($journal[$c]!='')
		{

			if ($journal_id[$c] == '')
			{

				$q = "insert into journal (enable, journal_code, journal)
							values ('".$enable[$c]."', '".$journal_code[$c]."',  '".$journal[$c]."'	)";
				$qr = @pg_query($q) or message (pg_errormessage());
				if (@pg_errno() == 1062)
				{
					message("journal name already exists...");
				}		
			}
			else
			{
				@pg_query("update journal set
						enable='".$enable[$c]."',
						journal_code='".$journal_code[$c]."',
						journal='".$journal[$c]."'
					where
						journal_id='".$journal_id[$c]."'") or message (pg_errormessage());
			}			
		}
		$ctr++;
	} 
	$ajournal['status']='SAVED';
}
?>
<form name="form1" method="post" action="" style="margin:10px">
  <table width="60%" border="0" cellspacing="1" cellpadding="2" bgcolor="#EFEFEF" align="center">
    <tr bgcolor="#C8D7E6" background="../graphics/table_horizontal.PNG"> 
      <td colspan="4" height="24"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><b><font color="#FFFFCC">Setup 
        Journals</font><a name="top"></a></b></font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="4" nowrap><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Find 
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
        </font> 
        <hr>
      </td>
    </tr>
    <tr bgcolor="#E9E9E9"> 
      <td width="11%"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">#</font></b></td>
      <td width="20%" nowrap><a href="<?=$href.'&sort=journal&start=$start&xSearch=$xSearch';?>"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Journal</font></b></a></td>
      <td width="21%" nowrap><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Code</font></b></td>
      <td width="19%" nowrap><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        <input type="hidden" name="sort" size="5" value="<?= $sort;?>">
        </font><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Enabled</font></b><b></b></td>
    </tr>
    <?
	if ($p1=='Insert')
	{
		$ajournal['status']='INSERT';
		$c=0;
		while ($c < $insertcount)
		{
			$c++;
	?>
    <tr bgcolor="#FFFFFF"> 
      <td align=right nowrap> <font size=1> 
        <?= $c;?>
        <input type="checkbox" name="mark[]" value="<?= $c;?>" id="<?= 'm'.$c;?>">
        <input name="journal_id[]" type="hidden" id="journal_id[]" size="5">
        </font> </td>
      <td> 
        <input type="text" name="journal[]" size="30"  onChange="vChk(this)" id="<?='t'.$c;?>">
      </td>
      <td> 
        <input type="text" name="journal_code[]" size="5"  onChange="vChk(this)" id="<?='t'.$c;?>" maxlength="5">
      </td>
      <td> 
        <?= lookUpAssoc('enable[]',array("Yes"=>"Y","No"=>"N"),'');?>
      </td>
    </tr>
    <?
		}
		?>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="4" height="2" valign="bottom"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        <input type="submit" name="p1" value="Save Checked">
        </font></td>
    </tr>
    <tr bgcolor="#E9E9E9"> 
      <td colspan="4" height="26"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Saved 
        Categories</font></td>
    </tr>
    <?
	} //if insert
	else
	{
		$ajournal['status']='LIST';
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
	$q = "select * from journal ";
	if ($xSearch != '')
	{
		$q .= " where journal like '$xSearch%' ";
	}
	
	if ($sort == '' || $sort=='journal')
	{
		$sort = 'journal';
	}
	$q .= " order by $sort offset $start limit 10";

	$qr = pg_query($q) or die (pg_errormessage());
	$ctr = $c;
	while ($r = pg_fetch_object($qr))
	{
		$ctr++;
		
	?>
    <tr  bgcolor=<?= ($r->enable=='N')? '#FFCCCC' :'#FFFFFF';?>> 
      <td  align=right><font size=1> 
        <input type="hidden" name="journal_id[]" size="5" value="<?= $r->journal_id;?>">
        <? 
	  echo "$ctr."; 
	  if ($p1!='Insert')
	  {
         echo "<input type='checkbox' name='mark[]' value='$ctr' id='m$ctr'>";
	  }
	  ?>
        </font> </td>
      <td > 
        <input name="journal[]" type="text" id="<?='t'.$ctr;?>"  onChange="vChk(this)" value="<?= $r->journal;?>" size="30">
      </td>
      <td >
        <input type="text" name="journal_code[]" size="5"  onChange="vChk(this)" id="<?='t'.$ctr;?>" maxlength="5" value="<?=$r->journal_code;?>">
      </td>
      <td> 
        <?= lookUpAssoc('enable[]',array("Yes"=>"Y","No"=>"N"),$r->enable);?>
      </td>
    </tr>
    <?
	}
	?>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="4" nowrap><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        <input type="submit" name="p1" value="Save Checked">
        <a href="#top">Go Top</a></font> </td>
     </tr>
  </table>
</form>
<div align="center">
        <?
	  	echo "<img src='graphics/redarrow_left.gif'><a href='?p=journal&p1=Previous&start=$start&sort=$sort&xSearch=$xSearch'> Previous</a>";
		?>
        <b>|</b> 
        <?
	  	echo "<a href='?p=journal&p1=Next&start=$start&sort=$sort&xSearch=$xSearch'> Next </a><img src='graphics/redarrow_right.gif'> ";
		?>

</div>
