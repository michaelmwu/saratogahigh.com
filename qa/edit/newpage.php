<?
include '../../db.php';

$isauthor = false;

if(is_id($_GET['id']) && $loggedin)
{
	$rsqa = mysql_query('SELECT * FROM QA_LIST INNER JOIN QAGROUP_LIST ON QA_GROUP=QAGROUP_ID WHERE QA_ID=' . $_GET['id'] . ' ORDER BY QA_TITLE');
	
	if($qa = mysql_fetch_array($rsqa, MYSQL_ASSOC))
	{
		$showitem = true;
		
		if($qa['QA_OPEN'] == 0)
		{
    		
    		$rsauthor = mysql_query('SELECT * FROM QAAUTHOR_LIST WHERE QAAUTHOR_QAGROUP=' . $qa['QA_GROUP'] . ' AND QAAUTHOR_USER=' . $userid);
    		if($author = mysql_fetch_array($rsauthor, MYSQL_ASSOC))
    		{
    			$isauthor = true;
    			
    			$rspages = mysql_query('SELECT QAPAGE_ID, QAPAGE_ORDER, QAPAGE_TITLE FROM QAPAGE_LIST WHERE QAPAGE_QA=' . $_GET['id'] . ' ORDER BY QAPAGE_ORDER ASC');
				$maxpage = 0;

				while($cpage = mysql_fetch_array($rspages, MYSQL_ASSOC))
				{
					if($maxpage < $cpage['QAPAGE_ORDER'])
						$maxpage = $cpage['QAPAGE_ORDER'];
						
					$pagearr[$cpage['QAPAGE_ORDER']] = $cpage;
				}
    		}
		}
	}
	
	if($isauthor && $_POST['action'] == 'create')
	{
        $newtitle = $_POST['title'];
        $newdesc = $_POST['desc'];
        $newgroup = $_GET['id'];
	  $neworder = $_POST['order'];

		if(strlen($newtitle) > 0)
		{
			if(is_id($neworder) && $neworder <= $maxpage + 1)
			{
				for($i = $maxpage; $i >= $neworder; $i--)
					mysql_query('UPDATE QAPAGE_LIST SET QAPAGE_ORDER=QAPAGE_ORDER + 1 WHERE QAPAGE_ID=' . $pagearr[$i]['QAPAGE_ID']);
			
    			mysql_query("INSERT INTO QAPAGE_LIST (QAPAGE_QA, QAPAGE_TITLE, QAPAGE_DESC, QAPAGE_ORDER) VALUES ($newgroup, '$newtitle', '$newdesc', $neworder)");
    		
    			header('location: http://' . DNAME . '/qa/edit/qa.php?id=' . $newgroup . '&mode=questions&page=' . mysql_insert_id());
			}
		}
		else
		{
			$errorm = 'Please enter a brief title for this form.';
		}
	}	
}
else if($loggedin)
{
	
}
else
{
	forceLogin();
}

if(!$isauthor)
	die();

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
	<head>
		<title><? print htmlentities($qa['QA_TITLE']); ?>: Add New Page</title>
		<link rel="stylesheet" type="text/css" href="../../shs.css">
		<link rel="stylesheet" type="text/css" href="../qa.css">
		<style type="text/css">
			a.lnkh { font-weight: bold }
		</style>
	</head>
	<body>
		<? include "inc-header.php"; ?>
		<? if($showitem) { ?>
		<h1 class="titlebar"><span style="float: right; font-size: medium; padding: 2px"><a href="../qa.php?id=<?= $qa['QA_ID'] ?>">View</a> | <a href="qa.php?id=<?= $qa['QA_ID'] ?>">Administer</a></span><span style="font-size: large"><a href="group.php?id=<?= $qa['QAGROUP_ID'] ?>&amp;sortstr=<?= $_GET['sortstr'] ?>"><?= $qa['QAGROUP_TITLE'] ?></a>:</span> <?= htmlentities($qa['QA_TITLE']) ?></h1>
		<table width="100%" cellpadding="3" cellspacing="0"><tr>
		<td style="vertical-align: top; background-color: #dddddd; width: 225px; font-size: small">
		</td>
		<td style="font-size: medium; vertical-align: top">
		<? if($showitem) { ?>
		<h2 class="grayheading">Add New Page</h2>
		<form style="margin: 0" action="newpage.php?id=<?= $_GET['id'] ?>" method="POST">
		<? if(strlen($errorm) > 0) { print '<p>' . $errorm . '</p>'; } ?>
		<table style="font-size: medium">
		<tr><td style="width: 10em; font-weight: bold">Title</td><td><input type="text" size="50" name="title" value="<?= htmlentities(stripslashes($newtitle)) ?>"></td></tr>
		<tr><td style="vertical-align: top; width: 10em">Description</td><td><textarea cols="35" rows="5" name="desc"><?= htmlentities(stripslashes($newdesc)) ?></textarea></td></tr>
		<tr><td style="width: 10em">Insert at</td><td><select name="order"><?
		
		if($maxpage == 0)
		{
			print '<option value="1">Page 1 (Insert as first page)</option>';
		}
		else
		{
			print '<option ' . (($neworder == 1) ? 'selected ' : '') . 'value="1">Page 1 (Insert before Page 1, "' . htmlentities($pagearr[1]['QAPAGE_TITLE']) . '")</option>';

			for($i = 1; $i <= $maxpage; $i++)
			{
				print '<option ' . (($neworder == $i + 1 || ($i == $maxpage && is_null($neworder))) ? 'selected ' : '') . 'value="' . ($i + 1) . '">Page ' . ($i + 1) . ' (Insert after Page ' . $i . ', "' . htmlentities($pagearr[$i]['QAPAGE_TITLE']) . '")</option>';
			}
		}
		
		?></select></td></tr>
		</table>
		<p style="margin: 0"><input type="hidden" name="action" value="create"><input type="submit" value="Create"></p>
		</form>
		<? } ?>
		</td></tr></table>
		<? include '../../inc-footer.php'; ?>
		<? } ?>
	</body>
</html>
