<?
	
	function rrLoad()
	{
		global $aRR, $aRRD;
		rrGrid();
		return done();
	}

	function rrDelete($form)
	{
		global $aRRD;
		
		$aChk = $form['aChk'];
		
		$newArray=null;
		$newArray=array();
		$nctr=0;
		$revRRD = array_reverse($aRRD);

		foreach ($revRRD as $temp)
		{
			$nctr++;
			if (in_array($nctr,$aChk))
			{
				
				if ($temp['rr_detail_id'] != '')
				{
					$qr = @query("delete from rr_detail where rr_detail_id='".$temp['rr_detail_id']."'") or message1(db_error());
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
		$aRRD = array_reverse($newArray);	
	
		rrGrid();
		return done();
	}	

	function rrSearchPO($form)
	{
		global $aRR, $aRRD, $iRRD;

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

		$po_header_id = $aRR['po_header_id'];
		if ($po_header_id*1 > '0')
		{
			$q = "select po_header_id,account_code, account.account_id, account
							from 
								po_header,
								account
							where
								po_header.account_id = account.account_id and
								po_header.po_header_id = '$po_header_id'";
			$qr = @pg_query($q);
			if (!$qr)
			{
				galert(" Error Querying Purchase Order...".pg_errormessage()."\n".$q);
				
			}
			else
			{
				$r = @pg_fetch_object($qr);
				$aRR['account_id'] = $r->account_id;
				$aRR['account'] = $r->account;
				$aRR['account_code'] = $r->account_code;
				gset('account',$aRR['account']);							
				gset('account_id',$aRR['account_id']);							
				gset('account_code',$aRR['account_code']);							
			}
		}
		return done();			
	}

	function rrOk($form)
	{
		global $aRR, $aRRD, $iRRD;

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
		
		
		if ($aRR['account_id'] == '0' || $aRR['account_id'] == '')
		{
			galert(" Please specify Supplier...");
			return done();
		}
		$aRR['status']='M';
		$dummy = null;
		$dummy = array();
		
		$fields = array('cost1','cost2','cost3',
				'case_qty','unit_qty','freight_case','amount');
		for ($c=0;$c<count($fields);$c++)
		{
			$iRRD[$fields[$c]] = $form[$fields[$c]];
			if ($iRRD[$fields[$c]] =='')
			{
				$iRRD[$fields[$c]]=0;
			}
		}
	
		$fnd=0;
		$c=0;

		foreach ($aRRD as $temp)
		{
			if ($temp['stock_id'] == $iRRD['stock_id'] && $temp['unit_qty'] == 0 && $temp['case_qty'] == 0)
			{
				$dummy = $temp;
				$dummy['case_qty'] = $iRRD['case_qty'];
				$dummy['unit_qty'] = $iRRD['unit_qty'];
				$dummy['cost1'] = $iRRD['cost1'];
				$dummy['cost2'] = $iRRD['cost2'];
				$dummy['cost3'] = $iRRD['cost3'];
				$dummy['freight_case'] = $iRRD['freight_case'];
				$dummy['amount'] = $iRRD['amount'];
				$dummy['stock'] = $iRRD['stock'];
				$dummy['qty1'] = $iRRD['case_qty']*$temp['fraction3'] + $iRRD['unit_qty'];
				$aRRD[$c] = $dummy;
				$fnd = 1;
				break;
			}
			$c++;
		}
		
		if($fnd == '0')
		{
				$iRRD['qty1'] = $iRRD['case_qty']*$temp['fraction3'] + $iRRD['unit_qty'];

				$aRRD[] = $iRRD;
		}
		$iRRD = null;
		$iRRD = array();
		gset('searchkey','');
		gset('stock','');
		gset('fraction3','');
		gset('cost1','');
		gset('cost2','');
		gset('cost3','');
		gset('case_qty','');
		gset('unit_qty','');
		gset('amount','');
			
		$focus = 'searchkey';
		//gfocus($focus);
		rrGrid();
		return done();
	}
	
	function rrEdit($id)
	{
		global $aRRD, $iRRD;
		if ($id == '')
		{
			galert(" No SRR Selected...");
			return done();
		}
		$iRRD = null;
		$iRRD = array();
		$c=0;
		$revRRD = array_reverse($aRRD);
		foreach ($revRRD as $temp)
		{
			$c++;
			if ($id == $c)
			{
				$iRRD = $temp;
				$iRRD['line_ctr'] = $c;
				$searchkey=$temp['barcode'];

				break;
			}
		
		}
		gset('searchkey',$iRRD['barcode']);
		gset('stock',$iRRD['stock']);
	//	gset('unit',$iRRD['unit']);
	//	gset('fraction2',$iRRD['fraction2']);
		gset('fraction3',$iRRD['fraction3']);
		gset('cost1',$iRRD['cost1']);
		gset('cost2',$iRRD['cost2']);
		gset('cost3',$iRRD['cost3']);
		gset('amount',$iRRD['amount']);
		gset('case_qty',$iRRD['case_qty']);
		gset('unit_qty',$iRRD['unit_qty']);
		gset('balance_order',$iRRD['balance_order']);
	//	gset('line_ctr',$iRRD['line_ctr']);
			
		$focus = 'case_qty';
		//gfocus($focus);

		return done();
	}

	function rrGrid()
	{
			global $aRR, $aRRD;
			
			$table = "<table width='100%' cellpadding='0' cellspacing='0' bgcolor='#EFEFEF'>";

			$gross_amount=$freight_amount = 0;
			$revRRD = array_reverse($aRRD);
			
			$po_header_id = $aRR['po_header_id'];
			
			
			foreach ($revRRD as $temp)
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
		
				if ($$temp['balance_order']== '0')
				{
					$bgColor = '#D1EFFF';
				}
				elseif ($temp['balance_order'] < '0')
				{
					$bgColor = '#FFCCFF';
				}
				elseif ($temp['qty1'] > $temp['balance_order'])
				{
					$bgColor = '#FFCCFF';
				}
				elseif ($temp['qty1'] > '0')
				{
					$bgColor = '#66FFCC';
				}
				else
				{
					$bgColor = '#FFFFFF';
				}
				//galert($bgColor);
				
				
				$result = @pg_query("
					select
						sum(case_qty) as case_qty,
						sum(unit_qty) as unit_qty
					from
						rr_header as h, rr_detail as d
					where
						h.rr_header_id = d.rr_header_id 
					and
						h.po_header_id = '$po_header_id'
					and
						h.status != 'C'
					and
						d.stock_id = '{$temp['stock_id']}'
				");
				
				$r = @pg_fetch_assoc($result);
				$total_case_qty = $r['case_qty'];
				$total_unit_qty = $r['unit_qty'];
				
				$po_result = @pg_query("
					select
						sum(case_qty) as case_qty,
						sum(unit_qty) as unit_qty
					from
						po_detail 
					where
						po_header_id = '$po_header_id'
					and
						stock_id = '".$temp['stock_id']."'
				");
				
				
				$po_r = @pg_fetch_assoc($po_result);
				$po_case_qty = $po_r['case_qty'];
				$po_unit_qty = $po_r['unit_qty'];
				
				$href = "onClick=\"xajax_rrEdit('$c')\"";
				$table .= "<tr class='grid' bgColor=\"$bgColor\" onMouseOver=\"bgColor='#FFFF99'\" onMouseOut=\"bgColor='$bgColor'\">
								<td align='right' width='5%'>$c. <input name=\"aChk[]\" type=\"checkbox\" id=\"aChk\" value=\"$c\"></td>
								<td width='15%'> &nbsp;<a href='#' $href >".$temp['barcode']."</a></td>
								<td width='30%'><a href='#' $href >".stripslashes($temp['stock'])."</a></td>
								<td width='6%'><a href='#' $href >".$cunit."</a></td>
								<!--<td align='right'width='9%'><a href='#' $href >".intval($temp['case_order']).':'.$temp['unit_order'].'('.$temp['balance_order'].")</a></td> -->
								<td align='right'width='9%'><a href='#' $href >".intval($po_case_qty).':'.$po_unit_qty.'('.$temp['balance_order'].")</a></td>
								<td align='right'width='5%'><a href='#' $href >".$case_qty .':'. $unit_qty." / "."$total_case_qty:$total_unit_qty"."</a></td>
								<td align='right' width='6%'>".number_format($temp['freight_case'],2)."</td>
								<td align='right' width='5%'>".($aRR['status'] == 'A' ? number_format($temp['cost3'],2):number_format($temp['cost3'],2))."</td>
								<td align='right' width='5%'>".($aRR['status'] == 'A' ? number_format($temp['cost1'],2):number_format($temp['cost1'],2))."</td>
								
								<td align='right' width='14%'>".($aRR['status'] == 'A' ? number_format($temp['amount_out'],2):number_format($temp['amount'],2))."</td>
								</tr>";
			}

			$table .= "</table>";
			
			rr_subtotalCompute($gross_amount);
			glayer('gridLayer',$table);
			return;
	}

	function rr_subtotal($form)
	{
		global $aRR;
		$aRR['status'] ='M';
		
		$q = "select * from rr_header where rr_header_id > '".$aRR['date']."' and account_id = '".$aRR['account_id']."'";
		$qr = @pg_query($q);
		if (@pg_num_rows($qr) == 0)
		{
			gset('updatecost','checked','1');
		}
		$fields = array('gross_amount','freight_add','tax_add','tax_add_type','disc1','disc1_type',
					'disc2','disc2_type','disc3','disc3_type');
					
		for ($c = 0;$c<count($fields);$c++)
		{
			$aRR[$fields[$c]]=str_replace(',','',$form[$fields[$c]]);
		}
		
		rr_subtotalCompute($aRR['gross_amount']);		
		
		gset('gross_amount', number_format($aRR['gross_amount'],2));
		gset('discount_amount', number_format($aRR['discount_amount'],2));
		gset('sub_wo_freight', number_format($aRR['sub_wo_freight'],2));
		gset('tax_amount', number_format($aRR['tax_amount'],2));
		gset('net_amount', number_format($aRR['net_amount'],2));
		gset('freight_amount', number_format($aRR['freight_amount'],2));
		return done();
	}
	
	function rr_subtotalCompute($gross_amount)
	{
		global $aRR, $aRRD, $SYSCONF;
		if ($gross_amount == 0) {
			foreach ($aRRD as $temp) {
				$gross_amount += $temp['amount'];
			}
		}

		if ($aRR['freight_add'] > 0) {
			$aRR['freight_amount'] = $aRR['freight_add'];
		} else {
			$aRR['freight_amount'] = $freight_amount ;
		}

		$aRR['gross_amount']  = $sub_gross = $gross_amount;	
		$aRR['total_items'] = $c;

		if ( $aRR['disc1_type'] == 'P' ) {
			$aRR['discount_amount'] = round($sub_gross *  $aRR['disc1'] / 100,2);
		} else {
			$aRR['discount_amount'] = $aRR['disc1'];
		}

		$sub_gross = $aRR['gross_amount'] - $aRR['discount_amount'];
		if ($aRR['disc2_type']=='P')
		{
			$aRR['discount_amount'] += round($sub_gross *  $aRR['disc2']/100,2);
		} else {
			$aRR['discount_amount'] += $aRR['disc2'];
		}

		$sub_gross = $aRR['gross_amount'] - $aRR['discount_amount'];

		if ($aRR['disc3_type']=='P') {
			$aRR['discount_amount'] += round($sub_gross *  $aRR['disc3']/100,2);
		} else {
			$aRR['discount_amount'] += $aRR['disc3'];
		}

		$sub_gross             = $aRR['gross_amount'] - $aRR['discount_amount'];	
		$aRR['net_amount']     = $aRR['gross_amount'] + $aRR['freight_amount'] - $aRR['discount_amount'];
		$aRR['sub_wo_freight'] = $aRR['gross_amount'] - $aRR['discount_amount'];
		$aRR['sub_gross']      = $sub_gross;

		
		
		if( $aRR['tax_exclusive'] ){  #exclusive
			if ($aRR['tax_add_type'] == 'A')
			{
				$aRR['tax_amount']     = $aRR['tax_add'];
				$aRR['net_amount']     += $aRR['tax_amount'];
				$aRR['sub_wo_freight'] += $aRR['tax_amount'];
			}
			else
			{
				$aRR['tax_amount']     = round($aRR['sub_wo_freight'] * ($aRR['tax_add']/100),2);
				$aRR['net_amount']     += $aRR['tax_amount'];
				$aRR['sub_wo_freight'] += $aRR['tax_amount'];
			}
		} else {
			if( $aRR['tax_add_type'] == 'A' ){

				$aRR['tax_amount']     = $aRR['tax_add'];
				$aRR['net_amount']     -= $aRR['tax_amount'];
				$aRR['sub_wo_freight'] -= $aRR['tax_amount'];

			} else {

				$taxbase = round($aRR['sub_wo_freight'] / (1 + ($aRR['tax_add']/100)),2);
				$aRR['tax_amount'] = round($taxbase * ( $aRR['tax_add'] / 100 ),2);

			}
			
		}
		
		@pg_query("
	      	update 
	      		rr_header 
	      	set 
				discount_amount = '".$aRR['discount_amount']."', 
				net_amount      = '$aRR[net_amount]',
				tax_amount      = '$aRR[tax_amount]',
				gross_amount    = '$aRR[gross_amount]',
				freight_amount  = '$aRR[freight_amount]'
	      	where 
	      		rr_header_id = '".$aRR['rr_header_id']."' 
	      "); 


		gset('gross_amount', number_format($aRR['gross_amount'],2));
		gset('discount_amount', number_format($aRR['discount_amount'],2));
		gset('sub_wo_freight', number_format($aRR['sub_wo_freight'],2));
		gset('tax_amount', number_format($aRR['tax_amount'],2));
		gset('net_amount', number_format($aRR['net_amount'],2));
		gset('freight_amount', number_format($aRR['freight_amount'],2));
		

		return;
	}

?>