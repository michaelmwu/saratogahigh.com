<?

include '../../db.php';

$acccboxes = array(56,51,70,64);

function isacc()
{
	global $acccboxes;
	return in_array($_GET['id'],$acccboxes);
}

$isauthor = false;

if(is_id($_GET['id']) && $loggedin)
{
	$rsqa = mysql_query('SELECT * FROM QAPAGE_LIST INNER JOIN QA_LIST ON QAPAGE_QA=QA_ID INNER JOIN QAGROUP_LIST ON QA_GROUP=QAGROUP_ID WHERE QAPAGE_ID=' . $_GET['id']);
	
	if($qa = mysql_fetch_array($rsqa, MYSQL_ASSOC))
	{
		$rsauthor = mysql_query('SELECT * FROM QAAUTHOR_LIST WHERE QAAUTHOR_QAGROUP=' . $qa['QA_GROUP'] . ' AND QAAUTHOR_USER=' . $userid);
		if($author = mysql_fetch_array($rsauthor, MYSQL_ASSOC))
		{
			$isauthor = true;
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

// save as csv
$title = "registrations";
if(strlen($qa['QAPAGE_TITLE']) > 0)
	$title = htmlentities($qa['QAPAGE_TITLE']);

header("Content-type: text/csv");
header('Content-Disposition: attachment; filename="' . $title . '.csv"');

// inserts escape characters (doubles quotation marks) and encloses a string in quotes
function quoteesc($str)
{
	return '"' . ereg_replace('"', '""', $str) . '"';
}

if($isauthor)
{
	// grab list of questions
	$rsquestions = mysql_query('SELECT * FROM QAQUESTION_LIST
		WHERE QAQUESTION_PAGE=' . $_GET['id'] . '
		ORDER BY QAQUESTION_ORDER');

	$numquestions = mysql_num_rows($rsquestions);

	// print column headings
	$quesarray = array('Time Finished');

	// print column headings
	while($ques = mysql_fetch_array($rsquestions, MYSQL_ASSOC))
	{
		if(isacc() && $ques['QAQUESTION_TYPE'] == 'Check')
		{
			$numquestions--;
			continue;
		}

		$quesarray[] = $ques['QAQUESTION_PROMPT'];
	}

	if(isacc())
		$quesarray[] = "Checked";
	
	printcsv($quesarray);

	$rsanswers = mysql_query('SELECT * FROM QAFILLPAGE_LIST
		INNER JOIN QAQUESTION_LIST ON QAFILLPAGE_PAGE=QAQUESTION_PAGE
		LEFT JOIN QARESP_LIST ON QARESP_FILLPAGE=QAFILLPAGE_ID AND QARESP_QUESTION=QAQUESTION_ID
		WHERE QAFILLPAGE_PAGE=' . $_GET['id'] . ' AND QAFILLPAGE_SAVED = 1
		ORDER BY QAFILLPAGE_FINISH, QAQUESTION_ORDER, QARESP_ID');	

	$ansarray = array();

	$accarray = array();

	// print all the data in succession
	$i = 0;
	while($ans = mysql_fetch_array($rsanswers, MYSQL_ASSOC))
	{
		// check for accumulating checkboxes
		if($ans['QAQUESTION_TYPE'] == 'Check' && isacc())
		{
			if($ans['QARESP_RESP_INT'])
				$accarray[] = $ans['QAQUESTION_PROMPT'];
			continue;
		}

		// print a row if a row is done
		if($i % $numquestions == 0 && $i > 0)
		{
			if(isacc())
				$ansarray[] = implode(',',$accarray);
			array_unshift($ansarray,$ans['QAFILLPAGE_FINISH']);
			printcsv($ansarray);

			$ansarray = array();
			$accarray = array();
		}
		
		// print actual data
		if($ans['QAQUESTION_TYPE'] == 'Check')
		{
			if($ans['QARESP_RESP_INT'])
				$ansarray[] = 'Yes';
			else
				$ansarray[] = 'No';
		}
		else
		{
			if($ans['QAQUESTION_TYPE'] == 'Text')
				$ansarray[] = $ans['QARESP_RESP_TEXT'];
			else if($ans['QAQUESTION_TYPE'] == 'Longtext')
				$ansarray[] = $ans['QARESP_RESP_LONGTEXT'];
			else if($ans['QAQUESTION_TYPE'] == 'Radio' || $ans['QAQUESTION_TYPE'] == 'Select')
			{
				// it is conceivable that you could use a left join to grab the text of the selected answer choice instead of using another query
				$rsoptions = mysql_query('SELECT * FROM QAMC_LIST WHERE QAMC_QUESTION=' . $ans['QAQUESTION_ID'] . ' ORDER BY QAMC_ORDER');
				$printedcat = false;
				while($option = mysql_fetch_array($rsoptions, MYSQL_ASSOC))
				{
					if($ans['QARESP_RESP_INT'] == $option['QAMC_ID'])
					{
						$printedcat = true;
						$ansarray[] = $option['QAMC_TEXT'];
					}
				}
				if(!$printedcat)
					$ansarray[] = 'NULL';
			}
		}

		$i++;
	}
}
?>