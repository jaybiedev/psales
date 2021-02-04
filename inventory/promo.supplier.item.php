 <script language="JavaScript" type="text/JavaScript">
<!--
function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);
//-->
</script>
<div id="displayitems" style="position:absolute; width:50%; height:500px; z-index:1; top: 50px; left: 5%;">
<?
		  $q = "select
		  				promo_header.promo_header_id,
		  				promo_header.sdisc,
		  				promo_header.cdisc,
						promo_header.category_code_from,
						promo_header.category_code_to,
						promo_header.include_net, 
						account.account_id,
						account.account_code,
						account.account
						
				 from 
				 		promo_header, 
						account 
				where  
						account.account_id=promo_header.account_id and 
						promo_header_id = '$id'";

		  $qr = @pg_query($q) or message1(pg_errormessage());
		  $r = @pg_fetch_object($qr);
		
 		 $cflen = strlen(rtrim($r->category_code_from));
 		 $ctlen = strlen(rtrim($r->category_code_to));
		  
		  $q = "select *
		  	from
				stock,
				category
			where
				category.category_id = stock.category_id and 
				account_id = '$r->account_id' and 
				stock.enable='Y' ";
			
			if ($r->category_code_from != '')
			{
				$q .= " and category_code >= substr('$r->category_code_from',1,$cflen) and
							category_code <= substr('$r->category_code_to',1,$ctlen)";
			}
			if ($r->include_net == 'N')
			{
				$q .= " and netitem = 'N'";
			}
			if ($r->include_net == 'Y')
			{
				$q .= " and netitem = 'Y'";
			}
			$qqr = @pg_query($q) or message1(pg_errormessage().$q);

?>
  <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#EFEFEF">
    <tr bgcolor="#FFFFFF"> 
      <td width="3" height="27" align="center" background="../graphics/table0_upper_left.PNG"></td>
      <td colspan="3" background="../graphics/table0_horizontal.PNG"><font color="#FFFFFF" size="2"> <b>
        <?= $r->account_code.' Less:'.round($r->sdisc,0).'% ' .$r->category_code_from.' to '.$r->category_code_to;?>
        </b></font></td>
      <td width="89" align="right" background="../graphics/table0_horizontal.PNG">
	  <img src="../graphics/table_close.PNG" width="21" height="21" onClick="document.getElementById('displayitems').style.visibility='hidden'"></td>
      <td width="5" align="center" background="../graphics/table0_upper_right.PNG"></td>
    </tr>
    <tr> 
      <td height="25" align="center"  bgcolor="#75AAD5"></td>
      <td colspan="4" rowspan="2" valign="top" height='400px'><div id="Layer1" style="position:virtual; width:100%; height:100%; z-index:2; overflow: auto;">
          <table width="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#EFEFEF">
            <tr> 
              <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">barcode</font></strong></td>
              <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">description</font></strong></td>
              <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">price</font></strong></td>
              <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">promo</font></strong></td>
            </tr>
            <?
			$ctr=0;
			while ($rr = @pg_fetch_object($qqr))
			{
				$ctr++;
			?>
            <tr bgcolor="#FFFFFF" onMouseOver="bgColor='#FFFFCC'" onMouseOut="bgColor='#FFFFFF'"> 
              <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
                <?= $rr->barcode;?>
                </font></td>
              <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
                <?= $rr->stock;?>
                </font></td>
              <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
                <?= number_format($rr->price1,2);?>
                </font></td>
              <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
                <?= number_format(round($rr->price1* (1 - ($r->sdisc+ $r->cdisc )/100),2),2);?>
                </font></td>
            </tr>
            <?
		  }
		  ?>
          </table>
        </div></td>
      <td align="center" bgcolor="#75AAD5"></td>
    </tr>
    <tr> 
      <td height="138" align="center"  bgcolor="#75AAD5"></td>
      <td align="center" bgcolor="#75AAD5"></td>
    </tr>
    <tr> 
      <td height="2" align="center" bgcolor="#75AAD5"> </td>
      <td colspan="4" align="center"  bgcolor="#75AAD5"></td>
      <td align="center" bgcolor="#75AAD5"></td>
    </tr>
  </table>
</div>
