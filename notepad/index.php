<?

include '../db.php';
include 'notepad.php';

if($_GET['charset'] == '')
	$charset = 'utf-8';
else
	$charset = htmlentities($_GET['charset']);

if($loggedin)
{
	$toomanynotes = TooManyNotes($userid);

	if(is_numeric($_GET['delete']))
		mysql_query('DELETE FROM NOTEPAGE_LIST WHERE NOTEPAGE_OWNER=' . $userid . ' AND NOTEPAGE_ID=' . $_GET['delete']) or die("Delete failed.");
	if($_POST['go'] == 'Save')
	{
		if(strlen(stripslashes($_POST['entrytext'])) > 65530)
			$errorm = '<p style="font-size: medium">Your note was too long. Notes are limited to 65530 characters.</p>.';
		else if($toomanynotes)
			$errorm = '<p style="font-size: medium">Sorry, you\'re limited to 60 notes.</p>';
		else
			mysql_query('INSERT INTO NOTEPAGE_LIST (NOTEPAGE_OWNER, NOTEPAGE_CREATED, NOTEPAGE_MODIFIED, NOTEPAGE_VALUE, NOTEPAGE_DIGEST) VALUES (' . $userid . ', "' . date(TIME_FORMAT_SQL, CURRENT_TIME) . '", NOTEPAGE_CREATED, \'' . $_POST['entrytext'] . '\', \'' . makedigest($_POST['entrytext']) . '\')') or die("Insert failed.");
	}
}
else
	forceLogin();

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
	<head>
		<title>Notepad</title>
		<meta name="GENERATOR" content="Microsoft Visual Studio.NET 7.0">
		<meta name="vs_targetSchema" content="http://schemas.microsoft.com/intellisense/ie5">
		<link rel="stylesheet" type="text/css" href="../shs.css">
		<meta http-equiv="Content-Type" content="text/html; charset=<?= $charset ?>">
		<style type="text/css">
			a.linkh { font-weight: bold }
			.headed { background-color: #f0f0f0; border-width: 1px 1px 1px 1px; border-style: solid; border-color: #666; padding: 3px; }
		</style>
	</head>
	<body>
		<? include "inc-header.php"; ?>
		<h1>Notepad</h1>
		<?= htmlentities($errorm) ?>
		<p>Notepad is the easy way to save information you've found on the web. You can use it to store links and text you've found at school and access them at home, or vice versa.</p> 
        <? if(strlen($_GET['query']) > 0) { ?>
        <form method="get" action="./">
        <p>Search my pages: <input type="text" name="query" value="<?= htmlentities(stripslashes($_GET['query'])) ?>"> <input type="submit" name="go" value="Search"><!-- <a href="search.php">Search Tips</a>--></p>
        </form>
        
        <h2>Pages matching '<?= htmlentities(stripslashes($_GET['query'])) ?>'</h2>
        <p><a href="./">Cancel search</a></p>
        <?
        
        $entries = mysql_query('SELECT NOTEPAGE_ID, NOTEPAGE_DIGEST, UNIX_TIMESTAMP(NOTEPAGE_CREATED) as TS FROM NOTEPAGE_LIST WHERE NOTEPAGE_OWNER=' . $userid . ' AND MATCH(NOTEPAGE_VALUE) AGAINST (\'' . $_GET['query'] . '\')') or die('Query failed.');
        
        print '<p>' . mysql_num_rows($entries) . ' page(s), sorted by relevance.</p>';
        
        while($l = mysql_fetch_array($entries, MYSQL_ASSOC))
        	print '<p><a href="page.php?id=' . $l['NOTEPAGE_ID'] . '">' . date(TIME_FORMAT, $l['TS']) . '</a>: ' .  htmlentities($l['NOTEPAGE_DIGEST']) . ' <a href="./?go=Search?&amp;query=' . $_GET['query'] . '&amp;delete=' . $l['NOTEPAGE_ID'] . '">Delete</a></p>';
        ?>				
        <? } else { ?>
        
	  <table>
	  <tr>
	  <td style="vertical-align: top;">
        <h2>New Page</h2>
        
        <? if(!$toomanynotes) { ?>
        <form method="post" action="./">
        <p><textarea name="entrytext" rows="12" cols="60" wrap="virtual"></textarea></p>
        <p><input type="submit" name="go" value="Save"></p>
        </form>
        <? } else { ?>
        <p>You can't save any more notes at this time, because you've exceeded your limit.</p>				
        <? } ?>
        </td>
	  <td style="vertical-align: top;">
        <h2>All Pages</h2>
	  <div class="headed">
        <form method="get" action="./">
        <p>Search my pages: <input type="text" name="query" value=""> <input type="submit" name="go" value="Search"><!-- <a href="search.php">Search Tips</a>--></p>
        </form>
        
        <?
        
        $entries = mysql_query('SELECT NOTEPAGE_ID, NOTEPAGE_DIGEST, NOTEPAGE_CREATED as TS FROM NOTEPAGE_LIST WHERE NOTEPAGE_OWNER=' . $userid . ' ORDER BY NOTEPAGE_CREATED DESC') or die('Query failed.');
        
        print '<p>' . mysql_num_rows($entries) . ' page(s).</p>';
        
        while($l = mysql_fetch_array($entries, MYSQL_ASSOC))
        	print '<p><a href="page.php?id=' . $l['NOTEPAGE_ID'] . '">' . date(TIME_FORMAT, strtotime($l['TS'])) . '</a>: ' .  htmlentities($l['NOTEPAGE_DIGEST']) . ' <a href="./?delete=' . $l['NOTEPAGE_ID'] . '">Delete</a></p>';
        ?>
        <? } ?>
	  </div>
	  </td>
	  </tr>
	  </table>
	<? include '../inc-footer.php'; ?></body>
</html>
