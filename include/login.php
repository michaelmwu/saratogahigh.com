<?
class login
{
	public $userR;
	public $loggedin;
	public $isclub;
	public $isadmin;
	public $isprog;
	protected $levelfield = "USER_ITCCLEVEL";
	
	function __construct()
	{
		if(is_numeric($_COOKIE['UN']) && isset($_COOKIE['PWD']))
		{
			$uq = mysql_query("SELECT * FROM PHPBB_users LEFT JOIN PRIVILEGE_LIST ON PRIVILEGE_USER=user_id AND PRIVILEGE_SITE=' . SITE . ' WHERE user_id='" . addslashes($_COOKIE['UN']) . "' AND user_password='" . addslashes($_COOKIE['PWD']) . "'");
	
			if($this->userR = mysql_fetch_array($uq, MYSQL_ASSOC))
				$this->loggedin = true;
			else
				header('Location: http://' . DNAME . '/login.php?next=' . $_SERVER['REQUEST_URI']);

			if($_SERVER['HTTP_HOST'] == 'csf.shsclubs.org')
				$this->levelfield = "USER_CSFLEVEL";

			$this->isclub = $this->userR[$this->levelfield] > 0 || $this->userR['PRIVILEGE_LEVEL'] > 0;
			$this->isadmin = $this->userR[$this->levelfield] > 1 || $this->userR['PRIVILEGE_LEVEL'] > 1;
			$this->isprog = $this->userR[$this->levelfield] > 2 || $this->userR['PRIVILEGE_LEVEl'] > 2;
		}
	}

	function go($user,$pass,$remember=0)
	{
		$uq = mysql_query("SELECT * FROM PHPBB_users WHERE username='" . $user . "' AND user_password=MD5('" . $pass . "')");
		if($ur = mysql_fetch_array($uq, MYSQL_ASSOC))
		{
			if($remember)
				$time = time() + 30 * 86400;
			else
				$time = 0;
	
			setcookie('UN', $ur['user_id'], $time, '/', '.digitalitcc.com');
			setcookie('PWD', $ur['user_password'], $time, '/', '.digitalitcc.com');
			setcookie('UN', $ur['user_id'], $time, '/', '.shsclubs.org');
			setcookie('PWD', $ur['user_password'], $time, '/', '.shsclubs.org');
	
			if(isset($_GET['next']))
				header('Location: http://' . DNAME . $_GET['next']);
			else
				header('Location: http://' . DNAME . '/index.php');
	
			return 1;
		}
		return 0;
	}
	
	function forceLogin($next = 'none')
	{
		if($next == 'none')
			$next = $_SERVER['REQUEST_URI'];
		header('Location: http://' . DNAME . '/login.php?req&next=' . urlencode($next));
	}
	
	function logout()
	{
		setcookie("UN", 'nobody', time() + 86400, "/", '.digitalitcc.com');
		setcookie("PWD", '0x000', time() + 86400, "/", '.digitalitcc.com');
		setcookie("UN", 'nobody', time() + 86400, "/", '.shsclubs.org');
		setcookie("PWD", '0x000', time() + 86400, "/", '.shsclubs.org');
		
		header('Location: http://' . DNAME . '/');
	}
	
	function desc() {
		return "login";
	}
}

$login = new login();
?>