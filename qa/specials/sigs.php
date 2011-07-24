<?
include '../../db.php';

$print = isset($_GET['print']);

if(is_id($_GET['group']))
{
	$rscurgroup = mysql_query('SELECT * FROM QAGROUP_LIST WHERE QAGROUP_ID=' . $_GET['group']);

	if($curgroup = mysql_fetch_array($rscurgroup, MYSQL_ASSOC))
	{

	}
	else
		die();
}
else
	die();

$rsuseritems = mysql_query('SELECT QAQUESTION_AUTOFILLNAME, QARESP_RESP_TEXT
	FROM QA_LIST
	INNER JOIN QAPAGE_LIST ON QAPAGE_QA=QA_ID
	INNER JOIN QAFILL_LIST ON QAFILL_QA=QA_ID
	INNER JOIN QAFILLPAGE_LIST ON QAFILLPAGE_FILL=QAFILL_ID AND QAFILLPAGE_PAGE=QAPAGE_ID
	INNER JOIN QAQUESTION_LIST ON QAQUESTION_PAGE=QAPAGE_ID
	INNER JOIN QARESP_LIST ON QARESP_QUESTION=QAQUESTION_ID AND QARESP_FILLPAGE=QAFILLPAGE_ID
	WHERE QAFILL_USER=' . $userid . ' AND QAPAGE_ID=6');

while($preentered = mysql_fetch_array($rsuseritems, MYSQL_ASSOC))
	$cuser[$preentered['QAQUESTION_AUTOFILLNAME']] = $preentered['QARESP_RESP_TEXT'];

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
	<head>
		<title>Signature Sheet</title>
		<link rel="stylesheet" type="text/css" href="../../shs.css">
		<link rel="stylesheet" type="text/css" href="../qa.css">
		<style type="text/css">
			a.linkh { font-weight: bold }
		</style>
	</head>
	<body <? if($print) print 'onLoad="window.print();"'?>>
		<?
		if(!$print)
			include 'inc-header.php';

		$signpages = mysql_query('SELECT QAPAGE_ID, QAFILL_CHILD, QAFILLPAGE_ID, QAPAGE_SIGPROMPT, QAPAGE_PARENTSIG, QAPAGE_STUDENTSIG, QA_TITLE, QAPAGE_TITLE FROM QAFILLPAGE_LIST
		INNER JOIN QAPAGE_LIST ON QAFILLPAGE_PAGE=QAPAGE_ID
		INNER JOIN QAFILL_LIST ON QAFILLPAGE_FILL=QAFILL_ID
		INNER JOIN QA_LIST ON QAFILL_QA=QA_ID
		WHERE QAFILL_USER=' . $userid . ' AND QAPAGE_SIGPROMPT IS NOT NULL AND QA_GROUP=' . $_GET['group'] . ' ORDER BY QA_TITLE, QAPAGE_ORDER');

		?>
		<h1 class="titlebar"><span style="font-size: large"><a href="../?group=<?= $curgroup['QAGROUP_ID'] ?>"><?= $curgroup['QAGROUP_TITLE'] ?></a>:</span> Signature Sheet</h1>
		<?
		if(!$print)
			include "inc-nav.php";
		print '<br>';
		if(!$print)
	{
			print '<h1>Print, sign, and return this page to the SHS Main Office along with any donations.</h1>';
			print PrintView('sigs.php?group=' . $_GET['group'] . '&print');
	}
		print '<div style="font-size: small">';
		if(mysql_num_rows($signpages) > 0)
		{
			while($sign = mysql_fetch_array($signpages, MYSQL_ASSOC))
			{
				print '<p style="font-weight: bold; font-size: medium">' . htmlentities($sign['QA_TITLE']) . ': ' . htmlentities($sign['QAPAGE_TITLE']) . ' (ID#' . $sign['QAFILLPAGE_ID'] . ')</p>';
				if(!is_null($sign['QAFILL_CHILD']))
					print '<p style="font-weight: bold">' . $cuser['~CHILD' . $sign['QAFILL_CHILD'] . '_FIRST_NAME'] . ' ' . $cuser['~CHILD' . $sign['QAFILL_CHILD'] . '_LAST_NAME'] . ', Grade ' . $cuser['~CHILD' . $sign['QAFILL_CHILD'] . '_GRADE'] . '</p>';
				print $sign['QAPAGE_SIGPROMPT'];
				if($sign['QAPAGE_PARENTSIG'])
					print '<p>Parent Signature: <span style="font-size: larger">x________________________________________</span></p>';
				if($sign['QAPAGE_STUDENTSIG'])
					print '<p>Student Signature: <span style="font-size: larger">x________________________________________</span></p>';
				print '<p>&nbsp;</p>';
			}

			if(!$print)
				print PrintView('sigs.php?group=' . $_GET['group'] . '&print');
		}
		else
		{
			print '<p>None of the forms you filled out require signatures.</p>';
		}
		print '</div>';
		?>
		</form>

	</body>
</html>
