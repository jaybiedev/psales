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
<?
if ($from_date == '')
{
	$from_date = date('m/d/Y');
	$to_date = date('m/d/Y');
}	
if ($p1=='Go' || $p1=='Print Draft')
{
  	@include_once('stockbalance.php');
	$mfrom_date = mdy2ymd($_REQUEST['from_date']);
	$mto_date = mdy2ymd($_REQUEST['to_date']);
	
	$q = "select *
			
		from 
			stock
		where 
			inventory='Y' and
			enable='Y' ";
	if ($account_id != '')
	{
		$q .= " and account_id='$account_id'";
	}
	if ($category_id != '')
	{
		$q .= " and category_id='$category_id'";
	}
	$q .= "order by account_id, category_id, $sort ";
	$qr = @pg_query($q) or message(pg_error());

	$header = "\n\n";
	$header .= center($SYSCONF['BUSINESS_NAME'],130)."\n";
	$header .= center('INVENTORY LEDGER REPORT',130)."\n";
	$header .= center('From '.$from_date.' To '.$to_date,130)."\n\n";
	$header .= "---- ---------------------------------------------- ---------------- ----- --------- --------- --------- --------- ---------\n";
	$header .= "      Item Description                               Bar Code         U/C   BegQty    RecdQty   SoldQty   AdjQty    BalQty  \n";
	$header .= "---- ---------------------------------------------- ---------------- ----- --------- --------- --------- --------- ---------\n";

	if ($p1 == 'Print Draft')
	{
		doPrint("<small3>");
	}
	$details = $details1 = '';
	$ctr=$total_cost = 0;
	$maccount_id = $mcategory_id = 'x~';
	$lc=8;
	$mcount=$subtotal_cost=$scount=0;

	while ($r = pg_fetch_object($qr))
	{
		if ($maccount_id != $r->account_id)
		{
			if ($maccount_id != 'x~')
			{
				$details .= space(68). adjustSize($sctr.' Item/s',12)."\n\n";
				$lc = $lc+2;
			}						
			$details .= "Supplier: ".adjustSize(lookUpTableReturnValue('x','account','account_id','account',$r->account_id),25)."\n";
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
		
		$stkled = stockBalance($r->stock_id,$mfrom_date, $mto_date);
		
		$balance_qty = $stkled['balance_qty'];
		$average_cost = $stkled['average_cost'];
		
		
		if ($balance_qty <=0 && $incZero=='0') continue;
		
		if ($r->fraction3 == '1' or $r->fraction3 == '0')
		{
			$bacase = '0';
			$bunits = $stkled['begqty'];

			$racase = '0';
			$runits = $stkled['in_qty'];

			$sacase = '0';
			$sunits = round($stkled['out_qty'],2);

			$lacase = '0';
			$lunits = $stkled['balance_qty'];
		}
		else
		{
			$bacase = intval($stkled['begqty']/$r->fraction3);
			$bunits = $stkled['begqty'] - $bacase*$r->fraction3; 

			$racase = intval($stkled['in_qty']/$r->fraction3);
			$runits = $stkled['in_qty'] - $racase*$r->fraction3; 

			$sacase = intval($stkled['out_qty']/$r->fraction3);
			$sunits = $stkled['out_qty'] - $sacase*$r->fraction3; 

			$lacase = intval($stkled['balance_qty']/$r->fraction3);
			$lunits = $stkled['balance_qty'] - $lacase*$r->fraction3; 
		}
		$ctr++;
		$sctr++;	

		$details .= adjustRight($ctr,4).'. '.
					adjustSize(substr($r->stock,0,45),45).' '.
					adjustSize($r->barcode,16).' '.
					adjustRight(number_format($r->fraction3,0),5).' '.
					adjustRight($bacase,4).':'.
					adjustRight($bunits,4).' '.
					adjustRight($racase,4).':'.
					adjustRight($runits,4).' '.
					adjustRight($sacase,4).':'.
					adjustRight($sunits,4).' '.
					adjustRight($aacase,4).':'.
					adjustRight($aunits,4).' '.
					adjustRight($lacase,4).':'.
					adjustRight($lunits,4)."\n";
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
		if ($maccount_id != $r->account_id)
		{
			$details .= space(68). adjustSize($sctr.' Item/s',12)."\n";
			$lc = $lc+2;
		}
	$details .= "---- ---------------------------------------------- ---------------- ----- --------- --------- --------- --------- ---------\n";
	$details .= space(10). adjustSize($ctr.' Item/s',18)."\n";
	$details .= "---- ---------------------------------------------- ---------------- ----- --------- --------- --------- --------- ---------\n";
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
    <table width="85%" border="0" cellspacing="1" cellpadding="0" bgcolor="#FFFFFF">
      <tr bgcolor="#EFEFEF" background="../graphics/table0_horizontal.PNG"> 
        <td height="21" colspan="6"><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
          &nbsp; Inventory Ledger </strong> ::</font></td>
      </tr>
      <tr bgcolor="#EFEFEF"> 
        <td width="24%"  nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          <font color="#CC0000">Supplier</font> </font></td>
        <td nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">Category</font></td>
        <td nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          Sort</font></td>
        <td nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
          </font><font color="#000000">&nbsp;</font> <font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">From 
          </font></td>
        <td  nowrap><font color="#000000">&nbsp;</font> <font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">To</font></td>
        <td width="33%" nowrap>&nbsp;</td>
      </tr>
      <tr> 
        <td><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          <select name='account_id' id='account_id' style="border: #CCCCCC 1px solid; width:200px">
            <option value=''>All Suppliers -- </option>
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
        <td width="22%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          <select name="category_id"   style="border: #CCCCCC 1px solid; width:200px">
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
        <td width="9%" align="center" nowrap><input name="from_date" type="text" id="from_date" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $from_date;?>" size="8"> 
          <img src="../graphics/arrow_down.jpg" onClick="popUpCalendar(this, form1.from_date, 'mm/dd/yyyy')"> 
        </td>
        <td width="9%" align="center" nowrap><input name="to_date" type="text" id="to_date" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $to_date;?>" size="8"> 
          <img src="../graphics/arrow_down.jpg" onClick="popUpCalendar(this, form1.to_date, 'mm/dd/yyyy')">&nbsp;
        </td>
        <td width="33%" nowrap> <input name="p1" type="submit" id="p1" value="Go"> 
        </td>
      </tr>
      <tr bgcolor="#000066"> 
        <td colspan="6"><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong><img src="../graphics/bluelist.gif" width="16" height="17">Inventory 
          Preview</strong></font> </td>
      </tr>
      <!--<tr>
        <td colspan="6"><textarea name="print_area" cols="120" rows="20"  wrap="off" readonly><?= $details1;?></textarea></td>
      </tr> -->
    </table>
	<? if ($p1=='Go') { ?>	
        <iframe id="JOframe" name="JOframe" style="background-color:#FFF;" frameborder="0" width="100%" height="500" src="print_report.inventoryledger.php?account_id=<?=$account_id?>&category_id=<?=$category_id?>&sort=<?=$sort?>&from_date=<?=$from_date?>&to_date=<?=$to_date?>">
        </iframe>
        <input type="button" value="Print" onclick="printIframe('JOframe');" />
    <? } ?>
    <!--<input name="p1" type="submit" id="p1" value="Print Draft" >
    <input name="p12" type="button" id="p12" value="Print"  onClick="printIframe(print_area)" > -->
  </div>
</form>

<iframe name="printit" id="printit" style="width:0px;height:0px;"></iframe>
