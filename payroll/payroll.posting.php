<?
	$month = $_REQUEST['month'];
	$year = $_REQUEST['year'];
	
	if ($year == '')
	{
		$year = date('Y');
	}
	if ($month == '')
	{
		if (date('d') < 10)
		{
			$month = date('m') - 1;
			if ($month < 1) 
			{
				$month = 12;
				$year--;
			}
		}
		else
		{
			$month = date('m');
		}
	}	

?>
<br>
<form name="fd" id="fd" method="post" action="">
  <table width="80%" border="0" align="center" cellpadding="1" cellspacing="1">
    <tr bgcolor="#EFEFEF" background="../graphics/table0_horizontal.PNG"> 
      <td width="22%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Payroll 
        Posting</font></strong></td>
      <td width="10%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Month/Year</font></strong></td>
      <td width="68%"><strong></strong></td>
    </tr>
    <tr> 
      <td nowrap>&nbsp;</td>
      <td nowrap><font  color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
        </strong></font><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        <?= lookUpMonth('month',$month);?>
        </font><font face="Verdana, Arial, Helvetica, sans-serif" size="2">
        <input name="year" type="text" value="<?=date('Y');?>" size="5" maxlength="4">
        </font><font  color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
        </strong></font> </td>
      <td nowrap> 
        <input name="p1" type="submit" id="p1" value="Go" > 
        <input name="p12" type="button" id="p122" value="Close" onClick="window.location='?p'"> 
      </td>
    </tr>
  </table>
  <table width="80%" border="0" align="center">
    <tr bgcolor="#003366"> 
      <td width="10%" align="center"><strong><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
      <td width="18%"><strong><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif">From</font></strong></td>
      <td width="18%"><strong><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif">To</font></strong></td>
      <td width="18%"><strong><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif">Schedule</font></strong></td>
      <td width="36%"><strong><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif">Remark</font></strong></td>
    </tr>
    <?
	$q = "select * from payroll_period where enable='Y' ";
	if ($month > '0')
	{
		$q .= " and month ='$month'";
	}
	if ($year > '0')
	{
		$q .= " and year ='$year'";
	}
	$q .= " 	order by period1";
	$qr = @pg_query($q) or message1(pg_errormessage().$q);
	if (@pg_num_rows($qr) == '0')
	{
		message1(" NO Payroll Period Created with the specified MONTH/YEAR");
	}
	while ($r = @pg_fetch_object($qr))
	{
		$ctr++;
	?>
    <tr> 
      <td align=right nowrap> <font size="2"> 
        <?=$ctr;?>
        . 
        <input type="radio" name="mark" id="mark" value="<?=$r->payroll_period_id;?>">
        </font></td>
      <td> <font size="2"> 
        <?= $r->period1;?>
        </font></td>
      <td> <font size="2"> 
        <?= $r->period2;?>
        </font></td>
      <td> <font size="2"> 
        <?= $r->num.' of '.$r->num2."($r->schedule $r->days days)";;?>
        </font></td>
      <td> <font size="2"> 
        <?= ($r->post == 'Y' ? 'Posted' : 'Not Posted');?>
        </font></td>
    </tr>
    <?
	}
	?>
    <tr> 
      <td colspan="5" ><div id="grid.layer">
          <input name="p13" type="button" id="p12" value="Post Selected" onCLick="if (confirm('Are you SURE to Post Payroll Transactions?')) {wait('Please wait. Processing data...');xajax_payroll_posting(xajax.getFormValues('fd'));}">
          <input name="p132" type="button" id="p13" value="Undo Posting" onCLick="if (confirm('Are you SURE to UN-Post Payroll Transactions? Be sure to recalculate accounts after...')) {wait('Please wait. Processing data...');xajax_payroll_unpost(xajax.getFormValues('fd'));}">
        </div></td>
    </tr>
    <tr> 
      <td colspan="5" ><div id="grid.layer"></div></td>
    </tr>
  </table>
 </form>
