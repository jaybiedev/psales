<?
if (!session_is_registered('aAClass'))
{
	session_register('aAClass');
	$aAClass = null;
	$aAClass = array();
}

$fields = array('account_class','account_class_code',
				'grocery_term','grocery_interval','grocery_surcharge','grocery_interest','grocery_grace','grocery_service',
				'grocery_cutoff1', 'grocery_cutoff2', 'grocery_cutoff3', 'grocery_cutoff4', 'grocery_discount',
				'drygood_term','drygood_interval','drygood_surcharge','drygood_interest','drygood_grace','drygood_service',
				'drygood_cutoff1', 'drygood_cutoff2', 'drygood_cutoff3', 'drygood_cutoff4','drygood_discount',
				'grocery_sc_net','drygood_sc_net',
				'remarks','enable');
				
if (!in_array($p1, array(null,'load','edit')))
{
	for($c=0;$c<count($fields);$c++)
	{
		$aAClass[$fields[$c]] = $_REQUEST[$fields[$c]];
		if (!in_array($fields[$c], array('account_class','account_class_code')) && $aAClass[$fields[$c]]=='')
		{
			$aAClass[$fields[$c]] = 0;
		}
	}
}

if ($p1 == 'New' || $p1 == 'Add New')
{
	$aAClass  = null;
	$aAClass = array();
}
elseif ($p1 == 'Load')
{
	$q = "select * from account_class where account_class_id='$id'";
	$qr = @pg_query($q) or mesasge(pg_errormessage());
	$r = @pg_fetch_assoc($qr);
	$aAClass  = null;
	$aAClass = array();
	$aAClass = $r;
}
elseif ($p1 == 'Save' && $aAClass['account_class'] == '')
{
	message('CANNOT Save. No Account Classification Description....');
}
elseif ($p1 == 'Save')
{
	if ($aAClass['account_class_id'] == '')
	{
		$q = "insert into account_class (";
		for($c=0;$c<count($fields);$c++)
		{
			if ($c > 0) $q .= ",";
			$q .= $fields[$c];
		}
		$q .= ") values (";
		for($c=0;$c<count($fields);$c++)
		{
			if ($c > 0) $q .= ",";
			$q .= "'".$aAClass[$fields[$c]]."'";
		}
		$q .= ")";
		$qr = @pg_query($q) or message(pg_errormessage());
		if ($qr)
		{
			$id = pg_insert_id('account_class');
			$aAClass['account_class_id'] = $id;
			message('Account Classification Data Saved...');
		}
		
	}
	else
	{
		$q = "update account_class set ";
		for($c=0;$c<count($fields);$c++)
		{
			if ($c > 0) $q .= ",";
			$q .= $fields[$c]."='".$aAClass[$fields[$c]]."'";
		}
		$q .= " where account_class_id='".$aAClass['account_class_id']."'";
		$qr = @pg_query($q) or message(pg_errormessage());
		if ($qr)
		{
			message('Account Classification Data Updated...');
		}
	}	
}			
?>
<form name="f1" method="post" action="" style="margin:0">
  <table width="85%" border="0" align="center" cellpadding="2" cellspacing="1">
    <tr> 
      <td>Search 
        <input name="search" type="text" id="search" value="<?= $search;?>"> 
        <?=lookUpAssoc('searchby',array('Account Classification'=>'account_class','account_class_code'=>'account_class_code'),$searchby);?>
        <input name="p1" type="button" id="p1" value="Go" accesskey="G" onClick="window.location='?p=account_class.browse&p1=Go&p1=God&xSearch='+xSearch.value"> <input name="p1" type="submit" id="p1" accesskey="N" onClick="window.location='?p=account_class&p1=New'" value="Add New">
        <input type="button" name="Submit22" value="Browse" onClick="window.location='?p=account_class.browse&p1=Browse'" accesskey="N"> 
        <hr color="#CC0000"></td>
    </tr>
  </table>
  <table width="80%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr background="../graphics/table0_horizontal.PNG"> 
      <td height="29" colspan="4"><font color="#FFFFFF" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Account 
        Classification</strong></font></td>
    </tr>
    <tr> 
      <td width="13%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Classification</font></td>
      <td><input name="account_class" type="text" id="account_class" value="<?= $aAClass['account_class'];?>" size="50"></td>
      <td>&nbsp;</td>
      <td><font size="2">Id: 
        <?= $aAClass['account_class_id'];?>
        </font> </td>
    </tr>
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Code</font></td>
      <td colspan="3"><input name="account_class_code" type="text" id="account_class_code" value="<?= $aAClass['account_class_code'];?>"></td>
    </tr>
    <tr bgcolor="#CCCCCC"> 
      <td height="22" colspan="4"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
        &nbsp;Grocery</strong></font></td>
    </tr>
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Term</font></td>
      <td width="37%"><input name="grocery_term" type="text" id="grocery_term" value="<?= $aAClass['grocery_term'];?>"></td>
      <td width="9%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Cut-Off1</font></td>
      <td width="41%"><input name="grocery_cutoff1" type="text" id="grocery_cutoff1" value="<?= $aAClass['grocery_cutoff1'];?>" size="5"></td>
    </tr>
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Due Interval</font></td>
      <td><input name="grocery_interval" type="text" id="grocery_interval" value="<?= $aAClass['grocery_interval'];?>"></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Cut-Off2</font></td>
      <td><input name="grocery_cutoff2" type="text" id="grocery_cutoff2" value="<?= $aAClass['grocery_cutoff2'];?>" size="5"></td>
    </tr>
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Surcharge</font></td>
      <td><input name="grocery_surcharge" type="text" id="grocery_surcharge" value="<?= $aAClass['grocery_surcharge'];?>"></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Cut-Off3</font></td>
      <td><input name="grocery_cutoff3" type="text" id="grocery_cutoff3" value="<?= $aAClass['grocery_cutoff3'];?>" size="5"></td>
    </tr>
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Interest</font></td>
      <td><input name="grocery_interest" type="text" id="grocery_interest" value="<?= $aAClass['grocery_interest'];?>"></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Cut-Off4</font></td>
      <td><input name="grocery_cutoff4" type="text" id="grocery_cutoff4" value="<?= $aAClass['grocery_cutoff4'];?>" size="5"></td>
    </tr>
    <tr> 
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Service 
        Charge </font></td>
      <td colspan="3"><input name="grocery_service" type="text" id="grocery_service" value="<?= $aAClass['grocery_service'];?>"></td>
    </tr>
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Grace Period</font></td>
      <td><input name="grocery_grace" type="text" id="grocery_grace" value="<?= $aAClass['grocery_grace'];?>"></td>
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Min.Discount</font></td>
      <td><input name="grocery_discount" type="text" id="grocery_discount" value="<?= $aAClass['grocery_discount'];?>" size="5"></td>
    </tr>
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">SC on NetItem</font></td>
      <td><?= lookUpAssoc('grocery_sc_net',array('Yes'=>'Y','No'=>'N'), $aAClass['grocery_sc_net']);?></td>
      <td nowrap>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr bgcolor="#CCCCCC"> 
      <td height="24" colspan="5"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
        &nbsp; Dry Goods</strong></font></td>
    </tr>
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Term</font></td>
      <td><input name="drygood_term" type="text" id="drygood_term" value="<?= $aAClass['drygood_term'];?>"></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Cut-Off1</font></td>
      <td><input name="drygood_cutoff1" type="text" id="drygood_cutoff1" value="<?= $aAClass['drygood_cutoff1'];?>" size="5"></td>
    </tr>
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Due Interval</font></td>
      <td><input name="drygood_interval" type="text" id="drygood_interval" value="<?= $aAClass['drygood_interval'];?>"></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Cut-Off2</font></td>
      <td><input name="drygood_cutoff2" type="text" id="drygood_cutoff2" value="<?= $aAClass['drygood_cutoff2'];?>" size="5"></td>
    </tr>
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Surcharge</font></td>
      <td><input name="drygood_surcharge" type="text" id="drygood_surcharge" value="<?= $aAClass['drygood_surcharge'];?>"></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Cut-Off3</font></td>
      <td><input name="drygood_cutoff3" type="text" id="drygood_cutoff3" value="<?= $aAClass['drygood_cutoff3'];?>" size="5"></td>
    </tr>
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Interest</font></td>
      <td><input name="drygood_interest" type="text" id="drygood_interest" value="<?= $aAClass['drygood_interest'];?>"></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Cut-Off4</font></td>
      <td><input name="drygood_cutoff4" type="text" id="drygood_cutoff4" value="<?= $aAClass['drygood_cutoff4'];?>" size="5"></td>
    </tr>
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Service 
        Charge </font></td>
      <td colspan="3"><input name="drygood_service" type="text" id="drygood_service" value="<?= $aAClass['drygood_service'];?>"></td>
    </tr>
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Grace Period</font></td>
      <td><input name="drygood_grace" type="text" id="drygood_grace" value="<?= $aAClass['drygood_grace'];?>"></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Min.Discount</font></td>
      <td><input name="drygood_discount" type="text" id="drygood_discount" value="<?= $aAClass['drygood_discount'];?>" size="5"></td>
    </tr>
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">SC on NetItem</font></td>
      <td><?= lookUpAssoc('drygood_sc_net',array('Yes'=>'Y','No'=>'N'), $aAClass['drygood_sc_net']);?></td>
      <td nowrap>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td colspan="4" bgcolor="#CCCCCC"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>&nbsp; 
        Remarks</strong></font></td>
    </tr>
    <tr> 
      <td>&nbsp;</td>
      <td colspan="3"><textarea name="remarks" cols="35" id="remarks"><?= $aAClass['remarks'];?></textarea></td>
    </tr>
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Enable</font></td>
      <td colspan="3"> 
        <?= lookUpAssoc('enable',array('Yes'=>'Y','No'=>'N'),$aAClass['enable']);?>
      </td>
    </tr>
    <tr> 
      <td colspan="5"><table width="1%" border="0" cellpadding="1" cellspacing="1" bgcolor="#CCCCCC">
          <tr bgcolor="#FFFFFF"> 
            <td nowrap width="3%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              <input type="image" src="../graphics/save.jpg" alt="Save This Claim Form" width="57" height="15" id="Save" onClick="f1.action='?p=account_class&p1=Save';f1.submit();" name="Save" accesskey="S">
              </strong></font></td>
            <td nowrap width="46%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
              <input name="Print" type="image" id="Print" onClick="vSubmit(this)" src="../graphics/print.jpg" alt="Print This Claim Form" width="65" height="18" accesskey="P">
              </strong></font></td>
            <!--<td nowrap width="26%"> <input name="Cancel" id="Cancel" type="image" src="../graphics/cancel.jpg" onClick="vSubmit(this)" alt="Cancel This Entry" width="77" height="20"></td>-->
            <td nowrap width="25%"> <input type='image' name="New" id="New" onClick="f1.action='?p=account_class&p1=New';f1.submit();"  src="../graphics/new.jpg" alt="New Claim Form" width="63" height="20" accesskey="N"> 
            </td>
          </tr>
        </table></td>
    </tr>
  </table>
</form>
