<? define("URL","http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']); ?>
<html>
<head>
<style type="text/css">
	body { font-family: Sans-Serif; font-size: 12px; }
</style>
<title>Pay Membership Online</title>
</head>
<body>
<?
if(isset($_GET['pay']))
{
?>
<h2>Pay Online</h2>
<p>You put your name (last, first), as <b><?= $_POST['ParentLastName'] ?></b>, <b><?= $_POST['ParentFirstName'] ?></b>.</p>
<p>You put your student's name and grade as <b><?= $_POST['StudentLastName'] ?></b>, <b><?= $_POST['StudentFirstName1'] ?></b> <b><?= $_POST['StudentGrade1'] ?></b><? if(strlen($_POST['StudentFirstName2']) > 0) { ?> and <b><?= $_POST['StudentLastName'] ?></b>, <b><?= $_POST['StudentFirstName2'] ?></b> <b><?= $_POST['StudentGrade2'] ?></b><? } ?></p>
<p><? if(strlen($_POST['Street']) > 0 && strlen($_POST['City']) > 0 && strlen($_POST['Zip']) > 0) { ?>You put your address as <b><?= $_POST['Street'] ?></b>, <b><?= $_POST['City'] ?></b>, CA <b><?= $_POST['Zip'] ?></b>.<? } else { ?>You did not set your address.<? } ?></p>
<? if($_POST['MembershipRadio'] == 'Patron') { ?>
<p>You selected a <b>Patron Membership</b> for <b>$65.00</b>. You choose for the <i>Falcon</i> <?= ($_POST['MailedFalcon'] == 'yes') ? '<b>to</b>' : 'to <b>not</b>' ?> be mailed home.</p>
<? } else { ?>
<p>You selected a <b>Basic Membership</b> for <b>$45.00</b>.</p>
<? } ?>
<p>You are getting <b><?= is_numeric($_POST['ExtraDirectories']) ? $_POST['ExtraDirectories'] : 0 ?></b> extra directories for <b>$<?= $_POST['ExtraDirectoriesCost'] ?></b>.</p>
<p>You are donating <b>$<?= sprintf("%01.2f",is_numeric($_POST['DonationsCost']) ? $_POST['DonationsCost'] : 0) ?></b>.</p>
<p>Your payment total is <b style="font-size: 14px;">$<?= $_POST['TotalCost'] ?></b>
<p>If any of this is incorrect, go <a href="javascript:" onClick="history.back();">back</a> and correct the form. Otherwise, click the below button to pay.</p>
<form name="paypalform" action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_cart">
<input type="hidden" name="upload" value="1">
<input type="hidden" name="business" value="shsptsaprimary@yahoo.com">
<input type="hidden" name="item_name_1" value="<?= ($_POST['MembershipRadio'] == 'Basic') ? 'PTSA Basic Membership' : 'PTSA Patron Membership' ?>">
<input type="hidden" name="amount_1" value="<?= ($_POST['MembershipRadio'] == 'Basic') ? 45 : 65 ?>">
<? if($_POST['ExtraDirectories'] > 0) { ?>
<input type="hidden" name="item_name_2" value="Extra directories">
<input type="hidden" name="amount_2" value="10">
<input type="hidden" name="quantity_2" value="<?= $_POST['ExtraDirectories'] ?>">
<? }
if($_POST['DonationsCost'] > 0) { ?>
<input type="hidden" name="item_name_3" value="Donation">
<input type="hidden" name="amount_3" value="<?= $_POST['DonationsCost'] ?>">
<? } ?>
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
<p>Thank you for registering to become a PTSA member! You can close this window now.</p>
<? } 
else if(isset($_GET['cancel']))
{ ?>
<h2 style="color: #DD0000;">Error!</h3>
<p>You must pay online with PayPal if you intend to submit this form by email. Go <a href="javascript:" onClick="history.go(-2);">back</a> to PayPal.</p>
<? } ?>
</body>
</html>