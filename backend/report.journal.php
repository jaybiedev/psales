<?
	if (!chkRights2('cashierreports','mview',$ADMIN['admin_id']))
	{
		message('You are NOT allowed to view Reports...');
		exit;
	}
	if ($p1=="") 
	{
		$date = ymd2mdy(yesterday());
	}
	
	if ($p1=="Go" || $p1=='Print Draft' ||  $p1=='Print') 
	{
		$date = mdy2ymd($_REQUEST['invdate']);

		if ($date == '' || $date == '--')
		{
			if ($_REQUEST['mdate'] == '' || $_REQUEST['mdate'] == '--')
			{
				$date = date('Y-m-d');
			}
			else
			{
				$date = $_REQUEST['mdate']; //paramter from cashier monitor
			}
		}
				
		$tables = currTables($date);
		$sales_header = $tables['sales_header'];
		$sales_detail = $tables['sales_detail'];
		$sales_tender = $tables['sales_tender'];

		$invoice = $_REQUEST['invoice'];
		$showbarcode = $_REQUEST['showbarcode'];
		$terminal = $_REQUEST['terminal'];

		if ($terminal == '')
		{
			$terminal = $_REQUEST['mterminal']; //parameter
		}
		
		$term = terminal($terminal);
		$details1 = '';
		$q = "select invoice from $sales_header where date= '$date'  and terminal='$terminal'";
		$qrs = @pg_query($q);

		while ($inv = pg_fetch_object($qrs))
		{	
			$invoice = $inv->invoice;
			$aCashier = null;
			$aCashier = array();
			$aItems = null;
			$aItems = array();
			$invoice = str_pad($invoice,8,'0',str_pad_left);	
			$q = "select * from $sales_header where invoice= '$invoice'  and terminal='$terminal'";
			$aCashier= fetch_assoc($q);
	
			$aCashier['nonvat_sales'] = $aCashier['net_amount'] - $aCashier['vat_sales'];
	
			if ($aCashier['sales_header_id'] > 0)
			{
				$q = "select 
							stock, 
							sd.barcode,
							sd.qty,
							sd.price,
							sd.price1,
							sd.amount,
							sd.discount,
							sd.cdisc,
							sd.sdisc,
							fraction
						from
							stock,
							$sales_detail as sd
						where
							stock.stock_id = sd.stock_id and 
							sd.sales_header_id='".$aCashier['sales_header_id']."'";
	
						$qr = @pg_query($q) or message1(pg_errormessage());
						
						while ($r = @pg_fetch_assoc($qr))
						{
							$aItems[] = $r;
						}
	
						$q = "select 
							tender.tender_type,
							tender.tender,
							$sales_tender.amount,
							'Tender' as type,
							$sales_tender.cardno,
							$sales_tender.account_id,
							$sales_tender.account
						from 
							$sales_tender,
							tender
						where
							tender.tender_id = $sales_tender.tender_id and 
							$sales_tender.sales_header_id = '".$aCashier['sales_header_id']."'";
				$qr = @pg_query($q) or message1(pg_errormessage());
				
				while ($r = @pg_fetch_assoc($qr))
				{
					$aItems[] = $r;
					if ($aCashier['account'] == '' && $r['account']!='')
					{
						$aCashier['account'] = $r['account'];
						$aCashier['account_id'] = $r['account_id'];
					}
					if ($aCashier['account'] == '' && $r['account']=='')
					{
						$q="select * from reward,account where reward.sales_header_id='".$aCashier['sales_header_id']."'
											and account.account_id=reward.account_id and 
											reward.invoice='".$aCashier['invoice']."'";
						$qrd = @pg_query($q) or message1(pg_errormessage());
						$rd = @pg_fetch_object($qrd);
	
						$aCashier['account'] = trim($rd->account).'('.trim($rd->cardno).')';
						$aCashier['account_id'] = $rd->account_id;										
					}
					if ($aCashier['cardno'] == '' && $r['cardno']!='')
					{
						$aCashier['cardno'] = $r['cardno'];
					}
				}
							
			} //cashier sales_header_id
			else
			{
				message1('Invoice/Receipt NOT Found...');
			}	
			
			$cashiername = lookUpTableReturnValue('x','admin','admin_id','name',$aCashier['admin_id']);		
			$details .= center($SYSCONF['BUSINESS_NAME'],38)."\n";
			if ($SYSCONF['PROPRIETOR'] != '')
			{
				$details .= center($SYSCONF['PROPRIETOR'],38)."\n";
			}
			$details .=	center($SYSCONF['BUSINESS_ADDR'],38)."\n";
			if ($SYSCONF['BUSINESS_TEL'] != '')
			{
				$details .= center($SYSCONF['BUSINESS_TEL'],38)."\n";
			}
			$details .= center($SYSCONF['BUSINESS_TIN'],38)."\n".
			adjustSize('Date:'.ymd2mdy($aCashier['date']),21).' '.
			adjustSize('Time:'.$aCashier['time'],18)."\n".
			adjustSize('Term/Serial No.',21).' '.	
			adjustSize($terminal.'/'.$term['SERIAL'],18)."\n".
			adjustSize('Cashier:'.substr($cashiername,0,13),21).' '.
			adjustSize('CSI#:'.$aCashier['invoice'],18)."\n".

			adjustSize('Name:'.($aCashier['account']==''?str_repeat('_',35):$aCashier['account']),40)."\n".
			'Description     Qty    Price   Total'."\n".
			str_repeat('-',40)."\n";
				
						
			$tender = $servicecharge = "";		
	
			$chargeinvoice = 0;		
			foreach ($aItems as $temp)
			{
				if ($temp['type'] == 'Tender')
				{
					if ($temp['tender_type'] == 'A')
					{
						$chargeinvoice = 1;
					}
					if ($temp['cardno'] != '')
					{
						$tender .= adjustSize(substr($temp['cardno'],0,16),16).' '.
							adjustSize(substr($temp['tender'],0,10),10).'P'.
							adjustRight(number_format($temp['amount'],2),12)."\n";
					}
					else
					{
						$tender .= space(15).adjustSize(substr($temp['tender'],0,12),12).'P'.
							adjustRight(number_format($temp['amount'],2),12)."\n";
					}
				}
				elseif ($temp['type'] == 'ServiceCharge')
				{
						$service .= space(11).' '.
							adjustSize(substr('Service Charge ',0,15),15).'P'.
							adjustRight(number_format($temp['amount'],2),12)."\n";
				}
				else
				{
						if ($showbarcode == 'Y')
						{
							$details .= adjustSize($temp['barcode'],16)."\n";
						}
						$cqty = '';
						if (intval($temp['qty']) !=  $temp['qty'])
						{
							$cqty = number_format($temp['qty'],3);
						}
					else
					{
							$cqty = number_format($temp['qty'],0);
					}
					
					$details .= adjustSize(substr($temp['stock'],0,13),13).' '.
						adjustRight($cqty,6).' '.
						adjustRight(number_format($temp['price'],2),8).' '.
						adjustRight(number_format($temp['amount'],2),10)."\n";
					$no_items += $temp['qty'];						
				}
			}
			
			$details .=	str_repeat('-',40)."\n".
					adjustSize($no_items.' Item(s)',14).space(1).
					adjustSize('Sale Total',12).'P'.
					adjustRight(number_format($aCashier['gross_amount'],2),12)."\n";
				if (strlen($service) > 1)
				{
					$details .= $service;
				}						
					if ($aCashier['discount_amount'] != 0)
					{
						$details .= space(15).adjustSize('Discount',12).'P'.
						adjustRight(number_format($aCashier['discount_amount'],2),12)."\n";
						$details .= space(15).adjustSize('Net Amount',12).'P'.
						adjustRight(number_format($aCashier['net_amount'],2),12)."\n\n";
					}
									
					$details .= $tender;
					if ($aCashier['tender_amount'] > $aCashier['net_amount'])
					{
						$details .= space(15).adjustSize('Change',12).'P'.
						adjustRight(number_format($aCashier['tender_amount'] - $aCashier['net_amount'],2),12)."\n";
					}
			
			$details .= "\n";
			$details .= space(15).adjustSize("VATable",12).' '.adjustRight(number_format($aCashier['vat_sales']-$aCashier['total_tax'],2),12)."\n";
			$details .= space(15).adjustSize("VAT Exempt",12).' '.adjustRight(number_format($aCashier['nonvat_sales'],2),12)."\n";
			$details .= space(15).adjustSize("Tax ".$SYSCONF['TAXRATE']."%",12).' '.adjustRight(number_format($aCashier['total_tax'],2),12)."\n";
			
			if ($aCashier['reward_total']>0)
			{
			  $details .= "\nPoints Earned: ".$aCashier['reward_total']."\n";
			  $details .= "Total Points : ".$aCashier['reward_total']+$aCashier['points_balance']."\n";
			}
			$details .= str_repeat('-',40)."\n";
			
		if ($aCashier['status'] =='V')
		{
			$details .= str_repeat('-',40)."\n";
			$details .= center('*** VOIDED TRANSACTION ***',40)."\n";
			$details .= str_repeat('-',40)."\n\n";
		}
		else
		{
			if ($printagain>'1' && $aCashier['REPRINT'] != '1')
			{
				$details .= "\n\n\n";
				$details .= space(6)."---------------------------\n";
				$details .= center('['.$aCashier['cardno'].']'.$aCashier['account'],40)."\n\n";
			}
	
			if ($chargeinvoice == '1')
			{
				$details .= center($SYSCONF['CHARGE_FOOTER1'],40)."\n";
			}
			elseif ($SYSCONF['RECEIPT_FOOTER1'] != '')
			{
//				$details .= center($SYSCONF['RECEIPT_FOOTER1'],40)."\n";
			}
				
			if ($SYSCONF['RECEIPT_FOOTER2'] != '')
			{
				if (strlen($SYSCONF['RECEIPT_FOOTER2'])<=40)
				{
//					$details .= center($SYSCONF['RECEIPT_FOOTER2'],40)."\n";
				}
				else
				{
//					$details .= center($SYSCONF['RECEIPT_FOOTER2'],40)."\n";
				}	
			}
			$details .=	"\n";
			$details1 .= $details;
			$details = '';
		}
	}	
	$details1 .=	"\n\n\n\n\n\n\n\n\n";
	$details = $details1;
		
	if ($p1 == 'Print Draft')
	{
			nPrinter($details,$SYSCONF['RECEIPT_PRINTER_TYPE'],$SYSCONF['RECEIPT_PRINTER_DEST']);
			if ($SYSCONF['CUTTER'] == 'Y')
			{
				nPrinter(chr(27)."m", $SYSCONF['RECEIPT_PRINTER_TYPE'], $SYSCONF['RECEIPT_PRINTER_DEST']);
			}
	}
} //printing
	
?> 
<form name='form1' method='post' action=''>
  <table width="80%" border="0" align="center" cellpadding="0" cellspacing="2">
    <tr bgcolor="#EFEFEF"> 
      <td height="24" colspan="3" background="../graphics/table0_horizontal.PNG"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <strong> <img src="../graphics/bluelist.gif" width="16" height="17"><font color="#FFFFCC"> 
        Re-Print Receipt</font></strong></font></td>
    </tr>
    <tr bgcolor="#EFEFEF"> 
      <td width="11%" nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">Invoice Date </font></td>
      <td width="6%" nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">Terminal</font></td>
      <td width="83%" nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">Barcode</font></td>
    </tr>
    <tr> 
      <td align="right" nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="invdate" type="text" id="invdate" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $invdate;?>" size="8"  onKeypress="if(event.keyCode == 13){document.getElementById('invdate').focus();return false;}">
        <img src="../graphics/dwn-arrow-grn.gif" height="12" width="12" onClick="popUpCalendar(this, form1.invdate, 'mm/dd/yyyy')"> 
        </font>        </font> <font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
        </font></td>
      <td nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="terminal" type="text" id="terminal" value="<?= $terminal;?>" size="5">
        </font></td>
      <td nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <?= lookUpAssoc('showbarcode',array('No'=>'N','Yes'=>'Y'),$showbarcode);?><input name="p1" type="Submit" id="p123" value="Go">
        </font></td>
    </tr>
    <tr> 
      <td colspan="3"><hr color="#993300" size="1"></td>
    </tr>
  </table>
  <table width="80%" border="0" cellspacing="1" cellpadding="1" height="1%" bgcolor="#DADADA" align="center">
    <tr bgcolor="#333366"> 
      <td height="26" bgcolor="#DADADA"> <font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"><b>Receipt 
        Preview</b></font></td>
    </tr>
    <tr> 
      <td valign="top" bgcolor="#FFFFFF"> <textarea name="print_area" cols="95" rows="22" wrap="OFF" readOnly><?= $details1;?></textarea></td>
    </tr>
  </table>
<div align=center>
    <input name="p1" type="submit" id="p1" value="Print Draft" >
    <input name="p122" type="button" id="p122" value="Print"  onClick="printIframe(print_area)" >
  </div>
</form>

<iframe name="printit" id="printit" style="width:0px;height:0px;"></iframe>
