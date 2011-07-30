<?

$canedit = false;
$filterclass = false;
$filterteacher = false;
$filterclasscat = false;

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
		else
		{
			$rsteachers = mysql_query('SELECT USER_ID, TEACHER_LIST.* FROM TEACHER_LIST INNER JOIN VALIDCLASS_LIST ON VALIDCLASS_TEACHER=TEACHER_ID LEFT JOIN USER_LIST ON TEACHER_ID=USER_TEACHERTAG WHERE VALIDCLASS_COURSE="' . $_GET['class'] . '"');
			while($t = mysql_fetch_array($rsteachers, MYSQL_ASSOC))
			{
				$canedit = $isadmin || $t['TEACHER_ID'] == $userR['USER_TEACHERTAG'] || $canedit;
			}
		}
	}
	else if(is_numeric($_GET['classcat']))
	{
		$rsclasscat = mysql_query('SELECT * FROM CLASSCAT_LIST WHERE CLASSCAT_ID=' . $_GET['classcat']);
		if($cc = mysql_fetch_array($rsclasscat, MYSQL_ASSOC))
		{
			$classcatid = $_GET['classcat'];
			
			$rsteachers = mysql_query('SELECT DISTINCT USER_ID, TEACHER_LIST.* FROM CLASSCAT_LIST
									INNER JOIN CLASS_LIST ON CLASS_CATEGORY=CLASSCAT_ID
									INNER JOIN VALIDCLASS_LIST ON CLASS_ID=VALIDCLASS_COURSE
									INNER JOIN TEACHER_LIST ON VALIDCLASS_TEACHER=TEACHER_ID
									LEFT JOIN USER_LIST ON TEACHER_ID=USER_TEACHERTAG
									WHERE CLASSCAT_ID="' . $_GET['classcat'] . '"');
									
			while($t = mysql_fetch_array($rsteachers, MYSQL_ASSOC))
			{
				$canedit = $isadmin || $t['TEACHER_ID'] == $userR['USER_TEACHERTAG'] || $canedit;
			}
			
			$filterclasscat = true;
		}
	}

        $where = array();
        
        if(is_numeric($classid))
        {
                $where[] = "CMFOLDER_COURSE='$classid'";
                if(is_numeric($teacherid))
                        $where[] = "CMFOLDER_TEACHER='$teacherid'";
                else
                        $where[] = "CMFOLDER_TEACHER IS NULL";
        }
        else if(is_numeric($classcatid))
                $where[] = "CMFOLDER_CLASSCAT='$classcatid'";

        $wherestring = implode(" AND ", $where);
        
        $get = array();
         
        if(is_numeric($classid))
        {
                $get[] = "class=$classid";
                if(is_numeric($teacherid))
                        $get[] = "teacher=$teacherid";
        }
        else if(is_numeric($classcatid))
                $get[] = "classcat=$classcatid";
                
        $getstring = implode(" & ", $get);
}
else
	forceLogin();

?>
