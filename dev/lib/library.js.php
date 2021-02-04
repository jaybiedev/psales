<script language="javascript" src="../js/setdatetime.js"></script>
<script language="javascript" src="../js/checkdate.js" type="text/javascript"></script>
<script language="javascript" src="../js/animate.js" type="text/javascript"></script>
<script language="javascript" src="../js/popcalendar.js" type="text/javascript"></script>
<script language="javascript" src="../js/twoDecimals.js" type="text/javascript"></script>
<script language="javascript" src="../js/printVersion.js" type="text/javascript"></script>

<script language="javascript">

<!--
function hideElement( elmID, overDiv )
{
	if( ie )
	{
		for( i = 0; i < document.all.tags( elmID ).length; i++ )
		{
  			obj = document.all.tags( elmID )[i];
  			if( !obj || !obj.offsetParent )
  			{
    				continue;
  			}
			  // Find the element's offsetTop and offsetLeft relative to the BODY tag.
			  objLeft   = obj.offsetLeft;
			  objTop    = obj.offsetTop;
			  objParent = obj.offsetParent;
			  while( objParent.tagName.toUpperCase() != "BODY" )
			  {
			    objLeft  += objParent.offsetLeft;
			    objTop   += objParent.offsetTop;
			    objParent = objParent.offsetParent;
			  }
			  objHeight = obj.offsetHeight;
			  objWidth = obj.offsetWidth;
			  if(( overDiv.offsetLeft + overDiv.offsetWidth ) <= objLeft );
			  else if(( overDiv.offsetTop + overDiv.offsetHeight ) <= objTop );
					/* CHANGE by Charlie Roche for nested TDs*/
			  else if( overDiv.offsetTop >= ( objTop + objHeight + obj.height ));
					/* END CHANGE */
			  else if( overDiv.offsetLeft >= ( objLeft + objWidth ));
			  else
			  {
			    obj.style.visibility = "hidden";
			  }
		}
	}
}
     
//unhides <select> and <applet> objects (for IE only)
function showElement( elmID ){
	if( ie ){
		for( i = 0; i < document.all.tags( elmID ).length; i++ ){
  			obj = document.all.tags( elmID )[i];
			if( !obj || !obj.offsetParent ){
    				continue;
			}
			obj.style.visibility = "";
		}
	}
}
-->	
</script>

