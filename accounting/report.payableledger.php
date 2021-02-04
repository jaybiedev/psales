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
			document.f2.action="?p=report.payableledger&p1=CancelConfirm"
		}	
		else
		{
			document.f2.action="?p=report.payableledger&p1=Cancel"
		}
	}
	else if (ul.name=='Go')
	{
		document.f1.action="?p=report.payableledger&p1="+ul.id;
	}	
	else
	{
		document.f2.action="?p=report.payableledger&p1="+ul.id;
	}
}
</script>
<?

if (!session_is_registered('aAPLedger'))
{
	session_register('aAPLedger');
	$aAPLedger = null;
	$aAPLedger = array();
}
if (!session_is_registered('aAPLedgerDetail'))
{
	session_register('aAPLedgerDetail');
	$aAPLedgerDetail = null;
	$aAPLedgerDetail = array();
}

if ($c_id!= ''&& $p1 == 'Selectaccount')
{
	$aAPLedger=null;
	$aAPLedger=array();
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
	$aAPLedger = $r;
	$p1='selectaccountId';
}	
if ($p1 == 'Print Draft' || $p1=='Print' || $p1 == 'selectaccountId' || $aAPLedger['account_id']!='')
{
	$details = '';
	$details .= center('P A Y A B L E   L E D G E R',80)."\n";
	$details .= center($SYSCONF['BUSINESS_NAME'],80)."\n";
	$details .= center($SYSCONF['BUSINESS_ADDR'],80)."\n";
	$details .= center('Date Printed '.date('m/d/Y g:ia'),80)."\n\n";
	$details .= 'Account  : ['.adjustSize($aAPLedger['account_code'],10).']'.adjustSize($aAPLedger['account'],30).'   Tel No.'.$aAPLedger['telno']."\n";
	$details .= 'Address  : '.adjustSize($aAPLedger['address'],45)."\n";
	$details .= 'Guarantor: '."\n";
	$details .= '---------- ---------- ----------- - ------------- -------------- -------------- -'."\n";
	$details .= 'Date       Reference   Type           Debit         Credit        Balance'."\n";
	$details .= '---------- ---------- ----------- - ------------- -------------- -------------- -'."\n";
	$q = "select 
			*			 
		from 
			apledger
		where 
			enable='Y' and 
			account_id='".$aAPLedger['account_id']."'
		order by
			record_id,type,date";
			
	$qr = @pg_query($q) or message(pg_errormessage());
	
	$old =$old1=0;
	$balance = 0;
		while ($temp = @pg_fetch_assoc($qr))
	   {
	   		$balance +=  $temp['credit'] - $temp['debit'];  //$temp['grocery_debit'] + $temp['drty_goods_debit'] - $temp['grocery_credit']-$temp['dry_goods_credit'];
			if ($temp['date'] < mdy2ymd($from_date)   && $from_date!='')
			{
				$beginning_balance = $balance;
				continue;
			}
			elseif ($beginning_balance > 0 )
			{
				$details .= adjustSize(ymd2mdy($mfrom_date),10).' '.
						adjustSize('Beginning Balance',55).
						adjustRight(number_format($beginning_balance,2),12)."\n";
			}

			$beginning_balance = 0;

			$details .= adjustSize(ymd2mdy($temp['date']),10).' '.
					adjustSize($temp['reference'],10).'  '.
					adjustSize($temp['type'],10).' '.
					adjustSize($temp['dept'],1).' '.
					adjustRight(number_format2($temp['debit'],2),12).'    '.
					adjustRight(number_format2($temp['credit'],2),12).'  '.
					adjustRight(number_format($balance,2),12)."\n";
			$total_credit += $temp['credit'];
			$total_debit += $temp['debit'];
  }
  

	$details .= '---------- ---------- ----------- - ------------- -------------- -------------- -'."\n";
	$details .= space(10).' Account Balance:'.space(9).
				adjustRight(number_format($total_debit,2),12).'   '.
				adjustRight(number_format($total_credit,2),12).'   '.
				adjustRight(number_format($balance,2),12)."\n";
	$details .= '---------- ---------- ----------- - ------------- -------------- -------------- -'."\n";
	$details .= 'Remarks :'."\n";
	$details .= $remarks."\n";
	$details1 = $details;
	if ($p1=='Print Draft')
	{
	if ($p1=='Print Draft')
	{
		$details .= "<eject>";
		nPrinter($header.$details, $SYSCONF['REPORT_PRINTER_TYPE'], $SYSCONF['REPORT_PRINTER_DEST']);
	}	
	elseif ($p1 == 'Print')
	{
	   	echo "<textarea name='print_area' cols='90' rows='18' readonly wrap='OFF'>$details</textarea></script>";
		echo "<iframe name='printit' id='printit' style='width:0px;height:0px;'></iframe>";
		echo "<script>printIframe(print_area)</script>";
	}
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
  <table width="80%" border="0" align="center" cellpadding="2" cellspacing="1">
    <tr> 
      <td background="../graphics/table_horizontal.PNG" bgcolor="#003366"><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
        :: Account Payable Ledger ::</strong></font></td>
    </tr>
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Search 
        <input name="xSearch" type="text" id="xSearch" value="<?= $xSearch;?>">
        <?=lookUpAssoc('sortby',array('Account No'=>'account_code','Name'=>'account'),$sortby);?>
        <?=lookUpAssoc('rtype',array('Summary'=>'S','Detailed'=>'D'),$rtype);?>
        <?=lookUpAssoc('show',array('Show All'=>'','Show Balance'=>'B','Show Paid'=>'P'),$show);?>
        </font><font size="3" color="#000000"><font face="Verdana, Arial, Helvetica, sans-serif"><strong><font size="2">
        <input name="from_date" type="text" id="from_date"  onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $from_date;?>" size="8">
        </font></strong></font></font><font size="3" color="#000000"><font face="Verdana, Arial, Helvetica, sans-serif"><strong><font size="2" color="#000000"><img src="../graphics/dwn-arrow-grn.gif" onClick="popUpCalendar(this, f1.from_date, 'mm/dd/yyyy')"></font></strong></font></font><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="p1" type="submit" id="p1" value="Go">
        <input name="p12" type="button" id="p12" value="Close" onClick="window.location='?p='">
        </font></td>
    </tr>
    <tr> 
      <td><hr color="#BBBBEE" size="1"></td>
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
					account_type_id in ('1','8') and
					$sortby ilike '$xSearch%'
				order by
					$sortby";
					

		$qr = @pg_query($q)	or message("Error Querying account file...".db_error());
?>
  
<table width="80%" border="0" align="center" cellpadding="2" cellspacing="1" bgcolor="#EFEFEF">
  <tr bgcolor="#CFD3E7"> 
    <td width="5%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
    <td width="44%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Name</font></strong><strong></strong></td>
    <td width="17%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Account 
      No. </font></strong></td>
    <td width="11%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Status</font></strong></td>
    <td width="23%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Balance</font></strong></td>
  </tr>
  <?
//  include_once('accountbalance.php');
  	$ctr=0;
  	while ($r = pg_fetch_assoc($qr))
	{
		$ctr++;
	//	$aBal = customerBalance($r['account_id']);

  ?>
  <tr bgcolor="#FFFFFF" onMouseOver="bgColor='#FFFFCC'" onMouseOut="bgColor='#FFFFFF'"> 
    <td align="right" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?=$ctr;?>
      .</font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href="javascript: document.getElementById('f1').action='?p=report.payableledger&p1=Selectaccount&c_id=<?=$r['account_id'];?>&xSearch=<?=$xSearch;?>&rtype=<?=$rtype;?>';document.getElementById('f1').submit()"> 
      <?= $r['account'];?>
      </a> </font><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
      </font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= $r['account_code'] ;?>
      </font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= status($r['account_status']);?>
      </font></td>
    <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= number_format($aBal['balance'],2);?>
      </font><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
      </font><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
      </font></td>
  </tr>
  <?
  }
  ?>
</table>

<?	
  }
  elseif ($aAPLedger['account_id'] != '')
  {
?>
<form action="" method="post" name="f2" id="f2">
<table width="80%" border="0" align="center" cellpadding="2" cellspacing="1" bgcolor="#EFEFEF">
  <tr> 
      <td colspan="4" background="../graphics/table0_horizontal.PNG"><font color="#FFFFFF" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Account 
        Ledger </strong></font></td>
  </tr>
  <tr> 
        <td valign="top" bgcolor="#FFFFFF">
	   <textarea name="print_area" cols="97" rows="18" readonly wrap="OFF"><?= $details1;?></textarea>
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

