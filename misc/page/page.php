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
	var $error_array = array();
	var $config = array();
	var $script;
	var $self;
	var $cwd = "";
	
	var $navbarstring;
	
	function page()
	{
		global $config;
		if(is_array($config))
			foreach($config as $key => $value)
				$this->config[$key] = $value;
		$this->script = $this->get_scriptname();
		$this->cwd = getcwd();
		$this->self = $_SERVER['PHP_SELF'];
	}
	
	function get_config($key)
	{
		return $this->config[$key]; 
	}
	
	function set_config($key,$value)
	{
		$this->config[$key] = $value; 
	}
	
	function __get($key)
	{
		return $this->config[$key]; 
	}
	
	function __set($key,$value)
	{
		$this->config[$key] = $value; 
	}

	function error($error)
	{
		if(strlen($error) > 0)
			$this->error_array[] = $error;
	}

	function do_error()
	{
		foreach($this->error_array as $value)
			print '<div class="error">' . $value . '</div>';
	}

	function header()
	{
		foreach($GLOBALS as $variable => $value)
			$$variable = $value;
		
		if($_GET[MESSAGE_VARIABLE])
			$this->error($_GET[MESSAGE_VARIABLE]);

		include(INCLUDE_DIR . "header.php");
	}
	
	function side($inc)
	{
		foreach($GLOBALS as $variable => $value)
			$$variable = $value;
			
		include SIDE_DIR . $inc . ".php";
	}
	
	function navbar($title,$pages,$format = 0)
	{
		$elements = array();

		if(!$format)
		{
			$format = new navbar_template;
			$format->head = '<div class="navbar">{NAVBAR_TITLE}: ';
			$format->element = '<a href="{ELEMENT_URL}">{ELEMENT_TITLE}</a>';
			$format->selectedelement = '<a href="{ELEMENT_URL}" class="navbar_selected">{ELEMENT_TITLE}</a>';
			$format->separator = ' | ';
			$format->footer = '</div>';
		}
		
		$temp = $format;

		foreach($temp as $key => $value)
		{
			$temp->$key = preg_replace("/{NAVBAR_TITLE}/",$title);
		}
		
		$this->navbarstring = $temp->header;
		foreach($pages as $page)
		{
			if($page[2])
				$elem = $temp->selectedelement;
			else
				$elem = $temp->element;
			$elem = preg_replace("/{ELEMENT_URL}/",$page[1]);
			$elem = preg_replace("/{ELEMENT_TITLE}/",$page[0]);
			$elements[] = $elem;
		}
		$this->navbarstring .= implode($temp->separator,$elements);
		$this->navbarstring .= $temp->footer;
	}
	
	function do_navbar()
	{
		print $this->navbarstring;
	}

	function footer()
	{
		foreach($GLOBALS as $variable => $value)
			$$variable = $value;
			
		include INCLUDE_DIR . "footer.php"; 
	}
	
	function get_scriptname()
	{
		$spath = $_SERVER['PHP_SELF'];
		$i = strlen($spath);
		for ($j = $i-1;$j>=0;$j--)
			if ($spath[$j] == '/') { break; }
		$justfile = substr($spath,$j+1);
		return $justfile;
	}
}

class navbar_template
{
	var $head;
	var $element;
	var $selected_element;
	var $separator;
	var $footer;
}

$page = new page(); // Automatically create a page object.

foreach(unserialize(MODULES) as $module) // Include the modules! In order too.
{
	require_once MODULE_DIR . $module . ".php";
}

function __autoload($module) // Autoload if you forget to include a module.
{
	require_once MODULE_DIR . $module . ".php"; // Assumes your module file is the same name as the class.
}
?>
