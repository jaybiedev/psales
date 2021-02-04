<script language="JavaScript" type="text/JavaScript">
<!--
function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);
//-->
function vSubmit(ul)
{
	if (ul.name == 'Cancel')
	{
		if (confirm("Are you sure to CANCEL account Record?"))
		{
			document.f2.action="?p=report.guarantor.account&p1=CancelConfirm"
		}	
		else
		{
			document.f2.action="?p=report.guarantor.account&p1=Cancel"
		}
	}
	else if (ul.name=='Go')
	{
		document.f1.action="?p=report.guarantor.account&p1="+ul.id;
	}	
	else
	{
		document.f2.action="?p=report.guarantor.account&p1="+ul.id;
	}
}
</script>
<?

if (!session_is_registered('aGRep'))
{
	session_register('aGRep');
	$aGRep = null;
	$aGRep = array();
}
if (!session_is_registered('aGRepDetail'))
{
	session_register('aGRepDetail');
	$aGRepDetail = null;
	$aGRepDetail = array();
}

if ($c_id!= ''&& $p1 == 'Selectaccount')
{
	$aGRep=null;
	$aGRep=array();
	$q = "select 
				account.cardno,
				account.account_code,
				account.account_id,
				account.account,
				account.address
		 from 
		 		account
		where 
				account.account_id='$c_id'";
	$qr = @pg_query($q) or message(db_error());			
	$r = @pg_fetch_assoc($qr);
	$aGRep = $r;
	$p1='selectaccountId';
}	
if ($p1 == 'Print Draft' || $p1=='Print' || $p1 == 'selectaccountId' || $aGRep['account_id']!='')
{
	$details = '';
	$details .= center($SYSCONF['BUSINESS_NAME'],80)."\n";
	$details .= center($SYSCONF['BUSINESS_ADDR'],80)."\n";
	$details .= center('GUARANTOR ACCOUNTS LISTING',80)."\n";
	$details .= center('Date Printed '.date('m/d/Y g:ia'),80)."\n\n";
	$details .= 'Guarantor Account  : ['.adjustSize($aGRep['account_code'],12).']'.adjustSize($aGRep['account'],30).'   Tel No.'.$aGRep['telno']."\n";
	$details .= 'Address  : '.adjustSize($aGRep['address'],45)."\n";
	$details .= '----- -------- --------------------------- ---------- -- ------------  ------------  ------------'."\n";
	$details .= ' #    CardNo.   Card Holder Name             Expiry   ST Credit Limit     Balance        Bond '."\n";
	$details .= '----- -------- --------------------------- ---------- -- ------------  ------------  ------------'."\n";
	$q = "select 
			*			 
		from 
			account
		where 
			guarantor_id='".$aGRep['account_id']."'
		order by
			cardno";
	$qr = @pg_query($q) or message(pg_errormessage());
	
		include_once('accountbalance.php');
		$ctr=$total_limit = $total_balance = $total_bond = 0;
		while ($temp = @pg_fetch_assoc($qr))
	   {
	   		$aBal = customerBalance($temp['account_id']);
	   		$ctr++;
			$details .= adjustRight($ctr,4).'. '.
					adjustSize($temp['cardno'],8).'  '.
					adjustSize($temp['account'],25).'  '.
					adjustSize(ymd2mdy($temp['date_expiry']),10).'  '.
					adjustSize($temp['account_status'],1).' '.
					adjustRight(number_format($temp['credit_limit'],2),12).'  '.
					adjustRight(number_format($aBal['balance'],2),12).'  '.
					adjustRight(number_format($temp['bond'],2),12)."\n";

			$total_limit += $temp['credit_limit'];
			$total_balance += $aBal['balance'];
			$total_bond += $temp['bond'];
    }
  

	$details .= '----- -------- --------------------------- ---------- -- ------------  ------------  ------------'."\n";
	$details .= space(6).adjustSize("$ctr Line(s)",38).' '.
				adjustSize('TOTALS :',11).' '.
				adjustRight(number_format($total_limit,2),12).'  '.
				adjustRight(number_format($total_balance,2),12).'  '.
				adjustRight(number_format($total_bond,2),12)."\n";
	$details .= '----- -------- --------------------------- ---------- -- ------------  ------------  ------------'."\n";
	$details1 = $details;
	if ($SYSCONF['RECEIPT_PRINT'] == 'DRAFT'  && $p1=='Print Draft')
	{
		$details .= "<eject>";
		doPrint($details);
	}
	elseif ($p1 == 'Print')
	{
	   	echo "<textarea name='print_area' cols='90' rows='18' readonly wrap='OFF'>$details</textarea></script>";
		echo "<iframe name='printit' id='printit' style='width:0px;height:0px;'></iframe>";
		echo "<script>printIframe(print_area)</script>";
	}

}
?> 

<form action="" method="post" name="f1" id="f1">
  <table width="90%" border="0" align="center" cellpadding="2" cellspacing="1">
    <tr> 
      <td background="../graphics/table0_horizontal.PNG" bgcolor="#003366" ><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
        :: Guarantor Accounts Listing ::</strong></font></td>
    </tr>
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Search 
        <input name="xSearch" type="text" id="xSearch" value="<?= $xSearch;?>">
        <?=lookUpAssoc('sortby',array('Card No.'=>'cardno','Name'=>'account','Account No'=>'account_code'),$sortby);?>
        <?=lookUpAssoc('rtype',array('Summary'=>'S','Detailed'=>'D'),$rtype);?>
        <?=lookUpAssoc('show',array('Show All'=>'','Show Balance'=>'B','Show Paid'=>'P'),$show);?>
        <input name="p1" type="submit" id="p1" value="Go">
        <input name="p12" type="button" id="p12" value="Close" onClick="window.location='?p='"><br><hr size="1">
        </font></td>
    </tr>
  </table>
</form>
<?
  if ($p1 == 'Go')
  {
	  $q = "select * 
				from 
					account
				where 
					$sortby like '$xSearch%' and
					account_type_id='3' 
				order by
					account";
					
					
		$qr = @pg_query($q)	or message("Error Querying account file...".db_error());
?>
  <br>
<table width="85%" border="0" align="center" cellpadding="2" cellspacing="1" bgcolor="#EFEFEF">
  <tr bgcolor="#CFD3E7"> 
    <td width="7%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
    <td width="38%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Guarantor 
      Name</font></strong></td>
    <td width="21%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Code</font></strong></td>
    <td width="11%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Status</font></strong></td>
    <td width="23%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Balance</font></strong></td>
  </tr>
  <?
  include_once('accountbalance.php');
  	$ctr=0;
  	while ($r = pg_fetch_assoc($qr))
	{
		$ctr++;
		$aBal = customerBalance($r['account_id']);

  ?>
  <tr bgcolor="#FFFFFF" onMouseOver="bgColor='#FFFFCC'" onMouseOut="bgColor='#FFFFFF'"> 
    <td align="right" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?=$ctr;?>
      .</font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
	<a href="javascript: document.getElementById('f1').action='?p=report.guarantor.account&p1=Selectaccount&c_id=<?=$r['account_id'];?>';document.getElementById('f1').submit()"> 
      <?= $r['account'];?>
      </a> </font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= $r['cardno'] ;?>
      </font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= status($r['account_status']);?>
      </font></td>
    <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= number_format($aBal['balance'],2);?>
      </font><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; </font><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
      </font></td>
  </tr>
  <?
  }
  ?>
</table>

<?	
  }
  elseif ($aGRep['account_id'] != '')
  {
?>
<form action="" method="post" name="f2" id="f2">
<table width="90%" border="0" align="center" cellpadding="2" cellspacing="1" bgcolor="#EFEFEF">
  <tr> 
      <td colspan="4" background="../graphics/table0_horizontal.PNG"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Guarantor 
        Accounts List</strong></font></td>
  </tr>
  <tr> 
        <td valign="top" bgcolor="#FFFFFF">
	   <textarea name="print_area" cols="105" rows="22" readonly wrap="OFF"><?= $details1;?></textarea>
      </td>
  </tr>
</table>
  <div align="center">
    <input name="p1" type="submit" id="p1" value="Print Draft" >
    <input name="p1" type="button" id="p1" value="Print"  onClick="printIframe(print_area)" >
  </div>
</form>
<iframe name="printit" id="printit" style="width:0px;height:0px;"></iframe>

<?
}
?>

