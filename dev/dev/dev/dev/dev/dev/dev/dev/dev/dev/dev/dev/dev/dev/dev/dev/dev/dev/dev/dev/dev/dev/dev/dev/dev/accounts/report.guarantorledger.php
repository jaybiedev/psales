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
			document.f2.action="?p=report.guarantorledger&p1=CancelConfirm"
		}	
		else
		{
			document.f2.action="?p=report.guarantorledger&p1=Cancel"
		}
	}
	else if (ul.name=='Go')
	{
		document.f1.action="?p=report.guarantorledger&p1="+ul.id;
	}	
	else
	{
		document.f2.action="?p=report.guarantorledger&p1="+ul.id;
	}
}
</script>
<?

if (!session_is_registered('aGLed'))
{
	session_register('aGLed');
	$aGLed = null;
	$aGLed = array();
}
if (!session_is_registered('aGLedDetail'))
{
	session_register('aGLedDetail');
	$aGLedDetail = null;
	$aGLedDetail = array();
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
	$aGLed=null;
	$aGLed=array();
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
	$aGLed = $r;
	$aGLed['cardno'] = $_REQUEST['cardno'];
	$aGLed['cutoff_date'] = $_REQUEST['cutoff_date'];
	

}	
if ($aGLed['guarantor_id'] != '' && $p1 != 'Go')
{
	$mcutoff_date = mdy2ymd($aGLed['cutoff_date']);
	
	$details = '';
	$header = '';
	$header .= center('G U A R A N T O R    A C C O U N T  L E D G E R',80)."\n";
	$header .= center($SYSCONF['BUSINESS_NAME'],80)."\n";
	$header .= center($SYSCONF['BUSINESS_ADDR'],80)."\n";
	$header .= center('Date Printed '.date('m/d/Y g:ia'),80)."\n\n";
	$header .= 'As of Date : '.$aGLed['cutoff_date']."\n";
	$header .= 'Guarantor ['.$aGLed['guarantor_cardno'].' ] '.$aGLed['guarantor']."\n";
	$header .= '---------- ---------- - -------- -------------------- ----------- ----------- -----------'."\n";
	$header .= '  Date      Reference    CardNo    Card Holder          Charges      Payment    Balance'."\n"; 
	$header .= '---------- ---------- - -------- -------------------- ----------- ----------- -----------'."\n";
	$q = "select *
					from 
						accountledger,
						account
					where
						account.account_id = accountledger.account_id and 
						account.guarantor_id='".$aGLed['guarantor_id']."' ";
	if ($aGLed['cardno'] != '')
	{
		$q .= " and cardno = '".$aGLed['cardno']."'";
	}

	$q .= "	order by
						account.cardno, accountledger.date";

	$qr = @pg_query($q) or message(pg_errormessage());
	$ctr = 0;
	$lc =12;
	$balance = $total_debit = $total_credit = 0;
	$maccount_id = '';
	while ($r = @pg_fetch_object($qr))
	{
		if ($maccount_id != $r->account_id)
		{
			if ($maccount_id != '')
			{
				$details .= "\n";
			}
			$maccount_id = $r->account_id;
			$gbal = 0;
			$begbal_display = 0;
			$lc++;
		}
		if ($r->date < mdy2ymd($cutoff_date)) 
		{
			$balance += $r->debit - $r->credit;
			$gbal += $r->debit - $r->credit;
			continue;
		}
		else
		{
			if ($begbal_display != '1')
			{
				$details .= space(10).adjustSize(' Beginning Balance',20).
						space(45).
						adjustRight(number_format($gbal,2),14)."\n";
				$begbal_display = 1;
			}
			$balance += $r->debit - $r->credit;
			$gbal += ($r->debit - $r->credit);
		}
		$ctr++;
		$details .= adjustSize(ymd2mdy($r->date),10).' ' . 
					adjustSize($r->invoice,10).' ' .
					adjustSize($r->type,1).' '.
					adjustSize($r->cardno,8).' '.
					adjustSize($r->account,20).' '.
					adjustRight(number_format($r->debit,2),11).' '.
					adjustRight(number_format($r->credit,2),11).' '.
					adjustRight(number_format($gbal,2),11).' '.
					"\n";
			$total_balance += $balance;
			$total_debit += $r->debit;
			$total_credit += $r->credit;
			
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
  
	$details .= '---------- ---------- - -------- -------------------- ----------- ----------- -----------'."\n";
	$details .= space(33).adjustSize(' TOTAL',20).
					adjustRight(number_format($total_debit,2),12).' '.
					adjustRight(number_format($total_credit, 2),11).' '.
					adjustRight(number_format($balance,2),11)."\n";
	$details .= '---------- ---------- - -------- -------------------- ----------- ----------- -----------'."\n";
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
        :: Guarantor Ledger ::</strong></font></td>
    </tr>
    <tr> 
      <td width="11%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Guarantor</font></td>
      <td width="8%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Card#</font></td>
      <td width="81%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">As 
        of </font></td>
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
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href="javascript: document.getElementById('f1').action='?p=report.guarantorledger&p1=SelectGuarantor&c_id=<?=$r['account_id'];?>&xSearch=<?=$xSearch;?>&rtype=<?=$rtype;?>';document.getElementById('f1').submit()"> 
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
  elseif ($aGLed['guarantor_id'] != '')
  {
?>
<form action="" method="post" name="f2" id="f2">
<table width="90%" border="0" align="center" cellpadding="2" cellspacing="1" bgcolor="#EFEFEF">
  <tr> 
      <td colspan="4" background="../graphics/table0_horizontal.PNG"><font color="#FFFFFF" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Ledger 
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

