<?
if (!session_is_registered('aTERM'))
{
	session_register('aTERM');
	$aTERM = null;
	$aTERM = array();
}
if (!session_is_registered('aTERMD'))
{
	session_register('aTERMD');
	$aTERMD = null;
	$aTERMD = array();
}

?>
<br>
<form name="f1" method="post" action="">
  <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr> 
      <td nowrap><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Find 
        <input type="text" name="xSearch" value="<?= $xSearch;?>">
        <input name="p1" type="submit" id="p1" value="Go">
        <input name="p1" type="submit" id="p1" value="Add New">
        <input name="p1" type="submit" id="p1" value="List">
        <input name="p1" type="button" id="p1" onClick="window.location='?p='" value="Close">
        </font>
        <hr color="#CC3300"></td>
    </tr>
  </table>
 <?
	if ($p1 == 'Add New' || $p1=='New' || $p1 == 'Save' || $p1 == 'Load')
	{
		include_once('terminal.config.edit.php');
	}
 ?> 
  
  <table width="90%" border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#EFEFEF">
    <tr> 
      <td colspan="10" background="../graphics/table0_horizontal.PNG">&nbsp;<img src="../graphics/bluelist.gif" width="16" height="17">         <strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Browse 
        Terminals</font></strong></td>
    </tr>
    <tr> 
      <td width="6%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
      <td width="15%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">IP 
        Address</font></strong></td>
      <td width="18%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Description</font></strong></td>
      <td width="8%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Term#</font></strong></td>
      <td width="19%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Area</font></strong></td>
      <td width="11%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Cashier</font></strong></td>
      <td width="12%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Machine No </font></strong></td>
      <td width="11%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Permit No</font></strong></td>
      <td width="11%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Enabled</font></strong></td>
      <td width="11%" style="text-align:center;"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Allow Mul Qty</font></strong></td>
    </tr>
    <?
		$q = "select * from terminal order by ip";
		$qr = @pg_query($q) or message(pg_errormessage());
		$mip= 'xxx';
		$ctr=0;
		while ($r = @pg_fetch_object($qr))
		{

				if ($mip != $r->ip )
				{
					if ($mip == 'xxx')
					{
						$mip = $r->ip;
					}
					else
					{
						$mip = $r->ip;
						$ctr++;
	?>
    <tr bgcolor="#FFFFFF"> 
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $ctr;?>
        .</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
	<a href="?p=../backend/terminal&p1=Load&id=<?=$id;?>"> 
        <?= $ip;?>
        </a></font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $description;?>
        </font></td>
      <td align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $terminal;?>
        </font></td>
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $area;?>
        </font></td>
      <td> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?=$cashiering;?>
        </font></td>
      <td align="left"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <?=$machineno;?>
      </font></td>
	 <td align="left"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <?=$permitno;?>
      </font></td>
      <td align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $enable;?>
        </font></td>        
      <td align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $allow_mul_qty;?>
        </font></td>
    </tr>
    <?
					}
					$description=$area_id=$enable=$terminal=$machineno=$permitno=$allow_mul_qty='';
				
				}
				
				$id = $r->terminal_id;
				if ($r->definition == 'IP')
				{
					$id = $r->terminal_id;
					$ip = $r->value;
				}
				elseif ($r->definition == 'DESCRIPTION')
				{
					$description = $r->value;
				}
				elseif ($r->definition == 'AREA_ID')
				{
					$area = lookUpTableReturnValue('x','area','area_id','area',$r->value);
				}
				elseif ($r->definition == 'TERMINAL')
				{
					$terminal = $r->value;
				}
				elseif ($r->definition == 'MACHINENO')
				{
					$machineno = $r->value;
				}
				elseif ($r->definition == 'PERMITNO')
				{
					$permitno = $r->value;
				}
				elseif ($r->definition == 'CASHIERING')
				{
					$cashiering = ($r->value == 'Y' ? 'Yes' : 'No');
				}
				elseif ($r->definition == 'ENABLE')
				{
					$enable = $r->value;
				} 
        else if ( $r->definition == "ALLOW_MUL_QTY" ) {
          $allow_mul_qty = $r->value;
        }

        if ( empty($allow_mul_qty) ) $allow_mul_qty = "N";
	}
	$ctr++;							
	?>
    <tr bgcolor="#FFFFFF"> 
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $ctr;?>
        .</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href="?p=terminal&p1=Load&id=<?=$id;?>"> 
        <?= $ip;?>
        </a></font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $description;?>
        </font></td>
      <td align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $terminal;?>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $area;?>
        </font></td>
      <td> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?=$cashiering;?>
        </font></td>
      <td align="left"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <?=$machineno;?>
      </font></td>
	<td align="left"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <?=$permitno;?>
      </font></td>
      <td align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $enable;?>
        </font></td>
        <td align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $allow_mul_qty;?>
        </font></td>
    </tr>
  </table>
</form>
