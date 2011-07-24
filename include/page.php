<?
require_once $config['maindir'] . 'db.php';

class page {
	public $title;
	public $bodytitle;
	public $side = 1;
	public $titlechar = "|";
	public $err = array('before' => array(),'after' => array());
	public $validate = 0;
	public $scriptname;
	public $maindir = "";
	public $self;
	protected $extrahead = 0;
	protected $extraheadstring = "";
	protected $extrabody = 0;
	protected $extrabodystring = "";
	protected $cwdir = "";
	protected $navbarstring;
	protected $msg404 = array();
	
//	$sideinc if necessary

	function __construct()
	{
		global $config;
		if(is_array($config))
			foreach($config as $key => $value)
				$this->$key = $value;
		$this->scriptname = $this->get_scriptname();
		$this->cwdir = getcwd();
		$this->self = $_SERVER['PHP_SELF'];
	}

	function error($error, $position = 0) // 0 for before, 1 for after
	{
		if($position)
			$this->err['after'][] = $error;
		else
			$this->err['before'][] = $error;
	}
	
	function extrahead($string)
	{
		$this->extrahead = 1;
		$this->extraheadstring .= $string;
	}
	
	function extrabody($string)
	{
		$this->extrabody = 1;
		$this->extrabodystring .= " " . $string;
	}

	function init()
	{
		$page = $this;
		include 'globals.php';
		
		if($_GET['msg'])
			$this->error($_GET['msg'],1);
		if($this->validate)
			require_once 'validate.php';
		$css = array( 'www.digitalitcc.com' => 'itcc',
				'lan.digitalitcc.com' => 'lan',
				'cs.digitalitcc.com' => 'cs',
				'repairs.digitalitcc.com' => 'repairs',
				'www.shsclubs.org' => 'default',
				'csf.shsclubs.org' => 'csf'
			);

		$titles = array( 'www.digitalitcc.com' => 'Digital ITCC',
				'lan.digitalitcc.com' => 'LAN',
				'cs.digitalitcc.com' => 'CounterStrike',
				'repairs.digitalitcc.com' => 'Repairs',
				'www.shsclubs.org' => 'SHS Club Hosting',
				'csf.shsclubs.org' => 'CSF'
			);
	?>
	
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
<title><?=$titles[$_SERVER['HTTP_HOST']]?> <?=$this->titlechar?> <?=$this->title?></title>
<link rel="stylesheet" type="text/css" href="/<?=$css[$_SERVER['HTTP_HOST']]?>.css">
<meta http-equiv="Set-Cookie" content="cookie=set; path=/">
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<?=$extraheadstring?>
</head>
<body<?=$extrabodystring?>>
<? 		include($this->maindir . "inc-header.php");
		print $this->navbarstring;
		if(count($this->err['before']) > 0)
		{
			foreach( $this->err['before'] as $value )
				print "$value<br>\n";
		} ?>
<div class="title"><?=$this->bodytitle?></div>
<div class="body">
		<?
		if(count($this->err['after']) > 0)
		{
			print "<div>\n";
			foreach( $this->err['after'] as $value )
				print "$value<br>\n";
			print "</div>\n";
		}
		?>
		
	<?
	}
	
	function sidebar()
	{
		include 'globals.php';
		
		foreach($this->sideinc as $inc)
		{
			if($first)
				print '<tr><td>&nbsp;</td></tr>';
			include $this->maindir . "inc/$inc.php";
			$first = true;
		}
	}
	
	function navbar($title,$pages) {
		$elements = array();
		$this->navbarstring .= '<div class="navbar">';
		$this->navbarstring .= "$title: ";
		foreach($pages as $page)
		{
			$elem = '<a href="' . $page[1] . '" ';
			if($page[2])
				$elem .= 'style="font-weight: bold"';
			$elem .= '>' . $page[0] . '</a>';
			$elements[] = $elem;
		}
		$this->navbarstring .= implode(' | ',$elements);
		$this->navbarstring .= '</div>';
	}
	
	function javascript($script) {
		$this->script = $script;
	}

	function footer() {
		$page = $this;
		include 'globals.php';
		print "</div>";
		include $this->maindir . "inc-footer.php"; 
		print $this->script; ?>
</body>
</html>
	<?
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
	
	function highlight_file_linenum($data, $funclink = true, $return = false)
	{
		// Init
		$data = explode ('<br />', $data);
		$start = '<span style="color: black;">';
		$end   = '</span>';
		$i = 1;
		$text = '';
	
		// Loop
		foreach ($data as $line) {
			$text .= $start . $i . ' ' . $end .
				str_replace("\n", '', $line) . "<br />\n";
			++$i;
		}
	
		// Optional function linking
		if ($funclink === true) {
			$keyword_col = ini_get('highlight.keyword');
			$manual = 'http://www.php.net/function.';
	
			$text = preg_replace(
				// Match a highlighted keyword
				'~([\w_]+)(\s*</span>)'.
				// Followed by a bracket
				'(\s*<span\s+style="color: ' . $keyword_col . '">\s*\()~m',
				// Replace with a link to the manual
				'<a href="' . $manual . '$1">$1</a>$2$3', $text);
		}
		
		// Return mode
		if ($return === false) {
			echo $text;
		} else {
			return $text;
		}
	}
	
	function error404 ()
	{
		if(count($this->error) < 1)
		{
			chdir(INCLUDE_DIR);
			$this->msg404 = file('404.txt');
			$i = 0;
			foreach($this->msg404 as $line)
			{
				$line = rtrim($line);
				if(preg_match_all("/\%(.+)\%/",$line,$matches,PREG_PATTERN_ORDER))
				{
					foreach($matches[1] as $value)
					{
						$search = quotemeta($value);
						$line = preg_replace("/\%($search)\%/",eval($value),$line) or die("bah");
					}
				}
				$this->msg404[$i] = $line;
				$i++;
			}
		}
		print $this->msg404[rand(0,count($this->msg404)-1)];
		chdir($this->cwdir);
	}
	
	function formout($string) {
		return htmlentities(stripslashes($string));
	}
	
	function desc() {
		return "page";
	}
}

$page = new page();
?>