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
<form action="?p=account.bond" method="post" name="f1" id="f1">
  <table width="90%" border="0" align="center">
    <tr>
      <td>Search Account 
        <input name="xSearch" type="text" id="xSearch" value="<?= $xSearch;?>"  onKeypress="if(event.keyCode == 13){document.getElementById('Go').click();return false;}">
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
  include_once('accountbalance.php');

if (!session_is_registered('aBOND'))
{
	session_register('aBOND');
	$aBOND = null;
	$aBOND = array();
}

$fields = array('date','reference','type','grocery_debit','debit','credit');
if (!in_array($p1, array('Edit','Load','New','Go','Selectaccount','Add')))
{
	for ($c=0;$c<count($fields);$c++)
	{
		if (substr($fields[$c],0,4) == 'date')
		{
			$aBOND[$fields[$c]] = mdy2ymd($_REQUEST[$fields[$c]]);
		}
		else
		{
			$aBOND[$fields[$c]] = $_REQUEST[$fields[$c]];
			if ($aBOND[$fields[$c]]=='')
			{
				$aBOND[$fields[$c]] = '0';
			}
		}	
	}
}
if ($c_id!= ''&& $p1 == 'Selectaccount')
{
	$aBOND=null;
	$aBOND=array();
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
	$qr = @pg_query($q) or message(db_error());			
	$r = @pg_fetch_assoc($qr);
	$aBOND = $r;
	$aBOND['date'] = date('Y-m-d');
	
/*
	$q = "select * from account_class where account_class_id = '".$aBOND['account_class_id']."'";
	$qr = @pg_query($q) or message(db_error());			
	$r = @pg_fetch_assoc($qr);
	if ($r)
	{
		$aBOND += $r;
	}
*/	
	$p1='selectaccountId';
	$aBOND['date'] = date('Y-m-d');
}	
elseif ($p1 == 'Go' && $sortby != 'account')
{
	$aBOND=null;
	$aBOND=array();
	$q = "select 
				account.cardno,
				account.account_code,
				account.account_id,
				account.account,
				account.address,
				account.date_expiry
		 from 
		 		account
		where 
				account_type_id = '3' and 
				$sortby='$xSearch'";
	$qr = @pg_query($q) or message(db_error());			
	
	if (@pg_num_rows($qr) == '0')
	{
		message1(" Searched Guarantor Account NOT Found...");
		$focus = 'xSearch';
	}
	else
	{
		$r = @pg_fetch_assoc($qr);
		
		$aBOND = $r;
		$aBal = customerBalance($r['account_id']);
	
		$aBOND['account_balance'] = round($aBal['balance'],2);
		$aBOND['amount_due'] =round($aBal['balance'],2);
		$aBOND['total_due'] =round($aBal['balance'],2);
		$aBOND['date'] = date('Y-m-d');
	
		$p1='selectaccountId';
		$focus = 'reference';
	}	
}

elseif ($p1 == 'Ok')
{
	if ($aBOND['bondledger_id'] == '')
	{
		$q = "insert into bondledger (account_id, admin_id, date,reference,  type, debit, credit)
				values ('".$aBOND['account_id']."','".$ADMIN['admin_id']."','".$aBOND['date']."','".$aBOND['reference']."',
						'".$aBOND['type']."','".$aBOND['debit']."','".$aBOND['credit']."')";
		$qr = @pg_query($q) or message1(pg_errormessage().$q);
		if ($qr)
		{
			$aBOND['bondledger_id'] = pg_insert_id('bondledger');
			message1("Transaction Saved...");
		}
	}
	else
	{
		$q = "update bondledger set 
					account_id = '".$aBOND['account_id']."',
					reference = '".$aBOND['reference']."',
					date = '".$aBOND['date']."',
					debit = '".$aBOND['debit']."',
					type = '".$aBOND['type']."',
					credit = '".$aBOND['credit']."'
				where
					bondledger_id = '".$aBOND['bondledger_id']."'";
			
		$qr = @pg_query($q) or message1(pg_errormessage());
		if ($qr)
		{
			message1("Transaction Updated...");
		}
					
	}
}
elseif ($p1 == 'Add')
{
	$aBOND['debit']='';
	$aBOND['credit']='';
	$aBOND['reference']='';
	$aBOND['date']=date('Y-m-d');
	$aBOND['type']='P';
	$aBOND['bondledger_id'] = '';
	$focus = 'type';

}
elseif ($p1 == 'Delete Checked')
{
	$al = implode(',',$delete);
	
	$q = " update bondledger set enable='N' where bondledger_id in ($al)";
	$qr = @pg_query($q) or message(pg_errormessage());
	if ($qr)
	{
		message1(@pg_affected_rows($qr)." Account Ledger Entries Successfully Deleted ");
	}
}
if ($p1 == 'Go' && $sortby == 'account')
 {
  	if ($sortby == '') $sortby = 'cardno';
	  $q = "select * 
				from 
					account
				where 
					$sortby like '$xSearch%' and
					account_type_id='3' 
				order by
					$sortby";
					
					
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
	  <a href="javascript: document.getElementById('f1').action='?p=account.bond&p1=Selectaccount&c_id=<?=$r['account_id'];?>&xSearch=<?=$xSearch;?>&rtype=<?=$rtype;?>';document.getElementById('f1').submit()"> 
        <?= $r['account'];?>
        </a> </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="javascript: document.getElementById('f1').action='?p=account.bond&p1=Selectaccount&c_id=<?=$r['account_id'];?>&xSearch=<?=$xSearch;?>&rtype=<?=$rtype;?>';document.getElementById('f1').submit()">
        <?= $r['cardno'];?>
        </a></font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $r->address ;?>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= lookUpTableReturnValue('x','account_type','account_type_id','account_type',$r['account_type_id']);?>
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
  <table width="90%" border="0" align="center">
    <tr> 
      <td colspan="5" nowrap><b> Account Bond Entry For: </b><font size='4'><b> 
        <?= '['.$aBOND['cardno'].'] '.$aBOND['account'];?>
        </b></font> </b></td>
    </tr>
    <tr> 
      <td colspan="5" nowrap><font color="#AA6600" size='2' face="Verdana, Arial, Helvetica, sans-serif"> 
        <?=$aBOND['account_class'];?>
        &nbsp;Grocery
        <?= $aBOND['grocery_interval'].'/'.$aBOND['grocery_term'];?>
        Dry Goods
        <?= $aBOND['drygood_interval'].'/'.$aBOND['drygood_term'];?>
        </font></td>
    </tr>
    <tr> 
      <td width="10%" nowrap bgcolor="#FFFFFF"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Date</font></strong></td>
      <td width="9%" nowrap bgcolor="#FFFFFF"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Reference</font></strong><strong></strong></td>
      <td width="7%" nowrap bgcolor="#FFFFFF"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Type</font></strong></td>
      <td width="8%" nowrap bgcolor="#FFFFFF"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Add</font></strong></td>
      <td nowrap bgcolor="#FFFFFF"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Less</font></strong></td>
    </tr>
    <tr> 
      <td nowrap><b> 
        <input name="date" type="text" id="date" value="<?= ymd2mdy($aBOND['date']);?>" size="10" maxlength="10" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" onKeypress="if(event.keyCode==13) {document.getElementById('reference').focus();return false;}">
        <font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"><img src="../graphics/arrow_down.jpg" onClick="popUpCalendar(this, f1.date_expiry, 'mm/dd/yyyy')"></font> 
        </b></td>
      <td nowrap><b> 
        <input name="reference" type="text" id="reference" value="<?= $aBOND['reference'];?>" size="12" maxlength="12"  onKeypress="if(event.keyCode == 13){document.getElementById('type').focus();return false;}">
        </b> </td>
      <td> 
        <?= lookUpAssoc('type',array('Deposit'=>'D','Interest'=>'I','Withdrawal'=>'W'),$aBOND['type']);?>
      </td>
      <td> <input name="debit" type="text" id="debit" value="<?= $aBOND['debit'];?>" size="12" maxlength="12" style="text-align:right" onKeypress="if(event.keyCode == 13){document.getElementById('credit').focus();return false;}"> 
      </td>
      <td><input name="credit" type="text" id="credit" value="<?= $aBOND['credit'];?>" size="12" maxlength="12" style="text-align:right"  onKeypress="if(event.keyCode == 13){document.getElementById('Ok').focus();return false;}"> 
        <input name="p1" type="submit" id="Ok" value="Ok"></td>
    </tr>
  </table>
  <table width="90%" border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#FFFFFF">
    <tr bgcolor="#CCCCCC"> 
      <td colspan="8"><strong><img src="../graphics/redlist.gif" width="16" height="17"> 
        Bond Ledger</strong></td>
    </tr>
    <tr bgcolor="#EFEFEF"> 
      <td width="3%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
      <td width="10%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Date</font></strong></td>
      <td width="13%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Reference</font></strong></td>
      <td width="14%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Type</font></strong></td>
      <td align="center"><strong></strong><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Add</font></strong></td>
      <td width="13%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Less</font></strong></td>
      <td width="13%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Balance</font></strong></td>
      <td width="13%">&nbsp;</td>
    </tr>
    <?
		if ($aBOND['account_id'] != '')
		{
			$q = "select * from bondledger 	
					where
						account_id ='".$aBOND['account_id']."' and
						enable = 'Y' 
					order by
						date";
			$qr = @pg_query($q) or message(pg_errormessage());
		}
		$ctr=$balance=0;
		while ($r = @pg_fetch_object($qr))
		{
			$ctr++;
			$balance += $r->debit - $r->credit;
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
        <input name="delete[]" type="checkbox" value="<?= $r->bondledger_id;?>">
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= ymd2mdy($r->date);?>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $r->reference;?>
        </font></td>
      <td align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $r->type;?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
        </font><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= number_format2($r->debit,2);?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
        <?= number_format2($r->credit,2);?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
        <?= number_format2($balance,2);?>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> &nbsp; 
        <?= lookUpTableReturnValue('x','admin','admin_id','name',$r->admin_id);?>
        </font></td>
    </tr>
    <?
		}
	?>
    <tr bgColor="#CCCCCC"> 
      <td colspan="8"><input name="p1" type="submit" id="p1" value="Delete Checked"> 
        <input name="p1" type="submit" id="p1" value="Add"></td>
    </tr>
  </table>
</form>
<?
if ($focus != '')
{
	echo "<script>document.getElementById('$focus').focus()</script>";
}
?>
