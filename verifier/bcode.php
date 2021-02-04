<?
require_once "lib/Image.php";
require_once "lib/DB.php";

$barcode = $_GET['barcode'];

$results = DB::fetch_array("
        select
            account_code as supcode,
            markup,
            stock as description,
            price1 as price,
            barcode
        from
            stock as s
            inner join account as a on a.account_id = s.account_id
        where
            barcode = '$barcode'
    ");


if ( count($results) > 0 ) {
    echo json_encode($results[0]);
}
?>
