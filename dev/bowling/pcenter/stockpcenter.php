<script language="javascript">
function vDesc()
{
	var desc=document.getElementById('stock_description').value;
	if (desc == '')
	{
		document.getElementById('stock_description').value = document.getElementById('stock').value;
	}
}function vSubmit(ul)
{
	if (ul.name == 'Cancel')
	{
		if (confirm("Are you sure to CANCEL stock Record?"))
		{
			document.f1.action="?p=stockpcenter&p1=CancelConfirm"
		}	
		else
		{
			document.f1.action="?p=stockpcenter&p1=Cancel"
		}
	}
	else
	{
		document.f1.action="?p=stockpcenter&p1="+ul.id;
	}	
}
</script>
<?
if (!session_is_registered('astock'))
{
	session_register('astock');
	$astock = null;
	$astock = array();
	$astock['stock_type_id'] = '1';
}
$fields = array('barcode','stock','stock_description','price1', 'cost1', 'taxable','inventory','unit1', 'account_id','category_id', 'enable');

if ($id!='' && $id != $astock['stock_id'])
{
	$astock = null;
	$astock = array();
	$q = "select * from $stocktable where stock_id = '$id'";
	$r = fetch_assoc($q);
	$astock = $r;
}		

if (!in_array($p1,array(null,'showaudit')))
{
	for ($c=0;$c<count($fields);$c++)
	{
		if (substr($fields[$c],0,4) == 'date')
		{
			$astock[$fields[$c]] = mdy2ymd($_REQUEST[$fields[$c]]);
		}
		else
		{
			$astock[$fields[$c]] = $_REQUEST[$fields[$c]];
			if ($astock[$fields[$c]]  == '') $astock[$fields[$c]]  = 0;
		}
	}
}	

if ($p1 == 'New')
{
	$astock = null;
	$astock = array();
	$astock['stock_type_id'] = '1';
}
elseif ($p1 == 'Load' && $id!='')
{
	$astock = null;
	$astock = array();

	$q = "select * from $stocktable where stock_id = '$id'";
	$qr = @pg_query($q) or message(pg_errormessage());
	$r = @pg_fetch_assoc($qr);
	$astock = $r;
}
elseif ($p1=='showaudit')
{
	$astock['showaudit'] =1;
}
elseif ($p1 == 'Save' && $astock['stock']!='')
{
	begin();
	$date_updated = date('Y-m-d');
	if ($astock['stock_id'] == '')
	{
		
		$audit = 'Encoded by '.$ADMIN['name'].' on '.date('m/d/Y g:ia').'; ';
		$astock['audit'] = $audit;
		$q = "insert into $stocktable (barcode,stock,  price1, unit1,taxable, inventory, cost1, category_id, account_id, date_encoded, date_updated)
				values ('".$astock['barcode']."','".$astock['stock']."',
					'".$astock['price1']."','".$astock['unit1']."',
					'".$astock['taxable']."', '".$astock['inventory']."','".$astock['cost1']."',
					'".$astock['category_id']."','".$astock['account_id']."',
					'$date_updated', '$date_updated')";
	}
	else
	{
		$audit = $astock['audit'].'Updated by '.$ADMIN['name'].' on '.date('m/d/Y g:ia').'; ';
		$astock['audit'] = $audit;
		$q = "update $stocktable set audit='$audit' , date_updated = '$date_updated' ";
	
		for($c=0;$c<count($fields);$c++)
		{
			$q .= ", ".$fields[$c] ."='".$astock[$fields[$c]]."'";
		}
		$q .= " where stock_id='".$astock['stock_id']."'";
	}

	$qr = @pg_query($q) or message1("Error saving stock data...".pg_errormessage());
	if ($qr)
	{
		if ($astock['stock_id'] == '')
		{
			$seq = $stocktable.'_stock_id_seq';
      		$Q = "select currval('".$seq."'::text)";
			$QR = pg_query($Q) or die (pg_errormessage());
			$R = pg_fetch_object($QR);
			$id = $R->currval;
			$astock['stock_id'] = $id;
		}
		if ($astock['stock_id'] != '')
		{
			commit();
			message("Stock Data Saved...");
		}
	}
	if ($astock['stock_id'] == '')
	{
		rollback();
		message("Unable to save...Errors Encountered...");
	}
}
?>
<br><br>
<form name="f1" id="f1" method="post" action="">
  <table width="80%" border="0" align="center" cellpadding="2" cellspacing="1">
    <tr bgcolor="#CCCCCC"> 
      <td colspan="4" background="../graphics/table0_horizontal.PNG"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <img src="../graphics/post_discussion.gif" width="16" height="16"> Stock 
        Data Entry</font></strong></td>
    </tr>
    <tr> 
      <td>Search 
        <input name="search" type="text" id="search" value="<?= $search;?>">
	<?= lookUpAssoc('searchby',array('stock Name'=>'stock','stock Code'=>'barcode'),$aAcctBrowse['searchby']);?>
        <input name="button" type="button" id="p1" value="Go" onClick="window.location='?p=stockpcenter.browse&p1=Go'+'&search='+f1.search.value+'&searchby='+f1.searchby.value" accesskey="G">
        <input type="button" name="Submit2" value="Add New" onClick="window.location='?p=stockpcenter&p1=New'" accesskey="A">
        <input type="button" name="Submit22" value="Browse" onClick="window.location='?p=stockpcenter.browse'" accesskey="B">
        <hr color="#CC0000"></td>
    </tr>
  </table>
  <table width="80%" border="0" align="center" cellpadding="0" cellspacing="1">
    <tr bgcolor="#FFFFFF"> 
      <td width="19%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Barcode</font></td>
      <td width="40%"><input name="barcode"  tabindex="1" type="text" id="barcode" value="<?= $astock['barcode'];?>" size="15" maxlength="15"  onKeypress="if(event.keyCode==13) {document.getElementById('stock').focus();return false;}"> 
        <font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; </font> 
      </td>
      <td width="12%">&nbsp;</td>
      <td width="29%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
        </font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td height="24" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Item 
        Name </font></td>
      <td><input name="stock" type="text" id="stock" value="<?= stripslashes($astock['stock']);?>" size="40" maxlength="40"  tabindex="2" max="40" onBlur="vDesc();"   onKeypress="if(event.keyCode==13) {document.getElementById('stock_description').focus();return false;}"></td>
      <td bgcolor="#FFFFFF">&nbsp;</td>
      <td bgcolor="#FFFFFF">&nbsp;</td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td rowspan="2" valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Item 
        Long Description</font></td>
      <td rowspan="2"><textarea name="stock_description"   tabindex="3"  cols="40" rows="2" id="stock_description"><?= $astock['stock_description'];?></textarea></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td height="35">&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td height="24" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Unit</font></td>
      <td colspan="3"> <input name="unit1" type="text"  tabindex="4"  id="unit1" value="<?= $astock['unit1'];?>" size="15" maxlength="15"  onKeypress="if(event.keyCode==13) {document.getElementById('price1').focus();return false;}"></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Price</font></td>
      <td colspan="3"><input name="price1" type="text" id="price1"  tabindex="6"  value="<?= $astock['price1'];?>" size="15" maxlength="15"  onKeypress="if(event.keyCode==13) {document.getElementById('cost1').focus();return false;}" style="text-align:right"></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Cost</font></td>
      <td colspan="3"> <input name="cost1" type="text" id="cost1"   tabindex="8"  value="<?= $astock['cost1'];?>" size="15" maxlength="15"  style="text-align:right"></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Taxable</font></td>
      <td colspan="3"> 
        <?= lookUpAssoc('taxable',array('Yes'=>'Y','No'=>'No'),$astock['taxable']);?>
      </td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Inventory</font></td>
      <td colspan="3"> 
        <?= lookUpAssoc('inventory',array('Yes'=>'Y','No'=>'No'),$astock['inventory']);?>
      </td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Category</font></td>
      <td colspan="3"><select name="category_id"   tabindex="<?= array_search('category_id',$fields);?>" style="border: #CCCCCC 1px solid; width:240px">
          <option value=''>--Select Category--</option>
          <?
		foreach ($aCATEGORY as $ctemp)
		{
			if ($SYSCONF['SORT_CATEGORY'] == 'category')
			{
				$category_code = '';
			}
			else
			{
				$category_code = substr($ctemp['category_code'],0,6);
			}
			if ($ctemp['category_id'] == $astock['category_id'])
			{
				echo "<option value=".$ctemp['category_id']." selected>".$category_code." ".$ctemp['category']."</option>";
			}
			else
			{
				echo "<option value=".$ctemp['category_id']." >".$category_code." ".$ctemp['category']."</option>";
			}
		}
	  ?>
        </select></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Supplier</font></td>
      <td colspan="3"><select name="account_id"   tabindex="<?= array_search('account_id',$fields);?>" style="border: #CCCCCC 1px solid; width:240px">
          <option value=''>--Select Supplier Account--</option>
          <?
  		foreach ($aSUPPLIER as $stemp)
		{
			if ($stemp['account_id'] == $astock['account_id'])
			{
				echo "<option value=".$stemp['account_id']." selected>".substr($stemp['account_code'],0,6)." ".$stemp['account']."</option>";
			}
			else
			{
				echo "<option value=".$stemp['account_id'].">".substr($stemp['account_code'],0,6)." ".$stemp['account']."</option>";
			}
		}

	  ?>
        </select></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Audit</font></td>
      <td colspan="3"> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?
	  if ($astock['showaudit']==1)
	  {
	  	echo $astock['audit'];
	  }
	  else
	  {
	  	echo "<a href='?p=stockpcenter&p1=showaudit'>Show</a>";
	  }
	  ?>
        </font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Enabled</font></td>
      <td colspan="3"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?=lookUpAssoc('enable',array('Yes'=>'Y','No'=>'N'),$astock['enable']);?>
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
<?
if ($focus == '')
{
	echo "<script>document.getElementById('barcode').focus()</script>";
}
else
{
	echo "<script>document.getElementById('$focus').focus()</script>";
}
?>