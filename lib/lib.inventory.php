<?php
require_once(dirname(__FILE__).'/../lib/lib.salvio.php');

class Inventory{

	public static function getCurrentBalance($stock_id,$date){		
		$aBeg    = self::getStockBalance($stock_id,'',$date,FALSE);
		$beg_qty = $aBeg['balance_qty'];
		return $beg_qty;
	}

	public static function getCurrentBalanceInWords($stock_id,$date){
		$bal = self::getCurrentBalance($stock_id, $date);
		$fraction3 = lib::getAttribute('stock','stock_id',$stock_id,'fraction3');

	
		if ( in_array($fraction3, array(0,1)) ) {
			return "0 case/s $bal unit/s";
		} 

		$case = $bal % $fraction3;

		$lcase = intval($bal/$fraction3);
		$lunits = $bal - $lcase * $fraction3; 

		return "$lcase case/s $lunits unit/s";

	}

	public static function getStockBalanceFromCategory($account_id, $from_category, $to_category,$date){

		

		$beg_qty = 0;

	
		$current_year = date("Y",strtotime($date));
		$first_day_of_current_year = "$current_year-01-01";
		$year_minus_1 = date("Y",strtotime($date)) - 1;		
	
		
		#STOCKS RECEVING + ADJUSTING ENTRIES - SALES + PURCHASE RETURNS
		$balance_qty = $in_qty = $out_qty = 0;
		$rr_qty = $adj_qty = $sales_qty = $por_qty = 0;
		
		$tables       = lib::currTables($date);
		$sales_header = $tables['sales_header'];
		$sales_detail = $tables['sales_detail'];

		
		//stock receiving
		$queries = array();

		$queries['rr'] = "
			select 
				sum(rr_detail.case_qty*stock.fraction3 + rr_detail.unit_qty) as in_qty,
				0 as out_qty,
				stock.stock_id
			from 
				rr_header 
				inner join rr_detail on rr_detail.rr_header_id = rr_header.rr_header_id 
					and rr_header.status != 'C'
				inner join stock on stock.stock_id = rr_detail.stock_id
					and stock.enable = 'Y'
					and stock.inventory = 'Y'
					and stock.account_id = '$account_id'
				inner join category on category.category_id = stock.category_id
		";

		if (!empty($from_category) && !empty($to_category) ) {
			$queries['rr'] .= "
				and category_code >= '$from_category'
				and category_code <= '$to_category'
			";
		}

		$queries['rr'] .= "				
			where
				date <= '$date'
			group by stock.stock_id
		";

		echo empty($from_category);

		//die($queries['rr']);

		//adjusting entries
		$queries['adjustments'] = "
			select 
				sum(case_qty*fraction3 + unit_qty) as in_qty,
				0 as out_qty,
				stock.stock_id
			from 
				invadjust_header 
				inner join invadjust_detail on invadjust_detail.invadjust_header_id = invadjust_header.invadjust_header_id
					and invadjust_header.branch_id = '1'
					and invadjust_header.status != 'C'
				inner join stock on stock.stock_id = invadjust_detail.stock_id
					and stock.enable = 'Y'
					and stock.inventory = 'Y'
					and stock.account_id = '$account_id'
				inner join category on category.category_id = stock.category_id ";
		if ( !empty ($from_category) && !empty($to_category)) {
			$queries['adjustments'] .= "
				and category_code >= '$from_category'
				and category_code <= '$to_category'
			";
		}

		$queries['adjustments'] .= "				
			where
				date <= '$date'	
			group by stock.stock_id
		";
		//die($queries['adjustments']);

		/* do not get sales if last day of the year */
		
		//sales
		/*$queries['sales'] = "
			select 
				0 as in_qty,
				sum(fraction*qty) as out_qty,
				stock.stock_id
			from 
				$sales_header as sh,
				$sales_detail as sd,
				stock,
				category
			where
				sh.sales_header_id = sd.sales_header_id
			and sd.stock_id = stock.stock_id
			and stock.category_id = category.category_id
			and sh.status != 'V'
			and stock.enable = 'Y'
			and stock.inventory = 'Y'
			and stock.account_id = '$account_id'
			and category_code >= '$from_category'
			and category_code <= '$to_category'
			and date <= '$date'
			group by stock.stock_id
		";*/

		//sales
		$queries['sales'] = "
			select 
				0 as in_qty,
				sum(quantity) as out_qty,
				stock.stock_id
			from 
				sales,
				stock,
				category
			where
				sales.stock_id = stock.stock_id
			and stock.category_id = category.category_id
			and stock.enable = 'Y'
			and stock.inventory = 'Y'
			and stock.account_id = '$account_id'
		";

		if ( !empty($from_category) && !empty($to_category)) {
			$queries['sales'] .= "
				and category_code >= '$from_category'
				and category_code <= '$to_category'
			";
		}
			
		$queries['sales'] .= "
			and date >= '$first_day_of_current_year'
			and date <= '$date'
			group by stock.stock_id
		";

		//die($queries['sales']);

		/*for beginning balance only*/
		
		//sales forwarded
		//only include for begnning balance

		
		$queries['sales_forwarded'] = "
			select 
				0 as in_qty,
				sum(stock.fraction2 * quantity) as out_qty,
				stock.stock_id
			from 
				sales_forwarded f
				inner join stock on stock.stock_id = f.stock_id
					and stock.enable = 'Y'
					and stock.inventory = 'Y'
					and stock.account_id = '$account_id'
				inner join category on category.category_id = stock.category_id ";

		if ( !empty($from_category) && !empty($to_category) ) {
			$queries['sales_forwarded'] .= "
				and category_code >= '$from_category'
				and category_code <= '$to_category'
			";
		}
		$queries['sales_forwarded'] .= "				
			where					
				year <= '$year_minus_1'
			group by stock.stock_id
		";			
		

		
		//purchase return
		$queries['purchase_returns'] = "
			select 
				0 as in_qty,
				sum(case_qty*fraction3 + unit_qty) as out_qty,
				stock.stock_id
			from 
				por_header as h,
				por_detail as d,
				stock,
				category
			where
				h.por_header_id = d.por_header_id
			and d.stock_id = stock.stock_id
			and category.category_id = stock.category_id
			and not ( h.status in ('C','V') )
			and stock.account_id = '$account_id' ";

		if (!empty($from_category) && !empty($to_category)) {
			$queries['purchase_returns'] .= "
				and category_code >= '$from_category'
				and category_code <= '$to_category'
			";
		}
			
		$queries['purchase_returns']  .= "
			and date <= '$date'
			group by stock.stock_id
		";

		
		
		//stocks transfer
		$queries['stocks_transfer'] = "
			select 
				0 as in_qty,
				sum(case_qty*fraction3 + unit_qty) as out_qty,
				stock.stock_id
			from 
				stocktransfer_header,
				stocktransfer_detail,
				stock,
				category
			where
				stock.stock_id = stocktransfer_detail.stock_id 
				and stocktransfer_header.stocktransfer_header_id = stocktransfer_detail.stocktransfer_header_id 
				and category.category_id = stock.category_id
				and not (stocktransfer_header.status in ('C','V'))
				and stock.account_id = '$account_id' ";
		if (!empty($from_category) && !empty($to_category)) {
			$queries['stocks_transfer'] .= "
				and category_code >= '$from_category'
				and category_code <= '$to_category'
			";
		}
		$queries['stocks_transfer'] .= "	
				and date <= '$date'
				group by stock.stock_id
			";
		
		$sql = "
			select 
				sum(in_qty) as in_qty, sum(out_qty) as out_qty,
				stock.stock_id
			from
				(
					$queries[rr]
						union all
					$queries[adjustments]
						union all
					$queries[sales]
						union all
					$queries[sales_forwarded]
						union all
					$queries[purchase_returns]
						union all
					$queries[stocks_transfer]
				) as t
				inner join stock on stock.stock_id = t.stock_id
			group by stock.stock_id
		";

		return lib::getArrayDetails($sql);


		
	}/*end of getStockBalance*/

	public static function getStockBalance($stock_id,$from_date,$to_date,$beg_balance = TRUE){
		$stkled  = null;
		$stkled  = array();
		$beg_qty = 0;

		/* if to_date is december 31 and from date is empty */
		$is_last_day_of_the_year = $from_date == '' && 
			lib::monthIsDecember($to_date) && lib::dayIsThirtyOne($to_date) ;

		if ( $is_last_day_of_the_year ) {
			$year_minus_1 = date("Y",strtotime($to_date));	//get current year
		} else {
			$year_minus_1 = date("Y",strtotime($to_date)) - 1;		
		}

		if($beg_balance){
			$balance_date = date("Y-m-d" ,strtotime("-1 day",strtotime($from_date)));
			$aBeg         = self::getStockBalance($stock_id,'',$balance_date,FALSE);
			$beg_qty      = $aBeg['balance_qty'];
		}
		
		#STOCKS RECEVING + ADJUSTING ENTRIES - SALES + PURCHASE RETURNS
		$balance_qty = $in_qty = $out_qty = 0;
		$rr_qty = $adj_qty = $sales_qty = $por_qty = 0;
		
		$tables       = lib::currTables($to_date);
		$sales_header = $tables['sales_header'];
		$sales_detail = $tables['sales_detail'];
		
		//stock receiving
		$q = "select 
					sum(rr_detail.case_qty*stock.fraction3 + rr_detail.unit_qty) as in_qty, 
					(sum(rr_detail.amount)/sum(case_qty*fraction3 + unit_qty)) as average_cost
				from 
					rr_header,
					rr_detail,
					stock
				where
					stock.stock_id = rr_detail.stock_id 
				and rr_header.rr_header_id=rr_detail.rr_header_id
				and rr_detail.stock_id='$stock_id'
				and rr_header.status!='C'
		";

		if ( $from_date != '' ) { $q .= " and date >= '$from_date'"; }
		if ( $to_date != '' ) { $q .= " and date <= '$to_date'"; }
		
		$q .= "	group by rr_detail.stock_id";

		//echo "$q <br>";

		$result       = @pg_query($q) or die (pg_errormessage());
		$r            = @pg_fetch_object($result);

		
		$in_qty       += $r->in_qty;
		$rr_qty       = $r->in_qty;
		$average_cost = $r->average_cost;
		
		//adjusting entries
		$q = "select sum(case_qty*fraction3 + unit_qty) as in_qty
				from 
					invadjust_header,
					invadjust_detail,
					stock
				where
					stock.stock_id=invadjust_detail.stock_id and 
					invadjust_header.invadjust_header_id=invadjust_detail.invadjust_header_id and
					invadjust_detail.stock_id='$stock_id' and
					invadjust_header.branch_id='1' and 
					invadjust_header.status!='C'";
		if ($from_date != ''){ $q .= " and date >= '$from_date'";  }
		if ($to_date != '') { $q .= " and date <= '$to_date'";  }

		$q       .= "	group by invadjust_detail.stock_id";

		$result  = pg_query($q) or die (pg_errormessage());
		$r       = pg_fetch_object($result);
		$in_qty  += $r->in_qty;
		$adj_qty = $r->in_qty;

		/* do not get sales if last day of the year */
		if ( !$is_last_day_of_the_year ) {

			$current_year = date("Y",strtotime($to_date));
			$first_day_of_current_year = "$current_year-01-01";

			//sales
			$q = "
					select sum(quantity) as out_qty
						from 
							sales
						where
							stock_id='$stock_id'
					";

			if ( $from_date == '' ) {
				$q .= "
					and date >= '$first_day_of_current_year'
					and date <= '$to_date'
				";
			} else {
				$q .= "
					and date >= '$from_date '
					and date <= '$to_date'
				";
			}

			$q .= "	group by stock_id";
			
			$result    = @pg_query($q) or die (pg_errormessage().$q);
			$r         = @pg_fetch_object($result);
			$out_qty   += $r->out_qty;
			$sales_qty = $r->out_qty;

		}


		/*for beginning balance only*/
		//sales forwarded
		//only include for begnning balance

		if( !$beg_balance ){
			$q = "
				select 
					sum(quantity) as out_qty
				from 
					sales_forwarded f
					inner join stock s on s.stock_id = f.stock_id				
				where				
					f.stock_id = '$stock_id'
				and year <= '$year_minus_1'
			";			
				
			$result              = @pg_query($q) or die (pg_errormessage().$q);
			$r                   = @pg_fetch_object($result);
			$out_qty             += $r->out_qty;
			$sales_forwarded_qty = $r->out_qty;
		}
		
		//purchase return
		$q = "select sum(case_qty*fraction3 + unit_qty) as out_qty
					from 
						por_header,
						por_detail,
						stock
					where
						stock.stock_id=por_detail.stock_id and 
						por_header.por_header_id=por_detail.por_header_id and
						por_detail.stock_id='$stock_id' and
						not (por_header.status in ('C','V'))";
		if ($from_date != '') { $q .= " and date >= '$from_date'"; }
		if ($to_date != '') { $q .= " and date<= '$to_date'"; }
		$q .= "	group by por_detail.stock_id";

		$result  = pg_query($q) or die (pg_errormessage());
		$r       = pg_fetch_object($result);
		$out_qty += $r->out_qty;
		$por_qty = $r->out_qty;
		
		
		//stocks transfer
		$q = "select sum(case_qty*fraction3 + unit_qty) as out_qty
					from 
						stocktransfer_header,
						stocktransfer_detail,
						stock
					where
						stock.stock_id=stocktransfer_detail.stock_id and 
						stocktransfer_header.stocktransfer_header_id=stocktransfer_detail.stocktransfer_header_id and
						stocktransfer_detail.stock_id='$stock_id' and
						not (stocktransfer_header.status in ('C','V'))";
		if ($from_date != '') { $q .= " and date >= '$from_date'"; }
		if ($to_date != '') { $q .= " and date<= '$to_date'"; }
		$q .= "	group by stocktransfer_detail.stock_id";

		$result       = pg_query($q) or die (pg_errormessage());
		$r            = pg_fetch_object($result);
		$out_qty      += $r->out_qty;
		$transfer_qty = $r->out_qty;
		
		$balance_qty  = $in_qty - $out_qty;
		
		$stkled['balance_qty']         = $balance_qty;
		$stkled['rr_qty']              = $rr_qty;
		$stkled['adj_qty']             = $adj_qty;
		$stkled['sales_qty']           = $sales_qty;
		$stkled['sales_forwarded_qty'] = $sales_forwarded_qty;
		$stkled['por_qty']             = $por_qty;
		$stkled['transfer_qty']        = $transfer_qty;
		$stkled['beg_qty']             = $beg_qty;
		$stkled['as_of_date_qty']      = $beg_qty + $balance_qty; /*this is how to get the current balance*/

		/*echo "<pre>";
		var_dump($stkled);
		echo "<pre>";*/
		
		return $stkled;
	}/*end of getStockBalance*/

	public static function getSalesForwarded($stock_id, $date){

		$year_minus_1 = date("Y",strtotime($to_date)) - 1;

		$q = "
			select 
				sum(quantity) as out_qty
			from 
				sales_forwarded f
				inner join stock s on s.stock_id = f.stock_id				
			where				
				f.stock_id = '$stock_id'
			and year = '$year_minus_1'
		";			
			
		$result              = @pg_query($q) or die (pg_errormessage().$q);
		$r                   = @pg_fetch_object($result);
		$sales_forwarded_qty = $r->out_qty;

		return $sales_forwarded_qty;
	}
}

?>