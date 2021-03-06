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
	
	$q = "select 
					stock.stock,
					sd.qty,
					sd.fraction,
					sd.price1,
					sd.price,
					sd.cdisc,
					sd.sdisc,
					sd.discount,
					sh.discount_id,
					sh.invoice,
					sh.date,
					sh.terminal,
					sh.admin_id
				from 
					$sales_header as sh,
					$sales_detail as sd,
					stock
			where 
					sh.sales_header_id=sd.sales_header_id and
					stock.stock_id=sd.stock_id  and 
					sh.date>='$msd'  and 
					sh.date<='$med'  and
					sh.status!='V'  and
					sd.price1 != sd.price and
					stock.taxable ='Y' ";
			if ($percent_discount != '')
			{
				if (in_array(substr($percent_discount, 0,1), array('>','<','=')))
				{
					if ($show == 'M')
					{
						$q .= " and sd.cdisc $percent_discount ";
					}
					elseif ($show == 'P')
					{
						$q .= " and sd.sdisc $percent_discount ";
					}
					else
					{
						$q .= " and (sd.sdisc $percent_discount OR sd.cdisc $percent_discount )";
					}
				}
				else
				{
					if ($show == 'M')
					{
						$q .= " and sd.cdisc = '$percent_discount'";
					}
					elseif ($show == 'P')
					{
						$q .= " and sd.sdisc = '$percent_discount'";
					}
					else
					{
						$q .= " and (sd.sdisc = '$percent_discount' OR sd.cdisc = '$percent_discount') ";
					}
				}
			}
			if ($terminal != '')
			{
				$q .= " and sh.terminal = '$terminal'";
			}
			if ($show == 'M')
			{
				$q .= " and sd.cdisc > 0 ";
			}
			elseif ($show == 'P')
			{
				$q .= " and sd.sdisc > 0 ";
//				$q .= " and (sh.remarks like 'Line Discount%'  or sh.remarks like 'Global Discount%')";
			}
			elseif ($show == 'O')
			{
				$q .= " and  sh.remarks =''  and discount='0'";
			}
			$q .= " order by	terminal, invoice ";
//echo $q;
	$qr = @query($q) or message1(db_error().$q);


	$header = "\n\n\n";
	$page=1;
	$header .= center($SYSCONF['BUSINESS_NAME'],134)."\n";
	$header .= center('CHANGED PRICE SALES REPORT',134)."\n";
	$header .= center('Date '.$sd.' To '.$ed,134)."\n";
	$header .= center('Printed:'.date('m/d/Y'),134)."\n";
	$header .= str_repeat('-',134)."\n";
	$header .= '                                                               Regular  Discounted    %'."\n";
	$header .= '  Date      Invoice    Item Description                 Qty     Price       Price    Disc.   Variance  Type          Cashier   '."\n";
	$header .= str_repeat('-',134)."\n";
	$lc=10;
	$ctr=0;
	$details = $details1 ='';
	$mterminal ='';	
	while ($r=@pg_fetch_object($qr))
	{
		$ctr++;
		$discount_type = '';
		if ($r->discount_id > '0')
		{
			$discount_type = lookUpTableReturnValue('x','discount','discount_id','discount',$r->discount_id);
		}
		if ($r->qty == intval($r->qty))
		{
			$cqty = number_format($r->qty,0);
		}
		else
		{
			$cqty = number_format($r->qty,3);
		}
		$pdisc= '';
		if ($r->cdisc > '0')
		{
			$pdisc = $r->cdisc;
		}
		elseif ($r->sdisc > '0')
		{
			$pdisc = $r->sdisc;
		}
		else
		{
			$pdisc = $r->discount;
		}
		if ($mterminal != $r->terminal)
		{
			$details .= "\nCashier : ".$r->terminal.'  - '.lookUpTableReturnValue('x','admin','admin_id','username',$r->admin_id);
			$details .= "\n";
			$mterminal = $r->terminal;
		}
		$variance = $r->price1 - $r->price;
		$details .= 
					adjustSize(ymd2mdy($r->date),10).'  '.
					adjustSize($r->invoice,10).' '.
					adjustSize($r->stock,30).' '.
					adjustRight($cqty,5).' '.
					adjustRight(number_format($r->price1,2),11).' '.
					adjustRight(number_format($r->price,2),11).' '.
					adjustRight(number_format($pdisc,2),6).' '.
					adjustRight(number_format($variance,2),11).' '.
					adjustSize($discount_type,11).' '.
					adjustSize(lookUpTableReturnValue('x','admin','admin_id','name',$r->admin_id),15).' '.
					"\n";
					
		$total_variance += $variance;			

		$lc++;			
		if ($lc > 55)
		{
			$details1 .= $header.$details;
			if  ($p1 == 'Print Draft' )
			{
				$details .= "<eject>";
				nPrint ($header.$details, $SYSCONF['REPORT_PRINTER_TYPE'], $SYSCONF['REPORT_PRINTER_DEST']);
			}
			$lc=10;
			$details = '';
		}	
		

	} //while
	$details .= str_repeat('-',134)."\n";
	$details .= 
					adjustSize(' ',7).'  '.
					adjustSize(number_format($ctr,0).' Line(s)',25).' '.
					space(56).
					adjustRight(number_format($total_variance,2),11).' '.
					"\n";
	$details .= str_repeat('=',134)."\n\n\n\n";
	$details1 .= $header.$details;
	if ($p1 =='Print Draft' || $p1 =='Print' )
	{
			nPrinter($header.$details, $SYSCONF['REPORT_PRINTER_TYPE'], $SYSCONF['REPORT_PRINTER_DEST']);
	}


} //with print

?> <br>
<form name='form1' method='post' action=''>
  <table width="75%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr background="../graphics/table0_horizontal.PNG"> 
      <td height="29" colspan="5"><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
        <img src="../graphics/bluelist.gif"> <strong> Sales Changed Price Report</strong></font></td>
    </tr>
    <tr valign="top"> 
      <td width="11%" nowrap> <font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        Date From<br>
        <input name="sd" type="text" id="sd" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $sd;?>" size="10">
        <img src="../graphics/dwn-arrow-grn.gif" width="12" height="12" onClick="popUpCalendar(this, form1.sd, 'mm/dd/yyyy')"> 
        </font> </td>
      <td width="11%" nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">To<br>
        <input name="ed" type="text" id="ed" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $ed;?>" size="10">
        <img src="../graphics/dwn-arrow-grn.gif" width="12" height="12" onClick="popUpCalendar(this, form1.ed, 'mm/dd/yyyy')"> 
        </font></td>
      <td width="4%" nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">Show<br>
        <?=lookUpAssoc('show',array('Price Overwrite'=>'O','Manual Discount'=>'M','Promo Discount'=>'P','All Discounted'=>'A'),$show);?>
        </font></td>
      <td width="9%" nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        %Discount<br>
        <input type="text" size="7" name="percent_discount" value="<?=$percent_discount;?>">
        </font></td>
      <td width="65%" nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        Terminal <font size="1">(* use &gt;, &lt;, = , &lt;=, &gt;= or &lt;&gt; 
        for discount range)</font> <br>
        </font><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="terminal" type="text" id="terminal" value="<?=$terminal;?>" size="4">
        <input name="p1" type="Submit" id="p123" value="Go">
        <input type="button" name="Submit232" value="Close" onClick="window.location='?p='" accesskey="C">
        </font></td>
    </tr>
    <tr> 
      <td colspan="5"><hr color="#993300"></td>
    </tr>
    <tr bgcolor="#B5CFD5"> 
      <td height="27" colspan="5"> <font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"><b>Report 
        Preview</b></font></td>
    </tr>
    <tr> 
      <td valign="top" bgcolor="#FFFFFF" colspan="5"> <textarea name="print_area" cols="110" rows="20" wrap="OFF" readOnly><?= $details1;?></textarea></td>
    </tr>
  </table>
  <div align=center>
    <input name="p1" type="submit" id="p1" value="Print Draft"  >
    <input name="p122" type="button" id="p122" value="Print"  onClick="printIframe(print_area)" >
  </div>
</form>

<iframe name="printit" id="printit" style="width:0px;height:0px;"></iframe>
