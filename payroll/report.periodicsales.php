<?
if ($p1=="") {
	$from_date = date("m/01/Y");
}
if ($p1=="Print" || $p1=='Go') 
{
	$mfrom_date=mdy2ymd($from_date);
	$mto_date=mdy2ymd($to_date);
	
	$q ="select amountdue as amount,
			transaction_type,
			transaction_header_id,
			old_transaction_header_id,
			date
		from 
			$transaction_header
		where 
			date>='$mfrom_date'  and
			date<='$mto_date' 
		order by 
			date";
		
	$qr = mysql_query($q) or die (mysql_error());
	
	$header = center('PERIODIC SALES REPORT',80)."\n";
	$header .= center('Transaction Date '.$from_date.' to '.$to_date,80)."\n\n";
	
	$details .= '           <-------- Cash Transactions --------->  <-------- Charge Transactions ------>'."\n";
	$details .= 'Date        Invoice From  To         Amount         Invoice From  To       Amount       '."\n";
	$details .= '---------- ---------- ----------- ---------------  ---------- ---------- ---------------'."\n";
	$lc=10;
  	$sub_total=0;
	$grand_total=0;
	$shift=0;
	$mdate='';
	$cash_from='';
	$cash_to='';
	$charge_from='';
	$charge_to='';
	$cash_total=0;
	$charge_total=0;
  	while ($r = mysql_fetch_object($qr)) 
	{
		$date=substr($r->date,0,10);
		if ($mdate != $date)
		{
			if ($mdate != '')
			{
				$details .= adjustSize(ymd2mdy($mdate),10).' '.
					adjustSize($cash_from,10).' '.
					adjustSize($cash_to,10).'  '.
					adjustRight(number_format($cash_amount,2),14).'   '.
					adjustSize($charge_from,10).' '.
					adjustSize($charge_to,10).' '.
					adjustRight(number_format($charge_amount,2),14)."\n";
			}		
			$cash_total+= $cash_amount;
			$charge_total += $charge_amount;

			$mdate=$date;
			$cash_amount=0;
			$charge_amount=0;
			$cash_from='';
			$cash_to='';
			$charge_from='';
			$charge_to='';
		}
				
		if ($r->transaction_type=='C')
		{
			$cash_amount +=  $r->amount;
			if ($r->transaction_header_id < $cash_from || $cash_from=='')
			{
				$cash_from= str_pad($r->transaction_header_id,7,'0',str_pad_left);
			}
			if ($r->transaction_header_id > $cash_to || $cash_to =='')
			{
				$cash_to=str_pad($r->transaction_header_id,7,'0',str_pad_left);
			}
		}
		elseif($r->transaction_type=='R' && lookUpTableReturnValue('x',$transaction_header,'transaction_header_id','transaction_type',$r->old_transaction_type)=='C')
		{
			$cash_amount -=  $r->amount;
		}
		elseif($r->transaction_type=='R' && lookUpTableReturnValue('x',$transaction_header,'transaction_header_id','transaction_type',$r->old_transaction_type)!='C')
		{
			$charge_amount -=  $r->amount;
		}
		else
		{
			$charge_amount +=  $r->amount;
			if ($r->transaction_header_id < $charge_from || $charge_from=='')
			{
				$charge_from=str_pad($r->transaction_header_id,7,'0',str_pad_left);
			}
			if ($r->transaction_header_id > $charge_to || $charge_to =='')
			{
				$charge_to=str_pad($r->transaction_header_id,7,'0',str_pad_left);
			}
		}
	} //while
	
	$details .= adjustSize(ymd2mdy($mdate),10).'  '.
			adjustSize($cash_from,9).'  '.
			adjustSize($cash_to,9).'  '.
			adjustRight(number_format($cash_amount,2),14).'    '.
			adjustSize($charge_from,9).'  '.
			adjustSize($charge_to,9).' '.
			adjustRight(number_format($charge_amount,2),14)."\n";

	$cash_total+= $cash_amount;
	$charge_total += $charge_amount;

	$details .= '---------- ---------- ----------- ---------------  ---------- ---------- ---------------'."\n";
	$details .= space(4).'TOTAL --> '.space(20).adjustRight(number_format($cash_total,2),14).'   '.
		    space(22).adjustRight(number_format($charge_total,2),14)."\n";
	$details .= '---------- ---------- ----------- ---------------  ---------- ---------- ---------------'."\n";
	$details .= '    Grand Total : '.adjustRight(number_format($cash_total+$charge_total,2),14)."\n\n\n";

	$details1 .= $header.$details;
	if ($p1=='Print Draft')
	{
		doPrint($header.$details);
		doPrint('<eject>');
	}
} //with print

?>
<div align="center"><font color="#000000" size="4" face="Times New Roman, Times, serif"><b>Periodic 
  Sales Report</b></font> </div>
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
	  <textarea name="print_area" cols="92" rows="20" wrap="OFF"><?= $details1;?></textarea></td>
    </tr>
  </table>
<div align=center>
    <input name="p122" type="button" id="p122" value="Print"  onClick="printIframe(print_area)" >
  </div>
</form>
