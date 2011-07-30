<? 
include '../db.php';

$id = $_GET['id'];

$item_r = mysql_query("SELECT SELL_IMAGE, SELL_IMAGETYPE FROM SELL_LIST WHERE SELL_ID='$id'");

if($item = mysql_fetch_array( $item_r, MYSQL_ASSOC ) or die(mysql_error()) )
{
	if($item['SELL_IMAGE'] != NULL)
	{
		header('Content-type: image/' . $item['SELL_IMAGETYPE'] . "\n");
		
		print base64_decode($item['SELL_IMAGE']);
	}
	else
		print 'No image.';		
}
else
	print 'Image not found';

?>