<?
global $SYSCONF;
//		$SYSCONF['TERMINAL'] = '21';		
//		$SYSCONF['AREA_ID'] =1;
$tables = $SYSCONF['tables'];
$sales_header = $tables['sales_header'];
$sales_detail = $tables['sales_detail'];
$sales_tender = $tables['sales_tender'];

if (!chkRights2('cashierreports','mview',$ADMIN['admin_id']))
{
	$message = 'Access Denied. Sales Report Generation...';
}
elseif ($SYSCONF['AREA_ID'] =='')
{
	$message = 'No Area Id for this terminal. Please inform EDP';
}
else
{

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
		$date = date("m/d/Y");
		$mdate = date('Y-m-d');
//		$mdate = '2006-06-23';
		$q = "select * from area where area_id='".$SYSCONF['AREA_ID']."'";
		$qr = @pg_query($q);
		$r = @pg_fetch_object($qr);
	
		$from_category_code = $r->from_category;
		$to_category_code = $r->to_category;

		$term = terminal($SYSCONF['TERMINAL']);

		
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
					terminal='".$SYSCONF['TERMINAL']."' and
					(rh.status != 'V' and rh.status !='C')";
 
					
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

		$total_amount = 0;
		$total_qty = 0;
		$ctr = 0;
		
		$data = null;
		$leg = null;
		$data = array();
		$leg = array();
//$details .= "Recs ".@pg_num_rows($qr)."\n";
//$details .= "from ".$r->from_category_id." ".$q."\n";
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
/*
			if ($p1 == 'Print Draft' && $lc > 58)
			{
				$details .= "Page ".$page."<eject>\n\n";
				doPrint($header.$details);
				$lc=10;
				$page++;
				$details2 .= $header.$details;
				$details = '';
			}
*/			
		}
		$details .= '----------------------------------------'."\n";
		$details .= adjustSize($ctr.' Total Items',15).space(2).
						adjustRight(number_format($total_qty,3),10).' '.
						adjustRight(number_format($total_amount,2),12)."\n";
		$details .= '========================================'."\n";
		$details .= "\n\n\n\n\n\n\n\n";
		$details2 .= $header.$details;

		nPrinter($details2, $SYSCONF['RECEIPT_PRINTER_TYPE'], $SYSCONF['RECEIPT_PRINTER_DEST']);
		
		if ($SYSCONF['CUTTER'] == 'Y')
		{
			nPrinter("<cutterm>", $SYSCONF['RECEIPT_PRINTER_TYPE'], $SYSCONF['RECEIPT_PRINTER_DEST']);
		}		
		//echo "<pre>$details2</pre>";

}
?>
