<br><form name="f1"  id="f1" method="post" action="">
Price LookUp <input name='searchstock' type='text' id='searchstock' value="<?= $searchstock;?>" size ="25">
<input type="submit" name="p1" id="p1" value="Search" >
  <input type="submit" name="p1" id="p1" value="Clear Data" >
  <input type="button" name="Submit232" value="Close" onClick="window.location='?p='" accesskey="C">
  <hr>
</form>
<?
if (!session_is_registered('aPLU'))
{
	session_register('aPLU');
	$aPLU  = null;
	$aPLU = array();
}
function plu($stock_id, $category_id, $supplier_id,$netitem)
{
			$today = date('Y-m-d');
			$aplu = null;
			$aplu = array();
			
			$q = "select 
									promo_detail.promo_price, 
									promo_header.cdisc,
									promo_header.sdisc
								from	
									promo_header, promo_detail
								where
									promo_header.promo_header_id=promo_detail.promo_header_id and 	
									promo_detail.stock_id='$stock_id'	and	
									promo_header.date_from<='$today'	and	
									promo_header.date_to>='$today' and
									promo_header.enable='Y'
								order by
									promo_detail.promo_header_id desc
								offset 0 limit 1";

				$qpr	=	@query($q);
				if (@pg_num_rows($qpr) == 0)
				{
					$q = "select department, category_code from category where category_id='$category_id'";
					$rc = fetch_assoc($q);
					$department = $rc['department'];
					$category_code = trim($rc['category_code']);

					$clen = strlen($category_code);
					$q = "select 
										promo_header.cdisc,
										promo_header.sdisc,
										promo_header.category_id_from,
										promo_header.category_id_to,									
										promo_header.category_code_from,
										promo_header.category_code_to									
									from 
										promo_header 
									where
										account_id = '$supplier_id' and
										promo_header.date_from<='$today'	and	
										promo_header.date_to>='$today' and
										((substr(category_code_from,1,$clen) <= '$category_code' and
										substr(category_code_to,1,$clen) >= '$category_code' ) or
										(category_code_from = '')) and
										promo_header.all_items='Y' and 
										promo_header.enable='Y'";
					if ($netitem == 'Y')
					{
						$q .= " and promo_header.include_net !='N'";
					}
					else
					{
						$q .= " and promo_header.include_net !='Y'";
					}
					$qpr	=	@query($q);

		}
		if (@pg_num_rows($qpr) > 0)
		{
				$rr = @pg_fetch_object($qpr);
				$aplu['price'] = round($r['price']*(1-(($rr->cdisc + $rr->sdisc)/100)),2);
				$aplu['cdisc']	=	$rr->cdisc;
				$aplu['sdisc']	=	$rr->sdisc;
		}
		return $aplu;
}

$browse = 1;


if ($p1 == 'Clear Data')
{
	$aPLU =  null;
	$aPLU = array();
}
if ($p1 == 'Search')
{
	$searchstock = strtoupper($searchstock);
	$q = "select * from stock where barcode='$searchstock'";
	$qr = @pg_query($q) or message1(pg_errormessage());
	if (@pg_num_rows($qr) > 0)
	{
		echo "<script>document.getElementById('searchstock').value=''</script>";
		$r = @pg_fetch_assoc($qr);
		$aPLU[] = $r ;

	}
	else
	{
		
		$q = "select * from stock where upper(stock_description) like'%$searchstock%'";
		$qr = @pg_query($q) or message1(pg_errormessage());
		if (@pg_num_rows($qr) == 0)
		{	

			message1("Search NOT found..."); 
		}
		else //if (@pg_num_rows($qr) > 0)
		{
			$browse = 0;
			while ($r = @pg_fetch_object($qr))
			{
				echo "Barcode : ".$r->barcode."</br>";
				echo "Item    : ".$r->stock_description."</br>";
				echo "U/C     : ".$r->fraction3."</br>";
				echo "SRP     : ".number_format($r->price1,2)."</br>";
				echo "<hr>";
			}
		}
	}
}
echo "<script>document.getElementById('searchstock').focus()</script>";
if ($browse == 0 || count($aPLU) == 0)
{
	exit;
}
?>
<table width="95%" border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#EFEFEF">
  <tr> 
    <td width="3%" height="18"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></td>
    <td width="18%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Barcode</font></td>
    <td width="37%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Description</font></td>
    <td width="7%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">U/C</font></td>
    <td width="10%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Reg.Price</font></td>
    <td width="11%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Discount</font></td>
    <td width="14%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">PromoPrice</font></td>
  </tr>
  <?
  $ctr=0;
  $today = date('Y-m-d');
  foreach ($aPLU as $temp)
  {
  	$ctr++;
	$a=plu($temp['stock_id'], $temp['category_id'], $temp['supplier_id'],$temp['netitem']);
	if ($temp['date2_promo']>=$today  && $temp['date1_promo']<=$today)
	{

		$cdisc = $temp['promo_cdisc'];
		$sdisc = $temp['promo_sdisc'];
		
		if ($temp['promo_price1'] > 0)
		{
			$price =  $temp['promo_price1'];
		}
		else
		{
			$price = round($temp['price1']*(1-(($temp['promo_cdisc'] + $temp['promo_sdisc'])/100)),2);
		}
		
	}
	elseif ($a['price'] > 0) 
	{

		$price =  $a['price'];
		$cdisc = $a['cdisc'];
		$sdisc = $a['sdisc'];
	}

	else
	{
		$price = $temp['price1'];
		$cdisc = 0;
		$sdisc = 0;
		$disc='';
	}
	$disc='';
	if ($cdisc > 0)
	{
		$disc = $cdisc.';';
	}
	if ($sdisc > 0)
	{
		$disc .= $sdisc.';';
	}
	
?>
  <tr bgcolor="#FFFFFF"> 
    <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><?= $ctr;?>. </font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><?= $temp['barcode'];?></font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><?= $temp['stock'];?>
      </font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
      <?= $temp['fraction3'];?>
      </font></td>
    <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
      <?= number_format($temp['price1'],2);?>
      </font></td>
    <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
      <?= $disc;?>
      </font></td>
    <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
      <?= number_format($price,2);?>&nbsp;
      </font></td>
  </tr>
  <?
  }
  ?>
  <tr> 
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;</font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;</font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;</font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;</font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;</font></td>
    <td>&nbsp;</td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;</font></td>
  </tr>
</table>
