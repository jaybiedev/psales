<div id="Layer1" style="position:absolute; width:100%; z-index:1; height: 100%; overflow: auto;">
  <table width="100%" border="0" cellspacing="0" cellpadding="0">
  <?
  $ctr=0;
  foreach ($iPTI as $temp)
  {
  ?>
    <tr>
      <td align="right" width=10%><?= $ctr;?>.</td>
      <td width=50%><?= $temp['income_type'];?></td>
      <td align="right"  width=15%><?= $temp['qty'];?></td>
      <td align="right" width=25%><?= $temp['amount'];?></td>
    </tr>
	<?
	}
	?>
  </table>
</div>	
