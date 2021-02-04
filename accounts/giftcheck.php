<STYLE TYPE="text/css">
<!--
	.grid {
	background-color: #efefef;
	font-family: verdana;
	border: #CCCCCC 1px solid;
	font-size: 12px;
	color: #000000
	} 

	.altText {
	background-color: #FFFFFF;
	font-family: verdana;
	border: #CCCCCC 1px solid;
	font-size: 12px;
	color: #000000
	} 
	.autocomplete {
	font-family: verdana;
	border: #CCCCCC 1px solid;
	font-size: 10px;
	color: #000000;
	
	} 
	
	.altNum {
	background-color: #FFFFFF;
	font-family: verdana;
	border: #CCCCCC 1px solid;
	font-size: 12px;
	color: #000000;
	text-align:right
	} 
	
	.altTextArea {
	background-color: #FFFFFF;
	font-family: verdana;
	border: #CCCCCC 1px solid;
	font-size: 12px;
	color: #000000
	} 
	
	SELECT {
	background-color: #FFFFFF;
	font-family: verdana;
	border: #CCCCCC 1px solid;
	font-size: 10px;
	margin:0px;
	color: #000000
	} 			

	.altBtn {
	background-color: #CFCFCF;
	font-family: verdana;
	font-size: 11px;
	padding: 1px;
	margin: 0px;
	color: #1F016D
	} 
-->
</STYLE>
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

<form name="f1" id="f1" method="post" action="" style="margin:10px">
  <table width="90%%" border="0" cellspacing="1" cellpadding="0">
    <tr background="../graphics/table_horizontal.PNG"> 
      <td height="25" colspan="4"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        &nbsp; <img src="../graphics/arrowgrn.gif"> <strong><font color="#FFFFCC">Gift 
        Check Encoding</font></strong></font></td>
    </tr>
    <tr> 
      <td colspan="4"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Search 
        <input name="xSearch" type="text" id="xSearch" value="<?= $xSearch;?>"   class="altText" >
        <?= lookUpAssoc('searchby',array('GC No.'=>'giftcheck','Name'=>'name','amount'=>'amount','Expiry'=>'date_expire','Encoded'=>'date','Record Id'=>'giftcheck_id'),$searchby);?>
        Show</font>
        <input name="date" type="text" id="date"  onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $date;?>" size="10" class="altText">
       <img src="../graphics/dwn-arrow-grn.gif" onClick="popUpCalendar(this, f1.date, 'mm/dd/yyyy')"></font>
        <input name="p1" type="submit"  class="altBtn" id="p1" value="Search">
        </font></td>
    </tr>
    <tr> 
      <td colspan="4"><hr size='1' color="#0066CC"></td>
    </tr>
    <tr> 
      <td width="9%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">GC 
        No.(Id 
        <input name="giftcheck_id" type="text"   style="text-align:right; background:none; border:none" id="giftcheck_id" size="5">
        ) <br>
        <input name="giftcheck" type="text"  class="altText" id="giftcheck" size="20"  onKeypress="if(event.keyCode==13) {document.getElementById('name').focus();return false;}">
        <input name="p12" type="button" id="p13" value="+"  onClick="xajax_gcGenerate(xajax.getFormValues('f1'))"  class="altBtn"  onmouseover="showToolTip(event,'Click To Generate Gift Check Serial...');return false" onmouseout="hideToolTip()">
       </font></td>
      <td width="13%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Name<br>
        <input name="name" type="text"   class="altText" id="name" size="30"  onKeypress="if(event.keyCode==13) {document.getElementById('amount').focus();return false;}">
        </font></td>
      <td width="11%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Amount<br>
        <input name="amount" type="text"   class="altNum" id="amount" size="12"  onKeypress="if(event.keyCode==13) {document.getElementById('date_expire').focus();return false;}">
        </font></td>
      <td width="67%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Date 
        Expire<br>
        </font><font size="3" color="#000000"><font face="Verdana, Arial, Helvetica, sans-serif"><font size="2"> 
        <input name="date_expire" type="text" id="date_expire"  onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $date_expire;?>" size="10" class="altText"  onKeypress="if(event.keyCode==13) {document.getElementById('ok').focus();return false;}">
        </font><font size="2" color="#000000"><img src="../graphics/dwn-arrow-grn.gif" onClick="popUpCalendar(this, f1.date_expire, 'mm/dd/yyyy')"></font><font size="2"> 
        </font></font></font><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="p1" type="button" class="altBtn" id="ok" value="Ok"  onClick="wait('Please wait. Saving Gift Check Entry...');xajax_saveGiftCheck(xajax.getFormValues('f1'));return false;"  accesskey="O">
        </font></td>
    </tr>
  </table>
  <table width="90%%" border="0" cellpadding="0" cellspacing="1" bgcolor="#FFFFFF">
    <tr background="../graphics/table_horizontal.PNG"> 
      <td width="5%" align="center"><font color="#EFEFEF" size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></td>
      <td width="12%" align="center"><font color="#EFEFEF" size="2" face="Verdana, Arial, Helvetica, sans-serif">GC 
        No.</font></td>
      <td width="30%"><font color="#EFEFEF" size="2" face="Verdana, Arial, Helvetica, sans-serif">Name</font></td>
      <td width="15%" align="center"><font color="#EFEFEF" size="2" face="Verdana, Arial, Helvetica, sans-serif">Amount</font></td>
      <td width="13%" align="center"><font color="#EFEFEF" size="2" face="Verdana, Arial, Helvetica, sans-serif">Expiry</font></td>
      <td width="30%"><font color="#EFEFEF" size="2" face="Verdana, Arial, Helvetica, sans-serif">Encoded</font></td>
      <td width="5%"><font color="#EFEFEF" size="2" face="Verdana, Arial, Helvetica, sans-serif">Used</font></td>
    </tr>
    <tr height="300px" valign="top"> 
      <td colspan="7"  height="300px"  valign="top">
	  <div id="gridLayer"  name="gridLayer"  style="position:virtual; width:100%; height:100%; z-index:1; overflow: scroll;"></div>
        </td>
    </tr>
  </table>
</form>
<script>
	xajax_gcLoad(xajax.getFormValues('f1'));
</script>