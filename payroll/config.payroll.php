<?
	$BUSINESS_NAME = "Hope ePayroll System";
	$BUSINESS_ADDR = "Bacolod City";
	
	include_once("../lib/dbconfig.php");
	include_once("../lib/connect.php");
	include_once('../var/system.conf.php');
	
	$qr = @pg_query("select * from payroll.payroll_period where active = '1'") or message (pg_errormessage());
	if (@pg_numrows($qr)>0)
	{
		$r=@pg_fetch_object($qr);
		
		$PAYROLL_PERIOD=ymd2mdy($r->period1).'-'.ymd2mdy($r->period2)."($r->days days)";
		$PAYROLL_PERIOD_ID=$r->payroll_period_id;
		$DAYS = $r->days;
		$SCHEDULE = $r->schedule;
		$NUM = $r->num;
		$NUM2 = $r->num2;

		$SYSCONF['PAYROLL_PERIOD'] = $PAYROLL_PERIOD;
		$SYSCONF['PAYROLL_PERIOD_ID'] = $PAYROLL_PERIOD_ID;
		$SYSCONF['DAYS'] = $DAYS;
		$SYSCONF['SCHEDULE'] = $SCHEDULE;
		$SYSCONF['NUM'] = $NUM;
		$SYSCONF['NUM2'] = $NUM2;
		$SYSCONF['PERIOD1'] = $r->period1;
		$SYSCONF['PERIOD2'] = $r->period2;
		$SYSCONF['POST'] = $r->post;
		$SYSCONF['MONTH'] = $r->month;
		$SYSCONF['YEAR'] = $r->year;
		
		
	}
	else
	{
		$PAYROLL_PERIOD = 'NOT SELECTED';
		$SYSCONF['PAYROLL_PERIOD'] = $PAYROLL_PERIOD;
	}
?>	