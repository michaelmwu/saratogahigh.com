<?
// Philip Sung | 0x7d3 | saratogahigh.com
// calendar/addlayer.php: lets the user create a new layer or modify an existing one

include "../db.php";
include "cal.php";

if($loggedin)
{	
	$errorm = '';
	$skipall = false;
	
	// Find the id# of the layer being modified
	// GET is for incoming hyperlinks
	// POST is for when the form posts to itself to save
	if(is_numeric($_GET['open']))
	{
		$loadrec = true;
		$openno = $_GET['open'];
	}
	else if(is_numeric($_POST['openno']))
	{
		$openno = $_POST['openno'];
	}
	
	// Save data
	if($_POST['go'] == "Save and Close")
	{
		$layer_title = stripslashes($_POST['LAYER_TITLE']);
		$layer_desc = stripslashes($_POST['LAYER_DESC']);

		$layer_open = $_POST['LAYER_OPEN'];
		
		// Validate data
		if(strlen($layer_title) == 0)
			$errorm = 'You must enter a title.';
		else if(strlen($layer_title) > 80)
			$errorm = 'Your title cannot exceed 80 characters.';
		else if(strlen($layer_desc) > 200)
			$errorm = 'Your description cannot exceed 200 characters.';
		else if(!ereg('^[A-Za-z0-9 ,\.\'"_-]{1,80}$', $layer_title))
			$errorm = 'Your title may only contain letters, numbers, spaces, and these characters: .,\'"_-';
			
		if($layer_open != 1)
			$layer_open = 0;

		// Save if no errors
		if($errorm == '')
		{
			// Save an existing layer
			if(is_numeric($openno))
			{
				$lresult = mysql_query('SELECT COUNT(*) FROM LAYERUSER_LIST WHERE LAYERUSER_LAYER=' . $openno . ' AND LAYERUSER_USER=' . $userid . ' AND LAYERUSER_ACCESS=3') or die('Authentication failed.');
				if($ll = mysql_fetch_array($lresult, MYSQL_ASSOC))
				{
					$e_layer = $ll['EVENT_LAYER'];

					$insertsql = 'UPDATE LAYER_LIST SET
						LAYER_TITLE=\'' . addslashes($layer_title) . '\',
						LAYER_DESC=\'' . addslashes($layer_desc) . '\',
						LAYER_OPEN=' . $layer_open . '
					WHERE LAYER_ID=' . $openno . '';
				}
				
				mysql_free_result($lresult);
				
				mysql_query($insertsql) or die('Update failed: ' . $insertsql);
				
				header('Location: layer.php?viewset=' . $openno);
				$skipall = true;
			}
			// Make a new layer
			else
			{
				$continue = true;
				if($isteacher && ! ($_POST['class'] == 'none'))
				{
					if( is_numeric($_POST['class']) )
					{
						$cresult = mysql_query("SELECT * FROM LAYER_LIST WHERE LAYER_CLASS=" . $_POST['class'] . " AND LAYER_TEACHER=" . $usertag) or die(mysql_error());

						if( mysql_fetch_array($cresult, MYSQL_ASSOC) )
						{
							$errorm = "A homework calendar has already been created for this class.";
							$continue = false;
						}
						else
						{
							$cresult = mysql_query("SELECT * FROM VALIDCLASS_LIST WHERE VALIDCLASS_TEACHER=$usertag AND VALIDCLASS_COURSE=" . $_POST['class']) or die(mysql_error());

							if( mysql_fetch_array($cresult, MYSQL_ASSOC) )
							{
								mysql_query('INSERT INTO LAYER_LIST (LAYER_LASTMODIFIED, LAYER_TITLE, LAYER_DESC, LAYER_OPEN, LAYER_CLASS, LAYER_TEACHER)
									VALUES ("' . date(TIME_FORMAT_SQL, CURRENT_TIME) . '",\'' . addslashes($layer_title) . '\',\'' . addslashes($layer_desc) . '\',' . $layer_open . ', ' . $_POST['class'] . ', ' . $usertag . ')') or die('Group update failed. ' . mysql_error());
							}
							else
							{
								$errorm = "Sorry, but you cannot create a calendar for this class.";
								$continue = false;								
							}
						}
					}
					else
					{
						$errorm = "Invalid Class";
						$continue = false;
					}
				}
				else
				{
					mysql_query('INSERT INTO LAYER_LIST (LAYER_LASTMODIFIED, LAYER_TITLE, LAYER_DESC, LAYER_OPEN)
						VALUES ("' . date(TIME_FORMAT_SQL, CURRENT_TIME) . '",\'' . addslashes($layer_title) . '\',\'' . addslashes($layer_desc) . '\',' . $layer_open . ')') or die('Group update failed.');
				}

				if( $continue )
				{
					$lastid = mysql_insert_id();
				
					// Select a color for the administrator
					$availcolors = mysql_query('SELECT DISTINCTROW CALCOLOR_ID FROM CALCOLOR_LIST LEFT JOIN LAYERUSER_LIST ON LAYERUSER_COLOR=CALCOLOR_ID AND LAYERUSER_USER=' . $userid . ' ORDER BY LAYERUSER_USER ASC, LAYERUSER_DISPLAY ASC, RAND() LIMIT 1') or die('Colors failed.');
					if($k = mysql_fetch_array($availcolors, MYSQL_ASSOC))
						$newcolor = $k['CALCOLOR_ID'];
					else
						$newcolor = 0;

					mysql_query('INSERT INTO LAYERUSER_LIST (LAYERUSER_COLOR, LAYERUSER_USER, LAYERUSER_LAYER, LAYERUSER_ACCESS)
						VALUES (' . $newcolor . ', ' . $userid . ',' . $lastid . ',3)') or die('Membership update failed.');
					
					//header('Location: layer.php?viewset=' . $lastid);
					header('Location: calendar.php?viewset=' . $lastid);
					$skipall = true;
				}
			}
		}
	}
	// Cancel update
	else if($_POST['go'] == "Cancel")
	{
		header('Location: layer.php?viewset=' . $openno);
		$skipall = true;
	}
	// Load settings from file and stick them into the form
	else if($loadrec)
	{
		$uresult = mysql_query('SELECT LAYER_LIST.* FROM LAYER_LIST INNER JOIN LAYERUSER_LIST ON LAYER_ID=LAYERUSER_LAYER WHERE LAYERUSER_ACCESS=3 AND LAYERUSER_USER=' . $userid . ' AND LAYER_ID=' . $openno) or die("Query error.");
		
		if($le = mysql_fetch_array($uresult, MYSQL_ASSOC))
		{
			$layer_title = $le['LAYER_TITLE'];
			$layer_desc = $le['LAYER_DESC'];
			$layer_open = $le['LAYER_OPEN'];
		}
	}
	// New layer
	else
	{	
		$layer_title = '';
		$layer_desc = '';

		$layer_open = 0;
	}
}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
<?

if(is_numeric($openno))
{
	print '<title>Edit Group</title>';
}
else
{
	print '<title>New Group</title>';
}

?>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<meta name="vs_targetSchema" content="http://schemas.microsoft.com/intellisense/ie5">
	<link rel="stylesheet" type="text/css" href="../shs.css">
	<style type="text/css"><!--
		a.lnkc { font-weight: bold }
		td.rh { background-color: #dddddd; border: 1px solid #999999 }
		td.rd { padding-left: 20px }
		input.numreccs { width: 60px }
	--></style>
	<script type="text/javascript"><!--
		function delgroup()
		{
			if(window.confirm("Are you sure you want to delete this group? This action cannot be undone."))
				location.href = "layer.php?viewset=<?= $openno ?>&amp;action=delete";
			return;
		}
	// --></script>	
</head>
<body>
	<? include "inc-header.php"; ?>
<?

if(is_numeric($openno))
	print '<h1>Edit Group</h1>';
else
	print '<h1>New Group</h1>';

if($loggedin && !$skipall)
{

if($errorm != "")
	print '<p style="font-weight: bold; color: #800000">' . $errorm . '</p>';
?>

	<?
	if(is_numeric($openno))
	{
		print '<p style="margin-bottom: 5px;"><span class="toolbar"><a href="javascript:delgroup();">Delete This Group</a></span></p>';
		print '<p><span class="toolbar"><a href="categories.php?view=l&amp;viewset=' . $HGVviewset .'&amp;start=' . $HGVstart .'&amp;id=' . $openno . '">Select Categories</a></span> Choose one or more categories for listing in the Browse Groups directory. The group is displayed only if you select the Public option under Privacy below.</p>'
	?>
		
	<?
	}
	?>
	<form method="post" action="addlayer.php?open=<?= $openno ?>">
		<table>
			<tr>
				<td>Title</b></td>
				<td><input type="text" name="LAYER_TITLE" size="35" maxlength="80" value="<?= htmlentities($layer_title) ?>"></td>
			</tr>
			<tr>
				<td>Description</b></td>
				<td><input type="text" name="LAYER_DESC" size="35" maxlength="200" value="<?= htmlentities($layer_desc) ?>"></td>
			</tr>
			<tr>
				<td>Privacy</td>
				<td><select name="LAYER_OPEN">
					<option <? if($layer_open == 1) { print 'selected'; } ?> value="1">Public</option>
					<option <? if($layer_open == 0) { print 'selected'; } ?> value="0">Private</option>
				</select></td>
				<td class="tdinfo">Select "Public" to allow anyone to view events. Otherwise, users who want to subscribe or view events will have to be approved by an admininstrator.</td>
			</tr>

	<? if($isteacher) { ?>

	<tr>

	<td style="width: 140px;">Homework Calendar for</td>

	<td>

	<?
	$cresult = mysql_query("SELECT CLASS_LIST.* FROM VALIDCLASS_LIST
					INNER JOIN CLASS_LIST ON VALIDCLASS_COURSE=CLASS_ID
					LEFT JOIN LAYER_LIST ON LAYER_TEACHER=$usertag AND LAYER_CLASS=VALIDCLASS_COURSE
					WHERE VALIDCLASS_TEACHER=$usertag AND LAYER_ID IS NULL") or die(mysql_error());

	print "<select name='class'>\n";
	print "<option value='none' selected>None\n";

	while( $l = mysql_fetch_array($cresult, MYSQL_ASSOC) )
	{
		print "<option value='" . $l['CLASS_ID'] . "'>" . $l['CLASS_NAME'] . "\n";
	}

	print "</select>";
	?>

	</td>

	<td class="tdinfo">If this calendar is to be a homework calendar for a class, select the class. Otherwise, select 'None.' You cannot create a homework calendar for a class which already has a homework calendar.</td>

	</tr>

	<? } ?>

	</table>

		<p><input type="submit" name="go" value="Save and Close"><? if(is_numeric($openno)) { ?> <input type="submit" name="go" value="Cancel"><? } ?><input type="hidden" name="openno" value="<?= $openno ?>"></p>
	</form>

<?

}
else
{

?>
	<p>Please <a href="../login.php?next=/calendar/addlayer.php">log in</a> to add groups.</p>
<?
}

?>
<? include '../inc-footer.php'; ?></body>

</html>

