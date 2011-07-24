<?
// Philip Sung | 0x7d3 | saratogahigh.com
// calendar/month.php: prints a month calendar

// Get the first day of the current month
$curmonth = makeidf(date('n'), 1, date('Y'));

// Get the first day of selected month, and of the months before and after it
$selmonth = makeidf($selmonthno, 1, $selyearno);
$lastmonth = makeidf($selmonthno - 1, 1, $selyearno);
$nextmonth = makeidf($selmonthno + 1, 1, $selyearno);

// Figure out where to print a link to the current month
if($curmonth < $lastmonth)
	$crelpos = -2;
else if($curmonth > $nextmonth)
	$crelpos = 2;
else if($curmonth == $lastmonth)
	$crelpos = -1;
else if($curmonth == $nextmonth)
	$crelpos = 1;
else
	$crelpos = 0;

if($printview)
{
	// Print group name and current month
	print '<h1>' . $caltitle . ': ' . idfFF($selmonth) . ' ' . idfYY($selmonth) . '</h1>';
}
else
{
	// Print navigation tabs
?>
	<table style="margin-top: 8px; font-size: large" cellspacing="0" cellpadding="3"><tr>
	<? if($crelpos <= -1) { ?>
		<td valign="bottom"><a class="curmonth" href="calendar.php?view=m&amp;viewset=<?= $HGVviewset ?>&amp;start=<?= $curmonth ?>"><?= idfFF($curmonth) . ' ' . idfYY($curmonth) ?></a></td>
	<? } ?>
	<? if($crelpos != -1) { ?>
		<td valign="bottom"><a href="calendar.php?view=m&amp;viewset=<?= $HGVviewset ?>&amp;start=<?= $lastmonth ?>"><?= idfFF($lastmonth) . ' ' . idfYY($lastmonth) ?></a></td>
	<? } ?>
	<td valign="bottom" style="background-color: #666688; color: #ffffff; font-weight: bold"><?= idfFF($selmonth) . ' ' . idfYY($selmonth) ?></td>
	<? if($crelpos != 1) { ?>
		<td valign="bottom"><a href="calendar.php?view=m&amp;viewset=<?= $HGVviewset ?>&amp;start=<?= $nextmonth ?>"><?= idfFF($nextmonth) . ' ' . idfYY($nextmonth) ?></a></td>
	<? } ?>
	<? if($crelpos >= 1) { ?>
		<td valign="bottom"><a class="curmonth" href="calendar.php?view=m&amp;viewset=<?= $HGVviewset ?>&amp;start=<?= $curmonth ?>"><?= idfFF($curmonth) . ' ' . idfYY($curmonth) ?></a></td>
	<? } ?>
		<td>Jump to: <select id="FEm"><?
			for($i=1; $i <= 12; $i++)
			{
				print '<option value="' . substr(100 + $i, 1, 2) . '"';
				if($selmonthno == $i)
					print ' selected';
				print '>' . monthnames($i) . '</option>';
			}
		?></select> <input id="FEy" value="<?= $selyearno ?>" size="5"> <input type="button" value="Go" onclick="datejump();"></td>
	</tr></table>
<?
}

// Set some bounds
$firstday = makeidf($selmonthno, 1 - idfw($selmonth), $selyearno);
$lastday = makeidf($selmonthno + 1, 7 - idfw($nextmonth), $selyearno);

// Get the actual data
include 'cal-dbget.php';

$nextday = $firstday;

if($printview)
	print '<table cellpadding="2" cellspacing="0" border="1" style="border-collapse: collapse; width: 100%; table-layout: fixed">';
else
	print '<table cellpadding="0" cellspacing="0" border="1" style="border-collapse: collapse; width: 100%; table-layout: fixed; border: 2px solid #666688">';

// Print the days of the week
print '<thead>';
print '<tr>';
for($i = 1; $i <= 7; $i++)
	print '<td class="calendarHeaderRow">' . weekdaynames($i - 1) . '</td>';
print '</tr>';
print '</thead><tbody>';

// Current month and year
$cm = 0;
$cy = 0;

// Load the repeating events into an array
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

// Loop through days until this month is over
while($nextday < $nextmonth)
{
	// Print a whole week at a time, because we usually don't want to stop at the last day of a month
	print '<tr class="CalendarRow">';
	for($i = 1; $i <= 7; $i++)
	{
		// Get day, month, and year
		$cj = idfj($nextday);
		$cm = idfn($nextday);
		$cy = idfYY($nextday);
		
		// Print different cell types depending on whether day is inside current month and whether day is today
		if(idfm($nextday) == $selmonthno)
		{
			if($nextday == $cur_idf)
			{
				$ytdclass = 'calendarMonthCell currentDate';
				$ydivclass = 'DateNoCD';
			}
			else
			{
				$ytdclass = 'calendarMonthCell';
				$ydivclass = 'DateNo';
			}
		}
		else
		{
			$ytdclass = 'calendarMonthCell dateOutOfRange';
			$ydivclass = 'DateNoOOR';
		}
		
		print '<td valign="top" class="' . $ytdclass . '"';
		if(!$printview)
			print ' ondblclick="goAdd(' . $nextday . ');"';
		print '>';
		
		// Print the floating date numbers

		print '<div class="' . $ydivclass . '">';
		print $cj;
		
		if($cj == 1 || $firstday)
			print " " . idfMM($nextday);
		
		if($cj == 1 && $cm == 1)
			print " " . idfy($nextday);
			
		print '</div>';

		$firstday = false;

		$dwd = ($ll["EVENT_DATE"] > $nextday);
		$j = 0;

		$cursched = 0;

		// $dwrs = done with record set;
		// $dwd = done with (current) day.
		// ($j < $numreps) means that there are repeating events remaining...
		// !($dwrs || $dwd) means there are nonrepeating events remaining today.
		while($j < $numreps || !($dwrs || $dwd))
		{	
			// Choose which event to load (from the recurring or the non-recurring queue); in the event that both are available, we choose the one that comes first in time.
			
			if($j >= $numreps)
				$loadrecur = false;
			else if($dwrs || $dwd)
				$loadrecur = true;
			else if($repeatnr[$j]["EVENT_TIME"] < $ll["EVENT_TIME"])
				$loadrecur = true;
			else
				$loadrecur = false;
			
			// Actually load the selected event
			if($loadrecur)
			{
				$l = $repeatnr[$j];
				$j++;
				
				// Figure out whether to print this event
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
				if($cursched != $l['SCHED_ID'])
				{
					print '<div class="ee ch"><span style="font-weight: bold">' . $l['SCHED_PER'] . '</span> ' . $l['CLASS_SHORTNAME'] . '</div>';
					$cursched = $l['SCHED_ID'];
				}
			
				printEvent($l, false);
			}
		}

		print '</td>';
		
		$nextday = makeidf($cm, $cj + 1, $cy);
	}
	print '</tr>';
}
print '</tbody></table>';
mysql_free_result($result);

?>