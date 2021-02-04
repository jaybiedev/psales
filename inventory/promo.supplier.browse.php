<form name="f1" id="f1" method="post" action="">
  <table width="97%" border="0" align="center" cellpadding="1" cellspacing="1" bgcolor="#EFEFEF">
    <tr> 
      <td colspan="13">Search 
        <input name="xSearch" type="text" id="xSearch" value="<?= $xSearch;?>"> 
        <?= lookUpAssoc('searchby',array('Supplier'=>'account','Date From'=>'date_from','Date To'=>'date_to','Discount'=>'sdisc'),$searchby);?>
        <?=lookUpAssoc('show',array('Show OnGoing/UpComing'=>'S','Upcoming Only'=>'U','OnGoing Only'=>'O','Done Only'=>'D','Show All'=>'A'),$show);?>
        <input name="p1" type="submit" id="go" value="Go"> <input name="p122" type="button" id="p12" value="Generate New" onClick="window.location='?p=promo.supplier&p1=New'"> 
        <hr color="#993300"> </td>
    </tr>
    <tr background="../graphics/table0_horizontal.PNG"> 
      <td height="23" colspan="13"><font color="#EFEFEF" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>&nbsp;::Browse 
        All Promotionals </strong></font></td>
    </tr>
    <tr bgcolor="#DADADA"> 
      <td width="6%" align="center"><strong><font size="1" face="Times New Roman, Times, serif">#</font></strong></td>
      <td width="4%"><strong><font size="1" face="Times New Roman, Times, serif">Code</font></strong></td>
      <td width="28%"><strong><font size="1" face="Times New Roman, Times, serif">Supplier 
        name </font></strong></td>
      <td colspan="2" align="center"><strong><font size="1" face="Times New Roman, Times, serif">Category</font></strong></td>
      <td width="4%" align="center"><strong><font size="1" face="Times New Roman, Times, serif">From</font></strong></td>
      <td width="5%" align="center"><strong><font size="1" face="Times New Roman, Times, serif">To</font></strong></td>
      <td width="6%" align="center"><strong><font size="1" face="Times New Roman, Times, serif">%SDisc</font></strong></td>
      <td width="6%" align="center"><strong><font size="1" face="Times New Roman, Times, serif">%CDisc</font></strong></td>
      <td width="4%" align="center"><strong><font size="1" face="Times New Roman, Times, serif">Items</font></strong></td>
      <td width="4%" align="center"><strong><font size="1" face="Times New Roman, Times, serif">Cust</font></strong></td>
      <td width="7%" align="center"><strong><font size="1" face="Times New Roman, Times, serif">Encoded</font></strong></td>
      <td width="5%" align="center">&nbsp;</td>
    </tr>
    <?
	$today = date('Y-m-d');
	$q = "select 
						promo_header.generated,
						promo_header.promo_header_id,
						promo_header.date_from,
						promo_header.date_to,
						promo_header.sdisc,
						promo_header.cdisc,
						promo_header.account_id, 
						promo_header.include_net,
						promo_header.customer,
						promo_header.category_id_from,
						promo_header.category_id_to,
						promo_header.category_code_from,
						promo_header.category_code_to,
						promo_header.enable,
						admin.username,
						account.account_code,
						account.account
							 
				from 
						promo_header, 
						account, 
						admin
				 where
				 		account.account_id = promo_header.account_id and 
						admin.admin_id = promo_header.admin_id and 
						date_to >= '$today' and
						all_items = 'Y'";
						
	if ($xSearch != '')
	{
		$q .= " and $searchby ilike '$xSearch%'";
	}		
		if ($show == 'S')
		{
			$q .= " and date_to>='$now' ";
		}
		elseif ($show == 'U')
		{
			$q .= " and date_from>'$now' ";
		}
		elseif ($show == 'O')
		{
			$q .= " and date_from<= '$now'  and date_to>='$now' ";
		}
		elseif ($show == 'D')
		{
			$q .= " and date_to<'$now' ";
		}
				
	$q .= "	order by 	date_to desc ";
	$qr = @pg_query($q) or message (pg_errormessage());

	$ctr=0;
	while ($r = @pg_fetch_object($qr))
	{
		$ctr++;
		if ($r->category_code_from == '')
		{	
			$category_code_from = "ALL";
		}
		else
		{
			$category_code_from = $r->category_code_from;
		}
		if ($r->category_code_to == '')
		{	
			$category_code_to = "ALL";
		}
		else
		{
			$category_code_to = $r->category_code_to;
		}
		
	?>
    <tr bgColor="<?= ($r->enable== 'N' ? '#FFCCFF' : '#FFFFFF');?>"> 
      <td align="right" nowrap><font size="1" face="Times New Roman, Times, serif"> 
        <?= $ctr;?>
        . 
        <input type="checkbox" name="mark[]" value="<?= $r->promo_header_id;?>">
        </font></td>
      <td><font size="1" face="Times New Roman, Times, serif"> <a href="?p=promo.supplier&p1=Edit&id=<?=$r->promo_header_id;?>"> 
        <?= $r->account_code;?>
        </a> </font></td>
      <td><font size="1" face="Times New Roman, Times, serif"><a href="?p=promo.supplier&p1=Edit&id=<?=$r->promo_header_id;?>"> 
        <?= $r->account;?>
        </a></font></td>
      <td width="5%" align="center"><font size="1" face="Times New Roman, Times, serif"><a href="?p=promo.supplier&p1=Edit&id=<?=$r->promo_header_id;?>"> 
        <?= $category_code_from;?>
        </a></font></td>
      <td width="5%" align="center"><font size="1" face="Times New Roman, Times, serif"><a href="?p=promo.supplier&p1=Edit&id=<?=$r->promo_header_id;?>"> 
        <?= $category_code_to;?>
        </a></font></td>
      <td><font size="1" face="Times New Roman, Times, serif"> 
        <?= ymd2mdy($r->date_from);?>
        </font></td>
      <td><font size="1" face="Times New Roman, Times, serif"> 
        <?= ymd2mdy($r->date_to);?>
        </font></td>
      <td align="right"><font size="1" face="Times New Roman, Times, serif"> 
        <?= round($r->sdisc,0);?>
        % </font></td>
      <td align="right"><font size="1" face="Times New Roman, Times, serif"> 
        <?= round($r->cdisc,0);?>
        % </font></td>
      <td align="center"><font size="1" face="Times New Roman, Times, serif"> 
        <?= ($r->include_net == 'N'? 'Reg.'  : ($r->include_net == 'Y' ? 'Net'  : 'All'));?>
        </font></td>
      <td align="center"><font size="1" face="Times New Roman, Times, serif"> 
        <?= ($r->customer == 'M'? 'Member'  :  'All');?>
        </font></td>
      <td><font size="1" face="Times New Roman, Times, serif"> 
        <?= $r->username;?>
        </font></td>
      <td> <font size="1" face="Times New Roman, Times, serif"> 
        <?= ($r->enable == 'N'  ?  'Disabled' :  "<a href='?p=promo.supplier&p1=displayitem&id=$r->promo_header_id'>Browse</a>");?>
        </font></td>
    </tr>
    <?
	}
	?>
    <tr> 
      <td colspan="13"><input name="p1" type="button" value="Disable Checked" onClick="if (confirm('Are you sure to DISABLE checked items?') ) {document.getElementById('f1').action = '?p=promo.supplier&p1=ConfirmDisable'; document.getElementById('f1').submit()}"> 
        <input name="p1" type="button" id="p1" value="Enable Checked"  onClick="if (confirm('Are you sure to enable checked items?') ) {document.getElementById('f1').action = '?p=promo.supplier&p1=ConfirmEnable'; document.getElementById('f1').submit()}"></td>
    </tr>
  </table>
  <?
  	if ($p1 == 'displayitem')
	{
		include_once('promo.supplier.item.php');
	}
  ?>
</form>
