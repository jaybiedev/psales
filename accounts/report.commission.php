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
if (empty($date)) 	$date=date('m/d/Y');	
?>	
<form name="form1" id="form1" method="post" action="">
    <div align="center">
        <table width="90%" border="0" cellspacing="1" cellpadding="0" bgcolor="#FFFFFF">
            <tr bgcolor="#EFEFEF"> 
	            <td nowrap style="font-weight:bold; font-family:Arial, Helvetica, sans-serif; font-size:13px; padding:3px;">COMMISSION</td>       
            </tr>
            <tr> 
                <td nowrap>
                <div style="display:inline-block;">
					Date <br />
                    <input name="date" type="text" id="date"  onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?=$date;?>" size="8">
		          	<img src="../graphics/dwn-arrow-grn.gif" onClick="popUpCalendar(this, form1.date, 'mm/dd/yyyy')">                	
                </div>                
                <input name="p1" type="submit" id="p1" value="Go">
                </td>
            </tr>
            <? if ($p1=='Go') { ?>
	     	<tr>
				<td>
                    <iframe id="JOframe" name="JOframe" frameborder="0" width="100%" height="500" src="print.report.commission.php?date=<?=$date?>">
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