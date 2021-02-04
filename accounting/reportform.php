<STYLE TYPE="text/css">
<!--
	.grid {
	background-color: #fffff;
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
	
	.altNum {
	background-color: #FFFFFF;
	font-family: verdana;
	border: #CCCCCC 1px solid;
	font-size: 12px;
	color: #000000;
	text-align:right
	} 
	
	TextArea {
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
<?
		if (!isset($_SESSION['aRF'])) 
		{ 
   			$_SESSION['aRF'] = array(); 
			$aRF = null;
			$aRF = array();
		} 
		if (!isset($_SESSION['aRFD'])) 
		{ 
   			$_SESSION['aRFD'] = array(); 
			$aRFD = null;
			$aRFD = array();
		} 


if ($p1 == 'New')
{
	$aRF = null;
	$aRF = array();
	$aRFD = null;
	$aRFD = array();
}
elseif ($p1 == 'Load' && $id != '')
{
	$aRF = null;
	$aRF = array();
	$aRFD = null;
	$aRFD = array();

	$q = "select * from rf_header where rf_header_id = '$id'";
	$qr = @mysql_query($q) or message(mysql_error());
	$r = @mysql_fetch_assoc($qr);
	$aRF = $r;
	
	$q = "select * from rf_detail where rf_header_id = '".$aRF['rf_header_id']."' order by line ";
	$qr = @mysql_query($q) or message(mysql_error());
	while ($r = @mysql_fetch_assoc($qr))
	{
		$qq = "select * from gchart where gchart_id = '".$r['gchart_id']."'";
		$qqr = @mysql_query($qq) or message(mysql_error());
		$rr = @mysql_fetch_assoc($qqr);
		
		$r['gchart'] = $rr['gchart'];
		$aRFD[] = $r;
	}
	
}
?>
<form name="f1"  id="f1" method="post" action="">
  <table width="90%" border="0" align="center" cellpadding="0" cellspacing="1" >
    <tr background="../graphics/table_horizontal.PNG"> 
      <td colspan="8"><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Report 
        Form</strong></font></td>
    </tr>
    <tr bgcolor="#EFEFEF"> 
      <td colspan="8"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Report 
        Title 
        <input name="reportform" type="text" class="altText" id="reportform" value="<?= $aRF['reportform'];?>" size="40">
        Report Id 
        <input name="rf_header_id" type="text" class="altNum" id="rf_header_id"  readOnly value="<?= $aRF['rf_header_id'];?>" size="8">
        <input type="button" class="altBtn" name="Submit22" value="Browse" onClick="window.location='?p=reportform.browse'">
        </font></td>
    </tr>
    <tr bgcolor="#EFEFEF"> 
      <td width="21%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Account 
        <input name="gchart_id" type="hidden" class="altText" id="gchart_id" size="15">
        <br>
        <input name="code" type="text" class="altText" id="code" size="15"  onKeypress="if(event.keyCode==13 && code.value=='') {document.getElementById('descr').focus(); return false;}else if (event.keyCode==13 ){document.getElementById('Search').click();return false;}">
        <input type="button" class="altBtn" name="Search"  id="Search" value="Search"  onClick="wait('Please wait...');xajax_searchGchartReportForm(xajax.getFormValues('f1'));">
        </font></td>
      <td width="34%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">descrription<br>
        <input name="descr" type="text" class="altText" id="descr" size="40" maxlength="40">
        </font></td>
      <td width="2%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Type 
        <br>
        <?= lookUpAssoc('type',array('Title'=>'T','Detail'=>'D','Result'=>'R','Line'=>'1L','DLine'=>'2L'),$type);?>
        </font></td>
      <td width="2%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        Use <br>
        <input name="var2" type="text" class="altText" id="var2" size="4">
        </font></td>
      <td width="4%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Operation<br>
        <?= lookUpAssoc('operation',array('None'=>'N','Add'=>'A','Substract'=>'S'),$operation);?>
        </font></td>
      <td width="4%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Use<br>
        <input name="var1" type="text" class="altText" id="var1" size="4">
        </font></td>
      <td width="3%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Tab<br>
        <input name="tab" type="text" class="altText" id="tab" size="4">
        </font></td>
      <td width="30%" valign="bottom"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;Line<br>
        <input name="line" type="text" class="altText" id="line" size="4">
        <input type="button" class="altBtn" name="Submit" value="Ok"  onClick="wait('Please wait...');xajax_okReportForm(xajax.getFormValues('f1'));">
        </font></td>
    </tr>
    <tr bgcolor="#DADADA"> 
      <td colspan="8"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Report 
        Layout</font></td>
    </tr>
    <tr valign="top" bgcolor="#EFEFEF"> 
      <td height="350px" colspan="8"><div id="gridLayer" name="gridLayer" style="position:virtual; width:100%; height:100%; z-index:1; overflow: scroll;"></div></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="8" ><table width="1%" border="0" cellpadding="1" cellspacing="1" bgcolor="#CCCCCC">
          <tr bgcolor="#FFFFFF"> 
            <td nowrap width="3%"> <input type="image" src="../graphics/save.jpg" alt="Save This Claim Form" name="Save" width="57" height="15" border="0" id="Save" onClick="wait('Please wait. Saving Report Form..');xajax_saveReportForm(xajax.getFormValues('f1'));return false;" tabIndex="99"> 
            </td>
            <td nowrap width="46%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              <img src="../graphics/print.jpg" alt="Print This  Form"  onClick="window.print()" accesskey="P"> 
              </strong></font></td>
            <td nowrap width="25%"> <input type='image' name="New" id="New" onClick="f1.action='?p=account&p1=New';f1.submit();"  src="../graphics/new.jpg" alt="New Claim Form" width="63" height="20" accesskey="N"> 
            </td>
          </tr>
        </table></td>
    </tr>
  </table>
</form>
<script>xajax_loadReportForm()</script>
<?
include_once('searchgchart.php');
?>