<? include '../db.php';

if($loggedin && is_numeric($_GET['id']))
{
	$rsiteminfo = mysql_query("SELECT SELL_LIST.*, USER_LIST.*, MAX(SELLBUY_PRICE) AS MAX FROM SELL_LIST INNER JOIN USER_LIST ON SELL_OWNER=USER_ID LEFT JOIN SELLBUY_LIST ON SELLBUY_ITEM = SELL_ID WHERE SELL_ID = " . $_GET['id'] . " GROUP BY SELLBUY_ITEM");
	$iteminfo = mysql_fetch_array($rsiteminfo, MYSQL_ASSOC);

}

if($loggedin && $_GET['hiddensubmit']=='submitted')
{
  if(is_numeric($_GET['bid']))
  {
	if($_GET['bid'] > $iteminfo['MAX'])
	{
		mysql_query("INSERT INTO SELLBUY_LIST (SELLBUY_ITEM, SELLBUY_BUYER, SELLBUY_PRICE, SELLBUY_TS) VALUES ('" . $_GET['id'] . "', '" . $userid . "', '" . $_GET['bid'] . "', '" . date(TIME_FORMAT_SQL, CURRENT_TIME) . "')") or die('died');
		
		$config = array('to' => $iteminfo['USER_EMAIL'],
						'from' => 'staff',
						'subject' => 'Someone bid on ' . $iteminfo['SELL_NAME'],
						'message' => 'Dear ' . $iteminfo['USER_FN'] . ",\n\n"
						 			. '    Someone bid on your ' . $iteminfo['SELL_NAME'] . '. Follow this link to check it out http://' . DNAME . '/classifieds/item.php?id=' . $_GET['id'] . ".\n\n"
									. "Sincerely,\n"
									. "SaratogaHigh.com Classifieds.");
									
		if(!email($config))
			print 'Email failed.';
		header('Location: item.php?id=' . $_GET['id']);
	}
	else
		$badbid = '<br>Sorry, your bid must be greater than the current price, which is ' . $iteminfo['MAX'];
  }
  else
   $badbid = '<br>Please make a numerical bid.';
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
<? include "inc-header.php";

if($loggedin)
{
	if($isvalidated)
	{
	
		if($userR['USER_CLASSIFIEDS_BANNED'] > date(TIME_FORMAT_SQL, CURRENT_TIME) && !$isadmin)
		{
			print '<p>Sorry, you are banned from using classifieds until ' . date(TIME_FORMAT, strtotime($userR['USER_CLASSIFIEDS_BANNED'])) . '. Contact an admin if you think you have been wrongfully banned.</p>';
		}
		else
		{

		if($badbid)
			print $badbid;
		else {
?>



<h1><?= $iteminfo['SELL_NAME'] ?></h1>
<span style="font-size:12pt"><b>Current Price:</b> $

<?
if(is_null($iteminfo['MAX']))
	print $iteminfo['SELL_PRICE'];
else
	print $iteminfo['MAX'];
?>

</span>
<br><br>
<form action="buy.php" method="get">
<input type="hidden" name="id" value="<?= $_GET['id'] ?>">
<input type="hidden" name="hiddensubmit" value="submitted">
<span style="font-size:12pt">Bid: </span>$<input type="text" name="bid"><input type="submit" name="submitbid" value="Submit Bid">
</form>



<?
		} //end of badbid(which checks if bid is large enough)
		}

	} else
		print '<p>Sorry, only verified users can buy and sell items through SaratogaHigh.com Classifieds. <a href="/help/validation.php">Click here</a> to learn more about validation.</p>';
} else
	print '<p>Please <a href="../login.php?next=' . urlencode($REQUEST_URI) . '">log in</a> to put a new item up for sale.</p>';

include "../inc-footer.php"; ?>
</body>
</html>



