<? define("URL","http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']); ?>
<html>
<head>
<style type="text/css">
	body { font-family: Sans-Serif; font-size: 12px; }
</style>
<title>Pay Donation Online</title>
</head>
<body>
<?
if(isset($_GET['pay']))
{
?>
<h2>Pay Online</h2>
<p>You are donating <b>$<?= sprintf("%01.2f",is_numeric($_POST['DonationCost']) ? $_POST['DonationCost'] : 0) ?></b>.</p>
<p>If this is incorrect, go <a href="javascript:" onClick="history.back();">back</a> and correct the form. Otherwise, click the below button to pay.</p>
<form name="paypalform" action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_xclick">
<input type="hidden" name="upload" value="1">
<input type="hidden" name="business" value="shsptsaprimary@yahoo.com">
<input type="hidden" name="item_name" value="PTSA Donation">
<input type="hidden" name="amount" value="<?= $_POST['DonationCost'] ?>">
<input type="hidden" name="no_shipping" value="1">
<input type="hidden" name="return" value="<?= URL ?>?paid">
<input type="hidden" name="cancel_return" value="<?= URL ?>?cancel">
<input type="hidden" name="currency_code" value="USD">
<input type="hidden" name="bn" value="PP-BuyNowBF">
<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but6.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!">
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>
<? }
else if(isset($_GET['paid']))
{ ?>
<h2>Thanks!</h2>
<p>Thank you for donating to the PTSA! You can close this window now.</p>
<? } 
else if(isset($_GET['cancel']))
{ ?>
<h2 style="color: #DD0000;">Error!</h3>
<p>You must pay online with PayPal if you intend to submit this form by email. Go <a href="javascript:" onClick="history.go(-2);">back</a> to PayPal.</p>
<? } ?>
</body>
</html>