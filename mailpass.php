<?
// mailpass.php
// emails passwords

include 'db.php';

if($_POST['email'])
	$email = $_POST['email'];
elseif($_GET['email'])
	$email = $_GET['email'];

if (emailvalidation($email))
{
	$eresult = mysql_query("SELECT * FROM USER_LIST WHERE USER_EMAIL='" . $email . "'") or die(mysql_error());
	$errorm = "Sorry, we could not find that email in our database.";
}
else
{
	$eresult = mysql_query("SELECT * FROM USER_LIST WHERE USER_UNAME='" . $email . "'") or die(mysql_error());
	$errorm = "Sorry, we could not find that username in our database.";
}
if(strlen($email) == 0)
{
	$errorm = 'Please enter a username or email.';
	$errorshow = true;
}
else
{
	if($e = mysql_fetch_array($eresult, MYSQL_ASSOC))
	{
		$newactivation = NewActivationCode();
		mysql_query("UPDATE USER_LIST SET USER_ACTIVATION='$newactivation' WHERE USER_ID='" . $e['USER_ID'] . "'") or die("Could not update! " . mysql_error());
		$newactivation = urlencode($newactivation);
		$config = array( 'from' => "staff",
						'to' => $e['USER_EMAIL'],
						'subject' => "SaratogaHigh.com Reset Password Request",
						'message' => "Dear " . $e['USER_FULLNAME'] . ",\n\n"
		. "We received a request to reset your SaratogaHigh.com password. Follow the link below to reset your password. Do NOT click the link below if you do not wish to change your password.\n\n"
		. "http://" . DNAME . "/resetpw.php?un=" . urlencode($e['USER_UNAME']) . "&code=$newactivation\n\n"
		. "This email was automatically generated in response to a request to reset a password. If you feel that this email has been sent in error, please notify the staff at SaratogaHigh.com\n\n"
		. "Sincerely,\n"
		. "The Staff of SaratogaHigh.com" );
		email($config);
	}
	else
		$errorshow = true;
}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>

<head>
	<title>Email Password Reminder</title>
	<link rel="stylesheet" type="text/css" href="../shs.css">
	<meta name="vs_targetSchema" content="http://schemas.microsoft.com/intellisense/ie5">
	<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
</head>

<body>

<? include "inc-header.php" ?>
<table width="100%" cellpadding="3" cellspacing="0">
<tr>
<td style="width: 260px; vertical-align: top; background-color: #933; color: #fff">
    <form name="lf" action="mailpass.php" method="POST" style="font-size: medium; margin: 0">
    <h1 style="font-size: large; margin: 0 0 6px 0; ; border-bottom: 2px dotted #fff">Email Password Reminder</h1>
</td>
</tr>
<tr>
<td>
<?
	if(!$errorshow)
	{
		if(emailvalidation($email))
			print 'A reset password link has been sent to ' . $e['USER_EMAIL'] . ' with a new activation code. Please check your email.';
		else
			print 'A reset password link has been sent to the email address registered to your account. Please check your email.';
	}
	else
		print $errorm;
?>
</td>
</tr>
</table>
</body>
</html>

