<?
if (!chkRights2('salesreports','mview',$ADMIN['admin_id']))
{
	message('You are NOT allowed to view Reports...');
	exit;
}

if ($year == '')
{
	$year = date('Y');
}
if ($p1 == 'Go' || $p1 == 'Print' || $p1 == 'Print Draft' || $p1 == '')
{
	
	if ($p1 == 'Print Draft')
	{
		doPrint("<reset><bold>");
	}
	else $header = '';
	$header .= center($SYSCONF['BUSINESS_NAME'],80)."\n";
	$header .= center($SYSCONF['BUSINESS_ADDR'],80)."\n";
	$header .= center('YEARLY SALES  REPORT',80)."\n";
	
	if ($p1 == 'Print Draft') 
	{
		doPrint("</bold>\n\n");
	}	
	else $header .= "\n";
	
	
	$header .= space(10)."------------ -------------  -------------  ----------------\n";
	$header .= space(10)."  Year           Count         Ave. Sales     Sales Amount \n";
	$header .= space(10)."------------ -------------  -------------  ----------------\n";
	$details = $details1 = '';
	$details1 = $header;
	$total_amount = $total_net = $total_discount = $total_count = $total_years = 0;
	
	$q = "select
				substring(date,1,4)  as year, 
				sum(net_amount) as amount, 
				count(th.sales_header_id) as mcount
			from 
			 	sales_header as th
			where
				th.status!='V' 	
			group by
				substring(date,1,4) 
			order by 
				substring(date,1,4) ";

	$qr = @pg_query($q) or message(pg_errormessage());
	while ($r = @pg_fetch_object($qr))
	{
		$details .= space(11).adjustSize($r->year,10).'  '.
					adjustRight($r->mcount,10).'     '.
					adjustRight(number_format($r->amount/$r->mcount,2),12)."  ".
					adjustRight(number_format($r->amount,2),16)."\n";
		$total_count += $r->mcount;
		$total_years++;
		$total_amount += $r->amount;
	}		
	$details .= space(10)."------------ -------------  -------------  ----------------\n";
	$details .= space(11).adjustSize('Years: '.$total_years,10).'  '.
				adjustRight($total_count,10).'    '.space(15).
				adjustRight(number_format($total_amount,2),16)."\n\n";
	if ($total_years > 0)
	{			
		$details .= space(10)."Yearly Average Transactions : ".number_format($total_count/$total_years,0)."\n";
		$details .= space(10)."Yearly Average Sales       : ".number_format($total_amount/$total_years,2)."\n";
	}	
	$details1 .= $details;
}
?>
<form name="form1" method="post" action="">
  <table width="80%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr>
      <td height="22" background="../graphics/table0_horizontal.PNG"><font color="#FFFFFF" size="2"><strong>Yearly 
        Sales Summary</strong></font></td>
    </tr>
    <tr> 
      <td>
<hr color="#993300"></td>
    </tr>
  </table>
  <table width="80%" border="0" align="center" cellpadding="2" cellspacing="1">
    <tr> 
      <td height="27"><table width="100%" border="0" cellspacing="0" cellpadding="2">
          <tr bgcolor="#000033"> 
            <td height="24" bgcolor="#C8D9E2"><font color="#000033" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Print 
              Preview</strong></font> </td>
          </tr>
        </table></td>
    </tr>
    <tr> 
      <td valign="top" bgcolor="#FFFFFF"> <textarea name="print_area" wrap="off" cols="90" rows="18" readonly><?= $details1;?></textarea> 
      </td>
    </tr>
	</table>
<div align="center">
    <input name="p1" type="submit" id="p1" value="Print Draft" >
    <input name="p12" type="button" id="p12" value="Print"  onClick="printIframe(print_area)" >
  </div>
</form>

<iframe name="printit" id="printit" style="width:0px;height:0px;"></iframe>
