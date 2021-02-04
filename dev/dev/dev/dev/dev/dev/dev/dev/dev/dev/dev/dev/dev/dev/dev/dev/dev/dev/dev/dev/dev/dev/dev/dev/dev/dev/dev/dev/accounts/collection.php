<script>
function vTotalPaid()
{
	if (parseFloat(document.getElementById('amount_cash').value) > 0)
	{
		document.getElementById('amount_cash').value = twoDecimals(document.getElementById('amount_cash').value);
	}	
	if (parseFloat(document.getElementById('amount_check').value) > 0)
	{
		document.getElementById('amount_check').value = twoDecimals(document.getElementById('amount_check').value);
	}	
	document.getElementById('amount_total').value = twoDecimals(1*(document.getElementById('amount_cash').value) + 1*(document.getElementById('amount_check').value));
	return false;
}

var	isNS = (navigator.appName	== "Netscape") ? 1 : 0;
var	EnableRightClick = 0;

if(isNS) 
document.captureEvents(Event.MOUSEDOWN||Event.MOUSEUP);
document.onhelp=function(){event.returnValue=false};


keys = new Array();
keys["f112"] = 'f1';
keys["f113"] = 'f2';
keys["f114"] = 'f3';
keys["f115"] = 'f4';
keys["f116"] = 'f5';
keys["f117"] = 'f6';
keys["f118"] = 'f7';
keys["f119"] = 'f8';
keys["f120"] = 'f9';
keys["f121"] = 'f10';
keys["f122"] = 'f11';
keys["f123"] = 'f12';
keys["a38"] = 'a38';
keys["a40"] = 'a40';

function mischandler(){
	if(EnableRightClick==1){ return	true;	}
	else {return false;	}
}
function mousehandler(e){
	if(EnableRightClick==1){ return	true;	}
	var	myevent	=	(isNS) ? e : event;
	var	eventbutton	=	(isNS) ? myevent.which : myevent.button;
	if((eventbutton==2)||(eventbutton==3)) return	false;
}
function keyhandler(e) 
{
	var myevent = (isNS) ? e : window.event;
	mycode=myevent.keyCode

	if (myevent.keyCode==96)
	{
   	    EnableRightClick = 1;
	}
	else if(keys["a"+myevent.keyCode])
	{
	}
	else if(keys["f"+myevent.keyCode])
	{
		if (mycode == 121)
		{
			document.getElementById('f1').action= '?p=../accounts/collection&p1=Save';
			document.getElementById('f1').submit();
		}
		return false;
	}	
	return;
}

document.oncontextmenu = mischandler;
document.onmousedown = mousehandler;
document.onmouseup = mousehandler;
document.onkeydown = keyhandler;
	
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
	.bigNum {
	background-color: #FFFFFF;
	font-family: verdana;
	border: #CCCCCC 1px solid;
	font-size: 16px;
	font-weight: bold;
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
if (!chkRights2('collection','mview',$ADMIN['admin_id']))
{
	message('You have [ NO ] permission to access this area...');
	exit;
}

if (!session_is_registered('aColl'))
{
	session_register('aColl');
	$aColl = null;
	$aColl = array();
}
  include_once('accountbalance.php');

$fields = array('account_id', 'cardno', 'account', 'date_expiry', 'account_balance', 'principal_due', 
						'amount_due', 'surcharge' , 'interest', 'mcheck', 'amount_check','date_check', 'amount_cash',
						'amount_total');
	
if ($p1 == ''  or $p1 == 'New')
{
	$focus = 'xSearch';
}					
if (!in_array($p1,array(null,'showaudit','Load')))
{
	for ($c=0;$c<count($fields);$c++)
	{
		if (substr($fields[$c],0,4) == 'date')
		{
			$aColl[$fields[$c]] = mdy2ymd($_REQUEST[$fields[$c]]);
		}
		else
		{
			$aColl[$fields[$c]] = $_REQUEST[$fields[$c]];
		}
		if ($aColl[$fields[$c]] == '' && !in_array($fields[$c], array('account','cardno','mcheck','date_expiry')))
		{
			$aColl[$fields[$c]] = 0;
		}
	}
}
if ($p1 == 'Save' && $aColl['amount_total'] == '0')
{
	message1("No Amount Paid...");
}
elseif ($p1 == 'Save')
{
	if ($aColl['date'] == '' or $aColl['date'] == '--')
	{
		$aColl['date'] = date('Y-m-d');
	}
	
	$ok = 1;
	begin();
	if ($aColl['collection_id'] == '')
	{
		
		$aColl['audit'] = 'Encoded by '.$ADMIN['name'].' on '.date('m/d/Y g:ia').'; ';

		//pg_query("begin transaction");
		$time = date('G:i');
		$aColl['time'] = $time;
		$q = "insert into collection ( time, admin_id, status, date, terminal ";
		$qq = ") values ('$time', '".$ADMIN['admin_id']."','S', '".$aColl['date']."',  '".$SYSCONF['TERMINAL']."'";
		for ($c=0;$c<count($fields);$c++)
		{
			if (in_array($fields[$c], array('account', 'cardno','date_expiry','account_balance','principal_due','interest', 'surcharge'))) continue;
			$q .= ",".$fields[$c];
			$qq .= ",'".$aColl[$fields[$c]]."'";
		}
		$q .= $qq.")";
		$qr = @pg_query($q) or message1(pg_errormessage().$q);

		if ($qr)
		{
			$ok=1;
			$aColl['collection_id'] = pg_insert_id('collection');
			
		}
		else
		{
			$ok=0;
		}	
	}
	else
	{
		$q = "update collection set collection_id = '".$aColl['collection_id']."'";
		for ($c=0;$c<count($fields);$c++)
		{
			if (in_array($fields[$c], array('account', 'cardno','date_expiry','account_balance','principal_due','interest', 'surcharge'))) continue;

			$q .= ",".$fields[$c]."='".$aColl[$fields[$c]]."'";
		}
		$q .= " where collection_id = '".$aColl['collection_id']."'";

		$qr = @pg_query($q) or message1(pg_errormessage().$q);
		if ($qr)
		{
			$ok=1;
		}
		else
		{
			$ok=0;
		}	
	}
	if ($ok == 1)
	{
		$q = "select * from accountledger where invoice='".$aColl['collection_id']."' and type='P'";
		$qr = @pg_query($q) or message (pg_errormessage());
		if (@pg_num_rows($qr) ==0)
		{
			$aColl['invoice'] = $aColl['collection_id'];
			$aColl['credit']  = $aColl['amount_total'];
			
			$ledger_fields = array('account_id', 'credit', 'invoice', 'date');
			$q = "insert into accountledger ( admin_id, status, type";
			$qq = ") values ('".$ADMIN['admin_id']."','S', 'P'";
			for ($c=0;$c<count($ledger_fields);$c++)
			{
				$q .= ",".$ledger_fields[$c];
				$qq .= ",'".$aColl[$ledger_fields[$c]]."'";
			}
			$q .= $qq.")";
			$qr = @pg_query($q) or message1(pg_errormessage().$q);
			if ($qr)
			{
				$ok=1;
				$aColl['accountledger_id'] = pg_insert_id('accountledger');
			}
			else
			{
				$ok=0;
			}	
		}
	}
	if ($ok=='1')
	{
		commit();

		include_once('account.postpayment.php');
		$aPost = postPayment($aColl['account_id'], $aColl['amount_total'], $aColl['date'], $aColl['date']);

		$q = "update accountledger set credit_balance='".$aPost['credit_balance']."'
					where accountledger_id= '".$aColl['accountledger_id']."'";
		@pg_query($q) or message(pg_errormessage());

		$reward_grocery =  0;
		$reward_drygood = 0;
		if ($aPost['Ok'] == 0)
		{
			message1('Error Occurred in Computing Rewards Points...'.$aPost['message']);
		}
		else
		{		
			
			if ($SYSCONF['CHG_GRC_POINT'] > 0)
			{
				$reward_grocery = round($aPost['applied_grocery']/$SYSCONF['CHG_GRC_POINT'],2);
				$reward_drygood = round($aPost['applied_drygood']/$SYSCONF['CHG_DRY_POINT'],2);
			}
			else
			{
				$reward_grocery =  0;
				$reward_drygood = 0;
			}
		}	
		$reward_total = $reward_grocery + $reward_drygood;
		$aColl['reward_total'] = $reward_total;

		if ($aColl['reward_total'] > 0)
		{
			$q = "insert into reward (sales_header_id, invoice, type, date,account_id,
	                       points_in, amount_in, points_out, amount_out, terminal)
					values ('0','".$aColl['collection_id']."', '1','".$aColl['date']."','".$aColl['account_id']."', 
					     '".$aColl['reward_total']."','".$aColl['amount_total']."','0', '0', '".$SYSCONF['TERMINAL']."')";
			$qr = @pg_query($q) or message('Unable to save rewards...'.pg_errormessage());
		}

		include_once('accountbalance.php');
		$aReward = rewardBalance($aColl['account_id']);

		message("Payment Saved...");
		include_once('collection.print.php');

		$aColl = null;
		$aColl = array();
		$xSearch = '';
		$focus = 'xSearch';
	}
	else
	{
		rollback();		message1("Error Unable to  Save...");
	}	
}
elseif ($p1 == 'REPRINT')
{
		$q = "select * 
						from 
							collection, account 
						where  
							account.account_id=collection.account_id ";
							// and collection.terminal='".$SYSCONF['TERMINAL']."' ";
	if ($xSearch !='')
	{
		$q .= " and collection_id = '$xSearch'";
	}
	$q .= "		order by 
							collection_id desc offset 0 limit 1";



//	$q = "select * from collection, account where  account.account_id=collection.account_id order by collection_id desc offset 0 limit 1";
	$qr = @pg_query($q) or message(pg_errormessage());
	$r = @pg_fetch_assoc($qr);
	$aColl = $r;
//	if ($_SERVER['REMOTE_ADDR'] == '10.0.0.4') 
//	{
//		print_r($aColl);
//	}

	$q = "select * from reward where invoice='".$aColl['collection_id']."' and account_id='".$aColl['account_id']."'";
	$qr = @pg_query($q) or message(pg_errormessage());
	$r = @pg_fetch_object($qr);
	$aColl['reward_total'] = $r->points_in;

	include_once('accountbalance.php');
	$aReward = rewardBalance($aColl['account_id']);

	$aColl['REPRINT'] = 1;
	include_once('collection.print.php');
	
	$aColl = null;
	$aColl = array();
	$aColl['date'] = date('Y-m-d');
	$focus = 'xSearch';
}
if ($c_id!= ''&& $p1 == 'Selectaccount')
{
	$q = "select * from accountpost where enable='Y' and type='A' order by cutoff_date desc ";
	$qr = @pg_query($q) or message(pg_errormessage());
	$r = @pg_fetch_object($qr);
	
	$c = explode('-',$r->cutoff_date);
	$g = explode('-',$r->grace_date);
	if ($c[1] < 12)
	{
		$mo=$c[1]+1;
		$cutoff_date = $mo.'/'.$c[2].'/'.$c[0];
	}
	else
	{
		$yr=$c[0]+1;
		$cutoff_date = '01/'.$c[2].'/'.$yr;
	}
	$aColl=null;
	$aColl=array();
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
				account.account_id='$c_id'";
	$qr = @pg_query($q) or message(db_error());			
	$r = @pg_fetch_assoc($qr);
	
	$aColl = $r;
	$aBal = customerDue($r['account_id'], mdy2ymd($cutoff_date));	

	$aColl['account_balance'] = round($aBal['balance'],2);
	$aColl['amount_due'] =round($aBal['principal_due'],2);
	$aColl['interest'] =round($aBal['interest_due'],2);
	$aColl['total_due'] =round($aBal['total_due'],2);

	$p1='selectaccountId';
	$focus = 'amount_cash';
}	
elseif ($p1 == 'Go' && $sortby != 'account')
{
	$q = "select * from accountpost where enable='Y' and type='A' order by cutoff_date desc ";
	$qr = @pg_query($q) or message(pg_errormessage());
	$r = @pg_fetch_object($qr);
	
	//--> advancing cutoff date to next month
	
	$c = explode('-',$r->cutoff_date);
	$g = explode('-',$r->grace_date);
	if ($c[1] < 12)
	{
		$mo=$c[1]+1;
		if (strlen($mo)<2) $mo='0'.$mo;
		$advanced_cutoff_date = $mo.'/'.$c[2].'/'.$c[0];
	}
	else
	{
		$yr=$c[0]+1;
		$advanced_cutoff_date = '01/'.$c[2].'/'.$yr;
	}
	
	//--> current cutoff date;
	$current_cutoff_date = ymd2mdy($r->cutoff_date);

	//--> use cutoff date
	$cutoff_date = $current_cutoff_date;
	
	$aColl=null;
	$aColl=array();
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
				$sortby='$xSearch'";
	$qr = @pg_query($q) or message(db_error());			
	
	if (@pg_num_rows($qr) == '0')
	{
		message1(" Searched Account NOT Found...");
		$focus = 'xSearch';
	}
	else
	{
		$r = @pg_fetch_assoc($qr);
		
		$aColl = $r;
		$aBal = customerDue($r['account_id'], mdy2ymd($cutoff_date));	
		
		$aColl['account_balance'] = round($aBal['balance'],2);
		$aColl['amount_due'] =round($aBal['principal_due'],2);
		$aColl['interest'] =round($aBal['interest_due'],2);
		$aColl['total_due'] =round($aBal['total_due'],2);
	
		$p1='selectaccountId';
		$focus = 'amount_cash';
	}	
}
?>
<form action="" method="post" name="f1" id="f1">
  <table width="90%" border="0" align="center">
    <tr>
      <td>Search Account 
        <input name="xSearch" type="text" id="xSearch" value="<?= $xSearch;?>" onKeypress="if(event.keyCode == 13){document.getElementById('Go').click();return false;}">
        <font size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <?=lookUpAssoc('sortby',array('Card No.'=>'cardno','Name'=>'account','Account No'=>'account_code'),$sortby);?>
        </font> 
        <input name="p1" type="submit" id="Go" value="Go">
        <font size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <input name="p12" type="button" id="p12" value="Close" onClick="window.location='?p='">
        </font><br>
        <hr color="#993300"></td>
    </tr>
  </table>

<?
if ($p1 == 'Go' && $sortby == 'account')
  {
  	if ($sortby == '') $sortby = 'cardno';
	  $q = "select * 
				from 
					account
				where 
					$sortby like '$xSearch%' and
					account_type_id!='1' 
				order by
					$sortby";
					
					
		$qr = @pg_query($q)	or message("Error Querying account file...".db_error());
?>
  
  <table width="90%" border="0" align="center" cellpadding="2" cellspacing="1" bgcolor="#EFEFEF">
    <tr bgcolor="#CFD3E7"> 
      <td width="7%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
      <td width="29%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Name</font></strong></td>
      <td width="9%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Card 
        No. </font></strong></td>
      <td width="33%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Address</font></strong></td>
      <td width="10%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Status</font></strong></td>
      <td width="12%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Balance</font></strong></td>
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
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href="javascript: document.getElementById('f1').action='?p=collection&p1=Selectaccount&c_id=<?=$r['account_id'];?>&xSearch=<?=$xSearch;?>&rtype=<?=$rtype;?>';document.getElementById('f1').submit()"> 
        <?= $r['account'];?>
        </a> </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="javascript: f1.action='?p=collection&p1=Selectaccount&c_id=<?=$r['account_id'];?>&xSearch=<?=$xSearch;?>&rtype=<?=$rtype;?>';f1.submit()">
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
    </tr>
    <?
  }
  ?>
  </table>

<?	
	  exit;
  }
 ?>
  <table width="90%" border="0" align="center" cellpadding="2" cellspacing="1" bgcolor="#FFFFFF">
    <tr bgcolor="#EFEFEF"> 
      <td width="19%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Card 
        No.</font></td>
      <td width="41%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="cardno" type="text" class="altText" value="<?= $aColl['cardno'];?>" size="12"  readOnly>
        Account No. 
        <input name="account_code" type="text"  class="altText" id="account_code" value="<?= $aColl['account_code'];?>" readOnly>
        <input name="account_id" type="hidden" class="altText" id="account_id" value="<?= $aColl['account_id'];?>" size="12"  readOnly>
        </font></td>
      <td width="33%" rowspan="13" align="center" valign="top">Hot keys Menu 
        <table width="1%" border="0">
          <tr> 
            <td><img src="../graphics/f1.jpg" width="36" height="34">&nbsp;</td>
            <td>Help</td>
          </tr>
          <tr> 
            <td><img src="../graphics/f2.jpg" width="36" height="34"></td>
            <td>Clear</td>
          </tr>
          <tr> 
            <td><img src="../graphics/f3.jpg" width="36" height="34"></td>
            <td>Delete</td>
          </tr>
          <tr> 
            <td><img src="../graphics/f4.jpg" width="36" height="34"></td>
            <td>Quantity</td>
          </tr>
          <tr> 
            <td><img src="../graphics/f5.jpg" width="36" height="34"></td>
            <td>Price</td>
          </tr>
          <tr> 
            <td><img src="../graphics/f10.jpg" width="36" height="36" id="f10" name="f10" onClick="document.getElementById('f1').action='?p=collection&p1=Save'; document.getElementById('f1').submit();"></td>
            <td>Finish</td>
          </tr>
          <tr> 
            <td><a href="javascript: document.getElementById('f1').action= '?p=../accounts/collection&p1=REPRINT'; document.getElementById('f1').submit()"><img src="../graphics/altR.jpg" alt="R" width="60" height="34" border="0"></a></td>
            <td>Re-Print Receipt</td>
          </tr>
        </table></td>
    </tr>
    <tr bgcolor="#EFEFEF"> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Account</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="account" type="text" class="bigText" id="account" value="<?= $aColl['account'];?>" size="40" readOnly>
        </font></td>
    </tr>
    <tr bgcolor="#EFEFEF"> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Expiry Date</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="date_expiry" type="text"  class="altText" id="date_expiry" value="<?= ymd2mdy($aColl['date_expiry']);?>" size="15" readOnly>
        </font></td>
    </tr>
    <tr bgcolor="#EFEFEF"> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Total Account</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="account_balance" type="text"  class="altNum" id="account_balance" value="<?= $aColl['account_balance'];?>" size="15" readOnly>
        </font></td>
    </tr>
    <tr bgcolor="#EFEFEF"> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Amount Due</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="amount_due" type="text"  class="altNum" id="amount_due" value="<?= $aColl['amount_due'];?>" size="15" readOnly>
        </font></td>
    </tr>
    <tr bgcolor="#EFEFEF"> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Interest/Surcharges</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="interest" type="text" class="altNum" id="interest" value="<?= $aColl['interest'];?>" size="15" readonly="readOnly">
        </font></td>
    </tr>
    <tr bgcolor="#EFEFEF"> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Total Due</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="total_due" type="text"  class="bigNum" id="total_due" value="<?= $aColl['total_due'];?>" size="12" readOnly>
        </font></td>
    </tr>
    <tr bgcolor="#CCCCCC"> 
      <td colspan="2"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Payment 
        &nbsp;</strong></font></td>
    </tr>
    <tr bgcolor="#EFEFEF"> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Cash Amount</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="amount_cash" type="text"  class="altNum" id="amount_cash"  style="font-size:18px;" value="<?= $aColl['amount_cash'];?>" size="12"  onKeypress="if(event.keyCode==13) {document.getElementById('amount_check').focus();return false;}" onBlur="vTotalPaid();return false;">
        </font></td>
    </tr>
    <tr bgcolor="#EFEFEF"> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Check Amount</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="amount_check" type="text"  class="altNum" id="amount_check" value="<?= $aColl['amount_check'];?>" size="12" style="font-size:18px;"  onKeyPress="if(event.keyCode==13) {document.getElementById('mcheck').focus();return false;}"  onBlur="vTotalPaid();return false;">
        </font></td>
    </tr>
    <tr bgcolor="#EFEFEF"> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Check No.</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="mcheck" type="text"  class="altText" id="mcheck" value="<?= $aColl['mcheck'];?>" size="18"  onKeypress="if(event.keyCode==13) {document.getElementById('date_check').focus();return false;}" >
        </font></td>
    </tr>
    <tr bgcolor="#EFEFEF"> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Check Date</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="date_check" type="text"  class="altText" id="date_check" value="<?= ymd2mdy($aColl['date_check']);?>" size="18"  onKeypress="if(event.keyCode==13) {document.getElementById('f10').focus();return false;}" >
        </font></td>
    </tr>
    <tr bgcolor="#EFEFEF"> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Total Payment</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="amount_total" type="text"  class="altNum" id="amount_total" style="font-size:20px; font-weight:bold" value="<?= $aColl['amount_total'];?>" size="15">
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