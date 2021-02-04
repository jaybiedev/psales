<script>
function vChk(t)
{
	var id = t.id
	var n = id.substring(1)
	document.getElementById('m'+n).checked =1;
}
</script>
<?

$href = '?p=account_type';

/*if (!chkRights2("account_type","mview",$ADMIN['admin_id']))
{
	message("You have no permission in this account_type...");
	exit;
}
*/
if (!session_is_registered('aaccount_type'))
{
	session_register('aaccount_type');
	$aaccount_type=array();
}

/*
if ($p1=="Save Checked" && !chkRights2("account_type","madd",$ADMIN['admin_id']))
	{
		message("You have no permission to modify or add...");
	}
*/	
if ($p1=="Save Checked")
{

	$ctr=0;
	while ($ctr < count($mark))
	{
		$c = $mark[$ctr]-1;
		if ($account_type[$c]!='')
		{
			if ($cdisc1[$c] == '') $cdisc1[$c] = 0;
			if ($sdisc1[$c] == '') $sdisc1[$c] = 0;
			if ($percent_income[$c] == '') $percent_income[$c] = 0;

			if ($account_type_id[$c] == '')
			{
				$q = "insert into account_type (enable, account_type_code, account_type)
						values ('".$enable[$c]."','".$account_type_code[$c]."','".$account_type[$c]."')";
				$qr = @pg_query($q) or message (pg_errormessage().$q);
				//if (pg_errno() == 1062)
				//{
				//	message("account_type name already exists...");
				//}		
			}
			else
			{
				@pg_query("update account_type set
						enable='".$enable[$c]."',
						account_type_code='".$account_type_code[$c]."',
						account_type='".$account_type[$c]."'
					where
						account_type_id='".$account_type_id[$c]."'") or 
					message (pg_errormessage());
			}			
		}
		$ctr++;
		
		if ($xSearch == '') $xSearch = $account_type[$c];
	} 
	$aaccount_type['status']='SAVED';
}
?>
<form name="form1" method="post" action="">
  <table width="70%" border="0" cellspacing="1" cellpadding="2" bgcolor="#FFFFFF" align="center">
    <tr bgcolor="#FFFFFF"> 
      <td height="28" colspan="6" nowrap><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Find 
        <input type="text" name="xSearch" value="<?= $xSearch;?>">
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
        </font>
        <hr color="#993300"></td>
    </tr>
    <tr bgcolor="#C8D7E6"> 
      <td height="27" colspan="9" background="../graphics/table0_horizontal.PNG"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><b><font color="#FFFFFF">Setup 
        Account Type</font> <a name="top"></a></b></font></td>
    </tr>
    <tr bgcolor="#E9E9E9"> 
      <td width="11%"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">#</font></b></td>
      <td width="36%" valign="bottom" nowrap><a href="<?=$href.'&sort=account_type&start=$start&xSearch=$xSearch';?>"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Account_type</font></b></a></td>
      <td width="5%" valign="bottom" nowrap><a href="<?=$href.'&sort=account_type_code&start=$start&xSearch=$xSearch';?>"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Code</font></b></a></td>
      <td width="48%" valign="bottom" nowrap><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        <input type="hidden" name="sort" size="5" value="<?= $sort;?>">
        </font><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Enabled</font></b></td>
    </tr>
    <?
	if ($p1=='Insert')
	{
		$aaccount_type['status']='INSERT';
		$c=0;
		while ($c < $insertcount)
		{
			$c++;
	?>
    <tr bgcolor="#FFFFFF"> 
      <td align=right nowrap> <font size=1> 
        <?= $c;?>
        <input type="checkbox" name="mark[]" value="<?= $c;?>" id="<?= 'm'.$c;?>">
        <input name="account_type_id[]" type="hidden" id="account_type_id[]" size="5">
        </font> </td>
      <td> <input type="text" name="account_type[]" size="40"  onChange="vChk(this)" id="<?='t'.$c;?>"> 
      </td>
      <td><input name="account_type_code[]" type="text" id="<?='t'.$c;?>"  onChange="vChk(this)" size="5" maxlength="5"> 
      </td>
      <td> 
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
        Categories</font></td>
    </tr>
    <?
	} //if insert
	else
	{
		$aaccount_type['status']='LIST';
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
	$q = "select * from account_type ";
	if ($xSearch != '')
	{
		$q .= " where account_type like '$xSearch%' ";
	}
	
	if ($sort == '' || $sort=='account_type')
	{
		$sort = 'account_type';
	}
	$q .= " order by $sort offset $start limit 10";

	$qr = pg_query($q) or die (pg_errormessage());
	$ctr = $c;
	while ($r = pg_fetch_object($qr))
	{
		$ctr++;
		
	?>
    <tr  bgcolor=<?= ($r->enable=='N')? '#FFCCCC' :'#FFFFFF';?>> 
      <td  align=right nowrap><font size=1> 
        <input type="hidden" name="account_type_id[]" size="5" value="<?= $r->account_type_id;?>">
        <? 
	  echo "$ctr."; 
	  if ($p1!='Insert')
	  {
         echo "<input type='checkbox' name='mark[]' value='$ctr' id='m$ctr'>";
	  }
	  ?>
        </font> </td>
      <td> <input name="account_type[]" type="text" id="<?='t'.$ctr;?>"  onChange="vChk(this)" value="<?= $r->account_type;?>" size="40"> 
      </td>
      <td><input name="account_type_code[]" type="text" id="<?='t'.$ctr;?>" onChange="vChk(this)" value="<?= $r->account_type_code;?>" size="5" maxlength="5"> 
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
<?
	  	echo "<img src='../graphics/redarrow_left.gif'><a href='?p=account_type&p1=Previous&start=$start&sort=$sort&xSearch=$xSearch'> Previous</a>";
		?>
        <b>|</b> 
        <?
	  	echo "<a href='?p=account_type&p1=Next&start=$start&sort=$sort&xSearch=$xSearch'> Next </a><img src='../graphics/redarrow_right.gif'> ";
		?>
</div>		
