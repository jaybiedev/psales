<?
if (!session_is_registered('aGUT'))
{
	session_register('aGUT');
	$aGUT = null;
	$aGUT = array();
}
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
  <table width="80%" border="0" align="center" cellpadding="0" cellspacing="1">
    <tr> 
      <td><b>Guarantor [ 
        <?=$rr->account_code;?>
        ] 
        <?=$rr->account;?>
        </b>&nbsp;&nbsp;&nbsp; | <font size="2"><a href="?p=guarantor.transac&id=<?=$rr->account_id;?>">Add 
        New Bond Entry</a> </font> <hr size="2"></td>
    </tr>
  </table>
  <table width="80%" border="0" align="center" cellpadding="0" cellspacing="1">
    <tr> 
      <td width="16%">Reference</td>
      <td width="84%"><input type="text" name="textfield"></td>
    </tr>
    <tr> 
      <td>Date</td>
      <td><input name="date" type="text" id="date" value="<?= date('m/d/Y');?>"></td>
    </tr>
    <tr> 
      <td>Type</td>
      <td><?= lookUpAssoc('type',array('Deposit'=>'D','Withdrawal'=>'W'),$type);?></td>
    </tr>
    <tr> 
      <td>Amount</td>
      <td><input name="amount" type="text" id="amount"></td>
    </tr>
    <tr> 
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td colspan="2"><input name="p1" type="submit" id="p1" value="Save">
        <input name="p1" type="submit" id="p1" value="Print"></td>
    </tr>
  </table>
</form>
<div align="center"></div>
