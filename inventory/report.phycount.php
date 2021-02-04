<?
require_once(dirname(__FILE__).'/../lib/lib.salvio.php');
?>

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
set_time_limit(0);
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

	$tables = currTables($mfrom_date);
	$sales_header = $tables['sales_header'];
	$sales_detail = $tables['sales_detail'];
	$sales_tender = $tables['sales_tender'];
	$stockledger = $tables['stockledger'];

	$q = "select 
					sum(sl.case_qty) as case_qty,
					sum(sl.unit_qty) as unit_qty,
					sl.cost3,
					stock.stock,
					stock.fraction3,
					stock.barcode,
					stock.stock_id,
					stock.account_id,
					stock.category_id
		from 
			stock,
			$stockledger as sl
		where 
			sl.stock_id = stock.stock_id and 
			stock.enable='Y' and
			sl.date>='$mfrom_date' and
			sl.date<='$mto_date'";
				
	if ($account_id != '')
	{
		$q .= " and account_id='$account_id'";
	}
	if ($category_id != '')
	{
		$q .= " and category_id='$category_id'";
	}
	if ($show == 'nouc')
	{
		if ($mvalue == '') 
		{
			message("No Value Specified...");
			$mvalue =1;
			$q .= " and stock.fraction3 ".$condition." '$mvalue'";
		}
		else
		{
			
			$q .= " and stock.fraction3 ".$condition." '$mvalue'";
		}
	}
	elseif ($show == 'spcost')
	{
		if ($mvalue == '') 
		{
			message("No Value Specified...");
		}
		else
		{
			$q .= " and sl.cost3 ".$condition." '$mvalue'";
		}
	}
	elseif ($show == 'spcaseqty')
	{
		if ($mvalue == '') 
		{
			message("No Value Specified...");
		}
		else
		{
			$q .= " and case_qty ".$condition." '$mvalue'";
		}
	}
	elseif ($show == 'spunitqty')
	{
		if ($mvalue == '') 
		{
			message("No Value Specified...");
		}
		else
		{
			$q .= " and unit_qty ".$condition." '$mvalue'";
		}
	}
	$q .= " group by 					
					sl.cost3,
					stock.stock,
					stock.fraction3,
					stock.barcode,
					stock.stock_id,
					stock.account_id,
					stock.category_id ";

	$q .= " order by account_id, category_id, $sort ";
	$qr = @pg_query($q) or message1(pg_errormessage().$q);

	$header = "\n\n";
	$header .= center($SYSCONF['BUSINESS_NAME'],130)."\n";
	$header .= center('PHYSICAL COUNT  REPORT',130)."\n";
	$header .= center('From '.$from_date.' To '.$to_date,130)."\n\n";
	$header .= "------ ---------------------------------------------- ---------------- ----- --------- --------- ---------- --------------\n";
	$header .= "         Item Description                               Bar Code         U/C   Cases     Units     CsCost       Amount  \n";
	$header .= "------ ---------------------------------------------- ---------------- ----- --------- --------- ---------- --------------\n";

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
				$details .= space(70). adjustSize($sctr.' Item/s',20).' '.space(15).
										adjustRight(number_format($stotal_amount,2),14)."\n";
				$lc = $lc+2;
			}						
			$details .= "Supplier: ".adjustSize(lookUpTableReturnValue('x','account','account_id','account',$r->account_id),25)."\n";
			$maccount_id=$r->account_id;
			$lc++;
			$subtotal_cost = $sctr = $stotal_amount = 0;
		}
		if ($mcategory_id != $r->category_id)
		{
			$details .= " ".adjustSize(lookUpTableReturnValue('x','category','category_id','category',$r->category_id),25)."\n";
			$mcategory_id=$r->category_id;
			$lc++;
		}
		
//		$stkled = stockBalance($r->stock_id,$mfrom_date, $mto_date);
		
		if ($combineqty == 'Y')
		{
			if ($r->fraction3 == '1' or $r->fraction3 == '0')
			{
				$bacase = '0';
				$bunits = $r->case_qty + $r->unit_qty;
				$total_cost = $bunits*$r->cost3;
			}
			else
			{
				$qty=$r->case_qty * $r->fraction3 + $r->unit_qty;		
				$bacase = intval($qty/$r->fraction3);
				$bunits = $qty - $bacase*$r->fraction3; 
				$total_cost = $bacase*$r->cost3 + $bunits*($r->cost3/$r->fraction3);
			}
		}
		else
		{
			$bacase = $r->case_qty;
			$bunits = $r->unit_qty;
			$total_cost = $bacase*$r->cost3 + $bunits*($r->cost3/$r->fraction3);

		}		
		$ctr++;
		$sctr++;	

		$details .= adjustRight($ctr,6).'. '.
					adjustSize(substr($r->stock,0,45),45).' '.
					adjustSize($r->barcode,16).' '.
					adjustRight(number_format($r->fraction3,0),5).' '.
					adjustRight($bacase,9).' '.
					adjustRight($bunits,9).' '.
					adjustRight(number_format($r->cost3,2),10).' '.
					adjustRight(number_format($total_cost,2),12)."\n";
		$lc++;
		$total_amount += $total_cost;
		$stotal_amount += $total_cost;		

		if ($lc>55)
		{
			$details1 .= $header.$details;
			if ($p1 == 'Print Draft')
			{
				doPrint($header.$details."<eject>");
				
				//--pang delay lg
				$k=0;
				for ($i=0;$i<1000;$i++)
				{
					$k+=1;
				}
				
			}
			$lc=8;
			$details = '';
		}

	}
		if ($maccount_id != $r->account_id)
		{
			$details .= space(10). adjustSize($sctr.' Item/s',20).' '.space(75).
										adjustRight(number_format($stotal_amount,2),14)."\n";
			$lc = $lc+2;
		}
	$details .= "------ ---------------------------------------------- ---------------- ----- --------- --------- ---------- --------------\n";
	$details .= space(70). adjustSize($ctr.' Item/s',20).' '.space(15).
										adjustRight(number_format($total_amount,2),14)."\n";
	$details .= "------ ---------------------------------------------- ---------------- ----- --------- --------- ---------- --------------\n";
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
      <tr> 
        <td height="21" colspan="7"  background="../graphics/table0_horizontal.PNG"><font  color="#EFEFEF" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
          &nbsp; </strong>.:: <strong>Physical Count Report</strong> ::</font></td>
      </tr>
      <tr> 
        <td width="24%" colspan="2"  nowrap bgcolor="#EFEFEF"><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          <font color="#CC0000">Supplier</font> </font></td>
        <td  nowrap bgcolor="#EFEFEF"><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">From Category</font></td>
        <td  nowrap bgcolor="#EFEFEF"><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">To Category</font></td>
        <td width="10%" nowrap bgcolor="#EFEFEF"><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
          </font><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
          </font> <font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">From 
          </font></td>
        <td align="center"  nowrap bgcolor="#EFEFEF"><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">To</font></td>
        <td width="34%" nowrap bgcolor="#EFEFEF">&nbsp;</td>
      </tr>
      <tr> 
        <td colspan="2"><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          <select name='account_id' id='account_id' style="border: #CCCCCC 1px solid; width:230px">
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

        <td width="22%" colspan="1" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <!-- insert from cateogry here   -->
        <?=lib::getTableAssoc($from_category_id,'from_category_id','Select Category',"select * from category order by category_code asc",'category_id','category',array('category_code','category'))?>
        </td>
        <td width="22%" colspan="1" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <!-- insert from cateogry here   -->
        <?=lib::getTableAssoc($to_category_id,'to_category_id','Select Category',"select * from category order by category_code asc",'category_id','category',array('category_code','category'))?>
        </td>

        <td nowrap> <input name="from_date" type="text" id="from_date" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $from_date;?>" size="8"> 
          <img src="../graphics/arrow_down.jpg" onClick="popUpCalendar(this, form1.from_date, 'mm/dd/yyyy')"> 
        </td>
        <td width="10%" align="center" nowrap><input name="to_date" type="text" id="to_date" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $to_date;?>" size="8"> 
          <img src="../graphics/arrow_down.jpg" onClick="popUpCalendar(this, form1.to_date, 'mm/dd/yyyy')">&nbsp; 
        </td>
        <td width="34%" nowrap>&nbsp; </td>
      </tr>
      <tr> 
        <td nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          Sort<br>
          <?=lookUpAssoc('sort',array('Barcode'=>'barcode','Name'=>'stock','Stock Code'=>stock_code),$sort);?>
          </font></td>
        <td nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">Show<br>
          <?=lookUpAssoc('show',array('All'=>'A','Units/Case'=>'nouc','Specific Cost'=>'spcost','With Case Qty'=>'spcaseqty', 'With Unit Qty'=>'spunitqty'),$show);?>
          </font></td>
        <td><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          If<br>
          <?= lookUpAssoc('condition',array('='=>'=','>='=>'>=','<='=>'<='),$condition);?>
          </font></td>
        <td><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">Value</font><br> 
          <input type="text" size="8" name="mvalue" id="mvalue" value="<?=$mvalue;?>"> 
        </td>
        <td valign="bottom"><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">CombineQty</font><br>
          <?= lookUpAssoc('combineqty',array('Yes'=>'Y','No'=>'N'),$combineqty);?>
        </td>
        <td colspan="2" valign="bottom"><input name="p1" type="submit" id="p1" value="Go">
          <input name="p1" type="submit" id="p1" value="Print Draft" ></td>
      </tr>
      <tr> 
        <td colspan="7" bgcolor="#000066"><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong><img src="../graphics/bluelist.gif" width="16" height="17">Physical 
          Count Report Preview</strong></font> </td>
      </tr>
      <!--<tr> 
        <td colspan="7"><textarea name="print_area" cols="120" rows="20"  wrap="off" readonly><?= $details1;?></textarea></td>
      </tr> -->
    </table>
    
    <? if ($p1=='Go') { ?>	
        <iframe id="JOframe" name="JOframe" style="background-color:#FFF; width:100%;" frameborder="0" height="500" src="print_report.phycount.php?
        account_id=<?=$account_id?>&
        from_category_id=<?=$from_category_id?>&
        to_category_id=<?=$to_category_id?>&
        from_date=<?=$from_date?>&
        to_date=<?=$to_date?>&
        sort=<?=$sort?>&
        show=<?=$show?>&
        condition=<?=$condition?>&
        mvalue=<?=$mvalue?>&
        combineqty=<?=$combineqty?>">
        </iframe>
        <input type="button" value="Print" onclick="printIframe('JOframe');" />
    <? } ?>
    <!--<input name="p1" type="submit" id="p1" value="Print Draft" >
    <input name="p12" type="button" id="p12" value="Print"  onClick="printIframe(print_area)" > -->
  </div>
</form>

<iframe name="printit" id="printit" style="width:0px;height:0px;"></iframe>
