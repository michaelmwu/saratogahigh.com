<?

include '../db.php';

if(!is_numeric($_GET['id']))
	die();

$pr = mysql_query("SELECT * FROM EXPORT_LIST INNER JOIN QAPAGE_LIST ON EXPORT_QA=QAPAGE_QA WHERE EXPORT_USER=$userid AND QAPAGE_ID=" . $_GET['id']);

if(!$isadmin && !mysql_fetch_array($pr,MYSQL_ASSOC)) die("Sorry, you don't have permission to view this page.");

$pr = mysql_query('SELECT * FROM QAPAGE_LIST WHERE QAPAGE_ID=' . $_GET['id']) or die(mysql_error());

$pt = mysql_fetch_array( $pr , MYSQL_ASSOC) or die(mysql_error());

// save as csv
header("Content-type: text/plain");
header("Content-Disposition: attachment; filename=" . preg_replace("/ /","",$pt['QAPAGE_TITLE']) . ".csv");

// inserts escape characters (doubles quotation marks) and encloses a string in quotes
function quoteesc($str)
{
	return '"' . ereg_replace('"', '""', $str) . '"';
}

if($loggedin && $isadmin)
{
	// grab list of questions
	$rsquestions = mysql_query('SELECT * FROM QAQUESTION_LIST
		WHERE QAQUESTION_PAGE=' . $_GET['id'] . '
		ORDER BY QAQUESTION_ORDER');

	$numquestions = mysql_num_rows($rsquestions);

	// print column headings
	$firstcol = true;
	while($ques = mysql_fetch_array($rsquestions, MYSQL_ASSOC))
	{
		if(!$firstcol)
			print ',';
		print quoteesc($ques['QAQUESTION_PROMPT']);
		$firstcol = false;
	}
	
	print "\n";


	// grab only one record
	if(is_numeric($_GET['id']))
	{
		$rsanswers = mysql_query('SELECT * FROM QAFILLPAGE_LIST
			INNER JOIN QAQUESTION_LIST ON QAFILLPAGE_PAGE=QAQUESTION_PAGE
			LEFT JOIN QARESP_LIST ON QARESP_FILLPAGE=QAFILLPAGE_ID AND QARESP_QUESTION=QAQUESTION_ID
			WHERE QAFILLPAGE_PAGE=' . 9 . ' AND QAFILLPAGE_ID=' . $_GET['id'] . '
			ORDER BY QAFILLPAGE_FINISH, QARESP_ID, QAQUESTION_ORDER');
	}
	// grab all records
	else
	{
		$rsanswers = mysql_query('SELECT * FROM QAFILLPAGE_LIST
			INNER JOIN QAQUESTION_LIST ON QAFILLPAGE_PAGE=QAQUESTION_PAGE
			LEFT JOIN QARESP_LIST ON QARESP_FILLPAGE=QAFILLPAGE_ID AND QARESP_QUESTION=QAQUESTION_ID
			WHERE QAFILLPAGE_PAGE=' . 9 . ' 
			ORDER BY QAFILLPAGE_FINISH, QARESP_ID, QAQUESTION_ORDER');	
	}


	// print all the data in succession
	$i = 0;
	$firstcol = true;
	while($ans = mysql_fetch_array($rsanswers, MYSQL_ASSOC))
	{
		// print a newline OR a comma before each entry (except the first)
		if(!$firstcol)
		{
			if($i % $numquestions == 0)
				print "\n";
			else
				print ',';
		}
		
		// print actual data
		if($ans['QAQUESTION_TYPE'] == 'Check')
		{
			if($ans['QARESP_RESP_INT'])
				print quoteesc('Yes');
			else
				print quoteesc('No');
		}
		else
		{
			if($ans['QAQUESTION_TYPE'] == 'Text')
				print quoteesc($ans['QARESP_RESP_TEXT']);
			else if($ans['QAQUESTION_TYPE'] == 'Longtext')
				print quoteesc($ans['QARESP_RESP_LONGTEXT']);
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
						print quoteesc($option['QAMC_TEXT']);
					}
				}
				if(!$printedcat)
					print 'NULL';
			}
		}

		$i++;
		$firstcol = false;
	}
}
?>