<script language="JavaScript" type="text/JavaScript">
<!--
function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);
//-->
function vSubmit(ul)
{
	if (ul.name == 'Cancel')
	{
		if (confirm("Are you sure to CANCEL customer Record?"))
		{
			document.f1.action="?p=report.bincard&p1=CancelConfirm"
		}	
		else
		{
			document.f1.action="?p=report.bincard&p1=Cancel"
		}
	}
	else
	{
		document.f1.action="?p=report.bincard&p1="+ul.id;
	}	
}
</script>
<?
if ($to_date == '') $to_date=date('m/d/Y');
if ($from_date == '') $from_date=addDate($to_date,-30);

if (!session_is_registered('aBIN'))
{
	session_register('aBIN');
	$aBIN=null;
	$aBIN=array();
}
if ($p1 == 'Refresh')
{
	$aBIN['from_date'] = $_REQUEST['from_date'];
	$aBIN['to_date'] = $_REQUEST['to_date'];
}
if ($c_id!= ''  && $p1 == 'selectStock')
{
	$aBIN=null;
	$aBIN=array();
	$q = "select 
				*
		 from 
		 		stock
		where 
				stock_id='$c_id'";
	$r = fetch_assoc($q);
	$aBIN = $r;
	$aBIN['from_date'] = $_REQUEST['from_date'];
	$aBIN['to_date'] = $_REQUEST['to_date'];
}
?> 
<br>
<form action="" method="post" name="f1" id="f1">
  <table width="95%" border="0" align="center" cellpadding="2" cellspacing="1">
    <tr bgcolor="#E9E9E9"> 
      <td width="9%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>BinCard</strong> 
        :: </font></td>
      <td width="16%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Search 
        Item</font></td>
      <td width="8%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Search 
        By</font></td>
      <td width="1%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">From</font></td>
      <td width="66%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">To</font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td nowrap><font  color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
        </strong></font><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
        </font></td>
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="xSearch" type="text" id="xSearch" value="<?= $xSearch;?>">
        </font></td>
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= lookUpAssoc('searchby',array('Item Name'=>'stock','Bar Code'=>'barcode','Item Code'=>'stock_code','Description'=>'stock_description'), $searchby);?>
        </font></td>
      <td nowrap><font  color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
        <input name="from_date" type="text" id="from_date2"  onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $aBIN['from_date'];?>" size="9">
        <img src="../graphics/dwn-arrow-grn.gif" onclick="popUpCalendar(this, f1.from_date, 'mm/dd/yyyy')"> 
        </strong></font></td>
      <td nowrap><font  color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
        <input name="to_date" type="text" id="to_date2"  onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $aBIN['to_date']?>" size="9">
        <img src="../graphics/dwn-arrow-grn.gif" onclick="popUpCalendar(this, f1.to_date, 'mm/dd/yyyy')"> 
        </strong></font><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="p13" type="submit" id="p13" value="Go">
        <input name="p14" type="submit" id="p14" value="Refresh">
        </font><font  color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
        </strong></font></td>
    </tr>
    <tr> 
      <td colspan="5" nowrap><hr size="1"></td>
    </tr>
  </table>
<?
if ($p1 == 'Go')
{
	$aBIN['from_date'] = $_REQUEST['from_date'];
	$aBIN['to_date'] = $_REQUEST['to_date'];

	echo "</form>";
  	$qr = pg_query("select * 
				from 
					stock
				where 
					$searchby like '%$xSearch%'
				order by
					stock")
			or message("Error Querying Stock file...".pg_errormessage());
?>
  
<table width="95%" border="0" align="center" cellpadding="2" cellspacing="1" bgcolor="#EFEFEF">
  <tr bgcolor="#CFD3E7"> 
    <td width="7%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
    <td width="9%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Item 
      Code </font></strong></td>
    <td width="29%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Item 
      Name</font></strong></td>
    <td width="10%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Unit</font></strong></td>
    <td width="22%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Classification</font></strong></td>
    <td width="23%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Category</font></strong></td>
  </tr>
  <?
  	$ctr=0;
  	while ($r = pg_fetch_object($qr))
	{
		$ctr++;
  ?>
  <tr bgcolor="#FFFFFF" onMouseOver="bgColor='#FFFFCC'" onMouseOut="bgColor='#FFFFFF'" onClick="window.location='?p=report.bincard&p1=selectStock&searchby=<?=$searchby;?>&c_id=<?=$r->stock_id;?>&xSearch=<?=$xSearch;?>&from_date=<?=$from_date;?>&to_date=<?=$to_date;?>'"> 
    <td align="right" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?=$ctr;?>
      .</font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href="?p=report.bincard&p1=selectStock&searchby=<?=$searchby;?>&c_id=<?=$r->stock_id;?>&xSearch=<?=$xSearch;?>&from_date=<?=$from_date;?>&to_date=<?=$to_date;?>"> 
      <?= $r->barcode;?>
      </a> </font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=report.bincard&p1=selectStock&searchby=<?=$searchby;?>&c_id=<?=$r->stock_id;?>&xSearch=<?=$xSearch;?>&from_date=<?=$from_date;?>&to_date=<?=$to_date;?>">
      <?= $r->stock;?>
      </a></font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= $r->unit;?>
      </font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= lookUpTableReturnValue('x','classification','classification_id','classification',$r->classification_id);?>
      </font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= lookUpTableReturnValue('x','category','category_id','category',$r->category_id);?>
      </font><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
      </font><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
      </font></td>
  </tr>
  <?
  }
  ?>
</table>

<?	
}

if ($p1!='Go' && $aBIN['stock_id']!='')
{
	$aBIN['from_date'] = $_REQUEST['from_date'];
	$aBIN['to_date'] = $_REQUEST['to_date'];

	if ($p1 == 'Print Draft') $header .= "<reset>";
  	$header .= center($SYSCONF['BUSINESS_NAME'],80)."\n";

	if ($p1 == 'Print Draft') $header .= "<bold>";
  	$header .= center('B-I-N-C-A-R-D',80)."\n\n";
	if ($p1 == 'Print Draft') $header .= "</bold>";

	if ($p1 == 'Print Draft') $header .= "</bold><small3>";
	$header .= adjustSize("Item Code/Descr : [".$aBIN['stock_id'] ."] ".$aBIN['stock'],65).' '.
  				'Dates: '.$from_date.' To '.$to_date."\n";
	$header .= "Bar Code   : ".$aBIN['barcode']."\n";

	$header .= "Unit       : ".$aBIN['unit1'].'; '.$aBIN['unit2'].'x'.$aBIN['fraction2'].'; '.$aBIN['unit3'].'x'.$aBIN['fraction3']."\n";
	$header .= "Last Cost  : ".number_format($aBIN['cost1'],2)."\n";
	$header .= "\n";
	$header .= str_repeat('-',120)."\n";
	$header .= " Date    Reference Type    Particulars           Unit  Fraction         IN         OUT       BALANCE  \n";
	$header .= str_repeat('-',120)."\n";
	
	$mfrom_date = mdy2ymd($aBIN['from_date']);
	$mto_date = mdy2ymd($aBIN['to_date']);
	$beginning_balance = 0;
	
	$aRep = array();
	$q = "select 
				rr_header.date, 
				rr_header.rr_header_id,
				rr_header.account_id,
				rr_header.status,
				rr_detail.stock_id, 
				rr_detail.fraction,
				rr_detail.unit,
				rr_detail.cunit,
				rr_detail.qty,
				rr_detail.cost,
				rr_detail.qty1,
				rr_detail.cost1,
				rr_detail.amount
		 from 
		 		rr_header, 
				rr_detail
		where
				rr_detail.rr_header_id = rr_header.rr_header_id and
				stock_id='".$aBIN['stock_id']."'
		order by
				rr_header.date";
	$qr = pg_query($q) or die (pg_errormessage().$q);

	while ($r=pg_fetch_assoc($qr))
	{
		if ($r['date'] < $mfrom_date)
		{
			$beginning_balance += $r['qty']*$r['fraction'];
		}
		else
		{
			$temp = $r;
			$temp['particulars'] = lookUpTableReturnValue('x','account','account_id','account',$r['account_id']);
			$temp['type']='RR';
			$temp['qty_in1']=$r['qty1'];
			$temp['qty_in']=$r['qty'];
			$temp['reference'] = str_pad($r['rr_header_id'],8,'0',str_pad_left);
			$aRep[] = $temp;
		}
	}
	//purchase return
	$q = "select 
				poreturn_header.date, 
				poreturn_header.poreturn_header_id,
				poreturn_header.account_id,
				poreturn_header.status,
				poreturn_detail.qty as qty_out,
				poreturn_detail.qty1 as qty_out1,
				poreturn_detail.unit,
				poreturn_detail.cunit,
				poreturn_detail.fraction,
				poreturn_detail.cost,
				poreturn_detail.amount,
				'PR' as type
		 from 
		 		poreturn_header, 
				poreturn_detail
		where
				poreturn_detail.poreturn_header_id = poreturn_header.poreturn_header_id and
				(poreturn_header.status!='C' or poreturn_header.status='P') and
				stock_id='".$aBIN['stock_id']."'";
				
	$qr = pg_query($q) or die (pg_errormessage());
	//echo "c ".pg_num_rows($qr).$q;
	while ($r=pg_fetch_assoc($qr))
	{
		if ($r['date'] < $mfrom_date)
		{
			$beginning_balance -= $r['qty_out'];
		}
		else
		{
			$temp = $r;
			$temp['particulars'] = lookUpTableReturnValue('x','account','account_id','account',$r['account_id']);
			$temp['reference'] = str_pad($r['poreturn_header_id'],8,'0',str_pad_left);
			$aRep[] = $temp;
		}
	}
	// stock returns	
	$q = "select 
				returnstock_header.date, 
				returnstock_header.returnstock_header_id,
				returnstock_header.reference,
				returnstock_header.remark,
				returnstock_header.status,
				returnstock_detail.stock_id, 
				returnstock_detail.qty,
				returnstock_detail.qty1 as qty_in1,
				returnstock_detail.cost,
				returnstock_detail.amount,
				'RS' as type
		 from 
		 		returnstock_header, 
				returnstock_detail
		where
				returnstock_detail.returnstock_header_id = returnstock_header.returnstock_header_id and
				stock_id='".$aBIN['stock_id']."'";
				
	$qr = pg_query($q) or die (pg_errormessage());
	while ($r=pg_fetch_assoc($qr))
	{
		if ($r['date'] < $mfrom_date)
		{
			$beginning_balance += $r['qty'];
		}
		else
		{
			$temp = $r;
			$temp['particulars'] = $r->remark;
			$temp['qty_in']=$r['qty'];
			$temp['reference'] = str_pad($r['returnstock_header_id'],8,'0',str_pad_left);
			$aRep[] = $temp;
		}
	}
	// stock adjustments	
	$q = "select 
				invadjust_header.date, 
				invadjust_header.invadjust_header_id,
				invadjust_header.reference,
				invadjust_header.remarks as particulars,
				invadjust_header.status,
				invadjust_detail.stock_id, 
				invadjust_detail.qty,
				invadjust_detail.qty1,
				invadjust_detail.cost,
				invadjust_detail.amount,
				'CM' as type
		 from 
		 		invadjust_header, 
				invadjust_detail
		where
				invadjust_detail.invadjust_header_id = invadjust_header.invadjust_header_id and
				stock_id='".$aBIN['stock_id']."'";
				
	$qr = @pg_query($q) or message (pg_errormessage());
	
	while ($r=pg_fetch_assoc($qr))
	{
		if ($r['date'] < $mfrom_date)
		{
			$beginning_balance += $r['qty'];
		}
		else
		{
			$temp = $r;
			if ($r['qty'] < 0)
			{
			 $temp['qty_out']=$r['qty'];
			 $temp['qty_out1']=$r['qty'];
			}
      else
      {
			 $temp['qty_in']=$r['qty'];
			 $temp['qty_in1']=$r['qty'];
      } 
			$temp['reference'] = str_pad($r['invadjust_header_id'],8,'0',str_pad_left);
			$aRep[] = $temp;
			
		}
	}
		
	// stocks issuance
	$q = "select 
				si_header.date, 
				si_header.si_header_id,
				si_header.section_id,
				si_header.type,
				si_header.status,
				si_detail.stock_id, 
				si_detail.qty_out,
				si_detail.qty_out as qty_out1,
				si_detail.unit,
				si_detail.cunit,
				si_detail.fraction,
				si_detail.cost,
				si_detail.amount
		 from 
		 		si_header, 
				si_detail
		where
				si_detail.si_header_id = si_header.si_header_id and
				(si_header.status='A' or si_header.status='P') and
				stock_id='".$aBIN['stock_id']."'";
//				date>='$mfrom_date' and
//				date<='$mto_date'and
				
	$qr = pg_query($q) or die (pg_errormessage());
	//echo "c ".pg_num_rows($qr).$q;
	while ($r=pg_fetch_assoc($qr))
	{
		if ($r['date'] < $mfrom_date)
		{
			$beginning_balance -= $r['qty_out'];
		}
		else
		{
			$temp = $r;
			$temp['particulars'] = lookUpTableReturnValue('x','section','section_id','section',$r['section_id']);
			$temp['reference'] = str_pad($r['si_header_id'],8,'0',str_pad_left);
			$aRep[] = $temp;
		}
	}

	// sales transaction
	$q = "select 
				sales_header.date, 
				sales_header.sales_header_id,
				sales_tender.account_id, 
				sales_tender.account,
				sales_header.status,
				sales_detail.stock_id, 
				sales_detail.qty as qty_out,
				sales_detail.qty as qty_out1,
				sales_detail.price as cost,
				sales_detail.amount,
				'SI' as type,
				'1.00' as fraction 
		 from 
		 		sales_header, 
				sales_detail,
				sales_tender
		where
				sales_detail.sales_header_id = sales_header.sales_header_id and
				sales_tender.sales_header_id = sales_header.sales_header_id and
				( not (sales_header.status in ('V','C'))) and
				stock_id='".$aBIN['stock_id']."'";

				
	$qr = pg_query($q) or die (pg_errormessage());
	//echo "c ".pg_num_rows($qr).$q;
	while ($r=pg_fetch_assoc($qr))
	{
		if ($r['date'] < $mfrom_date)
		{
			$beginning_balance -= $r['qty_out'];
		}
		else
		{
			$temp = $r;
			$temp['particulars'] = $r['account'];
			if ($r['account'] == '')
			{
				$temp['particulars'] = 'CASH  SALES';
			}
			$temp['reference'] = str_pad($r['sales_header_id'],8,'0',str_pad_left);
			$aRep[] = $temp;
		}
	}


	// reporting
	$balance = $beginning_balance;
	$details .= adjustSize('Balance Forwarded',90).
				adjustRight(number_format($balance,3),11)."\n";

	foreach ($aRep as $temp)
	{
		$details .= adjustSize(ymd2mdy($temp['date']),10).' '.
					adjustSize($temp['reference'],8).' '.
					adjustSize($temp['type'],2).' '.
					adjustSize($temp['particulars'],25).' ';
		if ($temp['status'] == 'C')
		{
			$details .= "Cancelled Transaction \n";
		}
		else
		{
			$balance += $temp['qty_in1'] - $temp['qty_out1'];
			$details .=	adjustSize($temp['cunit'],5).' '.
						adjustRight($temp['fraction'],7).' '.
						adjustRight(number_format2($temp['qty_in'],3),12).' '.
						adjustRight(number_format2($temp['qty_out'],3),12).' '.
						adjustRight(number_format($balance,3),12)."\n";
		}		
	}
	$details .= str_repeat('-',120)."\n\n";
	$details1 = $header.$details;
	if ($p1 == 'Print Draft')
	{
		doPrint($header.$details);
	}	
?>
  <div align="center">
    <table width="1%" border="0" align="center" cellpadding="2" cellspacing="1">
      <tr> 
        <td height="27"><table width="100%" border="0" cellspacing="0" cellpadding="2">
            <tr bgcolor="#000033"> 
              <td width="34%" nowrap><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
                <img src="../graphics/bluelist.gif" width="16" height="17">BinCard 
                Preview</strong></font></td>
              <td width="66%" align="right" nowrap>&nbsp; </td>
            </tr>
          </table></td>
      </tr>
      <tr> 
        <td valign="top" bgcolor="#FFFFFF">
	   <textarea name="print_area" cols="110" rows="20"  wrap="off" readonly><?= $details1;?></textarea>
        </td>
      </tr>
    </table>
    <input name="p1" type="submit" id="p1" value="Print Draft" >
    <input name="p12" type="button" id="p12" value="Print"  onClick="printIframe(print_area)" >
  </div>
</form>

<?
}
?>
<iframe name="printit" id="printit" style="width:0px;height:0px;"></iframe>
