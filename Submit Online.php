<?
include 'db.php';

if($submissions_open == false)
	print 'Submissions closed on Saturday, March 26. To contact the editors directly about late submissions, email us at staff(at)saratogasoundings.com.';
elseif($submissions_open == true)
{
// Philip Sung | 0x7d3 | saratogahigh.com
// cm/submit-online.php: displays class materials list

// include 'cm.php';
$maxspace = 0.4; //maximum upload file size in megabytes

if($_POST['action'] == 'newfile')
{
	mysql_query('INSERT INTO USER_LIST (USER_ID, USER_FN, USER_LN, USER_MI, USER_EMAIL, USER_GRADE) VALUES("", "' . $_POST['userfn'] . '", "' . $_POST['userln'] . '", "' . $_POST['usermi'] . '", "' . $_POST['email'] . '", "' . $_POST['grade'] . '")') or die("query failed");
	$userid = mysql_insert_id();

	if(eregi('\\.([a-z0-9]+)$', $_FILES['fileu']['name'], $matches))
	{
		$extension = strtolower($matches[1]);

		$exts = mysql_query('SELECT * FROM FILETYPE_LIST WHERE FILETYPE_EXT="' . $extension . '"');

		if($ext = mysql_fetch_array($exts, MYSQL_ASSOC))
		{
			if($_FILES['fileu']['size']  == 0 || $_FILES['fileu']['size'] > $maxspace * 1048576)
				$errorm = "<p style=\"font-weight: strong\">Your file is too large. The maximum size is " . $maxspace . " megabytes. Please try again.</p>";
			else
			{
			mysql_query('INSERT INTO CM_LIST VALUES ("", "' . $_POST['title'] . '", ' . $userid . ', "' . date(TIME_FORMAT_SQL, CURRENT_TIME) . '", "' . $_FILES['fileu']['name'] . '", ' . $ext['FILETYPE_ID'] . ', ' . $_FILES['fileu']['size'] . ')') or die("query failed");
			$file_id = mysql_insert_id();
			$filehandle = fopen($_FILES['fileu']['tmp_name'], "rb") or die("Can't open file!");
			while (!feof($filehandle))
				mysql_query('INSERT INTO CMFRAGMENT_LIST VALUES ("", ' . $file_id . ', "' . base64_encode(fread($filehandle, 48000)) . '")');
			fclose($filehandle);

			$success = true;
			}
		}
		else
			$errorm = 'That doesn\'t appear to be a supported file type.';
	}
	else
		$errorm = 'This file doesn\'t appear to have an extension, so we couldn\'t determine its file type.';
}

		if($success)
			header('location: confirm.php');

?>

include 'inc-head.txt';

<div id="content">
<?

	print $errorm;

	print '<form style="margin: 0" enctype="multipart/form-data" action="submit-online.php" method="POST">';
	print '<table cellpadding="0" cellspacing="0" style="border:0px;margin:0px;padding:0px" width="100%">';
	print '<tr><td style="width:85px">First Name</td><td><input style="width:6em" type="text" name="userfn">
			&nbsp;M.I.<input style="width: 1em" type="text" name="usermi"></td>
			<td style="width:90px">Last Name</td><td><input style="width:10em" type="text" name="userln"></td>
			</tr>';
	print '<tr><td>Email</td><td><input style="width: 10em" type="text" name="email"></td>
			<td>Title of piece</td><td><input style="width:10em" type="text" name="title"></td></tr>';
	//print '<tr><td>Title of piece</td><td colspan="3"><input style="width:10em" type="text" name="title"></td></tr>';
	print '<tr>
	<td><input type="hidden" name="MAX_FILE_SIZE" value=' . $maxspace * 1048576 . '>
	Select File...</td><td colspan="3"><input type="file" name="fileu" style="width:10em;height:22px">
	<input type="hidden" name="action" value="newfile"></td>
	</tr>';
	print '<tr><td colspan="4"><input type="submit" name="" value="Submit"></td></tr></table>';
	print '</form>';

	print '<div style="margin-top:15px;border-bottom: 2px solid #666;font-weight:bold">Currently Supported File Types</div>';

	$exts = mysql_query('SELECT * FROM FILETYPE_LIST ORDER BY FILETYPE_EXT');

	print '<table cellpadding="0" cellspacing="0" style="border:0px;margin:0px;padding:0px" width="60%">';

	while($ext = mysql_fetch_array($exts, MYSQL_ASSOC))
	{

		print '<tr><td class="filetype">.' . $ext['FILETYPE_EXT'] . '</td><td class="filetype"><img src="/fileicons/' . $ext['FILETYPE_ICON'] . '" alt="' . $ext['FILETYPE_EXT'] . '"></td><td class="filetype">' . $ext['FILETYPE_DESC'] . '</td></tr>';
	}
	print '</table>';

	}
?>
</div>
<? include 'inc-foot.txt'; ?>
