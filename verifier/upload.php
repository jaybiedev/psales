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

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="js/jquery-1.11.2.min.js"></script>

    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.index.css"/>
    <link rel="stylesheet" href="css/style.upload.css"/>
    <script src="js/upload.js"></script>

</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6 sidebar" >
                <div>
                    <form action="" class="form-group">
                        <div class="row">
                            <div class="col-md-4">
                                <h3>Upload Module</h3>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div style="display:inline-block; width:30%; vertical-align:top;">
                                    <div id="drop-files" ondragover="return false">
                                        Drop Images Here <br/><br/>
                                    </div>

                                    <div id="uploaded-holder">
                                        <div id="dropped-files">
                                            <div id="upload-button">
                                                <a href="#" class="upload">Upload!</a>
                                                <a href="#" class="delete">delete</a>
                                                <span>0 Files</span>
                                            </div>
                                        </div>
                                        <div id="extra-files">
                                            <div class="number">
                                                0
                                            </div>
                                            <div id="file-list">
                                                <ul></ul>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="loading">
                                        <div id="loading-bar">
                                            <div class="loading-color"> </div>
                                        </div>
                                        <div id="loading-content">Uploading file.jpg</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row top-buffer">
                            <div class="col-md-12">
                                <a href="." class="btn btn-primary">Back</a>
                            </div>
                        </div>
                    </form>

                </div>
            </div>

            <div class="col-md-6">
                <div style="height: 100%;">
                    <?
                    if( count($arr_ads) ) {
                        $i = 0;
                        foreach ( $arr_ads as $ad ){
                            ?>
                            <div style="display:inline-block; position: relative; overflow: hidden; width:200px; height: 100px; border:1px solid #c0c0c0;" >
                                <img style="width: 100%;" src="ads/<?= $ad ?>" alt="..." >
                                <span class="glyphicon glyphicon-remove" style="position: absolute; top: 0px; left: 0px; color:#000; cursor: pointer;"
                                      data-filename="<?= $ad ?>" onclick="deleteFile(this);" ></span>
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





</body>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="js/bootstrap.min.js"></script>

<script type="text/javascript">
    $(document).ready(function(){
        $('.sidebar').height($(window).height());
        $('.carousel-inner').css('max-height',$(window).height());
        $(window).resize(function(){
            $('.carousel-inner').css('max-height',$(window).height());
        });

        $('.carousel').carousel({
            interval : 500,
            pause : false
        });

        /*focus barcode field if unfocused*/
        setInterval(function(){
            var barcode = $("#barcode");
            if( !barcode.is(":focus") ) {
                barcode.focus();
            }
        },5000)
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

    function deleteFile(e)
    {

        if( !confirm("Would you like to confirm deleting the image?") ){
            return false;
        }

        var form_data = {
            filename : $(e).data("filename")
        };

        jQuery.post("lib/barcode.php", {action: "deleteFile", data: form_data}, function (data) {
            //actions
            $(e).parent().remove();
        });
    }

</script>
</html>