<script>
function vSelect(gd, cd, dd)
{
	 document.getElementById('grace_date').value=gd;
	 document.getElementById('cutoff_date').value=cd;
	 document.getElementById('date').value=dd;
	 return;
}
</script>
<?
/*	while (file_get_contents("/prog/ics/"))
	{
		echo "x";
	}
*/
	if (!chkRights2('arposting','madd',$ADMIN['admin_id']))
	{
		message('You are NOT allowed in this option...');
		exit;
	}
	
if ($cutoff_date == '' && 0)
{
	$q = "select * from accountpost where enable='Y' order by cutoff_date desc ";
	$qr = @pg_query($q) or message(pg_errormessage());
	$r = @pg_fetch_object($qr);
	
	
	if ($r->cutoff_date < date('Y-m-d'))
	{
		$cutoff_date = ymd2mdy($r->cutoff_date);
		$grace_date = ymd2mdy($r->grace_date);
	}
	elseif (@pg_num_rows($qr) > 0)
	{
		$c = explode('-',$r->cutoff_date);
		$g = explode('-',$r->grace_date);
		if ($c[1] < 12)
		{
			$mo=$c[1]+1;
			$cutoff_date = $mo.'/'.$c[2].'/'.$c[0];
		}
		else
		{
			$yr = $c[0]+1;
			$cutoff_date = '01/'.$c[2].'/'.$yr;
		}
		if ($g[1] < 12)
		{
			$mo =$g[1]+1;
			$grace_date = $mo.'/'.$g[2].'/'.$g[0];
		}
		else
		{
			$yr= $g[0]+1;
			$grace_date = '01/'.$g[2].'/'.$yr;
		}
	}
	$date = date('m/d/Y');
}

if ($p1 == 'NextCutOff')
{
	//--> advancing cutoff date to next month
	$q = "select * from accountpost where enable='Y' order by cutoff_date desc ";
	$qr = @pg_query($q) or message(pg_errormessage());
	$r = @pg_fetch_object($qr);
	
	$c = explode('-',$r->cutoff_date);
	$g = explode('-',$r->grace_date);
	
	if ($c[1] < 12)
	{
		$mo=$c[1]+1;
		if (strlen($mo)<2) $mo='0'.$mo;
		$advanced_cutoff_date = $mo.'/'.$c[2].'/'.$c[0];
	}
	else
	{
		$yr=$c[0]+1;
		$advanced_cutoff_date = '01/'.$c[2].'/'.$yr;
	}
	
	//next grace date
	if ($g[1] < 12)
	{
		$mo=$g[1]+1;
		if (strlen($mo)<2) $mo='0'.$mo;
		$advanced_grace_date = $mo.'/'.$g[2].'/'.$g[0];
	}
	else
	{
		$yr=$g[0]+1;
		$advanced_grace_date = '01/'.$g[2].'/'.$yr;
	}
	$cutoff_date = $advanced_cutoff_date;
	$grace_date = $advanced_grace_date;
	$date = date('m/d/Y');
	
	if ($cutoff_date > date('m/d/Y'))
	{
		message1("Next Cutoff date is: $cutoff_date; End of grace period: $grace_date is Beyond Current Date.");
		$cutoff_date = '';
		$grace_date = '';
	}
}
if ($date == '')
{
	$date = date('m/d/Y');
}
?>
<br>
<form name="fd" id="fd" method="post" action="">
  <table width="80%" border="0" align="center" cellpadding="1" cellspacing="1">
    <tr bgcolor="#EFEFEF" background="../graphics/table0_horizontal.PNG"> 
      <td height="26" colspan="5"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;<img src="../graphics/list3.gif"> 
        Accounts Transaciton Posting</font></strong></td>
    </tr>
    <tr background="#EFEFEF"> 
      <td width="10%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Cut-Off</font></td>
      <td width="11%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Grace 
        Period </font></td>
      <td width="10%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Posting</font></td>
      <td width="20%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Account 
        Classification </font></td>
      <td width="49%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Card</font></td>
    </tr>
    <tr> 
      <td nowrap><font  color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
        <input name="cutoff_date" type="text" id="cutoff_date"  onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $cutoff_date;?>" size="9" style="border: #CCCCCC 1px solid; ">
        <img src="../graphics/dwn-arrow-grn.gif" onclick="popUpCalendar(this, fd.cutoff_date, 'mm/dd/yyyy')"> 
        </strong></font></td>
      <td nowrap><font  color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
        <input name="grace_date" type="text" id="grace_date"  onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $grace_date;?>" size="9" style="border: #CCCCCC 1px solid; ">
        </strong></font><img src="../graphics/dwn-arrow-grn.gif" onclick="popUpCalendar(this, fd.grace_date, 'mm/dd/yyyy')"> 
      </td>
      <td nowrap><font  color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
        <input name="date" type="text" id="date"  onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $date;?>" size="9" style="border: #CCCCCC 1px solid; ">
        </strong></font><img src="../graphics/dwn-arrow-grn.gif" onclick="popUpCalendar(this, fd.date, 'mm/dd/yyyy')"> 
      </td>
      <td nowrap> <select  name="account_class_id" style="border: #CCCCCC 1px solid; width:250px">
          <option value=''>All Account Classifications</option>
          <?
	  $q = "select * from account_class where enable='Y'";
	  $qr = @pg_query($q);
	  while ($r = @pg_fetch_object($qr))
	  {
	  	if ($account_class_id == $r->account_class_id)
		{
	  		echo "<option value=$r->account_class_id selected>$r->account_class</option>";
		}
		else
		{
	  		echo "<option value=$r->account_class_id>$r->account_class</option>";
		}
	  }
	  ?>
        </select>
      </td>
      <td nowrap><input name="cardno" type="text" id="cardno" size="8"  style="border: #CCCCCC 1px solid; ">
        <input name="p1" type="button" id="p1" value="Go" onCLick="if (confirm('Are you sure to Post A/R with the Dates Provided?')){ document.getElementById('nextcut').style.display='none';wait('Please wait. Processing data...');xajax_posting(xajax.getFormValues('fd'))};">
        <input name="p12" type="button" id="p12" value="Close" onClick="window.location='?p'"></td>
    </tr>
  </table>
<div id="grid.layer" align="center"></div>
  <table width="80%" border="0" align="center">
    <tr bgcolor="#003366"> 
      <td width="9%" align="center"><strong><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
      <td width="17%"><strong><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif">Date</font></strong></td>
      <td width="17%"><strong><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif">Cutoff</font></strong></td>
      <td width="12%"><strong><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif">Grace 
        Period </font></strong></td>
      <td width="19%"><font color="#FFFFFF" size="2"><strong>Classification</strong></font></td>
      <td width="26%"><font color="#FFFFFF" size="2"><strong>Processed</strong></font></td>
    </tr>
    <?
	$q = "select * from accountpost where type='A' order by cutoff_date desc offset 0 limit 15";
	$qr = @pg_query($q) or message(pg_errormessage());
	$c = 0;
	while ($r = @pg_fetch_object($qr))
	{
		$c++;
		if ($c <3 ) //== 1)
		{
			$href = '<a href="'."javascript:vSelect('".ymd2mdy($r->grace_date)."','".ymd2mdy($r->cutoff_date)."','".ymd2mdy($r->date)."')".'">';
		}
		else
		{	
			$href='';
		}
	?>
    <tr> 
      <td align="right"> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $c;?>
        . </font></td>
      <td> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?=$href;?> 
        <?= ymd2mdy($r->date);?>
        </a> </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= ymd2mdy($r->cutoff_date);?>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= ymd2mdy($r->grace_date);?>
        </font></td>
      <td> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?
	  	if ($r->account_class_id > 0)
		{
			echo lookUpTableReturnValue('x','account_class','account_class_id','account_class',$r->account_class_id);
		}
		else
		{
			echo "All";
		}
	  ?>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= lookUpTableReturnValue('x','admin','admin_id','name',$r->admin_id);?>
        </font></td>
    </tr>
    <?
	}
	?>
  </table>
  <br>
  <div align="center" id='nextcut'><a href="?p=account.posting&p1=NextCutOff"><font size="2">Click Here For NEXT Cut-Off Date</font></a></div>
 </form>
