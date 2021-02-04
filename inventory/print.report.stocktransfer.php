<?
require_once(dirname(__FILE__).'/../lib/lib.salvio.php');
include_once('../lib/library.php');
include_once('../lib/dbconfig.php');
include_once('../lib/connect.php');
include_once('../var/system.conf.php');
include_once('../lib/library.js.php');

$business_name = lookUpTableReturnValue('x','sysconfig','sysconfig','value','BUSINESS_NAME');

$from_date     = mdy2ymd($_REQUEST['from_date']);
$to_date       = mdy2ymd($_REQUEST['to_date']);

function getData($from_date,$to_date){
	$sql  = "
		select 
			h.*, d.*, s.stock, s.barcode, s.unit1, s.unit3, b_from.branch as from_branch, b_to.branch as to_branch
		from
			stocktransfer_header as h 
			inner join stocktransfer_detail as d on h.stocktransfer_header_id = d.stocktransfer_header_id
			inner join stock as s on d.stock_id = s.stock_id
			inner join branch as b_to on h.branch_id_to = b_to.branch_id
			inner join branch as b_from on h.branch_id_from = b_from.branch_id
		where
			date between '$from_date' and '$to_date'
		order by date asc
	";

	return lib::getArrayDetails($sql);
}


?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>STOCK TRANSFER HISTORY</title>
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
			STOCK TRANSFER HISTORY <br>
			".date("m/d/Y",strtotime($from_date))." - ".date("m/d/Y",strtotime($to_date))."
		";
        ?>
    </div>
	<table style="width:100%;">
    	<tr>
        	<td>DATE</td>
            <td>TX#</td>            
            <td>FROM BRANCH</td>
            <td>TO BRANCH</td>
       		<td>ITEM</td>                 
            <td style="text-align:right; width:10%;">CASE QTY</td>
            <td style="width:8%;">U.O.M</td>
            <td style="text-align:right; width:10%;">UNIT QTY</td>
            <td style="width:8%;">U.O.M</td>
       	</tr>
            
		<?
		$arr = getData($from_date,$to_date);
		$t_unit_qty  = $t_case_qty = 0;
		if( count($arr) ){
			foreach ($arr as $r) {
				$t_unit_qty += $r['unit_qty'];
				$t_case_qty += $r['case_qty'];

				echo "
					<tr>
						<td>".ymd2mdy($r['date'])."</td>
						<td>".str_pad($r['stocktransfer_header_id'],7,0,STR_PAD_LEFT)."</td>																	
						<td>$r[from_branch]</td>						
						<td>$r[to_branch]</td>						
						<td>$r[stock]</td>						
						<td style='text-align:right;'>".number_format($r['case_qty'],2)."</td>						
						<td style='text-transform:uppercase;'>$r[unit3]</td>
						<td style='text-align:right;'>".number_format($r['unit_qty'],2)."</td>
						<td style='text-transform:uppercase;'>$r[unit1]</td>
					</tr>
				";
			}
		}
                
		echo "
			<tr class='summary'>
				<td></td>
				<td></td>
				<td></td>				
				<td></td>				
				<td></td>				
				<td style='text-align:right;'>".number_format($t_case_qty,2)."</td>
				<td></td>				
				<td style='text-align:right;'>".number_format($t_unit_qty,2)."</td>				
				<td></td>
			</tr>
		";
		
        ?>
  	</table>
</body>
</html>