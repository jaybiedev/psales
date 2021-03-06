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

	$cGraph['from_date'] = ymd2mdy(yesterday());
	$cGraph['to_date'] = ymd2mdy(yesterday());
	$cGraph['top'] = '';
}
if ($p1 == '')
{
		$cGraph['from_date'] = ymd2mdy(yesterday());
		$cGraph['to_date'] = ymd2mdy(yesterday());

		$q = "select * from area where area_id='".$SYSCONF['AREA_ID']."'";
		$qr = @pg_query($q);
		$r = @pg_fetch_object($qr);
	
		$from_category_code = $r->from_category;
		$to_category_code = $r->to_category;
		$from_category_id = lookUpTableReturnValue('x','category','category_code','category_id',$from_category_code);
		$to_category_id = lookUpTableReturnValue('x','category','category_code','category_id',$to_category_code);
}
	
	if ($p1=="Go" || $p1=='Print Draft' ||  $p1=='Print') 
	{
		$from_date = $_REQUEST['from_date'];
		$to_date = $_REQUEST['to_date'];
		$mfrom_date = mdy2ymd($from_date);
		$mto_date = mdy2ymd($to_date);

		$terminal = $_REQUEST['terminal'];
		$from_category_id = $_REQUEST['from_category_id'];
		$to_category_id = $_REQUEST['to_category_id'];

		$cGraph['from_date'] = $from_date;
		$cGraph['mfrom_date'] = $mfrom_date;

		$cGraph['to_date'] = $to_date;
		$cGraph['mto_date'] = $mto_date;

		$cGraph['g1'] = $g1;
		$cGraph['from_category_id'] = $from_category_id;
		$cGraph['to_category_id'] = $to_category_id;
		$cGraph['terminal'] = $terminal;
		$cGraph['top'] = $top;

		$tables = currTables($mfrom_date);
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
		
		$header ="\n";
		$header .= $SYSCONF['BUSINESS_NAME']."\n";
		$header .= $SYSCONF['BUSINESS_ADDR']."\n";
		$header .= 'CATEGORY SALES REPORT DETAILED BY BARCODE'."\n";
		$header .= 'Transaction Date : '.$from_date.' To '.$to_date.'  ';
		$header .= 'Printed : '.date('m/d/Y g:ia')."\n\n";
		$header .= ' Category             Items                             Qty        Amount'."\n";
		$header .= '----------------------------------------------------- -------- -------------'."\n";
		$page = 1;
		$lc = 10;
		$details = '';
		$details2 = '';
		
		$from_category_code  = $to_category_code = '';
		if ($from_category_id != '')
		{
			$from_category_code = lookUpTableReturnValue('x','category','category_id','category_code',$from_category_id);
		}
		if ($to_category_id != '')
		{
			$to_category_code = lookUpTableReturnValue('x','category','category_id','category_code',$to_category_id);
		}
		$from_len = strlen($from_category_code);
		$to_len = strlen($to_category_code);		
		
		$q = "select 
					sum(rd.qty) as qty,
					sum(rd.amount) as amount,
					category.category_code,
					stock.barcode
				from
					$sales_detail as rd,
					$sales_header as rh,
					stock,
					category
				where
					rh.sales_header_id=rd.sales_header_id and
					stock.stock_id=rd.stock_id and
					category.category_id=stock.category_id and 
					rh.date >= '$mfrom_date' and
					rh.date <= '$mto_date' and
					(rh.status != 'V' and rh.status !='C') ";

		if( !empty( $from_category_code ) && !empty( $to_category_code ) ) {
			
			 $q .= " and substr(category.category_code,1,$from_len)>='$from_category_code'";
			 $q .= " and substr(category.category_code,1,$to_len)<='$to_category_code'";	
			/*
			
			$q .= "
				and substr(ccategory,'1',length('$from_category_code')) >= '$from_category_code'
				and substr(ccategory,'1',length('$to_category_code')) <= '$to_category_code'
			";
			*/

		}
 
		$q .= "	group by 
						category.category_code,stock.barcode";

		$q .= " order by substring(category.category_code,1,2), stock.barcode ";	

		$total_amount = 0;
		$total_qty = 0;
		$ctr = 0;
		
		$data = null;
		$leg = null;
		$data = array();
		$leg = array();
		$xtick=null;
		$xtick=array();

		//echo $q;
		
		$qqr = @pg_query($q) or message1(pg_errormessage().$q);
		
		while ($r = @pg_fetch_object($qqr))
		{
			if (($top == '' && count($data) < 15) || $top != '')
			{
				$data[] = $r->amount;
				$leg[] = $r->category. '('.number_format($r->amount,2).')';
				$xtick[] = $r->category_code;
			}
			
			if (intval($r->qty) != $r->qty)
			{
				$cqty = number_format($r->qty,3);
			}
			else
			{
				$cqty = number_format($r->qty,0);
			}
			$ctr++;
			
			if ($category_code != substr($r->category_code,0,2))
			{
				if ($category_code != '') $details .= "\n";
				$category = lookUpTableReturnValue('x','category','category_code','category',$r->category_code);
				$details .= "CATEGORY : ".adjustSize(substr($r->category_code,0,2),5).' '.$category."\n";
				$category_code = substr($r->category_code,0,2);

			}
			$stock_description = lookUpTableReturnValue('x','stock','barcode','stock_description',$r->barcode);
			$details .= adjustSize($r->barcode,16).' '.
						adjustSize($stock_description,35).' '.
						adjustRight($cqty,7).' '.
						adjustRight(number_format($r->amount,2),13)."\n";
			$total_amount += $r->amount;
			$total_qty += $r->qty;			
			$lc++;
			if ($p1 == 'Print Draft' && $lc > 58)
			{
				//$details .= "Page ".$page."<eject>\n\n";
				doPrint($header.$details);
				$lc=10;
				$page++;
				$details2 .= $header.$details;
				$details = '';
			}
		}
		$details .= space(50).'------------ -----------'."\n";
		$details .= space(5).adjustSize($ctr.' Total Items',15).space(30).
						adjustRight(number_format($total_qty,3),10).' '.
						adjustRight(number_format($total_amount,2),13)."\n";
		$details .= space(50).'============ ==========='."\n";
		$details2 .= $header.$details;

		if ($p1 == 'Print Draft' or $g1 == 'Print Draft')
		{
			//$details .= "Page ".$page."<eject>\n\n";
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
      <td height="24" colspan="5" 
background="../graphics/table0_horizontal.PNG"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <strong> <img src="../graphics/bluelist.gif" width="16" height="17"><font color="#FFFFCC">Category 
        Sales Detailed Report</font></strong></font></td>
    </tr>
    <tr bgcolor="#EFEFEF"> 
      <td width="12%" align="center" nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">Date 
        From </font></td>
      <td width="11%" align="center" nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">Date 
        To </font></td>
      <td width="25%" nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        From </font></td>
      <td width="23%" nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">To</font></td>
      <td width="29%" nowrap>&nbsp;</td>
    </tr>
    <tr> 
      <td align="right" nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="from_date" type="text" id="from_date" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $cGraph['from_date'];?>" size="8">
        <img src="../graphics/dwn-arrow-grn.gif" height="12" width="12" onClick="popUpCalendar(this, form1.from_date, 'mm/dd/yyyy')"> 
        </font> <font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
        </font></td>
      <td nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="to_date" type="text" id="to_date" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $cGraph['to_date'];?>" size="8">
        <img src="../graphics/dwn-arrow-grn.gif" height="12" width="12" onClick="popUpCalendar(this, form1.to_date, 'mm/dd/yyyy')"> 
        </font></td>
      <td nowrap> <select name="from_category_id"   style="border: #CCCCCC 1px solid; width:180px">
          <option value=''>All Categories--</option>
          <?
		foreach ($aCATEGORY as $ctemp)
		{
			if ($SYSCONF['SORT_CATEGORY'] == 'category')
			{
				$category_code = '';
			}
			else
			{
				$category_code = substr($ctemp['category_code'],0,6);
			}
			if ($ctemp['category_id'] == $from_category_id)
			{
				echo "<option value=".$ctemp['category_id']." selected>".$category_code." ".$ctemp['category']."</option>";
			}
			else
			{
				echo "<option value=".$ctemp['category_id']." >".$category_code." ".$ctemp['category']."</option>";
			}
		}
	  ?>
        </select>
        -</td>
      <td nowrap> <select name="to_category_id"   style="border: #CCCCCC 1px solid; width:180px">
          <option value=''>All Categories--</option>
          <?
		foreach ($aCATEGORY as $ctemp)
		{
			if ($SYSCONF['SORT_CATEGORY'] == 'category')
			{
				$category_code = '';
			}
			else
			{
				$category_code = substr($ctemp['category_code'],0,6);
			}
			if ($ctemp['category_id'] == $to_category_id)
			{
				echo "<option value=".$ctemp['category_id']." selected>".$category_code." ".$ctemp['category']."</option>";
			}
			else
			{
				echo "<option value=".$ctemp['category_id']." >".$category_code." ".$ctemp['category']."</option>";
			}
		}
	  ?>
        </select> </td>
      <td nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="p1" type="Submit" id="p1" value="Go">
        </font></td>
    </tr>
    <tr bgcolor="#333366"> 
      <td height="26" bgcolor="#DADADA" colspan="5"> <font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"><b>Report 
        Category Sales Preview</b></font></td>
    </tr>
    <tr> 
      <td valign="top" bgcolor="#FFFFFF" colspan="5"> 
        <?
	  if ($p1= 'Go' && in_array($cGraph['g1'], array('bar','pie','line')))
	  {
	  	echo "<IFRAME SRC='graph.category.php' name='print_area' TITLE='Sales' WIDTH=750 HEIGHT=370 FRAMEBORDER=0></IFRAME>";
	  }
	  else
	  {
		 echo "<textarea name='print_area'  readOnly cols='97' rows='20' wrap='OFF'>$details2</textarea>";
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
