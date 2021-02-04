<script>
function vChk(t)
{
	var id = t.id
	var n = id.substring(1)

	document.getElementById('m'+n).checked =1;

}
</script>
<?
$href = '?p=ssstable';

if (!session_is_registered('assstable'))
{
	session_register('assstable');
	$assstable=array();
}

if ($p1=="Close")
{
	session_unregister('assstable');
	echo "<script> window.location='index.php' </script>";
}
if ($p1=="Save Checked" && $assstable['status']=='INSERT')
{
	$c=0;
//	print_r($income_from);
//	echo "here ";exit;
	while ($c < count($mark))
	{
		if ($income_from[$c]!='')
		{
			if ($income_to[$c] == '') $income_to[$c] = 0;
			if ($employer[$c] == '') $employer[$c] = 0;
			if ($employee[$c] == '') $employee[$c] = 0;
			if ($ecc[$c] == '') $ecc[$c] = 0;
			
			$q = "insert into ssstable (income_from, income_to, employer, employee, ecc, enable)
					values ('".$income_from[$c]."','".$income_to[$c]."','".$employer[$c]."',
							 '".$employee[$c]."','".$ecc[$c]."','".$enable[$c]."')";
			@pg_query($q) or die (pg_errormessage().$q);
		}
		$c++;
	} 
	$assstable['status']='SAVED';
}
elseif ($p1=="Save Checked" && $assstable['status']=='LIST')
{
	$c=0;

	while ($c < count($mark))
	{
		$index = $mark[$c]-1;
		if ($income_from[$index]!='')
		{
			pg_query("update ssstable set 
						enable='".$enable[$index]."',
						income_from='".$income_from[$index]."',
						income_to = '".$income_to[$index]."',
						employee = '".$employee[$index]."',
						employer='".$employer[$index]."',
						ecc='".$ecc[$index]."'
					where ssstable_id='".$ssstable_id[$index]."'") or die (pg_errormessage());
		}
		$c++;
	} 
	$assstable['status']='SAVED';
}
?><br>
<form name="form1" method="post" action="">
  <table width="70%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr> 
      <td nowrap colspan="7"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Find 
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
    <tr bgcolor="#C8D7E6"> 
      <td colspan="7" height="27"  background="../graphics/table_horizontal.PNG" ><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><b><font color="#EFEFEF">SSS 
        Table Setup</font> <a name="top"></a></b></font></td>
    </tr>
    <tr bgcolor="#E9E9E9"> 
      <td width="3%"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">#</font></b></td>
      <td width="18%"><a href="<?=$href.'&sort=income_from';?>"><b><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">From</font></b></a></td>
      <td width="21%"><a href="<?=$href.'&sort=income_from';?>"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">To</font></b></a></td>
      <td width="18%"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Employer</font></b></td>
      <td width="7%"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Employee</font></b></td>
      <td align="center"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Ecc</font></b></td>
      <td width="13%"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        <input type="hidden" name="sort" size="5" value="<?= $sort;?>">
        </font><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Enable</font></b></td>
    </tr>
    <?
	if ($p1=='Insert')
	{
		$assstable['status']='INSERT';
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
      <td width="18%"> <input type="text" name="income_from[]" size="15" id="<?= 'F'.$c;?>"  onChange="vChk(this)" style="text-align:right"> 
      </td>
      <td width="21%"><input type="text" name="income_to[]" size="15" id="<?= 'T'.$c;?>"  onChange="vChk(this)" style="text-align:right"></td>
      <td width="18%"><input name="employer[]" type="text"  size="10" id="<?= 'R'.$c;?>"  onChange="vChk(this)" style="text-align:right"> 
      </td>
      <td width="7%"><input name="employee[]" type="text"  id="<?= 'Y'.$c;?>"  onChange="vChk(this)" size="10" style="text-align:right"> 
      </td>
      <td> <input type="text" name="ecc[]" size="7"  id="<?= 'E'.$c;?>"  onChange="vChk(this)" style="text-align:right"> 
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
      <td colspan=7 height="2" valign="bottom">&nbsp;</td>
    </tr>
    <tr bgcolor="#E9E9E9"> 
      <td colspan=4 height="26"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Saved 
        Table Range</font></td>
      <td height="26" colspan="2">&nbsp;</td>
      <td width="13%" height="26">&nbsp;</td>
    </tr>
    <?
	} //if insert
	else
	{
		$assstable['status']='LIST';
		$c=0;
	}
	
	$q = "select * from ssstable ";
	
	if ($sort == '' || $sort=='income_from')
	{
		$sort = 'income_from';
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
        <input type="hidden" name="ssstable_id[]" size="5" value="<?= $r->ssstable_id;?>">
        <? 
	  echo "$ctr."; 
	  if ($p1!='Insert')
	  {
         echo "<input type='checkbox' name='mark[]' value='$ctr' id='m$ctr'>";
	  }
	  ?>
        </font> </td>
      <td width="18%"> <input name="income_from[]" type="text"  value="<?= $r->income_from;?>" size="15"  id="<?= 'F'.$ctr;?>"  onChange="vChk(this)"  style="text-align:right"> 
      </td>
      <td width="21%"> <input name="income_to[]" type="text"  id="<?= 'T'.$ctr;?>"  onChange="vChk(this)" value="<?= $r->income_to;?>" size="15" style="text-align:right"></td>
      <td width="18%"><input name="employer[]" type="text"  id="<?= 'R'.$ctr;?>"  onChange="vChk(this)" value="<?= $r->employer;?>" size="10" style="text-align:right"> 
      </td>
      <td> <input name="employee[]" employee="text"  id="<?= 'Y'.$ctr;?>"  onChange="vChk(this)" value="<?= $r->employee;?>" size="10" style="text-align:right"> 
      </td>
      <td> <input type="text" name="ecc[]" size="7" value="<?= $r->ecc;?>" id="<?= 'E'.$ctr;?>"  onChange="vChk(this)" style="text-align:right"> 
      </td>
      <td width="13%"> <select name="enable[]"  id="<?= 'A'.$ctr;?>"  onChange="vChk(this)">
          <option value="Y"  <?= ($r->enable ? 'Selected' :'');?>>Yes</option>
          <option value="N"  <?= (!$r->enable ? 'Selected' :'');?>>No</option>
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
