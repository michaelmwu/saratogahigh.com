<?
include 'db.php';
include 'calendar/cal.php';

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");              // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified
header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                                    // HTTP/1.0

$seldate     = makecuridf(-9);
$seldayno    = idfd($seldate);
$selmonthno  = idfm($seldate);
$selyearno   = idfYY($seldate);
$firstday    = $seldate;
$lasttestday = makeidf($selmonthno, $seldayno + 21, $selyearno);
$nextday     = $firstday;

$block       = 'bos.php';
$insideblock = 'temp.php';

$xml->handle_request();

function mail_block() { ?>
		<h2 class="red" onMouseOver="startMoveCursor(event);" onMouseDown="startDrag(event);"><a href="/mail/">My Mail</a></h2><div class="headed">
		<p style="margin-bottom: 6px; margin-top: 0px;">
		<form style="margin: 0px" action="mail/compose.php" name="mf" method="POST">
		<p style="margin: 0px">To: (type full name)</p>
		<p style="margin: 0px"><input onfocus="document.getElementById('mailcomposebox').style.display = 'block'; document.getElementById('mailcomposearrow').style.display = 'none';" style="width: 90%;" type="text" name="xto" value="" size="55"><img src="/imgs/arrow-down.gif" onclick="document.getElementById('mailcomposebox').style.display = 'block'; document.getElementById('mailcomposearrow').style.display = 'none';" id="mailcomposearrow" style="vertical-align: middle" alt="(more)"></p>
		<div style="display: none" id="mailcomposebox">
		<p style="margin: 0px">Subject:<br><input style="width: 90%" type="text" name="xsubj" value="" size="40"></p>
		<p style="margin: 0px"><textarea name="xmsgtxt" rows="3" style="width: 90%" cols="55"></textarea></p>
		<p style="margin: 0px; font-weight: bold; text-align: right"><input type="submit" name="go" value="Send"></p>
		</div>
		</form>
		</div>
		<div class="footer"><a href="mail/">More Mail...</a></div>
<? }

function notes_block($userid) { ?>
		<h2 class="red" onMouseOver="startMoveCursor(event);" onMouseDown="startDrag(event);"><a href="/notepad/">My Notes</a></h2><div class="headed">
		<p style="margin: 0px"><a href="notepad/"><?
		$cr = mysql_query('SELECT COUNT(*) FROM NOTEPAGE_LIST WHERE NOTEPAGE_OWNER=' . $userid) or die("Query failed");
		$l = mysql_fetch_array($cr, MYSQL_ASSOC);
		print $l['COUNT(*)'];
		?> note(s) saved</a>.</p>
		<? if (!TooManyNotes($userid)) { ?>
		<p style="margin: 0px; font-weight: bold">New Note:</p>
		<form action="notepad/" method="POST">
		<textarea name="entrytext" rows="3" cols="22"></textarea>
		<br><input type="hidden" name="go" value="Save"><input type="submit" value="Save">
		</form>
		<?
		}
		$entries = mysql_query('SELECT NOTEPAGE_ID, NOTEPAGE_VALUE, NOTEPAGE_MODIFIED as TS FROM NOTEPAGE_LIST WHERE NOTEPAGE_OWNER=' . $userid . ' ORDER BY NOTEPAGE_MODIFIED DESC LIMIT 3') or die('Query failed.');
		if(mysql_num_rows($entries) > 0)
		{
			print '<p style="margin: 0px; font-weight: bold">Most Recent:</p>';
			while($l = mysql_fetch_array($entries, MYSQL_ASSOC))
				print '<p style="margin: 0px;"><a style="font-weight: bold" href="notepad/page.php?id=' . $l['NOTEPAGE_ID'] . '">' . date('n/j', strtotime($l['TS'])) . '</a> ' .  htmlspecialchars(shorten_string(nl2slash($l['NOTEPAGE_VALUE']), 40)) . '</p>';
		}
		print '</div>';
}

function calendar_block($userid) { ?>
		<h2 class="red" onMouseOver="startMoveCursor(event);" onMouseDown="startDrag(event);"><a href="/calendar/">My Calendar</a></h2><div class="headed">
		<ul class="flat" style="margin: 0px">
		<?
		$applics = mysql_query('SELECT LAYER_ID, LAYER_TITLE, COUNT(LAYERUSER_LIST.LAYERUSER_ID) AS C FROM LAYERUSER_LIST AS ME_LIST INNER JOIN LAYER_LIST ON ME_LIST.LAYERUSER_LAYER=LAYER_ID INNER JOIN LAYERUSER_LIST ON LAYER_ID=LAYERUSER_LIST.LAYERUSER_LAYER WHERE ME_LIST.LAYERUSER_ACCESS=3 AND ME_LIST.LAYERUSER_USER=' . $userid . ' AND LAYERUSER_LIST.LAYERUSER_ACCESS=0 GROUP BY LAYER_ID') or die("Query failed");
		if(mysql_num_rows($applics) > 0)
		{
			print '<li><span style="font-weight: bold">Alerts</span><ul class="flat">';
			while($l = mysql_fetch_array($applics, MYSQL_ASSOC))
				print '<li>' . $l['C'] . ' person(s) applied to join <a href="calendar/layer.php?viewset=' . $l['LAYER_ID'] . '">' . $l['LAYER_TITLE'] . '</a>.</li>';
			print '</ul></li>';
		}
		mysql_free_result($applics);

		$seldate = makecuridf(8);
		$seldayno = idfd($seldate);
		$selmonthno = idfm($seldate);
		$selyearno = idfYY($seldate);
		$firstday = $seldate;
		$lastday = makeidf($selmonthno, $seldayno + 4, $selyearno);
		$nextday = $firstday;

		$result = mysql_query('SELECT (LENGTH(EVENT_DESC) > 0) AS YES_DESC, LAYER_CLASS, EVENT_LIST.*, LAYER_TITLE, LAYERUSER_COLOR
			FROM LAYERUSER_LIST LEFT JOIN CALCOLOR_LIST ON LAYERUSER_COLOR=CALCOLOR_ID INNER JOIN LAYER_LIST ON LAYERUSER_LAYER=LAYER_ID INNER JOIN EVENT_LIST ON LAYER_ID=EVENT_LAYER
			WHERE LAYERUSER_ACCESS > 0 AND  LAYERUSER_USER=' . $userid . ' AND LAYERUSER_DISPLAY=1 AND
				EVENT_DATE>=' . $firstday . ' AND EVENT_RECUR=\'none\' AND EVENT_DATE<' . $lastday . ' AND LAYERUSER_ACCESS>0
			ORDER BY EVENT_DATE, EVENT_TIME, EVENT_ID') or die('Calendar query failed: ' . mysql_error());

		$repeats = mysql_query('SELECT (LENGTH(EVENT_DESC) > 0) AS YES_DESC, LAYER_CLASS, EVENT_LIST.*, LAYER_TITLE, LAYERUSER_COLOR
			FROM LAYERUSER_LIST LEFT JOIN CALCOLOR_LIST ON LAYERUSER_COLOR=CALCOLOR_ID INNER JOIN LAYER_LIST ON LAYERUSER_LAYER=LAYER_ID INNER JOIN EVENT_LIST ON LAYER_ID=EVENT_LAYER
			WHERE LAYERUSER_ACCESS > 0 AND LAYERUSER_USER=' . $userid . ' AND LAYERUSER_DISPLAY=1 AND
				(EVENT_RECUREND=0 OR EVENT_RECUREND>=' . $firstday . ') AND EVENT_RECUR!=\'none\' AND EVENT_DATE<' . $lastday . ' AND LAYERUSER_ACCESS>0
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

		while($nextday < $lastday)
		{

			$cj = idfj($nextday);
			$cm = idfn($nextday);
			$cy = idfYY($nextday);

			print '<li><span style="font-weight: bold">' . idfl($nextday) . ' ' . $cj . ' ' . idfFF($nextday) . '</span>';

			print '<ul class="flat">';

			$firstday = false;

			$dwd = ($ll["EVENT_DATE"] > $nextday);
			$j = 0;

			$cursched = 0;

			$eventstoday = false;

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
					print '<li>';
					if($l['EVENT_TIME'] != -1)
						print '<span style="font-weight: bold">' . dateTIME(fromSeconds($l['EVENT_TIME'])) . '</span> ';
					print '<a href="calendar/event.php?view=m&amp;start=' . $HGVstart . '&amp;viewset=' . $HGVviewset . '&amp;open=' . $l['EVENT_ID'] . '">' . htmlentities($l['EVENT_TITLE']) . '</a>';
					if($l['YES_DESC'])
						print '<span title="description available">...</span>';
					print ' (' . $l['LAYER_TITLE'] . ')';
					print '</li>';
				}
			}

			if(!$eventstoday)
				print '<li>None</li>';

			print '</ul></li>';

			$nextday = makeidf($cm, $cj + 1, $cy);
		}
		mysql_free_result($result);

		print '</ul>';

		//display my groups!
		$rsmygroups = mysql_query('SELECT LAYER_TITLE, LAYER_ID, LAYERUSER_ID, LAYERUSER_DISPLAY, LAYERUSER_ACCESS
								FROM LAYERUSER_LIST INNER JOIN LAYER_LIST ON LAYERUSER_LAYER = LAYER_ID
								WHERE LAYERUSER_ACCESS > 0 AND LAYERUSER_USER = ' . $userid . ' ORDER BY LAYERUSER_ACCESS DESC, LAYER_TITLE');
		if(mysql_num_rows($rsmygroups) > 0)
			print '<div style="font-weight: bold">My Groups</div><ul class="flat">';
		while($mygroups = mysql_fetch_array($rsmygroups, MYSQL_ASSOC))
		{
		print '<li><a href="./calendar/calendar.php?view=m&amp;start=' . $seldate . '&amp;viewset=' . $mygroups['LAYER_ID'] . '">' . $mygroups['LAYER_TITLE'] . '</a></li>';
		}

		?>
	</ul></div>
	<div class="footer"><a href="calendar/">My Calendar...</a></div>
<? }

function news_block($block)
{
		$tracks = mysql_query("SELECT ASBXTRACK_ID, ASBXTRACK_SHORT, MAX(ASBX_ID) AS LASTPOST
	FROM ASBXTRACK_LIST
	INNER JOIN ASBX_LIST ON ASBX_TRACK=ASBXTRACK_ID
	LEFT JOIN ASBXUSER_LIST ON ASBXUSER_TRACK=ASBXTRACK_ID
	WHERE ASBXTRACK_ID = " . $block . " GROUP BY ASBXTRACK_ID ORDER BY LASTPOST DESC");

	if($ctrack = mysql_fetch_array($tracks, MYSQL_ASSOC))
	{
		print '<h2 class="blue" onMouseOver="startMoveCursor(event);" onMouseDown="startDrag(event);"><a href="news/?id=' . $ctrack['ASBXTRACK_ID'] . '">' . $ctrack['ASBXTRACK_SHORT'] . ' News</a></h2>';
		print '<div class="headed">';

		$rsnews = mysql_query('SELECT ASBX_LIST.*, USER_FULLNAME, USER_ID, ASBXUSER_TITLE FROM ASBX_LIST
			LEFT JOIN ASBXUSER_LIST ON ASBXUSER_USER = ASBX_USER AND ASBXUSER_TRACK = ASBX_TRACK
			INNER JOIN USER_LIST ON USER_ID = ASBX_USER
			WHERE ASBX_ID=' . $ctrack['LASTPOST']);

		if($news = mysql_fetch_array($rsnews, MYSQL_ASSOC))
		{
			$timedate = strtotime($news['ASBX_TS']);
			print '<div class="newsDateline"><span style="font-weight: bold">' . date('j F Y, g:i A', $timedate) . '</span> by <a href="/directory/?id=' . $news['USER_ID'] . '">' . $news['USER_FULLNAME'] . '</a></div>';
			print '<div class="newsHeadline">' . $news['ASBX_SUBJ'] . '</div>';
			print '<div class="newsContent">';
			print '<p>' . ereg_replace("([^\n])\n+([^\n])", '\\1</p><p>\\2', htmlentities(shorten_string($news['ASBX_MSG'], 360))) . '</p>';
			print '</div>';
			print '</div>';
			print '<div class="footer"><a href="news/?id=' . $ctrack['ASBXTRACK_ID'] . '">Read More...</a>';
		}
		else
			print 'This group has no news at this time.';

		print '</div>';
	}
}

function grade_block($grade)
{
	$gradeblockR = mysql_query('SELECT * FROM ASBXTRACK_LIST WHERE ASBXTRACK_GR = ' . $grade );
	if($gradeblock = mysql_fetch_array($gradeblockR, MYSQL_ASSOC))
		news_block($gradeblock['ASBXTRACK_ID']);
}

function move_boxes($boxes, $column)
{
	$column_boxes = $boxes[$column];
	
	foreach( $column_boxes as $current_box )
	{
		$boxesR = mysql_query('SELECT * FROM FRONTBOX_LIST WHERE FRONTBOX_ID=' . $current_box);
		if($box = mysql_fetch_array($boxesR, MYSQL_ASSOC ) )
		{
			print '<div id="' . $box['FRONTBOX_HTMLID'] . '" name="' . $box['FRONTBOX_ID'] . '" class="movebox"><div class="xbox"><a href="#" onClick="return removeMe(event);">x</a></div>';
			$php = '$tmp_array = array( ' . $box['FRONTBOX_PHPARGUMENT'] . ' );';
			eval($php);
			if(is_callable($box['FRONTBOX_FUNCTION']))
			{
				call_user_func_array($box['FRONTBOX_FUNCTION'],$tmp_array);
			}
			print '</div>';
		}
	}
}

function save_boxes($temp_boxes,$userid)
{
	$boxes = array();

	for($i = 0; $i < count($temp_boxes); $i++)
	{
		$boxes[$i] = array();
		foreach($temp_boxes[$i] as $boxid)
			array_push($boxes[$i],$boxid);
	}

	mysql_query("UPDATE USER_LIST SET USER_FRONTPAGE = '" . serialize($boxes) . "' WHERE USER_ID='$userid'");
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
	<title>SaratogaHigh.com</title>
	<link rel="stylesheet" type="text/css" href="shs.css">
	<style type="text/css"><!--
		div.pagecolumn { float: left; margin: 0px 5px 5px 0px }
		dd { margin-left: 25px }
		h2 { background: url('/imgs/bubble2.gif') no-repeat bottom left; padding-left: 30px; margin-bottom: 0px; }
		h2.red     { color: #c33; border-bottom: 3px #c33 solid; }
		h2.red  a  { color: #c33; }
		h2.blue    { color: #039; border-bottom: 3px #039 solid; }
		h2 blue a  { color: #039; }
		h2.gray    { color: #888; border-bottom: 3px #888 solid; }
		h2.gray a  { color: #888; }
		div.headed { background-color: #f0f0f0; border-width: 0 1px 1px 1px; border-style: solid; border-color: #666; padding: 3px; word-wrap: break-word; }
		div.footer { text-align: right; padding: 3px }
		.mainbulletinbox p { margin: 0px; text-align: center; font-size: medium }
		tr.announcebox td p   { margin: 2px; text-align: justify }
		tr.announcebox td div { font-weight: bold; text-align: center; font-size: large; }
		tr.announcebox td     { border: 1px dotted #666666; vertical-align: top }
		#commentBox { margin-left: 0px; margin-right: auto }
		#showCommentBox { text-align: left }
		
		.hidden {border: 0px; opacity: 0; width: 0px; height: 0px;}
		.xbox { float: right; }
		.xbox a { text-decoration: none; }
	--></style>
	<script type="text/javascript" src="/move_boxes.js.php"></script>
</head>
<body onload="calculate_boxes();">

<?
$boxes = unserialize( $userR['USER_FRONTPAGE'] );

$boxes = cleanup_boxes($boxes);
?>

<? print $xml->prepareObject(); ?>

<? if($isparent) { ?>
	<p style="margin: 5px; padding: 5px; font-size: large; font-weight: bold; border: 2px solid #666;" class="informational">You can now <a href="/qa/?group=5">fill out your Back-To-School Forms</a> online!</p>
	<? } ?>

<? if($userid == 4420) { ?>
	<div><a href="/office/">Office Staff Page</a>. Thank you for your support!</div>
<? } ?>

<? if(!$isvalidated) { ?>
	<div style="margin: 3px; border: 1px #cccccc solid; padding: 3px; font-size: medium">
	<p style="margin: 0px; font-weight: bold">You haven't yet verified your account. You can do so now, if you have your Activation Code.</p>
	<form style="margin: 0px" action="./" method="POST">
	<table>
	<? if($isstudent){ ?>
		<tr><td>Student ID</td><td><input size="6" type="text" name="sid" value="0000"></td><td><a href="/help/validation.php">Why?</a></td></tr>
	<? } ?>
	<tr><td>Activation Code</td><td><input size="14" type="text" name="code" value=""></td><td><a href="/help/validation.php">Lost it?</a></td></tr>
	<tr><td></td><td><input type="hidden" name="action" value="Verify"><input type="submit" name="go" value="Verify"></td><td></td></tr>
	</table>
	</form>
	</div>
<? } else if ($justvalidated) { ?>
	<div style="margin: 3px; border: 1px #cccccc solid; padding: 3px; font-size: medium; font-weight: bold">
	<p style="margin: 0px">Thanks! Your account has been verified.</p>
	</div>
<? } ?>

<div id="bigbox1" style="width: 210px" class="pagecolumn bigbox">
		<? print move_boxes($boxes,0); ?>
		<div id="fakebox1" class="movebox hidden"></div>
</div>

<div id="bigbox2" style="width: 415px" class="pagecolumn bigbox">

	<? /*

	if($isstudent || $isparent)
		$trackfilter = 'ASBXTRACK_ID = 6 OR ASBXTRACK_ID = 1 OR ASBXTRACK_GR=' . $userR['USER_GR'];
	else
		$trackfilter = 'ASBXTRACK_ID = 6 OR ASBXTRACK_ID = 1';

	$tracks = mysql_query("SELECT ASBXTRACK_ID, ASBXTRACK_SHORT, MAX(ASBX_ID) AS LASTPOST
	FROM ASBXTRACK_LIST
	INNER JOIN ASBX_LIST ON ASBX_TRACK=ASBXTRACK_ID
	LEFT JOIN ASBXUSER_LIST ON ASBXUSER_TRACK=ASBXTRACK_ID
	WHERE " . $trackfilter . " GROUP BY ASBXTRACK_ID ORDER BY LASTPOST DESC");

	while($ctrack = mysql_fetch_array($tracks, MYSQL_ASSOC))
	{
		print '<div id="' . preg_replace("#\s#","",$ctrack['ASBXTRACK_SHORT']) . 'news" class="itembox movebox"><h2 class="blue" onMouseOver="startMoveCursor(event);" onMouseDown="startDrag(event);"><a href="news/?id=' . $ctrack['ASBXTRACK_ID'] . '">' . $ctrack['ASBXTRACK_SHORT'] . ' News</a></h2>';
		print '<div class="headed">';

		$rsnews = mysql_query('SELECT ASBX_LIST.*, USER_FULLNAME, USER_ID, ASBXUSER_TITLE FROM ASBX_LIST
			LEFT JOIN ASBXUSER_LIST ON ASBXUSER_USER = ASBX_USER AND ASBXUSER_TRACK = ASBX_TRACK
			INNER JOIN USER_LIST ON USER_ID = ASBX_USER
			WHERE ASBX_ID=' . $ctrack['LASTPOST']);

		if($news = mysql_fetch_array($rsnews, MYSQL_ASSOC))
		{
			$timedate = strtotime($news['ASBX_TS']);
			print '<div class="newsDateline"><span style="font-weight: bold">' . date('j F Y, g:i A', $timedate) . '</span> by <a href="/directory/?id=' . $news['USER_ID'] . '">' . $news['USER_FULLNAME'] . '</a></div>';
			print '<div class="newsHeadline">' . $news['ASBX_SUBJ'] . '</div>';
			print '<div class="newsContent">';
			print '<p>' . ereg_replace("([^\n])\n+([^\n])", '\\1</p><p>\\2', htmlentities(shorten_string($news['ASBX_MSG'], 360))) . '</p>';
			print '</div>';
			print '</div>';
			print '<div class="footer"><a href="news/?id=' . $ctrack['ASBXTRACK_ID'] . '">Read More...</a>';
		}
		else
			print 'This group has no news at this time.';

		print '</div></div>';
	}

	?>

	<? if(false) { ?>
	<div id="notices" class="movebox"><h2 class="gray" onMouseOver="startMoveCursor(event);" onMouseDown="startDrag(event);">Notices</h2><div class="headed"><? include "blocks/$insideblock" ?></div></div>
	<? } */ ?>
	
		<? print move_boxes($boxes,1); ?>
<div id="fakebox2" class="movebox hidden"></div>
</div>

<div id="bigbox3" style="width: 210px" class="pagecolumn bigbox">

		<? print move_boxes($boxes,2); /* ?>
<h2 class="gray">Help</h2><div class="headed">
		<form style="margin: 0px" method="POST" action="/comment-confirm.php">
		<p style="margin: 0px">You can submit a question or comment here.<br><input type="hidden" name="go" value="comment">
		<input type="hidden" name="page" value="<?= $REQUEST_URI ?>">
		<textarea name="entrytext" rows="4" style="width: 195px" cols="50"><? if(!$isvalidated) { ?>From:
<? if($loggedin) { print $userR['USER_FULLNAME']; } ?>
<? if($loggedin) { print $userR['USER_EMAIL']; } ?>

Message: <? } ?></textarea><br><input type="submit" name="btn" value="Send"> Or <a href="mailto:staff@saratogahigh.com">email</a> us.</p>
		</form>
	</div>
<? */ ?>
<div id="fakebox3" class="movebox hidden"></div>
</div>

<div style="clear: left"><a href="/personalize.php">Personalize</a></div>

<hr>

<table>
<tr><td style="text-align: right; font-weight: bold">Account</td><td><a href="edit-user.php">Edit Account Info</a>, <a href="edit-pw.php">Change Password</a></td></tr>
<tr><td style="text-align: right; font-weight: bold">About</td><td><a href="stats.php">Site Statistics</a>, <a href="staff.php">Our Staff</a>, <a href="privacy.php">Privacy Policy</a>, <a href="/help/?page=<?
if($userR['USER_GR'] == 1)
	print 'parents';
else if($userR['USER_GR'] == 0)
	print 'teachers';
else
	print 'students';

?>">FAQ</a></td></tr>
</table>

<? include "inc-footer.php" ?>

<div id="infobox">
</div>

</body>
</html>

<? function weather_block($loc_id) {
	// Set Local variables
	if(!$loc_id)
		$location = 95070;
	else
		print '<h2 class="red" onMouseOver="startMoveCursor(event);" onMouseDown="startDrag(event);">Weather for 95070</a></h2><div class="headed">';
	$partner_ID = "1010740996";
	$license_key = "628e4891acd91e41";
	$length = 10; // Forecast length
	$image_size = "32x32"; // 32x32, 64x64, or 128x128 - size of daily weather images

	// First URL for searching, second for detail.
	$search_url = "http://xoap.weather.com/search/search?where=$location";
	$forecast_url = "http://xoap.weather.com/weather/local/$loc_id?cc=*&dayf=$length&prod=xoap&par=$partner_ID&key=$license_key";

	/*
	cc	Current Conditions OPTIONAL VALUE IGNORED
	dayf	Multi-day forecast information for some or all forecast elements OPTIONAL VALUE = [ 1..10 ]
	link	Links for weather.com pages OPTIONAL VALUE = xoap
	par	Application developers Id assigned to you REQUIRED VALUE = {partner id}
	prod	The XML Server product code REQUIRED VALUE = xoap
	key	The license key assigned to you REQUIRED VALUE = {license key}
	unit	Set of units. Standard or Metric OPTIONAL VALUES = [ s | m ] DEFAULT = s
	*/

	if ($location) // Determine URL to use. If location is passed, we're searching for a city or zip. Elese we're retrieving a forecast.
	{
		$url = $search_url;
	}
	else
	{
		$url = $forecast_url;
	}

	if ($location Or $loc_id) // If city, zip, or weather.com city id passed, do XML query. $loc_id is a weather.com city code, $location is user entered city or zip
	{
		/* 
		query db for md5 of url
		if doesn't exist, insert into db
		if exists, check date, if under X hours use db content
		if older then X hours, pull from weather.com and update db
		to delete old data: when querying, delete all records older than X hours
		*/
		
		$datetime = date("Y-m-d h:i:s");
		$xml_url = md5($url);
		$interval = 12;	// Hours to keep data in db before being considered old
		$expires = $interval*60*60;
		$expiredatetime = date("Y-m-d H:i:s", time() - $expires);
	
		// Delete expired records
		$query = "DELETE FROM weather_xml WHERE last_updated < '$expiredatetime'";
		$result = mysql_query($query) or die('Invalid query: ' . mysql_error());
	
		$query = "SELECT * FROM weather_xml WHERE xml_url = '$xml_url'"; 
		$result = mysql_query($query) or die('Invalid query: ' . mysql_error());
		$row = mysql_fetch_array($result);
		$time_diff = strtotime($datetime) - strtotime($row['last_updated']);
	
		if (mysql_num_rows($result) < 1) // Data not in table - Add.
		{
			
			// Get XML Query Results from Weather.com
			$fp = fopen($url,"r");
			while (!feof ($fp))
				$xml .= fgets($fp, 4096);
			fclose ($fp);
	
			// Fire up the built-in XML parser
			$parser = xml_parser_create(  ); 
			xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	
			// Set tag names and values
			xml_parse_into_struct($parser,$xml,$values,$index); 
	
			// Close down XML parser
			xml_parser_free($parser);
	
			$xml = str_replace("'","",$xml); // Added to handle cities with apostrophies in the name like T'Bilisi, Georgia
	
			if ($loc_id) // Only inserts forecast feed, not search results feed, into db
			{
				$query = "INSERT INTO weather_xml VALUES ('$xml_url', '$xml', '$datetime')";
				$result = mysql_query($query) or die('Invalid query: ' . mysql_error());
			}
	
		}
		else // Data in table, and it is within expiration period - do not load from weather.com and use cached copy instead.
		{
			$xml = $row['xml_data'];
	
			// Fire up the built-in XML parser
			$parser = xml_parser_create(  ); 
			xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	
			// Set tag names and values
			xml_parse_into_struct($parser,$xml,$values,$index); 
	
			// Close down XML parser
			xml_parser_free($parser);
		}
	}

	if ($loc_id) // Location code selected - Display detail info. A specific city has been selected from the drop down menu. Get forecast.
	{
		$city = htmlspecialchars($values[$index[dnam][0]][value]);
		$unit_temp = $values[$index[ut][0]][value];
		$unit_speed = $values[$index[us][0]][value];
		$unit_precip = $values[$index[up][0]][value];
		$unit_pressure = $values[$index[ur][0]][value];
		$sunrise = $values[$index[sunr][0]][value];
		$sunset = $values[$index[suns][0]][value];
		$timezone = $values[$index[tzone][0]][value];
		$last_update = $values[$index[lsup][0]][value];
		$curr_temp = $values[$index[tmp][0]][value];
		$curr_flik = $values[$index[flik][0]][value];
		$curr_text = $values[$index[t][0]][value];
		$curr_icon = $values[$index[icon][0]][value];
		$counter = 0;
		$row_counter = 2;
	
		echo "<font size=1>(Last updated $last_update).</font><br />\n";
		echo "<p><table><tr><td><img border=\"1\" src=\"http://www.notonebit.com/images/weather/64x64/$curr_icon.png\" alt=\"$curr_text\"></td><td>";
		echo "<font size=3>Currently: <b>$curr_temp&#730; $unit_temp</b></font><br />\n";
		echo "Feels Like: $curr_flik&#730; $unit_temp<br />Current conditions: $curr_text<br />\n";
		echo "Sunrise: $sunrise.<br />Sunset: $sunset.<br />\n";
		echo "</td></tr></table></p>";
		echo "</div>";
	}

	if ($location And is_array($index[loc])) // A city name has been entered and data returned from weather.com, draw drop down menu of matches
	{
		if (count($index[loc]) == 1) // If just one match returned, send to detail screen - no need to draw option box for one option.
		{
			$location_code = $values[$index[loc][0]][attributes][id];
			weather_block($location_code);
		}
	}
}
