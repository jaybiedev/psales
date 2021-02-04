<br>
<div align="center"><font color="#0000FF"><strong><font color="#990000">CRITICAL 
  SETUP:</font></strong> Do this in the presence of BIR Representative<br>
  This will OVERWRITE/RESET Z-Reading Accumulator Counter</font>
  <?
  if ($ADMIN['usergroup'] != 'A')
  {
  	message1("Access Denied on This Area");
	exit;
  }
  
  if ($p1 == '__proceed' && $terminal== '' )
  {
  	message1("No Terminal Number Specified...");
  }
  elseif ($p1 == '__proceed' && $zreadno == '' )
  {
  	message1("Please Provide Counter Number...");
  }
  elseif ($p1 == '__proceed' && $newgrand == '' )
  {
  	message1("Please Provide New Grand Total...");
  }
  elseif ($p1 == '__proceed')
  {

  	$mdate = mdy2ymd($_REQUEST['date']);
  	$q = "insert into zread (terminal, date, zreadno, newgrand, lines, admin_id)
				values ('$terminal','$mdate','$zreadno','$newgrand','99999999','".$ADMIN['admin_id']."')";
  	$qr = @pg_query($q) or message1(pg_errormessage().$q);
	if ($qr)
	{
		message1("Z-Reading Successfully Set");
		$p1 = 'Go';
		$findterminal=$terminal;
	}
  }
  ?>
  <form name="f1" id="f1" method="post" action="">
    <table width="60%" border="0" cellspacing="1" cellpadding="0">
      <tr> 
        <td height="26"  background="../graphics/table0_horizontal.PNG">&nbsp;:: 
          Reset Z-Reading Accumulators</td>
      </tr>
      <tr> 
        <td>Find Terminal <input name="findterminal" type="text" id="findterminal" value="<?= $findterminal;?>" size="5" maxlength="5"> 
          <input name="p1" type="submit" id="p1" value="Go"> <font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
          <input type="button" name="p12" value="Close" onClick="window.location='?p='">
          </font></td>
      </tr>
      <tr> 
        <td><hr></td>
      </tr>
      <tr> 
        <td bgcolor="#DADADA"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
          :: History</strong></font></td>
      </tr>
      <tr> 
        <td><table width="100%%" border="0" cellspacing="1" cellpadding="0">
            <tr> 
              <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Date</font></td>
              <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Counter</font></td>
              <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">New 
                Grand Total</font></td>
              <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">User</font></td>
            </tr>
            <?
		$terminal = $findterminal;
	  $q = "select * from zread where lines = '99999999' and terminal='$findterminal'";
	  $qr = @pg_query($q) or message1(pg_errormessage());
	  while ($r = @pg_fetch_object($qr))
	  {
	  ?>
            <tr> 
              <td><strong><font color="#993366" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
                <?= ymd2mdy($r->date);?>
                </font></strong></td>
              <td><strong><font color="#993366" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
                <?= $r->zreadno;?>
                </font></strong></td>
              <td><strong><font color="#993366" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
                <?= number_format($r->newgrand,2);?>
                </font></strong></td>
              <td><strong><font color="#993366" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
                <?= lookUpTableReturnValue('x','admin','admin_id','name',$r->admin_id);?>
                </font></strong></td>
            </tr>
            <?
		}
		?>
          </table></td>
      </tr>
      <tr>
        <td><table width="100%%" border="0" cellspacing="1" cellpadding="0">
            <tr bgcolor="#DADADA"> 
              <td colspan="2" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>New 
                Reset</strong></font></td>
            </tr>
            <tr> 
              <td width="22%" height="24" nowrap>Terminal</td>
              <td width="78%" nowrap><input name="terminal" type="text" id="terrninal" value="<?= $terminal;?>" readOnly></td>
            </tr>
            <tr> 
              <td width="22%" nowrap>Date</td>
              <td width="78%" nowrap><input name="date" type="text" id="date" value="<?= date('m/d/Y');?>" readOnly></td>
            </tr>
            <tr> 
              <td nowrap>Counter</td>
              <td nowrap><input name="zreadno" type="text" id="zreadno" value="<?= $zreadno;?>"></td>
            </tr>
            <tr> 
              <td nowrap>New Grand Total</td>
              <td nowrap><input name="newgrand" type="text" id="newgrand" value="<?= $newgrand;?>"></td>
            </tr>
            <tr> 
              <td colspan="2"><input name="p1" type="button" id="p1" value="Proceed Reset Z-Reading" onClick="if (confirm('Are you sure to RESET Z-Reading for this TERMINAL')){document.getElementById('f1').action='?p=resetzread&p1=__proceed';document.getElementById('f1').submit()}"></td>
            </tr>
          </table></td>
      </tr>
    </table>
  </form>
</div>