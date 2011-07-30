<?
include '../db.php';
require 'cpvalidation.php';

if(is_numeric($_GET['del']))
{
	$query = 'DELETE FROM TEACHERROOM_LIST WHERE TEACHERROOM_ID=' . $_GET['del'];
	$result = mysql_query($query) or die("Query failed");
}
else if($_POST['btn'] == 'New')
{	
	if(is_numeric($_POST['per']) && is_numeric($_POST['teacher']) && is_numeric($_POST['room']))
	{
		$result = mysql_query('INSERT INTO TEACHERROOM_LIST (TEACHERROOM_TEACHER, TEACHERROOM_ROOM, TEACHERROOM_PER) VALUES
			(' . $_POST['teacher'] . ', ' . $_POST['room'] . ', ' . $_POST['per'] . ')') or die('Insertion failed');
	}
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
	<head>
		<title>Teacher/Per/Room Combinations</title>
		<link rel="stylesheet" type="text/css" href="../shs.css">
		<meta name="vs_targetSchema" content="http://schemas.microsoft.com/intellisense/ie5">
		<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
		<link rel="stylesheet" type="text/css" href="admin.css">
		<style type="text/css"><!--
			span.db { display: inline }
			a.db-room { font-weight: bold }
		--></style>
	</head>
	<body>

<? include "inc-header.php" ?>

<form method="POST" name="fform" action="teacherroom.php">

<?
print $resp;

if($error == 0)
{
?>
	<table cellpadding="1" cellspacing="1">
	<tr><td class="header">Per</td><td class="header">Teacher</td><td class="header">Room</td><td style="width: 90px"></td></tr>
	<tr>
		<td class="data" valign="top"><select id="fper" name="per"><?
		for($i = 1; $i <= 7; $i++)
		{
			print "<option ";
			if($i == $_POST['per'])
				print "selected ";
			print 'value="' . $i . '">' . $i . '</option>\n';
		}
		print '</select>';
		?></td>
		<td class="data" valign="top"><select id="fteacher" name="teacher"><?
		$cResult = mysql_query("SELECT * FROM TEACHER_LIST WHERE TEACHER_ACTIVE = 1 ORDER BY TEACHER_NAME") or die("User query failed");
		while ($l = mysql_fetch_array($cResult, MYSQL_ASSOC)) {
			print "<option ";
			if($l['TEACHER_ID'] == $_POST['teacher'])
				print "selected ";
			print 'value="' . $l['TEACHER_ID'] . '">' . $l['TEACHER_NAME'] . '</option>\n';
		}
		mysql_free_result($cResult);
		print '</select>';
		?></td>
		<td class="data" valign="top"><select id="froom" name="room"><?
		$cResult = mysql_query("SELECT * FROM ROOM_LIST ORDER BY ROOM_NAME") or die("User query failed");
		while ($l = mysql_fetch_array($cResult, MYSQL_ASSOC)) {
			print "<option ";
			if($l['ROOM_ID'] == $_POST['room'])
				print "selected ";
			print 'value="' . $l['ROOM_ID'] . '">' . $l['ROOM_NAME'] . '</option>\n';
		}
		mysql_free_result($cResult);
		print '</select>';
		?></td>
		<td><input type="submit" name="btn" value="New"></td>
	</tr>
	</table>
	<p>Click a room name to delete it. Click on an empty space to add a room entry there.</p>
	<table style="table-layout: fixed">
	<tr><td style="width: 120px" class="header">Teacher</td><? 
		for($i = 1; $i <= 7; $i++)
		{
			print '<td style="width: 80px" class="header">Per ' . $i . '</td>';
		}
	?></tr>
<?
	$tresult = mysql_query("SELECT TEACHER_ID, TEACHER_NAME FROM TEACHER_LIST WHERE TEACHER_ACTIVE = 1 ORDER BY TEACHER_NAME") or die("Teacher query failed");
	while($line = mysql_fetch_array($tresult, MYSQL_ASSOC))
	{
		$rresult = mysql_query("SELECT TEACHERROOM_ID, TEACHERROOM_PER, ROOM_NAME FROM TEACHERROOM_LIST INNER JOIN TEACHER_LIST ON TEACHERROOM_TEACHER=TEACHER_ID INNER JOIN ROOM_LIST ON TEACHERROOM_ROOM=ROOM_ID WHERE TEACHER_ID=" . $line['TEACHER_ID'] . " ORDER BY TEACHER_NAME, TEACHERROOM_PER") or die("Periods query failed");

		print '<tr><td class="data">' . $line['TEACHER_NAME'] . '</td>';
		
		for($i = 1; $i <= 7; $i++)
		{
			$curroom[$i] = 0;
			$curentry[$i] = 0;
		}

		while($l = mysql_fetch_array($rresult, MYSQL_ASSOC))
		{
			$curroom[$l['TEACHERROOM_PER']] = $l['ROOM_NAME'];
			$curentry[$l['TEACHERROOM_PER']] = $l['TEACHERROOM_ID'];
		}

		for($i = 1; $i <= 7; $i++)
		{
			if($curentry[$i] > 0)
				print '<td class="data"><a href="teacherroom.php?del=' . $curentry[$i] . '">' . $curroom[$i] . '</a></td>';
			else
				print '<td onclick="fform.fteacher.value=' . $line['TEACHER_ID'] . '; fform.fper.value=' . $i . '; fform.froom.focus();" class="data">&nbsp;</td>';
		}
		
		print '</tr>';
		
		mysql_free_result($rresult);
	}
	mysql_free_result($tresult);
?>
	</table>
<?
}
?>
</form>

<? include '../inc-footer.php'; ?>
</body>
</html>
