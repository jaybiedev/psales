<br>
<form name="form1" method="post" action="">
  <table width="85%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr>
      <td>Search 
        <input name="xSearch" type="text" id="xSearch" value="<?= $xSearch;?>"> 
        <?=lookUpAssoc('searchby',array('Reference'=>'reference','Account'=>'account','Check'=>'mcheck','Check Date'=>'checkdate','Date Entry'=>'date','Amount'=>'amount'),$searchby);?>
        <input name="p1" type="submit" id="p14" value="Go"> <input name="p1" type="button" id="p14" value="Add New" onClick="window.location='?p=memo&p1=New'">
        <input name="p1" type="submit" id="p15" value="Close"> </td>
    </tr>
  </table>
  <hr align="center" color="#993300" width="95%">
  
  <table width="85%" border="0" align="center" cellpadding="1" cellspacing="1" bgcolor="#EFEFEF">
    <tr bgcolor="#003366"> 
      <td colspan="7"><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Employee 
        Memo </strong></font></td>
    </tr>
    <tr> 
      <td width="3%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></td>
      <td width="8%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Date</font></td>
      <td width="9%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Reference</font></td>
      <td width="25%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Employee</font></td>
      <td width="17%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Topic</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Memo</font></td>
      <td width="12%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Status</font></td>
    </tr>
    <?
		$q = "select * 
					from 
						memo ,
						paymast
					where
						memo.paymast_id = paymast.paymast_id
					order by date desc ";
		$qr = @pg_query($q) or message(pg_errormessage());
		$ctr=0;
		while ($r = @pg_fetch_object($qr))
		{
			$ctr++;
	?>
    <tr bgcolor="#FFFFFF" onClick="window.location='?p=memo&p1=Load&id=<?=$r->memo_id;?>'" onMouseOver="bgColor='#FFFFCC'" onMouseOut="bgColor='#FFFFFF'"> 
      <td align="right" ><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?=$ctr;?>
        . </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href="?p=memo&p1=Load&id=<?= $r->memo_id;?>"> 
        <?=ymd2mdy($r->date);?>
        </a></font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href="?p=memo&p1=Load&id=<?= $r->memo_id;?>"> 
        <?= $r->reference;?>
        </a></font> </td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href="?p=memo&p1=Load&id=<?= $r->memo_id;?>"> 
        <?= $r->elast.', '.$r->efirst;?>
        </a></font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $r->topic;?>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><?= substr($r->memo,0,30).'...';?></font></td>
      <td> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $r->enable;?>
        </font></td>
      <?
	  }
	  ?>
    </tr>
  </table>
</form>

 