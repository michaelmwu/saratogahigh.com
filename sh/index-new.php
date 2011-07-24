<?
include 'db.php';

include 'calendar/cal.php';

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");              // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified
header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                                    // HTTP/1.0

$seldate = makecuridf(-9);
$seldayno = idfd($seldate);
$selmonthno = idfm($seldate);
$selyearno = idfYY($seldate);
$firstday = $seldate;
$lasttestday = makeidf($selmonthno, $seldayno + 21, $selyearno);
$nextday = $firstday;

$block = 'bos.php';
$insideblock = 'temp.php';

if(!$isvalidated && $loggedin && $_POST['action'] == 'Verify' && is_numeric($_POST['code']))
{
	mysql_query("UPDATE USER_LIST SET
		USER_ACTIVATION=Null,
		USER_VALIDATED=1
	WHERE
		USER_ID='" . $userid . "' AND
		(USER_SID Is Null OR USER_SID='" . $_POST['sid'] . "') AND
		USER_ACTIVATION=" . $_POST['code']) or die("There was an unexpected error, and verification failed. Sorry! Please contact an administrator.");

	$tu = mysql_query('SELECT * FROM USER_LIST WHERE USER_ID=' . $userid) or die('User query failed');
	if($userR = mysql_fetch_array($tu, MYSQL_ASSOC))
		$isvalidated = ($userR['USER_VALIDATED'] == 1);
	mysql_free_result($tu);

	if($isvalidated)
	{
		$justvalidated = true;
		header('location: directory/');
	}
}

function LatestNews($trackid)
{
	$rsnews = mysql_query('SELECT * FROM ASBX_LIST WHERE ASBX_TRACK=' . $trackid . ' ORDER BY ASBX_TS DESC LIMIT 1');
	
	if($news = mysql_fetch_array($rsnews, MYSQL_ASSOC))
	{
		return '<p>' . ereg_replace("[^\n](\n)+[^\n]", '</p><p>', $news['ASBX_MSG']) . '</p>';
	}
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
	<title>SaratogaHigh.com</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<meta name="vs_targetSchema" content="http://schemas.microsoft.com/intellisense/ie5">
	<link rel="stylesheet" type="text/css" href="shs.css">
	<style type="text/css"><!--
		dt.linkhead { font-weight: bold; margin: 0px }
		dd { margin-left: 25px }
		h2 { background: url('/imgs/bubble2.gif') no-repeat bottom left; padding-left: 30px; margin-bottom: 0px; }
		
		h2.red,  h2.red a  { color: #cc3333; }
		h2.blue, h2 blue a { color: #003399; }
		h2.gray, h2.gray a { color: #999999; }

		div.headed  { background-color: #eeeeee; padding: 3px; }
		div.red     { border-top: 3px #cc3333 solid; }
		div.blue    { border-top: 3px #003399 solid; }
		div.gray    { border-top: 3px #999999 solid; }
		div.footer { text-align: right; background-color: #cccccc; padding: 3px }
<? if(false) { ?>
		h2 + div      { background-color: #eeeeee; padding: 3px; }
		h2.red + div  { border-top: 3px #cc3333 solid; }
		h2.blue + div { border-top: 3px #003399 solid; }
		h2.gray + div { border-top: 3px #999999 solid; }
		h2 + div + div { text-align: right; background-color: #cccccc; padding: 3px }
<? } ?>
		.mainbulletinbox p { margin: 0px; text-align: center; font-size: medium }
		.bulletinbox p { margin: 2px; text-align: justify }
		tr.fpbox td div { font-weight: bold; text-align: center; font-size: large; }
		tr.fpbox td { border: 1px dotted #666666; vertical-align: top }
		#maint td { vertical-align: top}
		
		h1.frontpage { letter-spacing: 2pt; font-size: medium; font-weight: bold; border-bottom: 1px solid black; margin-top: 1ex }
	--></style>
</head>
<body onload="document.sf.elements[0].focus();">

<? if($loggedin) { ?>

<h1 style="margin: 0px"><img src="/imgs/core/logo.gif" alt="saratogahigh.com"></h1>

<form style="margin: 0 0 0 3px" name="sf" method="POST" action="/directory/search-student.php"><input type="text" name="q" value="" style="width: 145px"> <input type="image" style="vertical-align: middle" src="/imgs/fp.gif" name="b" value="Search"><input type="hidden" name="a" value="qsearch"> <a style="margin-left: 15px" href="/help/"><img style="width: 58px; height: 22px; border: 0px; vertical-align: middle" src="/imgs/nav/help.png" alt="Help"></a><? if($isstaff) { ?> <a href="/shcp/"><img style="width: 58px; height: 22px; border: 0px; vertical-align: middle" src="/imgs/nav/admin.png" alt="Admin"></a><? } ?></form>

<? include "inc-navbar.php" ?>

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

<table style="table-layout: fixed" id="maint" cellpadding="0" cellspacing="5">
<tr><td style="width: 210px">

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
		<h2 class="red">Test Conflicts</h2><div class="headed red">
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
		<h2 class="red">Tests and Projects</h2><div class="headed red">
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
		ORDER BY EVENT_DATE, SCHED_PER, SCHED_TERM') or die('Calendar query failed');

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
		
		$pushdate = false;
		if(20031222 <= $seldate && $seldate <= 20040102)
		{
			$seldate = 20040105;
			$pushdate = true;
		}
		if($seldate == 20040119)
		{
			$seldate == 20040120;
			$pushdate = true;
		}
		if($seldate == 20040123)
		{
			$seldate == 20040126;
			$pushdate = true;
		}
		if(20040216 <= $seldate && $seldate <= 20040220)
		{
			$seldate = 20040223;
			$pushdate = true;
		}
		if(20040412 <= $seldate && $seldate <= 20040416)
		{
			$seldate = 20040419;
			$pushdate = true;
		}
		
		$seldayno = idfd($seldate);
		$selmonthno = idfm($seldate);
		$selyearno = idfYY($seldate);
		?>
		<h2 class="red">Due <?
		if($pushdate)
			print idfDD($seldate) . ' ' . idfj($seldate) . ' ' . idfMM($seldate);
		else
			print idfl($seldate); ?></h2><div class="headed red">
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
			
		$pushdate = false;
		if(20031222 <= $seldate && $seldate <= 20040102)
		{
			$seldate = 20040105;
			$pushdate = true;
		}
		if($seldate == 20040119)
		{
			$seldate == 20040120;
			$pushdate = true;
		}
		if($seldate == 20040123)
		{
			$seldate == 20040126;
			$pushdate = true;
		}
		if(20040216 <= $seldate && $seldate <= 20040220)
		{
			$seldate = 20040223;
			$pushdate = true;
		}
		if(20040412 <= $seldate && $seldate <= 20040416)
		{
			$seldate = 20040419;
			$pushdate = true;
		}

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
				print idfl($seldate); ?></h2><div class="headed red">
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
	
	<? if($isvalidated) { ?>
		<h2 class="red"><a href="/mail/">My Mail</a></h2><div class="headed red">
		<p style="margin-bottom: 6px; margin-top: 0px;">
		<form style="margin: 0px" action="mail/compose.php" name="mf" method="POST">
		<p style="margin: 0px">To: (use FirstName LastName)</p>
		<p style="margin: 0px"><input style="width: 195px" type="text" name="xto" value="" size="55"></p>
		<p style="margin: 0px">Subject:<br><input style="width: 195px" type="text" name="xsubj" value="" size="40"></p>
		<p style="margin: 0px"><textarea name="xmsgtxt" rows="3" style="width: 195px" cols="55"></textarea></p>
		<p style="margin: 0px; font-weight: bold; text-align: right"><input type="submit" name="go" value="Send"></p>
		</form>
		</div>
		<div class="footer"><a href="mail/">More Mail...</a></div>
	<? } ?>
		
	<h2 class="gray"><a href="/notepad/">Notes</a></h2><div class="headed gray">
		<p style="margin: 0px"><a href="notepad/"><?
		$cr = mysql_query('SELECT COUNT(*) FROM NOTEPAGE_LIST WHERE NOTEPAGE_OWNER=' . $userid) or die("Query failed");
		$l = mysql_fetch_array($cr, MYSQL_ASSOC);
		print $l['COUNT(*)'];
		?> note(s) saved</a>.</p>
		<?	
		$entries = mysql_query('SELECT NOTEPAGE_ID, NOTEPAGE_VALUE, NOTEPAGE_MODIFIED as TS FROM NOTEPAGE_LIST WHERE NOTEPAGE_OWNER=' . $userid . ' ORDER BY NOTEPAGE_MODIFIED DESC LIMIT 3') or die('Query failed.');
		if(mysql_num_rows($entries) > 0)
		{
			print '<p style="margin: 0px; font-weight: bold">Most Recent:</p>';
			while($l = mysql_fetch_array($entries, MYSQL_ASSOC))
				print '<p style="margin: 0px;"><a style="font-weight: bold" href="notepad/page.php?id=' . $l['NOTEPAGE_ID'] . '">' . date('n/j', strtotime($l['TS'])) . '</a> ' .  htmlspecialchars(shorten_string(nl2slash($l['NOTEPAGE_VALUE']), 40)) . '</p>';
		}
		?>
	</div>

</td><td style="width: 210px">

	<h2 class="red"><a href="/calendar/">My Calendar</a></h2><div class="headed red">
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
			ORDER BY EVENT_DATE, EVENT_TIME, EVENT_ID') or die('Calendar query failed');

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

		?>
		</ul>
	</div>
	<div class="footer"><a href="calendar/">My Calendar...</a></div>

	<h2 class="gray">Help</h2><div class="headed gray">
		<form style="margin: 0px" method="POST" action="/comment-confirm.php">
		<p style="margin: 0px">You can submit a question or comment here.<br><input type="hidden" name="go" value="comment">
		<input type="hidden" name="page" value="<?= $REQUEST_URI ?>">
		<textarea name="entrytext" rows="4" style="width: 195px" cols="50"><? if(!$isvalidated) { ?>From:
<? if($loggedin) { print $userR['USER_FULLNAME']; } ?>
<? if($loggedin) { print $userR['USER_EMAIL']; } ?>

Message: <? } ?></textarea><br><input type="submit" name="btn" value="Send"> Or <a href="mailto:staff@saratogahigh.com">email</a> us.</p>
		</form>
	</div>

</td><td style="width: 315px">

	<?

	if($isstudent)
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
		print '<h2 class="blue"><a href="news/?id=' . $ctrack['ASBXTRACK_ID'] . '">' . $ctrack['ASBXTRACK_SHORT'] . ' News</a></h2>';
		print '<div class="headed blue">';

		$rsnews = mysql_query('SELECT ASBX_LIST.*, USER_FULLNAME, USER_ID, ASBXUSER_TITLE FROM ASBX_LIST
			LEFT JOIN ASBXUSER_LIST ON ASBXUSER_USER = ASBX_USER AND ASBXUSER_TRACK = ASBX_TRACK
			INNER JOIN USER_LIST ON USER_ID = ASBX_USER
			WHERE ASBX_ID=' . $ctrack['LASTPOST']);
			
		if($news = mysql_fetch_array($rsnews, MYSQL_ASSOC))
		{
			$timedate = strtotime($news['ASBX_TS']);
			print '<div><span style="font-weight: bold">' . date('j F Y, g:i A', $timedate) . '</span> by <a href="/directory/?id=' . $news['USER_ID'] . '">' . $news['USER_FULLNAME'] . '</a></div>';
			print '<div style="font-weight: bold; font-size: medium">' . $news['ASBX_SUBJ'] . '</div>';
			print '<p style="margin: 0px; text-indent: 1em">' . ereg_replace("\n+", '</p><p style="margin: 0px; text-indent: 1em">', shorten_string($news['ASBX_MSG'], 360)) . '</p>';
			print '</div>';
			print '<div class="footer"><a href="news/?id=' . $ctrack['ASBXTRACK_ID'] . '">Read More...</a>';
		}
		else
			print 'This group has no news at this time.';
		
		print '</div>';
	}

	?>

	<? if(false) { ?>
	<h2 class="gray">Notices</h2><div class="headed gray"><? include "blocks/$insideblock" ?></div>
	<? } ?>
</td></tr></table>
<hr>
<table>
<tr><td style="text-align: right; font-weight: bold">More Services</td><td><a href="news/">News</a>, <a href="map/">Map</a>, <a href="notepad/">Notepad</a>, <a href="qa/">Q&amp;A Service</a></td></tr>
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

<? } else { ?>

	<div style="position: absolute; width: 190px; padding: 5px">
	<img style="border: 0px;" alt="[bubble logo]" src="imgs/large-bubble.gif">
	
	<h1 class="frontpage">Features</h1>
	<p style="text-align: center"><a href="calendar/"><img src="/imgs/frontmini-calendar.jpg" alt=""></a><br><a href="calendar/">Calendar</a></p>
	<p style="text-align: center"><a href="map/"><img src="/imgs/frontmini-map.gif" alt=""></a><br><a href="map/">Map</a></p>
	<ul style="font-size: medium">
	<li><a href="/tour/">Tour SaratogaHigh.com</a></li>
	<li><a href="/help/">Frequently Asked Questions</a></li>
	</ul>
	</div>

	<div style="position: absolute; left: 200px; padding: 5px; width: auto"><img style="border: 0px;" alt="[bubble logo]" src="imgs/sh-text.gif">
	<h1 class="frontpage">Log In</h1>
    <? if(SITE_ENABLED) { ?>
    <form name="sf" action="login.php" method="POST" style="margin: 0px">
    <div style="font-size: medium">Username <input type="text" name="un" value=""> Password <input type="password" name="pw"> <input type="hidden" name="job" value="login"> <input type="submit" name="btn" value="Login"></div>
	<p style="margin: 5px">First time users: are you holding an <span style="font-weight: bold">Activation Code</span>? <a href="new-user.php" style="font-weight: bold">Create a new account</a> before you log in.</p>
  <!--	<p style="margin: 5px"><span style="font-weight: bold; color: #800000">Help! You can contact saratogahigh.com staff</span> if you have feedback, questions, or problems logging in. Just fill out the "Questions or Comments?" box at the bottom of any page.</p>-->
    <table cellpadding="2" cellspacing="2"><tr><td class="yellowbox"><? include "blocks/$block" ?></td></tr></table>
    </form>
    <? } else { ?>
    <div style="font-size: medium; color: #009">SaratogaHigh.com has been disabled temporarily for site improvements and maintenance. We apologize for the inconvenience.</div>
    <? } ?>
		
	<h1 class="frontpage">Messages</h1>
	<div style="font-size: medium"><?= LatestNews(11) ?></div>
	<table cellpadding="2" cellspacing="2" style="width: 600px; table-layout: fixed">
	<tr class="fpbox">
	<td class="bulletinbox">
		<div style="border-bottom: 5px #003399 solid">Students</div>
		<p style="font-weight: bold; text-align: center"><a href="/help/?page=students">FAQs for Students</a></p>
		<?= LatestNews(7) ?>
	</td>
	<td class="bulletinbox">
		<div style="border-bottom: 5px #009900 solid">Alumni</div>
		<?= LatestNews(8) ?>
	</td>
	<td class="bulletinbox">
		<div style="border-bottom: 5px #cc3333 solid">Teachers</div>
		<p style="font-weight: bold; text-align: center"><a href="/help/?page=teachers">FAQs for Teachers</a></p>
		<?= LatestNews(9) ?>
	</td>
	<td class="bulletinbox">
		<div style="border-bottom: 5px #808080 solid">Parents</div>
		<p style="font-weight: bold; text-align: center"><a href="/help/?page=parents">FAQs for Parents</a></p>
		<?= LatestNews(10) ?>
	</td>
	</tr>
	</table>
	
	<? include "inc-footer.php" ?>
</div>

<? } ?>

</body>
</html>
