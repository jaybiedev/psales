<?
//-- Created By: Jared O. Santibanez
//               University of St. La Salle, Bacolod City Philippines
//               College of Engineering


function selectGchartReportForm($id)
{
	
	$q = "select * from gchart where gchart_id = '$id'";
	$qr = @pg_query($q);
	$r = @pg_fetch_object($qr);
	
	$code =$r->acode.'-'.$r->scode;
	gset('code',$code);
	gset('descr',$r->gchart);
	if ($r->level == '9')
	{
		gset('type','D');
	}
	else
	{
		gset('type','T');
	}
	$focus = "document.getElementById('descr').focus()";
			
	gscript($focus);

   hide_layer('browseLayer');
	return done();
}

function searchGchartReportForm($form)
{
			$code = $form['code'];
			
			$q = "select * from gchart, gchart_type where gchart.gchart_type_id=gchart_type.gchart_type_id and concat(acode,'-',scode) like '$code%'";
			$qr = @pg_query($q);
			
			$table = "<table width='100%' cellpadding='0' cellspacing='1'>";
			$ctr=0;

			if (@pg_num_rows($qr) == 1)
			{
				$r = @pg_fetch_object($qr);
				selectGchartReportForm($r->gchart_id);
			}
			else
			{
						
				while ($r = @pg_fetch_object($qr))
				{
					$ctr++;
					$href = "onClick=\"xajax_selectGchartReportForm('$r->gchart_id')\"";
					if ($r->level == '9')
					{
						$mlevel = 'Detail';
					}
					elseif ($r->level == '0')
					{
						$mlevel = 'Main';
					}
					else
					{
						$mlevel = 'Sub'.$r->level;
					}
						
					$table .= "<tr class='grid' bgColor=\"FFFFFF\">
									<td align='right' width='5%'>$ctr. </td>
									<td  width='15%'> &nbsp;<a href='#' $href >".$r->acode.'-'.$r->scode."</a></td>
									<td  width='40%'><a href='#' $href >".$r->gchart."</a></td>
									<td  width='10%'><a href='#' $href >".$r->gchart_type_code."</a></td>
									<td  width='5%'><a href='#' $href >".$mlevel."</a></td>";
					$table .= "</tr>";				
				}			
				$table .= "</table>";
	
				show_layer('browseLayer');
				glayer('innerLayer',$table);
			}
	return done();
}

function deleteReportForm($ctr)
{
	global $aRFD;
	
	$dummy = $aRFD[$ctr-1];
	$dummy['delete'] = 'Y';

	$aRFD[$ctr-1] = $dummy;

	gridReportForm($aRFD);
	return done();
}

function upReportForm($ctr)
{
	global $aRFD;
	
	$dummy = $aRFD[$ctr-1];
	$c=0;
	$newarray = null;
	$newarray = array();
	$ok=0;
	
	if ($ctr == 1)
	{
		$newarray[] = $dummy;
		$ok=1;
	}
	foreach ($aRFD as $temp)
	{
		$c++;
		if ($c == $ctr)
		{
			continue;
		}
		elseif ($c == ($ctr-1))
		{
			$newarray[] = $dummy;
			$ok=1;
		}
		$newarray[] = $temp;
	}
	if ($ok == '0')
	{
			$newarray[] = $dummy;
	}
	$aRFD = $newarray;
	gridReportForm($aRFD);
	return done();
}

function downReportForm($ctr)
{
	global $aRFD;
	
	$dummy = $aRFD[$ctr-1];
	$c=0;
	$newarray = null;
	$newarray = array();
	$ok=0;
	
	foreach ($aRFD as $temp)
	{
		$c++;
		if ($c == $ctr)
		{
			continue;
		}
		elseif ($c == ($ctr+2))
		{
			$newarray[] = $dummy;
			$ok=1;
		}
		$newarray[] = $temp;
	}
	if ($ok == '0')
	{
			$newarray[] = $dummy;
	}
	$aRFD = $newarray;
	gridReportForm($aRFD);
	return done();
}

function saveReportForm($form)
{
	global $aRF, $aRFD;

	$fields_header = array('reportform');
	for ($c=0;$c<count($fields_header);$c++)
	{
		$aRF[$fields_header[$c]]= $form[$fields_header[$c]];
	}
	
	if ($aRF['rf_header_id'] == '')
	{
		$q = "insert into rf_header 
					(reportform,enable)
				value
					('".$aRF['reportform']."','Y')";
		$qr = @pg_query($q);
		if (!$qr)
		{
			galert(pg_errormessage());
		}
		else
		{
			$id = @pg_insert_id();
			$aRF['rf_header_id'] = $id;
		}
	}	
	else
	{
		$q = "update rf_header set
					reportform='".$aRF['reportform']."'
				where
					rf_header_id = '".$aRF['rf_header_id']."'";

		$qr = @pg_query($q);
		if (!$qr)
		{
			galert(pg_errormessage());
		}

	}
	
	
	if ($aRF['rf_header_id'] != '')
	{
		$c=0;
		foreach ($aRFD as $temp)
		{
			if ($temp['rf_detail_id'] == '')
			{
				$q = "insert into rf_detail (rf_header_id,gchart_id,code,descr,type,operation,
									var1,var2, line)
							values
									('".$aRF['rf_header_id']."', '".$temp['gchart_id']."',
									'".$temp['code']."','".$temp['descr']."',
									'".$temp['type']."','".$temp['operation']."',
									'".$temp['var1']."','".$temp['var2']."','$c')";
				$qr = @pg_query($q);
				if (!$qr)
				{
					galert(pg_errormessage().$q);
				}
				else
				{
					$id = @pg_insert_id();
					$dummy = $temp;
					$dummy['rf_detail_id'] = $id;
					
					$aRFD[$c] = $dummy;
				}
			}
			elseif ($temp['rf_detail_id'] != '' && $temp['delete'] == 'Y')
			{	
				$q = "delete from rf_detail where rf_detail_id = '".$temp['rf_detail_id']."'";
				$qr = @pg_query($q);
				if (!$qr)
				{
					galert(pg_errormessage().$q);
				}

			}
			else
			{
				$q = "update rf_detail  set
								gchart_id = '".$temp['gchart_id']."',
								code = '".$temp['code']."',
								descr = '".$temp['descr']."',
								type = '".$temp['type']."',
								operation = '".$temp['operation']."',
								var1 = '".$temp['var1']."',
								var2 = '".$temp['var2']."',
								code = '".$temp['code']."',
								line = '$c'
						where
							rf_detail_id ='".$temp['rf_detail_id']."'";

				$qr = @pg_query($q);
				if (!$qr)
				{
					galert(pg_errormessage());
				}

			}
			$c++;
		}
	}
	
	return done();
}
function loadReportForm()
{
	global $aRF, $aRFD;
	
	gridReportForm($aRFD);
	return done();
}
function okReportForm($form)
{
	global $aRFD, $aRF;
	
	$dummy = null;
	$dummy = array();
	$fields = array('gchart_id','code','descr','tab','operation','type','line','var1','var2');
	for ($c=0;$c<count($fields);$c++)
	{
		$dummy[$fields[$c]]= $form[$fields[$c]];
	}
	
	if (in_array($dummy['type'], array('L1','L2')))
	{
		$dummy['tabs'] = 0;
	}
	
	if ($dummy['tab']>3) $dummy['tab'] = 3;
	
	$cc=$fnd=0;
	foreach ($aRFD as $temp)
	{
		if ($temp['code'] == $dummy['code'] && $temp['code'] != '')
		{
			$aRFD[$cc] = $dummy;
			$fnd = 1;
		}
		$cc++;
	}
	if ($fnd == '0')
	{
		if (($dummy['line'] == '') or ($dummy['line']>count($aRFD)))
		{
			$aRFD[] = $dummy;
		}
		elseif ($dummy['line'] != '')
		{
			$newarray = null;
			$newarray = array();
			$c=0;
			foreach ($aRFD as $temp1)
			{
				$c++;
				if ($c == $dummy['line'])
				{
					$newarray[] = $dummy;
				}
				$newarray[] = $temp1;
			}
			$aRFD =$newarray;
		}
		
	}
	
	gridReportForm($aRFD);
	
	gset('code','');
	gset('descr','');
	gset('line','');
	gset('tab','');
	return done();
}

function vType($type)
{
	$ctype = '';
	if ($type == 'T')
	{
		$ctype = 'Title';
	}
	elseif ($type == 'D')
	{
		$ctype = 'Detail';
	}
	elseif ($type == '1L')
	{
		$ctype = 'Line';
	}
	elseif ($type == '2L')
	{
		$ctype = 'Line';
	}
	return $ctype;
}

function editReportForm($ctr)
{
	global $aRFD;

	$dummy = $aRFD[$ctr-1];
	gset('line',$ctr);
	gset('code',$dummy['code']);
	gset('code',$dummy['code']);
	gset('descr',$dummy['descr']);
	gset('gchart_id',$dummy['gchart_id']);
	gset('type',$dummy['type']);
	gset('line',$dummy['line']);
	gset('var1',$dummy['var1']);
	gset('var2',$dummy['var2']);
	gset('operation',$dummy['operation']);
	gset('tab',$dummy['tab']);
	
	return done();
}

function gridReportForm($arr)
{
			
			$table = "<table width='100%' cellpadding='0' cellspacing='1'>";
			$ctr=0;
			foreach ($arr as $temp)
			{
				if ($temp['delete'] == 'Y')
				{
					continue;
				}
				$ctr++;
				$href = "onClick=\"xajax_editReportForm('$ctr')\"";
				$hrefup = "onClick=\"xajax_upReportForm('$ctr')\"";
				$hrefdown = "onClick=\"xajax_downReportForm('$ctr')\"";
				$hrefdelete = "onClick=\"if (confirm('Are you sure do remove this line?')){xajax_deleteReportForm('$ctr')}\"";
				
				$table .= "<tr class='grid' bgColor=\"FFFFFF\">
								<td align='right' width='5%'>$ctr.</td>
								<td  width='15%'> &nbsp;<a href='#' $href >".$temp['code']."</a></td>";
				
				$tabs = $temp['tab']*1;
				for ($tc=0;$tc<$tabs;$tc++)
				{
					$table .= "<td width='3%'>&nbsp; &nbsp; &nbsp;</td>";
				}
				$colspan = 4-$tabs;
				$width = 31 + 4*$colspan;
				$table .= 	"<td  colspan=\"$colspan\" width='$width%'><a href='#' $href >".$temp['descr']."</a></td>";

				if ($temp['type'] == 'T')
				{
					$table .= "	<td align='right' width='15%'>&nbsp;</td>";
				}
				elseif (in_array($temp['type'], array('D','R')))
				{
					$table .= "	<td align='right' width='15%'>";
					if ($temp['operation'] == 'A')
					{
						$table .= $temp['var1']."+ ";
					}
					elseif ($temp['operation'] == 'S')
					{
						$table .= $temp['var1']."- ";
					}
					$table .= "99,999,999.99</td>";
				}
				elseif ($temp['type'] == '1L')
				{
					$table .= "	<td align='right' width='15%'>".str_repeat('-',30)."</td>";
				}
				elseif ($temp['type'] == '2L')
				{
					$table .= "	<td align='right' width='15%'>============</td>";
				}
				else
				{
					$table .= "<td></td>";
				}


				$table .="<td  width='10%'><img src='graphics/up.gif' alt='Move Up' $hrefup>  
								<img src='graphics/down.gif'  alt='Move Down' $hrefdown> 
								<img src='graphics/close2.gif'  $hrefdelete></td>
								</tr>";
			}

			$table .= "</table>";
			
			glayer('gridLayer',$table);
			return;
	}

?>