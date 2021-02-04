<?
global $SYSCONF, $aCashier, $aItems;
$details = $tender = '';
$no_items = 0;
/*

		if ($SYSCONF['IP']== ''  || $ADMIN['sessionid']=='')
		{
			
			$SYSCONF='';
			$ADMIN='';
			session_unset();
			echo "<script>window.location='../'</script>";
			exit;
		}

		include_once('cashier.func.php');
		include_once('../lib/dbconfig.php');
		
		$DBCONN = pg_Connect($DBCONNECT) or die("Can't connect to server...");

*/

$details = '';
if ($aCashier['REPRINT'] == '1')
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
	if ($SYSCONF['DRAWER'] == 'COM1XX')
	{
		nPrinter("<drawer0>", $SYSCONF['RECEIPT_PRINTER_TYPE'], $SYSCONF['RECEIPT_PRINTER_DEST']);
	}	
	elseif ($SYSCONF['DRAWER'] == 'LPT')
	{
		nPrinter("<drawer>", $SYSCONF['RECEIPT_PRINTER_TYPE'], $SYSCONF['RECEIPT_PRINTER_DEST']);
	}
}

$minvoice = ($aCashier['invoice']==''?$aCashier['sales_header_id']:$aCashier['invoice']);
$cashiername = lookUpTableReturnValue('x','admin','admin_id','name',$aCashier['admin_id']);

 
$details .= center(strtoupper($SYSCONF['BUSINESS_NAME']),38)."\n".
			center('BOWLING',38)."\n".
			center($SYSCONF['BUSINESS_ADDR'],38)."\n";
if ($SYSCONF['BUSINESS_TEL'] != '')
{
	$details .= center($SYSCONF['BUSINESS_TEL'],38)."\n";
}
if ($SYSCONF['PROPRIETOR'] != '')
{
	$details .= center($SYSCONF['PROPRIETOR'],38)."\n";
}
$details .= center($SYSCONF['BUSINESS_TIN'],38)."\n".
	adjustSize('Date:'.ymd2mdy($aCashier['date']),21).' '.
	adjustSize('Time:'.$aCashier['time'],18)."\n".
	adjustSize('Term/Serial No.',21).' '.
	adjustSize($SYSCONF['TERMINAL'].'/'.$SYSCONF['SERIAL'],18)."\n".
	adjustSize('Cashier:'.substr($cashiername,0,13),21).' '.
	adjustSize('CSI#:'.$minvoice,18)."\n".
	
	adjustSize('Name:'.($aCashier['account']==''?str_repeat('_',35): htmlspecialchars($aCashier['account'])),40)."\n".
	'Description     Qty    Price   Total'."\n".
	str_repeat('-',40)."\n";
	
			
$tender = $servicecharge = "";		
foreach ($aItems as $temp)
{
	if ($temp['type'] == 'Tender')
	{
		if ($temp['cardno'] != '')
		{
			$tender .= adjustSize(substr($temp['cardno'],0,16),16).' '.
				adjustSize(substr($temp['tender'],0,10),10).'P'.
				adjustRight(number_format($temp['amount'],2),12)."\n";
		}
		else
		{
			$tender .= space(15).adjustSize(substr($temp['tender'],0,12),12).'P'.
				adjustRight(number_format($temp['amount'],2),12)."\n";
		}
	}
	elseif ($temp['type'] == 'ServiceCharge')
	{
			$service .= space(11).' '.
				adjustSize(substr('Service Charge ',0,15),15).'P'.
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
		
		$details .= adjustSize(substr($temp['stock'],0,13),13).' '.
			adjustRight($cqty,6).' '.
			adjustRight(number_format($temp['price'],2),8).' '.
			adjustRight(number_format($temp['amount'],2),10)."\n";
		$no_items += $temp['qty'];						
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
			$details .= space(15).adjustSize('Discount',12).'P'.
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
  $details .= "\nPoints Earned: ".$aCashier['reward_total']."\n";
  $details .= "Total Points : ".$aCashier['reward_total']+$aCashier['points_balance']."\n";
}
$details .= str_repeat('-',40)."\n";

if ($aCashier['status'] =='V')
{
	$details .= str_repeat('-',40)."\n";
	$details .= center('*** VOIDED TRANSACTION ***',40)."\n";
	$details .= str_repeat('-',40)."\n\n";
}
elseif ($aCashier['account_id'] != '' && $aCashier['REPRINT'] != '1')
{
	$details .= "\n\n\n";
	$details .= space(6)."---------------------------\n";
	$details .= center('['.$aCashier['cardno'].']'.$aCashier['account'],40)."\n";
}

if ($SYSCONF['RECEIPT_FOOTER1'] != '')
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
$details .=	"\n\n\n\n\n\n\n";

//echo "<pre>$details</pre>";
//glayer('message.layer',$details);
//print_r($SYSCONF);
//echo "Type ".$SYSCONF['RECEIPT_PRINTER_TYPE']."DEST ".$SYSCONF['RECEIPT_PRINTER_DEST'];
//nPrinter($details, 'LINUX LP Printer','10.0.0.122');

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
?>
