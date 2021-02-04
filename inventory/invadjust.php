<script type="text/javascript" src="../js/jquery-1.3.2.min.js"></script>
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
		if (confirm("Are you sure to CANCEL Stocks Transfer?"))
		{
			document.f1.action="?p=invadjust&p1=CancelConfirm"
		}	
		else
		{
			document.f1.action="?p=invadjust&p1=Cancel"
		}
	}
	else
	{
		document.f1.action="?p=invadjust&p1="+ul.id;
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
if (!session_is_registered("aIA"))
{
	session_register("aIA");
	$aIA=null;
	$aIA=array();
}

if (!session_is_registered("aIAD"))
{
	session_register("aIAD");
	$aIAD=null;
	$aIAD=array();
}
if (!session_is_registered("iIAD"))
{
	session_register("iIAD");
	$iIAD=null;
	$iIAD=array();
}

function genReference()
{
	global $aIA;
	$q = "select * from invoice where ip='invadjust'";
	$qr = @query($q) or message(db_error());
	$r = @fetch_object($qr);

	$sn = $r->invoice+1;
	$reference = date('Y-m').date('d').'-'.str_pad($sn,4,'0',STR_PAD_LEFT);
	$aIA['reference'] = $reference;
	
	$q = "update invoice set invoice='$sn' where ip='invadjust'";
	$qr = @query($q);
}

$p1 = $_REQUEST['p1'];

if ($p1 == ''  && $b1 == '...') $p1='...';

$fields_header = array('date',  'branch_id','remark');


$fields_detail = array('case_qty','unit_qty','fraction2' , 'fraction3', 'amount', 'stock');
$dfld = array('stock_id','case_qty','unit_qty');


if (!in_array($p1, array(null,'Edit','Delete','Print','Load','Serve')))
{

	for ($c=0;$c<count($fields_header);$c++)
	{
		$aIA[$fields_header[$c]] = $_REQUEST[$fields_header[$c]];
		if ($fields_header[$c] == 'date' || $fields_header[$c]=='date_released'|| $fields_header[$c]=='checkdate')
		{
			$aIA[$fields_header[$c]] = mdy2ymd($_REQUEST[$fields_header[$c]]);
		}
		else
		{
			if ($aIA[$fields_header[$c]] == '' && !in_array($fields_header[$c], array('account','remark','invoice')))
			{
				$aIA[$fields_header[$c]] = '0';
			}
		}
	}

	$aIA['gross_amount'] = str_replace(',','',$aIA['gross_amount']);
	$aIA['discount_amount'] = str_replace(',','',$aIA['discount_amount']);
	$aIA['net_amount'] = str_replace(',','',$aIA['net_amount']);

	$aIA['admin_id']=$ADMIN['admin_id'];

	for ($c=0;$c<count($fields_detail);$c++)
	{
		$iIAD[$fields_detail[$c]] = $_REQUEST[$fields_detail[$c]];
		if ($iIAD[$fields_detail[$c]] == '' && !in_array($fields_detail[$c], array('stock')))
		{
			$iIAD[$fields_detail[$c]] = '0';
		}
	}
	if ($iIAD['fraction3'] == '') $iIAD['fraction3'] = 1;
	$iIAD['qty1'] = $iIAD['unit_qty'] + $iIAD['case_qty'] * $iIAD['fraction3'] ;
	
	if ($aIA['account_id'] != '')
	{
		$q = "select terms, credit_limit , address, account , account_code, cardno from account where account_id = '".$aIA['account_id']."'";
		$qr = @query($q) or message(db_error());
		$r = @pg_fetch_assoc($qr);
		if ($r)
		{
			if ($aIA['terms'] == '')
			{
				$aIA['terms'] = $r['terms'];
			}
			$aIA['credit_limit'] = $r['credit_limit'];
			$aIA['address'] = $r['address'];
			$aIA['city'] = $r['city'];
			$aIA['account'] = $r['account'];
			$aIA['account_code'] = $r['account_code'];
		}	
	}	
}

if ($aIA['date'] =='' || $aIA['date'] =='//')
{
	$aIA['date'] = date('Y-m-d');
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
		$iIAD = $r;
		
		if ($iIAD['fraction2'] == 0 || $iIAD['fraction2'] == '') $iIAD['fraction2'] =1 ;
		if ($iIAD['fraction3'] == 0 || $iIAD['fraction3'] == '') $iIAD['fraction3'] =1 ;
		$iIAD['afraction'] = '1;'.$iIAD['fraction2'].';'.$iIAD['fraction3'];
		
		$iIAD['fraction'] = 1;
		$iIAD['price'] = $iIAD['cost1'];
		$iIAD['unit'] = 1;
		
		$p1 = 'searchFound';
		$searchkey = $iIAD['barcode'];
		$focus = 'case_qty';
		if ($r->account_id != $aIA['account_id'])
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
			$iIAD = $r;
			if ($iIAD['fraction2'] == 0 || $iIAD['fraction2'] == '') $iIAD['fraction2'] =1 ;
			if ($iIAD['fraction3'] == 0 || $iIAD['fraction3'] == '') $iIAD['fraction3'] =1 ;
			$iIAD['afraction'] = '1;'.$iIAD['fraction2'].';'.$iIAD['fraction3'];
			
			$iIAD['fraction'] = 1;
			$iIAD['price'] = $iIAD['cost1'];
			$iIAD['unit'] = 1;
			$searchkey = $iIAD['barcode'];
			$focus = 'case_qty';
			$p1 = 'searchFound';
			$focus='case_qty';
		}
	}
}

if ($p1 =='' && in_array($aIA['tender_id'], array('3')))
{
	//update credit limit on refresh
	
}
if ($p1=='Browse')
{
	echo "<script>window.location='?p=invadjust.browse&p1=Browse'</script>";
}
elseif ($p1 == '...')
{
  /*$q = "select * from account where account_code = '$account'  and  account_type_id in ('1','8')  and enable='Y'";
  $qr = @pg_query($q) or message1(pg_errormessage());
  if (@pg_num_rows($qr) > 0)
  {
  	$r = @pg_fetch_object($qr);
	$aIA['account_id'] = $r->account_id;
	$aIA['account'] = $r->account;
	$aIA['terms'] = $r->terms;
	$p1= '...done';
  }*/

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
	$aIA=null;
	$aIA=array();
	$aIAD= null;
	$aIAD = array();
	$q = "select * from invadjust_header where invadjust_header_id='$id'";
	$qr = @query($q) or message(db_error());
	if ($qr)
	{
		$qd = "select * from invadjust_detail where invadjust_header_id='$id'";
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
			$aIAD[]=$temp;
		}
		
		$r = @pg_fetch_assoc($qr);
		$aIA= $r;
		
		if ($aIA['account_id'] > 0)
		{
			/*$q = "select * from account where account_id='".$aIA['account_id']."'";
			$qra = @pg_query($q) or message1(pg_errormessage());
			$r = fetch_object($qra);
			$aIA['account'] = $r->account;
			$aIA['credit_limit'] = $r->credit_limit;
			$aIA['city'] = $r->city;
			$aIA['address'] = $r->address;
			$aIA['account_cide'] = $r->account_code;*/
		}	
	}
}
elseif ($p1 == 'New' or $p1 == 'Add New')
{
	$aIA=null;
	$aIA=array();
	$aIAD= null;
	$aIAD = array();
	$iIAD= null;
	$iIAD = array();
	$aIA['date'] = date('Y-m-d');
}
elseif (in_array($p1,array('Edit','Ok','Save','Update','Search','selectStock','Release') )&& $aIA['status'] == 'A')
{
	message('Editing not allowed. Inventory Adjustment   already Released/Posted');
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
	$iIAD = $r;
	if ($iIAD['fraction2'] == 0 || $iIAD['fraction2'] == '') $iIAD['fraction2'] =1 ;
	if ($iIAD['fraction3'] == 0 || $iIAD['fraction3'] == '') $iIAD['fraction3'] =1 ;
	$iIAD['afraction'] = '1;'.$iIAD['fraction2'].';'.$iIAD['fraction3'];
	
	$iIAD['fraction'] = 1;
	$iIAD['price'] = $iIAD['cost1'];
	$iIAD['unit'] = 1;
	$searchkey = $iIAD['barcode'];
	$focus = 'case_qty';
}
elseif ($p1 == 'Edit' && $id!='')
{
	$iIAD = null;
	$iIAD = array();
	$c=0;
	foreach ($aIAD as $temp)
	{
		$c++;
		if ($id == $c)
		{
			$iIAD = $temp;
			$iIAD['line_ctr'] = $c;
			$searchkey=$temp['barcode'];
			break;
		}
		
	}
	$focus = 'case_qty';

}
elseif ($p1 == 'Ok'  && ($iIAD['stock_id']=='' || $iIAD['barcode'] == ''))
{
	message("[ No Product Specified...]");
}
elseif ($p1 == 'Ok') // && $iIAD['stock_id']!='')
{
	$aIA['status']='M';
	$dummy = null;
	$dummy = array();

	if ($iIAD['line_ctr']  > 0)
	{
			$dummy = $aIAD[$iIAD['line_ctr'] - 1];
			$dummy['case_qty'] = $iIAD['case_qty'];
			$dummy['unit_qty'] = $iIAD['unit_qty'];
			$dummy['cost1'] = $iIAD['cost1'];
			$dummy['cost2'] = $iIAD['cost2'];
			$dummy['cost3'] = $iIAD['cost3'];
			$dummy['amount'] = $iIAD['amount'];
			$dummy['stock'] = $iIAD['stock'];
			$dummy['qty1'] = $iIAD['qty1'];
			$aIAD[$iIAD['line_ctr'] - 1] = $dummy;
	}
	else
	{
			$aIAD[] = $iIAD;
	}

	$iIAD = null;
	$iIAD = array();
	$searchkey='';
}
elseif ($p1 == 'Delete Checked' && count($aChk)>0)
{
	$newarray=null;
	$newarray=array();
	$nctr=0;
	foreach ($aIAD as $temp)
	{
		$nctr++;
		if (in_array($nctr,$aChk))
		{
			$stockledger_id='';
			if ($temp['invadjust_detail_id'] != '')
			{
					$qr = @query("delete from invadjust_detail where invadjust_detail_id='".$temp['invadjust_detail_id']."'") or message(db_error());
					if (pg_affected_rows($qr)>0)
					{
								$deleteok=true;
					}
					else
					{
						$deleteok=false;
						message("FATAL error: Was not able to delete from Inventory Adjustment   detail file...".mysql_error($qr));
					}			
			}
		}
		else
		{
			$newarray[]=$temp;
		}
	}
	$aIAD = $newarray;	
}
elseif ($p1 == 'Save' && !in_array($aIA['status'],array(null,'M','S','P')))
{
	message("Cannot Update Inventory Adjustment  . Data already Received...");
}
elseif ($p1 == 'Save' && count($aIAD) == 0)
{
	message("No items to save...");
}
elseif ($p1 == 'Save'  && $aIA['branch_id']*1 == '0')
{
	message("No Destination Branch Specified.  Please check and Save again...");
}
elseif ($p1 == 'Save')
{

	if ($aIA['invadjust_header_id'] == '')
	{
		$aIA['audit'] = 'Encoded by '.$ADMIN['name'].' on '.date('m/d/Y g:ia').'; ';

		query("begin transaction");
		$time = date('G:i');
		$q = "insert into invadjust_header ( time, admin_id, ip";
		$qq .= ") values ('$time', '".$ADMIN['admin_id']."', '".$_SERVER['REMOTE_ADDR']."'";
		for ($c=0;$c<count($fields_header);$c++)
		{
			if ($fields_header[$c] == 'account') continue;

			$q .= ",".$fields_header[$c];
			$qq .= ",'".$aIA[$fields_header[$c]]."'";
		}
		$q .= $qq.")";
		$qr = @query($q) or message(db_error());

		if ($qr && pg_affected_rows($qr)>0)
		{
			$ok=1;
			$aIA['invadjust_header_id'] = db_insert_id('invadjust_header');

			// insert to si detail
			$c=0;
			foreach ($aIAD as $temp)
			{
				$q = "insert into invadjust_detail (invadjust_header_id";
				$qq = ") values ('".$aIA['invadjust_header_id']."'";
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
					$dummy['invadjust_detail_id'] = db_insert_id('invadjust_detail');
					$aIAD[$c]=$dummy;
				}
				$c++;
			}	
			if ($ok)
			{
				query("commit");
				$aIA['status']='S';
				message(" Inventory Adjustment   Saved...");
			}
			else
			{
				query("rollback transaction");
				message1("Problem Adding To Inventory Adjustment   Details...".db_error().$q);
				$aIA['status']='S';
				$aIA['invadjust_header_id']='';
			}
		}
		else
		{
			message("Cannot Add Record To Inventory Adjustment   Header File...".pg_errormessage().$q);
			query("rollback transaction");
		}
							
	}
	else
	{
		$ok=true;
		$aIA['audit'] = $aIA['audit'].'Updated by '.$ADMIN['name'].' on '.date('m/d/Y g:ia').'; ';
	
		query("begin");	
		$q = "update invadjust_header set invadjust_header_id = '".$aIA['invadjust_header_id']."'";
		for ($c=0;$c<count($fields_header);$c++)
		{
			if ($fields_header[$c] == 'account') continue;

			$q .= ",".$fields_header[$c]."='".$aIA[$fields_header[$c]]."'";
		}
		$q .= " where invadjust_header_id = '".$aIA['invadjust_header_id']."'";

		$qr = @query($q) or message(db_error());
		if ($qr)
		{
			$c=0;
			foreach ($aIAD as $temp)
			{
				if ($temp['invadjust_detail_id'] == '')
				{
					$q = "insert into invadjust_detail (invadjust_header_id";
					$qq = ") values ('".$aIA['invadjust_header_id']."'";
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
					$q = "update invadjust_detail set invadjust_header_id='".$aIA['invadjust_header_id']."'";
					for ($i=0;$i<count($dfld);$i++)
					{
						$q .= ",".$dfld[$i]."='".addslashes($temp[$dfld[$i]])."'";
					}
					$q .= "	where invadjust_detail_id='".$temp['invadjust_detail_id']."'";
					$qr = @query($q) or message(db_error().$q);
				}	
				if (!$qr)
				{
					$ok=false;
					break;
				}
				else
				{
					if ($temp['invadjust_detail_id'] == '')
					{
						$dummy=$temp;
						$dummy['invadjust_detail_id'] = db_insert_id('invadjust_detail');
						$aIAD[$c]=$dummy;
					}
				}
				$c++;
			}	
			if ($ok)
			{
				query("commit");
				message(" Inventory Adjustment   Updated...");
				$aIA['status']='S';
			}
			else
			{
				query("rollback");
				message("Problem Updating To Inventory Adjustment   Details...".db_error());
			}
		}
		else
		{
			message("Cannot Modify Record To Inventory Adjustment   Header File...".db_error().$q);
			query("rollback");
		}
					
	}
	
} elseif ($p1 == 'Print' && !in_array($aIA['status'], array('A','P','S')))  {
	message("Cannot Print. <b>Save</b> Inventory Adjustment   Before Printing...");
}  elseif ($p1=='CancelConfirm' && !chkRights2('invadjust','mdelete',$ADMIN['admin_id'])) {
	message('You have [ NO ] permission to cancel sales transaction...');
}  elseif ($p1=='CancelConfirm') {
	//begin();
	$ok=true;
	@query("select * from invadjust_header where  invadjust_header_id='".$aIA['invadjust_header_id']."' for update");
	
	$q = "update invadjust_header set status='C' where invadjust_header_id='".$aIA['invadjust_header_id']."'";
	$qr = query($q) or message("Cannot Cancel Inventory Adjustment   ".db_error());	
	if (!$qr)
	{
		$ok=false;
	}
	if ($ok)
	{
		commit();
		$aIA['status']='C';
		message(' Stocks Inventory Adjustment   No. ['.str_pad($aIA['invadjust_header_id'],8,'0',STR_PAD_LEFT).'] Successfully CANCELLED');
	}
	else
	{
		message('Problem deleting stock ledger data...'.db_error());
	}
}
?><body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
 <form action="?p=invadjust" method="post" name="f1" id="f1" style="margin:0">

  <table width="97%" border="0" align="center" cellpadding="2" cellspacing="1">
    <tr> 
      <td>Search 
        <input name="xSearch" type="text" class="altText" id="xSearch2" value="<?= $xSearch;?>">
        <?= lookUpAssoc('searchby',array('Rec Id'=>'invadjust_header_id','Branch To'=>'branch.branch','Remarks'=>'remarks','Stock description'=>'stock_description'), $searchby);?>
        <?= lookUpAssoc('show',array('All'=>'','Saved'=>"'S','P'",'Partial Invoiced'=>"'A'",'Invoiced/Served'=>"'I'",'Cancelled'=>"'C'"), stripslashes($show));?>
        <input name="p122" type="button" class="altBtn" id="p122" value="Go" onClick="window.location='?p=invadjust.browse&p1=Go&xSearch='+xSearch.value+'&searchby='+searchby.value"> 
        <input type="submit" class="altBtn" name="p1" value="Add New"> 
        <input name="p12" type="button" class="altBtn" id="p12" value="Browse" onClick="window.location='?p=invadjust.browse&p1=Browse'"> 
		<input type="button" class="altBtn" name="Submit232" value="Close" onClick="window.location='?p='"> <br>
        <hr color="#CC0000"></td>
    </tr>
  </table>
  <table width="97%" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#EFEFEF">
    <tr bgcolor="#CCCCCC"> 
      <td height="19" colspan="2" background="../graphics/table_horizontal.PNG"> 
        <font size="2" face="Verdana, Arial, Helvetica, sans-serif"><img src="../graphics/waiting.gif" width="16" height="16"><strong><font color="#CCCCCC"> 
        Inventory Adjustment Entry</font></strong></font></td>
      <td height="19" colspan="2" align="center" background="../graphics/table_horizontal.PNG"> 
        <font size="2" face="Times New Roman, Times, serif"> <em> 
        <?= status($aIA['status']);?>
        </em></font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td width="116" height="24" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Branch</font></td>
      <td width="507" nowrap ><select name="branch_id" id="branch_id"  style="width:200px">
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
        </select> </td>
      <td width="63" nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif">ST</font><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#.&nbsp; 
        </font></td>
      <td width="277"  nowrap > <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <strong> 
        <?= str_pad($aIA['invadjust_header_id'],8,'0',STR_PAD_LEFT);?>
        </strong></font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td valign="top">&nbsp;</td>
      <td valign="top">&nbsp; </td>
      <td ><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Date</font></td>
      <td ><input name="date" type="text" class="altText" id="date" tabindex="8"  value="<?= ymd2mdy($aIA['date']);?>" size="11" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')"  onKeypress="if(event.keyCode==13) {document.getElementById('reference').focus();return false;}"> 
        <img src="../graphics/dwn-arrow-grn.gif" onClick="popUpCalendar(this, f1.date, 'mm/dd/yyyy')"> 
      </td>
    </tr>
    <tr bgcolor="#CCCCCC"> 
      <td colspan="4"><font size="2" face="Times New Roman"><strong><em>Details</em></strong><br>
        </font> <table width="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#EFEFEF">
          <tr valign="top"> 
            <td width="15%" nowrap><font size="1" face="Verdana, Arial, Helvetica, sans-serif">Item<br>
              <input name="searchkey" type="text" class="altText" id="searchkey" value="<?= $searchkey;?>" size="15"  tabindex="10"  onKeypress="if(event.keyCode==13) { checkInventory(); document.getElementById('SearchButton').click(); return false;}">
              <?= lookUpAssoc('searchitemby',array('Barcode'=>'barcode','Name'=>'stock','Desc'=>'stock_description'),$searchitemby);?>
              <input name="p13" type="button" class="altBtn" id="SearchButton" value="Search" onClick="wait('Please wait. Searching...');xajax_pc_search(xajax.getFormValues('f1'),'invadjust_select', 'case_qty');">
              </font><font size="1">&nbsp; </font></td>
            <td width="21%"><font size="1" face="Verdana, Arial, Helvetica, sans-serif">Description<br>
              <input name="stock" type="text" class="altText" id="stock" value="<?= stripslashes($iIAD['stock']);?>" size="38" readOnly>
              </font></td>
            <td width="4%"><font size="1" face="Verdana, Arial, Helvetica, sans-serif">Cases<br>
              <input name="case_qty" type="text" class="altNum" id="case_qty" value="<?= $iIAD['case_qty'];?>" size="8"  onKeypress="if(event.keyCode==13) {document.getElementById('unit_qty').focus();return false;}">
              </font></td>
            <td width="3%" align="center"><font size="1" face="Verdana, Arial, Helvetica, sans-serif">Units</font><font size="1"><br>
              <input name="unit_qty" type="text" class="altText" id="unit_qty" value="<?= $iIAD['unit_qty'];?>" size="8"  tabindex="12"   style="text-align:right"  onKeypress="if(event.keyCode==13) {document.getElementById('Ok').focus();return false;}">
              </font></td>
            <td width="47%"><font size="1"><br>
              &nbsp; 
	          	<input name="p1" type="submit" class="altBtn" id="Ok" value="Ok">
				<input type='button' onclick="checkInventory()" class='altBtn' value='Check Inventory'>
				<span style='margin-left:8px; font-size:12px; font-weight:bold; font-family:Arial;' id='current_inventory'></span>
              <br>
              <input name="cost2" type="hidden" id="cost2" value="<?= $iIAD['cost2'];?>" size="7">
              <input name="cost1" type="hidden" id="cost1" value="<?= $iIAD['cost1'];?>" size="7">
              <input name="fraction3" type="hidden" id="fraction3" value="<?= $iIAD['fraction3'];?>" size="7">
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
	foreach ($aIAD as $temp)
	{
		if ($aIA['status'] == 'A')
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
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href='?p=invadjust&p1=Edit&id=<?=$c;?>'> 
        <?= $temp['barcode'];?>
        </a> </font></td>
      <td valign="top"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href='?p=invadjust&p1=Edit&id=<?=$c;?>'> 
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
	$aIA['gross_amount']  = $gross_amount;
	$aIA['total_items'] = $c;
	if ($aIA['discount_type']=='P')
	{
		$aIA['discount_amount'] = round($aIA['gross_amount'] *  $aIA['discount']/100,2);
	}
	else
	{
		$aIA['discount_amount'] = $aIA['discount'];
	}
	$aIA['net_amount'] = $aIA['gross_amount'] - $aIA['discount_amount'];
	
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
              </font> <textarea class="altTextArea" name="remark" cols="75" id="remark"><?= stripslashes($aIA['remark']);?></textarea></td>
          </tr>
        </table></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="3"><table width="1%" border="0" cellpadding="1" cellspacing="1" bgcolor="#CCCCCC">
          <tr bgcolor="#FFFFFF"> 
            <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              <input type="image" src="../graphics/save.jpg" accesskey="S" alt="Save This Claim Form" width="57" height="15" id="Save" onClick="f1.action='?p=invadjust&p1=Save';f1.submit();" name="Save">
              </strong></font><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              </strong></font></td>
            <td nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              <input type = "image" src="../graphics/print.jpg"  alt="Print This Claim Form"  name="Print" border="0" id="Print"  accesskey="P" onClick="f1.action='?p=invadjust&p1=Print';f1.submit();">
              </strong></font></td>
            <td nowrap width="25%"> <input type='image' name="Cancel" id="Cancel2" onClick="vSubmit(this)"  src="../graphics/cancel.jpg" alt="Cancel Form" width="77" height="20"> 
            <td nowrap width="25%"> <input type='image' name="New" accesskey="N" id="New" onClick="f1.action='?p=invadjust&p1=New';f1.submit();"  src="../graphics/new.jpg" alt="New Claim Form" width="63" height="20"> 
            </td>
          </tr>
        </table></td>
      <td colspan="5">&nbsp; </td>
    </tr>
  </table>

</form>
<?
if($p1 == "Print"){
?>
<div align="center">
	<iframe id="JOframe" name="JOframe" style="background-color:#FFF; margin:auto;" frameborder="0" width="90%" height="500" src="print_invadjust.php?id=<?=$aIA['invadjust_header_id']?>"></iframe><br />
    <input type="button" value="Print" onClick="printIframe('JOframe');" />
</div>	
<? } ?>
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

<script>
	function checkInventory(){
		var form_data = {
			action : 'checkInventory',
			data : jQuery("input[name='searchkey']").val()
		};

		jQuery.post('api.php',form_data, function (data) {
			if ( data != -1 ) {
				jQuery('#current_inventory').html(data)
			} else {
				alert("Unable to determine barcode");
			}
		});

	}
</script>