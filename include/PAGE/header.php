<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
<title><?=$this->titlename?> <?=$this->titlechar?> <?=$this->title?></title>
<link rel="stylesheet" type="text/css" href="<?=$this->css?>.css">
<meta http-equiv="Set-Cookie" content="cookie=set; path=/">
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<?=$extraheadstring?>
</head>
<body<?=$extrabodystring?>>
<script type="text/javascript">
<!--
	points1 = new Image();
	points1.src = "/images/points1.gif";
	points2 = new Image();
	points2.src = "/images/points2.gif";

	email1 = new Image();
	email1.src = "/images/email1.gif";
	email2 = new Image();
	email2.src = "/images/email2.gif";

	login1 = new Image();
	login1.src = "/images/login1.gif";
	login2 = new Image();
	login2.src = "/images/login2.gif";

	logoff1 = new Image();
	logoff1.src = "/images/logoff1.gif";
	logoff2 = new Image();
	logoff2.src = "/images/logoff2.gif";
	
	control1 = new Image();
	control1.src = "/images/control1.gif";
	control2 = new Image();
	control2.src = "/images/control2.gif";

	home1 = new Image();
	home1.src = "/images/home1.gif";
	home2 = new Image();
	home2.src = "/images/home2.gif";
	
	news1 = new Image();
	news1.src = "/images/news1.gif";
	news2 = new Image();
	news2.src = "/images/news2.gif";
	
	events1 = new Image();
	events1.src = "/images/events1.gif";
	events2 = new Image();
	events2.src = "/images/events2.gif";
	
	members1 = new Image();
	members1.src = "/images/members1.gif";
	members2 = new Image();
	members2.src = "/images/members2.gif";
	
	pictures1 = new Image();
	pictures1.src = "/images/pictures1.gif";
	pictures2 = new Image();
	pictures2.src = "/images/pictures2.gif";
// -->
</script>
<table cellpadding="0" cellspacing="5" style="width: 900px; border-width: 0px;">
<tr>
<th colspan="2">
<img src="images/nothing!" width="700" height="110" alt="MICHAEL WANTS A LOGO">
</th>
</tr>

<tr>
<td style="width: 160px;" class="bodies">

<!-- Sidebar starts here -->
<table style="width: 160px; border-width: 0px;" cellpadding="0" cellspacing="0">
<tr><td>
<? if($login->isadmin) { ?>
<div class="sidetitle">admin</div>
<div class="sidebar">
<br><a class="side" href="http://mail.digitalitcc.com/" onMouseOver="document.email.src = email1.src;" onMouseOut="document.email.src = email2.src;"><img name="email" src="/images/email2.gif" style="vertical-align: middle" alt="Mail"> Mail</a>
</div>
<? } ?>
<div class="sidetitle">account<? if($login->loggedin) { ?> - <span class="cat"><?=$login->userR['username']?></span><? } ?></div>
<div class="sidebar">
<? if(!$login->loggedin) { ?>
<a class="side" href="/login.php" onMouseOver="document.login.src = login1.src;" onMouseOut="document.login.src = login2.src;"><img name="login" src="/images/login2.gif" style="vertical-align: middle" alt="Login"> Login</a>
<br><a class="side" href="http://csf.shsclubs.org/forums/profile.php?mode=register">Register</a>
<? }
else { ?>
<a class="side" href="/logout.php" onMouseOver="document.logoff.src = logoff1.src;" onMouseOut="document.logoff.src = logoff2.src;"><img name="logoff" src="/images/logoff2.gif" style="vertical-align: middle" alt="Logout"> Logout</a>
<br><a class="side" href="/members.php?mode=edit&id=<?=$login->memberR['MEMBER_ID']?>" onMouseOver="document.control.src = control1.src;" onMouseOut="document.control.src = control2.src;"><img name="control" src="/images/control2.gif" style="vertical-align: middle" alt="Profile"> Profile</a>
<? } ?>
</div>
<div class="sidetitle">csf</div>
<div class="sidebar">
<a class="side" href="/index.php" onMouseOver="document.home.src = home1.src;" onMouseOut="document.home.src = home2.src;"><img name="home" src="/images/home2.gif" style="vertical-align: middle" alt="Home"> Home</a>
<br><a class="side" href="/news.php" onMouseOver="document.news.src = news1.src;" onMouseOut="document.news.src = news2.src;"><img name="news" src="/images/news2.gif" style="vertical-align: middle" alt="News"> News</a>
<br><a class="side" href="/events.php" onMouseOver="document.events.src = events1.src;" onMouseOut="document.events.src = events2.src;"><img name="events" src="/images/events2.gif" style="vertical-align: middle" alt="Events"> Events</a>
<br><a class="side" href="/members.php" onMouseOver="document.members.src = members1.src;" onMouseOut="document.members.src = members2.src;"><img name="members" src="/images/members2.gif" style="vertical-align: middle" alt="Members"> Members</a>
<br><a class="side" href="/attendance.php">Attendance</a>
<br><a class="side" href="/points.php" onMouseOver="document.points.src = points1.src;" onMouseOut="document.points.src = points2.src;"><img name="points" src="/images/points2.gif" style="vertical-align: middle" alt="Points"> Points</a>
<br><a class="side" href="/pictures/" onMouseOver="document.pictures.src = pictures1.src;" onMouseOut="document.pictures.src = pictures2.src;"><img name="pictures" src="/images/pictures2.gif" style="vertical-align: middle" alt="Pictures"> Pictures</a>
<br><a class="side" href="/forums/">Forums</a>
<br><a class="side" href="/webmaster.php">Webmaster</a>
</div>
<div class="sidetitle">links</div>
<div class="sidebar">
<a class="side" href="http://www.csf-cjsf.org/">CSF Central</a>
</div>
</td></tr>
</table>
</td>
<!-- Sidebar ends here -->

<!-- Content starts here -->
<td style="width: 740px">
<?
$this->do_navbar();
$this->do_error();
?>
<div class="title"><?=$this->bodytitle?></div>
<div class="body">