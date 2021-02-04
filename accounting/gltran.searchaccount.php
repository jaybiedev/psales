<script language="JavaScript" type="text/JavaScript">
<!--
function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);

function vaccount(id, account)
{
	document.getElementById('account').value = account;
	document.getElementById('account_id').value = id;
	LayeraccountSearch.style.visibility='hidden';
}

//-->
</script>
<?	
	$q = "select * from account where account like '$account%' order by account";

	$qr = @pg_query($q) or message1(pg_error());
?>
<div id="LayeraccountSearch" name="LayeraccountSearch" style="position:absolute; width:60%; height:65%; z-index:1; overflow: auto; left: 35%; top: 30%;"> 
  <table width="100%" border="0" cellspacing="1" cellpadding="1" bgcolor="#E9E9E9" background="graphics/table0_horizontal.PNG">
	  <tr> 
		<td height="31" colspan="3" valign="top"> <table background="graphics/table0_horizontal.PNG" width="100%" cellpadding="0" cellspacing="0">
			<tr> 
			  <td> <strong><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif">Search 
				Payee</font></strong></td>
			  <td align="right"><img src="graphics/table_close.PNG" width="21" height="21" onClick="LayeraccountSearch.style.visibility='hidden'">&nbsp; 
			  </td>
			</tr>
		  </table></td>
	  </tr>
    <tr>
      <td bgcolor="#3399CC"><table width="100%" border="0" align="center" cellpadding="1" cellspacing="1" bgcolor="#EFEFEF">
          <tr> 
            <td width="8%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
            <td width="61%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Payee</font></strong></td>
            <td width="31%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Class</font></strong></td>
          </tr>
          <?
		$ctr=0;
		while ($r = @pg_fetch_object($qr))
		{
			$ctr++;
	?>
          <tr bgColor="#FFFFFF" onMouseOver="bgColor='#FFFFCC'" onMouseOut="bgColor='#FFFFFF'"> 
            <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
              <?=$ctr;?>
              .</font></td>
            <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
			<a href="javascript: vaccount(<?=$r->account_id;?>,'<?=addslashes($r->account);?>')">
              <?= $r->account;?></a>
              </font></td>
            <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
              <?= lookUpTableReturnValue('x','account_type','account_type','account_type_description',$r->account_type);?>
              </font></td>
          </tr>
          <?
		}
	?>
        </table></td>
    </tr>
  </table>
</div>
