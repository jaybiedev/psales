<?
require_once("numtowords.class.php");
function insertReference($gltran_header_id,$aReference){
	if(!empty($aReference)){
		
		foreach($aReference as $r){
			$header 	= $r['header'];
			$header_id 	= $r['header_id'];
		
			@pg_query("insert into gltran_reference (gltran_header_id,header_id,header) values ('$gltran_header_id','$header_id','$header') ");	
		}
	}
}

function insertGL($gltran_header_id,$gchart_id,$debit,$credit,$reference,$reference_source){
	$result 	= @pg_query(" select * from gchart where gchart_id = '$gchart_id' ");
	$r 			= @pg_fetch_assoc($result);
	$gchart 	= $r['gchart'];
	
	@pg_query("insert into gltran_detail (gltran_header_id,gchart_id,gchart,debit,credit,reference,reference_source,enable) values 
	('$gltran_header_id','$gchart_id','$gchart','$debit','$credit','$reference','$reference_source','Y')");
}

function getAPLedgerAttr($apledger_id){
	$result = @pg_query("select * from apledger where apledger_id = '$apledger_id'");
	$r = @pg_fetch_assoc($result);
	
	return $r;
}
function hasNoMultipleSupplier($list){
	$account_ids = array();
	foreach($list as $apledger_id){
		$r = getAPLedgerAttr($apledger_id);		
		$account_ids[] = $r['account_id'];
	}
	$x = 0;
	$id = 0;
	foreach($account_ids as $account_id){
		if($x == 0){
			$id = $account_id;
		}else{
			if($id != $account_id){
				return false;	
			}
		}
		
		$x++;
	}
	
	return $id;
}

function updateStatustoY($list){
	foreach($list as $apledger_id){
		@pg_query("update apledger set status = 'Y'  where apledger_id = '$apledger_id'");
		
	}
}

if($p1 == "Pay"){
	$aMark = $_REQUEST['aMark'];	
	
	$total_rr = 0;
	$total_pr = 0;
	$account_id = hasNoMultipleSupplier($aMark);
	if($account_id == false){
		message1("You have chosen Multiple Suppliers. Please go back and choose only one supplier.");
		exit;
	}
	$por_ids = array();
	$rr_ids = array();
	
	$aReference = array();
	
	foreach($aMark as $apledger_id){	
		$r = getAPLedgerAttr($apledger_id);	
		$amount = $r['credit'];
		
		$tmp_reference = array();
		
		if($r['type'] == "RR"){
			$total_rr += $amount;
			$rr_ids[] = $r['record_id'];
			
			#insert reference to rr here
			$tmp_reference['header'] 	= "rr_header_id";
			$tmp_reference['header_id']	= $r['record_id'];
			
		}else if($r['type'] == "PR"){
			$total_pr += $amount;
			$por_ids[] = $r['record_id'];
			#insert reference to pr_here
			
			$tmp_reference['header'] 	= "por_header_id";
			$tmp_reference['header_id']	= $r['record_id'];
		}
		
		$aReference[] = $tmp_reference;
	}	
	
	
	if(!empty($rr_ids)){
		$particulars = "SRR # :";
		$particulars .= implode($rr_ids);
	}
	
	if(!empty($por_ids)){
		$particulars .= "\n";
		$particulars .= "POR # :";
		$particulars .= implode($por_ids);
	}
	
	#POST TO GL
	$admin_id 			= $ADMIN['admin_id'];
	$ip					= $_SERVER['REMOTE_ADDR'];
	$time 				= date("H:i:s");
	$date				= date("Y-m-d");
	$journal_id			= 1; #DISBURSEMENT JOURNAL
	@pg_query("
		insert into gltran_header (date,particulars,journal_id,account_id,admin_id,status,pcenter_id,time,ip) values
		('$date','$particulars','$journal_id','$account_id','$admin_id','S','1','$time','$ip')
	") or message1(pg_errormessage().$q);
	$gltran_header_id = @db_insert_id('gltran_header');		
	#$gltran_header_id = @pg_query("SELECT lastval()");
	
	#DEBIT - ACCOUNTS PAYALBES
	#	7 - ACCOUNTS PAYALBE
	
	insertGL($gltran_header_id,7,$total_rr,0,'',"");
	
	#CREDIT - PURCHASE RETURNS AND ALLOWANCES
	# 	230 - PURCHASE RETURNS AND ALLOWANCES - EAST CENTRE
	# 	228 - PURCHASE RETURNS AND ALLOWANCES - ARANETA
	if($total_pr > 0){
		insertGL($gltran_header_id,230,0,$total_pr,'','');
	}
	
	#CREDIT - CASH IN BANK
	#	14 - CASH IN BANK - MBTC EAST CENTRE
	insertGL($gltran_header_id,14,0,$total_rr - $total_pr,'','');
	$p2 = "Load";	
	$id = $gltran_header_id;
	
	updateStatustoY($aMark);
	
	#POST REFERENCE
	insertReference($gltran_header_id,$aReference);
}
?>
<script type="text/javascript">
function printFrame(id)
{
    var iframe = document.frames ? document.frames[id] : document.getElementById(id);
    var ifWin = iframe.contentWindow || iframe;
    iframe.focus();
    ifWin.printPage();
    return false;
}
</script>
<script>
function vJournal()
{
//	alert(document.getElementById('journal_id').value);
	if (document.getElementById('journal_id').value != 2)
	{
		document.getElementById('SalesAutoPost').disabled = 1;
	}
	else
	{
		document.getElementById('SalesAutoPost').disabled = 0;
	}
	
}
</script>
<STYLE TYPE="text/css">
<!--
	.altTextFormat {
	background-color: #FFFFFF;
	font-family: verdana;
	border: #CCCCCC 1px solid;
	font-size: 12px;
	color: #000000
	} 
	.autocomplete {
	font-family: verdana;
	border: #CCCCCC 1px solid;
	font-size: 10px;
	color: #000000;
	
	} 
	
	.altNumFormat {
	background-color: #FFFFFF;
	font-family: verdana;
	border: #CCCCCC 1px solid;
	font-size: 12px;
	color: #000000;
	text-align:right
	} 
	
	.altTextArea {
	background-color: #FFFFFF;
	font-family: verdana;
	border: #CCCCCC 1px solid;
	font-size: 12px;
	color: #000000
	} 
	
	SELECT {
	background-color: #FFFFFF;
	font-family: verdana;
	border: #CCCCCC 1px solid;
	font-size: 10px;
	margin:0px;
	color: #000000
	} 			

	.altButtonFormat {
	background-color: #CFCFCF;
	font-family: verdana;
	font-size: 11px;
	padding: 1px;
	margin: 0px;
	color: #1F016D
	} 
.style1 {font-family: Arial, Helvetica, sans-serif}
-->
</STYLE>
<? 
//$this_module = 'Disbursement Entry Module';
//include_once('screen_header.php');

if (!session_is_registered("aItems")) {	
	session_register("aItems");
	$aItems=array();
}
if (!session_is_registered("gltran")) {	
	session_register("gltran");
	$gltran=array();
	$gltran['date'] = date('Y-m-d');
}
//echo 'p1 '.$p1;
$fields_header = array('journal_id','xrefer','account','account_id','mcheck','date_check','particulars','date','pcenter_id','po_header_id');
if (!in_array($p1, array(NULL,'Load','New','PayRR')))
{
	for ($c=0;$c<count($fields_header);$c++)
	{
		if (substr($fields_header[$c],0,4) == 'date')
		{
			$gltran[$fields_header[$c]] = mdy2ymd($_REQUEST[$fields_header[$c]]);
		}
		else
		{
			$gltran[$fields_header[$c]] = $_REQUEST[$fields_header[$c]];
		}
	}
}

if ($p1 == 'AutoPost' && $SYSCONF['AUTOPOST_GL_SALES'] !='Y')
{
	message("No Auto Post...");
}
elseif ($p1 == 'AutoPost')
{
	include_once('gltran.autopost.php');

	$x = autopost($gltran['date']);
	if (!x)
	{
		message("Error Auto Posting....");
	}
	else
	{
	
		$gltran=null;
		$gltran=array();
		$item = null;
		$aItems=null;
		$aItems=array();
		$q = "select * from gltran_header where gltran_header_id='$x'";
		$qr = @pg_query($q) or message(pg_errormessage());

		if (@pg_num_rows($qr) == 0)
		{
			message("Transaction not found...");
		}
		else
		{
			$r = @pg_fetch_assoc($qr);
			$gltran = $r;
			$gltran['account'] = lookUpTableReturnValue('x','account','account_id','account',$r['account_id']);
			$q = "select * from gltran_detail, gchart where gchart.gchart_id=gltran_detail.gchart_id and gltran_header_id='".$gltran['gltran_header_id']."'";
			$qr = @pg_query($q) or message(pg_errormessage());
			while ($r = @pg_fetch_assoc($qr))
			{
				$temp = $r;
				$temp['gcode'] = $r['acode'].'-'.$r['scode'];
				$aItems[] = $temp;
	
			}
		}
		message('Sales Auto Post Successful...');
	}	
		
}

elseif ($p1 == 'PayRR')
{	$gltran=null;
	$gltran=array();
	$item = null;
	$aItems=null;
	$aItems=array();

	$newarray=null;
	$newarray=array();

	$cmarray=null;
	$cmarray=array();

	$rrarray=null;
	$rrarray=array();

	$gltran['date'] = date('Y-m-d');
	$gltran['date_check'] = date('Y-m-d');
	$gltran['pcenter_id'] = 1;
	$gltran['journal_id'] = 1;
	
	$total_credit = $total_debit = $total_dm = 0;
	//--- credit disbursement code
	$q = "select * from accountingentry where accountingentry = 'gccredit_disbursement_id' and enable='Y' ";
	$qr = @pg_query($q) or message1(pg_errormessage());
	$r = @pg_fetch_object($qr);
	$gccredit_disbursement_id = $r->value;
	
	if ($gccredit_disbursement_id > '0')
	{
		$q = "select * from gchart where gchart_id = '$gccredit_disbursement_id'";
		$qr = @pg_query($q) or message1(pg_errormessage());
		$r = @pg_fetch_object($qr);
	
		$gccredit_disbursement = $r->gchart;
		$gccredit_disbursement_acode = $r->acode;
		$gccredit_disbursement_scode = $r->scode;
	}
	else
	{
		$gccredit_disbursement_id = 0;
	}	

	//--- debit disbursement code
	$q = "select * from accountingentry where accountingentry = 'gcdebit_disbursement_id' and enable='Y' ";
	$qr = @pg_query($q) or message1(pg_errormessage());
	$r = @pg_fetch_object($qr);
	$gcdebit_disbursement_id = $r->value;
	
	if ($gcdebit_disbursement_id > '0')
	{
		$q = "select * from gchart where gchart_id = '$gcdebit_disbursement_id'";
		$qr = @pg_query($q) or message1(pg_errormessage());
		$r = @pg_fetch_object($qr);
	
		$gcdebit_disbursement = $r->gchart;
		$gcdebit_disbursement_acode = $r->acode;
		$gcdebit_disbursement_scode = $r->scode;
	}
	else
	{
		$gcdebit_disbursement_id = 0;
	}	

	//--- credit accounting code
	$q = "select * from accountingentry where accountingentry = 'gccredit_payable_id' and enable='Y' ";
	$qr = @pg_query($q) or message1(pg_errormessage());
	$r = @pg_fetch_object($qr);
	$gccredit_payable_id = $r->value;
	
	if ($gccredit_payable_id > '0')
	{
		$q = "select * from gchart where gchart_id = '$gccredit_payable_id'";
		$qr = @pg_query($q) or message1(pg_errormessage());
		$r = @pg_fetch_object($qr);
	
		$gccredit_payable = $r->gchart;
		$gccredit_payable_acode = $r->acode;
		$gccredit_payable_scode = $r->scode;
	}
	else
	{
		$gccredit_payable_id = 0;
	}	

	//--- debit accounting code
	$q = "select * from accountingentry where accountingentry = 'gcdebit_payable_id' and enable='Y' ";
	$qr = @pg_query($q) or message1(pg_errormessage());
	$r = @pg_fetch_object($qr);
	$gcdebit_payable_id = $r->value;

	if ($gcdebit_payable_id > '0')
	{
		$q = "select * from gchart where gchart_id = '$gcdebit_payable_id'";
		$qr = @pg_query($q) or message1(pg_errormessage());
		$r = @pg_fetch_object($qr);
	
		$gcdebit_payable = $r->gchart;
		$gcdebit_payable_acode = $r->acode;
		$gcdebit_payable_scode = $r->scode;
	
	}
	else
	{
		$gcdebit_payable_id = 0;
	}
	
	//--- cm accounting code
	$q = "select * from accountingentry where accountingentry = 'gccm_payable_id' and enable='Y' ";
	$qr = @pg_query($q) or message1(pg_errormessage());
	$r = @pg_fetch_object($qr);
	$gccm_payable_id = $r->value;

	if ($gccm_payable_id > '0')
	{
		$q = "select * from gchart where gchart_id = '$gccm_payable_id'";
		$qr = @pg_query($q) or message1(pg_errormessage());
		$r = @pg_fetch_object($qr);

		$gccm_payable = $r->gchart;
		$gccm_payable_acode = $r->acode;
		$gccm_payable_scode = $r->scode;
	}
	else
	{
		$gccm_payable_id = 0;
	}
	//-- done retrieving constants...
	
	$aMark = $_REQUEST['aMark'];
	$rr_id = implode("','",$aMark);

//	$q = "select * from rr_header where rr_header_id in ('$rr_id')";
	$q = "select * from apledger where record_id in ('$rr_id')";
	$qr = @pg_query($q) or messge1(pg_errormessage());

	$errmsg = '';
	while ($ra = @pg_fetch_assoc($qr))
	{
		if ($ra['type']=='RR')
		{
			$q = "select * from rr_header where invoice='".$ra['reference']."'";
			$qr = @pg_query($q) or messge1(pg_errormessage());
			$r=@pg_fetch_assoc($qr);
			if ($gltran['account_id'] == '' && $r['account_id']*1 > '0')
			{
				$gltran['account_id'] = $r['account_id'];
				$gltran['account'] =  lookUpTableReturnValue('x','account','account_id','account',$gltran['account_id']);
			}
			elseif ($gltran['account_id'] != '' && $r['account_id'] != $gltran['account_id'])
			{
				$errmsg .= ' SRR# '.$r['rr_header_id'].' ; ';
				continue;
			}
	
			$temp = null;
			$temp = array();
			$temp = $r;
			$temp['debit'] = $r['net_amount'];
			$temp['reference'] = $r['invoice'];
			$temp['reference_source'] = 'RR';
			$temp['gchart_id'] = $gcdebit_disbursement_id;
			$temp['gchart'] = $gcdebit_disbursement;
			$temp['scode'] = $gcdebit_disbursement_scode;
			$temp['acode'] = $gcdebit_disbursement_acode;
			$temp['gcode'] = $gcdebit_disbursement_acode.'-'.$gcdebit_scode;
	
			$rrarray[] = $temp;
			$total_debit += $temp['debit'];
		
			//-- CM For Purchase Return Adjustments	
			$qret = "select * from por_header where rr_header_id='".$r['rr_header_id']."'";
			$qretr = @pg_query($qret) or message1(pg_errormessage().$q);
			if (@pg_num_rows($qretr) > 0)
			{
				$temp = null;
				$temp = array();
		
				$result = @pg_fetch_assoc($qretr);
				$temp = $result;
	
				$temp['credit'] = $result['net_amount'];
				$temp['reference'] = $result['reference'];
				$temp['reference_source'] = 'PR';
				$temp['gchart_id'] = $gcdebit_payable_id;
				$temp['gchart'] = $gccm_payable;
				$temp['scode'] = $gccm_payable_scode;
				$temp['acode'] = $gccm_payable_acode;
				$temp['gcode'] = $gccm_payable_acode.'-'.$gccm_scode;
	
				$total_cr += $temp['credit'];
				$cmarray[] = $temp;
	
			}
		} else
		{
			//-- CM For Purchase Return Adjustments	
			$qret = "select * from por_header where reference='".$ra['reference']."'";
			$qretr = @pg_query($qret) or message1(pg_errormessage().$qret);
			if (@pg_num_rows($qretr) > 0)
			{
				$temp = null;
				$temp = array();
		
				$result = @pg_fetch_assoc($qretr);
				$temp = $result;
	
				$temp['credit'] = $result['net_amount'];
				$temp['reference'] = $result['reference'];
				$temp['reference_source'] = 'PR';
				$temp['gchart_id'] = $gcdebit_payable_id;
				$temp['gchart'] = $gccm_payable;
				$temp['scode'] = $gccm_payable_scode;
				$temp['acode'] = $gccm_payable_acode;
				$temp['gcode'] = $gccm_payable_acode.'-'.$gccm_scode;
	
				$total_cm += $temp['credit'];
				$cmarray[] = $temp;
	
			}
		}
	}
	
	$temp = null;
	$temp = array();
	
	$gltran['total_debit'] = $total_debit;
	$gltran['check_amount'] = $total_debit - ($total_cm+$total_cr);
	$gltran['total_credit'] = $total_cm+$total_cr+$gltran['check_amount'];

//	$temp['reference_source'] = 'RR';
	$temp['credit'] = $gltran['check_amount'];
	$temp['gchart_id'] = $gccredit_disbursement_id;
	$temp['gchart'] = $gccredit_disbursement;
	$temp['scode'] = $gccredit_disbursement_scode;
	$temp['acode'] = $gccredit_disbursement_acode;
	$temp['gcode'] = $gccredit_disbursement_acode.'-'.$gccredit_scode;

	$aItems[] = $temp;

	if ($rrarray)
	{
	
		foreach ($rrarray as $temp)
		{
			$aItems[] = $temp;
		}
		
	}

	if ($cmarray)
	{
		foreach ($cmarray as $temp)
		{
			$aItems[] = $temp;
		}
	}

	if ($errmsg != '')
	{
		$errmsg .= ' Belongs to different supplier...';
		message1("<br>[ ".$errmsg." ] <br><br>");
	}
}
if ($p1=="New")
{
	$pcenter_id = $gltran['pcenter_id'];
	if ($pcenter_id == '')
	{
		$q = "select * from pcenter order by pcenter_id desc offset 0 limit 1";
		$qr = @pg_query($q) or message(pg_errormessage());
		$r = @pg_fetch_object($qr);
		$pcenter_id = $r->pcenter_id;
	}
	$gltran=null;
	$gltran=array();
	$item = null;
	$aItems=null;
	$aItems=array();
	$searchkey="";
	$gchart_id="";
	$gltran['date'] = date('Y-m-d');
	$gltran['date_check'] = date('Y-m-d');
	$gltran['pcenter_id'] = $pcenter_id;
}
elseif ($p2 == 'Load')
{
	$gltran=null;
	$gltran=array();
	$item = null;
	$aItems=null;
	$aItems=array();
	$q = "select * from gltran_header where gltran_header_id='$id'";
	$qr = @pg_query($q) or message(pg_errormessage());
	if (@pg_num_rows($qr) == 0)
	{
		message("Transaction not found...");
	}
	else
	{
		$r = @pg_fetch_assoc($qr);
		$gltran = $r;
		$gltran['account'] = lookUpTableReturnValue('x','account','account_id','account',$r['account_id']);
		$q = "select * from gltran_detail, gchart where gchart.gchart_id=gltran_detail.gchart_id and gltran_header_id='$id'";
		$qr = @pg_query($q) or message(pg_errormessage());
		while ($r = @pg_fetch_assoc($qr))
		{
			$temp = $r;
			$temp['gcode'] = $r['acode'].'-'.$r['scode'];
			if ($temp['gchart_id']==14) $gltran['check_amount']=$r['credit'];
			$aItems[] = $temp;
		}
	}
}
elseif ($p1 == 'QuickAdd' && $_REQUEST['newaccount']!='')
{
	$q = "insert into account set account='".$_REQUEST['newaccount']."', account_type='".$_REQUEST['account_type']."',enable='Y'";
	$qr = @pg_query($q) or message(pg_errormessage());
	if ($qr)
	{
		$id = @pg_insert_id();
		$gltran['account'] = $_REQUEST['newaccount'];
		$gltran['account_id'] = $id;
	}	
}
elseif (($p1 == 'Save' or $p1=="Save & Print") && $gltran['journal_id']=='')
{
	message('Cannot Save. Please Select Journal/Book');
}
elseif ($p1 == 'Save' && in_array($gltran['status'], array('A','P'))) 
{
	message('Cannot Save/Edit Transaction is already '.$gltran['status']);
}
elseif ($p1 == 'Save' or $p1=="Save & Print") 
{
		if ($gltran['gltran_header_id'] == '') 
		{
				$time = date('G:i');

				$q = "insert into gltran_header ( time, admin_id, status, ip ";
				$qq = ") values ('$time', '".$ADMIN['admin_id']."','S', '".$_SERVER['REMOTE_ADDR']."'";
				for ($c=0;$c<count($fields_header);$c++)
				{
					if ($fields_header[$c] == 'account') continue;
					$q .= ",".$fields_header[$c];
					$qq .= ",'".$gltran[$fields_header[$c]]."'";
				}
				$q .= $qq.")";
				$qr = @query($q) or message1(db_error().$q);
		
				if ($qr && pg_affected_rows($qr)>0)
				{
					$ok=1;
					$gltran['gltran_header_id'] = db_insert_id('gltran_header');

					$q = "select * from journal_reference where journal_id='".$gltran['journal_id']."' and enable='Y'";
					$qr = @pg_query($q) or message(pg_errormessage());
					if (@pg_num_rows($qr) > 0)
					{
						$r = @pg_fetch_object($qr);
						$gltran['xrefer'] = str_pad($r->value + 1,10,'0',STR_PAD_LEFT);
						$q = "update journal_reference set value1 = '".$gltran['xrefer']."' where journal_id='".$gltran['journal_id']."' and enable='Y'";
						$qr = @pg_query($q) or message(pg_errormessage());
					}
					else
					{
						$gltran['xrefer'] = '0000000001';
						$q = "insert into journal_reference (journal_id,value1) value ('".$gltran['journal_id']."',  '".$gltran['xrefer']."')";
						$qr = @pg_query($q) or message(pg_errormessage());
					}
				}
					
		}		
		else 
		{
		
				$q = "update gltran_header set gltran_header_id = '".$gltran['gltran_header_id']."'";
				for ($c=0;$c<count($fields_header);$c++)
				{
					if ($fields_header[$c] == 'account') continue;
		
					$q .= ",".$fields_header[$c]."='".$gltran[$fields_header[$c]]."'";
				}
				$q .= " where gltran_header_id = '".$gltran['gltran_header_id']."'";
		
				$qr = @query($q) or message1(db_error().$q);

		}	


		$fields_detail = array('gchart_id','gchart','debit','credit','reference','narrative','reference_source');
		if ($gltran['gltran_header_id'] != '')
		{
			$mctr=0;
			foreach ($aItems as $xItem) 
			{
				$temp = null;
				$temp = array();
				$temp	= $xItem;
				if ($temp['debit'] == '') $temp['debit'] = 0;
				if ($temp['credit'] == '') $temp['credit'] = 0;
	
				if ($xItem['gltran_detail_id']==null) 
				{
					$q = "insert into gltran_detail (gltran_header_id ";
					$qq = ") values ('".$gltran['gltran_header_id']."'";
					for ($c=0;$c<count($fields_detail);$c++)
					{
						$q .= ",".$fields_detail[$c];
						$qq .= ",'".$temp[$fields_detail[$c]]."'";
					}
					$q .= $qq.")";
					$qr = @query($q) or message1(db_error().$q);

					if ($qr && @pg_affected_rows($qr)>0)
					{
						$ok=1;
						$temp['gltran_detail_id'] = db_insert_id('gltran_detail');
					}			
					
					$aItems[$mctr]	= $temp;
				}
				else 
				{
					$q = "update gltran_detail set enable='Y'";
					for ($c=0;$c<count($fields_detail);$c++)
					{
						$q .= ",".$fields_detail[$c]."='".$temp[$fields_detail[$c]]."'";
					}
					$q .= " where gltran_detail_id = '".$xItem['gltran_detail_id']."'";


					$qr = @query($q) or message1(db_error().$q);
				}

//				if ($temp['reference_source'] == 'RR' && $temp['reference']!='')
				if ($temp['reference']!='')
				{
					$q = "update apledger set status='Y' where reference= '".$temp['reference']."' and account_id = '".$gltran['account_id']."'";
					$qar = @pg_query($q) or message1(db_error().$q);
				}
				$mctr++;	
			}
			$gltran["status"] = "S";
		}
		if ($gltran['status'] ==  'S')
		{
			message("Transaction Saved...");
		}
		
}
elseif ($p1 == "Delete Checked" && in_array($gltran['status'], array('A','P'))) 
{
	message('Cannot Delete/Edit Transaction is already '.$gltran['status']);
}
elseif ($p1=="Delete Checked") 
{
	$ctr=0;
	$temp=null;
	$temp=array();
	
	foreach ($aItems as $index)
	{
		$ctr++;
		if (!in_array($ctr, $delete))
		{
			$temp[] = $index;
		}
		else
		{
			if ($index['gltran_detail_id'] != '')
			{
				$q = "delete from gltran_detail where gltran_detail_id='".$index['gltran_detail_id']."'";
				$qr = @pg_query($q) or message(pg_errormessage());
			}
		}
	}
	$aItems = $temp;
}
elseif ($p1 == "Insert" && in_array($gltran['status'], array('A','P'))) 
{
	message('Cannot Insert/Edit Transaction is already '.$gltran['status']);
}
elseif ($p1=="Insert") 
{

	
	if ($gchart_id=='' or ($debit=='' and $credit=='')) message("Please provide account code in details.");
	else 
	{
		$qr = pg_query("Select * from gchart where gchart_id='$gchart_id'") or die (pg_errormessage());
		$r = pg_fetch_object($qr);
		$searchkey			=	$r->acode.'-'.$r->scode;
		$gchart				=	$r->gchart;
		$gchart_id			= 	$r->gchart_id;
		$gcode 				= 	$r->acode.'-'.$r->scode;
		
		$item = array();
		$item["gchart_id"]	=	$gchart_id;
		$item["gchart"]		= 	$gchart;
		$item["narrative"]	= 	$narrative;
		$item["gcode"]		=	$gcode;
		$item["debit"]		=	$debit;
		$item["credit"]		=	$credit;
		$item["reference"]	=	$reference;
		$item["reference_date"]		=	$reference_date;
		$aItems[]			=	$item;
		$item=null;
		$searchkey			= "";
		$gchart				= "";
		$gchart_id			= "";
		$debit				= "";
		$credit				= "";
		$reference 			= "";
		$reference_date		= "";
		$narrative			= "";
		$focus_flag			= 'searchkey';
	}
}
elseif ($p1 == "Update" && in_array($gltran['status'], array('A','P'))) 
{
	message('Cannot Insert/Edit Transaction is already '.$gltran['status']);
}
elseif ($p1=="Update") {
	if ($gchart_id=='' or ($debit=='' and $credit=='')) message("Please provide account code in details.");
	else 
	{
		$qr = pg_query("Select * from gchart where gchart_id='$gchart_id'") or message (pg_errormessage());
		$r = pg_fetch_object($qr);
		$searchkey			=	$r->acode.'-'.$r->scode;
		$gchart				=	$r->gchart;
		$gchart_id			= 	$r->gchart_id;
		$gcode 				= 	$r->acode.'-'.$r->scode;

		$dummy 				=   $aItems[$ctr-1];
		$item["gltran_detail_id"] = $dummy["gltran_detail_id"];
		$item["gchart_id"]	=	$gchart_id;
		$item["gcode"]		=	$gcode;
		$item["gchart"]		= 	$gchart;
		$item['narrative']	= 	$narrative;
		$item["debit"]		=	$debit;
		$item["credit"]		=	$credit;
		$item["reference"]	=	$reference;
		$item["reference_date"]		=	$reference_date;
		$aItems[$ctr-1]		=	$item;
		$item=null;
		$searchkey			= "";
		$gchart				= "";
		$gchart_id			= "";
		$debit				= "";
		$credit				= "";
		$narrative			= "";
		$reference 			= "";
		$reference_date		= "";
		$focus_flag			= 'searchkey';
	}
}

elseif ($p1 == 'Edit' && $id!='') 
{
//echo 'test suspend here '.$id;
//exit;
	$mctr=0;
	$item=array();
	foreach ($aItems as $xItem) 
	{
		$mctr++;
		if ($id == $mctr) 
		{
			$searchkey  = $xItem["gcode"];
			$gchart 	= $xItem["gchart"];	
			$gchart_id	= $xItem["gchart_id"];	
			$debit		= $xItem["debit"];	
			$credit		= $xItem["credit"];
			$reference  = $xItem["reference"];
			$narrative 	= $xItem["narrative"];
			$reference_date  = $xItem["reference_date"];
				
			$ctr 	    = $id;
			$item		= $xItem;
		}
	}
}
elseif($p1=="verifyGChart")
{
	$q = "select CONCAT(acode,'-',scode) as gcode, gchart_id, gchart from gchart where gchart_id='$gchart_id'";
	$qr = pg_query($q);
	if ($qr == 0)
	{
	}
	else
	{
		$r = pg_fetch_object($qr);
		$searchkey = $r->gcode;
		$gchart_id=$r->gchart_id;
		$gchart=$r->gchart;
	}
}
elseif ($p1 == 'Approve' && $gltran['gltran_header_id'] == '')
{
	message("Save Transaction first please...");
}
elseif ($p1 == 'Approve' && !in_array($gltran['status'],array('S')))
{
	message('Please Save Transaction Before Approval...');
}
elseif ($p1 == 'Approve')
{
	$q = "update gltran_header set status='A' where gltran_header_id='".$gltran['gltran_header_id']."'";
	$qr = @pg_query($q) or message(pg_errormessage());
	if ($qr)
	{
		message('Transaction A and Updated...');
		$gltran['status'] = 'A';
	}
}
elseif ($p1 == 'Revoke Approval')
{
	$q = "update gltran_header set status='S' where gltran_header_id='".$gltran['gltran_header_id']."'";
	$qr = @pg_query($q) or message(pg_errormessage());
	if ($qr)
	{
		message('Transaction Approval Revoked and Updated...');
		$gltran['status'] = 'S';
	}
}
elseif ($p1 == 'Print' && $gltran['gltran_header_id'] == '')
{
	message("Save Transaction first please...");
}
elseif ($p1 == 'Print' && !in_array($gltran['status'],array('A','P')))
{
	message('Please Save & Approve Transaction Before Printing...');
}
elseif ($p1 == 'PrintX')
{
	$details = "\n\n\n\n";
	$details .= space(75).'#'.$gltran['gltran_header_id']."\n\n";
	$details .= space(3).adjustSize(lookUpTableReturnValue('x','account','account_id','account',$gltran['account_id']),65).' '.
						ymd2mdy($gltran['date'])."\n";
	$details .= space(3).adjustSize(lookUpTableReturnValue('x','account','account_id','address',$gltran['account_id']),65).' '.
						lookUpTableReturnValue('x','account','account_id','tin',$gltran['account_id'])."\n";
	$details .= "\n\n\n\n";
	$ctr=$total_debit = $total_credit = 0;
	foreach ($aItems as $xItem)
	{
		$ctr++;

		$details .= adjustSize(substr($gltran['narrative'],($ctr-1)*35,35),35).' '.
					adjustSize(substr($xItem['gchart'],0,20),20).' '.
					adjustRight(number_format2($xItem['debit'],2),12).'  '.
					adjustRight(number_format2($xItem['credit'],2),12)."\n";
		$total_debit += $xItem['debit'];				
		$total_credit += $xItem['credit'];
	}	
	while (strlen($gltran['particulars']) > $ctr*35)
	{
		$ctr++;
		$details .= adjustSize(substr($gltran['particulars'],($ctr-1)*35,35),35)."\n";
	}
	while ($ctr < 14)
	{
		$ctr++;
		$details .= "\n";
	}
	if ($ctr <= 14)
	{
		$details .= space(25).adjustSize($gltran['mcheck'],15).
					space(10).adjustSize(ymd2mdy($gltran['date_check']),15)."\n\n\n";
					
		$cnum = numWord($total_debit);
		$ln1 = adjustSize(substr($cnum,0,45),45);
		$ln2 = adjustSize(substr($cnum,46,25),25);
		$details .= space(42).$ln1."\n";			
		$details .= space(42).$ln2.'    '.number_format($total_debit,2)."\n\n\n";			
	}
	
	$details .= "</font>";
//echo "<pre>$details</pre>";
	echo "<input type='hidden' id='print_area' name='print_area' value=".'"'.$details.'"'.">";
	echo "<iframe name='printit' id='printit' style='width:0px;height:0px;'>";
	echo "</iframe>";
	echo "<script>printIframe(print_area)</script>";

	$q = "update gltran_header set status='P' where gltran_header_id='".$gltran['gltran_header_id']."'";
	$qr = @pg_query($q) or message(pg_errormessage());
	if ($qr)
	{
		$gltran['status'] = 'P';
	}

	$printCheck=1;	
}
elseif ($p1 == 'Print')
{
	$adate = explode('-',$gltran['date']);
	
	$q = "select * from account where account_id = '".$gltran['account_id']."'";
	$qr = @pg_query($q)  or message(pg_errormessage());
	$r = @pg_fetch_object($qr);
	$gltran['terms'] = $r->terms;
	$gltran['account'] = $r->account;
	
	
	$cdate = cmonth($adate[1]).' '. $adate[2].', '.$adate[0];
	$header = '<reset>';
	$header .= space(2).adjustSize(strtoupper($SYSCONF['BUSINESS_NAME']),45).
					space(3).lookUpTableReturnValue('x','journal','journal_id','journal',$gltran['journal_id'])."\n";
	$header .= space(2).adjustSize(strtoupper($SYSCONF['BUSINESS_ADDR']),45).
					space(3)."REFERENCE: ".str_pad($gltran['gltran_header_id'],8,'0',STR_PAD_LEFT)."\n";
	$header .= space(50)."DATE     : ".$cdate."\n\n";

	$header .= '  '.adjustSize('Payee :***'.$gltran['account'].'***',55).
					space(3).'P'.adjustRight(number_format($gltran['check_amount'],2),12)."\n";
	$header .= '  '.'Cheque: '.$gltran['mcheck'].'   '.ymd2mdy($gltran['date_check'])."\n\n";

//	$header .= '  '.str_repeat('-',76)."\n";	
//	$header .= '        Account Particulars                               Debit     Credit   '."\n";
//	$header .= '  '.str_repeat('-',76)."\n";
//	$header .= space(55).'   Debit     Credit   '."\n";
//	$header .= space(55).'----------- -----------'."\n";
//	$header .= space(60).'   Amount   '."\n";
//	$header .= space(60).'----------- '."\n";

	$details = '<reset>';
	$ctr=$total_debit = $total_credit = 0;
	
	
	foreach ($aItems as $xItem)
	{
		if (substr($xItem['gcode'],0,3)=='106') continue;
		$ctr++;
//		$details .= '  '.adjustSize($xItem['acode'].'-'.$xItem['scode'],12).' ';

		if ($xItem['reference_source'] == 'RR' && $xItem['credit'] == '0')
		{
				$reference = 'INV# '.$xItem['reference'];
				$q = "select * from rr_header where invoice='".$xItem['reference']."' and status!='C'";
				$qr = @pg_query($q) or message(pg_errormessage());
				$r = @pg_fetch_object($qr);
				
				$date_invoice = $r->date;
				if ($date_invoice != '')
				{
					$adate = explode('-',$r->date);
					$date_due =  date('Y-m-d',mktime (0,0,0,$adate[1]  ,$adate[2]+$gltran['terms'],$adate[0])); 

				}
				$details .= space(5).adjustSize($reference,13).' '.
								adjustSize(ymd2mdy($date_invoice),10).' '.
								adjustSize(ymd2mdy($date_due),10).' '.
								space(18);
				$details .= ' '.adjustRight(number_format2($xItem['debit'],2),11)."\n"; 
				$total_debit += $xItem['debit'];				
		}
	}	
	$details .= space(60).'------------'."\n";
	$details .= space(60).adjustRight(number_format($total_debit-$total_credit,2),11)." -501\n\n";
	$ctr=0;
	
	foreach ($aItems as $xItem)
	{
		if (substr($xItem['gcode'],0,3)=='106') continue;
		$ctr++;
		if ($xItem['reference_source'] == 'PR' )
		{
				
				if ($xItem['credit'] > 0)
				{
					$reference = 'DM# '.$xItem['reference'];
				} else
				{
					$reference = 'CM# '.$xItem['reference'];
				}
				
				$q = "select * from por_header where reference='".$xItem['reference']."' ";
				$qr = @pg_query($q) or message(pg_errormessage());
				$pr = @pg_fetch_object($qr);

				$date_invoice = $pr->date;
				$details .= space(10).adjustSize($reference,14).' '.
								adjustSize(ymd2mdy($date_invoice),10).' '.
								space(8);
				$details .= '('.adjustRight(number_format2($xItem['credit'],2),10).")\n";
				$total_credit += $xItem['credit'];
		}
	}	
	$details .= space(60).'('.adjustRight(number_format($total_credit,2),10).")-509\n";

	$details .= space(60).'------------'."\n";
	$details .= space(60).adjustRight(number_format($total_debit-$total_credit,2),11)." -201\n";
	$details .= space(60).'============'."\n";

/*	$details .= space(55).'----------- -----------'."\n";
	$details .= space(55).
				adjustRight(number_format($total_debit,2),11).' '.
				adjustRight(number_format($total_credit,2),11)."\n";
	$details .= space(55).'=========== ==========='."\n";*/
	
	if ($gltran['particulars'] != '')
	{
		$details .= '  '.adjustSize('MEMO  :'.substr($gltran['particulars'],0,75),75)."\n";
	}
/*
	while ($ctr<5)
	{
		$details .= "\n";
		$ctr++;
	}
	$details .= '  '.str_repeat('-',76)."\n";
	$details .= '   Amount in  Words: '.numWord($total_credit)." PESOS \n";
*/
	$details .= "\n\n";

	$admin_id = $ADMIN['admin_id'];
	if ($admin_id > '0')
	{
		$details .= "  ".adjustSize(lookUpTableReturnValue('x','admin','admin_id','name',$admin_id),25);
	}
	else
	{
		$details .= "  _________________________";
	}
	$details .= space(3)."______________________".space(3)."______________________\n";
	$details .= "  Prepared by:               ".space(3)."  Approved By:        ".space(3)."   Received by: \n";
	$details .= "<eject>";
	
//	echo "<pre>$header$details</pre>";
	nPrinter($header.$details, $SYSCONF['REPORT_PRINTER_TYPE'], $SYSCONF['REPORT_PRINTER_DEST']);
	
	$q = "update gltran_header set status='P' where gltran_header_id='".$gltran['gltran_header_id']."'";
	$qr = @pg_query($q) or message(pg_errormessage());
	if ($qr)
	{
		$gltran['status'] = 'P';
	}
	
	$printCheck=1;
}
elseif ($p1 == 'PrintCheck' && $gltran['gltran_header_id'] != '')
{
	$_amount = $gltran['check_amount'];
	$convert = new num2words();
	$convert->setNumber($_amount);

	$style_check = ($gltran['check_amount'] >= 100000) ? "style='font-size:11px;'" : "style='font-size:12px;'"; 
	
	$details ="<RESET>";
	$details = "\n";
	$details .= 	space(100).date("F, j Y",strtotime($gltran['date_check']))."\n\n".
				//space(25).adjustSize("**".lookUpTableReturnValue('x','account','account_id','account',$gltran['account_id']))."**",43).
				space(40)."<bold>***".number_format($gltran['check_amount'],2)."***</bold>\n\n".
				space(35).'<span '.$style_check.'>***'.numWord($gltran['check_amount']).'***</span>'."\n\n\n\n\n\n"."<eject>";
//				space(5).'CV'.str_pad($gltran['gltran_header_id'],6,'0',STR_PAD_LEFT).' #'.$gltran['mcheck']."<eject>";
//	echo "<pre>$details</pre>";
//	nPrinter($details, $SYSCONF['REPORT_PRINTER_TYPE'], $SYSCONF['REPORT_PRINTER_DEST']);

	$details ="<font face='mono' size='3' style='letter-spacing:6px; line-height:10px;'>";
	$details .= "\n";
	$details .= "<div style='margin-top:16px;'></div>";
	$details .= 	space(95)."<b>".date("F, j Y",strtotime($gltran['date_check']))."</b>";
	
	$details .= "<div style='margin-bottom:15px;'></div>";
	$details .=
				space(16).
				adjustSize("**".lookUpTableReturnValue('x','account','account_id','account',$gltran['account_id'])."**",40).
				space(30)."<bold>***".number_format($gltran['check_amount'],2)."***</bold>\n\n\n".
				space(12)."<b $style_check >***".strtoupper($convert->getCurrency()).'***</b>'."\n\n\n\n\n\n"."<eject>";

	echo "<input type='hidden' id='print_area' name='print_area' value=".'"'.$details.'"'.">";
	echo "<iframe name='printit' id='printit' style='width:100%;height:200px;'>";
	echo "</iframe>";
	echo "<script>printIframe(print_area)</script>";

}
elseif ($p1 == 'Next>>')
{
	$aGLTBrowse['searchby'] = $_REQUEST['searchby'];
	$aGLTBrowse['show_journal_id'] = $_REQUEST['show_journal_id'];
	$searchby = $aGLTBrowse['searchby'];
	$show_journal_id = $aGLTBrowse['show_journal_id'];
	$q = "select * from gltran_header, account
			where 
				account.account_id=gltran_header.account_id and 
				$searchby > '".$gltran[$searchby]."'";
	if ($show_journal_id != '')
	{
		$q .= " and journal_id = '$show_journal_id' ";
	}
	$q .= " order by 
				$searchby  offset 0 limit 1";
	
	$qr = @pg_query($q);
	if (@pg_num_rows($qr) == 0)
	{
		message("End of file...");
	}	
	else
	{
		$gltran = null;
		$gltran = array();
		$aItems = null;
		$aItems = array();
		$r = fetch_assoc($q);
		$gltran = $r;
		
		$gltran['account'] = lookUpTableReturnValue('x','account','account_id','account',$r['account_id']);
		$q = "select * from gltran_detail, gchart where gchart.gchart_id=gltran_detail.gchart_id and gltran_header_id='".$gltran['gltran_header_id']."'";
		$qr = @pg_query($q) or message(pg_errormessage());
		while ($r = @pg_fetch_assoc($qr))
		{
			$temp = $r;
			$temp['gcode'] = $r['acode'].'-'.$r['scode'];
			$aItems[] = $temp;

		}
	}	

}
elseif ($p1 == '<<Previous')
{
	$aGLTBrowse['searchby'] = $_REQUEST['searchby'];
	$aGLTBrowse['show_journal_id'] = $_REQUEST['show_journal_id'];
	$searchby = $aGLTBrowse['searchby'];
	$show_journal_id = $aGLTBrowse['show_journal_id'];
	
	$q = "select * from gltran_header, account 
			where 
				account.account_id=gltran_header.account_id and 
				$searchby < '".$gltran[$searchby]."'";
				
	if ($show_journal_id != '')
	{
		$q .= " and journal_id = '$show_journal_id' ";
	}
	$q .= " order by 
				$searchby desc offset 0 limit 1";
				
	$qr = @pg_query($q);
	if (@pg_num_rows($qr) == 0)
	{
		message("Beginning of file...");
	}	
	else
	{
		$gltran = null;
		$gltran = array();
		$aItems = null;
		$aItems = array();

		$r = fetch_assoc($q);
		$gltran = $r;
		
		$gltran['account'] = lookUpTableReturnValue('x','account','account_id','account',$r['account_id']);
		$q = "select * from gltran_detail, gchart where gchart.gchart_id=gltran_detail.gchart_id and gltran_header_id='".$gltran['gltran_header_id']."'";
		$qr = @pg_query($q) or message(pg_errormessage());
		while ($r = @pg_fetch_assoc($qr))
		{
			$temp = $r;
			$temp['gcode'] = $r['acode'].'-'.$r['scode'];
			$aItems[] = $temp;

		}
	}	
	$d = "Previous Record Search Result by: ".$searchby;
	if ($show_journal_id != '')
	{
		$j = lookUpTableReturnValue('x','journal','journal_id','journal',$aGLTBrowse['show_journal_id']);
		$d .= " on $j";
	}
	//message($d);
}
	
elseif ($p1 == 'Find')
{
		$aG = explode('-',$searchkey);
		$acode = trim(chop($aG[0]));
		$scode = trim(chop($aG[1]));
		
		$q = "select * from gchart where acode='$acode' and scode = '$scode' and level='9'";
		$qr = @pg_query($q) or message(pg_errormessage());

		if (pg_num_rows($qr)>0)
		{
			$r = @pg_fetch_object($qr);
			$gchart_id = $r->gchart_id;
			$gchart = $r->gchart;
			$searchkey = $r->acode.'-'.$r->scode;
			$focus_flag='debit';
		}
		else
		{
			$p1 = 'selectGchart';
		}

}
if ($p1 == 'selectGchart')
{
	include_once('gltran.searchgchart.php');
}
if ($focus_flag=='' )
{
	$focus_flag = 'searchkey';
}
		
if ($focus_flag!='')
{
	echo "<script>document.getElementById('$focus_flag').focus()</script>";
}
?> 
<form name="f1" id='f1' method="post" action="?p=gltran" style="margin:0">
<table width="97%" border="0" cellspacing="0" cellpadding="0" align="center">
  <tr>
      <td><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><b>Find 
        Voucher </b></font> 
        <input type="text" name="xSearch" value="<?= $xSearch;?>" class="altTextFormat">
        <?= lookUpAssoc('searchby',array('Reference'=>'xrefer','Record Id'=>'gltran_header_id','Payee'=>'account','Date(yyyy-mm-dd)'=>'date','Check No.'=>'mcheck'),$aGLTBrowse['searchby']);?>
        <select name="show_journal_id" id="show_journal_id">
          <option value=''>Show Journal</option>
          <?
	  	$q = "select * from journal where enable='Y' order by journal";
		$qr = @pg_query($q);
		while ($r = @pg_fetch_object($qr))
		{
			if ($aGLTBrowse['show_journal_id'] == $r->journal_id)
			{
				echo "<option value = $r->journal_id selected>$r->journal</option>";
			}
			else
			{	
				echo "<option value = $r->journal_id>$r->journal</option>";
			}	
		}
	  ?>
        </select>
        <input type="button" name="p1" value="Go" class="altButtonFormat" onClick="window.location='?p=gltran.browse&p1=Go&xSearch='+xSearch.value+'&searchby='+searchby.value">
        <input type="button" name="p1" value="Browse" class="altButtonFormat"  onClick="window.location='?p=gltran.browse&p1=Browse'">
        <input type="submit" name="p1" value="New" class="altButtonFormat" >
        <input name="p1" type="submit" id="p1" value="&lt;&lt;Previous " class="altButtonFormat">
        <input name="p1" type="submit" id="p1" value="Next&gt;&gt;" class="altButtonFormat" > 
        <hr height="1" size="1">
	</td>
  </tr>
</table>
  <table width="97%" border="0" cellspacing="1" cellpadding="0" bgcolor="#EFEFEF" align="center">
    <tr bgcolor="#999999"> 
      <td height="13" colspan="3"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        &nbsp;<img src="../graphics/greenlist.gif" width="16" height="16"><b> Journal 
        Entry</b></font><font face="Verdana, Arial, Helvetica, sans-serif size=" 2="2" size="2">&nbsp; 
        </font></td>
      <td height="13" align="center"><font size="1">Record Id:  
        <?= ($gltran['gltran_header_id']==''? 'New Entry' : str_pad($gltran['gltran_header_id'],10,'0',STR_PAD_LEFT));?>
        </font></td>
    </tr>
    <tr bgcolor="#E9E9E9"> 
      <td width="6%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Journal</font></td>
      <td width="39%" nowrap>
	  <select name="journal_id" id="journal_id" onChange="vJournal()"  class="altSelectFormat">
	  <option value=''>Select Journal</option>
	  <?
	  	$q = "select * from journal where enable='Y' order by journal";
		$qr = @pg_query($q);
		while ($r = @pg_fetch_object($qr))
		{
			if ($gltran['journal_id'] == $r->journal_id)
			{
				echo "<option value = $r->journal_id selected>$r->journal</option>";
			}
			else
			{	
				echo "<option value = $r->journal_id>$r->journal</option>";
			}	
		}
	  ?>
	  </select>	  </td>
      <td width="9%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Reference</font></td>
      <td width="46%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <input name="xrefer" type="text" id="xrefer" value="<?= $gltran['xrefer'];?>" size="10"  class="altTextFormat">
		<b><?= $gltran['status'];?></b>
        </font></td>
    </tr>
    <tr bgcolor="#E9E9E9"> 
      <td width="6%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Account</font></td>
      <td width="39%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="account" type="text" id="account" value="<?= stripslashes($gltran['account']);?>" size="50" onBlur="btnaccountSearch.click()"  class="altTextFormat">
        <input type="button" name="p1" value="+" alt="Quick Add New Account/Payee" onClick="document.getElementById('LayerPayeeAdd').style.visibility='visible'"  class="altButtonFormat">
        <input name="account_id" type="hidden" id="account_id" value="<?= $gltran['account_id'];?>" size="5">
        <img src="../graphics/dwn-arrow-grn.gif" width="12" height="12" onClick="f1.action='?p=gltran&p1=searchaccount';f1.submit()" name="btnaccountSearch"> 
        </font></td>
      <td width="9%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Date</font><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
        </font></td>
      <td width="46%"> 
        <input type="text" name="date" size="10" value="<?= ymd2mdy($gltran['date']);?>" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')"  class="altTextFormat">
        <img src="../graphics/dwn-arrow-grn.gif" width="12" height="12" onclick="popUpCalendar(this, f1.date, 'mm/dd/yyyy')"> 
		<input type="submit" name="p1" value="AutoPost" id="SalesAutoPost"  class="altButtonFormat">      </td>
    </tr>
    <tr bgcolor="#E9E9E9"> 
      <td width="6%" rowspan="2" valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Memo 
        <img src="../graphics/dwn-arrow-grn.gif" width="12" height="12" onclick="document.getElementById('LayergltranRemark').style.visibility='visible'"> 
        </font></td>
      <td rowspan="2"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <textarea name="particulars" cols="45" class="altTextFormat" rows="2"><?= stripslashes($gltran['particulars']);?>
        </textarea>
      </font></td>
      <td valign="top"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Check#</font></td>
      <td valign="top"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input type="text" name="mcheck" size="26" value="<?= $gltran['mcheck'];?>"  class="altTextFormat">
        </font></td>
    </tr>
    <tr bgcolor="#E9E9E9"> 
      <td valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Check 
        Date </font></td>
      <td valign="top"> 
        <input type="text" name="date_check" size="10" value="<?= ymd2mdy($gltran['date_check']);?>" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')"  class="altTextFormat">
        <img src="../graphics/dwn-arrow-grn.gif" width="12" height="12" onclick="popUpCalendar(this, f1.date_check, 'mm/dd/yyyy')"> 
        <select name="pcenter_id"  id="pcenter_id" style="border: #CCCCCC 1px solid; width:200px">
          <?

	  	$q = "select * from pcenter where enable='Y' order by pcenter";
		$qr = @pg_query($q);
		while ($r = @pg_fetch_object($qr))
		{
			if ($r->pcenter_id == $gltran['pcenter_id'])
			{
				echo "<option value=$r->pcenter_id selected>".substr($r->pcenter_code,0,6)." $r->pcenter</option>";
			}
			else
			{
				echo "<option value=$r->pcenter_id>".substr($r->pcenter_code,0,6)." $r->pcenter</option>";
			}
		}
	  ?>
        </select> </td>
    </tr>
    <tr bgcolor="#E9E9E9">
    	<td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">PO # for Adv. Payments</font></td>
        <td><input type="text" name="po_header_id" size="26" value="<?= $gltran['po_header_id'];?>"  class="altTextFormat"></td>
    </tr>
    <tr> 
      <td height="4" colspan="4"> 
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr bgcolor="#000000"> 
            <td width="27%" colspan="3"><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#FFFFFF"><b>Transaction 
              Entry Details</b></font></td>
            <td width="6%">&nbsp;</td>
            <td width="6%">&nbsp;</td>
            <td colspan="2">&nbsp;</td>
          </tr>
          <tr bgcolor="#FFFFFF"> 
            <td><font size="1" face="Verdana, Arial, Helvetica, sans-serif">Account 
              <input type="hidden" name="gchart_id"  id="gchart_id" size="10" value="<?= $gchart_id;?>">
              </font></td>
            <td><font size="1" face="Verdana, Arial, Helvetica, sans-serif">Account 
              Title </font></td>
            <td><font size="1" face="Verdana, Arial, Helvetica, sans-serif">Narrative</font></td>
            <td><font size="1" face="Verdana, Arial, Helvetica, sans-serif">Debit</font></td>
            <td><font size="1" face="Verdana, Arial, Helvetica, sans-serif">Credit</font></td>
            <td width="6%"><font size="1" face="Verdana, Arial, Helvetica, sans-serif">Reference</font></td>
            <td>&nbsp;</td>
          </tr>
          <tr bgcolor="#FFFFFF"> 
            <td nowrap> <input type="text" name="searchkey" id="searchkey" size="12" value="<?= $searchkey;?>"  class="altTextFormat"> 
              <input type="submit" name="p1" value="Find"  class="altButtonFormat">            </td>
            <td nowrap><input name="gchart" type="text"  class="altTextFormat" id="gchart" value="<?= $gchart;?>" size="35"></td>
            <td nowrap><input type="text" name="narrative" size="30" value="<?= $narrative;?>"  class="altTextFormat"></td>
            <td nowrap> <input type="text" name="debit" size="10" value="<?= $debit;?>" class="altNumFormat">            </td>
            <td nowrap> <input type="text" name="credit" size="10" value="<?= $credit;?>"  class="altNumFormat">            </td>
            <td> <input type="text" name="reference" size="8" value="<?= $reference;?>"  class="altTextFormat"> 
            <td> <input type="hidden" name="ctr" size="8" value="<?= $ctr;?>"  class="altTextFormat">            </td>
            <td> <font size="1" face="Verdana, Arial, Helvetica, sans-serif"> 
              <input type="submit" name="p1" value="<?= ($p1=='Edit' ? 'Update' :'Insert');?>"  class="altButtonFormat">
              </font></td>
          </tr>
          <?
				if ($total_debit == $total_credit) {
					$focus_flag='Save';
				?>
          <?
				}
				?>
        </table>      </td>
    </tr>
    <tr height="47%"> 
      <td colspan ="4"> 
        <div id="Layer1" style="position:virtual;  width:100%; height:100%; overflow: auto"> 
          <table width="100%"  border="0" cellpadding="1" cellspacing="1" bgcolor="#FFFFFF">
            <tr bgcolor="#CCCCCC"> 
              <td width="5%"><strong><font face="Verdana, Arial, Helvetica, sans-serif" size="2">#</font></strong></td>
              <td width="10%"><strong><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Account</font></strong></td>
              <td width="51%"><strong><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Description</font></strong></td>
              <td width="12%"> <div align="center"><strong><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Debit</font></strong></div></td>
              <td width="12%"> <div align="center"><strong><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Credit</font></strong></div></td>
              <td width="10%" align="center"><strong><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Reference</font></strong><strong></strong></td>
            </tr>
            <?
	$ctr =0;
	$total_debit = 0;
	$total_credit = 0;
	foreach ($aItems as $xItem) {
		$ctr++;
		$total_debit 	+= $xItem["debit"];
		$total_credit	+= $xItem["credit"];
		$diff = "Diff:P ".number_format(($total_debit - $total_credit),2);
	?>
            <tr bgcolor=<?= $ctr%2==1 ? "#FFFFFF" : "#EFEFEF";?>> 
              <td width="5%" nowrap><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
                <?= $ctr;?>
                . 
                <input type="checkbox" name="delete[]" value="<?=$ctr;?>">
                </font></td>
              <td width="9%"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
                <a href = "javascript: document.forms.f1.action='?p=gltran&p1=Edit&id=<?=$ctr;?>';document.forms.f1.submit()"> 
                <?= $xItem["gcode"];?>
                </a> </font></td>
              <td width="48%"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
                <a href = "javascript: document.forms.f1.action='?p=gltran&p1=Edit&id=<?=$ctr;?>';document.forms.f1.submit()"> 
                <?= $xItem["gchart"].($xItem['narrative']!='' ? ' ('.$xItem['narrative'].')' : '');?>
                </a> </font></td>
              <td align="right" width="9%"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
                <?= number_format($xItem["debit"],2);?>
                </font></td>
              <td width="10%" align="right"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
                <?= number_format($xItem["credit"],2);?>
                </font></td>
              <td align="center"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><a href = "javascript: document.forms.f1.action='?p=gltran&p1=Edit&id=<?= $ctr;?>';document.forms.f1.submit()"> 
                <?= $xItem["reference"];?>
                </a></font><font face="Verdana, Arial, Helvetica, sans-serif" size="2">&nbsp; 
                </font> </td>
            </tr>
            <?
	  }
	  $gltran['total_debit'] = $total_debit;
	  $gltran['total_credit'] = $total_credit;
	  ?>
          </table>
        </div>      </td>
    </tr>
    <tr height="5"> 
      <td colspan="4" bgcolor="#FFFFFF" height="25"> 
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr bgcolor="#E9E9E9"> 
            <td width="33%" bgcolor="#CCCCCC"> <font face="Verdana, Arial, Helvetica, sans-serif" size="2" color=<?= ($total_debit==$total_credit ? "#000000" : "Red") ;?>><b> 
              <input type=submit name=p1 value='Delete Checked'>
              <?= ($total_debit==$total_credit ? "Balanced" : $diff);?>
              </b></font></td>
            <td width="27%" bgcolor="#CCCCCC"> 
              <div align="center"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Total</font></b></div>            </td>
            <td width="6%" bgcolor="#CCCCCC"> 
              <div align="right"><b>P</b></div>            </td>
            <td width="13%" align="right" bgcolor="#CCCCCC"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
              <?= number_format($gltran['total_debit'],2);?>
              </font></b></td>
            <td width="11%"  align="right" bgcolor="#CCCCCC"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
              <?= number_format($gltran['total_credit'],2);?>
              </font></b></td>
            <td width="10%"  align="right" bgcolor="#CCCCCC">&nbsp;</td>
          </tr>
        </table>      </td>
    </tr>
    <tr> 
      <td colspan="7" bgcolor="#000000" height="2"> 
        <table width="100%" border="0" cellspacing="0" cellpadding="1" bgcolor="#000000">
          <tr> 
            <td bgcolor="#FFFFFF"> 
              <table width="100%" border="0" cellpadding="1" cellspacing="1" bgcolor="#CCCCCC">
                <tr bgcolor="#FFFFFF"> 
                  <td width="6%"><img src="../graphics/save.jpg" width="57" height="15" onClick="f1.action='?p=gltran&p1=Save';f1.submit()" alt="Save Entry"></td>
                  <td width="7%"><img src="../graphics/print.jpg" width="65" height="18" onClick="f1.action='?p=gltran&p1=PrintVoucher';f1.submit()" alt="Print Voucher"></td>
                  <td width="7%"><img src="../graphics/new.jpg" width="63" height="20" onClick="f1.action='?p=gltran&p1=New';f1.submit()" alt="Start New Transaction"></td>
                  <td width="8%"><img src="../graphics/cancel.jpg" width="77" height="20" onClick="if(confirm('Are you sure to cancel voucher?')){f1.action='?p=gltran&p1=Cancel';f1.submit()}" alt="Cancel Voucher"></td>
                  <td width="7%"><img src="../graphics/check.gif" width="35" height="18" onClick="f1.action='?p=gltran&p1=PrintCheck';f1.submit()" alt="Check Printing"><span class="style1">Reprint</span></td>
				  <td width="">&nbsp;</td>
				  <td width="28%" align="center">
				  <?
				$disable = '';
				if (!chkRights2('gltranapprove','madd',$ADMIN['admin_id']) || $gltran['gltran_header_id']=='')
				{
				  	$disable = 'disabled';
				}
				if (!in_array($gltran['status'],array('A','P')))
				{
					  echo "<input name='p1' type='submit' value='Approve' $disable>";
				}
				else
				{
					  echo "<input name='p1' type='submit' value='Revoke Approval'  $disable>";
				}	  
				
				  ?>				  </td>
                </tr>
              </table>            </td>
          </tr>
        </table>      </td>
    </tr>
  </table>
  <?
  include_once('gltran.accountquickadd.php');
  include_once('gltran.gltranremark.php');
	?>
</form>
<?
if ($p1 == 'searchaccount')
{
	include_once('gltran.searchaccount.php');
}
if ($focus_flag=='' || $focus_flag=='searchkey') echo "<script>f1.searchkey.focus() </script>";
if ($focus_flag=='debit') echo "<script>f1.debit.focus() </script>";
if ($focus_flag=='credit') echo "<script>f1.credit.focus() </script>";
if ($printCheck==1)
{
	echo "<script>if (confirm('Do you wish to Print Cheque?')){document.getElementById('f1').action='?p=gltran&p1=PrintCheck';document.getElementById('f1').submit();}</script>";
}
?>
<? if($p1 == "PrintVoucher"){ ?>
<div align="center">
	<iframe id="JOframe" name="JOframe" style="background-color:#FFF; margin:auto;" frameborder="0" width="90%" height="500" src="print_voucher.php?id=<?=$gltran['gltran_header_id']?>"></iframe><br />
    <input type="button" value="Print" onClick="printFrame('JOframe');" />
</div>
<? } ?>

