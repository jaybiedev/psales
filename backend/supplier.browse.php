<form name="form1" method="post" action="">
  <table width="70%" border="0" align="center" cellpadding="2" cellspacing="1">
    <tr> 
      <td>Search 
        <input name="search" type="text" id="search" value="<?= $search;?>">
        <input name="p1" type="submit" id="p1" value="Go">
        <input type="button" name="Submit2" value="Add New" onClick="window.location='?p=supplier&p1=New'">
        <input type="button" name="Submit23" value="Close" onClick="window.location='?p='">
        <hr color="#CC0000"></td>
    </tr>
  </table>
</form>
<table width="70%" border="0" align="center" cellpadding="2" cellspacing="1" bgcolor="#EFEFEF">
  <tr bgcolor="#CCCCCC"> 
    <td height="20" colspan="4"  background="../graphics/table0_horizontal.PNG"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
      <img src="../graphics/team_wksp.gif" width="16" height="17"> Browse Supplier 
      Information</strong></font></td>
  </tr>
  <?
  	$q = "select * from supplier ";
  	
  if ($searchby == '') $searchby ='account';	
	if ($search != '')
	{
		$q .= " where $searchby like '%$search%' ";
	}
	$q .= " order by supplier ";
	if ($p1 == 'Go' or $p1 == '')
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
	
//	$q .= " limit $start,15 ";

	$qr = mysql_query($q) or message("Error querying Supplier data...".mysql_error().$q);

	if (mysql_num_rows($qr) == 0 && $p1!= '') message("Supplier data [NOT] found...");
	$ctr=0;
	while ($r = mysql_fetch_object($qr))
	{
		$ctr++;
  ?>
  <tr onMouseOver="bgColor='#FFFFDD'" onMouseOut="bgColor='#FFFFFF'" bgcolor="#FFFFFF"> 
    <td width="6%" align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= $ctr;?>
      .</font></td>
    <td width="14%""><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <a href="?p=supplier&id=<?= $r->supplier_id;?>"> 
      <?= $r->supplier_code;?>
      </a> </font></td>
    <td width="66%""><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=supplier&id=<?= $r->supplier_id;?>">
      <?= $r->supplier;?>
      </a></font></td>
    <td width="14%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?=($r->enable==Y ? 'Enabled' : 'Disabled' );?>
      </font></td>
  </tr>
  <?
  }
  ?>
  <tr> 
    <td colspan="4" bgcolor="#FFFFFF"><input type="button" name="Submit22" value="Add New" onClick="window.location='?p=supplier&p1=New'"> 
      <input type="button" name="Submit232" value="Close" onClick="window.location='?p='"></td>
  </tr>
</table>

<div align="center"> <a href="?p=supplier.browse&p1=Previous&start=<?=$start;?>&search=<?=$search;?>&searchby=<?=$searchby;?>"><img src="../graphics/redarrow_left.gif" width="4" height="7" border="0"></a> 
  <a href="?p=supplier.browse&p1=Previous&start=<?=$start;?>&search=<?=$search;?>&searchby=<?=$searchby;?>"> 
  Previous</a> | <a href="?p=supplier.browse&p1=Next&start=<?=$start;?>&search=<?=$search;?>&searchby=<?=$searchby;?>">Next</a> 
  <a href="?p=supplier.browse&p1=Next&start=<?=$start;?>&search=<?=$search;?>&searchby=<?=$searchby;?>"><img src="../graphics/redarrow_right.gif" width="4" height="7" border="0"></a> 
</div>
