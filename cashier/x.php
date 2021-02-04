<?
/*
if (!chkRights2('salesreports','mview',$ADMIN['admin_id']))
{
	$message = 'Access Denied. Sales Report Generation...';
}
else
{




/*		if ($SYSCONF['IP']== ''  || $ADMIN['sessionid']=='')
		{
			
			$SYSCONF='';
			$ADMIN='';
			session_unset();
			echo "<script>window.location='../'</script>";
			exit;
		}

*/
		include_once('../lib/library.php');
		include_once('../lib/dbconfig.php');
		
		$DBCONN = pg_Connect($DBCONNECT) or die("Can't connect to server...");
		
		$date = date("m/d/Y");
		$mdate = date('Y-m-d');
		$q = "select * from area where area_id='".$SYSCONF['AREA_ID']."'";
		$qr = @pg_query($q);
		$r = @pg_fetch_object($qr);
	
		$from_category_id = $r->from_category_id;
		$to_category_id = $r->to_category_id;

		$term = terminal($SYSCONF['TERMINAL']);

//		$term['ip'] = '127.0.0.1';
		
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
		
		$from_category_code = lookUpTableReturnValue('x','category','category_id','category_code',$from_category_id);
		$to_category_code = lookUpTableReturnValue('x','category','category_id','category_code',$to_category_id);
		
		$q = "select 
					sum(rd.qty) as qty,
					sum(rd.amount) as amount,
					category.category,
					stock.category_id,
					category.category_code
				from
					sales_detail as rd,
					sales_header as rh,
					stock,
					category
				where
					rh.sales_header_id=rd.sales_header_id and
					stock.stock_id=rd.stock_id and
					category.category_id=stock.category_id and 
					rh.date = '$mdate' and
					(rh.status != 'V' and rh.status !='C')";
 
		if ($terminal != '')
		{
			$q .= "and ip='".$term['IP']."'";
		}
					
		if ($from_category_id != '')
		{
			$q .= " and	category.category_code>='$from_category_code'";
		}		
		if ($to_category_id != '')
		{
			$q .= " and	category.category_code<='$to_category_code'";
		}		

		$q .= "	group by 
					stock.category_id , category.category, category.category_code
				order by 
					category.category_code";	

		$qr = @pg_query($q)
		if (!$qr)
		{
			$message = 'Error Reading...'.pg_errormessage();
		}

		$total_amount = 0;
		$total_qty = 0;
		$ctr = 0;
		
		$data = null;
		$leg = null;
		$data = array();
		$leg = array();
		while ($r = pg_fetch_object($qr))
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
		$details2 .= $header.$details;

		nPrinter($details2, $SYSCONF['RECEIPT_PRINTER_TYPE'], $SYSCONF['RECEIPT_PRINTER_DEST']);
		
		if ($SYSCONF['CUTTER'] == 'Y')
		{
			nPrinter("<cutterm>", $SYSCONF['RECEIPT_PRINTER_TYPE'], $SYSCONF['RECEIPT_PRINTER_DEST']);
		}		
		//echo "<pre>$details2</pre>";

}
*/
?> 
