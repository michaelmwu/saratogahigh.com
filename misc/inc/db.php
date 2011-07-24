<?
$loggedin = false;

define('C_SEMESTER', 'S1');
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

// Verify login
if(is_numeric($_COOKIE['UNO']))
{
	// Grab user record
	$tu = mysql_query('SELECT * FROM USER_LIST WHERE USER_ID=' . $_COOKIE['UNO'] . ' AND USER_UNAME !=\'\' AND USER_UNAME=\'' . $_COOKIE['UN'] . '\' AND USER_PW=\'' . addslashes($_COOKIE['PW']) . '\'') or die('User query failed');

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

function shorten_string($text, $length, $symbol = "...")
{
	$length_text = strlen($text);
	$length_symbol = strlen($symbol);

	if($length_text <= $length || $length_text <= $length_symbol || $length <= $length_symbol)
		return($text);
	else
		return(substr($text, 0, $length - $length_symbol) . $symbol);
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

function email($from, $email,$subject,$msg)
{
		mail($email, $subject, $msg
		, "From: $from@saratogahigh.com\r\n"
		. "Reply-To: $from@saratogahigh.com\r\n"
		. "X-Mailer: PHP/" . phpversion()) or die("Mail not sent!");
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

function cleanup_boxes($boxes)
{
	global $isstudent, $isparent, $isvalidated, $corrupt, $isadmin, $isteacher;
	
	$used = array();

	if(gettype($boxes) == "array")
	{
		foreach($boxes as $column)
		{
			if(gettype($column) == "array")
			{
				foreach($column as $box)
				{
					if(gettype($box) != "integer")
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
						0 => array( 26, 4, 6 ),
						1 => array( 2, 1, 3 ),
						2 => array( 5 ),
						3 => array(   )
					);
		else
			$boxes = array(
						0 => array( 26, 4, 6 ),
						1 => array( 2, 1 ),
						2 => array( 5 ),
						3 => array(   )
					);
		if($isstaff)
			array_unshift( $boxes[2], 28 );
		if($isstudent || $isteacher)
			array_unshift( $boxes[1], 29 );

	}

	for($i = 0; $i < count($boxes); $i++)
	{
		for( $j = 0; $j < count($boxes[$i]); $j++ )
		{
			if( ($boxes[$i][$j] == 3 && (!$isstudent && !$isparent))
				|| ($boxes[$i][$j] == 5 && !$isvalidated)
				|| (($boxes[$i][$j] == 13 || $boxes[$i][$j] == 28) && !$isadmin)
				|| ($boxes[$i][$j] == 29 || !($isstudent || $isteacher))
				|| in_array($boxes[$i][$j],$used) )
			{
				$boxes[$i] = array_splice( $boxes[$i], $j, 1 );
				$j--;
			}
				
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

include '/var/www/html' . '/magpierss/rss_fetch.inc';
?>