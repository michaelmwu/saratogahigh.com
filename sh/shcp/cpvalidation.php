<? if($loggedin)
{
	$current_path = stripslashes($_SERVER['PATH_INFO']);

	$rspermissions = mysql_query('SELECT ADMINPAGE_PERMISSION FROM ADMINPAGE_LIST WHERE ADMINPAGE_PATH="' . addslashes(substr($current_path, 1 + strrpos($current_path, '/'))) . '" AND ADMINPAGE_PERMISSION<=' . $userR['USER_STATUS']);

	if($permission = mysql_fetch_array($rspermissions, MYSQL_ASSOC) && $isstaff)
	{
		
	}
	else
	{
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
  <title>SaratogaHigh.com Admin</title>
  <link rel="stylesheet" href="/shs.css" type="text/css">
</head>
<body>
<p>You don't have permission to view this page.</p>
</body>
</html>
<?
		die();
	}
}
else
{
	forceLogin();
}
?>