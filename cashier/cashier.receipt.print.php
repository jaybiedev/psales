<?
function receiptPrint()
{
	global $SYSCONF, $aCashier, $aItems;
	$details = $tender = '';
	$printagain = $no_items = 0;

	$details = '';
/*	if ($aCashier['REPRINT'] == '1')
	{
		$details .= str_repeat('-',40)."\n";
		$details .= center('RE-PRINT RECEIPT',40)."\n";
		$details .= str_repeat('-',40)."\n\n";
	}
	elseif ($aCashier['status'] == 'V')
	{

		$details .= str_repeat('-',40)."\n";
		$details .= center('*** VOIDED TRANSACTION ***',40)."\n";
		$details .= str_repeat('-',40)."\n\n";
	}
	else
	{
*/
		if ($SYSCONF['DRAWER'] == 'COM1XX')
		{
			nPrinter("<drawer0>", $SYSCONF['RECEIPT_PRINTER_TYPE'], $SYSCONF['RECEIPT_PRINTER_DEST']);
		}	
		elseif ($SYSCONF['DRAWER'] == 'LPT')
		{
			nPrinter("<drawer>", $SYSCONF['RECEIPT_PRINTER_TYPE'], $SYSCONF['RECEIPT_PRINTER_DEST']);
		}
//	}

	$minvoice = ($aCashier['invoice']==''?$aCashier['sales_header_id']:$aCashier['invoice']);
	$cashiername = lookUpTableReturnValue('x','admin','admin_id','name',$aCashier['admin_id']);

 
	$details .= center($SYSCONF['BUSINESS_NAME'],38)."\n";
	if ($SYSCONF['PROPRIETOR'] != '')
	{
		$details .= center($SYSCONF['PROPRIETOR'],38)."\n";
	}
	$details .=	center($SYSCONF['BUSINESS_ADDR'],38)."\n";
	if ($SYSCONF['BUSINESS_TEL'] != '')
	{
		$details .= center($SYSCONF['BUSINESS_TEL'],38)."\n";
	}
	$details .= center($SYSCONF['BUSINESS_TIN'],38)."\n".
		adjustSize('Date:'.ymd2mdy($aCashier['date']),21).' '.
		adjustSize('Time:'.$aCashier['time'],18)."\n".
		adjustSize('Term/Serial No.',21).' '.
		adjustSize($SYSCONF['TERMINAL'].'/'.$SYSCONF['SERIAL'],18)."\n".
		adjustSize('Cashier:'.substr($cashiername,0,13),21).' '.
		adjustSize('CSI#:'.$minvoice,18)."\n";

	if ($aCashier['REPRINT'] == '1')
	{
		$details .= str_repeat('-',40)."\n";
		$details .= center('*** RE-PRINT RECEIPT ***',40)."\n";
		$details .= str_repeat('-',40)."\n";
	}
	elseif ($aCashier['status'] == 'V')
	{

		$details .= str_repeat('-',40)."\n";
		$details .= center('*** VOIDED TRANSACTION ***',40)."\n";
		$details .= str_repeat('-',40)."\n\n";
	}

	$customername = $aCashier['account'];
	foreach ($aItems as $temp)
	{
		if ($temp['type'] == 'Tender')
		{
			if ($temp['tender_type'] == 'A' ||  $temp['tender'] == 'Credit' || $temp['tender_type'] == 'B')
			{
				$customername = $temp['account'];
			}
		}

	}

	$details .=	adjustSize('Name:'.($customername==''?str_repeat('_',35): $customername),40)."\n";

	$customer_address = $customer_tin = null;
	$details .=	adjustSize('Address:'.($customer_address=='' ?str_repeat('_',35): $customer_address),40)."\n";
	$details .=	adjustSize('TIN:'.($customer_tin==''?str_repeat('_',35): $customer_tin),40)."\n";

	$details .=
			'Description     Qty    Price   Total'."\n".
			str_repeat('-',40)."\n";
	
			
	$tender = $servicecharge = "";
	$printagain = 1;
	$chargeinvoice = 0;		
	$ln=10;
	
	foreach ($aItems as $temp)
	{
		if ($temp['type'] == 'Tender')
		{
			if ($temp['tender_type'] == 'A' ||  $temp['tender'] == 'Credit')
			{
				$chargeinvoice = 1;
			}
			if ($temp['print'] > $printagain)
			{
				$printagain = $temp['print'];
			}
			if ($temp['cardno'] != '')
			{

				/**
				 * update card number here
				 */

				 
				 $cardno = trim($temp['cardno']);
				 if ( strlen($cardno) > 4 ) {
				 	$cardno = str_repeat('*', strlen($cardno) - 4) . substr($cardno,-4);	
				 }
				 

				$tender .= adjustSize(substr($cardno,0,14),14).' '.
					adjustSize(substr($temp['tender'],0,12),12).'P'.
					adjustRight(number_format($temp['amount'],2),12)."\n";
				if (strlen($cardno)>14)
				{
					$tender .= substr($cardno,14,14)."\n";
				}
			}
			else
			{
				$tender .= space(15).adjustSize(substr($temp['tender'],0,12),12).'P'.
					adjustRight(number_format($temp['amount'],2),12)."\n";
			}
		}
		elseif ($temp['type'] == 'ServiceCharge')
		{
			$service .= space(15).
				adjustSize(substr('Srvc Charge',0,11),11).' P'.
				adjustRight(number_format($temp['amount'],2),12)."\n";
		}
		else
		{
	
	     	$cqty = '';
	     	if (intval($temp['qty']) !=  $temp['qty'])
	     	{
	       		$cqty = number_format($temp['qty'],3);
	     	}
       	else
       	{
	       		$cqty = number_format($temp['qty'],0);
       	}
       	if ($temp['qty'] < 0)
       	{
       		if ($printagain < 2)
       		{
       			$printagain = 2;
       		}
       	}
			
			$details .= adjustSize(substr($temp['stock'],0,13),13).' '.
				adjustRight($cqty,6).' '.
				adjustRight(number_format($temp['price'],2),8).' '.
				adjustRight(number_format($temp['amount'],2),10)."\n";
			$no_items += $temp['qty'];						
		}
		$ln++;
		if ($ln > 30)
		{
			//galert($details);
			$rmsg = nPrinter($details, $SYSCONF['RECEIPT_PRINTER_TYPE'], $SYSCONF['RECEIPT_PRINTER_DEST']);
			$details = '';
			$ln=0;
		}
	}

	$details .=	str_repeat('-',40)."\n".
		adjustSize($no_items.' Item(s)',14).space(1).
		adjustSize('Sale Total',12).'P'.
		adjustRight(number_format($aCashier['gross_amount'],2),12)."\n";
   if (strlen($service) > 1)
   {
  		$details .= $service;
   }						
	if ($aCashier['discount_amount'] != 0)
	{
			$cdiscount = '';
			if ($aCashier['discount_id'] == '') 
			{
				$cdiscount .= 'Discount';
			}
			else 
			{
				$qd ="select * from discount where discount_id ='".$aCashier['discount_id']."'";
				$qdr = @pg_query($qd);
				if (@pg_num_rows($qdr)>0)
				{
					$cdiscount = '(-)';
					$rd = @pg_fetch_object($qdr);
					$cdiscount .= $rd->discount_type;
					$percent = $rd->discount_percent;
				}
				
			}
			$percent = round(($aCashier['discount_amount'] / $aCashier['gross_amount'])*100,0);
			//$details .= space(12).' Less ('.adjustSize(number_format($percent,0),5).'%) '.'P'.
			$details .= space(15).'    Less :  P'.
			adjustRight(number_format($aCashier['discount_amount'],2),12)."\n";
			$details .= space(15).adjustSize('Net Amount',12).'P'.
			adjustRight(number_format($aCashier['net_amount'],2),12)."\n\n";
	}
						
	$details .= $tender;
	if ($aCashier['tender_amount'] > $aCashier['net_amount'])
	{
		$details .= space(15).adjustSize('Change',12).'P'.
		adjustRight(number_format($aCashier['tender_amount'] - $aCashier['net_amount'],2),12)."\n";
	}

	$details .= "\n";
	$details .= space(15).adjustSize("VATable",12).' '.adjustRight(number_format($aCashier['vat_sales']-$aCashier['total_tax'],2),12)."\n";
	$details .= space(15).adjustSize("VAT Exempt",12).' '.adjustRight(number_format($aCashier['nonvat_sales'],2),12)."\n";
	$details .= space(15).adjustSize("Tax ".$SYSCONF['TAXRATE']."%",12).' '.adjustRight(number_format($aCashier['total_tax'],2),12)."\n";

	if ($aCashier['reward_total']>0)
	{
		$details .= "\n";
  		if ($customername !=  $aCashier['member_name'])
  		{
  			$details .= "[".$aCashier['member_name']."]\n";
  		}
  		$details .= "Points Earned: ".$aCashier['reward_total']."\n";
  		$details .= "Total Points : ".($aCashier['reward_total']+$aCashier['points_balance'])."\n";
	}
	$details .= str_repeat('-',40)."\n";

	if ($aCashier['status'] =='V')
	{	
		$details .= str_repeat('-',40)."\n";
		$details .= center('*** VOIDED TRANSACTION ***',40)."\n";
		$details .= str_repeat('-',40)."\n\n";
	}
	else
	{


		/**
		 * INSERT FREE ITEMS FOR PROMO HERE
		 */

		$promo_content = lib::getPromoFromSalesDb($aCashier['sales_header_id']);


		if (!empty($promo_content)) {
			$details .= center($SYSCONF['RECEIPT_PROMO_HEADER'],40)."\n";
			$details .= $promo_content ."\n";
			$details .= center($SYSCONF['RECEIPT_PROMO_FOOTER'],40)."\n";
			$details .= str_repeat('-',40)."\n\n";
		}


		if ($printagain>'1' && $aCashier['REPRINT'] != '1')
		{
			$details .= "\n\n\n";
			$details .= space(6)."---------------------------\n";
			$details .= center('['.$aCashier['cardno'].']'.$aCashier['account'],40)."\n\n";
		}

		if ($chargeinvoice == '1')
		{
			$details .= center($SYSCONF['CHARGE_FOOTER1'],40)."\n";
		}
		elseif ($SYSCONF['RECEIPT_FOOTER1'] != '')
		{
			$details .= center($SYSCONF['RECEIPT_FOOTER1'],40)."\n";
		}
			
		if ($SYSCONF['RECEIPT_FOOTER2'] != '')
		{
			if (strlen($SYSCONF['RECEIPT_FOOTER2'])<=40)
			{
				$details .= center($SYSCONF['RECEIPT_FOOTER2'],40)."\n";
			}
			else
			{
				$details .= center($SYSCONF['RECEIPT_FOOTER2'],40)."\n";
			}	
		}
		$p = "select * from terminal where definition='PERMITNO' and ip='".$SYSCONF['IP']."'";
		$q = "select * from terminal where definition='MACHINENO' and ip='".$SYSCONF['IP']."'";
		$pms = @pg_query($p);
		$rms = @pg_fetch_object($pms);
		$permit = $rms->value;
		$qms = @pg_query($q);
		$rms = @pg_fetch_object($qms);
		$machine = $rms->value;
		$details .= center('M1 Point of Sale System 1.0pg',40)."\n";
		$details .= center('Accreditation No: ',40)."\n";
		$details .= center($SYSCONF['ACCREDITATION'],40)."\n";
		$details .= center('Accreditation Date: August 24,2010',40)."\n";
		$details .= center('Permit No: '.$permit,40)."\n";
		$details .= center('Machine No: '.$machine,40)."\n";
	}
	
	$details .=	"\n\n\n\n\n\n\n";
	
 	$receiptfile= '../log/RECEIPT'.$aip[3].'.txt';
	//galert($details);
	@writefile($details, true, $receiptfile);
	$rmsg = nPrinter($details, $SYSCONF['RECEIPT_PRINTER_TYPE'], $SYSCONF['RECEIPT_PRINTER_DEST']);

	if ($rmsg == '')
	{
		if ($SYSCONF['CUTTER'] == 'Y')
		{
			nPrinter("<cutterm>", $SYSCONF['RECEIPT_PRINTER_TYPE'], $SYSCONF['RECEIPT_PRINTER_DEST']);
		}
	}
	else
	{
		galert("Transaction SAVED but ".$rmsg."\nPlease Start Printing Utility or Re-Start Computer, then Re-Print Invoice");
	}

	return $printagain;
}
?>
