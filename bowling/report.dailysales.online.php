<?
if (!chkRights2('salesreports','mview',$ADMIN['admin_id']))
{
	message('You are NOT allowed to view Reports...');
	exit;
}
if (!session_is_registered('dGraph'))
{
	session_register('dGraph');
	$dGraph = null;
	$dGraph = array();
}
elseif ($p1 =='')
{
	$month = $dGraph['month'];
	$year = $dGraph['year'];
	$g1 = $dGraph['g1'];
}

if ($year == '')
{
	$year = date('Y');
	$month = date('m')-1;
	if ($month == 12) $year--;
}
if ($p1 == 'Go' || $p1 == 'Print' || $p1 == 'Print Draft')
{


	if (strlen($month)==1) $mmonth = '0'.$month;
	else $mmonth = $month;
	
	$mfrom_date = $year.'-'.$mmonth.'-01';
	$mto_date = $year.'-'.$mmonth.'-31';

	$dGraph['month'] = $month;
	$dGraph['year'] = $year;
	$dGraph['g1'] = $g1;
	
	$tables = currTables($mfrom_date);
	$sales_header = $tables['sales_header'];
	$sales_detail = $tables['sales_detail'];
	$sales_tender = $tables['sales_tender'];

	if ($p1 == 'Print Draft')
	{
		doPrint("<reset><bold>");
	}
	else $header = '';
	$header .= center($SYSCONF['BUSINESS_NAME'],80)."\n";
	$header .= center($SYSCONF['BUSINESS_ADDR'],80)."\n";
	$header .= center('DAILY SALES OF THE MONTH  REPORT',80)."\n";
	$header .= center(cMonth($month).', '.$year,80);
	
	if ($p1 == 'Print Draft') 
	{
		doPrint("</bold>\n\n");
	}	
	else $header .= "\n";
	
	
	$header .= space(10)."------------ -------------  -------------  ----------------\n";
	$header .= space(10)."  Date          Count         Ave. Sales     Sales Amount \n";
	$header .= space(10)."------------ -------------  -------------  ----------------\n";
	$details = $details1 = '';
	$details1 = $header;
	$total_amount = $total_net = $total_discount = $total_count = $total_days = 0;
	
	$q = "select date, sum(net_amount) as amount, count(th.sales_header_id) as mcount
			from 
			 	$sales_header as th
			where
				th.status!='V' and 
				date>='$mfrom_date' and
				date<='$mto_date'	
			group by
				date
			order by 
				date";

	$qr = @pg_query($q) or message(pg_errormessage());

		$data = null;
		$leg = null;
		$data = array();
		$acount = null;
		$acount = array();
		$leg = array();
	while ($r = @pg_fetch_object($qr))
	{
			$acount[] = $r->mcount/10;
			$data[] = $r->amount/1000;
			$leg[] = substr($r->date,8,2);

		$details .= space(11).adjustSize(ymd2mdy($r->date),10).'  '.
					adjustRight($r->mcount,10).'     '.
					adjustRight(number_format($r->amount/$r->mcount,2),12)."  ".
					adjustRight(number_format($r->amount,2),14)."\n";
		$total_count += $r->mcount;
		$total_days++;
		$total_amount += $r->amount;
	}		
	$details .= space(10)."------------ -------------  -------------  ----------------\n";
	$details .= space(11).adjustSize('Days: '.$total_days,10).'  '.
				adjustRight($total_count,10).'    '.space(15).
				adjustRight(number_format($total_amount,2),14)."\n\n";
	if ($total_days > 0)
	{			
		$details .= space(10)."Daily Average Transaction : ".number_format($total_count/$total_days,0)."\n";
		$details .= space(10)."Daily Average Sales       : ".number_format($total_amount/$total_days,2)."\n";
	}	
	$details1 .= $details;

	$dGraph['details1'] = $details1;
	$dGraph['month'] = $month;
	$dGraph['cmonth'] = cmonth($month);
	$dGraph['year'] = $year;

		if (in_array($g1, array('bar','pie','line')))
		{
			$dGraph['data'] = $data;
			$dGraph['acount'] = $acount;
			$dGraph['leg'] = $leg;
			$dGraph['g1'] = $g1;
					
		}

}
?>
<br>
<form name="form1" method="post" action="">
  <table width="80%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr> 
      <td height="17" colspan="3" background="../graphics/table0_horizontal.PNG"><font color="#FFFFFF" size="2"><strong>Sales 
        Average per day for the Month</strong></font></td>
    </tr>
    <tr> 
      <td width="9%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Month/Year<font color="#000000"> 
        </font></font> </td>
      <td width="5%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Output</font></td>
      <td width="86%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;</font></td>
    </tr>
    <tr> 
      <td nowrap> 
        <?= lookUpMonth('month',$month);?>
        <input name="year" type="text" id="year" value="<?= $year;?>" size="4" maxlength="4">
      </td>
      <td><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= lookUpAssoc('g1',array('Print Preview'=>'Preview', 'Line Graph'=>'line',"Bar Graph"=>'bar',"Pie Chart"=>'pie'),$g1);?>
        </font></td>
      <td><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="p1" type="submit" id="p1" value="Go">
        </font></td>
    </tr>
  </table>
  <table width="80%" border="0" align="center" cellpadding="0" cellspacing="1">
    <tr> 
      <td height="27"><table width="100%" border="0" cellspacing="0" cellpadding="2">
          <tr bgcolor="#000033"> 
            <td height="24" bgcolor="#C8D9E2"><font color="#000033" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Print 
              Preview</strong></font> </td>
          </tr>
        </table></td>
    </tr>
    <tr> 
      <td valign="top" bgcolor="#FFFFFF"> 
        <?
		$details1 = $dGraph['details1'];
	  if ($p1= 'Go' && in_array($g1, array('bar','pie','line')))
	  {
	  	echo "<IFRAME SRC='graph.dailysales.php' name='print_area' TITLE='Sales' WIDTH=750 HEIGHT=370 FRAMEBORDER=0></IFRAME>";
	  }
	  else
	  {
		 echo "<textarea name='print_area' cols='97' rows='20' wrap='OFF'>$details1</textarea>";
		}	  
		?>
      </td>
    </tr>
	</table>
<div align="center">
    <input name="p1" type="submit" id="p1" value="Print Draft" >
    <input name="p12" type="button" id="p12" value="Print"  onClick="printIframe(print_area)" >
  </div>
</form>

<iframe name="printit" id="printit" style="width:0px;height:0px;"></iframe>
