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
if (empty($from_date)) $from_date=date('m/d/Y');	
?>	
<form name="form1" method="post" action="">
    <div align="center">
        <table width="90%" border="0" cellspacing="1" cellpadding="0" bgcolor="#FFFFFF">
            <tr bgcolor="#EFEFEF"> 
	            <td nowrap style="font-weight:bold; font-family:Arial, Helvetica, sans-serif; font-size:13px; padding:3px;">Price Monitoring</td>       
            </tr>
            <tr> 
                <td width="3%" nowrap>
                <select name="category_id" style="width:180" >
                <option value=''>All Categories:</option>
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
                <input name="p1" type="submit" id="p1" value="Go">
                </td>
            </tr>
            <? if ($p1=='Go') { ?>
	     	<tr>
				<td>
                    <iframe id="JOframe" name="JOframe" frameborder="0" width="100%" height="500" src="print.report.pricemonitor.php?category_id=<?=$category_id?>">
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