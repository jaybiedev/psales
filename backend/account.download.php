<?
/*	while (file_get_contents("/prog/ics/"))
	{
		echo "x";
	}
*/
	if ($SYSCONF['BRANCH_ID']==1)
	{
		$filename = 'LEC_ACCT_INFO.TXT';
	}
	else
	{
		$filename = 'LA_ACCT_INFO.TXT';
	}
	if ($date == '') $date=ymd2mdy(yesterday());
	
?>
<br>
<form name="fd" id="fd" method="post" action="">
  <table width="80%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr bgcolor="#EFEFEF" background="../graphics/table0_horizontal.PNG"> 
      <td width="0%" height="24" background="../graphics/table_left.PNG">&nbsp;</td>
      <td width="0%" background="../graphics/table_horizontal.PNG">&nbsp;</td>
      <td width="100%" background="../graphics/table_horizontal.PNG"><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Account 
        Info Download</strong></font> </td>
      <td width="0%" background="../graphics/table_right.PNG">&nbsp;</td>
    </tr>
    <tr> 
      <td colspan="4" nowrap><table width="100%%" border="0" cellpadding="0" cellspacing="1" bgcolor="#DDE5F3">
          <tr bgcolor="#DDE5F3"> 
            <td width="18%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Type 
              of Accounts</font></td>
            <td width="82%"><select name="account_type_id"  style="width:250px">
                <option value=''>Select Account Type</option>
                <?
	  	$q = "select * from account_type where enable='Y' order by account_type";
		$qr = @pg_query($q);
		while ($r=@pg_fetch_object($qr))
		{
			if ($account_type_id == '' && $r->account_type_id == 2)
			{
				echo "<option value=$r->account_type_id selected>$r->account_type</option>";	
			}
			else
			{
				echo "<option value=$r->account_type_id>$r->account_type</option>";	
			}
		}	
	  ?>
              </select></td>
          </tr>
          <tr bgcolor="#DDE5F3"> 
            <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Application 
              Date</font></td>
            <td><font  color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              <input name="date" type="text" id="date2"  onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $date;?>" size="9">
              <img src="../graphics/dwn-arrow-grn.gif" onclick="popUpCalendar(this, f1.date, 'mm/dd/yyyy')"> 
              </strong></font></td>
          </tr>
          <tr bgcolor="#DDE5F3"> 
            <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Filename</font></td>
            <td><input name="filename" type="text" id="filename2" value="<?= $filename;?>" size="50">
              <input name="p1" type="button" id="p14" value="Go" onCLick="wait('Please wait. Downloading account information...');xajax_account_download(xajax.getFormValues('fd'));"> 
              <input name="p12" type="button" id="p125" value="Close" onClick="window.location='?p'"></td>
          </tr>
          <tr bgcolor="#DDE5F3"> 
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
          <tr bgcolor="#DDE5F3"> 
            <td colspan="2" align="center">&nbsp;</td>
          </tr>
          <tr bgcolor="#DDE5F3"> 
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
        </table></td>
    </tr>
  </table>
  <div id="grid.layer" align="center"></div>
  </form>
