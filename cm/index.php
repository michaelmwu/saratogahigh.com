<?
// Philip Sung | 0x7d3 | saratogahigh.com
// cm/: displays class information

include '../db.php';
include 'cm.php';

if($loggedin && $isvalidated && $filterclass && !$filterteacher)
{
	$rsteachers = mysql_query('SELECT DISTINCT TEACHER_LIST.* FROM TEACHER_LIST INNER JOIN VALIDCLASS_LIST ON VALIDCLASS_TEACHER=TEACHER_ID WHERE VALIDCLASS_COURSE=' . $classid);

	$numteachers = mysql_num_rows($rsteachers);
	
	if($numteachers == 1)
	{
		$t = mysql_fetch_array($rsteachers, MYSQL_ASSOC);
		header('location: http://' . DNAME . '/cm/?class=' . $classid . '&teacher=' . $t['TEACHER_ID']);
	}
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
	<title><?
	if($filterclass)
	{
		print $c['CLASS_NAME'];
		if($filterteacher)
		{
			print ' - ';
			print $t['TEACHER_NAME'];
		}
	}
	else
		print 'Class Information';
		
	?></title>
	<link rel="stylesheet" type="text/css" href="../shs.css">
	<meta name="vs_targetSchema" content="http://schemas.microsoft.com/intellisense/ie5">
	<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
	<style type="text/css"><!--
		td.searchch { font-weight: bold; font-size: medium; background-color: #006666; color: #ffffff }
		h2 { border-bottom: 2px solid #666 }
	--></style>
</head>
<body>

<? include "inc-header.php" ?>

<?

if(!$loggedin)
	print 'Please log in to view this page.';
else if(!$isvalidated)
	print 'Only verified users can view this information.';

if($filterclass)
{
	if($filterteacher)
	{
		// Print title
		print '<div>';
		print '<div style="padding: 3px; margin: 0 3px 0 0; float: left; font-size: large; background-color: #c33; color: #fff">Course Info</div>';
		print '<h1 style="padding: 3px; background-color: #eee; font-size: large; margin: 0; font-weight: normal">';
		if($numteachers > 1)
			print '<a href="./?class=' . $classid . '">';
		print '<span style="font-weight: bold">' . $c['CLASS_NAME'] . '</span>';
		if($numteachers > 1)
			print '</a>';
		print ' ';
		if(!is_null($t['USER_ID']))
			print '<a href="/directory/?id=' . $t['USER_ID'] . '">';
		print $t['TEACHER_NAME'];
		if(!is_null($t['USER_ID']))
			print '</a>';
		print '</h1>';
		print '</div>';
		
		print '<table width="100%" cellpadding="0" cellspacing="4"><tr><td style="vertical-align: top" width="225">';
		
		print '<h2>' . (C_SCHOOLYEAR - 1) . '-' . C_SCHOOLYEAR . ' Schedule</h2>';
		
		// Print a list of teacher's classes
		$result = mysql_query("SELECT DISTINCTROW MAPNODE_ID, ROOM_ID, ROOM_NAME, VALIDCLASS_PER, VALIDCLASS_TERM
				FROM VALIDCLASS_LIST
					LEFT JOIN TEACHERROOM_LIST ON VALIDCLASS_TEACHER=TEACHERROOM_TEACHER AND VALIDCLASS_PER=TEACHERROOM_PER
					LEFT JOIN ROOM_LIST ON ROOM_ID=TEACHERROOM_ROOM
					LEFT JOIN MAPNODE_LIST ON ROOM_ID=MAPNODE_ROOM
				WHERE VALIDCLASS_COURSE=" . $classid . " AND VALIDCLASS_TEACHER= " . $teacherid . "
				ORDER BY VALIDCLASS_PER, VALIDCLASS_TERM") or die("Class query failed");
		
		if(mysql_num_rows($result) > 0)
		{
			print '<table class="datatable">';
			print '<thead><tr><th>Per</th><th>Term</th><th>Room</th></tr></thead>';
			while ($line = mysql_fetch_array($result, MYSQL_ASSOC))
			{
				print "<tr>";
				print "<td align=\"center\">" . $line["VALIDCLASS_PER"] . "</td>";
				print "<td align=\"center\">" . $line["VALIDCLASS_TERM"] . "</td>";

				if($line['ROOM_NAME'] == '')
					print "<td>&nbsp;</td>";
				else if($line['MAPNODE_ID'] == '')
					print "<td>" . $line['ROOM_NAME'] . "</td>";
				else
					print "<td><a href=\"/map/?lfind=" . $line['MAPNODE_ID'] . "&job=locate\">" . $line['ROOM_NAME'] . "</a></td>";
					
				print "</tr>\n";
			}
			print "</table>\n";
		}
		else
		{
			print '<p>No matching records.</p>';
		}

		mysql_free_result($result);
		
		print '</td><td style="vertical-align: top">';
		
		print '<h2>Course Materials</h2>';
		
		print '<ul class="flat" style="font-size: medium; margin-left: 0">';
		
		$rscalendar = mysql_query('SELECT LAYER_ID FROM LAYER_LIST WHERE LAYER_CLASS=' . $classid . ' AND (LAYER_TEACHER Is Null OR LAYER_TEACHER=' . $teacherid . ')');
		if($calendar = mysql_fetch_array($rscalendar, MYSQL_ASSOC))
		{
			print '<li><a style="font-weight: bold" href="/calendar/layer.php?viewset=' . $calendar['LAYER_ID'] . '">Homework Calendar</a><ul class="flat">';
			
			$cid = $calendar['LAYER_ID'];
			include 'calendar.php';
			
			print '</ul></li>';
		}
		
		$rslinks = mysql_query('SELECT * FROM CLASSLINK_LIST WHERE CLASSLINK_COURSE=' . $classid . ' AND CLASSLINK_TEACHER=' . $teacherid);

		if(mysql_num_rows($rslinks) > 0)
		{
			while($clink = mysql_fetch_array($rslinks, MYSQL_ASSOC))
			{
				print '<li style="font-weight: bold"><a href="' . $clink['CLASSLINK_URL'] . '">' . $clink['CLASSLINK_TYPE'] . '</a></li>';
			}
		}

		print '<li><span style="font-weight: bold"><a href="cmview.php?class=' . $classid . '&amp;teacher=' . $teacherid . '">Course Materials</a></span><ul class="flat">';
	
		include 'cmlist.php';
		
		print '</ul></li>';
		
		print '</ul>';

		// Show class rosters
		print "<h2>Classes</h2>";
		
		$rsperiods = mysql_query("SELECT DISTINCTROW VALIDCLASS_PER
			FROM VALIDCLASS_LIST
				INNER JOIN CLASS_LIST ON VALIDCLASS_COURSE=CLASS_LIST.CLASS_ID
				INNER JOIN TEACHER_LIST ON VALIDCLASS_TEACHER=TEACHER_LIST.TEACHER_ID
			WHERE TEACHER_ID=" . $teacherid . " AND CLASS_ID=" . $classid . " ORDER BY VALIDCLASS_PER");

		if(mysql_num_rows($rsperiods) > 0)
		{
			$i = 0;

			print "<table cellpadding=\"0\" cellspacing=\"4\">\n";
			print "<tr>";
			
			while($period = mysql_fetch_array($rsperiods, MYSQL_ASSOC))
			{
				$rsclasses = mysql_query("SELECT DISTINCTROW MAPNODE_ID, ROOM_ID, ROOM_NAME, VALIDCLASS_PER, VALIDCLASS_TERM, CLASS_ID, CLASS_NAME, TEACHER_ID, TEACHER_NAME
				FROM VALIDCLASS_LIST
					LEFT JOIN TEACHERROOM_LIST ON VALIDCLASS_TEACHER=TEACHERROOM_TEACHER AND VALIDCLASS_PER=TEACHERROOM_PER
					LEFT JOIN ROOM_LIST ON ROOM_ID=TEACHERROOM_ROOM
					LEFT JOIN MAPNODE_LIST ON ROOM_ID=MAPNODE_ROOM
					INNER JOIN CLASS_LIST ON VALIDCLASS_COURSE=CLASS_LIST.CLASS_ID
					INNER JOIN TEACHER_LIST ON VALIDCLASS_TEACHER=TEACHER_LIST.TEACHER_ID					
				WHERE VALIDCLASS_TEACHER=" . $teacherid . " AND VALIDCLASS_COURSE=" . $classid . " AND VALIDCLASS_PER=" . $period['VALIDCLASS_PER'] . "
				ORDER BY VALIDCLASS_PER, VALIDCLASS_TERM");
				
				$i++;
				if($i % 3 == 1 && $i > 1)
					print "</tr><tr>";

				print "<td style=\"width: 190px\" valign=\"top\">";
				print '<p class="periodlabel">' . $period["VALIDCLASS_PER"] . '</p>';

				while($thisclass = mysql_fetch_array($rsclasses, MYSQL_ASSOC))
				{
					if($thisclass['VALIDCLASS_TERM'] == 'YEAR')
						$termcond = '';
					else
						$termcond = ' AND (SCHED_TERM="YEAR" OR SCHED_TERM="' . $thisclass['VALIDCLASS_TERM'] . '")';

					$result = mysql_query('SELECT CLASS_LIST.*, USER_LIST.USER_VALIDATED, USER_LIST.USER_ID, USER_LIST.USER_FULLNAME, SCHED_TERM, SCHED_CLASS
					FROM SCHED_LIST
						INNER JOIN CLASS_LIST ON SCHED_CLASS=CLASS_LIST.CLASS_ID
						LEFT JOIN TEACHER_LIST ON SCHED_TEACHER=TEACHER_LIST.TEACHER_ID
						INNER JOIN USER_LIST ON SCHED_USER=USER_LIST.USER_ID
					WHERE SCHED_YEAR=' . C_SCHOOLYEAR . $termcond . ' AND (SCHED_CLASS=' . $classid . ' OR SCHED_CLASS=11) AND SCHED_PER=' . $thisclass['VALIDCLASS_PER'] . ' AND SCHED_TEACHER=' . $teacherid . '
					ORDER BY (SCHED_CLASS=11) DESC, USER_LIST.USER_LN, USER_LIST.USER_FN, USER_LIST.USER_GR') or die("Classes query failed");

					$numstudents = mysql_num_rows($result);

					print "<p class=\"classlabel\">";
					if($thisclass["VALIDCLASS_TERM"] != 'YEAR')
						print $thisclass["VALIDCLASS_TERM"] . ' ';
					print "<span style=\"font-weight: bold\">" . $thisclass["CLASS_NAME"] . "</span><br>" . $thisclass["TEACHER_NAME"];
					print "<br>$numstudents student";
					if ($numstudents != 1)
						print "s";
					print " in this class";
					print "</p>";

					print '<ul class="flat" style="margin: 0 0 2ex 0">';
				
					// Find a matching class calendar, if possible
					$curteacher = mysql_query('SELECT USER_FULLNAME, USER_ID FROM USER_LIST WHERE USER_GR=0 AND USER_TEACHERTAG=' . $teacherid);
					if($cline = mysql_fetch_array($curteacher, MYSQL_ASSOC))
					{
						print '<li><img class="imgicon" src="/imgs/person.gif"><span style="font-weight: bold">';
						printuserlink($cline['USER_FULLNAME'], $cline['USER_ID'], $userid, $sid);
						print '</span></li>';
						
						$curcalendar = mysql_query('SELECT LAYER_ID FROM LAYER_LIST WHERE LAYER_CLASS=' . $classid . ' AND (LAYER_TEACHER Is Null OR LAYER_TEACHER=' . $teacherid . ')');
						
						if(mysql_num_rows($curcalendar) > 0)
						{
							print '<ul class="flat">';
							while($ccal = mysql_fetch_array($curcalendar, MYSQL_ASSOC))
								print '<li><img class="imgicon" src="/calendar/img/calendar.gif"><a href="/calendar/layer.php?viewset=' . $ccal['LAYER_ID'] . '">View Class Calendar</a></li>';
							print '</ul>';
						}
					}					
					
					// Print all students
					while ($line = mysql_fetch_array($result, MYSQL_ASSOC))
					{
						print '<li>';
						// Print user's name and icon
						if($line['USER_VALIDATED'] == 1)
							print '<img class="imgicon" src="/imgs/person.gif">';
						else
							print '<img class="imgicon" title="Unverified user" src="/imgs/unv.gif">';
						printuserlink($line['USER_FULLNAME'], $line['USER_ID'], $userid, $sid);
						if($line['SCHED_CLASS'] == 11)
							print ' <span style="font-weight: bold">TA</span>';
						if($line["SCHED_TERM"] != $thisclass['VALIDCLASS_TERM'])
							print " (" . $line["SCHED_TERM"]. ")";
						print '</li>';
					}
					
					print '</ul>';
				}
				
				print '</td>';
			}
			
			print '</tr></table>';
		}
		
		print '</td></tr></table>';
	}
	else
	{
		print '<div>';
		print '<div style="padding: 3px; margin: 0 3px 0 0; float: left; font-size: large; background-color: #c33; color: #fff">Course Info</div>';
		print '<h1 style="padding: 3px; background-color: #eee; font-size: large; margin: 0">';
		print $c['CLASS_NAME'];
		print '</h1>';
		print '</div>';
		
		print '<table width="100%" cellpadding="0" cellspacing="4"><tr><td style="vertical-align: top" width="225">';
	
		print '<h2>Teachers</h2>';
		
		print '<p style="margin: 0; font-size: medium">Select a teacher to get specific class information:</p>';
		
		while($ts = mysql_fetch_array($rsteachers, MYSQL_ASSOC))
			print '<p style="margin: 0; font-size: medium"><a href="./?class=' . $classid . '&amp;teacher=' . $ts['TEACHER_ID'] . '">' . $c['CLASS_NAME'] . ' - <span style="font-weight: bold">' . $ts['TEACHER_NAME'] . '</span></a></p>';

		print '<h2>' . (C_SCHOOLYEAR - 1) . '-' . C_SCHOOLYEAR . ' Schedule</h2>';
		
		// Print a list of teacher's classes
		$result = mysql_query("SELECT DISTINCTROW MAPNODE_ID, ROOM_ID, ROOM_NAME, VALIDCLASS_PER, VALIDCLASS_TERM, TEACHER_ID, TEACHER_NAME
			FROM VALIDCLASS_LIST
				INNER JOIN TEACHER_LIST ON VALIDCLASS_TEACHER=TEACHER_ID
				LEFT JOIN TEACHERROOM_LIST ON VALIDCLASS_TEACHER=TEACHERROOM_TEACHER AND VALIDCLASS_PER=TEACHERROOM_PER
				LEFT JOIN ROOM_LIST ON ROOM_ID=TEACHERROOM_ROOM
				LEFT JOIN MAPNODE_LIST ON ROOM_ID=MAPNODE_ROOM
			WHERE VALIDCLASS_COURSE=" . $classid . "
			ORDER BY VALIDCLASS_PER, VALIDCLASS_TERM") or die("Class query failed");
		
		if(mysql_num_rows($result) > 0)
		{
			print "<table cellpadding=\"2\" cellspacing=\"2\">\n";
			print "<tr>";
			print "<td class=\"header\">Per</td><td class=\"header\">Term</td><td class=\"header\">Teacher</td><td class=\"header\">Room</td>";
			print "</tr>";
			while ($line = mysql_fetch_array($result, MYSQL_ASSOC))
			{
				print "<tr>";
				print "<td align=\"center\" class=\"data\">" . $line["VALIDCLASS_PER"] . "</td>";
				print "<td align=\"center\" class=\"data\">" . $line["VALIDCLASS_TERM"] . "</td>";
				print "<td class=\"data\">" . $line["TEACHER_NAME"] . "</td>";

				if($line['ROOM_NAME'] == '')
					print "<td class=\"data\">&nbsp;</td>";
				else if($line['MAPNODE_ID'] == '')
					print "<td class=\"data\">" . $line['ROOM_NAME'] . "</td>";
				else
					print "<td class=\"data\"><a href=\"/map/?lfind=" . $line['MAPNODE_ID'] . "&job=locate\">" . $line['ROOM_NAME'] . "</a></td>";
					
				print "</tr>\n";
			}
			print "</table>\n";	
		}
		else
		{
			print '<p>No matching records.</p>';
		}

		mysql_free_result($result);
		
		print '</td><td style="vertical-align: top">';

		// Show materials for the class in general

		print '<h2>Course Materials</h2>';
		
		print '<ul class="flat" style="font-size: medium; margin-left: 0">';
		
/*		$rslinks = mysql_query('SELECT * FROM CLASSLINK_LIST WHERE CLASSLINK_COURSE=' . $classid . ' AND CLASSLINK_TEACHER=' . $teacherid);

		if(mysql_num_rows($rslinks) > 0)
		{
			while($clink = mysql_fetch_array($rslinks, MYSQL_ASSOC))
			{
				print '<li style="font-weight: bold"><a href="' . $clink['CLASSLINK_URL'] . '">' . $clink['CLASSLINK_TYPE'] . '</a></li>';
			}
		} */

		print '<li><span style="font-weight: bold"><a href="cmview.php?class=' . $classid . '">Course Materials (General)</a></span><ul class="flat">';
	
		include 'cmlist.php';
		
		print '</ul></li>';
		
		print '</ul>';

		// Show class rosters
		print "<h2>Classes</h2>";
		
		$rsperiods = mysql_query("SELECT DISTINCTROW VALIDCLASS_PER
			FROM VALIDCLASS_LIST
				INNER JOIN CLASS_LIST ON VALIDCLASS_COURSE=CLASS_LIST.CLASS_ID
				INNER JOIN TEACHER_LIST ON VALIDCLASS_TEACHER=TEACHER_LIST.TEACHER_ID
			WHERE CLASS_ID=" . $classid . " ORDER BY VALIDCLASS_PER");
			
		if(mysql_num_rows($rsperiods) > 0)
		{
			$i = 0;
			
			print "<table cellpadding=\"0\" cellspacing=\"4\">\n";
			print "<tr>";
			
			while($period = mysql_fetch_array($rsperiods, MYSQL_ASSOC))
			{
				$rsclasses = mysql_query("SELECT DISTINCTROW MAPNODE_ID, ROOM_ID, ROOM_NAME, VALIDCLASS_PER, VALIDCLASS_TERM, CLASS_NAME, CLASS_ID, TEACHER_NAME, TEACHER_ID, USER_ID
				FROM VALIDCLASS_LIST
					INNER JOIN CLASS_LIST ON VALIDCLASS_COURSE=CLASS_LIST.CLASS_ID
					INNER JOIN TEACHER_LIST ON VALIDCLASS_TEACHER=TEACHER_LIST.TEACHER_ID
					LEFT JOIN TEACHERROOM_LIST ON VALIDCLASS_TEACHER=TEACHERROOM_TEACHER AND VALIDCLASS_PER=TEACHERROOM_PER
					LEFT JOIN ROOM_LIST ON ROOM_ID=TEACHERROOM_ROOM
					LEFT JOIN MAPNODE_LIST ON ROOM_ID=MAPNODE_ROOM
					LEFT JOIN USER_LIST ON USER_TEACHERTAG=TEACHER_ID
				WHERE CLASS_ID=" . $classid . " AND VALIDCLASS_PER=" . $period['VALIDCLASS_PER'] . "
				ORDER BY VALIDCLASS_PER, VALIDCLASS_TERM");

				$i++;
				if($i % 3 == 1 && $i > 1)
					print "</tr><tr>";

				print "<td style=\"width: 190px\" valign=\"top\">";
				print '<p class="periodlabel">' . $period["VALIDCLASS_PER"] . '</p>';
				
				while($thisclass = mysql_fetch_array($rsclasses, MYSQL_ASSOC))
				{
					$result = mysql_query('SELECT CLASS_LIST.*, USER_LIST.USER_VALIDATED, USER_LIST.USER_ID, USER_LIST.USER_FULLNAME, SCHED_TERM, SCHED_CLASS
					FROM SCHED_LIST
						INNER JOIN CLASS_LIST ON SCHED_CLASS=CLASS_LIST.CLASS_ID
						LEFT JOIN TEACHER_LIST ON SCHED_TEACHER=TEACHER_LIST.TEACHER_ID
						INNER JOIN USER_LIST ON SCHED_USER=USER_LIST.USER_ID
					WHERE SCHED_YEAR=' . C_SCHOOLYEAR . $termcond . ' AND (SCHED_CLASS=' . $thisclass['CLASS_ID'] . ' OR SCHED_CLASS=11) AND SCHED_PER=' . $thisclass['VALIDCLASS_PER'] . ' AND SCHED_TEACHER=' . $thisclass['TEACHER_ID'] . '
					ORDER BY (SCHED_CLASS=11) DESC, USER_LIST.USER_LN, USER_LIST.USER_FN, USER_LIST.USER_GR') or die("Classes query failed");
					print "<p class=\"classlabel\">";

					$numstudents = mysql_num_rows($result);

					if($thisclass["VALIDCLASS_TERM"] != 'YEAR')
						print $thisclass["VALIDCLASS_TERM"] . ' ';
					print "<a href=\"/cm/?class=" . $thisclass["CLASS_ID"] . "&amp;teacher=" . $thisclass["TEACHER_ID"] . "\"><span style=\"font-weight: bold\">" . $thisclass["CLASS_NAME"] . "</span><br>" . $thisclass["TEACHER_NAME"] . "</a>";
					print "<br>$numstudents student";
					if ($numstudents != 1)
						print "s";
					print " in this class";
					print "</p>";

					print '<ul class="flat" style="margin: 0 0 2ex 0">';
					
					// Find a matching class calendar, if possible
					$curteacher = mysql_query('SELECT USER_FULLNAME, USER_ID FROM USER_LIST WHERE USER_GR=0 AND USER_TEACHERTAG=' . $thisclass['TEACHER_ID']);
					if($cline = mysql_fetch_array($curteacher, MYSQL_ASSOC))
					{
						print '<li><img class="imgicon" src="/imgs/person.gif"><span style="font-weight: bold">';
						printuserlink($cline['USER_FULLNAME'], $cline['USER_ID'], $userid, $sid);
						print '</span></li>';
						
						$curcalendar = mysql_query('SELECT LAYER_ID FROM LAYER_LIST WHERE LAYER_CLASS=' . $thisclass['CLASS_ID'] . ' AND (LAYER_TEACHER Is Null OR LAYER_TEACHER=' . $thisclass['TEACHER_ID'] . ')');
						
						if(mysql_num_rows($curcalendar) > 0)
						{
							print '<ul class="flat">';
							while($ccal = mysql_fetch_array($curcalendar, MYSQL_ASSOC))
								print '<li><img class="imgicon" src="/calendar/img/calendar.gif"><a href="/calendar/layer.php?viewset=' . $ccal['LAYER_ID'] . '">View Class Calendar</a></li>';
							print '</ul>';
						}						
					}
					
					if($thisclass['VALIDCLASS_TERM'] == 'YEAR')
						$termcond = '';
					else
						$termcond = ' AND (SCHED_TERM="YEAR" OR SCHED_TERM="' . $thisclass['VALIDCLASS_TERM'] . '")';
				
					// Print all students
					while ($line = mysql_fetch_array($result, MYSQL_ASSOC))
					{
						print '<li>';
						// Print user's name and icon
						if($line['USER_VALIDATED'] == 1)
							print '<img class="imgicon" src="/imgs/person.gif">';
						else
							print '<img class="imgicon" title="Unverified user" src="/imgs/unv.gif">';
						printuserlink($line['USER_FULLNAME'], $line['USER_ID'], $userid, $sid);
						if($line['SCHED_CLASS'] == 11)
							print ' <span style="font-weight: bold">TA</span>';
						if($line["SCHED_TERM"] != $thisclass['VALIDCLASS_TERM'])
							print " (" . $line["SCHED_TERM"]. ")";
						print '</li>';
					}
					
					print '</ul>';
					print '</div>';
				}
				
				print '</td>';
			}
			
			print '</tr></table>';
		}
		
		print '</td></tr></table>';
	}
}
else if($filterclasscat)
{
		print '<div>';
		print '<div style="padding: 3px; margin: 0 3px 0 0; float: left; font-size: large; background-color: #c33; color: #fff">Category Info</div>';
		print '<h1 style="padding: 3px; background-color: #eee; font-size: large; margin: 0">';
		print $cc['CLASSCAT_NAME'];
		print '</h1>';
		print '</div>';
		
		print '<table width="100%" cellpadding="0" cellspacing="4"><tr><td style="vertical-align: top" width="225">';
	
		print '<h2>Teachers</h2>';
		
		print '<p style="margin: 0; font-size: medium">Select a teacher to get class information:</p>';
		
		$rsteachers = mysql_query('SELECT DISTINCT USER_ID, TEACHER_LIST.* FROM CLASSCAT_LIST
					INNER JOIN CLASS_LIST ON CLASS_CATEGORY=CLASSCAT_ID
					INNER JOIN VALIDCLASS_LIST ON CLASS_ID=VALIDCLASS_COURSE
					INNER JOIN TEACHER_LIST ON VALIDCLASS_TEACHER=TEACHER_ID
					LEFT JOIN USER_LIST ON TEACHER_ID=USER_TEACHERTAG
					WHERE CLASSCAT_ID="' . $_GET['classcat'] . '"');
		
		while($ts = mysql_fetch_array($rsteachers, MYSQL_ASSOC))
		{
			if(is_numeric($ts['USER_ID']))
				print '<p style="margin: 0; font-size: medium"><a href="/directory/?id=' . $ts['USER_ID'] . '"><span style="font-weight: bold">' . $ts['TEACHER_NAME'] . '</span></a></p>';
			else
				print '<p style="margin: 0; font-size: medium"><span style="font-weight: bold">' . $ts['TEACHER_NAME'] . '</span></p>';
		}

		print '</td><td style="vertical-align: top">';

		// Show materials for the class in general

		print '<h2>Course Materials</h2>';
		
		print '<ul class="flat" style="font-size: medium; margin-left: 0">';
		
/*		$rslinks = mysql_query('SELECT * FROM CLASSLINK_LIST WHERE CLASSLINK_COURSE=' . $classid . ' AND CLASSLINK_TEACHER=' . $teacherid);

		if(mysql_num_rows($rslinks) > 0)
		{
			while($clink = mysql_fetch_array($rslinks, MYSQL_ASSOC))
			{
				print '<li style="font-weight: bold"><a href="' . $clink['CLASSLINK_URL'] . '">' . $clink['CLASSLINK_TYPE'] . '</a></li>';
			}
		} */

		print '<li><span style="font-weight: bold"><a href="cmview.php?classcat=' . $classcatid . '">Category Materials</a></span><ul class="flat">';
	
		include 'cmlist.php';
		
		print '</ul></li>';
		
		print '</td></tr></table>';
}
else
{
	print '<div>';
	print '<div style="padding: 3px; margin: 0 3px 0 0; float: left; font-size: large; background-color: #c33; color: #fff">Course Info</div>';
	print '<h1 style="padding: 3px; background-color: #eee; font-size: large; margin: 0; font-weight: normal">Select a class...</h1>';
	print '</div>';
	
	print '<h2>Browse</h2>';
	
	print '<table cellpadding="4" cellspacing="2" style="width: 40em">';
	
	$rcats = mysql_query('SELECT CLASSCAT_ID, CLASSCAT_NAME FROM CLASSCAT_LIST ORDER BY CLASSCAT_NAME') or die("Categories query failed.");
	
	// Print by category
	while($k = mysql_fetch_array($rcats, MYSQL_ASSOC))
	{
		print '<tr><td colspan="2" class="searchch"><a style="color: #FFFFFF;" href="/cm/?classcat=' . $k['CLASSCAT_ID'] . '">' . $k['CLASSCAT_NAME'] . '</a></td></tr>';
		print '<tr style="margin-bottom: 10px;"><td valign="top">';
		
		$rclasses = mysql_query('SELECT DISTINCTROW CLASS_ID, CLASS_NAME FROM CLASS_LIST INNER JOIN VALIDCLASS_LIST ON CLASS_ID=VALIDCLASS_COURSE WHERE CLASS_ACTIVE = 1 AND CLASS_CATEGORY=' . $k['CLASSCAT_ID'] . ' ORDER BY CLASS_NAME') or die("Class listing failed.");
		
		// Print classes in this category
		while($l = mysql_fetch_array($rclasses, MYSQL_ASSOC))
			print '<p style="margin: 0"><a href="./?class=' . $l['CLASS_ID'] . '">' . $l['CLASS_NAME'] . '</a></p>';

		print '</td>';
		
		print '<td width="50%" valign="top">';

		// Print teachers who teach classes in this category
		$rclasses = mysql_query('SELECT DISTINCTROW TEACHER_NAME, TEACHER_ID, USER_ID
			FROM CLASS_LIST
				INNER JOIN VALIDCLASS_LIST ON VALIDCLASS_COURSE=CLASS_ID
				INNER JOIN TEACHER_LIST ON VALIDCLASS_TEACHER=TEACHER_ID
				LEFT JOIN USER_LIST ON TEACHER_ID=USER_TEACHERTAG
			WHERE CLASS_CATEGORY=' . $k['CLASSCAT_ID'] . ' ORDER BY TEACHER_NAME') or die("Teacher listing failed.");
	
		while($l = mysql_fetch_array($rclasses, MYSQL_ASSOC))
		{
			print '<p style="margin: 0">';
			if(!is_null($l['USER_ID']))
				print '<a href="/directory/?id=' . $l['USER_ID'] . '">';
			print $l['TEACHER_NAME'];
			if(!is_null($l['USER_ID']))
				print '</a>';
			print '</p>';
		}
		
		print '</td></tr>';
	}
	print '</table>';
}

?>
<? include '../inc-footer.php'; ?>
</body>
</html>