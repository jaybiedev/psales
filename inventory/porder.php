<?
require_once(dirname(__FILE__).'/../lib/lib.salvio.php');
require_once(dirname(__FILE__).'/../lib/lib.inventory.php');
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

function vSubmit(ul)
{
	if (ul.name == 'Cancel')
	{
		if (confirm("Are you sure to CANCEL Purchase Order?"))
		{
			document.f1.action="?p=porder&p1=CancelConfirm"
		}	
		else
		{
			document.f1.action="?p=porder&p1=Cancel"
		}
	}
	else
	{
		document.f1.action="?p=porder&p1="+ul.id;
	}	
}
function vUnit(t)
{
	if (t.value=='')
	{
	  alert('Invalid Unit Selected');
	  return false;
	}
	else
	{
		var afrac = f1.afraction.value.split(';');
		if (t.value == 1)
		{
			f1.fraction.value=afrac[0];
			f1.price.value = f1.cost1.value;
		}	
		else if (t.value == 2)
		{
			f1.fraction.value=afrac[1];
			f1.price.value = f1.cost2.value;
		}	
		else if (t.value == 3)
		{
			f1.fraction.value=afrac[2];
			f1.price.value = f1.cost3.value;
		}	
		else
			alert('Invalid Unit Selected');
	}
	var x=vCompute()
	
}
function negCheck()
{
	if (document.f1.qty.value<0)
	{
		alert("negative value not allowed");
		document.f1.qty.focus();
	}
	else
	{
		vCompute();
	}

	return false;
}
function vCompute()
{
	amt =parseFloat(document.getElementById('unit_qty').value*document.getElementById('cost1').value) + 
			parseFloat(document.getElementById('case_qty').value*document.getElementById('cost1').value*document.getElementById('fraction3').value);
	document.getElementById('amount').value=twoDecimals(amt);
	return false;
}
function vDisc(discount)
{
//	f1.discount_amount.value =  ((f1.gross_amount.value*discount.value/100))
//	f1.net_amount.value = (f1.gross_amount.value - f1.discount_amount.value)

	f1.discount_amount.value =  twoDecimals((f1.gross_amount.value*discount.value)/100)
	f1.net_amount.value = twoDecimals(f1.gross_amount.value*1 - f1.discount_amount.value*1)
	
	f1.discount_amount2.value = f1.discount_amount.value
	f1.net_amount2.value = f1.net_amount.value
}
-->
</script>
<STYLE TYPE="text/css">
<!--
	.altText {
	background-color: #FFFFFF;
	font-family: verdana;
	border: #CCCCCC 1px solid;
	font-size: 12px;
	color: #000000
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

if (!chkRights2('porder','madd',$ADMIN['admin_id']))
{
	message('You have [ NO ] permission to access this area...');
	exit;
}
//include_once("lib/lib.css.php");
if (!session_is_registered("aPO"))
{
	session_register("aPO");
	$aPO=null;
	$aPO=array();
}

if (!session_is_registered("aPOD"))
{
	session_register("aPOD");
	$aPOD=null;
	$aPOD=array();
}
if (!session_is_registered("iPOD"))
{
	session_register("iPOD");
	$iPOD=null;
	$iPOD=array();
}

function genReference()
{
	global $aPO;
	$q = "select * from invoice where ip='PORDER'";
	$qr = @query($q) or message(db_error());
	$r = @fetch_object($qr);

	$sn = $r->invoice+1;
	$reference = date('Y-m').date('d').'-'.str_pad($sn,4,'0',STR_PAD_LEFT);
	$aPO['reference'] = $reference;
	
	$q = "update invoice set invoice='$sn' where ip='PORDER'";
	$qr = @query($q);
}

function isFinished($po_header_id){
	$result = @pg_query("select * from po_header where po_header_id = '$po_header_id'");
	$r = @pg_fetch_assoc($result);
	$finished = $r['finished'];
	return $finished;
}

$p1 = $_REQUEST['p1'];

if ($p1 == ''  && $b1 == '...') $p1='...';

$fields_header = array('date',  'gross_amount', 'disc1', 'disc1_type','disc2', 'disc2_type','disc3', 'disc3_type','tax_add', 'tax_amount',
						'net_amount','discount_amount','account_id','account','freight_amount','freight_add','remark', 'reference','tender_id','category_from','category_to');


$fields_detail = array('case_qty','cost1','unit_qty', 'cost2','cost3' ,'fraction2' , 'fraction3', 'amount','freight_case', 'stock');
$dfld = array('stock_id','barcode','case_qty','unit_qty','cost1', 'cost2', 'cost3' ,'amount');

if( $p1 == "Close PO" ){
	@pg_query("update po_header set status = 'D' where po_header_id = '".$aPO['po_header_id']."'");	
	$aPO['status'] = "D";
}

if( $_REQUEST['b'] == "Commit" ){
	@pg_query("update po_header set finished='1' where po_header_id = '".$aPO['po_header_id']."'");	
}
if (!in_array($p1, array(null,'Edit','Delete','Print','Load','Serve')))
{

	for ($c=0;$c<count($fields_header);$c++)
	{
		$aPO[$fields_header[$c]] = $_REQUEST[$fields_header[$c]];
		if ($fields_header[$c] == 'date' || $fields_header[$c]=='date_released'|| $fields_header[$c]=='checkdate')
		{
			$aPO[$fields_header[$c]] = mdy2ymd($_REQUEST[$fields_header[$c]]);
		}
		else
		{
			if ($aPO[$fields_header[$c]] == '' && !in_array($fields_header[$c], array('account','remark','invoice','category_from','category_to')))
			{
				$aPO[$fields_header[$c]] = '0';
			}
		}
	}
	
	$aPO['gross_amount'] = str_replace(',','',$aPO['gross_amount']);
	$aPO['discount_amount'] = str_replace(',','',$aPO['discount_amount']);
	$aPO['net_amount'] = str_replace(',','',$aPO['net_amount']);


	$aPO['admin_id']=$ADMIN['admin_id'];
	for ($c=0;$c<count($fields_detail);$c++)
	{
		$iPOD[$fields_detail[$c]] = $_REQUEST[$fields_detail[$c]];
		if ($iPOD[$fields_detail[$c]] == '' && !in_array($fields_detail[$c], array('stock')))
		{
			$iPOD[$fields_detail[$c]] = '0';
		}
	}
	if ($iPOD['fraction3'] == '') $iPOD['fraction3'] = 1;
	$iPOD['qty1'] = $iPOD['unit_qty'] + $iPOD['case_qty'] * $iPOD['fraction3'] ;
	
	if ($aPO['account_id'] != '')
	{
		$q = "select terms, credit_limit , address, account , account_code, cardno from account where account_id = '".$aPO['account_id']."'";
		$qr = @pg_query($q) or message(db_error());
		$r = @pg_fetch_assoc($qr);
		if ($r)
		{
			if ($aPO['terms'] == '')
			{
				$aPO['terms'] = $r['terms'];
			}
			$aPO['credit_limit'] = $r['credit_limit'];
			$aPO['address'] = $r['address'];
			$aPO['city'] = $r['city'];
			$aPO['account'] = $r['account'];
			$aPO['account_code'] = $r['account_code'];
		}	
	}	
}

if ($aPO['date'] =='' || $aPO['date'] =='//')
{
	$aPO['date'] = date('Y-m-d');
}

if ($p1 == 'Search' && $searchkey*1>0)
{
	$q = "select * from stock where barcode='$searchkey'";
	
	$qr = @query($q) or message(db_error());
	if (pg_num_rows($qr)>0)
	{
		$r = @pg_fetch_assoc($qr);
		
		if ($r['stock_description'] != '')
		{
			$r['stock'] = $r['stock_description'];
		}
		$iPOD = $r;
		
		if ($iPOD['fraction2'] == 0 || $iPOD['fraction2'] == '') $iPOD['fraction2'] =1 ;
		if ($iPOD['fraction3'] == 0 || $iPOD['fraction3'] == '') $iPOD['fraction3'] =1 ;
		$iPOD['afraction'] = '1;'.$iPOD['fraction2'].';'.$iPOD['fraction3'];
		
		$iPOD['fraction'] = 1;
		$iPOD['price'] = $iPOD['cost1'];
		$iPOD['unit'] = 1;
		
		$p1 = 'searchFound';
		$searchkey = $iPOD['barcode'];
		$focus = 'case_qty';
		if ($r->account_id != $aPO['account_id'])
		{
			message1("Item Belongs to a Different Supplier...");
		}
	}
	else
	{
		$q = "select * from stock where upper(barcode)='".strtoupper($searchkey)."'";
		$qr = @query($q) or message(db_error());
		if (pg_num_rows($qr)>0)
		{
			$r = @pg_fetch_assoc($qr);
			if ($r['stock_description'] != '')
			{
				$r['stock'] = $r['stock_description'];
			}
			$iPOD = $r;
			if ($iPOD['fraction2'] == 0 || $iPOD['fraction2'] == '') $iPOD['fraction2'] =1 ;
			if ($iPOD['fraction3'] == 0 || $iPOD['fraction3'] == '') $iPOD['fraction3'] =1 ;
			$iPOD['afraction'] = '1;'.$iPOD['fraction2'].';'.$iPOD['fraction3'];
			
			$iPOD['fraction'] = 1;
			$iPOD['price'] = $iPOD['cost1'];
			$iPOD['unit'] = 1;
			$searchkey = $iPOD['barcode'];
			$focus = 'case_qty';
			$p1 = 'searchFound';
			$focus='case_qty';
		}
	}
}

if ($p1 =='' && in_array($aPO['tender_id'], array('3')))
{
	//update credit limit on refresh
	
}
if ($p1=='Browse')
{
	echo "<script>window.location='?p=porder.browse&p1=Browse'</script>";
}
elseif ($p1 == '...')
{
  $q = "select * from account where account_code = '$account'  and  account_type_id in ('1','8')  and enable='Y'";
  $qr = @pg_query($q) or message1(pg_errormessage());
  if (@pg_num_rows($qr) > 0)
  {
  	$r = @pg_fetch_object($qr);
	$aPO['account_id'] = $r->account_id;
	$aPO['account'] = $r->account;
	$aPO['terms'] = $r->terms;
	$p1= '...done';
  }

}
elseif ($p1 == 'Gen')
{
	genReference();
}
elseif ($p1 == 'Send')
{
	include_once('mail.func.php');
	echo $email_subject;
	echo "message : ".$email_message;
	mail_attachment("$email_to", "jay_565@yahoo.com", "$email_subject", "$email_message", 'mail.php');
}
elseif ($p1 == 'Load' && $id == '')
{
	message("Nothing to Load...");
}
elseif ($p1 == 'Load')
{
	$aPO=null;
	$aPO=array();
	$aPOD= null;
	$aPOD = array();
	$q = "select * from po_header where po_header_id='$id'";
	$qr = @query($q) or message(db_error());
	if ($qr)
	{
		$qd = "select * from po_detail where po_header_id='$id'";
		$qdr = @query($qd) or message(db_error());
		while ($r = @pg_fetch_assoc($qdr))
		{
			$temp = $r;
			$qs = "select stock, stock_description,stock_code, barcode, unit1, unit2, unit3, fraction2, fraction3
					 from 
						stock where stock_id='".$r['stock_id']."'";

			$qrs = @query($qs) or message(db_error());
			$rs = @pg_fetch_assoc($qrs);
			if ($rs)
			{
				if ($rs['stock_description'] != '')
				{
					$rs['stock'] = $rs['stock_description'];
				}

				$temp  += $rs;
			}	
			$aPOD[]=$temp;
		}
		
		$r = @pg_fetch_assoc($qr);
		$aPO= $r;
		
		if ($aPO['account_id'] > 0)
		{
			$q = "select * from account where account_id='".$aPO['account_id']."'";
			$qra = @pg_query($q) or message1(pg_errormessage());
			$r = fetch_object($qra);
			$aPO['account'] = $r->account;
			$aPO['credit_limit'] = $r->credit_limit;
			$aPO['city'] = $r->city;
			$aPO['address'] = $r->address;
			$aPO['account_code'] = $r->account_code;
		}	
	}
}
elseif ($p1 == 'New' or $p1 == 'Add New')
{
	$aPO=null;
	$aPO=array();
	$aPOD= null;
	$aPOD = array();
	$iPOD= null;
	$iPOD = array();
	$aPO['date'] = date('Y-m-d');
}
elseif (in_array($p1,array('Edit','Ok','Save','Update','Search','selectStock','Release') )&& $aPO['status'] == 'A')
{
	message('Editing not allowed. Purchase Order already Released/Posted');
}
elseif ($p1 == 'selectStock' && $id != '')
{
	$q = "select 
			stock_id, 
			stock_code, 
			barcode,
			stock,
			stock_description,
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
	if ($r['stock_description'] != '')
	{
		$r['stock'] = $r['stock_description'];
	}
	$iPOD = $r;
	if ($iPOD['fraction2'] == 0 || $iPOD['fraction2'] == '') $iPOD['fraction2'] =1 ;
	if ($iPOD['fraction3'] == 0 || $iPOD['fraction3'] == '') $iPOD['fraction3'] =1 ;
	$iPOD['afraction'] = '1;'.$iPOD['fraction2'].';'.$iPOD['fraction3'];
	
	$iPOD['fraction'] = 1;
	$iPOD['price'] = $iPOD['cost1'];
	$iPOD['unit'] = 1;
	$searchkey = $iPOD['barcode'];
	$focus = 'case_qty';
}
elseif ($p1 == 'Edit' && $id!='')
{
	$iPOD = null;
	$iPOD = array();
	$c=0;
	foreach ($aPOD as $temp)
	{
		$c++;
		if ($id == $c)
		{
			$iPOD = $temp;
			$iPOD['line_ctr'] = $c;
			$searchkey=$temp['barcode'];
			break;
		}
		
	}
	$focus = 'case_qty';
}
//elseif ($p1 == 'Ok' && ($iPOD['stock_id']=='' or (intval($iPOD['unit_qty'])== 0  && intval($iPOD['case_qty'])== 0)))
//{
//	message("Check: Stock Item Selected and Quantity ..");
//	$aPO['status']='M';
//}
elseif ($p1 == 'Ok'  && ($iPOD['stock_id']=='' || $iPOD['barcode'] == ''))
{
	message("[ No Product Specified...]");
}
elseif ($p1 == 'Ok' && !in_array($aPO['status'],array('','S')))
{
	message1("<br>[ Purchase Order is ".status($aPO['status']).". Editing NOT allowed...]<br>");
}
elseif ($p1 == 'Ok') // && $iPOD['stock_id']!='')
{

	#$aPO['status']='M';
	$dummy = null;
	$dummy = array();
	if ($iPOD['line_ctr']  > 0)
	{
			$dummy = $aPOD[$iPOD['line_ctr'] - 1];
			$dummy['case_qty'] = $iPOD['case_qty'];
			$dummy['unit_qty'] = $iPOD['unit_qty'];
			$dummy['cost1'] = $iPOD['cost1'];
			$dummy['cost2'] = $iPOD['cost2'];
			$dummy['cost3'] = $iPOD['cost3'];
			$dummy['amount'] = $iPOD['amount'];
			$dummy['stock'] = $iPOD['stock'];
			$dummy['qty1'] = $iPOD['qty1'];
			$aPOD[$iPOD['line_ctr'] - 1] = $dummy;
	}
	else
	{
		$fnd=$cc=0;
		foreach ($aPOD as $temp)
		{
			if ($temp['stock_id'] == $iPOD['stock_id'])
			{
				$dummy = $temp;

				$dummy['case_qty'] = $iPOD['case_qty'];
				$dummy['unit_qty'] = $iPOD['unit_qty'];
				$dummy['cost1'] = $iPOD['cost1'];
				$dummy['cost2'] = $iPOD['cost2'];
				$dummy['cost3'] = $iPOD['cost3'];
				$dummy['amount'] = $iPOD['amount'];
				$dummy['stock'] = $iPOD['stock'];
				$dummy['qty1'] = $iPOD['qty1'];
				
				$aPOD[$cc] = $dummy;
				$fnd=1;
				break;
			}
			$cc++;
		}
		if ($fnd == '0')
		{
			$aPOD[] = $iPOD;
		}
	}
	$iPOD = null;
	$iPOD = array();
	$searchkey='';
}
elseif ($p1 == 'Delete Checked' && count($aChk)>0)
{
	$newarray=null;
	$newarray=array();
	$nctr=0;
	foreach ($aPOD as $temp)
	{
		$nctr++;
		if (in_array($nctr,$aChk))
		{
			$stockledger_id='';
			if ($temp['po_detail_id'] != '')
			{
					$qr = @query("delete from po_detail where po_detail_id='".$temp['po_detail_id']."'") or message(db_error());
					if (pg_affected_rows($qr)>0)
					{
								$deleteok=true;
					}
					else
					{
						$deleteok=false;
						message("FATAL error: Was not able to delete from Purchase Order detail file...".mysql_error($qr));
					}			
			}
		}
		else
		{
			$newarray[]=$temp;
		}
	}
	$aPOD = $newarray;	
}
elseif ($p1 == 'Save' && !in_array($aPO['status'],array(null,'M','S','P')))
{
	message1("<br>[ Cannot Update Purchase Order. Transaction is ".status($aPO['status']).". ] <br>");
}
elseif ($p1 == 'Save' && count($aPOD) == 0)
{
	message("No items to save...");
}
elseif ($p1 == 'Save'  && $aPO['account_id'] == '')
{
	message("No Supplier Specified.  Please check and Save again...");
}
elseif ($p1 == 'Save')
{

	if ($aPO['reference'] == '')
	{
		genReference();
	}
	if ($aPO['po_header_id'] == '')
	{
		$aPO['audit'] = 'Encoded by '.$ADMIN['name'].' on '.date('m/d/Y g:ia').'; ';

		query("begin transaction");
		$time = date('G:i');
		$q = "insert into po_header ( time, admin_id, ip ";
		$qq .= ") values ('$time', '".$ADMIN['admin_id']."', '".$_SERVER['REMOTE_ADDR']."'";
		for ($c=0;$c<count($fields_header);$c++)
		{
			if ($fields_header[$c] == 'account') continue;

			$q .= ",".$fields_header[$c];
			$qq .= ",'".$aPO[$fields_header[$c]]."'";
		}
		$q .= $qq.")";
		$qr = @query($q) or message(db_error());

		if ($qr && pg_affected_rows($qr)>0)
		{
			$ok=1;
			$aPO['po_header_id'] = db_insert_id('po_header');

			// insert to si detail
			$c=0;
			foreach ($aPOD as $temp)
			{
				$q = "insert into po_detail (po_header_id";
				$qq = ") values ('".$aPO['po_header_id']."'";
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
					$dummy['po_detail_id'] = db_insert_id('po_detail');
					$aPOD[$c]=$dummy;
				}
				$c++;
			}	
			if ($ok)
			{
				query("commit");
				$aPO['status']='S';
				message(" Purchase Order Saved...");
			}
			else
			{
				query("rollback transaction");
				message1("Problem Adding To Purchase Order Details...".db_error().$q);
				$aPO['status']='S';
				$aPO['po_header_id']='';
			}
		}
		else
		{
			message("Cannot Add Record To Purchase Order Header File...");
			query("rollback transaction");
		}
							
	}
	else
	{
		$ok=true;
		$aPO['audit'] = $aPO['audit'].'Updated by '.$ADMIN['name'].' on '.date('m/d/Y g:ia').'; ';
	
		query("begin");	
		$q = "update po_header set po_header_id = '".$aPO['po_header_id']."'";
		for ($c=0;$c<count($fields_header);$c++)
		{
			if ($fields_header[$c] == 'account') continue;

			$q .= ",".$fields_header[$c]."='".$aPO[$fields_header[$c]]."'";
		}
		$q .= " where po_header_id = '".$aPO['po_header_id']."'";

		$qr = @query($q) or message(db_error());
		if ($qr)
		{
			$c=0;
			foreach ($aPOD as $temp)
			{
				if ($temp['po_detail_id'] == '')
				{
					$q = "insert into po_detail (po_header_id";
					$qq = ") values ('".$aPO['po_header_id']."'";
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
					$q = "update po_detail set po_header_id='".$aPO['po_header_id']."'";
					for ($i=0;$i<count($dfld);$i++)
					{
						$q .= ",".$dfld[$i]."='".addslashes($temp[$dfld[$i]])."'";
					}
					$q .= "	where po_detail_id='".$temp['po_detail_id']."'";
					$qr = @query($q) or message(db_error().$q);
				}	
				if (!$qr)
				{
					$ok=false;
					break;
				}
				else
				{
					if ($temp['po_detail_id'] == '')
					{
						$dummy=$temp;
						$dummy['po_detail_id'] = db_insert_id('po_detail');
						$aPOD[$c]=$dummy;
					}
				}
				$c++;
			}	
			if ($ok)
			{
				query("commit");
				message(" Purchase Order Updated...");
				$aPO['status']='S';
			}
			else
			{
				query("rollback");
				message("Problem Updating To Purchase Order Details...".db_error());
			}
		}
		else
		{
			message("Cannot Modify Record To Purchase Order Header File...".db_error().$q);
			query("rollback");
		}
					
	}
	
}
/*elseif ($p1 == 'Print' && !in_array($aPO['status'], array('A','P','S')))
{
	message("Cannot Print. <b>Save</b> Purchase Order Before Printing...");
}*/
elseif ($p1 == 'Printxx')
{
	echo "<div id='layer.print' position='a'>";
	include_once('porder.print.php');
	echo "</div>";
}
/*elseif ($p1 == 'Print')
{
	//Purchase Order
	$q  = "";
	
	$q .= adjustSize(strtoupper($SYSCONF['BUSINESS_NAME']),50)." PO No.:".str_pad($aPO['po_header_id'],8,'0',STR_PAD_LEFT)."\n";
	$q .= adjustSize($SYSCONF['BUSINESS_ADDR'],50)." Date  :".ymd2mdy($aPO['date'])."\n\n";
		
	$s = "select * from account where account_id='".$aPO['account_id']."'";
	$qs = @query($s) or message(db_error().$s);
	$sr = @fetch_object($qs);
	
	$q .= adjustSize('['.$sr->account_code.'] '.$sr->account,60).' '."Terms    :".$aPO['terms']."\n";
	$q .= adjustSize($sr->address,60).' '."Discount : ".$discount."\n";
	$q .= adjustSize($sr->city,43)."\n";

	$q .= str_repeat('-',80)."\n";
	$q .= "Ordered Quantity   Item Code          Item Description                          \n";
	$q .= str_repeat('-',80)."\n";
	$c=0;

	foreach ($aPOD as $temp)
	{
		$c++;

		if ($temp['case_qty'] > '0')
		{
			$q .= 	adjustRight(number_format2($temp['case_qty'],0),8).' '.adjustSize($temp['unit3'],3).' ';
		}
		if ($temp['unit_qty'] > '0')
		{
			$q .= adjustRight(number_format2($temp['unit_qty'],2),8).' '.adjustSize($temp['unit1'],3);
		}
		$q .=  ' '.adjustSize($temp['barcode'],16).' '.
				adjustSize(substr($temp['stock'],0,40),37).' ';
				
		
		$q .= "\n";
	}
	$q .= str_repeat('-',80)."\n";

	$q .= "\n\n";
	$q .= adjustSize($ADMIN['name'],25).
		space(2).str_repeat('_',23).
		space(2).str_repeat('_',23)."\n";
		
	$q .= adjustSize('Prepared by:',25).
		space(2).adjustSize('Checked by:',23).
		space(2).adjustSize('Approved by:',23)."\n".
		adjustSize(date('m/d/Y g:ia'),25);
		
	$q .= "\n\n\n\n";
	
//	echo "<pre>$q</pre>";
	if ($SYSCONF['REPORT_PRINTER_TYPE'] != 'GRAPHICS')
	{
				nPrinter($q, $SYSCONF['REPORT_PRINTER_TYPE'], $SYSCONF['REPORT_PRINTER_DEST']);
	}
	else
	{
	 	 echo "<input type='hidden' id='print_area' name='print_area' value=".'"'.$q.'"'.">";
		echo "<iframe name='printit' id='printit' style='width:0px;height:0px;'>";
		echo "</iframe>";
		echo "<script>printIframe(print_area)</script>";
	}
}*/
elseif ($p1=='CancelConfirm' && !chkRights2('porder','mdelete',$ADMIN['admin_id']))
{
	message('You have [ NO ] permission to cancel sales transaction...');
}
elseif ($p1=='CancelConfirm')
{
	//begin();
	$ok=true;
	@query("select * from po_header where  po_header_id='".$aPO['po_header_id']."' for update");
	
	$q = "update po_header set status='C' where po_header_id='".$aPO['po_header_id']."'";
	$qr = query($q) or message("Cannot Cancel Purchase Order ".db_error());	
	if (!$qr)
	{
		$ok=false;
	}
	if ($ok)
	{
		commit();
		$aPO['status']='C';
		message(' Stocks Purchase Order No. ['.str_pad($aPO['po_header_id'],8,'0',STR_PAD_LEFT).'] Successfully CANCELLED');
	}
	else
	{
		message('Problem deleting stock ledger data...'.db_error());
	}
}
elseif ($p1 == 'Create' && $aPO['account_id']== '')  //Auto PO
{
	message1("No Supplier Account Specified...");
}
elseif ($p1 == 'Create')
{
	if ($aPO['po_header_id'] == '') {
		$aPOD=null;
		$aPOD=array();
	}

	@include_once('stockbalance.php');
	
	$now = date("Y-m-d");
	$arr_balance = inventory::getStockBalanceFromCategory($aPO['account_id'], $aPO['category_from'],$aPO['category_to'], $now);


	foreach ( $arr_balance as $arr ) {

		$r = lib::getTableAttributes("select * from stock where stock_id = '$arr[stock_id]'");
		/*balance_qty unit is in pieces or in unit1*/
		$balance_qty = $arr['in_qty'] - $arr['out_qty'];

		#if balance qty is negative then balance qty should be equal to zero
		if( $balance_qty <= 0 ) $balance_qty = 0;
		
		if ($r['fraction3'] == 0) {
			$r['fraction3'] = 1;
		}

		$balance_case = intval($balance_qty /$r['fraction3']);	
		/*reorder level is in case*/
		
		#echo "$r[stock_id] ; Balance Qty :  $balance_qty ; Balance Case : $balance_case <br>";

		/*do not order if there is no reorder level*/
		if(  empty($r['reorder_level'])  || $r['reorder_level'] <= 0 ) continue;
		
		/*CASE QUANTITY THAT SHOULD BE ORDERED*/
		$case_qty = $r['reorder_level'] - $balance_case;
	
		/*do not order when balance is greater than reorder*/
		if( $balance_case >= $r['reorder_level'] ) continue;

		#echo "Case Quantity: $r[reorder_level] - $balance_case = $case_qty <br>";

		// -- check undelivered PO

		/*ADD SOMETHING IN DETAIL TO CLOSE THE PO.
		get po that are not cancelled, served, or closed*/

		$qp = "
			select 
				po_header.po_header_id,
				case_qty as case_qty,
				unit_qty as unit_qty
			from 
				po_header,
				po_detail
			where
				po_header.po_header_id = po_detail.po_header_id 
				and po_header.status not in ('C','D','E') and
				po_detail.stock_id = '".$r['stock_id']."'
		"; 		

		$t_po_case = $t_po_unit = 0;
		$qpr = pg_query($qp) or message(pg_errormessage().$qp);

		$arr_po = array();
		while( $rp = pg_fetch_object($qpr) ){			
			$arr_po[] = $rp->po_header_id;
			$t_po_case += $rp->case_qty;
			$t_po_unit += $rp->unit_qty;
		}		
		$undelivered_case = intval($t_po_case + $t_po_unit / $r['fraction3']);

		/*echo "PO : $undelivered_case <br>";*/

		/*echo "<pre>".print_r($arr_po)."</pre>";*/
	
		$case_qty -= $undelivered_case;
		
		/*check received items here*/
		if( count($arr_po) ):
			$sql =  "
				select 
					h.rr_header_id
				from
					rr_header as h
					inner join rr_detail as d on h.rr_header_id = d.rr_header_id
					and status != 'C'
				where 
					d.stock_id = '$r[stock_id]'
			";
			if( count($arr_po) ) $sql .= " and h.po_header_id in (".implode(',', $arr_po).")";
			$sql .= " order by h.rr_header_id asc";
			$a_id = lib::getArrayDetails($sql);	

			/*add quantity of received items*/
			$sql =  "
				select 
					sum(case_qty) as case_qty,
					sum(unit_qty) as unit_qty			
				from
					rr_header as h
					inner join rr_detail as d on h.rr_header_id = d.rr_header_id
					and status != 'C'
				where 
					d.stock_id = '$r[stock_id]'
			";
			if( count($arr_po) ) $sql .= " and h.po_header_id in (".implode(',', $arr_po).")";		

			$a_rr = lib::getTableAttributes($sql);

			$rr_case_qty = $a_rr['case_qty'];
			$rr_unit_qyt = $a_rr['unit_qty'];

			$received_case = intval($rr_case_qty + $rr_unit_qyt / $r['fraction3']);

			$case_qty += $received_case;
			
		endif;
		/*end check of received items here*/

		#echo "Received : $received_case ; RR#: $a_rr[rr_header_id] <br>";
									
		/*continue if more than reorder level*/
		if ($case_qty <= 0) continue;
		
		/*
		if ($case_qty > intval($case_qty)) {
			$case_qty++;
		}
		*/

		$r['cost1'] = $r['cost1']*1;
		$r['cost2'] = $r['cost2']*1;
		$r['cost3'] = $r['cost3']*1;

		if ($r['fraction3'] > 1) {
			$r['unit'] = ($r['unit3'] == '' ? 'CS' : $r['unit3']);
		} else {
			$r['unit'] = $r['unit1'];
		}

		if ($r['cost3'] == '0') {
			$r['cost3'] = $r['cost1']*$r['fraction3'];
		}

		$r['case_qty']     = round($case_qty,2);
		$r['unit_qty']     = 0;
		
		$r['freight_case'] = $r['addfreight_case'] * $r['case_qty'];
		$r['amount']       = $case_qty*$r['cost3'] + $r['freight_case'];
		
		if ($r['stock_description'] != '') {
			$r['stock'] = $r['stock_description'];
		}

		$aPOD[] = $r;

	//	break;
	}
	if (count($aPOD) == 0) message("NO Purchase Order Generated...");

}
?><body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
 <form action="?p=porder" method="post" name="f1" id="f1" style="margin:0">

  <table width="97%" border="0" align="center" cellpadding="1" cellspacing="1">
    <tr> 
      <td colspan="8">Search 
        <input name="xSearch" type="text" class="altText" id="xSearch2" value="<?= $xSearch;?>">
      <?= lookUpAssoc('searchby',array('PO No.'=>'reference','Rec Id'=>'po_header_id','Supplier'=>'account.account','Supplier Code'=>'account.account_code','Remarks'=>'remarks','Stock description'=>'stock_description'), $searchby);?>
      <?= lookUpAssoc('show',array('All'=>'','Saved'=>"'S','P'",'Partial Invoiced'=>"'A'",'Invoiced/Served'=>"'I'",'Cancelled'=>"'C'"), stripslashes($show));?>
      <input name="p122" type="button" class="altBtn" id="p122" value="Go" onClick="window.location='?p=porder.browse&p1=Go&xSearch='+xSearch.value+'&searchby='+searchby.value"> 
        <input type="submit" class="altBtn" name="p1" value="Add New"> 
        <input name="p12" type="button" class="altBtn" id="p12" value="Browse" onClick="window.location='?p=porder.browse&p1=Browse'"> 
		<!--<input type="button" class="altBtn" name="Submit232" value="Close" onClick="window.location='?p='"> -->
        
        <? 
		#IF STATUS IS NOT EQUAL TO CLOSED, SERVED, OR CANCELLED
		#if( $aPO['status'] != "D" && $aPO['status'] != "E" && $aPO['status'] != "C" ){
		if( $aPO['status'] == "T"){
			#echo $aPO['status'];
		?>
        <input type="submit" class="altBtn" name="p1" value="Close PO"> 
        <? } ?>
        <?  if(!isFinished($aPO['po_header_id'])) { ?>
        <input type="submit" class="altBtn" name="b" value="Commit"> 
        <? } ?>
        </td>
    </tr>
    <tr bgcolor="#CCCCCC"> 
      <td height="19" colspan="6"> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"><img src="../graphics/waiting.gif" width="16" height="16"><strong> 
        Purchase Order Entry</strong></font></td>
      <td height="19" colspan="2" align="center"> <font size="2" face="Times New Roman, Times, serif"> 
        <em> 
        <?  if(isFinished($aPO['po_header_id'])) { ?>
        	<em>(Committed)</em>
        <? } ?>
        <?= status($aPO['status']);?>
        </em></font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td width="93" height="24" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Type 
        </font></td>
      <td width="166" nowrap > <select name='type' tabindex="1"  onKeypress="if(event.keyCode==13) {document.getElementById('account').focus();return false;}">
          <option value='A'  <?= (($aPO['type'] =='A') ? 'selected' : '');?>>On 
          Account</option>
          <option value='C'  <?= (($aPO['type'] =='C') ? 'selected' : '');?>>Cash</option>
        </select> <input name="account_id" type="hidden" id="account_id" value="<?= $aPO['account_id'];?>" size="5" maxlength="30"> 
      </td>
      <td width="167" nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Disc1</font></td>
      <td width="334" nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="disc1" type="text" class="altText" id="disc1" value="<?=$aPO['disc1'];?>" size="5"  tabindex="4"  style="text-align:right"   onKeypress="if(event.keyCode==13) {document.getElementById('disc2').focus();return false;}" >
        <?= lookUpAssoc('disc1_type',array('Amount'=>'A','Percent'=>'P'), $aPO['disc1_type']);?>
        </font></td>
      <td width="56"  nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Add-OnFreight</font></td>
      <td width="143"  nowrap ><input name="freight_add" type="text" class="altText" id="freight_add" value="<?= $aPO['freight_add'];?>" size="10"  tabindex="4"  style="text-align:right"   onKeypress="if(event.keyCode==13) {document.getElementById('tax_add').focus();return false;}"> 
      </td>
      <td width="63" nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif">PO#</font><font size="2" face="Verdana, Arial, Helvetica, sans-serif">.&nbsp; 
        </font></td>
      <td width="277"  nowrap > <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <strong> 
        <?= str_pad($aPO['po_header_id'],8,'0',STR_PAD_LEFT);?>
        </strong></font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td valign="top"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Supplier</font></td>
      <td nowrap > <input name="account" type="text" class="altText" id="account" value="<?=stripslashes( $aPO['account']);?>" size="30"  onChange="b1.click()"  tabindex="2"   onKeypress="if(event.keyCode==13) {document.getElementById('b1').focus();return false;}"> 
        <input name="account_code"  readOnly type="text" class="altText" id="account_code" value="<?=stripslashes( $aPO['account_code']);?>" size="5"  onChange="b1.click()"  tabindex="2"   onKeypress="if(event.keyCode==13) {document.getElementById('b1').focus();return false;}"> 
        <input name="b1" type="button" class="altBtn" id="b1" value="..." onClick="xajax_porder_searchaccount(xajax.getFormValues('f1'));"> 
      </td>
      <td nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Disc2</font></td>
      <td nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="disc2" type="text" class="altText" id="disc2" value="<?= $aPO['disc2'];?>" size="5"  tabindex="4"  style="text-align:right"  onKeypress="if(event.keyCode==13) {document.getElementById('disc3').focus();return false;}" >
        <?= lookUpAssoc('disc2_type',array('Amount'=>'A','Percent'=>'P'), $aPO['disc2_type']);?>
        </font></td>
      <td nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif">%Add-On 
        Tax</font></td>
      <td nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="tax_add" type="text" class="altText" id="tax_add" value="<?= stripslashes( $aPO['tax_add']);?>" size="10"  tabindex="4"  style="text-align:right"   onKeyPress="if(event.keyCode==13) {document.getElementById('category_from').focus();return false;}">
        </font> </td>
      <td ><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Date</font></td>
      <td ><input name="date" type="text" class="altText" id="date" tabindex="8"  value="<?= ymd2mdy($aPO['date']);?>" size="11" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')"  onKeypress="if(event.keyCode==13) {document.getElementById('reference').focus();return false;}"> 
        <img src="../graphics/dwn-arrow-grn.gif" onClick="popUpCalendar(this, f1.date, 'mm/dd/yyyy')"> 
      </td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Term</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <input name="terms" type="text" class="altText" id="terms" tabindex="8"  value="<?= $aPO['terms'];?>" size="18"  readOnly>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Disc3</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="disc3" type="text" class="altText" id="disc3" value="<?=  $aPO['disc3'];?>" size="5"  tabindex="4"  style="text-align:right"  onKeypress="if(event.keyCode==13) {document.getElementById('freight_add').focus();return false;}" >
        <?= lookUpAssoc('disc3_type',array('Amount'=>'A','Percent'=>'P'), $aPO['disc3_type']);?>
        </font></td>
      <td colspan="4" nowrap bgcolor="#DADADA"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        &nbsp;.:: <font color="#0000CC">Auto PO</font> <font size="1">&gt;&gt;</font> 
        Category </font> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="category_from" type="text" class="altText" id="category_from" value="<?= stripslashes( $aPO['category_from']);?>" size="5"  tabindex="4"    onKeyPress="if(event.keyCode==13) {document.getElementById('category_to').focus();return false;}">
        To</font><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <input name="category_to" type="text" class="altText" id="category_to" value="<?= stripslashes( $aPO['category_to']);?>" size="5"  tabindex="4"    onKeyPress="if(event.keyCode==13) {document.getElementById('create').focus();return false;}">
        </font> 
        <input type="submit" name="p1" id="create" value="Create" class="altBtn"></td>
    </tr>
    <tr bgcolor="#CCCCCC"> 
      <td colspan="8"><font size="2" face="Times New Roman"><strong><em>Details</em></strong><br>
        </font> <table width="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#EFEFEF">
          <tr valign="top"> 
            <td width="15%" nowrap><font size="1" face="Verdana, Arial, Helvetica, sans-serif">Item<br>
              <input name="searchkey" type="text" class="altText" id="searchkey" value="<?= $searchkey;?>" size="15"  tabindex="10"  onKeypress="if(event.keyCode==13) {document.getElementById('SearchButton').click();return false;}">
              <?= lookUpAssoc('searchitemby',array('Barcode'=>'barcode','Name'=>'stock','Desc'=>'stock_description'),$searchitemby);?>
              <input name="p1" type="button" class="altBtn" id="SearchButton" value="Search" onClick="wait('Please wait. Searching...');xajax_pc_search(xajax.getFormValues('f1'),'po_select', 'case_qty');">
              </font><font size="1">&nbsp; </font></td>
            <td width="21%"><font size="1" face="Verdana, Arial, Helvetica, sans-serif">Description<br>
              <input name="stock" type="text" class="altText" id="stock" value="<?= stripslashes($iPOD['stock']);?>" size="38" readOnly>
              </font></td>
            <td width="4%"><font size="1" face="Verdana, Arial, Helvetica, sans-serif">Cases<br>
              <input name="case_qty" type="text" class="altNum" id="case_qty" value="<?= $iPOD['case_qty'];?>" size="8" onBlur="vCompute()"  onKeypress="if(event.keyCode==13) {document.getElementById('unit_qty').focus();return false;}">
              </font></td>
            <td width="3%" align="center"><font size="1" face="Verdana, Arial, Helvetica, sans-serif">Units</font><font size="1"><br>
              <input name="unit_qty" type="text" class="altText" id="unit_qty" value="<?= $iPOD['unit_qty'];?>" size="8"  tabindex="12"  onBlur="vCompute()"  style="text-align:right"  onKeypress="if(event.keyCode==13) {document.getElementById('cost3').focus();return false;}">
              </font></td>
            <td width="5%"><font size="1" face="Verdana, Arial, Helvetica, sans-serif">CasePrice</font><font size="1"><br>
              <input name="cost3" type="text" class="altText" id="cost3" value="<?= $iPOD['cost3'];?>" size="9" onChange="vCompute()"  onKeypress="if(event.keyCode==13) {document.getElementById('Ok').focus();return false;}" style="text-align:right">
              </font></td>
            <td width="5%"><font size="1" face="Verdana, Arial, Helvetica, sans-serif">Amount</font><font size="1"><br>
              <input name="amount" type="text" readOnly class="altText" id="amount" value="<?= $iPOD['amount'];?>" size="11"  style="text-align:right">
              </font></td>
            <td width="47%"><font size="1"><br>
              &nbsp; 
			  <? if(!isFinished($aPO['po_header_id'])){ ?>
              <input name="p1" type="submit" class="altBtn" id="Ok" value="Ok">
              <? } ?>
              <br>
              <input name="cost2" type="hidden" id="cost2" value="<?= $iPOD['cost2'];?>" size="7">
              <input name="cost1" type="hidden" id="cost1" value="<?= $iPOD['cost1'];?>" size="7">
              <input name="fraction3" type="hidden" id="fraction3" value="<?= $iPOD['fraction3'];?>" size="7">
              </font></td>
          </tr>
        </table></td>
		<input type="hidden" name="stock_id" id="stock_id">
        <input type="hidden" name="barcode" id="barcode" >
    </tr>
  </table>
  <table width="97%" border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#EFEFEF">
    <tr bgcolor="#D2DCDF"> 
      <td width="8%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
      <td width="12%" nowrap><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Bar 
        Code</font></strong></td>
      <td width="30%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Description</font></strong></td>
      <td width="6%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Unit</font></strong></td>
      <td width="9%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Order</font></strong></td>
      <td width="11%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Rec'd</font></strong></td>
      <td width="10%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">UnCost</font></strong></td>
      <td width="14%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Amount</font></strong></td>
    </tr>
    <?
	$c=0;
	$gross_amount=0;
	foreach ($aPOD as $temp)
	{
		$cunit='';
		if ($temp['case_qty'] != '0')
		{
			$cunit = $temp['unit3'];
		}		
		if ($temp['unit_qty'] != '0')
		{
			if ($cunit!='')$cunit.='/';
			$cunit .= $temp['unit1'];
		}		
	
		if ($aPO['status'] == 'A')
		{
			$gross_amount += $temp['amount_out'];
		}
		else
		{
			$gross_amount += $temp['amount'];
		}	
		if ($temp['fraction3'] == '' || $temp['fraction3']==0) $temp['fraction3'] =1;
		
		$case_inv = intval($temp['qty1_inv']/$temp['fraction3']);
		$unit_inv = $temp['qty1_inv'] - $case_inv*$temp['fraction3'];
		
		$order_qty = $temp['case_qty']*$temp['fraction3'] + $temp['unit_qty'];
		
		if ($order_qty == $temp['qty1_inv'])
		{
			$bgColor = '#D1EFFF';
		}
		elseif ($temp['qty1_inv'] > $order_qty)
		{
			$bgColor = '#FFCCFF';
		}
		elseif ($temp['qty1_inv'] > '0')
		{
			$bgColor = '#66FFCC';
		}
		else
		{
			$bgColor = '#FFFFFF';
		}
		$c++;
	?>
    <tr valign="top" bgColor='<?= $bgColor;?>' onMouseOver="bgColor='#FFFFCC'" onMouseOut="bgColor='<?= $bgColor;?>'"> 
      <td align="right" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $c;?>
        . 
        <input name="aChk[]" type="checkbox" id="aChk" value="<?= $c;?>">
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href='?p=porder&p1=Edit&id=<?=$c;?>'> 
        <?= $temp['barcode'];?>
        </a> </font></td>
      <td valign="top"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href='?p=porder&p1=Edit&id=<?=$c;?>'> 
        <?= stripslashes($temp['stock']);?>
        </a></font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $cunit;?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $temp['case_qty'].' : '.$temp['unit_qty'];?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $case_inv.' : '.$unit_inv;?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= ($aPO['status'] == 'A' ? number_format($temp['cost1'],2) : number_format($temp['cost1'],2));?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= ($aPO['status'] == 'A' ? number_format($temp['amount_out'],2) : number_format($temp['amount'],2));?>
        </font></td>
    </tr>
    <?
	}
	$aPO['gross_amount']  = $gross_amount;
	$aPO['total_items'] = $c;
	if ($aPO['discount_type']=='P')
	{
		$aPO['discount_amount'] = round($aPO['gross_amount'] *  $aPO['discount']/100,2);
	}
	else
	{
		$aPO['discount_amount'] = $aPO['discount'];
	}
	$aPO['net_amount'] = $aPO['gross_amount'] - $aPO['discount_amount'];
	
	?>
	<? if(!isFinished($aPO['po_header_id'])){ ?>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="8"> <input name="p1" type="submit" class="altBtn" id="p1" value="Delete Checked" >
      </td>
    </tr>
    <? } ?>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="8">
	  <table width="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#EFEFEF">
          <tr bgcolor="#FFFFFF"> 
            <!-- <td rowspan="3">
			<table width="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#EFEFEF">
                <tr bgcolor="#FFFFFF"> 
                  <td width="2%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Discount 
                    </font></td>
                  <td width="6%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">1</font></td>
                  <td width="7%" nowrap><input name="disc1" type="text" class="altNum" id="disc1" value="<?= $aInvoice['disc1'];?>" size="3"> 
                    <?= lookUpAssoc('disc1_type',array('Percent'=>'P','Amount'=>'A'),$aInvoice['disc1_type']);?>
                  </td>
                  <td width="1%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">4</font></td>
                  <td width="5%" nowrap><input name="disc4" type="text" class="altText" id="disc4" value="<?= $aInvoice['disc4'];?>" size="3" style="text-align:right"> 
                    <?= lookUpAssoc('disc4_type',array('Percent'=>'P','Amount'=>'A'),$aInvoice['disc4_type']);?>
                  </td>
                </tr>
                <tr bgcolor="#FFFFFF"> 
                  <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;</font></td>
                  <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">2</font></td>
                  <td nowrap><input name="disc2" type="text" class="altText" id="disc2" value="<?= $aInvoice['disc2'];?>" size="3" style="text-align:right"> 
                    <?= lookUpAssoc('disc2_type',array('Percent'=>'P','Amount'=>'A'),$aInvoice['disc2_type']);?>
                  </td>
                  <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">5</font></td>
                  <td nowrap><input name="disc5" type="text" class="altText" id="disc43" value="<?= $aInvoice['disc5'];?>" size="3" style="text-align:right"> 
                    <?= lookUpAssoc('disc5_type',array('Percent'=>'P','Amount'=>'A'),$aInvoice['disc5_type']);?>
                  </td>
                </tr>
                <tr bgcolor="#FFFFFF"> 
                  <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;</font></td>
                  <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">3</font></td>
                  <td nowrap><input name="disc3" type="text" class="altText" id="disc45" value="<?= $aInvoice['disc3'];?>" size="3" style="text-align:right"> 
                    <?= lookUpAssoc('disc3_type',array('Percent'=>'P','Amount'=>'A'),$aInvoice['disc3_type']);?>
                  </td>
                  <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">6</font></td>
                  <td nowrap><input name="disc6" type="text" class="altText" id="disc44" value="<?= $aInvoice['disc6'];?>" size="3" style="text-align:right"> 
                    <?= lookUpAssoc('disc6_type',array('Percent'=>'P','Amount'=>'A'),$aInvoice['disc6_type']);?>
                  </td>
                </tr>
              </table> </td>-->
            <td width="58%" rowspan="3" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Remarks<br>
              </font> <textarea class="altTextArea" name="remark" cols="75" id="remark"><?= stripslashes($aPO['remark']);?></textarea></td>
            <td width="10%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Gross 
              Sales</font></td>
            <td width="11%" align="right" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
              <strong> 
              <input name="gross_amount" type="text" class="altText" id="gross_amount22" value="<?= number_format($aPO['gross_amount'],2);?>" size="10" readOnly  style="text-align:right; font-weight:bold; border:#FFFFFF none">
              </strong> </font></td>
          </tr>
          <tr bgcolor="#FFFFFF"> 
            <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Discount</font></td>
            <td align="right" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
              <strong> 
              <input name="discount_amount" type="text" class="altText" id="discount_amount22" value="<?= number_format($aPO['discount_amount'],2);?>" readOnly size="10"  style="text-align:right; font-weight:bold;border:#FFFFFF none">
              </strong> </font></td>
          </tr>
          <tr bgcolor="#FFFFFF"> 
            <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Net 
              Sales</font></td>
            <td align="right" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
              <strong> 
              <input name="net_amount" type="text" class="altText" id="net_amount22" value="<?=number_format($aPO['net_amount'],2);?>" size="10" readOnly  style="text-align:right; font-weight:bold;border:#FFFFFF none">
              </strong> </font></td>
          </tr>
        </table></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="8"><table width="1%" border="0" cellpadding="1" cellspacing="1" bgcolor="#CCCCCC">
          <tr bgcolor="#FFFFFF"> 
          	<? if(!isFinished($aPO['po_header_id'])){ ?>
            <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              <input type="image" src="../graphics/save.jpg" accesskey="S" alt="Save This Claim Form" width="57" height="15" id="Save" onClick="f1.action='?p=porder&p1=Save';f1.submit();" name="Save">
              </strong></font><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              </strong></font></td>
            <? } ?>
            <td nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              <input type = "image" src="../graphics/print.jpg"  alt="Print This Claim Form"  name="Print" border="0" id="Print"  accesskey="P" onClick="f1.action='?p=porder&p1=Print';f1.submit();">
              </strong></font></td>
           	<? if(!isFinished($aPO['po_header_id']) || ( $ADMIN['usergroup'] == "A" && $aPO['status'] != 'C' ) ){ ?>
            <td nowrap width="25%"> <input type='image' name="Cancel" id="Cancel2" onClick="vSubmit(this)"  src="../graphics/cancel.jpg" alt="Cancel Form" width="77" height="20"> 
            <? } ?>
            <td nowrap width="25%"> <input type='image' name="New" accesskey="N" id="New" onClick="f1.action='?p=porder&p1=New';f1.submit();"  src="../graphics/new.jpg" alt="New Claim Form" width="63" height="20"> 
            </td>
           </tr>
        </table></td>
    </tr>
  </table>

</form>
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

if($p1 == "Print"){
?>
<div align="center">
	<iframe id="JOframe" name="JOframe" style="background-color:#FFF; margin:auto;" frameborder="0" width="90%" height="500" src="print_po.php?id=<?=$aPO['po_header_id']?>"></iframe><br />
    <input type="button" value="Print" onClick="printIframe('JOframe');" />
</div>	
<? } ?>
