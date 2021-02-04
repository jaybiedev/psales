<?
require_once(dirname(__FILE__).'/../lib/lib.salvio.php');
if( !empty($_REQUEST['action']) ) call_user_func($_REQUEST['action'], $_REQUEST);

function add($form_data){  

  pg_prepare('insert_additional_reward_points',"
    insert into additional_reward_points
      ( 
        from_date, 
        to_date, 
        supplier_id, 
        from_category_id, 
        to_category_id, 
        barcode,
        multiplier
      )
    values
      (
        $1,$2,$3,$4,$5,$6,$7
      )
  ");

  $arr = array( $form_data['from_date'], $form_data['to_date'] );
  $arr[] = empty($form_data['account_id']) ? null : $form_data['account_id'];
  $arr[] = empty($form_data['from_category_id']) ? null : $form_data['from_category_id'];
  $arr[] = empty($form_data['to_category_id']) ? null : $form_data['to_category_id'];
  $arr[] = empty($form_data['barcode']) ? null : $form_data['barcode'];
  $arr[] = empty($form_data['multiplier']) ? null : $form_data['multiplier'];
  
  pg_execute('insert_additional_reward_points', $arr);

}

function delete($form_data){
  pg_query("
    delete from additional_reward_points where id = '$form_data[id]'
  ");

}

?>
  <script type="text/javascript">
    function printIframe(id) {
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
      border-collapse: collapse;
      ;
      font-family: Arial;
    }

    .table thead {
      font-weight: bold;
      font-size: 12px;
    }

    .table tbody td {
      font-size: 12px;
      border-top: 1px solid #c0c0c0;
      border-bottom: 1px solid #c0c0c0;
    }

    .table tbody tr:hover td {
      background-color: #EFEFEF;
    }
  </style>
  <?
if ($from_date == '') $from_date=date('m/d/Y');	
if ($to_date == '') $to_date=date('m/d/Y'); 
?>
    <form name="form1" method="post" action="">
      <div align="center">
        <table width="97%" border="0" align="center" cellpadding="0" cellspacing="1">
          <tr bgcolor="#C7E9E3" background="../graphics/table0_horizontal.PNG">
            <td height="20" colspan="4">
              <font size="2" face="Verdana, Arial, Helvetica, sans-serif">
                <strong>&nbsp;
                  <font color="#EFEFEF">:: Promotional Items</font>
                </strong>
              </font>
            </td>
          </tr>
          <tr>
            <td width="16%">
              <font size="2" face="Verdana, Arial, Helvetica, sans-serif">Supplier Code From</font>
            </td>
            <td width="34%">
              <font size="2" face="Verdana, Arial, Helvetica, sans-serif">
                <select name='account_id' id='account_id' style="width:250px" onKeypress="if(event.keyCode==13) {document.getElementById('from_date').focus();return false;}">
                  <option value=''>Select Supplier -- </option>
                  <?
                  foreach ($aSUPPLIER as $stemp)
                  {
                    if ($stemp['account_id'] == $aPS['account_id'])
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
            </td>
            <td width="15%">
              <font size="2" face="Verdana, Arial, Helvetica, sans-serif">Category From
              </font>
            </td>
            <td width="35%">
              <font size="2" face="Verdana, Arial, Helvetica, sans-serif">
                <select name='from_category_id' id="from_category_id" style="width:235px" onKeypress="if(event.keyCode==13) {document.getElementById('to_category_id').focus();return false;}"
                  onBlur="vFrom('category_id')">
                  <option value=''>All Categories -- </option>
                  <?
                  foreach ($aCATEGORY as $ctemp)
                  {
                    if ($ctemp['category_id'] == $aPS['from_category_id'])
                    {
                      echo "<option value=".$ctemp['category_id']." selected>".substr($ctemp['category_code'],0,6)." ".$ctemp['category']."</option>";
                    }
                    else
                    {
                      echo "<option value=".$ctemp['category_id']." >".substr($ctemp['category_code'],0,6)." ".$ctemp['category']."</option>";
                    }
                  }
                  ?>
                </select>
              </font>
            </td>
          </tr>
          <tr>
            <td>
              <font size="2" face="Verdana, Arial, Helvetica, sans-serif">Promo Date From
              </font>
            </td>
            <td>
              <font size="2" face="Verdana, Arial, Helvetica, sans-serif">
                <input name="from_date" type="text" id="from_date" value="<?= ymd2mdy($aPS['from_date']);?>" size="10" onBlur="IsValidDate(this,'MM/dd/yyyy');vFrom('date')"
                  onKeyUp="setDate(this,'MM/dd/yyyy','en')" onKeypress="if(event.keyCode==13) {document.getElementById('to_date').focus();return false;}">
                <img src="../graphics/dwn-arrow-grn.gif" onclick="popUpCalendar(this, f1.from_date, 'mm/dd/yyyy')">
              </font>
            </td>
            <td>
              <font size="2" face="Verdana, Arial, Helvetica, sans-serif">Category To
              </font>
            </td>
            <td>
              <font size="2" face="Verdana, Arial, Helvetica, sans-serif">
                <select name='to_category_id' id="to_category_id" style="width:235px" onKeypress="if(event.keyCode==13) {document.getElementById('sdisc').focus();return false;}">
                  <option value=''>All Categories -- </option>
                  <?
							foreach ($aCATEGORY as $ctemp)
							{
								if ($ctemp['category_id'] == $aPS['to_category_id'])
								{
									echo "<option value=".$ctemp['category_id']." selected>".substr($ctemp['category_code'],0,6)." ".$ctemp['category']."</option>";
								}
								else
								{
									echo "<option value=".$ctemp['category_id']." >".substr($ctemp['category_code'],0,6)." ".$ctemp['category']."</option>";
								}
							}
							?>
                </select>
              </font>
            </td>
          </tr>
          <tr>
            <td>
              <font size="2" face="Verdana, Arial, Helvetica, sans-serif">Promo Date To
              </font>
            </td>
            <td>
              <font size="2" face="Verdana, Arial, Helvetica, sans-serif">
                <input name="to_date" type="text" id="to_date" value="<?= ymd2mdy($aPS['to_date']);?>" size="10" onBlur="IsValidDate(this,'MM/dd/yyyy');"
                  onKeyUp="setDate(this,'MM/dd/yyyy','en')" onKeypress="if(event.keyCode==13) {document.getElementById('from_category_id').focus();return false;}">
                <img src="../graphics/dwn-arrow-grn.gif" onclick="popUpCalendar(this, f1.to_date, 'mm/dd/yyyy')">
              </font>
            </td>
          </tr>
          <tr>
            <td>
              <font size="2" face="Verdana, Arial, Helvetica, sans-serif">Barcode</font>
            </td>
            <td>
              <input type="text" name="barcode" > 
            </td>
          </tr>
        
          <tr>
            <td>
              <font size="2" face="Verdana, Arial, Helvetica, sans-serif">Multiplier</font>
            </td>
            <td>
              <input type="text" name="multiplier" > 
              <button name='action' value='add'>Add</button>
            </td>
          </tr>
        </table>
        <table style='width:97%; background-color:#FFF;' class='table'>
          <thead>
            <tr background="../graphics/table0_horizontal.PNG">
              <td colspan="10" style="padding:4px;">
                <font color="#FFF" size="2" face="Verdana, Arial, Helvetica, sans-serif">
                  <strong>Promo Items Details</strong>
                </font>
              </td>
            </tr>
            <tr>
              <td style="width:5%;"></td>
              <td>FROM DATE</td>
              <td>TO DATE</td>
              <td>SUPPLIER</td>
              <td>FROM CATEGORY</td>
              <td>TO CATEGORY</td>
              <td>BARCODE</td>
              <td>MULTIPLIER</td>
            </tr>
          </thead>
          <tbody>
            <?
            $sql = "
              select 
                additional_reward_points.*,
                from_category.category as from_category,
                from_category.category_code as from_category_code,
                to_category.category as to_category,
                to_category.category_code as to_category_code,
                account,
                multiplier
              from 
                additional_reward_points 
                left join category as from_category on from_category.category_id = additional_reward_points.from_category_id
                left join category as to_category on to_category.category_id = additional_reward_points.to_category_id
                left join account on account.account_id = supplier_id
              order by id desc
            ";


            $arr = lib::getArrayDetails($sql);
            if ( count($arr) ){
              foreach ($arr as $r) { ?>
              <tr>
                <td style='text-align:center;'>
                  <a style='color:#f00; font-weight:bold;' href="?p=inventory.additional_rewards&action=delete&id=<?=$r['id']?>">x</a>
                </td>
                <td><?=lib::ymd2mdy($r['from_date'])?></td>
                <td><?=lib::ymd2mdy($r['to_date'])?></td>
                <td><?=$r['account']?></td>
                <td><?=$r['from_category_code']?>-<?=$r['from_category']?></td>
                <td><?=$r['to_category_code']?>-<?=$r['to_category']?></td>
                <td><?=$r['barcode']?></td>
                <td><?=$r['multiplier']?></td>
              </tr>
              <? }
            }
            ?>
          </tbody>
        </table>


      </div>
    </form>

    <iframe name="printit" id="printit" style="width:0px;height:0px;"></iframe>