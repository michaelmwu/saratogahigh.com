<?
class news
{
	public $id;
	public $datestr;
	public $newsdate;
	public $now;
	public $page = "index.php";

	function __construct()
	{
		$this->id = $_GET['id'];
		$this->datestr = $_GET['date'];

		if(! preg_match("/^\d{4,4}-\d{2,2}$/",$this->datestr))
			$this->now = true;
		else
			$this->newsdate = $this->datestr . "-01 00:00:00";
	}
	
	function handle()
	{
		include 'globals.php';
		
		if($_GET['mode']=='post' || $_GET['mode']=='edit')
			$page->side = 0;

		if($_GET['mode'] == 'del' && is_numeric($this->id))
		{
			if($login->isadmin)
				mysql_query('DELETE FROM NEWS_LIST WHERE NEWS_ID=' . $this->id);
			else
				$page->error('You do not have permission to delete that news item.',1);
		}
		else if($_POST['action'] == 'edit')
		{
			if($login->isadmin)
			{
				if(strlen($_POST['text']) < 1)
					$page->error('No content was input.',1);
				else
				{
					mysql_query('UPDATE NEWS_LIST SET NEWS_TITLE="' . $_POST['title'] . '", NEWS_TEXT="' . $_POST['text'] . '" WHERE NEWS_ID=' . $this->id); 
					$page->error('News item edited successfully.',1);
				}
			}
			else
				$page->error('Sorry, you do not have permission to edit this news item.',1);
		}
		else if($_POST['action'] == 'post')
		{
			if($login->isadmin)
			{
				if(strlen($_POST['text']) < 1)
					$page->error('You did not enter any content.');
				else
				{
					mysql_query('INSERT INTO NEWS_LIST (NEWS_TITLE,NEWS_USER,NEWS_TS,NEWS_TEXT,NEWS_SITE) VALUES ("' . $_POST['title'] . '","' . $login->userR['user_id'] . '","' . $DateTime->datetime() . '","' . $_POST['text'] . '", ' . SITE . ')') or die('Cannot insert news' . mysql_error());
					$page->error('News posted successfully.',1);
				}
				
				if($_POST['again'])
					header("Location: http://" . DNAME . $page->self . "?mode=post");
			}
			else $page->error('Sorry, you do not have permission to post news.');
		}
	}
	
	function body()
	{
		include 'globals.php';
		if($_GET['mode'] == 'post' && $login->isadmin)
			$this->post();
		else if($_GET['mode'] == 'edit' && is_numeric($this->id) && $login->isadmin)
			$this->edit();
		else if($_GET['mode'] == 'archive')
			$this->archive();
		else
			$this->main();
	}
	
	function post()
	{
		include 'globals.php';
?>
<form action="<?=$page->self?>" method="POST">
<input type="hidden" name="action" value="post">
<table width="400">
<tr>
<td class="bodies">
<span class="cat">Title:&nbsp;</span>
</td>
<td class="bodies">
<input type="text" name="title" size="40" maxlength="100">
</td>
</tr>
<tr>
<td class="bodies">
<span class="cat">Content:&nbsp;</span>
</td>
<td class="bodies">
<textarea name="text" rows="20" cols="60">
</textarea>
</td>
</tr>
</table>
<p>
<label><input type="checkbox" name="again" <?=isset($_POST['again'])?'checked':''?>> Post and go back to this page.</label>
<br><input type="submit" value="Post">
</p>
</form>
<?
	}
	
	function edit()
	{
		include 'globals.php';
		$nq = mysql_query('SELECT * FROM NEWS_LIST WHERE NEWS_ID=' . $this->id . ' AND NEWS_SITE=1');
		if(!$news = mysql_fetch_array($nq, MYSQL_ASSOC))
			print '<div style="padding-bottom: 12px">There is no such news item. Go back and try again.</div>';
		else
		{
			if($_GET['msg'] == 'blank')
			print 'Enter some content<br><br>';
?>
<form action="<?=$page->self?>?id=<?=$this->id?>" method="POST">
<input type="hidden" name="action" value="edit">
<table>
<tr><td class="bodies"><span class="cat">Title: </span></td><td><input type="text" name="title" value="<?=$page->formout($news['NEWS_TITLE'])?>"></td></tr>
<tr><td class="bodies"><span class="cat">Content: </span></td><td><textarea name="text" rows="20" cols="60"><?=$page->formout($news['NEWS_TEXT'])?></textarea></td></tr>
<tr><td></td><td><input type="hidden" name="action" value="edit"><input type="submit" value="Save"></td></tr>
</table>
</form>
<?
		}
	}
	
	function archive()
	{
		include 'globals.php';
		print '<ul style="list-style-type: none">';
		$dates = mysql_query('SELECT DATE_FORMAT(NEWS_TS,"%Y") AS YEAR, DATE_FORMAT(NEWS_TS,"%m") AS MONTH, DATE_FORMAT(NEWS_TS,"%M") AS MONTHTEXT, COUNT( * ) FROM NEWS_LIST WHERE NEWS_SITE=' . SITE . ' GROUP BY YEAR, MONTH HAVING COUNT( * ) > 0 ORDER BY YEAR DESC, MONTH DESC') or die(mysql_error());
	
		if(mysql_num_rows($dates) == 0)
			print '<li>Sorry, there are no dates where there are news.</li>';
	
		while($date = mysql_fetch_array($dates, MYSQL_ASSOC))
			print '<li><a href="' . $page->self . '?date=' . $date['YEAR'] . '-' . $date['MONTH'] . '">' . $date['MONTHTEXT'] . ' ' . $date['YEAR'] . '</a></li>';
		print '</ul>';
	}
	
	function main()
	{
		if($this->now)
		$newsq = mysql_query('SELECT NEWS_LIST.*, PHPBB_users.username FROM NEWS_LIST INNER JOIN PHPBB_users ON NEWS_USER=user_id WHERE NEWS_TS > DATE_SUB(CURDATE(),INTERVAL 1 MONTH) AND NEWS_SITE=' . SITE . ' ORDER BY NEWS_TS DESC');
		else
			$newsq = mysql_query('SELECT NEWS_LIST.*, PHPBB_users.username FROM NEWS_LIST INNER JOIN PHPBB_users ON NEWS_USER=user_id WHERE DATE_FORMAT(NEWS_TS,"%Y-%m") = "' . $this->datestr . '" AND NEWS_SITE=' . SITE . ' ORDER BY NEWS_TS DESC');
		
		if(mysql_num_rows($newsq) == 0 )
			print '<p>It doesn\'t look like there is any news for that time.</p>';
		
		while($news = mysql_fetch_array($newsq, MYSQL_ASSOC))
			$this->news($news['NEWS_ID']);
	}

	function news($newsid, $numcomm=1, $linkcomm = 1)
	{
		include 'globals.php';
	
		$newsq = mysql_query('SELECT NEWS_LIST.*, PHPBB_users.username, YEAR(NEWS_TS) FROM NEWS_LIST LEFT JOIN PHPBB_users ON NEWS_USER=user_id WHERE NEWS_ID=' . $newsid);
		if($newsr = mysql_fetch_array($newsq, MYSQL_ASSOC))
		{
			print '<p><span class="subtitle">' . stripslashes($newsr['NEWS_TITLE']) . '</span>';
	
			print '<br><br>' . nl2br(stripslashes($newsr['NEWS_TEXT'])) . '</p>';
	
			print '<table border="0" style="border-top: 1px dotted #f2f3f3; margin-right: 5px; width: 100%" cellspacing="0"><tr><td style="width: 220px; font-size: 12px;">';
			print $DateTime->jMY(strtotime($newsr['NEWS_TS'])) . ' by <span class="cat">' . (strlen($newsr['NEWS_USERNAME'])<1?$newsr['username']:$newsr['NEWS_USERNAME']) . '</span></td>';
			if($numcomm)
				$comments->commentlink($newsr['NEWS_ID'],$linkcomm);
			if($login->isadmin)
				print '<tr><td colspan="2" style="text-align: center; font-size: 12px;"><a href="' . $this->self . '?mode=edit&amp;id=' . $newsr['NEWS_ID'] . '&amp;year=' . $newsr['YEAR(NEWS_TS)'] . '">Edit</a>&nbsp;&nbsp;<a href="#" onClick="delconfirm(\'Really delete this news item?\', ' . $newsr['NEWS_ID'] . ', ' . $newsr['YEAR(NEWS_TS)'] . ');">Delete</a></td></tr>';
			print '</table>';
			print '<div><hr style="height: 1px; margin: 0px 5px 0px 0px;"></div>';
			return 1;
		}
		else
		{
			print 'No such news item.';
			return 0;
		}
	}

	function desc() {
		return "news";
	}
	
	function modet($mode)
	{
		switch($mode)
		{
			case 'post':
				return 'Post News';
			case 'edit':
				return 'Edit News';
			case 'archive':
				return 'Archives';
			default:
				return ($this->now?'Home':'View - ' . date("F Y",strtotime($this->newsdate)));
		}
	}
}

$news = new news();
?>