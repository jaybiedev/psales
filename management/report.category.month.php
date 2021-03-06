<?
if (!chkRights2('salesreports','mview',$ADMIN['admin_id']))
{
	message('You are NOT allowed to view Reports...');
	exit;
}
if (!session_is_registered('cGraph'))
{
	session_register('cGraph');
	$cGraph = null;
	$cGraph = array();

	$cGraph['date'] = ymd2mdy(yesterday());
	$cGraph['top'] = '';
}
if ($year == '')
{
	$year =  date('Y');
	$from_month = date('m')-1;
	$to_month = date('m')-1;
}
	
	if ($p1=="Go" || $p1=='Print Draft' ||  $p1=='Print') 
	{
		$from_month = $_REQUEST['from_month'];
		$to_month = $_REQUEST['to_month'];
		$year = $_REQUEST['year'];
		
		if (strlen($from_month) == 1) $from_month = '0'.$from_month;
		if (strlen($to_month) == 1) $to_month = '0'.$to_month;
		
		$mfromdate = $year.'-'.$from_month.'-01';
		$mtodate = $year.'-'.$to_month.'-31';
		$yearmonth = $year.'-'.$from_month;
		
		$terminal = $_REQUEST['terminal'];
		$from_category_id = $_REQUEST['from_category_id'];
		$to_category_id = $_REQUEST['to_category_id'];

		$cGraph['year'] = $year;
		$cGraph['g1'] = $g1;
		$cGraph['from_category_id'] = $from_category_id;
		$cGraph['to_category_id'] = $to_category_id;
		$cGraph['terminal'] = $terminal;
		$cGraph['top'] = $top;

		$tables = currTables($mfromdate);
		$sales_header = $tables['sales_header'];
		$sales_detail = $tables['sales_detail'];
		$sales_tender = $tables['sales_tender'];
	

		$term = terminal($terminal);
		if ($terminal == '')
		{
			$term['SERIAL'] = '';
			$term['TERMINAL'] = 'ALL TERMINALS';
		}

//		$term['ip'] = '127.0.0.1';
		
		$header ="<reset>\n";
		$header .= $SYSCONF['BUSINESS_NAME']."\n";
//		$header .= $SYSCONF['BUSINESS_ADDR']."\n";
		$header .= 'MONTHLY CATEGORY SALES REPORT'."\n";
		$header .= 'Transaction From : '.cmonth($from_month).' To '.cmonth($to_month).' '.$year.'    ';
		$header .= 'Printed: '.date('m/d/Y g:ia')."\n\n";
		
		$title = adjustSize(' Category',24).' ';
		$title1 = str_repeat('-',24).' ';
		for ($c=intval($from_month);$c<=intval($to_month);$c++)
		{
			$title .= adjustSize(cmonth($c),13).' ';
			$title1 .= str_repeat('-',13).' ';
		}
		$title .= adjustSize('Total',13).' '.
					adjustSize('  %',6)."\n";
		$title1 .= str_repeat('-',13).' '.
					  str_repeat('-',6)."\n";

		$header .= $title.$title1;
		$page = 1;
		$lc = 10;
		$details = '';
		$details2 = '';

//echo "fr ".$from_month;		
//		echo "<pre>$header</pre>";
//		exit;
		$q = "select 
					sum(rd.qty) as qty,
					sum(rd.amount) as amount,
					substring(date,6,2) as month,
					category.seqn
				from
					$sales_detail as rd,
					$sales_header as rh,
					stock,
					category
				where
					rh.sales_header_id=rd.sales_header_id and
					stock.stock_id=rd.stock_id and
					category.category_id=stock.category_id and 
					(rh.status != 'V' and rh.status !='C') and
					rh.date >=  '$mfromdate' and
					rh.date <=  '$mtodate'";
 
		if ($terminal != '')
		{
			$q .= " and terminal='$terminal'";
		}
		$q .= "	group by 
			substring(date,6,2), category.seqn";
					
		if ($g1 == 'Preview')
		{
			$q .= " order by category.seqn ";	
		}
		else
		{
			$q .= " order by sum(rd.amount) desc ";	
		}
		if ($top != '' )
		{
			$q .= " offset 0 limit $top  ";
		}

		$qr = @pg_query($q) or message(pg_errormessage());
		$total_amount = 0;
		$total_qty = 0;
		$ctr = 0;
		
		$data = null;
		$leg = null;
		$data = array();
		$leg = array();
		$xtick=null;
		$xtick=array();
		$aRep = null;
		$aRep = array();
		$grand_total = 0;
		while ($r = @pg_fetch_object($qr))
		{
			$c = $fnd = 0;
			foreach ($aRep as $temp)
			{
				if ($temp['seqn'] == $r->seqn)
				{
					$dummy = $temp;
					$dummy[$r->month] += $r->amount;
					$dummy['total'] += $r->amount;
					$aRep[$c] = $dummy;
					$fnd = 1;
					break;
				}
				$c++;
			}
			if ($fnd == '0')
			{
				$dummy = null;
				$dummy = array();
				$dummy['seqn'] = $r->seqn;
				$dummy[$r->month] = $r->amount;
				$dummy['total'] = $r->amount;
				$aRep[] = $dummy;
			}
			$grand_total += $r->amount;
		}
		
		$atemp =null;
		$atemp = array();

		foreach ($aRep as $temp)
		{
			$temp1 = intval($temp['seqn']);
			$atemp[] = $temp1;
		}
		if (count($atemp)>0)
		{
			asort($atemp);
			reset($atemp);
		}

		$mseqn = '~~';
		$aTotal = null;
		$aTotal = array();
		//foreach ($aRep as $temp)
		while (list($key,$val) = each ($atemp))
		{
			$temp = $aRep[$key];
			if ($temp['seqn'] != $mseqn)
			{
				if ($temp['seqn'] == '')
				{
					$mseqn = $temp['seqn'];
					$mcategory_code = '';
					$mcategory = 'No Category Sequence';
				}
				else
				{
					$q = "select * from category where enable = 'Y' and seqn = '".$temp['seqn']."' order by category_code offset 0 limit 1";
					$qqr = @pg_query($q) or message(pg_errormessage()); 
					$rr = @pg_fetch_object($qqr);

					$mseqn = $temp['seqn'];
					$mcategory_code = $rr->category_code;
					$mcategory = $rr->category;
				}


				if ($p1 == 'Print Draft' && $lc > 60)
				{
					$details .= "<eject>";
					$details2 .= $header.$details;
					//nPrinter($header.$details, $SYSCONF['RECEIPT_PRINTER_TYPE'], $SYSCONF['RECEIPT_PRINTER_DEST']);
					$lc=10;
					$page++;
					//$details = '';
				}

				
				$details .= adjustSize($mcategory_code,3).' '.
						adjustSize($mcategory,20).' ';
				
			}
			for ($c = 1*$from_month; $c<=1*$to_month; $c++)
			{
				if (strlen($c) == 1)
				{
					$fld = '0'.$c;
				}
				else
				{
					$fld = $c;
				}
				$details .= adjustRight(number_format($temp[$fld],2),13)." ";
				$aTotal[$fld] += $temp[$fld];
			}
			$details .= adjustRight(number_format($temp['total'],2),13)." ";
			$details .= adjustRight(round($temp['total']*100/$grand_total,2),6);

			$details .= "\n\n";
			$lc++;
			$lc++;
		}
		$details .= "\n";
		$details .= $title1;
		$details .= space(25);
		for ($c = 1*$from_month; $c<=1*$to_month; $c++)
		{
			if (strlen($c) == 1)
			{
				$fld = '0'.$c;
			}
			else
			{
				$fld = $c;
			}
			$details .= adjustRight(number_format($aTotal[$fld],2),13)." ";
		}
		$details .= adjustRight(number_format($grand_total,2),13)." ";
		$details .= adjustRight('100.00',6)."\n";
		$details .= $title1;

		$details2 .= $header.$details;

		if ($p1 == 'Print Draft' or $g1 == 'Print Draft')
		{
			nPrinter($header.$details, $SYSCONF['RECEIPT_PRINTER_TYPE'], $SYSCONF['RECEIPT_PRINTER_DEST']);
			$page++;
		}
		//echo "g1 $g1 ";
		if (in_array($g1, array('bar','pie','line')))
		{
			$cGraph['data'] = $data;
			$cGraph['leg'] = $leg;
			$cGraph['xtick'] = $xtick;
			$cGraph['g1'] = $g1;
			$cGraph['total_amount'] = $total_amount;		
		}
		
} //printing
?> 
<form name='form1' method='post' action=''>
  <table width="80%" border="0" align="center" cellpadding="0" cellspacing="2">
    <tr bgcolor="#EFEFEF"> 
      <td height="24" colspan="4" 
background="../graphics/table0_horizontal.PNG"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <strong> <img src="../graphics/bluelist.gif" width="16" height="17"><font color="#FFFFCC"> 
        Monthly Category Sales Report</font></strong></font></td>
    </tr>
    <tr bgcolor="#EFEFEF"> 
      <td width="7%" nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">From</font></td>
      <td width="4%" nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        To</font></td>
      <td width="4%" nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">Year</font></td>
      <td width="85%" nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">Output</font></td>
    </tr>
    <tr> 
      <td nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"><?= lookUpMonth('from_month',$from_month);?>
        </font></td>
      <td nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <?= lookUpMonth('to_month',$to_month);?>
        </font></td>
      <td nowrap> 
        <input name="year" type="text" id="year" value="<?= $year;?>" size="4" maxlength="4">
        </td>
      <td nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= lookUpAssoc('g1',array('Print Preview'=>'Preview',"Bar Graph"=>'bar',"Pie Chart"=>'pie', 'Line Graph'=>'line'),$cGraph['g1']);?>
        <input name="p1" type="Submit" id="p1" value="Go">
        </font></td>
    </tr>
    <tr bgcolor="#333366"> 
      <td height="26" bgcolor="#DADADA" colspan="4"> <font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"><b>Report 
        Preview</b></font></td>
    </tr>
    <tr> 
      <td valign="top" bgcolor="#FFFFFF" colspan="4"> 
        <?
	  if ($p1= 'Go' && in_array($cGraph['g1'], array('bar','pie','line')))
	  {
	  	echo "<IFRAME SRC='graph.category.php' name='print_area' TITLE='Sales' WIDTH=750 HEIGHT=370 FRAMEBORDER=0></IFRAME>";
	  }
	  else
	  {
		 echo "<textarea name='print_area' cols='97' readonly rows='20' wrap='OFF'>$details2</textarea>";
		}	  
		?>
      </td>
    </tr>
  </table>
<div align=center>
    <input name="p1" type="submit" id="p1" value="Print Draft">
    <input name="p122" type="button" id="p122" value="Print"  onClick="printIframe(print_area)" >
  </div>
</form>

<iframe name="printit" id="printit" style="width:0px;height:0px;"></iframe>
