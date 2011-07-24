<? include '../db.php';
	include '../calendar/cal.php';

$rscatinfo = mysql_query("SELECT * FROM SELLCAT_LIST WHERE 1");

if($loggedin && $_POST['newitem'] == 'Submit' && $_POST['itemname'] != '')
{
	$ts = getdate(CURRENT_TIME);
	$last_day = mktime($ts['hours'], $ts['minutes'], $ts['seconds'], $ts['mon'], $ts['mday'] + $_POST['itemtime'], $ts['year']);

	$filecontents = "NULL";
	$filetype = "";

	$filename = $_FILES['itempic']['name'];
	
	$tempfile = $_FILES['itempic']['tmp_name'];

	if($filename)
	{
		if(filesize($tempfile) <= SELL_IMAGE_SIZE && filesize($tempfile) > 0)
		{
			if(preg_match("/\.(jpe|jpeg|jpg|gif|png|bmp)$/",$filename,$matches))
			{
				$extension = $matches[1];
				switch($extension)
				{
					case 'jpe':
					case 'jpeg':
					case 'jpg':
						$filetype = 'jpeg';
						break;
					default:
						$filetype = $extension;
						break;
				}
				
				$filehandle = fopen($tempfile, "r");
				
				$filecontents = "'" . base64_encode( fread($filehandle, filesize( $tempfile ) ) ) . "'";
			}
		}
	}
	
	mysql_query("INSERT INTO SELL_LIST (SELL_CAT, SELL_NAME, SELL_DESC, SELL_DIGEST, SELL_IMAGE, SELL_IMAGETYPE, SELL_PRICE, SELL_OWNER, SELL_ST, SELL_ET) VALUES ('" . $_POST['category'] . "', '" . $_POST['itemname'] . "', '" . $_POST['itemdesc'] . "', '" . htmlspecialchars(shorten_string(nl2slash($_POST['itemdesc']), 40)) . "', $filecontents, '$filetype', '" . $_POST['itemprice'] . "', '" . $userid . "', '" . date(TIME_FORMAT_SQL, CURRENT_TIME) . "', '" . date(TIME_FORMAT_SQL, $last_day) . "')") or die(mysql_error());
	mysql_query("INSERT INTO SELLBUY_LIST (SELLBUY_ITEM, SELLBUY_BUYER, SELLBUY_PRICE, SELLBUY_TS) VALUES('" . mysql_insert_id() . "', '" . $userid . "', '" . $_POST['itemprice'] . "', '" . date(TIME_FORMAT_SQL, CURRENT_TIME) . "')");
	header('Location: http://' . DNAME . '/classifieds/');
}

elseif($loggedin && $_POST['newitem'] == 'Submit' && $_POST['itemname'] == '')
	die('Please enter a name for your item. Go <a href="javascript:history.back(1)">back</a> to add an item name.');

$brandnew = 1;

if($loggedin && is_numeric($_GET['id']))
{
	$rsiteminfo = mysql_query("SELECT SELL_ID, SELL_CAT, SELL_NAME, SELL_PRICE, SELL_DESC, SELL_IMAGE, UNIX_TIMESTAMP(SELL_ET) - UNIX_TIMESTAMP(SELL_ST) AS SELL_TIMEDIFF FROM SELL_LIST WHERE SELL_ID = " . $_GET['id'] . " AND SELL_OWNER = '" . $userid . "'");
	$iteminfo = mysql_fetch_array($rsiteminfo, MYSQL_ASSOC);
	
	if($iteminfo)
		$brandnew = 0;
	else
		$brandnew = 1;
}

if($loggedin && $_POST['changeitem'] == 'Save Changes')
{
	if(!$_POST['keep'])
	{
		$filecontents = "NULL";
		$filetype = "";
	
		$filename = $_FILES['itempic']['name'];
		
		$tempfile = $_FILES['itempic']['tmp_name'];
	
		if($filename)
		{
			if(filesize($tempfile) <= SELL_IMAGE_SIZE && filesize($tempfile) > 0)
			{
				if(preg_match("/\.(jpe|jpeg|jpg|gif|png|bmp)$/",$filename,$matches))
				{
					$extension = $matches[1];
					switch($extension)
					{
						case 'jpe':
						case 'jpeg':
						case 'jpg':
							$filetype = 'jpeg';
							break;
						default:
							$filetype = $extension;
							break;
					}
					
					$filehandle = fopen($tempfile, "r");
				
					$filecontents = "'" . base64_encode( fread($filehandle, filesize( $tempfile ) ) ) . "'";
				}
			}
		}
	}

	mysql_query("UPDATE SELLBUY_LIST SET SELLBUY_PRICE='" . $_POST['itemprice'] . "' WHERE SELLBUY_ITEM = '" . $_POST['id'] . "' AND SELLBUY_BUYER = '$userid'");
	mysql_query("UPDATE SELL_LIST SET SELL_CAT='" . $_POST['category'] . "', SELL_NAME='" . $_POST['itemname'] . "', SELL_PRICE='" . $_POST['itemprice'] . "', SELL_DESC='" . $_POST['itemdesc'] . "'" . ($_POST['keep']?'':", SELL_IMAGE = $filecontents, SELL_IMAGETYPE = '$filetype'") . " WHERE SELL_ID = '" . $_POST['id'] . "' AND SELL_OWNER = '$userid'");
	header('Location: item.php?id=' . $_POST['id']);
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

if($loggedin) {

if($userR['USER_CLASSIFIEDS_BANNED'] > date(TIME_FORMAT_SQL, CURRENT_TIME) && !$isadmin)
{
	print '<p>Sorry, you are banned from using classifieds until ' . date(TIME_FORMAT, strtotime($userR['USER_CLASSIFIEDS_BANNED'])) . '. Contact an admin if you think you have been wrongfully banned.</p>';
}
else
{

if($brandnew)
	print '<h1>Put new item up for sale</h1>';
else
	print '<h1>Edit item information</h1>';
	
if($err) print $err;
?>

<form action="add-item.php" method="post" enctype="multipart/form-data">
<input type="hidden" name="id" value="<?= $_GET['id'] ?>">
<table border="0" cellspacing="2">
<? 
$catprint = 1;

while($catinfo=mysql_fetch_array($rscatinfo, MYSQL_ASSOC))
{

print '<tr><td>';
if($catprint)
{	print '<b>Category:</b>';
	$catprint=0; }
print '</td><td><input type="radio" name="category" id="' . $catinfo['SELLCAT_ID'] . '" value="' . $catinfo['SELLCAT_ID'] . '"';
if($catinfo['SELLCAT_ID']==$iteminfo['SELL_CAT'])
	print ' checked';
print '></input><label for="' . $catinfo['SELLCAT_ID'] . '">' . $catinfo['SELLCAT_NAME'] . '</label></td></tr>';

}
?>

<tr><td valign="top"><b>Item Name</b></td>
 <td><input type="text" name="itemname" <? if($iteminfo['SELL_NAME']){print ' value="' . $iteminfo['SELL_NAME'] . '"';} ?>></td></tr>
<tr><td valign="top"><b>Price</b></td>
 <td>$<input type="text" size="6" name="itemprice" <? if($iteminfo['SELL_PRICE']){print ' value="' . $iteminfo['SELL_PRICE'] . '"';} ?>></td></tr>
<tr><td valign="top"><b>Time Allotted</b></td>
<td>

<?

if(!$iteminfo)
{
	$item = new select;
	$item->name = "itemtime";
	for($i=2;$i<7;$i++)
		$item->add_option($i,$i . ' days',$_POST['itemtime'] == $i);
	$item->add_option(7,'one week',$_POST['itemtime'] == 7);
	$item->add_option(14,'two weeks',$_POST['itemtime'] == 14);
	$item->add_option(31,'one month',$_POST['itemtime'] == 31);
	$item->element_print();
}
else
	print timedescriptor($iteminfo['SELL_TIMEDIFF']);
?>

</td></tr>
<tr><td valign="top"><b>Item Description</b></td>
 <td><textarea name="itemdesc" rows="4" cols="30"><? if($iteminfo['SELL_DESC']){print $iteminfo['SELL_DESC'];} ?></textarea></td></tr>
<tr><td valign="top"><b>Item Picture</b> (Optional)</td>
<td><input type="hidden" name="MAX_FILE_SIZE" value="<?=SELL_IMAGE_SIZE?>"> <input id="itempic" onChange="checkFile();" onKeyPress="checkFile();" type="file" size="10" name="itempic"> .jpg, .gif, .png, .bmp only. 50.0 KiB maximum, please.
<? if(strlen($iteminfo['SELL_IMAGE']) > 0) print '<br><label>Keep Current Picture? <input id="keep" type="checkbox" name="keep" checked></label><br><img src="image.php?id=' . $iteminfo['SELL_ID'] . '"></td></tr>'; ?>
<script type="text/javascript">
<!--
	function checkFile()
	{
		if(keep = document.getElementById("keep"))
		{
			if(document.getElementById("itempic") != '')
				keep.checked = false;
		}
	}
-->
</script>
<tr><td></td><td>

<?
if($brandnew)
	print '<input type="submit" name="newitem" value="Submit">';
else
	print '<input type="submit" name="changeitem" value="Save Changes">';
?>

</td></tr>

</table>

<? } ?>

</form>

<?
}

else
	print '<p>Please <a href="../login.php?next=' . urlencode($REQUEST_URI) . '">log in</a> to put a new item up for sale.</p>';

include "../inc-footer.php"; ?>
</body>
</html>

