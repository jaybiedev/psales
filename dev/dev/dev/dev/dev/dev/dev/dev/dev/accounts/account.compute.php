<?
function computeLedger($cid, $fate="", $tdate = "", $rtype="")
{
  $q = "select * 
          from 
            accountledger 
          where 
            enable='Y' and 
            account_id='$cid'
          order by
            date";
       
  $QR = @pg_query($q) or message(pg_errormessage());
  
  $aAL = null;
  $aAL = array();
  $beginning_balance = 0;
  $balance = 0;
  $total_credit = $total_debit = 0;
  while ($R = @pg_fetch_assoc($QR))
  {
    if ($R['date'] < $fdate && $fdate!='')
    {
      if (in_array($R['type'], array('C','I')))
        $beginning_balance += $R['amount'];
      elseif ($R->type == 'D')  
        $beginning_balance -= $R['amount'];
    }
    elseif ($R['date'] > $tdate && $tdate!='')
    {
      break;
    }
    else
    {
      $temp = null;
      $temp = array();
      if (count($aAL)== '0' && $beginning_balance != '0.00')
      {
        $temp['beginning_balance'] = $beginning_balance;
        $temp['amount'] = $beginning_balance;
        if ($R['type'] == 'C')
        {
          $total_credit += $R['amount'];
        }
        elseif($R['type'] == 'D')
        {
          $total_debit += $R['amount'];
        }
        elseif($R['type'] == 'I')
        {
          $total_interest += $R['amount'];
        }
        elseif($R['type'] == 'A')
        {
          $total_adjust += $R['amount'];
        }
        $aAL[] = $temp;
      }

      $temp = null;
      $temp = array();
      $temp = $R;
      $aAL[] = $temp;
      
      
    }
  }   

  $sAL = null;
  $sAL = array();
  $sAL['total_debit'] += $total_debit; 
  $sAL['total_credit'] += $total_credit; 
  $sAL['total_interest'] += $total_interest; 
  $sAL['total_adjust'] += $total_adjust;
  $sAL['balance_account'] = $sAL['total_credit'] + $sAL['total_adjust'] + $sAL['total_interest'] - $sAL['total_debit'];
  if ($rtype == 'S')
  {
    return $sAL; 
  }
  else
  {
    return $aAL;
  }           
}
?>
