<script>
function vTotalPaid()
{
	document.getElementById('total_paid').value =1*(document.getElementById('cash').value) + 1*(document.getElementById('checkamount').value);
	return false;
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
<?
if ($date_asof == '')
{
		//show only previous 3 months 
		$year = date('Y');
		$month = date('m');
		if ($month > 3)
		{
			$month = $month -3;
		}
		else
		{
			$month = 9 + $month ;
			$year--;
		}
		if (strlen($month) == 1) $month = '0'.$month;
		$mo3  = $year.'-'.$month.'-01';
		$date_asof = ymd2mdy($mo3);
}
?>
<form action="?p=account.begbal" method="post" name="f1" id="f1" style="margin:10px">
  <table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr> 
      <td width="0%" height="22" background="../graphics/table_left.PNG">&nbsp;</td>
      <td width="99%" background="../graphics/table_horizontal.PNG"><font color="#FFFF99" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Accounts 
        Receivables Credit Memo Posting</strong></font></td>
      <td width="0%" background="../graphics/table_right.PNG">&nbsp; </td>
    </tr>
    <tr bgcolor="#DDE5F3"> 
      <td height="49" colspan="3"><font size="2">Search Account</font> 
        <input name="xSearch" type="text" id="xSearch" value="<?= $xSearch;?>"  onKeypress="if(event.keyCode == 13){document.getElementById('Go').click();return false;}">
        <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?=lookUpAssoc('sortby',array('Card No.'=>'cardno','Name'=>'account','Account No'=>'account_code'),$sortby);?>
        </font><font size="2">Asof<b> 
        <input name="date_asof" type="text" id="date_asof" value="<?= $date_asof;?>" size="10" maxlength="10" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" onKeypress="if(event.keyCode==13) {document.getElementById('Go').focus();return false;}">
        <font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"><img src="../graphics/dwn-arrow-grn.gif" width="12" height="12" onClick="popUpCalendar(this, f1.date_expiry, 'mm/dd/yyyy')"></font> 
        </b></font> 
        <input name="p1" type="submit" id="Go" value="Go"> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="p12" type="button" id="p12" value="Close" onClick="window.location='?p='">
        </font><br> <hr color="#993300"></td>
    </tr>
  </table>
  <?

if (!session_is_registered('aCM'))
{
	session_register('aCM');
	$aCM = null;
	$aCM = array();
}

$fields = array('date','invoice','type','grocery_debit','drygood_debit','credit');
if (!in_array($p1, array('Edit','Load','New','Go','Selectaccount','Add')))
{
	for ($c=0;$c<count($fields);$c++)
	{
		if (substr($fields[$c],0,4) == 'date')
		{
			$aCM[$fields[$c]] = mdy2ymd($_REQUEST[$fields[$c]]);
		}
		else
		{
			$aCM[$fields[$c]] = $_REQUEST[$fields[$c]];
			if ($aCM[$fields[$c]]=='')
			{
				$aCM[$fields[$c]] = '0';
			}
		}	
	}
	$aCM['debit'] = $aCM['grocery_debit'] + $aCM['drygood_debit'];
	$aCM['debit_balance'] = $aCM['grocery_debit'] + $aCM['drygood_debit'];
	$aCM['credit_balance'] = $aCM['credit'];
}
if ($p1 == ''  or $p1 == 'New')
{
	$focus = 'xSearch';
}					

if ($c_id!= ''&& $p1 == 'Selectaccount')
{
	$aCM=null;
	$aCM=array();
	$q = "select 
				account.cardno,
				account.account_code,
				account.account_id,
				account.account,
				account.address,
				account.account_class_id
		 from 
		 		account
		where 
				account.account_id='$c_id'";
	$qr = @pg_query($q) or message(db_error().$q);			
	$r = @pg_fetch_assoc($qr);
	$aCM = $r;

	$q = "select * from account_class where account_class_id = '".$aCM['account_class_id']."'";
	$qr = @pg_query($q) or message(db_error().$q);			
	$r = @pg_fetch_assoc($qr);
	if ($r)
	{
		$aCM += $r;
	}
	
	$p1='selectaccountId';
	$aCM['date'] = date('Y-m-d');
	$focus = 'date';
}	
elseif ($p1 == 'Edit' && $id != '')
{
	$q = "select * from accountledger where accountledger_id='$id'";
	$qr = @pg_query($q) or message1(pg_errormessage());
	$r = @pg_fetch_assoc($qr);
	//$fields = array('date','invoice','type','grocery_debit','drygood_debit','credit');
	
	$aCM['accountledger_id'] = $r['accountledger_id'];
	$aCM['date'] = $r['date'];
	$aCM['invoice'] = $r['invoice'];
	$aCM['type'] = $r['type'];
	$aCM['grocery_debit'] = $r['grocery_debit'];
	$aCM['drygood_debit'] = $r['drygood_debit'];
	$aCM['credit'] = $r['credit'];
	
	$aCM['debit'] = $aCM['grocery_debit'] + $aCM['drygood_debit'];
	$aCM['debit_balance'] = $aCM['grocery_debit'] + $aCM['drygood_debit'];
	$aCM['credit_balance'] = $aCM['credit'];
}
elseif ($p1 == 'Ok' and $aCM['invoice']=='')
{
	message("Please provide reference no...");
}
elseif ($p1 == 'Ok')
{
	if ($aCM['accountledger_id'] == '')
	{
		$q = "insert into accountledger (account_id, admin_id, date,invoice,type, 
					grocery_debit, drygood_debit, debit, debit_balance, credit, 
					credit_balance, last_credit_balance)
				values ('".$aCM['account_id']."','".$ADMIN['admin_id']."','".$aCM['date']."','".$aCM['invoice']."',
						'".$aCM['type']."','".$aCM['grocery_debit']."','".$aCM['drygood_debit']."','".$aCM['debit']."','".$aCM['debit_balance']."',
						'".$aCM['credit']."','".$aCM['credit_balance']."','".$aCM['credit_balance']."')";
		$qr = @pg_query($q) or message1(pg_errormessage().$q);
		if ($qr)
		{
			$aCM['accountledger_id'] = pg_insert_id('accountledger');
			message1("Transaction Saved...");
			$focus = 'xSearch';
		}
	}
	else
	{
		$q = "update accountledger set 
					account_id = '".$aCM['account_id']."',
					date = '".$aCM['date']."',
					grocery_debit = '".$aCM['grocery_debit']."',
					drygood_debit = '".$aCM['drygood_debit']."',
					type = '".$aCM['type']."',
					debit = '".$aCM['debit']."',
					debit_balance = '".$aCM['debit_balance']."',
					credit = '".$aCM['credit']."',
					credit_balance = '".$aCM['credit_balance']."'
				where
					accountledger_id = '".$aCM['accountledger_id']."'";
			
		$qr = @pg_query($q) or message1(pg_errormessage());
		if ($qr)
		{
			message1("Transaction Updated...");
			$focus = 'xSearch';
		}
					
	}
	
	$focus = 'Add';
	//$aCM['reference'] = $aCM['grocery_debit'] = $aCM['drygood_debit'] ='$aCM['credit'] =';

}
elseif ($p1 == 'Add')
{
	$aCM['grocery_debit']='';
	$aCM['drygood_debit']='';
	$aCM['debit']='';
	$aCM['credit']='';
	$aCM['credit']='';
	$aCM['date'] = date('Y-m-d');
	$aCM['type']='P';
	$aCM['accountledger_id'] = '';
	$focus = 'xSearch';

}
elseif ($p1 == 'Delete Checked')
{
	$al = implode(',',$delete);
	
	$q = " update accountledger set enable='N' where accountledger_id in ($al)";
	$qr = @pg_query($q) or message(pg_errormessage());
	if ($qr)
	{
		message1(@pg_affected_rows($qr)." Account Ledger Entries Successfully Deleted ");
	}
}
elseif ($p1 == 'Go' && $sortby != 'account')
{
	$aCM=null;
	$aCM=array();
	$q = "select 
				account.cardno,
				account.account_code,
				account.account_id,
				account.account,
				account.address,
				account.account_class_id
		 from 
		 		account
		where 
				$sortby='$xSearch'";
	$qr = @pg_query($q) or message(db_error().$q);			
	$r = @pg_fetch_assoc($qr);
	$aCM = $r;

	$q = "select * from account_class where account_class_id = '".$aCM['account_class_id']."'";
	$qr = @pg_query($q) or message(db_error().$q);			
	$r = @pg_fetch_assoc($qr);
	if ($r)
	{
		$aCM += $r;
	}
	
	$p1='selectaccountId';
	$aCM['date'] = date('Y-m-d');
	$focus = 'date';
}	
elseif ($p1 == 'Go')
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
	  <a href="javascript: document.getElementById('f1').action='?p=account.begbal&p1=Selectaccount&c_id=<?=$r['account_id'];?>&xSearch=<?=$xSearch;?>&rtype=<?=$rtype;?>';document.getElementById('f1').submit()"> 
        <?= $r['account'];?>
        </a> </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="javascript: document.getElementById('f1').action='?p=account.begbal&p1=Selectaccount&c_id=<?=$r['account_id'];?>&xSearch=<?=$xSearch;?>&rtype=<?=$rtype;?>';document.getElementById('f1').submit()">
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
  <table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr bgcolor="#DDE5F3"> 
      <td colspan="6" nowrap><b> Account Adjustment For: </b><font size='4'><b> 
        <?= '['.$aCM['cardno'].'] '.$aCM['account'];?>
        </b></font> </b></td>
    </tr>
    <tr bgcolor="#DDE5F3"> 
      <td colspan="6" nowrap><font color="#AA6600" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?=$aCM['account_class'];?>
        &nbsp;Grocery 
        <?= $aCM['grocery_interval'].'/'.$aCM['grocery_term'];?>
        Dry Goods 
        <?= $aCM['drygood_interval'].'/'.$aCM['drygood_term'];?>
        </font><font color="#996600" size='3'>&nbsp; </font></td>
    </tr>
    <tr bgcolor="#DADADA"> 
      <td width="10%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Date</font></td>
      <td width="9%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Reference</font></td>
      <td width="7%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Type</font></td>
      <td width="8%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Grocery</font></td>
      <td width="8%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">DryGoods</font></td>
      <td width="58%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Credit</font></td>
    </tr>
    <tr> 
      <td nowrap><b> 
        <input name="date" type="text" id="date" value="<?= ymd2mdy($aCM['date']);?>" size="10" maxlength="10" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" onKeypress="if(event.keyCode==13) {document.getElementById('invoice').focus();return false;}">
        <font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"><img src="../graphics/dwn-arrow-grn.gif" width="12" height="12" onClick="popUpCalendar(this, f1.date_expiry, 'mm/dd/yyyy')"></font> 
        </b></td>
      <td nowrap><b> 
        <input name="invoice" type="text" id="invoice" value="<?= $aCM['invoice'];?>" size="12" maxlength="12"  onKeypress="if(event.keyCode == 13){document.getElementById('grocery_debit').focus();return false;}">
        </b> </td>
      <td> 
        <?= lookUpAssoc('type',array('Sales Transaction'=>'T','Interest'=>'I','Surcharge'=>'S','Payment'=>'P'),$aCM['type']);?>
      </td>
      <td> <input name="grocery_debit" type="text" id="grocery_debit" value="<?= $aCM['grocery_debit'];?>" size="12" maxlength="12" style="text-align:right"  onKeypress="if(event.keyCode == 13){document.getElementById('drygood_debit').focus();return false;}"> 
      </td>
      <td><input name="drygood_debit" type="text" id="drygood_debit" value="<?= $aCM['drygood_debit'];?>" size="12" maxlength="12" style="text-align:right"  onKeypress="if(event.keyCode == 13){document.getElementById('credit').focus();return false;}"></td>
      <td><input name="credit" type="text" id="credit" value="<?= $aCM['credit'];?>" size="12" maxlength="12" style="text-align:right"  onKeypress="if(event.keyCode == 13){document.getElementById('Ok').focus();return false;}"> 
        <input name="p1" type="submit" id="Ok" value="Ok"></td>
    </tr>
  </table>
  <table width="90%" border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#FFFFFF">
    <tr bgcolor="#DADADA" background="../graphics/table_horizontal.PNG"> 
      <td colspan="9"><strong><img src="../graphics/redlist.gif" width="16" height="17"><font color="#EFEFEF" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        Account Ledger</font></strong></td>
    </tr>
    <tr bgcolor="#DADADA"> 
      <td width="3%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
      <td width="10%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Date</font></td>
      <td width="13%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Reference</font></td>
      <td width="14%" align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Type</font></td>
      <td width="10%" align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Grocery</font></td>
      <td width="11%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">DryGoods</font></td>
      <td width="13%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Credit</font></td>
      <td width="13%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Balance</font></td>
      <td width="13%">&nbsp;</td>
    </tr>
    <?
		if ($aCM['account_id'] != '')
		{
			$q = "select * from accountledger 	
					where
						account_id ='".$aCM['account_id']."' and
						enable = 'Y' 
					order by
						date";
			$qr = @pg_query($q) or message(pg_errormessage());
		}
		$ctr=$balance=0;
		$beginning_balance = $showbegbal =0;
		$mdate_asof = mdy2ymd($date_asof);

		while ($r = @pg_fetch_object($qr))
		{
			
			$balance += $r->debit - $r->credit;
			if ( $r->date < $mdate_asof) 
			{

				$beginning_balance = $balance;
				continue;
			}
			if ($showbegbal == 0)
			{
				if ($beginning_balance != 0)
				{

					echo " <tr bgColor='#FFFFFF'><td align=right><font size='2'></font></td><td><font size='2'>$date_asof</font></td>
							<td colspan='2'><font size='2'>Balance Forwarded</font></td><td></td><td></td><td></td>
							<td align=right><font size='2'>".number_format($beginning_balance,2)."</font></td><td></td></tr> ";
				}
				$showbegbal = 1;
			}

			
			$ctr++;
			if ($ctr%2 == '0')
			{
				$bgColor = '#EFEEF9';
			}	
			else
			{
				$bgColor = '#FFFFFF';
			}		
	?>
    <tr bgColor="<?=$bgColor;?>"> 
      <td align="right" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?=$ctr;?>
        . 
        <input name="delete[]" type="checkbox" value="<?= $r->accountledger_id;?>">
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href="?p=account.begbal&p1=Edit&id=<?=$r->accountledger_id;?>"> 
        <?= ymd2mdy($r->date);?>
        </a> </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href="?p=account.begbal&p1=Edit&id=<?=$r->accountledger_id;?>"> 
        <?= $r->invoice;?>
        </a> </font></td>
      <td align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $r->type;?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= number_format2($r->grocery_debit,2);?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= number_format2($r->drygood_debit,2);?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
        <?= number_format2($r->credit,2);?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
        <?= number_format2($balance,2);?>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
        <?= lookUpTableReturnValue('x','admin','admin_id','username',$r->admin_id);?>
        </font></td>
    </tr>
    <?
		}
	?>
    <tr bgColor="#CCCCCC"> 
      <td colspan="9"><input name="p1" type="submit" id="p1" value="Delete Checked"> 
        <input name="p1" type="submit" id="Add" value="Add"></td>
    </tr>
  </table>
</form>
<?
if ($focus != '')
{
	echo "<script>document.getElementById('$focus').focus()</script>";
}
?>
