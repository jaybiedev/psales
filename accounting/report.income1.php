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
if ($month == '' || $year == '')
{
	$month = intval(date('m'));
	$year = date('Y');
}	
if ($p1=='Go' || $p1=='Print Draft')
{
	if (strlen($month) == 1) $month = '0'.$month;
	//$mdate = $year.'-'.$month.'-31';
	$mdate = date("Y-m-d", mktime(0, 0, 0, $month+1, 0, $year));

	$this_month = $year.'-'.$month;
	$lastyear = intval($year) - 1;
	
	$q = "select 
			gchart.acode,
			gchart.scode,
			gchart.gchart,
			gchart.mclass,
			gltran_header.journal_id, 
			gltran_detail.gchart_id,
			sum(if(substr(date,1,7)='$this_month', debit, 0)) as debit_mtd,
			sum(if(substr(date,1,7)='$this_month', credit,0)) as credit_mtd,
			sum(if(substr(date,1,4)='$year', debit, 0)) as debit_ytd,
			sum(if(substr(date,1,4)='$year', credit, 0)) as credit_ytd,
			sum(if(substr(date,1,4)='$lastyear', debit, 0)) as debit_ly,
			sum(if(substr(date,1,4)='$lastyear', credit, 0)) as credit_ly
			
		from 
			gltran_header,
			gltran_detail,
			gchart,
			account
		where 
			gltran_header.gltran_header_id=gltran_detail.gltran_header_id and 
			gchart.gchart_id=gltran_detail.gchart_id and 
			account.account_id = gltran_header.account_id and 
			gltran_header.status!='C' and
			gltran_header.date<='$mdate' and
			gchart.mclass in ('I','E')";
	if ($pcenter_id != '')
	{
		$q .= " and pcenter_id='$pcenter_id'";
	}
	$q .= "group by 
				gltran_header.journal_id, 
				gltran_detail.gchart_id
			order by 
				mclass
			desc";
			
		//	sum(debit) as debit,
		//	sum(credit) as credit,
		//	substring(date,1,7) as date
		
	$qr = @pg_query($q) or message(pg_error());

	if ($pcenter_id != '')
	{
		$pcenter = '-'.lookUpTableReturnValue('x','pcenter','pcenter_id','pcenter',$pcenter_id);
	}
	$header = "\n\n";
	$header .= center(strtoupper($SYSCONF['BUSINESS_NAME'].$pcenter),130)."\n";
	$header .= center('INCOME STATEMENT',130)."\n";
	$header .= center('FOR THE MONTH ENDING '.strtoupper(cMonth($month)).', '.$year,130)."\n\n";
	$header .= "------------ ------------ ---- ------------------------------------------------------------ ------------ ---- ------------ ----------- ----\n";
	$header .= "      For the Month         %                                                                             %         YearToDate          %\n";
	$header .= "  Acutal       Budget     Inc               Account Description                              LastYear    Inc     Actual      Budget     Inc\n";
	$header .= "------------ ------------ ---- ------------------------------------------------------------ ------------ ---- ------------ ----------- ----\n";

	if ($p1 == 'Print Draft')
	{
		doPrint("<small3>");
	}
	$details = $details1 = '';
	$mjournal_id='';
	$ctr=$sctr=$total_debit = $total_credit = 0;
	$lc=8;
	$mcount=$subtotal_cost=$scount=0;
	while ($r = @pg_fetch_object($qr))
	{

		if ($mclass != $r->mclass)
		{
			if ($mclass != '')
			{
				if ($mclass == 'I') $m = 'REVENUE';
				else $m='EXPENSE';
				//$details .= space(80)."  --------------- ---------------\n";
				$details .= adjustRight(number_format2($total_mtd,2),12).' '.
						space(18).adjustSize($m.' TOTAL:'.adjustSize($ctr.' Item/s',44),44).space(17).
						adjustRight(number_format($total_ly,2),12).' '.
						space(5).
						adjustRight(number_format($total_ytd,2),12).' '."\n\n";
				$total_ly = $total_ytd = $total_mtd = $ctr = 0;
				$lc = $lc+3;
			}						
			if ($r->mclass == 'I') $m = 'REVENUE';
			else $m='EXPENSE';
			$details .= space(27).$m."\n";
			$mclass=$r->mclass;
			$lc = $lc+2;
		}
/*
		$debit_mtd= $debit_ytd= $debit_ly= $credit_mtd =$credit_ytd = $credit_ly = 0;
		if ($r->date < $lastyear) 
		{
			continue;
		}
		elseif (substr($r->date,0,4) == $lastyear)
		{
			$debit_ly = $r->debit;
			$credit_ly = $r->credit;
		}
		elseif (substr($r->date,0,4) == $year)
		{
			$debit_ly = $r->debit;
			$credit_ly = $r->credit;
		}
			sum(if(substring(date,1,7)='$this_month', debit, 0)) as debit_mtd,
			sum(if(substring(date,1,4)='$year', credit, 0)) as credit_mtd,
			sum(if(substring(date,1,4)='$year', debit, 0)) as debit_ytd,
			sum(if(substring(date,1,4)='$year', credit, 0)) as credit_ytd,
			sum(if(substring(date,1,4)='$lastyear', debit, 0)) as debit_ly,
			sum(if(substring(date,1,4)='$lastyear', credit, 0)) as credit_ly
		*/
		$ctr++;
		if ($r->mclass=='I')
		{
			$ytd_amount = $r->credit_ytd - $r->debit_ytd;
			$mtd_amount = $r->credit_mtd - $r->debit_mtd;			
			$ly_amount  = $r->credit_ly - $r->debit_ly;			
			$ytd_income += $ytd_amount;
		}
		else
		{
			$ytd_amount = $r->debit_ytd - $r->credit_ytd;
			$mtd_amount = $r->debit_mtd - $r->credit_mtd;			
			$ly_amount  = $r->debit_ly - $r->credit_ly;	
			$ytd_expense += $ytd_amount;
		}
		$total_ytd += $ytd_amount;
		$total_mtd += $mtd_amount;
		$total_ly += $ly_amount;
		
		$net_income_ytd += $r->debit_ytd - $r->credit_ytd;
		$net_income_ly += $r->debit_ly - $r->credit_ly;
		
		$details .= adjustRight(number_format2($mtd_amount,2),12).' '.
					space(12).'      '.
					adjustSize(substr($r->gchart,0,45),45).' '.
					adjustSize($r->acode.' '.$r->scode,14).' '.
					adjustRight(number_format2($ly_amount,2),12).' '.
					space(4).' '.
					adjustRight(number_format2($ytd_amount,2),12).' '."\n";
		$lc++;

		if ($lc>55)
		{
			$details1 .= $header.$details;
			if ($p1 == 'Print Draft')
			{
				doPrint($header.$details."<eject>");
			}
			$lc=8;
			$details = '';
		}

	}
	$details .= adjustRight(number_format2($total_mtd,2),12).' '.
			space(18).adjustSize($m.' TOTAL:'.adjustSize($ctr.' Item/s',44),44).space(17).
			adjustRight(number_format($total_ly,2),12).' '.
			space(5).
			adjustRight(number_format($total_ytd,2),12).' '."\n\n";


	$details .= space(31).adjustSize("NET INCOME",60).' '.
			adjustRight(number_format($net_income_ly,2),12).' '.
			space(5).
			adjustRight(number_format($net_income_ytd,2),12).' '."\n\n\n\n";
			
	$details1 .= $header.$details;
	if ($p1=='Print Draft')
	{
		doPrint($header.$details);
	}	
}
else
{
	$incZero = 1;
}
if ($date == '') $date=date('m/d/Y');	
?>	
<form name="form1" method="post" action="">
  <div align="center">
    <table width="90%" border="0" cellspacing="1" cellpadding="0" bgcolor="#FFFFFF">
      <tr bgcolor="#EFEFEF"> 
        <td width="17%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Income 
          Statement </strong>::</font></td>
        <td width="23%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          <font color="#CC0000">Profit Center</font></font></td>
        <td width="8%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          Month of</font></td>
        <td width="52%" nowrap>&nbsp;</td>
      </tr>
      <tr> 
        <td width="17%">&nbsp;</td>
        <td width="23%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          <select name="pcenter_id" style="width:200" >
            <option value=''>Select Profit Center --- </option>
            <?
		$q = "select * from pcenter where enable='Y' order by pcenter ";
		$qr = @pg_query($q);
		while ($r= pg_fetch_object($qr))
		{
			if ($pcenter_id == $r->pcenter_id)
			{
				echo "<option value=$r->pcenter_id selected>$r->pcenter</option>";
			}
			else
			{		
				echo "<option value=$r->pcenter_id>$r->pcenter</option>";
			}	
		}
		
		?>
          </select>
          </font></td>
        <td nowrap> 
          <?= lookUpMonth('month',intval($month));?>
          <input name="year" type="text" id="year" value="<?= $year;?>" size="4"> 
        <td width="52%" nowrap> <input name="p1" type="submit" id="p1" value="Go"> 
        </td>
      </tr>
      <tr background="#EFEFEF"> 
        <td colspan="4"><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong><img src="graphics/bluelist.gif" width="16" height="17"></strong></font> 
          <font color="#000033" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Income 
          Satement Preview</strong></font> </td>
      </tr>
      <tr> 
        <td colspan="4"><textarea name="print_area" cols="110" rows="20"  wrap="off" readonly><?= $details1;?></textarea> 
        </td>
      </tr>
    </table>
    <input name="p1" type="submit" id="p1" value="Print Draft" >
    <input name="p12" type="button" id="p12" value="Print"  onClick="printIframe(print_area)" >
  </div>
</form>

<iframe name="printit" id="printit" style="width:0px;height:0px;"></iframe>
