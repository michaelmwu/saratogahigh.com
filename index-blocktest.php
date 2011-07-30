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

include 'blocktest.php';

$xml->handle_request();

if(!$isvalidated && $loggedin && $_POST['action'] == 'Verify' && is_numeric($_POST['code']))
{
	mysql_query("UPDATE USER_LIST SET
		USER_ACTIVATION=Null,
		USER_VALIDATED=1
	WHERE
		USER_ID='" . $userid . "' AND
		(USER_SID Is Null OR USER_SID='" . $_POST['sid'] . "') AND
		USER_ACTIVATION='" . $_POST['code'] . "'") or die("There was an unexpected error, and verification failed. Sorry! Please contact an administrator.");

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
		return '<p>' . ereg_replace("[^\n](\n)+[^\n]", '</p><p>', $news['ASBX_MSG']) . '</p>';
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
	<title>SaratogaHigh.com</title>
	<link rel="stylesheet" type="text/css" href="shs.css">
	<script type="text/javascript" src="tophp.js"></script>
	<script type="text/javascript" src="dom.js"></script>
	<script type="text/javascript" src="move_boxes_blocktest.js.php"></script>
	<style type="text/css">
		div.headed { background-color: #f0f0f0; border-width: 0 1px 1px 1px; border-style: solid; border-color: #666; padding: 3px; word-wrap: break-word; }
	</style>
</head>
<body onLoad="document.sf.elements[0].focus(); calculate_boxes();">


<!-- <?= $_SERVER['HTTP_HOST'] ?> -->

<? if($loggedin) {

$boxes = unserialize( $userR['USER_FRONTPAGE'] );

$boxes = cleanup_boxes_test($boxes);
?>

<h1 style="margin: 0px"><img src="/imgs/core/logo.gif" alt="saratogahigh.com"></h1>

<form style="margin: 0px 0px 0px 3px" name="sf" method="POST" action="/directory/search-student.php"><p style="margin: 0"><input type="text" name="q" value="" style="width: 145px"> <input type="image" style="vertical-align: middle" src="/imgs/fp.gif" name="b" value="Search"><input type="hidden" name="a" value="qsearch"></p></form>

<? include "inc-navbar.php" ?>

<? if($isparent) { ?>
	<p style="margin: 5px; padding: 5px; font-size: large; font-weight: bold; border: 2px solid #666;" class="informational">You can now <a href="/qa/?group=6">fill out your Back-To-School Forms</a> online!</p>
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

<div style="width: 1100px;">

<? if($isstudent || $isteacher) { ?>
<div style="clear: left">Saratoga High is on a modified block schedule right now. Get the new <a href="http://www.saratogahigh.org/shs/home/BELLSCHEDULE.pdf">Bell Schedule</a>! (Requires Adobe Acrobat Reader)</div>
<? } ?>

<div id="bigbox1" style="width: 220px" class="pagecolumn bigbox">
		<? print move_boxes($boxes,0); ?>
		<div id="fakebox1" class="movebox hidden"></div>
</div>

<div id="bigbox2" style="width: 415px" class="pagecolumn bigbox">

		<? print move_boxes($boxes,1); ?>
<div id="fakebox2" class="movebox hidden"></div>
</div>

<div id="bigbox3" style="width: 220px" class="pagecolumn bigbox">

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

<div id="bigbox4" style="width: 220px" class="pagecolumn bigbox">

		<? print move_boxes($boxes,3); ?>
<div id="fakebox4" class="movebox hidden"></div>
</div>

<div style="clear: left">If you have a Javascript Enabled browser, you can now personalize your Front Page! Try dragging around or deleting boxes, or even adding boxes. If you do not have Javascript, you can only use the <a href="personalize.php">Personalize</a> page.</div>
</div>

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

<? } else { ?>

	<table style="width: 750px; margin-left: auto; margin-right: auto;">
	<tr>
	<td style="text-align: center; width: 50%">
		<img style="border: 0px;" alt="SaratogaHigh.com Launch Logo" src="./launchlogo.gif">
	</td>
	<td valign="middle" style="width: 50%">
		<div style="text-align: center">
		<? if(SITE_ENABLED && FRONT_ENABLED) { ?>
		<form name="sf" action="login.php" method="POST" style="margin: 0px">
		<div style="font-size: medium; letter-spacing: 1pt; border-bottom:1px dotted black">Log In</div>
		<table style="margin-left: auto; margin-right: auto; margin-bottom: 5px" width="75%">
			<tr><td>Username</td><td style="text-align: left"><input type="text" name="un" value="" size="14"></td></tr>
			<tr><td>Password</td><td style="text-align: left"><input type="password" name="pw" size="14"> <input type="hidden" name="job" value="login"><input type="submit" name="btn" value="Login"></td></tr>
			<tr><td colspan="2"><a href="login.php?forgot=1">Forgot your username or password?</a></td></tr>
		</table>
		</form>
		<div style="margin: 0px; padding: 3px; background-color: #dddddd">New users: did you get an <span style="font-weight: bold">Activation Code</span> in the mail? Use it to <a href="new-user.php" style="font-weight: bold">create a new account</a>.</div>
		<? } else { ?>
		<div style="font-size: medium; color: #009; text-align: justify; padding: 1em"><!--SaratogaHigh.com has been disabled temporarily for site improvements and maintenance. We apologize for the inconvenience.-->Login has temporarily been disabled. We're working around the clock to fix and add new site features for the 2005-2006 school year-- look for our mailings in August!</div>
		<? } ?>
		<table cellpadding="1" cellspacing="1" width="100%" style="border-style: solid; border-color: #999; border-width: 1px 0px; table-layout: fixed; font-size: medium; text-align: center"><tr><td><a href="/tour/">Tour</a></td><td><a href="/help/">FAQ</a></td><td style="font-weight: bold;"><a href="calendar/">Calendar</a></td><td style="font-weight: bold;"><a href="map/">Map</a></td></tr></table>
		</div>
		<!--<p style="margin: 5px"><span style="font-weight: bold; color: #800000">Help! You can contact saratogahigh.com staff</span> if you have feedback, questions, or problems logging in. Just fill out the "Questions or Comments?" box at the bottom of any page.</p>-->
		<table width="100%" cellpadding="2" cellspacing="2" style="margin-top:10px"><tr><td class="yellowbox"><strong>Don't have a SaratogaHigh.com account</strong>, either because you're a freshman or haven't received an activation code? Leave us a comment! Give us some time though, school has started, and we are very busy with homework.</td></tr></table>
	</td>
	</tr>
	</table>

	<table cellpadding="2" cellspacing="5" style="width: 750px; table-layout: fixed; margin-left: auto; margin-right: auto;">
	<tr>
	<td colspan="4" class="mainbulletinbox"><?= LatestNews(11) ?></td>
	</tr>
	<tr class="announcebox">
	<td><div style="border-bottom: 5px #003399 solid">Students</div><p style="font-weight: bold; text-align: center"><a href="/help/?page=students">FAQs for Students</a></p>		<?= LatestNews(7) ?></td>
	<td><div style="border-bottom: 5px #009900 solid">Alumni</div><?= LatestNews(8) ?></td>
	<td><div style="border-bottom: 5px #cc3333 solid">Teachers</div><p style="font-weight: bold; text-align: center"><a href="/help/?page=teachers">FAQs for Teachers</a></p>		<?= LatestNews(9) ?></td>
	<td><div style="border-bottom: 5px #808080 solid">Parents</div><p style="font-weight: bold; text-align: center"><a href="/help/?page=parents">FAQs for Parents</a></p>		<?= LatestNews(10) ?></td>
	</tr>
	</table>

	<p style="text-align: center; color: #999">Saratoga High School | 20300 Herriman Ave, Saratoga, CA 95070<br>
	<strong>The official Saratoga High website is located at <a href="http://www.saratogahigh.org/" style="color: #999">www.saratogahigh.org</a>.</strong></p>
<? } ?>

<? include "inc-footer.php" ?>

<div id="infobox">
</div>

</body>
</html>