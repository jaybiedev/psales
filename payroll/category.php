<script>
function vChk(t)
{
	var id = t.id
	var n = id.substring(1)
	document.getElementById('m'+n).checked =1;
}
</script>
<?

$href = '?p=category';
if (!chkRights2("category","mview",$ADMIN['admin_id']))
{
	message("You have no permission in this category...");
	exit;
}

if (!session_is_registered('acategory'))
{
	session_register('acategory');
	$acategory=array();
}

/*
if ($p1=="Save Checked" && !chkRights2("category","madd",$ADMIN['admin_id']))
	{
		message("You have no permission to modify or add...");
	}
*/
	
if ($_REQUEST['p1'] == 'sort')
{
	$acategory['sortby'] = $_REQUEST['sortby'];
}
elseif ($p1=="Save Checked")
{

	$ctr=0;
	while ($ctr < count($mark))
	{
		$c = $mark[$ctr]-1;
		if ($category[$c]!='')
		{
			if ($cdisc1[$c] == '') $cdisc1[$c] = 0;
			if ($sdisc1[$c] == '') $sdisc1[$c] = 0;
			if ($percent_income[$c] == '') $percent_income[$c] = 0;

			if ($category_id[$c] == '')
			{
				if ($category_code[$c] != '')
				{
					$q = "select * from category where category_code = '".$category_code[$c]."'";
					$qr = @pg_query($q) or message(pg_errormessage());
					if (@pg_num_rows($qr) > 0)
					{
						message1("Category Code [".$category_code[$c]." ]  Already Exists...");
						$ctr++;
						continue;
					}
				}			
				$q = "insert into category (enable, category_code, category, department, seqn)
						values ('".$enable[$c]."','".$category_code[$c]."','".$category[$c]."', '".$department[$c]."', '".$seqn[$c]."')";
				$qr = @pg_query($q) or message (pg_errormessage().$q);
				}
			else
			{
				@pg_query("update category set
						enable='".$enable[$c]."',
						category_code='".$category_code[$c]."',
						category='".$category[$c]."',
						seqn='".$seqn[$c]."',
						department = '".$department[$c]."'
					where
						category_id='".$category_id[$c]."'") or 
					message (pg_errormessage());
			}			
		}
		$ctr++;
		
		if ($xSearch == '') $xSearch = $category[$c];
	} 
	$acategory['status']='SAVED';
}
?>
<form name="form1" id="f1"  method="post" action="">
  <table width="50%" border="0" cellspacing="1" cellpadding="0" bgcolor="#E9E9E9" align="center">
    <tr bgcolor="#C8D7E6"> 
      <td height="27" colspan="11" background="../graphics/table0_horizontal.PNG"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><b>Setup 
        Category <a name="top"></a></b></font></td>
    </tr>
    <tr bgcolor="#FFFFFF"> 
      <td height="28" colspan="11" nowrap><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Find 
        <input type="text" name="xSearch" value="<?= $xSearch;?>">
        <?=lookUpAssoc('searchby',array('Category'=>'category','Code'=>'category_code'),$searchby);?>
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
      <td valign="bottom" nowrap><a href="javascript: document.getElementById('f1').action='?p=category&p1=sort&sortby=category'; document.getElementById('f1').submit()"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Category</font></b></a></td>
      <td valign="bottom" nowrap><a href="javascript: document.getElementById('f1').action='?p=category&p1=sort&sortby=category_code'; document.getElementById('f1').submit()"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Code</font></b></a></td>
      <td align="center" nowrap><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000"> 
        </font></b><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000"><br>
        </font></b><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Seqn</font></b></td>
      <td align="center" valign="bottom" nowrap><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000"></font></b><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Department</font></b></td>
      <td valign="bottom" nowrap><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        </font><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Enabled</font></b></td>
    </tr>
    <?
	if ($p1=='Insert')
	{
		$acategory['status']='INSERT';
		$c=0;
		while ($c < $insertcount)
		{
			$c++;
	?>
    <tr bgcolor="#FFFFFF"> 
      <td align=right nowrap> <font size=1> 
        <?= $c;?>
        <input type="checkbox" name="mark[]" value="<?= $c;?>" id="<?= 'm'.$c;?>">
        <input name="category_id[]" type="hidden" id="category_id[]" size="5">
        </font> </td>
      <td> <input type="text" name="category[]" size="40"  onChange="vChk(this)" id="<?='t'.$c;?>"> 
      </td>
      <td><input name="category_code[]" type="text" id="<?='t'.$c;?>"  onChange="vChk(this)" size="5" maxlength="5"></td>
      <td><input name="seqn[]" type="text" id="<?='t'.$c;?>"  onChange="vChk(this)" size="8" maxlength="8"></td>
      <td> <select name="department[]"  onChange="vChk(this)" id="<?='t'.$c;?>">
          <option value="G">Grocery</option>
          <option value="D">Dry Goods</option>
        </select> </td>
      <td> 
        <?= lookUpAssoc('enable[]',array("Yes"=>"Y","No"=>"N"),'');?>
      </td>
    </tr>
    <?
		}
		?>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="6" height="2" valign="bottom">&nbsp;</td>
    </tr>
    <tr bgcolor="#E9E9E9"> 
      <td colspan="6" height="20"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Saved 
        Categories</font></td>
    </tr>
    <?
	} //if insert
	else
	{
		$acategory['status']='LIST';
		$c=0;
	}
	if ($p1=='List') 
	{
		$acategory['start']=0;
		$acategory['sortby'] ='category_code, category';
		$xSearch='';
	}
	elseif ($p1 == 'Go')
	{
		$acategory['start']=0;
	}	
	if ($acategory['start'] == '') 
	{
		$acategory['start'] = 0;
	}
	if ($p1=='Next') 
	{
		$acategory['start']  +=  20;
	}
	if ($p1=='Previous') 
	{
		$acategory['start'] -= 20;
	}
	if ($acategory['start'] < 0) $acategory['start']=0;	
	
	$q = "select * from category ";
	if ($xSearch != '')
	{
		$q .= " where $searchby ilike '%$xSearch%' ";
	}
	
	if ($acategory['sortby'] == '' )
	{
		$acategory['sortby'] = 'category_code, category';
	}
	$sortby = $acategory['sortby'];
	$start = $acategory['start'];
	$q .= " order by $sortby offset $start limit 20";

	$qr = @pg_query($q) or die (pg_errormessage());
	$ctr = $c;
	while ($r = pg_fetch_object($qr))
	{
		$ctr++;
		
	?>
    <tr  bgcolor=<?= ($r->enable=='N')? '#FFCCCC' :'#FFFFFF';?>> 
      <td  align=right nowrap><font size=1> 
        <input type="hidden" name="category_id[]" size="5" value="<?= $r->category_id;?>">
        <? 
	  echo "$ctr."; 
	  if ($p1!='Insert')
	  {
         echo "<input type='checkbox' name='mark[]' value='$ctr' id='m$ctr'>";
	  }
	  ?>
        </font> </td>
      <td> <input name="category[]" type="text" id="<?='t'.$ctr;?>"  onChange="vChk(this)" value="<?= $r->category;?>" size="40"> 
      </td>
      <td><input name="category_code[]" type="text" id="<?='t'.$ctr;?>" onChange="vChk(this)" value="<?= $r->category_code;?>" size="5" maxlength="5"></td>
      <td><input name="seqn[]" type="text"  id="<?='t'.$ctr;?>"  onChange="vChk(this)" value="<?= $r->seqn;?>" size="8" maxlength="8" ></td>
      <td> <select name="department[]"  onChange="vChk(this)"   id="<?='t'.$ctr;?>" >
          <option value="G" <?=($r->department=='G' ? 'selected' : '');?>>Grocery</option>
          <option value="D" <?=($r->department=='D' ? 'selected' : '');?>>Dry 
          Goods</option>
        </select> </td>
      <td> 
        <?= lookUpAssoc('enable[]',array("Yes"=>"Y","No"=>"N"),$r->enable);?>
      </td>
    </tr>
    <?
	}
	?>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="6" nowrap><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        <input type="submit" name="p1" value="Save Checked">
        </font> </td>
    </tr>
  </table>
</form>
<div align="center">
	  	<img src='../graphics/redarrow_left.gif'>
		<a href="javascript: document.getElementById('f1').action='?p=category&p1=Previous'; document.getElementById('f1').submit()"> Previous</a>
        <b>|</b> 
        <a href="javascript: document.getElementById('f1').action='?p=category&p1=Next'; document.getElementById('f1').submit()"> Next </a>
		<img src='../graphics/redarrow_right.gif'> 
</div>		
