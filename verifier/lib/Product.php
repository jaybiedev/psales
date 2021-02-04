<?php
class Product
{
    public function __construct( $stock_id, $stock, $barcode, $price, 
    	$date1_promo, $date2_promo, $promo_price1, $promo_sdisc, $account_id)
    {

		$this->stock_id        = $stock_id;
		$this->stock           = $stock;
		$this->barcode         = $barcode;
		$this->from_date_promo = $date1_promo;
		$this->to_date_promo   = $date2_promo;
		$this->account_id = $account_id;

		$this->gross_price = $price;

        $this->now = date("Y-m-d");

        $this->price    = $price;        
        if ( $date1_promo != "--" && $date2_promo != "--" ) {

        	/*check if within promo*/
        	if ( $this->from_date_promo <= $this->now &&
        		$this->to_date_promo >= $this->now ) {

				/*check if promo price is set*/
        		if ( $promo_price1 > 0 && !empty( $promo_price1 ) ) {
        			$this->price = $promo_price1;
        		} else {
                    /*check if promo discount is set, if set overide promo price*/
                    if ( $promo_sdisc > 0 && !empty( $promo_sdisc ) ) {
                        $this->price = round($this->gross_price * ( 1 - $promo_sdisc / 100 ),2);
                    }    
                }

        	}

        	
        } else {
        	/*check if supplier global discount*/
	    	$this->price = DB::supplierDiscountPrice($account_id, $stock_id, $this->gross_price);
	    	//$this->price = 1;	
        }        

    }
}