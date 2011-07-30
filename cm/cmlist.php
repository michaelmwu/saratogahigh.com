<?

if($_SERVER['PATH_INFO'] != '/cm/index.php' && $_SERVER['PATH_INFO'] != '/cm/cmview.php')
	die('Error.');

$where = array();

if(is_numeric($classid))
{
	$where[] = "CMFOLDER_COURSE='$classid'";
	if(is_numeric($teacherid))
		$where[] = "CMFOLDER_TEACHER='$teacherid'";
	else
		$where[] = "CMFOLDER_TEACHER IS NULL";
}
else if(is_numeric($classcatid))
	$where[] = "CMFOLDER_CLASSCAT='$classcatid'";

$wherestring = implode(" AND ",$where);

$rscmfolders = mysql_query('SELECT CMFOLDER_ID, CMFOLDER_TITLE, CMFOLDER_SORT FROM CMFOLDER_LIST WHERE ' . $wherestring . ' ORDER BY CMFOLDER_TITLE');

if(mysql_num_rows($rscmfolders) > 0)
{
	while($cfolder = mysql_fetch_array($rscmfolders, MYSQL_ASSOC))
	{
		print '<li><span style="font-weight: bold">' . htmlentities($cfolder['CMFOLDER_TITLE']) . '</span><ul class="flat">';
		
		if($cfolder['CMFOLDER_SORT'] == 'title')
		{
			$orderstr = 'CM_TITLE';
		}
		else
		{
			$orderstr = 'CM_ID';
		}
		
		$rscms = mysql_query('SELECT CM_ID, CM_TYPE, CM_TITLE, CM_DESC, UNIX_TIMESTAMP(CM_DATE) DATE, CM_LENGTH, CM_FILENAME, FILETYPE_DESC, FILETYPE_ICON FROM CM_LIST LEFT JOIN FILETYPE_LIST ON CM_FILETYPE=FILETYPE_ID WHERE CM_FOLDER=' . $cfolder['CMFOLDER_ID'] . ' ORDER BY ' . $orderstr . ' ASC');
		
		if(mysql_num_rows($rscms) > 0)
		{
			while($cms = mysql_fetch_array($rscms, MYSQL_ASSOC))
			{
				print '<li><a href="file.php?id=' . $cms['CM_ID'] . '">';
				
				if(strlen(trim($cms['CM_TITLE'])) > 0)
					print htmlentities($cms['CM_TITLE']);
				else
					print '(no title)';
				
				print '</a>';

				if($cms['CM_TYPE'] == 'File')
					print ' <img src="/imgs/fileicons/' . $cms['FILETYPE_ICON'] . '" style="vertical-align: middle" alt="" title="' . $cms['FILETYPE_DESC'] . '">';
				print ' <span style="color: #999; font-size: small">';		


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
				
					if($cms['CM_TYPE'] == 'File')	
						print ', ' . $cms['FILETYPE_DESC'];
				}
				else if($cms['CM_TYPE'] == 'Link')
				{
					print htmlentities($cms['CM_FILENAME']);
				}
				
				print ', ' . date('j M Y', $cms['DATE']) . '</span> ' . htmlentities($cms['CM_DESC']) . '</li>';
			}
		}
		else
			print '<li><span style="font-style: italic">None</span></li>';
		
		print '</ul>';
	}
}
else
{
	print '<li>None listed</li>';
}

?>
