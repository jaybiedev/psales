<?
if (!session_is_registered('account_id'))
{
	session_register('account_id');
}
$account_id = $_REQUEST['account_id'];

?>
<form name="f1"  id="f1" style="margin:10px" method="post" action="">
  <table width="95%%" border="0" align="center" cellpadding="1" cellspacing="1">
    <tr> 
      <td height="23" colspan="8" background="../graphics/table_horizontal.PNG"><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Browse 
        Account Payables</strong></font></td>
    </tr>
    <tr> 
      <td colspan="8"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Find 
        <input type="text" class="altText" name="xSearch" value="<?= $xSearch;?>">
        <?= lookUpAssoc('searchby',array('SRR No'=>'record_id','Reference'=>'reference'), $searchby);?>
        Supplier</font> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <select name="account_id" id="account_id"   tabindex="<?= array_search('account_id',$fields);?>" style="border: #CCCCCC 1px solid; width:280px">
          <option value=''>Show All Supplier Accounts--</option>
          <?
  		foreach ($aSUPPLIER as $stemp)
		{
			if ($stemp['account_id'] == $account_id)
			{
				echo "<option value=".$stemp['account_id']." selected>".substr($stemp['account_code'],0,6)." ".$stemp['account']."</option>";
			}
			else
			{
				echo "<option value=".$stemp['account_id'].">".substr($stemp['account_code'],0,6)." ".$stemp['account']."</option>";
			}
		}

	  ?>
        </select>
        <input name="p1" type="submit" id="p1" value="Go">
        </font></td>
    </tr>
    <tr bgcolor="#E1E7F1"> 
      <td width="11%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></td>
      <td width="6%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Date</font></td>
      <td width="12%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">SRR/CM #</font></td>
      <td width="3%">&nbsp;</td>
      <td width="8%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Inv/Ref 
        # </font></td>
      <td width="44%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Supplier</font></td>
      <td width="9%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Amount</font></td>
      <td width="7%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Status</font></td>
    </tr>
    <?
		$xSearch = $_REQUEST['xSearch'];
		$searchby = $_REQUEST['searchby'];
		$account_id = $_REQUEST['account_id'];

		$q = "select * from apledger where enable='Y'";

		if ($xSearch != '')
		{
			$q .= " and $searchby = '$xSearch' ";
		}
		if ($account_id != '')
		{
			$q .= " and apledger.account_id = '$account_id'";
		}
		$q .= " order by date ";
		$qr = @pg_query($q) or message(@pg_errormessage());
		while ($r = @pg_fetch_object($qr))
		{
			$ctr++;
			if ($r->status == 'C')
			{
				$bgColor = '#FFCCCC';
			}
			elseif ($r->status == 'Y')
			{
				$bgColor = '#CCCCFF';
			}
			else
			{
				$bgColor = '#FFFFFF';
			}
			
			if ($r->type=='RR')
			{
				$camount = number_format($r->credit,2);
			}
			else
			{
				$camount = '('.number_format($r->credit,2).')';
			}
	?>
    <tr onMouseOver="bgColor='#FFFF99'" onMouseOut="bgColor='<?=$bgColor;?>'" bgColor='<?=$bgColor;?>'"> 
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $ctr;?>
        . 
        <input type="checkbox" name="aMark[]" id="aMark[]" value="<?= $r->apledger_id;?>" <?= (in_array($r->status, array('C','Y')) ? 'Disabled=1':'');?> onFocus="vSelect()">
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= ymd2mdy($r->date);?>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href="?p=payable.browse&p1=ViewDetails&id=<?= $r->record_id;?>&type=<?= $r->type;?>"> 
        <?= str_pad($r->record_id,9,'0',STR_PAD_LEFT);?>
        </a> </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=payable.browse&p1=ViewDetails&id=<?= $r->record_id;?>&type=<?= $r->type;?>">
        <?= $r->type;?>
        </a></font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $r->reference;?>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= lookUpTableReturnValue('x','account','account_id','account_code',$r->account_id).' '.lookUpTableReturnValue('x','account','account_id','account',$r->account_id);?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $camount;?>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= status($r->status);?>
        </font></td>
    </tr>
    <?
	}
	?>
    <tr> 
      <td colspan="8"><input name="p1" type="button" id="p1" value="Pay All Marked SRRs" onClick="document.getElementById('f1').action='?p=gltran&p1=Pay';document.getElementById('f1').submit()"> 
        <font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;</font><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;</font><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;</font></td>
    </tr>
  </table>
</form>
<?
if ($p1 == 'ViewDetails')
{
	include_once('payable.browse.detail.php');
}
?>