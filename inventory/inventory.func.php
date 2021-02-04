<?
function newPrice($aItem)
{
	$aD = null;
	$aD = array();
	
	$sub_gross = $aItem['cost1']; //unit cost
	$discount_amount = 0;
/*
	if ($aItem['disc1_type']=='P')
	{
		$discount_amount = round($sub_gross *  $aItem['disc1']/100,2);
	}

	$sub_gross = $aItem['cost1'] - $discount_amount;
	
	if ($aItem['disc2_type']=='P')
	{
		$discount_amount += round($sub_gross *  $aItem['disc2']/100,2);
	}
	$sub_gross = $aItem['cost1'] - $discount_amount;
	if ($aItem['disc3_type']=='P')
	{
		$discount_amount += round($sub_gross *  $aItem['disc3']/100,2);
	}
*/
	$sub_gross = $aItem['cost1'] - $discount_amount;
	$sub_gross += $aItem['freight_case']/$aItem['fraction3']; //- freight per case
	
	if ($aItem['tax_add'] > 0)
	{
		$sub_gross = $sub_gross * (1 + $aItem['tax_add']/100);
	}
	$price1 = round($sub_gross*(1 + ($aItem['markup']/100)),2);
	$aD['price1'] = $price1;
	$aD['cost1'] = $sub_gross;
	
	return $aD;	
}
?>