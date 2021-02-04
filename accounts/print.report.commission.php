<?
include_once('../lib/library.php');
include_once('../lib/dbconfig.php');
include_once('../lib/connect.php');
include_once('../var/system.conf.php');
include_once('../lib/library.js.php');

$business_name = lookUpTableReturnValue('x','sysconfig','sysconfig','value','BUSINESS_NAME');


$date	= mdy2ymd($_REQUEST['date']);

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>OVERDUE ACCOUNTS</title>
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
			COMMISSION <br>
			As of ".date("m/d/Y",strtotime($date));	
        ?>
    </div>
	<table style="width:100%;">
    	<tr>
        	<td>DATE</td>
        	<td>INVOICE</td>
        	<td>ACCOUNT NAME</td>
            <td style="text-align:right;">AMOUNT</td>            
       	</tr>
            
		<?

		$q = "
			SELECT
				DATE,
				invoice,
				ac.account,
				credit,
				debit,
				(
					COALESCE (credit, 0) - COALESCE (debit, 0)
				) AS amount
			FROM
				accountledger AS al,
				account AS ac
			WHERE
				al.account_id = ac.account_id
			AND al.\"enable\" = 'Y'
			AND ac.\"enable\" = 'Y'
			and date <= '$date'
			AND TYPE = 'P'
			ORDER BY
				date desc					
		";
        
		
        $result = pg_query($q) or message(pg_errormessage());
	
		
        while( $r = pg_fetch_assoc($result) ){
						
            echo "
				<tr>
					<td>".ymd2mdy($r['date'])."</td>
					<td>".$r['invoice']."</td>
					<td>".$r['account']."</td>
					<td style='text-align:right;'>".number_format($r['amount'],2)."</td>
				</tr>
			";
        }
        ?>
  	</table>
</body>
</html>