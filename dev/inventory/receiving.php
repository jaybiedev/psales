<script>
<!--
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

$fields_header = array('date', 'invoice', 'gross_amount', 'disc1', 'disc1_type','disc2', 'disc2_type','disc3', 'disc3_type','tax_add', 'tax_amount',
						'net_amount','discount_amount','account_id','account','freight_amount','remark', 'reference','po_header_id');

$fields_detail = array('case_qty','cost1','unit_qty', 'cost2','cost3' ,'fraction2' , 'fraction3', 'amount', 'stock');
$dfld = array('stock_id','barcode','case_qty','unit_qty','cost1', 'cost2', 'cost3' ,'amount');

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
			if ($aRR[$fields_header[$c]] == '' && !in_array($fields_header[$c], array('account','remark','invoice')))
			{
				$aRR[$fields_header[$c]] = '0';
			}
		}
	}
	if ($aRR['reference'] != '' && $aRR['po_header_id'] == '')
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
	
	$aRR['gross_amount'] = str_ireplace(',','',$aRR['gross_amount']);
	$aRR['discount_amount'] = str_ireplace(',','',$aRR['discount_amount']);
	$aRR['net_amount'] = str_ireplace(',','',$aRR['net_amount']);


	$aRR['admin_id']=$ADMIN['admin_id'];
	for ($c=0;$c<count($fields_detail);$c++)
	{
		$iRRD[$fields_detail[$c]] = $_REQUEST[$fields_detail[$c]];
		if ($iRRD[$fields_detail[$c]] == '' && !in_array($fields_detail[$c], array('stock')))
		{
			$iRRD[$fields_detail[$c]] = '0';
		}
	}
	if ($iRRD['fraction3'] == '') $iRRD['fraction3'] = 1;
	$iRRD['qty1'] = $iRRD['unit_qty'] + $iRRD['case_qty'] * $iRRD['fraction3'] ;
	
	if ($aRR['account_id'] != '')
	{
		$q = "select terms, credit_limit , address from account where account_id = '".$aRR['account_id']."'";
		$qr = @query($q) or message(db_error());
		$r = @fetch_assoc($qr);
		if ($r)
		{
			$aRR['terms'] = $r['terms'];
			$aRR['credit_limit'] = $r['credit_limit'];
			$aRR['address'] = $r['address'];
		}	
	}	
}
if ($aRR['date'] =='' || $aRR['date'] =='//')
{
	$aRR['date'] = date('Y-m-d');
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
		$iRRD = $r;
		
		if ($iRRD['fraction2'] == 0 || $iRRD['fraction2'] == '') $iRRD['fraction2'] =1 ;
		if ($iRRD['fraction3'] == 0 || $iRRD['fraction3'] == '') $iRRD['fraction3'] =1 ;
		$iRRD['afraction'] = '1;'.$iRRD['fraction2'].';'.$iRRD['fraction3'];
		
		$iRRD['fraction'] = 1;
		$iRRD['price'] = $iRRD['cost1'];
		$iRRD['unit'] = 1;
		
		$p1 = 'searchFound';
		$searchkey = $iRRD['barcode'];
		$focus = 'case_qty';
		if ($r->account_id != $aRR['account_id'])
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
elseif ($p1 == '...')
{
  $q = "select * from account where account_code = '$account'  and  account_type_id in ('1','8')  and enable='Y'";
  $qr = @pg_query($q) or message1(pg_errormessage());
  if (@pg_num_rows($qr) > 0)
  {
  	$r = @pg_fetch_object($qr);
	$aRR['account_id'] = $r->account_id;
	$aRR['account'] = $r->account;
	$aRR['terms'] = $r->terms;
	$p1= '...done';
  }

}
elseif (($p1 == 'Submit' )&& in_array($source, array('N','')))
{
	//retrieve PO
	$aRR=null;
	$aRR=array();
	$aRRD= null;
	$aRRD = array();
	$iRRD= null;
	$iRRD = array();
	$aRR['date'] = date('Y-m-d');
}
elseif (($p1 == 'Submit' )&& !in_array($source, array('N','')))
{
	//retrieve PO
	$aRR=null;
	$aRR=array();
	$aRRD= null;
	$aRRD = array();
	$iRRD= null;
	$iRRD = array();
	$aRR['date'] = date('Y-m-d');

	$q = "select *
			from 
				po_header 
			where 	
				$source='$document'";

	$qr =@ pg_query($q) or message1(db_error());
	if (!$qr or pg_num_rows($qr)==0)
	{
		message(" Purchase Order NOT found...");
	}
	elseif ($qr)
	{
		$r = @pg_fetch_assoc($qr);
		$aRR = $r;
		$aRR['date'] = date('Y-m-d');
		
		$qd = "select * from po_detail where po_header_id='".$aRR['po_header_id']."'";
		$qdr = @pg_query($qd) or message(db_error());

		while ($r = @pg_fetch_assoc($qdr))
		{
			$temp = $r;
			$temp['case_order'] =  $r['case_qty'];
			$temp['unit_order'] = $r['unit_qty'];
			$temp['case_qty'] = '';
			$temp['unit_qty'] = '';
			$temp['amount'] = '';
		
			$qs = "select stock, stock_description,stock_code, barcode, unit1, unit2, unit3, fraction2, fraction3
					 from 
						stock where stock_id='".$r['stock_id']."'";
			$qrs = @pg_query($qs) or message(db_error());
			$rs = @pg_fetch_assoc($qrs);
			if ($rs['stock_description'] != '')
			{
				$rs['stock'] = $rs['stock_description'];
			}
		
			if ($rs['equivalent_qty'] == 0) $rs['equivalent_qty'] = 1;

			$qty1_order = $r['case_qty']*$rs['equivalent_qty'] + $r['unit_qty'];

			$invoice_qty = $qty1_order - $r['qty1_inv'];
			$temp['balance_order'] = $invoice_qty;	
			$temp  += $rs;
			$aRRD[]=$temp;
		}
		
		if ($aRR['account_id'] > 0)
		{
			$q = "select * from account where account_id='".$aRR['account_id']."'";
			$qra = @pg_query($q) or message1(pg_errormessage());
			
			$ra = @pg_fetch_object($qra);
			$aRR['account'] = $ra->account;
			$aRR['credit_limit'] = $ra->credit_limit;
			$aRR['terms'] = $ra->terms;
			$aRR['address'] = $ra->address;
		}	
		
	}	
}
elseif ($p1 == 'Load' && $id == '')
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
			stock,
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
elseif ($p1 == 'Ok' && $iRRD['stock']=='' && $iRRD['amount'] == '')
{
	message("Check: Stock Item Selected and Quantity ..");
	$aRR['status']='M';
}
elseif ($p1 == 'Ok') // && $iRRD['stock_id']!='')
{
	$aRR['status']='M';
	$dummy = null;
	$dummy = array();
	if ($iRRD['line_ctr']  > 0)
	{
			$dummy = $aRRD[$iRRD['line_ctr'] - 1];

			$dummy['case_qty'] = $iRRD['case_qty'];
			$dummy['unit_qty'] = $iRRD['unit_qty'];
			$dummy['cost1'] = $iRRD['cost1'];
			$dummy['cost2'] = $iRRD['cost2'];
			$dummy['cost3'] = $iRRD['cost3'];
			$dummy['amount'] = $iRRD['amount'];
			$dummy['stock'] = $iRRD['stock'];
			$dummy['qty1'] = $iRRD['qty1'];
			$aRRD[$iRRD['line_ctr'] - 1] = $dummy;
	}
	else
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
elseif ($p1 == 'Save' && $aRR['tender_id'] == '3' && $aRR['account_id'] == '')
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
					$q = "update stock set cost3='$cost3', cost1='$cost1' where stock_id='".$temp['stock_id']."'";
					@pg_query($q) or message1('Error Updating Stocks Master...');
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
/*					if ($aRR['tax_add'] > 0)
					{
						$cost3 = round($temp['cost3']*(1+($aRR['tax_add']/100)),2);
					}
					else
					{
						$cost3 = $temp['cost3'];
					}
					$cost1 = $cost3/ $temp['fraction3'];
					$q = "update stock set cost3='$cost3', cost1='$cost1' where stock_id='".$temp['stock_id']."'";
					@pg_query($q) or message1('Error Updating Stocks Master...');
*/
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
					if ($temp['rr_detail_id'] == '')
					{
						$dummy=$temp;
						$dummy['rr_detail_id'] = db_insert_id('rr_detail');
						$aRRD[$c]=$dummy;
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
									rr_header.po_header_id = '".$aRR['po_header_id']."' 
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
							if ($fraction3 == '0')  $fraction3 = 1;
							$qty1_inv = $r->case_qty*$fraction3 + $r->unit_qty;
							
							$q = "update stock set account_id='".$aRR['account_id']."' where stock_id='$r->stock_id'";
							$qqr = @query($q) or message(db_error());
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
			$temp['case_qty'] = '';
			$temp['unit_qty'] = '';
			$temp['amount'] = '';
		
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
			$r = fetch_object($q);
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
elseif ($p1 == 'Print1')
{
	//Stocks Receiving Report
	$q  = "<reset>";
	$q .= "\n\n\n\n";
	$q .= center(strtoupper($SYSCONF['BUSINESS_NAME']),80)."\n";
	$q .= center($SYSCONF['BUSINESS_ADDR'],80)."\n\n";
	$q .= center('STOCKS RECEIVING REPORT',80)."\n";
	
	$q .= "Supplier  : ".adjustSize($aRR['account'],43).' '. "Rec No.  : ".str_pad($aRR['rr_header_id'],8,'0',str_pad_left)."\n";
	$q .= "Disccount: ".adjustSize($aRR['disc1'].$aRR['disc1_type'].'; '.$aRR['disc2'].$aRR['disc2_type'].'; '.$aRR['disc3'].$aRR['disc3_type'].' %AddOnTax '.$aRR['tax_add'],43).' '. 
							"Date  : ".ymd2mdy($aRR['date'])."\n";
	$q .= "Remark   : ".adjustSize($aRR['remark'],60)."\n";
	
	$q .= "\n";
	//$q .= "<small3>";
	$q .= str_repeat('-',80)."\n";
	$q .= "  Cs   Pcs Barcode          Item Description                   Cost/Cs   Amount  \n";
	$q .= str_repeat('-',80)."\n";
	//$header = $q;
	$c=0;
	foreach ($aRRD as $temp)
	{
		$cqty = '';
		$c++;
		$q .=  	adjustRight($temp['case_qty'],4).' '.
				adjustRight($temp['unit_qty'],4).'  '.
				adjustSize($temp['barcode'],14).'  '.
				adjustSize(stripslashes(substr($temp['stock'],0,32)),32).'  '.
				adjustRight(number_format($temp['cost3'],2),8).' '.
				adjustRight(number_format($temp['amount'],2),10)."\n";
	}
	$q .= str_repeat('-',80)."\n";
	$q .= '                  '.adjustSize($aRR['total_items'].' Item(s)',25).'  '.
				'GROSS AMOUNT   :       '.adjustRight(number_format($aRR['gross_amount'],2),12)."\n";
	$q .= '                  '.adjustSize(' ',25)."  ".
				'DISCOUNT       :       '.adjustRight(number_format($aRR['discount_amount'],2),12)."\n";
	$q .= '                  '.adjustSize(' ',25)."  ".
				'TAX AMOUNT     :       '.adjustRight(number_format($aRR['tax_amount'],2),12)."\n";
	$q .= '                  '.adjustSize(' ',25)."  ".
				'FREIGHT AMOUNT :       '.adjustRight(number_format($aRR['freight_amount'],2),12)."\n";
	$q .= '                  '.adjustSize(' ',25)."  ".
				'NET AMOUNT     :       '.adjustRight(number_format($aRR['net_amount'],2),12)."\n";

	$q .= str_repeat('-',80)."\n";
	$q .= "\n\n";
	$q .= "      ".adjustSize($ADMIN['name'],20).space(07)."_____________________".space(7)."_____________________\n";
	$q .= "      Prepared by: ".space(15)."Checked by:".space(15)."  Received by:\n";
	$q .= "      ".date('m/d/Y g:ia');
	$q .= "\n\n\n\n";
	
//	echo "<pre>$q</pre>";
	if ($SYSCONF['REPORT_PRINTER_TYPE'] == 'DRAFT')
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
	$q .= "\n\n\n\n";
	$q .= center(strtoupper($SYSCONF['BUSINESS_NAME']),80)."\n";
	$q .= center($SYSCONF['BUSINESS_ADDR'],80)."\n\n";
	$q .= center('STOCKS RECEIVING REPORT',80)."\n";
	
	$q .= space(3)."Supplier : ".adjustSize($aRR['account'],43).' '. "Rec No.  : ".str_pad($aRR['rr_header_id'],8,'0',str_pad_left)."\n";
	$q .= space(3)."Disccount: ".adjustSize($aRR['disc1'].$aRR['disc1_type'].'; '.$aRR['disc2'].$aRR['disc2_type'].'; '.$aRR['disc3'].$aRR['disc3_type'].' %AddOnTax '.$aRR['tax_add'],43).' '. 
							"Date  : ".ymd2mdy($aRR['date'])."\n";
	$q .= space(3)."Remark   : ".adjustSize($aRR['remark'],60)."\n";
	
	$q .= "<small3>";
	$q .= space(4).str_repeat('-',125)."\n";
	$q .= space(4)."  Cs    Pcs    Barcode            Item Description                                     U/C    SRP     Cost/Cs       Amount  \n";
	$q .= space(4).str_repeat('-',125)."\n";
	//$header = $q;
	$c=0;
	foreach ($aRRD as $temp)
	{
		$qs = "select price1, fraction3 from stock where stock_id='".$temp['stock_id']."'";
		$qr = @pg_query($qs);
		$r = @pg_fetch_object($qr);
		
		$cqty = '';
		$c++;
		$q .= space(4).adjustRight($temp['case_qty'],5).' '.
				adjustRight($temp['unit_qty'],6).'   '.
				adjustSize($temp['barcode'],16).'  '.
				adjustSize(stripslashes(substr($temp['stock'],0,50)),50).'  '.
				adjustRight($r->fraction3,4).' '.
				adjustRight(number_format($r->price1,2),10).' '.
				adjustRight(number_format($temp['cost3'],2),10).' '.
				adjustRight(number_format($temp['amount'],2),10)."\n";
	}
	$q .= space(4).str_repeat('-',125)."\n";
	$q .= space(34).adjustSize($aRR['total_items'].' Item(s)',55).'  '.
				'GROSS AMOUNT   :       '.adjustRight(number_format($aRR['gross_amount'],2),12)."\n";
	$q .= space(34).adjustSize(' ',55)."  ".
				'DISCOUNT       :       '.adjustRight(number_format($aRR['discount_amount'],2),12)."\n";
	$q .= space(34).adjustSize(' ',55)."  ".
				'TAX AMOUNT     :       '.adjustRight(number_format($aRR['tax_amount'],2),12)."\n";
	$q .= space(34).adjustSize(' ',55)."  ".
				'FREIGHT AMOUNT :       '.adjustRight(number_format($aRR['freight_amount'],2),12)."\n";
	$q .= space(34).adjustSize(' ',55)."  ".
				'NET AMOUNT     :       '.adjustRight(number_format($aRR['net_amount'],2),12)."\n";

//	$q .= str_repeat('-',120)."\n";
	$q .= "\n\n";
	$q .= "      ".adjustSize($ADMIN['name'],20).space(07)."_____________________".space(7)."_____________________\n";
	$q .= "      Prepared by: ".space(15)."Checked by:".space(15)."  Received by:\n";
	$q .= "      ".date('m/d/Y g:ia');
	$q .= "\n\n\n\n";
	
//	echo "<pre>$q</pre>";
	if ($SYSCONF['REPORT_PRINTER_TYPE'] == 'DRAFT')
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
  <table width="97%" border="0" align="center" cellpadding="2" cellspacing="1">
    <tr> 
      <td>Search 
        <input name="xSearch" type="text" class="altText" id="xSearch2" value="<?= $xSearch;?>">
        <?= lookUpAssoc('searchby',array('Rec No.'=>'rr_header_id','Invoice'=>'invoice','PO. No.'=>'po_header_id','Supplier'=>'account.account','Supplier Code'=>'account.account_code','Remarks'=>'remarks','Stock description'=>'stock_description'), $searchby);?>
        <input name="p122" type="button" class="altBtn" id="p122" value="Go" onClick="window.location='?p=receiving.browse&p1=Go&xSearch='+xSearch.value+'&searchby='+searchby.value"> 
        <input type="button" class="altBtn" name="p1" id="addnew" value="Add New" onClick="window.location='?p=receiving.new'"> 
        <input name="p12" type="button" class="altBtn" id="p12" value="Browse" onClick="window.location='?p=receiving.browse&p1=Browse'"> 
		<input type="button" class="altBtn" name="Submit232" value="Close" onClick="window.location='?p='"> 
        <hr color="#CC0000"></td>
    </tr>
  </table>
  <table width="97%" border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#EFEFEF">
    <tr bgcolor="#CCCCCC"> 
      <td height="20" colspan="4"> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"><img src="../graphics/waiting.gif" width="16" height="16"><strong> 
        Stocks Receiving Report Entry</strong></font></td>
      <td height="20" colspan="2" align="center"> <font size="2" face="Times New Roman, Times, serif"> 
        <em> 
        <?= status($aRR['status']);?>
        </em></font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td width="101" height="25" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">P.O.#</font></td>
      <td width="409" nowrap > <input name="po_header_id" type="text" class="altText" id="po_header_id" tabindex="8"  value="<?= $aRR['po_header_id'];?>" size="18"  onKeypress="if(event.keyCode==13) {document.getElementById('supplier_id').focus();return false;}"> 
      </td>
      <td width="114"  nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Date</font></td>
      <td width="115"  nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="date" type="text" class="altText" id="date" tabindex="8"  value="<?= ymd2mdy($aRR['date']);?>" size="11" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')"  onKeypress="if(event.keyCode==13) {document.getElementById('invoice').focus();return false;}">
        <img src="../graphics/dwn-arrow-grn.gif" onclick="popUpCalendar(this, f1.date, 'mm/dd/yyyy')"> 
        </font></td>
      <td width="86" nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Rec 
        No.</font><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
        </font></td>
      <td width="141"  nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= str_pad($aRR['rr_header_id'],8,'0',str_pad_left);?>
        </font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td valign="top"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Supplier</font></td>
      <td nowrap > <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="account" type="text" class="altText" id="account" value="<?=stripslashes( $aRR['account']);?>" size="35"  onChange="b1.click()"  tabindex="2">
        </font><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="b1" type="button" class="altBtn" id="b1" value="..." onClick="f1.action='?p=receiving&b1=...';f1.submit()">
        </font> <input name="account_id" type="hidden" id="account_id" value="<?= $aRR['account_id'];?>" size="5" maxlength="30"> 
        <font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; </font> 
      </td>
      <td nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Invoice</font></td>
      <td ><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="invoice" type="text" class="altText" id="invoice" tabindex="8"  value="<?= $aRR['invoice'];?>" size="15"  onKeypress="if(event.keyCode==13) {document.getElementById('searchkey').focus();return false;}">
        </font></td>
      <td ><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Term</font></td>
      <td ><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $aRR['terms'];?>
        </font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="6"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Disc1</font><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="disc1" type="text" class="altText" id="disc1" value="<?= stripslashes( $aRR['disc1']);?>" size="5"  tabindex="4"  style="text-align:right" >
        <?= lookUpAssoc('disc1_type',array('Amount'=>'A','Percent'=>'P'), $aRR['disc1_type']);?>
        Disc2</font><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="disc2" type="text" class="altText" id="disc2" value="<?= stripslashes( $aRR['disc2']);?>" size="5"  tabindex="4"  style="text-align:right" >
        <?= lookUpAssoc('disc2_type',array('Amount'=>'A','Percent'=>'P'), $aRR['disc2_type']);?>
        Disc3 
        <input name="disc3" type="text" class="altText" id="disc3" value="<?= stripslashes( $aRR['disc3']);?>" size="5"  tabindex="4"  style="text-align:right" >
        <?= lookUpAssoc('disc3_type',array('Amount'=>'A','Percent'=>'P'), $aRR['disc3_type']);?>
        Freight </font><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="discount4" type="text" class="altText" id="discount4" value="<?= stripslashes( $aRR['discount']);?>" size="5"  tabindex="4"  style="text-align:right" >
        %Add-On Tax</font><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="tax_add" type="text" class="altText" id="tax" value="<?= stripslashes( $aRR['tax_add']);?>" size="5"  tabindex="4"  style="text-align:right" >
        </font></td>
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
              <input name="stock" type="text" class="altText" id="stock" value="<?= stripslashes($iRRD['stock']).' x'.$iRRD['fraction3'].'\'s';?>" size="40" readOnly>
              </font></td>
            <td width="4%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Cases<br>
              <input name="case_qty" type="text" class="altNum" id="case_qty" value="<?= $iRRD['case_qty'];?>" size="8" onBlur="vCompute()"  onKeypress="if(event.keyCode==13) {document.getElementById('unit_qty').focus();return false;}">
              </font></td>
            <td width="4%" align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Units</font><br> 
              <input name="unit_qty" type="text" class="altText" id="unit_qty" value="<?= $iRRD['unit_qty'];?>" size="8"  tabindex="12"  onBlur="vCompute()"  style="text-align:right"  onKeypress="if(event.keyCode==13) {document.getElementById('cost1').focus();return false;}"> 
            </td>
            <td width="4%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">CsCost</font><br> 
              <input name="cost3" type="text" class="altText" id="cost3" value="<?= $iRRD['cost3'];?>" size="10" onChange="vCompute()"  onKeypress="if(event.keyCode==13) {document.getElementById('Ok').focus();return false;}" style="text-align:right"> 
            </td>
            <td width="1%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Amount</font><br> 
              <input name="amount" type="text" readOnly class="altText" id="amount" value="<?= $iRRD['amount'];?>" size="11"  style="text-align:right"> 
            </td>
            <td width="49%"><br> <input name="p1" type="submit" class="altBtn" id="Ok" value="Ok"> 
              <input name="cost2" type="hidden" id="cost2" value="<?= $iRRD['cost2'];?>" size="7"> 
              <input name="cost1" type="hidden" id="cost1" value="<?= $iRRD['cost1'];?>" size="7"> 
              <input name="fraction3" type="hidden" id="fraction3" value="<?= $iRRD['fraction3'];?>" size="7"> 
              <input name="case_order" type="hidden" id="case_order" value="<?= $iRRD['case_order'];?>" size="7"> 
              <input name="unit_order" type="hidden" id="unit_order" value="<?= $iRRD['unit_order'];?>" size="7"> 
              <input name="balance_order" type="hidden" id="balance_order" value="<?= $iRRD['balance_order'];?>" size="7"> 
            </td>
          </tr>
        </table></tr>
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
      <td width="11%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">CsCost</font></strong></td>
      <td width="11%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Balance</font></strong></td>
    </tr>
    <?
		//include_once('stockbalance.php');
	  	$q = "select * 
					from 
						stock 
					where 
						(barcode like '%$searchkey%' or stock ilike '%$searchkey%') and 
						account_id = '".$aRR['account_id']."' and 
						enable='Y' order by lower(stock) offset 0 limit 50";
		$qr = @query($q) or message(db_error());
		$cs = 0;
		while ($r = @fetch_object($qr))
		{
			$cs++;
			//$balance_qty = stockBalance($r->stock_id);
	
	?>
    <tr  onClick="f1.action='?p=receiving&p1=selectStock&id=<?=$r->stock_id;?>';f1.submit()" bgColor='#FFFFFF' onMouseOver="bgColor='#FFFFCC'" onMouseOut="bgColor='#FFFFFF'"> 
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $cs;?>. </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
	  	<a javascript: "f1.action='?p=receiving&p1=selectStock&id=<?=$r->stock_id;?>';f1.submit()">
        <?= $r->barcode;?></a>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
	  	<a href= "javascript: f1.action='?p=receiving&p1=selectStock&id=<?=$r->stock_id;?>';f1.submit();">
        <?= $r->stock.' x'.$r->fraction3."'s";?></a>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $r->unit1;?>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= lookUpTableReturnValue('x','category','category_id','category',$r->category_id);?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <?= number_format($r->cost3,2);?></font>
      </td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
	  	<a href= "javascript: f1.action='?p=receiving&p1=selectStock&id=<?=$r->stock_id;?>';f1.submit();">
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
      <td width="11%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Received</font></strong></td>
      <td width="10%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">CsCost</font></strong></td>
      <td width="14%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Amount</font></strong></td>
    </tr>
    <?
	$c=0;
	$gross_amount=0;
	foreach ($aRRD as $temp)
	{
		if ($aRR['status'] == 'A')
		{
			$gross_amount += $temp['amount_out'];
		}
		else
		{
			$gross_amount += $temp['amount'];
		}	
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
        <?= stripslashes($temp['stock'])." x".$temp['fraction3']."'s";?>
        </a></font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $temp['unit1'];?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= intval($temp['case_order']).' : '.$temp['unit_order'].'('.$temp['balance_order'].')';?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= intval($temp['case_qty']).' : '.$temp['unit_qty'];?>
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
	$aRR['gross_amount']  = $sub_gross = $gross_amount;
	$aRR['total_items'] = $c;
	if ($aRR['disc1_type']=='P')
	{
		$aRR['discount_amount'] = round($sub_gross *  $aRR['disc1']/100,2);
	}
	else
	{
		$aRR['discount_amount'] = $aRR['disc1'];
	}
	$sub_gross = $aRR['gross_amount'] - $aRR['discount_amount'];
	if ($aRR['disc2_type']=='P')
	{
		$aRR['discount_amount'] += round($sub_gross *  $aRR['disc2']/100,2);
	}
	else
	{
		$aRR['discount_amount'] += $aRR['disc2'];
	}
	$sub_gross = $aRR['gross_amount'] - $aRR['discount_amount'];
	if ($aRR['disc3_type']=='P')
	{
		$aRR['discount_amount'] += round($sub_gross *  $aRR['disc3']/100,2);
	}
	else
	{
		$aRR['discount_amount'] += $aRR['disc3'];
	}
	$sub_gross = $aRR['gross_amount'] - $aRR['discount_amount'];

	$aRR['net_amount'] = $aRR['gross_amount'] +$aRR['freight_amount'] - $aRR['discount_amount'];

	if ($aRR['tax_add'] >  '0') //add on gross
	{
			$aRR['tax_amount'] = round($aRR['net_amount'] * ($aRR['tax_add']/100),2);
			$aRR['net_amount'] += $aRR['tax_amount'];
	}	
	else //inclusive
	{
			$taxbase = round($aRR['net_amount']/(1 + ($SYSCONF['TAXRATE']/100)),2);
			$aRR['tax_amount'] = $aRR['net_amount'] - $taxbase;
	}
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
                  <td width="7%" nowrap><input name="disc1" type="text" class="altNum" id="disc1" value="<?= $aRR['disc1'];?>" size="3"> 
                    <?= lookUpAssoc('disc1_type',array('Percent'=>'P','Amount'=>'A'),$aRR['disc1_type']);?>
                  </td>
                  <td width="1%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">4</font></td>
                  <td width="5%" nowrap><input name="disc4" type="text" class="altText" id="disc4" value="<?= $aRR['disc4'];?>" size="3" style="text-align:right"> 
                    <?= lookUpAssoc('disc4_type',array('Percent'=>'P','Amount'=>'A'),$aRR['disc4_type']);?>
                  </td>
                </tr>
                <tr bgcolor="#FFFFFF"> 
                  <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;</font></td>
                  <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">2</font></td>
                  <td nowrap><input name="disc2" type="text" class="altText" id="disc2" value="<?= $aRR['disc2'];?>" size="3" style="text-align:right"> 
                    <?= lookUpAssoc('disc2_type',array('Percent'=>'P','Amount'=>'A'),$aRR['disc2_type']);?>
                  </td>
                  <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">5</font></td>
                  <td nowrap><input name="disc5" type="text" class="altText" id="disc43" value="<?= $aRR['disc5'];?>" size="3" style="text-align:right"> 
                    <?= lookUpAssoc('disc5_type',array('Percent'=>'P','Amount'=>'A'),$aRR['disc5_type']);?>
                  </td>
                </tr>
                <tr bgcolor="#FFFFFF"> 
                  <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;</font></td>
                  <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">3</font></td>
                  <td nowrap><input name="disc3" type="text" class="altText" id="disc45" value="<?= $aRR['disc3'];?>" size="3" style="text-align:right"> 
                    <?= lookUpAssoc('disc3_type',array('Percent'=>'P','Amount'=>'A'),$aRR['disc3_type']);?>
                  </td>
                  <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">6</font></td>
                  <td nowrap><input name="disc6" type="text" class="altText" id="disc44" value="<?= $aRR['disc6'];?>" size="3" style="text-align:right"> 
                    <?= lookUpAssoc('disc6_type',array('Percent'=>'P','Amount'=>'A'),$aRR['disc6_type']);?>
                  </td>
                </tr>
              </table> </td>-->
            <td width="58%" rowspan="5" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Remarks<br>
              </font> <textarea class="altTextArea" name="remark" cols="75" id="remark"><?= $aRR['remark'];?></textarea></td>
            <td width="10%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Gross 
              Amount</font></td>
            <td width="11%" align="right" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
              <strong> 
              <input name="gross_amount" type="text" class="altText" id="gross_amount22" value="<?= number_format($aRR['gross_amount'],2);?>" size="10" readOnly  style="text-align:right; font-weight:bold; border:#FFFFFF none">
              </strong> </font></td>
          </tr>
          <tr bgcolor="#FFFFFF"> 
            <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">(-) 
              Total Discount</font></td>
            <td align="right" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
              <strong> 
              <input name="discount_amount" type="text" class="altText" id="discount_amount22" value="<?= number_format($aRR['discount_amount'],2);?>" readOnly size="10"  style="text-align:right; font-weight:bold;border:#FFFFFF none">
              </strong> </font></td>
          </tr>
          <tr bgcolor="#FFFFFF"> 
            <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">(+) 
              Freight</font></td>
            <td align="right" nowrap> <input name="freight_amount" type="text" class="altText" id="freight_amount" value="<?=number_format($aRR['freight_amount'],2);?>" size="10" readOnly  style="text-align:right; font-weight:bold;border:#FFFFFF none"> 
            </td>
          </tr>
          <tr bgcolor="#FFFFFF"> 
            <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">(+) 
              Tax <?= $SYSCONF['TAXRATE'].'% '.( $aRR['tax_add'] > '0' ? 'AddOn' : 'Inclusive');?></font></td>
            <td align="right" nowrap><input name="tax_amount" type="text" class="altText" id="tax_amount" value="<?=number_format($aRR['tax_amount'],2);?>" size="10" readOnly  style="text-align:right; font-weight:bold;border:#FFFFFF none"></td>
          </tr>
          <tr bgcolor="#FFFFFF"> 
            <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Net 
              Amount</font></td>
            <td align="right" nowrap><input name="net_amount" type="text" class="altText" id="net_amount" value="<?=number_format($aRR['net_amount'],2);?>" size="10" readOnly  style="text-align:right; font-weight:bold;border:#FFFFFF none"></td>
          </tr>
        </table></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="8"><table width="1%" border="0" cellpadding="1" cellspacing="1" bgcolor="#CCCCCC">
          <tr bgcolor="#FFFFFF"> 
            <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              <input type="image" src="../graphics/save.jpg" accesskey="S" alt="Save This Claim Form" width="57" height="15" id="Save" onClick="f1.action='?p=receiving&p1=Save';f1.submit();" name="Save">
              </strong></font><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              </strong></font></td>
            <td nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              <input type="image"  accesskey="P" src="../graphics/print.jpg" alt="Print This Claim Form"  onClick="f1.action='?p=receiving&p1=Print';f1.submit();" name="Print" id="Print">
              </strong></font></td>
            <td nowrap width="25%"> <input type='image' name="Cancel" id="Cancel" onClick="if (confirm('Are you sure to CANCEL Entry?')) {document.getElementById('f1').action='?p=receiving&p1=CancelConfirm'; document.getElementById('f1').submit();}"  src="../graphics/cancel.jpg" alt="Cancel Form" width="77" height="20"> 
            <td nowrap width="25%"> <a href="javascript: document.getElementById('addnew').click()"><img  src="../graphics/new.jpg"  alt="New Claim Form" name="New" width="63" height="20" border="0" id="New" accesskey="N"></a> 
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
	echo "<pre>Credit Limit   : P".adjustRight(number_format($aRR['credit_limit'],2),12)."<br>";
	echo "Account Balance: P".adjustRight(number_format($aRR['account_balance'],2),12)."<br>";
	echo "Credit Balance : P".adjustRight(number_format($aRR['credit_limit']-$aRR['account_balance'],2),12)."</pre>";
	
	if ($aRR['credit_limit'] > 0 && ($aRR['net_amount']+$aRR['account_balance'])>$aRR['credit_limit'])
	{
		$net_amount = 'P'.number_format($aRR['net_amount'],2);
		$credit_balance = 'P'.number_format($aRR['credit_limit']-$aRR['account_balance'],2);
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