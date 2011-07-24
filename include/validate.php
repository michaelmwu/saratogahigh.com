<? // validate.php | Michael Wu

	if(!$login->loggedin)
		$login->forceLogin();

	if($_SERVER['HTTP_HOST'] != 'csf.shsclubs.org')
		$aq = mysql_query('SELECT * FROM ADMINPAGE_LIST INNER JOIN PHPBB_users ON user_id=' . $login->userR['user_id'] . ' 
LEFT JOIN PRIVILEGE_LIST ON PRIVILEGE_USER=user_id AND PRIVILEGE_SITE=1 WHERE ADMINPAGE_PATH="' . $_SERVER['PHP_SELF'] 
. '" AND ADMINPAGE_SITE=' . SITE . ' AND (PRIVILEGE_LEVEL >= ADMINPAGE_PERMISSION OR USER_ITCCLEVEL >= 
ADMINPAGE_PERMISSION)');
	else
		$aq = mysql_query('SELECT * FROM ADMINPAGE_LIST INNER JOIN PHPBB_users ON user_id=' . $login->userR['user_id'] . ' 
LEFT JOIN PRIVILEGE_LIST ON PRIVILEGE_USER=user_id AND PRIVILEGE_SITE=1 WHERE ADMINPAGE_PATH="' . $_SERVER['PHP_SELF'] 
. '" AND ADMINPAGE_SITE=' . SITE . ' AND (PRIVILEGE_LEVEL >= ADMINPAGE_PERMISSION OR USER_CSFLEVEL >= 
ADMINPAGE_PERMISSION)');

	if(!($ar = mysql_fetch_array($aq, MYSQL_ASSOC)))
		die('Sorry, you do not have permission to view this page.');
?>
