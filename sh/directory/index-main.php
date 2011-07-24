<?
	// Print children
	$rschildren = mysql_query('SELECT * FROM PARENTSTUDENT_LIST INNER JOIN USER_LIST ON PARENTSTUDENT_STUDENT=USER_ID WHERE PARENTSTUDENT_PARENT=' . $sid . ' ORDER BY USER_GR DESC');
	if(mysql_num_rows($rschildren) > 0)
	{
		print '<p style="font-size: medium"><span style="font-weight: bold">Children</span>: ';
		$firstchild = true;
		while($child = mysql_fetch_array($rschildren, MYSQL_ASSOC))
		{
			if(!$firstchild)
				print ', ';
			print '<a href="./?id=' . $child['USER_ID'] . '">' . $child['USER_FULLNAME'] . '</a> ' . GradePrint($child['USER_GR']);
			
			$firstchild = false;
		}
		
		print '</p>';	
	}
	
	// Print parents
	$rsparents = mysql_query('SELECT * FROM PARENTSTUDENT_LIST INNER JOIN USER_LIST ON PARENTSTUDENT_PARENT=USER_ID WHERE PARENTSTUDENT_STUDENT=' . $sid);
	if(mysql_num_rows($rsparents) > 0)
	{
		print '<p style="font-size: medium"><span style="font-weight: bold">Registered Parents/Guardians</span>: ';
		$firstparent = true;
		while($parent = mysql_fetch_array($rsparents, MYSQL_ASSOC))
		{
			if(!$firstparent)
				print ', ';
			print '<a href="./?id=' . $parent['USER_ID'] . '">' . $parent['USER_FULLNAME'] . '</a>';
			
			$firstparent = false;
		}
		
		print '</p>';	
	}
	
	// Contact info
	$displayedstuff = false;
	print '<h2>Contact Information</h2>';
	print '<table class="contactinfo">';
	if($viewingAlum)
	{
		$rsmycollege = mysql_query('SELECT * FROM USERCOLLEGE_LIST INNER JOIN COLLEGE_LIST ON USERCOLLEGE_COLLEGE=COLLEGE_ID WHERE USERCOLLEGE_USER=' . $sid);
		if($mycollege = mysql_fetch_array($rsmycollege, MYSQL_ASSOC))
		{
			if(strlen($mycollege['COLLEGE_URL']) > 0)
				print '<tr><td class="contacttype">College</td><td><a href="' . $mycollege['COLLEGE_URL'] . '">' . $mycollege['COLLEGE_NAME'] . '</a>';
			else
				print '<tr><td class="contacttype">College</td><td>' . $mycollege['COLLEGE_NAME'];

			print ' <a href="college.php?id=' . $mycollege['COLLEGE_ID'] . '" style="font-weight: bold">...</a></td></tr>';
			$displayedstuff = true;
		}
	}

	$rsmyphones = mysql_query('SELECT * FROM PHONE_LIST WHERE PHONE_USER=' . $sid . ' ORDER BY PHONE_TYPE, PHONE_ID');
	while($myphone = mysql_fetch_array($rsmyphones, MYSQL_ASSOC))
	{
		print '<tr><td class="contacttype">' . $myphone['PHONE_TYPE'] . ' Number</td><td>' . $myphone['PHONE_NO'] .'</td></tr>';
		$displayedstuff = true;
	}
	
	$rsmyemails = mysql_query('SELECT * FROM EMAIL_LIST WHERE EMAIL_USER=' . $sid . ' ORDER BY EMAIL_ID');
	while($myemail = mysql_fetch_array($rsmyemails, MYSQL_ASSOC))
	{
		print '<tr><td class="contacttype">Email</td><td><a href="mailto:' . $myemail['EMAIL_EMAIL'] . '">' . $myemail['EMAIL_EMAIL'] .'</a></td></tr>';
		$displayedstuff = true;
	}
	
	$rsmyims = mysql_query('SELECT * FROM IM_LIST INNER JOIN IMSERVICE_LIST ON IM_SERVICE=IMSERVICE_ID WHERE IM_USER=' . $sid . ' ORDER BY IMSERVICE_NAME, IM_STRING');
	while($myim = mysql_fetch_array($rsmyims, MYSQL_ASSOC))
	{
		print '<tr><td class="contacttype">' . $myim['IMSERVICE_NAME'] . '</td><td>';
		if($myim['IMSERVICE_LINKER'] != '')
			print '<a href="' . ereg_replace('%1', urlencode($myim['IM_STRING']), $myim['IMSERVICE_LINKER']) . '">';
		print $myim['IM_STRING'];
		if($myim['IMSERVICE_LINKER'] != '')
			print '</a>';
		print '</td></tr>';		
		$displayedstuff = true;
	}
	
	if(!$displayedstuff)
		print '<tr><td style="color: #999999">None available</td><td>No contact information has been entered.</td></tr>';
		
	print '</table>';

	if($viewingTeacher && $teacherid)
	{
		$rscourses = mysql_query('SELECT DISTINCT TEACHER_NAME, CLASS_LIST.* FROM CLASS_LIST INNER JOIN VALIDCLASS_LIST ON VALIDCLASS_COURSE=CLASS_ID INNER JOIN TEACHER_LIST ON VALIDCLASS_TEACHER=TEACHER_ID WHERE VALIDCLASS_TEACHER=' . $teacherid);	
		print '<h2>Classes Taught</h2>';
		print '<p style="margin: 0; font-size: medium">Select a course to get specific class information:</p>';	
		while($ts = mysql_fetch_array($rscourses, MYSQL_ASSOC))
			print '<p style="margin: 0; font-size: medium"><a href="/cm/?class=' . $ts['CLASS_ID'] . '&amp;teacher=' . $teacherid . '"><span style="font-weight: bold">' . $ts['CLASS_NAME'] . '</span> &mdash; ' . $ts['TEACHER_NAME'] . '</a></p>';
	}

	// Heading
	if($viewingStudent || ($viewingTeacher && $teacherid))
	{
		if($isyou)
			print '<h2>Your ' . (C_SCHOOLYEAR - 1) . '-' . C_SCHOOLYEAR . ' Class Schedule</h2>';
		else
			print '<h2>' . (C_SCHOOLYEAR - 1) . '-' . C_SCHOOLYEAR . ' Class Schedule</h2>';
	}
	
	if($viewingTeacher && $teacherid)
	{
		// Print a list of teacher's classes
		$result = mysql_query("SELECT DISTINCTROW MAPNODE_ID, ROOM_ID, ROOM_NAME, VALIDCLASS_PER, VALIDCLASS_TERM, CLASS_NAME, CLASS_ID, TEACHER_NAME, TEACHER_ID
				FROM VALIDCLASS_LIST
					INNER JOIN CLASS_LIST ON VALIDCLASS_COURSE=CLASS_LIST.CLASS_ID
					INNER JOIN TEACHER_LIST ON VALIDCLASS_TEACHER=TEACHER_LIST.TEACHER_ID
					LEFT JOIN TEACHERROOM_LIST ON VALIDCLASS_TEACHER=TEACHERROOM_TEACHER AND VALIDCLASS_PER=TEACHERROOM_PER
					LEFT JOIN ROOM_LIST ON ROOM_ID=TEACHERROOM_ROOM
					LEFT JOIN MAPNODE_LIST ON ROOM_ID=MAPNODE_ROOM
				WHERE TEACHER_ID=$teacherid
				ORDER BY VALIDCLASS_PER, VALIDCLASS_TERM") or die("Class query failed");
		
		if(mysql_num_rows($result) > 0)
		{
			print "<table cellpadding=\"2\" cellspacing=\"2\">\n";
			print "<tr>";
			print "<td class=\"header\">Per</td><td class=\"header\">Term</td><td class=\"header\">Course</td><td class=\"header\">Room</td>";
			print "</tr>";
			while ($line = mysql_fetch_array($result, MYSQL_ASSOC))
			{
				print "<tr>";
				print "<td align=\"center\" class=\"data\">" . $line["VALIDCLASS_PER"] . "</td>";
				print "<td align=\"center\" class=\"data\">" . $line["VALIDCLASS_TERM"] . "</td>";
				print "<td style=\"font-weight: bold\" class=\"data\"><a href=\"/cm/?class=" . $line["CLASS_ID"] . "\">" . $line["CLASS_NAME"] . "</a></td>";

				if($line['ROOM_NAME'] == '')
					print "<td class=\"data\">&nbsp;</td>";
				else if($line['MAPNODE_ID'] == '')
					print "<td class=\"data\">" . $line['ROOM_NAME'] . "</td>";
				else
					print "<td class=\"data\"><a href=\"/map/?lfind=" . $line['MAPNODE_ID'] . "&amp;job=locate\">" . $line['ROOM_NAME'] . "</a></td>";
					
				print "</tr>\n";
			}
			print "</table>\n";	
			$noclasses = false;
		}
		else
		{
			$noclasses = true;
			
			print '<p>No classes entered.</p>';
		}
			
		mysql_free_result($result);
		
	}
	else if($viewingStudent)
	{
		// Print a list of student's classes
		if(!$isyou)
		{
			$result = mysql_query("SELECT MAPNODE_ID, ROOM_ID, ROOM_NAME, SCHED_LIST.*, CLASS_NAME, CLASS_ID, TEACHER_NAME, TEACHER_ID, R_LIST.SCHED_PER AS R_PER, R_LIST.SCHED_PER Is Null AS R_NULL
				FROM SCHED_LIST
					INNER JOIN USER_LIST ON SCHED_LIST.SCHED_USER=USER_LIST.USER_ID
					INNER JOIN CLASS_LIST ON SCHED_LIST.SCHED_CLASS=CLASS_LIST.CLASS_ID
					LEFT JOIN TEACHER_LIST ON SCHED_LIST.SCHED_TEACHER=TEACHER_LIST.TEACHER_ID
					LEFT JOIN SCHED_LIST AS R_LIST ON R_LIST.SCHED_YEAR=SCHED_LIST.SCHED_YEAR AND R_LIST.SCHED_USER=$userid AND SCHED_LIST.SCHED_CLASS=R_LIST.SCHED_CLASS AND SCHED_LIST.SCHED_TEACHER=R_LIST.SCHED_TEACHER AND (SCHED_LIST.SCHED_TERM=R_LIST.SCHED_TERM || SCHED_LIST.SCHED_TERM = 'YEAR' || R_LIST.SCHED_TERM = 'YEAR')
					LEFT JOIN TEACHERROOM_LIST ON SCHED_LIST.SCHED_TEACHER=TEACHERROOM_TEACHER AND SCHED_LIST.SCHED_PER=TEACHERROOM_PER
					LEFT JOIN ROOM_LIST ON ROOM_ID=TEACHERROOM_ROOM
					LEFT JOIN MAPNODE_LIST ON ROOM_ID=MAPNODE_ROOM
				WHERE USER_ID=$sid AND SCHED_LIST.SCHED_YEAR=" . C_SCHOOLYEAR . "
				ORDER BY SCHED_LIST.SCHED_PER, SCHED_LIST.SCHED_TERM") or die("Class query failed");
				
			$sresult = mysql_query("SELECT SCHEDSPORT_LIST.*, SPORTSEASON_NAME, SPORT_NAME, SPORT_ID, R_LIST.SCHEDSPORT_ID Is Null AS R_NULL
				FROM
					SCHEDSPORT_LIST INNER JOIN USER_LIST ON SCHEDSPORT_LIST.SCHEDSPORT_USER=USER_ID
					INNER JOIN SPORT_LIST ON SCHEDSPORT_LIST.SCHEDSPORT_SPORT=SPORT_ID
					INNER JOIN SPORTSEASON_LIST ON SPORTSEASON_ID=SPORT_SEASON
					LEFT JOIN SCHEDSPORT_LIST AS R_LIST ON R_LIST.SCHEDSPORT_YEAR=" . C_SCHOOLYEAR . " AND R_LIST.SCHEDSPORT_USER=$userid AND SCHEDSPORT_LIST.SCHEDSPORT_SPORT=R_LIST.SCHEDSPORT_SPORT
				WHERE USER_ID=$sid AND SCHEDSPORT_LIST.SCHEDSPORT_YEAR=" . C_SCHOOLYEAR . "
				ORDER BY SPORTSEASON_ID, SPORT_NAME") or die("Sport query failed");
		}
		else
		{
			$result = mysql_query("SELECT MAPNODE_ID, ROOM_ID, ROOM_NAME, SCHED_ID, SCHED_PER, SCHED_TERM, CLASS_NAME, CLASS_ID, TEACHER_NAME, TEACHER_ID
				FROM
					SCHED_LIST INNER JOIN USER_LIST ON SCHED_LIST.SCHED_USER=USER_LIST.USER_ID
					INNER JOIN CLASS_LIST ON SCHED_LIST.SCHED_CLASS=CLASS_LIST.CLASS_ID
					LEFT JOIN TEACHER_LIST ON SCHED_LIST.SCHED_TEACHER=TEACHER_LIST.TEACHER_ID
					LEFT JOIN TEACHERROOM_LIST ON SCHED_TEACHER=TEACHERROOM_TEACHER AND SCHED_PER=TEACHERROOM_PER
					LEFT JOIN ROOM_LIST ON ROOM_ID=TEACHERROOM_ROOM
					LEFT JOIN MAPNODE_LIST ON ROOM_ID=MAPNODE_ROOM
				WHERE USER_ID=$sid AND SCHED_YEAR=" . C_SCHOOLYEAR . "
				ORDER BY SCHED_PER, SCHED_TERM") or die("Class query failed");

			$sresult = mysql_query("SELECT SCHEDSPORT_LIST.*, SPORTSEASON_NAME, SPORT_NAME, SPORT_ID
				FROM
					SCHEDSPORT_LIST INNER JOIN USER_LIST ON SCHEDSPORT_LIST.SCHEDSPORT_USER=USER_ID
					INNER JOIN SPORT_LIST ON SCHEDSPORT_LIST.SCHEDSPORT_SPORT=SPORT_ID
					INNER JOIN SPORTSEASON_LIST ON SPORTSEASON_ID=SPORT_SEASON
				WHERE USER_ID=$sid AND SCHEDSPORT_YEAR=" . C_SCHOOLYEAR . "
				ORDER BY SPORTSEASON_ID, SPORT_NAME") or die("Sport query failed");
		}
		
		if(mysql_num_rows($result) > 0 || mysql_num_rows($sresult) > 0)
		{
			print '<table cellpadding="2" cellspacing="2">';
			
			print '<tr>';
			if($showicons)
				print '<td>&nbsp;</td>';
			print '<td class="header" align="center">Per</td>
				<td class="header" align="center">Term</td>
				<td class="header">Course</td>
				<td class="header">Teacher</td>
				<td class="header">Room</td>';
			if($isyou)
				print '<td></td>';
			print '</tr>';
			
			while($line = mysql_fetch_array($result, MYSQL_ASSOC))
			{
				print '<tr>';
				if($showicons)
				{
					if($line["R_NULL"])
						print "<td><img src=\"imgs/x.gif\" width=\"24\" height=\"20\" alt=\"You are not in this class\"></td>";
					else if($line["R_PER"] == $line["SCHED_PER"])
						print "<td><img src=\"imgs/c.gif\" width=\"24\" height=\"20\" alt=\"You are in the same period\"></td>";
					else if($line["R_PER"] < $line["SCHED_PER"])
						print "<td><img src=\"imgs/u.gif\" width=\"24\" height=\"20\" alt=\"You are in an earlier period\"></td>";
					else if($line["R_PER"] > $line["SCHED_PER"])
						print "<td><img src=\"imgs/d.gif\" width=\"24\" height=\"20\" alt=\"You are in a later period\"></td>";
					print '<!-- ' . $line["R_NULL"] . ' -->';
				}
				
				if($line['SCHED_TERM'] == C_SEMESTER || $line['SCHED_TERM'] == 'YEAR')
					$stylestr = '';
				else
					$stylestr = ' style="color: #aaaaaa"';
			
				print "<td{$stylestr} align=\"center\" class=\"data\">" . $line["SCHED_PER"] . "</td>";
				print "<td{$stylestr} align=\"center\" class=\"data\">" . $line["SCHED_TERM"] . "</td>";
				print "<td style=\"font-weight: bold\" class=\"data\"><a{$stylestr} href=\"/cm/?class=" . $line["CLASS_ID"] . "\">" . $line["CLASS_NAME"] . "</a></td>";
				print "<td class=\"data\"><a{$stylestr} href=\"class.php?teacher=" . $line["TEACHER_ID"] . "\">" . $line["TEACHER_NAME"] . "</a></td>";
				
				if($line['ROOM_NAME'] == '')
					print "<td class=\"data\">&nbsp;</td>";
				else if($line['MAPNODE_ID'] == '')
					print "<td class=\"data\">" . $line['ROOM_NAME'] . "</td>";
				else
					print "<td class=\"data\"><a{$stylestr} href=\"/map/?lfind=" . $line['MAPNODE_ID'] . "&amp;job=locate\">" . $line['ROOM_NAME'] . "</a></td>";
				
				if($canedit)
					print "<td><a href=\"./?id=$sid&amp;del=" . $line["SCHED_ID"] . "\">Delete</a></td>";

				print "</tr>\n";
			}

			while ($line = mysql_fetch_array($sresult, MYSQL_ASSOC))
			{
				print '<tr>';
				if($showicons)
				{
					if($line["R_NULL"])
						print "<td><img src=\"imgs/x.gif\" width=\"24\" height=\"20\" alt=\"You do not take this sport\"></td>";
					else
						print "<td><img src=\"imgs/c.gif\" width=\"24\" height=\"20\" alt=\"You take this sport\"></td>";
				}
				
				print "<td align=\"center\" class=\"data\">&nbsp;</td>";
				print "<td align=\"center\" class=\"data\">" . $line["SPORTSEASON_NAME"] . "</td>";
				print "<td style=\"font-weight: bold\" class=\"data\"><a href=\"sport.php?sport=" . $line["SPORT_ID"] . "\">" . $line["SPORT_NAME"] . "</a></td>";
				print "<td class=\"data\">&nbsp;</td>";
				print "<td class=\"data\">&nbsp;</td>";
				
				if($isyou)
					print "<td><a href=\"./?id=$sid&amp;delsport=" . $line["SCHEDSPORT_ID"] . "\">Delete</a></td>";

				print "</tr>\n";
			}

			print "</table>\n";
			
			$noclasses = false;
		}
		else
		{
			if($canedit)
			{
				if(SITE_ACTIVE)
					print "<p>No classes have yet been entered. Once you enter your schedule, you can see who's in your classes, and easily view your homework assignments each night.</p>";
				else
					print '<p>You cannot yet enter your class schedule for this school year.</p>';
			}
			else
				print "<p>No classes have yet been entered.</p>";
				
			$noclasses = true;
		}
		
		mysql_free_result($result);
	}

	// Add class/sport form
	if($isyou && $viewingStudent && SITE_ACTIVE)
	{
		print '<h2 style="margin-bottom: 0px">Add Class</h2>';
		print '<form action="sched1.php" method="GET"><select name="course">';

		$data = mysql_query('SELECT CLASS_NAME, CLASS_ID FROM CLASS_LIST WHERE CLASS_ACTIVE = 1 ORDER BY CLASS_NAME');
		while ($temp = mysql_fetch_array($data, MYSQL_ASSOC))
			print '<option value="' . $temp['CLASS_ID'] . '">' . $temp['CLASS_NAME'] . '</option>';

		print '</select> <input type="hidden" name="action" value="course"><input type="submit" name="btn" value="Choose Course"></form>';

		print '<h2 style="margin-bottom: 0px">Add Sport</h2>';
		print '<form style="margin: 0px" action="./?id=' . $sid . '" method="POST">';
		print '<select name="sport"><option value="">(Sport)</option>';
		$result = mysql_query("SELECT * FROM SPORT_LIST ORDER BY SPORT_NAME") or die("User query failed");
		while ($line = mysql_fetch_array($result, MYSQL_ASSOC))
			print "<option value=\"" . $line["SPORT_ID"] . "\">" . $line["SPORT_NAME"] . "</option>";
		mysql_free_result($result);
		print "</select> <input type=\"submit\" name=\"btn\" value=\"Add Sport\">";
		print '</form>';
	}

	print $resp;

	
	// Show a notice if not verified
	if(!$isvalidated && $canedit)
	{
		print '<h2>Verification</h2>';
		if($isstudent)
			print '<p>Once you verify your account, you can see a list of all the people who are in your classes, as well as a list of the people who have the most classes in common with you. <a href="/help/validation.php">More information about verification</a></p>';
		else
			print '<p>Once you verify your account, you\'ll be able to see other users\' directory information. <a href="/help/validation.php">More information about verification</a></p>';
	}

	// Show class rosters
	if(($viewingStudent || ($viewingTeacher && $teacherid)) && !$noclasses && $isvalidated)
	{
		if($isyou)
			print "<h2>Your Classes</h2>";
		else
			print "<h2>Classes</h2>";
		
		// Get lists of all students
		if($viewingTeacher && $teacherid)
		{
			$rsperiods = mysql_query("SELECT DISTINCTROW VALIDCLASS_PER
				FROM VALIDCLASS_LIST
					INNER JOIN CLASS_LIST ON VALIDCLASS_COURSE=CLASS_LIST.CLASS_ID
					INNER JOIN TEACHER_LIST ON VALIDCLASS_TEACHER=TEACHER_LIST.TEACHER_ID
				WHERE TEACHER_ID=$teacherid ORDER BY VALIDCLASS_PER");
				
			if(mysql_num_rows($rsperiods) > 0)
			{
				$i = 0;
				
				print "<table cellpadding=\"0\" cellspacing=\"4\">\n";
				print "<tr>";
				
				while($cper = mysql_fetch_array($rsperiods, MYSQL_ASSOC))
				{
					$rsclasses = mysql_query("SELECT DISTINCTROW MAPNODE_ID, ROOM_ID, ROOM_NAME, VALIDCLASS_PER, VALIDCLASS_TERM, CLASS_NAME, CLASS_ID, TEACHER_NAME, TEACHER_ID
					FROM VALIDCLASS_LIST
						INNER JOIN CLASS_LIST ON VALIDCLASS_COURSE=CLASS_LIST.CLASS_ID
						INNER JOIN TEACHER_LIST ON VALIDCLASS_TEACHER=TEACHER_LIST.TEACHER_ID
						LEFT JOIN TEACHERROOM_LIST ON VALIDCLASS_TEACHER=TEACHERROOM_TEACHER AND VALIDCLASS_PER=TEACHERROOM_PER
						LEFT JOIN ROOM_LIST ON ROOM_ID=TEACHERROOM_ROOM
						LEFT JOIN MAPNODE_LIST ON ROOM_ID=MAPNODE_ROOM
					WHERE TEACHER_ID=$teacherid AND VALIDCLASS_PER=" . $cper['VALIDCLASS_PER'] . "
					ORDER BY VALIDCLASS_PER, VALIDCLASS_TERM");
					
					$i++;
					if($i % 4 == 1 && $i > 1)
						print "</tr><tr>";
					print "<td style=\"width: 190px\" valign=\"top\">";
					print '<p class="periodlabel">' . $cper["VALIDCLASS_PER"] . '</p>';
					
					while($cclass = mysql_fetch_array($rsclasses, MYSQL_ASSOC))
					{
						print "<p class=\"classlabel\">";
						if($cclass["VALIDCLASS_TERM"] != 'YEAR')
							print $cclass["VALIDCLASS_TERM"] . ' ';
						print "<a href=\"/cm/?class=" . $cclass["CLASS_ID"] . "&amp;teacher=" . $cclass["TEACHER_ID"] . "\"><span style=\"font-weight: bold\">" . $cclass["CLASS_NAME"] . "</span><br>" . $cclass["TEACHER_NAME"] . "</a>";
						print "</p>";

						print '<ul class="flat" style="margin: 0 0 2ex 0">';
						
						// Find a matching class calendar, if possible
						// Class calendars disabled at request of administration
/*						$curteacher = mysql_query('SELECT USER_FULLNAME, USER_ID FROM USER_LIST WHERE USER_GR=0 AND USER_TEACHERTAG=' . $teacherid);
						if($cline = mysql_fetch_array($curteacher, MYSQL_ASSOC))
						{
							print '<li><img class="imgicon" src="/imgs/person.gif" alt=""><span style="font-weight: bold">';
							printuserlink($cline['USER_FULLNAME'], $cline['USER_ID'], $userid, $sid);
							print '</span></li>';
							
							$curcalendar = mysql_query('SELECT LAYER_ID FROM LAYER_LIST WHERE LAYER_CLASS=' . $cclass['CLASS_ID'] . ' AND (LAYER_TEACHER Is Null OR LAYER_TEACHER=' . $teacherid . ')');
							
							if(mysql_num_rows($curcalendar) > 0)
							{
								print '<ul class="flat">';
								while($ccal = mysql_fetch_array($curcalendar, MYSQL_ASSOC))
									print '<li><img class="imgicon" src="/calendar/img/calendar.gif" alt=""><a href="/calendar/layer.php?viewset=' . $ccal['LAYER_ID'] . '">View Class Calendar</a></li>';
								print '</ul>';
							}
						} */
						
						if($cclass['VALIDCLASS_TERM'] == 'YEAR')
							$termcond = '';
						else
							$termcond = ' AND (SCHED_TERM="YEAR" OR SCHED_TERM="' . $cclass['VALIDCLASS_TERM'] . '")';
										
						$result = mysql_query('SELECT CLASS_LIST.*, USER_LIST.USER_VALIDATED, USER_LIST.USER_ID, USER_LIST.USER_FULLNAME, SCHED_TERM, SCHED_CLASS
						FROM SCHED_LIST
							INNER JOIN CLASS_LIST ON SCHED_CLASS=CLASS_LIST.CLASS_ID
							LEFT JOIN TEACHER_LIST ON SCHED_TEACHER=TEACHER_LIST.TEACHER_ID
							INNER JOIN USER_LIST ON SCHED_USER=USER_LIST.USER_ID
						WHERE SCHED_YEAR=' . C_SCHOOLYEAR . $termcond . ' AND (SCHED_CLASS=' . $cclass['CLASS_ID'] . ' OR SCHED_CLASS=11) AND SCHED_PER=' . $cclass['VALIDCLASS_PER'] . ' AND SCHED_TEACHER=' . $teacherid . '
						ORDER BY (SCHED_CLASS=11) DESC, USER_LIST.USER_LN, USER_LIST.USER_FN, USER_LIST.USER_GR') or die("Classes query failed");
					
						// Print all students
						while ($line = mysql_fetch_array($result, MYSQL_ASSOC))
						{
							print '<li>';
							// Print user's name and icon
							if($line['USER_VALIDATED'] == 1)
								print '<img class="imgicon" src="/imgs/person.gif" alt="">';
							else
								print '<img class="imgicon" title="Unverified user" src="/imgs/unv.gif">';
							printuserlink($line['USER_FULLNAME'], $line['USER_ID'], $userid, $sid);
							if($line['SCHED_CLASS'] == 11)
								print ' <span style="font-weight: bold">TA</span>';
							if($line["SCHED_TERM"] != $cclass['VALIDCLASS_TERM'])
								print " (" . $line["SCHED_TERM"]. ")";
							print '</li>';
						}
						print '</ul>';
					}
					print '</td>';
				}
				print '</tr></table>';
			}	
		}
		else
		{
			$rsperiods = mysql_query("SELECT DISTINCTROW SCHED_PER FROM SCHED_LIST
				WHERE SCHED_USER=$sid AND SCHED_YEAR=" . C_SCHOOLYEAR . " ORDER BY SCHED_PER");

			print "<table cellpadding=\"0\" cellspacing=\"4\">\n";
			print "<tr>";
			
			$i = 0;

			// Print all students
			while($cper = mysql_fetch_array($rsperiods, MYSQL_ASSOC))
			{
				$rsclasses = mysql_query("SELECT * FROM SCHED_LIST
					INNER JOIN TEACHER_LIST ON SCHED_TEACHER=TEACHER_ID
					INNER JOIN CLASS_LIST ON SCHED_CLASS=CLASS_ID
						WHERE SCHED_USER=$sid AND SCHED_YEAR=" . C_SCHOOLYEAR . " AND SCHED_PER=" . $cper['SCHED_PER'] . "
					ORDER BY SCHED_TERM");

				$i++;

				if($i % 4 == 1 && $i > 1)
					print "</tr><tr>";
				print "<td style=\"width: 190px\" valign=\"top\">";
				print '<p class="periodlabel">' . $cper["SCHED_PER"] . '</p>';

				while($cclass = mysql_fetch_array($rsclasses, MYSQL_ASSOC))
				{
					print "<p class=\"classlabel\">";
					if($cclass["SCHED_TERM"] != 'YEAR')
						print $cclass["SCHED_TERM"] . ' ';
					print "<a href=\"/cm/?class=" . $cclass["SCHED_CLASS"] . "&amp;teacher=" . $cclass["SCHED_TEACHER"] . "\"><span style=\"font-weight: bold\">" . $cclass["CLASS_NAME"] . "</span><br>" . $cclass["TEACHER_NAME"] . "</a>";
					print "</p>";

					print '<ul class="flat" style="margin: 0 0 2ex 0">';

/*					// Find a matching class calendar, if possible
					$curteacher = mysql_query('SELECT USER_FULLNAME, USER_ID FROM USER_LIST WHERE USER_GR=0 AND USER_TEACHERTAG=' . $cclass['SCHED_TEACHER']);
					
					if($cline = mysql_fetch_array($curteacher, MYSQL_ASSOC))
					{
						print '<li><img class="imgicon" src="/imgs/person.gif" alt=""><span style="font-weight: bold">';
						printuserlink($cline['USER_FULLNAME'], $cline['USER_ID'], $userid, $sid);
						print '</span></li>';
						
						$curcalendar = mysql_query('SELECT LAYER_ID FROM LAYER_LIST WHERE LAYER_CLASS=' . $cclass['SCHED_CLASS'] . ' AND (LAYER_TEACHER Is Null OR LAYER_TEACHER=' . $cclass['SCHED_TEACHER'] . ')');
						
						if(mysql_num_rows($curcalendar) > 0)
						{
							print '<li><ul class="flat">';
							while($ccal = mysql_fetch_array($curcalendar, MYSQL_ASSOC))
								print '<li><img class="imgicon" src="/calendar/img/calendar.gif" alt=""><a href="/calendar/layer.php?viewset=' . $ccal['LAYER_ID'] . '">View Class Calendar</a></li>';
							print '</ul></li>';
						}
					} */
					
					if($cclass['SCHED_TERM'] == 'YEAR')
						$termcond = '';
					else
						$termcond = ' AND (SCHED_TERM="YEAR" OR SCHED_TERM="' . $cclass['SCHED_TERM'] . '")';
									
					$rsstudents = mysql_query("SELECT USER_VALIDATED, USER_GR, USER_ID, USER_FULLNAME, USER_FN, USER_LN, SCHED_TERM
						FROM SCHED_LIST
							INNER JOIN USER_LIST ON SCHED_USER=USER_ID
						WHERE SCHED_YEAR=" . C_SCHOOLYEAR . " AND SCHED_CLASS=" . $cclass['SCHED_CLASS'] . " AND SCHED_TEACHER=" . $cclass['SCHED_TEACHER'] . " AND SCHED_PER=" . $cclass['SCHED_PER'] . $termcond . "
						ORDER BY USER_LN, USER_FN, USER_GR") or die("Classes query failed");

					while($cclassmate = mysql_fetch_array($rsstudents, MYSQL_ASSOC))
					{
						print '<li>';
						if($cclassmate['USER_VALIDATED'] == 1)
							print '<img class="imgicon" src="/imgs/person.gif" alt="">';
						else
							print '<img class="imgicon" title="Unverified user" src="/imgs/unv.gif">';
						printuserlink($cclassmate['USER_FULLNAME'], $cclassmate['USER_ID'], $userid, $sid);
						if($cclass["SCHED_TERM"] == 'YEAR' && $cclass["SCHED_TERM"] != $cclassmate["SCHED_TERM"])
							print " ({$cclassmate["SCHED_TERM"]})";
						print '</li>';
					}
					print '</ul>';
				}
				print '</td>';
			}

			print "</tr></table>";
		}
	}

	if(!$noclasses && $viewingStudent && $isvalidated)
		include 'freqs.php';
?>