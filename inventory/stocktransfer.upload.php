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
if (!chkRights3('payment','madd',$ADMIN['admin_id']))
{
	message("You have no permission to entry payment/collection...");
	exit;
}

		if (!isset($_SESSION['aPay'])) 
		{ 
   			$_SESSION['aPay'] = array(); 
			$aPay = null;
			$aPay = array();
		} 
		if (!isset($_SESSION['aPayD'])) 
		{ 
   			$_SESSION['aPayD'] = array(); 
			$aPayD = null;
			$aPayD = array();
		} 
	if ($p1 == '')
	{
			$aPay= null;
			$aPay = array();
			$aPayD= null;
			$aPayD = array();

			$filename = 'PAY_'.$SYSCONF['BRANCH_CODE'].'TXT';
		if ($date == '') $date=ymd2mdy(yesterday());
	}
	elseif ($p1 == 'Upload' &&  $_FILES['filename']['tmp_name']=='')
	{
		message("No FILE Specified.... Nothing to Upload...");
	}
	elseif ($p1 == 'Upload')
	{
		$sourceFile = $_FILES['filename']['tmp_name'];
		
		
		$aip = explode('.',$_SERVER['REMOTE_ADDR']);
		$uploadfile= '../var/upload'.$aip[3].'.txt';
		
		if (!copy ($sourceFile, $uploadfile))
		{
			message("Problem Uploading File...");
		}

		if (!session_is_registered('aPayD'))
		{
			session_register('aPayD');
			$aPayD = null;
			$aPayD = array();
		}
		if (!session_is_registered('iPayD'))
		{
			session_register('iPayD');
			$iPayD = null;
			$iPayD = array();
		}	
		$aPay = null;
		$aPay = array();
		$aPayD = null;
		$aPayD = array();
		$iPayD = null;
		$iPayD = array();


		$fields_header = array('payment_header_id','date','reference','account_group_id','entry_type',
						'date_withdrawn','withdraw_day','clientbank_id','total_amount',
						'mcheck','user','branch_id');

		$fields_detail = array('payment_header_id','account_id','account_code','delete','releasing_id','ddate',
						'mconfirm','withdrawn','amount','excess','remark');
			
		if (!file_exists($uploadfile))
		{
			message('File [ '.$uploadfile.' ] Does NOT exists...');
		}
		else
		{
			$fo = @fopen($uploadfile,'r');
			$fo = @fopen($filename,'r');
			$contents = '';

		  	while (!feof($fo)) 
		  	{
  				$contents = fgets($fo, 8192);
  				if (strlen($contents) < 15) continue;

  				$temp = explode('||',$contents);
  				
  				if ($temp[0] == 'PAYMENTHEADER')
  				{
					$dummy = null;
					$dummy = array();

					for($c=0;$c<count($fields_header);$c++)
					{
						$val = $temp[$c+1];
						$dummy[$fields_header[$c]] = htmlentities($val);
				 	}
				 	
					$aPay[] = $dummy;
				}
				else
				{
					$dummy = null;
					$dummy = array();
					for($c=0;$c<count($fields_detail);$c++)
					{
						$dummy[$fields_detail[$c]] = htmlentities($temp[$c]);
				 	}
				 	$dummy['account'] = lookUpTableReturnValue('x','account','account_id','account',$dummy['account_id']);	
					$aPayD[] = $dummy;
				}		
			}
		}

		$message = "$ctr Records Uploaded.  Please Click SAVE Button Below to POST Payment Entries.. ";
//		print_r($aPay);
		
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
      <td width="100%" background="../graphics/table_horizontal.PNG"><font color="#FFFFCC" size="2" face="Verdana, Arial, Helvetica, sans-serif"><strong>Stocks 
        Transfer Uploading (From Another Branch)</strong></font></td>
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
              <input name="p13" type="hidden" id="p1" value="Upload" onCLick="wait('Please wait. Processing (Upload) data...');xajax_paymentUpload(xajax.getFormValues('fd'));"> 
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
			foreach ($aPay as $temp)
			{
				$ctr++;
				$account_group = lookUpTableReturnValue('x','account_group','account_group_id','account_group',$temp['account_group_id']); 
				echo  "<tr class=\"gridRow\"><td align='right'>$ctr.</td>
								<td><input type='checkbox' name='delete[]' id='d$ctr' value='$ctr'></td>
								<td>".$account_group."</td>
								<td>".ymd2mdy($temp['date'])."</td>
								<td>".$temp['mcheck']."</td>
								<td align='right'>".number_format($temp['total_amount'],2)."</td>
								<td>&nbsp;".$temp['user']."</td>
								</tr>";
			}
			?>
			</table>

			</div></td>
          </tr>
          <tr bgcolor="#DADADA">
            <td colspan="2" valign="top"><input name="saveAccountUpload" type="button" id="saveAccountUpload" value="Save Payment Entries"  onCLick="wait('Please wait. Saving data...');xajax_savePaymentUpload(xajax.getFormValues('fd'));"></td>
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