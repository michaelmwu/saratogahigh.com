<?
class news
{
	var $id;
	var $datestr;
	var $newsdate;
	var $now;
	var $page;

	function news(&$page)
	{
		$this->page =& $db->page;
	
		$this->id = $_GET['id'];
		$this->datestr = $_GET['date'];

		if(! preg_match("/^\d{4,4}-\d{2,2}$/",$this->datestr))
			$this->now = true;
		else
			$this->newsdate = $this->datestr . "-01 00:00:00";
	}
	
	function handle()
	{
		global $login;
		if($_GET['mode'] == 'del' && is_numeric($this->id))
		{
			if($login->isadmin)
				db::delete('DELETE FROM NEWS_LIST WHERE NEWS_ID=' . $this->id);
			else
				$this->page->error('You do not have permission to delete that news item.');
		}
		else if($_POST['action'] == 'edit')
		{
			if($login->isadmin)
			{
				if(strlen($_POST['text']) < 1)
					$this->page->error('No content was input.',1);
				else
				{
					mysql_query('UPDATE NEWS_LIST SET NEWS_TITLE="' . $_POST['title'] . '", NEWS_TEXT="' . $_POST['text'] . '" WHERE NEWS_ID=' . $this->id); 
					$this->page->error('News item edited successfully.');
				}
			}
			else
				$this->page->error('Sorry, you do not have permission to edit this news item.',1);
		}
		else if($_POST['action'] == 'post')
		{
			if($login->isadmin)
			{
				if(strlen($_POST['text']) < 1)
					$this->page->error('You did not enter any content.');
				else
				{
					mysql_query('INSERT INTO NEWS_LIST (NEWS_TITLE,NEWS_USER,NEWS_TS,NEWS_TEXT,NEWS_SITE) VALUES ("' . $_POST['title'] . '","' . $login->userR['user_id'] . '","' . $DateTime->datetime() . '","' . $_POST['text'] . '", ' . SITE . ')') or die('Cannot insert news' . mysql_error());
					$page->error('News posted successfully.');
				}
				
				if($_POST['again'])
					header("Location: http://" . DNAME . $page->self . "?mode=post");
			}
			else $this->page->error('Sorry, you do not have permission to post news.');
		}
	}
	
	function body()
	{
		global $login;
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
?>
<form action="<?=$this->page->self?>" method="POST">
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
		$nq = mysql_query('SELECT * FROM NEWS_LIST WHERE NEWS_ID=' . $this->id . ' AND NEWS_SITE=1');
		if(!$news = mysql_fetch_array($nq, MYSQL_ASSOC))
			print '<div style="padding-bottom: 12px">There is no such news item. Go back and try again.</div>';
		else
		{
			if($_GET['msg'] == 'blank')
			print 'Enter some content<br><br>';
?>
<form action="<?=$this->page->self?>?id=<?=$this->id?>" method="POST">
<input type="hidden" name="action" value="edit">
<table>
<tr><td class="bodies"><span class="cat">Title: </span></td><td><input type="text" name="title" value="<?=form::formout($news['NEWS_TITLE'])?>"></td></tr>
<tr><td class="bodies"><span class="cat">Content: </span></td><td><textarea name="text" rows="20" cols="60"><?=form::formout($news['NEWS_TEXT'])?></textarea></td></tr>
<tr><td></td><td><input type="hidden" name="action" value="edit"><input type="submit" value="Save"></td></tr>
</table>
</form>
<?
		}
	}
	
	function archive()
	{
		print '<ul style="list-style-type: none">';
		$dates = mysql_query('SELECT DATE_FORMAT(NEWS_TS,"%Y") AS YEAR, DATE_FORMAT(NEWS_TS,"%m") AS MONTH, DATE_FORMAT(NEWS_TS,"%M") AS MONTHTEXT, COUNT( * ) FROM NEWS_LIST WHERE NEWS_SITE=' . SITE . ' GROUP BY YEAR, MONTH HAVING COUNT( * ) > 0 ORDER BY YEAR DESC, MONTH DESC') or die(mysql_error());
	
		if(mysql_num_rows($dates) == 0)
			print '<li>Sorry, there are no dates where there are news.</li>';
	
		while($date = mysql_fetch_array($dates, MYSQL_ASSOC))
			print '<li><a href="' . $this->page->self . '?date=' . $date['YEAR'] . '-' . $date['MONTH'] . '">' . $date['MONTHTEXT'] . ' ' . $date['YEAR'] . '</a></li>';
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

	function newsitem($newsid, $numcomm=1, $linkcomm = 1)
	{
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
				print '<tr><td colspan="2" style="text-align: center; font-size: 12px;"><a href="' . $this->page->self . '?mode=edit&amp;id=' . $newsr['NEWS_ID'] . '&amp;year=' . $newsr['YEAR(NEWS_TS)'] . '">Edit</a>&nbsp;&nbsp;<a href="#" onClick="delconfirm(\'Really delete this news item?\', ' . $newsr['NEWS_ID'] . ', ' . $newsr['YEAR(NEWS_TS)'] . ');">Delete</a></td></tr>';
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

class comments
{
	var $newsid;
	var $page = 'comments.php';
	var $fail;

	function __construct()
	{
		$this->newsid = $_GET['news'];
	}

	function handle()
	{
		include 'globals.php';

		if(!is_numeric($newsid))
		{
			$newsq = mysql_query('SELECT NEWS_LIST.*, PHPBB_users.username FROM NEWS_LIST INNER JOIN PHPBB_users ON NEWS_USER=user_id WHERE NEWS_SITE=1 AND NEWS_ID=' . $this->newsid);
			if(!$newsr = mysql_fetch_array($newsq, MYSQL_ASSOC))
				$page->error('Could not find news item.');
			
			$newsr['NEWS_TITLE'] = $newsr['NEWS_TITLE'] or "Error";
			
			$page->title = $newsr['NEWS_TITLE'] . " | View Comments";
			$page->bodytitle = 'View Comments';
		}
		else
		{
			$page->title = 'Error';
			$page->bodytitle = 'Error';
			$page->error('Invalid ID');
			$page->fail = 1;
		}

		if(!$this->fail)
		{
			if($_POST['action'] = 'post' && $_GET['mode'] == 'post')
			{
				$_POST['text'] = preg_replace("/<[^<>]+>/", "", $_POST['text']);
				$_POST['text'] = preg_replace("/^\s+?/", "", $_POST['text']);
				$_POST['text'] = preg_replace("/\s+?$/", "", $_POST['text']);
				
				if(strlen($_POST['text']) < 1)
				{
					$page->error('No text was entered.',1);
					$this->fail = 1;
				}
				
				if(!$this->fail)
				{
					if($login->loggedin)
					{
						mysql_query('INSERT INTO COMMENT_LIST (COMMENT_USER,COMMENT_NEWS,COMMENT_TS,COMMENT_TEXT,COMMENT_IP) VALUES("' . $login->userR['user_id'] . '","' . $this->newsid . '","' . $DateTime->datetime() . '","' . $_POST['text'] . '","' . $_SERVER['REMOTE_ADDR'] . '")') or die('Insert failed:' . mysql_error());
						$page->error('Comment Posted.',1);
					}
					else if($_POST['posttype'] == 'user')
					{
						$uq = mysql_query("SELECT * FROM PHPBB_users WHERE username='" . $_POST['username'] . "' AND user_password=MD5('" . $_POST['pass'] . "')") or die('Could not select:' . mysql_error());
			
						if($postuser = mysql_fetch_array($uq, MYSQL_ASSOC))
						{
							mysql_query('INSERT INTO COMMENT_LIST (COMMENT_USER,COMMENT_NEWS,COMMENT_TS,COMMENT_TEXT,COMMENT_IP) VALUES("' . $postuser['user_id'] . '","' . $this->newsid . '","' . $DateTime->datetime() . '","' . $_POST['text'] . '","' . $_SERVER['REMOTE_ADDR'] . '")') or die('Insert failed:' . mysql_error());
							if(isset($_POST['login']))
							{
								setcookie('UN', $postuser['user_id'], time() + 86400, '/', '.digitalitcc.com');
								setcookie('PWD', $postuser['user_password'], time() + 86400, '/', '.digitalitcc.com');
								$page->error('Comment Posted. You are now logged in as <span class="cat">' . $postuser['username'],1);
							}
						}
						else
							$page->error('Incorrect Username / Password.',1);
					}
					else if($_POST['posttype'] == 'anon')
					{
						mysql_query('INSERT INTO COMMENT_LIST (COMMENT_NEWS,COMMENT_TS,COMMENT_TEXT,COMMENT_IP) VALUES("' . $this->newsid . '","' . $DateTime->datetime() . '","' . $_POST['text'] . '","' . $_SERVER['REMOTE_ADDR'] . '")') or die('Insert failed:' . mysql_error());
						$page->error('Comment Posted.',1);
					}
					else
						$page->error('Invalid post type',1);
				}
			}
			else if($_POST['action'] = 'edit' && $_GET['mode'] == 'edit')
			{
			}
			else if($_GET['mode'] == 'del')
			{
				$commid = $_GET['id'];
	
				$cq = mysql_query("SELECT * FROM COMMENT_LIST WHERE COMMENT_ID=" . $commid);
				if(!$cr = mysql_fetch_array($cq, MYSQL_ASSOC))
					die('No such comment.');
				
				if($login->loggedin)
				{
					if($login->isadmin || $cr['COMMENT_USER'] == $login->userR['user_id'])
					{
						mysql_query('DELETE FROM COMMENT_LIST WHERE COMMENT_ID=' . $commid);
						$page->error('Comment deleted.',1);
					}
					else
						$page->error('You do not have permission to delete that comment.',1);
				}
				else
					$login->forceLogin( $this->page . '?news=' . $_GET['news']);
			}
		}
/*				else if($_GET['err'] == 'editperm')
					$page->error('<p>You do not have permission to edit that comment.</p>',1);
				else if($_GET['err'] == 'edit')
					$page->error('<p>Comment edited.</p>',1); */
	}
	
	function comments($newsid)
	{
		$cq = mysql_query('SELECT * FROM COMMENT_LIST WHERE COMMENT_NEWS=' . $newsid);
		while($comm = mysql_fetch_array($cq,MYSQL_ASSOC))
			$this->comment($comm['COMMENT_ID']);
	}
	
	function comment($commentid)
	{
		include 'globals.php';
		$cq = mysql_query("SELECT *, PHPBB_users.username as username FROM COMMENT_LIST LEFT JOIN PHPBB_users ON COMMENT_USER=user_id WHERE COMMENT_ID=$commentid");
		
		if($comm = mysql_fetch_array($cq,MYSQL_ASSOC))
		{
			print '<table style="border-bottom: 1px solid; width: 100%;"><tr><td><div>by <span class="cat">' . "\n";
			if(strlen($comm['username']) > 0)
				print stripslashes($comm['username']);
			else
				print 'Anonymous';
			print '</span> on ' . $DateTime->fulldatetime(strtotime($comm['COMMENT_TS']));
			if($login->isadmin)
				print ", IP: " . $comm['COMMENT_IP'];
			print "</div></td>\n";
			if($login->loggedin && ($login->isadmin || $comm['COMMENT_USER'] == $login->userR['user_id']))
			{
				print '<td style="text-align: right;"><a href="' . $this->page . '?mode=edit&id=' . $comm['COMMENT_ID'] . '&news=' . $this->newsid . '">Edit</a>&nbsp;<a href="#" onClick="delconfirm(\'Really delete this comment?\', ' . $comm['COMMENT_ID'] . ');">Delete</a></td>' . "\n";
			}
			print '</tr></table>' . "\n";
			print '<p style="padding-left: 4px; margin-top: 0px; background-color: #F4F4F4;">' . nl2br($comm['COMMENT_TEXT']) . '</p>' . "\n";
		}
		else
			print "No such comment";
	}
	
	function commentlink($newsid,$link = 1)
	{
		print '<td style="text-align: right; font-size: 12px;">';
		if($link)
			print '<a href="' . $this->page . '?news=' . $newsid . '" style="margin-left: 0px;">';
		print 'Comments (';
		$cq = mysql_query('SELECT * FROM COMMENT_LIST WHERE COMMENT_NEWS=' . $newsid);
		print mysql_num_rows($cq) . ')';
		if($link)
			print '</a>';
		print '</td></tr>';
	}
	
	function body()
	{
		if($_GET['mode']=='edit')
			$this->edit();
		else
			$this->main();
	}
	
	function edit()
	{
		include 'globals.php';
		
		if(!$login->loggedin)
			forceLogin();
	}
	
	function main()
	{
		include 'globals.php';
		
		$news->news($this->newsid,1,0);

		print '<div style="text-align: center; padding-top: 4px;"><span><a href="#" onClick="showPost();">Post a Comment</a></span><hr style="height: 1px;"></div>';

		$this->comments($this->newsid);

		print '<div id="postdiv"><span class="cat">Post a Comment</span><form action="' . $this->page . '?mode=post&news=' . $this->newsid . '" method="POST"><p>' . "\n";
		if(!$login->loggedin)
		{
			print '<label><input type="radio" name="posttype" value="anon" checked> Post as Anonymous (logs IP)</label>' . "\n";
			print '<br><label><input type="radio" name="posttype" value="user"> As user</label>' . "\n";
			print '<br> <span style="margin-left: 25px;">Username:</span> <input type="text" name="username" size="14" maxlength="40"> Password: <input type="password" name="pass" size="14" maxlength="40"> <label>Log In? <input type="checkbox" name="login"></label>' . "\n";
			print '<input type="hidden" name="loggedin" value="0">' . "\n";
		}
		else
		{
			print '<input type="hidden" name="loggedin" value="1">' . "\n";
			print 'Logged in as <span class="cat">' . $login->userR['username'] . '</span>' . "\n";
		}
	
		print '<br><br>No HTML allowed' . "\n";
		print '<br><span class="cat">Message:</span>' . "\n";
		print '<br><textarea name="text" rows="8" cols="70"></textarea>' . "\n";
		print '<br><input type="hidden" name="action" value="post"><input type="submit" value="Post"></p>' . "\n";
		print '</form></div>' . "\n";

		$javascript = 
"<script type=\"text/javascript\">
<!--
	document.getElementById('postdiv').style.display = 'none';

	function showPost()
	{
		document.getElementById('postdiv').style.display = 'block';
	}

	function delconfirm(msg,no)
	{
		input = confirm(msg);
		if (input==true)
		{
			window.location.href = '" . $this->page . "?mode=del&news=" . $this->newsid . "&id=' + no;
		}
	}
-->
</script>";
		$page->javascript($javascript);
	}
}
?>
