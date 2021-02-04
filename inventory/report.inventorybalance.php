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
if ($from_date == '') $from_date=date('m/d/Y');	
?>	
<form name="form1" method="post" action="">
  <div align="center">
    <table width="90%" border="0" cellspacing="1" cellpadding="0" bgcolor="#FFFFFF">
      <tr bgcolor="#EFEFEF" background="../graphics/table0_horizontal.PNG"> 
        <td height="20" colspan="8" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          &nbsp;:: <strong>Inventory Balances </strong>::</font></td>
      </tr>
      <tr bgcolor="#EFEFEF"> 
        <td width="20%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          <font color="#CC0000">From Supplier</font> </font></td>
        <td width="40%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          <font color="#CC0000">To Supplier</font> </font></td>
        <td width="10%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">Category 
          From </font></td>
        <td width="11%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          To</font></td>
        <td width="3%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          Sort</font></td>
        <td width="5%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">IncZero</font></td>
        <td width="8%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
          </font><font color="#000000">&nbsp;</font> <font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">As 
          of</font></td>
        <td width="36%" nowrap>&nbsp;</td>
      </tr>
      <tr> 
        <td style='font-family:Verdana; font-size:11px;'>
          <!-- From Supplier -->
          <select name="from_supplier_code" id="from_supplier_code"   style="border: #CCCCCC 1px solid; width:180px">
            <option value=''>Select Supplier --</option>
            <?
        		foreach ($aSUPPLIER as $stemp)
        		{
        			if ($stemp['account_code'] == $from_supplier_code && !empty($from_supplier_code))
        			{
        				echo "<option value='".$stemp['account_code']."' selected>".substr($stemp['account_code'],0,6)." ".$stemp['account']."</option>";
        			}
        			else
        			{
        				echo "<option value='".$stemp['account_code']."'>".substr($stemp['account_code'],0,6)." ".$stemp['account']."</option>";
        			}
        		}

      	  ?>
          </select>                    
        </td>
        <td style='font-family:Verdana; font-size:11px;'>
          <!-- To Supplier -->
          <select name="to_supplier_code" id="to_supplier_code"   style="border: #CCCCCC 1px solid; width:180px">
            <option value=''>Select Supplier --</option>
            <?
            foreach ($aSUPPLIER as $stemp)
            {
              if ($stemp['account_code'] == $to_supplier_code && !empty($to_supplier_code))
              {
                echo "<option value='".$stemp['account_code']."' selected>".substr($stemp['account_code'],0,6)." ".$stemp['account']."</option>";
              }
              else
              {
                echo "<option value='".$stemp['account_code']."'>".substr($stemp['account_code'],0,6)." ".$stemp['account']."</option>";
              }
            }

          ?>
          </select>          
          <input type='checkbox' id='include_concessionaire_check_box' name='include_concessionaire_check_box' 
            <? if( $include_concessionaire_check_box ) echo "checked" ?> value='1'>
          <label for='include_concessionaire_check_box'>Include Concessionaire</label>
        </td>
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
        <td width="11%" nowrap><select name="to_category_id"   style="border: #CCCCCC 1px solid; width:180px">
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
          <?=lookUpAssoc('sort',array('Barcode'=>'barcode','Name'=>'stock','Stock Code'=>stock_code),$sort);?>
          </font> </td>
        <td width="5%" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          <?=lookUpAssoc('incZero',array('No'=>'N','Yes'=>'Y'),$incZero);?>
          </font></td>
        <td width="8%" nowrap align="center"><input name="from_date" type="text" id="from_date" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $from_date;?>" size="8"> 
          <img src="../graphics/arrow_down.jpg" onClick="popUpCalendar(this, form1.from_date, 'mm/dd/yyyy')"> 
        </td>
        <td width="36%" nowrap>&nbsp; <input name="p1" type="submit" id="p1" value="Go"> 
        </td>
      </tr>
      <tr bgcolor="#DADADA"> 
        <td colspan="8"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Report 
          Preview</strong></font></td>
      </tr>
      <!--<tr> 
        <td colspan="7"><textarea name="print_area" cols="110" rows="20"  wrap="off" readonly><?= $details1;?></textarea></td>
      </tr> -->
    </table>
    <!--<input name="p1" type="submit" id="p1" value="Print Draft" >
    <input name="p12" type="button" id="p12" value="Print"  onClick="printIframe(print_area)" > -->
    
    <? if ($p1=='Go') { ?>	
        <iframe id="JOframe" name="JOframe" style="background-color:#FFF; width:100%;" frameborder="0" height="500" src="print_report.inventorybalance.php?from_supplier_code=<?=$from_supplier_code?>&
          to_supplier_code=<?=$to_supplier_code?>&from_category_id=<?=$from_category_id?>&to_category_id=<?=$to_category_id?>&sort=<?=$sort?>&from_date=<?=$from_date?>
        	&incZero=<?=$incZero?>&include_concessionaire_check_box=<?=$include_concessionaire_check_box?>">
        </iframe>
        <input type="button" value="Print" onclick="printIframe('JOframe');" />
    <? } ?>
    
  </div>
</form>

<iframe name="printit" id="printit" style="width:0px;height:0px;"></iframe>
