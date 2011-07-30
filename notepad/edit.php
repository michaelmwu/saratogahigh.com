<?

include '../db.php';
include 'notepad.php';

if($loggedin)
{
	if($_POST['go'] == 'Save')
	{
		mysql_query('UPDATE NOTEPAGE_LIST SET NOTEPAGE_MODIFIED="' . date(TIME_FORMAT_SQL, CURRENT_TIME) . '", NOTEPAGE_VALUE=\'' . $_POST['entrytext'] . '\', NOTEPAGE_DIGEST=\'' . makedigest($_POST['entrytext']) . '\' WHERE NOTEPAGE_OWNER=' . $userid . ' AND NOTEPAGE_ID=' . $_GET['id']) or die("Insert failed.");
		header("Location: http://" . DNAME . "/notepad/");
	}
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
	<head>
		<title>Notepad</title>
		<meta name="GENERATOR" content="Microsoft Visual Studio.NET 7.0">
		<meta name="vs_targetSchema" content="http://schemas.microsoft.com/intellisense/ie5">
		<link rel="stylesheet" type="text/css" href="../shs.css">
		<style type="text/css">
			a.linkh { font-weight: bold }
		</style>
	</head>
	<body>
		<? include "inc-header.php"; ?>
		<h1>Modify Page</h1>
		<? if($loggedin) { ?>
			<?
			$entries = mysql_query('SELECT NOTEPAGE_LIST.*, NOTEPAGE_CREATED as CR, NOTEPAGE_MODIFIED as MO FROM NOTEPAGE_LIST WHERE NOTEPAGE_OWNER=' . $userid . ' AND NOTEPAGE_ID=' . $_GET['id']) or die('Query failed.');
			if($l = mysql_fetch_array($entries, MYSQL_ASSOC))
			{
			?>
			<form method="post" action="edit.php?id=<?= $_GET['id'] ?>">
			<p><textarea name="entrytext" rows="12" cols="60" wrap="virtual"><?= htmlspecialchars($l['NOTEPAGE_VALUE']) ?></textarea></p>
			<p><input type="submit" name="go" value="Save"></p>
			</form>
			<? } ?>
		<? } else { ?>
			<p>Please <a href="../login.php?next=/notepad/">log in</a> to view your notepad.</p>
		<? } ?>
	<? include '../inc-footer.php'; ?></body>
</html>
