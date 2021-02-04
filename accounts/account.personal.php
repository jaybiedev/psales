<div id="LayerPersonal" style="position:virtual; width:100%; height:100%; z-index:1; display:block"> 
  <table width="100%" border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#EFEFEF">
    <tr> 
      <td nowrap><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Name 
        on the card</font></td>
      <td><input name="cardname" type="text" id="cardname" onFocus="nextfield ='telefax'" value="<?= stripslashes($aaccount['cardname']);?>" size="40" maxlength="25"  onKeypress="if(event.keyCode==13) {document.getElementById('date_birth').focus();return false;}"></td>
      <td colspan="1" rowspan="12" align="center"><img  name="pix" id="pix" src="../images/<?= $aaccount['pix'];?>" width="250" height="250"><br> 
        <input type="file" id="pixfile" name="pixfile" size="1" onBlur="vPix()" value="<?=$aaccount['pixfile'];?>"  tabindex="<?= count($fields)+10;?>" class="hideTextFormat"> 
      </td>
    </tr>
    <tr> 
      <td nowrap width="130"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Date 
        of Birth</font></td>
      <td width="344"> <input name="date_birth" type="text" id="date_birth" value="<?= ymd2mdy($aaccount['date_birth']);?>" size="12" maxlength="12" onBlur="IsValidDate(this,'MM/dd/yyyy')" onKeyUp="setDate(this,'MM/dd/yyyy','en')"  onKeypress="if(event.keyCode==13) {document.getElementById('address').focus();return false;}"> 
        <font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif"><img src="../graphics/arrow_down.jpg" onClick="popUpCalendar(this, f1.date_birth, 'mm/dd/yyyy')"></font> 
      </td>
    </tr>
    <tr> 
      <td valign="top" nowrap width="130"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Gender</font></td>
      <td width="344"> 
        <?= lookUpAssoc('gender',array('Male'=>'M','Female'=>'F'),$aaccount['gender']);?>
      </td>
    </tr>
    <tr> 
      <td valign="top" nowrap width="130"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Status</font></td>
      <td width="344"> 
        <?= lookUpAssoc('civil_status',array('Single'=>'S','Married'=>'M','Widowed'=>'W','Single Parent'=>'I'),$aaccount['civil_status']);?>
      </td>
    </tr>
    <tr> 
      <td nowrap valign="top" width="130"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Address</font></td>
      <td valign="top" width="344"> <textarea name="address" cols="40" rows="2" id="address"  onFocus="nextfield ='telno'"  onKeypress="if(event.keyCode==13) {document.getElementById('telno').focus();return false;}"><?= $aaccount['address'];?></textarea> 
      </td>
    </tr>
    <tr> 
      <td valign="top" nowrap width="130"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Telephone</font></td>
      <td width="344"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input name="telno" type="text" id="telno" value="<?= $aaccount['telno'];?>" size="15" maxlength="15" onFocus="nextfield ='guarantor_id'"  onKeypress="if(event.keyCode==13) {document.getElementById('spouse').focus();return false;}">
        <input name="telefax" type="hidden" id="telefax" value="<?= $aaccount['telefax'];?>" size="15" maxlength="15" onFocus="nextfield ='telno'">
        </font> </td>
    </tr>
    <tr> 
      <td valign="top" nowrap width="130"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Spouse</font></td>
      <td width="344"> <input type="text" name="spouse" id="spouse" size="30" value="<?= $aaccount['spouse'];?>"  onKeypress="if(event.keyCode==13) {document.getElementById('no_dependent').focus();return false;}"> 
      </td>
    </tr>
    <tr> 
      <td valign="top" nowrap width="130"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">No. 
        of Dependents</font></td>
      <td width="344"> <font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input type="text" name="no_dependent" id="no_dependent"  style="text-align:right" size="10" value="<?= $aaccount['no_dependent'];?>"  onKeypress="if(event.keyCode==13) {document.getElementById('salary').focus();return false;}">
        </font> </td>
    </tr>
    <tr> 
      <td valign="top" nowrap width="130"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Monthly 
        Salary</font></td>
      <td width="344"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <input type="text" name="salary" id="salary" size="10" value="<?= $aaccount['salary'];?>"  style="text-align:right" onKeypress="if(event.keyCode==13) {document.getElementById('occupation').focus();return false;}">
        </font> </td>
    </tr>
    <tr> 
      <td valign="top" nowrap width="130"><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Occupation</font></td>
      <td width="344"> <input type="text" name="occupation" id="occupation" size="30" value="<?= $aaccount['occupation'];?>"  onKeypress="if(event.keyCode==13) {document.getElementById('employer').focus();return false;}"> 
      </td>
    </tr>
    <tr> 
      <td valign="top" nowrap width="130"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Employer</font></td>
      <td width="344"> <input type="text" name="employer" id="employer" size="30" value="<?= $aaccount['employer'];?>"  onKeypress="if(event.keyCode==13) {document.getElementById('remarks').focus();return false;}"> 
      </td>
    </tr>
    <tr> 
      <td valign="top" nowrap width="130"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Remarks</font></td>
      <td valign="top" width="344"> <textarea name="remarks" cols="40" rows="2" id="remarks"  onKeypress="if(event.keyCode==13) {document.getElementById('Save').focus();return false;}"><?= $aaccount['remarks'];?></textarea> 
      </td>
    </tr>
    <tr> 
      <td valign="top" nowrap width="130"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Enabled</font></td>
      <td colspan="3"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"> 
        <?=lookUpAssoc('enable',array('Yes'=>'Y','No'=>'N'),$aaccount['enable']);?>
        </font></td>
    </tr>
  </table>
</div>
