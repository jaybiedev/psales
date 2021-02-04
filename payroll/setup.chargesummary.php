<script>
function vChk(t)
{
	var id = t.id
	var n = id.substring(1)
	document.getElementById('m'+n).checked =1;
}
</script>
<?

$href = '?p=reportform';
if (!chkRights2("reportform","mview",$ADMIN['admin_id']))
{
	message("You have no permission in this reportform...");
	exit;
}

if (!session_is_registered('areportform'))
{
	session_register('areportform');
	$areportform=array();
}

/*
if ($p1=="Save Checked" && !chkRights2("reportform","madd",$ADMIN['admin_id']))
	{
		message("You have no permission to modify or add...");
	}
*/
	
if ($_REQUEST['p1'] == 'sort')
{
	$areportform['sortby'] = $_REQUEST['sortby'];
}
elseif ($p1=="Save Checked")
{

	$ctr=0;
	while ($ctr < count($mark))
	{
		$c = $mark[$ctr]-1;
		if ($seqn[$c]!='')
		{

			if ($reportform_id[$c] == '')
			{
				if ($reportform_code[$c] != '')
				{
					$q = "select * from reportform where  reportform='CHARGESUM' and reportform_code = '".$reportform_code[$c]."'";
					$qr = @pg_query($q) or message(pg_errormessage());
					if (@pg_num_rows($qr) > 0)
					{
						message1("reportform Code [".$reportform_code[$c]." ]  Already Exists...");
						$ctr++;
						continue;
					}
				}			
				$q = "insert into reportform (reportform, enable, reportform_code, title1, title2, type, seqn, long)
						values ('CHARGESUM', '".$enable[$c]."','".$reportform_code[$c]."','".$title1[$c]."', '".$title2[$c]."','".$type[$c]."', '".$seqn[$c]."', '".$long[$c]."')";
				$qr = @pg_query($q) or message (pg_errormessage().$q);
			}
			else
			{
				@pg_query("update reportform set
						enable='".$enable[$c]."',
						reportform_code='".$reportform_code[$c]."',
						title1='".$title1[$c]."',
						title2='".$title2[$c]."',
						seqn='".$seqn[$c]."',
						long='".$long[$c]."',
						type='".$type[$c]."'
						where
						reportform_id='".$reportform_id[$c]."'") or 
					message (pg_errormessage());
			}			
		}
		$ctr++;
		
		if ($xSearch == '') $xSearch = $title1[$c];
	} 
	$areportform['status']='SAVED';
}
?><br>
<form name="form1" id="f1"  method="post" action="">
  <table width="50%" border="0" cellspacing="1" cellpadding="0" bgcolor="#E9E9E9" align="center">
    <tr bgcolor="#C8D7E6"> 
      <td height="27" colspan="13" background="../graphics/table0_horizontal.PNG"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        <img src="../graphics/list3.gif" width="16" height="16"> <strong><font color="#F3F7F9">Setup 
        Charges Summary Reportform </font></strong></font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td height="28" colspan="13" nowrap><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Find 
        <input type="text" name="xSearch" value="<?= $xSearch;?>">
        <?=lookUpAssoc('searchby',array('reportform'=>'title1','Code'=>'reportform_code'),$searchby);?>
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
      <td width="4%" rowspan="2"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">#</font></b></td>
      <td rowspan="2" valign="bottom" nowrap><a href="javascript: document.getElementById('f1').action='?p=reportform&p1=sort&sortby=seqn'; document.getElementById('f1').submit()"> 
        <b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Seqn</font></b></a></td>
      <td colspan="2" align="center" valign="bottom" nowrap><a href="javascript: document.getElementById('f1').action='?p=reportform&p1=sort&sortby=reportform_code'; document.getElementById('f1').submit()"><b></b></a><a href="javascript: document.getElementById('f1').action='?p=reportform&p1=sort&sortby=reportform'; document.getElementById('f1').submit()"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Title</font></b></a></td>
      <td rowspan="2" align="center" nowrap><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000"> 
        </font></b><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000"><br>
        </font></b><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000"><a href="javascript: document.getElementById('f1').action='?p=reportform&p1=sort&sortby=reportform_code'; document.getElementById('f1').submit()"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Code</font></b></a></font></b></td>
      <td rowspan="2" align="center" nowrap><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Len</font></b></td>
      <td rowspan="2" align="center" valign="bottom" nowrap><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Type</font></b></td>
      <td rowspan="2" valign="bottom" nowrap><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        </font><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Enabled</font></b></td>
    </tr>
    <tr bgcolor="#E9E9E9"> 
      <td valign="bottom" nowrap><a href="javascript: document.getElementById('f1').action='?p=reportform&p1=sort&sortby=reportform'; document.getElementById('f1').submit()"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Line1</font></b></a></td>
      <td valign="bottom" nowrap><a href="javascript: document.getElementById('f1').action='?p=reportform&p1=sort&sortby=reportform'; document.getElementById('f1').submit()"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Line2</font></b></a></td>
    </tr>
    <?
	if ($p1=='Insert')
	{
		$areportform['status']='INSERT';
		$c=0;
		while ($c < $insertcount)
		{
			$c++;
	?>
    <tr bgcolor="#FFFFFF"> 
      <td align=right nowrap> <font size=1> 
        <?= $c;?>
        <input type="checkbox" name="mark[]" value="<?= $c;?>" id="<?= 'm'.$c;?>">
        <input name="reportform_id[]" type="hidden" id="reportform_id[]" size="5">
        </font> </td>
      <td><input name="seqn[]" type="text" id="<?='t'.$c;?>"  onChange="vChk(this)" size="8" maxlength="8"></td>
      <td><input name="title1[]" type="text" id="<?='t'.$c;?>"  onChange="vChk(this)" size="10" maxlength="10"></td>
      <td> <input name="title2[]" type="text" id="<?='t'.$c;?>"  onChange="vChk(this)" size="10" maxlength="10"></td>
      <td><input name="reportform_code[]" type="text" id="<?='t'.$c;?>"  onChange="vChk(this)" size="25" maxlength="25"></td>
      <td><input name="long[]2" type="text"  id="long[]"   onChange="vChk(this)" value="<?= $r->long;?>" size="5" maxlength="5"></td>
      <td> 
        <?= lookUpAssoc('type[]',array('Income'=>'I','Deduction'=>'D'), '');?>
      </td>
      <td> 
        <?= lookUpAssoc('enable[]',array("Yes"=>"Y","No"=>"N"),'');?>
      </td>
    </tr>
    <?
		}
		?>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="8" height="2" valign="bottom">&nbsp;</td>
    </tr>
    <tr bgcolor="#E9E9E9"> 
      <td colspan="8" height="20"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Saved 
        Categories</font></td>
    </tr>
    <?
	} //if insert
	else
	{
		$areportform['status']='LIST';
		$c=0;
	}
	if ($p1=='List') 
	{
		$areportform['start']=0;
		$areportform['sortby'] ='seqn';
		$xSearch='';
	}
	elseif ($p1 == 'Go')
	{
		$areportform['start']=0;
	}	
	if ($areportform['start'] == '') 
	{
		$areportform['start'] = 0;
	}
	if ($p1=='Next') 
	{
		$areportform['start']  +=  20;
	}
	if ($p1=='Previous') 
	{
		$areportform['start'] -= 20;
	}
	if ($areportform['start'] < 0) $areportform['start']=0;	
	
	$q = "select * from reportform where reportform='CHARGESUM' ";
	if ($xSearch != '')
	{
		$q .= " and $searchby ilike '%$xSearch%' ";
	}
	
	if ($areportform['sortby'] == '' )
	{
		$areportform['sortby'] = 'seqn';
	}
	$sortby = $areportform['sortby'];
	$start = $areportform['start'];
	$q .= " order by $sortby offset $start limit 20";

	$qr = @pg_query($q) or die (pg_errormessage());
	$ctr = $c;
	while ($r = pg_fetch_object($qr))
	{
		$ctr++;
		
	?>
    <tr  bgcolor=<?= ($r->enable=='N')? '#FFCCCC' :'#FFFFFF';?>> 
      <td  align=right nowrap><font size=1> 
        <input type="hidden" name="reportform_id[]" size="5" value="<?= $r->reportform_id;?>">
        <? 
	  echo "$ctr."; 
	  if ($p1!='Insert')
	  {
         echo "<input type='checkbox' name='mark[]' value='$ctr' id='m$ctr'>";
	  }
	  ?>
        </font> </td>
      <td><input name="seqn[]" type="text"  id="<?='t'.$ctr;?>"  onChange="vChk(this)" value="<?= $r->seqn;?>" size="8" maxlength="8" ></td>
      <td> <input name="title1[]" type="text" id="<?='t'.$ctr;?>"  onChange="vChk(this)" value="<?= $r->title1;?>" size="10" maxlength="10"> 
      </td>
      <td> <input name="title2[]" type="text" id="<?='t'.$ctr;?>"  onChange="vChk(this)" value="<?= $r->title2;?>" size="10" maxlength="10"> 
      </td>
      <td><input name="reportform_code[]" type="text" id="<?='t'.$ctr;?>" onChange="vChk(this)" value="<?= $r->reportform_code;?>" size="25" maxlength="25"></td>
      <td><input name="long[]" type="text"  id="<?='t'.$ctr;?>"   onChange="vChk(this)" value="<?= $r->long;?>" size="5" maxlength="5"></td>
      <td> 
        <?= lookUpAssoc('type[]',array('Income'=>'I','Deduction'=>'D'), $r->type);?>
      </td>
      <td> 
        <?= lookUpAssoc('enable[]',array("Yes"=>"Y","No"=>"N"),$r->enable);?>
      </td>
    </tr>
    <?
	}
	?>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="8" nowrap><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        <input type="submit" name="p1" value="Save Checked">
        </font> </td>
    </tr>
  </table>
</form>
<div align="center">
	  	<img src='../graphics/redarrow_left.gif'>
		<a href="javascript: document.getElementById('f1').action='?p=reportform&p1=Previous'; document.getElementById('f1').submit()"> Previous</a>
        <b>|</b> 
        <a href="javascript: document.getElementById('f1').action='?p=reportform&p1=Next'; document.getElementById('f1').submit()"> Next </a>
		<img src='../graphics/redarrow_right.gif'> 
</div>		
