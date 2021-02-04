 <script language="JavaScript" type="text/JavaScript">
<!--
function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);
//-->
</script>

<?
	$insertItems=5;
?>
<form name="form1" method="post" action="">
  <table width="90%" border="0" align="center" cellpadding="2" cellspacing="0">
    <tr> 
      <td width="66%" height="33" bgcolor="#D2DCDF"><font size="3" face="Verdana, Arial, Helvetica, sans-serif"><strong>Payroll 
        Transaction</strong></font></td>
      <td width="34%" nowrap bgcolor="#D2DCDF"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
        <?= $PAYROLL_PERIOD.'-'.$SCHEDULE.' '.$DAYS.' days';?>
        </strong></font></td>
    </tr>
    <tr> 
      <td colspan="2"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Employee 
        <input type="text" name="textfield">
        <input name="p1" type="submit" id="p1" value="Search">
        <input name="p1" type="submit" id="p1" value="Select">
        </font></td>
    </tr>
    <tr height=470>
      <td colspan="2" valign="top"><div id="Layer1" style="position:absolute; width:100%; height:230px; z-index:1; overflow: scroll;">
          <table width="100%" border="0" align="center" cellpadding="1" cellspacing="1">
            <tr> 
              <td colspan="4"><table width="100%" border="0" cellspacing="0" cellpadding="0">
                  <tr> 
                    <td height="20" bgcolor="#DBEFFB"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Income 
                      <select name="select">
                        <option value="1">1</option>
                        <option value="3">3</option>
                        <option value="5">5</option>
                      </select>
                      <input name="p1" type="submit" id="p1" value="Insert">
                      </strong></font></td>
                  </tr>
                </table></td>
            </tr>
            <tr bgcolor="#F0F0F0"> 
              <td width="11%"><strong>#</strong></td>
              <td width="67%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;Income 
                Type </font></strong></td>
              <td width="14%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Qty</font></strong></td>
              <td width="8%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;Amount</font></strong></td>
            </tr>
			<?
				$ctr=0;
				while ($ctr < $insertItems)
				{
					$ctr++;
					echo "<tr bgColor='#F0F0F0'><td align=right>$ctr<input type='checkbox'>.</td>
						<td>".lookUpTable2('income_type_id[]','income_type','income_type_id','income_type','')."</td>
						<td><input type=text name='qty[]' value=''></td>
						<td><input type='text' name='amount[]'></td>";
				}	
			?>
          </table>
  </div>
  <div id="Layer1" style="position:absolute; width:100%; height:230px; z-index:1; overflow: scroll; top: 350;">
          <table width="100%" border="0" align="center" cellpadding="1" cellspacing="1">
            <tr> 
              <td colspan="4"><table width="100%" border="0" cellspacing="0" cellpadding="0">
                  <tr> 
                    <td height="20" bgcolor="#DBEFFB"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Deductions  
                      <select name="select">
                        <option value="1">1</option>
                        <option value="3">3</option>
                        <option value="5">5</option>
                      </select>
                      <input name="p1" type="submit" id="p1" value="Insert">
                      </strong></font></td>
                  </tr>
                </table></td>
            </tr>
            <tr bgcolor="#F0F0F0"> 
              <td width="11%"><strong>#</strong></td>
              <td width="67%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;Income 
                Type </font></strong></td>
              <td width="14%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Qty</font></strong></td>
              <td width="8%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;Amount</font></strong></td>
            </tr>
            <tr> 
              <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;</font></td>
              <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;</font></td>
              <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;</font></td>
              <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;</font></td>
            </tr>
          </table>
  </div></td>
    </tr>
	<tr><td valign=top colspan=4>
	<table width="100%" align="center" bgcolor="#EFEFEF">
	<tr>
			<td nowrap><input name="p1" type="button" value="Delete Checked"> <input name="p1" type="button" value="Save Transaction"></td>
            <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Gross Income </strong></font></td>
            <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Total Deductions</strong></font></td>
			<td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Net Income</strong></font></td></tr>
	</table>
  </td></tr>
  </table>
  </form>
