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
			document.f1.action="?p=poitem&p1=CancelConfirm"
		}	
		else
		{
			document.f1.action="?p=poitem&p1=Cancel"
		}
	}
	else
	{
		document.f1.action="?p=poitem&p1="+ul.id;
	}	
}
</script>
<?
if ($to_date == '') $to_date=date('m/d/Y');
if ($from_date == '') $from_date=addDate($to_date,-30);

?> 

<form action="" method="post" name="f1" id="f1">
  <table width="90%" border="0" align="center" cellpadding="2" cellspacing="1">
    <tr> 
      <td background="../graphics/table_horizontal.PNG"><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong><img src="../graphics/bluelist.gif" width="16" height="17"></strong></font> 
        <font color="#FFFFFF" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>History 
        of Item Purchases Order</strong></font></td>
    </tr>
    <tr> 
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Search Item 
        <input name="xSearch" type="text" id="xSearch" value="<?= $xSearch;?>">
        <?= lookUpAssoc('searchby',array('Bar Code'=>'barcode','Item Name'=>'stock','Description'=>'stock_description'), $searchby);?>
        </font><font  color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
        <input name="from_date" type="text" id="from_date"  onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $from_date;?>" size="9">
        </strong></font><img src="../graphics/dwn-arrow-grn.gif" onclick="popUpCalendar(this, f1.from_date, 'mm/dd/yyyy')"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        To </font><font  color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong> 
        <input name="to_date" type="text" id="to_date"  onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $to_date?>" size="9">
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

if ($c_id!= ''  && $p1 == 'selectStock')
{
?>
<div align="center">
	<iframe id="JOframe" name="JOframe" style="background-color:#FFF; margin:auto;" frameborder="0" width="90%" height="500" src="print_poitem.php?to_date=<?=$_REQUEST['to_date']?>&from_date=<?=$_REQUEST['from_date']?>&c_id=<?=$_REQUEST['c_id']?>"></iframe><br />
    <input type="button" value="Print" onclick="printIframe('JOframe');" />
</div>   
<?
}
elseif ($p1 == 'Go')
{
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
  
<table width="75%" border="0" align="center" cellpadding="2" cellspacing="1" bgcolor="#EFEFEF">
  <tr bgcolor="#CFD3E7"> 
    <td width="6%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
      <td width="11%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">BarCode 
        </font></strong></td>
    <td width="39%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Item 
      Name</font></strong></td>
    <td width="6%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Unit</font></strong></td>
      <td width="21%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Category</font></strong></td>
      <td width="17%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">SRP</font></strong></td>
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
	<a href="javascript: document.getElementById('f1').action='?p=poitem&p1=selectStock&c_id=<?=$r->stock_id;?>';document.getElementById('f1').submit()">      <?= $r->barcode;?>
      </a> </font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
	<a href="javascript: document.getElementById('f1').action='?p=poitem&p1=selectStock&c_id=<?=$r->stock_id;?>';document.getElementById('f1').submit()">
      <?= $r->stock;?>
      </a></font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= $r->unit;?>
      </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= lookUpTableReturnValue('x','category','category_id','category',$r->category_id);?>
        </font></td>
      <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $r->price1;?>
        </font><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
        </font><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
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
