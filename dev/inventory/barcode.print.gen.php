<script language="JavaScript" src="../js/code39.js"></script>
<form name="f2" id="f2" action="" method="post" style="margin:'0'">
<input type="text" id="bar" name="bar" value="<?=$bar;?>" style="border:none; font-size:7" size="16">
<input type="text" id="copies" name="copies" value="<?= $copies;?>" style="border:none; font-size:7" size="3">
</form>
<?
echo "<table bgColor='#EFEFEF' cellspacing='1' cellpadding='10'><tr bgColor='#FFFFFF'>";
for ($c=0;$c<$copies;$c++)
{
  if ($c%5 == 0)
  {
    echo "</tr><tr bgColor='#FFFFFF'>";
  }
  //Code39(theX, theY, theBarHeight, theFontHeight, theBarCodeText)
  
  echo "<td><script>Code39('','',40,8,'$bar')</script></td>";
}
echo "</tr></table>";
?>
