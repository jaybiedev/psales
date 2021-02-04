<script language="JavaScript" type="text/JavaScript">
<!--
function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);

function vRemark(remark)
{
	document.getElementById('particulars').value = remark;
	LayergltranRemark.style.visibility='hidden';
	document.getElementById('particulars').focus();
}

//-->
</script>
<?	
	$q = "select * from gltranremark where enable='Y'";

	$qr = @pg_query($q) or message1(pg_errormessage());
?>
<div id="LayergltranRemark" name="LayergltranRemark" style="position:absolute; width:40%; height:65%; z-index:1; overflow: auto; left: 52%; top: 30%; visibility: hidden;"> 
  <table width="100%" border="0" cellspacing="1" cellpadding="1" bgcolor="#E9E9E9" background="../graphics/table0_horizontal.PNG">
	  <tr> 
		<td height="31" colspan="3" valign="top"> <table background="../graphics/table0_horizontal.PNG" width="100%" cellpadding="0" cellspacing="0">
			<tr> 
			  
            <td> <strong><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif">Search 
              Remarks</font></strong></td>
			  <td align="right"><img src="../graphics/table_close.PNG" width="21" height="21" onClick="LayergltranRemark.style.visibility='hidden'">&nbsp; 
			  </td>
			</tr>
		  </table></td>
	  </tr>
    <tr>
      <td bgcolor="#3399CC"><table width="100%" border="0" align="center" cellpadding="2" cellspacing="1" bgcolor="#EFEFEF">
          <tr> 
            <td width="13%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
            <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Remarks</font></strong><strong></strong></td>
          </tr>
          <?
		$ctr=0;
		while ($r = @pg_fetch_object($qr))
		{
			$ctr++;
	?>
          <tr valign="top" bgColor="#FFFFFF" onMouseOver="bgColor='#FFFFCC'" onMouseOut="bgColor='#FFFFFF'"> 
            <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
              <?=$ctr;?>
              .</font></td>
            <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href="javascript: vRemark('<?=addslashes($r->gltranremark);?>')"> 
              <?= $r->gltranremark;?>
              </a> </font><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
              </font></td>
          </tr>
          <?
		}
	?>
        </table></td>
    </tr>
  </table>
</div>
