<?
include '../db.php';

$rslist = mysql_query("SELECT USER_LIST.* FROM USER_LIST WHERE USER_VERSTR = ''"); // ADD CRITERIA

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
	<head>
		<title>Generatate Verification Codes</title>
		<link rel="stylesheet" type="text/css" href="../shs.css">
		<meta name="vs_targetSchema" content="http://schemas.microsoft.com/intellisense/ie5">
		<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
		<link rel="stylesheet" type="text/css" href="admin.css">
	</head>
	<body>
	
<? include "inc-header.php";

function make_seed()
{
   list($usec, $sec) = explode(' ', microtime());
   return (float) $sec + ((double) $usec * 100000);
}

function rand_hex()
{
   mt_srand(make_seed());
   $randval = mt_rand(0,255);
   return sprintf("%02X",$randval);
}

while($list = mysql_fetch_array($rslist, MYSQL_ASSOC))
{
	$newverstr = '';

	for($j = 1; $j <= 120; $j++)
		$newverstr .= rand_hex();

mysql_query("UPDATE USER_LIST SET USER_VERSTR = 0x" . $newverstr . " WHERE USER_ID = " . $list['USER_ID']);

}

include '../inc-footer.php'; ?>

</body>
</html>