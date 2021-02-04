<?
if ($aPT!= null && !in_array($p1,array('Browse','Next','Previous')))
{
	echo "<script>window.location='?p=payroll.transaction'</script>";
	exit;
}
if ($p1 == 'Go')
{
	$q = "select * from payroll_period where payroll_period_id = '".$SYSCONF['PAYROLL_PERIOD_ID']."'";
	$qr = @pg_query($q) or message(pg_erormessage().$q);
	$r = @pg_fetch_object($qr);
}

?><br>
<form name="form1" method="post" action="">
  <table width="80%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Payroll Transaction</font>
        <input name="year" type="text" id="year" value="<?= $SYSCONF['year'];?>" size="4" maxlength="4">
        <?= lookUpPayPeriod('payroll_period_id',$SYSCONF['PAYROLL_PERIOD_ID']);?>
        <input name="p1" type="submit" id="p1" value="Go">
        <input name="p1" type="button" id="p1" value="Setup Pay Period" onClick="window.location='?p=payroll_period'">
        <input name="p1" type="button" id="p1" value="Add New Entry" onClick="window.location='?p=payroll.transaction&p1=New'";>
        </strong> 
        <hr color="#993300">
        <strong> <font size="2" face="Verdana, Arial, Helvetica, sans-serif">Active 
        Pay Period : <font color="#CC0000"> 
        <?= $SYSCONF['PAYROLL_PERIOD'];?>
        </font></font></strong></td>
    </tr>
  </table>
  <table width="80%" border="0" align="center" cellpadding="2" cellspacing="1" bgcolor="#EFEFEF">
    <tr bgcolor="#C2D6C0"> 
      <td width="7%" height="22"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
      <td width="43%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Employee</font></strong></td>
      <td width="25%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Branch</font></strong></td>
      <td width="25%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Department</font></strong></td>
    </tr>
	<?
		$q = "select * from 
							paymast, payroll_header 
					where 
							paymast.paymast_id=payroll_header.paymast_id and 
							payroll_period_id='".$SYSCONF['PAYROLL_PERIOD_ID']."'
					order 
							by elast, efirst ";

		$qr= @pg_query($q) or message(pg_errormessage().$q);
		$ctr = 0;
		while ($r = @pg_fetch_object($qr))
		{
			$ctr++;
			if ($r->status == 'C')
			{
				$cstatus = '****(CANCELLED)';
			}
			else
			{
				$cstatus = '';
			}
	?>
    <tr bgcolor="#FFFFFF"> 
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><?= $ctr;?>.</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
	  <a href="?p=payroll.transaction&p1=Load&id=<?=$r->payroll_header_id;?>">
	  <?= $r->elast.', '.$r->efirst.' '.$cstatus ;?></a>
	  </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
	  <?=lookUpTableReturnValue('x','branch','branch_id','branch',$r->branch_id);?></font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?=lookUpTableReturnValue('x','department','department_id','department',$r->department_id);?>
        </font></td>
    </tr>
	<?
	}
	?>
  </table>
</form>
