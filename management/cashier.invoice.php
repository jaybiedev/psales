<?
if (!session_is_registered('aRep'))
{
	session_register('aRep');
	$aRep = null;
	$aRep = array();
}
if ($p1 == 'Go')
{
	$aRep = null;
	$aRep = array();
	$invoice = $_REQUEST['invoice'];
	$aRep['invoice'] = str_pad(trim($invoice),8,'0',str_pad_left);
}
elseif ($p1 == 'choose' && $id!='')
{
	$aRep['sales_header_id'] = $id;
}
?>
<br>
<form action="" method="post" name="f1" id="f1">
  <table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr> 
      <td background="../graphics/table0_horizontal.PNG"><strong>&nbsp;<img src="../graphics/bluelist.gif" width="16" height="17"> 
        <font color="#FFFFFF" size="2" face="Verdana, Arial, Helvetica, sans-serif">
        Invoice Inquiry</font></strong></td>
    </tr>
    <tr> 
      <td> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
	Invoice 
        <input name="invoice" type="text" id="invoice" value="<?= $aRep['invoice'];?>" size="10">
        <input name="terminal" type="text" id="terminal" size="5" value="<?=$terminal;?>">
        <input name="p1" type="submit" id="p1" value="Go">
        </font></td>
    </tr>
    <tr>
      <td><hr></td>
    </tr>
  </table>
  <?
  if ($aRep['invoice']!= '')
  {
  	$q = "select  
					sales_header_id,
					sales_header.invoice, 
					sales_header.time as invoice_time,
					sales_header.status,
					sales_header.ip,
					sales_header.date as invoice_date,
					sales_header.admin_id,					
					sales_header.net_amount,
					sales_header.terminal,
					admin.name as user
				from 
					sales_header,
					admin
				where 
					admin.admin_id=sales_header.admin_id ";
	if ($aRep['invoice'] != '')
	{
		$q .= " and invoice='".$aRep['invoice']."'";
	}

	$qr = @pg_query($q) or message(pg_errormessage());

  ?>
  <table width="90%" border="0" align="center" cellpadding="1" cellspacing="1" bgcolor="#EFEFEF">
    <tr> 
      <td width="6%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
      <td width="10%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Invoice</font></strong></td>
      <td width="6%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        Date</font></strong></td>
      <td width="8%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Time</font></strong></td>
      <td width="9%"><strong></strong><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Terminal</font></strong></td>
      <td width="12%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Net 
        Amount</font>t</strong></td>
      <td width="33%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Audit</font></strong></td>
      <td width="16%">&nbsp;</td>
    </tr>
	<?
	$ctr=0;
	while ($r = @pg_fetch_assoc($qr))
	{
		if ($aRep['sales_header_id_chosen_id'] == '' && $ctr==0)
		{
			$aRep['sales_header_id_choosen_id'] = $r['sales_header_id'];
		}
		$ctr++;
	?>
    <tr bgcolor="#FFFFFF" onMouseOver="bgColor='#FFFFCC'" onMouseOut="bgColor='#FFFFFF'"> 
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $ctr;?>
        .</font></td>
      <td align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <a href="?p=cashier.invoice&p1=choose&id=<?= $r['sales_header_id'];?>"><?= $r['invoice'];?></a>
        </font></td>
      <td align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= ymd2mdy($r['invoice_date']);?>
        </font></td>
      <td align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $r['invoice_time'];?>
        </font></td>
      <td align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <?= $r['terminal'];?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= number_format($r['net_amount'],2);?>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $r['user'];?>
        </font></td>
      <td align="center"><font size="2">Re-Print</font></td>
    </tr>
	<?
	}
	?>
  </table>

	<?

  	$q = "select  *
		from
			sales_detail,
			stock
		where 
			stock.stock_id=sales_detail.stock_id and
			sales_detail.sales_header_id='".$aRep['sales_header_id_choosen_id']."'";

	$qr = @pg_query($q) or message(pg_errormessage());
   ?><br>
  <table width="90%" border="0" align="center" cellpadding="1" cellspacing="1" bgcolor="#EFEFEF">
    <tr bgcolor="#FFCCCC"> 
      <td colspan="7"><strong><font color="#993300">Transaction Details</font></strong></td>
    </tr>
    <tr> 
      <td width="4%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
      <td width="7%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Barcode</font></strong></td>
      <td width="10%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Qty</font></strong></td>
      <td width="25%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        Description</font></strong></td>
      <td width="12%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Price</font></strong></td>
      <td colspan="2"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Amount</font></strong></td>
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
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $r->qty;?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $r->barcode;?>
        &nbsp; </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $r->stock;?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= number_format($r->price,2);?>
        </font> </td>
      <td width="13%" align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= number_format($r->amount,2);?>
        </font></td>
      <td width="29%" align="right">&nbsp;</td>
    </tr>
    <?
	}
	?>
  </table>
  <?
  }
?>	
</form>

