<script>
<!--

function vNewPrice()
{
	markup =1*(document.getElementById('markup').value);
	freight_case =1*(document.getElementById('freight_case').value);
	cost1 =1*(document.getElementById('cost1').value);
	
	newprice1 = twoDecimals((1+markup/100)*(cost1 + freight_case));
	document.getElementById('newprice1').value = newprice1
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


if (!chkRights2('changeprice','madd',$ADMIN['admin_id']))
{
	message('You have [ NO ] permission to access this area...');
	exit;
}
//include_once("lib/lib.css.php");
if (!session_is_registered('aCP'))
{
	session_register('aCP');
	$aCP=null;
	$aCP=array();
}

if (!session_is_registered("aCPD"))
{
	session_register("aCPD");
	$aCPD=null;
	$aCPD=array();
}
if (!session_is_registered("iCPD"))
{
	session_register("iRRD");
	$iCPD=null;
	$iCPD=array();
}

$p1 = $_REQUEST['p1'];

if ($p1 == ''  && $b1 == '...') $p1='...';

$fields_header = array('date', 'reference', 'account_id','account','remark');

$fields_detail = array('markup','oldprice1','newprice1', 'cost1','stock','stock_id');
$dfld = array('stock_id','markup','oldprice1','newprice1', 'cost1');
if (!in_array($p1, array(null,'Delete','Print','Load','Serve','Submit', 'Receive')))
{

	for ($c=0;$c<count($fields_header);$c++)
	{
		$aCP[$fields_header[$c]] = $_REQUEST[$fields_header[$c]];
		if ($fields_header[$c] == 'date' || $fields_header[$c]=='date_released'|| $fields_header[$c]=='checkdate')
		{
			$aCP[$fields_header[$c]] = mdy2ymd($_REQUEST[$fields_header[$c]]);
		}
		else
		{
			if ($aCP[$fields_header[$c]] == '' && !in_array($fields_header[$c], array('account','remark','invoice','reference')))
			{
				$aCP[$fields_header[$c]] = '0';
			}
		}
	}

	$aCP['admin_id']=$ADMIN['admin_id'];
	for ($c=0;$c<count($fields_detail);$c++)
	{
		$iCPD[$fields_detail[$c]] = $_REQUEST[$fields_detail[$c]];
		if ($iCPD[$fields_detail[$c]] == '' && !in_array($fields_detail[$c], array('stock')))
		{
			$iCPD[$fields_detail[$c]] = '0';
		}
	}
	
	if ($aCP['account_id'] != '')
	{
		$q = "select terms, credit_limit , address from account where account_id = '".$aCP['account_id']."'";
		$qr = @query($q) or message(db_error());
		$r = @fetch_assoc($qr);
		if ($r)
		{
			$aCP['terms'] = $r['terms'];
			$aCP['credit_limit'] = $r['credit_limit'];
			$aCP['address'] = $r['address'];
		}	
	}	
}
if ($aCP['date'] =='' || $aCP['date'] =='//')
{
	$aCP['date'] = date('Y-m-d');
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
		$iCPD = $r;
		
		if ($iCPD['fraction2'] == 0 || $iCPD['fraction2'] == '') $iCPD['fraction2'] =1 ;
		if ($iCPD['fraction3'] == 0 || $iCPD['fraction3'] == '') $iCPD['fraction3'] =1 ;
		$iCPD['afraction'] = '1;'.$iCPD['fraction2'].';'.$iCPD['fraction3'];
		$iCPD['oldprice1'] = $iCPD['price1'];

		if ($r['account_id'] != $aCP['account_id'])
		{
			message1("Item Belongs to a Different Supplier...");
		}

		$p1 = 'searchFound';
		$searchkey = $iCPD['barcode'];
		$focus = 'newprice1';

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
			$iCPD = $r;
			if ($iCPD['fraction2'] == 0 || $iCPD['fraction2'] == '') $iCPD['fraction2'] =1 ;
			if ($iCPD['fraction3'] == 0 || $iCPD['fraction3'] == '') $iCPD['fraction3'] =1 ;
			$iCPD['afraction'] = '1;'.$iCPD['fraction2'].';'.$iCPD['fraction3'];
			
			$iCPD['fraction'] = 1;
			$iCPD['oldprice1'] = $iCPD['price1'];
			$iCPD['cost1'] = $iCPD['cost1'];
			$iCPD['markup'] = $iCPD['markup'];
			$iCPD['unit'] = 1;
			$searchkey = $iCPD['barcode'];
			$focus = 'newprice1';
			$p1 = 'searchFound';
			$focus='case_qty';
		}
	}
	
	
}

if ($p1=='Browse')
{
	echo "<script>window.location='?p=changeprice.browse&p1=Browse'</script>";
}
elseif ($p1 == '...')
{
  $q = "select * from account where account_code = '$account'  and  account_type_id in ('1','8')  and enable='Y'";
  $qr = @pg_query($q) or message1(pg_errormessage());
  if (@pg_num_rows($qr) > 0)
  {
  	$r = @pg_fetch_object($qr);
	$aCP['account_id'] = $r->account_id;
	$aCP['account'] = $r->account;
	$aCP['terms'] = $r->terms;
	$p1= '...done';
  }

}
elseif ($p1 == 'Load' && $id == '')
{
	message("Nothing to Load...");
}
elseif ($p1 == 'Load')
{
	$aCP=null;
	$aCP=array();
	$aCPD= null;
	$aCPD = array();
	$q = "select * from cp_header where cp_header_id='$id'";
	$qr = @query($q) or message(pg_errormessage());
	if ($qr)
	{
		$qd = "select * from cp_detail where cp_header_id='$id'";
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
			$aCPD[]=$temp;
		}
		
		$r = @pg_fetch_assoc($qr);
		$aCP= $r;

		if ($aCP['account_id'] > 0)
		{
			$q = "select * from account where account_id='".$aCP['account_id']."'";
			$qra = @pg_query($q) or message1(pg_errormessage());
			
			$ra = @pg_fetch_object($qra);
			$aCP['account'] = $ra->account;
			$aCP['credit_limit'] = $ra->credit_limit;
			$aCP['terms'] = $ra->terms;
			$aCP['address'] = $ra->address;
		}	
	}
}
elseif ($p1 == 'New' or $p1 == 'Add New')
{

	$aCP=null;
	$aCP=array();
	$aCPD= null;
	$aCPD = array();
	$iCPD= null;
	$iCPD = array();
	$aCP['date'] = date('Y-m-d');
	

}
elseif (in_array($p1,array('Edit','Ok','Save','Update','Search','selectStock','Release') )&& $aCP['status'] == 'A')
{
	message('Editing not allowed. Stocks changeprice Report already Released/Posted');
}
elseif ($p1 == 'selectStock' && $id != '')
{
	$q = "select 
			stock_id, 
			stock_code, 
			barcode,
			stock,
			price1,
			markup,
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
	
	$iCPD = $r;
	if ($iCPD['fraction2'] == 0 || $iCPD['fraction2'] == '') $iCPD['fraction2'] =1 ;
	if ($iCPD['fraction3'] == 0 || $iCPD['fraction3'] == '') $iCPD['fraction3'] =1 ;
	$iCPD['afraction'] = '1;'.$iCPD['fraction2'].';'.$iCPD['fraction3'];
	
	$iCPD['fraction'] = 1;
	$iCPD['oldprice1'] = $iCPD['price1'];
	$iCPD['cost1'] = $iCPD['cost1'];
	$iCPD['markup'] = $iCPD['markup'];

	$iCPD['unit'] = 1;
	$searchkey = $iCPD['barcode'];
	$focus = 'newprice1';
	$p1 = 'searchFound';
}
elseif ($p1 == 'Edit' && $id!='')
{
	$iCPD = null;
	$iCPD = array();
	$c=0;
	foreach ($aCPD as $temp)
	{
		$c++;
		if ($id == $c)
		{
			$iCPD = $temp;
			$iCPD['line_ctr'] = $c;
			$searchkey=$temp['barcode'];
			break;
		}
		
	}
	$focus = 'case_qty';
	
}
elseif ($p1 == 'Ok' && $iCPD['stock']=='' && $iCPD['amount'] == '')
{
	message("Check: Stock Item Selected and Quantity ..");
	$aCP['status']='M';
}
elseif ($p1 == 'Ok') // && $iCPD['stock_id']!='')
{
	$q = "select * from stock where stock_id='".$iCPD['stock_id']."'";
	$qr=@pg_query($q) or message1(pg_errormessage());
	$r = @pg_fetch_object($qr);
	$iCPD['barcode'] = $r->barcode;

	$aCP['status']='M';
	$dummy = null;
	$dummy = array();

	if ($iCPD['line_ctr']  > 0)
	{
			$dummy = $aCPD[$iCPD['line_ctr'] - 1];

			$dummy['barcode'] = $iCPD['barcode'];
			$dummy['cost1'] = $iCPD['cost1'];
			$dummy['newprice1'] = $iCPD['newprice1'];
			$dummy['oldprice1'] = $iCPD['oldprice1'];
			$dummy['markup'] = $iCPD['markup'];
			$dummy['stock'] = $iCPD['stock'];
			$dummy['stock_id'] = $iCPD['stock_id'];
			$aCPD[$iCPD['line_ctr'] - 1] = $dummy;
	}
	else
	{
			$aCPD[] = $iCPD;
	}
	$iCPD = null;
	$iCPD = array();
	$searchkey='';
}
elseif ($p1 == 'Delete Checked' && count($aChk)>0)
{
	$newArray=null;
	$newArray=array();
	$nctr=0;
	foreach ($aCPD as $temp)
	{
		$nctr++;
		if (in_array($nctr,$aChk))
		{
			if ($temp['cp_detail_id'] != '')
			{
				$deletok=false;
				$qr = @query("delete from cp_detail where cp_detail_id='".$temp['cp_detail_id']."'") or message1(db_error());
				if (@pg_affected_rows($qr)>0)
				{
					$deleteok=true;
				}
				else
				{
					message("FATAL error: Was not able to delete from Stocks changeprice Report detail file...".mysql_error($qr));
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
	$aCPD = $newArray;	
}
elseif ($p1 == 'Save' && !in_array($aCP['status'],array(null,'M','S')))
{
	message("Cannot Update Stocks changeprice Report. Data already Released...");
}
elseif ($p1 == 'Save' && count($aCPD) == 0)
{
	message("No items to save...");
}
elseif ($p1 == 'Save'  && $aCP['account_id'] == '')
{
	message("No Supplier Specified.  Please check and Save again...");
}
elseif ($p1 == 'Save')
{

	if ($aCP['cp_header_id'] == '')
	{
		$aCP['audit'] = 'Encoded by '.$ADMIN['name'].' on '.date('m/d/Y g:ia').'; ';

		query("begin transaction");
		$time = date('G:i');
		$q = "insert into cp_header ( time, admin_id, status, ip ";
		$qq .= ") values ('$time', '".$ADMIN['admin_id']."','S', '".$_SERVER['REMOTE_ADDR']."'";
		for ($c=0;$c<count($fields_header);$c++)
		{
			if ($fields_header[$c] == 'account') continue;
			$q .= ",".$fields_header[$c];
			$qq .= ",'".$aCP[$fields_header[$c]]."'";
		}
		$q .= $qq.")";
		$qr = @query($q) or message1(db_error().$q);

		if ($qr && pg_affected_rows($qr)>0)
		{
			$ok=1;
			$aCP['cp_header_id'] = db_insert_id('cp_header');

			// insert to si detail
			$c=0;
			foreach ($aCPD as $temp)
			{
				$q = "insert into cp_detail (cp_header_id";
				$qq = ") values ('".$aCP['cp_header_id']."'";
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
					$dummy['cp_detail_id'] = db_insert_id('cp_detail');
					$aCPD[$c]=$dummy;
					
				}

				$c++;
			}	
			if ($ok)
			{
				query("commit");
				$aCP['status']='S';
				message(" Stocks changeprice Report Saved...");
			}
			else
			{
				query("rollback transaction");
				message("Problem Adding To Stocks changeprice Report Details...".db_error());
				$aCP['status']='S';
				$aCP['cp_header_id']='';
			}
		}
		else
		{
			$ok=0;
			message("Cannot Add Record To Stocks changeprice Report Header File...");
			query("rollback transaction");
		}
							
	}
	else
	{
		$ok=	1;
		$aCP['audit'] = $aCP['audit'].'Updated by '.$ADMIN['name'].' on '.date('m/d/Y g:ia').'; ';
	
		query("begin");	
		$q = "update cp_header set cp_header_id = '".$aCP['cp_header_id']."'";
		for ($c=0;$c<count($fields_header);$c++)
		{
			if ($fields_header[$c] == 'account') continue;

			$q .= ",".$fields_header[$c]."='".$aCP[$fields_header[$c]]."'";
		}
		$q .= " where cp_header_id = '".$aCP['cp_header_id']."'";

		$qr = @query($q) or message(db_error());
		if ($qr)
		{
			$c=0;
			foreach ($aCPD as $temp)
			{
				if ($temp['cp_detail_id'] == '')
				{

					$q = "insert into cp_detail (cp_header_id";
					$qq = ") values ('".$aCP['cp_header_id']."'";
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
					$q = "update cp_detail set cp_header_id='".$aCP['cp_header_id']."'";
					for ($i=0;$i<count($dfld);$i++)
					{
						$q .= ",".$dfld[$i]."='".addslashes($temp[$dfld[$i]])."'";
					}
					$q .= "	where cp_detail_id='".$temp['cp_detail_id']."'";

					$qr = @query($q) or message(db_error().$q);
				}	
				if (!$qr)
				{
					$ok=false;
					break;
				}
				else
				{
					if ($temp['cp_detail_id'] == '')
					{
						$dummy=$temp;
						$dummy['cp_detail_id'] = db_insert_id('cp_detail');
						$aCPD[$c]=$dummy;
					}
				}
				$c++;
			}	
			if ($ok)
			{
				query("commit");
				message(" Stocks changeprice Report Updated...");
				$aCP['status']='S';
			}
			else
			{
				$ok=0;
				query("rollback");
				message1("Problem Updating To Stocks changeprice Report Details...".db_error().$q);
			}
		}
		else
		{
			$ok=0;
			message("Cannot Modify Record To Stocks changeprice Report Header File...".db_error().$q);
			query("rollback");
		}
					
	}
	if ($ok == '1')
	{
			$audit .="Price change by :".$ADMIN['username']." on ".date('m/d/Y g:ia');
			$sql= "update stock where stock_id in (";
			foreach ($aCPD as $temp)
			{
				$sql .= $temp['stock_id'].",";
				$q = "update stock set price1 = '".$temp['newprice1']."', price3 = ".$temp['newprice1']."*fraction3 ,
									cost1 = '".$temp['cost1']."', cost3 = ".$temp['cost1']."*fraction3 ,
									markup = '".$temp['markup']."'
								where stock_id = '".$temp['stock_id']."'";
				@pg_query($q) or message('Unable to update stock master file...'.pg_errormessage());
			}
			$sql .= ")";
			@audit('changeprice', $sql, $ADMIN['admin_id'], $audit, $aCP['cp_header_id']);

	}
}
elseif ($p1 == 'Print' && !in_array($aCP['status'], array('A','P','S')))
{
	message("Cannot Print. <b>Save</b> Stocks changeprice Report Before Printing...");
}
elseif ($p1 == 'Print')
{
	//Stocks changeprice Report
	$q  = "<reset>";
	$q .= "\n\n\n\n";
	$q .= center(strtoupper($SYSCONF['BUSINESS_NAME']),80)."\n";
	$q .= center($SYSCONF['BUSINESS_ADDR'],80)."\n\n";
	$q .= space(28).'NEW PRICE LISTING REPORT'.space(5).' Record:'.str_pad($aCP['cp_header_id'],8,'0',str_pad_left)."\n";
	
	$q .= "Supplier : ".adjustSize($aCP['account'],43).' '. "Date  : ".ymd2mdy($aCP['date'])."\n";
	$q .= str_repeat('-',78)."\n";
	$q .= "  Barcode        Item Description                                   New Price \n";
	$q .= str_repeat('-',78)."\n";
	//$header = $q;
	$c=0;
	foreach ($aCPD as $temp)
	{
		$qs = "select price1, fraction3 from stock where stock_id='".$temp['stock_id']."'";
		$qr = @pg_query($qs);
		$r = @pg_fetch_object($qr);
		
		$cqty = '';
		$c++;
		$q .= adjustSize($temp['barcode'],15).' '.
				adjustSize(stripslashes(substr($temp['stock'],0,42)),42).' '.
				space(10).
				adjustRight(number_format($temp['newprice1'],2),9)."\n";
//				adjustRight(number_format($temp['oldprice1'],2),9).' '.
	}
	$q .= str_repeat('-',78)."\n";
	$q .= "\n\n";
	$q .= "      ".adjustSize($ADMIN['name'],20)."\n";
	$q .= "      Prepared by: \n";
	$q .= "      ".date('m/d/Y g:ia')."\n\n";
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
elseif ($p1=='CancelConfirm' && !chkRights2('sales','mdelete',$ADMIN['admin_id']))
{
	message('You have [ NO ] permission to cancel sales transaction...');
}
elseif ($p1=='CancelConfirm')
{
	//begin();
	$ok=true;
	@query("select * from cp_header where  cp_header_id='".$aCP['cp_header_id']."' for update");
	
	$q = "update cp_header set status='C' where cp_header_id='".$aCP['cp_header_id']."'";
	$qr = query($q) or message("Cannot Cancel Stocks changeprice Report ".db_error());	
	if ($qr)
	{
		commit();
		$aCP['status']='C';
		message(' Stocks Changeprice Report No. ['.str_pad($aCP['cp_header_id'],8,'0',str_pad_left).'] Successfully CANCELLED');
	}
	else
	{
		message('Problem deleting stock ledger data...'.db_error());
	}
}

if ($p1 == 'searchFound')
{

		$q = "select cost1, disc1, disc1_type, disc2, disc2_type, disc3, disc3_type, rr_detail.freight_case 
					from
						rr_header,
						rr_detail
					where
						rr_header.rr_header_id = rr_detail.rr_header_id and
						rr_header.status!= 'C' and
						rr_detail.stock_id='".$iCPD['stock_id']."'
					order by
						rr_header.date desc
					offset 0 limit 1";
		$qr = @pg_query($q) or message1('Error searching previous SRR information...'.pg_errormessage().$q);
		$r = @pg_fetch_assoc($qr);
		
		if ($r)
		{
			$iCPD += $r;
		}
		$iCPD['cost1'] = $r['cost1'];

		include_once('inventory.func.php');
		$a = newPrice($iCPD);

		$iCPD['cost1'] = $a['cost1'];
		$iCPD['newprice1'] = round2($a['price1']);
		$focus = 'markup';
}
?><body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<form action="?p=changeprice" method="post" name="f1" id="f1" style="margin:0">
  <table width="97%" border="0" align="center" cellpadding="2" cellspacing="1">
    <tr> 
      <td>Search 
        <input name="xSearch" type="text" class="altText" id="xSearch2" value="<?= $xSearch;?>">
        <?= lookUpAssoc('searchby',array('RecNo.'=>'cp_header_id','Reference No.'=>'reference','Supplier'=>'account.account','Supplier Code'=>'account.account_code','Date'=>'date'), $searchby);?>
        <input name="p122" type="button" class="altBtn" id="p122" value="Go" onClick="window.location='?p=changeprice.browse&p1=Go&xSearch='+xSearch.value+'&searchby='+searchby.value"> 
        <input type="button" class="altBtn" name="p1" id="addnew" value="Add New" onClick="window.location='?p=changeprice&p1=New'"> 
        <input name="p12" type="button" class="altBtn" id="p12" value="Browse" onClick="window.location='?p=changeprice.browse&p1=Browse'"> 
		<input type="button" class="altBtn" name="Submit232" value="Close" onClick="window.location='?p='"> 
        <hr color="#CC0000"></td>
    </tr>
  </table>
  <table width="97%" border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#EFEFEF">
    <tr bgcolor="#CCCCCC"> 
      <td height="20" colspan="4"> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"><img src="../graphics/waiting.gif" width="16" height="16"><strong> 
        Stocks Change Selling Price Entry</strong></font></td>
      <td height="20" colspan="2" align="center"> <font size="2" face="Times New Roman, Times, serif"> 
        <em> 
        <?= status($aCP['status']);?>
        </em></font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td width="101" height="25" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Supplier</font></td>
      <td width="409" nowrap > 
        <input name="account" type="text" class="altText" id="account" value="<?=stripslashes( $aCP['account']);?>" size="35"  onChange="b1.click()"  tabindex="2"  onKeypress="if(event.keyCode==13) {document.getElementById('b1').focus();return false;}">
        <font size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <input name="account_code" type="text" readOnly class="altText" id="account_code" tabindex="8"  value="<?= $aCP['account_code'];?>" size="7"  onKeypress="if(event.keyCode==13) {document.getElementById('disc1').focus();return false;}">
        </font> 
        <input name="b1" type="button" class="altBtn" id="b1" value="..." onClick="xajax_porder_searchaccount(xajax.getFormValues('f1'));">
        <input name="account_id" type="hidden" id="account_id" value="<?= $aCP['account_id'];?>" size="5" maxlength="30">
        </td>
      <td width="114"  nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Date</font></td>
      <td width="115"  nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="date" type="text" class="altText" id="date" tabindex="8"  value="<?= ymd2mdy($aCP['date']);?>" size="11" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')"  onKeypress="if(event.keyCode==13) {document.getElementById('invoice').focus();return false;}">
        <img src="../graphics/dwn-arrow-grn.gif" onclick="popUpCalendar(this, f1.date, 'mm/dd/yyyy')"> 
        </font></td>
      <td width="86" nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Rec 
        No.</font><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
        </font></td>
      <td width="141"  nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= str_pad($aCP['cp_header_id'],8,'0',str_pad_left);?>
        </font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td valign="top">&nbsp;</td>
      <td nowrap > <font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
        </font><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
        </font> <font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
        </font> </td>
      <td nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Reference</font></td>
      <td ><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="reference" type="text" class="altText" id="reference" tabindex="8"  value="<?= $aCP['reference'];?>" size="18"  onKeypress="if(event.keyCode==13) {document.getElementById('supplier_id').focus();return false;}">
        </font></td>
      <td >&nbsp;</td>
      <td ><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
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
              <input name="stock" type="text" class="altText" id="stock" value="<?= stripslashes($iCPD['stock']);?>" size="40" readOnly>
              <input name="stock_id" type="hidden" id="stock_id" value="<?= $iCPD['stock_id'];?>" size="10">
              </font></td>
            <td width="4%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">MarkUp<br>
              <input name="markup" type="text" class="altNum"  id="markup" value="<?= $iCPD['markup'];?>" size="8" onKeypress="if(event.keyCode==13) {document.getElementById('cost1').focus();return false;}" onChange="vNewPrice()">
              </font></td>
            <td width="2%" align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">LUCost</font><br> 
              <input name="cost1" type="text" class="altText"   id="cost1" value="<?= $iCPD['cost1'];?>" size="8"  tabindex="12" style="text-align:right"  onKeypress="if(event.keyCode==13) {document.getElementById('freight_case').focus();return false;}"  onChange="vNewPrice()"> 
            </td>
            <td width="2%" align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">CsFrt<br>
              <input name="freight_case" type="text" class="altText"   id="freight_case" value="<?= $iCPD['freight_case'];?>" size="8"  tabindex="12" style="text-align:right"  onKeypress="if(event.keyCode==13) {document.getElementById('newprice1').focus();return false;}"  onChange="vNewPrice()">
              </font></td>
            <td width="4%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">OldPrice</font><br> 
              <input name="oldprice1" type="text" class="altText"  readOnly id="oldprice1" value="<?= $iCPD['oldprice1'];?>" size="10" onKeypress="if(event.keyCode==13) {document.getElementById('Ok').focus();return false;}" style="text-align:right"> 
            </td>
            <td width="1%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">New 
              Price </font><br> <input name="newprice1" type="text" class="altText" id="newprice1" value="<?= $iCPD['newprice1'];?>" size="11"  style="text-align:right" onKeypress="if(event.keyCode==13) {document.getElementById('Ok').focus();return false;}"> 
            </td>
            <td width="49%"><br> <input name="p1" type="submit" class="altBtn" id="Ok" value="Ok"> 
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
      <td width="11%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Markup</font></strong></td>
      <td width="11%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">LU 
        Price </font></strong></td>
    </tr>
    <?
		//include_once('stockbalance.php');
	  	$q = "select * 
					from 
						stock 
					where 
						(barcode like '%$searchkey%' or stock ilike '%$searchkey%') and 
						account_id = '".$aCP['account_id']."' and 
						enable='Y' order by lower(stock) offset 0 limit 50";
		$qr = @query($q) or message(db_error());
		$cs = 0;
		while ($r = @fetch_object($qr))
		{
			$cs++;
			//$balance_qty = stockBalance($r->stock_id);
	
	?>
    <tr  onClick="f1.action='?p=changeprice&p1=selectStock&id=<?=$r->stock_id;?>';f1.submit()" bgColor='#FFFFFF' onMouseOver="bgColor='#FFFFCC'" onMouseOut="bgColor='#FFFFFF'"> 
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $cs;?>. </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
	  	<a javascript: "f1.action='?p=changeprice&p1=selectStock&id=<?=$r->stock_id;?>';f1.submit()">
        <?= $r->barcode;?></a>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
	  	<a href= "javascript: f1.action='?p=changeprice&p1=selectStock&id=<?=$r->stock_id;?>';f1.submit();">
        <?= $r->stock.' x'.$r->fraction3."'s";?></a>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $r->unit1;?>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= lookUpTableReturnValue('x','category','category_id','category',$r->category_id);?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <?= number_format($r->markup,2);?></font>
      </td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
	  	<a href= "javascript: f1.action='?p=changeprice&p1=selectStock&id=<?=$r->stock_id;?>';f1.submit();">
	  <?= $r->price1;?></a></font></td>
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
      <td width="9%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Markup</font></strong></td>
      <td width="11%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">LU 
        Cost </font></strong></td>
      <td width="10%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Old 
        Price </font></strong></td>
      <td width="14%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">New 
        Price </font></strong></td>
    </tr>
    <?
	$c=0;
	$gross_amount=0;
	foreach ($aCPD as $temp)
	{
		if ($aCP['status'] == 'A')
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
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href="javascript: document.getElementById('f1').action='?p=changeprice&p1=Edit&id=<?=$c;?>';document.getElementById('f1').submit()"> 
        <?= $temp['barcode'];?>
        </a> </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="javascript: document.getElementById('f1').action='?p=changeprice&p1=Edit&id=<?=$c;?>';document.getElementById('f1').submit()"> 
        <?= stripslashes($temp['stock']);?>
        </a></font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $temp['unit1'];?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $temp['markup'];?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?=number_format($temp['cost1'],2);?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?=number_format($temp['oldprice1'],2);?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?=number_format($temp['newprice1'],2);?>
        </font></td>
    </tr>
    <?
	}
	?>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="8"> <input name="p1" type="submit" class="altBtn" id="p1" value="Delete Checked" > &nbsp;&nbsp;<?= $c;?> Item(s)
      </td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
            <td width="58%" colspan="8" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Remarks<br>
              </font> <textarea class="altTextArea" name="remark" cols="75" id="remark"><?= $aCP['remark'];?></textarea></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="8"><table width="1%" border="0" cellpadding="1" cellspacing="1" bgcolor="#CCCCCC">
          <tr bgcolor="#FFFFFF"> 
            <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              <input type="image" src="../graphics/save.jpg" accesskey="S" alt="Save This Claim Form" width="57" height="15" id="Save" onClick="f1.action='?p=changeprice&p1=Save';f1.submit();" name="Save">
              </strong></font><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              </strong></font></td>
            <td nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              <input type="image"  accesskey="P" src="../graphics/print.jpg" alt="Print This Claim Form"  onClick="f1.action='?p=changeprice&p1=Print';f1.submit();" name="Print" id="Print">
              </strong></font></td>
            <td nowrap width="25%"> <input type='image' name="Cancel" id="Cancel" onClick="if (confirm('Are you sure to CANCEL Entry?')) {document.getElementById('f1').action='?p=changeprice&p1=CancelConfirm'; document.getElementById('f1').submit();}"  src="../graphics/cancel.jpg" alt="Cancel Form" width="77" height="20"> 
            <td nowrap width="25%"> <a href="javascript: document.getElementById('addnew').click()"><img  src="../graphics/new.jpg"  alt="New Claim Form" name="New" width="63" height="20" border="0" id="New" accesskey="N"></a> 
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
?>
