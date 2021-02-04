<style type="text/css">
	*{ font-family:Arial, Helvetica, sans-serif; font-size:11px;}
	table{
		border-collapse:collapse;
		width:100%;
		border:1px solid #000;
	}
	table thead td{
		border:1px solid #000;
	}

	table td{
		border-left: 1px solid #000;
		border-right: 1px solid #000;
	}
		
	table th{
		border-top:1px solid #000;
		border-bottom:1px solid #000;	
	}
</style>
<script type="text/javascript">
	function printPage() { print(); } //Must be present for Iframe printing
</script>
<?
include_once('../lib/library.php');
include_once('../lib/dbconfig.php');
include_once('../lib/connect.php');
include_once('../var/system.conf.php');
include_once('../lib/library.js.php');
include_once('../lib/lib.salvio.php');

$from_date = mdy2ymd($from_date);
$to_date = mdy2ymd($to_date);

$sql = "
	select 
		*
	from
		mul_qty_audit
	where
		audit_date >= '$from_date'
	and audit_date <= '$to_date'	
";

if ( !empty($terminal) ) $sql .= " and terminal = '$terminal'";

$sql .= " order by audit_time asc";

$arr = lib::getArrayDetails($sql);
?>
<p style="text-align:center; font-weight:bold; font-size:13px;">
	Audit Trail <br>
	from <?= lib::ymd2mdy($from_date) ?> to <?= lib::ymd2mdy($to_date) ?>
</p>
<table>
	<thead>
		<tr>
			<td style="width:1%">#</td>
			<td>AUDIT</td>
		</tr>
	</thead>
	<tbody>
		<? if( count($arr) ){ ?>
		 	<? foreach ($arr as $i => $r) { ?>
			 	<tr>
					<td><?= ( $i + 1 ) ?></td>
					<td><?= $r['remark'] ?></td>
				</tr>
			<? } ?>
		 <? } ?>		
	</tbody>
</table>

