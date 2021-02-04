<?
	if ($p1=="Go" || $p1=='Print') 
	{
		$mfrom_date = mdy2ymd($from_date);
		$mto_date = mdy2ymd($to_date);
		$header .= '<bold>REPORT On GROSS PROFIT/LOSS SALES'."</bold>\n";
		$header .= 'Transaction Date from '.$from_date.' to '.$to_date.'             Printed '.date('m/d/Y').' '.$admin->username."\n\n";
		$header .= '                                        Sales    Net Sales     Total       Profit/'."\n";
		$header .= ' Item Description                        Qty       Amount      Cost         (Loss)'."\n";
		$header .= '-------------------------------------  -------- ------------ ------------ ------------'."\n";
		$page = 1;
		$lc = 10;
		$details = '';
		$details2 = '';
		
		$q = "select 
					sum(IF(transaction_type='R',unit_qty,0)) as return_qty,
					sum(IF(transaction_type!='R',unit_qty,0)) as sales_qty,	
					sum(IF(transaction_type='R',amount,0)) as return_amount,
					sum(IF(transaction_type!='R',amount,0)) as sales_amount,	
					sum(IF(transaction_type='R',rd.price*rd.unit_qty - rd.amount ,0)) as return_discount,
					sum(IF(transaction_type!='R',rd.price*rd.unit_qty - rd.amount ,0)) as sales_discount,	
					rd.stock_id, 
					stock.stock,
					stock.price,
					stock.cost
				from
					$transaction_detail as rd,
					$transaction_header as rh,
					stock
				where
					rh.transaction_header_id=rd.transaction_header_id and
					stock.stock_id=rd.stock_id and
					rh.date >= '$mfrom_date' and
					rh.date <= '$mto_date' and
					(rh.status != 'V' or rh.status !='C')";
				
		$q .= "	group by rd.stock_id order by stock";	
		
		$qr = mysql_query($q);
		if (!$qr) message(mysql_error());
		
		$total_amount = 0;
		$total_discount = 0;
		$ctr = 0;
		while ($r = mysql_fetch_object($qr))
		{
			$net_sales_qty = $r->sales_qty - $r->return_qty;
			$net_sales_amount = $r->sales_amount - $r->return_amount;
			$cost = $net_sales_qty * $r->cost;
			$profit = $net_sales_amount - $cost;
			$ctr++;
			$details .= adjustSize($r->stock,35).'  '.
						adjustRight(number_format($net_sales_qty,0),8).' '.
						adjustRight(number_format($net_sales_amount,2),12).' '.
						adjustRight(number_format($cost,2),12).' '.
						adjustRight(number_format($profit,2),12).' '."\n";
			$total_net_sales  += $net_sales_amount;
			$total_cost += $cost;
			$total_profit += $profit;			
			$lc++;
			if ($p1 == 'Print Draft' && $lc > 58)
			{
				$details .= "Page ".$page."<eject>\n\n";
				doPrint($header.$details);
				$lc=10;
				$page++;
				$details2 .= $header.$details;
				$details = '';
			}
		}
		$details .= '-------------------------------------  -------- ------------ ------------ ------------'."\n";
		$details .= adjustSize(' Total Items  '.$ctr,25).space(21).
						adjustRight(number_format($total_net_sales,2),12).' '.
						adjustRight(number_format($total_cost,2),12).' '.
						adjustRight(number_format($total_profit,2),12).' '."\n";
		$details .= '=====================================  ======== ============ ============ ============'."\n";
		$details2 .= $header.$details;
		
		if ($p1 == 'Print Draft')
		{
			$details .= "Page ".$page."<eject>\n\n";
			doPrint($header.$details);
			$page++;
		}
		
} //printing
?> 
<div align="center"><font color="#000000" size="4" face="Times New Roman, Times, serif"><b>Gross 
  Profit/Loss </b></font> </div>
<form name='form1' method='post' action=''>
  <table width="95%" border="0" cellspacing="1" cellpadding="1" height="75%" bgcolor="#999999" align="center">
    <tr bgcolor="#333366"> 
      <td width="59%" height="26"> <font color="#FFFFFF" size="2" face="Verdana, Arial, Helvetica, sans-serif"><b>Report 
        Preview</b></font></td>
      <td width="41%" nowrap><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif">Date 
        From
        <input type="text" name="from_date" value="<?= $from_date;?>" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" size="8">
        <img src="../graphics/arrow_down.jpg" onClick="popUpCalendar(this, form1.from_date, 'mm/dd/yyyy')"> 
        To 
        <input type="text" name="to_date" value="<?= $to_date;?>" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" size="8">
        <img src="../graphics/arrow_down.jpg" onClick="popUpCalendar(this, form1.to_date, 'mm/dd/yyyy')"> 
        <input type="Submit" name="p1" value="Go">
        &nbsp; </font></td>
    </tr>
    <tr> 
      <td colspan="2" valign="top" bgcolor="#FFFFFF" align="center">
	  <textarea name="print_area" cols="92" rows="20" wrap="OFF"><?= $details2;?></textarea></td>
    </tr>
  </table>
<div align=center>
    <input name="p122" type="button" id="p122" value="Print"  onClick="printIframe(print_area)" >
  </div>
</form>
