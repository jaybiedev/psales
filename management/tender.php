<script>
function vChk(t)
{
	var id = t.id
	var n = id.substring(1)

	document.getElementById('m'+n).checked =1;
	//var mid = eval("this.form1.m"+n)
	//mid.checked = true
}
</script>
<?

$href = '?p=tender';

/*if (!chkRights2("tender","mview",$ADMIN['admin_id']))
{
	message("You have no permission in this tender...");
	exit;
}
*/
if (!session_is_registered('atender'))
{
	session_register('atender');
	$atender=array();
}

/*
if ($p1=="Save Checked" && !chkRights2("tender","madd",$ADMIN['admin_id']))
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
		if ($tender[$c]!='')
		{
			if ($service[$c ] == '') $service[$c ] = 0;

			if ($tender_id[$c] == '')
			{
				$q = "insert into tender (enable, bankable, tender_type, service, tender_code, seq, tender)
							values ('".$enable[$c]."','".$bankable[$c]."','".$tender_type[$c]."','".$service[$c]."',
							'".$tender_code[$c]."','".$seq[$c]."','".$tender[$c]."')";
				$qr = @pg_query($q) or message (pg_errormessage());
				if (pg_errormessage() == 1062)
				{
					message("tender name already exists...");
				}		
			}
			else
			{
				@pg_query("update tender set
						enable='".$enable[$c]."',
						bankable='".$bankable[$c]."',
						tender_type='".$tender_type[$c]."',
						service='".$service[$c]."',
						seq='".$seq[$c]."',
						tender_code = '".$tender_code[$c]."',
						tender='".$tender[$c]."'
					where
						tender_id='".$tender_id[$c]."'") or message (pg_errormessage());
			}			
		}
		$ctr++;
	} 
	$atender['status']='SAVED';
}
?>
<form name="form1" method="post" action="">
  <table width="90%" border="0" cellspacing="1" cellpadding="2" bgcolor="#FFFFFF" align="center">
    <tr bgcolor="#FFFFFF"> 
      <td height="28" colspan="7" nowrap><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Find 
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
	</table>
  <table width="50%" border="0" cellspacing="1" cellpadding="2" bgcolor="#E9E9E9" align="center">
    <tr bgcolor="#C8D7E6"> 
      <td height="27" colspan="12" background="../graphics/table0_horizontal.PNG"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><b>Setup 
        Tender Types<a name="top"></a></b></font></td>
    </tr>
    <tr bgcolor="#E9E9E9"> 
      <td width="4%"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">#</font></b></td>
      <td nowrap><a href="<?=$href.'&sort=tender&start=$start&xSearch=$xSearch';?>"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Tender</font></b></a></td>
      <td nowrap><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Bankable</font></b></td>
      <td nowrap><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Type</font></b></td>
      <td nowrap><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Icon</font></b></td>
      <td align="center" nowrap><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Service<br>
        Charge</font></b></td>
      <td align="center" nowrap><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Seq</font></b></td>
      <td nowrap><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        </font><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Enabled</font></b><b></b></td>
    </tr>
    <?
	if ($p1=='Insert')
	{
		$atender['status']='INSERT';
		$c=0;
		while ($c < $insertcount)
		{
			$c++;
	?>
    <tr bgcolor="#FFFFFF"> 
      <td align=right nowrap> <font size=1> 
        <?= $c;?>
        <input type="checkbox" name="mark[]" value="<?= $c;?>" id="<?= 'm'.$c;?>">
        <input name="tender_id[]" type="hidden"  id="<?= 'm'.$c;?>" size="5">
        </font> </td>
      <td> <input name="tender[]" type="text" id="<?='t'.$c;?>"  onChange="vChk(this)" size="30" maxlength="40"> 
      </td>
      <td> 
        <?= lookUpAssoc('bankable[]',array("Yes"=>"Y","No"=>"N"),'');?>
      </td>
      <td> 
        <?= lookUpAssoc('tender_type[]',array("Cash"=>"C","Check"=>"K","Bankcard"=>"B","A/R"=>"A","Gift Check"=>"G","Personnel Incentive"=>"P"),'');?>
      </td>
      <td><input name="tender_code[]" type="text"  id="<?= 'm'.$c;?>"  onChange="vChk(this)" size="5" maxlength="5"></td>
      <td nowrap><input type="text" name="service[]" size="4"  onChange="vChk(this)"  id="<?='t'.$c;?>"  >
        %</td>
      <td><input type="text" name="seq[]" size="4"  onChange="vChk(this)"  id="<?='t'.$c;?>"  ></td>
      <td> 
        <?= lookUpAssoc('enable[]',array("Yes"=>"Y","No"=>"N"),'');?>
      </td>
    </tr>
    <?
		}
		?>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="10" height="2" valign="bottom">&nbsp;</td>
    </tr>
    <tr bgcolor="#E9E9E9"> 
      <td colspan="8" height="20"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Saved 
        Categories</font></td>
    </tr>
    <?
	} //if insert
	else
	{
		$atender['status']='LIST';
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
	$q = "select * from tender ";
	if ($xSearch != '')
	{
		$q .= " where tender like '$xSearch%' ";
	}
	
	if ($sort == '' || $sort=='seq')
	{
		$sort = 'tender';
	}
	$q .= " order by $sort offset $start limit 10";

	$qr = @pg_query($q) or message (pg_errormessage());
	$ctr = $c;
	while ($r = pg_fetch_object($qr))
	{
		$ctr++;
		
	?>
    <tr  bgcolor=<?= ($r->enable=='N')? '#FFCCCC' :'#FFFFFF';?>> 
      <td  align=right nowrap><font size=1> 
        <input type="hidden" name="tender_id[]" size="5" value="<?= $r->tender_id;?>">
        <? 
	  echo "$ctr."; 
	  if ($p1!='Insert')
	  {
         echo "<input type='checkbox' name='mark[]' value='$ctr' id='m$ctr'>";
	  }
	  ?>
        </font> </td>
      <td> <input name="tender[]" type="text" id="<?='t'.$ctr;?>"  onChange="vChk(this)" value="<?= $r->tender;?>" size="30" maxlength="40"> 
      </td>
      <td> 
        <?= lookUpAssoc('bankable[]',array("Yes"=>"Y","No"=>"N"),$r->bankable);?>
      </td>
      <td> 
        <?= lookUpAssoc('tender_type[]',array("Cash"=>"C","Check"=>"K","Bankcard"=>"B","A/R"=>"A","Gift Check"=>"G","Personnel Incentive"=>"P"),$r->tender_type);?>
      </td>
      <td><input name="tender_code[]" type="text" id="<?='t'.$ctr;?>"  onChange="vChk(this)" value="<?= $r->tender_code;?>" size="5" 
maxlength="40"  ></td>
      <td nowrap><input type="text" name="service[]" size="4"  onChange="vChk(this)"  id="<?='t'.$ctr;?>" value="<?=$r->service;?>">
        %</td>
      <td><input name="seq[]" type="text"  id="<?='t'.$ctr;?>"  onChange="vChk(this)" value="<?= $r->seq;?>" size="4"  ></td>
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
<?
	  	echo "<img src='graphics/redarrow_left.gif'><a href='?p=tender&p1=Previous&start=$start&sort=$sort&xSearch=$xSearch'> Previous</a>";
		?>
        <b>|</b> 
        <?
	  	echo "<a href='?p=tender&p1=Next&start=$start&sort=$sort&xSearch=$xSearch'> Next </a><img src='graphics/redarrow_right.gif'> ";
		?>
</div>		
