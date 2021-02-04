<?php
ob_start();
session_start();

@include_once('../lib/library.php');
@include_once('../lib/lib.inventory.php');
@include_once('../lib/dbconfig.php');
@include_once('../lib/connect.php');
@include_once('../var/system.conf.php');

date_default_timezone_set("Asia/Manila");
if( !empty($_REQUEST['action']) ) call_user_func($_REQUEST['action'], $_REQUEST['data']);

function checkInventory ($barcode) {
    $arr;
    $stock_id = lib::getAttribute('stock','barcode',$barcode,'stock_id');
    
    if ($stock_id) {

        $bal = Inventory::getCurrentBalanceInWords($stock_id,lib::now());
        echo $bal;

    } else {
        echo -1;
    }

    
    

}




?>