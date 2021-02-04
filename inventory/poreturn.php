<?
function insertGL($gltran_header_id,$gchart_id,$debit,$credit,$reference,$reference_source){
	$result 	= @pg_query(" select * from gchart where gchart_id = '$gchart_id' ");
	$r 			= @pg_fetch_assoc($result);
	$gchart 	= $r['gchart'];
	
	@pg_query("insert into gltran_detail (gltran_header_id,gchart_id,gchart,debit,credit,reference,reference_source,enable) values 
	('$gltran_header_id','$gchart_id','$gchart','$debit','$credit','$reference','$reference_source','Y')");
}

?>


<script type="text/javascript">
function printIframe(id)
{
    var iframe = document.frames ? document.frames[id] : document.getElementById(id);
    var ifWin = iframe.contentWindow || iframe;
    iframe.focus();
    ifWin.printPage();
    return false;
}
</script>
<script>
<!--
function vFocus(e)
{
//	if(document.all)e = event;

	var obj = document.getElementById('wordlist');
	var st = Math.max(document.body.scrollTop,document.documentElement.scrollTop);
//alert(obj.offsetTop);
//alert(e.clientX);
	if(navigator.userAgent.toLowerCase().indexOf('safari')>=0)st=0; 
	var leftPos = obj.offsetLeft;
	if(leftPos<0)leftPos = 0;
	
	obj.style.left = leftPos + 'px';
	obj.style.top = obj.offsetTop+ 'px';
	
//	alert(e.clientX);
	return false;	
}

function vAmount()
{
	amount = 1*(document.getElementById('amount').value);
	freight_case = 1*(document.getElementById('freight_case').value);
	fraction3 = 1*(document.getElementById('fraction3').value);
	unit_qty = 1*(document.getElementById('unit_qty').value);
	case_qty = 1*(document.getElementById('case_qty').value);

	total_qty = case_qty*fraction3 + unit_qty;

	net_amount = amount; // - freight;
	cost1 =net_amount/total_qty;
	cost3 = twoDecimals(cost1*fraction3);
	cost1 = twoDecimals(cost1);

	document.getElementById('cost3').value=cost3;
	document.getElementById('cost1').value=cost1;
	return;
}
function vFrac()
{
	fraction3 = parseFloat(document.getElementById('fraction3').value);
	cost1 = parseFloat(document.getElementById('cost1').value);
	cost3 = twoDecimals(cost1*fraction3);

	document.getElementById('cost3').value=cost3;
	return;
}
function vFrac3()
{
	fraction3 = parseFloat(document.getElementById('fraction3').value);
	cost3 = parseFloat(document.getElementById('cost3').value);
	cost1 = twoDecimals(cost3/fraction3);
	document.getElementById('cost1').value=cost1;
	return;
}

function vCompute(obj)
{
	rr_header_id = document.getElementById('rr_header_id').value;
	type = document.getElementById('type').value;
	
	/*if (type=='1'  && (rr_header_id == '' || rr_header_id=='0'))
	{
		alert('No SRR # specified...');
		return;
	}*/
	balance_order  = document.getElementById('balance_order').value;
	case_qty = document.getElementById('case_qty').value;
	unit_qty  = document.getElementById('unit_qty').value;

	fraction3 = parseFloat(document.getElementById('fraction3').value);
	cost3 = parseFloat(document.getElementById('cost3').value);
	
	case_balance = parseInt(balance_order/fraction3);
	unit_balance = balance_order - case_balance*fraction3;
	qty = case_qty * fraction3 + unit_qty;


	if (parseFloat(qty) > parseFloat(balance_order))
	{
		alert('Delivered Quatity ('+case_qty+'/'+unit_qty+' )is More than Remaining Order Quantity ('+case_balance+'/'+unit_balance+' )...');
		document.getElementById(obj.id).value = '';
		document.getElementById(obj.id).focus();
		return ;
	}
	
	if (fraction3 == '')
	{
		fraction3 = 1
	}
	cost1 = cost3/fraction3;
	
	document.getElementById('amount').value=twoDecimals(parseFloat(document.getElementById('unit_qty').value*cost1)+ 
			parseFloat(document.getElementById('case_qty').value*cost3));
			
	return false;
}

-->
</script>
<STYLE TYPE="text/css">
<!--
	.grid {
	font-family: verdana;
	border: #CCCCCC 1px solid;
	font-size: 12px;
	color: #000000
	} 


	.altText {
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
	
	.altNum {
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

	.altBtn {
	background-color: #CFCFCF;
	font-family: verdana;
	font-size: 11px;
	padding: 1px;
	margin: 0px;
	color: #1F016D
	} 
-->
</STYLE>
<?

/*
if ($ADMIN['admin_id']  == 1)
{

$q = "select * from stock where cost3=0 and cost1=0";
$qr = @pg_query($q) or message1(pg_errormessage().$q);
while ($r = @pg_fetch_object($qr))
{
	$qq = "select * from por_detail where stock_id = '$r->stock_id' order by por_header_id offset 0 limit 1";
	$qqr = @pg_query($qq) or message1( pg_errormessage().$qq);
	if (@pg_num_rows($qqr) >0)
	{
		$rr = @pg_fetch_object($qqr);
			$qu = "update stock set cost3 = '$rr->cost3' where stock_id = '$r->stock_id'";
			$qur = @pg_query($qu);
			

	} 
 }	
}
*/
if (!chkRights2('poreturn','madd',$ADMIN['admin_id']))
{
	message('You have [ NO ] permission to access this area...');
	exit;
}
//include_once("lib/lib.css.php");
if (!session_is_registered('aPOR'))
{
	session_register('aPOR');
	$aPOR=null;
	$aPOR=array();
}

if (!session_is_registered("aPORD"))
{
	session_register("aPORD");
	$aPORD=null;
	$aPORD=array();
}
if (!session_is_registered("iPORD"))
{
	session_register("iPORD");
	$iPORD=null;
	$iPORD=array();
}
if (!session_is_registered("aPOR_RRD"))
{
	session_register("aPOR_RRD");
	$aPOR_RRD=null;
	$aPOR_RRD=array();
}


function postApLedgerXXXX()
{
	global $aPOR, $aPORD;
	
	$q = "select * from apledger where type='CM' and record_id = '".$aPOR['por_header_id']."' ";
	$qr = @pg_query($q) or message1(pg_errormessage().$q);

	if (@pg_num_rows($qr) == 0)
	{
		$q = "insert into apledger (account_id, date, record_id, reference, debit,credit,type)
					values ('".$aPOR['account_id']."', '".$aPOR['date']."', '".$aPOR['por_header_id']."','".$aPOR['reference']."','".$aPOR['net_amount']."','0','CM')";
		$qr = @pg_query($q) or message1(pg_errormessage().$q);
							
	}
	else
	{
		$r = @pg_fetch_object($qr);
		$apledger_id = $r->apledger_id;
		$q = "update apledger set
									debit = '".$aPOR['net_amount']."',
									credit='0',
									account_id = '".$aPOR['account_id']."',
									date = '".$aPOR['date']."',
									reference ='".$aPOR['reference']."'
						where
									apledger_id = '$apledger_id'";
		$qr = @pg_query($q) or message1(pg_errormessage().$q);
									
	}

	return;
}

function isFinished($por_header_id){
	$result = @pg_query("select * from por_header where por_header_id = '$por_header_id'");
	$r = @pg_fetch_assoc($result);
	$finished = $r['finished'];
	return $finished;
}


$p1 = $_REQUEST['p1'];
if( $_REQUEST['b'] == "Commit" ){
	@pg_query("update por_header set finished='1' where por_header_id = '".$aPOR['por_header_id']."'");	
}
if ($p1 == ''  && $b1 == '...') $p1='...';

$fields_header = array('date', 'reference', 'gross_amount', 'disc1', 'disc1_type','disc2', 'disc2_type','disc3', 'disc3_type','tax_add','tax_add_type', 'tax_amount',
						'net_amount','discount_amount','account_id','account','freight_amount','freight_add','remark', 'rr_header_id','releasedby','type','return_code','internal_reference','invoice_date');

$fields_detail = array('case_qty','cost1','unit_qty', 'cost2','cost3' ,'fraction2' , 'fraction3', 'amount', 'freight_case','stock');
$dfld = array('stock_id','barcode','case_qty','unit_qty','freight_case','cost1', 'cost2', 'cost3' ,'amount');

if (!in_array($p1, array(null,'Delete','Print','Load','Serve','Submit', 'poreturn')))
{

	for ($c=0;$c<count($fields_header);$c++)
	{
		$aPOR[$fields_header[$c]] = $_REQUEST[$fields_header[$c]];
		if ($fields_header[$c] == 'date' || $fields_header[$c]=='date_released'|| $fields_header[$c]=='checkdate' || $fields_header[$c]=="invoice_date" )
		{
			$aPOR[$fields_header[$c]] = mdy2ymd($_REQUEST[$fields_header[$c]]);
		}
		else
		{
			if (!in_array($fields_header[$c], array('account','remark','reference','releasedby')))
			{
				if ($aPOR[$fields_header[$c]] == '' )
				{
					$aPOR[$fields_header[$c]] = '0';
				}
				else
				{
					$aPOR[$fields_header[$c]] = str_replace(',','',$aPOR[$fields_header[$c]]);
				}
			}
		}
	}
	if ($aPOR['rr_header_id'] > '0')
	{
		$q = "select rr_header_id from rr_header where rr_header_id='".$aPOR['rr_header_id']."'";
		$qr = @query($q) or message(msyql_error());
		if (@pg_num_rows($qr) == 0)
		{
			message("SRR Number NOT found in Stocks Receiving File...");
		}
		else
		{
			$r = @fetch_object($qr);
			$aPOR['rr_header_id'] = $r->rr_header_id;
		}
			
	}
	
	$aPOR['gross_amount'] = $aPOR['gross_amount'] ; //str_ireplace(',','',$aPOR['gross_amount']);
	$aPOR['discount_amount'] = $aPOR['discount_amount']; //str_ireplace(',','',$aPOR['discount_amount']);
	$aPOR['net_amount'] = $aPOR['net_amount'] ; //str_ireplace(',','',$aPOR['net_amount']);


	$aPOR['admin_id']=$ADMIN['admin_id'];
	for ($c=0;$c<count($fields_detail);$c++)
	{
		$iPORD[$fields_detail[$c]] = $_REQUEST[$fields_detail[$c]];
		if (!in_array($fields_detail[$c], array('stock')))
		{
			if ($iPORD[$fields_detail[$c]] == '' )
			{
				$iPORD[$fields_detail[$c]] = '0';
			}
			else
			{
				$iPORD[$fields_detail[$c]] = str_replace(',','',$iPORD[$fields_detail[$c]]);
			}
		}
	}
	if ($iPORD['fraction3'] == '') $iPORD['fraction3'] = 1;
	$iPORD['qty1'] = $iPORD['unit_qty'] + $iPORD['case_qty'] * $iPORD['fraction3'] ;
	
	if ($aPOR['account_id'] != '')
	{
		$q = "select terms, credit_limit , address, account_code from account where account_id = '".$aPOR['account_id']."'";
		$qr = @pg_query($q) or message(db_error());
		$r = @pg_fetch_assoc($qr);
		if ($r)
		{
			$aPOR['terms'] = $r['terms'];
			$aPOR['credit_limit'] = $r['credit_limit'];
			$aPOR['address'] = $r['address'];
			$aPOR['account_code'] = $r['account_code'];
		}	
	}	
}
if ($aPOR['date'] =='' || $aPOR['date'] =='//')
{
	$aPOR['date'] = date('Y-m-d');
}
if ($p1 == 'Search' && $searchkey != '')
{
	$barcode = readBarcode($searchkey);
	$q = "select * from stock where barcode='$barcode'";
	$qr = @query($q) or message(db_error());
	if (pg_num_rows($qr)>0)
	{
		$r = @pg_fetch_assoc($qr);
		
		if ($r['stock_description'] != '')
		{
			$r['stock'] = $r['stock_description'];
		}
		$iPORD = $r;
		
		if ($iPORD['fraction2'] == 0 || $iPORD['fraction2'] == '') $iPORD['fraction2'] =1 ;
		if ($iPORD['fraction3'] == 0 || $iPORD['fraction3'] == '') $iPORD['fraction3'] =1 ;
		$iPORD['afraction'] = '1;'.$iPORD['fraction2'].';'.$iPORD['fraction3'];

		if ($aPOR['tax_add'] > 0)
		{
			$iPORD['cost3_with_tax'] = $iPORD['cost3'];
			$iPORD['cost3'] =round($iPORD['cost3'] / (1+($aPOR['tax_add']/100)),2);
			$iPORD['cost1'] = round($iPORD['cost3']/$iPORD['fraction3'],2);
		}
		$iPORD['fraction'] = 1;
		$iPORD['price'] = $iPORD['cost1'];
		$iPORD['unit'] = 1;
		
		$p1 = 'searchFound';
		$searchkey = $iPORD['barcode'];
		$focus = 'case_qty';
		if ($r['account_id'] != $aPOR['account_id'])
		{
			message1("Item Belongs to a Different Supplier...");
		}
	}
	else
	{
		$q = "select * from stock where upper(barcode)='".strtoupper($barcode)."'";
		$qr = @query($q) or message(db_error());
		if (pg_num_rows($qr)>0)
		{
			$r = @pg_fetch_assoc($qr);
			if ($r['stock_description'] != '')
			{
				$r['stock'] = $r['stock_description'];
			}
			$iPORD = $r;
			if ($iPORD['fraction2'] == 0 || $iPORD['fraction2'] == '') $iPORD['fraction2'] =1 ;
			if ($iPORD['fraction3'] == 0 || $iPORD['fraction3'] == '') $iPORD['fraction3'] =1 ;
			$iPORD['afraction'] = '1;'.$iPORD['fraction2'].';'.$iPORD['fraction3'];
			
			$iPORD['fraction'] = 1;
			$iPORD['price'] = $iPORD['cost1'];
			$iPORD['unit'] = 1;
			$searchkey = $iPORD['barcode'];
			$focus = 'case_qty';
			$p1 = 'searchFound';
			$focus='case_qty';
		}
	}
}

if ($p1=='Browse')
{
	echo "<script>window.location='?p=poreturn.browse&p1=Browse'</script>";
}
elseif (($p1 == 'Submit' )&& $source != '' )
{
	//retrieve PO
	if ($source == '' or $source == 'N' or $document== '')
	{
		$aPOR=null;
		$aPOR=array();
		$aPORD= null;
		$aPORD = array();
		$iPORD= null;
		$iPORD = array();
		$aPOR['date'] = date('Y-m-d');
	}
	else
	{
		$q = "select * from rr_header where $source = '$document'";
		$qr  = @pg_query($q) or message1(pg_errormessage());
		
		if (@pg_num_rows($qr) == 0)
		{
			message1("<br>[ SRR With Document No. ($source): $document NOT found... ]<br><br>
							<a href='?p=poreturn.new'>Click here to Try Again</a>");
			exit;
		}
		else
		{
			$r = @pg_fetch_object($qr);
			$id = $r->rr_header_id;
			$p1 = 'Receive';
		}
	}
}
$result = @pg_query("select * from rr_header where rr_header_id = '{$aPOR[rr_header_id]}' and status != 'C'");
$num_rows = @pg_num_rows($result);

if ($p1 == 'Load' && $id == '')
{
	message("Nothing to Load...");
}
elseif ($p1 == 'Load')
{
	$aPOR=null;
	$aPOR=array();
	$aPORD= null;
	$aPORD = array();
	$iPORD= null;
	$iPORD = array();

	$q = "select * from por_header where por_header_id='$id'";
	$qr = @query($q) or message(pg_errormessage());
	if ($qr)
	{
		$qd = "select * from por_detail where por_header_id='$id' order by por_detail_id";
		$qdr = @query($qd) or message(db_error());
		while ($r = @pg_fetch_assoc($qdr))
		{
			$temp = $r;
			$qs = "select stock, stock_description, stock_code, barcode, unit1, unit2, unit3, fraction2, fraction3
					 from 
						stock where stock_id='".$r['stock_id']."'";
						
			$qrs = @query($qs) or message(pg_errormessage());
			$rs = @pg_fetch_assoc($qrs);
			if ($rs['fraction3'] == 0) $rs['fraction3']=1;
			if ($rs['stock_description'] != '')
			{
				$rs['stock'] = $rs['stock_description'];
			}
			if ($rs)
			{
				$temp  += $rs;
			}	
			$aPORD[]=$temp;
		}
		
		$r = @pg_fetch_assoc($qr);
		$aPOR= $r;

		if ($aPOR['account_id'] > 0)
		{
			$q = "select * from account where account_id='".$aPOR['account_id']."'";
			$qra = @pg_query($q) or message1(pg_errormessage());
			
			$ra = @pg_fetch_object($qra);
			$aPOR['account'] = $ra->account;
			$aPOR['credit_limit'] = $ra->credit_limit;
			$aPOR['terms'] = $ra->terms;
			$aPOR['address'] = $ra->address;
			$aPOR['account_code'] = $ra->account_code;
		}	
	}
}
elseif ($p1 == 'New' or $p1 == 'Add New')
{

	$aPOR=null;
	$aPOR=array();
	$aPORD= null;
	$aPORD = array();
	$iPORD= null;
	$iPORD = array();
	$aPOR['date'] = date('Y-m-d');
	
}
elseif (in_array($p1,array('Edit','Ok','Save','Update','Search','selectStock','Release') )&& $aPOR['status'] == 'A')
{
	message('Editing not allowed. Stocks poreturn Report already Released/Posted');
}
elseif ($p1 == 'selectStock' && $id != '')
{
	$q = "select 
			stock_id, 
			stock_code, 
			barcode,
			stock_description as stock,
			cost1,
			cost2, 
			cost3,
			unit1,
			unit2,
			unit3, 
			fraction2,
			fraction3
		from 
			stock 
		where 
			stock_id = '$id'";
	$qr = @query($q) or message(db_error());
	$r = @pg_fetch_assoc($qr);
	
	$iPORD = $r;
	if ($iPORD['fraction2'] == 0 || $iPORD['fraction2'] == '') $iPORD['fraction2'] =1 ;
	if ($iPORD['fraction3'] == 0 || $iPORD['fraction3'] == '') $iPORD['fraction3'] =1 ;
	$iPORD['afraction'] = '1;'.$iPORD['fraction2'].';'.$iPORD['fraction3'];
	
	$iPORD['fraction'] = 1;
	$iPORD['price'] = $iPORD['cost1'];
	$iPORD['unit'] = 1;
	$searchkey = $iPORD['barcode'];
	$focus = 'case_qty';
	
	if ($aPOR['tax_add'] > '0')
	{
		$iPORD['cost3_with_tax'] = $iPORD['cost3'];
		$iPORD['cost3'] =round($iPORD['cost3'] / (1+($aPOR['tax_add']/100)),2);
		$iPORD['cost1'] = round($iPORD['cost3']/$iPORD['fraction3'],2);
	}
}
elseif ($p1 == 'Edit' && $id!='')
{
	$iPORD = null;
	$iPORD = array();
	$c=0;
	foreach ($aPORD as $temp)
	{
		$c++;
		if ($id == $c)
		{
			$iPORD = $temp;
			$iPORD['line_ctr'] = $c;
			$searchkey=$temp['barcode'];
			break;
		}
		
	}
	$focus = 'case_qty';
	
}
elseif ($p1 == 'Ok' && ($iPORD['stock_id']=='' || $iPORD['barcode']=='' || $iPORD['stock_id'] == '' || $iPORD['amount'] == '0'))
{
	message("Check: Stock Item Selected and Quantity ..");
	$aPOR['status']='M';
}
elseif ($p1 == 'Ok') // && $iPORD['stock_id']!='')
{
	$aPOR['status']='M';
	$dummy = null;
	$dummy = array();
	
	$fnd=0;
	$c=0;
	foreach ($aPORD as $temp)
	{
		if ($temp['stock_id'] == $iPORD['stock_id'])
		{
			$dummy = $temp;
			$dummy['case_qty'] = $iPORD['case_qty'];
			$dummy['unit_qty'] = $iPORD['unit_qty'];
			$dummy['cost1'] = $iPORD['cost1'];
			$dummy['cost2'] = $iPORD['cost2'];
			$dummy['cost3'] = $iPORD['cost3'];
			$dummy['freight_case'] = $iPORD['freight_case'];
			$dummy['amount'] = $iPORD['amount'];
			$dummy['stock'] = $iPORD['stock'];
			$dummy['qty1'] = $iPORD['qty1'];
			$aPORD[$c] = $dummy;
			$fnd = 1;
			break;
		}
		$c++;
	}
	
	if ($fnd == '0')
	{
			$aPORD[] = $iPORD;
	}
	$iPORD = null;
	$iPORD = array();
	$searchkey='';
}
elseif ($p1 == 'Delete Checked' && count($aChk)>0)
{
	$newArray=null;
	$newArray=array();
	$nctr=0;
	foreach ($aPORD as $temp)
	{
		$nctr++;
		if (in_array($nctr,$aChk))
		{
			if ($temp['por_detail_id'] != '')
			{
				$deletok=false;
				$qr = @query("delete from por_detail where por_detail_id='".$temp['por_detail_id']."'") or message1(db_error());
				if (@pg_affected_rows($qr)>0)
				{
					$deleteok=true;
				}
				else
				{
					message("FATAL error: Was not able to delete from Stocks poreturn Report detail file...".mysql_error($qr));
				}			
				if ($deleteok)
				{
					message("Record Deleted...");
				}
			}		
		}
		else
		{
			$newArray[]=$temp;
		}
	}
	$aPORD = $newArray;	
}
elseif ($p1 == 'Save' && !in_array($aPOR['status'],array(null,'M','S')))
{
	message("Cannot Update Stocks poreturn Report. Data already Released...".$aPOR['status']);
}
elseif ($p1 == 'Save' && count($aPORD) == 0)
{
	message("No items to save...");
}
elseif ($p1 == 'Save' && $aPOR['account_id'] == '0')
{
	message("No Supplier Specified.  Please check and Save again...");
}
/*else if($p1 == 'Save' && $num_rows == 0 ){
	message("SRR # Not Found");	
}*/
elseif ($p1 == 'Save')
{

	if ($aPOR['por_header_id'] == '')
	{
		$aPOR['audit'] = 'Encoded by '.$ADMIN['name'].' on '.date('m/d/Y g:ia').'; ';

		query("begin transaction");
		$time = date('G:i');
		$q = "insert into por_header ( time, admin_id, status, ip ";
		$qq .= ") values ('$time', '".$ADMIN['admin_id']."','S', '".$_SERVER['REMOTE_ADDR']."'";
		for ($c=0;$c<count($fields_header);$c++)
		{
			if ($fields_header[$c] == 'account') continue;
			$q .= ",".$fields_header[$c];
			$qq .= ",'".$aPOR[$fields_header[$c]]."'";
		}
		$q .= $qq.")";
		$qr = @query($q) or message1(db_error().$q);

		if ($qr && pg_affected_rows($qr)>0)
		{
			$ok=1;
			$aPOR['por_header_id'] = db_insert_id('por_header');

			// insert to si detail
			$c=0;
			foreach ($aPORD as $temp)
			{
				if ($temp['case_qty']*1 == '0' && $temp['unit_qty']*1 == '0') 
				{
					$c++;
					continue;
				}

				$q = "insert into por_detail (por_header_id";
				$qq = ") values ('".$aPOR['por_header_id']."'";
				for ($i=0;$i<count($dfld);$i++)
				{
					$q .= ",".$dfld[$i];
					$qq .= ",'".$temp[$dfld[$i]]."'";
				}
				$q .= $qq.")";

				$qr = @query($q) or message1(db_error().$q);
				if (@pg_affected_rows($qr) == 0 || !$qr)
				{
					$ok=false;
					break;
				}
				else
				{
					$dummy=$temp;
					$dummy['por_detail_id'] = db_insert_id('por_detail');
					$aPORD[$c]=$dummy;
					
					if ($aPOR['tax_add'] > 0)
					{
						$cost3 = round($temp['cost3']*(1+($aPOR['tax_add']/100)),2);
					}
					else
					{
						$cost3 = $temp['cost3'];
					}
					$cost1 = $cost3/ $temp['fraction3'];
					
					if ($SYSCONF['por_FORMAT'] != 'LEC')
					{
						/*$q = "update stock set cost3='$cost3', cost1='$cost1' where stock_id='".$temp['stock_id']."'";
						@pg_query($q) or message1('Error Updating Stocks Master...');*/
					}
				}

				$c++;
			}	
			if ($ok)
			{
				query("commit");
				$aPOR['status']='S';
				message(" Stocks poreturn Report Saved...");
			}
			else
			{
				query("rollback transaction");
				message("Problem Adding To Stocks poreturn Report Details...".db_error());
				$aPOR['status']='S';
				$aPOR['por_header_id']='';
			}
		}
		else
		{
			message("Cannot Add Record To Stocks poreturn Report Header File...");
			query("rollback transaction");
		}
							
	}
	else
	{
		$ok=true;
		$aPOR['audit'] = $aPOR['audit'].'Updated by '.$ADMIN['name'].' on '.date('m/d/Y g:ia').'; ';
	
		query("begin");	
		$q = "update por_header set por_header_id = '".$aPOR['por_header_id']."'";
		for ($c=0;$c<count($fields_header);$c++)
		{
			if ($fields_header[$c] == 'account') continue;

			$q .= ",".$fields_header[$c]."='".$aPOR[$fields_header[$c]]."'";
		}
		$q .= " where por_header_id = '".$aPOR['por_header_id']."'";

		#echo $q."<br>";
		
		$qr = @query($q) or message(db_error());
		if ($qr)
		{
			$c=0;
			foreach ($aPORD as $temp)
			{
				if ($temp['por_detail_id'] == '')
				{
					if ($temp['case_qty']*1 == '0' && $temp['unit_qty']*1 == '0') continue;

					$q = "insert into por_detail (por_header_id";
					$qq = ") values ('".$aPOR['por_header_id']."'";
					for ($i=0;$i<count($dfld);$i++)
					{
						$q .= ",".$dfld[$i];
						$qq .= ",'".addslashes($temp[$dfld[$i]])."'";
					}
					$q .= $qq.")";
					$qr = @query($q) or message(db_error().$q);
				}
				else
				{
					if ($temp['case_qty'] == '')
					{
						$temp['case_qty'] = 0;
					} 
					if ($temp['unit_qty'] == '')
					{
						$temp['unit_qty'] = 0;
					} 
					$q = "update por_detail set por_header_id='".$aPOR['por_header_id']."'";
					for ($i=0;$i<count($dfld);$i++)
					{
						$q .= ",".$dfld[$i]."='".addslashes($temp[$dfld[$i]])."'";
					}
					$q .= "	where por_detail_id='".$temp['por_detail_id']."'";

					$qr = @query($q) or message(db_error().$q);
				}	
				if (!$qr)
				{
					$ok=false;
					break;
				}
				else
				{
					$updatecost = $_REQUEST['updatecost'];
					if ($temp['por_detail_id'] == ''  || $updatecost == 'update')
					{
						if ($temp['por_detail_id'] == '')
						{
							$dummy=$temp;
							$dummy['por_detail_id'] = db_insert_id('por_detail');
							$aPORD[$c]=$dummy;
						}
						
						if ($aPOR['tax_add'] > 0)
						{
							$cost3 = round($temp['cost3']*(1+($aPOR['tax_add']/100)),2);
						}
						else
						{
							$cost3 = $temp['cost3'];
						}
						$cost1 = $cost3/ $temp['fraction3'];
						if ($SYSCONF['por_FORMAT'] != 'LEC')
						{
							/*$q = "update stock set cost3='$cost3', cost1='$cost1' where stock_id='".$temp['stock_id']."'";
							@pg_query($q) or message1('Error Updating Stocks Master...');*/
						}
					}
				}
				$c++;
			}	
			if ($ok)
			{
				query("commit");
				message(" Stocks poreturn Report Updated...");
				$aPOR['status']='S';
			}
			else
			{
				query("rollback");
				message("Problem Updating To Stocks poreturn Report Details...".db_error());
			}
		}
		else
		{
			message("Cannot Modify Record To Stocks poreturn Report Header File...".db_error().$q);
			query("rollback");
		}
					
			
	}
}
elseif ($p1 == 'poreturn' && $id == '')
{
	message("No Stocks Receiving Reference Specified...");
}
elseif ($p1 == 'poreturn')
{
	//-- Auto Posting From SRR
	$aPOR=null;
	$aPOR=array();
	$aPORD= null;
	$aPORD = array();
	
	$aPOR['rr_header_id'] = $id;
	$aPOR['date'] = date('Y-m-d');
	
}
elseif ($p1 == 'Release' && $aPOR['status']!='S')
{
	message('Status must be [ SAVED ] to Release Stocks poreturn Report...');
}
elseif ($p1 == 'Release')
{
	//update to stock ledger
	include_once('stockbalance.php');
	$total_request_qty = $total_issued_qty = $gross_amount = 0;
	$c=0;
	$ok=true;
	foreach ($aPORD as $temp)
	{
		$qty = $temp['qty1'];
		$price = $temp['price'];
		$total_request_qty += $qty;
		$ok=true;
		
		$stkled = stockBalance($temp['stock_id']);
		$balance_qty = $stkled['balance_qty'];
		
		$total_por_qty_out = $amount_out =  0;
		$por_qty_out = $qty_out = $qty_balance = 0;
		if ($balance_qty >= $qty)
		{
			$por_qty_out = $qty;
		}
		else
		{
			$por_qty_out = $balance_qty;
		}
		$amount_out += $por_qty_out*$price;
		$total_por_qty_out += $por_qty_out;

		$total_issued_qty += $total_por_qty_out;
		$gross_amount  += $amount_out;	
		$q = "update por_detail set 
					qty_out='$total_por_qty_out', 
					amount_out = '$amount_out',
					stockledger_id_array='$stockledger_id_array'
				where
					por_detail_id='".$temp['por_detail_id']."'";
		$qr = @query($q) or message(db_error()." Error updating Issued Quantities...Item :".$temp['stock']);
		if (!$qr)
		{
			$ok = false;
		}
		else
		{
			commit();
			$dummy = $temp;
			
			if ($total_por_qty_out > 0)
			{
				$dummy['qty_out'] = $total_por_qty_out;
				$dummy['amount_out'] = $amount_out;
				$dummy['price_out'] = $amount_out/$total_por_qty_out;
			}
			else
			{
				$dummy['qty_out'] = 0;
				$dummy['amount_out'] = 0;
				$dummy['price_out'] = 0;
			}	
			$aPORD[$c] = $dummy;
		}	
		$c++;
	}
	if ($ok)
	{
		$aPOR['gross_amount'] = $gross_amount;
		$aPOR['discount_amount'] = round($aPOR['gross_amount'] * $aPOR['discount_percent']/100,2);
		$aPOR['net_amount'] = $aPOR['gross_amount'] - $aPOR['discount_amount'];
		
		if ($aPOR['date_released'] == '')
		{
			$aPOR['date_released'] = date('Y-m-d');
		}
		
		$q = "update por_header set 
					status='A', 
					date_released = '".$aPOR['date_released']."',
					gross_amount = '".$aPOR['gross_amount']."',
					discount_amount = '".$aPOR['discount_amount']."',
					net_amount = '".$aPOR['net_amount']."'
			 where 
			 		por_header_id='".$aPOR['por_header_id']."'";
		@query($q) or message('Unable to update Stocks poreturn Report Status '.db_error());
		$aPOR['status']='A';
		if ($total_request_qty ==  $total_issued_qty)
			message("Stocks Successfully Released...");
		else
			message("Stocks Successfully Released with UNSERVED Items.");
	}
}
elseif ($p1 == 'Print' && !in_array($aPOR['status'], array('A','P','S')))
{
	message("Cannot Print. <b>Save</b> Stocks poreturn Report Before Printing...");
}
elseif ($p1 == 'Print' ) //&& $SYSCONF['RR_FORMAT']=='LEC')
{
	postApLedgerPR($aPOR['por_header_id']);
	//Stocks poreturn Report
	$q = "";
	$q .= space(4).adjustSize(strtoupper($SYSCONF['BUSINESS_NAME']),82);
	$q .= "PURCHASE RETURN\n";
	$q .= space(4).adjustSize($SYSCONF['BUSINESS_ADDR'],82);	
	$q .= 'SRR No.: '.str_pad($aPOR['por_header_id'],8,'0',STR_PAD_LEFT)."\n\n";
	
	$q .= space(4)."Supplier : [".$aPOR['account_code'].'] '.adjustSize($aPOR['account'],70).' '. "Date  : ".ymd2mdy($aPOR['date'])."\n";
	$q .= "<small3>";
	$q .= space(4).str_repeat('-',128)."\n";
	$q .= space(4)."  Received  Quantity       Barcode          Item Description                                     UnPrice   UnCost    Amount  \n";
	$q .= space(4).str_repeat('-',128)."\n";
	$c=0;

	foreach ($aPORD as $temp)
	{
		if ($temp['case_qty'] ==  intval($temp['case_qty']))
		{
			$case_qty = $temp['case_qty'];
		}
		else
		{
			$case_qty = round($temp['case_qty'],3);
		}
		if ($temp['unit_qty'] ==  intval($temp['unit_qty']))
		{
			$unit_qty = $temp['unit_qty'];
		}
		else
		{
			$unit_qty = round($temp['unit_qty'],3);
		}
		$price1 = lookUpTableReturnValue('x','stock','stock_id','price1',$temp['stock_id']);

		$cqty = '';
		$c++;
		$q .= space(4);
		
		if ($case_qty>0)
		{
			$cqty = adjustRight($case_qty,5).' '.adjustSize($temp['unit3'],4).'  ';
		}
		if ($unit_qty>0)
		{
			$cqty .= adjustRight($unit_qty,6).'  '.adjustSize($temp['unit1'],4).' ';
		}
		$q .= adjustSize($cqty,25).	adjustSize($temp['barcode'],15).'  '.
				adjustSize(stripslashes(substr($temp['stock'],0,50)),50).'  '.
//				adjustRight($temp['fraction3'],4).' '.
//				adjustRight(number_format($temp['freight_case'],2),10).' '.
				adjustRight(number_format($price1,2),10).' '.
				adjustRight(number_format($temp['cost1'],2),10).' '.
				adjustRight(number_format($temp['amount'],2),11)."\n";
	}
	$q .= space(4).str_repeat('-',128)."\n";
	$sub_wo_freight = $aPOR['gross_amount'] - $aPOR['discount_amount'];

	$q .= adjustSize(space(5).adjustSize($c.' Line(s)',60),96).
				'GROSS AMOUNT   :       '.adjustRight(number_format($aPOR['gross_amount'],2),12)."\n";
	$q .= space(96).
				'DISCOUNT       :       '.adjustRight(number_format($aPOR['discount_amount'],2),12)."\n";
	$q .= adjustSize(space(10).adjustSize($ADMIN['name'],30).space(25).center(($aPOR['releasedby']*1 == '0' ? str_repeat("_",30): $aPOR['releasedby']),30),96).
				'TAX AMOUNT     :       '.adjustRight(number_format($aPOR['tax_amount'],2),12)."\n";
	$q .= adjustSize(space(10).adjustSize('Prepared by:',30).space(35)."Received by:",96).
				'SUB-TOTAL      :       '.adjustRight(number_format($aPOR['sub_wo_freight'],2),12)."\n";
	$q .= adjustSize(space(10).date('m/d/Y g:ia'),96).
				'FREIGHT AMOUNT :       '.adjustRight(number_format($aPOR['freight_amount'],2),12)."\n";
	$q .= space(96).
				'NET AMOUNT     :       '.adjustRight(number_format($aPOR['net_amount'],2),12)."\n";

	$q .= space(10).str_repeat("_",30).space(25).str_repeat("_",30)."\n";
	$q .= space(10).adjustSize("Approved for Payment:",30).space(35)."Checked by:\n";
	
	if ($aPOR['remark'] != '')
	{
		$q .= "\n".space(5).'Remarks:'.$aPOR['remark']."\n";
	}
	$q .= "<reset>\n\n\n\n";
	
//	 echo "<pre>$q</pre>";
	if ($SYSCONF['REPORT_PRINTER_TYPE'] != 'GRAPHICS')
	{
	//			nPrinter($q, $SYSCONF['REPORT_PRINTER_TYPE'], $SYSCONF['REPORT_PRINTER_DEST']);
	}
	else
	{
	 //	 echo "<input type='hidden' id='print_area' name='print_area' value=".'"'.$q.'"'.">";
	//	echo "<iframe name='printit' id='printit' style='width:0px;height:0px;'>";
	//	echo "</iframe>";
	}
}
elseif ($p1=='CancelConfirm' && !chkRights2('sales','mdelete',$ADMIN['admin_id']))
{
	message('You have [ NO ] permission to cancel sales transaction...');
}
elseif ($p1=='CancelConfirm')
{
	//begin();
	$ok=true;
	@query("select * from por_header where  por_header_id='".$aPOR['por_header_id']."' for update");
	
	$q = "update por_header set status='C' where por_header_id='".$aPOR['por_header_id']."'";
	$qr = query($q) or message("Cannot Cancel Stocks poreturn Report ".db_error());	
	/*
	if ($qr)
	{
		foreach ($aPORD as $temp)
		{
			if ($temp['stockledger_id_array'] != '')
			{
				$qty=$temp['qty'];
				$ids = explode(',',$temp['stockledger_id_array']);
				for ($c=0;$c<count($ids);$c++)
				{
					$stockledger_id=$ids[$c];
					$q = "select * from stockledger 
							where 
								stockledger_id='$stockledger_id' ";
					//$qr = query($q) or die ("Problem Querying Stock Ledger ".db_error());
					$r = fetch_object($q);
					if ($r->qty_out >= $qty)
					{
						$qty_out = $r->qty_out-$qty;
						$qty_balance = $r->qty_in - $qty_out;
						$qr = @query("update stockledger set qty_out='$qty_out', qty_balance='$qty_balance'
									where stockledger_id='$r->stockledger_id'") or die ("Problem updating stockledger ".db_error());
						$qty=0;									
					}
					else
					{
						$qty -= $r->qty_out;
						$qty_out=0;
						$qty_balance=$r->qty_in;
						$qr = query("update stockledger set qty_out='$qty_out', qty_balance='$qty_balance'
									where stockledger_id='$r->stockledger_id'") or die ("Problem updating stockledger ".db_error());
					}
					if ($qty<=0) break;
					
				}
			}
			else
			{
				$q = "select * from stockledger 
						where 
							stock_id='".$temp['stock_id']."' 
						order by 
							stockledger_id desc";
				$qr = @query($q) or message(db_error()) ;
				$qty = $temp['qty'];
				while ($r = fetch_object($qr))
				{
					if ($r->qty_out >= $qty)
					{
						$qty_out = $r->qty_out-$qty;
						$qty_balance = $r->qty_in-$qty_out;
						$qr = @query("update stockledger set qty_out='$qty_out', qty_balance='$qty_balance'
									where stockledger_id='$r->stockledger_id'") or die ("Problem updating stockledger ".db_error());
						$qty=0;									
					}
					else
					{
						$qty -= $r->qty_out;
						$qty_out=0;
						$qty_balance=$r->qty_in;
						$qr = query("update stockledger set qty_out='$qty_out', qty_balance='$qty_balance'
									where stockledger_id='$r->stockledger_id'") or die ("Problem updating stockledger ".db_error());
					}
					if ($qty<=0) break;
				}
			}
			if (!$qr)
			{
				$ok=false;
				break;
			}
		}	
	}
	else
	{
		$ok=false;
	}
	*/
	if ($ok)
	{
		commit();
		$aPOR['status']='C';
		message(' Stocks Stocks poreturn Report No. ['.str_pad($aPOR['por_header_id'],8,'0',STR_PAD_LEFT).'] Successfully CANCELLED');
	}
	else
	{
		message('Problem deleting stock ledger data...'.db_error());
	}
}
?>
<script language="JavaScript" type="text/JavaScript">
<!--
function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);
//-->
</script>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<form action="?p=poreturn" method="post" name="f1" id="f1" style="margin:0">
  <table width="99%" border="0" align="center" cellpadding="1" cellspacing="1">
    <tr> 
      <td colspan="8"><font size="2">Search</font> 
        <input name="xSearch" type="text" class="altText" id="xSearch2" value="<?= $xSearch;?>">
        <?= lookUpAssoc('searchby',array('Rec No.'=>'por_header_id','reference'=>'reference','SRR. No.'=>'rr_header_id','Supplier'=>'account.account','Supplier Code'=>'account.account_code','Remarks'=>'remarks','Stock description'=>'stock_description'), $searchby);?>
        <input name="p122" type="button" class="altBtn" id="p122" value="Go" onClick="window.location='?p=poreturn.browse&p1=Go&xSearch='+xSearch.value+'&searchby='+searchby.value"> 
        <input type="submit" class="altBtn" name="p1" id="addnew" value="Add New"> 
        <input name="p12" type="button" class="altBtn" id="p12" value="Browse" onClick="window.location='?p=poreturn.browse&p1=Browse'"> 
	<input type="button" class="altBtn" name="Submit232" value="Close" onClick="window.location='?p='"> 
    <? if(!isFinished($aPOR['por_header_id'])) { ?>
    <input type="submit" class="altBtn" name="b" value="Commit">
    <? } ?>
      </td>
    </tr>
     <tr bgcolor="#CCCCCC"> 
      <td  colspan="6"> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"><img src="../graphics/waiting.gif" width="16" height="16"><strong> 
          Purchase Return Entry</strong></font></td>
      <td height="20" colspan="2" align="center"> <font size="2" face="Times New Roman, Times, serif"> 
        <em> 
        <?
         if(!isFinished($aPOR['por_header_id'])) {
			status($aPOR['status']); 
		 }else{
         	echo "Commited";
		 }
		?>
        </em></font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td width="8%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Type</font></td>
      <td width="22%" nowrap >
        <input name="account_id" type="hidden" id="account_id" value="<?= $aPOR['account_id'];?>" size="5" maxlength="30">
		<select name='type' id='type'>
		<option value='1' <?= ($aPOR['type'] == 1 ? 'selected' : '');?>>Return from SRR</option>
		<option value='2' <?= ($aPOR['type'] == 2 ? 'selected' : '');?>>Bad/Damaged Stocks</option>
		</select>
        <font size="2" face="Verdana, Arial, Helvetica, sans-serif">SRR # 
        <input name="rr_header_id" type="text" class="altText" id="rr_header_id" tabindex="8"  value="<?= $aPOR['rr_header_id'];?>" size="15"  onKeypress="if(event.keyCode==13) {document.getElementById('checkRR').focus();return false;}" onChange="document.getElementById('checkRR').click()">
        </font><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <input name="checkRR" type="button" id="checkRR" value="^" onClick="xajax_porCheckRR(xajax.getFormValues('f1'));" onMouseOver="showToolTip(event,'Check if SRR exists...');return false" onMouseOut="hideToolTip()" class="altBtn">
        </font><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; </font></td>
      <td width="8%" nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Disc1</font></td>
      <td width="8%" nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="disc1" type="text" class="altText" id="disc1" value="<?=$aPOR['disc1'];?>" size="5"  tabindex="4"  style="text-align:right"   onKeypress="if(event.keyCode==13) {document.getElementById('disc2').focus();return false;}" onChange="xajax_por_subtotal(xajax.getFormValues('f1'))">
        <?= lookUpAssoc('disc1_type',array('AMT'=>'A','%'=>'P'), $aPOR['disc1_type']);?>
        </font></td>
      <td width="8%"  nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Add-OnFreight</font></td>
      <td width="8%"  nowrap >
        <input name="freight_add" type="text" class="altText" id="freight_add" value="<?= $aPOR['freight_add'];?>" size="14"  tabindex="4"  style="text-align:right"   onKeypress="if(event.keyCode==13) {document.getElementById('tax_add').focus();return false;}" onChange="xajax_por_subtotal(xajax.getFormValues('f1'))">
        </td>
      <td width=8% nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Rec 
        No.
        </font></td>
      <td width="30%"  nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= str_pad($aPOR['por_header_id'],8,'0',STR_PAD_LEFT);?>
        </font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td valign="top"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Supplier</font></td>
      <td nowrap >
        <input name="account" type="text" class="altText" id="account" value="<?=stripslashes( $aPOR['account']);?>" size="35"  onChange="b1.click()"  tabindex="2"   onKeypress="if(event.keyCode==13) {document.getElementById('b1').focus();return false;}">
        <input name="account_code" type="text" readOnly class="altText" id="account_code" tabindex="8"  value="<?= $aPOR['account_code'];?>" size="7"  onKeypress="if(event.keyCode==13) {document.getElementById('reference').focus();return false;}">
        <input name="b1" type="button" class="altBtn" id="b1" value="..." onClick="xajax_porder_searchaccount(xajax.getFormValues('f1'));document.getElementById('reference').focus()" >
        </td>
      <td nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Disc2</font><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
        </font></td>
      <td nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <input name="disc2" type="text" class="altText" id="disc2" value="<?= $aPOR['disc2'];?>" size="5"  tabindex="4"  style="text-align:right"  onKeypress="if(event.keyCode==13) {document.getElementById('disc3').focus();return false;}"  onChange="xajax_por_subtotal(xajax.getFormValues('f1'))">
        <?= lookUpAssoc('disc2_type',array('AMT'=>'A','%'=>'P'), $aPOR['disc2_type']);?>
        </font></td>
      <td nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Add-On 
        Tax</font></td>
      <td nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="tax_add" type="text" class="altText" id="tax_add" value="<?= stripslashes( $aPOR['tax_add']);?>" size="8"  tabindex="4"  style="text-align:right"   onKeyPress="if(event.keyCode==13) {document.getElementById('searchkey').focus();return false;}" onBlur="xajax_por_subtotal(xajax.getFormValues('f1'))" onChange="document.getElementById('updatecost').checked=true">
        <?= lookUpAssoc('tax_add_type',array('%'=>'P','AMT'=>'A'), $aPOR['tax_add_type']);?>
        </font></td>
      <td ><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Date </font></td>
      <td nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="date" type="text" class="altText" id="date" tabindex="8"  value="<?= ymd2mdy($aPOR['date']);?>" size="10" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')"  onKeypress="if(event.keyCode==13) {document.getElementById('reference').focus();return false;}">
        <img src="../graphics/dwn-arrow-grn.gif" onClick="popUpCalendar(this, f1.date, 'mm/dd/yyyy')"> 
        </font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Invoice #</font></td>
      <td nowrap>
	  <input name="reference" type="text" class="altText" id="reference" tabindex="8"  value="<?= $aPOR['reference'];?>" size="15"  onKeypress="if(event.keyCode==13) {document.getElementById('searchkey').focus();return false;}">
        <font size="2" face="Verdana, Arial, Helvetica, sans-serif">Terms 
        <input name="terms" type="text" class="altText" id="terms" tabindex="8"  value="<?= $aPOR['terms'];?>" size="18"  readOnly>
        </font> </td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Disc3</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <input name="disc3" type="text" class="altText" id="disc3" value="<?=  $aPOR['disc3'];?>" size="5"  tabindex="4"  style="text-align:right"  onKeypress="if(event.keyCode==13) {document.getElementById('freight').focus();return false;}" onChange="xajax_por_subtotal(xajax.getFormValues('f1'))">
        <?= lookUpAssoc('disc3_type',array('AMT'=>'A','%'=>'P'), $aPOR['disc3_type']);?>
        </font></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    
    <tr bgcolor="#FFFFFF"> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Date Received</font></td>
      <td nowrap>
	  <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input readonly name="date_received" type="text" class="altText" id="date_received" tabindex="8"  value="<?= ymd2mdy($aPOR['date_received']);?>" size="10" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')"  onKeypress="if(event.keyCode==13) {document.getElementById('reference').focus();return false;}" >
        <!--<img src="../graphics/dwn-arrow-grn.gif" onClick="popUpCalendar(this, f1.date_received, 'mm/dd/yyyy')">  -->
      </font>
	  <font size="2" face="Verdana, Arial, Helvetica, sans-serif">Invoice Date </font>
      <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="invoice_date" readonly type="text" class="altText" id="invoice_date" tabindex="8"  value="<?= ymd2mdy($aPOR['invoice_date']);?>" size="10" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')"  onKeypress="if(event.keyCode==13) {document.getElementById('reference').focus();return false;}">
        <img src="../graphics/dwn-arrow-grn.gif" onClick="popUpCalendar(this, f1.invoice_date, 'mm/dd/yyyy')"> 
      </font></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    
    
    
    <?
	$result = @pg_query("select * from return_code order by return_code asc");
	$rc_content = "
		<select name='return_code' id='return_code'>
			<option value=''>Select Return Code:</option>
	";
	while($r = @pg_fetch_assoc($result)){
	
	$selected = ($r['return_code_id'] == $aPOR['return_code'])?"selected='selected'":"";
	
	$rc_content.="
		<option value='$r[return_code_id]' $selected >$r[return_code] - $r[description]</option>
	";	
		
	}
	
	?>
    
    <tr bgcolor="#FFFFFF"> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Internal Reference </font></td>
      <td nowrap>
	  <input name="internal_reference" type="text" class="altText" id="internal_reference" tabindex="8"  value="<?= $aPOR['internal_reference'];?>" size="15"  onKeypress="if(event.keyCode==13) {document.getElementById('searchkey').focus();return false;}">
      <font size="2" face="Verdana, Arial, Helvetica, sans-serif">Return Code 
        <?=$rc_content?>
        </font> </td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    
    <tr bgcolor="#CCCCCC"> 
      <td colspan="8">
	   <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#EFEFEF">
          <tr valign="top"> 
            <td width="15%" nowrap><font size="1" face="Verdana, Arial, Helvetica, sans-serif">Item 
              <br>
              <input name="searchkey" type="text" class="altText" id="searchkey" value="<?= $searchkey;?>" size="15"  tabindex="10"  onKeypress="if(event.keyCode==13) {document.getElementById('case_qty').focus();return false;}" onBlur="if (searchkey.value != '') {document.getElementById('SearchButton').click();}">
              <?= lookUpAssoc('searchitemby',array('Barcode'=>'barcode','Name'=>'stock','Desc'=>'stock_description'),$searchitemby);?>
              <input name="SearchButton" type="button" class="altBtn" id="SearchButton" value="Search" onClick="wait('Please wait. Searching...');xajax_pc_search(xajax.getFormValues('f1'),'por_select', 'case_qty');"">
              </font></td>
            <td width="21%"><font size="1" face="Verdana, Arial, Helvetica, sans-serif">Description<br>
              <input name="stock" type="text" class="altText" id="stock" value="<?= stripslashes($iPORD['stock']);?>" size="35" readOnly>
              </font></td>
            <td width="3%"><font size="1" face="Verdana, Arial, Helvetica, sans-serif">Cases<br>
              <input name="case_qty" type="text" class="altNum" id="case_qty" value="<?= $iPORD['case_qty'];?>" size="5" onBlur="vCompute(this)"  onKeypress="if(event.keyCode==13) {document.getElementById('unit_qty').focus();return false;}">
              </font></td>
            <td width="3%" align="center"><font size="1" face="Verdana, Arial, Helvetica, sans-serif">Units</font><font size="1"><br>
              <input name="unit_qty" type="text" class="altText" id="unit_qty" value="<?= $iPORD['unit_qty'];?>" size="5"  tabindex="12"  onBlur="vCompute(this)"  style="text-align:right"  onKeypress="if(event.keyCode==13) {document.getElementById('cost3').focus();return false;}">
              </font></td>
            <td width="3%"><font size="1" face="Verdana, Arial, Helvetica, sans-serif">CsCost</font><font size="1"><br>
              <input name="cost3" type="text" class="altText" id="cost3" value="<?= $iPORD['cost3'];?>" size="9" onChange="vFrac3();vCompute(this)"  onKeypress="if(event.keyCode==13) {document.getElementById('cost1').focus();return false;}" style="text-align:right">
              </font></td>
            <td width="3%"><font size="1" face="Verdana, Arial, Helvetica, sans-serif">UnCost<br>
              <input name="cost1" type="text" id="cost1" class="altText" value="<?= $iPORD['cost1'];?>" size="8" onChange="vFrac();vCompute(this)"  onKeypress="if(event.keyCode==13) {document.getElementById('freight_case').focus();return false;}" style="text-align:right">
              </font></td>
            <td width="2%"><font size="1" face="Verdana, Arial, Helvetica, sans-serif">CsFrt<br>
              <input name="freight_case" type="text" id="freight_case" class="altText" value="<?= $iPORD['freight_case'];?>" size="7" onChange="vCompute(this)"  onKeypress="if(event.keyCode==13) {document.getElementById('amount').focus();return false;}" style="text-align:right">
              </font></td>
            <td width="3%"><font size="1" face="Verdana, Arial, Helvetica, sans-serif">Amount</font><font size="1"><br>
              <input name="amount" type="text" class="altText" id="amount" value="<?= number_format($iPORD['amount'],2);?>" size="10"  style="text-align:right"  onChange="vAmount()"  onKeypress="if(event.keyCode==13) {document.getElementById('Ok').focus();return false;}">
              </font></td>
            <td width="47%" valign="middle"> <font size="1"><br>
              <? if(!isFinished($aPOR['por_header_id'])) {?>
              <input name="p1" type="button" class="altBtn" id="Ok" value="   Ok   " onClick="xajax_porOk(xajax.getFormValues('f1'));">
              <? } ?>
              <input name="cost2" type="hidden" id="cost2" value="<?= $iPORD['cost2'];?>" size="7">
              <input name="fraction3" type="hidden" id="fraction3" value="<?= $iPORD['fraction3'];?>" size="7">
              <input name="case_order" type="hidden" id="case_order" value="<?= $iPORD['case_order'];?>" size="7">
              <input name="unit_order" type="hidden" id="unit_order" value="<?= $iPORD['unit_order'];?>" size="7">
              <input name="balance_order" type="hidden" id="balance_order" value="<?= $iPORD['balance_order'];?>" size="7">
              </font></td>
          </tr>
        </table></tr>
  </table>
  <table width="99%" border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#EFEFEF">
    <tr bgcolor="#D2DCDF"> 
      <td width="5%" align="center"><font color="#000099" size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></td>
      <td width="15%" nowrap><font color="#000099" size="2" face="Verdana, Arial, Helvetica, sans-serif">Bar 
        Code</font></td>
      <td width="30%"><font color="#000099" size="2" face="Verdana, Arial, Helvetica, sans-serif">Description</font></td>
      <td width="6%" align="center"><font color="#000099" size="2" face="Verdana, Arial, Helvetica, sans-serif">Unit</font></td>
      <td width="9%" align="center"><font color="#000099" size="2" face="Verdana, Arial, Helvetica, sans-serif">Received</font></td>
      <td width="5%" align="center"><font color="#000099" size="2" face="Verdana, Arial, Helvetica, sans-serif">Returned</font></td>
      <td width="6%" align="center"><font color="#000099" size="2" face="Verdana, Arial, Helvetica, sans-serif">CsFrt</font></td>
      <td width="5%" align="center"><font color="#000099" size="2" face="Verdana, Arial, Helvetica, sans-serif">UnCost</font></td>
      <td width="5%" align="center"><font color="#000099" size="2" face="Verdana, Arial, Helvetica, sans-serif">CsCost</font></td>
      <td width="5%" align="center"><font color="#000099" size="2" face="Verdana, Arial, Helvetica, sans-serif">Amount</font></td>
    </tr>
    <tr height="220px"> 
      <td height="220px" colspan='10'> <div id="gridLayer"  name="gridLayer" style="position:virtual; width:100%; height:100%; z-index:1; overflow: auto;"></div></td>
    </tr>
    <!--
    <?
	$c=0;
	$gross_amount=$freight_amount = 0;
	foreach ($aPORD as $temp)
	{
		$gross_amount += $temp['amount'];
		
		$cqty = intval(($temp['case_qty']*$temp['fraction3'] + $temp['unit_qty'])/$temp['fraction3']);
		
		if ($temp['case_qty'] ==  intval($temp['case_qty']))
		{
			$case_qty = $temp['case_qty'];
		}
		else
		{
			$case_qty = round($temp['case_qty'],3);
		}
		if ($temp['unit_qty'] ==  intval($temp['unit_qty']))
		{
			$unit_qty = $temp['unit_qty'];
		}
		else
		{
			$unit_qty = round($temp['unit_qty'],3);
		}

		$freight_amount += $cqty * $temp['freight_case'];

		if ($temp['fraction'] == '' || $temp['fraction']==0) $temp['fraction'] =1;
		$stock = trim($temp['stock']);
		$c++;
		
		if ($temp['case_qty'] > 0 || $temp['unit_qty'] > 0)
		{
			$bgcolor = '#CCCCFF';
		}		
		else
		{
			$bgcolor = '#FFFFFF';
		}

	?>
    <tr valign="top" bgColor='<?= $bgcolor;?>' onMouseOver="bgColor='#FFFFCC'" onMouseOut="bgColor='<?= $bgcolor;?>'"> 
      <td align="right" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $c;?>
        . 
        <input name="aChk[]" type="checkbox" id="aChk" value="<?= $c;?>">
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href="javascript: document.getElementById('f1').action='?p=poreturn&p1=Edit&id=<?=$c;?>';document.getElementById('f1').submit()"> 
        <?= $temp['barcode'];?>
        </a> </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="javascript: document.getElementById('f1').action='?p=poreturn&p1=Edit&id=<?=$c;?>';document.getElementById('f1').submit()"> 
        <?= stripslashes($temp['stock']);?>
        </a></font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $temp['unit1'];?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= intval($temp['case_order']).' : '.$temp['unit_order'].'('.$temp['balance_order'].')';?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $case_qty." : ".$unit_qty;;?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= number_format($temp['freight_case'],2);?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= ($aPOR['status'] == 'A' ? number_format($temp['cost3'],2) : number_format($temp['cost3'],2));?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= ($aPOR['status'] == 'A' ? number_format($temp['amount_out'],2) : number_format($temp['amount'],2));?>
        </font></td>
    </tr>
    <?

	}

	por_subtotalCompute($gross_amount);
	?>
-->
    <tr bgcolor="#FFFFFF"> 
      <td colspan="10"> <table width="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#EFEFEF">
          <tr bgcolor="#FFFFFF"> 
            <td width="58%" rowspan="6" valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Remarks 
              <? if(!isFinished($aPOR['por_header_id'])) {?>
              | <a href="#" onClick="if (confirm('Are you sure to delete items?')){xajax_porDelete(xajax.getFormValues('f1'));};return false">Delete Checked </a>
              <? } ?>
              | Received by: 
              <input name="releasedby" type="text" id="releasedby" value="<?= $aPOR['releasedby'];?>" size="38" class="altText">
              <br>
              </font> <textarea class="altTextArea" name="remark" cols="75" id="remark"><?= $aPOR['remark'];?></textarea> 
              <br> 
			  <table width="1%" border="0" cellpadding="1" cellspacing="1" bgcolor="#CCCCCC">
                <tr bgcolor="#FFFFFF"> 
                <? if(!isFinished($aPOR['por_header_id'])) {?>
                  <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
                    <input type="image" src="../graphics/save.jpg" accesskey="S" alt="Save This Claim Form" width="57" height="15" id="Save2" onClick="f1.action='?p=poreturn&p1=Save';f1.submit();" name="Save">
                    </strong></font><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
                    </strong></font></td>
                <? } ?>
                  <td nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
                    <input type = "image" src="../graphics/print.jpg"  alt="Print This Claim Form"  name="Print" border="0" id="Print"  accesskey="P" onClick="f1.action='?p=poreturn&p1=Print';f1.submit();">
                    </strong></font></td>
                  <td nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
                    <input type="image"  accesskey="P" src="../graphics/price.jpg" alt="Print Price List"  onClick="f1.action='?p=poreturn&p1=PrintSRP';f1.submit();" name="Print" id="Print2">
                    </strong></font></td>
               	<? if(!isFinished($aPOR['por_header_id'])) {?>
                  <td nowrap width="25%"> <input type='image' name="Cancel" id="Cancel2" onClick="if (confirm('Are you sure to CANCEL Entry?')) {document.getElementById('f1').action='?p=poreturn&p1=CancelConfirm'; document.getElementById('f1').submit();}"  src="../graphics/cancel.jpg" alt="Cancel Form" width="77" height="20"> 
             	<? } ?>
                  <td nowrap width="25%"> <a href="javascript: document.getElementById('addnew').click()"><img  src="../graphics/new.jpg"  alt="New Claim Form" name="New" width="63" height="20" border="0" id="New" accesskey="N"></a> 
                  </td>
                </tr>
              </table></td>
            <td width="10%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Gross 
              Amount</font></td>
            <td width="11%" align="right" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
              <strong> 
              <input name="gross_amount" type="text" class="altText" id="gross_amount" value="<?= number_format($aPOR['gross_amount'],2);?>" size="10" readOnly  style="text-align:right; font-weight:bold; border:#FFFFFF none">
              </strong> </font></td>
          </tr>
          <tr bgcolor="#FFFFFF"> 
            <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">(-) 
              Total Discount</font></td>
            <td align="right" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
              <strong> 
              <input name="discount_amount" type="text" class="altText" id="discount_amount" value="<?= number_format($aPOR['discount_amount'],2);?>" readOnly size="10"  style="text-align:right; font-weight:bold;border:#FFFFFF none">
              <? @pg_query("update por_header set discount_amount = '".$aPOR['discount_amount']."' where por_header_id = '".$aPOR['por_header_id']."' "); ?>
              <? @pg_query("update por_header set net_amount = '".$aPOR['net_amount']."' where por_header_id = '".$aPOR['por_header_id']."' "); ?>
              </strong> </font></td>
          </tr>
          <tr bgcolor="#FFFFFF"> 
            <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">(+) 
              Tax 
              <?
			  	if ($aPOR['tax_add'] == '0')
				{
					echo "Inclusive";
				}
				elseif ($aPOR['tax_add_type'] == 'P')
				{
					echo $aPOR['tax_add']."% AddOn";
				}
				elseif ($aPOR['tax_add_type'] == 'A')
				{
					echo " AddOn";
				}
				?>
              </font></td>
            <td align="right" nowrap><input name="tax_amount" type="text" class="altText" id="tax_amount" value="<?= number_format($aPOR['tax_amount'],2);?>" size="10" readOnly  style="text-align:right; font-weight:bold;border:#FFFFFF none"> 
            </td>
          </tr>
          <tr bgcolor="#FFFFFF"> 
            <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Sub-Total</font></td>
            <td align="right" nowrap><input name="sub_wo_freight" type="text" class="altText" id="sub_wo_freight" value="<?= number_format($aPOR['sub_wo_freight'],2);?>" size="10" readOnly  style="text-align:right; font-weight:bold;border:#FFFFFF none"></td>
          </tr>
          <tr bgcolor="#FFFFFF"> 
            <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">(+) 
              Freight </font></td>
            <td align="right" nowrap><input name="freight_amount" type="text" class="altText" id="freight_amount" value="<?= number_format($aPOR['freight_amount'],2);?>" size="10" readOnly  style="text-align:right; font-weight:bold;border:#FFFFFF none"> 
            </td>
          </tr>
          <tr bgcolor="#FFFFFF"> 
            <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Net 
              Amount</font></td>
            <td align="right" nowrap><input name="net_amount" type="text" class="altText" id="net_amount" value="<?= number_format($aPOR['net_amount'],2);?>" size="10" readOnly  style="text-align:right; font-weight:bold;border:#FFFFFF none"></td>
          </tr>
        </table></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="7"> 
      <td colspan="2"> <font size="2"><strong> 
        <input type="checkbox" value='update' id='updatecost' name='updatecost' <?= ($updatecost== 'update'?'checked':'');?>>
        <font size="1">Update Cost</font></strong></font></td>
    </tr>
  </table>
</form>
<div id="wordlist" style="position:absolute; border:thin dotted #3300FF; visibility:none; background: #CCCCCC; width:300px; top=0px"></div>
<?
	include_once('xajax_popup.php');

	
if ($focus != '')
{
	echo "<script>document.getElementById('$focus').focus()</script>";
}
else
{
	echo "<script>document.getElementById('searchkey').focus()</script>";
}

if ($p1 == 'poreturn' && $id !='')
{
	echo "<script>xajax_porCheckRR(xajax.getFormValues('f1'))</script>";
}

if($p1 == "Print"){
?>
<div align="center">
	<iframe id="JOframe" name="JOframe" style="background-color:#FFF; margin:auto;" frameborder="0" width="90%" height="500" src="print_poreturn.php?id=<?=$aPOR['por_header_id']?>"></iframe><br />
    <input type="button" value="Print" onClick="printIframe('JOframe');" />
</div>	
<? } ?>
<script>
	xajax_porLoad(xajax.getFormValues('f1'));
</script>
<?
if( $_REQUEST['b'] == "Commit" ){
	#POST TO GL
	$admin_id 			= $ADMIN['admin_id'];
	$ip					= $_SERVER['REMOTE_ADDR'];
	$time 				= date("H:i:s");
	$date				= $aPOR['date'];
	$por_header_id_pad 	= str_pad($aPOR['por_header_id'],7,0,STR_PAD_LEFT);
	$particulars 		= "POR:$por_header_id";
	$journal_id			= 4; #PURCHASE JOURNAL
	$account_id			= $aPOR['account_id'];
	@pg_query("
		insert into gltran_header (xrefer,date,particulars,journal_id,account_id,admin_id,status,pcenter_id,time,ip) values
		('$rr_header_id_pad','$date','$particulars','$journal_id','$account_id','$admin_id','S','1','$time','$ip')
	") or message1(pg_errormessage().$q);
	$por_header_id	= $aPOR['por_header_id'];
	$gltran_header_id = @db_insert_id('gltran_header');		
	#$gltran_header_id = @pg_query("SELECT lastval()");
	#CREDIT - INVENTORY
	#	25 - MERCHANDISE INVENTORY - EAST CENTRE
	#	23 - MERCHANDISE INVENTORY - ARANETA CENTRE
	$net_amount 	= $aPOR['net_amount'];
	
	insertGL($gltran_header_id,25,0,$net_amount,'POR',$por_header_id);
	
	#DEBIT - PURCHASE RETURNS AND ALLOWANCES
	# 	230 - PURCHASE RETURNS AND ALLOWANCES - EAST CENTRE
	# 	228 - PURCHASE RETURNS AND ALLOWANCES - ARANETA
	insertGL($gltran_header_id,230,$net_amount,0,'POR',$por_header_id);
	
	#CREDIT - PURCHASES
	#	108 - 501D PURCHASES VAT EAST CENTRE
	#	111 - 502D PURCHASES NON-VAT EAST CENTRE
	
	#	106 - PURCHASES VAT ARANETA CENTRE
	#	109 - PURCAHSES NON-VAT ARANETA CENTRE
	if($aPOR['tax_add'] > 0){
		#WITH TAX	
		insertGL($gltran_header_id,108,0,$net_amount,'POR',$por_header_id);
	}else{
		#NON TAX
		insertGL($gltran_header_id,111,0,$net_amount,'POR',$por_header_id);
	}
	
	#DEBIT - GAIN/LOSS
	#	70 - PROFIT AND LOSS-ARANETA
	#	73 - PROFIT AND LOSS-EAST CENTRE
	insertGL($gltran_header_id,73,$net_amount,0,'POR',$por_header_id);
}
?>