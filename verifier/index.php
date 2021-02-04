<?
require_once "lib/Image.php";
require_once "lib/DB.php";

$arr_ads = Image::getImageFiles(__FILE__);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>LOPUES PRICE VERIFIER</title>

    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.index.css"/>
</head>
<body>
    <div class="container-fluid" style="height: 100%;">
        <div class="row frame-border">
        </div>
        <div class="row" style="height: 80%;">
            <div class="col-md-4" style="height: 100%;">
                <div style="position: relative; height: 100%;">
                    <div class="row">
                        <div class="col-md-12 text-center" style="margin-top:30px;">
                            <h1>SCAN HERE</h1>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <input type="text" class="form-control input-lg" name="barcode"
                                id="barcode" autofocus="true" placeholder="BARCODE"
                                onkeypress="if( event.keyCode == 13 ){ getBarcode(); return false; }"/>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <span class="price-display">&#8369;</span><span class="price-display" id="price_display">--.--</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <span class="product-display" id="product_display">Product Description</span>
                        </div>
                    </div>

                    <div class="lopues-logo" style="position: absolute; bottom: 20px; left: 0px;">
                        <div class="col-md-12 text-center">
                            <a href="upload.php"><img src="img/lec-logo.jpg" alt="" style=" width:100%;"/></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8" style="height:100%; overflow: hidden; padding:30px;">
                <div style="height: 100%;">
                    <div class="row" style="height: 100%;">
                        <div class="col-md-12" style="height: 100%;">
                            <div id="carousel-example-generic" class="carousel slide" data-ride="carousel" style="height: 100%;">
                                <!-- Wrapper for slides -->
                                <div class="carousel-inner" role="listbox" style="width:100%; height: 100%; overflow: hidden;">
                                    <?
                                    if( count($arr_ads) ) {
                                        $i = 0;
                                        foreach ( $arr_ads as $ad ){
                                    ?>
                                            <div class="item <?= (($i == 0) ? "active" : "") ?>">
                                                <img src="ads/<?= $ad ?>" alt="..." >
                                            </div>
                                        <?
                                            $i++;
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row frame-border">
        </div>
    </div>
</body>
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="js/jquery-1.11.2.min.js"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="js/bootstrap.min.js"></script>

<script type="text/javascript">

    var images = JSON.parse('<?=json_encode($arr_ads);?>');
    console.log(images);

    $(document).ready(function(){
        $('.sidebar').height($(window).height());
        $('.carousel-inner').css('max-height',$(window).height());
        $(window).resize(function(){
            $('.carousel-inner').css('max-height',$(window).height());
            $('.lopues-logo').css('bottom','20px;');
        });

        $('.carousel').carousel({
            interval : 5000,
            pause : false
        });

        /*focus barcode field if unfocused*/
        setInterval(function(){
            if( !$("#barcode").is(":focus") ) {
                $("#barcode").focus();
            }
        },10000);

        setInterval(checkImages,500);
    });

    function getBarcode(){
        var barcode = $("#barcode").val();

        var form_data = {
            barcode : barcode
        };

        jQuery.post("lib/barcode.php", {action: "getBarcode", data: form_data}, function (data) {
            //actions
            var obj = JSON.parse(data);
            console.log(obj);

            if ( obj.stock == null || obj.price == null ){
                $("#price_display").html("--.--");
                $("#product_display").html("Product not found");
            } else {
                $("#price_display").html(obj.price);
                $("#product_display").html(obj.stock);
            }

            $("#barcode").val("");

        });
    }

    function checkImages(){
        var form_data = images;

        jQuery.post("lib/barcode.php", {action: "checkImages", data: form_data}, function (data) {
            //actions                              
            /*var obj = JSON.parse(data);*/
            console.log(data);
            if ( data == 0 ){
                window.location.reload();
            }
        });
    }


    /*checkSize();
    function checkSize(){
        alert($(".carousel-inner").width());
        alert($(".carousel-inner").height());
    }*/

</script>
</html>