<?
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
	
	function desc()
	{
		return "comments";
	}
}

$comments = new comments();
?>
