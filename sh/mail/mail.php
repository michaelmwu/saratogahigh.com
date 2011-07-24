<?

function permissionview($mailid, $userid)
{
	$messages = mysql_query('SELECT MAILREC_SENDTYPE FROM MAIL_LIST INNER JOIN MAILREC_LIST ON MAIL_ID=MAILREC_MSG WHERE MAIL_ID=' . $mailid . ' AND MAILREC_RECIPIENT=' . $userid) or die('Query failed.');
	if($l = mysql_fetch_array($messages, MYSQL_ASSOC))
		return $l['MAILREC_SENDTYPE'];
	else
		return '';
}

function printfoldericon($cview, $tag, $title)
{
	if($cview == $tag)
		print '<tr><td class="data" style="font-weight: bold">' . $title . '</td></tr>';
	else
		print '<tr><td class="data"><a href="./?cf=' . $tag . '">' . $title . '</a></td></tr>';
}

function movemessage($cmsg, $dest, $userid)
{
	mysql_query('UPDATE MAILREC_LIST SET MAILREC_FOLDER=' . $dest . ' WHERE MAILREC_MSG=' . $cmsg . ' AND MAILREC_RECIPIENT=' . $userid) or die('Deletion failed.');
}

function deletemessage($cmsg, $userid)
{
	mysql_query('UPDATE MAILREC_LIST SET MAILREC_DELETED=1 WHERE MAILREC_MSG=' . $cmsg . ' AND MAILREC_RECIPIENT=' . $userid) or die('Deletion failed.');
}


function longname($user)
{
	if($user['USER_DUPNAME'])
	{
		if($user['USER_GR'] == 0)
			return htmlentities($user['USER_FULLNAME']) . ' :F';
		else if($user['USER_GR'] == 1)
			return htmlentities($user['USER_FULLNAME']) . ' :P';
		else
			return htmlentities($user['USER_FULLNAME']) . ' :' . $user['USER_GR'];
	}
	else
		return htmlentities($user['USER_FULLNAME']);
}

?>