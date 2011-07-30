<? include "db.php";

$h = 0;
$nextpath = $_GET["next"];

if(emailvalidation($_GET['email']))
	header("location: http://" . DNAME . "/mailpass.php?email=" . $_GET['email']);
elseif($_GET['email'] && !emailvalidation($_GET['email']))
	$h = 2;
	
if($h == 2 && $_GET['reset'] == "Reset")
{
	header("location: http://" . DNAME . "/resetpw.php?un=" . $_GET['email']);
}

if($loggedin)
{
	if(strlen($nextpath) > 0)
		header("location: http://" . DNAME . $nextpath);
	else
		header("location: http://" . DNAME . "/");
}

if($_POST["job"] == "login")
{
	$result = mysql_query("SELECT * FROM USER_LIST
		WHERE USER_UNAME!='' AND USER_UNAME='" . $_POST['un'] . "'
		AND (USER_PW=PASSWORD('" . $_POST['pw'] . "') OR USER_PW=MD5('" . $_POST['pw'] . "'))") or die("Query failed");

	if($line = mysql_fetch_array($result, MYSQL_ASSOC))
	{
		if(strlen($line['USER_PW']) == 16) // password() encoded password, change to MD5
		{
			mysql_query("UPDATE USER_LIST SET USER_PW=MD5('" . $_POST['pw'] . "') WHERE USER_ID=" . $line['USER_ID']) or die("Query failed");
			$line['USER_PW'] = md5($_POST['pw']);
		}


		// Only affects the login box, not loginas!
		if($line['USER_STATUS'] > 0 || (SITE_ENABLED && ((IsParent($line['USER_GR']) && PARENT_ENABLED) || (IsTeacher($line['USER_GR']) && TEACHER_ENABLED) || (IsAlum($line['USER_GR']) && ALUM_ENABLED) || (IsStudent($line['USER_GR']) && STUDENT_ENABLED))))
		{
			if($line['USER_STATUS'] > 0 || !is_null($line['USER_TEACHERTAG']))
				$timeout = 0;
			else
				$timeout = time() + 864000;

			setcookie("UN", $line["USER_UNAME"], $timeout, "/");
			setcookie("UNO", $line["USER_ID"], $timeout, "/");
			setcookie("PW", $line["USER_PW"], $timeout, "/");

			$tu = mysql_query('SELECT * FROM USER_LIST WHERE USER_ID=' . $line["USER_ID"] . ' AND USER_UNAME !=\'\' AND USER_UNAME=\'' . addslashes($line['USER_UNAME']) . '\' AND USER_PW=\'' . addslashes($line['USER_PW']) . '\'') or die('User query failed');
			if($userR = mysql_fetch_array($tu, MYSQL_ASSOC))
				$loggedin = true;
			mysql_free_result($tu);

			if(strlen($nextpath) > 0)
				header("location: http://" . DNAME . $nextpath);
			else
				header("location: http://" . DNAME . "/");

			$h = 1;
		}
		else
			$resp = "Sorry, the site has been temporarily disabled for maintenance. We apologize for the inconvenience.";
	}
	else
		$resp = "Either your username or password was incorrect.";

	mysql_free_result($result);
}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>

<head>
	<title>Login</title>
	<link rel="stylesheet" type="text/css" href="../shs.css">
	<meta name="vs_targetSchema" content="http://schemas.microsoft.com/intellisense/ie5">
	<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
	<script type="text/javascript"><!--
		if (window.location.href.match(/http:\/\/saratogahigh\.com/))
			window.location.href = window.location.href.replace(/^http:\/\/saratogahigh\.com/,"http://www.saratogahigh.com");
		// fixes the http://saratogahigh.com/ and http://www.saratogahigh.com/ compatibility issues
		// makes all webpages start with http://www.saratogahigh.com/
	// --></script>
</head>

<body>

<?

//pw reset, showing 2 choices if username was entered

if($h == 2)
{
include "inc-header.php";

print '<p>You entered your username, <b>' . $_GET['email'] . '</b>. Please confirm the following.</p>';
print '<p><a href="./mailpass.php?email=' . $_GET['email'] . '"?>I don\'t remember my username/password combination, please send me an email so I can reset my password.</a><br>(Warning: This action will generate a new activation code for your account.)</p>';
}

elseif($h != 1)
{

?>

<? include "inc-header.php" ?>
<?

if($_GET['reqd'] == 'true')
	print '<p style="font-size: medium; font-style: italic; border-bottom: 2px #c33 solid; margin: 0px;  padding: 8px">The page you requested requires you to log in.</p>';
if(!SITE_ENABLED)
	print '<p style="font-size: medium; font-style: italic; border-bottom: 2px #c44 solid; margin: 0px; padding: 8px">Login has been temporarily disabled for all users. We apologize for the inconvenience.</p>';
//else
//{
	//if(!PARENT_ENABLED)
	//print '<p style="font-size: medium; font-style: italic; border-bottom: 2px #c44 solid; margin: 0px; padding: 8px">Login has been temporarily disabled for parents. We apologize for the inconvenience.</p>';
	i//f(!STUDENT_ENABLED)
	//print '<p style="font-size: medium; font-style: italic; border-bottom: 2px #c44 solid; margin: 0px; padding: 8px">Login has been temporarily disabled for parents. We apologize for the inconvenience.</p>';
//}

?>
<table width="100%" cellpadding="3" cellspacing="0">
<tr>
<td style="width: 260px; vertical-align: top; background-color: #933; color: #fff">
    <form name="lf" action="login.php?next=<?= urlencode($nextpath) ?>" method="POST" style="font-size: medium; margin: 0">
    <h1 style="font-size: large; margin: 0 0 6px 0; ; border-bottom: 2px dotted #fff">Log In...</h1>
    <?
    if($_POST["btn"] == "Login" && strlen($resp) > 0)
    	print '<p style="margin: 0; padding: 2px; background-color: #fff; color: #000; border: 2px solid #999">' . $resp . '</p>';
    ?>
    <table>
    	<tr>
    	<td>Username</td>
    	<td><input type="text" name="un" value="<?= $_POST["un"]; ?>"></td>
    	</tr>
    	<tr>
    	<td>Password</td>
    	<td><input type="password" name="pw"></td>
    	</tr>
    	<tr><td></td><td><input type="hidden" name="job" value="login"><input type="submit" name="btn" value="Login"></td></tr>
    </table>

    <p style="margin: 0; padding: 2px; background-color: #fff; color: #000; border: 2px solid #999">New users: Are you holding an <span style="font-weight: bold">activation code</span>? <a href="new-user.php">Create an account</a> before you try to log in.</p>
	
    </form>
</td><td style="width: 260px; vertical-align: top; background-color: #ccc">
    <form name="fg" action="login.php" method="GET" style="margin: 0">
    <h2 style="margin: 0; margin: 0 0 6px 0; border-bottom: 2px dotted #000">...or reset your password</h2>
    <p><b>Returning users</b>: if you forgot your password, you can have it reset. Please enter the username or email address associated with your account. You'll be emailed a link to reset your password.</p>
    <table>
    <tr>
    <td>Email Address or Username</td>
	</tr>
	<tr>
    <td><input type="text" name="email" value=""> <input type="submit" value="Go"></td></tr>
	<tr><td><b>Have an activation code?</b>: Enter a username above and press the button below.</td></tr>
	<tr><td><input name="reset" type="submit" value="Reset"></td></tr>
    </table>
    </form>
</td><td style="vertical-align: top; width: 260px;">

	<h2 style="margin: 0; margin: 0 0 6px 0; ">Problems?</h2>
	<p>Other problems?<br>Fill out the "Questions or Comments?" box below and write us a note.</p>

</td></tr></table>

<? if($_GET['forgot']==true)
	{ ?>
	<script type="text/javascript">
	<!--
	document.fg.email.focus();
	// -->
	</script>
	<? }
	else
	{ ?>
	<script type="text/javascript">
	<!--
	document.lf.un.focus();
	// -->
	</script>

<?	}
}
?>

<? include 'inc-footer.php'; ?></body>

</html>
