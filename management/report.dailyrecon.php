<?
	if (!chkRights2('reconciliation','mview',$ADMIN['admin_id']))
	{
		message('You are NOT allowed to view Reports...');
		exit;
	}

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
	
		if ($p1 =='Print Draft' && $SYSCONF['RECEIPT_PRINTER_TYPE'] == 'DRAFT')
		{
			nPrinter('<reset>', $SYSCONF['RECEIPT_PRINTER_TYPE'], $SYSCONF['RECEIPT_PRINTER_DEST']);
		}	

		$ok = $nos  = 1;
		$admin_id = '';
		$aShift = null;
		$aShift = array();
		if ($username != '')
		{
			$users = '';
			$a = explode(",",$username);
			
			for ($c=0;$c<count($a);$c++)
			{
				if ($c>0) $users .= ",";
				$users .= "'".ltrim(chop($a[$c]))."'"; 
			}
			
			$q = "select * from admin where username  in ($users)";
			
			$qr = @pg_query($q) or message1(pg_errormessage().$q);
			while ($r = @pg_fetch_object($qr))
			{
				if (strlen($admin_id)> '0') $admin_id .= ",";
				$admin_id .= "'".$r->admin_id."'";
			}
			
			if (count($a) == 1)
			{
				$q = "select * from userlog 
							where 
									last_invoice!='LOGIN' and
									admin_id in ($admin_id) and substr(date_out,1,10) = '$mdate'";
				$qr = @pg_query($q) or message1(pg_errormessage());
				
				if (@pg_num_rows($qr) > 1)
				{
					$multiple_shift = 1;
				
					while ($r = @pg_fetch_assoc($qr))
					{
						$aShift[] = $r;
					}
					if ($last_invoice == '') 
					{
						$ok = 0;
					}
					elseif ($last_invoice == 'All')
					{
						$from_invoice = $to_invoice = '';
					}
					else
					{
						$to_invoice = $last_invoice;
						$from_invoice = '';
						$q = "select * from userlog 
							where 
									last_invoice!='LOGIN' and
									admin_id in ($admin_id) and 
									substr(date_out,1,10) = '$mdate' and
									last_invoice<='$last_invoice'
							order by
									last_invoice desc ";
						$qr = @pg_query($q) or message1(pg_errormessage());
						while ($r = @pg_fetch_object($qr))
						{
							if ($r->last_invoice != $last_invoice)
							{	
								$from_invoice = $r->last_invoice;
								break;
							}
						}
					} 
				}
			}	
		}

		if ($ok == 1)
		{
			$q = "select distinct ip, value as terminal from terminal where definition='TERMINAL'";
			if ($terminal != '')
			{
				$q .= " and value ='$terminal'";
			}

			$qtr = @pg_query($q) or message(pg_errormessage());
		
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
					$q .= " and admin_id in ($admin_id)";
				}
		
				if ($to_invoice != '')
				{
					$q .= " and invoice>'$from_invoice' and invoice<='$to_invoice'";
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
						if (in_array($r->tender_type,array('A','B','K','G')))
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
		
				if ($admin_id != '')
				{
					$q .= " and admin_id in ($admin_id)";
				}
				if ($to_invoice != '')
				{
					$q .= " and invoice>'$from_invoice' and invoice<='$to_invoice'";
				}
								
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
							 	status!='C'";
							 	
				if ($admin_id != '')
				{
					$q .= " and admin_id in ($admin_id)";
				}
							 	
				$q .= " group by
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

				$details .= "\n\n";
				$details1 .= $details;
				
				if ($p1 == 'Print Draft')
				{
					nPrinter($details, $SYSCONF['RECEIPT_PRINTER_TYPE'], $SYSCONF['RECEIPT_PRINTER_DEST']);
					
					$nos++;
					if ($nos > 10) 
					{
						sleep(2);
						$nos = 0;
					}
				}

			
				//$sb = @implode(',',$SYSCONF['SALESBREAKDOWN']);
				if ($show=='D')
				{
					//--- display some sales details
					// breakdown of category sales			
					
					$details = '';
					$details .= "FUND REPLENISH : ____________________\n\n";
					$details .= "ADJUSTMENT     : ____________________\n\n";		
	
					$q = "select * from area where area_id='1'";
					$qr = @pg_query($q) or message1(pg_errormessage());
					$r = @pg_fetch_object($qr);
					
					$from_category_code = $r->from_category;
					$to_category_code = $r->to_category;

					$details .= 'CATEGORY SALES REPORT'."\n";
					$details .= 'Transaction Date : '.$date."\n";
					$details .= 'Printed          : '.date('m/d/Y g:ia')."\n\n";
					$details .= ' Category             Items    Amount'."\n";
					$details .= '-------------------- ------- -----------'."\n";
					$page = 1;
					$lc = 10;
			
					$from_len = strlen($from_category_code);
					$to_len = strlen($to_category_code);
			
					$q = "select 
						sum(rd.qty) as qty,
						sum(rd.amount) as amount,
						category.category,
						stock.category_id,
						category.category_code
					from
						$sales_detail as rd,
						$sales_header as rh,
						stock,
						category
					where
						rh.sales_header_id=rd.sales_header_id and
						stock.stock_id=rd.stock_id and
						category.category_id=stock.category_id and 
						rh.date = '$mdate' and	
						terminal='$rt->terminal' and
						rh.status != 'V' and 
						rh.status !='C'";
	 
					if ($admin_id != '')
					{
						$q .= " and rh.admin_id in ($admin_id)";
					}
					if ($to_invoice != '')
					{
						$q .= " and invoice>'$from_invoice' and invoice<='$to_invoice'";
					}
					if ($from_category_code != '')
					{
						$q .= " and substr(category.category_code,1, $from_len) >='$from_category_code'";
					}		
					if ($to_category_code != '')
					{
						$q .= " and substr(category.category_code,1,$to_len) <='$to_category_code'";
					}			

					$q .= "	group by 
							stock.category_id , category.category, category.category_code
						order by 
							category.category_code";		


					$qr = @pg_query($q);
					//$details = $q."\n";

					$total_amount = 0;
					$total_qty = 0;
					$ctr = 0;
			
					while ($r = pg_fetch_object($qr) )
					{
						if (intval($r->qty) != $r->qty)
						{
							$cqty = number_format($r->qty,3);
						}
						else
						{
							$cqty = number_format($r->qty,0);
						}
						$ctr++;
						$details .= adjustRight($r->category_code,4).' '.
							adjustSize($r->category,15).' '.
							adjustRight($cqty,7).' '.
							adjustRight(number_format($r->amount,2),11)."\n";
						$total_amount += $r->amount;
						$total_qty += $r->qty;			
						$lc++;
					}
					$details .= '----------------------------------------'."\n";
					$details .= adjustSize($ctr.' Total Items',15).space(2).
								adjustRight(number_format($total_qty,3),10).' '.
								adjustRight(number_format($total_amount,2),12)."\n";
					$details .= '========================================'."\n";
					$details .= "\n\n";
					$details1 .= $details;
					if ($p1 == 'Print Draft')
					{
						nPrinter($details, $SYSCONF['RECEIPT_PRINTER_TYPE'], $SYSCONF['RECEIPT_PRINTER_DEST']);
					}
			
					
					//brakdown of cards			
				
					$details = '';
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
					
					$details .= "\n";
					$details .= "Short :_____________  Over:____________\n\n\n";
					$details .= "Schedule   : _______________________\n\n";
					
					$details .= "Received By: _______________________\n";
					$details .= "\n\n\n\n";
					$details1 .= $details;
					
					if ($p1 == 'Print Draft')
					{
						nPrinter($details, $SYSCONF['RECEIPT_PRINTER_TYPE'], $SYSCONF['RECEIPT_PRINTER_DEST']);
					}
				} //details

			} //if OK (NOT multiple shifts);
		}		

		if ($p1 =='Print Draft' && $SYSCONF['RECEIPT_PRINTER_TYPE'] == 'DRAFT')
		{
			sleep(2);
			nPrinter("\n\n\n<reset>", $SYSCONF['RECEIPT_PRINTER_TYPE'], $SYSCONF['RECEIPT_PRINTER_DEST']);
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
        <input name="username" type="text" id="username" value="<?=$username;?>" size="20"></font>
        </td>
      <td width="4%"><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">Show<br>
        </font> 
        <?= lookUpAssoc('show',array('----'=>'','Details'=>'D'),$show);?>
      </td>
      <td width="74%">
        <font size="1">* Blank for all.Separate cashiers with comma (,)<br></font> 
        <input type="submit" name="p1" id="Go" value="Go">
       <input type="submit" name="p1" value="Print Draft"> 
        <input name="p122" type="button" id="p122" value="Print"  onClick="printIframe(print_area)" >
        
      </td>
    </tr>
    <tr> 
      <td colspan="5" nowrap><hr color="#CC0000"></td>
    </tr>
  </table>
  <?
	if ($multiple_shift == 1 && $show=='D')
	{
		echo "<br><div align='center'>";
		echo "Select Shift: <select name='last_invoice' id='last_invoice'>";
		echo "<option value='All'>All Transactions</option>";
		foreach ($aShift as $temp)
		{
			if ($temp['last_invoice'] == $last_invoice)
			{
				echo "<option value=".$temp['last_invoice']." selected >".$temp['date_out']."</option>";
			}
			else
			{
				echo "<option value=".$temp['last_invoice'].">".$temp['date_out']."</option>";
			}
		}
		echo "</select>";
		echo "<input type='button' value='Select Shift' onClick=".'"'."document.getElementById('Go').click()".'"'."><br>";
		echo  "</div>";
	}
  ?>
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

