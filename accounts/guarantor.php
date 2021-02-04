<script>
function vSubmit(ul)
{
	if (ul.name == 'Cancel')
	{
		if (confirm("Are you sure to CANCEL guarantor Record?"))
		{
			document.f1.action="?p=../guarantor&p1=CancelConfirm"
		}	
		else
		{
			document.f1.action="?p=../guarantor&p1=Cancel"
		}
	}
	else
	{
		document.f1.action="?p=guarantor&p1="+ul.id;
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
if (!chkRights2('guarantor','mview',$ADMIN['admin_id']))
{
	message('You have [ NO ] permission to access this area...');
	exit;
}

if (!session_is_registered('aguarantor'))
{
	session_register('aguarantor');
	$aguarantor = null;
	$aguarantor = array();
}
$fields = array('account_code', 'cardno', 'account','address','telefax',
			'pcdrygoods','pcgrocery','bond_withdraw','bond_arrears','bond_interest',			
			'telno','remarks','enable');


if (!in_array($p1,array(null,'showaudit','Load')))
{
	for ($c=0;$c<count($fields);$c++)
	{
		$aguarantor[$fields[$c]] = $_REQUEST[$fields[$c]];
		if ($aguarantor[$fields[$c]] == '' && !in_array($fields[$c], array('account_code','account','address','tin','remarks','telno','telefax','enable')))
		{
			$aguarantor[$fields[$c]] = 0;
		}
	}
}	
if ($id!='' && $p1 == 'Load')
{
	$aguarantor = null;
	$aguarantor = array();
	$q = "select * from account where account_id = '$id'";
	$r = fetch_assoc($q);
	$aguarantor = $r;
}		

if ($p1 == 'New')
{
	if (!chkRights2("guarantor","madd",$ADMIN['admin_id']))
	{
		message("You have no permission in this area...");
		exit;
	}	
	$aguarantor = null;
	$aguarantor = array();
	$aguarantor['account_type_id'] = '6';
}
elseif ($p1=='showaudit')
{
	$aguarantor['showaudit'] =1;
}
elseif ($p1 == 'Save' && $aguarantor['account']!='')
{
	if (!chkRights2("guarantor","medit",$ADMIN['admin_id']))
	{	
		message("You have no permission in this area...");
		exit;
	}
	if ($aguarantor['account_id'] == '')
	{
		$audit = 'Encoded by '.$ADMIN['name'].' on '.date('m/d/Y g:ia').'; ';
		$aguarantor['audit'] = $audit;
		$q = "insert into account (account_code, cardno,account, address, telefax, telno, account_type_id, enable, pcdrygoods,pcgrocery, bond_withdraw, bond_arrears, bond_interest)
				values ('".$aguarantor['account_code']."','".$aguarantor['cardno']."','".$aguarantor['account']."','".$aguarantor['address']."',
				'".$aguarantor['telefax']."','".$aguarantor['telno']."','3', '".$aguarantor['enable']."','$aguarantor[pcdrygoods]','$aguarantor[pcgrocery]','$aguarantor[bond_withdraw]','$aguarantor[bond_arrears]','$aguarantor[bond_interest]')";
	}
	else
	{
		$audit = $aguarantor['audit'].'Updated by '.$ADMIN['name'].' on '.date('m/d/Y g:ia').'; ';
		$aguarantor['audit'] = $audit;
		$q = "update account set audit='$audit'  ";
	
		for($c=0;$c<count($fields);$c++)
		{
			$q .= ", ".$fields[$c] ."='".$aguarantor[$fields[$c]]."'";
		}
		$q .= " where account_id='".$aguarantor['account_id']."'";
	}

	$qr = pg_query($q) or message("Error saving guarantor data...".pg_errormessage().$q);
	if ($qr)
	{
		if ($aguarantor['account_id'] == '')
		{
			$aguarantor['account_id'] = pg_insert_id('account');
		}
		message("Guarantor Data Saved...");
	}
}
?>
<form action="" method="post" name="f1" id="f1">
  <table width="90%" border="0" align="center" cellpadding="2" cellspacing="1">
    <tr> 
      <td>Search 
        <input name="search" type="text" id="search" value="<?= $search;?>">
        <input name="p1" type="submit" id="p1" value="Go">
        <input type="button" name="Submit2" value="Add New" onClick="window.location='?p=guarantor&p1=New'">
        <input type="button" name="Submit22" value="Browse" onClick="window.location='?p=guarantor.browse'"> 
        <input type="button" name="Submit23" value="Close" onClick="window.location='?p='">
        <hr color="#CC0000"></td>
    </tr>
  </table>
  <table width="90%" border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#EFEFEF">
    <tr bgcolor="#CCCCCC"> 
      <td colspan="6"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <img src="../graphics/post_discussion.gif" width="16" height="16"> Guarantor 
        Data Entry</font></strong></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td width="16%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Guarantor 
        Code</font></td>
      <td colspan="5"><input name="account_code" type="text" id="account_code" value="<?= $aguarantor['account_code'];?>" size="15" maxlength="15"></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Name 
        of Guarantor</font></td>
      <td><input name="account" type="text" id="account" value="<?= $aguarantor['account'];?>" size="40" maxlength="40"></td>
      <td colspan="4" bgcolor="#EFEFEF"><strong> <img src="../graphics/redlist.gif" width="16" height="17"> 
        <font size="2" face="Verdana, Arial, Helvetica, sans-serif">Commission</font></strong></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td rowspan="2" valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Address</font></td>
      <td width="35%" rowspan="2"><textarea name="address" cols="40" rows="2" id="address"><?= $aguarantor['address'];?></textarea></td>
      <td width="12%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">%DryGoods</font></td>
      <td width="18%"><input name="pcdrygoods" type="text" id="telefax23452" value="<?= $aguarantor['pcdrygoods'];?>" size="8" maxlength="8"></td>
      <td width="19%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Dry 
        Goods</font></td>
      <td width="37%"><input name="cdrygoods" type="text" id="cdrygoods" value="<?= $aguarantor['telefax'];?>" size="15" maxlength="15"></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">%Grocery</font></td>
      <td width="18%"><input name="pcgrocery" type="text" id="telefax23442" value="<?= $aguarantor['pcgrocery'];?>" size="8" maxlength="8"></td>
      <td width="19%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Grocery</font></td>
      <td width="37%"><input name="cgrocery" type="text" id="cgrocery" value="<?= $aguarantor['cgrocery'];?>" size="15" maxlength="15"></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Telephone/Fax</font></td>
      <td><input name="telefax" type="text" id="telefax" value="<?= $aguarantor['telefax'];?>" size="15" maxlength="15"></td>
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">MTD</font></td>
      <td><input name="cmtd" type="text" id="telefax23443" value="<?= $aguarantor['cmtd'];?>" size="8" maxlength="8"></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">YTD</font></td>
      <td><input name="cytd" type="text" id="cytd" value="<?= $aguarantor['cytd'];?>" size="15" maxlength="15"></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Telephone</font></td>
      <td><input name="telno" type="text" id="telno" value="<?= $aguarantor['telno'];?>" size="15" maxlength="15"></td>
      <td colspan="4" nowrap bgcolor="#EFEFEF"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong><img src="../graphics/lockfolder.gif" width="16" height="17"> 
        Bond</strong></font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">TIN</font></td>
      <td><input name="tin" type="text" id="tin" value="<?= $aguarantor['tin'];?>" size="15" maxlength="15"></td>
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">%Bond 
        to Withdraw</font></td>
      <td><input name="bond_withdraw" type="text" id="bond_withdraw" value="<?= $aguarantor['bond_withdraw'];?>" size="8" maxlength="8"></td>
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Total Deposited</font></td>
      <td><input name="bond_deposit" type="text" id="bond_deposit" value="<?= $aguarantor['bond_deposit'];?>" size="15" maxlength="15"></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Terms</font></td>
      <td><input name="term" type="text" id="term" value="<?= $aguarantor['term'];?>" size="15" maxlength="15"></td>
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">% 
        Arrears</font></td>
      <td><input name="bond_arrears" type="text" id="bond_arrears" value="<?= $aguarantor['bond_arrears'];?>" size="8" maxlength="8"></td>
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Total Used</font></td>
      <td><input name="bond_used" type="text" id="bond_used" value="<?= $aguarantor['bond_used'];?>" size="15" maxlength="15"></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td rowspan="2" valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Remarks</font></td>
      <td rowspan="2"><textarea name="remarks" cols="40" id="remarks"><?= $aguarantor['remarks'];?></textarea></td>
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Bond 
        Interest </font></td>
      <td><input name="bond_interest" type="text" id="bond_interest" value="<?= $aguarantor['bond_interest'];?>" size="8" maxlength="8"></td>
      <td nowrap>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td nowrap>&nbsp;</td>
      <td colspan="3">&nbsp;</td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Audit</font></td>
      <td colspan="5"> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?
	  if ($aguarantor['showaudit']==1)
	  {
	  	echo $aguarantor['audit'];
	  }
	  else
	  {
	  	echo "<a href='?p=guarantor&p1=showaudit'>Show</a>";
	  }
	  ?>
        </font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Enabled</font></td>
      <td colspan="5"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?=lookUpAssoc('enable',array('Yes'=>'Y','No'=>'N'),$aguarantor['enable']);?>
        </font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="6" valign="top" nowrap><table width="1%" border="0" cellpadding="1" cellspacing="1" bgcolor="#CCCCCC">
          <tr bgcolor="#FFFFFF"> 
            <td nowrap width="3%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              <input type="image" src="../graphics/save.jpg" alt="Save This Claim Form" width="57" height="15" id="Save" onClick="vSubmit(this)" name="Save">
              </strong></font></td>
            <td nowrap width="46%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              <input name="Print" type="image" id="Print" onClick="vSubmit(this)" src="../graphics/print.jpg" alt="Print This Claim Form" width="65" height="18">
              </strong></font></td>
            <!--<td nowrap width="26%"> <input name="Cancel" id="Cancel" type="image" src="../graphics/cancel.jpg" onClick="vSubmit(this)" alt="Cancel This Entry" width="77" height="20"></td>-->
            <td nowrap width="25%"> <input type='image' name="New" id="New" onClick="vSubmit(this)"  src="../graphics/new.jpg" alt="New Claim Form" width="63" height="20"> 
            </td>
          </tr>
        </table></td>
    </tr>
  </table>
</form>
