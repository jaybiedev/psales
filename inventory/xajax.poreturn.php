<?
	function porCheckRR($form)
	{
		global $aPOR, $aPOR_RRD, $aPORD;
		
		$rr_header_id = $form['rr_header_id'];
		$aPOR['rr_header_id'] = $rr_header_id;
		
		if ($rr_header_id*1 == '0') return done();

		$q = "select * from rr_header where rr_header_id='$rr_header_id'";
		$qr = @pg_query($q);
		if (!$qr)
		{
			gset('rr_header_id',$aPOR['rr_header_id']);
			galert('Error searching SRR...'.pg_errormessage());
		}
		elseif (@pg_num_rows($qr) == '0')
		{
			galert(' Stocks Receiving NOT found...');
			gset('rr_header_id',$aPOR['rr_header_id']);
		}
		else
		{
			$r = @pg_fetch_object($qr);
			if ($r->status == 'C')
			{
				galert(' Stocks Receiving is CANCELLED...'."\n".'Cannot make PO Return...');
				gset('rr_header_id',$aPOR['rr_header_id']);
			}
			else
			{
				galert(" SRR# $rr_header_id found...");

				$qq = "select * from account where account_id = '$r->account_id'";
				$qqr = @pg_query($qq);

				if (!$qqr)
				{
					galert('Error Checking Supplier...'.pg_errormessage());
				}
				$rr = @pg_fetch_object($qqr);
				$aPOR['rr_header_id']=$rr_header_id;
				$aPOR['reference']=$r->invoice;
				$aPOR['account'] = $rr->account;
				$aPOR['account_id'] = $rr->account_id;
				$aPOR['account_code'] = $rr->account_code;
				$aPOR['invoice_date'] = $r->invoice_date;
				$aPOR['date_received'] = $r->date;
				
				
				if ($aPOR['date'] == '--' or $aPOR['date']=='//' or $aPOR['date']=='')
				{
					$aPOR['date']=date('Y-m-d');
					gset('date',date('m/d/Y'));
				}
				
				if ($aPOR['invoice_date'] == '--' or $aPOR['invoice_date']=='//' or $aPOR['invoice_date']=='')
				{
					$aPOR['invoice_date']="";
					
				}
				
				if ($aPOR['date_received'] == '--' or $aPOR['date_received']=='//' or $aPOR['date_received']=='')
				{
					$aPOR['date_received']="";
					
				}

				gset('account',$aPOR['account']);
				gset('account_code',$aPOR['account_code']);
				gset('account_id',$aPOR['account_id']);
				gset('reference',$aPOR['reference']);
				gset('invoice_date',ymd2mdy($aPOR['invoice_date']));
				gset('date_received',ymd2mdy($aPOR['date_received']));
				
				$aPORD=null;
				$aPORD=array();
				 	
				$aPOR_RRD=null;
				$aPOR_RRD=array();
				$net_amount = $gross_amount = 0;
				
				$q= "select 
							stock.stock_description,
							stock.stock,
							stock.stock_id,
							stock.barcode,
							stock.fraction3,
							stock.fraction2,
							value1 as case_qty,
							value2 as unit_qty
						from 
							error_table,
							stock 
						where 
							error_table.detail_id=stock.stock_id and
							record_id = '".$aPOR['rr_header_id']."' and
							mtable='RR' and
							error_table.enable='Y'";
				$qr = @pg_query($q);
				
				if (!$qr)
				{
					galert('Unable To Load SRR Errors...'.@pg_errormessage().$q);
				}
				else
				{
					while ($r = @pg_fetch_assoc($qr))
					{
						if ($r['case_qty'] == '0' && $r['unit_qty'] == '0')
						{
							continue;
						}
						$dummy['case_order'] = 0;
						$dummy['unit_order'] = 0;
						$dummy['cost1'] = 0;
						$dummy['cost2'] = 0;
						$dummy['cost3'] = 0;
						$dummy['freight_case'] = 0;
						$dummy['amount'] = 0;
						$aPORD[] = $r;

					}
				}
				
				$q= "select * from rr_detail where rr_header_id = '".$aPOR['rr_header_id']."'";
				$qr = @pg_query($q);
				if (!$qr)
				{
					galert('Unable To Load SRR...'.@pg_errormessage().$q);
				}
				else
				{
					while ($r = @pg_fetch_assoc($qr))
					{
						$aPOR_RRD[] = $r;
						$cc=0;
						foreach ($aPORD as $temp1)
						{
							if ($temp1['stock_id'] == $r['stock_id'])
							{
								$dummy = $temp1;
								$dummy['case_order'] = $r['case_qty'];
								$dummy['unit_order'] = $r['unit_qty'];
								$dummy['cost1'] = $r['cost1'];
								$dummy['cost2'] = $r['cost2'];
								$dummy['cost3'] = $r['cost3'];
								$dummy['freight_case'] = $r['freight_case'];
								
								if ($dummy['cost1'] == '')
								{
									$dummy['cost1'] = 0;
								}
								if ($dummy['cost2'] == '')
								{
									$dummy['cost2'] = 0;
								}
								if ($dummy['cost3'] == '')
								{
									$dummy['cost3'] = 0;
								}
								if ($dummy['freight_case'] == '')
								{
									$dummy['freight_case'] = 0;
								}
								if ($dummy['fraction3']*1 == '0')
								{
									$dummy['fraction3'] = 1;
								}
								$dummy['amount'] = $dummy['cost1']*$temp1['unit_qty'] + $dummy['cost3']*$temp1['case_qty'];
								$aPORD[$cc]= $dummy;
								$net_amount += $dummy['amount'];

								
								break;
							}
							$cc++;
						}
					}
				}
				$aPOR['net_amount'] = $aPOR['gross_amount'] = $net_amount;
				gset('net_amount',$aPOR['net_amount']);
				gset('gross_amount',$aPOR['gross_amount']);
				
				porGrid();

			}
		}
		return done();
	}
	
	function porLoad()
	{
		global $aPOR, $aPORD;
		porGrid();
		return done();
	}

	function porDelete($form)
	{
		global $aPORD;
		
		$aChk = $form['aChk'];
		
		$newArray=null;
		$newArray=array();
		$nctr=0;
		foreach ($aPORD as $temp)
		{
			$nctr++;
			if (in_array($nctr,$aChk))
			{
				if ($temp['por_detail_id'] != '')
				{
					$qr = @query("delete from por_detail where por_detail_id='".$temp['por_detail_id']."'") or message1(db_error());
					if (!$qr)
					{
						galert("FATAL error: Was not able to delete from Stocks Receiving Report detail file...".pg_errormessage($qr).$q);
						return done();
					}
				}		
			}
			else
			{
				$newArray[]=$temp;
			}
		}
		$aPORD = $newArray;	
	
		porGrid();
		return done();
	}	

	function porOk($form)
	{
		global $aPOR, $aPORD, $iPORD;
		
		if ($aPOR['account_id'] == '0' || $aPOR['account_id'] == '')
		{
			galert(" Please specify Supplier...");
			return done();
		}
		$aPOR['status']='M';
		$dummy = null;
		$dummy = array();
		
		$fields = array('cost1','cost2','cost3',
				'case_qty','unit_qty','freight_case','amount');
		for ($c=0;$c<count($fields);$c++)
		{
			$iPORD[$fields[$c]] = $form[$fields[$c]];
			if ($iPORD[$fields[$c]] =='')
			{
				$iPORD[$fields[$c]]=0;
			}
		}
	
		$fnd=0;
		/*$c=0;
		foreach ($aPORD as $temp)
		{
			if ($temp['stock_id'] == $iPORD['stock_id'])
			{
				$dummy = $temp;
				$dummy['case_qty'] = $iPORD['case_qty'];
				$dummy['unit_qty'] = $iPORD['unit_qty'];
				
				$dummy['case_order'] = $iPORD['case_order'];
				$dummy['unit_order'] = $iPORD['unit_order'];

				$dummy['cost1'] = $iPORD['cost1'];
				$dummy['cost2'] = $iPORD['cost2'];
				$dummy['cost3'] = $iPORD['cost3'];
				$dummy['freight_case'] = $iPORD['freight_case'];
				$dummy['amount'] = $iPORD['amount'];
				$dummy['stock'] = $iPORD['stock'];
				$dummy['qty1'] = $iPORD['qty1'];
				$aPORD[$c] = $dummy;
				$fnd = 1;
				break;
			}
			$c++;
		}*/
		
		if ($fnd == '0' && $aPOR['po_header_id'] != '')
		{
			galert(" Item NOT found on Purchase Order: ".$aPOR['po_header_id']);
		}
		elseif($fnd == '0')
		{
				$aPORD[] = $iPORD;
		}
		$iPORD = null;
		$iPORD = array();
		gset('searchkey','');
		gset('stock','');
		gset('fraction3','');
		gset('cost1','');
		gset('cost2','');
		gset('cost3','');
		gset('case_qty','');
		gset('unit_qty','');
			
		$focus = 'searchkey';
		gfocus($focus);
		porGrid();
		return done();
	}
	
	function porEdit($id)
	{
		global $aPOR, $aPORD, $iPORD;

		if ($id == '')
		{
			galert(" No Purchase Return Selected...");
			return done();
		}
		$iPORD = null;
		$iPORD = array();
		$c=0;
		$revPORD = array_reverse($aPORD);
		foreach ($revPORD as $temp)
		{
			$c++;
			if ($id == $c)
			{
				$iPORD = $temp;
				$iPORD['line_ctr'] = $c;
				$searchkey=$temp['barcode'];

				break;
			}
		
		}
		gset('searchkey',$iPORD['barcode']);
		gset('stock',$iPORD['stock']);
	//	gset('unit',$iPORD['unit']);
	//	gset('fraction2',$iPORD['fraction2']);
		gset('fraction3',$iPORD['fraction3']);
		gset('cost1',$iPORD['cost1']);
		gset('cost2',$iPORD['cost2']);
		gset('cost3',$iPORD['cost3']);
		gset('case_qty',$iPORD['case_qty']);
		gset('unit_qty',$iPORD['unit_qty']);
	//	gset('line_ctr',$iPORD['line_ctr']);
			
		$focus = 'case_qty';
		gfocus($focus);

		return done();
	}

	function porGrid()
	{
			global $aPOR, $aPORD;
			
			$table = "<table width='100%' cellpadding='0' cellspacing='0' bgcolor='#EFEFEF'>";

			$gross_amount=$freight_amount = 0;
			$revPORD = array_reverse($aPORD);
			foreach ($revPORD as $temp)
			{
				$gross_amount += $temp['amount'];

				$cunit='';
				if ($temp['case_qty'] != '0')
				{
					$cunit = $temp['unit3'];
				}		
				if ($temp['unit_qty'] != '0')
				{
					if ($cunit!='')$cunit.='/';
					$cunit .= $temp['unit1'];
				}		
				
				$cqty = intval(($temp['case_qty']*$temp['fraction3'] + $temp['unit_qty'])/$temp['fraction3']);
		
				if ($temp['case_qty'] ==  intval($temp['case_qty']))
				{
					$case_qty = $temp['case_qty'];
				}
				else
				{
					$case_qty = round($temp['case_qty'],3);
				}
				if ($temp['unit_qty'] ==  intval($temp['unit_qty']))
				{
					$unit_qty = $temp['unit_qty'];
				}
				else
				{
					$unit_qty = round($temp['unit_qty'],3);
				}

				$freight_amount += $cqty * $temp['freight_case'];

				if ($temp['fraction'] == '' || $temp['fraction']==0) $temp['fraction'] =1;
				$stock = trim($temp['stock']);
				$c++;
		

				$total_order = $temp['case_order']*$temp['fraction3'] + $temp['unit_order'];
				$total_deliver = $temp['case_qty']*$temp['fraction3'] + $temp['unit_qty'] + $total_order - $temp['balance_order'];
				
				//if ($temp['case_qty'] > 0 || $temp['unit_qty'] > 0)
				if ($total_order <= $total_deliver)
				{
					$bgcolor = '#CCCCFF';
				}		
				elseif ($total_deliver == '0')
				{
					$bgcolor = '#EFEFEF';
				}
				else
				{
					//--partial
					$bgcolor = '#FFFFCC';
				}
				
				$href = "onClick=\"xajax_porEdit('$c')\"";
				$table .= "<tr class='grid' bgColor=\"$bgcolor\" onMouseOver=\"bgColor='#FFFF99'\" onMouseOut=\"bgColor='$bgcolor'\">
								<td align='right' width='5%'>$c. <input name=\"aChk[]\" type=\"checkbox\" id=\"aChk\" value=\"$c\"></td>
								<td width='15%'> &nbsp;<a href='#' $href >".$temp['barcode']."</a></td>
								<td width='30%'><a href='#' $href >".stripslashes($temp['stock'])."</a></td>
								<td width='6%'><a href='#' $href >".$cunit."</a></td>
								<td align='right'width='9%'><a href='#' $href >".intval($temp['case_order']).':'.$temp['unit_order'].'('.$temp['balance_order'].")</a></td>
								<td align='right'width='5%'><a href='#' $href >".$case_qty .':'. $unit_qty."</a></td>
								<td align='right' width='6%'>".number_format($temp['freight_case'],2)."</td>
								<td align='right' width='5%'>".($aPOR['status'] == 'A' ? number_format($temp['cost1'],2):number_format($temp['cost1'],2))."</td>
								<td align='right' width='5%'>".($aPOR['status'] == 'A' ? number_format($temp['cost3'],2):number_format($temp['cost3'],2))."</td>
								<td align='right' width='5%'>".($aPOR['status'] == 'A' ? number_format($temp['amount_out'],2):number_format($temp['amount'],2))."</td>
								</tr>";
			}

			$table .= "</table>";
			
			por_subtotalCompute($gross_amount);
			glayer('gridLayer',$table);
			return;
	}

	function por_subtotal($form)
	{
		global $aPOR;
		$aPOR['status'] ='M';
		
		$q = "select * from por_header where por_header_id > '".$aPOR['date']."' and account_id = '".$aPOR['account_id']."'";
		$qr = @pg_query($q);
		if (@pg_num_rows($qr) == 0)
		{
			gset('updatecost','checked','1');
		}
		$fields = array('gross_amount','freight_add','tax_add','tax_add_type','disc1','disc1_type',
					'disc2','disc2_type','disc3','disc3_type');
					
		for ($c = 0;$c<count($fields);$c++)
		{
			$aPOR[$fields[$c]]=str_replace(',','',$form[$fields[$c]]);
		}
		
		por_subtotalCompute($aPOR['gross_amount']);		
		
		gset('gross_amount', number_format($aPOR['gross_amount'],2));
		gset('discount_amount', number_format($aPOR['discount_amount'],2));
		gset('sub_wo_freight', number_format($aPOR['sub_wo_freight'],2));
		gset('tax_amount', number_format($aPOR['tax_amount'],2));
		gset('net_amount', number_format($aPOR['net_amount'],2));
		gset('freight_amount', number_format($aPOR['freight_amount'],2));
		return done();
	}
	
	function por_subtotalCompute($gross_amount)
	{
		global $aPOR, $aPORD, $SYSCONF;
		if ($gross_amount == 0)
		{
			foreach ($aPORD as $temp)
			{
				$gross_amount += $temp['amount'];
			}
		}
		if ($aPOR['freight_add'] > 0)
		{
			$aPOR['freight_amount'] = $aPOR['freight_add'];
		}
		else
		{
			$aPOR['freight_amount'] = $freight_amount ;
		}
		$aPOR['gross_amount']  = $sub_gross = $gross_amount;
		$aPOR['total_items'] = $c;
		if ($aPOR['disc1_type']=='P')
		{
			$aPOR['discount_amount'] = round($sub_gross *  $aPOR['disc1']/100,2);
		}
		else
		{
			$aPOR['discount_amount'] = $aPOR['disc1'];
		}

		$sub_gross = $aPOR['gross_amount'] - $aPOR['discount_amount'];
		if ($aPOR['disc2_type']=='P')
		{
			$aPOR['discount_amount'] += round($sub_gross *  $aPOR['disc2']/100,2);
		}
		else
		{
			$aPOR['discount_amount'] += $aPOR['disc2'];
		}
		$sub_gross = $aPOR['gross_amount'] - $aPOR['discount_amount'];
		if ($aPOR['disc3_type']=='P')
		{
			$aPOR['discount_amount'] += round($sub_gross *  $aPOR['disc3']/100,2);
		}
		else
		{
			$aPOR['discount_amount'] += $aPOR['disc3'];
		}
		$sub_gross = $aPOR['gross_amount'] - $aPOR['discount_amount'];	
		$aPOR['net_amount'] = $aPOR['gross_amount'] +$aPOR['freight_amount'] - $aPOR['discount_amount'];
		$aPOR['sub_wo_freight'] = $aPOR['gross_amount'] - $aPOR['discount_amount'];
		$aPOR['sub_gross'] = $sub_gross;
		if ($aPOR['tax_add'] >  '0') //add on gross
		{
			if ($aPOR['tax_add_type'] == 'A')
			{
				$aPOR['tax_amount'] = $aPOR['tax_add'];
				$aPOR['net_amount'] += $aPOR['tax_amount'];
				$aPOR['sub_wo_freight'] += $aPOR['tax_amount'];
			}
			else
			{
				$aPOR['tax_amount'] = round($aPOR['sub_wo_freight'] * ($aPOR['tax_add']/100),2);
				$aPOR['net_amount'] += $aPOR['tax_amount'];
				$aPOR['sub_wo_freight'] += $aPOR['tax_amount'];
			}
		}	
		elseif ($SYSCONF['RR_FORMAT']=='LEC') //inclusive
		{
			$taxbase = 0;
			$aPOR['tax_amount'] = 0;
		}
		else //inclusive
		{
			$taxbase = round($aPOR['sub_wo_freight']/(1 + ($SYSCONF['TAXRATE']/100)),2);
			$aPOR['tax_amount'] = $aPOR['net_amount'] - $taxbase;
		}

		return;
	}

?>