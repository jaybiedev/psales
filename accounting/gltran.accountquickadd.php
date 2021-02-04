
<div id="LayerPayeeAdd" style="position:absolute; width:35%; height:115px; z-index:1; visibility: hidden; left: 50%; top: 25%;"> 
  <?
	if (!chkRights2("customers","mview",$admin->adminId)) 
	{
//		message("You don't have the privilege to add customer...");
	//	exit;
	}
?>
  <table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td bgcolor="#CCCCCC"><table width="98%" height="98" border="0" align="center" cellpadding="0" cellspacing="0" background="../graphics/table0_horizontal.PNG">
          <tr> 
            <td width="1%"><img src="../graphics/table0_upper_left.PNG" width="8" height="30"></td>
            <td width="49%" align="left" background="../graphics/table0_horizontal.PNG"><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><b>Payee 
              Quick Add</b></font></td>
            <td width="50%" align="right" background="../graphics/table0_horizontal.PNG">
			<img src="../graphics/table_close.PNG" width="21" height="21" onClick="LayerPayeeAdd.style.visibility='hidden'"></td>
            <td width="0%" align="right"><img src="../graphics/table0_upper_right.PNG" width="8" height="30"></td>
          </tr>
          <tr bgcolor="#A4B9DB"> 
            <td colspan="4"> <table width="99%" height="99%" border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#EFEFEF">
                <!--DWLayoutTable-->
                <tr> 
                  <td width="64" height="24" valign="top"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Payee</font></td>
                  <td width="275" valign="top"> <input name="newaccount" type="text" id="newaccount" value="<?= $newaccount;?>" size="40"> 
                    &nbsp; </td>
                </tr>
                <!--            <tr> 
              <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">As 
                of</font></td>
              <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
                <input name="date" type="text" id="date2"  onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?=$date;?>" size="10">
                <img src="../graphics/dwn-arrow-grn.gif" onclick="popUpCalendar(this, form1.date, 'mm/dd/yyyy')"> 
                </font></td>
            </tr>
-->
                <tr> 
                  <td height="18" valign="top" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Type</font></td>
                  <td valign="top"> <font size="2" face="Verdana, Arial, Helvetica, sans-serif">
                    <?= lookUpAssoc('account_type',array('Personal Account'=>'P','Employee'=>'E', 'Discount Card'=>'D','Membership Card'=>'M','Company Account'=>'C','Institutional'=>'I','Government'=>'G', 'Supplier'=>'S','Consignee'=>'N', 'Payee'=>'Y','Other'=>'O'), $aaccount['account_type']);?>
                    </font> </td>
                </tr>
                <tr> 
                  <td height="24" colspan="2"><table width="1%" border="0" cellpadding="1" cellspacing="1" bgcolor="#CCCCCC">
                      <tr bgcolor="#FFFFFF"> 
                        <td><img src="../graphics/save.jpg" width="57" height="15" onClick="f1.action='?p=gltran&p2=QuickAdd';f1.submit()"></td>
                        <td><img src="../graphics/cancel.jpg" width="77" height="20" onClick="LayerBankAdd.style.visibility='hidden'"></td>
                      </tr>
                    </table></td>
                </tr>
              </table></td>
          </tr>
          <tr> 
            <td colspan="4" height="3"  background="../graphics/table0_vertical.PNG"></td>
          </tr>
        </table></td>
    </tr>
  </table>
</div>
