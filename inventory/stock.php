<STYLE>
<!--
	.altText {
	background-color: #FFFFFF;
	font-family: verdana;
	border: #CCCCCC 1px solid;
	font-size: 12px;
	color: #000000
	} 

	
	.altNum {
	background-color: #FFFFFF;
	font-family: verdana;
	border: #CCCCCC 1px solid;
	font-size: 12px;
	color: #000000;
	text-align:right
	} 
	
	.altTextArea {
	background-color: #FFFFFF;
	font-family: verdana;
	border: #CCCCCC 1px solid;
	font-size: 12px;
	color: #000000
	} 
	
	SELECT {
	background-color: #FFFFFF;
	font-family: verdana;
	border: #CCCCCC 1px solid;
	font-size: 10px;
	margin:0px;
	color: #000000
	} 			

	.altBtn {
	background-color: #CFCFCF;
	font-family: verdana;
	font-size: 11px;
	padding: 1px;
	margin: 0px;
	color: #1F016D
	} 
-->
</STYLE>
<script>
function vEnable()
{
	enable = document.getElementById('enable').value;
	if (enable=='Y')
	{
		document.getElementById('header').bgColor='#EFEFEF';
	}
	else
	{
		document.getElementById('header').bgColor='#FF9999';
	}
	return false;
}
function vCost(t)
{
	if (f1.fraction.value == '' || f1.fraction.value == 0)
	{
		f1.fraction.value=1
	}	
	f1.cost.value = t.value/f1.fraction.value
}
function vPrice(t)
{
	if (f1.fraction.value == '' || f1.fraction.value == 0)
	{
		f1.fraction.value=1
	}	
	f1.price.value = t.value/f1.fraction.value
}
function vFraction(t)
{
	
	if (1*(document.getElementById('fraction3').value) == 0)
	{
		document.getElementById('fraction3').value = 1
	}
	if (1*(document.getElementById('fraction2').value) == 0)
	{
		document.getElementById('fraction2').value = 1
	}

	if (1*document.getElementById('cost1').value>0 && 1*document.getElementById('price1').value>0 && document.getElementById('AUTOMARKUP').value != 'NO')
	{
		document.getElementById('markup').value = twoDecimals(100*(1 - (document.getElementById('cost1').value/document.getElementById('price1').value)))
	}
	else if (document.getElementById('markup').value != '')
	{
		if (t.name != 'price1')
		{
			document.getElementById('price1').value = twoDecimals(((document.getElementById('markup').value /100)+1)*document.getElementById('cost1').value );
		}
	}
	
	document.getElementById('price2').value = document.getElementById('price1').value * document.getElementById('fraction2').value
	document.getElementById('price3').value = document.getElementById('price1').value * document.getElementById('fraction3').value
	//added line
	document.getElementById('cost3').value = document.getElementById('cost1').value * document.getElementById('fraction3').value
}
function vSubmit(ul)
{
	if (ul.name == 'Cancel')
	{
		if (confirm("Are you sure to CANCEL stock Record?"))
		{
			document.f1.action="?p=stock&p1=CancelConfirm"
		}	
		else
		{
			document.f1.action="?p=stock&p1=Cancel"
		}
	}
	else if (ul.name== 'Save')
	{
		if (document.getElementById('stock').value == '')
		{
			alert('Please Provide Product Name');
		}
		else if (document.getElementById('unit1').value == '')
		{
			alert('Please Provide Unit of Measure');
		}
		else if (document.getElementById('price1').value == '')
		{
			alert('Please Provide Selling Price');
		}
		else if (document.getElementById('category_id').value == '')
		{
			alert('Please Provide Product Category');
		}
		else
		{
			document.f1.action="?p=stock&p1="+ul.id;
			document.f1.submit();
		}
	}
	else
	{
		document.f1.action="?p=stock&p1="+ul.id;
	}
	return false;	
}
//-->
</script>
<?
if (!chkRights2('stock','mview',$ADMIN['admin_id']))
{
	message1("You have NO acces to Inventory Master...");
	exit;
}
if (!session_is_registered('astock'))
{
	session_register('astock');
	$astock = null;
	$astock = array();
}

//'income_type','unit3','fraction3','cost3','price3',
$fields = array('barcode','casecode','stock','stock_description',
			'serial_required',
			'unit1','unit2','unit3', 'fraction2','fraction3','cost1', 'cost2','cost3',
			'price1','price2','price3', 'markup', 'max_discount',
			'taxable','netitem','promo_customer','essential',
			'inventory','reorder_level','reorder_qty','category_id',
			'classification_id',  'account_id',
			'enable', 'date1_promo', 'date2_promo', 'promo_sdisc', 'promo_cdisc',
			'promo_price1', 'addfreight_case','additional_reward_points');

if ($p1 == '' && $astock['stock_id']=='')
{
	$astock['unit1']='PCS';
	$astock['unit2']='DOZ';
	$astock['unit3']='CASE';
}
if ($id =='' && $p1=='Load')
{
	message("Nothing to Edit...");
}
elseif ($p1=='LoadDone')
{

  	//@include_once('stockbalance.php');
	
	//$stkled = @stockBalance($astock['stock_id'],'', date('Y-m-d'));
	//$astock['balance_qty'] = $stkled['balance_qty'];


}
elseif ($p1=='Load')
{
	$astock = null;
	$astock = array();
	$q = "select * from stock where stock_id = '$id'";
	$r = fetch_assoc($q);

	$astock = $r;
	$focus = 'stock';
	if ($astock['unit1'] == '') $astock['unit1']='PC';
  	/*@include_once('stockbalance.php');*/
	
	//$stkled = @stockBalance($astock['stock_id'],'', date('Y-m-d'));
	//$astock['balance_qty'] = $stkled['balance_qty'];

}		
elseif ($p1 == 'Next >>')
{
	if ($searchby == '') $searchby == 'barcode';
	$q = "select * from stock where $searchby > '".$astock[$searchby]."' order by $searchby offset 0 limit 1";

	$qr = @pg_query($q) or message(pg_errormessage());
	if (@pg_num_rows($qr) == 0)
	{
		message("End of file...");
	}	
	else
	{
		$astock = null;
		$astock = array();
		$r = fetch_assoc($q);
		$astock = $r;
	}	

}
elseif ($p1 == '<< Previous')
{
	if ($searchby == '') $searchby == 'barcode';
	$q = "select * from stock where $searchby < '".$astock[$searchby]."' order by $searchby desc offset 0 limit 1";
	$qr = @pg_query($q);
	if (@pg_num_rows($qr) == 0)
	{
		message("Beginning of file...");
	}	
	else
	{
		$astock = null;
		$astock = array();
		$r = fetch_assoc($q);
		$astock = $r;
	}	

}

if (!in_array($p1,array(null,'showaudit','Load','LoadDone','Next >>','<< Previous')))
{
	for ($c=0;$c<count($fields);$c++)
	{
		if (substr($fields[$c],0,4) == 'date')
		{
			$astock[$fields[$c]] = mdy2ymd(chop($_REQUEST[$fields[$c]]));
		}
		else
		{
			$astock[$fields[$c]] = chop($_REQUEST[$fields[$c]]);
		}	
		if (in_array($fields[$c], array('cost1','price1','percent_share',
                    			'cost2','price2',
					'cost3','price3', 'markup','max_discount',
                    			'reorder_level','reorder_qty','category_id',
                    			'classification_id',  'account_id', 'addfreight_case',
                    			'promo_sdisc', 'promo_cdisc','promo_price1')) && $astock[$fields[$c]]=='')
     {
        $astock[$fields[$c]] = 0;
     } 
		if (in_array($fields[$c], array('fraction3','fraction2',)) && $astock[$fields[$c]]=='')
     {
        $astock[$fields[$c]] = 1;
     } 	 
	 $astock['category_code'] = $_REQUEST['category_code'];              			
	 $astock['classification_code'] = $_REQUEST['classification_code'];              			
	 $astock['account_code'] = $_REQUEST['account_code'];              			
	}

	if (intval($astock['fraction']) == 0)
	{
		$astock['fraction'] =1;
	}
	$astock['stock_code'] = substr($astock['stock_code'],0,10);
	$astock['pixfile_tmp']=$_FILES['pixfile']['tmp_name'];
	$astock['pixfile']=$_FILES['pixfile']['name'];
	$x = explode('.',$astock['pixfile']);
	$astock['pixfile_extension'] = $x[count($x)-1];
	
//	$astock['stock'] = str_ireplace('"',"''",$astock['stock']);

}	


if ($p1 == 'New')
{
	if (!chkRights2('stock','madd',$ADMIN['admin_id']))
	{
		message('You are NOT allowed to add items...');
		exit;
	}
	$aid = $astock['account_id'];
        $cid = $astock['category_id'];

	$astock = null;
	$astock = array();
	$astock['fraction1'] = 1;
        $astock['category_id'] = $cid;
        $astock['account_id'] = $aid;
	$astock['unit1']='PCS';
	$astock['unit2']='DOZ';
	$astock['unit3']='CASE';

}
elseif ($p1 == 'Check')
{
	$q .= "select * from stock where barcode = '".$astock['barcode']."'";
	$qr = @pg_query($q) or message(pg_errormessage());
	if (@pg_num_rows($qr))
	{
		message("Barcode Already Exist...");
	}
	$focus = 'stock';
}

elseif ($p1 == 'selectCategory' && $astock['category_code'] !='')
{
	$q = "select * from category where category_code = '".$astock['category_code']."'";
	$qr = @pg_query($q) or message(pg_errormessage());
	if (@pg_num_rows($qr) > 0)
	{
		$r = @pg_fetch_object($qr);
		$astock['category_id'] = $r->category_id;
		$focus='classification_code';
	}
	else
	{
		message('Category Not Found...');	
		$focus='category_code';
	}	
}
elseif ($p1 == 'selectClassification' && $astock['classification_code'] !='')
{
	$q = "select * from classification where classification_code = '".$astock['classification_code']."'";
	$qr = @pg_query($q) or message(pg_errormessage());
	if (@pg_num_rows($qr) > 0)
	{
		$r = @pg_fetch_object($qr);
		$astock['classification_id'] = $r->category_id;
		$focus='account_code';
	}
	else
	{
		message('Classification Not Found...');	
		$focus='classification_code';
	}	
}
elseif ($p1 == 'selectAccount' && $astock['account_code'] !='')
{
	$q = "select * from account where account_code = '".$astock['account_code']."'";
	$qr = @pg_query($q) or message(pg_errormessage());
	if (@pg_num_rows($qr) > 0)
	{
		$r = @pg_fetch_object($qr);
		$astock['account_id'] = $r->account_id;
//		$focus='date1_promo';
	}
	else
	{
		message('Supplier Account Not Found...');	
	}	
	$focus='account_code';
}
elseif ($p1=='showaudit')
{
	$astock['showaudit'] =1;
}
elseif ($p1 == 'Save' && $astock['stock']=='')
{
	message('NOT able to Save, Please Provide Stock Definition...');
}
elseif ($p1 == 'Save' && $astock['unit1']== '')
{
	message('NOT able to Save, Please Provide Unit...');
}
elseif ($p1 == 'Save' && $astock['category_id']== '')
{
	message('NOT able to Save, Please Provide Product Category...');
}
elseif ($p1 == 'Save' && $astock['account_id']== '')
{
	message('NOT able to Save, Please Provide Supplier...');
}
elseif ($p1 == 'Save' && $astock['stock']!='')
{
	if (!chkRights2('stock','madd',$ADMIN['admin_id']))
	{
		message('You have permission for editing entries...');
		exit;
	}
	if ($astock['price1'] == '')
	{
		message("No Price Provided...");
	}
	if ($astock['stock_id'] == '')
	{
		$q = "select * from stock where barcode = '".$astock['barcode']."'";
		$qr = @pg_query($q) or message(pg_errormessage());
		if (pg_num_rows($qr) > 0)
		{
			message("Barcode Already Exists...");
			$ok = false;
			exit;
		}
		else
		{
			$ok=true;
		}
		if ($ok)
		{
			$date_encoded = date('Y-m-d');
			$audit = 'Encoded by '.$ADMIN['name'].' on '.date('m/d/Y g:ia').'; ';
			$astock['audit'] = $audit;

			$date_encoded = date('Y-m-d');		
			$q = "insert into stock 
					(date_encoded,date_updated, casecode,barcode,stock,stock_description,serial_required,
					unit1,unit2,unit3, fraction2, fraction3, category_id, classification_id, cost1, cost2, cost3, price1,price2, price3,  
					reorder_level, reorder_qty, account_id, income_type,  taxable, essential, markup, inventory, addfreight_case,
					netitem, max_discount, promo_cdisc, promo_sdisc, date1_promo, date2_promo, promo_price1, promo_customer, 
					audit, enable, additional_reward_points)
				values
					('$date_encoded','$date_encoded', '".$astock['casecode']."','".$astock['barcode']."','".$astock['stock']."',
					'".$astock['stock_description']."','".$astock['serial_required']."',
					'".$astock['unit1']."','".$astock['unit2']."','".$astock['unit3']."', 
					'".$astock['fraction2']."','".$astock['fraction3']."', 
					'".$astock['category_id']."','".$astock['classification_id']."',
					'".$astock['cost1']."','".$astock['cost2']."','".$astock['cost3']."',
					'".$astock['price1']."', '".$astock['price2']."', '".$astock['price3']."', 
					'".$astock['reorder_level']."','".$astock['reorder_qty']."',
					'".$astock['account_id']."','".$astock['income_type']."',
					'".$astock['taxable']."','".$astock['essential']."','".$astock['markup']."',
					'".$astock['inventory']."','".$astock['addfreight_case']."','".$astock['netitem']."',
					'".$astock['max_discount']."','".$astock['promo_cdisc']."','".$astock['promo_sdisc']."',
					'".$astock['date1_promo']."','".$astock['date2_promo']."','".$astock['promo_price1']."','".$astock['promo_customer']."',
					'".$astock['audit']."','".$astock['enable']."','".$astock['additional_reward_points']."')";
		}				
	}
	else
	{
		$audit = $astock['audit'].'Updated by '.$ADMIN['name'].' on '.date('m/d/Y g:ia').'; ';
		$astock['audit'] = $audit;
		$date_updated = date('Y-m-d');
		$q = "update stock set audit='$audit', date_updated='$date_updated'";
		for($c=0;$c<count($fields);$c++)
		{
			$q .= ", ".$fields[$c] ."='".$astock[$fields[$c]]."'";
		}
		$q .= " where stock_id='".$astock['stock_id']."'";
	}
	
	$qr = @pg_query($q);
	if ($qr)
	{
		message("Stocks Inventory Data Saved...");
	//	pg_query("COMMIT");
	}	
	else
	{
	//	@pg_query("ROLLBACK");	
		message1("Error saving stock data...".pg_errormessage().$q);	
	}	
	if ($qr)
	{
		if ($astock['stock_id'] == '')
		{
			$id = pg_insert_id('stock');
			$astock['stock_id'] = $id;
		}
		if ($astock['pixfile_tmp'] != '')
		{
			$extension = $astock['pixfile_extension'];
			$picture_source = $astock['pixfile_tmp'];
			$picture_file = "images\stock_".strtolower($astock['stock_id']).".".strtolower($extension);
			$pix = "stock_".strtolower($astock['stock_id']).".".strtolower($extension);
			if (!copy($picture_source,$picture_file))
			{
				message("Unable to upload picture....".$picture_source." To ".$picture_file);
			}
			else
			{
				$astock['pix'] = $pix;
				$q = "update stock set pix='".$astock['pix']."' where stock_id='".$astock['stock_id']."'";
				$qr = @pg_query($q) or message("Unable to update picture filename to database...");
				if ($qr) message("Picture file name updated...");
			}
		}	

	}

}
elseif($p1 == 'Add Barcode' && $astock['stock_id'] !='')
{
	$new_barcode = $_REQUEST['new_barcode'];
	if ($new_barcode != '')
	{
		$q = "insert into barcode (barcode, stock_id, admin_id)
				values ('$new_barcode','".$astock['stock_id']."', '".$ADMIN['admin_id']."')";
		$qr = @pg_query($q) or message(pg_errormessage());
	}
	else
	{
		message("No Barcode Specified...");
	}
}
		$in_qty = $out_qty = 0;
		$balance_qty = $in_qty - $out_qty;
		
?>
<script type="text/javascript" src="tabber.js"></script>
<link rel="stylesheet" href="tab.css" TYPE="text/css" MEDIA="screen">
<link rel="stylesheet" href="tab-print.css" TYPE="text/css" MEDIA="print">

<script type="text/javascript">

/* Optional: Temporarily hide the "tabber" class so it does not "flash"
   on the page as plain HTML. After tabber runs, the class is changed
   to "tabberlive" and it will appear. */

document.write('<style type="text/css">.tabber{display:none;}<\/style>');
</script>
<form enctype="multipart/form-data" action="" name="f1" method="post" id="f1" style="margin:10px">
  <table width="95%" border="0" cellpadding="0" cellspacing="0">
    <tr> 
      <td>Stock Search 
        <input name="search" type="text" id="search" value="<?= $search;?>"  onKeypress="if(event.keyCode == 13){document.getElementById('go').click();}"> 
        <?= lookUpAssoc('searchby',array('Barcode'=>'barcode','Item Name'=>'stock','Item Code'=>'stock_code','Description'=>'stock_description'), $aStockBrowse['searchby']);?>
        <input name="p1" type="button" id="go" value="Go" accesskey="G" onClick="f1.action='?p=stock.browse&p1=Go&mcategory_id=&search='+search.value+'&searchby='+searchby.value;f1.submit()"  class="altBtn"> 
        <input type="button" name="Submit2" value="Add New" onClick="window.location='?p=stock&p1=New'" accesskey="N" class="altBtn"> 
        <input type="button" name="Submit23" value="Browse" onClick="window.location='?p=stock.browse'" accesskey="B"  class="altBtn"> 
        <input name="p1" type="submit" id="p1" accesskey="V" value="&lt;&lt; Previous" class="altBtn"> 
        <input name="p1" type="submit" id="p1" accesskey="C" value="Next &gt;&gt;" class="altBtn"> 
        <input type="button" name="Submit232" value="Close" onClick="window.location='?p='" accesskey="C" class="altBtn"> 
        <hr color="#CC0000"></td>
    </tr>
    <tr> 
      <td>
	  <table width="100%" cellpadding="0" cellspacing="0" bgcolor="<?= ($astock['enable']=='N' ? '#FF9999' :  '#EFEFEF' );?>" id="header">
          <tr> 
            <td width="30%" height="18" nowrap><b><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Item 
              Bacode<br>
              <input name="barcode" type="text" id="barcode" value="<?= $astock['barcode'];?>" size="16" maxlength="16" onKeypress="if(event.keyCode==13) {document.getElementById('Check').focus();return false;}" onBlur="document.getElementById('Check').click();" style="font-size:17px;">
              <input name="p12" type="button" id="p13" value="+"  onClick="if (category_id.value == '' && !confirm(' No Category Specified. Do you still wish to Generate Barcode?')){return false;}else {wait('Generating Serialized Barcode...');xajax_genBarcode(xajax.getFormValues('f1'))};return false;""   onmouseover="showToolTip(event,'Click To Generate Serialized Barcode...');return false" onmouseout="hideToolTip()" class="altBtn">
              <input name="p12" type="button" id="Check" value="?" onClick="xajax_vBarcode(xajax.getFormValues('f1'))" alt="Check If Barcode Exists..."  onmouseover="showToolTip(event,'Click to check if the Barcode Already Exists..');return false" onmouseout="hideToolTip()" class="altBtn">
              </font></b></td>
            <td height="18" width="21%"><b><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Item 
              Name <br>
              <input name="stock" type="text" id="stock" value="<?= stripslashes($astock['stock']);?>" size="30" maxlength="40"  tabindex="<?= array_search('stock',$fields);?>" max="40" onBlur="vDesc()"   onKeypress="if(event.keyCode==13) {document.getElementById('casecode').focus();return false;}" style="font-size:17px;">
              </font></b></td>
            <td height="18" width="38%"><b><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Enabled<br>
              <select name="enable"  id="enable" tabindex="<?= array_search('enable',$fields);?>" style="font-size:17px;" onChange="vEnable()">
                <option value="Y" <?=($astock['enable']=='Y' ? 'selected' : '');?>>Yes</option>
                <option value="N" <?=($astock['enable']=='N' ? 'selected' : '');?>>No</option>
              </select>
              </font></b></td>
            <td height="18" width="0%"><b></b><br> </td>
            <td height="18" width="11%" align="center"><b><font size="2" face="Verdana, Arial, Helvetica, sans-serif">SKU 
              Id<br>
              <input name="account_id" type="text" id="account_id" value="<?= str_pad($astock['stock_id'],8,'0',STR_PAD_LEFT);?>" size="12" maxlength="12" style="text-align:center; border:0; background-color:#EFEFEF; padding:0;" readOnly >
              </font></b></td>
          </tr>
        </table></td>
    </tr>
    <tr height="363px">
      <td valign="top">	
  <div class="tabber" style="width:95%; left:20px">
  <div class="tabbertab" style="top-margin:0px">
	  <h2>Product Info</h2>
	  <p><? include_once('stock.info.php'); ?></p>
     </div>

     <div class="tabbertab">
	  <h2>Alt Barcode</h2>
	  <p><? include_once('stock.barcode.php'); ?></p>
     </div>

     <div class="tabbertab">
	  <h2>Promotions</h2>
	  <p><? include_once('stock.promo.php'); ?></p>
     </div>
     <div class="tabbertab">
	  <h2>Image</h2>
	  <p><? include_once('stock.image.php'); ?></p>
     </div>

     <div class="tabbertab">

     <div class="tabbertab">
	  <h2>Audit Log</h2>
	  <p><? include_once('stock.audit.php'); ?></p>
     </div>

</div>
	  </td>
    </tr>
	<tr>
	<td><table width="1%" border="0" cellpadding="1" cellspacing="1" bgcolor="#CCCCCC">
        <tr bgcolor="#FFFFFF"> 
          <td nowrap width="3%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
            <a  accesskey="S" href="javascript: f1.action='?p=stock&p1=Save';f1.submit();"> 
            <input type="image" acc  accesskey="S" src="../graphics/save.jpg" alt="Save This Claim Form" name="Save" width="57" height="15" border="0" id="Save" onClick="f1.action='?p=stock&p1=Save';f1.submit();" tabIndex="99">
            </a> </strong></font></td>
          <td nowrap width="46%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
            <img src="../graphics/print.jpg" alt="Print This Claim Form"  onClick="window.print()" accesskey="P"> 
            </strong></font></td>
          <!--<td nowrap width="26%"> <input name="Cancel" id="Cancel" type="image" src="../graphics/cancel.jpg" onClick="vSubmit(this)" alt="Cancel This Entry" width="77" height="20"></td>-->
          <td nowrap width="25%"> <input type='image' name="New" id="New" onClick="f1.action='?p=stock&p1=New';f1.submit();"  src="../graphics/new.jpg" alt="New Claim Form" width="63" height="20" accesskey="N"> 
          </td>
        </tr>
      </table></td></tr>
  </table>
  </form>
  
  <?
  
  if ($focus != '')
  {
 	echo "<script>document.getElementById('$focus').focus()</script>";
  }
  else
  {
  	echo "<script>document.getElementById('search').focus()</script>";
  }
  ?>
