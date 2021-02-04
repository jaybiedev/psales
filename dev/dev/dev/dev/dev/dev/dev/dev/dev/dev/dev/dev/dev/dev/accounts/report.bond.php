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
	$details .= center('GUARANTOR BOND LEDGER',80)."\n";
	$details .= center('Date Printed '.date('m/d/Y g:ia'),80)."\n\n";
	$details .= 'Guarantor Account  : ['.adjustSize($aGRep['account_code'],12).']'.adjustSize($aGRep['account'],30)."\n";
	$details .= 'Address  : '.adjustSize($aGRep['address'],45)."\n";
	$details .= '----- ------------ ------------- ---- ------------ ------------ ------------'."\n";
	$details .= ' #       Date       Reference    Type     Add         Less        Balance  '."\n";
	$details .= '----- ------------ ------------- ---- ------------ ------------ ------------'."\n";
  
	$q = "select * from bondledger where account_id='".$aGRep['account_id']."' and enable='Y' order by date";
	

	$qr = @pg_query($q) or message(pg_errormessage());
	$ctr=0;
	$total_debit = $total_credit = $balance = 0;
	while ($r = @pg_fetch_object($qr))
	{
		$ctr++;
		$total_credit += $r->credit;
		$total_debit += $r->debit;
		$balance += $r->debit - $r->credit;
		$details .= adjustRight($ctr,4).'.  '.
			adjustSize(ymd2mdy($r->date),10).'   '.
			adjustSize($r->reference,12).'  '.
			adjustSize($r->type,2).' '.
			adjustRight(number_format2($r->debit,2),12).' '.
			adjustRight(number_format2($r->credit,2),12).' '.
			adjustRight(number_format2($balance,2),12)."\n";
	}
	$details .= '----- ------------ ------------- ---- ------------ ------------ ------------'."\n";
	$details .= space(1).adjustSize("$ctr Line(s)",18).' '.
				adjustSize('TOTALS :',14).'   '.
				adjustRight(number_format($total_debit,2),12).' '.
				adjustRight(number_format($total_credit,2),12).' '.
				adjustRight(number_format($balance,2),12)."\n";
	$details .= '----- ------------ ------------- ---- ------------ ------------ ------------'."\n";
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
  <table width="85%" border="0" align="center" cellpadding="2" cellspacing="1">
    <tr> 
      <td bgcolor="#003366"><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
        :: Guarantor Bond Report ::</strong></font></td>
    </tr>
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Search 
        <input name="xSearch" type="text" id="xSearch" value="<?= $xSearch;?>">
        <?=lookUpAssoc('sortby',array('Card No.'=>'cardno','Name'=>'account','Account No'=>'account_code','Record Id'=>'account_id'),$sortby);?>
        <?=lookUpAssoc('rtype',array('Summary'=>'S','Detailed'=>'D'),$rtype);?>
        <?=lookUpAssoc('show',array('Show All'=>'','Show Balance'=>'B','Show Paid'=>'P'),$show);?>
        <input name="p1" type="submit" id="p1" value="Go">
        <input name="p12" type="button" id="p12" value="Close" onClick="window.location='?p='">
        </font></td>
    </tr>
    <tr> 
      <td><hr color="red"></td>
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
  
<table width="75%" border="0" align="center" cellpadding="2" cellspacing="1" bgcolor="#EFEFEF">
  <tr bgcolor="#CFD3E7"> 
    <td width="7%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
    <td width="38%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Guarantor 
      Name</font></strong></td>
    <td width="21%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Code</font></strong></td>
    <td width="11%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Status</font></strong></td>
    <td width="23%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Bond</font></strong></td>
  </tr>
  <?
  include_once('accountbalance.php');
  	$ctr=0;
  	while ($r = pg_fetch_assoc($qr))
	{
		$ctr++;
		$aBal = bondBalance($r['account_id']);

  ?>
  <tr bgcolor="#FFFFFF" onMouseOver="bgColor='#FFFFCC'" onMouseOut="bgColor='#FFFFFF'"> 
    <td align="right" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?=$ctr;?>
      .</font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
	<a href="javascript: document.getElementById('f1').action='?p=report.bond&p1=Selectaccount&c_id=<?=$r['account_id'];?>&xSearch=<?=$xSearch;?>&rtype=<?=$rtype;?>';document.getElementById('f1').submit()"> 
      <?= $r['account'];?>
      </a> </font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= $r['account_code'] ;?>
      </font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= ($r['enable']=='Y' ? 'Active' : 'Inactive');?>
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
<table width="85%" border="0" align="center" cellpadding="2" cellspacing="1" bgcolor="#EFEFEF">
  <tr> 
      <td colspan="4"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Bond 
        Report Preview</strong></font></td>
  </tr>
  <tr> 
        <td valign="top" bgcolor="#FFFFFF">
	   <textarea name="print_area" cols="105" rows="18" readonly wrap="OFF"><?= $details1;?></textarea>
        <br>
        Remarks<br> 
        <textarea name="remarks" cols="97" id="remarks"><?= $remarks;?>
</textarea> </td>
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

