 <div id='payroll_income' style='position:absolute; width:100%; height:100%; z-index:1; overflow: auto; background-color: #FFFFFF; layer-background-color: #FFFFFF; border: 1px none #000000;'>
   <table width="100%" cellpadding="0" cellspacing="1" border="0" bgcolor="#000033">
      <tr bgcolor="#ADC8E4"> 
        <td width="10%" height="24" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">#</font></strong></td>
        <td colspan="2" width="43%"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp;Income 
          Type</font></strong></td>
        <td width="15%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Qty</font></strong></td>
        <td width="19%" align="center"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Amount</font></strong></td>
        <td width="14%">&nbsp;</td>
      </tr>		  
      <tr height="200"> 
        <td valign="top" colspan=6> 
	  <?="<div id='Layer1' style='position:absolute; width:100%; height:100%; z-index:1; overflow: scroll; background-color: #FFFFFF; layer-background-color: #FFFFFF; border: 1px none #000000;'>";?>
          <table width='100%' border='0' align='left' cellpadding='3' cellspacing='1' bgcolor='#EFEFEF'>
		  <?
		  $ctr=0;
		  $c=0;
		  $paytrans['gross_income']=0;
		  $paytrans['gross_deduction']=0;
		  foreach ($aIncome as $temp)
		  {
		  	$ctr++;
			if ($temp['type']=='I')
				$paytrans['gross_income'] += $temp['amount'];
			else
			{
				$paytrans['gross_deduction'] += $temp['amount'];
				continue;
			}	
			$c++;
			?>
      <tr bgcolor="#FFFFFF" onMouseOver="bgColor='#FFFFCC'" onMouseOut="bgColor='#FFFFFF'"> 
        <td width="10%" align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          <?= $c;?>
          . 
          <input name="delete[]" type="checkbox" value="<?= $temp['paytrans_type_id'];?>">
          </font></td>
        <td width="45%"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          <?= $temp['paytrans_type'];?>
          </font></td>
        <td width="15%" align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          <?= number_format($temp['qty'],2);?>
          </font></td>
        <td width="20%" align="right"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          <?= number_format($temp['amount'],2);?>
          </font></td>
        <td width="10%" align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
          <a href=''>&laquo; Edit</a></font></td>
      </tr>
      <?
			}
			
       ?>
	   </table>
	<?="</div>";?>
	</td>
	</tr>
	<tr  height="50"> 
      
    <td colspan="6" bgcolor="#ADC8E4"><strong><font size="2" face="Verdana, Arial, Helvetica, sans-serif" color="#000033"> 
      Income Type 
      <input type='text' name='paytrans_code' size="10" value='<?=$paytrans_code;?>'>
        <input type='submit' name='p3' value='Select'>
        </font><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?
			echo "<select name='paytrans_type_id'>";
			echo "<option value=''>Select Transaction Type</option>";

			$q = "select paytrans_type, paytrans_type_id 
					from paytrans_type where type='I' ";
				
			$q .= " order by paytrans_code, paytrans_type";
			
			$qr = mysql_query($q) or die (mysql_error());
			while ($r=mysql_fetch_object($qr))
			{
				if ($paytrans_type_id == $r->paytrans_type_id)
					echo "<option value=$r->paytrans_type_id selected>$r->paytrans_type</option>";
				else	
					echo "<option value=$r->paytrans_type_id>$r->paytrans_type</option>";
			}
			echo "</select>";	
			?>
        </font><font size="2" face="Verdana, Arial, Helvetica, sans-serif" color="#000033"> 
        Qty 
        <input type='text' size='10' name='qty' value='<?=$qty;?>'>
        Amount 
        <input type='text' size='10' name='amount' value='<?=$amount;?>'>
        <input name='p1' type='submit' id="p1" value="<?= ($p1=='Edit' ? 'Update' : 'Insert');?>">
      </font></strong> </td>
    </tr>
 
    </table>
</div>	