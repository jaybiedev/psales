<?
if (!session_is_registered('aRep'))
{
	session_register('aRep');
	$aRep = null;
	$aRep = array();
}
if ($aRep['date'] == '') $aRep['date']=date('m/d/Y');
if (in_array($p1, array('Print','Print Draft','Go')))
{
	$aRep = null;
	$aRep = array();
	$aRep['terminal'] = $_REQUEST['terminal'];
	$aRep['date'] = mdy2ymd($_REQUEST['date']);
	$aRep['barcode'] = $_REQUEST['barcode'];
}
?>
<br>
<form action="" method="post" name="f1" id="f1">
  <table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr> 
      <td colspan="3" background="../graphics/table0_horizontal.PNG"><strong>&nbsp;<img src="../graphics/bluelist.gif" width="16" height="17"> 
        <font color="#FFFFFF" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        Cashier Item Sales Inquiry</font></strong></td>
    </tr>
    <tr> 
      <td width="8%" nowrap> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> Date<br>
        <input name="date" type="text" id="date" value="<?= ymd2mdy($aRep['date']);?>" size="8" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')">
        <img src="../graphics/dwn-arrow-grn.gif" onclick="popUpCalendar(this, f1.date, 'mm/dd/yyyy')"> 
        </font></td>
      <td width="6%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">*Terminal<br>
        <input name="terminal" type="text" id="terminal3" size="5" value="<?=$aRep['terminal'];?>">
        </font></td>
      <td width="86%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        *Barcode<br>
        <input name="barcode" type="text" id="barcode" size="15" value="<?=$aRep['barcode'];?>">
        <input name="p1" type="submit" id="p13" value="Go">
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<em>&nbsp;*optional filter information</em> 
        </font></td>
    </tr>
    <tr> 
      <td colspan="3"><hr></td>
    </tr>
  </table>
  <?
		$tables = currTables($aRep['date']);
		$sales_header = $tables['sales_header'];
		$sales_detail = $tables['sales_detail'];
		$sales_tender = $tables['sales_tender'];

  	$q = "select 
			sum(qty) as qty,
			sum(amount) as amount,
			stock.stock,
			stock.barcode,
			stock.price1
		from
			$sales_header as sh,
			$sales_detail as sd,
			stock
		where 
			sh.sales_header_id = sd.sales_header_id and 
			stock.stock_id=sd.stock_id and
			sh.status!='V' and 
			sh.date = '".$aRep['date']."'";

	if ($aRep['terminal'] != '')
	{
		$q .= " and terminal = '".$aRep['terminal']."'";
	}
	if ($aRep['barcode'] != '')
	{
		$q .= " and sd.barcode = '".$aRep['barcode']."'";
	}
	
	$q .= " group by sd.stock_id , stock.stock, stock.barcode,stock.price1
			order by
					stock.barcode ";
	
	$qr = @pg_query($q) or message(pg_errormessage());
   ?>

  <table width="90%" border="0" align="center" cellpadding="1" cellspacing="1" bgcolor="#EFEFEF">
    <tr bgcolor="#FFCCCC"> 
      <td colspan="7"><strong><font color="#993300" size="2">Item Transaction Details</font></strong></td>
    </tr>
    <tr> 
      <td width="7%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
      <td width="12%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Barcode</font></strong></td>
      <td width="10%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Qty</font></strong></td>
      <td width="35%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
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
      <td align="right" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $ctr;?>
        .</font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $r->barcode;?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $r->qty;?>
        &nbsp; </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $r->stock;?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= number_format($r->price1,2);?>
        </font> </td>
      <td width="12%"  align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= number_format($r->amount,2);?>
        </font></td>
      <td width="12%"  align="right">&nbsp;</td>
    </tr>
    <?
	}
	?>
  </table>
  </form>

