<? include '../db.php';

if(!is_numeric($_GET['id']))
{
	header('location: http://' . DNAME . '/news/?id=1');
	die();
}

$showpage = false;

if($loggedin)
{
	$showpage = true;

	$rstrack = mysql_query("SELECT ASBXTRACK_NAME, ASBXUSER_TITLE, USER_FULLNAME, USER_ID
	FROM ASBXTRACK_LIST
		LEFT JOIN ASBXUSER_LIST ON ASBXUSER_USER=" . $userid . " AND ASBXUSER_TRACK=ASBXTRACK_ID
		LEFT JOIN USER_LIST ON USER_ID=ASBXUSER_USER
		WHERE ASBXTRACK_ID=" . $_GET['id']);

	if(!($curtrack = mysql_fetch_array($rstrack, MYSQL_ASSOC)))
		exit();

	$canpost = (!is_null($curtrack['USER_ID']));

	if(is_numeric($_GET['post']) && $_GET['delete']=='yes' && $canpost)
	{
		mysql_query("DELETE FROM ASBX_LIST WHERE ASBX_ID = " . $_GET['post']);
		header('location: http://' . DNAME . '/news/?id=' . $_GET['id']);
	}
}
else
	forceLogin();


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
	<title>SaratogaHigh.com News Feeds</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<meta name="vs_targetSchema" content="http://schemas.microsoft.com/intellisense/ie5">
	<link rel="stylesheet" type="text/css" href="../shs.css">
	<style type="text/css"><!--
	dt
	{
		font-weight:bold;
		font-size:12pt;
		margin-top:0px
	}
	.newsDateline { margin-top: 2em }
	--></style>
</head>
<body>
<?
include 'inc-header.php';

if($showpage)
{

	print '<div style="background-color: #cc3333; color: #ffffff; padding: 3px; font-size: large; font-weight: bold">' . $curtrack['ASBXTRACK_NAME'] . '</div>';

	print '<table cellpadding="0" cellspacing="0" border="0" style="width: 660px"><tr><td style="vertical-align: top; width: 180px; background-color: #c0c0c0">';

	$rstrax = mysql_query("SELECT ASBXTRACK_LIST.*, UNIX_TIMESTAMP(MAX(ASBX_TS)) AS TS, ASBXUSER_ID
		FROM ASBXTRACK_LIST
		LEFT JOIN ASBX_LIST ON ASBX_TRACK=ASBXTRACK_ID
		LEFT JOIN ASBXUSER_LIST ON ASBXUSER_USER=" . $userid . " AND ASBXUSER_TRACK=ASBXTRACK_ID
		GROUP BY ASBXTRACK_ID ORDER BY ASBXTRACK_ID");

	print '<div style="font-size: medium; padding: 2px; font-weight: bold">Groups</div>';
	while($trax = mysql_fetch_array($rstrax, MYSQL_ASSOC))
	{
		if($trax['ASBXTRACK_DISPLAY'] || !is_null($trax['ASBXUSER_ID']))
		{
			print '<div style="padding: 3px;';
			if($_GET['id'] == $trax['ASBXTRACK_ID'])
				print ' background-color: #ffffff;';
			print '">';
			print '<div><a ';
			if($_GET['id'] == $trax['ASBXTRACK_ID'])
				print 'style="font-weight: bold" ';
			print 'href="./?id=' . $trax['ASBXTRACK_ID'] . '">';
			print $trax['ASBXTRACK_SHORT'];
			print '</a>';
			if($trax['ASBXTRACK_DISPLAY'] == 0)
				print ' (hidden)';
			print '</div>';
			if(!is_null($trax['TS']))
			{
				print '<div style="font-size: small">&nbsp;&nbsp;';
				$postts = $trax['TS'];
				if(time() - $postts < 86400 * 5)
					print date('l ga', $postts);
				else
					print date('D j M y', $postts);
				print '</div>';
			}
			else
			{
				print '<div style="font-size: small">&nbsp;&nbsp;no posts</div>';			
			}
			print '</div>';
		}
	}

	print '</td><td style="vertical-align: top; padding: 5px">';

	if($canpost)
		print '<div style="padding: 4px"><a href="post.php?id=' . $_GET['id'] . '">New Post</a></div>';

	$perpage = 5;
	if(is_numeric($_GET['startno']))
		$startno = $_GET['startno'];
	else
		$startno = 0;

	if(is_numeric($perpage) && is_numeric($startno) && $startno >= 0)
		$limitstr = ' LIMIT ' . $startno . ', ' . $perpage;

	$rsnewscount = mysql_query("SELECT COUNT(ASBX_ID) FROM ASBX_LIST WHERE ASBX_TRACK=" . $_GET['id']);
	$newscount = mysql_fetch_array($rsnewscount, MYSQL_ASSOC);
	$numrows = $newscount['COUNT(ASBX_ID)'];

	print '<div style="padding: 2px; margin-bottom: 4px; border-top: 2px solid #cccccc; border-bottom: 2px solid #cccccc; font-size: medium"><span style="font-weight: bold; color: #600">Show:</span>';
	for($i = 0; $i < $numrows; $i += $perpage)
	{
		if($i + $perpage - 1 >= $numrows)
			$j = $numrows - 1;
		else
			$j = $i + $perpage - 1;
			
		if($i == $j)
			$numtext = $i + 1;
		else
			$numtext = ($i + 1) . '-' . ($j + 1);
			
		if($startno == $i)
			print ' <span style="font-weight: bold">' . $numtext . '</span> ';
		else
			print ' <a href="./?id=' . $_GET['id'] . '&amp;startno=' . $i . '">' . $numtext . '</a>';
	}
	if($i == 0)
		print ' 0';
	print ' of ' . $numrows . '</div>';

	$rsnews = mysql_query("SELECT ASBX_ID, ASBX_SUBJ, ASBX_MSG, ASBX_TS, ASBXUSER_TITLE, USER_FULLNAME, USER_ID
	FROM ASBX_LIST
		LEFT JOIN ASBXUSER_LIST ON ASBXUSER_USER = ASBX_USER AND ASBXUSER_TRACK = ASBX_TRACK
		LEFT JOIN USER_LIST ON USER_ID = ASBX_USER
		WHERE ASBX_TRACK = " . $_GET['id'] . " ORDER BY ASBX_ID DESC" . $limitstr);

	while($news = mysql_fetch_array($rsnews, MYSQL_ASSOC))
	{
		$timestamp = strtotime($news['ASBX_TS']);

		print '<div class="newsDateline"><span style="font-weight: bold">' . date('j F Y, g:i A', $timestamp) . '</span> by <a href="../directory/?id=' . $news['USER_ID'] . '">' . $news['USER_FULLNAME'] . '</a></div>';
		print '<div class="newsHeadline">' . $news['ASBX_SUBJ'] . '</div>';
		print '<div class="newsContent">';
		print '<p>' . ereg_replace("([^\n])\n+([^\n])", '\\1</p><p>\\2', $news['ASBX_MSG']) . '</p>';
		print '</div>';

		if($canpost)
			print '<div style="text-align: right"><span class="toolbar"><a href="post.php?id=' . $_GET['id'] . '&post=' . $news['ASBX_ID'] . '">Edit</a></span> <span class="toolbar"><a href="./?id=' . $_GET['id'] . '&post=' . $news['ASBX_ID'] . '&delete=yes">Delete</a></span></div>';
	}

	print '</td></tr></table>';
}

include "../inc-footer.php";
?>
</body>
</html>

