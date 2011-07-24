<?
include '../../db.php';

if($showitem)
	$showpage = true;
	
if(is_numeric($_GET['group']))
	$groupid = $_GET['group'];

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

if($_POST['type'] == 'saveform')
{
	if($_POST['nextpage'] == 'next')
	{
		header('location: http://' . DNAME . $_POST['path']);
	}
	else
		header('location: http://' . DNAME . '/qa/index.php?group=' . $_GET['group']);
}
	
if(!$loggedin)
	forceLogin();

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
	<head>
		<title>Confirm Forms</title>
		<link rel="stylesheet" type="text/css" href="../../shs.css">
		<link rel="stylesheet" type="text/css" href="../qa.css">
		<style type="text/css">
			a.linkh { font-weight: bold }
			div.selpage { padding: 2px; spacing: 3px; border: 1px solid #cccccc; background-color: #ffffff }
			div.nselpage { padding: 2px; spacing: 3px; border: 1px solid #eeeeee; }
			.errortd { font-size: small; color: #660000 }
			.sampletd { font-size: small; color: #666666 }
		</style>
	</head>
	<body <? if(isset($_GET['print'])) print 'onLoad="javascript:window.print()"';?>>
		<?

		if(!isset($_GET['print']))
			include "inc-header.php";
		
		?>
		<h1 class="titlebar"><span style="font-size: large"><a href="./?group=<?= $curgroup['QAGROUP_ID'] ?>"><?= $curgroup['QAGROUP_TITLE'] ?></a>:</span> Confirm Submissions</h1>
		<?
		if(!isset($_GET['print']))
			$group = $_GET['group'];
			include "inc-nav.php";

		$rspage = mysql_query('SELECT * FROM QAPAGE_LIST
			INNER JOIN QA_LIST ON QAPAGE_QA=QA_ID
			INNER JOIN QAGROUP_LIST ON QA_GROUP=QAGROUP_ID
			INNER JOIN QAFILL_LIST ON QA_ID=QAFILL_QA AND QAFILL_USER=' . $userid . '
			INNER JOIN QAFILLPAGE_LIST ON QAFILLPAGE_FILL=QAFILL_ID AND QAFILLPAGE_PAGE=QAPAGE_ID
			WHERE QAGROUP_ID=' . $groupid);

		if(mysql_num_rows($rspage) == 0)
		{
			print '<p>You have submitted no forms.</p>';
		}
		else
		{
			if(!isset($_GET['print']))
			{
				print '<p style="font-size: medium">Here are all the forms you\'ve filled out. If some items are wrong, you can click "Edit This Form" to correct the problem. You may print or save this page for your records. Make sure that EACH form you filled out is represented on this page! Only information displayed below will be submitted.</p>';
			}

			while($page = mysql_fetch_array($rspage, MYSQL_ASSOC))
			{
				print '<h1 style="font-weight: normal; font-size: large; border-bottom: 2px solid black; margin-bottom: 0px">' . $page['QA_TITLE'] . ': ' . $page['QAPAGE_TITLE'];
				print '</h1>';
				if($page['QA_OPEN'] == 1 && !isset($_GET['print']))
					print '<div><a style="font-size: medium" href="../page.php?id=' . $page['QAPAGE_ID'] . '&amp;fill=' . $page['QAFILL_ID'] . '">Edit This Form</a></div>';

				$rsquestions = mysql_query('SELECT * FROM QAQUESTION_LIST
					LEFT JOIN QAFORMAT_LIST ON QAQUESTION_FORMAT=QAFORMAT_ID
					LEFT JOIN QAFILLPAGE_LIST ON QAFILLPAGE_FILL=' . $page['QAFILL_ID'] . ' AND QAFILLPAGE_PAGE=QAQUESTION_PAGE
					LEFT JOIN QARESP_LIST ON QARESP_FILLPAGE=QAFILLPAGE_ID AND QARESP_QUESTION=QAQUESTION_ID
					WHERE QAQUESTION_PAGE=' . $page['QAPAGE_ID'] . ' ORDER BY QAQUESTION_ORDER');
				while($ques = mysql_fetch_array($rsquestions, MYSQL_ASSOC))
				{
					if($ques['QAQUESTION_TYPE'] == 'Select' || $ques['QAQUESTION_TYPE'] == 'Radio' || $ques['QAQUESTION_TYPE'] == 'Check')
						$fvalues[$ques['QAQUESTION_ID']] = $ques['QARESP_RESP_INT'];
					else if($ques['QAQUESTION_TYPE'] == 'Text')
						$fvalues[$ques['QAQUESTION_ID']] = $ques['QARESP_RESP_TEXT'];
					else if($ques['QAQUESTION_TYPE'] == 'Longtext')
						$fvalues[$ques['QAQUESTION_ID']] = $ques['QARESP_RESP_LONGTEXT'];
				}
		
				$rsquestions = mysql_query('SELECT * FROM QAQUESTION_LIST LEFT JOIN QAFORMAT_LIST ON QAQUESTION_FORMAT=QAFORMAT_ID WHERE QAQUESTION_PAGE=' . $page['QAPAGE_ID'] . ' ORDER BY QAQUESTION_ORDER');
				if(mysql_num_rows($rsquestions) > 0)
				{
					while($ques = mysql_fetch_array($rsquestions, MYSQL_ASSOC))
					{
						$defaultvalue = $fvalues[$ques['QAQUESTION_ID']];

						if($ques['QAQUESTION_HEADING'] != '')
							print '<h2>' . $ques['QAQUESTION_HEADING'] . '</h2>';

						print '<table cellpadding="1" cellspacing="0" style="font-size: small" width="100%">';

						if($errors[$ques['QAQUESTION_ID']])
							print '<tr><td></td><td class="errortd">' . htmlentities($errors[$ques['QAQUESTION_ID']]) . '</td></tr>';

						if($ques['QAQUESTION_TYPE'] == 'Check')
						{
							print '<tr>';
							print '<td style="width: 2em; font-weight: bold">';
							if($defaultvalue)
								print '&nbsp;<img align="absmiddle" src="../imgs/finished.gif">';
							else
								print '&nbsp;<img align="absmiddle" src="../imgs/undone.gif">';
							print '</td>';
							print '<td style="';
							print '">' . $ques['QAQUESTION_PROMPT'] . '</td>';
							print '</tr>';
						}
						else
						{
							print '<tr>';
							print '<td style="width: 12em">' . $ques['QAQUESTION_PROMPT'] . '</td>';
							print '<td style="font-weight: bold">';

							if($ques['QAQUESTION_TYPE'] == 'Text' || $ques['QAQUESTION_TYPE'] == 'Longtext')
								print nl2br(htmlentities($defaultvalue));
							else if($ques['QAQUESTION_TYPE'] == 'Radio' || $ques['QAQUESTION_TYPE'] == 'Select')
							{
								$rsoptions = mysql_query('SELECT * FROM QAMC_LIST WHERE QAMC_QUESTION=' . $ques['QAQUESTION_ID'] . ' ORDER BY QAMC_ORDER');
								while($option = mysql_fetch_array($rsoptions, MYSQL_ASSOC))
								{
									if($defaultvalue == $option['QAMC_ID'])
										print htmlentities($option['QAMC_TEXT']);
								}
							}
							print '</td>';
							print '</tr>';
						}
						print '</table>';
						
						if($ques['QAQUESTION_DESC'] != '')
							print '<div style="padding-left: 3em; padding-top: 3px; padding-right: 3px; padding-bottom: 3px; font-size: small;">' . $ques['QAQUESTION_DESC'] . '</div>';
					}
				}
			
			}
			?>

			<? if(!isset($_GET['print'])){
			$next = $qary[$curposition + 1];
			print '<h2 class="grayheading">Confirm</h2>';
			print '<form action="confirm.php?group=' . $groupid . '" method="POST">';
			print '<p><input type="hidden" name="pageno" value="' . $page['QAPAGE_ID'] . '"><input type="hidden" name="type" value="saveform"><input type="hidden" name="path" value="' . $next['path'] . $next['name'] . $next['query'] . '">';
			print '<p><input type="hidden" name="pageno" value="50"><input type="hidden" name="type" value="saveform"><input type="submit" name="btn" value="Confirm"> and then <select name="nextpage">';
			if(!is_null($next))
				print '<option value="next">go to next page</option>';
			print '<option value="close">quit</option></select></p>';
			?>
			<p style="font-size: large">All done checking? You can <a href="confirm.php?group=<?= $groupid ?>&print" target="_new">print</a></p>
			<? include '../../inc-footer.php'; ?>
			<? } ?>

			<?
		}
		?>
		</td></tr></table>	
	</body>
</html>
