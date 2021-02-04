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
  <table width="97%" border="0" align="center" cellpadding="2" cellspacing="1">
    <tr> 
      <td><font size="2">Search</font> 
        <input name="xSearch" type="text" id="xSearch" value="<?= $xSearch;?>" class="altText">
        <?= lookUpAssoc('searchby',array('RR No.'=>'rr_header_id','Invoice No.'=>'invoice','PO Rec.No.'=>'po_header_id','Supplier'=>'account.account','Supplier Code'=>'account.account_code','Date'=>'date'), $searchby);?>
        <font size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <select name="account_id" id="account_id"   style="border: #CCCCCC 1px solid; width:240px">
          <option value=''>All Supplier Accounts--</option>
          <?
  		foreach ($aSUPPLIER as $stemp)
		{
			if ($stemp['account_id'] == $account_id)
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
        </font> 
        <input name="p1" type="submit" id="p1" value="Go" class="altBtn"> 
		<input type="button" name="Submit2" value="Add New" onClick="window.location='?p=receiving&p1=New'"  class="altBtn">
        <input type="button" name="Submit23222" value="Close" onClick="window.location='?p='"  class="altBtn"> 
        <hr color="#CC0000"></td>
    </tr>
  </table>

  <table width="97%" border="0" align="center" cellpadding="2" cellspacing="1" bgcolor="#EFEFEF">
    <tr bgcolor="#CCCCCC"> 
      <td height="20" colspan="8"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
        <img src="../graphics/storage.gif" width="16" height="17"> Browse Stocks 
        Receiving </strong></font></td>
    </tr>
    <tr> 
      <td align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></td>
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <a href="?p=receiving.browse&p1=Go&sortby=receiving&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>">RR 
        No. </a></font></td>
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <a href="?p=receiving.browse&p1=Go&sortby=receiving&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"> 
        Date</a></font></td>
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <a href="?p=receiving.browse&p1=Go&sortby=receiving&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"> 
        Invoice#.</a></font></td>
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=receiving.browse&p1=Go&sortby=receiving&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>">PO#</a></font></td>
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <a href="?p=receiving.browse&p1=Go&sortby=unit&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"> 
        Supplier</a></font></td>
      <td width="7%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <a href="?p=receiving.browse&p1=Go&sortby=category_id&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"> 
        Status</a></font></td>
      <td width="12%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;<a href="?p=receiving.browse&p1=Go&sortby=category_id&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>">Encoder</a></font></td>
    </tr>
    <?
  if (!in_array($p1, array('')))
  {
  	$fields = array('xSearch', 'sortby', 'searchby',  'show', 'maction');
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

if ($account_id > '0')
{
	$q .= " and rr_header.account_id = '$account_id'";
}
if ($aRRBrowse['xSearch'] != '')
{
	$q .= " and ".$aRRBrowse['searchby']." like '".$aRRBrowse['xSearch']."%'";
}
if ($aRRBrowse['sortby'] == '')
{
	$aRRBrowse['sortby'] = 'rr_header_id';
}
$q .= " order by ".$aRRBrowse['sortby']." desc";

if ($p1 == 'Go' or $p1 == '' )
{

	$aRRBrowse['start'] = 0;
}
elseif ($p1 == 'Next')
{
	$aRRBrowse['start'] += 15;

}
elseif ($p1 == 'Previous')
{
	$aRRBrowse['start'] -= 15;
}

if ($aRRBrowse['start']<'0') $aRRBrowse['start']=0;
	
$q .= " offset ".$aRRBrowse['start']." limit 15 ";

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
      <td width="6%" align="right" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $ctr;?>
        . 
        <input name="mark[]" type="checkbox" id="mark[]" value="<?= $r->rr_header_id;?>">
        </font></td>
      <td width="8%" nowrap" bgColor="<?=($r->status=='C' ? '#FFCCCC' : '');?>"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <a href="?p=receiving&p1=Load&id=<?= $r->rr_header_id;?>"> 
        <?= str_pad($r->rr_header_id,8,'0',str_pad_left);?>
        </a> </font></td>
      <td width="7%" nowrap"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <a href="?p=receiving&p1=Load&id=<?= $r->rr_header_id;?>"> 
        <?= ymd2mdy($r->date);?>
        </a> </font></td>
      <td width="8%" nowrap"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <a href="?p=receiving&p1=Load&id=<?= $r->rr_header_id;?>"> 
        <?= $r->invoice;?>
        </a> </font></td>
      <td width="8%" nowrap"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=receiving&p1=Load&id=<?= $r->rr_header_id;?>"> 
        <?= str_pad($r->po_header_id,8,'0',str_pad_left);?>
        </a></font></td>
      <td width="37%" nowrap"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <a href="?p=receiving&p1=Load&id=<?= $r->rr_header_id;?>"> 
        <?= $r->account;?>
        </a> </font></td>
      <td nowrap bgColor="<?=($r->status=='C' ? '#FFCCCC' : '');?>"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= status($r->status);?>
        </font></td>
      <td nowrap><font size="1" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= lookUpTableReturnValue('x','admin','admin_id','username',$r->admin_id);?>
        </font></td>
    </tr>
    <?
  }
  ?>
    <tr> 
      <td colspan="8" bgcolor="#FFFFFF"><font size="2">With Checked</font> 
        <?= lookUpAssoc('maction',array('Nothing'=>'','Mark as Paid'=>'Paid'),$maction);?>
        <input name="p1" type="submit" id="p1"  value="Proceed"> </td>
    </tr>
    <tr> 
      <td colspan="8" bgcolor="#FFFFFF"><input type="button" name="Submit22" value="Add New" onClick="window.location='?p=receiving&p1=New'"> 
        <input type="button" name="Submit232" value="Close" onClick="window.location='?p='"></td>
    </tr>
  </table>
</form>

<div align="center"> <a href="?p=receiving.browse&p1=Previous&sortby=<?=$sortby;?>&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"><img src="../graphics/redarrow_left.gif" width="4" height="7" border="0"></a> 
  <a href="?p=receiving.browse&p1=Previous&sortby=<?=$sortby;?>&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"> 
  Previous</a> | <a href="?p=receiving.browse&p1=Next&sortby=<?=$sortby;?>&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>">Next</a> 
  <a href="?p=receiving.browse&p1=Next&sortby=<?=$sortby;?>&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"><img src="../graphics/redarrow_right.gif" width="4" height="7" border="0"></a> 
</div>
