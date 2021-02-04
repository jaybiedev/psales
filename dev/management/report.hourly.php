<?
if (!chkRights2('salesreports','mview',$ADMIN['admin_id']))
{
	message('You are NOT allowed to view Reports...');
	exit;
}
if ($p1=="") 
{
	$sd = ymd2mdy(yesterday());
	$ed = ymd2mdy(yesterday());

}
if ($p1=="Go" || $p1 == 'Print' || $p1 == 'Print Draft')
{
	$msd=mdy2ymd($sd);
	$med=mdy2ymd($ed);

	$tables = currTables($msd);
	$sales_header = $tables['sales_header'];
	$sales_detail = $tables['sales_detail'];
	$sales_tender = $tables['sales_tender'];
	
	$q = "select  sum(net_amount) as t_amount 
						from 
							$sales_header 
						where 
							status!='V' and 
							$sales_header.date>='$msd'  and 
							$sales_header.date<='$med' ";
	if ($terminal != '')
	{
		$q .= " and terminal = '$terminal'";
	}
	$qr = @query($q) or message1(db_error().$q);
	$r = fetch_object($qr);
	$t_amount  = $r->t_amount;

			$q = "select 
							substr(time,1,2) as time, 
							sum(net_amount) as total_amount,
							sum($sales_header.item_lines) as  lines,
							count(invoice) as mcount
				from 
						$sales_header
				where 
						$sales_header.date>='$msd'  and 
						$sales_header.date<='$med'  and
						$sales_header.status!='V' ";
	if ($terminal != '')
	{
		$q .= " and terminal = '$terminal'";
	}
	$q .=" group by
					substr(time,1,2)
			order by 
					substr(time,1,2)";

	
	$header = "\n\n\n";
	$page=1;
	$header .= center($SYSCONF['BUSINESS_NAME'],40)."\n";
	$header .= center('HOURLY SALES REPORT',40)."\n";
	$header .= center('Date '.$sd.' To '.$ed,40)."\n";
	$header .= center('Printed:'.date('m/d/Y'),40)."\n";
	if ($terminal == '')
	{
		$header .= center("Terminal : ALL",40)."\n";
	}
	else
	{
		$header .= center("Terminal : ".$terminal,40)."\n";
		
	}
	$header .= str_repeat('-',40)."\n";
	$header .= '  Time  #Cust #Lns  Unit    Amount   % '."\n";
	$header .= str_repeat('-',40)."\n";
	$lc=10;
	$ictr=0;
	$mdate='';
	
	$qr = @query($q) or message1(db_error().$q);

  	while ($r = pg_fetch_object($qr)) 
	{

		$q = "select sum(qty) as units
						from 
							$sales_header, $sales_detail 
						where 
							$sales_header.sales_header_id=$sales_detail.sales_header_id and
							date>='$msd' and
							date<= '$med' and
							substr(time,1,2) = '$r->time'";
		if ($terminal !='') 
		{
			$q .= " and terminal = '$terminal'";
		}
		$qqr = @query($q);
		$rr = @fetch_object($qqr);
							
		$time_from  = 1*$r->time;
		if ($time_from > 12) $time_from -= 12;
		$time_to  = $time_from+1;
		if ($time_to > 12) $time_to -= 12;
		
		$time  = str_pad($time_from,2,' ',str_pad_left).'-'.str_pad($time_to,2,' ',str_pad_left);
		if ($r->time =='11') $time .= 'nn';
		elseif ($r->time < '11') $time .='am';
		elseif ($r->time >'11') $time .= 'pm';

		$ictr++;
		$details .= 
					adjustSize($time,7).'  '.
					adjustRight(number_format($r->mcount,0),4).' '.
					adjustRight($r->lines,4).' '.
					adjustRight(intval($rr->units),4).' '.
					adjustRight(number_format($r->total_amount,2),11).' '.
					adjustRight(number_format(round(($r->total_amount/$t_amount)*100,2),0),3).'%'.
					"\n";
					
		$total_count += $r->mcount;
		$total_lines += $r->lines;
		$total_units +=  intval($rr->units);
		$total_amount += $r->total_amount;			
		$lc++;			
		if ($lc > 55 && $p1 == 'Print Draft' && $SYSCONF['REPORT_PRINT']=='UDP DRAFT')
		{
			nPrint ($header.$details, $SYSCONF['REPORT_PRINTER_TYPE'], $SYSCONF['REPORT_PRINTER_DEST']);
			$lc=10;
			$details = '';
		}	
		

	} //while
	$details .= str_repeat('-',40)."\n";
	$details .= space(8)." " .
						adjustRight($total_count,4).' '.
						adjustRight($total_lines,4).' '.
						adjustRight($total_units,4).' '.
						adjustRight(number_format($total_amount,2),11)."\n";
	$details .= str_repeat('=',40)."\n\n\n\n";
	$details1 .= $header.$details;
	if ($p1 =='Print Draft' || $p1 =='Print' )
	{
			nPrinter($header.$details, $SYSCONF['REPORT_PRINTER_TYPE'], $SYSCONF['REPORT_PRINTER_DEST']);
	}


} //with print

?> 
<form name='form1' method='post' action=''>
  <table width="75%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr background="../graphics/table0_horizontal.PNG"> 
      <td height="34" colspan="4"><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; <img src="../graphics/bluelist.gif"> <strong> Hourly 
        Sales Report</strong></font></td>
    </tr>
    <tr valign="top"> 
      <td width="8%" nowrap> <font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        Date From<br>
        <input name="sd" type="text" id="sd" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $sd;?>" size="10">
        <img src="../graphics/dwn-arrow-grn.gif" width="12" height="12" onClick="popUpCalendar(this, form1.sd, 'mm/dd/yyyy')"> 
        </font> </td>
      <td width="8%" nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">To<br>
        <input name="ed" type="text" id="ed" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $ed;?>" size="10">
        <img src="../graphics/dwn-arrow-grn.gif" width="12" height="12" onClick="popUpCalendar(this, form1.ed, 'mm/dd/yyyy')"> 
        </font></td>
      <td width="7%" nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">Terminal<br>
        <input name="terminal" type="text" id="terminal" value="<?= $terminal;?>"  size="8">
        </font></td>
      <td width="77%" nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <br>
        <input name="p1" type="Submit" id="p1" value="Go">
        </font></td>
    </tr>
    <tr> 
      <td colspan="4"><hr color="#993300"></td>
    </tr>
    <tr bgcolor="#B5CFD5"> 
      <td height="27" colspan="4"> <font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"><b>Report 
        Hourly Sales Preview</b></font></td>
    </tr>
    <tr> 
      <td valign="top" bgcolor="#FFFFFF" colspan="4"> <textarea name="print_area" cols="100" rows="20" wrap="OFF"><?= $details1;?></textarea></td>
    </tr>
  </table>
  <div align=center>
    <input name="p1" type="submit" id="p1" value="Print Draft"  >
    <input name="p122" type="button" id="p122" value="Print"  onClick="printIframe(print_area)" >
  </div>
</form>

<iframe name="printit" id="printit" style="width:0px;height:0px;"></iframe>
