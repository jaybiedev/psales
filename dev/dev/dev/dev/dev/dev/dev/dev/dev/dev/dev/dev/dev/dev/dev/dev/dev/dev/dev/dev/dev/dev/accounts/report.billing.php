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
			document.f2.action="?p=report.billing&p1=CancelConfirm"
		}	
		else
		{
			document.f2.action="?p=report.billing&p1=Cancel"
		}
	}
	else if (ul.name=='Go')
	{
		document.f1.action="?p=report.billing&p1="+ul.id;
	}	
	else
	{
		document.f2.action="?p=report.billing&p1="+ul.id;
	}
}
</script>
<?

if (!session_is_registered('aLedger'))
{
	session_register('aLedger');
	$aLedger = null;
	$aLedger = array();
}
if (!session_is_registered('aLedgerDetail'))
{
	session_register('aLedgerDetail');
	$aLedgerDetail = null;
	$aLedgerDetail = array();
}
if ($cutoff_date == '' and $p1 == '')
{
	$q = "select * from accountpost where enable='Y' order by cutoff_date desc ";
	$qr = @pg_query($q) or message(pg_errormessage());
	$r = @pg_fetch_object($qr);
	$cutoff_date = ymd2mdy($r->cutoff_date);
}
if ($c_id!= ''&& $p1 == 'SelectGuarantor')
{
	$aLedger=null;
	$aLedger=array();
	$q = "select 
				account.cardno as guarantor_cardno,
				account.account_code,
				account.account_id as guarantor_id,
				account.account as guarantor,
				account.address
		 from 
		 		account
		where 
				account.account_id='$c_id'";
	$qr = @pg_query($q) or message(db_error());			
	$r = @pg_fetch_assoc($qr);
	$aLedger = $r;
	$aLedger['cardno'] = $_REQUEST['cardno'];
	$aLedger['cutoff_date'] = $_REQUEST['cutoff_date'];
	

}	
if ($aLedger['guarantor_id'] != '' && $p1 != 'Go')
{
	$mcutoff_date = mdy2ymd($aLedger['cutoff_date']);
	
	$details = '';
	$header = '';
	$header .= center('G U A R A N T O R   B I L L I N G   S T A T E M E N T',80)."\n";
	$header .= center($SYSCONF['BUSINESS_NAME'],80)."\n";
	$header .= center($SYSCONF['BUSINESS_ADDR'],80)."\n";
	$header .= center('Date Printed '.date('m/d/Y g:ia'),80)."\n\n";
	$header .= 'Billing Cut-off Date : '.$aLedger['cutoff_date']."\n";
	$header .= 'Guarantor ['.$aLedger['guarantor_cardno'].' ] '.$aLedger['guarantor']."\n";
	$header .= '---- ---------- ------------------------------ ------------- ------------ ---------- ------------'."\n";
	$header .= '                                                   Total       Principal                 Total'."\n";
	$header .= '  #   CardNo    Card Holder                       Balance          Due     Interest      Due '."\n"; 
	$header .= '---- ---------- ------------------------------ ------------- ------------ ---------- ------------'."\n";
	$q = "select *
					from 
						account
					where
						guarantor_id='".$aLedger['guarantor_id']."' and
						enable='Y' 
					order by
						cardno";
	if ($aLedger['cardno'] != '')
	{
		$q .= " and cardno = '".$aLedger['cardno']."'";
	}

	$qr = @pg_query($q) or message(pg_errormessage());
	$ctr = 0;
	$lc =12;
	
	include_once('accountbalance.php');
	while ($r = @pg_fetch_object($qr))
	{
		
		/*$q = "select 
			debit_balance,
			grocery_debit,
			drygood_debit,
			date,
			type
		from 
			accountledger 
		where 
			enable='Y' and 
			account_id='$r->account_id' and
			debit_balance != '0'";

		$qqr = @pg_query($q) or message(pg_errormessage());
		
		$balance = $interest = $principal_due = $interest = $amount_due = 0;
		while ($rr = @pg_fetch_object($qqr))
	   {
	   	if (in_array($rr->type, array('I','S')))
			{
				$interest += $rr->debit_balance;
			}
	   	else
			{
				if ($rr->date <= $mcutoff_date)
				{
					$principal_due += $rr->debit_balance;
				}
			}
		//	if ($r->account_id == '73')
		//	{
		//		echo "db ".$rr->debit_balance;
		//	}
			$balance += $rr->debit_balance;
		}
		*/

		$aBal = customerDue($r->account_id, $mcutoff_date);		
		if ($aBal['balance'] == '0')
		{
				continue;
		}
		//echo $q;
		//exit;
		$ctr++;
		$amount_due = $principal_due + $interest;
		$details .= adjustRight($ctr,3).'. ' . adjustSize($r->cardno,10).' '.
					adjustSize($r->account,30).'  '.
					adjustRight(number_format($aBal['balance'],2),12).' '.
					adjustRight(number_format($aBal['principal_due'],2),12).' '.
					adjustRight(number_format($aBal['interest_due'],2),10).' '.
					adjustRight(number_format($aBal['total_due'],2),12).' '.
					"\n";
			$total_balance += $aBal['balance'];
			$total_principal_due += $aBal['principal_due'];
			$total_interest += $aBal['interest_due'];
			$total_due += $aBal['total_due'];
			
			$lc++;
			if ($p1=='Print Draft' && $lc > 55)
			{
				$details1 .= $header.$details;
				$details .= "<eject>";
				nPrinter($header.$details, $SYSCONF['REPORT_PRINTER_TYPE'], $SYSCONF['REPORT_PRINTER_DEST']);
				$details = '';
				$lc=12;
			}	

  }
  

	$details .= '---- ---------- ------------------------------ ------------- ------------ ---------- ------------'."\n";
	$details .= space(28).adjustSize(' TOTAL',20).
					adjustRight(number_format($total_balance,2),12).' '.
					adjustRight(number_format($total_principal_due,2),12).' '.
					adjustRight(number_format($total_interest,2),10).' '.
					adjustRight(number_format($total_due,2),12)."\n";
	$details .= '---- ---------- ------------------------------ ------------- ------------ ---------- ------------'."\n";
	$details .= 'Remarks :'."\n";
	$details .= $remarks."\n";
	$details1 = $header.$details;
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
?> 

<form action="" method="post" name="f1" id="f1">
  <table width="90%" border="0" align="center" cellpadding="0" cellspacing="1">
    <tr> 
      <td colspan="3" background="../graphics/table0_horizontal.PNG" bgcolor="#003366"><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
        :: Account Billing ::</strong></font></td>
    </tr>
    <tr> 
      <td width="11%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Guarantor</font></td>
      <td width="8%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Card#</font></td>
      <td width="81%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Cut-Off</font></td>
    </tr>
    <tr> 
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="guarantor_code" type="text" id="xSearch3" value="<?= $guarantor_code;?>" size="10" maxlength="10">
        <?=lookUpAssoc('sortby',array('Card No.'=>'cardno','Name'=>'account','Account No'=>'account_code'),$sortby);?>
        </font></td>
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <input name="cardno" type="text" id="cardno" value="<?= $cardno;?>" size="10" maxlength="10">
        </font></td>
      <td nowrap><font size="3" color="#000000"><font face="Verdana, Arial, Helvetica, sans-serif"><strong><font size="2">
        <input name="cutoff_date" type="text" id="cutoff_date"  onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $cutoff_date;?>" size="8">
        </font></strong></font></font><font size="3" color="#000000"><font face="Verdana, Arial, Helvetica, sans-serif"><strong><font size="2" color="#000000"><img src="../graphics/dwn-arrow-grn.gif" onClick="popUpCalendar(this, f1.from_date, 'mm/dd/yyyy')"></font></strong></font></font><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="p1" type="submit" id="p1" value="Go">
        <input name="p12" type="button" id="p122" value="Close" onClick="window.location='?p='">
        </font></td>
    </tr>
    <tr> 
      <td colspan="3"><hr color="#BBBBEE" size="1"></td>
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
					account_type_id!='1' and
					$sortby like '$guarantor_code%'
				order by
					$sortby";
					
					
		$qr = @pg_query($q)	or message("Error Querying account file...".db_error());
?>
  
<table width="80%" border="0" align="center" cellpadding="2" cellspacing="1" bgcolor="#EFEFEF">
  <tr bgcolor="#CFD3E7"> 
    <td width="7%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
    <td width="34%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Name</font></strong></td>
    <td width="13%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Card 
      No. </font></strong></td>
    <td width="18%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Account 
      No. </font></strong></td>
    <td width="13%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Status</font></strong></td>
    <td width="15%" align="center"><strong></strong></td>
  </tr>
  <?
  //include_once('accountbalance.php');
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
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href="javascript: document.getElementById('f1').action='?p=report.billing&p1=SelectGuarantor&c_id=<?=$r['account_id'];?>&xSearch=<?=$xSearch;?>&rtype=<?=$rtype;?>';document.getElementById('f1').submit()"> 
      <?= $r['account'];?>
      </a> </font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= $r['cardno'] ;?>
      </font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
      <?= $r['account_code'] ;?>
      </font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= status($r['account_status']);?>
      </font></td>
    <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
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
  elseif ($aLedger['guarantor_id'] != '')
  {
?>
<form action="" method="post" name="f2" id="f2">
<table width="90%" border="0" align="center" cellpadding="2" cellspacing="1" bgcolor="#EFEFEF">
  <tr> 
      <td colspan="4" background="../graphics/table0_horizontal.PNG"><font color="#FFFFFF" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Billing 
        Preview </strong></font></td>
  </tr>
  <tr> 
        <td valign="top" bgcolor="#FFFFFF">
	   <textarea name="print_area" cols="110" rows="18" readonly wrap="OFF"><?= $details1;?></textarea>
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

