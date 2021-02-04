<?php
	session_start();
	require_once(dirname(__FILE__).'/../lib/lib.salvio.php');
    require_once("../xajax.inc.php");
    $xajax = new xajax();
    $g     = "";
	
	function checkGiftCheckUnused($giftcheck){
		#if row is greater than 1 it means that the giftcheck is found and not used
		$q = "select * from giftcheck where giftcheck = '$giftcheck' and used = '0'";
		
		$_gc = $giftcheck;
		$count = count(explode('-',$_gc));
		
		if($count == 1){
			$result = @pg_query("select * from giftcheck where giftcheck = '$giftcheck' and used = '0'");	
			$rows = @pg_num_rows($result);
			
		}else if($count > 1){										
			$s = explode('-',$_gc);
			$rows = 0;
			for($x = $s[0] ; $x <= $s[1] ; $x++){;	
				$result = @pg_query("select * from giftcheck where giftcheck = '$x' and used = '0'");	
				$rows += @pg_num_rows($result);
			}
			
		}	
		
		return ($rows == $count) ? true : false;
	}

    $g->objResponse = new xajaxResponse();

		if ($p == 'logout' || $SYSCONF['IP']== ''  || $ADMIN['sessionid']=='' || ($SYSCONF['TERMINAL'] == '' && $ADMIN['admin_id'] != '1'))
		{
			echo "<script>alert('Terminal NOT registered for Point of Sale...')</script>";
			
			$SYSCONF='';
			$ADMIN='';
			session_unset();
			echo "<script>window.location='../'</script>";
			exit;
		}
		include_once('cashier.func.php');
		include_once('../lib/dbconfig.php');
		
		$DBCONN = pg_Connect($DBCONNECT) or die("Can't connect to server...");
		
		$aCashier['admin_id'] = $ADMIN['admin_id'];
		$aCashier['TAXRATE'] = $SYSCONF['TAXRATE'];
		
		include_once('cashier.lib.php');
		
		if (!session_is_registered('aItems'))
		{
			session_register('aItems');
			$aItems=null;
			$aItems=array();
		}
		if (!session_is_registered('aCashier'))
		{
			session_register('aCashier');
			$aCashiers=null;
			$aCashier=array();
		}

		function alertNoItemFound(){
			global $g;
			$g->objResponse->addScript("openDialog();");	
		}

		function galert($m)
		{
			global $g;
			$g->objResponse->addAlert($m);
		}
		function gscript($m)
		{
			global $g;
			$g->objResponse->addScript($m);
		}
		function gconfirm($m)
		{
			global $g;
			return $g->objResponse->addConfirm($m);
		}
        function glayer($layer, $content) {
                global $g;
                $g->objResponse->addAssign($layer, 'style.display', 'block');
                $g->objResponse->addAssign($layer, 'innerHTML', $content);
        }
        
        function hide_layer($layer) {
                global $g;
                $g->objResponse->addAssign($layer, 'style.display', 'none');
        }
        function move_layer($layer, $top, $height, $width, $bgcolor, $overflow) {
                global $g;
                $g->objResponse->addAssign($layer, 'style.display', 'block');
                $g->objResponse->addAssign($layer, 'style.top', $top);
                $g->objResponse->addAssign($layer, 'style.height', $height);
                $g->objResponse->addAssign($layer, 'style.width', $width);
                $g->objResponse->addAssign($layer, 'style.background', $bgcolor);
                $g->objResponse->addAssign($layer, 'style.overflow', $overflow);
        }
        
        function show_layer($layer) {
                global $g;
                $g->objResponse->addAssign($layer, 'style.display', 'block');
        }
        function done() {
                global $g;
                hide_layer('wait.layer');
                return $g->objResponse->getXML();
        }
        
        
        function gset($element, $value) {
                global $g;
                 $g->objResponse->addAssign($element, 'value', $value);
        }

        function gfocus($element) {
                global $g;
                 $g->objResponse->addAssign($element, 'value', $value);
        }
        
		// Variable Initialization
		if ($aCashier['invoice'] == '')
		{
			$aCashier['invoice'] = nextInvoice();
		}

		/**
		 * Function for processing promo items
		 */

		function processPromoItems($aItems, $sales_header_id){
			
			$promos = lib::getPromoItems($aItems);

			foreach ( $promos as &$promo ) {
				$promo['total_quantity'] = 0;

				foreach ( $aItems as $aItem )  {
					if ( lib::hasPromo( $promo, $aItem ) ) {
						$promo['total_quantity'] += $aItem['amount'] / $promo['amount'];
					}
				}
			}

			lib::savePromoSales($promos, $sales_header_id);

			return $promos;

			/* ob_start();
			print_r($promos);
			$x = ob_get_clean();
			galert($x); */
			
		}

		// form/module defined  
		// on Process Functions

		function saveSales($textbox)
		{
			global $aCashier, $aItems, $ADMIN, $SYSCONF;
			$aCashier['admin_id'] = $ADMIN['admin_id'];
			
			$tables = $SYSCONF['tables'];
			$sales_header = $tables['sales_header'];
			$sales_detail = $tables['sales_detail'];
			$sales_tender = $tables['sales_tender'];
			
	      	grid($aItems);
			checkZeroFields('sales_header');

			
			if ($aCashier['tender_amount'] == 0 || ($aCashier['tender_amount']>0 && $textbox!=''))
			{
				$q = "select tender,tender	as barcode,	tender as	unit,	'Tender' as	type,	tender_id,tender_type, bankable	from tender	where	tender_type='C'";
				$qr = @query($q);
				if (!$qr)
				{
					$message = 'Error Assigning Cash Payment...'.db_error();
				}	
				else
				{
					$r = @qr_assoc($qr);
					
					if($aCashier['bag'] && $aCashier['subtotal'] > 0){
						$eco_bag_amount = lookUpTableReturnValue('x','sysconfig','sysconfig','value','ECO_BAG');
						$eco_bag_amount = (empty($eco_bag_amount)) ? 1 : $eco_bag_amount;
						
						$aCashier['subtotal'] -= $eco_bag_amount;
						$aCashier['net_amount'] -= $eco_bag_amount;
					}

					$tender_amount = 1*$textbox;
					if ($tender_amount <= 0)
					{
						$r['amount'] = $aCashier['net_amount'];
					}
					elseif ($tender_amount < $aCashier['subtotal'])
					{
					 	return 'Lacking Payment...';
				    }
					else
					{
						$r['amount'] = $tender_amount;
					}

					$aItems[]	=	$r;
					$aCashier['tender_amount'] += $r['amount'];
					$aCashier['subtotal'] = $aCashier['net_amount'] - $aCashier['tender_amount'];
				}	
			}
			
			$commit	= 1;
			$qr = begin();
			if ($aCashier['sales_header_id'] ==	'')
			{
				$time	=	date('G:i:s');
				$t = explode(':',$time);
				if (strlen($t[0]) == 1) $time = '0'.$time;
				$date	=	date('Y-m-d');
				
				if ($SYSCONF['DATE_TRANSACTION'] > $date)
				{
					$date = $SYSCONF['DATE_TRANSACTION'];
				}
				$aCashier['time'] = $time;
				$aCashier['date'] = $date;
				
				
				if(empty($aCashier['bag'])){
					$aCashier['bag'] = 0;
				}
				
				if($aCashier['bag']){
					$aCashier['discount_amount'] += $eco_bag_amount;
				}
				
				
				$q = "insert into	$sales_header (invoice, date,time,status,gross_amount, 	
									discount_percent,discount_amount, discount_id,  discount_card, 
                  					net_amount,vat_sales, total_tax, 
									service_charge, ip, terminal, admin_id, remarks,
									item_lines, units, account_id,bag_disc)
								values
									('".$aCashier['invoice']."','$date','$time','S','".$aCashier['gross_amount']."',
									'".$aCashier['discount_percent']."','".$aCashier['discount_amount']."',
                  			'".$aCashier['discount_id']."','".addslashes($aCashier['discount_card'])."',	
									'".$aCashier['net_amount']."', '".$aCashier['vat_sales']."', 
									'".$aCashier['total_tax']."', '".$aCashier['service_charge']."', 
									'".$_SERVER['REMOTE_ADDR']."', '".$SYSCONF['TERMINAL']."', 
									'".$ADMIN['admin_id']."','".addslashes($aCashier['remarks'])."',
									'".$aCashier['item_lines']."','".$aCashier['item_qty']."', 
									'".$aCashier['account_id']."', '".$aCashier['bag']."')"; 

				$qr	=	@query($q);
				
				if ($qr	&& @pg_affected_rows($qr)>0)
				{
					$hid = @db_insert_id('sales_header');
					$aCashier['sales_header_id'] = $hid;

					processPromoItems($aItems, $aCashier['sales_header_id']);
		
					// Collate all similar items
					//	$aCashier['grocery_amount'] = $aCashier['dry_good_amount'] = $grocery_amount = $dry_good_amount = 0;
					$account_amount = 0;
					$newarray = null;
					$newarray = array();
					$cctr=0;
					foreach ($aItems as $temp)
					{
						$cctr++;
						if ($temp['amount'] == '0' && $temp['type']!='Tender')
						{
							galert("Line ".$cctr." has INVALID/NO Amount...CANNOT Save Transaction");
							rollback();
							$aCashier['sales_header_id'] = '';
							return 0;
						}
						$fnd=0;
						$ctr=0;
						
						if ($temp['type'] != 'Tender' && $temp['qty']>'0')
						{
							
							foreach ($newarray as $xtemp)
							{
								if ($xtemp['stock_id'] == $temp['stock_id'] && $xtemp['cdisc'] == $temp['cdisc'] && 
									  $xtemp['sdisc'] == $temp['sdisc'] && $xtemp['price'] == $temp['price'] &&
                    				  $xtemp['barcode'] == $temp['barcode'] &&
				                      $xtemp['fraction'] == $temp['fraction'] && $temp['qty']>'0' && $xtemp['qty'] > '0')
								{
									
									$dummy = $xtemp;
									$dummy['qty'] += $temp['qty'];
									$dummy['amount'] += $temp['amount'];
									$dummy['discount'] += $temp['discount'];
									$dummy['tax'] += $temp['tax'];
									$newarray[$ctr] = $dummy;
									$fnd = 1;
									break;
								}
								$ctr++;
							}
						}
						if ($fnd == 0)
						{
							$newarray[] = $temp;
						}	
					}
					// end of collation

					$aItems = null;
					$aItems = array();
					$aItems = $newarray;
					$invoice_balance = $aCashier['net_amount'];
					$cash_amount = $bankcard_amount = $account_amount = $other_amount = $reward_amount_out= 0;
    			   $reward_grocery = $reward_dry = $reward_total =  $reward_points_out = 0;
					$c=0;
					foreach	($aItems as	$temp)
					{
						
						if ($temp['type']	== 'Tender')
						{
								if (in_array($temp['tender_type'], array('C','K','G')))
								{
									//-cash and check rewards calculations
										$cash_amount += $temp['amount'];
								}
								elseif (in_array($temp['tender_type'], array('B')))
								{
									//-bankcards rewards calculations
									$bankcard_amount += $temp['amount'];
									if ($temp['bankcard_found']*1 == '0' && $temp['account']!='' && substr($temp['account'],0,1)!='*')
									{
										$q = "insert into bankcard (bankcard, bankcard_name, tender_id)
														values ('".addslashes($temp['cardno'])."',
																	'".addslashes($temp['account'])."',
																	'".$temp['tender_id']."')";
										@pg_query($q);
									}
			
								}
								elseif (in_array($temp['tender_type'], array('R')))
								{
									$reward_amount_out += $temp['amount'];
									if ($aCashier['account_id']=='' && $temp['account_id'] != '')
									{
										$aCashier['account_id'] = $temp['account_id'];
										$aCashier['account'] = $temp['account'];
									}
								}
								elseif (in_array($temp['tender_type'], array('A')))
								{
									$account_amount += $temp['amount'];
								}
								else
								{
									$other_amount += $temp['amount'];
								}
								
								//--saving
								if ($temp['sales_detail_id'] ==	'')
								{
									//checkZeroFields('sales_tender');
									$cf = array('tender_id', 'account_id','amount','service_charge');
									for ($ci = 0 ; $ci<count($cf); $ci++)
									{
										if ($temp[$cf[$ci]] == '')
										{
											$temp[$cf[$ci]]= 0;
								  		}
									}
									if ($temp['amount'] > 10000000)
									{
										$aCashier['sales_header_id'] = '';
										rollback();
										galert("Please Check Tender Amount --> ".$temp['amount']);
										return done();
									}
									#USE GIFTCHECK
									$_gc = $_SESSION['giftcheck'];
									$count = count(explode('-',$_gc));
									
									if($count == 1){
										@pg_query("update giftcheck set used = '1' where giftcheck = '".$_gc."'");	
									}else if($count > 1){										
										$s = explode('-',$_gc);
										for($x = $s[0] ; $x <= $s[1] ; $x++){;	
											@pg_query("update giftcheck set used = '1' where giftcheck = '".$x."'");	
										}
									}
									
									$q = "insert into	$sales_tender (sales_header_id, tender_id,	
												account_id,	account, cardno, carddate, amount, service_charge, remark)
										values ('".$aCashier['sales_header_id']."',	'".$temp['tender_id']."', '".$temp['account_id']."',
												'".addslashes(substr($temp['account'],0,29))."',	'".substr(addslashes($temp['cardno']),0,24)."', '".$temp['carddate']."',	
												'".$temp['amount']."', '".$temp['service_charge']."',	'".addslashes($temp['remark'])."')";
												
									$qqr = @query($q);

									if ($qqr && pg_affected_rows($qqr)>0)
									{
										$dummy = null;
										$dummy = array();
										$dummy = $temp;
										$tid = @db_insert_id('sales_tender');
										$dummy['sales_detail_id']	=	$tid;	 //force sales_tender_id to	sales_detail_id just to make sure sales_detail_id is not blank on this record
										$aItems[$c]	=	$dummy;
									}
									else
									{
								  		$commit = '2 '.db_error().$q;
								  		$message .= "Unable to Add Sales Tender...\n".db_error().$q;
										rollback();
										$aCashier['sales_header_id']='';
								  		break;
									}
								}
	
						}	
						else
						{
							if ($temp['sales_detail_id'] ==	'')
							{
								//checkZeroFields('sales_detail');
								$cf = array('stock_id', 'qty',	'price1','price', 'cost1','cdisc','sdisc', 
										'discount', 'amount', 'tax', 'fraction');
								for ($ci = 0 ; $ci<count($cf); $ci++)
								{
								  if ($temp[$cf[$ci]] == '')
								  {
									$temp[$cf[$ci]]= 0;
								  }
								}

								$q = "insert into $sales_detail 
										(sales_header_id, stock_id, barcode, fraction,qty, price1,
											price, cost1, cdisc, sdisc, discount, amount, tax)
									values 
										('".$aCashier['sales_header_id']."','".$temp['stock_id']."',
						                    '".$temp['barcode']."','".$temp['fraction']."', '".$temp['qty']."',
											'".$temp['price1']."', '".$temp['price']."', '".$temp['cost1']."',  '".$temp['cdisc']."', '".$temp['sdisc']."',
											'".$temp['discount']."', '".$temp['amount']."',	'".$temp['tax']."')";
								$qqr = @query($q);

								if ($qqr && pg_affected_rows($qqr)>0)
								{
									$dummy = null;
									$dummy = array();
									$dummy = $temp;
									$did = @db_insert_id('sales_detail');
									$dummy['sales_detail_id']	=	$did;
									$aItems[$c]	=	$dummy;
								}
								else
								{
								  	$commit = 3;
								 // $message .= 'Unable To Insert Into Sales Detail on BARCODE ['.$temp['barcode']."] \n".db_error().$q;
									 galert('Unable To Insert Into Sales Detail on BARCODE ['.$temp['barcode']."] sh_id".$aCashier['sales_header_id']."\n".pg_errormessage().$q);
								  	$aCashier['sales_header_id'] = '';
									rollback();
								  break;
								}
												
							}
							
						}
						$c++;
					}

				}
				else
				{
					$commit	= 4 . db_error();
					$message .=  "Unable to Insert Into Sales Header \n".db_error().$q;
					//return $message; //$commit;
			 	}					
			}
			else
			{
				$message .= 'Transaction Already Exists!';
				$commit = 5;
		  	}
			if ($commit == '1')
		  	{
				//$audit = 'Encoded by:'.$ADMIN['username'].' on '.date('m/d/Y g:ia').';';
				//audit($module, $q, $ADMIN['admin_id'], $audit, $aCashier['sales_header_id']);
				
				$account_balance = $account_amount;
				if ($account_balance > 0)
				{
					$grocery_debit = $drygood_debit = 0;
					if ($account_balance >= ($aCashier['grocery_amount'] + $aCashier['grocery_servicecharge']))
					{

						$grocery_debit = $aCashier['grocery_amount'] + $aCashier['grocery_servicecharge'];
						$account_balance -= ($aCashier['grocery_amount']+ $aCashier['grocery_servicecharge']);

					}
					else
					{
						$grocery_debit = $account_balance; // + $aCashier['grocery_servicecharge'];
						$account_balance = 0; // $account_amount+ $aCashier['grocery_servicecharge']);

          			}
					if ( $account_balance > '0')
					{
						$drygood_debit = $account_balance;
					}
					if ($grocery_debit != '0' || $drygood_debit!='0')
					{
						$totaldebit = $grocery_debit*1 + 1*$drygood_debit;
					 	$q = "insert into accountledger (account_id, date, sales_header_id, invoice, 
					 					grocery_debit, drygood_debit, debit, debit_balance, last_debit_balance, type, admin_id)
								values ('".$aCashier['account_id']."','$date','".$aCashier['sales_header_id']."',
								'".$aCashier['invoice']."', '$grocery_debit', '$drygood_debit','$totaldebit'
								,'$totaldebit','$totaldebit', 'T','".$ADMIN['admin_id']."')";
						 $qqr = @query($q);
						if (!$qqr)
						{
							$commit = 6;
							$message .=  "Unable to Insert Into AccountLedger \n".db_error().$q;
							
						}
					} 
				}


				$cash_amount = $aCashier['net_amount']-($bankcard_amount+$account_amount+$reward_amount_out+$other_amount);
				$rewardable_amount = $cash_amount + $bankcard_amount;
				
				if ($aCashier['account_id'] != ''  && ($rewardable_amount>0  || $reward_amount_out >0) && $commit == 1)
				{

					//compute rewards
					$reward_drygood = $reward_grocery = 0;
					$grocery_balance = $aCashier['grocery_amount'];
					$drygood_balance = $aCashier['dry_good_amount'];

					/**
					*
					* FOR GROCERIES
					*
					*/

					$is_cash_grocery = $is_card_grocery = $is_cash_dry = $is_card_dry = false;
					
					/*FOR CASH AMOUNT*/

					if ($cash_amount > $grocery_balance) {
						$reward_grocery  = round($grocery_balance/$SYSCONF['CASH_GRC_POINT'],2);
						$cash_amount     -= $grocery_balance;
						$grocery_balance = 0;

						$is_cash_grocery = true;
					} elseif ($cash_amount > 0) {

						$reward_grocery  = round($cash_amount/$SYSCONF['CASH_GRC_POINT'],2);
						$grocery_balance -= $cash_amount;
						$cash_amount     = 0;

						$is_cash_grocery = true;
					}

					/*FOR BANK CARD AMOUNT*/

					if ( $grocery_balance > 0 && $bankcard_amount > $grocery_balance ) {
						$reward_grocery  += round($grocery_balance/$SYSCONF['BANK_GRC_POINT'],2);
						$bankcard_amount -= $grocery_balance;
						$grocery_balance = 0;

						$is_card_grocery = true;
					} elseif ( $grocery_balance > 0 && $bankcard_amount > 0) {
						$reward_grocery  += round($bankcard_amount/$SYSCONF['BANK_GRC_POINT'],2);
						$grocery_balance -= $bankcard_amount;
						$bankcard_amount = 0;

						$is_card_grocery = true;
					}

					/**
					*
					* FOR DRY GOODS
					*
					*/

					if ($cash_amount > $drygood_balance) {
						$reward_drygood  = round($drygood_balance/$SYSCONF['CASH_DRY_POINT'],2);
						$cash_amount     -= $drygood_balance;
						$drygood_balance = 0;

						$is_cash_dry = true;
					} elseif ($cash_amount > 0 && $drygood_balance > 0) {
						$reward_drygood  = round($cash_amount/$SYSCONF['CASH_DRY_POINT'],2);
						$drygood_balance -= $cash_amount;
						$cash_amount     = 0;

						$is_cash_dry = true;
					}
					
					if ($bankcard_amount > 0) {
						// $reward_drygood += round($drygood_balance/$SYSCONF['BANK_GRC_POINT'],2);
						$reward_drygood += round($bankcard_amount/$SYSCONF['BANK_DRY_POINT'],2);
						$is_card_dry = true;
					}

					$reward_total = $reward_grocery + $reward_drygood;			

					/**
					 *
					 * INSERT ADDITONAL POINTS HERE
					 * test - 08000016258
					*/	

					$additional_reward_total = 0;
					if ( count($aItems) > 0 ) {
						
						/**
						 * Get promo for rewareds
						 */

						$reward_promos = lib::getPromoForRewards();

						foreach ( $aItems as $arr_item ) {
							if ( $arr_item['type'] != 'stock' ) continue;

							$additional_reward_points = lib::getRewardsMultiplier($reward_promos,$arr_item);
							
							if ( $additional_reward_points <= 0 ) continue;


							if ( $arr_item['department'] == 'G' ) { //Grocery

								if ( $is_cash_grocery ) { //use cash point system
									$additional_reward_total += $arr_item['amount'] / $SYSCONF['CASH_GRC_POINT'] * $additional_reward_points;
								} else { //use card point system
									$additional_reward_total += $arr_item['amount'] / $SYSCONF['BANK_GRC_POINT'] * $additional_reward_points;
								}


							} else { //dry goods

								if ( $is_cash_dry ) { //use cash point system
									$additional_reward_total += $arr_item['amount'] / $SYSCONF['CASH_DRY_POINT'] * $additional_reward_points;
								} else { //use card point system
									$additional_reward_total += $arr_item['amount'] / $SYSCONF['BANK_DRY_POINT'] * $additional_reward_points;
								}
							}
						}
					}
					
					$reward_total += $additional_reward_total;

					if ( ($reward_total > 0  || $reward_amount_out  > 0 ) && $commit == 1) {
						
						$aCashier['reward_total']      = round($reward_total,2);
						$aCashier['reward_points_out'] = round($reward_amount_out/$SYSCONF['VALUE_PER_POINT'],2);
						$aCashier['reward_amount_out'] = $reward_amount_out;
          
						if ($aCashier['reward_total'] == '') $aCashier['reward_total']=0;
						if ($aCashier['reward_points_out'] == '') $aCashier['reward_points_out']=0;
						if ($aCashier['reward_amount_out'] == '') $aCashier['reward_amount_out']=0;
           
						$q = "insert into reward (sales_header_id, invoice, type, date,account_id,
					                       	points_in, amount_in, points_out, amount_out, terminal)
							values ('".$aCashier['sales_header_id']."','".$aCashier['invoice']."', '1',
					     							'$date','".$aCashier['account_id']."', 
										      	'".$aCashier['reward_total']."','$rewardable_amount', 
								              	'".$aCashier['reward_points_out']."', 
											      '".$aCashier['reward_amount_out']."', '".$SYSCONF['TERMINAL']."')";
						$qr = @query($q);
						if (!$qr) {	
							$commit = 9;
							$message .=  "Unable to Insert Into Reward \n".db_error().$q;
						}
					}
				}
			
				$q = "update invoice set invoice='".$aCashier['invoice']."' where ip='".$_SERVER['REMOTE_ADDR']."'";
				$qr = @query($q);
				if ($qr && @pg_affected_rows($qr) == 0)
				{
					$q = "insert into invoice (invoice,ip,terminal) 
								values ('".$aCashier['invoice']."', 
											'".$_SERVER['REMOTE_ADDR']."',
											'".$SYSCONF['TERMINAL']."')";
					$qr = @query($q);
					if (!$qr)
					{
						$commit = 16;
						$message = "Unable to Insert Into invoice sequence...".$q;
						galert($message);
					}
					else
					{
						galert('Invoice Instance Made...');
						
					}
				}
				elseif (!$qr)
				{
				   	$commit=7;
						$message = "Unable to update invoice sequence...";
 				}

				if ($aCashier['suspend_header_id'] != '')
				{
					$q = "update suspend_header set status='S' where suspend_header_id='".$aCashier['suspend_header_id']."'";
					$qr = @query($q);
					if ($qr)
					{
						$audit = 'Suspend Finished by:'.$ADMIN['username'].' on '.date('m/d/Y g:ia').';';
						//audit($module.'_suspend', $q, $ADMIN['admin_id'], $audit, $aCashier['suspend_header_id']);
					}
					else
					{
					  	$commit=8;
 						$message = db_error();
 					}				
				}	
		
				if ($commit == 1)
				{
					commit();
					return $commit;
				}  
				else
				{
					$aCashier['sales_header_id'] = '';
					rollback();
					return $commit;
        		}
			
			}	
			else
			{
			  $aCashier['sales_header_id'] = '';
			  $qr = rollback();
			  $message .= 'FATAL PROBLEM: Unable to Save Transaction. Commit Error No.: '.$commit;
			  glayer('message.layer',$message);
			  
			  $aip = explode('.',$_SERVER['REMOTE_ADDR']);
			  $reportfile= '/data/log/ERR'.$aip[3].'.txt';
			  $fo = @fopen($reportfile,'a+');
			  @fwrite($fo, "\n".date('Y-m-d g:ia')."\n".$message);
			  @fclose($fo);
			  return $commit;
		  	}
		  	return $commit;
  	}        
		
		
	//-- on Call Functions / Event Driven
	$xajax->registerFunction('fkey');
	function fkey($f, $form) 
	{
		global $aItems, $aCashier, $ADMIN , $SYSCONF;
		
			if ($ADMIN == null)
			{
				galert('User session has expired. Please Log-In Again...');
				session_unset();
				echo "<script>window.location='../'</script>";
				exit;
			}
	
			$tables = $SYSCONF['tables'];
			$sales_header = $tables['sales_header'];
			$sales_detail = $tables['sales_detail'];
			$sales_tender = $tables['sales_tender'];
		
			$textbox = $form['textbox'];
			$line_no = $form['line_no'];
			$aCashier['line_no'] = $line_no;
	      //grid($aItems);
			
			$grid_flag = 1;
			
			if($f == "H"){
				$aCashier['bag'] = 1;
				galert("Eco Bag Discount Applied");
				//gscript("xajax_fkey(13,xajax.getFormValues('f1'))");
				grid($aItems);
			}else if($f == "I"){
				$aCashier['bag'] = 0;
				galert("Eco Bag Discount Unapplied");
				//gscript("xajax_fkey(13,xajax.getFormValues('f1'))");
				grid($aItems);
			}
			
			if ($f == 'R' && !chkRights2('salesreprint','mview',$ADMIN['admin_id']))
			{
				galert("RE-PRINTING NOT ALLOWED. PLEASE ASK SUPERVISOR"); 
			}
			elseif ($f == 'R')
			{
//				$aCashier['REPRINT'] = 1;
				$p = reprint($textbox);
			}
			elseif ($f == 'Z')
			{
				$q= @include_once('report.closing.php');
				if (!$q)
				{
					$message = $q;
				}
				else
				{
					$cl = closing();
					if ($cl == '1')
					{
						$message = 'End of Day Closing...';
					}
					else
					{
						$message = $cl;
					}
				}
			}	
			elseif ($f == 'G' && $textbox!='')
			{
				$aCashier['bagger'] = $textbox;
				$msg .= 'Bagger Set: '.$aCashier['bagger'];
				glayer('bagger.layer','Bagger: '.$aCashier['bagger']);
				$grid_flag = 0;
				gset('textbox','');				
			}
			elseif ($f == 'C')
			{
				$aCashier['account'] = $textbox;
				$msg = 'Customer Name Set:'.$aCashier['account'];
				gset('textbox','');
				$grid_flag = 0;				
			}
			elseif ($f == 'S')
			{
				include_once('report.category.php');
				
				$grid_flag = 0;				
			}
			elseif ($f == 'E')
			{
				include_once('cashier.receipt.print.php');
							
				$aRaffle=null;
				$aRaffle = array();
				$ok=1;
				
				$q = "select * from raffle where enable='Y' order by date_from desc ";
				$qr = @pg_query($q);
				if (@pg_num_rows($qr) == 0)
				{
					galert(' No Active E-Raffle Promo Available... ');
					$ok=0;
				}
				
				if ($ok == '1')
				{
					$aRaffle = @pg_fetch_assoc($qr);
				
					if ($aRaffle['raffle_modulu']*1 == '0')
					{
						$aRaffle['raffle_modulu'] =1;
					}
				
				
					if ($textbox == '')
					{
						include_once('../accounts/accountbalance.php');
						$aReward =  rewardBalance($aCashier['account_id']);
						$points_balance = $aReward['points_balance'];
						if ($points_balance > $aRaffle['raffle_modulu']);
						{
							
							$points_convert = intval($points_balance/$aRaffle['raffle_modulu'])*$aRaffle['raffle_modulu'];
							gset('textbox',$points_convert);
						}
						galert("Nothing to Convert....\nCurrent Running Points for ".$aCashier['account']."\nis : ".$points_balance." Max Convertible Points: ".$points_convert);

					}
					elseif ($aCashier['account_id'] == '')
					{
						galert('No Bonus Card Provided...');
					}
					else
					{
						include_once('../accounts/accountbalance.php');
					
						$points_specified = $textbox;
						$aReward =  rewardBalance($aCashier['account_id']);
						$points_balance = $aReward['points_balance'];
						$points_maxconvert = intval($points_balance/$aRaffle['raffle_modulu'])*$aRaffle['raffle_modulu'];
						
						if ($points_specified > $points_maxconvert)
						{
							galert(' Specified Points ( '.$points_specified.') for e-raffle Ticket exceed Max Convertible Points ( '.$points_maxconvert.")\n   Please Change Points to be Converted...");
							gset('textbox',$points_maxconvert);
						}
						elseif ($points_maxconvert <= 0)
						{
							galert(" Points $points_convert NOT enough for a raffle ticket...");
						}
						else
						{ 
							$q = "select * from cache where type='RAFFLE'";
							$qr = @pg_query($q);
							if (!$qr)
							{
								galert('Error Querying Raffle Numbers...');
							}
							else
							{
								$r = @pg_fetch_object($qr);
								$cache_id = $r->cache_id;
								
								$raffle_count = intval($points_specified/$aRaffle['raffle_modulu']);
								$points_convert = $raffle_count*$aRaffle['raffle_modulu'];	

								$raffle_from = $r->value1 + 1;	
								$raffle_to = $raffle_from + $raffle_count -1;

								if ($cache_id > '0')
								{
									$qc = "update cache set value1=$raffle_to where cache_id='$cache_id'";
								}
								else
								{
									$qc = "insert into cache (type, value1) values ('RAFFLE','$raffle_to')";
								}
								$qcr = @pg_query($qc);
								if (!$qcr)
								{
									galert('Error Updating Raffle Generator...');
								}
								else
								{
									$now = date('Y-m-d G:i');
									$q = "insert into raffleledger (account_id, raffle_from, raffle_to, 
															date, raffle_count, points_convert, raffle_modulu, 
															raffle_id)
												values ('".$aCashier['account_id']."',
															'$raffle_from',
															'$raffle_to',
															'$now',
															'$raffle_count',
															'$points_convert',
															'".$aRaffle['raffle_modulu']."',
															'".$aRaffle['raffle_id']."')";
									$qr = @pg_query($q);	

									if (!$qr)
									{
										galert(' Error Posting Raffle Numbers....'.pg_errormessage().$q);
									}
									else
									{
										$raffleledger_id  = @db_insert_id('raffleledger');
										$q = "insert into reward (account_id,date,points_out,type,terminal,admin_id)
												values ('".$aCashier['account_id']."','".substr($now,0,10)."','$points_convert','3',
															'".$SYSCONF['TERMINAL']."', '".$ADMIN['admin_id']."')";
										$qr = @pg_query($q);	
										
										if ($aRaffle['autoprint'] == 'Y')
										{
											rafflePrint($raffleledger_id);
										}

										if (!$qr)	
										{
											galert(' Error Crediting Raffle Points To Bonus Points....'.pg_errormessage().$q);
										}
										else
										{
											galert('Converted '.$points_convert.' Bonus Points to '.$raffle_count.' Raffle Ticket(s)...');
										}
										gset('textbox','');

									}
								}
							}
						}
					}
				}
			}
			elseif ($f == 'D' && $textbox != '')
			{
				
				$temp = $aItems[$line_no - 1]; //$aItems[$aCashier['line_no']-1];
				if ($temp['barcode'] == 'G')
				{
					$temp['stock'] = $textbox;
					//$aItems[$aCashier['line_no']-1] = $temp;
					$aItems[$line_no-1] = $temp;
				}
				else
				{
					galert('Cannot Change Description...');
				}
				gset('textbox','');
			}
			
			elseif ($f == 'M' && $textbox=='')
			{
				$aCashier['account_code'] = '';
				$aCashier['cardno'] = '';
				$aCashier['account'] = '';
				$aCashier['account_id'] = '';
				$aCashier['member'] = '';
				$aCashier['remarks'] = '';
				$aCashier['credit_limit'] = '';

				renewLineItems();
				galert('Credit Card Information Cleared');					
				$i = showInstruction();
				glayer('instruction.layer',$i);
			}
			elseif ($f == 'B' && $textbox=='')
			{
				$aCashier['account_code'] = '';
				$aCashier['cardno'] = '';
				$aCashier['account'] = '';
				$aCashier['account_id'] = '';
				$aCashier['member'] = '';
				$aCashier['remarks'] = '';
				$aCashier['credit_limit'] = '';

				renewLineItems();

				galert('Member Information Cleared');
				$i = showInstruction();
				glayer('instruction.layer',$i);

			}
			elseif ($f == 'B' && $textbox!='')
			{
				$msg = checkRewardMember($textbox);
				if ($msg)
				{
					renewLineItems();
					gset('textbox','');
				}
				else
				{
						$msg = 'Bonus Card Number NOT Found ...';
				}
				//$grid_flag = 0;				
				$i = showInstruction();
				glayer('instruction.layer',$i);
			}
			elseif ($f == 'M' && $textbox!='')
			{
				$q = "select * from account where cardno = '$textbox'";
				$r = @fetch_object($q);
				if ($r)
				{
					$rem = '';
					if ($r->date_expiry < date('Y-m-d'))
					{
						$rem = " ** CARD EXPIRED ** ";
					}
					else
					{
						renewLineItems();
					}
					$q = "select 
                			sum(points_in) as points_in, 
                			sum(points_out) as points_out 
              			from 
                			reward 
              			where 
                			account_id='$r->account_id' and
                			status!='C'";
          		$rr = @fetch_object($q);
					$points_balance = $rr->points_in - $rr->points_out;
		  			include_once('../accounts/accountbalance.php');
					$acct = customerBalance($r->account_id);
					$balance_available = round($r->credit_limit - $acct['balance'],2);
					$msg = "Inquiry Credit Member: [".$r->cardno.'] '.$r->account."\n".
								'  Points:'.$points_balance.
								'  Available: '.number_format($balance_available,2).
								"\n Expiry Date: ".ymd2mdy($r->date_expiry).$rem.
								"\n(Press ALT+M Again To Clear Credit Card Member Data)";
					
					gset('textbox','');
				}
				else
				{
					$msg = 'Account/Card Number NOT Found ...';
					//galert($msg);
				}
				$grid_flag = 0;				
				$i = showInstruction();
				glayer('instruction.layer',$i);
			}
			elseif ($f == 'V' && !chkRights2('sales','mdelete',$ADMIN['admin_id']))
			{
				$msg = "Access Denied. CANNOT VOID Transaction";
			}
			elseif ($f == 'V' && $textbox!='')
			{
				$invoice = str_pad($textbox,8,'0',str_pad_left);
				$q = "select * 
							from 
								$sales_header 
							where 
								invoice='$invoice' and ip='".$_SERVER['REMOTE_ADDR']."'
							order by
								sales_header_id desc offset 0 limit 1";
				$qr = @query($q);
				
				if (!$qr)
				{
					$msg = 'Unable to query transaction header file...';
				}
				elseif (pg_num_rows($qr) == 0)
				{
					$msg = 'Transaction INVOICE/DOCKET NOT Found...';
				}
				else
				{
					$r = @qr_object($qr);
					$q = "update $sales_header set status='V' where sales_header_id='$r->sales_header_id'";
					$qr = @query($q);
					if (!$qr)
					{
						$msg = "Unable to VOID transaction...".db_error();
					}
					elseif (@pg_affected_rows($qr) == 0)
					{
						$msg = "Invoice/Docket Number NOT found...".$q;
					}
					else
					{
						$audit = "Voided by:".$ADMIN['name']." on ".date('m/d/Y g:ia').';';
						$a = audit('cashier',$q,$ADMIN['admin_id'],$audit,$r->sales_header_id);
						if (!$a)
						{
							$msg = "Unable to create audit..";
						}
						
						$q = "update accountledger set status='C', enable='N' where
										invoice='$invoice' and 
										sales_header_id = '$r->sales_header_id' and
										account_id='$r->account_id'";
						$qa = @pg_query($q);
						if (!$qa)
						{
							galert("Error Updating Accountledger ".pg_errormessage());
						}
						
						$q = "update reward set status='C' 
										where
											sales_header_id = '$r->sales_header_id' and
											invoice='$invoice' and
											account_id = '$r->account_id'";
						$qa = @pg_query($q);
						if (!$qa)
						{
							galert("Error Updating Rewards ".pg_errormessage());
						}
						$msg = "Transaction Docket Successfully VOIDED...";
						$p = reprint($textbox);
						gset('textbox','');
					}
				}
			}
			elseif ($f == 27)
			{
			   
				if ($aCashier['Tender'] > 0)
				{
					$temp = $aItems[$line_no -1];  //$aItems[$aCashier['line_no'] -1];
					if ($temp['type'] == 'Tender')
					{
						$arr = null;
						$arr = array();
						$c=0;
						foreach ($aItems as $temp)
						{
							$c++;
							if ($c != $line_no && $temp['type'] != 'ServiceCharge')
							{
								$arr[] = $temp;
							}	
						}
						$aItems = $arr;
					}	
					$aCashier['Tender'] = 0;
				}
				gset('textbox','');
				$aCashier['GlobalDiscount'] = 0;
				$aCashier['LineDiscount'] = 0;
				glayer('prompt.layer','<font size=5>Scan Item</font>');
				$i = showInstruction();
				glayer('instruction.layer',$i);
				glayer('plu.layer','');
				hide_layer('plu.layer');
			}
			elseif ($f == 113)
			{
				//f2 - clear textbox
				gset('textbox','');
				$grid_flag = 0;
			}
			elseif (($f == 114 || $f == 123) && $ADMIN['usergroup']!='A' && chkRights2('noPOSdelete','madd',$ADMIN['admin_id']))
			{
				//cannot delete Item
				galert('You have NO permission to Delete Line Item');
			}
			elseif ($f == 114 || $f == 123)
			{
				//delete Item
				//$aItems = array_slice($aItems, $aCashier['line_no']-1,1);

				$c=0;
				$arr = null;
				$arr = array();
				foreach ($aItems as $temp)
				{
					$c++;
					if ($c != $line_no && $temp['type'] != 'Tender'  && $temp['type'] != 'ServiceCharge')
					{
						$arr[] = $temp;
					}
				}
				$aItems = $arr;


				if ($line_no > 1) 
				{
					$line_no--;
				}
				elseif (count($aItems)>0) 
				{
					$line_no = 1;
				}
				else
				{
					$line_no = '';
				}
				$aCashier['line_no']=$line_no;
				gset('line_no',$line_no);
				gset('textbox','');
				
			}
			elseif ($f == 115 && (1*$textbox > 10000 or 1*$textbox == '0')) //explicit expression to limit qty
			{
				$msg = "Check Quantity Please.";
			}
			elseif ($f == 115 && $textbox != '')
			{

				$temp = $aItems[$line_no -1]; //$aItems[$aCashier['line_no']-1];
				if ($temp['readonly'] == 1)
				{
					galert('Cannot Change Quantity for this Line');
				}
				else
				{
					$temp['qty'] = 1*$textbox;
					if ($SYSCONF['USE_MEDIUM_PRICE']=='Y' && $temp['fraction'] == 1 && ($temp['qty']>=$temp['fraction2']/2) && $temp['price2']>0)
					{
					 	// for wholesale price (per dozen price)
				    	$temp['qty'] = round($temp['qty']/$temp['fraction2'],3);
					 	$temp['fraction'] = $temp['fraction2'];
					 	$temp['price'] = $temp['price2'];
					 	$temp['price1'] = $temp['price2'];
  	      		}

					$temp['amount'] = round2($temp['price'] * $temp['qty']);
					if ($temp['taxable'] == 'Y')
					{
						$temp['taxbase'] = round($temp['amount']/(1 + ($SYSCONF['TAXRATE']/100)),2);
						$temp['tax'] = $temp['amount'] - $temp['taxbase'];
					}
					
					//$aItems[$aCashier['line_no']-1] = $temp;
					$aItems[$line_no-1] = $temp;

					checkPayment();
					$line_no = $aCashier['line_no'];
					gset('textbox','');
				}
			}
			elseif ($f == 116 && $textbox == '')
			{
				//restore price
				
				$temp = $aItems[$line_no -1]; //$aItems[$aCashier['line_no']-1];
				if ($temp['fraction'] == 1)
				{
					$temp['price'] = $temp['price1'];
				}
				else
				{
					$temp['price'] = $temp['price3'];
				}
				$temp['amount'] = round2($temp['price'] * $temp['qty']);

				if ($temp['taxable'] == 'Y')
				{
					$temp['taxbase'] = round($temp['amount']/(1 + ($SYSCONF['TAXRATE']/100)),2);
					$temp['tax'] = $temp['amount'] - $temp['taxbase'];
				}
				$aItems[$line_no-1] = $temp;
				checkPayment();
				$line_no = $aCashier['line_no'];
				// $aItems[$aCashier['line_no']-1] = $temp;
				gset('textbox','');
			}
			elseif ($f == 116 && $textbox != '')
			{
				//change price -- F5
				if (1*$textbox < '0')
				{
					$msg = "Invalid Price";
				}
				elseif (1*$textbox > '250000')  //explicit expression to limit price
				{
					$msg = "Check Price Please.";
				}
				else
				{
					$temp = $aItems[$line_no -1]; //$aItems[$aCashier['line_no']-1];
					if ($temp['readonly'] == 1)
					{
						galert('Cannot Change Amount for this Line...');
					}
					else
					{
						if ($temp['price1'] != '0' && $ADMIN['usergroup']!='A' && chkRights2('noPOSChangePrice','madd',$ADMIN['admin_id']))
						{
							//restrict change Price
							galert('You have NO Permission to Change Item Price');
						}
						else
						{
						
						//if ($temp['taxable'] != 'Y')
						//{
							$temp['note'] = 'edited';
							$temp['price'] = 1*$textbox;
							$temp['amount'] = round2($temp['price'] * $temp['qty']);
							if ($temp['price1']=='0')
							{
								$temp['price1'] = $temp['price'];
							}	

							if ($temp['taxable'] == 'Y')
							{
								$temp['taxbase'] = round($temp['amount']/(1 + ($SYSCONF['TAXRATE']/100)),2);
								$temp['tax'] = $temp['amount'] - $temp['taxbase'];
							}
							
							$aItems[$line_no-1] = $temp;
							checkPayment();
							$line_no = $aCashier['line_no'];
//							$aItems[$aCashier['line_no']-1] = $temp;
						//}
						//else
						//{
						//	$msg = "Cannot Change Price...";
						//}
						}	
						gset('textbox','');
					}
				}
			}
			elseif ($f == 117 && $aCashier['Tender'] == '2')
			{
				//-- F6
				include_once('cashier.searchaccount.php');
			}	
			elseif ($f == 117 && $textbox != '')
			{
				//-- F6
				include_once('cashier.searchstock.php');
			}	
			elseif ($f == 118 && $textbox != '')
			{
				//-- F7 Line Discount
//				$temp = $aItems[$aCashier['line_no']-1];
				$temp = $aItems[$line_no-1];

				if ($temp['netitem'] == 'Y')
				{
					$msg = "NET ITEM. Discount Not Applied";
				}
				elseif ($textbox > $temp['max_discount'] && $temp['max_discount'] > 0)
				{
					$msg = "MAXIMUM Discount Exceeded. (".$temp['max_discount']."%)";
				}
				else
				{
				
					if ($textbox*1 > '100' or $textbox*1 < '0')
					{
						$msg = "Invalid Discount ($textbox%)";
					}
					elseif ($textbox*1 == '0' && $textbox != '0')
					{
						$msg = "Invalid Discount ($textbox%)";
					}
					else
					{
						galert(" $textbox % Line Discount Applied...");
						
						$temp['cdisc'] = $textbox;
						$price_discount = $temp['price1']*($temp['cdisc']/100);
						$temp['price'] = round($temp['price1'] - $price_discount,2);
						$temp['amount'] = round2($temp['price'] * $temp['qty']);
						$temp['discount'] = $temp['amount'] - round2($temp['price1']*$temp['qty']) ;
						$temp['taxbase'] = round($temp['amount']/(1 + ($SYSCONF['TAXRATE']/100)),2);
						$temp['tax'] = $temp['amount'] - $temp['taxbase'];
						$temp['note'] = 'edited';
						$aCashier['remarks'] .= 'Line Discount;';
		
//						$aItems[$aCashier['line_no']-1] = $temp;
						$aItems[$line_no-1] = $temp;
						gset('textbox','');
					}
				}
			}	
			elseif ($f == 119)
			{
				//e -- F8 Global Discount
				if ($textbox != '' && ($textbox*1 > '100' || $textbox*1<='0'))
				{
				  $msg = 'Invalid discount...';
        		}
				elseif ($textbox != '')
				{
				  $aCashier['GlobalDiscount'] = 1;
        		}
        		else
        		{
				  $aCashier['GlobalDiscount'] = 0;
        		}
			  	insertGlobalDiscount($textbox);
			}	
			elseif ($f == 120 &&  $aCashier['item_lines']>0)
			{
				//tender -- F9
				if ($textbox != '')
				{
					$aCashier['Tender'] = 1;
					//$form = $form['textbox'] = $textbox;
					//$form['called'] = 1;
					$msg = insertTender($textbox);
					if ($aCashier['account'] != ''  && $aCashier['Tender'] == '3')
					{
						gset('textbox', $aCashier['account']);
					}
					elseif ($aCashier['cardno'] != ''  && $aCashier['Tender'] == '2')
					{
						gset('textbox', $aCashier['cardno']);
					}
					else
					{
						gset('textbox', '');
					}	
				}
				else
				{
					$q .= "select * from tender where enable='Y' order by tender_code";
					$qr = @query($q);
					if (!$qr)
					{
						$msg = 'Error Selecting Payment Types';
					}
					else
					{
						$result="<table width='100%' height='100%' cellspacing='0' cellpadding='0' border='0'>";
						while ($r = @qr_object($qr))
						{
							$result .= "<tr><td valign='top' height='1%'>&nbsp;<b><font face='Verdana' size='2'>$r->tender_code</font></b></td><td align='left'>&nbsp;<font face='Verdana' size='2'>$r->tender</font></td></tr>";
						}
						$result .= "<tr><td colspan='2' align='center'><div id='itemcount.layer'></div></td></tr>";
						$result .= "</table>";
						glayer('prompt.layer','<font size=5>Select Payment Type</font>');
						glayer('instruction.layer',$result);
						$aCashier['Tender']  =1;
					}
					$grid_flag=0;
				}
			}	
			elseif ($f == 121 && $aCashier['Tender']>0 && ($aCashier['subtotal']>$textbox))
			{
				$msg = 'Payment is NOT yet finished...';
			}
			elseif ($f == 121 && $aCashier['item_lines']<=0)
			{
				$msg = 'Nothing to save...';
			}
			elseif ($f == 121 && $aCashier['tender_amount'] > 0 && $aCashier['subtotal'] > 1*$textbox)
			{
				$msg = 'Lacking Payment...';
			}
			elseif ($f == 121 && $textbox*1 > 10000000)
			{
				$msg = 'Please Re-Check Amount Tendered...';
			}
			elseif ($f == 121 && $aCashier['Tender']>0 && $aCashier['Tender']<4)
			{
				$msg = 'Provide Payment Information';
			}
			elseif ($f == 121 && $aCashier['item_lines']>0)
			{
				// finish
				if ($aCashier['Tender'] == '4' && $textbox!= '')
				{
					$msg = insertTender($textbox);
					grid($aItems);
					gset('textbox','');
					$textbox = '';
				}

				if ($aCashier['tender_amount'] != 0 && (round($aCashier['net_amount'],2) > round($aCashier['tender_amount']+ 1*$textbox,2)))
				{
					//$message = '<font size=3><b>Lacking Payment ('.$aCashier['net_amount'].'/'.$aCashier['tender_amount'].')</b></font>';
					$msg = 'Lacking Payment ('.$aCashier['net_amount'].'/'.$aCashier['tender_amount'].')';
				}
				else
				{
					//include_once('cashier.receipt.print.php');
					$s = saveSales($textbox);					
					
					if ($s == '1')
					{
						include_once('cashier.receipt.print.php');
						
						$printagain = receiptprint();
						if ($printagain > 1)
						{
						//	$aCashier['REPRINT'] = 1;
							for ($cp=1;$cp<$printagain;$cp++)
							{
								$xp = receiptprint();
							}
						}
						
						$previous_subtotal = $aCashier['subtotal'];
						$aItems = null;
						$aItems = array();
						$aCashier = null;
						$aCashier = array();

						$bagger = $aCashier['bagger'];

						$aCashier['previous_subtotal'] = $previous_subtotal;

						$aCashier['admin_id'] = $ADMIN['admin_id'];
						$aCashier['TAXRATE'] = $aCashier['TAXRATE'];
					
						$aCashier['bagger'] = $bagger;
						showSubtotal();
						
						$i = showInstruction();
					
						gset('textbox','');
						gset('line_no','');				
						glayer('grid.layer','');
						glayer('prompt.layer','<font size=5> Scan Item</font>');
						glayer('instruction.layer',$i);
						$message = 'Transaction Saved...';

					  	$aip = explode('.',$_SERVER['REMOTE_ADDR']);
			  			$reportfile= '/data/cache/CACHE'.$aip[3].'.txt';
	  					if (file_exists($reportfile))
	  					{
	  						$fo = @fopen($reportfile, "w+");
	  						@fclose($fo);
						}
	  					

					}
					else
					{
						$msg = 'Problem Saving Transaction...Commit Error '.$s;
					}	
					$grid_flag = 0;
				}	
			}
			elseif ($f == 13)
			{
				//select [ENTER]
//				$temp = $aItems[$aCashier['line_no']-1];
				$temp = $aItems[$line_no-1];
				if ($temp['qty'] != '1' && $temp['qty']!='')
				{
					$value = $temp['qty'].'*'.$temp['barcode'];
				}
				else
				{
					$value = $temp['barcode'];
				}
				$aCashier['edit'] = 1;
				gset('textbox',$value);
				
			}
			elseif ($f == 38)
			{
				//Up
				if ($aCashier['line_no'] > 1)
				{
					$aCashier['line_no'] = $aCashier['line_no'] - 1;
				}	
			}
			elseif ($f == 40)
			{
				//down
				if ($aCashier['line_no'] < count($aItems))
				{
					$aCashier['line_no'] = $aCashier['line_no'] + 1;
				}
			}
			else
			{
				$grid_flag = 0;
			}
			
			if ($grid_flag == 1)
			{
	         
	         //if ($line_no == '' && count($aItems)>0) $line_no=1;
	         //gset('line_no',$line_no);
	         //$aCashier['line_no'] = $line_no;

	         grid($aItems);
			}	
			glayer('message.layer',$message);
			if ($msg != '')
			{
				galert($msg);
			}	
			return done();           
    }

    function insertGlobalDiscount($value)
    {
			global $aCashier, $aItems;
			$ax	=	explode("*",$value);

        	if ($aCashier['GlobalDiscount'] < 1)
        	{
					$q .= "select * from discount where enable='Y' order by discount_code";
					$qr = @query($q);
					if (!$qr)
					{
						$message = 'Error Selecting Discount Types';
					}
					else
					{
						$result="<table width='100%' height='100%' cellspacing='0' cellpadding='0' border='0'>";
						while ($r = @qr_object($qr))
						{
							$result .= "<tr><td valign='top' height='1%'>&nbsp;<b><font face='Verdana' size='2'>$r->discount_code</font></b></td><td align='left'>&nbsp;<font face='Verdana' size='2'>$r->discount_type</font></td></tr>";
						}
						$result .= "<tr><td colspan='2' align='center'><div id='itemcount.layer'></div></td></tr>";
						$result .= "</table>";
						glayer('prompt.layer','<font size=5>Select Discount Type</font>');
						glayer('instruction.layer',$result);
						$aCashier['GlobalDiscount']  =1;
					}

        	}
			elseif ($aCashier['GlobalDiscount'] == 1)
			{
					//1 - select Discount Type
					$discount_code = $ax[0];
					$q = "select * from discount where discount_code='$discount_code' and enable='Y'";
					$qr = @query($q);
					if (!$qr || (@pg_num_rows($qr) == 0))
					{
						//glayer('message.layer','<font size=3><b>No or Invalid Discount Code Selected</b></font>');
						galert("No or Invalid Discount Code Selected");
						$grid_flag = 0;
					}
					else
					{
  				  		$r = @qr_object($qr);
  				  		$aCashier['GlobalDiscountCode'] = $r->discount_code;
  				  		$aCashier['GlobalDiscountId'] = $r->discount_id;
  				  		$aCashier['GlobalDiscountPercent'] = $r->discount_percent;
  				  		$aCashier['GlobalDiscountComputation'] = $r->computation;
  				 		$aCashier['discount_type'] = $r->discount_type;

  				  		$aCashier['discount_id'] = $r->discount_id;
  				  		$aCashier['discount_percent'] = $r->discount_percent;

 				    	$aCashier['GlobalDiscount']=2;
  						glayer('prompt.layer','<font size=5>Discount Card Number</font>');
     					gset('textbox','');
  					
  				  		if (count($ax)>1)
  				  		{
  				    		$aCashier['discount_card'] = $ax[1];
  				    		if ($aCashier['GlobalDiscountComputation']=='A')
  				    		{
    							glayer('prompt.layer',"<font size=5>$r->discount_type Discount Amount</font>");
  				    		}
  				    		else
  				    		{
    							glayer('prompt.layer',"<font size=5>$r->discount_type Percent Discount</font> <font size=2>(use [,] for multiples)</font>");
      					}
     						gset('textbox',$aCashier['GlobalDiscountPercent']);
   				    	$aCashier['GlobalDiscount']=3;
  				  		}
  				  		if (count($ax)>2)
  				  		{
  				    		$aCashier['GlobalDiscountPercent']=$ax[2];
  				    		$aCashier['GlobalDiscount']=4;
          	  		}
          		}
        }  
        elseif ($aCashier['GlobalDiscount']==2)
        {
            //-Discount Card Number
  				  	$aCashier['discount_card'] = substr($ax[0],0,15);
 				   $aCashier['GlobalDiscount']=3;
  				   if ($aCashier['GlobalDiscountComputation']=='A')
  				   {
    					glayer('prompt.layer',"<font size=5>$r->discount_type Discount Amount</font>");
  				   }
  				   else
  				   {
    					glayer('prompt.layer',"<font size=5>$r->discount_type Percent Discount</font> <font size=2>(use [,] for multiples)</font>");
      			}
     				gset('textbox',$aCashier['GlobalDiscountPercent']);
  				  	if (count($ax)>1)
  				  	{
  				   	$aCashier['GlobalDiscountPercent']=$ax[1]*1;
  				    	$aCashier['GlobalDiscount']=4;
            	}
        }
        elseif ($aCashier['GlobalDiscount']==3)
        {
              //-Percent Discount
             
	         $d = $ax[0]*1;
				if (($aCashier['GlobalDiscountComputation']=='P') && ($d > 100 or $d < 0))
				{
					galert("Invalid Discount...($d%)");
					$grid_flag = 0;
				
				}
				elseif ($d==0 && $ax[0] != '0')
				{
					 galert("Zero/Non Numeric or No Value Discount is Specified...(".$ax[0].")");
  				    $aCashier['GlobalDiscount']=3;
				} 
				else
				{
  				    $aCashier['GlobalDiscountPercent']=$d;
  				    $aCashier['GlobalDiscount']=4;
          	}
        }
        
        if ($aCashier['GlobalDiscount'] == 4 && $aCashier['GlobalDiscountComputation']== 'A')
        {
        		
				galert("P $d global discount is applied...");
        		$aCashier['discount_amount'] = $aCashier['GlobalDiscountPercent'];
				#DISCOUNT AMOUNT APPLICATION
        		
				$c=0;
				#$aCashier['discount_amount'] = 0;
  				foreach ($aItems as $temp)
  				{
  					if ($temp['netitem'] == 'Y')
  					{
  						$c++;
  						continue;
  					}
  		
					galert("$temp[price1] - $d");
  					#$price_discount0 = $temp['price1']*($temp['cdisc']/100);
  					$temp['price'] = $temp['price1'] - $d;

  					#$price_discount1 = $temp['price']*($temp['sdisc']/100);
  					#$temp['price'] = $temp['price'] - $d;


  					$temp['amount'] = round2($temp['price'] * $temp['qty']);
  					$temp['discount'] = $temp['amount'] - $temp['price1']*$temp['qty'] ;
  					$temp['taxbase'] = round($temp['amount']/(1 + ($temp['taxrate']/100)),2);
  					$temp['tax'] = $temp['amount'] - $temp['taxbase'];
  					$temp['note'] = 'edited';
  					$aCashier['discount_amount'] += $temp['discount'];
  
  					$aItems[$c] = $temp;
  					$c++;
  				}
				$aCashier['remarks'] .= 'Global Discount '.$aCashier['GlobalDiscountPercent'].';';
				$aCashier['discount_percent'] = $textbox*1;
				 
				$aCashier['GlobalDiscount']='';
				$aCashier['GlobalDiscountCode']='';
				$aCashier['GlobalDiscountPercent']='';
				 
  				gset('textbox','');
				glayer('prompt.layer','<font size=5>Scan Item</font>');
				$i = showInstruction();
				glayer('instruction.layer',$i);
				glayer('plu.layer','');
				hide_layer('plu.layer');
				
				
				#END OF DISCOUNT AMOUNT APPLICATION
        }
        elseif($aCashier['GlobalDiscount'] == 4 )
        {
				galert("$d% global discount is applied...");
        
				$gd0 = $gd1 = 0;
        		$gd = explode(',',$aCashier['GlobalDiscountPercent']);
        		$gd0 = $gd[0]*1;
        		$gd1 = $gd[1]*1;
        		
          	$c=0;
          	$aCashier['discount_amount'] = 0;
  				foreach ($aItems as $temp)
  				{
  					if ($temp['netitem'] == 'Y')
  					{
  						$c++;
  						continue;
  					}
  		
					if ($gd0>$temp['max_discount'] && $temp['max_discount'] > 0)
					{
						$temp['cdisc'] = 0;
						$temp['sdisc'] = $temp['max_discount'];
					}
					else
					{  		
  						$temp['cdisc'] = $gd0; //$aCashier['GlobalDiscountPercent'];
  						$temp['sdisc'] = $gd1;
  					}
  					$price_discount0 = $temp['price1']*($temp['cdisc']/100);
  					$temp['price'] = $temp['price1'] - $price_discount0;

  					$price_discount1 = $temp['price']*($temp['sdisc']/100);
  					$temp['price'] = $temp['price'] - $price_discount1;


  					$temp['amount'] = round2($temp['price'] * $temp['qty']);
  					$temp['discount'] = $temp['amount'] - $temp['price1']*$temp['qty'] ;
  					$temp['taxbase'] = round($temp['amount']/(1 + ($temp['taxrate']/100)),2);
  					$temp['tax'] = $temp['amount'] - $temp['taxbase'];
  					$temp['note'] = 'edited';
  					$aCashier['discount_amount'] += $temp['discount'];
  
  					$aItems[$c] = $temp;
  					$c++;
  				}
				$aCashier['remarks'] .= 'Global Discount '.$aCashier['GlobalDiscountPercent'].';';
				$aCashier['discount_percent'] = $textbox*1;
				 
				$aCashier['GlobalDiscount']='';
				$aCashier['GlobalDiscountCode']='';
				$aCashier['GlobalDiscountPercent']='';
				 
  				gset('textbox','');
				glayer('prompt.layer','<font size=5>Scan Item</font>');
				$i = showInstruction();
				glayer('instruction.layer',$i);
				glayer('plu.layer','');
				hide_layer('plu.layer');

			}

			return;
    }
    
		function insertTender($value)
		{
				global $aCashier, $aItems, $SYSCONF;
				$message = '';
				$ax	=	explode("*",$value);

				if ($aCashier['Tender'] == 1)
				{
					//1 - select Payment Type
					$tender_code = $ax[0];
					$q = "select * from tender where tender_code='$tender_code' and enable='Y'";
					$qr = @query($q);
					if (!$qr || (@pg_num_rows($qr) == 0))
					{
						//glayer('message.layer','<font size=3><b>No or Invalid Payment Types Selected</b></font>');
						$message = 'No or Invalid Payment Types Selected';
						$grid_flag = 0;
					}
					else
					{
						$temp = @qr_assoc($qr);
						$temp['type'] = 'Tender';
						$temp['stock'] = $temp['tender_code'].' '.$temp['tender'];
						$temp['tender_type'] = $temp['tender_type'];

						if (count($ax) > 1)
						{
							$cardno = $ax[1];
							if ($cardno != '')
							{
								$temp['cardno'] = $ax[1];
								$temp['barcode'] = $ax[1];
								$aCashier['Tender'] = 3;
							}
						
							if (count($ax)>2 && $ax[2]>0)
							{
								$aCashier['account'] = $ax[2];
								$aCashier['Tender'] = 4;
							}
							if (count($ax)>3 && $ax[3]>0)
							{
								$amount = $ax[3];
								if ($amount != '')
								{
									$temp['amount'] = $ax[3];
									$aCashier['Tender'] = 0;
								}
							}
						}
						else
						{
							$aCashier['Tender'] = 2;
						}
						$rItems = array_reverse($aItems);
						$rItems[] = $temp;
						$aItems = array_reverse($rItems);
						$aCashier['line_no'] = 1;
						gset('line_no',1);
					}
					//if ($form['called'] == 1) return;
				}
				elseif ($aCashier['Tender'] == 2)
				{
					//2 - Input Document No.
					$temp = $aItems[$aCashier['line_no'] -1];
					$temp['cardno'] = $value;
					$temp['barcode'] = $value;
					$ok_to_charge = 1;
					
					if ($temp['tender_type'] == 'A')
					{
						 $q = "select 	cardno, 
						 					account, 
						 					cardname, 
						 					account_id, 
						 					account_class_id, 
						 					account_status, 
						 					enable, 
						 					date_expiry, 
						 					credit_limit,
						 					branch_id
						 			 from 
						 			 		account 
						 			 where 
						 			 		cardno='".$temp['cardno']."'";
						 $qr = @query($q);
						 if (@pg_num_rows($qr) == 0)
						{
							//$message = "<font face='Verdana' size='3'><b>Credit Card No. NOT Found!!!</b></font>";
							$message = "Credit Card No. NOT Found!!!";
							$aCashier['Tender']=2;
							return $message;
						} 
						else
						{
						
							$r = @qr_object($qr);
							$temp['stock'] .= '('.$r->account.')';
							$temp['account_id'] = $r->account_id;
							$temp['account'] = substr($r->account,0,29);
							$temp['cardno'] = $r->cardno;
							$temp['account_class_id'] = $r->account_class_id;
							$temp['credit_limit'] = $r->credit_limit;

							if ($r->enable == 'N')
							{
								$message = "Account is INACTIVE. Please refer to credit section...";
								return $message;
							}
							if ($r->branch_id != $SYSCONF['BRANCH_ID'])
							{
								$message = "Account is NOT in this branch....(".lookUpTableReturnValue('x','branch','branch_id','branch',$r->branch_id);
								return $message;
							}
							if (date('Y-m-d') > $r->date_expiry) 
							{
								$message = "Account is EXPIRED. Please refer to credit section...";
								return $message;
							}
							
							include_once('../accounts/accountbalance.php');
							$acct = @customerBalance($temp['account_id']);
							
							if ($acct['overdue'] > 0)
							{
								$message = "Account has OVERDUE. Cannot Make Additional Charges...";
								return $message;
							}
							$aCashier['balance_available'] = round($temp['credit_limit'] - $acct['balance'],2);

							if ($aCashier['balance_available'] <= 0)
							{
								$message = "No more available balance. Cannot Make Additional Charges...\n Current balance: P ".number_format($acct['balance'],2)."  Available: P ".number_format($aCashier['balance_available'],2);
								return $message;
							}
								
							$temp['balance_account'] = $acct['balance'];
							$temp['balance_available'] = $aCashier['balance_available'];

							$chargeable = $aCashier['subtotal'];
							
							//--call subroutine to compute servicecharge;
							$aSC = computeServiceCharge($chargeable, $aCashier['balance_available'], $temp['account_id']);
							
							$drygood_charge = $aSC['drygood_charge'];
							$grocery_charge = $aSC['grocery_charge'];
							$drygood_servicecharge = $aSC['drygood_servicecharge'];
							$grocery_servicecharge = $aSC['grocery_servicecharge'];
							$srvc_amount = $aSC['srvc_amount'];
							$account_id = $aSC['account_id'];
//							$message = $aSC['message'];
								
							$temp['grocery_service'] = $aSC['grocery_service']; //service charge rate
							$temp['drygood_service'] = $aSC['drygood_service']; //service charge rate

							$dummy = null;
							$dummy = array();
							$dummy['barcode'] = 'SRVC';
							$dummy['stock'] = 'ServiceCharge('.$grocery_servicecharge.'/'.$drygood_servicecharge.')';
							$dummy['type'] = 'ServiceCharge';
							$dummy['readonly'] = 1;
							$dummy['account_id'] = $account_id;
				         $dummy['drygood_servicecharge'] = $drygood_servicecharge;
				         $dummy['grocery_servicecharge'] = $grocery_servicecharge;
							$dummy['amount'] = round($srvc_amount,2);

							$chargeable_amount = $grocery_charge + $drygood_charge;
							gset('textbox',round($chargeable_amount,2));
						} 
						if ($ok_to_charge == 1)
						{
							$aItems[$aCashier['line_no'] - 1] = $temp;
							$rItems = array_reverse($aItems);
							
							if ($dummy['amount'] > 0)
							{
								$array_pop = array_pop($rItems);
								$rItems[] = $dummy;
								$rItems[] = $array_pop;
							}
							$aItems = array_reverse($rItems);

							$aCashier['account_id'] = $temp['account_id'];
							$aCashier['account'] = substr($temp['account'],0,29);
							$aCashier['cardno'] = $temp['cardno'];
							$aCashier['member_name'] = substr($temp['member_name'],0,29);
							
							$aCashier['Tender']=4;
							//$message = 'Card Holder: ['.$aCashier['cardno'].'] '.$aCashier['account'].' Available:'.number_format($temp['balance_available'],2);
						}
						else
						{
							$aCashier['Tender']=2;
						}
					}  
					elseif ($temp['tender_type'] == 'R')
					{
						 //rewards checking
						$q = "select cardno, account, account_id from account where cardno='".$temp['cardno']."'";
						$qr = @query($q);
						if (@pg_num_rows($qr) == 0)
						{
		  					$message = 'Member Card No. NOT Found!!!';
  							$aCashier['Tender']=2;
        				} 
			      		else
					   	{
							$r = @qr_object($qr);
							$temp['stock'] .= '('.$r->account.')';
							$temp['account_id'] = substr($r->account_id,0,29);
							$temp['account'] = $r->account;

							$aCashier['account_id'] = $r->account_id;
							$aCashier['account'] = $r->account;
							$aCashier['cardno'] = $r->cardno;

							$q = "select 
									  sum(points_in) as points_in, 
									  sum(points_out) as points_out 
									from 
									  reward 
									where 
									  account_id='".$temp['account_id']."' and
									  status!='C'";
									  $qr = @query($q);
							$r = @qr_object($qr);
							$points_balance = $r->points_in - $r->points_out;
							$aCashier['points_balance']  = $points_balance;
									 
							if ($r->points_in < $SYSCONF['MINIMUM_POINTS'])
							{
								$aCashier['Tender'] = 1;
								$message = '<h2>Member has NOT reached Minimum Points of '.$SYSCONF['MINIMUM_POINTS'].' yet !!!</h2>';
							}
							else
							{
								$rewards_amount = $points_balance*$SYSCONF['VALUE_PER_POINT'];
								$temp['max_amount'] = $rewards_amount;
								$aItems[$aCashier['line_no'] - 1] = $temp;
				
								$message = 'Member ['.$aCashier['cardno'].' '.$aCashier['account'].
											' Points: '.$points_balance.' (P '.number_format($temp['max_amount'],2).')'."\n".
											'(Press ALT+M Again To Clear Memeber Data)';
				
							  	if ($rewards_amount > $aCashier['subtotal'])
							  	{
									gset('textbox',$aCashier['subtotal']);
								}
							  	else
							  	{
									gset('textbox',$rewards_amount);
							  	}
								$aCashier['Tender']=4;
							}
           			}
          		}
          		elseif  ($temp['tender_type'] == 'G')
          		{
					#CHECK GIFT CHECK RANGE
          			//--gift check;
          			$giftcheck = $value;
					$temp['cardno'] = $value;
          			//-- if condition is temporary, only until all old GCs are consumed or changed
          			
						$aCashier['Tender'] = 3;
						
					#CHECK IF GIFT CHECK IS AVAILABLE					
					$return_status = checkGiftCheckUnused($giftcheck);
					
					$_SESSION['giftcheck'] = $giftcheck;
					
					if (!$return_status && 0)
					{
							$message = "Gift Check Number NOT FOUND or already USED....";
							$aCashier['Tender'] = 2;
					}
					else{
						$r= @pg_fetch_object($qr);
						gset('textbox',$r->name);
						$aCashier['Tender'] = 3;
						$giftcheck_amount = $r->amount;
						
					}
					
          			/*
          			if (substr($giftcheck,0,1) != $SYSCONF['BRANCH_ID'])
          			{
							$aCashier['Tender'] = 3;
						}
						else
						{
          				if (!checkDigit9($giftcheck))
          				{
          				
								$message = "Invalid Gift Check Number....";
          					$aCashier['Tender'] = 2;
          				}
          				else
          				{
	          				$q = "select * from giftcheck where giftcheck = '$giftcheck'";
	          				$qr = @pg_query($q);
	          				if (!$qr)
								{
									galert(pg_errormessage().$q);
								}
	          				if (@pg_num_rows($qr) == 0)
	          				{
									$message = "Gift Check Number NOT found....";
	          					$aCashier['Tender'] = 2;
	          				}
	          				else
	          				{
	          					$r= @pg_fetch_object($qr);
	          					gset('textbox',$r->name);
	          					$aCashier['Tender'] = 3;
	          				}
          				}
          			}
          			*/
          		}
					else
					{
						$textbox='';
						if ($temp['tender_type'] == 'B')
						{
							$temp['bankcard_found'] = 0;
							$q = "select * 
										from 
											bankcard 
										where 
											tender_id='".$temp['tender_id']."' and 
											bankcard='".$temp['cardno']."'";
							
							$qb = @pg_query($q);
							if (@pg_num_rows($qb) > 0)
							{
								$rb = @pg_fetch_object($qb);
								$textbox = $rb->bankcard_name;
								$temp['bankcard_found'] = 1;
								$aCashier['account'] = $textbox;
							}
						}
						$aItems[$aCashier['line_no'] - 1] = $temp;
						gset('textbox',$textbox); //$aCashier['subtotal']);
						$aCashier['Tender']=3;
					}
          			//end of rewards
				}				
				elseif ($aCashier['Tender'] == 3)
				{
					//- Input Customer Name
					$aCashier['account'] = strtoupper($value);
					$aCashier['remarks'] = $value;

					$temp = $aItems[$aCashier['line_no'] -1];
					$temp['stock'] .= '('.$value.')';
					$temp['account'] = strtoupper(substr($value,0,29));
					$aItems[$aCashier['line_no'] - 1] = $temp;

					gset('textbox',$aCashier['subtotal']);
					$aCashier['Tender']=4;
				}
				elseif ($aCashier['Tender'] == 4 && $value*1 == 0)
				{				
					$message = 'Invalid Amount';
				}
				elseif ($aCashier['Tender'] == 4 && $value*1 > 10000000)
				{
					$message = 'Please Check Amount Entered.';
				}
				elseif ($aCashier['Tender'] == 4 && $value>0)
				{
					//4 - Input Payment Amount
					$temp = $aItems[$aCashier['line_no'] -1];
					$temp['amount'] = 1*$value;
					if ( !in_array($temp['tender_type'], array('C','K')) && $temp['amount'] > $aCashier['subtotal']+1)
					{
						galert("Payment ".number_format($temp['amount'],2).
										" is MORE than Due Amount ".number_format($aCashier['subtotal'],2));
					}
					else
					{
					
						if ($temp['tender_type'] == 'R' && $temp['amount'] > $temp['max_amount'])
						{
    						gset('textbox',$temp['max_amount']);
    						$message = 'Amount Exceeds Total Earned Points of P '.number_format($temp['max_amount'],2);
    						$aCashier['Tender'] = 4;
          			}
	          		elseif ($temp['tender_type'] == 'A' && round($temp['amount'],2) > round($temp['balance_available'],2))
   	       		{
      	    			galert("Amount Exceeds Available Balance (".number_format($temp['balance_available'],2).")");
    						gset('textbox', round($temp['balance_available'],2));
    						$aCashier['Tender'] = 4;
          			}
          			elseif ($temp['tender_type'] == 'A')
          			{
          				//-- recompute service charge incase amount paid is modified 
							//--call subroutine to compute servicecharge;
							$chargeable = $temp['amount'];
							$aSC = computeServiceCharge($chargeable, $chargeable , $temp['account_id']);
							
							$drygood_charge = $aSC['drygood_charge'];
							$grocery_charge = $aSC['grocery_charge'];
							$drygood_servicecharge = $aSC['drygood_servicecharge'];
							$grocery_servicecharge = $aSC['grocery_servicecharge'];
							$srvc_amount = $aSC['srvc_amount'];
							$message = $aSC['message'];
											
							$xt=0;
							$dummy = null;
							$dummy = array();
							foreach ($aItems as $xtemp)
							{
								if ($xtemp['barcode'] == 'SRVC' && $xtemp['type'] == 'ServiceCharge' && $xtemp['account_id'] == $temp['account_id'])
								{
									$dummy = $xtemp;
									$dummy['barcode'] = 'SRVC';
									$dummy['stock'] = 'ServiceCharge('.$grocery_servicecharge.'/'.$drygood_servicecharge.')';
									$dummy['readonly'] = 1;
						         $dummy['drygood_servicecharge'] = $drygood_service;
						         $dummy['grocery_servicecharge'] = $grocery_servicecharge;
									$dummy['amount'] = round($srvc_amount,2);
									
									$aItems[$xt] = $dummy;
									break;
								}
								$xt++;
							}
							$aCashier['Tender'] = 0;
							glayer('prompt.layer','<font size=5>Scan Item</font>');

          			}
	          		else
   	       		{
							$aCashier['Tender'] = 0;
							glayer('prompt.layer','<font size=5>Scan Item</font>');
			   	   }
						$aItems[$aCashier['line_no'] - 1] = $temp;
					
					}
				}				
				if ($aCashier['Tender']>0 && $aCashier['Tender']<3&& $temp['tender_type'] == 'C')
				{
					$aCashier['Tender'] = 3;
				}
				if ($aCashier['Tender'] == 0)
				{
					glayer('prompt.layer','<font size=5>Scan Item</font>');
				}
				if ($aCashier['Tender'] == 1)
				{
					glayer('prompt.layer','<font size=5>Select Payment Type</font>');
				}
				elseif ($aCashier['Tender'] == 2)
				{
					glayer('prompt.layer','<font size=5>Enter Card/Document No.</font>');
				}
				elseif ($aCashier['Tender'] == 3)
				{
					glayer('prompt.layer','<font size=5>Enter Customer Name</font>');
				}
				elseif ($aCashier['Tender'] == 4)
				{
					glayer('prompt.layer','<font size=5>Enter Amount</font>');
				}
				return $message;
		}


    $xajax->registerFunction('search');
    function search($form) 
		{
			global $aItems, $aCashier, $SYSCONF, $ADMIN;
		
			if (!session_is_registered('ADMIN'))
			{
				galert('User session has expired. Please Log-In Again...');
				$a = "document.getElementById('f1').action='?p=logout';document.getElementById('f1').submit()";
				gscript($a);
				return done();
			}
			
			$value  = $form['textbox'];
			if ($value == '') 
			{
				return done();
			}
				
			$aCashier['line_no'] = $form['line_no'];
			$line_no = $form['line_no'];

			$grid_flag = 1;
			$empty_textbox = 1;

			if ($aCashier['Tender'] > 0)
			{
				$msg = insertTender($value);
				if ($message != '')
				{
					$grid_flag = 1;
    				glayer('message.layer', $message);
        		}
				if ($aCashier['Tender'] == '4')
				{
					$empty_textbox = 0;
				}
				if ($aCashier['Tender'] == '3' && $aCashier['account'] != '')
				{
					gset('textbox', $aCashier['account']);
					$empty_textbox = 0;
				}
				if ($aCashier['Tender'] == '2' && $aCashier['cardno'] != '')
				{
					gset('textbox', $aCashier['cardno']);
					$empty_textbox = 0;
				}
			}
			elseif ($aCashier['GlobalDiscount'] > 0)
			{
				insertGlobalDiscount($value);
				$message = $aCashier['GlobalDiscount'];
				$empty_textbox = 0;
			}
			elseif ($aCashier['LineDiscount'] > 0)
			{
				insertLineDiscount($value);
			}
			else
			{

				// Scan Item
				checkPayment();

				if ($form['plu_pop_up'] == 1)
				{
					$mark = $form['mark'];
					$q = "select barcode from stock where stock_id='".$mark[$value-1]."'";
					$qr = @query($q);
					$r = @qr_object($qr);
					$value = $r->barcode;
					glayer('plu.layer','');
					hide_layer('plu.layer');
				}
				
				$use_medium_price=0; //by dozen price (qty coded with **)
				if ($SYSCONF['USE_MEDIUM_PRICE']=='Y')
				{
  					$ax	=	explode("**",$value);
  					if (count($ax)>1)
  					{
  						$use_medium_price=1;
  					} 
			   } 
			   if ($use_medium_price==0)
			   {
  					$ax	=	explode("*",$value);
  				}	
				if (count($ax) > 1)
				{
					$qty = 1*$ax[0];
					$searchitem = $ax[count($ax)-1];
					if ($qty == 0) $qty =1;
				}
				else
				{
					$qty=1;
					$searchitem = $value;
				}
				$fraction_qty='';
				
				if ($searchitem == '')
				{
					galert('No Item Search Specified...');
					return done();
				}
				$searchitem = readBarcode($searchitem);	
				
				$plu = plu($searchitem, $qty, $use_medium_price);

				if ($plu)
				{
					if ($plu == '2')
					{			
						//-- Item not found. Check If Reward Member
						$msg = checkRewardMember($searchitem);
						if ($msg)
						{
							gset('textbox','');
							//$grid_flag = 0;				
							$i = showInstruction();
							glayer('instruction.layer',$i);
							//galert($msg);
							renewLineItems();
						}
						else
						{
								//galert("Item NOT Found!");
								alertNoItemFound();
						}
					}
					else
					{
				
						if ($aCashier['edit'] == '10000') //'1' - TO allow overwrite
						{
							$aItems[$aCashier['line_no'] -1] = $r;
						}
						else
						{
							/**
							 * Items are appended here
							 */
							$rItems = array_reverse($aItems);
							$rItems[] = $plu;
							$aItems = array_reverse($rItems);
							$aCashier['line_no'] = 1;
						}	
						$aCashier['edit'] = 0;
					}
				}
				//-- end
			}
			
			//--display	
			if ($grid_flag == 1)
			{
				grid($aItems);
			}
			if ($empty_textbox == 1)
			{	
				gset('textbox', '');
			}	
			if ($msg != '')
			{
				galert($msg);
			}
		  
		  $aCashier['line_no'] =1 ;
		  gset('line_no',1);
        return done();
    }
		
    $xajax->registerFunction('onload');
    function onload() 
		{
				global $aItems, $aCashier, $ADMIN, $SYSCONF;
				
				if ($SYSCONF['DATE_TRANSACTION']!=''  && $SYSCONF['DATE_TRANSACTION']!='--' && $SYSCONF['DATE_TRANSACTION']!='//' && $SYSCONF['DATE_TRANSACTION'] < date('Y-m-d'))
				{
					galert("         INVALID TRANSACTION DATE (".ymd2mdy($SYSCONF['DATE_TRANSACTION']).") FOR THIS TERMINAL.  \n    PLEASE INFORM SYSTEMS ADMINISTRATOR TO RE-CONFIGURE TERMINAL.");
					$script = "window.location='../?p=logout'";
					gscript($script);
					return done();
				}
				if ($SYSCONF['DATE_TRANSACTION']!=''  && $SYSCONF['DATE_TRANSACTION']!='--' && $SYSCONF['DATE_TRANSACTION']!='//' && $SYSCONF['DATE_TRANSACTION']  > date('Y-m-d'))
				{
					galert("NOTE: ".$ADMIN['name'].", You Are Using a POST Dated TERMINAL. \n    Transaction Date is : ".ymd2mdy($SYSCONF['DATE_TRANSACTION']).". \n        Press Ok to continue");
				}
				
				grid($aItems);
				return done();
		}

        //$xajax->debugOn();
        $xajax->processRequests();
		
		if ($SYSCONF['CASHIERCOLOR'] == '') $BODYCOLOR='#CCCCCC';
		else $BODYCOLOR=$SYSCONF['CASHIERCOLOR'];
		
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html><head>
<title>Point of Sale</title>
<?php $xajax->printJavascript('../'); // output	the xajax javascript. This must	be called between the head tags	?>

<script type="text/javascript" src="../js/jquery-1.3.2.min.js"></script>
<script type="text/javascript">
	jQuery.noConflict();
</script>

<script language='javascript'>

var quantity_action = "enter";

function wait($message) {
	xajax.$('message.layer').style.display = 'block';
	xajax.$('message.layer').innerHTML     = $message+"<br><img src='wait.gif'>";
}

function blank_layer($layer) {
		xajax.$($layer).innerHTML       = "";
}

function hide_layer($layer)  {
		xajax.$($layer).style.display = 'none';
}
function selectLine($v)
{
//	document.f1.textbox.value = $v;
	xajax_selectLine($v);
	return;
}
function focusIt($e)
{
	document.getElementById($e).focus();
}

var	isNS = (navigator.appName	== "Netscape") ? 1 : 0;
var	EnableRightClick = 0;

if(isNS) 
document.captureEvents(Event.MOUSEDOWN||Event.MOUSEUP);
document.onhelp=function(){event.returnValue=false};


keys = new Array();
keys["f112"] = 'f1';
keys["f113"] = 'f2';
keys["f114"] = 'f3';
keys["f115"] = 'f4';
keys["f116"] = 'f5';
keys["f117"] = 'f6';
keys["f118"] = 'f7';
keys["f119"] = 'f8';
keys["f120"] = 'f9';
keys["f121"] = 'f10';
keys["f122"] = 'f11';
keys["f123"] = 'f12';
keys["a38"] = 'a38';
keys["a40"] = 'a40';

function mischandler(){
	if(EnableRightClick==1){ return	true;	}
	else {return false;	}
}
function mousehandler(e){
	if(EnableRightClick==1){ return	true;	}
	var	myevent	=	(isNS) ? e : event;
	var	eventbutton	=	(isNS) ? myevent.which : myevent.button;
	if((eventbutton==2)||(eventbutton==3)) return	false;
}
function altkey($key)
{
	//alert($key);
	if ($key == 'R' || $key == 'Z' || $key=='B' ||$key=='C' || $key == 'M' || $key == 'G' || $key == 'D' || $key=='E' || $key == "H" || $key == "I" ) //H FOR ECO BAG
	{
		xajax_fkey($key,xajax.getFormValues('f1'));
		focusIt('textbox');
	}
	else if ($key == 'S')
	{
		if (document.getElementById('grid.layer').innerHTML.indexOf("rightarrow.jpg") > 0)
		{
			alert('Cannot Perform Reading. There is Pending Transasction.');
			focusIt('textbox');
		}
		else
		{
			wait('Printing...');
			xajax_fkey($key,xajax.getFormValues('f1'));

			if (confirm('Do you wish to Close Session and Logout (Z-Reading)'))
			{
				xajax_fkey('Z',xajax.getFormValues('f1'));
				if (confirm('Do you wish to Logout'))
				{
					window.location="../?p=logout";
				}
			}
			else
			{
				focusIt('textbox');
			}
			//return false;
		}
	}
	else if ($key == 'V' && document.getElementById('textbox').value!='')
	{
		if (confirm('Are you sure to VOID Docket# '+document.getElementById('textbox').value))
		{
			xajax_fkey($key,xajax.getFormValues('f1'));
			focusIt('textbox');
		}
	}
	else if ($key == 'L')
	{

		var line_no = document.getElementById('line_no').value;
//		if (document.getElementById('grid.layer').innerHTML.indexOf("rightarrow.jpg") > 0)

		if (line_no != '')
		{
			alert('Cannot Logout. There is Pending Transasction.');
		}
		else
		{
			window.location="../?p=logout";
			return false;
		}

	}

}


function keyhandler(e) 
{
	var myevent = (isNS) ? e : window.event;
	var mycode=myevent.keyCode
	var focus_id=document.getElementById('line_no').value;

	//alert(e.keyCode);

	if(e.altKey && e.keyCode == 65){
		//alert("APPLY ECO BAG DISCOUNT");	
		xajax_fkey('H',xajax.getFormValues('f1'));
	}
	
	if(e.altKey && e.keyCode == 81){
		//alert("REMOVE ECO BAG DISCOUNT");
		xajax_fkey('I',xajax.getFormValues('f1'));
	}

	if(e.keyCode == 27 && e.shiftKey){
		document.getElementById("dialog").style.display = "none";
		document.getElementById("textbox").disabled = false;
		document.getElementById("textbox").focus();	
	}		
	
	
	
	if (myevent.keyCode==96)
	{
   	    EnableRightClick = 1;
	}
	else if (mycode == 27)
	{
		xajax_fkey(mycode,xajax.getFormValues('f1'));
 		focusIt('textbox');
		return false;
	}
	else if (mycode == 13 && jQuery("#textbox").is(":focus") ) 
	{
		/*start of enter key code here*/
		if ( jQuery("#promptDialog").css("display") == "block" ) {
			handleEnter();
			return;
		}



		if (document.getElementById('textbox').value != '')
		{
			var textbox_value = document.getElementById('textbox').value;

			
			/*count the number of asterisk*/			
			if ( textbox_value.indexOf("*") > 0 ) {
			
				var count = (textbox_value.match(/\*/g)).length;

				<?
				/*check if terminal allow change quantity*/
				$ip = $SYSCONF['IP'];
				//$ip = "10.0.0.113";

				$rs = pg_query("
					select 
						* 
					from 
						terminal 
					where 
						ip = '".$ip."' 
					and definition = 'ALLOW_MUL_QTY'
				");
				

				if ( pg_num_rows($rs) > 0 ) {
					$r = pg_fetch_assoc($rs);
					$allow_mul_qty = ( $r['value'] == "Y" ) ? 1 : 0;
				} else {
					/*do not allow mul qty*/
					$allow_mul_qty = 0;
				}				

				echo "var allow_mul_qty = $allow_mul_qty";
				?>

				<?
				/* check if there is a from date and to date involved in mul_qty */				

				
				$now = lib::now();
				$rs = pg_query("
					select 
						*
					from
						mul_qty
					where
						from_date >= '$now'
					and to_date <= '$now'
				");
				
				if ( pg_num_rows($rs) > 0 ) {
					$global_allow_mul_qty = 1;	
				} else {
					$global_allow_mul_qty = 0;	
				}				

				echo "var global_allow_mul_qty = $global_allow_mul_qty";
				?>
				
				/*console.log(!allow_mul_qty);
				console.log(!global_allow_mul_qty);*/			
				
				if ( count == 1 ){
					if ( !global_allow_mul_qty ){
						if ( !allow_mul_qty ) {

							if ( jQuery('#textbox').is(':focus') ){	
								quantity_action = "enter";
								openPromptDialog();
								return false;
							}
							
						} else {
							document.getElementById('Ok').click();						
						}	
					} else {
						document.getElementById('Ok').click();
					}					
				}
				
				return false;		
			}
		

			/*if ( jQuery("#promptDialogTextBox").is(":focus") ){
				return false;
			}*/
		
			document.getElementById('Ok').click();
		} else {
			xajax_fkey(mycode,xajax.getFormValues('f1'));
		}	

 		focusIt('textbox');
		return false;

		/*end of enter key code */
	}
	else if (mycode == 38 && document.getElementById('textbox').value == '')
	{
		//scroll up
		if (focus_id == '')
		{
			focus_id =1;
		}
		else
		{
			previous_line = focus_id;
			focus_fld = 'a'+focus_id;

			document.getElementById(focus_fld).style.background='';
			document.getElementById(focus_fld).style.color='';
			
			focus_id--;
			if (focus_id<1) focus_id=1;
		}
		//new
		focus_fld = 'a'+focus_id
		if (document.getElementById(focus_fld) == null)
		{
			focus_id--
			focus_fld = 'a'+focus_id
		}
	
		document.getElementById(focus_fld).style.background='#000CCC';
		document.getElementById(focus_fld).style.color='#FFFFFF';
		document.getElementById(focus_fld).scrollIntoView(false);
		document.getElementById('line_no').value = focus_id;
	}
	else if (mycode == 40 && document.getElementById('textbox').value == '')
	{
		//scroll down
		if (focus_id == '')
		{
			focus_id =1;
		}
		else
		{
			previous_line = focus_id;
			focus_fld = 'a'+focus_id;
			document.getElementById(focus_fld).style.color='';
			document.getElementById(focus_fld).style.background='';
			focus_id++;
		}
		//new
		focus_fld = 'a'+focus_id
		if (document.getElementById(focus_fld) == null)
		{
			focus_id--
			focus_fld = 'a'+focus_id
		}
		document.getElementById(focus_fld).style.background='#000CCC';
		document.getElementById(focus_fld).style.color='#FFFFFF';
		document.getElementById('line_no').value = focus_id;
		document.getElementById(focus_fld).scrollIntoView(false);
	}
	else if(mycode == 40 && document.getElementById('textbox').value != '')
	{
		document.getElementById('Ok').click();
 		focusIt('textbox');
		return false;
	}
	else if(keys["f"+myevent.keyCode])
	{
	
		if (mycode == 113)
		{
			document.getElementById('textbox').value='';
		}
		else if (mycode == 114 || mycode == 123)
		{
			if (confirm('Are you sure to DELETE Item?'))
			{
				mycode=114;
				xajax_fkey(mycode,xajax.getFormValues('f1'));
			}	
		}		
		else if (mycode == 116  && document.getElementById('textbox').value=='')
		{
			if (confirm('RESTORE ORIGINAL PRICE?'))
			{
				xajax_fkey(mycode,xajax.getFormValues('f1'));
			}	
		}		
		else if (mycode == 121)
		{
			wait('Printing...');
			xajax_fkey(mycode,xajax.getFormValues('f1'));
		}		
		else
		{
			
			if ( mycode == 115 ) {

				// xajax_fkey(mycode,xajax.getFormValues('f1'));
				// return;

				if (document.getElementById('textbox').value != '') {					
					var textbox_value = document.getElementById('textbox').value;											

					<?
					/*check if terminal allow change quantity*/
					$ip = $SYSCONF['IP'];
					//$ip = "10.0.0.113";

					$rs = pg_query("
						select 
							* 
						from 
							terminal 
						where 
							ip = '".$ip."' 
						and definition = 'ALLOW_MUL_QTY'
					");
					

					if ( pg_num_rows($rs) > 0 ) {
						$r = pg_fetch_assoc($rs);
						$allow_mul_qty = ( $r['value'] == "Y" ) ? 1 : 0;
					} else {
						/*do not allow mul qty*/
						$allow_mul_qty = 0;
					}				

					echo "var allow_mul_qty = $allow_mul_qty";
					?>

					<?
					/* check if there is a from date and to date involved in mul_qty */				

					
					$now = lib::now();
					$rs = pg_query("
						select 
							*
						from
							mul_qty
						where
							from_date >= '$now'
						and to_date <= '$now'
					");
					
					if ( pg_num_rows($rs) > 0 ) {
						$global_allow_mul_qty = 1;	
					} else {
						$global_allow_mul_qty = 0;	
					}				

					echo "var global_allow_mul_qty = $global_allow_mul_qty";
					?>
					
					/*console.log(!allow_mul_qty);
					console.log(!global_allow_mul_qty);*/			
					
					
					if ( !global_allow_mul_qty ){
						if ( !allow_mul_qty ) {

							if ( jQuery('#textbox').is(':focus') ){								
								quantity_action = "f4";
								openPromptDialog();
								return false;														
							}												
							
						} else {
							xajax_fkey(mycode,xajax.getFormValues('f1'));
						}	
					} else {						
						xajax_fkey(mycode,xajax.getFormValues('f1'));
					}					
				
					
					return false;		
								
				} else {
					xajax_fkey(mycode,xajax.getFormValues('f1'));
				}	

		 		//focusIt('textbox');
				return false;

			}

			xajax_fkey(mycode,xajax.getFormValues('f1'));
		}		
 		focusIt('textbox');
 		return false;
	}	

	return;

}
function vOnLoad()
{
	xajax_onload();
	return;
}
document.oncontextmenu = mischandler;
document.onmousedown = mousehandler;
document.onmouseup = mousehandler;
document.onkeydown = keyhandler;
			  
</script>
<STYLE type=text/css>
<!--
  .initial { background-color: #FFFFFF; color:#000000 }
  .normal { background-color: #FFFFFF ;  color:#000000 }
  .highlight { background-color: #333333; color:#FFFFFF; font-weight:bold }
  .hi_initial { background-color: #F3DCD6; color:#000000; font-weight:bold  }
  .hi_normal { background-color: #F3DCD6 ;  color:#000000 ; font-weight:bold }
  .hi_highlight { background-color: #880000; color:#FFFFFF; font-weight:bold}

  A:link  {text-decoration: none; color: #3D5A84;}

 .gridRow
 {
 	white-space:pre;
// 	font-family:monospace;
 	font-weight:bold;
 	font-size:16px;
 	
 } 
/* .gridRow:hover
 {
 	background-color:#0061D7;
 	color:#FFFFFF;
 }
*/  

-->
</style>
</head>
<body bgcolor="<?= $BODYCOLOR;?>" style="margin:0" onfocus="document.getElementById('textbox').focus()"; onLoad="vOnLoad()";>
<form id="f1" name="f1" style="margin:0px;padding:0px">
  <table width="99%" height="99%" border="0"	cellpadding="0"	cellspacing="0" align="center">
    <tr	height="1%"> 
      <td width="79%" height="1%" bgcolor="#FFFFFF"> <strong>
      <font color="#CC3300" size="5" face="Times New Roman, Times, serif"> 
        &nbsp;<img src="../graphics/logo.jpg"  height="50" /> &nbsp; 
        <?= ($_SERVER['REMOTE_ADDR'] == '127.0.0.1' ? 'O-F-F-L-I-N-E  P-O-S' : ($SYSCONF['DATABASE'] != '' ? 'NOTICE: DB = '.$SYSCONF['DATABASE'] : ''));?>
        </font></strong></td>
      <td	width="21%"	align="center" bgcolor="<?= $BODYCOLOR;?>"> <font size="2"> 
        <?=	$ADMIN['username'];?>
       <br />
        <?
		$cdate =	date('F	d, Y');
		if ($SYSCONF['DATE_TRANSACTION'] > date('Y-m-d'))
		{
			$cdate = ymd2mdy($SYSCONF['DATE_TRANSACTION']);
		}
		echo $cdate;
		?></font><br><font size="1"> 
        [ <a accesskey='L' href="javascript: altkey('L')" id='logout'>Logout</a> ] </font></td>
    </tr>
    <tr> 
      <td valign="top" width="79%"> <table width="100%"	height="100%"	border="0" cellspacing="0" cellpadding="0" >
          <tr height="5%"> 
            <td	colspan="5" bgcolor="<?= $BODYCOLOR;?>"><div id="prompt.layer"><font size="5"> 
                Scan Item</font></div>
              <input name="textbox" type="text" id="textbox" style="font-size:Large; font-family: 'Times New Roman'; font-weight:bold" value="<?= $item['textbox'];?>" size="40" onfocus="document.getElementById('Ok').disabled=0"/> 
              <input type="button" name="p1" value="Ok"	id='Ok' style="font-size:20; font-weight: bold; font-family: 'Times New Roman'" onclick="xajax_search(xajax.getFormValues('f1')); focusIt('textbox');" /> 
              <?

			  if ($aCashier['return'] == 'RETURN')
			  {
			  	echo " Return From Receipt#:[".$aCashier['return_invoice'].'] Scan Item ...';
			  }
			  ?>
            </td>
          </tr>
          <tr	height="2%" bgcolor="#C0C0C0"> 
            <td	width="8%" align="center" height="4"><strong><font size="2"	face="Verdana, Arial,	Helvetica, sans-serif">Qty</font></strong></td>
            <td	width="18%" align="center" height="4"><strong><font	size="2" face="Verdana,	Arial, Helvetica,	sans-serif"> 
              BarCode</font></strong></td>
            <td	width="45%" height="12"><strong><font	size="2" face="Verdana,	Arial, Helvetica,	sans-serif">Item 
              Description</font></strong></td>
            <td	width="10%"	align="center" height="4"><strong><font size="2"	face="Verdana, Arial,	Helvetica, sans-serif">Price</font></strong></td>
            <td	width="14%"	align="center" height="4"><strong><font size="2"	face="Verdana, Arial,	Helvetica, sans-serif">Amount</font></strong></td>
          </tr>
          <tr height="70%"	 valign="top"> 
            <td	colspan="5"	 valign="top"></td>
          </tr>
      </table></td>
      <td width="21%" rowspan="2" align="center" valign="top"> 
      	<div id="invoice.layer" align="left"><font size="2">No:<b> 
          <?=$SYSCONF['TERMINAL'].'-'.$aCashier['invoice'];?>
          </b></font>
          <hr size='1'/>
        </div>
		<div id="instruction.layer" style="position:relative; width:100%; height:405px; z-index:2; overflow: auto;"> 
          <?= showInstruction();?>
        </div>
        <!-- <div id="itemcount.layer"></div>-->
      </td>
    </tr>
    <tr  valign="top"> 
      <td valign="top">
		<div id="grid.layer" style="position:relative; width:100%; height:300px; z-index:2; overflow: auto; left: 0; top: 0; background-color: #FFFFFF; layer-background-color: #FFFFFF; border: 1px none #000000;"></div>
		<div id='subtotal.layer' style="text-align:right"><font size='+5'>Amount Due...0.00</font></div>
	  </td>
    </tr>
  </table>
  <input type="hidden" name="line_no" id="line_no" value="">
  <div id="plu.layer" style="position:absolute; display:none; top:10%; left:40%; z-index: 100; overflow: auto; width: 50%; background-color: <?=$BODYCOLOR;?>; layer-background-color: #CCCCCC; border: 1px none #000000; height: 80%;"></div>
</form>                        
<div id="message.layer" style="position:absolute; border: 1px solid #CCCC00; display:none;text-align:center" align="top"></div>	  
<div id='wait.layer' style='position:absolute; display:none;left:40%;top:50%; z-index: 10' onClick="this.style.display='none'">Search...<br><img src='wait.gif'></div>
<script>document.getElementById('textbox').focus()</script>
  </body>
</html>  

<div id="dialog" style="padding:0px; display:none; z-index:9999; position:fixed;
    top: 50%;
    left: 50%;
    width:30em;
    height:10em;
    margin-top: -9em; 
    margin-left: -15em; 
    border: 1px solid #ccc;
    background-color: #f3f3f3;" >
	<p style="padding:40px; 100px; text-align:center; vertical-align:middle; font-weight:bolder; font-family:Arial, Helvetica, sans-serif;">
    	<span id="dialogInstruction">ITEM NOT FOUND</span><br />
        <div style="font-size:10px; text-align:center; font-family:Arial, Helvetica, sans-serif;">Press SHIFT + ESC to REMOVE</div>
        <input type="text" id="focus_on" style="border:1px solid #f3f3f3; color:#f3f3f3; background-color:#f3f3f3;"/>
    </p>
</div>
<script type="text/javascript">
	function openDialog(instruction){

		if ( instruction == null ) {			
			jQuery('#dialogInstruction').html("ITEM NOT FOUND");
		} else {			
			jQuery('#dialogInstruction').html(instruction);
		}

		document.getElementById("dialog").style.display = "block";
		document.getElementById("textbox").disabled = true;
		document.getElementById("focus_on").focus();	
	}	
</script>
<style type="text/css">
	.dumbBoxWrap { /* The div that shows/hides. */ display:none; /* starts out hidden */ z-index:40001; /* High z-index to ensure it appears above all content */ } 
	.dumbBoxOverlay { /* Shades out background when selector is active */ position:fixed; width:100%; height:100%; background-color:black; opacity:.5; /* Sets opacity so it's partly transparent */ -ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity=50)"; /* IE transparency */ filter:alpha(opacity=50); /* More IE transparency */ z-index:40001; }
	.vertical-offset { /* Fixed position to provide the vertical offset */ position:fixed; top:30%; width:100%; z-index:40002; /* ensures box appears above overlay */ }
	.dumbBox { /* The actual box, centered in the fixed-position div */ width:405px; /* Whatever width you want the box to be */ position:relative; margin:0 auto; /* Everything below is just visual styling */ background-color:white; padding:10px; border:1px solid black; }

</style>

<div class="dumbBoxWrap" id='promptDialog'>
    <div class="dumbBoxOverlay">
        &nbsp;
    </div>

    <div class="vertical-offset">
        <div class="dumbBox">
            <span id="promptDialogInstruction">Enter Supervisor Code</span>
            <input type='text' class='' id='promptDialogTextBox' >
        </div>
    </div>
</div>

<script type="text/javascript">
	
	function openPromptDialog(){		
		jQuery('#promptDialog').show();
		focusIt('promptDialogTextBox');		
		jQuery('#promptDialogTextBox').unbind();
		jQuery('#promptDialogTextBox').focus();

		/*setTimeout(function(){
			jQuery('#promptDialogTextBox').keyup(function(e){
				if ( e.which == 13 ){					
					handleEnter();
				}
			});	
		},100);*/
		
	}

	function closePromptDialog(){
		//document.getElementById('promptDialog').style.display="none";	
		jQuery('#promptDialog').hide();
	}

	function handleEnter(){		
		if ( jQuery('#promptDialogTextBox').val() == '' )  return false;
		
		if( jQuery('#textbox').val() !='' ){ 			
			if ( document.getElementById('promptDialogInstruction').innerHTML == "Enter Supervisor Code" ){
				username = document.getElementById('promptDialogTextBox').value;
				document.getElementById('promptDialogTextBox').value = "";
				document.getElementById('promptDialogInstruction').innerHTML = "Enter Password";
				document.getElementById('promptDialogTextBox').type = "password";
			} else if ( document.getElementById('promptDialogInstruction').innerHTML == "Enter Password" ){
				password = document.getElementById('promptDialogTextBox').value;
				document.getElementById('promptDialogTextBox').value = "";
				document.getElementById('promptDialogInstruction').innerHTML = "Enter Supervisor Code";
				document.getElementById('promptDialogTextBox').type = "text";
				closePromptDialog();
				//document.getElementById('Ok').click();
				
				var form_data = {
					username : username,
					password : password
				};				

				checkCredentials(form_data);
			}
		}
	}

	function checkCredentials(form_data){

		form_data['terminal'] = '<?= $SYSCONF['TERMINAL'] ?>';
		
		jQuery.post("cashier.ajax.php", 
			{ 
				'action' : "checkCredentials",
				'username' : form_data.username,
				'password' : form_data.password
			},
			function(data){		    
				
	    		/*console.log(form_data);
	    		alert(data);*/

		    	if ( data == 0 ) {
		    		openDialog("Invalid Username or Password");
		    		jQuery('#textbox').val('');		    		
		    	} else {		    		
		    		if ( quantity_action == "enter" ) {
		    			document.getElementById('Ok').click();
		    		} else if ( quantity_action == "f4" ) {		    			
		    			xajax_fkey(115,xajax.getFormValues('f1'));
		    		}	    		
		    		
		    		focusIt('textbox');		
		    	}
		});

	}

</script>