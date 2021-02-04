<STYLE TYPE="text/css">
<!--
	.grid {
	font-family: verdana;
	border: #CCCCCC 1px solid;
	font-size: 12px;
	color: #000000
	} 


	.altText {
	background-color: #FFFFFF;
	font-family: verdana;
	border: #CCCCCC 1px solid;
	font-size: 12px;
	color: #000000
	} 
	.autocomplete {
	font-family: verdana;
	border: #CCCCCC 1px solid;
	font-size: 10px;
	color: #000000;
	
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
elseif ($p1 == 'Cancel Checked' && !chkRights2("phycount","mdelete",$ADMIN['admin_id']))
{
	message("You have no permission in this area (Physical Count)...");
	exit;
}
elseif ($p1 == 'cancelConfirm')
{
	$apid = implode("','",$mark);
	$q = "update phycount set enable='N' where phycount_id in ('$apid')";
	$qr = @pg_query($q) or message(pg_errormessage());
	if ($qr)
	{
		message(" Cancelled ".@pg_affected_rows($qr)." Record(s)...");
	}
}

?>
<form name="f1"  id="f1" method="post" action="">
  <table width="95%" border="0" align="center" cellpadding="2" cellspacing="1">
    <tr> 
      <td> Inventory Period Year 
        <input type="text" size="5" name="year" id="year" value="<?= $year;?>">
        <input name="p1" type="submit" id="p1" value="Go"  class="altBtn">
        <input type="button" name="Submit2" value="Add New" onClick="window.location='?p=phycount&p1=New'" onmouseover="showToolTip(event,'Add NEW Physical Count Schedule..');return false" onmouseout="hideToolTip()" class="altBtn">
        <input type="button" name="Submit23" value="Close" onClick="window.location='?p='" class="altBtn">
        <hr color="#CC0000"></td>
    </tr>
  </table>
  <table width="95%" border="0" align="center" cellpadding="2" cellspacing="1" bgcolor="#EFEFEF">
    <tr bgcolor="#CCCCCC"> 
      <td height="20" colspan="7" background="../graphics/table0_horizontal.PNG"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
        <img src="../graphics/storage.gif" width="16" height="17"> Browse Physical 
        Count Entries</strong></font></td>
    </tr>
    <tr> 
      <td align="center"><strong><font size="2" face="Geneva, Arial, Helvetica, san-serif">#</font></strong></td>
      <td width="6%"><strong><font size="2" face="Geneva, Arial, Helvetica, san-serif"><a href="?p=phycount.browse&p1=Go&sortby=date desc&start=<?=$start;?>&search=<?=$search;?>&searchby=<?=$searchby;?>">RecId</a></font></strong></td>
      <td width="7%"><strong><font size="2" face="Geneva, Arial, Helvetica, san-serif"><a href="?p=phycount.browse&p1=Go&sortby=date desc&start=<?=$start;?>&search=<?=$search;?>&searchby=<?=$searchby;?>">Date</a></font></strong><strong></strong></td>
      <td width="29%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=phycount.browse&p1=Go&sortby=date, admin_id desc &start=<?=$start;?>&search=<?=$search;?>&searchby=<?=$searchby;?>">Supplier</a></font></strong></td>
      <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=phycount.browse&p1=Go&sortby=date, admin_id desc &start=<?=$start;?>&search=<?=$search;?>&searchby=<?=$searchby;?>">User</a></font></strong></td>
      <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=phycount.browse&p1=Go&sortby=type, admin_id desc &start=<?=$start;?>&search=<?=$search;?>&searchby=<?=$searchby;?>">Type</a></font></strong></td>
      <td width="13%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=phycount.browse&p1=Go&sortby=date, enable desc&start=<?=$start;?>&search=<?=$search;?>&searchby=<?=$searchby;?>">Enable</a></font></strong><strong></strong></td>
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
					*
				from 
					phycount
				where
					type='E'";  		// -- type 'C' Computer , E 'Encoded'
	if ($search != '' && $searchby == 'barcode')
	{
		$q .= " and $searchby like '$search%' ";
	}
	elseif ($search != '')
	{
		$q .= " and $msearchby like '$msearch%' ";
	}
	if ($sortby == '')
		$q .= " order by date desc  ";
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

	$qr = @pg_query($q) or message1("Error querying stock data...".pg_errormessage().$q);

	if (@pg_num_rows($qr) == 0 && $p1!= '') message("stock data [NOT] found...");
	$ctr=0;
	while ($r = pg_fetch_object($qr))
	{
		$ctr++;
		$bgColor='#FFFFFF';
		if ($r->enable=='N') $bgColor='#FFD2D2';
		$in_qty = $out_qty = 0;
		$balance_qty = $in_qty - $out_qty;		
		
		if ($r->type == 'E')
		{
			$type = 'Encoded';
		}
		elseif ($r->type=='C')
		{
			$type = 'Automated';
		}
		else
		{
			$type = 'Unknown';
		}
		if ($r->account_id == '0')
		{
			$supplier = 'Various';
		}
		else
		{
			$supplier = lookUpTableReturnValue('x','account','account_id','account',$r->account_id);
		}
		
		$d = explode('-',$r->date);
		$date = $d[0].$d[1].$d[2];
  ?>
    <tr onMouseOver="bgColor='#FFFFDD'" onMouseOut="bgColor='<?= $bgColor;?>'" bgcolor="<?= $bgColor;?>"> 
      <td width="7%" align="right" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $ctr;?>
        . 
        <input type="checkbox" name="mark[]" value="<?= $r->phycount_id;?>">
        </font></td>
      <td> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href="?p=phycount&p1=Load&id=<?=$r->phycount_id;?>"> 
        <?= str_pad($r->phycount_id,8,'0', str_pad_left);?>
        </a> </font><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <a href="?p=phycount&p1=Load&id=<?=$r->phycount_id;?>"> </a></font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=phycount&p1=Load&id=<?=$r->phycount_id;?>">
        <?= ymd2mdy($r->date);?>
        </a></font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
	  <a href="?p=phycount&p1=Load&id=<?=$r->phycount_id;?>"  onmouseover="showToolTip(event,'Add/Edit Items/Products to this Physical Count Schedule..');return false" onmouseout="hideToolTip()" > 
        <?= $supplier;?>
        </a></font></td>
      <td width="26%""><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <a href="?p=phycount&p1=Load&id=<?=$r->phycount_id;?>"> 
        <?= lookUpTableReturnValue('x','admin','admin_id','name',$r->admin_id);?>
        </a></font></td>
      <td width="12%""><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <a href="?p=phycount&p1=Load&id=<?=$r->phycount_id;?>"> 
        <?= $type;?>
        </a></font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href="?p=phycount&p1=Load&id=<?=$r->phycount_id;?>"> 
        <?= ($r->enable=='Y' ? 'Yes' : 'No');?>
        </a></font></td>
    </tr>
    <?
	}
	?>
    <tr> 
      <td colspan="7" bgcolor="#FFFFFF"><input type="button" name="Submit22" value="Add New"  class="altBtn" onClick="window.location='?p=phycount&p1=New'"> 
        <input type="button" name="p1" value="Cancel Checked"  class="altBtn" onClick="if (confirm('Are you sure to CANCEL these records?')){document.getElementById('f1').action='?p=phycount.browse&p1=cancelConfirm';document.getElementById('f1').submit();return false;}"> 
      </td>
    </tr>
  </table>
</form>

<div align="center"> <a 
href="?p=phycount.browse&p1=Previous&sortby=<?=$sortby;?>&category_id=<?=$category_id;?>&start=<?=$start;?>&search=<?=$search;?>&searchby=<?=$searchby;?>"><img src="../graphics/redarrow_left.gif" width="4" height="7" border="0"></a> 
  <a href="?p=phycount.browse&p1=Previous&sortby=<?=$sortby;?>&category_id=<?=$category_id;?>&start=<?=$start;?>&search=<?=$search;?>&searchby=<?=$searchby;?>"> 
  Previous</a> | <a href="?p=phycount.browse&p1=Next&sortby=<?=$sortby;?>&category_id=<?=$category_id;?>&start=<?=$start;?>&search=<?=$search;?>&searchby=<?=$searchby;?>">Next</a> 
  <a href="?p=phycount.browse&p1=Next&sortby=<?=$sortby;?>&category_id=<?=$category_id;?>&start=<?=$start;?>&search=<?=$search;?>&searchby=<?=$searchby;?>"><img src="../graphics/redarrow_right.gif" width="4" height="7" border="0"></a> 
</div>
