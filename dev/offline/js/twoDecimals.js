function twoDecimals(theNum)
{
var result = "";
var fpString = theNum + "";
var decimalAt = fpString.indexOf(".");

if (decimalAt == -1)
{
	result = fpString + ".00";
}
else
{
	var frac = fpString.substring(decimalAt);
	frac = parseFloat(frac) * 100;
	frac = Math.round(frac);
	var wholeNum = fpString.substring(0,decimalAt);
	result = wholeNum + "."+frac;
}

return result;
}