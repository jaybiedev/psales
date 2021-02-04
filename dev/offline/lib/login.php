<body leftmargin="2" topmargin="2" marginwidth="2" marginheight="2" onLoad="getElementById('username').focus()">
<?
	if ($message!="") echo "<center> $message </center>";
	$ip = $_SERVER['REMOTE_ADDR'];
	$i = explode('.',$ip);
	$terminal = $i[3];
?>
<br>
<div align=center>
  <?
	$q = "select * from terminal where ip='".$_SERVER['REMOTE_ADDR']."' and definition='AREA_ID'";
	$qr = @pg_query($q) or message(pg_error());
	$r = @pg_fetch_object($qr);
	if ($r->value < 3)
	{
?>
  <span style="background-color:#FFCCCC"> 
  <h1>&nbsp; &nbsp;  Please Proceed To Next Counter.   &nbsp; &nbsp;</h1></span>
<?
	}
?><br>
Today is 
  <?=date("l F j, Y g:ia").'</br> User@'.lookUpTableReturnValue('x','area','area_id','area',$r->value).' '.$terminal;?>
    <p>
  <h3>Press [ F11 ] Now To View FULL SCREEN</h3>
  </p>

</div>
<form name="form1" method="post" action="?">
   
  <table width="22%" cellspacing="0" align="center">
    <tr> 
		
<!--      <td valign="top" align="center" nowrap><strong><font size="+3" face="Times New Roman, Times, serif">
	  <em><?= $SYSCONF['BUSINESS_NAME'];?></em></font></strong><br>
		</td>
	</tr>	-->
	<tr><td>&nbsp;<br></td></tr>
	<tr bgColor="#CCCCCC">	
      <td> 
        <table width="100%" border="0" cellpadding="1" cellspacing="1" bgcolor="#E2E2E2">
          <tr bgcolor="#3366CC"> 
            <td height="25" colspan="2" align=center><strong><font color="#FFCC33" size="4">LOGIN</font></strong></td>
          </tr>
          <tr bgcolor="#EFEFEF"> 
            <td width="44%" height="25"> <div align="center"><font size="2"><b>USERNAME</b></font></div></td>
            <td width="56%"> <input type="text" name="username" id="username"> </td>
          </tr>
          <tr bgcolor="#EFEFEF"> 
            <td width="44%"> <div align="center"><font size="2"><b>PASSWORD</b></font></div></td>
            <td width="56%"> <input type="password" name="mpassword" id="mpassword"> </td>
          </tr>
          <tr bgcolor="#EFEFEF"> 
            <td colspan="2"> <div align="right"> 
                <input type="submit" name="p1" value="Login" id="p1">
              </div></td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</form>
<div align="center">

<br>
  <br>
  <br>
  <br>
  <br>
</div>
<address>
<center>
  <img src="graphics/elephantSmall.gif" width="50" height="50"> <img src="graphics/php-small-white.gif" width="88" height="31"><img src="graphics/worm_in_hole.gif" width="23" height="33"><br>
  <font size="2">Developed by: Jared O. Santibañez, ECE, MT </font><br>
</center>
</address>
<script>
document.getElementById('username').focus();
</script>
