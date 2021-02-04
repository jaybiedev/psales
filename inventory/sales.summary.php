<?

require_once("../lib/lib.salvio.php");

if( empty($category_year) ) $category_year = date("Y");
if( empty($year) ) $year = date("Y");


if ($from_category_id != '') $from_category_code = trim(lookUpTableReturnValue('x','category','category_id','category_code',$from_category_id));
if ($to_category_id != '') $to_category_code = trim(lookUpTableReturnValue('x','category','category_id','category_code',$to_category_id));

$arr = array();

$year = date("Y",strtotime($from_date));
$from_date = lib::mdy2ymd($from_date);
$to_date = lib::mdy2ymd($to_date);	
if( $p1 == "Go" ){

	$sql = "
		delete from 
			sales
		using 
			public.stock as s,
			category as c
		where 	
			c.category_id = s.category_id
		and s.stock_id = sales.stock_id
		and date >= '$from_date'
		and date <= '$to_date'
		and substr(category_code,'1',length('$from_category_code')) >= '$from_category_code'
		and substr(category_code,'1',length('$to_category_code')) <= '$to_category_code'
	";

	pg_query($sql);

	$sql = "
		insert into sales
		select
			h.date, d.stock_id, sum(qty) as quantity
		from
			e".$year.".sales_header as h
			inner join e".$year.".sales_detail as d on h.sales_header_id = d.sales_header_id
			inner join public.stock as s on s.stock_id = d.stock_id
			inner join category as c on c.category_id = s.category_id
		where 
			h.status != 'V'
		and date >= '$from_date'
		and date <= '$to_date'
		and substr(category_code,'1',length('$from_category_code')) >= '$from_category_code'
		and substr(category_code,'1',length('$to_category_code')) <= '$to_category_code'
		GROUP BY h.date,d.stock_id
	";

	
	pg_query($sql);

} else if ( $p1 == 'Search' ) {
	$arr = lib::getArrayDetails("
		select
			sales.date, stock, quantity, category, barcode
		from
			sales
			inner join public.stock as s on s.stock_id = sales.stock_id
			inner join category as c on c.category_id = s.category_id
		where
			date >= '$from_date'
			and date <= '$to_date'
			and substr(category_code,'1',length('$from_category_code')) >= '$from_category_code'
			and substr(category_code,'1',length('$to_category_code')) <= '$to_category_code'
		order by date, stock
	");
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
<style type="text/css">
	.table {
		width: 90%;
		border-collapse: collapse;
		font-family: Arial;
	}
	.table thead th {
		background-color: #D2DCDF;
		font-size: 11px;
		padding: 3px;
	}
	.table tbody td {
		background: white;
		padding: 3px 5px;
		font-size: 11px;
		text-align: left;
	}
</style>
<form name="f1" id="f1" method="post" action="">
  <div align="center">
    <table width="90%" border="0" cellspacing="1" cellpadding="0" bgcolor="#FFFFFF">
      <tr bgcolor="#EFEFEF" background="../graphics/table0_horizontal.PNG"> 
        <td height="20" colspan="7" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          &nbsp;:: <strong>SUMMARIZE SALES</strong>::</font></td>
      </tr>
      <tr bgcolor="#EFEFEF">         
        <td width="10%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">Category 
          From </font></td>
        <td width="11%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          To</font></td>        
        <td width="5%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">From Date</font></td>        
        <td width="5%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">To Date</font></td>        
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
          <input name="from_date" type="text" class="altText" id="from_date" tabindex="8"  value="<?= ymd2mdy($from_date);?>" size="10" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')"  onKeypress="if(event.keyCode==13) {return false;}">
        <img src="../graphics/dwn-arrow-grn.gif" onClick="popUpCalendar(this, f1.from_date, 'mm/dd/yyyy')"> 
          </font> </td>        

         <td width="3%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          <input name="to_date" type="text" class="altText" id="to_date" tabindex="8"  value="<?= ymd2mdy($to_date);?>" size="10" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')"  onKeypress="if(event.keyCode==13) {return false;}">
        <img src="../graphics/dwn-arrow-grn.gif" onClick="popUpCalendar(this, f1.to_date, 'mm/dd/yyyy')"> 
          </font> </td>        
        <td width="36%" nowrap>
        	<input name="p1" type="submit" id="p1" value="Go"> 
        	<input name="p1" type="submit" id="p1" value="Search"> 
        </td>
      </tr>
      
    </table>
    <!--<input name="p1" type="submit" id="p1" value="Print Draft" >
    <input name="p12" type="button" id="p12" value="Print"  onClick="printIframe(print_area)" > -->
        
  </div>

  <div align="center">
    <table width="90%" border="0" cellspacing="1" cellpadding="0" bgcolor="#FFFFFF">

    <tr bgcolor="#EFEFEF" background="../graphics/table0_horizontal.PNG"> 
        <td height="20" colspan="7" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          &nbsp;:: <strong>Summarized Sales</strong>::</font></td>
      </tr>
      
      
      <!--<tr> 
        <td colspan="7"><textarea name="print_area" cols="110" rows="20"  wrap="off" readonly><?= $details1;?></textarea></td>
      </tr> -->
    </table>
    <table class="table">
    	<thead>
    		<tr>
    			<th style="width:5%;">#</th>
    			<th style="width: 10%;">DATE</th>
			<th>SKU</th>
    			<th>STOCK</th>
    			<th>CATEGORY</th>
    			<th>QTY</th>
    		</tr>
    	</thead>
    	<tbody>
    		<? foreach ( $arr as $i => $r ) { ?>
    		<tr>
    			<td><?=++$i?></td>
    			<td><?= lib::ymd2mdy($r['date']) ?></td>
    			<td><?= $r['barcode'] ?></td>
    			<td><?= $r['stock'] ?></td>
    			<td><?= $r['category'] ?></td>
    			<td><?= $r['quantity'] ?></td>
    		</tr>
    		<? } ?>
    	</tbody>

    </table>
    
    
  </div>
</form>

<iframe name="printit" id="printit" style="width:0px;height:0px;"></iframe>
