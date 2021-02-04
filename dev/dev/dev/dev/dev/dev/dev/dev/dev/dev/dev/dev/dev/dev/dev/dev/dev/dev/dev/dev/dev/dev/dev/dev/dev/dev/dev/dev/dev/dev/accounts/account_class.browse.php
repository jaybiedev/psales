<form name="form1" method="post" action="" style="margin:0">
  <table width="85%" border="0" align="center" cellpadding="2" cellspacing="1">
    <tr> 
      <td>Search 
        <input name="search" type="text" id="search" value="<?= $search;?>">
		<?=lookUpAssoc('searchby',array('Account Classification'=>'account_class','account_class_code'=>'account_class_code'),$searchby);?>
        <input name="p1" type="submit" id="p1" value="Go" accesskey="G">
        <input type="button" name="Submit2" value="Add New" onClick="window.location='?p=account_class&p1=New'" accesskey="N">
        <hr color="#CC0000"></td>
    </tr>
  </table>
</form>
<table width="80%" border="0" align="center" cellpadding="2" cellspacing="0" bgcolor="#EFEFEF">
  <tr bgcolor="#CCCCCC"> 
    <td height="20" colspan="6"  background="../graphics/table0_horizontal.PNG"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
      <img src="../graphics/team_wksp.gif" width="16" height="17"> <font color="#FFFFFF">Browse 
      <font size="2" face="Verdana, Arial, Helvetica, sans-serif">Account</font> 
      Classification</font></strong></font></td>
  </tr>
  <tr> 
    <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
    <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Account 
      Classification </font></strong></td>
    <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Code</font></strong></td>
    <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Grocery</font></strong></td>
    <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Dry 
      Goods </font></strong></td>
    <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Enable</font></strong></td>
  </tr>
  <?
  	if (!session_is_registered('aAcctBrowse'))
	{
		session_register('aAcctBrowse');
		$aAcctBrowse = null;
		$aAcctBrowse = array();
	}
	if ($act != '')
	{
		$aAcctBrowse['account_class_type'] = $act;
	}

  
  	$q = "select * from account_class where true ";
	
	if ($searchby == '') $searchby = 'account_class';
	if ($search != '')
	{
		$q .= " and $searchby ilike '%$search%' ";
	}
	$q .= " order by account_class ";
	
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
	if ($start<0 || $start=='') $start=0;
	
	$q .= " offset $start limit 15 ";

	$qr = @pg_query($q) or message("Error querying Account Classification data...".pg_errormessage().$q);

	if (@pg_num_rows($qr) == 0 && $p1!= '') message("Account Classification data [NOT] found...");
	$ctr=0;
	while ($r = pg_fetch_object($qr))
	{
		$ctr++;
		$href = "?p=account_class&p1=Load&id=$r->account_class_id";
  ?>
  <tr onMouseOver="bgColor='#FFFFDD'" onMouseOut="bgColor='#FFFFFF'" bgcolor="#FFFFFF"> 
    <td width="6%" align="right" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= $ctr;?>
      .</font></td>
    <td width="42%""><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <a href="<?=$href;?>"> 
      <?= $r->account_class;?>
      </a> </font></td>
    <td width="18%""><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="<?=$href;?>"> 
      <?= $r->account_class_code;?>
      </a></font></td>
    <td width="12%""><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="<?=$href;?>"> 
      <?= $r->grocery_interval.'/'.$r->grocery_term;?>
      </a></font></td>
    <td width="14%""><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
      <a href="<?=$href;?>">
      <?= $r->drygood_interval.'/'.$r->drygood_term;?>
      </a> </font></td>
    <td width="8%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?=($r->enable=='Y' ? 'Enabled' : 'Disabled' );?>
      </font></td>
  </tr>
  <?
  }
  ?>
  <tr> 
    <td colspan="6" bgcolor="#FFFFFF"><hr size="1"></td>
  </tr>
  <tr> 
    <td colspan="6" bgcolor="#FFFFFF"><input type="button" name="Submit22" value="Add New" onClick="window.location='?p=account_class&p1=New'"> 
      <input type="button" name="Submit232" value="Close" onClick="window.location='?p='"></td>
  </tr>
</table>

<div align="center"> <a href="?p=account_class.browse&p1=Previous&start=<?=$start;?>&search=<?=$search;?>&searchby=<?=$searchby;?>"><img src="../graphics/redarrow_left.gif" width="4" height="7" border="0"></a> 
  <a href="?p=account_class.browse&p1=Previous&start=<?=$start;?>&search=<?=$search;?>&searchby=<?=$searchby;?>"> 
  Previous</a> | <a href="?p=account_class.browse&p1=Next&start=<?=$start;?>&search=<?=$search;?>&searchby=<?=$searchby;?>">Next</a> 
  <a href="?p=account_class.browse&p1=Next&start=<?=$start;?>&search=<?=$search;?>&searchby=<?=$searchby;?>"><img src="../graphics/redarrow_right.gif" width="4" height="7" border="0"></a> 
</div>
