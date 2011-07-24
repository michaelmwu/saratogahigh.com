<?
// Philip Sung | 0x7d3 | saratogahigh.com
// directory/userlog.php: displays a user's or ip's hit logs
include '../db.php';
require 'cpvalidation.php';

$sid = '';     // ID of target user
$errorM = '';  // Fatal error message, if any
$resp = '';    // Response message to user, if any
$searchip = false; // Search by IP?
$pass = isset($_GET['pass']);

// Read target id from the query string, or, if this is not available, redirect to my own schedule, unless it matches ip format.
if(is_id($_GET["id"]))
{
	$sid = $_GET["id"];
}
else if( preg_match("/^\d+?\.\d+?\.\d+?\.\d+?$/", $_GET["id"] ) )
{
	$searchip = true;
	$ip = $_GET["id"];
}
else
	$sid = $userid;

if(! ($searchip || $pass) )
{
// Grab user record from database, if not ip;
$result = mysql_query("SELECT *, USER_LASTLOGIN Is Null as TSNULL, USER_LASTLOGIN AS TS FROM USER_LIST WHERE USER_ID=" . $sid) or die("User query failed");
$l = mysql_fetch_array($result, MYSQL_ASSOC);

mysql_free_result($result);
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
	<title><? if($searchip) print 'IP'; else if($pass) print 'Reset Password'; else print 'User'; ?> Hit Log</title>
	<link rel="stylesheet" type="text/css" href="../shs.css">
	<meta name="vs_targetSchema" content="http://schemas.microsoft.com/intellisense/ie5">
	<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
</head>
<body>

<? include "inc-header.php" ?>

<?
if($errorM == '')
{
	if($searchip || $pass)
	{
		// Print IP or Reset Password;
		print '<h1>' . ($pass ? 'Reset Password Requests' : $ip) . '</h1>';
		print '<form action="userlog.php" method="GET" style="padding-top: 12px;">Search by ID or IP: <input name="id" type="text" size="12"> <input type="submit" value="Go"></form>';
		print '<a href="userlog.php?pass">View Reset Password Requests</a>';

		print '<table width="100%" style="table-layout: fixed"><tr>
			<td style="width: 18em" class="header">Time</td><td class="header" style="width: 15em">Name</td><td class="header">Path</td><td style="width: 8em" class="header">IP</td></tr>';

		if($searchip)
			$rsvisits = mysql_query("SELECT LOG_LIST.*, USER_FULLNAME FROM LOG_LIST LEFT JOIN USER_LIST ON LOG_USER=USER_ID WHERE LOG_IP='$ip' ORDER BY LOG_TS DESC");
		else if($pass)
			$rsvisits = mysql_query("SELECT LOG_LIST.*, USER_FULLNAME FROM LOG_LIST LEFT JOIN USER_LIST ON LOG_USER=USER_ID WHERE LOG_PATH='/mailpass.php' ORDER BY LOG_TS DESC");
		while($visit = mysql_fetch_array($rsvisits, MYSQL_ASSOC))
		{
			if($visit['LOG_QUERY'] != '')
				$cururl = $visit['LOG_PATH'] . '?' . $visit['LOG_QUERY'];
			else
				$cururl = $visit['LOG_PATH'];
		print '<tr><td class="data">' . date('D Y M j, h:i:s a', strtotime($visit['LOG_TS'])) . '</td><td class="data">' . $visit['USER_FULLNAME'] . '</td><td class="data"><a href="' . $cururl . '">' . htmlentities($cururl) . '</td><td class="data" title="' . $visit['LOG_BROWSER'] . '">' . $visit['LOG_IP'] . '</td></tr>';
		}
		print '</table>';
	}
	else
	{
		// Print name
		print '<h1>' . $l['USER_FN'] . ' ' . $l['USER_LN'] . '<br>';
		print '<span style="font-size: smaller">' . GradePrint($l['USER_GR']) . ' &mdash; ';
		if($l['USER_VALIDATED'])
		{
			if($l['USER_SUPERPARENT'])
				print 'saratogahigh.com parent contact';
			else
				print 'Verified';
		}
		else
		{
			if($isadmin && strlen($l['USER_UNAME']) == 0)
				print '<span style="color: #660000">Not Registered</span>';
			else
				print '<span style="color: #660000">Not Verified</span>';
		}
		print '</span></h1>';
		print '<form action="userlog.php" method="GET" style="padding-top: 12px;">Search by ID or IP: <input name="id" type="text" size="12"> <input type="submit" value="Go"></form>';
		print '<a href="userlog.php?pass">View Reset Password Requests</a>';
	
		$rsvisits = mysql_query('SELECT * FROM LOG_LIST WHERE LOG_USER=' . $sid . ' ORDER BY LOG_TS DESC');
	
		print '<table width="100%" style="table-layout: fixed"><tr>
			<td style="width: 18em" class="header">Time</td><td class="header">Path</td><td style="width: 8em" class="header">IP</td></tr>';
		while($visit = mysql_fetch_array($rsvisits, MYSQL_ASSOC))
		{
			if($visit['LOG_QUERY'] != '')
				$cururl = $visit['LOG_PATH'] . '?' . $visit['LOG_QUERY'];
			else
				$cururl = $visit['LOG_PATH'];

			print '<tr><td class="data">' . date('D Y M j, h:i:s a', strtotime($visit['LOG_TS'])) . '</td><td class="data"><a href="' . $cururl . '">' . htmlentities($cururl) . '</td><td class="data" title="' . $visit['LOG_BROWSER'] . '">' . $visit['LOG_IP'] . '</td></tr>';
		}
		print '</table>';
	}
}
else
	print $errorM;

include '../inc-footer.php'; ?>
</body>
</html>
