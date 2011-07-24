<?

include "db.php";

if($HTTP_POST_VARS['go'] == 'comment')
{
	if(eregi('(activation code)|(password)', $_POST['entrytext']))
		$newcat = 2;
	else
		$newcat = 1;

	if($loggedin)
	{
		mysql_query("INSERT INTO COMMENT_LIST (COMMENT_USER, COMMENT_TS, COMMENT_TEXT, COMMENT_PAGE, COMMENT_CAT) VALUES (" . $userid . ", '" . date(TIME_FORMAT_SQL, CURRENT_TIME) . "', '" . $_POST['entrytext'] . "', '" . $_POST['page'] . "', " . $newcat . ");") or die("Insert failed.");
		$r = mysql_query("SELECT USER_FULLNAME, USER_UNAME FROM USER_LIST WHERE USER_ID='" . $userid . "'") or die("Cannot select");
		$u = mysql_fetch_array($r, MYSQL_ASSOC);
	}
	else
		mysql_query("INSERT INTO COMMENT_LIST (COMMENT_USER, COMMENT_TS, COMMENT_TEXT, COMMENT_PAGE, COMMENT_CAT) VALUES (Null, '" . date(TIME_FORMAT_SQL, CURRENT_TIME) . "', '" . $_POST['entrytext'] . "', '" . $_POST['page'] . "', " . $newcat . ");") or die("Insert failed.");

	$ru = mysql_query("SELECT USER_EMAIL FROM USER_LIST WHERE USER_COMMENTEMAIL = 1");
	
	$receive = array();
	
	while($user = mysql_fetch_array($ru, MYSQL_ASSOC))
	{
		$receive[] = $user['USER_EMAIL'];
	}
	
	$receive = implode(', ',$receive);

	$config = array( 'from' => "staff",
					'to' => $receive,
					'subject' => "SaratogaHigh.com Comment - " . ($newcat == 1 ? 'General' : 'Password Request'),
					'message' => "Dear Admin,\n"
		. "\tA comment has been posted on SaratogaHigh.com. Please do respond in a timely matter.\n\n"
		. ($loggedin?$u['USER_FULLNAME'] . ' - ' . $u['USER_UNAME']:'Anonymous') . ' wrote at ' . date(TIME_FORMAT_SQL, CURRENT_TIME) . "\n"
		. $_POST['entrytext']);

	email($config);
}

?>
<? if(!isset($_POST['XMLRequest'])) { ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
	<head>
		<title>Thanks!</title>
		<link rel="stylesheet" type="text/css" href="../shs.css">
		<meta name="vs_targetSchema" content="http://schemas.microsoft.com/intellisense/ie5">
		<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
	</head>
	<body>
<? include("inc-header.php"); ?>
<? } ?>
		<p style="font-size: small">Thank you for your question or comment! We will try and get back to you as soon as possible. If you had a question, check the <a href="/help/validation.php">FAQ</a> to see if your question has already been addressed.</p>
<? if(!isset($_POST['XMLRequest'])) { ?>
		<p style="font-size: large"><a href="http://<?= DNAME ?><?= $_POST['page'] ?>">Back to previous page</a></p>
	</body>
</html>
<? } ?>
