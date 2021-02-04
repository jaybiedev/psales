<script>
function vChk(t)
{
	var id = t.id
	var n = id.substring(1)

	document.getElementById('m'+n).checked =1;
}
</script>
<?
$href = '?p=wtaxtable';

if (!session_is_registered('awtaxtable'))
{
	session_register('awtaxtable');
	$awtaxtable=array();
}

if ($p1=="Close")
{
	session_unregister('awtaxtable');
	echo "<script> window.location='index.php' </script>";
}
if ($p1=="Save Checked" && $awtaxtable['status']=='INSERT')
{
	$c=0;
//	print_r($income_from);
//	echo "here ";exit;
	while ($c < count($mark))
	{
		if ($income_from[$c]!='')
		{
			if ($income_to[$c] == '') $income_to[$c] = 0;
			if ($basic_deduction[$c] == '') $basic_deduction[$c] = 0;
			if ($percent_add[$c] == '') $percent_add[$c] = 0;
			if ($ecc[$c] == '') $ecc[$c] = 0;
			
			$q = "insert into wtaxtable (taxcode,income_from, income_to, basic_deduction, percent_add, enable)
					values ('".$taxcode[$c]."','".$income_from[$c]."','".$income_to[$c]."','".$basic_deduction[$c]."',
							 '".$percent_add[$c]."','".$enable[$c]."')";
			@pg_query($q) or die (pg_errormessage().$q);
		}
		$c++;
	} 
	$awtaxtable['status']='SAVED';
}
elseif ($p1=="Save Checked" && $awtaxtable['status']=='LIST')
{
	$c=0;

	while ($c < count($mark))
	{
		$index = $mark[$c]-1;
		if ($income_from[$index]!='')
		{
			pg_query("update wtaxtable set 
						enable='".$enable[$index]."',
						taxcode='".$taxcode[$index]."',
						income_from='".$income_from[$index]."',
						income_to = '".$income_to[$index]."',
						percent_add = '".$percent_add[$index]."',
						basic_deduction='".$basic_deduction[$index]."'
					where wtaxtable_id='".$wtaxtable_id[$index]."'") or die (pg_errormessage());
		}
		$c++;
	} 
	$awtaxtable['status']='SAVED';
}
?>
<br>
<form name="form1" method="post" action="">
  <table width="85%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr> 
      <td colspan="7" nowrap><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Find 
        <input type="text" name="xSearch2" value="<?= $xSearch;?>">
        <input name="p1" type="submit" id="p1" value="Go">
        Filter 
        <select name="taxcodefilter">
          <option value=''>Show All</option>
          <?
		$q = "select distinct taxcode as taxcode from wtaxtable where enable='Y'";
		$qr = @pg_query($q);
		while ($r = @pg_fetch_object($qr))
		{
			if ($taxcodefilter == $r->taxcode)
			{
				echo "<option value = $r->taxcode selected>$r->taxcode</option>";
			}
			else
			{
				echo "<option value = $r->taxcode>$r->taxcode</option>";
			}
		}
		?>
        </select>
        <input name="p1" type="submit" id="p1" value="List">
        Insert 
        <select name="insertcount">
          <option value="5">5</option>
          <option value="10">10</option>
          <option value="15">15</option>
          <option value="20">20</option>
        </select>
        <input name="p1" type="submit" id="p1" value="Insert">
        <input name="p1" type="button" id="p1" onClick="window.location='?p='" value="Close">
        </font> 
        <hr color="#CC3300"></td>
    </tr>
    <tr bgcolor="#C8D7E6"> 
      <td colspan="7" height="27"  background="../graphics/table_horizontal.PNG" ><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><b><font color="#EFEFEF">Withholding 
        Tax Table Setup</font> <a name="top"></a></b></font></td>
    </tr>
    <tr bgcolor="#E9E9E9"> 
      <td width="9%"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">#</font></b></td>
      <td width="9%"><a href="<?=$href.'&sort=taxcode';?>"><b><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">TaxCode</font></b></a></td>
      <td width="10%"><a href="<?=$href.'&sort=income_from';?>"><b><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">From</font></b></a></td>
      <td width="19%"><a href="<?=$href.'&sort=income_from';?>"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">To</font></b></a></td>
      <td width="16%"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Basic</font></b></td>
      <td width="14%"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">%AddOn</font></b><b></b></td>
      <td width="23%"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        <input type="hidden" name="sort" size="5" value="<?= $sort;?>">
        </font></b></td>
    </tr>
    <?
	if ($p1=='Insert')
	{
		$awtaxtable['status']='INSERT';
		$c=0;
		while ($c < $insertcount)
		{
			$c++;
	?>
    <tr bgcolor="#FFFFFF"> 
      <td width="9%" align=right nowrap> <font size=1> 
        <?= $c;?>
        <input type="checkbox" name="mark[]" value="<?= $c;?>" id="m<?= $c;?>">
        </font> </td>
      <td width="9%"> <input name="taxcode[]" type="text" id="<?= 't'.$c;?>"  onChange="vChk(this)" value="<?= $taxcodefilter;?>" size="10" maxlength="10"> 
      </td>
      <td width="10%"><input type="text" name="income_from[]" size="15" id="<?= 'F'.$c;?>"  onChange="vChk(this)" style="text-align:right"> </td>
      <td width="19%"><input type="text" name="income_to[]" size="15" id="<?= 'T'.$c;?>"  onChange="vChk(this)" style="text-align:right"></td>
      <td width="16%"><input name="basic_deduction[]" type="text"  size="10" id="<?= 'R'.$c;?>"  onChange="vChk(this)" style="text-align:right"> 
      </td>
      <td><input name="percent_add[]" type="text"  id="<?= 'Y'.$c;?>"  onChange="vChk(this)" size="10" style="text-align:right"> 
      </td>
      <td width="23%"> <select name="enable[]"  id="<?= 'A'.$c;?>"  onChange="vChk(this)">
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
      <td colspan=5 height="26"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Saved 
        Table Range</font></td>
      <td height="26">&nbsp;</td>
      <td width="23%" height="26">&nbsp;</td>
    </tr>
    <?
	} //if insert
	else
	{
		$awtaxtable['status']='LIST';
		$c=0;
	}
	
	$q = "select * from wtaxtable ";
	
	if ($taxcodefilter !='')
	{
		$q .= " where taxcode = '$taxcodefilter'";
	}
	if ($sort == '' )
	{
		$sort = 'taxcode, income_from';
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
      <td width="9%" align=right nowrap><font size=1> 
        <input type="hidden" name="wtaxtable_id[]" size="5" value="<?= $r->wtaxtable_id;?>">
        <? 
	  echo "$ctr."; 
	  if ($p1!='Insert')
	  {
         echo "<input type='checkbox' name='mark[]' value='$ctr' id='m$ctr'>";
	  }
	  ?>
        </font> </td>
      <td width="9%"> <input name="taxcode[]" type="text"  id="<?= 'F'.$ctr;?>"  onChange="vChk(this)"  value="<?= $r->taxcode;?>" size="10" maxlength="10"> 
      </td>
      <td width="10%"><input name="income_from[]" type="text"  value="<?= $r->income_from;?>" size="15"  id="<?= 'F'.$ctr;?>"  onChange="vChk(this)" style="text-align:right"> </td>
      <td width="19%"> <input name="income_to[]" type="text"  id="<?= 'T'.$ctr;?>"  onChange="vChk(this)" value="<?= $r->income_to;?>" size="15" style="text-align:right"></td>
      <td width="16%"><input name="basic_deduction[]" type="text"  id="<?= 'R'.$ctr;?>"  onChange="vChk(this)" value="<?= $r->basic_deduction;?>" size="10" style="text-align:right"> 
      </td>
      <td> <input name="percent_add[]" percent_add="text"  id="<?= 'Y'.$ctr;?>"  onChange="vChk(this)" value="<?= $r->percent_add;?>" size="10" style="text-align:right"> 
      </td>
      <td width="23%"> <select name="enable[]"  id="<?= 'A'.$ctr;?>"  onChange="vChk(this)">
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
