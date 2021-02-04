<?

if ($p1 != 'Go')
{
	$from_date = ymd2mdy($SYSCONF['PERIOD1']);
	$to_date = ymd2mdy($SYSCONF['PERIOD2']);
}
else
{
	$from_date = $_REQUEST['from_date'];
	$to_date = $_REQUEST['to_date'];
}
?>
<br>
<form name="f1" id="f1" method="post" action="">
  <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr bgcolor="#CCCCCC" background="../graphics/table0_horizontal.PNG"> 
      <td colspan="4" background="../graphics/table0_horizontal.PNG"><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
        &nbsp;</strong>..::<strong> Payroll Charges </strong>::..</font></td>
    </tr>
    <tr bgcolor="#CCCCCC"> 
      <td width="19%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Search 
        </font><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
        </font> </td>
      <td width="3%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Show</font></td>
      <td width="1%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">From</font></td>
      <td width="77%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">To 
        </font></td>
    </tr>
    <tr> 
      <td nowrap><input name="xSearch" type="text" id="xSearch2" value="<?= $xSearch;?>"> 
        <?=lookUpAssoc('searchby',array('Reference'=>'reference','Account'=>'elast','Check'=>'mcheck','Check Date'=>'checkdate','Date Entry'=>'date','Amount'=>'credit'),$searchby);?>
      </td>
      <td nowrap> 
        <?=lookUpAssoc('show',array('Filter Cancelled'=>'Y','Show All'=>'','Cancelled Only'=>'N'),$show);?>
      </td>
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="from_date" type="text" id="from_date3" value="<?= $from_date;?>" size="10"  onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')"  onKeyPress="if(event.keyCode==13) {document.getElementById('paymast_id').focus();return false;}">
        <img src="../graphics/dwn-arrow-grn.gif" onclick="popUpCalendar(this, f1.from_date, 'mm/dd/yyyy')"></font></td>
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="to_date" type="text" id="to_date2" value="<?=$to_date;?>" size="10"  onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')"  onKeypress="if(event.keyCode==13) {document.getElementById('paymast_id').focus();return false;}">
        <img src="../graphics/dwn-arrow-grn.gif" onclick="popUpCalendar(this, f1.to_date, 'mm/dd/yyyy')"> 
        <input name="p1" type="submit" id="p1" value="Go">
        <input name="p1" type="button" id="p1" value="Add New" onClick="window.location='?p=payrollcharge&p1=New'">
        <input name="p1" type="submit" id="p1" value="Close">
        </font></td>
    </tr>
  </table>
  <hr align="center" color="#993300" width="95%">
  
  <table width="95%" border="0" align="center" cellpadding="1" cellspacing="1" bgcolor="#EFEFEF">
    <tr bgcolor="#CCCCCC"> 
      <td width="4%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></td>
      <td width="9%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Date</font></td>
      <td width="9%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Reference</font></td>
      <td width="23%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Employee</font></td>
      <td width="13%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Account</font></td>
      <td width="11%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Credit</font></td>
      <td width="11%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Debit</font></td>
      <td width="10%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Deducted</font></td>
      <td width="8%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Status</font></td>
    </tr>
    <?
		$mfrom_date = mdy2ymd($from_date);
		$mto_date = mdy2ymd($to_date);
		
		$q = "select 
						paymast.paymast_id,
						paymast.elast,
						paymast.efirst,
						payrollcharge.date,
						payrollcharge.payrollcharge_id,
						payrollcharge.reference,
						payrollcharge.deduction_type_id,
						payrollcharge.credit,
						payrollcharge.debit,
						payrollcharge.deduct,
						payrollcharge.enable
					from 
						payrollcharge ,
						paymast
					where
						payrollcharge.paymast_id = paymast.paymast_id";
		if ($show != '')
		{
			$q .= " and payrollcharge.enable = '$show'";
		}
		if ($from_date != '')
		{
			$q .= " and date  >= '$mfrom_date'";
		}
		if ($to_date != '')
		{
			$q .= " and date  <= '$mto_date'";
		}
		if ($xSearch != '')
		{
			$q .= " and $searchby ilike '$xSearch%'";
		}
		$q .=" order by date desc ";
		$qr = @pg_query($q) or message(pg_errormessage());
		$ctr=0;
		while ($r = @pg_fetch_object($qr))
		{
			$ctr++;
			if ($r->enable == 'Y')
			{
				$bgColor = '#FFFFFF';
			}
			else
			{
				$bgColor = '#FFCCCC';
			}
	?>
    <tr bgcolor="<?=$bgColor;?>" onClick="window.location='?p=payrollcharge&p1=Load&id=<?=$r->payrollcharge_id;?>'" onMouseOver="bgColor='#FFFFCC'" onMouseOut="bgColor='<?=$bgColor;?>'"> 
      <td align="right" nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?=$ctr;?>
        . </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href="?p=payrollcharge&p1=Load&id=<?= $r->payrollcharge_id;?>"> 
        <?=ymd2mdy($r->date);?>
        </a></font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href="?p=payrollcharge&p1=Load&id=<?= $r->payrollcharge_id;?>"> 
        <?= $r->reference;?>
        </a></font> </td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href="?p=payrollcharge&p1=Load&id=<?= $r->payrollcharge_id;?>"> 
        <?= $r->elast.', '.$r->efirst;?>
        </a></font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= lookUpTableReturnValue('x','deduction_type','deduction_type_id','deduction_type',$r->deduction_type_id);?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= number_format2($r->credit,2);?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= number_format2($r->debit,2);?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= number_format2($r->deduct,2);?>
        </font></td>
      <td align="center"> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= ($r->enable =='N' ?'Cancelled' : '');?>
        </font></td>
      <?
	  }
	  ?>
    </tr>
  </table>
</form>

 