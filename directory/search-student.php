<?
// Philip Sung | 0x7d3 | saratogahigh.com
// directory/search-student.php: search for students by name

include "../db.php";

// Process queries from FIND PERSON box in header
if($_POST["a"] == "qsearch")
{
	$q = $_POST["q"];
	
	// Grab users by userid
	if(is_numeric($q))
		$result = mysql_query("SELECT USER_ID, USER_FN, USER_LN, USER_GR FROM USER_LIST WHERE USER_ID=$q");
	// No spaces in name? Then search for exact matches for first OR last name
	else if(strpos($q, ' ') === false)
	{
		$result = mysql_query("SELECT USER_ID, USER_FN, USER_LN, USER_GR FROM USER_LIST WHERE USER_FN='$q' OR USER_LN='$q' ORDER BY USER_GR DESC, USER_LN, USER_FN");
		if(mysql_num_rows($result) == 0)
		{
			mysql_free_result($result);
			$result = mysql_query("SELECT USER_ID, USER_FN, USER_LN, USER_GR FROM USER_LIST WHERE USER_FN_SOUNDEX=SOUNDEX('$q') OR USER_LN_SOUNDEX=SOUNDEX('$q') ORDER BY (USER_FN='$q' OR USER_LN='$q') DESC, USER_GR DESC, USER_LN, USER_FN");
		}
	}
	// Search on fullname
	else
	{
		$result = mysql_query("SELECT USER_ID, USER_FN, USER_LN, USER_GR FROM USER_LIST WHERE USER_FULLNAME='$q' ORDER BY USER_GR DESC, USER_LN, USER_FN");
		if(mysql_num_rows($result) == 0)
		{
			mysql_free_result($result);
			$result = mysql_query("SELECT USER_ID, USER_FN, USER_LN, USER_GR FROM USER_LIST WHERE USER_FULLNAME_SOUNDEX=SOUNDEX('$q') ORDER BY (USER_FULLNAME='$q') DESC, USER_GR DESC, USER_LN, USER_FN");
		}
	}
	
	// If exactly one result, then redirect automatically to that person
	if(mysql_num_rows($result) == 1)
	{
		$line = mysql_fetch_array($result, MYSQL_ASSOC);
		header("Location: ./?id=" . $line['USER_ID']);
		$hideall = true;
	}
	
	$l = ' ';
	
	$showresults = true;
}
// Process firstname/lastname search from this page.
else if($_POST["btn"] == "Search")
{
	$l = stripslashes($_POST["xln"]);
	$f = stripslashes($_POST["xfn"]);
	
	$hideall = false;

	if($l == "" && $f == "")
		$showresults = false;
	else
	{
		// Search by first or last name or both
		if($l == "")
			$query = "SELECT USER_ID, USER_FN, USER_LN, USER_GR FROM USER_LIST WHERE USER_FN_SOUNDEX=SOUNDEX('" . addslashes($f) . "') ORDER BY (USER_FN='" . addslashes($f) . "') DESC, USER_GR DESC, USER_LN, USER_FN";
		else if($f == "")
			$query = "SELECT USER_ID, USER_FN, USER_LN, USER_GR FROM USER_LIST WHERE USER_LN_SOUNDEX=SOUNDEX('" . addslashes($l) . "') ORDER BY (USER_LN='" . addslashes($l) . "') DESC, USER_GR DESC, USER_LN, USER_FN";
		else
			$query = "SELECT USER_ID, USER_FN, USER_LN, USER_GR FROM USER_LIST WHERE USER_LN_SOUNDEX=SOUNDEX('" . addslashes($l) . "') AND USER_FN_SOUNDEX=SOUNDEX('" . addslashes($f) . "') ORDER BY (USER_LN='" . addslashes($l) . "') DESC, (USER_FN='" . addslashes($f) . "') DESC, USER_GR DESC, USER_LN, USER_FN";

		$result = mysql_query($query) or die("Query failed");
		
		if(mysql_num_rows($result) == 1)
		{
			$line = mysql_fetch_array($result, MYSQL_ASSOC);
			header("Location: ./?id=" . $line['USER_ID']);
			$hideall = true;
		}
		
		$showresults = true;
	}
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
	<head>
		<title>Search by Name</title>
		<link rel="stylesheet" type="text/css" href="../shs.css">
		<meta name="vs_targetSchema" content="http://schemas.microsoft.com/intellisense/ie5">
		<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
		<style type="text/css"><!--
			a.lnks {font-weight: bold}
		--></style>
	</head>
<? if(!$hideall) { ?>
	<body>
	
<? include "inc-header.php" ?>

<div style="WIDTH: 210px; POSITION: absolute">

<h1 style="margin-bottom: 0px; margin-top: 5px; font-size: large; padding: 2px; background-color: #eeeeee; border: 1px solid #cccccc">Search by Name</h1>
<form style="margin: 0px" action="search-student.php" name="sf" method="POST">
<p style="margin-top: 2px">Enter either a first name or last name, or both, to search for. The search will correct many minor spelling errors, so you might get nonexact matches.</p>
<dl>
<dt>First Name</dt>
<dd><input type="text" name="xfn" value="<?= htmlentities(stripslashes($_POST["xfn"])) ?>"></dd>
<dt>Last Name</dt>
<dd><input type="text" name="xln" value="<?= htmlentities(stripslashes($_POST["xln"])) ?>"></dd>
</dl>
<p><input type="submit" name="btn" value="Search"></p>
</form>

</div>
<div style="LEFT: 225px; width: 470px; POSITION: absolute">
	
<? if($showresults) { ?>
<h1 style="margin: 0px; margin-top: 5px; font-size: large; padding: 2px; background-color: #eeeeee; border: 1px solid #cccccc">Search Results</h1>
<?
	if(mysql_num_rows($result) > 0)
	{
		print '<table cellpadding="2">';
		while($line = mysql_fetch_array($result, MYSQL_ASSOC))
			print '<tr><td><a style="font-weight: bold" href="./?id=' . $line['USER_ID'] . '">' . $line['USER_FN'] . ' ' . $line['USER_LN'] . '</a></td><td>' . GradePrint($line['USER_GR']) . "</td></tr>\n";
		print "</table>";
	}
	else
		print "<p>Your search yielded no matches. Check your spelling and try again.</p>";
	
	mysql_free_result($result);
?>

<? } ?>
<script type="text/javascript">
<!--
document.sf.xfn.focus();
// -->
</script>

<? include '../inc-footer.php'; ?>
</div>
</body>
<? } ?>
</html>
