<?
if (!chkRights2('terminal','madd',$ADMIN['admin_id']))
{
	message("Permission Denied...");
	exit;
}

$fields = array("IP","DESCRIPTION","TERMINAL","AREA_ID","CASHIERING", "SERIAL","RECEIPT_PRINTER_DEST","RECEIPT_PRINTER_TYPE","REPORT_PRINTER_DEST",
				"REPORT_PRINTER_TYPE", "DRAWER","DISPLAY","CUTTER","EXTRA","ENABLE");
	
if (!in_array($p1, array('Load','Edit','Print','New')))
{
	for ($c=0;$c<count($fields);$c++)
	{
		$aTERMD[$fields[$c]] = $_REQUEST[$fields[$c]];
	}
}
	
if ($p1 == 'Add New' || $p1 == 'New')
{
	$aTERM = null;
	$aTERM = array();
	$aTERMD = null;
	$aTERMD = array();	
}				
elseif ($p1 == 'Load' && $id == '')
{
	message("Nothing to Load...");
}

elseif ($p1 == 'Load' && $id != '')
{
	$aTERM = null;
	$aTERM = array();

	$aTERMD = null;
	$aTERMD = array();
	
	$q = "select * from terminal where terminal_id='$id'";
	$qr = @pg_query($q) or message1(pg_errormessage());
	$r = @pg_fetch_object($qr);
	$aTERM['ip'] = $r->ip;
	
	$q = "select * from terminal where ip='".$aTERM['ip']."'";
	$qr = @pg_query($q) or message(pg_errormessage());

	while ($r = @pg_fetch_assoc($qr))
	{
		$aTERMD[$r['definition']] = $r['value'];
	}
}
elseif ($p1 == 'Save')
{
	if ($aTERM['ip']=='')
	{
		$aTERM['ip'] = $aTERMD['IP'];
	}
	else
	{
		$q = "update terminal set ip='".$aTERMD['IP']."' where ip='".$aTERM['ip']."'";
		$qr = @pg_query($q) or message(pg_errormessage());
		$aTERM['ip'] = $aTERMD['IP'];
	}
	
	for ($c=0;$c<count($fields);$c++)
	{
		$ckey = $fields[$c];
		$value = $aTERMD[$ckey];
		$q = "select * from terminal where ip='".$aTERM['ip']."' and definition='$ckey'";
		$qr = @pg_query($q) or message(pg_errormessage());
		if (@pg_num_rows($qr) == 0)
		{
			$q = "insert into terminal (ip, definition, value)
				values ('".$aTERM['ip']."', '$ckey', '$value')";
			$qr =@pg_query($q) or message(pg_errormessage());
			
			if ($qr)
				$ok=1;
			else
				$ok=0;
		}
		else
		{
			$r = @pg_fetch_object($qr);
			$q = "update terminal set ip='".$aTERM['ip']."', definition='$ckey', value='$value' where terminal_id='$r->terminal_id'";
			$qr =@pg_query($q) or message(pg_errormessage());
			if ($qr)
				$ok=1;
			else
				$ok=0;
		}
	}
	if ($ok)
	{
		if ($aTERMD['CASHIERING'] == 'Y')
		{
			$q = "select * from invoice where terminal='".$aTERMD['TERMINAL']."'";
			$qr = @pg_query($q) or message(pg_errormessage());
			if (@pg_num_rows($qr)== '0' && $qr)
			{
				$w = "insert into invoice (ip, invoice, terminal)
					values ('".$aTERM['ip']."','0','".$aTERMD['TERMINAL']."')";
				$qr = @pg_query($w) or message(pg_errormessage());
			}
		}
		message("Configuration Updated...");
	}
}
	
?>
<table width="70%" border="0" align="center" cellpadding="0" cellspacing="1">
  <tr> 
    <td colspan="2"  background="../graphics/table0_horizontal.PNG"> 
&nbsp;<img src="../graphics/b_edit.png" width="16" height="16"> 
      <font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Terminal 
      Configuration</strong></font></td>
  </tr>
  <tr> 
    <td width="29%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">IP 
      Addr</font></td>
    <td width="71%"> 
      <input name="IP" type="text" id="IP" value="<?= $aTERMD['IP'];?>">
    </td>
  </tr>
  <tr> 
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Description</font></td>
    <td> 
      <input name="DESCRIPTION" type="text" id="DESCRIPTION" value="<?= $aTERMD['DESCRIPTION'];?>">
    </td>
  </tr>
  <tr> 
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Terminal No.</font></td>
    <td> 
      <input name="TERMINAL" type="text" id="TERMINAL" value="<?= $aTERMD['TERMINAL'];?>">
    </td>
  </tr>
  <tr> 
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Area </font></td>
    <td> 
      <?= lookUpTable2('AREA_ID','area','area_id','area',$aTERMD['AREA_ID']);?>
    </td>
  </tr>
  <tr> 
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Allow Cashiering</font></td>
    <td>
      <?= lookUpAssoc('CASHIERING',array('No'=>'N','Yes'=>'Y'),$aTERMD['CASHIERING']);?>
    </td>
  </tr>
  <tr> 
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Serial No.</font></td>
    <td> 
      <input name="SERIAL" type="text" id="SERIAL" value="<?= $aTERMD['SERIAL'];?>">
    </td>
  </tr>
  <tr> 
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Receipt Print 
      Destination</font></td>
    <td> 
      <input name="RECEIPT_PRINTER_DEST" type="text" id="RECEIPT_PRINTER_DEST" value="<?= $aTERMD['RECEIPT_PRINTER_DEST'];?>">
    </td>
  </tr>
  <tr> 
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Receipt Type</font></td>
    <td> 
      <?=lookUpAssoc('RECEIPT_PRINTER_TYPE',array('NONE'=>'NONE', 'DRAFT (USE IP PRINTER)'=>'DRAFT','GRAPHIC'=>'GRAPHIC','LINUX LP Printer'=>'LINUX LP Printer','PHP Printer(DRAFT)'=>'PHP Printer(DRAFT)','PHP Printer(TEXT)'=>'PHP Printer(TEXT)'),$aTERMD['RECEIPT_PRINTER_TYPE']);?>
    </td>
  </tr>
  <tr> 
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Report Print 
      Destination</font></td>
    <td> 
      <input name="REPORT_PRINTER_DEST" type="text" id="REPORT_PRINTER_DEST" value="<?= $aTERMD['REPORT_PRINTER_DEST'];?>">
    </td>
  </tr>
  <tr> 
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Report Type</font></td>
    <td> 
      <?=lookUpAssoc('REPORT_PRINTER_TYPE',array('NONE'=>'NONE', 'DRAFT (USE IP PRINTER)'=>'DRAFT','Linux LP Printer'=>'LINUX LP Printer','GRAPHIC'=>'GRAPHIC','PHP Printer(DRAFT)'=>'PHP Printer(DRAFT)','PHP Printer(TEXT)'=>'PHP Printer(TEXT)'),$aTERMD['REPORT_PRINTER_TYPE']);?>
    </td>
  </tr>
  <tr> 
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Drawer</font></td>
    <td> 
      <?=lookUpAssoc('DRAWER',array('NONE'=>'NONE', 'COM1'=>'COM1','COM2'=>'COM2','COM3'=>'COM3','LPT'=>'LPT'),$aTERMD['DRAWER']);?>
    </td>
  </tr>
  <tr> 
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Customer Display</font></td>
    <td> 
      <?=lookUpAssoc('DISPLAY',array('NONE'=>'NONE', 'COM1'=>'COM1','COM2'=>'COM2','COM3'=>'COM3','LPT'=>'LPT'),$aTERMD['DISPLAY']);?>
    </td>
  </tr>
  <tr> 
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Printer Auto Cutter</font></td>
    <td> 
      <?= lookUpAssoc('CUTTER',array('Yes'=>'Y','No'=>'N'), $aTERMD['CUTTER']);?>
    </td>
  </tr>
  <tr> 
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Extra</font></td>
    <td> <input name="EXTRA" type="<?= ($ADMIN['usergroup'] == 'A' ? 'text' : 'hidden');?>" id="EXTRA" value="<?= $aTERMD['EXTRA'];?>" size="10"> 
    </td>
  </tr>
  <tr> 
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Enabled</font></td>
    <td> 
      <?= lookUpAssoc('ENABLE',array("Yes"=>"Y","No"=>"N"),$aTERMD['enable']);?>
    </td>
  </tr>
  <tr> 
    <td colspan="2"> 
      <table width="1%" border="0" cellpadding="1" cellspacing="1" bgcolor="#CCCCCC">
        <tr bgcolor="#FFFFFF"> 
          <td nowrap width="3%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
            <input type="image" src="../graphics/save.jpg" alt="Save This Configuration" width="57" height="15" id="Save" onClick="f1.action='?p=../backend/terminal&p1=Save';f1.submit();" accesskey="S">            </strong></font></td>
          <!--<td nowrap width="26%"> <input name="Cancel" id="Cancel" type="image" src="../graphics/cancel.jpg" onClick="vSubmit(this)" alt="Cancel This Entry" width="77" height="20"></td>-->
          <td nowrap width="25%"> <input type='image' name="New" id="New" onClick="f1.action='?p=../backend/terminal&p1=New';f1.submit()"  src="../graphics/new.jpg" alt="New Claim Form" width="63" height="20" accesskey="N">
          <td nowrap width="25%"> <img  src="../graphics/browse.gif"  name="New" id="New" onClick="window.location='?p=../backend/terminal'" alt="Browse Terminals" width="68" height="18" accesskey="B">
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
