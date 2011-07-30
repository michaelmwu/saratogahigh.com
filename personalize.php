<? include 'db.php';
if($loggedin)
	$boxes = unserialize( $userR['USER_FRONTPAGE'] );
else
	forceLogin();

$boxes = cleanup_boxes($boxes);

if($_POST['action'] == 'save')
{
	$icount = count($boxes);
	for($i = 0; $i < $icount; $i++)
	{
		$jcount = count($boxes[$i]);
		for($j = 0; $j < $jcount; $j++ )
		{
			if(!isset($_POST['box' . $boxes[$i][$j]]))
			{
				array_splice($boxes[$i],$j,1);
				$j--;
				$jcount--;
			}
		}
	}
	
	$postboxes = preg_grep( "/^box/", array_keys($_POST) );
	
	foreach( $postboxes as $box )
	{
		$number = (int) substr($box,3);
		if(!box_exists($boxes,$number))
			array_push($boxes[choose_row($boxes)],$number);
	}
	
	$boxes = cleanup_boxes($boxes);
	
	mysql_query("UPDATE USER_LIST SET USER_FRONTPAGE='" . serialize($boxes) . "' WHERE USER_ID='$userid'");
	
	header('Location: index-blocktest.php');
}

function choose_row($boxes)
{
	$row = 0;
	$min_count = "";
	for($i = 0; $i < count($boxes); $i++)
	{
		$cur_count = count($boxes[$i]);
		if($min_count == "")
			$min_count = $cur_count;
		if($cur_count < $min_count)
		{
			$row = $i;
			$min_count = $cur_count;
		}
	}
	
	return $row;
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
	<title>SaratogaHigh.com</title>
	<link rel="stylesheet" type="text/css" href="shs.css">
	<style type="text/css"><!--
		div.pagecolumn { float: left; margin: 0px 5px 5px 0px }
		.block_check { font-weight: bold; margin: 5px; }
		-->
	</style>
</head>
<body>
<? include 'inc-header.php'; ?>
<h1>Personalize</h1>
<p>Here you can personalize what you see on your front page.</p>
<form action="personalize.php" method="POST">
<?
$filter = "1";

if($isalum || $isteacher)
	$filter .= " AND FRONTBOX_ID != 3";
if(!$isvalidated)
	$filter .= " AND FRONTBOX_ID != 5";
if(!$isadmin)
	$filter .= " AND FRONTBOX_ID != 13 AND FRONTBOX_ID != 28";
if( !($isstudent || $isteacher) )
	$filter .= " AND FRONTBOX_ID != 29";
	
$totalR = mysql_query("SELECT COUNT( * ) AS TOTAL FROM FRONTBOX_LIST WHERE $filter");

$blocksR = mysql_query("SELECT * FROM FRONTBOX_LIST WHERE $filter");

if($total = mysql_fetch_array($totalR))
{
	$rows = array();
	$left = $total['TOTAL'];
	$rows[0] = ceil($left / 3);
	$left -= $rows[0];
	
	$rows[1] = ceil($left / 2);
	$rows[2] = $left - $rows[1];
}

for($i = 0; $i < 3; $i++)
{
	print '<div class="pagecolumn" style="width: 280px">';
	for($j = 0; $j < $rows[$i]; $j++)
	{
		if($block = mysql_fetch_array($blocksR, MYSQL_ASSOC))
			print '<label class="block_check"><input type="checkbox" name="box' . $block['FRONTBOX_ID'] . '"' . (box_exists($boxes,$block['FRONTBOX_ID'])?' checked':'') . '> ' . $block['FRONTBOX_TITLE'] . "</label><br>\n";
	}
	print '</div>';
}
?>
<hr style="clear: left;">
<input type="hidden" name="action" value="save"><input type="submit" value="Save">
</form>