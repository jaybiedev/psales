<?
if (!session_is_registered('aPOBrowse'))
{
	session_register('aPOBrowse');
	$aPOBrowse = null;
	$aPOBrowse = array();
	
}
if ($aPO != '' && !in_array($p1, array('Go','Next','Previous','Sort','Browse')))
{
	echo "<script> window.location='?p=porder' </script>";
	exit;
}
?>
<form action="" method="post" name="form1" >
  <table width="90%" border="0" align="center" cellpadding="2" cellspacing="1">
    <tr> 
      <td>Search 
        <input name="xSearch" type="text" id="xSearch" value="<?= $xSearch;?>">
        <?= lookUpAssoc('searchby',array('PO No.'=>'reference','Record Id'=>'po_header_id','Supplier'=>'account.account','Supplier Code'=>'account.account_code','Date'=>'date','Stock description'=>'stock_description'), $searchby);?>
        <input name="p1" type="submit" id="p1" value="Go"> 
		<input type="button" name="Submit2" value="Add New" onClick="window.location='?p=porder&p1=New'">
        <input type="button" name="Submit23222" value="Close" onClick="window.location='?p='"> 
        <hr color="#CC0000"></td>
    </tr>
  </table>
</form>

<table width="90%" border="0" align="center" cellpadding="2" cellspacing="1" bgcolor="#EFEFEF">
  <tr bgcolor="#CCCCCC"> 
    <td height="20" colspan="8"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
      <img src="../graphics/storage.gif" width="16" height="17"> Browse Purchase 
      Orders</strong></font></td>
  </tr>
  <tr> 
    <td align="center"><font size="2" face="Geneva, Arial, Helvetica, san-serif">#</font></td>
    <td nowrap><font size="2" face="Geneva, Arial, Helvetica, san-serif"> <a href="?p=porder.browse&p1=Go&sortby=po_header_id&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>">Record# 
      </a></font></td>
    <td nowrap><font size="2" face="Geneva, Arial, Helvetica, san-serif"> <a href="?p=porder.browse&p1=Go&sortby=date&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"> 
      Date</a></font></td>
    <td nowrap><font size="2" face="Geneva, Arial, Helvetica, san-serif"> <a href="?p=porder.browse&p1=Go&sortby=reference desc&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"> 
      PO No.</a></font></td>
    <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href="?p=porder.browse&p1=Go&sortby=account&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"> 
      Supplier Account</a></font></td>
    <td width="10%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <a href="?p=porder.browse&p1=Go&sortby=status&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"> 
      Status</a></font></td>
    <td width="10%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=porder.browse&p1=Go&sortby=category_id&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>">Encoder</a></font></td>
    <td width="5%" nowrap>&nbsp;</td>
  </tr>
  <?
  
  if (!in_array($p1, array('')))
  {
  	$fields = array('xSearch', 'sortby', 'searchby', 'start');
	for ($c=0;$c<count($fields);$c++)
	{
		$aPOBrowse[$fields[$c]] = $_REQUEST[$fields[$c]];
	}
  }
$q = "select 
			po_header.po_header_id,
			po_header.date,
			po_header.reference,
			po_header.account_id,
			account.account,
			po_header.status,
			po_header.admin_id,
			po_header.transaction_type
		from 
			po_header,
			account
		where
			account.account_id=po_header.account_id ";

if ($aPOBrowse['xSearch'] != '')
{
	$q .= " and ".$aPOBrowse['searchby']." like '".$aPOBrowse['xSearch']."%' ";
}
if ($aPOBrowse['sortby'] == '')
{
	$aPOBrowse['sortby'] = 'reference desc ';
}
$q .= " order by ".$aPOBrowse['sortby'];


if ($p1 == 'Go' or $p1 == '' or $aPOBrowse['start']=='')
{
	$aPOBRowse['start'] = 0;
}
elseif ($p1 == 'Next')
{
	$aPOBrowse['start'] += 15;
}
elseif ($p1 == 'Previous')
{
	$aPOBRowse['start'] -= 15;
}
if ($aPOBRowse['start']<0) $aPOBRowse['start']=0;
	
$q .= " offset ".$aPOBRowse['start']." limit 15 ";

$qr = @query($q) or message("Error querying Purchase Order data...".db_error().$q);

if (@pg_num_rows($qr) == 0)
{
	if ($p1== 'Go') 
	{
	 	message("Purchase Order data [NOT] found...");
	}	
	else
	{
	 	message("End of File...");
	}
}
	
$ctr=0;
while ($r = @fetch_object($qr))
{
	$ctr++;
	
	
	if ($r->status == 'C')
	{
		$bgcolor = '#FFCCCC';
	}
	elseif ($r->status== 'T')
	{
		$bgcolor = '#BBCCFF';
	}
	elseif ($r->status== 'E')
	{
		$bgcolor = '#AAFFFF';
	}
	else
	{
		$bgcolor = '#FFFFFF';
	}
  ?>
  <tr onMouseOver="bgColor='#FFFFDD'" onMouseOut="bgColor='#FFFFFF'" bgcolor="#FFFFFF" > 
    <td width="3%" align="right" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= $ctr;?>
      .</font></td>
    <td width="7%" nowrap" bgColor="<?=$bgcolor;?>"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <a href="?p=porder&p1=Load&id=<?= $r->po_header_id;?>"> 
      <?= str_pad($r->po_header_id,8,'0',str_pad_left);?>
      </a> </font></td>
    <td width="8%" nowrap"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <a href="?p=porder&p1=Load&id=<?= $r->po_header_id;?>"> 
      <?= ymd2mdy($r->date);?></a>
      </font></td>
    <td width="15%" nowrap"><font size="2" face="Verdana, Arial, Helvetica, sans-serif" onClick="window.location='?p=porder&p1=Load&id=<?= $r->po_header_id;?>'"> 
      <?= $r->reference;?>
      </font></td>
    <td width="34%" nowrap"><font size="2" face="Verdana, Arial, Helvetica, sans-serif" onClick="window.location='?p=porder&p1=Load&id=<?= $r->po_header_id;?>'"> 
      <?= $r->account;?>
      </font></td>
    <td nowrap bgColor="<?=$bgcolor;?>"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= status($r->status);?>
      </font></td>
    <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= lookUpTableReturnValue('x','admin','admin_id','name',$r->admin_id);?>
      </font></td>
    <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <input name="b" type="button" value="Receive" class="btnhov" <?= (!in_array($r->status,array('S','A','T'))?'disabled':'');?> onClick="window.location='?p=receiving&p1=Receive&id=<?= $r->po_header_id;?>'">
      </font></td>
  </tr>
  <?
  }
  ?>
  <tr> 
    <td colspan="8" bgcolor="#FFFFFF"><input type="button" name="Submit22" value="Add New" onClick="window.location='?p=porder&p1=New'"> 
      <input type="button" name="Submit232" value="Close" onClick="window.location='?p='"></td>
  </tr>
</table>

<div align="center"> <a href="?p=porder.browse&p1=Previous&sortby=<?=$sortby;?>&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"><img src="../graphics/redarrow_left.gif" width="4" height="7" border="0"></a> 
  <a href="?p=porder.browse&p1=Previous&sortby=<?=$sortby;?>&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"> 
  Previous</a> | <a href="?p=porder.browse&p1=Next&sortby=<?=$sortby;?>&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>">Next</a> 
  <a href="?p=porder.browse&p1=Next&sortby=<?=$sortby;?>&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"><img src="../graphics/redarrow_right.gif" width="4" height="7" border="0"></a> 
</div>
