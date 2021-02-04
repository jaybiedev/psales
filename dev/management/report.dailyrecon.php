<?
	if ($date == '')
	{
		$date = ymd2mdy(yesterday());
	}
	if ($p1 == 'Go' || $p1=='Print' || $p1 == 'Print Draft')
	{
		$mdate = mdy2ymd($_REQUEST['date']);
		$terminal = $_REQUEST['terminal'];

		$tables = currTables($mdate);
		$sales_header = $tables['sales_header'];
		$sales_detail = $tables['sales_detail'];
		$sales_tender = $tables['sales_tender'];
	
	
		$q = "select distinct ip, value as terminal from terminal where definition='TERMINAL'";
		if ($terminal != '')
		{
			$q .= " and value ='$terminal'";
		}

		$qtr = @pg_query($q) or message(pg_errormessage());
		if ($username != '')
		{
			$admin_id = lookUpTableReturnValue('x','admin','username','admin_id',$username);
		}
		
		while ($rt = @pg_fetch_object($qtr))
		{
			$term = terminal($rt->terminal);
				
			$aSC = null;
			$aSC = array();
			
			$aTT = null; //tender types summary
			$aTT = array();

			$aTB = null;   //detailed breakdown of tender types
			$aTB = array();
			
			$q = "select 
							$sales_header.sales_header_id,
							$sales_header.invoice,
							$sales_header.date,
							$sales_header.net_amount,
							$sales_header.discount_amount,
							$sales_header.gross_amount,
							$sales_header.vat_sales,
							$sales_header.ip,
							$sales_header.terminal,
							$sales_header.total_tax,
							$sales_header.admin_id,
							$sales_header.status,
							$sales_tender.amount as tender_amount,
							$sales_tender.tender_id,
							$sales_tender.cardno,
							$sales_tender.account,
							tender.tender,
							tender.tender_type,
							tender.bankable
							
					from 
							$sales_header, $sales_tender, tender 
					where 
							$sales_tender.sales_header_id=$sales_header.sales_header_id and				
							tender.tender_id=$sales_tender.tender_id and
							date='$mdate' and
							terminal='$rt->terminal'";
							
			if ($admin_id != '')
			{
				$q .= " and admin_id = '$admin_id'";
			}
	
			$q .= "	order by $sales_header.sales_header_id, tender.seq ";
			$qr = @pg_query($q) or message(pg_errormessage());
			if (@pg_num_rows($qr) == 0) continue;
			
			$total_discount = $total_amount = 0;
			$total_voided = $total_suspened = 0;
			$count_voided = $count_suspended = 0;
			$total_cash = $total_charge = $total_check = 0;
			$counter_cash = $counter_charge = $counter_check = 0;
			$total_vat_sales = $total_novat = $total_tax = 0;
			$total_invoices = 0;
			
			$lc=0;
			$sales_counter = $total_cash = $total_tender = $accountability = 0;
			$_date = $msales_header_id = '';
			$mnet_amount  = $mtendered = 0;
			while ($r = @pg_fetch_object($qr))
			{
					
				if ($r->status == 'V')
				{
					if ($msales_header_id != $r->sales_header_id)
					{
						$msales_header_id = $r->sales_header_id;
						$total_voided += $r->gross_amount;
						$count_voided++;
					}
					else
					{
						continue;
					}	
				}
				else
				{
				
					//-- detailed breakdown of tender types
					if (in_array($r->tender_type,array('A','B','K')))
					{
						$b = null;
						$b = array();
						$b['tender_type'] =  $r->tender_type;
						$b['tender_id'] =  $r->tender_id;
						$b['tender'] = $r->tender;
						$b['account'] =  $r->account;
						$b['cardno'] = $r->cardno;
						$b['amount'] = $r->tender_amount;
						$aTB[] = $b;
					} 					
						
					if ($msales_header_id != $r->sales_header_id)
					{
						$mtendered = $excess = 0;
						$msales_header_id = $r->sales_header_id;
						$mnet_amount = $r->net_amount;
						$mdiscount_amount = $r->discount_amount;
			
						$total_invoices += 1;
						$total_discount += $r->discount_amount;
						$total_amount += $r->net_amount;
						
						$total_vat_sales += $r->vat_sales;
						$total_nonvat += ($r->net_amount - $r->vat_sales);
						$total_tax += $r->total_tax;
						$sales_counter++;
						$increment_c=1;
					}
					else
					{
							$discount_amount = 0;
							$increment_c=0;
					}
					if ($r->tender_amount <= $mnet_amount)
					  $amount = $r->tender_amount;	
					else
					{
						$amount = $mnet_amount;
						if ($r->tender_type !='C')
						{
								$excess = $r->net_amount   - ($mtendered + $r->tender_amount);
						}
					}	
					
					$mtendered += $amount;
					$mnet_amount -= $amount;
					
					//for accountability per cashier
					$c=0;
					$fnd = 0;
					foreach ($aSC as $temp)
					{
						if ($temp['admin_id'] == $r->admin_id)
						{
						  $dummy = $temp;
						  $dummy['net_amount'] += $amount;
						  if ($r->bankable == 'Y')
						  {
							$dummy['bankable'] += $amount;
						  }  
						  $aSC[$c] = $dummy;
						  $fnd = 1;
						  break;
						}
						$c++;
					}
					if ($fnd == 0)
					{
						$dummy=null;
						$dummy = array();
						$dummy['admin_id'] = $r->admin_id;
						$dummy['username'] = lookUpTableReturnValue('s','admin','admin_id','name',$r->admin_id);
						$dummy['net_amount'] += $amount;
						if ($r->bankable == 'Y')
						{
						  $dummy['bankable'] += $amount;
						}  
						$aSC[]=$dummy;
					  }
				  
					  $c = 0;
					  $fnd=0;
					  foreach ($aTT as $temp)
					  {
						if ($temp['tender_id'] == $r->tender_id)
						{
							$dummy = $temp;
							if ($increment_c==1)
							{
								$dummy['count'] += 1;
							}  
							$dummy['amount'] += $amount;
							$aTT[$c] = $dummy;
							$fnd =1;
							break;
						}
						$c++;
					  }
					  if ($fnd==0)
					  {
						$dummy = null;
						$dummy = array();
						$dummy['tender_id'] = $r->tender_id;
						$dummy['tender'] = $r->tender;
						$dummy['tender_type'] = $r->tender_type;
						$dummy['amount']= $amount;
						$dummy['bankable']=$r->bankable;
						$dummy['count'] = 1;
						$aTT[]=$dummy;
					  }
				}	
			}
	
			$details = $SYSCONF['BUSINESS_NAME']."\n";
			$details .= $SYSCONF['BUSINESS_ADDR']."\n";
			$details .= 'Terminal : '.$term['TERMINAL']."\n";
			$details .= 'Serial   : '.$term['SERIAL']."\n";
			$details .= 'Transaction : '.$date."\n";
			$details .= 'Printed     : '.date('m/d/Y g:ia')."\n\n";
			$details .= "SALES BY CASHIER\n";
			$details .= str_repeat('-',40)."\n";
			$total_net = 0;
			foreach ($aSC as $temp)
			{
			  $details .= adjustSize($temp['username'],25).' '.
						adjustRight(number_format($temp['net_amount'],2),12)."\n";
			  $total_net += $temp['net_amount'];			
			}	
			$details .= str_repeat('-',40)."\n";
			$details .=	space(26).adjustRight(number_format($total_net,2),12)."\n";
			$details .= "\n\n";
			$details .= "PayType             Count     Amount \n";
			$details .= str_repeat('-',40)."\n";
			
			$details .= str_pad('VOIDED',20,'.').
							adjustRight($count_voided,5).
							adjustRight(number_format($total_voided,2),13)."\n";
	
			$q = "select 
							count(distinct(sd.sales_header_id)) as count_return,
							sum(amount) as total_return
						from
							$sales_header as sh,
							$sales_detail as sd
						where
							sh.sales_header_id = sd.sales_header_id and
							sh.date = '$mdate' and
							sh.status !='V' and
							sh.terminal='$rt->terminal' and
							sd.qty < 0 ";
							
			$qqr = @pg_query($q) or message(pg_errormessage().$q);
			$rr = @pg_fetch_object($qqr);
			$details .= str_pad('SALES RETURNS',20,'.').
							adjustRight($rr->count_return,5).
							adjustRight(number_format($rr->total_return,2),13)."\n";
	
			
							
			$total_amount = 0;
			foreach ($aTT as $temp)
			{
				$details .= str_pad($temp['tender'],20,'.').
							adjustRight($temp['count'],5).
							adjustRight(number_format($temp['amount'],2),13)."\n";
							
				$total_tender += $temp['amount'];
				if ($temp['bankable'] == 'Y')
				{
					$total_bankable += $temp['amount'];
				}	
				if ($temp['tender_type'] == 'C')
				{
				 $total_cash += $temp['amount'];
				}
				$total_amount += $temp['amount'];
			}		
			$details .= str_repeat('-',40)."\n";
			$details .=	space(26).adjustRight(number_format($total_amount,2),12)."\n";
	
			$q = "select 
							count(*) as ncollect,
							sum(amount_total) as amount,
							admin_id
						 from 
						 	collection 
						 where 
						 	date='$mdate' and 
						 	terminal='$rt->terminal' and 
						 	status!='C'
						 group by
						 	admin_id";
			$qrc = @pg_query($q) or message(pg_errormessage());
			if (@pg_num_rows($qrc)>0)
			{
				$details .= "\nPayment of Account     Count     Amount \n";
				$details .= str_repeat('-',40)."\n";
				
			}
			
			$total_collection = 0;
			while ($rc = @pg_fetch_object($qrc))
			{
				$total_collection += $rc->amount;
				$details .= str_pad(lookUpTableReturnValue('x','admin','admin_id','name', $rc->admin_id),20).' '.
							adjustRight($rc->ncollect,5).
							adjustRight(number_format($rc->amount,2),13)."\n";
			}
			$details .= str_repeat('-',40)."\n";
			//$details .=	space(26).adjustRight(number_format($total_collection,2),13)."\n";
			
			
			//$sb = @implode(',',$SYSCONF['SALESBREAKDOWN']);
			if ($show=='D')
			{
				$details .= adjustSize("\nNO. OF CUSTOMERS",25).' '.
								adjustRight($total_invoices,13)."\n\n";
				$details .="BREAKDOWN OF CARDS/ACCOUNTS\n";
				$atemp = null;
				$atemp = array();
				foreach ($aTB as $temp)
				{
					$temp1=$temp['tender_type'].$temp['tender_id'];
					$atemp[]=$temp1;
				}
		
				if (count($atemp) > 0)
				{
					asort($atemp);
					reset($atemp);
				}
				
				$tender_id = '';
				while (list ($key, $val) = each ($atemp))
				{
					$temp=$aTB[$key];
					if ($tender_id != $temp['tender_id'])
					{
						$details .= "\n".$temp['tender']."\n";
						$tender_id = $temp['tender_id'];
					}
					$details .= adjustSize($temp['account'],28).' '.
									adjustRight(number_format($temp['amount'],2),10)."\n";
					if ($temp['cardno'] != '')
					{
						$details .= "  ".adjustSize($temp['cardno'],25)."\n";
					}
				}
			}
			$details .= "\n\n\n\n";
			$details1 .= $details;
			
			if ($p1 == 'Print Draft')
			{
				nPrinter($details, $SYSCONF['RECEIPT_PRINTER_TYPE'], $SYSCONF['RECEIPT_PRINTER_DEST']);
			}
		}		
	}
?>
<form name="form1" method="post" action="">
  <table width="80%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr> 
      <td height="2%" colspan="5" background="../graphics/table0_horizontal.PNG"> 
        <strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> &nbsp;<img src="../graphics/bluelist.gif" width="16" height="17"><font color="#FFFFFF"> 
        Daily Reconciliation</font></font></strong></td>
    </tr>
    <tr> 
      <td width="9%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Trans 
        Date<br>
        </font> <font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="date" type="text" id="date" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $date;?>" size="8">
        <img src="../graphics/dwn-arrow-grn.gif" width="12" height="12" onClick="popUpCalendar(this, form1.date, 'mm/dd/yyyy')"> 
        </font> </td>
      <td width="7%"><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">Terminal 
        <br> <input type="text" size="5" name="terminal" value="<?=$terminal;?>"></font></td>
      <td width="6%"><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">Cashier<br>
        <input name="username" type="text" id="username" value="<?=$username;?>" size="10"></font>
        </td>
      <td width="4%"><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">Show<br>
        </font> 
        <?= lookUpAssoc('show',array('----'=>'','Details'=>'D'),$show);?>
      </td>
      <td width="74%"><input type="submit" name="p1" value="Go"> <input type="submit" name="p1" value="Print Draft"> 
        <input name="p122" type="button" id="p122" value="Print"  onClick="printIframe(print_area)" > 
      </td>
    </tr>
    <tr> 
      <td colspan="5" nowrap><hr color="#CC0000"></td>
    </tr>
  </table>
  <table width="80%" height="50%" border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#EFEFEF">
    <tr bgColor='#CCCCCC'> 
      <td height="28" bgcolor="#CCCCCC"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Report 
        Preview</strong></font></td>
    </tr>
    <tr> 
      <td height="98%" valign="top" bgcolor="#FFFFFF"> <textarea name="print_area" cols="90" rows="20" readOnly><?= $details1;?></textarea></td>
    </tr>
  </table>
</form>

<iframe name="printit" id="printit" style="width:0px;height:0px;"></iframe>

