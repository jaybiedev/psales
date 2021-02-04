<script>
<!--

function vSubmit(ul)
{
	if (ul.name == 'Cancel')
	{
		if (confirm("Are you sure to CANCEL Stocks Transfer?"))
		{
			document.f1.action="?p=stocktransfer&p1=CancelConfirm"
		}	
		else
		{
			document.f1.action="?p=stocktransfer&p1=Cancel"
		}
	}
	else
	{
		document.f1.action="?p=stocktransfer&p1="+ul.id;
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

if (!chkRights2('invadjust','madd',$ADMIN['admin_id']))
{
	message('You have [ NO ] permission to access this area...');
	exit;
}
//include_once("lib/lib.css.php");
if (!session_is_registered("aST"))
{
	session_register("aST");
	$aST=null;
	$aST=array();
}

if (!session_is_registered("aSTD"))
{
	session_register("aSTD");
	$aSTD=null;
	$aSTD=array();
}
if (!session_is_registered("iSTD"))
{
	session_register("iSTD");
	$iSTD=null;
	$iSTD=array();
}

function genReference()
{
	global $aST;
	$q = "select * from invoice where ip='stocktransfer'";
	$qr = @query($q) or message(db_error());
	$r = @fetch_object($qr);

	$sn = $r->invoice+1;
	$reference = date('Y-m').date('d').'-'.str_pad($sn,4,'0',str_pad_left);
	$aST['reference'] = $reference;
	
	$q = "update invoice set invoice='$sn' where ip='stocktransfer'";
	$qr = @query($q);
}

$p1 = $_REQUEST['p1'];

if ($p1 == ''  && $b1 == '...') $p1='...';

$fields_header = array('date',  'branch_id_to', 'remark');


$fields_detail = array('case_qty','unit_qty','fraction2' , 'fraction3', 'amount', 'stock');
$dfld = array('stock_id','case_qty','unit_qty');

$aST['branch_id_from'] = $SYSCONF['BRANCH_ID'];
if (!in_array($p1, array(null,'Edit','Delete','Print','Load','Serve')))
{
	for ($c=0;$c<count($fields_header);$c++)
	{
		$aST[$fields_header[$c]] = $_REQUEST[$fields_header[$c]];
		if ($fields_header[$c] == 'date' || $fields_header[$c]=='date_released'|| $fields_header[$c]=='checkdate')
		{
			$aST[$fields_header[$c]] = mdy2ymd($_REQUEST[$fields_header[$c]]);
		}
		else
		{
			if ($aST[$fields_header[$c]] == '' && !in_array($fields_header[$c], array('account','remark','invoice')))
			{
				$aST[$fields_header[$c]] = '0';
			}
		}
	}
	
	$aST['gross_amount'] = str_replace(',','',$aST['gross_amount']);
	$aST['discount_amount'] = str_replace(',','',$aST['discount_amount']);
	$aST['net_amount'] = str_replace(',','',$aST['net_amount']);

	$aST['admin_id']=$ADMIN['admin_id'];

	for ($c=0;$c<count($fields_detail);$c++)
	{
		$iSTD[$fields_detail[$c]] = $_REQUEST[$fields_detail[$c]];
		if ($iSTD[$fields_detail[$c]] == '' && !in_array($fields_detail[$c], array('stock')))
		{
			$iSTD[$fields_detail[$c]] = '0';
		}
	}
	if ($iSTD['fraction3'] == '') $iSTD['fraction3'] = 1;
	$iSTD['qty1'] = $iSTD['unit_qty'] + $iSTD['case_qty'] * $iSTD['fraction3'] ;
	
	/*
	if ($aST['account_id'] != '')
	{
		$q = "select terms, credit_limit , address, account , account_code, cardno from account where account_id = '".$aST['account_id']."'";
		$qr = @query($q) or message(db_error());
		$r = @pg_fetch_assoc($qr);
		if ($r)
		{
			if ($aST['terms'] == '')
			{				$aST['terms'] = $r['terms'];
			}
			$aST['credit_limit'] = $r['credit_limit'];
			$aST['address'] = $r['address'];
			$aST['city'] = $r['city'];
			$aST['account'] = $r['account'];
			$aST['account_code'] = $r['account_code'];
		}
		
	}	
	*/
}

if ($aST['date'] =='' || $aST['date'] =='//')
{
	$aST['date'] = date('Y-m-d');
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
		$iSTD = $r;
		
		if ($iSTD['fraction2'] == 0 || $iSTD['fraction2'] == '') $iSTD['fraction2'] =1 ;
		if ($iSTD['fraction3'] == 0 || $iSTD['fraction3'] == '') $iSTD['fraction3'] =1 ;
		$iSTD['afraction'] = '1;'.$iSTD['fraction2'].';'.$iSTD['fraction3'];
		
		$iSTD['fraction'] = 1;
		$iSTD['price'] = $iSTD['cost1'];
		$iSTD['unit'] = 1;
		
		$p1 = 'searchFound';
		$searchkey = $iSTD['barcode'];
		$focus = 'case_qty';
		if ($r->account_id != $aST['account_id'])
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
			$iSTD = $r;
			if ($iSTD['fraction2'] == 0 || $iSTD['fraction2'] == '') $iSTD['fraction2'] =1 ;
			if ($iSTD['fraction3'] == 0 || $iSTD['fraction3'] == '') $iSTD['fraction3'] =1 ;
			$iSTD['afraction'] = '1;'.$iSTD['fraction2'].';'.$iSTD['fraction3'];
			
			$iSTD['fraction'] = 1;
			$iSTD['price'] = $iSTD['cost1'];
			$iSTD['unit'] = 1;
			$searchkey = $iSTD['barcode'];
			$focus = 'case_qty';
			$p1 = 'searchFound';
			$focus='case_qty';
		}
	}
}

if ($p1 =='' && in_array($aST['tender_id'], array('3')))
{
	//update credit limit on refresh
	
}
if ($p1=='Browse')
{
	echo "<script>window.location='?p=stocktransfer.browse&p1=Browse'</script>";
}
elseif ($p1 == '...')
{
  $q = "select * from account where account_code = '$account'  and  account_type_id in ('1','8')  and enable='Y'";
  $qr = @pg_query($q) or message1(pg_errormessage());
  if (@pg_num_rows($qr) > 0)
  {
  	$r = @pg_fetch_object($qr);
	$aST['account_id'] = $r->account_id;
	$aST['account'] = $r->account;
	$aST['terms'] = $r->terms;
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
	$aST=null;
	$aST=array();
	$aSTD= null;
	$aSTD = array();
	$q = "select * from stocktransfer_header where stocktransfer_header_id='$id'";
	$qr = @query($q) or message(db_error());
	if ($qr)
	{
		$qd = "select * from stocktransfer_detail where stocktransfer_header_id='$id'";
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
			$aSTD[]=$temp;
		}
		
		$r = @pg_fetch_assoc($qr);
		$aST= $r;
		
		/*
		if ($aST['account_id'] > 0)
		{
			$q = "select * from account where account_id='".$aST['account_id']."'";
			$qra = @pg_query($q) or message1(pg_errormessage());
			$r = fetch_object($qra);
			$aST['account'] = $r->account;
			$aST['credit_limit'] = $r->credit_limit;
			$aST['city'] = $r->city;
			$aST['address'] = $r->address;
			$aST['account_cide'] = $r->account_code;
		}	
		*/
	}
}
elseif ($p1 == 'New' or $p1 == 'Add New')
{
	$aST=null;
	$aST=array();
	$aSTD= null;
	$aSTD = array();
	$iSTD= null;
	$iSTD = array();
	$aST['date'] = date('Y-m-d');
}
elseif (in_array($p1,array('Edit','Ok','Save','Update','Search','selectStock','Release') )&& $aST['status'] == 'A')
{
	message('Editing not allowed. Stock Transfer  already Released/Posted');
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
	$iSTD = $r;
	if ($iSTD['fraction2'] == 0 || $iSTD['fraction2'] == '') $iSTD['fraction2'] =1 ;
	if ($iSTD['fraction3'] == 0 || $iSTD['fraction3'] == '') $iSTD['fraction3'] =1 ;
	$iSTD['afraction'] = '1;'.$iSTD['fraction2'].';'.$iSTD['fraction3'];
	
	$iSTD['fraction'] = 1;
	$iSTD['price'] = $iSTD['cost1'];
	$iSTD['unit'] = 1;
	$searchkey = $iSTD['barcode'];
	$focus = 'case_qty';
}
elseif ($p1 == 'Edit' && $id!='')
{
	$iSTD = null;
	$iSTD = array();
	$c=0;
	foreach ($aSTD as $temp)
	{
		$c++;
		if ($id == $c)
		{
			$iSTD = $temp;
			$iSTD['line_ctr'] = $c;
			$searchkey=$temp['barcode'];
			break;
		}
		
	}
	$focus = 'case_qty';

}
elseif ($p1 == 'Ok'  && ($iSTD['stock_id']=='' || $iSTD['barcode'] == ''))
{
	message("[ No Product Specified...]");
}
elseif ($p1 == 'Ok') // && $iSTD['stock_id']!='')
{
	$aST['status']='M';
	$dummy = null;
	$dummy = array();

	if ($iSTD['line_ctr']  > 0)
	{
			$dummy = $aSTD[$iSTD['line_ctr'] - 1];
			$dummy['case_qty'] = $iSTD['case_qty'];
			$dummy['unit_qty'] = $iSTD['unit_qty'];
			$dummy['cost1'] = $iSTD['cost1'];
			$dummy['cost2'] = $iSTD['cost2'];
			$dummy['cost3'] = $iSTD['cost3'];
			$dummy['amount'] = $iSTD['amount'];
			$dummy['stock'] = $iSTD['stock'];
			$dummy['qty1'] = $iSTD['qty1'];
			$aSTD[$iSTD['line_ctr'] - 1] = $dummy;
	}
	else
	{
			$aSTD[] = $iSTD;
	}

	$iSTD = null;
	$iSTD = array();
	$searchkey='';
}
elseif ($p1 == 'Delete Checked' && count($aChk)>0)
{
	$newarray=null;
	$newarray=array();
	$nctr=0;
	foreach ($aSTD as $temp)
	{
		$nctr++;
		if (in_array($nctr,$aChk))
		{
			$stockledger_id='';
			if ($temp['stocktransfer_detail_id'] != '')
			{
					$qr = @query("delete from stocktransfer_detail where stocktransfer_detail_id='".$temp['stocktransfer_detail_id']."'") or message(db_error());
					if (pg_affected_rows($qr)>0)
					{
								$deleteok=true;
					}
					else
					{
						$deleteok=false;
						message("FATAL error: Was not able to delete from Stock Transfer  detail file...".mysql_error($qr));
					}			
			}
		}
		else
		{
			$newarray[]=$temp;
		}
	}
	$aSTD = $newarray;	
}
elseif ($p1 == 'Save' && !in_array($aST['status'],array(null,'M','S','P')))
{
	message("Cannot Update Stock Transfer . Data already Received...");
}
elseif ($p1 == 'Save' && count($aSTD) == 0)
{
	message("No items to save...");
}
elseif ($p1 == 'Save'  && $aST['branch_id_to']*1 == '0')
{
	message("No Destination Branch Specified.  Please check and Save again...");
}
elseif ($p1 == 'Save'  && $aST['branch_id_to'] == $SYSCONF['BRANCH_ID'])
{
	message("Cannot Transfer to the same branch.  Please check and Save again...");
}
elseif ($p1 == 'Save')
{

	if ($aST['stocktransfer_header_id'] == '')
	{
		$aST['audit'] = 'Encoded by '.$ADMIN['name'].' on '.date('m/d/Y g:ia').'; ';

		query("begin transaction");
		$time = date('G:i');
		$q = "insert into stocktransfer_header ( time, admin_id, ip, branch_id_from ";
		$qq .= ") values ('$time', '".$ADMIN['admin_id']."', '".$_SERVER['REMOTE_ADDR']."', '".$SYSCONF['BRANCH_ID']."'";
		for ($c=0;$c<count($fields_header);$c++)
		{
			if ($fields_header[$c] == 'account') continue;

			$q .= ",".$fields_header[$c];
			$qq .= ",'".$aST[$fields_header[$c]]."'";
		}
		$q .= $qq.")";
		$qr = @query($q) or message(db_error());

		if ($qr && pg_affected_rows($qr)>0)
		{
			$ok=1;
			$aST['stocktransfer_header_id'] = db_insert_id('stocktransfer_header');

			// insert to si detail
			$c=0;
			foreach ($aSTD as $temp)
			{
				$q = "insert into stocktransfer_detail (stocktransfer_header_id";
				$qq = ") values ('".$aST['stocktransfer_header_id']."'";
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
					$dummy['stocktransfer_detail_id'] = db_insert_id('stocktransfer_detail');
					$aSTD[$c]=$dummy;
				}
				$c++;
			}	
			if ($ok)
			{
				query("commit");
				$aST['status']='S';
				message(" Stock Transfer  Saved...");
			}
			else
			{
				query("rollback transaction");
				message1("Problem Adding To Stock Transfer  Details...".db_error().$q);
				$aST['status']='S';
				$aST['stocktransfer_header_id']='';
			}
		}
		else
		{
			message("Cannot Add Record To Stock Transfer  Header File...");
			query("rollback transaction");
		}
							
	}
	else
	{
		$ok=true;
		$aST['audit'] = $aST['audit'].'Updated by '.$ADMIN['name'].' on '.date('m/d/Y g:ia').'; ';
	
		query("begin");	
		$q = "update stocktransfer_header set stocktransfer_header_id = '".$aST['stocktransfer_header_id']."'";
		for ($c=0;$c<count($fields_header);$c++)
		{
			if ($fields_header[$c] == 'account') continue;

			$q .= ",".$fields_header[$c]."='".$aST[$fields_header[$c]]."'";
		}
		$q .= " where stocktransfer_header_id = '".$aST['stocktransfer_header_id']."'";

		$qr = @query($q) or message(db_error());
		if ($qr)
		{
			$c=0;
			foreach ($aSTD as $temp)
			{
				if ($temp['stocktransfer_detail_id'] == '')
				{
					$q = "insert into stocktransfer_detail (stocktransfer_header_id";
					$qq = ") values ('".$aST['stocktransfer_header_id']."'";
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
					$q = "update stocktransfer_detail set stocktransfer_header_id='".$aST['stocktransfer_header_id']."'";
					for ($i=0;$i<count($dfld);$i++)
					{
						$q .= ",".$dfld[$i]."='".addslashes($temp[$dfld[$i]])."'";
					}
					$q .= "	where stocktransfer_detail_id='".$temp['stocktransfer_detail_id']."'";
					$qr = @query($q) or message(db_error().$q);
				}	
				if (!$qr)
				{
					$ok=false;
					break;
				}
				else
				{
					if ($temp['stocktransfer_detail_id'] == '')
					{
						$dummy=$temp;
						$dummy['stocktransfer_detail_id'] = db_insert_id('stocktransfer_detail');
						$aSTD[$c]=$dummy;
					}
				}
				$c++;
			}	
			if ($ok)
			{
				query("commit");
				message(" Stock Transfer  Updated...");
				$aST['status']='S';
			}
			else
			{
				query("rollback");
				message("Problem Updating To Stock Transfer  Details...".db_error());
			}
		}
		else
		{
			message("Cannot Modify Record To Stock Transfer  Header File...".db_error().$q);
			query("rollback");
		}
					
	}
	
}
elseif ($p1 == 'Print' && !in_array($aST['status'], array('A','P','S')))
{
	message("Cannot Print. <b>Save</b> Stock Transfer  Before Printing...");
}
elseif ($p1 == 'Printxx')
{
	echo "<div id='layer.print' position='a'>";
	include_once('stocktransfer.print.php');
	echo "</div>";
}
elseif ($p1 == 'Print')
{
	//Stock Transfer 
	$q  = "";
	
	$q .= 'STOCKS TRANSFER'."\n";
	$q .= adjustSize(strtoupper($SYSCONF['BUSINESS_NAME']),50)." ST No.:".str_pad($aST['stocktransfer_header_id'],8,'0',str_pad_left)."\n";
	$q .= adjustSize($SYSCONF['BUSINESS_ADDR'],50)." Date  :".ymd2mdy($aST['date'])."\n\n";
		
	$q .= "From : ".lookUpTableReturnValue('x','branch','branch_id','branch',$aST['branch_id_from'])."\n";
	$q .= "To   : ".lookUpTableReturnValue('x','branch','branch_id','branch',$aST['branch_id_to'])."\n";
	$q .= str_repeat('-',80)."\n";
	$q .= " Item Code          Item Description                         Ordered Quantity   \n";
	$q .= str_repeat('-',80)."\n";
	$c=0;

	foreach ($aSTD as $temp)
	{
		$c++;
		$q .=  	adjustSize($temp['barcode'],16).' '.
				adjustSize(substr($temp['stock'],0,40),37).' ';
				
		if ($temp['case_qty'] > 0)
		{
			$q .= 	adjustRight(number_format2($temp['case_qty'],0),8).' '.adjustSize($temp['unit3'],3).' ';
		}
		if ($temp['unit_qty'] > 0)
		{
			$q .= adjustRight(number_format2($temp['unit_qty'],2),8).' '.adjustSize($temp['unit1'],3);
		}
		
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
}
elseif ($p1=='CancelConfirm' && !chkRights2('stocktransfer','mdelete',$ADMIN['admin_id']))
{
	message('You have [ NO ] permission to cancel sales transaction...');
}
elseif ($p1=='CancelConfirm')
{
	//begin();
	$ok=true;
	@query("select * from stocktransfer_header where  stocktransfer_header_id='".$aST['stocktransfer_header_id']."' for update");
	
	$q = "update stocktransfer_header set status='C' where stocktransfer_header_id='".$aST['stocktransfer_header_id']."'";
	$qr = query($q) or message("Cannot Cancel Stock Transfer  ".db_error());	
	if (!$qr)
	{
		$ok=false;
	}
	if ($ok)
	{
		commit();
		$aST['status']='C';
		message(' Stocks Stock Transfer  No. ['.str_pad($aST['stocktransfer_header_id'],8,'0',str_pad_left).'] Successfully CANCELLED');
	}
	else
	{
		message('Problem deleting stock ledger data...'.db_error());
	}
}
elseif ($p1 == 'Auto Create Purchcase Order' && $aST['account_id']== '')
{
	message1("No Supplier Account Specified...");
}
elseif ($p1 == 'Auto Create Purchcase Order')
{
	$aSTD=null;
	$aSTD=array();
	@include_once('stockbalance.php');
	$q = "select 
					stock_id,
					category_id,
					barcode,
					stock,
					stock_description,
					unit1,
					unit2,
					unit3,
					cost1,
					cost2,
					cost3,
					fraction2,
					fraction3,
					addfreight_case,
					reorder_level,
					reorder_qty
			 from 
			 		stock 
			 where 
			 		account_id = '".$aST['account_id']."' and 
			 		enable='Y' and 
			 		inventory='Y' 
			 order by 
			 		barcode";
	$qr = @pg_query($q) or message(pg_errormessage());

	while ($r = @pg_fetch_assoc($qr))
	{
		$stkled = @stockBalance($r['stock_id'],'', date('Y-m-d'));
		if ($stkled['balance_qty'] < 0)
		{
			$balance_qty = 0;
		}
		else
		{
			$balance_qty = $stkled['balance_qty'];
		}
		
		if ($r['fraction3'] == '0') 
		{
			$r['fraction3'] = 1;
		}

		$balance_case = $balance_qty /$r['fraction3'];
		//-- reorder level is in case;
		
		$case_qty = $r['reorder_level'] - $balance_case;
		if ($case_qty <= 0) continue;
		
		if ($case_qty > intval($case_qty))
		{
			$case_qty++;
		}

		if ($r['fraction3'] > 1)
		{
			$r['unit'] = ($r['unit3'] == '' ? 'CS' : $r['unit3']);
		}
		else
		{
			$r['unit'] = $r['unit1'];
		}
		if ($r['cost3'] == '0')
		{
			$r['cost3'] = $r['cost1']*$r['fraction3'];
		}
		$r['case_qty'] = round($case_qty,2);
		$r['unit_qty'] = 0;
		
		$r['freight_case'] = $r['addfreight_case'] * $r['case_qty'];
		$r['amount'] = $case_qty*$r['cost3'] + $r['freight_case'];
		if ($r['stock_description'] != '')
		{
			$r['stock'] = $r['stock_description'];
		}
		$aSTD[] = $r;

	}

}
?><body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
 <form action="?p=stocktransfer" method="post" name="f1" id="f1" style="margin:0">

  <table width="97%" border="0" align="center" cellpadding="2" cellspacing="1">
    <tr> 
      <td>Search 
        <input name="xSearch" type="text" class="altText" id="xSearch2" value="<?= $xSearch;?>">
      <?= lookUpAssoc('searchby',array('Rec Id'=>'stocktransfer_header_id','Branch To'=>'branch.branch','Remarks'=>'remarks','Stock description'=>'stock_description'), $searchby);?>
      <?= lookUpAssoc('show',array('All'=>'','Saved'=>"'S','P'",'Partial Invoiced'=>"'A'",'Invoiced/Served'=>"'I'",'Cancelled'=>"'C'"), stripslashes($show));?>
      <input name="p122" type="button" class="altBtn" id="p122" value="Go" onClick="window.location='?p=stocktransfer.browse&p1=Go&xSearch='+xSearch.value+'&searchby='+searchby.value"> 
        <input type="submit" class="altBtn" name="p1" value="Add New"> 
        <input name="p12" type="button" class="altBtn" id="p12" value="Browse" onClick="window.location='?p=stocktransfer.browse&p1=Browse'"> 
		<input type="button" class="altBtn" name="Submit232" value="Close" onClick="window.location='?p='"> <br>
        <hr color="#CC0000"></td>
    </tr>
  </table>
  <table width="97%" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#EFEFEF">
    <tr bgcolor="#CCCCCC"> 
      <td height="19" colspan="2" background="../graphics/table_horizontal.PNG"> 
        <font size="2" face="Verdana, Arial, Helvetica, sans-serif"><img src="../graphics/waiting.gif" width="16" height="16"><strong><font color="#CCCCCC"> 
        Stocks Transfer Entry</font></strong></font></td>
      <td height="19" colspan="2" align="center" background="../graphics/table_horizontal.PNG"> 
        <font size="2" face="Times New Roman, Times, serif"> <em> 
        <?= status($aST['status']);?>
        </em></font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td width="116" height="24" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Transfer 
        to Branch</font></td>
      <td width="507" nowrap > <select name="branch_id_to" id="branch_id" style="width:200px">
          <option value=''>Transfer To Branch (Select)</option>
          <?
	  	$q = "select * from branch where enable='Y' order by branch";
		$qr = @pg_query($q);
		while ($r = @pg_fetch_object($qr))
		{
			if ($r->branch_id == $aST['branch_id_to'])
			{
				echo "<option value='$r->branch_id' selected>$r->branch ($r->branch_code)</option>";	
			}
			else
			{
				echo "<option value='$r->branch_id'>$r->branch ($r->branch_code)</option>";	
			}
		}
	  ?>
        </select></td>
      <td width="63" nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif">ST</font><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#.&nbsp; 
        </font></td>
      <td width="277"  nowrap > <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <strong> 
        <?= str_pad($aST['stocktransfer_header_id'],8,'0',str_pad_left);?>
        </strong></font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td valign="top"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">From</font></td>
      <td valign="top">
	  <select name="branch_id_from" id="branch_id_from" disabled style="width:200px">
          <option>From Branch (Select)</option>
          <?
	  	$q = "select * from branch where enable='Y' order by branch";
		$qr = @pg_query($q);
		while ($r = @pg_fetch_object($qr))
		{
			if ($r->branch_id == $SYSCONF['BRANCH_ID'])
			{
				echo "<option value='$r->branch_id' selected>$r->branch</option>";	
			}
			else
			{
				echo "<option value='$r->branch_id'>$r->branch</option>";	
			}
		}
	  ?>
        </select></td>
      <td ><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Date</font></td>
      <td ><input name="date" type="text" class="altText" id="date" tabindex="8"  value="<?= ymd2mdy($aST['date']);?>" size="11" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')"  onKeypress="if(event.keyCode==13) {document.getElementById('reference').focus();return false;}"> 
        <img src="../graphics/dwn-arrow-grn.gif" onclick="popUpCalendar(this, f1.date, 'mm/dd/yyyy')"> 
      </td>
    </tr>
    <tr bgcolor="#CCCCCC"> 
      <td colspan="4"><font size="2" face="Times New Roman"><strong><em>Details</em></strong><br>
        </font> <table width="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#EFEFEF">
          <tr valign="top"> 
            <td width="15%" nowrap><font size="1" face="Verdana, Arial, Helvetica, sans-serif">Item<br>
              <input name="searchkey" type="text" class="altText" id="searchkey" value="<?= $searchkey;?>" size="15"  tabindex="10"  onKeypress="if(event.keyCode==13) {document.getElementById('SearchButton').click();return false;}">
              <?= lookUpAssoc('searchitemby',array('Barcode'=>'barcode','Name'=>'stock','Desc'=>'stock_description'),$searchitemby);?>
              <input name="p13" type="button" class="altBtn" id="SearchButton" value="Search" onClick="wait('Please wait. Searching...');xajax_pc_search(xajax.getFormValues('f1'),'stocktransfer_select', 'case_qty');">
              </font><font size="1">&nbsp; </font></td>
            <td width="21%"><font size="1" face="Verdana, Arial, Helvetica, sans-serif">Description<br>
              <input name="stock" type="text" class="altText" id="stock" value="<?= stripslashes($iSTD['stock']);?>" size="38" readOnly>
              </font></td>
            <td width="4%"><font size="1" face="Verdana, Arial, Helvetica, sans-serif">Cases<br>
              <input name="case_qty" type="text" class="altNum" id="case_qty" value="<?= $iSTD['case_qty'];?>" size="8"  onKeypress="if(event.keyCode==13) {document.getElementById('unit_qty').focus();return false;}">
              </font></td>
            <td width="3%" align="center"><font size="1" face="Verdana, Arial, Helvetica, sans-serif">Units</font><font size="1"><br>
              <input name="unit_qty" type="text" class="altText" id="unit_qty" value="<?= $iSTD['unit_qty'];?>" size="8"  tabindex="12"   style="text-align:right"  onKeypress="if(event.keyCode==13) {document.getElementById('Ok').focus();return false;}">
              </font></td>
            <td width="47%"><font size="1"><br>
              &nbsp; 
              <input name="p1" type="submit" class="altBtn" id="Ok" value="Ok">
              <br>
              <input name="cost2" type="hidden" id="cost2" value="<?= $iSTD['cost2'];?>" size="7">
              <input name="cost1" type="hidden" id="cost1" value="<?= $iSTD['cost1'];?>" size="7">
              <input name="fraction3" type="hidden" id="fraction3" value="<?= $iSTD['fraction3'];?>" size="7">
              </font></td>
          </tr>
        </table></td>
    </tr>
  </table>
  <table width="97%" border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#EFEFEF">
    <tr bgcolor="#D2DCDF"> 
      <td width="8%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
      <td width="12%" nowrap><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Bar 
        Code</font></strong></td>
      <td width="30%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Description</font></strong></td>
      <td width="6%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Unit</font></strong></td>
      <td width="9%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Cases</font></strong></td>
      <td width="11%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Units</font></strong></td>
      <td width="10%" align="center"><strong></strong></td>
      <td width="14%" align="center"><strong></strong></td>
    </tr>
    <?
	$c=0;
	$gross_amount=0;
	foreach ($aSTD as $temp)
	{
		if ($aST['status'] == 'A')
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
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href='?p=stocktransfer&p1=Edit&id=<?=$c;?>'> 
        <?= $temp['barcode'];?>
        </a> </font></td>
      <td valign="top"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href='?p=stocktransfer&p1=Edit&id=<?=$c;?>'> 
        <?= stripslashes($temp['stock']);?>
        </a></font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $temp['unit1'];?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $temp['case_qty'];?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $temp['unit_qty'];?>
        </font></td>
      <td align="right">&nbsp;</td>
      <td align="right">&nbsp;</td>
    </tr>
    <?
	}
	$aST['gross_amount']  = $gross_amount;
	$aST['total_items'] = $c;
	if ($aST['discount_type']=='P')
	{
		$aST['discount_amount'] = round($aST['gross_amount'] *  $aST['discount']/100,2);
	}
	else
	{
		$aST['discount_amount'] = $aST['discount'];
	}
	$aST['net_amount'] = $aST['gross_amount'] - $aST['discount_amount'];
	
	?>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="8"> <input name="p1" type="submit" class="altBtn" id="p1" value="Delete Checked" > 
      </td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="8"> <table width="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#EFEFEF">
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
            <td width="65%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Remarks<br>
              </font> <textarea class="altTextArea" name="remark" cols="75" id="remark"><?= stripslashes($aST['remark']);?></textarea></td>
          </tr>
        </table></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="3"><table width="1%" border="0" cellpadding="1" cellspacing="1" bgcolor="#CCCCCC">
          <tr bgcolor="#FFFFFF"> 
            <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              <input type="image" src="../graphics/save.jpg" accesskey="S" alt="Save This Claim Form" width="57" height="15" id="Save" onClick="f1.action='?p=stocktransfer&p1=Save';f1.submit();" name="Save">
              </strong></font><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              </strong></font></td>
            <td nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              <input type = "image" src="../graphics/print.jpg"  alt="Print This Claim Form"  name="Print" border="0" id="Print"  accesskey="P" onClick="f1.action='?p=stocktransfer&p1=Print';f1.submit();">
              </strong></font></td>
            <td nowrap width="25%"> <input type='image' name="Cancel" id="Cancel2" onClick="vSubmit(this)"  src="../graphics/cancel.jpg" alt="Cancel Form" width="77" height="20"> 
            <td nowrap width="25%"> <input type='image' name="New" accesskey="N" id="New" onClick="f1.action='?p=stocktransfer&p1=New';f1.submit();"  src="../graphics/new.jpg" alt="New Claim Form" width="63" height="20"> 
            </td>
          </tr>
        </table></td>
      <td colspan="5"><input type="button" name="Button" value="VPN Upload"  onClick="wait('Please wait. Uploading To VPN...');xajax_stocktransferVPNUpload(xajax.getFormValues('f1'));">
        <input type="button" name="Button2" value="Download" ></td>
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
?>