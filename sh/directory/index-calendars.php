<?
	
	// Contact info
	$mycalendars = mysql_query('SELECT LAYER_ID, LAYER_TITLE, (LAYER_PERSONAL=' . $sid . ') AS P FROM LAYER_LIST LEFT JOIN LAYERUSER_LIST ON LAYER_ID=LAYERUSER_LAYER AND LAYERUSER_USER=' . $sid . ' AND LAYERUSER_ACCESS=3 WHERE LAYER_OPEN=1 AND (Not (LAYERUSER_USER Is NULL) OR LAYER_PERSONAL=' . $sid . ') ORDER BY LAYER_TITLE');
	
	if(mysql_num_rows($mycalendars) > 0)
	{
		print '<p style="font-size: medium">These public calendars are maintained by ' . $l['USER_FN'] . ' ' . $l['USER_LN'] . '.</p>';
		print '<table style="font-size: medium">';

		while($curcal = mysql_fetch_array($mycalendars, MYSQL_ASSOC))
		{
			print '<tr><td><a ';
			if($curcal['P'])
				print 'style="font-weight: bold" ';
			print 'href="/calendar/calendar.php?viewset=' . $curcal['LAYER_ID'] . '">' . htmlentities($curcal['LAYER_TITLE']) . '</a></td><td>';
			if($curcal['P'])
				print 'Personal Calendar';
			print '</td></tr>';		
		}

		print '</table>';
	}
	else
	{
		print '<p>This person has no public calendars.</p>';
	}
?>