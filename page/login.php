<?
define('IS_USER',1);
define('IS_ADMIN',2);
define('IS_PROG',3);

class login
{
	var $userR;
	var $loggedin;
	var $isuser;
	var $isadmin;
	var $isprog;
	
	function login()
	{
		if(is_numeric($_COOKIE['UN']) && isset($_COOKIE['PWD']))
		{
			if($this->userR = db::get_row(USER_TABLE,ID_FIELD . "='" . $_COOKIE['UN'] . "' AND " . PASSWORD_FIELD . "='" . $_COOKIE['PWD'] . "'") )
				$this->loggedin = true;
			else
				header('Location: http://' . DNAME . '/login.php?next=' . $_SERVER['REQUEST_URI']);

			$this->isuser = $this->userR[LEVEL_FIELD] >= IS_USER;
			$this->isadmin = $this->userR[LEVEL_FIELD] >= IS_ADMIN;
			$this->isprog = $this->userR[LEVEL_FIELD] >= IS_PROG;
		}
	}

	function go($user,$pass,$remember=0)
	{
		if($ur = db::get_row(USER_TABLE,UNAME_FIELD . "='" . $user . "' AND " . PASSWORD_FIELD . "=MD5('" . $pass . "')"))
		{
			if($remember)
				$time = time() + 30 * 86400;
			else
				$time = 0;
	
			setcookie('UN', $ur[ID_FIELD], $time, '/', DNAME);
			setcookie('PWD', $ur[PASSWORD_FIELD], $time, '/', DNAME);
	
			if(isset($_GET['next']))
				header('Location: http://' . DNAME . $_GET['next']);
			else
				header('Location: http://' . DNAME . '/index.php');
	
			return true;
		}
		return false;
	}
	
	function forceLogin($next = 'none')
	{
		if($next == 'none')
			$next = $_SERVER['REQUEST_URI'];
		header('Location: http://' . DNAME . '/login.php?req&next=' . urlencode($next));
	}
	
	function logout()
	{
		setcookie("UN", 'nobody', time() - 3600, "/", DNAME);
		setcookie("PWD", '0x000', time() - 3600, "/", DNAME);
		
		header('Location: http://' . DNAME . '/');
	}
	
	function validate()
	{
		if($row = db::get_row(ADMINPAGE_TABLE,"WHERE " . ADMINPAGE_PAGE . " = '" . $_SERVER['PHP_SELF'] . "'"))
			if($row[ADMINPAGE_PRIVILEGE] > $this->userR[LEVEL_FIELD])
				if($this->loggedin)
					die('You do not have permission to view this page.');
				else
					this::forceLogin();
	}
}

$login = new login();
?>
