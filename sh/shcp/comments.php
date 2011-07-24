<?
include '../db.php';
require 'cpvalidation.php';

if(is_numeric($_GET['del']))
	mysql_query("DELETE FROM COMMENT_LIST WHERE COMMENT_ID=" . $_GET['del']) or die('Query failed.');

if(is_numeric($_GET['archive']))
	mysql_query("UPDATE COMMENT_LIST SET COMMENT_ARCHIVED=1 WHERE COMMENT_ID=" . $_GET['archive']) or die('Query failed.');

$error = 0; ?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
	<head>
		<title>View Comments</title>
		<link rel="stylesheet" type="text/css" href="../shs.css">
		<meta name="vs_targetSchema" content="http://schemas.microsoft.com/intellisense/ie5">
		<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
		<link rel="stylesheet" type="text/css" href="admin.css">
		<style type="text/css"><!--
			span.proj { display: inline }
			a.proj-comm { font-weight: bold }
			.title { font-family: sans-serif; margin: 0px; padding: 1px; color: #800000 }
		--></style>
	</head>
	<body>

<? include "inc-header.php" ?>

<?
if(is_numeric($_GET['cat']))
	$currentcat = $_GET['cat'];
else
	$currentcat = 1;

$rscategories = mysql_query('SELECT COMMENTCAT_ID, COMMENTCAT_NAME, COUNT(COMMENT_ID) AS C FROM COMMENTCAT_LIST LEFT JOIN COMMENT_LIST ON COMMENTCAT_ID=COMMENT_CAT AND COMMENT_ARCHIVED=0 GROUP BY COMMENTCAT_ID');

print '<div style="margin-bottom: 2ex; font-size: medium"><span style="font-weight: bold">Categories</span>:';
while($category = mysql_fetch_array($rscategories, MYSQL_ASSOC))
{
	if($currentcat == $category['COMMENTCAT_ID'])
		print ' <span style="font-style: italic">' . $category['COMMENTCAT_NAME'] . '</span> (' . $category['C'] . ')';
	else
		print ' <a href="comments.php?cat=' . $category['COMMENTCAT_ID'] . '"><span style="font-style: italic">' . $category['COMMENTCAT_NAME'] . '</span> (' . $category['C'] . ')</a>';
}
print '</div>';

$entries = mysql_query("SELECT COMMENT_LIST.*, USER_EMAIL, USER_FULLNAME, USER_GR, USER_ID FROM COMMENT_LIST LEFT JOIN USER_LIST ON COMMENT_USER=USER_ID WHERE COMMENT_ARCHIVED=0 AND COMMENT_CAT=" . $currentcat . " ORDER BY COMMENT_TS DESC") or die('Query failed.');
print '<table style="table-layout: fixed">';
while($l = mysql_fetch_array($entries, MYSQL_ASSOC))
{
	print '<tr>';
	
	print '<td style="vertical-align: top; width: 11em"><p style="margin: 0; padding: 3px">' . date('D j M Y', strtotime($l['COMMENT_TS'])) . '<br><a href="comments.php?del=' . $l['COMMENT_ID'] . '&amp;cat=' . $currentcat . '">Delete</a><br><a href="comments.php?archive=' . $l['COMMENT_ID'] . '&amp;cat=' . $currentcat . '">Archive</a>';
	if(!is_null($l['COMMENT_USER']))
		print '<br><a href="/mail/compose.php?recomment=' . $l['COMMENT_ID'] . '">Reply</a>';
	print '<br><a href="/shcp/email.php?recomment=' . $l['COMMENT_ID'] . '"&amp;next=/shcp/comments%3Fcat=' . $currentcat . '>Reply by Email</a>';
	print '</p></td>';
	
	print '<td style="vertical-align: top">';
	print '<div style="padding: 3px; margin: 0">from </span><a style="font-family: monospace" href="' . htmlentities($l['COMMENT_PAGE']) . '">' . $l['COMMENT_PAGE'] . '</a></div>';
	if(!is_null($l['COMMENT_USER']))
		print '<div style="margin: 0px; padding: 3px; background-color: #ddd">Sender: <a href="/directory/?id=' . $l['USER_ID'] . '">' . $l['USER_FULLNAME'] . '</a> Email: <a href="mailto:' . $l['USER_EMAIL'] . '">' . $l['USER_EMAIL'] . '</a> ' . GradePrint($l['USER_GR']) . '</div>';
	print '<div style="font-family: monospace; margin: 0px; padding: 3px; background-color: #eeeeee">' . nl2br(urls_clickable(htmlspecialchars($l['COMMENT_TEXT']))) . '</div>';
	print '</td>';
	
	print '<tr>';
}
print '</table>';
?>

<? include '../inc-footer.php'; ?></body>
</html>