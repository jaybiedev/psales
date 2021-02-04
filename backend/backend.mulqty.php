<?
require_once(dirname(__FILE__).'/../lib/lib.salvio.php');
if( !empty($_REQUEST['action']) ) call_user_func($_REQUEST['action'], $_REQUEST);

function add($form_data){  

  
  $sql = "
    insert into mul_qty
      ( from_date, to_date, remark )
    values
      ('$form_data[from_date]', '$form_data[to_date]', '$form_data[remark]')
  ";
    
  pg_query($sql);


}

function delete($form_data){
  pg_query("
    delete from mul_qty where id = '$form_data[id]'
  ");

}

?>
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
</script>
<style type="text/css">
  .table {
    border-collapse: collapse;;    
    font-family: Arial;
  }
  .table thead{
    font-weight: bold;
    font-size: 12px;
  }
  .table tbody td{
    font-size: 12px;
    border-top: 1px solid #c0c0c0;
    border-bottom: 1px solid #c0c0c0;
  }

  .table tbody tr:hover td{
    background-color: #EFEFEF;
  }



</style>
<?
if ($from_date == '') $from_date=date('m/d/Y');	
if ($to_date == '') $to_date=date('m/d/Y'); 
?>	
<form name="form1" method="post" action="">
  <div align="center">
    <table width="90%" border="0" cellspacing="1" cellpadding="0" bgcolor="#FFFFFF">
      <tr bgcolor="#EFEFEF" background="../graphics/table0_horizontal.PNG"> 
        <td height="20" colspan="8" nowrap><font  color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          &nbsp;:: <strong>Allow Multiple Quantity on Specified Dates</strong>::</font></td>
      </tr>
      <tr style='font-family:Arial; font-size:12px;'>
        <td width="5%">From Date</td>
        <td width="5%">To Date</td>
        <td width="85%">Remarks</td>        
        <td width="5%">&nbsp;</td>
      </tr>
      <tr bgcolor="#EFEFEF"> 
          <td width="5%" nowrap align="center"><input name="from_date" type="text" id="from_date" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $from_date;?>" size="8">             
            <img src="../graphics/arrow_down.jpg" onClick="popUpCalendar(this, form1.from_date, 'mm/dd/yyyy')"> 
          </td>
          <td width="5%" nowrap align="center"><input name="to_date" type="text" id="to_date" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')" value="<?= $to_date;?>" size="8"> 
            <img src="../graphics/arrow_down.jpg" onClick="popUpCalendar(this, form1.to_date, 'mm/dd/yyyy')"> 
          </td>          
          <td width="90%" nowrap align="center">
              <input type='text' name='remark' style='width:100%;'>
          </td>          
          <td width="5%">
            <button name='action' value='add'>Add</button>
          </td>
      </tr>            
    </table>    
    <table style='width:90%; background-color:#FFF;' class='table'>
      <thead>
        <tr>
          <td style="width:5%;"></td>
          <td style="width:10%;">FROM DATE</td>
          <td style="width:10%;">TO DATE</td>
          <td style="width:75%;">REMARK</td>
        </tr>
      </thead>
      <tbody>
        <?
        $arr = lib::getArrayDetails("select * from mul_qty order by id desc");
        if ( count($arr) ){
          foreach ($arr as $r) { ?>
            <tr>
              <td style='text-align:center;'><a style='color:#f00; font-weight:bold;' href="?p=backend.mulqty&action=delete&id=<?=$r['id']?>">x</a></td>
              <td><?=lib::ymd2mdy($r['from_date'])?></td>
              <td><?=lib::ymd2mdy($r['to_date'])?></td>
              <td><?=$r['remark']?></td>              
            </tr>  
          <? }

        }
        ?>        
      </tbody>
    </table>
        
    
  </div>
</form>

<iframe name="printit" id="printit" style="width:0px;height:0px;"></iframe>
