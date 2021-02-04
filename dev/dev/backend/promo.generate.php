  <?
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
  
  $fields_header = array('account_id','date_from','date_to','sdisc','cdisc','barcode','category_id','classification_id','promo_price', 'remark');
  if (!in_array($p1,array('','Delete Checked','New','Print','Load')))
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

			if ($aPD[$fields_header[$c]] == '' && in_array($fields_header[$c],array('category_id','classification_id','promo_price')))
			{
				$aPD[$fields_header[$c]] = 0;
			}

		}
	}
  }
  if ($p1 == 'Cancel')
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
		$q = "select stock, barcode from stock where stock_id='".$r['stock_id']."'";
		$qqr = @pg_query($q) or message(pg_errormessage());
		$r += @pg_fetch_assoc($qqr);
		$aPDD[] = $r;
	}

  }
  elseif ($p1 == 'Generate Promo' && $aPD['account_id'] == '')
  {
  	message('Specify Supplier Please...');
  }
  elseif ($p1 == 'Generate Promo' && $aPD['sdisc'] == '' && $aPD['cdisc'] == '')
  {
  	message('Specify Discount Please...');
  }
  elseif ($p1 == 'Generate Promo' && ($aPD['date_from'] == '--' || $aPD['date_to'] == '--'))
  {
  	message('Specify Promo [From] and [To] Dates Please...');
  }
  elseif ($p1 == 'Generate Promo')
  {
  		$aPD['status'] = 'MODIFY';
		$q = "select * 
				from 
					stock 
				where
					enable = 'Y' and
					account_id='".$aPD['account_id']."'";

		if ($aPD['barcode'] != '')
		{
			$q .= " and barcode='".$aPD['barcode']."'";
		}	
		if ($aPD['category_id'] != '')
		{
			$q .= " and category_id='".$aPD['category_id']."'";
		}
		if ($aPD['classification_id'] != '')
		{
			$q .= " and classification_id='".$aPD['classification_id']."'";
		}
		$q .= " order by stock ";
		
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
  elseif ($p1 == 'Save' && ($aPD['account_id'] == '' or count($aPDD) == 0))
  {
  	message("Nothing to SAVE...");
  }
  elseif ($p1 == 'Save')
  {
  	$ok=1;
  	if ($aPD['promo_header_id'] == '')
	{
		$generated=date('Y-m-d');
		$audit = 'Encoded by '.$ADMIN['name'].' on '.date('m/d/Y g:ia').'; ';
		$q =  "insert into promo_header (admin_id, generated,
					category_id, classification_id, account_id, 
					date_from, date_to,promo_price, cdisc, sdisc, enable)
				values
					( '".$ADMIN['admin_id']."', '$generated',
					'".$aPD['category_id']."', '".$aPD['classification_id']."','".$aPD['account_id']."',
					'".$aPD['date_from']."', '".$aPD['date_to']."', '".$aPD['promo_price']."',
					'".$aPD['cdisc']."', '".$aPD['sdisc']."', 'Y')";
					
	}
	else
	{
		$audit = 'Updated by '.$ADMIN['name'].' on '.date('m/d/Y g:ia').'; ';
		$q = "update promo_header set enable='Y', ";
	
		for ($c=0;$c<count($fields_header);$c++)
		{
			if ($c>0) $q .= ',';
			$q .= "$fields_header[$c]='".$aPD[$fields_header[$c]]."'";
		}
		$q .= " where promo_header_id = '".$aPD['promo_header_id']."'";
	}

	$qr = @pg_query($q) or message(pg_errormessage());
  	if ($qr && $aPD['promo_header_id'] == '')
	{
		$id=pg_insert_id('promo_header');
		$aPD['promo_header_id'] = $id;
	}

	if ($aPD['promo_header_id'] != '')
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

			
			if ($qr && $temp['promo_detail_id'] == '')
			{
				$id = @pg_insert_id('promo_detail');
				$dummy = $temp;
				$dummy['promo_detail_id'] = $id;
				$aPDD[$c]=$dummy;
			}
			elseif (!$qr)
			{
				$ok=3;
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
		audit($module, $q, $ADMIN['admin_id'], $audit, $aPD['promo_header_id']);

  		$aPD['status'] = 'SAVED';
		$aPD['enable'] = 'Y';
		message("Data successfully saved...");
	}
	elseif ($ok == '2')
	{
		message("Unable to update promo-header...");
	}
	elseif ($ok == '3')
	{
		message("Unable to update promo-details...");
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

<form action="" method="post" name="f1" id="f1">
  <table width="80%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr> 
      <td height="21" colspan="3" background="graphics/table0_horizontal.PNG"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
        Product Promo Period</strong></font></td>
    </tr>
    <tr> 
      <td width="15%" valign="bottom"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Supplier 
        </font></td>
      <td width="42%"> <select name='account_id'>
          <option value=''>Select Supplier -- </option>
          <?
		$q = "select * from account, account_type where account.account_type=account_type.account_type and account_type.class='S' order by account ";
		$qr = @pg_query($q);
		while ($r = @pg_fetch_object($qr))
		{
			if ($aPD['account_id'] == $r->account_id)
			{
				echo "<option value = $r->account_id selected>$r->account</option>";
			}
			else
			{
				echo "<option value = $r->account_id>$r->account</option>";
			}	
		}
		?>
        </select> </td>
      <td width="43%" align="center"> <strong> 
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
        </strong></td>
    </tr>
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> Date From</font></td>
      <td colspan="2"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="date_from" type="text" id="date_from" value="<?= ymd2mdy($aPD['date_from']);?>" size="8" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')">
        <img src="graphics/dwn-arrow-grn.gif" onclick="popUpCalendar(this, f1.date_from, 'mm/dd/yyyy')"> 
        To To 
        <input name="date_to" type="text" id="date_to" value="<?= ymd2mdy($aPD['date_to']);?>" size="8" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')">
        <img src="graphics/dwn-arrow-grn.gif" onclick="popUpCalendar(this, f1.date_to, 'mm/dd/yyyy')"></font></td>
    </tr>
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">%Supplier 
        Discount</font></td>
      <td colspan="2"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="sdisc" type="text" value="<?= $aPD['sdisc'];?>" size="5">
        %Company Discount 
        <input name="cdisc" type="text" value="<?= $aPD['cdisc'];?>" size="5">
        or Promo Price 
        <input name="promo_price" type="text" value="<?= $aPD['promo_price'];?>" size="5">
        </font></td>
    </tr>
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Barcode 
        </font></td>
      <td colspan="2"> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="barcode" type="text" id="barcode" value="<?= $aPD['barcode'];?>">
        or 
        <select name='category_id'>
          <option value=''>All Categories -- </option>
          <?
		$q = "select * from category order by category";
		$qr = @pg_query($q);
		while ($r = @pg_fetch_object($qr))
		{
			if ($aPD['category_id'] == $r->category_id)
			{
				echo "<option value = $r->category_id selected>$r->category</option>";
			}
			else
			{
				echo "<option value = $r->category_id>$r->category</option>";
			}	
		}
		?>
        </select>
        or 
        <select name='classification_id'>
          <option value=''>All Classifications -- </option>
          <?
		$q = "select * from classification order by classification";
		$qr = @pg_query($q);
		while ($r = @pg_fetch_object($qr))
		{
			if ($aPD['classification_id'] == $r->classification_id)
			{
				echo "<option value = $r->classification_id selected>$r->classification</option>";
			}
			else
			{
				echo "<option value = $r->classification_id>$r->classification</option>";
			}	
		}
		?>
        </select>
        </font></td>
    </tr>
    <tr> 
      <td valign="top"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Remark 
        </font></td>
      <td colspan="2"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <textarea name="remark" cols="60" rows="2" id="remark"><?= $aPD['remark'];?></textarea>
        </font></td>
    </tr>
    <tr> 
      <td colspan="3"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="p1" type="submit" id="p1" value="Generate Promo">
        <input name="p12" type="button" id="p13" value="Browse" onClick="window.location='?p=promo'">
        </font></td>
    </tr>
    <tr> 
      <td colspan="3"><hr></td>
    </tr>
  </table>
  <table width="80%" border="0" align="center" cellpadding="1" cellspacing="1" bgcolor="#EFEFEF">
    <tr bgcolor="#CCCCCC"> 
      <td colspan="5"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Generated 
        Items</strong></font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
      <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Barcode</font></strong></td>
      <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Stock 
        Description</font></strong></td>
      <td width="14%" align="center"><strong></strong><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">SRP</font></strong></td>
      <td width="19%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Promo<br>
        Price </font></strong></td>
    </tr>
    <?
		$ctr=0;
		foreach ($aPDD as $temp)
		{
			$ctr++;
	?>
    <tr bgcolor="#FFFFFF"> 
      <td width="8%" align="right" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?=$ctr;?>
        . 
        <input name="delete[]" type="checkbox" id="delete[]" value="<?= $ctr;?>">
        </font></td>
      <td width="14%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $temp['barcode'];?>
        </font></td>
      <td width="45%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $temp['stock'];?>
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
	?>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="5"><input name="p1" type="submit" value="Delete Checked"></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="5" nowrap><table width="1%" border="0" cellpadding="1" cellspacing="1" bgcolor="#CCCCCC">
          <tr bgcolor="#FFFFFF"> 
            <td nowrap width="3%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              <input type="image" src="graphics/save.jpg" alt="Save This Form" width="57" height="15" id="Save" onClick="f1.action='?p=promo.generate&p1=Save';f1.submit();" name="Save">
              </strong></font></td>
            <td nowrap width="46%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              <input type="image" src="graphics/print.jpg" alt="Print This Claim Form"  onClick="f1.action='?p=promo.generate&p1=Print';f1.submit();" name="Print" id="Print">
              </strong></font></td>
            <!--<td nowrap width="26%"> <input name="Cancel" id="Cancel" type="image" src="graphics/cancel.jpg" onClick="vSubmit(this)" alt="Cancel This Entry" width="77" height="20"></td>-->
            <td nowrap width="25%"> <input type='image' name="Cancel" id="Cancel" onClick="if (confirm('Are you sure to CANCEL Entry?')){f1.action='?p=promo.generate&p1=Cancel';f1.submit()};"  src="graphics/cancel.jpg" alt="Cancel Form" width="77" height="20"> 
            <td nowrap width="25%"> <input type='image' name="New" id="New" onClick="f1.action='?p=promo.generate&p1=New';f1.submit();"  src="graphics/new.jpg" alt="New Claim Form" width="63" height="20"> 
            </td>
          </tr>
        </table></td>
    </tr>
  </table>
</form>
