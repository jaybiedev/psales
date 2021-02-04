<script>
<!--
function vSelect(id,account)
{
		document.getElementById('account_id').value=id;
		document.getElementById('account').value=account;
		document.getElementById('acctLayer').style.display='none';
}

function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);

//-->
</script>
<title>Select Supplier Account</title>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<div id="acctLayer" style="position:absolute; width:500px; height:300px; z-index:1; left: 5%; top: 38%;"> 
  <table width="100%" height="100%" border="0" align="center" cellpadding="0" cellspacing="0" background="../graphics/table0_horizontal.PNG">
    <tr> 
      <td width="1%"><img src="../graphics/table0_upper_left.PNG" width="6" height="31"></td>
      <td width="48%"  height="1%"align="left" background="../graphics/table0_horizontal.PNG"><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><b>Select 
        Account </b></font></td>
      <td width="49%" height="1%" align="right" background="../graphics/table0_horizontal.PNG"><img src="../graphics/table_close.PNG" onClick="acctLayer.style.visibility='hidden'" ></td>
      <td width="2%" align="right"><img src="../graphics/table0_upper_right.PNG" width="6" height="30"></td>
    </tr>
    <tr valign="top" bgcolor="#A4B9DB"> 
      <td colspan="4"> 
       <div id="Layer2" style="position:virtual; width:100%; height:100%; z-index:2; left: 0; top: 0; overflow: auto;">
        <table width="99%" height="1%" border="0" align="center" cellpadding="2" cellspacing="1" bgcolor="#EFEFEF">
          <tr> 
            <td width="9%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
              <td width="20%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Code</font></strong></td>
              <td width="71%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Supplier 
                Account </font></strong></td>
          </tr>
          <?
  $q = "select * from account where (upper(account) like '".strtoupper($account)."%'  or account_code = '$account' ) and 
  					account_type_id='1' order by account_code,account offset 0 limit 25";

  $qr = @pg_query($q) or message(pg_errormessage());
  $ctr=0;
  while ($r = @pg_fetch_object($qr))
  {
  	$ctr++;
  ?>
          <tr bgcolor="#FFFFFF" onMouseOver="bgColor='#FFFFCC'" onMouseOut="bgColor='#FFFFFF'"> 
            <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
              <?= $ctr;?>
              .</font></td>
            <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href="javascript: vSelect(<?=$r->account_id;?>,'<?=addslashes($r->account);?>')"> 
              <?= $r->account_code;?>
              </a></font></td>
              <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href="javascript: vSelect(<?=$r->account_id;?>,'<?=addslashes($r->account);?>')">
                <?= $r->account;?>
                </a> </font></td>
          </tr>
          <?
  }
  ?>
        </table>
        </div>
      </td>
    </tr>
    <tr> 
      <td colspan="4" height="3"  background="../graphics/table0_vertical.PNG">
      </td>
    </tr>
  </table>
</div>
		<?

		if (@pg_num_rows($qr) == 0)
		{
			echo "<script>acctLayer.style.visibility='hidden';alert('Account on search NOT FOUND!...')</script>";
		}
		else
		{
			echo "<script>	hideElement('select',false);</script>";
		}
		?>
