<?
function writefile($text, $new=false, $file=null)
{
    if ($file == '')
    {
          $aip = explode('.',$_SERVER['REMOTE_ADDR']);
          $file= '/prog/log/DOC'.$aip[3].'.txt';
    }
    if ($new == 1)
    {
        $fo = @fopen($file,'w+');
   }
   else
   {
        $fo = @fopen($file,'a+');
   }
   //echo " file : $file new :$new text : $text <br>";
        $w = @fwrite($fo, $text);
        @fclose($fo);
        return $w;
}

$DBDOMAIN='localhost';
$DBNAME='lec';
$DBUSERNAME='Jared';
$DBPASSWORD='jnb2000';
$DBCONNECT = "host=$DBDOMAIN port=5432  dbname=$DBNAME user=$DBUSERNAME password=$DBPASSWORD";
$DBCONN = @pg_Connect($DBCONNECT) or die("Can't connect to server...");
?>
