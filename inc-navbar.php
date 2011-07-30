<?

if(eregi('^/([a-z]+)/', $_SERVER['SCRIPT_NAME'], $matches))
{
	$subdirectory = $matches[1];

	if($subdirectory == 'mail')
		$seltab = 'mail';
	else if($subdirectory== 'directory')
		$seltab = 'directory';
	else if($subdirectory == 'calendar')
		$seltab = 'calendar';
	else if($subdirectory == 'cm')
		$seltab = 'cm';
	else if($subdirectory == 'qa')
		$seltab = 'qa';
	else if($subdirectory == 'classifieds')
		$seltab = 'classifieds';
	else if($subdirectory == 'news')
		$seltab = 'news';
	else if($subdirectory == 'map')
		$seltab = 'map';
	else if($subdirectory == 'notepad')
		$seltab = 'notepad';
	else if($subdirectory == 'help')
		$seltab = 'help';
	else if($subdirectory == 'shcp')
		$seltab = 'admin';
	else if($subdirectory == 'ptsa')
		$seltab = 'ptsa';
	else
		$seltab = 'home';
}
else
	$seltab = 'home';

?>
<div id="dnavbar">
<ul>
<li id="home"<? if($seltab == 'home') {print ' class="current"'; } ?>><a href="/">Home</a></li>
<?
if($loggedin)
{
/*	$msgs = mysql_query('SELECT COUNT(*) FROM MAILREC_LIST WHERE MAILREC_SENDTYPE!="from" AND MAILREC_READ=0 AND MAILREC_DELETED=0 AND MAILREC_RECIPIENT=' . $userid) or die('Checking for new messages failed.');
	$lmailp = mysql_fetch_array($msgs);
	if($lmailp['COUNT(*)'] > 0)
	{
	?><li<? if($seltab == 'mail') {print ' class="current"'; } else {print ' class="inverted"';} ?>><a href="/mail/"><?= $lmailp['COUNT(*)'] ?> Message<? if($lmailp['COUNT(*)'] > 1) { print 's'; } ?></a></li><?
	}
	else
	{
	?><li<? if($seltab == 'mail') {print ' class="current"'; } else {print ' id="mail"';} ?>><a href="/mail/">Mail</a></li><?
	}
	mysql_free_result($msgs); */
}
else
{
	?><li<? if($seltab == 'mail') {print ' class="current"'; } else {print ' id="mail"';} ?>><a href="/mail/">Mail</a></li><?
}
?>
<li<? if($seltab == 'directory') {print ' class="current"'; } ?>><a href="/directory/">Directory</a></li>
<li<? if($seltab == 'cm') {print ' class="current"'; } ?>><a href="/cm/">Courses</a></li>
<li<? if($seltab == 'calendar') {print ' class="current"'; } ?>><a href="/calendar/">Calendar</a></li>
<li<? if($seltab == 'news') {print ' class="current"'; } ?>><a href="/news/">News</a></li>
<li<? if($seltab == 'map') {print ' class="current"'; } ?>><a href="/map/">Map</a></li>
<li<? if($seltab == 'notepad') {print ' class="current"'; } ?>><a href="/notepad/">Notepad</a></li>
<li<? if($seltab == 'classifieds') {print ' class="current"'; } ?>><a href="/classifieds/">Classifieds</a></li>
<? if(true || $seltab == 'qa') { ?>
<li<? if($seltab == 'qa') {print ' class="current"'; } ?>><a href="/qa/">Q&amp;A Service</a></li>
<? } ?>
<? if(false) { ?>
<li id="ptsa"<? if($seltab == 'ptsa') {print ' class="current"'; } ?>><a href="/ptsa/">PTSA</a></li>
<? } ?>

<? if($loggedin) { ?>
<li class="inverted"><a href="/logout.php">Logout</a></li>
<? } else if($_SERVER['SCRIPT_NAME'] != '/login.php') { ?>
<li class="inverted"><a href="/login.php?next=<?= urlencode($REQUEST_URI) ?>">Login...</a></li>
<? } ?>
<li<? if($seltab == 'help') {print ' class="current"'; } ?>><a href="/help/">Help</a></li>
<? if($isstaff) { ?>
<li<? if($seltab == 'admin') {print ' class="current"'; } ?>><a href="/shcp/">Admin</a></li>
<? } ?>
</ul>
</div>
<div style="clear: left; padding: 3px"><?
if($loggedin)
{ ?>Logged in as <span style="FONT-WEIGHT: bold"><?= $userR['USER_UNAME'] ?></span> (<?= GradePrint($userR['USER_GR']) ?>)<? if(!$isvalidated) print ' — Unverified User (<a href="/help/validation.php">more info</a>)'; ?><?
} else {
?>Not logged in<? } ?> | <span style="color: #666"><?= date(TIME_FORMAT, CURRENT_TIME) ?></span></div>
<? print $xml->prepareObject(); ?>