<?
if (!session_is_registered('aRRBrowse'))
{
	session_register('aRRBrowse');
	$aRRBrowse = null;
	$aRRBrowse = array();
	
}

//if (!in_array($p1, array('Go','Next','Previous','Sort','Browse')))
if ($aRR['account_id'] != '' && !in_array($p1, array('Go','Next','Previous','Sort','Browse')))
{
	echo "<script> window.location='?p=receiving' </script>";
	exit;
}
?>
<form action="" method="post" name="form1" >
  <table width="85%" border="0" align="center" cellpadding="2" cellspacing="1">
    <tr> 
      <td>Search 
        <input name="xSearch" type="text" id="xSearch" value="<?= $xSearch;?>">
        <?= lookUpAssoc('searchby',array('RR No.'=>'rr_header_id','Invoice No.'=>'invoice','PO Rec.No.'=>'po_header_id','Supplier'=>'account.account','Supplier Code'=>'account.account_code','Date'=>'date'), $searchby);?>
        <input name="p1" type="submit" id="p1" value="Go"> 
		<input type="button" name="Submit2" value="Add New" onClick="window.location='?p=receiving&p1=New'">
        <input type="button" name="Submit23222" value="Close" onClick="window.location='?p='"> 
        <hr color="#CC0000"></td>
    </tr>
  </table>

  <table width="85%" border="0" align="center" cellpadding="2" cellspacing="1" bgcolor="#EFEFEF">
    <tr bgcolor="#CCCCCC"> 
      <td height="20" colspan="7"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
        <img src="../graphics/storage.gif" width="16" height="17"> Browse Stocks 
        Receiving </strong></font></td>
    </tr>
    <tr> 
      <td align="center"><font size="2" face="Geneva, Arial, Helvetica, san-serif">#</font></td>
      <td nowrap><font size="2" face="Geneva, Arial, Helvetica, san-serif"> <a href="?p=receiving.browse&p1=Go&sortby=receiving&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>">RR 
        No. </a></font></td>
      <td nowrap><font size="2" face="Geneva, Arial, Helvetica, san-serif"> <a href="?p=receiving.browse&p1=Go&sortby=receiving&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"> 
        Date</a></font></td>
      <td nowrap><font size="2" face="Geneva, Arial, Helvetica, san-serif"> <a href="?p=receiving.browse&p1=Go&sortby=receiving&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"> 
        Invoice No.</a></font></td>
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <a href="?p=receiving.browse&p1=Go&sortby=unit&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"> 
        Supplier</a></font></td>
      <td width="7%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <a href="?p=receiving.browse&p1=Go&sortby=category_id&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"> 
        Status</a></font></td>
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;</font><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=receiving.browse&p1=Go&sortby=category_id&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>">Encoder</a></font></td>
    </tr>
    <?
  if (!in_array($p1, array('')))
  {
  	$fields = array('xSearch', 'sortby', 'searchby', 'start', 'show', 'maction');
	for ($c=0;$c<count($fields);$c++)
	{
		$aRRBrowse[$fields[$c]] = $_REQUEST[$fields[$c]];
	}
  }

	if ($p1 == 'Proceed' && $maction == 'Paid')
	{
		$ids = implode(',',$mark);
		$q = "update rr_header set status = 'Y' where rr_header_id in ($ids)";
		$qr = @query($q) or message(db_error());
		if ($qr)
		{
			message("Invoices Tagged as Paid...");
		}
	}
$q = "select 
			rr_header.rr_header_id,
			rr_header.date,
			rr_header.invoice,
			rr_header.po_header_id,
			rr_header.account_id,
			account.account,
			rr_header.status,
			rr_header.admin_id
		from 
			rr_header,
			account
		where
			account.account_id=rr_header.account_id ";

if ($aRRBrowse['xSearch'] != '')
{
	$q .= " and ".$aRRBrowse['searchby']." like '".$aRRBrowse['xSearch']."%'";
}
if ($aRRBrowse['sortby'] == '')
{
	$aRRBrowse['sortby'] = 'rr_header_id';
}
$q .= " order by ".$aRRBrowse['sortby']." desc";

		

if ($p1 == 'Go' or $p1 == '' or $aRRBrowse['start']=='')
{
	$aRRBRowse['start'] = 0;
}
elseif ($p1 == 'Next')
{
	$aRRBrowse['start'] += 15;
}
elseif ($p1 == 'Previous')
{
	$aRRBRowse['start'] -= 15;
}
if ($aRRBRowse['start']<0) $aRRBRowse['start']=0;
	
$q .= " offset ".$aRRBRowse['start']." limit 15 ";


$qr = @pg_query($q) or message1("Error querying Stocks Receiving data...".db_error().$q);

if (@pg_num_rows($qr) == 0 )
{
	if ($p1 == 'Go') 
	{
		message("Stocks Receiving data [NOT] found...");
	}
	else
	{
		message("End of File...");
	}	
}
$ctr=0;

while ($r = @pg_fetch_object($qr))
{
	$ctr++;
  ?>
    <tr onMouseOver="bgColor='#FFFFDD'" onMouseOut="bgColor='#FFFFFF'" bgcolor="#FFFFFF"> 
      <td width="4%" align="right" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $ctr;?>.
        <input name="mark[]" type="checkbox" id="mark[]" value="<?= $r->rr_header_id;?>">
        </font></td>
      <td width="8%" nowrap" bgColor="<?=($r->status=='C' ? '#FFCCCC' : '');?>"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <a href="?p=receiving&p1=Load&id=<?= $r->rr_header_id;?>"> 
        <?= str_pad($r->rr_header_id,8,'0',str_pad_left);?>
        </a> </font></td>
      <td width="7%" nowrap"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <a href="?p=receiving&p1=Load&id=<?= $r->rr_header_id;?>"> 
        <?= ymd2mdy($r->date);?></a>
        </font></td>
      <td width="9%" nowrap"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <a href="?p=receiving&p1=Load&id=<?= $r->rr_header_id;?>"> 
        <?= $r->invoice;?></a>
        </font></td>
      <td width="44%" nowrap"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <a href="?p=receiving&p1=Load&id=<?= $r->rr_header_id;?>"> 
        <?= $r->account;?></a>
        </font></td>
      <td nowrap bgColor="<?=($r->status=='C' ? '#FFCCCC' : '');?>"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= status($r->status);?>
        </font></td>
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
        </font><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= lookUpTableReturnValue('x','admin','admin_id','username',$r->admin_id);?>
        </font></td>
    </tr>
    <?
  }
  ?>
    <tr> 
      <td colspan="7" bgcolor="#FFFFFF"><font size="2">With Checked</font> 
        <?= lookUpAssoc('maction',array('Nothing'=>'','Mark as Paid'=>'Paid'),$maction);?>
        <input name="p1" type="submit" id="p1"  value="Proceed"> 
      </td>
    </tr>
    <tr>
      <td colspan="7" bgcolor="#FFFFFF"><input type="button" name="Submit22" value="Add New" onClick="window.location='?p=receiving&p1=New'">
        <input type="button" name="Submit232" value="Close" onClick="window.location='?p='"></td>
    </tr>
  </table>
</form>

<div align="center"> <a href="?p=receiving.browse&p1=Previous&sortby=<?=$sortby;?>&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"><img src="../graphics/redarrow_left.gif" width="4" height="7" border="0"></a> 
  <a href="?p=receiving.browse&p1=Previous&sortby=<?=$sortby;?>&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"> 
  Previous</a> | <a href="?p=receiving.browse&p1=Next&sortby=<?=$sortby;?>&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>">Next</a> 
  <a href="?p=receiving.browse&p1=Next&sortby=<?=$sortby;?>&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"><img src="../graphics/redarrow_right.gif" width="4" height="7" border="0"></a> 
</div>
