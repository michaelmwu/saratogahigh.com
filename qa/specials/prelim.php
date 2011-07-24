<?
include '../../db.php';

$qa_id = 3;
$_GET['id'] = 6;

$rsfills = mysql_query('SELECT * FROM QAFILL_LIST WHERE QAFILL_USER='. $userid . ' AND QAFILL_QA=' . $qa_id);
if($fills = mysql_fetch_array($rsfills, MYSQL_ASSOC))
{
	$_GET['fill'] = $fills['QAFILL_ID'];
}
else
{
	mysql_query('INSERT INTO QAFILL_LIST (QAFILL_USER, QAFILL_QA, QAFILL_START, QAFILL_FINISH) VALUES ('. $userid . ', '. $qa_id . ', "' . date(TIME_FORMAT_SQL, CURRENT_TIME) . '", Null)');
	$_GET['fill'] = mysql_insert_id();
}

if(is_id($_GET['rg']))
{
	$rscurgroup = mysql_query('SELECT * FROM QAGROUP_LIST WHERE QAGROUP_ID=' . $_GET['rg']);

	if($curgroup = mysql_fetch_array($rscurgroup, MYSQL_ASSOC))
	{

	}
	else
		die();
}
else
	die();

if(is_numeric($_GET['id']) && is_numeric($_GET['fill']) && $loggedin)
{
	/*
	if(is_numeric($_GET['delpage']))
	{
		$rsdelpage = mysql_query('SELECT * FROM QAPAGE_LIST
			INNER JOIN QA_LIST ON QAPAGE_QA=QA_ID
			INNER JOIN QAFILL_LIST ON QA_ID=QAFILL_QA AND QAFILL_USER=' . $userid . '
			INNER JOIN QAFILLPAGE_LIST ON QAFILLPAGE_FILL=QAFILL_ID AND QAFILLPAGE_PAGE=QAPAGE_ID
			WHERE QAFILLPAGE_ID=' . $_GET['delpage'] . ' AND QAFILL_ID=' . $_GET['fill'] . ' AND QAPAGE_ID=' . $_GET['id']);
		
		if($delpage = mysql_fetch_array($rsdelpage, MYSQL_ASSOC))
		{
			mysql_query('DELETE FROM QAFILLPAGE_LIST WHERE QAFILLPAGE_ID=' . $_GET['delpage']);
			mysql_query('DELETE FROM QARESP_LIST WHERE QARESP_FILLPAGE=' . $_GET['delpage']);
		}
	}
	*/

	$rspage = mysql_query('SELECT * FROM QAPAGE_LIST
		INNER JOIN QA_LIST ON QAPAGE_QA=QA_ID
		INNER JOIN QAFILL_LIST ON QA_ID=QAFILL_QA AND QAFILL_USER=' . $userid . '
		LEFT JOIN QAFILLPAGE_LIST ON QAFILLPAGE_FILL=QAFILL_ID AND QAFILLPAGE_PAGE=QAPAGE_ID
		WHERE QAFILL_ID=' . $_GET['fill'] . ' AND QAPAGE_ID=' . $_GET['id']);
	if($page = mysql_fetch_array($rspage, MYSQL_ASSOC))
	{
		$showitem = true;
		
		// Save form data
		if($_POST['type'] == 'saveform')
		{
			$numerrors = 0;
		
			$rsquestions = mysql_query('SELECT * FROM QAQUESTION_LIST LEFT JOIN QAFORMAT_LIST ON QAQUESTION_FORMAT=QAFORMAT_ID WHERE QAQUESTION_PAGE=' . $page['QAPAGE_ID'] . ' ORDER BY QAQUESTION_ORDER');
			while($ques = mysql_fetch_array($rsquestions, MYSQL_ASSOC))
			{
				if($_POST['q' . $ques['QAQUESTION_ID']] == '')
				{
					if($ques['QAQUESTION_REQD'])
					{
						$errors[$ques['QAQUESTION_ID']] = 'This question is required.';
						$numerrors++;
					}
				}
				else if(!is_null($ques['QAFORMAT_REGEX']))
				{
					if(!ereg($ques['QAFORMAT_REGEX'], $_POST['q' . $ques['QAQUESTION_ID']]))
					{
						$errors[$ques['QAQUESTION_ID']] = $ques['QAFORMAT_ERR'];
						$numerrors++;
					}
				}
				$fvalues[$ques['QAQUESTION_ID']] = stripslashes($_POST['q' . $ques['QAQUESTION_ID']]);
			}
			
			// TODO: check data length

			if($numerrors == 0)
			{
				$rsquestions = mysql_query('SELECT * FROM QAQUESTION_LIST LEFT JOIN QAFORMAT_LIST ON QAQUESTION_FORMAT=QAFORMAT_ID WHERE QAQUESTION_PAGE=' . $page['QAPAGE_ID'] . ' ORDER BY QAQUESTION_ORDER');

				// insert new records
				if(is_null($page['QAFILLPAGE_ID']))
				{
					mysql_query('INSERT INTO QAFILLPAGE_LIST VALUES (\'\', ' . $_GET['fill'] . ', ' . $_GET['id'] . ', 1, 0, "' . date(TIME_FORMAT_SQL, CURRENT_TIME) . '", "' . date(TIME_FORMAT_SQL, CURRENT_TIME) . '")') or die('Insert failed.');
					$newqafillpage = mysql_insert_id();
									
					while($ques = mysql_fetch_array($rsquestions, MYSQL_ASSOC))
					{
						if($ques['QAQUESTION_TYPE'] == 'Select' || $ques['QAQUESTION_TYPE'] == 'Radio' || $ques['QAQUESTION_TYPE'] == 'Check')
							mysql_query('INSERT INTO QARESP_LIST (QARESP_FILLPAGE, QARESP_QUESTION, QARESP_RESP_INT) VALUES (' . $newqafillpage . ', ' . $ques['QAQUESTION_ID'] . ', "' . $_POST['q' . $ques['QAQUESTION_ID']] . '")');
						else if($ques['QAQUESTION_TYPE'] == 'Text')
							mysql_query('INSERT INTO QARESP_LIST (QARESP_FILLPAGE, QARESP_QUESTION, QARESP_RESP_TEXT) VALUES (' . $newqafillpage . ', ' . $ques['QAQUESTION_ID'] . ', "' . $_POST['q' . $ques['QAQUESTION_ID']] . '")');	
						else if($ques['QAQUESTION_TYPE'] == 'Longtext')
							mysql_query('INSERT INTO QARESP_LIST (QARESP_FILLPAGE, QARESP_QUESTION, QARESP_RESP_LONGTEXT) VALUES (' . $newqafillpage . ', ' . $ques['QAQUESTION_ID'] . ', "' . $_POST['q' . $ques['QAQUESTION_ID']] . '")');
					}
				}
				else
				{
					mysql_query('UPDATE QAFILLPAGE_LIST SET QAFILLPAGE_FINISH="' . date(TIME_FORMAT_SQL, CURRENT_TIME) . '" WHERE QAFILLPAGE_ID=' . $page['QAFILLPAGE_ID']);				
				
					while($ques = mysql_fetch_array($rsquestions, MYSQL_ASSOC))
					{
						if($ques['QAQUESTION_TYPE'] == 'Select' || $ques['QAQUESTION_TYPE'] == 'Radio' || $ques['QAQUESTION_TYPE'] == 'Check')
							mysql_query('UPDATE QARESP_LIST SET QARESP_RESP_INT="' . $_POST['q' . $ques['QAQUESTION_ID']] . '" WHERE QARESP_FILLPAGE=' . $page['QAFILLPAGE_ID'] . ' AND QARESP_QUESTION=' . $ques['QAQUESTION_ID'] . ' LIMIT 1');
						else if($ques['QAQUESTION_TYPE'] == 'Text')
							mysql_query('UPDATE QARESP_LIST SET QARESP_RESP_TEXT="' . $_POST['q' . $ques['QAQUESTION_ID']] . '" WHERE QARESP_FILLPAGE=' . $page['QAFILLPAGE_ID'] . ' AND QARESP_QUESTION=' . $ques['QAQUESTION_ID'] . ' LIMIT 1');
						else if($ques['QAQUESTION_TYPE'] == 'Longtext')
							mysql_query('UPDATE QARESP_LIST SET QARESP_RESP_LONGTEXT="' . $_POST['q' . $ques['QAQUESTION_ID']] . '" WHERE QARESP_FILLPAGE=' . $page['QAFILLPAGE_ID'] . ' AND QARESP_QUESTION=' . $ques['QAQUESTION_ID'] . ' LIMIT 1');
					}
				}
				if($_POST['nextpage'] == 'next')
				{
					header('location: http://' . DNAME . $_POST['path']);
				}
				else
					header('location: http://' . DNAME . '/qa/index.php?group=' . $_GET['rg']);
			}
		}
		else
		{
			// new data from defaults
			if(is_null($page['QAFILLPAGE_ID']))
			{
				$rsuseritems = mysql_query('SELECT USER_LN, USER_FN, USER_FULLNAME, USER_ADDRESS, USER_CITY, USER_EMAIL, USER_STATE, USER_ZIP
				FROM USER_LIST WHERE USER_ID=' . $userid);
				$cuserrecord = mysql_fetch_array($rsuseritems, MYSQL_ASSOC);
				
				$cuser['~LAST_NAME'] = $cuserrecord['USER_LN'];
				$cuser['~FIRST_NAME'] = $cuserrecord['USER_FN'];
				$cuser['~FULL_NAME'] = $cuserrecord['USER_FULLNAME'];
				$cuser['~ADDRESS'] = $cuserrecord['USER_ADDRESS'];
				$cuser['~CITY'] = $cuserrecord['USER_CITY'];
				$cuser['~EMAIL'] = $cuserrecord['USER_EMAIL'];
				$cuser['~STATE'] = $cuserrecord['USER_STATE'];
				$cuser['~ZIP'] = $cuserrecord['USER_ZIP'];
				
				$rschildren = mysql_query('SELECT USER_LN, USER_FN, USER_GR FROM USER_LIST INNER JOIN PARENTSTUDENT_LIST ON PARENTSTUDENT_STUDENT=USER_ID WHERE PARENTSTUDENT_PARENT=' . $userid . ' ORDER BY USER_GR ASC, USER_LN, USER_FN LIMIT 4');
				$i = 1;
				while($child = mysql_fetch_array($rschildren, MYSQL_ASSOC))
				{
					$cuser['~CHILD' . $i . '_FIRST_NAME'] = $child['USER_FN'];
					$cuser['~CHILD' . $i . '_LAST_NAME'] = $child['USER_LN'];
					$cuser['~CHILD' . $i . '_FULL_NAME'] = $child['USER_FN'] . ' ' . $child['USER_LN'];
					$cuser['~CHILD' . $i . '_GRADE'] = C_SCHOOLYEAR + 12 - $child['USER_GR'];
					
					$i++;
				}
			
				$rsquestions = mysql_query('SELECT * FROM QAQUESTION_LIST WHERE QAQUESTION_PAGE=' . $page['QAPAGE_ID'] . ' ORDER BY QAQUESTION_ORDER');
				if(mysql_num_rows($rsquestions) > 0)
				{
					while($ques = mysql_fetch_array($rsquestions, MYSQL_ASSOC))
					{
						if($ques['QAQUESTION_TYPE'] == 'Select' || $ques['QAQUESTION_TYPE'] == 'Radio' || $ques['QAQUESTION_TYPE'] == 'Check')
						{
							$fvalues[$ques['QAQUESTION_ID']] = $ques['QAQUESTION_DVAL_INT'];
						}
						else
						{
							if(substr($ques['QAQUESTION_DVAL_TEXT'], 0, 1) == '~')
								$fvalues[$ques['QAQUESTION_ID']] = $cuser[$ques['QAQUESTION_DVAL_TEXT']];
							else
								$fvalues[$ques['QAQUESTION_ID']] = $ques['QAQUESTION_DVAL_TEXT'];
						}
					}
				}
			}
			// load previously saved data
			else
			{
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
			}
		}
	}
}



?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
	<head>
		<title><?= htmlentities($page['QAPAGE_TITLE']) ?></title>
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
	<body>
		<? include "inc-header.php"; ?>
		
		<? if($showitem) { ?>
		<form method="POST" action="prelim.php?rg=<?= $_GET['rg'] ?>" style="margin: 0px">
		<h1 class="titlebar"><span style="font-size: large"><a href="./?group=<?= $curgroup['QAGROUP_ID'] ?>"><?= $curgroup['QAGROUP_TITLE'] ?></a>:</span> Preliminary Information</h1>
		<? $group = $_GET['rg'];
		include "inc-nav.php"; ?>
		<table width="100%" cellpadding="5" cellspacing="0"><tr><td style="vertical-align: top; background-color: #dddddd; width: 225px"><a href="../?group=<?= $_GET['rg'] ?>">Return to forms without saving</a></td><td style="font-size: medium; vertical-align: top">
		<?
			if($numerrors)
			{
				print '<div style="color: #660000; background-color: #ffffdd; border: 1px solid #000000; padding: 3px">';
				if($numerrors == 1)
					print 'There was an error in processing this form. Please read the problem noted below.';
				else
					print 'There were errors in processing this form. Please read the ' . $numerrors . ' problems noted below.';
				print '</div>';
			}

			print $page['QAPAGE_DESC'];

			print '<h2 class="grayheading">Preliminary Information</h2>';
			print '<p>Filling this page out will help us automatically fill in your name, phone number, email address, etc. on later forms.</p>';
			
			$rsquestions = mysql_query('SELECT * FROM QAQUESTION_LIST LEFT JOIN QAFORMAT_LIST ON QAQUESTION_FORMAT=QAFORMAT_ID WHERE QAQUESTION_PAGE=' . $page['QAPAGE_ID'] . ' ORDER BY QAQUESTION_ORDER');
			if(mysql_num_rows($rsquestions) > 0)
			{
				while($ques = mysql_fetch_array($rsquestions, MYSQL_ASSOC))
				{
					$defaultvalue = $fvalues[$ques['QAQUESTION_ID']];

					if($ques['QAQUESTION_TYPE'] == 'Radio' || $ques['QAQUESTION_TYPE'] == 'Longtext')
						$tdalign = 'vertical-align: top; ';
					else
						$tdalign = '';
				
					if($ques['QAQUESTION_HEADING'] != '')
						print '<h3>' . $ques['QAQUESTION_HEADING'] . '</h3>';

					print '<table cellpadding="3" cellspacing="0" style="font-size: medium" width="100%">';

					if($errors[$ques['QAQUESTION_ID']])
						print '<tr><td></td><td class="errortd"><span style="background-color: #ffffcc">' . htmlentities($errors[$ques['QAQUESTION_ID']]) . '</span></td></tr>';

					if($ques['QAQUESTION_TYPE'] == 'Check')
					{
						print '<tr>';
						print '<td style="width: 2em"><input ';
						if($defaultvalue)
							print 'checked ';
						print 'type="checkbox" name="q' . $ques['QAQUESTION_ID'] . '" id="checkq' . $ques['QAQUESTION_ID'] . '" value="1"></td>';
						print '<td style="';
						if($ques['QAQUESTION_REQD'] || $errors[$ques['QAQUESTION_ID']])
							print 'font-weight: bold; ';
						if($errors[$ques['QAQUESTION_ID']])
							print 'color: #cc0000; ';
						print '"><label for="checkq' . $ques['QAQUESTION_ID'] . '">' . $ques['QAQUESTION_PROMPT'] . '</label></td>';
						print '</tr>';
					}
					else
					{
						print '<tr>';
						print '<td style="width: 12em; ' . $tdalign;
						if($ques['QAQUESTION_REQD'] || $errors[$ques['QAQUESTION_ID']])
							print 'font-weight: bold; ';

						if($errors[$ques['QAQUESTION_ID']])
							print 'color: #cc0000; ';
						print '">' . $ques['QAQUESTION_PROMPT'] . '</td>';
						print '<td style="' . $tdalign . '">';

						if($ques['QAQUESTION_TYPE'] == 'Text')
							print '<input type="text" style="width: ' . $ques['QAQUESTION_SIZE'] . 'em" name="q' . $ques['QAQUESTION_ID'] . '" value="' . htmlentities($defaultvalue) . '">';
						else if($ques['QAQUESTION_TYPE'] == 'Longtext')
							print '<textarea style="width: 20em" rows="' . $ques['QAQUESTION_SIZE'] . '" name="q' . $ques['QAQUESTION_ID'] . '">' . htmlentities($defaultvalue) . '</textarea>';
						else if($ques['QAQUESTION_TYPE'] == 'Select')
						{
							print '<select name="q' . $ques['QAQUESTION_ID'] . '">';
							$rsoptions = mysql_query('SELECT * FROM QAMC_LIST WHERE QAMC_QUESTION=' . $ques['QAQUESTION_ID'] . ' ORDER BY QAMC_ORDER');
							while($option = mysql_fetch_array($rsoptions, MYSQL_ASSOC))
							{
								print '<option ';
								if($defaultvalue == $option['QAMC_ID'])
									print 'selected ';
								print 'value="' . $option['QAMC_ID'] . '">' . htmlentities($option['QAMC_TEXT']) . '</option>';
							}
							print '</select>';
						}
						else if($ques['QAQUESTION_TYPE'] == 'Radio')
						{
							$rsoptions = mysql_query('SELECT * FROM QAMC_LIST WHERE QAMC_QUESTION=' . $ques['QAQUESTION_ID'] . ' ORDER BY QAMC_ORDER');
							while($option = mysql_fetch_array($rsoptions, MYSQL_ASSOC))
							{
								print '<div><input ';
								if($defaultvalue == $option['QAMC_ID'])
									print 'checked ';
								print 'type="radio" name="q' . $ques['QAQUESTION_ID'] . '" id="radioq' . $option['QAMC_ID'] . '" value="' . $option['QAMC_ID'] . '"> <label for="radioq' . $option['QAMC_ID'] . '">' . htmlentities($option['QAMC_TEXT']) . '</label></div>';
							}
						}
						print '</td>';
						print '</tr>';
					}
					if($ques['QAFORMAT_SAMPLE'])
						print '<tr><td></td><td class="sampletd">Example: ' . $ques['QAFORMAT_SAMPLE'] . '</td></tr>';
					print '</table>';
					if($ques['QAQUESTION_DESC'] != '')
						print '<div style="padding-left: 3em; padding-top: 3px; padding-right: 3px; padding-bottom: 3px; font-size: small;">' . $ques['QAQUESTION_DESC'] . '</div>';
				}
			}
			$next = $qary[$curposition + 1];
			print '<h2 class="grayheading">Save</h2>';
			print '<p><input type="hidden" name="pageno" value="' . $page['QAPAGE_ID'] . '"><input type="hidden" name="type" value="saveform"><input type="hidden" name="path" value="' . $next['path'] . $next['name'] . $next['query'] . '">';
			print '<p><input type="hidden" name="pageno" value="50"><input type="hidden" name="type" value="saveform"><input type="submit" name="btn" value="Save"> and then <select name="nextpage">';
			if(!is_null($next))
				print '<option value="next">go to next page</option>'
			print '<option value="close">quit</option></select></p>';
		?>
		</td></tr></table>
		</form>
		<? include '../../inc-footer.php'; ?>
		<? } ?>
	</body>
</html>
