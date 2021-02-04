<?
include_once('../lib/library.php');
include_once('../lib/dbconfig.php');
include_once('../lib/connect.php');
include_once('../var/system.conf.php');
include_once('../lib/library.js.php');

$category_id 	= $_REQUEST['category_id'];

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Mark Up Pricing Report</title>
<style type="text/css">
	*{ font-family:Arial, Helvetica, sans-serif; font-size:11px;}
	table{
		border-collapse:collapse;
	}
	table td{
		padding:3px;	
	}
	table tr:first-child td{
		border-top:1px solid #000;
		border-bottom:1px solid #000;	
		font-weight:bold;
	}
	table td:nth-child(n+2){
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
	<table style="width:100%;">
    	<tr>
        	<td>ITEM</td>
            <td style="width:10%;">COST</td>
            <td style="width:10%;">MARKUP</td>
            <td style="width:10%;">COST+MARKUP</td>
            <td style="width:10%;">PRICING</td>
       	</tr>
            
		<?
        $q = "
            select 
                *
            from 
                stock
            where 
                enable='Y' and inventory = 'Y' ";
        if ($category_id != ''){
            $q .= " and category_id='$category_id'";
        }
        $q .= " order by category_id, stock asc";
		$category_id = 'x';
        $result = pg_query($q) or message(pg_errormessage());
        while( $r = pg_fetch_assoc($result) ){
			if($category_id != $r['category_id']){
				echo "
					<tr>
						<td colspan='5' class='header'>".lookUpTableReturnValue('x','category','category_id','category',$r['category_id'])."</td>
					</tr>
				";
				$category_id = $r['category_id'];
			}	
			
            echo "
				<tr>
					<td style='padding-left:10px;'>".$r['stock']."</td>
					<td>".number_format($r['cost1'],2)."</td>
					<td>".number_format($r['markup'],2)."%</td>
					<td>".number_format(($r['cost1'] + ($r['cost1'] * ($r['markup']/100))),2)."</td>
					<td>".number_format($r['price1'],2)."</td>
				</tr>
			";
        }
        ?>
  	</table>
</body>
</html>