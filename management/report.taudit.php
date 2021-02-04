<?
	if (!chkRights2('reconciliation','mview',$ADMIN['admin_id']))
	{
		message('You are NOT allowed to view Reports...');
		exit;
	}

if ($p1 == 'Print Draft' || $p1 == 'Go')
{

	$terminal = $_REQUEST['terminal'];
	$date = $_REQUEST['date'];
	
	$mdate = mdy2ymd($date);	
	$term = terminal($terminal);

	$tables = currTables($mdate);
	$sales_header = $tables['sales_header'];
	$sales_detail = $tables['sales_detail'];
	$sales_tender = $tables['sales_tender'];
	
	if ($p1 =='Print Draft' && $SYSCONF['RECEIPT_PRINTER_TYPE'] == 'DRAFT')
	{
		nPrinter('<reset>', $SYSCONF['RECEIPT_PRINTER_TYPE'], $SYSCONF['RECEIPT_PRINTER_DEST']);
	}	

	$q = "select 
					$sales_header.sales_header_id,
					$sales_header.invoice,
					$sales_header.date,
					$sales_header.net_amount,
					$sales_header.discount_amount,
					$sales_header.gross_amount,
					$sales_header.vat_sales,
					$sales_header.total_tax,
					$sales_header.admin_id,
					$sales_header.status,
					$sales_tender.amount as tender_amount,
					$sales_tender.tender_id,
					tender.tender,
					tender.tender_type
			from 
					$sales_header, $sales_tender, tender 
			where 
					$sales_tender.sales_header_id=$sales_header.sales_header_id and
					tender.tender_id=$sales_tender.tender_id and
					ip='".$term['IP']."' and
					date = '$mdate' ";
	if ($username != '')
	{
			$q1 = " select * from admin where username='$username'";
			$q1r = @pg_query($q1) or message1(pg_errormessage());
			$r1 = @pg_fetch_object($q1r);
			
			$admin_id = $r1->admin_id;
			if ($admin_id == '') message1("User/Cashier Name NOT found...");
			else
			{
				$q .= " and $sales_header.admin_id = '$admin_id'";
			}
	}
	$q .= "		order by
					$sales_header.sales_header_id, tender.seq";
	
	$qr = @pg_query($q) or message(pg_errormessage());
	
	$details = '';
	$details1 = '';	
	$lc=7;
	$details.= $SYSCONF['BUSINESS_NAME']."\n";
	$details.= $SYSCONF['BUSINESS_ADDR']."\n";
	$details.= 'Register :'.$term['TERMINAL']."\n";
	$details.= 'Serial   :'.$term['SERIAL']."\n";
	$details.= 'Transaction : '.$date."\n";
	$details.= 'Printed     : '.date('m/d/Y g:ia')."\n";
	$details.= " Docket      Transaction        Amount \n";
	$details.= "---------- ----------------- -----------\n";
	
	if ($p1 == 'Print Draft')
	{
		nPrinter($details, $SYSCONF['RECEIPT_PRINTER_TYPE'], $SYSCONF['RECEIPT_PRINTER_DEST']);
	}	
	$details1.=$details;
	$details = '';

	
	$total_discount = $total_amount = 0;
	$total_voided = $total_suspened = 0;
	$count_voided = $count_suspended = 0;
	$total_cash = $total_charge = $total_check = 0;
	$counter_cash = $counter_charge = $counter_check = 0;
	$total_vat_sales = $total_novat = $total_tax = 0;
	
	$lc=0;
	$sales_counter = 0;
	$_date = $msales_header_id = '';
	$mnet_amount  = $mtendered = 0;
	while ($r = @pg_fetch_object($qr))
	{
		if ($_date != $r->date) 
		{
			$details .= "\n*** Date: ".ymd2mdy($r->date)."\n";
		}
		$_date = $r->date;
		
			
		if ($r->status == 'V')
		{
			if ($msales_header_id != $r->sales_header_id)
			{
				$msales_header_id = $r->sales_header_id;
				$invoice = $r->invoice; //adjustSize(str_pad($r->invoice,8,'0',str_pad_left),8);
				$details .= $invoice.' ';
				$details .= "*** VOIDED ***\n";
				$total_voided += $r->gross_amount;
				$count_voided++;
			}
			else
			{
				continue;
			}	
		}
		else
		{	
			$invoice = adjustSize($r->invoice,10); //adjustSize(str_pad($r->sales_header_id,10,'0',str_pad_left),8);
	
			$details .= adjustSize($invoice,10).' ';
			if ($msales_header_id != $r->sales_header_id)
			{
				$mtendered = $excess = 0;
				$msales_header_id = $r->sales_header_id;
				$mnet_amount = $r->net_amount;
				$mdiscount_amount = $r->discount_amount;
	
				$total_discount += $r->discount_amount;
				$total_amount += $r->net_amount;
				
				$vat_sales = $r->net_amount - $r->total_tax;
				$total_vat_sales += $vat_sales;
				$total_nonvat += ($r->net_amount - ($vat_sales+$r->total_tax));
				$total_tax += $r->total_tax;
				$sales_counter++;
	
			}
			else
			{
					$discount_amount = 0;
			}
			
			if ($r->tender_amount <= $mnet_amount)
			  $amount = $r->tender_amount;	
			else
			{
				$amount = $mnet_amount;
				if ($r->tender_type !='C')
				{
						$excess = $r->net_amount   - ($mtenderered + $r->tender_amount);
				}
			}	
			$mtendered += $amount;
			$mnet_amount -= $amount;
			
			$details .= adjustSize(substr($r->tender,0,15),15).' ';
			//$details .= adjustRight(number_format2($mdiscount_amount,2),8).'  ';
			$details .= adjustRight(number_format2($amount,2),12)."\n";
			
			if ($excess != 0)
			{
				$details .= adjustSize($r->invoice,10).' '; //str_pad($r->sales_header_id,8,'0',str_pad_left),8).' ';
				$details .= adjustSize(substr($r->tender,0,15),15).' ';
				//$details .= adjustRight(number_format2($mdiscount_amount,2),8).'  ';
				$details .= adjustRight(number_format2($excess,2),12)."\n";
			}
			
		}	
/*		if ($lc>55 && $p1 =='Print Draft' &&  ($SYSCONF['RECEIPT_PRINTER_TYPE']=='UDP DRAFT' ||  $SYSCONF['RECEIPT_PRINTER_TYPE']=='DRAFT'))
		{
			$lc=0;
			nPrinter($details, $SYSCONF['RECEIPT_PRINTER_TYPE'], $SYSCONF['RECEIPT_PRINTER_DEST']);
			$details1 .= $details;
			$details = '';
		}*/
	}
	
	
	$details .= str_pad("TOTAL",15,'.').' ';
	$details .= space(11);
	//$details .= adjustRight(number_format2($total_discount,2),9).' ';
	$details .= adjustRight(number_format2($total_amount,2),12)."\n\n";
	$details .= str_pad("    TOTAL VAT SALES",15,'.').' '.adjustRight(number_format($total_vat_sales,2),12)."\n";
	$details .= str_pad("TOTAL NON-VAT SALES",15,'.').' '.adjustRight(number_format($total_nonvat,2),12)."\n";
	$details .= str_pad("          TOTAL TAX",15,'.').' '.adjustRight(number_format($total_tax,2),12)."\n";
	$details .= str_pad("    TOTAL DISCOUNTS",15,'.').' '.adjustRight(number_format($total_discount,2),12)."\n\n";
	
	$details .= "\n\n";
	
	$q = "select * from terminal where definition='TERMINAL' and value='".$term['TERMINAL']."'";
	$qms = @pg_query($q);
	$rms = @pg_fetch_object($qms);
	$ip = $rms->ip;

	$q = "select * from terminal where definition='MACHINENO' and ip='$ip'";
	$qms = @pg_query($q);
	$rms = @pg_fetch_object($qms);
	$machine = $rms->value;
	$details .= center('Accreditation No: '.$SYSCONF['ACCREDITATION'],40)."\n";
	$details .= center('M1 Point of Sale System 1.0pg',40)."\n";
	$details .= center('Machine No: '.$machine,40)."\n";
	
	$details .= "\n\n\n\n\n\n\n\n\n";
	if ($p1 =='Print Draft')
	{
		nPrinter($details, $SYSCONF['RECEIPT_PRINTER_TYPE'], $SYSCONF['RECEIPT_PRINTER_DEST']);
		sleep(2);
		nPrinter('<reset>', $SYSCONF['RECEIPT_PRINTER_TYPE'], $SYSCONF['RECEIPT_PRINTER_DEST']);

	}	
	$details1 .= $details;
	$details = '';


		$ip = $_SERVER['REMOTE_ADDR'];
		$aip = explode('.',$ip);
		$reportfile= 'log/REP'.$aip[3].'.txt';
		
		$reportfile = '../'.$reportfile;
		@writefile($details1, true, $reportfile);


//echo $details;
}
?>
<div align="center">
<form name="form1" method="post" action="">
  <table width="80%" border="0" align="center" cellpadding="2" cellspacing="0">
    <tr> 
      <td background="../graphics/table0_horizontal.PNG" height="2%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        &nbsp;<img src="../graphics/bluelist.gif" width="16" height="17"><font color="#FFFFFF"> 
        Transaction Audit </font></font></strong></td>
    </tr>
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> Transaction 
        Audit</font> <font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="date" type="text" id="date" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $date;?>" size="8">
        <img src="../graphics/dwn-arrow-grn.gif" width="12" height="12" onClick="popUpCalendar(this, form1.date, 'mm/dd/yyyy')"> 
        Terminal 
        <input type="text" size="5" value="<?=$terminal;?>" name="terminal">
        Cashier 
        <input name="username" type="text" id="username" value="<?=$username;?>" size="10">
        <input type="submit" name="p1" value="Go">
        <input type="submit" name="p1" value="Print Draft">
        </font> 
        <hr color="#CC0000"> </td>
    </tr>
    <tr>
      <td height="28" bgcolor="#CCCCCC"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Report 
        Preview </strong></font><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"><b>
        <?
		if (file_exists($reportfile))
		{
			echo " | <a href=\"$reportfile\"><font size=2>Download</font></a>";
		}
		?>
        </b></font></td>
    </tr>
    <tr>
      <td height="98%" valign="top" bgcolor="#FFFFFF">
	  	   <textarea name="print_area" cols="110" rows="20"  wrap="off" readonly><?= $details1;?></textarea>

	  </td>
    </tr>

  </table>
  
      
  <div align="center">
    <input name="p1" type="submit" id="p1" value="Print Draft" >
    <input name="p12" type="button" id="p12" value="Print"  onClick="printIframe(print_area)" >
  </div>
</form>
<iframe name="printit" id="printit" style="width:0px;height:0px;"></iframe>
