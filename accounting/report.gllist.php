 <script language="JavaScript" type="text/JavaScript">
<!--
function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);
//-->
</script>
<?
if ($from_date == '')
{
	$from_date = date('m/01/Y');
	$to_date = date('m/d/Y');
}	
if ($p1=='Go' || $p1=='Print Draft')
{
	if ($from_date == '')
	{
		$from_date = date('m/d/Y');
	}	
	$mfrom_date = mdy2ymd($from_date);
	
	$q = "select 
				gltran_detail.gchart_id,
				gltran_detail.debit,
				gltran_detail.credit,
				gltran_detail.narrative,
				gltran_header.xrefer,
				gltran_header.mcheck,
				gltran_header.date,
				account.account
		from 
			gltran_header,
			gltran_detail,
			account
		where 
			gltran_header.gltran_header_id=gltran_detail.gltran_header_id and 
			account.account_id = gltran_header.account_id and 
			gltran_header.status!='C' and
			gltran_header.date<='".mdy2ymd($to_date)."'";
	if ($gchart_id != '')
	{
		$q .= " and gltran_detail.gchart_id='$gchart_id'";
	}
	if ($pcenter_id != '')
	{
		$q .= " and gltran_header.pcenter_id='$pcenter_id'";
	}
	

	$q .= "order by gltran_detail.gchart_id, gltran_header.date";
	$qr = @pg_query($q) or message(pg_error());

	if ($p1 == 'Print Draft')
	{
		doPrint("<small3>");
	}
	$header = "\n\n";
	$header .= center($SYSCONF['BUSINESS_NAME'],130)."\n";
	$header .= center('GENERAL LEDGER LISTING - '.($pcenter_id == '' ? 'ALL' : lookUpTableReturnValue('p','pcenter','pcenter_id','pcenter',$pcenter_id)),130)."\n";
	$header .= center('As of Date '.$from_date.' to '.$to_date,130)."\n\n";
	$header .= "---------- ------------------------- ---------- ------ ----------- ----------- -------------\n";
	$header .= " Date       Particulars              Reference   Check     Debit       Credit       Balance\n";
	$header .= "---------- ------------------------- ---------- ------ ----------- ----------- -------------\n";

	$details = $details1 = '';
	$mgchart_id='';
	$ctr=$sctr=$total_debit = $total_credit = 0;
	$lc=8;
	$mcount=$subtotal_cost=$scount=$balance=0;
	
	
	while ($r = pg_fetch_object($qr))
	{

		if ($mgchart_id != $r->gchart_id)
		{
			if ($mgchart_id != '')
			{
				$details .= space(52)." ----------- ------------\n";
				$details .= space(3). adjustSize($ctr.' Item/s',32).adjustSize('Account Total:',14).space(5).
						adjustRight(number_format($sub_debit,2),11).' '.
						adjustRight(number_format($sub_credit,2),11).' '.	"\n";
				$sub_credit = $sub_debit = $ctr = 0;
				$lc = $lc+2;
			}						
			$details .= "\nAccount : ".adjustSize(lookUpTableReturnValue('x','gchart','gchart_id','gchart',$r->gchart_id),45)."\n";
			$mgchart_id=$r->gchart_id;
			$beginning_balance = 0;
			$show_beginning = 0;
			$balance = 0;
			$lc = $lc+2;
			
			$beginning_balance = lookUpTableReturnValue('x','gchart','gchart_id','beginning_balance',$r->gchart_id);
			$balance = $beginning_balance;
		}
		
		$balance += $r->debit - $r->credit;
		if ($r->date < $mfrom_date) 
		{
			$beginning_balance += $r->debit - $r->credit;
			continue;
		}	
		
		if ($show_beginning == 0)
		{
			$details .= adjustSize('   Beginning Balance '.str_repeat('.',70),70).space(8);
			$details .= adjustRight(number_format($beginning_balance,2),14)."\n";
			$show_beginning = 1;
		}

		if ($r->narrative == '') $narrative = $r->account;
		else	$narrative = $r->account.' ('.$r->narrative.')';
		
		$ctr++;
//		$details .= adjustRight($ctr,4).'. '.
		$details .= adjustSize(ymd2mdy($r->date),10).' '.
					adjustSize($narrative,25).' '.
					adjustSize($r->xrefer,10).' '.
					adjustSize($r->mcheck,6).' '.
					adjustRight(number_format2($r->debit,2),11).' '.
					adjustRight(number_format2($r->credit,2),11).' '.
					adjustRight(number_format2($balance,2),13)."\n";

		$total_debit += $r->debit;
		$total_credit += $r->credit;
		$sub_debit += $r->debit;
		$sub_credit += $r->credit;
		$lc++;

		if ($lc>57)
		{
			$details1 .= $header.$details;
			if ($p1 == 'Print Draft')
			{
				doPrint($header.$details."<eject>");
			}
			$lc=8;
			$details = '';
		}

	}
	if ($mgchart_id != $r->gchart_id)
	{
		$details .= space(52)." ----------- ------------\n";
		$details .= space(3). adjustSize($ctr.' Item/s',32).adjustSize('Account Total:',14).space(5).
				adjustRight(number_format($sub_debit,2),11).' '.
				adjustRight(number_format($sub_credit,2),11).' '.	"\n";
		$lc = $lc+2;
	}
	$details .= "---------- ------------------------- ---------- ------ ----------- ----------- -------------\n";
	$details .= space(7). adjustSize($ctr.' Item/s',32).adjustSize('Grand Total :',14).space(5).
				adjustRight(number_format($total_debit,2),11).' '.
				adjustRight(number_format($total_credit,2),11).' '.	"\n";
	$details .= "---------- ------------------------- ---------- ------ ----------- ----------- -------------\n\n\n";
	$details1 .= $header.$details;
	if ($p1=='Print Draft')
	{
		doPrint($header.$details);
	}	
}
else
{
	$incZero = 1;
}
if ($date == '') $date=date('m/d/Y');	
?>	
<form name="form1" method="post" action="">
  <div align="center">
    <table width="90%" border="0" cellspacing="1" cellpadding="0" bgcolor="#FFFFFF">
      <tr bgcolor="#EFEFEF" background="graphics/table0_horizontal.PNG"> 
        <td colspan="5" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong><font color="#FFFFFF">:: 
          General Ledger </font></strong></font><font  color="#FFFFFF" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Listing</strong></font></td>
      </tr>
      <tr bgcolor="#EFEFEF"> 
        <td width="10%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          <font color="#CC0000">Account</font> </font></td>
        <td width="10%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"><font color="#CC0000">PCenter</font></font></td>
        <td width="3%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">From</font></td>
        <td width="8%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          To</font></td>
        <td width="42%" nowrap>&nbsp;</td>
      </tr>
      <tr> 
        <td width="10%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          <select name="gchart_id"  style="border: #CCCCCC 1px solid; width:350px" >
            <option value=''>All Accounts --- </option>
            <?
		$q = "select * from gchart where 1 order by acode, scode ";
		$qr = @pg_query($q);
		while ($r= pg_fetch_object($qr))
		{
			if ($gchart_id == $r->gchart_id)
			{
				echo "<option value=$r->gchart_id selected>$r->acode-$r->scode   $r->gchart</option>";
			}
			else
			{		
				echo "<option value=$r->gchart_id>$r->acode-$r->scode  $r->gchart</option>";
			}	
		}
		
		?>
          </select>
          </font></td>
        <td width="10%" nowrap><select name="pcenter_id"  id="pcenter_id" style="border: #CCCCCC 1px solid; width:150px">
            <option value=''>Profit Center --</option>
            <?

	  	$q = "select * from pcenter where enable='Y' order by pcenter";
		$qr = @pg_query($q);
		while ($r = @pg_fetch_object($qr))
		{
			if ($r->pcenter_id == $pcenter_id)
			{
				echo "<option value=$r->pcenter_id selected>".substr($r->pcenter_code,0,6)." $r->pcenter</option>";
			}
			else
			{
				echo "<option value=$r->pcenter_id>".substr($r->pcenter_code,0,6)." $r->pcenter</option>";
			}
		}
	  ?>
          </select></td>
        <td width="3%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
          </font><font color="#000000"> 
          <input name="from_date" type="text" id="from_date" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $from_date;?>" size="8">
          <img src="graphics/dwn-arrow-grn.gif" width="12" height="12" onClick="popUpCalendar(this, form1.from_date, 'mm/dd/yyyy')"></font></td>
        <td width="8%" nowrap> <input name="to_date" type="text" id="to_date" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $to_date;?>" size="8"> 
          <img src="graphics/dwn-arrow-grn.gif" width="12" height="12" onClick="popUpCalendar(this, form1.to_date, 'mm/dd/yyyy')"></td>
        <td width="42%" nowrap> <input name="p1" type="submit" id="p1" value="Go"> 
        </td>
      </tr>
      <tr> 
        <td colspan="5" nowrap><hr size="1"></td>
      </tr>
      <tr bgcolor="#B5CFD5"> 
        <td colspan="5" nowrap><font color="#00000CC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
          <img src="graphics/bluelist.gif" width="16" height="17"> General Ledger 
          Listing Preview</strong></font></td>
      </tr>
      <tr> 
        <td colspan="5" nowrap><textarea name="print_area" cols="110" rows="20"  wrap="off" readonly><?= $details1;?></textarea></td>
      </tr>
    </table>
    <input name="p1" type="submit" id="p1" value="Print Draft" >
    <input name="p12" type="button" id="p12" value="Print"  onClick="printIframe(print_area)" >
  </div>
</form>

<iframe name="printit" id="printit" style="width:0px;height:0px;"></iframe>
