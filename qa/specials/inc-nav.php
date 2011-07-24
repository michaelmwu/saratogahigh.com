<style type="text/css">
<!--
ul.qanavbar {list-style: none;}
ul.qanavbar li { float: left; background-color: #009; color: #fff; border: #66c 2px solid; padding: 3px; margin: 3px;}
ul.qanavbar li.current { background-color: #fff; font-weight: bold; color: #d00; border: #c00 2px solid;}
ul.qanavbar + div {clear: left;}

span.current {background-color: #fff; font-weight: bold; color: #d00; border: #c00 2px solid; padding: 3px; margin: 3px;}
span.notcurrent {background-color: #009; color: #fff; border: #66c 2px solid; padding: 3px; margin: 3px;}
-->
</style>
<div style="background-color: #006; padding: 8px 0px">
<?
$qary = array();
if(!is_numeric($group))
	die('Sorry, that group does not exist.');

if($gr = mysql_fetch_array( mysql_query("SELECT * FROM QAGROUP_LIST WHERE QAGROUP_ID=$group"), MYSQL_ASSOC))
{
	if($gr['QAGROUP_SHOWPRELIM'])
	{
		$qary[] = array('name' => 'prelim.php', 'query' => '?rg=' . $group, 'title' => 'Preliminary Information', 'path' => '/qa/specials/');
	}

	$gt = mysql_query("SELECT * FROM QA_LIST WHERE QA_GROUP=$group");
	if($qaline = mysql_fetch_array($gt, MYSQL_ASSOC))
		$qary[] = array('name' => 'qa.php', 'query' => '?id=' . $qaline['QA_ID'], 'title' => 'Fill Out Forms', 'path' => '/qa/');

	if($gr['QAGROUP_SHOWCONFIRM'])
		$qary[] = array('name' => 'confirm.php', 'query' => '?group=' . $group, 'title' => 'Check Your Information', 'path' => '/qa/specials/');
	if($gr['QAGROUP_SHOWCHECK'])
		$qary[] = array('name' => 'check.php', 'query' => '?group=' . $group, 'title' => 'Donation Summary', 'path' => '/qa/specials/');
	if($gr['QAGROUP_SHOWSIG'])
		$qary[] = array('name' => 'sigs.php', 'query' => '?group=' . $group, 'title' => 'Signature Sheet', 'path' => '/qa/specials/');
	//if($gr['QAGROUP_SHOWRECEIPT'])
		//$qary[] = array('name' => 'receipt.php', 'query' => '?group=' . $group, 'title' => 'Receipt', 'path' => '/qa/specials/');
}

$z = 0;
foreach ($qary as $value)
{
	if($value['name'] == get_scriptname() || ($value['name'] == 'qa.php' && get_scriptname() == 'page.php'))
	{
		print '<span class="current">';
		print ($z + 1) . ". ";
		print '<a href="' . $value['path'] . $value['name'] . $value['query'] . '" ';
		print 'style="color: #d00;"';
		print '>' . $value['title'] . '</a>';
		print "</span>\n";
		$curposition = $z;

	}
	else
	{
		print '<span class="notcurrent">';
		print ($z + 1) . ". ";
		print '<a href="' . $value['path'] . $value['name'] . $value['query'] . '" ';
		print 'style="color: #fff;"';
		print '>' . $value['title'] . '</a>';
		print "</span>\n";
	}

	$z++;
}
?>
</div>

