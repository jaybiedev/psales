// ********************************************************************
// Copyright (C) 2001 Microsoft Corporation. All rights reserved.
//
//	IMPORTANT.  Please read the legal.txt file, located in the 
//	"<CMS Install Directory>\Server\httpexec\WBC\Internals" 
//	directory, governing the use of this internal file."
// ********************************************************************

// Define constants
var IDS_FRAMEWORK_VIRTUAL_PATH = "/NR/System/WBC";	// also defined in CommonServer.inc
var IDS_INTLINKS_PATH = IDS_FRAMEWORK_VIRTUAL_PATH + "/Internals/WBCFuncPages/Cross-Browser/InternalLinks/InternalLinks.asp";
var IDS_HTTP_PROTOCOL = "http://";	// also defined in CommonServer.inc

// Define querystring parameters - all are also defined in CommonServer.inc
var IDS_QPARAM_ACCEPT = "wbc_acceptcontent";
var IDS_QPARAM_ATTACHICON = "wbc_attachicon";
var IDS_QPARAM_DESKRES = "wbc_deskres";
var IDS_QPARAM_IMG = "wbc_img";
var IDS_QPARAM_IMGNAME = "wbc_imgname";
var IDS_QPARAM_NREMIT = "nr_emit";

var IDS_QPARAM_PHNAME = "wbc_phname";
var IDS_QPARAM_PHTYPE = "wbc_phtype";
var IDS_QPARAM_URL = "wbc_url";
var IDS_QPARAM_VIDEO = "wbc_video";
var IDS_QPARAM_WBCPURPOSE = "wbc_purpose";
var IDS_QPARAM_WBCACTION = "wbc_action";
var IDS_QPARAM_REFRESHTREE = "FreshTree";
// Define specific placeholder type variables
var IDS_PLACEHOLDER_DHTML = "dhtml"
var IDS_PLACEHOLDER_ACTIVEX = "activeX"
var IDS_NCPHNAME = "NCPHNAME"
var IDS_NCPH_ = "NCPH_" 
var IDS_PLACEHOLDER_TYPE = "placeholderType"

// Define querystring values - all are also defined in CommonServer.inc
var IDS_ACTION_APPROVE = "Approve";
var IDS_ACTION_DECLINE = "Decline"
var IDS_ACTION_SAVE = "Save";
var IDS_ACTION_SAVEANDEXIT = "SaveAndExit";
var IDS_ACTION_SUBMIT = "Submit";
var IDS_PHTYPE_MULTIPURPOSE = "MultiPurpose";
var IDS_PHTYPE_MULTILINETEXT = "MultiLineText";
var IDS_PHTYPE_SINGLEATTACHMENT = "SingleAttachment";
var IDS_PHTYPE_SINGLEIMAGE = "SingleImage";
var IDS_PHTYPE_SINGLELINETEXT = "SingleLineText";
var IDS_PHTYPE_THINEDITIE = "ThinEditIE";
var IDS_RESTYPE_ATTACHMENT = "Attachment";
var IDS_RESTYPE_IMAGE = "Image";
var IDS_RESTYPE_VIDEO = "Video";


// This function checks for the existence of a function in the specified
// window. It returns the window or frame where the function is defined. 
// If no requested function is found, it will return null.

function WBC_getFuncWin(strFunc, pTargetWin) {
	if (pTargetWin) {
		// try the window itself first
		if (eval("pTargetWin." + strFunc) != null) 
		{
			return pTargetWin;
		}
		// if not found, look into the children frames
		for (i=0; i < pTargetWin.top.frames.length; i++) 
		{
			if (eval("pTargetWin.top.frames[i]." + strFunc) != null) 
			{
				return pTargetWin.top.frames[i];
			}
		}
	}
	return null;
}



// [PUBLIC] Make main WBC window go to a particular Posting
function WBC_onSelectGoto( strPostingURL )
{
	if ( WBC_isOpenerWindowClosed() )
	{
		window.open(strPostingURL, "WBC_wndMain", IDS_WIN_MAIN_WBC_FEATURES);
	}
	else
	{
		window.top.opener.top.location = strPostingURL;
	}
	window.top.close();
}

// submit a form using the specified action URL
function WBC_submitForm( strFormName, strFormActionURL ) {
	document.forms[strFormName].action = strFormActionURL;
	document.forms[strFormName].submit();
}


// Preload images for rollovers. Arguments are name of all rollovering
// images to be preloaded.

function WBC_preloadImages () {
	if (document.images) {
		var arrayPreload = new Array();
		for (var i = 0; i < arguments.length; i++) {
			arrayPreload[i] = new Image;
			arrayPreload[i].src = arguments[i];
		}
	}
}

// for supporting Rollover image
function WBC_changeImgSafe(imgDOM, imgSrcReplacedBy)
{
	// brace for the case that there is no image tag 
	// when people use text as link
	if (document.images[imgDOM] != null)
	{
		document.images[imgDOM].src = imgSrcReplacedBy;
	}
}

// take user to an authoring page based on the selected template URL
function WBC_createFromTmpl(strURL) {
	if (top.opener != null) {
		top.opener.top.location.href = strURL;
		top.close();
	} else {
		alert(IDS_WINDOW_OPENER_NOT_FOUND);
	}
}


// Checks if browser is IE
function WBC_isIE() {
	if (navigator.appName.indexOf("Microsoft") != -1) {
		return true;
	} else {
		return false;
	}
}

// Checks if browser is Netscape
function WBC_isNetscape() {
	if (navigator.appName.indexOf("Netscape") != -1) {
		return true;
	} else {
		return false;
	}
}

// Returns browser version
function WBC_getBrowserVer() {
	if (!WBC_isNetscape()) {
		var strUserAgent = navigator.userAgent;
		var nBrowserUserAgentStart = strUserAgent.indexOf("(");
		if (nBrowserUserAgentStart != -1) {
			var strBrowserUserAgent = strUserAgent.substring(nBrowserUserAgentStart)
			var nBrowserVerStart = strBrowserUserAgent.indexOf(".") - 1;
			while ( !isNaN(parseInt(strBrowserUserAgent.charAt(nBrowserVerStart))) && (nBrowserVerStart > 0)) {
				nBrowserVerStart = nBrowserVerStart - 1;
			}
			return parseInt(strBrowserUserAgent.substring(nBrowserVerStart));
		}
	}
	return parseInt(navigator.appVersion);
}



function WM_netscapeCssFix() {
  /*
    Source: Webmonkey Code Library
    (http://www.hotwired.com/webmonkey/javascript/code_library/)

    Author: Taylor
    Author Email: taylor@wired.com
    Author URL: http://www.taylor.org/
    */

  // This part was inspired by Matthew_Baird@wayfarer.com
  // It gets around another unfortunate bug whereby Netscape 
  // fires a resize event when the scrollbars pop up. This 
  // checks to make sure that the window's available size 
  // has actually changed.
  if (document.WM.WM_netscapeCssFix.initWindowWidth != window.innerWidth || document.WM.WM_netscapeCssFix.initWindowHeight != window.innerHeight) {
    document.location = document.location;
  }
}

function WM_netscapeCssFixCheckIn() {
  // This function checks to make sure the version of Netscape 
  // in use contains the bug; if so, it records the window's 
  // width and height and sets all resize events to be handled 
  // by the WM_netscapeCssFix() function.
  if ((navigator.appName == 'Netscape') && (parseInt(navigator.appVersion) == 4)) {
    if (typeof document.WM == 'undefined'){
      document.WM = new Object;
    }
    if (typeof document.WM.WM_scaleFont == 'undefined') {
      document.WM.WM_netscapeCssFix = new Object;
      document.WM.WM_netscapeCssFix.initWindowWidth = window.innerWidth;
      document.WM.WM_netscapeCssFix.initWindowHeight = window.innerHeight;
    }
    window.onresize = WM_netscapeCssFix;
  }
}

WM_netscapeCssFixCheckIn();

//  Get the element of the specified name in the form
//	Get Form Element objects and populate them into arrayElements.
//	IE4.5 (Mac) has a bug that if there are more than one set of radio buttons,
//	accessing the second set and thereafter directly by name will fail.
//	The radio buttons form elements will therefore be retrieved using arrayElements
//	rather than directly from the form elements collection.
function WBC_getFormElement( pForm, strName, arrayElements ) 
{
	var j = 0;
	for (var i = 0; i < pForm.elements.length; i++) {
		if (pForm.elements[i].name == strName) {
			arrayElements[j] = pForm.elements[i];
			j++;
		}
	}
}



//	Verify name to see if its empty, all blanks or contain illegal characters
//	such as #, &, +, %, /, | and empty string.
function WBC_verifyInputName(strName) {
	if ((strName.value.split(" ").length == strName.value.length + 1) || (strName.value.indexOf("#") >= 0) || (strName.value.indexOf("&") >= 0) || (strName.value.indexOf("%") >= 0) || (strName.value.indexOf("+") >= 0) || (strName.value.indexOf("/") >= 0) || (strName.value.indexOf("|") >= 0)) {
		return false;
	} else {
		return true;
	}
}

