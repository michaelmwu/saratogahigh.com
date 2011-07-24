<?
include 'db.php';

$showpage = false;

// Only do a quick check to see if the username is syntactically valid
if(ereg(USERNAME_REGEX, $_GET["un"]))
{
	$showpage = true;

	if($_POST['action'] == 'change')
	{
		// Search by activation code and username
		$result = mysql_query("SELECT * FROM USER_LIST WHERE USER_UNAME='" . $_GET['un'] . "'") or die("Grab failed");

		if($list = mysql_fetch_array($result, MYSQL_ASSOC))
		{
			if($_POST['code'] == $list['USER_ACTIVATION'])
			{
				if($_POST['newpw'] == $_POST['newpw2'])
				{
					if(strlen($_POST["newpw"]) < 6)
					{
						$errorm = "<p>Your password must be at least six characters long.</p>";
					}
					else
					{
	        				mysql_query("UPDATE USER_LIST SET USER_PW=MD5('" . $_POST['newpw'] . "'), USER_ACTIVATION=Null, USER_VALIDATED=1 WHERE USER_ID=" . $list['USER_ID']);
        	
        	    				/*
						if($line['USER_STATUS'] > 0 || !is_null($line['USER_TEACHERTAG']))
			            			$timeout = 0;
      	  			    	else
	      	      			$timeout = time() + 864000;
            	
	        			    	setcookie("UN", $line["USER_UNAME"], $timeout, "/");
		        		    	setcookie("UNO", $line["USER_ID"], $timeout, "/");
            					setcookie("PW", md5($_POST["newpw"]), $timeout, "/");
				            	*/

      	  	    			$errorm = '<p>Your password was successfully changed. <a href="/login.php">Log in</a></p>';
					}
				}
				else
        			{
        				$errorm = '<p>Your passwords didn\'t match. Please <a href="javascript:go.back(1)">go back</a> and try again.</p>';
	        		}
			}
			else
			{
				$errorm = '<p>Your activation code seems to be invalid.</p>';
			}
		}
		else
	    	{
    			$errorm = '<p>We couldn\'t find your user record. Check that your activation code and your username are correct.</p>';
	    	}
	}
}
else
{
	$errorm = '<p>That doesn\'t appear to be a valid username.</p>';
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
	<head>
		<title>Reset Password</title>
		<link rel="stylesheet" type="text/css" href="shs.css">
	</head>
	<body>
	
<? include "inc-header.php" ?>
<h1 style="font-size: large">Reset Your Password</h1>
<?= $errorm ?>
<? if($showpage) { ?>
<form action="resetpw.php?un=<?= urlencode($_GET['un']) ?>&code=<?=urlencode($_GET['code'])?>" method="POST">
<table>
<tr><td><b>Username:</b></td><td><?= htmlentities($_GET['un']) ?></td></tr>
<? if(!isset($_GET['code'])) { ?><tr><td><b>Activation code:</b></td><td><input type="text" name="code" value=""></td></tr><? } else print '<input type="hidden" name="code" value="' . $_GET['code'] .'">'; ?>
<tr><td><b>New password:</b></td><td><input name="newpw" type="password"></td></tr>
<tr><td><b>Confirm new password:</b></td><td><input name="newpw2" type="password"></td></tr>
<tr><td><input type="hidden" name="uid" value="<?= $list['USER_ID'] ?>"></td>
<td><input type="hidden" name="action" value="change"><input type="submit" value="Change Password"></td></tr>
</table>
</form>
<? } ?>
<? include 'inc-footer.php'; ?>

</body>
</html>
