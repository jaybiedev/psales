<?
if (!chkRights2("phycount","mview",$ADMIN['admin_id']))
{
	message("You have no permission in this area (Physical Count)...");
	exit;
}

if ($year == '') 
{
	if (date('m') == '01')
	{
		$year = date('Y')-1;
	}
	else
	{
		$year = date('Y');
	}
}
?>
<form name="form1" method="post" action="">
  <table width="80%" border="0" align="center" cellpadding="2" cellspacing="1">
    <tr> 
      <td> Inventory Peiod Year <input type="text" size="5" name="year" id="year" value="<?= $year;?>">
        <input name="p1" type="submit" id="p1" value="Go">
        <input type="button" name="Submit2" value="Add New" onClick="window.location='?p=phycount&p1=New'">
        <input type="button" name="Submit23" value="Close" onClick="window.location='?p='">
        <hr color="#CC0000"></td>
    </tr>
  </table>
  <table width="80%" border="0" align="center" cellpadding="2" cellspacing="1" bgcolor="#EFEFEF">
    <tr bgcolor="#CCCCCC"> 
      <td height="20" colspan="5" background="../graphics/table0_horizontal.PNG"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
        <img src="../graphics/storage.gif" width="16" height="17"> Browse Physical 
        Count Entries</strong></font></td>
    </tr>
    <tr> 
      <td align="center"><strong><font size="2" face="Geneva, Arial, Helvetica, san-serif">#</font></strong></td>
      <td><strong><font size="2" face="Geneva, Arial, Helvetica, san-serif"><a href="?p=phycount.browse&p1=Go&sortby=date desc&start=<?=$start;?>&search=<?=$search;?>&searchby=<?=$searchby;?>">Date</a></font></strong></td>
      <td><strong><font size="2" face="Geneva, Arial, Helvetica, san-serif"><a href="?p=phycount.browse&p1=Go&sortby=date,account_id desc&start=<?=$start;?>&search=<?=$search;?>&searchby=<?=$searchby;?>">Supplier</a></font></strong></td>
      <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=phycount.browse&p1=Go&sortby=date, admin_id desc &start=<?=$start;?>&search=<?=$search;?>&searchby=<?=$searchby;?>">User</a></font></strong></td>
      <td width="27%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=phycount.browse&p1=Go&sortby=date, enable desc&start=<?=$start;?>&search=<?=$search;?>&searchby=<?=$searchby;?>">Enable</a></font></strong><strong></strong></td>
    </tr>
	<?
	
	$mdate = $year.'-06-01';

	$tables = currTables($mdate);
	$sales_header = $tables['sales_header'];
	$sales_detail = $tables['sales_detail'];
	$sales_tender = $tables['sales_tender'];
	$stockledger = $tables['stockledger'];
	
	$msearchby = 'upper('.$searchby.')';
	$msearch = strtoupper($search);

  	$q = "select 
					distinct sl.date, stock.account_id, 
					sl.date,
					stock.account_id, 
					sl.admin_id, 
					sl.enable
				from 
					$stockledger as sl,
					stock
				where
					stock.stock_id=sl.stock_id and 
					sl.type='E'";  		// -- type 'C' Computer , E 'Encoded'
	if ($search != '' && $searchby == 'barcode')
	{
		$q .= " and $searchby like '$search%' ";
	}
	elseif ($search != '')
	{
		$q .= " and $msearchby like '$msearch%' ";
	}
	if ($sortby == '')
		$q .= " order by date, stock.account_id desc  ";
	else
		$q .= " order by $sortby";
			
	if ($p1 == 'Go' or $p1 == '' or $start=='')
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

	$qr = @pg_query($q) or message("Error querying stock data...".pg_errormessage().$q);

	if (@pg_num_rows($qr) == 0 && $p1!= '') message("stock data [NOT] found...");
	$ctr=0;
	while ($r = pg_fetch_object($qr))
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
        <a href="?p=phycount&p1=Load&date=<?= $r->date;?>&account_id=<?= $r->account_id;?>"> 
        <?= ymd2mdy($r->date);?>
        </a> </font></td>
      <td width="42%""><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <a href="?p=phycount&p1=Load&date=<?= $r->date;?>&account_id=<?= $r->account_id;?>"> 
        <?= lookUpTableReturnValue('x','account','account_id','account',$r->account_id);?>
        </a></font></td>
      <td width="15%""><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <a href="?p=phycount&p1=Load&date=<?= $r->date;?>&account_id=<?= $r->account_id;?>"> 
        <?= lookUpTableReturnValue('x','admin','admin_id','name',$r->admin_id);?>
        </a></font></td>
		<td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <a href="?p=phycount&p1=Load&date=<?= $r->date;?>&account_id=<?= $r->account_id;?>"> 
      <?= $r->enable;?>
      </a></font></td>
	</tr>
    <?
	}
	?>
	
    <tr> 
      <td colspan="5" bgcolor="#FFFFFF"><input type="button" name="Submit22" value="Add New" onClick="window.location='?p=phycount&p1=New'"> 
        <input type="submit" name="p1" value="Save Checked"> </td>
    </tr>
  </table>
</form>

<div align="center"> <a 
href="?p=phycount.browse&p1=Previous&sortby=<?=$sortby;?>&category_id=<?=$category_id;?>&start=<?=$start;?>&search=<?=$search;?>&searchby=<?=$searchby;?>"><img src="../graphics/redarrow_left.gif" width="4" height="7" border="0"></a> 
  <a href="?p=phycount.browse&p1=Previous&sortby=<?=$sortby;?>&category_id=<?=$category_id;?>&start=<?=$start;?>&search=<?=$search;?>&searchby=<?=$searchby;?>"> 
  Previous</a> | <a href="?p=phycount.browse&p1=Next&sortby=<?=$sortby;?>&category_id=<?=$category_id;?>&start=<?=$start;?>&search=<?=$search;?>&searchby=<?=$searchby;?>">Next</a> 
  <a href="?p=phycount.browse&p1=Next&sortby=<?=$sortby;?>&category_id=<?=$category_id;?>&start=<?=$start;?>&search=<?=$search;?>&searchby=<?=$searchby;?>"><img src="../graphics/redarrow_right.gif" width="4" height="7" border="0"></a> 
</div>
