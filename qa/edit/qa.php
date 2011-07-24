<?
include '../../db.php';

$isauthor = false;
$showitem = false;
$showpage = false;

if(is_numeric($_GET['id']) && $loggedin)
{
	// Get information about QA
	$rsqa = mysql_query('SELECT * FROM QA_LIST INNER JOIN QAGROUP_LIST ON QA_GROUP=QAGROUP_ID WHERE QA_ID=' . $_GET['id'] . ' ORDER BY QA_TITLE');
	
	if($qa = mysql_fetch_array($rsqa, MYSQL_ASSOC))
	{
		$showitem = true;
		
		$updatedstatus = false;
		
        if($_GET['newstatus'] == '1' && $qa['QA_OPEN'] == '0')
        {
        	mysql_query('UPDATE QA_LIST SET QA_OPEN=1 WHERE QA_ID=' . $qa['QA_ID']);
			$updatedstatus = true;
        }
        else if($_GET['newstatus'] == '0' && $qa['QA_OPEN'] == '1' && $numfills == 0)
        {
        	mysql_query('UPDATE QA_LIST SET QA_OPEN=0 WHERE QA_ID=' . $qa['QA_ID']);
			$updatedstatus = true;
        }
        else if($_GET['newstatus'] == '2' && $qa['QA_OPEN'] == '1')
        {
        	mysql_query('UPDATE QA_LIST SET QA_OPEN=2 WHERE QA_ID=' . $qa['QA_ID']);
			$updatedstatus = true;
        }
        else if($_GET['newstatus'] == '1' && $qa['QA_OPEN'] == '2')
        {
        	mysql_query('UPDATE QA_LIST SET QA_OPEN=1 WHERE QA_ID=' . $qa['QA_ID']);
			$updatedstatus = true;
        }
		
		if($updatedstatus)
			header("location: qa.php?id=" . $qa['QA_ID'] . "&mode=questions");

		// Get information about page
		if(is_id($_GET['page']))
		{
			$rspage = mysql_query('SELECT * FROM QAPAGE_LIST
				INNER JOIN QA_LIST ON QAPAGE_QA=QA_ID
				INNER JOIN QAGROUP_LIST ON QAGROUP_ID=QA_GROUP
				WHERE QAPAGE_ID=' . $_GET['page']);
				
			if($page = mysql_fetch_array($rspage, MYSQL_ASSOC))
			{
				$showpage = true;
			}
		}
		
		// Determine which display mode to use
        if($_GET['mode'] == 'key')
			$showmode = 'key';
		else if($_GET['mode'] == 'answers')
        	$showmode = 'answers';
        else if($_GET['mode'] == 'questions')
        	$showmode = 'questions';
		else if($qa['QA_OPEN'] == 0)
        	$showmode = 'questions';
        else
        	$showmode = 'answers';
		
		// Check permissions
		$rsauthor = mysql_query('SELECT * FROM QAAUTHOR_LIST WHERE QAAUTHOR_QAGROUP=' . $qa['QA_GROUP'] . ' AND QAAUTHOR_USER=' . $userid);
		if($author = mysql_fetch_array($rsauthor, MYSQL_ASSOC))
		{
			$isauthor = true;
			if($_POST['action'] == 'saveform')
			{
				$savelimit = $_POST['savelimit'];
				$anonymous = $_POST['anonymous'];
				$invitation = $_POST['invitation'];
				$fill_limit = $_POST['fill_limit'];

				mysql_query("UPDATE QA_LIST SET QA_SAVELIMIT=$savelimit, QA_ALLOW_ANONYMOUS=$anonymous, QA_PRIVATE=$invitation, QA_FILLLIMIT=$fill_limit WHERE QA_ID=" . $qa['QA_ID']) or die('Could not save! ' . mysql_error());
				header('location: http://' . DNAME . '/qa/edit/qa.php?id=' . $_GET['id']  . '&mode=questions');
			}
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
	
$rsfills = mysql_query('SELECT COUNT(*) FROM QAFILL_LIST WHERE QAFILL_QA=' . $_GET['id']);
$rnumfills = mysql_fetch_array($rsfills, MYSQL_ASSOC);
$numfills = $rnumfills['COUNT(*)'];

if($numfills == 0 && is_id($_GET['delques']) && is_id($_GET['page']))
{
	$rsdelques = mysql_query('SELECT * FROM QAQUESTION_LIST WHERE QAQUESTION_ID=' . $_GET['delques'] . ' AND QAQUESTION_PAGE=' . $_GET['page']);

	if($delques = mysql_fetch_array($rsdelques, MYSQL_ASSOC))
	{
		mysql_query('DELETE FROM QAQUESTION_LIST WHERE QAQUESTION_ID=' . $_GET['delques']);
		if($delques['QAQUESTION_TYPE'] == 'Radio' || $delques['QAQUESTION_TYPE'] == 'Select')
			mysql_query('DELETE FROM QAMC_LIST WHERE QAMC_QUESTION=' . $_GET['delques']);

		$rsincr = mysql_query('SELECT QAQUESTION_ID FROM QAQUESTION_LIST WHERE QAQUESTION_PAGE=' . $_GET['page'] . ' AND QAQUESTION_ORDER > ' . $delques['QAQUESTION_ORDER'] . ' ORDER BY QAQUESTION_ORDER ASC');
		while($incr = mysql_fetch_array($rsincr, MYSQL_ASSOC))
			mysql_query('UPDATE QAQUESTION_LIST SET QAQUESTION_ORDER=QAQUESTION_ORDER - 1 WHERE QAQUESTION_ID=' . $incr['QAQUESTION_ID']);
	}
	
	header('location: http://' . DNAME . '/qa/edit/qa.php?id=' . $_GET['id']  . '&mode=questions&page=' . $_GET['page']);
}

// Do a submission confirm
if(is_id($_GET['delpage']))
{
     $rsdelpage = mysql_query('SELECT QAPAGE_ORDER FROM QAPAGE_LIST WHERE QAPAGE_QA=' . $qa['QA_ID'] . ' AND QAPAGE_ID=' . $_GET['delpage']);

	if($del = mysql_fetch_array($rsdelpage, MYSQL_ASSOC))
	{
		mysql_query('DELETE FROM QAPAGE_LIST WHERE QAPAGE_QA=' . $qa['QA_ID'] . ' AND QAPAGE_ID=' . $_GET['delpage'] . ' LIMIT 1');

		$rstoincrement = mysql_query('SELECT QAPAGE_ID FROM QAPAGE_LIST WHERE QAPAGE_ORDER>' . $del['QAPAGE_ORDER'] . ' ORDER BY QAPAGE_ORDER ASC');

		while($toincrement = mysql_fetch_array($rstoincrement, MYSQL_ASSOC))
		{
			mysql_query('UPDATE QAPAGE_LIST SET QAPAGE_ORDER=QAPAGE_ORDER - 1 WHERE QAPAGE_ID=' . $toincrement['QAPAGE_ID']);
		}
	}

     header('location: http://' . DNAME . '/qa/edit/qa.php?id=' . $_GET['id']);
}

// Do a submission confirm
if(is_id($_GET['confirm']))
{
    $rsconfirmpage = mysql_query('SELECT * FROM QAFILLPAGE_LIST INNER JOIN QAFILL_LIST ON QAFILLPAGE_FILL=QAFILL_ID WHERE QAFILLPAGE_ID=' . $_GET['confirm'] . ' AND QAFILL_QA=' . $qa['QA_ID']);
    
    if($confirmpage = mysql_fetch_array($rsconfirmpage, MYSQL_ASSOC))
    {
     mysql_query('UPDATE QAFILLPAGE_LIST SET QAFILLPAGE_SIGCONFIRM=1 WHERE QAFILLPAGE_ID=' . $_GET['confirm']);
     header('location: http://' . DNAME . '/qa/qa.php?id=' . $_GET['id']);
    }
}

// Save answer key
if($_POST['type'] == 'saveform' && $showpage)
{
	$rsquestions = mysql_query('SELECT * FROM QAQUESTION_LIST LEFT JOIN QAFORMAT_LIST ON QAQUESTION_FORMAT=QAFORMAT_ID WHERE QAQUESTION_PAGE=' . $page['QAPAGE_ID'] . ' ORDER BY QAQUESTION_ORDER');

	while($ques = mysql_fetch_array($rsquestions, MYSQL_ASSOC))
	{
		$newvalue = $_POST['q' . $ques['QAQUESTION_ID']];
		
		if($showmode == 'questions')
		{
    		if($ques['QAQUESTION_TYPE'] == 'Select' || $ques['QAQUESTION_TYPE'] == 'Radio' || $ques['QAQUESTION_TYPE'] == 'Check')
    		{
    			mysql_query('UPDATE QAQUESTION_LIST SET QAQUESTION_DVAL_INT="' . $newvalue . '" WHERE QAQUESTION_ID=' . $ques['QAQUESTION_ID'] . ' LIMIT 1');
    		}
    		else if($ques['QAQUESTION_TYPE'] == 'Text' || $ques['QAQUESTION_TYPE'] == 'Longtext')
    		{
    			mysql_query('UPDATE QAQUESTION_LIST SET QAQUESTION_DVAL_TEXT="' . $newvalue . '" WHERE QAQUESTION_ID=' . $ques['QAQUESTION_ID'] . ' LIMIT 1');
    		}
		}
		else if($qa['QA_ISGRADED'])
		{
    		if($ques['QAQUESTION_TYPE'] == 'Select' || $ques['QAQUESTION_TYPE'] == 'Radio' || $ques['QAQUESTION_TYPE'] == 'Check')
    		{
    			mysql_query('UPDATE QAQUESTION_LIST SET QAQUESTION_ANSWER_INT="' . $newvalue . '" WHERE QAQUESTION_ID=' . $ques['QAQUESTION_ID'] . ' LIMIT 1');
    			mysql_query('UPDATE QARESP_LIST SET QARESP_PTS=IF(QARESP_RESP_INT=' . $newvalue . ',' . $ques['QAQUESTION_PTS'] . ',0) WHERE QARESP_QUESTION=' . $ques['QAQUESTION_ID']);
    		}
    		else if($ques['QAQUESTION_TYPE'] == 'Text')
    		{
    			mysql_query('UPDATE QAQUESTION_LIST SET QAQUESTION_ANSWER_TEXT="' . $newvalue . '" WHERE QAQUESTION_ID=' . $ques['QAQUESTION_ID'] . ' LIMIT 1');
    			mysql_query('UPDATE QARESP_LIST SET QARESP_PTS=IF(QARESP_RESP_TEXT="' . $newvalue . '",' . $ques['QAQUESTION_PTS'] . ',0) WHERE QARESP_QUESTION=' . $ques['QAQUESTION_ID']);
    		}		
		}
	}
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
	<head>
		<title><? print htmlentities($qa['QA_TITLE']); ?>: Administrative Options</title>
		<link rel="stylesheet" type="text/css" href="../../shs.css">
		<link rel="stylesheet" type="text/css" href="../qa.css">
		<style type="text/css">
			a.lnkh           { font-weight: bold }
			#statustable { table-layout: fixed; width: 600px; font-size: small }
			#statustable td { vertical-align: top; padding: 5px; border: 1px solid #999 }
			#statustable td.curstatus { vertical-align: top; padding: 3px; border: 1px solid #666; background-color: #ccc }
			#statustable td p { margin: 0; }
			#statustable td p:first-child { font-weight: bold; font-size: medium }
		</style>
	</head>
	<body>
		<? include "inc-header.php"; ?>
		<? if($showitem) { ?>
		<h1 class="titlebar"><span style="float: right; font-size: medium; padding: 2px"><a href="../qa.php?id=<?= $qa['QA_ID'] ?>">View</a> | Administer</span><span style="font-size: large"><a href="group.php?id=<?= $qa['QAGROUP_ID'] ?>&amp;sortstr=<?= $_GET['sortstr'] ?>"><?= $qa['QAGROUP_TITLE'] ?></a>:</span> <?= htmlentities($qa['QA_TITLE']) ?></h1>
		<table width="100%" cellpadding="3" cellspacing="0"><tr>
		<td style="vertical-align: top; background-color: #dddddd; width: 225px; font-size: small">
		<?
		// Preload all the pages so we can print them quickly
		$rspages = mysql_query('SELECT * FROM QAPAGE_LIST WHERE QAPAGE_QA=' . $qa['QA_ID'] . ' ORDER BY QAPAGE_ORDER');
		$numpages = 0;
		while($temppage = mysql_fetch_array($rspages, MYSQL_ASSOC))
		{
			$pagearr[$numpages] = $temppage;
			$numpages++;
		}
		
		print '<h2 class="grayheading">Edit Form</h2><div class="hcontent">';
		
		if(!$showpage && $showmode == 'questions')
			print '<div class="selpage"><span style="font-weight: bold">General Settings</span></div>';
		else
			print '<div class="nselpage"><a href="qa.php?id=' . $_GET['id'] . '&amp;mode=questions">General Settings</a></div>';

		// Edit form if form hasn't been opened yet
		if($qa['QA_OPEN'] == 0)
		{
    		for($i = 0; $i < $numpages; $i++)
    		{
    			$thispage = $showpage && $showmode == 'questions' && $page['QAPAGE_ID'] == $pagearr[$i]['QAPAGE_ID'];
    			print '<div class="' . ($thispage ? '' : 'n') . 'selpage">' . $pagearr[$i]['QAPAGE_ORDER'] . ' ' . ($thispage ? '<span style="font-weight: bold">' : '<a href="qa.php?id=' . $_GET['id'] . '&amp;mode=questions&amp;page=' . $pagearr[$i]['QAPAGE_ID'] . '">') . htmlentities($pagearr[$i]['QAPAGE_TITLE']) . ($thispage ? '</span>' : '</a>') . '</div>';
    		}
    
    		print '<div class="nselpage"><a href="newpage.php?id=' . $_GET['id'] . '">New Page...</a></div>';

		}

		print '</div>';
		
		// Answer key if form is graded
		if($qa['QA_ISGRADED'])
		{
    		print '<h2 class="grayheading">Answer Key</h2><div class="hcontent">';
    		
    		for($i = 0; $i < $numpages; $i++)
    		{
    			$thispage = $showpage && $showmode == 'key' && $page['QAPAGE_ID'] == $pagearr[$i]['QAPAGE_ID'];
    			print '<div class="' . ($thispage ? '' : 'n') . 'selpage">' . $pagearr[$i]['QAPAGE_ORDER'] . ' ' . ($thispage ? '<span style="font-weight: bold">' : '<a href="qa.php?id=' . $_GET['id'] . '&amp;mode=key&amp;page=' . $pagearr[$i]['QAPAGE_ID'] . '">') . htmlentities($pagearr[$i]['QAPAGE_TITLE']) . ($thispage ? '</span>' : '</a>') . '</div>';
    		}

			print '</div>';
		}

		// View responses if form is open
		if($qa['QA_OPEN'] > 0)
		{
   			print '<h2 class="grayheading">View Responses...</h2><div class="hcontent">';

    		if(!$showpage && $showmode == 'answers')
    			print '<div class="selpage"><span style="font-weight: bold">by User</span></div>';
    		else
    			print '<div class="nselpage"><a href="qa.php?id=' . $_GET['id'] . '&amp;mode=answers">by User</a></div>';

			print '<div class="nselpage">by Page:</div>';
    			
    		for($i = 0; $i < $numpages; $i++)
    		{
    			$thispage = $showpage && $showmode == 'answers' && $page['QAPAGE_ID'] == $pagearr[$i]['QAPAGE_ID'];
    			print '<div class="' . ($thispage ? '' : 'n') . 'selpage">' . $pagearr[$i]['QAPAGE_ORDER'] . ' ' . ($thispage ? '<span style="font-weight: bold">' : '<a href="qa.php?id=' . $_GET['id'] . '&amp;mode=answers&amp;page=' . $pagearr[$i]['QAPAGE_ID'] . '">') . htmlentities($pagearr[$i]['QAPAGE_TITLE']) . ($thispage ? '</span>' : '</a>') . '</div>';
    		}
			
			print '</div>';
		}
		
		?>		
		</td>
		<td style="font-size: medium; vertical-align: top">
	
		<?
			
		if($showpage)
		{
			?>
			<form id="qform" method="POST" action="qa.php?id=<?= $qa['QA_ID'] ?>&amp;mode=<?= $showmode ?>&amp;page=<?= $page['QAPAGE_ID'] ?>" style="margin: 0px">
			<?
			// Form heading
			print '<h2 class="blueheading">';
			if($showmode == 'questions')
				print 'Edit Form';
			else if($showmode == 'key')
				print 'Answer Key';
			else
   				print 'Response Breakdowns';
			print ', Page ' . $page['QAPAGE_ORDER'] . ': ' . htmlentities($page['QAPAGE_TITLE']) . '</h2>';

			$showanswers = $showmode == 'answers';
			$showform = $showmode != 'answers';
			
			$rsquestions = mysql_query('SELECT * FROM QAQUESTION_LIST LEFT JOIN QAFORMAT_LIST ON QAQUESTION_FORMAT=QAFORMAT_ID WHERE QAQUESTION_PAGE=' . $page['QAPAGE_ID'] . ' ORDER BY QAQUESTION_ORDER');
			$numquestions = mysql_num_rows($rsquestions);
			
			if($qa['QA_OPEN'] == 0)
			{
    			print '<p style="font-size: small">';
    			print '<span class="toolbar"><a href="newquestion.php?id=' . $page['QAPAGE_ID'] . '">Add Question</a></span>';
    			if($numquestions == 0)
    			{
    				print ' <span class="toolbar"><a href="qa.php?id=' . $_GET['id'] . '&amp;delpage=' . $_GET['page'] . '" onclick="return window.confirm(\'Are you sure you want to delete this page? This action cannot be undone.\');">Delete This Page</a></span>';
    			}
    			print '</p>';
			}
			
            while($ques = mysql_fetch_array($rsquestions, MYSQL_ASSOC))
            {
				if($showmode == 'questions')
				{
                	if($ques['QAQUESTION_TYPE'] == 'Select' || $ques['QAQUESTION_TYPE'] == 'Radio' || $ques['QAQUESTION_TYPE'] == 'Check')
                		$defaultvalue = $ques['QAQUESTION_DVAL_INT'];
                	else
                		$defaultvalue = $ques['QAQUESTION_DVAL_TEXT'];					
				}
				else
				{
                	if($ques['QAQUESTION_TYPE'] == 'Select' || $ques['QAQUESTION_TYPE'] == 'Radio' || $ques['QAQUESTION_TYPE'] == 'Check')
                		$defaultvalue = $ques['QAQUESTION_ANSWER_INT'];
                	else
                		$defaultvalue = $ques['QAQUESTION_ANSWER_TEXT'];
				} 
            
				if($ques['QAQUESTION_TYPE'] == 'Radio' || $ques['QAQUESTION_TYPE'] == 'Longtext' || ($ques['QAQUESTION_TYPE'] == 'Select' && $ques['QAQUESTION_SIZE'] > 1))
            		$tdalign = 'vertical-align: top; ';
            	else
            		$tdalign = '';
            
            	if($ques['QAQUESTION_HEADING'] != '')
            		print '<h3>' . $ques['QAQUESTION_HEADING'] . '</h3>';
            
            	print '<table cellpadding="3" cellspacing="0" style="font-size: medium" width="100%">';
        
            	if($ques['QAQUESTION_TYPE'] == 'Check')
            	{
            		print '<tr>';
            		print '<td style="width: 2em"><input ' . ($showform ? '' : 'disabled ') . ($defaultvalue ? 'checked ' : '');
            		print 'type="checkbox" name="q' . $ques['QAQUESTION_ID'] . '" id="checkq' . $ques['QAQUESTION_ID'] . '" value="1"></td>';
            		print '<td style="';
            		if($ques['QAQUESTION_REQD'])
            			print 'font-weight: bold; ';
            		print '"><p style="margin: 0"><label for="checkq' . $ques['QAQUESTION_ID'] . '"';
            		print '>' . $ques['QAQUESTION_PROMPT'] . '</label></p>';
					if($showmode == 'questions')
						print '<p style="margin: 0; font-size: x-small"><a href="custexam.php?id=' . $_GET['id'] . '&amp;ques=' . $ques['QAQUESTION_ID'] . '">Custom Example</a><br><a href="qa.php?id=' . $_GET['id'] . '&amp;mode=questions&amp;page=' . $_GET['page'] . '&amp;delques=' . $ques['QAQUESTION_ID'] . '" onclick="return window.confirm(\'Are you sure you want to delete this question? This action cannot be undone.\');">Delete Question</a></p>';
					print '</td>';
            		print '</tr>';
            	}
            	else
            	{
            		print '<tr>';
            		print '<td style="width: 12em; ' . $tdalign;
            		if($ques['QAQUESTION_REQD'] || $errors[$ques['QAQUESTION_ID']])
            			print 'font-weight: bold; ';
            		print '"><p style="margin: 0">' . $ques['QAQUESTION_PROMPT'] . '</p>';
					if($showmode == 'questions')
						print '<p style="margin: 0; font-size: x-small"><a href="custexam.php?id=' . $_GET['id'] . '&amp;ques=' . $ques['QAQUESTION_ID'] . '">Custom Example</a><br><a href="qa.php?id=' . $_GET['id'] . '&amp;mode=questions&amp;page=' . $_GET['page'] . '&amp;delques=' . $ques['QAQUESTION_ID'] . '" onclick="return window.confirm(\'Are you sure you want to delete this question? This action cannot be undone.\');">Delete Question</a></p>';
					print '</td>';
            		print '<td style="' . $tdalign . '">';

            		if($ques['QAQUESTION_TYPE'] == 'Text')
            		{
            			print '<input ' . ($showform ? '' : 'disabled ') . 'type="text" style="width: ' . $ques['QAQUESTION_SIZE'] . 'em" name="q' . $ques['QAQUESTION_ID'] . '" value="' . htmlentities($defaultvalue) . '">';
            		}
            		else if($ques['QAQUESTION_TYPE'] == 'Longtext')
            		{
            			print '<textarea ' . ($showform ? '' : 'disabled ') . 'style="width: 20em" rows="' . $ques['QAQUESTION_SIZE'] . '" name="q' . $ques['QAQUESTION_ID'] . '">' . htmlentities($defaultvalue) . '</textarea>';
            		}
            		else if($ques['QAQUESTION_TYPE'] == 'Select')
            		{
            			print '<select ' . ($showform ? '' : 'disabled ') . 'name="q' . $ques['QAQUESTION_ID'] . '" size="' . $ques['QAQUESTION_SIZE'] . '">';
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
            				print '<div><input ' . ($showform ? '' : 'disabled ');
            				if($defaultvalue == $option['QAMC_ID'])
            					print 'checked ';
            				print 'type="radio" name="q' . $ques['QAQUESTION_ID'] . '" id="radioq' . $option['QAMC_ID'] . '" value="' . $option['QAMC_ID'] . '"> <label for="radioq' . $option['QAMC_ID'] . '">' . htmlentities($option['QAMC_TEXT']) . '</label></div>';
            			}
            		}
            		print '</td>';
            		print '</tr>';						
            	}

            	if($showmode == 'answers')
				{
                	if($ques['QAQUESTION_TYPE'] == 'Check')
                	{
                		$rsresponses = mysql_query('SELECT QARESP_RESP_INT, COUNT(QARESP_ID) AS C FROM QARESP_LIST WHERE QARESP_QUESTION=' . $ques['QAQUESTION_ID'] . ' GROUP BY QARESP_RESP_INT ORDER BY C DESC');
                		
                		if(mysql_num_rows($rsresponses) > 0)
                		{
                			print '<tr><td></td><td style="vertical-align: top"><table style="border: 2px solid #cc3333; font-size: small; table-layout: fixed" cellpadding="2" cellspacing="0">';
                			$max = 0;
                			
                			$rstotals = mysql_query('SELECT COUNT(QARESP_ID) AS C FROM QARESP_LIST WHERE QARESP_QUESTION=' . $ques['QAQUESTION_ID']);
                			$totals = mysql_fetch_array($rstotals, MYSQL_ASSOC);
                			$restotal = $totals['C'];
                			print '<tr style="font-weight: bold; background-color: #ff9999"><td style="width: 4em">Total</td><td style="width: 3em; text-align: right">' . $restotal . '</td><td style="width: 100px"></td><td style="width: 3em; text-align: right">100%</td></tr>';												
                			while($response = mysql_fetch_array($rsresponses))
                			{
                				if($response['C'] > $max)
                					$max = $response['C'];
                				print '<tr><td>';
                
                				if($response['QARESP_RESP_INT'] == 1)
                					print '<img align="absmiddle" src="../imgs/finished.gif">';
                				else
                					print '<img align="absmiddle" src="../imgs/undone.gif">';
                
                				print '</td><td style="text-align: right">' . $response['C'] . '</td><td>';
                				if($response['C'] > 0)
                				{
                					print '<div style="';
                					if($response['QARESP_RESP_INT'] == $defaultvalue)
                						print 'background-color: #008000;';
                					else
                						print 'background-color: #c0c0c0;';
                					print 'width: ' . floor(90 * $response['C'] / $max) . 'px">&nbsp;</div>';
                				}
                				print '</td><td style="text-align: right">' . floor($response['C']/$restotal*100) . '%</td></tr>';
                			}
                			print '</table></td></tr>';
                		}
                	}
                	else if($ques['QAQUESTION_TYPE'] == 'Radio' || $ques['QAQUESTION_TYPE'] == 'Select')
                	{
                		$rsresponses = mysql_query('SELECT QAMC_ID, QAMC_TEXT, COUNT(QARESP_ID) AS C FROM QAMC_LIST LEFT JOIN QARESP_LIST ON QARESP_RESP_INT=QAMC_ID AND QARESP_QUESTION=QAMC_QUESTION WHERE QAMC_QUESTION=' . $ques['QAQUESTION_ID'] . ' GROUP BY QAMC_ID ORDER BY C DESC, QAMC_ORDER');
                		
                		if(mysql_num_rows($rsresponses) > 0)
                		{
                			print '<tr><td style="font-weight: bold; font-size: small; vertical-align: top; text-align: right; padding: 6px"></td><td style="vertical-align: top"><table style="border: 2px solid #cc3333; font-size: small; table-layout: fixed" cellpadding="2" cellspacing="0">';
                			$max = 0;
                			
                			$rstotals = mysql_query('SELECT COUNT(QARESP_ID) AS C FROM QAMC_LIST LEFT JOIN QARESP_LIST ON QARESP_RESP_INT=QAMC_ID AND QARESP_QUESTION=QAMC_QUESTION WHERE QAMC_QUESTION=' . $ques['QAQUESTION_ID']);
                			
                			$totals = mysql_fetch_array($rstotals, MYSQL_ASSOC);
                			$restotal = $totals['C'];
                			print '<tr style="font-weight: bold; background-color: #ff9999"><td style="width: 10em">Total</td><td style="width: 3em; text-align: right">' . $restotal . '</td><td style="width: 100px"></td><td style="width: 3em; text-align: right">100%</td></tr>';												
                			while($response = mysql_fetch_array($rsresponses))
                			{
                				if($response['C'] > $max)
                					$max = $response['C'];
                				print '<tr><td style="width: 150px">';
                				if(is_null($response['QAMC_TEXT']))
                					print '<span style="font-style: italic">Blank</span>';
                				else
                					print $response['QAMC_TEXT'];
                				print '</td><td style="text-align: right">' . $response['C'] . '</td><td>';
                				if($response['C'] > 0)
                				{
                					print '<div style="';
                					if($response['QAMC_ID'] == $defaultvalue)
                						print 'background-color: #008000;';
                					else
                						print 'background-color: #c0c0c0;';
                					print 'width: ' . floor(90 * $response['C'] / $max) . 'px">&nbsp;</div>';
                				}
                				print '</td><td style="text-align: right">' . floor($response['C']/$restotal*100) . '%</td></tr>';
                			}
                			print '</table></td></tr>';
                		}
                	}
				}
            	
			if($ques['QAQUESTION_SAMPLE'] != NULL && $showmode == 'questions')
            		print '<tr><td></td><td class="sampletd">' . $ques['QAQUESTION_SAMPLE'] . '</td></tr>';				
            	else if($ques['QAFORMAT_SAMPLE'] && $showmode == 'questions')
            		print '<tr><td></td><td class="sampletd">' . $ques['QAFORMAT_SAMPLE'] . '</td></tr>';

            	print '</table>';

            	if($ques['QAQUESTION_DESC'] != '' && $showmode == 'questions')
            		print '<div style="padding-left: 3em; padding-top: 3px; padding-right: 3px; padding-bottom: 3px; font-size: small;">' . $ques['QAQUESTION_DESC'] . '</div>';
            }

			if($showform && $numquestions > 0)
			{
				print '<p><input type="hidden" name="pageno" value="' . $page['QAPAGE_ID'] . '"><input type="hidden" name="type" value="saveform">';
				if($showmode == 'questions')
					print '<input type="submit" name="btn" value="Save Default Answers">';
				else
					print '<input type="submit" name="btn" value="Save Answer Key">';
				print '</p>';
			}
			print '</form>';
		}
		else if($showmode == 'answers')
		{
			if($_GET['sortstr'] == 'score')
				$orderstr = 'S DESC, USER_LN, USER_FN';
			else
				$orderstr = 'USER_LN, USER_FN, USER_ID, QAFILL_ID';
		
			$rsfills = mysql_query('SELECT USER_FULLNAME, QAFILL_IP, USER_ID, QAFILL_ID, SUM(QARESP_PTS) AS S FROM QAFILL_LIST LEFT JOIN USER_LIST ON USER_ID=QAFILL_USER LEFT JOIN QAFILLPAGE_LIST ON QAFILLPAGE_FILL=QAFILL_ID LEFT JOIN QARESP_LIST ON QARESP_FILLPAGE=QAFILLPAGE_ID WHERE QAFILL_QA=' . $qa['QA_ID'] . ' GROUP BY QAFILL_ID ORDER BY ' . $orderstr);
			
			if(mysql_num_rows($rsfills) > 0)
			{
				print '<h2 class="grayheading">' . mysql_num_rows($rsfills) . ' response';
				if(mysql_num_rows($rsfills) != 1)
					print 's';
				print '</h2>';
				
				print '<div class="hcontent">';

				if($qa['QA_ISGRADED'])
				{
					if($_GET['sortstr'] == 'score')
						print '<p><span style="">Sort by</span>: <a href="qa.php?id=' . $qa['QA_ID'] . '&amp;mode=answers&amp;sortstr=name">Name</a> | <span style="font-weight: bold">Score</span></p>';
					else
						print '<p><span style="">Sort by</span>: <span style="font-weight: bold">Name</span> | <a href="qa.php?id=' . $qa['QA_ID'] . '&amp;mode=answers&amp;sortstr=score">Score</a></p>';
				}

				print '<table cellpadding="2" cellspacing="0" style="font-size: small; table-layout: fixed">';
				
				print '<tr style="background-color: #dddddd; font-weight: bold;"><td style="width: 12em">Page Number</td>';
				$rsfillpages = mysql_query('SELECT * FROM QAPAGE_LIST WHERE QAPAGE_QA=' . $qa['QA_ID'] . ' ORDER BY QAPAGE_ORDER');
				while($fillpages = mysql_fetch_array($rsfillpages, MYSQL_ASSOC))
				{
					print '<td style="text-align: right; width: 2em">' . $fillpages['QAPAGE_ORDER'] . '</td>';
				}
				if($qa['QA_ISGRADED'])
					print '<td style="text-align: right; font-weight: bold; width: 4em">Total</td>';
				print '</tr>';

				if($qa['QA_ISGRADED'])
				{
					print '<tr style="color: #800000; background-color: #eeeeee"><td>Maximum</td>';
					$rsfillpages = mysql_query('SELECT SUM(QAQUESTION_PTS) AS S FROM QAQUESTION_LIST INNER JOIN QAPAGE_LIST ON QAPAGE_ID=QAQUESTION_PAGE WHERE QAPAGE_QA=' . $qa['QA_ID'] . ' GROUP BY QAQUESTION_PAGE ORDER BY QAQUESTION_PAGE');
					$s = 0;
					while($fillpages = mysql_fetch_array($rsfillpages, MYSQL_ASSOC))
					{
						print '<td style="text-align: right">' . $fillpages['S'] . '</td>';
						$s += $fillpages['S'];
					}
					print '<td style="text-align: right; font-weight: bold">' . $s . '</td></tr>';
				}
				
				while($thisfill = mysql_fetch_array($rsfills, MYSQL_ASSOC))
				{
					print '<tr><td>';
					print '<a href="../fillview.php?fill=' . $thisfill['QAFILL_ID'] . '">';
					if(is_null($thisfill['USER_ID']))
						print $thisfill['QAFILL_IP'];
					else
						print $thisfill['USER_FULLNAME'];
					print '</a>';
					print '</td>';
					// print '<td>' . $thisfill['QAFILL_ID'] . '</td>';
					
					$rsfillpages = mysql_query('SELECT QAPAGE_LIST.*, QAFILLPAGE_LIST.*, SUM(QARESP_PTS) AS S FROM QAPAGE_LIST LEFT JOIN QAFILLPAGE_LIST ON QAFILLPAGE_FILL=' . $thisfill['QAFILL_ID'] . ' AND QAFILLPAGE_PAGE=QAPAGE_ID
LEFT JOIN QARESP_LIST ON QARESP_FILLPAGE=QAFILLPAGE_ID WHERE QAPAGE_QA=' . $qa['QA_ID'] . ' GROUP BY QAPAGE_ID ORDER BY QAPAGE_ORDER');

					while($fillpages = mysql_fetch_array($rsfillpages, MYSQL_ASSOC))
					{
						print '<td style="text-align: right; ';
						if($fillpages['QAFILLPAGE_SAVED'])
							print 'color: #0000a0';
						else
							print 'color: #aaaaaa';
						print '">';
						if(!is_null($fillpages['QAFILLPAGE_ID']))
						{
							if(!is_null($fillpages['QAPAGE_SIGPROMPT']) && $fillpages['QAFILLPAGE_SIGCONFIRM'])
								print '<a href="qa.php?id=' . $qa['QA_ID'] . '&amp;confirm=' . $fillpages['QAFILLPAGE_ID'] . '">U</a>';
							
							if($qa['QA_ISGRADED'])
								print $fillpages['S'];
							else
								print '<img align="absmiddle" src="../imgs/finished.gif">';
								
							// print ' ID#' . $fillpages['QAFILLPAGE_ID'];
						}
						print '</td>';
					}
					if($qa['QA_ISGRADED'])
						print '<td style="font-weight: bold; text-align: right">' . $thisfill['S'] . '</td>';
					print '</tr>';					
				}
				print '</table>';
				
				print '</div>';
			}
		}
		else if($showmode == 'questions')
		{
			print '<h2 class="grayheading">Form Status</h2>';
			
			print '<table cellpadding="2" cellspacing="1"><tr><td style="font-weight: bold; vertical-align: top">Current Status</td><td>';
			
			if($qa['QA_OPEN'] == 0)
				print '1. Under Construction<br><a href="qa.php?id=' . $_GET['id'] . '&amp;newstatus=1">Make Active</a>';
			else if($qa['QA_OPEN'] == 1)
			{
				print '2. Active<br>';
				if($numfills == 0)
					print '<a href="qa.php?id=' . $_GET['id'] . '&amp;newstatus=0">Deactivate This Form</a><br>';
				else
					print '<a href="qa.php?id=' . $_GET['id'] . '&amp;newstatus=2">Close This Form</a><br>';
				print '<a href="qa.php?id=' . $_GET['id'] . '&amp;newstatus=0">Make This Form Under Construction</a>';
			}
			else
				print '3. Closed<br><a href="qa.php?id=' . $_GET['id'] . '&amp;newstatus=1">Re-open</a>';

			print '</td></tr></table>';
			
			?>
			<table id="statustable"><tr><td>
			<p>1. Under Construction</p><p>You design your form. Click <em>New Page...</em> to add a page to this form, or click on an existing page name to edit it.<?= ($qa['QA_ISGRADED'] ? " You can also add an answer key at this time if you'd like." : '') ?></p>
			</td><td>
			<p>2. Active</p><p>People fill out your form. <?= ($qa['QA_ISGRADED'] ? "You can't change the questions at this time, but you can change the answer key. " : '') ?>While the form is active, you can keep track of responses in real time.</p>
			</td><td>
			<p>3. Closed</p><p>After you close the form, you can finalize everything and examine the results.</p>
			</td></tr></table>
			<form action="qa.php?id=<?= $_GET['id'] ?>" method="POST">
			
			<table cellpadding="2" cellspacing="1">
			
			<tr><td style="font-weight: bold">Require Invitations?</td><td>
			<label><input type="radio" name="invitation" value="1" <? if($qa['QA_PRIVATE']==1) print 'checked'; ?>> Yes</label>
			<label><input type="radio" name="invitation" value="0" <? if($qa['QA_PRIVATE']==0) print 'checked'; ?>> No</label>
			</td></tr>
			
			<tr><td style="font-weight: bold">Allow Anonymous Submissions?</td><td>
			<label><input type="radio" name="anonymous" value="1" <? if($qa['QA_ALLOW_ANONYMOUS']==1) print 'checked'; ?>> Yes</label>
			<label><input type="radio" name="anonymous" value="0" <? if($qa['QA_ALLOW_ANONYMOUS']==0) print 'checked'; ?>> No</label>
			</td></tr>
			
			<tr><td style="font-weight: bold">Maximum Number of Submissions</td><td>
			<select name="fill_limit">
			<?
			for($i=1;$i<=10;$i++)
			{
				if($i == $qa['QA_FILLLIMIT'])
					print '<option value="' . $i . '" selected>' . $i . "\n";
				else
					print '<option value="' . $i . '">' . $i . "\n";
			}

			if(is_null($qa['QA_FILLLIMIT']))
				print '<option value="NULL" selected>No Limit' . "\n";
			else
				print '<option value="NULL">No Limit' . "\n";
			?>
			</td></tr>
			
			<tr><td style="font-weight: bold">Allow Editing After Saving</td><td>
			<label><input type="radio" name="savelimit" value="0" <? if($qa['QA_SAVELIMIT']==0) print 'checked'; ?>> Yes</label>
			<label><input type="radio" name="savelimit" value="1" <? if($qa['QA_SAVELIMIT']==1) print 'checked'; ?>> No</label>			</td></tr>
			<tr><td><input type="hidden" name="action" value="saveform"><input type="submit" name="saveform" value="Save"></form></td></tr>			
			</table>
		<? } ?>
		</td></tr></table>
		<? include '../../inc-footer.php'; ?>
		<? } ?>
	</body>
</html>
