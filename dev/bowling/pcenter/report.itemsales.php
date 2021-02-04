<script>
function vSelect(fd, td, aid)
{
	document.getElementById('from_date').value = fd;
	document.getElementById('to_date').value = td;
	document.getElementById('account_id').value = aid;

}
</script>
<?
	if (!chkRights2('salesreports','mview',$ADMIN['admin_id']))
	{
		message('You are NOT allowed to view Reports...');
		exit;
	}
	if ($p1=="") 
	{
		$from_date = date('m/01/Y');
		$to_date = ymd2mdy(yesterday());
	}
	elseif ($p1 == 'select')
	{
		$q = "select * from reports where report_id = '$rid'";
		$qr = @pg_query($q);
		$r = @pg_fetch_object($qr);
		$from_date = ymd2mdy($r->date_from);
		$to_date = ymd2mdy($r->date_to);
		$account_id = $r->account_id;
	}	
	if ($p1=="Go" || $p1=='Print Draft' ||  $p1=='Print') 
	{
		$from_date = $_REQUEST['from_date'];
		$to_date = $_REQUEST['to_date'];
		$account_id = $_REQUEST['account_id'];
		
		$mfrom_date = mdy2ymd($from_date);
		$mto_date = mdy2ymd($to_date);
		
		if ($account_id != '')
		{
			$q = "select * from reports
					where
						account_id = '$account_id' and
						((date_from <= '$mfrom_date' and
						date_to >= '$mfrom_date') or 
						(date_from <= '$mto_date' and
						date_to >= '$mto_date'))";
			$qr = @pg_query($q);
			
			if (@pg_num_rows($qr) > 0)
			{
				echo "<div align='center'><b>Conflicting Report Date Coverage</b></div>";
				while ($r=@pg_fetch_object($qr))
				{
					echo "<div align='center'>".ymd2mdy($r->date_from).' - '. ymd2mdy($r->date_to).' '.$r->audit."</div>";
				}				
			}
					
					
		}
		$tables = currTables($mfrom_date);
		$sales_header = $tables['sales_header'];
		$sales_detail = $tables['sales_detail'];
		$sales_tender = $tables['sales_tender'];

		$q = "select
					stock.stock,
					stock.barcode,
					stock.cost1,
					stock.account_id,
					stock.stock_id,
					sd.price1,
					sd.price,
					sum(sd.qty*sd.fraction) as qty,
					sum(sd.amount) as amount
					
			from
					$sales_header as sh,
					$sales_detail as sd,
					stock
			where
					sh.sales_header_id = sd.sales_header_id and
					stock.stock_id = sd.stock_id and
					sh.date>='$mfrom_date' and
					sh.date<= '$mto_date' and
					sh.status!='V'";
					
			if ($account_id != '')
			{
				$q .= " and stock.account_id = '$account_id'";
			}
			$q .= " group by stock, stock.barcode, stock.stock_id, sd.price1, 
									sd.price, stock.cost1, stock.account_id ";
			
			$q .= " order by stock.account_id, stock.stock ";
			
			$qr = @pg_query($q) or message1(pg_errormessage().$q);
			
			if ($p1 == 'Print Draft')
			{
				$header = "<small3>\n";
			}	
			if ($account_id == '')
			{
				$account_code = '';
				$account = 'ALL SUPPLIERS';
			}
			else
			{
				$qq = "select * from account where account_id = '$account_id'";
				$qqr = @pg_query($qq) or message(pg_errormessage());
				$rr = @pg_fetch_object($qqr);
				$account_code = $rr->account_code;
				$account = $rr->account;
			}
			$header .= $SYSCONF['BUSINESS_NAME']."\n";
			$header .= $SYSCONF['BUSINESS_ADDR']."\n\n";
			$header .= "SALES BY STOCK ITEM\n";
			$header .= "Supplier : [$account_code] $account\n";
			$header .= "From     : ".$from_date." To ".$to_date."\n";
			$header .= "Printed  : ".date('m/d/Y g:ia').' '.$ADMIN['username']."\n\n";
			$header .= "-------------- --------------------------- --------- --------- ------------|--------- --------- ------------|--------- ------------\n";
			$header .= "                                           TotalQty   Regular  Amount Sold | Promo      Promo    Total Promo|              Total\n";
			$header .= "Barcode           Item Description           Sold     Price    (RegularPrc)|  Sold      Price       Sales   |   Cost       Cost \n";
			$header .= "-------------- --------------------------- --------- --------- ------------|--------- --------- ------------|--------- ------------\n";
			
			$details = $msupplier = '';
			$total_regular = $total_sold = $total_cost = $tqty = 0;
			$stotal_cost = $stotal_sold = $stotal_regular = $stqty = 0;
			$lc = 10;
			
			$aRep = null;
			$aRep = array();
			$temp = null;
			$temp = array();
			$mstock_id = '';
			while ($r = @pg_fetch_assoc($qr))
			{
				if ($mstock_id != $r['stock_id'])					
				{
					if ($mstock_id != '')
					{
						$aRep[] = $temp;
					}
					$temp = null;
					$temp = array();
			
					$mstock_id = $r['stock_id'];
					$temp = $r;
				}
				if ($r['price'] != $r['price1'])
				{
					$temp['promo_qty'] += $r['qty'];
					$temp['promo_price'] = $r['price'];
					$temp['promo_amount'] += $r['amount'];  
				}
				else
				{
					$temp['regular_qty'] += $r['qty'];
					$temp['regular_price'] = $r['price'];
				}
				$temp['regular_amount'] += $r['price1']*$r['qty'];  
				$temp['sold_qty'] += $r['qty'];
				$temp['sold_amount'] += $r['amount'];
			}
			$aRep[] = $temp;
			
			foreach ($aRep as $temp)
			{
				if ($temp['account_id'] != $maccount_id)
				{
					if ($maccount_id != '')
					{
						$details .= space(9).' Sub Total ---->  '.space(13).
						adjustRight(number_format($stqty,2),12).' '.
						space(10).
						adjustRight(number_format($stotal_regular,2),12).'|'.
						adjustRight(number_format($spqty,2),9).' '.
						space(10).
						adjustRight(number_format($stotal_promo,2),12).'|'.
						space(10).
						adjustRight(number_format($stotal_cost,2),12)."\n";
						$lc++;
						$lc++;
					}
					$details .= adjustSize("SUPPLIER  [ ".strtoupper(lookUpTableReturnValue('x','account','account_id','account_code',$temp['account_id'])).'] '.strtoupper(lookUpTableReturnValue('x','account','account_id','account',$temp['account_id'])),75).
									'|'.space(32).'|'."\n";
					$details .= str_repeat('-',50).space(25).'|'.space(32).'|'."\n";
					$maccount_id = $temp['account_id'];
					
					$stotal_cost = $stotal_sold = $stotal_regular = $stqty = 0;
					$spqty = $stotal_promo = 0;
					$lc++;
					$lc++;
				}

				if (intval($temp['sold_qty']) == $temp['sold_qty'])
				{
					$csold_qty = number_format($temp['sold_qty'],0);
				}				
				else
				{
					$csold_qty = number_format($temp['sold_qty'],3);
				}
				
				if (intval($temp['promo_qty']) == $temp['promo_qty'])
				{
					$cpromo_qty = number_format2($temp['promo_qty'],0);
				}				
				else
				{
					$cpromo_qty = number_format($temp['promo_qty'],3);
				}
				
				$tqty += $temp['sold_qty'];
				$tpqty += $temp['promo_qty'];
				$cost_amount = $temp['sold_qty'] * $temp['cost1'];
				
				$stqty += $temp['sold_qty'];
				$spqty += $temp['promo_qty'];
				$stotal_regular += $temp['regular_amount'];
				$stotal_cost += $cost_amount;
				$stotal_sold += $temp['sold_amount'];
				$stotal_promo += $temp['promo_amount'];

				$total_regular += $temp['regular_amount'];
				$total_cost += $cost_amount;
				$total_sold += $temp['sold_amount'];
				$total_promo += $temp['promo_amount'];

				
				$details .= adjustSize($temp['barcode'],14).' '.
							adjustSize($temp['stock'],27).' '.
							adjustRight($csold_qty,9).' '.
							adjustRight(number_format($temp['price1'],2),9).' '.
							adjustRight(number_format($temp['regular_amount'],2),12).'|'.
							adjustRight($cpromo_qty,9).' '.
							adjustRight(number_format2($temp['promo_price'],2),9).' '.
							adjustRight(number_format2($temp['promo_amount'],2),12).'|'.
							adjustRight(number_format($temp['cost1'],2),9).' '.
							adjustRight(number_format($cost_amount,2),12)."\n";
				$lc++;
				if ($lc > 50)
				{
					if ($p1 == 'Print Draft')
					{
						$details .= "<eject>";
						nPrinter($header.$details, $SYSCONF['REPORT_PRINTER_TYPE'], $SYSCONF['REPORT_PRINTER_DEST']);
						$details = '';
					}
					$details1 .= $header.$details;	
					$details = '';
					$lc=10;
				}			
			}
					
			$details .= "-------------- --------------------------- --------- --------- ------------|--------- --------- ------------|--------- ------------\n";
			$details .= space(9).' Sub Total ---->  '.space(13).
			adjustRight(number_format($stqty,2),12).' '.
			space(10).
			adjustRight(number_format($stotal_regular,2),12).'|'.
			adjustRight(number_format($spqty,2),9).' '.
			space(10).
			adjustRight(number_format($stotal_promo,2),12).'|'.
			space(10).
			adjustRight(number_format($stotal_cost,2),12)."\n";

			if ($total_sold != $stotal_sold || $stotal_promo !=  $total_promo)
			{
	 	 		$details .= space(9).' Grand Total ---->'.space(16).
	 		    	adjustRight(number_format($tqty,2),9).' '.
			  		space(10).
					adjustRight(number_format($total_regular,2),12).'|'.
					adjustRight(number_format($tpqty,2),9).' '.
					space(10).
					adjustRight(number_format($total_promo,2),12).'|'.
					space(9).
					adjustRight(number_format($total_cost,2),13)."\n";
			}
			$details .= "-------------- --------------------------- --------- --------- ------------|--------- --------- ------------|--------- ------------\n";
					
		
		$details1 .= $header.$details;
		
		if ($p1 == 'Print Draft')
		{
			nPrinter($header.$details, $SYSCONF['REPORT_PRINTER_TYPE'], $SYSCONF['REPORT_PRINTER_DEST']);
		}

		if ($p1 == 'Print' || $p1 == 'Print Draft')
		{		
			$q = "select * from reports where report='report.itemsales' and date_from='$mfrom_date' and date_to = '$mto_date' and account_id='$account_id'";
			$qr = @pg_query($q) or message(pg_errormessage());
			$mdate = date('Y-m-d');
			if (@pg_num_rows($qr) == '0' && $qr)
			{
				$audit = 'Generated: '.$ADMIN['username'].' on '.date('m/d/Y g:ia');
				$q = "insert into reports (report, date_generated, date_from, date_to, account_id, audit)
							values ('report.itemsales','$mdate', '$mfrom_date','$mto_date', '$account_id', '$audit')";
				$qr = @pg_query($q) or message(pg_errormessage());
			}
			elseif (@pg_num_rows($qr) > '0')
			{
				$r = @pg_fetch_object($qr);
				$audit = $r->audit.' ; '.$ADMIN['username'].' on '.date('m/d/Y g:ia');
				$q = "update report set audit = '$audit' where report_id='$r->report_id'";
				$qr = @pg_query($q) or message(pg_errormessage());
			}
		}
	} //printing
?> 
<form name='form1' method='post' action=''>
  <table width="80%" border="0" align="center" cellpadding="0" cellspacing="2">
    <tr bgcolor="#EFEFEF"> 
      <td height="24" colspan="3" background="../graphics/table0_horizontal.PNG"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <strong> <img src="../graphics/bluelist.gif" width="16" height="17"><font color="#FFFFCC"> 
        Sales By Stock Item</font></strong></font></td>
    </tr>
    <tr bgcolor="#EFEFEF"> 
      <td width="10%" align="center" nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">From</font></td>
      <td width="10%" align="center" nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">To</font></td>
      <td width="90%" nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">Supplier</font></td>
    </tr>
    <tr> 
      <td align="right" nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="from_date" type="text" id="from_date" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $from_date;?>" size="8">
        <img src="../graphics/dwn-arrow-grn.gif" height="12" width="12" onClick="popUpCalendar(this, form1.from_date, 'mm/dd/yyyy')"> 
        </font></td>
      <td align="right" nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="to_date" type="text" id="to_date" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $to_date;?>" size="8">
        </font> <font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"><img src="../graphics/dwn-arrow-grn.gif" height="12" width="12" onClick="popUpCalendar(this, form1.to_date, 'mm/dd/yyyy')">&nbsp; 
        </font></td>
      <td nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <select name="account_id" style="width:250">
          <option value="">All Suppliers</option>
          <?
			$q = "select * from account where enable='Y' and account_type_id in ('1','8') ";
			if ($account_id != '' && ($p1 == 'Go' || $p1 == 'Print' || $p1 == 'Print Draft'))
			{
				$q .= " and account_id ='$account_id'";
			}
			$q .= " order by account_code";
			$qr = @pg_query($q);
			while ($r = @pg_fetch_object($qr))
			{
				if ($account_id == $r->account_id)
				{
					echo "<option value=$r->account_id selected>$r->account_code &nbsp;  $r->account</option>";
				}
				else
				{
					echo "<option value=$r->account_id> $r->account_code &nbsp;  $r->account</option>";
				}	
			}
		?>
        </select>
        <input name="p1" type="Submit" id="p1" value="Go">
        <input name="p1" type="Submit" id="p1" value="Select Report">
        </font></td>
    </tr>
	<?
	if ($p1 == '' || $p1 == 'Select Report' || $p1 == 'select')
	{
	?>
	<tr><td colspan="3"><br>
        <table width="95%" border="0" align="center" cellpadding="1" cellspacing="1" bgcolor="#EFEFEF">
          <tr bgcolor="#FFCCFF"> 
            <td colspan="6"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Last 
              25 Reports Generated</strong></font></td>
          </tr>
          <tr align="center"> 
            <td width="5%"><font color="#CC0000" size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></td>
            <td width="10%"><font color="#CC0000" size="2" face="Verdana, Arial, Helvetica, sans-serif">Generated</font></td>
            <td width="10%"><font color="#CC0000" size="2" face="Verdana, Arial, Helvetica, sans-serif">From</font></td>
            <td width="10%"><font color="#CC0000" size="2" face="Verdana, Arial, Helvetica, sans-serif">To</font></td>
            <td width="30%"><font color="#CC0000" size="2" face="Verdana, Arial, Helvetica, sans-serif">Supplier</font></td>
            <td width="35%"><font color="#CC0000" size="2" face="Verdana, Arial, Helvetica, sans-serif">Audit</font></td>
          </tr>
          <?
		  $q = "select * from reports where report='report.itemsales' order by date_from desc offset 0 limit 25";
		  $qr = @pg_query($q) or message(pg_errormessage());
		  $ctr=0;
		  while ($r = @pg_fetch_object($qr))
		  {
		  	$ctr++;
			$qq = "select * from account where account_id = '$r->account_id'";
			$qqr = @pg_query($qq) or message(pg_errormessage());
			$rr = @pg_fetch_object($qqr);
			$account_code = $rr->account_code;
			$account = $rr->account;
		?>
          <tr bgcolor="#FFFFFF"> 
            <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
              <?= $ctr;?>
              .</font></td>
            <td align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
              <a href="?p=report.itemsales&p1=select&rid=<?=$r->report_id;?>"> 
              <?= ymd2mdy($r->date_generated);?>
              </a></font></td>
            <td align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
              <?= ymd2mdy($r->date_from);?>
              </font></td>
            <td align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
              <?= ymd2mdy($r->date_to);?>
              </font></td>
            <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href="?p=report.itemsales&p1=select&rid=<?=$r->report_id;?>"> 
              <?= $account_code.' '. $account;?>
              </a></font></td>
            <td><font size="1" face="Verdana, Arial, Helvetica, sans-serif"> 
              <?= $r->audit;?>
              </font></td>
          </tr>
          <?
		  }
		  ?>
        </table>

	</td></tr>
	<?
	}
	else
	{
	?>
    <tr bgcolor="#DADADA"> 
      <td colspan="3"><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"><b>Report 
        Preview</b></font></td>
    </tr>
    <tr> 
      <td colspan="3"><textarea name="print_area" cols="110" readOnly rows="20" wrap="OFF"><?= $details1;?></textarea></td>
    </tr>
	<?
	}
	?>
  </table>
  <div align=center>
    <input name="p1" type="submit" id="p1" value="Print Draft" >
    <input name="p122" type="button" id="p122" value="Print"  onClick="printIframe(print_area)" >
  </div>
</form>

<iframe name="printit" id="printit" style="width:0px;height:0px;"></iframe>
