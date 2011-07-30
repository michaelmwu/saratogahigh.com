<?
define('CUR_SEM', csf::time2sem(time()));

class csf
{
	var $page;
	
	function __construct(&$page)
	{
		$this->page &= $page;
	}
	
	function count_points($member,$sem = CUR_SEM)
	{
		$pointsR = mysql_query("SELECT SUM(HOURS_AMOUNT) AS TOTAL FROM HOURS_LIST LEFT JOIN EVENT_LIST ON HOURS_EVENT=EVENT_ID WHERE EVENT_SEM='" . $sem . "' AND HOURS_MEMBER='" . $member . "'") or die(mysql_error());
		if($point = mysql_fetch_array($pointsR,MYSQL_ASSOC))
		{
			if($point['TOTAL'] < 1)
				return '0';
			return $point['TOTAL'];
		}
	}
	
	function count_attends($member,$sem = CUR_SEM)
	{
		$attendR = mysql_query("SELECT SUM(ATTEND_VALUE) AS TOTAL FROM ATTEND_LIST LEFT JOIN MEETING_LIST ON ATTEND_MEETING=MEETING_ID WHERE MEETING_SEM='" . $sem . "' AND ATTEND_MEMBER='" . $member . "'") or die(mysql_error());
		if($attend = mysql_fetch_array($attendR,MYSQL_ASSOC))
		{
			if($attend['TOTAL'] < 1)
				return '0';
			return $attend['TOTAL'];
		}
	}
	
	function grade($year)
	{
		return 12 - $year + CUR_YEAR;
	}
	
	function ungrade($grade)
	{
		return CUR_YEAR + 12 - $grade;
	}
	
	function isuser($member)
	{
		$userR = mysql_query("SELECT * FROM PHPBB_users INNER JOIN MEMBER_LIST ON MEMBER_USER=user_id WHERE MEMBER_ID=" . $member) or die(mysql_error());
		if($user = mysql_fetch_array($userR, MYSQL_ASSOC))
			return true;
	}
	
	function time2sem($time)
	{
		$shiftyear = date("Y",$time + 5 * 30 * 86400);
		$date = date("Ym",$time);
		if($date < $shiftyear . "01")
			return "$shiftyear-1";
		else
			return "$shiftyear-2";
	}
	
	function date2sem($date)
	{
		$shiftyear = date("Y",strtotime($date) + 5 * 30 * 86400);
		$date = date("Ym",strtotime($date));
		if($date < $shiftyear . "01")
			return "$shiftyear-1";
		else
			return "$shiftyear-2";
	}
	
	function sem2date($sem)
	{
		if(preg_match("/^(\d{4})-(\d)$/",$sem,$match))
		{
			if($match[2] == 1)
				return $match[1] - 1 . '-07-01 00:00:00';
			else if($match[2] == 2)
				return $match[1] . '-01-14 00:00:00';
			else
				return "Invalid";
		}
		return "Invalid";
	}
	
	function event($event,$link = 1)
	{
		global $DateTime;
		global $login;
	?>
	<div><span class="subtitle">
	<? if($link) { ?>
	<a href="<?=$page->self?>?mode=view&id=<?=$event['EVENT_ID']?>">
	<? } 
		print $event['EVENT_TITLE'];
		if($link)
			print '</a>';
		print '</span>';
		if(!isset($_GET['print']))
			print ' <a href="/events.php?mode=view&print&id=' . $event['EVENT_ID'] . '">Printable Version</a>';
		print '<br>';
	?>
	</div>
	<table style="width: 600px;">
	<tr><td style="width: 80px;"><span class="cat">When:</td><td class="ralign"><?=$event['EVENT_WHEN']?></td></tr>
	<tr><td><span class="cat">Location:</td><td class="ralign"><?=$event['EVENT_LOCATION']?></td></tr>
	<tr><td><span class="cat">Project Chair:</td><td class="ralign"><a href="/members.php?mode=view&id=<?=$event['EVENT_CHAIR']?>"><?=$event['MEMBER_FULLNAME']?></a></td></tr>
	<tr><td><span class="cat">Description:</td><td><?=$event['EVENT_DESC']?nl2br($event['EVENT_DESC']):'No description entered'?></td></tr>
	</table>
	<? if($login->isadmin && !isset($_GET['print'])) { ?>
	<div class="center"><a href="<?=$page->self?>?mode=edit&id=<?=$event['EVENT_ID']?>">Edit</a> <a href="#" onClick="delconfirm(<?=$event['EVENT_ID']?>);">Delete</a></div>
	<? }
	}
}

$csf = new csf($page);
?>
