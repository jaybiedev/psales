<style type="text/css"> 
#form1 *{
	font-size:11px; font-family:Arial, Helvetica, sans-serif;	
}

</style>
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
if (empty($from_date)) 	$from_date=date('m/d/Y');	
if (empty($to_date)) 	$to_date=date('m/d/Y');	
?>	
<form name="form1" id="form1" method="post" action="">
    <div align="center">
        <table width="90%" border="0" cellspacing="1" cellpadding="0" bgcolor="#FFFFFF">
            <tr bgcolor="#EFEFEF"> 
	            <td nowrap style="font-weight:bold; font-family:Arial, Helvetica, sans-serif; font-size:13px; padding:3px;">SRR HISTORY DETAIL</td>       
            </tr>
            <tr> 
                <td nowrap>
                <div style="display:inline-block;">
					From <br />
                    <input name="from_date" type="text" id="from_date"  onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?=$from_date;?>" size="8">
		          	<img src="../graphics/dwn-arrow-grn.gif" onClick="popUpCalendar(this, form1.from_date, 'mm/dd/yyyy')">                	
                </div>
                <div style="display:inline-block;">
					To <br />
                    <input name="to_date" type="text" id="to_date"  onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?=$to_date;?>" size="8">
		          	<img src="../graphics/dwn-arrow-grn.gif" onClick="popUpCalendar(this, form1.to_date, 'mm/dd/yyyy')">                	
                </div>
                <div style="display:inline-block;">
                	<select name='account_id' id='account_id' style="width:250px"  onKeypress="if(event.keyCode==13) {document.getElementById('date_from').focus();return false;}">
                        <option value=''>--Select Supplier--</option>
						<?
                        foreach ($aSUPPLIER as $stemp){
                            if ($stemp['account_id'] == $account_id){
                                echo "<option value=".$stemp['account_id']." selected>".substr($stemp['account_code'],0,6)." ".$stemp['account']."</option>";
                            } else {
                                echo "<option value=".$stemp['account_id'].">".substr($stemp['account_code'],0,6)." ".$stemp['account']."</option>";
                            }
                        }
                        ?>
                	</select>
                </div>
                
                <input name="p1" type="submit" id="p1" value="Go">
                </td>
            </tr>
            <? if ($p1=='Go') { ?>
	     	<tr>
				<td>
                    <iframe id="JOframe" name="JOframe" frameborder="0" width="100%" height="500" src="print.report.rr_history_detail.php?account_id=<?=$account_id?>&from_date=<?=$from_date?>&to_date=<?=$to_date?>">
                    </iframe>
                    <div style="text-align:center;">
	                    <input type="button" value="Print" onclick="printIframe('JOframe');" />
                   	</div>
              	</td>
            </tr>
            <? } ?>
        </table>
    </div>
</form>