<script>
function vSubmit(ul)
{
	if (ul.name == 'Cancel')
	{
		if (confirm("Are you sure to CANCEL account Record?"))
		{
			document.f1.action="?p=account.supplier&p1=CancelConfirm"
		}	
		else
		{
			document.f1.action="?p=account.supplier&p1=Cancel"
		}
	}
	else
	{
		document.f1.action="?p=account.supplier&p1="+ul.id;
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
if (!session_is_registered('asupplier'))
{
	session_register('asupplier');
	$asupplier = null;
	$asupplier = array();
	$asupplier['account_type_id'] = '1';
}
$fields = array('account_code','account','address','telefax', 'account_type_id', 'tin','terms',
			'telno','remarks', 'enable');

if ($id!='' && $id != $asupplier['account_id'])
{
	$asupplier = null;
	$asupplier = array();
	$q = "select * from account where account_id = '$id'";
	$r = fetch_assoc($q);
	$asupplier = $r;
}		

if (!in_array($p1,array(null,'showaudit')))
{
	for ($c=0;$c<count($fields);$c++)
	{
		if (substr($fields[$c],0,4) == 'date')
		{
			$asupplier[$fields[$c]] = mdy2ymd($_REQUEST[$fields[$c]]);
		}
		else
		{
			$asupplier[$fields[$c]] = $_REQUEST[$fields[$c]];
		}
	}
}	

if ($p1 == 'New')
{
	$asupplier = null;
	$asupplier = array();
	$asupplier['account_type_id'] = '1';
}
elseif ($p1 == 'Load' && $id!='')
{
	$asupplier = null;
	$asupplier = array();
	$asupplier['account_type'] = 'S';
	$q = "select * from account where account_id = '$id'";
	$qr = @pg_query($q) or message(pg_errormessage());
	$r = @pg_fetch_assoc($qr);
	$asupplier = $r;
}
elseif ($p1=='showaudit')
{
	$asupplier['showaudit'] =1;
}
elseif ($p1 == 'Save' && $asupplier['account']!='')
{
	if ($asupplier['account_id'] == '')
	{
		$q = "select * from account where account_code = '".$asupplier['account_code']."'";
		$qr = @pg_query($q) or message(pg_errormessage());
		if (@pg_num_rows($qr) > 0)
		{
			message("Supplier Code Already Exists...");
			exit;
		}
		
		$audit = 'Encoded by '.$ADMIN['name'].' on '.date('m/d/Y g:ia').'; ';
		$asupplier['audit'] = $audit;
		$q = "insert into account (account_code,account, address, telefax, telno, remarks, account_type_id, tin, terms)
				values ('".$asupplier['account_code']."','".$asupplier['account']."','".$asupplier['address']."',
					'".$asupplier['telefax']."','".$asupplier['telno']."','".$asupplier['remarks']."',
					'".$asupplier['account_type_id']."', '".$asupplier['tin']."','".$asupplier['terms']."')";
	}
	else
	{
		$audit = $asupplier['audit'].'Updated by '.$ADMIN['name'].' on '.date('m/d/Y g:ia').'; ';
		$asupplier['audit'] = $audit;
		$q = "update account set audit='$audit'  ";
	
		for($c=0;$c<count($fields);$c++)
		{
			$q .= ", ".$fields[$c] ."='".$asupplier[$fields[$c]]."'";
		}
		$q .= " where account_id='".$asupplier['account_id']."'";
	}

	$qr = @pg_query($q) or message("Error saving account data...".pg_errormessage().$q);
	if ($qr)
	{
		if ($asupplier['account_id'] == '')
		{
			$asupplier['account_id'] = pg_insert_id('account');
		}
		message("account Data Saved...");
	}
	renewSupplier(); //renew aSUPPLIER array on session
}
?>
<form name="f1" method="post" action="">
  <table width="85%" border="0" align="center" cellpadding="2" cellspacing="1">
    <tr> 
      <td>Search 
        <input name="search" type="text" id="search" value="<?= $search;?>">
	<?= lookUpAssoc('searchby',array('Account Name'=>'account','Account Code'=>'account_code'),$aAcctBrowse['searchby']);?>
        <input name="button" type="button" id="p1" value="Go" onClick="window.location='?p=account.supplier.browse&p1=Go'+'&search='+f1.search.value+'&searchby='+f1.searchby.value" accesskey="G">
        <input type="button" name="Submit2" value="Add New" onClick="window.location='?p=account.supplier&p1=New'" accesskey="A">
        <input type="button" name="Submit22" value="Browse" onClick="window.location='?p=account.supplier.browse'" accesskey="B">
        <hr color="#CC0000"></td>
    </tr>
  </table>
  <table width="80%" border="0" align="center" cellpadding="1" cellspacing="1" bgcolor="#EFEFEF">
    <tr bgcolor="#CCCCCC"> 
      <td colspan="4"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <img src="../graphics/post_discussion.gif" width="16" height="16"> Supplier 
        Account Data Entry</font></strong></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td width="19%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Account 
        No.</font></td>
      <td width="40%"><input name="account_code" type="text" id="account" value="<?= $asupplier['account_code'];?>" size="15" maxlength="15"> 
        <font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; </font> </td>
      <td width="12%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Account 
        Type </font></td>
      <td width="29%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <select name="account_type_id">
          <option value=''>Account Type</option>
          <?
	  	$q = "select * from account_type where enable='Y' order by account_type";
		$qr = @pg_query($q);
		while ($r=@pg_fetch_object($qr))
		{
			if ($asupplier['account_type_id'] == $r->account_type_id)
			{
				echo "<option value=$r->account_type_id selected>$r->account_type</option>";	
			}
			else
			{
				echo "<option value=$r->account_type_id>$r->account_type</option>";	
			}
		}	
	  ?>
        </select>
        </font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Name 
        of account</font></td>
      <td><input name="account" type="text" id="account" value="<?= $asupplier['account'];?>" size="40" maxlength="40"></td>
      <td bgcolor="#FFFFFF">&nbsp;</td>
      <td bgcolor="#FFFFFF">&nbsp;</td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td rowspan="2" valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Address</font></td>
      <td rowspan="2"><textarea name="address" cols="40" rows="2" id="address"><?= $asupplier['address'];?></textarea></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Telephone/Fax</font></td>
      <td colspan="3"><input name="telefax" type="text" id="telefax" value="<?= $asupplier['telefax'];?>" size="15" maxlength="15"></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Telephone</font></td>
      <td colspan="3"><input name="telno" type="text" id="telno" value="<?= $asupplier['telno'];?>" size="15" maxlength="15"></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Terms</font></td>
      <td colspan="3"> 
        <input name="terms" type="text" id="terms" value="<?= $asupplier['terms'];?>" size="15" maxlength="15"></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">TIN</font></td>
      <td colspan="3"><input name="tin" type="text" id="tin" value="<?= $asupplier['tin'];?>" size="30" maxlength="30"></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Remarks</font></td>
      <td colspan="3"><textarea name="remarks" cols="40" id="remarks"><?= $asupplier['remarks'];?></textarea></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Audit</font></td>
      <td colspan="3"> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?
	  if ($asupplier['showaudit']==1)
	  {
	  	echo $asupplier['audit'];
	  }
	  else
	  {
	  	echo "<a href='?p=account.supplier&p1=showaudit'>Show</a>";
	  }
	  ?>
        </font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Enabled</font></td>
      <td colspan="3"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?=lookUpAssoc('enable',array('Yes'=>'Y','No'=>'N'),$asupplier['enable']);?>
        </font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="4" valign="top" nowrap><table width="1%" border="0" cellpadding="1" cellspacing="1" bgcolor="#CCCCCC">
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
