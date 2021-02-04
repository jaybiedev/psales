<script>
function vChk(t)
{
	var id = t.id
	var n = id.substring(1)

	document.getElementById("m"+n).checked = true;
//	var mid = eval("this.form1.m"+n)
//	mid.checked = true
}
</script>
<?
$href = '?p=discount';

if (!chkRights2("discount","mview",$ADMIN['admin_id'] ) )
{
	message("You have no permission in this discount...");
	exit;
}

if (!session_is_registered('adiscount'))
{
	session_register('adiscount');
	$adiscount=array();
}


if ($p1=="Save Checked" && !chkRights2("discount","madd",$ADMIN['admin_id']))
{
	message("You have no permission to modify or add...");
}
if ($p1=="Save Checked")
{

	$ctr=0;
	while ($ctr < count($mark))
	{
		$c = $mark[$ctr]-1;
		if ($discount_percent[$c] == '') $discount_percent[$c] = 0;
		if ($discount_type[$c]!='')
		{

			if ($discount_id[$c] == '')
			{
				$q = "insert into discount(enable, discount_type, discount_code, discount_percent)
					values
					('".$enable[$c]."','".$discount_type[$c]."','".$discount_code[$c]."','".$discount_percent[$c]."')";
				$qr = pg_query($q) or message (pg_errormessage());
			}
			else
			{
				pg_query("update discount set
						enable='".$enable[$c]."',
						discount_type='".$discount_type[$c]."',
						discount_code = '".$discount_code[$c]."',
						discount_percent = '".$discount_percent[$c]."'
					where
						discount_id='".$discount_id[$c]."'") or message (pg_errormessage());
			}			
		}
		$ctr++;
	} 
	$adiscount['status']='SAVED';
}
?>
<br>
<form name="form1" method="post" action="">
  <table width="60%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr> 
      <td nowrap><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Find 
        <input type="text" name="xSearch" value="<?= $xSearch;?>">
        <input name="p1" type="submit" id="p1" value="Go">
        Insert 
        <select name="insertcount">
          <option value="5">5</option>
          <option value="10">10</option>
          <option value="15">15</option>
          <option value="20">20</option>
        </select>
        <input name="p1" type="submit" id="p1" value="Insert">
        <input name="p1" type="submit" id="p1" value="List">
        <input name="p1" type="button" id="p1" onClick="window.location='?p='" value="Close">
        </font>
        <hr color="#CC3300">
        <font face="Verdana, Arial, Helvetica, sans-serif" size="2">&nbsp; </font></td>
    </tr>
  </table>
  <table width="60%" border="0" cellspacing="1" cellpadding="2" bgcolor="#EFEFEF" align="center">
    <tr bgcolor="#C8D7E6"> 
      <td colspan="5" height="27" background="../graphics/table0_horizontal.PNG"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><b> 
        <img src="../graphics/mail.gif" width="16" height="17"> Discount Setup 
        <a name="top"></a></b></font></td>
    </tr>
    <tr bgcolor="#E9E9E9"> 
      <td width="8%"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">#</font></b></td>
      <td width="20%" nowrap><a href="<?=$href.'&sort=discount_type&start=$start&xSearch=$xSearch';?>"><b><font face="Verdana, Arial, 
Helvetica, sans-serif" size="2" color="#000000">Discount</font></b></a></td>
      <td nowrap><a href="<?=$href.'&sort=discount_code&start=$start&xSearch=$xSearch';?>"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Code</font></b></a></td>
      <td nowrap><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Percent</font></b></td>
      <td nowrap><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        <input type="hidden" name="sort" size="5" value="<?= $sort;?>">
        </font><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Enabled</font></b><b></b></td>
    </tr>
    <?
	if ($p1=='Insert')
	{
		if (!chkRights2("discount","madd",$ADMIN['admin_id']))
		{
			message("You have no permission in this discount...");
			exit;
		}
		$adiscount['status']='INSERT';
		$c=0;
		while ($c < $insertcount)
		{
			$c++;
	?>
    <tr bgcolor="#FFFFFF" onMouseOver="bgColor='#FFFFCC'" onMouseOut="bgColor='#FFFFFF'"> 
      <td align=right nowrap> <font size=1> 
        <?= $c;?>
        <input type="checkbox" name="mark[]" value="<?= $c;?>" id="<?= 'm'.$c;?>">
        <input name="discount_id[]" type="hidden" id="discount_id[]" size="5">
        </font> </td>
      <td> <input type="text" name="discount_type[]" size="30"  onChange="vChk(this)" id="<?='t'.$c;?>"> 
      </td>
      <td ><input name="discount_code[]" type="text" id="<?='k'.$c;?>"  onChange="vChk(this)" size="5" maxlength="5"> 
      </td>
      <td ><input name="discount_percent[]" type="text" id="<?='k'.$c;?>" onChange="vChk(this)" size="5" maxlength="5"></td>
      <td> 
        <?= lookUpAssoc('enable[]',array("Yes"=>"Y","No"=>"N"),'');?>
      </td>
    </tr>
    <?
		}
		?>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="5" height="2" valign="bottom">&nbsp;</td>
    </tr>
    <tr bgcolor="#E9E9E9"> 
      <td colspan="5" height="26"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Saved 
        Areas</font></td>
    </tr>
    <?
	} //if insert
	else
	{
		$adiscount['status']='LIST';
		$c=0;
	}
	if ($p1=='List') 
	{
		$start=0;
		$xSearch='';
	}	
	if ($start == '') $start=0;
	if ($p1=='Next') $start = $start + 10;
	if ($p1=='Previous') $start = $start - 10;
	if ($start < 0) $start=0;	
	$q = "select * from discount";
	if ($xSearch != '')
	{
		$q .= " where discountlike '$xSearch%' ";
	}
	
	if ($sort == '' || $sort=='discount_type')
	{
		$sort = 'discount_type';
	}
	$q .= " order by $sort " ; // limit $start,10";

	$qr = @pg_query($q) or die (pg_errormessage());
	$ctr = $c;
	while ($r = pg_fetch_object($qr))
	{
		$ctr++;
		
	?>
    <tr  bgcolor=<?= ($r->enable=='N')? '#FFCCCC' :'#FFFFFF';?>> 
      <td  align=right><font size=1> 
        <input type="hidden" name="discount_id[]" size="5" value="<?= $r->discount_id;?>">
        <? 
	  echo "$ctr."; 
	  if ($p1!='Insert')
	  {
         echo "<input type='checkbox' name='mark[]' value='$ctr' id='m$ctr'>";
	  }
	  ?>
        </font> </td>
      <td> <input name="discount_type[]" type="text" id="<?='t'.$ctr;?>"  onChange="vChk(this)" value="<?= $r->discount_type;?>" size="30"> 
      </td>
      <td><input name="discount_code[]" type="text" id="<?='k'.$ctr;?>"  onChange="vChk(this)" value="<?=$r->discount_code;?>" size="5" maxlength="5"> 
      </td>
      <td><input name="discount_percent[]" type="text" id="<?='k'.$ctr;?>"  onChange="vChk(this)" value="<?= $r->discount_percent;?>" size="5" maxlength="5"></td>
      <td> 
        <?= lookUpAssoc('enable[]',array("Yes"=>"Y","No"=>"N"),$r->enable);?>
      </td>
    </tr>
    <?
	}
	?>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="5"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        <input type="submit" name="p1" value="Save Checked">
        </font> </td>
    </tr>
  </table>
</form>
<div align="center">
<?
	echo "<img src='../graphics/redarrow_left.gif'><a href='?p=discount&p1=Previous&start=$start&sort=$sort&xSearch=$xSearch'> Previous</a>";
	echo " <b> | </b>";
	echo "<a href='?p=discount&p1=Next&start=$start&sort=$sort&xSearch=$xSearch'> Next </a><img src='../graphics/redarrow_right.gif'> ";
?>	
</div>
