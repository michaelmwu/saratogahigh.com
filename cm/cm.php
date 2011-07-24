<?

$canedit = false;
$filterclass = false;
$filterteacher = false;

if($loggedin)
{
	if(is_numeric($_GET['class']))
	{
		$rsclass = mysql_query('SELECT * FROM CLASS_LIST WHERE CLASS_ID=' . $_GET['class']);
		if($c = mysql_fetch_array($rsclass, MYSQL_ASSOC))
		{
			$filterclass = true;
			$classid = $_GET['class'];
		}
	}

	if($filterclass)
	{
		if(is_numeric($_GET['teacher']))
		{
			$rsteacher = mysql_query('SELECT USER_ID, TEACHER_LIST.* FROM TEACHER_LIST INNER JOIN VALIDCLASS_LIST ON VALIDCLASS_TEACHER=TEACHER_ID LEFT JOIN USER_LIST ON TEACHER_ID=USER_TEACHERTAG WHERE VALIDCLASS_COURSE=' . $_GET['class'] . ' AND TEACHER_ID=' . $_GET['teacher']);
			if($t = mysql_fetch_array($rsteacher, MYSQL_ASSOC))
			{
				$filterteacher = true;
				$teacherid = $_GET['teacher'];
				
				$canedit = $isadmin || $teacherid == $userR['USER_TEACHERTAG'];
			}
		}
	}
}
else
	forceLogin();

?>