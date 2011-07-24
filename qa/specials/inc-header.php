<?
include '../../inc-headercommon.php';

$qary = array();
$group = $_GET['group'];
if(!is_numeric($group))
	die();

if($gr = mysql_fetch_array( mysql_query("SELECT * FROM QAGROUP_LIST WHERE QAGROUP_ID=$group"), MYSQL_ASSOC))
{
	if($gr['QAGROUP_SHOWPRELIM'])
	{
		$rsfills = mysql_query('SELECT * FROM QAFILL_LIST WHERE QAFILL_USER='. $userid . ' AND QAFILL_QA=' . $qa_id);
		if($fills = mysql_fetch_array($rsfills, MYSQL_ASSOC))
			$value = 2;
		else
			$value = 0;

		$qary[] = array('prelim.php?group=' . $group, 'Preliminary Information', $value);
	}

	$gt = mysql_query("SELECT * FROM QA_LIST WHERE QA_GROUP=$group");
	while($qa = mysql_fetch_array($gt, MYSQL_ASSOC))
	{
		$value = 2;
		$gu = mysql_query("SELECT * FROM QAPAGE_LIST
					INNER JOIN QA_LIST ON QAPAGE_QA=QA_ID
					LEFT JOIN QAFILLPAGE_LIST ON QAFILLPAGE_PAGE=QAPAGE_ID
					WHERE QA_ID=" . $qa['QA_ID']);
		while($fill = mysql_fetch_array($gu, MYSQL_ASSOC))
		{
			$value = ($value && $fill['QAFILLPAGE_SAVED']) ? 2 : 0;
		}
		$qary[] = array('../qa.php?id=' . $qa['QA_ID'], $qa['QA_TITLE'], $value);
	}

	if($gr['QAGROUP_SHOWCONFIRM'])
		$qary[] = array('confirm.php?group=' . $group, 'Confirm', 0);
	if($gr['QAGROUP_SHOWCHECK'])
		$qary[] = array('check.php?group=' . $group, 'Donations', 0);
	if($gr['QAGROUP_SHOWSIG'])
		$qary[] = array('sigs.php?group=' . $group, 'Signatures', 0);
	if($gr['QAGROUP_SHOWRECEIPT'])
		$qary[] = array('receipt.php?group=' . $group, 'Receipt', 0);
}
else
	die("Sorry, that group does not exist.");\
?>
<div class="navbar"><span style="font-weight: bold">Q&amp;A Service</span>:
<a class="lnkh" href="../">Home</a></div>