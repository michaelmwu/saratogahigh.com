<?

include("../../db.php");

if($_POST['action']=='create' && $isprog)
{
	$newtitle = $_POST['title'];
	$newdesc = $_POST['desc'];
	$prelim = isset($_POST['prelim'])?1:0;
	$confirm = isset($_POST['confirm'])?1:0;
	$receipt = isset($_POST['receipt'])?1:0;
      $newdate = date(TIME_FORMAT_SQL, CURRENT_TIME);

	if(strlen($newtitle) > 0)
	{
		mysql_query("INSERT INTO QAGROUP_LIST (QAGROUP_ID,QAGROUP_TITLE,QAGROUP_DESC,QAGROUP_DATE,QAGROUP_SHOWPRELIM,QAGROUP_SHOWCONFIRM,QAGROUP_SHOWRECEIPT,QAGROUP_ACTIVE) VALUES ('','$newtitle','$newdesc','$newdate',$prelim,$confirm,$receipt,0)") or die("Could not insert! " . mysql_error());
		$newgroup = mysql_insert_id();
		mysql_query("INSERT INTO QAAUTHOR_LIST (QAAUTHOR_ID,QAAUTHOR_QAGROUP,QAAUTHOR_USER) VALUES ('','$newgroup','$userid')");

		header('location: http://' . DNAME . '/qa/edit/group.php?id=' . $newgroup);
	}
	else
	{
		$errorm = "Please enter a title for this group.";
	}
}
else if($loggedin)
{
}
else
{
	forceLogin();
}

if(!$isprog)
{
	die();
}
?>
<html>
	<head>
		<title>Add New QA Group</title>
		<link rel="stylesheet" type="text/css" href="../../shs.css">
		<link rel="stylesheet" type="text/css" href="../qa.css">
		<style type="text/css">
			a.lnkh { font-weight: bold }
		</style>
	</head>
	<body>
		<? include "inc-header.php"; ?>
		<table width="100%" cellpadding="3" cellspacing="0"><tr>
		<td style="vertical-align: top; background-color: #dddddd; width: 225px; font-size: small">
		</td>
		<td style="font-size: medium; vertical-align: top">
		<h2 class="grayheading">Add New Group</h2>
		<form style="margin: 0" action="newgroup.php" method="POST">
		<? if(strlen($errorm) > 0) { print '<p>' . $errorm . '</p>'; } ?>
		<table style="font-size: medium">
		<tr><td style="width: 10em; font-weight: bold">Title</td><td><input type="text" size="50" name="title" value="<?= htmlentities(stripslashes($newtitle)) ?>"></td></tr>
		<tr><td style="vertical-align: top; width: 10em">Description</td><td><textarea cols="35" rows="5" name="desc"><?= htmlentities(stripslashes($newdesc)) ?></textarea></td></tr>
		<tr><td style="vertical-align: top; width: 10em">Show Prelim?</td><td><label><input type="checkbox" name="prelim" <? if($prelim) print("checked");?>> Show Preliminary Info check?</label></td></tr>
		<tr><td style="vertical-align: top; width: 10em">Show Confirm?</td><td><label><input type="checkbox" name="confirm" <? if($confirm) print("checked");?>> Show Confirm?</label></td></tr>
		<tr><td style="vertical-align: top; width: 10em">Show Receipt?</td><td><label><input type="checkbox" name="receipt" <? if($receipt) print("checked");?>> Show Receipt?</label></td></tr>
		</td></tr>
		</table>
		<p style="margin: 0"><input type="hidden" name="action" value="create"><input type="submit" value="Create"></p>
		</form>
		</td></tr></table>
		<? include '../../inc-footer.php'; ?>
	</body>
</html>
