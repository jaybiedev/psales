<?
	if ($date == '')
	{
		$date = ymd2mdy(yesterday());
	}
	if ($p1 == 'Go' || $p1=='Print' || $p1 == 'Print Draft')
	{
		$mdate = mdy2ymd($_REQUEST['date']);
		$terminal = $_REQUEST['terminal'];

		$tables = currTables($mdate);
		$sales_header = $tables['sales_header'];
		$sales_detail = $tables['sales_detail'];
		$sales_tender = $tables['sales_tender'];

		$term = terminal($terminal);		
		$aSC = null;
		$aSC = array();
		
		$aTT = null;
		$aTT = array();
		
		$q = "select 
						net_amount, 
						$sales_header.admin_id						
				from 
						$sales_header
				where 
						$sales_header.status!='V' and 
						$sales_header.date='$mdate'";
 
		if ($terminal != '')
		{
			$q .= " and terminal ='$terminal'";
		}

		$q .= "	order by $sales_header.sales_header_id ";
		$qr = @pg_query($q) or message(pg_errormessage());
		$details = $SYSCONF['BUSINESS_NAME']."\n";
		$details .= $SYSCONF['BUSINESS_ADDR']."\n";
		$details .= 'Terminal : '.$term['TERMINAL']."\n";
		$details .= 'Serial   : '.$term['SERIAL']."\n";
		$details .= 'Transaction : '.$date."\n";
		$details .= 'Printed     : '.date('m/d/Y g:ia')."\n\n";
		$details .= "SALES BY CASHIER SHIFT\n";
		$details .= str_repeat('-',40)."\n";
		$total_amount = $net_amount  = 0;
		$admin_id = '';
		while ($r = @pg_fetch_object($qr))
		{
			if ($admin_id != $r->admin_id)
			{
					if ($admin_id != '')
					{
		  				$details .= adjustSize(lookUpTableReturnValue('x','admin','admin_id','name', $admin_id), 25).' '.
		  				adjustRight(number_format($net_amount,2),12)."\n";
					}
					$net_amount = 0;
					$admin_id = $r->admin_id;
			}
			$net_amount += $r->net_amount;
			$total_amount += $r->net_amount;
						
		}	
		$details .= adjustSize(lookUpTableReturnValue('x','admin','admin_id','name', $admin_id), 25).' '.
		adjustRight(number_format($net_amount,2),12)."\n";
		$details .= str_repeat('-',40)."\n";
	  	$details .=	space(26).adjustRight(number_format($total_amount,2),12)."\n";
		$details .= str_repeat('-',40)."\n";
		$details .= "\n\n";

		$details .= "\n\n\n\n";
		$details1 .= $details;
		
		if ($p1 == 'Print Draft')
		{
			nPrinter($details, $SYSCONF['RECEIPT_PRINTER_TYPE'], $SYSCONF['RECEIPT_PRINTER_DEST']);
		}	
	}
?>
<form name="form1" method="post" action="">
  <table width="80%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr> 
      <td background="../graphics/table0_horizontal.PNG" height="2%"> <strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        &nbsp;<img src="../graphics/bluelist.gif" width="16" height="17"><font color="#FFFFFF"> 
        Cashier Sift Reading</font></font></strong></td>
    </tr>
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Transaction 
        Date </font> <font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="date" type="text" id="date" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $date;?>" size="8">
        <img src="../graphics/dwn-arrow-grn.gif" width="12" height="12" onClick="popUpCalendar(this, form1.date, 'mm/dd/yyyy')"> 
        Terminal</font> <input type="text" size="5" name="terminal" value="<?=$terminal;?>">
        <input type="submit" name="p1" value="Go"> <input type="submit" name="p1" value="Print Draft"> 
 		  <input name="p122" type="button" id="p122" value="Print"  onClick="printIframe(print_area)" >
        <hr color="#CC0000"> </td>
    </tr>
  </table>
  <table width="80%" height="50%" border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#EFEFEF">
    <tr bgColor='#CCCCCC'>
      <td height="28"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Report Preview</strong></font></td>
    </tr>
    <tr>
      <td height="98%" valign="top" bgcolor="#FFFFFF"> <textarea name="print_area" cols="90" rows="20" readOnly><?= $details1;?></textarea></td>
    </tr>

  </table>
</form>

<iframe name="printit" id="printit" style="width:0px;height:0px;"></iframe>

