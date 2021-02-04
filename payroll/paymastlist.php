 
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

<form action="" method="post">
  <table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr bgcolor="#B1BFC7"> 
      <td width="67%" bgcolor="#B1BFC7"><strong><font size="3" face="Verdana, Arial, Helvetica, sans-serif">Employee 
        Master</font></strong></td>
      <td width="33%" nowrap bgcolor="#B1BFC7"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Search 
        <input type="text" name="textfield">
        <img src="../graphics/go.gif" width="23" height="19"> </font></strong></td>
    </tr>
  <tr height="500"><td colspan=2>
  <div id="Layer1" style="position:absolute; width:100%; height:100%; z-index:1; overflow: auto;" >
    <table width="100%" border="0" align="center" cellpadding="1" cellspacing="1">
      <tr bgcolor="#D2DCDF"> 
        <td width="4%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
        <td width="12%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Idno</font></strong></td>
        <td width="26%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Last 
          Name</font></strong></td>
        <td width="26%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">First 
          Name</font></strong></td>
        <td width="23%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Middle 
          Name</font></strong></td>
        <td width="9%">&nbsp;</td>
      </tr>
	<?
		$qr = mysql_query("select 
				elast, 
				efirst, 
				emiddle,
				paymast_id
			from 
			 	paymast 
			where 
				(enable='Y' or isnull(enable)) 
			order by 
				elast, efirst");
				
		$ctr=0;		
		while ($r = mysql_fetch_object($qr))
		{
			$ctr++;		
			if ($ctr%2==0)
			{
				echo "<tr bgColor='#F4F7F7' onMouseOver=bgColor='".'#E3F4F0'."' onMouseOut=bgColor='".'#F4F7F7'."'>";
			}
			else
			{
				echo "<tr bgColor='#FFFFFF' onMouseOver=bgColor='".'#E3F4F0'."' onMouseOut=bgColor='".'#FFFFFF'."'>";
			}
				
	?>
        <td align="right" width="4%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
           <?=$ctr;?>.</font></td>
        <td width="12%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
			<?= $r->idno;?></font></td>
        <td width="26%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
			<?= $r->elast;?></font></td>
        <td width="26%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
			<?= $r->efirst;?></font></td>
        <td width="23%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
			<?= $r->emiddle;?></font></td>
              <td width="9%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
                <a href="?p=paymast&p1=Edit&paymast_id=<?=$r->paymast_id;?>">View</a></font></td>
      </tr>
	  <?
	  }
	  ?>
    
  
  </table>
  </div> 
  </td>
  </tr>
  <tr bgcolor="#B1BFC7">
  <td colspan=6><input name="button" type="button" onClick="location.href='?p=paymast'" value="Add New"></td>
  </tr>
  </table>
</form>