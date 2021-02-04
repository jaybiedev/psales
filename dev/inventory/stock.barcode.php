<!-- <div id="Layer.StockBarcode" style="visibility: hidden; position:absolute;">-->
  <table width="100%" border="0" cellpadding="1" cellspacing="1" bgcolor="#FFFFFF">
    <tr bgcolor="#CCCCCC"> 
      <td colspan="3"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Stock 
        Alternate Barcodes</strong></font></td>
    </tr>
    <tr bgcolor="#EFEFEF"> 
      <td width="12%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Current 
        Barcode</font></td>
      <td width="25%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Item 
        Description</font></td>
      <td width="63%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;</font></td>
    </tr>
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input type="text" name="textfield" readOnly value="<?=$astock['barcode'];?>">
        </font></td>
      <td colspan="2"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="textfield2" type="text" value="<?=$astock['stock'];?>" size="45" readOnly>
        </font><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;</font></td>
    </tr>
    <tr bgcolor="#DADADA"> 
      <td colspan="3"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Other 
        Barcodes</strong></font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
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
      <td align='right'><?= $ctr;?>.</td>
      <td><?= $r->barcode;?></td>
      <td>&nbsp;</td>
    </tr>
	<?
	}
	?>
  </table>
