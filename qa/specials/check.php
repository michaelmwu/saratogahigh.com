<?

include '../../db.php';
$print = isset($_GET['print']);

if(is_id($_GET['group']))
{
	$rscurgroup = mysql_query('SELECT * FROM QAGROUP_LIST WHERE QAGROUP_ID=' . $_GET['group']);

	if($curgroup = mysql_fetch_array($rscurgroup, MYSQL_ASSOC))
	{

	}
	else
		die();
}
else
	die();

if($_POST['type'] == 'saveform')
{
	if($_POST['nextpage'] == 'next')
	{
		header('location: http://' . DNAME . $_POST['path']);
	}
	else
		header('location: http://' . DNAME . '/qa/index.php?group=' . $_GET['group']);
}


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
	<head>
		<title>Donation Summary Page</title>
		<link rel="stylesheet" type="text/css" href="../../shs.css">
		<link rel="stylesheet" type="text/css" href="../qa.css">
		<style type="text/css">
			a.linkh { font-weight: bold }
			.cf tr td { vertical-align: top }
			.msg p { margin-top: 0.5ex; margin-bottom: 0; text-indent: 50px }
			.thank p {font-weight: bold;}
			.error p {font-weight: bold; color: #DD0000;}
		</style>
	</head>
	<body <? if($print) print 'onLoad="window.print();"'?>>
	<?
	if(!$print)
		include "inc-header.php";
	$group = $_GET['group'];
			?>
	<h1 class="titlebar"><span style="font-size: large"><a href="../?group=<?= $curgroup['QAGROUP_ID'] ?>"><?= $curgroup['QAGROUP_TITLE'] ?></a>:</span> Donation Summary</h1>
		<?
		if($loggedin)
		{ //load autofill data
			if(!$print)
				include "inc-nav.php";

		if($_GET['confirm'] == 1 && preg_match("#^https*://[\w]{1,10}.paypal.com/#",$_SERVER['HTTP_REFERER']))
		{
			print '<p class="thank">Thank you for your donation.</p>';
		}
		if($_GET['confirm'] == 0 && preg_match("#^https*://[\w]{1,10}.paypal.com/#",$_SERVER['HTTP_REFERER']))
		{
			print '<p class="error">Your donation was canceled.</p>';
		}
		?>
		<p style="font-size: medium">If you filled in a donation amount on any form, the amount is listed below for your reference. You have two options for payment this year:</p>
		<ol>
		<li>The PTSA is now accepting either secure online payment via PayPal or by check. </li>
		<li>For all other organizations (or for PTSA if you do not wish to use PayPal), please pay by check. On each check that you write, please write "Registered via SaratogaHigh.com" so its recipient knows to find your registration information online. You can place all the checks, along with your signature sheet, in one envelope and drop it in the "SaratogaHigh.com Forms Online" box in the office. Or you can mail it to the address below.</li>
		</ol>
		<p>Forms Online/SaratogaHigh.com<br>Saratoga High School<br>20300 Herriman Ave<br>Saratoga, CA 95070</p>
		<?
		function donationlines ($name, $to, $qno, $type, $sum)
		{
			global $userid;
			$qu = mysql_query('SELECT QARESP_LIST.* FROM QA_LIST
							INNER JOIN QAPAGE_LIST ON QAPAGE_QA=QA_ID
							INNER JOIN QAFILL_LIST ON QAFILL_QA=QA_ID
							INNER JOIN QAFILLPAGE_LIST ON QAFILLPAGE_FILL=QAFILL_ID AND QAFILLPAGE_PAGE=QAPAGE_ID
							INNER JOIN QAQUESTION_LIST ON QAQUESTION_PAGE=QAPAGE_ID
							INNER JOIN QARESP_LIST ON QARESP_QUESTION=QAQUESTION_ID AND QARESP_FILLPAGE=QAFILLPAGE_ID
							WHERE QAFILL_USER=' . $userid . ' AND QAQUESTION_ID=' . $qno . ' AND QA_GROUP=' . $_GET['group']);

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

			if($line = mysql_fetch_array($qu, MYSQL_ASSOC))
			{
				if($type=='text' && $line['QARESP_RESP_TEXT'] != '$0' && $line['QARESP_RESP_TEXT'] != '')
				{
					print '<tr><td>' . $name . '</td><td>' . $line['QARESP_RESP_TEXT'];

					if(preg_match('/^\$*[\d\.]+$/',$line['QARESP_RESP_TEXT']))
					{
						print ' payable to ' . $to;

						if(!is_null($sum))
						{
							$qu = mysql_query('SELECT * FROM PAYPAL_LIST WHERE PAYPAL_ID=' . $sum) or print mysql_error();
							$code = mysql_fetch_array($qu,MYSQL_ASSOC);
						//	print '</td><td><form action="https://www.paypal.com/cgi-bin/webscr" method="post">' . $code['PAYPAL_CODE'] . '<input type="hidden" name="amount" value="' . preg_replace("/\$/","",$line['QARESP_RESP_TEXT']) . '"></form>';
							eval($code['PAYPAL_CODE']);
						}
						
						else
							print '</td><td>Check';
					}

					print '</td></tr>';

				}
				else if($type=='int' && $line['QARESP_RESP_INT'] == 1)
					print '<tr><td>' . $name . '</td><td>' . $to . '</td></tr>';
				else
					return false;
				return true;
			}
			return false;
		}
		print '<table style="font-size: medium;margin-bottom:10px" class="cf" width="100%">';
		print '<tr style="background-color: #dddddd"><td>Description</td><td>Amount</td><td>Payment</td></tr>';

		$qu = mysql_query('SELECT * FROM SUM_LIST');

		$itemprinted = false;
		while($line = mysql_fetch_array($qu, MYSQL_ASSOC))
		{
			$itemprinted = donationlines($line['SUM_NAME'],$line['SUM_TO'],$line['SUM_QNO'],$line['SUM_TYPE'],$line['SUM_PAYPAL']) || $itemprinted;
		}
		if(!$itemprinted)
			print '<tr><td>No items.</td><td></td></tr>';

		print '</table></form>';

	if(!$print)
		print PrintView('check.php?group=' . $_GET['group'] . '&print');

	if(!$print)
		{
		/*
		?>
		<h2 style="margin-top:20px;padding-top:10px;border-top:1px dotted black">Conclusion by Karen Hyde</h2><div style="font-size: medium" class="msg">
		<p style="margin-bottom: 3ex; font-size: large">I hope you have found Forms Online to be a useful and time-saving service. If you agree, please take a minute to read this letter to the end.</p>
		<p>This past year, SaratogaHigh.com staff spent thousands of hours developing the infrastructure for seamless communication between parents, teachers, organizations, and students. The service you've just finished using is just the latest example of their work.</p>
		<p>If your children go online to check their friends' class schedules, find their homework assignments and test dates, get game times for sports, or check club calendars, that's also SaratogaHigh.com at work.</p>
		<p>In fact, the next school form, permission slip, or survey you fill out just might be online, right here. Why? Filling out forms online provides a number of benefits to you and to the recipient organizations. For one, your identity online is <span style="text-decoration: underline">certified</span> by virtue of your activation code. And consider that going digital saves time, money, and paper. In these tough economic times, SaratogaHigh.com helps SHS marshal its existing tech resources with services like Forms Online.</p>
		<p>If that sounds promising, keep in mind that it's just one of dozens of exciting features appearing this year. SaratogaHigh.com staff members <span style="text-decoration: underline">each</span> spent hundreds of hours in the school office programming and planning this summer- including 80 hours during the one week prior to August 9th. </p>
		<p>These online services cost money to run. SaratogaHigh.com staff paid all of SaratogaHigh.com's costs out of their pockets this past year: a website server; printers, paper, toner, envelopes, and labels in massive quantities; and much more behind the scenes.</p>
		<p>A problem arose this summer when SaratogaHigh.com staff needed to securely deliver unique activation codes to 1149 parents. For lack of $689.40 worth of postage, they staff invented and executed <a href="/hedwig/">Operation Hedwig</a>, a system to reach more than a thousand households by foot. On the morning of August 9, 2003, scores of runners and bikers, including Saratoga High Cross Country Teams, covered 130 miles in six hours to hand-deliver your personalized manila envelope to your doorstep.</p>
		<p>If SaratogaHigh.com is able to continue its work, you'll reap the benefits through this and future years. The information technology infrastructure they've created- one in which people can disseminate information instantly and reach each other securely- will let you communicate with PTSA, SHS, the Los Gatos-Saratoga District, teachers, and school administration in various ways. The possibilities are endless!</p>
		<p>If you think this is a worthwhile goal, SaratogaHigh.com would be very grateful for your modest donation of just $25, or as much as your budget will allow. Your generous gift will help defray their costs so they can get back to working on the things they love most.</p>
		<p>If you would like to donate, please make checks payable to "SaratogaHigh.com". You can enclose them in the envelope with all your other back-to-school donations and drop it off in the office. If you would like to donate equipment or other resources, they would love to talk to you. Just drop them a line at <a href="mailto:staff@saratogahigh.com">staff@saratogahigh.com</a>. </p>
		<p>Thank you for your support of this invaluable resource.</p>
		<p align="right">Sincerely, </p><br><br>
		<p align="right">Karen Hyde <Br><Br>
		Assistant Principal<br>
		Saratoga High School</p>
		</div>
		<?
		*/
		$next = $qary[$curposition + 1];
		print '<h2 class="grayheading">Confirm</h2>';
		print '<form action="check.php?group=' . $_GET['group'] . '" method="POST">';
			print '<p><input type="hidden" name="pageno" value="' . $page['QAPAGE_ID'] . '"><input type="hidden" name="type" value="saveform"><input type="hidden" name="path" value="' . $next['path'] . $next['name'] . $next['query'] . '">';
			print '<select name="nextpage">';
			if(!is_null($next))
				print '<option value="next">Continue to the next step</option>';
			print '<option value="close">Quit and return to Q&A Home</option></select>';
			print '<input type="hidden" name="pageno" value="50"><input type="hidden" name="type" value="saveform"><input type="submit" name="btn" style="margin-left:5px" value="Go"></p>';

	}
}
else
		{
			print '<p>Q&amp;A Service is a service which allows organizations, clubs, teachers, and students to collect information using online forms.</p>';
			print '<p>You can use Q&amp;A Service to fill out your back-to-school forms for PTSA, LGSJUHSD, and other organizations.</p>';
			print '<p>Please <a href="/login.php?next=%2Fqa%2F">log in</a> to use this service.</p>';
		}
		?>
		<? if(!$print) include '../../inc-footer.php'; ?>
	</body>
</html>
