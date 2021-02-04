<?
class lib{

	public static function getPromoFromSalesDb($sales_header_id){
		$schema = lib::getCurrentSchema();

		$result = pg_query("select * from $schema.sales_promo_items where sales_header_id = '$sales_header_id'");
		$num_rows = pg_num_rows($result);

		$content = "";

		if ( $num_rows > 0 ) {
			while ( $r = pg_fetch_assoc($result) ) {
				$content .= round($r['free_items'],0)." - " . round($r['qty_of_free_item'],0) . " pc/s of $r[free_item]\n";
			}
		}

		return $content;

	}

	public static function getRewardsMultiplier($promos, $aItem){

		if ( count( $promos ) ) {
			foreach ( $promos as $promo ) {
				if ( self::hasPromo($promo, $aItem) ) {
					return $promo['multiplier'];
				}
			}
		}

		return 0;

	}

	public static function savePromoSales($promos, $sales_header_id){
		$schema = self::getCurrentSchema();

		if ( count($promos) > 0 ) {
			foreach ( $promos as $promo ) {
				if ( floor($promo['total_quantity']) > 0 ) {
					$quantity  = floor($promo['total_quantity']);
					pg_query("
						insert into
							$schema.sales_promo_items
						(sales_header_id, free_item, qty_of_free_item, free_items)
							values
						('$sales_header_id', '$promo[free_item]', '$promo[qty_of_free_item]', '$quantity')
					");
				}
			}		
		}
		
	}

	public static function hasPromo($promo, $aItem) {
		
		/**
		 * check if stock
		 */

		if ( $aItem['type'] != 'stock' ) {
			return false;
		}

		/**
		 * check barcode, from/to category, supplier
		 *
		 */

		$bBarcode =  empty($promo['barcode']) ? true : $promo['barcode'] == $aItem['barcode'];

		$bCategory = empty($promo['from_category_code']) || empty($promo['to_category_code']) ? true : 
			$aItem['category_code'] >=  substr($promo['from_category_code'],0,strlen($aItem['category_code'])) &&
			$aItem['category_code'] <=  substr($promo['to_category_code'],0,strlen($aItem['category_code']));
	
		$bSupplier = empty($promo['supplier_id']) ? true : $promo['supplier_id'] == $aItem['supplier_id'];

		return $bBarcode && $bCategory && $bSupplier;
	}

	public static function getPromoItems(){
		$date = self::now();

		$sql = "
			select 
				promo_items.*,
				from_category.category as from_category,
				from_category.category_code as from_category_code,
				to_category.category as to_category,
				to_category.category_code as to_category_code,
				account,
				supplier_id
			from 
				promo_items 
				left join category as from_category on from_category.category_id = promo_items.from_category_id
				left join category as to_category on to_category.category_id = promo_items.to_category_id
				left join account on account.account_id = supplier_id
			where
				from_date <= '$date'
			and to_date >= '$date'
			order by id desc
		";

		return self::getArrayDetails($sql);
	}

	public static function getPromoForRewards(){
		$date = self::now();

		$sql = "
			select 
				additional_reward_points.*,
				from_category.category as from_category,
				from_category.category_code as from_category_code,
				to_category.category as to_category,
				to_category.category_code as to_category_code,
				account,
				supplier_id
			from 
				additional_reward_points 
				left join category as from_category on from_category.category_id = additional_reward_points.from_category_id
				left join category as to_category on to_category.category_id = additional_reward_points.to_category_id
				left join account on account.account_id = supplier_id
			where
				from_date <= '$date'
			and to_date >= '$date'
			order by id desc
		";

		return self::getArrayDetails($sql);
	}


	public static function getAttribute($table,$search_column,$search_id,$column){
		$result = pg_query("
			select
				*
			from
				$table
			where
				$search_column = '$search_id'
		") or die(pg_result_error());
		$r =  pg_fetch_assoc($result);
		return $r[$column];
	}
	
	public static function getTableAssoc($selectedid=NULL,$name='name',$label = NULL,$query,$id_column,$display_column,$aDisplay = NULL){
		$content="
			<select name='$name' id='$name'>
				<option value=''>$label:</option>
		";	
		
		$result=pg_query($query);
		while($r=pg_fetch_assoc($result)):
			$selected=($selectedid==$r[$id_column])?"selected='selected'":"";
			
			$display_content = $r[$display_column];
			if(!empty($aDisplay)){
				$display_content = "";
				foreach($aDisplay as $a){
					$display_content .="$r[$a] ";
				}
			}
			
			$content.="
				<option value='$r[$id_column]' $selected >".htmlspecialchars($display_content)."</option>
			";
		endwhile;
		
		$content.="
			</select>
		";
		
		return $content;
	}
	
	public static function getArraySelect($selectedid=NULL,$name='name',$label = NULL, $array, $js=NULL){
		$content=" <select name='$name' id='$name' $js> ";
		if( !empty($label) ) $content .= "<option value=''>$label:</option>";
		
		foreach($array as $key => $value):
			$selected=($selectedid == $key)?"selected='selected'":"";	
			$content.="
				<option value='$key' $selected >".htmlspecialchars($value)."</option>
			";
		endforeach;
		
		$content.="
			</select>
		";
		
		return $content;
	}
	
	public static function getMonthOptions($selectedid=NULL,$name='month'){
		$lists=array(
				1 => "Jan",
				2 => "Feb",
				3 => "Mar",
				4 => "Apr",
				5 => "May",
				6 => "Jun",
				7 => "Jul",
				8 => "Aug",
				9 => "Sept",
				10=> "Oct",
				11=> "Nov",
				12=> "Dec"
			);
			
		$content="
			<select name='$name' id='$name' >
				<option value=''>Select Month:</option>
		";
		
		foreach($lists as $value => $list):
			$selected=($selectedid==$value)?"selected='selected'":"";
			$content.="
				<option value='$value' $selected >$list</option>
			";
		endforeach;	
		
		
		$content.="
			</select>
		";
		
		return $content;
		
	}
	
	public static function inputText($a){
		$a['id'] = !empty($a['id']) ? $a['id'] : $a['name'];
		$a['keypress'] = !empty($a['keypress']) ? "if(event.keyCode==13){ jQuery('#$a[keypress]').focus(); return false; } " : "";
		$content = "
			<input type=\"text\" class=\"textbox $a[class] \" id=\"$a[id]\" name=\"$a[name]\" value=\"$a[value]\" 
			onkeypress=\"$a[keypress]\" ".(($a['readonly']) ?  "readonly='readonly'" : "")." ".((!$a['autocomplete']) ?  "autocomplete='off'" : "")." 
			".(($a['required']) ?  "required" : "")."
			 />
		";
		echo $content;		
	}
	
	public static function ymd2mdy($date){
		if( $date == "0000-00-00" || empty($date) ){
			return "-";
		} else {
			return date("m/d/Y",strtotime($date));
		}		
	}

	public static function mdy2ymd($date){
		if( $date == "0000-00-00" || empty($date) ){
			return self::now();
		} else {
			return date("Y-m-d",strtotime($date));
		}		
	}
	public static function toStandardDate($date){
		return date("F j, Y",strtotime($date));
		
	}
	
	public static function getTableAttributes($sql){
		$result = pg_query($sql) or die(pg_result_error());
		return pg_fetch_assoc($result);
	}
	
	public static function getArrayDetails($sql){

		$result = pg_query($sql) or die(pg_result_error());
		$a = array();
		while( $r = pg_fetch_assoc( $result ) ){
			$a[] = $r;		
		}
		return $a;
	}

	public static function getLastDayOfMonth($date){
		return date("Y-m-t", strtotime($date));
	}

	public static function transactionDataExists($sql){
		$result = pg_query($sql) or die(pg_result_error());
		if( pg_num_rows($result) ){
			return TRUE;
		} else {
			return FALSE;
		}

	}

	public static function plusOneDay($date){
		return date("Y-m-d" ,strtotime("+1 day",strtotime($date)));
	}

	public static function monthIsDecember($date){
		return self::month($date) == 12;
	}

	public static function dayIsThirtyOne($date){
		return self::day($date) == 31;
	}

	public static function minusOneDay($date){
		return date("Y-m-d" ,strtotime("-1 day",strtotime($date)));
	}

	public static function month($date){
		return date("m" ,strtotime($date));
	}

	public static function day($date){
		return date("d" ,strtotime($date));
	}

	public static function now(){
		return date("Y-m-d");
	}

	public static function currTables($date) {
		global $SYSCONF;
		
		$year = substr($date,0,4);
		$schema = "e$year"; 			//-- schema by year
		if (substr($date,0,4) < '2007')
		{
			$sales_header = 'sales_header';
			$sales_detail = 'sales_detail';
			$sales_tender = 'sales_tender';
			$stockledger  = 'stockledger';
		}
		elseif ($_SERVER['REMOTE_ADDR']=='127.0.0.1' && !file_exists('/jaybie.conf') && 0)
		{
			$sales_header = 'offline.sales_header';
			$sales_detail = 'offline.sales_detail';
			$sales_tender = 'offline.sales_tender';
			$stockledger  = 'offline.stockledger';
		}
		elseif ($year < '2008')
		{
			$sales_header = 'sh_'.$year;
			$sales_detail = 'sd_'.$year;
			$sales_tender = 'st_'.$year;
			$stockledger  = 'sl_'.$year;
		}
		else
		{
			$sales_header = "$schema.sales_header";
			$sales_detail = "$schema.sales_detail";
			$sales_tender = "$schema.sales_tender";
			$stockledger  = "$schema.stockledger";
		}
		$aTables = null;
		$aTables = array();
		
		if ($SYSCONF['PCENTER'] != '')
		{
			//-- special tables for other profit center-- bowling
			$aTables['stock_table'] = 'stock'.$SYSCONF['PCENTER'];
			$aTables['sales_header'] = 'sh_'.$SYSCONF['PCENTER'];
			$aTables['sales_tender'] = 'st_'.$SYSCONF['PCENTER'];
			$aTables['sales_detail'] = 'sd_'.$SYSCONF['PCENTER'];
			$aTables['stockledger'] = 'sl_'.$SYSCONF['PCENTER'];	
		}
		else
		{
			$aTables['stock_table'] = 'stock';
			$aTables['sales_header'] = $sales_header;
			$aTables['sales_tender'] = $sales_tender;
			$aTables['sales_detail'] = $sales_detail;
			$aTables['stockledger'] = $stockledger;	
		}
		
		return $aTables;
	}

	public static function getCurrentSchema(){
		return "e" . date('Y'); 	
	}
		
}
?>