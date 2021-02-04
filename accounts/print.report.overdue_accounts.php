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
			OVERDUE ACCOUNTS <br>
			As of ".date("m/d/Y",strtotime($date));
	        ?>
    </div>
	<table style="width:100%;">
    	<tr>
    		<td>ACCOUNT CODE</td>
        	<td>ACCOUNT NAME</td>
            <td style="text-align:right;">BALANCE</td>            
       	</tr>
            
		<?
        $q = "
            select sum(debit) as debit, sum(credit)  as credit, al.account_id ,ac.account, (sum(debit) - sum(credit)) as balance, ac.cardno
			from accountledger as al, account as ac
			where 
			al.account_id = ac.account_id
			and al.\"enable\" = 'Y'
			and ac.\"enable\" = 'Y'
			and date <= '$date'
			and ac.account_type_id != '2'
			group by al.account_id, ac.account, ac.cardno
			having 
			(sum(debit) - sum(credit)) > 0
			order by ac.cardno ASC
		";
		
        $result = pg_query($q) or message(pg_errormessage());
	
		
        while( $r = pg_fetch_assoc($result) ){
						
            echo "
				<tr>
					<td>".$r['cardno']."</td>
					<td>".$r['account']."</td>
			";

			if( $r['balance'] < 1 ) echo " <td style='text-align:right;'>".($r['balance'])."</td> ";
			else echo " <td style='text-align:right;'>".number_format($r['balance'],2)."</td> ";
			echo "
				</tr>
			";
        }
        ?>
  	</table>
</body>
</html>