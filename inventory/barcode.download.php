<?
if (!session_is_registered('aBardown'))
{
	session_register('aBardown');
	$aBardown = null;
	$aBardown = array();
}
$aip = explode('.',$_SERVER['REMOTE_ADDR']);
$reportfile= '/cache/BAR_'.$aip[3].'.txt';


if ($p1 == 'Add New' or $p1 == 'New')
{
	$aBardown = null;
	$aBardown = array();
}
elseif ($p1 == 'Go')
{
	$barcode = $_REQUEST['barcode'];
	$qty = $_REQUEST['qty'];
	$q = "select * from stock where barcode='$barcode'";
	$qr = @pg_query($q) or message(pg_errormessage());
	if (@pg_num_rows($qr) == 0)
	{
		message1('Barcode NOT Found...');
	}
	else
	{
		$r = @pg_fetch_object($qr);
		$stock = $r->stock;
		$price1 = $r->price1;
		$barcode = $r->barcode;
		
		$c=0;
		$found = 0;
		foreach ($aBardown as $temp)
		{
			if ($temp['barcode'] ==  $barcode)
			{
				$dummy = $temp;
				$dummy['qty'] = $qty;
				$dummy['barcode'] = $barcode;
				$aBardown[$c] = $dummy;
				$found = 1;
				break;
			}
			$c++;
		}
		if ($found == '0')
		{
			$dummy = null;
			$dummy = array();
			$dummy['stock'] = $stock;
			$dummy['price1'] = $price1;
			$dummy['barcode'] = $barcode;
			$dummy['qty'] = $qty;
			$aBardown[]  = $dummy;
		}
		
		$barcode = '';
		$qty = 1;
	}
} //printing
elseif ($p1 == 'Edit')
{
	$c=0;
	foreach ($aBardown as $temp)
	{
		$c++;
		if ($c == $id)
		{
			$barcode = $temp['barcode'] ;
			$qty = $temp['qty'];
		}
	}
}
elseif ($p1 == 'Delete Checked')
{
	$newarray = null;
	$newarray = array();
	$c=0;
	foreach ($aBardown as $temp)
	{
		$c++;
		if (!in_array($c, $mark))
		{
			$newarray[] = $temp;
		}
	}
	$aBardown = $newarray;
}
if ($qty == '')
{
	$qty = 1;
}


?> 
<form name='form1' method='post' action=''>
  <table width="80%" border="0" align="center" cellpadding="0" cellspacing="2">
    <tr bgcolor="#EFEFEF"> 
      <td height="24" colspan="2" 
background="../graphics/table0_horizontal.PNG"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <strong> <img src="../graphics/bluelist.gif" width="16" height="17"><font color="#FFFFCC">Download 
        Barcode For Printing</font></strong></font></td>
    </tr>
    <tr> 
      <td nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        Barcode 
        <input name="barcode" type="text" id="barcode" value="<?= $barcode;?>" size="16"    onKeypress="if(event.keyCode==13) {document.getElementById('qty').focus();return false;}">
        Copies </font> <font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="qty" type="text" id="qty" value="<?= $qty;?>" size="5">
        <input name="p1" type="submit" id="p1" value="Go" >
        <input name="p1" type="submit" id="p14" value="Add New"  ></td>
      <td width="54%" nowrap><font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"><?= $stock;?>
        </font></td>
    </tr>
    <tr> 
      <td colspan="2"><hr color="#993300" size="1"></td>
    </tr>
  </table>
  <table width="80%" border="0" cellspacing="1" cellpadding="1" height="1%" bgcolor="#DADADA" align="center">
    <tr bgcolor="#333366"> 
      <td height="26" bgcolor="#DADADA"> <font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"><b>Barcode 
        Print Preview &nbsp;&nbsp; | &nbsp;<a href="<?=$reportfile;?>">Download</a></font></td>
    </tr>
    <tr> 
      <td valign="top" bgcolor="#FFFFFF" align="center"><table width="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#EFEFEF">
          <tr> 
            <td width="9%" nowrap><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
            <td width="17%" nowrap><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Barcode</font></strong></td>
            <td width="47%" nowrap><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Description</font></strong></td>
            <td width="14%" align="center" nowrap><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Price</font></strong></td>
            <td width="13%" align="center" nowrap><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Quantity</font></strong></td>
          </tr>
          <?
		  $c=0;
		  $details = '';
		  foreach ($aBardown as $temp)
		  {
		  	$c++;
			$details .=  adjustSize($temp['barcode'],16).' '.
								adjustSize($temp['stock'],35).' '.
								adjustRight(number_format($temp['price1'],2),10).' '.
								adjustRight($temp['qty'],10)."\n";
								
			
			?>
          <tr bgcolor="#FFFFFF"> 
            <td align="right" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
              <?= $c;?>
              . 
              <input name="mark[]" type="checkbox" value="<?= $c;?>">
              </font></td>
            <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
              <?=$temp['barcode'];?>
              </font></td>
            <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
              <?= $temp['stock'];?>
              </font></td>
            <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
              <?= $temp['price1'];?>
              </font></td>
            <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
              <?= $temp['qty'];?>
              &nbsp; </font> &nbsp; </td>
          </tr>
          <?
		  }
		  
			  $fo = @fopen($reportfile,'w+');
			  @fwrite($fo, "\n".$details);
			  @fclose($fo);
			  
			  if ($p1 == 'Print Draft')
			  {
					nPrinter($header.$details, $SYSCONF['REPORT_PRINTER_TYPE'], $SYSCONF['REPORT_PRINTER_DEST']);
			  }

		  ?>
          <tr bgcolor="#FFFFFF"> 
            <td colspan="5" nowrap><input name="p1" type="submit" id="p1" value="Delete Checked"  ></td>
          </tr>
        </table></td>
    </tr>
  </table>
<div align=center>
    <input name="p1" type="submit" id="p1" value="Save as Template"  >
    <input name="p1" type="submit" id="p1" value="Print Draft">
    <input name="p12" type="button" id="p12" value="Print"  onClick="printIframe('print_area')" >
    <input name="p1" type="submit" id="p1" value="New"  >
  </div>
</form>
<div align="center"><br>
  <a href="<?=$reportfile;?>">Right Click Here to Download Data</a> </div>
<iframe name="printit" id="printit" style="width:0px;height:0px;"></iframe>
<script>document.getElementById('barcode').focus()</script>