<?
// Philip Sung | 0x7d3 | saratogahigh.com
// cm/calendar.php: prints class calendar

if($_SERVER['PATH_INFO'] != '/cm/index.php')
	die('Error.');

include '../calendar/cal.php';

$rsthislayer = mysql_query('SELECT * FROM LAYER_LIST WHERE LAYER_ID=' . $cid);
if($thislayer = mysql_fetch_array($rsthislayer, MYSQL_ASSOC))
{
	if($thislayer['LAYER_OPEN'])
	{
		$seldate = makecuridf(8);
		// Get start and end dates
		$seldayno = idfd($seldate);
		$selmonthno = idfm($seldate);
		$selyearno = idfYY($seldate);
		$firstday = $seldate;
		$lastday = makeidf($selmonthno, $seldayno + 14, $selyearno);
		$nextday = $firstday;

		// Load all events
		$result = mysql_query('SELECT (LENGTH(EVENT_DESC) > 0) AS YES_DESC, EVENT_LIST.*
			FROM EVENT_LIST
			WHERE EVENT_LAYER=' . $cid . ' AND
				EVENT_DATE>=' . $firstday . ' AND EVENT_RECUR=\'none\' AND EVENT_DATE<' . $lastday . '
			ORDER BY EVENT_DATE, EVENT_TIME, EVENT_ID') or die('Calendar query failed');

		$repeats = mysql_query('SELECT (LENGTH(EVENT_DESC) > 0) AS YES_DESC, EVENT_LIST.*
			FROM EVENT_LIST
			WHERE EVENT_LAYER=' . $cid . ' AND
				(EVENT_RECUREND=0 OR EVENT_RECUREND>=' . $firstday . ') AND EVENT_RECUR!=\'none\' AND EVENT_DATE<' . $lastday . '
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

		$firstday = true;
		$eventstoday = false;

		// Print a date heading over each day
		while($nextday < $lastday)
		{	
			$cj = idfj($nextday);
			$cm = idfn($nextday);
			$cy = idfYY($nextday);

			$firstday = false;

			$dwd = ($ll["EVENT_DATE"] > $nextday);
			$j = 0;

			$cursched = 0;


			// Print events
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
					$eventstoday = true;
					print '<li><span style="font-family: monospace">';
					print idfDD($nextday) . ' ' . idfd($nextday) . ' ' . idfMM($nextday) . '</span>. ';
					
					if($l['EVENT_TIME'] != -1)
						print '<span style="font-weight: bold">' . dateTIME(fromSeconds($l['EVENT_TIME'])) . '-' . dateTIME(fromSeconds($l['EVENT_TIME']+$l['EVENT_DURATION'])) . '</span> ';
					print '<a href="/calendar/event.php?viewset=' . $cid . '&amp;view=l&amp;start=' . $nextday . '&amp;open=' . $l['EVENT_ID'] . '">' . htmlentities($l['EVENT_TITLE']) . '</a>';
					if(strlen($l['EVENT_LOCATION']) > 0)
						print ' (' . htmlentities($l['EVENT_LOCATION']) . ')';
					print '</li>';
				}
			}
			
			$nextday = makeidf($cm, $cj + 1, $cy);
		}
		mysql_free_result($result);
		
		if(!$eventstoday)
			print '<li>No assignments listed for next two weeks</li>';
	}
}
?>