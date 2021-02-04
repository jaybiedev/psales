<?
function employeeBalance($aid, $did=null, $mdate_asof='')
{
	// aid = paymast_id
	// did = deduction_type_id
	// pid = payroll
	
  $aBal = null;
  $aBal = array();

  $q = "select sum(credit-debit) as credit,
  			payrollcharge.deduction_type_id,
  			deduction_type 
  		from 
  			payrollcharge,
  			deduction_type
  		where 
  			deduction_type.deduction_type_id = payrollcharge.deduction_type_id and 
  			payrollcharge.enable='Y' and 
  			payrollcharge.paymast_id ='$aid' ";

  if ($did !='')
  {
  	$q .= " and payrollcharge.deduction_type_id = '$did'";
  }
  if ($mdate_asof != '')
  {
  	$q .= " and date<='$mdate_asof'";
  }

  	$q	.= " group by
  					payrollcharge.paymast_id,
  					payrollcharge.deduction_type_id,
  					deduction_type";
  $qr = @pg_query($q) or message1(pg_errormessage().$q);
  $r = @pg_fetch_assoc($qr);
  
  $aBal = $r;

  if ($did !='')
  {
    // must be modified to identify the correct current amount to be deducted
  	$q = "select sum(ammort) as ammort 
  				from 
  					payrollcharge 
  				where 
  					enable='Y' and 
  					paymast_id = '$aid' and
  					balance <> '0' and  
  					deduction_type_id = '$did'";

	if ($mdate_asof != '')
  	{
  		$q .= " and date<='$mdate_asof'";
  	}
  					
  	$q .=" group by paymast_id"; 
  				

	 $qr = @pg_query($q) or message1(pg_errormessage().$q);
 	 $r = @pg_fetch_assoc($qr);
	 $aBal['ammort'] = $r['ammort']; 
	 					

	}
	
	
	$q = "select sum(amount) as debit from payroll_header, payroll_detail
				where
					payroll_header.payroll_header_id =  payroll_detail.payroll_header_id and
					payroll_header.paymast_id = '$aid' and
					payroll_header.status!='C' and
					payroll_detail.type = 'D'";
					
	if ($did != '')
	{
		$q .= " and type_id = '$did'";
	}
	 $qr = @pg_query($q) or message1(pg_errormessage().$q);
 	 $r = @pg_fetch_assoc($qr);
					
	$aBal['debit'] = $r['debit'];
	$aBal['balance'] = $aBal['credit'] - $aBal['debit']; 		
	if ($aBal['balance'] < $aBal['ammort'] && $aBal['balance'] > 0)
	{
		$aBal['ammort'] = $aBal['balance'];
	}
  return $aBal;
}

function employeeTotalBalance($pid)
{
	$q = "select sum(credit-debit-deduct) as balance
			from 
				payrollcharge
			where
				enable='Y' and
				paymast_id = '$pid'";
	$qr = @pg_query($q) or message1(pg_errormessage());
	$r = @pg_fetch_assoc($qr);
	return $r;
}
?>
