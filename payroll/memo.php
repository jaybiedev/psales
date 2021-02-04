<script>
function vSubmit(ul)
{
	if (ul.name == 'Cancel')
	{
		if (confirm("Are you sure to CANCEL memo Entry?"))
		{
			document.f1.action="?p=memo&p1=CancelConfirm"
		}	
		else
		{
			document.f1.action="?p=memo&p1=Cancel"
		}
	}
	else
	{
		document.f1.action="?p=memo&p1="+ul.id;
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
if (!session_is_registered('aMemo'))
{
	session_register('aMemo');
	$aMemo = null;
	$aMemo = array();
}
$fields_header = array('reference','date','paymast_id','cc','memo');

$p1 = $_REQUEST['p1'];
if (!in_array($p1, array(null,'Edit','Delete','Print','Load','Serve')))
{

	for ($c=0;$c<count($fields_header);$c++)
	{
		$aMemo[$fields_header[$c]] = $_REQUEST[$fields_header[$c]];
		if (substr($fields_header[$c],0,4) == 'date' || $fields_header[$c] == 'checkdate')
		{
			$aMemo[$fields_header[$c]] = mdy2ymd($_REQUEST[$fields_header[$c]]);
		}
		if (in_array($fields_header[$c],array('debit','credit','deduction_id','paymast_id')) && $_REQUEST[$fields_header[$c]] == '')
		{
			$aMemo[$fields_header[$c]] = 0;
		}
		
	}


	
}

if ($p1 == 'New' or $p1 == 'Add New') 
{

	$aMemo = null;
	$aMemo = array();
}
elseif ($p1 == 'Load' && $id=='')
{
	message("Nothing to load...");
	exit;
}
elseif ($p1 == 'Load' && $id!='')
{
	$q = "select * from memo where memo_id='$id'";
	$qr = @pg_query($q) or message(pg_errormessage());
	$r = @pg_fetch_assoc($qr);
	$aMemo = null;
	$aMemo = array();
	$aMemo = $r;	
}
elseif ($p1 == 'Save')
{
	if ($aMemo['memo_id'] == '')
	{
		$audit = 'Encoded: '.$ADMIN['username'].' on '.date('m/d/Y g:ia');
		$q = 'insert into memo (audit, admin_id';
		$q1 = "values ('$audit','".$ADMIN['admin_id']."'";
		$c=0;
		while (list ($key, $val) = each ($fields_header)) 
		{
			$item = chop($val);
			$q .= ',';
			$q1 .= ',';
			$q .= $item;
			$q1 .= "'".$aMemo[$item]."'";
			$c++;
		}
		$q .= ') '.$q1.')';

	}
	else
	{
		$audit = $aMemo['audit']. 'Updated: '.$ADMIN['username'].' on '.date('m/d/Y g:ia');
		$q = "update memo set audit ='$audit' ";
		for ($c=0;$c<count($fields_header);$c++)
		{
			if (in_array($fields_header[$c], array('account','checkstatus')) ) continue;
			$q .=  ", ".$fields_header[$c]." = '".$aMemo[$fields_header[$c]]."'";
		}
	}
	if ($aMemo['memo_id'] != '')
	{
		$q .= " where memo_id='".$aMemo['memo_id']."'";
	}

	$qr = @pg_query($q) or message1(pg_errormessage().$q);


	if ($qr)
	{
		if ($aMemo['memo_id'] == '')
		{
			$qr = @query("select currval('memo_memo_id_seq'::text)");
			$r = @pg_fetch_object($qr);
			$aMemo['memo_id'] = $r->currval;
		}
		message("Transaction Saved...");
	}	
	else
	{
		message("Unable To Save memo Entry ".pg_errormessage().$q);
	}
	
}
?>
<br>
<form name="f1" method="post" action="">
  <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr>
      <td>Search 
        <input name="xSearch" type="text" id="xSearch" value="<?= $xSearch;?>"> 
        <?=lookUpAssoc('searchby',array('Name'=>'elas','Reference'=>'reference','Date Entry'=>'date','Amount'=>'amount'),$searchby);?>
        <input name="p1" type="submit" id="p14" value="Go"> <input name="p1" type="button" id="p14" value="Add New" onClick="window.location='?p=memo&p1=New'">
        <input name="p1" type="button" id="p1" value="Browse" onClick="window.location='?p=memo.browse&p1=New'"> 
        <input name="p1" type="submit" id="p15" value="Close"> </td>
    </tr>
  </table>
  <hr align="center" color="#993300" width="95%">
  <table width="70%" border="0" align="center" cellpadding="1" cellspacing="1" bgcolor="#EFEFEF">
    <tr bgcolor="#003366"> 
      <td colspan="2"><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Memo 
        Entry </strong></font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td width="14%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Reference</font></td>
      <td width="86%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="reference" type="text" id="reference" value="<?= $aMemo['reference'];?>" size="10">
        </font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td height="22"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Date</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="date" type="text" id="date" value="<?= ymd2mdy($aMemo['date']);?>" size="10">
        <img src="../graphics/dwn-arrow-grn.gif" onclick="popUpCalendar(this, f1.date, 'mm/dd/yyyy')"> 
        </font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Employee</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <select name="paymast_id" id="paymast_id" style="width:280"  onKeypress="if(event.keyCode==13) {document.getElementById('date').focus();return false;}">
          <option value="">Select Employee Account</option>
          <?
		$q = "select * from paymast where enable='Y'   order by elast, efirst";
		$qr = @pg_query($q);
		while ($r = @pg_fetch_object($qr))
		{
			if ($aMemo['paymast_id'] == $r->paymast_id)
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
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">cc:</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="cc"   type="text" id="cc" value="<?= $aMemo['cc'];?>" size="50" maxlength="50">
        </font> </td>
    </tr>
    <tr bgcolor="#EFEFEF"> 
      <td colspan="2"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Memo</font><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
        </font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="2"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <textarea name="memo" cols="100" rows="15" id="memo"><?= $aMemo['memo'];?></textarea>
        </font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td>&nbsp;</td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; </font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="2"><table width="1%" border="0" cellpadding="1" cellspacing="1" bgcolor="#CCCCCC">
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

 