<?
if (!session_is_registered("aGLTBrowse")) 
{	
	session_register("aGLTBrowse");
	$aGLTBrowse=array();
}

 if (in_array($p2,array('sortby','Next','Previous','Go','Browse')))
	{
		if ($sortby == $aGLTBrowse['sortby'])
		{
			if ($sortby == '') $sortby = 'gltran_header_id';
			$aGLTBrowse['sortby'] = $sortby.' desc';
		}
		else
		{
			$aGLTBrowse['sortby'] = $sortby;
		}	
		
		if ($aGLTBrowse['sortby'] == '') $aGLTBrowse['sortby']=' gltran_header_id desc ';
		$aGLTBrowse['searchby'] = $_REQUEST['searchby'];
		$aGLTBrowse['xSearch'] = $_REQUEST['xSearch'];
		$aGLTBrowse['status'] = $_REQUEST['status'];
		$aGLTBrowse['show_journal_id'] = $_REQUEST['show_journal_id'];
		if ($p2 == 'Next')
		{
			$aGLTBrowse['start'] += 20;
		}
		elseif ($p2 == 'Previous')
		{
			$aGLTBrowse['start'] -= 20;
			if ($aGLTBrowse['start'] < 0) 
				$aGLTBrowse['start'] = 0;
		}
	}
?>
<form name="form1" method="post" action="">
  <table width="97%" border="0" cellspacing="0" cellpadding="0" align="center">
    <tr> 
      <td><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><b>Find 
        Voucher </b></font> <input type="text" name="xSearch" value="<?= $aGLTBrowse['xSearch'];?>">
        <?= lookUpAssoc('searchby',array('Reference'=>'xrefer','Record Id'=>'gltran_header_id','Payee'=>'account','Date(yyyy-mm-dd)'=>'date','Check No.'=>'mcheck'),$aGLTBrowse['searchby']);?>
        <?= lookUpAssoc('status',array('Show All'=>'','Saved'=>'SAVED','Approved'=>'APPROVED','Printed'=>'PRINTED','Cancelled'=>'CANCELLED'),$aGLTBrowse['status']);?>
        <select name="show_journal_id" id="show_journal_id">
          <option value=''>Show Journal</option>
          <?
	  	$q = "select * from journal where enable='Y' order by journal";
		$qr = @pg_query($q);
		while ($r = @pg_fetch_object($qr))
		{
			if ($aGLTBrowse['show_journal_id'] == $r->journal_id)
			{
				echo "<option value = $r->journal_id selected>$r->journal</option>";
			}
			else
			{	
				echo "<option value = $r->journal_id>$r->journal</option>";
			}	
		}
	  ?>
        </select>
        <input type="submit" name="p2" value="Go" > 
        <input type="submit" name="p2" value="Browse"> 
        <input type="button" name="p2" value="New" onClick="window.location='?p=gltran&p2=New'"> <hr height="1"> </td>
    </tr>
  </table>
<table width="97%" border="0" align="center" cellpadding="1" cellspacing="1" bgcolor="#EFEFEF">
  <tr bgcolor="#CCCCCC"> 
    <td width="3%" height="19"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></td>
    <td width="8%" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="javascript: window.location='?p=gltran.browse&p2=sortby&sortby=gltran_header_id'">Record 
      Id </a></font></td>
    <td width="8%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="javascript: window.location='?p=gltran.browse&p2=sortby&sortby=xrefer'">Reference</a></font></td>
    <td width="30%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <a href="javascript: window.location='?p=gltran.browse&p2=sortby&sortby=account'">Payee</a></font></td>
    <td width="9%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="javascript:window.location='?p=gltran.browse&p2=sortby&sortby=date'">Date</a></font></td>
    <td width="12%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="javascript:window.location='?p=gltran.browse&p2=sortby&sortby=mcheck'">Check</a></font></td>
    <td width="16%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="javascript:window.location='?p=gltran.browse&p2=sortby&sortby=pcenter_id'">Pcenter</a></font></td>
    <td width="14%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="javascript:window.location='?p=gltran.browse&p2=sortby&sortby=status'">Status</a></font></td>
  </tr>
  <?

  	$q = "select gltran_header_id, xrefer, account, date, mcheck, gltran_header.status, pcenter_id
			from 
				gltran_header,
				account
			where
				account.account_id =gltran_header.account_id ";

  if ($aGLTBrowse['show_journal_id'] !='')
  {
  	$q .= " and journal_id='".$aGLTBrowse['show_journal_id']."'";
  }

  if ($aGLTBrowse['xSearch'] != '')
  { 
  	if ($aGLTBrowse['searchby'] == 'gltran_header_id')
  	{
  		$q .= " and gltran_header_id = '$xSearch'";
  	}
  	elseif ($aGLTBrowse['searchby'] == 'xrefer')
  	{
  		$q .= " and xrefer like '$xSearch%'";
  	}
  	else
  	{
		$searchby = $aGLTBrowse['searchby'];
  		$q .= " and $searchby like '%$xSearch%'";
  	}
  }	
  if ($aGLTBrowse['status'] != '')
  {
  	$q .= " and status = '".$aGLTBrowse['status']."'";
  }
  if ($aGLTBrowse['start'] == '') $aGLTBrowse['start']=0;
  if ($aGLTBrowse['sortby'] == '') $aGLTBrowse['sortby'] = 'gltran_header_id desc';
	
	$q .= " order by ".$aGLTBrowse['sortby'];
	$q .= " offset  ".$aGLTBrowse['start']." limit 20";

	$qr = @pg_query($q) or message(pg_errormessage());
	$ctr=0;
	while ($r = @pg_fetch_object($qr))
	{
		$ctr++;
  ?>
  <tr onMouseOver="bgColor='#FFFFCC'" onMouseOut="bgColor='#FFFFFF'" bgColor='#FFFFFF'> 
    <td align="right" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= $ctr;?>
      .</font></td>
    <td align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=gltran&p2=Load&id=<?= $r->gltran_header_id;?>"> 
      <?= str_pad($r->gltran_header_id,8,'0',str_pad_left);?>
      </a></font></td>
    <td align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><a href="?p=gltran&p2=Load&id=<?= $r->gltran_header_id;?>"> 
      <?= $r->xrefer;?>
      </a></font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href="?p=gltran&p2=Load&id=<?= $r->gltran_header_id;?>"> 
      <?= $r->account ;?>
      </a></font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href="?p=gltran&p2=Load&id=<?= $r->gltran_header_id;?>"> 
      <?= ymd2mdy($r->date);?>
      </a></font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= $r->mcheck;?>
      </font></td>
    <td> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= lookUpTableReturnValue('x','pcenter','pcenter_id','pcenter',$r->pcenter_id);?>
      </font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= $r->status;?>
      </font></td>
  </tr>
  <?
  }
  ?>
</table>
</form>
<div align="center"><br>
  <a href="javascript: form1.action='?p=gltran.browse&p2=Previous'; form1.submit()"><img src="../graphics/redarrow_left.gif" width="4" height="7" border="0"></a> 
  <a href="javascript: form1.action='?p=gltran.browse&p2=Previous'; form1.submit()"> 
  Previous</a> | <a href="javascript: form1.action='?p=gltran.browse&p2=Next'; form1.submit()">Next</a> 
  <a href="javascript: form1.action='?p=gltran.browse&p2=Next'; form1.submit()"><img src="../graphics/redarrow_right.gif" width="4" height="7" border="0"></a> 
</div>
