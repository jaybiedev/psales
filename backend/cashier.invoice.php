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
	if ($invoice!= '')
	{
		$aRep['invoice'] = str_pad(trim($invoice),8,'0',str_pad_left);
	}
	else
	{
		$aRep['invoice'] = '';
	}
	$aRep['qty'] = $_REQUEST['qty'];
	$aRep['terminal'] = $_REQUEST['terminal'];
	$aRep['date'] = mdy2ymd($_REQUEST['date']);
	$aRep['barcode'] = $_REQUEST['barcode'];
	$aRep['amount'] = $_REQUEST['amount'];

	if ($aRep['qty'] == '') $aRep['qty'] = 0;
	if ($aRep['amount'] == '') $aRep['amount'] = 0;

	$aRep['sales_chosen_id_header_id'] = '';
	$aRep['process'] = 'go';
}
elseif ($p1 == 'choose' && $id!='')
{
	$aRep['sales_header_id_chosen_id'] = $id;
}
?>
<br>
<form action="" method="post" name="f1" id="f1">
  <table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr> 
      <td colspan="6" background="../graphics/table0_horizontal.PNG"><strong>&nbsp;<img src="../graphics/bluelist.gif" width="16" height="17"> 
        <font color="#FFFFFF" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        Sales Inquiry</font></strong></td>
    </tr>
    <tr> 
      <td width="7%" height="19"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Invoice</font></td>
      <td width="3%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Terminal</font></td>
      <td width="3%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Date</font></td>
      <td width="3%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Qty</font></td>
      <td width="5%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">ItemAmount</font></td>
      <td width="79%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Barcode</font></td>
    </tr>
    <tr> 
      <td> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="invoice" type="text" id="invoice" value="<?= $aRep['invoice'];?>" size="10">
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="terminal" type="text" id="terminal" size="5" value="<?=$aRep['terminal'];?>">
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <input name="date" type="text" id="date" size="10" value="<?=ymd2mdy($aRep['date']);?>">
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="qty" type="text" id="qty" size="5" value="<?=$aRep['qty'];?>">
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="amount" type="text" id="amount" size="10" value="<?=$aRep['amount'];?>">
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="barcode" type="text" id="barcode" size="15" value="<?=$aRep['barcode'];?>">
        <input name="p1" type="submit" id="p1" value="Go">
        </font></td>
    </tr>
    <tr> 
      <td colspan="6"><hr></td>
    </tr>
  </table>
  <?
 if ($aRep['process'] =='go')
  {
  		if (strlen($aRep['date']) < 4)
  		{
  			if ($_REQUEST['date'] == '')
			{
				$mdate = date('Y-m-d');
			}
			else
			{
				$mdate = mdy2ymd($_REQUEST['date']);
			}
		}
		else
		{
			$mdate = $aRep['date'];
		}
		$tables = currTables($mdate);
	
		$sales_header = $tables['sales_header'];
		$sales_detail = $tables['sales_detail'];
		$sales_tender = $tables['sales_tender'];

  	$q = "select  
					distinct ($sales_header.sales_header_id) as sales_header_id,
					$sales_header.invoice, 
					$sales_header.time as invoice_time,
					$sales_header.status,
					$sales_header.ip,
					$sales_header.date as invoice_date,
					$sales_header.admin_id,					
					$sales_header.net_amount,
					$sales_header.terminal,
					admin.name as user
				from 
					$sales_header,
					$sales_detail,
					admin
				where 
					$sales_header.sales_header_id=$sales_detail.sales_header_id and 
					admin.admin_id=$sales_header.admin_id ";
	if ($aRep['invoice'] != '')
	{
		$q .= " and invoice='".$aRep['invoice']."'";
	}
	if ($aRep['terminal'] != '')
	{
		$q .= " and $sales_header.terminal = '".$aRep['terminal']."'";
	}
	if ($aRep['date'] != '' && $aRep['date']!='--')
	{
		$q .= " and $sales_header.date = '".$aRep['date']."'";
	}
	if ($aRep['qty'] != '' && $aRep['qty'] != '0')
	{
		$q .= " and $sales_detail.qty = '".$aRep['qty']."'";
	}
	if ($aRep['amount'] != '' && $aRep['amount']!='0')
	{
		$q .= " and $sales_detail.amount = '".$aRep['amount']."'";
	}
	if ($aRep['barcode'] != '')
	{
		$q .= " and $sales_detail.barcode = '".$aRep['barcode']."'";
	}

	$qr = @pg_query($q) or message1(pg_errormessage().$q);
	if (@pg_num_rows($qr) == 0) message1("Sales Transaction Search NOT Found...");
  ?>
  <table width="90%" border="0" align="center" cellpadding="1" cellspacing="1" bgcolor="#EFEFEF">
    <tr bgColor='#CCCCCC'> 
      <td width="6%" align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></td>
      <td width="10%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Invoice</font></td>
      <td width="6%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        Date</font></td>
      <td width="8%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Time</font></td>
      <td width="9%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Terminal</font></td>
      <td width="12%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Net 
        Amount</font></td>
      <td width="5%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Status</font></td>
      <td width="35%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Audit</font></td>
      <td width="9%">&nbsp;</td>
    </tr>
    <?
	$ctr=0;
	while ($r = @pg_fetch_assoc($qr))
	{
		if ($aRep['sales_header_id_chosen_id'] == ''  &&  $ctr==0)
		{
			$aRep['sales_header_id_chosen_id'] = $r['sales_header_id'];
		}
		$ctr++;
	?>
    <tr bgcolor="#FFFFFF" onMouseOver="bgColor='#FFFFCC'" onMouseOut="bgColor='#FFFFFF'"> 
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $ctr;?>
        .</font></td>
      <td align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <a href="?p=cashier.invoice&p1=choose&id=<?= $r['sales_header_id'];?>"  onmouseover="showToolTip(event,'Click To Select and Show Invoice...');return false" onmouseout="hideToolTip()"> 
        <?= $r['invoice'];?>
        </a> </font></td>
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
      <td align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $r['status'];?>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <?= $r['user'];?>
        </font></td>
      <td align="center"><font size="2"><a 
href="?p=report.receipt&p1=Go&invoice=<?=$r['invoice'];?>&terminal=<?=$r['terminal'];?>">Re-Print</a></font></td>
    </tr>
    <?
	}
	?>
  </table>

	<?
if ($aRep['sales_header_id_chosen_id'] != '')
{
  	$q = "select  *
		from
			$sales_detail,
			stock
		where 
			stock.stock_id=$sales_detail.stock_id and
			$sales_detail.sales_header_id='".$aRep['sales_header_id_chosen_id']."'";

	$qr = @pg_query($q) or message1(pg_errormessage().$q);
   ?><br>
  <table width="90%" border="0" align="center" cellpadding="1" cellspacing="1" bgcolor="#EFEFEF">
    <tr bgcolor="#FFCCCC" background="../graphics/table_horizontal.PNG"> 
      <td colspan="7"><strong><font color="#CCCCCC" size="2" face="Verdana, Arial, Helvetica, sans-serif">Transaction Details</font></strong></td>
    </tr>
    <tr bgColor='#CCCCCC'> 
      <td width="6%" align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></td>
      <td width="9%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Qty</font></td>
      <td width="15%" align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Barcode</font></td>
      <td width="33%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        Description</font></td>
      <td width="11%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Price</font></td>
      <td colspan="2"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Amount</font></td>
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
        <?= $r->qty;?>&nbsp;
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $r->barcode;?> </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $r->stock;?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= number_format($r->price,2);?>
        </font> </td>
      <td width="10%" align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= number_format($r->amount,2);?>
        </font></td>
      <td width="16%" align="right">&nbsp;</td>
    </tr>
    <?
	}
	?>
  </table>
  <?
    }	
}
?>	
</form>

