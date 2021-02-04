<script>
function vDisable(obj)
{
	if (obj=='sdisc')
	{
		document.getElementById('buyn').value=0;
		document.getElementById('buyn').disabled=1;

		document.getElementById('buym').value=0;
		document.getElementById('buym').disabled=1;

		document.getElementById('nfor').value=0;
		document.getElementById('nfor').disabled=1;

		document.getElementById('pricenfor').value=0;
		document.getElementById('pricenfor').disabled=1;

		document.getElementById('promo_price').value=0;
		document.getElementById('promo_price').disabled=1;

		document.getElementById('promo_type').value=2;

	}
	else if (obj=='promo_price')
	{
		document.getElementById('buyn').value=0;
		document.getElementById('buyn').disabled=1;

		document.getElementById('buym').value=0;
		document.getElementById('buym').disabled=1;

		document.getElementById('nfor').value=0;
		document.getElementById('nfor').disabled=1;

		document.getElementById('pricenfor').value=0;
		document.getElementById('pricenfor').disabled=1;

		document.getElementById('sdisc').value=0;
		document.getElementById('sdisc').disabled=1;

		document.getElementById('cdisc').value=0;
		document.getElementById('cdisc').disabled=1;

		document.getElementById('promo_type').value=3;

	}
	else if (obj=='buynm')
	{
		document.getElementById('sdisc').value=0;
		document.getElementById('sdisc').disabled=1;

		document.getElementById('cdisc').value=0;
		document.getElementById('cdisc').disabled=1;

		document.getElementById('promo_price').value=0;
		document.getElementById('promo_price').disabled=1;

		document.getElementById('nfor').value=0;
		document.getElementById('nfor').disabled=1;

		document.getElementById('pricenfor').value=0;
		document.getElementById('pricenfor').disabled=1;

		document.getElementById('promo_type').value=4;

	}
	else if (obj=='nfor')
	{
		document.getElementById('sdisc').value=0;
		document.getElementById('sdisc').disabled=1;

		document.getElementById('cdisc').value=0;
		document.getElementById('cdisc').disabled=1;

		document.getElementById('promo_price').value=0;
		document.getElementById('promo_price').disabled=1;

		document.getElementById('buyn').value=0;
		document.getElementById('buyn').disabled=1;

		document.getElementById('buym').value=0;
		document.getElementById('buym').disabled=1;

		document.getElementById('promo_type').value=5;
	}
	return;
}
function vFrom(o)
{
	if (o == 'date')
	{
		if (document.getElementById('date_to').value == '')
		{
			document.getElementById('date_to').value = document.getElementById('date_from').value
		}
	}	
	else if (o == 'barcode')
	{
		if (document.getElementById('barcode_to').value == '')
		{
			document.getElementById('barcode_to').value = document.getElementById('barcode_from').value
		}
	}	
	else if (o == 'category_id')
	{
		if (document.getElementById('category_id_to').value == '')
		{
			document.getElementById('category_id_to').value = document.getElementById('category_id_from').value
		}
	}	
	return false
}
</script>
  <?
  function rndOff($n)
  {
  	$n1 = round($n,2);
	$an = explode('.',$n1);
	$nn=='';
	if (substr($an[1],1,1) > 2)
	{
		$nn = $an[0].'.'.substr($an[1],0,1).'5';
	}
	else
	{
		$nn = $an[0].'.'.substr($an[1],0,1).'0';
	}
	return $nn;
  }
  if (!session_is_registered('aPD'))
  {
  	session_register('aPD');
	$aPD=null;
	$aPD=array();
	
  }
  if (!session_is_registered('aPDD'))
  {
  	session_register('aPDD');
	$aPDD=null;
	$aPDD=array();
	
  }
 
  $module ='promo';
  
  $fields_header = array('account_code','account_id','date_from','date_to','sdisc','include_net','customer','cdisc',
  									'promo_type','buyn','buym','nfor','pricenfor',
  									'barcode_from', 'barcode_to', 'all_items', 
							  		'category_code_from','category_code_to' ,'category_id_from','category_id_to','promo_price', 'remark');
  if (!in_array($p1,array('','Delete Checked','New','Print','Load','mv')))
  {
  	for ($c=0;$c<count($fields_header);$c++)
	{
		if (substr($fields_header[$c],0,4) == 'date')
		{	
			$aPD[$fields_header[$c]] = mdy2ymd($_REQUEST[$fields_header[$c]]);
		}
		else
		{
			$aPD[$fields_header[$c]] = $_REQUEST[$fields_header[$c]];

			if ($aPD[$fields_header[$c]] == '' && in_array($fields_header[$c],array('category_id_from','category_id_to','promo_price','sdisc','cdisc','promo_type','buyn','buym','nfor','pricenfor')))
			{
				$aPD[$fields_header[$c]] = 0;
			}

		}
	}
	
	if ($aPD['category_id_from']  > 0)
	{
		$aPD['category_code_from'] = lookUpTableReturnValue('x','category','category_id', 'category_code', $aPD['category_id_from']);
	}
	else
	{
		$aPD['category_code_from'] = '';
	}
	if ($aPD['category_id_to']  > 0)
	{
		$aPD['category_code_to'] = lookUpTableReturnValue('x','category','category_id', 'category_code', $aPD['category_id_to']);
	}
	else
	{
		$aPD['category_code_to'] = '';
	}
  }

  if ($p1 == 'selectAccountCode')
  {
  	$q = "select * from account where account_code = '".$aPD['account_code']."'";
	$qr = @pg_query($q) or message(pg_errormessage());
	if (@pg_num_rows($qr) > 0)
	{
		$r = @pg_fetch_object($qr);
		$aPD['account_id'] = $r->account_id;
		$focus = 'account_id';
	}
	else
	{
		message("Supplier Account Code NOT Found...");
		$focus = 'account_code';
	}
  }
  elseif ($p1 == 'selectCategoryCodeFrom' && $aPD['category_code_from'] != '')
  {
  	$q = "select * from category where category_code = '".$aPD['category_code_from']."'";
	$qr = @pg_query($q) or message(pg_errormessage());
	if (@pg_num_rows($qr) > 0)
	{
		$r = @pg_fetch_object($qr);
		$aPD['category_id_from'] = $r->category_id;
		$focus = 'category_id_from';
	}
	else
	{
		message("Category Code NOT Found...");
		$focus = 'category_code_from';
	}
  }
  elseif ($p1 == 'selectCategoryCodeTo'  && $aPD['category_code_to'] != '')
  {
  	$q = "select * from category where category_code = '".$aPD['category_code_to']."'";
	$qr = @pg_query($q) or message(pg_errormessage());
	if (@pg_num_rows($qr) > 0)
	{
		$r = @pg_fetch_object($qr);
		$aPD['category_id_to'] = $r->category_id;
		$focus = 'category_id_to';
	}
	else
	{
		message("Category Code NOT Found...");
		$focus = 'category_code_to';
	}
  }
  elseif ($p1 == 'Cancel')
  {
  	if ($aPD['promo_header_id'] != '')
	{
	  	$q = "update promo_header set enable='N' where promo_header_id='".$aPD['promo_header_id']."'";
		$qr = @pg_query($q) or message(pg_errormessage());
		if ($qr)
		{
			$audit = 'Cancelled by '.$ADMIN['name'].' on '.date('m/d/Y g:ia').'; ';
			audit($module, $q, $ADMIN['admin_id'], $audit, $aPD['promo_header_id']);
			message("Promo Period CANCELLED...");
		}	
	}	
	$aPD = null;
	$aPD = array();
	$aPDD = null;
	$aPDD = array();
		
  }
  elseif ($p1 == 'New' or $p1 == 'Add New' or $p1 == 'Generate New')
  {
	$aPD = null;
	$aPD = array();
	$aPDD = null;
	$aPDD = array();
  }
  elseif ($p1 == 'Load' && $id != '')
  {
  	$aPD = null;
	$aPD = array();
  	$aPDD = null;
	$aPDD = array();
	$q = "select * from promo_header where promo_header_id='$id'";
	$qr = @pg_query($q) or message(pg_errormessage());
	$r = @pg_fetch_assoc($qr);
	$aPD = $r;
	
	$q = "select * from promo_detail where promo_header_id='".$aPD['promo_header_id']."'";
	$qr = @pg_query($q) or message(pg_errormessage());

	while ($r = @pg_fetch_assoc($qr))
	{
		$q = "select stock, barcode, netitem from stock where stock_id='".$r['stock_id']."'";
		$qqr = @pg_query($q) or message(pg_errormessage());
		$r += @pg_fetch_assoc($qqr);
		$aPDD[] = $r;
	}

  }
  elseif (($p1 == 'GenBarcode' || $p1 == 'GenCategory' || $p1 == 'GenSupplier') && $aPD['account_id'] == '')
  {
  	message('Specify Supplier Please...');
  }
  elseif (($p1 == 'GenBarcode' || $p1 == 'GenCategory' || $p1 == 'GenSupplier') && $aPD['sdisc'] == '' && $aPD['cdisc'] == '' && $aPD['promo_type'] < 3)
  {
  	message('Specify Discount Please...');
  }
  elseif (($p1 == 'GenBar' || $p1 == 'GenCategory' || $p1 == 'GenSupplier' ) && ($aPD['date_from'] == '--' || $aPD['date_to'] == '--'))
  {
  	message('Specify Promo [From] and [To] Dates Please...');
  }
  elseif ($p1 == 'GenSupplier')
  {
  		$aPDD = null;
		$aPDD = array();
  		$aPD['status'] = 'MODIFY';
		$q = "select * 
				from 
					stock 
				where
					enable = 'Y' and
					account_id='".$aPD['account_id']."'";
					
		if ($aPD['include_net'] == 'N')
		{
			$q .= " and netitem = 'N'";
		}
		elseif ($aPD['include_net'] == 'Y')
		{
			$q .= " and netitem = 'Y'";
		}
		$q .= " order by barcode ";

		$qr = @pg_query($q) or message(pg_errormessage());
		while ($r = @pg_fetch_assoc($qr))
		{
			$r['date_from'] = ymd2mdy($aPD['date_from']);
			$r['date_to'] = ymd2mdy($aPD['date_to']);
			$r['sdisc'] = $aPD['sdisc'];
			$r['cdisc'] = $aPD['cdisc'];
			$r['promo_price'] = $r['price1'] * (1 - ($aPD['sdisc'] + $aPD['cdisc'])/100);

			$c=0;
			$fnd=0;
			
			if ($aPD['promo_header_id'] != '')
			{
				foreach ($aPDD as $temp)
				{
					if ($temp['stock_id'] == $r['stock_id'])
					{
						$r['promo_detail_id']=$temp['promo_detail_id'];
						$aPDD[$c] = $r;
						$fnd=1;
						break;
					}
					$c++;
				}
			}
			if ($fnd == '0')
			{
				$aPDD[] = $r;
			}
		}	
  }
  elseif ($p1 == 'GenBarcode'  && $aPD['barcode_from'] == '')
  {
  	message('No Barcode To Generate...Please specify...');
  }
  elseif ($p1 == 'GenBarcode')
  {
  		$aPDD = null;
		$aPDD = array();

  		$aPD['status'] = 'MODIFY';
		$q = "select * 
				from 
					stock 
				where
					enable = 'Y' and
					account_id='".$aPD['account_id']."'";
		if ($aPD['barcode_to']=='')
		{
			$q .= " and	barcode='".$aPD['barcode_from']."'";
		}
		else
		{
			$q.=  " and	barcode>='".$aPD['barcode_from']."' 
					and barcode<='".$aPD['barcode_to']."'";
		}
		if ($aPD['include_net'] == 'N')
		{
			$q .= " and netitem = 'N'";
		}
		elseif ($aPD['include_net'] == 'Y')
		{
			$q .= " and netitem = 'Y'";
		}
		$q .= "order by barcode ";
		
		$qr = @pg_query($q) or message(pg_errormessage());
		while ($r = @pg_fetch_assoc($qr))
		{
			$r['date_from'] = ymd2mdy($aPD['date_from']);
			$r['date_to'] = ymd2mdy($aPD['date_to']);
			$r['sdisc'] = $aPD['sdisc'];
			$r['cdisc'] = $aPD['cdisc'];
			$r['promo_price'] = $r['price1'] * (1 - ($aPD['sdisc'] + $aPD['cdisc'])/100);

			$c=0;
			$fnd=0;
			foreach ($aPDD as $temp)
			{
				if ($temp['stock_id'] == $r['stock_id'])
				{
					$r['promo_detail_id']=$temp['promo_detail_id'];
					$aPDD[$c] = $r;
					$fnd=1;
					break;
				}
				$c++;
			}		
			if ($fnd == '0')
			{
				$aPDD[] = $r;
			}
		}	
  }
  elseif ($p1 == 'GenCategory'  && $aPD['category_id_from'] == '' && $aPD['all_items'] == 'N')
  {
  	message('No Product Category To Generate...Please specify...');
  }
  elseif ($p1 == 'GenCategory' && $aPD['all_items'] == 'Y' )
  {
  }
  elseif ($p1 == 'GenCategory')
  {
  		$aPDD = null;
		$aPDD = array();

  		$aPD['category_code_from'] = lookUpTableReturnValue('x','category','category_id','category_code',$aPD['category_id_from']);
  		$aPD['category_code_to'] = lookUpTableReturnValue('x','category','category_id','category_code',$aPD['category_id_to']);
		
  		$aPD['status'] = 'MODIFY';
		$q = "select * 
				from 
					stock,
					category
				where
					category.category_id=stock.category_id and
					stock.enable = 'Y' and
					stock.account_id='".$aPD['account_id']."'";

		if ($aPD['include_net'] == 'N')
		{
			$q .= " and netitem = 'N'";
		}
		elseif ($aPD['include_net'] == 'Y')
		{
			$q .= " and netitem = 'Y'";
		}

		if ($aPD['category_code_to'] == '')
		{
				$q .= " and category.category_code='".$aPD['category_code_from']."'";
		}
		else
		{	 
				$q .= " and category.category_code>='".$aPD['category_code_from']."' and 
						category.category_code<='".$aPD['category_code_to']."'";
		}
		$q .= "order by barcode ";
		$qr = @pg_query($q) or message(pg_errormessage());
		while ($r = @pg_fetch_assoc($qr))
		{
			$r['date_from'] = ymd2mdy($aPD['date_from']);
			$r['date_to'] = ymd2mdy($aPD['date_to']);
			$r['sdisc'] = $aPD['sdisc'];
			$r['cdisc'] = $aPD['cdisc'];
			//$r['promo_price'] = rndOff($r['price1'] * (1 - ($aPD['sdisc'] + $aPD['cdisc'])/100));

			$r['promo_price'] = $r['price1'] * (1 - ($aPD['sdisc'] + $aPD['cdisc'])/100);
			$c=0;
			$fnd=0;
			foreach ($aPDD as $temp)
			{
				if ($temp['stock_id'] == $r['stock_id'])
				{
					$r['promo_detail_id']=$temp['promo_detail_id'];
					$aPDD[$c] = $r;
					$fnd=1;
					break;
				}
				$c++;
			}		
			if ($fnd == '0')
			{
				$aPDD[] = $r;
			}
		}	
  }
  elseif ($p1 == 'Save' && ($aPD['account_id'] == '' or (count($aPDD) == 0 && $aPD['all_items'] =='N')))
  {
  	message("Nothing to SAVE...");
  }
  elseif ($p1 == 'Save' && $aPD['promo_type'] == 0)
  {
  	message("Unable To Save.  No Promo Type Specified...");
  }
  elseif ($p1 == 'Save' && (($aPD['buyn'] >'0' && $aPD['buym']<= '0') || ($aPD['buyn'] == '0' && $aPD['buym']> '0')))
  {
  	message("Unable To Save.   Buy (n) Free (m)..... NO value for (m) Specified...");
  }
  elseif ($p1 == 'Save' && (($aPD['nfor'] >'0' && $aPD['pricenfor']<= '0') || ($aPD['nfor'] =='0' && $aPD['pricenfor']> '0')))
  {
  	message("Unable To Save.   Buy (n) For the Price of (m)..... NO value for (m) Specified...");
  }
  elseif ($p1 == 'Save')
  {
  	begin();
  	$ok=1;
  	if ($aPD['promo_header_id'] == '')
	{
		$generated=date('Y-m-d');
		$audit = 'Encoded by '.$ADMIN['name'].' on '.date('m/d/Y g:ia').'; ';
		$q =  "insert into promo_header (admin_id, generated,
					category_id_from, category_id_to, 
					barcode_from, barcode_to,  
					account_id, 
					date_from, date_to,promo_price, cdisc, sdisc, 
					include_net,	all_items, customer,
					category_code_from, category_code_to, 
					remark,enable)
				values
					( '".$ADMIN['admin_id']."', '$generated',
					'".$aPD['category_id_from']."', '".$aPD['category_id_to']."',
					'".$aPD['barcode_from']."', '".$aPD['barcode_to']."',
					'".$aPD['account_id']."',
					'".$aPD['date_from']."', '".$aPD['date_to']."', '".$aPD['promo_price']."',
					'".$aPD['cdisc']."', '".$aPD['sdisc']."', '".$aPD['include_net']."', '".$aPD['all_items']."', '".$aPD['customer']."', 
					'".$aPD['category_code_from']."', '".$aPD['category_code_to']."', 
					'".$aPD['remark']."', 'Y')";
		$qr = @pg_query($q) or message(pg_errormessage());
					
	}
	else
	{
		$audit = 'Updated by '.$ADMIN['name'].' on '.date('m/d/Y g:ia').'; ';
		$q = "update promo_header set enable='Y', ";
	
		$fields_header = array('account_id','date_from','date_to','sdisc','cdisc','barcode_from', 'barcode_to', 'all_items', 'customer',
  								'category_id_from','category_id_to','category_code_from','category_code_to','promo_price', 'remark');
		for ($c=0;$c<count($fields_header);$c++)
		{
			if ($c>0) $q .= ',';
			$q .= "$fields_header[$c]='".$aPD[$fields_header[$c]]."'";
		}
		$q .= " where promo_header_id = '".$aPD['promo_header_id']."'";
		$qr = @pg_query($q) or message(pg_errormessage());
	}

  	if ($qr)
	{
		if ($aPD['promo_header_id'] == '')
		{
			$id=pg_insert_id('promo_header');
			$aPD['promo_header_id'] = $id;
		}	
	}
	else
	{
		$ok = 2;
	}

	if ($aPD['promo_header_id'] != '' && $ok == 1)
	{
		$c=0;

		foreach ($aPDD as $temp)
		{
			if ($temp['stock_id'] == '') continue;

			if ($temp['promo_price'] == '') $temp['promo_price'] = 0;
			if ($temp['price1'] == '') $temp['price1'] = 0;

			if ($temp['promo_detail_id'] == '')
			{
				$q = "insert into promo_detail (promo_header_id, stock_id, promo_price, price1)
						values ('".$aPD['promo_header_id']."', '".$temp['stock_id']."',
							'".$temp['promo_price']."', '".$temp['price1']."') ";
					
			}
			else
			{
				$q = "update promo_detail set ";
			
				$q .= "promo_header_id ='".$aPD['promo_header_id']."',
					stock_id='".$temp['stock_id']."',
					promo_price='".$temp['promo_price']."',
					price1 = '".$temp['price1']."'";
				$q .= " where promo_detail_id = '".$temp['promo_detail_id']."'";
			}		
			$qr = @pg_query($q) or message(pg_errormessage().$q);
			
			if ($qr)
			{
				$dummy = $temp;
				if ($temp['promo_detail_id'] == '')
				{
					$id = @pg_insert_id('promo_detail');
					$dummy['promo_detail_id'] = $id;
				}	
				$aPDD[$c]=$dummy;
			}
			elseif (!$qr)
			{
				$ok=3;
				break;
			}
			$c++;			
		}
	}
	else
	{
		$ok=2;
	}
	if ($ok == '1')
	{
		commit();
		audit($module, $q, $ADMIN['admin_id'], $audit, $aPD['promo_header_id']);

  		$aPD['status'] = 'SAVED';
		$aPD['enable'] = 'Y';
		message("Data successfully saved...");
	}
	elseif ($ok == '2')
	{
		rollback();
		message("Unable to update Promo-header...".$q);
	}
	elseif ($ok == '3')
	{
		rollback();
		message("Unable to update Promo-details...");
	}
  }
  elseif ($p1 == 'Delete Checked')
  {
  	$newarray= null;
	$newarray=array();
	$c=0;
  	foreach ($aPDD as $temp)
	{
		$c++;
		if (in_array($c, $delete))
		{
			if ($temp['promo_detail_id'] != '')
			{
				$q = "delete from promo_detail where promo_detail_id = '".$temp['promo_detail_id']."'";
				$qr = @pg_query($q) or message(pg_errormessage());
			}
		}
		else
		{
			$newarray[] = $temp;
		}
	}
	$items = implode(',',$delete);
	$audit = 'Items Deleted by '.$ADMIN['name'].' on '.date('m/d/Y g:ia').':'.$items.'; ';
	audit($module, $q, $ADMIN['admin_id'], $audit, $aPD['promo_header_id']);
	$aPDD = null;
	$aPDD = array();
	$aPDD = $newarray;
  }
  ?>
<script language="JavaScript" type="text/JavaScript">
<!--
function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);
//-->
</script>


<form action="" method="post" name="f1" id="f1">
  <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr bgcolor="#003366"> 
      <td height="21" colspan="2" nowrap background="../graphics/table0_horizontal.PNG"><font color="#FFFFFF" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
        &nbsp;<img src="../graphics/activate.gif"> Product Promo Period</strong></font></td>
      <td width="28%" height="21" align="right" background="../graphics/table0_horizontal.PNG"><strong> 
        <?
			$date=date('Y-m-d');
			if ($aPD['promo_header_id'] == '')
			{
				$status='NEW';
			}
			elseif ($aPD['enable'] == 'N') 
			{
				$status='CANCELLED';
				$bgColor = '#FFCCCC';
			}	
			elseif ($date<=$aPD['date_to'] && $date>=$aPD['date_from']) 
			{
				$status='ON GOING';
				$bgColor = '#66FFFF';
			}	
			elseif ($aPD['date_from'] > $date) 
			{
				$status='UP COMING';
			}	
			elseif ($date>$aPD['date_to'])
			{
				$status = 'DONE';
			}
			echo $status;
	  ?>
        </strong><spacer type="block" width="100"></td>
    </tr>
    <tr> 
      <td width="1%" height="26" valign="bottom"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Supplier 
        </font></td>
      <td nowrap> <input name="account_code" type="hidden" id="account_code" value="<?= $aPD['account_code'];?>" size="10"  onKeypress="if(event.keyCode==13) {document.getElementById('account_id').focus();return false;}" onBlur="f1.action='?p=promo.generate&p1=selectAccountCode';f1.submit()"> 
        <select name='account_id' id='account_id' style="width:250px"  onKeypress="if(event.keyCode==13) {document.getElementById('date_from').focus();return false;}">
          <option value=''>Select Supplier -- </option>
          <?
  		foreach ($aSUPPLIER as $stemp)
		{
			if ($stemp['account_id'] == $aPD['account_id'])
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
      </td>
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Promo 
        Type 
        <input name="promo_type" type="text" id="promo_type"  value="<?= $aPD['promo_type'];?>" size="1" readOnly style="border:none; text-align:right ">
        </font></td>
    </tr>
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> Date From</font></td>
      <td colspan="2"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="date_from" type="text" id="date_from" value="<?= ymd2mdy($aPD['date_from']);?>" size="10" onBlur="IsValidDate(this,'MM/dd/yyyy');vFrom('date')" onKeyUp="setDate(this,'MM/dd/yyyy','en')"  onKeypress="if(event.keyCode==13) {document.getElementById('date_to').focus();return false;}">
        <img src="../graphics/dwn-arrow-grn.gif" onclick="popUpCalendar(this, f1.date_from, 'mm/dd/yyyy')"> 
        <spacer type="block" width="22">To&nbsp; 
        <input name="date_to" type="text" id="date_to" value="<?= ymd2mdy($aPD['date_to']);?>" size="10" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')"  onKeypress="if(event.keyCode==13) {document.getElementById('sdisc').focus();return false;}">
        <img src="../graphics/dwn-arrow-grn.gif" onclick="popUpCalendar(this, f1.date_to, 'mm/dd/yyyy')"> 
        Promo For 
        <?= lookUpAssoc('customer',array('All Customers'=>'A','Reward Members'=>'M'),$aPD['customer']);?>
        <?= looKUpAssoc('include_net', array('Regular Price'=>'N','Net Items'=>'Y','All Items'=>'A'), $aPD['include_net']);?>
        </font></td>
    </tr>
    <tr> 
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">%Discount 
        Supplier</font></td>
      <td colspan="2"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="sdisc" type="text" id="sdisc"  onKeypress="if(event.keyCode==13) {document.getElementById('cdisc').focus();return false;}" value="<?= $aPD['sdisc'];?>" size="5"   onBlur="if (parseInt(this.value)>0){vDisable('sdisc')}">
        Company 
        <input name="cdisc" type="text" id="cdisc"  onKeypress="if(event.keyCode==13) {document.getElementById('promo_price').focus();return false;}" value="<?= $aPD['cdisc'];?>" size="5"   onBlur="if (parseInt(this.value)>0){vDisable('sdisc')}">
        or Promo Price 
        <input name="promo_price" type="text" id="promo_price"  onKeypress="if(event.keyCode==13) {document.getElementById('barcode_from').focus();return false;}" value="<?= $aPD['promo_price'];?>" size="5"   onBlur="if (parseInt(this.value)>0){vDisable('promo_price')}">
        </font><spacer type="BLOCK" width="0"> <font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
        </font> </td>
    </tr>
    <tr> 
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Buy 
        (n) </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="buyn" type="text" id="buyn"  onKeypress="if(event.keyCode==13) {document.getElementById('buym').focus();return false;}" value="<?= $aPD['buyn'];?>" size="5" onBlur="if (parseInt(this.value)>0){vDisable('buynm')}">
        Free (m) &nbsp; 
        <input name="buym" type="text" id="buym"  onKeypress="if(event.keyCode==13) {document.getElementById('barcode_from').focus();return false;}" value="<?= $aPD['buym'];?>" size="5"  onBlur="if (parseInt(this.value)>0){vDisable('buynm')}">
        or Buy (n) 
        <input name="nfor" type="text" id="nfor"  onKeypress="if(event.keyCode==13) {document.getElementById('pricenfor').focus();return false;}" value="<?= $aPD['nfor'];?>" size="5"   onBlur="if (parseInt(this.value)>0){vDisable('nfor')}">
        for the price of 
        <input name="pricenfor" type="text" id="pricenfor"  onKeypress="if(event.keyCode==13) {document.getElementById('barcode_from').focus();return false;}" value="<?= $aPD['pricenfor'];?>" size="7"   onBlur="if (parseInt(this.value)>0){vDisable('nfor')}">
        </font></td>
      <td><input name="p1" type="button" id="go1" value="Go" onClick="f1.action='?p=promo.generate&p1=GenSupplier';f1.submit()"></td>
    </tr>
    <tr> 
      <td colspan="3"><hr size="1" color="#CCCCCC"></td>
    </tr>
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Barcode 
        From</font></td>
      <td nowrap> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="barcode_from" type="text" id="barcode_from" value="<?= $aPD['barcode_from'];?>" size="20" onBlur="vFrom('barcode')" onKeypress="if(event.keyCode==13) {document.getElementById('barcode_to').focus();return false;}">
        To 
        <input name="barcode_to" type="text" id="barcode_to" value="<?= $aPD['barcode_to'];?>" size="20"  onKeypress="if(event.keyCode==13) {document.getElementById('category_id_from').focus();return false;}">
        &nbsp;&nbsp; </font></td>
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="p1" type="button" id="p1" value="Go" onClick="f1.action='?p=promo.generate&p1=GenBarcode';f1.submit()">
        </font></td>
    </tr>
    <tr> 
      <td colspan="3" valign="top"><hr size="1" color="#CCCCCC"></td>
    </tr>
    <tr> 
      <td valign="top"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Category 
        From </font></td>
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="category_code_from" type="hidden" id="category_code_from" value="<?= $aPD['category_code_from'];?>" size="5"  onKeypress="if(event.keyCode==13) {document.getElementById('category_id_from').focus();return false;}"  onBlur="f1.action='?p=promo.generate&p1=selectCategoryCodeFrom';f1.submit()">
        <select name='category_id_from' id="category_id_from" style="width:235px"  onKeypress="if(event.keyCode==13) {document.getElementById('category_id_to').focus();return false;}"  onBlur="vFrom('category_id')">
          <option value=''>All Categories -- </option>
          <?
		foreach ($aCATEGORY as $ctemp)
		{
			if ($ctemp['category_id'] == $aPD['category_id_from'])
			{
				echo "<option value=".$ctemp['category_id']." selected>".substr($ctemp['category_code'],0,6)." ".$ctemp['category']."</option>";
			}
			else
			{
				echo "<option value=".$ctemp['category_id']." >".substr($ctemp['category_code'],0,6)." ".$ctemp['category']."</option>";
			}
		}
		?>
        </select>
        To 
        <input name="category_code_to" type="hidden" id="category_code_to" value="<?= $aPD['category_code_to'];?>" size="5"  onKeypress="if(event.keyCode==13) {document.getElementById('category_id_to').focus();return false;}"  onBlur="f1.action='?p=promo.generate&p1=selectCategoryCodeTo';f1.submit()">
        <select name='category_id_to' id="category_id_to"  style="width:235px"  onKeypress="if(event.keyCode==13) {document.getElementById('remark').focus();return false;}">
          <option value=''>All Categories -- </option>
          <?
		foreach ($aCATEGORY as $ctemp)
		{
			if ($ctemp['category_id'] == $aPD['category_id_to'])
			{
				echo "<option value=".$ctemp['category_id']." selected>".substr($ctemp['category_code'],0,6)." ".$ctemp['category']."</option>";
			}
			else
			{
				echo "<option value=".$ctemp['category_id']." >".substr($ctemp['category_code'],0,6)." ".$ctemp['category']."</option>";
			}
		}
		?>
        </select>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="p1" type="button" id="p1" value="Go"  onClick="f1.action='?p=promo.generate&p1=GenCategory';f1.submit()">
        </font></td>
    </tr>
    <tr> 
      <td colspan="3" valign="top"><hr size="1" color="#CCCCCC"></td>
    </tr>
    <tr> 
      <td valign="top"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Remark 
        </font></td>
      <td colspan="2"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="remark" type="text" id="remark" value="<?= $aPD['remark'];?>" size="60">
        </font></td>
    </tr>
  </table>
  <table width="95%" border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#EFEFEF">
    <tr bgcolor="#CCCCCC"> 
      <td valign="top" ><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Generated 
        Items</strong></font></td>
    </tr>
	<tr><td  height="400px" valign="top" bgcolor="#FFFFFF">
      <div id="Layer1" style="position:virtual; width:100%; height:100%; z-index:1; overflow: auto;">
	<table width="100%" cellpadding="0" cellspacing="0" border="0" >
    <tr bgcolor="#FFFFFF"> 
      <td align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
      <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Barcode</font></strong></td>
      <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Stock 
        Description</font></strong></td>
      <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Net</font></strong></td>
              <td width="14%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">SRP</font></strong></td>
      <td width="19%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Promo<br>
        Price </font></strong></td>
    </tr>
    <?
	if ($aPD['all_items'] == 'Y')
	{
		echo "<tr><td colspan='6'  align='center'>All Items Filtered</td></tr>";
	}
		if ($n == '0') $n=1;
		if ($p1=='mv')
		{
			$start=$n*500 - 500;
		}
		$ctr=0;
		$details = '';
		foreach ($aPDD as $temp)
		{
			$ctr++;
			/*
			$details .= adjustRight($ctr,5).' '.adjustSize($temp['barcode'],16).'  '.adjustSize($temp['stock'],30).'   '.
							adjustRight($temp['price1'],12).'   '.adjustRight($temp['promo_price'],12)."\n";
			
			if ($start > $ctr) continue;
			if ($ctr-$start>500) 
			{
				break;
			}
			*/
	?>
    <tr bgcolor="#FFFFFF"> 
      <td width="8%" align="right" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?=$ctr;?>
        . 
        <input name="delete[]" type="checkbox" id="delete[]" value="<?= $ctr;?>">
        </font></td>
      <td width="14%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= addslashes($temp['barcode']);?>
        </font></td>
      <td width="40%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $temp['stock'];?>
        </font></td>
      <td width="5%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <?= ($temp['netitem']=='Y' ? 'Yes' : 'No');?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
        </font><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= number_format($temp['price1'],2);?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= number_format($temp['promo_price'],2);?>
        </font></td>
    </tr>
    <?
		
	}
	//	echo "<tr><td colspan='6'><pre>$details</pre></td></tr>";
	?>
	</table>
	</div>
	</td>
	</tr>
    <tr bgcolor="#FFFFFF"> 
      <td ><input name="p1" type="submit" value="Delete Checked"> &nbsp;&nbsp;&nbsp;<font size="2" face="Verdana, Arial, Helvetica, sans-serif">
	  <?
	  	$cc= count($aPDD)/500;
		for ($c=1;$c<$cc;$c++)
		{
			echo "<a href='?p=promo.generate&p1=mv&n=$c'> $c </a> | ";
		}
	  ?></font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td  nowrap><table width="1%" border="0" cellpadding="1" cellspacing="1" bgcolor="#CCCCCC">
          <tr bgcolor="#FFFFFF"> 
            <td nowrap width="3%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              <input type="image" src="../graphics/save.jpg" accesskey="S" alt="Save This Form" width="57" height="15" id="Save" onClick="f1.action='?p=promo.generate&p1=Save';f1.submit();" name="Save">
              </strong></font></td>
            <td nowrap width="46%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              <input type="image" src="../graphics/print.jpg" accesskey="P" alt="Print This Claim Form"  onClick="f1.action='?p=promo.generate&p1=Print';f1.submit();" name="Print" id="Print">
              </strong></font></td>
            <td nowrap width="25%"> <input type='image' accesskey="C" name="Cancel" id="Cancel" onClick="if (confirm('Are you sure to CANCEL Entry?')){f1.action='?p=promo.generate&p1=Cancel';f1.submit()};"  src="../graphics/cancel.jpg" alt="Cancel Form" width="77" height="20"> 
            <td nowrap width="25%"> <input type='image' accesskey="N" name="New" id="New" onClick="f1.action='?p=promo.generate&p1=New';f1.submit();"  src="../graphics/new.jpg" alt="New Claim Form" width="63" height="20"> 
            </td>
            <td nowrap width="25%"> <a href="?p=promo"><img src="../graphics/browse.gif" alt="Browse Promo Dates" name="Browse" width="65" height="17" border="0" id="Browse" accesskey="B"></a> 
            </td>
          </tr>
        </table></td>
    </tr>
  </table>
</form>
<?
/*echo "nry ".$aPD['include_net'];
if ($focus != '')
{
	echo "<script>document.getElementById('$focus').focus()</script>";
}
else
{
	echo "<script>document.getElementById('account_id').focus()</script>";
}
*/
?>
