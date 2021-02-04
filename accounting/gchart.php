<script>
function vChk(t)
{
	var id = t.id
	var n = id.substr(1)
	document.getElementById('m'+n).checked =1;
}
</script>
<STYLE TYPE="text/css">
<!--

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
$href = '?p=gchart';
$aclass = array();
$aclass = array('Income'=>'I0','Other Income'=>'I1','Expense'=>'E0','Asset'=>'A0','Fixed Asset'=>'A1','Other Assets'=>'A2','Liabilities'=>'L0','Long Term Liabilities'=>'L1','Retained Earnings'=>'R0');
$aclassH = array('All'=>'','Income'=>'I0','Other Income'=>'I1','Expense'=>'E0','Asset'=>'A0','Fixed Asset'=>'A1','Other Assets'=>'A2','Liabilities'=>'L0','Long Term Liabilities'=>'L1','Retained Earnings'=>'R0');
//$aclassH = array('All'=>'','Income'=>'I','Expense'=>'E','Asset'=>'A','Liabilities'=>'L','Retained Earnings'=>'R');
/*if (!chkRights2("gchart","mview",$ADMIN['adminId']))
{
	message("You have no permission in this gchart...");
	exit;
}
*/
if (!session_is_registered('agchart'))
{
	session_register('agchart');
	$agchart=array();
}


if ($p1=="Save Checked" && !chkRights2("gchart","madd",$ADMIN['adminId']))
{
		message("You have no permission to modify or add...");
}
	
if ($p1=="Save Checked")
{
	$ctr=0;
	while ($ctr < count($mark))
	{
		$c = $mark[$ctr]-1;

		if ($gchart[$c]!='')
		{
			if ($beginning_balance[$c] == '') $beginning_balance[$c] = 0;
			
			if ($gchart_id[$c] == '')
			{
				$q = "insert into gchart (enable, acode, scode, gchart_type_id, beginning_balance, level, gchart)
							values ('".$enable[$c]."','".$acode[$c]."','".$scode[$c]."','".$gchart_type_id[$c]."','".$beginning_balance[$c]."','".$level[$c]."','".$gchart[$c]."')";
				$qr = @pg_query($q) 	or message1 (pg_errormessage().$q);
				if (@pg_errnum() == 1062)
				{
					message("Account name already exists...");
				}		
			}
			else
			{
				@pg_query("update gchart set
						enable='".$enable[$c]."',
						acode='".$acode[$c]."',
						scode='".$scode[$c]."',
						gchart_type_id='".$gchart_type_id[$c]."',
						level='".$level[$c]."',
						beginning_balance='".$beginning_balance[$c]."',
						gchart='".$gchart[$c]."'
					where
						gchart_id='".$gchart_id[$c]."'") or message (pg_errormessage());
			}			
		}
		$ctr++;
	} 
	$agchart['status']='SAVED';
}
elseif ($p1 == 'sort')
{
	if ($agchart['sort'] == $sort)
	{
		$agchart['sort'] = $sort;
		$agchart['desc']  = 'desc';
	}	
	else
	{
		$agchart['sort'] = $sort;
		$agchart['desc']  = '';
	}	
	$agchart['start'] =0;
}
elseif ($p1 == 'Go' || $p1 == 'List')
{
	$agchart['xSearch'] = $xSearch;
	$agchart['mgchart_type_id'] = $mgchart_type_id;
}
elseif ($p1 == 'mgchart_type_id')
{
	$agchart['mgchart_type_id'] = $mgchart_type_id;
}
if ($agchart['sort'] == '')
{
	$agchart['sort'] = 'gchart_type_code, acode, scode ';
}

$xSearch = $agchart['xSearch'];
$sort = $agchart['sort'].' '.$agchart['desc'];
$mgchart_type_id = $agchart['mgchart_type_id'];

if ($agchart['start'] == '') 
	$start=0;
else
	$start = $agchart['start'];	

?>
<form name="form1" method="post" action="?p=gchart">
  <table width="80%" border="0" cellspacing="1" cellpadding="0" bgcolor="#EFEFEF" align="center">
    <tr background="../graphics/table_horizontal.PNG"> 
      <td height="23" colspan="12" bgcolor="#C8D7E6" background="../graphics/table_horizontal.PNG"> 
        <font face="Verdana, Arial, Helvetica, sans-serif" size="2"><b><img src="../graphics/list3.gif" width="16" height="16"> 
        <font color="#FFFFCC">Setup Chart of Accounts</font> <a name="top"></a></b></font></td>
    </tr>
    <tr> 
      <td height="27" colspan="12" nowrap><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Find 
        <input type="text" class="altText" name="xSearch" value="<?= $agchart['xSearch'];?>">
        Show 
        <select name="mgchart_type_id"  id="mgchart_type_id" style="border: #CCCCCC 1px solid; width:160px">
		<option value="0">All Account Types</option>
          <?

	  	$q = "select * from gchart_type where enable='Y' order by gchart_type_code";
		$qqr = @pg_query($q);
		while ($rr = @pg_fetch_object($qqr))
		{
			if ($agchart['mgchart_type_id'] == $rr->gchart_type_id)
			{
				echo "<option value=$rr->gchart_type_id selected>$rr->gchart_type</option>";
			}
			else
			{
				echo "<option value=$rr->gchart_type_id>$rr->gchart_type</option>";
			}
		}
	  ?>
        </select>
        <input name="p1" type="submit" id="p1" value="Go" class="altBtn">
        Insert </font> 
        <select name="insertcount">
          <option value="5">5</option>
          <option value="10">10</option>
          <option value="15">15</option>
          <option value="20">20</option>
        </select> <input name="p1" type="submit" id="p1" value="Insert" class="altBtn"> 
        <input name="p1" type="submit" id="p1" value="List"  class="altBtn"> <input name="p1" type="button" id="p1" onClick="window.location='?p='" value="Close"  class="altBtn"> 
       </td>
    </tr>
    <tr> 
      <td width="4%" bgcolor="#E9E9E9"><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">#</font></td>
      <td nowrap bgcolor="#E9E9E9"><a href="<?=$href.'&p1=sort&sort=acode';?>"><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Account</font></a></td>
      <td nowrap bgcolor="#E9E9E9"><a href="<?=$href.'&p1=sort&sort=scode';?>"><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Subsidiary</font></a></td>
      <td nowrap bgcolor="#E9E9E9"><a href="<?=$href.'&p1=sort&sort=gchart';?>"><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Description</font></a></td>
      <td nowrap bgcolor="#E9E9E9"><a href="<?=$href.'&p1=sort&sort=gchart_type_id';?>"><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Type</font></a></td>
      <td nowrap bgcolor="#E9E9E9"><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Level</font></td>
      <td nowrap bgcolor="#E9E9E9"><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Beginning</font></td>
      <td nowrap bgcolor="#E9E9E9"><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Enabled</font></td>
    </tr>
    <?
	if ($p1=='Insert')
	{
		$agchart['status']='INSERT';
		$c=0;
		while ($c < $insertcount)
		{
			$c++;
	?>
    <tr> 
      <td align=right nowrap bgcolor="#FFFFFF"> <font size=1> 
        <?= $c;?>
        <input type="checkbox" name="mark[]" value="<?= $c;?>" id="<?= 'm'.$c;?>">
        <input name="gchart_id[]" type="hidden"  id="<?='t'.$c;?>" size="5">
        </font> </td>
      <td bgcolor="#FFFFFF"><input type="text" class="altText" name="acode[]" size="8"  onChange="vChk(this)" id="<?='t'.$c;?>"> 
      </td>
      <td bgcolor="#FFFFFF"><input type="text" class="altText" name="scode[]" size="8"  onChange="vChk(this)" id="<?='t'.$c;?>"></td>
      <td bgcolor="#FFFFFF"><input type="text" class="altText" name="gchart[]" size="40"  onChange="vChk(this)" id="<?='t'.$c;?>"></td>
      <td bgcolor="#FFFFFF"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        <select name="gchart_type_id[]"  onChange="vChk(this)" id="<?='t'.$c;?>" style="border: #CCCCCC 1px solid; width:180px">
          <?

	  	$q = "select * from gchart_type where enable='Y' order by gchart_type_code";
		$qqr = @pg_query($q);
		while ($rr = @pg_fetch_object($qqr))
		{
			echo "<option value=$rr->gchart_type_id>$rr->gchart_type</option>";
		}
	  ?>
        </select>
        </font></td>
      <td bgcolor="#FFFFFF"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        <?= lookUpAssoc("level[]",array('Main'=>'0','Sub1'=>'1','Sub2'=>'2','Sub3'=>'3','Detail'=>'9'),'');?>
        </font></td>
      <td bgcolor="#FFFFFF"><input type="text" class="altNum" name="beginning_balance[]" size="12"  onChange="vChk(this)" id="<?='t'.$c;?>"></td>
      <td bgcolor="#FFFFFF"> 
        <?= lookUpAssoc('enable[]',array("Yes"=>"Y","No"=>"N"),'');?>
      </td>
    </tr>
    <?
		}
		?>
    <tr> 
      <td height="2" colspan="10" valign="bottom" bgcolor="#FFFFFF">&nbsp;</td>
    </tr>
    <tr> 
      <td height="26" colspan="8" bgcolor="#E9E9E9"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Saved 
        Categories</font></td>
    </tr>
    <?
	} //if insert
	else
	{
		$agchart['status']='LIST';
		$c=0;
	}
	if ($p1=='List') 
	{
		$start=0;
		$xSearch='';
	}	
	if ($p1=='Next') 
	{
		$start = $start + 15;
	}	
	if ($p1=='Previous') 
	{
		$start = $start - 15;
	}	
	if ($start < 0) $start=0;	
	$agchart['start'] = $start;
	
	$q = "select * from gchart, gchart_type where gchart.gchart_type_id = gchart_type.gchart_type_id";
	if ($xSearch != '')
	{
		$agchart['xSearch'] = $xSearch;
		$q .= " and gchart like '$xSearch%'  ";
	}
	else
	{
		if ($mgchart_type_id !='' and $mgchart_type_id !='0')
		{
			$q .= " and gchart.gchart_type_id ='$mgchart_type_id'";
		}
	}
	
	if ($sort == '' || $sort=='gchart_type_code')
	{
		$sort = 'gchart_type_code, scode, acode';
	}
	$q .= " order by $sort  offset $start limit 15";

	$qr = @pg_query($q) or message1 (pg_errormessage());
	$ctr = $c;
	while ($r = @pg_fetch_object($qr))
	{
		$ctr++;
		
		if ($r->enable == 'N')
		{
			$bgColor = '#C60606';
			$fontColor = '#FFFCCC';
		}
		elseif (substr($r->gchart_type_code,0,1) == 'I')
		{
			$bgColor = '#E4F7F8';
			$fontColor = '#000000';
		}
		elseif (substr($r->gchart_type_code,0,1) == 'E')
		{
			$bgColor = '#FFEAEA';
			$fontColor = '#000000';
		}	
		elseif (substr($r->gchart_type_code,0,1) == 'A')
		{
			$bgColor = '#D9ECEC';
			$fontColor = '#000000';
		}	
		elseif (substr($r->gchart_type_code,0,1) == 'L')
		{
			$bgColor = '#E9E9E9';
			$fontColor = '#000000';
		}	
		else
		{
			$bgColor = '#FFFFFF';
			$fontColor = '#000000';
		}
		
	?>
    <tr onMouseOver="bgColor='#FFFFCC'" onMouseOut="bgColor='<?=$bgColor;?>'"> 
      <td  align=right nowrap bgcolor="<?= $bgColor;?>"><font size=1 color="<?=$fontColor;?>"> 
        <input type="hidden" name="gchart_id[]" size="5" value="<?= $r->gchart_id;?>">
        <? 
	  echo "$ctr."; 
	  if ($p1!='Insert')
	  {
         echo "<input type='checkbox' name='mark[]' value='$ctr' id='m$ctr'>";
	  }
	  ?>
        </font> </td>
      <td bgcolor="<?= $bgColor;?>"><input name="acode[]" type="text" class="altText" id="<?='t'.$ctr;?>"  onChange="vChk(this)" value="<?= $r->acode;?>" size="8" > 
      </td>
      <td bgcolor="<?= $bgColor;?>"><input name="scode[]" type="text" class="altText" id="<?='t'.$ctr;?>"  onChange="vChk(this)" value="<?= $r->scode;?>" size="8" ></td>
      <td bgcolor="<?= $bgColor;?>"><input name="gchart[]" type="text" class="altText"  id="<?='t'.$ctr;?>" onChange="vChk(this)" value="<?= $r->gchart;?>" size="40"></td>
      <td bgcolor="<?= $bgColor;?>"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">
        <select name="gchart_type_id[]"  onChange="vChk(this)"  id="<?='t'.$ctr;?>"  style="border: #CCCCCC 1px solid; width:180px">
          <?

	  	$q = "select * from gchart_type where enable='Y' order by gchart_type_code, gchart_type";
		$qqr = @pg_query($q);
		while ($rr = @pg_fetch_object($qqr))
		{
			if ($rr->gchart_type_id == $r->gchart_type_id )
			{
				echo "<option value=$rr->gchart_type_id selected>$rr->gchart_type</option>";
			}
			else
			{
				echo "<option value=$rr->gchart_type_id>$rr->gchart_type</option>";
			}
		}
	  ?>
        </select>
        </font></td>
      <td bgcolor="<?= $bgColor;?>"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        <?= lookUpAssoc("level[]",array('Main'=>'0','Sub1'=>'1','Sub2'=>'2','Sub3'=>'3','Detail'=>'9'),$r->level);?>
        </font></td>
      <td bgcolor="<?= $bgColor;?>"><input name="beginning_balance[]" type="text" class="altNum"  id="<?='t'.$ctr;?>" onChange="vChk(this)" value="<?= $r->beginning_balance;?>" size="12"></td>
      <td bgcolor="<?= $bgColor;?>"> 
        <?= lookUpAssoc('enable[]',array("Yes"=>"Y","No"=>"N"),$r->enable);?>
      </td>
    </tr>
    <?
	}
	?>
    <tr> 
      <td colspan="7" nowrap bgcolor="#FFFFFF"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        <input type="submit" name="p1" value="Save Checked" class="altBtn">
        <a href="#top">Go Top</a></font> </td>
      <td align=right nowrap bgcolor="#FFFFFF"> </td>
    </tr>
  </table>
</form>
<div align="Center">
        <?
	  	echo "<img src='graphics/redarrow_left.gif'><a href='?p=gchart&p1=Previous'> Previous</a>";
		?>
        <b>|</b> 
        <?
	  	echo "<a href='?p=gchart&p1=Next'> Next </a><img src='graphics/redarrow_right.gif'> ";
		?>

</div>
