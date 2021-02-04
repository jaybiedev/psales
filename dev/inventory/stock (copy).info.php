<script language="javascript">
function vDesc()
{
	var desc=document.getElementById('stock_description').value;
	if (desc == '')
	{
		document.getElementById('stock_description').value = document.getElementById('stock').value;
	}
}
function vCode(c)
{
	if (c == 'category_code')
	{
			if (document.getElementById(c).value!='')
			{
				f1.action='?p=stock&p1=selectCategory';
				f1.submit();
			}
			else
			{
				document.getElementById('category_id').value=0;
				return false;
			}
	}
	else if (c == 'classification_code')
	{
			if (document.getElementById(c).value!='')
			{
				f1.action='?p=stock&p1=selectClassification';
				f1.submit();
			}
			else
			{
				document.getElementById('classification_id').value=0;
				return false;
			}
	}
	if (c == 'account_code')
	{
			if (document.getElementById(c).value!='')
			{
				f1.action='?p=stock&p1=selectAccount';
				f1.submit();
			}
			else
			{
				document.getElementById('account_id').value=0;
				return false;
			}
	}
}

</script>

<!-- <div id="Layer.StockInfo" align="center" style="visibility:visible; position:absolute; width: 95%;">-->
<table width="100%" border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#EFEFEF">
  <tr> 
    <td colspan="4" bgcolor="#CCCCCC"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <img src="../graphics/bluelist.gif" width="15" height="16"> Stock Data Entry</font></strong></td>
    <td colspan="2" align="center" bgcolor="#CCCCCC"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Rec.Id : 
      <?= $astock['stock_id'];?>
      </font></td>
  </tr>
  <tr> 
    <td width="15%" nowrap bgcolor="#FFFFFF"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Item 
      Bar code</font></td>
    <td bgcolor="#FFFFFF" colspan="3"> <input name="barcode" type="text" id="barcode" value="<?= $astock['barcode'];?>" size="20" maxlength="20" tabindex="<?= array_search('barcode',$fields);?>1" nextfield="casecode" onKeypress="if(event.keyCode==13) {document.getElementById('casecode').focus();return false;}"  <? if ($astock['stock_id'] == '') { echo "onBlur=\"document.getElementById('Check').click()\"";};?>> 
      <input name="p1" type="submit" id="Check" value="Check"> <font size="2">Id: 
      <?= $astock['stock_id'];?>
      </font></td>
    <td colspan="2" rowspan="4" align="center" valign="middle" bgcolor="#FFFFFF"><img src="images/<?= $astock['pix'];?>" name="pix" width="150" height="100"> 
      <input type="hidden" name="MAX_FILE_SIZE" value="8192"> <br> <font size="2">Pix 
      150x100 </font> <input type="file" name="pixfile" onChange="vPix()" value="<?=$astock['pixfile'];?>"  tabindex="<?= count($fields)+10;?>"  class="hideTextFormat" size="1"></td>
  </tr>
  <tr> 
    <td height="24" nowrap bgcolor="#FFFFFF"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Case 
      Code</font></td>
    <td bgcolor="#FFFFFF" colspan="3"><input name="casecode" type="text" id="casecode" value="<?= $astock['casecode'];?>" size="20" maxlength="20" tabindex="<?= array_search('casecode',$fields);?>"  onKeypress="if(event.keyCode==13) {document.getElementById('stock').focus();return false;}"></td>
  </tr>
  <tr> 
    <td height="24" nowrap bgcolor="#FFFFFF"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Item 
      Name</font></td>
    <td bgcolor="#FFFFFF" colspan="3"> <input name="stock" type="text" id="stock" value="<?= stripslashes($astock['stock']);?>" size="50" maxlength="50"  tabindex="<?= array_search('stock',$fields);?>" max="40" onBlur="vDesc()"   onKeypress="if(event.keyCode==13) {document.getElementById('stock_description').focus();return false;}"> 
    </td>
  </tr>
  <tr> 
    <td valign="top" nowrap bgcolor="#FFFFFF"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Item 
      Description</font></td>
    <td bgcolor="#FFFFFF" colspan="3"> <textarea name="stock_description"  tabindex="<?= array_search('stock_description',$fields);?>" cols="38" rows="2" id="stock_description" ><?= stripslashes($astock['stock_description']);?></textarea> 
    </td>
  </tr>
  <tr> 
    <td height="22" width="15%" nowrap bgcolor="#FFFFFF"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      Measure </font></td>
    <td width="5%" align="center" bgcolor="#EFEFEF"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Smallest</font> 
    </td>
    <td width="5%" align="center" bgcolor="#EFEFEF"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Medium</font></td>
    <td width="15%" align="center" nowrap bgcolor="#EFEFEF"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Large</font></td>
    <td width="15%" bgcolor="#FFFFFF"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Inventory</font></td>
    <td width="40%" bgcolor="#FFFFFF"> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <select name="inventory"  id="inventory" tabindex="<?= array_search('inventory',$fields);?>"   onKeypress="if(event.keyCode==13) {document.getElementById('reorder_level').focus();return false;}">
        <option value="Y" <?=($astock['inventory']=='Y' ? 'selected' : '');?>>Yes</option>
        <option value="N" <?=($astock['inventory']=='N' ? 'selected' : '');?>>No</option>
      </select>
      </font> </td>
  </tr>
  <tr> 
    <td height="29" nowrap bgcolor="#FFFFFF"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Unit 
      of Measure</font></td>
    <td height="29" bgcolor="#FFFFFF"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <input name="unit1" type="text" id="unit1" value="<?= $astock['unit1'];?>" size="10" maxlength="10"  tabindex="<?= array_search('unit1',$fields);?>"   onKeypress="if(event.keyCode==13) {document.getElementById('unit2').focus();return false;}">
      </font> </td>
    <td height="29" bgcolor="#FFFFFF"> <input name="unit2" type="text"  tabindex="<?= array_search('unit2',$fields);?>" id="unit2" value="<?= $astock['unit2'];?>" size="10" maxlength="10"   onKeypress="if(event.keyCode==13) {document.getElementById('unit3').focus();return false;}" <?= ($SYSCONF['USE_MEDIUM_PRICE']=='N' ? 'disabled=1' :'');?>> 
    </td>
    <td height="29" bgcolor="#FFFFFF"> <input name="unit3" type="text"  tabindex="<?= array_search('unit3',$fields);?>" id="unit3" value="<?= $astock['unit3'];?>" size="10" maxlength="10"   onKeypress="if(event.keyCode==13) {document.getElementById('fraction2').focus();return false;}"> 
    </td>
    <td height="29" nowrap bgcolor="#FFFFFF"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Reorder 
      Level</font></td>
    <td height="29" bgcolor="#FFFFFF"><input name="reorder_level" type="text"  tabindex="<?= array_search('reorder_level',$fields);?>" id="reorder_level" value="<?= $astock['reorder_level'];?>" size="10" maxlength="10"   onKeypress="if(event.keyCode==13) {document.getElementById('reorder_qty').focus();return false;}"> 
    </td>
  </tr>
  <tr> 
    <td height="22" nowrap bgcolor="#FFFFFF"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Multiplier 
      (x Unit1)</font></td>
    <td bgcolor="#FFFFFF">&nbsp; </td>
    <td bgcolor="#FFFFFF"><input name="fraction2" type="text" id="fraction2" value="<?= $astock['fraction2'];?>" size="10" maxlength="10"  tabindex="<?= array_search('fraction2',$fields);?>" onBlur="vFraction(this)"   onKeypress="if(event.keyCode==13) {document.getElementById('fraction3').focus();return false;}"  <?= ($SYSCONF['USE_MEDIUM_PRICE']=='N' ? 'disabled=1' :'');?>> 
    </td>
    <td bgcolor="#FFFFFF"><input name="fraction3" type="text" id="fraction3" value="<?= $astock['fraction3'];?>" size="10" maxlength="10"  tabindex="<?= array_search('fraction2',$fields);?>" onBlur="vFraction(this)"   onKeypress="if(event.keyCode==13) {document.getElementById('cost1').focus();return false;}"> 
    </td>
    <td nowrap bgcolor="#FFFFFF"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Reorder 
      Qty</font></td>
    <td bgcolor="#FFFFFF"><input name="reorder_qty" type="text"  tabindex="<?= array_search('reorder_qty',$fields);?>" id="reorder_qty" value="<?= $astock['reorder_qty'];?>" size="10" maxlength="10"   onKeypress="if(event.keyCode==13) {document.getElementById('category_id').focus();return false;}"> 
    </td>
  </tr>
  <tr> 
    <td nowrap bgcolor="#FFFFFF"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      Cost </font></td>
    <td bgcolor="#FFFFFF"> <input name="cost1" type="text" id="cost1" value="<?= $astock['cost1'];?>" size="10" maxlength="10" style="text-align:right"  onBlur="vFraction(this)"  tabindex="<?= array_search('cost1',$fields);?>"   onKeypress="if(event.keyCode==13) {document.getElementById('cost2').focus();return false;}"> 
    </td>
    <td bgcolor="#FFFFFF"> <input name="cost2" type="text" id="cost2" value="<?= $astock['cost2'];?>" size="10" maxlength="10" style="text-align:right"  onBlur="vFraction(this)"  tabindex="<?= array_search('cost2',$fields);?>"   onKeypress="if(event.keyCode==13) {document.getElementById('cost3').focus();return false;}"  <?= ($SYSCONF['USE_MEDIUM_PRICE']=='N' ? 'disabled=1' :'');?>> 
    </td>
    <td bgcolor="#FFFFFF"> <input name="cost3" type="text" id="cost3" value="<?= $astock['cost3'];?>" size="10" maxlength="10" style="text-align:right"  onBlur="vFraction(this)"  tabindex="<?= array_search('cost2',$fields);?>"   onKeypress="if(event.keyCode==13) {document.getElementById('price1').focus();return false;}"> 
    </td>
    <td nowrap bgcolor="#FFFFFF">&nbsp;</td>
    <td bgcolor="#FFFFFF"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
      </font></td>
  </tr>
  <tr> 
    <td nowrap bgcolor="#FFFFFF"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Price 
      </font></td>
    <td bgcolor="#FFFFFF"> <input name="price1" type="text" id="price1" value="<?= $astock['price1'];?>" size="10" maxlength="10" style="text-align:right"  onBlur="vFraction(this)"  tabindex="<?= array_search('price1',$fields);?>"   onKeypress="if(event.keyCode==13) {document.getElementById('price2').focus();return false;}"> 
    </td>
    <td bgcolor="#FFFFFF"> <input name="price2" type="text" id="price2" value="<?= $astock['price2'];?>" size="10" maxlength="10" style="text-align:right" tabindex="<?= array_search('price2',$fields);?>"   onKeypress="if(event.keyCode==13) {document.getElementById('price3').focus();return false;}"  <?= ($SYSCONF['USE_MEDIUM_PRICE']=='N' ? 'disabled=1' :'');?>></td>
    <td bgcolor="#FFFFFF"> <input name="price3" type="text" id="price3" value="<?= $astock['price3'];?>" size="10" maxlength="10" style="text-align:right"  onBlur="vFraction(this)"  tabindex="<?= array_search('price2',$fields);?>"   onKeypress="if(event.keyCode==13) {document.getElementById('taxable').focus();return false;}"> 
      <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <input type='hidden' name='percent_share'>
      </font></td>
    <td nowrap bgcolor="#FFFFFF"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Category 
      </font></td>
    <td bgcolor="#FFFFFF"> <input name="category_code" id="category_code" type="hidden"   tabindex="<?= array_search('category_id',$fields);?>" value="<?= $astock['category_code'];?>" size="5" maxlength="7" onChange="vCode('category_code')"   onKeypress="if(event.keyCode==13) {document.getElementById('classification_code').focus();return false;}"> 
      <select name="category_id"   tabindex="<?= array_search('category_id',$fields);?>" style="border: #CCCCCC 1px solid; width:220px">
        <option value=''>--Select Category--</option>
        <?
	  	$q = "select * from category where enable='Y' order by category_code,category";
		$qr = @pg_query($q);
		while ($r = @pg_fetch_object($qr))
		{
			if ($r->category_id == $astock['category_id'])
			{
				echo "<option value=$r->category_id selected>".substr($r->category_code,0,6)." $r->category</option>";
			}
			else
			{
				echo "<option value=$r->category_id>".substr($r->category_code,0,6)." $r->category</option>";
			}
		}
	  ?>
      </select> </td>
  </tr>
  <tr> 
    <td valign="top" nowrap bgcolor="#FFFFFF"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">MarkUp</font></td>
    <td  bgcolor="#FFFFFF"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <input name="markup" type="text" id="markup" value="<?= $astock['markup'];?>" size="10" maxlength="10" style="text-align:right" tabindex="<?= array_search('markup',$fields);?>"   onKeypress="if(event.keyCode==13) {document.getElementById('max_discount').focus();return false;}">
      </font> </td>
    <td nowrap  bgcolor="#FFFFFF"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      %Max Disc </font> </td>
    <td  bgcolor="#FFFFFF"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <input name="max_discount" type="text" id="max_discount2" value="<?= $astock['max_discount'];?>" size="10"  style="text-align:right" maxlength="10" tabindex="<?= array_search('max_discount',$fields);?>"   onKeypress="if(event.keyCode==13) {document.getElementById('unit1').focus();return false;}">
      </font> </td>
    <td bgcolor="#FFFFFF"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Classification</font></td>
    <td bgcolor="#FFFFFF"> <input name="classification_code" type="hidden" id="classification_code"   tabindex="<?= array_search('category_id',$fields);?>" value="<?= $astock['classification_code'];?>" size="5" maxlength="5" onChange="vCode('classification_code')"   onKeypress="if(event.keyCode==13) {document.getElementById('account_code').focus();return false;}"> 
      <select name="classification"   tabindex="<?= array_search('classification_id',$fields);?>" style="border: #CCCCCC 1px solid; width:220px">
        <option value=''>--Select Classification--</option>
        <?
	  	$q = "select * from classification where enable='Y' order by classification";
		$qr = @pg_query($q);
		while ($r = @pg_fetch_object($qr))
		{
			if ($r->classification_id == $astock['classification_id'])
			{
				echo "<option value=$r->classification_id selected>".substr($r->classification_code,0,6)." $r->classification</option>";
			}
			else
			{
				echo "<option value=$r->classification_id>".substr($r->classification_code,0,6)." $r->classification</option>";
			}
		}
	  ?>
      </select> </td>
  </tr>
  <tr> 
    <td valign="top" nowrap bgcolor="#FFFFFF"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Taxable</font></td>
    <td colspan="3" bgcolor="#FFFFFF"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <select name="taxable"  id="taxable" tabindex="<?= array_search('taxable',$fields);?>"   onKeypress="if(event.keyCode==13) {document.getElementById('netitem').focus();return false;}">
        <option value="Y" <?=($astock['taxable']=='Y' ? 'selected' : '');?>>Yes</option>
        <option value="N" <?=($astock['taxable']=='N' ? 'selected' : '');?>>No</option>
      </select>
      </font> </td>
    <td bgcolor="#FFFFFF"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Supplier</font></td>
    <td bgcolor="#FFFFFF"> <input name="account_code" type="hidden" id="account_code"   tabindex="<?= array_search('category_id',$fields);?>" value="<?= $astock['account_code'];?>" size="5" maxlength="5" onChange="vCode('account_code')"   onKeypress="if(event.keyCode==13) {document.getElementById('date1_promo').focus();return false;}"> 
      <select name="account_id"   tabindex="<?= array_search('account_id',$fields);?>" style="border: #CCCCCC 1px solid; width:220px">
        <option value=''>--Select Supplier Account--</option>
        <?

		if (file_exists('nosupplier__xx'))
		{
			if ($astock['account_code'] != '')
			{
				$q = "select * from account where account_code = '".$astock['account_code']."' or account ilike '".$astock['account_code']."%'";
			}
		}
		else
		{
	  		$q = "select * from account  where account.account_type_id='1' and 
						account.enable='Y' order by  account_code,account";
		}
		$qr = @pg_query($q);
		while ($r = @pg_fetch_object($qr))
		{
			if ($r->account_id == $astock['account_id'])
			{
				echo "<option value=$r->account_id selected>".substr($r->account_code,0,6)." $r->account</option>";
			}
			else
			{
				echo "<option value=$r->account_id>".substr($r->account_code,0,6)." $r->account</option>";
			}
		}

	  ?>
      </select> </td>
  </tr>
  <tr> 
    <td valign="top" nowrap bgcolor="#FFFFFF"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Net 
      Item </font></td>
    <td colspan="3" bgcolor="#FFFFFF"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <select name="netitem" id="netitem"  tabindex="<?= array_search('taxable',$fields);?>"   onKeypress="if(event.keyCode==13) {document.getElementById('inventory').focus();return false;}">
        <option value="N" <?=($astock['netitem']=='N' ? 'selected' : '');?>>No</option>
        <option value="Y" <?=($astock['netitem']=='Y' ? 'selected' : '');?>>Yes</option>
      </select>
      </font></td>
    <td colspan="2" rowspan="5" valign="top" bgcolor="#FFFFFF"><table width="100%" border="0" cellpadding="1" cellspacing="0" bgcolor="#EFEFEF">
        <tr bgcolor="#EFEFEF"> 
          <td colspan="4"> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>&nbsp; 
            Scheduled Promo </strong></font> </td>
        </tr>
        <tr bgcolor="#FFFFFF"> 
          <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">From</font></td>
          <td nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
            <input name="date1_promo" type="text" id="date1_promo" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= ymd2mdy($astock['date1_promo']);?>" size="8"   onKeypress="if(event.keyCode==13) {document.getElementById('date2_promo').focus();return false;}">
            <img src="../graphics/dwn-arrow-grn.gif" width="12" height="12" onClick="popUpCalendar(this, f1.date1_promo, 'mm/dd/yyyy')"></font></td>
          <td align="center" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">To</font></td>
          <td nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
            <input name="date2_promo" type="text" id="date2_promo" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= ymd2mdy($astock['date2_promo'])?>" size="8" onKeypress="if(event.keyCode==13) {document.getElementById('promo_sdisc').focus();return false;}">
            <img src="../graphics/dwn-arrow-grn.gif" width="12" height="12" onClick="popUpCalendar(this, f1.date2_promo, 'mm/dd/yyyy')"> 
            </font></td>
        </tr>
        <tr bgcolor="#FFFFFF"> 
          <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Supplier(%)</font></td>
          <td nowrap><input name="promo_sdisc" type="text" id="promo_sdisc" value="<?= $astock['promo_sdisc'];?>" size="8"  style="text-align:right"  onKeypress="if(event.keyCode==13) {document.getElementById('promo_cdisc').focus();return false;}"></td>
          <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Company(%)</font></td>
          <td nowrap><input name="promo_cdisc" type="text" id="promo_cdisc" value="<?= $astock['promo_cdisc'];?>" size="8" style="text-align:right" onKeypress="if(event.keyCode==13) {document.getElementById('promo_price').focus();return false;}"></td>
        </tr>
        <tr bgcolor="#FFFFFF"> 
          <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Promo 
            Price </font></td>
          <td nowrap><input name="promo_price1" type="text" id="promo_price1" value="<?= $astock['promo_price1'];?>" size="8"  style="text-align:right"  onKeypress="if(event.keyCode==13) {document.getElementById('Save').focus();return false;}"></td>
          <td align="center" nowrap>&nbsp;</td>
          <td align="center" nowrap>&nbsp;</td>
        </tr>
      </table></td>
  </tr>
  <tr> 
    <td valign="top" nowrap bgcolor="#FFFFFF"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Stock 
      Balance</font></td>
    <td colspan="3" bgcolor="#FFFFFF"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= $balance_qty.' '.$astock['unit1'];?>
      </font> </td>
  </tr>
  <tr> 
    <td valign="top" nowrap bgcolor="#FFFFFF">&nbsp;</td>
    <td colspan="3" bgcolor="#FFFFFF"> <font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
      </font></td>
  </tr>
  <tr> 
    <td valign="top" nowrap bgcolor="#FFFFFF"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Enabled</font></td>
    <td colspan="3" bgcolor="#FFFFFF"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <select name="enable"  tabindex="<?= array_search('enable',$fields);?>">
        <option value="Y" <?=($astock['enable']=='Y' ? 'selected' : '');?>>Yes</option>
        <option value="N" <?=($astock['enable']=='N' ? 'selected' : '');?>>No</option>
      </select>
      </font></td>
  </tr>
  <tr> 
    <td colspan="4" valign="top" nowrap bgcolor="#FFFFFF"> <table width="1%" border="0" cellpadding="1" cellspacing="1" bgcolor="#CCCCCC">
        <tr bgcolor="#FFFFFF"> 
          <td nowrap width="3%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
            <a  accesskey="S" href="javascript: f1.action='?p=stock&p1=Save';f1.submit();"> 
            <input type="image" src="../graphics/save.jpg" alt="Save This Claim Form" name="Save" width="57" height="15" border="0" id="Save" onClick="f1.action='?p=stock&p1=Save';f1.submit();" tabIndex="99">
            </a> </strong></font></td>
          <td nowrap width="46%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
            <img src="../graphics/print.jpg" alt="Print This Claim Form"  onClick="window.print()" accesskey="P"> 
            </strong></font></td>
          <!--<td nowrap width="26%"> <input name="Cancel" id="Cancel" type="image" src="../graphics/cancel.jpg" onClick="vSubmit(this)" alt="Cancel This Entry" width="77" height="20"></td>-->
          <td nowrap width="25%"> <input type='image' name="New" id="New" onClick="f1.action='?p=stock&p1=New';f1.submit();"  src="../graphics/new.jpg" alt="New Claim Form" width="63" height="20" accesskey="N"> 
          </td>
        </tr>
      </table></td>
  </tr>
</table>
  <!-- </div>-->
