<?
// Philip Sung | 0x7d3 | saratogahigh.com
// calendar/calendar.php: default home page. displays user calendar in a variety of views and subsets.

include "../db.php";
include "cal.php";

if(ereg('MSIE [56789]\.', $_SERVER['HTTP_USER_AGENT']))
	$ieplus = true;

// Funtions to print the View buttons selected or not selected based on the current view settings
function printButton($viewid, $viewTitle, $viewimg)
{
	global $HGVview, $HGVviewset, $seldate;

	if($HGVview == $viewid)
	{
		// print '<td class="selectedButton"><img title="' . $viewTitle . ' View" style="border: 0px" src="img/' . $viewimg . '"></td>';
		
		print '<span style="font-weight: bold">' . $viewTitle . '</span> | ';
	}
	else
	{
		// print '<td class="unselectedButton"><a href="calendar.php?view=' . $viewid . '&amp;viewset=' . $HGVviewset . '&amp;start=' . $seldate . '"><img title="' . $viewTitle . ' View" style="border: 0px" src="img/' . $viewimg . '"></a></td>';
		
		print '<a href="calendar.php?view=' . $viewid . '&amp;viewset=' . $HGVviewset . '&amp;start=' . $seldate . '">' . $viewTitle . '</a> | ';
	}
}

function printPrintButton($viewid, $viewTitle, $viewimg)
{
	global $HGVview, $HGVviewset, $seldate;

	if($HGVview == $viewid)
	{
		// print '<td class="unselectedButton"><a href="calendar.php?print=true&amp;view=' . $viewid . '&amp;viewset=' . $HGVviewset . '&amp;start=' . $seldate . '"><img style="border: 0px" src="img/print.gif" title="Printer-Friendly Calendar"></a></td>';
		
		print '<a style="font-style: italic" href="calendar.php?print=true&view=' . $viewid . '&amp;viewset=' . $HGVviewset . '&amp;start=' . $seldate . '">Print Calendar</a> | <a style="font-style: italic" href="listprint.php?view=' . $viewid . '&amp;viewset=' . $HGVviewset . '&amp;start=' . $seldate . '">Print Event List</a>';
	}
	else
	{
		// print '<td></td>';
	}
}

// Printer-friendly view?
if($HGVprint == 'true')
	$printview = true;
else
	$printview = false;

// Select calendar subset to view
if($loggedin && $HGVviewset == 'p')
	$viewmode = VIEWMODE_PERSONAL; // My Calendar (p is for Personalized)
else if($loggedin && $HGVviewset == 'a')
	$viewmode = VIEWMODE_ALL; // All Groups
else if($loggedin && $HGVviewset == 'c' && $isstudent)
	$viewmode = VIEWMODE_HOMEWORK; // Homework Groups
else if($HGVviewset == 's')
	$viewmode = VIEWMODE_SCHOOL; // School Calendar
else if(is_numeric($HGVviewset))
{
	$viewmode = VIEWMODE_GROUP; // Single group
	$viewset = $HGVviewset; // Set group ID
}
else // Defaults
{
	if($loggedin)
	{
		$viewmode = VIEWMODE_PERSONAL; // Default to My Calendar for logged-in users
		$HGVviewset = 'p';
	}
	else
	{
		$viewmode = VIEWMODE_SCHOOL; // Default to School Calendar for not logged-in users
		$HGVviewset = 's';
	}
}

// Get starting date
$seldate = $HGVstart;
	$seldayno = idfd($seldate);
	$selmonthno = idfm($seldate);
	$selyearno = idfYY($seldate);

if($HGVview == 'd')
{
	$HGVview = 'd';
	$viewdescriptor = 'Day View';
}
else if($HGVview == 's')
{
	$HGVview = 's';
	$viewdescriptor = 'School Week View';
}
else if($HGVview == 'w')
{
	$HGVview = 'w';
	$viewdescriptor = 'Week View';
}
else
{
	$HGVview = 'm';
	$viewdescriptor = 'Month View';
}

if($viewmode != VIEWMODE_ERROR)
{
	// Update display colors if asked to
	if($loggedin)
	{
		if($_POST['go'] == 'Save and View')
		{
			$result = mysql_query('SELECT LAYERUSER_ID FROM LAYERUSER_LIST WHERE LAYERUSER_USER=' . $userid) or die('Current group listing failed');
			while($l = mysql_fetch_array($result, MYSQL_ASSOC))
			{
				if(is_numeric($_POST['color' . $l['LAYERUSER_ID']]))
				{
					if($_POST['show' . $l['LAYERUSER_ID']] == "D")
						mysql_query('UPDATE LAYERUSER_LIST SET LAYERUSER_COLOR=' . $_POST['color' . $l['LAYERUSER_ID']] . ', LAYERUSER_DISPLAY=1 WHERE LAYERUSER_ID=' . $l['LAYERUSER_ID']) or die('Update failed');
					else
						mysql_query('UPDATE LAYERUSER_LIST SET LAYERUSER_COLOR=' . $_POST['color' . $l['LAYERUSER_ID']] . ', LAYERUSER_DISPLAY=0 WHERE LAYERUSER_ID=' . $l['LAYERUSER_ID']) or die('Update failed');
				}
			}
			mysql_free_result($result);
		}
	}

	// Do setup for each subset type...load the subset title
	if($viewmode == VIEWMODE_GROUP)
	{
		// Find that layer and the user's status
		if($loggedin)
			$nres = mysql_query('SELECT LAYER_ID, LAYER_TITLE, LAYER_OPEN, CLASS_ID, CLASS_NAME, TEACHER_ID, TEACHER_NAME, USER_ID, USER_FULLNAME, LAYER_USED FROM LAYER_LIST
				LEFT JOIN CLASS_LIST ON CLASS_ID=LAYER_CLASS
				LEFT JOIN TEACHER_LIST ON TEACHER_ID=LAYER_TEACHER
				LEFT JOIN USER_LIST ON USER_ID=LAYER_PERSONAL
				LEFT JOIN LAYERUSER_LIST ON LAYERUSER_LAYER=LAYER_ID
				WHERE ((LAYERUSER_USER=' . $userid . ' AND LAYERUSER_ACCESS > 0) OR LAYER_OPEN=1) AND LAYER_ID=' . $viewset) or die('Calendar query failed');
		else
			$nres = mysql_query('SELECT LAYER_ID, LAYER_TITLE, LAYER_OPEN, CLASS_ID, CLASS_NAME, TEACHER_ID, TEACHER_NAME, USER_ID, USER_FULLNAME, LAYER_USED FROM LAYER_LIST
				LEFT JOIN CLASS_LIST ON CLASS_ID=LAYER_CLASS
				LEFT JOIN TEACHER_LIST ON TEACHER_ID=LAYER_TEACHER
				LEFT JOIN USER_LIST ON USER_ID=LAYER_PERSONAL
				WHERE LAYER_ID=' . $viewset . ' AND LAYER_OPEN=1') or die('Calendar query failed');
		
		if($groupnameR = mysql_fetch_array($nres, MYSQL_ASSOC))
		{
			$groupid = $groupnameR['LAYER_ID'];
			$caltitle = $groupnameR['LAYER_TITLE'];

			if($loggedin)
				$userlevel = layeruserlevel($groupid, $userid);
			else
				$userlevel = -1;
		}
		else // Layer not found, or inappropriate permissions
			$viewmode = VIEWMODE_ERROR;

		if( ! is_null($groupnameR['CLASS_ID']) )
		{
			mysql_query('UPDATE LAYER_LIST SET LAYER_USED=' . ($groupnameR['LAYER_USED'] + 1) . ' WHERE LAYER_ID=' . $groupnameR['LAYER_ID']) or die(mysql_error());
		}
	}
	else if($viewmode == VIEWMODE_PERSONAL)
		$caltitle = "My Calendar";
	else if($viewmode == VIEWMODE_ALL)
		$caltitle = "All My Groups";
	else if($viewmode == VIEWMODE_HOMEWORK)
		$caltitle = "My Homework";
	else if($viewmode == VIEWMODE_SCHOOL)
		$caltitle = "School Calendar";
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
	<title><?= $caltitle ?>: <? print $viewdescriptor; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<meta name="vs_targetSchema" content="http://schemas.microsoft.com/intellisense/ie5">
	<link rel="stylesheet" type="text/css" href="../shs.css">
	<? if($printview) { ?>
	<link rel="stylesheet" type="text/css" href="calprint.css">
	<? } else { ?>
	<link rel="stylesheet" type="text/css" href="calcss.php">
	<? } ?>
	<style type="text/css"><!--
		a.lnkh { font-weight: bold }
		a.curmonth { font-weight: bold }
		a.vv:link { text-decoration: none; }
		a.vv:visited { text-decoration: none; }
		a.vv:hover { text-decoration: underline; color: #ff6600; }
		td.toolbarside { background-color: #330099; color: #ffffff; font-weight: bold }
<?
			// Create the gradient styles necessary for the group list at the bottom
			if($loggedin && ($viewmode == VIEWMODE_PERSONAL || $viewmode == VIEWMODE_ALL))
			{
				if($viewmode == VIEWMODE_PERSONAL)
					$usedcolors = mysql_query('SELECT CALCOLOR_LIST.CALCOLOR_ID, CALCOLOR_LIST.CALCOLOR_COLOR FROM CALCOLOR_LIST LEFT JOIN LAYERUSER_LIST ON LAYERUSER_COLOR=CALCOLOR_ID AND LAYERUSER_USER=' . $userid . ' AND LAYERUSER_DISPLAY=1 WHERE NOT (LAYERUSER_ID Is Null)') or die('Query failed.');
				else
					$usedcolors = mysql_query('SELECT CALCOLOR_LIST.CALCOLOR_ID, CALCOLOR_LIST.CALCOLOR_COLOR FROM CALCOLOR_LIST LEFT JOIN LAYERUSER_LIST ON LAYERUSER_COLOR=CALCOLOR_ID AND LAYERUSER_USER=' . $userid . ' WHERE NOT (LAYERUSER_ID Is Null)') or die('Query failed.');
				
				while($c = mysql_fetch_array($usedcolors, MYSQL_ASSOC))
				{
					print '		td.gr' . $c['CALCOLOR_ID'] . ' { filter:progid:DXImageTransform.Microsoft.Gradient(GradientType=1, StartColorStr=\'' . $c['CALCOLOR_COLOR'] . "', EndColorStr='#ffffff') }\n";
					print '		div.ev' . $c['CALCOLOR_ID'] . ' { background-color: ' . $c['CALCOLOR_COLOR'] . " }\n";
				}
			}
		?>
	--></style>
	<? if(!$printview) {
		// Javascript for those nifty popups
	?>
	<script type="text/javascript"><!--
		function datejump()
		{
			var agt=navigator.userAgent.toLowerCase();

			if (agt.indexOf('mozilla/5')!=-1)
			{
				FEy = document.getElementById('FEy');
				FEm = document.getElementById('FEm');
			}
			if(!isNaN(parseInt(FEy.value)))
			{
				if(1000 <= parseInt(FEy.value) && parseInt(FEy.value) <= 9999)
					location.href = 'calendar.php?view=m&viewset=<?= $HGVviewset ?>&start=' + parseInt(FEy.value) + FEm.value + '01';
			}
		}
		
		function goAdd(idf)
		{
<?
			if($viewmode == VIEWMODE_GROUP)
			{
?>
				document.location.href = "addevent.php?view=m&viewset=<?= $HGVviewset ?>&start=" + idf + "&layer=<?= $viewset ?>";
<?
			}
			else
			{
?>
				document.location.href = "addevent.php?view=m&viewset=<?= $HGVviewset ?>&start=" + idf;
<?
			}
?>
		}
	
		function pb(paramgroup, paramtime, paramplace)
		{
			boxleft = 3 + event.srcElement.parentElement.offsetLeft + event.srcElement.parentElement.parentElement.offsetLeft;
			boxtop = 10 + event.srcElement.parentElement.offsetHeight + event.srcElement.offsetTop + event.srcElement.parentElement.parentElement.offsetTop;
			
			if(boxleft + 220 > document.body.clientWidth)
				boxleft = document.body.clientWidth - 220;
			
			infobox.style.left = boxleft;
			infobox.style.top = boxtop;
			
			infobox.style.display = 'block';
			
			infoboxtext = '<table cellpadding="2" cellspacing="0">';
			
			infoboxtext += '<tr><td class="toolbarside">Group</td><td>' + paramgroup +  '</td></tr>';
			if(paramtime != '')
				infoboxtext += '<tr><td class="toolbarside">Time</td><td>' + paramtime +  '</td></tr>';
			if(paramplace != '')
				infoboxtext += '<tr><td class="toolbarside">Place</td><td>' + paramplace +  '</td></tr>';
			
			infoboxtext += '</table>';
			
			infobox.innerHTML = infoboxtext;
		}
		
		function hb()
		{
			infobox.style.display = 'none';
		}
	// --></script>
	<? } ?>
</head>
<body>

<?

if($viewmode != VIEWMODE_ERROR)
{
	if(!$printview)
	{
		?>
		<div id="infobox" style="border: 1px solid #666666; z-index: 200; width: 220px; position: absolute; display: none; background-color: #f8f8f8"></div>
		
		<? include "inc-header.php";
		
		// Print list of calendar subsets
		
		// Check for hidden calendars
		if($loggedin)
		{
			$rshc = mysql_query('SELECT COUNT(*) FROM LAYERUSER_LIST WHERE LAYERUSER_USER=' . $userid . ' AND LAYERUSER_DISPLAY=0');
			$hc = mysql_fetch_array($rshc, MYSQL_ASSOC);
			if($hc['COUNT(*)'] > 0)
				$hiddencalendars = true;
			else
				$hiddencalendars = false;
		}
		?>
		
		<table style="margin-top: 8px" cellspacing="0" cellpadding="2">
		<tr>
			<td class="optionName">Calendars:</td>
			<td>
			<form action="calendar.php" method="GET" style="margin: 0px"><p style="margin: 0">
			<input type="hidden" name="view" value="<?= $HGVview ?>">
			<input type="hidden" name="start" value="<?= $seldate ?>">
			<select name="viewset" style="font-size: medium">
			<? if($loggedin) { ?>
				<option value="p" class="bigoption"<? if($viewmode == VIEWMODE_PERSONAL) { print ' selected'; } ?>>My Calendar</option>
				<?
				$rscals = mysql_query('SELECT LAYER_ID, LAYER_TITLE FROM LAYERUSER_LIST INNER JOIN LAYER_LIST ON LAYERUSER_LAYER=LAYER_ID WHERE LAYERUSER_USER=' . $userid . ' AND LAYERUSER_DISPLAY=1 ORDER BY LAYER_TITLE');
				while($ccal = mysql_fetch_array($rscals, MYSQL_ASSOC))
				{
					print '<option value="' . $ccal['LAYER_ID'] . '"';
					if($ccal['LAYER_ID'] == $HGVviewset)
					{
						print ' selected';
						$layerprinted = true;
					}
					print '>&nbsp;&nbsp;&nbsp;' . htmlentities($ccal['LAYER_TITLE']) . '</option>';
				}
				?>
				<? if($hiddencalendars) { ?>
				<option value="a" class="bigoption"<? if($viewmode == VIEWMODE_ALL) { print ' selected'; } ?>>All Groups</option>
				<?
				$rscals = mysql_query('SELECT LAYER_ID, LAYER_TITLE FROM LAYERUSER_LIST INNER JOIN LAYER_LIST ON LAYERUSER_LAYER=LAYER_ID WHERE LAYERUSER_USER=' . $userid . ' AND LAYERUSER_DISPLAY=0 ORDER BY LAYER_TITLE');
				while($ccal = mysql_fetch_array($rscals, MYSQL_ASSOC))
				{
					print '<option value="' . $ccal['LAYER_ID'] . '"';
					if($ccal['LAYER_ID'] == $HGVviewset)
					{
						print ' selected';
						$layerprinted = true;
					}
					print '>&nbsp;&nbsp;&nbsp;' . htmlentities($ccal['LAYER_TITLE']) . '</option>';
				}
				?>
				<? } ?>
				<? if($isstudent) { ?>
				<option value="c" class="bigoption"<? if($viewmode == VIEWMODE_HOMEWORK) { print ' selected'; } ?>>My Homework</option>
				<?
				$rscals = mysql_query('SELECT LAYER_ID, CLASS_NAME, SCHED_PER, TEACHER_NAME, CLASS_NAME
					FROM SCHED_LIST
						INNER JOIN LAYER_LIST ON SCHED_CLASS=LAYER_CLASS
						INNER JOIN CLASS_LIST ON LAYER_CLASS=CLASS_ID
						LEFT JOIN TEACHER_LIST ON LAYER_TEACHER=TEACHER_ID
					WHERE
						SCHED_YEAR=' . C_SCHOOLYEAR . ' AND
						(SCHED_TERM="YEAR" OR SCHED_TERM="' . C_SEMESTER . '") AND
						(LAYER_TEACHER=SCHED_TEACHER OR LAYER_TEACHER Is Null) AND
						SCHED_USER=' . $userid . '
					ORDER BY SCHED_PER, SCHED_TERM');
				while($ccal = mysql_fetch_array($rscals, MYSQL_ASSOC))
				{
					print '<option value="' . $ccal['LAYER_ID'] . '"';
					if($ccal['LAYER_ID'] == $HGVviewset)
					{
						print ' selected';
						$layerprinted = true;
					}
					print '>&nbsp;&nbsp;&nbsp;' . htmlentities('Per ' . $ccal['SCHED_PER'] . ': ' . $ccal['CLASS_NAME']) . '</option>';
				}
				?>
				<? } ?>
			<? } ?>
			<option value="s" class="bigoption"<? if($viewmode == VIEWMODE_SCHOOL) { print ' selected'; } ?>>School Calendar</option>
			<?
			$rscals = mysql_query('SELECT LAYER_ID, LAYER_TITLE FROM LAYER_LIST WHERE LAYER_SHOWDEFAULT=1 ORDER BY LAYER_TITLE');
			while($ccal = mysql_fetch_array($rscals, MYSQL_ASSOC))
			{
				print '<option value="' . $ccal['LAYER_ID'] . '"';
				if($ccal['LAYER_ID'] == $HGVviewset)
				{
					print ' selected';
					$layerprinted = true;
				}
				print '>&nbsp;&nbsp;&nbsp;' . htmlentities($ccal['LAYER_TITLE']) . '</option>';
			}
			?>
			<?
			if($viewmode == VIEWMODE_GROUP && !$layerprinted)
			{
				$rscals = mysql_query('SELECT LAYER_ID, LAYER_TITLE FROM LAYER_LIST WHERE LAYER_ID=' . $HGVviewset . ' ORDER BY LAYER_TITLE');
				while($ccal = mysql_fetch_array($rscals, MYSQL_ASSOC))
				{
					print '<option value="' . $ccal['LAYER_ID'] . '"';
					if($ccal['LAYER_ID'] == $HGVviewset)
					{
						print ' selected';
					}
					print '>' . htmlentities($ccal['LAYER_TITLE']) . '</option>';
				}				
			}
			?>
			</select> <input type="submit" name="" value="Go"></p>
			</form>
				<? if(false) { ?>
				</td>
				</tr></table>
				<? } ?>
			</td>
		</tr>
		<? // Print list of calendar views ?>
		<tr>
			<td class="optionName">Views:</td>
			<td>
				<div style="font-size: medium; padding: 4px">
				<?
				printButton('d', 'Day', '01.gif');
				printButton('w', 'Week', '07.gif');
				printButton('m', 'Month', '31.gif');
				
				if($viewmode == VIEWMODE_GROUP)
				{
					?><a href="layer.php?view=l&amp;viewset=<?= $groupid ?>&amp;start=<?= $HGVstart ?>">Group Details</a> | <?
				}

				printPrintButton('d', 'Day', '01.gif');
				printPrintButton('w', 'Week', '07.gif');
				printPrintButton('m', 'Month', '31.gif');
				
				?>
				</div>
			</td>
		</tr>
		<?
		// Print Actions and Status lines
		
		// Looking at a single group
		if($viewmode == VIEWMODE_GROUP)
		{
			// Show ADD EVENT button if user is an author/admin of the group
			if($loggedin)
			{
				if($userlevel > 2)
				{
				?>
				<tr>
					<td class="optionName">Actions:</td>
					<td>
						<table cellpadding="1" cellpadding="1">
						<tr><td class="unselectedButton"><a href="addevent.php?view=m&amp;start=<?= $HGVstart ?>&amp;viewset=<?= $HGVviewset ?>&amp;layer=<?= $HGVviewset ?>" style="font-size: medium; padding-right: 3px"><img alt="Add Event" style="border: 0px; margin-right: 3px; vertical-align: middle" src="img/add.gif">Add Event</a></td>
						<? if($HGVview != 'd') {
						?><td>or double-click on a day<br>to add an event on that day.</td><?
						} ?></tr>
						</table>
					</td>
				</tr>
				<?
				}
			}
			?>
			<tr>
				<td class="optionName">Last&nbsp;Updated:</td>
				<td style="font-size: medium"><?= lastmodified($viewset) ?> ago<? $updatedto = updatedto($current_layer['LAYER_ID']);
if($updatedto) { print ' (last entry is ' . printDATEMID($updatedto) . ')'; } ?></td>
			</tr>
			<tr>
				<td class="optionName">My&nbsp;Status:</td>
				<td style="font-size: medium"><?
				
				print printMemberStatus($userlevel, $groupnameR['LAYER_ID'], $groupnameR['LAYER_OPEN']);
				
				?></td>
			</tr>
			<? if(!is_null($groupnameR['USER_ID'])) { ?>
			<tr>
				<td class="optionName">Personal:</td>
				<td style="font-size: medium">This group is <a href="/directory/?id=<?= $groupnameR['USER_ID'] ?>"><?= $groupnameR['USER_FULLNAME'] ?></a>'s personal group.</td>
			</tr>			
			<? } ?>
			<? if(!is_null($groupnameR['CLASS_ID'])) { ?>
			<tr>
				<td class="optionName">Homework:</td>
				<td style="font-size: medium">This is the homework calendar for <? 
			if(!is_null($groupnameR['TEACHER_ID']))
				print '<a href="/cm/?class=' . $groupnameR['CLASS_ID'] . '&amp;teacher=' . $groupnameR['TEACHER_ID'] . '">';
			else
				print '<a href="/cm/?class=' . $groupnameR['CLASS_ID'] . '">';
			
			print '<span style="font-weight: bold">' . $groupnameR['CLASS_NAME'] . '</span>';
			
			if(!is_null($groupnameR['TEACHER_ID']))
				print ' with ' . $groupnameR['TEACHER_NAME'];
				
			print '</a>';
			?></td>
			
			</tr>
			<? } ?>
		        <? if(!is_null($groupnameR['CLASS_ID']) && ($userlevel >= 2 || $isstaff)) { ?>
		        <tr>
		                <td class="optionName">Usage:</td>
		                <td style="font-size: medium">This homework calendar has been used <span style="font-weight: bold"><? print $groupnameR['LAYER_USED']; ?> time<? if($groupnameR['LAYER_USED'] != 1) print 's'; ?></span> since June 30, 2004
		                </td>
		        </tr>
        <? } ?>

			<?
		}
		// Looking at a special subset
		else if($loggedin)
		{
			// Only display ADD EVENT button on My Calendar and All Groups views
			if($viewmode == VIEWMODE_PERSONAL || $viewmode == VIEWMODE_ALL)
			{
				?>
				<tr>
					<td class="optionName">Actions:</td>
					<td>
						<table cellpadding="1">
						<tr><td class="unselectedButton"><a href="addevent.php?view=m&amp;viewset=<?= $HGVviewset ?>&amp;start=<?= $seldate ?>" style="font-size: medium; padding-right: 3px"><img alt="Add Event" style="border: 0px; margin-right: 3px; vertical-align: middle" src="img/add.gif">Add Event</a></td><? if($HGVview != 'd') {
						?><td>Double-click on a day<br>to add an event on that day.</td><?
						} ?>
						</tr>
						</table>
					</td>
				</tr>
				<?
			}
		}
		
		print '</table>';
		
		// Notifications for if a user applied to join a private group
		printalerts($userid);
		
		if(!$loggedin)
		{
			?>
			<p>Please <a href="../login.php?next=/calendar/calendar.php">log in</a> to access your customized calendar groups.
			Once you log in, you can create and share your own groups; keep a private calendar; or see events from the groups you like, all integrated into one calendar.</p>
			<?
		}
	}
	
	// Print the actual calendar
	if($HGVview == 'm')
		include 'month.php';
	else if($HGVview == 'w')
		include 'week.php';
	else
		include 'daily.php';

	if($loggedin && $viewmode != VIEWMODE_GROUP && !$printview)
	{	
		if($viewmode == VIEWMODE_PERSONAL)
			include 'grouplist-personal.php';
		else if($viewmode == VIEWMODE_ALL)
			include 'grouplist-all.php';
		else if($viewmode == VIEWMODE_HOMEWORK)
			include 'grouplist-homework.php';
		else // if($viewmode == VIEWMODE_SCHOOL)
			include 'grouplist-school.php';
	}
}

if(!$printview)
	include '../inc-footer.php';
?>
</body>

</html>
