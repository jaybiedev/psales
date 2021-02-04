<?
$u = explode('/',$_SERVER["REDIRECT_URL"]);

$regdir = $SystemRoot.'/home/';

for ($c=0;$c<count($u)-1;$c++)
{
	$regdir .= $u[$c].'/';
	@mkdir($regdir,0700);
}	

$regfile = $regdir.'pos.reg';
@mkdir($regdir,0700);

// get contents of a file into a string
if ($KURUKOKUK != lango(2))
{
	echo "Security Check. Please Login Again";
	$p=login;
	exit;
}	

if ($bj =='Register')
{
	if ($erika == ew($REG_SERIAL_NO))
	{
		$fd = fopen ($regfile, "w");
		fwrite($fd, md5($REG_SERIAL_NO));
		fclose($fd);
		echo "Registration Code Accepted...";
	}
	else
	{
		echo "Invalid Registraton Code...";
		require_once('../lib/hope.jay.lib.php');
	}	
}
else
{
	$fd = @fopen ($regfile, "r");
	$contents = @fread ($fd, filesize ($regfile));
	@fclose ($fd);
	
	if (substr($contents,0,strlen($REG_SERIAL_NO)) != ew($REG_SERIAL_NO))
	{
		require_once('../lib/hope.jay.lib.php');
	}
}
$KURUKOKUK.='abc';
?>