<?
if (!session_is_registered('aRep'))
{
	session_register('aRep');
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
	$aRep = null;
	$aRep = array();
	$aRep['terminal'] = $_REQUEST['terminal'];
	$aRep['date'] = $_REQUEST['date'];
}
?>
<br>
<form action="" method="post" name="f1" id="f1">
  <table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr> 
      <td background="../graphics/table0_horizontal.PNG"><strong>&nbsp;<img src="../graphics/bluelist.gif" width="16" height="17"> 
        <font color="#FFFFFF" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        Cashier Item Sales Inquiry</font></strong></td>
    </tr>
    <tr> 
      <td> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> Date 
        <input name="date" type="text" id="date" value="<?= $aRep['date'];?>" size="8" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')">
        <img src="../graphics/dwn-arrow-grn.gif" onclick="popUpCalendar(this, f1.date, 'mm/dd/yyyy')"> 
        Terminal 
        <input name="terminal" type="text" id="terminal" size="5" value="<?=$terminal;?>">
        <input name="p1" type="submit" id="p1" value="Go">
        </font></td>
    </tr>
    <tr>
      <td><hr></td>
    </tr>
  </table>
  <?

  	$q = "select 
			sum(qty) as qty,
			sum(amount) as amount,
			stock.stock,
			stock.barcode,
			stock.price1
		from
			sales_header,
			sales_detail,
			stock
		where 
			sales_header.sales_header_id=sales_detail.sales_header_id and 
			stock.stock_id=sales_detail.stock_id and
			date = '".mdy2ymd($aRep['date'])."'";

	if ($aRep['terminal'] != '')
	{
		$q .= " and terminal = '".$aRep['terminal']."'";
	}
	
	$q .= " group by sales_detail.stock_id , stock.stock, stock.barcode,stock.price1
			order by
					stock.barcode ";
	$qr = @pg_query($q) or message(pg_errormessage());
   ?>

  <table width="90%" border="0" align="center" cellpadding="1" cellspacing="1" bgcolor="#EFEFEF">
    <tr bgcolor="#FFCCCC"> 
      <td colspan="7"><strong><font color="#993300">Item Transaction Details</font></strong></td>
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
      <td width="13%" align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= number_format($r->amount,2);?>
        </font></td>
      <td width="29%" align="right">&nbsp;</td>
    </tr>
    <?
	}
	?>
  </table>
  </form>

