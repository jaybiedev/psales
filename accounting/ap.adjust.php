<script>
</script><STYLE TYPE="text/css">
<!--
	.altSelectFormat {
	background-color: #FFFFFF;
	font-family: verdana;
	border: #CCCCCC 1px solid;
	padding:0px 0px 0px 0px; 
	font-size: 10px;
	color: #000000
	} 
	 .altTextFormat {
	background-color: #FFFFFF;
	font-family: verdana;
	border: #CCCCCC 1px solid;
	font-size: 11px;
	color: #000000
	} 
	.numTextFormat {
	background-color: #FFFFFF;
	font-family: verdana;
	border: #CCCCCC 1px solid;
	font-size: 11px;
	text-align:right;
	color: #000000
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
	
	.altTextField {
	background-color: #ececec;
	font-family: verdana;
	font-size: 12pt;
	color: #09c09c
	} 
	
	.radioStyle {
	background-color: #FF0000;
	border: #000000 solid 1px;
	font-family: verdana;
	font-size: 12px;
	color: #000000
	}
	-->
</style>
<?
$module = 'ap.adjust';
if (!session_is_registered('aAdjust'))
{
	session_register('aAdjust');
	$aAdjust=null;
	$aAdjust=array();
}
if ($p1 == 'New')
{
	$area_id=$aAdjust['area_id'];
	$date=$aAdjust['date'];
	$aAdjust = null;
	$aAdjust = array();
	$aAdjust['area_id']=$area_id;
	$aAdjust['date']=$date;
}		
if ($p1 == 'Ok' && $_REQUEST['account_id'] == '')
  {
  	$fields = array('reference','date','account_id','remarks','debit','credit','area_id','ptype','first','second','third','fourth');
	for ($c=0;$c<count($fields);$c++)
	{
		if ($fields[$c] == 'date')
		{
			$aAdjust['date'] = mdy2ymd($_REQUEST['date']);
		}
		else
		{
			$aAdjust[$fields[$c]] = $_REQUEST[$fields[$c]];
		}
	}
  	message("Lacking Data. Cannot save record...");
  }
  elseif ($p1 == 'Ok')
  {
  	$fields = array('reference','date','account_id','remarks','debit','credit','area_id','first','second','third','fourth','ptype');
	for ($c=0;$c<count($fields);$c++)
	{
		if ($fields[$c] == 'date')
		{
			$aAdjust['date'] = mdy2ymd($_REQUEST['date']);
		}
		else
		{
			$aAdjust[$fields[$c]] = $_REQUEST[$fields[$c]];
		}
	}

  	if ($aAdjust['apledger_id'] == '')
	{
		$audit = "Encoded by: ".$admin->username." on ".date('m/d/Y g:ia').';';
		
		$q = "insert into apledger (reference, date, account_id, remarks, debit, credit, enable, admin_id)
					values ('".$aAdjust['reference']."','".$aAdjust['date']."','".$aAdjust['account_id']."',
							'".$aAdjust['remarks']."','".$aAdjust['debit']."','".$aAdjust['credit']."', 'Y', 
							'".$ADMIN['admin_id']."')";
		$qr = @pg_query($q) or message(pg_errormessage());
		if ($qr)
		{
			$id = @pg_insert_id();
			$audit = 'Encoded by:'.$ADMIN['name'].' on '.date('m/d/Y g:ia').'; ';
			audit($module, $q, $ADMIN['admin_id'], $audit, $id);
			message("Record Successfully Saved");
			$aAdjust=null;
			$aAdjust=array();
		}
	}
	else
	{
		$audit = $aAdjust['audit']."Updated by: ".$admin->username." on ".date('m/d/Y g:ia').';';

		$q = "update apledger set 
					reference='".$aAdjust['reference']."',
					date='".$aAdjust['date']."',
					account_id='".$aAdjust['account_id']."',
					remarks='".$aAdjust['remarks']."',
					debit='".$aAdjust['debit']."',
					credit='".$aAdjust['credit']."',
	 			    where
					apledger_id='".$aAdjust['apledger_id']."'";
		$qr = @pg_query($q) or message(pg_errormessage());

		if ($qr)
		{
			audit($module, $q, $ADMIN['admin_id'], $audit,$aAdjust['apledger_id']);
			message("Record Update Successful");
			$aAdjust=null;
			$aAdjust=array();
		}					
	}
	
  }
  elseif ($p1 == 'Cancel' && $id != '')
  {
  	$q = "update apledger set enable='N' where apledger_id='$id'";
	$qr = @pg_query($q) or message(pg_errormessage());
  }
  elseif ($p1 == 'Restore' && $id != '')
  {
  	$q = "update apledger set enable='Y' where apledger_id='$id'";
	$qr = @pg_query($q) or message(pg_errormessage());
  }
  elseif ($p1 == 'Edit' && $id != '')
  {
  	$aAdjust=null;
	$aAdjust = array();
  	$q = "select * from apledger where apledger_id='$id'";
	$qr = @pg_query($q) or message(pg_errormessage());
	$r = @pg_fetch_assoc($qr);
	$aAdjust=$r;
  } 
  
  if ($aAdjust['date'] == '') $aAdjust['date']=date('Y-m-d');
?>

<form action="?p=ap.adjust" method="post" name="f1" id="f1">
  <table width="95%" border="0" align="center" cellpadding="1" cellspacing="1">
    <tr>
      <td height="50" colspan="8">Search 
        <input name="xSearch" type="text" id="xSearch" value="<?= $xSearch;?>"> 
        <?= lookUpAssoc('searchby',array('Reference#'=>'reference','Rec Id.'=>'apledger_id','account'=>'Account','Remarks'=>'remarks'), $searchby);?>
        <input name="p1" type="submit" id="p1" value="Go"> <input type="button" name="Submit2" value="Add New" onClick="window.location='?p=ar.adjust&p1=New'"> 
        <input type="button" name="Submit23222" value="Close" onClick="window.location='?p='"> 
        <hr color="#CC0000"></td>
    </tr>
    <tr> 
      <td colspan="8"> <div align="left"></div></td>
    </tr>
    <tr> 
      <td width="6%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Rec 
        No<br>
        <input type="text" name="apledger_id" size="8" value="<?= $aAdjust['apledger_id'];?>" readOnly class="numTextFormat">
        </font></td>
      <td width="11%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Date<br>
        <input name="date" type="text" id="date" value="<?= ymd2mdy($aAdjust['date']);?>" size="9" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" class="altTextFormat">
        <img src="../graphics/dwn-arrow-grn.gif" onClick="popUpCalendar(this, f1.date, 'mm/dd/yyyy')"> 
        </font></td>
      <td width="8%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Reference<br>
        <input type="text" name="reference" size="10" value="<?= $aAdjust['reference'];?>" onkeypress="if(event.keyCode==13) {document.getElementById('remarks').focus();return false;}">
      </font></td>
      <td width="17%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Remark<br>
        <input type="text" name="remarks" size="25" value="<?= $aAdjust['remarks'];?>" class="altTextFormat" onkeypress="if(event.keyCode==13) {document.getElementById('account_id').focus();return false;}" />
      </font></td>
      <td width="22%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Supplier<br>
        <select name="account_id" id="account_id"   tabindex="<?= array_search('account_id',$fields);?>" style="border: #CCCCCC 1px solid; width:200px">
          <option value=''>Select Supplier Accounts--</option>
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
      </font></td>
      <td width="13%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Account Entry
        <select name="gchart_id" id="gchart_id" style="width:150px"   onkeypress="if(event.keyCode==13) {document.getElementById('debit').focus();return false;}">
          <option value="">----</option>
          <?
              $q = "select * from gchart where enable='Y' order by gchart";
              $qr = @pg_query($q);
              while ($r = @pg_fetch_object($qr))
              {
              		if ($aAdjust['gchart_id'] == $r->gchart_id)
              		{
              			echo "<option value = '$r->gchart_id' selected>$r->gchart</option>";
              		}
              		else
              		{
              			echo "<option value = '$r->gchart_id'>$r->gchart</option>";
              		}	
        	}
              ?>
        </select>
      </font></td>
      <td width="7%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Debit<br>
        <input type="text" name="debit" size="9" value="<?= $aAdjust['debit'];?>" class="numTextFormat" onkeypress="if(event.keyCode==13) {document.getElementById('credit').focus();return false;}">
      </font></td>
      <td width="16%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        Credit<br>
        <input type="text" name="credit" size="9" value="<?= $aAdjust['credit'];?>" class="numTextFormat" onkeypress="if(event.keyCode==13) {document.getElementById('first').focus();return false;}">
        </font><font size="3" face="Verdana, Arial, Helvetica, sans-serif">
        <input type="submit" name="p1" value="Ok" class="altButtonFormat" />
        </font></td>
    </tr></table>
  <table width="95%" border="0" align="center" cellpadding="1" cellspacing="1">
  <tr>
  <td width="20%"></td>
  <td width="20%">&nbsp;</td>
  <td width="10%">&nbsp;</td>
  <td width="10%">&nbsp;</td>
  <td width="10%">&nbsp;</td>
  <td width="10%">&nbsp;</td>
  <td width="40%">&nbsp;</td>
  </tr>
  </table>
</form>

<table width="95%" border="0" align="center" cellpadding="2" cellspacing="1" bgcolor="#EFEFEF">
  <tr bgcolor="#CCCCCC"> 
    <td height="20" colspan="10"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
      <img src="../graphics/storage.gif" width="16" height="17"> AP Adjustment Transaction Entries </strong></font></td>
  </tr>
  <tr> 
    <td align="center" width="3%"><font size="2" face="Geneva, Arial, Helvetica, san-serif">#</font></td>
    <td nowrap width="7%"><font size="2" face="Geneva, Arial, Helvetica, san-serif"> 
      <a href="?p=ap.adjust&p1=Go&sortby=apledger_id&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>">Rec 
      No. </a></font></td>
    <td nowrap width="9%"><font size="2" face="Geneva, Arial, Helvetica, san-serif"> 
      <a href="?p=ap.adjust&p1=Go&sortby=date&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"> 
      Date</a></font></td>
    <td nowrap width="9%"><font size="2" face="Geneva, Arial, Helvetica, san-serif"> 
      <a href="?p=ap.adjust&p1=Go&sortby=reference&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"> 
      Reference</a></font></td>
    <td nowrap width="32%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=ap.adjust&p1=Go&sortby=account&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>">Account</a></font></td>
    <td width="8%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <a href="?p=ap.adjust&p1=Go&sortby=account&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"> 
      </a>Debit</font></td>
    <td width="7%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Credit</font></td>
    <td width="6%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=ap.adjust&p1=Go&sortby=enable&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>">Status</a></font></td>
    <td width="14%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=ap.adjust&p1=Go&sortby=admin_id&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>">Encoder</a></font></td>
    <td width="5%" nowrap>&nbsp;</td>
  </tr>
  <?

	$q = "select 
			apledger.apledger_id,
			apledger.reference,
			apledger.date,
			apledger.remarks,
			apledger.enable,
			apledger.admin_id,
			apledger.credit,
			apledger.debit,
			account.account
		from 
			apledger,
			account
		where
			account.account_id=apledger.account_id ";

if ($xSearch != '')
{
	$q .= " and $searchby like '$xSearch%' ";
}
if ($sortby == '')
{
	$sortby = 'date desc';
}
$q .= " order by $sortby ";

if ($p1 == 'Go' or $p1 == '' or $start=='')
{
	$start = 0;
}
elseif ($p1 == 'Next')
{
	$start += 15;
}
elseif ($p1 == 'Previous')
{
	$start -= 15;
}
if ($start<0) $start=0;
	
$q .= "offset $start limit 15 ";

$qr = @pg_query($q) or message("Error querying AP Ledger data....".pg_errormessage().$q);

/*if (pg_num_rows($qr) == 0 )
{
	if ($p1 == 'Go') 
	{
		message("Search on Payment data [NOT] found...");
	}
	else
	{
		message("End of File...");
	}	
}*/
$ctr=0;
while ($r = pg_fetch_object($qr))
{
	$ctr++;
	if ($r->reference == '') $refer='DM'.str_pad($r->apledger_id,8,'0',str_pad_left);
	else $refer = $r->reference;
	if ($r->enable== 'N')
	{
		$bgcolor = '#FFCCCC';
	}
	else
	{
		$bgcolor = '#FFFFFF';
	}
  ?>
  <tr onMouseOver="bgColor='#FFFFDD'" onMouseOut="bgColor='<?=$bgcolor;?>'" bgcolor="<?=$bgcolor;?>" onClick="window.location='?p=ap.adjust&p1=Load&id=<?= $r->apledger_id;?>'"> 
    <td width="3%" align="right" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= $ctr;?>
      .</font></td>
    <td width="7%" nowrap"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <a href="?p=ap.adjust&p1=Edit&id=<?= $r->apledger_id;?>"> 
      <?= str_pad($r->apledger_id,8,'0',str_pad_left);?>
      </a> </font></td>
    <td width="9%" nowrap"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= ymd2mdy($r->date);?>
      </font></td>
    <td width="9%" nowrap"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <a href="?p=ar.adjust&p1=Edit&id=<?= $r->apledger_id;?>"> 
      <?= $refer;?>
      </a></font></td>
    <td nowrap width="32%""><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <a href="?p=ap.adjust&p1=Edit&id=<?= $r->apledger_id;?>"> 
      <?= $r->account.' '.($r->status=='C' ? '- CANCELLED' : '');?></a>
      </font></td>
    <td width="8%" align="right" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><?= number_format($r->debit,2);?> 
      </font></td>
    <td width="7%" align="right" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
      <?= number_format($r->credit,2);?>
      </font></td>
    <td nowrap width="6%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= ($r->enable=='Y' ? 'Saved' : 'Cancelled');?>
      </font></td>
    <td nowrap width="14%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= lookUpTableReturnValue('x','admin','admin_id','name',$r->admin_id);?>
      </font></td>
    <td nowrap width="5%"><font size="1">
	<?
	if ($r->enable =='N')
	{
	?>
		<a href="javascript: if (confirm('Are you sure to Restore Entry?')){form1.action='?p=ar.adjust&p1=Restore&id=<?=$r->apledger_id;?>';form1.submit();}">Restore</a></font></td>
	<?	
	}
	else
	{
	?>
		<a href="javascript: if (confirm('Are you sure to Cancel Entry?')){form1.action='?p=ap.adjust&p1=Cancel&id=<?=$r->apledger_id;?>';form1.submit();}">Delete</a>
		</td>
	<?
	}
	?>	
  </tr>
  <?
  }
  ?>
  <tr> 
    <td colspan="10" bgcolor="#FFFFFF"> <input type="button" name="Submit22" value="Add New" onClick="window.location='?p=ap.adjust&p1=New'"> 
      <input type="button" name="Submit232" value="Close" onClick="window.location='?p='"> 
    </td>
  </tr>
</table>

<div align="center"> <a href="?p=ap.adjust&p1=Previous&sortby=<?=$sortby;?>&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"><img src="graphics/redarrow_left.gif" width="4" height="7" border="0"></a> 
  <a href="?p=ap.adjust&p1=Previous&sortby=<?=$sortby;?>&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"> 
  Previous</a> | <a href="?p=ap.adjust&p1=Next&sortby=<?=$sortby;?>&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>">Next</a> 
  <a href="?p=ar.apjust&p1=Next&sortby=<?=$sortby;?>&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"><img src="graphics/redarrow_right.gif" width="4" height="7" border="0"></a> 
</div>
