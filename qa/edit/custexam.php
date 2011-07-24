<?
//custexam.php
include '../../db.php';

$isauthor = false;

if(is_numeric($_GET['id']) && is_numeric($_GET['ques']) && $loggedin)
{
	// Get information about QA
	$rsqa = mysql_query('SELECT * FROM QA_LIST INNER JOIN QAGROUP_LIST ON QA_GROUP=QAGROUP_ID WHERE QA_ID=' . $_GET['id'] . ' ORDER BY QA_TITLE');
	
	if($qa = mysql_fetch_array($rsqa, MYSQL_ASSOC))
	{
		// Check permissions
		$rsauthor = mysql_query('SELECT * FROM QAAUTHOR_LIST WHERE QAAUTHOR_QAGROUP=' . $qa['QA_GROUP'] . ' AND QAAUTHOR_USER=' . $userid);
		if($author = mysql_fetch_array($rsauthor, MYSQL_ASSOC))
		{
			$isauthor = true;
		}
	}

	$rsques = mysql_query('SELECT * FROM QAQUESTION_LIST INNER JOIN QAPAGE_LIST ON QAQUESTION_PAGE=QAPAGE_ID INNER JOIN QA_LIST ON QAPAGE_QA=QA_ID WHERE QAQUESTION_ID=' . $_GET['ques'] . ' AND QA_ID=' . $_GET['id']) or die("Could not select: " . mysql_error());
	if($ques = mysql_fetch_array($rsques, MYSQL_ASSOC))
	{
	}
	else
		die("No such question.");

	if($_POST['action']=='custexam')
	{
		$custexamtext = $_POST['custexamtext'];
		if(strlen($custexamtext) > 0)
			mysql_query("UPDATE QAQUESTION_LIST SET QAQUESTION_SAMPLE='$custexamtext' WHERE QAQUESTION_ID=" . $_GET['ques']) or die("Could not update: " . mysql_error());
		else
			mysql_query("UPDATE QAQUESTION_LIST SET QAQUESTION_SAMPLE=NULL WHERE QAQUESTION_ID=" . $_GET['ques']) or die("Could not update: " . mysql_error());

		header("location: qa.php?id=" . $qa['QA_ID'] . "&mode=questions&page=" . $ques['QAQUESTION_PAGE']);
	}
}
else if($loggedin)
{
	die();
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
		<h2 class="blueheading">Edit Custom Example</h2>
		<form action="custexam.php?id=<?=$_GET['id']?>&amp;ques=<?=$_GET['ques']?>" method="POST">
		<span style="font-weight: bold; font-size: bigger;">Custom Example</span><br>
		Blank for no custom example<br>
		<input type="text" name="custexamtext" value="<?=$ques['QAQUESTION_SAMPLE']?>" size=20><br>
		<input type="hidden" name="action" value="custexam"><input type="submit" name="custexam" value="Save">
		</form>
		</td></tr></table>
		<? include '../../inc-footer.php'; ?>
	</body>
</html>