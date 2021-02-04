<?
if ($p1=='Set  Selected Active Globally')
{


	$audit = "update:$admin->username:".date('m/d/Y');
	@pg_query("update payroll_period set active=null, audit='$audit' where active>0" )or die (pg_errormessage());
	$qr = @pg_query("update payroll_period set active='1', audit='$audit' where payroll_period_id='$active'" ) or die (pg_errormessage());
	if ($qr)
	{
		$q = "select * from payroll_period where payroll_period_id ='$active'";
		$qr = @pg_query($q) or message1(pg_errormessage());
		$r = @pg_fetch_object($qr);
		$SYSCONF['PAYROLL_PERIOD'] = ymd2mdy($r->period1).' - '.ymd2mdy($r->period2);
		$SYSCONF['PAYROLL_PERIOD_ID'] = $r->payroll_period_id;
		
	}
}
elseif ($p1=='Set  Selected Active Locally')
{
	$qr = pg_query("select * from cache where type='payroll_period'")or die (pg_errormessage());
	if (pg_num_rows($qr)==0)
	{
		@pg_query("insert into cache set type='payroll_period', ip='$REMOTE_ADDR', value='$active'")or die (pg_errormessage());
	}
	else
	{
		@pg_query("update cache set value='$active' where ip='$REMOTE_ADDR' and type='payroll_period'")or die (pg_errormessage());
	}
}
?><br>
<form name="form1" method="post" action="">
  <table width="60%" border="0" align="center" cellpadding="1" cellspacing="1">
    <tr> 
      <td height="27" colspan="6" bgcolor="#CCCCCC"><font size="3" face="Verdana, Arial, Helvetica, sans-serif"><strong>Select 
        Payroll Period</strong></font></td>
    </tr>
    <tr> 
      <td colspan="2" bgcolor="#E2E2E2" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Active</font></strong></td>
      <td width="35%" rowspan="2" bgcolor="#E2E2E2"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Period 
        Covered</font></strong></td>
      <td width="23%" rowspan="2" bgcolor="#E2E2E2"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Schedule</font></strong></td>
      <td width="16%" rowspan="2" bgcolor="#E2E2E2"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Number</font></strong></td>
      <td width="11%" rowspan="2" bgcolor="#E2E2E2"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Post</strong></font></td>
    </tr>
    <tr> 
      <td width="7%" bgcolor="#E2E2E2"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Global</font></strong></td>
      <td width="8%" bgcolor="#E2E2E2"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Local</font></strong></td>
    </tr>
    <?
	$qr = pg_query("select * from cache where type='payroll_period'")or die (pg_errormessage());
	if (pg_num_rows($qr) >0 )
	{
		$r = pg_fetch_object($qr);
		$local_active=$r->value;
	}

	$qr = pg_query("select * from payroll_period where enable='Y' order by period1 desc") or die (pg_errormessage());
	$ctr=0;
	while ($r = pg_fetch_object($qr))
	{
	?>
    <tr bgcolor="#FFFFFF"> 
      <td align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input type="radio" name="active" value="<?=$r->payroll_period_id;?>" <?=($r->active=='1' ? 'checked' :'');?>>
        </font></td>
      <td align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <input type="radio" name="local_active" value="<?=$r->payroll_period_id;?>" <?=($local_active==$r->payroll_period_id ? 'checked' :'');?>>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= ymd2mdy($r->period1).' - '.ymd2mdy($r->period2);?>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $r->schedule.' '.$r->days.' days';?>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $r->num.' of '.$r->num2;?>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $r->post;?>
        </font></td>
    </tr>
    <?
	}
	?>
    <tr> 
      <td colspan="6" bgcolor="#E2E2E2"><input name="p1" type="submit" id="p1" value="Set  Selected Active Globally"> 
        <input name="p1" type="submit" id="p1" value="Set  Selected Active Locally"></td>
    </tr>
  </table>
</form>
