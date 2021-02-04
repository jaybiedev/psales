
  <?
  	if (!session_is_registered('aStockBrowse'))
	{
		session_register('aStockBrowse');
		$aStockBrowse = null;
		$aStockBrowse = array();
	}
	if (!in_array($p1, array('Next','Previous','Sort')))
	{
		$aStockBrowse['search'] = $_REQUEST['search'];
		$aStockBrowse['searchby'] = $_REQUEST['searchby'];
	}   

  	$q = "select * from $stocktable where 1=1";
				
	if ($aStockBrowse['searchby'] == '') $aStockBrowse['searchby'] = 'stock';
	if ($aStockBrowse['search'] != '')
	{
		$q .= " and $searchby ilike '%".$aStockBrowse['search']."%' ";
	}
	$q .= " order by stock ";
	
	if ($p1 == 'Go' or $p1 == '')
	{
		$aStockBrowse['start'] = 0;
	}
	elseif ($p1 == 'Next')
	{
		$aStockBrowse['start'] += 15;
	}
	elseif ($p1 == 'Previous')
	{
		$aStockBrowse['start'] -= 15;
	}
	if ($aStockBrowse['start']<=0) $aStockBrowse['start']=0;
	
	$q .= " offset '".$aStockBrowse['start']."' limit 15 ";

	$qr = @pg_query($q) or message("Error querying stock data...".pg_errormessage().$q);

	if (pg_num_rows($qr) == 0 && $p1!= '') message("stock data [NOT] found...");
?>
<br><br>
<form name="form1" method="post" action="" style="margin:0">
  <table width="80%" border="0" align="center" cellpadding="2" cellspacing="1">
   <tr bgcolor="#CCCCCC"> 
    <td height="20" colspan="5"  background="../graphics/table0_horizontal.PNG"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
      <img src="../graphics/team_wksp.gif" width="16" height="17"> Browse Stocks 
      Information</strong></font></td>
  </tr>
   <tr> 
      <td>Search 
        <input name="search" type="text" id="search" value="<?= $search;?>">
	<?= lookUpAssoc('searchby',array('Item Name'=>'stock','BarCode'=>'barcodeb'),$aStockBrowse['searchby']);?>
        <input name="p1" type="submit" id="p1" value="Go" accesskey="G">
        <input type="button" name="Submit2" value="Add New" onClick="window.location='?p=stockpcenter&p1=New'" accesskey="A">
        <input type="button" name="Submit222" value="Browse" onClick="window.location='?p=stockpcenter.browse&act=All'" accesskey="B">
        <hr color="#CC0000"></td>
    </tr>
  </table>
</form>
<table width="80%" border="0" align="center" cellpadding="2" cellspacing="1" bgcolor="#EFEFEF">
  <tr> 
    <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
    <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Barcode</font></strong></td>
    <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Item 
      Description</font></strong></td>
    <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Price</font></strong></td>
    <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Enable</font></strong></td>
  </tr>  
<?
	$ctr=0;
	while ($r = pg_fetch_object($qr))
	{
		$ctr++;
		if (!in_array($r->stock_type,array('S','N','Y')))
		{
			$href = "?p=stockpcenter&p1=Load&id=$r->stock_id";
		}
		else
		{
			$href = "?p=stockpcenter&p1=Load&id=$r->stock_id";
		}
		if ($r->stock_description == '') $stock_description = $stock;
		else $stock_description = $r->stock;	
  ?>

<tr onMouseOver="bgColor='#FFFFDD'" onMouseOut="bgColor='#FFFFFF'" bgcolor="#FFFFFF"> 
    <td width="6%" align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= $ctr;?>
      .</font></td>
    <td width="21%""><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <a href="<?=$href;?>"> 
      <?= $r->barcode;?>
      </a> </font></td>
    <td width="41%""><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="<?=$href;?>">
      <?= $stock_description;?>
      </a></font></td>
    <td width="18%" align="right""><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= number_format($r->price1,2);?>
      </font></td>
    <td width="14%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?=($r->enable=='Y' ? 'Enabled' : 'Disabled' );?>
      </font></td>
  </tr>
  <?
  }
  ?>
  <tr> 
    <td colspan="5" bgcolor="#FFFFFF"><input type="button" name="Submit22" value="Add New" onClick="window.location='?p=stockpcenter&p1=New'"> 
      <input type="button" name="Submit232" value="Close" onClick="window.location='?p='"></td>
  </tr>
</table>

<div align="center"> <a href="?p=stockpcenter.browse&p1=Previous&start=<?=$start;?>&search=<?=$search;?>&searchby=<?=$searchby;?>"><img src="../graphics/redarrow_left.gif" width="4" height="7" border="0"></a> 
  <a href="?p=stockpcenter.browse&p1=Previous&start=<?=$start;?>&search=<?=$search;?>&searchby=<?=$searchby;?>"> 
  Previous</a> | <a href="?p=stockpcenter.browse&p1=Next&start=<?=$start;?>&search=<?=$search;?>&searchby=<?=$searchby;?>">Next</a> 
  <a href="?p=stockpcenter.browse&p1=Next&start=<?=$start;?>&search=<?=$search;?>&searchby=<?=$searchby;?>"><img src="../graphics/redarrow_right.gif" width="4" height="7" border="0"></a> 
</div>
