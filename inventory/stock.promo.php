<!-- <div id="Layer.StockPromo" style="visibility: hidden; position:absolute;">-->
  
<table width="100%" border="0" cellpadding="1" cellspacing="1" bgcolor="#FFFFFF">
  <tr bgcolor="#EFEFEF"> 
    <td width="12%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">From</font></strong></td>
    <td width="25%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">To</font></strong></td>
    <td width="31%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Price</font></strong></td>
    <td width="16%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">%Discount</font></strong></td>
    <td width="16%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Promo</font></strong></td>
  </tr>
  <?
	if ($astock['stock_id'] != '')
	{
		$q = "select * from promo_header, promo_detail
			where
				promo_header.promo_header_id = promo_detail.promo_header_id and
				promo_detail.stock_id = '".$astock['stock_id']."' and
				date_to>='".date('Y-m-d')."'";
		$qr = @pg_query($q) or message(pg_errormessage());
	}
	$pc = 0;
	while ($rp = @pg_fetch_object($qr))
	{
		if ($r->all_items == 'Y')
		{
			$href = "?p=promo.supplier&p1=Edit&id=$rp->promo_header_id";
		}
		else
		{
			$href = "?p=promo.generate&p1=Load&id=$rp->promo_header_id";
		}
		$pc++;			
	?>
  <tr> 
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= ymd2mdy($rp->date_from);?>
      </font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href="<?=$href;?>"> 
      <?= ymd2mdy($rp->date_to);?>
      </a> </font></td>
    <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= number_format($rp->price1,2);?>
      </font></td>
    <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= $rp->sdisc;?>
      </font></td>
    <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= number_format($rp->promo_price,2);?>
      </font></td>
  </tr>
  <?
	}
	?>
</table>
