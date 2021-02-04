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
if ($p1=='Go' || $p1=='Print Draft')
{
	$from_date = $_REQUEST['from_date'];
	$to_date = $_REQUEST['to_date'];
	
	$mfrom_date = mdy2ymd($from_date);
	$mto_date = mdy2ymd($to_date);
	if ($rtype == 'S')
	{
		$q = "select 
				po_header.po_header_id,
				po_header.reference,
				po_header.terms,
				po_header.date,
				account.account as supplier
			from 
				po_header,
				account
			where 
				account.account_id=po_header.account_id and
				date>='$mfrom_date' and 
				date<='$mto_date'";
	}
	else
	{
		$q = "select 
				po_header.po_header_id,
				po_header.reference,
				po_header.terms,
				po_header.date,
				account.account as supplier
			from 
				po_header,
				account
			where 
				account.account_id=po_header.account_id and
				date>='$mfrom_date' and 
				date<='$mto_date'";
	}

	$qr = @pg_query($q) or message(pg_errornessage());
	$header = center($SYSCONF['BUSINESS_NAME'],80)."\n";
	$header .= center('PURCHASE ORDER SUMMARY',80)."\n";
	$header .= center($from_date.' To '.$to_date,80)."\n\n";
	$header .= "   Date      PO. No.  Reference  Supplier                                Term       Amount \n";
	$header .= "-----------  -------- --------- ---------------------------------------- --------- -----------\n";
	$details = $details1 = '';
	$total_amount =0;
	$subtotal  = 0;
	$lc=6;
	while ($r = @pg_fetch_object($qr))
	{
		$lc++;
		if ($p1 == 'Print Draft'  && rtype=='D') $details .= "<bold>";
		$details.= ' '.adjustSize(ymd2mdy($r->date),10).'  '.
					adjustSize(str_pad($r->po_header_id,8,'0',str_pad_left),8).'  '.
					adjustSize($r->reference,8).' '.				
					adjustSize($r->supplier,40).' '.
					adjustRight($r->terms,9).' ';
		if ($rtype=='S')
		{
			$q = "select sum(amount) as amount from po_detail 
							where po_header_id='$r->po_header_id'";
			$qqr = @pg_query($q) or message(pg_errormessage());
			$rr = @pg_fetch_object($qqr);

			$details .=	adjustRight(number_format($rr->amount,2),11)."\n";
			$total_amount += $rr->amount;
		}	
		else
		{
			$q = "select stock.stock,  po_detail.barcode, unit_qty, case_qty,  stock.cost3,
								amount
							from po_detail, stock
							where 
								stock.stock_id=po_detail.stock_id and 
								po_detail.po_header_id='$r->po_header_id'";
			$qqr = pg_query($q) or die (pg_errornessage());
			$subtotal = 0;
			$detailsx='';
			while ($rr = pg_fetch_object($qqr))
			{
				$lc++;
				$subtotal += $rr->amount;
				$detailsx .= space(5).
							adjustSize($rr->stock,33).'  '.
							adjustSize($rr->barcode,15).'  '.
							adjustRight(number_format($rr->case_qty,2),7).' '.
							adjustRight(number_format($rr->unit_qty,0),7).' '.
							adjustRight(number_format($rr->cost1,2),10).' '.
							adjustRight(number_format($rr->amount,2),10)."\n";
							
				if ($lc>55 && $p1 == 'Print Draft')
				{
					$details1 .= $header.$details;
					$details .= "<eject>";
					doPrint($header.$details);
					$details = '';
					$lc=6;
				}			
			}	
			$details .=	adjustRight(number_format($subtotal,2),11)."\n";
			if ($p1 == 'Print Draft'  && rtype=='D') $details .= "</bold>";
			$details .= $detailsx;
			$details .= "\n";
			$total_amount += $subtotal;
		}
		if ($lc>55 && $p1 == 'Print Draft')
		{
			$details1 .= $header.$details;
			$details .= "<eject>";
			doPrint($header.$details);
			$lc=6;
		}			
	}

	$details .= "-----------  -------- --------- ---------------------------------------- --------- -----------\n";
	$details .= space(40).adjustSize('TOTAL AMOUNT ->',40).'  '.
				adjustRight(number_format($total_amount,2),12)."\n";
	$details .= "-----------  -------- --------- ---------------------------------------- --------- -----------\n";
	
	
	$details1 .= $header.$details;
	if ($p1=='Print Draft')
	{
		doPrint($details1);
	}	
}	
?>	
<form name="form1" method="post" action="">
  <div align="center">
    <table width="80%" border="0" cellpadding="0" cellspacing="0">
      <tr> 
        <td height="23" colspan="4" background="../graphics/table0_horizontal.PNG"><font color="#FFFFFF" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Purchase 
          Order</strong></font></td>
      </tr>
      <tr> 
        <td width="10%" nowrap><font size="3" color="#000000">&nbsp;<font face="Verdana, Arial, Helvetica, sans-serif" size="2">From<br>
          <input name="from_date" type="text" id="from_date"  onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $from_date;?>" size="8">
          </font></font></font> <font size="2" color="#000000"><img src="../graphics/dwn-arrow-grn.gif" onClick="popUpCalendar(this, form1.from_date, 'mm/dd/yyyy')"><font face="Verdana, Arial, Helvetica, sans-serif"><strong> 
          </strong></font></font></td>
        <td width="8%" nowrap><font size="2" color="#000000" face="Verdana, Arial, Helvetica, sans-serif">To<br>
          <input type="text" name="to_date" value="<?= $to_date;?>" size="8"  onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')">
          </font></font> <font size="2" color="#000000"><img src="../graphics/dwn-arrow-grn.gif" onClick="popUpCalendar(this, form1.to_date, 'mm/dd/yyyy')"></font></td>
        <td width="12%" nowrap><font  size="2" face="Verdana, Arial, Helvetica, sans-serif">Supplier</font><br> 
          <select name="account_id" style="width:200" >
            <option value=''>All Suppliers --- </option>
            <?
		$q = "select * from account, account_type where account.account_type_id=account_type.account_type_id and account_type_code='S' order by account_code, account ";
		$qr = pg_query($q);
		while ($r= pg_fetch_object($qr))
		{
			if ($account_id == $r->account_id)
			{
				echo "<option value=$r->account_id selected>$r->account_code $r->account</option>";
			}
			else
			{		
				echo "<option value=$r->account_id>$r->account_code $r->account</option>";
			}	
		}
		
		?>
          </select>
		</td>
        <td width="70%" nowrap><font size="2" color="#000000"> <font face="Verdana, Arial, Helvetica, sans-serif">Type</font><br>
          <?= lookUpAssoc('rtype',array('Summary'=>'S','Detailed'=>'D'), $rtype);?>
          <input name="p1" type="submit" id="p132" value="Go">
          </font></td>
      </tr>
      <tr bgcolor="#CCCCCC"> 
        <td colspan="4"><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong><img src="../graphics/bluelist.gif"><font color="#000000"> 
          Print Preview</font></strong></font><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
          </strong></font></td>
      </tr>
      <tr> 
        <td colspan="4"><textarea name="textarea" cols="110" rows="18" readonly wrap="OFF"><?= $details1;?></textarea></td>
      </tr>
    </table>
    <input name="p1" type="submit" id="p1" value="Print Draft" >
    <input name="p12" type="button" id="p12" value="Print"  onClick="printIframe(print_area)" >
  </div>
</form>

<iframe name="printit" id="printit" style="width:0px;height:0px;"></iframe>
