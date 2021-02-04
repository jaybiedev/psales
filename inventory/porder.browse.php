<STYLE TYPE="text/css">
<!--
	.grid {
	font-family: verdana;
	border: #CCCCCC 1px solid;
	font-size: 12px;
	color: #000000
	} 


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
	font-size: 10px;
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
if (!session_is_registered('aPOBrowse'))
{
	session_register('aPOBrowse');
	$aPOBrowse = null;
	$aPOBrowse = array();
	
}
if ($aPO != '' && !in_array($p1, array('Go','Next','Previous','Sort','Browse','Submit')))
{
	echo "<script> window.location='?p=porder' </script>";
	exit;
}
elseif ($p1 == 'Submit' && !chkRights2('pordertag','mdelete',$ADMIN['admin_id']))
{
	message1("<br>[ Access Denied... ]<br>");
}
elseif ($p1 == 'Submit')
{
	$mark = $_REQUEST['mark'];
	if (count($mark) == 0)
	{
		message1("[ No transaction selected.... ] ");
	}
	elseif ($withchecked == '')
	{
		message1("<br>[  Nothing to do....  ] <br>");
	}
	else
	{
		$po_header_id = implode("','",$mark);
		
		$q = "update  po_header set status='$withchecked' where po_header_id in ('$po_header_id')";
		$qr = @pg_query($q) or message1(pg_errormessage().$q);
		if ($qr)
		{
			message1("[ ".@pg_affected_rows($qr)." Transaction(s) Updated ... ]");
		}
	}
}
?>
<form action="" method="post" name="f1" id="f1" >
  <table width="90%" border="0" align="center" cellpadding="2" cellspacing="1">
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Search</font> 
        <input name="xSearch" type="text" id="xSearch" value="<?= $xSearch;?>" class="altText">
        <?= lookUpAssoc('searchby',array('PO No.'=>'reference','Record Id'=>'po_header_id','Supplier'=>'account.account','Supplier Code'=>'account.account_code','Date'=>'date','Stock description'=>'stock_description'), $searchby);?>
        <font size="2" face="Verdana, Arial, Helvetica, sans-serif">
        <select name="account_id" id="account_id"   style="border: #CCCCCC 1px solid; width:240px">
          <option value=''>All Supplier Accounts --</option>
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
        </font>
<input name="p1" type="submit" id="p1" value="Go" class="altBtn"> 
		<input type="button" name="Submit2" value="Add New" onClick="window.location='?p=porder&p1=New'"  class="altBtn">
        <input type="button" name="Submit23222" value="Close" onClick="window.location='?p='"  class="altBtn"> 
        <hr color="#CC0000"></td>
    </tr>
  </table>
<table width="90%" border="0" align="center" cellpadding="1" cellspacing="1" bgcolor="#EFEFEF">
  <tr bgcolor="#CCCCCC"> 
      <td height="20" colspan="8"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
        <img src="../graphics/storage.gif" width="16" height="17"> </strong>Browse 
        Purchase Orders</font></td>
  </tr>
  <tr> 
    <td align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></td>
    <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href="?p=porder.browse&p1=Go&sortby=po_header_id&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>">Record# 
      </a></font></td>
    <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href="?p=porder.browse&p1=Go&sortby=date&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"> 
      Date</a></font></td>
    <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href="?p=porder.browse&p1=Go&sortby=reference desc&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"> 
      Reference#</a></font></td>
    <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href="?p=porder.browse&p1=Go&sortby=account&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"> 
      Supplier Account</a></font></td>
    <td width="10%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <a href="?p=porder.browse&p1=Go&sortby=status&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"> 
      Status</a></font></td>
    <td width="10%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=porder.browse&p1=Go&sortby=category_id&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>">Encoder</a></font></td>
    <td width="15%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;</font></td>
  </tr>
  <?
  
  if (!in_array($p1, array('')))
  {
  	$fields = array('xSearch', 'sortby', 'searchby', 'maction');
	for ($c=0;$c<count($fields);$c++)
	{
		$aPOBrowse[$fields[$c]] = $_REQUEST[$fields[$c]];
	}
  }
$q = "select 
			po_header.po_header_id,
			po_header.date,
			po_header.reference,
			po_header.account_id,
			account.account,
			account.account_code,
			po_header.status,
			po_header.admin_id,
			po_header.transaction_type
		from 
			po_header,
			account
		where
			account.account_id=po_header.account_id ";

if ($account_id > '0')
{
	$q .= " and po_header.account_id = '$account_id'";
}
if ($aPOBrowse['xSearch'] != '')
{
	$q .= " and ".$aPOBrowse['searchby']." like '".$aPOBrowse['xSearch']."%' ";
}
if ($aPOBrowse['sortby'] == '')
{
	$aPOBrowse['sortby'] = 'po_header_id desc ';
}
$q .= " order by ".$aPOBrowse['sortby'];


if ($p1 == 'Go' or $p1 == '' )
{
	$aPOBrowse['start'] = 0;
}
elseif ($p1 == 'Next')
{
	$aPOBrowse['start'] += 15;
}
elseif ($p1 == 'Previous')
{
	$aPOBrowse['start'] -= 15;
}

if ($aPOBrowse['start'] < 0) $aPOBrowse['start'] = 0;
	
$q .= "offset ".$aPOBrowse['start']." limit 15  ";

$qr = @query($q) or message("Error querying Purchase Order data...".db_error().$q);

if (@pg_num_rows($qr) == 0)
{
	if ($p1== 'Go') 
	{
	 	message("Purchase Order data [NOT] found...");
	}	
	else
	{
	 	message("End of File...");
	}
}
	
$ctr=0;
while ($r = @fetch_object($qr))
{
	$ctr++;
	
	
	if ($r->status == 'C')
	{
		$bgcolor = '#FFCCCC';
	}
	elseif ($r->status== 'T')
	{
		$bgcolor = '#BBCCFF';
	}
	elseif ($r->status== 'E')
	{
		$bgcolor = '#AAFFFF';
	}
	elseif ($r->status== 'D')
	{
		$bgcolor = '#DDCCFF';
	}
	else
	{
		$bgcolor = '#FFFFFF';
	}
  ?>
  <tr onMouseOver="bgColor='#FFFFDD'" onMouseOut="bgColor='#FFFFFF'" bgcolor="#FFFFFF" > 
    <td width="3%" align="right" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= $ctr;?>
      .
      <input name="mark[]" type="checkbox" id="mark[]" value="<?= $r->po_header_id;?>">
      </font></td>
    <td width="8%" nowrap" bgColor="<?=$bgcolor;?>"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <a href="?p=porder&p1=Load&id=<?= $r->po_header_id;?>"> 
      <?= str_pad($r->po_header_id,8,'0',STR_PAD_LEFT);?>
      </a> </font></td>
    <td width="10%" nowrap"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <a href="?p=porder&p1=Load&id=<?= $r->po_header_id;?>"> 
      <?= ymd2mdy($r->date);?>
      </a> </font></td>
    <td width="13%" nowrap"><font size="2" face="Verdana, Arial, Helvetica, sans-serif" onClick="window.location='?p=porder&p1=Load&id=<?= $r->po_header_id;?>'"> 
      <?= $r->reference;?>
      </font></td>
    <td width="31%" nowrap"><font size="2" face="Verdana, Arial, Helvetica, sans-serif" onClick="window.location='?p=porder&p1=Load&id=<?= $r->po_header_id;?>'"> 
      <?= $r->account_code.' '.$r->account;?>
      </font></td>
    <td nowrap bgColor="<?=$bgcolor;?>"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= status($r->status);?>
      </font></td>
    <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= lookUpTableReturnValue('x','admin','admin_id','name',$r->admin_id);?>
      </font></td>
    <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <input name="b" type="button" value="Receive" class="btnhov" <?= (!in_array($r->status,array('S','A','T'))?'disabled':'');?> onClick="window.location='?p=receiving&p1=Receive&id=<?= $r->po_header_id;?>'">
      </font></td>
  </tr>
  <?
  }
  ?>
  <tr> 
      <td colspan="8" bgcolor="#FFFFFF"> <font size="1">With Checked</font> 
			<?= lookUpAssoc('withchecked',array('Nothing'=>'','Mark as Save'=>'S','Mark as Partial'=>'T','Mark as Served'=>'E','Close PO'=>'D'),$withchecked);?>
        <input name="p1" type="button" id="p1" value="Submit" <?= (chkRights2('pordertag','madd',$ADMIN['admin_id'])?'':'disabled');?> onClick="if (confirm('Are you sure to perform tagging?')){document.getElementById('f1').action='?p=porder.browse&p1=Submit';document.getElementById('f1').submit();}"> 
        <input type="button" name="Submit22" value="Add New" onClick="window.location='?p=porder&p1=New'"> 
      <input type="button" name="Submit232" value="Close" onClick="window.location='?p='"></td>
  </tr>
</table>

<div align="center"> <a href="?p=porder.browse&p1=Previous&sortby=<?=$sortby;?>&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"><img src="../graphics/redarrow_left.gif" width="4" height="7" border="0"></a> 
  <a href="?p=porder.browse&p1=Previous&sortby=<?=$sortby;?>&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"> 
  Previous</a> | <a href="?p=porder.browse&p1=Next&sortby=<?=$sortby;?>&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>">Next</a> 
  <a href="?p=porder.browse&p1=Next&sortby=<?=$sortby;?>&start=<?=$start;?>&xSearch=<?=$xSearch;?>&searchby=<?=$searchby;?>"><img src="../graphics/redarrow_right.gif" width="4" height="7" border="0"></a> 
</div>
</form>

