<script>
<!--
function vs(sid, return_function, return_focus)
{
	document.getElementById('browsePLULayer').style.display='none';
	xajax_pc_select_id(sid, return_function, return_focus);
	return;
}

//-->
</script>
<div id="browsePLULayer" name="browsePLULayer"  style="position:absolute; width:500px; height:299px; z-index:1; left: 5%; top:20%; display:none;"> 
  <table width="100%" height="100%" border="0" align="center" cellpadding="0" cellspacing="0" background="../graphics/table0_horizontal.PNG">
    <tr> 
      <td width="1%"><img src="../graphics/table0_upper_left.PNG" width="6" height="31"></td>
      <td width="48%"  height="31"align="left" background="../graphics/table0_horizontal.PNG"><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><b>Select 
        Product</b></font></td>
      <td width="49%" height="31" align="right" background="../graphics/table0_horizontal.PNG">
	  <img src="../graphics/table_close.PNG" onClick="document.getElementById('browsePLULayer').style.display='none'" ></td>
      <td width="2%" align="right"><img src="../graphics/table0_upper_right.PNG" width="6" height="30"></td>
    </tr>
    <tr valign="top" bgcolor="#A4B9DB"> 
      <td colspan="4" height="300px"> 
       <div id="innerPLULayer" name="innerPLULayer" style="position:virtual; width:100%; height:100%; z-index:2; left: 0; top: 0; overflow: auto;">
		</div>
		</td>
    </tr>
    <tr> 
      <td colspan="4" height="3"  background="../graphics/table0_vertical.PNG">
      </td>
    </tr>
  </table>
  </div>