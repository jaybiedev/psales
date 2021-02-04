<STYLE TYPE="text/css">
<!--
 .altTextFormat {
	background-color: #FFFFFF;
	font-family: verdana;
	border: #CCCCCC 1px solid;
	font-size: 11px;
	color: #000000
	} 
 .hideTextFormat {
	background-color: #FFFFFF;
	font-family: verdana;
	border: #CCCCCC 0px solid;
	font-size: 11px;
	font-color: #FFFFFF;
	color:  #FFFFFF
	} 		
-->
</STYLE><script>
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
	document.getElementById('price2').value = document.getElementById('price1').value * document.getElementById('fraction2').value
	document.getElementById('price3').value = document.getElementById('price1').value * document.getElementById('fraction3').value

	if (1*document.getElementById('cost1').value>0 && 1*document.getElementById('price1').value>0)
	{
		document.getElementById('markup').value = twoDecimals(100*(1 - (document.getElementById('cost1').value/document.getElementById('price1').value)))
//		document.getElementById('markup').value = twoDecimals(100*(document.getElementById('cost1').value/document.getElementById('price1').value))
	}
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
			'taxable','netitem',
			'inventory','reorder_level','reorder_qty','category_id',
			'classification_id',  'account_id',
			'enable', 'date1_promo', 'date2_promo', 'promo_sdisc', 'promo_cdisc',
			'promo_price1');

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
elseif ($p1=='Load')
{
	$astock = null;
	$astock = array();
	$q = "select * from stock where stock_id = '$id'";
	$r = fetch_assoc($q);

	$astock = $r;
	//$astock['stock'] = str_ireplace('"',"''",$astock['stock']);
	if ($astock['unit1'] == '') $astock['unit1']='PC';
	
	if ($astock['category_id'] >0)
	{
		$q = "select * from category where category_id='".$astock['category_id']."'";
		$qr = @pg_query($q) or message(pg_errormessage());
		$r = @pg_fetch_object($qr);
		$astock['category_code'] = $r->category_code;
	}
	if ($astock['classification_id'] > 0)
	{
		$q = "select * from classification where classification_id='".$astock['classification_id']."'";
		$qr = @pg_query($q) or message(pg_errormessage());
		$r = @pg_fetch_object($qr);
		$astock['classification_code'] = $r->classification_code;
	}
	if ($astock['account_id'] > 0)
	{
		$q = "select * from account where account_id='".$astock['account_id']."'";
		$qr = @pg_query($q) or message(pg_errormessage());
		$r = @pg_fetch_object($qr);
		$astock['account_code'] = $r->account_code;
	}

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

if (!in_array($p1,array(null,'showaudit','Load','Next >>','<< Previous')))
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
                    			'classification_id',  'account_id',
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
	$focus = 'case_code';
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
		
			$q = "insert into stock 
					(date_encoded,date_updated, casecode,barcode,stock,stock_description,serial_required,
					unit1,unit2,unit3, fraction2, fraction3, category_id, classification_id, cost1, cost2, cost3, price1,price2, price3,  
					reorder_level, reorder_qty, account_id, income_type,  taxable, markup, inventory, 
					netitem, max_discount, promo_cdisc, promo_sdisc, date1_promo, date2_promo, promo_price1, audit, enable)
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
					'".$astock['taxable']."','".$astock['markup']."',
					'".$astock['inventory']."','".$astock['netitem']."',
					'".$astock['max_discount']."','".$astock['promo_cdisc']."','".$astock['promo_sdisc']."',
					'".$astock['date1_promo']."','".$astock['date2_promo']."','".$astock['promo_price1']."',
					'".$astock['audit']."','".$astock['enable']."')";
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
<form enctype="multipart/form-data" action="" name="f1" method="post" id="f1" style="margin:1px">
  <table width="95%" border="0" cellpadding="0" cellspacing="0">
    <tr> 
      <td>Stock Search 
        <input name="search" type="text" id="search" value="<?= $search;?>"  onKeypress="if(event.keyCode == 13){document.getElementById('go').click();return false;}"> 
        <?= lookUpAssoc('searchby',array('Barcode'=>'barcode','Item Name'=>'stock','Item Code'=>'stock_code','Description'=>'stock_description'), $searchby);?>
        <input name="p1" type="button" id="go" value="Go" accesskey="G" onClick="f1.action='?p=stock.browse&p1=Go&mcategory_id=&search='+search.value+'&searchby='+searchby.value;f1.submit()"> 
        <input type="button" name="Submit2" value="Add New" onClick="window.location='?p=stock&p1=New'" accesskey="N"> 
        <input type="button" name="Submit23" value="Browse" onClick="window.location='?p=stock.browse'" accesskey="B">
        <input name="p1" type="submit" id="p1" accesskey="V" value="&lt;&lt; Previous">
        <input name="p1" type="submit" id="p1" accesskey="C" value="Next &gt;&gt;"> 
        <input type="button" name="Submit232" value="Close" onClick="window.location='?p='" accesskey="C"> 
        <hr color="#CC0000"></td>
    </tr>
    <tr>
</table>	
<div class="tabber" style="width:95%; left:20px">
     <div class="tabbertab" style="top-margin:0px">
	  <h2>Product Info</h2>
	  <p><? include_once('stock.info.php');?></p>
     </div>

     <div class="tabbertab">
	  <h2>Alt Barcode</h2>
	  <p><? include_once('stock.barcode.php');?></p>
     </div>

     <div class="tabbertab">
	  <h2>Promotions</h2>
	  <p><? include_once('stock.promo.php');?></p>
     </div>

     <div class="tabbertab">
	  <h2>Audit</h2>
	  <p><? include_once('stock.audit.php');?></p>
     </div>

</div>
<!-- 	
	  <td><table width="100%" border="0" cellpadding="3" cellspacing="2" bgcolor="#FFFFFF">
          <tr> 
            <td width="107px" nowrap id="StockInfo" background="../graphics/tab_hi.JPG"  onMouseOver="this.className='highlight'" onMouseOut="this.className='normal'"> 
              <a accesskey="A" href="javascript: vTab('S')">General Info</a></td>
            <td width="107px" nowrap id="StockBarcode" background="../graphics/tab_lo.JPG" onMouseOver="this.className='highlight'" onMouseOut="this.className='normal'"> 
              <a accesskey="B" href="javascript: vTab('B')">Alt Barcode</a></td>
            <td width="107px" nowrap id="StockPromo"  background="../graphics/tab_lo.JPG" onMouseOver="this.className='highlight'" onMouseOut="this.className='normal'"> 
              <a accesskey="B" href="javascript: vTab('P')">Promo</a></td>
            <td width="50%"></td>
            <td nowrap bgcolor="#FFFFFF">&nbsp;<a href='?p=stock&p1=Previous'> 
              <img src="../graphics/redarrow_left.gif" width="4" height="7" border="0"> 
              Previous</a>&nbsp;| &nbsp;<a href='?p=stock&p1=Next'>Next</a> <a href="?p=stock&p1=Next"><img src="../graphics/redarrow_right.gif" width="4" height="7" border="0"></a>&nbsp;</td>
            <td width="5%"></td>
          </tr>
        </table> </td>
		<tr>
		<td>
	  <?
		  include_once('stock.info.php');
		  include_once('stock.barcode.php');
		  include_once('stock.promo.php');
  		?>
	  </td>
    </tr>
  </table>
 --> 
  </form>
  
  <?

  if ($focus != '')
  {
 	echo "<script>document.getElementById(\"$focus\").focus()</script>";
  }
  else
  {
  	echo "<script>document.getElementById('search').focus()</script>";
  }
  ?>
