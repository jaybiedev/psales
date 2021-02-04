<?
$s = "
<table width=\"99%\" height=\"1%\" border=\"0\" align=\"left\" cellpadding=\"1\" cellspacing=\"1\" bgcolor=\"#EFEFEF\">
  <tr> 
    <td height=\"34\" colspan=\"4\" background=\"graphics/table0_horizontal.PNG\"> <table width=\"100%\" height=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" background=\"graphics/table0_horizontal.PNG\">
        <tr> 
          <td height=\"32\" background=\"../graphics/table0_horizontal.PNG\"><font size=\"2\" face=\"Verdana, Arial, Helvetica, sans-serif\"><strong> 
            <img src=\"../graphics/eyeglass.gif\"> Price LookUp</strong></font></td>
          <td align=\"right\" background=\"../graphics/table0_horizontal.PNG\"><img accesskey=\"X\" onClick=\"document.getElementById('plu.layer').innerHTML='';document.getElementById('plu.layer').style.display='none';document.getElementById('plu_pop_up').value='0'\" src=\"../graphics/close2.gif\" width=\"15\" height=\"13\"></td>
        </tr>
      </table></td>
  </tr>
  <tr height=\"5%\"> 
    <td width=\"8%\" height=\"18\"><strong><font size=\"2\" face=\"Verdana, Arial, Helvetica, sans-serif\">#</font></strong></td>
    <td width=\"20%\"> <strong><font face=\"Verdana, Arial, Helvetica, sans-serif\" size=\"2\">Barcode</font></strong></td>
    <td width=\"56%\"><strong><font size=\"2\" face=\"Verdana, Arial, Helvetica, sans-serif\">Product 
      Description </font></strong></td>
    <td width=\"16%\"><strong><font size=\"2\" face=\"Verdana, Arial, Helvetica, sans-serif\">Price</font></strong></td>
  </tr>";
  
 $q = "select stock_id, barcode, stock,price1 from $stocktable where enable='Y' and stock ilike '$textbox%' offset 0 limit 15";
 $qr = @pg_query($q);
 
 if (!$qr)
 {
 	$message = "Error Querying ".pg_errormessage()." ".$q;
 }
 $ctr=0;
  while ($r=@pg_fetch_object($qr))
  {
	$onm = 'onMouseOver="'."bgColor='#FFCCCC' ".'" onMouseOut="'."bgColor='#EFEFEF'".'"';  
    $link1 = 'onClick="'."document.getElementById('textbox').value=$r->barcode;hide_layer('plu.layer');"."document.getElementById('plu_pop_up').value=0;".'"';
  	$ctr++;
 	$s .= "<tr $link1 $onm>
			<td align=\"right\"><input type='hidden' name='mark[]' size='1' value='$r->stock_id'>$ctr. </td>
		    <td><font face='Verdana' size='2'>$r->barcode</font></a></td>
		    <td><font size='2'>$r->stock</font></td>
		    <td align=\"right\"><font size='2'>$r->price1</font></td>
			</tr>";
  }

  $s .= "<tr><td colspan='4'><hr></td></tr>";
  $s .= "<tr><td colspan='4' align='center'>Enter Line Number To Select<input type='hidden' name='plu_pop_up' id='plu_pop_up' size='1' value='1'></td></tr>";
  $s .= "<tr><td colspan='4' align='center'>F6 To Search Again<br></td></tr>";
  $s .= "</table>";
  $s .= "<script>document.getElementById('textbox_plu').focus()</script>";
  glayer('plu.layer',$s);
?>