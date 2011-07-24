<?
// Philip Sung | 0x7d3 | saratogahigh.com
// calendar/cal.php: calendar utility functions

// GET variables
$HGVstart = $_GET['start'];
$HGVviewset = $_GET['viewset'];
$HGVview = $_GET['view'];
$HGVprint = $_GET['print'];
$HGVprintl = $_GET['printl'];

// Current date
$cur_ts = CURRENT_TIME;
$cur_idf = makeidf(date('n', $cur_ts), date('j', $cur_ts), date('Y', $cur_ts));

// Set date to current date if no other date was specified
if(!(is_numeric($HGVstart) || $HGVstart == 'l'))
	$HGVstart = $cur_idf;

// View names
define("VIEWMODE_ERROR", -1);
define("VIEWMODE_GROUP", 0);
define("VIEWMODE_PERSONAL", 1);
define("VIEWMODE_ALL", 2);
define("VIEWMODE_HOMEWORK", 3);
define("VIEWMODE_SCHOOL", 4);

// LAYER_ID of official school calendar
define('OFFICIAL_SCHOOL_CALENDAR', 15);

// Current IDF
function makecuridf($offset)
{
	return date(TIME_FORMAT_IDF, mktimeoffset(-$offset, 0, 0, 0, 0, 0));
}

// Retrieves last modified date for a layer, in seconds ago
function lastmodified($layerid)
{
	$q = mysql_query('SELECT UNIX_TIMESTAMP("' . date(TIME_FORMAT_SQL, CURRENT_TIME) . '") - UNIX_TIMESTAMP(LAYER_LASTMODIFIED) AS TDiff FROM LAYER_LIST WHERE LAYER_ID=' . $layerid);
	if($l = mysql_fetch_array($q, MYSQL_ASSOC))
		return timedescriptor($l['TDiff']);
}

// Retrieves latest event date for a layer in YYYYMMDD format
function updatedto($layerid)
{
	$tresult = mysql_query("SELECT EVENT_DATE FROM EVENT_LIST WHERE EVENT_LAYER='$layerid' ORDER BY EVENT_DATE DESC LIMIT 0, 1") or die("Query error.");
	if($upto = mysql_fetch_array($tresult, MYSQL_ASSOC))
		return $upto['EVENT_DATE'];
	else
		return null;
}

// Print alerts if someone applied to join your group(s)
function printalerts($userid)
{
	if($loggedin)
	{
		$applics = mysql_query('SELECT LAYER_ID, LAYER_TITLE, COUNT(LAYERUSER_LIST.LAYERUSER_ID) AS C FROM LAYERUSER_LIST AS ME_LIST INNER JOIN LAYER_LIST ON ME_LIST.LAYERUSER_LAYER=LAYER_ID INNER JOIN LAYERUSER_LIST ON LAYER_ID=LAYERUSER_LIST.LAYERUSER_LAYER WHERE ME_LIST.LAYERUSER_ACCESS=3 AND ME_LIST.LAYERUSER_USER=' . $userid . ' AND LAYERUSER_LIST.LAYERUSER_ACCESS=0 GROUP BY LAYER_ID') or die("Query failed");
		if(mysql_num_rows($applics) > 0 || mysql_num_rows($tentatives))
		{
			print '<div style="width: 500px; padding: 3px; margin: 10px; border: 1px solid #999999"><h1 style="font-size: medium; font-weight: bold; margin-bottom: 0px">Alerts</h1>';
			print '<ul class="flat" style="margin: 0px">';
			while($l = mysql_fetch_array($applics, MYSQL_ASSOC))
				print '<li>' . $l['C'] . ' person(s) applied to join <a href="layer.php?viewset=' . $l['LAYER_ID'] . '">' . $l['LAYER_TITLE'] . '</a></li>';
			print '</ul></div>';
		}
		mysql_free_result($applics);
	}
}

// Prints user status for a group (Administrator, etc.) with a short description and links to join/leave
function printMemberStatus($userlevel, $layerid, $layeropen)
{
	global $loggedin;
	switch($userlevel)
	{
		case -1:
			if($loggedin)
			{
				if($layeropen)
					return '<span style="font-weight: bold">Not a Member</span> (<a href="layer.php?viewset=' . $layerid . '&amp;action=join">Join Group</a>)';
				else
					return '<span style="font-weight: bold">Not a Member</span> (<a href="layer.php?viewset=' . $layerid . '&amp;action=join">Join Group</a> &ndash; requires administrator approval)';
			}
			else
				return 'Not a member (<a href="/login.php?next=/calendar/layer.php?viewset=' . $layerid . '">Log in</a> to join)';
			break;
		case 0: return '<span style="color: #000080; font-weight: bold">Awaiting Confirmation</span>. Once an administrator confirms your request, you will be a member of the group.<br><a href="layer.php?viewset=' . $layerid . '&amp;action=leave">Leave Group</a>';
			break;
		case 1: return '<span style="font-weight: bold">Member</span>. You can view events in this group. (<a href="layer.php?viewset=' . $layerid . '&amp;action=leave">Leave Group</a>)';
			break;
		case 2: return '<span style="font-weight: bold">Author</span>. You can view events, and add or modify events in this group. (<a href="layer.php?viewset=' . $layerid . '&amp;action=leave">Leave Group</a>)';
			break;
		case 3: return '<span style="font-weight: bold">Administrator</span>. You have full control over this group.';
			break;
	}
}

// Prints all the events for a day
function printDay($layerid, $seldate)
{
	$result = mysql_query('SELECT (LENGTH(EVENT_DESC) > 0) AS YES_DESC, EVENT_LIST.*
		FROM EVENT_LIST
		WHERE
			EVENT_DATE=' . $seldate . ' AND
			EVENT_RECUR=\'none\' AND
			EVENT_LAYER=' . $layerid . '
		ORDER BY EVENT_TIME') or die('Calendar query failed');

	$repeats = mysql_query('SELECT (LENGTH(EVENT_DESC) > 0) AS YES_DESC, EVENT_LIST.*
		FROM EVENT_LIST
		WHERE
			(EVENT_RECUREND=0 OR EVENT_RECUREND>=' . $seldate . ') AND
			EVENT_RECUR!=\'none\' AND
			EVENT_DATE<=' . $seldate . ' AND
			EVENT_LAYER=' . $layerid . '
		') or die('Repeats query failed');

	$numreps = mysql_num_rows($repeats);

	for($i = 0; $i < $numreps; $i++)
		$repeatnr[$i] = mysql_fetch_array($repeats, MYSQL_ASSOC);

	mysql_free_result($repeats);

	if(mysql_num_rows($result) > 0)
	{
		$ll = mysql_fetch_array($result, MYSQL_ASSOC);
		$dwrs = false;
	}
	else
		$dwrs = true;

	$dwd = ($ll["EVENT_DATE"] > $seldate);
	$j = 0;
	$displayedevent = false;

	while($j < $numreps || !($dwrs || $dwd))
	{
		if($j >= $numreps)
			$loadrecur = false;
		else if($dwrs || $dwd)
			$loadrecur = true;
		else if($repeatnr[$j]["SCHED_PER"] < $ll["SCHED_PER"])
			$loadrecur = true;
		else
			$loadrecur = false;

		if($loadrecur)
		{
			$l = $repeatnr[$j];
			$j++;
			$printevent = showrecur($seldate, $l['EVENT_DATE'], $l['EVENT_RECUR'], $l['EVENT_RECUREND'], $l['EVENT_RECURPARAM'], $l['EVENT_RECURFREQ']);
		}
		else
		{
			$l = $ll;
			if($ll = mysql_fetch_array($result, MYSQL_ASSOC))
			{
				if($ll["EVENT_DATE"] > $seldate)
					$dwd = true;
			}
			else
				$dwrs = true;

			$printevent = true;
		}

		if($printevent)
		{
			$displayedevent = true;

			print '<li>';
			print '<a href="calendar/event.php?view=m&amp;start=&amp;viewset=c&amp;open=' . $l['EVENT_ID'] . '">' . htmlentities($l['EVENT_TITLE']) . '</a>';
			if($l['YES_DESC'])
				print '<span title="description available">...</span>';
			print '</li>';
		}
	}
	
	mysql_free_result($result);
	
	return $displayedevent;
}

// Prints an event inside the month, week, or day views for calendar.php
function printEvent($l, $detailsview)
{
	global $ieplus, $HGVstart, $HGVview, $HGVviewset, $viewmode, $printview;

	// Print the background color
	print '<div class="ee';
	if($l['EVENT_TIME'] == -1 && is_null($l['LAYER_CLASS']))
		print ' ad';
	if(!$printview)
		print ' ev' . $l['LAYERUSER_COLOR'];
	print '">';

	// Print the event time, if available
	if($l['EVENT_TIME'] != -1)
	{
		if($detailsview && $l['EVENT_DURATION'] > 0)
			print '<span class="TimeNo">' . dateTIME(fromSeconds($l['EVENT_TIME'])) . '-' . dateTIME(fromSeconds($l['EVENT_TIME']+$l['EVENT_DURATION'])) . '</span> ';
		else
			print '<span class="TimeNo">' . dateTIME(fromSeconds($l['EVENT_TIME'])) . '</span> ';
	}
		
	// Print the layer name for school events which are not on the Official School Calendar layer
	if($viewmode == VIEWMODE_SCHOOL && $l['LAYER_ID'] != OFFICIAL_SCHOOL_CALENDAR)
		print $l['LAYER_TITLE'] . ': ';

	// Print hyperlink
	if(!$printview)
	{
		print '<a ';
		if($ieplus && !$detailsview)
		{
			print 'onmouseover="pb(\'' . addslashes(htmlentities($l['LAYER_TITLE'])) . '\',\'';
			if($l['EVENT_DURATION'] > 0)
				print dateTIME(fromSeconds($l['EVENT_TIME'])) . ' to ' . dateTIME(fromSeconds($l['EVENT_TIME']+$l['EVENT_DURATION']));
			print '\',\'';
			if(strlen($l['EVENT_LOCATION']) > 0)
				print addslashes(htmlentities($l['EVENT_LOCATION']));
			print '\');" onmouseout="hb();" ';
		}
		print 'class="vv" href="event.php?view=' . $HGVview . '&amp;start=' . $HGVstart . '&amp;viewset=' . $HGVviewset . '&amp;open=' . $l['EVENT_ID'] . '">';
	}
	print htmlentities($l['EVENT_TITLE']);
	if(!$printview)
	{
		print '</a>';
		if($l['YES_DESC']) // Description is available. Print an ellipsis
			print '<span title="description available">...</span>';
	}
	if($detailsview && strlen($l['EVENT_LOCATION']) > 0)
		print ' (' . htmlentities($l['EVENT_LOCATION']) . ')';
	print '</div>';
}

// Writes an interval (in seconds) in some natural and concise way
function timedescriptor($d)
{
	return timedescriptorf($d, 1);
}

// Writes an interval (in seconds) in some natural and concise way, to however many significant figures
function timedescriptorf($d, $accuracy)
{
	if($d == 1)
	{
		$retval = $d . "&nbsp;second";
		$e = $d;
	}
	else if($d < 60)
	{
		$retval = $d . "&nbsp;seconds";
		$e = $d;
	}
	else if($d < 120)
	{
		$retval = floor($d/60) . "&nbsp;minute";
		$e = 60 * floor($d/60);
	}
	else if($d < 3600)
	{
		$retval = floor($d/60) . "&nbsp;minutes";
		$e = 60 * floor($d/60);
	}
	else if($d < 7200)
	{
		$retval = floor($d/3600) . "&nbsp;hour";
		$e = 3600 * floor($d/3600);
	}
	else if($d < 86400)
	{
		$retval = floor($d/3600) . "&nbsp;hours";
		$e = 3600 * floor($d/3600);
	}
	else if($d < 172800)
	{
		$retval = floor($d/86400) . "&nbsp;day";
		$e = 86400 * floor($d/86400);
	}
	else if($d < 604800)
	{
		$retval = floor($d/86400) . "&nbsp;days";
		$e = 86400 * floor($d/86400);
	}
	else if($d < 1209600)
	{
		$retval = floor($d/604800) . "&nbsp;week";
		$e = 604800 * floor($d/604800);
	}
	else
	{
		$retval = floor($d/604800) . "&nbsp;weeks";
		$e = 604800 * floor($d/604800);
	}
	
	if($e == $d || $accuracy == 1)
		return $retval;
	else
		return $retval . ' ' . timedescriptorf($d - $e, $accuracy - 1);
}

// Redirects to the appropriate page, given a layer and a view type
function redirectLayer($startdate, $subset, $view)
{
	if($view == 'l' || $view == 'r')
		header('Location: layer.php?view=' . $view . '&start=' . $startdate . '&viewset=' . $subset);
	else
		header('Location: calendar.php?view=' . $view . '&start=' . $startdate . '&viewset=' . $subset);
}

// Prints a time. Sample: 6:00p
function dateTIME($timein) {
	return date("h:i", $timein) . substr(date("a", $timein),0,1);
}

// Prints dates in various formats suitable for display
function printDATESHORT($idf) {
	return idfj($idf) . ' ' . idfMM($idf) . ' ' . idfYY($idf);
}
function printDATEMID($idf) {
	return idfDD($idf) . ' ' . idfj($idf) . ' ' . idfMM($idf) . ' ' . idfYY($idf);
}
function printDATELONG($idf) {
	return idfl($idf) . ' ' . idfj($idf) . ' ' . idfFF($idf) . ' ' . idfYY($idf);
}

// Returns a timestamp with the specified time (as measured in seconds after midnight)
function fromSeconds($second) {
	return mktime(0, 0, $second, 1, 1, 2000, 0);
}

// Converts an IDF to a timestamp
function fromIDF($idf) {
	return mktime(0, 0, 0, idfm($idf), idfd($idf), idfY($idf), 0);
}

// Various idf output functions.
// They correspond to the options on date(); double letters mean capitals
// For example: idfd() means date('d'); idfDD means date('D');
function idfj($idf) {
	return idfd($idf) + 0;
}
function idfd($idf) {
	return substr($idf, 6, 2);
}
function idfn($idf) {
	return idfm($idf) + 0;
}
function idfm($idf) {
	return substr($idf, 4, 2);
}
function idfDD($idf) {
	return substr(idfl($idf), 0, 3);
}
function idfl($idf) {
	return weekdaynames(idfw($idf));
}
function idfMM($idf) {
	return substr(idfFF($idf), 0, 3);
}
function idfFF($idf) {
	return monthnames(idfm($idf));
}
function idfYY($idf) {
	return substr($idf, 0, 4);
}
function idfy($idf) {
	return substr($idf, 2, 2);
}
function idfw($idf) {
	return date('w', fromIDF($idf));
}
function idft($idf) {
	return date('t', fromIDF($idf));
}

// Create a new idf, given mdy parts
function makeidf($month, $day, $year)
{
	return date('Ymd', mktime(0, 0, 0, $month, $day, $year, 0));
}

// Adds leading zeros to an integer to make it the specified length
function intToLength($num, $digits)
{
	return substr($num + ('1' . pow('0', $digits)),1);
}

// alias to mktime in which dst is turned off
function MAKETIME($hour, $minute, $second, $month, $day, $year)
{
	return mktime($hour, $minute, $second, $month, $day, $year, 0);
}

// Determines a user's status in a group
function layeruserlevel($layer, $u)
{
	if(is_numeric($layer))
	{
		if(is_numeric($u))
		{
			$UserRec = mysql_query('SELECT LAYERUSER_ACCESS FROM LAYERUSER_LIST WHERE LAYERUSER_LAYER=' . $layer . ' AND LAYERUSER_USER=' . $u) or die('Authentication failed.');
			if($l = mysql_fetch_array($UserRec, MYSQL_ASSOC))
				$retval = $l['LAYERUSER_ACCESS'];
			else
				$retval = -1;
			mysql_free_result($UserRec);
		}
		else
			$retval = -1;
		
		return $retval;
	}
	else
		return -1;
}

// Grabs an arbitrary field value for a given row in EVENT_LIST (fields in the corresponding entry of LAYER_LIST are joined too)
function eventlayerproperty($event, $p)
{
	if(is_numeric($event))
	{
		$UserRec = mysql_query('SELECT ' . $p . ' FROM LAYER_LIST INNER JOIN EVENT_LIST ON LAYER_ID=EVENT_LAYER WHERE EVENT_ID=' . $event) or die('Authentication failed.');
		if($l = mysql_fetch_array($UserRec, MYSQL_ASSOC))
			$retval = $l[$p];
		else
			$retval = '';
		mysql_free_result($UserRec);
		
		return $retval;
	}
	else
		return '';
}

// Determines the status of a user in an given event's layer
function eventuserlevel($event, $u)
{
	if(is_numeric($event))
	{
		if(is_numeric($u))
		{
			$UserRec = mysql_query('SELECT LAYERUSER_ACCESS FROM LAYERUSER_LIST INNER JOIN EVENT_LIST ON LAYERUSER_LAYER=EVENT_LAYER WHERE EVENT_ID=' . $event . ' AND LAYERUSER_USER=' . $u) or die('Authentication failed.');
			if($l = mysql_fetch_array($UserRec, MYSQL_ASSOC))
				$retval = $l['LAYERUSER_ACCESS'];
			else
				$retval = -1;
			mysql_free_result($UserRec);
		}
		else
			$retval = -1;
			
		return $retval;
	}
	else
		return -1;
}

// Weekday names
function weekdaynames($i)
{
	switch($i)
	{
		case 0:	return 'Sunday'; break;
		case 1:	return 'Monday'; break;
		case 2:	return 'Tuesday'; break;
		case 3:	return 'Wednesday'; break;
		case 4:	return 'Thursday'; break;
		case 5:	return 'Friday'; break;
		case 6:	return 'Saturday'; break;
	}
}

// Month names
function monthnames($i)
{
	switch($i)
	{
		case 1: return 'January'; break;
		case 2: return 'February'; break;
		case 3: return 'March'; break;
		case 4: return 'April'; break;
		case 5: return 'May'; break;
		case 6: return 'June'; break;
		case 7: return 'July'; break;
		case 8: return 'August'; break;
		case 9: return 'September'; break;
		case 10: return 'October'; break;
		case 11: return 'November'; break;
		case 12: return 'December'; break;
	}
}

// Prints an ordinal: 1st, 2nd, 3rd, etc.
function printordinal($dayin)
{
	if ($dayin % 10 == 1 && $dayin % 100 != 11)
		$ex = "st";
	else if ($dayin % 10 == 2 && $dayin % 100 != 12)
		$ex = "nd";
	else if ($dayin % 10 == 3 && $dayin % 100 != 13)
		$ex = "rd";
	else
		$ex = "th";
		
	return $dayin . $ex;
}

// Numbers days consecutively
function dayno($dayin)
{
	return floor(fromIDF($dayin)/86400);
}

// Numbers weeks consecutively
function weekno($dayin)
{
	return floor((fromIDF($dayin) -259200)/604800);
}

// Numbers months consecutively
function monthno($idf)
{
	return 12 * yearno($idf) + idfm($idf);
}

// Numbers years consecutively
function yearno($idf)
{
	return idfy($idf) - 1970;
}

// Prints a string telling about an event's recurrence
function recurrenceinfo($s, $r, $i, $p, $e)
{
	if($r == 'none')
		$retval = 'None';
	else
	{
		if($r == 'day')
		{
			if($i == 1)
				$retval = '';
			else
				$retval = 'Every ' . $i . ' days ';
		}
		else if($r == 'week')
		{
			if($i == 1)
				$retval = 'Every ';
			else
				$retval = 'Every ' . $i . ' weeks on ';
		
			if($p == 62)
			{
				if($i == 1)
					$retval .= 'weekday ';
				else
					$retval .= 'weekdays ';
			}
			else if($p == 65)
			{
				if($i == 1)
					$retval .= 'weekend ';
				else
					$retval .= 'weekends ';
			}
			else
			{
				$n = 0;
				
				for($j = 0; $j < 7; $j++)
					if(($p >> $j) % 2 == 1)
						$n++;
						
				$nn = $n;
			
				for($j = 0; $j < 7; $j++)
				{
					if(($p >> $j) % 2 == 1)
					{
						$retval .= substr(weekdaynames($j), 0, 3);
					
						if($n == 1)
							$retval .= ' ';
						else if($n == 2)
							$retval .= ' and ';
						else
							$retval .= ', ';
						
						$n--;
					}
				}
			}
		}
		else if($r == 'month')
		{
			if($i == 1)
				$retval = 'Every month ';
			else
				$retval = 'Every ' . $i . ' months ';
				
			if($p == 0)
				$retval .= 'on the ' . printordinal(idfj($s)) . ' of the month ';
			else if($p == 1)
				$retval .= 'on the ' . printordinal(1+floor((idfj($s)-1)/7)) . ' ' . idfDD($s) . ' of the month ';
			else if($p == 2)
				$retval .= 'on the last ' . idfl($s) . ' of the month ';
		}
		else if($r == 'year')
		{
			if($i == 1)
				$retval = 'Every year ';
			else
				$retval = 'Every ' . $i . ' years ';
				
			if($p == 0)
				$retval .= 'on ' . idFMM($s) . ' ' . idfj($s) . ' ';
			else if($p == 1)
				$retval .= 'on the ' . printordinal(1+floor((idfj($s)-1)/7)) . ' ' . idfDD($s) . ' of ' . idFMM($s) . ' ';
			else if($p == 2)
				$retval .= 'on the last ' . idfl($s) . ' of ' . idFMM($s) . ' ';
		}
		else
			$retval = '';
			
		if($e == 0)
			$retval .= 'effective ' . printDATESHORT($s);
		else if($i == 1 && $r == 'day')
			$retval = printDATESHORT($s) . ' to ' . printDATESHORT($e);
		else
			$retval .= 'from ' .  printDATESHORT($s) . ' to ' . printDATESHORT($e);
	}
	
	return $retval;
}

// Determines whether a recurring event takes place on an arbitrary date
function showrecur($seldate, $event_date, $event_recur, $event_recurend, $event_recurparam, $event_recurfreq)
{
	if($seldate >= $event_date && ($seldate <= $event_recurend || 0 == $event_recurend))
	{
		if($event_recur == 'day')
			return ((dayno($seldate) - dayno($event_date)) % $event_recurfreq == 0);
		else if($event_recur == 'week')
			return (weekno($seldate) - weekno($event_date)) % $event_recurfreq == 0 &&
				($event_recurparam >> idfw($seldate)) % 2 == 1;
		else if($event_recur == 'month' && $event_recurparam == 0)
			return ((monthno($seldate) - monthno($event_date)) % $event_recurfreq == 0) &&
				(
					idfj($event_date) == idfj($seldate) ||
					(idfj($event_date) > idft($seldate) && idft($seldate) == idfj($seldate))
				);
		else if($event_recur == 'month' && $event_recurparam == 1)
			return ((monthno($seldate) - monthno($event_date)) % $event_recurfreq == 0) &&
				idfw($event_date) == idfw($seldate) &&
				(
					floor((idfj($event_date) - 1) / 7) == floor((idfj($seldate) - 1) / 7)  ||
					(idfj($event_date) > 28 && idft($seldate) - idfj($seldate) < 7)
				);
		else if($event_recur == 'month' && $event_recurparam == 2)
			return ((monthno($seldate) - monthno($event_date)) % $event_recurfreq == 0) &&
				idfw($event_date) == idfw($seldate) &&
				idft($seldate) - idfj($seldate) < 7;
		else if($event_recur == 'year' && $event_recurparam == 0)
			return ((yearno($seldate) - yearno($event_date)) % $event_recurfreq == 0) &&
				idfn($event_date) == idfn($seldate) &&
				(
					(idfj($event_date) == idfj($seldate)) ||
					(idfj($event_date) > idft($seldate) && idft($seldate) == idfj($seldate))
				);
		else if($event_recur == 'year' && $event_recurparam == 1)
			return ((yearno($seldate) - yearno($event_date)) % $event_recurfreq == 0) &&
				idfn($event_date) == idfn($seldate) &&
				idfw($event_date) == idfw($seldate) &&
				(
					floor((idfj($event_date) - 1) / 7) == floor((idfj($seldate) - 1) / 7) ||
					(idfj($event_date) > 28 && idft($seldate) - idfj($seldate) < 7)
				);
		else if($event_recur == 'year' && $event_recurparam == 2)
			return ((yearno($seldate) - yearno($event_date)) % $event_recurfreq == 0) &&
				idfn($event_date) == idfn($seldate) &&
				idfw($event_date) == idfw($seldate) &&
				idft($seldate) - idfj($seldate) < 7;
		else
			return false;
	}
	else
		return false;
}

?>