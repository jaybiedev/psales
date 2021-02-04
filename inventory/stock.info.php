<?
require_once(dirname(__FILE__).'/../lib/lib.salvio.php');
require_once(dirname(__FILE__).'/../lib/lib.inventory.php');
?>
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
<table width="100%" border="0" align="center" cellpadding="0" cellspacing="1">
  <tr> 
    <td height="24" nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Case 
      Code</font></td>
    <td colspan="3"> <input name="casecode" type="text" id="casecode" value="<?= $astock['casecode'];?>" size="20" maxlength="20" tabindex="<?= array_search('casecode',$fields);?>"  onKeypress="if(event.keyCode==13) {document.getElementById('stock_description').focus();return false;}"></td>
    <td valign="middle"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Inventory</font></td>
    <td valign="middle"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <select name="inventory"  id="inventory" tabindex="<?= array_search('inventory',$fields);?>"   onKeypress="if(event.keyCode==13) {document.getElementById('reorder_level').focus();return false;}">
        <option value="Y" <?=($astock['inventory']=='Y' ? 'selected' : '');?>>Yes</option>
        <option value="N" <?=($astock['inventory']=='N' ? 'selected' : '');?>>No</option>
      </select>
      <!-- Stk Bal:   -->
      <?
      
    	if ($astock['fraction3']*1 == '0'){
        $fraction3 = 1;
      } else {
        $fraction3 = $astock['fraction3'];
      }
      
      if( $astock['stock_id'] ) {
        $balance_qty = Inventory::getCurrentBalance($astock['stock_id'],date("Y-m-d"));  
      } else {
        $balance_qty = 0;
      }
      

      $fraction3 = ( $fraction3 <= 0 ) ? 1 : $fraction3;

    	$balance_case = intval($balance_qty / $fraction3);
    	$balance_unit = $balance_qty - ($balance_case * $fraction3);
    	echo $balance_case.' '.$astock['unit3'].' '.$balance_unit.' '.$astock['unit1']; 
      
    //	  = $astock['balance_qty'].' '.$astock['unit1'];
	  ?>
      </font></td>
  </tr>
  <tr> 
    <td rowspan="2" valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Item 
      Description</font></td>
    <td colspan="3" rowspan="2"> <textarea name="stock_description"  tabindex="<?= array_search('stock_description',$fields);?>" cols="40" rows="2" id="stock_description" ><?= stripslashes($astock['stock_description']);?></textarea> 
    </td>
    <td valign="middle"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Reorder 
      Level</font></td>
    <td valign="middle"><input name="reorder_level" type="text"  tabindex="<?= array_search('reorder_level',$fields);?>" id="reorder_level" value="<?= $astock['reorder_level'];?>" size="10" maxlength="10"   onKeypress="if(event.keyCode==13) {document.getElementById('reorder_qty').focus();return false;}"> 
      <font size="1">or initial stocking</font></td>
  </tr>
  <tr> 
    <td valign="middle"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Reorder 
      Qty</font></td>
    <td valign="middle"><input name="reorder_qty" type="text"  tabindex="<?= array_search('reorder_qty',$fields);?>" id="reorder_qty" value="<?= $astock['reorder_qty'];?>" size="10" maxlength="10"   onKeypress="if(event.keyCode==13) {document.getElementById('category_id').focus();return false;}"></td>
  </tr>
  <tr> 
    <td  width="15%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      Measure </font></td>
    <td width="5%" align="center" bgcolor="#DADADA"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Smallest</font> 
    </td>
    <td width="5%" align="center" bgcolor="#DADADA"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Medium</font></td>
    <td width="15%" align="center" nowrap bgcolor="#DADADA"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Large</font></td>
    <td width="15%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Category 
      </font></td>
    <td width="40%"> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
       <select name="category_id" id="category_id"   tabindex="<?= array_search('category_id',$fields);?>" style="border: #CCCCCC 1px solid; width:240px"  onKeypress="if(event.keyCode==13) {document.getElementById('classification_id').focus();return false;}">
        <option value=''>Select Category--</option>
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
      </select>
      </font> </td>
  </tr>
  <tr> 
    <td  nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Unit 
      of Measure</font></td>
    <td ><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <input name="unit1" type="text" id="unit1" value="<?= $astock['unit1'];?>" size="10" maxlength="10"  tabindex="<?= array_search('unit1',$fields);?>"   onKeypress="if(event.keyCode==13) {document.getElementById('unit3').focus();return false;}">
      </font> </td>
    <td > <input name="unit2" type="text"  tabindex="<?= array_search('unit2',$fields);?>" id="unit2" value="<?= $astock['unit2'];?>" size="10" maxlength="10"   onKeypress="if(event.keyCode==13) {document.getElementById('unit3').focus();return false;}" <?= ($SYSCONF['USE_MEDIUM_PRICE']=='N' ? 'disabled=1' :'');?>> 
    </td>
    <td > <input name="unit3" type="text"  tabindex="<?= array_search('unit3',$fields);?>" id="unit3" value="<?= $astock['unit3'];?>" size="10" maxlength="10"   onKeypress="if(event.keyCode==13) {document.getElementById('fraction3').focus();return false;}"> 
    </td>
    <td  nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Classification</font></td>
    <td > <select name="classification_id"  id="classification_id" tabindex="<?= array_search('classification_id',$fields);?>" style="border: #CCCCCC 1px solid; width:240px" onKeypress="if(event.keyCode==13) {document.getElementById('account_id').focus();return false;}">
        <option value=''>Select Classification--</option>
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
    <td  nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Multiplier 
      (x Unit1)</font></td>
    <td>&nbsp; </td>
    <td><input name="fraction2" type="text" id="fraction2" value="<?= $astock['fraction2'];?>" size="10" maxlength="10"  tabindex="<?= array_search('fraction2',$fields);?>" onBlur="vFraction(this)"   onKeypress="if(event.keyCode==13) {document.getElementById('fraction3').focus();return false;}"  <?= ($SYSCONF['USE_MEDIUM_PRICE']=='N' ? 'disabled=1' :'');?>> 
    </td>
    <td><input name="fraction3" type="text" id="fraction3" value="<?= $astock['fraction3'];?>" size="10" maxlength="10"  tabindex="<?= array_search('fraction2',$fields);?>" onBlur="vFraction(this)"   onKeypress="if(event.keyCode==13) {document.getElementById('cost1').focus();return false;}"> 
    </td>
    <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Supplier</font></td>
    <td>
      <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <select name="account_id" id="account_id"   tabindex="<?= array_search('account_id',$fields);?>" style="border: #CCCCCC 1px solid; width:240px">
        <option value=''>Select Supplier Account--</option>
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
      </select>
      </font>  </td>
  </tr>
  <tr> 
    <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> Cost 
      </font></td>
    <td> <input name="cost1" type="text" id="cost1" value="<?= $astock['cost1'];?>" size="10" maxlength="10" style="text-align:right"  onBlur="vFraction(this)"  tabindex="<?= array_search('cost1',$fields);?>"   onKeypress="if(event.keyCode==13) {document.getElementById('cost3').focus();return false;}"> 
    </td>
    <td> <input name="cost2" type="text" id="cost2" value="<?= $astock['cost2'];?>" size="10" maxlength="10" style="text-align:right"  onBlur="vFraction(this)"  tabindex="<?= array_search('cost2',$fields);?>"   onKeypress="if(event.keyCode==13) {document.getElementById('cost3').focus();return false;}"  <?= ($SYSCONF['USE_MEDIUM_PRICE']=='N' ? 'disabled=1' :'');?>> 
    </td>
    <td> <input name="cost3" type="text" id="cost3" value="<?= $astock['cost3'];?>" size="10" maxlength="10" style="text-align:right"  onBlur="vFraction(this)"  tabindex="<?= array_search('cost2',$fields);?>"   onKeypress="if(event.keyCode==13) {document.getElementById('price1').focus();return false;}"> 
    </td>
    <td nowrap>&nbsp;</td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; </font></td>
  </tr>
  <tr> 
    <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Price 
      </font></td>
    <td> <input name="price1" type="text" id="price1" value="<?= $astock['price1'];?>" size="10" maxlength="10" style="text-align:right"  onBlur="vFraction(this)"  tabindex="<?= array_search('price1',$fields);?>"   onKeypress="if(event.keyCode==13) {document.getElementById('price3').focus();return false;}"> 
    </td>
    <td> <input name="price2" type="text" id="price2" value="<?= $astock['price2'];?>" size="10" maxlength="10" style="text-align:right" tabindex="<?= array_search('price2',$fields);?>"   onKeypress="if(event.keyCode==13) {document.getElementById('price3').focus();return false;}"  <?= ($SYSCONF['USE_MEDIUM_PRICE']=='N' ? 'disabled=1' :'');?>></td>
    <td> <input name="price3" type="text" id="price3" value="<?= $astock['price3'];?>" size="10" maxlength="10" style="text-align:right"  onBlur="vFraction(this)"  tabindex="<?= array_search('price2',$fields);?>"   onKeypress="if(event.keyCode==13) {document.getElementById('markup').focus();return false;}"> 
      <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <input name='AUTOMARKUP' type='hidden' id="AUTOMARKUP" value="<?= $SYSCONF['AUTOMARKUP'];?>">
      </font></td>
    <td colspan="2" nowrap bgcolor="#DADADA"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Scheduled 
      Promo </strong></font> </td>
  </tr>
  <tr> 
    <td valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">MarkUp</font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <input name="markup" type="text" id="markup" value="<?= $astock['markup'];?>"  onBlur="vFraction(this)"  size="10" maxlength="10" style="text-align:right" tabindex="<?= array_search('markup',$fields);?>"   onKeypress="if(event.keyCode==13) {document.getElementById('max_discount').focus();return false;}">
      </font> </td>
    <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> %Max 
      Disc </font> </td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <input name="max_discount" type="text" id="max_discount" value="<?= $astock['max_discount'];?>" size="10"  style="text-align:right" maxlength="10" tabindex="<?= array_search('max_discount',$fields);?>"   onKeypress="if(event.keyCode==13) {document.getElementById('addfreight_case').focus();return false;}">
      </font> </td>
    <td colspan="2" rowspan="3" valign="top"><table width="100%" border="0" cellpadding="1" cellspacing="0">
        <tr> 
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
        <tr> 
          <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Supplier(%)</font></td>
          <td nowrap><input name="promo_sdisc" type="text" id="promo_sdisc" value="<?= $astock['promo_sdisc'];?>" size="8"  style="text-align:right"  onKeypress="if(event.keyCode==13) {document.getElementById('promo_cdisc').focus();return false;}"></td>
          <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Company(%)</font></td>
          <td nowrap><input name="promo_cdisc" type="text" id="promo_cdisc" value="<?= $astock['promo_cdisc'];?>" size="8" style="text-align:right" onKeypress="if(event.keyCode==13) {document.getElementById('promo_price').focus();return false;}"></td>
        </tr>
        <tr> 
          <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Promo 
            Price </font></td>
          <td nowrap><input name="promo_price1" type="text" id="promo_price1" value="<?= $astock['promo_price1'];?>" size="8"  style="text-align:right"  onKeypress="if(event.keyCode==13) {document.getElementById('Save').focus();return false;}"></td>
          <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Promo 
            For</font></td>
          <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
            <?= lookUpAssoc('promo_customer',array('All Customers'=>'A','Reward Members'=>'M'),$astock['promo_customer']);?>
            </font></td>
        </tr>
      </table>
      <font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> </strong></font> 
    </td>
  </tr>
  <tr> 
    <td valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Purchase 
      Freight/Cs</font></td>
    <td colspan="3"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <input name="addfreight_case" type="text" id="addfreight_case" value="<?= $astock['addfreight_case'];?>" size="10" maxlength="10" style="text-align:right" tabindex="<?= array_search('markup',$fields);?>"   onKeypress="if(event.keyCode==13) {document.getElementById('taxable').focus();return false;}">
      </font> </td>
  </tr>
  <tr> 
    <td valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Taxable</font></td>
    <td colspan="3" valign="top"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <select name="taxable"  id="taxable" tabindex="<?= array_search('taxable',$fields);?>"   onKeypress="if(event.keyCode==13) {document.getElementById('netitem').focus();return false;}">
        <option value="Y" <?=($astock['taxable']=='Y' ? 'selected' : '');?>>Yes</option>
        <option value="N" <?=($astock['taxable']=='N' ? 'selected' : '');?>>No</option>
      </select>
      Essential
      <select name="essential"  id="essential" tabindex="<?= array_search('taxable',$fields);?>"   onKeypress="if(event.keyCode==13) {document.getElementById('netitem').focus();return false;}">
        <option value="N" <?=($astock['essential']=='N' ? 'selected' : '');?>>No</option>
        <option value="Y" <?=($astock['essential']=='Y' ? 'selected' : '');?>>Yes</option>
      </select>
      </font></td>
  </tr>
  <tr> 
    <td valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Net 
      Item </font></td>
    <td colspan="3"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <select name="netitem" id="netitem"  tabindex="<?= array_search('taxable',$fields);?>"   onKeypress="if(event.keyCode==13) {document.getElementById('inventory').focus();return false;}">
        <option value="N" <?=($astock['netitem']=='N' ? 'selected' : '');?>>No</option>
        <option value="Y" <?=($astock['netitem']=='Y' ? 'selected' : '');?>>Yes</option>
      </select>
      </font> </td>
    <td valign="top">&nbsp;</td>
    <td valign="top"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
      </font></td>
  </tr>
  <tr> 
    <td valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Additional Rewards</font></td>
    <td colspan="3"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <input name="additional_reward_points" type="text" id="additional_reward_points" value="<?= $astock['additional_reward_points'];?>" size="10" maxlength="10" style="text-align:right" tabindex="<?= array_search('markup',$fields);?>"   onKeypress="if(event.keyCode==13) {document.getElementById('taxable').focus();return false;}">
      </font> </td>
  </tr>
</table>
  <!-- </div>-->
