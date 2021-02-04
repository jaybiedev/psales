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
	if ($from_date == '')
	{
		$from_date = date('m/d/Y');
	}	
	$mdate = mdy2ymd($from_date);
	
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
	$header .= center('As of Date '.$from_date,130)."\n\n";
	$header .= "---- ---------------------------------------------- ---------------- ----- --------- --------- --------- ---------\n";
	$header .= "      Item Description                               Bar Code         U/C   BegQty    RecdQty   AdjQty    BalQty  \n";
	$header .= "---- ---------------------------------------------- ---------------- ----- --------- --------- --------- ---------\n";

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
		
		$stkled = stockBalance($r->stock_id,'', $mdate);
		
		$balance_qty = $stkled['balance_qty'];
		$average_cost = $stkled['average_cost'];
		
		
		if ($balance_qty <=0 && $incZero=='0') continue;
		
		if ($r->fraction3 == '1' or $r->fraction3 == '0')
		{
			$racase = '0';
			$runits = $stkled['in_qty'];

			$lacase = '0';
			$lunits = $stkled['balance_qty'];
		}
		else
		{
			$racase = intval($stkled['in_qty']/$r->fraction3);
			$runits = $stkled['in_qty'] - $racase*$r->fraction3; 

			$lacase = intval($stkled['balance_qty']/$r->fraction3);
			$lunits = $stkled['balance_qty'] - $racase*$r->fraction3; 
		}
		$ctr++;
		$sctr++;	

		$details .= adjustRight($ctr,4).'. '.
					adjustSize(substr($r->stock,0,45),45).' '.
					adjustSize($r->barcode,16).' '.
					adjustRight(number_format($r->fraction3,0),5).' '.
					adjustRight($bacase,4).'-'.
					adjustRight($bunits,4).' '.
					adjustRight($racase,4).'-'.
					adjustRight($runits,4).' '.
					adjustRight($aacase,4).'-'.
					adjustRight($aunits,4).' '.
					adjustRight($lacase,4).'-'.
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
	$details .= "---- ---------------------------------------------- ---------------- ----- --------- --------- --------- ---------\n";
	$details .= space(60). adjustSize($ctr.' Item/s',18)."\n";
	$details .= "---- ---------------------------------------------- ---------------- ----- --------- --------- --------- ---------\n";
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
        <td width="17%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Inventory 
          Ledger </strong> ::</font></td>
        <td width="20%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          <font color="#CC0000">Supplier</font> </font></td>
        <td width="3%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">Category</font></td>
        <td width="8%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          Sort</font></td>
        <td width="5%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
          </font><font color="#000000">&nbsp;</font> <font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">From 
          </font></td>
        <td width="5%" nowrap><font color="#000000">&nbsp;</font> <font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">To</font></td>
        <td width="42%" nowrap>&nbsp;</td>
      </tr>
      <tr> 
        <td width="17%">&nbsp;</td>
        <td width="20%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          <select name="account_id" style="width:200" >
            <option value=''>All Suppliers --- </option>
            <?
		$q = "select * from account, account_type where account.account_type_id=account_type.account_type_id and account_type_code='S' order by account_code, account ";
		$qr = pg_query($q);
		while ($r= pg_fetch_object($qr))
		{
			if ($account_id == $r->account_id)
			{
				echo "<option value=$r->account_id selected>$r->account_code $r->account</option>";
			}
			else
			{		
				echo "<option value=$r->account_id>$r->account_code $r->account</option>";
			}	
		}
		
		?>
          </select>
          </font></td>
        <td width="3%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          <select name="category_id" style="width:180" >
            <option value=''>All Categories --- </option>
            <?
		$q = "select * from category order by category";
		$qr = @pg_query($q);
		while ($r= pg_fetch_object($qr))
		{
			if ($category_id == $r->category_id)
			{
				echo "<option value=$r->category_id selected>$r->category</option>";
			}
			else
			{		
				echo "<option value=$r->category_id>$r->category</option>";
			}	
		}
		
		?>
          </select>
          </font></td>
        <td width="8%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          <?=lookUpAssoc('sort',array('Barcode'=>'barcode','Name'=>'stock','Stock Code'=>stock_code),$sort);?>
          </font> </td>
        <td width="5%" align="center" nowrap><input name="from_date" type="text" id="from_date" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $from_date;?>" size="8"> 
          <img src="../graphics/arrow_down.jpg" onClick="popUpCalendar(this, form1.from_date, 'mm/dd/yyyy')"> 
        </td>
        <td width="5%" align="center" nowrap><input name="to_date" type="text" id="to_date" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $to_date;?>" size="8">
          <img src="../graphics/arrow_down.jpg" onClick="popUpCalendar(this, form1.from_date, 'mm/dd/yyyy')"> </td>
        <td width="42%" nowrap>&nbsp; <input name="p1" type="submit" id="p1" value="Go"> 
        </td>
      </tr>
    </table>
    <table width="1%" border="0" align="center" cellpadding="2" cellspacing="1">
      <tr> 
        <td height="27"><table width="100%" border="0" cellspacing="0" cellpadding="2">
            <tr bgcolor="#000033"> 
              <td nowrap><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
                <img src="../graphics/bluelist.gif" width="16" height="17">Inventory 
                Preview</strong></font> </td>
            </tr>
          </table></td>
      </tr>
      <tr> 
        <td valign="top" bgcolor="#FFFFFF">
	   <textarea name="print_area" cols="120" rows="20"  wrap="off" readonly><?= $details1;?></textarea>
        </td>
      </tr>
    </table>
    <input name="p1" type="submit" id="p1" value="Print Draft" >
    <input name="p12" type="button" id="p12" value="Print"  onClick="printIframe(print_area)" >
  </div>
</form>

<iframe name="printit" id="printit" style="width:0px;height:0px;"></iframe>
