<script>
function vChk(t)
{
	var id = t.id
	var n = id.substring(1)
	document.getElementById('m'+n).checked =1;
}
</script>
<STYLE TYPE="text/css">
<!--
	.altText {
	background-color: #FFFFFF;
	font-family: verdana;
	border: #CCCCCC 1px solid;
	padding: 1px;
	font-size: 12px;
	color: #000000
	} 
	
	.altNum {
	background-color: #FFFFFF;
	font-family: verdana;
	border: #CCCCCC 1px solid;
	font-size: 12px;
	color: #000000;
	text-align:right
	} 
	
	SELECT {
	background-color: #FFFFFF;
	font-family: verdana;
	border: #CCCCCC 1px solid;
	font-size: 10px;
	margin:0px;
	color: #000000
	} 			

	.altBtn {
	background-color: #CFCFCF;
	font-family: verdana;
	font-size: 11px;
	padding: 1px;
	margin: 0px;
	color: #1F016D
	} 
-->
</STYLE>
<?

$href = '?p=bankcard';

/*if (!chkRights2("bankcard","mview",$ADMIN['admin_id']))
{
	message("You have no permission in this bankcard...");
	exit;
}
*/
if (!session_is_registered('abankcard'))
{
	session_register('abankcard');
	$abankcard=array();
}

/*
if ($p1=="Save Checked" && !chkRights2("bankcard","madd",$ADMIN['admin_id']))
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
		if ($bankcard[$c]!='')
		{
			if ($cdisc1[$c] == '') $cdisc1[$c] = 0;
			if ($sdisc1[$c] == '') $sdisc1[$c] = 0;
			if ($percent_income[$c] == '') $percent_income[$c] = 0;

			if ($bankcard_id[$c] == '')
			{
				$q = "insert into bankcard (enable, bankcard_name, bankcard)
						values ('".$enable[$c]."','".$bankcard_name[$c]."','".$bankcard[$c]."')";
				$qr = @pg_query($q) or message (pg_errormessage().$q);
				//if (pg_errno() == 1062)
				//{
				//	message("bankcard name already exists...");
				//}		
			}
			else
			{
				@pg_query("update bankcard set
						enable='".$enable[$c]."',
						bankcard_name='".$bankcard_name[$c]."',
						bankcard='".$bankcard[$c]."'
					where
						bankcard_id='".$bankcard_id[$c]."'") or 
					message (pg_errormessage());
			}			
		}
		$ctr++;
		
		if ($xSearch == '') $xSearch = $bankcard[$c];
	} 
	$abankcard['status']='SAVED';
}
?>
<form name="form1" method="post" action="">
  <table width="70%" border="0" align="center" cellpadding="2" cellspacing="1">
    <tr> 
      <td height="28" colspan="7" nowrap><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Find 
        <input type="text" class="altText" name="xSearch" value="<?= $xSearch;?>">
        <input  type="submit" class="altBtn" name="p1" value="Go">
        Insert 
        <select name="insertcount">
          <option value="5">5</option>
          <option value="10">10</option>
          <option value="15">15</option>
          <option value="20">20</option>
        </select>
        <input  type="submit" class="altBtn" name="p1" value="Insert">
        <input  type="submit" class="altBtn" name="p1" value="List">
        <input type="button" name="p1" value="Close" onClick="window.location='?p='" class="altBtn">
        </font> <hr color="#993300"></td>
    </tr>
    <tr> 
      <td height="27" colspan="10" background="../graphics/table0_horizontal.PNG" bgcolor="#C8D7E6"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><b><font color="#FFFFFF">Setup 
        Customer Bankcards</font> <a name="top"></a></b></font></td>
    </tr>
    <tr> 
      <td width="11%" bgcolor="#E9E9E9"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">#</font></b></td>
      <td width="18%" valign="bottom" nowrap bgcolor="#E9E9E9"><a href="<?=$href.'&sort=bankcard&start=$start&xSearch=$xSearch';?>"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Bankcard</font></b></a></td>
      <td width="20%" valign="bottom" nowrap bgcolor="#E9E9E9"><a href="<?=$href.'&sort=bankcard_name&start=$start&xSearch=$xSearch';?>"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Name</font></b></a></td>
      <td width="20%" valign="bottom" nowrap bgcolor="#E9E9E9"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Company</font></b></td>
      <td width="31%" valign="bottom" nowrap bgcolor="#E9E9E9"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        <input type="hidden" name="sort" size="5" value="<?= $sort;?>">
        </font><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Enabled</font></b></td>
    </tr>
    <?
	if ($p1=='Insert')
	{
		$abankcard['status']='INSERT';
		$c=0;
		while ($c < $insertcount)
		{
			$c++;
	?>
    <tr> 
      <td align=right nowrap> <font size=1> 
        <?= $c;?>
        <input type="checkbox" name="mark[]" value="<?= $c;?>" id="<?= 'm'.$c;?>">
        <input name="bankcard_id[]" type="hidden" id="bankcard_id[]" size="5">
        </font> </td>
      <td> <input type="text" class="altText" name="bankcard[]" size="20"  onChange="vChk(this)" id="<?='t'.$c;?>"> 
      </td>
      <td><input name="bankcard_name[]" type="text" class="altText" id="<?='t'.$c;?>"  onChange="vChk(this)" size="25" maxlength="25"> 
      </td>
      <td><?= lookUpTable2('tender_id[]','tender','tender_id','tender','');?></td>
      <td> 
        <?= lookUpAssoc('enable[]',array("Yes"=>"Y","No"=>"N"),'');?>
      </td>
    </tr>
    <?
		}
		?>
    <tr> 
      <td height="2" colspan="5" valign="bottom">&nbsp;</td>
    </tr>
    <tr> 
      <td height="20" colspan="5" bgcolor="#E9E9E9"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Saved 
        Bankcards</font></td>
    </tr>
    <?
	} //if insert
	else
	{
		$abankcard['status']='LIST';
		$c=0;
	}
	if ($p1=='List') 
	{
		$start=0;
		$xSearch='';
	}	
	if ($start == '') $start=0;
	if ($p1=='Next') $start = $start + 15;
	if ($p1=='Previous') $start = $start - 15;
	if ($start < 0) $start=0;	
	$q = "select * from bankcard ";
	if ($xSearch != '')
	{
		$q .= " where bankcard like '$xSearch%' ";
	}
	
	if ($sort == '' || $sort=='bankcard')
	{
		$sort = 'bankcard';
	}
	$q .= " order by $sort offset $start limit 15";

	$qr = pg_query($q) or die (pg_errormessage());
	$ctr = $c;
	while ($r = pg_fetch_object($qr))
	{
		$ctr++;
		if ($r->tender_id == '') $r->tender_id = '0';
		else
		{
			$tender_id = $r->tender_id;
		}
		
	?>
    <tr bgcolor="<?= ($r->enable=='N')? '#FFCCCC' :'#FFFFFF';?>"> 
      <td  align=right nowrap ><font size=1> 
        <input type="hidden" name="bankcard_id[]" size="5" value="<?= $r->bankcard_id;?>">
        <? 
	  echo "$ctr."; 
	  if ($p1!='Insert')
	  {
         echo "<input type='checkbox' name='mark[]' value='$ctr' id='m$ctr'>";
	  }
	  ?>
        </font> </td>
      <td bgcolor="<?= ($r->enable=='N')? '#FFCCCC' :'';?>"> <input name="bankcard[]" type="text" class="altText" id="<?='t'.$ctr;?>"  onChange="vChk(this)" value="<?= $r->bankcard;?>" size="20"> 
      </td>
      <td bgcolor="<?= ($r->enable=='N')? '#FFCCCC' :'';?>"><input name="bankcard_name[]" type="text" class="altText" id="<?='t'.$ctr;?>" onChange="vChk(this)" value="<?= $r->bankcard_name;?>" size="25" maxlength="25"> 
      </td>
      <td bgcolor="<?= ($r->enable=='N')? '#FFCCCC' :'';?>">
        <?= lookUpTable2('tender_id[]','tender','tender_id','tender',$tender_id);?>
      </td>
      <td bgcolor="<?= ($r->enable=='N')? '#FFCCCC' :'';?>"> 
        <?= lookUpAssoc('enable[]',array("Yes"=>"Y","No"=>"N"),$r->enable);?>
      </td>
    </tr>
    <?
	}
	?>
    <tr> 
      <td colspan="5" nowrap><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        <input  type="submit" class="altBtn" name="p1" value="Save Checked">
        </font> </td>
    </tr>
  </table>
</form>
<div align="center">
<?
	  	echo "<img src='../graphics/redarrow_left.gif'><a href='?p=bankcard&p1=Previous&start=$start&sort=$sort&xSearch=$xSearch'> Previous</a>";
		?>
        <b>|</b> 
        <?
	  	echo "<a href='?p=bankcard&p1=Next&start=$start&sort=$sort&xSearch=$xSearch'> Next </a><img src='../graphics/redarrow_right.gif'> ";
		?>
</div>		
