<?php
	session_start();
    require_once("../xajax.inc.php");
    $xajax = new xajax();
    $g     = "";

    $g->objResponse = new xajaxResponse();

		if ($p == 'logout' || $SYSCONF['IP']== ''  || $ADMIN['sessionid']=='')
		{
			
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

		function galert($m)
		{
			global $g;
			$g->objResponse->addAlert($m);
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
				$aCashier['time'] = $time;
				$aCashier['date'] = $date;
				
				
				$q = "insert into	$sales_header (invoice, date,time,status,gross_amount, 	
									discount_percent,discount_amount, discount_id,  discount_card, 
                  					net_amount,vat_sales, total_tax, 
									service_charge, ip, terminal, admin_id, remarks,
									item_lines, units, account_id)
								values
									('".$aCashier['invoice']."','$date','$time','S','".$aCashier['gross_amount']."',
									'".$aCashier['discount_percent']."','".$aCashier['discount_amount']."',
                  			'".$aCashier['discount_id']."','".$aCashier['discount_card']."',	
									'".$aCashier['net_amount']."', '".$aCashier['vat_sales']."', 
									'".$aCashier['total_tax']."', '".$aCashier['service_charge']."', 
									'".$_SERVER['REMOTE_ADDR']."', '".$SYSCONF['TERMINAL']."', 
									'".$ADMIN['admin_id']."','".$aCashier['remarks']."',
									'".$aCashier['item_lines']."','".$aCashier['item_qty']."', '".$aCashier['account_id']."')"; 

				$qr	=	@query($q);
				if ($qr	&& @pg_affected_rows($qr)>0)
				{
					$hid = @db_insert_id('sales_header');
					$aCashier['sales_header_id'] = $hid;
		
					// Collate all similar items
					//	$aCashier['grocery_amount'] = $aCashier['dry_good_amount'] = $grocery_amount = $dry_good_amount = 0;
					$account_amount = 0;
					$newarray = null;
					$newarray = array();
					$cctr=0;
					foreach ($aItems as $temp)
					{
						$cctr++;
						if ($temp['amount'] == '0')
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
					$reward_amount = 0;
    			   $reward_grocery = $reward_dry = $reward_total = $reward_amount_out= $reward_points_out = 0;
					$grocery_balance = $aCashier['grocery_amount'];
					$dry_good_balance = $aCashier['dry_good_amount'];

					$c=0;
					foreach	($aItems as	$temp)
					{
						
						if ($temp['type']	== 'Tender')
						{
							if ($invoice_balance > $temp['amount'])
							{
								$invoice_balance -= $temp['amount'];
								$reward_amount = $temp['amount'];
							}
							else
							{
								$reward_amount = $invoice_balance;
								$invoice_balance = 0;
							}
							//galert("aid ".$aCashier['account_id']." tt ".$temp['tender_type']." fnd ".$temp['bankcard_found']);
							if ($aCashier['account_id'] != '')
							{
								if (in_array($temp['tender_type'], array('C','K')) && $reward_amount>0)
								{
									//-cash and check rewards calculations
									if ($grocery_balance >= $reward_amount)
									{
										$reward_grocery += round($reward_amount/$SYSCONF['CASH_GRC_POINT'],2);
										$grocery_balance -= $reward_amount;
										$reward_amount = 0;
										$m .= "; ga ".$c.' - '.round($reward_amount/$SYSCONF['CASH_GRC_POINT'],2);
									}	
									elseif ($grocery_balance > 0)
									{
										$reward_grocery += round($grocery_balance/$SYSCONF['CASH_GRC_POINT'],2);
										$reward_amount -= $grocery_balance;
										$grocery_balance = 0;
										$m .= "; gb ".$c.' - '.round($grocery_balance/$SYSCONF['CASH_GRC_POINT'],2);
									}
									$reward_dry += round($reward_amount/$SYSCONF['CASH_DRY_POINT'],2);
									$dry_good_balance -= $reward_amount;
									if ($reward_amount != 0)
									{
										$m .= "; db".$c.' - '.intval($grocery_balance/$SYSCONF['CASH_DRY_POINT']);
									}	
								}
								elseif (in_array($temp['tender_type'], array('B')) && $reward_amount>0)
								{
									//-bankcards rewards calculations
									if ($grocery_balance >= $reward_amount)
									{
										$reward_grocery += round($reward_amount/$SYSCONF['BANK_GRC_POINT'],2);
										$grocery_balance -= $reward_amount;
										$reward_amount = 0;
									}	
									elseif ($grocery_balance > 0)
									{
										$reward_grocery += round($grocery_balance/$SYSCONF['BANK_GRC_POINT'],10);
										$reward_amount -= $grocery_balance;
										$grocery_balance = 0;
									}
									$reward_dry += round($reward_amount/$SYSCONF['BANK_DRY_POINT'],2);
									$dry_good_balance -= $reward_amount;

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
							}
							elseif ($temp['tender_type'] =='B'  && $temp['bankcard_found']*1 == '0' && $temp['account']!='' && substr($temp['account'],0,1)!='*')
							{
										$q = "insert into bankcard (bankcard, bankcard_name, tender_id)
														values ('".$temp['cardno']."',
																	'".$temp['account']."',
																	'".$temp['tender_id']."')";
										@pg_query($q);
							}
							$reward_total = $reward_dry + $reward_grocery;
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
								if ($temp['amount'] > 1000000)
								{
									$aCashier['sales_header_id'] = '';
									galert("Please Check Tender Amount --> ".$temp['amount']);
									return done();
								}

								$q = "insert into	$sales_tender (sales_header_id, tender_id,	
												account_id,	account, cardno, carddate, amount, service_charge, remark)
										values ('".$aCashier['sales_header_id']."',	'".$temp['tender_id']."', '".$temp['account_id']."',
												'".addslashes(substr($temp['account'],0,29))."',	'".substr($temp['cardno'],0,24)."', '".$temp['carddate']."',	
												'".$temp['amount']."', '".$temp['service_charge']."',	'".$temp['remark']."')";
												
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
								  break;
								}
							}
	
						}	
						else
						{
							if ($temp['sales_detail_id'] ==	'')
							{
								//checkZeroFields('sales_detail');
								$cf = array('stock_id', 'qty',	'price1','price', 'cdisc','sdisc', 
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
											price, cdisc, sdisc, discount, amount, tax)
									values 
										('".$aCashier['sales_header_id']."','".$temp['stock_id']."',
						                    '".$temp['barcode']."','".$temp['fraction']."', '".$temp['qty']."',
											'".$temp['price1']."', '".$temp['price']."', '".$temp['cdisc']."', '".$temp['sdisc']."',
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
								  $message .= 'Unable To Insert Into Sales Detail on BARCODE ['.$temp['barcode']."] \n".db_error().$q;
								  break;
								}
												
							}
							
						}
						$c++;
					}

				}
				else
				{
					$commit	= 4;
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
				
				if ($account_amount > 0)
				{
					$grocery_debit = $drygood_debit = 0;
					if ($account_amount >= ($aCashier['grocery_amount'] + $aCashier['grocery_servicecharge']))
					{

						$grocery_debit = $aCashier['grocery_amount'] + $aCashier['grocery_servicecharge'];
						$account_amount -= ($aCashier['grocery_amount']+ $aCashier['grocery_servicecharge']);

					}
					else
					{
						$grocery_debit = $account_amount; // + $aCashier['grocery_servicecharge'];
						$account_amount = 0; // $account_amount+ $aCashier['grocery_servicecharge']);

          			}
					if ( $account_amount > '0')
					{
						$drygood_debit = $account_amount;
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
				
				if ($aCashier['account_id'] != ''  && ($reward_total>0  || $reward_amount_out >0) && $commit == 1)
				{
          			$aCashier['reward_total'] = $reward_total;
				    $aCashier['reward_points_out'] = intval($reward_amount_out/$SYSCONF['VALUE_PER_POINT']);
				    $aCashier['reward_amount_out'] = $reward_amount_out;
          
					if ($aCashier['reward_total'] == '') $aCashier['reward_total']=0;
					if ($aCashier['reward_points_out'] == '') $aCashier['reward_points_out']=0;
					if ($aCashier['reward_amount_out'] == '') $aCashier['reward_amount_out']=0;
           
					$q = "insert into reward (sales_header_id, invoice, type, date,account_id,
				                       points_in, amount_in, points_out, amount_out, terminal)
						values ('".$aCashier['sales_header_id']."','".$aCashier['invoice']."', '1',
				                      '$date','".$aCashier['account_id']."', 
						      '".$aCashier['reward_total']."','".$aCashier['net_amount']."', 
					              '".$aCashier['reward_points_out']."', 
						      '".$aCashier['reward_amount_out']."', '".$SYSCONF['TERMINAL']."')";
					$qr = @query($q);
					if (!$qr)
					{	
						$commit = 9;
						$message .=  "Unable to Insert Into Reward \n".db_error().$q;
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
	
			$tables = $SYSCONF['tables'];
			$sales_header = $tables['sales_header'];
			$sales_detail = $tables['sales_detail'];
			$sales_tender = $tables['sales_tender'];
		
			//move_layer('message.layer','90%','','','', '');
			$textbox = $form['textbox'];
	      	grid($aItems);
			
			$grid_flag = 1;
			if ($f == 'R')
			{
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
			elseif ($f == 'B' && $textbox!='')
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
			elseif ($f == 'M' && $textbox=='')
			{
				$aCashier['account_code'] = '';
				$aCashier['cardno'] = '';
				$aCashier['account'] = '';
				$aCashier['account_id'] = '';
				$aCashier['member'] = '';
				$aCashier['remarks'] = '';
				$aCashier['credit_limit'] = '';
				galert('Member Information Cleared');					
			}
			elseif ($f == 'M' && $textbox!='')
			{
				$q = "select * from account where cardno = '$textbox'";
				$r = @fetch_object($q);
				if ($r)
				{
					$aCashier['account_code'] = $r->account_code;
					$aCashier['cardno'] = $r->cardno;
					$aCashier['account'] = $r->account;
					$aCashier['account_id'] = $r->account_id;
					$aCashier['member'] = '1';
					$aCashier['remarks'] = $r->account;
					$aCashier['credit_limit'] = $r->credit_limit;
					
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
          
		  			include_once('../accounts/accountbalance.php');
					$acct = customerBalance($aCashier['account_id']);
					$aCashier['balance_available'] = round($aCashier['credit_limit'] - $acct['balance'],2);
					//move_layer('message.layer','90%','42px','70%','#FFFFFF', 'auto');
					//$msg = "<font face='Verdana' size='4'>Member:<b>[".$aCashier['cardno'].'] '.$aCashier['account'].'</b> Points: <b>'.
					//			$aCashier['points_balance'].
					//			'</b> Available: <b>'.number_format($aCashier['balance_available'],2).'</b></font></b></h3>';
					$msg = "Member: [".$aCashier['cardno'].'] '.$aCashier['account'].'  Points:'.
								$aCashier['points_balance'].
								'  Available:'.number_format($aCashier['balance_available'],2).
								"\n(Press ALT+M Again To Clear Memeber Data)";
					//galert($msg);
					gset('textbox','');
				}
				else
				{
					$msg = 'Account/Card Number NOT Found ...';
					//galert($msg);
				}
				$grid_flag = 0;				
			}
			elseif ($f == 'V' && !chkRights2('sales','mdelete',$ADMIN['admin_id']))
			{
				$msg = "Access Denied. CANNOT VOID Transaction";
			}
			elseif ($f == 'V' && $textbox!='')
			{
				$invoice = str_pad($textbox,8,'0',str_pad_left);
				$q = "select * from $sales_header where invoice='$invoice' and ip='".$_SERVER['REMOTE_ADDR']."'";
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
					$temp = $aItems[$aCashier['line_no'] -1];
					if ($temp['type'] == 'Tender')
					{
						$arr = null;
						$arr = array();
						$c=0;
						foreach ($aItems as $temp)
						{
							$c++;
							if ($c != $aCashier['line_no'])
							{
								$arr[] = $temp;
							}	
						}
						$aItems = $arr;
					}	
					$aCashier['Tender'] = 0;
				}
				else
				{
					$grid_flag = 0;
				}
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
				galert('You have NO permission to Delete Line Item');			}
			elseif ($f == 114 || $f == 123)
			{
				//delete Item
				//$aItems = array_slice($aItems, $aCashier['line_no']-1,1);
				$c=$aCashier['line_no'];
				$c=0;
				$arr = null;
				$arr = array();
				foreach ($aItems as $temp)
				{
					$c++;
					if ($c != $aCashier['line_no'])
					{
						$arr[] = $temp;
					}
				}
				$aItems = $arr;
			}
			elseif ($f == 115 && (1*$textbox > 10000 or 1*$textbox == '0')) //explicit expression to limit qty
			{
				$msg = "Check Quantity Please.";
			}
			elseif ($f == 115 && $textbox != '')
			{
				
				$temp = $aItems[$aCashier['line_no']-1];
				$temp['qty'] = 1*$textbox;
				if ($SYSCONF['USE_MEDIUM_PRICE']=='Y' && $temp['fraction'] == 1 && ($temp['qty']>=$temp['fraction2']/2) && $temp['price2']>0)
				{
				 	// for wholesale price (per dozen price)
			    	$temp['qty'] = round($temp['qty']/$temp['fraction2'],3);
				 	$temp['fraction'] = $temp['fraction2'];
				 	$temp['price'] = $temp['price2'];
				 	$temp['price1'] = $temp['price2'];
        		}



				$temp['amount'] = $temp['price'] * $temp['qty'];
				if ($temp['taxable'] == 'Y')
				{
					$temp['taxbase'] = round($temp['amount']/(1 + ($SYSCONF['TAXRATE']/100)),2);
					$temp['tax'] = $temp['amount'] - $temp['taxbase'];
				}
				
				$aItems[$aCashier['line_no']-1] = $temp;
				gset('textbox','');
			}
			elseif ($f == 116 && $textbox == '')
			{
				//restore price				
				$temp = $aItems[$aCashier['line_no']-1];
				if ($temp['fraction'] == 1)
				{
					$temp['price'] = $temp['price1'];
				}
				else
				{
					$temp['price'] = $temp['price3'];
				}
				$temp['amount'] = $temp['price'] * $temp['qty'];

				if ($temp['taxable'] == 'Y')
				{
					$temp['taxbase'] = round($temp['amount']/(1 + ($SYSCONF['TAXRATE']/100)),2);
					$temp['tax'] = $temp['amount'] - $temp['taxbase'];
				}
				$aItems[$aCashier['line_no']-1] = $temp;
				gset('textbox','');
			}
			elseif ($f == 116 && $textbox != '')
			{
				//change price -- F5
				if (1*$textbox < 0)
				{
					$msg = "Invalid Price";
				}
				elseif (1*$textbox > 250000)  //explicit expression to limit price
				{
					$msg = "Check Price Please.";
				}
				else
				{
					$temp = $aItems[$aCashier['line_no']-1];
					if ($temp['price1'] != '0' && $ADMIN['usergroup']!='A' && chkRights2('noPOSChangePrice','madd',$ADMIN['admin_id']))
					{
						//restrict change Price
						galert('You have NO Permission to Change Item Price');
					}
					else
					{
					
					//if ($temp['taxable'] != 'Y')
					//{
						$temp['price'] = 1*$textbox;
						$temp['amount'] = $temp['price'] * $temp['qty'];
						if ($temp['price1']=='0')
						{
							$temp['price1'] = $temp['price'];
						}

						if ($temp['taxable'] == 'Y')
						{
							$temp['taxbase'] = round($temp['amount']/(1 + ($SYSCONF['TAXRATE']/100)),2);
							$temp['tax'] = $temp['amount'] - $temp['taxbase'];
						}
						
						$aItems[$aCashier['line_no']-1] = $temp;
					//}
					//else
					//{
					//	$msg = "Cannot Change Price...";
					//}
					}	
					gset('textbox','');
				}
			}
			elseif ($f == 117 && $textbox != '')
			{
				//-- F6
				include_once('cashier.searchstock.php');
			}	
			elseif ($f == 118 && $textbox != '')
			{
				//-- F7 Line Discount
				$temp = $aItems[$aCashier['line_no']-1];
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
					if ($textbox > 100 or $textbox < 0)
					{
						$msg = "Invalid Discount";
					}
					else
					{
						$temp['cdisc'] = $textbox;
						$price_discount = $temp['price1']*($temp['cdisc']/100);
						$temp['price'] = round($temp['price1'] - $price_discount,2);
						$temp['amount'] = $temp['price'] * $temp['qty'];
						$temp['discount'] = $temp['amount'] - $temp['price']*$temp['qty'] ;
						$temp['taxbase'] = round($temp['amount']/(1 + ($SYSCONF['TAXRATE']/100)),2);
						$temp['tax'] = $temp['amount'] - $temp['taxbase'];
						$aCashier['remarks'] .= 'Line Discount;';
		
						$aItems[$aCashier['line_no']-1] = $temp;
						gset('textbox','');
					}
				}
			}	
			elseif ($f == 119)
			{
				//e -- F8 Global Discount
				if ($textbox != '')
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
					$msg = 					insertTender($textbox);
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
			elseif ($f == 121 && $textbox*1 > 1000000)
			{
				$msg = 'Please Re-Check Amount Tendered...';
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
				$temp = $aItems[$aCashier['line_no']-1];
				if ($temp['qty'] != '1')
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
						glayer('message.layer','<font size=3><b>No or Invalid Discount Code Selected</b></font>');
						$grid_flag = 0;
					}
					else
					{
  				  		$r = @qr_object($qr);
  				  		$aCashier['GlobalDiscountCode'] = $r->discount_code;
  				  		$aCashier['GlobalDiscountId'] = $r->discount_id;
  				  		$aCashier['GlobalDiscountPercent'] = $r->discount_percent;
  				 		$aCashier['discount_type'] = $r->discount_type;
 				    	$aCashier['GlobalDiscount']=2;
  						glayer('prompt.layer','<font size=5>Discount Card Number</font>');
     					gset('textbox','');
  					
  				  		if (count($ax)>1)
  				  		{
  				    		$aCashier['discount_card'] = $ax[1];
    						glayer('prompt.layer','<font size=5>Percent Discount</font>');
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
    				glayer('prompt.layer','<font size=5>Percent Discount</font>');
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
				if ($ax[0] > 100 or $ax[0] < 0)
				{
					glayer('message.layer','<font size=3><b>Invalid Discount</b></font>');
					$grid_flag = 0;
				
				}
				else
				{
  				    $aCashier['GlobalDiscountPercent']=$ax[0]*1;
  				    $aCashier['GlobalDiscount']=4;
          	}
        }
        
        if ($aCashier['GlobalDiscount'] == 4)
        {
          	$c=0;
  				foreach ($aItems as $temp)
  				{
  					$temp['cdisc'] = $aCashier['GlobalDiscountPercent'];
  					$price_discount = $temp['price1']*($temp['cdisc']/100);
  					$temp['price'] = $temp['price1'] - $price_discount;
  				//	$temp['price'] = round($temp['price1'] - $price_discount,2);
  					$temp['amount'] = $temp['price'] * $temp['qty'];
  					$temp['discount'] = $temp['amount'] - $temp['price']*$temp['qty'] ;
  
  					$temp['taxbase'] = round($temp['amount']/(1 + ($temp['taxrate']/100)),2);
  					$temp['tax'] = $temp['amount'] - $temp['taxbase'];
  
  					$aItems[$c] = $temp;
  					$c++;
  				}
				$aCashier['remarks'] .= 'Global Discount;';
				$aCashier['discount_percent'] = $textbox;
				 
				$aCashier['GlobalDiscount']='';
				$aCashier['GlobalDiscountCode']='';
				$aCashier['GlobalDiscountPercent']='';
				 
  				gset('textbox','');
				glayer('prompt.layer','<font size=5>Scan Item</font>');

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
						$temp['stock'] = $temp['tender_code'].' - '.$temp['tender'];
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
						 					credit_limit
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
							if (date('Y-m-d') > $r->date_expiry) 
							{
								$message = "Account is EXPIRED. Please refer to credit section...";
								return $message;
							}
							
							include_once('../accounts/accountbalance.php');
							$acct = customerBalance($temp['account_id']);
							
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

							$q = "select * from account_class where account_class_id='$r->account_class_id'";
						 	$qr = @query($q);
							$rr = @qr_object($qr);
							$temp['grocery_service'] = $rr->grocery_service;
							$temp['drygood_service'] = $rr->drygood_service;
							
							$dummy = null;
							$dummy = array();
							$dummy['barcode'] = 'SRVC';
							$dummy['stock'] = 'Service Charge';
							$dummy['type'] = 'ServiceCharge';
							$srvc_amount = $grocery_netitem = $dry_good_netitem = 0;

              			$balance_subtotal = $aCashier['subtotal'];
              			if ($aCashier['balance_available'] < $balance_subtotal)
              			{
              				$balance_subtotal = $aCashier['balance_available'];
							}

              			$chargeable_amount = $balance_subtotal;

							if ($balance_subtotal >= $aCashier['grocery_amount'] && $rr->grocery_service > '0')
							{
								$grocery_amount = $aCashier['grocery_amount'];
								$balance_subtotal -= $aCashier['grocery_amount']; 
							}
							elseif ($aCashier['grocery_amount'] > 0 && $rr->grocery_service > '0')
							{
								$grocery_amount = $balance_subtotal;
								$balance_subtotal -= 0; 
							}
							
							if ($balance_subtotal >= $aCashier['dry_good_netitem'] && $rr->drygood_service > '0')
							{
								$dry_good_netitem = $aCashier['dry_good_netitem'];
								$balance_subtotal -= $aCashier['dry_good_netitem']; 
							}
							elseif ($aCashier['dry_good_netitem'] > 0 && $rr->drygood_service > '0')
							{
								$dry_good_netitem = $balance_subtotal;
								$balance_subtotal -= 0; 
							}

 							//note service charge for NET ITEMS AND PROMOTIONAL ITEMS Only
							$grocery_factor = (100 - $temp['grocery_service'])/100;
							$drygood_factor = (100 - $temp['drygood_service'])/100;
							if ($grocery_factor > '0')
							{
								$grocery_servicecharge = round(($grocery_amount/$grocery_factor) - $grocery_amount,2);
  								$amount_due_real = $aCashier['grocery_amount']/$grocery_factor;
								if (($grocery_servicecharge+$grocery_amount) > $aCashier['balance_available'])
								{
									//recalculate if beyond credit limit
									$grocery_amount = $chargeable_amount*$grocery_factor;
									$grocery_servicecharge = round(($grocery_amount/$grocery_factor) - $grocery_amount,2);
								}
  								$dummy['grocery_servicecharge'] = $grocery_servicecharge;
  								$srvc_amount = $grocery_servicecharge;
              			}
              			if ($drygood_factor > '0')
              			{
								$dry_good_servicecharge = round(($dry_good_netitem/$drygood_factor) - $dry_good_netitem,2);
  								$amount_due_real += ($aCashier['dry_good_netitem']/$drygood_factor);
								if (($grocery_servicecharge+$grocery_amount+$dry_good_servicecharge+$dry_good_netitem) > $aCashier['balance_available'])
								{
									//recalculate if beyond credit limit
									$dry_good_netitem = ($chargeable_amount - ($grocery_amount+$grocery_servicecharge))*$drygood_factor;
									if ($dry_good_netitem<=0)$dry_good_netitem =0;
									$dry_good_servicecharge = round(($dry_good_netitem/$drygood_factor) - $dry_good_netitem,2);
								}
								$dummy['dry_good_servicecharge'] = $dry_good_servicecharge;
								$srvc_amount += $dry_good_servicecharge;
				         }
							
							if ($amount_due_real > $temp['balance_available'])
							{
								$message = "   *** Amount is BEYOND CREDIT LIMIT ***.\n  Available Balance :".number_format($temp['balance_available'],2)." Amount Due: ".number_format($amount_due_real,2);
							}
							$srvc_amount = round($srvc_amount,2);
							$dummy['amount'] = round($srvc_amount,2);
							
							$chargeable_amount = $grocery_amount + $dry_good_netitem + $srvc_amount ;
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
											' Points: '.$points_balance.' (P '.number_format($temp['max_amount'],2).')'.
											'<br />(Press ALT+M Again To Clear Memeber Data)';
				
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
				elseif ($aCashier['Tender'] == 4 && $value*1 > 1000000)
				{
					$message = 'Please Check Amount Entered.';
				}
				elseif ($aCashier['Tender'] == 4 && $value>0)
				{
					//4 - Input Payment Amount
					$temp = $aItems[$aCashier['line_no'] -1];
					$temp['amount'] = 1*$value;
					
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
          		else
          		{
						$aCashier['Tender'] = 0;
						glayer('prompt.layer','<font size=5>Scan Item</font>');
			      }
					$aItems[$aCashier['line_no'] - 1] = $temp;
					
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
			if ($ADMIN == null)
			{
				galert('User session has expired. Please Log-In Again...');
				session_unset();
				echo "<script>window.location='../'</script>";
				exit;
			}
			//move_layer('message.layer','90%','','','', '');

			$grid_flag = 1;
			$empty_textbox = 1;
			
			$value  = $form['textbox'];
				
			if ($value == '') return done();
				
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
					
					$q    = "select 
									stock_id,
									barcode,
									stock,
									price1,
									price1 as price,
									price2,
									price3,
									'1' as fraction,
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
									account_id as supplier_id
								from 
									stock
								where
									
									barcode = trim('$searchitem') and
									stock.enable='Y'
                			offset 0 limit 1";
					$qr = @query($q);
										   
					if (!$qr) 
					{
						  $message  = db_error();
						  glayer('message.layer', $message);
						  return done();  
					}                   

					if (!@pg_num_rows($qr)) 
					{
  						$q    = "select 
  									barcode.stock_id,
  									barcode.barcode,
  									stock,
  									price1,
  									price1 as price,
  									price2,
  									price3,
  									taxable,
  									'1' as fraction,
								    category_id,
                   					fraction2, 
  									date1_promo,
  									date2_promo,
  									promo_cdisc,
  									promo_sdisc,
  									promo_price1,
								    max_discount,
							       netitem,
								   account_id as supplier_id
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
    						  return done();  
    					}                   
    				      					
    					if (!@pg_num_rows($qr)) 
    					{
    					   if ($SYSCONF['USE_CASE_PRICE']=='Y')
    					   {
						   		$q    = "select 
										stock_id,
										casecode as barcode,
										price3 as price,
										price3 as price1,
										price2,
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
										account_id as supplier_id
									from 
            							stock
      	   					where
      		    	  				casecode = '$searchitem' and
      		    	  				stock.enable='Y'
                				offset 0 limit 1";					    
    
								$qr = @query($q);
								
								if (!@pg_num_rows($qr)) 
								{
									galert("Item NOT Found!");
									//$message  = "No record found...";
									//glayer('message.layer', $message);
									return done();
								}
    						}
    						else
              				{
								galert("Item NOT Found!");
    						   //$message  = "No record found...";
    						   //glayer('message.layer', $message);
    						   return done();
               				}
             			}  
					}                     
			
					$rows = null;
					$rows = array();
					$r = @qr_assoc($qr);
					$r['qty']=$qty;
					
					$q = "select department, category_code from category where category_id='".$r['category_id']."'";
					$rc = fetch_assoc($q);
					$r['department'] = $rc['department'];
					$r['category_code'] = trim($rc['category_code']);
					
					if ($r['fraction'] == 0) $r['fraction'] = 1;
					if ($r['fraction2'] == 0) $r['fraction2'] = 1;
					if ($r['fraction3'] == 0) $r['fraction3'] = 1;

					$today = date('Y-m-d');
				
					if ($r['fraction'] == 1) //per piece retail
					{
						if ($r['date2_promo'] >= $today && $r['date1_promo'] <= $today)
						{
							$r['cdisc'] = $r['promo_cdisc'];
							$r['sdisc'] = $r['promo_sdisc'];
							
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
											promo_header.sdisc
										from	
											promo_header, promo_detail
										where
											promo_header.promo_header_id=promo_detail.promo_header_id and 	
											promo_detail.stock_id='".$r['stock_id']."'	and	
											promo_header.date_from<='$today'	and	
											promo_header.date_to>='$today' and
											promo_header.enable='Y'
										order by
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
									//-- check promo by supplier
									$clen = strlen($r['category_code']);
									$q = "select 
														promo_header.cdisc,
														promo_header.sdisc,
														promo_header.category_id_from,
														promo_header.category_id_to,									
														promo_header.category_code_from,
														promo_header.category_code_to									
													from 
														promo_header 
													where
														account_id = '".$r['supplier_id']."' and
														promo_header.date_from<='$today'	and	
														promo_header.date_to>='$today' and
														((substr(category_code_from,1,$clen) <= '".$r['category_code']."' and
														substr(category_code_to,1,$clen) >= '".$r['category_code']."' ) or
														(category_code_from = '')) and
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
							 		$rr = @qr_object($qpr);
							
								}
							} 

							if ($rr)
							{
								$message = 'Found Promo..';
								//$r['price']	=	$rr->promo_price;
								$r['price'] = round($r['price']*(1-(($rr->cdisc + $rr->sdisc)/100)),2);
								$r['cdisc']	=	$rr->cdisc;
								$r['sdisc']	=	$rr->sdisc;
								glayer('message.layer', $message);
							}
							elseif ( ($r['qty'] >= ($r['fraction2']/2) || $use_medium_price==1) && $r['price2']>0 && $SYSCONF['USE_MEDIUM_PRICE']=='Y')
							{
							 // for wholesale price (per dozen price)
						    if ($use_medium_price == 0)
                {
			            $r['qty'] = round($r['qty']/$r['fraction2'],3);
						    }  
					      $r['fraction'] = $r['fraction2'];
						    $r['price'] = $r['price2'];
						    $r['price1'] = $r['price2'];
              }
						}		
					}
				
					$r['amount'] = round($r['price']*$r['qty'],2);
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
					if ($aCashier['edit'] == '10000') //'1' - TO allow overwrite
					{
						$aItems[$aCashier['line_no'] -1] = $r;
					}
					else
					{
						$rItems = array_reverse($aItems);
						$rItems[] = $r;
						$aItems = array_reverse($rItems);
						$aCashier['line_no'] = 1;
					}	
					$aCashier['edit'] = 0;
				}
				
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
        return done();
    }
		
    $xajax->registerFunction('onload');
    function onload() 
		{
				global $aItems, $aCashier;
				grid($aItems);
				return done();
		}

        //$xajax->debugOn();
        $xajax->processRequests();
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html><head>
<title>Point of Sale</title>
<?php $xajax->printJavascript('../'); // output	the xajax javascript. This must	be called between the head tags	?>

<script language='javascript'>
function wait($message) {
	xajax.$('message.layer').style.display = 'block';
	xajax.$('message.layer').innerHTML = $message+"<br><img src='wait.gif'>";
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
	if ($key == 'R' || $key == 'Z' || $key=='B' ||$key=='C' || $key == 'M')
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

		if (document.getElementById('grid.layer').innerHTML.indexOf("rightarrow.jpg") > 0)
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
	mycode=myevent.keyCode

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
	else if (mycode == 13)
	{
		if (document.getElementById('textbox').value != '')
		{
			document.getElementById('Ok').click();
		}
		else
		{
			xajax_fkey(mycode,xajax.getFormValues('f1'));
		}	
 		focusIt('textbox');
		return false;
	}
	else if(keys["a"+myevent.keyCode])
	{
		if (document.getElementById('textbox').value != '')
		{
			document.getElementById('Ok').click();
		}
		else
		{
			xajax_fkey(mycode,xajax.getFormValues('f1'));

			e = document.getElementById('rightarrow');
			if (e)
			{
				e.scrollIntoView(false);
			}	
//				alert('here');
		}
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
				xajax_fkey(mycode,'f1');
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
  
-->
</style>
</head>
<body bgcolor="#CCCCCC" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" onfocus="document.getElementById('textbox').focus()"; onLoad="vOnLoad()" >
<form id="f1" name="f1" style="margin:0px;padding:0px">
  <table width="99%" height="99%" border="0"	cellpadding="0"	cellspacing="0" align="center">
    <tr	height="5%"> 
      <td width="79%" height="1%" bgcolor="#FFFFFF"> <strong><font color="#CC3300" size="5" face="Times New Roman, Times, serif"> 
        <!--  <em>
        <?=	$SYSCONF['BUSINESS_NAME'];?>
        <?= ($_SERVER['REMOTE_ADDR'] == '127.0.0.1' ? ' - Local' : '');?>
        </em></font></strong>
		<br>
        <font size="2"	face="Verdana, Arial,	Helvetica, sans-serif">
        <?=	$SYSCONF['BUSINESS_ADDR'];?>
        </font>		-->
        &nbsp;<img src="../graphics/logo.jpg" height="60" width="280"> <?= ($SYSCONF['DATABASE'] != '' ? 'NOTICE: DB = '.$SYSCONF['DATABASE'] : '');?>
        </font></strong></td>
      <td	width="21%"	align="center" bgcolor="#CCCCCC"> <font size="2"> 
        <?=	$ADMIN['username'];?>
        <br /><div id='bagger.layer'></div><br />
        <?=	date('F	d, Y');?><br>
        [ <a accesskey='L' href="javascript: altkey('L')" id='logout'>Logout</a> ] </font></td>
    </tr>
    <tr> 
      <td valign="top" width="79%"> <table width="100%"	height="100%"	border="0" cellspacing="0" cellpadding="0" >
          <tr height="5%"> 
            <td	colspan="5" bgcolor="#C6C6C6"><div id="prompt.layer"><font size="5"> 
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
      <td width="21%" rowspan="2" align="center" valign="top"> <div 
id="invoice.layer" align="left"><font size="2">No:<b> 
          <?=$aCashier['invoice'];?>
          </b></font> <br />
          Terminal: <b> 
          <?=$SYSCONF['TERMINAL'];?>
          </b> 
          <hr />
        </div>
		<div id="instruction.layer" style="position:relative; width:100%; height:400px; z-index:2; overflow: auto;"> 
          <?= showInstruction();?>
        </div>
        <!-- <div id="itemcount.layer"></div>-->
      </td>
    </tr>
    <tr  valign="top"> 
      <td valign="top">
		<div id="grid.layer" style="position:relative; width:100%; height:300px; z-index:2; overflow: auto; left: 0; top: 50; background-color: #FFFFFF; layer-background-color: #FFFFFF; border: 1px none #000000;"></div>
		<div id='subtotal.layer' style="text-align:right"><font size='+5'>Amount Due...0.00</font></div>
	  </td>
    </tr>
  </table>
  <div id="plu.layer" style="position:absolute; display:none; top:10%; left:40%; z-index: 100; overflow: auto; width: 50%; background-color: #CCCCCC; layer-background-color: #CCCCCC; border: 1px none #000000; height: 80%;"></div>
</form>                        
<div id="message.layer" style="position:absolute; border: 1px solid #CCCC00; display:none;text-align:center" align="top"></div>	  
<div id='wait.layer' style='position:absolute; display:none;left:40%;top:50%; z-index: 10' onClick="this.style.display='none'">Search...<br><img src='wait.gif'></div>
<script>document.getElementById('textbox').focus()</script>
  </body>
</html>  
