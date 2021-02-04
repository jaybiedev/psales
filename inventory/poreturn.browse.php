<?
if (!session_is_registered('aPORBrowse'))
{
	session_register('aPORBrowse');
	$aPORBrowse = null;
	$aPORBrowse = array();
	
}
if ($aPOR != '' && !in_array($p1, array('Go','Next','Previous','Sort','Browse')))
{
	echo "<script> window.location='?p=poreturn' </script>";
	exit;
}
?>
<form action="" method="post" name="form1" >
  <table width="90%" border="0" align="center" cellpadding="2" cellspacing="1">
    <tr> 
      <td>Search 
        <input name="xSearch" type="text" id="xSearch" value="<?= $xSearch;?>">
        <?= lookUpAssoc('searchby',array('PO No.'=>'reference','Record Id'=>'por_header_id','Supplier'=>'account.account','Supplier Code'=>'account.account_code','Date'=>'date','Stock description'=>'stock_description'), $searchby);?>
        <input name="p1" type="submit" id="p1" value="Go"> 
		<input type="button" name="Submit2" value="Add New" onClick="window.location='?p=poreturn&p1=New'">
        <input type="button" name="Submit23222" value="Close" onClick="window.location='?p='"> 
        <hr color="#CC0000"></td>
    </tr>
  </table>
</form>

<table width="90%" border="0" align="center" cellpadding="2" cellspacing="1" bgcolor="#EFEFEF">
  <tr bgcolor="#CCCCCC"> 
    <td height="20" colspan="9"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
      <img src="../graphics/storage.gif" width="16" height="17"> Browse Purchase 
      Return</strong></font></td>
  </tr>
  <tr> 
    <td align="center"><font size="2" face="Geneva, Arial, Helvetica, san-serif">#</font></td>
    <td nowrap><font size="2" face="Geneva, Arial, Helvetica, san-serif"> <a href="?p=poreturn.browse&p1=Go&sortby=por_header_id&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>">Record# 
      </a></font></td>
    <td nowrap><font size="2" face="Geneva, Arial, Helvetica, san-serif"> <a href="?p=poreturn.browse&p1=Go&sortby=date&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"> 
      Date</a></font></td>
    <td nowrap><font size="2" face="Geneva, Arial, Helvetica, san-serif"><a href="?p=poreturn.browse&p1=Go&sortby=rr_header_id desc&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>">SRR</a></font></td>
    <td nowrap><font size="2" face="Geneva, Arial, Helvetica, san-serif"><a href="?p=poreturn.browse&p1=Go&sortby=type desc&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>">Type</a></font></td>
    <td nowrap><font size="2" face="Geneva, Arial, Helvetica, san-serif"> <a href="?p=poreturn.browse&p1=Go&sortby=reference desc&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"> 
      Reference</a></font></td>
    <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href="?p=poreturn.browse&p1=Go&sortby=account&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"> 
      Supplier Account</a></font></td>
    <td width="10%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <a href="?p=poreturn.browse&p1=Go&sortby=status&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"> 
      Status</a></font></td>
    <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=poreturn.browse&p1=Go&sortby=category_id&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>">Encoder</a></font></td>
  </tr>
  <?
  
  if (!in_array($p1, array('')))
  {
  	$fields = array('xSearch', 'sortby', 'searchby', 'start');
	for ($c=0;$c<count($fields);$c++)
	{
		$aPORBrowse[$fields[$c]] = $_REQUEST[$fields[$c]];
	}
  }
$q = "select 
			por_header.por_header_id,
			por_header.rr_header_id,
			por_header.type,
			por_header.date,
			por_header.reference,
			por_header.account_id,
			account.account,
			account.account_code,
			por_header.status,
			por_header.admin_id
		from 
			por_header,
			account
		where
			account.account_id=por_header.account_id ";

if ($aPORBrowse['xSearch'] != '')
{
	$q .= " and ".$aPORBrowse['searchby']." like '".$aPORBrowse['xSearch']."%' ";
}
if ($aPORBrowse['sortby'] == '')
{
	$aPORBrowse['sortby'] = 'reference desc ';
}
$q .= " order by ".$aPORBrowse['sortby'];


if ($p1 == 'Go' or $p1 == '' or $aPORBrowse['start']=='')
{
	$aPORBRowse['start'] = 0;
}
elseif ($p1 == 'Next')
{
	$aPORBrowse['start'] += 15;
}
elseif ($p1 == 'Previous')
{
	$aPORBRowse['start'] -= 15;
}
if ($aPORBRowse['start']<0) $aPORBRowse['start']=0;
	
//$q .= " offset ".$aPORBRowse['start']." limit 15 ";

$qr = @query($q) or message("Error querying Purchase Return Order data...".db_error().$q);

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
      <a href="?p=poreturn&p1=Load&id=<?= $r->por_header_id;?>"> 
      <?= str_pad($r->por_header_id,8,'0',STR_PAD_LEFT);?>
      </a> </font></td>
    <td width="2%" nowrap"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <a href="?p=poreturn&p1=Load&id=<?= $r->por_header_id;?>"> 
      <?= ymd2mdy($r->date);?>
      </a> </font></td>
    <td width="2%" nowrap"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
      <a href="?p=poreturn&p1=Load&id=<?= $r->por_header_id;?>"> 
      <?= str_pad($r->rr_header_id,9,'0',STR_PAD_LEFT);?>
      </a></font></td>
    <td width="4%" nowrap"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?=($r->type=='1'?'RR':'BO');?>
      </font></td>
    <td width="15%" nowrap"><font size="2" face="Verdana, Arial, Helvetica, sans-serif" onClick="window.location='?p=poreturn&p1=Load&id=<?= $r->por_header_id;?>'"> 
      <?= $r->reference;?>
      </font></td>
    <td width="34%" nowrap"><font size="2" face="Verdana, Arial, Helvetica, sans-serif" onClick="window.location='?p=poreturn&p1=Load&id=<?= $r->por_header_id;?>'"> 
      <?= $r->account_code.' '. $r->account;?>
      </font></td>
    <td nowrap bgColor="<?=$bgcolor;?>"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= status($r->status);?>
      </font></td>
    <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= lookUpTableReturnValue('x','admin','admin_id','name',$r->admin_id);?>
      </font><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
      </font></td>
  </tr>
  <?
  }
  ?>
  <tr> 
    <td colspan="9" bgcolor="#FFFFFF"><input type="button" name="Submit22" value="Add New" onClick="window.location='?p=poreturn&p1=New'"> 
      <input type="button" name="Submit232" value="Close" onClick="window.location='?p='"></td>
  </tr>
</table>

<div align="center"> <a href="?p=poreturn.browse&p1=Previous&sortby=<?=$sortby;?>&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"><img src="../graphics/redarrow_left.gif" width="4" height="7" border="0"></a> 
  <a href="?p=poreturn.browse&p1=Previous&sortby=<?=$sortby;?>&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"> 
  Previous</a> | <a href="?p=poreturn.browse&p1=Next&sortby=<?=$sortby;?>&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>">Next</a> 
  <a href="?p=poreturn.browse&p1=Next&sortby=<?=$sortby;?>&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"><img src="../graphics/redarrow_right.gif" width="4" height="7" border="0"></a> 
</div>
