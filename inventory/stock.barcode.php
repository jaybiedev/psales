<!-- <div id="Layer.StockBarcode" style="visibility: hidden; position:absolute;">-->
  
<table width="100%" border="0" cellpadding="1" cellspacing="1" bgcolor="#FFFFFF">
  <tr bgcolor="#DADADA"> 
    <td colspan="3"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Other/Alternate 
      Barcodes</strong></font></td>
  </tr>
  <tr bgcolor="#FFFFFF"> 
    <td width="12%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <input name="new_barcode" type="text" id="new_barcode">
      </font></td>
    <td colspan="2"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;</font><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
      <input name="p1" type="submit" id="p1" value="Add Barcode">
      </font></td>
  </tr>
  <?
		$q = "select * from barcode where stock_id='".$astock['stock_id']."'";
		$qr = @pg_query($q) or message(pg_errormessage());
		
		$ctr=0;
		if ($r = @pg_fetch_object($qr))
		{
			$ctr++;
	?>
  <tr bgcolor="#FFFFFF"> 
    <td align='right'>
      <?= $ctr;?>
      .</td>
    <td width="25%">
      <?= $r->barcode;?>
    </td>
    <td width="63%">&nbsp;</td>
  </tr>
  <?
	}
	?>
</table>
