<?
if (!session_is_registered("aRep"))
{
	session_register("aRep");
	$aRep = null;
	$aRep = array();
}

if ($p1 != '')
{
	$aRep['date'] = $_REQUEST['date'];
}
if ($aRep['date'] == '') $aRep['date']=date('m/d/Y');

if ($p1 == 'Go')
{
	$aRep['date'] = $_REQUEST['date'];
	$aRep['mrefresh'] = $_REQUEST['mrefresh'];
	$aRep['terminal'] = $_REQUEST['terminal'];
	$aRep['xSearch'] = $_REQUEST['xSearch'];
	$aRep['searchby'] = $_REQUEST['searchby'];
}
?>
<meta http-equiv="refresh" <?= ($aRep['mrefresh'] > '0' ? ' content= '.$aRep['mrefresh'] : '');?>>
<form action="" method="post" name="f1" id="f1">
  <table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr> 
      <td background="../graphics/table0_horizontal.PNG"><strong>&nbsp;<img src="../graphics/bluelist.gif" width="16" height="17"> 
        <font color="#FFFFFF" size="2" face="Verdana, Arial, Helvetica, sans-serif">Cashier 
        Monitor</font></strong></td>
    </tr>
    <tr> 
      <td> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> Audit 
        Date 
        <input name="date" type="text" id="date" value="<?= $aRep['date'];?>" size="8" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')">
        <img src="../graphics/dwn-arrow-grn.gif" onclick="popUpCalendar(this, f1.date, 'mm/dd/yyyy')"> 
        Terminal 
        <input name="terminal" type="text" id="terminal" value="<?= $aRep['terminal'];?>" size="5">
		<?= lookUpAssoc('mrefresh',array('Auto Refresh'=>'10','20 Seconds'=>'20','30 Seconds'=>'30','No Refresh'=>'0'),$aRep['mrefresh']);?>
        <input name="p1" type="submit" id="p1" value="Go">
        Search For 
        <input name="xSearch" type="text" id="xSearch" value="<?= $aRep['xSearch'];?>" size="12">
        <?= lookUpAssoc('searchby',array('Invoice'=>'invoice', 'Sales Rec.Id'=>'sales_header_id'),$aRep['searchby']);?>
        <input name="p1" type="submit" id="p1" value="Search">
        </font></td>
    </tr>
    <tr>
      <td><hr></td>
    </tr>
  </table>
  <?
  	$q = "select 
					sales_header_id,
					sales_header.invoice, 
					sales_header.time as invoice_time,
					sales_header.status,
					sales_header.ip,
					sales_header.date as invoice_date,
					sales_header.admin_id,
					sales_header.gross_amount,
					sales_header.net_amount,
					sales_header.terminal,
					admin.name as user
				from 
					sales_header ,
					admin
				where 
					admin.admin_id=sales_header.admin_id and 
					sales_header.date='".mdy2ymd($aRep['date'])."'";
					
	if ($aRep['terminal'] != '')
	{
		$q .= " and terminal = '".$aRep['terminal']."'";
	}
	if ($aRep['xSearch'] != '')
	{
			$q .= "	 and ".$aRep['searchby']." = '".$aRep['xSearch']."'";
	}
	$q .= " order  by sales_header_id desc ";
	
	$qr = @pg_query($q) or message(pg_errormessage());
  ?>
  <table width="90%" border="0" align="center" cellpadding="1" cellspacing="1" bgcolor="#EFEFEF">
    <tr> 
      <td width="6%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
      <td width="10%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Invoice</font></strong></td>
      <td width="6%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        Date</font></strong></td>
      <td width="8%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Time</font></strong></td>
      <td width="9%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Amount</font></strong></td>
      <td width="6%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Term</font></strong></td>
      <td width="27%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Audit</font></strong></td>
      <td width="28%">&nbsp;</td>
    </tr>
    <?
	$ctr=0;
	while ($r = @pg_fetch_object($qr))
	{
		$ctr++;
	?>
    <tr bgcolor="#FFFFFF" onMouseOver="bgColor='#FFFFCC'" onMouseOut="bgColor='#FFFFFF'"> 
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $ctr;?>
        .</font></td>
      <td align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= str_pad($r->invoice,9,'0',str_pad_left);?>
        </font></td>
      <td align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= ymd2mdy($r->invoice_date);?>
        </font></td>
      <td align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $r->invoice_time;?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= number_format($r->net_amount,2);?>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $r->terminal;?>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $r->user;?>
        </font></td>
      <td><font size="2">Re-Print</font></td>
    </tr>
    <?
	}
	?>
  </table>
	<?
  	$q = "select  
					suspend_header_id,
					suspend_header.status,
					suspend_header.ip,
					suspend_header.date as suspend_date,
					suspend_header.time as suspend_time,
					row_id,
					audit.date,
					audit.remark,
					audit.admin_id,
					audit.dbsql
					
				from 
					audit,
					suspend_header
				where 
					suspend_header.suspend_header_id=audit.row_id and 
					module='cashier_suspend' and 
					audit.date='".mdy2ymd($date)."'";
					
	$qr = @pg_query($q) or message(pg_errormessage());
   ?><br>
  <table width="90%" border="0" align="center" cellpadding="1" cellspacing="1" bgcolor="#EFEFEF">
    <tr bgcolor="#FFCCCC"> 
      <td colspan="5"><strong><font color="#993300">Suspended Sale</font></strong></td>
    </tr>
    <tr> 
      <td width="6%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
      <td width="10%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Docket</font></strong></td>
      <td width="6%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        Date</font></strong></td>
      <td width="8%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Time</font></strong></td>
      <td width="70%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Audit</font></strong></td>
    </tr>
    <?
	$ctr=0;
	while ($r = @pg_fetch_object($qr))
	{
		$ctr++;
	?>
    <tr bgcolor="#FFFFFF" onMouseOver="bgColor='#FFFFCC'" onMouseOut="bgColor='#FFFFFF'"> 
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $ctr;?>
        .</font></td>
      <td align="left"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= str_pad($r->suspend_header_id,9,'0',str_pad_left);?>
        </font></td>
      <td align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= ymd2mdy($r->suspend_date);?>
        </font></td>
      <td align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $r->suspend_time;?></font>
      </td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $r->user;?>
        </font></td>
    </tr>
    <?
	}
	?>
  </table>
	
</form>

