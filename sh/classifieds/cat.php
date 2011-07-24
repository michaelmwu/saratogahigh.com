<? include '../db.php';

if($loggedin && is_numeric($userid))
{
$rsyouritems = mysql_query("SELECT SELL_LIST.*, MAX(SELLBUY_PRICE) AS MAX FROM SELL_LIST INNER JOIN SELLCAT_LIST ON SELLCAT_ID=SELL_CAT LEFT JOIN SELLBUY_LIST ON SELLBUY_ITEM=SELL_ID WHERE SELL_OWNER = " . $userid . " AND SELL_CAT = " . $_GET['id'] . " AND SELL_ET >= '" . date(TIME_FORMAT_SQL, CURRENT_TIME) . "' GROUP BY SELL_ID ORDER BY SELL_ST");

$rsallitems = mysql_query("SELECT SELL_LIST.*, MAX(SELLBUY_PRICE) AS MAX, USER_FULLNAME FROM SELL_LIST INNER JOIN SELLCAT_LIST ON SELLCAT_ID=SELL_CAT LEFT JOIN SELLBUY_LIST ON SELLBUY_ITEM=SELL_ID INNER JOIN USER_LIST ON USER_ID=SELL_OWNER WHERE SELL_OWNER != " . $userid . " AND SELL_CAT = " . $_GET['id'] . " AND SELL_ET >= '" . date(TIME_FORMAT_SQL, CURRENT_TIME) . "' GROUP BY SELL_ID ORDER BY SELL_ST") or die(mysql_error());
}
else
	$rsallitems = mysql_query("SELECT SELL_LIST.*, MAX(SELLBUY_PRICE) AS MAX FROM SELL_LIST INNER JOIN SELLCAT_LIST ON SELLCAT_ID=SELL_CAT LEFT JOIN SELLBUY_LIST ON SELLBUY_ITEM=SELL_ID WHERE SELL_CAT = " . $_GET['id'] . " AND SELL_ET >= '" . date(TIME_FORMAT_SQL, CURRENT_TIME) . "' GROUP BY SELL_ID ORDER BY SELL_ST");

$rsinfo = mysql_query("SELECT SELLCAT_NAME, SELLCAT_DESC FROM SELLCAT_LIST WHERE SELLCAT_ID = " . $_GET['id']);
$info = mysql_fetch_array($rsinfo, MYSQL_ASSOC);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>

<head>
	<title>SaratogaHigh.com Classifieds</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<meta name="vs_targetSchema" content="http://schemas.microsoft.com/intellisense/ie5">
	<link rel="stylesheet" type="text/css" href="../shs.css">
</head>
<body>
<? include "inc-header.php";?>

<h1>SaratogaHigh.com Classifieds - <?= $info['SELLCAT_NAME'] ?></h1>
<h3><?= $info['SELLCAT_DESC'] ?></h3>

<? if($loggedin && is_numeric($_GET['id']))
{

if(mysql_num_rows($rsyouritems) > 0)
{
?>
<h3>Your Sales</h3>
<table border="0">
<tr>
<td class="header" width="90">Current Price</td>
<td class="header" width="200">Item Name</td>
<td class="header" width="275">Description</td>
<td class="header" width="85">Ends on</td></tr>

<?
while($youritems = mysql_fetch_array($rsyouritems, MYSQL_ASSOC))
{
	$starttime = strtotime($youritems['SELL_ET']);
print '<tr><td class="data" valign="top">$';
if(is_null($youritems['MAX']))
	print $youritems['SELL_PRICE'];
else
	print $youritems['MAX'];
	print '</td><td class="data" valign="top"><a href="item.php?id=' . $youritems['SELL_ID'] . '">' . $youritems['SELL_NAME'] . '</a></td>
	<td class="data" valign="top">' . $youritems['SELL_DIGEST'] . '</td><td class="data" valign="top">' . date('n/d/y', $starttime) . '</td></tr>';
}
}
?>
</table>
<?
	if(mysql_num_rows($rsyouritems) > 0)
	 print '<h3>All Other Sales';
	else
	 print '<h3>All Sales';
}
else
	if(mysql_num_rows($rsallitems) > 0)
	 print '<h3>All Sales';

if(mysql_num_rows($rsallitems) > 0)
{
?>

</h3>
<table border="0">
<tr>
<td class="header" width="90">Current Price</td>
<td class="header" width="200">Item Name</td>
<td class="header" width="150">Seller</td>
<td class="header" width="275">Description</td>
<td class="header" width="85">Ends on</td></tr>
<?

while($allitems = mysql_fetch_array($rsallitems, MYSQL_ASSOC))
{
	$itemid = $allitems['SELL_ID'];
	$timestart = strtotime($allitems['SELL_ET']);
	print '<tr><td class="data" valign="top">$';	
if(is_null($allitems['MAX']))
	print $allitems['SELL_PRICE'];
else
	print $allitems['MAX'];
	print '</td><td class="data" valign="top"><a href="item.php?id=' . $allitems['SELL_ID'] . '">' . $allitems['SELL_NAME'] . '</a></td>
	<td class="data" valign="top"><a href="../directory/?id=' . $allitems['SELL_OWNER'] . '">' . $allitems['USER_FULLNAME'] . '</a></td><td class="data" valign="top">' . $allitems['SELL_DIGEST'] . '</td><td class="data" valign="top">' . date('n/d/y', $timestart) . '</td></tr>';
}
print '</table>';
}
include "../inc-footer.php"; ?>
</body>
</html>

