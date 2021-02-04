// ****************************************************************************
//
//	Client-side JavaScript UI Strings 
//
// ****************************************************************************
 

// -- Generic UI strings
var IDS_FUNCTION_NOT_FOUND = "Function not found.";
var IDS_INVALID_NAME = "Name cannot be all blanks and cannot contain characters #, &, %, +, / and |.";


// ----------------------------------------------------------------------------
//
//	Delete (purge) confirm UI dialogs
//
// ----------------------------------------------------------------------------
var IDS_DELETE_CONFIRM = "Deleting this Page will remove it permanently from the system. Continue?";
var IDS_CONFIRM_DELETE_RESOURCES = "Deleting the Resource will remove it permanently from the system. Continue?";


// ---------------------------------------------------------------------------- 
//
//	Page/Channel Properties dialog
//
// ---------------------------------------------------------------------------- 
var IDS_INVALID_START_DATE = "Invalid start date.";
var IDS_INVALID_EXPIRY_DATE = "Invalid expiry date.";
var IDS_EXPIRYDATE_LESS_THAN_STARTDATE = "Expiry date must occur after start date.";


// ---------------------------------------------------------------------------- 
//
//	Resource Browser dialogs
//
// ---------------------------------------------------------------------------- 
var IDS_INVALID_IMAGE_TYPE = "The selected file is not recognized as a valid image file by its file extension. Do you want to proceed?";
var IDS_INVALID_VIDEO_TYPE = "The selected file is not recognized as a valid video file by its file extension. Do you want to proceed?";
var IDS_NO_ATTACHMENT_SELECTED = "No attachment is selected.";
var IDS_NO_FILE_SELECTED = "No desktop file is selected.";
var IDS_NO_IMAGE_SELECTED = "No image is selected.";
var IDS_NO_RESOURCE_SELECTED = "No resource is selected.";
var IDS_INVALID_DISP_TEXT = "Invalid display text.";


// ---------------------------------------------------------------------------- 
//
//	UI Strings used in WBC Authoring Mode
//
// ---------------------------------------------------------------------------- 
var IDS_TMPL_EXTRA_FORM = "The Template HTML may contain a form which conflicts with the WBC Authoring form.\n" +
							"Please mask out each of the <form> and </form> tags in the Template HTML with \<\% If Not IsAuthoringMode() Then \%\>.\n" +
							"See WBC section in the Site Programmers Guide for details."
var IDS_SWITCH_TO_LIVE_CONFIRM = "Switching to the live site will cancel any unsaved changes.  Continue?";
var IDS_WARN_BEFORE_LEAVE = "Refreshing or leaving this page will lose all unsaved changes.";
var IDS_CONTENT_STRIPPED = "Invalid content or formatting was found and was stripped out when saved.\nCheck the placeholder properties for allowed content and formatting.";
var IDS_CONTENT_STRIPPED_FOR_PREVIEW = "Invalid content or formatting was found and will not be shown in the preview.\n\nThe invalid content or formatting will be removed permanently when the page is saved.\nCheck the placeholder properties for allowed content and formatting.";

var IDS_WINDOW_OPENER_NOT_FOUND = "Launching window cannot be found.";

var IDS_APPROVALASSISTANT_NOPOSTINGSSELECTED = "No pages were selected. Please select at least one page before clicking the Approve or Decline button.";


// ---------------------------------------------------------------------------- 
//
//	For the Insert Table dialog
//
// ---------------------------------------------------------------------------- 
var IDS_INVALID_ROWS = "Number of rows must be a number.";
var IDS_INVALID_COLUMNS = "Number of columns must be a number.";
var IDS_INVALID_COLUMNS_RANGE = "Number of columns must be a number greater than zero.";
var IDS_INVALID_ROWS_RANGE = "Number of rows must be a number greater than zero.";


// ---------------------------------------------------------------------------- 
//
//	For the Insert Hyperlink dialog
//
// ---------------------------------------------------------------------------- 
var IDS_HLINK_CUSTOMWIN_HELPTEXT = "Please enter your custom window name"
var IDS_HLINK_WARN_EMPTY = "You did not specify a Hyperlink Address or a Name.\r\n" +
						"As a result the selection in the placeholder will receive no anchor tag.\r\n\r\n" +
						"Do you want to proceed?"
var IDS_HLINK_WARN_RELLINK = "You seemed to have specified a relative URL.\r\n" +
						"Relative URL is currently not supported by Content Management Server.\r\n\r\n" +
						"Do you want to continue?"


// ---------------------------------------------------------------------------- 
//
//	Revision History dialog
//
// ---------------------------------------------------------------------------- 
var IDS_UI_LABEL_SELECT_DESCRIPTION = "Select revision from list below"
