<?
include '../db.php';
require 'cpvalidation.php';

if(is_numeric($_GET['del']))
{
	$query = 'DELETE FROM VALIDCLASS_LIST WHERE VALIDCLASS_ID=' . $_GET['del'];
	$result = mysql_query($query) or die("Query failed");
}
else if($_POST['btn'] == 'New')
{	
	if(is_numeric($_POST['per']) && is_numeric($_POST['class']) && is_numeric($_POST['teacher']))
	{
		$result = mysql_query('INSERT INTO VALIDCLASS_LIST (VALIDCLASS_COURSE, VALIDCLASS_TEACHER, VALIDCLASS_PER, VALIDCLASS_TERM) VALUES
			(' . $_POST['class'] . ', ' . $_POST['teacher'] . ', ' . $_POST['per'] . ', \'' . $_POST['term'] . '\')') or die('Insertion failed');
	}
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
	<head>
		<title>Teacher/Per/Class Combinations</title>
		<link rel="stylesheet" type="text/css" href="../shs.css">
		<meta name="vs_targetSchema" content="http://schemas.microsoft.com/intellisense/ie5">
		<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
		<link rel="stylesheet" type="text/css" href="admin.css">
		<style type="text/css"><!--
			span.db { display: inline }
			a.db-classc { font-weight: bold }
		--></style>
		<script type="text/javascript" src="/dom.js"></script>
	</head>
	<body>

<? include "inc-header.php" ?>

<form method="POST" name="fform" action="validclass.php">

<?
print $resp;

if($error == 0)
{
?>
	<table id="selectmenu" cellpadding="1" cellspacing="1" style="position: relative; top: 0px; background-color: #FFFFFF; border: 1px solid #000000; z-index: 2;">
	<tr><td class="header">Per</td><td class="header">Teacher</td><td class="header">Class</td><td class="header">Term</td><td></td></tr>
	<tr>
		<td class="data" valign="top"><select id="fper" name="per"><?
		for($i = 0; $i <= 8; $i++)
		{
			print "<option ";
			if($i == $_POST['per'])
				print "selected ";
			print 'value="' . $i . '">' . $i . '</option>\n';
		}
		print '</select>';
		?></td>
		<td class="data" valign="top"><select id="fteacher" name="teacher"><?
		$cResult = mysql_query("SELECT * FROM TEACHER_LIST WHERE TEACHER_ACTIVE=1 ORDER BY TEACHER_NAME") or die("User query failed");
		while ($l = mysql_fetch_array($cResult, MYSQL_ASSOC)) {
			print "<option ";
			if($l['TEACHER_ID'] == $_POST['teacher'])
				print "selected ";
			print 'value="' . $l['TEACHER_ID'] . '">' . $l['TEACHER_NAME'] . '</option>\n';
		}
		mysql_free_result($cResult);
		print '</select>';
		?></td>
		<td class="data" valign="top"><select id="fclass" name="class"><?
		$cResult = mysql_query("SELECT * FROM CLASS_LIST WHERE CLASS_ACTIVE=1 ORDER BY CLASS_NAME") or die("User query failed");
		while ($l = mysql_fetch_array($cResult, MYSQL_ASSOC)) {
			print "<option ";
			if($l['CLASS_ID'] == $_POST['class'])
				print "selected ";
			print 'value="' . $l['CLASS_ID'] . '">' . $l['CLASS_NAME'] . '</option>\n';
		}
		mysql_free_result($cResult);
		print '</select></td><td><select id="fclass" name="term"><option value="YEAR">Year</option><option value="S1">Sem 1</option><option value="S2">Sem 2</option></select>';
		?></td>
		<td><input type="submit" name="btn" value="New"></td>
	</tr>
	</table>
	
	<script type="text/javascript">
	<!--
	var selectmenu = document.getElementById("selectmenu");
	var realTop = DomUtils.getY(selectmenu);
	var constantBuffer = 5;
	
		EventUtils.addEventListener(window,"scroll",onScroll);
//		EventUtils.addEventListener(window,"mousewheel",onScroll);
//		EventUtils.addEventListener(window,"keydown",onScroll);
		EventUtils.addEventListener(window,"load",onScroll);
		if("addEventListener" in window)
			window.addEventListener("DOMMouseScroll", onScroll, false);
		
		function onScroll(evt)
		{
			var top = (document.documentElement.scrollTop) ? document.documentElement.scrollTop :
				(document.body.scrollTop) ? document.body.scrollTop :
				(window.pageYOffset) ? window.pageYOffset : 0;
				
			top -= realTop - constantBuffer;
			
			if(top > 0)
				selectmenu.style.top = top + "px";
			else
				selectmenu.style.top = "0px";
		}
	-->
	</script>
	
	<p>If there's a class/teacher/period combination listed with YEAR, you don't need to enter it again as S1 or S2.</p>
	<p>Click an entry to delete it.</p>
	<table style="table-layout: fixed">
	<tr><td style="width: 90px" class="header">Teacher</td>
	<? 
		for($i = 0; $i <= 8; $i++)
			print '<td style="width: 120px" class="header">Per ' . $i . '</td>';
	?>
	</tr>
<?
	$tresult = mysql_query("SELECT TEACHER_ID, TEACHER_NAME FROM TEACHER_LIST WHERE TEACHER_ACTIVE = 1 ORDER BY TEACHER_NAME") or die("Teacher query failed");
	while($line = mysql_fetch_array($tresult, MYSQL_ASSOC))
	{
		print '<tr><td valign="top">' . $line['TEACHER_NAME'] . '</td>';
		
		for($i = 0; $i <= 8; $i++)
		{
			$rresult = mysql_query("SELECT VALIDCLASS_TERM, VALIDCLASS_ID, CLASS_NAME
				FROM VALIDCLASS_LIST
					INNER JOIN CLASS_LIST ON VALIDCLASS_COURSE=CLASS_ID
				WHERE VALIDCLASS_TEACHER=" . $line['TEACHER_ID'] . " AND VALIDCLASS_PER=$i
				ORDER BY VALIDCLASS_TERM, CLASS_NAME") or die("Periods query failed");
			
			print '<td valign="top" class="data">';
			
			print '<div align="right" style="font-size: smaller"><span onclick="fform.fteacher.value=' . $line['TEACHER_ID'] . '; fform.fper.value=' . $i . '; fform.fclass.focus();">New...</span></div>';

			while($cclass = mysql_fetch_array($rresult, MYSQL_ASSOC))
				print '<div><a href="validclass.php?del=' . $cclass['VALIDCLASS_ID'] . '">' . $cclass['CLASS_NAME'] . '</a> <span style="font-size: smaller">' . $cclass['VALIDCLASS_TERM'] . '</span></div>';

			print '</td>';
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
