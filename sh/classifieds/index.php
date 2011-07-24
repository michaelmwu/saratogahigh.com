<? include '../db.php';

if($loggedin && is_numeric($userid))
{
$rsyouritems = mysql_query("SELECT SELL_LIST.*, SELLCAT_LIST.*, MAX(SELLBUY_PRICE) AS MAX FROM SELL_LIST INNER JOIN SELLCAT_LIST ON SELLCAT_ID=SELL_CAT LEFT JOIN SELLBUY_LIST ON SELLBUY_ITEM=SELL_ID WHERE SELL_OWNER = " . $userid . " AND SELL_ET >= '" . date(TIME_FORMAT_SQL, CURRENT_TIME) . "' GROUP BY SELL_ID ORDER BY SELL_CAT, SELL_ST");
$rsallitems = mysql_query("SELECT SELL_LIST.*, SELLCAT_LIST.*, MAX(SELLBUY_PRICE) AS MAX, USER_FULLNAME FROM SELL_LIST INNER JOIN SELLCAT_LIST ON SELLCAT_ID=SELL_CAT INNER JOIN USER_LIST ON USER_ID=SELL_OWNER LEFT JOIN SELLBUY_LIST ON SELLBUY_ITEM=SELL_ID WHERE SELL_OWNER != " . $userid . " AND SELL_ET >= '" . date(TIME_FORMAT_SQL, CURRENT_TIME) . "' GROUP BY SELL_ID ORDER BY SELL_CAT, SELL_ST");
}

else
$rsallitems = mysql_query("SELECT SELL_LIST.*, SELLCAT_LIST.*, MAX(SELLBUY_PRICE) AS MAX FROM SELL_LIST INNER JOIN SELLCAT_LIST ON SELLCAT_ID = SELL_CAT LEFT JOIN SELLBUY_LIST ON SELLBUY_ITEM = SELL_ID WHERE SELL_ET >= '" . date(TIME_FORMAT_SQL, CURRENT_TIME) . "' GROUP BY SELL_ID ORDER BY SELL_CAT, SELL_ST");



if($loggedin && is_numeric($_GET['id']) && $_GET['delete']=='yes')
{
	mysql_query("DELETE FROM SELL_LIST WHERE SELL_ID = " . $_GET['id']);
	mysql_query("DELETE FROM SELLBUY_LIST WHERE SELLBUY_ITEM = " . $_GET['id']);
	header('Location: http://' . DNAME . '/classifieds/');
}


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

<h1>SaratogaHigh.com Classifieds</h1>
Welcome to SaratogaHigh.com Classifieds - where you can buy (and sell) to your heart's desire.
 Once an item is put up for sale, buyers have a set amount of days to bid until the auction closes.
 <br>
 <br>
 <b>Once it closes, the two of you can set up a time and place to exchange money and merchandise.</b>
<br><br>
<b>Rules:</b>
<br>
1. Bidders may not pull out of an auction. Only sellers can delete an auction.
<br>
2. Items will be deleted if put up for sale on SaratogaHigh.com Classifieds if they are in direct violation of any school policy or rule.
<br>
3. If, after the set amount of time days, a seller has not contacted the highest bidder (or there are no bids), the item will no longer be displayed in Classifieds.
<br>
4. Sellers may end the auction at any time.

<? if($loggedin && is_numeric($userid))
{

if(mysql_num_rows($rsyouritems) > 0)
{ ?>
<h3>Your Sales</h3>
<table border="0">
<tr><td class="header" width="100">Category</td>
<td class="header" width="95">Current Price</td>
<td class="header" width="195">Item Name</td>
<td class="header" width="275">Description</td>
<td class="header" width="85">Ends on</td></tr>

<?
while($youritems = mysql_fetch_array($rsyouritems, MYSQL_ASSOC))
{
	$starttime = strtotime($youritems['SELL_ET']);
	print '<tr><td class="data" valign="top"><a href="cat.php?id=' . $youritems['SELL_CAT'] . '">' . $youritems['SELLCAT_NAME'] . '</a></td>
		<td class="data" valign="top">$';
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
	print'<h3>All Sales';
 }
else
{ print '<h3>All Sales'; }?>

</h3>
<table border="0">
<tr><td class="header" width="100">Category</td>
<td class="header" width="95">Current Price</td>
<td class="header" width="195">Item Name</td>
<td class="header" width="150">Seller</td>
<td class="header" width="275">Description</td>
<td class="header" width="85">Ends on</td></tr>
<?

while($allitems = mysql_fetch_array($rsallitems, MYSQL_ASSOC))
{
	$timestart = strtotime($allitems['SELL_ET']);
	print '<tr><td class="data" valign="top"><a href="cat.php?id=' . $allitems['SELL_CAT'] . '">' . $allitems['SELLCAT_NAME'] . '</a></td>
	 <td class="data" valign="top">$';
if(is_null($allitems['MAX']))
	print $allitems['SELL_PRICE'];
else
	print $allitems['MAX'];
	print '</td><td class="data" valign="top"><a href="item.php?id=' . $allitems['SELL_ID'] . '">' . $allitems['SELL_NAME'] . '</a></td>
	 <td class="data" valign="top"><a href="../directory/?id=' . $allitems['SELL_OWNER'] . '">' . $allitems['USER_FULLNAME'] . '</a></td><td class="data" valign="top">' . $allitems['SELL_DIGEST'] . '</td><td class="data" valign="top">' . date('n/d/y', $timestart) . '</td></tr>';
}
print '</table>';

include "../inc-footer.php"; ?>
</body>
</html>

