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

		//$paypalmatch = preg_match("#^https*://[\w]{1,10}.paypal.com/#",$_SERVER['HTTP_REFERER']))
		if($_GET['confirm'] == 2)
		{
			print '<p class="informational">Thank you for your payment. Your transaction has been completed, and a receipt for your purchase has been emailed to you. You may log into your account at <a href="www.paypal.com/us">www.paypal.com/us"</a> to view details of this transaction.</p>';
		}
		else if($_GET['confirm'] == 1)
		{
			print '<p class="informational">Your donation was canceled.</p>';
		}

		?>
		<p style="font-size: medium">If you filled in a donation amount on any form, the amount is listed below for your reference.</p>

		<p>The PTSA would like all parents to use SaratogaHigh.com to fill in all PTSA	forms for ease in form processing. The PTSA is now accepting only secure online payment via PayPal, an eBay subsidiary and the world's most popular online payment service. However, if you do not wish to pay online, please make a check out to Saratoga High PTSA, attach a copy of your 'Join Saratoga High PTSA' form with your check, and place both in the PTSA box in the school office. This helps Membership VPs keep track of payments.</p>

		<?
		function donationlines ($name, $to, $qno, $type, $sum, $print)
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

					//if($print)
						//print '<tr><td colspan="3"><div style="width:6in; height:2in; border:1px dashed black; text-align:center; margin-bottom:15px">Attach check to ' . $to . '</div>';
					//print '</td></tr>';

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
			$itemprinted = donationlines($line['SUM_NAME'],$line['SUM_TO'],$line['SUM_QNO'],$line['SUM_TYPE'],$line['SUM_PAYPAL'], $print) || $itemprinted;
		}
		if(!$itemprinted)
			print '<tr><td>No items.</td><td></td></tr>';

		print '</table></form>';

	if(!$print)
		print PrintView('check.php?group=' . $_GET['group'] . '&print');

	if(!$print)
		{
		$next = $qary[$curposition + 1];
		print '<h2 class="grayheading">Confirm</h2>';
		print '<form action="check.php?group=' . $_GET['group'] . '" method="POST">';
			print '<p><input type="hidden" name="pageno" value="' . $page['QAPAGE_ID'] . '"><input type="hidden" name="type" value="saveform"><input type="hidden" name="path" value="' . $next['path'] . $next['name'] . $next['query'] . '">';
			print '<select name="nextpage">';
			if(!is_null($next))
				print '<option value="next">Continue to the next step</option>';
			print '<option value="close">Quit and return to' . $curgroup['QAGROUP_TITLE'] . '</option></select>';
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
