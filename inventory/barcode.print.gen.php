<?
$bar	= $_REQUEST['bar'];
$copies	= $_REQUEST['copies'];
?>
<script type="text/javascript">
	function printPage() { print(); } //Must be present for Iframe printing
</script>
<html>
  <head>

    <style>
      * {
          color:#000;
          font-family:Arial,sans-serif;
          font-size:8px;
          font-weight:normal;
      }
	body{
		padding:0px;margin:0px;
		width:100%;
	}    
	  
    </style>
   </head>
   <body>
    
    <script type="text/javascript" src="../js/jquery-1.3.2.min.js"></script>
    <script type="text/javascript" src="../js/jquery-barcode-2.0.2.min.js"></script>
    <script type="text/javascript">
	$(function(){
		$(".div").barcode("<?=$bar?>", "ean13",{barWidth:1, barHeight:36});
	});
	</script>
    <div style="margin:0px; padding:0px;">
    	<? for($x=1; $x<=$copies;$x++){ ?>
			<div id="" class="div" style="display:inline-block;  float:right;"></div>
 

         
        <div style="margin-bottom:32px; clear:both;"></div>
        <? } ?>
    </div>	
  
  </body>
</html>