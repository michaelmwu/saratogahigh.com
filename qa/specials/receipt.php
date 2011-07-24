<?
include '../../db.php';

if(is_numeric($_GET['conf']))
{
	mysql_query("UPDATE QAFILLPAGE_LIST SET QAFILLPAGE_SIGCONFIRM=1 WHERE QAFILLPAGE_ID=" . $_GET['conf']);
	header('Location: receipt.php?id=' . $_GET['conf']);
}

if($_POST['type'] == 'saveform')
{
	if($_POST['nextpage'] == 'next')
	{
		header('location: http://' . DNAME . $_POST['path']);
	}
	else
		header('location: http://' . DNAME . '/qa/index.php?group=' . $_GET['group']);
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
	<head>
		<title>Q&amp;A Service Confirm</title>
		<link rel="stylesheet" type="text/css" href="../../shs.css">
		<link rel="stylesheet" type="text/css" href="../qa.css">
		<style type="text/css">
			a.linkh { font-weight: bold }
		</style>
	</head>
	<body onload="document.idForm.elements[0].focus();">

		<? include "inc-header.php";

		$group = $_GET['group'];
		if(!is_numeric($group)) die();
		include "inc-nav.php";

		if($loggedin)
		{
			if($isprog || $userid==3 || $userid == 1996)
			{
if(is_null($_GET['id']))
	print '<p><form name="idForm" id="idForm" action="receipt.php" method="get">Enter Form ID: <input type="text" name="id"><input type="submit" value="Submit"></form></p>';

if(is_numeric($_GET['id']))
{
$rsinfo = mysql_query("SELECT QAFILL_ID, QAFILLPAGE_ID, QAFILL_USER, P.USER_FULLNAME AS PARENT, C.USER_FULLNAME AS CHILD, QAFILLPAGE_SIGCONFIRM AS CONFIRMED
	 FROM `QAFILL_LIST` INNER JOIN QAFILLPAGE_LIST ON QAFILLPAGE_FILL = QAFILL_ID
	 INNER JOIN USER_LIST AS P ON P.USER_ID = QAFILL_USER
	 INNER JOIN PARENTSTUDENT_LIST ON PARENTSTUDENT_PARENT = QAFILL_USER
	 INNER JOIN USER_LIST AS C ON C.USER_ID = PARENTSTUDENT_STUDENT
	 WHERE QAFILL_USER = " . $userid);
//	 WHERE QAFILLPAGE_ID = " . $_GET['id']);
	 // ignore QAFILL_ID

print '<p><table><tr><td class="header">FORM ID</td><td class="header">PARENT ID</td><td class="header">PARENT NAME</td><td class="header">CHILD NAME</td><td class="header">CONFIRMED</td></tr>';

while($info = mysql_fetch_array($rsinfo, MYSQL_ASSOC))
{
print '<tr><td class="data">' . $info['QAFILLPAGE_ID'] . '</td><td class="data">' . $info['QAFILL_USER'] . '<td class="data">' . $info['PARENT'] . '</td><td class="data">' . $info['CHILD'] . '</td><td class="data">';
	if($info['CONFIRMED'])
		print 'Yes';
	else
		print 'No. <a href="receipt.php?conf=' . $info['QAFILLPAGE_ID'] . '">Confirm</a>';
print '</td></tr>';
}
print '</table></p>
		<p><form name="idForm" id="idForm" action="receipt.php" method="get">Enter Form ID: <input type="text" name="id" onload="this.focus"><input type="submit" value="Submit"></form></p>';
}

}

}
	print '<h2 class="grayheading">Confirm</h2>';
	print '<form action="receipt.php?group=' . $_GET['group'] . '" method="POST">';
	print '<p><input type="hidden" name="pageno" value="' . $page['QAPAGE_ID'] . '"><input type="hidden" name="type" value="saveform"><input type="hidden" name="path" value="' . $next['path'] . $next['name'] . $next['query'] . '">';
	print '<p><input type="hidden" name="pageno" value="50"><input type="hidden" name="type" value="saveform"><input type="submit" name="btn" value="Confirm"> and then <select name="nextpage">';
	if(!is_null($next))
		print '<option value="next">go to next page</option>';
	print '<option value="close">quit</option></select></p>';
	?>
	<? include '../../inc-footer.php'; ?>
</body>
</html>
