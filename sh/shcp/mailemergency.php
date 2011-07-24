<?
include('../db.php');

		mail("icy@digitalitcc.com", "SaratogaHigh.com", "Dear Sir,\n"
		. "You are a poo.\n"
		. "Sincerely,\n"
		. "The Staff of SaratogaHigh.com"
		, "From: billgates@microsoft.com\r\n"
		. "Reply-To: YOUR-MOM@microsoft.com\r\n"
		. "X-Mailer: PHP/" . phpversion() . "\r\n"
		. 'Content-type: multipart/mixed; boundary="' . 'frontier' . '"' . "\r\n"
		. "MIME-version: 1.0 \r\n"
		. "--frontier"
		. "Content-type: text/plain\r\n"
		. "Cc: goldconker@comcast.net\r\n"
		. "--frontier--") or die("Mail not sent!");

/* $query = mysql_query("SELECT * FROM USER_LIST WHERE USER_GR >= 2006 AND USER_GR <= 2009");
$first = true;
while($email = mysql_fetch_array($query, MYSQL_ASSOC))
{
	if(strlen($email['USER_EMAIL']) > 0)
	{
		if(!$first)
			print ', ';
		print $email['USER_EMAIL'];
		$first = false;
	}
} */

/*	$rsqa = mysql_query('SELECT * FROM QAPAGE_LIST INNER JOIN QA_LIST ON QAPAGE_QA=QA_ID INNER JOIN QAGROUP_LIST ON QA_GROUP=QAGROUP_ID WHERE QAPAGE_ID=67');
	
	if($qa = mysql_fetch_array($rsqa, MYSQL_ASSOC))
	{
		$rsauthor = mysql_query('SELECT * FROM QAAUTHOR_LIST WHERE QAAUTHOR_QAGROUP=' . $qa['QA_GROUP'] . ' AND QAAUTHOR_USER=' . $userid);
		if($author = mysql_fetch_array($rsauthor, MYSQL_ASSOC))
		{
			$isauthor = true;
		}
	}

	$rsquestions = mysql_query('SELECT * FROM QAQUESTION_LIST
		WHERE QAQUESTION_PAGE=67
		ORDER BY QAQUESTION_ORDER');

	$numquestions = mysql_num_rows($rsquestions);
	
		$rsanswers = mysql_query('SELECT * FROM QAFILLPAGE_LIST
		INNER JOIN QAQUESTION_LIST ON QAFILLPAGE_PAGE=QAQUESTION_PAGE
		INNER JOIN QAFILL_LIST ON QAFILLPAGE_FILL=QAFILL_ID
		INNER JOIN USER_LIST ON QAFILL_USER=USER_ID
		LEFT JOIN QARESP_LIST ON QARESP_FILLPAGE=QAFILLPAGE_ID AND QARESP_QUESTION=QAQUESTION_ID
		WHERE QAFILLPAGE_PAGE=67 AND QAFILLPAGE_SAVED=1
		GROUP BY USER_ID
		ORDER BY QAFILLPAGE_FINISH, QAQUESTION_ORDER, QARESP_ID');	

	// print all the data in succession
	$i = 0;
	while($ans = mysql_fetch_array($rsanswers, MYSQL_ASSOC))
	{
				print $ans['USER_FULLNAME'] . " " . $ans['USER_EMAIL'] . "\n";

		email("staff", $ans['USER_EMAIL'], "SaratogaHigh.com Emergency Cards",
		"Dear " . $ans['USER_FULLNAME'] . ",\n\n"
		. "According to our records, you have filled out emergency card on SaratogaHigh.com. These emergency cards are due TOMORROW. Please make sure to turn in a signature sheet to the main office tomorrow. Without a signature sheet, the emergency card is invalid. If not, students may be pulled out of classes, and any classses missed will be counted as unexcused. So make sure to get that signature sheet in."
		. "Sincerely,\n"
		. "The Staff of SaratogaHigh.com");

		$i++;
	}
*/
?>