<script>
function vSelect(gd, cd)
{
	 document.getElementById('cutoff_date').value=gd;
	 document.getElementById('cutoff_date').value=cd;
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
	
if ($cutoff_date == '')
{
	$q = "select * from accountpost where type='B' and enable='Y' order by cutoff_date desc ";
	$qr = @pg_query($q) or message(pg_errormessage().$q);
	$r = @pg_fetch_object($qr);
		
	
	if (@pg_num_rows($qr)>0 && $r->cutoff_date < date('Y-m-d'))
	{
		$cutoff_date = $r->cutoff_date;
	}
	else
	{
		$cutoff_date = date('Y-m-d');
	}
	$rate = 7.0;
}

if ($p1 == 'NextCutOff')
{
	//--> advancing cutoff date to next month
	$q = "select * from accountpost where type='B' and enable='Y' order by cutoff_date desc ";
	$qr = @pg_query($q) or message(pg_errormessage());
	$r = @pg_fetch_object($qr);
	$cutoff_date = date('Y-m-d');	
}
?>
<br>
<form name="fd" id="fd" method="post" action="">
  <table width="80%" border="0" align="center" cellpadding="1" cellspacing="1">
    <tr bgcolor="#EFEFEF" background="../graphics/table0_horizontal.PNG"> 
      <td height="26" colspan="2"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;<img src="../graphics/list3.gif"> 
        Guarantor Bond Interest Posting</font></strong></td>
    </tr>
    <tr background="#EFEFEF"> 
      <td width="10%" nowrap><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Date</font></strong></td>
      <td nowrap><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Rate</font></strong></td>
    </tr>
    <tr> 
      <td nowrap><font  color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
        <input name="cutoff_date" type="text" id="cutoff_date"  onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= ymd2mdy($cutoff_date);?>" size="9">
        <img src="../graphics/dwn-arrow-grn.gif" onclick="popUpCalendar(this, f1.cutooff_date, 'mm/dd/yyyy')"> 
        </strong></font></td>
      <td nowrap><font  color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
        </strong></font> <font  color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>
        <input name="rate" type="text" id="rate"   value="<?= $rate;?>" size="5">
        </strong></font>
<input name="p1" type="button" id="p1" value="Go" onCLick="if (confirm('Are you sure to Post BOND INTEREST with the Dates Provided?')){ document.getElementById('nextcut').style.display='none';wait('Please wait. Processing data...');xajax_bondposting(xajax.getFormValues('fd'))};"> 
        <input name="p12" type="button" id="p122" value="Close" onClick="window.location='?p'"> 
      </td>
    </tr>
  </table>
<div id="grid.layer" align="center"></div>
  <table width="80%" border="0" align="center">
    <tr bgcolor="#003366"> 
      <td width="9%" align="center"><strong><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
      <td width="17%"><strong><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif">Date</font></strong></td>
      <td><font color="#FFFFFF" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Classification</strong></font></td>
      <td width="26%"><font color="#FFFFFF" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Processed</strong></font></td>
    </tr>
    <?
	$q = "select * from accountpost where type='B'  order by cutoff_date desc offset 0 limit 15";
	$qr = @pg_query($q) or message(pg_errormessage());
	$c = 0;
	while ($r = @pg_fetch_object($qr))
	{
		$c++;
		if ($c <3 ) //== 1)
		{
			$href = '<a href="'."javascript:vSelect('".ymd2mdy($r->cutoff_date)."','".ymd2mdy($r->cutoff_date)."')".'">';
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
        <?= ymd2mdy($r->date);?></a>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; </font><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
        </font> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
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
