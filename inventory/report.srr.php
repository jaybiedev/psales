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

if ($to_date == '') $to_date=date('m/d/Y');
if ($from_date == '') $from_date=addDate($to_date,-30);

if ($p1=='Go' || $p1=='Print Draft')
{
	$mfrom_date = mdy2ymd($from_date);
	$mto_date = mdy2ymd($to_date);

	$q = "select 
			rr_header.rr_header_id,
			rr_header.po_header_id,
			rr_header.invoice,
			rr_header.reference,
			rr_header.terms,
			rr_header.freight_amount,
			rr_header.discount_amount,
			rr_header.gross_amount,
			rr_header.net_amount,
			rr_header.tax_amount,
			rr_header.tax_add,
			rr_header.tax_add_type,
			rr_header.date,
			status,
			account.account
		from 
			rr_header,
			account
		where 
			account.account_id=rr_header.account_id and
			date>='$mfrom_date' and 
			date<='$mto_date' ";
	if ($account_id != '')
	{
		$q .= " and rr_header.account_id='$account_id'";
	}
	$q .= " order by	rr_header.rr_header_id";
	
	$qr = pg_query($q) or message(pg_errormessage());
	
	$page = 1;
	
	if ($p1 == 'Print Draft') $header = "<small3>";
	
	$header .= "\n\n";
	$header .= adjustSize(strtoupper($SYSCONF['BUSINESS_NAME']),70).
					adjustSize('Date Printed: '.date('m/d/Y g:ia'),40)."\n";
	$header .= adjustSize('STOCKS RECEIVING REPORT SUMMARY',70).
					adjustSize('by: '.$ADMIN['username'],40)."\n";
	$header .= adjustSize($from_date.' To '.$to_date,70).
					adjustSize('Page: '.$page,40)."\n";

	$header .= "---------- -------- ---------------- ---------- ------------------------------ --------- --------- ---------- ---------- ---------- ----------\n";
	$header .= "  Date      SRR No.  Invoice          PO No.         Supplier                   Term       Gross     Discount   Freight   TaxAddOn   Net \n";
	$header .= "---------- -------- ---------------- ---------- ------------------------------ --------- --------- ---------- ---------- ---------- ----------\n";
	$details = $details1 = '';
	$total_amount = $total_gross = $total_discount = $total_freight = $total_net = 0;
	$subtotal  = 0;
	$lc=8;
	while ($r = pg_fetch_object($qr))
	{
		$ponum = '';
		if ($r->po_header_id != '')
		{
			$q = "select * from po_header where po_header_id='$r->po_header_id'";
			$qqr = @pg_query($q) or message(pg_errormessage());
			$rr = @pg_fetch_object($qqr);
			$ponum = $rr->reference;
		}
		if ($p1 == 'Print Draft'  && rtype=='D') $details .= "<bold>";
		$details.= adjustSize(ymd2mdy($r->date),10).' '.
					adjustSize(str_pad($r->rr_header_id,8,'0',str_pad_left),8).'  '.
					adjustSize($r->invoice,15).' '.
					adjustSize($ponum,10).' '.
					adjustSize($r->account,30).' ';
		if ($r->status == 'C')
		{
			$details .= adjustRight('*********** CANCELLED **********',20)."\n";
			$lc++;

		}
		else
		{	
			if ($r->tax_add_type=='P')
			{
				$tax_addon_amount = round($r->gross_amount *($r->tax_add/100),2);
			}
			else
			{
				$tax_addon_amount = $r->tax_add;
			}
			$details .= adjustRight($r->terms,9).' '.
							adjustRight(number_format($r->gross_amount,2),10).' '.
							adjustRight(number_format($r->discount_amount,2),10).' '.
							adjustRight(number_format($r->freight_amount,2),10).' '.
							adjustRight(number_format($tax_addon_amount,2),9).' '.
							adjustRight(number_format($r->net_amount,2),10)."\n";
			$total_gross += $r->gross_amount;
			$total_discount += $r->discount_amount;
			$total_taxaddon_amount += $tax_addon_amount;
			$total_freight += $r->freight_amount;
			$total_net += $r->net_amount;
			$lc++;

		}	
		if ($rtype== 'D')
		{
			$q = "select 
						stock.stock, 
						stock.stock_code, 
						stock.barcode,
						stock.fraction3,
						stock.stock as stock_stock,  
						rr_detail.case_qty, 
						rr_detail.unit_qty,  
						rr_detail.cost1, 
						rr_detail.cost3, 
						amount 
					from 
						rr_detail, stock
					where 
						stock.stock_id=rr_detail.stock_id and 
						rr_detail.rr_header_id='$r->rr_header_id'
					order by
						rr_header.rr_header_id";
			$qqr = @pg_query($q) or die (pg_errormessage());
			$subtotal = 0;
			$detailsx='';
			while ($rr = pg_fetch_object($qqr))
			{
				if ($rr->case_qty == 0 && $rr->unit_qty == 0) continue;
				if ($rr->stock == '') $stock=  $rr->stock_stock;
				else $stock = $rr->stock;
				$lc++;
				$subtotal += $rr->amount;
				
				if ($rr->fraction3 <= '0') $fraction3 = 1;
				else $fraction3 = $rr->fraction3;
				$detailsx .= space(3).
							adjustSize($rr->barcode,15).' '.
							adjustSize($stock,39).' '.
							adjustSize($rr->fraction3."'s ",4).' '.
							adjustRight($rr->case_qty,5).' '.
							adjustRight($rr->unit_qty,5).' '.
							adjustRight(number_format($rr->cost3,2),10).' '.
							adjustRight(number_format($rr->amount,2),12)."\n";
				$lc++;
						
				if ($lc>55 && $p1 == 'Print Draft')
				{
					$details .= "<eject>";
					
					$details1 .= $header.$details;
					if ($SYSCONF['REPORT_PRINTER_TYPE'] != 'TCP DRAFT')
					{
						nPrinter($header.$details, $SYSCONF['REPORT_PRINTER_TYPE'], $SYSCONF['REPORT_PRINTER_DEST']);
						$details = '';
					}
					$page++;
					
					$header = "\n\n";
					$header .= adjustSize(strtoupper($SYSCONF['BUSINESS_NAME']),70).
									adjustSize('Date Printed: '.date('m/d/Y g:ia'),40)."\n";
					$header .= adjustSize('STOCKS RECEIVING REPORT SUMMARY',70).
									adjustSize('by: '.$ADMIN['username'],40)."\n";
					$header .= adjustSize($from_date.' To '.$to_date,70).
									adjustSize('Page: '.$page,40)."\n";
				
					$header .= "---------- -------- ---------------- ---------- ------------------------------ --------- --------- ---------- ---------- ---------- ----------\n";
					$header .= "  Date      SRR No.  Invoice          PO No.         Supplier                   Term       Gross     Discount   Freight   TaxAddOn   Net \n";
					$header .= "---------- -------- ---------------- ---------- ------------------------------ --------- --------- ---------- ---------- ---------- ----------\n";
					
					$lc=8;
				}			
			}	
			if ($p1 == 'Print Draft'  && rtype=='D') $details .= "</bold>";
			$details .= $detailsx;
			$details .= "\n";
			$total_amount += $subtotal;
			$lc++;

		}
		if ($lc>55 && $p1 == 'Print Draft' )
		{
			$details .= "<eject>";
			
			$details1 .= $header.$details;
			if ($SYSCONF['REPORT_PRINTER_TYPE'] != 'TCP DRAFT')
			{
				nPrinter($header.$details, $SYSCONF['REPORT_PRINTER_TYPE'], $SYSCONF['REPORT_PRINTER_DEST']);
				$details = '';
			}
			
			$page++;
			
			$header = "\n\n";
			$header .= adjustSize(strtoupper($SYSCONF['BUSINESS_NAME']),70).
							adjustSize('Date Printed: '.date('m/d/Y g:ia'),40)."\n";
			$header .= adjustSize('STOCKS RECEIVING REPORT SUMMARY',70).
							adjustSize('by: '.$ADMIN['username'],40)."\n";
			$header .= adjustSize($from_date.' To '.$to_date,70).
							adjustSize('Page: '.$page,40)."\n";
		
			$header .= "---------- -------- ---------------- ---------- ------------------------------ --------- --------- ---------- ---------- ---------- ----------\n";
			$header .= "  Date      SRR No.  Invoice          PO No.         Supplier                   Term       Gross     Discount   Freight   TaxAddOn   Net \n";
			$header .= "---------- -------- ---------------- ---------- ------------------------------ --------- --------- ---------- ---------- ---------- ----------\n";
			
			$lc=8;
		}			
	}

	$details .= "---------- -------- ---------------- ---------- ------------------------------ --------- --------- ---------- ---------- ---------- ----------\n";
	$details .= space(60).adjustSize('TOTAL  -->',26).'   '.
				adjustRight(number_format($total_gross,2),10).' '.
				adjustRight(number_format($total_discount,2),10).' '.
				adjustRight(number_format($total_freight,2),10).' '.
				adjustRight(number_format($total_taxaddon_amount,2),9).' '.
				adjustRight(number_format($total_net,2),10)."\n";
	
	
	$details1 .= $header.$details;
	if ($p1=='Print Draft')
	{
	
		$details .= "<eject>";
		
		if ($SYSCONF['REPORT_PRINTER_TYPE'] == 'TCP DRAFT')
		{
			nPrinter($details1, $SYSCONF['REPORT_PRINTER_TYPE'], $SYSCONF['REPORT_PRINTER_DEST']);
		}
		else
		{
			nPrinter($header.$details, $SYSCONF['REPORT_PRINTER_TYPE'], $SYSCONF['REPORT_PRINTER_DEST']);
		}
	}	
}	
?>	
<form name="form1" method="post" action="">
  <div align="center">
    <table width="80%" border="0" cellpadding="0" cellspacing="0">
      <tr> 
        <td colspan="4"  background="../graphics/table0_horizontal.PNG"><font color="#FFFFFF" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Stock Receiving 
          </strong><strong></strong></font></td>
      </tr>
      <tr> 
        <td width="7%" nowrap><font size="3" color="#000000">&nbsp;</font><font size="2" color="#000000">&nbsp; 
          </font><font size="3" color="#000000"><font face="Verdana, Arial, Helvetica, sans-serif"><font size="2">From<br>
          <input name="from_date" type="text" id="from_date"  onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $from_date;?>" size="8">
          </font></font></font> <font size="2" color="#000000"><img src="../graphics/dwn-arrow-grn.gif" onClick="popUpCalendar(this, form1.from_date, 'mm/dd/yyyy')"><font face="Verdana, Arial, Helvetica, sans-serif"><strong> 
          </strong></font></font></td>
        <td width="9%" nowrap><font size="2" color="#000000"><font face="Verdana, Arial, Helvetica, sans-serif">To<br>
          <input type="text" name="to_date" value="<?= $to_date;?>" size="8"  onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')">
          </font></font> <font size="2" color="#000000"><img src="../graphics/dwn-arrow-grn.gif" onClick="popUpCalendar(this, form1.to_date, 'mm/dd/yyyy')"></font></td>
        <td width="15%" nowrap><font size="2" color="#000000"><font face="Verdana, Arial, Helvetica, sans-serif">Supplier 
          Account</font> <br>
          <select name='account_id' id='account_id' style="width:250px"  onKeypress="if(event.keyCode==13) {document.getElementById('date_from').focus();return false;}">
            <option value=''>Select Supplier -- </option>
            <?
  		foreach ($aSUPPLIER as $stemp)
		{
			if ($stemp['account_id'] == $account_id)
			{
				echo "<option value=".$stemp['account_id']." selected>".substr($stemp['account_code'],0,6)." ".$stemp['account']."</option>";
			}
			else
			{
				echo "<option value=".$stemp['account_id'].">".substr($stemp['account_code'],0,6)." ".$stemp['account']."</option>";
			}
		}
		?>
          </select>
          </font> </td>
        <td width="69%" nowrap><font size="2" color="#000000"> <font face="Verdana, Arial, Helvetica, sans-serif">Type</font><br>
          <?= lookUpAssoc('rtype',array('Summary'=>'S','Detailed'=>'D'), $rtype);?>
          <input name="p1" type="submit" id="p132" value="Go">
          </font></td>
      </tr>
      <tr bgcolor="#CCCCCC"> 
        <td colspan="4"><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong><img src="../graphics/bluelist.gif"><font color="#000000">Stocks 
          Receiving Print Preview</font></strong></font><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
          </strong></font></td>
      </tr>
      <tr> 
        <td colspan="4"><textarea name="print_area" cols="110" rows="18" readonly wrap="OFF"><?= $details1;?></textarea></td>
      </tr>
    </table>
    <input name="p1" type="submit" id="p1" value="Print Draft" >
    <input name="p12" type="button" id="p12" value="Print"  onClick="printIframe(print_area)" >
  </div>
</form>

<iframe name="printit" id="printit" style="width:0px;height:0px;"></iframe>
