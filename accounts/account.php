<STYLE TYPE="text/css">
 .altTextFormat {
	background-color: #FFFFFF;
	font-family: verdana;
	border: #CCCCCC 1px solid;
	font-size: 11px;
	color: #000000
	} 
 .hideTextFormat {
	background-color: #FFFFFF;
	font-family: verdana;
	border: #CCCCCC 0px solid;
	font-size: 11px;
	font-color: #FFFFFF;
	color:  #FFFFFF
	} 	
.altButtonFormat {
	background-color: #C1C1C1;
	font-family: verdana;
	border: #4B4B4B 1px solid;
	font-size: 11px;
	padding: 0px;
	margin: 0px;
	color: #1F016D
	} 
.bhigh {
	background-color: #EFEFEF;
	font-family: verdana;
	border: #EFEFEF 2px solid;
	font-size: 13px;
	font-weight: bold;
	padding: 0px;
	margin: 0px;
	color: #1F016D
	} 
.blow {
	background-color: #C1C1C1;
	font-family: verdana;
	border: #CCCCCC 1px solid;
	font-size: 11px;
	padding: 0px;
	margin: 0px;
	color: #1F016D
	} 
		
-->
</STYLE>
<script>
function btn(t)
{
	return;
}
function btnSelect(t)
{
	if (t.id == 'personalinfo')
	{
		t.className = 'bhigh';
		document.getElementById('accountinfo').className = 'blow';
		document.getElementById('cardinfo').className = 'blow';
		document.getElementById('LayerPersonal').style.visibility= "visible";
		document.getElementById('LayerCard').style.visibility= "hidden";
		document.getElementById('LayerAccount').style.visibility= "hidden";
		document.getElementById('LayerLedger').style.visibility= "hidden";
	}

	else if (t.id == 'accountinfo')
	{
		document.getElementById('LayerPersonal').style.visibility= "hidden";
		document.getElementById('LayerAccount').style.visibility= "visible";
		document.getElementById('LayerCard').style.visibility= "hidden";
		document.getElementById('LayerLedger').style.visibility= "hidden";
		document.getElementById('accountinfo').className = "bhigh";
		document.getElementById('personalinfo').className = 'blow';
		document.getElementById('cardinfo').className = 'blow';
		document.getElementById('ledger').className = 'blow';
	}
	else if (t.id == 'cardinfo')
	{
		document.getElementById('LayerPersonal').style.visibility= "hidden";
		document.getElementById('LayerAccount').style.visibility= "hidden";
		document.getElementById('LayerCard').style.visibility= "visible";
		document.getElementById('LayerLedger').style.visibility= "hidden";
		document.getElementById('accountinfo').className = "blow";
		document.getElementById('personalinfo').className = 'blow';
		document.getElementById('cardinfo').className = 'bhigh';
		document.getElementById('ledger').className = 'blow';
	}
	else if (t.id == 'ledger')
	{
		document.getElementById('LayerPersonal').style.visibility= "hidden";
		document.getElementById('LayerAccount').style.visibility= "hidden";
		document.getElementById('LayerCard').style.visibility= "hidden";
		document.getElementById('LayerLedger').style.visibility= "visible";
		document.getElementById('accountinfo').className = "blow";
		document.getElementById('personalinfo').className = 'blow';
		document.getElementById('cardinfo').className = 'blow';
		document.getElementById('ledger').className = 'bhigh';
	}
	
}
function vPix()
{
	f1.pix.src=f1.pixfile.value;
}
function vSubmit(ul)
{
	if (ul.name == 'Cancel')
	{
		if (confirm("Are you sure to CANCEL account Record?"))
		{
			document.f1.action="?p=../account&p1=CancelConfirm"
		}	
		else
		{
			document.f1.action="?p=../account&p1=Cancel"
		}
	}
	else
	{
		document.f1.action="?p=account&p1="+ul.id;
	}	
}
function switchPix(o)
{
	var folder = "../graphics/";
	var n = o.name;
	var obj = new Array('patient_data','address','admission','account','diagnosis');

	for (c = 0;c<obj.length;c++)
	{
		eval("this.f1."+obj[c]).src=folder+obj[c]+"_lo.jpg";
		eval("this."+obj[c]+".style").visibility="hidden";
	}

	o.src=folder+o.name+"_hi.jpg"
	eval("this."+n+".style").visibility = "visible"
	
}

/*
// trapping enter key
nextfield = "account_code"; // name of first box on page
netscape = "";
ver = navigator.appVersion; len = ver.length;
for(iln = 0; iln < len; iln++) if (ver.charAt(iln) == "(") break;
netscape = (ver.charAt(iln+1).toUpperCase() != "C");

function keyDown(DnEvents) 
{ 
	// handles keypress
	// determines whether Netscape or Internet Explorer
	k = (netscape) ? DnEvents.which : window.event.keyCode;
	if (k == 13) 
	{ // enter key pressed
		if (nextfield == 'done') return true; // submit, we finished all fields
		else 
		{ // we're not done yet, send focus to next box
			eval('document.f1.' + nextfield + '.focus()');
			return false;
	    }
	}
}
document.onkeydown = keyDown; // work together to analyze keystrokes
if (netscape) document.captureEvents(Event.KEYDOWN|Event.KEYUP);
*/
</script>
<?
if (!chkRights2('account','mview',$ADMIN['admin_id']))
{
	message1("You have NO acces to Account Master...");
	exit;
}

if (!session_is_registered('aaccount'))
{
	session_register('aaccount');
	$aaccount = null;
	$aaccount = array();
}
$fields = array('account_code','account','address','telefax', 'account_type_id','account_class_id','guarantor_id', 'zero_rated', 'cardname', 
			'cardno', 'telno','remarks', 'credit_limit','date_expiry','lifetime','sdisc','cdisc','enable',
			'date_birth','date_applied','gender','civil_status','no_dependent','salary','employer','occupation',
			'terms', 'days_interval', 'grace_period','surcharge','interest_interval','grocery_charge','bond',
			'cutoff1','cutoff2','years_employed','spouse','branch_id');

if ($p1=='Load' && $id!='' )
{
	$aaccount = null;
	$aaccount = array();
	$q = "select * from account where account_id = '$id'";
	$r = fetch_assoc($q);
	$aaccount = $r;
	
	$q = "select sum(credit) as total_purchase, sum(debit)  as debit from accountledger 
					where enable='Y' and status!='C' and account_id='".$aaccount['account_id']."'";
	$r = fetch_assoc($q);
	$aaccount['total_purchase'] = $r['total_purchase'];
	$aaccount['debit'] = $r['debit'];
	$aaccount['total_due'] = $aaccount['total_current'] = $aaccount['total_purchase'] - $aaccount['debit'];
	
	$q = "select sum(points_in) as points_in, sum(points_out) as points_out from reward where account_id='".$aaccount['account_id']."'";
	$qr = @pg_query($q);
	
	if ($r != '')
	{
		$r = @pg_fetch_assoc($qr);
		$aaccount += $r;
		$aaccount['points_unclaimed'] = $aaccount['points_in'] -$aaccount['points_out'] ;
	}	
}		

if (!in_array($p1,array(null,'showaudit','Load','Next','Previous')))
{
	for ($c=0;$c<count($fields);$c++)
	{
		if (substr($fields[$c],0,4) == 'date')
		{
			if ($aaccount[$fields[$c]]='' or $aaccount[$fields[$c]]=='--' or $aaccount[$fields[$c]]=='//')
			{
				$aaccount[$fields[$c]] = '';
			}
			else
			{
				$aaccount[$fields[$c]] = mdy2ymd($_REQUEST[$fields[$c]]);
			}	
		}
		else
		{
			$aaccount[$fields[$c]] = $_REQUEST[$fields[$c]];
		}
		if ($aaccount[$fields[$c]]==null && in_array($fields[$c], array('sdisc','cdisc',
										'days_interval','surcharge','interest_interval','credit_limit',
										'guarantor_id','years_employed','grocery_charge',
										'no_dependent','bond','cutoff1','cutoff2','salary',
										'grace_period','account_type_id','branch_id')))
		{
			$aaccount[$fields[$c]]=0;
		}


	}

	$aaccount['pixfile_tmp']=$_FILES['pixfile']['tmp_name'];
	$aaccount['pixfile']=$_FILES['pixfile']['name'];
	$x = explode('.',$aaccount['pixfile']);
	$aaccount['pixfile_extension'] = $x[count($x)-1];
	
	if ($aaccount['branch_id'] == 0) $aaccount['branch_id'] = $SYSCONF['BRANCH_ID'];
}	

if ($p1 == 'New')
{
	$aaccount = null;
	$aaccount = array();

	$aaccount['branch_id'] = $SYSCONF['BRANCH_ID'];
}
elseif ($p1 == 'Next')
{
	$q = "select * from account where account > '".$aaccount['account']."' order by account limit 0,1";
	$qr = @pg_query($q);
	if (@pg_num_rows($qr) == 0)
	{
		message("End of file...");
	}	
	else
	{
		$aaccount = null;
		$aaccount = array();
		$r = fetch_assoc($q);
		$aaccount = $r;
	}	

}
elseif ($p1 == 'Previous')
{
	$q = "select * from account where account < '".$aaccount['account']."' order by account desc limit 0,1";
	$qr = @pg_query($q);
	if (@pg_num_rows($qr) == 0)
	{
		message("Beginning of file...");
	}	
	else
	{
		$aaccount = null;
		$aaccount = array();
		$r = fetch_assoc($q);
		$aaccount = $r;
	}	

}
elseif ($p1=='showaudit')
{
	$aaccount['showaudit'] =1;
}
elseif ($p1 == 'Save' && in_array($aaccount['date_expiry'],array('','--','//'))) 
{
	message1('CANNOT Save. No Expiry Date...');
}
elseif ($p1 == 'Save' && $aaccount['account_type_id']<='0')
{
	message1('CANNOT Save. No Account Classification...');
}
elseif ($p1 == 'Save' && $aaccount['account']!='')
{
	if ($aaccount['account_id'] == '')
	{
		$audit = 'Encoded by '.$ADMIN['name'].' on '.date('m/d/Y g:ia').'; ';
		$aaccount['audit'] = $audit;
		$q = "insert into account (account_code,account, address, telefax, telno, remarks, credit_limit, zero_rated, cardname,
						account_type_id,date_expiry, lifetime, sdisc, cdisc, guarantor_id, account_class_id, cardno, occupation,branch_id,date_applied)
				values ('".$aaccount['account_code']."','".$aaccount['account']."','".$aaccount['address']."','".$aaccount['telefax']."','".$aaccount['telno']."',
						'".$aaccount['remarks']."','".$aaccount['credit_limit']."','".$aaccount['zero_rated']."','".$aaccount['cardname']."','".$aaccount['account_type_id']."','".$aaccount['date_expiry']."',
						'".$aaccount['lifetime']."','".$aaccount['sdisc']."','".$aaccount['cdisc']."','".$aaccount['guarantor_id']."',
						'".$aaccount['account_class_id']."','".$aaccount['cardno']."','".$aaccount['occupation']."','".$aaccount['branch_id']."','$aaccount[date_applied]')";
	}
	else
	{
		$audit = $aaccount['audit'].'Updated by '.$ADMIN['name'].' on '.date('m/d/Y g:ia').'; ';
		$aaccount['audit'] = $audit;
		$q = "update account set audit='$audit'  ";
	
		for($c=0;$c<count($fields);$c++)
		{
			$q .= ", ".$fields[$c] ."='".$aaccount[$fields[$c]]."'";
		}
		$q .= " where account_id='".$aaccount['account_id']."'";
	}
	$qr = @pg_query($q) or message1("Error saving Account data...".pg_errormessage().$q);
	if ($qr)
	{
		if ($aaccount['account_id'] == '')
		{
			$aaccount['account_id'] = pg_insert_id('account');
		}
		if ($aaccount['pixfile_tmp'] != '')
		{
			$extension = $aaccount['pixfile_extension'];
			$picture_source = $aaccount['pixfile_tmp'];
			$picture_file = "images\account_".strtolower($aaccount['account_id']).".".strtolower($extension);
			$pix = "account_".strtolower($aaccount['account_id']).".".strtolower($extension);
			if (!copy($picture_source,$picture_file))
			{
				message("Unable to upload picture....".$picture_source." To ".$picture_file);
			}
			else
			{
				$aaccount['pix'] = $pix;
				$q = "update account set pix='".$aaccount['pix']."' where account_id='".$aaccount['account_id']."'";
				$qr = @pg_query($q) or message("Unable to update picture filename to database...");
				if ($qr) message("Picture file name updated...");
			}
		}
		message("Account Data Saved...");
	}
}

if ($aaccount['account_id'] > '0')
{
	include_once('accountbalance.php');
	$aBal = customerBalance($aaccount['account_id']);
}
else
{
	$aBal = null;
	$aBal = array();
}
$q = "select * from accountpost where enable='Y' order by date desc offset 0 limit 1";
$qar  = @pg_query($q);
$ra = @pg_fetch_assoc($qar);
$aaccount['date_posted'] = $ra['date'];
?>
<script type="text/javascript" src="tabber.js"></script>
<link rel="stylesheet" href="tab.css" TYPE="text/css" MEDIA="screen">
<link rel="stylesheet" href="tab-print.css" TYPE="text/css" MEDIA="print">

<script type="text/javascript">

/* Optional: Temporarily hide the "tabber" class so it does not "flash"
   on the page as plain HTML. After tabber runs, the class is changed
   to "tabberlive" and it will appear. */

document.write('<style type="text/css">.tabber{display:none;}<\/style>');
</script>
<body bgcolor="#EFEFEF"><form name="f1" id="f1" method="post" action="" style="margin:10px"  enctype="multipart/form-data" >
  <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr> 
      <td><img src="../graphics/post_discussion.gif" width="20" height="20"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><b> 
        </b></font>Search 
        <input name="search" type="text" id="search" value="<?= $search;?>"  onKeypress="if(event.keyCode == 13){document.getElementById('go').click(); return false;}">
        <?=lookUpAssoc('searchby',array('Card No.'=>'cardno','Account No.'=>'account_code','Account Name'=>'account', 'Record Id'=>'account_id'),$searchby);?>
 		<input name="p1" type="button" id="go" value="Go" accesskey="G" onClick="f1.action='?p=account.browse&p1=Go&account_class_id=&search='+search.value+'&searchby='+searchby.value;f1.submit()">
        <input type="button" name="Submit2" value="Add New" onClick="window.location='?p=account&p1=New'" accesskey="N">
        <input type="button" name="Submit222" value="Browse" onClick="window.location='?p=account.browse&act=C'" accesskey="C">
        <input type="button" name="Submit23" value="Close" onClick="window.location='?p='" accesskey="C">
        <hr color="#CC0000" style="margin:0"></td>
    </tr>
  </table>
  <table width="95%" border="0" cellspacing="0" cellpadding="0" align="center">
      <td colspan="2"> <table width="100%" cellpadding="0" cellspacing="1" bgcolor="#EFEFEF">
          <tr> 
            <td height="18" width="23%"><b><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Name<br>
              <input name="account" type="text" id="account" value="<?= stripslashes($aaccount['account']);?>" size="30" maxlength="40" onFocus="nextfield ='account_code'" onKeypress="if(event.keyCode==13) {document.getElementById('account_code').focus();return false;}" onBlur="document.getElementById('cardname').value=this.value">
              </font></b></td>
            <td height="18" width="10%"><b><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Account 
              # <br>
              <input name="account_code"  onFocus="nextfield ='cardno'" type="text" id="account_code" value="<?= $aaccount['account_code'];?>" size="12" maxlength="15" onChange="xajax_vAcctno(xajax.getFormValues('f1'))" onKeypress="if(event.keyCode==13) {document.getElementById('cardno').focus();return false;}">
              </font></b></td>
            <td height="18" width="9%"><b><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Card 
              No.<br>
              <input name="cardno"  onFocus="nextfield ='date_applied'" type="text" id="cardno" onChange="xajax_vCardno(xajax.getFormValues('f1'))" value="<?= $aaccount['cardno'];?>" size="12" maxlength="15" onKeypress="if(event.keyCode==13) {document.getElementById('date_applied').focus();return false;}">
              </font></b></td>
            <td width="1%" height="18" nowrap><b><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Application</font></b><br> 
              <input name="date_applied"  onFocus="nextfield ='date_expiry'" type="text" id="date_applied" value="<?= ymd2mdy($aaccount['date_applied']);?>" size="10" maxlength="10" onKeypress="if(event.keyCode==13) {document.getElementById('cardname').focus();return false;}"  onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')"></font></b> 
              <img src="../graphics/dwn-arrow-grn.gif" width="12" height="12" onClick="popUpCalendar(this, f1.date_applied, 'mm/dd/yyyy')"> &nbsp;
            </td>
            <td width="49%"><b><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Branch<br>
             <?= lookUpTable2('branch_id', 'branch','branch_id','branch',$aaccount['branch_id']);?> </font></b></td>
            <td height="18" width="8%" align="center"><b><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Id<br>
              <input name="account_id" type="text" id="account_id" value="<?= str_pad($aaccount['account_id'],8,'0',str_pad_left);?>" size="12" maxlength="12" style="text-align:center; border:0; background-color:#EFEFEF; padding:0;" readOnly >
              </font></b></td>
          </tr>
        </table></td>
    </tr>
    <tr> 
      <td colspan="2" height="400px" valign="top"> <div class="tabber" style="width:95%; left:20px"> 
          <div class="tabbertab" style="top-margin:0px"> 
            <h2>Personal Info</h2>
            <p>
              <? include_once('account.personal.php');?>
            </p>
          </div>
          <div class="tabbertab"> 
            <h2>Account Info</h2>
            <p>
              <? include_once('account.account.php');?>
            </p>
          </div>
          <div class="tabbertab"> 
            <h2>Card Info</h2>
            <p>
              <? include_once('account.card.php');?>
            </p>
          </div>
          <div class="tabbertab"> 
            <h2>Ledger</h2>
            <p style="margin:1px">
              <? include_once('account.ledger.php');?>
            </p>
          </div>
          <div class="tabbertab"> 
            <h2>Balance</h2>
            <p>
              <? include_once('account.balance.php');?>
            </p>
          </div>
        </div>
      </td>
    </tr>
	<tr>
	  <td bgcolor="#EFEFEF"><table width="1%" border="0" cellpadding="1" cellspacing="1" bgcolor="#CCCCCC">
          <tr bgcolor="#FFFFFF"> 
            <td nowrap width="3%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              <a  accesskey="S" href="javascript: f1.action='?p=account&p1=Save';f1.submit();"> 
              <input type="image" src="../graphics/save.jpg" alt="Save This Claim Form" name="Save" width="57" height="15" border="0" id="Save" onClick="f1.action='?p=account&p1=Save';f1.submit();" tabIndex="99">
              </a> </strong></font></td>
            <td nowrap width="46%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              <img src="../graphics/print.jpg" alt="Print This  Form"  onClick="window.print()" accesskey="P"> 
              </strong></font></td>
            <!--<td nowrap width="26%"> <input name="Cancel" id="Cancel" type="image" src="../graphics/cancel.jpg" onClick="vSubmit(this)" alt="Cancel This Entry" width="77" height="20"></td>-->
            <td nowrap width="25%"> <input type='image' name="New" id="New" onClick="f1.action='?p=account&p1=New';f1.submit();"  src="../graphics/new.jpg" alt="New Claim Form" width="63" height="20" accesskey="N"> 
            </td>
          </tr>
        </table></td>
	</tr>
  </table>
      </form>
<div align="center"><a href='?p=account&p1=Previous'> <img src="../graphics/redarrow_left.gif" width="4" height="7" border="0"> 
  Previous</a>&nbsp;| &nbsp;<a href='?p=account&p1=Next'>Next</a> <a href="?p=account&p1=Next"><img src="../graphics/redarrow_right.gif" width="4" height="7" border="0"></a></div>
<script>document.getElementById('search').focus();</script>