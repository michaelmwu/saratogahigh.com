<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
	<title><?= $page->get_config('title') ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<meta name="vs_targetSchema" content="http://schemas.microsoft.com/intellisense/ie5">
	<? $css_array = $page->get_config('css');
	if(!is_array($css_array))
		$css_array = array( $css_array );
	foreach($css_array as $css)
	{
		if(strlen($css) > 0)
			print '<link rel="stylesheet" type="text/css" href="' . $css . '.css">' . "\n";
	}
	?>
	<style type="text/css">
	<!--
	<?=$page->get_config('extracss');?>
	-->
	</style>
	<script type="text/javascript" src="/tophp.js"></script>
	<script type="text/javascript" src="/dom.js"></script>
	<? $js_array = $page->get_config('javascript');
	if(!is_array($js_array))
		$js_array = array( $js_array );
	foreach($js_array as $js)
	{
		if(strlen($js) > 0)
			print '<script type="text/javascript" src="' . $js . '.js"></script>' . "\n";
	}
	?>
	<script type="text/javascript">
	<!--
	<?=$page->get_config('extrajavascript');?>
	// -->
	</script>
</head>
<body>

<div style="position: relative; width: 700px;"><form style="margin: 0px" method="POST" action="/directory/search-student.php"><div><a href="/"><img style="vertical-align: middle; border: 0" src="/imgs/logo.gif" alt="SaratogaHigh.com"></a> <input type="hidden" name="a" value="qsearch"><input type="text" name="q" value="" style="width: 145px; vertical-align: middle"> <input type="image" style="vertical-align: middle" src="/imgs/fp.gif" name="b" value="Search"><? if($isstaff) { ?> <a href="/shcp/newtask.php?ref=<?= urlencode($_SERVER['REQUEST_URI']) ?>"><img src="/shcp/addtask.gif" alt="taskify" style="border: 0px; vertical-align: middle"></a><? } ?></div></form></div>

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
	$msgs = mysql_query('SELECT COUNT(*) FROM MAILREC_LIST WHERE MAILREC_SENDTYPE!="from" AND MAILREC_READ=0 AND MAILREC_DELETED=0 AND MAILREC_RECIPIENT=' . $userid) or die('Checking for new messages failed.');
	$lmailp = mysql_fetch_array($msgs);
	if($lmailp['COUNT(*)'] > 0)
	{
	?><li<? if($seltab == 'mail') {print ' class="current"'; } else {print ' class="inverted"';} ?>><a href="/mail/"><?= $lmailp['COUNT(*)'] ?> Message<? if($lmailp['COUNT(*)'] > 1) { print 's'; } ?></a></li><?
	}
	else
	{
	?><li<? if($seltab == 'mail') {print ' class="current"'; } else {print ' id="mail"';} ?>><a href="/mail/">Mail</a></li><?
	}
	mysql_free_result($msgs);
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

<?
$xml = new XMLHTTPRequest();
print $xml->prepareObject(); ?>

<div class="navbar"><span style="font-weight: bold">&nbsp;</span></div>