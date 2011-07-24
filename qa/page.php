<?
include '../db.php';

function templateJsExpr($str)
{
	return ereg_replace("#q([0-9]+)#", "document.getElementById('q\\1').value", ereg_replace("#m([0-9]+)#", "document.getElementById('radioq\\1').checked", $str));
}

if(is_id($_GET['id']))
{
    if((is_id($_GET['fill']) && $loggedin) || !$loggedin)
    {
    	if($loggedin && $_GET['delpage'])
    	{
    		$rsdelpage = mysql_query('SELECT * FROM QAPAGE_LIST
    			INNER JOIN QA_LIST ON QAPAGE_QA=QA_ID
    			INNER JOIN QAFILL_LIST ON QA_ID=QAFILL_QA AND QAFILL_USER=' . $userid . '
    			INNER JOIN QAFILLPAGE_LIST ON QAFILLPAGE_FILL=QAFILL_ID AND QAFILLPAGE_PAGE=QAPAGE_ID
    			WHERE QA_SAVELIMIT=0 AND QAFILL_USER=' . $userid . ' AND QAFILLPAGE_ID=' . $_GET['delpage'] . ' AND QAFILL_ID=' . $_GET['fill'] . ' AND QAPAGE_ID=' . $_GET['id']);

    		if($delpage = mysql_fetch_array($rsdelpage, MYSQL_ASSOC))
    		{
    			mysql_query('DELETE FROM QAFILLPAGE_LIST WHERE QAFILLPAGE_ID=' . $_GET['delpage']);
    			mysql_query('DELETE FROM QARESP_LIST WHERE QARESP_FILLPAGE=' . $_GET['delpage']);
    		}
    	}

    	// Load fill
    	if($loggedin)
	{
    		$rspage = mysql_query('SELECT * FROM QAPAGE_LIST
        		INNER JOIN QA_LIST ON QAPAGE_QA=QA_ID
        		INNER JOIN QAFILL_LIST ON QA_ID=QAFILL_QA
        		INNER JOIN QAGROUP_LIST ON QAGROUP_ID=QA_GROUP
        		LEFT JOIN QAFILLPAGE_LIST ON QAFILLPAGE_FILL=QAFILL_ID AND QAFILLPAGE_PAGE=QAPAGE_ID
        		WHERE QAFILL_USER=' . $userid . ' AND QAFILL_ID=' . $_GET['fill'] . ' AND QAPAGE_ID=' . $_GET['id']);
	}
	else
	{
		$rspage = mysql_query('SELECT * FROM QAPAGE_LIST
        		INNER JOIN QA_LIST ON QAPAGE_QA=QA_ID
        		INNER JOIN QAGROUP_LIST ON QAGROUP_ID=QA_GROUP
        		WHERE QA_ALLOW_ANONYMOUS=1 AND QAPAGE_ID=' . $_GET['id']);
	}

    	if($page = mysql_fetch_array($rspage, MYSQL_ASSOC))
    	{
    		$showitem = true;

    		// No current fill?
    		if(is_null($page['QAFILLPAGE_ID']))
    		{
    			$pagelocked = false;
    		}
    		else
    		{
			// Lock page if timed out or #saves maxed
    			$rstimeoffset = mysql_query('SELECT UNIX_TIMESTAMP("' . date(TIME_FORMAT_SQL, CURRENT_TIME) . '") - UNIX_TIMESTAMP(QAFILLPAGE_FINISH) AS C FROM QAFILLPAGE_LIST WHERE QAFILLPAGE_ID=' . $page['QAFILLPAGE_ID']);
    			$timeoffset = mysql_fetch_array($rstimeoffset, MYSQL_ASSOC);
    			$outoftime = !is_null($page['QAPAGE_TIMELIMIT']) && ($timeoffset['C'] > ($page['QAPAGE_TIMELIMIT'] + 15)); // 15 = grace period
    			$savemaxed = ($page['QA_SAVELIMIT'] && $page['QAFILLPAGE_SAVED']);

    			$pagelocked = $savemaxed || $outoftime || !($page['QA_OPEN'] == 1);
    		}

    		if($page['QA_SHOWANSWERS'] == 1 && $page['QA_OPEN'] == 2)
    		{
    			$rsquestions = mysql_query('SELECT * FROM QAQUESTION_LIST WHERE QAQUESTION_PAGE=' . $page['QAPAGE_ID'] . ' ORDER BY QAQUESTION_ORDER');
    			if(mysql_num_rows($rsquestions) > 0)
    			{
    				while($ques = mysql_fetch_array($rsquestions, MYSQL_ASSOC))
    				{
    					if($ques['QAQUESTION_TYPE'] == 'Select' || $ques['QAQUESTION_TYPE'] == 'Radio' || $ques['QAQUESTION_TYPE'] == 'Check')
    						$fanswers[$ques['QAQUESTION_ID']] = $ques['QAQUESTION_ANSWER_INT'];
    					else
    						$fanswers[$ques['QAQUESTION_ID']] = $ques['QAQUESTION_ANSWERs_TEXT'];
    				}
    			}

    			$showanswers = true;
    		}
    		else
    			$showanswers = false;

    		if($loggedin)
		{
    			// Load autofill data
        		$rsuseritems = mysql_query('SELECT QAQUESTION_AUTOFILLNAME, QARESP_RESP_TEXT
        			FROM QA_LIST
        				INNER JOIN QAPAGE_LIST ON QAPAGE_QA=QA_ID
        				INNER JOIN QAFILL_LIST ON QAFILL_QA=QA_ID
        				INNER JOIN QAFILLPAGE_LIST ON QAFILLPAGE_FILL=QAFILL_ID AND QAFILLPAGE_PAGE=QAPAGE_ID
        				INNER JOIN QAQUESTION_LIST ON QAQUESTION_PAGE=QAPAGE_ID
        				INNER JOIN QARESP_LIST ON QARESP_QUESTION=QAQUESTION_ID AND QARESP_FILLPAGE=QAFILLPAGE_ID
        			WHERE QAFILL_USER=' . $userid . ' AND QAPAGE_ID=6');
        		while($preentered = mysql_fetch_array($rsuseritems, MYSQL_ASSOC))
        		{
        			$cuser[$preentered['QAQUESTION_AUTOFILLNAME']] = $preentered['QARESP_RESP_TEXT'];
        			if(substr($preentered['QAQUESTION_AUTOFILLNAME'], 0, 7) == ('~CHILD' . $page['QAFILL_CHILD']))
        			{
        				$cuser['~CHILD' . substr($preentered['QAQUESTION_AUTOFILLNAME'], 7)] = $preentered['QARESP_RESP_TEXT'];
        			}
        		}
        		$cuser['~FULL_ADDRESS'] = $cuser['~ADDRESS'] . ', ' . $cuser['~CITY'] . ', CA ' . $cuser['~ZIP'];
        		$cuser['~FULL_NAME'] = $cuser['~FIRST_NAME'] . ' ' . $cuser['~LAST_NAME'];

        		if($cuser['~CHILD_FIRST_NAME'] && $cuser['~CHILD_LAST_NAME'])
        			$cuser['~CHILD_FULL_NAME'] = $cuser['~CHILD_FIRST_NAME'] . ' ' . $cuser['~CHILD_LAST_NAME'];
        		else
        			$cuser['~CHILD_FULL_NAME'] = $cuser['~CHILD_FIRST_NAME'] . $cuser['~CHILD_LAST_NAME'];

        		for($i = 1; $i <= 4; $i++)
        		{
        			if($cuser['~CHILD' . $i . '_FIRST_NAME'] && $cuser['~CHILD' . $i . '_LAST_NAME'])
        				$cuser['~CHILD' . $i . '_FULL_NAME'] = $cuser['~CHILD' . $i . '_FIRST_NAME'] . ' ' . $cuser['~CHILD' . $i . '_LAST_NAME'];
        			else
        				$cuser['~CHILD' . $i . '_FULL_NAME'] = $cuser['~CHILD' . $i . '_FIRST_NAME'] . $cuser['~CHILD' . $i . '_LAST_NAME'];

        			$cuser['~HASCHILD_GRADE_' . $cuser['~CHILD' . $i . '_GRADE']] = 1;
        		}

        		$cuser['~CHILDREN_NAMESGRADES'] = $cuser['~CHILD1_FULL_NAME'] . ', ' . $cuser['~CHILD1_GRADE'];
        		$cuser['~CHILDREN_NAMES'] = $cuser['~CHILD1_FULL_NAME'];
        		$cuser['~CHILDREN_GRADES'] = $cuser['~CHILD1_GRADE'];
        		for($i = 2; $i <= 4; $i++)
        		{
        			if($cuser['~CHILD' . $i . '_FIRST_NAME'] || $cuser['~CHILD' . $i . '_LAST_NAME'])
        			{
        				$cuser['~CHILDREN_NAMESGRADES'] .= "\n" . $cuser['~CHILD' . $i . '_FULL_NAME'] . ', ' . $cuser['~CHILD' . $i . '_GRADE'];
        				$cuser['~CHILDREN_NAMES'] .= ', ' . $cuser['~CHILD' . $i . '_FULL_NAME'];
        				$cuser['~CHILDREN_GRADES'] .= ', ' . $cuser['~CHILD' . $i . '_GRADE'];
        			}
        		}

        		// Load data from previous fill
        		$rsprevfill = mysql_query('SELECT * FROM QAFILL_LIST INNER JOIN QAFILLPAGE_LIST ON QAFILLPAGE_FILL=QAFILL_ID WHERE QAFILLPAGE_PAGE=' . $page['QAPAGE_ID'] . ' AND QAFILL_QA=' . $page['QA_ID'] . ' AND QAFILL_USER=' . $userid . ' AND QAFILL_ID!=' . $page['QAFILL_ID'] . ' ORDER BY QAFILL_ID ASC LIMIT 1');
        		if($prevfill = mysql_fetch_array($rsprevfill, MYSQL_ASSOC))
        		{
        			// Pages previously saved
        			if($page['QAFILLPAGE_SAVED'] == 1)
        			{
        				$oldfill = true;

        				// Load old responses
        				$rsoldquestions = mysql_query('SELECT * FROM QAQUESTION_LIST
        					LEFT JOIN QAFILLPAGE_LIST ON QAFILLPAGE_FILL=' . $prevfill['QAFILL_ID'] . ' AND QAFILLPAGE_PAGE=QAQUESTION_PAGE
        					LEFT JOIN QAFORMAT_LIST ON QAQUESTION_FORMAT=QAFORMAT_ID
        					LEFT JOIN QARESP_LIST ON QARESP_FILLPAGE=QAFILLPAGE_ID AND QARESP_QUESTION=QAQUESTION_ID
        					WHERE QAQUESTION_PAGE=' . $page['QAPAGE_ID'] . ' ORDER BY QAQUESTION_ORDER');
        				while($ques = mysql_fetch_array($rsoldquestions, MYSQL_ASSOC))
        				{
        					if($ques['QAQUESTION_TYPE'] == 'Select' || $ques['QAQUESTION_TYPE'] == 'Radio' || $ques['QAQUESTION_TYPE'] == 'Check')
        						$oldfvalues[$ques['QAQUESTION_ID']] = $ques['QARESP_RESP_INT'];
        					else if($ques['QAQUESTION_TYPE'] == 'Text')
        						$oldfvalues[$ques['QAQUESTION_ID']] = $ques['QARESP_RESP_TEXT'];
        					else if($ques['QAQUESTION_TYPE'] == 'Longtext')
        						$oldfvalues[$ques['QAQUESTION_ID']] = $ques['QARESP_RESP_LONGTEXT'];
        				}
        			}
        			else
        			{
        				$oldfill = false;
        			}
        		}
			else
				$oldfill = false;

			// Create a new fillpage when the page is loaded for the first time
			$rssavedfill = mysql_query('SELECT * FROM QAFILL_LIST INNER JOIN QAFILLPAGE_LIST ON QAFILLPAGE_FILL=QAFILL_ID WHERE QAFILLPAGE_PAGE=' . $page['QAPAGE_ID'] . ' AND QAFILL_QA=' . $page['QA_ID'] . ' AND QAFILL_USER=' . $userid . ' AND QAFILL_ID=' . $page['QAFILL_ID'] . ' ORDER BY QAFILL_ID ASC LIMIT 1');
			if(mysql_num_rows($rssavedfill) == 0)
			{
        			mysql_query('INSERT INTO QAFILLPAGE_LIST VALUES (\'\', ' . $_GET['fill'] . ', ' . $_GET['id'] . ', 0, 0, "' . date(TIME_FORMAT_SQL, CURRENT_TIME) . '", "' . date(TIME_FORMAT_SQL, CURRENT_TIME) . '")');
        		}
		}

    		// Save form data
    		if($_POST['type'] == 'saveform')
    		{
    			$numerrors = 0;

    			// Check required fields and formats
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

    			// Save data
    			if($numerrors == 0)
    			{
    				$rsquestions = mysql_query('SELECT * FROM QAQUESTION_LIST LEFT JOIN QAFORMAT_LIST ON QAQUESTION_FORMAT=QAFORMAT_ID WHERE QAQUESTION_PAGE=' . $page['QAPAGE_ID'] . ' ORDER BY QAQUESTION_ORDER');

    				if(!$pagelocked)
    				{
    					// Insert new records
    					if(!($page['QAFILLPAGE_SAVED'] == 1)) // Not previously saved
    					{
						if($loggedin)
						{
    							$newqafillpage = $page['QAFILLPAGE_ID'];
						}
						else
						{
							mysql_query('INSERT INTO QAFILL_LIST (QAFILL_IP, QAFILL_QA, QAFILL_CHILD, QAFILL_START, QAFILL_FINISH) VALUES ("'. $_SERVER['REMOTE_ADDR'] . '", '. $page['QAPAGE_QA'] . ', Null , "' . date(TIME_FORMAT_SQL, CURRENT_TIME) . '", Null)');
							mysql_query('INSERT INTO QAFILLPAGE_LIST VALUES (\'\', ' . mysql_insert_id() . ', ' . $page['QAPAGE_ID'] . ', 0, 0, "' . date(TIME_FORMAT_SQL, CURRENT_TIME) . '", "' . date(TIME_FORMAT_SQL, CURRENT_TIME) . '")');
							$newqafillpage = mysql_insert_id();
						}

						if($newqafillpage)
						{
    							mysql_query('UPDATE QAFILLPAGE_LIST SET QAFILLPAGE_SAVED=1, QAFILLPAGE_FINISH="' . date(TIME_FORMAT_SQL, CURRENT_TIME) . '" WHERE QAFILLPAGE_ID=' . $page['QAFILLPAGE_ID']);

    							while($ques = mysql_fetch_array($rsquestions, MYSQL_ASSOC))
    							{
    								// Insert new RESPs
    								if($ques['QAQUESTION_TYPE'] == 'Select' || $ques['QAQUESTION_TYPE'] == 'Radio' || $ques['QAQUESTION_TYPE'] == 'Check')
    									mysql_query('INSERT INTO QARESP_LIST (QARESP_FILLPAGE, QARESP_QUESTION, QARESP_RESP_INT) VALUES (' . $newqafillpage . ', ' . $ques['QAQUESTION_ID'] . ', "' . $_POST['q' . $ques['QAQUESTION_ID']] . '")');
    								else if($ques['QAQUESTION_TYPE'] == 'Text')
    									mysql_query('INSERT INTO QARESP_LIST (QARESP_FILLPAGE, QARESP_QUESTION, QARESP_RESP_TEXT) VALUES (' . $newqafillpage . ', ' . $ques['QAQUESTION_ID'] . ', "' . $_POST['q' . $ques['QAQUESTION_ID']] . '")');
    								else if($ques['QAQUESTION_TYPE'] == 'Longtext')
    									mysql_query('INSERT INTO QARESP_LIST (QARESP_FILLPAGE, QARESP_QUESTION, QARESP_RESP_LONGTEXT) VALUES (' . $newqafillpage . ', ' . $ques['QAQUESTION_ID'] . ', "' . $_POST['q' . $ques['QAQUESTION_ID']] . '")');

    								// Update scores
    								if($ques['QAQUESTION_TYPE'] == 'Select' || $ques['QAQUESTION_TYPE'] == 'Radio' || $ques['QAQUESTION_TYPE'] == 'Check')
    									mysql_query('UPDATE QARESP_LIST SET QARESP_PTS=IF(QARESP_RESP_INT=' . $ques['QAQUESTION_ANSWER_INT'] . ',' . $ques['QAQUESTION_PTS'] . ',0) WHERE QARESP_FILLPAGE=' . $page['QAFILLPAGE_ID'] . ' AND QARESP_QUESTION=' . $ques['QAQUESTION_ID']);
    								else
    									mysql_query('UPDATE QARESP_LIST SET QARESP_PTS=IF(QARESP_RESP_TEXT="' . addslashes($ques['QAQUESTION_ANSWER_TEXT']) . '",' . $ques['QAQUESTION_PTS'] . ',0) WHERE QARESP_FILLPAGE=' . $page['QAFILLPAGE_ID'] . ' AND QARESP_QUESTION=' . $ques['QAQUESTION_ID']);
    							}
    						}
    					}
    					else
    					{
    						mysql_query('UPDATE QAFILLPAGE_LIST SET QAFILLPAGE_FINISH="' . date(TIME_FORMAT_SQL, CURRENT_TIME) . '" WHERE QAFILLPAGE_ID=' . $page['QAFILLPAGE_ID']);

    						while($ques = mysql_fetch_array($rsquestions, MYSQL_ASSOC))
    						{
    							// Update old RESPs
    							if($ques['QAQUESTION_TYPE'] == 'Select' || $ques['QAQUESTION_TYPE'] == 'Radio' || $ques['QAQUESTION_TYPE'] == 'Check')
    								mysql_query('UPDATE QARESP_LIST SET QARESP_RESP_INT="' . $_POST['q' . $ques['QAQUESTION_ID']] . '" WHERE QARESP_FILLPAGE=' . $page['QAFILLPAGE_ID'] . ' AND QARESP_QUESTION=' . $ques['QAQUESTION_ID'] . ' LIMIT 1');
    							else if($ques['QAQUESTION_TYPE'] == 'Text')
    								mysql_query('UPDATE QARESP_LIST SET QARESP_RESP_TEXT="' . $_POST['q' . $ques['QAQUESTION_ID']] . '" WHERE QARESP_FILLPAGE=' . $page['QAFILLPAGE_ID'] . ' AND QARESP_QUESTION=' . $ques['QAQUESTION_ID'] . ' LIMIT 1');
    							else if($ques['QAQUESTION_TYPE'] == 'Longtext')
    								mysql_query('UPDATE QARESP_LIST SET QARESP_RESP_LONGTEXT="' . $_POST['q' . $ques['QAQUESTION_ID']] . '" WHERE QARESP_FILLPAGE=' . $page['QAFILLPAGE_ID'] . ' AND QARESP_QUESTION=' . $ques['QAQUESTION_ID'] . ' LIMIT 1');

    							// Update scores
    							if($ques['QAQUESTION_TYPE'] == 'Select' || $ques['QAQUESTION_TYPE'] == 'Radio' || $ques['QAQUESTION_TYPE'] == 'Check')
    								mysql_query('UPDATE QARESP_LIST SET QARESP_PTS=IF(QARESP_RESP_INT=' . $ques['QAQUESTION_ANSWER_INT'] . ',' . $ques['QAQUESTION_PTS'] . ',0) WHERE QARESP_FILLPAGE=' . $page['QAFILLPAGE_ID'] . ' AND QARESP_QUESTION=' . $ques['QAQUESTION_ID']);
    							else
    								mysql_query('UPDATE QARESP_LIST SET QARESP_PTS=IF(QARESP_RESP_TEXT="' . addslashes($ques['QAQUESTION_ANSWER_TEXT']) . '",' . $ques['QAQUESTION_PTS'] . ',0) WHERE QARESP_FILLPAGE=' . $page['QAFILLPAGE_ID'] . ' AND QARESP_QUESTION=' . $ques['QAQUESTION_ID']);
    						}
    					}
    				}

    				if(!is_id($_POST['nextpage']))
					header('location: http://' . DNAME . '/qa/qa.php?id=' . $page['QA_ID'] . '&fill=' . $_GET['fill'] . '&closepage=true');
				else
    					header('location: http://' . DNAME . '/qa/page.php?id=' . $_POST['nextpage'] . '&fill=' . $_GET['fill']);
    			}
    		}
    		else
    		{
    			// new data from defaults
    			// if(is_null($page['QAFILLPAGE_ID']))
    			if(!$page['QAFILLPAGE_SAVED'])
    			{
    				$rsquestions = mysql_query('SELECT * FROM QAQUESTION_LIST WHERE QAQUESTION_PAGE=' . $page['QAPAGE_ID'] . ' ORDER BY QAQUESTION_ORDER');
    				if(mysql_num_rows($rsquestions) > 0)
    				{
    					while($ques = mysql_fetch_array($rsquestions, MYSQL_ASSOC))
    					{
    						if(substr($ques['QAQUESTION_DVAL_TEXT'], 0, 1) == '~')
    							$fvalues[$ques['QAQUESTION_ID']] = $cuser[$ques['QAQUESTION_DVAL_TEXT']];
    						else
    						{
    							if($oldfill)
    								$fvalues[$ques['QAQUESTION_ID']] = $oldfvalues[$ques['QAQUESTION_ID']];
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
}

if(!$showitem)
	die();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
	<head>
		<title><? print htmlentities($page['QAPAGE_TITLE']); ?></title>
		<meta name="GENERATOR" content="Microsoft Visual Studio.NET 7.0">
		<meta name="vs_targetSchema" content="http://schemas.microsoft.com/intellisense/ie5">
		<link rel="stylesheet" type="text/css" href="../shs.css">
		<link rel="stylesheet" type="text/css" href="qa.css">
		<style type="text/css">
			a.lnkh { font-weight: bold }
			.errortd { font-size: small; color: #660000 }
			.sampletd { font-size: small; color: #666666 }
			div.sigprompttext p { text-indent: 4em; margin: 0px }
			.notsaved { font-weight: bold; color: #DD0000; margin-top: 2px }
		</style>
		<script type="text/javascript">
		<!--
		function parseDollars(ques)
		{
		return (parseInt(ques) ? parseInt(ques) : (parseInt(ques.substr(1)) ? parseInt(ques.substr(1)) : 0));
		}
		//-->
		</script>
	</head>
	<body<? if(!is_null($page['QAPAGE_TIMELIMIT']) && !$pagelocked) { ?> onload="setTimeout('qform.submit();', <?= $page['QAPAGE_TIMELIMIT'] ?> * 1000);"<? } ?>>
		<? include "inc-header.php";
		?>
		<form id="qform" method="POST" action="page.php?id=<?= $page['QAPAGE_ID'] ?>&amp;fill=<?= $_GET['fill'] ?>" name="<?= $page['QAPAGE_ID'] ?>" style="margin: 0px">
		<h1 class="titlebar"><span style="font-size: large"><a href="./?group=<?= $page['QAGROUP_ID'] ?>"><?= $page['QAGROUP_TITLE'] ?></a>: <a href="qa.php?id=<?= $page['QA_ID'] ?>"><?= htmlentities($page['QA_TITLE']) ?></a>:</span> <?= htmlentities($page['QAPAGE_TITLE']) ?></h1>
		<? $group = $page['QA_GROUP'];
		include "specials/inc-nav.php"; ?>
		<table width="100%" cellpadding="3" cellspacing="0"><tr>
		<td style="vertical-align: top; background-color: #dddddd; width: 225px; font-size: small">
		<?
			print '<h2 class="grayheading">' . htmlentities($page['QA_TITLE']) . '</h2>';

			if($loggedin)
				$rspages = mysql_query('SELECT * FROM QAPAGE_LIST LEFT JOIN QAFILLPAGE_LIST ON QAFILLPAGE_FILL=' . $_GET['fill'] . ' AND QAFILLPAGE_PAGE=QAPAGE_ID WHERE QAPAGE_QA=' . $page['QA_ID'] . ' ORDER BY QAPAGE_ORDER');
			else
				$rspages = mysql_query('SELECT * FROM QAPAGE_LIST WHERE QAPAGE_QA=' . $page['QA_ID'] . ' ORDER BY QAPAGE_ORDER');

			print '<div class="hcontent" style="font-size: medium"><div><a href="qa.php?id=' . $page['QA_ID'] . '&amp;fill=' . $_GET['fill'] . '">Back to Introduction';
			if($page['QA_PERCHILD'])
				print '/View all copies';
			print '</a></div>';

			if($page['QA_PERCHILD'] && $page['QAFILL_CHILD'])
				print '<div>This copy is for <span style="font-weight: bold">' . $cuser['~CHILD_FULL_NAME'] . '</span>.</div>';


			print '</div>';

			print '<h2 class="grayheading">' . mysql_num_rows($rspages) . ' page';
			if(mysql_num_rows($rspages) != 1)
				print 's';
			print '</h2>';
			print '<div class="hcontent">';
			while($temppage = mysql_fetch_array($rspages, MYSQL_ASSOC))
			{
				if($temppage['QAFILLPAGE_SAVED'] == 1)
					$imgstr = '<img align="absmiddle" src="imgs/finished.gif">';
				else
					$imgstr = '<img align="absmiddle" src="imgs/undone.gif">';

				if($page['QAPAGE_ID'] == $temppage['QAPAGE_ID'])
				{
					print '<div class="selpage">' . $temppage['QAPAGE_ORDER'] . ' ' . $imgstr . ' <span style="font-weight: bold">' . htmlentities($temppage['QAPAGE_TITLE']) . '</span></div>';

					if(is_null($temppage['QAFILLPAGE_ID']))
						print '<div class="nselpage" style="padding-left: 2em; color: #990000">Click Save to move on to the next page (or quit).</div>';
					else if($page['QA_SAVELIMIT'] == 0)
						print '<div class="nselpage" style="padding-left: 2em"><a href="page.php?id=' . $_GET['id'] . '&amp;fill=' . $_GET['fill'] . '&amp;delpage=' . $temppage['QAFILLPAGE_ID'] . '" onclick="return window.confirm(\'Are you sure you want to clear this page?\')">Erase this page and start over</a></div>';

					$lastpage = true;
				}
				else
				{
					print '<div class="nselpage">' . $temppage['QAPAGE_ORDER'] . ' ' . $imgstr . ' <a href="page.php?id=' . $temppage['QAPAGE_ID'] . '&amp;fill=' . $_GET['fill'] . '">' . htmlentities($temppage['QAPAGE_TITLE']) . '</a></div>';
					if($lastpage)
						$nextpageno = $temppage['QAPAGE_ID'];
					$lastpage = false;
				}
			}
			print '</div>';
		?>
		</td><td style="font-size: medium; vertical-align: top">
		<h1 style="margin: 0px"><span style="font-size: x-large; letter-spacing: 3pt; color: #999999"><?= 'Page ' . $page['QAPAGE_ORDER'] ?></span></h1><h2 class="grayheading"><?= htmlentities($page['QAPAGE_TITLE']) ?></h2><div class="hcontent">

		<? if($page['QAFILLPAGE_SAVED'] == 0)
			print '<p class="notsaved">This page hasn\'t been saved.</p>'; ?>

		<p>Question in <strong>bold</strong> are required.</p>
		<?
			if($numerrors)
			{
				print '<div style="color: #660000; background-color: #ffffdd; border: 1px solid #000000; padding: 6px; font-size: large">';
				if($numerrors == 1)
					print 'There was an error in processing this form. Please read the problem noted below.';
				else
					print 'There were errors in processing this form. Please read the ' . $numerrors . ' problems noted below.';
				print '</div>';
			}

			print $page['QAPAGE_DESC'];

			if(!is_null($page['QAPAGE_TIMELIMIT']) && !$pagelocked)
				print '<div style="padding-left: 3em; padding-top: 3px; padding-right: 3px; padding-bottom: 3px; font-size: large; font-weight: bold">Time limit: ' . $page['QAPAGE_TIMELIMIT'] . ' seconds.</div>';

			$rsquestions = mysql_query('SELECT * FROM QAQUESTION_LIST LEFT JOIN QAFORMAT_LIST ON QAQUESTION_FORMAT=QAFORMAT_ID WHERE QAQUESTION_PAGE=' . $page['QAPAGE_ID'] . ' ORDER BY QAQUESTION_ORDER');
			if(mysql_num_rows($rsquestions) > 0)
			{
				while($ques = mysql_fetch_array($rsquestions, MYSQL_ASSOC))
				{
					$defaultvalue = $fvalues[$ques['QAQUESTION_ID']];
					$answervalue = $fanswers[$ques['QAQUESTION_ID']];

					if($ques['QAQUESTION_TYPE'] == 'Radio' || $ques['QAQUESTION_TYPE'] == 'Longtext' || ($ques['QAQUESTION_TYPE'] == 'Select' && $ques['QAQUESTION_SIZE'] > 1))
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
						if($answervalue)
							$answertext = "Yes";
						else
							$answertext = "No";
						print 'type="checkbox" name="q' . $ques['QAQUESTION_ID'] . '" id="checkq' . $ques['QAQUESTION_ID'] . '" value="1"></td>';
						print '<td style="';
						if($ques['QAQUESTION_REQD'])
							print 'font-weight: bold; ';
						if($errors[$ques['QAQUESTION_ID']])
							print 'color: #cc0000; ';
						print '"><label for="checkq' . $ques['QAQUESTION_ID'] . '"';
						if($isadmin)
							print ' title="QAQUESTION ' . $ques['QAQUESTION_ID'] . ' ORDER ' . $ques['QAQUESTION_ORDER'] . '"';
						print '>' . $ques['QAQUESTION_PROMPT'] . '</label></td>';
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
						print '"><span';
						if($isadmin)
							print ' title="QAQUESTION ' . $ques['QAQUESTION_ID'] . ' ORDER ' . $ques['QAQUESTION_ORDER'] . '"';
						print '>' . $ques['QAQUESTION_PROMPT'] . '</span></td>';
						print '<td style="' . $tdalign . '">';

						if($ques['QAQUESTION_TYPE'] == 'Text')
						{
							print '<input type="text" style="width: ' . $ques['QAQUESTION_SIZE'] . 'em" name="q' . $ques['QAQUESTION_ID'] . '" value="' . htmlentities($defaultvalue) . '" id="q' . $ques['QAQUESTION_ID'] . '"';
							if(!$ques['QAQUESTION_VALUE_JS'])
								print ' onchange="updateDynamics();"';
							if($ques['QAQUESTION_VALUE_JS'])
								print ' readonly';
							print '>';
							$answertext = htmlentities($answervalue);
							if($ques['QAQUESTION_VALUE_JS'])
								$updateScripts .= "document.getElementById('q" . $ques['QAQUESTION_ID'] . "').value=" . templateJsExpr($ques['QAQUESTION_VALUE_JS']) . ";\n";
						}
						else if($ques['QAQUESTION_TYPE'] == 'Longtext')
						{
							print '<textarea style="width: 20em" rows="' . $ques['QAQUESTION_SIZE'] . '" name="q' . $ques['QAQUESTION_ID'] . '">' . htmlentities($defaultvalue) . '</textarea>';
							$answertext = nl2br(htmlentities($answervalue));
						}
						else if($ques['QAQUESTION_TYPE'] == 'HTML')
						{
							print $ques['QAQUESTION_PROMPT'];
						}
						else if($ques['QAQUESTION_TYPE'] == 'Select')
						{
							print '<select name="q' . $ques['QAQUESTION_ID'] . '" size="' . $ques['QAQUESTION_SIZE'] . '">';
							$rsoptions = mysql_query('SELECT * FROM QAMC_LIST WHERE QAMC_QUESTION=' . $ques['QAQUESTION_ID'] . ' ORDER BY QAMC_ORDER');
							while($option = mysql_fetch_array($rsoptions, MYSQL_ASSOC))
							{
								print '<option ';
								if($defaultvalue == $option['QAMC_ID'])
									print 'selected ';
								if($answervalue == $option['QAMC_ID'])
									$answertext = htmlentities($option['QAMC_TEXT']);
								print 'value="' . $option['QAMC_ID'] . '">' . htmlentities($option['QAMC_TEXT']) . '</option>';
							}
							print '</select>';
						}
						else if($ques['QAQUESTION_TYPE'] == 'Radio')
						{
							$rsoptions = mysql_query('SELECT * FROM QAMC_LIST WHERE QAMC_QUESTION=' . $ques['QAQUESTION_ID'] . ' ORDER BY QAMC_ORDER');
							while($option = mysql_fetch_array($rsoptions, MYSQL_ASSOC))
							{
								print '<div><input onchange="updateDynamics();" ';
								if($defaultvalue == $option['QAMC_ID'])
									print 'checked ';
								if($answervalue == $option['QAMC_ID'])
									$answertext = htmlentities($option['QAMC_TEXT']);
								print 'type="radio" name="q' . $ques['QAQUESTION_ID'] . '" id="radioq' . $option['QAMC_ID'] . '" value="' . $option['QAMC_ID'] . '"> <label title="';
								if($isadmin)
									print 'QAMC ' . $option['QAMC_ID'];
								print '" for="radioq' . $option['QAMC_ID'] . '">' . htmlentities($option['QAMC_TEXT']) . '</label></div>';
							}
						}
						print '</td>';
						print '</tr>';
					}

					if($showanswers)
						print '<tr style="background-color: #eeeeee"><td style="font-size: small; text-align: right; font-weight: bold">Answer</td><td style="font-size: small">' . $answertext . '</td></tr>';
					else if(!is_null($ques['QAQUESTION_SAMPLE']))
						print '<tr><td></td><td class="sampletd">Example: ' . $ques['QAQUESTION_SAMPLE'] . '</td></tr>';
					else if($ques['QAFORMAT_SAMPLE'])
						print '<tr><td></td><td class="sampletd">Example: ' . $ques['QAFORMAT_SAMPLE'] . '</td></tr>';
					print '</table>';
					if($ques['QAQUESTION_DESC'] != '')
						print '<div style="padding-left: 3em; padding-top: 3px; padding-right: 3px; padding-bottom: 3px; font-size: small;">' . $ques['QAQUESTION_DESC'] . '</div>';
				}
			}

			print '<script type="text/javascript" defer><!--' . "\n";
			print 'function updateDynamics() {' . "\n";
			print $updateScripts;
			print '}' . "\n";
			print '// --></script>';
		?>
		</div>
		<h2 class="grayheading">Save</h2>
		<div class="hcontent">
		<?
			if(!is_null($page['QAPAGE_SIGPROMPT']))
				print '<div style="border: 2px solid #cccccc; margin: 2ex"><div style="padding: 2px; font-style: italic; text-indent: 0px; background-color: #dddddd">Upon completing these forms, you will be asked to sign to the following statement:</div><div style="padding: 2px;" class="sigprompttext">' . $page['QAPAGE_SIGPROMPT'] . '</div></div>';

			if($pagelocked)
			{
				print '<p style="font-style: italic; margin: 3px">';
				if(is_null($page['QAFILLPAGE_ID']))
					print 'You cannot save your responses on this page.';
				else
					print 'You cannot change your responses on this page.';
				if ($page['QA_OPEN'] == 2)
					print ' The quiz is no longer active.';
				else if($savemaxed)
					print ' You are only allowed to save each page once.';
				else if($outoftime)
					print ' Your time has expired.';
				print '</p>';

				print '<p style="margin: 3px"><input type="hidden" name="pageno" value="' . $page['QAPAGE_ID'] . '"><input type="hidden" name="type" value="saveform">';
				print '<input type="submit" name="btn" value="Continue"> and <select name="nextpage">';
				if(!$lastpage)
					print '<option value="' . $nextpageno . '">go to next page</option>';
				print '<option value="close">quit</option></select>';
				print '</p>';
			}
			else
			{
				print '<p><input type="hidden" name="pageno" value="' . $page['QAPAGE_ID'] . '"><input type="hidden" name="type" value="saveform">';
				print '<input type="submit" name="btn" value="Save"> and then <select name="nextpage">';
				if(!$lastpage)
					print '<option value="' . $nextpageno . '">go to next page</option>';
				print '<option value="close">quit</option></select>';
				print '</p>';
			}
		?>
		</div>
		</td></tr></table>
		</form>
		<? include '../inc-footer.php'; ?>
	</body>
</html>
