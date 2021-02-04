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
<div id="displayitems" style="position:absolute; width:70%; top:16%; left:10%; height:500px; z-index:1; background-color: #EFEFEF; layer-background-color: #EFEFEF; border: 1px none #000000;">
<?
		$por_header_id='';
		$por_amount= $net_amount = $gross_amount = $discount_amount = 0;
		
		if ($type == 'RR')
		{
		  $q = "select
						*						
				 from 
				 		rr_header, 
						account 
				where  
						account.account_id=rr_header.account_id and 
						rr_header.rr_header_id = '$id' and status != 'C'";

		  $qr = @pg_query($q) or message1(pg_errormessage());
		  $r = @pg_fetch_object($qr);
		  $gross_amount = $r->gross_amount;  
		  $discount_amount = $r->discount_amount;
		  $rr_header_id = $r->rr_header_id;
		  $net_amount = $r->net_amount;	


		  $q = "select
						*						
				 from 
				 		por_header
				where  
						por_header.rr_header_id = '$rr_header_id' and status != 'C'";

			$qr = @pg_query($q) or message1(pg_errormessage());

//echo $por_amount.' no of records '.@pg_num_rows($qr).'  '.$q;
			if (@pg_num_rows($qr)>0)
			{
		 		$rp = @pg_fetch_object($qr);
				$por_header_id = $rp->por_header_id;
		  		$por_amount = $rp->net_amount;
			}	
//echo 'por amount '.$por_amount;
		}
		else
		{
		  $q = "select
						*						
				 from 
				 		por_header
				where  
						por_header.por_header_id = '$id'";

			$qr = @pg_query($q) or message1(pg_errormessage());
			if (@pg_num_rows($qr)>0)
			{
		 		$rp = @pg_fetch_object($qr);
				$por_header_id = $rp->por_header_id;
		  		$por_amount = $rp->net_amount;
			}	
		}
		$net_amount = $net_amount - $por_amount;

		  
?>
  <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#EFEFEF">
    <tr bgcolor="#FFFFFF"> 
      <td width="3" height="27" align="center" background="../graphics/table0_upper_left.PNG"></td>
      <td colspan="3" background="../graphics/table0_horizontal.PNG"><font color="#FFFFFF" size="2"> <b>
        <?= substr($r->account,0,30).' SRR No:'.$r->rr_header_id.' Invoice: '.$r->invoice;?>
        </b></font></td>
      <td width="89" align="right" background="../graphics/table0_horizontal.PNG">
	  <img src="../graphics/table_close.PNG" width="21" height="21" onClick="document.getElementById('displayitems').style.visibility='hidden'"></td>
      <td width="5" align="center" background="../graphics/table0_upper_right.PNG"></td>
    </tr>
    <tr> 
      <td height="25" align="center"  bgcolor="#75AAD5"></td>
      <td colspan="4" rowspan="2" valign="top" height='400px'><div id="Layer1" style="position:virtual; width:100%; height:100%; z-index:2; overflow: scroll;">
          <table width="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#EFEFEF">
            <tr> 
              <td colspan="4"><strong><font color="#000099" size="3" face="Verdana, Arial, Helvetica, sans-serif">Gross 
                :</font><font color="#CC0000" size="3" face="Verdana, Arial, Helvetica, sans-serif"> 
                <?= number_format($gross_amount,2);?>
                </font><font color="#330099" size="3" face="Verdana, Arial, Helvetica, sans-serif">Disc:</font><font color="#CC0000" size="3" face="Verdana, Arial, Helvetica, sans-serif"> 
                <?= number_format($discount_amount,2);?>
                </font></strong><font color="#CC0000"><strong><font color="#330099" size="3" face="Verdana, Arial, Helvetica, sans-serif">Return:</font><font color="#CC0000" size="3" face="Verdana, Arial, Helvetica, sans-serif">  
                <?= number_format($por_amount,2);?>
                </font><font color="#330099" size="3" face="Verdana, Arial, Helvetica, sans-serif">Net 
                Due:</font><font size="3" face="Verdana, Arial, Helvetica, sans-serif">
                <?= number_format($net_amount,2);?>
                </font></strong></font></td>
            </tr>
            <tr align="center" bgcolor="#DADADA"> 
              <td width="20%" nowrap><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">barcode</font></strong></td>
              <td width="44%" nowrap><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">description</font></strong></td>
              <td width="17%" nowrap><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Case 
                : Units</font></strong></td>
              <td width="19%" nowrap><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Amount</font></strong></td>
            </tr>
            <?
         
         
		  if ($rr_header_id > '0')
		  {
		  	$q = "select *
			  	from
					rr_detail,
					stock
				where
					stock.stock_id = rr_detail.stock_id and 
					rr_header_id = '$r->rr_header_id'";

				$qqr = @pg_query($q) or message1(pg_errormessage().$q);

				$ctr=0;
				while ($rr = @pg_fetch_object($qqr))
				{
					$ctr++;
					$amount = $rr->amount
			?>
            <tr bgcolor="#FFFFFF" onMouseOver="bgColor='#FFFFCC'" onMouseOut="bgColor='#FFFFFF'"> 
              <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
                <?= $rr->barcode;?>
                </font></td>
              <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
                <?= $rr->stock;?>
                </font></td>
              <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
                <?= $rr->case_qty .' : '. $rr->unit_qty;?>
                </font></td>
              <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
                <?= number_format($amount,2);?>
                </font></td>
            </tr>
            <?
		  		}

			}

		  if ($por_header_id != '')
		  {
		  		echo "<tr><td colspan='4'><font size='2'><b>Purchase Returns</b></font></td></tr>";
				  $q = "select *
						from
							por_detail,
							stock
						where
							stock.stock_id = por_detail.stock_id and 
							por_header_id = '$por_header_id'";
			
						$qqr = @pg_query($q) or message1(pg_errormessage().$q);

						while ($rr = @pg_fetch_object($qqr))
						{
							$ctr++;
//							if ($rr->taxable == 'Y') $amount = $rr->amount * 1.12;
							$amount = $rr->amount;
						?>
						<tr bgcolor="#FFFFFF" onMouseOver="bgColor='#FFFFCC'" onMouseOut="bgColor='#FFFFFF'"> 
						  <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
							<?= $rr->barcode;?>
							</font></td>
						  <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
							<?= $rr->stock;?>
							</font></td>
						  <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
							<?= $rr->case_qty .' : '. $rr->unit_qty;?>
							</font></td>
						  <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
							<?= number_format($amount,2);?>
							</font></td>
						</tr>
						<?
					  }
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
