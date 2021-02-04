<form name="form1" method="post" action="">
  <table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr> 
      <td height="21" background="graphics/table0_horizontal.PNG"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
        Product Promo Period</strong></font></td>
    </tr>
  </table>
  <table width="90%" border="0" align="center" cellpadding="1" cellspacing="1" bgcolor="#EFEFFEF">
    <tr bgcolor="#EFEFEF"> 
      <td colspan="7"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Filter 
        Supplier</font> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <select name='account_id'>
          <option value=''>Select Supplier -- </option>
          <?
		$q = "select * from account, account_type where account.account_type=account_type.account_type and account_type.class='S' order by account ";
		$qr = @pg_query($q);
		while ($r = @pg_fetch_object($qr))
		{
			if ($account_id == $r->account_id)
			{
				echo "<option value = $r->account_id selected>$r->account</option>";
			}
			else
			{
				echo "<option value = $r->account_id>$r->account</option>";
			}	
		}
		?>
        </select>
        </font> <input name="p1" type="submit" id="p1" value="Go"> <input name="p12" type="button" id="p12" value="Generate New" onClick="window.location='?p=promo.generate&p1=New'"></td>
      <td colspan="2"><input name="showaudit" type="checkbox" id="showaudit" value="1" <?= ($showaudit == '1' ? 'checked' : '')?>>
        <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> Show Audit</font></td>
    </tr>
    <tr bgcolor="#CCCCCC"> 
      <td width="5%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>#</strong></font></td>
      <td width="19%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Supplier</strong></font></td>
      <td width="8%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>From</strong></font></td>
      <td width="7%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>To</strong></font></td>
      <td width="7%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Supplier</strong></font></td>
      <td width="8%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Company</strong></font></td>
      <td width="19%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Remark</strong></font></td>
      <td width="17%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Status</strong></font></td>
      <td width="10%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Audit</strong></font></td>
    </tr>
    <?
		$q = "select account,  promo_header_id, 
					date_from, 
					date_to, 
					promo_header.account_id, 
					promo_header.sdisc, 
					promo_header.cdisc, 
					promo_header.admin_id, 
					promo_header.enable, 
					promo_header.remark
				from 
					promo_header,
					account 
				where 
					account.account_id=promo_header.account_id ";
		if ($account_id != '')
		{
			$q .= " and promo_header.account_id ='$account_id'";
		}
		$q .= " order by date_to desc ";
		$qr = @pg_query($q); 

		if (!$qr)
		{
			message(pg_errormessage(). '  No. ');
		}		
		if (pg_errormessage() == 'relation "promo_header" does not exist ')
		{

			$qh = "CREATE TABLE `promo_header` (
				  `promo_header_id` bigint(20) unsigned NOT NULL auto_increment,
				  `date_from` date default NULL,
				  `date_to` date default NULL,
				  `account_id` int(11) default NULL,
				  `barcode` char(1) collate latin1_general_ci default NULL,
				  `category_id` int(11) default NULL,
				  `classification_id` int(11) default NULL,
				  `promo_price` decimal(11,2) NOT NULL default '0.00',
				  `sdisc` int(5) default NULL,
				  `cdisc` int(5) NOT NULL default '0',
				  `admin_id` int(11) default NULL,
				  `enable` char(1) collate latin1_general_ci default 'Y',
				  `generated` date default NULL,
				  `remark` blob,
				  PRIMARY KEY  (`promo_header_id`),
				  KEY `stock_id_date` (`account_id`,`date_from`,`date_to`)
				) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=0" ;
				
			$qr = @pg_query($qh) or message(pg_errormessagemessage());
			if ($qr) message(" Promo Header has been created ... ");
			$qd = "CREATE TABLE `promo_detail` (
					  `promo_detail_id` int(11) NOT NULL auto_increment,
					  `promo_header_id` int(11) NOT NULL default '0',
					  `stock_id` bigint(20) NOT NULL default '0',
					  `price1` decimal(10,2) default NULL,
					  `promo_price` decimal(10,2) default NULL,
					  PRIMARY KEY  (`promo_detail_id`),
					  KEY `stock_id` (`stock_id`)
					) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=0";
					
			$qr = @pg_query($qd) or message(pg_errormessagemessage());
			if ($qr) message(" Promo Details has been created ... ");
			
			$qr = @pg_query($q); 

		}
		$ctr=0;
		while ($r = @pg_fetch_object($qr))
		{
			$ctr++;
			$date=date('Y-m-d');
			$bgColor= '#FFFFFF';
			if ($r->enable == 'N') 
			{
				$status='CANCELLED';
				$bgColor = '#FFCCCC';
			}	
			elseif ($date<=$r->date_to && $date>=$r->date_from) 
			{
				$status='ON GOING';
				$bgColor = '#66FFFF';
			}	
			elseif ($r->date_from > $date) 
			{
				$status='UP COMING';
			}	
			elseif ($date>$r->date_to)
			{
				$status = 'DONE';
			}
			
			if ($showaudit == '1')
			{
				$q = "select * from audit where module='promo' and row_id='$r->promo_header_id'";
				$qqr = @pg_query($q);
				$audit='';
				while ($rr=@pg_fetch_object($qqr))
				{
					$audit .= $rr->remark;
				}
			}
			else
			{
				$audit = lookUpTableReturnValue('x','admin','admin_id','username',$r->admin_id);
			}	
			?>
    <tr valign="top" bgcolor="<?=$bgColor;?>"> 
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $ctr;?>
        .</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> <a href="?p=promo.generate&p1=Load&id=<?= $r->promo_header_id;?>"> 
        <?= $r->account;?>
        </a></font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= ymd2mdy($r->date_from);?>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= ymd2mdy($r->date_to);?>
        </font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $r->sdisc;?>
        %</font></td>
      <td align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $r->cdisc;?>
        %</font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $r->remark;?>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $status;?>
        </font></td>
      <td> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $audit;?>
        </font></td>
    </tr>
    <?
		}
	?>
  </table>
</form>
