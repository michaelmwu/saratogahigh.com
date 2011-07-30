<? include '../db.php';
if($loggedin && is_numeric($_GET['id']))
{
$rsitem = mysql_query("SELECT SELL_LIST.*, USER_FULLNAME, SELLCAT_NAME, SELLBUY_PRICE FROM SELL_LIST INNER JOIN USER_LIST ON SELL_OWNER = USER_ID INNER JOIN SELLCAT_LIST ON SELLCAT_ID = SELL_CAT LEFT JOIN SELLBUY_LIST ON SELLBUY_ITEM = SELL_ID WHERE SELL_ID = " . $_GET['id']);
$item = mysql_fetch_array($rsitem, MYSQL_ASSOC);

$rsstatus = mysql_query("SELECT *, !(SELL_ID Is Null) AS ISOWNER FROM SELL_LIST WHERE SELL_ID = " . $_GET['id'] . " AND SELL_OWNER = " . $userid);
$status = mysql_fetch_array($rsstatus, MYSQL_ASSOC);

$rsbids = mysql_query("SELECT SELLBUY_LIST.*, USER_FULLNAME FROM SELLBUY_LIST INNER JOIN USER_LIST ON SELLBUY_BUYER = USER_ID WHERE SELLBUY_ITEM = " . $_GET['id'] . " AND SELLBUY_BUYER != " . $item['SELL_OWNER'] . " ORDER BY SELLBUY_ID DESC");
if(mysql_num_rows($rsbids) > 0)
	$therearebids = 1;
else
	$therearebids = 0;

	if($therearebids)
	{
	$rsbidinfo = mysql_query("SELECT MAX(SELLBUY_PRICE) AS MAX FROM SELLBUY_LIST WHERE SELLBUY_ITEM = " . $_GET['id']);
	$bidinfo = mysql_fetch_array($rsbidinfo, MYSQL_ASSOC);
	}
}

if($loggedin && is_numeric($_GET['id']) && $_GET['delete']=='yes')
{
	mysql_query("DELETE FROM SELL_LIST WHERE SELL_ID = " . $_GET['id']);
	mysql_query("DELETE FROM SELLBUY_LIST WHERE SELLBUY_ITEM = " . $_GET['id']);
	header('Location: http://' . DNAME . '/classifieds/');
}

if($loggedin && is_numeric($_GET['id']) && is_numeric($_GET['deletebid']))
{
	mysql_query("DELETE FROM SELLBUY_LIST WHERE SELLBUY_ID = " . $_GET['deletebid']);
	header('Location: item.php?id=' . $_GET['id']);
}

if($isadmin && $_POST['action'] = 'ban')
{
	if(is_numeric($_POST['banamount']))
	{
		$bandatetime = date(TIME_FORMAT_SQL,CURRENT_TIME + $_POST['banamount'] * $_POST['bantype']);
		mysql_query("UPDATE USER_LIST SET USER_CLASSIFIEDS_BANNED = '$bandatetime' WHERE USER_ID='" . $item['SELL_OWNER'] . "'");
	}
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>

<head>
	<title>SaratogaHigh.com Classifieds</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<meta name="vs_targetSchema" content="http://schemas.microsoft.com/intellisense/ie5">
	<link rel="stylesheet" type="text/css" href="../shs.css">
<style type="text/css">
<!--
td {
	vertical-align:top
}
-->
</style>
</head>
<body>
<? include "inc-header.php";
if($loggedin)
{

	if($isvalidated)
	{
?>

<h1><?= $item['SELL_NAME'] ?></h1>

<?

if($status['ISOWNER'] || $isadmin)
{
	print '<form action="item.php?id=' . $_GET['id'] . '&delete=yes" method="POST"><p><span class="toolbar"><a href="add-item.php?id=' . $_GET['id'] . '">Change information</a></span>
		<span class="toolbar"><a href="item.php?id=' . $_GET['id'] . '&delete=yes">Delete item</a>';
	if($isadmin && !$status['ISOWNER'])
		print ' and ban for <input type="text" name="banamount" value="0" style="width: 50px;"> <select name="bantype"><option value="3600">Hours <option value="86400">Days <option value="604800">Weeks <option value="2592000">Months <option value="31536000">Years</select> <input type="hidden" name="action" value="ban"> <input type="submit" value="Go"></span></p></form><p><span class="toolbar"><a href="buy.php?id=' . $_GET['id'] . '">Bid on item</a>';
	print '</span></p>';
}
else
	print '<p><span class="toolbar"><a href="buy.php?id=' . $_GET['id'] . '">Bid on item</a></span></p>';

$starttime = strtotime($item['SELL_ST']);
$endtime = strtotime($item['SELL_ET']);
?>

<table border="0" cellspacing="2">
<tr><td align="right" style="font-size:12pt" width="125"><b>Description:</b></td><td style="font-size:12pt"><?= $item['SELL_DESC'] ?></td></tr>
<tr><td align="right" style="font-size:12pt"><b>Current Price:</b></td><td>$
<?
if($therearebids)
	print $bidinfo['MAX'];
else
	print $item['SELL_PRICE'];
?>
</td></tr>
<tr><td></td><td></td></tr>
<tr><td></td><td></td></tr>
<tr><td align="right">Starting Time:</td><td><?= date('n/d/y', $starttime) ?></td></tr>
<tr><td align="right">Ending Time:</td><td><?= date('n/d/y', $endtime) ?></td></tr>
<tr><td align="right">Seller:</td><td><a href="../directory/?id=<?= $item['SELL_OWNER'] ?>"><?= $item['USER_FULLNAME'] ?></a></td></tr>
<?
if ($item['SELL_IMAGE'])
{
	print '<tr><td align="right">Picture:</td><td><img src="image.php?id=' . $item['SELL_ID'] . '"></td></tr>';
}
?>
</table>

<br>
<h3>Bid History</h3>

<?
if(mysql_num_rows($rsbids) > 0)
{
?>
<table border="0" cellspacing="2">
<tr><td class="header" width="150">Bidder</td><td class="header" width="200">Time/Date of Bid</td><? if($status['ISOWNER']) { print '<td class="header" width="100">Price Bid</td>'; } ?><td>&nbsp;</td></tr>

	
while($bids = mysql_fetch_array($rsbids, MYSQL_ASSOC))
{
	$timestamp = strtotime($bids['SELLBUY_TS']);
	print '<tr><td class="data">' . $bids['USER_FULLNAME'] . '</td><td class="data">' . date('n/d/y', $timestamp) . '</td>';
	if($status['ISOWNER'])
		print '<td>\$' . $bids['SELLBUY_PRICE'] . '</td><td><a href="item.php?id=' . $item['SELL_ID'] . '&deletebid=' . $bids['SELLBUY_ID'] . '">Delete</a></td>';
	else
		print '<td>&nbsp;</td><td>&nbsp;</td>';
	print '</tr>';
}
print '</table>';
} // end of checking for any bids

else
{
	print 'No bids yet.';
	if(!$status['ISOWNER'])
		print ' <a href="buy.php?id=' . $item['SELL_ID'] . '">Bid</a> on this item!';
}


	} else
		print '<p>Sorry, only verified users can buy and sell items through SaratogaHigh.com Classifieds. <a href="/help/validation.php">Click here</a> to learn more about validation.</p>';
} else
	print '<p>Please <a href="../login.php?next=' . urlencode($REQUEST_URI) . '">log in</a> to view SaratogaHigh.com Classifieds.</p>';

include "../inc-footer.php"; ?>
</body>
</html>
