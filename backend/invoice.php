<script>
function vChk(t)
{
	var id = t.id
	var n = id.substring(1)
	document.getElementById('m'+n).checked =1;
}
</script>
<?

$href = '?p=invoice';
if (!chkRights2("invoice","mview",$ADMIN['admin_id']))
{
	message("You have no permission in this invoice...");
	exit;
}

if (!session_is_registered('ainvoice'))
{
	session_register('ainvoice');
	$ainvoice=array();
}

/*
if ($p1=="Save Checked" && !chkRights2("invoice","madd",$ADMIN['admin_id']))
	{
		message("You have no permission to modify or add...");
	}
*/
	
if ($_REQUEST['p1'] == 'sort')
{
	$ainvoice['sortby'] = $_REQUEST['sortby'];
}
elseif ($p1=="Save Checked")
{

	$ctr=0;
	while ($ctr < count($mark))
	{
		$c = $mark[$ctr]-1;
		if ($invoice[$c]!='')
		{
			if ($terminal[$c] == '')
			{
				if ($terminal[$c] != '')
				{
					$q = "select * from invoice where terminal = '".$terminal[$c]."'";
					$qr = @pg_query($q) or message(pg_errormessage());
					if (@pg_num_rows($qr) > 0)
					{
						message1("invoice Code [".$terminal[$c]." ]  Already Exists...");
						$ctr++;
						continue;
					}
				}			
				$q = "insert into invoice (terminal, invoice)
						values ('".$terminal[$c]."','".$invoice[$c]."')";
				$qr = @pg_query($q) or message (pg_errormessage().$q);
				}
			else
			{
				@pg_query("update invoice set
						terminal='".$terminal[$c]."',
						invoice='".$invoice[$c]."'
					where
						terminal='".$terminal[$c]."'") or 
					message (pg_errormessage());
			}			
		}
		$ctr++;
		
		if ($xSearch == '') $xSearch = $terminal[$c];
	} 
	$ainvoice['status']='SAVED';
}
?>
<br>
<div align="center"><font color="#990000"><strong>* Critical Setup:</strong></font><font color="#000066"> 
  This will OVERWRITE whatever Invoice/Docket <br>
  Number currently used by the terminal</font></div>
<form name="form1" id="f1"  method="post" action="">
  <table width="50%" border="0" cellspacing="1" cellpadding="0" bgcolor="#E9E9E9" align="center">
    <tr bgcolor="#C8D7E6"> 
      <td height="27" colspan="9" background="../graphics/table0_horizontal.PNG"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><b>Setup 
        Invoice/Docket<a name="top"></a></b></font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td height="28" colspan="9" nowrap><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Find 
        <input type="text" name="xSearch" value="<?= $xSearch;?>">
        <?=lookUpAssoc('searchby',array('Terninal'=>'terminal','Invoice'=>'invoice'),$searchby);?>
        <input type="submit" name="p1" value="Go">
        Insert 
        <select name="insertcount">
          <option value="5">5</option>
          <option value="10">10</option>
          <option value="15">15</option>
          <option value="20">20</option>
        </select>
        <input type="submit" name="p1" value="Insert">
        <input type="submit" name="p1" value="List">
        <input type="button" name="p1" value="Close" onClick="window.location='?p='">
        </font> <hr color="#993300"></td>
    </tr>
    <tr bgcolor="#E9E9E9"> 
      <td width="13%"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">#</font></b></td>
      <td width="12%" valign="bottom" nowrap> <a href="javascript: document.getElementById('f1').action='?p=invoice&p1=sort&sortby=terminal'; document.getElementById('f1').submit()"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Terminal</font></b></a> 
      </td>
      <td width="37%" valign="bottom" nowrap><a href="javascript: document.getElementById('f1').action='?p=invoice&p1=sort&sortby=invoice'; document.getElementById('f1').submit()"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Invoice</font></b></a></td>
      <td width="38%" valign="bottom" nowrap><a href="javascript: document.getElementById('f1').action='?p=invoice&p1=sort&sortby=invoice'; document.getElementById('f1').submit()"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Invoice</font></b></a></td>
    </tr>
    <?
	if ($p1=='Insert')
	{
		$ainvoice['status']='INSERT';
		$c=0;
		while ($c < $insertcount)
		{
			$c++;
	?>
    <tr bgcolor="#FFFFFF"> 
      <td align=right nowrap> <font size=1> 
        <?= $c;?>
        <input type="checkbox" name="mark[]" value="<?= $c;?>" id="<?= 'm'.$c;?>">
        <input name="invoice_id[]" type="hidden" id="invoice_id[]" size="5">
        </font> </td>
      <td><input name="terminal[]" type="text" id="<?='t'.$c;?>"  onChange="vChk(this)" size="5" maxlength="5"> 
      </td>
      <td> <input type="text" name="ip[]" size="10"  onChange="vChk(this)" id="<?='t'.$c;?>"> 
      </td>
      <td> <input type="text" name="invoice[]" size="10"  onChange="vChk(this)" id="<?='t'.$c;?>"> 
      </td>
    </tr>
    <?
		}
		?>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="4" height="2" valign="bottom">&nbsp;</td>
    </tr>
    <tr bgcolor="#E9E9E9"> 
      <td colspan="4" height="20"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Saved 
        Categories</font></td>
    </tr>
    <?
	} //if insert
	else
	{
		$ainvoice['status']='LIST';
		$c=0;
	}
	if ($p1=='List') 
	{
		$ainvoice['start']=0;
		$ainvoice['sortby'] ='terminal, invoice';
		$xSearch='';
	}
	elseif ($p1 == 'Go')
	{
		$ainvoice['start']=0;
	}	
	if ($ainvoice['start'] == '') 
	{
		$ainvoice['start'] = 0;
	}
	if ($p1=='Next') 
	{
		$ainvoice['start']  +=  20;
	}
	if ($p1=='Previous') 
	{
		$ainvoice['start'] -= 20;
	}
	if ($ainvoice['start'] < 0) $ainvoice['start']=0;	
	
	$q = "select * from invoice ";
	if ($xSearch != '')
	{
		$q .= " where $searchby ilike '%$xSearch%' ";
	}
	
	if ($ainvoice['sortby'] == '' )
	{
		$ainvoice['sortby'] = 'terminal, invoice';
	}
	$sortby = $ainvoice['sortby'];
	$start = $ainvoice['start'];
	$q .= " order by $sortby offset $start limit 20";
	


	$qr = @pg_query($q) or die (pg_errormessage().$q);
	$ctr = $c;
	while ($r = pg_fetch_object($qr))
	{
		$ctr++;
		
	?>
    <tr  bgcolor=<?= ($r->enable=='N')? '#FFCCCC' :'#FFFFFF';?>> 
      <td  align=right nowrap><font size=1> 
        <? 
	  echo "$ctr."; 
	  if ($p1!='Insert')
	  {
         echo "<input type='checkbox' name='mark[]' value='$ctr' id='m$ctr'>";
	  }
	  ?>
        </font> </td>
      <td><input name="terminal[]" type="text" id="<?='t'.$ctr;?>" onChange="vChk(this)" value="<?= $r->terminal;?>" size="5" maxlength="5"> 
      </td>
      <td> <input name="ip[]" type="text" id="<?='t'.$ctr;?>"  onChange="vChk(this)" value="<?= $r->ip;?>" size="10"> 
      </td>
      <td> <input name="invoice[]" type="text" id="<?='t'.$ctr;?>"  onChange="vChk(this)" value="<?= $r->invoice;?>" size="10"> 
      </td>
    </tr>
    <?
	}
	?>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="4" nowrap><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        <input type="submit" name="p1" value="Save Checked">
        </font> </td>
    </tr>
  </table>
</form>
<div align="center">
	  	<img src='../graphics/redarrow_left.gif'>
		<a href="javascript: document.getElementById('f1').action='?p=invoice&p1=Previous'; document.getElementById('f1').submit()"> Previous</a>
        <b>|</b> 
        <a href="javascript: document.getElementById('f1').action='?p=invoice&p1=Next'; document.getElementById('f1').submit()"> Next </a>
		<img src='../graphics/redarrow_right.gif'> 
</div>		
