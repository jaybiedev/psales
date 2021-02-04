<script>
function vChk(t)
{
	var id = t.id
	var n = id.substring(1)

	document.getElementById('m'+n).checked =1;

}
</script>
<?
$href = '?p=income_type';

if (!session_is_registered('aincome_type'))
{
	session_register('aincome_type');
	$aincome_type=array();
}

if ($p1=="Close")
{
	session_unregister('aincome_type');
	echo "<script> window.location='index.php' </script>";
}

if ($p1=="Save Checked" && $aincome_type['status']=='INSERT')
{
	$c=0;
//	print_r($income_from);
//	echo "here ";exit;
	while ($c < count($mark))
	{
		if ($income_type[$c]!='')
		{
			if ($rate[$c] == '') $rate[$c] = 0;
			
			$q = "insert into income_type (income_code, income_type, basis, rate, tax,sss,phic,enable)
					values ('".$income_code[$c]."','".$income_type[$c]."','".$basis[$c]."',
							 '".$rate[$c]."','".$tax[$c]."','".$sss[$c]."','".$phic[$c]."','".$enable[$c]."')";
			@pg_query($q) or die (pg_errormessage().$q);
		}
		$c++;
	} 
	$aincome_type['status']='SAVED';
}
elseif ($p1=="Save Checked" && $aincome_type['status']=='LIST')
{
	$c=0;

	while ($c < count($mark))
	{
		$index = $mark[$c]-1;
		if ($income_type[$index]!='')
		{
			$q = "update income_type set 
						enable='".$enable[$index]."',
						income_code='".$income_code[$index]."',
						income_type = '".$income_type[$index]."',
						basis = '".$basis[$index]."',
						tax= '".$tax[$index]."',
						sss = '".$sss[$index]."',
						phic = '".$phic[$index]."',
						rate='".$rate[$index]."'
					where income_type_id='".$income_type_id[$index]."'";

			$qr =pg_query($q) or die (pg_errormessage());
		}
		$c++;
	} 
	$aincome_type['status']='SAVED';
}
?><br>
<form name="form1" method="post" action="">
  <table width="80%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr> 
      <td colspan="9"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Find 
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
      <td colspan="9" height="27"  background="../graphics/table_horizontal.PNG" ><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><b><font color="#EFEFEF">Income 
        Types Setup</font> <a name="top"></a></b></font></td>
    </tr>
    <tr bgcolor="#E9E9E9"> 
      <td width="3%"><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">#</font></td>
      <td width="18%"><a href="<?=$href.'&sort=income_code';?>"><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">Code</font></a></td>
      <td width="21%"><a href="<?=$href.'&sort=income_code';?>"><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Description</font></a></td>
      <td width="18%"><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Basis</font></td>
      <td width="7%"><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Rate</font></td>
      <td><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Tax</font></td>
      <td><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">SSS</font></td>
      <td><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">PHIC</font></td>
      <td width="13%"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        Enable 
        <input type="hidden" name="sort" size="5" value="<?= $sort;?>">
        </font></td>
    </tr>
    <?
	if ($p1=='Insert')
	{
		$aincome_type['status']='INSERT';
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
      <td width="18%"> <input name="income_code[]" type="text" size="5" maxlength="5" onChange="vChk(this)" id="c<?=$c;?>"> 
      </td>
      <td width="21%"><input name="income_type[]" type="text" size="30" maxlength="40" onChange="vChk(this)" id="d<?=$c;?>"></td>
      <td width="18%"> 
        <?= lookUpAssoc("basis[]",array("N/A"=>"N","Daily"=>"D","Tendure Allowance"=>"T","Hourly"=>"H","Monthly"=>"M","Auto"=>"A","Manual"=>"U"),"");?>
      </td>
      <td width="7%"><input name="rate[]" type="text" size="10"  onChange="vChk(this)" id="c<?=$c;?>"> 
      </td>
      <td> 
        <?= lookUpAssoc("tax[]",array("No"=>"N","Yes"=>"Y"),"");?>
      </td>
      <td> 
        <?= lookUpAssoc("sss[]",array("No"=>"N","Yes"=>"Y"),"");?>
      </td>
      <td> 
        <?= lookUpAssoc("phic[]",array("No"=>"N","Yes"=>"Y"),"");?>
      </td>
      <td width="13%"> 
        <?= lookUpAssoc("enable[]",array("Yes"=>"Y","No"=>"N"),"");?>
      </td>
    </tr>
    <?
		}
		?>
    <tr bgcolor="#FFFFFF"> 
      <td colspan=9 height="2" valign="bottom">&nbsp;</td>
    </tr>
    <tr bgcolor="#E9E9E9"> 
      <td colspan=4 height="26"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Saved 
        Income Types</font></td>
      <td height="26" colspan="4">&nbsp;</td>
      <td width="13%" height="26">&nbsp;</td>
    </tr>
    <?
	} //if insert
	else
	{
		$aincome_type['status']='LIST';
		$c=0;
	}
	
	$q = "select * from income_type ";
	
	if ($sort == '' || $sort=='income_code')
	{
		$sort = 'income_code';
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
        <input type="hidden" name="income_type_id[]" size="5" value="<?= $r->income_type_id;?>">
        <? 
	  echo "$ctr."; 
	  if ($p1!='Insert')
	  {
         echo "<input type='checkbox' name='mark[]' value='$ctr' id='m$ctr'>";
	  }
	  ?>
        </font> </td>
      <td width="18%"> <input name="income_code[]" type="text"  onChange="vChk(this)" id="c<?=$ctr;?>" value="<?= $r->income_code;?>" size="5" maxlength="5"> 
      </td>
      <td width="21%"> <input name="income_type[]" type="text"  onChange="vChk(this)" id="D<?=$ctr;?>" value="<?= $r->income_type;?>" size="30" maxlength="40"> 
      </td>
      <td width="18%"> 
        <?= lookUpAssoc("basis[]",array("N/A"=>"N","Daily"=>"D","Hourly"=>"H","Monthly"=>"M","Auto"=>"A","Manual"=>"U"),$r->basis);?>
      </td>
      <td> <input name="rate[]" rate="text" value="<?= $r->rate;?>" size="10"  onChange="vChk(this)" id="R<?=$ctr;?>"> 
      </td>
      <td> 
        <?= lookUpAssoc("tax[]",array("No"=>"N","Yes"=>"Y"),$r->tax);?>
      </td>
      <td> 
        <?= lookUpAssoc("sss[]",array("No"=>"N","Yes"=>"Y"),$r->sss);?>
      </td>
      <td> 
        <?= lookUpAssoc("phic[]",array("No"=>"N","Yes"=>"Y"),$r->phic);?>
      </td>
      <td width="13%"> 
        <?= lookUpAssoc("enable[]",array("Yes"=>"Y","No"=>"N"),$r->enable);?>
      </td>
    </tr>
    <?
	}
	?>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="9"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        <input name="p1" type="submit" id="p1" value="Save Checked">
        <a href="#top">Go Top</a></font></td>
    </tr>
  </table>
</form>
