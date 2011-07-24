<?
// Philip Sung | 0x7d3 | saratogahigh.com
// directory/: displays directory information (schedule and/or contact information) for a user.
include '../db.php';

$sid = '';     // ID of target user
$errorM = '';  // Fatal error message, if any
$resp = '';    // Response message to user, if any

$canedit = false; // Whether I can modify user's 
$isyou = false;   // Whether I am the target

if($loggedin)
{
	// Read target id from the query string, or, if this is not available, redirect to my own schedule
	if(is_numeric($_GET["id"]))
		$sid = $_GET["id"];
	else
	{
		header("location: ./?id=$userid");
		// Halt script to ensure that redirect goes through
		exit(0);
	}

	// Set appropriate permissions
	if($userR["USER_ID"] == $sid)
	{
		$canedit = true;
		$isyou = true;
	}
	else if($isadmin)
	{
		$canedit = true;
		$isyou = false;
	}
	else
	{
		$canedit = false;
		$isyou = false;
	}

	// Update record as necessary, if an admin asks for it
	if($isadmin)
	{
		if($_GET['validate'] == 'true')
		{
			mysql_query("UPDATE USER_LIST SET USER_VALIDATED=1 WHERE USER_ID=" . $sid) or die("User query failed");
		}
	}

	// Grab user record from database
	$result = mysql_query("SELECT *, USER_LASTLOGIN Is Null as TSNULL, USER_LASTLOGIN AS TS FROM USER_LIST WHERE USER_ID=" . $sid) or die("User query failed");
	
	// You can view someone else's schedule if it is your own OR if you are a verified user.
	if($isvalidated || $isyou)
	{
		if($l = mysql_fetch_array($result, MYSQL_ASSOC))
		{
			$viewingStudent = IsStudent($l['USER_GR']);
			$viewingTeacher = IsTeacher($l['USER_GR']);
			$viewingAlum = IsAlum($l['USER_GR']);

			if($viewingTeacher)
				$teacherid = $l['USER_TEACHERTAG'];
			
			$showRecord = true;
		}
		else
			$errorM = '<p>That user couldn\'t be found.</a><p>';
	}
	else
		$errorM = '<p>You need to verify your account in order to access others\' directory information. <a href="/help/validation.php">More information about verification</a><p>';
	
	mysql_free_result($result);

	// Show icons next to schedule
	$showicons = !$isyou && ($viewingStudent && $isstudent);

	// Update record as necessary at user's request
	if($showRecord && $canedit && SITE_ACTIVE)
	{
		// Store the values posted from the form, so we can fill them in later.
		$c_class = 0;
		$c_teacher = 0;
		$c_per = 0;
		$c_term = '';
	
		// Add a class
	$course = $_GET['course'];
	$teacher = $_GET['teacher'];
	$term = $_GET['term'];
	$period = $_GET['period'];
	$step = 0;

	if ($_GET['action'] == "course")
	  $step = 1;
	else if ($_GET['action'] == "teacher")
	  $step = 2;
	else if ($_GET['action'] == 'period')
	  $step = 3; 

		// Add a sport
		else if($_POST["btn"] == 'Add Sport')
		{
			if(is_numeric($_POST['sport']))
			{
				// See if there is already that sport
				$cc = mysql_query('SELECT SCHEDSPORT_ID FROM SCHEDSPORT_LIST WHERE SCHEDSPORT_YEAR=' . C_SCHOOLYEAR . ' AND SCHEDSPORT_USER=' . $sid . ' AND SCHEDSPORT_SPORT=' . $_POST['sport']);

				// OK to insert (no conflicting records)
				if(mysql_num_rows($cc) == 0)
				{
					mysql_query("INSERT INTO SCHEDSPORT_LIST (SCHEDSPORT_YEAR, SCHEDSPORT_USER, SCHEDSPORT_SPORT) VALUES (" . C_SCHOOLYEAR . ",$sid," . $_POST["sport"] . ")") or die("Insertion failed");
					$resp = '<p style="color: #008000">Sport successfully added.</p>';
				}
			}
			else
				$resp = "<p style=\"color: #800000\">Please select a sport.</p>";
		}
		else if(is_numeric($_GET["del"]))
		{
			mysql_query("DELETE FROM SCHED_LIST WHERE SCHED_ID=" . $_GET["del"] . " AND SCHED_YEAR=" . C_SCHOOLYEAR . " AND SCHED_USER=$sid") or die("Deletion failed");
			$resp = '<p style="color: #008000">Class deleted.</p>';
		}

		else if(is_numeric($_GET["delsport"]))
		{
			mysql_query("DELETE FROM SCHEDSPORT_LIST WHERE SCHEDSPORT_ID=" . $_GET["delsport"] . " AND SCHEDSPORT_YEAR=" . C_SCHOOLYEAR . " AND SCHEDSPORT_USER=$sid") or die("Deletion failed");
			$resp = '<p style="color: #008000">Sport deleted.</p>';
		}
	}
}
else
{
	forceLogin();
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
	<title><?
if($showRecord)
	print $l['USER_FN'] . " " . $l['USER_LN'];
else
	print "SaratogaHigh.com Directory";
?></title>
	<link rel="stylesheet" type="text/css" href="../shs.css">
	<style type="text/css"><!--
#directory-navbar { float: left; width: 100%; background: #ffffff url("/imgs/nav/navred-bottom.gif") repeat-x bottom; font-family: sans-serif; font-size: 95%; margin-bottom: 1em }
#directory-navbar ul { margin: 0; padding: 5px 5px 0; list-style: none; }
#directory-navbar li { float: left; background-image: url("/imgs/nav/nav-right.gif"); background-position: right top; background-repeat: no-repeat; padding: 0; margin: 0 2px 0 0; }
#directory-navbar li a { background-image: url("/imgs/nav/nav-left.gif"); display: block; padding: 5px 7px 3px 7px; color: #c33; background-position: left top; background-repeat: no-repeat; }
#directory-navbar li a:hover { color: #039 }
#directory-navbar .current a   { padding-bottom: 5px; font-weight: bold; color: #000099; }

<?
if($isyou)
{
?>
		a.lnkm {font-weight: bold}
<?
}
else
{
?>
		a.lnks {font-weight: bold}
<?
}
?>
	--></style>
</head>
<body>

<? include "inc-header.php" ?>

<?
if($errorM == '')
{
	// Print name
	print '<h1 style="letter-spacing: 1pt">' . $l['USER_FN'] . ' ' . $l['USER_LN'] . '<br>';
	print '<span style="font-size: smaller">' . GradePrint($l['USER_GR']);
	if($l['USER_VALIDATED'])
	{
		if($l['USER_SUPERPARENT'])
			print ' &mdash; saratogahigh.com parent contact';
		else
			print '';
	}
	else
	{
		if($isadmin && strlen($l['USER_UNAME']) == 0)
			print ' &mdash; <span style="color: #660000">Not Registered</span>';
		else
			print ' &mdash; <span style="color: #660000">Not Verified</span>';
	}
	print '</span></h1>';
	
	// Print toolbar with options
	print '<p>';
		// MAIL
		if(($sid != $userid) && $isvalidated && $l['USER_VALIDATED'])
			print "<span class=\"toolbar\"><a href=\"/mail/compose.php?sendto=$sid\">Send&nbsp;Mail</a></span> ";
	
		// ROUTING
		if(($sid != $userid) && $isstudent && $viewingStudent && SITE_ACTIVE)
			print "<span class=\"toolbar\"><a href=\"passoff.php?to=$sid&amp;from=$userid\">Route&nbsp;To</a></span> <span class=\"toolbar\"><a href=\"passoff.php?to=$userid&amp;from=$sid\">Route&nbsp;From</a></span> ";

		// EDIT INFO
		if($canedit)
		{
			print "<span class=\"toolbar\"><a href=\"../edit-user.php?id=$sid\">Edit&nbsp;Account&nbsp;Info</a></span> ";
			if($isyou)
				print "<span class=\"toolbar\"><a href=\"contact.php\">Edit&nbsp;Contact&nbsp;Info</a></span> ";
		}
		// RESET PW, LOGIN AS, VALFORM, VERIFY
		if($isadmin && !$isyou)
		{
			if(strlen($l['USER_UNAME']) > 0)
			{
				print "<span class=\"toolbar\"><a href=\"../shcp/resetpw.php?rptry=$sid\">Reset&nbsp;Password</a></span> ";
				print "<span class=\"toolbar\"><a href=\"../shcp/loginas.php?loginas=$sid\">Login&nbsp;As</a></span> ";
			}
		}
		if($isadmin || $userid == 4416)
		{
			if(!$l['USER_VALIDATED'] || $l['USER_UNAME'] == '')
				print "<span class=\"toolbar\"><a href=\"../shcp/valform.php?sid={$sid}\">Duplicate&nbsp;Verification&nbsp;Form</a></span> ";
		}
		if($isadmin)
		{
			if(!$l['USER_VALIDATED'] && $l['USER_UNAME'] != '')
				print "<span class=\"toolbar\"><a href=\"./?id=$sid&amp;validate=true\">Verify</a></span> ";

			if(!$l['TSNULL'])
				print "<span class=\"toolbar\"><a href=\"/shcp/userlog.php?id=" . $sid . "\">Log</a> " . date('j M Y', strtotime($l['TS'])) . "</span> ";
		}
	
	print '</p>';

	if($_GET['page'] == "calendars")
		$currentpage = "calendars";
	else if($_GET['page'] == "archived")
		$currentpage = "archived";
	else
		$currentpage = "main";

	print '<div id="directory-navbar"><ul>';
	print '<li style="font-weight: bold"' . ($currentpage == "main" ? ' class="current"' : '') . '><a href="./?id=' . $sid . '">General Info</a></li>';

	// CALENDARS
	$rsadmincals = mysql_query('SELECT COUNT(*) FROM LAYER_LIST INNER JOIN LAYERUSER_LIST ON LAYER_ID=LAYERUSER_LAYER AND LAYERUSER_USER=' . $sid . ' AND LAYERUSER_ACCESS=3 WHERE LAYER_OPEN=1 ORDER BY LAYER_TITLE');
	$admincals = mysql_fetch_array($rsadmincals, MYSQL_ASSOC);
	if($admincals['COUNT(*)'] > 0)
	{
		print '<li' . ($currentpage == "calendars" ? ' class="current"' : '') . '><a href="./?id=' . $sid . '&page=calendars">';
		print $admincals['COUNT(*)'] . ' Public Calendar';
		if($admincals['COUNT(*)'] != 1)
			print 's';
		print '</a></li>';
	}

	// ARCHIVED
	if($viewingStudent || $viewingAlum)
	{
		$archivedclasses = mysql_query('SELECT COUNT(*) FROM SCHED_LIST WHERE SCHED_USER=' . $sid . ' AND SCHED_YEAR<' . C_SCHOOLYEAR);
		
		$numclasses = mysql_fetch_array($archivedclasses, MYSQL_ASSOC);
		
		if($numclasses['COUNT(*)'] > 0)
		{
			print '<li' . ($currentpage == "archived" ? ' class="current"' : '') . '><a href="./?id=' . $sid . '&page=archived">';
			print 'Archived Schedules';
			print '</a></li>';
		}
	}
	
	print '</ul></div>';

	if($currentpage == 'main')	
		include 'index-main.php';
	else if($currentpage == 'calendars')
		include 'index-calendars.php';
	else if($currentpage == 'archived')
		include 'index-archived.php';
}
else
	print $errorM;

?>
<? include '../inc-footer.php'; ?>
</body>
</html>
