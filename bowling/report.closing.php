<?
function closing()
{

	global $SYSCONF, $ADMIN;
	

	if (!chkRights2('cashierreports','mview',$ADMIN['admin_id']))
	{
		return "You have NO permission to Generate Cashier Report...";
	}
	else
	{

		$date = date("m/d/Y");
		$mdate = date('Y-m-d');
		$terminal = $SYSCONF['TERMINAL'];
		

		$tables = $SYSCONF['tables'];
		$sales_header = $tables['sales_header'];
		$sales_detail = $tables['sales_detail'];
		$sales_tender = $tables['sales_tender'];
/*
		$q = "select * 
					from 
						$sales_header 
					where 
						post='N' and
						date='$mdate' and
						terminal='$terminal'
					offset 0 limit 1";
		$qr = @pg_query($q);
		if (!$qr)
		{
			galert(pg_errormessage().$q);
			return 0;
		}
		
		if (@pg_num_rows($qr) > 0)
		{
			galert("WARNING!! WARNING!!\n CAN NOT PERFORM CLOSING. Journal Report was NOT Performed Yet!!!");
			return 0;
		}
*/

		if ($SYSCONF['DRAWER'] == 'COM1XX')
		{
			nPrinter("<drawer0>", $SYSCONF['RECEIPT_PRINTER_TYPE'], $SYSCONF['RECEIPT_PRINTER_DEST']);
		}	
		elseif ($SYSCONF['DRAWER'] == 'LPT')
		{
			nPrinter("<drawer>", $SYSCONF['RECEIPT_PRINTER_TYPE'], $SYSCONF['RECEIPT_PRINTER_DEST']);
		}

				
		$last_invoice = '';
		$q = "select * from invoice where ip='".$_SERVER['REMOTE_ADDR']."'";
		$qr = @pg_query($q);
		$r =  @pg_fetch_object($qr);
		if ($r)
		{
			$last_invoice = str_pad($r->invoice,8,'0', str_pad_left);
		}
		else
		{
			$last_invoice = '00000000';
		}	
	
		
		$q = "select * 
					from 
						userlog 
					where 
						substr(date_in,1,10)='$mdate' and 
						admin_id='".$ADMIN['admin_id']."' and 
						ip='".$_SERVER['REMOTE_ADDR']."' and
						last_invoice='LOGIN'
					order by 
						userlog_id desc";
					//offset 0 limit 1";
					
		$qr = @pg_query($q);
		if (@pg_num_rows($qr) == 0)
		{
			$q = "select * 
					from 
						userlog 
					where 
						substr(date_in,1,10)='$mdate' and
						last_invoice='LOGIN' and  
						admin_id='".$ADMIN['admin_id']."' 
					order by 
						userlog_id desc
					offset 0 limit 1";
					
			$qr = @pg_query($q);
		}
		
		$uid = $signout = ''; 
		while ($r = @pg_fetch_object($qr))
		{
			$signin = $r->date_in;
			if ($uid == '')
			{
				$uid = $r->userlog_id;
			}
			if ($r->last_invoice != 'LOGIN') break;
		}
		$signout = date('Y-m-d g:ia');
		$remarks = 'ZREAD : '.date('m/d/Y g:ia');
		
		$q = "update userlog set last_invoice='$last_invoice', 
						remarks='$remarks' 
					where 
						userlog_id='$uid'";
		$qr = @pg_query($q);

		if (!$qr)
		{
			$err = pg_errormessage().$q;
		  	$aip = explode('.',$_SERVER['REMOTE_ADDR']);
		  	$reportfile= '/data/cache/ERROR'.$aip[3].'.txt';
		  	$fo = @fopen($reportfile,'a+');
		  	@fwrite($fo, $err);
			@fclose($fo);
		}
		$term = terminal($SYSCONF['TERMINAL']);

		
		$header ="\n";
		$header .= $SYSCONF['BUSINESS_NAME']."\n";
		$header .= $SYSCONF['BUSINESS_ADDR']."\n";
		$header .= 'Register :'.$term['TERMINAL']."\n";
		$header .= 'Serial   :'.$term['SERIAL']."\n";
		$header .= 'Transaction Date : '.$date."\n";
		$header .= 'Printed          : '.date('m/d/Y g:ia')."\n\n";
		$header .= '-------------------- ------- -----------'."\n";
		$header .= center('TERMINAL  '.$SYSCONF['TERMINAL'],40)."\n";
		$header .= center('SIGN-IN '.$signin,40)."\n";
		$header .= center('SIGN-OUT '.$signout,40)."\n";
		$header .= center('LAST INVOICE  '.$last_invoice,40)."\n";
		$header .= '  **   END OF DAY CLOSING PERFORMED **'."\n";
		$header .= center('by : '.$ADMIN['name'],40)."\n";
		$header .= center(date('m/d/Y g:ia'),40)."\n";
		$header .= '========================================'."\n";
		$header .= "\n\n\n\n\n\n\n\n";

		nPrinter($header, $SYSCONF['RECEIPT_PRINTER_TYPE'], $SYSCONF['RECEIPT_PRINTER_DEST']);
		
		if ($SYSCONF['CUTTER'] == 'Y')
		{
			//nPrinter("<cutterm>", $SYSCONF['RECEIPT_PRINTER_TYPE'], $SYSCONF['RECEIPT_PRINTER_DEST']);
		}		
		
		//echo "<pre>$header</pre>";

		$q = "update $sales_header set
					post='Y'
				where
					terminal='$terminal' and
					post='N' and 
					date='$mdate'";
		$qr = @pg_query($q);
//					admin_id='".$ADMIN['admin_id']."' and

		
	  $aip = explode('.',$_SERVER['REMOTE_ADDR']);
	  $reportfile= '/data/cache/REPORT'.$aip[3].'.txt';
	  $fo = @fopen($reportfile,'a+');
	  @fwrite($fo, $header);
	  @fclose($fo);
		
	
		return 1;

	}


}	
?>
