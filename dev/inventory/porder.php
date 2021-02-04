<script>
<!--

function vSubmit(ul)
{
	if (ul.name == 'Cancel')
	{
		if (confirm("Are you sure to CANCEL Sales Transaction?"))
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
	font-size: 12px;
	color: #000000
	} 			

	.altBtn {
	background-color: #CFCFCF;
	font-family: verdana;
	border: #B1B1B1 1px solid;
	font-size: 11px;
	font-weight: bold;
	padding: 2px;
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
	$reference = date('Y-m').date('d').'-'.str_pad($sn,4,'0',str_pad_left);
	$aPO['reference'] = $reference;
	
	$q = "update invoice set invoice='$sn' where ip='PORDER'";
	$qr = @query($q);
}

$p1 = $_REQUEST['p1'];

if ($p1 == ''  && $b1 == '...') $p1='...';

$fields_header = array('date','reference', 'gross_amount', 'discount', 'discount_type',
						'net_amount','discount_amount','account','account_id','tender_id','remark','terms');

$fields_detail = array('case_qty','cost1','unit_qty', 'cost2','cost3' ,'fraction2' , 'fraction3', 'amount', 'stock');
$dfld = array('stock_id','barcode','case_qty','unit_qty','cost1', 'cost2', 'cost3' ,'amount');

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
			if ($aPO[$fields_header[$c]] == '' && !in_array($fields_header[$c], array('account','remark','invoice')))
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
		$q = "select terms, credit_limit , address from account where account_id = '".$aPO['account_id']."'";
		$qr = @query($q) or message(db_error());
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
elseif ($p1 == 'Ok') // && $iPOD['stock_id']!='')
{
	$aPO['status']='M';
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
			$aPOD[] = $iPOD;
	}
	$iPOD = null;
	$iPOD = array();
	$searchkey='';
}
elseif ($p1 == 'Delete Checked' && count($aChk)>0)
{
	$newArray=null;
	$newArray=array();
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
			$newArray[]=$temp;
		}
	}
	$aPOD = $newArray;	
}
elseif ($p1 == 'Save' && !in_array($aPO['status'],array(null,'M','S')))
{
	message("Cannot Update Purchase Order. Data already Released...");
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

				$qr = @query($q) or message(db_error());
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
				message("Problem Adding To Purchase Order Details...".db_error());
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
elseif ($p1 == 'Release' && $aPO['status']!='S')
{
	message('Status must be [ SAVED ] to Release Purchase Order...');
}
elseif ($p1 == 'Release')
{
	//update to stock ledger
	include_once('stockbalance.php');
	$total_request_qty = $total_issued_qty = $gross_amount = 0;
	$c=0;
	$ok=true;
	foreach ($aPOD as $temp)
	{
		$qty = $temp['qty1'];
		$price = $temp['price'];
		$total_request_qty += $qty;
		$ok=true;
		
		$stkled = stockBalance($temp['stock_id']);
		$balance_qty = $stkled['balance_qty'];
		
		$total_sales_qty_out = $amount_out =  0;
		$sales_qty_out = $qty_out = $qty_balance = 0;
		if ($balance_qty >= $qty)
		{
			$sales_qty_out = $qty;
		}
		else
		{
			$sales_qty_out = $balance_qty;
		}
		$amount_out += $sales_qty_out*$price;
		$total_sales_qty_out += $sales_qty_out;

		$total_issued_qty += $total_sales_qty_out;
		$gross_amount  += $amount_out;	
		$q = "update po_detail set 
					qty_out='$total_sales_qty_out', 
					amount_out = '$amount_out',
					stockledger_id_array='$stockledger_id_array'
				where
					po_detail_id='".$temp['po_detail_id']."'";
		$qr = @query($q) or message(db_error()." Error updating Issued Quantities...Item :".$temp['stock']);
		if (!$qr)
		{
			$ok = false;
		}
		else
		{
			commit();
			$dummy = $temp;
			
			if ($total_sales_qty_out > 0)
			{
				$dummy['qty_out'] = $total_sales_qty_out;
				$dummy['amount_out'] = $amount_out;
				$dummy['price_out'] = $amount_out/$total_sales_qty_out;
			}
			else
			{
				$dummy['qty_out'] = 0;
				$dummy['amount_out'] = 0;
				$dummy['price_out'] = 0;
			}	
			$aPOD[$c] = $dummy;
		}	
		$c++;
	}
	if ($ok)
	{
		$aPO['gross_amount'] = $gross_amount;
		$aPO['discount_amount'] = round($aPO['gross_amount'] * $aPO['discount_percent']/100,2);
		$aPO['net_amount'] = $aPO['gross_amount'] - $aPO['discount_amount'];
		
		if ($aPO['date_released'] == '')
		{
			$aPO['date_released'] = date('Y-m-d');
		}
		
		$q = "update po_header set 
					status='A', 
					date_released = '".$aPO['date_released']."',
					gross_amount = '".$aPO['gross_amount']."',
					discount_amount = '".$aPO['discount_amount']."',
					net_amount = '".$aPO['net_amount']."'
			 where 
			 		po_header_id='".$aPO['po_header_id']."'";
		@query($q) or message('Unable to update Purchase Order Status '.db_error());
		$aPO['status']='A';
		if ($total_request_qty ==  $total_issued_qty)
			message("Stocks Successfully Released...");
		else
			message("Stocks Successfully Released with UNSERVED Items.");
	}
}
elseif ($p1 == 'Print' && !in_array($aPO['status'], array('A','P','S')))
{
	message("Cannot Print. <b>Save</b> Purchase Order Before Printing...");
}
elseif ($p1 == 'Print')
{
	echo "<div id='layer.print' position='a'>";
	include_once('porder.print.php');
	echo "</div>";
	//echo "<script> window.open('index.php?p=porder.print&no_menu=yes','printwin','scrollbars=yes, width=800, height=600, status=no, resize=1')</script>";
}
elseif ($p1 == 'Printxx')
{
	//Purchase Order
	$q  = "<font size='+3' face='Bitstream Vera Sans Mono'>";
	if ($aPO['print_header'] == 'yes')
	{
		$q .= "\n\n\n\n";
		$q .= center(strtoupper($SYSCONF['BUSINESS_NAME']),80)."\n";
		$q .= center($SYSCONF['BUSINESS_ADDR'],80)."\n\n";
		$q .= "<bold>".center('Purchase Order',80)."</bold>\n";
	}
	else
	{
		$q .= "\n\n\n\n\n\n\n\n";
	}
		
	$s = "select * from account where account_id='".$aPO['account_id']."'";
	$qs = @query($s) or message(db_error().$s);
	$sr = @fetch_object($qs);
	
	$q .= adjustSize($sr->account,43).' '."Rec No.: ".str_pad($aPO['po_header_id'],8,'0',str_pad_left)."\n";
	$q .= adjustSize($sr->address,43).' '.    "Date   : ".ymd2mdy($aPO['date'])."\n";
	$q .= adjustSize($sr->city,43).' '.       "PO No. : ".$aPO['reference']."\n";
	$q .= adjustSize(' ',43).' '.            "Terms  : ".$aPO['terms']."</font>\n";
	
	$q .= "<font face='Times New Roman, Times, serif' size='7'>".center("Purchase Order",90)."</font>\n";
	$q .= "\n";
	$q .= "<font size='+3' face='Bitstream Vera Sans Mono'>";
	$q .= str_repeat('-',75)."\n";
	$q .= " Iem Code          Item Description              Qty   Price     Amount   \n";
	$q .= str_repeat('-',75)."\n";
	$header = $q;
	$c=0;
	foreach ($aPOD as $temp)
	{
		$cqty = '';
		if ($temp['case_qty'] != 0)
		{
			$cqty = intval($temp['case_qty']).adjustSize($temp['unit3'],3).' ';
		}
		if ($temp['uni_qty'] != 0)
		{
			$cqty .= intval($temp['unit_qty']).adjustSize($temp['unit1'],3).' ';
		}					
		$c++;
		$q .=  	adjustSize($temp['stock_code'],15).' '.
				adjustSize(substr($temp['stock'],0,25),25).' ';
		$q .= 	adjustRight($cqty,11).' '.
				adjustRight(number_format($temp['cost1'],2),10).' '.
				adjustRight(number_format($temp['amount'],2),10);
				if (strlen($temp['stock'])>25)
				{
					$q .= "\n".space(16).adjustSize(substr($temp['stock'],25,25),25).' ';
				}
				if (strlen($temp['stock'])>50)
				{
					$q .= "\n".space(16).adjustSize(substr($temp['stock'],50,75),25).' ';
				}
		$q .= "\n";
	}
	$q .= str_repeat('-',75)."\n";
	$q .= '   '.adjustSize($aPO['total_items'].' Item(s)',35).'  '.
				'GROSS AMOUNT :         '.adjustRight(number_format($aPO['gross_amount'],2),12)."\n";
	$q .= '                  '.adjustSize(' ',20)."  ".
				'DISCOUNT     :         '.adjustRight(number_format($aPO['discount_amount'],2),12)."\n";
	$q .= '                  '.adjustSize(' ',20)."  ".
				'NET AMOUNT   :         '.adjustRight(number_format($aPO['net_amount'],2),12)."\n";

	$q .= str_repeat('-',75)."\n";
	$q .= "\n\n";
	$q .= adjustSize($ADMIN['name'],40).
		  space(10).str_repeat('_',25)."\n";
	$q .= adjustSize('Prepared by:',40).
		  space(10).adjustSize('Checked by:',25)."\n";
	$q .= adjustSize(date('m/d/Y g:ia'),40)."\n";
	$q .= space(50).adjustSize('Approved by:',25)."\n\n";
	$q .= adjustSize('SHIP TO:',40).
		  space(10).adjustSize('ENGR. SUGAR ABABAO',25)."\n";
	$q .= adjustSize($SYSCONF['BUSINESS_NAME'],40)."\n";
	$q .= adjustSize($SYSCONF['BUSINESS_ADDR'],50)."\n\n";
	$q .= "\n\n\n\n</font>";
	
	//echo "<pre>$q</pre>";
	if ($SYSCONF['REPORT_PRINTER_TYPE'] == 'DRAFT')
	{
		doPrint($q);
		doPrint("\n\n\n\n\n\n\n\n\n\n\n\n\n");
	}
	else
	{
	 	 echo "<input type='hidden' id='print_area' name='print_area' value=".'"'.$q.'"'.">";
		echo "<iframe name='printit' id='printit' style='width:0px;height:0px;'>";
		echo "</iframe>";
		echo "<script>printIframe(print_area)</script>";
	}
}
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
	if ($qr)
	{
		foreach ($aPOD as $temp)
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
	if ($ok)
	{
		commit();
		$aPO['status']='C';
		message(' Stocks Purchase Order No. ['.str_pad($aPO['po_header_id'],8,'0',str_pad_left).'] Successfully CANCELLED');
	}
	else
	{
		message('Problem deleting stock ledger data...'.db_error());
	}
}
?><body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
 <form action="?p=porder" method="post" name="f1" id="f1" style="margin:0">

  <table width="97%" border="0" align="center" cellpadding="2" cellspacing="1">
    <tr> 
      <td>Search 
        <input name="xSearch" type="text" class="altText" id="xSearch2" value="<?= $xSearch;?>">
      <?= lookUpAssoc('searchby',array('PO No.'=>'reference','Rec Id'=>'po_header_id','Supplier'=>'account.account','Supplier Code'=>'account.account_code','Remarks'=>'remarks','Stock description'=>'stock_description'), $searchby);?>
      <?= lookUpAssoc('show',array('All'=>'','Saved'=>"'S','P'",'Partial Invoiced'=>"'A'",'Invoiced/Served'=>"'I'",'Cancelled'=>"'C'"), stripslashes($show));?>
      <input name="p122" type="button" class="altBtn" id="p122" value="Go" onClick="window.location='?p=porder.browse&p1=Go&xSearch='+xSearch.value+'&searchby='+searchby.value"> 
        <input type="submit" class="altBtn" name="p1" value="Add New"> 
        <input name="p12" type="button" class="altBtn" id="p12" value="Browse" onClick="window.location='?p=porder.browse&p1=Browse'"> 
		<input type="button" class="altBtn" name="Submit232" value="Close" onClick="window.location='?p='"> <br>
        <hr color="#CC0000"></td>
    </tr>
  </table>
  <?
  if (in_array($aPO['status'] , array('S','P')) && ($p1 == 'Email' || $p1 == 'Send'))
  {
  ?>
  <table width="97%" border="0" align="center" cellpadding="0" cellspacing="1">
    <tr bgcolor="#EFEFEF"> 
      <td colspan="2"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Email 
        </strong></font></td>
    </tr>
    <tr> 
      <td width="10%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">To</font></td>
      <td width="90%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="email_to" type="text" id="email_to" value="<?= $email_to;?>" size="40">
        </font></td>
    </tr>
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Subject</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="email_subject" type="text" id="email_subject" value="<?= $email_subject;?>" size="40">
        </font></td>
    </tr>
    <tr align="left" valign="top"> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Message</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <textarea name="email_message" cols="40" rows="3" id="email_message"><?= $email_message;?></textarea>
        <input name="p1" type="submit" class="altBtn" id="send" value="Send" >
        </font></td>
    </tr>
  </table>

  <?
  }
  ?>
  <table width="97%" border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#EFEFEF">
    <tr bgcolor="#CCCCCC"> 
      <td height="19" colspan="4"> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"><img src="../graphics/waiting.gif" width="16" height="16"><strong> 
        Purchase Order Entry</strong></font></td>
      <td height="19" colspan="2" align="center"> <font size="2" face="Times New Roman, Times, serif"> 
        <em> 
        <?= status($aPO['status']);?>
        </em></font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td width="93" height="24" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Type 
        </font></td>
      <td width="334" nowrap >
        <select name='tender_id' tabindex="1"  onKeypress="if(event.keyCode==13) {document.getElementById('account').focus();return false;}">
          <?
			$q = "select * from tender order by tender_type";
			$qr =@query($q);
			while ($r = @fetch_object($qr))
			{
				if ($aPO['tender_id'] == $r->tender_id)
				{
					echo "<option value=$r->tender_id selected>$r->tender</option>";
				}
				else
				{
					echo "<option value=$r->tender_id>$r->tender</option>";
				}	
			}
		?>
        </select>
        <input name="account_id" type="hidden" id="account_id" value="<?= $aPO['account_id'];?>" size="5" maxlength="30">
        </td>
      <td width="56"  nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Date</font></td>
      <td width="143"  nowrap >
        <input name="date" type="text" class="altText" id="date" tabindex="8"  value="<?= ymd2mdy($aPO['date']);?>" size="11" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')"  onKeypress="if(event.keyCode==13) {document.getElementById('reference').focus();return false;}">
        <img src="../graphics/dwn-arrow-grn.gif" onclick="popUpCalendar(this, f1.date, 'mm/dd/yyyy')"> 
        </td>
      <td width="63" nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Rec 
        No.</font><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
        </font></td>
      <td width="277"  nowrap >
        <?= str_pad($aPO['po_header_id'],8,'0',str_pad_left);?>
        </td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td valign="top"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Supplier</font></td>
      <td nowrap > <font size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <input name="account" type="text" class="altText" id="account" value="<?=stripslashes( $aPO['account']);?>" size="35"  onChange="b1.click()"  tabindex="2">
        </font><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <input name="b1" type="button" class="altBtn" id="b1" value="..." onClick="f1.action='?p=porder&b1=...';f1.submit()">
        </font> </td>
      <td nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif">PO#</font></td>
      <td nowrap >
        <input name="reference" type="text" class="altText" id="reference" tabindex="8"  value="<?= $aPO['reference'];?>" size="17"  onKeypress="if(event.keyCode==13) {document.getElementById('terms').focus();return false;}">
        <input name="p1" type="submit" class="altBtn" id="gen" value="Gen">
        </td>
      <td ><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Term</font></td>
      <td >
        <input name="terms" type="text" class="altText" id="terms" tabindex="8"  value="<?= $aPO['terms'];?>" size="15"  onKeypress="if(event.keyCode==13) {document.getElementById('searchkey').focus();return false;}">
        </td>
    </tr>
	<tr bgcolor="#FFFFFF">
	  <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Discount</font></td>  <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <input name="discount" type="text" class="altText" id="discount" value="<?= stripslashes( $aPO['discount']);?>" size="10"  tabindex="4"  style="text-align:right" >
        </font><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <?= lookUpAssoc('discount_type',array('Amount'=>'A','Percent'=>'P'), $aPO['discount_type']);?>
        </font></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	</tr>
    <tr bgcolor="#CCCCCC"> 
      <td colspan="6"><font size="2" face="Times New Roman"><strong><em>Details</em></strong><br>
        </font> <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#EFEFEF">
          <tr valign="top"> 
            <td width="16%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Item</font><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><br>
              <input name="searchkey" type="text" class="altText" id="searchkey" value="<?= $searchkey;?>" size="15"  tabindex="10"  onKeypress="if(event.keyCode==13) {document.getElementById('SearchButton').click();return false;}">
              </font> <input name="p1" type="submit" class="altBtn" id="SearchButton" value="Search" > 
              &nbsp; </td>
            <td width="22%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Description<br>
              <input name="stock" type="text" class="altText" id="stock" value="<?= stripslashes($iPOD['stock']);?>" size="38" readOnly>
              </font></td>
            <td width="4%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Cases<br>
              <input name="case_qty" type="text" class="altNum" id="case_qty" value="<?= $iPOD['case_qty'];?>" size="8" onBlur="vCompute()"  onKeypress="if(event.keyCode==13) {document.getElementById('unit_qty').focus();return false;}">
              </font></td>
            <td width="4%" align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Units</font><br> 
              <input name="unit_qty" type="text" class="altText" id="unit_qty" value="<?= $iPOD['unit_qty'];?>" size="8"  tabindex="12"  onBlur="vCompute()"  style="text-align:right"  onKeypress="if(event.keyCode==13) {document.getElementById('cost1').focus();return false;}"> 
            </td>
            <td width="4%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">UnPrice</font><br> 
              <input name="cost1" type="text" class="altText" id="cost1" value="<?= $iPOD['cost1'];?>" size="9" onChange="vCompute()"  onKeypress="if(event.keyCode==13) {document.getElementById('Ok').focus();return false;}" style="text-align:right"> 
            </td>
            <td width="1%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Amount</font><br> 
              <input name="amount" type="text" readOnly class="altText" id="amount" value="<?= $iPOD['amount'];?>" size="11"  style="text-align:right"> 
            </td>
            <td width="49%"><br> &nbsp;<input name="p1" type="submit" class="altBtn" id="Ok" value="Ok">
              <br>
              <input name="cost2" type="hidden" id="cost2" value="<?= $iPOD['cost2'];?>" size="7"> 
              <input name="cost3" type="hidden" id="cost3" value="<?= $iPOD['cost3'];?>" size="7"> 
              <input name="fraction3" type="hidden" id="fraction3" value="<?= $iPOD['fraction3'];?>" size="7"> 
            </td>
          </tr>
        </table></td></tr>
  </table>
  <?
	if ($p1 == 'Search')
  	{
  ?>
  <br>
  <table width="90%" border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#F4EAFF">
    <tr bgcolor="#9999CC"> 
      <td width="3%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
      <td width="15%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Barcode</font></strong></td>
      <td width="30%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Description</font></strong></td>
      <td width="8%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Unit</font></strong></td>
      <td width="22%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Category</font></strong></td>
      <td width="11%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">UnCost</font></strong></td>
      <td width="11%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Balance</font></strong></td>
    </tr>
    <?
		//include_once('stockbalance.php');
		$msearchkey = strtoupper($searchkey);		
		
	  	$q = "select * 
					from 
						stock 
					where 
						upper(stock) like '%$msearchkey%' and 
						account_id = '".$aPO['account_id']."' and 
						enable='Y' order by lower(stock) offset 0 limit 50";

		$qr = @query($q) or message(db_error());
		$cs = 0;
		while ($r = @fetch_object($qr))
		{
			$cs++;
			//$balance_qty = stockBalance($r->stock_id);
	
	?>
    <tr  onClick="f1.action='?p=porder&p1=selectStock&id=<?=$r->stock_id;?>';f1.submit()" bgColor='#FFFFFF' onMouseOver="bgColor='#FFFFCC'" onMouseOut="bgColor='#FFFFFF'"> 
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $cs;?>. </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
	  	<a javascript: "f1.action='?p=porder&p1=selectStock&id=<?=$r->stock_id;?>';f1.submit()">
        <?= $r->barcode;?></a>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
	  	<a href= "javascript: f1.action='?p=porder&p1=selectStock&id=<?=$r->stock_id;?>';f1.submit();">
        <?= $r->stock;?></a>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $r->unit1;?>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= lookUpTableReturnValue('x','category','category_id','category',$r->category_id);?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <?= number_format($r->cost1,2);?></font>
      </td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
	  	<a href= "javascript: f1.action='?p=porder&p1=selectStock&id=<?=$r->stock_id;?>';f1.submit();">
	  <?= $balance_qty;?></a></font></td>
    </tr>
    <?
		}
	?>
  </table>
  <hr width="96%">
  <br>
  <?
  }
  ?>
  <table width="97%" border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#EFEFEF">
    <tr bgcolor="#D2DCDF"> 
      <td width="8%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
      <td width="12%" nowrap><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Bar 
        Code</font></strong></td>
      <td width="30%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Description</font></strong></td>
      <td width="6%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Unit</font></strong></td>
      <td width="9%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Order</font></strong></td>
      <td width="11%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Issued</font></strong></td>
      <td width="10%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">UnCost</font></strong></td>
      <td width="14%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Amount</font></strong></td>
    </tr>
    <?
	$c=0;
	$gross_amount=0;
	foreach ($aPOD as $temp)
	{
		if ($aPO['status'] == 'A')
		{
			$gross_amount += $temp['amount_out'];
		}
		else
		{
			$gross_amount += $temp['amount'];
		}	
		if ($temp['fraction'] == '' || $temp['fraction']==0) $temp['fraction'] =1;
		$c++;
	?>
    <tr valign="top" bgColor='#FFFFFF' onMouseOver="bgColor='#FFFFCC'" onMouseOut="bgColor='#FFFFFF'"> 
      <td align="right" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $c;?>
        . 
        <input name="aChk[]" type="checkbox" id="aChk" value="<?= $c;?>">
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href='?p=porder&p1=Edit&id=<?=$c;?>'> 
        <?= $temp['barcode'];?>
        </a> </font></td>
      <td valign="top"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href='?p=porder&p1=Edit&id=<?=$c;?>'> 
        <?= $temp['stock'];?>
        </a></font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $temp['unit1'];?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $temp['case_qty'].' : '.$temp['unit_qty'];?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $temp['case_inv'].' : '.$temp['unit_inv'];?>
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
    <tr bgcolor="#FFFFFF"> 
      <td colspan="8"> <input name="p1" type="submit" class="altBtn" id="p1" value="Delete Checked" >
      </td>
    </tr>
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
            <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              <input type="image" src="../graphics/save.jpg" accesskey="S" alt="Save This Claim Form" width="57" height="15" id="Save" onClick="f1.action='?p=porder&p1=Save';f1.submit();" name="Save">
              </strong></font><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              </strong></font></td>
            <td nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              <input type = "image" src="../graphics/print.jpg"  alt="Print This Claim Form"  name="Print" border="0" id="Print"  accesskey="P" onClick="f1.action='?p=porder&p1=Print';f1.submit();">
              </strong></font></td>
            <td nowrap width="25%"> <input type='image' name="Cancel" id="Cancel2" onClick="vSubmit(this)"  src="../graphics/cancel.jpg" alt="Cancel Form" width="77" height="20"> 
            <td nowrap width="25%"> <input type='image' name="New" accesskey="N" id="New" onClick="f1.action='?p=porder&p1=New';f1.submit();"  src="../graphics/new.jpg" alt="New Claim Form" width="63" height="20"> 
            </td>
           </tr>
        </table></td>
    </tr>
  </table>

</form>
<?
if ($p1 == '...' )
{
	include_once('porder.searchaccount.php');
}
else
{
	echo "<pre>Credit Limit   : P".adjustRight(number_format($aPO['credit_limit'],2),12)."<br>";
	echo "Account Balance: P".adjustRight(number_format($aPO['account_balance'],2),12)."<br>";
	echo "Credit Balance : P".adjustRight(number_format($aPO['credit_limit']-$aPO['account_balance'],2),12)."</pre>";
	
	if ($aPO['credit_limit'] > 0 && ($aPO['net_amount']+$aPO['account_balance'])>$aPO['credit_limit'])
	{
		$net_amount = 'P'.number_format($aPO['net_amount'],2);
		$credit_balance = 'P'.number_format($aPO['credit_limit']-$aPO['account_balance'],2);
		echo "<script>alert('Credit Amount ($net_amount) Greater Than Credit Balance ($credit_balance)!!')</script>";
	}	
}
	
if ($focus != '')
{
	echo "<script>document.getElementById('$focus').focus()</script>";
}
else
{
	echo "<script>document.getElementById('searchkey').focus()</script>";
}
?>