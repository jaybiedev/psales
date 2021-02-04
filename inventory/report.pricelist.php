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
<script type="text/javascript">
function printIframe(id)
{
    var iframe = document.frames ? document.frames[id] : document.getElementById(id);
    var ifWin = iframe.contentWindow || iframe;
    iframe.focus();
    ifWin.printPage();
    return false;
}
</script>
<?
if ($from_date == '')
{
	$from_date = date('m/d/Y');
}	
if ($p1=='Go' || $p1=='Print Draft')
{
	if ($from_date == '')
	{
		$from_date = date('m/d/Y');
	}	
	$mdate = mdy2ymd($from_date);
	
	$q = "select *
			
		from 
			stock
		where 
			enable='Y' ";
	if ($account_id != '')
	{
		$q .= " and account_id='$account_id'";
	}
	if ($category_id != '')
	{
		$q .= " and category_id='$category_id'";
	}
	$q .= " order by account_id, category_id, $sort ";
	$qr = @pg_query($q) or message(pg_errormessage());
	$header = "\n\n";
	$header .= center($SYSCONF['BUSINESS_NAME'],80)."\n";
	$header .= center('PRICE LISTING REPORT',80)."\n";
	$header .= center('As of Date '.$from_date,80)."\n\n";
	$header .= "---- ---------------------------------------------- ---------------- ----- ----- ------------\n";
	$header .= "      Item Description                               Bar Code        Unit  U/C      UnPrice   \n";
	$header .= "---- ---------------------------------------------- ---------------- ----- ----- ------------\n";

	if ($p1 == 'Print Draft')
	{
		doPrint("<small3>");
	}
	$details = $details1 = '';
	$ctr=$total_cost = 0;
	$maccount_id = $mcategory_id = 'x~';
	$lc=8;
	$mcount=$subtotal_cost=$scount=0;
	while ($r = @pg_fetch_object($qr))
	{
		if ($maccount_id != $r->account_id)
		{
			if ($maccount_id != 'x~')
			{
				$details .= "\n";
				$lc = $lc+1;
			}						
			$details .= "Producer/Supplier: ".adjustSize(lookUpTableReturnValue('x','account','account_id','account',$r->account_id),25)."\n";
			$maccount_id=$r->account_id;
			$lc++;
			$subtotal_cost = $sctr = 0;
		}
		if ($mcategory_id != $r->category_id)
		{
			$details .= "\n  ".adjustSize(lookUpTableReturnValue('x','category','category_id','category',$r->category_id),25)."\n";
			$mcategory_id=$r->category_id;
			$lc++;
		}
		
		$ctr++;
		$sctr++;	
		
		if ($r->stock_description != '') $stock_description=$r->stock_description;
		else $stock_description = $r->stock;

		$details .= adjustRight($ctr,4).'. '.
					adjustSize(substr($stock_description,0,45),45).' '.
					adjustSize($r->barcode,16).' '.
					adjustSize($r->unit1,5).' '.
					adjustSize($r->fraction3,5).' '.
					adjustRight(number_format($r->price1,2),10)."\n";
		if (strlen($stock_description)>45)
		{
			$details .= space(14).adjustSize(substr($stock_descsription,46,45),45)."\n";
			$lc++;
		}			
		$lc++;

		if ($lc>55)
		{
			$details1 .= $header.$details;
			if ($p1 == 'Print Draft')
			{
				doPrint($header.$details."<eject>");
			}
			$lc=8;
			$details = '';
		}

	}
	$details .= "---- ---------------------------------------------- ---------------- ----- ----- ------------\n";
	$details1 .= $header.$details;
	if ($p1=='Print Draft')
	{
		doPrint($header.$details);
	}	
}
else
{
	$incZero = 1;
}
if ($from_date == '') $from_date=date('m/d/Y');	
?>	
<form name="form1" method="post" action="">
  <div align="center">
    <table width="90%" border="0" cellspacing="1" cellpadding="0" bgcolor="#FFFFFF">
      <tr bgcolor="#EFEFEF"> 
        <td height="21" colspan="4" nowrap  background="../graphics/table0_horizontal.PNG"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>:: 
          Price List</strong></font></td>
      </tr>
      <tr bgcolor="#EFEFEF"> 
        <td width="23%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          <font color="#CC0000">Supplier</font> </font></td>
        <td width="23%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">Category</font></td>
        <td width="3%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          Sort</font></td>
        <td nowrap>&nbsp;</td>
      </tr>
      <tr> 
        <td><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          <select name='account_id' id='account_id' style="border: #CCCCCC 1px solid; width:200px">
            <option value=''>All  Suppliers -- </option>
            <?
  		foreach ($aSUPPLIER as $stemp)
		{
			if ($stemp['account_id'] == $account_id)
			{
				echo "<option value=".$stemp['account_id']." selected>".substr($stemp['account_code'],0,6)." ".$stemp['account']."</option>";
			}
			else
			{
				echo "<option value=".$stemp['account_id'].">".substr($stemp['account_code'],0,6)." ".$stemp['account']."</option>";
			}
		}
		?>
          </select>
          </font></td>
        <td width="23%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          <select name="category_id"   tabindex="<?= array_search('category_id',$fields);?>" style="border: #CCCCCC 1px solid; width:200px">
            <option value=''>All Categories--</option>
            <?
		foreach ($aCATEGORY as $ctemp)
		{
			if ($SYSCONF['SORT_CATEGORY'] == 'category')
			{
				$category_code = '';
			}
			else
			{
				$category_code = substr($ctemp['category_code'],0,6);
			}
			if ($ctemp['category_id'] == $category_id)
			{
				echo "<option value=".$ctemp['category_id']." selected>".$category_code." ".$ctemp['category']."</option>";
			}
			else
			{
				echo "<option value=".$ctemp['category_id']." >".$category_code." ".$ctemp['category']."</option>";
			}
		}
	  ?>
          </select>
          </font></td>
        <td width="3%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          <?=lookUpAssoc('sort',array('Barcode'=>'barcode','Name'=>'stock','Stock Code'=>stock_code),$sort);?>
          </font> </td>
        <td nowrap> &nbsp; <input name="p1" type="submit" id="p1" value="Go"> 
        </td>
      </tr>
      <tr> 
        <!--<td colspan="4"><textarea name="textarea" cols="110" rows="20"  wrap="off" id="targetTextArea" readonly><?= $details1;?></textarea></td> -->
        <td colspan="4">
        	<? if ($p1=='Go') { ?>
        	<iframe id="JOframe" name="JOframe" frameborder="0" width="100%" height="500" src="print_report.pricelist.php?account_id=<?=$account_id?>&category_id=<?=$category_id?>&sort=<?=$sort?>">
        	</iframe>
            <? } ?>
       	</td>
      </tr>
    </table>
    
    <!--<input name="p1" type="submit" id="p1" value="Print Draft" >
    <input name="p12" type="button" id="p12" value="Print"  onClick="printIframe(print_area)" > -->
	<input type="button" value="Print" onclick="printIframe('JOframe');" />
  </div>
</form>

<iframe name="printit" id="printit" style="width:0px;height:0px;"></iframe>
