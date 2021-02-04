<?
			  	$aip = explode('.',$_SERVER['REMOTE_ADDR']);
	  			$reportfile= '/data/cache/CACHE'.$aip[3].'.txt';
	  			$fo = @fopen($reportfile,'r');
			
				if (file_exists($reportfile) && count($aItems)== '0')
				{
					if (!session_is_registered('aItems'))
					{
						session_register('aItems');
					}
					$aItems = null;
					$aItems = array();
					$contents = '';
		  			while (!feof($fo)) 
		  			{
  						$contents = fgets($fo, 8192);
  						if (strlen($contents) < 15) continue;

  						$temp = explode('||',$contents);
						$dummy = null;
						$dummy = array();
						$temp1 = null;
						$temp1 = array();
						foreach ($temp as $key => $value)
						{
							$temp1 = explode('=>',$value);
							$dummy[$temp1[0]] = $temp1[1];
					 	}
						$aItems[] = $dummy;
					}		
				}
?>