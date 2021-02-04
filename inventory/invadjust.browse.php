<?
if (!session_is_registered('aSTBrowse'))
{
	session_register('aSTBrowse');
	$aIABrowse = null;
	$aIABrowse = array();
	
}
if ($aIA != '' && !in_array($p1, array('Go','Next','Previous','Sort','Browse')))
{
	echo "<script> window.location='?p=invadjust' </script>";
	exit;
}
?>
<form action="" method="post" name="form1" >
  <table width="90%" border="0" align="center" cellpadding="2" cellspacing="1">
    <tr> 
      <td>Search 
        <input name="xSearch" type="text" id="xSearch" value="<?= $xSearch;?>">
        <?= lookUpAssoc('searchby',array('Rec Id'=>'invadjust_header_id','Branch To'=>'branch.branch','Remarks'=>'remarks','Stock description'=>'stock_description'), $searchby);?>
        <input name="p1" type="submit" id="p1" value="Go"> 
		<input type="button" name="Submit2" value="Add New" onClick="window.location='?p=invadjust&p1=New'">
        <input type="button" name="Submit23222" value="Close" onClick="window.location='?p='"> 
        <hr color="#CC0000"></td>
    </tr>
  </table>
</form>

<table width="90%" border="0" align="center" cellpadding="2" cellspacing="1" bgcolor="#EFEFEF">
  <tr bgcolor="#CCCCCC" background="../graphics/table_horizontal.PNG"> 
    <td height="20" colspan="8" background="../graphics/table_horizontal.PNG"> 
	   <img src="../graphics/storage.gif" width="16" height="17"> 
      <font size="2" face="Verdana, Arial, Helvetica, sans-serif" color="#DADADA"><strong>
      Browse Inventory Adjustments</strong></font></td>
  </tr>
  <tr> 
    <td align="center"><font size="2" face="Geneva, Arial, Helvetica, san-serif">#</font></td>
    <td nowrap><font size="2" face="Geneva, Arial, Helvetica, san-serif"> <a href="?p=invadjust.browse&p1=Go&sortby=invadjust_header_id&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>">Record# 
      </a></font></td>
    <td nowrap><font size="2" face="Geneva, Arial, Helvetica, san-serif"> <a href="?p=invadjust.browse&p1=Go&sortby=date&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"> 
      Date</a></font></td>
    <td nowrap><font size="2" face="Geneva, Arial, Helvetica, san-serif"> <a href="?p=invadjust.browse&p1=Go&sortby=branch_id_from&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"> 
      Branch </a></font></td>
    <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href="?p=invadjust.browse&p1=Go&sortby=branch_id_to&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"> 
      Remarks</a></font></td>
    <td width="10%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <a href="?p=invadjust.browse&p1=Go&sortby=status&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"> 
      Status</a></font></td>
    <td width="10%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=invadjust.browse&p1=Go&sortby=category_id&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>">Encoder</a></font></td>
    <td width="14%" nowrap>&nbsp;</td>
  </tr>
  <?
  
  if (!in_array($p1, array('')))
  {
  	$fields = array('xSearch', 'sortby', 'searchby', 'start');
	for ($c=0;$c<count($fields);$c++)
	{
		$aIABrowse[$fields[$c]] = $_REQUEST[$fields[$c]];
	}
  }
$q = "select 
			invadjust_header.invadjust_header_id,
			invadjust_header.date,
			invadjust_header.branch_id,
			invadjust_header.status,
			invadjust_header.admin_id,
			invadjust_header.remark,
			branch.branch
		from 
			invadjust_header,
			branch
		where
			branch.branch_id = invadjust_header.branch_id ";
if ($aIABrowse['xSearch'] != '')
{
	$q .= " and ".$aIABrowse['searchby']." like '".$aIABrowse['xSearch']."%' ";
}
if ($aIABrowse['sortby'] == '')
{
	$aIABrowse['sortby'] = 'invadjust_header_id desc ';
}
$q .= " order by ".$aIABrowse['sortby'];

if ($p1 == 'Go' or $p1 == '' /*or $aIABrowse['start']==''*/)
{  
	$aIABrowse['start'] = 0;
}
elseif ($p1 == 'Next')
{  
	$aIABrowse['start'] += 15;
}
elseif ($p1 == 'Previous')
{
	$aIABrowse['start'] -= 15;
}
if ($aIABrowse['start']<0) $aIABrowse['start']=0;
	
$q .= " offset ".$aIABrowse['start']." limit 15 ";

//echo $q;
$qr = @query($q) or message("Error querying Inventory Adjustment data...".db_error().$q);

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
      <a href="?p=invadjust&p1=Load&id=<?= $r->invadjust_header_id;?>"> 
      <?= str_pad($r->invadjust_header_id,8,'0',STR_PAD_LEFT);?>
      </a> </font></td>
    <td width="8%" nowrap"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <a href="?p=invadjust&p1=Load&id=<?= $r->invadjust_header_id;?>"> 
      <?= ymd2mdy($r->date);?></a>
      </font></td>
    <td width="21%" nowrap"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= lookUpTableReturnValue('x','branch','branch_id','branch',$r->branch_id);?>
      </font><font size="2" face="Verdana, Arial, Helvetica, sans-serif" onClick="window.location='?p=invadjust&p1=Load&id=<?= $r->invadjust_header_id;?>'">&nbsp; 
      </font></td>
    <td width="27%" nowrap"><font size="2" face="Verdana, Arial, Helvetica, sans-serif" onClick="window.location='?p=invadjust&p1=Load&id=<?= $r->invadjust_header_id;?>'">&nbsp; 
      </font><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
      <?= $r->remark;?>
      </font><font size="2" face="Verdana, Arial, Helvetica, sans-serif" onClick="window.location='?p=invadjust&p1=Load&id=<?= $r->invadjust_header_id;?>'">&nbsp; 
      </font></td>
    <td nowrap bgColor="<?=$bgcolor;?>"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= status($r->status);?>
      </font></td>
    <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= lookUpTableReturnValue('x','admin','admin_id','name',$r->admin_id);?>
      </font></td>
    <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; </font></td>
  </tr>
  <?
  }
  ?>
  <tr> 
    <td colspan="8" bgcolor="#FFFFFF"><input type="button" name="Submit22" value="Add New" onClick="window.location='?p=invadjust&p1=New'"> 
      <input type="button" name="Submit232" value="Close" onClick="window.location='?p='"></td>
  </tr>
</table>

<div align="center"> <a href="?p=invadjust.browse&p1=Previous&sortby=<?=$sortby;?>&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"><img src="../graphics/redarrow_left.gif" width="4" height="7" border="0"></a> 
  <a href="?p=invadjust.browse&p1=Previous&sortby=<?=$sortby;?>&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"> 
  Previous</a> | <a href="?p=invadjust.browse&p1=Next&sortby=<?=$sortby;?>&start=<?=$start?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>">Next</a> 
  <a href="?p=invadjust.browse&p1=Next&sortby=<?=$sortby;?>&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"><img src="../graphics/redarrow_right.gif" width="4" height="7" border="0"></a> 
</div>
