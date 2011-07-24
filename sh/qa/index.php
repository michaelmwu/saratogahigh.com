<?
include '../db.php';

$showgroup = false;

if(is_numeric($_GET['group']))
{
	$rsgroup = mysql_query('SELECT * FROM QAGROUP_LIST WHERE QAGROUP_ID=' . $_GET['group']);

	if($group = mysql_fetch_array($rsgroup, MYSQL_ASSOC))
		$showgroup = true;

	if($loggedin)
	{
	    	$rsauthor = mysql_query('SELECT * FROM QAAUTHOR_LIST WHERE QAAUTHOR_QAGROUP=' . $group['QAGROUP_ID'] . ' AND QAAUTHOR_USER=' . $userid);
    		if($author = mysql_fetch_array($rsauthor, MYSQL_ASSOC))
    		{
    			$isauthor = true;
    		}
	}
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
	<head>
		<title><? if($showgroup) { print htmlentities($group['QAGROUP_TITLE']); } else { print 'Q&amp;A Service'; } ?></title>
		<link rel="stylesheet" type="text/css" href="../shs.css">
		<link rel="stylesheet" type="text/css" href="qa.css">
		<style type="text/css">
			a.lnkh { font-weight: bold }
			.groupdesc, p { margin-top: 0px }
			h2 { border-bottom: 1px solid black }
		</style>
	</head>
	<body>
		<? include "inc-header.php";

        if($showgroup)
        {
        ?>
        <table cellpadding="5" cellspacing="0" border="0" style="text-align: left; width: 100%">
        <tr>
        <td style="vertical-align: top; background-color: #ffffff" class="groupdesc">
        <h1 class="titlebar" style="margin-bottom: 0.5em"><?
        if($isauthor)
        	print '<span style="float: right; font-size: medium; padding: 2px">View | <a href="edit/group.php?id=' . $group['QAGROUP_ID'] . '">Administer</a></span>'
        ?><?= $group['QAGROUP_TITLE'] ?></h1>

        <? $rsadmins = mysql_query('SELECT USER_ID, USER_FULLNAME FROM QAAUTHOR_LIST INNER JOIN USER_LIST ON QAAUTHOR_USER=USER_ID WHERE QAAUTHOR_QAGROUP=' . $group['QAGROUP_ID'] . ' ORDER BY USER_LN, USER_FN'); ?>
        <p style="font-size: medium"><span style="font-weight: bold">Administered by </span> <?
        $firstadmin = true;
        while($admin = mysql_fetch_array($rsadmins, MYSQL_ASSOC))
        {
        	if(!$firstadmin)
        		print ', ';
        	print '<a href="/directory/?id=' . $admin['USER_ID'] . '">' . $admin['USER_FULLNAME'] . '</a>';
        	$firstadmin = false;
        }
        ?>
        </p>
	<p style="font-size: large; line-height: 48px; font-style: italic; color: #336; background-image: url('bg-arrow.png'); repeat-x: repeat; repeat-y: none">Select an option from the right-hand column...</p>

        <? if(strlen($group['QAGROUP_DESC']) > 0) {  ?>
        <h2>Description</h2>
        <div style="font-size: medium"><?= $group['QAGROUP_DESC'] ?></div>
        <? } ?>
	<? print ($loggedin ? '' : '<p>Please log in to access this group. Click on the red tab above.</p>'); ?>
	<? if($group['QAGROUP_ID'] == 5) { ?>
	<p><em style="font-size: medium">As one of the first parents to use our Q&amp;A Service this year, your feedback and bug reports are especially important. If you encounter any problems with our software&mdash; no matter how small or large&mdash; or if you just have questions, please don't hesitate to contact us either by using the grey comment box at the bottom of each page or by calling 867-3411 x248. Thank you!</em></p>
	<? } ?>
	<p><strong>Note</strong>: Q&amp;A Service requires <a href="http://www.mozilla.org/">Mozilla</a>, or Microsoft Internet Explorer 5.0+.</p>
    </td>
        <td style="vertical-align: top" class="rightcol">
        <?

	if($loggedin)
	{

        if($group['QAGROUP_SHOWPRELIM'])
        	print '<h2 class="blueheading">Enter Preliminary Info</h2><div class="hcontent"><a href="specials/prelim.php?rg=' . $_GET['group'] . '">Check</a> that your personal information is up-to-date before you begin.</div>';

        $rstypes = mysql_query('SELECT DISTINCT QA_TYPE FROM QA_LIST WHERE QA_GROUP=' . $_GET['group'] . ' AND QA_OPEN > 0 ORDER BY QA_TYPE');
        while($type = mysql_fetch_array($rstypes, MYSQL_ASSOC))
        {
        	print '<h2 class="blueheading">Fill Out ' . $type['QA_TYPE'] . ($type['QA_TYPE'] == 'Quiz' ? 'zes' : ($type['QA_TYPE'] == 'Signup' ? ' sheet' : 's')) . '</h2><div class="hcontent">';

        				$rsqas = mysql_query('SELECT * FROM QA_LIST WHERE QA_GROUP=' . $_GET['group'] . ' AND QA_TYPE="' . $type['QA_TYPE'] . '" AND QA_OPEN > 0 ORDER BY QA_ID');
        	while($qa = mysql_fetch_array($rsqas, MYSQL_ASSOC))
        	{
        		if($qa['QA_OPEN'] == 1)
	        		print '<div style="padding: 1px"><div style="font-size: medium"><a href="qa.php?id=' . $qa['QA_ID'] . '">' . $qa['QA_TITLE'] . '</a></div><div style="margin-left: 1em">';
			if($qa['QA_OPEN'] == 2 && $qa['QA_SHOWANSWERS'] == 1)
				print '<div style="padding: 1px"><div style="font-size: medium"><a style="color: #888" href="qa.php?id=' . $qa['QA_ID'] . '">' . $qa['QA_TITLE'] . '</a></div><div style="margin-left: 1em">';
			/*
			if($qa['QA_OPEN'] == 1)
        			print 'Open';
        		else if($qa['QA_OPEN'] == 2)
        		{
        			print 'Closed';
            			if($qa['QA_SHOWANSWERS'] == 1)
            				print '; answers posted';
        		}
			*/
        		print '</div></div>';
        	}
        	print '</div>';
        }

        if($group['QAGROUP_SHOWCONFIRM'])
        	print '<h2 class="blueheading">Check Your Information</h2><div class="hcontent">Be sure to <a href="specials/confirm.php?group=' . $_GET['group'] . '">double-check</a> all your submissions for correctness.</div>';

	if($group['QAGROUP_ID'] == 5)
	{
        	print '<h2 class="blueheading">Donation Summary</h2><div class="hcontent">Here\'s the <a href="specials/check.php?group=' . $_GET['group'] . '">list of organizations</a> you opted to donate to. Write checks and/or pay online!</div>';
        	print '<h2 class="blueheading">Submit Your Signature</h2><div class="hcontent">Print, sign and mail or drop off <a href="specials/sigs.php?group=' . $_GET['group'] . '">this sheet</a> along with your donation(s), and you\'re all set.</div>';
        }

        //if($group['QAGROUP_SHOWRECEIPT'])
        //	print '<h2 class="blueheading">Receipt Confirm</h2><div class="hcontent">Check back to <a href="specials/receipt.php?id=' . $_GET['group'] . '">confirm</a> that we've received your papers.</div>';

	}

        ?>
        </td>
        </tr>
        </table>
        <?
        }
        else
        {
        ?>
        <h1 style="letter-spacing: 3pt; margin: 0px; padding: 3px; background-color: #cc3333; color: #ffffff; font-size: large">Q&amp;A Service</h1>
        <p style="margin: 0px; padding: 3px; background-color: #eeeeee; border-bottom: 1px solid #999999">All data are submitted electronically; our software compiles the results and calculates scores and tallies. It saves time, energy, and paper! Please send us your suggestions using the gray comment box at the bottom of this page. If you're a teacher or a club head who would like to test out this service, please drop us a line (using the gray box).</p>

        <?
        $rsgroups = mysql_query('SELECT * FROM QAGROUP_LIST ORDER BY QAGROUP_TITLE');

        ?>
        <div style="font-size: medium">
        <h2 style="margin: 0px">Select a group to get started...</h2><div style="padding: 15px; font-size: medium"><?

        $numgroups = 0;

        while($group = mysql_fetch_array($rsgroups, MYSQL_ASSOC))
        {
        	if($group['QAGROUP_ACTIVE'] || $isadmin)
        	{
        		print '<div><a';
        		if(!$group['QAGROUP_ACTIVE'] && $isadmin)
        			print ' style="color: #999999"';
        		print ' href="./?group=' . $group['QAGROUP_ID'] . '">' . $group['QAGROUP_TITLE'] . '</a></div>';

        		$numgroups++;
        	}
        }

        if($numgroups == 0)
        	print '<p>No groups are active at this time.</p>';

        ?>
		<p><strong>Note</strong>: Q&amp;A Service requires <a href="http://www.mozilla.org/">Mozilla</a> or Internet Explorer 5.0+.</p>
        </div>
        <?
        }
		?>

		<? include '../inc-footer.php'; ?>
	</body>
</html>
