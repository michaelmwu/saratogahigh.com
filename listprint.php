<?
// Michael Wu | saratogahigh.com
// calendar/listprint.php: displays a printable list of events in a variety of views and subsets.

include "../db.php";
include "cal.php";

if(ereg('MSIE [56789]\.', $_SERVER['HTTP_USER_AGENT']))
	$ieplus = true;

	$start = '';
	$end = '';

	$eventlist = array();

// Get starting date
	$seldate = $HGVstart;
	$seldayno = idfd($seldate);
	$selmonthno = idfm($seldate);
	$selyearno = idfYY($seldate);

	if($HGVview == 'd')
	{
		$firstday = $seldate;
		$lastday = makeidf($selmonthno, 1 + $seldayno, $selyearno);
		$viewdescriptor = 'Day View';
	}
	else if($HGVview == 's')
	{
		$firstday = makeidf($selmonthno, 1 + $seldayno - idfw($seldate), $selyearno);
		$lastday = makeidf($selmonthno, 5 + $seldayno - idfw($seldate), $selyearno);
		$viewdescriptor = 'School Week View';		
	}
	else if($HGVview == 'w')
	{
		$firstday = makeidf($selmonthno, $seldayno - idfw($seldate), $selyearno);
		$lastday = makeidf($selmonthno, 7 + $seldayno - idfw($seldate), $selyearno);
		$viewdescriptor = 'Week View';
	}
	else if($HGVview == 'm')
	{
		$firstday = makeidf($selmonthno, 1, $selyearno);
		$lastday = makeidf($selmonthno + 1, 1, $selyearno);
		$viewdescriptor = 'Month View';
	}

	if($loggedin && $HGVviewset == 'p')
	{
		$caltitle = "My Calendar";
		$viewmode = VIEWMODE_PERSONAL;
	}
	else if($loggedin && $HGVviewset == 'a')
	{
		$caltitle = "All My Groups";
		$viewmode = VIEWMODE_ALL; // All Groups
	}
	else if($loggedin && $HGVviewset == 'c' && $isstudent)
	{
		$caltitle = "My Homework";
		$viewmode = VIEWMODE_HOMEWORK; // Homework Groups
	}
	else if($HGVviewset == 's')
	{
		$caltitle = "School Calendar";
		$viewmode = VIEWMODE_SCHOOL; // School Calendar
	}
	else if ( is_numeric($HGVviewset) )
	{
		$viewmode = VIEWMODE_GROUP; // Single group
		$cresult = mysql_query("SELECT LAYER_TITLE FROM LAYER_LIST WHERE LAYER_ID=$HGVviewset") or die("Could not query calendar.");
		if($title = mysql_fetch_array($cresult, MYSQL_ASSOC))
		{
			$caltitle = $title['LAYER_TITLE'];
		}
	}
	else
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

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
	<title><?= $caltitle ?>: <? print $viewdescriptor; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<meta name="vs_targetSchema" content="http://schemas.microsoft.com/intellisense/ie5">
	<link rel="stylesheet" type="text/css" href="../shs.css">
	<link rel="stylesheet" type="text/css" href="calprint.css">
	<style type="text/css"><!--
	.date {font-size: medium; font-weight: bold;}
	-->
	</style>
</head>
<body>
<h2><?= $caltitle ?>: <? print $viewdescriptor; ?></h2>
<?
	include 'cal-dbget.php';

	while( $l = mysql_fetch_array($result, MYSQL_ASSOC) )
	{
		$eventdate = $l['EVENT_DATE'];
		$eventlist["$eventdate"] .= "<b>";

		if($l['EVENT_TIME'] != -1)
		{
			$eventlist["$eventdate"] .= dateTIME(fromSeconds($l['EVENT_TIME'])) . "</b> " . $l['EVENT_TITLE'] . "<BR>\n";
		}
		else
		{
			$eventlist["$eventdate"] .= $l['EVENT_TITLE'] . "</b><BR>\n";
		}	
	}

	while( $l = mysql_fetch_array($repeats, MYSQL_ASSOC) )
	{
		for($i = fromIDF($firstday); $i < fromIDF($lastday); $i += 86400)
		{
			$eventdate = date('Ymd', $i);
			if( showrecur($eventdate, $l['EVENT_DATE'], $l['EVENT_RECUR'], $l['EVENT_RECUREND'], $l['EVENT_RECURPARAM'], $l['EVENT_RECURFREQ']) )
			{
				$eventlist["$eventdate"] .= "<b>";

				if($l['EVENT_TIME'] != -1)
				{
					$eventlist["$eventdate"] .= dateTIME(fromSeconds($l['EVENT_TIME'])) . "</b> " . $l['EVENT_TITLE'] . "<BR>\n";
				}
				else
				{
					$eventlist["$eventdate"] .= $l['EVENT_TITLE'] . "</b><BR>\n";
				}
			}
		}
	}

	if( count($eventlist) > 0 )
	{
		ksort($eventlist);

		foreach ($eventlist as $key => $value)
		{
			print "<BR><span class='date'>" . printDATEMID($key) . "</span><BR>\n";
			print $value;
		}
	}
	else
		print "No Events";
?>
</body>
</html>