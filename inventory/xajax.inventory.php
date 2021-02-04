<?
	function hidelayer($layer)
	{
		hide_layer($layer);
		return done();
	}
	

function postApLedger($rr_header_id)
{
	if ($rr_header_id  == '')
	{
		return;
	}
	$q = "select * from rr_header where rr_header_id='$rr_header_id'";
	$qr = @pg_query($q) or message1(pg_errormessage().$q);
	$arrayRR = @pg_fetch_assoc($qr);
	
	$net_amount = $arrayRR['net_amount'];
	$q = "select * from por_header where rr_header_id='".$arrayRR['rr_header_id']."'";
	$qqr = @pg_query($q) or message1(pg_errormessage().$q);
	if (@pg_num_rows($qqr) > 0)
	{
		$rr = @pg_fetch_object($qqr);
		$net_amount -= $rr->net_amount;

	}

	$q = "select * from apledger where type='RR' and record_id = '".$arrayRR['rr_header_id']."' ";
	$qr = @pg_query($q) or message1(pg_errormessage().$q);
	if (@pg_num_rows($qr) == 0)
	{
		$q = "insert into apledger (account_id, date, record_id, reference, credit,debit,type)
					values ('".$arrayRR['account_id']."', '".$arrayRR['date']."', '".$arrayRR['rr_header_id']."','".$arrayRR['invoice']."','$net_amount','0','RR')";
		$qr = @pg_query($q) or message1(pg_errormessage().$q);
							
	}
	else
	{
		$r = @pg_fetch_object($qr);
		$apledger_id = $r->apledger_id;
		$q = "update apledger set
									credit = '$net_amount',
									debit='0',
									account_id = '".$arrayRR['account_id']."',
									date = '".$arrayRR['date']."',
									reference ='".$arrayRR['invoice']."'
						where
									apledger_id = '$apledger_id'";
		$qr = @pg_query($q) or message1(pg_errormessage().$q);
									
	}

	return;
}

function postApLedgerPR($por_header_id)
{
	if ($por_header_id == '')
	{
		return;
	}
	$q = "select * from por_header where por_header_id = '$por_header_id'";
	$qr = @pg_query($q) or message1(pg_errormessage().$q);
	$arrayRR = @pg_fetch_assoc($qr);
	$net_amount = $arrayRR['net_amount'];

	$q = "select * from apledger where type='PR' and record_id = '$por_header_id' ";
	$qr = @pg_query($q) or message1(pg_errormessage().$q);
	if (@pg_num_rows($qr) == 0)
	{
		$q = "insert into apledger (account_id, date, record_id, reference, credit,debit,type)
					values ('".$arrayRR['account_id']."', '".$arrayRR['date']."', '".$arrayRR['por_header_id']."','".str_pad($arrayRR['por_header_id'],7,0,STR_PAD_LEFT)."','$net_amount','0','PR')";
		$qr = @pg_query($q) or message1(pg_errormessage().$q);
							
	}
	else
	{
		$r = @pg_fetch_object($qr);
		$apledger_id = $r->apledger_id;
		$q = "update apledger set
									credit = '$net_amount',
									debit='0',
									account_id = '".$arrayRR['account_id']."',
									date = '".$arrayRR['date']."',
									reference ='".str_pad($arrayRR['por_header_id'],7,0,STR_PAD_LEFT)."'
						where
									apledger_id = '$apledger_id'";
		$qr = @pg_query($q) or message1(pg_errormessage().$q);
									
	}

	return;
}

	function encodeStocktransfer($astid)
	{
		$info='';
		
		$q = "select 
					* 
				from
					stocktransfer_header as sh,
					stocktransfer_detail as sd,
					stock
				where
					sh.stocktransfer_header_id = sd.stocktransfer_header_id and
					stock.stock_id = sd.stock_id and
					sh.status!='C' and
					sh.stocktransfer_header_id in ('$astid')";
		$qr = @pg_query($q);
		if (!$qr)
		{
			galert(pg_errormessage().$q);
		}
		
		$mstocktransfer_header_id = '';
		while ($r = @pg_fetch_object($qr))
		{
			if ($mstocktransfer_header_id != $r->stocktransfer_header_id)
			{
				$info .= "STOCKTRANSFER||".$r->stocktransfer_header_id."||".$r->date."||".$r->branch_id_from."||".$r->branch_id_to."||".$r->admin_id."||".$ADMIN['admin_id']."\n";
				$mstocktransfer_header_id = $r->stocktransfer_header_id;
			}
			$info .= $r->barcode."||".$r->case_qty."||".$r->unit_qty."\n";
			
		}
		return $info;
	}	

	function stocktransferVPNUpload($form)
	{
		global $aST, $aSYSCONF, $ADMIN;
		
		$q="select * from branch where branch_id ='".$aST['branch_id_to']."'";
		$qr = @pg_query($q);
		$r = @pg_fetch_object($qr);
		
		if ($r->branch_code == '')
		{
			galert(" CANNOT upload. No Branch Code Specified...");
			return done();
		}
		$branch_code_to = $r->branch_code;
		$destination = "/tmp/".
		$destination .=  $branch_code_to."_ST_".$aST['stocktransfer_header_id'];
		
		$contents = '';
		
		$contents = encodeStocktransfer($aST['stocktransfer_header_id']);

		if ($contents == '')
		{
			galert(" CANNOT upload. No data generated...");
			return done();
		}

		$handle = @fopen("$destination", "w");
		
		if (!$handle)
		{
			galert("Unable To Connect/Open Server ... ");
			return done();
		}
		
		$w = @fwrite($handle, $contents);
		if (!$w)
		{
			galert("Unable To Write To VPN Server...");
		}
		else
		{
			galert(" Data Successfully Uploaded...");
		}
		@fclose($handle);

		sleep(1);
		return done();
	}

	function checkRRInvoice($form)
	{
		global $aRR;
		
		$invoice = $form['invoice'];
		$rr_id = $aRR['rr_header_id'];
		$account_id = $form['account_id'];
		
		if ($invoice == '' || $account_id*1 == '0') return done();
		if ($rr_id == '') $rr_id = 0;
		
		$q = "select * from rr_header
					where
						invoice='$invoice' and
						account_id='$account_id' and
						rr_header_id != '$rr_id'";
						
		$qr = @pg_query($q);

		if (!$qr)
		{
			galert("Error checking Invoice...".pg_errormessage().$q);
		}
		elseif (@pg_num_rows($qr) > 0)
		{
			$r = @pg_fetch_object($qr);
			galert("WARNING! Invoice already Exists for this supplier....\nPlease Refer to SRR No.: $r->rr_header_id Invoice: $r->invoice  Dated: (".ymd2mdy($r->date).")");
		}
		return done();
	}
	
	function autoCompleteAccount($form)
	{
		$account = $form['account'];
		if ($account == '')
		{
			return done();
		}
		
		$q = "select * from account where account_code like '$account%' or account ilike '$account%'";
		$qr = @pg_query($q);
		if (!$qr)
		{
			galert(pg_errormessage());
		}
		else
		{
			$pln='';
			while ($r=@pg_fetch_object($qr))
			{
				$pln .= "<a href='' class='autocomplete'><u>$r->account_code $r->account</u></a><br>";
			}
			//galert($pln);
			glayer('wordlist',$pln);
		}	
		return done();
	}
	
	function genBarcode($form)
	{
		global $astock;

		$category_id = $form['category_id'];		
		if ($astock['stock_id'] != '')
		{
			galert("Cannot increment barcode...Data is already Saved...");
			return done();
		}
		elseif ($category_id != '')
		{
			$q = "select * from category where category_id = '$category_id'";
			$qr = @pg_query($q);
			if (!$qr)
			{
				galert(pg_errormessage().$q);
				return done();
			}
			$r = @pg_fetch_object($qr);
			$category_code = substr($r->category_code,0,2);

			$q = "select * from cache where type='BARCODESERIAL' and value2='$category_code'";
			$qr = @pg_query($q);
			if (!$qr)
			{
				galert(pg_errormessage().$q);
			}
	
			if (@pg_num_rows($qr) == '0')
			{
				$value1 = 1;
				$barcode_val = $value1;
				
				while (true)
				{
					$barcode = chop($category_code).str_pad($value1,5,'0',STR_PAD_LEFT);
					$q = "select * from stock where barcode = '$barcode'";
					$qr =@pg_query($q);
					if (!$qr)
					{
						galert(pg_errormessage().$q);
						return done();
					}
					if (@pg_num_rows($qr) == 0) 
					{
						$barcode_val=$value1;
						break;
					}
					
					else	$value1++;
					
				}
				$q = "insert into cache (type, value1, value2) values ('BARCODESERIAL','$barcode_val','$category_code')";
				$qr = @pg_query($q);
				if (!$qr)
				{
					galert(pg_errormesage().$q);
				}
		
			}
			else
			{
				$r = @pg_fetch_object($qr);
				
				$value1 = $r->value1+1;
				$barcode_val = $value1;
				
				while (true)
				{
					$barcode = chop($category_code).str_pad($value1,5,'0',STR_PAD_LEFT);
					$q = "select * from stock where barcode = '$barcode'";
					$qr =@pg_query($q);
					if (!$qr)
					{
						galert(pg_errormessage().$q);
						return done();
					}
					if (@pg_num_rows($qr) == 0) 
					{
						$barcode_val=$value1;
						break;
					}
					
					else	$value1++;
					
				}
				
				$q = "update cache set value1 = '$barcode_val',value2='$category_code' where cache_id = '$r->cache_id'";
				$qr = @pg_query($q);
				if (!$qr)
				{
					galert("Unable to update serialized barcode table...".pg_errormessage().$q);
				}
			}
									
		}
		else
		{	
			$q = "select * from cache where type='BARCODESERIAL' and value2='none'";
			$qr = @pg_query($q);
			if (!$qr)
			{
				galert(pg_errormessage().$q);
			}
	
			if (@pg_num_rows($qr) == '0')
			{
				$barcode = 1;
				while (true)
				{
					$q = "select * from stock where barcode = '$barcode'";
					$qr =@pg_query($q);
					if (!$qr)
					{
						galert(pg_errormessage().$q);
						return done();
					}
					if (@pg_num_rows($qr) == 0) 
					{
						break;
					}
					
					else	$barcode++;
					
				}

				$q = "insert into cache (type, value1, value2) values ('BARCODESERIAL','$barcode','none')";
				$qr = @pg_query($q);
				if (!$qr)
				{
					galert(pg_errormesage().$q);
				}
		
			}
			else
			{
				$r = @pg_fetch_object($qr);
				$barcode = $r->value1 + 1;
				while (true)
				{
					$q = "select * from stock where barcode = '$barcode'";
					$qr =@pg_query($q);
					if (!$qr)
					{
						galert(pg_errormessage().$q);
						return done();
					}
					if (@pg_num_rows($qr) == 0) 
					{
						break;
					}
					
					else	$barcode++;
					
				}

				$q = "update cache set value1 = '$barcode',value2='none' where cache_id = '$r->cache_id'";
				$qr = @pg_query($q);
				if (!$qr)
				{
					galert("Unable to update serialized barcode table...".pg_errormessage().$q);
				}
			}
		}

		gset('barcode',$barcode);
		return done();
	}
		
	function vBarcode($form)
	{
		global $ADMIN, $astock;
		$barcode = $form['barcode'];
		if ($barcode == '')
		{
			return done();
		}
		$q .= "select * from stock where barcode = '$barcode'";
		$qr = @pg_query($q);
		$fnd = 0;
		if (!$qr)
		{
			galert("Error Verifying Barcode...".$q);
		}			
		if (@pg_num_rows($qr)>0)
		{
			$r = @pg_fetch_object($qr);
			if ($r->stock_id != $astock['stock_id'])
			{
				galert("WARNING: ".$ADMIN['name'].", Barcode Already Exist for: \n".$r->stock);
				$fnd=1;
			}
		}
		if ($fnd == 1)
		{
			gset('barcode','');
			gset('stock_description','');
			$focus = "document.getElementById('barcode').focus()";
		}
		else
		{
			$focus = "document.getElementById('stock').focus()";
		}			
		gscript($focus);
		return done();		
	}
	
	
	function pc_insert($form)
	{
		global $aPHYC, $ADMIN;
		
		$stock_id = $form['stock_id'];
		$icase_qty = $form['icase_qty'];
		$iunit_qty = $form['iunit_qty'];
		$icost3 = $form['icost3'];
		
		if ($icase_qty =='' && $iunit_qty =='')
		{
			galert("No Quantity Specified...");
			return done();
		}
		if ($stock_id == '')
		{
			galert("No Item/Product Specified...");
			return done();
		}
		if ($iunit_qty == '') $iunit_qty = 0;
		if ($icase_qty == '') $icase_qty = 0;
		if ($icost3 == '') $icost3 = 0;
		$timestamp = date('Y-m-d g:i:s');
		
 
		$q = "select * from ".$aPHYC['tmp_table']." where stock_id = '$stock_id'";
		$qr = @pg_query($q);
		if (!$qr)
		{
			galert(pg_errormessage().$q);
			return done();
		}
		if (@pg_num_rows($qr) > 0)
		{
				$r = @pg_fetch_object($qr);
				$q = "update ".$aPHYC['tmp_table']." 
							set 
								case_qty = '$icase_qty', 
								unit_qty = '$iunit_qty', 
								cost3='$icost3',
								timestamp = '$timestamp',
								liwat='Y' 
							where 
								stock_id = '$r->stock_id'";
				$qr = @pg_query($q);
				if (!$qr)
				{
					galert(pg_errormessage().$q);
					return done();
				}
			
		}
		else
		{
				$q = "insert into ".$aPHYC['tmp_table']." (stock_id, stockledger_id, cost3, case_qty, unit_qty, timestamp, liwat)
						values ('$stock_id','0','$icost3','$icase_qty','$iunit_qty', '$timestamp','Y')";
	
				$qqr = @pg_query($q);
				if (!$qqr)
				{
					galert(pg_errormessage().$q);
					return done();
				}
		}

		gset('searchkey','');
		gset('stock','');
		gset('stock_id','');
		gset('icase_qty','');
		gset('iunit_qty','');
		gset('icost3','');
		gset('ctr','');	

		$focus = "document.getElementById('searchkey').focus()";
		gscript($focus);
		
		pc_grid($form,'');
		return done();
	}
	
	function vAcctno($form)
	{
		global $ADMIN, $asupplier;

		$account_code = $form['account_code'];
		if ($account_code == '') return done();
		$q .= "select * from account where account_code = '$account_code'";
		$qr = @pg_query($q);
		$fnd = 0;
		
		if (!$qr)
		{
			galert("Error Verifying Account Code...".$q);
		}			
		
		if (@pg_num_rows($qr)>0)
		{
			$r = @pg_fetch_object($qr);
			if ($r->account_id != $asupplier['account_id'])
			{
				galert("WARNING: ".$ADMIN['name'].", Account Code Already Exist for: \n".$r->account);
				$fnd=1;
			}
		}
		if ($fnd == 1)
		{
			gset('account_code','');
			$focus = "document.getElementById('account_code').focus()";
		}
		else
		{
			$focus = "document.getElementById('account').focus()";
		}			
		gscript($focus);
		return done();		
	}

	function pc_grid($form, $key)
	{
			//physical count grid
			global $aPHYC;

			$show = $form['show'];
			$details = '';
			$tmp_table = $aPHYC['tmp_table'];
			$ctr = $aPHYC['ctr'];
			if ($key == 'DOWN')
			{
				$aPHYC['start'] += 14;
				$ctr-=1;
			}
			elseif ($key == 'UP')
			{
				$ctr -= 29;
				$aPHYC['start'] -= 14;
				if ($aPHYC['start'] < 0) 
				{
					$aPHYC['start'] = 0;
				}
				if ($ctr<0) $ctr=0;
			}
			$q = "select 	tmp.stock_id,
								tmp.case_qty,
								tmp.unit_qty,
								tmp.cost3,
								tmp.stockledger_id, 
								stock.stock, 
								stock.stock_description, 
								stock.barcode,
								stock.unit1,
								stock.fraction3
				from 
								$tmp_table as tmp,
								stock
				where
								stock.stock_id = tmp.stock_id";
			
			if ($show == 'nocost')
			{
				$q .= " and tmp.cost3 = '0.00' ";
			}
			if ($sortby == '' or $sortby=='timestamp')
			{
				$q .= " order by	timestamp desc ";
			}
			elseif ($sortby == 'id')
			{
				$q .= " order by	tmp_id desc ";
			}
			elseif ($sortby == 'barcode')
			{
				$q .= " order by	stock.barcode";
			}
			else
			{
				$q .= " order by stock.stock_description ";
			}
			$q .= " offset ".$aPHYC['start']." limit 15";
			$qr = @pg_query($q);
				 
			if (!$qr)
			{
				 galert(pg_errormessage().$q);
			}
			while ($temp = @pg_fetch_assoc($qr))
			{

				$ctr++;

				if ($temp['stock_description'] == '')
				{
					$stock = $temp['stock'];
				}
				else
				{
					$stock=$temp['stock_description'];
				}
				$previous_id = $ctr-1;
				$next_id = $ctr+1;
				

				$href = "javascript:SL('".$temp['stock_id']."','".$temp['barcode']."','".addslashes($stock)."','".$temp['case_qty']."','".$temp['unit_qty']."','".$temp['cost3']."','".$ctr."')";
				
				$details .= "<a href=\"$href\">".adjustRight($ctr,7).'. '.
								"<input type='checkbox' name='delete[]' id='a".$temp['stock_id']."' value = '".$temp['stockledger_id']."' >".
								adjustSize($temp['barcode'],16).' '.
								adjustSize($stock,48).'  '.
								adjustSize($temp['fraction3'],4).' '.
								adjustRight($temp['case_qty'],9).' '.
								adjustRight($temp['unit_qty'],9).' '.
								adjustRight(number_format($temp['cost3'],2),13).' '.
								"</a>\n";
			}
			
			$aPHYC['ctr'] = $ctr;
			glayer(gridLayer,"<pre>".$details."</pre>");
			
			return done();	
	}
	

	function pc_print($form) 
	{
		global $aPHYC;
		hide_layer('printLayer');
			$total_cost= 0;
			$table = $aPHYC['tmp_table'];
			
			$q = "select 
							sum(tmp.cost3*tmp.case_qty + (tmp.cost3/stock.fraction3) * tmp.unit_qty) as total_cost
					from 
							$table as tmp,
							stock
					where
							stock.stock_id =tmp.stock_id";
			$qr = @pg_query($q);
			
			$r = @pg_fetch_object($qr);
							 
			$message = "<br><br><br>&nbsp; &nbsp; Total Cost of Inventory for this entry <br>&nbsp;".
							" &nbsp; &nbsp; (as of this Period) is : ".number_format($r->total_cost,2)."\n\n";
							
			show_layer('browsePLULayer');
			glayer('innerPLULayer',$message);
		return done();
	}
	

	function select_sid($sid, $return_function, $return_focus) 
	{
			//--select from browse
			//hide_layer('browsePLULayer');
			$q = "select *
					from
						stock
					where
						stock_id='$sid' and
						enable='Y'";

			$qr = @pg_query($q);
			
			if (!$qr)
			{
				galert("Error Query in select_sid: ". pg_errormessage());
			}
			elseif (@pg_num_rows($qr) == 0)
			{
				galert(" Item NOT found...");
			}
			else
			{
				$r = @pg_fetch_assoc($qr);
				$asid = $r;
				$found = 1;
			}
			
			if ($found == 1)
			{
				if ($return_function == 'pc_select')
				{
					pc_select($asid);
				}
				elseif ($return_function == 'por_select')
				{
					por_select($asid);
				}
				elseif ($return_function == 'rr_select')
				{
					rr_select($asid);
				}
				elseif ($return_function == 'po_select')
				{
					po_select($asid);
				}
				elseif ($return_function == 'por_select')
				{
					por_select($asid);
				}
				elseif ($return_function == 'stocktransfer_select')
				{
					stocktransfer_select($asid);
				}
				elseif ($return_function == 'invadjust_select')
				{
					stocktransfer_select($asid);
				}
			}
						
		$focus = "document.getElementById('$return_focus').focus()";
		
		gscript($focus);
		return done();
	}
	
	function pc_search($form, $return_function, $return_focus)
	{

		if ($return_function == 'rr_select')
		{
			global $aRR;

			$fields_header = array('date', 'invoice', 'gross_amount', 
							'disc1', 'disc1_type','disc2', 'disc2_type','disc3', 'disc3_type',
							'tax_add','account_id','freight_add','po_header_id');

			for ($c=0;$c<count($fields_header);$c++)
			{
				$aRR[$fields_header[$c]] = $form[$fields_header[$c]];
				if ($aRR[$fields_header[$c]] == '')
				{
					$aRR[$fields_header[$c]] = 0;
				}
				elseif (substr($fields_header[$c],0,4) =='date')
				{
					$aRR[$fields_header[$c]] = mdy2ymd($form[$fields_header[$c]]);
				}
			}			
		}
		elseif ($return_function == 'por_select')
		{
			global $aPOR;

			$fields_header = array('date', 'invoice', 'gross_amount', 
							'disc1', 'disc1_type','disc2', 'disc2_type','disc3', 'disc3_type',
							'tax_add','account_id','freight_add','rr_header_id');

			for ($c=0;$c<count($fields_header);$c++)
			{
				$aPOR[$fields_header[$c]] = $form[$fields_header[$c]];
				if ($aPOR[$fields_header[$c]] == '')
				{
					$aPOR[$fields_header[$c]] = 0;
				}
				elseif (substr($fields_header[$c],0,4) =='date')
				{
					$aPOR[$fields_header[$c]] = mdy2ymd($form[$fields_header[$c]]);
				}
			}			
		}
		elseif ($return_function == 'po_select')
		{
			global $aPO;

			$fields_header = array('date', 'invoice', 'gross_amount', 
							'disc1', 'disc1_type','disc2', 'disc2_type','disc3', 'disc3_type',
							'tax_add','account_id','freight_add','category_from','category_to');

			for ($c=0;$c<count($fields_header);$c++)
			{
				$aPO[$fields_header[$c]] = $form[$fields_header[$c]];
				if ($aPO[$fields_header[$c]] == '')
				{
					$aPO[$fields_header[$c]] = 0;
				}
				elseif (substr($fields_header[$c],0,4) =='date')
				{
					$aPO[$fields_header[$c]] = mdy2ymd($form[$fields_header[$c]]);
				}
			}			
		}
		elseif ($return_function == 'stocktransfer_select')
		{
			global $aST;

			$fields_header = array('date', 'branch_id_to');

			for ($c=0;$c<count($fields_header);$c++)
			{
				$aST[$fields_header[$c]] = $form[$fields_header[$c]];
				if ($aST[$fields_header[$c]] == '')
				{
					$aST[$fields_header[$c]] = 0;
				}
				elseif (substr($fields_header[$c],0,4) =='date')
				{
					$aST[$fields_header[$c]] = mdy2ymd($form[$fields_header[$c]]);
				}
			}			
		}
		elseif ($return_function == 'invadjust_select')
		{
			global $aIA;

			$fields_header = array('date', 'branch_id');

			for ($c=0;$c<count($fields_header);$c++)
			{
				$aIA[$fields_header[$c]] = $form[$fields_header[$c]];
				if ($aIA[$fields_header[$c]] == '')
				{
					$aIA[$fields_header[$c]] = 0;
				}
				elseif (substr($fields_header[$c],0,4) =='date')
				{
					$aIA[$fields_header[$c]] = mdy2ymd($form[$fields_header[$c]]);
				}
			}			
		}
		elseif ($return_function == 'pc_select')
		{
			global $aPHYC;

			$fields_header = array('date', 'account_id');

			for ($c=0;$c<count($fields_header);$c++)
			{
				$aPHYC[$fields_header[$c]] = $form[$fields_header[$c]];
				if ($aPHYC[$fields_header[$c]] == '')
				{
					$aPHYC[$fields_header[$c]] = 0;
				}
				elseif (substr($fields_header[$c],0,4) =='date')
				{
					$aPHYC[$fields_header[$c]] = mdy2ymd($form[$fields_header[$c]]);
				}
			}			
		}
		$searchby = $form['searchitemby'];
		$searchkey = $form['searchkey'];
		$account_id = $form['account_id'];

		if ($searchby == '')
		{
			$searchby = 'stock';
		}

		$found = 0;
		if ($searchby == 'stock' || $searchby == '' || $searchby=='stock_description')
		{
			pc_searchStock($searchby, $searchkey, $return_function, $return_focus, $account_id);
		}
		else
		{
			$q = "select *
					from
						stock
					where
						$searchby='$searchkey' and
						enable='Y'";

			$qr = @pg_query($q);
			if (!$qr)
			{
				galert("Error Query in pc_search :". pg_errormessage().$q);
			}
			elseif (@pg_num_rows($qr) == 0)
			{
				galert(" Item NOT found...");
			}
			else
			{
				$r = @pg_fetch_assoc($qr);
				$asid = $r;
				$found = 1;
			}
			if ($found == 1)
			{
				if ($return_function == 'pc_select')
				{
					pc_select($asid);
				}
				elseif ($return_function == 'rr_select')
				{
					rr_select($asid);
				}
				elseif ($return_function == 'por_select')
				{
					por_select($asid);
				}
				elseif ($return_function == 'po_select')
				{
					po_select($asid);
				}
				elseif ($return_function == 'stocktransfer_select')
				{
					stocktransfer_select($asid);
				}
				elseif ($return_function == 'invadjust_select')
				{
					invadjust_select($asid);
				}
				$focus = "document.getElementById('$return_focus').focus()";
				gscript($focus);

			}
		}
		return done();
	}


	//$xajax->registerFunction('pc_searchStock');
	function pc_searchStock($searchby, $searchkey, $return_function, $return_focus, $account_id) 
	{
        	$m = "<table width=\"99%\" height=\"1%\" border=\"0\" align=\"center\" cellpadding=\"2\" cellspacing=\"1\" bgcolor=\"#EFEFEF\">
	        	  <tr> 
	            <td width=\"9%\"><strong><font size=\"2\" face=\"Verdana, Arial, Helvetica, sans-serif\">#</font></strong></td>
    	          <td width=\"20%\"><strong><font size=\"2\" face=\"Verdana, Arial, Helvetica, sans-serif\">Barcode</font></strong></td>
        	      <td width=\"55%\"><strong><font size=\"2\" face=\"Verdana, Arial, Helvetica, sans-serif\">Item 
            	    Description </font></strong></td>
        	      <td width=\"5%\"><strong><font size=\"2\" face=\"Verdana, Arial, Helvetica, sans-serif\">U/C</font></strong></td>
        	      <td width=\"11%\"><strong><font size=\"2\" face=\"Verdana, Arial, Helvetica, sans-serif\">Cost/Case </font></strong></td>
          	</tr>";
		  	$q = "select * from stock 
		  				where 
  							enable='Y'";
  			if ($searchkey  != '')
  			{
  				$q .= " and $searchby ilike '%$searchkey%'";
  			} 
  			else
  			{
  				$q .= " and account_id = '$account_id'";
  			}
  			$q .= " order by $searchby ";
  			if ($searchkey == '')
  			{
  				$q .= "  offset 0 limit 50 ";
  			}

  			$qr = @pg_query($q) ;
			if (!$qr)
			{
				galert(pg_errormessage());
			}
			elseif (@pg_num_rows($qr) == 0)
			{
				galert(" Search NOT found...");
				return;
			}
	  		$ctr=0;
  			while ($r = @pg_fetch_object($qr))
  			{
  				$ctr++;
  				
				$href ="onClick=\"wait('Loading data...');hW();xajax_select_sid('$r->stock_id', '$return_function', '$return_focus');return false;\"";
				if ($r->stock_description != '')
				{
					$stock = $r->stock_description;
				}
				else
				{
					$stock = $r->stock;
				}
          
          	$_aid= 'L'.$ctr;
          	$_bid = 'M'.$ctr;
				$href2 = " onClick=\"document.getElementById('$_bid').click()\"";

		  		$m .= "<tr  class=\"gridRow\" id=\"$_aid\" $href2> 
		  				<td align=\"right\">$ctr<input type=\"button\" class=\"gridbutton\" $href id=\"$_bid\"> </td>
            			<td>$r->barcode</td>
              			<td> $stock</td>
              			<td>  $r->fraction3</td>
              			<td align='right'>".number_format($r->cost3,2)."</td>
	          		</tr>";

	  		}
     		$m .= "   </table>";
			show_layer('browsePLULayer');
	 		glayer('innerPLULayer',$m);
	 		
			$hi = "if (document.getElementById('L1')){document.getElementById('line_no').value='1';document.getElementById('L1').style.background='#000CCC';
				document.getElementById('L1').style.color='#FFFFFF';}";

	 		gscript($hi);
	 		return;
	}


	function rr_select($asid)
	{
		global $iRRD, $aRRD, $aRR;

		$sid = $asid['stock_id'];
		$fnd = 0;
		foreach ($aRRD as $temp)
		{
			if ($temp['stock_id'] == $sid)
			{
				$iRRD = $temp;
				$fnd=1;
			}				
		}

		if ($fnd == '0')
		{
			/*if ($aRR['rr_header_id'] == '' && $aRR['po_header_id'] !='')
			{
				galert(" This Item is NOT found in Purchase Order: ".$aRR['po_header_id']);
				gset('searchkey','');
				return;
			}
			else
			{*/
				$iRRD = $asid;
/*			}
*/		}
		if ($iRRD['rr_detail_id'] == '')
		{

			$balance_case = $balance_unit=0;
			if ($iRRD['case_order'] > 0)
			{
				$balance_case = intval($iRRD['balance_order']/$iRRD['fraction3']);
				$balance_unit = round($iRRD['balance_order'] - ($balance_case* $iRRD['fraction3']),0);
			}
			else
			{
				$balance_unit = $iRRD['balance_order'];
			}
			gset('case_qty', $balance_case);
			gset('unit_qty', $balance_unit);
		}	
		if ($iRRD['fraction2'] == 0 || $iRRD['fraction2'] == '') $iRRD['fraction2'] =1 ;
		if ($iRRD['fraction3'] == 0 || $iRRD['fraction3'] == '') $iRRD['fraction3'] =1 ;
		$iRRD['afraction'] = '1;'.$iRRD['fraction2'].';'.$iRRD['fraction3'];
	
		$iRRD['fraction'] = 1;
		$iRRD['price'] = $iRRD['cost1'];
		$iRRD['unit'] = 1;
	
		if ($aRR['tax_add'] > '0')
		{
			$iRRD['cost3_with_tax'] = $iRRD['cost3'];
			$iRRD['cost3'] =round($iRRD['cost3'] / (1+($aRR['tax_add']/100)),2);
			$iRRD['cost1'] = round($iRRD['cost3']/$iRRD['fraction3'],2);
		}
		gset('searchkey', $iRRD['barcode']); 
		gset('stock', $iRRD['stock']) ;
		gset('fraction3', $iRRD['fraction3']) ;
		gset('cost3',$iRRD['cost3']) ;
		gset('cost2',$iRRD['cost2']) ;
		gset('cost1',$iRRD['cost1']) ;
		gset('balance_order',$iRRD['balance_order']);

		return;	
	}
		
	function por_select($asid)
	{
		global $iPORD, $aPOR, $aPOR_RRD;

		$sid = $asid['stock_id'];
		
		$fnd=0;
		foreach ($aPOR_RRD as $temp)
		{
			if ($temp['stock_id'] == $sid)
			{
				$fraction3 = $asid['fraction3'];
				if ($fraction3*1 == '0') $fraction3 = 1;
				
				$asid['case_order'] = $temp['case_qty'];
				$asid['unit_order'] = $temp['unit_qty'];
					
				gset('case_order',$asid['case_qty']);
				gset('unit_order',$asid['unit_qty']);			
				gset('balance_order', $asid['case_qty']*$fraction3 + $asid['unit_qty']);
				$fnd=1;
				break;
			}
		}
		/*if ($fnd == '0' && $aPOR['rr_header_id'] != '')
		{
			galert(' Barcode NOT found on this Stocks Receiving Report....');
			gset('barcode','');
			return;
		}*/

		$iPORD = $asid;
		
		if ($iPORD['stock_description'] != '')
		{
			$iPORD['stock'] = $iPORD['stock_description'];
		}
		if ($iPORD['fraction2'] == 0 || $iPORD['fraction2'] == '') $iPORD['fraction2'] =1 ;
		if ($iPORD['fraction3'] == 0 || $iPORD['fraction3'] == '') $iPORD['fraction3'] =1 ;
		$iPORD['afraction'] = '1;'.$iPORD['fraction2'].';'.$iPORD['fraction3'];
	
		$iPORD['fraction'] = 1;
		$iPORD['price'] = $iPORD['cost1'];
		$iPORD['unit'] = 1;
	
		if ($aPOR['tax_add'] > '0')
		{
			$iPORD['cost3_with_tax'] = $iPORD['cost3'];
			$iPORD['cost3'] =round($iPORD['cost3'] / (1+($aPOR['tax_add']/100)),2);
			$iPORD['cost1'] = round($iPORD['cost3']/$iPORD['fraction3'],2);
		}
		gset('searchkey', $iPORD['barcode']); 
		gset('stock', $iPORD['stock']) ;
		gset('fraction3', $iPORD['fraction3']) ;
		gset('cost3',$iPORD['cost3']) ;
		gset('cost2',$iPORD['cost2']) ;
		gset('cost1',$iPORD['cost1']) ;

		return;	
	}

	function po_select($asid)
	{
		global $iPOD, $aPO;

		$sid = $asid['stock_id'];

		$iPOD = $asid;
		
		$iPOD['stock_id'] = $asid['stock_id'];
		
		if ($iPOD['stock_description'] != '')
		{
			$iPOD['stock'] = $iPOD['stock_description'];
		}
		if ($iPOD['fraction2'] == 0 || $iPOD['fraction2'] == '') $iPOD['fraction2'] =1 ;
		if ($iPOD['fraction3'] == 0 || $iPOD['fraction3'] == '') $iPOD['fraction3'] =1 ;
		$iPOD['afraction'] = '1;'.$iPOD['fraction2'].';'.$iPOD['fraction3'];
	
		$iPOD['fraction'] = 1;
		$iPOD['price'] = $iPOD['cost1'];
		$iPOD['unit'] = 1;
	
		if ($aPO['tax_add'] > '0')
		{
			$iPOD['cost3_with_tax'] = $iRRD['cost3'];
			$iPOD['cost3'] =round($iPOD['cost3'] / (1+($aPO['tax_add']/100)),2);
			$iPOD['cost1'] = round($iPOD['cost3']/$iPOD['fraction3'],2);
		}
		gset('searchkey', $iPOD['barcode']); 
		gset('stock', $iPOD['stock']) ;
		gset('fraction3', $iPOD['fraction3']) ;
		gset('cost3',$iPOD['cost3']) ;
		gset('cost2',$iPOD['cost2']) ;
		gset('cost1',$iPOD['cost1']) ;
		gset('stock_id',$iPOD['stock_id']) ;

		return;	
	}

	function stocktransfer_select($asid)
	{
		global $iSTD, $aST;

		$sid = $asid['stock_id'];

		$iSTD = $asid;
		
		if ($iSTD['stock_description'] != '')
		{
			$iSTD['stock'] = $iSTD['stock_description'];
		}
		if ($iSTD['fraction2'] == 0 || $iSTD['fraction2'] == '') $iSTD['fraction2'] =1 ;
		if ($iSTD['fraction3'] == 0 || $iSTD['fraction3'] == '') $iSTD['fraction3'] =1 ;
		$iSTD['afraction'] = '1;'.$iSTD['fraction2'].';'.$iSTD['fraction3'];
	
		$iSTD['fraction'] = 1;
		$iSTD['unit'] = 1;

		gset('searchkey', $iSTD['barcode']); 
		gset('stock', $iSTD['stock']) ;
		gset('fraction3', $iSTD['fraction3']) ;

		return;	
	}


	function invadjust_select($asid)
	{
		global $iIAD, $aIA;

		$sid = $asid['stock_id'];

		$iIAD = $asid;
		
		if ($iIAD['stock_description'] != '')
		{
			$iIAD['stock'] = $iIAD['stock_description'];
		}
		if ($iIAD['fraction2'] == 0 || $iIAD['fraction2'] == '') $iIAD['fraction2'] =1 ;
		if ($iIAD['fraction3'] == 0 || $iIAD['fraction3'] == '') $iIAD['fraction3'] =1 ;
		$iIAD['afraction'] = '1;'.$iIAD['fraction2'].';'.$iIAD['fraction3'];
	
		$iIAD['fraction'] = 1;
		$iIAD['unit'] = 1;

		gset('searchkey', $iIAD['barcode']); 
		gset('stock', $iIAD['stock']) ;
		gset('fraction3', $iIAD['fraction3']) ;

		return;	
	}
	
	function pc_select($asid) 
	{
		global $aPHYC;

		$c=0;
		$sid = $asid['stock_id'];
		$found = 0;
		$q = "select 
						tmp.stockledger_id,
						tmp.stock_id,
						tmp.case_qty,
						tmp.unit_qty,
						tmp.cost3,
						stock.stock,
						stock.barcode,
						stock.stock_description,
						stock.fraction3,
						stock.unit1,
						stock.unit3
				 from ".
				 		$aPHYC['tmp_table']." as tmp ,
				 		stock
				  where 
				  		stock.stock_id = tmp.stock_id and
				  		tmp.stock_id = '$sid'";
		$qr = @pg_query($q);
		if (!$qr) galert("Error querying details...".pg_errormessage().$q);
		if (@pg_num_rows($qr) > 0)
		{
		
				$temp = @pg_fetch_assoc($qr);
				$ctr = $temp['stockledger_id'];
				$stock = $temp['stock'];
				if ($temp['stock_description'] != '')
				{
					$stock = $temp['stock_description'];
				}
				$searchkey = $temp['barcode']; 
				$stock_id = $temp['stock_id'];
				$icase_qty = $temp['case_qty'] ;
				$iunit_qty = $temp['unit_qty'] ;
				$icost3 = $temp['cost3'];

		}
		else
		{
			$ctr='';
			$stock = $asid['stock'];
			if (strlen(trim($asid['stock_description'])) > 10)
			{
				$stock = $asid['stock_description'];
			}

			$searchkey = $asid['barcode']; 
			$stock_id = $asid['stock_id'];
			$icase_qty = $asid['case_qty'] ;
			$iunit_qty = $asid['unit_qty'] ;
			$icost3 = $asid['cost3']*1;
			
			if ($icost3 == 0 && $asid['fraction3']<=1)
			{
				$icost3 = $asid['cost1']*1;
			}
			
		}

		gset('ctr', $c);
		gset('searchkey', $searchkey); 
		gset('stock_id', $stock_id);
		gset('stock', $stock) ;
		gset('icase_qty', $icase_qty) ;
		gset('iunit_qty', $iunit_qty) ;
		gset('icost3', $icost3) ;
		return;
	}

	
	function pc_save($form)
	{
		global $aPHYC, $ADMIN, $SYSCONF;
		if ($form['date'] =='')
		{
			galert('Please Specify Date of Physical Count...');
			return done();
		}


		$aPHYC['date'] = mdy2ymd($form['date']);
		$aPHYC['account_id'] = $form['account_id'];
		$aPHYC['category_id'] = $form['account_id'];
		if ($aPHYC['account_id'] == '') $aPHYC['account_id'] = 0;
		if ($aPHYC['category_id'] == '') $aPHYC['category_id'] = 0;
		

		$tables = currTables($aPHYC['date']);
		$sales_header = $tables['sales_header'];
		$sales_detail = $tables['sales_detail'];
		$sales_tender = $tables['sales_tender'];
		$stockledger = $tables['stockledger'];

		// galert($stockledger);return done();
		$date = date('Y-m-d');
		if ($aPHYC['phycount_id'] == '')
		{
			if (!@chkRights2("phycount","madd",$ADMIN['admin_id']))
			{
				galert("You have no permission to ADD Physical Count Items...");
				return done();
			}
			else
			{
				$q = "insert into phycount (date, date_updated, account_id,category_id,admin_id, audit)
					values ('".$aPHYC['date']."', '$date','".$aPHYC['account_id']."', '".$aPHYC['category_id']."', '".$ADMIN['admin_id']."', '$audit')";
				$qr = @pg_query($q);
				if (!$qr)
				{
					galert(pg_errormessage().$q);
					return done();
				}
				$id = @db_insert_id('phycount');
				$aPHYC['phycount_id'] = $id;
			}
		}
		else
		{
			if (!@chkRights2("phycount","medit",$ADMIN['admin_id']))
			{
				galert("You have no permission to UPDATE Physical Count Items...");
				return done();
			}
			else
			{
				$q = "update phycount set date_updated='$date', audit = '$audit' where phycount_id = '".$aPHYC['phycount_id']."'";
				$qr = @pg_query($q);
			}
		}
		$c=$uc=$um=$ic=$im = 0;
		$message = "";


		$q = "select * from ".$aPHYC['tmp_table']." where liwat='Y'";
		$qr = @pg_query($q);
		if (!$qr)
		{
			$message .= @pg_errormessage().$q;
		}
		
		while ($temp = @pg_fetch_assoc($qr))
		{

			if ($temp['stockledger_id'] == '0' or $temp['stockledger_id'] == '')
			{
				if ($temp['case_qty'] == '0'  && $temp['unit_qty'] == '0')
				{
				 continue;
				}

				$q = "insert into $stockledger (date,phycount_id, stock_id, case_qty,unit_qty, cost3, type,  admin_id) 
							values ('".$aPHYC['date']."', '".$aPHYC['phycount_id']."', '".$temp['stock_id']."', '".$temp['case_qty']."',
											'".$temp['unit_qty']."','".$temp['cost3']."', 'E', '".$ADMIN['admin_id']."')";
				$qqr = @pg_query($q);

				if ($qqr)
				{
					if (substr($aPHYC['date'],0,4) > 2007)
					{
						$id = @db_insert_id("stockledger");
					}
					else
					{
						$seq = 'sl_'.substr($aPHYC['date'],0,4).'_stockledger_id_seq';
   				   $Q = "select currval('".$seq."'::text)";
						$QR = @pg_query($Q);
						if (!$QR)
						{	 
								galert(pg_errormessage().' '.$Q);
								return done();
						}
						$R 	= @pg_fetch_object($QR);
						$id = $R->currval;
					}

					$qq = "update ".$aPHYC['tmp_table']." set stockledger_id = '$id' where stock_id = '".$temp['stock_id']."'";
					@pg_query($qq);
	
					$ic++;
				}
				else
				{

					$im++;
					$message .= "Errors occured during saving...".pg_errormessage();
					break;
				}

			}
			else
			{
				$q = "update $stockledger set
							phycount_id ='".$aPHYC['phycount_id']."', 
							case_qty = '".$temp['case_qty']."',
							unit_qty = '".$temp['unit_qty']."',
							cost3 = '".$temp['cost3']."',
							date = '".$aPHYC['date']."'
						where
							stockledger_id = '".$temp['stockledger_id']."'";

				$qqr = @pg_query($q);
				if ($qqr)
				{
					$uc++;
				}
				else
				{
					$um++;
					$message .= "Errors occured during update...";
				}
			}
			
			$c++;
		}

		glayer('wait.layer','');
		galert(" Physical Count Data Successfully Saved...\n$message Inserted=$ic Updated= $uc Inserts Failed=$im Updates Failed=$um");

		return done();
	}
	
	function download($form) 
	{
		
		$date = $form['date'];
		$filename = $form['filename'];
		
		if ($date == '')
		{
			glayer('message.layer','No date specified...'.$form['date']);
			return done();
		}
		
		if ($filename == '')
		{
			glayer('message.layer','No Filename specified...'.$form['filename']);
			return done();
		}
		$mdate = mdy2ymd($date);
		
		$q = "select 
				stock.barcode,
				sales_detail.qty,
				sales_detail.price,
				sales_detail.amount,
				stock.ccategory,
				stock.category_id
				
			from
				stock,
				sales_header,
				sales_detail
			where
				stock.stock_id=sales_detail.stock_id and
				sales_header.sales_header_id=sales_detail.sales_header_id and
				sales_header.status!='V' and
				sales_header.date='$mdate'";
			
		$qr = @pg_query($q);
		if (!$qr)
		{
			glayer('message.layer','Error querying...'.pg_errormessage());
			return done();
		}
		if (@pg_num_rows($qr) <= 0)
		{
			glayer('message.layer','No DATA Found for this transaction date...');
			return done();
    }
		
		$d = explode('-',$mdate);
		$d1 = substr($d[0],2,2).$d[1].$d[2];
		$ln = "\"H\",\"POS MACHS\", \"$d1\", 1,\"       \",0"."\n";
		
		while ($r = @pg_fetch_object($qr))
		{
			if ($r->category_id > 0)
			{
				$q = "select category_code from category where category_id = '$r->category_id'";
				$qqr = @pg_query($q);
				$rr = @pg_fetch_object($qqr);
				$category_code = $rr->category_code;
			}
			else
			{
				$category_code = $r->ccategory;
			}	
			$ln .= "\"D\",".
				"\"".adjustSize($r->barcode,12)."\",".
				"\"".adjustSize($category_code,4)."\",".
				"\"A2\",".
				"\"".space(10)."\",".
				number_format($r->qty,2).",".
				"\"".space(10)."\",".
				number_format($r->amount,2).",".
				number_format($r->price,2)."\n";
				
		}
		
		$fl = "../ics/".$filename;
		
  		$handle = @fopen($fl,"w+");
  		if (!$handle)
  		{
  			glayer('message.layer','Cannot create file...'.$fl);
  			return done();
  		}
  		
  		$w = @fwrite($handle,$ln);
  		if (!$w)
  		{
  			glayer('message.layer','Cannot write into file...'.$fl);
  			return done();
  		}
  		fclose($handle);
  			
  		$t = "<table align=\"center\">";
  		
  		$t .= "<tr><td><a href='$fl'><h3>$filename</a></h3></td></tr>";
  		$t .="</table>";
  		
	 	 glayer('grid.layer',$t);
					
		  glayer('message.layer','Finished Processing data...Please Click File To Download...Date: '.$mdate);

		return done();
	}

	function porder_supplier_select_id($sid) 
	{
			//--select from browse
		
			$q = "select *
					from
						account
					where
						account_id='$sid' and
						enable='Y'";

			$qr = @pg_query($q);

			if (!$qr)
			{
				galert("Error Query in Supplier Accounts:\n". pg_errormessage().$q);
			}
			elseif (@pg_num_rows($qr) == 0)
			{
				galert(" Supplier NOT found...");
			}
			else
			{

				$r = @pg_fetch_assoc($qr);
				$asid = $r;
				$found = 1;
			}

			if ($found == 1)
			{
				gset('account', $r['account']);
				gset('account_id',$r['account_id']);
				gset('account_code',$r['account_code']);
				gset('terms',$r->terms);
				
			}
						
		$focus = "document.getElementById('searchkey').focus()";
		
		gscript($focus);
		return done();
	}

	function porder_searchaccount($form)
	{
		
		$account = $form['account'];
		$q .= "select * from account where account_type_id !='5' and  account_code = '$account'";
		$qr = @pg_query($q);
		if (!$qr)
		{
			galert('Error Querying Supplier Accounts ... '.pg_errormessage().$q);
		}
		
		if (@pg_num_rows($qr) > 0)
		{
			$r = @pg_fetch_object($qr);

			gset('account_id',$r->account_id);
			gset('account',$r->account);
			gset('account_code',$r->account_code);
			gset('terms',$r->terms);

			$focus = "document.getElementById('disc1').focus()";
			gscript($focus);

			return done();
		}
		else
		{
			$q = "select * from account where account_type_id !='5' and account ilike '$account%'";
			$qr = @pg_query($q);
			if (!$qr)
			{
				galert('Error Querying Supplier Accounts ... '.pg_errormessage().$q);
				return done();
			}
        	$m = "<table width=\"99%\" height=\"1%\" border=\"0\" align=\"center\" cellpadding=\"2\" cellspacing=\"1\" bgcolor=\"#EFEFEF\">
	        	  <tr> 
	            <td width=\"9%\"><strong><font size=\"2\" face=\"Verdana, Arial, Helvetica, sans-serif\">#</font></strong></td>
    	          <td width=\"20%\"><strong><font size=\"2\" face=\"Verdana, Arial, Helvetica, sans-serif\">AccountCode</font></strong></td>
        	      <td width=\"71%\"><strong><font size=\"2\" face=\"Verdana, Arial, Helvetica, sans-serif\">Name
            	    Description </font></strong></td>
          	</tr>";
  			while ($r = @pg_fetch_object($qr))
  			{
  				$ctr++;
	
				$href =" onClick=\"wait('Loading data...');hW();xajax_porder_supplier_select_id('$r->account_id');return false;\"";
          
          	$_aid= 'L'.$ctr;
          	$_bid = 'M'.$ctr;
				$href2 = " onClick=\"document.getElementById('$_bid').click()\"";


		  		$m .= "<tr  class=\"gridRow\" id=\"$_aid\" $href2> 
		  				<td align=\"right\">$ctr<input type=\"button\" class=\"gridbutton\" $href id=\"$_bid\"> </td>
             			</td>
            			<td> 
              			$r->account_code</td>
              			<td> 
                		$r->account</td>
	          		</tr>";

	  		}
     		$m .= "   </table>";
			show_layer('browsePLULayer');
	 		glayer('innerPLULayer',$m);

			$hi = "if (document.getElementById('L1')){document.getElementById('line_no').value='1';document.getElementById('L1').style.background='#000CCC';
				document.getElementById('L1').style.color='#FFFFFF';}";

	 		gscript($hi);

			return done();
		}
	}	
?>