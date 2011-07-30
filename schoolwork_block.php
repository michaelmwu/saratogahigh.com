	<?

	$conflicts = mysql_query("SELECT AL.LAYER_CLASS AS A_CLASS, AL.LAYER_TEACHER AS A_TEACHER, BL.LAYER_CLASS AS B_CLASS, BL.LAYER_TEACHER AS B_TEACHER, AL.LAYER_ID AS A_LAYERID, ACL.CLASS_SHORTNAME AS A_CLASSNAME, BL.LAYER_ID AS B_LAYERID, BCL.CLASS_SHORTNAME AS B_CLASSNAME, AE.EVENT_ID AS A_EID, AE.EVENT_TITLE AS A_TITLE, BE.EVENT_ID AS B_EID, BE.EVENT_TITLE AS B_TITLE, AE.EVENT_DATE AS DATE, COUNT(S.SCHED_USER) AS C
	FROM LAYERUSER_LIST AS ALU
	INNER JOIN LAYER_LIST AS AL ON ALU.LAYERUSER_LAYER=AL.LAYER_ID
	INNER JOIN SCHED_LIST AS S ON (S.SCHED_CLASS=AL.LAYER_CLASS AND (S.SCHED_TEACHER=AL.LAYER_TEACHER OR AL.LAYER_TEACHER Is Null))
	INNER JOIN SCHED_LIST AS T ON S.SCHED_USER=T.SCHED_USER AND S.SCHED_PER!=T.SCHED_PER AND S.SCHED_CLASS!=T.SCHED_CLASS
	INNER JOIN LAYER_LIST AS BL ON (T.SCHED_CLASS=BL.LAYER_CLASS AND (T.SCHED_TEACHER=AL.LAYER_TEACHER OR AL.LAYER_TEACHER Is Null))
	INNER JOIN EVENT_LIST AS AE ON AL.LAYER_ID=AE.EVENT_LAYER
	INNER JOIN EVENTCAT_LIST AS AC ON AE.EVENT_CAT=AC.EVENTCAT_ID
	INNER JOIN EVENT_LIST AS BE ON BL.LAYER_ID=BE.EVENT_LAYER
	INNER JOIN EVENTCAT_LIST AS BC ON BE.EVENT_CAT=BC.EVENTCAT_ID
	INNER JOIN CLASS_LIST AS ACL ON AL.LAYER_CLASS=ACL.CLASS_ID
	INNER JOIN CLASS_LIST AS BCL ON BL.LAYER_CLASS=BCL.CLASS_ID
	WHERE
	S.SCHED_YEAR=" . C_SCHOOLYEAR . " AND T.SCHED_YEAR=" . C_SCHOOLYEAR . " AND
	LAYERUSER_USER=$userid AND LAYERUSER_ACCESS>=2 AND AC.EVENTCAT_ISTEST=1 AND BC.EVENTCAT_ISTEST=1 AND (S.SCHED_TERM='" . C_SEMESTER . "' OR S.SCHED_TERM='YEAR') AND (T.SCHED_TERM='" . C_SEMESTER . "' OR T.SCHED_TERM='YEAR') AND AE.EVENT_DATE=BE.EVENT_DATE AND BE.EVENT_DATE>$cur_idf
	GROUP BY A_LAYERID, B_LAYERID, A_EID, B_EID, DATE
	ORDER BY DATE, A_CLASSNAME, B_CLASSNAME, A_TITLE, B_TITLE");
	?>
	<? if(mysql_num_rows($conflicts) > 0) { ?>
		<h2 class="red">Test Conflicts</h2><div class="headed">
		<ul class="flat" style="margin: 0px">
		<?
		while($conf = mysql_fetch_array($conflicts, MYSQL_ASSOC))
		{
			print '<li>';
			print '<span style="font-weight: bold">';
			print idfl($conf['DATE']) . ' ' . idfj($conf['DATE']) . ' ' . idfFF($conf['DATE']);
			print ': <a href="/calendar/common.php?c1=' . $conf['A_CLASS'] . '&amp;t1=' . $conf['A_TEACHER'] . '&amp;c2=' . $conf['B_CLASS'] . '&amp;t2=' . $conf['B_TEACHER'] . '">' . $conf['C'] . ' student';
			if($conf['C'] != 1)
				print 's';
			print '</a> from ' . $conf['A_CLASSNAME'] . '</span></li>';
			print '<ul class="flat"><a href="/calendar/event.php?open=' . $conf['A_EID'] . '">' . $conf['A_TITLE'] . '</a> (<a href="/calendar/layer.php?viewset=' . $conf['A_LAYERID'] . '">' . $conf['A_CLASSNAME'] . '</a>) conflicts with <a href="/calendar/event.php?open=' . $conf['B_EID'] . '">' . $conf['B_TITLE'] . '</a> (<a href="/calendar/layer.php?id=' . $conf['B_LAYERID'] . '">' . $conf['B_CLASSNAME'] . '</a>)</ul>';
		}
		?>
		</ul>
		</div>
	<? } ?>

	<? if($isstudent && SITE_ACTIVE) { ?>
		<h2 class="red">Tests and Projects</h2><div class="headed">
		<ul class="flat" style="margin: 0px">
		<?
		$result = mysql_query('SELECT (LENGTH(EVENT_DESC) > 0) AS YES_DESC, CLASS_NAME, CLASS_SHORTNAME, SCHED_ID, SCHED_PER, EVENT_LIST.*, LAYER_TITLE
		FROM SCHED_LIST
			INNER JOIN LAYER_LIST ON SCHED_CLASS=LAYER_CLASS
			INNER JOIN EVENT_LIST ON LAYER_ID=EVENT_LAYER
			INNER JOIN CLASS_LIST ON SCHED_CLASS=CLASS_ID
			LEFT JOIN EVENTCAT_LIST ON EVENT_CAT=EVENTCAT_ID
		WHERE
			SCHED_YEAR=' . C_SCHOOLYEAR . ' AND
			(LAYER_TEACHER=SCHED_TEACHER OR LAYER_TEACHER Is Null) AND
			EVENTCAT_ISTEST=1 AND
			SCHED_USER=' . $userid . ' AND
			EVENT_DATE>=' . $firstday . ' AND EVENT_RECUR=\'none\' AND EVENT_DATE<' . $lasttestday . ' AND
			(SCHED_TERM="YEAR" OR SCHED_TERM="' . C_SEMESTER . '")
		ORDER BY EVENT_DATE, SCHED_PER, SCHED_TERM') or die('Calendar query failed: ' . mysql_error());

		$repeats = mysql_query('SELECT (LENGTH(EVENT_DESC) > 0) AS YES_DESC, CLASS_NAME, CLASS_SHORTNAME, SCHED_ID, SCHED_PER, EVENT_LIST.*, LAYER_TITLE
		FROM SCHED_LIST
			INNER JOIN LAYER_LIST ON SCHED_CLASS=LAYER_CLASS
			INNER JOIN EVENT_LIST ON LAYER_ID=EVENT_LAYER
			INNER JOIN CLASS_LIST ON SCHED_CLASS=CLASS_ID
			LEFT JOIN EVENTCAT_LIST ON EVENT_CAT=EVENTCAT_ID
		WHERE
			SCHED_YEAR=' . C_SCHOOLYEAR . ' AND
			(LAYER_TEACHER=SCHED_TEACHER OR LAYER_TEACHER Is Null) AND
			EVENTCAT_ISTEST=1 AND
			SCHED_USER=' . $userid . ' AND
			(EVENT_RECUREND=0 OR EVENT_RECUREND>=' . $firstday . ') AND EVENT_RECUR!=\'none\' AND EVENT_DATE<' . $lasttestday . ' AND
			(SCHED_TERM="YEAR" OR SCHED_TERM="' . C_SEMESTER . '")
		ORDER BY EVENT_TIME, EVENT_TITLE, EVENT_ID') or die('Repeats query failed');

		$numreps = mysql_num_rows($repeats);

		for($i = 0; $i < $numreps; $i++)
			$repeatnr[$i] = mysql_fetch_array($repeats, MYSQL_ASSOC);

		if(mysql_num_rows($result) > 0)
		{
			$ll = mysql_fetch_array($result, MYSQL_ASSOC);
			$dwrs = false;
		}
		else
			$dwrs = true;

		$eventsshown = false;

		while($nextday < $lasttestday)
		{
			$eventstoday = false;
			$cj = idfj($nextday);
			$cm = idfn($nextday);
			$cy = idfYY($nextday);

			$dwd = ($ll["EVENT_DATE"] > $nextday);
			$j = 0;

			$cursched = 0;

			while($j < $numreps || !($dwrs || $dwd))
			{
				if($j >= $numreps)
					$loadrecur = false;
				else if($dwrs || $dwd)
					$loadrecur = true;
				else if($repeatnr[$j]["EVENT_TIME"] < $ll["EVENT_TIME"])
					$loadrecur = true;
				else
					$loadrecur = false;

				if($loadrecur)
				{
					$l = $repeatnr[$j];
					$j++;

					$printevent = showrecur($nextday, $l['EVENT_DATE'], $l['EVENT_RECUR'], $l['EVENT_RECUREND'], $l['EVENT_RECURPARAM'], $l['EVENT_RECURFREQ']);
				}
				else
				{
					$l = $ll;
					if($ll = mysql_fetch_array($result, MYSQL_ASSOC))
					{
						if($ll["EVENT_DATE"] > $nextday)
							$dwd = true;
					}
					else
						$dwrs = true;


					$printevent = true;
				}

				if($printevent)
				{
					if(!$eventstoday)
					{
						print '<li>';
						print '<span style="font-weight: bold">';
						print idfl($nextday) . ' ' . $cj . ' ' . idfFF($nextday);
						print '</span></li>';
						print '<li><ul class="flat">';
					}

					$eventstoday = true;
					$eventsshown = true;

					print '<li>';
					print '<span style="font-weight: bold; color: #999999">' . $l['CLASS_SHORTNAME'] . '</span> ';
					print '<a href="calendar/event.php?view=m&amp;start=' . $HGVstart . '&amp;viewset=' . $HGVviewset . '&amp;open=' . $l['EVENT_ID'] . '">' . htmlentities($l['EVENT_TITLE']) . '</a>';
					print '</li>';
				}
			}

			if($eventstoday)
				print '</ul></li>';

			$nextday = makeidf($cm, $cj + 1, $cy);
		}

		mysql_free_result($result);

		if(!$eventsshown)
			print '<li>None</li>';
		?>
		</ul>
		</div>
	<? } ?>

	<? if($isstudent && SITE_ACTIVE) { ?>
		<?
		$seldate = makecuridf(8);
		$seldayno = idfd($seldate);
		$selmonthno = idfm($seldate);
		$selyearno = idfYY($seldate);
		$firstday = $seldate;
		$nextday = $firstday;

		if(idfw($seldate) == 5)
			$offset = 3;
		else if(idfw($seldate) == 6)
			$offset = 2;
		else
			$offset = 1;

		$seldate = makeidf($selmonthno, $offset + $seldayno, $selyearno);

		// Adjusting for holidays
		$pushdate = true;

		if($seldate == 20040906)								// Labor Day
			$seldate = 20040907;
		else if($seldate == 20041111)							// Veterans' Day
			$seldate = 20041112;
		else if(20041124 <= $seldate && $seldate <= 20041126)	// Thanksgiving Day
			$seldate = 20041129;
		else if(20041220 <= $seldate && $seldate <= 20041231)	// December Recess
			$seldate = 20050103;
		else if(20050114 <= $seldate && $seldate <= 20050118)	// Martin Luther King Weekend
			$seldate == 20050119;
		else if(20050214 <= $seldate && $seldate <= 20050218)	// February Recess
			$seldate = 20050221;
		else if(20050411 <= $seldate && $seldate <= 20050415)	// Spring Recess
			$seldate = 20050418;
		else if(20050528 <= $seldate && $seldate <= 20050530)	// Memorial Day Weekend
			$seldate = 20050531;
		else
			$pushdate = false;

		$seldayno = idfd($seldate);
		$selmonthno = idfm($seldate);
		$selyearno = idfYY($seldate);
		?>
		<h2 class="red">Due <?
		if($pushdate)
			print idfDD($seldate) . ' ' . idfj($seldate) . ' ' . idfMM($seldate);
		else
			print idfl($seldate); ?></h2><div class="headed">
		<?

		$classes = mysql_query('SELECT CLASS_NAME, CLASSLINK_URL, SCHED_ID, SCHED_PER, SCHED_CLASS, SCHED_TEACHER, LAYER_ID
		FROM SCHED_LIST
			INNER JOIN CLASS_LIST ON SCHED_CLASS=CLASS_ID
			LEFT JOIN LAYER_LIST ON SCHED_CLASS=LAYER_CLASS AND (LAYER_TEACHER=SCHED_TEACHER OR LAYER_TEACHER Is Null)
			LEFT JOIN CLASSLINK_LIST ON CLASSLINK_COURSE=CLASS_ID AND CLASSLINK_TEACHER=SCHED_TEACHER AND CLASSLINK_TYPE="Class Website"
		WHERE
			SCHED_YEAR=' . C_SCHOOLYEAR . ' AND
			SCHED_USER=' . $userid . ' AND
			(SCHED_TERM="YEAR" OR SCHED_TERM="' . C_SEMESTER . '")
		ORDER BY SCHED_PER, SCHED_TERM') or die(mysql_error());

		if(mysql_num_rows($classes) > 0)
		{
			print '<ul class="flat" style="margin: 0px;">';

			while($cclass = mysql_fetch_array($classes, MYSQL_ASSOC))
			{
				/*
				if($cclass['LAYER_ID'] > 0)
					print '<li><span style="font-weight: bold"><span style="color: #999999">' . $cclass['SCHED_PER'] . '</span> <a href="/calendar/layer.php?viewset=' . $cclass['LAYER_ID'] . '">' . $cclass['CLASS_NAME'] . '</a></span></li>';
				else
					print '<li><span style="font-weight: bold"><span style="color: #999999">' . $cclass['SCHED_PER'] . '</span> ' . $cclass['CLASS_NAME'] . '</span></li>';
				*/

				print '<li><span style="font-weight: bold"><span style="color: #999999">' . $cclass['SCHED_PER'] . '</span> <a href="/cm/?class=' . $cclass['SCHED_CLASS'] . '&amp;teacher=' . $cclass['SCHED_TEACHER'] . '">' . $cclass['CLASS_NAME'] . '</a></span></li>';

				print '<li><ul class="flat">';

				if(!is_null($cclass['LAYER_ID']))
				{
					$displayedevents = printDay($cclass['LAYER_ID'], $seldate);
					if(!$displayedevents)
						print '<li>No assignments.</li>';
				}
				else if(isset($cclass['CLASSLINK_URL']))
					print '<li><span style="font-style: italic"><a href="' . $cclass['CLASSLINK_URL'] . '">View homework website</a></span></li>';
				else
					print '<li><span style="color: #999999">No calendar available.</span></li>';

				print '</ul></li>';
			}

			print '</ul>';
		}
		else
			print '<p style="margin: 0px">If you enter the classes in your schedule, you can see your homework assignments in this space.</p>';
		?>
		</div>
		<div class="footer"><a href="calendar/calendar.php?view=w&amp;viewset=c&amp;start=<?= $seldate ?>">More Homework...</a></div>

	<? } else if($isteacher && SITE_ACTIVE) { ?>

		<?
		$seldate = makecuridf(8);
		$seldayno = idfd($seldate);
		$selmonthno = idfm($seldate);
		$selyearno = idfYY($seldate);
		$firstday = $seldate;
		$nextday = $firstday;

		if(idfw($seldate) == 5)
			$offset = 3;
		else if(idfw($seldate) == 6)
			$offset = 2;
		else
			$offset = 1;

		$seldate = makeidf($selmonthno, $offset + $seldayno, $selyearno);

		// Adjusting for holidays
		$pushdate = true;

		if($seldate == 20040906)								// Labor Day
			$seldate = 20040907;
		else if($seldate == 20041111)							// Veterans' Day
			$seldate = 20041112;
		else if(20041124 <= $seldate && $seldate <= 20041126)	// Thanksgiving Day
			$seldate = 20041129;
		else if(20041220 <= $seldate && $seldate <= 20041231)	// December Recess
			$seldate = 20050103;
		else if(20050114 <= $seldate && $seldate <= 20050118)	// Martin Luther King Weekend
			$seldate == 20050119;
		else if(20050214 <= $seldate && $seldate <= 20050218)	// February Recess
			$seldate = 20050221;
		else if(20050411 <= $seldate && $seldate <= 20050415)	// Spring Recess
			$seldate = 20050418;
		else if(20050528 <= $seldate && $seldate <= 20050530)	// Memorial Day Weekend
			$seldate = 20050531;
		else
			$pushdate = false;

		$seldayno = idfd($seldate);
		$selmonthno = idfm($seldate);
		$selyearno = idfYY($seldate);

		$classes = mysql_query('SELECT DISTINCT CLASS_NAME, VALIDCLASS_COURSE, VALIDCLASS_TEACHER, LAYER_ID FROM VALIDCLASS_LIST INNER JOIN CLASS_LIST ON VALIDCLASS_COURSE=CLASS_ID LEFT JOIN LAYER_LIST ON LAYER_CLASS=VALIDCLASS_COURSE AND LAYER_TEACHER=VALIDCLASS_TEACHER WHERE VALIDCLASS_TEACHER=' . $usertag . ' ORDER BY CLASS_NAME') or die('Class query failed.');

		if(mysql_num_rows($classes) > 0)
		{
			?>
			<h2 class="red">Due <?
			if($pushdate)
				print idfDD($seldate) . ' ' . idfj($seldate) . ' ' . idfMM($seldate);
			else
				print idfl($seldate); ?></h2><div class="headed">
			<?
			print '<li><ul class="flat" style="margin: 0px;">';

			while($cclass = mysql_fetch_array($classes, MYSQL_ASSOC))
			{
				$numclasses++;

				/*
				print '<li><span style="font-weight: bold"><a href="/calendar/layer.php?viewset=' . $cclass['LAYER_ID'] . '">' . $cclass['CLASS_NAME'] . '</a></span></li>';
				*/

				print '<li><span style="font-weight: bold"><span style="color: #999999">' . $cclass['SCHED_PER'] . '</span> <a href="/cm/?class=' . $cclass['VALIDCLASS_COURSE'] . '&amp;teacher=' . $cclass['VALIDCLASS_TEACHER'] . '">' . $cclass['CLASS_NAME'] . '</a></span></li>';

				print '<li><ul class="flat">';

				if(is_null($cclass['LAYER_ID']))
				{
					print '<li style="color: #999999">No calendar available.</li>';
				}
				else
				{
					$displayedevents = printDay($cclass['LAYER_ID'], $seldate);
					if(!$displayedevents)
						print '<li>No assignments.</li>';
				}

				print '</ul></li>';
			}

			print '</ul></li>';

			print '<div style="margin-top: 1.0ex; padding: 2px; background-color: #ffffff; border: 1px solid #666666">Based on what you entered in your homework calendar';
			if(mysql_num_rows($classes) > 1)
				print 's';
			print ', this is a preview of what your students see on their home page.</div>';
			print '</div>'; ?>
		<? } ?>
	<? } ?>