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
        <input type="button" name="Submit24" value="Browse Guarantors" onClick="window.location='?p=guarantor.browse'">
        <input type="button" name="Submit23" value="Close" onClick="window.location='?p='">
        <hr color="#CC0000"></td>
    </tr>
  </table>
</form>
<table width="80%" border="0" align="center" cellpadding="0" cellspacing="1">
  <tr>
    <td><b>Guarantor [<?=$rr->account_code;?>]<?=$rr->account;?></b>&nbsp;&nbsp;&nbsp; | <a href="?p=guarantor.transac.browse&id=<?=$rr->account_id;?>">Bond 
      Transaction</a> </font>
<hr size="2"></td>
  </tr>
</table>
<table width="80%" border="0" align="center" cellpadding="2" cellspacing="1" bgcolor="#EFEFEF">
  <tr bgcolor="#CCCCCC"> 
    <td height="20" colspan="4"  background="../graphics/table0_horizontal.PNG"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
      <img src="../graphics/team_wksp.gif" width="16" height="17"> Browse Guarantor 
      Information</strong></font></td>
  </tr>
  <tr bgcolor="#EFEFEF"> 
    <td align="right" nowrap><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
    <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=guarantor.browse&sortby=account_code">Code</a></font></strong></td>
    <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=guarantor.browse&sortby=account">Name</a></font></strong></td>
    <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Enabled</font></strong></td>
  </tr>
  
<?
$q = "select * from account where  guarantor_id='$rr->account_id'";

$qr = @pg_query($q) or message1(pg_errormessage());
	if (@pg_num_rows($qr) =='0')
	{
		message1("No Accounts Under this Guarantor...");
	}
	$ctr=0;
	while ($r = pg_fetch_object($qr))
	{
		$ctr++;
  ?>
  <tr onMouseOver="bgColor='#FFFFDD'" onMouseOut="bgColor='#FFFFFF'" bgcolor="#FFFFFF"> 
    <td width="5%" align="right" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= $ctr;?>
      .</font></td>
    <td width="13%""><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <a href="?p=guarantor&p1=Load&id=<?= $r->account_id;?>"> 
      <?= $r->account_code;?>
      </a> </font></td>
    <td width="61%""><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=guarantor&p1=Load&id=<?= $r->account_id;?>"> 
      <?= $r->account;?>
      </a></font></td>
    <td width="21%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?=($r->enable=='Y'? 'Yes' : 'No' );?>
      </font></td>
  </tr>
  <?
  }
  ?>
  <tr> 
    <td colspan="4" bgcolor="#FFFFFF"><input type="button" name="Submit22" value="Add New" onClick="window.location='?p=guarantor&p1=New'"> 
      <input type="button" name="Submit232" value="Close" onClick="window.location='?p='"></td>
  </tr>
</table>

<div align="center"> <a href="?p=guarantor.browse&p1=Previous&start=<?=$start;?>&search=<?=$search;?>&searchby=<?=$searchby;?>"><img src="../graphics/redarrow_left.gif" width="4" height="7" border="0"></a> 
  <a href="?p=guarantor.browse&p1=Previous&start=<?=$start;?>&search=<?=$search;?>&searchby=<?=$searchby;?>"> 
  Previous</a> | <a href="?p=guarantor.browse&p1=Next&start=<?=$start;?>&search=<?=$search;?>&searchby=<?=$searchby;?>">Next</a> 
  <a href="?p=guarantor.browse&p1=Next&start=<?=$start;?>&search=<?=$search;?>&searchby=<?=$searchby;?>"><img src="../graphics/redarrow_right.gif" width="4" height="7" border="0"></a> 
</div>
