 <script>
 function vComputeSalary()
 {
 	var pay_category = document.getElementById('pay_category').value;

	if (pay_category%2 != 0)
	{
	 	document.getElementById('f1').adwr.value = twoDecimals((document.getElementById('ratem').value*12)/365);
 		document.getElementById('f1').hourly.value = twoDecimals(document.getElementById('adwr').value/8);
	}
 }
 
 function vAdwr(obj)
 {
 	var adwr = obj.value;
	document.getElementById('f1').hourly.value = twoDecimals(document.getElementById('adwr').value/8);
 }
 </script> 
  
<table width="100%" border="0" align="center" cellpadding="1" cellspacing="1">
  <tr bgcolor="#EFEFEF"> 
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Employment 
      Status</font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= lookUpAssoc('emp_status',array('Active'=>'1','Resigned'=>'2','Retired'=>'3','In Active'=>'4','AWOL'=>'5','Casual'=>'6'),$paymast['emp_status']);?>
      </font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Date Employed 
      </font></td>
    <td colspan="2"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <input name="date_employ" type="text" id="date_employ" value="<?= ymd2mdy($paymast['date_employ']);?>" size="12" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')"  onKeypress="if(event.keyCode==13) {document.getElementById('tenureallowance').focus();return false;}" >
      <img src="../graphics/dwn-arrow-grn.gif" width="12" height="12" onClick="popUpCalendar(this, f1.date_employ, 'mm/dd/yyyy')"> 
      </font><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
      </font></td>
  </tr>
  <tr bgcolor="#EFEFEF"> 
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Branch</font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= lookUpTable2('branch_id','branch','branch_id','branch',$paymast['branch_id']);?>
      </font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Salary Level</font></td>
    <td colspan="2"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
    <select name="level_id" id="level_id" onChange="xajax_paymastLevel(xajax.getFormValues('f1'))">
	<option value='0'>Salary Level</option>
	<?
		$qq="select * from level where enable='Y' order by level";
		$qqr = @pg_query($qq);
		while ($rr = @pg_fetch_object($qqr))
		{
			if ($paymast['level_id'] == $rr->level_id)
			{
				echo "<option value='$rr->level_id' selected>$rr->level</option>";
			}
			else
			{
				echo "<option value='$rr->level_id'>$rr->level</option>";
			}
			
		}
	?>
	</select>
      </font></td>
  </tr>
  <tr bgcolor="#EFEFEF"> 
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Department</font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= lookUpTable2('department_id','department','department_id','department',$paymast['department_id']);?>
      </font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Tenure Allowance</font></td>
    <td colspan="2"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <input name="tenureallowance" type="text" id="tenureallowance" value="<?= $paymast['tenureallowance'];?>" size="15" style="text-align:right"   onKeypress="if(event.keyCode==13) {document.getElementById('ratem').focus();return false;}">
      <?= lookUpAssoc("tenurew",array("None"=>'0',"Daily"=>"D","Monthly"=>"M"),$paymast['tenurew']);?>
      </font></td>
  </tr>
  <tr bgcolor="#EFEFEF"> 
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Section</font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= lookUpTable2('section_id','section','section_id','section',$paymast['section_id']);?>
      </font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Pay Category 
      </font></td>
    <td colspan="2"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <?= lookUpAssoc('pay_category',array('Regular Monthly'=>'1','Regular Daily'=>'2','ProB Monthly'=>'3','ProB Daily'=>'4','Contractual'=>'5','Daily Casual'=>'6'),$paymast['pay_category']);?>
      </font></td>
  </tr>
  <tr bgcolor="#EFEFEF"> 
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Postition</font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <input name="position" type="text" id="position" value="<?= $paymast['position'];?>" size="30">
      </font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Monthly Rate</font></td>
    <td colspan="2"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <input name="ratem" type="text" id="ratem" value="<?= $paymast['ratem'];?>" size="15" onBlur="vComputeSalary()" style="text-align:right"   onKeypress="if(event.keyCode==13) {document.getElementById('adwr').focus();return false;}">
      </font></td>
  </tr>
  <tr bgcolor="#EFEFEF"> 
    <td valign="top"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Rank</font></td>
    <td> 
      <?= lookUpAssoc('rank',array('Rank & File'=>'R', 'Supervisor'=>'S','BOD'=>'B','Resigned'=>'D'), $paymast['rank']);?>
    </td>
    <td valign="top"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Average 
      Daily Wage</font></td>
    <td colspan="2" valign="top"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <input name="adwr" type="text" id="adwr" value="<?= $paymast['adwr'];?>" size="15" style="text-align:right"   onKeypress="if(event.keyCode==13) {document.getElementById('hourly').focus();return false;}" onBlur="vAdwr(this)">
      </font></td>
  </tr>
  <tr bgcolor="#EFEFEF"> 
    <td>&nbsp;</td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; </font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Hourly Rate 
      </font></td>
    <td colspan="2"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <input name="hourly" type="text" id="hourly" value="<?= $paymast['hourly'];?>" size="15"  style="text-align:right"   onKeypress="if(event.keyCode==13) {document.getElementById('fixed_sss').focus();return false;}">
      </font></td>
  </tr>
  <tr bgcolor="#EFEFEF"> 
    <td>&nbsp;</td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; </font></td>
    <td bgcolor="#DADADA"><font color="#000066" size="2" face="Verdana, Arial, Helvetica, sans-serif">Monthly 
      Fixed Deductions</font></td>
    <td bgcolor="#DADADA"><font color="#000066" size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; 
      Employee</font></td>
    <td bgcolor="#DADADA"><font color="#000066" size="2" face="Verdana, Arial, Helvetica, sans-serif">Employer</font></td>
  </tr>
  <tr bgcolor="#EFEFEF"> 
    <td>&nbsp;</td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; </font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Fixed SSS 
      Deduction </font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <input name="fixed_sss" type="text" id="fixed_sss" value="<?= $paymast['fixed_sss'];?>" size="15"  style="text-align:right"   onKeypress="if(event.keyCode==13) {document.getElementById('fixed_ssse').focus();return false;}">
      </font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <input name="fixed_ssse" type="text" id="fixed_ssse" value="<?= $paymast['fixed_ssse'];?>" size="15"  style="text-align:right"   onKeypress="if(event.keyCode==13) {document.getElementById('fixed_phic').focus();return false;}">
      </font></td>
  </tr>
  <tr bgcolor="#EFEFEF"> 
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Fixed PHIC 
      Deduction </font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <input name="fixed_phic" type="text" id="fixed_phic" value="<?= $paymast['fixed_phic'];?>" size="15"  style="text-align:right"   onKeypress="if(event.keyCode==13) {document.getElementById('fixed_phice').focus();return false;}">
      </font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <input name="fixed_phice" type="text" id="fixed_phice" value="<?= $paymast['fixed_phice'];?>" size="15"  style="text-align:right"   onKeypress="if(event.keyCode==13) {document.getElementById('fixed_wtax').focus();return false;}">
      </font></td>
  </tr>
  <tr bgcolor="#EFEFEF"> 
    <td>&nbsp;</td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">&nbsp; </font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Fixed WTax 
      Deduction</font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <input name="fixed_wtax" type="text" id="fixed_wtax" value="<?= $paymast['fixed_wtax'];?>" size="15"  style="text-align:right"   onKeypress="if(event.keyCode==13) {document.getElementById('fixed_wtaxe').focus();return false;}">
      </font></td>
    <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <input name="fixed_wtaxe" type="text" id="fixed_wtaxe" value="<?= $paymast['fixed_wtaxe'];?>" size="15"  style="text-align:right"   onKeypress="if(event.keyCode==13) {document.getElementById('fixed_pagibig').focus();return false;}">
      </font></td>
  </tr>
  <tr bgcolor="#EFEFEF"> 
    <td valign="top">&nbsp;</td>
    <td>&nbsp;</td>
    <td valign="top"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Fixed 
      Pag-Ibig Deduction</font></td>
    <td valign="top"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <input name="fixed_pagibig" type="text" id="fixed_pagibig" value="<?= $paymast['fixed_pagibig'];?>" size="15"  style="text-align:right"   onKeypress="if(event.keyCode==13) {document.getElementById('fixed_pagibige').focus();return false;}">
      </font></td>
    <td valign="top"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
      <input name="fixed_pagibige" type="text" id="fixed_pagibige" value="<?= $paymast['fixed_pagibige'];?>" size="15"  style="text-align:right"   onKeypress="if(event.keyCode==13) {document.getElementById('date_employed').focus();return false;}">
      </font></td>
  </tr>
  <tr bgcolor="#EFEFEF"> 
    <td valign="top">&nbsp;</td>
    <td>&nbsp;</td>
    <td colspan="3" valign="top"><font size="1" face="Times New Roman, Times, serif"><em>*Fixed 
      Deductions will NOT follow deduction table. Leave blank if table is used.</em></font></td>
  </tr>
</table>
