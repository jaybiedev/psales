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

<?

		if (!isset($_SESSION['aRecs'])) 
		{ 
   			$_SESSION['aRecs'] = array(); 
			$aRecs = null;
			$aRecs = array();
		} 
		
/*	while (file_get_contents("/prog/ics/"))
	{
		echo "x";
	}
*/
	if ($p1 == '')
	{
			$aRecs = null;
			$aRecs = array();

		if ($SYSCONF['BRANCH_ID']==1)
		{
			$filename = 'LA_ACCT_INFO.TXT';
		}
		else
		{
			$filename = 'LEC_ACCT_INFO.TXT';
		}
		if ($date == '') $date=ymd2mdy(yesterday());
	}
	elseif ($p1 == 'Upload')
	{
		$sourceFile = $_FILES['filename']['tmp_name'];
		
		$aip = explode('.',$_SERVER['REMOTE_ADDR']);
		$uploadfile= '../log/upload'.$aip[3].'.txt';
		
		if (!copy ($sourceFile, $uploadfile))
		{
			message("Problem Uploading File...");
		}
		
		$fields = array('account_id','account_code','cardno','cardname','account','account_status',
							'date_applied','date_expiry','date_birth','gender','civil_status','telno',
							'guarantor_id','credit_limit','bond','remarks',
							'account_class_id','account_type_id','address','branch_id');


			
		if (!file_exists($uploadfile))
		{
			message('File [ '.$uploadfile.' ] Does NOT exists...');
		}
		else
		{
			$fo = @fopen($uploadfile,'r');
			$contents = '';
			$aRecs = null;
			$aRecs=array();
			
		  	while (!feof($fo)) 
		  	{
  				$contents = fgets($fo, 8192);
  				if (strlen($contents) < 15) continue;

  				$temp = explode('||',$contents);
				$dummy = null;
				$dummy = array();
				for($c=0;$c<count($fields);$c++)
				{
					$dummy[$fields[$c]] = htmlentities($temp[$c]);
			 	}
			 	
				$aRecs[] = $dummy;
			}		
			
			$message = "$ctr Records Uploaded.  Please Click SAVE Button Below to POST Account Information.. ";
		}
		
	}	
?> 
<script language="JavaScript" type="text/JavaScript">
<!--
function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);
//-->
</script>

<br>
<form action="" method="post" enctype="multipart/form-data" name="fd" id="fd">
  <table width="80%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr bgcolor="#EFEFEF" background="../graphics/table0_horizontal.PNG"> 
      <td width="0%" height="24" background="../graphics/table_left.PNG">&nbsp;</td>
      <td width="0%" background="../graphics/table_horizontal.PNG">&nbsp;</td>
      <td width="100%" background="../graphics/table_horizontal.PNG"><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Account 
        Info Uploading (From Other Branch)</strong></font></td>
      <td width="0%" background="../graphics/table_right.PNG">&nbsp;</td>
    </tr>
    <tr> 
      <td colspan="4" nowrap><table width="100%%" border="0" cellpadding="0" cellspacing="1" bgcolor="#DDE5F3">
          <tr bgcolor="#DDE5F3"> 
            <td width="18%">&nbsp;</td>
            <td width="82%">&nbsp;</td>
          </tr>
          <tr bgcolor="#DDE5F3"> 
            <td><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Filename</font></td>
            <td> <input name="filename" type="file" id="filename" size="40">
			<input type="hidden" name="MAX_FILE_SIZE" value="200000"> 
              <input name="p1" type="submit" id="p1" value="Upload" >
              <input name="p13" type="hidden" id="p1" value="Upload" onCLick="wait('Please wait. Processing data...');xajax_accountUpload(xajax.getFormValues('fd'));"> 
              <input name="p12" type="button" id="p125" value="Close" onClick="window.location='?p'"></td>
          </tr>
          <tr bgcolor="#CCCCCC"> 
            <td colspan="2"><font color="#000066" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Upload 
              Details</strong></font></td>
          </tr>
          <tr bgcolor="#DDE5F3" height="320px"> 
            <td height="320px" colspan="2" valign="top">
			<div id="grid" name="grid" style="position:virtual; width:100%; height:100%; z-index:1; overflow: scroll;">
			<table width='100%' cellpadding='0' cellspacing='0'>
			<?
			$ctr=0;
			foreach ($aRecs as $temp)
			{
				$ctr++;
				$account_type = lookUpTableReturnValue('x','account_type','account_type_id','account_type',$temp['account_type_id']); 
				echo  "<tr class=\"gridRow\"><td align='right'>$ctr.</td>
								<td><input type='checkbox' name='delete[]' id='d$ctr' value='$ctr'></td>
								<td>".$temp['account_code']."</td>
								<td>".$temp['account']."</td>
								<td>$account_type</td>
								</tr>";
			}
			?>
			</table>

			</div></td>
          </tr>
          <tr bgcolor="#DADADA">
            <td colspan="2" valign="top"><input name="saveAccountUpload" type="button" id="saveAccountUpload" value="Save Accounts"  onCLick="wait('Please wait. Saving data...');xajax_saveAccountUpload(xajax.getFormValues('fd'));"></td>
          </tr>
        </table></td>
    </tr>
  </table>
  </form>
<?
if ($message != '')
{
	echo "<script>alert('$message')</script>";
}
?>