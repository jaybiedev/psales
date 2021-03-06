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
	
	
		$term = terminal($terminal);		
		$aSC = null;
		$aSC = array();
		
		$aTT = null;
		$aTT = array();
		
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
						tender.tender,
						tender.tender_type,
						tender.bankable
						
				from 
						$sales_header, $sales_tender, tender 
				where 
						$sales_tender.sales_header_id=$sales_header.sales_header_id and				
						tender.tender_id=$sales_tender.tender_id and
						date='$mdate'";

		if ($terminal != '')
		{
			$q .= " and terminal ='$terminal'";
		}

		$q .= "	order by $sales_header.sales_header_id, tender.bankable ";
		$qr = @pg_query($q) or message(pg_errormessage());
		
		$total_discount = $total_amount = 0;
		$total_voided = $total_suspened = 0;
		$count_voided = $count_suspended = 0;
		$total_cash = $total_charge = $total_check = 0;
		$counter_cash = $counter_charge = $counter_check = 0;
		$total_vat_sales = $total_novat = $total_tax = 0;
		
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
				if ($msales_header_id != $r->sales_header_id)
				{
					$mtendered = $excess = 0;
					$msales_header_id = $r->sales_header_id;
					$mnet_amount = $r->net_amount;
					$mdiscount_amount = $r->discount_amount;
		
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
				$mnet_amount -= $mtendered;
				
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

		$details .= "\n\n\n\n";
		$details1 .= $details;
		
		if ($p1 == 'Print Draft')
		{
			nPrinter($details, $SYSCONF['RECEIPT_PRINTER_TYPE'], $SYSCONF['RECEIPT_PRINTER_DEST']);
		}	
	}
?>
<form name="form1" method="post" action="">
  <table width="80%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr> 
      <td background="../graphics/table0_horizontal.PNG" height="2%"> <strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        &nbsp;<img src="../graphics/bluelist.gif" width="16" height="17"><font color="#FFFFFF"> 
        Daily Reconciliation</font></font></strong></td>
    </tr>
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Transaction 
        Date </font> <font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="date" type="text" id="date" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $date;?>" size="8">
        <img src="../graphics/dwn-arrow-grn.gif" width="12" height="12" onClick="popUpCalendar(this, form1.date, 'mm/dd/yyyy')"> 
        Terminal</font> <input type="text" size="5" name="terminal" value="<?=$terminal;?>">
        <input type="submit" name="p1" value="Go"> <input type="submit" name="p1" value="Print Draft"> 
 		  <input name="p122" type="button" id="p122" value="Print"  onClick="printIframe(print_area)" >
        <hr color="#CC0000"> </td>
    </tr>
  </table>
  <table width="80%" height="50%" border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#EFEFEF">
    <tr bgColor='#CCCCCC'>
      <td height="28"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Report Preview</strong></font></td>
    </tr>
    <tr>
      <td height="98%" valign="top" bgcolor="#FFFFFF"> <textarea name="print_area" cols="90" rows="20" readOnly><?= $details1;?></textarea></td>
    </tr>

  </table>
</form>

<iframe name="printit" id="printit" style="width:0px;height:0px;"></iframe>

