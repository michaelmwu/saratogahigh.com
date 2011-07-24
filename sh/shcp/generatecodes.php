<?
include '../db.php';

// $rslist = mysql_query("SELECT USER_LIST.* FROM USER_LIST WHERE USER_VALIDATED=0 AND USER_ACTIVATION IS NULL"); // ADD CRITERIA

$rslist = mysql_query("SELECT USER_LIST.* FROM USER_LIST WHERE (USER_GR > 2005 AND USER_GR < 2010)"); // New School Year!

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
	<head>
		<title>Generatate Activation Code</title>
		<link rel="stylesheet" type="text/css" href="../shs.css">
		<meta name="vs_targetSchema" content="http://schemas.microsoft.com/intellisense/ie5">
		<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
		<link rel="stylesheet" type="text/css" href="admin.css">
	</head>
	<body>
	
<? include "inc-header.php";

while($list = mysql_fetch_array($rslist, MYSQL_ASSOC))
{
$acode = NewActivationCode();
mysql_query("UPDATE USER_LIST SET USER_ACTIVATION = '" . $acode . "' WHERE USER_ID = " . $list['USER_ID']);

}

include '../inc-footer.php'; ?>

</body>
</html>