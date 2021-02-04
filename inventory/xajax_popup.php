<script>
<!--
function hW()
{
	document.getElementById('browsePLULayer').style.display='none';
	return;
}

document.onkeydown = keyhandler;

var	isNS = (navigator.appName	== "Netscape") ? 1 : 0;

function keyhandler(e) 
{
	var myevent = (isNS) ? e : window.event;
	var mycode=myevent.keyCode
	var focus_id=document.getElementById('line_no').value;

	if (focus_id == '')
	{
		focus_id =1;
	}

	if (document.getElementById('browsePLULayer').style.display != 'block')
	{
		return;
	}
	if (mycode == 13)
	{
		focus_fld = 'L'+focus_id;
		b_focus_fld = 'M'+focus_id;
		if (focus_id != '')
		{
			document.getElementById(b_focus_fld).click();
		}
		return false;		
	}
	else if (mycode == 27)
	{
		document.getElementById('browsePLULayer').style.display='none';
	}
	else if (mycode == 38) //&& if layer is shown
	{
		//scroll up
		if (focus_id == '')
		{
			focus_id =1;
		}
		else
		{
			previous_line = focus_id;
			focus_fld = 'L'+focus_id;

			document.getElementById(focus_fld).style.background='';
			document.getElementById(focus_fld).style.color='';
			
			focus_id--;
			if (focus_id<1) focus_id=1;
		}
		//new
		focus_fld = 'L'+focus_id
		if (document.getElementById(focus_fld) == null)
		{
			focus_id--
			focus_fld = 'L'+focus_id
		}
	
		document.getElementById(focus_fld).style.background='#000CCC';
		document.getElementById(focus_fld).style.color='#FFFFFF';
		document.getElementById(focus_fld).scrollIntoView(false);
		document.getElementById('line_no').value = focus_id;
	}
	else if (mycode == 40) //&& if layer exists
	{
		//scroll down
		if (focus_id == '')
		{
			focus_id =1;
		}
		else
		{
			previous_line = focus_id;
			focus_fld = 'L'+focus_id;
			document.getElementById(focus_fld).style.color='';
			document.getElementById(focus_fld).style.background='';
			focus_id++;
		}
		//new
		focus_fld = 'L'+focus_id
		if (document.getElementById(focus_fld) == null)
		{
			focus_id--
			focus_fld = 'L'+focus_id
		}
		document.getElementById(focus_fld).style.background='#000CCC';
		document.getElementById(focus_fld).style.color='#FFFFFF';
		document.getElementById('line_no').value = focus_id;
		document.getElementById(focus_fld).scrollIntoView(false);
		
	}
}

//-->
</script>
<style>
 .gridRow
 {
// 	white-space:pre;
 	font-family:Verdana;
 	font-weight:normal;
 	font-size:12px;
  } 
 .gridRow:hover
 {
 	background-color:#FFFFCC;
 	color:#000000;
 }
 .gridbutton
 {
 	background-color:#DADADA;
	font-family: verdana;
	border: none;
	padding: 0px;
	margin: 0px;
	font-size: 1px;
	color: none;
	
 }
</style>
<div id="browsePLULayer" name="browsePLULayer"  style="position:absolute; width:700px; height:350px; z-index:1; left: 5%; top:20%; display:none;"> 
  <table width="100%" height="100%" border="0" align="center" cellpadding="0" cellspacing="0" >
    <tr height="1%"> 
      <td width="1%" background="../graphics/table_left.PNG" ></td>
      <td width="48%"  height="25" background="../graphics/table_horizontal.PNG"><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><b>Select 
        from List<input type="hidden" id="line_no" name="line_no" value=""></b></font></td>
      <td width="49%"  align="right" background="../graphics/table_horizontal.PNG"> 
        <img src="../graphics/table_x.PNG" onClick="document.getElementById('browsePLULayer').style.display='none'" ></td>
      <td  width="1%" align="right" background="../graphics/table_right.PNG"  valign="top" ></td>
    </tr>
    <tr valign="top" bgcolor="#A4B9DB"> 
      <td colspan="4" height="300px"> 
       <div id="innerPLULayer" name="innerPLULayer" style="position:virtual; width:100%; height:100%; z-index:2; left: 0; top: 0; overflow: auto;">
		</div>
		</td>
    </tr>
    <tr> 
      <td colspan="4" height="3"  background="../graphics/table_horizontal.PNG">
      </td>
    </tr>
  </table>
  </div>