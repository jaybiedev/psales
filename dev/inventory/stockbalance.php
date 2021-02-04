<?
function stockBalance($sid, $fdate=null, $tdate=null)
{
	if ($fdate == '')
	{
		if ($tdate != '')
		{
			$ledgerdate = $tdate;
		}
		else
		{
			$ledgerdate = date('Y').'-01-01';
		}
	}
	else
	{
		$ledgerdate = $fdate;
	}

	$tables = currTables($ledgerdate);
	$sales_header = $tables['sales_header'];
	$sales_detail = $tables['sales_detail'];
	$sales_tender = $tables['sales_tender'];
	$stockledger = $tables['stockledger'];

	$balance_qty = $in_qty = $out_qty = 0;
	$q = "select * from $stockledger 
					where stock_id='$sid' and date<='$ledgerdate' 
						order by date desc offset 0  limit 1";


	$qr = @pg_query($q) or message1(pg_errormessage());
	$r = @pg_fetch_object($qr);
	$begqty = $r->qty;
							
		//stock receiving
		$q = "select 
						sum(rr_detail.case_qty*stock.fraction3 + rr_detail.unit_qty) as in_qty, 
						(sum(rr_detail.amount)/sum(case_qty*fraction3 + unit_qty)) as average_cost
					from 
						rr_header,
						rr_detail,
						stock
					where
						stock.stock_id=rr_detail.stock_id and 
						rr_header.rr_header_id=rr_detail.rr_header_id and
						rr_detail.stock_id='$sid' and
						rr_header.status!='C'";
		if ($fdate!='')
		{
		  $q .= " and date>= '$fdate'";
    }
		if ($tdate!='')
		{
		  $q .= " and date<= '$tdate'";
    }
		
		$q .= "	group by rr_detail.stock_id";
		$qlr = @pg_query($q) or die (pg_errormessage());
		$rl = @pg_fetch_object($qlr);

		$in_qty = $rl->in_qty;
		$average_cost = $rl->average_cost;
		
		//sales return entries
		$q = "select sum(case_qty*fraction3 + unit_qty) as in_qty, salesreturn_detail.price1
					from 
						salesreturn_header,
						salesreturn_detail,
						stock
					where
						stock.stock_id=salesreturn_detail.stock_id and 
						salesreturn_header.salesreturn_header_id=salesreturn_detail.salesreturn_header_id and
						salesreturn_detail.stock_id='$sid' and
						salesreturn_header.status!='C'";
		if ($fdate!='')
		{
		  $q .= " and date>= '$fdate'";
    }
		if ($tdate!='')
		{
		  $q .= " and date<= '$tdate'";
    }
		$q .= "	group by salesreturn_detail.stock_id";
		
	//	$qlr = pg_query($q) or die (pg_errormessage());
	//	$rl = pg_fetch_object($qlr);
	//	$in_qty += $rl->in_qty;

		//adjusting entries
		$q = "select sum(case_qty*fraction3 + unit_qty) as in_qty, invadjust_detail.cost1
					from 
						invadjust_header,
						invadjust_detail,
						stock
					where
						stock.stock_id=invadjust_detail.stock_id and 
						invadjust_header.invadjust_header_id=invadjust_detail.invadjust_header_id and
						invadjust_detail.stock_id='$sid' and
						invadjust_header.warehouse_id='3' and 
						invadjust_header.status!='C'";
		if ($fdate!='')
		{
		  $q .= " and date>= '$fdate'";
    }
		if ($tdate!='')
		{
		  $q .= " and date<= '$tdate'";
    }
		$q .= "	group by invadjust_detail.stock_id";
	//	$qlr = pg_query($q) or die (pg_errormessage());
	//	$rl = pg_fetch_object($qlr);
	//	$in_qty += $rl->in_qty;
	
	//sales
		$q = "select sum(fraction*qty) as out_qty
					from 
						$sales_header as sh,
						$sales_detail as sd,
						stock
					where
						stock.stock_id=sd.stock_id and 
						sh.sales_header_id=sd.sales_header_id and
						sd.stock_id='$sid' and
						sh.status!='V'";
					//	not (sh.status in ('C','V'))";
	if ($fdate!='')
	{
		  $q .= " and date>= '$fdate'";
   }
	if ($tdate!='')
	{
		  $q .= " and date<= '$tdate'";
   }
	$q .= "	group by sd.stock_id";
		
	$qlr = @pg_query($q) or die (pg_errormessage());
	$rl = @pg_fetch_object($qlr);
	$out_qty = $rl->out_qty;


		//stock_issuance
		$q = "select sum(case_qty*fraction3 + unit_qty) as out_qty
					from 
						si_header,
						si_detail,
						stock
					where
						stock.stock_id=si_detail.stock_id and 
						si_header.si_header_id=si_detail.si_header_id and
						si_detail.stock_id='$sid' and
						not (si_header.status in ('C','V'))";

		if ($fdate!='')
		{
		  $q .= " and date>= '$fdate'";
    }
		if ($tdate!='')
		{
		  $q .= " and date<= '$tdate'";
    }
		$q .= "	group by si_detail.stock_id";

	//	$qlr = pg_query($q) or die (pg_errormessage());
	//	$rl = pg_fetch_object($qlr);
	//	$out_qty += $rl->out_qty;

		//purchase return
		$q = "select sum(case_qty*fraction3 + unit_qty) as out_qty
					from 
						poreturn_header,
						poreturn_detail,
						stock
					where
						stock.stock_id=poreturn_detail.stock_id and 
						poreturn_header.poreturn_header_id=poreturn_detail.poreturn_header_id and
						poreturn_detail.stock_id='$sid' and
						not (poreturn_header.status in ('C','V'))";
		if ($fdate!='')
		{
		  $q .= " and date>= '$fdate'";
    	}
		if ($tdate!='')
		{
		  $q .= " and date<= '$tdate'";
    	}
		$q .= "	group by poreturn_detail.stock_id";
	

	//	$qlr = pg_query($q) or die (pg_errormessage());
	//	$rl = pg_fetch_object($qlr);
	//	$out_qty += $rl->out_qty;

					
		$q = "select  sum(case_qty*fraction3 + unit_qty) as out_qty
			from 
					st_header,
					st_detail,
					stock
				where
					stock.stock_id=st_detail.stock_id and 
					st_header.st_header_id=st_detail.st_header_id and
					st_header.status!='C' and
					st_detail.stock_id='$sid' and 
					warehouse_id='3'";
	//	$QR = @pg_query($q) or message(pg_errormessage());
	//	$rl = @pg_fetch_object($QR);
	//	$out_qty += $rl->out_qty;

					
		$q = "select  sum(case_qty*fraction3 + unit_qty) as in_qty
			from 
					st_header,
					st_detail,
					stock
				where
					stock.stock_id=st_detail.stock_id and 
					st_header.st_header_id=st_detail.st_header_id and
					st_header.status!='C' and
					st_detail.stock_id='$sid' and 
					warehouse_to_id='3'";
	//	$QR = @pg_query($q) or message1(pg_errormessage().$q);
	//	$rl = @pg_fetch_object($QR);
	//	$in_qty += $rl->in_qty;
		
  
  	$q = "select 
					sum(po_detail.case_qty*stock.fraction3 + po_detail.unit_qty) as po_qty, 
  					sum(qty1_inv) as qty1_inv
  				from 
  					po_header, po_detail, stock				
  				where 
  				  stock.stock_id=po_detail.stock_id and 
  					po_header.po_header_id = po_detail.po_header_id and
  					po_header.status != 'C' and po_header.status!='R' and po_header.status!='D' and 
  					po_detail.stock_id = '$sid'
          group by
            po_detail.stock_id";
  			
  	$QR = @pg_query($q) or message1(pg_errormessage());
  	$R = @pg_fetch_object($QR);
  	$po_qty = $R->po_qty  - $R->qty1_inv;
  
		
	$balance_qty = $begqty + $in_qty - $out_qty;
  	$balance_available = $balance_qty - $so_qty;
  	$balance_projected = $balance_available + $po_qty;

		$stkled=null;
		$stkled=array();
		$stkled['in_qty'] = $in_qty;
		$stkled['out_qty'] = $out_qty;
		$stkled['balance_qty'] = $balance_qty;
		$stkled['average_cost'] = $average_cost;
		$stkled['so_qty'] = $so_qty;
		$stkled['po_qty'] = $po_qty;
		$stkled['balance_available'] = $balance_available;
		$stkled['balance_projected'] = $balance_projected;

		return $stkled;
}

//**
function stockBalMaterial($sid, $fd, $ed)
{
	$stkled=null;
	$stkled=array();	
	$in_qty = $out_qty = $bal = $balance_qty = '';

	// for all materials of this main menu
	$q .= "select * from material where stock_id = '$sid'";
	$qr = @pg_query($q) or message(pg_errormessage());
	while ($r = @pg_fetch_object($qr))
	{
		$stk=null;
		$stk=array();	

		$stk = stockBalance($r->material_stock_id,'','');
		$in_qty = $stk['in_qty'];
		$out_qty = $stk['out_qty'];


		// -- for i/o of the main menus with this material 
		$q = "select * from material where material_stock_id='$r->material_stock_id'";
		$qqr = @pg_query($q) or message(pg_errormessage());
		while ($rr = @pg_fetch_object($qqr))
		{
			$stk = stockBalance($rr->stock_id,'','');
			$in_qty += $rr->unit_qty*$stk['in_qty'];
			$out_qty += $rr->unit_qty*$stk['out_qty'];
		}
		$balance_qty = intval(($in_qty - $out_qty)/$r->unit_qty);
		
		if ($balance_qty < $stkled['balance_qty'] or $stkled['balance_qty'] == '')
		{
			$stkled['balance_qty'] = $balance_qty;
		}
/*		global $ADMIN;
		if ($ADMIN['admin_id'] == 1)
		{
			print_r($stkled);
		}
*/
	}
	return $stkled;
}
//**
function stockFifo($sid)
{
		$stkled = null;
		$stkled = array();
		
		$akey1 = null;
		$akey1 = array();
		
		$akey2 = null;
		$akey2 = array();

		$q = "select 
					rr_detail.stock_id,
					rr_detail.rr_detail_id,
					rr_detail.condition_id,
					rr_detail.brand_id,
					sum(rr_detail.qty1) as qty_in,
					rr_detail.serial,
					rr_header.remarks,
					'RR' as type
				from 
					rr_detail, 
					rr_header 
				where 
					rr_header.rr_header_id=rr_detail.rr_header_id and 
					rr_detail.stock_id='$sid' and
					rr_header.status!='C'
				group by
					brand_id, condition_id, serial, stock_id
				order by
					brand_id, condition_id, serial";
		$qr = @pg_query($q) or message(pg_errormessage());

		while ($r=@pg_fetch_assoc($qr))
		{
				$r['qty_balance'] = $r['qty_in'];
				$r['key1']=$r['remarks'];
				$r['key2']=$r['brand_id'].'-'.$r['condition_id'].'-'.$r['serial'];
				$akey1[] = $r['key1'];
				$akey2[] = $r['key2'];
				
				$stkled[]=$r;
		}

		$q = "select 
					poreturn_detail.stock_id,
					poreturn_detail.condition_id,
					poreturn_detail.brand_id,
					poreturn_detail.qty1 as qty_out,
					poreturn_detail.serial,
					poreturn_header.remarks,
					'POR' as type
				from 
					poreturn_detail, 
					poreturn_header 
				where 
					poreturn_header.poreturn_header_id=poreturn_detail.poreturn_detail_id and 
					poreturn_detail.stock_id='$sid'";
		$qr = @pg_query($q) or message(pg_errormessage());
		
		while ($r = @pg_fetch_assoc($qr))
		{
				$key1=$r['remarks'];
				$key2=$r['brand_id'].'-'.$r['condition_id'].'-'.$r['serial'];
				$key = array_search($key2, $akey2); 
		}
		$q = "select 
					si_detail.stock_id,
					si_detail.condition_id,
					si_detail.brand_id,
					sum(si_detail.qty_out) as qty_out,
					si_detail.serial,
					si_header.remarks,
					si_header.type
				from 
					si_detail, 
					si_header 
				where 
					si_header.si_header_id=si_detail.si_header_id and 
					status in ('A','P') and 
					si_detail.stock_id='$sid'
				group by
					brand_id, condition_id, serial, stock_id";
		$qr = @pg_query($q) or message(pg_errormessage());
	
		while ($r = @pg_fetch_assoc($qr))
		{
				$key1=$r['remarks'];
				$key2=$r['brand_id'].'-'.$r['condition_id'].'-'.$r['serial'];
				$key = array_search($key2, $akey2); 
				//echo "fnd ".$key;
				$qty = $r['qty_out'];
				$issued_qty = 0;
				//echo "<br><br>qissued ".$qty;
				$c=0;
				foreach ($stkled as $sl)
				{
					
					if ($r['serial'] !='' && $sl['serial']!=$r['serial']) 
					{
							$c++;
							continue;
					}		
					if (intval($r['brand_id']) != '0' && intval($sl['brand_id']) != intval($r['brand_id'])) 
					{
						$c++;
						continue;
					}	
					if (intval($r['condition_id']) != '0' && intval($sl['condition_id']) != intval($r['condition_id'])) 
					{
							$c++;
							continue;
					}		
					$dummy = $sl;		
					if ($sl['qty_balance'] >= $qty)
					{
						//echo "<br>";
						//print_r($sl);
						$issued_qty += $qty;
						$amount_out += $qty* $sl['cost1'];
						$dummy['qty_balance'] -= $qty;
						$qty=0;
						//echo "<br>";
						//echo "qty $qty bal ".$dummy['qty_balance']."<br<br>";
					}
					elseif ($sl['qty_balance'] > 0)
					{	
						$issued_qty += $sl['qty_balance'];

						$qty -= $sl['qty_balance'];
						$amount_out += $sl['qty_balance']*$sl['cost1'];
						$dummy['qty_balance'] = 0;
					}
					$dummy['qty_out'] = $issued_qty;
					$stkled[$c] = $dummy;
					if ($qty <= 0)
					{
						break;
					}
					$c++;
				}

				// ---> problem posting with serial number. POST FIFO;
				$c=0;
				if ($qty > 0) 
				{
					foreach ($stkled as $sl)
					{
						$dummy = $sl;		
						if ($sl['qty_balance'] >= $qty)
						{
							//echo "<br>";
							//print_r($sl);
							$issued_qty += $qty;
							$amount_out += $qty* $sl['cost1'];
							$dummy['qty_balance'] -= $qty;
							$qty=0;
							//echo "<br>";
							//echo "qty $qty bal ".$dummy['qty_balance']."<br<br>";
						}
						elseif ($sl['qty_balance'] > 0)
							{	
							$issued_qty += $sl['qty_balance'];

							$qty -= $sl['qty_balance'];
							$amount_out += $sl['qty_balance']*$sl['cost1'];
							$dummy['qty_balance'] = 0;
						}
						$dummy['qty_out'] = $issued_qty;
						$stkled[$c] = $dummy;
						if ($qty <= 0)
						{
							break;
						}
						$c++;
					}

					
				}

		}
		
//print_r($stkled);		
		return $stkled;		
		
}


function stockLedger($sid, $fdate='',$tdate='')
{
		//stock receiving
		$q = "select sum(if(date < '$fdate',qty,0)) as beginning_qty, 
						sum(if((date >='$fdate' AND date<='$tdate'),qty,0)) as rr_qty, 
						(sum(cost) /sum(qty)) as average_cost,
						sum(datediff('$fdate',date)/qty) as days_age 
						
					from 
						rr_header,
						rr_detail
					where
						rr_header.rr_header_id=rr_detail.rr_header_id and
						rr_detail.stock_id='$sid' and
						rr_header.status!='C' and
						date<='$tdate'
					group by
						stock_id";
		$qlr = @pg_query($q) or die (pg_errormessage());

		if ($sid == 12)
		{
			//echo $q;exit;
		}
		$rl = pg_fetch_object($qlr);
		$beginning_qty = $rl->beginning_qty;
		$rr_qty = $rl->rr_qty;
		$average_cost = $rl->average_cost;
		if (($rl->rr_qty+$rl->beginning_qty) > 0)
		{
		  $days_age = $rl->days_age/($rl->rr_qty+$rl->beginning_qty);
		}
    else
    {
		  $days_age = 0;
    }  

		//stock invadjust
		$q = "select sum(if(date < '$fdate',qty,0)) as beginning_qty, 
						sum(if((date >='$fdate' AND date<='$tdate'),qty,0)) as invadjust_qty, 
						(sum(cost) /sum(qty)) as average_cost
					from 
						invadjust_header,
						invadjust_detail
					where
						invadjust_header.invadjust_header_id=invadjust_detail.invadjust_header_id and
						invadjust_detail.stock_id='$sid' and
						invadjust_header.status!='C' and
						date<='$tdate'
					group by
						stock_id";
		$qlr = pg_query($q) or die (pg_errormessage());
		$rl = pg_fetch_object($qlr);
		$beginning_qty += $rl->beginning_qty;
		$invadjust_qty = $rl->invadjust_qty;

		//sales
		$q = "select sum(if(date < '$fdate',qty,0)) as beginning_qty, 
						sum(if((date >='$fdate' AND date<='$tdate'),qty,0)) as sold_qty
					from 
						sales_header,
						sales_detail
					where
						sales_header.sales_header_id=sales_detail.sales_header_id and
						sales_detail.stock_id='$sid' and
						not (sales_header.status in ('C','V')) and
						date<='$tdate'
					group by
						stock_id";

		$qlr = @pg_query($q) or die (pg_errormessage());
		$rl = pg_fetch_object($qlr);
		$beginning_qty -= $rl->beginning_qty;
		$sold_qty = $rl->sold_qty;

		//stock_issuance
		$q = "select sum(if(date < '$fdate',qty,0)) as beginning_qty, 
						sum(if((date >='$fdate' AND date<='$tdate'),qty_out,0)) as si_qty
					from 
						si_header,
						si_detail
					where
						si_header.si_header_id=si_detail.si_header_id and
						si_detail.stock_id='$sid' and
						not (si_header.status in ('C','V')) and
						date<='$tdate'
					group by
						stock_id";

		$qlr = pg_query($q) or die (pg_errormessage());
		$rl = pg_fetch_object($qlr);
		$beginning_qty -= $rl->beginning_qty;
		$si_qty -= $rl->si_qty;

		//purchase return
		$q = "select sum(if(date < '$fdate',qty,0)) as beginning_qty, 
						sum(if((date >='$fdate' AND date<='$tdate'),qty,0)) as poreturn_qty
					from 
						poreturn_header,
						poreturn_detail
					where
						poreturn_header.poreturn_header_id=poreturn_detail.poreturn_header_id and
						poreturn_detail.stock_id='$sid' and
						not (poreturn_header.status in ('C','V'))
					group by
						stock_id";
		$qlr = pg_query($q) or die (pg_errormessage());
		$rl = pg_fetch_object($qlr);
		$beginning_qty -= $rl->beginning_qty;
		$poreturn_qty = $rl->poreturn_qty;
		
		$balance_qty = $beginning_qty + $rr_qty + $invadjust_qty - $poreturn_qty - $sold_qty - $si_qty ;
		$stkled = null;
		$stkled = array();
		$stkled['rr_qty'] = $rr_qty;
		$stkled['sold_qty'] = $sold_qty;
		$stkled['invadjust_qty'] = $invadjust_qty;
		$stkled['poreturn_qty'] = $poreturn_qty;
		$stkled['si_qty'] = $si_qty;
		$stkled['beginning_qty'] = $beginning_qty;
		$stkled['balance_qty'] = $balance_qty;
		$stkled['average_cost'] = $average_cost;
		$stkled['days_age'] = $days_age;
		
		
    return $stkled; 
}

function whBalance($sid, $wid)
{
	$wh = null;
	$wh = array();
	
	$q = "select fraction3 from stock where stock_id='$sid'";
	$QR = @pg_query($q) or message(pg_errormessage());
	$R = @pg_fetch_object($QR);
	$wh['fraction3'] = $R->fraction3;
	if ($wh['fraction3'] == '0') $fraction3=1;
	
	$q = "select sum(case_qty) as case_in,
				sum(unit_qty) as unit_in
			from 
					st_header,
					st_detail
				where
					st_header.st_header_id=st_detail.st_header_id and
					st_header.status!='C' and
					st_detail.stock_id='$sid' and
					warehouse_to_id='$wid'";
	$QR = @pg_query($q) or message(pg_errormessage());
	$R = @pg_fetch_object($QR);
	
	
	$wh['in_qty']=$R->case_in*$wh['fraction3'] + $R->unit_in;
					
	$q = "select sum(case_qty) as case_out,
				sum(unit_qty) as unit_out
			from 
					st_header,
					st_detail
				where
					st_header.st_header_id=st_detail.st_header_id and
					st_header.status!='C' and
					st_detail.stock_id='$sid' and 
					warehouse_id='$wid'";
	$QR = @pg_query($q) or message(pg_errormessage());
	$R = @pg_fetch_object($QR);

	$wh['out_qty']=$R->case_out*$wh['fraction3'] + $R->unit_out;
	
	
	if ($wid == '4')
	{
		//summarize total invoiced from fabrication
		$q = "select sum(case_qty*fraction3 + unit_qty) as out_qty
					from 
						sales_header,
						sales_detail,
						stock
					where
						stock.stock_id=sales_detail.stock_id and 
						sales_header.sales_header_id=sales_detail.sales_header_id and
						sales_detail.stock_id='$sid' and
						not (sales_header.status in ('C','V')) and
						(sales_header.extra in ('F'))";
		if ($fdate!='')
		{
		  $q .= " and date>= '$fdate'";
   		}
		if ($tdate!='')
		{
		  $q .= " and date<= '$tdate'";
    	}
		$q .= "	group by sales_detail.stock_id";

		$qlr = pg_query($q) or die (pg_errormessage());
		$rl = pg_fetch_object($qlr);
		$wh['out_qty'] += $rl->out_qty;
	}
	
		//adjusting entries
		$q = "select sum(case_qty*stock.fraction3 + unit_qty) as in_qty, invadjust_detail.cost1
					from 
						invadjust_header,
						invadjust_detail,
						stock
					where
						stock.stock_id=invadjust_detail.stock_id and 
						invadjust_header.invadjust_header_id=invadjust_detail.invadjust_header_id and
						invadjust_detail.stock_id='$sid' and
						invadjust_header.warehouse_id='$wid' and 
						invadjust_header.status!='C'";
		if ($fdate!='')
		{
		  $q .= " and date>= '$fdate'";
	    }
		if ($tdate!='')
		{
		  $q .= " and date<= '$tdate'";
    	}
		$q .= "	group by invadjust_detail.stock_id";

		$qlr = pg_query($q) or die (pg_errormessage());
		$rl = pg_fetch_object($qlr);
		$wh['in_qty'] += $rl->in_qty;
	
	$wh['balance_qty'] = $wh['in_qty'] - $wh['out_qty'];
	return $wh;
}

?>
