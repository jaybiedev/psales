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

function vCompute()
{
	fraction3 = parseFloat(document.getElementById('fraction3').value);
	cost3 = parseFloat(document.getElementById('cost3').value);
	
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
	$qq = "select * from rr_detail where stock_id = '$r->stock_id' order by rr_header_id offset 0 limit 1";
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
if (!chkRights2('receiving','madd',$ADMIN['admin_id']))
{
	message('You have [ NO ] permission to access this area...');
	exit;
}
//include_once("lib/lib.css.php");
if (!session_is_registered('aRR'))
{
	session_register('aRR');
	$aRR=null;
	$aRR=array();
}

if (!session_is_registered("aRRD"))
{
	session_register("aRRD");
	$aRRD=null;
	$aRRD=array();
}
if (!session_is_registered("iRRD"))
{
	session_register("iRRD");
	$iRRD=null;
	$iRRD=array();
}

$p1 = $_REQUEST['p1'];

if ($p1 == ''  && $b1 == '...') $p1='...';

$fields_header = array('date', 'invoice', 'gross_amount', 'disc1', 'disc1_type','disc2', 'disc2_type','disc3', 'disc3_type','tax_add','tax_add_type', 'tax_amount',
						'net_amount','discount_amount','account_id','account','freight_amount','freight_add','remark', 'reference','po_header_id','receivedby');

$fields_detail = array('case_qty','cost1','unit_qty', 'cost2','cost3' ,'fraction2' , 'fraction3', 'amount', 'freight_case','stock');
$dfld = array('stock_id','barcode','case_qty','unit_qty','freight_case','cost1', 'cost2', 'cost3' ,'amount');

if (!in_array($p1, array(null,'Delete','Print','Load','Serve','Submit', 'Receive')))
{

	for ($c=0;$c<count($fields_header);$c++)
	{
		$aRR[$fields_header[$c]] = $_REQUEST[$fields_header[$c]];
		if ($fields_header[$c] == 'date' || $fields_header[$c]=='date_released'|| $fields_header[$c]=='checkdate')
		{
			$aRR[$fields_header[$c]] = mdy2ymd($_REQUEST[$fields_header[$c]]);
		}
		else
		{
			if (!in_array($fields_header[$c], array('account','remark','invoice')))
			{
				if ($aRR[$fields_header[$c]] == '' )
				{
					$aRR[$fields_header[$c]] = '0';
				}
				else
				{
					$aRR[$fields_header[$c]] = str_replace(',','',$aRR[$fields_header[$c]]);
				}
			}
		}
	}
	if ($aRR['po_header_id'] > '0')
	{
		$q = "select po_header_id from po_header where reference='".$aRR['reference']."'";
		$qr = @query($q) or message(msyql_error());
		if (@pg_num_rows($qr) == 0)
		{
			message("PO Number NOT found in Purchase Order File...");
		}
		else
		{
			$r = @fetch_object($qr);
			$aRR['po_header_id'] = $r->po_header_id;
		}
			
	}
	
	$aRR['gross_amount'] = $aRR['gross_amount'] ; //str_ireplace(',','',$aRR['gross_amount']);
	$aRR['discount_amount'] = $aRR['discount_amount']; //str_ireplace(',','',$aRR['discount_amount']);
	$aRR['net_amount'] = $aRR['net_amount'] ; //str_ireplace(',','',$aRR['net_amount']);


	$aRR['admin_id']=$ADMIN['admin_id'];
	for ($c=0;$c<count($fields_detail);$c++)
	{
		$iRRD[$fields_detail[$c]] = $_REQUEST[$fields_detail[$c]];
		if (!in_array($fields_detail[$c], array('stock')))
		{
			if ($iRRD[$fields_detail[$c]] == '' )
			{
				$iRRD[$fields_detail[$c]] = '0';
			}
			else
			{
				$iRRD[$fields_detail[$c]] = str_replace(',','',$iRRD[$fields_detail[$c]]);
			}
		}
	}
	if ($iRRD['fraction3'] == '') $iRRD['fraction3'] = 1;
	$iRRD['qty1'] = $iRRD['unit_qty'] + $iRRD['case_qty'] * $iRRD['fraction3'] ;
	
	if ($aRR['account_id'] != '')
	{
		$q = "select terms, credit_limit , address, account_code from account where account_id = '".$aRR['account_id']."'";
		$qr = @query($q) or message(db_error());
		$r = @fetch_assoc($qr);
		if ($r)
		{
			$aRR['terms'] = $r['terms'];
			$aRR['credit_limit'] = $r['credit_limit'];
			$aRR['address'] = $r['address'];
			$aRR['account_code'] = $r['account_code'];
		}	
	}	
}
if ($aRR['date'] =='' || $aRR['date'] =='//')
{
	$aRR['date'] = date('Y-m-d');
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
		$iRRD = $r;
		
		if ($iRRD['fraction2'] == 0 || $iRRD['fraction2'] == '') $iRRD['fraction2'] =1 ;
		if ($iRRD['fraction3'] == 0 || $iRRD['fraction3'] == '') $iRRD['fraction3'] =1 ;
		$iRRD['afraction'] = '1;'.$iRRD['fraction2'].';'.$iRRD['fraction3'];

		if ($aRR['tax_add'] > 0)
		{
			$iRRD['cost3_with_tax'] = $iRRD['cost3'];
			$iRRD['cost3'] =round($iRRD['cost3'] / (1+($aRR['tax_add']/100)),2);
			$iRRD['cost1'] = round($iRRD['cost3']/$iRRD['fraction3'],2);
		}
		$iRRD['fraction'] = 1;
		$iRRD['price'] = $iRRD['cost1'];
		$iRRD['unit'] = 1;
		
		$p1 = 'searchFound';
		$searchkey = $iRRD['barcode'];
		$focus = 'case_qty';
		if ($r['account_id'] != $aRR['account_id'])
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
			$iRRD = $r;
			if ($iRRD['fraction2'] == 0 || $iRRD['fraction2'] == '') $iRRD['fraction2'] =1 ;
			if ($iRRD['fraction3'] == 0 || $iRRD['fraction3'] == '') $iRRD['fraction3'] =1 ;
			$iRRD['afraction'] = '1;'.$iRRD['fraction2'].';'.$iRRD['fraction3'];
			
			$iRRD['fraction'] = 1;
			$iRRD['price'] = $iRRD['cost1'];
			$iRRD['unit'] = 1;
			$searchkey = $iRRD['barcode'];
			$focus = 'case_qty';
			$p1 = 'searchFound';
			$focus='case_qty';
		}
	}
}

if ($p1=='Browse')
{
	echo "<script>window.location='?p=receiving.browse&p1=Browse'</script>";
}
elseif (($p1 == 'Submit' )&& $source != '' )
{
	//retrieve PO
	if ($source == '' or $source == 'N' or $document== '')
	{
		$aRR=null;
		$aRR=array();
		$aRRD= null;
		$aRRD = array();
		$iRRD= null;
		$iRRD = array();
		$aRR['date'] = date('Y-m-d');
	}
	else
	{
		$q = "select * from po_header where $source = '$document'";
		$qr  = @pg_query($q) or message1(pg_errormessage());
		
		if (@pg_num_rows($qr) == 0)
		{
			message1("<br>[ Purchase Order With Document No. ($source): $document NOT found... ]<br><br>
							<a href='?p=receiving.new'>Click here to Try Again</a>");
			exit;
		}
		else
		{
			$r = @pg_fetch_object($qr);
			$id = $r->po_header_id;
			$p1 = 'Receive';
		}
	}
}

if ($p1 == 'Load' && $id == '')
{
	message("Nothing to Load...");
}
elseif ($p1 == 'Load')
{
	$aRR=null;
	$aRR=array();
	$aRRD= null;
	$aRRD = array();
	$q = "select * from rr_header where rr_header_id='$id'";
	$qr = @query($q) or message(pg_errormessage());
	if ($qr)
	{
		$qd = "select * from rr_detail where rr_header_id='$id'";
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
			$aRRD[]=$temp;
		}
		
		$r = @pg_fetch_assoc($qr);
		$aRR= $r;

		if ($aRR['account_id'] > 0)
		{
			$q = "select * from account where account_id='".$aRR['account_id']."'";
			$qra = @pg_query($q) or message1(pg_errormessage());
			
			$ra = @pg_fetch_object($qra);
			$aRR['account'] = $ra->account;
			$aRR['credit_limit'] = $ra->credit_limit;
			$aRR['terms'] = $ra->terms;
			$aRR['address'] = $ra->address;
			$aRR['account_code'] = $ra->account_code;
		}	
	}
}
elseif ($p1 == 'New' or $p1 == 'Add New')
{

	$aRR=null;
	$aRR=array();
	$aRRD= null;
	$aRRD = array();
	$iRRD= null;
	$iRRD = array();
	$aRR['date'] = date('Y-m-d');
	
	echo "<script>window.location='?p=receiving.new'</script>";

}
elseif (in_array($p1,array('Edit','Ok','Save','Update','Search','selectStock','Release') )&& $aRR['status'] == 'A')
{
	message('Editing not allowed. Stocks Receiving Report already Released/Posted');
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
	
	$iRRD = $r;
	if ($iRRD['fraction2'] == 0 || $iRRD['fraction2'] == '') $iRRD['fraction2'] =1 ;
	if ($iRRD['fraction3'] == 0 || $iRRD['fraction3'] == '') $iRRD['fraction3'] =1 ;
	$iRRD['afraction'] = '1;'.$iRRD['fraction2'].';'.$iRRD['fraction3'];
	
	$iRRD['fraction'] = 1;
	$iRRD['price'] = $iRRD['cost1'];
	$iRRD['unit'] = 1;
	$searchkey = $iRRD['barcode'];
	$focus = 'case_qty';
	
	if ($aRR['tax_add'] > '0')
	{
		$iRRD['cost3_with_tax'] = $iRRD['cost3'];
		$iRRD['cost3'] =round($iRRD['cost3'] / (1+($aRR['tax_add']/100)),2);
		$iRRD['cost1'] = round($iRRD['cost3']/$iRRD['fraction3'],2);
	}
}
elseif ($p1 == 'Edit' && $id!='')
{
	$iRRD = null;
	$iRRD = array();
	$c=0;
	foreach ($aRRD as $temp)
	{
		$c++;
		if ($id == $c)
		{
			$iRRD = $temp;
			$iRRD['line_ctr'] = $c;
			$searchkey=$temp['barcode'];
			break;
		}
		
	}
	$focus = 'case_qty';
	
}
elseif ($p1 == 'Ok' && ($iRRD['stock_id']=='' || $iRRD['barcode']=='' || $iRRD['stock_id'] == '' || $iRRD['amount'] == '0'))
{
	message("Check: Stock Item Selected and Quantity ..");
	$aRR['status']='M';
}
elseif ($p1 == 'Ok') // && $iRRD['stock_id']!='')
{
	$aRR['status']='M';
	$dummy = null;
	$dummy = array();
	
	$fnd=0;
	$c=0;
	foreach ($aRRD as $temp)
	{
		if ($temp['stock_id'] == $iRRD['stock_id'])
		{
			$dummy = $temp;
			$dummy['case_qty'] = $iRRD['case_qty'];
			$dummy['unit_qty'] = $iRRD['unit_qty'];
			$dummy['cost1'] = $iRRD['cost1'];
			$dummy['cost2'] = $iRRD['cost2'];
			$dummy['cost3'] = $iRRD['cost3'];
			$dummy['freight_case'] = $iRRD['freight_case'];
			$dummy['amount'] = $iRRD['amount'];
			$dummy['stock'] = $iRRD['stock'];
			$dummy['qty1'] = $iRRD['qty1'];
			$aRRD[$c] = $dummy;
			$fnd = 1;
			break;
		}
		$c++;
	}
	
	if ($fnd == '0')
	{
			$aRRD[] = $iRRD;
	}
	$iRRD = null;
	$iRRD = array();
	$searchkey='';
}
elseif ($p1 == 'Delete Checked' && count($aChk)>0)
{
	$newArray=null;
	$newArray=array();
	$nctr=0;
	foreach ($aRRD as $temp)
	{
		$nctr++;
		if (in_array($nctr,$aChk))
		{
			if ($temp['rr_detail_id'] != '')
			{
				$deletok=false;
				$qr = @query("delete from rr_detail where rr_detail_id='".$temp['rr_detail_id']."'") or message1(db_error());
				if (@pg_affected_rows($qr)>0)
				{
					$deleteok=true;
				}
				else
				{
					message("FATAL error: Was not able to delete from Stocks Receiving Report detail file...".mysql_error($qr));
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
	$aRRD = $newArray;	
}
elseif ($p1 == 'Save' && !in_array($aRR['status'],array(null,'M','S')))
{
	message("Cannot Update Stocks Receiving Report. Data already Released...");
}
elseif ($p1 == 'Save' && count($aRRD) == 0)
{
	message("No items to save...");
}
elseif ($p1 == 'Save' && $aRR['account_id'] == '0')
{
	message("No Supplier Specified.  Please check and Save again...");
}
elseif ($p1 == 'Save')
{

	if ($aRR['rr_header_id'] == '')
	{
		$aRR['audit'] = 'Encoded by '.$ADMIN['name'].' on '.date('m/d/Y g:ia').'; ';

		query("begin transaction");
		$time = date('G:i');
		$q = "insert into rr_header ( time, admin_id, status, ip ";
		$qq .= ") values ('$time', '".$ADMIN['admin_id']."','S', '".$_SERVER['REMOTE_ADDR']."'";
		for ($c=0;$c<count($fields_header);$c++)
		{
			if ($fields_header[$c] == 'account') continue;
			$q .= ",".$fields_header[$c];
			$qq .= ",'".$aRR[$fields_header[$c]]."'";
		}
		$q .= $qq.")";
		$qr = @query($q) or message1(db_error().$q);

		if ($qr && pg_affected_rows($qr)>0)
		{
			$ok=1;
			$aRR['rr_header_id'] = db_insert_id('rr_header');

			// insert to si detail
			$c=0;
			foreach ($aRRD as $temp)
			{
				if ($temp['case_qty']*1 == '0' && $temp['unit_qty']*1 == '0') continue;
				$q = "insert into rr_detail (rr_header_id";
				$qq = ") values ('".$aRR['rr_header_id']."'";
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
					$dummy['rr_detail_id'] = db_insert_id('rr_detail');
					$aRRD[$c]=$dummy;
					
					if ($aRR['tax_add'] > 0)
					{
						$cost3 = round($temp['cost3']*(1+($aRR['tax_add']/100)),2);
					}
					else
					{
						$cost3 = $temp['cost3'];
					}
					$cost1 = $cost3/ $temp['fraction3'];
					
					if ($SYSCONF['RR_FORMAT'] != 'LEC')
					{
						$q = "update stock set cost3='$cost3', cost1='$cost1' where stock_id='".$temp['stock_id']."'";
						@pg_query($q) or message1('Error Updating Stocks Master...');
					}
				}

				$c++;
			}	
			if ($ok)
			{
				query("commit");
				$aRR['status']='S';
				message(" Stocks Receiving Report Saved...");
			}
			else
			{
				query("rollback transaction");
				message("Problem Adding To Stocks Receiving Report Details...".db_error());
				$aRR['status']='S';
				$aRR['rr_header_id']='';
			}
		}
		else
		{
			message("Cannot Add Record To Stocks Receiving Report Header File...");
			query("rollback transaction");
		}
							
	}
	else
	{
		$ok=true;
		$aRR['audit'] = $aRR['audit'].'Updated by '.$ADMIN['name'].' on '.date('m/d/Y g:ia').'; ';
	
		query("begin");	
		$q = "update rr_header set rr_header_id = '".$aRR['rr_header_id']."'";
		for ($c=0;$c<count($fields_header);$c++)
		{
			if ($fields_header[$c] == 'account') continue;

			$q .= ",".$fields_header[$c]."='".$aRR[$fields_header[$c]]."'";
		}
		$q .= " where rr_header_id = '".$aRR['rr_header_id']."'";

		$qr = @query($q) or message(db_error());
		if ($qr)
		{
			$c=0;
			foreach ($aRRD as $temp)
			{
				if ($temp['rr_detail_id'] == '')
				{
					if ($temp['case_qty']*1 == '0' && $temp['unit_qty']*1 == '0') continue;

					$q = "insert into rr_detail (rr_header_id";
					$qq = ") values ('".$aRR['rr_header_id']."'";
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
					$q = "update rr_detail set rr_header_id='".$aRR['rr_header_id']."'";
					for ($i=0;$i<count($dfld);$i++)
					{
						$q .= ",".$dfld[$i]."='".addslashes($temp[$dfld[$i]])."'";
					}
					$q .= "	where rr_detail_id='".$temp['rr_detail_id']."'";

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
					if ($temp['rr_detail_id'] == ''  || $updatecost == 'update')
					{
						if ($temp['rr_detail_id'] == '')
						{
							$dummy=$temp;
							$dummy['rr_detail_id'] = db_insert_id('rr_detail');
							$aRRD[$c]=$dummy;
						}
						
						if ($aRR['tax_add'] > 0)
						{
							$cost3 = round($temp['cost3']*(1+($aRR['tax_add']/100)),2);
						}
						else
						{
							$cost3 = $temp['cost3'];
						}
						$cost1 = $cost3/ $temp['fraction3'];
						if ($SYSCONF['RR_FORMAT'] != 'LEC')
						{
							$q = "update stock set cost3='$cost3', cost1='$cost1' where stock_id='".$temp['stock_id']."'";
							@pg_query($q) or message1('Error Updating Stocks Master...');
						}
					}
				}
				$c++;
			}	
			if ($ok)
			{
				query("commit");
				message(" Stocks Receiving Report Updated...");
				$aRR['status']='S';
			}
			else
			{
				query("rollback");
				message("Problem Updating To Stocks Receiving Report Details...".db_error());
			}
		}
		else
		{
			message("Cannot Modify Record To Stocks Receiving Report Header File...".db_error().$q);
			query("rollback");
		}
					
	}
	if ($ok)
	{

			if ($aRR['rr_header_id'] != '')
			{
					$po_status = 'R';
					$q = "select 	sum(case_qty) as case_qty,
									sum(unit_qty) as unit_qty,
									rr_detail.stock_id, 
									rr_header.account_id
								from 
									rr_header,
									rr_detail
								where
									rr_header.rr_header_id=rr_detail.rr_header_id and
									rr_header.status!='C' and
									rr_header.po_header_id = '".$aRR['po_header_id']."' and
									rr_header.rr_header_id = '".$aRR['rr_header_id']."' 
								group by
									rr_detail.stock_id,
									account_id";

							$qr = @query($q) or message(db_error());
							
					while ($r = @fetch_object($qr))
					{
						$fraction3 = 1;
						if ($r->stock_id > '0')
						{
							$q = "select fraction3 from stock where stock_id='$r->stock_id'";
							$rr= fetch_object($q);

							$fraction3 = $rr->fraction3;
							if ($fraction3 == '0')  
							{
								$fraction3 = 1;
								@pg_query("update stock set fraction3 = '1' where stock_id = '$r->stock_id'");
							}
							$qty1_inv = $r->case_qty*$fraction3 + $r->unit_qty;
							
							if ($SYSCONF['RR_FORMAT'] != 'LEC')
							{
								$q = "update stock set account_id='$r->account_id' where stock_id='$r->stock_id'";
								$qqr = @query($q) or message(db_error());
							}
						}
						else
						{
							$qty1_inv = $r->case_qty + $r->unit_qty;
						}	
						$q = "update po_detail set qty1_inv='$qty1_inv' where po_header_id ='".$aRR['po_header_id']."'and stock_id='$r->stock_id'";
						$qqr = @query($q) or message(db_error());
					}
					
					$q = "select * from po_detail where po_header_id = '".$aRR['po_header_id']."'";
					$qr = @query($q) or message(db_error());
					
					$po_status = 'E';
					while ($r = @fetch_object($qr))
					{
						if ($r->qty1_inv < ($r->case_qty + $r->unit_qty))
						{
							$po_status = 'T';
							break;
						}
						
					}		

					//set status of purchase order
					$q = "update po_header set status='$po_status' where po_header_id='".$aRR['po_header_id']."'";
					$qr = @query($q) or message(db_error());
			}
			
			$q = "select * from apledger where type='R' and record_id = '".$aRR['rr_header_id']."' ";
			$qr = @pg_query($q) or message1(pg_errormessage().$q);
			if (@pg_num_rows($qr) == 0)
			{
				$q = "insert into apledger (account_id, date, record_id, reference, credit,debit,type)
							values ('".$aRR['account_id']."', '".$aRR['date']."', '".$aRR['rr_header_id']."','".$aRR['invoice']."','".$aRR['net_amount']."','0','R')";
				$qr = @pg_query($q) or message1(pg_errormessage().$q);
							
			}
			else
			{
				$r = @pg_fetch_object($qr);
				$apledger_id = $r->apledger_id;
				$q = "update apledger set
									credit = '".$aRR['net_amount']."',
									debit='0',
									account_id = '".$aRR['account_id']."',
									date = '".$aRR['date']."',
									reference ='".$aRR['invoice']."'";
				$qr = @pg_query($q) or message1(pg_errormessage().$q);
									
			}
	}
}
elseif ($p1 == 'Receive' && $id == '')
{
	message("No Purchase Order Specified...");
}
elseif ($p1 == 'Receive')
{
	$aRR=null;
	$aRR=array();
	$aRRD= null;
	$aRRD = array();
	$q = "select * from po_header where po_header_id='$id'";
	$qr = @query($q) or message(db_error());
	
	if ($qr)
	{
		$qd = "select * from po_detail where po_header_id='$id'";
		$qdr = @query($qd) or message(db_error());

		while ($r = @pg_fetch_assoc($qdr))
		{
			$temp = $r;
			$temp['case_order'] =  $r['case_qty'];
			$temp['unit_order'] = $r['unit_qty'];
			$temp['barcode'] = $r['barcode'];
//			$temp['case_qty'] = '';
//			$temp['unit_qty'] = '';
//			$temp['amount'] = '';
		
			if ($temp['stock'] == '')
			{
				$qs = "select stock, stock_code, unit1, unit2, unit3, fraction2, fraction3
						 from 
						 	stock where stock_id='".$r['stock_id']."'";
				$qrs = @query($qs) or message(db_error());
				$rs = @pg_fetch_assoc($qrs);
				$temp['stock'] = $rs['stock'];
			}
			else
			{
				$qs = "select stock_code, unit1, unit2, unit3, fraction2, fraction3
						 from 
						 	stock where stock_id='".$r['stock_id']."'";
				$qrs = @query($qs) or message(db_error());
				$rs = @pg_fetch_assoc($qrs);
			}
			if ($rs['equivalent_qty'] == 0) $rs['equivalent_qty'] = 1;

			$qty1_order = $r['case_qty']*$rs['equivalent_qty'] + $r['unit_qty'];

			
			$invoice_qty = $qty1_order - $r['qty1_inv'];
			
			//$temp['amount']  = $temp['case_qty']*$temp['cost3'] + $temp['unit_qty']*$temp['cost1'];
			$temp['balance_order'] = $invoice_qty;	
			$temp  += $rs;
			$aRRD[]=$temp;
		}
		
		$r = @pg_fetch_assoc($qr);
		$aRR = $r;
		if ($aRR['account_id'] > 0)
		{
			$q = "select * from account where account_id='".$aRR['account_id']."'";
			$qr = @pg_query($q) or message1(pg_errormessage());
			$r = @pg_fetch_object($qr);

			$aRR['account'] = $r->account;
			$aRR['terms'] = $r->terms;
			$aRR['credit_limit'] = $r->credit_limit;
		}	
	}
}
elseif ($p1 == 'Release' && $aRR['status']!='S')
{
	message('Status must be [ SAVED ] to Release Stocks Receiving Report...');
}
elseif ($p1 == 'Release')
{
	//update to stock ledger
	include_once('stockbalance.php');
	$total_request_qty = $total_issued_qty = $gross_amount = 0;
	$c=0;
	$ok=true;
	foreach ($aRRD as $temp)
	{
		$qty = $temp['qty1'];
		$price = $temp['price'];
		$total_request_qty += $qty;
		$ok=true;
		
		$stkled = stockBalance($temp['stock_id']);
		$balance_qty = $stkled['balance_qty'];
		
		$total_rr_qty_out = $amount_out =  0;
		$rr_qty_out = $qty_out = $qty_balance = 0;
		if ($balance_qty >= $qty)
		{
			$rr_qty_out = $qty;
		}
		else
		{
			$rr_qty_out = $balance_qty;
		}
		$amount_out += $rr_qty_out*$price;
		$total_rr_qty_out += $rr_qty_out;

		$total_issued_qty += $total_rr_qty_out;
		$gross_amount  += $amount_out;	
		$q = "update rr_detail set 
					qty_out='$total_rr_qty_out', 
					amount_out = '$amount_out',
					stockledger_id_array='$stockledger_id_array'
				where
					rr_detail_id='".$temp['rr_detail_id']."'";
		$qr = @query($q) or message(db_error()." Error updating Issued Quantities...Item :".$temp['stock']);
		if (!$qr)
		{
			$ok = false;
		}
		else
		{
			commit();
			$dummy = $temp;
			
			if ($total_rr_qty_out > 0)
			{
				$dummy['qty_out'] = $total_rr_qty_out;
				$dummy['amount_out'] = $amount_out;
				$dummy['price_out'] = $amount_out/$total_rr_qty_out;
			}
			else
			{
				$dummy['qty_out'] = 0;
				$dummy['amount_out'] = 0;
				$dummy['price_out'] = 0;
			}	
			$aRRD[$c] = $dummy;
		}	
		$c++;
	}
	if ($ok)
	{
		$aRR['gross_amount'] = $gross_amount;
		$aRR['discount_amount'] = round($aRR['gross_amount'] * $aRR['discount_percent']/100,2);
		$aRR['net_amount'] = $aRR['gross_amount'] - $aRR['discount_amount'];
		
		if ($aRR['date_released'] == '')
		{
			$aRR['date_released'] = date('Y-m-d');
		}
		
		$q = "update rr_header set 
					status='A', 
					date_released = '".$aRR['date_released']."',
					gross_amount = '".$aRR['gross_amount']."',
					discount_amount = '".$aRR['discount_amount']."',
					net_amount = '".$aRR['net_amount']."'
			 where 
			 		rr_header_id='".$aRR['rr_header_id']."'";
		@query($q) or message('Unable to update Stocks Receiving Report Status '.db_error());
		$aRR['status']='A';
		if ($total_request_qty ==  $total_issued_qty)
			message("Stocks Successfully Released...");
		else
			message("Stocks Successfully Released with UNSERVED Items.");
	}
}
elseif ($p1 == 'Print' && !in_array($aRR['status'], array('A','P','S')))
{
	message("Cannot Print. <b>Save</b> Stocks Receiving Report Before Printing...");
}
elseif ($p1 == 'Print' && $SYSCONF['RR_FORMAT']=='LEC')
{
	//Stocks Receiving Report
	$q = "";
	$q .= space(4).adjustSize(strtoupper($SYSCONF['BUSINESS_NAME']),82);
	$q .= "STOCKS RECEIVING REPORT\n";
	$q .= space(4).adjustSize($SYSCONF['BUSINESS_ADDR'],82);	
	$q .= 'SRR No.: '.str_pad($aRR['rr_header_id'],8,'0',str_pad_left)."\n\n";
	
	$q .= space(4)."Supplier : [".$aRR['account_code'].'] '.adjustSize($aRR['account'],70).' '. "Date  : ".ymd2mdy($aRR['date'])."\n";
	$q .= "<small3>";
	$q .= space(4).str_repeat('-',128)."\n";
	$q .= space(4)."  Received  Quantity       Barcode          Item Description                                     UnPrice   UnCost    Amount  \n";
	$q .= space(4).str_repeat('-',128)."\n";
	$c=0;

	foreach ($aRRD as $temp)
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
	$sub_wo_freight = $aRR['gross_amount'] - $aRR['discount_amount'];

	$q .= adjustSize(space(5).adjustSize($c.' Line(s)',60),96).
				'GROSS AMOUNT   :       '.adjustRight(number_format($aRR['gross_amount'],2),12)."\n";
	$q .= space(96).
				'DISCOUNT       :       '.adjustRight(number_format($aRR['discount_amount'],2),12)."\n";
	$q .= adjustSize(space(10).adjustSize($ADMIN['name'],30).space(25).center(($aRR['receivedby']*1 == '0' ? str_repeat("_",30): $aRR['receivedby']),30),96).
				'TAX AMOUNT     :       '.adjustRight(number_format($aRR['tax_amount'],2),12)."\n";
	$q .= adjustSize(space(10).adjustSize('Prepared by:',30).space(35)."Received by:",96).
				'SUB-TOTAL      :       '.adjustRight(number_format($aRR['sub_wo_freight'],2),12)."\n";
	$q .= adjustSize(space(10).date('m/d/Y g:ia'),96).
				'FREIGHT AMOUNT :       '.adjustRight(number_format($aRR['freight_amount'],2),12)."\n";
	$q .= space(96).
				'NET AMOUNT     :       '.adjustRight(number_format($aRR['net_amount'],2),12)."\n";

	$q .= space(10).str_repeat("_",30).space(25).str_repeat("_",30)."\n";
	$q .= space(10).adjustSize("Approved for Payment:",30).space(35)."Checked by:\n";
	
	if ($aRR['remark'] != '')
	{
		$q .= "\n".space(5).'Remarks:'.$aRR['remark']."\n";
	}
	$q .= "<reset>\n\n\n\n";
	
//	 echo "<pre>$q</pre>";
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
}
elseif ($p1 == 'Print')
{

	//Stocks Receiving Report
	$q  = "<reset>";
	$q .= "\n";
	$q .= center(strtoupper($SYSCONF['BUSINESS_NAME']),80)."\n";
	$q .= center($SYSCONF['BUSINESS_ADDR'],80)."\n\n";
	$q .= space(29).'STOCKS RECEIVING REPORT'.space(7).' RR No.: '.str_pad($aRR['rr_header_id'],8,'0',str_pad_left)."\n\n";
	
	$q .= space(3)."Supplier : ".adjustSize($aRR['account'],43).' '. "Date  : ".ymd2mdy($aRR['date'])."\n";
	$q .= "<small3>";
	$q .= space(4).str_repeat('-',128)."\n";
	$q .= space(4)."  Cs    Pcs    Barcode          Item Description                                U/C    Freight   Cost/Cs   Cost/Un    Amount  \n";
	$q .= space(4).str_repeat('-',128)."\n";
	$c=0;

	foreach ($aRRD as $temp)
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

		$cqty = '';
		$c++;
		$q .= space(4).adjustRight($case_qty,5).' '.
				adjustRight($unit_qty,6).'   '.
				adjustSize($temp['barcode'],15).'  '.
				adjustSize(stripslashes(substr($temp['stock'],0,50)),45).'  '.
				adjustRight($temp['fraction3'],4).' '.
				adjustRight(number_format($temp['freight_case'],2),10).' '.
				adjustRight(number_format($temp['cost3'],2),10).' '.
				adjustRight(number_format($temp['cost1'],2),10).' '.
				adjustRight(number_format($temp['amount'],2),10)."\n";
	}
	$q .= space(4).str_repeat('-',128)."\n";
	$sub_wo_freight = $aRR['gross_amount'] - $aRR['discount_amount'];

	$q .= adjustSize(space(5).adjustSize($aRR['total_items'].' Item(s)',60),96).
				'GROSS AMOUNT   :       '.adjustRight(number_format($aRR['gross_amount'],2),12)."\n";
	$q .= space(96).
				'DISCOUNT       :       '.adjustRight(number_format($aRR['discount_amount'],2),12)."\n";
	$q .= adjustSize(space(10).adjustSize($ADMIN['name'],30).space(25).str_repeat("_",30),96).
				'TAX AMOUNT     :       '.adjustRight(number_format($aRR['tax_amount'],2),12)."\n";
	$q .= adjustSize(space(10).adjustSize('Prepared by:',30).space(35)."Received by:",96).
				'SUB-TOTAL AMOUNT :     '.adjustRight(number_format($aRR['sub_wo_freight'],2),12)."\n";
	$q .= adjustSize(space(10).date('m/d/Y g:ia'),96).
				'FREIGHT AMOUNT :       '.adjustRight(number_format($aRR['freight_amount'],2),12)."\n";
	$q .= space(96).
				'NET AMOUNT     :       '.adjustRight(number_format($aRR['net_amount'],2),12)."\n";

	$q .= space(10).str_repeat("_",30).space(25).str_repeat("_",30)."\n";
	$q .= space(10).adjustSize("Approved for Payment:",30).space(35)."Checked by:\n";
	$q .= "<reset>\n\n\n\n";
	
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
}
elseif ($p1 == 'PrintSRP')
{
	//Stocks changeprice Report
	$q  = "<reset>";
	$q .= "\n\n\n\n";
	$q .= center(strtoupper($SYSCONF['BUSINESS_NAME']),80)."\n";
	$q .= center($SYSCONF['BUSINESS_ADDR'],80)."\n\n";
	$q .= space(25).'NEW COMPUTED PRICE LISTING REPORT'.space(5).' RR No.'.str_pad($aRR['rr_header_id'],8,'0',str_pad_left)."\n";
	
	$q .= "Supplier : ".adjustSize($aRR['account'],43).' '. "Date  : ".ymd2mdy($aRR['date'])."\n";
	$q .= str_repeat('-',78)."\n";
	$q .= "  Barcode        Item Description                        Curr Price  New Price \n";
	$q .= str_repeat('-',78)."\n";
	//$header = $q;

	include_once('inventory.func.php');
	$c=0;
	foreach ($aRRD as $temp)
	{
		$qs = "select price1, fraction3, markup from stock where stock_id='".$temp['stock_id']."'";
		$qr = @pg_query($qs);
		$r = @pg_fetch_object($qr);
		
		$cqty = '';
		$c++; 
		
		$temp['disc1'] = $aRR['disc1']; $temp['disc2'] = $aRR['disc2']; $temp['disc3']=$aRR['disc3'];
		$temp['disc1_type'] = $aRR['disc1_type']; $temp['disc2_type'] = $aRR['disc2_type']; $temp['disc3_type']=$aRR['disc3_type'];
		$temp['tax_add'] = $aRR['tax_add'];
		$temp['markup'] = $r->markup;

		$a = newPrice($temp);
		$cost1 = $a['cost1'];
		$newprice1 = round2($a['price1']);

		
		$q .= adjustSize($temp['barcode'],15).' '.
				adjustSize(stripslashes(substr($temp['stock'],0,42)),42).' '.
				adjustRight(number_format($r->price1,2),9).' '.
				adjustRight(number_format($newprice1,2),9)."\n";
	}
	$q .= str_repeat('-',78)."\n";
	$q .= " ** NOTE : This price list is NOT posted.  Encode New Price to effect changes ."."\n";
	$q .= "\n\n";
	$q .= "      ".adjustSize($ADMIN['name'],20)."\n";
	$q .= "      Prepared by: \n";
	$q .= "      ".date('m/d/Y g:ia')."\n\n";
	$q .= "<reset>\n\n\n\n";
	
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
}
elseif ($p1=='CancelConfirm' && !chkRights2('sales','mdelete',$ADMIN['admin_id']))
{
	message('You have [ NO ] permission to cancel sales transaction...');
}
elseif ($p1=='CancelConfirm')
{
	//begin();
	$ok=true;
	@query("select * from rr_header where  rr_header_id='".$aRR['rr_header_id']."' for update");
	
	$q = "update rr_header set status='C' where rr_header_id='".$aRR['rr_header_id']."'";
	$qr = query($q) or message("Cannot Cancel Stocks Receiving Report ".db_error());	
	/*
	if ($qr)
	{
		foreach ($aRRD as $temp)
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
		$aRR['status']='C';
		message(' Stocks Stocks Receiving Report No. ['.str_pad($aRR['rr_header_id'],8,'0',str_pad_left).'] Successfully CANCELLED');
	}
	else
	{
		message('Problem deleting stock ledger data...'.db_error());
	}
}
?><body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<form action="?p=receiving" method="post" name="f1" id="f1" style="margin:0">
  <table width="98%" border="0" align="center" cellpadding="2" cellspacing="1">
    <tr> 
      <td>Search 
        <input name="xSearch" type="text" class="altText" id="xSearch2" value="<?= $xSearch;?>">
        <?= lookUpAssoc('searchby',array('Rec No.'=>'rr_header_id','Invoice'=>'invoice','PO. No.'=>'po_header_id','Supplier'=>'account.account','Supplier Code'=>'account.account_code','Remarks'=>'remarks','Stock description'=>'stock_description'), $searchby);?>
        <input name="p122" type="button" class="altBtn" id="p122" value="Go" onClick="window.location='?p=receiving.browse&p1=Go&xSearch='+xSearch.value+'&searchby='+searchby.value"> 
        <input type="button" class="altBtn" name="p1" id="addnew" value="Add New" onClick="window.location='?p=receiving.new'"> 
        <input name="p12" type="button" class="altBtn" id="p12" value="Browse" onClick="window.location='?p=receiving.browse&p1=Browse'"> 
		<input type="button" class="altBtn" name="Submit232" value="Close" onClick="window.location='?p='"> 
        <hr color="#CC0000" size="1"></td>
    </tr>
  </table>
  <table width="98%" border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#EFEFEF">
    <tr bgcolor="#CCCCCC"> 
      <td height="20" colspan="6"> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"><img src="../graphics/waiting.gif" width="16" height="16"><strong> 
        Stocks Receiving Report Entry</strong></font></td>
      <td height="20" colspan="2" align="center"> <font size="2" face="Times New Roman, Times, serif"> 
        <em> 
        <?= status($aRR['status']);?>
        </em></font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td width="10%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">P.O.#</font></td>
      <td width="15%" nowrap > <input name="po_header_id" type="text" class="altText" id="po_header_id" tabindex="8"  value="<?= $aRR['po_header_id'];?>" size="12"  onKeypress="if(event.keyCode==13) {document.getElementById('account').focus();return false;}">
        <font size="2" face="Verdana, Arial, Helvetica, sans-serif">Invoice
        <input name="invoice" type="text" class="altText" id="invoice" tabindex="8"  value="<?= $aRR['invoice'];?>" size="18"  onKeypress="if(event.keyCode==13) {document.getElementById('disc1').focus();return false;}" onBlur="xajax_checkRRInvoice(xajax.getFormValues('f1'));">
        </font> </td>
      <td width="10%" nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Disc1</font></td>
      <td width="10%" nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="disc1" type="text" class="altText" id="disc1" value="<?=$aRR['disc1'];?>" size="5"  tabindex="4"  style="text-align:right"   onKeypress="if(event.keyCode==13) {document.getElementById('disc2').focus();return false;}" onChange="xajax_rr_subtotal(xajax.getFormValues('f1'))">
        <?= lookUpAssoc('disc1_type',array('AMT'=>'A','%'=>'P'), $aRR['disc1_type']);?>
        </font></td>
      <td width="10%"  nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Add-OnFreight</font></td>
      <td width="10%"  nowrap >
        <input name="freight_add" type="text" class="altText" id="freight_add" value="<?= $aRR['freight_add'];?>" size="16"  tabindex="4"  style="text-align:right"   onKeypress="if(event.keyCode==13) {document.getElementById('tax_add').focus();return false;}" onChange="xajax_rr_subtotal(xajax.getFormValues('f1'))">
        </td>
      <td width=10%" nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Rec 
        No.
        </font></td>
      <td width="25%"  nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= str_pad($aRR['rr_header_id'],8,'0',str_pad_left);?>
        </font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td valign="top"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Supplier</font></td>
      <td nowrap > <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <!-- <input name="account" type="text" class="altText" id="account" onBlur="document.getElementById('wordlist').style.visibility='none'" onFocus="vFocus(this)" value="<?=stripslashes( $aRR['account']);?>" size="35"  tabindex="2"   onKeyUp="xajax_autoCompleteAccount(xajax.getFormValues('f1'));">-->
        <input name="account" type="text" class="altText" id="account" value="<?=stripslashes( $aRR['account']);?>" size="35"  onChange="b1.click()"  tabindex="2"   onKeypress="if(event.keyCode==13) {document.getElementById('b1').focus();return false;}">
        <input name="account_code" type="text" readOnly class="altText" id="account_code" tabindex="8"  value="<?= $aRR['account_code'];?>" size="7"  onKeypress="if(event.keyCode==13) {document.getElementById('disc1').focus();return false;}">
        </font><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="b1" type="button" class="altBtn" id="b1" value="..." onClick="xajax_porder_searchaccount(xajax.getFormValues('f1'));" onBlur="xajax_checkRRInvoice(xajax.getFormValues('f1'))">
        </font> 
        <input name="account_id" type="hidden" id="account_id" value="<?= $aRR['account_id'];?>" size="5" maxlength="30"> 
        <font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; </font> 
      </td>
      <td nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Disc2</font><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
        </font></td>
      <td nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <input name="disc2" type="text" class="altText" id="disc2" value="<?= $aRR['disc2'];?>" size="5"  tabindex="4"  style="text-align:right"  onKeypress="if(event.keyCode==13) {document.getElementById('disc3').focus();return false;}"  onChange="xajax_rr_subtotal(xajax.getFormValues('f1'))">
        <?= lookUpAssoc('disc2_type',array('AMT'=>'A','%'=>'P'), $aRR['disc2_type']);?>
        </font></td>
      <td nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Add-On 
        Tax</font></td>
      <td nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="tax_add" type="text" class="altText" id="tax_add" value="<?= stripslashes( $aRR['tax_add']);?>" size="10"  tabindex="4"  style="text-align:right"   onKeyPress="if(event.keyCode==13) {document.getElementById('searchkey').focus();return false;}" onBlur="xajax_rr_subtotal(xajax.getFormValues('f1'))" onChange="document.getElementById('updatecost').checked=true">
        <?= lookUpAssoc('tax_add_type',array('A'=>'A','%'=>'P'), $aRR['tax_add_type']);?>
        </font></td>
      <td ><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Date </font></td>
      <td nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="date" type="text" class="altText" id="date3" tabindex="8"  value="<?= ymd2mdy($aRR['date']);?>" size="10" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')"  onKeypress="if(event.keyCode==13) {document.getElementById('invoice').focus();return false;}">
        <img src="../graphics/dwn-arrow-grn.gif" onclick="popUpCalendar(this, f1.date, 'mm/dd/yyyy')"> 
        </font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Term </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <?= $aRR['terms'];?>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Disc3</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <input name="disc3" type="text" class="altText" id="disc3" value="<?=  $aRR['disc3'];?>" size="5"  tabindex="4"  style="text-align:right"  onKeypress="if(event.keyCode==13) {document.getElementById('freight').focus();return false;}" onChange="xajax_rr_subtotal(xajax.getFormValues('f1'))">
        <?= lookUpAssoc('disc3_type',array('AMT'=>'A','%'=>'P'), $aRR['disc3_type']);?>
        </font></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr bgcolor="#CCCCCC"> 
      <td colspan="8"><font size="2" face="Times New Roman"><strong><em>Details</em></strong><br>
        </font> <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#EFEFEF">
          <tr valign="top"> 
            <td width="16%" nowrap><font size="1" face="Verdana, Arial, Helvetica, sans-serif">Item 
              <br>
              <input name="searchkey" type="text" class="altText" id="searchkey" value="<?= $searchkey;?>" size="15"  tabindex="10"  onKeypress="if(event.keyCode==13) {document.getElementById('SearchButton').click();document.getElementById('case_qty').focus();return false;}">
              <?= lookUpAssoc('searchitemby',array('Barcode'=>'barcode','Name'=>'stock','Desc'=>'stock_description'),$searchitemby);?>
              <input name="SearchButton" type="button" class="altBtn" id="SearchButton" value="Search" onClick="wait('Please wait. Searching...');xajax_pc_search(xajax.getFormValues('f1'),'rr_select', 'case_qty');"">
              </font></td>
            <td width="22%"><font size="1" face="Verdana, Arial, Helvetica, sans-serif">Description<br>
              <input name="stock" type="text" class="altText" id="stock" value="<?= stripslashes($iRRD['stock']);?>" size="35" readOnly>
              </font></td>
            <td width="4%"><font size="1" face="Verdana, Arial, Helvetica, sans-serif">Cases<br>
              <input name="case_qty" type="text" class="altNum" id="case_qty" value="<?= $iRRD['case_qty'];?>" size="5" onBlur="vCompute()"  onKeypress="if(event.keyCode==13) {document.getElementById('unit_qty').focus();return false;}">
              </font></td>
            <td width="4%" align="center"><font size="1" face="Verdana, Arial, Helvetica, sans-serif">Units</font><font size="1"><br>
              <input name="unit_qty" type="text" class="altText" id="unit_qty" value="<?= $iRRD['unit_qty'];?>" size="5"  tabindex="12"  onBlur="vCompute()"  style="text-align:right"  onKeypress="if(event.keyCode==13) {document.getElementById('cost3').focus();return false;}">
              </font></td>
            <td width="2%"><font size="1" face="Verdana, Arial, Helvetica, sans-serif">CsCost</font><font size="1"><br>
              <input name="cost3" type="text" class="altText" id="cost3" value="<?= $iRRD['cost3'];?>" size="9" onChange="vFrac3();vCompute()"  onKeypress="if(event.keyCode==13) {document.getElementById('cost1').focus();return false;}" style="text-align:right">
              </font></td>
            <td width="1%"><font size="1" face="Verdana, Arial, Helvetica, sans-serif">UnCost<br>
              <input name="cost1" type="text" id="cost1" class="altText" value="<?= $iRRD['cost1'];?>" size="8" onChange="vFrac();vCompute()"  onKeypress="if(event.keyCode==13) {document.getElementById('freight_case').focus();return false;}" style="text-align:right">
              </font></td>
            <td width="1%"><font size="1" face="Verdana, Arial, Helvetica, sans-serif">CsFrt<br>
              <input name="freight_case" type="text" id="freight_case" class="altText" value="<?= $iRRD['freight_case'];?>" size="7" onChange="vCompute()"  onKeypress="if(event.keyCode==13) {document.getElementById('amount').focus();return false;}" style="text-align:right">
              </font></td>
            <td width="1%"><font size="1" face="Verdana, Arial, Helvetica, sans-serif">Amount</font><font size="1"><br>
              <input name="amount" type="text" class="altText" id="amount" value="<?= number_format($iRRD['amount'],2);?>" size="10"  style="text-align:right"  onChange="vAmount()"  onKeypress="if(event.keyCode==13) {document.getElementById('Ok').focus();return false;}">
              </font></td>
            <td width="49%" valign="middle"> <font size="1"><br>
              <input name="p1" type="submit" class="altBtn" id="Ok" value="Ok">
              <input name="cost2" type="hidden" id="cost2" value="<?= $iRRD['cost2'];?>" size="7">
              <input name="fraction3" type="hidden" id="fraction3" value="<?= $iRRD['fraction3'];?>" size="7">
              <input name="case_order" type="hidden" id="case_order" value="<?= $iRRD['case_order'];?>" size="7">
              <input name="unit_order" type="hidden" id="unit_order" value="<?= $iRRD['unit_order'];?>" size="7">
              <input name="balance_order" type="hidden" id="balance_order" value="<?= $iRRD['balance_order'];?>" size="7">
              </font></td>
          </tr>
        </table></tr>
  </table>
  <table width="98%" border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#EFEFEF">
    <tr bgcolor="#D2DCDF"> 
      <td width="8%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
      <td width="12%" nowrap><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Bar 
        Code</font></strong></td>
      <td width="30%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Description</font></strong></td>
      <td width="6%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Unit</font></strong></td>
      <td width="9%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Order</font></strong></td>
      <td width="5%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Received</font></strong></td>
      <td width="6%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">CsFrt</font></strong></td>
      <td width="10%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">CsCost</font></strong></td>
      <td width="14%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Amount</font></strong></td>
    </tr>
    <?
	$c=0;
	$gross_amount=$freight_amount = 0;
	foreach ($aRRD as $temp)
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
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href="javascript: document.getElementById('f1').action='?p=receiving&p1=Edit&id=<?=$c;?>';document.getElementById('f1').submit()"> 
        <?= $temp['barcode'];?>
        </a> </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="javascript: document.getElementById('f1').action='?p=receiving&p1=Edit&id=<?=$c;?>';document.getElementById('f1').submit()"> 
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
        <?= ($aRR['status'] == 'A' ? number_format($temp['cost3'],2) : number_format($temp['cost3'],2));?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= ($aRR['status'] == 'A' ? number_format($temp['amount_out'],2) : number_format($temp['amount'],2));?>
        </font></td>
    </tr>
    <?

	}

	rr_subtotalCompute($gross_amount);
	?>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="9"> <input name="p1" type="submit" class="altBtn" id="p1" value="Delete Checked" > 
      </td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="9"> <table width="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#EFEFEF">
          <tr bgcolor="#FFFFFF"> 
            <td width="58%" rowspan="5" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Remarks<br>
              </font> <textarea class="altTextArea" name="remark" cols="75" id="remark"><?= $aRR['remark'];?></textarea></td>
            <td width="10%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Gross 
              Amount</font></td>
            <td width="11%" align="right" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
              <strong> 
              <input name="gross_amount" type="text" class="altText" id="gross_amount" value="<?= number_format($aRR['gross_amount'],2);?>" size="10" readOnly  style="text-align:right; font-weight:bold; border:#FFFFFF none">
              </strong> </font></td>
          </tr>
          <tr bgcolor="#FFFFFF"> 
            <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">(-) 
              Total Discount</font></td>
            <td align="right" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
              <strong> 
              <input name="discount_amount" type="text" class="altText" id="discount_amount" value="<?= number_format($aRR['discount_amount'],2);?>" readOnly size="10"  style="text-align:right; font-weight:bold;border:#FFFFFF none">
              </strong> </font></td>
          </tr>
          <tr bgcolor="#FFFFFF"> 
            <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">(+) 
              Tax 
              <?
			  	if ($aRR['tax_add'] == '0')
				{
					echo "Inclusive";
				}
				elseif ($aRR['tax_add_type'] == 'P')
				{
					echo $aRR['tax_add']."% AddOn";
				}
				elseif ($aRR['tax_add_type'] == 'A')
				{
					echo " AddOn";
				}
				?>
              </font></td>
            <td align="right" nowrap><input name="tax_amount" type="text" class="altText" id="tax_amount" value="<?= number_format($aRR['tax_amount'],2);?>" size="10" readOnly  style="text-align:right; font-weight:bold;border:#FFFFFF none"> 
            </td>
          </tr>
          <tr bgcolor="#FFFFFF"> 
            <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Sub-Total</font></td>
            <td align="right" nowrap><input name="sub_wo_freight" type="text" class="altText" id="sub_wo_freight" value="<?= number_format($aRR['sub_wo_freight'],2);?>" size="10" readOnly  style="text-align:right; font-weight:bold;border:#FFFFFF none"></td>
          </tr>
          <tr bgcolor="#FFFFFF"> 
            <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">(+) 
              Freight </font></td>
            <td align="right" nowrap><input name="freight_amount" type="text" class="altText" id="freight_amount" value="<?= number_format($aRR['freight_amount'],2);?>" size="10" readOnly  style="text-align:right; font-weight:bold;border:#FFFFFF none"> 
            </td>
          </tr>
          <tr bgcolor="#FFFFFF"> 
            <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
              Received by: 
              <input name="receivedby" type="text" id="receivedby" value="<?= $aRR['receivedby'];?>" size="35" class="altText">
              </font></td>
            <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Net 
              Amount</font></td>
            <td align="right" nowrap><input name="net_amount" type="text" class="altText" id="net_amount" value="<?= number_format($aRR['net_amount'],2);?>" size="10" readOnly  style="text-align:right; font-weight:bold;border:#FFFFFF none"></td>
          </tr>
        </table></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="7"><table width="1%" border="0" cellpadding="1" cellspacing="1" bgcolor="#CCCCCC">
          <tr bgcolor="#FFFFFF"> 
            <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              <input type="image" src="../graphics/save.jpg" accesskey="S" alt="Save This Claim Form" width="57" height="15" id="Save" onClick="f1.action='?p=receiving&p1=Save';f1.submit();" name="Save">
              </strong></font><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              </strong></font></td>
            <td nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              <input type="image"  accesskey="P" src="../graphics/print.jpg" alt="Print This Claim Form"  onClick="f1.action='?p=receiving&p1=Print';f1.submit();" name="Print" id="Print">
              </strong></font></td>
            <td nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              <input type="image"  accesskey="P" src="../graphics/price.jpg" alt="Print Price List"  onClick="f1.action='?p=receiving&p1=PrintSRP';f1.submit();" name="Print" id="Print">
              </strong></font></td>
            <td nowrap width="25%"> <input type='image' name="Cancel" id="Cancel" onClick="if (confirm('Are you sure to CANCEL Entry?')) {document.getElementById('f1').action='?p=receiving&p1=CancelConfirm'; document.getElementById('f1').submit();}"  src="../graphics/cancel.jpg" alt="Cancel Form" width="77" height="20"> 
            <td nowrap width="25%"> <a href="javascript: document.getElementById('addnew').click()"><img  src="../graphics/new.jpg"  alt="New Claim Form" name="New" width="63" height="20" border="0" id="New" accesskey="N"></a> 
            </td>
          </tr>
        </table>
		
      <td colspan="2"> <font size="2"><strong> 
        <input type="checkbox" value='update' id='updatecost' name='updatecost' <?= ($updatecost== 'update'?'checked':'');?>>
        Update Cost</strong></font></td>
    </tr>
  </table>
</form>
<div id="wordlist" style="position:absolute; border:thin dotted #3300FF; visibility:none; background: #CCCCCC; width:300px; top=0px"></div>
<?
	include_once('xajax_popup.php');

//	include_once('porder.searchaccount.php');
	
if ($focus != '')
{
	echo "<script>document.getElementById('$focus').focus()</script>";
}
else
{
	echo "<script>document.getElementById('searchkey').focus()</script>";
}
?>
