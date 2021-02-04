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

	if (substr($mto_date,0,4) != substr($mfrom_date,0,4))
	{
		$year = substr($mfrom_date,0,4);
		message1("<br>[  Cannot generate transaction crossing different years at this time... Generating report for : $year only...]\n");
	}
		
	$tables = currTables($mfrom_date);
	$sales_header = $tables['sales_header'];
	$sales_detail = $tables['sales_detail'];
	$sales_tender = $tables['sales_tender'];


	$q = "select 
			st.account,
			st.tender_id,
			sum(st.amount) as amount
		from 
			$sales_tender as st,
			$sales_header as sh
		where 
			sh.sales_header_id=st.sales_header_id and 
			sh.date>='$mfrom_date' and 
			sh.date<='$mto_date' and
			sh.status!='V' ";

	if ($account != '')
	{
		if ($show == 'S')
		{
			$q .= " and account ilike '$account%' ";
		}
		elseif ($show == 'E')
		{
			$q .= " and account ilike '%$account' ";
		}
		else
		{
			$q .= " and account ilike '%$account%' ";
		}
	}				
	$q .= " group by
				st.account, st.tender_id
			order by 
				st.account, st.tender_id ";
				
	if ($top != '')
	{
			$q .= " offset 0 limit $top ";
	}

	$qr = @pg_query($q) or message1(pg_errormessage());

	$header .= "\n\n";
	$header .= center($SYSCONF['BUSINESS_NAME'],80)."\n";
	$header .= center("SALES BY CUSTOMER NAME (WILDCARD)",80)."\n";
	$header .= center($from_date.' To '.$to_date,80)."\n\n";
	$header .= "  #        Account                                        Amount of Sales  	\n";
	$header .= "-------  ------------------------------------------------ -------------------\n";
	$details = $details1 = '';
	$total_amount =0;
	$subtotal  = 0;
	$lc=8;
	$ctr=0;
	$this_year = date('Y');
	while ($r = pg_fetch_object($qr))
	{
		$lc++;
		$ctr++;
		$details.= ' '.adjustRight($ctr,4).'.   '.
					adjustSize($r->account,35).'   '.
					adjustSize(lookUpTableReturnValue('x','tender','tender_id','tender',$r->tender_id),15).'   '.
					adjustRight(number_format($r->amount,2),12).'  '.
					"\n";
					
		if ($lc>55 && $p1 == 'Print Draft')
		{
			$details1 .= $header.$details;
			$details .= "<eject>";
			doPrint($header.$details);
			$lc=8;
		}			
	}

	$details .= "-------  ------------------------------------------------ -------------------\n";
	$details .= space(5).adjustSize('TOTAL COUNT :'.$ctr,25)."\n";
	$details .= "-------  ------------------------------------------------ -------------------\n";
	
	
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
          Sales by Customer Name</strong></font></td>
      </tr>
      <tr> 
        <td width="11%" nowrap><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
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
        <td width="31%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Name 
          of Customer </font><br>
          <font size="2" color="#000000"> 
          <input name="account" type="text" id="account" value="<?= $account;?>">
		  <?=lookUpAssoc('show',array('Contains'=>'C','Starts With'=>'S','Ends With'=>'E'),$show);?>
          </font> </td>
        <td width="48%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Top</font><br> 
          <input name="top" type="text" id="top" value="<?= $top;?>" size="5"> 
          <input name="p1" type="submit" id="p1" value="Go"> </td>
      </tr>
      <tr> 
        <td colspan="4"> <hr color="red" size="1"> </td>
      </tr>
      <tr background="../graphics/table0_horizontal.PNG"> 
        <td colspan="4"><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>&nbsp;<img src="../graphics/bluelist.gif"> 
          Top Customer Sales Print Preview</strong></font><font  color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
          </strong></font></td>
      </tr>
      <tr>
        <td colspan="4"><textarea name="print_area" cols="92" rows="18" readonly wrap="OFF"><?= $details1;?></textarea></td>
      </tr>
    </table>
    <input name="p1" type="submit" id="p1" value="Print Draft" >
    <input name="p12" type="button" id="p12" value="Print"  onClick="printIframe(print_area)" >
  </div>
</form>

<iframe name="printit" id="printit" style="width:0px;height:0px;"></iframe>
