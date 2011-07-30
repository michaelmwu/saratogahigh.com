<? include "db.php"; ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
	<head>
		<title>Site Statistics</title>
		<link rel="stylesheet" type="text/css" href="../shs.css">
		<meta name="vs_targetSchema" content="http://schemas.microsoft.com/intellisense/ie5">
		<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
	</head>
	<body>
	
<? include "inc-header.php" ?>

<h2>Registration Statistics</h2>
<table cellpadding="2" cellspacing="2">
<tr><td></td><td></td><td>Verified Users</td></tr>
<?

$maxG = 0;
$result = mysql_query("SELECT USER_GR, COUNT(*) AS C FROM USER_LIST GROUP BY USER_GR ORDER BY USER_GR DESC") or die("Grade breakdown query failed");
while ($l = mysql_fetch_array($result, MYSQL_ASSOC))
if($l["C"] > $maxG)
	$maxG = $l["C"];

mysql_free_result($result);
$result = mysql_query("SELECT USER_GR, COUNT(*) AS C FROM USER_LIST WHERE USER_VALIDATED=1 GROUP BY USER_GR ORDER BY USER_GR DESC") or die("Grade breakdown query failed");
$countU = 0;
$MAX_WIDTH = 450;
while($l = mysql_fetch_array($result, MYSQL_ASSOC))
{
	$result2 = mysql_query("SELECT COUNT(*) AS C FROM USER_LIST WHERE USER_GR=" . $l['USER_GR']) or die("Grade breakdown query failed");

	if($j = mysql_fetch_array($result2, MYSQL_ASSOC))
	{
		$x = floor($l['C'] / $maxG * $MAX_WIDTH);
		$y = floor(($j['C'] - $l['C']) / $maxG * $MAX_WIDTH);
		print "<tr>";
		if($l['C'] > 0)
		{
			print "<td>" . GradePrint($l['USER_GR']) . "</td><td>" . ceil($l['C'] / $j['C'] * 100) . "%</td><td><table cellpadding=\"1\" cellspacing=\"0\"><tr><td style=\"color: #ffffff; background-color: #000080; width: " . $x . "px\">{$l['C']}</td>";
		
		}
		if($l['C'] < $j['C'])
			print "<td align=\"right\" style=\"background-color: #dddddd; width: " . $y . "px\"></td><td style=\"color: #666666\">/{$j['C']}</td>";
		print "</tr></table></td></tr>";
		$countU += $l['C'];
	}
}
mysql_free_result($result);

?>
<tr style="font-weight: bold"><td>Total</td><td></td><td><?= $countU ?></td></tr>
</table>

<?

$result = mysql_query('SELECT COUNT(*) AS C FROM USER_LIST WHERE USER_UNAME!="" AND USER_UNAME IS NOT NULL AND ' . C_SCHOOLYEAR . ' <= USER_GR AND USER_GR <= ' . (C_SCHOOLYEAR + 3)) or die('User-count query failed');
$l = mysql_fetch_array($result, MYSQL_ASSOC);
$countU = $l["C"];
mysql_free_result($result);

$result = mysql_query('SELECT COUNT(*) AS C FROM USER_LIST WHERE ' . C_SCHOOLYEAR . ' <= USER_GR AND USER_GR <= ' . (C_SCHOOLYEAR + 3)) or die('User-count query failed');
$l = mysql_fetch_array($result, MYSQL_ASSOC);
$countSt = $l["C"];
mysql_free_result($result);

$result = mysql_query('SELECT COUNT(DISTINCT SCHED_CLASS) AS C FROM SCHED_LIST WHERE SCHED_YEAR=' . C_SCHOOLYEAR) or die('Course-count query failed');
$l = mysql_fetch_array($result, MYSQL_ASSOC);
$countC = $l["C"];
mysql_free_result($result);

$result = mysql_query('SELECT COUNT(DISTINCT SCHEDSPORT_SPORT) AS C FROM SCHEDSPORT_LIST WHERE SCHEDSPORT_YEAR=' . C_SCHOOLYEAR) or die('Sports query failed');
$l = mysql_fetch_array($result, MYSQL_ASSOC);
$countSp = $l["C"];
mysql_free_result($result);

$result = mysql_query('SELECT COUNT(DISTINCT SCHED_TEACHER) AS C FROM SCHED_LIST WHERE SCHED_YEAR=' . C_SCHOOLYEAR) or die('Teacher-count query failed');
$l = mysql_fetch_array($result, MYSQL_ASSOC);
$countT = $l["C"];
mysql_free_result($result);

$result = mysql_query('SELECT COUNT(*) AS C FROM SCHED_LIST WHERE SCHED_YEAR=' . C_SCHOOLYEAR) or die('Sched-count query failed');
$l = mysql_fetch_array($result, MYSQL_ASSOC);
$countS = $l["C"];
mysql_free_result($result);

?>

<? if(SITE_ACTIVE) { ?>
<ul class="flat">
<li>There are currently <span style="font-weight: bold"><?= $countU ?></span> students registered, taking <span style="font-weight: bold"><?= $countC ?></span> courses from <span style="font-weight: bold"><?= $countT ?></span> teachers, and <span style="font-weight: bold"><?= $countSp ?></span> sports.</li>
<li><span style="font-weight: bold"><?= floor($countU/$countSt*100) ?>%</span> of the school's <span style="font-weight: bold"><?= $countSt ?></span> students have registered.</li>
<li>The total number of student/class entries in the database is <span style="font-weight: bold"><?= $countS ?></span>.</li>
<li>View <a href="directory/aggr-class.php">courses</a> | <a href="directory/aggr-teacher.php">teachers</a> with most students</li>
</ul>
<? } ?>
		
	<? include 'inc-footer.php'; ?></body>
</html>
