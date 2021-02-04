<table width="100%" border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#EFEFEF">
  <tr> 
    <td colspan="2" align="center" bgcolor="#CCCCCC"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Product 
      Image</font></td>
  </tr>
  <tr height="200px"> 
    <td width="40%" colspan="2" align="center" valign="bottom" bgcolor="#FFFFFF"><img src="images/<?= $astock['pix'];?>" name="pix" width="300" height="300"> 
      <input type="hidden" name="MAX_FILE_SIZE" value="8192"> <br> <font size="2">Pix 
      150x100 </font> <input type="file" name="pixfile" onChange="vPix()" value="<?=$astock['pixfile'];?>"  tabindex="<?= count($fields)+10;?>"  class="hideTextFormat" size="1"></td>
  </tr>
</table>
  