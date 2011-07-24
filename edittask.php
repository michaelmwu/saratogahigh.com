<?
include '../db.php';
require 'cpvalidation.php';

if(is_id($_GET['edit']))
{
    if($_POST['btn'] == 'Post')
    {
	if($_POST['msgtext'] !== "") //hey no one checked for emptiness
	{	
		$newid = $_GET['edit'];
		$newts = date(TIME_FORMAT_SQL, CURRENT_TIME);
    	mysql_query("INSERT INTO TASKCOMMENT_LIST VALUES ('', $newid, $userid, '" . $_POST['msgtext'] . "', '$newts')");
	}

		header('location: http://' . DNAME . '/shcp/edittask.php?edit=' . $_GET['edit']);
    }
	else if($_POST['btn'] == 'Save and Close' && $isadmin)
    {
    	if(is_id($_POST['TASK_PRIORITY']) && is_id($_POST['TASK_CAT']) && is_id($_POST['TASK_STATUS']) && is_id($_POST['TASK_TYPE']))
    	{
    		mysql_query('UPDATE TASK_LIST SET
        		TASK_PRIORITY=' . $_POST['TASK_PRIORITY'] . ',
        		TASK_CAT=' . $_POST['TASK_CAT'] . ',
        		TASK_STATUS=' . $_POST['TASK_STATUS'] . ',
        		TASK_TYPE=' . $_POST['TASK_TYPE'] . '
        	WHERE TASK_ID=' . $_GET['edit']);
    	}
    
    	header('location: http://' . DNAME . '/shcp/tasklist.php?type=' . $_POST['TASK_TYPE']);
    }
    else if($_POST['btn'] == 'Delete' && $isadmin)
    {
    	mysql_query('DELETE FROM TASK_LIST WHERE TASK_ID=' . $_GET['edit']);
		mysql_query('DELETE FROM TASKCOMMENT_LIST WHERE TASKCOMMENT_TASK=' . $_GET['edit']);
    	header('location: tasklist.php');
    }
    else if($_POST['btn'] == 'Archive' && $isadmin)
    {
        mysql_query('UPDATE TASK_LIST SET TASK_ARCHIVED=1 WHERE TASK_ID=' . $_GET['edit']);
        header('location: tasklist.php');
    }
	else if($_POST['btn'] == 'Close')
    {
		header('location: http://' . DNAME . '/shcp/tasklist.php?type=' . (is_id($_POST['TASK_TYPE']) ? $_POST['TASK_TYPE'] : ''));
    }
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
	<head>
		<title>Edit Task</title>
		<link rel="stylesheet" type="text/css" href="../shs.css">
		<meta name="vs_targetSchema" content="http://schemas.microsoft.com/intellisense/ie5">
		<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
		<link rel="stylesheet" type="text/css" href="admin.css">
		<style type="text/css"><!--
			h1 { font-size: large }
			h2 { text-transform: uppercase; font-size: medium; font-weight: bold; margin-bottom: 0; border-bottom: 2px dotted gray }
			table.propertybox tr td:first-child { width: 6em; background-color: #ddd; font-weight: bold; text-align: right }
			table.propertybox { background-color: #eee; font-size: medium; width: 400px; float: left; border: 2px solid #999; margin: 0 6pt 6pt 0 }
		--></style>
	</head>
	<body>

<? include "inc-header.php" ?>

<?
if(is_id($_GET['edit']))
{
?>

<h1>Edit Task</h1>

<form method="POST" action="edittask.php?edit=<?= $_GET['edit'] ?>">
<?
$tasks = mysql_query('SELECT TASK_LIST.*, USER_FULLNAME FROM TASK_LIST LEFT JOIN USER_LIST ON USER_ID=TASK_AUTHOR WHERE TASK_ID=' . $_GET['edit']);

if($ctask = mysql_fetch_array($tasks, MYSQL_ASSOC))
{
?>
	<table class="propertybox" cellpadding="2" cellspacing="0">
	<tr><td>Title</td><td><?= htmlentities($ctask['TASK_TITLE']) ?></td></tr>
	<tr><td>Status</td><td><select name="TASK_STATUS">
<?
$rsstatus = mysql_query('SELECT * FROM TASKSTATUS_LIST ORDER BY TASKSTATUS_ID');
while($status = mysql_fetch_array($rsstatus, MYSQL_ASSOC))
{
	print '<option value="' . $status['TASKSTATUS_ID'] . '"';
	if($status['TASKSTATUS_ID'] == $ctask['TASK_STATUS'])
		print ' selected';
	print '>' . $status['TASKSTATUS_NAME'] . '</option>';
}
?>
	</select></td></tr>
	<tr><td>Priority</td><td><select name="TASK_PRIORITY">
<?
$rspriority = mysql_query('SELECT * FROM TASKPRIORITY_LIST ORDER BY TASKPRIORITY_ID');
while($priority = mysql_fetch_array($rspriority, MYSQL_ASSOC))
{
	print '<option value="' . $priority['TASKPRIORITY_ID'] . '"';
	if($priority['TASKPRIORITY_ID'] == $ctask['TASK_PRIORITY'])
		print ' selected';
	print '>' . $priority['TASKPRIORITY_NAME'] . '</option>';
}
?>
	</select></td></tr>
	<tr><td>Category</td><td><select name="TASK_CAT">
<?
$rscat = mysql_query('SELECT * FROM TASKCAT_LIST ORDER BY TASKCAT_NAME');
while($cat = mysql_fetch_array($rscat, MYSQL_ASSOC))
{
	print '<option value="' . $cat['TASKCAT_ID'] . '"';
	if($cat['TASKCAT_ID'] == $ctask['TASK_CAT'])
		print ' selected';
	print '>' . $cat['TASKCAT_NAME'] . '</option>';
}
?>
	</select></td></tr>
	<tr><td>Type</td><td><select name="TASK_TYPE">
<?
$rstype = mysql_query('SELECT * FROM TASKTYPE_LIST ORDER BY TASKTYPE_NAME');
while($type = mysql_fetch_array($rstype, MYSQL_ASSOC))
{
	print '<option value="' . $type['TASKTYPE_ID'] . '"';
	if($type['TASKTYPE_ID'] == $ctask['TASK_TYPE'])
		print ' selected';
	print '>' . $type['TASKTYPE_NAME'] . '</option>';
}
?>
	</select></td></tr>
	<tr><td>Author</td><td><?= $ctask['USER_FULLNAME'] ?></td></tr>
	<tr><td>Date</td><td><?= date(TIME_FORMAT, strtotime($ctask['TASK_CREATED'])) ?></td></tr>
	<tr><td>References</td><td><?= is_null($ctask['TASK_REFURL']) ? 'None' : '<a href="' . htmlentities($ctask['TASK_REFURL']) . '">' .  htmlentities($ctask['TASK_REFURL']) . '</a>' ?></td></tr>
<? if($isadmin) { ?>
	<tr><td style="background-color: #999; text-align: left"><? if($isadmin) { ?><input type="submit" name="btn" value="Delete" onclick="return window.confirm('Do you really want to delete this task?');" style="font-weight: normal"><? } ?></td><td style="background-color: #999; text-align: right"><? if($isadmin) { ?><input type="submit" name="btn" value="Archive" onclick="return window.confirm('Archive this task?');" style="font-weight: normal"><? } ?> <input type="hidden" name="act" value="save"><input type="submit" name="btn" value="Save and Close"> <input type="submit" name="btn" value="Close"></td></tr>
<? } ?>
	</table>
	
	<?= (strlen($ctask['TASK_DESC']) > 0) ? '<h2>Details</h2>' : '' ?>
	<p><?= nl2br(htmlentities($ctask['TASK_DESC'])) ?></p>
	
	<?
	
	$rscomments = mysql_query('SELECT TASKCOMMENT_TEXT, TASKCOMMENT_TS, USER_FULLNAME FROM TASKCOMMENT_LIST INNER JOIN USER_LIST ON TASKCOMMENT_AUTHOR=USER_ID WHERE TASKCOMMENT_TASK=' . $_GET['edit']);

	while($comment = mysql_fetch_array($rscomments, MYSQL_ASSOC))
	{
    	print '<h2>' . $comment['USER_FULLNAME'] . ', ' . date(TIME_FORMAT, strtotime($comment['TASKCOMMENT_TS'])) .  '</h2>';
    	print '<p>' . nl2br(htmlentities($comment['TASKCOMMENT_TEXT'])) . '</p>';
	}
	
	?>

	<h2>New Comment</h2>
	<p><textarea name="msgtext" style="width: 350px" rows="5"></textarea></p>
	<p><input type="submit" name="btn" value="Post"></p>
	
	</form>
	<hr style="clear: left">
<?
	}
?>

<?
}
?>

<? include '../inc-footer.php'; ?></body>
</html>
