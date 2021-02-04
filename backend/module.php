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
$href = '?p=module';

if (!chkRights2("module","mview",$ADMIN['admin_id'] ) )
{
	message1("<br>You have no permission in this module...");
	exit;
}

if ($ADMIN['username']!='root' )
{
	message1("<br>You have no permission in this module... Program systems developer use only... Critical Constants Area...");
	exit;
}

if (!session_is_registered('amodule'))
{
	session_register('amodule');
	$amodule=array();
}


if ($p1=="Save Checked" && !chkRights2("module","madd",$ADMIN['admin_id']))
{
	message("You have no permission to modify or add...");
}
if ($p1=="Save Checked")
{

	$ctr=0;
	while ($ctr < count($mark))
	{
		$c = $mark[$ctr]-1;
		if ($module_percent[$c] == '') $module_percent[$c] = 0;
		if ($description[$c]!='')
		{

			if ($module_id[$c] == '')
			{
				$q = "insert into module(enable, description, module)
					values
					('".$enable[$c]."','".$description[$c]."','".$module[$c]."')";
				$qr = @pg_query($q) or message (pg_errormessage());
			}
			else
			{
				@pg_query("update module set
						enable='".$enable[$c]."',
						description='".$description[$c]."',
						module = '".$module[$c]."'
					where
						module_id='".$module_id[$c]."'") or message (pg_errormessage());
			}			
		}
		$ctr++;
	} 
	$amodule['status']='SAVED';
}
?>
<div align="center">
  <p><br>
    <font color="#CC0000">*** !! <strong>WARNING</strong> !! System Critical Setup. 
    </font></p>
  <p><font color="#CC0000">This Area is Only for the Programmer/s Systems Administrator 
    !! </font></p>
</div>
<form name="form1" method="post" action="">
  <table width="60%" border="0" align="center" cellpadding="1" cellspacing="1">
    <tr> 
      <td nowrap colspan="4"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Find 
        <input type="text"  class="altText"name="xSearch" value="<?= $xSearch;?>">
        <input name="p1" type="submit"   class="altBtn" id="p1" value="Go">
        Insert 
        <select name="insertcount">
          <option value="5">5</option>
          <option value="10">10</option>
          <option value="15">15</option>
          <option value="20">20</option>
        </select>
        <input name="p1" type="submit"   class="altBtn" id="p1" value="Insert">
        <input name="p1" type="submit"   class="altBtn" id="p1" value="List">
        <input name="p1" type="button"  class="altBtn" id="p1" onClick="window.location='?p='" value="Close">
        </font> <hr color="#CC3300"> </td>
    </tr>
    <tr bgcolor="#C8D7E6"> 
      <td colspan="4" height="27" background="../graphics/table0_horizontal.PNG"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><b> 
        <img src="../graphics/mail.gif" width="16" height="17"> module Setup <a name="top"></a></b></font></td>
    </tr>
    <tr bgcolor="#E9E9E9"> 
      <td width="8%"><strong><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">#</font></strong></td>
      <td width="20%" nowrap><a href="<?=$href.'&sort=description&start=$start&xSearch=$xSearch';?>"> 
        <font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Module 
        Description</font></a></td>
      <td nowrap><a href="<?=$href.'&sort=module&start=$start&xSearch=$xSearch';?>"><font face="Verdana, Arial, Helvetica, sans-serif" size="2" color="#000000">Code 
        (<font color="#CC0000"><strong>!! VERY IMPORTANT CONSTANTS!!</strong></font>)</font></a></td>
      <td nowrap><strong><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        <input type="hidden" name="sort" size="5" value="<?= $sort;?>">
        </font></strong><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        Enabled</font></td>
    </tr>
    <?
	if ($p1=='Insert')
	{
		if (!chkRights2("module","madd",$ADMIN['admin_id']))
		{
			message("You have no permission in this module...");
			exit;
		}
		$amodule['status']='INSERT';
		$c=0;
		while ($c < $insertcount)
		{
			$c++;
	?>
    <tr bgcolor="#FFFFFF" onMouseOver="bgColor='#FFFFCC'" onMouseOut="bgColor='#FFFFFF'"> 
      <td align=right nowrap> <font size=1> 
        <?= $c;?>
        <input type="checkbox" name="mark[]" value="<?= $c;?>" id="<?= 'm'.$c;?>">
        <input name="module_id[]" type="hidden" id="module_id[]" size="5">
        </font> </td>
      <td> <input type="text"  class="altText"name="description[]" size="30"  onChange="vChk(this)" id="<?='t'.$c;?>"> 
      </td>
      <td ><input name="module[]" type="text"  class="altText"id="<?='k'.$c;?>"  onChange="vChk(this)" size="30" maxlength="30"> 
      </td>
      <td > 
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
      <td colspan="4" height="26"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Saved 
        Areas</font></td>
    </tr>
    <?
	} //if insert
	else
	{
		$amodule['status']='LIST';
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
	$q = "select * from module";
	if ($xSearch != '')
	{
		$q .= " where modulelike '$xSearch%' ";
	}
	
	if ($sort == '' || $sort=='description')
	{
		$sort = 'description';
	}
	$q .= " order by $sort " ; // limit $start,10";

	$qr = @pg_query($q) or die (pg_errormessage());
	$ctr = $c;
	while ($r = pg_fetch_object($qr))
	{
		$ctr++;
		
	?>
    <tr  bgcolor=<?= ($r->enable=='N')? '#FFCCCC' :'#FFFFFF';?>> 
      <td  align=right nowrap><font size=1> 
        <input type="hidden" name="module_id[]" size="5" value="<?= $r->module_id;?>">
        <? 
	  echo "$ctr."; 
	  if ($p1!='Insert')
	  {
         echo "<input type='checkbox' name='mark[]' value='$ctr' id='m$ctr'>";
	  }
	  ?>
        </font> </td>
      <td> <input name="description[]" type="text"  class="altText"id="<?='t'.$ctr;?>"  onChange="vChk(this)" value="<?= $r->description;?>" size="30"> 
      </td>
      <td><input name="module[]" type="text"  class="altText"id="<?='k'.$ctr;?>"  onChange="vChk(this)" value="<?=$r->module;?>" size="30" maxlength="30">
        <font color="#CC0000">*!</font> </td>
      <td> 
        <?= lookUpAssoc('enable[]',array("Yes"=>"Y","No"=>"N"),$r->enable);?>
      </td>
    </tr>
    <?
	}
	?>
    <tr bgcolor="#FFFFFF"> 
      <td colspan="4"><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
        <input type="submit"   class="altBtn" name="p1" value="Save Checked">
        </font> </td>
    </tr>
  </table>
</form>
<div align="center">
<?
	echo "<img src='../graphics/redarrow_left.gif'><a href='?p=module&p1=Previous&start=$start&sort=$sort&xSearch=$xSearch'> Previous</a>";
	echo " <b> | </b>";
	echo "<a href='?p=module&p1=Next&start=$start&sort=$sort&xSearch=$xSearch'> Next </a><img src='../graphics/redarrow_right.gif'> ";
?>	
</div>
