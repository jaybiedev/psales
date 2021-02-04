<?

require_once("../lib/lib.salvio.php");

if( empty($year) ) $year = date("Y");

if ($from_category_id != '') $from_category_code = trim(lookUpTableReturnValue('x','category','category_id','category_code',$from_category_id));
if ($to_category_id != '') $to_category_code = trim(lookUpTableReturnValue('x','category','category_id','category_code',$to_category_id));


if( $p1 == "Go" ){
	$year_minus_1 = $year - 1;

	$sql = "
		select
			d.stock_id, sum(qty) as quantity
		from
			e".$year_minus_1.".sales_header as h
			inner join e".$year_minus_1.".sales_detail as d on h.sales_header_id = d.sales_header_id
			inner join public.stock as s on s.stock_id = d.stock_id
			inner join public.category as c on c.category_id = s.category_id
		where 
			h.status != 'V'
		and substr(category_code,'1',length('$from_category_code')) >= '$from_category_code'
		and substr(category_code,'1',length('$to_category_code')) <= '$to_category_code'
		GROUP BY d.stock_id
	";

	set_time_limit(0);
	$result = pg_query($sql);
	$arr = array();
	while( $r = pg_fetch_assoc($result) ){
		$arr[] = $r;
	}

	/*echo "<pre>";
	print_r($arr);
	echo "</pre>";*/

	if( count($arr) ){
		foreach ($arr as $r) {
			$sql = "
				update public.sales_forwarded
				set quantity = '$r[quantity]'
				where 
					stock_id = '$r[stock_id]'
				and year = '$year_minus_1';
			";

			pg_query($sql);

			$sql = "

				insert into public.sales_forwarded (stock_id,year,quantity)
				select '$r[stock_id]','$year_minus_1','$r[quantity]' where not EXISTS 
				( select 1 from public.sales_forwarded where stock_id = '$r[stock_id]' and year = '$year_minus_1' );
			";

			pg_query($sql);

			set_time_limit(30);

		}

		$msg = message("$from_category_code to $to_category_code Successfuly forwarded.");
	} else {
		$msg = message("No sales to forwared.");
	}

}

if( $p1 == "Forward Barcode" ){	
	$year_minus_1 = $year - 1;

	/*get stock_id*/
	$stock_id = lib::getAttribute('stock','barcode',$barcode,'stock_id');

	if( !empty($stock_id) ){
		$sql = "
			select
				d.stock_id, sum(qty) as quantity
			from
				e".$year_minus_1.".sales_header as h
				inner join e".$year_minus_1.".sales_detail as d on h.sales_header_id = d.sales_header_id
				inner join public.stock as s on s.stock_id = d.stock_id
			where 
				h.status != 'V'
			and s.stock_id = '$stock_id'
			GROUP BY d.stock_id
		";

		set_time_limit(0);
		$result = pg_query($sql);
		$arr = array();
		while( $r = pg_fetch_assoc($result) ){
			$arr[] = $r;
		}

		/*echo "<pre>";
		print_r($arr);
		echo "</pre>";*/

		if( count($arr) ){
			$quantity = 0;

			/*has only 1 loop*/
			foreach ($arr as $r) {
				$sql = "
					update public.sales_forwarded
					set quantity = '$r[quantity]'
					where 
						stock_id = '$r[stock_id]'
					and year = '$year_minus_1';
				";
				 $quantity = $r['quantity'];

				pg_query($sql);

				$sql = "

					insert into public.sales_forwarded (stock_id,year,quantity)
					select '$r[stock_id]','$year_minus_1','$r[quantity]' where not EXISTS 
					( select 1 from public.sales_forwarded where stock_id = '$r[stock_id]' and year = '$year_minus_1' );
				";

				pg_query($sql);

				set_time_limit(30);

			}

			$msg = message("$barcode Successfuly forwarded $quantity quantity.");
		} else {
			$msg = message("No Sales to Forward.");
		}
	} else {
		$msg = message("Barcode Does not exist.");		
	}
}
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
?>	
<? if(isset($msg)) echo $msg ?>
<form name="form1" method="post" action="">
  <div align="center">
    <table width="90%" border="0" cellspacing="1" cellpadding="0" bgcolor="#FFFFFF">
      <tr bgcolor="#EFEFEF" background="../graphics/table0_horizontal.PNG"> 
        <td height="20" colspan="7" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          &nbsp;:: <strong>Sales Forwarding Per Category </strong>::</font></td>
      </tr>
      <tr bgcolor="#EFEFEF">         
        <td width="10%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">Category 
          From </font></td>
        <td width="11%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          To</font></td>        
        <td width="5%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">To Year</font></td>        
        <td width="36%" nowrap>&nbsp;</td>
      </tr>
      <tr>         
        <td width="10%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          <select name="from_category_id"   style="border: #CCCCCC 1px solid; width:180px">
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
			if ($ctemp['category_id'] == $from_category_id)
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
        <td width="11%" nowrap>
        	<select name="to_category_id"   style="border: #CCCCCC 1px solid; width:180px">
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
				if ($ctemp['category_id'] == $to_category_id)
				{
					echo "<option value=".$ctemp['category_id']." selected>".$category_code." ".$ctemp['category']."</option>";
				}
				else
				{
					echo "<option value=".$ctemp['category_id']." >".$category_code." ".$ctemp['category']."</option>";
				}
			}
		  ?>
          </select></td>         
        <td width="3%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          <input type="text" class="textbox" name="year" style="border:1px solid #c0c0c0;" autocomplete="on" value="<?=$year?>">
          </font> </td>        
        <td width="36%" nowrap>&nbsp; <input name="p1" type="submit" id="p1" value="Go"> 
        </td>
      </tr>
      
      <!--<tr> 
        <td colspan="7"><textarea name="print_area" cols="110" rows="20"  wrap="off" readonly><?= $details1;?></textarea></td>
      </tr> -->
    </table>
    <!--<input name="p1" type="submit" id="p1" value="Print Draft" >
    <input name="p12" type="button" id="p12" value="Print"  onClick="printIframe(print_area)" > -->
        
  </div>

  <div align="center">
    <table width="90%" border="0" cellspacing="1" cellpadding="0" bgcolor="#FFFFFF">

    <tr bgcolor="#EFEFEF" background="../graphics/table0_horizontal.PNG"> 
        <td height="20" colspan="7" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          &nbsp;:: <strong>Sales Forwarding Per Barcode </strong>::</font></td>
      </tr>
      <tr bgcolor="#EFEFEF">               	
        <td width="10%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">
        	Barcode</font></td>
        
        <td width="5%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">To Year</font></td>        
        <td width="36%" nowrap>&nbsp;</td>
      </tr>
      <tr>         
        <td width="10%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        	<input type="text" class="textbox" name="barcode" style="border:1px solid #c0c0c0;" value="<?=$barcode?>">
          </font></td>        
        <td width="3%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          <input type="text" class="textbox" name="year" style="border:1px solid #c0c0c0;" autocomplete="on" value="<?=$year?>">
          </font> </td>        
        <td width="36%" nowrap>&nbsp; <input name="p1" type="submit" id="p1" value="Forward Barcode"> 
        </td>
      </tr>
      
      <!--<tr> 
        <td colspan="7"><textarea name="print_area" cols="110" rows="20"  wrap="off" readonly><?= $details1;?></textarea></td>
      </tr> -->
    </table>
    <!--<input name="p1" type="submit" id="p1" value="Print Draft" >
    <input name="p12" type="button" id="p12" value="Print"  onClick="printIframe(print_area)" > -->
        
  </div>
</form>

<iframe name="printit" id="printit" style="width:0px;height:0px;"></iframe>
