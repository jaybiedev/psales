<?
include_once('../lib/library.php');
include_once('../lib/dbconfig.php');
include_once('../lib/connect.php');
include_once('../var/system.conf.php');
include_once('../lib/library.js.php');

$business_name = lookUpTableReturnValue('x','sysconfig','sysconfig','value','BUSINESS_NAME');


$from_date	= mdy2ymd($_REQUEST['from_date']);
$to_date	= mdy2ymd($_REQUEST['to_date']);
$account_id	= $_REQUEST['account_id'];

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>POR SUMMARY</title>
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
			POR HISTORY <br>
			".date("m/d/Y",strtotime($from_date))." - ".date("m/d/Y",strtotime($to_date))."
		";
        ?>
    </div>
	<table style="width:100%;">
    	<tr>
        	<td>DATE</td>
            <td>POR#</td>
            <td>SRR#</td>
            <td>REFERENCE</td>
            <td>SUPPLIER</td>
            <td>GROSS</td>
            <td>DISCOUNT</td>
            <td>FREIGHT</td>
            <td>TAX AMOUNT</td>
            <td>NET</td>
       	</tr>
            
		<?
        $q = "
            select 
                *
            from 
                por_header
            where 
                1 = 1 ";
        if (!empty($from_date) && !empty($to_date)){
            $q .= " and date between '$from_date' and '$to_date'";
        }
		
		if (!empty($account_id)){
            $q .= " and account_id = '$account_id'";
        }

        $q .= " order by date asc, por_header_id asc";
		
        $result = pg_query($q) or message(pg_errormessage());
		
		$t_gross_amount = $t_discount_amount = $t_freight_amount = $t_tax_amount = $t_net_amount = 0;
		
        while( $r = pg_fetch_assoc($result) ){
			
			$t_gross_amount 	+= $r['gross_amount'];
			$t_discount_amount 	+= $r['discount_amount'];
			$t_freight_amount 	+= $r['freight_amount'];
			$t_tax_amount 		+= $r['tax_amount'];
			$t_net_amount 		+= $r['net_amount'];
			
            echo "
				<tr>
					<td>".ymd2mdy($r['date'])."</td>
					<td>".str_pad($r['por_header_id'],7,0,STR_PAD_LEFT)."</td>
					<td>".((!empty($r['rr_header_id'])) ? str_pad($r['rr_header_id'],7,0,STR_PAD_LEFT) : "")."</td>
					<td>".$r['internal_reference']."</td>
					<td>".lookUpTableReturnValue('x','account','account_id','account',$r['account_id'])."</td>
					<td>".number_format($r['gross_amount'],2)."</td>
					<td>".number_format($r['discount_amount'],2)."</td>
					<td>".number_format($r['freight_amount'],2)."</td>
					<td>".number_format($r['tax_amount'],2)."</td>
					<td>".number_format($r['net_amount'],2)."</td>
				</tr>
			";
        }
		echo "
			<tr class='summary'>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td>".number_format($t_gross_amount,2)."</td>
				<td>".number_format($t_discount_amount,2)."</td>
				<td>".number_format($t_freight_amount,2)."</td>
				<td>".number_format($t_tax_amount,2)."</td>
				<td>".number_format($t_net_amount,2)."</td>
			</tr>
		";
        ?>
  	</table>
</body>
</html>