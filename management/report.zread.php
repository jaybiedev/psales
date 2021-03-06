<?
	function printZread($aRep, $output)
	{
		global $details1,  $term, $SYSCONF;
		
		
			$header ="\n";
			$header .= $SYSCONF['BUSINESS_NAME']."\n";
			$header .= $SYSCONF['BUSINESS_ADDR']."\n";
			$header .= $SYSCONF['BUSINESS_TIN']."\n\n";
			$header .= center('Z - R E A D I N G',40)."\n";
			$header .= 'Register :'.$term['TERMINAL']."\n";
			$header .= 'No. '.str_pad($aRep['zreadno'],8,'0',str_pad_left)."\n";
			$header .= 'Serial   :'.$term['SERIAL']."\n";
			$header .= 'Transaction Date : '.ymd2mdy($aRep['date'])."\n";
			$header .= 'Printed          : '.date('m/d/Y g:ia')."\n";
			$header .= str_repeat('-',40)."\n";
			$details .= 'Daily Sales       --> P'.adjustRight(number_format($aRep['current'],2),17)."\n";
			$details .= 'Old Grand Total   --> P'.adjustRight(number_format($aRep['oldgrand'],2),17)."\n";
			$details .= 'New Grand Total   --> P'.adjustRight(number_format($aRep['newgrand'],2),17)."\n\n\n";
			$details .= 'Total VAT Sales       P'.adjustRight(number_format($aRep['vatsales'],2),17)."\n";
			$details .= 'Total NON-Vat Sales   P'.adjustRight(number_format($aRep['nonvatsales'],2),17)."\n";
			$details .= 'Total TAX Amount      P'.adjustRight(number_format($aRep['taxamount'],2),17)."\n";
			$details .= 'Invoice From '.$aRep['from_invoice'].' To '.$aRep['to_invoice']."\n";
			$details .= str_repeat('-',40)."\n";
			$details .="\n\n\n\n";
			$details1 .= $header.$details;
			
			//writefile($header.$details, true, '/prog/log/zread.txt');
			if ($output == 'Print Draft' || $output == 'Print')
			{
				nPrinter($header.$details, $SYSCONF['RECEIPT_PRINTER_TYPE'], $SYSCONF['RECEIPT_PRINTER_DEST']);
			}
			return true;
	}


	if (!chkRights2('reconciliation','mview',$ADMIN['admin_id']))
	{
		message('You are NOT allowed to view Reports...');
		exit;
	}
	if ($p1=="") 
	{
		
		$yesterday = yesterday();
		$q = "select * from zread order by date desc offset 0 limit 1";
		$qr = @pg_query($q) or message(pg_errormessage());
		$r = @pg_fetch_object($qr);
		$lastday = $r->date;
		
		if ($lastday == $yesterday)
		{
			$date = ymd2mdy($yesterday);
		}
		else
		{
			$q = "select date '$lastday' + integer '1' as date ";
			$qr = @pg_query($q) or message(pg_errormessage());
			$r = @pg_fetch_object($qr);
			$date = ymd2mdy($r->date);
		}
				
	}
	
	if ($p1=="Go" || $p1=='Print Draft' ||  $p1=='Print') 
	{
		$date = $_REQUEST['date'];
		$mdate = mdy2ymd($date);
		$terminal = $_REQUEST['terminal'];
		$details1 = '';

		if ($mdate == date('Y-m-d'))
		{
			message1("It is suggested that Z-Reading be done at the end of the day...");
			exit;
		}
		$tables = currTables($mdate);
		$sales_header = $tables['sales_header'];
		$sales_detail = $tables['sales_detail'];
		$sales_tender = $tables['sales_tender'];
	
		$aZTerminal = null;
		$aZTerminal = array();

		if ($p1 =='Print Draft' && $SYSCONF['RECEIPT_PRINTER_TYPE'] == 'DRAFT')
		{
			nPrinter('<reset>', $SYSCONF['RECEIPT_PRINTER_TYPE'], $SYSCONF['RECEIPT_PRINTER_DEST']);
		}	
		
		if ($terminal == '')
		{
			$q = "select distinct ip from terminal where definition='CASHIERING' and value='Y'";
			$qtr = @pg_query($q) or message(pg_errormessage());
			while ($rt = @pg_fetch_object($qtr))
			{
				$q = "select distinct ip, value as terminal 
							from 
								terminal 
							where 
								definition='TERMINAL'";
				$q .= " and ip ='$rt->ip'";
				$qqtr = @pg_query($q) or message(pg_errormessage());
				while ($rrt = @pg_fetch_assoc($qqtr))
				{
					$aZTerminal[] = $rrt;
				}  
			}
		}
		else
		{	
			$q = "select distinct ip, value as terminal from terminal where definition='TERMINAL'";
			$q .= " and value ='$terminal'";
			$qqtr = @pg_query($q) or message(pg_errormessage());
			$rrt = @pg_fetch_assoc($qqtr);
			$aZTerminal[] = $rrt;  
		}


		$c=0;
		//while ($rt = @pg_fetch_object($qtr))
		foreach ($aZTerminal as $atemp)
		{
	
			$mterminal = $atemp['terminal'];
			$mip = $atemp['ip'];
			$aRep = array();
			$aRep = null;
			$term = terminal($mterminal);
			$aRep['date'] = $date;
			$q = "select * from zread where terminal='$mterminal' and date='$mdate'";
			$qqr = @pg_query($q) or message(pg_errormessage());

			if (pg_num_rows($qqr) > 0)
			{
				$aRep = pg_fetch_assoc($qqr);
				if ($aRep['current'] != '0')
				{
					printZread($aRep,$p1);
				}
				continue;
			}
			else
			{
				$q = "select * from userlog where ip='$mip' and substring(date_in,1,10)='$mdate'";
				$qqr = @pg_query($q) or message(pg_errormessage());

				if (pg_num_rows($qqr) == 0)
				{
		//			printZread($aRep,$p1);
					continue;
				}					
			}
			
			$q = "select 
						$sales_header.sales_header_id,
						$sales_header.date,
						$sales_header.net_amount,
						$sales_header.discount_amount,
						$sales_header.gross_amount,
						$sales_header.vat_sales,
						$sales_header.total_tax,
						$sales_header.invoice, 
						$sales_header.status,
						$sales_tender.amount as tender_amount,
						$sales_tender.account_id, 
						tender.tender_type,
						tender.tender_id,
						tender.bankable
				from 
						$sales_header, $sales_tender, tender 
				where 
						$sales_tender.sales_header_id=$sales_header.sales_header_id and
						tender.tender_id=$sales_tender.tender_id and
						$sales_header.status!='V' and 
						$sales_header.status!='C' and 
						date = '$mdate' and 
						ip='$mip'
				order by 
						$sales_header.sales_header_id, tender_id desc";
					
			$qr = @pg_query($q) or message(pg_errormessage());

			if (pg_num_rows($qr) == 0)
			{
				continue;
			}
			$from_invoice= $to_invoice = '';
			$mnet_amount  = $mtendered = $vatsales = $nonvatsales = $taxamount = $oldgrand = $newgrand = $current = $lines = 0;
			while ($r = @pg_fetch_object($qr))
			{
				$to_invoice = $r->invoice;
				$zero_rated = 'N';
				if ($r->status == 'V')
				{
					continue;
				}

				if ($r->tender_type == 'A')
				{
					$q = "select * from account where account_id = '$r->account_id'";
					$qa = @pg_query($q) or message(pg_errormessage());
					$ra = @pg_fetch_object($qa);
					$zero_rated = $ra->zero_rated;
				}
			  	$lines += $r->lines;
				if ($msales_header_id != $r->sales_header_id)
				{
					$mtendered = $excess = 0;
					$msales_header_id = $r->sales_header_id;
					$mnet_amount = $r->net_amount;
					$mdiscount_amount = $r->discount_amount;
		
					$total_discount += $r->discount_amount;
					$total_amount += $r->net_amount;
					
					$vatsales += $r->vat_sales;
					$nonvatsales += ($r->net_amount - $r->vat_sales);
					$taxamount += $r->total_tax;
					$sales_counter++;
					$increment_c=1;
				}
				else
				{
						$discount_amount = 0;
						$increment_c=0;
				}

				if ($r->tender_amount <= $mnet_amount)
				{
				  $amount = $r->tender_amount;	
				}  
				else
				{
					$amount = $mnet_amount;
					if ($r->tender_type !='C')
					{
							$excess = $r->net_amount   - ($mtendered + $r->tender_amount);
					}
				}	
				if ($r->bankable == 'Y' and $zero_rated != 'Y')
				{
					$current += $amount;
				}
				
				$mtendered += $amount;
				$mnet_amount -= $amount;
			}


			$q = "select * from zread where terminal='$mterminal' order by date desc offset 0 limit 1";
			$qqr = @pg_query($q) or message(pg_errormessage());
			$rr = @pg_fetch_object($qqr);
			if ($rr)
			{
				$oldgrand = $rr->newgrand;
				$from_invoice = str_pad($rr->to_invoice+1,8,'0',str_pad_left);
				$zreadno = $rr->zreadno+1;
			}
			else
			{
				$zreadno = 1;
			}
			
			$aRep = null;
			$aRep = array();
			
			//system control
			if ($term['EXTRA'] > '0')
			{
				$current = round($current*$term['EXTRA']/100,2);
				$nonvatsales = $nonvatsales*$term['EXTRA']/100;
			}
			
	
			// override vatsales and taxamount
			$vatsales = $current - $nonvatsales;
			$taxamount = $vatsales - ($vatsales/(1+$SYSCONF['TAXRATE']/100));
			$vatsales -= $taxamount;			
			//
			
			$newgrand = $oldgrand + $current;
			$aRep['date'] = $mdate;
			$aRep['terminal'] = $mterminal;
			$aRep['zreadno'] = $zreadno;
			$aRep['newgrand'] = $newgrand;
	
			$aRep['oldgrand'] = $oldgrand;
			$aRep['current'] = $current;
			$aRep['vatsales'] = $vatsales;
			$aRep['nonvatsales'] = $nonvatsales;
			$aRep['taxamount'] = $taxamount;
			$aRep['lines'] = $lines;
			$aRep['to_invoice'] = $to_invoice;
			$aRep['from_invoice'] = $from_invoice;
			
			$q = "insert into zread (zreadno, date, terminal, current, oldgrand, newgrand, vatsales, nonvatsales, taxamount, lines, from_invoice, to_invoice, admin_id)
						values
								('".$aRep['zreadno']."', '".$aRep['date']."', '".$aRep['terminal']."', '".$aRep['current']."', 
								'".$aRep['oldgrand']."', '".$aRep['newgrand']."', '".$aRep['vatsales']."', '".$aRep['nonvatsales']."', 
								'".$aRep['taxamount']."', '".$aRep['lines']."','".$aRep['from_invoice']."', '".$aRep['to_invoice']."','".$ADMIN['admin_id']."')";
			$qqr = @pg_query($q) or message(pg_errormessage());
			
			if ($aRep['current'] != '0')
			{
				printZread($aRep, $p1);
			}
	
		
		}
		
		
		$q = "select * from eoday where date='$mdate'";
		$qr = @pg_query($q) or message('Error Posting End of Day : '.pg_errormessage().$q);
		if (@pg_num_rows($qr) == 0)
		{
			include_once('../backend/eoday.php');
			$aPost = eoday(mdy2ymd($aRep['date']), mdy2ymd($aRep['date']));
			if ($aPost['Ok'] == 0)
			{
				message1($aPost['message']);
			}
		}
		
		if ($p1 =='Print Draft' && $SYSCONF['RECEIPT_PRINTER_TYPE'] == 'DRAFT')
		{
			nPrinter('<reset>', $SYSCONF['RECEIPT_PRINTER_TYPE'], $SYSCONF['RECEIPT_PRINTER_DEST']);
		}	
	
} //printing
?> 
<form name='form1' method='post' action=''>
  <table width="70%" border="0" align="center" cellpadding="0" cellspacing="2">
    <tr bgcolor="#EFEFEF"> 
      <td height="24" colspan="2" background="../graphics/table0_horizontal.PNG"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <strong> <img src="../graphics/bluelist.gif" width="16" height="17"><font color="#FFFFCC"> 
        Z-Read Closing Report</font></strong></font></td>
    </tr>
    <tr bgcolor="#EFEFEF"> 
      <td width="11%" align="center" nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">Date</font></td>
      <td width="89%" nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">Terminal</font></td>
    </tr>
    <tr> 
      <td align="right" nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="date" type="text" id="date" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $date;?>" size="8">
        <img src="../graphics/arrow_down.jpg" onClick="popUpCalendar(this, form1.date, 'mm/dd/yyyy')"> 
        </font> <font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
        </font></td>
      <td nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="terminal" type="text" id="terminal" value="<?= $terminal;?>" size="5">
        <input name="p1" type="Submit" id="p1" value="Go">
        <input name="p1" type="submit" id="p1" value="Print Draft" >
        <input name="p1" type="button" id="p1" value="Print"  onClick="printIframe(print_area)" >
        </font></td>
    </tr>
    <tr> 
      <td colspan="2"><hr color="#993300" size="1"></td>
    </tr>
    <tr bgcolor="#DADADA"> 
      <td colspan="2"><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"><b>Z-Reading 
        Preview</b></font></td>
    </tr>
    <tr>
      <td colspan="2"><textarea name="textarea" cols="95" rows="20" wrap="OFF"><?= $details1;?></textarea></td>
    </tr>
  </table>
  <div align=center>
    <input name="p1" type="submit" id="p1" value="Print Draft" >
    <input name="p122" type="button" id="p122" value="Print"  onClick="printIframe(print_area)" >
  </div>
</form>

<iframe name="printit" id="printit" style="width:0px;height:0px;"></iframe>
