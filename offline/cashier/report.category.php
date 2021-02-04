<?
global $SYSCONF;

$tables = $SYSCONF['tables'];
$sales_header = $tables['sales_header'];
$sales_detail = $tables['sales_detail'];
$sales_tender = $tables['sales_tender'];

if (!chkRights2('cashierreports','mview',$ADMIN['admin_id']))
{
		galert("You have NO permission to Generate Cashier Report...");
		return;
}
elseif ($SYSCONF['AREA_ID'] =='')
{
	galert("No Area Id for this terminal. Please inform EDP");
	return;
}
else
{

		$date = date("m/d/Y");
		$mdate = date('Y-m-d');
		$q = "select * from area where area_id='".$SYSCONF['AREA_ID']."'";
		$qr = @pg_query($q);
		$r = @pg_fetch_object($qr);
	
		$from_category_code = $r->from_category;
		$to_category_code = $r->to_category;

		$term = terminal($SYSCONF['TERMINAL']);
		$terminal = $SYSCONF['TERMINAL'];

/*		
		$mdate = '2007-01-15';
		$terminal = '02';
		$sales_header = 'sales_header';
		$sales_detail = 'sales_detail';
		$sales_tender = 'sales_tender';
*/		
		$header ="\n";
		$header .= $SYSCONF['BUSINESS_NAME']."\n";
		$header .= $SYSCONF['BUSINESS_ADDR']."\n";
		$header .= 'CATEGORY SALES REPORT'."\n";
		$header .= 'Register :'.$term['TERMINAL']."\n";
		$header .= 'Serial   :'.$term['SERIAL']."\n";
		$header .= 'Transaction Date : '.$date."\n";
		$header .= 'Printed          : '.date('m/d/Y g:ia')."\n\n";
		$header .= ' Category             Items    Amount'."\n";
		$header .= '-------------------- ------- -----------'."\n";
		$page = 1;
		$lc = 10;
		$details = '';
		$details2 = '';

		
	//	$from_category_code = lookUpTableReturnValue('x','category','category_id','category_code',$from_category_id);
//		$to_category_code = lookUpTableReturnValue('x','category','category_id','category_code',$to_category_id);

		$from_len = strlen($from_category_code);
		$to_len = strlen($to_category_code);
		
		$q = "select 
					sum(rd.qty) as qty,
					sum(rd.amount) as amount,
					category.category,
					stock.category_id,
					category.category_code
				from
					$sales_detail as rd,
					$sales_header as rh,
					stock,
					category
				where
					rh.sales_header_id=rd.sales_header_id and
					stock.stock_id=rd.stock_id and
					category.category_id=stock.category_id and 
					rh.date = '$mdate' and	
					terminal='$terminal' and
					rh.status != 'V' and 
					rh.status !='C'and
					post='N'";
 
					
		if ($from_category_code != '')
		{
			$q .= " and substr(category.category_code,1, $from_len) >='$from_category_code'";
		}		
		if ($to_category_code != '')
		{
			$q .= " and substr(category.category_code,1,$to_len) <='$to_category_code'";
		}		

		$q .= "	group by 
					stock.category_id , category.category, category.category_code
				order by 
					category.category_code";	

		$qr = @pg_query($q);
		//$details = $q."\n";

		$total_amount = 0;
		$total_qty = 0;
		$ctr = 0;
		
		$data = null;
		$leg = null;
		$data = array();
		$leg = array();
		while ($r = pg_fetch_object($qr) )
		{
			$data[] = $r->amount;
			$leg[] = $r->category;
			
			if (intval($r->qty) != $r->qty)
			{
				$cqty = number_format($r->qty,3);
			}
			else
			{
				$cqty = number_format($r->qty,0);
			}
			$ctr++;
			$details .= adjustRight($r->category_code,4).' '.
						adjustSize($r->category,15).' '.
						adjustRight($cqty,7).' '.
						adjustRight(number_format($r->amount,2),11)."\n";
			$total_amount += $r->amount;
			$total_qty += $r->qty;			
			$lc++;
		}
		$details .= '----------------------------------------'."\n";
		$details .= adjustSize($ctr.' Total Items',15).space(2).
						adjustRight(number_format($total_qty,3),10).' '.
						adjustRight(number_format($total_amount,2),12)."\n";
		$details .= '========================================'."\n";
/*

		$q = "select sum(net_amount) as net_amount 
					from
						$sales_header
					where
						terminal='$terminal' and
						status != 'V' and 
						status != 'C' and 
						date='$mdate' and
						post='N'";
		$qr = @pg_query($q);
		if (!$qr)
		{
			galert('Error Querying Invoices...'.pg_errormessage());
		}
		$r = @pg_fetch_object($qr);
		$details .= adjustSize('Total Sales',15).space(13).
						adjustRight(number_format($r->net_amount,2),12)."\n";
*/
		$details .= "\n\n\n\n\n\n\n\n";
		$details2 .= $header.$details;

		nPrinter($details2, $SYSCONF['RECEIPT_PRINTER_TYPE'], $SYSCONF['RECEIPT_PRINTER_DEST']);
		
		if ($SYSCONF['CUTTER'] == 'Y')
		{
			nPrinter("<cutterm>", $SYSCONF['RECEIPT_PRINTER_TYPE'], $SYSCONF['RECEIPT_PRINTER_DEST']);
		}		
		//echo "<pre>$details2</pre>";
		
	  $aip = explode('.',$_SERVER['REMOTE_ADDR']);
	  $reportfile= '/prog/cache/REPORT'.$aip[3].'.txt';
	  $fo = @fopen($reportfile,'a+');
	  @fwrite($fo, $details2);
	  @fclose($fo);

		
}
?>
