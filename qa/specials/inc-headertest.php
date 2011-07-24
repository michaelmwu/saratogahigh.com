<div class="navbar">
<span style="font-weight: bold;">Navigate: </span>
<?
$qary = array();
if(!is_numeric($group))
	die('Sorry, that group does not exist.');

if($gr = mysql_fetch_array( mysql_query("SELECT * FROM QAGROUP_LIST WHERE QAGROUP_ID=$group"), MYSQL_ASSOC))
{
	if($gr['QAGROUP_SHOWPRELIM'])
	{
		$qary[] = array('name' => 'prelim.php', 'query' => '?group=' . $group, 'title' => 'Preliminary Information', 'extra' => '/qa/specials/');
	}

	$gt = mysql_query("SELECT * FROM QA_LIST WHERE QA_GROUP=$group");
	if($qa = mysql_fetch_array($gt, MYSQL_ASSOC))
		$qary[] = array('name' => 'qa.php', 'query' => '?id=' . $qa['QA_ID'], 'title' => 'Fill Out Forms', 'path' => '/qa/');

	if($gr['QAGROUP_SHOWCONFIRM'])
		$qary[] = array('name' => 'confirm.php', 'query' => '?group=' . $group, 'title' => 'Confirm', 'path' => '/qa/specials/');
	if($gr['QAGROUP_SHOWCHECK'])
		$qary[] = array('name' => 'check.php', 'query' => '?group=' . $group, 'title' => 'Donations', 'path' => '/qa/specials/');
	if($gr['QAGROUP_SHOWSIG'])
		$qary[] = array('name' => 'sigs.php', 'query' => '?group=' . $group, 'title' => 'Signatures', 'path' => '/qa/specials/');
	if($gr['QAGROUP_SHOWRECEIPT'])
		$qary[] = array('name' => 'receipt.php', 'query' => '?group=' . $group, 'title' => 'Receipt', 'path' => '/qa/specials/');
}

foreach ($qary as $value)
{
	if($flag)
	{
		print ' | ';
		$flag = true;
	}
	print '<a href="' . $value['path'] . $value['name'] . $value['query'] . '" ';
	if($value['name'] == get_scriptname())
		print 'style="font-weight: bold"';
	print '>' . $value['title'] . '</a>';
}
?>

</div>

