<script>
<!--
function va(sid)
{
	document.getElementById('browsePLULayer').style.display='none';
	//xajax_porder_supplier_select_id(sid);
	return;
}
//-->
</script>
<title>Select Supplier Account</title>
<div id="browseLayer"  name="browseLayer" style="position:absolute; width:600px; height:380px; z-index:1; left: 5%; top: 35%;display:none"> 
  <table width="100%" height="100%" border="0" align="center" cellpadding="0" cellspacing="0" background="../graphics/table_horizontal.PNG">
    <tr height="1%"> 
      <td width="1%" background="../graphics/table_left.PNG" ></td>
      <td width="48%"  height="25" background="../graphics/table_horizontal.PNG"><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><b>Select 
        Account </b></font></td>
      <td width="49%"  align="right" background="../graphics/table_horizontal.PNG"> 
        <img src="../graphics/table_x.PNG" onClick="document.getElementById('browseLayer').style.display='none'" ></td>
      <td  width="1%" align="right" background="../graphics/table_right.PNG"  valign="top" ></td>
    </tr>
    <tr valign="top" bgcolor="#A4B9DB" > 
      <td colspan="4" height="95%"> 
       <div id="innerLayer"  name="innerLayer" style="position:virtual; width:100%; height:100%; z-index:2; left: 0; top: 0; overflow:auto;"> 
        </div>
      </td>
    </tr>
    <tr> 
      <td colspan="4" height="3"  background="../graphics/table0_vertical.PNG">
      </td>
    </tr>
  </table>
</div>