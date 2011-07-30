<?
// Michael Wu | saratogahigh.com
// shcp/email.php: composes messages by email in response to comments

include "../inc/config.php";

$errorm = "";
$done = 0;

function supertrim($mystr)
{
	return trim(ereg_replace(' +',' ',$mystr));
}

function namedigest($mystr)
{
	return md5(strtolower($mystr));
}

if($loggedin && $isvalidated)
{
	$xattach = 0;
	$appendsubj = false;
	
	if(!is_null($userR['USER_MAILCAP']))
	{
		$rsmailssent = mysql_query('SELECT COUNT(*) AS C
			FROM MAIL_LIST
				INNER JOIN MAILREC_LIST ON MAILREC_MSG = MAIL_ID AND MAILREC_SENDTYPE != "from"
			WHERE MAIL_TS > SUBDATE("' . date(TIME_FORMAT_SQL, CURRENT_TIME) . '", INTERVAL 7 DAY) AND MAIL_SENDER = ' . $userid);
		$rmailssent = mysql_fetch_array($rsmailssent, MYSQL_ASSOC);
		$mailssent = $rmailssent['C'];
		
		if($mailssent >= $userR['USER_MAILCAP'])
			header('location: http://' . DNAME . '/mail/cap-error.php');
	}

	if($_POST['go'] == 'Send')
	{
		$numrecs = 0;
		
		$xto = stripslashes($_POST['xto']);
		$xcc = stripslashes($_POST['xcc']);
		$xbcc = stripslashes($_POST['xbcc']);
		$xsubj = stripslashes($_POST['xsubj']);
		$xmsgtxt = stripslashes($_POST['xmsgtxt']);
		$xattach = stripslashes($_POST['xattach']);
		$xthread = null;
		
		if(is_id($xattach))
		{
			if(permissionview($xattach, $userid) == '')
				$xattach = 'null';
			else
			{
				$rsattached = mysql_query('SELECT MAIL_THREAD FROM MAIL_LIST WHERE MAIL_ID=' . $xattach);
				$attachedmail = mysql_fetch_array($rsattached, MYSQL_ASSOC);
				$xthread = $attachedmail['MAIL_THREAD'];
			}
		}
		else
			$xattach = 'null';
		
		mysql_query('INSERT INTO MAIL_LIST (MAIL_ID, MAIL_ATTACH, MAIL_SENDER, MAIL_SUBJ, MAIL_TS, MAIL_MESSAGE) VALUES ("", ' . addslashes($xattach) . ', ' . $userid . ', "' . addslashes($xsubj) . '", "' . date(TIME_FORMAT_SQL, CURRENT_TIME) . '", "' . addslashes($xmsgtxt) . '")') or die('Message insert failed.');
		$lastid = mysql_insert_id();	
		if(is_null($xthread))
			$xthread = $lastid;
		mysql_query('UPDATE MAIL_LIST SET MAIL_THREAD=' . $xthread . ' WHERE MAIL_ID=' . $lastid);

		mysql_query('INSERT INTO MAILREC_LIST (MAILREC_THREAD, MAILREC_MSG, MAILREC_SENDTYPE, MAILREC_RECIPIENT, MAILREC_FOLDER) VALUES (' . $xthread . ', ' . $lastid . ', "from", ' . $userid . ', null)') or die('Message insert failed.');

		$numrecs += parsereclist($xto, $lastid, 'to', $xthread);
		$numrecs += parsereclist($xcc, $lastid, 'cc', $xthread);
		$numrecs += parsereclist($xbcc, $lastid, 'bcc', $xthread);
		
		if($errorm == '' && $numrecs == 0)
			$errorm = "No valid recipients were found.";

		if($errorm == '' && !is_null($userR['USER_MAILCAP']) && ($numrecs + $mailssent > $userR['USER_MAILCAP']))
			$errorm = "This message exceeds your mail cap. It contains $numrecs recipients but your quota only permits " . ($userR['USER_MAILCAP'] - $mailssent) . " more right now.";
		
		if($errorm != '')
		{
			mysql_query('DELETE FROM MAILREC_LIST WHERE MAILREC_MSG=' . $lastid) or die('Message insert failed.');
			mysql_query('DELETE FROM MAIL_LIST WHERE MAIL_ID=' . $lastid) or die('Message insert failed.');
		}
		else
		{
			header("Location: view.php?confirm=true&id=" . $lastid);
			$done = 1;
		}
	}
	else
	{
		$xmsgtxt = "";
		if(is_id($_GET['fwnote']))
		{
			$cn = mysql_query('SELECT NOTEPAGE_LIST.*, USER_FULLNAME FROM NOTEPAGE_LIST INNER JOIN USER_LIST ON NOTEPAGE_OWNER=USER_ID WHERE NOTEPAGE_OWNER=' . $userid . ' AND NOTEPAGE_ID=' . $_GET['fwnote']);
			
			if($n = mysql_fetch_array($cn))
			{
				$xmsgtxt = '



Note by: ' . $n['USER_FULLNAME'] . '
Modified: ' . date(TIME_FORMAT, strtotime($n['NOTEPAGE_MODIFIED'])) . '
Original note follows ---------------
' . $n['NOTEPAGE_VALUE'];
			}
		}
		else if(is_id($_GET['recomment']) && $isadmin)
		{
			$cc = mysql_query('SELECT * FROM COMMENT_LIST WHERE COMMENT_ID=' . $_GET['recomment']);
			
			if($n = mysql_fetch_array($cc))
			{
				$xmsgtxt = '


------ On ' . date(TIME_FORMAT, strtotime($n['COMMENT_TS'])) . ' you wrote:
' . $n['COMMENT_TEXT'];

				$rsusers = mysql_query('SELECT USER_FULLNAME, USER_GR, USER_DUPNAME FROM USER_LIST WHERE USER_ID=' . $n['COMMENT_USER']);
				if($user = mysql_fetch_array($rsusers, MYSQL_ASSOC))
					$xto = longname($user);
			}
		}
			
		$xattach = $_GET['attach'];
		
		if(is_numeric($xattach))
		{
			if(permissionview($xattach, $userid) != '')
			{
				$appendsubj = true;
			
				if($_GET['action'] == 'r')
				{
					$recipients = mysql_query('SELECT USER_FULLNAME, USER_DUPNAME, USER_GR FROM MAIL_LIST INNER JOIN MAILREC_LIST ON MAIL_ID=MAILREC_MSG INNER JOIN USER_LIST ON MAILREC_RECIPIENT=USER_ID WHERE USER_ID!=' . $userid . ' AND MAILREC_SENDTYPE="from" AND MAIL_ID=' . $xattach . ' ORDER BY USER_LN, USER_FN');
					$firstrec = true;
					while($currec = mysql_fetch_array($recipients, MYSQL_ASSOC))
					{
						if(!$firstrec)
							$xto .= ', ';
						$xto .= longname($currec);
						$firstrec = false;
					}
				}
				else if($_GET['action'] == 'rta')
				{
					$recipients = mysql_query('SELECT USER_FULLNAME, USER_DUPNAME, USER_GR FROM MAIL_LIST INNER JOIN MAILREC_LIST ON MAIL_ID=MAILREC_MSG INNER JOIN USER_LIST ON MAILREC_RECIPIENT=USER_ID WHERE USER_ID!=' . $userid . ' AND (MAILREC_SENDTYPE="from" OR MAILREC_SENDTYPE="to") AND MAIL_ID=' . $xattach . ' ORDER BY USER_LN, USER_FN');
					$firstrec = true;
					while($currec = mysql_fetch_array($recipients, MYSQL_ASSOC))
					{
						if(!$firstrec)
							$xto .= ', ';
						$xto .= longname($currec);
						$firstrec = false;
					}
					
					$recipients = mysql_query('SELECT USER_FULLNAME, USER_DUPNAME, USER_GR FROM MAIL_LIST INNER JOIN MAILREC_LIST ON MAIL_ID=MAILREC_MSG INNER JOIN USER_LIST ON MAILREC_RECIPIENT=USER_ID WHERE USER_ID!=' . $userid . ' AND MAILREC_SENDTYPE="cc" AND MAIL_ID=' . $xattach . ' ORDER BY USER_LN, USER_FN');
					$firstrec = true;
					while($currec = mysql_fetch_array($recipients, MYSQL_ASSOC))
					{
						if(!$firstrec)
							$xcc .= ', ';
						$xcc .= longname($currec);
						$firstrec = false;
					}
				}
				else if($_GET['action'] == 'f')
				{
				
				}
			}
			else
				$xattach = 0;
		}
		else
			$xattach = 0;
			
		if(is_numeric($_GET['sendto']))
		{
			$rsusers = mysql_query('SELECT USER_FULLNAME, USER_GR, USER_DUPNAME FROM USER_LIST WHERE USER_ID=' . $_GET['sendto']);
			
			if($user = mysql_fetch_array($rsusers, MYSQL_ASSOC))
				$xto = longname($user);
		}
	}
	
	if($xattach > 0)
	{
		$attachmsg = mysql_query('SELECT MAIL_SUBJ, USER_FN, USER_LN FROM MAIL_LIST INNER JOIN USER_LIST ON MAIL_SENDER=USER_ID WHERE MAIL_ID=' . $xattach);
		$l = mysql_fetch_array($attachmsg, MYSQL_ASSOC);
		$xattachstr = '<a href="view.php?id=' . $xattach . '">&quot;' . htmlentities($l['MAIL_SUBJ']) . '&quot; by ' . htmlentities($l['USER_FN']) . ' ' . htmlentities($l['USER_LN']) . '</a>';
		
		if($appendsubj)
		{
			$xsubj = htmlentities($l['MAIL_SUBJ']);
		}
	}
}

if($done != 1)
{

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
	<head>
		<title>Compose New Message</title>
		<link rel="stylesheet" type="text/css" href="../shs.css">
		<meta name="vs_targetSchema" content="http://schemas.microsoft.com/intellisense/ie5">
		<style type="text/css"><!--
			a.lnkc { font-weight: bold }
		--></style>
		<script type="text/javascript"><!--
		
		var selbox;
		
		function addname(selbox, namestr)
		{
			mytext = selbox.value;
			
			if(mytext == '')
				selbox.value = namestr;
			else
				selbox.value = selbox.value + ', ' + namestr;
		}
		
		
		// --></script>
	</head>
	<body onload="selbox = document.mf.xto">
<? include "inc-header.php" ?>

		<? if($loggedin) { ?>
			<? if($isvalidated) { ?>

<div id="rr" style="margin: 2px; width: 190px; border: 1px solid #cccccc; left: 590px; padding: 2px; float: right; position: absolute">
<ul class="flat" style="margin: 0px"><li>Click on a person's name to add him/her to the recipient list.<? if($isteacher) {
	print ' Or, click on a class name or period number to add the entire class.';
} ?></li>
<li><img class="imgicon" src="/imgs/people.gif">Recent Recipients</li><ul class="flat">
<?
$recentnames = mysql_query('SELECT DISTINCTROW USER_DUPNAME, USER_GR, USER_ID, USER_FULLNAME, MAX(MAIL_TS) AS LAST_SENT FROM MAILREC_LIST INNER  JOIN MAIL_LIST ON MAILREC_MSG = MAIL_ID AND MAILREC_SENDTYPE !=  "from" INNER  JOIN USER_LIST ON MAILREC_RECIPIENT = USER_ID WHERE MAIL_SENDER = ' . $userid . ' GROUP  BY USER_FULLNAME ORDER BY LAST_SENT DESC LIMIT 25');
if(mysql_num_rows($recentnames) > 0)
{
	while($l = mysql_fetch_array($recentnames, MYSQL_ASSOC))
		print '<li><img class="imgicon" src="/imgs/person.gif"><a href="javascript:addname(selbox, \'' . longname($l) . '\');">' . htmlentities($l['USER_FULLNAME']) . '</a></li>';
}
else
{
	print '<li><img class="imgicon" src="/imgs/person.gif">(None)</li>';
}

?>
</ul>
<? if($isteacher) { ?>
<?
$myclasses = mysql_query('SELECT DISTINCT SCHED_TEACHER, CLASS_NAME, CLASS_ID
	FROM CLASS_LIST
		INNER JOIN SCHED_LIST ON CLASS_ID=SCHED_CLASS
	WHERE SCHED_YEAR=' . C_SCHOOLYEAR . ' AND SCHED_TEACHER=' . $usertag . ' ORDER BY CLASS_NAME');

while($l = mysql_fetch_array($myclasses, MYSQL_ASSOC))
{
	$pernames = array();

	for($i = 1; $i <= 7; $i++)
		$firstname[$i] = true;

	$mystudents = mysql_query('SELECT USER_GR, USER_DUPNAME, USER_FULLNAME, SCHED_PER
		FROM SCHED_LIST
			INNER JOIN USER_LIST ON SCHED_USER=USER_ID
		WHERE SCHED_YEAR=' . C_SCHOOLYEAR . ' AND (SCHED_TERM="YEAR" OR SCHED_TERM="' . C_SEMESTER . '") AND SCHED_TEACHER=' . $l['SCHED_TEACHER'] . ' AND SCHED_CLASS=' . $l['CLASS_ID'] . ' AND USER_VALIDATED=1
		ORDER BY SCHED_PER, USER_LN, USER_FN');
		
	while($ll = mysql_fetch_array($mystudents, MYSQL_ASSOC))
	{
		if(!$firstname[$ll['SCHED_PER']])
			$pernames[$ll['SCHED_PER']] .= ', ';
		$pernames[$ll['SCHED_PER']] .= addslashes(longname($ll));
		$firstname[$ll['SCHED_PER']] = false;
	}
	
	$allnames = join(", ", $pernames);

	print '<li><img class="imgicon" src="/imgs/person.gif"><a href="javascript:addname(selbox, \'' . $allnames . '\')" style="font-weight: bold">' . $l['CLASS_NAME'] . '</a></li>';
	print '<li><ul class="flat">';

	$rsperiods = mysql_query('SELECT DISTINCT SCHED_PER
		FROM SCHED_LIST
			INNER JOIN USER_LIST ON SCHED_USER=USER_ID
		WHERE SCHED_YEAR=' . C_SCHOOLYEAR . ' AND (SCHED_TERM="YEAR" OR SCHED_TERM="' . C_SEMESTER . '") AND SCHED_TEACHER=' . $l['SCHED_TEACHER'] . ' AND SCHED_CLASS=' . $l['CLASS_ID'] . ' AND USER_VALIDATED=1
		ORDER BY SCHED_PER');
		
	while($pp = mysql_fetch_array($rsperiods, MYSQL_ASSOC))
	{
		if(mysql_num_rows($rsperiods) > 1)
		{
			print '<li><img class="imgicon" src="/imgs/person.gif"><a href="javascript:addname(selbox, \'' . $pernames[$pp['SCHED_PER']] . '\')" style="font-weight: bold">Per ' . $pp['SCHED_PER'] . '</a></li>';
			
			print '<li><ul class="flat">';
		}
		
		$mystudents = mysql_query('SELECT DISTINCT USER_FULLNAME
			FROM SCHED_LIST
				INNER JOIN USER_LIST ON SCHED_USER=USER_ID
			WHERE SCHED_YEAR=' . C_SCHOOLYEAR . ' AND (SCHED_TERM="' . C_SEMESTER . '" OR SCHED_TERM="YEAR") AND SCHED_TEACHER=' . $l['SCHED_TEACHER'] . ' AND SCHED_CLASS=' . $l['CLASS_ID'] . ' AND USER_VALIDATED=1 AND SCHED_PER=' . $pp['SCHED_PER'] . '
			ORDER BY USER_LN, USER_FN');
			
		while($ll = mysql_fetch_array($mystudents, MYSQL_ASSOC))
		{
			print '<li><img class="imgicon" src="/imgs/person.gif"><a href="javascript:addname(selbox, \'' . addslashes(longname($ll)) . '\');">' . htmlentities($ll['USER_FULLNAME']) . '</a></li>';
		}
		
		if(mysql_num_rows($rsperiods) > 1)
			print '</ul></li>';
	}
	print '</ul></li>';
}
?>
<? } ?>
</ul>
</div>
<div style="width: 570px; padding: 0px; margin: 0px; position: absolute">
<div style="background-color: #dddddd">
<h1 style="margin: 0px; padding: 2px; letter-spacing: 1pt; font-size: large">Compose New Message</h1>
</div>
<form action="compose.php" name="mf" method="POST" style="margin: 0px">
<?
if(strlen($errorm) > 0)
	print '<p style="font-size: medium; margin: 0px; color: #800; padding: 2px; border: 2px solid #c33">' . $errorm . '</p>';
if(strlen($duplist) > 0)
	print '<table style="font-size: medium">' . $duplist . '</table>';

?>
<div style="background-color: #eeeeee">
<p style="margin: 0px; padding: 2px; font-size: medium">Type the full names of your recipients, separated by commas.</p>
<? if(!is_null($userR['USER_MAILCAP'])) { ?>
<p style="margin: 0px; padding: 2px; font-size: small">Your mail quota: <?= $mailssent ?> recipients in last 7 days (<?= $userR['USER_MAILCAP'] ?> allowed)</p>
<? } ?>
</div>
	<table>
	<tr><td align="right">to</td><td><input id="txtto" onfocus="selbox = document.mf.xto;" type="text" name="xto" value="<?= htmlentities($xto) ?>" style="width: 430px" size="55"></td></tr>
	<tr><td align="right">cc</td><td><input id="txtcc" onfocus="selbox = document.mf.xcc;" type="text" name="xcc" value="<?= htmlentities($xcc) ?>" style="width: 430px" size="55"></td></tr>
	<tr><td align="right">bcc</td><td><input id="txtbcc" onfocus="selbox = document.mf.xbcc;" type="text" name="xbcc" value="<?= htmlentities($xbcc) ?>" style="width: 430px" size="55"></td></tr>
	<tr><td align="right" style="font-weight: bold">subject</td><td><input type="text" name="xsubj" value="<?= htmlentities($xsubj) ?>" style="width: 400px" size="40"></td></tr>
	<? if($xattach > 0) { ?>
	<tr><td align="right" style="font-weight: bold">attach</td><td><?= $xattachstr ?></td></tr>
	<tr><td></td><td>The message above, including all its text and attachments, will be attached to the current message.</td></tr>
	<? } ?>
	</table>
	<input type="hidden" name="xattach" value="<?= $xattach ?>">
	<p><textarea name="xmsgtxt" rows="12" style="width: 555px" cols="55" wrap="virtual"><?= htmlentities($xmsgtxt) ?></textarea></p>
	<p><input type="submit" name="go" value="Send"></p>
	</form>
	<?
	} else { ?>
		<p>Sorry, only verified users can send and receive mail. <a href="/help/validation.php">Click here</a> to learn more about validation.</p>
	<? } ?>
<? } else { ?>
	<p>Please <a href="../login.php?next=/mail/compose.php">log in</a> to compose messages.</p>
<? } ?>

<? include '../inc-footer.php'; ?></body>
</html>
<?

}
?>