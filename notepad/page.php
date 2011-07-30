<?
include '../db.php';
include 'notepad.php';
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
		<h1>View Page</h1>
		<? if($loggedin) { ?>
			<?
			$entries = mysql_query('SELECT NOTEPAGE_LIST.*, NOTEPAGE_CREATED as CR, NOTEPAGE_MODIFIED as MO FROM NOTEPAGE_LIST WHERE NOTEPAGE_OWNER=' . $userid . ' AND NOTEPAGE_ID=' . $_GET['id']) or die('Query failed.');
			
			if($l = mysql_fetch_array($entries, MYSQL_ASSOC))
			{
				print '<table>';
				print '<tr><td>Created</td><td>' . date(TIME_FORMAT, strtotime($l['CR'])) . '</td></tr>';
				print '<tr><td>Modified</td><td>' . date(TIME_FORMAT, strtotime($l['MO'])) . '</td></tr>';
				print '</table>';
				print '<p><span class="toolbar"><a href="edit.php?id=' . $_GET['id'] . '">Modify</a></span>&nbsp;<span class="toolbar"><a href="/mail/compose.php?fwnote=' . $_GET['id'] . '">Forward</a></span>&nbsp;<span class="toolbar"><a href="./?delete=' . $_GET['id'] . '">Delete</a></span></p>';
				print '<p style="font-family: monospace; background-color: #eeeeee; padding: 5px;">' . printable($l['NOTEPAGE_VALUE']) . '</p>';
			}
			?>
		<? } else { ?>
			<p>Please <a href="../login.php?next=/notepad/">log in</a> to view your notepad.</p>
		<? } ?>
	<? include '../inc-footer.php'; ?></body>
</html>
