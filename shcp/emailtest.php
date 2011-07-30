<?
// Michael Wu | saratogahigh.com
// shcp/email.php: composes messages by email in response to comments

$config = array( 'title' => 'Administrator Email' );
define('MAIL_SPLITCOUNT',100);

include "../inc/config.php";

define('ORIG_FOLDER','/tmp/');
define('TMP_FOLDER','/home/webmaster/html_temp/');
define('XATTACH_SIZE',2097152);
define('XATTACH_TOTAL',6291456);

$done = 0;

function supertrim($mystr)
{
	return trim(ereg_replace(' +',' ',$mystr));
}

function cleanup_recipients($receive)
{
	if(!is_array($receive))
		return array();

	$count = count($receive);
	
	for($i = 0;$i < $count;$i++)
	{
		$receive[$i] = trim($receive[$i]);
	}
	
	$groups = array('All Students' => array(),
		'Freshman Class' => array(),
		'Sophomore Class' => array(),
		'Junior Class' => array(),
		'Senior Class' => array(),
		'All Parents' => array(),
		'Freshman Class Parents' => array(),
		'Sophomore Class Parents' => array(),
		'Junior Class Parents' => array(),
		'Senior Class Parents' => array(),
		'All Teachers' => array(),
		'All Staff' => array(),
		'Admins' => array(),
		'Programmers' => array() );

	foreach( array_keys($groups) as $group )
	{
		if(in_array($group,$receive))
		{
			switch($group)
			{
				case "All Students":
					$emails = db::get_prefix_result("USER",'USER_GR >= "' . C_SCHOOLYEAR . '" AND USER_GR < "' . (C_SCHOOLYEAR + 4) . '" AND LENGTH(USER_EMAIL) > 0 AND USER_ASBMAIL = "1"');
					break;
				case "Freshman Class":
					$emails = db::get_prefix_result("USER",'USER_GR = "' . (C_SCHOOLYEAR + 3) . '" AND LENGTH(USER_EMAIL) > 0 AND USER_ASBMAIL = "1"');
					break;
				case "Sophomore Class":
					$emails = db::get_prefix_result("USER",'USER_GR = "' . (C_SCHOOLYEAR + 2) . '" AND LENGTH(USER_EMAIL) > 0 AND USER_ASBMAIL = "1"');
					break;
				case "Junior Class":
					$emails = db::get_prefix_result("USER",'USER_GR = "' . (C_SCHOOLYEAR + 1) . '" AND LENGTH(USER_EMAIL) > 0 AND USER_ASBMAIL = "1"');
					break;
				case "Senior Class":
					$emails = db::get_prefix_result("USER",'USER_GR = "' . C_SCHOOLYEAR . '" AND LENGTH(USER_EMAIL) > 0 AND USER_ASBMAIL = "1"');
					break;
				case "All Parents":
					$emails = mysql_query("SELECT DISTINCT USER_LIST.* FROM USER_LIST
							INNER JOIN PARENTSTUDENT_LIST ON PARENTSTUDENT_PARENT = USER_LIST.USER_ID
							INNER JOIN USER_LIST AS CHILD_LIST ON CHILD_LIST.USER_ID = PARENTSTUDENT_STUDENT AND CHILD_LIST.USER_GR >= '" . C_SCHOOLYEAR . "' AND CHILD_LIST.USER_GR < '" . (C_SCHOOLYEAR + 4) . "'
							WHERE USER_LIST.USER_GR = '1' AND LENGTH(USER_LIST.USER_EMAIL) > 0 AND USER_LIST.USER_ASBMAIL = '1'");
					break;
				case "Freshman Class Parents":
					$emails = mysql_query("SELECT DISTINCT USER_LIST.* FROM USER_LIST
							INNER JOIN PARENTSTUDENT_LIST ON PARENTSTUDENT_PARENT = USER_LIST.USER_ID
							INNER JOIN USER_LIST AS CHILD_LIST ON CHILD_LIST.USER_ID = PARENTSTUDENT_STUDENT AND CHILD_LIST.USER_GR = '" . (C_SCHOOLYEAR + 3) . "'
							WHERE USER_LIST.USER_GR = '1' AND LENGTH(USER_LIST.USER_EMAIL) > 0 AND USER_LIST.USER_ASBMAIL = '1'");
					break;
				case "Sophmore Class Parents":
					$emails = mysql_query("SELECT DISTINCT USER_LIST.* FROM USER_LIST
							INNER JOIN PARENTSTUDENT_LIST ON PARENTSTUDENT_PARENT = USER_LIST.USER_ID
							INNER JOIN USER_LIST AS CHILD_LIST ON CHILD_LIST.USER_ID = PARENTSTUDENT_STUDENT AND CHILD_LIST.USER_GR = '" . (C_SCHOOLYEAR + 2) . "'
							WHERE USER_LIST.USER_GR = '1' AND LENGTH(USER_LIST.USER_EMAIL) > 0 AND USER_LIST.USER_ASBMAIL = '1'");
					break;
				case "Junior Class Parents":
					$emails = mysql_query("SELECT DISTINCT USER_LIST.* FROM USER_LIST
							INNER JOIN PARENTSTUDENT_LIST ON PARENTSTUDENT_PARENT = USER_LIST.USER_ID
							INNER JOIN USER_LIST AS CHILD_LIST ON CHILD_LIST.USER_ID = PARENTSTUDENT_STUDENT AND CHILD_LIST.USER_GR = '" . (C_SCHOOLYEAR + 1) . "'
							WHERE USER_LIST.USER_GR = '1' AND LENGTH(USER_LIST.USER_EMAIL) > 0 AND USER_LIST.USER_ASBMAIL = '1'");
					break;
				case "Senior Class Parents":
					$emails = mysql_query("SELECT DISTINCT USER_LIST.* FROM USER_LIST
							INNER JOIN PARENTSTUDENT_LIST ON PARENTSTUDENT_PARENT = USER_LIST.USER_ID
							INNER JOIN USER_LIST AS CHILD_LIST ON CHILD_LIST.USER_ID = PARENTSTUDENT_STUDENT AND CHILD_LIST.USER_GR = '" . C_SCHOOLYEAR . "'
							WHERE USER_LIST.USER_GR = '1' AND LENGTH(USER_LIST.USER_EMAIL) > 0 AND USER_LIST.USER_ASBMAIL = '1'");
					break;
				case "All Teachers":
					$emails = db::get_prefix_result("USER","USER_GR = '0' AND LENGTH(USER_EMAIL) > 0 AND USER_ASBMAIL = '1'");
					break;
				case "All Staff":
					$emails = db::get_prefix_result("USER",'USER_STATUS > 0 AND LENGTH(USER_EMAIL) > 0 AND USER_ASBMAIL = "1"');
					break;
				case "Admins":
					$emails = db::get_prefix_result("USER",'USER_STATUS > 1 AND LENGTH(USER_EMAIL) > 0 AND USER_ASBMAIL = "1"');
					break;
				case "Programmers":
					$emails = db::get_prefix_result("USER",'USER_STATUS > 2 AND LENGTH(USER_EMAIL) > 0 AND USER_ASBMAIL = "1"');
					break;
			}
			
			while($row = db::fetch_row($emails))
				$groups[$group][] = $row['USER_FULLNAME'] . " <" . $row['USER_EMAIL'] . ">";
			mysql_free_result($emails);
		}
	}
	for($i = 0;$i < $count;$i++)
	{
		$group_found = false;
		
		foreach(array_keys($groups) as $group)
		{
			if(preg_match("/^$group$/",$receive[$i]))
			{
				$receive = array_merge($receive,$groups[$group]);
				$group_found = true;
			}
		}
		
		if($group_found)
		{						
			array_splice($receive,$i,1);
			$i--;
		}
	}
	
	$count = count($receive);
	for($i = 0;$i < $count;$i++)
	{
		if(0 && preg_match("/asdfafdsasdfadsfasdfasfd/",$receive[$i])) // INVALID
		{
			array_splice($receive,$i,1);
			$i--;
		}
		
		if(strlen($receive[$i]) < 1)
			unset($receive[$i]);
	}
	
	$receive = array_unique($receive);
	
	return $receive;
}

if($loggedin && $isvalidated)
{
	$xfrom = "{$userR['USER_FULLNAME']} <{$userR['USER_EMAIL']}>";
	
	if(isset($_POST['xto']))
		$xto = stripslashes($_POST['xto']);
	if(isset($_POST['xfrom']))
		$xfrom = stripslashes($_POST['xfrom']);
	if(isset($_POST['xcc']))
		$xcc = stripslashes($_POST['xcc']);
	if(isset($_POST['xbcc']))
		$xbcc = stripslashes($_POST['xbcc']);
	if(isset($_POST['xsubj']))
		$xsubj = stripslashes($_POST['xsubj']);
	if(isset($_POST['xmsgtxt']))
		$xmsgtxt = stripslashes($_POST['xmsgtxt']);
	if(isset($_POST['xhtml']))
		$xhtml = true;
	else
		$xhtml = false;
	
	$xattaches = array();
	
	$keys = preg_grep("/^xattach_\d+$/",array_keys($_POST));
	$size = 0;
		
	foreach($keys as $key)
	{
		if(!file_exists(TMP_FOLDER . $_POST[$key . '_tmp_name']))
			continue;
	
		if(preg_match('#^(.*/.*|\.\.)$#',$_POST[$key . '_name']) || preg_match('#^(.*/.*|\.\.)$#',$_POST[$key . '_tmp_name']) )
			continue;
				
		if($size + filesize( $tmp_name ) > XATTACH_TOTAL)
			continue;
				
		$size += filesize( $tmp_name );

		$tmp_name = TMP_FOLDER . $_POST[$key . '_tmp_name'];

		array_push($xattaches, array( 'name' => $_POST[$key . '_name'],
			'tmp_name' => $_POST[$key . '_tmp_name'],
			'size' => filesize( $tmp_name ),
			'type' => $_POST[$key . '_type'] ) );
	}
	
	if(is_id($_GET['recomment']) && $isadmin)
	{
		$cc = mysql_query('SELECT * FROM COMMENT_LIST LEFT JOIN USER_LIST ON COMMENT_USER=USER_ID AND COMMENT_USER IS NOT NULL WHERE COMMENT_ID="' . $_GET['recomment'] . '"');

		if($n = mysql_fetch_array($cc))
		{
			if($n['COMMENT_USER'])
			{
				$name = $n['USER_FULLNAME'];
				$recommentxto = "$name <{$n['USER_EMAIL']}>";
			}
			else
			{
				preg_match("/^Name:\s*(.*)$/m",$n['COMMENT_TEXT'],$matches);
				$name = $matches[1];
					
				preg_match("/^Email:\s*(.*)$/m",$n['COMMENT_TEXT'],$matches);

				$email = $matches[1];

				$name = trim($name);
				$email = trim($email);
				
				$recommentxto = "$name <$email>";
			}
			
			$commentuserinfo = array();
	
			if($n['COMMENT_USER'])
				$commentuserinfo[] = $n;
			else if(strlen($name) > 0)
			{
				if($row = db::get_prefix_row('USER','USER_FULLNAME="' . $name . '"'))
					$commentuserinfo[] = $row;
			}
					
			if(is_array($commentuserinfo[0]))
			{
				$commentuserid = $commentuserinfo[0]['USER_ID'];
				$relationsR = db::get_prefix_result('USER','1','','','INNER JOIN PARENTSTUDENT_LIST ON (USER_ID=PARENTSTUDENT_PARENT AND PARENTSTUDENT_STUDENT="' . $commentuserid . '") OR (USER_ID=PARENTSTUDENT_STUDENT AND PARENTSTUDENT_PARENT="' . $commentuserid . '")');
				while($relation = db::fetch_row($relationsR))
					$commentuserinfo[] = $relation;
				db::free($relationsR);
			}
		}
	}

	if($_POST['go'] == 'Send')
	{
		$params['host'] = 'mail.saratogahigh.com';				// The smtp server host/ip
		$params['port'] = 25;						// The smtp server port
		$params['helo'] = "mail.saratogahigh.com";			// What to use when sending the helo command. Typically, your domain/hostname
		$params['auth'] = TRUE;						// Whether to use basic authentication or not
		$params['user'] = 'staff@saratogahigh.com';				// Username for authentication
		$params['pass'] = 'powertrip1024';				// Password for authentication
		
		if(!is_object($smtp = smtp::connect($params)))
			die('Connection failed.');
	
		preg_match("/^(.*)$/m",$xto,$matches);
		$xto = $matches[1];
		
		preg_match("/^(.*)$/m",$xfrom,$matches);
		$xfrom = $matches[1];
		
		preg_match("/^(.*)$/m",$xcc,$matches);
		$xcc = $matches[1];
		
		preg_match("/^(.*)$/m",$xbcc,$matches);
		$xbcc = $matches[1];
		
		preg_match("/^(.*)$/m",$xsubj,$matches);
		$xsubj = $matches[1];
		
		$receive = explode(",",$xto);
		$receivecc = explode(",",$xcc);
		$receivebcc = explode(",",$xbcc);
		
		$receive = cleanup_recipients($receive);
		$receivecc = cleanup_recipients($receivecc);
		$receivebcc = cleanup_recipients($receivebcc);
		
		$tocc_string = "";
		
		$receive_all = array();
		
		if(count($receive) > 0)
		{
			$tocc_string .= "To: $xto\n";
			$receive_all = array_merge($receive_all,$receive);
		}
		if(count($receivecc) > 0)
		{
			$tocc_string .= "Cc: $xcc\n";
			$receive_all = array_merge($receive_all,$receivecc);
		}
		if(count($receivebcc) > 0)
		{
			$receive_all = array_merge($receive_all,$receivebcc);
		}
		
		$receive_all = array_unique($receive_all);
		
		set_time_limit(600);
		
		$headers = "From: $xfrom\n"
		. "Reply-To: $xfrom\n"
		. "X-Mailer: PHP/" . phpversion() . "\n"
		. "Subject: $xsubj\n"
		. "MIME-version: 1.0\n";
		
		$boundary = random_string(10,20);
		$mixed = (count($xattaches) > 0 || $xhtml);
		
		if($mixed)
			$headers .= "Content-Type: multipart/mixed; boundary = $boundary\n\n";
		
		if($mixed)
			$headers .= "--$boundary\n";
		$headers .= "Content-Type: text/plain; charset=ISO-8859-1\n"
			. "Content-Transfer-Encoding: base64\n\n";
		$xmsgtxt_plain = $xmsgtxt;
		if($xhtml)
			$xmsgtxt_plain = preg_replace("/<.+?>/","",$xmsgtxt_plain);
		$headers .= chunk_split(base64_encode($xmsgtxt_plain));
		$headers .= "\n\n";
		
		if($xhtml)
		{
			$headers .= "--$boundary\n";
			$headers .= "Content-Type: text/html; charset=ISO-8859-1\n"
				. "Content-Transfer-Encoding: base64\n\n";
			$headers .= chunk_split(base64_encode($xmsgtxt));
			$headers .= "\n\n";
		}
		
		foreach($xattaches as $xattach)
		{
			$headers .= "--$boundary\n";
			$headers .= "Content-Type: " . $xattach['type'] . "; name=\"" . $xattach['name'] . "\"\n"
				. "Content-Transfer-Encoding: base64\n\n"
				. "Content-disposition: attachment; filename=\"" . $xattach['name'] . "\"\n\n";

			$linesz= filesize( TMP_FOLDER . $xattach['tmp_name'])+1;

			$fp=fopen( TMP_FOLDER . $xattach['tmp_name'], 'r' );
			$headers .= chunk_split(base64_encode(fread( $fp, $linesz)));
			fclose($fp);
			
			$headers .= "\n\n";
		}
		
		if($mixed)
			$headers .= "--$boundary--";
			
		while(count($receive_all) > 0)
		{
			$send_params = array();
			
			$send_params['recipients']	= array_splice($receive_all,0,MAIL_SPLITCOUNT);	// The recipients (can be multiple)
			$send_params['headers']		= array($tocc_string . $headers);

			print_r($send_params);

			$smtp->send($send_params);
		}
		
		foreach($smtp->errors as $error)
			$page->error(htmlentities($error));
		
		$done = 1;
	}
	else if(isset($_POST['addattach']))
	{
		if(!$_FILES['xattach']['error'])
		{
			$tmp_name = $_FILES['xattach']['tmp_name'];
		
			if($size + filesize($tmp_name) <= XATTACH_TOTAL)
			{
				preg_match("#^" . ORIG_FOLDER . "(.*)$#",$tmp_name,$matches);
				$tmp_name = $matches[1];
				
				move_uploaded_file(ORIG_FOLDER . $tmp_name,TMP_FOLDER . $tmp_name);

				$mime = exec("file -i " . TMP_FOLDER . $tmp_name);
				
				preg_match("#^" . TMP_FOLDER . $tmp_name . ": (.*)$#",$mime,$matches);
				$mime = $matches[1];
				$xattaches[] = array( 'name' => $_FILES['xattach']['name'],
					'tmp_name' => $tmp_name,
					'type' => $mime,
					'size' => filesize(TMP_FOLDER . $tmp_name) );
			}
		}
		else if($_FILES['xattach']['error'] > 0 && $_FILES['xattach']['error'] < 3)
			$page->error('File too big!');
	}
	else if(isset($_POST['delattach']))
	{
		$keys = preg_grep("/^xattach_\d+_check$/",array_keys($_POST));
		
		foreach($keys as $key)
		{
			preg_match("/^xattach_(\d+)_check$/",$key,$matches);
			if(is_numeric($no = $matches[1]))
			{
				unlink(TMP_DIRECTORY . $xattaches[$no]['tmp_name']);
			
				array_splice($xattaches,$no,1);
			}
			else
				continue;
		}
	}
	else
	{
		if(is_id($_GET['recomment']) && $isadmin)
		{
			$xto = $recommentxto;
			$xcc = "Staff <staff@saratogahigh.com>";
		
			$xmsgtxt = "Dear " . $name . ",\n\n"
					. "\t\n\n"
					. "Sincerely,\n"
					. $userR['USER_FULLNAME'] . " and the staff of saratogahigh.com";
			
			$xmsgtxt .= '


------ On ' . date(TIME_FORMAT, strtotime($n['COMMENT_TS'])) . ' you wrote:
' . $n['COMMENT_TEXT'];
		}
	}
}

$page->set_config('extracss','a.lnkc { font-weight: bold }');
	
$page->header();
	
if($done != 1)
{
?>

<? // include "inc-header.php" WORK ON NAVBAR CAPABILITIES ?>

		<? if($loggedin) { ?>
			<? if($isadmin) { ?>
<script type="text/javascript">
<!--
	var current_box = "txtto";
	
	function addRecipient(name)
	{
		if(current_box)
		{
			var i;
			var current_value = document.getElementById(current_box).value;
			var current_value_array = current_value.split(/,/);
			var already_regexp = new RegExp("^\s*" + name + "\s*$", "i");
			
			var found = false;
			
			for(i=0; i < current_value_array.length; i++)
			{
				current_value_array[i] = current_value_array[i].trim();
				if(already_regexp.exec(current_value_array[i]))
					found = true;
			}
			
			if(!found)
			{
				if(document.getElementById(current_box).value.match(/\w/))
					document.getElementById(current_box).value += ", ";
				document.getElementById(current_box).value += name;
			}
		}
			
		return false;
	}
	
	function makeCurrent(evt)
	{
		evt = new Evt(evt);
		src = evt.getSource();
		
		current_box = src.id;
	}
// -->
</script>
<div style="width: 570px; padding: 0px; margin: 0px; position: absolute">
<div style="background-color: #dddddd">
<h1 style="margin: 0px; padding: 2px; letter-spacing: 1pt; font-size: large">Compose New Message</h1>
</div>
<?
	$getoptions = array();
	if(strlen($_GET['next']) > 0)
		$getoptions[] = "next=" . $_GET['next'];
	if(is_numeric($_GET['recomment']))
		$getoptions[] = "recomment=" . $_GET['recomment'];
		
	$getstring = "";
	if(count($getoptions) > 0)
		$getstring = "?" . implode("&",$getoptions);
?>
<form action="emailtest.php<?= $getstring ?>" enctype="multipart/form-data" name="mf" method="POST" style="margin: 0px">
<? $page->do_error(); ?>
<div style="background-color: #eeeeee">
<p style="margin: 0px; padding: 2px; font-size: medium">Type the emails of your recipients, separated by commas. This must conform to <a href="http://rfc.net/rfc2822.html">RFC 2822</a>. Please do not SMTP header inject, firstly, because that's not nice, and secondly, this won't allow it, and lastly, you're an admin. We trust you.</p>
</div>
	<table>
	<tr><td align="right">from</td><td><input id="txtfrom" type="text" name="xfrom" value="<?= htmlentities($xfrom) ?>" style="width: 430px" size="100"></td></tr>
	<tr><td align="right">to</td><td><input id="txtto" type="text" name="xto" value="<?= htmlentities($xto) ?>" style="width: 430px" onClick="makeCurrent(event);"></td></tr>
	<tr><td align="right">cc</td><td><input id="txtcc" type="text" name="xcc" value="<?= htmlentities($xcc) ?>" style="width: 430px" onClick="makeCurrent(event);"></td></tr>
	<tr><td align="right">bcc</td><td><input id="txtbcc" type="text" name="xbcc" value="<?= htmlentities($xbcc) ?>" style="width: 430px" onClick="makeCurrent(event);"></td></tr>
	<tr><td align="right" style="font-weight: bold">subject</td><td><input type="text" name="xsubj" value="<?= htmlentities($xsubj) ?>" style="width: 400px" size="40"></td></tr>
	<? if($xattach > 0) { ?>
	<tr><td align="right" style="font-weight: bold">attach</td><td><?= $xattachstr ?></td></tr>
	<tr><td></td><td>The message above, including all its text and attachments, will be attached to the current message.</td></tr>
	<? } ?>
	</table>
	<input type="hidden" name="xattach" value="<?= $xattach ?>">
	<p><textarea name="xmsgtxt" rows="12" style="width: 555px" cols="55" wrap="virtual"><?= htmlentities($xmsgtxt) ?></textarea></p>
	<p style="border: 1px solid #CCCCCC; padding: 5px;">attach file <input type="hidden" name="MAX_FILE_SIZE" value="<?=XATTACH_SIZE?>"> <input type="file" name="xattach"> <input type="submit" name="addattach" value="Add"><div style="float: right">No more than <?= human_file_size(XATTACH_TOTAL) ?> total or <?= human_file_size(XATTACH_SIZE) ?> individually.</div>
	<div id="xattachlist">
	<?
	for($i = 0;$i < count($xattaches);$i++) {
		print '<input type="checkbox" name="xattach_' . $i . '_check"> ' . $xattaches[$i]['name'] . ' ' . $xattaches[$i]['type'] . ' (' . human_file_size($xattaches[$i]['size']) . ')'
			. '<input type="hidden" name="xattach_' . $i . '" value="1">'
			. '<input type="hidden" name="xattach_' . $i . '_tmp_name" value="' . $xattaches[$i]['tmp_name'] . '">'
			. '<input type="hidden" name="xattach_' . $i . '_type" value="' . $xattaches[$i]['type'] . '">'
			. '<input type="hidden" name="xattach_' . $i . '_name" value="' . $xattaches[$i]['name'] . '"><br>';
	}
	
	if(count($xattaches) > 0)
	{
		print '<div>'
			. '<input type="submit" name="delattach" value="Delete Checked">'
			. '</div>';
	}
	?>
	</div>
	</p>
	<p>HTML mail? <input type="checkbox" name="xhtml"<?= ($xhtml?' selected':'') ?>>
	<br><input type="submit" name="go" value="Send"></p>
	</form>
</div>
<div style="float: right; position: absolute; left: 600px;">
<div style="background-color: #dddddd; width: 300px;">
<h1 style="margin: 0px; padding: 2px; letter-spacing: 1pt; font-size: large">Email Lists</h1>
</div>
<p>Click on a list to add to your recipients.</p>
<p><a href="javascript:" onClick="return addRecipient('All Students');">All Students</a>
<br><a href="javascript:" onClick="return addRecipient('Freshman Class');">Freshman Class</a>
<br><a href="javascript:" onClick="return addRecipient('Sophomore Class');">Sophomore Class</a>
<br><a href="javascript:" onClick="return addRecipient('Junior Class');">Junior Class</a>
<br><a href="javascript:" onClick="return addRecipient('Senior Class');">Senior Class</a>
<br><a href="javascript:" onClick="return addRecipient('All Parents');">All Parents</a>
<br><a href="javascript:" onClick="return addRecipient('Freshman Class Parents');">Freshman Class Parents</a>
<br><a href="javascript:" onClick="return addRecipient('Sophomore Class Parents');">Sophomore Class Parents</a>
<br><a href="javascript:" onClick="return addRecipient('Junior Class Parents');">Junior Class Parents</a>
<br><a href="javascript:" onClick="return addRecipient('Senior Class Parents');">Senior Class Parents</a>
<br><a href="javascript:" onClick="return addRecipient('All Teachers');">All Teachers</a>
<br><a href="javascript:" onClick="return addRecipient('All Staff');">All SaratogaHigh.com Staff</a>
<br><a href="javascript:" onClick="return addRecipient('Admins');">SaratogaHigh.com Admins</a>
<br><a href="javascript:" onClick="return addRecipient('Programmers');">SaratogaHigh.com Programmers</a>
</p>
<? if($_GET['recomment']) { ?>
<div style="background-color: #dddddd; width: 300px;">
<h1 style="margin: 0px; padding: 2px; letter-spacing: 1pt; font-size: large">Relations</h1>
</div>
<?
if(!is_array($commentuserinfo))
	$commentuserinfo = array();
foreach($commentuserinfo as $l)
{
	$afull = $l["USER_FULLNAME"];

	$aun = $l["USER_UNAME"];
	$axfn = $l["USER_FN"];
	$axln = $l["USER_LN"];
	$aaddr = $l["USER_ADDRESS"];
	$acity = $l["USER_CITY"];
	$azip = $l["USER_ZIP"];
	$agr = $l["USER_GR"];
	$atag = $l["USER_TEACHERTAG"];
	$aac = $l["USER_ACTIVATION"];
	$aemail = $l["USER_EMAIL"];
	$asid = $l["USER_SID"];
	$aaim = $l["USER_AIM"];
	$amailcap = $l["USER_MAILCAP"];
?>
<form action="/edit-user.php?id=<?= $sid ?>" name="mf" method="POST" target="_blank">
<h2><?= $afull ?></h2>
<h3><?= GradePrint($agr); ?></h3>

<table>

	<tr>
	<td>First Name</td>
	<td><input type="text" maxlength="16" size="20" name="xfn" value="<?= htmlentities($axfn) ?>"></td>
	</tr>
	<tr>
	<td>Last Name</td>
	<td><input type="text" maxlength="16" size="20" name="xln" value="<?= htmlentities($axln) ?>"></td>
	</tr>
	<tr>
	<td style="vertical-align: top;">Address</td>
	<td><input type="text" maxlength="48" size="35" name="addr" value="<?= htmlentities($aaddr) ?>">
	<br><input type="text" maxlength="16" size="16" name="city" value="<?= htmlentities($acity) ?>">, CA <input type="text" maxlength="5" size="8" name="zip" value="<?= htmlentities($azip) ?>">
	</td>
	</tr>
	<tr>
	<td>Email Address</td>
	<td><input type="text" maxlength="48" size="35" name="email" value="<?= htmlentities($aemail) ?>"></td>
	</tr>
	<tr>
	<td>User Name</td>
	<td><input type="text" maxlength="32" size="35" name="un" value="<?= htmlentities($aun) ?>"></td>
	</tr>
	<tr>
	<td>Student ID#</td>
	<td><input type="text" maxlength="6" size="8" name="sid" value="<?= $asid ?>"></td>
	</tr>
	<tr>
	<td>Grade</td>
	<td><select name="gr">
		<?
		for($i = C_SCHOOLYEAR - 4; $i <= C_SCHOOLYEAR + 4; $i++)
		{
			print '<option ';
			if($agr == $i)
				print 'selected ';
			print 'value="' .  $i . '">' . GradePrint($i) . '</option>';
		}

		?>
		<option <? if($agr=='0') { print "selected"; } ?> value="0">Faculty</option>
		<option <? if($agr=='1') { print "selected"; } ?> value="1">Parent</option>
	</select></td>
	</tr>
	<tr>
		<td>Teacher Tag</td>
		<td>
		<?
		print "<select name=\"tag\">";
		$result = mysql_query("SELECT * FROM TEACHER_LIST ORDER BY TEACHER_NAME") or die("User query failed");
		print "<option value=\"\">Not applicable</option>";
		while ($line = mysql_fetch_array($result, MYSQL_ASSOC))
		{
			print "<option ";
			if($line["TEACHER_ID"] == $atag)
				print " selected ";
			print "value=\"" . $line["TEACHER_ID"] . "\">" . $line["TEACHER_NAME"] . "</option>";
		}
		mysql_free_result($result);
		print "</select>";
		?></td>
	</tr>
	<tr>
	<td>Activation Code</td>
	<td style="font-weight: bold"><? if(is_null($aac))
		print '(Activated)';
	else
		print $aac; ?></td>
	</tr>
	<tr>
	<td>AIM SN</td>
	<td style="font-weight: bold">
	<? if(is_null($aaim))
		print 'None';
	else
		print "$aaim <a href=\"resetsn.php\">Reset</a>"; ?></td>
	</tr>
	<tr>
	<td>Shmail Cap</td>
	<td><input type="text" maxlength="4" size="6" name="mailcap" value="<?= htmlentities($amailcap) ?>"></td>
	</tr>
</table>

<p><input type="submit" name="btn" value="Save"></p>

</form>
<?	}

	if(count($commentuserinfo) < 1)
		print '<p>No information could be found.</p>';
} ?>
</div>
	<?
	} else { ?>
		<p>Sorry, only administrators can send mail.</p>
	<? } ?>
<? } else { ?>
	<p>Please <a href="../login.php?next=/shcp/email.php">log in</a> to compose messages.</p>
<? } ?>

<?
}
else
{
	if(count($page->error_array) > 0)
	{
		print 'Email failed. Reasons below:<br>';
		
		$page->do_error();
	}
	else
		print 'Email successfully sent.';
		if(strlen($_GET['next']))
		{
			print ' Follow this <a href="' . $_GET['next'] . '">link</a> to continue.';
		}
}

$page->footer();
?>