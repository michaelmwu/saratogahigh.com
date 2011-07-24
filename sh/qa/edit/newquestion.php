<?
include '../../db.php';

$isauthor = false;

if(is_id($_GET['id']) && $loggedin)
{
	$rspage = mysql_query('SELECT * FROM QAPAGE_LIST INNER JOIN QA_LIST ON QAPAGE_QA=QA_ID INNER JOIN QAGROUP_LIST ON QA_GROUP=QAGROUP_ID WHERE QAPAGE_ID=' . $_GET['id']);
	
	if($page = mysql_fetch_array($rspage, MYSQL_ASSOC))
	{
		$showitem = true;
		
		if($page['QA_OPEN'] == 0)
		{
    		$rsauthor = mysql_query('SELECT * FROM QAAUTHOR_LIST WHERE QAAUTHOR_QAGROUP=' . $page['QA_GROUP'] . ' AND QAAUTHOR_USER=' . $userid);
    		if($author = mysql_fetch_array($rsauthor, MYSQL_ASSOC))
    		{
    			$isauthor = true;
				
    			$rsquestions = mysql_query('SELECT QAQUESTION_ID, QAQUESTION_ORDER, QAQUESTION_PROMPT FROM QAQUESTION_LIST WHERE QAQUESTION_PAGE=' . $_GET['id'] . ' ORDER BY QAQUESTION_ORDER ASC');
				$maxquestion = 0;

				while($cques = mysql_fetch_array($rsquestions, MYSQL_ASSOC))
				{
					if($maxquestion < $cques['QAQUESTION_ORDER'])
						$maxquestion = $cques['QAQUESTION_ORDER'];
						
					$quesarr[$cques['QAQUESTION_ORDER']] = $cques;
				}
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
	
$stepno = 0;

if($_POST['formsubmit'] == "yes")
{
    if(ereg('^(0|1|2|3|4|5|6)$', $_POST['mode']))
    	$stepno = $_POST['mode'];

	$isformatted = ereg('^format([1-9][0-9]*)$', $_POST['questype']);
	$firsttry = false;
	
	if($_POST['submit'] != "<< Previous")
		$datacheck = 0;
	else
		$datacheck = $stepno - 1;

	do
	{
        $dataok = false;
		$skippage = false;
		
        if($datacheck == 0)
        {
            if(ereg('^(qcheck|qmc|qt|qlt)$', $_POST['questype']))
            {
            	$dataok = true;
				
            	if($_POST['questype'] != 'qmc')
    				$stepno++;				
            }
            else if(ereg('^format([1-9][0-9]*)$', $_POST['questype'], $matches))
            {
            	$rsformats = mysql_query('SELECT * FROM QAFORMAT_LIST WHERE QAFORMAT_ID=' . $matches[1]);
            	if($formats = mysql_fetch_array($rsformats, MYSQL_ASSOC))
            		$dataok = true;
            	else
            		$errorm = 'Please select one of the answer types.';
            }
			else
				$errorm = 'Please select one of the answer types.';
        }
        else if($datacheck == 1)
        {
			if($_POST['questype'] != 'qmc')
			{
				$dataok = true;
				$skippage = true;
			}
            else if(ereg('^(list|select|radio)$', $_POST['quessubtype']))
            	$dataok = true;
            else
            	$errorm = 'Please select one of the display formats.';
        }
		else if($datacheck == 2)
		{
			if(strlen($_POST['prompt']))
			{
				if(is_id($_POST['order']))
					$dataok = true;
				else
					$errorm = 'Please select where the question should be inserted.';
			}
			else
				$errorm = 'Please enter a question prompt.';
		}
		else if($datacheck == 3)
		{
			if($_POST['questype'] != 'qmc')
			{
				$dataok = true;
				$skippage = true;
			}
            else
			{
				$items = explode("\n", $_POST['qchoices']);
				$numitems = 0;
				
				foreach($items as $value)
					if(strlen($value) > 0)
						$numitems++;
				
				if($numitems >= 2)
				{
					if($_POST['listsizedq'] == 'yes' || $_POST['quessubtype'] != 'list')
						$dataok = true;
					else
					{
						if(is_id($_POST['listsize']))
						{
							if($_POST['listsize'] >= 2)
								$dataok = true;
							else
								$errorm = 'The number of lines has to be at least two.';
						}
						else
							$errorm = 'Please specify how many lines to show in the list box.';
					}
				}
				else
					$errorm = 'Please enter at least two choices.';
			}
		}
		else if($datacheck == 4)
		{
			if(!($_POST['questype'] == 'qt' || $isformatted))
			{
				$dataok = true;
				$skippage = true;
			}
            else
			{
				if(is_id($_POST['textsize']))
					$dataok = true;
				else
					$errorm = 'Please enter a size for the text box.';
			}			
		}
		else if($datacheck == 5)
		{
			if($_POST['questype'] != 'qlt')
			{
				$dataok = true;
				$skippage = true;
			}
            else
			{
				if(is_id($_POST['longtextsize']))
					$dataok = true;
				else
					$errorm = 'Please enter a size for the text box.';
			}
		}
		else if($datacheck == 6)
		{
			if($qa['QA_ISGRADED'] == 0)
			{
				$dataok = true;
				$skippage = true;
			}
            else if(is_id($_POST['numpts']) || $_POST['numpts'] == 0)
			{
				$dataok = true;
			}
			else
			{
				$errorm = 'Please enter a point value for this question.';
			}
		}	
        else if($datacheck == 7)
        {
			$newheading = htmlentities(stripslashes($_POST['heading']),ENT_QUOTES);
			$newprompt = htmlentities(stripslashes($_POST['prompt']),ENT_QUOTES);
			$newdesc = htmlentities(stripslashes($_POST['desc']),ENT_QUOTES);
			
			$neworder = $_POST['order'];
			
			$newpage = $_GET['id'];
			$newformat = 'null';
			$newsize = 0;
			$newreqd = 0;
    
			if(is_id($neworder) && $neworder <= $maxquestion + 1)
			{		
				if($_POST['questype'] == 'qt' || $isformatted)
				{
					$newtype = 'Text';
					$newsize = $_POST['textsize'];
					$newreqd = ($_POST['required'] == 'yes') ? 1 : 0;				
				
					if($isformatted)
					{
						$newformat = $matches[1];
					}
				}
				else if($_POST['questype'] == 'qlt')
				{
					$newtype = 'Longtext';
					
					$newsize = $_POST['longtextsize'];
				}
				else if($_POST['questype'] == 'qcheck')
				{
					$newtype = 'Check';
				}
				else if($_POST['questype'] == 'qmc')
				{
					$newreqd = ($_POST['mcrequired'] == 'yes') ? 1 : 0;
					
					if($_POST['quessubtype'] == 'radio')
						$newtype = 'Radio';
					else
						$newtype = 'Select';
						
					if($_POST['quessubtype'] == 'list')
					{
						if($_POST['listsizedq'])
							$newsize = $numitems;
						else
							$newsize = $_POST['listsize'];
					}
					else if($_POST['quessubtype'] == 'select')
						$newsize = 1;
				}
				
				if($qa['QA_ISGRADED'])
					$newnumpts = $_POST['numpts'];
				else
					$newnumpts = 'null';
			
				for($i = $maxquestion; $i >= $neworder; $i--)
					mysql_query('UPDATE QAQUESTION_LIST SET QAQUESTION_ORDER=QAQUESTION_ORDER + 1 WHERE QAQUESTION_ID=' . $quesarr[$i]['QAQUESTION_ID']);
			
    			mysql_query("INSERT INTO QAQUESTION_LIST (QAQUESTION_PAGE, QAQUESTION_HEADING, QAQUESTION_PROMPT, QAQUESTION_DESC, QAQUESTION_PTS, QAQUESTION_TYPE, QAQUESTION_REQD, QAQUESTION_FORMAT, QAQUESTION_ORDER, QAQUESTION_SIZE) VALUES ('$newpage', '$newheading', '$newprompt', '$newdesc', '$newnumpts', '$newtype', '$newreqd', '$newformat', '$neworder', '$newsize')") or die("INSERT INTO QAQUESTION_LIST (QAQUESTION_PAGE, QAQUESTION_HEADING, QAQUESTION_PROMPT, QAQUESTION_DESC, QAQUESTION_PTS, QAQUESTION_TYPE, QAQUESTION_REQD, QAQUESTION_FORMAT, QAQUESTION_ORDER, QAQUESTION_SIZE) VALUES ('$newpage', '$newheading', '$newprompt', '$newdesc', '$newnumpts', '$newtype', '$newreqd', '$newformat', '$neworder', '$newsize')");

				if($_POST['questype'] == 'qmc')
				{
					$qid = mysql_insert_id();
					$curorder = 1;
					
 	   				foreach($items as $value)
					{
    						if(strlen($value) > 0)
						{
							$curtext = addslashes($value);

							mysql_query("INSERT INTO QAMC_LIST (QAMC_QUESTION, QAMC_ORDER, QAMC_TEXT) VALUES ($qid, $curorder, '$curtext')");			
    							$curorder++;
						}
					}
				}
    		
    			header('location: http://' . DNAME . '/qa/edit/qa.php?id=' . $page['QA_ID'] . '&mode=questions&page=' . $newpage);
			}
		}

		if($_POST['submit'] != "<< Previous")
		{
			$loopcond = ($datacheck <= $stepno || $skippage) && $dataok;
			
			if($loopcond)
				$datacheck++;
			else if($datacheck > $stepno)
			{
				$errorm = '';
				$firsttry = true;
			}
		}
		else
		{
			$loopcond = $skippage;
			
			if($loopcond)
				$datacheck--;
		}

	} while($loopcond);
	
	$stepno = $datacheck;
	
	if($firsttry)
	{
		if($stepno == 2 && $isformatted)
		{
			$_POST['prompt'] = $formats['QAFORMAT_DEFAULTPROMPT'];
		}
		else if($stepno == 3)
		{
			$_POST['listsize'] = 4;
		}
		else if($stepno == 4)
		{
			if($isformatted)
				$_POST['textsize'] = $formats['QAFORMAT_DEFAULTSIZE'];
			else
				$_POST['textsize'] = 12;
		}
		else if($stepno == 5)
		{
			$_POST['longtextsize'] = 6;
		}
		else if($stepno == 6)
		{
			$_POST['numpts'] = 10;
		}
	}
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
	<head>
		<title><? print htmlentities($page['QAPAGE_TITLE']); ?>: Add New Question</title>
		<link rel="stylesheet" type="text/css" href="../../shs.css">
		<link rel="stylesheet" type="text/css" href="../qa.css">
		<style type="text/css">
			a.lnkh { font-weight: bold }
		</style>
	</head>
	<body>
		<? include "inc-header.php"; ?>
		<? if($showitem) { ?>
		<h1 class="titlebar"><span style="float: right; font-size: medium; padding: 2px"><a href="../qa.php?id=<?= $page['QA_ID'] ?>">View</a> | <a href="qa.php?id=<?= $page['QA_ID'] ?>">Administer</a></span><span style="font-size: large"><a href="group.php?id=<?= $page['QAGROUP_ID'] ?>&amp;sortstr=<?= $_GET['sortstr'] ?>"><?= $page['QAGROUP_TITLE'] ?></a>:</span> <?= htmlentities($page['QA_TITLE']) ?></h1>
		<table width="100%" cellpadding="3" cellspacing="0"><tr>
		<td style="vertical-align: top; background-color: #dddddd; width: 225px; font-size: small">
		</td>
		<td style="font-size: medium; vertical-align: top">
		<? if($showitem) { ?>
		<h2 class="grayheading">Add New Question</h2>
		<form style="margin: 0" name="addqform" action="newquestion.php?id=<?= $_GET['id'] ?>" method="POST">
    	<? if(strlen($errorm) > 0) { print '<p style="margin: 0; padding: 5px; color: #900">' . $errorm . '</p>'; } ?>

		<?
		if($stepno == 0)
		{
		?>
			<table>
    		<tr><td style="vertical-align: top; font-size: medium; width: 10em">Answer Format</td><td>
				<table cellpadding="2" cellspacing="0" style="margin: 0; font-size: small">
				<tr><td></td><td style="font-weight: bold">General Formats</td><td></td></tr>
				<tr><td style="padding: 0"><input type="radio" name="questype" value="qcheck" id="qcheck"<?= (($_POST['questype'] == 'qcheck') ? ' checked ' : '') ?>></td><td><label for="qcheck">Checkbox</labe></td><td>(Yes/No or True/False)</td></tr>
				<tr><td style="padding: 0"><input type="radio" name="questype" value="qmc" id="qmc"<?= (($_POST['questype'] == 'qmc') ? ' checked ' : '') ?>></td><td><label for="qmc">Multiple Choice</label></td><td></td></tr>
				<tr><td style="padding: 0"><input type="radio" name="questype" value="qt" id="qt"<?= (($_POST['questype'] == 'qt') ? ' checked ' : '') ?>></td><td><label for="qt">Short Answer</label></td><td>(One line)</td></tr>
				<tr><td style="padding: 0"><input type="radio" name="questype" value="qlt" id="qlt"<?= (($_POST['questype'] == 'qlt') ? ' checked ' : '') ?>></td><td><label for="qlt">Long Answer</label></td><td>(Several lines or essay)</td></tr>
				<tr style="font-weight: bold"><td></td><td>Custom Formats</td><td style="color: #999">example</td></tr>
				<?
				
				$rsformats = mysql_query('SELECT QAFORMAT_ID, QAFORMAT_NAME, QAFORMAT_SAMPLE FROM QAFORMAT_LIST');
				
				while($format = mysql_fetch_array($rsformats, MYSQL_ASSOC))
				{
					print '<tr><td style="padding: 0"><input ' . (($_POST['questype'] == 'format' . $format['QAFORMAT_ID']) ? 'checked ' : '') . 'type="radio" name="questype" value="format' . $format['QAFORMAT_ID'] . '" id="qf' . $format['QAFORMAT_ID'] . '"></td><td><label for="qf' . $format['QAFORMAT_ID'] . '">' . $format['QAFORMAT_NAME'] . '</label></td><td style="color: #666">' . $format['QAFORMAT_SAMPLE'] . '</td></tr>';				
				}
				
				?>
				</table>
			</td></tr>
    		</table>
		<?
		}
		else if($stepno > 0)
		{
			print '<input type="hidden" name="questype" value="' . $_POST['questype'] . '">';
		}
		
		if($stepno == 1)
		{
		?>
<table>
    		<tr><td style="vertical-align: top; font-size: medium; width: 10em">Multiple Choice Subtype</td><td>
				<table cellpadding="2" cellspacing="0" style="margin: 0; font-size: small">
				<tr><td style="padding: 0"><input type="radio" name="quessubtype" value="select" id="select"<?= ($_POST['quessubtype'] == 'select') ? ' checked' : '' ?>></td><td><label for="select">Drop-down Box</labe></td><td>&nbsp;</td></tr>
				<tr><td style="padding: 0"><input type="radio" name="quessubtype" value="list" id="list"<?= ($_POST['quessubtype'] == 'list') ? ' checked' : '' ?>></td><td><label for="list">List Box</label></td><td>&nbsp;</td></tr>
				<tr><td style="padding: 0"><input type="radio" name="quessubtype" value="radio" id="radio"<?= ($_POST['quessubtype'] == 'radio') ? ' checked' : '' ?>></td><td><label for="radio">Radio Buttons</label></td><td>&nbsp;</td></tr>
				</table>
			</td></tr>
    		</table>
		<?
		}
		else if($stepno > 1)
		{
			print '<input type="hidden" name="quessubtype" value="' . $_POST['quessubtype'] . '">';
		}
		
		if($stepno == 2)
		{
		?>
    		<table style="font-size: medium">
			<tr><td style="vertical-align: top; font-size: medium; width: 10em"><p style="margin: 0">Heading</p><p style="margin: 0; color: #888; font-size: small">The heading (optional) appears in large type above the question. Use headings to separate major sections on the page; you only need to type a heading before the first question in each section.</p></td><td style="vertical-align: top"><input type="text" size="50" name="heading" value="<?= htmlentities(stripslashes($_POST['heading'])) ?>"></td></tr>
    		<tr><td style="font-size: medium; width: 10em; font-weight: bold">Question Prompt</td><td><input type="text" size="50" name="prompt" value="<?= htmlentities(stripslashes($_POST['prompt'])) ?>"></td></tr>
    		<tr><td style="vertical-align: top"><p style="margin: 0">Description</p><p style="margin: 0; color: #888; font-size: small">The description (optional) appears in small type underneath the question. Use a description to clarify the question, provide additional information, or give hints.</p></td><td style="vertical-align: top"><textarea cols="35" rows="6" name="desc"><?= htmlentities(stripslashes($_POST['desc'])) ?></textarea></td></tr>
    		<tr><td>Insert at</td><td><select name="order"><?
    		
    		if($maxquestion == 0)
    			print '<option value="1">Question 1 (Insert as first question)</option>';
    		else
    		{
    			print '<option ' . (($neworder == 1) ? 'selected ' : '') . 'value="1">Question 1 (Insert before Question 1, "' . htmlentities($quesarr[1]['QAQUESTION_PROMPT']) . '")</option>';
    
    			for($i = 1; $i <= $maxquestion; $i++)
    				print '<option ' . (($_POST['order'] == $i + 1 || ($i == $maxquestion && $_POST['order'] == '')) ? 'selected ' : '') . 'value="' . ($i + 1) . '">Question ' . ($i + 1) . ' (Insert after Question ' . $i . ', "' . htmlentities($quesarr[$i]['QAQUESTION_PROMPT']) . '")</option>';
    		}
    		
    		?></select></td></tr>
    		</table>
		<?
		}
		else if($stepno > 2)
		{
			print '<input type="hidden" name="heading" value="' . htmlentities(stripslashes($_POST['heading']),ENT_QUOTES) . '">
			<input type="hidden" name="prompt" value="' . htmlentities(stripslashes($_POST['prompt']),ENT_QUOTES) . '">
			<input type="hidden" name="desc" value="' . htmlentities(stripslashes($_POST['desc']),ENT_QUOTES) . '">
			<input type="hidden" name="order" value="' . $_POST['order'] . '">';
		}

		// MC
		if($stepno == 3)
		{
			?>
			<table style="font-size: medium">
			<tr><td style="vertical-align: top; font-size: medium; width: 10em"><p style="margin: 0">Answer Choices</p><p style="margin: 0; color: #888; font-size: small">Type all the answer choices you want for your multiple-choice question; separate them by RETURNs.</p></td><td style="vertical-align: top"><textarea cols="50" rows="7" name="qchoices"><?= htmlentities(stripslashes($_POST['qchoices'])) ?></textarea></td></tr>
			<?
			if($_POST['quessubtype'] == 'list')
			{
			?>
			<tr><td style="vertical-align: top; font-size: medium; width: 10em"><p style="margin: 0">List Box Size</p><p style="margin: 0; color: #888; font-size: small">A list box can only show a certain number of items at a time. (The user can scroll up and down to see all of them.) How many items should we show? </p></td><td style="vertical-align: top"><p style="margin: 0"><input type="radio" name="listsizedq" value="yes" id="listsizedqyes"<?= ($_POST['listsizedq'] == 'yes') ? ' checked' : '' ?>> <label for="listsizedqyes">Show all of the items.</label></p><p style="margin: 0"><input type="radio" name="listsizedq" value="no" id="listsizedqno"<?= ($_POST['listsizedq'] == 'no') ? ' checked' : '' ?>> <label for="listsizedqno">Show only</label> <input type="text" name="listsize" size="4" value="<?= htmlentities(stripslashes($_POST['listsize'])) ?>"> items.</p></td></tr>
			<tr><td style="vertical-align: top; font-size: medium; width: 10em"><p style="margin: 0">Required?</p><p style="margin: 0; color: #888; font-size: small">If you make this question required, the user isn't allowed to leave it blank.</p></td><td style="vertical-align: top"><input type="checkbox" name="mcrequired" value="yes"<?= ($_POST['mcrequired'] == 'yes') ? ' checked' : '' ?>></td></tr>
			<?
			}
			?>
			</table>
			<?
		}
		else if($stepno > 3)
		{
			print '<input type="hidden" name="qchoices" value="' . $_POST['qchoices'] . '">
			<input type="hidden" name="listsizedq" value="' . $_POST['listsizedq'] . '">
			<input type="hidden" name="listsize" value="' . $_POST['listsize'] . '">
			<input type="hidden" name="mcrequired" value="' . $_POST['mcrequired'] . '">';
		}
		
		// Texts
		if($stepno == 4)
		{
			?>
			<table style="font-size: medium">
			<tr><td style="vertical-align: top; font-size: medium; width: 10em"><p style="margin: 0">Text Box Size</p><p style="margin: 0; color: #888; font-size: small">A text box can only show a certain number of characters at a time. (The user can type as much as he/she wants, however, and the text scrolls to the right.) How big should we make the text box?</p></td><td style="vertical-align: top"><p style="margin: 0"><input type="text" maxlength="2" onkeyup="document.getElementById('samplewidth').style.width = this.value + 'em';" name="textsize" style="width: 4em" value="<?= htmlentities(stripslashes($_POST['textsize'])) ?>"></p><div style="width: 12em; border: 1px inset #888; padding: 1px; background-color: #ddd; margin-top: 1ex; font-size: small" id="samplewidth">Sample</div></td></tr>
			<tr><td style="vertical-align: top; font-size: medium; width: 10em"><p style="margin: 0">Required?</p><p style="margin: 0; color: #888; font-size: small">If you make this question required, the user isn't allowed to leave it blank.</p></td><td style="vertical-align: top"><input type="checkbox" name="required" value="yes"<?= ($_POST['required'] == 'yes') ? ' checked' : '' ?>></td></tr>
    		</table>
			<?
		}
		else if($stepno > 4)
		{
			print '<input type="hidden" name="textsize" value="' . $_POST['textsize'] . '">
			<input type="hidden" name="required" value="' . $_POST['required'] . '">';
		}
		
		// Longtexts
		if($stepno == 5)
		{
			?>
			<table style="font-size: medium">
			<tr><td style="vertical-align: top; font-size: medium; width: 10em"><p style="margin: 0">Text Box Size</p><p style="margin: 0; color: #888; font-size: small">A long-answer text box can only show a certain number of lines at a time. (The user can type as much as he/she wants, however, and the text scrolls down.) How many lines should the text box hold?</p></td><td style="vertical-align: top"><input type="text" name="longtextsize" style="width: 4em" value="<?= htmlentities(stripslashes($_POST['longtextsize'])) ?>"></td></tr>
    		</table>
			<?
		}
		else if($stepno > 5)
		{
			print '<input type="hidden" name="longtextsize" value="' . $_POST['longtextsize'] . '">';
		}
		
		// Isgraded
		if($stepno == 6)
		{
			?>
			<table style="font-size: medium">
			<tr><td style="vertical-align: top; font-size: medium; width: 10em"><p style="margin: 0">Point Value</p></td><td style="vertical-align: top"><input type="text" name="numpts" style="width: 4em" value="4"></td></tr>
    		</table>
			<?
		}
		else if($stepno > 6)
		{
			print '<input type="hidden" name="numpts" value="' . $_POST['numpts'] . '">';
		}

		?>
    	<p style="margin: 0"><input type="hidden" name="formsubmit" value="yes"><input type="hidden" name="mode" value="<?= $stepno ?>"><? if($stepno >= 1) { ?><input type="submit" name="submit" value="&lt;&lt; Previous"> <? } ?><input type="submit" name="submit" value="Next &gt;&gt;"></p>
		</form>
		<? } ?>
		</td></tr></table>
		<? include '../../inc-footer.php'; ?>
		<? } ?>
	</body>
</html>