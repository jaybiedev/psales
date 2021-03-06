<?
include_once('../lib/library.php');
include_once('../lib/dbconfig.php');
include_once('../lib/connect.php');
include_once('../var/system.conf.php');
include_once('../lib/library.js.php');

$business_name = lookUpTableReturnValue('x','sysconfig','sysconfig','value','BUSINESS_NAME');


$from_date        = $_REQUEST['from_date'];
$to_date          = $_REQUEST['to_date'];

$account_class_id = $_REQUEST['account_class_id'];
$account_type_id  = $_REQUEST['account_type_id'];
$top              = $_REQUEST['top'];
$mfrom_date       = mdy2ymd($from_date);
$mto_date         = mdy2ymd($to_date);

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>SALES BY CUSTOMER</title>
<style type="text/css">
	*{ font-family:Arial, Helvetica, sans-serif; font-size:11px;}
	table{
		border-collapse:collapse;
	}
	table td{
		padding:3px;	
	}
	table tr:first-child td,.summary td{
		border-top:1px solid #000;
		border-bottom:1px solid #000;	
		font-weight:bold;
	}
	table td:nth-child(n+6){
		text-align:right;	
	}
	.header{
		font-weight:bold;
	}
</style>
<script type="text/javascript">
function printPage() { print(); } //Must be present for Iframe printing
</script>
</head>
<body>
	<div style="font-weight:bold;">
    	<?
		echo "
			$business_name <br>		
		";

		if( !empty($top) ){
			echo "TOP $top SALES BY CUSTOMER <br>";
		} else {
			echo "SALES BY CUSTOMER <br>";
		}

		echo "
			As of ".date("m/d/Y",strtotime($from_date))." - ".date("m/d/Y",strtotime($to_date))
        ?>
    </div>
	<table style="width:100%;">
    	<tr>
        	<td>ACCOUNT</td>
        	<td>CARD NO</td>
            <td style="text-align:right;">AMOUNT OF SALE</td>            
       	</tr>
            
		<?

		$tables       = currTables($mto_date);
		$sales_header = $tables['sales_header'];
		$sales_detail = $tables['sales_detail'];
		$sales_tender = $tables['sales_tender'];

        $q = "
        	select 
				$sales_header.account_id,
				account,
				cardno,
				sum($sales_header.net_amount) as amount
			from 
				$sales_header,
				account
			where 
				$sales_header.account_id=account.account_id and 
				$sales_header.date>='$mfrom_date' and 
				$sales_header.date<='$mto_date' and
				$sales_header.status!='V' ";

		if ($account_type_id != 'All Types')
		{
			$q .= " and account.account_type_id = '$account_type_id'";
		}			
		if ($account_class_id != '')
		{
			$q .= " and account.account_class_id = '$account_class_id'";
		}			
					
		$q .= " group by
					$sales_header.account_id, account, cardno
				order by 
					amount desc ";
		if ($top != '')
		{
				$q .= " offset 0 limit $top ";
		}
		
        $result = pg_query($q) or message(pg_errormessage());
	
		
        while( $r = pg_fetch_assoc($result) ){
						
            echo "
				<tr>
					<td>".$r['account']."</td>
					<td>".$r['cardno']."</td>
					<td style='text-align:right;'>".number_format($r['amount'],2)."</td>
				</tr>
			";
        }
        ?>
  	</table>
</body>
</html>