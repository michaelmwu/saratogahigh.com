<?
include '../db.php';
require 'cpvalidation.php';

if(is_numeric($_GET['t']))
{
	if($_GET['set'] == '0')
		mysql_query('UPDATE TEACHER_LIST SET TEACHER_ACTIVE=0 WHERE TEACHER_ID=' . $_GET['t']);
	else
		mysql_query('UPDATE TEACHER_LIST SET TEACHER_ACTIVE=1 WHERE TEACHER_ID=' . $_GET['t']);
}

$xml->handle_request();

?>
<? if (!$_POST['XMLRequest']) { ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
	<head>
		<title>Edit Teachers</title>
		<link rel="stylesheet" type="text/css" href="../shs.css">
		<meta name="vs_targetSchema" content="http://schemas.microsoft.com/intellisense/ie5">
		<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
		<link rel="stylesheet" type="text/css" href="admin.css">
		<style type="text/css"><!--
			span.db { display: inline }
			a.db-teachers { font-weight: bold }
		--></style>
	</head>
	<body>

	<? include "inc-header.php" ?>

		<script type="text/javascript">
		<!--
			function updateteacher(teacher,set)
			{
				url = "/shcp/teachers.php?t=" + teacher + "&set=" + set;

				<? print $xml->make_request('url','POST','content','teacherlist'); ?>
				return false;
			}
	//-->
	</script>
	
	<div id="content">
<? }	
	teacherlist();

	function teacherlist()
	{
		print '<p>Click a teacher\'s name to toggle his/her status.</p>';
	
		print '<table>';
		print '<tr><td style="width: 180px" valign="top">';
	
		print '<div style="font-size: medium; font-weight: bold">Active</div>';
	
		$teachers = mysql_query('SELECT * FROM TEACHER_LIST WHERE TEACHER_ACTIVE=1 ORDER BY TEACHER_NAME');
		while($cteacher = mysql_fetch_array($teachers, MYSQL_ASSOC))
			print '<div><a href="teachers.php?set=0&amp;t=' . $cteacher['TEACHER_ID'] . '" onClick="return updateteacher(' . $cteacher['TEACHER_ID'] . ',0);">' . htmlentities($cteacher['TEACHER_NAME']) . '</a></div>';
		
		print '</td><td style="width: 180px" valign="top">';
		
		print '<div style="font-size: medium; font-weight: bold">Inactive</div>';
	
		$teachers = mysql_query('SELECT * FROM TEACHER_LIST WHERE TEACHER_ACTIVE=0 ORDER BY TEACHER_NAME');
		while($cteacher = mysql_fetch_array($teachers, MYSQL_ASSOC))
			print '<div><a href="teachers.php?set=1&amp;t=' . $cteacher['TEACHER_ID'] . '" onClick="return updateteacher(' . $cteacher['TEACHER_ID'] . ',1);">' . htmlentities($cteacher['TEACHER_NAME']) . '</a></div>';
		
		print '</td></tr>';
		
		print '</table>';
	}
	
	?>

	<? if (!$_POST['XMLRequest']) { ?>
	
	</div>

	<? include '../inc-footer.php'; ?>
</body>
</html>
<? } ?>