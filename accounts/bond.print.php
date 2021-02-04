<?
$details = $tender = '';
$no_items = 0;

$details = '';
if ($aBOND['REPRINT'] == '1')
{
	$details .= str_repeat('-',40)."\n";
	$details .= center('RE-PRINT RECEIPT',40)."\n";
	$details .= str_repeat('-',40)."\n\n";
}
elseif ($aBOND['status'] == 'V')
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

$minvoice = str_pad($aBOND['bondledger_id'],8,'0',str_pad_left); //($aBOND['invoice']==''?$aBOND['collection_id']:$aBOND['invoice']);
$cashiername = lookUpTableReturnValue('x','admin','admin_id','name',$ADMIN['admin_id']);

 
$details .= center(strtoupper($SYSCONF['BUSINESS_NAME']),38)."\n".
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
	adjustSize('Date:'.ymd2mdy($aBOND['date']),21).' '.
	adjustSize('Time:'.$aBOND['time'],18)."\n".
	adjustSize('Term/Serial No.',21).' '.
	adjustSize($SYSCONF['TERMINAL'].'/'.$SYSCONF['SERIAL'],18)."\n".
	adjustSize('Cashier:'.substr($cashiername,0,13),21).' '.
	adjustSize('Receipt#:'.$minvoice,18)."\n".
	
	adjustSize('Name:'.($aBOND['account']==''?str_repeat('_',35):$aBOND['account']),40)."\n".
	'Description                  Amount'."\n".
	str_repeat('-',40)."\n";
	
if ($aBOND['debit'] > 0)
{
	$details .= adjustSize("Bond Deposit for: ".$aBOND['cardno'],28).' '.
		adjustRight(number_format($aBOND['debit'],2),11)."\n";
}
elseif ($aBOND['credit'] > 0)
{
	$details .= adjustSize("Bond Withdrawal for: ".$aBOND['cardno'],28).' '.
		adjustRight(number_format($aBOND['credit'],2),11)."\n";
}
$details .= '  '.substr($aBOND['account'],0,37)."\n\n";
if ($aBOND['amount_check'] != '0')
{
	$details .= str_pad('Check',15,'.').adjustRight(number_format($aBOND['amount_check'],2),12)."\n";
	$details .= str_pad('Check No.',15,'.').adjustSize($aBOND['mcheck'],20)."\n";
	$details .= str_pad('Check date',15,'.').ymd2mdy($aBOND['date_check'],12)."\n";
}

$details .=	str_repeat('-',40)."\n";

if ($aBOND['status'] =='V')
{
	$details .= str_repeat('-',40)."\n";
	$details .= center('*** VOIDED TRANSACTION ***',40)."\n";
	$details .= str_repeat('-',40)."\n\n";
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

nPrinter($details, $SYSCONF['RECEIPT_PRINTER_TYPE'], $SYSCONF['RECEIPT_PRINTER_DEST']);

if ($SYSCONF['CUTTER'] == 'Y')
{
	nPrinter("<cutterm>", $SYSCONF['RECEIPT_PRINTER_TYPE'], $SYSCONF['RECEIPT_PRINTER_DEST']);
}

?>
