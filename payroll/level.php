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
	.grid {
	font-family: verdana;
	border: #CCCCCC 1px solid;
	font-size: 12px;
	color: #000000
	} 


	.altText {
	background-color: #FFFFFF;
	font-family: verdana;
	border: #CCCCCC 1px solid;
	font-size: 12px;
	color: #000000
	} 
	.autocomplete {
	font-family: verdana;
	border: #CCCCCC 1px solid;
	font-size: 10px;
	color: #000000;
	
	} 
	
	.altNum {
	background-color: #FFFFFF;
	font-family: verdana;
	border: #CCCCCC 1px solid;
	font-size: 12px;
	color: #000000;
	text-align:right
	} 
	
	.altTextArea {
	background-color: #FFFFFF;
	font-family: verdana;
	border: #CCCCCC 1px solid;
	font-size: 12px;
	color: #000000
	} 
	
	SELECT {
	background-color: #FFFFFF;
	font-family: verdana;
	border: #CCCCCC 1px solid;
	font-size: 11px;
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
$href = '?p=level';
require_once('../lib/dbconfig.php');
require_once('../lib/connect.php');

if (!chkRights2("level","mview",$ADMIN['admin_id']))
{
	message("You have no permission in this area...");
	exit;
}

if (!session_is_registered('alevel'))
{
	session_register('alevel');
	$alevel=array();
}


if ($p1=="Save Checked" && !chkRights2("level","madd",$ADMIN['admin_id']))
	{
		message("You have no permission to modify or add...");
	}
if ($p1=="Save Checked")
{

	$ctr=0;

	while ($ctr < count($mark))
	{
		$c = $mark[$ctr]-1;

		if ($ratem[$c] == '') $ratem[$c] =0;
		if ($adwr[$c] == '') $adwr[$c] =0;
		if ($hourly[$c] == '') $hourly[$c] =0;

		if ($level[$c]!='')
		{
			if ($level_id[$c] == '')
			{
				$q = "insert into level (enable, level,ratem, adwr, hourly,pay_category)
					values
						('".$enable[$c]."','".$level[$c]."','".$ratem[$c]."','".$adwr[$c]."','".$hourly[$c]."','".$pay_category[$c]."')";
				$qr = @pg_query($q) or message1 (pg_errormessage());
			}
			else
			{
				$q = "update level set
						enable='".$enable[$c]."',
						ratem='".$ratem[$c]."',
						adwr='".$adwr[$c]."',
						hourly='".$hourly[$c]."',
						pay_category='".$pay_category[$c]."',
						level='".$level[$c]."'
					where
						level_id='".$level_id[$c]."'";
				$qr = @pg_query($q) or message1 (pg_errormessage().$q);
				
			}			
		}
		$ctr++;
	} 
	$alevel['status']='SAVED';
}
elseif ($p1 =='For Checked, Update Employee Salary')
{

	$ctr=0;

	while ($ctr < count($mark))
	{
		$c = $mark[$ctr]-1;
		$pc =$pay_category[$c];
		$rm = $ratem[$c];
		$rd = $adwr[$c];
		$rh = $hourly[$c];
		$lid = $level_id[$c];
		
		$audit ='Level '.$pc.' Salary updated to ratem:'.$rm.';adwr:'.$rd.';rh:'.$rh.' by: '.$ADMIN['username'].' on :'.date('m/dY g:ia');
		$q="update paymast set ratem='$rm', adwr='$rd', hourly='$rh' where pay_category='$pc' and enable='Y'";
		$qr  = @pg_query($q) or message(pg_errormessage().$q);
//		@audit('level', addslashes($q),$audit,$lid);
		if ($qr)
		{
			message1("<br>Updated ".@pg_affected_rows($qr)." employees for Level: ".$level[$c]."<br>");
		}
		$ctr++;
	}
}

?><br>
<form name="form1" method="post" action="">
  <table width="85%" border="0" align="center" cellpadding="1" cellspacing="1">
    <tr> 
      <td nowrap colspan="7"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Find 
        <input type="text" class="altText" name="xSearch" value="<?= $xSearch;?>">
        <input name="p1" type="submit" class="altBtn" id="p1" value="Go">
        Insert </font> <select name="insertcount">
          <option value="5">5</option>
          <option value="10">10</option>
          <option value="15">15</option>
          <option value="20">20</option>
        </select> <input name="p1" type="submit" class="altBtn" id="p1" value="Insert"> 
        <input name="p1" type="submit" class="altBtn" id="p1" value="List"> <input name="p1" type="button" id="p1" onClick="window.location='?p='" value="Close" class="altBtn"> 
        <hr color="#CC3300"> </td>
    </tr>
    <tr background="../graphics/table_horizontal.PNG"> 
      <td height="27" colspan="7" bgcolor="#C8D7E6"  background="../graphics/table_horizontal.PNG"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><b> 
        <img src="../graphics/mail.gif" width="16" height="17"> <font color="#EFEFEF">Salary 
        Level Setup</font> <a name="top"></a></b></font></td>
    </tr>
    <tr> 
      <td width="8%" bgcolor="#E9E9E9"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">#</font></b></td>
      <td width="18%" nowrap bgcolor="#E9E9E9"><a href="<?=$href.'&sort=level&start=$start&xSearch=$xSearch';?>"><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Level</font></a></td>
      <td width="14%" nowrap bgcolor="#E9E9E9"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Category</font></td>
      <td width="4%" nowrap bgcolor="#E9E9E9"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Month</font></td>
      <td width="3%" nowrap bgcolor="#E9E9E9"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Daily</font></td>
      <td width="3%" nowrap bgcolor="#E9E9E9"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Hourly</font></td>
      <td width="50%" nowrap bgcolor="#E9E9E9"><b><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        <input type="hidden" name="sort" size="5" value="<?= $sort;?>">
        </font></b><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Enabled</font></td>
    </tr>
    <?
	if ($p1=='Insert')
	{
		$alevel['status']='INSERT';
		$c=0;
		while ($c < $insertcount)
		{
			$c++;
	?>
    <tr onMouseOver="bgColor='#FFFFCC'" onMouseOut="bgColor='#FFFFFF'"> 
      <td align=right nowrap bgcolor="#FFFFFF"> <font size=1> 
        <?= $c;?>
        <input type="checkbox" name="mark[]" value="<?= $c;?>" id="<?= 'm'.$c;?>">
        <input name="level_id[]" type="hidden" id="level_id[]" size="5">
        </font> </td>
      <td bgcolor="#FFFFFF"> <input type="text" class="altText" name="level[]" size="30"  onChange="vChk(this)" id="<?='t'.$c;?>"> 
      </td>
      <td bgcolor="#FFFFFF" ><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= lookUpAssoc('pay_category[]',array('Regular Monthly'=>'1','Regular Daily'=>'2','ProB Monthly'=>'3','ProB Daily'=>'4','Contractual'=>'5','Daily Casual'=>'6'),'');?>
        </font></td>
      <td bgcolor="#FFFFFF" ><input type="text" class="altText" name="ratem[]" size="10"  onChange="vChk(this)"  id="<?='t'.$c;?>" style="text-align:right"></td>
      <td bgcolor="#FFFFFF" ><input type="text" class="altText" name="adwr]" size="10"  onChange="vChk(this)"  id="<?='t'.$c;?>" style="text-align:right"></td>
      <td bgcolor="#FFFFFF" ><input type="text" class="altText" name="hourly[]" size="10"  onChange="vChk(this)"  id="<?='t'.$c;?>" style="text-align:right"></td>
      <td bgcolor="#FFFFFF"> 
        <?= lookUpAssoc('enable[]',array("Yes"=>"Y","No"=>"N"),'');?>
      </td>
    </tr>
    <?
		}
		?>
    <tr> 
      <td height="2" colspan="7" valign="bottom" bgcolor="#FFFFFF">&nbsp;</td>
    </tr>
    <tr> 
      <td height="26" colspan="7" bgcolor="#E9E9E9"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Saved 
        Levels</font></td>
    </tr>
    <?
	} //if insert
	else
	{
		$alevel['status']='LIST';
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
	$q = "select * from level ";
	if ($xSearch != '')
	{
		$q .= " where level like '$xSearch%' ";
	}
	
	if ($sort == '' || $sort=='level')
	{
		$sort = 'level';
	}
	$q .= " order by $sort " ; // limit $start,10";

	$qr = pg_exec($q) or die (pg_errormessage());
	$ctr = $c;
	while ($r = pg_fetch_object($qr))
	{
		$ctr++;
	?>
    <tr> 
      <td  align=right nowrap bgcolor="<?= ($r->enable=='N')? '#FFCCCC' :'';?>"><font size=1> 
        <input type="hidden" name="level_id[]" size="5" value="<?= $r->level_id;?>">
        <? 
	  echo "$ctr."; 
	  if ($p1!='Insert')
	  {
         echo "<input type='checkbox' name='mark[]' value='$ctr' id='m$ctr'>";
	  }
	  ?>
        </font> </td>
      <td bgcolor="<?= ($r->enable=='N')? '#FFCCCC' :'#FFFFFF';?>"> <input name="level[]" type="text" class="altText" id="<?='t'.$ctr;?>"  onChange="vChk(this)" value="<?= $r->level;?>" size="30"> 
      </td>
      <td bgcolor="<?= ($r->enable=='N')? '#FFCCCC' :'#FFFFFF';?>"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= lookUpAssoc('pay_category[]',array('Regular Monthly'=>'1','Regular Daily'=>'2','ProB Monthly'=>'3','ProB Daily'=>'4','Contractual'=>'5','Daily Casual'=>'6'),$r->pay_category);?>
        </font></td>
      <td bgcolor="<?= ($r->enable=='N')? '#FFCCCC' :'#FFFFFF';?>"><input type="text" class="altText" name="ratem[]" size="10"  onChange="vChk(this)"  id="<?='t'.$ctr;?>" value="<?= $r->ratem;?>" style="text-align:right"></td>
      <td bgcolor="<?= ($r->enable=='N')? '#FFCCCC' :'#FFFFFF';?>"><input type="text" class="altText" name="adwr[]" size="10"  onChange="vChk(this)"  id="<?='t'.$ctr;?>" value="<?= $r->adwr;?>" style="text-align:right"></td>
      <td bgcolor="<?= ($r->enable=='N')? '#FFCCCC' :'#FFFFFF';?>"><input type="text" class="altText" name="hourly[]" size="10"  onChange="vChk(this)"  id="<?='t'.$ctr;?>" value="<?= $r->hourly;?>" style="text-align:right"></td>
      <td nowrap bgcolor="<?= ($r->enable=='N')? '#FFCCCC' :'#FFFFFF';?>"> 
        <?= lookUpAssoc('enable[]',array("Yes"=>"Y","No"=>"N"),$r->enable);?>
      </td>
    </tr>
    <?
	}
	?>
    <tr> 
      <td colspan="7"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        <input type="submit" class="altBtn" name="p1" value="Save Checked">
        <input name="p1" type="submit" class="altBtn" id="p1" onMouseOver="" value="For Checked, Update Employee Salary">
        </font> </td>
    </tr>
  </table>
</form>
<div align="center">
<?
	echo "<img src='../graphics/redarrow_left.gif'><a href='?p=level&p1=Previous&start=$start&sort=$sort&xSearch=$xSearch'> Previous</a>";
	echo " <b> | </b>";
	echo "<a href='?p=level&p1=Next&start=$start&sort=$sort&xSearch=$xSearch'> Next </a><img src='../graphics/redarrow_right.gif'> ";
?>	
</div>
