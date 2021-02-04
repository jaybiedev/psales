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
<?
if (!session_is_registered("paymast"))
{
	session_register("paymast");
	$paymast=null;
	$paymast=array();
}

if (!in_array($p1, array('','Edit','Print')))
{
	$fields = array(  'idnum', 'etype', 'branch_id', 'department_id', 'section_id', 'efirst', 'pay_category','rank',
  			'elast', 'emiddle', 'address', 'position', 'telno', 'mobile','date_birth', 'date_employ', 'sex', 'civil_status',
  			'tin', 'sssid', 'phicid', 'pagibigid', 'taxcode', 'ratem', 'adwr', 'sssw', 'taxw', 'pagibigw','phicw',
  			'tenureallowance', 'tenurew', 'cola', 'emp_status', 'atm', 'hourly',  'level_id', 'enable');

	for($c=0;$c<count($fields);$c++)
	{
		$item = $fields[$c];
		if (substr($fields[$c],0,4) == 'date')
		{
			$paymast[$item] = mdy2ymd(chop($_REQUEST[$item]));
		}
		else
		{
			$paymast[$item] = chop($_REQUEST[$item]);
		}
		
		if ( $paymast[$item] == '' && in_array($item, array('etype','department_id','section_id','level_id','civil_status','emp_status','ratem','hourly','adwr','cola','branch_id','tenureallowance')))
		{
			$paymast[$item] = 0;
		}
	}
}

if ($p1=='Edit' && $paymast_id=='')
{
	message("Please specify Employee...");
}
elseif ($p1=='Add New' or $p1 == 'New')
{
	$paymast=null;
	$paymast=array();
}
elseif ($p1 == 'Load' && $id == '')
{
	$paymast=null;
	$paymast = array();
	echo "<script> window.location='?p=paymast.browse'</script>";
	exit;
	
}
elseif ($p1 == 'Load')
{
	$qr = @pg_query("select * from paymast where paymast_id='$id'") or message1(pg_errormessage());
	$paymast=null;
	$paymast=array();
	$paymast = @pg_fetch_assoc($qr);
}
elseif ($p1=='Save')
{	
	if ($paymast['paymast_id'] == '') // || $paymast['paymast_id'] == '31')
	{
		$q = "";
		reset($fields);
		$q = 'insert into paymast (';
		$q1 = 'values (';
		$c=0;
		while (list ($key, $val) = each ($fields)) 
		{
			$item = chop($val);
			if ($c > 0)
			{
				$q .= ',';
				$q1 .= ',';
			}
			$q .= $item;
			$q1 .= "'".$paymast[$item]."'";
			$c++;
		}
		$q .= ') '.$q1.')';
		$qr= @pg_query($q) or message1 (pg_errormessage().$q);
		if ($qr && pg_affected_rows($qr)>0) 
		{

			$qr = query("select currval('paymast_paymast_id_seq'::text)");
			$r = pg_fetch_object($qr);
			$paymast["paymast_id"]= $r->currval;
			message("New Employee Data Added...");
		}
		else 
		{
			message("WARNING!! Error saving Employee data....");
		}	

	}
	else 
	{
		reset($fields);
		$q = '';
		while (list ($key, $val) = each ($fields)) 
		{
			$item = chop($val);
			if ($q == '')
			{
				$q = "update paymast set "."$item	='".$paymast[$item]."'";
			}
			else
			{
				$q .= ','."$item	='".$paymast[$item]."'";
			}	
		}			
		$q .= " where paymast_id='".$paymast["paymast_id"]."'";

		$qr=@pg_query($q) ;
		if (!$qr) 
		{
			message1("WARNING!! Error updating employee data...".pg_errormessage().$q);
		}
		else
		{
						message("Employee Data Updated...");

		}

	}	
		//insert or update paymast record

}

?>
<script type="text/javascript" src="tabber.js"></script>
<link rel="stylesheet" href="tab.css" TYPE="text/css" MEDIA="screen">
<link rel="stylesheet" href="tab-print.css" TYPE="text/css" MEDIA="print">

<script type="text/javascript">

/* Optional: Temporarily hide the "tabber" class so it does not "flash"
   on the page as plain HTML. After tabber runs, the class is changed
   to "tabberlive" and it will appear. */

document.write('<style type="text/css">.tabber{display:none;}<\/style>');
</script><br>
<form name="f1" id="f1" method="post" action="" style="margin:0"  enctype="multipart/form-data" >
  <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr> 
      <td><img src="../graphics/post_discussion.gif" width="20" height="20"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><b> 
        </b></font>Search 
        <input name="search" type="text" id="search" value="<?= $search;?>"  onKeypress="if(event.keyCode == 13){document.getElementById('go').click(); return false;}">
        <?= lookUpAssoc('searchby',array('Employee Lname'=>'elast','Idnum'=>'idnum','Record Id'=>'paymast_id','Position'=>'position'), $searchby);?>
        <input name="p1" type="button" id="go" value="Go" accesskey="G" onClick="f1.action='?p=paymast.browse&p1=Go&search='+search.value+'&searchby='+searchby.value;f1.submit()">
        <input type="button" name="Submit" value="Add New" onClick="window.location='?p=paymast&p1=New'" accesskey="N">
        <input type="button" name="Submit" value="Browse" onClick="window.location='?p=paymast.browse'" accesskey="B">
        <input type="button" name="Submit" value="Close" onClick="window.location='?p='" accesskey="C">
        <hr color="#CC0000" style="margin:0"></td>
    </tr>
  </table>
  <table width="95%" border="0" cellspacing="0" cellpadding="0" align="center">
      <td colspan="2"> <table width="100%" cellpadding="0" cellspacing="1" bgcolor="#EFEFEF">
          <tr> 
            <td height="18" width="15%"><b><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Last 
              Name<br>
              <input name="elast" type="text" id="elast" value="<?= stripslashes($paymast['elast']);?>" size="25" maxlength="25" onKeypress="if(event.keyCode==13) {document.getElementById('efirst').focus();return false;}">
              </font></b></td>
            <td height="18" width="21%"><b><font size="2" face="Verdana, Arial, Helvetica, sans-serif">First 
              Name <br>
              <input name="efirst" type="text" id="efirst" value="<?= stripslashes($paymast['efirst']);?>" size="25" maxlength="25" onKeypress="if(event.keyCode==13) {document.getElementById('emiddle').focus();return false;}">
              </font></b></td>
            <td height="18" width="10%"><b><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Middle 
              Name <br>
              <input name="emiddle" type="text" id="emiddle" value="<?= stripslashes($paymast['emiddle']);?>" size="25" maxlength="25" onKeypress="if(event.keyCode==13) {document.getElementById('idnum').focus();return false;}">
              </font></b></td>
            <td height="18" width="46%"><b><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Id 
              No. <br>
              <input name="idnum" type="text" id="idnum" value="<?= stripslashes($paymast['idnum']);?>" size="10" maxlength="10" onKeypress="if(event.keyCode==13) {document.getElementById('date_birth').focus();return false;}">
              </font></b></td>
            <td height="18" width="8%" align="center"><b><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Rec.#<br>
              <input name="account_id" type="text" id="account_id" value="<?= str_pad($paymast['paymast_id'],8,'0',str_pad_left);?>" size="12" maxlength="12" style="text-align:center; border:0; background-color:#EFEFEF; padding:0;" readOnly >
              </font></b></td>
          </tr>
        </table></td>
    </tr>
    <tr> 
      <td colspan="2" bgcolor="#FFFFFF" height="400px" valign="top"> <div class="tabber" style="width:95%; left:20px"> 
          <div class="tabbertab" style="top-margin:0px"> 
            <h2>Personal Info</h2>
            <p>
              <? include_once('paymast.info.php');?>
            </p>
          </div>
          <div class="tabbertab"> 
            <h2>Employement</h2>
            <p>
              <? include_once('paymast.employment.php');?>
            </p>
          </div>
          <div class="tabbertab"> 
            <h2>Payroll</h2>
            <p>
              <? include_once('paymast.payroll.php');?>
            </p>
          </div>
          <div class="tabbertab"> 
            <h2>Ledger</h2>
            <p>
              <? include_once('paymast.ledger.php');?>
            </p>
          </div>
          <div class="tabbertab"> 
            <h2>Memo</h2>
            <p>
              <? include_once('paymast.memo.php');?>
            </p>
          </div>
        </div>
      </td>
    </tr>
	<tr>
	  <td bgcolor="#EFEFEF"><table width="1%" border="0" cellpadding="1" cellspacing="1" bgcolor="#CCCCCC">
          <tr bgcolor="#FFFFFF"> 
            <td nowrap width="3%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              <a  accesskey="S" href="javascript: f1.action='?p=paymast&p1=Save';f1.submit();"> 
              <input type="image" src="../graphics/save.jpg" alt="Save This Claim Form" name="Save" width="57" height="15" border="0" id="Save" onClick="f1.action='?p=paymast&p1=Save';f1.submit();" tabIndex="99">
              </a> </strong></font></td>
            <td nowrap width="46%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              <img src="../graphics/print.jpg" alt="Print This  Form"  onClick="window.print()" accesskey="P"> 
              </strong></font></td>
            <!--<td nowrap width="26%"> <input name="Cancel" id="Cancel" type="image" src="../graphics/cancel.jpg" onClick="vSubmit(this)" alt="Cancel This Entry" width="77" height="20"></td>-->
            <td nowrap width="25%"> <input type='image' name="New" id="New" onClick="f1.action='?p=paymast&p1=New';f1.submit();"  src="../graphics/new.jpg" alt="New Claim Form" width="63" height="20" accesskey="N"> 
            </td>
          </tr>
        </table></td>
	</tr>
  </table>
      </form>
<div align="center"><a href='?p=paymast&p1=Previous'> <img src="../graphics/redarrow_left.gif" width="4" height="7" border="0"> 
  Previous</a>&nbsp;| &nbsp;<a href='?p=paymast&p1=Next'>Next</a> <a href="?p=paymast&p1=Next"><img src="../graphics/redarrow_right.gif" width="4" height="7" border="0"></a></div>
<script>document.getElementById('search').focus();</script>
