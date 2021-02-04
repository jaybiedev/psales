<?
if ($p1=="") 
{
	$date = date("m/d/Y");
	$q = "select date from $transaction_header order by date desc limit 0,1";
	$qr = mysql_query($q) or die (mysql_error());
	if (mysql_num_rows($qr) > 0)
	{
		$r = mysql_fetch_object($qr);
		$date = ymd2mdy($r->date);
	}	
}
if ($p1=="Go" || $p1 == 'Print')
{
	$mdate=mdy2ymd($date);
	$qr = mysql_query("select 
				shift,
				transaction_type,
				$transaction_header.transaction_header_id,
				$transaction_header.name, 
				stock.stock, 
				unit_qty,
				$transaction_detail.price, 
				$transaction_detail.amount, 
				$transaction_detail.discount,
				username 
		from 
				$transaction_header, $transaction_detail, stock, admin
		where 
				$transaction_header.transaction_header_id=$transaction_detail.transaction_header_id and 
				stock.stock_id=$transaction_detail.stock_id and 
				admin.adminId=$transaction_header.adminId and 
				$transaction_header.status!='V' and
				$transaction_header.status!='C' and
				date='$mdate'  
		order by 
				shift" ) 
		or die (mysql_error());
	
	$header = "\n\n\n";
	$page=1;
	$header .= center('DAILY SALES REPORT',130)."\n";
	$header .= center('Transaction Date '.$date,130)."\n\n";
	
	$header .= 'Reference   Customer                     Item Description                   Qty     Price     Disc    Amount      Cashier  '."\n";
	$header .= '--------- ------------------------- ------------------------------------ ------- ---------- ----- ------------ --------------'."\n";
	$lc=10;
  	$sub_total=0;
	$grand_total=0;
	$shift=0;

  	while ($r = mysql_fetch_object($qr)) 
	{
		if ($r->shift != $shift) {
			if ($sub_total != 0) {
				$details .= space(98)."-------------"."\n";
				$details .= space(75)." Shift $shift  Sub Total :  ".adjustRight(number_format($sub_total,2),12)."\n";
				$details .="\n";
				$lc++;
				$lc++;
			}
			$sub_total=0;
			$shift =$r->shift;
			$details .= "Shift No.  $shift \n";
			$lc++;
		}
		if ($r->transaction_type == 'R')
		{
			$sub_total -= $r->amount;
			$grand_total -= $r->amount;
		}
		else
		{
			$sub_total += $r->amount;
			$grand_total += $r->amount;
		}	
      		$details .= ' '.str_pad($r->transaction_header_id,7,'0',str_pad_left).'  '.
					adjustSize(htmlspecialchars($r->name),25).' '.
					adjustSize($r->stock,35).'  '.
					adjustRight($r->unit_qty,7).' '.
					adjustRight(number_format($r->price,2),10).' '.
					adjustRight(number_format($r->discount,0).'%',5).' '.
					adjustRight(number_format($r->amount,2),12).' '.
					adjustSize($r->username,13)."\n";
		$lc++;
					
		if ($lc > 55 && $p1 == 'Print Draft')
		{
			$details1 .= $header.$details;
			$details .= "\n\n";
			$details .= "<eject>";
			$details = "<small3>";
			$page++;
			$header = "\n\n\n";
			$header .= center('DAILY SALES REPORT - '.$PCENTER,130)."\n";
			$header .= center('Transaction Date '.$date.'  Page '.$page,130)."\n\n";
			
			$header .= 'Receipt   Reference   Customer                     Item Description                   Qty     Price     Disc    Amount      Cashier  '."\n";
			$header .= '--------- ----------- ------------------------- ------------------------------------ ------- ---------- ----- ------------ --------------'."\n";
			$lc=10;
		}			
	} //while
	if ($sub_total != 0) 
	{
			$details .= space(98)."-------------"."\n";
			$details .= space(75)." Shift $shift  Sub Total :  ".adjustRight(number_format($sub_total,2),12)."\n";
			$lc++;
			$lc++;
	}
	$details .= space(98)."-------------"."\n";
	$details .= space(80)." GRAND TOTAL :    ".adjustRight(number_format($grand_total,2),12)."\n";
	$details .= space(98)."============="."\n\n\n";
	$details1 .= $header.$details;
	if ($p1 =='Print Draft')
	{
		doPrint2($header.$details);
		doPrint2('<eject>');
	}
} //with print

?> 
<div align="center"><font size="4"><strong>Daily Sales Report </strong></font></div>
<form name='form1' method='post' action=''>
  <table width="95%" border="0" cellspacing="1" cellpadding="1" height="75%" bgcolor="#999999" align="center">
    <tr bgcolor="#333366"> 
      <td width="59%" height="26"> <font color="#FFFFFF" size="2" face="Verdana, Arial, Helvetica, sans-serif"><b>Report 
        Preview</b></font></td>
      <td width="41%" nowrap><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif">Date 
        From 
        <input name="date" type="text" id="date" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $date;?>" size="8">
        <img src="../graphics/arrow_down.jpg" onClick="popUpCalendar(this, form1.date, 'mm/dd/yyyy')">
		<input type="Submit" name="p1" value="Go">
        &nbsp; </font></td>
    </tr>
    <tr> 
      <td colspan="2" valign="top" bgcolor="#FFFFFF" align="center"> 
	  <textarea name="print_area" cols="92" rows="20" wrap="OFF"><?= $details1;?></textarea></td>
    </tr>
  </table>
  <div align=center> 
    <input name="p122" type="button" id="p122" value="Print"  onClick="printIframe(print_area)" >
  </div>
</form>
