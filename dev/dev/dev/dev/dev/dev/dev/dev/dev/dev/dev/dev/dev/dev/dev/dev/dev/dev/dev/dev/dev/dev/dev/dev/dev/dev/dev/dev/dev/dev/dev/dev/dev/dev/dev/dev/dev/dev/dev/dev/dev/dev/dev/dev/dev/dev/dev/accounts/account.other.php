<script>
function vSubmit(ul)
{
	if (ul.name == 'Cancel')
	{
		if (confirm("Are you sure to CANCEL account Record?"))
		{
			document.f1.action="?p=../account&p1=CancelConfirm"
		}	
		else
		{
			document.f1.action="?p=../account&p1=Cancel"
		}
	}
	else
	{
		document.f1.action="?p=account&p1="+ul.id;
	}	
}
function switchPix(o)
{
	var folder = "../graphics/";
	var n = o.name;
	var obj = new Array('patient_data','address','admission','account','diagnosis');

	for (c = 0;c<obj.length;c++)
	{
		eval("this.f1."+obj[c]).src=folder+obj[c]+"_lo.jpg";
		eval("this."+obj[c]+".style").visibility="hidden";
	}

	o.src=folder+o.name+"_hi.jpg"
	eval("this."+n+".style").visibility = "visible"
	
}
</script>
<?
if (!session_is_registered('aaccount'))
{
	session_register('aaccount');
	$aaccount = null;
	$aaccount = array();
}
$fields = array('account_code','account','address','telefax', 'account_type',
			'telno','remarks', 'credit_limit','date_expiry','lifetime','sdisc','cdisc','enable');

if ($id!='' && $id != $aaccount['account_id'])
{
	$aaccount = null;
	$aaccount = array();
	$q = "select * from account where account_id = '$id'";
	$r = fetch_assoc($q);
	$aaccount = $r;
}		

if (!in_array($p1,array(null,'showaudit')))
{
	for ($c=0;$c<count($fields);$c++)
	{
		if (substr($fields[$c],0,4) == 'date')
		{
			$aaccount[$fields[$c]] = mdy2ymd($_REQUEST[$fields[$c]]);
		}
		else
		{
			$aaccount[$fields[$c]] = $_REQUEST[$fields[$c]];
		}
	}
}	

if ($p1 == 'New')
{
	$aaccount = null;
	$aaccount = array();
}
elseif ($p1=='showaudit')
{
	$aaccount['showaudit'] =1;
}
elseif ($p1 == 'Save' && $aaccount['account']!='')
{
	if ($aaccount['account_id'] == '')
	{
		$audit = 'Encoded by '.$ADMIN['name'].' on '.date('m/d/Y g:ia').'; ';
		$aaccount['audit'] = $audit;
		$q = "insert into account (account_code,account, address, telefax, telno, remarks, credit_limit, account_type, date_expiry,lifetime,sdisc,cdisc)
				values ('".$aaccount['account_code']."','".$aaccount['account']."','".$aaccount['address']."',
				'".$aaccount['telefax']."','".$aaccount['telno']."','".$aaccount['remarks']."','".$aaccount['credit_limit']."','".$aaccount['account_type']."','".$aaccount['date_expiry']."',,'".$aaccount['lifetime']."',,'".$aaccount['sdisc']."','".$aaccount['cdisc']."')";
	}
	else
	{
		$audit = $aaccount['audit'].'Updated by '.$ADMIN['name'].' on '.date('m/d/Y g:ia').'; ';
		$aaccount['audit'] = $audit;
		$q = "update account set audit='$audit'  ";
	
		for($c=0;$c<count($fields);$c++)
		{
			$q .= ", ".$fields[$c] ."='".$aaccount[$fields[$c]]."'";
		}
		$q .= " where account_id='".$aaccount['account_id']."'";
	}

	$qr = mysql_query($q) or message("Error saving account data...".mysql_error().$q);
	if ($qr)
	{
		if ($aaccount['account_id'] == '')
		{
			$aaccount['account_id'] = mysql_insert_id();
		}
		message("account Data Saved...");
	}
}
?>
<form name="f1" method="post" action="">
  <table width="85%" border="0" align="center" cellpadding="2" cellspacing="1">
    <tr> 
      <td>Search 
        <input name="search" type="text" id="search" value="<?= $search;?>">
        <input name="button" type="button" id="p1" value="Go" onClick="window.location='?p=account&p1=Go'+'&search='+search.value" accesskey="G">
        <input type="button" name="Submit2" value="Add New" onClick="window.location='?p=account&p1=New'" accesskey="N">
        <input type="button" name="Submit22" value="Browse All" onClick="window.location='?p=account.browse&act=All'" accesskey="A">
        <input type="button" name="Submit222" value="Customers" onClick="window.location='?p=account.browse&act=C'" accesskey="C">
        <input type="button" name="Submit2222" value="Suppliers" onClick="window.location='?p=account.browse&act=S'" accesskey="S">
        <input type="button" name="Submit223" value="Other Payee" onClick="window.location='?p=account.browse&act=Y'" accesskey="P"> 
        <input type="button" name="Submit23" value="Close" onClick="window.location='?p='" accesskey="C">
        <hr color="#CC0000"></td>
    </tr>
  </table>
  <table width="80%" border="0" align="center" cellpadding="1" cellspacing="1" bgcolor="#EFEFEF">
    <tr bgcolor="#CCCCCC"> 
      <td colspan="2"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <img src="../graphics/post_discussion.gif" width="16" height="16"> Account 
        Data Entry</font></strong></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td width="19%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Account 
        No.</font></td>
      <td width="81%"><input name="account_code" type="text" id="account" value="<?= $aaccount['account_code'];?>" size="15" maxlength="15"> 
        <font size="2" face="Verdana, Arial, Helvetica, sans-serif">Account Type 
        <?= lookUpAssoc('account_type',array('Personal Account'=>'P','Company Account'=>'C','Institutional'=>'I','Government'=>'G', 'Supplier'=>'S','Consignee'=>'N','Employee'=>'E', 'Payee'=>'Y','Other'=>'O'), $aaccount['account_type']);?>
        </font> </td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Name 
        of account</font></td>
      <td><input name="account" type="text" id="account" value="<?= $aaccount['account'];?>" size="40" maxlength="40"></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Address</font></td>
      <td><textarea name="address" cols="40" rows="2" id="address"><?= $aaccount['address'];?></textarea></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Telephone/Fax</font></td>
      <td><input name="telefax" type="text" id="telefax" value="<?= $aaccount['telefax'];?>" size="15" maxlength="15"></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Telephone</font></td>
      <td><input name="telno" type="text" id="telno" value="<?= $aaccount['telno'];?>" size="15" maxlength="15"></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Remarks</font></td>
      <td><textarea name="remarks" cols="40" id="remarks"><?= $aaccount['remarks'];?></textarea></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Credit 
        Limit </font></td>
      <td><input name="credit_limit" type="text" id="credit_limit" value="<?= $aaccount['credit_limit'];?>" size="12" maxlength="12" style="text-align:right"></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Supplier 
        Discount </font></td>
      <td><input name="sdisc" type="text" id="sdisc" value="<?= $aaccount['sdisc'];?>" size="12" maxlength="8" style="text-align:right">
      </td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Audit</font></td>
      <td> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?
	  if ($aaccount['showaudit']==1)
	  {
	  	echo $aaccount['audit'];
	  }
	  else
	  {
	  	echo "<a href='?p=account&p1=showaudit'>Show</a>";
	  }
	  ?>
        </font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Enabled</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?=lookUpAssoc('enable',array('Yes'=>'Y','No'=>'N'),$aaccount['enable']);?>
        </font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="2" valign="top" nowrap><table width="1%" border="0" cellpadding="1" cellspacing="1" bgcolor="#CCCCCC">
          <tr bgcolor="#FFFFFF"> 
            <td nowrap width="3%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              <input type="image" src="../graphics/save.jpg" alt="Save This Claim Form" width="57" height="15" id="Save" onClick="vSubmit(this)" name="Save" accesskey="S">
              </strong></font></td>
            <td nowrap width="46%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              <input name="Print" type="image" id="Print" onClick="vSubmit(this)" src="../graphics/print.jpg" alt="Print This Claim Form" width="65" height="18" accesskey="P">
              </strong></font></td>
            <!--<td nowrap width="26%"> <input name="Cancel" id="Cancel" type="image" src="../graphics/cancel.jpg" onClick="vSubmit(this)" alt="Cancel This Entry" width="77" height="20"></td>-->
            <td nowrap width="25%"> <input type='image' name="New" id="New" onClick="vSubmit(this)"  src="../graphics/new.jpg" alt="New Claim Form" width="63" height="20" accesskey="N"> 
            </td>
          </tr>
        </table></td>
    </tr>
  </table>
</form>
