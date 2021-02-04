<?
function eoday($mfrom_date, $mto_date)
{
	
	$aPost = null;
	$aPost = array();
	if ($mfrom_date == '' && $mto_date == '')
	{
		$aPost['Ok'] = 0;
		$aPost['message'] .= 'Invalid Dates '.$mfrom_date .' To '.$mto_date;
		return $aPost;
	}

	$Ok=1;
	$tables = currTables($mfrom_date);
	$sales_header = $tables['sales_header'];
	$sales_detail = $tables['sales_detail'];
	$sales_tender = $tables['sales_tender'];

	$q = "select
				
				sum(net_amount) as total_amount,
				sum(item_lines) as lines,
				sum(units) as units,
				count(*) as mcount,
				date
			from
				$sales_header as sh
			where
				date>='$mfrom_date' and
				sh.status!='V'";
	if ($mto_date != '')
	{
		$q .= " and date <= '$mto_date'";
	}

	$q .= " group by sh.date ";
	
	$qr = @pg_query($q);
	if (!$qr)
	{
		$Ok = 0;
		$message .= 'Error Query Sales Header '.pg_errormessage();
	}
	
	while ($r = @pg_fetch_object($qr))
	{
	
		$grocery_amount = $drygood_amount  = 0;
		$qq = "select sum(amount) as drygood_amount
					from
						$sales_header as sh,
						$sales_detail as sd,
						stock,
						category
					where
						sd.sales_header_id=sh.sales_header_id and
						stock.stock_id = sd.stock_id and
						category.category_id = stock.category_id and
						category.department = 'D' and
						sh.date='$r->date' and
						sh.status !='V'";
		$qqr = @pg_query($qq);
		if (!$qqr)
		{
			$Ok =0;
			$message .= 'Error Query by Sales Department: '.pg_errormessage();
		}
		
		if (@pg_num_rows($qqr) > 0)
		{
			$rr = @pg_fetch_object($qqr);
			$drygood_amount = $rr->drygood_amount;
		}
		$grocery_amount = $r->total_amount - $drygood_amount;
								
		$qq = "select date from eoday where date='$r->date'";
		$qqr = @pg_query($qq);
		if (!$qqr)
		{
			$Ok =0;
			$message .= 'Error Query Checking Eoday Table: '.pg_errormessage();
		}
		if (@pg_num_rows($qqr) == 0)
		{
			$qq = " insert into eoday 
						(date, mcount, lines, units, total_amount, drygood_amount, grocery_amount)
					values
						('$r->date', '$r->mcount','$r->lines','$r->units','$r->total_amount',
						'$drygood_amount', '$grocery_amount')";
		}
		else
		{
			$qq = " update eoday set
							mcount = '$r->mcount',
							lines = '$r->lines',
							units = '$r->units',
							total_amount = '$r->total_amount',
							drygood_amount = '$drygood_amount',
							grocery_amount = '$grocery_amount'
						where
							date = '$r->date'";
							
		}					
		$qqr = @pg_query($qq);
		if (!$qqr)
		{
			$Ok = 0;
			$message .= 'Error Updating Eoday Table: '.pg_errormessage();
		}

					 
	}
	$aPost['Ok'] = $Ok;
	$aPost['message'] = $message;
	return $aPost;
}

function recalcUnits($mdate=null)
{
	if ($mdate == '')
	{
		$mdate = date('Y-m-d');
	}
	$tables = currTables($mdate);
	$sales_header = $tables['sales_header'];
	$sales_detail = $tables['sales_detail'];
	$sales_tender = $tables['sales_tender'];

	$q = "select
				sum(qty) as units,
				sales_header_id
			from
				$sales_detail
			group by
				sales_header_id";
				
	$qr = @pg_query($q) or die (pg_errormessage().$q);
	while ($r = @pg_fetch_object($qr))
	{
		$q = "update 
					$sales_header 
				set
					units = '$r->units'
				where
					sales_header_id = '$r->sales_header_id'";
		@pg_query($q) or die (pg_errormessage().$q);
	}	
	
} 

?>