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
	font-size: 12px;
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
			if ($tender_id[$c] != '')
			{
				$q = "update tender set
						gcdebit_id='".$gcdebit_id[$c]."',
						gccredit_id='".$gccredit_id[$c]."'
					where
						tender_id='".$tender_id[$c]."'";
					
				$qr = @pg_query($q) or message (pg_errormessage());
			}			
		}
		$ctr++;
	} 
	$atender['status']='SAVED';
}
?>
<form name="form1" method="post" action="">
  <table width="80%" border="0" cellspacing="1" cellpadding="2" align="center">
    <tr bgcolor="#C8D7E6" background="../graphics/table_horizontal.PNG"> 
      <td height="27" colspan="11"><font color="#FFFF99" size="2" face="Verdana, Arial, Helvetica, sans-serif"><b><img src="../graphics/arrowgrn.gif" width="16" height="16"> 
        Setup Tender Types Accounting Entry <a name="top"></a></b></font></td>
    </tr>
    <tr bgcolor="#DADADA"> 
      <td><font size="2">#</font></td>
      <td><font size="2">Tender Type</font></td>
      <td><font size="2">Debit Account</font></td>
      <td><font size="2">Credit Account</font></td>
    </tr>
    <?
	$q = "select * from tender where enable='Y' order by tender_code";
	$qr = @pg_query($q) or message (pg_errormessage());
	$ctr = 0;
	while ($r = @pg_fetch_object($qr))
	{
		$ctr++;
		
	?>
    <tr bgColor='#FFFFFF'> 
      <td width="6%"  align=right nowrap><font size=1> 
        <input type="hidden" name="tender_id[]" size="5" value="<?= $r->tender_id;?>">
        <?= $ctr;?>
        <input type='checkbox' name='mark[]' value="<?=$ctr;?>" id="m<?=$ctr;?>"  class="altText">
        </font> </td>
      <td width="12%"> <input name="tender[]" type="text" id="<?='t'.$ctr;?>" class="altText" readOnly onChange="vChk(this)" value="<?= $r->tender;?>" size="30" maxlength="40"> 
      </td>
      <td width="13%"><select name="gcdebit_id[]" id="<?='d'.$ctr;?>"  onChange="vChk(this)" >
          <option value="0">Select Account</option>
          <?
			$qq= "select * from gchart where enable='Y' order by acode, scode";
			$qqr = @pg_query($qq);
			while ($rr = @pg_fetch_object($qqr))
			{
				if ($r->gcdebit_id == $rr->gchart_id)
				{
					echo "<option value=\"$rr->gchart_id\" selected>$rr->acode - $rr->scode ".' '.addslashes($rr->gchart)."</option>";
				}
				else
				{
					echo "<option value=\"$rr->gchart_id\">$rr->acode - $rr->scode ".' '.addslashes($rr->gchart)."</option>";
				}
			}
		?>
        </select> </td>
      <td width="69%" colspan="4"> <select name="gccredit_id[]"  id="<?='c'.$ctr;?>"  onChange="vChk(this)" >
          <option value="0">Select Account</option>
          <?
			$qq= "select * from gchart where enable='Y' order by acode, scode";
			$qqr = @pg_query($qq);
			while ($rr = @pg_fetch_object($qqr))
			{
				if ($r->gccredit_id == $rr->gchart_id)
				{
					echo "<option value=\"$rr->gchart_id\" selected>$rr->acode - $rr->scode ".' '.addslashes($rr->gchart)."</option>";
				}
				else
				{
					echo "<option value=\"$rr->gchart_id\">$rr->acode - $rr->scode ".' '.addslashes($rr->gchart)."</option>";
				}
			}
		?>
        </select> </td>
    </tr>
	
    <?
		if ($ctr%15 == '0')
		{
			?>
				<tr > <td colspan="4" nowrap>
					<input type="submit" name="p1" value="Save Checked">
					</td>
				</tr>
			<?
		}
	}
	?>
	<tr > <td colspan="4" nowrap>
		<input type="submit" name="p1" value="Save Checked">
		</td>
	</tr>
  </table>
</form>
<div align="center"></div>		
