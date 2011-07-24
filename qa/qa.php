<?
include '../db.php';

$isauthor = false;
$showitem = false;

if(is_numeric($_GET['id']))
{
	if($_POST['action'] == 'newexport' && $isadmin)
	{
		$_POST['exporter'];
	}

	// Load QA and user's fills
	$rsqa = mysql_query('SELECT * FROM QA_LIST
		INNER JOIN QAGROUP_LIST ON QA_GROUP=QAGROUP_ID
		WHERE QA_ID=' . $_GET['id']);
	
	if($qa = mysql_fetch_array($rsqa, MYSQL_ASSOC))
	{
		if($loggedin || $qa['QA_ALLOW_ANONYMOUS'])
		{
			$showitem = true;
		
    		// Check if user is an author
			if($loggedin)
    		{
        		$rsauthor = mysql_query('SELECT * FROM QAAUTHOR_LIST WHERE QAAUTHOR_QAGROUP=' . $qa['QA_GROUP'] . ' AND QAAUTHOR_USER=' . $userid);
        		if($author = mysql_fetch_array($rsauthor, MYSQL_ASSOC))
        		{
        			$isauthor = true;
        			
        			// Confirm someone's submission
        			if(is_numeric($_GET['confirm']))
        			{
        				$rsconfirmpage = mysql_query('SELECT * FROM QAFILLPAGE_LIST INNER JOIN QAFILL_LIST ON QAFILLPAGE_FILL=QAFILL_ID WHERE QAFILLPAGE_ID=' . $_GET['confirm'] . ' AND QAFILL_QA=' . $qa['QA_ID']);
        				
        				if($confirmpage = mysql_fetch_array($rsconfirmpage, MYSQL_ASSOC))
        				{
        					mysql_query('UPDATE QAFILLPAGE_LIST SET QAFILLPAGE_SIGCONFIRM=1 WHERE QAFILLPAGE_ID=' . $_GET['confirm']);
        					header('location: http://' . DNAME . '/qa/qa.php?id=' . $_GET['id']);
        				}
        			}
        		}
    		}
    		
    		// Create new fill if user asks for it or AUTOSTART is on, but only if the form is currently OPEN.
    		if($loggedin && ($_GET['fill'] == 'new' || ($qa['QA_AUTOSTART'] == 1 && !$isauthor)) && $qa['QA_OPEN'] == 1)
    		{
    			// Check number of existing fills
   				$rsnumfills = mysql_query('SELECT COUNT(*) FROM QAFILL_LIST WHERE QAFILL_USER=' . $userid . ' AND QAFILL_QA=' . $qa['QA_ID']);

    			$numfills = mysql_fetch_array($rsnumfills, MYSQL_ASSOC);
    			if( ($numfills['COUNT(*)'] < $qa['QA_FILLLIMIT'] || is_null($qa['QA_FILLLIMIT'])) && ($qa['QA_AUTOSTART'] == 0 || ($qa['QA_AUTOSTART'] == 1 && $numfills['COUNT(*)'] == 0) ) )
    			{
					// Insert new QAFILL				
   					mysql_query('INSERT INTO QAFILL_LIST (QAFILL_USER, QAFILL_QA, QAFILL_CHILD, QAFILL_START, QAFILL_FINISH) VALUES ('. $userid . ', '. $_GET['id'] . ', "' . $_GET['child'] . '", "' . date(TIME_FORMAT_SQL, CURRENT_TIME) . '", Null)');

    				$newfill = mysql_insert_id();
    				// Redirect to new page
    				header('location: http://' . DNAME . '/qa/qa.php?id=' . $qa['QA_ID'] . '&fill=' . $newfill);
    			}
    		}
    		
    		// If there is only one fill, open it
		if($loggedin)
		{
			$rsfills = mysql_query('SELECT QAFILL_ID FROM QAFILL_LIST WHERE QAFILL_USER=' . $userid . ' AND QAFILL_QA=' . $qa['QA_ID']);
        		if($loggedin && mysql_num_rows($rsfills) == 1)
        		{
        			$fill = mysql_fetch_array($rsfills, MYSQL_ASSOC);
        			if($fill['QAFILL_ID'] != $_GET['fill'])
        				header('location: http://' . DNAME . '/qa/qa.php?id=' . $qa['QA_ID'] . '&fill=' . $fill['QAFILL_ID'] . (($_GET['closepage'] == 'true') ? '&closepage=true' : ''));
        		}
		}

    		// Delete a fill when asked to
		if($loggedin && is_numeric($_GET['delfill']) && $qa['QA_SAVELIMIT'] == 0)
    		{
    			$rsfill = mysql_query('SELECT * FROM QAFILL_LIST WHERE QAFILL_USER=' . $userid . ' AND QAFILL_ID=' . $_GET['delfill']);
    			if($fill = mysql_fetch_array($rsfill, MYSQL_ASSOC))
    			{
    				$rsfillpages = mysql_query('SELECT * FROM QAFILLPAGE_LIST INNER JOIN QAFILL_LIST ON QAFILLPAGE_FILL=QAFILL_ID WHERE QAFILL_USER=' . $userid . ' AND QAFILL_ID=' . $_GET['delfill']);
    				while($fillpage = mysql_fetch_array($rsfillpages))
    					mysql_query('DELETE FROM QARESP_LIST WHERE QARESP_FILLPAGE=' . $fillpage['QAFILLPAGE_ID']);
    				mysql_query('DELETE FROM QAFILLPAGE_LIST WHERE QAFILLPAGE_FILL=' . $_GET['delfill']);
    				mysql_query('DELETE FROM QAFILL_LIST WHERE QAFILL_ID=' . $_GET['delfill']);
    			}
    			
    			header('location: http://' . DNAME . '/qa/qa.php?id=' . $_GET['id']);
    		}
    		
    		// Per child forms
		if($loggedin && $qa['QA_PERCHILD'])
    		{
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
    		}
		}
		else
			forceLogin();
	}
}

if(!$showitem)
	die();

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
	<head>
		<title><? print htmlentities($qa['QA_TITLE']); ?></title>
		<meta name="GENERATOR" content="Microsoft Visual Studio.NET 7.0">
		<meta name="vs_targetSchema" content="http://schemas.microsoft.com/intellisense/ie5">
		<link rel="stylesheet" type="text/css" href="../shs.css">
		<link rel="stylesheet" type="text/css" href="qa.css">
		<style type="text/css">
			a.lnkh { font-weight: bold }
			div.redbox { margin-top: 1.5ex; background-color: #ffffff; border: 2px solid #cc0000; padding: 2px }
			div.bluebox { margin-top: 1.5ex; background-color: #ffffdd; border: 2px solid #0000aa; padding: 2px }
		</style>
	</head>
	<body onLoad="sframe = document.getElementById('searchid'); sframe.style.display='none';">
		<? include "inc-header.php";
		$group = $qa['QA_GROUP'];
?>
		<h1 class="titlebar"><?
				if($isauthor)
					print '<span style="float: right; font-size: medium; padding: 2px">View | <a href="edit/qa.php?id=' . $_GET['id'] . '">Administer</a></span>'
				?><span style="font-size: large"><a href="./?group=<?= $qa['QAGROUP_ID'] ?>"><?= $qa['QAGROUP_TITLE'] ?></a>:</span> <?= htmlentities($qa['QA_TITLE']) ?></h1>

		<? include "specials/inc-nav.php"; ?>
	
		<table width="100%" cellpadding="3" cellspacing="0"><tr>
		<td style="vertical-align: top; background-color: #dddddd; width: 225px">
		<?
		// Print description
		if($qa['QA_DESC'] != '')
			print '<h2 class="grayheading">Description</h2><div class="hcontent">' . htmlentities($qa['QA_DESC']) . '</div>';
			
		$i = 0;
		$fillopen = '';

		// Print list of all user's fills
		if($loggedin)
		{
			$rsfills = mysql_query('SELECT * FROM QAFILL_LIST WHERE QAFILL_USER=' . $userid . ' AND QAFILL_QA=' . $qa['QA_ID'] . ' ORDER BY QAFILL_START');

    		while($fill = mysql_fetch_array($rsfills, MYSQL_ASSOC))
    		{
    			$i++;
    			// Currently open
    			if($fill['QAFILL_ID'] == $_GET['fill'])
    			{
    				$fillopen = $_GET['fill'];
    				print '<h2 class="blueheading">';
    			}
    			else
    				print '<h2 class="redheading">';
    
    			if(mysql_num_rows($rsfills) > 1)
    				print 'Progress: Copy #' . $i;
    			else
    				print 'My Progress';
    				
    			print '</h2><div class="hcontent"';
    			if($fill['QAFILL_ID'] == $_GET['fill'])
    				print ' style="background-color: #ffffff"';
    			print '>';
    			
    			// Print arrows to the right, if there is more than one fill
    			if($fill['QAFILL_ID'] == $_GET['fill'])
    			{
    				if(mysql_num_rows($rsfills) > 1)
    					print '<div style="text-align: center; padding: 1px; margin: 1px; border: 1px solid #999999; background-color: #eeeeee; font-weight: bold; font-size: medium">Currently Open</div>';
    			}
    			else
    			{
    				print '<div style="text-align: center; padding: 1px; margin: 1px; border: 1px solid #999999; background-color: #eeeeee; font-size: medium"><a href="qa.php?id=' . $qa['QA_ID'] . '&amp;fill=' . $fill['QAFILL_ID'] . '">Open Copy #' . $i . '</a></div>';
    			}
    			
    			print '<table cellspacing="0" cellpadding="2">';
    			// print '<tr><td style="font-weight: bold">Pages</td><td>';
    			// Print page checkboxes
    			$rspages = mysql_query('SELECT * FROM QAPAGE_LIST LEFT JOIN QAFILLPAGE_LIST ON QAFILLPAGE_FILL=' . $fill['QAFILL_ID'] . ' AND QAFILLPAGE_PAGE=QAPAGE_ID AND QAFILLPAGE_SAVED=1 WHERE QAPAGE_QA=' . $qa['QA_ID'] . ' ORDER BY QAPAGE_ORDER');
    			$donepages = $undonepages = 0;
    			if(mysql_num_rows($rspages) > 0)
    				while($page = mysql_fetch_array($rspages, MYSQL_ASSOC))
    				{
    					if(!($page['QAFILLPAGE_SAVED'] == 1))
    					{
    						// print '<img align="absmiddle" src="imgs/undone.gif"> ';
    						$undonepages++;
    					}
    					else
    					{
    						// print '<img align="absmiddle" src="imgs/finished.gif"> ';
    						$donepages++;
    					}
    				}
    			// print '</td></tr>';
    				
    			// Print user's status
    			print '<tr><td style="font-weight: bold">Status</td><td>';
    			if($donepages == 0)
    				$fillmsg = 0;
    			else if($undonepages == 0)
    				$fillmsg = 2;
    			else
    				$fillmsg = 1;
    				
    			if($fillmsg == 2)
    				print '<img align="absmiddle" src="imgs/finished.gif"> ';			
    			else
    				print '<img align="absmiddle" src="imgs/undone.gif"> ';
    				
    			print $donepages . ' of ' . mysql_num_rows($rspages) . ' page';
    			if(mysql_num_rows($rspages) != 1)
    				print 's';
    			print ' saved';
    			
    			if($fill['QAFILL_ID'] == $_GET['fill'])
    				$curfillmsg = $fillmsg;
    				
    			print '</td></tr>';
    				
    			print '<tr><td style="font-weight: bold">Started</td><td>' . date('M j, h:i a', strtotime($fill['QAFILL_START'])) . '</td></tr>';
    			
    			if($fill['QAFILL_CHILD'])
    			{
    				if($cuser['~CHILD' . $fill['QAFILL_CHILD'] . '_FIRST_NAME'] || $cuser['~CHILD' . $fill['QAFILL_CHILD'] . '_LAST_NAME'])
    					print '<tr><td style="font-weight: bold">For</td><td>' . htmlentities($cuser['~CHILD' . $fill['QAFILL_CHILD'] . '_FIRST_NAME']) . ' ' . htmlentities($cuser['~CHILD' . $fill['QAFILL_CHILD'] . '_LAST_NAME']) . '</td></tr>';
    			}
    				
    			print '</table>';
    
				if($qa['QA_AUTOSTART'] == 1)
				{
					$deletetxt = "Clear This Form";
					$deletetxtwarning = "Are you sure want to clear this form?";
				}
				else
				{
					$deletetxt = "Delete This Copy";
					$deletetxtwarning = "Are you sure want to delete this copy of your form?";
				}
    			
				if($fill['QAFILL_ID'] == $_GET['fill'] && $qa['QA_SAVELIMIT'] == 0)
    				print '<div style="margin: 1px; padding: 1px; border: 1px solid #999999; text-align: center; background-color: #eeeeee"><a href="qa.php?id=' . $_GET['id'] . '&amp;delfill=' . $_GET['fill'] . '" onclick="return window.confirm(\'' . $deletetxtwarning . '\')">' . $deletetxt . '</a></div>';

    			print '</div>';
    		}
		}
		
		// Prompt to start a new copy
		if($qa['QA_OPEN'] == 1 && $loggedin)
		{
			if($i < $qa['QA_FILLLIMIT'] || is_null($qa['QA_FILLLIMIT']))
			{
				print '<h2 class="redheading">New Copy</h2>';
				print '<div class="hcontent">';				
				
				if($loggedin && $qa['QA_PERCHILD'])
				{
					
					$haschildren = false;
					for($i = 1; $i <= 4; $i++)
					{
						if($cuser['~CHILD' . $i . '_FIRST_NAME'] || $cuser['~CHILD' . $i . '_LAST_NAME'])
						{
							print '<div style="padding: 2px; margin: 2px; border: 1px solid #999999; background-color: #eeeeee"><a href="qa.php?id=' . $_GET['id'] . '&amp;fill=new&amp;child=' . $i . '">for ' . htmlentities($cuser['~CHILD' . $i . '_FIRST_NAME']) . ' ' . htmlentities($cuser['~CHILD' . $i . '_LAST_NAME']) . '</a></div>';
							$haschildren = true;
						}
					}

					if($haschildren)
						print '<div>Select a child to automatically fill in that child\'s information.</div>';
					
					print '<div style="padding: 2px; margin: 2px; border: 1px solid #999999; background-color: #eeeeee"><a href="qa.php?id=' . $qa['QA_ID'] . '&amp;fill=new">(Blank Form)</a></div>';
					print '<div>Click on Blank Form to start on a new copy of this form.';
					
					if($haschildren)
							print ' <strong>Use Blank Form only if the child you wish to fill out a form for is not listed above.</strong>';
							
					print '</div>';
				}
				else
				{
					if($i == 0)
						print '<a href="qa.php?id=' . $_GET['id'] . '&amp;fill=new">Start New Copy</a>: Click to start on a fresh copy of this form.';
					else
						print '<a href="qa.php?id=' . $_GET['id'] . '&amp;fill=new">Start New Copy</a>: Click to start on a fresh copy of this form. Your other copies will be saved.';
				}
				
				print '</div>';
			}
			else if($qa['QA_FILLLIMIT'] > 1)
				print '<h2 class="redheading">New Copy</h2><div class="hcontent">You are allowed to fill out up to ' . $qa['QA_FILLLIMIT'] . ' different copies of this form.</div>';
		}

		print '<h2 class="grayheading">Export</h2>';

		$export_user = mysql_query("SELECT * FROM EXPORT_LIST WHERE EXPORT_USER=$userid AND EXPORT_QA=" . $qa['QA_ID']);
		if($isadmin || mysql_fetch_array($export_user, MYSQL_ASSOC) )
		{
			$export_pages = mysql_query("SELECT * FROM QAPAGE_LIST WHERE QAPAGE_QA=" . $qa['QA_ID']);
			while($pline = mysql_fetch_array($export_pages,MYSQL_ASSOC))
			{
				print '<p><a href="export.php?id=' . $pline['QAPAGE_ID'] . '">' . $pline['QAPAGE_TITLE'] . '</a></p>';
			}

			if($isadmin)
				print 'New Exporter:<form name="addexport" action="qa.php?id=' . $qa['QA_ID'] . '" method="POST"><input type="text" name="exporter" onClick="loadSearchID();" size="6"><input type="hidden" name="action" value="newexport"><input type="submit" value="Go"></form>';
		}

		print '<h2 class="grayheading">' . htmlentities($qa['QAGROUP_TITLE']) . '</h2>';
		print '<div class="hcontent">';
		print '<p style="margin: 0; padding-bottom: 12px;"><a style="font-weight: bold" href="index.php?group=' . $qa['QAGROUP_ID'] . '">Return to <span>' . htmlentities($qa['QAGROUP_TITLE']) . '</span></a></p>';

		$rsallqas = mysql_query('SELECT * FROM QA_LIST WHERE QA_OPEN > 0 AND QA_GROUP=' . $qa['QAGROUP_ID'] . ' ORDER BY QA_ID');
		while($listqa = mysql_fetch_array($rsallqas, MYSQL_ASSOC))
		{
			print '<p style="margin: 0"><a href="qa.php?id=' . $listqa['QA_ID'] . '">' . $listqa['QA_TITLE'] . '</a></p>';

			$rsallfills = mysql_query('SELECT * FROM QAFILL_LIST WHERE QAFILL_QA=' . $listqa['QA_ID'] . ' AND QAFILL_USER=' . $userid . ' ORDER BY QAFILL_START');

			if(mysql_num_rows($rsallfills) > 1)
				print '<p style="margin: 0 0 0 1em">' . mysql_num_rows($rsallfills) . ' copies:</p>';
			if(mysql_num_rows($rsallfills) == 0)
				print '<p style="margin: 0 0 0 1em"><span style="color: #a00; font-weight: bold">not started</span></p>';

			while($listfill = mysql_fetch_array($rsallfills, MYSQL_ASSOC))
			{
				print '<p style="margin: 0 0 0 1em">';

				$rsallpages = mysql_query('SELECT * FROM QAPAGE_LIST LEFT JOIN QAFILLPAGE_LIST ON QAFILLPAGE_FILL=' . $listfill['QAFILL_ID'] . ' AND QAFILLPAGE_PAGE=QAPAGE_ID AND QAFILLPAGE_SAVED=1 WHERE QAPAGE_QA=' . $listqa['QA_ID'] . ' ORDER BY QAPAGE_ORDER');

				$donepages = $undonepages = 0;

				if(mysql_num_rows($rsallpages) > 0)
				{
					while($listpage = mysql_fetch_array($rsallpages, MYSQL_ASSOC))
					{
    						if(!($listpage['QAFILLPAGE_SAVED'] == 1))
    							$undonepages++;
    						else
    							$donepages++;
					}
				}

				if(mysql_num_rows($rsallfills) > 1)
					print 'one ';

				if($donepages == 0)
					print '<span style="color: #a00; font-weight: bold">not started</span>';
				else if ($undonepages == 0)
					print '<span style="font-weight: bold">finished</span>';
				else
					print '<span style="">in progress</span>';

				print '</p>';
			}
		}

		print '</div>';
		
		?>
		</td><td style="font-size: medium; vertical-align: top">
		<?
		// Print form status
		print '<h2 class="grayheading">';
		if($qa['QA_OPEN'] == 1)
			print '<span style="font-weight:bold">Status: Currently Active</span>';
		else if($qa['QA_OPEN'] == 0)
			print '<span style="font-weight:bold">Status: Under Construction</span>';
		else if($qa['QA_OPEN'] == 2)
			print '<span style="font-weight:bold">Status: Over</span>';
		print '</h2>';

		print '<div class="hcontent">';
		if($qa['QA_OPEN'] == 0)
			print '<div>This ' . strtolower($qa['QA_TYPE']) . ' isn\'t active yet.</div>';
		else if($fillopen)
		{
			if($qa['QA_OPEN'] == 1)
			{
				if($curfillmsg == 0)
					print '<div>Click on a page below to begin.</div>';
				else if($curfillmsg == 2)
					print '<div>Click on a page below to review your responses.</div>';
				else
					print '<div>Click on a page below to resume working.</div>';
			}
			else if($qa['QA_OPEN'] == 2 && $qa['QA_SHOWANSWERS'])
				print '<div>This ' . strtolower($qa['QA_TYPE']) . ' is no longer active. Click on a page to review your responses.</div>';
		}
		else
		{
			if($qa['QA_OPEN'] == 1)
				print '<div>You can complete this ' . strtolower($qa['QA_TYPE']) . ' right now.</div>';
			else if($qa['QA_OPEN'] == 2)
				print '<div>This ' . strtolower($qa['QA_TYPE']) . ' is no longer active.</div>';
		}
		print '</div>';

		if($_GET['closepage'] == 'true')
		{
			print '<div style="padding: 2em; margin: 2em; border: 3px solid #c00; background-color: #eee">';
			print '<p>';
			print 'The page or pages you just submitted have been saved' . (($curfillmsg == 2) ? ', and that completes this form.' : ', but this form isn\'t complete yet.');
			print '</p>';
			print '<ul style="font-weight: bold">';
			if($curfillmsg == 2)
			{
				print '<li>You can return to the pages listed below to double-check that everything is correct.</li>';
				print '<li>To start or continue work on another form, click on one of the links to the left.';
				if($qa['QA_PERCHILD'])
					print '<li>Remember, you should fill out one copy of this form for each of your children.</li>';
			}
			else
			{
				print '<li>Click on one of the pages below to continue.</li>';
				print '<li>Alternatively, click on one of the links to the left to continue with another form.</li>';
			}

			print '<li>If you\'re done with all your forms, you may proceed to <a href="specials/confirm.php?group=' . $qa['QAGROUP_ID'] . '">check your information</a>.</li>';

			print '</ul>';
			print '</div>';
		}
	
		if($fillopen)
			$rspages = mysql_query('SELECT * FROM QAPAGE_LIST LEFT JOIN QAFILLPAGE_LIST ON QAFILLPAGE_FILL=' . $fillopen . ' AND QAFILLPAGE_PAGE=QAPAGE_ID WHERE QAPAGE_QA=' . $qa['QA_ID'] . ' ORDER BY QAPAGE_ORDER');
		else
			$rspages = mysql_query('SELECT * FROM QAPAGE_LIST WHERE QAPAGE_QA=' . $qa['QA_ID'] . ' ORDER BY QAPAGE_ORDER');
		
		// Print list of pages
		if(mysql_num_rows($rspages) > 0)
		{
			print '<h2 class="grayheading">' . mysql_num_rows($rspages) . ' page';
			if(mysql_num_rows($rspages) != 1)
				print 's';
			print '</h2>';
		
			print '<div class="hcontent">';
			print '<table cellpadding="2" cellspacing="1" style="font-size: medium">';
			while($page = mysql_fetch_array($rspages, MYSQL_ASSOC))
			{
				print '<tr><td style="font-weight: bold">Page ' . $page['QAPAGE_ORDER'] . '</td>';
				if($fillopen)
				{
					if($page['QAFILLPAGE_SAVED'] == 1)
						print '<td><img src="imgs/finished.gif"></td>';
					else
						print '<td><img src="imgs/undone.gif"></td>';
				}
				print '<td>';
				if(($fillopen && $qa['QA_OPEN']))
					print '<a href="page.php?id=' . $page['QAPAGE_ID'] . '&amp;fill=' . $fillopen . '">';
				else if(!$loggedin)
					print '<a href="page.php?id=' . $page['QAPAGE_ID'] . '">';

				print htmlentities($page['QAPAGE_TITLE']);
				if(($fillopen && $qa['QA_OPEN']) || !$loggedin)
					print '</a>';
				print '</td>';
				print '</tr>';
			}
			print '</table>';
			print '</div>';
		}
		?>
		<iframe name="searchid" id="searchid" style="width: 600px; height: 400px; vertical-align: top; horizontal-align: left;">
		Your browser does not support iframes.
		</iframe>
<script type="text/javascript">
<!--
function loadSearchID()
{
	sframe = document.getElementById('searchid');
	sframe.src='/directory/search-id.php?form=addexport&formelement=exporter';
	sframe.style.display='block';
}
// -->
</script>
		</td></tr></table>
		<? include '../inc-footer.php'; ?>
	</body>
</html>

