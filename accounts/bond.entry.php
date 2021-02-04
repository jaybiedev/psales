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
			document.getElementById('f1').action= '?p=../accounts/bond.entry&p1=Save';
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

if (!session_is_registered('aBOND'))
{
	session_register('aBOND');
	$aBOND = null;
	$aBOND = array();
}
  include_once('accountbalance.php');

$fields = array('account_id', 'cardno', 'account', 'date_expiry', 'debit','credit');
	
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
			$aBOND[$fields[$c]] = mdy2ymd($_REQUEST[$fields[$c]]);
		}
		else
		{
			$aBOND[$fields[$c]] = $_REQUEST[$fields[$c]];
		}
		if ($aBOND[$fields[$c]] == '' && !in_array($fields[$c], array('account','cardno','mcheck','date_expiry')))
		{
			$aBOND[$fields[$c]] = 0;
		}
	}
}
if ($p1 == 'Save' && $aBOND['debit'] == '0' && $aBOND['credit'] == '0')
{
	message1("[ No Amount Specified... ]");
	$focus='debit';
}
elseif ($p1 == 'Save')
{
	if ($aBOND['date'] == '' or $aBOND['date'] == '--')
	{
		$aBOND['date'] = date('Y-m-d');
	}
	
	$ok = 1;
	begin();
	if ($aBOND['bondledger_id'] == '')
	{
		
		$aBOND['audit'] = 'Encoded by '.$ADMIN['name'].' on '.date('m/d/Y g:ia').'; ';

		//pg_query("begin transaction");
		$time = date('G:i');
		$aBOND['time'] = $time;
		$q = "insert into bondledger ( admin_id, date ";
		$qq = ") values ( '".$ADMIN['admin_id']."', '".$aBOND['date']."'";
		for ($c=0;$c<count($fields);$c++)
		{
			if (in_array($fields[$c], array('account', 'cardno','date_expiry','account_balance','principal_due','interest', 'surcharge'))) continue;
			$q .= ",".$fields[$c];
			$qq .= ",'".$aBOND[$fields[$c]]."'";
		}
		$q .= $qq.")";
		$qr = @pg_query($q) or message1(pg_errormessage().$q);

		if ($qr)
		{
			$ok=1;
			$aBOND['bondledger_id'] = pg_insert_id('bondledger');
			
		}
		else
		{
			$ok=0;
		}	
	}
	else
	{
		$q = "update bondledger set bondledger_id = '".$aBOND['bondledger_id']."'";
		for ($c=0;$c<count($fields);$c++)
		{
			if (in_array($fields[$c], array('account', 'cardno','date_expiry','account_balance','principal_due','interest', 'surcharge'))) continue;

			$q .= ",".$fields[$c]."='".$aBOND[$fields[$c]]."'";
		}
		$q .= " where bondledger_id = '".$aBOND['bondledger_id']."'";

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
	if ($ok=='1')
	{
		commit();

		message("Bond Entry Saved...");
		@include_once('bond.print.php');

		$aBOND = null;
		$aBOND = array();
		$xSearch = '';
		$focus = 'xSearch';
	}
	else
	{
		rollback();		
		message1("Error Unable to  Save...");
	}	
}
elseif ($p1 == 'REPRINT')
{
		$q = "select * 
						from 
							bondledger, account 
						where  
							account.account_id=bondledger.account_id ";
							// and collection.terminal='".$SYSCONF['TERMINAL']."' ";
	if ($xSearch !='')
	{
		$q .= " and bondledger_id = '$xSearch'";
	}
	$q .= "		order by 
							bondledger_id desc offset 0 limit 1";



//	$q = "select * from collection, account where  account.account_id=collection.account_id order by bondledger_id desc offset 0 limit 1";
	$qr = @pg_query($q) or message(pg_errormessage());
	$r = @pg_fetch_assoc($qr);
	$aBOND = $r;
//	if ($_SERVER['REMOTE_ADDR'] == '10.0.0.4') 
//	{
//		print_r($aBOND);
//	}
/*
	$q = "select * from reward where invoice='".$aBOND['bondledger_id']."' and account_id='".$aBOND['account_id']."'";
	$qr = @pg_query($q) or message(pg_errormessage());
	$r = @pg_fetch_object($qr);
	$aBOND['reward_total'] = $r->points_in;
*/
	include_once('accountbalance.php');
	$aReward = rewardBalance($aBOND['account_id']);

	$aBOND['REPRINT'] = 1;
	@include_once('bond.print.php');
	
	$aBOND = null;
	$aBOND = array();
	$aBOND['date'] = date('Y-m-d');
	$focus = 'xSearch';
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
				account.date_expiry
		 from 
		 		account
		where 
				account.account_id='$c_id'";
	$qr = @pg_query($q) or message(db_error());			
	$r = @pg_fetch_assoc($qr);
	
	$aBOND = $r;
	$aBal = customerDue($r['account_id'], mdy2ymd($cutoff_date));	

	$aBOND['account_balance'] = round($aBal['balance'],2);
	$aBOND['amount_due'] =round($aBal['principal_due'],2);
	$aBOND['interest'] =round($aBal['interest_due'],2);
	$aBOND['total_due'] =round($aBal['total_due'],2);

	$p1='selectaccountId';
	$focus = 'debit';
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
		
		$aBOND = $r;
		$aBal = customerDue($r['account_id'], mdy2ymd($cutoff_date));	
		
		$aBOND['account_balance'] = round($aBal['balance'],2);
		$aBOND['amount_due'] =round($aBal['principal_due'],2);
		$aBOND['interest'] =round($aBal['interest_due'],2);
		$aBOND['total_due'] =round($aBal['total_due'],2);
	
		$p1='selectaccountId';
		$focus = 'debit';
	}	
}
?>
<form action="" method="post" name="f1" id="f1" style="margin:10px">
  <table width="90%" border="0" align="center" cellpadding="0" cellspacing="0" bordercolor="#BBCBE6">
    <tr> 
      <td width="0%" height="22" background="../graphics/table_left.PNG">&nbsp;</td>
      <td width="99%" background="../graphics/table_horizontal.PNG"><font color="#FFFF99" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Bond 
        Posting</strong></font></td>
      <td width="0%" background="../graphics/table_right.PNG">&nbsp; </td>
    </tr>
    <tr valign="middle" bgcolor="#DDE5F3"> 
      <td height="45" colspan="3"><font size="2">
        Search Account</font> <input name="xSearch" type="text" id="xSearch" value="<?= $xSearch;?>" onKeypress="if(event.keyCode == 13){document.getElementById('Go').click();return false;}"> 
        <?=lookUpAssoc('sortby',array('Card No.'=>'cardno','Name'=>'account','Account No'=>'account_code'),$sortby);?>
        <input name="p1" type="submit" id="Go" value="Go"> <input name="p12" type="button" id="p12" value="Close" onClick="window.location='?p='"> 
        <br> </td>
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
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href="javascript: document.getElementById('f1').action='?p=bond.entry&p1=Selectaccount&c_id=<?=$r['account_id'];?>&xSearch=<?=$xSearch;?>&rtype=<?=$rtype;?>';document.getElementById('f1').submit()"> 
        <?= $r['account'];?>
        </a> </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="javascript: f1.action='?p=bond.entry&p1=Selectaccount&c_id=<?=$r['account_id'];?>&xSearch=<?=$xSearch;?>&rtype=<?=$rtype;?>';f1.submit()">
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
  <table width="90%" border="0" align="center" cellpadding="2" cellspacing="1" bgcolor="#DADADA">
    <tr background="../graphics/table_horizontal.PNG"> 
      <td colspan="3"><font color="#EFEFEF" size="2" face="Verdana, Arial, Helvetica, sans-serif">Entry 
        Details</font></td>
    </tr>
    <tr> 
      <td width="19%" height="23"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Card 
        No.</font></td>
      <td width="41%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="cardno" type="text" class="altText" value="<?= $aBOND['cardno'];?>" size="12"  readOnly>
        Account No. 
        <input name="account_code" type="text"  class="altText" id="account_code" value="<?= $aBOND['account_code'];?>" readOnly>
        <input name="account_id" type="hidden" class="altText" id="account_id" value="<?= $aBOND['account_id'];?>" size="12"  readOnly>
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
            <td><img src="../graphics/f10.jpg" width="36" height="36" id="f10" name="f10" onClick="document.getElementById('f1').action='?p=bond.entry&p1=Save'; document.getElementById('f1').submit();"></td>
            <td>Finish</td>
          </tr>
          <tr> 
            <td><a href="javascript: document.getElementById('f1').action= '?p=../accounts/bond.entry&p1=REPRINT'; document.getElementById('f1').submit()"><img src="../graphics/altR.jpg" alt="R" width="60" height="34" border="0"></a></td>
            <td>Re-Print Receipt</td>
          </tr>
        </table></td>
    </tr>
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Account</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="account" type="text" class="bigText" id="account" value="<?= $aBOND['account'];?>" size="40" readOnly>
        </font></td>
    </tr>
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Expiry Date</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="date_expiry" type="text"  class="altText" id="date_expiry" value="<?= ymd2mdy($aBOND['date_expiry']);?>" size="15" readOnly>
        </font></td>
    </tr>
    <tr> 
      <td>&nbsp;</td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; </font></td>
    </tr>
    <tr> 
      <td colspan="2" bgcolor="#CCCCCC"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Bond 
        Entry </strong></font></td>
    </tr>
    <tr> 
      <td height="22"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Add'l 
        Bond Amount</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="debit" type="text"  class="bigText" id="debit" value="<?= $aBOND['debit'];?>" size="15"   style="text-align:right"  onKeypress="if(event.keyCode==13) {document.getElementById('credit').focus();return false;}">
        </font></td>
    </tr>
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Widthdrawal 
        from Bond</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="credit" type="text"  class="bigText" id="credit" value="<?= $aBOND['credit'];?>" size="15"  style="text-align:right"  onKeypress="if(event.keyCode==13) {document.getElementById('f10').focus();return false;}">
        </font></td>
    </tr>
    <tr> 
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
  </table>
</form>
<?
if ($focus != '')
{
	echo "<script>document.getElementById('$focus').focus()</script>";
}
?>