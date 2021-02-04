<script>
function vChk(t)
{
	var id = t.id
	var n = id.substring(1)
	document.getElementById('m'+n).checked =1;
}
</script>
<?

$href = '?p=gltranremark';
if (!chkRights2("gltranremark","mview",$ADMIN['admin_id']))
{
	message("You have no permission in this gltranremark...");
	exit;
}

if (!session_is_registered('agltranremark'))
{
	session_register('agltranremark');
	$agltranremark=array();
}

/*
if ($p1=="Save Checked" && !chkRights2("gltranremark","madd",$ADMIN['admin_id']))
	{
		message("You have no permission to modify or add...");
	}
*/
	
if ($_REQUEST['p1'] == 'sort')
{
	$agltranremark['sortby'] = $_REQUEST['sortby'];
}
elseif ($p1=="Save Checked")
{

	$ctr=0;
	while ($ctr < count($mark))
	{
		$c = $mark[$ctr]-1;
		if ($gltranremark[$c]!='')
		{
			if ($cdisc1[$c] == '') $cdisc1[$c] = 0;
			if ($sdisc1[$c] == '') $sdisc1[$c] = 0;
			if ($percent_income[$c] == '') $percent_income[$c] = 0;

			if ($gltranremark_id[$c] == '')
			{
				if ($gltranremark_code[$c] != '')
				{
					$q = "select * from gltranremark where gltranremark_code = '".$gltranremark_code[$c]."'";
					$qr = @pg_query($q) or message(pg_errormessage());
					if (@pg_num_rows($qr) > 0)
					{
						message1("gltranremark Code [".$gltranremark_code[$c]." ]  Already Exists...");
						$ctr++;
						continue;
					}
				}			
				$q = "insert into gltranremark (enable, gltranremark_code, gltranremark)
						values ('".$enable[$c]."','".$gltranremark_code[$c]."','".$gltranremark[$c]."')";
				$qr = @pg_query($q) or message (pg_errormessage().$q);
				}
			else
			{
				@pg_query("update gltranremark set
						enable='".$enable[$c]."',
						gltranremark_code='".$gltranremark_code[$c]."',
						gltranremark='".$gltranremark[$c]."'
					where
						gltranremark_id='".$gltranremark_id[$c]."'") or 
					message (pg_errormessage());
			}			
		}
		$ctr++;
		
		if ($xSearch == '') $xSearch = $gltranremark[$c];
	} 
	$agltranremark['status']='SAVED';
}
?>
<form name="form1" id="f1"  method="post" action="" style="margin:10px">
  <table width="50%" border="0" cellspacing="1" cellpadding="0" bgcolor="#E9E9E9" align="center">
    <tr bgcolor="#C8D7E6" background="../graphics/table_horizontal.PNG"> 
      <td height="22" colspan="9"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><b><font color="#FFFFCC">Setup 
        GL Remarks Template</font> <a name="top"></a></b></font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td height="28" colspan="9" nowrap><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Find 
        <input type="text" name="xSearch" value="<?= $xSearch;?>">
        <?=lookUpAssoc('searchby',array('gltranremark'=>'gltranremark','Code'=>'gltranremark_code'),$searchby);?>
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
      <td width="4%"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">#</font></b></td>
      <td valign="bottom" nowrap><a href="javascript: document.getElementById('f1').action='?p=gltranremark&p1=sort&sortby=gltranremark'; document.getElementById('f1').submit()"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">GL 
        Journal Entry Remark</font></b></a></td>
      <td valign="bottom" nowrap><a href="javascript: document.getElementById('f1').action='?p=gltranremark&p1=sort&sortby=gltranremark_code'; document.getElementById('f1').submit()"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Code</font></b></a></td>
      <td valign="bottom" nowrap><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        </font><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Enabled</font></b></td>
    </tr>
    <?
	if ($p1=='Insert')
	{
		$agltranremark['status']='INSERT';
		$c=0;
		while ($c < $insertcount)
		{
			$c++;
	?>
    <tr bgcolor="#FFFFFF"> 
      <td align=right valign="top" nowrap> <font size=1> 
        <?= $c;?>
        <input type="checkbox" name="mark[]" value="<?= $c;?>" id="<?= 'm'.$c;?>">
        <input name="gltranremark_id[]" type="hidden" id="gltranremark_id[]" size="5">
        </font> </td>
      <td> <textarea name="gltranremark[]" cols="60" rows="2" id="<?='t'.$c;?>" onChange="vChk(this)"></textarea> 
      </td>
      <td valign="top"><input name="gltranremark_code[]" type="text" id="<?='t'.$c;?>"  onChange="vChk(this)" size="5" maxlength="5"></td>
      <td valign="top"> 
        <?= lookUpAssoc('enable[]',array("Yes"=>"Y","No"=>"N"),'');?>
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
        Remarks </font></td>
    </tr>
    <?
	} //if insert
	else
	{
		$agltranremark['status']='LIST';
		$c=0;
	}
	if ($p1=='List') 
	{
		$agltranremark['start']=0;
		$agltranremark['sortby'] ='gltranremark_code, gltranremark';
		$xSearch='';
	}
	elseif ($p1 == 'Go')
	{
		$agltranremark['start']=0;
	}	
	if ($agltranremark['start'] == '') 
	{
		$agltranremark['start'] = 0;
	}
	if ($p1=='Next') 
	{
		$agltranremark['start']  +=  20;
	}
	if ($p1=='Previous') 
	{
		$agltranremark['start'] -= 20;
	}
	if ($agltranremark['start'] < 0) $agltranremark['start']=0;	
	
	$q = "select * from gltranremark ";
	if ($xSearch != '')
	{
		$q .= " where $searchby ilike '%$xSearch%' ";
	}
	
	if ($agltranremark['sortby'] == '' )
	{
		$agltranremark['sortby'] = 'gltranremark_code, gltranremark';
	}
	$sortby = $agltranremark['sortby'];
	$start = $agltranremark['start'];
	$q .= " order by $sortby offset $start limit 20";

	$qr = @pg_query($q) or die (pg_errormessage());
	$ctr = $c;
	while ($r = pg_fetch_object($qr))
	{
		$ctr++;
		
	?>
    <tr valign="top"  bgcolor=<?= ($r->enable=='N')? '#FFCCCC' :'#FFFFFF';?>> 
      <td  align=right nowrap><font size=1> 
        <input type="hidden" name="gltranremark_id[]" size="5" value="<?= $r->gltranremark_id;?>">
        <? 
	  echo "$ctr."; 
	  if ($p1!='Insert')
	  {
         echo "<input type='checkbox' name='mark[]' value='$ctr' id='m$ctr'>";
	  }
	  ?>
        </font> </td>
      <td> <textarea name="gltranremark[]" cols="60" rows="2" id="<?='t'.$ctr;?>" onChange="vChk(this)"><?= $r->gltranremark;?></textarea> 
      </td>
      <td><input name="gltranremark_code[]" type="text" id="<?='t'.$ctr;?>" onChange="vChk(this)" value="<?= $r->gltranremark_code;?>" size="5" maxlength="5"> 
      </td>
      <td> 
        <?= lookUpAssoc('enable[]',array("Yes"=>"Y","No"=>"N"),$r->enable);?>
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
		<a href="javascript: document.getElementById('f1').action='?p=gltranremark&p1=Previous'; document.getElementById('f1').submit()"> Previous</a>
        <b>|</b> 
        <a href="javascript: document.getElementById('f1').action='?p=gltranremark&p1=Next'; document.getElementById('f1').submit()"> Next </a>
		<img src='../graphics/redarrow_right.gif'> 
</div>		
