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
  <table width="60%" border="0" align="center" cellpadding="0" cellspacing="1">
    <tr background="../graphics/table0_horizontal.PNG"> 
      <td height="23" colspan="2">&nbsp;<font size="2" face="Verdana, Arial, Helvetica, sans-serif">Recalculate 
        Accounts</font></td>
    </tr>
    <tr> 
      <td width="17%" height="18">&nbsp;</td>
      <td width="83%">&nbsp;</td>
    </tr>
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Employee</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <select name="paymast_id" id="paymast_id" style="width:280"  onKeypress="if(event.keyCode==13) {document.getElementById('date').focus();return false;}">
          <option value="">All Employee Accounts</option>
          <?
		$q = "select * from paymast where enable='Y'   order by elast, efirst";
		$qr = @pg_query($q);
		while ($r = @pg_fetch_object($qr))
		{
			if ($aPC['paymast_id'] == $r->paymast_id)
			{
				echo "<option value=$r->paymast_id selected>$r->elast, $r->efirst</option>";
			}
			else
			{
				echo "<option value=$r->paymast_id>$r->elast, $r->efirst</option>";
			}
		}
	?>
        </select>
        </font></td>
    </tr>
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">AccountType</font></td>
      <td><select name="deduction_type_id">
          <option value="">All Accounts</option>
          <?
	  	$q = "select * from deduction_type where enable='Y' and basis='L' order by deduction_type";
		$qr = @pg_query($q);
		while ($r = @pg_fetch_object($qr))
		{
			if ($r->deduction_type_id == $aPC['deduction_type_id'])
			{
				echo "<option value = '$r->deduction_type_id' selected>$r->deduction_type</option>";
			}
			else
			{
				echo "<option value = '$r->deduction_type_id'>$r->deduction_type</option>";
			}
		}
	  ?>
        </select></td>
    </tr>
    <tr> 
      <td colspan="2"><hr></td>
    </tr>
    <tr> 
      <td colspan="2"><input name="p133" type="button" id="p132" value="Proceed With Recalculation" onCLick="wait('Please wait. Processing data...');xajax_payroll_recalc_account(xajax.getFormValues('fd'));"></td>
    </tr>
  </table>
  </form>
