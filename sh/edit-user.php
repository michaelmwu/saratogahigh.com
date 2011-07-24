<? include "db.php"; ?>
<?

if($loggedin)
{
	if($isadmin && is_numeric($_GET["id"]))
	{
		$sid = $_GET["id"];
		$isv = false;
	}
	else
	{
		$sid = $userid;
		$isv = $isvalidated;
	}

	$result = mysql_query("SELECT * FROM USER_LIST WHERE USER_ID=$sid") or die("User query failed");
	
	$l = mysql_fetch_array($result, MYSQL_ASSOC);
	mysql_free_result($result);
}
else
	forceLogin();
	
$done = false;

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
	<head>
		<title>Edit Account</title>
		<link rel="stylesheet" type="text/css" href="../shs.css">
		<meta name="vs_targetSchema" content="http://schemas.microsoft.com/intellisense/ie5">
		<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
	</head>
	<body>
	
<? include "inc-header.php" ?>

<h1>Edit Account</h1>

<?

if($_POST["btn"] == "Save")
{
	$aun = stripslashes($_POST['un']);
	$aaddr = stripslashes($_POST['addr']);
	$acity = stripslashes($_POST['city']);
	$azip = stripslashes($_POST['zip']);
	$axfn = stripslashes($_POST['xfn']);
	$axln = stripslashes($_POST['xln']);
	$agr = stripslashes($_POST['gr']);
	$atag = stripslashes($_POST['tag']);
	$aemail = stripslashes($_POST['email']);
	$asid = stripslashes($_POST['sid']);
	$amailcap = stripslashes($_POST['mailcap']);

	if(strlen($aemail) > 48)
		print '<p>Sorry, your email address cannot exceed 48 characters in length.</p>';
	else if (!ereg('^[-_.a-zA-Z0-9]+@[-_.a-zA-Z0-9]+\\.([-_.a-zA-Z0-9]{1,3})$', $aemail) && !$isadmin)
		print '<p>The email address you entered appears to be invalid.</p>';
	else if ($admin && !ereg('^([-_a-zA-Z0-9]{1,32})$', $aun))
		print '<p>The username you entered was invalid. Valid usernames may contain letters, numbers, _, and -, and may be at most 32 characters long.</p>';
	else
	{
		if($isadmin && is_numeric($agr))
		{
			if(!is_numeric($asid))
				$asid = 'Null';
			if(strlen($aun) < 1)
				$aun = 'Null';
			else
				$aun = "'" . addslashes($aun) . "'";
			if(!is_numeric($atag))
				$atag = 'Null';
			if(!is_numeric($amailcap))
				$amailcap = 'Null';
			
			$unameconflicts = mysql_query('SELECT COUNT(*) FROM USER_LIST WHERE USER_ID!=' . $sid . ' AND USER_UNAME=' . $aun);
			$numconflicts = mysql_fetch_array($unameconflicts, MYSQL_ASSOC);
			
			if($numconflicts['COUNT(*)'] > 0 && strlen($aun) > 0)
			{
				print '<p>That username was already taken.</p>';
			}
			else
			{		
				mysql_query("UPDATE USER_LIST SET USER_SID=" . $asid . ",
					USER_EMAIL='" . addslashes($aemail) . "',
					USER_TEACHERTAG=" . $atag . ",
					USER_UNAME=" . $aun . ",
					USER_GR=" . $agr . ",
					USER_ADDRESS='" . $aaddr . "',
					USER_CITY='" . $acity . "',
					USER_ZIP='" . $azip . "',
					USER_MAILCAP=" . $amailcap . ",
					USER_FN=CONCAT(UPPER(LEFT('" . $_POST["xfn"] . "',1)),SUBSTRING('" . $_POST["xfn"] . "' FROM 2)),
					USER_LN=CONCAT(UPPER(LEFT('" . $_POST["xln"] . "',1)),SUBSTRING('" . $_POST["xln"] . "' FROM 2)),
					USER_FULLNAME=CONCAT_WS(' ', USER_FN, USER_LN),
					USER_FN_SOUNDEX=SOUNDEX(USER_FN),
					USER_LN_SOUNDEX=SOUNDEX(USER_LN),
					USER_FULLNAME_SOUNDEX=SOUNDEX(USER_FULLNAME)
					WHERE USER_ID=$sid") or die("User update failed" . mysql_error());
					
				$success = true;
			}
		}
		else
		{
			mysql_query("UPDATE USER_LIST SET USER_EMAIL='" . addslashes($aemail) . "' WHERE USER_ID=$sid") or die("Update failed");
			$success = true;
		}

		if($success)
			print "<p>Your account has been successfully updated. <a href=\"./\">Home</a> <a href=\"directory/?id=$sid\">Your schedule</a></p>";

		$done = true;
	}
}
else
{
	$aun = $l["USER_UNAME"];
	$axfn = $l["USER_FN"];
	$axln = $l["USER_LN"];
	$aaddr = $l["USER_ADDRESS"];
	$acity = $l["USER_CITY"];
	$azip = $l["USER_ZIP"];
	$agr = $l["USER_GR"];
	$atag = $l["USER_TEACHERTAG"];
	$aac = $l["USER_ACTIVATION"];
	$aemail = $l["USER_EMAIL"];
	$asid = $l["USER_SID"];
	$aaim = $l["USER_AIM"];
	$amailcap = $l["USER_MAILCAP"];
}

if(!$done)
{
?>
<form action="edit-user.php?id=<?= $sid ?>" name="mf" method="POST">
<table>

<? if($isadmin) { ?>
	<tr>
	<td>First Name</td>
	<td><input type="text" maxlength="16" size="20" name="xfn" value="<?= htmlentities($axfn) ?>"></td>
	</tr>
	<tr>
	<td>Last Name</td>
	<td><input type="text" maxlength="16" size="20" name="xln" value="<?= htmlentities($axln) ?>"></td>
	</tr>
	<tr>
	<td style="vertical-align: top;">Address</td>
	<td><input type="text" maxlength="48" size="35" name="addr" value="<?= htmlentities($aaddr) ?>">
	<br><input type="text" maxlength="16" size="16" name="city" value="<?= htmlentities($acity) ?>">, CA <input type="text" maxlength="5" size="8" name="zip" value="<?= htmlentities($azip) ?>">
	</td>
	</tr>
	<tr>
	<td>Email Address</td>
	<td><input type="text" maxlength="48" size="35" name="email" value="<?= htmlentities($aemail) ?>"></td>
	</tr>
	<tr>
	<td>User Name</td>
	<td><input type="text" maxlength="32" size="35" name="un" value="<?= htmlentities($aun) ?>"></td>
	</tr>
	<tr>
	<td>Student ID#</td>
	<td><input type="text" maxlength="6" size="8" name="sid" value="<?= $asid ?>"></td>
	</tr>
	<tr>
	<td>Grade</td>
	<td><select name="gr">
		<?
		for($i = C_SCHOOLYEAR - 4; $i <= C_SCHOOLYEAR + 4; $i++)
		{
			print '<option ';
			if($agr == $i)
				print 'selected ';
			print 'value="' .  $i . '">' . GradePrint($i) . '</option>';
		}

		?>
		<option <? if($agr=='0') { print "selected"; } ?> value="0">Faculty</option>
		<option <? if($agr=='1') { print "selected"; } ?> value="1">Parent</option>
	</select></td>
	</tr>
	<tr>
		<td>Teacher Tag</td>
		<td>
		<?
		print "<select name=\"tag\">";
		$result = mysql_query("SELECT * FROM TEACHER_LIST ORDER BY TEACHER_NAME") or die("User query failed");
		print "<option value=\"\">Not applicable</option>";
		while ($line = mysql_fetch_array($result, MYSQL_ASSOC))
		{
			print "<option ";
			if($line["TEACHER_ID"] == $atag)
				print " selected ";
			print "value=\"" . $line["TEACHER_ID"] . "\">" . $line["TEACHER_NAME"] . "</option>";
		}
		mysql_free_result($result);
		print "</select>";
		?></td>
	</tr>
	<tr>
	<td>Activation Code</td>
	<td style="font-weight: bold"><? if(is_null($aac))
		print '(Activated)';
	else
		print $aac; ?></td>
	</tr>
	<tr>
	<td>AIM SN</td>
	<td style="font-weight: bold">
	<? if(is_null($aaim))
		print 'None';
	else
		print "$aaim <a href=\"resetsn.php\">Reset</a>"; ?></td>
	</tr>
	<tr>
	<td>Shmail Cap</td>
	<td><input type="text" maxlength="4" size="6" name="mailcap" value="<?= htmlentities($amailcap) ?>"></td>
	</tr>
<? } else { ?>
	<tr>
	<td>First Name</td>
	<td><?= $axfn ?></td>
	</tr>
	<tr>
	<td>Last Name</td>
	<td><?= $axln ?></td>
	</tr>
	<tr>
	<td style="vertical-align: top;">Address</td>
	<td><?= htmlentities($aaddr) ?>
	<br><?= htmlentities($acity) ?>, CA <?= htmlentities($azip) ?>
	</td>
	</tr>
	<tr>
	<td>Email Address</td>
	<td><input type="text" size="35" name="email" value="<?= $aemail ?>"></td>
	</tr>
	<? if(IsStudent($agr)) { ?>
		<tr>
		<td>Grade</td>
		<td style="font-weight: bold"><?= GradePrint($agr) ?><input type="hidden" name="gr" value="<?= $agr ?>"></td>
		</tr>
	<? } else { ?>
		<tr>
		<td>Level</td>
		<td style="font-weight: bold"><?= GradePrint($agr) ?><input type="hidden" name="gr" value="<?= $agr ?>"></td>
		</tr>
	<? } ?>
	<tr>
	<td>AIM SN</td>
	<td style="font-weight: bold">
	<? if(is_null($aaim))
		print 'None';
	else
		print "$aaim <a href=\"resetsn.php\">Reset</a>"; ?></td>
	</tr>
<? } ?>
</table>

<p><input type="submit" name="btn" value="Save"></p>

</form>

<?	} ?>

<? include 'inc-footer.php'; ?>
	</body>
</html>
