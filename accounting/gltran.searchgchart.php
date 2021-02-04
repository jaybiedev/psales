<script>
<!--
function vSelect(id,account, code)
{
		document.getElementById('gchart_id').value=id;
		document.getElementById('searchkey').value=code;
		document.getElementById('gchart').value=account;
		document.getElementById('acctLayer').style.visibility="hidden";
		document.getElementById('debit').focus();
		
}

function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);

//-->
</script>
<title>Select Account</title>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<div id="acctLayer" style="position:absolute; width:640px; height:450px; z-index:1; left: 5%; top: 18%; overflow: auto;"> 
  <table width="100%" height="100%" border="0" align="center" cellpadding="0" cellspacing="0" background="../graphics/table0_horizontal.PNG">
    <tr> 
      <td width="1%"><img src="../graphics/table0_upper_left.PNG" width="6" height="31"></td>
      <td width="48%"  height="1%"align="left" background="../graphics/table0_horizontal.PNG"><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><b>Select 
        Account </b></font></td>
      <td width="49%" height="1%" align="right" background="../graphics/table0_horizontal.PNG"><img src="../graphics/table_close.PNG" onClick="document.getElementById('acctLayer').style.visibility='hidden'"></td>
      <td width="2%" align="right"><img src="../graphics/table0_upper_right.PNG" width="6" height="30"></td>
    </tr>
    <tr valign="top" bgcolor="#A4B9DB"> 
      <td colspan="4"> 
       <div id="Layer2" style="position:virtual; width:100%; height:100%; z-index:2; left: 0; top: 0; overflow: auto;">
          <table width="99%" height="1%" border="0" align="center" cellpadding="2" cellspacing="1" bgcolor="#EFEFEF">
            <tr> 
              <td width="8%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
              <td colspan="2"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Account 
                Code </font></strong></td>
              <td width="46%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Account</font></strong></td>
              <td width="8%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Level</font></strong></td>
              <td width="15%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Type</font></strong></td>
            </tr>
            <?
  $aG = explode('-',$searchkey);
  $q = "select * 
  			from 
				gchart 
			where 
				(gchart like '$searchkey%'  or (acode||'-'||scode) like  '$searchkey%' )  and 
				enable='Y' 
			order by 
				acode,scode 
			offset 0 limit 50";

  $qr = @pg_query($q) or message(pg_errormessage());
  $ctr=0;

  while ($r = @pg_fetch_object($qr))
  {
  	$ctr++;
	$gltrancode = $r->acode.' - '.$r->scode;

	if ($acode != $r->acode)
	{
		$macode = $r->acode;
		$acode = $r->acode;
	}
	else
	{
		$macode = '';
	}
	
	$href = "vSelect('$r->gchart_id','$r->gchart','$gltrancode');";
  ?>
            <tr bgcolor="#FFFFFF" onMouseOver="bgColor='#FFFFCC'" onMouseOut="bgColor='#FFFFFF'"> 
              <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
                <?= $ctr;?>
                .</font></td>
              <td width="8%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
                <a href="#" onClick="<?=$href?>"> 
                <?= $macode;?>
                </a></font></td>
              <td width="15%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
                <a href="<?= $href;?>"> 
                <?= $r->scode;?>
                </a></font></td>
              <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
                <a href="#" onClick="<?=$href?>"> 
                <?= $r->gchart;?>
                </a> </font></td>
              <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
                <?=level($r->level);?>
                </font></td>
              <td> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
                <?= mclassReturn($r->mclass);?>
                </font></td>
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
			echo "<script>acctLayer.style.visibility='hidden';alert('Account on search NOT FOUND!')</script>";
		}
		else
		{
			echo "<script>	hideElement('select',false);</script>";
		}
		?>
