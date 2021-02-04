<div id="Layer1" style="position:virtual; width:100%; z-index:1; height: 100%; overflow: auto;">
  <table width="100%" border="0" cellspacing="1" cellpadding="1">
    <?
  $ctr=0;
  foreach ($iPTI as $temp)
  {
  	$ctr++;
  ?>
    <tr bgcolor="#FFFFFF" onMouseOver="bgColor='#FFFFCC'" onMouseOut="bgColor='#FFFFFF'"> 
      <td align="right" width=10%><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $ctr;?>
        . <a href="?p=payroll.transaction&p1=editincome&ctr=<?=$ctr;?>">
		<img src="../graphics/b_edit.png" alt="Click To Edit <?=$temp['income_type'];?>  Income Entry" width="11" height="12" border="0"></a> 
        </font></td>
      <td width=50%><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <a href="?p=payroll.transaction&p1=editincome&ctr=<?=$ctr;?>"> 
        <?= $temp['income_type'];?>
        </a></font></td>
      <td align="right"  width=15%><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= $temp['income_qty'];?>
        </font></td>
      <td align="right" width=25%><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?= number_format($temp['income_amount'],2);?>
 		<img src="../graphics/b_drop.png" alt="Click To Delete <?=$temp['income_type'];?>  Income Entry" width="9" height="8" border="0" onClick="if (confirm('Are you sure to Delete this Income Entry')){document.getElementById('f1').action='?p=payroll.transaction&p1=deleteincome&ctr=<?=$ctr;?>';document.getElementById('f1').submit()}">
       </font></td>
    </tr>
    <?
	}
	?>
  </table>
</div>	
