<?
include '../db.php';
include '../calendar/cal.php';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
	<head>
		<title>SHdc Control Panel</title>
		<link rel="stylesheet" type="text/css" href="../shs.css">
		<meta name="vs_targetSchema" content="http://schemas.microsoft.com/intellisense/ie5">
		<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
		<link rel="stylesheet" type="text/css" href="admin.css">
		<style type="text/css"><!--
			div.cpmodule { margin: 3px; padding: 2px; border: 1px #c0c0c0 solid }
			div.cpmodule h1 { margin: 1px; font-size: medium; border-bottom: 2px solid #cc3333; color: #cc3333; font-weight: bold; letter-spacing: 2pt }
			div.cpmodule h2 { margin: 10px 0 0 0; font-size: medium; color: #880000; font-weight: bold; letter-spacing: 1pt }
		--></style>
	</head>
	<body>

<? include "inc-header.php" ?>

<?
if($isstaff)
{
?>
	<table id="maintable" cellspacing="0" cellpadding="0" width="100%"><tr><td width="200" style="vertical-align: top">
<?
	$ts = getdate(CURRENT_TIME);
	$earliest_date = date(TIME_FORMAT_SQL, mktime($ts['hours'], $ts['minutes'], $ts['seconds'], $ts['mon'], $ts['mday'] - 7, $ts['year']));
?>
<div class="cpmodule">
<h1>Project Tasks</h1>
<table style="width: 100%" cellpadding="0" cellspacing="1" border="0">
<tr><td style="font-weight: bold"><a href="tasklist.php">Active Tasks</a></td><td style="text-align: right">#</td><td style="text-align: right">&#0931;</td></tr>
<?
	$tasks = mysql_query('SELECT TASKPRIORITY_LIST.*, COUNT(TASK_ID) AS C FROM TASK_LIST
		INNER JOIN TASKPRIORITY_LIST ON TASK_PRIORITY=TASKPRIORITY_ID
		INNER JOIN TASKCAT_LIST ON TASK_CAT=TASKCAT_ID
		WHERE TASK_ARCHIVED=0
		GROUP BY TASKPRIORITY_ID ORDER BY TASKPRIORITY_ID');
	$total = 0;
	while($task = mysql_fetch_array($tasks, MYSQL_ASSOC))
	{
		$total += $task['C'];
		print '<tr><td style="';
		print 'padding-left: 1em; ' . $task['TASKPRIORITY_STYLESTR'] . '">' . $task['TASKPRIORITY_NAME'] . '</td>
		<td style="text-align: right">' . $task['C'] . '</td><td style="text-align: right">' . $total . '</td></tr>';
	}

	$rsfinished = mysql_query("SELECT TASK_ID, TASK_TITLE, USER_FN, USER_LN, TASK_CREATED
		FROM TASK_LIST
			INNER JOIN TASKCAT_LIST ON TASK_CAT=TASKCAT_ID
			INNER JOIN USER_LIST ON TASK_AUTHOR=USER_ID
		WHERE TASK_ARCHIVED=0 AND TASK_CREATED>='$earliest_date'");
	
	print '<tr><td style="font-weight: bold">New</td><td style="text-align: right">' . mysql_num_rows($rsfinished) . '</td><td></td></tr>';
	
	while($finished = mysql_fetch_array($rsfinished, MYSQL_ASSOC))
	{
		print '<tr><td colspan="3"><p style="text-indent: -1em; margin: 0 0 0 2em; font-size: x-small"><a href="edittask.php?edit=' . $finished['TASK_ID'] . '">' . $finished['TASK_TITLE'] . '</a> ' . substr($finished['USER_FN'], 0, 1) . substr($finished['USER_LN'], 0, 1) . '&nbsp;' . date('n/j', strtotime($finished['TASK_CREATED'])) . '</p></td></tr>';
	}

	$archived = mysql_query("SELECT COUNT(*) AS C FROM TASK_LIST WHERE TASK_ARCHIVED=1");
	$numarchived = mysql_fetch_array($archived, MYSQL_ASSOC);
	
	print '<tr><td style="font-weight: bold">Archived</td><td style="text-align: right">' . $numarchived['C'] . '</td><td></td></tr>';
?>
</table>	
<?	
	print '</div>';

	if($isadmin)
	{
		print '<div class="cpmodule"><h1>Active Comments</h1>';
		print '<table style="width: 100%">';
	
		$rscomments = mysql_query('SELECT COUNT(*) AS C FROM COMMENT_LIST WHERE COMMENT_ARCHIVED=0');
		$numcomments = mysql_fetch_array($rscomments, MYSQL_ASSOC);
		
		print '<tr style="font-weight: bold"><td>Total</td><td style="text-align: right">' . $numcomments['C'] . '</td></tr>';

		$rscategories = mysql_query('SELECT COMMENTCAT_ID, COMMENTCAT_NAME, COUNT(COMMENT_ID) AS C FROM COMMENTCAT_LIST LEFT JOIN COMMENT_LIST ON COMMENTCAT_ID=COMMENT_CAT AND COMMENT_ARCHIVED=0 GROUP BY COMMENTCAT_ID');

		while($category = mysql_fetch_array($rscategories, MYSQL_ASSOC))
		{
			print '<tr><td style="padding-left: 1em"><a href="comments.php?cat=' . $category['COMMENTCAT_ID'] . '">' . $category['COMMENTCAT_NAME'] . '</a></td><td style="text-align: right">' . $category['C'] . '</td></tr>';
		}
		
		print '</table></div>';
	}
	
	
	print '<div class="cpmodule"><h1>Staff</h1>';
	print '<table style="width: 100%">';
	
	$rsstaff = mysql_query('SELECT USER_FULLNAME, USER_ID, USER_STATUS FROM USER_LIST WHERE USER_STATUS > 0 ORDER BY USER_STATUS DESC, USER_LN, USER_FN');
	
	$cstatus = 0;
	
	while($staffmember = mysql_fetch_array($rsstaff, MYSQL_ASSOC))
	{
		if($staffmember['USER_STATUS'] != $cstatus)
		{
			$cstatus = $staffmember['USER_STATUS'];
			print '<tr><td style="font-weight: bold">' . StatusPrint($staffmember['USER_STATUS']) . '</td></tr>';
		}
		print '<tr><td style="padding-left: 1em"><a href="/directory/?id=' . $staffmember['USER_ID'] . '">' . $staffmember['USER_FULLNAME'] . '</a></td></tr>';
	}
			
	print '</table></div>';
?>
	
	

	</td><td width="280" style="vertical-align: top">
	
	<?
	print '<div class="cpmodule"><h1>Control Panels</h1>';
	print '<table>';
	
	$categories = mysql_query('SELECT DISTINCT ADMINCAT_NAME, ADMINCAT_ID
		FROM ADMINLINK_LIST
			INNER JOIN ADMINCAT_LIST ON ADMINLINK_CAT=ADMINCAT_ID
			INNER JOIN ADMINPAGE_LIST ON ADMINLINK_PAGE=ADMINPAGE_ID
		WHERE ADMINPAGE_PERMISSION<=' . $userR['USER_STATUS'] . '
		ORDER BY ADMINCAT_NAME');

	while($category = mysql_fetch_array($categories, MYSQL_ASSOC))
	{
		print '<tr><td style="font-weight: bold">' . $category['ADMINCAT_NAME'] . '</td><td></td></tr>';
	
		$adminpages = mysql_query('SELECT ADMINLINK_NAME, ADMINPAGE_PATH, ADMINLINK_QUERY, ADMINPAGE_PERMISSION
			FROM ADMINLINK_LIST
				INNER JOIN ADMINCAT_LIST ON ADMINLINK_CAT=ADMINCAT_ID
				INNER JOIN ADMINPAGE_LIST ON ADMINLINK_PAGE=ADMINPAGE_ID
			WHERE ADMINPAGE_PERMISSION<=' . $userR['USER_STATUS'] . ' AND ADMINCAT_ID=' . $category['ADMINCAT_ID'] . '
			ORDER BY ADMINLINK_NAME');
			
		while($page = mysql_fetch_array($adminpages, MYSQL_ASSOC))
		{
			print '<tr><td style="padding-left: 1em; font-weight: bold"><a href="' . $page['ADMINPAGE_PATH'] . $page['ADMINLINK_QUERY'] . '">' . $page['ADMINLINK_NAME'] . '</a></td><td>';
			
			print StatusPrint($page['ADMINPAGE_PERMISSION']);
			
			print '</td></tr>';
		}
	}
	
	print '<tr><td style="font-weight: bold">Auxiliary Pages</td><td></td></tr>';

	$auxpages = mysql_query('SELECT ADMINPAGE_PATH, ADMINPAGE_ACTIONNAME, ADMINPAGE_PERMISSION
		FROM ADMINPAGE_LIST
			LEFT JOIN ADMINLINK_LIST ON ADMINLINK_PAGE=ADMINPAGE_ID
		WHERE ADMINPAGE_PERMISSION<=' . $userR['USER_STATUS'] . ' AND ADMINLINK_ID Is Null
		ORDER BY ADMINPAGE_ACTIONNAME, ADMINPAGE_PATH');
		
	while($page = mysql_fetch_array($auxpages, MYSQL_ASSOC))
	{
		print '<tr><td style="padding-left: 1em; font-weight: bold">';
		if($page['ADMINPAGE_ACTIONNAME'] == '')
			print $page['ADMINPAGE_PATH'];
		else
			print $page['ADMINPAGE_ACTIONNAME'];

		print '</td><td>';
		
		if($page['ADMINPAGE_PERMISSION'] == 1)
			print 'Staff';
		else if($page['ADMINPAGE_PERMISSION'] == 2)
			print 'Admin';
		else
			print 'Programmer';
		
		print '</td></tr>';
	}
	
	print '</table></div>';
	?>
	</td><td style="vertical-align: top"><div class="cpmodule">
		<h1>Quick Stats</h1>
	<?	$rsdonations = mysql_query("SELECT COUNT(QARESP_RESP_TEXT) AS A, SUM(TRIM(LEADING '$' FROM QARESP_RESP_TEXT)) AS C FROM QARESP_LIST WHERE QARESP_QUESTION = 573") or die("Query failed"); 
	$donations = mysql_fetch_array($rsdonations, MYSQL_ASSOC);
	print $donations['A'] . ' donations totalling $' . $donations['C']; ?>
	<h1>Login Distributions</h1>
	<h2><span style="font-weight: bold">Last 24 Hours</h2>
	<?
	$rsallusers = mysql_query("SELECT USER_GR, COUNT(USER_ID) FROM USER_LIST WHERE USER_LASTLOGIN > ADDDATE('" . date(TIME_FORMAT_SQL, CURRENT_TIME) . "', INTERVAL -1 DAY) GROUP BY USER_GR ORDER BY USER_GR ASC") or die("Query failed");
	print '<table cellpadding="1" cellspacing="0">';
	$sumstudents = 0;
	$sumall = 0;
	while($allusers =mysql_fetch_array($rsallusers, MYSQL_ASSOC))
	{
		print '<tr><td style="font-weight: bold">' . GradePrint($allusers['USER_GR']) . '</td><td style="text-align: right"><a href="logs.php?grade=' . $allusers['USER_GR'] . '">' . $allusers['COUNT(USER_ID)'] . '</a></td></tr>';
		$sumall += $allusers['COUNT(USER_ID)'];
		if(IsStudent($allusers['USER_GR']))
			$sumstudents += $allusers['COUNT(USER_ID)'];
	}
	print '<tr style="font-weight: bold"><td style="color: #aa0000">Students</td><td style="text-align: right">' . $sumstudents . '</td></tr>';
	print '<tr style="font-weight: bold"><td style="color: #aa0000">Total</td><td style="text-align: right">' . $sumall . '</td></tr>';
	print '</table>';
	?>
	
	<?
	
	$NUM_DAYS = 21;
	
	print '<h2>Last ' . $NUM_DAYS . ' Days</h2>';
	
	?>
	<table style="table-layout: fixed" width="100%" cellpadding="1" cellspacing="0" border="0">
	<?
	
	$rsverified = mysql_query('SELECT COUNT(*) FROM USER_LIST WHERE USER_VALIDATED=1');
	$verified = mysql_fetch_array($rsverified, MYSQL_ASSOC);

	$currenttime = CURRENT_TIME;
	$TOTAL_WIDTH = 1080;
	
	$curparts = getdate($currenttime);
	
	$m = 1;
	$cumulative = 0;
	
	for($i = 0; $i < $NUM_DAYS; $i++)
	{
		$cm = $curparts['mon'];
		$cd = $curparts['mday'] - $i;
		$cy = $curparts['year'];
		
		$cdate = mktime(0,0,0,$cm,$cd,$cy);
		$ndate = mktime(0,0,0,$cm,$cd+1,$cy);
		
		$c = mysql_query('SELECT COUNT(*) as C
			FROM USER_LIST WHERE
			USER_LASTLOGIN >= "' . date(TIME_FORMAT_SQL, $cdate) . '" AND
			USER_LASTLOGIN <  "' . date(TIME_FORMAT_SQL, $ndate) . '"');
			
		$x = mysql_fetch_array($c, MYSQL_ASSOC);
			
		$a[$i] = $cdate;
		$b[$i] = $x['C'];
		$cumulative += $x['C'];
		$cumu[$i] = $cumulative;
		if($x['C'] > $m)
			$m = $x['C'];
	}
	
	$cumuprintthreshold = 100;
	$printthreshold = 10;
	
	for($i = 0; $i < $NUM_DAYS; $i++)
	{
		if(date('w', $a[$i]) == 0 || date('w', $a[$i]) == 6)
		{
			$darkbarcolor = '#000066';
			$barcolor = '#000099';
		}
		else
		{
			$darkbarcolor = '#663333';
			$barcolor = '#cc3333';
		}
	
		$daywidth = floor($b[$i]/$m*100);
		$cumuwidth = floor($cumu[$i]/$verified['COUNT(*)']*100);

		print '<tr';
		if(floor($i / 7) % 2 == 1)
			print ' style="background-color: #dddddd"';
		print '>';
		print '<td style="text-align: right; width: 1.5em">' . date('d', $a[$i]) . '</td>';

		if($b[$i] > 0)
		{
			print '<td style="text-align: right; width: 2em"><a href="logs.php?range=' . date('Ymd', $a[$i]) . '">' . $b[$i] . '</a></td>';
		}
		else
		{
			print '<td style="text-align: right; width: 2em">0</td>';
		}

		print '<td style="text-align: right; width: 2.5em">';
		if($cumu[$i] >= $cumuprintthreshold || $i == NUM_DAYS - 1)
		{
			print '/' . $cumu[$i];
			$cumuprintthreshold = 100 * floor(($cumu[$i] / 100) + 1);
		}
		print '</td>';
		print '<td>';

		if($daywidth >= $cumuwidth)
		{
			if($cumuwidth > 0)
				print '<div style="float: left; background-color: ' . $darkbarcolor . '; width: ' . $cumuwidth . '%">&nbsp;</div>';
			if($daywidth > $cumuwidth)
			{
				print '<div style="float: left; color: #ffffff; background-color: ' . $barcolor . '; width: ' . ($daywidth - $cumuwidth) . '%">';
				if($cumuwidth >= $printthreshold || $i == NUM_DAYS - 1)
				{
					print $cumuwidth . '%';
					$printthreshold = 10 * floor(($cumuwidth / 10) + 1);
				}
				print '&nbsp;</div>';
			}
			else
			{
				if($cumuwidth >= $printthreshold)
				{
					print '<div style="float: left; color: #999999">' . $cumuwidth . '%</div>';
					$printthreshold = 10 * floor(($cumuwidth / 10) + 1);
				}
			}
		}
		else
		{
			if($daywidth > 0)
				print '<div style="float: left; background-color: ' . $barcolor . '; width: ' . $daywidth . '%">&nbsp;</div>';
			if($daywidth < $cumuwidth)
				print '<div style="float: left; background-color: #999999; width: ' . ($cumuwidth - $daywidth) . '%">&nbsp;</div>';
			if($cumuwidth >= $printthreshold)
			{
				print '<div style="float: left; color: #999999">' . $cumuwidth . '%</div>';
				$printthreshold = 10 * floor(($cumuwidth / 10) + 1);
			}
		}
		print '</td></tr>';
	}
	
	?>
	
	</table>
	
	</div>
	</td></tr></table>
	
	<? include '../inc-footer.php'; ?>
	
<?
}
?>
</body>
</html>