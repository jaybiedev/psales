<?
if (!chkRights2('managementreports','mview',$ADMIN['admin_id']))
{
	message('You are NOT allowed to view Reports...');
	exit;
}
if (!session_is_registered('mGraph'))
{
	session_register('mGraph');
	$mGraph = null;
	$mGraph = array();

	$year = date('Y');
	$mGraph['year'] = $year;

}

if ($year == '')
{
	$year = date('Y');
}
if ($p1 == 'Go' || $p1 == 'Print' || $p1 == 'Print Draft')
{
	
	if ($p1 == 'Print Draft')
	{
		doPrint("<reset><bold>");
	}
	else $header = '';
	$header .= center($SYSCONF['BUSINESS_NAME'],120)."\n";
	$header .= center($SYSCONF['BUSINESS_ADDR'],120)."\n";
	$header .= center('MONTHLY COST OF SALES  REPORT',120)."\n";
	$header .= center($year,120);
	
	$msd = $year.'-01-01';
	
	$mGraph['year'] = $year;
	$mGraph['msd'] = $msd;
	$mGraph['g1'] = $g1;
	
	$tables = currTables($msd);
	$sales_header = $tables['sales_header'];
	$sales_detail = $tables['sales_detail'];
	$sales_tender = $tables['sales_tender'];
	
	if ($p1 == 'Print Draft') 
	{
		doPrint("</bold>\n\n");
	}	
	else $header .= "\n";
	
	
	$header .= space(3)."------------+------------- ------------- -------------+------------- ------------- -------------+-------------\n";
	$header .= space(3)."            |             NET SALES AMOUNT            |	     COST OF SALES                 |   Gross\n";
	$header .= space(3)."  Month     |   Dry Goods     Grocery     Total Sales |   DryGoods       Grocery     Total Cost |   Profit  \n";
	$header .= space(3)."------------+------------- ------------- -------------+------------- ------------- -------------+-------------\n";
	$details = $details1 = '';
	$details1 = $header;
	$total_amount = $total_net = $total_discount = $total_count = $total_months = 0;
	
	$q = "select
				substring(date,6,2)  as month, 
				sum(total_amount) as amount, 
				sum(drygood_amount) as drygood_amount,
				sum(grocery_amount) as grocery_amount,
				sum(drygood_cost) as drygood_cost,
				sum(grocery_cost) as grocery_cost,
				sum(mcount) as mcount
			from 
			 	eoday
			where
				substring(date,1,4)>='$year' 	
			group by
				substring(date,6,2) 
			order by 
				substring(date,6,2) ";

	$qr = @pg_query($q) or message(pg_errormessage());
	
	$total_drygood = $total_grocery = $total_amount = 0;
	$data = null;
	$data = array();
	$data1 = null;
	$data1 = array();
	$data2 = null;
	$data2 = array();

	$acount = null;
	$acount = array();
	$leg = null;
	$leg = array();
	$xtick = null;
	$xtick= array();
	while ($r = @pg_fetch_object($qr))
	{
		$drygood_amount = $r->drygood_amount;
		$grocery_amount = $r->grocery_amount ;
		$drygood_cost = $r->drygood_cost;
		$grocery_cost = $r->grocery_cost ;

		$cost = $r->drygood_cost + $r->grocery_cost;
		
		$data1[] = $drygood_cost/1000;
		$data2[] = $grocery_cost/1000;
		$data[] = $total_cost/1000;
		$acount[]=$total_cost/10;
		$leg[] = cMonth($r->month).' ('.number_format($cost,2).')';
		$xtick[] = substr(cMonth($r->month),0,3);
		
		$gross_profit = $r->amount - $cost;
		$details .= space(3).adjustSize(cMonth($r->month),10).'  '.
					adjustRight(number_format($drygood_amount,2),13)." ".
					adjustRight(number_format($grocery_amount,2),13)." ".
					adjustRight(number_format($r->amount,2),13)." ".
					adjustRight(number_format($drygood_cost,2),13)." ".
					adjustRight(number_format($grocery_cost,2),13)." ".
					adjustRight(number_format($cost,2),13)." ".
					adjustRight(number_format($gross_profit,2),13)."\n";

		$total_count += $r->mcount;
		$total_months++;
		$total_amount += $r->amount;
		$total_drygood += $drygood_amount;
		$total_grocery += $grocery_amount;

		$total_drygood_cost += $drygood_cost;
		$total_grocery_cost += $grocery_cost;
		
		$total_cost += $cost;
		$total_profit += $gross_profit;
		
	}		
	$details .= space(3)."------------+------------- ------------- -------------+------------- ------------- -------------+-------------\n";
	$details .= space(3).adjustSize('Months: '.$total_months,10).'  '.
				adjustRight(number_format($total_drygood,2),13).' '.
				adjustRight(number_format($total_grocery,2),13).' '.
				adjustRight(number_format($total_amount,2),13).' '.
				adjustRight(number_format($total_drygood_cost,2),13).' '.
				adjustRight(number_format($total_grocery_cost,2),13).' '.
				adjustRight(number_format($total_cost,2),13)." ".
				adjustRight(number_format($total_profit,2),13)."\n\n";
	if ($total_months > 0)
	{			
		$details .= space(10)."Monthly Average Transactions : ".number_format($total_count/$total_months,0)."\n";
		$details .= space(10)."Monthly Average Sales       : ".number_format($total_amount/$total_months,2)."\n";
	}	
	$details1 .= $details;

	if ($p1=='Print Draft' )
	{
		$details .= "<eject>";
		nPrinter($header.$details, $SYSCONF['REPORT_PRINTER_TYPE'], $SYSCONF['REPORT_PRINTER_DEST']);
	}	

	if (in_array($g1, array('bar','pie','line')))
	{
		$mGraph['data'] = $data;
		$mGraph['data1'] = $data1;
		$mGraph['data2'] = $data2;
		$mGraph['acount'] = $acount;
		$mGraph['leg'] = $leg;
		$mGraph['xtick'] = $xtick;
		$mGraph['g1'] = $g1;
				
	}	
}
?>
<form name="form1" method="post" action="">
  <table width="80%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr>
      <td height="22" background="../graphics/table0_horizontal.PNG"><font color="#FFFFFF" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Monthly 
        Cost of Sales Summary</strong></font></td>
    </tr>
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">This year 
        <input name="year" type="text" id="year" value="<?= $mGraph['year'];?>" size="4" maxlength="4">
        Output<font color="#000000"> 
        <?= lookUpAssoc('g1',array('Print Preview'=>'Preview', 'Line Graph'=>'line',"Bar Graph"=>'bar',"Pie Chart"=>'pie'),$mGraph['g1']);?>
        </font></font> <font size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <input name="p1" type="submit" id="p1" value="Go">
        </font>
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
      <td valign="top" bgcolor="#FFFFFF"> 
        <?
	  if (in_array($mGraph['g1'], array('bar','pie','line')))
	  {
	  	echo "<IFRAME SRC='graph.monthlydept.php' name='print_area' TITLE='Sales' WIDTH=750 HEIGHT=370 FRAMEBORDER=0></IFRAME>";
	  }
	  else
	  {
		 echo "<textarea name='print_area' cols='110' rows='20' wrap='OFF'>$details1</textarea>";
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
