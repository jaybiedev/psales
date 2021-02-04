<STYLE TYPE="text/css">
<!--

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
	SELECT {
	background-color: #FFFFFF;
	font-family: verdana;
	border: #CCCCCC 1px solid;
	font-size: 12px;
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
if (!isset($_SESSION['aAE'])) 
{ 
	$_SESSION['aAE'] = array(); 
	$aAE = null;
	$aAE = array();
} 


if ($p1 == 'Save')
{
	$fields = array('gcdebit_disbursement_id','gccredit_disbursement_id','gcdebit_payable_id','gccredit_payable_id','gcdebit_por_id','gccredit_por_id','gcdebit_collection_id','gccredit_collection_id');

	for ($c=0;$c<count($fields);$c++)
	{
		$aAE[$fields[$c]] = $_REQUEST[$fields[$c]];

		$q = "select * from accountingentry where accountingentry='".$fields[$c]."'";
		$qr = @pg_query($q) or message('Error querying...');
		if (pg_num_rows($qr) != 0)
		{
			$r = pg_fetch_object($qr);
			$q = "update accountingentry set 
						accountingentry='".$fields[$c]."', 
						value='".$aAE[$fields[$c]]."'
					where
						accountingentry_id='$r->accountingentry_id'";
			$qr = @pg_query($q) or message("Error updating system configuration...".pg_errormessage());

		}
		else
		{
			$q = "insert into accountingentry (accountingentry,value)
			       values ('".$fields[$c]."','".$aAE[$fields[$c]]."')";
			$qr = @pg_query($q) or message("Error updating system configuration...".pg_error());
		}
	}
	if ($qr)
	{
		message(" Accounting Entries Setup updated...");
	}
	
}
else
{
	$q = "select * from accountingentry";
	$qr  =@ pg_query($q) or message(pg_errormessage());
	while ($r= @pg_fetch_assoc($qr))
	{
		$aAE[$r['accountingentry']] = $r['value'];
	}
}
?>
<form name="f1" method="post" action="">
  <table width="90%" border="0" align="center" cellpadding="1" cellspacing="1">
    <tr> 
      <td colspan="3" background="../graphics/table_horizontal.PNG"> <font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>&nbsp; 
        <img src="../graphics/arrowgrn.gif" width="16" height="16"> Accounting 
        Entries For Auto Posting</strong></font></td>
    </tr>
    <tr bgcolor="#DADADA"> 
      <td width="25%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Transaction</font></td>
      <td width="29%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Debit 
        Account</font></td>
      <td width="46%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Credit 
        Account</font></td>
    </tr>
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Disbursement</font></td>
      <td> <select name="gcdebit_disbursement_id" id="gcdebit_disbursement_id">
          <option value="0">Select Account</option>
          <?
			$q= "select * from gchart where enable='Y' order by acode, scode";
			$qr = @pg_query($q);
			while ($r = @pg_fetch_object($qr))
			{
				if($r->gchart_id == $aAE['gcdebit_disbursement_id'])
				{
					echo "<option value=\"$r->gchart_id\" selected>$r->acode - $r->scode ".' '.addslashes($r->gchart)."</option>";
				}
				else
				{
					echo "<option value=\"$r->gchart_id\">$r->acode - $r->scode ".' '.addslashes($r->gchart)."</option>";
				}
			}
		?>
        </select> </td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <select name="gccredit_disbursement_id" id="gccredit_disbursement_id">
          <option value="0">Select Account</option>
          <?
			$q= "select * from gchart where enable='Y' order by acode, scode";
			$qr = @pg_query($q);
			while ($r = @pg_fetch_object($qr))
			{
				if($r->gchart_id == $aAE['gccredit_disbursement_id'])
				{
					echo "<option value=\"$r->gchart_id\" selected>$r->acode - $r->scode ".' '.addslashes($r->gchart)."</option>";
				}
				else
				{
					echo "<option value=\"$r->gchart_id\">$r->acode - $r->scode ".' '.addslashes($r->gchart)."</option>";
				}
			}
		?>
        </select>
        </font></td>
    </tr>
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Stocks Receiving 
        Payable</font></td>
      <td><select name="gcdebit_payable_id" id="gcdebit_payable_id">
          <option value="0">Select Account</option>
          <?
			$q= "select * from gchart where enable='Y' order by acode, scode";
			$qr = @pg_query($q);
			while ($r = @pg_fetch_object($qr))
			{
				if($r->gchart_id == $aAE['gcdebit_payable_id'])
				{
					echo "<option value=\"$r->gchart_id\" selected>$r->acode - $r->scode ".' '.addslashes($r->gchart)."</option>";
				}
				else
				{
					echo "<option value=\"$r->gchart_id\">$r->acode - $r->scode ".' '.addslashes($r->gchart)."</option>";
				}
			}
		?>
        </select></td>
      <td><select name="gccredit_payable_id" id="gccredit_payable_id">
          <option value="0">Select Account</option>
          <?
			$q= "select * from gchart where enable='Y' order by acode, scode";
			$qr = @pg_query($q);
			while ($r = @pg_fetch_object($qr))
			{
				if($r->gchart_id == $aAE['gccredit_payable_id'])
				{
					echo "<option value=\"$r->gchart_id\" selected>$r->acode - $r->scode ".' '.addslashes($r->gchart)."</option>";
				}
				else
				{
					echo "<option value=\"$r->gchart_id\">$r->acode - $r->scode ".' '.addslashes($r->gchart)."</option>";
				}
			}
		?>
        </select></td>
    </tr>
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Purchase 
        Return </font></td>
      <td><select name="gcdebit_por_id" id="gcdebit_por_id">
          <option value="0">Select Account</option>
          <?
			$q= "select * from gchart where enable='Y' order by acode, scode";
			$qr = @pg_query($q);
			while ($r = @pg_fetch_object($qr))
			{
				if($r->gchart_id == $aAE['gcdebit_por_id'])
				{
					echo "<option value=\"$r->gchart_id\" selected>$r->acode - $r->scode ".' '.addslashes($r->gchart)."</option>";
				}
				else
				{
					echo "<option value=\"$r->gchart_id\">$r->acode - $r->scode ".' '.addslashes($r->gchart)."</option>";
				}
			}
		?>
        </select></td>
      <td><select name="gccredit_por_id" id="gccredit_por_id">
          <option value="0">Select Account</option>
          <?
			$q= "select * from gchart where enable='Y' order by acode, scode";
			$qr = @pg_query($q);
			while ($r = @pg_fetch_object($qr))
			{
				if($r->gchart_id == $aAE['gccredit_por_id'])
				{
					echo "<option value=\"$r->gchart_id\" selected>$r->acode - $r->scode ".' '.addslashes($r->gchart)."</option>";
				}
				else
				{
					echo "<option value=\"$r->gchart_id\">$r->acode - $r->scode ".' '.addslashes($r->gchart)."</option>";
				}
			}
		?>
        </select></td>
    </tr>
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Collection 
        </font></td>
      <td> <select name="gcdebit_collection_id" id="gcdebit_collection_id">
          <option value="0">Select Account</option>
          <?
			$q= "select * from gchart where enable='Y' order by acode, scode";
			$qr = @pg_query($q);
			while ($r = @pg_fetch_object($qr))
			{
				if($r->gchart_id == $aAE['gcdebit_collection_id'])
				{
					echo "<option value=\"$r->gchart_id\" selected>$r->acode - $r->scode ".' '.addslashes($r->gchart)."</option>";
				}
				else
				{
					echo "<option value=\"$r->gchart_id\">$r->acode - $r->scode ".' '.addslashes($r->gchart)."</option>";
				}
			}
		?>
        </select> </td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <select name="gccredit_collection_id" id="gccredit_collection_id">
          <option value="0">Select Account</option>
          <?
			$q= "select * from gchart where enable='Y' order by acode, scode";
			$qr = @pg_query($q);
			while ($r = @pg_fetch_object($qr))
			{
				if($r->gchart_id == $aAE['gccredit_collection_id'])
				{
					echo "<option value=\"$r->gchart_id\" selected>$r->acode - $r->scode ".' '.addslashes($r->gchart)."</option>";
				}
				else
				{
					echo "<option value=\"$r->gchart_id\">$r->acode - $r->scode ".' '.addslashes($r->gchart)."</option>";
				}
			}
		?>
        </select>
        </font></td>
    </tr>
    <tr bgcolor="#DADADA"> 
      <td colspan="3"><input name="p1" type="submit" id="p1" value="Save"></td>
    </tr>
  </table>
</form>
