<?
if (!chkRights2('managementreports','mview',$ADMIN['admin_id']))
{
	message('You are NOT allowed to view Reports...');
	exit;
}
if (!session_is_registered('yGraph'))
{
	session_register('yGraph');
	$yGraph = null;
	$yGraph = array();

	$year = date('Y');
	$yGraph['year'] = $year;

}


if ($year == '')
{
	$year = date('Y');
}
if ($p1 == 'Go' || $p1 == 'Print' || $p1 == 'Print Draft' || $p1 == '')
{
	$yGraph['g1'] = $_REQUEST['g1'];
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
	
	
	$header .= space(5)."------ --------- ------------ ----------- ------------- -------------- -------------- --------------\n";
	$header .= space(5)." Year   Count       Units      Ave./Recpt AmtAve./Month    Drygoods       Grocery      Total Sales \n";
	$header .= space(5)."------ --------- ------------ ----------- ------------- -------------- -------------- --------------\n";
	$details = $details1 = '';
	$details1 = $header;
	$total_amount = $total_net = $total_discount = $total_count = $total_years = $total_drygood = $total_grocery = 0;

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
	
	$q = "select
				substring(date,1,4)  as year, 
				sum(total_amount) as amount, 
				sum(drygood_amount) as drygood_amount,
				sum(grocery_amount) as grocery_amount,
				sum(units) as units,
				sum(mcount) as mcount,
				sum(lines) as lines,
				count(distinct (substring(date,6,2))) as months
			from 
			 	eoday
			group by
				substring(date,1,4) 
			order by 
				substring(date,1,4) ";

	$qr = @pg_query($q) or message(pg_errormessage());
	while ($r = @pg_fetch_object($qr))
	{

		$data1[] = $r->drygood_amount/1000;
		$data2[] = $r->grocery_amount/1000;
		$data[] = $r->amount/1000;
		$acount[]=$r->mcount/10;
		$leg[] = $r->year.' ('.number_format($r->amount,2).')';
		$xtick[] = $r->year;

		$details .= space(6).adjustSize($r->year,6).' '.
					adjustRight($r->mcount,8).' '.
					adjustRight(number_format($r->units,2),12)." ".
					adjustRight(number_format($r->amount/$r->mcount,2),11)." ".
					adjustRight(number_format($r->amount/$r->months,2),12)." ".
					adjustRight(number_format($r->drygood_amount,2),14)." ".
					adjustRight(number_format($r->grocery_amount,2),14)." ".
					adjustRight(number_format($r->amount,2),14)."\n";
		$total_count += $r->mcount;
		$total_months += $r->months;
		$total_years++;
		$total_amount += $r->amount;
		$total_drygood += $r->drygood_amount;
		$total_grocery += $r->grocery_amount;
	}		
	$details .= space(5)."------ --------- ------------ ----------- ------------- -------------- -------------- --------------\n";
	$details .= space(5).adjustSize('Years: '.$total_years,9).' '.
				adjustRight($total_count,12).space(19).
				adjustRight(number_format($total_amount/$total_months,2),14).' '.
				adjustRight(number_format($total_drygood,2),14).' '.
				adjustRight(number_format($total_grocery,2),14).' '.
				adjustRight(number_format($total_amount,2),14)."\n";
	$details .= space(5)."------ --------- ------------ ----------- ------------- -------------- -------------- --------------\n\n";
	if ($total_years > 0)
	{			
		$details .= space(10)."Yearly Average Transactions : ".number_format($total_count/$total_years,0)."\n";
		$details .= space(10)."Yearly Average Sales        : ".number_format($total_amount/$total_years,2)."\n";
	}	
	$details1 .= $details;

	$yGraph['details1'] = $details1;	
	if (in_array($g1, array('bar','pie_total','pie_yearly','line')))
	{
		$yGraph['data'] = $data;
		$yGraph['data1'] = $data1;
		$yGraph['data2'] = $data2;
		$yGraph['total_amount'] = $total_amount;
		$yGraph['total_drygood'] = $total_drygood;
		$yGraph['total_grocery'] = $total_grocery;
		$yGraph['acount'] = $acount;
		$yGraph['leg'] = $leg;
		$yGraph['xtick'] = $xtick;
		$yGraph['g1'] = $g1;
		
				
	}	

}
?>
<form name="form1" method="post" action="">
  <table width="80%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr>
      <td height="22" background="../graphics/table0_horizontal.PNG"><font color="#FFFFFF" size="2"><strong>Yearly 
        Sales Summary</strong></font></td>
    </tr>
    <tr> 
      <td> <font size="2" face="Verdana, Arial, Helvetica, sans-serif">Output<font color="#000000"> 
        <?= lookUpAssoc('g1',array('Print Preview'=>'Preview', 'Line Graph'=>'line',"Pie Chart(Yearly Comparative)"=>'pie_yearly',"Pie Chart(Overall Total)"=>'pie_total',"Bar Graph"=>'bar'),$yGraph['g1']);?>
        </font></font> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="p1" type="submit" id="p1" value="Go">
        </font><br>
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
	  if (in_array($yGraph['g1'], array('bar','pie_yearly','pie_total','line')))
	  {
	  	echo "<IFRAME SRC='graph.annualsales.php' name='print_area' TITLE='Sales' WIDTH=750 HEIGHT=370 FRAMEBORDER=0></IFRAME>";
	  }
	  else
	  {
	  	$detail1 = $yGraph['details1'];
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
