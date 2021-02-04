<?php
function plu($searchitem, $qty, $use_medium_price)
{
	global $aCashier, $SYSCONF;
	
	$r = null;
	$r =array();
	$uom=1;	
	$q    = "select 
				stock_id,
				barcode,
				stock,
				price1,
				price1 as price,
				price2,
				price3,
				cost1,
				'1' as fraction,
				fraction2, 
				taxable,
				date1_promo,
				date2_promo,
				promo_cdisc,
				promo_sdisc,
				promo_price1,
				promo_customer as customer, 
				max_discount,
				stock.category_id,
				netitem,
				account_id as supplier_id,
				stock.date1_promo,
				stock.date2_promo,
				category.category_code
			from 
				stock
				left join category on category.category_id = stock.category_id
			where
				barcode = trim('$searchitem') and
				stock.enable='Y'
		offset 0 limit 1";
		$qr = @query($q);
							   
		if (!$qr) 
		{
			  $message  = db_error();
			  glayer('message.layer', $message);
			  return 0;  
		}                   

		if (@pg_num_rows($qr)=='0') 
		{
			$q    = "select 
						barcode.stock_id,
						barcode.barcode,
						stock,
						price1,
						price1 as price,
						price2,
						price3,
						cost1,
						taxable,
						'1' as fraction,
						category_id,
						fraction2, 
						date1_promo,
						date2_promo,
						promo_cdisc,
						promo_sdisc,
						promo_price1,
						promo_customer as customer, 
						max_discount,
					   netitem,
					   account_id as supplier_id,
						stock.date1_promo,
						stock.date2_promo						

					from 
						stock,
						barcode
					where
						barcode.stock_id=stock.stock_id and
						stock.enable='Y' and 
						barcode.barcode = '$searchitem'
				  offset 0 limit 1";					    

			$qr = @query($q);

		  	if (!$qr) 
			{
				  $message  = db_error();
				  glayer('message.layer', $message);
				  return 0;  
			}                   
			if (@pg_num_rows($qr) == '0') 
			{
			   if ($SYSCONF['USE_CASE_PRICE']=='Y')
			   {
					$q    = "select 
							stock_id,
							casecode as barcode,
							price3 as price,
							price3 as price1,
							price2,
							price1 as price_unit,
							cost1,
							stock,
							fraction3 as fraction,
							fraction2,
							taxable,
							date1_promo,
							date2_promo,
							promo_cdisc,
							promo_sdisc,
							promo_price1,
							max_discount,
							category_id,
							netitem,
							promo_customer as customer, 
							account_id as supplier_id,
							stock.date1_promo,
							stock.date2_promo						
						from 
							stock
					where
						casecode = '$searchitem' and
						stock.enable='Y'
					offset 0 limit 1";					    

					$qr = @query($q);
					
					if (@pg_num_rows($qr) == 0) 
					{
						//--item not found
						return 2;
					}
				}
				else
				{
						//--item not found
						return 2;
				}
			}  
			$uom = 3;
		}                     

		$rows = null;
		$rows = array();
		$r = @qr_assoc($qr);
		
		$r['type'] = 'stock';
		$r['use_medium_size'] = $use_medium_size;
		$r['qty']=$qty;

		
		if ((($r['qty'] >= ($r['fraction2']/2)) || $use_medium_price==1) && $SYSCONF['USE_MEDIUM_PRICE']=='Y')
		{
			//-- medium price is pack price --
			
			if ($use_medium_price == 0)
			{
				//-- converting pieces to packs
				$r['qty'] = round($r['qty']/$r['fraction2'],3);
			}  
			$r['fraction'] = $r['fraction2'];
			if ($r['price2'] > 0)
			{
				$r['price'] = $r['price2'];
				$r['price1'] = $r['price2'];
			}
			else
			{
				//-- if pack price is not available use price1*fraction2
				
				$r['price'] = $r['price1']*$r['fraction2'];
				$r['price1'] = $r['price'];
			}
			$uom = 2;
		}
				
		
		$q = "select department, category_code from category where category_id='".$r['category_id']."'";
		$rc = fetch_assoc($q);
		$r['department'] = $rc['department'];
		$r['category_code'] = trim($rc['category_code']);
		
		if ($r['fraction'] == 0) $r['fraction'] = 1;
		if ($r['fraction2'] == 0) $r['fraction2'] = 1;
		if ($r['fraction3'] == 0) $r['fraction3'] = 1;

		if ($uom == 2)
		{
			$r['stock'] = '(PK)'.$r['stock'];
		}
		elseif ($uom == 3)
		{
			$r['stock'] = '(CS)'.$r['stock'];
			if ($r['price'] == '0')
			{
				$r['price'] = $r['price1'] = $r['price_unit']* $r['fraction3'];
			}
		}

		
		$today = date('Y-m-d');
		if ($SYSCONF['DATE_TRANSACTION'] > $today)
		{
			$today = $SYSCONF['DATE_TRANSACTION'];
		}
	

		if (($r['customer'] == 'A' || $r['customer']=='M' && $aCashier['account_id'] > '0') && 
						$r['date2_promo'] >= $today && $r['date1_promo'] <= $today)
		{
				//--if promo is found from stocks table
				$r['cdisc'] = $r['promo_cdisc'];
				$r['sdisc'] = $r['promo_sdisc'];
				$r['customer'] = $rr->customer;
				$r['date1_promo'] = $rr->date1_promo;
				$r['date2_promo'] = $rr->date2_promo;
	
				if ($r['cdisc'] > '0' or $r['sdisc'] >0)
				{
					$r['price'] = round($r['price']*(1-(($r['cdisc'] + $r['sdisc'])/100)),2);
				}
				elseif ($r['promo_price1'] > '0')
				{	
					$r['price'] = $r['promo_price1']; //	round($r['price1'] - $disc,2);
				}	

		}
		else
		{
				//check for promo price
				$q = "select 
								promo_detail.promo_price, 
								promo_header.cdisc,
								promo_header.sdisc,
								promo_header.customer,
								promo_header.date_from as date1_promo,
								promo_header.date_to	as date2_promo						
								
							from	
								promo_header, promo_detail
							where
								promo_header.promo_header_id=promo_detail.promo_header_id and 	
								promo_detail.stock_id='".$r['stock_id']."'	and	
								promo_header.date_from<='$today'	and	
								promo_header.date_to>='$today' and
								promo_header.enable='Y'";
								
				if ($aCashier['account_id'] == '0' or $aCashier['account_id'] == '')
				{
					$q .= " and promo_header.customer  = 'A'";
				}

				$q .= "	order by
								promo_detail.promo_header_id desc
							offset 0 limit 1";

				$qpr	=	@query($q);

				if (!$qpr)
				{
					$message = 'Error querying Promo Period...';
					glayer('message.layer', $message.$q);
					return done();
				}
				else
				{
					if (@pg_num_rows($qpr) > 0)
					{
						$rr = @qr_object($qpr);
					}
					else
					{
						//-- check promo by supplier with specic category first
						$clen = strlen($r['category_code']);
						$q = "select 
											promo_header.cdisc,
											promo_header.sdisc,
											promo_header.category_id_from,
											promo_header.category_id_to,									
											promo_header.category_code_from,
											promo_header.category_code_to,
											promo_header.customer,								
											promo_header.date_from as date1_promo,
											promo_header.date_to	as date2_promo							
										from 
											promo_header 
										where
											account_id = '".$r['supplier_id']."' and
											promo_header.date_from<='$today'	and	
											promo_header.date_to>='$today' and
											substr(category_code_from,1,$clen) <= '".$r['category_code']."' and
											substr(category_code_to,1,$clen) >= '".$r['category_code']."' and
											promo_header.all_items='Y' and 
											promo_header.enable='Y'";

						if ($aCashier['account_id'] == '0' or $aCashier['account_id'] == '')
						{
							$q .= " and promo_header.customer  = 'A'";
						}

						if ($r['netitem'] == 'Y')
						{
							$q .= " and promo_header.include_net !='N'";
						}
						else
						{
							$q .= " and promo_header.include_net !='Y'";
						}
						$qpr	=	@query($q);

						if (!$qpr)
						{
							$message = 'Error querying Promo Period...'.pg_errormessage();
							glayer('message.layer', $message.$q);
							return done();
						}
						
						if (@pg_num_rows($qpr) > 0)
						{
							$rr = @qr_object($qpr);
						}
						else
						{
							//-- check if promo for all categories
							$q = "select 
											promo_header.cdisc,
											promo_header.sdisc,
											promo_header.category_id_from,
											promo_header.category_id_to,									
											promo_header.category_code_from,
											promo_header.category_code_to,									
											promo_header.customer,
											promo_header.date_from as date1_promo,
											promo_header.date_to	as date2_promo						
										from 
											promo_header 
										where
											account_id = '".$r['supplier_id']."' and
											promo_header.date_from<='$today'	and	
											promo_header.date_to>='$today' and
											category_code_from = '' and
											promo_header.all_items='Y' and 
											promo_header.enable='Y'";
							if ($r['netitem'] == 'Y')
							{
								$q .= " and promo_header.include_net !='N'";
							}
							else
							{
								$q .= " and promo_header.include_net !='Y'";
							}
							$qpr	=	@query($q);

							if (!$qpr)
							{
								$message = 'Error querying Promo Period...';
								glayer('message.layer', $message.$q);
								return done();
							}

							$rr = @qr_object($qpr);
						}						
					}
				} 
				
				if ($rr)
				{
					//-- promo must be for all customers or for reward members

					if ($rr->customer == 'A' || ($rr->customer=='M' && $aCashier['account_id'] > '0'))
					{								
						$message = 'Found Promotional Sale...';
						//$r['price']	=	$rr->promo_price;
						$r['price'] = round($r['price']*(1-(($rr->cdisc + $rr->sdisc)/100)),2);
						$r['cdisc']	=	$rr->cdisc;
						$r['sdisc']	=	$rr->sdisc;
						$r['customer'] = $rr->customer;
						$r['date1_promo'] = $rr->date1_promo;
						$r['date2_promo'] = $rr->date2_promo;
						
						glayer('message.layer', $message);
					}
				} //--if ($rr) promo is found... 
		} //-- if search from promo tables 
		
		
		$r['amount'] = round2($r['price']*$r['qty'],2);
		$r['discount'] = round($r['price1']*$r['qty'],2)-$r['amount'];
		if ($r['discount'] <= 0)
		{
		 	$r['discount'] = 0;
		}
		if ($r['taxable'] == 'Y')
		{
			$r['taxrate'] = $aCashier['TAXRATE'];
			$r['taxbase'] = round($r['amount']/(1 + ($r['taxrate']/100)),2);
			$r['tax'] = $r['amount'] - $r['taxbase'];
		}

		return $r;
}

function updateLineItems()
{
	global $aItems, $aCashier, $SYSCONF;
	//-- get all lines again from stocks table again 
	$today = date('Y-m-d');
	if ($SYSCONF['DATE_TRANSACTION'] > $today)
	{
		$today = $SYSCONF['DATE_TRANSACTION'];
	}

	$c=0;
	foreach ($aItems as $temp)
	{
		if ($temp['type'] != 'stock') continue;

		$r = null;
		$r = array();
		$r = $temp;
		if (($temp['customer'] == 'A' || ($temp['customer']=='M' && $aCashier['account_id'] > '0')) &&
				$temp['date2_promo']>=$today && $temp['date1_promo']<=$today)
		{		
				//-- update discounts;						
				$r['price'] = round($r['price1']*(1-(($temp['cdisc'] + $temp['sdisc'])/100)),2);
				$r['amount'] = round2($r['price']*$r['qty'],2);
				$r['discount'] = round($r['price1']*$r['qty'],2)-$r['amount'];
				if ($r['discount'] <= 0)
				{
		 			$r['discount'] = 0;
				}
				if ($r['taxable'] == 'Y')
				{
					$r['taxrate'] = $aCashier['TAXRATE'];
					$r['taxbase'] = round($r['amount']/(1 + ($r['taxrate']/100)),2);
					$r['tax'] = $r['amount'] - $r['taxbase'];
				}

				$aItems[$c] = $r;

		}

		$c++;
	}
	return;
}

function renewLineItems()
{
	global $aItems;
	//-- get all lines again from stocks table again 
	
	$c=0;
	foreach ($aItems as $temp)
	{
		if ($temp['type'] != 'stock' || in_array($temp['note'],array('norenew','edited','free'))) 
		{
			$c++;
			continue;
		}
		
		$dummy = null;
		$dummy = array();
		$mplu = plu($temp['barcode'],$temp['qty'], $temp['use_medium_size']);
		$dummy = $mplu;
		$aItems[$c] = $dummy;
		$c++;
	}
	return;
}

function checkRewardMember($cardno)
{
	global $aCashier;
	$msg='';

	$q = "select * from account where cardno = '$cardno'";
	$qr = @pg_query($q);
	if (!$qr)
	{
		galert("Unable To Query Accounts File...".pg_errormessage().$q);
		return done();
	}	
	$r = @pg_fetch_object($qr);
	
	if ($r)
	{
	
//			$rem = '';
			if ($r->date_expiry < date('Y-m-d'))
			{
				$msg = " ** CARD EXPIRED (".ymd2mdy($r->date_expiry).") ** \n Sorry NO Transactions Allowed";
				$aCashier['account_code'] = '';
				$aCashier['cardno'] =  '';
				$aCashier['account'] =  '';
				$aCashier['account_id'] =  '0';
				$aCashier['member'] =  '';
				$aCashier['member_name'] =  '';
				$aCashier['remarks'] =  '';
				$aCashier['credit_limit'] =  '0';
				$aCashier['date_expiry'] =  '';

			}
			else
			{					
				$aCashier['account_code'] = $r->account_code;
				$aCashier['cardno'] = $r->cardno;
				$aCashier['account'] = $r->account;
				$aCashier['account_id'] = $r->account_id;
				$aCashier['member'] = '1';
				$aCashier['member_name'] = $r->account;
				$aCashier['remarks'] = $r->account;
				$aCashier['credit_limit'] = $r->credit_limit;
				$aCashier['date_expiry'] = $r->date_expiry;

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
				$msg = "Bonus Card: [".$aCashier['cardno'].'] '.$aCashier['account'].
						"\n Total Points: ".$aCashier['points_balance'].
						"\n Expiry Date: ".ymd2mdy($aCashier['date_expiry']).$rem.
						"\n(Press ALT+B Again To Clear Bonus Card Member Data)";
			}	
	}
	else
	{
			$msg = 0;
	}
	
	return $msg;
}


function computeServiceCharge($chargeable_amount, $authorized_amount, $aid)
{
	global $aCashier;
	$aSC = null;
	$aSC = array();
 
 	$balance_subtotal=$chargeable_amount;

							$q = "select 
										grocery_service,
										drygood_service
									 from 
									 	account_class,
									 	account
									 where 
									 	account.account_class_id=account_class.account_class_id and
									 	account.account_id='$aid'";
									 	
						 	$qr = @query($q);
							$rr = @qr_object($qr);
							$grocery_service = $rr->grocery_service;
							$drygood_service = $rr->drygood_service;

							$aSC['grocery_service'] = $rr->grocery_service;
							$aSC['drygood_service'] = $rr->drygood_service;
							
							
							$srvc_amount = $grocery_netitem = $dry_good_netitem = 0;
							$grocery_charge = $drygood_charge;

 							//--- note service charge for NET ITEMS AND PROMOTIONAL ITEMS Only
							$grocery_factor = (100 - $grocery_service)/100;
							$drygood_factor = (100 - $drygood_service)/100;

 	
              			$grocery_amount = $aCashier['grocery_amount'];
              			$drygood_amount = $aCashier['dry_good_netitem'];
              			$balance_available = $aCashier['balance_available'];

				$other_charge = $balance_subtotal - ($grocery_amount +$drygood_amount);
				$balance_subtotal -= $other_charge;

				//-- iterate if account balance available is sufficient              			
             			while ($grocery_amount > 0)
              			{
				
								if ($balance_subtotal >= $grocery_amount)
								{
									$grocery_charge = round($grocery_amount/$grocery_factor,2);
									$grocery_servicecharge = $grocery_charge-$grocery_amount;
									$balance_subtotal -= $grocery_amount;
								}
								elseif ($grocery_factor > '0')
								{
									$grocery_charge = round($balance_subtotal/$grocery_factor,2);
									$grocery_servicecharge = $grocery_charge-$balance_subtotal;
									$balance_subtotal = 0; 
								}
								elseif ($balance_subtotal >= $grocery_amount)
								{
									$grocery_charge = $grocery_amount;
									$balance_subtotal -= $grocery_charge; 
								}
								else
								{
									$grocery_charge = $balance_subtotal;
									$balance_subtotal = 0; 
								}
								$grocery_charge = round($grocery_charge,2);

								if ($balance_available < $grocery_charge)
								{
									$balance_subtotal = $balance_available*$grocery_factor;
									$grocery_amount = $balance_subtotal;
								}
								elseif ($authorized_amount < $grocery_charge)
								{
									$balance_subtotal = $authorized_amount*$grocery_factor;
									$grocery_amount = $authorized_amount;
								}
								else
								{
									break;
								}

							}
							$balance_available = round($balance_available - $grocery_charge,2);
							$authorized_amount = round($authorized_amount - $grocery_charge,2);

              			while ($drygood_amount > 0)
              			{
								if ($balance_subtotal >= $drygood_amount  && $drygood_factor > '0')
								{
									$drygood_charge = round($drygood_amount/$drygood_factor,2);
									$drygood_servicecharge = $drygood_charge-$drygood_amount;
									$balance_subtotal -= $drygood_amount; 
								}
								elseif($drygood_factor > '0')
								{
									$drygood_charge = round($balance_subtotal/$drygood_factor,2);
									$drygood_servicecharge = $drygood_charge-$balance_subtotal;
									$balance_subtotal = 0; 
								}
								elseif ($balance_subtotal >= $drygood_amount)
								{
									$drygood_charge = $drygood_amount;
									$balance_subtotal -= $drygood_charge; 
								}
								else
								{
									$drygood_charge = $balance_subtotal;
									$balance_subtotal = 0; 
								}
								
								$drygood_charge = round($drygood_charge,2);

								if ($balance_available < $drygood_charge)
								{
									$balance_subtotal = $balance_available*$drygood_factor;
									$drygood_amount = $balance_subtotal;
								}
								elseif ($authorized_amount < $drygood_charge)
								{
									$balance_subtotal = $authorized_amount*$drygood_factor;
									$drygood_amount = $authorized_amount;
								}
								else
								{
									break;
								}

							}

							$balance_available -= $drygood_charge;
							$authorized_amount -= $drygood_charge;

							//-- only for the purpose of displaying the actual due if beyond credit limit
							//-- succeeding procedure has nothing to do with posted info
							$amount_due_real = 0;
							$subtotal_real = $chargeable_amount;
							
							if ($subtotal_real >= $aCashier['grocery_amount'])
							{
								$amount_due_real = $aCashier['grocery_amount']/$grocery_factor;
								$subtotal_real -= $aCashier['grocery_amount'];
							} 
							else
							{
								$amount_due_real = $subtotal_real/$grocery_factor;
								$subtotal_real -= $subtotal_real;
							}
							
							if ($subtotal_real >= $aCashier['drygood_amount'])
							{
								$amount_due_real += $aCashier['drygood_amount']/$drygood_factor;
								$subtotal_real -= $aCashier['drygood_amount'];
							} 
							else
							{
								$amount_due_real += $subtotal_real/$drygood_factor;
								$subtotal_real -= $subtotal_real;
							}


							if ($amount_due_real > $aCashier['balance_available'])
							{
								$message = "   *** Amount is BEYOND CREDIT LIMIT ***.\n  Available Balance :".
												number_format($balance_available,2).
												" Amount Due: ".number_format($amount_due_real,2)."\n".
												" Adjusting Charge Amount to: ".
												number_format(($grocery_charge+$drygood_charge),2);
							}

				         
			$srvc_amount =  $grocery_servicecharge+$drygood_servicecharge;
			$aSC['grocery_charge'] =  round2($grocery_charge);
			$aSC['grocery_servicecharge'] =  round2($grocery_servicecharge);

			$aSC['drygood_charge'] =  round2($drygood_charge + $other_charge);
			$aSC['drygood_servicecharge'] =  round2($drygood_servicecharge);

			$aSC['srvc_amount'] = round2($srvc_amount);
			$aSC['message'] = $message;
			$aSC['account_id'] = $aid;

		return $aSC;
}

function checkPayment()
{
	global $aCashier, $aItems;
			if ($aCashier['tender_amount'] > 0)
			{
				//-- Unpost all payments made...
				$newarray = null;
				$newarray = array();				
				foreach($aItems as $temp)
				{
					if (!in_array($temp['type'],array('Tender','SRVC','ServiceCharge')))
					{
						$newarray[] = $temp;
					}
				}
				galert("Additional/Modification of Line Item is requested,\nResetting all Payments Made...");
				$aItems = $newarray;
				$aCashier['line_no']=1;
				gset('line_no',1);
			}
	return;

}
function nextInvoice()
{
	global $SYSCONF;
	$tables = $SYSCONF['tables'];
	$sales_header = $tables['sales_header'];

	$invoice = '';
	$q = "select * from invoice where ip='".$_SERVER['REMOTE_ADDR']."'";
	$r = fetch_object($q);
	if ($r)
	{
		$invoice = str_pad($r->invoice + 1,8,'0', STR_PAD_LEFT);
		
		$q = "select * 
					from 
						$sales_header 
					where 
						invoice='$invoice' and
						ip='".$_SERVER['REMOTE_ADDR']."'";
		$qr = @pg_query($q);
		if (!$qr)
		{
			galert(pg_errormessage().$q);
		}
		while (@pg_num_rows($qr) > 0)
		{
			$invoice = str_pad($invoice + 1,8,'0', STR_PAD_LEFT);

			$q = "select * 
					from 
						$sales_header 
					where 
						invoice='$invoice' and
						ip='".$_SERVER['REMOTE_ADDR']."'";
			$qr = @pg_query($q);
		}
		
	}
	else
	{
		$q = "select * from $sales_header where ip='".$_SERVER['REMOTE_ADDR']."' order by sales_header_id desc offset 0 limit 1";
		$qr = @pg_query($q);
		$r = @pg_fetch_object($qr);
		if ($r)
		{
			$aCashier['invoice'] = str_pad($r->sales_header_id + 1,8,'0', STR_PAD_LEFT);
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
		if ($temp['fraction'] != 1 && $temp['qty']<1)
		{
			 $qty = round($temp['qty']* $temp['fraction'],0).'/'.$temp['fraction'];
		}
		else
		{
			$qty = $temp['qty'];
		}

		$stock_id = $temp['stock_id'];
		$barcode = $temp['barcode'];
		$stock = '';
		if ($temp['netitem'] == 'Y')
		{
			$stock = '(Net)';
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
			$rstock = substr($stock,0,25);
			$rbarcode = substr($barcode,0,16);
			$rows[] = "<tr class=\"gridRow\" id=\"a".$ctr."\" onClick=\"selectLine('$ctr')\"> 
							<td align='right' width='10%'>$qty</td>
							<td width='18%'>$rbarcode</td>
							<td width='45%'>$rstock</td>
							<td width='12%' align='right'>$price</td>
							<td width='15%' align='right'>$amount</td>
						</tr>";
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
		showSubtotal();
		
		if ($aCashier['line_no']*1 == 0 && count($aItems)>0) 
		{
			$aCashier['line_no'] = 1;
		}
		$line_id = 'a'.$aCashier['line_no'];
		$hi = "if (document.getElementById('$line_id')){document.getElementById('$line_id').style.background='#000CCC';
				document.getElementById('$line_id').style.color='#FFFFFF';}";

	  gset('line_no',$aCashier['line_no']);
	  gscript($hi);		
		
	  $aip = explode('.',$_SERVER['REMOTE_ADDR']);
	  $reportfile= '/data/cache/CACHE'.$aip[3].'.txt';
	  $fo = @fopen($reportfile,'w+');
	  @fwrite($fo, $details);
	  @fclose($fo);
		
 }

function showSubtotal() 
{
	#DISPLAYS SUBTOTAL HERE
	global $aCashier;
	$camt = $aCashier['subtotal'];
	
	if($aCashier['bag'] && $camt > 1){
		$eco_bag_amount = lookUpTableReturnValue('x','sysconfig','sysconfig','value','ECO_BAG');
		$eco_bag_amount = (empty($eco_bag_amount)) ? 1 : $eco_bag_amount;
		$camt -= $eco_bag_amount;	
	}

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

	#DISPLAY ITEMS HERE
	$itemcount = '<font size=2>'.$aCashier['item_qty'].' Item'.($aCashier['item_qty']>1?'s ':' ').'<br>'.$aCashier['item_lines'].' Line'.($aCashier['item_lines']>1?'s':'').'</font>';
	if($aCashier['bag'] == 1){
		$itemcount .= '<br><b>Eco Bag Discount Applied</b>';
	}
	glayer('itemcount.layer',$itemcount);
	showInvoice();
}

function showInvoice()
{
	global $SYSCONF, $aCashier;
	glayer('invoice.layer','<font size=2>No: <b>'.$SYSCONF['TERMINAL'].'-'.$aCashier['invoice'].'</b><hr size=1> </font>');
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
  gset('line_no',$line_no);
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
			$invoice=str_pad($invoice,8,'0',STR_PAD_LEFT);
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
			$reprintagain = receiptprint();
			
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
            <td  valign='middle' align='left' colspan='2'><font size='2'>&nbsp;&nbsp; Alt+<b>E</b>
            <a accesskey='E' href=\"javascript: altkey('E')\">-Raffle</a></font></td>
          </tr>
          <tr> 
            <td  valign='middle' align='left' colspan='2'><font size='2'>&nbsp;&nbsp; Alt+<b>D</b>
            <a accesskey='D' href=\"javascript: altkey('D')\">escription</a></font></td>
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
            <td  valign='middle' align='left' colspan='2'><font size='2'>&nbsp;&nbsp;Alt+<b>A</b> EcoBag </b>
            <a accesskey='H' href=\"javascript: altkey('H')\">Disc</a></font></td>
          </tr>
		  <tr> 
            <td  valign='middle' align='left' colspan='2'><font size='2'>&nbsp;&nbsp;Alt+<b>Q</b> RemoveEcoBag </b>
            <a accesskey='I' href=\"javascript: altkey('I')\">Disc</a></font></td>
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
