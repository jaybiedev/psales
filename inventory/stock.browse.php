<?
if (!session_is_registered('astock'))
{
	session_register('astock');
	$astock = null;
	$astock = array();
}

if (!session_is_registered('aStockBrowse'))
{
	session_register('aStockBrowse');
	$aStockBrowse = null;
	$aStockBrowse = array();
}
if (in_array($p1, array('Browse','Go','Next','Previous')))
{

	$aStockBrowse['search'] = $_REQUEST['search'];
	$aStockBrowse['searchby'] = $_REQUEST['searchby'];
	$aStockBrowse['mcategory_id'] = $_REQUEST['mcategory_id'];

}
?>

<form name="f1" id="f1" method="post" action="">
  <table width="95%" border="0" align="center" cellpadding="2" cellspacing="1">
    <tr> 
      <td>Search 
        <input name="search" type="text" id="search" value="<?=  $aStockBrowse['search'];?>" onKeyUp="if(event.keyCode == 13){document.getElementById('go').click();}">
		<?= lookUpAssoc('searchby',array('Bar Code'=>'barcode','Item Name'=>'stock','Item 
Code'=>'stock_code','Description'=>'stock_description'), $aStockBrowse['searchby']);?>
        <select name="mcategory_id"   style="border: #CCCCCC 1px solid; width:200px">
          <option value=''>--Select Category--</option>
          <option value='0' <?= ($mcategory_id == '0' ? 'selected' : '');?>>No Category Items Only</option>
          <?
		foreach ($aCATEGORY as $ctemp)
		{
			if ($ctemp['category_id'] == $aStockBrowse['mcategory_id'])
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
        <input name="p1" type="submit" id="go" value="Go">
        <input type="button" name="Submit2" value="Add New" onClick="window.location='?p=stock&p1=New'">
        <input type="submit" name="p1" value="Previous">
        <input type="submit" name="p1" value="Next">
        <input type="button" name="Submit233" value="Close" onClick="window.location='?p='"> 
        <hr color="#CC0000"></td>
    </tr>
  </table>
  <table width="95%" border="0" align="center" cellpadding="2" cellspacing="1" bgcolor="#EFEFEF">
    <tr bgcolor="#CCCCCC"> 
      <td height="20" colspan="8" background="../graphics/table0_horizontal.PNG"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
        <img src="../graphics/storage.gif" width="16" height="17"> Browse Stock Information</strong></font></td>
    </tr>
    <tr> 
      <td align="center"><strong><font size="2" face="Geneva, Arial, Helvetica, san-serif">#</font></strong></td>
      <td><strong><font size="2" face="Geneva, Arial, Helvetica, san-serif"><a href="?p=stock.browse&p1=Go&sortby=barcode&start=<?=$start;?>&search=<?=$search;?>&searchby=<?=$searchby;?>">Bar 
        Code </a></font></strong></td>
      <td><strong><font size="2" face="Geneva, Arial, Helvetica, san-serif"><a href="?p=stock.browse&p1=Go&sortby=stock&start=<?=$start;?>&search=<?=$search;?>&searchby=<?=$searchby;?>">Description</a></font></strong></td>
      <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=stock.browse&p1=Go&sortby=unit&start=<?=$start;?>&search=<?=$search;?>&searchby=<?=$searchby;?>">Unit</a></font></strong></td>
      <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=stock.browse&p1=Go&sortby=category_id&start=<?=$start;?>&search=<?=$search;?>&searchby=<?=$searchby;?>">Category</a></font></strong></td>
      <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Price</font></strong></td>
      <td width="1%">&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <?
  	if ($p1 == 'Delete Checked')
	{
		for ($c=0;$c<count($delete);$c++)
		{
			$q = "select * from stockledger where stock_id='".$delete[$c]."'";
			$qr = pg_query($q);
			if (pg_num_rows($qr) == 0)
			{
				$q = "delete from stock where stock_id='".$delete[$c]."'";
				pg_query($q);
			}	
		}
		$p1='Go';
	}
	
	$searchby = $aStockBrowse['searchby'];
	$search = $aStockBrowse['search'];
	$start = $aStockBrowse['start'];
	$mcategory_id = $aStockBrowse['mcategory_id'];
	
	$msearchby = 'upper('.$searchby.')';

  	$q = "select * from stock where true ";
  	
  	if ($search != '')
  	{
		if ($searchby == 'barcode')
		{
			$msearch = strtoupper(readBarcode($search));
			$q .= " and barcode = '$msearch' ";
		}
		else
		{
			$msearch = strtoupper($search);
			$q .= " and $msearchby like '$msearch%' ";
		}
	}
	if ($mcategory_id != '')
	{
		$q .= " and category_id='$mcategory_id'";
	}
	if ($sortby == '')
		$q .= " order by barcode ";
	else
		$q .= " order by $sortby";
			
			


	if ($p1 == 'Go' or $p1 == '')
	{
		$start = 0;
	}
	elseif ($p1 == 'Next')
	{
		$start += 15;

	}
	elseif ($p1 == 'Previous')
	{
		$start -= 15;
	}
	if ($start<0) $start=0;
	
	$q .= " offset $start limit 15 ";

	$aStockBrowse['start'] = $start;

	$qr = @pg_query($q) or message("Error querying stock data...".pg_errormessage().$q);

	if (@pg_num_rows($qr) == 0 && $p1!= '') 
	{
		message("Stock/Product data [NOT] found...");
		$aStockBrowse['start'] = 0;
	}
	elseif (@pg_num_rows($qr) == 1 && $p1 == 'Go')
	{
		$astock = null;
		$astock = array();

		$r = @pg_fetch_assoc($qr);

		$astock = $r;
		if ($astock['unit1'] == '') $astock['unit1']='PC';

  		@include_once('stockbalance.php');
	
		$stkled = @stockBalance($astock['stock_id'],'', date('Y-m-d'));
		$astock['balance_qty'] = $stkled['balance_qty'];

		echo "<script>window.location='?p=stock&p1=LoadDone&focus=stock'</script>";
		exit;
	}
	$ctr=0;
	while ($r = pg_fetch_object($qr))
	{
		$ctr++;
		$bgColor='#FFFFFF';
		if ($r->enable=='N') $bgColor='FFD2D2';
		$in_qty = $out_qty = 0;
		$balance_qty = $in_qty - $out_qty;		

		if ($r->stock_description == '') 
		{
			$stock=$r->stock;
		}
		else
		{
			$stock = $r->stock_description;
		}
  ?>
    <tr onMouseOver="bgColor='#FFFFDD'" onMouseOut="bgColor='<?= $bgColor;?>'" bgcolor="<?= $bgColor;?>"> 
      <td width="5%" align="right" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $ctr;?>
        . 
        <input type="checkbox" name="delete[]" value="<?= $r->stock_id;?>">
        </font></td>
      <td width="7%""><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <a href="?p=stock&p1=Load&id=<?= $r->stock_id;?>"> 
        <?= $r->barcode;?>
        </a> </font></td>
      <td width="25%""><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=stock&p1=Load&id=<?= $r->stock_id;?>"> 
        <?= $stock;?>
        </a></font></td>
      <td width="2%""><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=stock&p1=Load&id=<?= $r->stock_id;?>"> 
        <?= $r->unit1;?>
        </a></font></td>
      <td width="20%""><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?=lookUpTableReturnValue('x','category','category_id','category',$r->category_id);?>
        </font></td>
      <td width="10%" align="right""> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"><?= number_format($r->price1,2);?> 
        </font></td>
      <td nowrap><a href="?p=report.bincard&p1=selectStock&c_id=<?=$r->stock_id;?>"><img src="../graphics/list3.gif" alt="View BinCard" width="16" height="17" border="0"></a> 
        <a href="?p=stock.browse&p1=viewsupplier&id=<?=$r->stock_id;?>"><img src="../graphics/people_search.gif" alt="View Suppliers" width="16" height="16" border="0"></a> 
      </td>
      <td width="1%" align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= number_format($balance_qty,0)//($r->enable=='Y' ? 'Enabled' : 'Disabled' );?>
        </font></td>
    </tr>
  	<?
		if ($p1 == 'viewsupplier' && $r->stock_id == $id)
		{
			$q = "select distinct(rr_header.supplier_id) as supplier_id, 
						supplier, cost,
						cunit,
						rr_header.rr_header_id
					 from 
					 	rr_detail, 
						rr_header,
						supplier
					where
						rr_header.rr_header_id=rr_detail.rr_header_id and
						supplier.supplier_id=rr_header.supplier_id and
						rr_detail.stock_id='$id'
					order by
						rr_header.supplier_id";
			$qqr = @pg_query($q) or message(pg_errormessage());
			$sid='';		
			while ($rr = pg_fetch_object($qqr))
			{
				if ($rr->supplier_id == $sid) continue;
				$sid = $rr->supplier_id;
				if ($rr->rr_header_id <= 1)	 continue;
				echo "<tr>";
				echo "<td colspan='2' align='right'><font size='2' face='Verdana, Arial, Helvetica, sans-serif' color='#993300'>
						Supplier: </font></td><td>
						<font size='2' face='Verdana, Arial, Helvetica, sans-serif' color='#993300'>
						 $rr->supplier</font></td>
						 <td>
						<font size='2' face='Verdana, Arial, Helvetica, sans-serif' color='#993300'>
						 $rr->cunit</font></td>
						 <td>
						<font size='2' face='Verdana, Arial, Helvetica, sans-serif' color='#993300'>".
						 number_format($rr->cost,2)."</font></td>";
				echo "</tr>";
			}
		}
  }
  ?>
    <tr> 
      <td colspan="8" bgcolor="#FFFFFF"><input type="button" name="Submit22" value="Add New" onClick="window.location='?p=stock&p1=New'"> 
        <input type="submit" name="p1" value="Save Checked"> </td>
    </tr>
  </table>
</form>

<div align="center"> <a href="javascript: document.getElementById('f1').action='?p=stock.browse&p1=Previous';document.getElementById('f1').submit()"><img src="../graphics/redarrow_left.gif" width="4" height="7" border="0"></a> 
  <a href="javascript: document.getElementById('f1').action='?p=stock.browse&p1=Previous';document.getElementById('f1').submit()"> 
  Previous</a> | <a href="javascript: document.getElementById('f1').action='?p=stock.browse&p1=Next';document.getElementById('f1').submit()">Next</a> 
  <a href="javascript: document.getElementById('f1').action='?p=stock.browse&p1=Next';document.getElementById('f1').submit()"><img src="../graphics/redarrow_right.gif" width="4" height="7" border="0"></a> 
</div>
