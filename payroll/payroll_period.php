<script>
function vChk(t)
{
	var id = t.id
	var n = id.substring(1)

	document.getElementById('m'+n).checked =1;

}
function vChkDate(t)
{
	var id = t.id
	var n = id.substring(1)
	var mid = eval("this.form1.m"+n)
	mid.checked = true

	var mid = eval("this.form1.d"+n)
	
}
</script>
<?
$href = '?p=payroll_period';
if (!session_is_registered('apayroll_period'))
{
	session_register('apayroll_period');
	$apayroll_period=array();
}
/*
if(($m=='' or $m > 12) and $apayroll_period['m']=='')
{
	exitmessage("Specify Month for Payroll Coverage...");
}
elseif ($apayroll_period['m']=='')
{
	$apayroll_period['m']=$m;
}
*/
if ($p2=="Close")
{
	session_unregister('apayroll_period');
	echo "<script> window.location='index.php' </script>";
}
if ($p2=="Save Checked" && $year=='')
{
	message("Specify Year.  Data not updated...");
}
elseif ($p2=="Save Checked" && $apayroll_period['status']=='INSERT')
{
	$c=0;
//	print_r($period1);
//	echo "here ";exit;
	while ($c < count($mark))
	{
		if ($period1[$c]!='')
		{
			$audit = "add: ".date('m/d/Y').":".$ADMIN['username'].";";
			$mperiod1 = mdy2ymd($period1[$c]);
			$mperiod2 = mdy2ymd($period2[$c]);
			$q = "insert into payroll_period (enable, year, month, period1,period2, num, num2, schedule, days, post, active, audit)
						values ('".$enable[$c]."','$year','$month','$mperiod1','$mperiod2','".$num[$c]."','".$num2[$c]."',
								'".$schedule[$c]."','".$days[$c]."','".$post[$c]."','".$active[$c]."','$audit')";
			$qr = @pg_query($q) or message1 (pg_errormessage().$q);
		}
		$c++;
	} 
	$apayroll_period['status']='SAVED';
}

elseif ($p2=="Save Checked" && $apayroll_period['status']=='LIST')
{
	$c=0;

	while ($c < count($mark))
	{
		$index = $mark[$c]-1;
		if ($period1[$index]!='')
		{
			$audit = "update: ".date('m/d/Y').":".$ADMIN['username'].";";
			$mperiod1 = mdy2ymd($period1[$index]);
			$mperiod2 = mdy2ymd($period2[$index]);

			$q = "update payroll_period set 
						enable='".$enable[$index]."',
						year='$year',
						month='$month',
						period1='$mperiod1',
						period2 = '$mperiod2',
						num= '".$num[$index]."',
						num2 ='".$num2[$index]."',
						schedule ='".$schedule[$index]."',
						days ='".$days[$index]."',
						post='".$post[$index]."',
						active='".$active[$index]."',
						audit = '$audit'
					where payroll_period_id='".$payroll_period_id[$index]."'";
			$qr = @pg_query($q) or message1 (pg_errormessage().$q);
			
		}
		
		$c++;
	} 
	$apayroll_period['status']='SAVED';
}
elseif ($p1 == 'setactive' && $id!='')
{
	$q = "update payroll_period set active=''";
	$qr = @pg_query($q) or message(pg_errormessage());

	$q = "update payroll_period set active='A' where payroll_period_id='$id'";
	$qr = @pg_query($q) or message(pg_errormessage());
	commit();
	$apayroll_period['status']='LIST';
}
if ($month == '') $month=date('m');
?><br>
<form name="form1" method="post" action="">
  <table width="65%" border="0" cellspacing="1" cellpadding="2" align="center">
    <tr bgcolor="#C8D7E6"> 
      <td colspan="10" height="27"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><b>Payroll 
        Period Setup <a name="top"></a></b></font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="10" nowrap><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Month 
        of 
		<select name="month" id="month">
		<option value="1" <?= ($month == '1'?'selected' : '');?>>January</option>
		<option value="2" <?= ($month == '2'?'selected' : '');?>>Febuary</option>
		<option value="3" <?= ($month == '3'?'selected' : '');?>>March</option>
		<option value="4" <?= ($month == '4'?'selected' : '');?>>April</option>
		<option value="5" <?= ($month == '5'?'selected' : '');?>>May</option>
		<option value="6" <?= ($month == '6'?'selected' : '');?>>June</option>
		<option value="7" <?= ($month == '7'?'selected' : '');?>>July</option>
		<option value="8" <?= ($month == '8'?'selected' : '');?>>August</option>
		<option value="9" <?= ($month == '9'?'selected' : '');?>>September</option>
		<option value="10" <?= ($month == '10'?'selected' : '');?>>October</option>
		<option value="11" <?= ($month == '11'?'selected' : '');?>>November</option>
		<option value="12" <?= ($month == '12'?'selected' : '');?>>December</option>
		<option value="99" <?= ($month == '99'?'selected' : '');?>>Mid-Year</option>
		<option value="13" <?= ($month == '13'?'selected' : '');?>>13th Month</option>
		<option value="14" <?= ($month == '14'?'selected' : '');?>>14th Month</option>
		<option value="15" <?= ($month == '15'?'selected' : '');?>>15th Month</option>
		</select>
        , 
        <input name="year" type="text" value="<?=date('Y');?>" size="5" maxlength="4">
        Insert 
        <select name="insertcount">
          <option value="2">2</option>
          <option value="3">3</option>
          <option value="4">4</option>
          <option value="5">5</option>
        </select>
        <input type="submit" name="p2" value="Insert">
        <input type="submit" name="p2" value="List">
        <input type="submit" name="p2" value="Close">
        </font></td>
    </tr>
    <tr bgcolor="#E9E9E9"> 
      <td width="9%"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">#</font></b></td>
      <td width="12%"><a href="<?=$href.'&sort=period1';?>"><b><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">From</font></b></a></td>
      <td width="12%"><a href="<?=$href.'&sort=period1';?>"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">To</font></b></a></td>
      <td width="13%"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Schedule</font></b></td>
      <td width="10%"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Period</font></b></td>
      <td width="7%"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Of</font></b></td>
      <td width="8%"><b><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">Days</font></b><b></b></td>
      <td width="7%"><b><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">Post</font></b></td>
      <td width="9%"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Enable</font></b></td>
      <td width="13%"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        <input type="hidden" name="sort" size="5" value="<?= $sort;?>">
        </font></b></td>
    </tr>
    <?
	if ($p2=='Insert')
	{
		$apayroll_period['status']='INSERT';
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
      <td width="12%" nowrap> <input name="period1[]" type="text" size="10" maxlength="10" onChange="vChkDate(this);vChk(this);" id="c<?=$c;?>" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')"> 
      </td>
      <td width="12%" nowrap><input name="period2[]" type="text" size="10" onChange="vChkDate(this)" id="x<?=$c;?>" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')"> 
      </td>
      <td width="13%" nowrap><input name="schedule[]" type="text"  onChange="vChk(this)" id="<?='t'.$c;?>" size="5" > 
      </td>
      <td width="10%" nowrap><input name="num[]" type="text"  onChange="vChk(this)" id="<?='y'.$c;?>" size="5" > 
      </td>
      <td nowrap> <input name="num2[]" type="text"  onChange="vChk(this)" id="<?='x'.$c;?>"size="5" ></td>
      <td nowrap> <input name="days[]" type="text"   onChange="vChk(this)" id="<?='d'.$c;?>" size="5" > 
      </td>
      <td nowrap> 
        <?= lookUpAssoc("post[]",array("No"=>"N","Yes"=>"Y"),"");?>
      </td>
      <td nowrap>
        <?= lookUpAssoc("enable[]",array("Yes"=>"Y","No"=>"N"),"");?>
      </td>
      <td width="13%" nowrap>&nbsp; </td>
    </tr>
    <?
		}
		?>
    <tr bgcolor="#FFFFFF"> 
      <td colspan=10 height="2" valign="bottom">&nbsp;</td>
    </tr>
    <tr bgcolor="#E9E9E9"> 
      <td colspan=4 height="26"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Saved 
        Table Range</font></td>
      <td height="26" colspan="5">&nbsp;</td>
      <td width="13%" height="26">&nbsp;</td>
    </tr>
    <?
	} //if insert
	else
	{
		$apayroll_period['status']='LIST';
		$c=0;
	}
	
	$q = "select * from payroll_period where month='$month'";
	
	if ($sort == '' || $sort=='period1')
	{
		$sort = 'period1';
	}
	$q .= " order by $sort ";

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
      <td width="9%" align=right nowrap><font size=1> 
        <input type="hidden" name="payroll_period_id[]" size="5" value="<?= $r->payroll_period_id;?>">
        <? 
	  echo "$ctr."; 
	  if ($p2!='Insert')
	  {
         echo "<input type='checkbox' name='mark[]' value='$ctr' id='m$ctr'>";
	  }
	  ?>
        </font> </td>
      <td width="12%"> <input name="period1[]" type="text" onChange="vChkDate(this);vChk(this);" id="c<?=$ctr;?>" value="<?= ymd2mdy($r->period1);?>" size="10" maxlength="10" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')"> 
      </td>
      <td width="12%"> <input name="period2[]" type="text" onChange="vChkDate(this);vChk(this)" id="x<?=$ctr;?>" value="<?= ymd2mdy($r->period2);?>" size="10" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')"> 
      </td>
      <td width="13%"> <input name="schedule[]" type="text" id="x<?=$ctr;?>" value="<?= $r->schedule;?>" size="5"  onChange="vChk(this)"> 
      </td>
      <td><input name="num[]" type="text" id="d<?=$ctr;?>" value="<?= $r->num;?>" size="5"  onChange="vChk(this)"> 
      </td>
      <td> <input name="num2[]" type="text" id="d<?=$ctr;?>" value="<?= $r->num2;?>" size="5"  onChange="vChk(this)"> 
      </td>
      <td> <input name="days[]" type="text"  id="d<?=$ctr;?>" value="<?= $r->days;?>" size="5"  onChange="vChk(this)"> 
      </td>
      <td> 
        <?= lookUpAssoc("post[]",array("No"=>"N","Yes"=>"Y"),$r->post);?>
      </td>
      <td>
        <?= lookUpAssoc("enable[]",array("Yes"=>"Y","No"=>"N"),$r->enable);?>
      </td>
      <td width="13%" nowrap> <font size="1"> 
        <?
	  if ($r->active == 'A') 
	  {
	  	echo "<b><font color='gree'>Active</font></b>";
	  }	
	  else 
	  {
	  	echo "<a href='?p=payroll_period&p1=setactive&id=$r->payroll_period_id&month=$month'>Click To Set</a>";
	  } 
	  ?>
        </font></td>
    </tr>
    <?
	}
	?>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="10"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        <input type="submit" name="p2" value="Save Checked">
        <a href="#top">Go Top</a></font></td>
    </tr>
  </table>
</form>
