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
if ($from_date == '')
{
	$from_date = date('m/01/Y');
	$to_date = date('m/d/Y');
}	
if ($p1=='Go' || $p1=='Print Draft')
{
	if ($from_date == '')
	{
		$from_date = date('m/d/Y');
	}	
	$mdate = mdy2ymd($from_date);
	
	$q = "select 
			gchart.acode,
			gchart.scode,
			gchart.gchart,
			gltran_header.journal_id, 
			gltran_detail.gchart_id,
			sum(gltran_detail.debit) as debit,
			sum(gltran_detail.credit) as credit
				
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
			gltran_header.date>='".mdy2ymd($from_date)."' and
			gltran_header.date<='".mdy2ymd($to_date)."'";
	if ($journal_id != '')
	{
		$q .= " and journal_id='$journal_id'";
	}
	if ($pcenter_id != '')
	{
		$q .= " and gltran_header.pcenter_id='$pcenter_id'";
	}
	$q .= "group by 
				gltran_header.journal_id, 
				gltran_detail.gchart_id
			order by 
				journal_id,
				gchart.acode,
				gchart.scode";
				
	$qr = @pg_query($q) or message(pg_error());

	$header = "\n\n";
	$header .= center($SYSCONF['BUSINESS_NAME'],130)."\n";
	$header .= center('JOURNAL SUMMARY - '.($pcenter_id == '' ? 'ALL' : lookUpTableReturnValue('p','pcenter','pcenter_id','pcenter',$pcenter_id)),130)."\n";
	$header .= center('As of Date '.$from_date.' to '.$to_date,130)."\n\n";
	$header .= "----- ---------- ------------ --------------------------------------------------- --------------- ---------------\n";
	$header .= "       Account Code                 Account Description                                Debit         Credit\n";
	$header .= "----- ---------- ------------ --------------------------------------------------- --------------- ---------------\n";

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
		if ($mjournal_id != $r->journal_id)
		{
			if ($mjournal_id != '')
			{
				$details .= space(80)."  --------------- ---------------\n";
				$details .= space(33). adjustSize($ctr.' Item/s',18).adjustSize('Journal Total:',14).space(16).
						adjustRight(number_format($sub_debit,2),15).' '.
						adjustRight(number_format($sub_credit,2),15).' '.	"\n";
				$sub_credit = $sub_debit = $ctr = 0;
			}						
			$details .= "Journal : ".adjustSize(lookUpTableReturnValue('x','journal','journal_id','journal',$r->journal_id),25)."\n";
			$mjournal_id=$r->journal_id;
			$lc = $lc+2;
		}
		$ctr++;
		$details .= adjustRight($ctr,4).'. '.
					adjustSize($r->acode,10).' '.
					adjustSize($r->scode,10).'   '.
					adjustSize(substr($r->gchart,0,50),50).' '.
					adjustRight(number_format2($r->debit,2),15).' '.
					adjustRight(number_format2($r->credit,2),15)."\n";
		$total_debit += $r->debit;
		$total_credit += $r->credit;
		$sub_debit += $r->debit;
		$sub_credit += $r->credit;
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
	if ($mjournal_id != $r->journal_id)
	{
		$details .= space(80)."  --------------- ---------------\n";
		$details .= space(33). adjustSize($ctr.' Item/s',18).adjustSize('Journal Total:',14).space(16).
				adjustRight(number_format($sub_debit,2),15).' '.
				adjustRight(number_format($sub_credit,2),15).' '.	"\n";
		$lc = $lc+2;
	}
	$details .= "----- ---------- ------------ --------------------------------------------------- --------------- ---------------\n";
	$details .= space(33). adjustSize($ctr.' Item/s',18).adjustSize('Grand Total :',14).space(16).
				adjustRight(number_format($total_debit,2),15).' '.
				adjustRight(number_format($total_credit,2),15).' '.	"\n";
	$details .= "----- ---------- ------------ --------------------------------------------------- --------------- ---------------\n";
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
      <tr bgcolor="#EFEFEF" background="graphics/table0_horizontal.PNG"> 
        <td colspan="4" nowrap> <font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">::<strong>Journal 
          Summary</strong></font></td>
      </tr>
      <tr bgcolor="#EFEFEF"> 
        <td width="23%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"><font color="#CC0000">Journal</font> 
          </font></td>
        <td width="10%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">From</font></td>
        <td width="7%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          To</font></td>
        <td width="60%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">Profit 
          Center</font></td>
      </tr>
      <tr> 
        <td><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          <select name="journal_id" style="width:200" >
            <option value=''>All Journals --- </option>
            <?
		$q = "select * from journal where enable='Y' order by journal ";
		$qr = @pg_query($q);
		while ($r= pg_fetch_object($qr))
		{
			if ($journal_id == $r->journal_id)
			{
				echo "<option value=$r->journal_id selected>$r->journal</option>";
			}
			else
			{		
				echo "<option value=$r->journal_id>$r->journal</option>";
			}	
		}
		
		?>
          </select>
          </font></td>
        <td width="10%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
          </font><font color="#000000"><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          <input name="from_date" type="text" id="from_date" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $from_date;?>" size="8">
          </font><font color="#000000"><font color="#000000"><font color="#000000"><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"><img src="graphics/dwn-arrow-grn.gif" width="12" height="12" onClick="popUpCalendar(this, form1.from_date, 'mm/dd/yyyy')"></font></font></font></font><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          </font></font><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
          </font></td>
        <td width="7%" nowrap><font color="#000000"><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          <input name="to_date" type="text" id="to_date" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $to_date;?>" size="8">
          </font><font color="#000000"><font color="#000000"><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"><img src="graphics/dwn-arrow-grn.gif" width="12" height="12" onClick="popUpCalendar(this, form1.to_date, 'mm/dd/yyyy')"></font></font></font></font></td>
        <td width="60%" nowrap> <select name="pcenter_id"  id="pcenter_id" style="border: #CCCCCC 1px solid; width:150px">
            <option value=''>Profit Center --</option>
            <?

	  	$q = "select * from pcenter where enable='Y' order by pcenter";
		$qr = @pg_query($q);
		while ($r = @pg_fetch_object($qr))
		{
			if ($r->pcenter_id == $pcenter_id)
			{
				echo "<option value=$r->pcenter_id selected>".substr($r->pcenter_code,0,6)." $r->pcenter</option>";
			}
			else
			{
				echo "<option value=$r->pcenter_id>".substr($r->pcenter_code,0,6)." $r->pcenter</option>";
			}
		}
	  ?>
          </select> <input name="p1" type="submit" id="p1" value="Go"> </td>
      </tr>
      <tr> 
        <td colspan="4"><hr size="1"></td>
      </tr>
      <tr bgcolor="#B5CFD5"> 
        <td colspan="4"><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong><img src="../graphics/bluelist.gif" width="16" height="17"> 
          Journal Summary Preview</strong></font> </td>
      </tr>
      <tr>
        <td colspan="4"><textarea name="print_area" cols="110" rows="20"  wrap="off" readonly><?= $details1;?></textarea></td>
      </tr>
    </table>
    <input name="p1" type="submit" id="p1" value="Print Draft" >
    <input name="p12" type="button" id="p12" value="Print"  onClick="printIframe(print_area)" >
  </div>
</form>

<iframe name="printit" id="printit" style="width:0px;height:0px;"></iframe>
