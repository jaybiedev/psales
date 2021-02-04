 <script language="JavaScript" type="text/JavaScript">
<!--
function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);
//-->
</script>
<?

if ($to_date == '') $to_date=date('m/d/Y');
if ($from_date == '') $from_date=addDate($to_date,-30);
if ($account_type_id == '' && $p1 == '') $account_type_id = 5;

if ($p1=='Go' || $p1=='Print Draft')
{
	$mfrom_date = mdy2ymd($from_date);
	$mto_date = mdy2ymd($to_date);
	
	$tables = currTables($mto_date);
	$sales_header = $tables['sales_header'];
	$sales_detail = $tables['sales_detail'];
	$sales_tender = $tables['sales_tender'];

	$q = "select 
			$sales_header.account_id,
			account,
			cardno,
			sum($sales_header.net_amount) as amount
		from 
			$sales_header,
			account
		where 
			$sales_header.account_id=account.account_id and 
			$sales_header.date>='$mfrom_date' and 
			$sales_header.date<='$mto_date' and
			$sales_header.status!='V' ";
	if ($account_class_id != '')
	{
		$q .= " and account.account_class_id = '$account_class_id'";
	}			
				
	$q .= " group by
				$sales_header.account_id, account, cardno
			order by 
				amount desc ";
	if ($top != '')
	{
			$q .= " offset 0 limit $top ";
	}

	$qr = @pg_query($q) or message1(pg_errormessage() .$q);

	$header .= "\n\n";
	$header .= center($SYSCONF['BUSINESS_NAME'],80)."\n";
	$header .= center("TOP $top SALES BY CUSTOMER",80)."\n";
	$header .= center($from_date.' To '.$to_date,80)."\n\n";
	$header .= "  #      Account                             DryGoods    Grocery        Total     	\n";
	$header .= "------ ----------------------------------- ----------- ----------- ------------\n";
	$details = $details1 = '';
	$total_amount = $total_drygood = $total_grocery = 0;
	$subtotal  = 0;
	$lc=8;
	$ctr=0;
	$this_year = date('Y');
	while ($r = pg_fetch_object($qr))
	{
		$lc++;
		$ctr++;
		$details.= ' '.adjustRight($ctr,4).'. '.
					adjustSize($r->account,25).' '.
					adjustRight($r->cardno,8).'  ';

			$q = "select 
					sum($sales_detail.amount) as amount,
					category.department
				from 
					$sales_header,
					$sales_detail,
					category,
					stock
				where 
					$sales_header.sales_header_id = $sales_detail.sales_header_id and 
					$sales_header.account_id = '$r->account_id' and 
					$sales_header.date>='$mfrom_date' and 
					$sales_header.date<='$mto_date' and
					$sales_header.status!='V'  and
					stock.stock_id = $sales_detail.stock_id and
					category.category_id=stock.category_id";
					
			$q .= " group by  department ";
			$qqr = @pg_query($q) or message1(pg_errormessage().$q);

		$d = $g = 0;
		while ($rr	= @pg_fetch_object($qqr))
		{				
			
			if ($rr->department  == 'D')
				$d = $rr->amount;
			else
				$g = $rr->amount;
		}			

		$details .= adjustRight(number_format($d,2),11).' '.
						adjustRight(number_format($g,2),11).' '.
						adjustRight(number_format($r->amount,2),12)."\n";
						
		$total_drygood += $d;
		$total_grocery += $g;
		$total_amount += $r->amount;

		if ($lc>55 && $p1 == 'Print Draft')
		{
			$details1 .= $header.$details;
			$details .= "<eject>";
			doPrint($header.$details);
			$lc=8;
		}			
	}

	$details .= "------ ----------------------------------- ----------- ----------- ------------\n";
	$details .= space(5).adjustSize('TOTAL COUNT :'.$ctr,39).
					adjustRight(number_format($total_drygood,2),11).' '.
					adjustRight(number_format($total_grocery,2),11).' '.
					adjustRight(number_format($total_amount,2),11)."\n";
	$details .= "------ ----------------------------------- ----------- ----------- ------------\n";
	
	
	$details1 .= $header.$details;
	if ($p1=='Print Draft')
	{
		$details .= "<eject>";
		doPrint($header.$details);
	}	
}	
?>	
<form name="form1" method="post" action="">
  <div align="center">
    <table width="75%" border="0">
      <tr background="../graphics/table0_horizontal.PNG"> 
        <td colspan="4"><font color="#FFFFFF" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong><img src="../graphics/redlist.gif" width="16" height="17"> 
          Sales by Customer/ Department</strong></font></td>
      </tr>
      <tr> 
        <td width="10%" nowrap><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
          Dates From<br>
          </font><font size="3" color="#000000"><font face="Verdana, Arial, Helvetica, sans-serif"><strong><font size="2"> 
          <input name="from_date" type="text" id="from_date"  onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $from_date;?>" size="8">
          </font><font size="2" color="#000000"><img src="../graphics/dwn-arrow-grn.gif" onClick="popUpCalendar(this, form1.from_date, 'mm/dd/yyyy')"></font><font size="2"> 
          </font></strong></font></font><font size="2">&nbsp; </font></font></font></td>
        <td width="10%" nowrap><font size="2" color="#000000"><font face="Verdana, Arial, Helvetica, sans-serif">To<br>
          </font> 
          <input type="text" name="to_date" value="<?= $to_date;?>" size="8"  onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')"></strong> 
          </font><font size="3" color="#000000"><font face="Verdana, Arial, Helvetica, sans-serif"><strong><font size="3" color="#000000"><font face="Verdana, Arial, Helvetica, sans-serif"><strong><font size="2" color="#000000"><img src="../graphics/dwn-arrow-grn.gif" onClick="popUpCalendar(this, form1.to_date, 'mm/dd/yyyy')"></font></strong></font></font></strong></font></font><font face="Verdana, Arial, Helvetica, sans-serif"><strong> 
          </strong></font></font><font face="Verdana, Arial, Helvetica, sans-serif"><strong> 
          </strong></font></font></td>
        <td width="17%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Account 
          Classification</font><br> <font size="2" color="#000000"> 
          <select name='account_class_id' style="width:240px">
            <option value="">All Accounts</option>
            <?
			$q = "select * from account_class where enable='Y' order by account_class";
			$qr = @pg_query($q);
			while ($r = @pg_fetch_object($qr))
			{
				if ($r->account_class_id == $account_class_id)
				{
					echo "<option value=$r->account_class_id selected>$r->account_class</option>";
				}
				else
				{
					echo "<option value=$r->account_class_id >$r->account_class</option>";
				}
			}
			?>
          </select>
          </font> </td>
        <td width="63%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Top</font><br> 
          <input name="top" type="text" id="top" value="<?= $top;?>" size="5"> 
          <input name="p1" type="submit" id="p1" value="Go"> </td>
      </tr>
      <tr> 
        <td colspan="4"> <hr color="red" size="1"> </td>
      </tr>
      <tr background="../graphics/table0_horizontal.PNG"> 
        <td colspan="4"><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>&nbsp;<img src="../graphics/bluelist.gif"> 
          Top Customer Sales / Department Print Preview</strong></font><font  color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
          </strong></font></td>
      </tr>
      <tr>
        <td colspan="4"><textarea name="print_area" cols="110" rows="18" readonly wrap="OFF"><?= $details1;?></textarea></td>
      </tr>
    </table>
    <input name="p1" type="submit" id="p1" value="Print Draft" >
    <input name="p12" type="button" id="p12" value="Print"  onClick="printIframe(print_area)" >
  </div>
</form>

<iframe name="printit" id="printit" style="width:0px;height:0px;"></iframe>
