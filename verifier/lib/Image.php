<?php

class Image {
    /**
     *Get imgage file names in the ads directory
     */
    public static function getImageFiles( $file )
    {
        $arr_ads = scandir(dirname($file) . "/ads" );

        $arr_ad_images = array();
        if (count($arr_ads)){
            foreach ($arr_ads as $ad){
                if ( in_array( $ad, array(".","..") ) ){
                    continue;
                }

                $arr_ad_images[] = $ad;
            }
        }

        return $arr_ad_images;

    }

}