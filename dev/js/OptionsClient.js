// ********************************************************************
// Copyright (C) 2001 Microsoft Corporation. All rights reserved.
//
//	IMPORTANT.  Please read the legal.txt file, located in the 
//	"<CMS Install Directory>\Server\httpexec\WBC\Internals" 
//	directory, governing the use of this internal file."
// ********************************************************************


// ********************************************************************
//	This file provides all the window feature contants used by all WBC
//	dialogs. There are 2 types of dialogs: Fixed Size and Dynamic Size.
//
//	1) Fixed Size dialog:
//			Non-empty window features string will be interpreted as Fixed Size 
//			dialogs. These constants contain value such as "width=200,height=100". 
//			This means when the UI of a specified feature popup, the pop up
//			windows's feature will be based on the constant specified.
//			Example:  IDS_WIN_ATTACHPROPS_FEATURES 
//			
//			If user wants to have attachment properties windows to have size of
//			width="100" and height = "100". He would need to change the constants of 
//			IDS_WIN_ATTACHPROPS_FEATURES.
//			Example: IDS_WIN_ATTACHPROPS_FEATURE="width=100,height=100"
//
//	2) Dynamic Size dialog:
//			Empty window features string will be interpreted as Dynamic Size dialogs.
//			in this case, the pop up window will use the calculation 
//			rountine written in Windows.js to determine the window's feature.
//				 
//			The position calculation is based on the pop up windows'parent size and will
//			be 30 pixels off the original caller, as long as the pop up window can
//			fill up the whole screen.  If not, the pop up will place exactly on top
//			of the caller
//
//			To change the size of the pop up constants to your customized one, 
//			just modified the constant (e.g. IDS_WIN_PRODUCTIONMANAGER_FEATURES)
//			to carry your specified value.
//			Example: To change the pop up size of Production Manager from
//			          dynamic to customized window of width=100,height=100
//					  You will need to change IDS_WIN_PRODUCTIONMANAGER_FEATURES from 
//					  IDS_WIN_PRODUCTIONMANAGER_FEATURES="width=100,height=100". This
//					  will override the default 30 pixel off caller feature
//
//
//	Note: the behavior described above is a result of the implementation
//			of WBC_getPoppedUpWindowFeatures() in windows.js
//
// ********************************************************************



// IE Modal/Modeless dialog will use this string
var IDS_IE_DIALOG_DEFAULT_POSITION = ",dialogTop=50,dialogWidth=300"

// IE/Netscape dialog will use this string 
var IDS_DEFAULT_OPEN_POSITION = ",top=50,left=300,screenY=50,screenX=300"


// ----------------------------------------------------------------------------
//
//	WBC dialogs browser window features (FIXED SIZE)
//
// ----------------------------------------------------------------------------

// the main WBC window (empty string means full features)
var IDS_WIN_MAIN_WBC_FEATURES = "";

// the window popped up for re-login during WBC Authoring 
var IDS_WIN_SESSION_TIMEOUT_LOGIN = "WBC_wndSessionTimeoutLogin";
var IDS_WIN_SESSION_TIMEOUT_LOGIN_FEATURES = "width=450,height=500,resizable" + IDS_DEFAULT_OPEN_POSITION;

var IDS_WIN_TABLE = "WBC_wndTable";
var IDS_WIN_TABLE_FEATURES = "width=320,height=190,resizable,scrollbars" + IDS_DEFAULT_OPEN_POSITION;

// ----------------------------------------------------------------------------
//	All Properties Features
// ----------------------------------------------------------------------------
var IDS_WIN_INSERT_PROPS = "WBC_wndInsertProps";
var IDS_WIN_ATTACHPROPS_FEATURES = "width=600,height=210,resizable" + IDS_DEFAULT_OPEN_POSITION;
var IDS_WIN_IMAGEPROPS_FEATURES = "width=600,height=240,resizable" + IDS_DEFAULT_OPEN_POSITION;

// for properties dialog
var IDS_WIN_PAGEPROPS_NAME = "WBC_wndPageProperties";
var IDS_WIN_PAGEPROPS_FEATURES = "width=650,height=600,resizable,status=yes" + IDS_DEFAULT_OPEN_POSITION;


// ----------------------------------------------------------------------------
//	All Revision Features
// ----------------------------------------------------------------------------
// for page revision option dialog
var IDS_WIN_PAGEREVISION_OPTION_NAME = "WBC_wndPageRevisionOption";
var IDS_WIN_PAGEREVISION_OPTION_FEATURES = "width=400,height=280, resizable=yes" + IDS_DEFAULT_OPEN_POSITION;

// for page revision error message dialog
var IDS_WIN_PAGEREVISION_ERROR_FEATURES = "dialogWidth:450px; dialogHeight:310px; help:no; status:no; scroll:no" + IDS_IE_DIALOG_DEFAULT_POSITION;

//for resource management
var IDS_WIN_RESPROPS_NAME = "WBC_wndResourceProperties";
var IDS_WIN_RESPROPS_FEATURES = "width=725,height=450,resizable=yes,status=yes" + IDS_DEFAULT_OPEN_POSITION;


//for resource management
var IDS_WIN_RESCREATE_NAME = "WBC_wndResourceCreate";
var IDS_WIN_RESCREATE_FEATURES = "width=725,height=475,resizable=yes,status=yes" + IDS_DEFAULT_OPEN_POSITION;


//for resource management
var IDS_WIN_RESREPLACE_NAME = "WBC_wndResourceReplace";
var IDS_WIN_RESREPLACE_FEATURES = "width=725,height=475,resizable=yes,status=yes" + IDS_DEFAULT_OPEN_POSITION;

//for default propertties setup when creating a new page
var IDS_WIN_NEWPAGESAVEDLG_NAME = "WBC_wndNewPageSaveDlg";
var IDS_WIN_NEWPAGESAVEDLG_FEATURES = "width=650,height=280,resizable,status=yes" + IDS_DEFAULT_OPEN_POSITION;

//for default properties setup the page copy selection dialog
var IDS_WIN_PAGECOPYDLG_NAME = "WBC_wndPageCopyDlg";
var IDS_WIN_PAGECOPYDLG_FEATURES = "width=450,height=500,resizable=no,status=yes" + IDS_DEFAULT_OPEN_POSITION;

//for default properties setup the page move selection dialog
var IDS_WIN_PAGEMOVEDLG_NAME = "WBC_wndPageMoveDlg";
var IDS_WIN_PAGEMOVEDLG_FEATURES = "width=450,height=500,resizable=no,status=yes" + IDS_DEFAULT_OPEN_POSITION;

// for Internal Links dialog
var IDS_WIN_INTLINKS_FEATURES = "width=450,height=500" + IDS_DEFAULT_OPEN_POSITION;
var IDS_IEWIN_INTLINKS_FEATURES = "dialogWidth:450px;dialogHeight:500px;status:yes" + IDS_IE_DIALOG_DEFAULT_POSITION;

// for view revisions by date dialog
var IDS_WIN_PAGEREVISION_BY_DATE_NAME = "WBC_wndPageRevisionViewByDate";
var IDS_WIN_PAGEREVISION_BY_DATE_FEATURES = "width=450,height=320, resizable=yes" + IDS_DEFAULT_OPEN_POSITION; 


// ----------------------------------------------------------------------------
//
//	WBC dialogs browser window features (DYNAMIC SIZE)
//
// ----------------------------------------------------------------------------

// ----------------------------------------------------------------------------
//	Task Assistants
// ----------------------------------------------------------------------------
//for production manager dialog
var IDS_WIN_PRODUCTIONMANAGER_NAME = "WBC_wndProductionManager";
var IDS_WIN_PRODUCTIONMANAGER_FEATURES = "";

//for approval assistant dialog
var IDS_WIN_APPROVALASSISTANT_NAME = "WBC_wndApprovalAssistant";
var IDS_WIN_APPROVALASSISTANT_FEATURES = "";

// ----------------------------------------------------------------------------
//	Resource Management
// ----------------------------------------------------------------------------
//for resource management
var IDS_WIN_RESOURCE_MANAGEMENT_NAME = "WBC_wndResourceManagement";
var IDS_WIN_RESOURCE_MANAGEMENT_FEATURES = "";

// ----------------------------------------------------------------------------
//	All Preview Features
// ----------------------------------------------------------------------------
// for Posting preview
var IDS_WIN_PREVIEW = "WBC_wndPreview";
var IDS_WIN_PREVIEW_FEATURES = ""; 

//for page authoring preview
var IDS_WIN_AUTHPREVIEW_CONNECTED_SELECTION_NAME = "WBC_wndAuthPreviewConnectedSelection";
var IDS_WIN_AUTHPREVIEW_CONNECTED_SELECTION_FEATURES = ""; 


// ----------------------------------------------------------------------------
//	All Other Features
// ----------------------------------------------------------------------------
//for quick jump
var IDS_WIN_GOTO_CONNECTED_SELECTION = "WBC_wndGoToConnectedPagesSelectionDlg";
var IDS_WIN_GOTO_CONNECTED_SELECTION_FEATURES = ""; 


//for page creation of both simple and connected
var IDS_WINNAME_PAGE_CREATION = "NC_winChannelsBrowse";
var IDS_WINFEATURES_PAGE_CREATION = ""; 


// for page revision history
var IDS_WIN_PAGEREVISION_NAME = "WBC_wndPageRevison";
var IDS_WIN_PAGEREVISION_FEATURES = "";

// for page revision comparison dialog
var IDS_WIN_PAGEREVISION_COMPARE_NAME = "WBC_wndPageRevisionCompare";
var IDS_WIN_PAGEREVISION_COMPARE_FEATURES = "";


// for insert Image/Attachment/Video/table and insert properties
var IDS_WIN_GALLERY = "WBC_wndGallery";
var IDS_WIN_GALLERY_FEATURES = ""; 




