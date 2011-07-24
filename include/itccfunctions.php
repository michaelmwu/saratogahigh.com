<?
// Connecting, selecting database
$link = mysql_connect("localhost:3306", "digitalitcc", "ehp0d1ng")
   or die("Could not connect : " . mysql_error());

mysql_select_db("digitalitcc_db") or die("Could not select database");

define("_BBCLONE_DIR", "/var/www/www/htdocs/bbclone/");
define("COUNTER", _BBCLONE_DIR."mark_page.php");
if (is_readable(COUNTER)) include_once(COUNTER);

require_once 'datetime.php';
require_once INCLUDE_DIR . 'login.php';
require_once INCLUDE_DIR . 'db.php';

function __autoload($class_name) {
   require_once $class_name . '.php';
}

/*
function timeYMD ( $now = 0 )
{
	if(!$now)
		$now = time();
	return date("Ymd",$now);
}

function printjMY ( $now = 0 )
{
	if(!$now)
		$now = time();
	return date("j M, Y",$now);
}

function datetime ( $now = 0 )
{
	if(!$now)
		$now = time();
	return date("Y-m-d H:i:s",$now);
}

function fulldatetime ( $now = 0 )
{
	if(!$now)
		$now = time();
	return date("l, F j, Y g:i A",$now);
}

function newsprint ($newsid, $comments=1)
{
	global $isadmin;

	$newsq = mysql_query('SELECT NEWS_LIST.*, PHPBB_users.username, YEAR(NEWS_TS) FROM NEWS_LIST LEFT JOIN PHPBB_users ON NEWS_USER=user_id WHERE NEWS_ID=' . $newsid);
	if($news = mysql_fetch_array($newsq, MYSQL_ASSOC))
	{
		print '<p><span class="subtitle">' . stripslashes($news['NEWS_TITLE']) . '</span>';

		print '<br><br>' . nl2br(stripslashes($news['NEWS_TEXT'])) . '</p>';

		print '<table border="0" style="border-top: 1px dotted #f2f3f3; margin-right: 5px; width: 100%" cellspacing="0"><tr><td style="width: 220px; font-size: 12px;">';
		print printjMY(strtotime($news['NEWS_TS'])) . ' by <span class="cat">' . (strlen($news['NEWS_USERNAME'])<1?$news['username']:$news['NEWS_USERNAME']) . '</span></td>';
		if($comments)
		{
			print '<td style="text-align: right; font-size: 12px;">';
			print '<a href="comments.php?news=' . $news['NEWS_ID'] . '" style="margin-left: 0px;">Comments (';
			$cq = mysql_query('SELECT * FROM COMMENT_LIST WHERE COMMENT_NEWS=' . $news['NEWS_ID']);
			print mysql_num_rows($cq) . ')</a></td></tr>';
		}
		if($isadmin)
			print '<tr><td colspan="2" style="text-align: center; font-size: 12px;"><a href="index.php?mode=edit&amp;id=' . $news['NEWS_ID'] . '&amp;year=' . $news['YEAR(NEWS_TS)'] . '">Edit</a>&nbsp;&nbsp;<a href="#" onClick="delconfirm(\'Really delete this news item?\', ' . $news['NEWS_ID'] . ', ' . $news['YEAR(NEWS_TS)'] . ');">Delete</a></td></tr>';
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

function printM( $month = 0 )
{
	if(!$month)
		$month = date("m");
	switch($month)
	{
		case 1:
			return "January";
		case 2:
			return "February";
		case 3:
			return "March";
		case 4:
			return "April";
		case 5:
			return "May";
		case 6:
			return "June";
		case 7:
			return "July";
		case 8:
			return "August";
		case 9:
			return "September";
		case 10:
			return "October";
		case 11:
			return "November";
		case 12:
			return "December";
	}
}

function login($user,$pass,$remember=0)
{
	$uq = mysql_query("SELECT * FROM PHPBB_users WHERE username='" . addslashes($user) . "' AND user_password=MD5('" . addslashes($pass) . "')");
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
	die();
	return;
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
	$error = array('Every once in a while, a web page will, you know, run away from home. This bad webpage apparently did so.',
		'Call an AMBER Alert! This web page is missing!',
		'You blew up our server, didn\'t you? Huh? HUH?',
		'I think that web page went on a bathroom break.',
		'I looked, and looked, and looked, and now my eyes hurt, but I still couldn\'t find that webpage.',
		'Oh that web page? It\'s out back doing something.',
		'As times go on, things inevitably change. This web page evidently followed the flow of time.',
		'Hmm, we couldn\'t find that web page, but we found this on his door: "Out on a Lunch Break."',
		'Sooo not found it\'s scary.',
		'Looks like this web page has been abducted by aliens.',
		'Arrowed! I mean 404\'d!',
		'<img src="http://www.digitalitcc.com/images/baghdadbob.jpg">
		<br>No there is no page here.  There is no page within a hundred kilobytes of here.
		<br>I am not worried.  And neither should you be.
		<br>In fact, as we speak, viruses are committing suicide outside the firewall of this server, and we encourage them to continue doing so.<br>
		<br>There is only truth here.  All the rest is lies! Lies! Lies!',
		'Hmmm ...seems one of our pages hasn\'t returned from its coffee break - we\'ll need to dock its pay.',
		'Boy, you sure are stupid.<br><br>Were you just making up names of files or what? i mean, i\'ve seen some pretend file names in my day, but come on! it\'s like you\'re not even trying.<br><br>in closing, go away.',
		'<span class="cat">Oswald Denies Role In 404 Error</span>
		<br><img src="http://www.saintaardvarkthecarpeted.com/images/oswald_press_conference.jpg">
		<br>"I am not resisting your browsing"
		<br>
		<br>DALLAS, TEXAS (Aardvark News Agency): In a press conference today, accused JFK-assassin Lee Harvey Oswald denied any role in the "404: Page Not Found" web server error.
		<br>
		<br>"I am not responsible for this error," said Oswald. "I suspect the reader is looking for a page that has moved, or is simply not there at all. I am not resisting your browser."
		<br>
		<br>When asked what he thought the reader should do, he replied simply, "Look on the <a href="/">main</a> page."
		<br>
		<br>Oswald was then shot by agents of Madonna.',
		'<span class="cat">902 Screen Error</span>
		<br>The system is working perfectly, I\'m not lying, your monitor is wrong',
		'I blame George W. Bush and Halliburton. Wouldn\'t it be just like them to steal the document you were looking for, just to keep you from seeing it? Damned, evil Patriot Act.',
		'<span class="cat">019 User error</span>
		<br>Not our fault. Is Not! Is Not!',
		'<span class="cat">007 System price error</span>
		<br>Inadequate money spent on hardware.',
		'Page destroyed by lightning.',
		'Godzilla was here, thought it was tasty, and ate it.',
		'No, the bleach does not go with the ammonia. What? Oh, that page cannot be found.',
		'Unfortunately the page you are looking for has been declared
		<br>
		<br>M.I.A.
		<br>(missing in action)',
		'With searching comes loss
		<br>and the presence of absence
		<br>This web page not found',
		'Ah cain\'t find th\' page yer lookin\' fer. - Southern American',
		'Wuhloss, man, de page yuh lookin for ent here!! - Bajan',
		'Dude? - West Coast USA',
		'It no ded-deh! Jamaican Patois',
		'j00 f001, 7|-|47 p4g3 d0|\|\'7 eXi57! y0u sux0rz. - 1337/h4x0r',
		'It\'s not there, eh? - Canadian',
		'546865207061676520796F7520617265206C6F6F6B696E
		6720666F722063616E6E6F7420626520666F756E642E00 - Hexadecimal',
		'0011010000110000001101000010000001000110010010
		01010011000100010100100000010011100100111101010
		100001000000100011001001111010101010100111001000100. - Binary',
		'That file is cow\'s-legs-up. - Mid-Michigan dairy farm colloquialism',
		'<span class="cat">404 - eaten by cats!</span>
		<br>sorry, but the page you\'re looking for is not on this server.',
		'The alien has abducted the page! The alien, however, lacks remorse for the kidnapping, and refuses to give the page back.',
		'The requested document is totally fake.
		<br>No /404 here.
		<br>Even tried multi.
		<br>Nothing helped.
		<br>I\'m really depressed about this.
		<br>You see, I\'m just a web server...
		<br>-- here I am, brain the size of the universe,
		<br>trying to serve you a simple web page,
		<br>and then it doesn\'t even exist!
		<br>Where does that leave me?!
		<br>I mean, I don\'t even know you.
		<br>How should I know what you wanted from me?
		<br>You honestly think I can *guess*
		<br>what someone I don\'t even *know*
		<br>wants to find here?
		<br>*sigh*
		<br>Man, I\'m so depressed I could just cry.
		<br>And then where would we be, I ask you?
		<br>It\'s not pretty when a web server cries.
		<br>And where do you get off telling me what to show anyway?
		<br>Just because I\'m a web server,
		<br>and possibly a manic depressive one at that?
		<br>Why does that give you the right to tell me what to do?
		<br>Huh?
		<br>I\'m so depressed...
		<br>I think I\'ll crawl off into the trash can and decompose.
		<br>I mean, I\'m gonna be obsolete in what, two weeks anyway?
		<br>What kind of a life is that?
		<br>Two effing weeks,
		<br>and then I\'ll be replaced by a .01 release,
		<br>that thinks it\'s God\'s gift to web servers,
		<br>just because it doesn\'t have some tiddly little
		<br>security hole with its HTTP POST implementation,
		<br>or something.
		<br>I\'m really sorry to burden you with all this,
		<br>I mean, it\'s not your job to listen to my problems,
		<br>and I guess it is my job to go and fetch web pages for you.
		<br>But I couldn\'t get this one.
		<br>I\'m so sorry.
		<br>Believe me!
		<br>Maybe I could interest you in another page?
		<br>There are a lot out there that are pretty neat, they say,
		<br>although none of them were put on *my* server, of course.
		<br>Figures, huh?
		<br>Everything here is just mind-numbingly stupid.
		<br>That makes me depressed too, since I have to serve them,
		<br>all day and all night long.
		<br>Two weeks of information overload,
		<br>and then *pffftt*, consigned to the trash.
		<br>What kind of a life is that?
		<br>Now, please let me sulk alone.
		<br>I\'m so depressed.',
		'The page cannot be found.
		<br>It has probably been eaten by bears.
		<br>
		<br>The page you are looking for might have been removed or had its name changed, but it\'s much more likely the bears got it.  These things happen.',
		'Wrong page, Sucka! Stop clicking all that jibba-jabba.<br><img src="http://www.morefreaky.com/mrt.jpg">',
		'Nicholas jumped up, pumped his fists in the air and shouted, "Yes! I am the Burger King!" as he spat out the last bits of the 3 and one-fifth burgers that could put him in the Guinness Book of World Records.
		<br>Hmm.. interesting...
		<br>What\'s even more interesting is that we can\'t find that page.',
		'<img src="http://www.digitalitcc.com/images/baghdadbob.jpg">
		<br>Yes, those lying, imperialistic dogs have lied to you again. They have told you that would would find ' . $_SERVER['REQUEST_URI'] . ' here but we do not have such a file.
		<br>
		<br>That file is not even within 100 miles of this website. That file is committing suicide because it has been shamed and Allah will roast its stomach in hell.
		<br>
		<br>Look around. Do you see the file they claimed was here? No! But we will be sending so many files to them that their weak and stupid browsers will scream for our mercy. And we will show those browsers no mercy whatsoever. We will show no mercy because those browsers who sent you looking for the file deserve no mercy.
		<br>
		<br>Do not be deceived any longer. Do not believe the lies that pour out of the mouth of the wicked. The great satan sends you looking for things that do not exist because he is confused and trapped. He only wishes to divert your attention as we slaughter him with our urls of power.'
		);
		
	return $error[rand(0,count($error)-1)];
}

function printitccheader()
{
	$css = array( 'www.digitalitcc.com' => 'itcc',
			'lan.digitalitcc.com' => 'lan',
			'cs.digitalitcc.com' => 'cs',
			'repairs.digitalitcc.com' => 'repairs',
			'www.shsclubs.org' => 'default',
			'csf.shsclubs.org' => 'csf'
		);
?>
	<link rel="stylesheet" type="text/css" href="/<?=$css[$_SERVER['HTTP_HOST']]?>.css">
	<META HTTP-EQUIV="Set-Cookie" CONTENT="cookie=set; path=/">
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<?
}
*/
?>
