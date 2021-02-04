<?
    $dest = $_SERVER["REMOTE_ADDR"];
	   $m = "tcp://$dest";
	   echo $m;
	   $pln = "HOPE INTEGRATED TECHONOLOGIES \n Description Qty Amount \n -----------\n";
		 $fp = fsockopen("tcp://$dest", 5003, $errno, $errstr, 10);
		 if (!$fp) {
		     echo " $errstr ($errno)<br>\n";
		     
		 }
		 else
		 {	
			      fputs($fp,$pln);
			      fputs($fp,"eof");
	        	fclose ($fp);
	        	sleep(1);
	        	
      		  $fp = fsockopen("tcp://$dest", 5003, $errno, $errstr, 10);
			      fputs($fp,"eof");
	        	fclose ($fp);
	        	sleep(1);	        	
		}
?>
