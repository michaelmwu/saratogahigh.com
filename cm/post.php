<?
// Philip Sung | 0x7d3 | saratogahigh.com
// cm/post.php: posts new item to course materials list

include '../db.php';
include 'cm.php';

if(!$canedit)
	die();

if(true)
{
	if($filterclass || $filterclasscat)
	{
		if($_POST['action'] == 'newfile' || $_POST['action'] == 'newmemo')
		{
			if($_POST['action'] == 'newfile')
			{
				if(eregi('\\.([a-z0-9]+)$', $_FILES['fileu']['name'], $matches))
				{
					$extension = strtolower($matches[1]);
					
					$exts = mysql_query('SELECT * FROM FILETYPE_LIST WHERE FILETYPE_EXT="' . $extension . '"');
					
					if($ext = mysql_fetch_array($exts, MYSQL_ASSOC))
					{
						switch($_FILES['fileu']['error'])
						{
						case 0:
							$filehandle = fopen($_FILES['fileu']['tmp_name'], "rb") or die("Can't open file!");
							mysql_query('INSERT INTO CM_LIST VALUES ("", ' . $_POST['category'] . ', "File", "' . $_POST['title'] . '", ' . $userid . ', "' . $_POST['desc'] . '", "' . date(TIME_FORMAT_SQL, CURRENT_TIME) . '", "' . $_FILES['fileu']['name'] . '", ' . $ext['FILETYPE_ID'] . ', ' . $_FILES['fileu']['size'] . ')') or die(mysql_error());
							$file_id = mysql_insert_id();
							while (!feof($filehandle))
								mysql_query('INSERT INTO CMFRAGMENT_LIST VALUES ("", ' . $file_id . ', "' . base64_encode(fread($filehandle, 48000)) . '")') or die(mysql_error());
							fclose($filehandle);
							
							$success = true;
							break;
						case 1:
						case 2:
							$errorm = 'The file uploaded is larger than 2mb.';
							break;
						case 3:
							$errorm = 'The file was only uploaded partially.';
							break;
						case 4:
							$errorm = 'No file was uploaded.';
							break;
						default:
							$errorm = 'We have no idea what is going on, sorry.';
						}
					}
					else
						$errorm = 'That doesn\'t appear to be a supported file type.';
				}
				else
					$errorm = 'This file doesn\'t appear to have an extension, so we couldn\'t determine its file type.';
			}
			else if($_POST['action'] == 'newmemo')
			{
			
				$msgstr = stripslashes($_POST['msg']);
				$slen = strlen($msgstr);
				
				mysql_query('INSERT INTO CM_LIST VALUES ("", ' . $_POST['category'] . ', "Message", "' . $_POST['title'] . '", ' . $userid . ', "' . $_POST['desc'] . '", "' . date(TIME_FORMAT_SQL, CURRENT_TIME) . '", null, null, ' . $slen . ')');
				$file_id = mysql_insert_id();
				$i = 0;
				while($i < $slen)
				{
					mysql_query('INSERT INTO CMFRAGMENT_LIST VALUES ("", ' . $file_id . ', "' . base64_encode(substr($msgstr, $i, 48000)) . '")');
					$i += 48000;
				}
				
				$success = true;
			}
		}
		else if($_POST['action'] == 'newlink')
		{
			$msgstr = stripslashes($_POST['link']);
				
			mysql_query('INSERT INTO CM_LIST VALUES ("", ' . $_POST['category'] . ', "Link", "' . $_POST['title'] . '", ' . $userid . ', "' . $_POST['desc'] . '", "' . date(TIME_FORMAT_SQL, CURRENT_TIME) . '", "' . $msgstr . '", null, null)');
				
			$success = true;
		}
		
		if($success)
			header('location: http://' . DNAME . '/cm/cmview.php?' . $getstring;
	}
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
	<title><?
	if($filterclass || $filterclasscat)
	{
		print 'Course Materials: ';
		print $c['CLASS_NAME'];
		print ' - ';
	print $t['TEACHER_NAME'];
		print ': Post Course Materials';
	}
		
	?></title>
	<link rel="stylesheet" type="text/css" href="../shs.css">
	<meta name="vs_targetSchema" content="http://schemas.microsoft.com/intellisense/ie5">
	<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
	<style type="text/css"><!--
		h2 { border-bottom: 2px solid #666 }
	--></style>
</head>
<body>

<? include "inc-header.php" ?>

<?
if( $filterclass || $filterclasscat)
{
	// Print title
	print '<div>';
	print '<div style="padding: 3px; margin: 0 3px 0 0; float: left; font-size: large; background-color: #c33; color: #fff">Course Materials</div>';
	print '<h1 style="padding: 3px; background-color: #eee; font-size: large; margin: 0; font-weight: normal">';
	print '<a href="./?' . $getstring . '">';
	if($filterclass)
	{
		print '<span style="font-weight: bold">' . $c['CLASS_NAME'] . '</span>';
		if($filterteacher)
			print ' ' . $t['TEACHER_NAME'];
	}
	else if($filterclasscat)
		print $cc['CLASSCAT_NAME'];
	print '</a>: <a href="cmview.php?' . $getstring . '">Course Materials</a>';
	print '</h1>';
	print '</div>';
	
	print '<div style="color: #800; font-size: medium">' . $errorm . '</div>';
	
	if($_POST['itemtype'] == 'file')
	{
		print '<h2>Upload File</h2>';
		print '<form style="margin: 0" enctype="multipart/form-data" action="post.php?' . $getstring . '" method="POST">';
		print '<table>';
		print '<tr><td style="font-weight: bold">Title</td><td><input style="width: 20em" type="text" name="title"></td><td style="color: #999">e.g. "Worksheet #4"</td></tr>';
		print '<tr><td style="font-weight: bold">Description</td><td><input style="width: 20em" type="text" name="desc"></td><td style="color: #999">e.g. "Extra practice for final"</td></tr>';
		print '<tr><td style="font-weight: bold">Select File...</td><td><input type="file" name="fileu"></td><td style="color: #999"></td></tr>';
		print '</table>';
		print '<input type="hidden" name="MAX_FILE_SIZE" value="2048000" />';
		print '<input type="submit" name="" value="Submit"><input type="hidden" name="category" value="' . htmlentities($_POST['category']) . '"><input type="hidden" name="action" value="newfile"><input type="hidden" name="itemtype" value="file">';
		print '</form>';
		
		print '<h2>Supported File Types</h2>';
		
		$exts = mysql_query('SELECT * FROM FILETYPE_LIST ORDER BY FILETYPE_EXT');
		
		print '<table>';
		while($ext = mysql_fetch_array($exts, MYSQL_ASSOC))
		{
			print '<tr><td>.' . $ext['FILETYPE_EXT'] . '</td><td><img src="/imgs/fileicons/' . $ext['FILETYPE_ICON'] . '"></td><td>' . $ext['FILETYPE_DESC'] . '</td></tr>';		
		}
		print '</table>';
	}

	if($_POST['itemtype'] == 'memo')
	{
		print '<h2>New Memo</h2>';
		print '<form style="margin: 0" enctype="multipart/form-data" action="post.php?' . $getstring . '" method="POST">';
		print '<table>';
		print '<tr><td style="font-weight: bold">Title</td><td><input style="width: 20em" type="text" name="title"></td><td style="color: #999">e.g. "A note to students"</td></tr>';
		print '<tr><td style="font-weight: bold">Description</td><td><input style="width: 20em" type="text" name="desc"></td><td style="color: #999">e.g. "Info pertaining to this class"</td></tr>';
		print '<tr><td style="font-weight: bold;" valign="top">Message</td><td><textarea style="width: 50em; height: 15em;" name="msg"></textarea></td><td style="color: #999" valign="top">e.g. "Welcome students!<BR>This class is composed of..."</td></tr>';
		print '</table>';
		print '<input type="submit" name="" value="Submit"><input type="hidden" name="category" value="' . htmlentities($_POST['category']) . '"><input type="hidden" name="action" value="newmemo"><input type="hidden" name="itemtype" value="memo">';
		print '</form>';
		
		print '</table>';
	}

	if($_POST['itemtype'] == 'link')
	{
		print '<h2>New Web Link</h2>';
		print '<form style="margin: 0" enctype="multipart/form-data" action="post.php?' . $getstring . '" method="POST">';
		print '<table>';
		print '<tr><td style="font-weight: bold">Title</td><td><input style="width: 20em" type="text" name="title"></td><td style="color: #999">e.g. "Google"</td></tr>';
		print '<tr><td style="font-weight: bold">Description</td><td><input style="width: 20em" type="text" name="desc"></td><td style="color: #999">e.g. "A popular all-purpose search engine."</td></tr>';
		print '<tr><td style="font-weight: bold">URL</td><td><input style="width: 20em" type="text" name="link"></td><td style="color: #999">e.g. "http://www.google.com"</td></tr>';
		print '</table>';
		print '<input type="submit" name="" value="Submit"><input type="hidden" name="category" value="' . htmlentities($_POST['category']) . '"><input type="hidden" name="action" value="newlink"><input type="hidden" name="itemtype" value="memo">';
		print '</form>';
		
		print '</table>';
	}
}
?>
</body>
</html>
