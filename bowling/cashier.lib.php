<?php
function checkRewardMember($cardno)
{
	global $aCashier;
	$msg='';

	$q = "select * from account where cardno = '$cardno'";
	$r = @fetch_object($q);
	if ($r)
	{
	
			$rem = '';
			$aCashier['account_code'] = $r->account_code;
			$aCashier['cardno'] = $r->cardno;
			$aCashier['account'] = $r->account;
			$aCashier['account_id'] = $r->account_id;
			$aCashier['member'] = '1';
			$aCashier['remarks'] = $r->account;
			$aCashier['credit_limit'] = $r->credit_limit;
			$aCashier['date_expiry'] = $r->date_expiry;
			if ($aCashier['date_expiry'] < date('Y-m-d'))
			{
				$rem = " ** CARD EXPIRED ** ";
			}
					
			$q = "select 
               			sum(points_in) as points_in, 
                			sum(points_out) as points_out 
          			from 
               			reward 
           			where 
                			account_id='".$aCashier['account_id']."' and
                			status!='C'";
   		$r = @fetch_object($q);
   		$aCashier['points_in'] = $r->points_in;
      	$aCashier['points_out'] = $r->points_out;
      	$aCashier['points_balance'] = $r->points_in - $r->points_out;
          
			$aCashier['balance_available'] = round($aCashier['credit_limit'] - $acct['balance'],2);
			$msg = "Bonus Card: [".$aCashier['cardno'].'] '.$aCashier['account'].'  Points:'.
						$aCashier['points_balance'].
						"\n Expiry Date: ".ymd2mdy($aCashier['date_expiry']).$rem.
						"\n(Press ALT+B Again To Clear Bonus Card Member Data)";
	}
	else
	{
			$msg = 0;
	}
	
	return $msg;
}

function nextInvoice()
{
	$invoice = '';
	$q = "select * from invoice where ip='".$_SERVER['REMOTE_ADDR']."'";
	$r = fetch_object($q);
	if ($r)
	{
		$invoice = str_pad($r->invoice + 1,8,'0', str_pad_left);
	}
	else
	{
		$q = "select * from $sales_header where ip='".$_SERVER['REMOTE_ADDR']."' order by sales_header_id desc offset 0 limit 1";
		$qr = @pg_query($q);
		$r = @pg_fetch_object($qr);
		if ($r)
		{
			$aCashier['invoice'] = str_pad($r->sales_header_id + 1,8,'0', str_pad_left);
			$q = "insert into invoice (invoice, ip) values ('$r->sales_header_id','".$_SERVER['REMOTE_ADDR']."')";
			$qr = @pg_query($q);
		}
		else
		{
			$invoice = '000000001';
		}
	}
	return $invoice;
}

 function grid($arr)
 {
 	 global $aCashier;
	 $ctr = $subtotal = $gross_amount = $net_amount = $item_qty = $item_lines = 0;
    $discount_amount = $tender_amount = $total_tax = $vat_sales = 0;
    $grocery_amount = $dry_good_amount = $grocery_netitem = $dry_good_netitem = 0 ;
    $grocery_servicecharge = $dry_good_servicecharge =0;
    
    $details = '';
    foreach ($arr as $temp)
	{
		$e='';
		if (strlen($details) > 10) $details .= "\n";
		foreach ($temp as $key => $value)
		{
			if ($value == '' ) continue;
	 		$e .= $key.'=>'.$value.'||';
	 	}
	 	$details .= $e;
		$ctr++;
		/*
		if ($temp['amount'] == '0')
		{
			galert("Line ".$ctr." has NO Amount...");
		}
		*/	
		if ($temp['fraction'] != 1 && $temp['qty']<1)
		{
			 $qty = round($temp['qty']* $temp['fraction'],0).'/'.$temp['fraction'];
		}
		else
		{
			$qty = $temp['qty'];
		}
		if ($ctr == $aCashier['line_no'])
		{
				$initial = 'hi_initial';
				$normal = 'hi_normal';
				$highlight = 'hi_highlight';
				$qty = "<img src='rightarrow.jpg' id='rightarrow' width='20' height='20'> ".$qty;
				$bgColor='#F3DCD6';
		}
		else
		{
				$initial = 'initial';
				$normal = 'normal';
				$highlight = 'highlight';
				$qty=$qty;
				$bgColor='#FFFFFF';
		}

		$stock_id = $temp['stock_id'];
		$barcode = $temp['barcode'];
		$stock = '';
		if ($temp['netitem'] == 'Y')
		{
			$stock = '(Net) ';
		}		
		if ($temp['fraction'] > '1')
      	{
        	$stock .= $temp['stock'].' '.$temp['fraction']."'s";
      	}
      	else
      	{
      	 	$stock .= $temp['stock'];
      	}
      
			$price = number_format($temp['price'],2);
			$amount = number_format($temp['amount'],2);

			if ($temp['type'] == 'Tender')
			{
				$subtotal -= $temp['amount'];
				$tender_amount += $temp['amount'];
			}
			else
			{
  			if ($temp['type'] == 'ServiceCharge')
  			{
  				$service_charge += $temp['amount'];
  				$grocery_servicecharge += $temp['grocery_servicecharge'];
  				$dry_good_servicecharge += $temp['dry_good_servicecharge'];
  				
  			}
  			else
  			{
  				if ($temp['department'] == 'G')
  			  	{
  			    	$grocery_amount += $temp['amount'];
  			     	if ($temp['netitem'] == "Y" || $temp['discount'] > 0)
  			    	{
  			       		$grocery_netitem += $temp['amount'];
         	 	}
         	}
         	else
         	{
           		$dry_good_amount += $temp['amount'];
  			     	if ($temp['netitem'] == "Y" || $temp['discount'] > 0)
  			     	{
  			      		$dry_good_netitem += $temp['amount'];
           		}
         	}
        	}  
			  	$prc = ($temp['cdisc']==0?$temp['price']:$temp['price1']);
				$gross_amount += $prc*$temp['qty'];
				$subtotal += $temp['amount'];
				$net_amount += $temp['amount'];
				$item_qty += $temp['qty'];
				$discount_amount = $gross_amount + $service_charge - $net_amount;
				if (abs($discount_amount) < 0.05) $discount_amount = 0.00;
				
				if ($temp['taxable'] == 'Y')
				{
				  $total_tax += $temp['tax'];
				  $vat_sales += $temp['amount'];
        }
				$item_lines++;
			}	

			if ($qty != 1)
			{
				$editvalue = $qty.'*'.$barcode;
			}
			else
			{
				$editvalue = $barcode;
			}
//			$rows[] = "<tr onMouseOver=\"this.className='$highlight'\" onMouseOut=\"this.className='$normal'\" bgColor=\"$bgColor\" onClick=\"selectLine('$ctr')\"> 
			$rows[] = "<tr bgColor=\"$bgColor\" onClick=\"selectLine('$ctr')\"> 
							<td align='right' width='10%'>$qty</td>
							<td width='18%'>$barcode</td>
							<td width='45%'>$stock</td>
							<td width='12%' align='right'>$price</td>
							<td width='15%' align='right'>$amount</td>
						</tr>";
						//<a href=\"javascript: selectLine('$ctr')\">
    }                
		$aCashier['item_qty'] = $item_qty;
		$aCashier['item_lines'] = $item_lines;
		$aCashier['net_amount'] = $net_amount;
		$aCashier['gross_amount'] = $gross_amount;
		$aCashier['discount_amount'] = $discount_amount;
		$aCashier['subtotal'] = round($subtotal,2);
		$aCashier['grocery_amount'] = $grocery_amount;
		$aCashier['dry_good_amount'] = $dry_good_amount;

		$aCashier['grocery_netitem'] = $grocery_netitem;
		$aCashier['dry_good_netitem'] = $dry_good_netitem;
		
		$aCashier['grocery_servicecharge'] = $grocery_servicecharge;
		$aCashier['dry_good_servicecharge'] = $dry_good_servicecharge;
		$aCashier['service_charge'] = $service_charge;

		$aCashier['tender_amount'] = $tender_amount;
		$aCashier['total_tax'] = $total_tax;
		$aCashier['vat_sales'] = $vat_sales;
		$aCashier['nonvat_sales'] = $aCashier['net_amount'] - $aCashier['vat_sales'] - $aCashier['service_charge'];
		if ($aCashier['nonvat_sales'] < 0.00) $aCashier['nonvat_sales'] = 0.00;
		
		if (count($rows) == 0)
		{
		  $rows = array('<tr><td></td></tr>');
    	}
		$header = "<table border='0' bgColor='#EFEFEF' cellpacing=\"0\" cellpadding=\"1\" width=\"100%\">";
		$footer  = "</table>";
		
    	$result = $header;
		$result .= implode($rows);
		$result .= $footer;
		
    glayer("grid.layer", $result);
    //glayer("message.layer", $message);
		showSubtotal();
		
	  $aip = explode('.',$_SERVER['REMOTE_ADDR']);
	  $reportfile= '/data/cache/CACHE'.$aip[3].'.txt';
	  $fo = @fopen($reportfile,'w+');
	  @fwrite($fo, $details);
	  @fclose($fo);
		
 }

function showSubtotal() 
{
	global $aCashier;
	$camt = $aCashier['subtotal'];

	if ($camt == 0 && $aCashier['gross_amount'] == 0 && $aCashier['previous_subtotal'] != 0)
	{
		//show last tender change
		$camt = $aCashier['previous_subtotal'];
	}
	if ($camt < 0)
	{
	 $camt = -1*$camt;
	 $contents = "<font size='50'>Change...".number_format($camt,2)."&nbsp;</font>";
	}
	else
	{
	 $contents = "<font size='50'>Amount Due...".number_format($camt,2)."&nbsp;</font>";
	} 
	glayer('subtotal.layer', $contents);
/*

	$breakdown = "<table border='0' width='90%'cellpadding='0' cellpadding='0'>".
                "<tr><td>Gross</td><td align='right'>".number_format($aCashier['gross_amount'],2)."</td></tr></tr>". 
                "<tr><td>Discount</td><td align='right'>".number_format($aCashier['discount_amount'],2)."</td></tr>". 
                "<tr><td>Charges</td><td align='right'>".number_format($aCashier['service_charge'],2)."</td></tr>". 
                "<tr><td>Net</td><td align='right'>".number_format($aCashier['net_amount'],2)."</td></tr>".
                "<tr><td>Tendered</td><td align='right'>".number_format($aCashier['tender_amount'],2)."</td></tr>".
                "<tr><td>Non-Vat</td><td align='right'>".number_format($aCashier['net_amount'] - $aCashier['vat_sales'],2)."</td></tr>".
                "<tr><td>Vat Sales</td><td align='right'>".number_format($aCashier['vat_sales'],2)."</td></tr>".
                "<tr><td>Tax</td><td align='right'>".number_format($aCashier['total_tax'],2)."</td></tr>".
                "</table>"; 
	glayer('breakdown.layer',$breakdown);
*/	
	$itemcount = '<font size=2>'.$aCashier['item_qty'].' Item'.($aCashier['item_qty']>1?'s ':' ').'<br>'.$aCashier['item_lines'].' Line'.($aCashier['item_lines']>1?'s':'').'</font>';
	glayer('itemcount.layer',$itemcount);
	showInvoice();
}

function showInvoice()
{
	global $SYSCONF, $aCashier;
	glayer('invoice.layer','Docket : <b>'.$aCashier['invoice'].'</b><br>Terminal : <b>'.$SYSCONF['TERMINAL'].'</b><hr>');
}


$xajax->registerFunction('selectLine');
function selectLine($line_no) 
{
  global $aCashier, $aItems;
  $temp = $aItems[$line_no-1];
  if ($temp['qty'] != '1')
  {
    $value = $temp['qty'].'*'.$temp['barcode'];
  }
  else
  {
    $value = $temp['barcode'];
  }
  
  $aCashier['line_no'] = $line_no;
  $aCashier['edit'] = 1;
  grid($aItems);
  gset('textbox', $value);
  return done();
}

function checkZeroFields($table)
{
  if ($table == 'sales_header')
  {
    global $aCashier;
    $cf = array('gross_amount','net_amount','discount_percent','discount_amount',
                'discount_id','vat_sales','total_tax','service_charge', 
                'item_lines','account_id');
    for ($c = 0 ; $c<count($cf); $c++)
    {
      if ($aCashier[$cf[$c]] == '')
      {
        $aCashier[$cf[$c]]= 0;
      }
    }
  }
  elseif ($table == 'sales_tender')
  {
    global $temp;
    $cf = array('tender_id','account_id','amount', 'service_charge');
    for ($c = 0 ; $c<count($cf); $c++)
    {
      if ($temp[$cf[$c]] == '')
      {
        $temp[$cf[$c]]= 0;
      }
    }
  } 
  
  elseif ($table == 'sales_detail')
  {
    global $temp;
    $cf = array('stock_id', 'qty',	'price1',	'price', 'cdisc',	'sdisc', 'discount', 'amount', 'tax');
    for ($c = 0 ; $c<count($cf); $c++)
    {
      if ($temp[$cf[$c]] == '')
      {
        $temp[$cf[$c]]= 0;
      }
    }
  } 
  
}

function reprint($invoice)
{
	  global $aCashier, $aItems, $SYSCONF;
  
		$tables = $SYSCONF['tables'];
		$sales_header = $tables['sales_header'];
		$sales_detail = $tables['sales_detail'];
		$sales_tender = $tables['sales_tender'];

		$old_aItems = $aItems;
		$old_aCashier = $aCashier;

		$aItems = null;
		$aItems = array();
		$aCashier = null;
		$aCashier = array();
		$ok=1;
		
		if ($invoice == '')
		{
				$q = "select * from $sales_header where ip='".$_SERVER['REMOTE_ADDR']."' 
					order by sales_header_id desc offset 0 limit 1";
		}
		else
		{		
			$invoice=str_pad($invoice,8,'0',str_pad_left);
			$q = "select * from $sales_header where invoice='$invoice' and ip='".$_SERVER['REMOTE_ADDR']."'";
		}	
		$qr = @pg_query($q);
		if (!$qr)
		{
		  $ok=0;
    		}
		
		$r = @pg_fetch_assoc($qr);
		if (!$r)
		{
				$ok=0;
		}
		else
		{
			$aCashier = $r;
			$aCashier['nonvat_sales'] = $aCashier['net_amount'] - $aCashier['vat_sales'] - $aCashier['service_charge'];
			if ($aCashier['nonvat_sales'] < 0.00) $aCashier['nonvat_sales'] = 0.00;

			$q = "select 
								stock.stock, 
								stock.barcode, 
								$sales_detail.price1,
								$sales_detail.price,
								$sales_detail.qty,
								$sales_detail.cdisc,
								$sales_detail.sdisc,
								$sales_detail.discount,
								$sales_detail.tax,
								$sales_detail.qty,
								$sales_detail.amount
					from 
								$sales_detail,
								stock 
					where 
							stock.stock_id=$sales_detail.stock_id and 
							$sales_detail.sales_header_id='".$aCashier['sales_header_id']."'";
			$qr = @pg_query($q);
			if (!$qr)
			{
			 $ok=0;
      			}
			while ($r = @pg_fetch_assoc($qr))
			{
				$aItems[] = $r;
			}

			
			$q = "select
								account_id,
								account,
								$sales_tender.tender_id,
								tender.tender as barcode,
								cardno,
								carddate,
								'Tender' as type,
								amount,
								tender.tender,
								tender.tender_type
						from 
								$sales_tender,
								tender
						where
								tender.tender_id=$sales_tender.tender_id and 
								$sales_tender.sales_header_id='".$aCashier['sales_header_id']."'";
								
			$qr = @pg_query($q);
			
			if (!$qr)
			{
			 $ok=0;
			 $message = pg_errormessage();
      			}
			
			while ($r = @pg_fetch_assoc($qr))
			{
				$temp = $r;
				$temp['stock'] = $r['cardno'].'-'.ymd2mdy($r['carddate']);
				$aItems[] = $temp;
				$aCashier['tender_amount'] += $r['amount'];
				$aCashier['account_id'] = $r['account_id'];
				$aCashier['account'] = $r['account'];
				$aCashier['cardno'] = $r['cardno'];
				$aCashier['account_code'] = $r['account_code'];
			}
			if ($aCashier['service_charge'] > 0)
			{
				$temp['type'] = 'ServiceCharge';
				$temp['amount'] = $aCashier['service_charge'];
				$aItems[] = $temp;
			}
			$aCashier['REPRINT'] = 1;
			include_once('cashier.receipt.print.php');
		}
		
		//restore variables;
		$aItems = null;
		$aItems = array();
		$aItems = $old_aItems;
		$aCashier = null;
		$aCashier = array();
		$aCashier = $old_aCashier;
		
		return $ok;
		
}
    
function showInstruction()
{
		global $aCashier;
		$account='';
		if ($aCashier['account'] != '') $account = $aCashier['account']."<br>";
		$istr =  "<table width='100%' cellpadding='0' cellspacing='0'>
			<tr><td colspan='2' bgColor='#FFFFCC'>$account</td></tr>
          <tr> 
            <td	width='26%'	align='center'>
              <font size='2'><strong>F1</strong></font> </td>
            <td valign='middle' align='left'><font face='Verdana,	Arial, Helvetica,	sans-serif' size='3'>Help</font></td>
          </tr>
          <tr> 
            <td	width='26%'	align='center'> 
              <font size='2'><strong>F2</strong></font></td>
            <td  valign='middle' align='left'><font face='Verdana,	Arial, Helvetica,	sans-serif' size='3'>Clear</font></td>
          </tr>
          <tr> 
            <td	width='26%'	align='center'> 
              <font size='2'><strong>F3</strong></font></td>
            <td valign='middle' align='left'><font face='Verdana,	Arial, Helvetica,	sans-serif' size='3'>Delete 
              </font></td>
          </tr>
          <tr> 
            <td	width='26%'	align='center'> 
              <font size='2'><strong>F4</strong></font> </td>
            <td valign='middle' align='left'><font face='Verdana,	Arial, Helvetica,	sans-serif' size='3'>Qty</font></td>
          </tr>
          <tr> 
            <td	width='26%'	align='center' height='2'> 
              <font size='2'><strong>F5</strong></font></td>
            <td height='2' valign='middle' align='left'><font face='Verdana,	Arial, Helvetica,	sans-serif' size='3'>Price</font></td>
          </tr>
          <tr> 
            <td align='center' valign='middle'> 
              <font size='2'><strong>F6</strong></font> </td>
            <td height='2' valign='middle'  align='left'><font size='3' face='Verdana,	Arial, Helvetica,	sans-serif'>PLU</font></td>
          </tr>
          <tr> 
            <td align='center' valign='middle' > 
              <font size='2'><strong>F7</strong></font> </td>
            <td height='2' valign='middle' align='left'><font size='3' face='Verdana,	Arial, Helvetica,	sans-serif'>Line 
              Disc </font></td>
          </tr>
          <tr> 
            <td align='center' valign='middle'> 
              <font size='2'><strong>F8</strong></font> </td>
            <td height='2' valign='middle' align='left'><font size='3' face='Verdana,	Arial, Helvetica,	sans-serif'>Global Disc</font></td>
          </tr>
          <tr> 
            <td align='center' valign='middle'>
              <font size='2'><strong>F9</strong></font></td>
            <td height='2' valign='middle' align='left' ><font size='3' face='Verdana,	Arial, Helvetica,	sans-serif'>Tender</font></td>
          </tr>
          <tr> 
            <td height='2' align='center' valign='middle' nowrap><font size='2'><strong>F10</strong></font></td>
            <td height='2' valign='middle' align='left'><font size='3' face='Verdana,	Arial, Helvetica,	sans-serif'>Finish 
              </font></td>
          </tr>
          <tr> 
            <td colspan='2' valign='middle'>&nbsp;</td>
          </tr>
          <tr> 
            <td  valign='middle' align='left' colspan='2'><font size='2'>&nbsp;&nbsp; Alt+<b>C</b>
            <a accesskey='C' href=\"javascript: altkey('C')\">ustmrName</a></font></td>
          </tr>
          <tr> 
            <td  valign='middle' align='left'  colspan='2'nowrap><font size='2'>&nbsp;&nbsp; Alt+<b>B</b>
            <a accesskey='B' href=\"javascript: altkey('B')\">onus Card</a></font></td>
          </tr>
          <tr> 
            <td  valign='middle' align='left'  colspan='2'nowrap><font size='2'>&nbsp;&nbsp; Alt+<b>M</b>
            <a accesskey='M' href=\"javascript: altkey('M')\">ember(Credit)</a></font></td>
          </tr>
          <tr> 
            <td  valign='middle'  align='left' colspan='2'><font size='2'>&nbsp;&nbsp; Alt+<b>R</b>
            <a accesskey='R' href=\"javascript: altkey('R')\">e-Print</a></font></td>
          </tr>
          <tr> 
            <td  valign='middle' align='left' colspan='2'><font size='2'>&nbsp;&nbsp; Alt+<b>V</b>
            <a accesskey='V' href=\"javascript: altkey('V')\">oid</a></font></td>
          </tr>
          <tr> 
            <td  valign='middle' align='left' colspan='2'><font size='2'>&nbsp;&nbsp; Alt+<b>S</b>
            <a accesskey='S' href=\"javascript: altkey('S')\">ales(Category)</a></font></td>
          </tr>
          <tr> 
            <td  valign='middle' align='left' colspan='2'><font size='2'>&nbsp;&nbsp; Alt+<b>Z</b>
            <a accesskey='Z' href=\"javascript: altkey('Z')\">-Read</a></font></td>
          </tr>
	<tr>
	<td colspan='2' align='center'><hr><div id='itemcount.layer'></div></td>
	<tr>
		 </table>";
		 return $istr;

}

function alertBoxDisplay($message)
{
  $str = "
    <table width='100%' height='100%' border='0' cellspacing='0' cellpadding='0'>
      <tr> 
        <td bgcolor='#CCCCCC'><table width='98%' height='98' border='0' align='center' cellpadding='0' cellspacing='0' background='graphics/table0_horizontal.PNG'>
            <tr> 
              <td width='1%'><img src='table0_upper_left.PNG' width='8' height='30'></td>
              <td width='49%' align='left' background='table0_horizontal.PNG'><font color='#FFFFCC' size='2' face='Verdana, Arial, Helvetica, sans-serif'><b>Message</b></font></td>
              <td width='50%' align='right' background='table0_horizontal.PNG'> 
                <img src='table_close.PNG' width='21' height='21' onClick='msgbox.style.visibility='hidden''></td>
              <td width='0%' align='right'><img src='table0_upper_right.PNG' width='8' height='30'></td>
            </tr>
            <tr bgcolor='#A4B9DB'> 
              <td colspan='4'> <table width='99%' height='99%' border='0' align='center' cellpadding='0' cellspacing='1' bgcolor='#EFEFEF'>
                  <tr> 
                    <td colspan='2' valign='top' height='100%'><font size='+3'><?= $message;?></font>
                </table></td>
            </tr>
            <tr> 
              <td colspan='4' height='3'  background='table0_vertical.PNG'></td>
            </tr>
          </table></td>
      </tr>
    </table>";
}
?>
