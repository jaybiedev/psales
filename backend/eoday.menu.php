<?
	if ($p1=="") 
	{
		
		$yesterday = yesterday();
		$q = "select * from zread order by date desc offset 0 limit 1";
		$qr = @pg_query($q) or message(pg_errormessage());
		$r = @pg_fetch_object($qr);
		$lastday = $r->date;
		
		if ($lastday == $yesterday)
		{
			$date = ymd2mdy($yesterday);
		}
		else
		{
			$q = "select date '$lastday' + integer '1' as date ";
			$qr = @pg_query($q) or message(pg_errormessage());
			$r = @pg_fetch_object($qr);
			$date = ymd2mdy($r->date);
		}
		$from_date = $date;
		$to_date = $date;		
	}
	
?>
<br>
<form name="fd" id="fd" method="post" action="">
  <table width="80%" border="0" align="center" cellpadding="1" cellspacing="1">
    <tr bgcolor="#EFEFEF" background="../graphics/table0_horizontal.PNG"> 
      <td width="22%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Posting 
        End of Day</font></strong></td>
      <td width="10%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">From 
        Date</font></strong></td>
      <td width="68%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">To 
        Date </font></strong></td>
    </tr>
    <tr> 
      <td nowrap>&nbsp;</td>
      <td nowrap><font  color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
        <input name="from_date" type="text" id="from_date"  onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $from_date;?>" size="9">
        </strong></font><img src="../graphics/dwn-arrow-grn.gif" onclick="popUpCalendar(this, fd.from_date, 'mm/dd/yyyy')"> 
      </td>
      <td nowrap><font  color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>
        <input name="to_date" type="text" id="to_date"  onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $to_date;?>" size="9">
        </strong></font> <img src="../graphics/dwn-arrow-grn.gif" onclick="popUpCalendar(this, fd.to_date, 'mm/dd/yyyy')">
<input name="p1" type="button" id="p1" value="Go" onCLick="wait('Please wait. Processing data...');xajax_process_eoday(xajax.getFormValues('fd'));"> 
        <input name="p12" type="button" id="p122" value="Close" onClick="window.location='?p'"> 
<input name="p1" type="button" id="p1" value="Post Cost to SD" onCLick="wait('Please wait. Processing data...');xajax_postcost(xajax.getFormValues('fd'));"> 
      </td>
    </tr>
  </table>
  <table width="80%" border="0" align="center" cellpadding="2" cellspacing="1" bgcolor="#EFEFEF">
    <tr bgcolor="#003366"> 
      <td width="10%"><strong><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
      <td width="18%" align="center"><strong><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif">Date</font></strong></td>
      <td width="14%" align="center"><strong><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif">Amount</font></strong></td>
      <td width="58%">&nbsp;</td>
    </tr>
    <?
	$q = "select * from eoday order by date desc offset 0 limit 30";
	$qr = @pg_query($q) or message(pg_errormessage());
	$c=0;
	while ($r = @pg_fetch_object($qr))
	{
		$c++;
	?>
    <tr bgcolor="#FFFFFF"> 
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $c;?>
        .</font></td>
      <td align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= ymd2mdy($r->date);?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= number_format($r->total_amount,2);?>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;</font></td>
    </tr>
    <?
	}
	?>
  </table>
 </form>
