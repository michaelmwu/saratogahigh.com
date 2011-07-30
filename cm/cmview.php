<?
// Philip Sung | 0x7d3 | saratogahigh.com
// cm/cmview.php: displays class materials list

include '../db.php';
include 'cm.php';

if($canedit)
{
	if($_POST['action'] == 'newfolder' && $canedit)
	{
		if(strlen($_POST['foldername']) > 0)
		{
			mysql_query('INSERT INTO CMFOLDER_LIST VALUES ("", "' . $classid . '", "' . $teacherid . '", "' . $classcatid . '","' . $_POST['foldername'] . '","' . $_POST['sortby'] . '")');
		}
		else
			$errorm = '<p>Please type a title for your new category in the box.</p>';
	}
	
	if(is_numeric($_GET['delitem']) && $canedit)
	{
		$rspermissions = mysql_query('SELECT COUNT(*) FROM CMFOLDER_LIST INNER JOIN CM_LIST ON CM_FOLDER=CMFOLDER_ID WHERE CM_ID="' . $_GET['delitem'] . '"');
		
		//when classid and teacherid are set in cm.php it verifies to make sure you have permission to modify that teacher's classes
		
		$permission = mysql_fetch_array($rspermissions, MYSQL_ASSOC);
		
		if($permission['COUNT(*)'] > 0)
		{
			mysql_query('DELETE FROM CM_LIST WHERE CM_ID=' . $_GET['delitem']);
			mysql_query('DELETE FROM CMFRAGMENT_LIST WHERE CMFRAGMENT_CM=' . $_GET['delitem']);
		}
		
		header('location: http://' . DNAME . '/cm/cmview.php?' . $getstring . '#itemtable');
	}
	
	if(is_numeric($_GET['delfolder']) && $canedit)
	{
		$rspermissions = mysql_query('SELECT CMFOLDER_ID, COUNT(CM_ID) FROM CMFOLDER_LIST LEFT JOIN CM_LIST ON CM_FOLDER=CMFOLDER_ID WHERE CMFOLDER_ID="' . $_GET['delfolder'] . '" GROUP BY CMFOLDER_ID');
		
		$permission = mysql_fetch_array($rspermissions, MYSQL_ASSOC);
		
		if($permission['CMFOLDER_ID'] > 0 && $permission['COUNT(CM_ID)'] == 0)
			mysql_query('DELETE FROM CMFOLDER_LIST WHERE CMFOLDER_ID=' . $_GET['delfolder']);
		
		header('location: http://' . DNAME . '/cm/cmview.php?' . $getstring . '#itemtable');
	}	
	
	if(is_numeric($_GET['folderid']) && (($_GET['sortby'] == 'title') || ($_GET['sortby'] == 'date')))
	{
		mysql_query('UPDATE CMFOLDER_LIST SET CMFOLDER_SORT = "' . $_GET['sortby'] . '" WHERE CMFOLDER_ID="' . $_GET['folderid'] . '"');
		
		header('location: http://' . DNAME . '/cm/cmview.php?' . $getstring . '#folder' . $_GET['folderid']);
	}
	
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
	<title><?
	print 'Course Materials: ';
	if($filterclass)
	{
		print $c['CLASS_NAME'];
		if($filterteacher)
			print ' — ' . $t['TEACHER_NAME'];
	}
	else if($filterclasscat)
	{
		print $cc['CLASSCAT_NAME'];
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
if($filterclass || $filterclasscat)
{
	// Print title
	print '<div>';
	print '<div style="padding: 3px; margin: 0 3px 0 0; float: left; font-size: large; background-color: #c33; color: #fff">' . ($filterclass?'Course':'Category') . ' Materials</div>';
	print '<h1 style="padding: 3px; background-color: #eee; font-size: large; margin: 0; font-weight: normal">';
	print '<a href="./?' . $getstring . '"><span style="font-weight: bold;">';
	if($filterclass)
	{
		print $c['CLASS_NAME'];
		if($filterteacher)
			print ' ' . $t['TEACHER_NAME'];
	}
	else if($filterclasscat)
		print $cc['CLASSCAT_NAME'];
	print '</span>';
	
	print '</a>';
	print '</h1>';
	print '</div>';
	
	print '<div style="color: #800; font-size: medium">' . $errorm . '</div>';
	
	print '<h2>Course Materials</h2>';
	
	print '<ul class="flat" style="font-size: medium; margin-left: 0">';
	
	include 'cmlist.php';
	
	print '</ul>';
	
	if($canedit)
	{
		print '<h2>Add Course Materials</h2>';

		print '<form method="POST" action="cmview.php?' . $getstring . '" style="margin: 0; font-size: medium"><p>Create new category named <input type="text" name="foldername" value=""><br>Sort contents by <input type="radio" checked name="sortby" value="date" id="sortdate"><label for "sortdate">Date</label><input type="radio" name="sortby" value="title" id="sorttitle"><label for "sorttitle">Title</label> <input type="submit" name="" value="Create"><input type="hidden" name="action" value="newfolder"></p></form>';
		
		$rscategories = mysql_query('SELECT CMFOLDER_ID, CMFOLDER_TITLE FROM CMFOLDER_LIST WHERE ' . $wherestring . ' ORDER BY CMFOLDER_TITLE');
		
		if(mysql_num_rows($rscategories) > 0)
		{
			print '<form method="POST" action="post.php?' . $getstring . '" style="margin: 0; font-size: medium"><p>Add new <input type="radio" checked name="itemtype" value="file" id="itemfile"> <label for="itemfile">File</label> <input type="radio" name="itemtype" value="link" id="itemlink"> <label for="itemlink">Web Link</label> <input type="radio" name="itemtype" value="memo" id="itemmemo"> <label for="itemmemo">Memo</label><br>to category <select name="category">';
			
			while($category = mysql_fetch_array($rscategories, MYSQL_ASSOC))
			{
				print '<option value="' . $category['CMFOLDER_ID'] . '">' . htmlentities($category['CMFOLDER_TITLE']) . '</option>';
			}
			
			print '</select><input type="submit" name="" value="Create..."></form>';
			
			print '<h2><a name="itemtable">Edit Items</a></h2>';
		
			$rscmfolders = mysql_query('SELECT CMFOLDER_ID, CMFOLDER_TITLE, CMFOLDER_SORT FROM CMFOLDER_LIST WHERE ' . implode(" AND 
",$where) . ' ORDER BY CMFOLDER_TITLE');

			print '<table style="font-size: small" cellpadding="2" cellspacing="1">';
			if(mysql_num_rows($rscmfolders) > 0)
			{
				$i = 0;
				while($cfolder = mysql_fetch_array($rscmfolders, MYSQL_ASSOC))
				{
					$i++;
					print '<tr style="' . ($i > 1 ? 'margin-top: 12px; ' : '') . 'font-weight: bold"><td></td><td class="header">' . ($cfolder['CMFOLDER_SORT'] == 'title' ? '<a name="folder' . $cfolder['CMFOLDER_ID'] . '">Title</a> <img style="vertical-align: middle" src="/imgs/tri-down.gif">' : '<a name="folder' . $cfolder['CMFOLDER_ID'] . '" href="cmview.php?class=' . $classid . '&amp;folderid=' . $cfolder['CMFOLDER_ID'] . '&amp;teacher=' . $teacherid . '&amp;sortby=title">Title</a>') . '</td><td class="header">Type</td><td class="header" style="text-align: right">Size</td><td class="header">' . ($cfolder['CMFOLDER_SORT'] == 'date' ? 'Date <img style="vertical-align: middle" src="/imgs/tri-down.gif">' : '<a href="cmview.php?class=' . $classid . '&amp;folderid=' . $cfolder['CMFOLDER_ID'] . '&amp;teacher=' . $teacherid . '&amp;sortby=date">Date</a>') . '</td><td class="header">Actions</td></tr>';				

					$rscms = mysql_query('SELECT CM_ID, CM_TYPE, CM_TITLE, CM_DESC, UNIX_TIMESTAMP(CM_DATE) DATE, CM_LENGTH, CM_FILENAME, FILETYPE_DESC, FILETYPE_ICON FROM CM_LIST LEFT JOIN FILETYPE_LIST ON CM_FILETYPE=FILETYPE_ID WHERE CM_FOLDER=' . $cfolder['CMFOLDER_ID'] . ' ORDER BY ' . ($cfolder['CMFOLDER_SORT'] == 'title' ? 'CM_TITLE' : 'CM_DATE') . ' ASC');

					print '<tr><td></td><td class="data" style="font-weight: bold">' . htmlentities($cfolder['CMFOLDER_TITLE']) . '</td><td class="data">Category</td><td class="data" style="text-align: right">' . mysql_num_rows($rscms) . ' item';
					if(mysql_num_rows($rscms) != 1)
						print 's';

					print '</td><td class="data">&nbsp;</td><td class="data">';
					if(mysql_num_rows($rscms) == 0)
						print '<a href="cmview.php?' . $getstring . '&amp;delfolder=' . $cfolder['CMFOLDER_ID'] . '" onclick="return window.confirm(\'Are you sure you want to delete this category?\')">Delete</a>';
					else
						print '<a style="color: #999" href="cmview.php?' . $getstring . '" onclick="window.alert(\'You have to delete all the items in a category before you can delete the category itself.\'); return false;">Delete</a>';					
					print '</td></tr>';

					if(mysql_num_rows($rscms) > 0)
					{
						while($cms = mysql_fetch_array($rscms, MYSQL_ASSOC))
						{
							print '<tr>';
							
							print '<td style="padding: 0px">';
							if($cms['CM_TYPE'] == 'File')
								print '<img src="/imgs/fileicons/' . $cms['FILETYPE_ICON'] . '" style="vertical-align: middle" alt="" title="' . $cms['FILETYPE_DESC'] . '">';
							print '</td>';
							
							print '<td class="data" style="padding-left: 18px">';
							if(strlen(trim($cms['CM_TITLE'])) > 0)
								print htmlentities($cms['CM_TITLE']);
							else
								print '(no title)';
							print '</td>';
							
							print '<td class="data">';
							if($cms['CM_TYPE'] == 'File')
								print $cms['FILETYPE_DESC'];
							else if($cms['CM_TYPE'] == 'Link')
								print 'Web Link';
							else if($cms['CM_TYPE'] == 'Memo')
								print 'Memo';
							print '</td>';

							print '<td class="data" style="text-align: right">';
							if($cms['CM_TYPE'] == 'File' || $cms['CM_TYPE'] == 'Message')
							{
								if($cms['CM_LENGTH'] == 1)
									print '1 byte';
								else if($cms['CM_LENGTH'] < 1024)
									print $cms['CM_LENGTH'] . ' bytes';
								else if($cms['CM_LENGTH'] < 10240)
									print floor($cms['CM_LENGTH']/102.4)/10  . ' kb';
								else if($cms['CM_LENGTH'] < 1048576)
									print floor($cms['CM_LENGTH']/1024)  . ' kb';
								else if($cms['CM_LENGTH'] < 10485760)
									print floor($cms['CM_LENGTH']/104857.6)/10  . ' mb';
								else
									print floor($cms['CM_LENGTH']/1048576)  . ' mb';
							}
							print '</td>';
							
							print '<td class="data">' . date('j M Y', $cms['DATE']) . '</td>';
							print '<td class="data"><a href="cmview.php?' . $getstring . '&amp;delitem=' . $cms['CM_ID'] . '" onclick="return window.confirm(\'Are you sure you want to delete this item?\')">Delete</a></td>';
							
							print '</tr>';
						}
					}
				}
			}
			print '</table>';
		}
		else
			print '<p>Once you create one or more categories, you can add your own items (files or links) to the Course Materials list.</p>';
	}
}
?>
<? include '../inc-footer.php'; ?>
</body>
</html>
