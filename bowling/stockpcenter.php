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
$fields = array('barcode','stock','stock_description','price1', 'cost1', 'taxable','inventory','unit1', 'account_id','category_id', 
						'date1_promo','date2_promo','promo_sdisc','promo_price1','promo_customer','enable',
						'price_level1','price_level2','price_level3','price_level4','price_level5','price_level6','price_level0');

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
			if ($astock[$fields[$c]]  == '' && $fields[$c] != 'stock_description') $astock[$fields[$c]]  = 0;
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

	$q = "select * from stock_price where stock_id = '$id'";
	$qr = @pg_query($q) or message(pg_errormessage());
	if (@pg_num_rows($qr) > 0)
	{
		$r = @pg_fetch_assoc($qr);
		$astock += $r;
	}
	

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
		$q = "insert into $stocktable (barcode,stock,  price1, unit1,taxable, inventory, cost1, 
					category_id, account_id, date1_promo, date2_promo, promo_price1, promo_customer,
					promo_sdisc, date_encoded, date_updated)
				values ('".$astock['barcode']."','".$astock['stock']."',
					'".$astock['price1']."','".$astock['unit1']."',
					'".$astock['taxable']."', '".$astock['inventory']."','".$astock['cost1']."',
					'".$astock['category_id']."','".$astock['account_id']."',
					'".$astock['date1_promo']."','".$astock['date2_promo']."',
					'".$astock['promo_price1']."','".$astock['promo_customer']."',
					'".$astock['promo_sdisc']."',
					'$date_updated', '$date_updated')";
	}
	else
	{
		$audit = $astock['audit'].'Updated by '.$ADMIN['name'].' on '.date('m/d/Y g:ia').'; ';
		$astock['audit'] = $audit;
		$q = "update $stocktable set audit='$audit' , date_updated = '$date_updated' ";
	
		for($c=0;$c<count($fields);$c++)
		{
			if (substr($fields[$c],5,6) == '_level') continue;
			
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
	else
	{
		$q = "select * from stock_price where stock_id = '".$astock['stock_id']."'";
		$qr = @pg_query($q) or message1(pg_errormessage());
		if (@pg_num_rows($qr) > 0)
		{
			$q = "update stock_price set
								price_level1 = '".$astock['price_level1']."',
								price_level2 = '".$astock['price_level2']."',
								price_level3 = '".$astock['price_level3']."',
								price_level4 = '".$astock['price_level4']."',
								price_level5 = '".$astock['price_level5']."',
								price_level6 = '".$astock['price_level6']."',
								price_level0 = '".$astock['price_level0']."'
							where
								stock_id = '".$astock['stock_id']."'";
								
				$qr = @pg_query($q) or message1(pg_errormessage().$q);

		}
		else
		{
			if ($astock['price_level1'] > 0 || $astock['price_level2'] > 0 || $astock['price_level3'] > 0 || $astock['price_level4'] > 0  || $astock['price_level5'] > 0 || $astock['price_level6'] > 0  || $astock['price_level0'] > 0 )
			{
				$q  = "insert into stock_price (stock_id, price_level1, price_level2, price_level3, price_level4, price_level5, price_level6, price_level0)
							values
									('".$astock['stock_id']."','".$astock['price_level1']."','".$astock['price_level2']."',
									'".$astock['price_level3']."','".$astock['price_level4']."','".$astock['price_level5']."',
									'".$astock['price_level6']."','".$astock['price_level0']."')";
				$qr = @pg_query($q) or message1(pg_errormessage().$q);

			}
		}
	}
}
?>
<br><br>
<form name="f1" id="f1" method="post" action="">
  <table width="90%" border="0" align="center" cellpadding="2" cellspacing="1" bgcolor="#DADADA">
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
  <table width="90%" align="center" cellpadding="0" cellspacing="1" bgcolor="#DADADA">
    <tr> 
      <td width="19%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Barcode</font></td>
      <td width="40%"><input name="barcode"  tabindex="1" type="text" id="barcode" value="<?= $astock['barcode'];?>" size="15" maxlength="15"  onKeypress="if(event.keyCode==13) {document.getElementById('stock').focus();return false;}"> 
        <font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; </font> 
      </td>
      <td colspan="4" bgcolor="#CCCCCC"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
        Price Schedule </font></td>
    </tr>
    <tr> 
      <td height="24" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Item 
        Name </font></td>
      <td><input name="stock" type="text" id="stock" value="<?= stripslashes($astock['stock']);?>" size="40" maxlength="40"  tabindex="2" max="40" onBlur="vDesc();"   onKeypress="if(event.keyCode==13) {document.getElementById('stock_description').focus();return false;}"></td>
      <td width="12%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Monday</font></td>
      <td width="29%" colspan="3"><input name="price_level1" type="text" id="price_level1"  tabindex="6"  value="<?= $astock['price_level1'];?>" size="15" maxlength="15"  onKeypress="if(event.keyCode==13) {document.getElementById('price_level2').focus();return false;}" style="text-align:right"></td>
    </tr>
    <tr> 
      <td rowspan="3" valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Item 
        Long Description</font></td>
      <td rowspan="3"><textarea name="stock_description"   tabindex="3"  cols="40" rows="2" id="stock_description"><?= $astock['stock_description'];?></textarea></td>
      <td height="23"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Tuesday</font></td>
      <td colspan="3"><input name="price_level2" type="text" id="price_level2"  tabindex="6"  value="<?= $astock['price_level2'];?>" size="15" maxlength="15"  onKeypress="if(event.keyCode==13) {document.getElementById('price_level3').focus();return false;}" style="text-align:right"></td>
    </tr>
    <tr > 
      <td ><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Wednesday</font></td>
      <td colspan="3"><input name="price_level3" type="text" id="price_level3"  tabindex="6"  value="<?= $astock['price_level3'];?>" size="15" maxlength="15"  onKeypress="if(event.keyCode==13) {document.getElementById('price_level4').focus();return false;}" style="text-align:right"></td>
    </tr>
    <tr > 
      <td ><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Thursday</font></td>
      <td colspan="3"><input name="price_level4" type="text" id="price_level4"  tabindex="6"  value="<?= $astock['price_level4'];?>" size="15" maxlength="15"  onKeypress="if(event.keyCode==13) {document.getElementById('price_level5').focus();return false;}" style="text-align:right"></td>
    </tr>
    <tr> 
      <td height="24" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Unit</font></td>
      <td> <input name="unit1" type="text"  tabindex="4"  id="unit1" value="<?= $astock['unit1'];?>" size="15" maxlength="15"  onKeypress="if(event.keyCode==13) {document.getElementById('price1').focus();return false;}"></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Friday</font></td>
      <td colspan="3"><input name="price_level5" type="text" id="price_level5"  tabindex="6"  value="<?= $astock['price_level5'];?>" size="15" maxlength="15"  onKeypress="if(event.keyCode==13) {document.getElementById('price_level6').focus();return false;}" style="text-align:right"></td>
    </tr>
    <tr> 
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Regular 
        Price</font></td>
      <td><input name="price1" type="text" id="price1"  tabindex="6"  value="<?= $astock['price1'];?>" size="15" maxlength="15"  onKeypress="if(event.keyCode==13) {document.getElementById('cost1').focus();return false;}" style="text-align:right"></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Saturday</font></td>
      <td colspan="3"><input name="price_level6" type="text" id="price_level6"  tabindex="6"  value="<?= $astock['price_level6'];?>" size="15" maxlength="15"  onKeypress="if(event.keyCode==13) {document.getElementById('price_level0').focus();return false;}" style="text-align:right"></td>
    </tr>
    <tr> 
      <td valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Cost</font></td>
      <td> <input name="cost1" type="text" id="cost1"   tabindex="8"  value="<?= $astock['cost1'];?>" size="15" maxlength="15"  style="text-align:right"></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Sunday</font></td>
      <td colspan="3"><input name="price_level0" type="text" id="price_level0"  tabindex="6"  value="<?= $astock['price_level0'];?>" size="15" maxlength="15"  onKeypress="if(event.keyCode==13) {document.getElementById('date1_promo').focus();return false;}" style="text-align:right"></td>
    </tr>
    <tr> 
      <td valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Taxable</font></td>
      <td> 
        <?= lookUpAssoc('taxable',array('Yes'=>'Y','No'=>'No'),$astock['taxable']);?>
      </td>
      <td colspan="4" bgcolor="#CCCCCC"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
        Promo Schedule (priority price)</font></td>
    </tr>
    <tr> 
      <td valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Inventory</font></td>
      <td> 
        <?= lookUpAssoc('inventory',array('Yes'=>'Y','No'=>'No'),$astock['inventory']);?>
      </td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> From</font></td>
      <td nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="date1_promo" type="text" id="date1_promo" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= ymd2mdy($astock['date1_promo']);?>" size="8"   onKeypress="if(event.keyCode==13) {document.getElementById('date2_promo').focus();return false;}">
        <img src="../graphics/dwn-arrow-grn.gif" width="12" height="12" onClick="popUpCalendar(this, f1.date1_promo, 'mm/dd/yyyy')"> 
        </font></td>
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">To</font></td>
      <td nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="date2_promo" type="text" id="date2_promo" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= ymd2mdy($astock['date2_promo'])?>" size="8" onKeypress="if(event.keyCode==13) {document.getElementById('promo_sdisc').focus();return false;}">
        <img src="../graphics/dwn-arrow-grn.gif" width="12" height="12" onClick="popUpCalendar(this, f1.date2_promo, 'mm/dd/yyyy')"> 
        </font></td>
    </tr>
    <tr> 
      <td valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Category</font></td>
      <td><select name="category_id"   tabindex="<?= array_search('category_id',$fields);?>" style="border: #CCCCCC 1px solid; width:240px">
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
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> %Discount</font></td>
      <td nowrap><input name="promo_sdisc" type="text" id="promo_sdisc" value="<?= $astock['promo_sdisc'];?>" size="8"  style="text-align:right"  onKeypress="if(event.keyCode==13) {document.getElementById('promo_cdisc').focus();return false;}"></td>
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">or New Price</font></td>
      <td nowrap><input name="promo_price1" type="text" id="promo_price1" value="<?= $astock['promo_price1'];?>" size="8"  style="text-align:right"  onKeypress="if(event.keyCode==13) {document.getElementById('Save').focus();return false;}"></td>
    </tr>
    <tr> 
      <td valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Supplier</font></td>
      <td><select name="account_id"   tabindex="<?= array_search('account_id',$fields);?>" style="border: #CCCCCC 1px solid; width:240px">
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
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Promo For</font></td>
      <td colspan="3" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <?= lookUpAssoc('promo_customer',array('All Customers'=>'A','Reward Members'=>'M'),$astock['promo_customer']);?>
        </font></td>
    </tr>
    <tr> 
      <td valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Audit</font></td>
      <td colspan="5"> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
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
    <tr> 
      <td valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Enabled</font></td>
      <td colspan="5"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?=lookUpAssoc('enable',array('Yes'=>'Y','No'=>'N'),$astock['enable']);?>
        </font></td>
    </tr>
    <tr> 
      <td colspan="6" valign="top" nowrap><table width="1%" border="0" cellpadding="1" cellspacing="1" bgcolor="#CCCCCC">
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