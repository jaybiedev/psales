<script>
function vComp()
{
	document.getElementById('points_in').value= twoDecimals(1*document.getElementById('grocery').value/document.getElementById('CHG_GRC_POINT').value*1 + 1*document.getElementById('drygoods').value/document.getElementById('CHG_DRY_POINT').value*1);
}
</script>
<STYLE TYPE="text/css">
<!--
	.altText {
	background-color: #FFFFFF;
	font-family: verdana;
	border: #CCCCCC 1px solid;
	font-size: 14px;
	color: #000000
	} 
	.bigText {
	background-color: #FFFFFF;
	font-family: verdana;
	border: #CCCCCC 1px solid;
	font-size: 16px;
	font-weight: bold;
	color: #000000
	} 
	
	.altNum {
	background-color: #FFFFFF;
	font-family: verdana;
	border: #CCCCCC 1px solid;
	font-size: 14px;
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
	font-size: 12px;
	color: #000000
	} 			

	.altBtn {
	background-color: #CFCFCF;
	font-family: verdana;
	border: #B1B1B1 1px solid;
	font-size: 11px;
	font-weight: bold;
	padding: 2px;
	margin: 0px;
	color: #1F016D
	} 
-->
</STYLE>
<form action="?p=rewards.begbal" method="post" name="f1" id="f1">
  <table width="90%" border="0" align="center">
    <tr>
      <td>Search Account 
        <input name="xSearch" type="text" id="xSearch" value="<?= $xSearch;?>">
        <font size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <?=lookUpAssoc('sortby',array('Card No.'=>'cardno','Name'=>'account','Account No'=>'account_code'),$sortby);?>
        </font> 
        <input name="p1" type="submit" id="p1" value="Go">
        <font size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <input name="p12" type="button" id="p12" value="Close" onClick="window.location='?p='">
        </font><br>
        <hr color="#993300"></td>
    </tr>
  </table>
  <?

if (!session_is_registered('aREW'))
{
	session_register('aREW');
	$aREW = null;
	$aREW = array();
}

$fields = array('date','points_in','points_out','grocery','drygoods');
if (!in_array($p1, array('Edit','Load','New','Go','Selectaccount','Add')))
{
	for ($c=0;$c<count($fields);$c++)
	{
		if (substr($fields[$c],0,4) == 'date')
		{
			$aREW[$fields[$c]] = mdy2ymd($_REQUEST[$fields[$c]]);
		}
		else
		{
			$aREW[$fields[$c]] = $_REQUEST[$fields[$c]];
			if ($aREW[$fields[$c]]=='')
			{
				$aREW[$fields[$c]] = '0';
			}
		}	
	}
}
if ($c_id!= ''&& $p1 == 'Selectaccount')
{
	$aREW=null;
	$aREW=array();
	$q = "select 
				account.cardno,
				account.account_code,
				account.account_id,
				account.account,
				account.address
		 from 
		 		account
		where 
				account.account_id='$c_id'";
	$qr = @pg_query($q) or message(db_error());			
	$r = @pg_fetch_assoc($qr);
	$aREW = $r;
	$p1='selectaccountId';
	$aREW['date'] = date('Y-m-d');
}	
elseif ($p1 == 'Ok' && $aREW['account_id'] <= '0')
{
	message1("No Account Specified...");
}
elseif ($p1 == 'Ok')
{
	$aREW['amount'] = $aREW['grocery'] + $aREW['drygoods'];
	if ($aREW['reward_id'] == '')
	{
		$q = "insert into reward  (account_id, admin_id, date, amount_in, terminal, points_in, points_out)
				values ('".$aREW['account_id']."','".$ADMIN['admin_id']."','".$aREW['date']."','".$aREW['amount']."',
						'00','".$aREW['points_in']."','".$aREW['points_out']."')";
		$qr = @pg_query($q) or message1(pg_errormessage().$q);
		if ($qr)
		{
			$aREW['reward_id'] = pg_insert_id('reward');
			message1("Transaction Saved...");
		}
	}
	else
	{
		$q = "update reward set 
					account_id = '".$aREW['account_id']."',
					date = '".$aREW['date']."',
					amount_in= '".$aREW['amount']."',
					points_in = '".$aREW['points_in']."',
					points_out= '".$aREW['points_out']."'
				where
					reward_id = '".$aREW['reward_id']."'";
			
		$qr = @pg_query($q) or message1(pg_errormessage().$q);
		if ($qr)
		{
			message1("Transaction Updated...");
		}
					
	}
}
elseif ($p1 == 'Add')
{
	$aREW['debit']='';
	$aREW['credit']='';
	$aREW['dept']='G';
	$aREW['type']='P';
	$aREW['reward_id'] = '';
	$focus = 'type';

}
elseif ($p1 == 'DeleteChecked')
{
	$al = implode(',',$delete);
	
	$q = " update reward set status='C' where reward_id in ($al)";
	$qr = @pg_query($q) or message(pg_errormessage());
	if ($qr)
	{
		message1(@pg_affected_rows($qr)." Rewards Entries Successfully Deleted ");
	}
}
elseif ($p1 == 'Edit' && $id !='')
{
	$q = "select * from reward where reward_id = '$id' and account_id = '".$aREW['account_id']."'";
	$qr = @pg_query($q) or message(pg_errormessage().$q);
	if (@pg_num_rows($qr) == '0')
	{
		message1("Record NOT Found...");
	}
	else
	{
		$r = @pg_fetch_object($qr);
		$aREW['date'] = $r->date;
		$aREW['invoice'] = $r->invoice;
		$aREW['amount'] = $r->amount_in;
		$aREW['grocery'] = $r->grocery;
		$aREW['drygoods'] = $r->drygoods;
		$aREW['points_in'] = $r->points_in;
		$aREW['points_out'] = $r->points_out;
		$aREW['reward_id'] = $r->reward_id;
	}
}
if ($p1 == 'Go')
{
	  $q = "select * 
				from 
					account
				where 
					account_type_id!='1' ";
		if ($searchby == '')
		{
			$searchby = 'account';
			
		}
		$q .= " and $sortby  like '$xSearch%' 
				order by 	$sortby";
					
					
		$qr = @pg_query($q)	or message("Error Querying account file...".db_error());
?>
  
  <table width="80%" border="0" align="center" cellpadding="2" cellspacing="1" bgcolor="#EFEFEF">
    <tr bgcolor="#CFD3E7"> 
      <td width="7%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
      <td width="32%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Name</font></strong></td>
      <td width="6%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Card</font></strong></td>
      <td width="21%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Address</font></strong></td>
      <td width="11%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Status</font></strong></td>
      <td width="11%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Balance</font></strong></td>
      <td width="12%" align="center">&nbsp;</td>
    </tr>
    <?
  include_once('accountbalance.php');
  	$ctr=0;
  	while ($r = pg_fetch_assoc($qr))
	{
		$ctr++;
		$aBal = customerBalance($r['account_id']);

  ?>
    <tr bgcolor="#FFFFFF" onMouseOver="bgColor='#FFFFCC'" onMouseOut="bgColor='#FFFFFF'"> 
      <td align="right" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?=$ctr;?>
        .</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
	  <a href="javascript: document.getElementById('f1').action='?p=rewards.begbal&p1=Selectaccount&c_id=<?=$r['account_id'];?>&xSearch=<?=$xSearch;?>&rtype=<?=$rtype;?>'; document.getElementById('f1').submit()"> 
        <?= $r['account'];?>
        </a> </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
	  <a href="javascript: document.getElementById('f1').action='?p=rewards.begbal&p1=Selectaccount&c_id=<?=$r['account_id'];?>&xSearch=<?=$xSearch;?>&rtype=<?=$rtype;?>'; document.getElementById('f1').submit()"> 
        <?= $r['cardno'];?>
        </a></font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $r->address ;?>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= status($r['account_status']);?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= number_format($aBal['balance'],2);?>
        </font><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
        </font><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; </font></td>
    </tr>
    <?
  }
  ?>
  </table>

  <?	
	  exit;
  }
 ?>
  <table width="80%" border="0" align="center">
    <tr> 
      <td colspan="6" nowrap><b> Rewards Adjustment For: <font size='4'><b> 
        <?= '['.$aREW['cardno'].'] '.$aREW['account'];?>
        </b></font> </b></td>
    </tr>
    <tr> 
      <td width="10%" nowrap bgcolor="#FFFFFF"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Date</font></strong></td>
      <td width="10%" align="center" nowrap bgcolor="#FFFFFF"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Grocery 
        Amt<br>
        </font> /
<input name="CHG_GRC_POINT" id="CHG_GRC_POINT" type="text" value="<?= $SYSCONF['CHG_GRC_POINT'];?>" size="5"  style="text-align:right; border:0; padding:0" readOnly>
        <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> </font></strong></td>
      <td width="9%" align="center" nowrap bgcolor="#FFFFFF"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">DryGoods<br>
        /
<input name="CHG_DRY_POINT" id="CHG_DRY_POINT" type="text" value="<?= $SYSCONF['CHG_DRY_POINT'];?>" size="5" style="text-align:right; border:0; padding:0" readOnl>
        </font></strong></td>
      <td width="9%" nowrap bgcolor="#FFFFFF"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Points-IN</font></strong></td>
      <td width="8%" nowrap bgcolor="#FFFFFF"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Points-OUT</font></strong></td>
      <td width="54%" nowrap bgcolor="#FFFFFF"><strong> </strong></td>
    </tr>
    <tr> 
      <td nowrap><b> 
        <input name="date" type="text" id="date" value="<?= ymd2mdy($aREW['date']);?>" size="10" maxlength="10" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" onKeypress="if(event.keyCode==13) {document.getElementById('lifetime').focus();return false;}">
        <font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"><img src="../graphics/arrow_down.jpg" onClick="popUpCalendar(this, f1.date, 'mm/dd/yyyy')"></font> 
        </b></td>
      <td nowrap><b>
        <input name="grocery" type="text" id="grocery" value="<?= $aREW['grocery'];?>" size="12" maxlength="12" style="text-align:right" onBlur="vComp()">
        </b> </td>
      <td><input name="drygoods" type="text" id="drygoods" value="<?= $aREW['drygoods'];?>" size="12" maxlength="12" style="text-align:right" onBlur="vComp()"> 
      </td>
      <td><input name="points_in" type="text" id="points_in" value="<?= $aREW['points_in'];?>" size="12" maxlength="12" style="text-align:right"> 
      </td>
      <td><input name="points_out" type="text" id="points_out" value="<?= $aREW['points_out'];?>" size="12" maxlength="12" style="text-align:right"></td>
      <td> 
        <input name="p1" type="submit" id="p1" value="Ok">
      </td>
    </tr>
  </table>
  <table width="80%" border="0" align="center" cellpadding="2" cellspacing="1" bgcolor="#EFEFEF">
    <tr bgcolor="#CCCCCC"> 
      <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Date</font></strong></td>
      <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Invoice</font></strong></td>
      <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Amount</font></strong></td>
      <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Points 
        In</font></strong></td>
      <td><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Points 
        Out</font></strong></td>
    </tr>
    <?
	if ($aREW['account_id'] != '')
	{
   		$q = "select * from reward where account_id='".$aREW['account_id']."' and status!='C' order by date";
		$qr = @pg_query($q) or message(pg_errormessage());
	}
	$ctr=0;
	$total_in = $total_out = 0;
	while ($r = @pg_fetch_object($qr))
	{
	
		$ctr++;
		if ($ctr%2 == 0) $bgColor='#FFFFFF';
		else $bgColor='#EFEFEF';
		
		$total_in += $r->points_in;
		$total_out += $r->points_out;
  ?>
    <tr  bgColor="<?= $bgColor;?>" onMouseOut="bgColor='<?= $bgColor;?>'" onMouseOver="bgColor='#FFFFCC'"> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href="javascript: document.getElementById('f1').action='?p=rewards.begbal&p1=Edit&id=<?=$r->reward_id;?>'; document.getElementById('f1').submit()"> 
        </a> 
        <input type="checkbox" name="delete[]" value="<?= $r->reward_id;?>">
        <a href="javascript: document.getElementById('f1').action='?p=rewards.begbal&p1=Edit&id=<?=$r->reward_id;?>'; document.getElementById('f1').submit()"> 
        <?= ymd2mdy($r->date);?>
        </a> </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $r->invoice;?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= number_format($r->amount_in,2);?>
        &nbsp;&nbsp; </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $r->points_in;?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $r->points_out;?>
        </font></td>
    </tr>
    <?
		}
	?>
    <tr> 
      <td colspan="3"><input type="button" name="p13" value="Delete Checked" onClick="if (confirm('Are you sure to DELETE checked items?')){document.getElementById('f1').action='?p=rewards.begbal&p1=DeleteChecked';document.getElementById('f1').submit();}">
        &nbsp;&nbsp;&nbsp;Available Points : <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $total_in - $total_out;?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $total_in;?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $total_out;?>
        </font></td>
    </tr>
  </table>
</form>
<?
if ($focus != '')
{
	echo "<script>document.getElementById('$focus').focus()</script>";
}
?>
