<script type="text/javascript">
function printIframe(id)
{
    var iframe = document.frames ? document.frames[id] : document.getElementById(id);
    var ifWin = iframe.contentWindow || iframe;
    iframe.focus();
    ifWin.printPage();
    return false;
}
</script>

<script language="JavaScript" type="text/JavaScript">
<!--
function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);
//-->
function vSubmit(ul)
{
	if (ul.name == 'Cancel')
	{
		if (confirm("Are you sure to CANCEL customer Record?"))
		{
			document.f1.action="?p=report.bincard&p1=CancelConfirm"
		}	
		else
		{
			document.f1.action="?p=report.bincard&p1=Cancel"
		}
	}
	else
	{
		document.f1.action="?p=report.bincard&p1="+ul.id;
	}	
}
</script>
<?
if ($to_date == '') $aBIN['to_date']=date('m/d/Y');
if ($from_date == '') $aBIN['from_date']=addDate($aBIN['to_date'],-30);

if (!session_is_registered('aBIN'))
{
	session_register('aBIN');
	$aBIN=null;
	$aBIN=array();
}
if ($p1 != '')
{
	$aBIN['from_date'] = $_REQUEST['from_date'];
	$aBIN['to_date'] = $_REQUEST['to_date'];
}
if ($c_id!= ''  && $p1 == 'selectStock')
{
	$aBIN=null;
	$aBIN=array();
	$q = "select 
				*
		 from 
		 		stock
		where 
				stock_id='$c_id'";
	$r = fetch_assoc($q);
	$aBIN = $r;
	$aBIN['from_date'] = $_REQUEST['from_date'];
	$aBIN['to_date'] = $_REQUEST['to_date'];
}
?> 

<form action="" method="post" name="f1" id="f1">
  <table width="80%" border="0" align="center" cellpadding="2" cellspacing="1">
    <tr> 
      <td background="../graphics/table0_horizontal.PNG"><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong><img src="../graphics/bluelist.gif" width="16" height="17"> 
        BinCard/Stock Card </strong></font></td>
    </tr>
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Search Item 
        <input name="xSearch" type="text" id="xSearch" value="<?= $xSearch;?>">
        <?= lookUpAssoc('searchby',array('Bar Code'=>'barcode','Item Name'=>'stock','Description'=>'stock_description'), $searchby);?>
        </font><font  color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
        <input name="from_date" type="text" id="from_date"  onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $aBIN['from_date'];?>" size="9">
        </strong></font><img src="../graphics/dwn-arrow-grn.gif" onclick="popUpCalendar(this, f1.from_date, 'mm/dd/yyyy')"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        To </font><font  color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
        <input name="to_date" type="text" id="to_date"  onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $aBIN['to_date']?>" size="9">
        <img src="../graphics/dwn-arrow-grn.gif" onclick="popUpCalendar(this, f1.to_date, 'mm/dd/yyyy')"> 
        </strong></font><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="p1" type="submit" id="p1" value="Go">
        <input name="p1" type="submit" id="p1" value="Refresh">
        </font></td>
    </tr>
    <tr> 
      <td><hr color="red"></td>
    </tr>
  </table>
<?

if ($p1!='Go' && $aBIN['stock_id']!='')
{
	if (in_array($aBIN['from_date'], array('','--','//')))
	{
		$aBIN['from_date'] = date('m/01/Y');
		$aBIN['to_date'] = date('m/d/Y');
	}
	$tables = currTables(mdy2ymd($aBIN['from_date']));
	$sales_header = $tables['sales_header'];
	$sales_detail = $tables['sales_detail'];
	$sales_tender = $tables['sales_tender'];
	$stockledger = $tables['stockledger'];


	if ($aBIN['fraction3'] == '0') $aBIN['fraction3'] = 1;
	$fraction3 = $aBIN['fraction3'];

	if ($aBIN['fraction2'] == '0') $aBIN['fraction2'] = 1;
	$fraction2 = $aBIN['fraction2'];

	$aBIN['from_date'] = $_REQUEST['from_date'];
	$aBIN['to_date'] = $_REQUEST['to_date'];

	if ($p1 == 'Print Draft') $header .= "<reset>";
  	$header .= center($SYSCONF['BUSINESS_NAME'],80)."\n";

	if ($p1 == 'Print Draft') $header .= "<bold>";
  	$header .= center('B-I-N-C-A-R-D',80)."\n\n";
	if ($p1 == 'Print Draft') $header .= "</bold>";

	if ($p1 == 'Print Draft') $header .= "</bold><small3>";
	$header .= adjustSize("Item: [".$aBIN['stock_id']."] ".$aBIN['stock'],45).' '.
  				'Dates: '.$from_date.' To '.$to_date."\n";
	$header .= "Item Code  : ".$aBIN['barcode'].'  ';
	$header .= "U/C: ".$aBIN['fraction3']."'s   ";
	$header .= "Last Cost: ".number_format($aBIN['cost1'],2).'    ';
	$header .= "SRP: ".number_format($aBIN['price1'],2)."\n";
	$header .= "\n";
	$header .= str_repeat('-',78)."\n";
	$header .= " Date      Reference Type        Particulars        IN     OUT     BALANCE  \n";
	$header .= str_repeat('-',78)."\n";
	
	$mfrom_date = mdy2ymd($aBIN['from_date']);
	$mto_date = mdy2ymd($aBIN['to_date']);
	$beginning_balance = 0;
	
	$fraction3 = $aBIN['fraction3'];	
?>
  <div align="center">
    <table width="80%" border="0" align="center" cellpadding="2" cellspacing="1">
      <tr> 
        <td height="27"><table width="100%" border="0" cellspacing="0" cellpadding="2">
            <tr bgcolor="#000033"> 
              <td nowrap background="../graphics/table0_horizontal.PNG"><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
                <img src="../graphics/bluelist.gif" width="16" height="17">BinCard 
                Preview</strong></font> </td>
            </tr>
          </table></td>
      </tr>
      <tr> 
        <td valign="top" bgcolor="#FFFFFF">
	   <!--<textarea name="print_area" cols="110" rows="20"  wrap="off" readonly><?= $details1;?></textarea> -->
        </td>
      </tr>
    </table>
    
    <?
	if($_REQUEST['from_date'] && $_REQUEST['to_date'] && $_REQUEST['c_id'] ){
	?>
    
    <iframe id="JOframe" name="JOframe" style="background-color:#FFF; width:100%;" frameborder="0" height="500" src="print_stock_card.php?from_date=<?=$_REQUEST['from_date']?>&to_date=<?=$_REQUEST['to_date']?>&stock_id=<?=$_REQUEST['c_id']?>">
    </iframe>
    <input type="button" value="Print" onclick="printIframe('JOframe');" />
	<? } ?>
    <!--<input name="p1" type="submit" id="p1" value="Print Draft" >
    <input name="p12" type="button" id="p12" value="Print"  onClick="printIframe(print_area)" > -->
  </div>

<?
}
elseif ($p1 == 'Go')
{
	$aBIN['from_date'] = $_REQUEST['from_date'];
	$aBIN['to_date'] = $_REQUEST['to_date'];

  	$q = "select * 
				from 
					stock
				where  1=1 ";
	if ($searchby == 'barcode')
	{
		$q .= " and barcode = '$xSearch'";
	}
	else
	{
		$q .= "	and $searchby like '%$xSearch%' 	order by stock";
	}
	$qr = @pg_query($q) 	or message1("Error Querying Stock file...".pg_errormessage());
	
?>
  
<table width="85%" border="0" align="center" cellpadding="2" cellspacing="1" bgcolor="#EFEFEF">
  <tr bgcolor="#CFD3E7"> 
    <td width="7%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
    <td width="9%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Item 
      Code </font></strong></td>
    <td width="29%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Item 
      Name</font></strong></td>
    <td width="10%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Unit</font></strong></td>
      <td width="22%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Category</font></strong></td>
      <td width="23%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">SRP</font></strong></td>
  </tr>
  <?
  	$ctr=0;
  	while ($r = @pg_fetch_object($qr))
	{
		$ctr++;
  ?>
  <tr bgcolor="#FFFFFF" onMouseOver="bgColor='#FFFFCC'" onMouseOut="bgColor='#FFFFFF'"> 
    <td align="right" nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?=$ctr;?>
      .</font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
	<a href="javascript: document.getElementById('f1').action='?p=report.bincard&p1=selectStock&c_id=<?=$r->stock_id;?>';document.getElementById('f1').submit()">      <?= $r->barcode;?>
      </a> </font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
	<a href="javascript: document.getElementById('f1').action='?p=report.bincard&p1=selectStock&c_id=<?=$r->stock_id;?>&from_date=<?=$_REQUEST['from_date']?>&to_date=<?=$_REQUEST['to_date']?>';document.getElementById('f1').submit()">
      <?= $r->stock;?>
      </a></font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= $r->unit;?>
      </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= lookUpTableReturnValue('x','category','category_id','category',$r->category_id);?>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; </font><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
        </font><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
        <?= number_format($r->price1,2);?>
        </font></td>
  </tr>
  <?
  }
  ?>
</table>
<?	
  }
?>
</form>

<iframe name="printit" id="printit" style="width:0px;height:0px;"></iframe>
