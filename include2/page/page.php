<?
// page.php | icyhandofcrap
/*
	This sets up a nice environment to program PHP pages in.
*/
// Redirect if wrong domain
if( $_SERVER['HTTP_HOST'] != DNAME )
	header("Location: http://" . DNAME . $_SERVER['REQUEST_URI']);

if(!SITE_ENABLED)
	die(MAINTAIN_ERROR);

$dependencies = array(
			'comments' => array('db','news'),
			'csf' => array('db'),
			'login' => array('db'),
			'news' => array('db'),
			);

if( NO_CACHE )
{
	// Date in the past
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	
	// always modified
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	 
	// HTTP/1.1
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	
	// HTTP/1.0
	header("Pragma: no-cache");
}

if( OUTPUT_BUFFER )
	ob_start($callback);

// Load GETs and POSTs if necessary - use references.
if(LOAD_GET) // GETs
	foreach($_GET as $name)
		$$name =& $_GET[$name];
		
if(LOAD_POST) // POSTs
	foreach($_POST as $name)
		$$name =& $_POST[$name];

class page {
	public $error = array();
	public $config = array();
	public $script;
	public $self;
	public $cwd = "";
	
	protected $navbarstring;
	
	public function __construct()
	{
		global $config;
		if(is_array($config))
			foreach($config as $key => $value)
				$this->config[$key] = $value;
		$this->script = $this->get_scriptname();
		$this->cwd = getcwd();
		$this->self = $_SERVER['PHP_SELF'];
	}
	
	public function get_config($key)
	{
		return $this->config[$key]; 
	}
	
	public function set_config($key,$value)
	{
		$this->config[$key] = $value; 
	}
	
	public function __get($key)
	{
		return $this->config[$key]; 
	}
	
	public function __set($key,$value)
	{
		$this->config[$key] = $value; 
	}

	public function error($error)
	{
		$this->error[] = $error;
	}

	public function do_error()
	{
		foreach($this->error as $value)
			print '<div class="error">' . $value . '</div>';
	}

	public function header()
	{
		$page =& $this;
		
		if($_GET[MESSAGE_VARIABLE])
			$this->error($_GET[MESSAGE_VARIABLE]);

		include(INCLUDE_DIR . "header.php");
	}
	
	public function side($inc)
	{
		$page =& $this;
		include SIDE_DIR . $inc . ".php";
	}
	
	public function navbar($title,$pages)
	{
		$elements = array();

		$this->navbarstring = '<div class="navbar">';
		$this->navbarstring .= "$title: ";
		foreach($pages as $page)
		{
			$elem = '<a href="' . $page[1] . '" ';
			if($page[2])
				$elem .= 'style="navbarselected"';
			$elem .= '>' . $page[0] . '</a>';
			$elements[] = $elem;
		}
		$this->navbarstring .= implode(' | ',$elements);
		$this->navbarstring .= '</div>';
	}
	
	public function do_navbar()
	{
		print $this->navbarstring;
	}

	public function footer()
	{
		$page =& $this;

		include INCLUDE_DIR . "footer.php"; 
	}
	
	protected function get_scriptname()
	{
		$spath = $_SERVER['PHP_SELF'];
		$i = strlen($spath);
		for ($j = $i-1;$j>=0;$j--)
			if ($spath[$j] == '/') { break; }
		$justfile = substr($spath,$j+1);
		return $justfile;
	}
}

$page = new page(); // Automatically create a page object.

foreach(unserialize(MODULES) as $module) // Include the modules! In order too.
{
	load_module($module);
}

function load_module($class)
{
	if(class_exists($class))
		return;
	if($depend = $dependencies[$class])
		foreach($depend as $class)
			load_module($class);
	require_once MODULE_DIR . $class . '.php';
	if(!class_exists($class))
		return;
}

function __autoload($module) // Autoload if you forget to include a module.
{
   load_module($module); // Assumes your module file is the same name as the class.
}
?>