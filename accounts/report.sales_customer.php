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

?>	
<form name="form1" id="form1" method="post" action="">
    <div align="center">
        <table width="90%" border="0" cellspacing="1" cellpadding="0" bgcolor="#FFFFFF">
            <tr bgcolor="#EFEFEF"> 
	            <td nowrap style="font-weight:bold; font-family:Arial, Helvetica, sans-serif; font-size:13px; padding:3px;">SALES BY CUSTOMER</td>       
            </tr>
            <tr> 
                <td nowrap>
                    <div style="display:inline-block;">
    					From Date <br />
                        <input name="from_date" type="text" id="from_date"  onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?=$from_date;?>" size="8">
    		          	<img src="../graphics/dwn-arrow-grn.gif" onClick="popUpCalendar(this, form1.from_date, 'mm/dd/yyyy')">                	
                    </div>                
                    <div style="display:inline-block;">
                        To Date <br />
                        <input name="to_date" type="text" id="to_date"  onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?=$to_date;?>" size="8">
                        <img src="../graphics/dwn-arrow-grn.gif" onClick="popUpCalendar(this, form1.to_date, 'mm/dd/yyyy')">                  
                    </div>            
                    <div style="display:inline-block;">
                        Account Classification <br>
                        <select name='account_class_id' style="width:240px">
                            <option value="">All Accounts</option>
                            <?
                            $q = "select * from account_class where enable='Y' order by account_class";
                            $qr = @pg_query($q);
                            while ($r = @pg_fetch_object($qr))
                            {
                                if ($r->account_class_id == $account_class_id)
                                {
                                    echo "<option value=$r->account_class_id selected>$r->account_class</option>";
                                }
                                else
                                {
                                    echo "<option value=$r->account_class_id >$r->account_class</option>";
                                }
                            }
                            ?>
                      </select>
                    </div>    
                    <div style="display:inline-block;">
                        Account Type <br>
                        <select name='account_type_id'>
                            <option>All Types</option>
                            <?
                            $q = "select * from account_type where enable='Y' order by account_type";
                            $qr = @pg_query($q);
                            while ($r = @pg_fetch_object($qr))
                            {
                                if ($r->account_type_id == $account_type_id)
                                {
                                    echo "<option value=$r->account_type_id selected>$r->account_type</option>";
                                }
                                else
                                {
                                    echo "<option value=$r->account_type_id>$r->account_type</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div style="display:inline-block;">
                        Top <br>
                        <input name="top" type="text" id="top" value="<?= $top;?>" size="5"> 
                    </div>
                <input name="p1" type="submit" id="p1" value="Go">
                </td>
            </tr>
            <? if ($p1=='Go') { ?>
	     	<tr>
				<td>
                    <iframe id="JOframe" name="JOframe" frameborder="0" width="100%" height="500" src="print.report.sales_customer.php?
                        from_date=<?=$from_date?>&
                        to_date=<?=$to_date?>&
                        account_class_id=<?=$account_class_id?>&
                        account_type_id=<?=$account_type_id?>&
                        top=<?=$top?>
                    ">
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