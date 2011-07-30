<?
include '../db.php';
require 'cpvalidation.php';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
	<head>
		<title>Create New Account</title>
		<link rel="stylesheet" type="text/css" href="../shs.css">
		<meta name="vs_targetSchema" content="http://schemas.microsoft.com/intellisense/ie5">
		<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
		<link rel="stylesheet" type="text/css" href="admin.css">
		<style type="text/css"><!--
			span.reg { display: inline }
			a.reg-new { font-weight: bold }
		--></style>
	</head>
	<body>
	
<? include "inc-header.php"; ?>

<?
function make_seed()
{
   list($usec, $sec) = explode(' ', microtime());
   return (float) $sec + ((double) $usec * 100000);
}

function rand_hex()
{
   mt_srand(make_seed());
   $randval = mt_rand(0,255);
   return sprintf("%02X",$randval);
}

if(($isadmin || $issuperparent) && $_POST["btn"] == "Add")
{
	if(is_numeric($_POST["gr"]))
	{
		if (strlen($_POST["xfn"]) > 16)
			print "<p style=\"color: #800000\">Sorry, we can only store the first 16 letters of your first name.</p>";
		else if (strlen($_POST["xln"]) > 16)
			print "<p style=\"color: #800000\">Sorry, we can only store the first 16 letters of your last name.</p>";
		else if (strlen($_POST["xfn"]) == 0)
			print "<p style=\"color: #800000\">You must enter your first name.</p>";
		else if (strlen($_POST["xln"]) == 0)
			print "<p style=\"color: #800000\">You must enter your last name.</p>";
		else
		{
			srand((double)microtime()*1000000);
			$acode = NewActivationCode();
			
			if($_POST['tn'] == 0)
				$_POST['tn'] = "Null";
				
			$verstr = '';
				
			for($j = 1; $j <= 120; $j++)
				$newverstr .= rand_hex();
		
			if($isadmin)
			{
				mysql_query("INSERT INTO USER_LIST (USER_ACTIVATION, USER_TEACHERTAG, USER_FN, USER_LN, USER_GR, USER_FULLNAME, USER_FN_SOUNDEX, USER_LN_SOUNDEX, USER_FULLNAME_SOUNDEX, USER_VERSTR, USER_VALIDATED) VALUES
					('" . $acode . "',
					" . $_POST['tn'] . ",
					CONCAT(UPPER(LEFT('" . $_POST["xfn"] . "',1)),SUBSTRING('" . $_POST["xfn"] . "' FROM 2)),
					CONCAT(UPPER(LEFT('" . $_POST["xln"] . "',1)),SUBSTRING('" . $_POST["xln"] . "' FROM 2)),
					" . $_POST["gr"] . ",
					CONCAT_WS(' ', USER_FN, USER_LN),
					SOUNDEX(USER_FN),
					SOUNDEX(USER_LN), 
					SOUNDEX(USER_FULLNAME), 
					0x" . $newverstr . ",
					0)") or die(mysql_error());
			}
			else
			{
				mysql_query("INSERT INTO USER_LIST (USER_ACTIVATION, USER_TEACHERTAG, USER_FN, USER_LN, USER_GR, USER_FULLNAME, USER_FN_SOUNDEX, USER_LN_SOUNDEX, USER_FULLNAME_SOUNDEX, USER_VALIDATED) VALUES
					(" . $acode . ",Null,CONCAT(UPPER(LEFT('" . $_POST["xfn"] . "',1)),SUBSTRING('" . addslashes($_POST["xfn"]) . "' FROM 2)),CONCAT(UPPER(LEFT('" . addslashes($_POST["xln"]) . "',1)),SUBSTRING('" . addslashes($_POST["xln"]) . "' FROM 2)),15,CONCAT_WS(' ', USER_FN, USER_LN), SOUNDEX(USER_FN), SOUNDEX(USER_LN), SOUNDEX(USER_FULLNAME), 0x" . $newverstr . " 0)") or die("Update failed");
			}
			
			print "<p>The account you requested has been created. The activation code is <span style=\"font-weight: bold\">" . $acode . "</span>.<br>" . $_POST['xfn'] . " can activate his/her account using just his/her name (as you entered it) and the provided activation code. Please tell " . $_POST['xfn'] . " to go to Create New Account from the saratogahigh.com home page, and then enter the requested information.";
		}
	}
}

if($isadmin || $issuperparent)
{

?>

<form action="newuser.php" name="mf" method="POST">

<table>
<tr>
	<td>First Name</td>
	<td><input type="text" name="xfn" value="<? echo $_POST["xfn"]; ?>"></td>
</tr>
<tr>
	<td>Last Name</td>
	<td><input type="text" name="xln" value="<? echo $_POST["xln"]; ?>"></td>
</tr>
<? if($isadmin) { ?>
<tr>
	<td>Grade</td>
	<td>
	
	<select name="gr">
		<?
		for($i = C_SCHOOLYEAR - 8; $i <= C_SCHOOLYEAR + 4; $i++)
		{
			print '<option ';
			if($_POST['gr'] == $i)
				print 'selected ';
			print 'value="' .  $i . '">' . GradePrint($i) . '</option>';
		}

		?>
		<option <? if($_POST['gr']=='0') { print "selected"; } ?> value="0">Teacher</option>
		<option <? if($_POST['gr']=='1') { print "selected"; } ?> value="1">Parent</option>
	</select>
	
	</td>
</tr>
<? } else {?>
	<tr><td>Level</td><td>Parent<input type="hidden" name="gr" value="15"></td></tr>
<? } ?>
<? if($isadmin) { ?>
<tr>
	<td>Select Teacher</td>
	<td>
<?
	print "<select name=\"tn\">";
	$result = mysql_query("SELECT * FROM TEACHER_LIST ORDER BY TEACHER_NAME") or die("User query failed");
	print "<option value=\"0\">Not applicable</option>";
	while ($line = mysql_fetch_array($result, MYSQL_ASSOC))
		print "<option value=\"" . $line["TEACHER_ID"] . "\">" . $line["TEACHER_NAME"] . "</option>\n";
	mysql_free_result($result);
	print "</select>";
?>
</td>
</tr>
<? } ?>
</table>
<p><input type="submit" name="btn" value="Add"></p>
</form>
<script type="text/javascript">
<!--
document.mf.xfn.focus();
// -->
</script>

<? } else { ?>
	<p>You need to be a staff member or a Parent Contact to view this page.</p>
<? } ?>

<? include '../inc-footer.php'; ?></body>
</html>
