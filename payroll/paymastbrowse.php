<?
$href = '?p=paymastbrowse';
if (!session_is_registered('apaymast'))
{
	session_register('apaymast');
	$apaymast=array();
}

if ($p2=="Close")
{
	session_unregister('apaymast');
	echo "<script> window.location='index.php' </script>";
}
if ($p2=="Save Checked" && $apaymast['status']=='INSERT')
{
	$c=0;
//	echo "here ";exit;
	while ($c < count($mark))
	{
		if ($elast[$c]!='')
		{
			if ($department_id[$c] == '')
				$department_id[$c] = 0;
			if ($section_id[$c] == '')
				$section_id[$c] = 0;
			
			$q = "insert into paymast
					(enable, elast, efirst, emiddle,
					department_id, section_id)
				values
					('".$enable[$c]."',
					'".$elast[$c]."',
					'".$efirst[$c]."',
					'".$emiddle[$c]."',
					'".$department_id[$c]."',
					'".$section_id[$c]."')";
			$qr = pg_query($q) or message (pg_errormessage().$q);
		}
		else
		{
		}
		$c++;
	} 
	$apaymast['status']='SAVED';
}
elseif ($p2=="Save Checked" && $apaymast['status']=='LIST')
{
	$c=0;

	while ($c < count($mark))
	{
		$index = $mark[$c]-1;
		
		if ($elast[$index]!='')
		{
			if ($department_id[$index] == '')
				$department_id[$index] = 0;
			if ($section_id[$c] == '')
				$section_id[$c] = 0;
			$q = "update paymast set 
						enable='".$enable[$index]."',
						elast='".$elast[$index]."',
						efirst = '".$efirst[$index]."',
						emiddle = '".$emiddle[$index]."',
						department_id='".$department_id[$index]."',
						section_id='".$section_id[$index]."'
					where 
						paymast_id='".$paymast_id[$index]."'";
			$qr =pg_query($q) or message (pg_errormessage().$q);
		}
		$c++;
	} 
	$apaymast['status']='SAVED';
}
?>
<form name="form1" method="post" action="">
  <table width="39%" border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC" align="center">
    <tr bgcolor="#C8D7E6"> 
      <td colspan="7" height="27"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><b>Payroll 
        Master Browse Setup <a name="top"></a></b></font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="7" nowrap><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Find 
        <input type="text" name="xSearch" value="<?= $xSearch;?>">
        <input type="submit" name="p2" value="Go">
        Insert 
        <select name="insertcount">
          <option value="5">5</option>
          <option value="10">10</option>
          <option value="15">15</option>
          <option value="20">20</option>
        </select>
        <input type="submit" name="p2" value="Insert">
        <input type="submit" name="p2" value="List">
        <input type="submit" name="p2" value="Close">
        </font></td>
    </tr>
    <tr bgcolor="#E9E9E9"> 
      <td width="3%" nowrap><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">#</font></b></td>
      <td width="9%" nowrap><a href="<?=$href.'&sort=elast';?>"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Last 
        Name</font></b></a></td>
      <td width="9%" nowrap><a href="<?=$href.'&sort=efirst';?>"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">First 
        Name </font></b></a></td>
      <td width="18%" nowrap><a href="<?=$href.'&sort=emiddle';?>"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Middle 
        Name</font></b></a></td>
      <td width="21%" nowrap><a href="<?=$href.'&sort=department_id';?>"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Department</font></b></a></td>
      <td nowrap><b><a href="<?=$href.'&sort=section_id';?>"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Section</font></b></a></b><b></b><b></b></td>
      <td width="13%" nowrap><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        <input type="hidden" name="sort" size="5" value="<?= $sort;?>">
        </font></b></td>
    </tr>
    <?
	if ($p2=='Insert')
	{
		$apaymast['status']='INSERT';
		$c=0;
		while ($c < $insertcount)
		{
			$c++;
	?>
    <tr bgcolor="#FFFFFF"> 
      <td width="3%" align=right nowrap> <font size=1> 
        <?= $c;?>
        <input type="checkbox" name="mark[]" value="<?= $c;?>" id="<?= $c;?>">
        </font> </td>
      <td width="9%"> <input name="elast[]" type="text" id="elast[]" size="18"> </td>
      <td width="9%"><input name="efirst[]" type="text" id="efirst[]" size="18"></td>
      <td width="18%"><input name="emiddle[]" type="text" id="emiddle[]" size="18"></td>
      <td width="21%"> 
        <?=lookUpTable2('department_id[]','department','department_id','department','');?>
      </td>
      <td>
        <?=lookUpTable2('section_id[]','section','section_id','section','');?>
      </td>
      <td width="13%"> <select name="enable[]">
          <option value="t">Y</option>
          <option value="f">N</option>
        </select> </td>
    </tr>
    <?
		}
		?>
    <tr bgcolor="#FFFFFF"> 
      <td colspan=7 height="2" valign="bottom">&nbsp;</td>
    </tr>
    <tr bgcolor="#E9E9E9"> 
      <td colspan=6 height="26"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Saved 
        Names</font></td>
      <td width="13%" height="26">&nbsp;</td>
    </tr>
    <?
	} //if insert
	else
	{
		$apaymast['status']='LIST';
		$c=0;
	}

	if ($xSearch=='')
	{
		$q = "select * from paymast order by paymast_id desc ";  //limit 0,5";
	}
	else
	{
		$q = "select * from paymast where elast like '$xSearch%'";
		if ($sort == '' || $sort=='elast')
		{
			$sort = 'elast';
		}
		$q .= " order by $sort ";
	}
	$qr = pg_query($q) or die (pg_errormessage());
	$ctr = $c;
	while ($r = pg_fetch_object($qr))
	{
		$ctr++;
		if ($ctr %10 == 0)
		{
			echo "<tr bgcolor='#FFFFFF'> 
				 	<td colspan=10><input type='submit' name='p2' value='Save Checked'> <a href='#top'><font face='Verdana' size=2>Top</font></a></td></tr>";

		}
	?>
    <tr  bgcolor=<?= ($r->enable=='N')? '#FFCCCC' :'#FFFFFF';?>> 
      <td width="3%" align=right><font size=1> 
        <input type="hidden" name="paymast_id[]" size="5" value="<?= $r->paymast_id;?>">
        <? 
	  echo "$ctr."; 
	  if ($p2!='Insert')
	  {
         echo "<input type='checkbox' name='mark[]' value='$ctr'>";
	  }
	  ?>
        </font> </td>
      <td width="9%"> <input name="elast[]" type="text" id="elast[]" value="<?= $r->elast;?>" size="18"> 
      </td>
      <td width="9%"><input name="efirst[]" type="text" id="efirst[]" value="<?= $r->efirst;?>" size="18"></td>
      <td width="18%"><input name="emiddle[]" type="text" id="emiddle[]" value="<?= $r->emiddle;?>" size="18"></td>
      <td width="21%"> 
        <?=lookUpTable2('department_id[]','department','department_id','department',$r->department_id);?>
      </td>
      <td>
        <?=lookUpTable2('section_id[]','section','section_id','section',$r->section_id);?>
      </td>
      <td width="13%"> <select name="enable[]">
          <option value="t"  <?= ($r->enable=='t' ? 'Selected' :'');?>>Y</option>
          <option value="f"  <?= ($r->enable=='f' ? 'Selected' :'');?>>N</option>
        </select> </td>
    </tr>
    <?
	}
	?>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="7"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        <input type="submit" name="p2" value="Save Checked">
        <a href="#top">Go Top</a></font></td>
    </tr>
  </table>
</form>
