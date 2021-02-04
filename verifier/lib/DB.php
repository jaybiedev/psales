<?php
require_once "Product.php";
class DB
{

    private static $DB_NAME = "lec";
    private static $DB_USERNAME = "pgsql";
    private static $DB_PASSWORD = "";
    private static $DB_HOST = "localhost";
    private static $DB_PORT = "5432";

    public static function init(){
        return pg_connect("host=".self::$DB_HOST." port=".self::$DB_PORT." user=".self::$DB_USERNAME." 	dbname=".self::$DB_NAME);
    }

    public static function fetch_array($sql){
        $db = self::init();

        $arr = array();
        $result = pg_query($db, $sql) or die( pg_last_error($db) );
        while( $r = pg_fetch_assoc($result) ){
            $arr[] = $r;
        }

        pg_close($db);

        return $arr;
    }

    public static function fetch_one($sql){
        $db = self::init();

        $result = pg_query($db, $sql) or die( pg_last_error($db) );
        $r = pg_fetch_assoc($result);

        pg_close($db);

        return new Product($r['stock_id'],$r['stock'],$r['barcode'],$r['price1'], 
            $r['date1_promo'], $r['date2_promo'], $r['promo_price1'], $r['promo_sdisc'],$r['account_id']);
    }

    public static function supplierDiscountPrice($account_id, $stock_id, $price){        
        $db = self::init();        

        $today = date("Y-m-d");

        $result = pg_query($db, "
            select 
                category.category_code 
            from 
                stock, category
            where
                stock.category_id = category.category_id  
            and stock_id = '$stock_id'
        ");

        $Item = pg_fetch_assoc($result);

        /*check promo by supplier with specic category first*/
        $clen = strlen($Item['category_code']);


        $sql = "
            select 
                *
            from 
                promo_header 
            where
                account_id = '".$account_id."' 
                and promo_header.date_from <= '$today'    
                and promo_header.date_to >= '$today' 
                and substr(category_code_from,1,$clen) <= '".$Item['category_code']."' 
                and substr(category_code_to,1,$clen) >= '".$Item['category_code']."'                 
                and promo_header.enable='Y'
            order by promo_header_id desc
        ";

        $result = pg_query($db, $sql) or  pg_last_error($db) ;

        if ( pg_num_rows($result) > 0 ) {
            $r = pg_fetch_assoc($result);
            return number_format($price * ( 1 - $r['sdisc'] / 100 ),2);

        } 


        $sql = "
            select 
                    h.*
            from 
                promo_header as h                
            where 
                account_id = '$account_id'
            and date_from <= '".date("Y-m-d")."'
            and date_to >= '".date("Y-m-d")."'
            and category_code_from = ''
            and enable = 'Y'
            order by promo_header_id desc
        ";
        
        $result = pg_query($db, $sql) or  pg_last_error($db) ;

        if ( pg_num_rows($result) > 0 ) {
            $r = pg_fetch_assoc($result);
            return number_format($price * ( 1 - $r['sdisc'] / 100 ),2);

        } else {
            return $price;
        }
    
    }

}
