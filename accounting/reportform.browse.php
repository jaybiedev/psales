<?

if (!session_is_registered('aSTBrowse'))
{
	session_register('aSTBrowse');
	$aRFBrowse = null;
	$aRFBrowse = array();
	
}
if ($aIA != '' && !in_array($p1, array('Go','Next','Previous','Sort','Browse')))
{
	echo "<script> window.location='?p=reportform' </script>";
	exit;
}
?>
<form action="" method="post" name="form1" >
  <table width="90%" border="0" align="center" cellpadding="2" cellspacing="1">
    <tr> 
      <td>Search 
        <input name="xSearch" type="text" id="xSearch" value="<?= $xSearch;?>">
        <?= lookUpAssoc('searchby',array('Rec Id'=>'rf_header_id','Branch To'=>'branch.branch','Remarks'=>'remarks','Stock description'=>'stock_description'), $searchby);?>
        <input name="p1" type="submit" id="p1" value="Go"> 
		<input type="button" name="Submit2" value="Add New" onClick="window.location='?p=reportform&p1=New'">
        <input type="button" name="Submit23222" value="Close" onClick="window.location='?p='"> 
        <hr color="#CC0000"></td>
    </tr>
  </table>
</form>

<table width="90%" border="0" align="center" cellpadding="2" cellspacing="1" bgcolor="#EFEFEF">
  <tr bgcolor="#CCCCCC" background="../graphics/table_horizontal.PNG"> 
    <td height="20" colspan="6" background="../graphics/table_horizontal.PNG"> <img src="../graphics/storage.gif" width="16" height="17"> 
      <font size="2" face="Verdana, Arial, Helvetica, sans-serif" color="#DADADA"><strong> 
      Browse Accounting Report Format</strong></font></td>
  </tr>
  <tr> 
    <td align="center"><font size="2" face="Geneva, Arial, Helvetica, san-serif">#</font></td>
    <td nowrap><font size="2" face="Geneva, Arial, Helvetica, san-serif"> <a href="?p=reportform.browse&p1=Go&sortby=rf_header_id&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>">Record# 
      </a></font></td>
    <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href="?p=reportform.browse&p1=Go&sortby=branch_id_to&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"> 
      Report Form Title</a></font></td>
    <td width="10%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <a href="?p=reportform.browse&p1=Go&sortby=status&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"> 
      Status</a></font></td>
    <td width="10%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;</font></td>
    <td width="14%" nowrap>&nbsp;</td>
  </tr>
  <?
  
  if (!in_array($p1, array('')))
  {
  	$fields = array('xSearch', 'sortby', 'searchby', 'start');
	for ($c=0;$c<count($fields);$c++)
	{
		$aRFBrowse[$fields[$c]] = $_REQUEST[$fields[$c]];
	}
  }
$q = "select 
				*
		from 
			rf_header
		where
			1";
if ($aRFBrowse['xSearch'] != '')
{
	$q .= " and ".$aRFBrowse['searchby']." like '".$aRFBrowse['xSearch']."%' ";
}
if ($aRFBrowse['sortby'] == '')
{
	$aRFBrowse['sortby'] = 'rf_header_id desc ';
}
$q .= " order by ".$aRFBrowse['sortby'];


if ($p1 == 'Go' or $p1 == '' or $aRFBrowse['start']=='')
{
	$aRFBrowse['start'] = 0;
}
elseif ($p1 == 'Next')
{
	$aRFBrowse['start'] += 15;
}
elseif ($p1 == 'Previous')
{
	$aRFBrowse['start'] -= 15;
}
if ($aRFBrowse['start']<0) $aRFBrowse['start']=0;
	


//$q .= " offset ".$aRFBrowse['start']." limit 15 ";

$qr = @pg_query($q) or message("Error querying Report Format data...".pg_errormessage().$q);

if (@pg_num_rows($qr) == 0)
{
	if ($p1== 'Go') 
	{
	 	message("Report Format  data [NOT] found...");
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
      <a href="?p=reportform&p1=Load&id=<?= $r->rf_header_id;?>"> 
      <?= str_pad($r->rf_header_id,8,'0',str_pad_left);?>
      </a> </font></td>
    <td nowrap">
	<font size="2" face="Verdana, Arial, Helvetica, sans-serif" onClick="window.location='?p=reportform&p1=Load&id=<?= $r->rf_header_id;?>'">
      <?= $r->reportform;?>
       </font></td>
    <td nowrap bgColor="<?=$bgcolor;?>"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= $r->enable;?>
      </font></td>
    <td nowrap></td>
    <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
      </font></td>
  </tr>
  <?
  }
  ?>
  <tr> 
    <td colspan="6" bgcolor="#FFFFFF"><input type="button" name="Submit22" value="Add New" onClick="window.location='?p=reportform&p1=New'"> 
      <input type="button" name="Submit232" value="Close" onClick="window.location='?p='"></td>
  </tr>
</table>

<div align="center"> <a href="?p=reportform.browse&p1=Previous&sortby=<?=$sortby;?>&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"><img src="../graphics/redarrow_left.gif" width="4" height="7" border="0"></a> 
  <a href="?p=reportform.browse&p1=Previous&sortby=<?=$sortby;?>&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"> 
  Previous</a> | <a href="?p=reportform.browse&p1=Next&sortby=<?=$sortby;?>&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>">Next</a> 
  <a href="?p=reportform.browse&p1=Next&sortby=<?=$sortby;?>&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"><img src="../graphics/redarrow_right.gif" width="4" height="7" border="0"></a> 
</div>
