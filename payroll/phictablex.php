<?
$href = '?p=phictable';
require_once('connect.php');
if (!session_is_registered('aphictable'))
{
	session_register('aphictable');
	$aphictable=array();
}

if ($p2=="Close")
{
	session_unregister('aphictable');
	echo "<script> window.location='index.php' </script>";
}
if ($p2=="Save Checked" && $aphictable['status']=='INSERT')
{
	$c=0;
//	print_r($income_from);
//	echo "here ";exit;
	while ($c < count($mark))
	{
		if ($income_from[$c]!='')
		{
			mysql_query("insert into phictable set
						enable='".$enable[$c]."',
						income_from='".$income_from[$c]."',
						income_to = '".$income_to[$c]."',
						employee = '".$employee[$c]."',
						employer='".$employer[$c]."'")
					or die (mysql_error());
		}
		$c++;
	} 
	$aphictable['status']='SAVED';
}
elseif ($p2=="Save Checked" && $aphictable['status']=='LIST')
{
	$c=0;

	while ($c < count($mark))
	{
		$index = $mark[$c]-1;
		if ($income_from[$index]!='')
		{
			mysql_query("update phictable set 
						enable='".$enable[$index]."',
						income_from='".$income_from[$index]."',
						income_to = '".$income_to[$index]."',
						employee = '".$employee[$index]."',
						employer='".$employer[$index]."'
					where phictable_id='".$phictable_id[$index]."'") or die (mysql_error());
		}
		$c++;
	} 
	$aphictable['status']='SAVED';
}
?>
<form name="form1" method="post" action="">
  <table width="59%" border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC" align="center">
    <tr bgcolor="#C8D7E6"> 
      <td colspan="6" height="27"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><b>PHIC 
        Table Setup <a name="top"></a></b></font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="6" nowrap><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Find 
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
      <td width="3%"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">#</font></b></td>
      <td width="18%"><a href="<?=$href.'&sort=income_from';?>"><b><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">From</font></b></a></td>
      <td width="21%"><a href="<?=$href.'&sort=income_from';?>"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">To</font></b></a></td>
      <td width="18%"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">employer</font></b></td>
      <td width="7%"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">employee</font></b></td>
      <td><b></b><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        <input type="hidden" name="sort" size="5" value="<?= $sort;?>">
        </font></b></td>
    </tr>
    <?
	if ($p2=='Insert')
	{
		$aphictable['status']='INSERT';
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
      <td width="18%"> <input type="text" name="income_from[]" size="15"> </td>
      <td width="21%"><input type="text" name="income_to[]" size="15"></td>
      <td width="18%"><input name="employer[]" type="text" id="employer[]" size="10"> 
      </td>
      <td width="7%"><input name="employee[]" type="text" id="employer[]3" size="10"> 
      </td>
      <td> <select name="enable[]">
          <option value="Y">Y</option>
          <option value="N">N</option>
        </select> </td>
    </tr>
    <?
		}
		?>
    <tr bgcolor="#FFFFFF"> 
      <td colspan=6 height="2" valign="bottom">&nbsp;</td>
    </tr>
    <tr bgcolor="#E9E9E9"> 
      <td colspan=6 height="26"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Saved 
        Table Range</font></td>
    </tr>
    <?
	} //if insert
	else
	{
		$aphictable['status']='LIST';
		$c=0;
	}
	
	$q = "select * from phictable ";
	
	if ($sort == '' || $sort=='income_from')
	{
		$sort = 'income_from';
	}
	$q .= " order by $sort ";

	$qr = mysql_query($q) or die (mysql_error());
	$ctr = $c;
	while ($r = mysql_fetch_object($qr))
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
        <input type="hidden" name="phictable_id[]" size="5" value="<?= $r->phictable_id;?>">
        <? 
	  echo "$ctr."; 
	  if ($p2!='Insert')
	  {
         echo "<input type='checkbox' name='mark[]' value='$ctr'>";
	  }
	  ?>
        </font> </td>
      <td width="18%"> <input name="income_from[]" type="text" id="income_from[]" value="<?= $r->income_from;?>" size="15"> 
      </td>
      <td width="21%"> <input name="income_to[]" type="text" id="income_to[]" value="<?= $r->income_to;?>" size="15"> 
      </td>
      <td width="18%"><input name="employer[]" type="text" id="employer[]" value="<?= $r->employer;?>" size="10"> 
      </td>
      <td> <input name="employee[]" employee="text" id="employee[]" value="<?= $r->employee;?>" size="10"> 
      </td>
      <td> <select name="enable[]">
          <option value="Y"  <?= ($r->enable!='N' ? 'Selected' :'');?>>Y</option>
          <option value="N"  <?= ($r->enable=='N' ? 'Selected' :'');?>>N</option>
        </select> </td>
    </tr>
    <?
	}
	?>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="6"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        <input type="submit" name="p2" value="Save Checked">
        <a href="#top">Go Top</a></font></td>
    </tr>
  </table>
</form>
