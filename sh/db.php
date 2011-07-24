<?

$link = mysql_connect('127.0.0.1','saratogahigh','k142857w') or die('Could not connect to sh.com database');
mysql_select_db('saratogahigh_com_-_main') or die('Could not select database');

$loggedin = false;

define('DNAME', 'www.saratogahigh.com');
define('C_SEMESTER', 'S2');
define('C_SCHOOLYEAR', 2006);
define('FRONT_ENABLED', true);
define('PARENT_ENABLED', true);
define('STUDENT_ENABLED', true);
define('TEACHER_ENABLED', true);
define('ALUM_ENABLED', true);
define('SITE_ENABLED', true); // set to FALSE to lock out everyone except admins
define('SITE_ACTIVE', true);  // set to TRUE during the school year
define('TIME_OFFSET', 3);     // Time zones server ahead of Saratoga
define('TIME_FORMAT', 'j M Y g:i a');
define('TIME_FORMAT_SQL', 'Y-m-d H:i:s');
define('TIME_FORMAT_IDF', 'Ymd');
define('CURRENT_TIME', mktime(date('G') - TIME_OFFSET, date('i'), date('s'), date('n'), date('j'), date('Y')));
define('USERNAME_REGEX', '^([-_a-zA-Z0-9]{1,32})$');

define('SELL_IMAGE_SIZE', 51200);

if( $_SERVER['HTTP_HOST'] == "saratogahigh.com" )
{
	header('Location: http://' . DNAME . $_SERVER['REQUEST_URI']);
}

// Verify login
if(is_numeric($_COOKIE['UNO']))
{
	// Grab user record
	$tu = mysql_query('SELECT * FROM USER_LIST WHERE USER_ID=' . $_COOKIE['UNO'] . ' AND USER_UNAME !=\'\' AND USER_UNAME=\'' . $_COOKIE['UN'] . '\' AND USER_PW=\'' . addslashes($_COOKIE['PW']) . '\'') or die('User query failed: ' . mysql_error());

	if($userR = mysql_fetch_array($tu, MYSQL_ASSOC))
	{
		// Update view log and nullify activation code
		mysql_query('UPDATE USER_LIST SET USER_ACTIVATION=Null, USER_LASTLOGIN="' . date(TIME_FORMAT_SQL, CURRENT_TIME) . '" WHERE USER_ID=' . $userR['USER_ID']) or die('Timestamp update failed');

		$loggedin = SITE_ENABLED || $userR['USER_STATUS'] > 0 || (IsParent($line['USER_GR']) && PARENT_ENABLED) || (IsTeacher($line['USER_GR']) && TEACHER_ENABLED) || (IsAlum($line['USER_GR']) && ALUM_ENABLED) || (IsStudent($line['USER_GR']) && STUDENT_ENABLED);

		$cmo = date('n', CURRENT_TIME);
		$cyr = date('Y', CURRENT_TIME);
		if($loggedin)
			$cusr = $userR['USER_ID'];
		else
			$cusr = 'null';

		mysql_query('INSERT INTO LOG_LIST VALUES (\'\', ' . $cmo . ', ' . $cyr . ',' . $cusr . ', "' . date(TIME_FORMAT_SQL, CURRENT_TIME) . '", "' .
$_SERVER['SCRIPT_URL'] . '", "' . $_SERVER['QUERY_STRING'] . '", "' . $_SERVER['HTTP_USER_AGENT'] . '", "' . $_SERVER['REMOTE_ADDR'] . '")') or $FAIL_logupdate =
true;

		// Set cookie and user statistics
		if($loggedin)
		{
			if($userR['USER_STATUS'] > 0 || !is_null($line['USER_TEACHERTAG']))
				$timeout = 0;
			else
				$timeout = time() + 864000;

			setcookie("UN", $_COOKIE["UN"], $timeout, "/");
			setcookie("UNO", $_COOKIE["UNO"], $timeout, "/");
			setcookie("PW", $_COOKIE["PW"], $timeout, "/");

			$userid        = $userR['USER_ID'];
			$usertag       = $userR['USER_TEACHERTAG'];
			$isalum        = IsAlum($userR['USER_GR']);
			$isstudent     = IsStudent($userR['USER_GR']);
			$isfaculty     = IsTeacher($userR['USER_GR']);
			$isteacher     = !is_null($userR['USER_TEACHERTAG']);
			$isparent      = IsParent($userR['USER_GR']);
			$issuperparent = ($userR['USER_SUPERPARENT'] == 1);
			$isvalidated   = ($userR['USER_VALIDATED'] == 1);
			$isprog        = ($userR['USER_STATUS'] > 2);
			$isadmin       = ($userR['USER_STATUS'] > 1);
			$isstaff       = ($userR['USER_STATUS'] > 0);
		}
	}
	else
	{
		$cmo = date('n', CURRENT_TIME);
		$cyr = date('Y', CURRENT_TIME);
		mysql_query('INSERT INTO LOG_LIST VALUES (\'\', ' . $cmo . ', ' . $cyr . ',NULL, "' . date(TIME_FORMAT_SQL, CURRENT_TIME) . '", "' . $_SERVER['SCRIPT_URL'] . '", "' . $_SERVER['QUERY_STRING'] . '", "' . $_SERVER['HTTP_USER_AGENT'] . '", "' . $_SERVER['REMOTE_ADDR'] . '")') or $FAIL_logupdate = true;
	}

	mysql_free_result($tu);
}

function mktimeoffset($dh, $di, $ds, $dm, $dd, $dy)
{
	return mktime(date('G', CURRENT_TIME) + $dh, date('i', CURRENT_TIME) + $di, date('s', CURRENT_TIME) + $ds, date('n', CURRENT_TIME) + $dm, date('j', CURRENT_TIME) + $dd, date('Y', CURRENT_TIME) + $dy);
}

function forceLogin()
{
	header('location: /login.php?reqd=true&next=' . urlencode($_SERVER['REQUEST_URI']));
	die();
	return;
}

function printuserlink($xfullname, $xuserid, $userid, $viewuser)
{
	if($xuserid == $viewuser)
		print $xfullname;
	else
	{
		print '<a style="';
		if($xuserid == $userid)
			print 'color: #006000';
		print '" href="/directory/?id=' . $xuserid . '">' . $xfullname . '</a>';
	}
}

function GradeInt($gin)
{
	return (C_SCHOOLYEAR - $gin + 12);
}

function GradePrint($gin)
{
//	if(C_SCHOOLYEAR <= $gin && $gin <= C_SCHOOLYEAR + 3)
        if(C_SCHOOLYEAR <= $gin)
		return 'Grade ' . (C_SCHOOLYEAR - $gin + 12);
	else if($gin == 0)
		return 'Faculty';
	else if($gin == 1)
		return 'Parent';
	else
		return 'Alum ' . $gin;
}

function StatusPrint($statusin)
{
	if($statusin == 3)
		return 'Programmer';
	else if($statusin == 2)
		return 'Admin';
	else if($statusin == 1)
		return 'Staff';
}

function IsStudent($gin)
{
	if(C_SCHOOLYEAR <= $gin && $gin <= C_SCHOOLYEAR + 3)
		return true;
	else
		return false;
}

function IsAlum($gin)
{
	if(1 < $gin && $gin < C_SCHOOLYEAR)
		return true;
	else
		return false;
}

function IsTeacher($gin)
{
	return $gin == 0;
}

function IsParent($gin)
{
	return $gin == 1;
}

function TooManyNotes($userid)
{
	$rsnumnotes = mysql_query('SELECT COUNT(*) FROM NOTEPAGE_LIST WHERE NOTEPAGE_OWNER=' . $userid);
	$numnotes = mysql_fetch_array($rsnumnotes, MYSQL_ASSOC);

	return $numnotes['COUNT(*)'] >= 50;
}

function shorten_string($text, $length, $symbol = "...", $html = false)
{
	$length_text = strlen($text);
	$length_symbol = strlen($symbol);

	if($html)
	{
		$html_text = preg_replace("/<.+?>/","",$text);
		$real_length = $length_text;
		$length_text = strlen($html_text);
	}

	if($length_text <= $length || $length_text <= $length_symbol || $length <= $length_symbol)
		return($text);
	else
	{
		if($html)
		{
			$j = 0;
			$tag_state = false;
			for($i=0;$i < $length && $i < $real_length;$i++)
			{
				if(!$tag_state && $text{$i} == "<")
					$tag_state = true;
					
				if($tag_state && $text{$i} == ">")
					$tag_state = false;
			
				if(!$tag_state)
					$j++;
			}
			
			for($i=$j;$j - $i < $length_symbol;$i--)
			{
				if($text{$i-1} == ">")
					break;
			}
			
			return(substr($text, 0, $i) . $symbol);
		}
		return(substr($text, 0, $length - $length_symbol) . $symbol);
	}
}

function urls_clickable($string)
{
	for($n = 0; $n < strlen($string); $n++)
	{
		if(strtolower($string[$n]) == 'h')
		{
			if(!strcmp("http://", strtolower($string[$n]) . strtolower($string[$n+1]) . strtolower($string[$n+2]) . strtolower($string[$n+3]) . $string[$n+4] . $string[$n+5] . $string[$n+6]))
			{
				$startpos = $n;
				while($n < strlen($string) && eregi('[a-z0-9;.:?/~_&=%+"\\\'-]', $string[$n]))
					$n++;
				if(!eregi('[a-z0-9/~_&=%+"\\\'-]', $string[$n-1]))
					$n--;

				if($string[$n] == "/")
					$p = 1;
				else
					$p = 0;

				$link = substr($string, $startpos, $n - $startpos + $p);
				$link = $link;
				$string_tmp = $string;
				$string = substr($string_tmp, 0, $startpos) . "<a href=\"$link\">$link</a>" . substr($string_tmp, $n + $p, strlen($string_tmp));
				$n = $n + 15;
			}
		}
	}
	return $string;
}

function compact_string($text)
{
	return ereg_replace(' +', ' ', $text);
}

function nl2slash($txt)
{
	return preg_replace("/(\015\012)|(\015)|(\012)/"," / ", $txt);
}

function makedigest($txt)
{
	return shorten_string(nl2slash($txt), 120);
}

function printable($txt)
{
	return urls_clickable(nl2br(htmlspecialchars($txt)));
}

function printablenolinks($txt)
{
	return nl2br(utf8Encode($txt));
}

function PrintView($url)
{
	return '<div class="yellowbox" style="margin-top: 10px; margin-bottom: 10px; font-size: large">Make sure to go to <a href="' . $url . '" target="_blank">Print View</a> before printing this page.</div>';
}

function utf8Encode ($source)
{
	$utf8Str = '';
	$entityArray = explode ("&#", $source);
	$size = count ($entityArray);
	for ($i = 0; $i < $size; $i++) {
	$subStr = $entityArray[$i];
	$nonEntity = strstr ($subStr, ';');
	if ($nonEntity !== false) {
	$unicode = intval (substr ($subStr, 0, (strpos ($subStr, ';') + 1)));
	// determine how many chars are needed to reprsent this unicode char
	if ($unicode < 128) {
	$utf8Substring = chr ($unicode);
	}
	else if ($unicode >= 128 && $unicode < 2048) {
	$binVal = str_pad (decbin ($unicode), 11, "0", STR_PAD_LEFT);
	$binPart1 = substr ($binVal, 0, 5);
	$binPart2 = substr ($binVal, 5);

	$char1 = chr (192 + bindec ($binPart1));
	$char2 = chr (128 + bindec ($binPart2));
	$utf8Substring = $char1 . $char2;
	}
	else if ($unicode >= 2048 && $unicode < 65536) {
	$binVal = str_pad (decbin ($unicode), 16, "0", STR_PAD_LEFT);
	$binPart1 = substr ($binVal, 0, 4);
	$binPart2 = substr ($binVal, 4, 6);
	$binPart3 = substr ($binVal, 10);

	$char1 = chr (224 + bindec ($binPart1));
	$char2 = chr (128 + bindec ($binPart2));
	$char3 = chr (128 + bindec ($binPart3));
	$utf8Substring = $char1 . $char2 . $char3;
	}
	else {
	$binVal = str_pad (decbin ($unicode), 21, "0", STR_PAD_LEFT);
	$binPart1 = substr ($binVal, 0, 3);
	$binPart2 = substr ($binVal, 3, 6);
	$binPart3 = substr ($binVal, 9, 6);
	$binPart4 = substr ($binVal, 15);

	$char1 = chr (240 + bindec ($binPart1));
	$char2 = chr (128 + bindec ($binPart2));
	$char3 = chr (128 + bindec ($binPart3));
	$char4 = chr (128 + bindec ($binPart4));
	$utf8Substring = $char1 . $char2 . $char3 . $char4;
	}

	if (strlen ($nonEntity) > 1)
	$nonEntity = substr ($nonEntity, 1); // chop the first char (';')
	else
	$nonEntity = '';

	$utf8Str .= $utf8Substring . $nonEntity;
	}
	else {
	$utf8Str .= $subStr;
	}
	}

	return $utf8Str;
}

function is_id($stringin)
{
	return ereg('^[1-9][0-9]*$', $stringin);
}

class ActivationCodeMaker
{
    function RandomSymbol()
    {
    	$x = mt_rand(0,3);

    	$digits = "23456789";
    	$letters = "abcdefghijkmnpqrstuvwxyz";

    	if($x == 0 || $x == 3)
    		return $digits{mt_rand(0,7)};
    	else
    		return $letters{mt_rand(0,23)}; // no l or o
    }

    function RandomBlock($l)
    {
    	$res = '';
    	for($i = 0; $i < $l; $i++)
    		$res .= $this->RandomSymbol();

    	return $res;
    }

    function RandomSig($s)
    {
    	$res = '';
    	for($i = 0; $i < strlen($s); $i++)
    	{
    		if($i > 0)
    			$res .= ' ';

    		$res .= $this->RandomBlock($s{$i});
    	}

    	return $res;
    }

    function RandomActivationCode()
    {
        switch(mt_rand(0, 5))
        {
        	case 0: $type = '344'; break;
        	case 1: $type = '434'; break;
        	case 2: $type = '443'; break;
        	case 3: $type = '533'; break;
        	case 4: $type = '353'; break;
        	case 5: $type = '355'; break;
        }
    	// return $this->RandomSig($type);
	return $this->RandomBlock(16);
    }
}

function NewActivationCode()
{
	$acmaker = new ActivationCodeMaker();
    return $acmaker->RandomActivationCode();
}

function email($config)
{
	$headers = "MIME-Version: 1.0\n";
	
	foreach( array( $config['from'], $config['cc'], $config['bcc']) as $scan)
	{
		if(preg_match("/[\r\n]/",$scan))
			return false;
	}
	
	$headers .= "From: " . $config['from'] . "\r\n"
	. "Reply-To: " . $config['from'] . "\r\n"
	. "X-Mailer: PHP/" . phpversion() . "\r\n";
	
	if(strlen($config['cc']) > 0)
	{
		$headers .= "Cc: " . $config['cc'] . "\r\n";
	}
	
	if(strlen($config['bcc']) > 0)
	{
		$headers .= "Bcc: " . $config['bcc'] . "\r\n";
	}
	
	if(!mail($config['to'], $config['subject'], $config['message'], $headers))
		return false;
	return true;
}

function emailvalidation($email)
{
	return preg_match("/^[\w\.]+?@[\w]+?(\.[\w]+?)+$/", $email);
}

function get_scriptname()
{
   $spath = $_SERVER['PHP_SELF'];
   $i = strlen($spath);
   for ($j = $i-1;$j>=0;$j--)
       { if ($spath[$j] == '/') { break; } }
   $justfile = substr($spath,$j+1);
   return $justfile;
}

function human_file_size($size) // itsacon@itsacon.net off php.net, thanks!
{
   $filesizename = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
   return round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . $filesizename[$i];
}

function random_string($min,$max)
{
	$chars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_-";
	$string = "";
	$length = mt_rand($min,$max);
	for($i = 0; $i < $length; $i++)
	{
		$string .= $chars{mt_rand(0,strlen($chars)-1)};
	}
	return $string;
}

include_once 'inc-xml.php';

class form
{
	function formout($string)
	{
		return htmlentities(stripslashes($string));
	}
}

//michael's object-oriented php base class

class element
{
	var $form;

	var $type;

	var $class = array();

	var $config = array( 'owncheck' => true, 'options' => array() );

	function __get($key)
	{
		return $this->config[$key];
	}

	function __set($key,$value)
	{
		$this->config[$key] = $value;
	}

	function add_check($bit)
	{
		$this->req |= (int)$bit;
	}

	function add_class($class)
	{
		$this->class[] = $class;
	}

	function do_class()
	{
//		if($this->form->check_error($this))
//			$this->class[] = FORM_ERROR;
		return implode(" ",$this->class);
	}

	function required()
	{
		return strlen($_POST[$this->name]) > 0;
	}

	function numeric()
	{
		return is_numeric($_POST[$this->name]);
	}

	function two()
	{
		return preg_match("/^\d{2}$/",$_POST[$this->name]);
	}

	function four()
	{
		return preg_match("/^\d{4}$/",$_POST[$this->name]);
	}

	function phone()
	{
		$phone = $_POST[$this->name];
        if(empty($num))
            return false;

   		preg_replace("/[\s\(\)\-\+]/","",$phone);
        if(!preg_match("/^\d+$/",$phone) || (strlen($phone) ) < 7 || strlen($phone) > 13 )
            return false;

        return true;
	}
}

class input extends element
{
	var $type = 'input';
	var $maxlength;

	function element_print($return = false)
	{
		$string = '<input name="' . $this->name . '" type="text" class="' . $this->do_class() . '" ';
		if(strlen($this->maxlength) > 0)
			$string .= 'maxlength="' . $this->maxlength . '" ';
		$string .= 'style="' . $this->css . '" value="' . (isset($_POST[$this->name]) ? form::formout($_POST[$this->name]) : $this->value) . '">';
		if(!$return)
			print $string;
		else
			return $string;
	}
}

class password extends element
{
	var $type = 'password';

	function element_print($return = false)
	{
		$string = '<input name="' . $this->name . '" type="password" class="' . $this->do_class() . '" ';
		if(strlen($this->maxlength) > 0)
			$string .= 'maxlength="' . $this->maxlength . '" ';
		$string .= 'style="' . $this->css . '" value="' . (isset($_POST[$this->name]) ? form::formout($_POST[$this->name]) : $this->value) . '">';
		if(!$return)
			print $string;
		else
			return $string;
	}
}

class file extends element
{
	var $type = 'file';

	function element_print($return = false)
	{
		$string = '<input name="' . $this->name . '" type="file" class="' . $this->do_class() . '" ';
		if(strlen($this->maxlength) > 0)
			$string .= 'maxlength="' . $this->maxlength . '" ';
		$string .= 'style="' . $this->css . '" value="' . (isset($_POST[$this->name]) ? form::formout($_POST[$this->name]) : $this->value) . '">';
		if(!$return)
			print $string;
		else
			return $string;
	}

	function required()
	{
		return isset($_POST[$this->name]);
	}

	function is_uploaded()
	{
		return isset($_FILES[$this->name]);
	}

	function temp_file()
	{
		return $_FILES[$this->name]['tmp_file'];
	}

	function temp_filehandle()
	{
		return fopen($_FILES[$this->name]['tmp_file'],'r');
	}

	function temp_filecontents()
	{
		return fread( fopen($_FILES[$this->name]['tmp_file'],'r'), $_FILES['userfile']['size'] );
	}

	function upload_error()
	{
		return $_FILES[$this->name]['error'];
	}
}

class check extends element
{
	var $type = 'check';

	function element_print($return = false)
	{
		$string = '<input name="' . $this->name . '" type="checkbox" class="' . $this->do_class() . '" style="' . $this->css . '" class="' . $this->class . '" ' . ($this->selected?'checked':'') . '>';
		if(!$return)
			print $string;
		else
			return $string;
	}

	function required()
	{
		return 1;
	}
}

class text extends element
{
	var $type = 'text';

	function element_print($return = false)
	{
		$string = '<textarea name="' . $this->name . '" rows="' . $this->row . '" cols="' . $this->col . '" class="' . $this->do_class() . '" style="' . $this->css . '">' . (isset($_POST[$this->name]) ? form::formout($_POST[$this->name]) : $this->value) . '</textarea>';
		if(!$return)
			print $string;
		else
			return $string;
	}
}

class radio extends element
{
	var $type = 'radio';
	var $options = array();

	function add_option($value,$label,$selected = 0)
	{
		$this->options[] = array('value' => $value, 'label' => $label, 'selected' => $selected);
	}

	function element_print($return = false)
	{
		$string = "";
		foreach($this->options as $values)
		{
			$string .= '<label class="' . $this->do_class() . '">' . $values['label'] . '<input type="radio" class="' . $this->do_class() . '" name="' . $this->name . '" style="' . $this->css . '" value="' . form::formout($values['value']) . '" ';
			if(!isset($_POST[$this->name]) && $values['selected'])
				$string .= 'selected';
			else if($isset($_POST[$this->name]) && $_POST[$this->name] == $value['value'])
				$string .= 'selected';
			$string .= "></label>\n";
		}
		if(!$return)
			print $string;
		else
			return $string;
	}

	function required()
	{
		return isset($_POST[$this->name]);
	}
}

class select extends element
{
	var $type = 'select';
	var $options = array();

	function add_option($value,$label,$selected = 0)
	{
		$this->options[] = array('value' => $value, 'label' => $label, 'selected' => $selected);
	}

	function element_print($return = false)
	{
		$string = '<select name="' . $this->name . '" class="' . $this->do_class() . '" style="' . $this->css . '">' . "\n";
		foreach($this->options as $values)
		{
			$string .= '<option value="' . form::formout($values['value']) . '"';
			if(!isset($_POST[$this->name]) && $values['selected'])
				$string .= ' selected';
			else if(isset($_POST[$this->name]) && $_POST[$this->name] == $values['value'])
				$string .= ' selected';

			$string .= '>' . form::formout($values['label']) . "</option>\n";
		}
		$string .= "</select>\n";
		if(!$return)
			print $string;
		else
			return $string;
	}

	function required()
	{
		return isset($_POST[$this->name]);
	}
}

function cleanup_boxes($boxes)
{
	global $isstudent, $isparent, $isvalidated, $corrupt, $isadmin, $isstaff, $isprog;

	$used = array();
	
	$frontbox_array = array();
	
	$frontboxR = db::get_prefix_result("FRONTBOX","1");
	while($row = db::fetch_row($frontboxR))
		$frontbox_array[] = $row['FRONTBOX_ID'];
	mysql_free_result($frontboxR);

	if(gettype($boxes) == "array")
	{
		foreach($boxes as $column)
		{
			if(gettype($column) == "array")
			{
				foreach($column as $box)
				{
					if(!is_numeric($box) && !preg_match("/^c\d+$/i",$box))
						$corrupt = true;
				}
			}
			else
				$corrupt = true;
		}
	}
	else
		$corrupt = true;
		
	if(count($boxes) == 0 || $corrupt)
	{
		if($isstudent || $isparent)
			$boxes = array(
						0 => array( 29, 4 ),
						1 => array( 2, 1, 3 ),
						2 => array( 5, 6 ),
						3 => array( )
					);
		else
			$boxes = array(
						0 => array( 29, 4 ),
						1 => array( 2, 1 ),
						2 => array( 5, 6 ),
						3 => array( )
					);
		if($isstaff)
			$boxes[2] = array( 28, 5, 6 );
	}

	$icount = count($boxes);

	for($i = 0; $i < $icount; $i++)
	{
		$jcount = count($boxes[$i]);
		for( $j = 0; $j < $jcount; $j++ )
		{
			if( ($boxes[$i][$j] == 3 && (!$isstudent && !$isparent))
				|| ($boxes[$i][$j] == 5 && !$isvalidated)
				|| (($boxes[$i][$j] == 13 || $boxes[$i][$j] == 28) && !$isstaff)
				|| in_array($boxes[$i][$j],$used)
				|| (!preg_match("/^c\d+$/i",$boxes[$i][$j]) && !in_array($boxes[$i][$j],$frontbox_array)) )
			{
				array_splice( $boxes[$i], $j, 1 );
				$j--;
				$jcount--;
			}
			else if(!in_array($boxes[$i][$j],$used))
				array_push($used, $boxes[$i][$j]);
		}
	}

	return $boxes;
}

function box_exists($boxes,$box)
{
	for($i = 0; $i < count($boxes); $i++)
	{
		if(in_array($box,$boxes[$i]))
			return true;
	}
	return false;
}


function fputcsv($handle, $row, $fd=',', $quot='"')
{
   $str='';
   foreach ($row as $cell)
   {
     $cell = str_replace($quot, $quot.$quot, $cell);
        
     if (strchr($cell, $fd) !== FALSE || strchr($cell, $quot) !== FALSE || strchr($cell, "\n") !== FALSE)
     {
         $str .= $quot.$cell.$quot.$fd;
     }
     else
     {
         $str .= $cell.$fd;
     }
   }

   fputs($handle, substr($str, 0, -1)."\n");

   return strlen($str);
}

function printcsv($row, $fd=',', $quot='"')
{
   $str='';
   foreach ($row as $cell)
   {
     $cell = str_replace($quot, $quot.$quot, $cell);
        
     $str .= $quot.$cell.$quot.$fd;
   }

   print substr($str, 0, -1)."\n";

   return strlen($str);
}

	class db {
		function get_prefix_id($table,$id,$extrawhere = "",$extraselect = "",$joins = "")
		{
			$statement = "SELECT *";
			if(strlen($extraselect) > 0)
				$statment .= ",$extraselect";
			$statement .= " FROM $table" . "_LIST";
			if(strlen($joins) > 0)
				$statement .= " $joins";
			$statement .=" WHERE " . $table . "_ID='" . $id . "'";
			if(strlen($extrawhere > 0))
				$statement .= " AND $extrawhere";
			$row = mysql_query($statement) or die($statement . "<br>" . mysql_error());
			return db::fetch_row( $row );
		}
	
		function get_prefix_row($table,$where,$extraselect = "",$joins = "")
		{
			return db::get_row($table . "_LIST",$where,$extraselect,$joins);
		}
		
		function get_row($table,$where,$extraselect = "",$joins = ""){
			$statement = "SELECT *";
			if(strlen($extraselect) > 0)
				$statment .= ",$extraselect";
			$statement .= " FROM $table";
			if(strlen($joins) > 0)
				$statement .= " $joins";
			$statement .= " WHERE $where";
			$row = mysql_query($statement) or die($statement . "<br>" . mysql_error());
			return db::fetch_row( $row );
		}
	
		function get_prefix_result($table,$where,$order="",$extraselect = "",$joins = "")
		{
			return db::get_result($table . "_LIST",$where,$order,$extraselect,$joins);
		}
		
		function get_result($table,$where,$order="",$extraselect = "",$joins = "")
		{
			$statement = "SELECT *";
			if(strlen($extraselect) > 0)
				$statment .= ",$extraselect";
			$statement .= " FROM $table";
			if(strlen($joins) > 0)
				$statement .= " $joins";
			$statement .= " WHERE $where";
			if(strlen($order) > 0)
				$statement .= " ORDER BY $order";
			$result = mysql_query($statement) or die($statement . "<br>" . mysql_error());
			return $result;
		}
		
		function fetch_row($result) {
			return mysql_fetch_array( $result, MYSQL_ASSOC );
		}
	
		function prefix_update($table,$values,$where,$extraset = "") 
		{
			db::update($table . "_LIST",$values,$where,$extraset);
		}
		
		function update($table,$values,$where,$extraset = "") 
		{
			$statement = "SELECT * FROM $table WHERE $where";
			$result = mysql_query($statement) or die($statement . "<br>" . mysql_error());
			$update = db::fetch_row( $result );

			$statement = ($update ? "UPDATE" : "INSERT INTO") . " $table SET ";
			$first = true;
			foreach($values as $key => $value)
			{
				if(!$first)
					$statement .= ",";
				$statement .= "$key = '$value'";
				$first = false;
			}
			if(strlen($extraset) > 0)
				$statement .= ", $extraset";

			if($update)
				$statement .= " WHERE $where";
			mysql_query($statement) or die($statement . "<br>" . mysql_error());
			
			return ($update ? 0 : mysql_insert_id());
		}
		
		function prefix_delete($table,$where)
		{
			db::delete($table . "_LIST",$where);
		}
		
		function delete($table,$where)
		{
			$statement = "DELETE FROM $table WHERE $where";
			mysql_query($statement) or die($statement . "<br>" . mysql_error());
		}
	}

include 'magpierss/rss_fetch.inc';
?>
