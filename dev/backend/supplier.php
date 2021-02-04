<script>
function vSubmit(ul)
{
	if (ul.name == 'Cancel')
	{
		if (confirm("Are you sure to CANCEL supplier Record?"))
		{
			document.f1.action="?p=../supplier&p1=CancelConfirm"
		}	
		else
		{
			document.f1.action="?p=../supplier&p1=Cancel"
		}
	}
	else
	{
		document.f1.action="?p=supplier&p1="+ul.id;
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
if (!chkRights2('supplier','mview',$ADMIN['admin_id']))
{
	message('You have [ NO ] permission to access this area...');
	exit;
}

if (!session_is_registered('asupplier'))
{
	session_register('asupplier');
	$asupplier = null;
	$asupplier = array();
}
$fields = array('supplier_code','supplier','address','telefax',
			'telno','remarks','enable');

if ($id!='' && $id != $asupplier['supplier_id'])
{
	$asupplier = null;
	$asupplier = array();
	$q = "select * from supplier where supplier_id = '$id'";
	$r = fetch_assoc($q);
	$asupplier = $r;
}		

if (!in_array($p1,array(null,'showaudit')))
{
	for ($c=0;$c<count($fields);$c++)
	{
		$asupplier[$fields[$c]] = $_REQUEST[$fields[$c]];
	}
}	

if ($p1 == 'New')
{
	if (!chkRights2("supplier","madd",$ADMIN['admin_id']))
	{
		message("You have no permission in this area...");
		exit;
	}	
	$asupplier = null;
	$asupplier = array();
}
elseif ($p1=='showaudit')
{
	$asupplier['showaudit'] =1;
}
elseif ($p1 == 'Save' && $asupplier['supplier']!='')
{
	if (!chkRights2("supplier","medit",$ADMIN['admin_id']))
	{	
		message("You have no permission in this area...");
		exit;
	}
	if ($asupplier['supplier_id'] == '')
	{
		$audit = 'Encoded by '.$ADMIN['name'].' on '.date('m/d/Y g:ia').'; ';
		$asupplier['audit'] = $audit;
		$q = "insert into supplier (supplier_code, supplier, address, telefax, telno,enable)
				values ('".$asupplier['supplier_code']."','".$asupplier['supplier']."','".$asupplier['address']."',
				'".$asupplier['telefax']."','".$asupplier['telno']."','".$asupplier['enable']."')";
	}
	else
	{
		$audit = $asupplier['audit'].'Updated by '.$ADMIN['name'].' on '.date('m/d/Y g:ia').'; ';
		$asupplier['audit'] = $audit;
		$q = "update supplier set audit='$audit'  ";
	
		for($c=0;$c<count($fields);$c++)
		{
			$q .= ", ".$fields[$c] ."='".$asupplier[$fields[$c]]."'";
		}
		$q .= " where supplier_id='".$asupplier['supplier_id']."'";
	}

	$qr = mysql_query($q) or message("Error saving supplier data...".mysql_error().$q);
	if ($qr)
	{
		if ($asupplier['supplier_id'] == '')
		{
			$asupplier['supplier_id'] = mysql_insert_id();
		}
		message("supplier Data Saved...");
	}
}
?>
<form name="form1" method="post" action="">
  <table width="85%" border="0" align="center" cellpadding="2" cellspacing="1">
    <tr> 
      <td>Search 
        <input name="search" type="text" id="search" value="<?= $search;?>">
        <input name="p1" type="submit" id="p1" value="Go">
        <input type="button" name="Submit2" value="Add New" onClick="window.location='?p=supplier&p1=New'">
        <input type="button" name="Submit22" value="Browse" onClick="window.location='?p=supplier.browse'"> 
        <input type="button" name="Submit23" value="Close" onClick="window.location='?p='">
        <hr color="#CC0000"></td>
    </tr>
  </table>
</form>
<form action="" method="post" name="f1" id="f1">
  <table width="80%" border="0" align="center" cellpadding="2" cellspacing="1" bgcolor="#EFEFEF">
    <tr bgcolor="#CCCCCC"> 
      <td colspan="2"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <img src="../graphics/post_discussion.gif" width="16" height="16"> Supplier 
        Data Entry</font></strong></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td width="19%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Supplier 
        Code</font></td>
      <td width="81%"><input name="supplier_code" type="text" id="supplier_code" value="<?= $asupplier['supplier_code'];?>" size="15" maxlength="15"></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td width="19%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Supplier 
        Code</font></td>
      <td width="81%"><input name="supplier_code" type="text" id="supplier_code" value="<?= $asupplier['supplier_code'];?>" size="15" maxlength="15"></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Name 
        of Supplier</font></td>
      <td><input name="supplier" type="text" id="supplier" value="<?= $asupplier['supplier'];?>" size="40" maxlength="40"></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Address</font></td>
      <td><textarea name="address" cols="40" rows="2" id="address"><?= $asupplier['address'];?></textarea></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Telephone/Fax</font></td>
      <td><input name="telefax" type="text" id="telefax" value="<?= $asupplier['telefax'];?>" size="15" maxlength="15"></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Telephone</font></td>
      <td><input name="telno" type="text" id="telno" value="<?= $asupplier['telno'];?>" size="15" maxlength="15"></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">TIN</font></td>
      <td><input name="tin" type="text" id="tin" value="<?= $asupplier['tin'];?>" size="15" maxlength="15"></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Terms</font></td>
      <td><input name="term" type="text" id="term" value="<?= $asupplier['term'];?>" size="15" maxlength="15"></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Remarks</font></td>
      <td><textarea name="remarks" cols="40" id="remarks"><?= $asupplier['remarks'];?></textarea></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Audit</font></td>
      <td> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?
	  if ($asupplier['showaudit']==1)
	  {
	  	echo $asupplier['audit'];
	  }
	  else
	  {
	  	echo "<a href='?p=supplier&p1=showaudit'>Show</a>";
	  }
	  ?>
        </font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Enabled</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?=lookUpAssoc('enable',array('Yes'=>'Y','No'=>'N'),$asupplier['enable']);?>
        </font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="2" valign="top" nowrap><table width="1%" border="0" cellpadding="1" cellspacing="1" bgcolor="#CCCCCC">
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
