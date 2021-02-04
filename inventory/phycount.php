 <script language="JavaScript" type="text/JavaScript">
<!--
function vOk()
{
	var icase_qty = document.getElementById('icase_qty').value;
	var iunit_qty = document.getElementById('iunit_qty').value;
	var stock_id = document.getElementById('stock_id').value;
	if (icase_qty == '' && iunit_qty == '')
	{
		alert("No Quantity Specified...")
	}
	else if (stock_id == '')
	{
		alert("No Item/Product Specified...");
		return;
	}
	else
	{
		 wait('Please wait. Updating...');
		 xajax_pc_insert(xajax.getFormValues('f1'));
		return false;
	}
	return;
}
function v(ctr)
{
	xajax_pc_select_ctr(ctr);
	return;
}

function SL(sid,barcode,stock,case_qty,unit_qty,cost3,ctr)
{
	document.getElementById('stock_id').value = sid;
	document.getElementById('stock').value = stock;
	document.getElementById('searchkey').value = barcode;
	document.getElementById('icase_qty').value = case_qty;
	document.getElementById('iunit_qty').value = unit_qty;
	document.getElementById('icost3').value = cost3;
	document.getElementById('ctr').value = ctr;
	document.getElementById('icase_qty').focus();
	return;
}

function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);

var	isNS = (navigator.appName	== "Netscape") ? 1 : 0;
var	EnableRightClick = 0;

if(isNS) 
document.captureEvents(Event.MOUSEDOWN||Event.MOUSEUP);
document.onhelp=function(){event.returnValue=false};

document.onkeydown = pc_keyhandler;
pc_focus_id='';
function pc_keyhandler(e) 
{
	var previous_line='';
	var myevent = (isNS) ? e : window.event;
	mycode=myevent.keyCode
	//40 -down  38-up
	if (document.getElementById('browsePLULayer').style.display == 'block')
	{
		return;
	}
	
	if (mycode == 40)
	{
		
		if (pc_focus_id == '')
		{
			pc_focus_id =1;
		}
		else
		{
			previous_line = pc_focus_id
			document.getElementById(focus_fld).style.background='';
			pc_focus_id++;
			if (pc_focus_id %15 == 0)
			{
				xajax_grid(xajax.getFormValues('f1'),'DOWN');
			}
		}
		focus_fld = 'a'+pc_focus_id
		if (document.getElementById(focus_fld) == null)
		{
			pc_focus_id--
			focus_fld = 'a'+pc_focus_id
		}
		document.getElementById(focus_fld).style.background='#AFFCCA';
		document.getElementById(focus_fld).focus()
	}
	else if (mycode == 38)
	{
		//-- down arrow
		if (pc_focus_id == '')
		{
			return
		}
		else
		{
			previous_line = pc_focus_id
			document.getElementById(focus_fld).style.background='';
			pc_focus_id--;
		}
		focus_fld = 'a'+pc_focus_id
		if (document.getElementById(focus_fld) == null)
		{
			pc_focus_id++
			focus_fld = 'a'+pc_focus_id
		}
		document.getElementById(focus_fld).style.background='#AFFCCA';
		document.getElementById(focus_fld).focus()
	}
	else if (mycode == 33)
	{
		//page up
		xajax_grid(xajax.getFormValues('f1'),'UP');		
	}
	else if (mycode == 34)
	{
		//page down
		xajax_grid(xajax.getFormValues('f1'),'DOWN');		
	}
		
}
//-->
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

if (!chkRights2("phycount","mview",$ADMIN['admin_id']))
{
	message("You have no permission in this area (Physical Count)...");
	exit;
}

function checkTable()
{
	global $aPHYC;

	$ip = $_SERVER['REMOTE_ADDR'];
	$aip = explode('.',$ip);
	$table = 'tmp_'.$aip[3];
	$aPHYC['tmp_table'] = $table;

	$q= "select * from $table";
	$qr = @pg_query($q);
	$return = 1;
	if ($qr)
	{
		$q = "drop table $table";
		$qr = @pg_query($q) or message1(pg_errormessage());
	}

	$q = "create table $table (tmp_id bigserial NOT NULL, timestamp varchar, liwat char(1) default 'N' , stock_id bigint, stockledger_id bigint, case_qty numeric, unit_qty numeric, cost3 numeric)";
	$qr = @pg_query($q) or message1(pg_errormessage().$q);
	if (!$qr) $return=0;

	$table_unique_index = $table.'_'.$table.'_stock_id';
	$q = " CREATE UNIQUE INDEX $table_unique_index  ON $table  USING btree  (stock_id)";
	$qr = @pg_query($q) or message1(pg_errormessage().$q);

	if (!$qr) $return=0;
	
	return $return;
}
if (!session_is_registered('aPHYC'))
{
	session_register('aPHYC');
	$aPHYC=null;
	$aPHYC=array();
}
if (!session_is_registered('aPHYCD'))
{
	session_register('aPHYCD');
	$aPHYCD=null;
	$aPHYCD=array();
}

if (!in_array($_REQUEST['p1'] , array('','Load','New')))
{
	$aPHYC['date']  = mdy2ymd($_REQUEST['date']);
	$aPHYC['account_id'] = $_REQUEST['account_id'];
	$aPHYC['category_id'] = $_REQUEST['category_id'];

	
	$searchkey = $_REQUEST['searchkey'];

	$icase_qty = $_REQUEST['icase_qty'];
	$iunit_qty = $_REQUEST['iunit_qty'];
	$cost3 = $_REQUEST['cost3'];
	$stock_id = $_REQUEST['stock_id'];
	
	if ($icase_qty == '') $icase_qty = 0;
	if ($iunit_qty == '') $iunit_qty = 0;
	if ($icost3 == '') $icost3 = 0;
	$c=$fnd = 0;

	if ($p1 == 'Ok'  && ($icase_qty!='' or $iunit_qty!=''))
	{
		//$q = "insert into 
		$q = "select * from ".$aPHYC['tmp_table']." where stock_id = '$stock_id'";
		$qr = @pg_query($q) or message1(pg_errormessage());
		if (@pg_num_rows($qr) > 0)
		{
				$r = @pg_fetch_object($qr);
				$q = "update ".$aPHYC['tmp_table']." 
							set 
								case_qty = '$icase_qty', 
								unit_qty = '$iunit_qty', 
								cost3='$icost3' 
							where 
								stock_id = '$r->stock_id'";
				$qr = @pg_query($q) or message1(pg_errormessage());
			
		}
		else
		{
				$q = "insert into ".$aPHYC['tmp_table']." (stock_id, stockledger_id, cost3, case_qty, unit_qty)
						values ('$stock_id','0','$icost3','$icase_qty','$iunit_qty')";
	
				$qqr = @pg_query($q) or message1(pg_errormessage().$q);
		}
		$searchkey='';

	}

	$stock=$stock_id=$icase_qty= $iunit_qty=$icost3=$ctr='';
	
}

if ($aPHYC['date'] == '') $aPHYC['date'] = date('Y-m-d');

$tables = currTables($aPHYC['date']);
$sales_header = $tables['sales_header'];
$sales_detail = $tables['sales_detail'];
$sales_tender = $tables['sales_tender'];
$stockledger = $tables['stockledger'];

if ($p1 == 'Add New' || $p1 == 'New')
{
	$aPHYC=  null;
	$aPHYC = array();
	$aPHYCD=  null;
	$aPHYCD = array();

	$check = checkTable();
	if (!$check ) message1(" WARNING! NO temporary file was created....");

}
elseif ($p1 == 'Load' && $id!='')
{
	$aPHYC=  null;
	$aPHYC = array();
	$aPHYCD=  null;
	$aPHYCD = array();

	$check = checkTable();
	$table = $aPHYC['tmp_table'];

	if (!$check ) message1(" WARNING! NO temporary file was created....");

	$q = "select * from phycount where phycount_id = '$id'";
	$qr = @pg_query($q) or message1(pg_errormessage());
	$r = @pg_fetch_assoc($qr);
	$aPHYC =  $r;
	$aPHYC['tmp_table'] = $table;

	$tables = currTables($aPHYC['date']);
	$sales_header = $tables['sales_header'];
	$sales_detail = $tables['sales_detail'];
	$sales_tender = $tables['sales_tender'];
	$stockledger = $tables['stockledger'];

	$q = "select 
					sl.stock_id,
					sl.case_qty,
					sl.unit_qty,
					sl.admin_id,
					sl.enable,
					sl.date,
					sl.stockledger_id,
					sl.cost3,
					stock.account_id,
					stock.barcode,
					stock.stock,
					stock.unit1,
					stock.fraction3,
					stock.cost1 as stock_cost1,
					stock.cost3 as stock_cost3
				from 
					$stockledger as sl,
					stock
				where
					stock.stock_id=sl.stock_id and 
					sl.phycount_id ='".$aPHYC['phycount_id']."' 
				order by
					stock.barcode";

	$qr = @pg_query($q) or message1(pg_errormessage());

	while ($r = @pg_fetch_object($qr))
	{
//		$aPHYCD[] = $r;
		$cost3 = $r->cost3*1;
		if ($cost3 == 0 && $r->fraction3 <= 1)
		{
			$cost3 = $r->stock_cost1;
		}
		if ($cost3 == 0 )
		{
			$cost3 = $r->stock_cost3*1;
		}
		
		if ($cost3 == 0)
		{
			// -- temporary solution to cost3 = 0
			$q = "select cost3, cost1 from rr_detail where stock_id = '$r->stock_id' order by rr_detail_id desc offset 0 limit 1";
			$qrc = @pg_query($q) or message1(pg_errormessage().$q);
			$rc = @pg_fetch_object($qrc);
			$cost3 = $rc->cost3*1;
			if ($cost3 == 0 && $r->fraction3 <=1 )
			{
				$cost3 = $rc->cost1*1;
			}
		}

		$case_qty = $r->case_qty*1;
		$unit_qty = $r->unit_qty*1;

		$timestamp = date('Y-m-d g:i:s');
		$q = "insert into $table (stock_id, timestamp, stockledger_id, cost3, case_qty, unit_qty)
					values ('$r->stock_id','$timestamp','$r->stockledger_id','$cost3','$case_qty','$unit_qty')";
	
		$qqr = @pg_query($q); // or message1(pg_errormessage().$q);
				
		
	}



	if ($aPHYC['account_id'] > '0')
	{
		$q = "select 
					stock_id,
					barcode, 
					stock,
					fraction3,
					cost1,
					cost3,
					stock_description,
					unit1
				from
					stock
				where
					stock.enable='Y' and
					account_id='".$aPHYC['account_id']."'
				order by
					barcode";
		$qr = @pg_query($q) or message1(pg_errormessage());
		while ($r = @pg_fetch_assoc($qr))
		{
			$fnd = 0;
			foreach ($aPHYCD as $temp)
			{
				if ($temp['stock_id'] == $r['stock_id'])
				{
					$fnd = 1;
					break;
				}
			}
			if ($fnd == '0')
			{
				$aPHYCD[] = $r;
			}
		}
	}
}
elseif ($p1 == 'sortx' && in_array($sortby, array('barcode','stock')))
{

	$atemp = null;
	$atemp = array();
	foreach ($aPHYCD as $temp)
	{
			$temp1=$temp[$sortby];
			$atemp[]=$temp1;
	}
	
	if (count($atemp) > 0)
	{
		asort($atemp);
		reset($atemp);
	}
	$newarray = null;
	$newarray = array();
	while (list ($key, $val) = each ($atemp))
	{
			$temp=$aPHYCD[$key];
			$newarray[] = $temp;
	}
	$aPHYCD = $newarray;
}
elseif ($p1 == 'DeleteCheckedConfirm' && !chkRights2("phycount","mdelete",$ADMIN['admin_id']))
{
	message("You have no permission to DELETE Physical Count Items...");
}
elseif ($p1 == 'DeleteCheckedConfirm')
{
	$table = $aPHYC['tmp_table'];
	$adel = implode (',',$delete);
	$q = "delete from $table where stockledger_id in 	 ($adel)";
	$qr = @pg_query($q) or message1(pg_errormessage().$q);

	$q = "delete from $stockledger where stockledger_id in 	 ($adel)";
	$qr = @pg_query($q) or message1(pg_errormessage().$q);

	if ($dc>0)
	{
		message1(" $dc Records Successfully Deleted");
	}
}
elseif ($p1 == 'Load All Items' && $aPHYC['account_id'] > '0')
{

	$q = "select 
					stock_id,
					barcode, 
					stock,
					fraction3,
					cost1,
					stock_description,
					unit1,
					category_id
				from
					stock
				where
					stock.enable='Y' ";
			if ($aPHYC['account_id'] != '')
			{
				$q .= " and account_id='".$aPHYC['account_id']."'";
			}
			if ($aPHYC['category_id'] != '')
			{
				$q .= " and category_id='".$aPHYC['category_id']."'";
			}
			$q .= "	order by barcode";

	$qr = @pg_query($q) or message1(pg_errormessage());
	while ($r = @pg_fetch_assoc($qr))
	{
		$q = "insert into $table (stock_id, stockledger_id, cost3, case_qty, unit_qty)
					values ('$r->stock_id','$r->stockledger_id','$cost3','$case_qty','$unit_qty')";
	
		$qqr = @pg_query($q) or message1(pg_errormessage().$q);
	}

}

elseif ($p1 == 'Save' && ($aPHYC['date'] == '' or $aPHYC['date'] == '---' or $aPHYC['date'] == '0000-00-00') )
{
	message1 ('Please Specify Date of Inventory...');
}
elseif ($p1 == 'Save')
{
	$date = date('Y-m-d');
	if ($aPHYC['phycount_id'] == '')
	{
		if (!chkRights2("phycount","madd",$ADMIN['admin_id']))
		{
			message1("You have no permission to ADD Physical Count Items...");
		}
		else
		{
			$q = "insert into phycount (date, date_updated, account_id,category_id,admin_id, audit)
					values ('".$aPHYC['date']."', '$date','".$aPHYC['account_id']."', '".$aPHYC['category_id']."', '".$ADMIN['admin_id']."', '$audit')";
			$qr = @pg_query($q) or message1('Cannot Insert new entry...'.pg_errormessage().$q);
			$id = @db_insert_id('phycount');
			$aPHYC['phycount_id'] = $id;
		}
	}
	else
	{
		if (!chkRights2("phycount","medit",$ADMIN['admin_id']))
		{
			message1("You have no permission to UPDATE Physical Count Items...");
		}
		else
		{
			$q = "update phycount set date_updated='$date', audit = '$audit' where phycount_id = '".$aPHYC['phycount_id']."'";
			$qr = @pg_query($q) or message1('Cannot update entry...'.pg_errormessage().$q);
		}
	}
	$c=$uc=$um=$ic=$im = 0;
	$message = "Data Successfilly Saved.";
	
	$q = "select * from ".$aPHYC['tmp_table'];
	$qr = @pg_query($q) or message1(pg_errormessage().$q);
	while ($temp = @pg_fetch_assoc($qr))
	{
		if ($temp['case_qty'] == '0'  && $temp['unit_qty'] == '0') continue;

	/*
		$q = "select * from $stockledger where phycount_id ='".$aPHYC['phycount_id']."' and  stock_id = '".$temp['stock_id']."' ";
	
		$qlr = @pg_query($q) or message1(pg_errormessage());
		if (@pg_num_rows($qlr) == 0)
	*/
		if ($temp['stockledger_id'] == '0' or $temp['stockledger_id'] == '')
		{

			$q = "insert into $stockledger (date,phycount_id, stock_id, case_qty,unit_qty, cost3, type,  admin_id) 
							values ('".$aPHYC['date']."', '".$aPHYC['phycount_id']."', '".$temp['stock_id']."', '".$temp['case_qty']."',
											'".$temp['unit_qty']."','".$temp['cost3']."', 'E', '".$ADMIN['admin_id']."')";
			$qqr = @pg_query($q) or message1(pg_errormessage().$q);
			if ($qr)
			{
				$id = @db_insert_id("stockledger");
				$qq = "update ".$aPHYC['tmp_table']." set stockledger_id = '$id' where stock_id = '".$temp['stock_id']."'";
				@pg_query($qq);
	
//				$aPHYCD[$c] = $temp;
				$ic++;
			}
			else
			{
				$im++;
				$message = "Errors occured during saving...";
			}
		}
		else
		{
//			$rl = @pg_fetch_object($qlr);
			$q = "update $stockledger set
							phycount_id ='".$aPHYC['phycount_id']."', 
							case_qty = '".$temp['case_qty']."',
							unit_qty = '".$temp['unit_qty']."',
							cost3 = '".$temp['cost3']."',
							date = '".$aPHYC['date']."'
						where
							stockledger_id = '".$temp['stockledger_id']."'";
							
	//						phycount_id ='".$aPHYC['phycount_id']."' and  
	//						stock_id = '".$temp['stock_id']."'";
							
							
 							//stockledger_id = '$rl->stockledger_id'";
			$qqr = @pg_query($q) or message1(pg_errormessage().$q);
			if ($qqr)
			{
					$uc++;
			}
			else
			{
				$um++;
				$message = "Errors occured during update...";
			}
		}
//		if ($temp['stock_id'] == '9098') echo $q;
		$c++;
	}
	message1("$message <br> $ic Inserted $uc Updated $im Inserts Failed $um Updates Failed");
}
$href1="javascript: document.getElementById('f1').action='?p=phycount&p1=sort&sortby=barcode';document.getElementById('f1').submit()";
$href2="javascript: document.getElementById('f1').action='?p=phycount&p1=sort&sortby=stock';document.getElementById('f1').submit()";


$header .= 	center('#',10).'|'.
					"<a href=\"$href1\">".center('Barcode',16).'</a>|'.
					"<a href=\"$href2\">".center('Item Description',48).'</a> | '.
					center('U/C',4).'|'.
					center('Cases',9).'|'.
					center('Units',9).'|'.
					center('CaseCost',10);

?>
<form name="f1" id="f1" method="post" action="?p=phycount">
  <table width="97%" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr><td colspan='6'>
  <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
          <tr > 
            <td height="23" colspan="4" background="../graphics/table0_horizontal.PNG"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;:: 
              Inventory Physical Count</font></td>
          </tr>
          <tr <?= ($aPHYC['enable'] == 'N' ? "bgColor='#FFCCCC'" : '');?>> 
            <td width="9%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">InventoryDate</font></td>
            <td width="23%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Supplier</font></td>
            <td width="21%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Category</font></td>
            <td width="47%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Show <?= ($aPHYC['enable'] == 'N' ? " *** CANCELLED ***" : '  *** Record Id: '.$aPHYC['phycount_id']);?></font></td>
          </tr>
          <tr> 
            <td nowrap> <input name="date" type="text" id="date" value="<?= ymd2mdy($aPHYC['date']);?>" size="10" onBlur="IsValidDate(this,'MM/dd/yyyy');" onKeyUp="setDate(this,'MM/dd/yyyy','en')"  onKeypress="if(event.keyCode==13) {document.getElementById('date_to').focus();return false;}" style="border: #CCCCCC 1px solid;"> 
              <img src="../graphics/dwn-arrow-grn.gif" onclick="popUpCalendar(this, f1.date, 'mm/dd/yyyy')"></td>
            <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
              <select name="account_id" id="account_id"   style="border: #CCCCCC 1px solid; width:220px">
                <option value='0'>Any Supplier Account--</option>
                <?
  		foreach ($aSUPPLIER as $stemp)
		{
			if ($stemp['account_id'] == $aPHYC['account_id'])
			{
				echo "<option value=".$stemp['account_id']." selected>".substr($stemp['account_code'],0,6)." ".$stemp['account']."</option>";
			}
			else
			{
				echo "<option value=".$stemp['account_id'].">".substr($stemp['account_code'],0,6)." ".$stemp['account']."</option>";
			}
		}

	  ?>
              </select>
              </font> </td>
            <td><select name="category_id"   tabindex="<?= array_search('category_id',$fields);?>" style="border: #CCCCCC 1px solid; width:200px">
                <option value='0'>All Categories--</option>
                <?
		foreach ($aCATEGORY as $ctemp)
		{
			if ($SYSCONF['SORT_CATEGORY'] == 'category')
			{
				$category_code = '';
			}
			else
			{
				$category_code = substr($ctemp['category_code'],0,6);
			}
			if ($ctemp['category_id'] == $aPHYC['category_id'])
			{
				echo "<option value=".$ctemp['category_id']." selected>".$category_code." ".$ctemp['category']."</option>";
			}
			else
			{
				echo "<option value=".$ctemp['category_id']." >".$category_code." ".$ctemp['category']."</option>";
			}
		}
	  ?>
              </select>
            </td>
            <td>
			<select name="show" id="show" onChange="xajax_grid(xajax.getFormValues('f1'),'');">
			<option value="">All</option>
			<option value="nocost">No Cost</option>
			<option value="nouc">U/C=1</option>
			</select>
			<input name="p12" type="submit" class="altBtn" id="p1" value="Load All Items"> 
              <input name="p12" type="submit" id="p1" value="Add New"  onmouseover="showToolTip(event,'Add NEW Physical Count Schedule...');return false" onmouseout="hideToolTip()" class="altBtn"> 
			  <input name="p12" type="button" id="p12" value="Browse" onClick="window.location='?p=phycount.browse'"  class="altBtn"> 
              <input type="button" name="Submit23" value="Close" onClick="window.location='?p='" class="altBtn"></td>
          </tr>
          <tr> 
            <td colspan="4"><hr size=1 color='#990000'></td>
          </tr>
        </table>
 </td>
 </tr>
    <tr> 
      <td width="18%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Search</font></td>
      <td width="23%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Item 
        Description</font></td>
      <td width="9%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Cases</font></td>
      <td width="9%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Units</font></td>
      <td colspan="2"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">CaseCost</font></td>
    </tr>
    <tr> 
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="searchkey" type="text" class="altText" id="searchkey" value="<?= $searchkey;?>" size="15"  tabindex="10"  onKeypress="if(event.keyCode==13) {document.getElementById('SearchButton').click();document.getElementById('icase_qty').focus();return false;}"  style="border: #CCCCCC 1px solid;">
        <?= lookUpAssoc('searchitemby',array('Barcode'=>'barcode','Name'=>'stock','Desc'=>'stock_description'),$searchitemby);?>
        </font> <input name="p1" type="button" class="altBtn" id="SearchButton" value="Search" onClick="wait('Please wait. Searching...');xajax_pc_search(xajax.getFormValues('f1'),'pc_select', 'icase_qty');"> 
      </td>
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="stock" type="text" class="altText" id="stock" value="<?= stripslashes($stock);?>" size="30" style="border: #CCCCCC 1px solid;" readOnly>
        </font></td>
      <td><input name="icase_qty" type="text" class="altText" id="icase_qty" value="<?= $icase_qty;?>" size="8"  tabindex="12"  style="border: #CCCCCC 1px solid; text-align:right"  onKeypress="if(event.keyCode==13) {document.getElementById('iunit_qty').focus();return false;}"></td>
      <td><input name="iunit_qty" type="text" class="altText" id="iunit_qty" value="<?= $iunit_qty;?>" size="8"  tabindex="12"     style="border: #CCCCCC 1px solid; text-align:right" onKeypress="if(event.keyCode==13) {document.getElementById('icost3').focus();return false;}"> 
      </td>
      <td width="36%"><input name="icost3" type="text" class="altText" id="icost3" value="<?= $icost3;?>" size="8"  tabindex="12"     style="border: #CCCCCC 1px solid; text-align:right" onKeypress="if(event.keyCode==13) {document.getElementById('Ok').focus();return false;}"> 
        <input name="p1" type="button" class="altBtn" id="Ok" value="Ok" onClick="vOk()"> <input name="stock_id" type="hidden" class="altText" id="stock_id" value="<?= $stock_id;?>" size="8"  tabindex="12"  o style="text-align:right"  onKeypress="if(event.keyCode==13) {document.getElementById('cost1').focus();return false;}"> 
        <input name="ctr" type="hidden" class="altText" id="ctr" value="<?= $ctr;?>" size="8"  tabindex="12"  o style="text-align:right"  onKeypress="if(event.keyCode==13) {document.getElementById('cost1').focus();return false;}"></td>
      <td></td>
    </tr>
    <tr valign="top" bgcolor="#CCCCCC"> 
      <td colspan="5" nowrap> <pre style="top-margin:0; bottom-margin=0;padding: 0px; margin:2px"><?=$header;?>
	  </pre></td>
      <td nowrap bgcolor="#DADADA"><img src="../graphics/arrow_up.jpg" onClick="wait('Loading...');xajax_pc_grid(xajax.getFormValues('f1'),'UP');"></td>
    </tr>
    <tr valign="top"> 
      <td height="320px" colspan="5" nowrap> <div id="gridLayer" style="position:virtual; width:100%; height:100%; z-index:1; overflow: visible;"> 
          <?
		  	if ($aPHYC['start'] == '') $aPHYC['start'] = 0;
			$ctr=0;
			$details = '';
			$tmp_table = $aPHYC['tmp_table'];

			$q = "select 	tmp.stock_id,
								tmp.case_qty,
								tmp.unit_qty,
								tmp.cost3,
								tmp.stockledger_id, 
								stock.stock, 
								stock.stock_description, 
								stock.barcode,
								stock.unit1,
								stock.fraction3
				from 
								$tmp_table as tmp,
								stock
				where
								stock.stock_id = tmp.stock_id";
			
			if ($sortby == '' or $sortby == 'barcode')
			{
				$q .= " order by	stock.barcode";
			}
			else
			{
				$q .= " order by stock.stock ";
			}
			$q .= " offset ".$aPHYC['start']." limit 15";
			$qr = @pg_query($q) or message1(pg_errormessage().$q);
			while ($temp = @pg_fetch_assoc($qr))
			{

				$ctr++;

				if ($temp['stock_description'] == '')
				{
					$stock = $temp['stock'];
				}
				else
				{
					$stock=$temp['stock_description'];
				}
				$previous_id = $ctr-1;
				$next_id = $ctr+1;
				

				$href = "javascript:SL('".$temp['stock_id']."','".$temp['barcode']."','".addslashes($stock)."','".$temp['case_qty']."','".$temp['unit_qty']."','".$temp['cost3']."','".$ctr."')";
				//$href = "javascript:v($ctr)";
				
				$details .= "<a href=\"$href\">".adjustRight($ctr,7).'. '.
								"<input type='checkbox' name='delete[]' id='a".$temp['stock_id']."' value = '".$temp['stockledger_id']."' >".
								adjustSize($temp['barcode'],16).' '.
								adjustSize($stock,48).'  '.
								adjustSize($temp['fraction3'],4).' '.
								adjustRight($temp['case_qty'],9).' '.
								adjustRight($temp['unit_qty'],9).' '.
								adjustRight(number_format($temp['cost3'],2),10).' '.
								"</a>\n";

				echo "<pre style=\"margin:0\" id=\"a$ctr\">$details</pre>";
				$details = '';
			}

			$aPHYC['ctr'] = $ctr;
			?>
        </div></td>
      <td height="320px" nowrap bgcolor="#DADADA"></td>
    </tr>
    <tr valign="top"> 
      <td colspan="2" nowrap><table width="1%" border="0" cellpadding="1" cellspacing="1" bgcolor="#CCCCCC">
          <tr bgcolor="#FFFFFF"> 
            <td nowrap> <img src="../graphics/save.jpg" accesskey="S" alt="Save This Claim Form" width="57" height="15" id="Save"  onClick="wait('Please wait. Saving data...');xajax_pc_save(xajax.getFormValues('f1'));" name="Save"> 
            </td>
            <td nowrap > <img src="../graphics/print.jpg"  alt="Print This  Form"  name="Print" border="0" id="Print"  accesskey="P" onClick="wait('Please wait. Calculating...');xajax_pc_print(xajax.getFormValues('f1'));"></td>
            <td nowrap width="25%"> <input type='image' name="Cancel" id="Cancel" onClick="vSubmit(this)"  src="../graphics/cancel.jpg" alt="Cancel Form" width="77" height="20"> 
            <td nowrap width="25%"> <input type='image' name="New" accesskey="N" id="New" onClick="f1.action='?p=phycount&p1=New';f1.submit();"  src="../graphics/new.jpg" alt="New Claim Form" width="63" height="20"> 
            </td>
          </tr>
        </table></td>
      <td colspan="2" nowrap><input name="delete[]" type="button" id="delete" onClick="if(confirm('Are you sure to DELETE checked Items?')) {document.getElementById('f1').action='?p=phycount&p1=DeleteCheckedConfirm';document.getElementById('f1').submit()}Q" value="Delete Checked" class="altBtn"></td>
      <td align="center" nowrap><font size="2">(Page UP/DOWN to Scroll)</font></td>
      <td nowrap bgcolor="#DADADA"><img src="../graphics/arrow_down.jpg" onClick="wait('Loading...');xajax_pc_grid(xajax.getFormValues('f1'),'DOWN');"></td>
    </tr>
  </table>
<?
include_once('xajax_popup.php');
include_once('phycount.print.php');
?>
</form>
<?
if ($p1 == 'searchStock'  && $searchkey == '' && $aPHYCD['account_id'] == '')
{
	message("No Filter/Search Item Specified...");
}
elseif ($focus == '')
{
	$focus = 'searchkey';
}
echo " <script>document.getElementById('$focus').focus()</script>";


?>
