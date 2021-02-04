<?
/*	$DBDOMAIN='localhost';
	$DBNAME='lopues';
	$DBUSERNAME='root';
	$DBPASSWORD='123';
*/
	$DBDOMAIN='localhost';
	
	if ($SYSCONF['DATABASE'] != '')
	{
		$DBNAME = $SYSCONF['DATABASE'];
	}
	else
	{
		$DBNAME= 'lec';
	}
	$DBUSERNAME='Jared';
	$DBPASSWORD='jnb2000';
	$DBCONNECT = "host=$DBDOMAIN port=5432  dbname=$DBNAME user=$DBUSERNAME password=$DBPASSWORD";
	$transaction_header = 'transaction_header';
	$transaction_detail = 'transaction_detail';
	$cashcount_table = 'cashcount';
	$zread_table = 'zread_pos';
	
	$ZREAD_TRANSMIT_DRIVE = 'c:';
	$ZREAD_TRANSMIT = 1;
	
	if (date('Y') < '2007')
	{
		//$sales_header = 'e'.date('Y').'.sales_header';
		//$sales_detail = 'e'.date('Y').'.sales_detail';
		//$sales_tender = 'e'.date('Y').'.sales_tender';
		$sales_header = 'sales_header';
		$sales_detail = 'sales_detail';
		$sales_tender = 'sales_tender';
		$stockledger  = 'sl_'.date('Y');
		
	}
	elseif ($_SERVER['REMOTE_ADDR']=='127.0.0.1' && !file_exists('/jaybie.conf'))
	{
		$sales_header = 'offline.sales_header';
		$sales_detail = 'offline.sales_detail';
		$sales_tender = 'offline.sales_tender';
		$stockledger  = 'offline.stockledger';
	}
	else
	{
		$sales_header = 'sh_'.date('Y');
		$sales_detail = 'sd_'.date('Y');
		$sales_tender = 'st_'.date('Y');
		$stockledger  = 'sl_'.date('Y');
	}
	$SYSCONF['tables']['sales_header'] = $sales_header;
	$SYSCONF['tables']['sales_tender'] = $sales_tender;
	$SYSCONF['tables']['sales_detail'] = $sales_detail;
	$SYSCONF['tables']['stockledger'] = $stockledger;

function we($w,$u=null)
{
	$s  = array('04-BJB-991906','02-YKA-002010','13-DIN-711907','30-CKE-032001','17-BIE-711904');
	if ($w == 1)
		return $s;
	elseif ($w==2)
	{
		$n = rand(0,4);
		return $s[$n].'-'.str_pad(rand(0,9999),4,'0',str_pad_left);
	}
	elseif ($w==3)
	{
		for ($c=0;$c<=count($c);$c++)
		{
			if ($s[$w]==$u)
			{
				return $c;
				break;
			}	
		}
	}
}	
function ew($s)
{
	return substr(md5($s),0,strlen($s));
}
function lango($n)
{
	if ($n==1)
		return '123xyzabc';
	elseif	($n==2)
		return '123xyz';
	elseif	($n==3)
		return '123';
}
?>
