// ********************************************************************
// Copyright (C) 2001 Microsoft Corporation. All rights reserved.
//
//	IMPORTANT.  Please read the legal.txt file, located in the 
//	"<CMS Install Directory>\Server\httpexec\WBC\Internals" 
//	directory, governing the use of this internal file."
// ********************************************************************
//	This file provides all the windows related helper function such as
//	calculating the window size, pop up a new windows.

var IDS_WIDTH_OFFSET = 30;
var IDS_HEIGHT_OFFSET = 30;


// ---- [Public] Returns whether the Opener browser window is closed or not
function WBC_isOpenerWindowClosed()
{
	// The implementation doesn't seem to work on all browsers!
	return !(window.top.opener.top && !window.top.opener.top.closed);
}


// ---- [Public] Open a new window and focus it
function WBC_openWindowOnFocus(strURL, strWinTarget, strWinFeatures) 
{

	var pWindow = window.top.open(strURL, strWinTarget, strWinFeatures);
	if (pWindow)
		pWindow.focus();
}


// ---- [Public] refresh the current window
function WBC_refreshWindow()
{
	window.top.history.go(0);
}



// ---- [Public] Return default features if input feature is ""
function WBC_getPoppedUpWindowFeatures(strWindowFeature, pWindowBasedOn)
{
	var strFeature; 
	
	if (strWindowFeature == "")
	{
		strFeature = WBC_UseDefaultSizing(pWindowBasedOn);
	}
	else 
	{
		strFeature = strWindowFeature;
	}
	return strFeature;
}	



// ---- [Public] Return default window size 
function WBC_UseDefaultSizing(pWindow)
{
	var	lWidth = WBC_getWindowWidth(pWindow);
	var lHeight = WBC_getWindowHeight(pWindow);
	var strFeature;
	var lLeft;
	var lTop;

	if (WBC_isIE())
	{
		// IE case
		lTop = pWindow.screenTop + IDS_HEIGHT_OFFSET;
		lLeft = pWindow.screenLeft + IDS_WIDTH_OFFSET;
		
		strFeature = "left=" + lLeft + ",top=" + lTop + ",width=" + lWidth + ",height=" + lHeight + ",resizable,scrollbars,status=yes";
	}
	else
	{
		// Netscape case	
		lTop = pWindow.screenY + IDS_HEIGHT_OFFSET;
		lLeft = pWindow.screenX + IDS_WIDTH_OFFSET;
		strFeature = "screenX=" + lLeft + ",screenY=" + lTop + ",outerWidth=" + lWidth + ",outerHeight=" + lHeight + ",resizable,scrollbars,status=yes";
	}

	return strFeature;
}

// ---- [Private] get the window width
function WBC_getWindowWidth(pWin)
{
	if (WBC_isIE()) 
	{
		return pWin.document.body.clientWidth;
	} 
	else if (WBC_isNetscape()) 
	{
		return pWin.outerWidth;
	}
}

// ---- [Private] get the window height
function WBC_getWindowHeight(pWin)
{
	if (WBC_isIE()) 
	{
		return pWin.document.body.clientHeight;
	} 
	else if (WBC_isNetscape()) 
	{
		return pWin.outerHeight;
	}
}


