<?php

require_once "DB.php";
require_once "Image.php";

if (!empty($_REQUEST['action'])) call_user_func($_REQUEST['action'], $_REQUEST['data']);

function getBarcode($form_data)
{
    $obj = DB::fetch_one("select * from stock where barcode = '$form_data[barcode]'");
    echo json_encode($obj);
}

function deleteFile($form_data)
{
    unlink("../ads/".$form_data['filename']);
}

function checkImages($form_data)
{

    $arr_ads = Image::getImageFiles(dirname(__FILE__) .'/../index.php');

    $is_the_same = TRUE;
    if ( count( $arr_ads ) == count( $form_data ) ) {
        if ( count($form_data) ){
            $i = 0 ;
            foreach ( $form_data as $image ){
                /*image has different orientation*/
                if( $image != $form_data[$i] ){
                    echo 0;
                    return false;
                }
                $i++;
            }
            /*the image exists*/
            echo 1;
            return false;
        }
    } else {
        /*different image exists*/
        echo 0;
    }
}
