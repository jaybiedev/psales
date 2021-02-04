<?

	$q = "select * from account where account_id='$id'";
	$qqr = pg_query($q) or message(pg_errormessage());
	$rr = pg_fetch_object($qqr);

	if (@pg_num_rows($qqr) =='0')
	{
		message1("Selected Guarantor NOT found ...");
	}
	
	
?>
<form name="form1" method="post" action="">
  <table width="80%" border="0" align="center" cellpadding="2" cellspacing="1">
    <tr> 
      <td>Search 
        <input name="search" type="text" id="search" value="<?= $search;?>">
        <input name="p1" type="button" id="p1" value="Go" onClick="window.location='?p=guarantor.browse&p1=Go&search='+form1.search.value"> 
        <input type="button" name="Submit2" value="Add New" onClick="window.location='?p=guarantor&p1=New'">
        <input type="button" name="Submit24" value="Browse Guarantors" onClick="window.location='?p=guarantor'">
        <input type="button" name="Submit23" value="Close" onClick="window.location='?p='">
        <hr color="#CC0000"></td>
    </tr>
  </table>
</form>
<table width="80%" border="0" align="center" cellpadding="0" cellspacing="1">
  <tr>
    <td><b>Guarantor [ 
      <?=$rr->account_code;?>
      ] 
      <?=$rr->account;?>
      </b>&nbsp;&nbsp;&nbsp; | <font size="2"><a href="?p=guarantor.transac&id=<?=$rr->account_id;?>">Add 
      New Bond Entry</a> </font> 
      <hr size="2"></td>
  </tr>
</table>
<table width="80%" border="0" align="center" cellpadding="2" cellspacing="1" bgcolor="#EFEFEF">
  <tr bgcolor="#CCCCCC"> 
    <td height="20" colspan="8"  background="../graphics/table0_horizontal.PNG"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
      <img src="../graphics/team_wksp.gif" width="16" height="17"> Guarantor Bond 
      Transaciton </strong></font></td>
  </tr>
  <tr bgcolor="#EFEFEF"> 
    <td align="right" nowrap><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
    <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=guarantor.browse&sortby=account_code">Date</a></font></strong></td>
    <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=guarantor.browse&sortby=account">Reference 
      </a></font></strong></td>
    <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Type</font></strong></td>
    <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Add</font></strong></td>
    <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Minus</font></strong></td>
    <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Balance</font></strong></td>
    <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Status</font></strong></td>
  </tr>
  <?
	$q = "select * from bond where  account_id='$rr->account_id' order by date";
	$qr = @pg_query($q) or message1(pg_errormessage());
	if (@pg_num_rows($qr) =='0')
	{
		message1("No Transaction Under this Guarantor...");
	}
	$ctr=0;
	while ($r = pg_fetch_object($qr))
	{
		$ctr++;
  ?>
  <tr onMouseOver="bgColor='#FFFFDD'" onMouseOut="bgColor='#FFFFFF'" bgcolor="#FFFFFF"> 
    <td width="3%" align="right" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= $ctr;?>
      .</font></td>
    <td width="10%""><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <a href="?p=guarantor&p1=Load&id=<?= $r->account_id;?>"> 
      <?= $r->account_code;?>
      </a> </font></td>
    <td width="12%""><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=guarantor&p1=Load&id=<?= $r->account_id;?>"> 
      <?= $r->account;?>
      </a></font></td>
    <td width="25%"">&nbsp;</td>
    <td width="15%"">&nbsp;</td>
    <td width="15%"">&nbsp;</td>
    <td width="15%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?=($r->enable=='Y'? 'Yes' : 'No' );?>
      </font></td>
    <td width="23%" nowrap>&nbsp;</td>
  </tr>
  <?
  }
  ?>
  <tr> 
    <td colspan="8" bgcolor="#FFFFFF"><input type="button" name="Submit22" value="Add New" onClick="window.location='?p=guarantor&p1=New'"> 
      <input type="button" name="Submit232" value="Close" onClick="window.location='?p='"></td>
  </tr>
</table>

<div align="center"> <a href="?p=guarantor.browse&p1=Previous&start=<?=$start;?>&search=<?=$search;?>&searchby=<?=$searchby;?>"><img src="../graphics/redarrow_left.gif" width="4" height="7" border="0"></a> 
  <a href="?p=guarantor.browse&p1=Previous&start=<?=$start;?>&search=<?=$search;?>&searchby=<?=$searchby;?>"> 
  Previous</a> | <a href="?p=guarantor.browse&p1=Next&start=<?=$start;?>&search=<?=$search;?>&searchby=<?=$searchby;?>">Next</a> 
  <a href="?p=guarantor.browse&p1=Next&start=<?=$start;?>&search=<?=$search;?>&searchby=<?=$searchby;?>"><img src="../graphics/redarrow_right.gif" width="4" height="7" border="0"></a> 
</div>
