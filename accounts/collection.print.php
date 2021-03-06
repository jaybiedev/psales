<?
$details = $tender = '';
$no_items = 0;

$details = '';
if ($aColl['REPRINT'] == '1')
{
	$details .= str_repeat('-',40)."\n";
	$details .= center('RE-PRINT RECEIPT',40)."\n";
	$details .= str_repeat('-',40)."\n\n";
}
elseif ($aColl['status'] == 'V')
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

$minvoice = str_pad($aColl['collection_id'],8,'0',str_pad_left); //($aColl['invoice']==''?$aColl['collection_id']:$aColl['invoice']);
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
	adjustSize('Date:'.ymd2mdy($aColl['date']),21).' '.
	adjustSize('Time:'.$aColl['time'],18)."\n".
	adjustSize('Term/Serial No.',21).' '.
	adjustSize($SYSCONF['TERMINAL'].'/'.$SYSCONF['SERIAL'],18)."\n".
	adjustSize('Cashier:'.substr($cashiername,0,13),21).' '.
	adjustSize('Receipt#:'.$minvoice,18)."\n".
	
	adjustSize('Name:'.($aColl['account']==''?str_repeat('_',35):$aColl['account']),40)."\n".
	'Description                  Amount'."\n".
	str_repeat('-',40)."\n";
	
$details .= adjustSize("Payment for  : ".$aColl['cardno'],28).' '.
	adjustRight(number_format($aColl['amount_total'],2),11)."\n";
$details .= '  '.substr($aColl['account'],0,37)."\n";
$details .= str_pad('Cash',15,'.').adjustRight(number_format($aColl['amount_cash'],2),12)."\n";
if ($aColl['amount_check'] != '0')
{
	$details .= str_pad('Check',15,'.').adjustRight(number_format($aColl['amount_check'],2),12)."\n";
	$details .= str_pad('Check No.',15,'.').adjustSize($aColl['mcheck'],20)."\n";
	$details .= str_pad('Check date',15,'.').ymd2mdy($aColl['date_check'],12)."\n";
}
$details .= adjustSize('Interest',15).'('.adjustRight(number_format($aColl['interest'],2),12).')'."\n";
$details .= adjustSize('Principal',15).'('.adjustRight(number_format($aColl['principal'],2),12).')'."\n";
$details .= adjustSize('Advances',15).'('.adjustRight(number_format($aColl['advance'],2),12).')'."\n";

//$details .= adjustSize("Club Bonus Points Earned.....",30).' '.$aColl['rewards_points']."\n";
//$details .= adjustSize("Total Club Bonus Points......",30).' '.$aColl['total_rewards_points']."\n";

if ($aColl['reward_total']>0)
{
  $details .= "\nClub Bonus Points Earned: ".$aColl['reward_total']."\n";
  $details .= "Total Club Bonus Points : ".$aReward['points_balance']."\n\n";
}
$details .=	str_repeat('-',40)."\n";

if ($aColl['status'] =='V')
{
	$details .= str_repeat('-',40)."\n";
	$details .= center('*** VOIDED TRANSACTION ***',40)."\n";
	$details .= str_repeat('-',40)."\n\n";
}

if ($SYSCONF['RECEIPT_FOOTER1'] != '')
{
	$details .= center($SYSCONF['RECEIPT_FOOTER1'],40)."\n";
}	

if ($SYSCONF['RECEIPT_FOOTER2'] != '' && 0)
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
