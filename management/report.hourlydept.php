<?
if (!chkRights2('salesreports','mview',$ADMIN['admin_id']))
{
	message('You are NOT allowed to view Reports...');
	exit;
}
if (!session_is_registered('hGraph'))
{
	session_register('hGraph');
	$hGraph = null;
	$hGraph = array();

	$hGraph['date'] = ymd2mdy(yesterday());
	$hGraph['top'] = '';
	$sd = ymd2mdy(yesterday());
	$ed = ymd2mdy(yesterday());
	$hGraph['ed'] = $ed;
	$hGraph['sd'] = $sd;

}

if ($p1=="Go" || $p1 == 'Print' || $p1 == 'Print Draft')
{
	$msd=mdy2ymd($sd);
	$med=mdy2ymd($ed);

	$hGraph['ed'] = $ed;
	$hGraph['sd'] = $sd;
	$hGraph['g1'] = $g1;

	$tables = currTables($msd);
	$sales_header = $tables['sales_header'];
	$sales_detail = $tables['sales_detail'];
	$sales_tender = $tables['sales_tender'];
	
	$q = "select 
					substr(sh.time,1,2) as time, 
					sum(sd.amount) as amount,
					category.department
	
			from 
					$sales_header as sh,
					$sales_detail as sd,
					stock,
					category
			where 
					sh.sales_header_id=sd.sales_header_id and
					stock.stock_id=sd.stock_id and
					category.category_id = stock.category_id and
					sh.date>='$msd'  and 
					sh.date<='$med'  and
					sh.status!='V' ";

	$q .=" group by
					substr(time,1,2),
					department
			order by 
					substr(time,1,2)";

	
	$qr = @query($q) or message1(db_error().$q);

	$aRep = null;
	$aRep = array();
  	while ($r = pg_fetch_object($qr)) 
	{
		$fnd = $c = 0;
		foreach ($aRep as $dummy)
		{
			if ($dummy['time'] == $r->time)
			{
				$temp = $dummy;
				if ($r->department == 'D')
				{
					$temp['drygood_amount'] += $r->amount;
				}
				else
				{
					$temp['grocery_amount'] += $r->amount;
				}
				$aRep[$c] = $temp;
				$fnd = 1;
				break;
			}
			$c++;
		}
		if ($fnd == 0)
		{
			$temp = null;
			$temp = array();
			$temp['time'] = $r->time;
			if ($r->department == 'D')
			{
				$temp['drygood_amount'] = $r->amount;
			}
			else
			{
				$temp['grocery_amount'] = $r->amount;
			}
			$aRep[] = $temp;
		}
	}
	
	$header = "\n\n\n";
	$page=1;
	$header .= center($SYSCONF['BUSINESS_NAME'],40)."\n";
	$header .= center('HOURLY SALES BY DEPARTMENT REPORT',40)."\n";
	$header .= center('Date '.$sd.' To '.$ed,40)."\n";
	$header .= center('Printed:'.date('m/d/Y'),40)."\n";
	$header .= str_repeat('-',40)."\n";
	$header .= '  Time    DryGoods    Grocery   '."\n";
	$header .= str_repeat('-',40)."\n";
	$lc=10;
	$ictr=0;
	$mdate='';

		$data1= null;
		$data2= null;
		$leg = null;
		$data1 = array();
		$data2 = array();
		$leg = array();
		$xtick=null;
		$xtick=array();
	$total_amount = 0;	
	foreach ($aRep as $dummy)
	{
		$time_from  = 1*$dummy['time'];
		if ($time_from > 12) $time_from -= 12;
		$time_to  = $time_from+1;
		if ($time_to > 12) $time_to -= 12;
		
		$time  = str_pad($time_from,2,' ',str_pad_left).'-'.str_pad($time_to,2,' ',str_pad_left);
		if ($dummy['time'] =='11') $time .= 'nn';
		elseif ($dummy['time'] < '11') $time .='am';
		elseif ($dummy['time'] >'11') $time .= 'pm';

		$xtick[] = $time;
		$data1[] = $dummy['drygood_amount']/1000;
		$data2[] = $dummy['grocery_amount']/1000;
		$data[] = ($dummy['grocery_amount'] + $dummy['drygood_amount'])/1000;
		$leg[]=$time;
		
		$ictr++;
		$drygood_amount = $dummy['drygood_amount'];
		$grocery_amount = $dummy['grocery_amount'];
		$details .= 
					adjustSize($time,7).'  '.
					adjustRight(number_format($drygood_amount,2),11).' '.
					adjustRight(number_format($grocery_amount,2),11).' '.
					"\n";
					
		$total_amount = $grocery_amount + $drygood_amount;
		$total_grocery += $grocery_amount;
		$total_drygood += $drygood_amount;
		$lc++;			
		if ($lc > 55 && $p1 == 'Print Draft' && $SYSCONF['REPORT_PRINT']=='UDP DRAFT')
		{
			nPrint ($header.$details, $SYSCONF['REPORT_PRINTER_TYPE'], $SYSCONF['REPORT_PRINTER_DEST']);
			$lc=10;
			$details = '';
		}	
		

	} //while
	$details .= str_repeat('-',40)."\n";
	$details .= 
					adjustSize(' ',7).'  '.
					adjustRight(number_format($total_drygood,2),11).' '.
					adjustRight(number_format($total_grocery,2),11).' '.
					"\n";
	$details .= str_repeat('=',40)."\n\n\n\n";
	$details1 .= $header.$details;
	if ($p1 =='Print Draft' || $p1 =='Print' )
	{
			nPrinter($header.$details, $SYSCONF['REPORT_PRINTER_TYPE'], $SYSCONF['REPORT_PRINTER_DEST']);
	}

		$total_amount = $total_grocery + $total_drygood;
		if (in_array($g1, array('bar','pie','line')))
		{
			$hGraph['data'] = $data;
			$hGraph['data1'] = $data1;
			$hGraph['data2'] = $data2;
			$hGraph['leg'] = $leg;
			$hGraph['xtick'] = $xtick;
			$hGraph['g1'] = $g1;
			$hGraph['total_amount'] = $total_amount;
			$hGraph['total_grocery'] = $total_grocery;
			$hGraph['total_drygood'] = $total_drygood;		
		}

} //with print

?> 
<form name='form1' method='post' action=''>
  <table width="75%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr background="../graphics/table0_horizontal.PNG"> 
      <td height="27" colspan="4"><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
        <img src="../graphics/bluelist.gif"> <strong> Hourly Sales by Department 
        Report</strong></font></td>
    </tr>
    <tr valign="top"> 
      <td width="8%" nowrap> <font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        Date From<br>
        <input name="sd" type="text" id="sd" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $hGraph['sd'];?>" size="10">
        <img src="../graphics/dwn-arrow-grn.gif" width="12" height="12" onClick="popUpCalendar(this, form1.sd, 'mm/dd/yyyy')"> 
        </font> </td>
      <td width="8%" nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">To<br>
        <input name="ed" type="text" id="ed" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $hGraph['ed'];?>" size="10">
        <img src="../graphics/dwn-arrow-grn.gif" width="12" height="12" onClick="popUpCalendar(this, form1.ed, 'mm/dd/yyyy')"> 
        </font></td>
      <td width="7%" nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">Output<br>
        <?= lookUpAssoc('g1',array('Print Preview'=>'Preview',"Line Graph"=>'line', "Bar Graph"=>'bar',"Pie Chart"=>'pie'),$hGraph['g1']);?>
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
        Hourly Sales by Department Preview</b></font></td>
    </tr>
    <tr> 
      <td valign="top" bgcolor="#FFFFFF" colspan="4"> 
        <?
	  if (in_array($hGraph['g1'], array('bar','pie','line')))
	  {
	  	echo "<IFRAME SRC='graph.hourlydept.php' name='print_area' TITLE='Sales' WIDTH=750 HEIGHT=370 FRAMEBORDER=0></IFRAME>";
	  }
	  else
	  {
		 echo "<textarea name='print_area' cols='97' rows='20' wrap='OFF'>$details1</textarea>";
		}	  
		?>
      </td>
    </tr>
  </table>
  <div align=center>
    <input name="p1" type="submit" id="p1" value="Print Draft"  >
    <input name="p122" type="button" id="p122" value="Print"  onClick="printIframe(print_area)" >
  </div>
</form>

<iframe name="printit" id="printit" style="width:0px;height:0px;"></iframe>
