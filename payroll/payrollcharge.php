<script>
function vSubmit(ul)
{
	if (ul.name == 'Cancel')
	{
		if (confirm("Are you sure to CANCEL payrollcharge Entry?"))
		{
			document.f1.action="?p=payrollcharge&p1=CancelConfirm"
		}	
		else
		{
			document.f1.action="?p=payrollcharge&p1=Cancel"
		}
	}
	else
	{
		document.f1.action="?p=payrollcharge&p1="+ul.id;
	}	
}

function vAmt()
{
	document.getElementById('net_credit').value = twoDecimals(document.getElementById('check_amount').value + document.getElementById('deduction').value);
	document.getElementById('check_amount').value = twoDecimals(document.getElementById('check_amount').value);
	document.getElementById('deduction').value = twoDecimals(document.getElementById('deduction').value);
}
</script>
<?
/*
if (!chkRights2('customeraccount','madd',$ADMIN['admin_id']))
{
	message('You have [ NO ] permission to access this area...');
	exit;
}
*/
if (!session_is_registered('aPC'))
{
	session_register('aPC');
	$aPC = null;
	$aPC = array();
}
$fields_header = array('reference','date','paymast_id','deduction_type_id','credit','debit','ammort','schedule_ammort', 'remarks');

$p1 = $_REQUEST['p1'];
if (!in_array($p1, array(null,'Edit','Delete','Print','Load','Serve')))
{

	for ($c=0;$c<count($fields_header);$c++)
	{
		$aPC[$fields_header[$c]] = $_REQUEST[$fields_header[$c]];
		if (substr($fields_header[$c],0,4) == 'date' || $fields_header[$c] == 'checkdate')
		{
			$aPC[$fields_header[$c]] = mdy2ymd($_REQUEST[$fields_header[$c]]);
		}
		if (in_array($fields_header[$c],array('debit','credit','deduction_id','paymast_id','ammort')) && $_REQUEST[$fields_header[$c]] == '')
		{
			$aPC[$fields_header[$c]] = 0;
		}
		
	}


	
}

if ($p1 == 'New' or $p1 == 'Add New') 
{

	$aPC = null;
	$aPC = array();
}
elseif ($p1 == 'Load' && $id=='')
{
	message("Nothing to load...");
	exit;
}
elseif ($p1 == 'Load' && $id!='')
{
	$q = "select * from payrollcharge where payrollcharge_id='$id'";
	$qr = @pg_query($q) or message(pg_errormessage());
	$r = @pg_fetch_assoc($qr);
	$aPC = null;
	$aPC = array();
	$aPC = $r;	
}
elseif ($p1 == 'Save' && $aPC['paymast_id'] == '')
{
	message1("No employee specified...");
}
elseif ($p1 == 'CancelConfirm' && $aPC['deduction_type_id'] != '')
{
	$q = "update payrollcharge set enable='N' where payrollcharge_id = '".$aPC['payrollcharge_id']."'";
	$qr = @pg_query($q) or message1("Error Updating Tabled...");
	if ($qr)
	{
		message1("<br>Transaction Cancelled...");
	}
}
elseif ($p1 == 'Save' && $aPC['deduction_type_id'] == '')
{
	message1("No deduction type specified...");
}
elseif ($p1 == 'Save')
{
	if ($aPC['payrollcharge_id'] == '')
	{
		$balance = $aPC['credit'] - $aPC['debit'];
		$audit = 'Encoded: '.$ADMIN['username'].' on '.date('m/d/Y g:ia');
		$q = 'insert into payrollcharge (audit, admin_id,balance';
		$q1 = "values ('$audit','".$ADMIN['admin_id']."', '$balance'";
		$c=0;
		while (list ($key, $val) = each ($fields_header)) 
		{
			$item = chop($val);
			$q .= ',';
			$q1 .= ',';
			$q .= $item;
			$q1 .= "'".$aPC[$item]."'";
			$c++;
		}
		$q .= ') '.$q1.')';

	}
	else
	{
		$balance = $aPC['credit'] - $aPC['debit']  - $aPC['deduct'];
		
		$audit = $aPC['audit']. 'Updated: '.$ADMIN['username'].' on '.date('m/d/Y g:ia');
		$q = "update payrollcharge set audit ='$audit' ";
		
		for ($c=0;$c<count($fields_header);$c++)
		{
			if (in_array($fields_header[$c], array('account','checkstatus')) ) continue;
			$q .=  ", ".$fields_header[$c]." = '".$aPC[$fields_header[$c]]."'";
		}
		$q .= ", balance = '$balance' ";
	}
	if ($aPC['payrollcharge_id'] != '')
	{
		$q .= " where payrollcharge_id='".$aPC['payrollcharge_id']."'";
	}

	$qr = @pg_query($q) or message1(pg_errormessage().$q);


	if ($qr)
	{
		if ($aPC['payrollcharge_id'] == '')
		{
			$qr = @query("select currval('payrollcharge_payrollcharge_id_seq'::text)");
			$r = @pg_fetch_object($qr);
			$aPC['payrollcharge_id'] = $r->currval;
		}
		
		include_once("xajax.payroll.php");
		$pc = payroll_recalc_account_process($aPC['paymast_id'], $aPC['deduction_type_id']);
		message("Transaction Saved...");
	}	
	else
	{
		message("Unable To Save payrollcharge Entry ".pg_errormessage().$q);
	}
	
}
?>
<br>
<form name="f1" id="f1" method="post" action="">
  <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr>
      <td>Search 
        <input name="xSearch" type="text" id="xSearch" value="<?= $xSearch;?>">
        <?=lookUpAssoc('searchby',array('Reference'=>'reference','Account'=>'elast','Check'=>'mcheck','Check Date'=>'checkdate','Date Entry'=>'date','Amount'=>'amount'),$searchby);?>
        <input name="p12" type="button" id="go" value="Go" accesskey="G" onClick="document.getElementById('f1').action='?p=payrollcharge.browse&p1=Go&xSearch='+xSearch.value+'&searchby='+searchby.value;document.getElementById('f1').submit()"> 
        <input name="p1" type="button" id="p14" value="Add New" onClick="window.location='?p=payrollcharge&p1=New'">
        <input name="p1" type="button" id="p1" value="Browse" onClick="window.location='?p=payrollcharge.browse&p1=New'"> 
        <input name="p1" type="button" id="p15" value="Close" onClick="window.location='?p='"> </td>
    </tr>
  </table>
  <hr align="center" color="#993300" width="95%">
  <table width="70%" border="0" align="center" cellpadding="1" cellspacing="1" bgcolor="#EFEFEF">
    <tr bgcolor="#003366" background="../graphics/table0_horizontal.PNG"> 
      <td colspan="4" bgcolor="#003366" background="../graphics/table0_horizontal.PNG"><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Payroll 
        Charges Entry</strong></font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td width="15%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Reference</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="reference" type="text" id="reference" value="<?= $aPC['reference'];?>" size="10"  onKeypress="if(event.keyCode==13) {document.getElementById('date').focus();return false;}">
        </font></td>
      <td colspan="2" align="center"><?= ($aPC['enable'] =='N' ? 'CANCELLED' : '');?></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td height="22"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Date</font></td>
      <td colspan="3"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="date" type="text" id="date" value="<?= ymd2mdy($aPC['date']);?>" size="10"  onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')"  onKeypress="if(event.keyCode==13) {document.getElementById('paymast_id').focus();return false;}">
        <img src="../graphics/dwn-arrow-grn.gif" onclick="popUpCalendar(this, f1.date, 'mm/dd/yyyy')"> 
        </font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Employee</font></td>
      <td colspan="3"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <select name="paymast_id" id="paymast_id" style="width:280"  onKeypress="if(event.keyCode==13) {document.getElementById('deduction_type_id').focus();return false;}">
          <option value="">Select Employee Account</option>
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
        </font><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
        </font><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
        </font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Account</font></td>
      <td colspan="3"> <select name="deduction_type_id" id="deduction_type_id"  onKeypress="if(event.keyCode==13) {document.getElementById('remarks').focus();return false;}">
          <option value="">Select Account</option>
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
        </select> </td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td valign="top"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Remarks</font></td>
      <td colspan="3"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <textarea name="remarks" cols="50" rows="2" id="remarks"><?= $aPC['remarks'];?></textarea>
        </font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Credit/Charges</font></td>
      <td width="55%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="credit"   type="text" id="credit" style="text-align:right" value="<?= $aPC['credit'];?>" size="15" maxlength="15"  onKeypress="if(event.keyCode==13) {document.getElementById('debit').focus();return false;}">
        </font></td>
      <td width="9%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Deducted</font></td>
      <td width="21%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="deduct"   type="text" id="deduct" style="text-align:right; background:'#EFEFEF'" value="<?= $aPC['deduct'];?>" size="15"  readOnly>
        </font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Debits</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="debit"   type="text" id="debit" style="text-align:right" value="<?= $aPC['debit'];?>" size="15"  onKeypress="if(event.keyCode==13) {document.getElementById('ammort').focus();return false;}">
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Balance</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="balance"   type="text" id="balance" style="text-align:right" value="<?= $aPC['balance'];?>" size="15"  readOnly>
        </font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">PayrollDeduction</font></td>
      <td colspan="3"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="ammort"   type="text" id="ammort" style="text-align:right" value="<?= $aPC['ammort'];?>" size="15"  onKeypress="if(event.keyCode==13) {document.getElementById('Save').focus();return false;}">
        <?= lookUpAssoc('schedule_ammort',array('Manual'=>'M','Every Payday'=>'E','Cutoff 1'=>'1','Cutoff  2'=>'2','Cutoff 3'=>'3','Cutoff 4'=>'4'),$aPC['schedule_ammort']);?>
        </font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="4"><table width="1%" border="0" cellpadding="1" cellspacing="1" bgcolor="#CCCCCC">
          <tr bgcolor="#FFFFFF"> 
            <td nowrap width="3%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              <input type="image" src="../graphics/save.jpg" alt="Save This Claim Form" width="57" height="15" id="Save" onClick="vSubmit(this)" name="Save">
              </strong></font></td>
            <td nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              <input type="image" src="../graphics/print.jpg" alt="Print This Claim Form"  onClick="vSubmit(this)" name="Print" id="Print">
              </strong></font></td>
            <td nowrap width="25%"> <input type='image' name="Cancel" id="Cancel" onClick="vSubmit(this)"  src="../graphics/cancel.jpg" alt="Cancel Form" width="77" height="20"> 
            <td nowrap width="25%"> <input type='image' name="New" id="New" onClick="vSubmit(this)"  src="../graphics/new.jpg" alt="New Claim Form" width="63" height="20"> 
            </td>
          </tr>
        </table></td>
    </tr>
  </table>
</form>

 