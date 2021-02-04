function printSpecial()
{
// using this function, use <div id='printReady'>.  The data within the <div> will be printed
	if (document.getElementById != null)
	{
		var html='';
		var printReadyElem = document.getElementById("printReady");
		
		if (printReadyElem != null)
		{
				html += printReadyElem.innerHTML;
		}
		else
		{
			alert("Could not find the printReady section in the HTML");
			return;
		}
			
		var printWin = window.open("","printVersion","scrollbar=yes, location=no");
		printWin.document.open();
		printWin.document.write(html);
		printWin.document.close();
		printWin.print();
			
	}
	else
	{
		alert("Sorry, the print ready feature is only available in modern browsers.");
	}
}

function printIframe(taName) {
//using this function create an invisible iframe within your page
//<iframe name="printit" id="printit" style="width:0px;height:0px;"></iframe>
//      or you can change printScript = frames['printit'] with printScript=window.open("","printVersion","scriollbar=yes, location=no")
//      instead of using iframe
//taName is the object of a textarea you wish to print
var printItElem = document.getElementById("printit");

if (printItElem == null)
{
	alert("Could not find the printReady section in the HTML");
	return;
}
var printScript = frames['printit'];
printScript.document.open();
printScript.document.write('<pre>'+taName.value+'</pre>');
printScript.document.close();
printScript.focus();
printScript.print();

}

