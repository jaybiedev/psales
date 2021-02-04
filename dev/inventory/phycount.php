 <script language="JavaScript" type="text/JavaScript">
<!--

function vSelect(sid,barcode,stock,qty,ctr)
{
	document.getElementById('stock_id').value = sid;
	document.getElementById('stock').value = stock;
	document.getElementById('searchkey').value = barcode;
	document.getElementById('iqty').value = qty;
	document.getElementById('ctr').value = ctr;
	
	document.getElementById('iqty').focus();
	return;
}

function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);


//-->
</script>

<?
if (!chkRights2("phycount","mview",$ADMIN['admin_id']))
{
	message("You have no permission in this area (Physical Count)...");
	exit;
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

if (!in_array($p1 , array('')))
{
	$aPHYC['date']  = mdy2ymd($_REQUEST['date']);
	$aPHYC['account_id'] = $_REQUEST['account_id'];

	$iqty = $_REQUEST['iqty'];
	$stock_id = $_REQUEST['stock_id'];
	$c=$fnd = 0;

	foreach ($aPHYCD as $temp)
	{
		$c++;
		$dummy = $temp;
		$dummy['qty'] = $qty[$c-1];
		$aPHYCD[$c-1] = $dummy;

	}
	if ($p1 == 'Ok'  && $iqty!='')
	{
		$fnd=0;
		foreach ($aPHYCD as $temp)
		{
			$c++;
			
			if ($temp['stock_id'] == $stock_id)
			{
				$dummy = $temp;
				$dummy['qty'] = $iqty;
				$aPHYCD[$c-1] = $dummy;
				$fnd=1;
				break;
			}
		}
		if ($fnd == '0')
		{
			$q = "select stock_id, account_id, stock, unit1, barcode from stock where stock_id = '$stock_id'";
			$qr = @pg_query($q) or message(pg_errormessage());
			$r = @pg_fetch_assoc($qr);
			$dummy = $r;
			$dummy['qty'] = $iqty;
	
			$aPHYCD[] = $dummy;
		}
	}

	$stock=$stock_id=$iqty=$ctr='';
	
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
}
elseif ($p1 == 'Load' && $date!='' && $account_id !='')
{
	$tables = currTables($date);
	$sales_header = $tables['sales_header'];
	$sales_detail = $tables['sales_detail'];
	$sales_tender = $tables['sales_tender'];
	$stockledger = $tables['stockledger'];
	

	$q = "select 
					sl.stock_id,
					sl.qty,
					sl.admin_id,
					sl.enable,
					sl.date,
					sl.stockledger_id,
					stock.account_id,
					stock.barcode,
					stock.stock,
					stock.unit1
				from 
					$stockledger as sl,
					stock
				where
					stock.stock_id=sl.stock_id and 
					sl.date='$date' and
					stock.account_id ='$account_id'
				order by
					stock.barcode";
	$qr = @pg_query($q) or message1(pg_errormessage());

	$aPHYC = null;
	$aPHYC = array();	
	$aPHYCD = null;
	$aPHYCD = array();	

	while ($r = @pg_fetch_assoc($qr))
	{

		$aPHYC['date'] = $r['date'];
		$aPHYC['account_id'] = $r['account_id'];
		$aPHYC['admin_id'] = $r['admin_id'];
		$aPHYCD[] = $r;
	}

}
elseif ($p1 == 'Search')
{
	$q = "select *
					from
						stock
					where
						stock.barcode='$searchkey' and
						enable='Y'";
	if ($aPHYC['account_id'] > '0')
	{
		$q .= " and stock.account_id = '".$aPHYC['account_id']."'";
	}
	$qr = @pg_query($q) or message1(pg_errormessage());
	if (@pg_num_rows($qr) == 0)
	{
		message1(" Search NOT found or may belong to a different supplier...");
	}
	else
	{
		$r = @pg_fetch_object($qr);
		$searchkey = $r->barcode;
		$stock = $r->stock;
		$stock_id = $r->stock_id;
		foreach ($aPHYCD as $temp)
		{
			if ($temp['stock_id'] == $stock_id)
			{
				$iqty = $temp['qty'];
				break;
			}
		}
	}
	
}
elseif ($p1 == 'DeleteCheckedConfirm' && !chkRights2("phycount","mdelete",$ADMIN['admin_id']))
{
	message("You have no permission to DELETE Physical Count Items...");
}
elseif ($p1 == 'DeleteCheckedConfirm')
{
	$newarray = null;
	$newarray = array();
	
	$cc=$dc=0;
	foreach ($aPHYCD as $temp)
	{
		$cc++;
		if (in_array($cc,$mark))
		{
			if ($temp['stockledger_id'] != '')
			{
				$q = "delete from $stockledger where stockledger_id = '".$temp['stockledger_id']."'";
				$qr = @pg_query($q) or message1(pg_errormessage());
			}
			$dc++;
		}
		else
		{
			$newarray[] = $temp;
		}
	}
	$aPHYCD = $newarray;

	if ($dc>0)
	{
		message1(" $dc Records Successfully Deleted");
	}
}
elseif ($p1 == 'Load All Items' && $aPHYC['account_id'] > '0')
{

	$aPHYCD = null;
	$aPHYCD = array();
	
	
	$dummy = null;
	$dummy = array();
	$q = "select * from $stockledger as sl where date='".$aPHYC['date']."' and enable='Y'";
	$qr = @pg_query($q) or message(pg_errormessage());
	while ($r = @pg_fetch_assoc($qr))
	{
		$dummy[] = $r;
	}

	
	$q = "select 
					stock_id,
					barcode, 
					stock,
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
		foreach ($dummy as $temp)
		{
			if ($temp['stock_id'] == $r['stock_id'])
			{
				$r['qty'] = $temp['qty'];
			}
		}
		$aPHYCD[] = $r;
	}

}

elseif ($p1 == 'Save')
{
	$c=$uc=$um=$ic=$im = 0;
	$message = "Data Successfilly Saved.";
	foreach ($aPHYCD as $temp)
	{
		if ($temp['stockledger_id'] == '')
		{
			if (!chkRights2("phycount","madd",$ADMIN['admin_id']))
			{
				message("You have no permission to ADD Physical Count Items...");
				exit;
			}


			if ($temp['qty']*1 == '0') continue;
			
			$q = "insert into $stockledger (date, stock_id, qty, type,  admin_id) 
							values ('".$aPHYC['date']."', '".$temp['stock_id']."', '".$temp['qty']."', 'E', '".$ADMIN['admin_id']."')";
			$qr = @pg_query($q) or message1(pg_errormessage().$q);
			if ($qr)
			{
				$id = @db_insert_id('stockledger');
				$temp['stockledger_id'] = $id;
	
				$aPHYCD[$c] = $temp;
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
			if (!chkRights2("phycount","medit",$ADMIN['admin_id']))
			{
				message("You have no permission to UPDATE Physical Count Items...");
				exit;
			}

			$q = "update $stockledger set
							qty = '".$temp['qty']."',
							date = '".$aPHYC['date']."'
						where
							stockledger_id = '".$temp['stockledger_id']."'";
			$qr = @pg_query($q) or message1(pg_errormessage().$q);
			if ($qr)
			{
					$uc++;
			}
			else
			{
				$um++;
				$message = "Errors occured during update...";
			}
		}
		$c++;
	}
	message1("$message <br> $ic Inserted $uc Updated $im Inserts Failed $um Updates Failed");
}
?>
<form name="f1" id="f1" method="post" action="">
  <table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr > 
      <td height="23" colspan="2" background="../graphics/table0_horizontal.PNG"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;:: 
        Inventory Physical Count</font></td>
    </tr>
    <tr> 
      <td width="9%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">InventoryDate</font></td>
      <td width="91%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Supplier</font></td>
    </tr>
    <tr> 
      <td nowrap>
        <input name="date" type="text" id="date" value="<?= ymd2mdy($aPHYC['date']);?>" size="10" onBlur="IsValidDate(this,'MM/dd/yyyy');" onKeyUp="setDate(this,'MM/dd/yyyy','en')"  onKeypress="if(event.keyCode==13) {document.getElementById('date_to').focus();return false;}">
        <img src="../graphics/dwn-arrow-grn.gif" onclick="popUpCalendar(this, f1.date_from, 'mm/dd/yyyy')"></td>
      <td><select name='account_id' id='account_id' style="width:250px"  onKeypress="if(event.keyCode==13) {document.getElementById('date').focus();return false;}">
          <option value=''>Any Supplier -- </option>
          <?
		$q = "select account_id, account, account_code 
					from 
						account, 
						account_type 
					where 
						account.account_type_id=account_type.account_type_id and 
						account_type.account_type_code='S' 
					order by 
						account_code, account ";
						
		$qr = @pg_query($q);
		while ($r = @pg_fetch_object($qr))
		{
			if ($aPHYC['account_id'] == $r->account_id)
			{
				echo "<option value = $r->account_id selected>".substr($r->account_code,0,7)."  $r->account</option>";
			}
			else
			{
				echo "<option value = $r->account_id>".substr($r->account_code,0,7)."  $r->account</option>";
			}	
		}
		?>
        </select>
        <input name="p1" type="submit" class="altBtn" id="p1" value="Load All Items">
        <input name="p1" type="submit" id="p1" value="Add New">
        <input name="p12" type="button" id="p12" value="Browse" onClick="window.location='?p=phycount.browse'">
        <input type="button" name="Submit23" value="Close" onClick="window.location='?p='"></td>
    </tr>
    <tr> 
      <td colspan="2"><hr size=1 color='#990000'></td>
    </tr>
  </table>
  <table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr> 
      <td width="18%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Search</font></td>
      <td width="25%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Item 
        Description</font></td>
      <td width="57%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Count</font></td>
    </tr>
    <tr> 
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="searchkey" type="text" class="altText" id="searchkey" value="<?= $searchkey;?>" size="15"  tabindex="10"  onKeypress="if(event.keyCode==13) {document.getElementById('SearchButton').click();return false;}">
        </font> <input name="p1" type="submit" class="altBtn" id="SearchButton" value="Search" > 
        <font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="stock" type="text" class="altText" id="stock" value="<?= stripslashes($stock);?>" size="38">
        </font></td>
      <td><input name="iqty" type="text" class="altText" id="iqty" value="<?= $iqty;?>" size="8"  tabindex="12"   style="text-align:right"  onKeypress="if(event.keyCode==13) {document.getElementById('Ok').focus();return false;}"> 
        <input name="p1" type="submit" class="altBtn" id="Ok" value="Ok">
        <input name="stock_id" type="hidden" class="altText" id="stock_id" value="<?= $stock_id;?>" size="8"  tabindex="12"  o style="text-align:right"  onKeypress="if(event.keyCode==13) {document.getElementById('cost1').focus();return false;}">
        <input name="ctr" type="hidden" class="altText" id="ctr" value="<?= $ctr;?>" size="8"  tabindex="12"  o style="text-align:right"  onKeypress="if(event.keyCode==13) {document.getElementById('cost1').focus();return false;}"></td>
    </tr>
    <tr valign="top"> 
      <td height="300px" colspan="3" nowrap><div id="Layer1" style="position:relative; width:100%; height:100%; z-index:1; overflow: scroll;">
          <table width="100%%" border="0" cellspacing="1" cellpadding="0" bgcolor="#EFEFEF">
            <tr> 
              <td align="center"><font size="2" face="Georgia, Times New Roman, Times, serif">#</font></td>
              <td><font size="2" face="Georgia, Times New Roman, Times, serif">Barcode</font></td>
              <td><font size="2" face="Georgia, Times New Roman, Times, serif">Item 
                Description</font></td>
              <td><font size="2" face="Georgia, Times New Roman, Times, serif">Unit</font></td>
              <td><font size="2" face="Georgia, Times New Roman, Times, serif">Count</font></td>
            </tr>
			
			<?
			$ctr=0;

			foreach ($aPHYCD as $temp)
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
				

				$href = "javascript: vSelect('".$temp['stock_id']."','".$temp['barcode']."','".addslashes($stock)."','".$temp['qty']."','".$ctr."')";
			?>
            <tr bgcolor="#FFFFFF" onMouseOver="bgColor='#FFFFCC'" onMouseOut="bgColor='#FFFFFF'"> 
              <td align="right"><font size="2" face="Georgia, Times New Roman, Times, serif"><?=$ctr;?>.<input name="mark[]" type="checkbox" value="<?=$ctr;?>"></font></td>
              <td><font size="2" face="Georgia, Times New Roman, Times, serif"><a href="<?=$href;?>"><?= $temp['barcode'];?></a></font></td>
              <td><font size="2" face="Georgia, Times New Roman, Times, serif"><?= $stock;?></font></td>
              <td><font size="2" face="Georgia, Times New Roman, Times, serif"><?= $temp['unit1'];?></font></td>
              <td><input type="text" style="text-align:right" size="6" name="qty[]" id="q<?=$ctr;?>" value="<?=$temp['qty'];?>"  onKeypress="if(event.keyCode==13) {document.getElementById('q<?=$next_id;?>').focus();return false;}"></td>
            </tr>
			<?
			}
			?>
          </table>
        </div></td>
    </tr>
    <tr valign="top"> 
      <td colspan="2" nowrap><table width="1%" border="0" cellpadding="1" cellspacing="1" bgcolor="#CCCCCC">
          <tr bgcolor="#FFFFFF"> 
            <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              <input type="image" src="../graphics/save.jpg" accesskey="S" alt="Save This Claim Form" width="57" height="15" id="Save" onClick="f1.action='?p=phycount&p1=Save';f1.submit();" name="Save">
              </strong></font><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              </strong></font></td>
            <td nowrap ><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              <input type = "image" src="../graphics/print.jpg"  alt="Print This Claim Form"  name="Print" border="0" id="Print"  accesskey="P" onClick="f1.action='?p=phycount&p1=Print';f1.submit();">
              </strong></font></td>
            <td nowrap width="25%"> <input type='image' name="Cancel" id="Cancel2" onClick="vSubmit(this)"  src="../graphics/cancel.jpg" alt="Cancel Form" width="77" height="20"> 
            <td nowrap width="25%"> <input type='image' name="New" accesskey="N" id="New" onClick="f1.action='?p=phycount&p1=New';f1.submit();"  src="../graphics/new.jpg" alt="New Claim Form" width="63" height="20"> 
            </td>
          </tr>
        </table></td>
      <td nowrap><input type="button" name="p1" value="Delete Checked" onClick="if(confirm('Are you sure to DELETE checked Items?')) {document.getElementById('f1').action='?p=phycount&p1=DeleteCheckedConfirm';document.getElementById('f1').submit()}"></td>
    </tr>
  </table>
</form>
