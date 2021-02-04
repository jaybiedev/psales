<form name="form1" method="post" action="">
  <table width="95%" border="0" align="center" cellpadding="2" cellspacing="1">
    <tr> 
      <td> Query Product By Supplier 
        <select name='account_id' id='account_id' style="width:300px">
          <option value=''>Select Supplier -- </option>
          <?
		$q = "select account_id, account, account_code 
					from 
						account, 
						account_type 
					where 
						account.account_type_id=account_type.account_type_id and 
						account_type.account_type_code='S' 
					order by 
						account_code, account ";
						
		$qr = @pg_query($q);
		while ($r = @pg_fetch_object($qr))
		{
			if ($account_id == $r->account_id)
			{
				echo "<option value = $r->account_id selected>".substr($r->account_code,0,7)."  $r->account</option>";
			}
			else
			{
				echo "<option value = $r->account_id>".substr($r->account_code,0,7)."  $r->account</option>";
			}	
		}
		?>
        </select>
        <input name="p1" type="submit" id="p1" value="Go">
        <input type="button" name="Submit23" value="Close" onClick="window.location='?p='">
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
      <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Net 
        Price </font></strong></td>
      <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Enable</font></strong></td>
    </tr>
    <?
  	if ($p1 == 'Disable')
	{
		for ($c=0;$c<count($delete);$c++)
		{
				$q = "update stock set enable='N'  where stock_id='".$delete[$c]."'";
				pg_query($q);
		}
	}
  	elseif ($p1 == 'unnet')
	{
		for ($c=0;$c<count($delete);$c++)
		{
				$q = "update stock set netitem='N'  where stock_id='".$delete[$c]."'";
				pg_query($q);
		}
	}
  	elseif ($p1 == 'net')
	{
		for ($c=0;$c<count($delete);$c++)
		{
				$q = "update stock set netitem='Y'  where stock_id='".$delete[$c]."'";
				pg_query($q);
		}
	}
	if ($account_id != '')
	{
	 	$q = "select * from stock where account_id='$account_id' order by barcode";
		$qr = @pg_query($q) or message("Error querying stock data...".pg_errormessage().$q);
	}

	$ctr=0;
	while ($r = @pg_fetch_object($qr))
	{
		$ctr++;
		$bgColor='#FFFFFF';
		if ($r->enable=='N') $bgColor='FFD2D2';
		$in_qty = $out_qty = 0;
		$balance_qty = $in_qty - $out_qty;		
  ?>
    <tr onMouseOver="bgColor='#FFFFDD'" onMouseOut="bgColor='<?= $bgColor;?>'" bgcolor="<?= $bgColor;?>"> 
      <td width="7%" align="right" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $ctr;?>
        . 
        <input type="checkbox" name="delete[]" value="<?= $r->stock_id;?>">
        </font></td>
      <td width="9%""><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <a href="?p=stock&p1=Load&id=<?= $r->stock_id;?>"> 
        <?= $r->barcode;?>
        </a> </font></td>
      <td width="42%""><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=stock&p1=Load&id=<?= $r->stock_id;?>"> 
        <?= $r->stock;?>
        </a></font></td>
      <td width="7%""><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=stock&p1=Load&id=<?= $r->stock_id;?>"> 
        <?= $r->unit1;?>
        </a></font></td>
      <td width="13%""><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?=lookUpTableReturnValue('x','category','category_id','category',$r->category_id);?>
        </font></td>
      <td width="12%" align="right""> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"><?= number_format($r->price1,2);?> 
        </font></td>
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <?= ($r->netitem=='Y' ? 'Yes' : 'No' );?>
        </font></td>
      <td width="5%" align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= ($r->enable=='Y' ? 'Enabled' : 'Disabled' );?>
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
      <td colspan="8" bgcolor="#FFFFFF">With Checked 
        <?= lookUpAssoc('p1',array('Tag as Net Price'=>'net','Tag as Regular Price'=>'unnet','Disable Checked'=>'disable'), $p1);?>
        <input type="submit" name="Submit" value="Go"></td>
    </tr>
  </table>
</form>
