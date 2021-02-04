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
$href = '?p=deduction_type';
require_once('connect.php');
if (!session_is_registered('adeduction_type'))
{
	session_register('adeduction_type');
	$adeduction_type=array();
}

if ($p2=="Close")
{
	session_unregister('adeduction_type');
	echo "<script> window.location='index.php' </script>";
}
if ($p2=="Save Checked" && $adeduction_type['status']=='INSERT')
{
	$c=0;
//	print_r($deduction_code);
//	echo "here ";exit;
	while ($c < count($mark))
	{
		if ($deduction_code[$c]!='')
		{
			$audit = "add:$admin->username:".date('m/d/Y');

			mysql_query("insert into deduction_type set
						enable='".$enable[$c]."',
						deduction_code='".$deduction_code[$c]."',
						deduction_type = '".$deduction_type[$c]."',
						rate = '".$rate[$c]."',
						basis='".$basis[$c]."',
						tax='".$tax[$c]."',
						sss='".$sss[$c]."',
						phic='".$phic[$c]."',
						audit = '$audit'")
					or die (mysql_error());
		}
		$c++;
	} 
	$adeduction_type['status']='SAVED';
}
elseif ($p2=="Save Checked" && $adeduction_type['status']=='LIST')
{
	$c=0;

	while ($c < count($mark))
	{
		$index = $mark[$c]-1;
		if ($deduction_code[$index]!='')
		{
			$audit = "update:$admin->username:".date('m/d/Y');

			mysql_query("update deduction_type set 
						enable='".$enable[$index]."',
						deduction_code='".$deduction_code[$index]."',
						deduction_type = '".$deduction_type[$index]."',
						rate = '".$rate[$index]."',
						basis='".$basis[$index]."',
						tax='".$tax[$index]."',
						sss='".$phic[$index]."',
						phic='".$phic[$index]."',
						audit = '$audit'
					where deduction_type_id='".$deduction_type_id[$index]."'") or die (mysql_error());
		}
		$c++;
	} 
	$adeduction_type['status']='SAVED';
}
?>
<form name="form1" method="post" action="">
  <table width="39%" border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC" align="center">
    <tr bgcolor="#C8D7E6"> 
      <td colspan="9" height="27"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><b>Deduction 
        Types Setup <a name="top"></a></b></font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="9" nowrap><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Find 
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
      <td width="18%"><a href="<?=$href.'&sort=deduction_code';?>"><b><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">Code</font></b></a></td>
      <td width="21%"><a href="<?=$href.'&sort=deduction_code';?>"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Description</font></b></a></td>
      <td width="18%"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Basis</font></b></td>
      <td width="7%"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Rate</font></b></td>
      <td><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Tax</font></b></td>
      <td><b><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">SSS</font></b></td>
      <td><b><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">PHIC</font></b></td>
      <td width="13%"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        Enable 
        <input type="hidden" name="sort" size="5" value="<?= $sort;?>">
        </font></b></td>
    </tr>
    <?
	if ($p2=='Insert')
	{
		$adeduction_type['status']='INSERT';
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
      <td width="18%"> <input name="deduction_code[]" type="text" size="10" maxlength="10" onChange="vChk(this)" id="c<?=$c;?>"> 
      </td>
      <td width="21%"><input name="deduction_type[]" type="text" size="30" maxlength="40" onChange="vChk(this)" id="d<?=$c;?>"></td>
      <td width="18%">
        <?= lookUpAssoc("basis[]",array("N/A"=>"N","Daily"=>"D","Hourly"=>"H","Monthly"=>"M","Auto"=>"A","Manual"=>"U"),"");?>
      </td>
      <td width="7%"><input name="rate[]2" type="text" id="rate[]" size="10" > 
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
        Table Range</font></td>
      <td height="26" colspan="4">&nbsp;</td>
      <td width="13%" height="26">&nbsp;</td>
    </tr>
    <?
	} //if insert
	else
	{
		$adeduction_type['status']='LIST';
		$c=0;
	}
	
	$q = "select * from deduction_type ";
	
	if ($sort == '' || $sort=='deduction_code')
	{
		$sort = 'deduction_code';
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
        <input type="hidden" name="deduction_type_id[]" size="5" value="<?= $r->deduction_type_id;?>">
        <? 
	  echo "$ctr."; 
	  if ($p2!='Insert')
	  {
         echo "<input type='checkbox' name='mark[]' value='$ctr'>";
	  }
	  ?>
        </font> </td>
      <td width="18%"> <input name="deduction_code[]" type="text" id="deduction_code[]" value="<?= $r->deduction_code;?>" size="10" maxlength="10"> 
      </td>
      <td width="21%"> <input name="deduction_type[]" type="text" id="deduction_type[]" value="<?= $r->deduction_type;?>" size="30" maxlength="40"> 
      </td>
      <td width="18%">
        <?= lookUpAssoc("basis[]",array("N/A"=>"N","Daily"=>"D","Hourly"=>"H","Monthly"=>"M","Auto"=>"A","Manual"=>"U"),$r->basis);?>
      </td>
      <td> <input name="rate[]" rate="text" id="rate[]" value="<?= $r->rate;?>" size="10"> 
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
        <input type="submit" name="p2" value="Save Checked">
        <a href="#top">Go Top</a></font></td>
    </tr>
  </table>
</form>
