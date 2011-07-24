<?
include '../../db.php';

if($loggedin)
{
	
}
else
{
	forceLogin();
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
    <title>Q&amp;A Editor</title>
    <link rel="stylesheet" href="/shs.css" type="text/css">
	<link rel="stylesheet" href="../qa.css" type="text/css">	
</head>
<body>
<? include 'inc-header.php'; ?>

<? 

if($showgroup)
{
	print '<h1 class="titlebar"><span style="font-size: large"><a href="./">Q&amp;A Editor</a>:</span> ' . $cgroup['QAGROUP_TITLE'] . '</h1>';
}
else
{
	print '<h1 class="titlebar">Q&amp;A Editor</h1>';

	$rsgroups = mysql_query('SELECT QAGROUP_TITLE, QAGROUP_ID
		FROM QAGROUP_LIST
		INNER JOIN QAAUTHOR_LIST ON QAAUTHOR_QAGROUP=QAGROUP_ID
		WHERE QAAUTHOR_USER=' . $userid);
	
	if(mysql_num_rows($rsgroups) > 0)
	{
		print '<p style="font-size: medium">Select a group to modify it.</p>';
		
		while($group = mysql_fetch_array($rsgroups, MYSQL_ASSOC))
		{
			print '<p style="font-size: medium"><a href="group.php?id=' . $group['QAGROUP_ID'] . '">' . $group['QAGROUP_TITLE'] . '</a></p>';
		}

		if($isprog)
			print '<p style="font-size: small"><a href="newgroup.php">Create a New Group</a></p>';
	}
	else
	{
		print '<p>You aren\'t the administrator of any Q&amp;A groups.</p>';
	}
}

?>
<? include '../../inc-footer.php'; ?>
</body>
</html>
