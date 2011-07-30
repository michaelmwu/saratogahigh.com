<? $config = array(
				'title' => 'Create New Account'
			);
		
require_once 'inc/config.php';

$xml->register_function('checkinfo');
$xml->register_function('checkpass');
$xml->register_function('checkemail');
$xml->register_function('checkuname');

$xml->handle_request();

$page->header();

function checkinfo($xfn,$xln,$code)
{
	if ( !ereg( '(^$)|(^([- a-zA-Z\'\\(\\)\\.]{1,16})$)', $xfn ) )
		return 'You must enter your first name.';
	else if ( !ereg( '^([- a-zA-Z\']{1,16})$', $xln ) )
		return 'You must enter your last name.';
		
	if(strlen($code) > 0)
	{
		if(!$line = db::get_prefix_row('USER',"(USER_UNAME='' OR USER_UNAME IS NULL) AND
			USER_FN='" . $xfn . "' AND
			USER_LN='" . $xln . "' AND
			USER_ACTIVATION='" . $code . "'") )
		{
			if($_POST["sf"] == 'true')
				return 'Sorry, your account information could not be found. Please check your spelling and your activation code and try again. If you have trouble, contact an administrator.';
			else
				return 'Your name does not match your activation code.';
		}
	}
	else
		return 'Please enter your activation code.';
}

function checkpass($pass1,$pass2)
{
	if(strlen($pass1) < 6)
		return 'Your password must be at least six characters long.';
	else if($pass1 != $pass2)
		return 'The passwords you entered did not match.';
	return false;
}

function checkemail($email)
{
	if(!email::is_valid($email,EMAIL_DNS))
		return 'Your email address appears to be invalid.';
	return false;
}

function checkuname($uname)
{
	if (!ereg(USERNAME_REGEX, $uname))
	{
		if($_POST['sf'])
			return 'Your username was invalid. Valid usernames may contain letters, numbers, _, and - and may be at most 32 characters long.';
		else
			return 'Your username is invalid.';
	}
	else if($uline = db::get_prefix_row('USER', "USER_UNAME='" . $uname . "'") )
		return 'That user name is already taken.';
	return false;
}
?>

<h1 style="letter-spacing: 2pt; margin-bottom: 0px">Create New Account</h1>

<?

$form = new form($page);

$xfn = new input;
$xfn->name = "xfn";
$xfn->id = "xfn";
$xfn->maxlength = 16;
$xfn->css = "width: 10em;";
$xfn->add_javascript('onChange','checkInfo();');
$xfn->add_javascript('onKeyUp','checkInfo();');

$form->add_element($xfn);

$xln = new input;
$xln->name = "xln";
$xln->id = "xln";
$xln->maxlength = 16;
$xln->css = "width: 10em;";
$xln->add_javascript('onChange','checkInfo();');
$xln->add_javascript('onKeyUp','checkInfo();');

$form->add_element($xln);

$code = new input;
$code->name = "code";
$code->id = "code";
$code->Css = "width: 10em;";
$code->add_javascript('onChange','checkInfo();');
$code->add_javascript('onKeyUp','checkInfo();');

$form->add_element($code);

$un = new input;
$un->name = "un";
$un->id = "un";
$un->maxlength = 32;
$un->css = "width: 10em;";
$un->add_javascript('onChange','checkUname();');
$un->add_javascript('onKeyUp','checkUname();');

$form->add_element($un);

$pw = new password;
$pw->name = "pw";
$pw->id = "pw";
$pw->css = "width: 10em;";
$pw->add_javascript('onChange','checkPass();');
$pw->add_javascript('onKeyUp','checkPass();');

$form->add_element($pw);

$pw2 = new password;
$pw2->name = "pw2";
$pw2->id = "pw2";
$pw2->css = "width: 10em;";
$pw2->add_javascript('onChange','checkPass();');
$pw2->add_javascript('onKeyUp','checkPass();');

$form->add_element($pw2);

$email = new input;
$email->name = "email";
$email->id = "email";
$email->maxlength = 48;
$email->css = "width: 18em;";
$email->add_javascript('onChange','checkEmail();');
$email->add_javascript('onKeyUp','checkEmail();');

$form->add_element($email);


if($_POST["sf"] == 'true')
{
	if ( $error = checkinfo( stripslashes( $_POST["xfn"]), stripslashes($_POST["xln"]), $_POST['code'] ) )
		$page->error($error);
	else if ( $error = checkpass($_POST['pw'],$_POST['pw2']) )
		$page->error($error);
	else if ( $error = checkemail( stripslashes( $_POST["email"] ) ) )
		$page->error($error);
	else if ( $error = checkuname( stripslashes( $_POST['un'] ) ) )
		$page->error($error);
	else
	{
		$done = 1;
		if($line = db::get_prefix_row('USER',"(USER_UNAME='' OR USER_UNAME IS NULL) AND
			USER_FN='" . $_POST["xfn"] . "' AND
			USER_LN='" . $_POST["xln"] . "' AND
			USER_ACTIVATION='" . $_POST['code'] . "'") )
		{
			mysql_query("UPDATE USER_LIST
			SET
				USER_UNAME='" . $_POST["un"] . "',
				USER_PW=MD5('" . $_POST["pw"] . "'),
				USER_EMAIL='" . $_POST["email"] . "',
				USER_ACTIVATION=Null,
				USER_VALIDATED=1
			WHERE
				USER_ID=" . $line['USER_ID']) or die("Update failed");

				print "<p style=\"font-size: medium\">Your account has been successfully created. <a href=\"login.php?next=\" style=\"font-weight:bold\">Login now</a></p>";
		}
			
		$page->do_error();
	}
}

if($done != 1)
{

?>

<p style="font-size: medium">You must be a Saratoga High student or parent to register for a SaratogaHigh.com account.</p>
<p style="font-size: medium">You'll need to have your activation code handy. Codes are mailed out to all Saratoga High students and parents without SaratogaHigh.com accounts once each year during August. This year, you should have received one by Saturday, August 14th, 2004. For security reasons, we can't distribute activation codes by e-mail.</p>
<p style="font-size: medium; color: #800000">If you have any problems, contact us using the "QUESTIONS OR COMMENTS?" form at the bottom of the page. Be sure to include your name and email address.</p>

<script type="text/javascript">
<!--
	function checkInfo()
	{
		url = "new-user2.php";
	
		args = Array( document.getElementById('xfn').value, document.getElementById('xln').value, document.getElementById('code').value );
	
		xmlhttp.open("POST", url,true);
		xmlhttp.onreadystatechange=function()
		{
			if (xmlhttp.readyState==4)
			{
				document.getElementById("info_infobox").innerHTML = xmlhttp.responseText;
			}
		}
		xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		xmlhttp.send('XMLRequest=1&XMLFunction=checkinfo&XMLArgs=' + args.toPHP() + '&XMLPrint=1');	
		return false;
	}
	
	function checkUname()
	{
		url = "new-user2.php";
	
		args = Array( document.getElementById('un').value );

		xmlhttp.open("POST", url,true);
		xmlhttp.onreadystatechange=function()
		{
			if (xmlhttp.readyState==4)
			{
				document.getElementById("user_infobox").innerHTML = xmlhttp.responseText;
			}
		}
		xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		xmlhttp.send('XMLRequest=1&XMLFunction=checkuname&XMLArgs=' + args.toPHP() + '&XMLPrint=1');	
		return false;
	}
	
	function checkPass()
	{
		url = "new-user2.php";
	
		args = Array( document.getElementById('pw').value, document.getElementById('pw2').value );

		xmlhttp.open("POST", url,true);
		xmlhttp.onreadystatechange=function()
		{
			if (xmlhttp.readyState==4)
			{
				document.getElementById("pass_infobox").innerHTML = xmlhttp.responseText;
			}
		}
		xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		xmlhttp.send('XMLRequest=1&XMLFunction=checkpass&XMLArgs=' + args.toPHP() + '&XMLPrint=1');	
		return false;
	}
	
	function checkEmail()
	{
		url = "new-user2.php";
	
		args = Array( document.getElementById('email').value );

		xmlhttp.open("POST", url,true);
		xmlhttp.onreadystatechange=function()
		{
			if (xmlhttp.readyState==4)
			{
				document.getElementById("email_infobox").innerHTML = xmlhttp.responseText;
			}
		}
		xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		xmlhttp.send('XMLRequest=1&XMLFunction=checkemail&XMLArgs=' + args.toPHP() + '&XMLPrint=1');	
		return false;
	}
// -->
</script>

<form action="new-user.php" name="mf" method="POST">

<div style="font-size: medium; padding: 2px; background-color: #cccccc">Please copy these three fields directly from your activation sheet.</div>
<div style="font-size: medium; padding: 2px; background-color: #dddddd">(They have to match the information in our database. If you'd like to change how your name is displayed, just contact us <em>after</em> you've registered.)</div>
<table cellpadding="1" cellspacing="0"><tr>
<td class="lefttd">First Name</td>
<td><? $form->element('xfn'); ?></td>
</tr></table>
<table cellpadding="1" cellspacing="0"><tr>
<td class="lefttd">Last Name</td>
<td><? $form->element('xln'); ?></td>
</tr></table>
<table cellpadding="1" cellspacing="0"><tr>
<td class="lefttd">Activation Code</td>
<td><? $form->element('code'); ?></td>
<td><div id="info_infobox"></div></td>
</tr></table>
<div style="font-size: medium; padding: 2px; background-color: #cccccc">Login Information</div>
<table cellpadding="1" cellspacing="0"><tr>
<td class="lefttd">Username</td>
<td><? $form->element('un'); ?></td>
<td><div id="user_infobox"></div></td>
</tr></table>
<div class="fieldinfo">Choose a username for yourself. It may contain letters, numbers, - (dash), and _ (underscore), and may be up to 32 characters long.</div>
<table cellpadding="1" cellspacing="0"><tr>
<td class="lefttd">Password</td>
<td><? $form->element('pw'); ?></td>
</tr></table>
<table cellpadding="1" cellspacing="0"><tr>
<td class="lefttd">Confirm Password</td>
<td><? $form->element('pw2'); ?></td>
<td><div id="pass_infobox"></div></td>
</tr></table>
<div class="fieldinfo">Your password must be at least six characters long.</div>
<table cellpadding="1" cellspacing="0"><tr>
<td class="lefttd">Email Address</td>
<td><? $form->element('email'); ?></td>
<td><div id="email_infobox"></div></td>
</tr></table>
<div class="fieldinfo">Be sure to use your <b>main email address</b>; if you forget your password, you must have access to your email. Your email address won't be rented, sold, shared, or displayed without your consent.</div>

<!--
<table cellpadding="1" cellspacing="0"><tr>
<td class="lefttd">Student ID#</td>
<td><input style="width: 4em" type="text" name="sid" maxlength="6" value="<? echo htmlentities(stripslashes($_POST["sid"])); ?>"></td>
</tr></table>
<div class="fieldinfo">If you're not a student, leave this blank.</div>
-->

<h2>Terms of Service</h2>
<p>By submitting the form you agree that the information SaratogaHigh.com provides comes with no warranty, quality guarantee, or service guarantee.</p>
<p style="font-weight: bold; color: #800000"><input type="hidden" name="sf" value="true"><input type="submit" name="btn" value="Submit"></p>
</form>

<script type="text/javascript">
<!--
document.mf.xfn.focus();
// -->
</script>
<?
	}
?>
	<? include 'inc-footer.php'; ?></body>
</html>