<?
include '../../db.php';

$isauthor = false;
$showitem = false;

if(is_numeric($_GET['id']) && $loggedin)
{
	$rsqagroup = mysql_query('SELECT * FROM QAGROUP_LIST WHERE QAGROUP_ID=' . $_GET['id']);
	
	if($group = mysql_fetch_array($rsqagroup, MYSQL_ASSOC))
	{
		$showitem = true;

		$rsauthor = mysql_query('SELECT * FROM QAAUTHOR_LIST WHERE QAAUTHOR_QAGROUP=' . $group['QAGROUP_ID'] . ' AND QAAUTHOR_USER=' . $userid);
		if($author = mysql_fetch_array($rsauthor, MYSQL_ASSOC))
		{
			$isauthor = true;
		}
	}
	if($isprog && isset($_POST['newauthor']))
	{
		$authorid = $_POST['author'];
		$query = mysql_query("SELECT * FROM QAAUTHOR_LIST WHERE QAAUTHOR_QAGROUP=" . $_GET['id'] . " AND QAAUTHOR_USER=$authorid") or die("Could not select." . mysql_error());;
		if(mysql_fetch_array($query, MYSQL_ASSOC))
		{
			$errorm = 'That user is already an author.';
		}
		else
		{
			mysql_query("INSERT INTO QAAUTHOR_LIST (QAAUTHOR_QAGROUP,QAAUTHOR_USER) VALUES (" . $_GET['id'] . ",$authorid)") or die("Could not insert." . mysql_error());
			$errorm = "User $authorid added as author.";
		}
	}
}
else if($loggedin)
{
	
}
else
{
	forceLogin();
}

if(!$isauthor)
	die();

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
	<head>
		<title><? print htmlentities($group['QAGROUP_TITLE']); ?>: Administrative Options</title>
		<link rel="stylesheet" type="text/css" href="../../shs.css">
		<link rel="stylesheet" type="text/css" href="../qa.css">
		<style type="text/css">
			a.lnkh { font-weight: bold }
			div.selpage { padding: 2px; spacing: 3px; border: 1px solid #cccccc; background-color: #ffffff }
			div.nselpage { padding: 2px; spacing: 3px; border: 1px solid #eeeeee; }
		</style>
	</head>
	<body onLoad="sframe = document.getElementById('searchid'); sframe.style.display='none';">
		<? include "inc-header.php"; ?>
		<? if($showitem) { ?>
		<table cellpadding="5" cellspacing="0" border="0" style="text-align: left; width: 100%">
		<tr>
		<td style="vertical-align: top; background-color: #ffffff" class="groupdesc">
		<h1 class="titlebar" style="margin-bottom: 0.5em"><span style="float: right; font-size: medium; padding: 2px"><a href="../?group=<?= $group['QAGROUP_ID'] ?>">View</a> | Administer</span><?= htmlentities($group['QAGROUP_TITLE']) ?></h1>
		<?
		$rsqas = mysql_query('SELECT QA_LIST.*, COUNT(QAFILL_ID) AS C FROM QA_LIST LEFT JOIN QAFILL_LIST ON QAFILL_QA=QA_ID WHERE QA_GROUP=' . $group['QAGROUP_ID'] . ' GROUP BY QA_ID ORDER BY QA_ID');
		
		$numqas = mysql_num_rows($rsqas);
		
		$showscores = false;
		if(strlen($errorm) > 0)
			print $errorm;
		print '<h2 class="grayheading">' . $numqas . ' form' . ($numqas != 1 ? 's' : '') . '</h2>';
		print '<div class="hcontent"><table cellpadding="3" cellspacing="0">';
		while($qa = mysql_fetch_array($rsqas, MYSQL_ASSOC))
		{
			$i++;
			print '<tr><td style="font-weight: bold">' . $i . '</td><td>' . $qa['QA_TYPE'] . '</td><td><a href="qa.php?id=' . $qa['QA_ID'] . '&amp;sortstr=' . $_GET['sortstr'] . '">' . $qa['QA_TITLE'] . '</a></td><td>';
			print $qa['QA_OPEN'] > 0 ? ($qa['C'] . ' response' . ($qa['C'] != 1 ? 's' : '')  . ($qa['QA_OPEN'] == 2 ? '; closed' : '')) : 'Under construction';
			print '</td></tr>';
			if($qa['QA_ISGRADED'])
				$showscores = true;
		}
		print '<tr><td></td><td></td><td><a href="newqa.php?id=' . $_GET['id'] . '">Add New Form...</a></td><td></td></tr>';
		print '</table></div>';
		
		if($_GET['sortstr'] == 'score')
			$orderstr = 'S DESC, USER_LN, USER_FN, USER_ID';
		else
			$orderstr = 'USER_LN, USER_FN, USER_ID';
	
		$rsfills = mysql_query('SELECT QA_TYPE, USER_FULLNAME, USER_ID, SUM(QARESP_PTS) AS S
		FROM QA_LIST
			INNER JOIN QAFILL_LIST ON QAFILL_QA=QA_ID
			INNER JOIN USER_LIST ON QAFILL_USER=USER_ID
			INNER JOIN QAFILLPAGE_LIST ON QAFILLPAGE_FILL=QAFILL_ID
			INNER JOIN QARESP_LIST ON QARESP_FILLPAGE=QAFILLPAGE_ID
		WHERE QA_GROUP=' . $group['QAGROUP_ID'] . '
		GROUP BY USER_ID
		ORDER BY ' . $orderstr);
		
		if(mysql_num_rows($rsfills) > 0)
		{
			print '<h2 class="grayheading">' . mysql_num_rows($rsfills) . ' respondent';
			if (mysql_num_rows($rsfills) != 1)
				print 's';
			print '</h2><div class="hcontent">';

			if($showscores)
			{
				if($_GET['sortstr'] == 'score')
					print '<p><span style="">Sort by</span>: <a href="group.php?id=' . $group['QAGROUP_ID'] . '&amp;sortstr=name">Name</a> | <span style="font-weight: bold">Score</span></p>';
				else
					print '<p><span style="">Sort by</span>: <span style="font-weight: bold">Name</span> | <a href="group.php?id=' . $group['QAGROUP_ID'] . '&amp;sortstr=score">Score</a></p>';
			}

			print '<table cellpadding="2" cellspacing="0" style="table-layout: fixed; font-size: small">';
			
			print '<tr style="font-weight: bold; background-color: #dddddd"><td style="width: 12em">Form Number</td>';
			for($k = 1; $k <= $numqas; $k++)
			{
				print '<td style="text-align: right; width: 2em">' . $k . '</td>';
			}
			if($showscores)
				print '<td style="text-align: right; width: 3em">Total</td>';

			print '</tr>';

			if($showscores)
			{
				print '<tr style="color: #800000; background-color: #eeeeee"><td>Maximum</td>';
				$rsqapts = mysql_query('SELECT QA_ISGRADED, SUM(QAQUESTION_PTS) AS S FROM QAQUESTION_LIST INNER JOIN QAPAGE_LIST ON QAPAGE_ID=QAQUESTION_PAGE INNER JOIN QA_LIST ON QAPAGE_QA=QA_ID WHERE QA_GROUP=' . $group['QAGROUP_ID'] . ' GROUP BY QA_ID ORDER BY QA_ID');
				$s = 0;
				while($qapts = mysql_fetch_array($rsqapts, MYSQL_ASSOC))
				{
					if($qapts['QA_ISGRADED'])
						print '<td style="text-align: right">' . $qapts['S'] . '</td>';
					else
						print '<td style="text-align: right"></td>';
						
					$s += $qapts['S'];
				}
				print '<td style="text-align: right; font-weight: bold">' . $s . '</td></tr>';
			}
			
			while($fill = mysql_fetch_array($rsfills, MYSQL_ASSOC))
			{
				print '<tr><td>' . $fill['USER_FULLNAME'] . '</td>';
				
				$rspages = mysql_query('SELECT QA_ISGRADED, QA_ID, SUM(QARESP_PTS) AS S, COUNT(DISTINCT QAFILL_ID) AS C
					FROM QA_LIST
						INNER JOIN QAPAGE_LIST ON QAPAGE_QA=QA_ID
						LEFT JOIN QAFILL_LIST ON QAFILL_QA=QA_ID AND QAFILL_USER=' . $fill['USER_ID'] . '
						LEFT JOIN QAFILLPAGE_LIST ON QAFILLPAGE_PAGE=QAPAGE_ID AND QAFILLPAGE_FILL=QAFILL_ID
						LEFT JOIN QARESP_LIST ON QARESP_FILLPAGE=QAFILLPAGE_ID
					WHERE QA_GROUP=' . $group['QAGROUP_ID'] . '
					GROUP BY QA_ID');
					
				$multifill = false;
				$ptotal = 0;

				while($page = mysql_fetch_array($rspages, MYSQL_ASSOC))
				{
					print '<td style="text-align: right; ';
					if($page['C'] > 0)
						print 'color: #0000a0';
					else
						print 'color: #aaaaaa';
					print '">';
					if($page['C'] == 1)
					{
						if($page['QA_ISGRADED'])
						{
							print $page['S'];
							$ptotal += $page['S'];
						}
						else
							print '<img align="absmiddle" src="../imgs/finished.gif">';
					}
					else if($page['C'] > 1)
					{
						$multifill = true;
						print $page['C'] . 'x';
					}

					print '</td>';
				}
				if($showscores)
					print '<td style="font-weight: bold; text-align: right">' . $fill['S'] . '</td>';
				print '</tr>';					
			}
			print '</table></div>';
		}
		if($isprog){?>

<h2 class="grayheading">Add Author</h2>
<form style="padding: 2px" name="addauthor" action="group.php?id=<?=$group['QAGROUP_ID']?>" method="POST">
<span style="font-weight: bold">ID</span> <input type="text" name="author" onClick="loadSearchID();" size="6"> <input type="hidden" value="newauthor"><input type="submit" name="newauthor" value="New">
</form>
<iframe name="searchid" id="searchid" style="width: 600px; height: 400px; vertical-align: top; horizontal-align: left;">
Your browser does not support iframes.
</iframe>
<script type="text/javascript">
<!--
function loadSearchID()
{
	sframe = document.getElementById('searchid');
	sframe.src='/directory/search-id.php?form=addauthor&formelement=author';
	sframe.style.display='block';
}
// -->
</script>

		<? } ?>
		</td>
		<td style="vertical-align: top" class="rightcol"></td>
		</tr></table>
		<? include '../../inc-footer.php'; ?>
		<? } ?>
	</body>
</html>
