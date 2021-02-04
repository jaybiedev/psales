<?php session_start();
        require_once("../xajax.inc.php");
        $xajax = new xajax();
        $g     = "";
        
        $g->objResponse = new xajaxResponse();
        
		$DBDOMAIN='localhost';
		$DBNAME='markone';
		$DBUSERNAME='postgres';
		$DBPASSWORD='postgres';
		$DBCONNECT = "host=$DBDOMAIN port=5432  dbname=$DBNAME user=$DBUSERNAME password=$DBPASSWORD";
		$DBCONN = pg_Connect($DBCONNECT) or die("Can't connect to server...");
		
		$module = 'cashier';
		if (!session_is_registered('aCashier'))
		{
			session_register('aCashier');
			$aCashier	=	null;
			$aCashier	=	array();
		}
		if (!session_is_registered('aItems'))
		{
			session_register('aItems');
			$aItems	=	null;
			$aItems	=	array();
		}
		if (!session_is_registered('item'))
		{
			session_register('item');
			$item=null;
			$item=array();	
		}
		
        function glayer($layer, $content) {
                global $g;
                $g->objResponse->addAssign($layer, 'innerHTML', $content);
        }
        
        function hide_layer($layer) {
                global $g;
                $g->objResponse->addAssign($layer, 'style.display', 'none');
        }
        
        function show_layer($layer) {
                global $g;
                $g->objResponse->addAssign($layer, 'style.display', 'block');
        }
        
        function done() {
                global $g;
                hide_layer('wait.layer');
                return $g->objResponse->getXML();
        }
        
        
        function gset($element, $value) {
                global $g;
                 $g->objResponse->addAssign($element, 'value', $value);
        }
        
		//-- form/module defined         
       $xajax->registerFunction('select_stock');
        function select_stock($id) {
                $sql    = "select * from suspect where id = '$id'";
                
                glayer('message.layer', $sql);
                //return done();
                
                if (!$q = mysql_query($sql)) {
                      $message  = mysql_error($q);
                      glayer('message.layer', $message);
                      return done();  
                }                   
                
                if (!mysql_num_rows($q)) {
                      $message  = "No record found...";
                      glayer('message.layer', $message);
                      return done();
                }
                
                $r      = mysql_fetch_object($q);
                
                $res    = "<table border='1'>
                                <tr>    
                                        <td>$r->lastname</td>
                                        <td>$r->firstname</td>
                                        <td>$r->middle_name</td>
                                        <td>$r->aliases</td>
                                </tr>
                        </table>";
                        
                glayer('suspect.layer', $res);
                return done();           
        }
        

        $xajax->registerFunction('search');
        function search($form) 
		{
				global $aItems;
                $value  = $form['textbox'];
 
                $sql    = "select * from stock
                                where
                                        barcode = '$value'";
                $q = pg_query($sql);
					  $message = "TEST ";
                      glayer('message.layer', $message);
                      return done();  
				                       
                if ($q) 
				{
                      $message  = pg_errormessage();
					  $message = "TEST ";
                      glayer('message.layer', $message);
                      return done();  
                }                   
              
                if (!pg_num_rows($q)) {
                      $message  = "No record found...";
                      glayer('message.layer', $message);
                      return done();
                }                     
			
				$rows = null;
				$rows = array();

				$r = pg_fetch_assoc($q);
				$aItems[] = $r;

				$ctr=0;
                foreach ($aItems as $temp)
				{
					$ctr++;
					
					$qty = $temp ['qty'];
					$stock_id = $temp['stock_id'];
					$barcode = $temp['barcode'];
					$stock=$temp['stock'];
					$price = $temp['price1'];
					$amount = $temp['amount'];
					$bgColor='#FFFFFF';
					
                    $rows[] = "<tr bgColor='$bgColor'> <td align='right'>$qty</td>
									<td><a href='#'  onclick=\"form1.search.value=$barcode\">
					                $barcode</a></td>
									<td>$stock</td>
									<td align='right'>$price</td>
									<td align='right'>$amount</td>
								</tr>";
                }                
				$header = "<table border='0' bgColor='#EFEFEF'><tr>
							<td><Qty></td><td>Barcode</td><td>Description</td><td>Price</td><td>Amount</td></tr>";
				$footer  = "</table>";
				
                $result = $header;
				$result .= implode($rows);
				$result .= $footer;
   /*             if (1)
				{
                      $message  = "MESS ".$result;
                      glayer('message.layer', $message);
                      return done();
                }                     
	*/
                glayer("result.layer", $result);

                return done();
        }
        
//$xajax->debugOn();
		

$xajax->processRequests();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html><head>
<?php $xajax->printJavascript('../'); // output	the xajax javascript. This must	be called between the head tags	?>
<script language='javascript'>
function wait($message) {
	   xajax.$('wait.layer').style.display = 'block';
	  }
	  
	  function blank_layer($layer) {
				xajax.$($layer).innerHTML       = "";
	  }
	  
	  function hide_layer($layer)  {
				xajax.$($layer).style.display = 'none';
	  }
                      
</script>
</head>
<body bgcolor="#CCCCCC" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<form name="f1"	method="post" style="margin:0px;padding:0px">
  <table width="99%" height="95%" border="0"	cellpadding="0"	cellspacing="0" align="center">
    <tr	height="5%"> 
      <td	width="79%"	height="1%"	bgcolor="#CCCCCC"> <strong><font color="#CC3300" size="5" face="Times New Roman, Times, serif"> 
        <em>
        <?=	$SYSCONF['BUSINESS_NAME'];?>
        <?= ($_SERVER['REMOTE_ADDR'] == '127.0.0.1' ? ' - Local' : '');?>
        </em></font></strong><br>
        <font size="1"	face="Verdana, Arial,	Helvetica, sans-serif">
        <?=	$SYSCONF['BUSINESS_ADDR'];?>
        </font></td>
      <td	width="21%"	align="center" bgcolor="#CCCCCC"> <font size="2"> <img alt="Home Page" src="graphics/home.gif" width="21" height="10">: 
        <?=	$ADMIN['username'];?>
        <br>
        <?=	date('F	d, Y');?>
        [ <a href="?p=logout">Logout</a> ] </font></td>
    </tr>
    <tr	height="70%"> 
      <td	valign="top" width="79%"> 
        <table width="100%"	height="100%"	border="0" cellspacing="0" cellpadding="0" >
          <tr	height="5%"> 
            <td	colspan="5" bgcolor="#C6C6C6"><font	size="5">Item</font> 
              <input	name="textbox" type="text" id="textbox" style="font-size:25; font-family: 'Times New Roman'; border:1 solid #CCCCFF; background-color:#EFEFEF;" value="<?= $item['textbox'];?>" size="30" onFocus="document.getElementById('Ok').disabled=0">
              <input type="button" name="p1" value="Ok"	id='Ok' style="font-size:20; font-family: 'Times New Roman'" onclick="wait('Searching');xajax_search(xajax.getFormValues('f1'));">
              <?

			  if ($aCashier['return'] == 'RETURN')
			  {
			  	echo " Return From Receipt#:[".$aCashier['return_invoice'].'] Scan Item ...';
			  }
			  ?>
            </td>
          </tr>
          <tr	height="2%" bgcolor="#C0C0C0"> 
            <td	width="10%" align="center" height="4"><strong><font size="2"	face="Verdana, Arial,	Helvetica, sans-serif">Qty</font></strong></td>
            <td	width="15%" align="center" height="4"><strong><font	size="2" face="Verdana,	Arial, Helvetica,	sans-serif"> 
              BarCode</font></strong></td>
            <td	width="48%" height="12"><strong><font	size="2" face="Verdana,	Arial, Helvetica,	sans-serif">Item 
              Description</font></strong></td>
            <td	width="12%"	align="center" height="4"><strong><font size="2"	face="Verdana, Arial,	Helvetica, sans-serif">Price</font></strong></td>
            <td	width="15%"	align="center" height="4"><strong><font size="2"	face="Verdana, Arial,	Helvetica, sans-serif">Amount</font></strong></td>
          </tr>
          <tr height="70%"> 
            <td	colspan="5"	 valign="top"> 
            <div id="result.layer" style="position:relative; width:50%; height:300px; z-index:1; overflow: scroll; left: 0; top: 30; background-color: #FFFFFF; layer-background-color: #FFFFFF; border: 1px none #000000;"></div> 
            </td>
          </tr>
        </table>
      </td>
      <td valign="top" align="center" width="21%"> 
        <table	width="100%" border="0"	cellpadding="0"	cellspacing="0" bgcolor="#CCCCCC">
          <tr> 
            <td colspan="4"></td>
          </tr>
          <tr> 
            <td colspan="2">Docket<b> </b></td>
            <td colspan="2"><b> 
              <?= $aCashier['invoice'];?>
              </b></td>
          </tr>
          <tr> 
            <td colspan="2">Terminal</td>
            <td colspan="2"><b> 
              <?= $SYSCONF['TERMINAL'];?>
              </b></td>
          </tr>
          <tr> 
            <td colspan="4"><hr></td>
          <tr> 
            <td	width="26%"	align="center"> 
              <!--<input	name="image" type="image" onClick="" src="graphics/f1.jpg"border="0"; alt="Help">-->
              <font size="2"><strong>F1</strong></font> </td>
            <td colspan="3" valign="middle"><font face="Verdana,	Arial, Helvetica,	sans-serif" size="1">Help</font></td>
          </tr>
          <tr> 
            <td	width="26%"	align="center"> 
              <!--<img type="image" onClick="f1.textbox.value='';getElementById('textbox').focus()" src="graphics/f2.jpg" border="0";>-->
              <font size="2"><strong>F2</strong></font></td>
            <td colspan="3" valign="middle"><font face="Verdana,	Arial, Helvetica,	sans-serif" size="1">Clear</font></td>
          </tr>
          <tr> 
            <td	width="26%"	align="center"> 
              <!-- <img type="image" onClick="if(confirm('Are you sure to Delete Item?')){f1.action='?p=cashier&p1=Delete';f1.submit();}" src="graphics/f3.jpg" border="0";>-->
              <font size="2"><strong>F3</strong></font></td>
            <td colspan="3" valign="middle"><font face="Verdana,	Arial, Helvetica,	sans-serif" size="1">Delete 
              </font></td>
          </tr>
          <tr> 
            <td	width="26%"	align="center"> 
              <!-- <input	name="image" type="image" onClick="f1.action='?p=cashier&p1=Qty';f1.submit()" src="graphics/f4.jpg" border="0";>-->
              <font size="2"><strong>F4</strong></font> </td>
            <td colspan="3" valign="middle"><font face="Verdana,	Arial, Helvetica,	sans-serif" size="1">Qty</font></td>
          </tr>
          <tr> 
            <td	width="26%"	align="center" height="2"> 
              <!-- <input	name="image" type="image" onClick="f1.action='?p=cashier&p1=Price';f1.submit()" src="graphics/f5.jpg" border="0";>-->
              <font size="2"><strong>F5</strong></font></td>
            <td height="2" colspan="3" valign="middle"><font face="Verdana,	Arial, Helvetica,	sans-serif" size="1">Price</font></td>
          </tr>
          <tr> 
            <td align="center" valign="middle"> 
              <!-- <input	name="image2" type="image" onClick="f1.action='?p=cashier&p1=lineDisc';f1.submit()" src="graphics/f6.jpg" border="0";>-->
              <font size="2"><strong>F6</strong></font> </td>
            <td height="2" colspan="3" valign="middle"><font size="1" face="Verdana,	Arial, Helvetica,	sans-serif">Line 
              Disc </font></td>
          </tr>
          <tr> 
            <td align="center" valign="middle"> 
              <!-- <input	name="image3" type="image" onClick="f1.action='?p=cashier&p1=globalDisc';f1.submit()" src="graphics/f7.jpg" border="0";>-->
              <font size="2"><strong>F7</strong></font> </td>
            <td height="2" colspan="3" valign="middle"><font size="1" face="Verdana,	Arial, Helvetica,	sans-serif">Global 
              Disc </font></td>
          </tr>
          <tr> 
            <td align="center" valign="middle"> 
              <!-- <input	name="image32" type="image" onClick="f1.action='?p=cashier&p1=PopTender&id=1&tt=Cash';f1.submit()" src="graphics/f8.jpg" border="0";>-->
              <font size="2"><strong>F8</strong></font> </td>
            <td height="2" colspan="3" valign="middle"><font size="1" face="Verdana,	Arial, Helvetica,	sans-serif">Cash</font></td>
          </tr>
          <tr> 
            <td align="center" valign="middle"> 
              <!-- <img	src="graphics/f9.jpg"	border="0" onClick="f1.action='?p=cashier&p1=searchTender';f1.submit()";>-->
              <font size="2"><strong>F9</strong></font></td>
            <td height="2" colspan="3" valign="middle"><font size="1" face="Verdana,	Arial, Helvetica,	sans-serif">Tender</font></td>
          </tr>
          <tr> 
            <td height="2" align="center" valign="middle" nowrap> 
              <!-- <img	src="graphics/f10.jpg"	border="0" onClick="f1.action='?p=cashier&p1=Finish';f1.submit()";>-->
              <font size="2"><strong>F10</strong></font></td>
            <td height="2" colspan="3" valign="middle"><font size="1" face="Verdana,	Arial, Helvetica,	sans-serif">Finish 
              </font></td>
          </tr>
          <tr> 
            <td colspan="2" valign="middle">&nbsp;</td>
            <td width="52%" height="2" valign="middle">&nbsp;</td>
            <td width="20%" height="2" valign="middle">&nbsp;</td>
          </tr>
          <tr> 
            <td colspan="2" valign="middle"><font size="2"><strong>Alt+P</strong></font></td>
            <td height="2" colspan="2" valign="middle"><font size="1" face="Verdana,	Arial, Helvetica,	sans-serif">Re-Print</font></td>
          </tr>
          <tr> 
            <td colspan="2" valign="middle"><font size="2"><strong>Alt+Z</strong></font></td>
            <td height="2" colspan="2" valign="middle"><font size="1" face="Verdana,	Arial, Helvetica,	sans-serif">Z-Read</font></td>
          </tr>
        </table>
        <hr>
		<!-- 
<input name="image4322" type="image" accesskey="R" onClick="if (x=prompt('Return Item : Input Receipt No.:','')){f1.action='?p=cashier&p1=returnReceipt&id='+x}" src="graphics/altR.jpg" alt="Alt+R: Return Item " width="45" height="30">
        <input name="image432" type="image" accesskey="M" onClick="f1.action='?p=cashier&p1=memberSearch'" src="graphics/altM.jpg" alt="Alt+M: Rewards Member " width="45" height="30">
        <input name="image4" type="image" accesskey="S" onClick="f1.action='?p=cashier&p1=SuspendTry';f1.submit();" src="graphics/altS.jpg" alt="Suspend Transaction" width="45" height="30">
        <input type="image" src="graphics/altV.jpg" width="45" height="30" accesskey="V" onClick="if(confirm('Are you sure to VOID this transaction?')){f1.action='?p=cashier&p1=Void';f1.submit();}" alt="Void Transaction" name="image4">
        <br>
        <input name="image42" type="image" accesskey="P" onClick="if(confirm('Confirm Re-Print of Receipt?')){f1.action='?p=cashier&p1=RePrint';f1.submit();}" src="graphics/altP.jpg" alt="RePrint Receipt" width="45" height="30">
        <input name="image422" type="image" accesskey="C" onClick="f1.action='?p=cashier&p1=CashCount';f1.submit();" src="graphics/altC.jpg" alt="Cash Count" width="45" height="30">
        <input name="image42222" type="image" accesskey="T" onClick="if(confirm('Confirm Transaction Audit (X-Read)?')){f1.action='?p=cashier&p1=TAudit';f1.submit();}" src="graphics/altT.jpg" alt="Transaction Audit :: X-Reading" width="45" height="30">
        <input name="image4222" type="image" accesskey="Z" onClick="if(confirm('Confirm Transaction Closing?')){f1.action='?p=cashier&p1=ZRead';f1.submit();}" src="graphics/altZ.jpg" alt="Z-Reading" width="45" height="30">
		-->
      </td>
    </tr>
    <tr> 
      <td valign="top" colspan="2">
	    <table	width="100%" border="0"	cellpadding="1"	cellspacing="1">
          <tr> 
            <td colspan="3" valign="top"> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong><a href="?p=cashier&p1=Remarks">Customer:</a> 
              </strong> 
              <input type="image" src="graphics/b_edit.png" width="16" height="16" alt="Alt+N: Customer Name/Remark" accesskey="N" onClick="f1.action='?p=cashier&p1=Remarks';f1.submit();" name="image5">
              <br>
              &nbsp;&nbsp; <font color="#CC0000" size="2"><b> 
              <?= ($aCashier['account'] != '' ? $aCashier['account'] : $aCashier['remarks']);?>
              </b> </font></font><font	size="15">&nbsp; </font> </td>
            <td width="19%" valign="top" nowrap><font	size="15"> 
              <?
				if ($aCashier['tender_amount'] > $aCashier['net_amount'])
				{
					$due = "Change ";
				}
				else
				{
					$due = "Total: ";
				}
				echo $due;
				?>
              </font></td>
            <td width="29%"	align="right" valign="top" nowrap><font	size="15"> 
              <?
				if ($aCashier['tender_amount'] > $aCashier['net_amount'])
				{
					$due = number_format($aCashier['tender_amount'] - $aCashier['net_amount'],2);
				}
				else
				{
					$due = number_format($aCashier['net_amount']	-	$aCashier['tender_amount'],2);
				}
				echo $due;
				?>
              </font></td>
            <td width="6%"	align="right" valign="top" nowrap>&nbsp;</td>
          </tr>
          <tr> 
            <td	width="14%">Counter</td>
            <td	width="20%"	align="right"><?= $line_counter.' Lines '.$qty_counter.' Items';?></td>
            <td	width="12%"	align="right">&nbsp;</td>
            <td	width="19%">Gross Sales</td>
            <td	width="29%"	align="right"> 
              <?=	number_format($aCashier['gross_amount'],2);?>
            </td>
            <td	width="6%"	align="right">&nbsp;</td>
          </tr>
          <tr> 
            <td width="14%">Tax Base</td>
            <td	align="right" width="20%">
              <?=	number_format($aCashier['vat_sales'],2);?>
            </td>
            <td	align="right" width="12%">&nbsp;</td>
            <td width="19%">Discount</td>
            <td	align="right" width="29%"> 
              <?=	number_format($aCashier['discount_amount'],2);?>
            </td>
            <td	align="right" width="6%">&nbsp;</td>
          </tr>
          <tr> 
            <td width="14%">VAT</td>
            <td	align="right" width="20%">
              <?=	number_format($aCashier['total_tax'],2);?>
            </td>
            <td	align="right" width="12%">&nbsp;</td>
            <td width="19%">Service Charge</td>
            <td	align="right" width="29%"> 
              <?=	number_format($aCashier['service_charge'],2);?>
            </td>
            <td	align="right" width="6%">&nbsp;</td>
          </tr>
          <tr> 
            <td width="14%">NON VAT</td>
            <td	align="right" width="20%">
              <?=	number_format($aCashier['nonvat_sales'],2);?>
            </td>
            <td	align="right" width="12%">&nbsp;</td>
            <td width="19%">NET SALES</td>
            <td	align="right" width="29%"> 
              <?=	number_format($aCashier['net_amount'],2);?>
            </td>
            <td	align="right" width="6%">&nbsp;</td>
          </tr>
        </table>
	  </td>
    </tr>
  </table>
  <input type="image" src="graphics/blue_bullet.gif" width="1" height="1" onClick="window.location='?p=logout'" accesskey="L">
  <input type="image" src="graphics/blue_bullet.gif" width="1" height="1" onClick="f1.action='?p=';f1.submit();" accesskey="H">
  <div id='wait.layer'  style='position:absolute; display:none;top:40%;left:40%'>Searching...<br /><img src='wait.gif' /></div>
  <div id='message.layer'></div>
  <?
if ($p1	== 'PopTender')
{
	$amount = 0;
	foreach ($aItems as $temp)
	{
		if ($temp['type'] == 'Tender')
		{
			$amount -= $temp['amount'];
		}
		else
		{
			$amount += $temp['amount'];
		}
	}

	if ($tt	== 'C')
	{
		include_once('cashier.cash.php');
	}	
	elseif ($tt	== 'K')
	{
		include_once('cashier.cheque.php');
	}	
	elseif ($tt	== 'B')
	{
		include_once('cashier.bankcard.php');
	}	
	elseif ($tt	== 'A')
	{
		include_once('cashier.account.php');
	}
	else
	{
		echo "<script> document.getElementById('textbox').focus()</script>";
	}	
}		
elseif ($p1	== 'BrowseStock')
{
		include_once('cashier.searchstock.php');
}
elseif ($p1 == 'searchAccount')
{
		$q = "select * from account where account_code = '$cardno'";
		$r = fetch_object($q);
		$ok=0;
		if ($r>0)
		{
			$account = $r->account;
			$account_id= $r->account_id;
			$credit_limit = $r->credit_limit;
			$ok=1;
			
		}	
		elseif (intval($cardno) > '0')
		{
			$q = "select * from account where account_id = '$cardno'";
			$r = fetch_object($q);
			if ($r>0)
			{
				$account = $r->account;
				$account_code = $r->account_code;
				$cardno = $r->account_id;
				$account_id= $r->account_id;
				$credit_limit = $r->credit_limit;
				$ok=1;
			}	
		}
		elseif (strlen($cardno)>0)
		{
			$ok=2;
		}
		if ($ok<2)
		{
			include_once('cashier.account.php');
		}
		else
		{
			$account_id='';
			$account='';
			include_once('cashier.account.php');
			include_once('cashier.searchaccount.php');
		}
}
elseif ($p1	== 'searchTender')
{
		include_once('cashier.searchtender.php');
}
elseif ($p1	== 'SuspendTry')
{
		include_once('cashier.suspend.php');
}
elseif ($p1	== 'Remarks')
{
		include_once('cashier.remarks.php');
}
elseif ($p1	== 'memberSearch' || $p1 == 'Search Member')
{
		include_once('cashier.member.php');
}
elseif ($p1	== 'CashCount')
{
		echo "<script>window.open('cashcount.php','ccWin','left=50, top=50, height=370, width=450 ,location=no, status=0, scrollbars=1,resizable=1')</script>";
}
else
{
	echo "<script> document.getElementById('textbox').focus()</script>";
}
?>
</form>
<?
if ($message !=	'')
{
	echo "<script>alert('$message')</script>";
}
?>
