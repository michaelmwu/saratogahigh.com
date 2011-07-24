<?
// Michael Wu || saratogahigh.com
// directory/search-id.php: search for students by id
// Most probably an XML request thing

include "../inc/config.php";

$xml->register_function('search_box');
$xml->register_function('search_inner');
$xml->handle_request();
?>

<style type="text/css"><!--
	a.lnks {font-weight: bold}
--></style>

<? function search_box($search_id,$element_id) { ?>

<div style="WIDTH: 210px; POSITION: absolute">

<h1 style="margin-bottom: 0px; margin-top: 5px; font-size: large; padding: 2px; background-color: #eeeeee; border: 1px solid #cccccc">Search by Name</h1>
<form style="margin: 0px" action="#" name="sf" onSubmit="search_for('<?=$search_id?>','<?=$element_id?>',document.getElementById('xfn').value,document.getElementById('xln').value);" method="POST">
<p style="margin-top: 2px">Enter either a first name or last name, or both, to search for. The search will correct many minor spelling errors, so you might get nonexact matches.</p>
<dl>
<dt>First Name</dt>
<dd><input type="text" id="xfn" name="xfn" value="<?=$f?>"></dd>
<dt>Last Name</dt>
<dd><input type="text" id="xln" name="xln" value="<?=$l?>"></dd>
</dl>
<p><input type="submit" name="btn" value="Search" onClick="search_inner('searchid','<?=$element_id?>',document.getElementById('xfn').value,document.getElementById('xln').value);"></p>
</form>
</div>

<div id="search_inner" style="LEFT: 225px; width: 470px; POSITION: absolute">
</div>
<?
}

function search_inner($search_id,$element_id,$f = "",$l = "")
{
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
			$hideall = true;
		}
		
		$showresults = true;
	}
	if(!$hideall) { ?>

	<? if($showresults) { ?>
	<h1 style="margin: 0px; margin-top: 5px; font-size: large; padding: 2px; background-color: #eeeeee; border: 1px solid #cccccc">Search Results</h1>
	<?
		if(mysql_num_rows($result) > 0)
		{
			print '<table cellpadding="2">';
			while($line = db::fetch_row($result))
				print '<tr><td><a style="font-weight: bold" href="#" onClick="return loadID(\'' . $search_id . '\',\'' . $element_id . '\',' . $line['USER_ID'] . ');">' . $line['USER_FN'] . ' ' . $line['USER_LN'] . '</a></td><td>' . GradePrint($line['USER_GR']) . "</td></tr>\n";
			print "</table>";
		}
		else
			print "<p>Your search yielded no matches. Check your spelling and try again.</p>";
		
		mysql_free_result($result);
		}
	}
	else
	{ ?>
		<script type="text/javascript">
		<!--
			loadID('<?=$search_id?>','<?=$element_id>?',<?=$line['USER_ID']?>);
		-->
		</script>
	<? }
} ?>