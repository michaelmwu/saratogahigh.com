<?
if(isset($_GET['google']))
{
	$type = $_POST['type'];
	$query = $_POST['query'];
	
	if($type == 'web')
	{
		header('Location: http://www.google.com/search?q=' . urlencode($query) . '&svnum=10&hl=en&lr=&tab=nw&ie=UTF-8&sa=N');
	}
	else if($type == 'images')
	{
		header('Location: http://images.google.com/images?svnum=10&hl=en&lr=&q=' . urlencode($query) . '&btnG=Search');
	}
	else if($type == 'maps')
	{
		header('Location: http://maps.google.com/maps?q=' . urlencode($query));
	}
	else if($type == 'news')
	{
		header('Location: http://news.google.com/news?svnum=10&hl=en&lr=&tab=in&ie=UTF-8&q=' . urlencode($query) . '&btnG=Search+News');
	}
}

class box
{
	var $id;
	var $userid;
	var $userR;
	
	var $args;
	
	var $frontbox;
	
	var $prefs = array();
	var $default_prefs = array();
	
	function get_pref($key)
	{
		return (array_key_exists($prefs,$key)) ? $prefs['key'] :
				(array_key_exists($default_prefs,$key)) ? $default_prefs[$key] :
				false;
	}
	
	function box($frontbox_id)
	{
		$this->userid = $GLOBAL['userid'];
		$this->userR = $GLOBAL['userR'];
		if(is_numeric($frontbox_id))
			$this->frontbox = db::get_prefix_id("FRONTBOX",$frontbox_id);
		else if(preg_match("/^c\d+$/i",$frontbox_id))
			return new custom_box($frontbox_id);
		else
			return false;
		
		$result = db::get_prefix_result("FRONTPREF",'FRONTPREF_USER="' . $this->userid . '" AND FRONTPREF_BOX="' . $frontbox_id . '"');
		while($row = db::fetch_row($result))
		{
			$pref[$row['FRONTPREF_KEY']] = $row['FRONTPREF_VALUE'];
		}
		
		if(is_callable($this->frontbox['FRONTBOX_FUNCTION']))
		{
			$php = '$tmp_array = array( ' . $box['FRONTBOX_PHPARGUMENT'] . ' );';
			eval($php);
			
			array_unshift($tmp_array,$this);
			
			call_user_func_array($box['FRONTBOX_FUNCTION'],$tmp_array);
		}
		
		return true;
	}
	
	function output()
	{
		$content = box_header($this);
		$content .= '<div id="">';
		$content .= $this->contents();
		$content .= '</div>';
		if(strlen($contentafter = $this->content_after()) > 0)
		{
			$content .= '<div class="footer">';
			$content .= $contentafter;
			$content .= '</div>';
		}
		$content .= '</div>';
		
	}
	
	function title()
	{
	}
	
	function htmlid()
	{
	}
	
	function label()
	{
	}
	
	function content()
	{
	}
	
	function content_after()
	{
	}
}

class custom_box extends box
{
}

function move_boxes($boxes, $column)
{
	$column_boxes = $boxes[$column];
	if(!is_array($column_boxes))
		$column_boxes = array();
	
	foreach( $column_boxes as $current_box )
	{
		$boxesR = mysql_query('SELECT * FROM FRONTBOX_LIST WHERE FRONTBOX_ID=' . $current_box);
		if($box = mysql_fetch_array($boxesR, MYSQL_ASSOC ) )
		{
			print '<div id="' . $box['FRONTBOX_HTMLID'] . '" name="' . $box['FRONTBOX_ID'] . '" class="movebox">';
			$php = '$tmp_array = array( ' . $box['FRONTBOX_PHPARGUMENT'] . ' );';
			eval($php);
			array_unshift($tmp_array,$box['FRONTBOX_ID'],$GLOBALS['userR']);
			if(is_callable($box['FRONTBOX_FUNCTION']))
			{
				call_user_func_array($box['FRONTBOX_FUNCTION'],$tmp_array);
			}
			print '</div>';
		}
	}
}

function get_box_prefs($box_id, $userid)
{
    $result=mysql_query("SELECT FRONTPREF_KEY, FRONTPREF_VALUE FROM FRONTPREF_LIST WHERE FRONTPREF_BOX=$box_id AND FRONTPREF_USER=$userid");
    
    //the first conditional is the ugly hack: if no content_state exists, create and initialize to shown
    if(!mysql_num_rows($result))
    {
        mysql_query("INSERT INTO FRONTPREF_LIST (FRONTPREF_USER, FRONTPREF_BOX, FRONTPREF_KEY, FRONTPREF_VALUE) VALUES($userid, $box_id, 'content_state', 'expanded')");
        return get_box_prefs($box_id, $userid);
    }
    else
    {
        $temp=array();
        while($row=mysql_fetch_assoc($result))
        {
            $temp[$row['FRONTPREF_KEY']]=$row['FRONTPREF_VALUE'];    
        }
        return $temp;
    }
}

//$content_state is kinda "returned" for use in open closing boxes
function box_header($title, $userid, &$content_state, $href = "", $class = "red", $title_backup=null) //$title_backup used for things like Class News
{
	//get the FRONTBOX_ID from FRONTBOX_LIST to be used later... also a good check for a insufficiently
    //caffeinated programmer...
    $result=mysql_query("SELECT FRONTBOX_ID FROM FRONTBOX_LIST WHERE FRONTBOX_TITLE='$title'");
    if(!mysql_num_rows($result))
    {
        if(!is_null($title_backup))
        {
            $result=mysql_query("SELECT FRONTBOX_ID FROM FRONTBOX_LIST WHERE FRONTBOX_TITLE='$title_backup'");
        }
    }
    
    if(!mysql_num_rows($result))
    {
        echo "Internal error detected: Mismatch between front box ID and front box title. Things Fall Apart";
        return;
    }
    $row=mysql_fetch_assoc($result);
    $box_prefs=get_box_prefs($row['FRONTBOX_ID'], $userid);
    $content_state=$box_prefs['content_state'];    
    
    print "\n\n";
	print '<h2 class="' . $class . '" onMouseOver="startMoveCursor(event);" onMouseDown="startDrag(event);">';
	if($href)
		print '<a href="' . $href . '">';
	print $title;
	if($href)
		print '</a>';
    
    print '<span class="xbox">';
    if($content_state=="expanded")
        print '<a href="#" class="expand" onClick="return expandMe(event);" style="display: none">&darr;</a><a href="#" class="minimize" onClick="return minimizeMe(event);">&uarr;</a>';
    else
        print '<a href="#" class="expand" onClick="return expandMe(event);">&darr;</a><a href="#" class="minimize" onClick="return minimizeMe(event);" style="display: none">&uarr;</a>';
          
	print ' <a href="#" onClick="return removeMe(event);">&times;</a></span></h2>';
}

/**
Err Michael... I think it's a bad idea to allow XMLRequest to post the userid-allows for spoofing
Methinks we should use a global variable (ie $userid)
**/

function save_boxes($temp_boxes,$userid = "")
{
	$boxes = array();
	$userid = $GLOBALS['userid'];

	for($i = 0; $i < count($temp_boxes); $i++)
	{
		$boxes[$i] = array();
		foreach($temp_boxes[$i] as $boxid)
			array_push($boxes[$i],$boxid);
	}

	mysql_query("UPDATE USER_LIST SET USER_FRONTPAGE = '" . serialize($boxes) . "' WHERE USER_ID='$userid'");
}

function save_box_prefs($box_id, $key, $value, $userid)
{
    mysql_query("UPDATE FRONTPREF_LIST SET FRONTPREF_VALUE='$value' WHERE FRONTPREF_USER='$userid' AND FRONTPREF_BOX='$box_id' AND FRONTPREF_KEY='$key'");   
}

function mail_block($frontbox,$userR) { ?>
		<?
            $userid = $userR['USER_ID'];
            box_header('My Mail',$userid,$content_state,"/mail","red");
            if($content_state=="expanded")
                print '<div class="content">';
            else
                print '<div class="content" style="display: none">';
        ?>
		<div class="headed">
		<p style="margin-bottom: 6px; margin-top: 0px;">
		<form style="margin: 0px" action="mail/compose.php" name="mf" method="POST">
		<p style="margin: 0px">To: (type full name)</p>
		<p style="margin: 0px"><input onfocus="document.getElementById('mailcomposebox').style.display = 'block'; document.getElementById('mailcomposearrow').style.display = 'none';" style="width: 90%;" type="text" name="xto" value="" size="55"><img src="/imgs/arrow-down.gif" onclick="document.getElementById('mailcomposebox').style.display = 'block'; document.getElementById('mailcomposearrow').style.display = 'none';" id="mailcomposearrow" style="vertical-align: middle" alt="(more)"></p>
		<div style="display: none" id="mailcomposebox">
		<p style="margin: 0px">Subject:<br><input style="width: 90%" type="text" name="xsubj" value="" size="40"></p>
		<p style="margin: 0px"><textarea name="xmsgtxt" rows="3" style="width: 90%" cols="55"></textarea></p>
		<p style="margin: 0px; font-weight: bold; text-align: right"><input type="submit" name="go" value="Send"></p>
		</div>
		</form>
		</div>
		<div class="footer"><a href="mail/">More Mail...</a></div>
        </div>
<? }

function notes_block($frontbox,$userR) { ?>
		<?
            $userid = $userR['USER_ID'];
            box_header('My Notes',$userid,$content_state,"/notepad","red");
            if($content_state=="expanded")
                print '<div class="content">';
            else
                print '<div class="content" style="display: none">'; 
        ?>
		<div class="headed">
		<p style="margin: 0px"><a href="notepad/"><?
		$cr = mysql_query('SELECT COUNT(*) FROM NOTEPAGE_LIST WHERE NOTEPAGE_OWNER=' . $userid) or die("Query failed");
		$l = mysql_fetch_array($cr, MYSQL_ASSOC);
		print $l['COUNT(*)'];
		?> note(s) saved</a>.</p>
		<? if (!TooManyNotes($userid)) { ?>
		<p style="margin: 0px; font-weight: bold">New Note:</p>
		<form action="notepad/" method="POST">
		<div>
		<textarea name="entrytext" rows="3" cols="22"></textarea>
		<br><input type="hidden" name="go" value="Save"><input type="submit" value="Save">
		</div>
		</form>
		<?
		}
		$entries = mysql_query('SELECT NOTEPAGE_ID, NOTEPAGE_VALUE, NOTEPAGE_MODIFIED as TS FROM NOTEPAGE_LIST WHERE NOTEPAGE_OWNER=' . $userid . ' ORDER BY NOTEPAGE_MODIFIED DESC LIMIT 3') or die('Query failed.');
		if(mysql_num_rows($entries) > 0)
		{
			print '<p style="margin: 0px; font-weight: bold">Most Recent:</p>';
			while($l = mysql_fetch_array($entries, MYSQL_ASSOC))
				print '<p style="margin: 0px;"><a style="font-weight: bold" href="notepad/index.php?mode=view&amp;id=' . $l['NOTEPAGE_ID'] . '">' . date('n/j', strtotime($l['TS'])) . '</a> ' .  htmlspecialchars(shorten_string(nl2slash($l['NOTEPAGE_VALUE']), 40)) . '</p>';
		}
		print '</div>';
        print "\n</div>";
}

function calendar_block($frontbox,$userR) { ?>
		<?
            $userid = $userR['USER_ID'];
            box_header('My Calendar',$userid,$content_state,"/calendar","red");
            if($content_state=="expanded")
                print '<div class="content">';
            else
                print '<div class="content" style="display: none">'; 
        ?>
		<div class="headed">
		<ul class="flat" style="margin: 0px">
		<?
		$applics = mysql_query('SELECT LAYER_ID, LAYER_TITLE, COUNT(LAYERUSER_LIST.LAYERUSER_ID) AS C FROM LAYERUSER_LIST AS ME_LIST INNER JOIN LAYER_LIST ON ME_LIST.LAYERUSER_LAYER=LAYER_ID INNER JOIN LAYERUSER_LIST ON LAYER_ID=LAYERUSER_LIST.LAYERUSER_LAYER WHERE ME_LIST.LAYERUSER_ACCESS=3 AND ME_LIST.LAYERUSER_USER=' . $userid . ' AND LAYERUSER_LIST.LAYERUSER_ACCESS=0 GROUP BY LAYER_ID') or die("Query failed");
		if(mysql_num_rows($applics) > 0)
		{
			print '<li><span style="font-weight: bold">Alerts</span><ul class="flat">';
			while($l = mysql_fetch_array($applics, MYSQL_ASSOC))
				print '<li>' . $l['C'] . ' person(s) applied to join <a href="calendar/layer.php?viewset=' . $l['LAYER_ID'] . '">' . $l['LAYER_TITLE'] . '</a>.</li>';
			print '</ul></li>';
		}
		mysql_free_result($applics);

		$seldate = makecuridf(8);
		$seldayno = idfd($seldate);
		$selmonthno = idfm($seldate);
		$selyearno = idfYY($seldate);
		$firstday = $seldate;
		$lastday = makeidf($selmonthno, $seldayno + 4, $selyearno);
		$nextday = $firstday;

		$result = mysql_query('SELECT (LENGTH(EVENT_DESC) > 0) AS YES_DESC, LAYER_CLASS, EVENT_LIST.*, LAYER_TITLE, LAYERUSER_COLOR
			FROM LAYERUSER_LIST LEFT JOIN CALCOLOR_LIST ON LAYERUSER_COLOR=CALCOLOR_ID INNER JOIN LAYER_LIST ON LAYERUSER_LAYER=LAYER_ID INNER JOIN EVENT_LIST ON LAYER_ID=EVENT_LAYER
			WHERE LAYERUSER_ACCESS > 0 AND  LAYERUSER_USER=' . $userid . ' AND LAYERUSER_DISPLAY=1 AND
				EVENT_DATE>=' . $firstday . ' AND EVENT_RECUR=\'none\' AND EVENT_DATE<' . $lastday . ' AND LAYERUSER_ACCESS>0
			ORDER BY EVENT_DATE, EVENT_TIME, EVENT_ID') or die('Calendar query failed: ' . mysql_error());

		$repeats = mysql_query('SELECT (LENGTH(EVENT_DESC) > 0) AS YES_DESC, LAYER_CLASS, EVENT_LIST.*, LAYER_TITLE, LAYERUSER_COLOR
			FROM LAYERUSER_LIST LEFT JOIN CALCOLOR_LIST ON LAYERUSER_COLOR=CALCOLOR_ID INNER JOIN LAYER_LIST ON LAYERUSER_LAYER=LAYER_ID INNER JOIN EVENT_LIST ON LAYER_ID=EVENT_LAYER
			WHERE LAYERUSER_ACCESS > 0 AND LAYERUSER_USER=' . $userid . ' AND LAYERUSER_DISPLAY=1 AND
				(EVENT_RECUREND=0 OR EVENT_RECUREND>=' . $firstday . ') AND EVENT_RECUR!=\'none\' AND EVENT_DATE<' . $lastday . ' AND LAYERUSER_ACCESS>0
			ORDER BY EVENT_TIME, EVENT_TITLE, EVENT_ID') or die('Repeats query failed');

		$numreps = mysql_num_rows($repeats);

		for($i = 0; $i < $numreps; $i++)
			$repeatnr[$i] = mysql_fetch_array($repeats, MYSQL_ASSOC);

		if(mysql_num_rows($result) > 0)
		{
			$ll = mysql_fetch_array($result, MYSQL_ASSOC);
			$dwrs = false;
		}
		else
			$dwrs = true;

		$firstday = true;

		while($nextday < $lastday)
		{

			$cj = idfj($nextday);
			$cm = idfn($nextday);
			$cy = idfYY($nextday);

			print '<li><span style="font-weight: bold">' . idfl($nextday) . ' ' . $cj . ' ' . idfFF($nextday) . '</span>';

			print '<ul class="flat">';

			$firstday = false;

			$dwd = ($ll["EVENT_DATE"] > $nextday);
			$j = 0;

			$cursched = 0;

			$eventstoday = false;

			while($j < $numreps || !($dwrs || $dwd))
			{
				if($j >= $numreps)
					$loadrecur = false;
				else if($dwrs || $dwd)
					$loadrecur = true;
				else if($repeatnr[$j]["EVENT_TIME"] < $ll["EVENT_TIME"])
					$loadrecur = true;
				else
					$loadrecur = false;

				if($loadrecur)
				{
					$l = $repeatnr[$j];
					$j++;

					$printevent = showrecur($nextday, $l['EVENT_DATE'], $l['EVENT_RECUR'], $l['EVENT_RECUREND'], $l['EVENT_RECURPARAM'], $l['EVENT_RECURFREQ']);
				}
				else
				{
					$l = $ll;
					if($ll = mysql_fetch_array($result, MYSQL_ASSOC))
					{
						if($ll["EVENT_DATE"] > $nextday)
							$dwd = true;
					}
					else
						$dwrs = true;

					$printevent = true;
				}

				if($printevent)
				{
					$eventstoday = true;
					print '<li>';
					if($l['EVENT_TIME'] != -1)
						print '<span style="font-weight: bold">' . dateTIME(fromSeconds($l['EVENT_TIME'])) . '</span> ';
					print '<a href="calendar/event.php?view=m&amp;start=' . $HGVstart . '&amp;viewset=' . $HGVviewset . '&amp;open=' . $l['EVENT_ID'] . '">' . htmlentities($l['EVENT_TITLE']) . '</a>';
					if($l['YES_DESC'])
						print '<span title="description available">...</span>';
					print ' (' . $l['LAYER_TITLE'] . ')';
					print '</li>';
				}
			}

			if(!$eventstoday)
				print '<li>None</li>';

			print '</ul></li>';

			$nextday = makeidf($cm, $cj + 1, $cy);
		}
		mysql_free_result($result);

		print '</ul>';

		//display my groups!
		$rsmygroups = mysql_query('SELECT LAYER_TITLE, LAYER_ID, LAYERUSER_ID, LAYERUSER_DISPLAY, LAYERUSER_ACCESS
								FROM LAYERUSER_LIST INNER JOIN LAYER_LIST ON LAYERUSER_LAYER = LAYER_ID
								WHERE LAYERUSER_ACCESS > 0 AND LAYERUSER_USER = ' . $userid . ' ORDER BY LAYERUSER_ACCESS DESC, LAYER_TITLE');
		if(mysql_num_rows($rsmygroups) > 0)
			print '<div style="font-weight: bold">My Groups</div><ul class="flat">';
		while($mygroups = mysql_fetch_array($rsmygroups, MYSQL_ASSOC))
		{
		print '<li><a href="./calendar/calendar.php?view=m&amp;start=' . $seldate . '&amp;viewset=' . $mygroups['LAYER_ID'] . '">' . $mygroups['LAYER_TITLE'] . '</a></li>';
		}

		?>
	</ul></div>
	<div class="footer"><a href="calendar/">My Calendar...</a></div>
    </div>
<? }

function news_block($frontbox,$userR,$block)
{
		$tracks = mysql_query("SELECT ASBXTRACK_ID, ASBXTRACK_SHORT, MAX(ASBX_ID) AS LASTPOST
	FROM ASBXTRACK_LIST
	LEFT JOIN ASBX_LIST ON ASBX_TRACK=ASBXTRACK_ID
	LEFT JOIN ASBXUSER_LIST ON ASBXUSER_TRACK=ASBXTRACK_ID
	WHERE ASBXTRACK_ID = " . $block . " GROUP BY ASBXTRACK_ID ORDER BY LASTPOST DESC");

	if($ctrack = mysql_fetch_array($tracks, MYSQL_ASSOC))
	{
		box_header($ctrack['ASBXTRACK_SHORT'] . ' News',$userR['USER_ID'],$content_state,'news/?id=' . $ctrack['ASBXTRACK_ID'],"blue","My Class News");
        if($content_state=="expanded")
            print '<div class="content">';
        else
            print '<div class="content" style="display: none">';
        print '<div class="headed">';

		$rsnews = mysql_query('SELECT ASBX_LIST.*, USER_FULLNAME, USER_ID, ASBXUSER_TITLE FROM ASBX_LIST
			LEFT JOIN ASBXUSER_LIST ON ASBXUSER_USER = ASBX_USER AND ASBXUSER_TRACK = ASBX_TRACK
			INNER JOIN USER_LIST ON USER_ID = ASBX_USER
			WHERE ASBX_ID="' . $ctrack['LASTPOST'] . '"');

		if($news = mysql_fetch_array($rsnews, MYSQL_ASSOC))
		{
			$timedate = strtotime($news['ASBX_TS']);
			print '<div class="newsDateline"><span style="font-weight: bold">' . date('j F Y, g:i A', $timedate) . '</span> by <a href="/directory/?id=' . $news['USER_ID'] . '">' . $news['USER_FULLNAME'] . '</a></div>';
			print '<div class="newsHeadline">' . $news['ASBX_SUBJ'] . '</div>';
			print '<div class="newsContent">';
			print '<p>' . ereg_replace("([^\n])\n+([^\n])", '\\1</p><p>\\2', shorten_string($news['ASBX_MSG'], 360)) . '</p>';
			print '</div>';
			print '</div>';
			print '<div class="footer"><a href="news/?id=' . $ctrack['ASBXTRACK_ID'] . '">Read More...</a>';
		}
		else
			print 'This group has no news at this time.';

		print '</div>';
        print '</div>';
	}
}

function grade_block($frontbox,$userR)
{
	$grade = $userR['USER_GR'];
	$gradeblockR = mysql_query('SELECT * FROM ASBXTRACK_LIST WHERE ASBXTRACK_GR = ' . $grade );
	if($gradeblock = mysql_fetch_array($gradeblockR, MYSQL_ASSOC))
		news_block($frontbox,$userR,$gradeblock['ASBXTRACK_ID']);
}

function rss_block($frontbox,$userR,$title,$url,$href = "",$itemdesc = false)
{
	box_header($title,$userR['USER_ID'],$content_state,$href);
    if($content_state=="expanded")
        print '<div class="content">';
    else
        print '<div class="content" style="display: none">';
    print '<div class="headed">';

	rss_content($url,false,$itemdesc);
	
	print '</div>';
    print '</div>';
}

function rss_content($url,$feedtitle = false,$itemdesc = false)
{
	$rss = @fetch_rss( $url );

	if(!$rss)
		print '<p class="rsslink">RSS Feed does not exist or is invalid.</p>';
		
	if($feedtitle)
		print '<p class="rsslink"><a href="' . $rss->channel['link'] . '">' . $rss->channel['title'] . '</a></p>';
	
	$count = count($rss->items);
	for ($i = 0; $i < 3 && $i < $count; $i++) {
		$item = $rss->items[$i];
		$href = $item['link'];
		$title = $item['title'];
		$desc = $item['description'];
		if(strlen($title) < 1)
			$title = $item['pubdate'];
		print '<p class="rsslink"><a href="' . $href . '">' . $title . '</a>';
		if($itemdesc)
			print '<br>' . $desc;
		print '</p>';
	}
}

function fark_block($frontbox,$userR)
{
	$url = "http://www.fark.com/fark.rss";

	$rss = @fetch_rss( $url );
	
	box_header("FARK",$userR['USER_ID'],$content_state,"http://www.fark.com/");
	
	if($content_state=="expanded")
        print '<div class="content">';
    else
        print '<div class="content" style="display: none">';
    print '<div class="headed">';
	
	if(!$rss)
		print '<p class="rsslink">Error</p>';
	
	$count = count($rss->items);
	for ($i = 0; $i < 3 && $i < $count; $i++) {
		$item = $rss->items[$i];
		$href = $item['link'];
		$title = $item['title'];
		preg_match("/\[(\w+?)\] /", $title, $matches);
		$image = $matches[1];
		$image = '<img style="border: 0px;" src="/fark/' . $image . '.gif" alt="' . $image . '">';
		$title = preg_replace("/\[\w+?\]/ ",'',$title);
		
		if( preg_match("/Weeners|Boobies/i",$image ) )
		{
			array_splice($rss->items,$i,1);
			$i--;
			continue;
		}
		print '<p class="rsslink">' . $image . ' <a href="' . $href . '">' . $title . '</a></p>';
	}
	
	print '</div>';
    print '</div>';
}

function weather_block($frontbox,$userR,$loc_id) {
	// Set Local variables
	if(!$loc_id)
		$location = 95070;
	else
		box_header('Weather for 95070',$userR['USER_ID'],$content_state,"","red");
	$partner_ID = "1010740996";
	$license_key = "628e4891acd91e41";
	$length = 10; // Forecast length
	$image_size = "32x32"; // 32x32, 64x64, or 128x128 - size of daily weather images

	// First URL for searching, second for detail.
	$search_url = "http://xoap.weather.com/search/search?where=$location";
	$forecast_url = "http://xoap.weather.com/weather/local/$loc_id?cc=*&dayf=$length&prod=xoap&par=$partner_ID&key=$license_key";

	/*
	cc	Current Conditions OPTIONAL VALUE IGNORED
	dayf	Multi-day forecast information for some or all forecast elements OPTIONAL VALUE = [ 1..10 ]
	link	Links for weather.com pages OPTIONAL VALUE = xoap
	par	Application developers Id assigned to you REQUIRED VALUE = {partner id}
	prod	The XML Server product code REQUIRED VALUE = xoap
	key	The license key assigned to you REQUIRED VALUE = {license key}
	unit	Set of units. Standard or Metric OPTIONAL VALUES = [ s | m ] DEFAULT = s
	*/

	if ($location) // Determine URL to use. If location is passed, we're searching for a city or zip. Elese we're retrieving a forecast.
	{
		$url = $search_url;
	}
	else
	{
		$url = $forecast_url;
	}

	if ($location Or $loc_id) // If city, zip, or weather.com city id passed, do XML query. $loc_id is a weather.com city code, $location is user entered city or zip
	{
		/* 
		query db for md5 of url
		if doesn't exist, insert into db
		if exists, check date, if under X hours use db content
		if older then X hours, pull from weather.com and update db
		to delete old data: when querying, delete all records older than X hours
		*/
		
		$datetime = date("Y-m-d h:i:s");
		$xml_url = md5($url);
		$interval = 12;	// Hours to keep data in db before being considered old
		$expires = $interval*60*60;
		$expiredatetime = date("Y-m-d H:i:s", time() - $expires);
	
		// Delete expired records
		$query = "DELETE FROM weather_xml WHERE last_updated < '$expiredatetime'";
		$result = mysql_query($query) or die('Invalid query: ' . mysql_error());
	
		$query = "SELECT * FROM weather_xml WHERE xml_url = '$xml_url'"; 
		$result = mysql_query($query) or die('Invalid query: ' . mysql_error());
		$row = mysql_fetch_array($result);
		$time_diff = strtotime($datetime) - strtotime($row['last_updated']);
	
		if (mysql_num_rows($result) < 1) // Data not in table - Add.
		{
			
			// Get XML Query Results from Weather.com
			$fp = fopen($url,"r");
			while (!feof ($fp))
				$xml .= fgets($fp, 4096);
			fclose ($fp);
	
			// Fire up the built-in XML parser
			$parser = xml_parser_create(  ); 
			xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	
			// Set tag names and values
			xml_parse_into_struct($parser,$xml,$values,$index); 
	
			// Close down XML parser
			xml_parser_free($parser);
	
			$xml = str_replace("'","",$xml); // Added to handle cities with apostrophies in the name like T'Bilisi, Georgia
	
			if ($loc_id) // Only inserts forecast feed, not search results feed, into db
			{
				$query = "INSERT INTO weather_xml VALUES ('$xml_url', '$xml', '$datetime')";
				$result = mysql_query($query) or die('Invalid query: ' . mysql_error());
			}
	
		}
		else // Data in table, and it is within expiration period - do not load from weather.com and use cached copy instead.
		{
			$xml = $row['xml_data'];
	
			// Fire up the built-in XML parser
			$parser = xml_parser_create(  ); 
			xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	
			// Set tag names and values
			xml_parse_into_struct($parser,$xml,$values,$index); 
	
			// Close down XML parser
			xml_parser_free($parser);
		}
	}

	if ($loc_id) // Location code selected - Display detail info. A specific city has been selected from the drop down menu. Get forecast.
	{
		if($content_state=="expanded")
            print '<div class="content">';
        else
            print '<div class="content" style="display: none">';
        print '<div class="headed">';
		$city = htmlspecialchars($values[$index[dnam][0]][value]);
		$unit_temp = $values[$index[ut][0]][value];
		$unit_speed = $values[$index[us][0]][value];
		$unit_precip = $values[$index[up][0]][value];
		$unit_pressure = $values[$index[ur][0]][value];
		$sunrise = $values[$index[sunr][0]][value];
		$sunset = $values[$index[suns][0]][value];
		$timezone = $values[$index[tzone][0]][value];
		$last_update = $values[$index[lsup][0]][value];
		$curr_temp = $values[$index[tmp][0]][value];
		$curr_flik = $values[$index[flik][0]][value];
		$curr_text = $values[$index[t][0]][value];
		$curr_icon = $values[$index[icon][0]][value];
		$counter = 0;
		$row_counter = 2;
	
		echo "<span style=\"font-size: 10px;\">(Last updated $last_update).</span><br>\n";
		echo "<p><img style=\"border: 1px solid #000000; margin: 10px; float: left;\" src=\"http://www.notonebit.com/images/weather/64x64/$curr_icon.png\" alt=\"$curr_text\">";
		echo "<span style=\"font-size: 14px;\">Currently: <b>$curr_temp&#730; $unit_temp</b></span><br>\n";
		echo "Feels Like: $curr_flik&#730; $unit_temp<br>Current conditions: $curr_text<br>\n";
		echo "Sunrise: $sunrise.<br>Sunset: $sunset.<br>\n";
		echo "</p>";
		echo "</div>";
        echo "</div>";
	}

	if ($location And is_array($index[loc])) // A city name has been entered and data returned from weather.com, draw drop down menu of matches
	{
		if (count($index[loc]) == 1) // If just one match returned, send to detail screen - no need to draw option box for one option.
		{
			$location_code = $values[$index[loc][0]][attributes][id];
			weather_block($frontbox,$userR,$location_code);
		}
	}
}

function google_block($frontbox,$userR)
{
	box_header("Google",$userR['USER_ID'],$content_state,"http://www.google.com/");
    if($content_state=="expanded")
        print '<div class="content">';
    else
        print '<div class="content" style="display: none">';
?>
    <div class="headed">
	<span style="font-weight: bold;">Google Search:</span>
	<form action="/index.php?google" method="POST">
	<div>
	<input type="text" name="query" style="width: 90%;">
	<br><label><input type="radio" name="type" value="web" checked> Web</label>
	<label><input type="radio" name="type" value="images"> Images</label>
	<label><input type="radio" name="type" value="maps"> Maps</label>
	<label><input type="radio" name="type" value="news"> News</label>
	<input type="submit" value="Go">
	</div>
	</form>
	</div>
    </div>
<? }

function xanga_block($frontbox,$userR)
{
	box_header("Xanga",$userR['USER_ID'],$content_state,"http://www.xanga.com/");
    if($content_state=="expanded")
        print '<div class="content">';
    else
        print '<div class="content" style="display: none">';
?>
    <div class="headed">
	<span style="font-weight: bold;">Xanga User:</span>
	<form action="/index.php" method="POST" onSubmit="return get_xanga();">
	<div>
	<input id="xanga_query" type="text" name="query" style="width: 70%;"> <input type="submit" value="Go">
	</div>
	</form>
	
	<script type="text/javascript">
	<!--
	function get_xanga() {
		var rss_url = "http://www.xanga.com/rss.aspx?user=";	
		rss_url += document.getElementById("xanga_query").value;
	
		xmlhttp.open("POST", "/index.php",true);
		xmlhttp.onreadystatechange=function()
		{
			if (xmlhttp.readyState==4)
			{
				document.getElementById("xanga_content_box").innerHTML = xmlhttp.responseText;
				}
			}
		xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		xmlhttp.send('XMLRequest=1&XMLFunction=rss_content&XMLArgs=a:2:{i:0;s:' + rss_url.length + ':"' + rss_url + '";i:1;b:1;}');
		return false;
	}
	// -->
	</script>
	
	<div id="xanga_content_box"></div>
	</div>
    </div>
<? }

function livejournal_block($frontbox,$userR)
{
	box_header("Livejournal",$userR['USER_ID'],$content_state,"http://www.livejournal.com/");
    if($content_state=="expanded")
        print '<div class="content">';
    else
        print '<div class="content" style="display: none">';
?>
    <div class="headed">
	<span style="font-weight: bold;">Livejournal:</span>
	<form name="livejournal_form" action="/index.php" method="POST" onSubmit="return get_livejournal();">
	<div>
	<input id="livejournal_query" type="text" name="query" style="width: 70%;"> <input type="submit" value="Go">
	<br><label><input type="radio" name="type" value="users" checked> User</label>
	<label><input type="radio" name="type" value="community"> Community</label>
	</div>
	</form>
	
	<script type="text/javascript">
	<!--
	function get_livejournal() {
		var rss_url = "http://www.livejournal.com/";
		rss_url += getCheckedValue(document.livejournal_form.type) + "/";
		rss_url += document.getElementById("livejournal_query").value + "/data/rss";
	
		xmlhttp.open("POST", "/index.php",true);
		xmlhttp.onreadystatechange=function()
		{
			if (xmlhttp.readyState==4)
			{
				document.getElementById("livejournal_content_box").innerHTML = xmlhttp.responseText;
				}
			}
		xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		xmlhttp.send('XMLRequest=1&XMLFunction=rss_content&XMLArgs=a:2:{i:0;s:' + rss_url.length + ':"' + rss_url + '";i:1;b:1;}');
		return false;
	}
	// -->
	</script>
	
	<div id="livejournal_content_box"></div>
	</div>
    </div>
<? }

function task_block($frontbox,$userR)
{
	if($userR['USER_STATUS'] < 1)
		return;
		
	box_header("Project Tasks",$userR['USER_ID'],$content_state,"/shcp/tasklist.php");
	
	$ts = getdate(CURRENT_TIME);
	$earliest_date = date(TIME_FORMAT_SQL, mktime($ts['hours'], $ts['minutes'], $ts['seconds'], $ts['mon'], $ts['mday'] - 7, $ts['year']));
	
	if($content_state=="expanded")
        print '<div class="content">';
    else
        print '<div class="content" style="display: none">';
    print '<div class="headed">';
	print '<table style="width: 100%" cellpadding="0" cellspacing="1" border="0">';
	print '<tr><td style="font-weight: bold"><a href="/shcp/tasklist.php">Active Tasks</a></td><td style="text-align: right">#</td><td style="text-align: right">&#0931;</td></tr>';
	$tasks = mysql_query('SELECT TASKPRIORITY_LIST.*, COUNT(TASK_ID) AS C FROM TASK_LIST
		INNER JOIN TASKPRIORITY_LIST ON TASK_PRIORITY=TASKPRIORITY_ID
		INNER JOIN TASKCAT_LIST ON TASK_CAT=TASKCAT_ID
		WHERE TASK_ARCHIVED=0
		GROUP BY TASKPRIORITY_ID ORDER BY TASKPRIORITY_ID');
	$total = 0;
	while($task = mysql_fetch_array($tasks, MYSQL_ASSOC))
	{
		$total += $task['C'];
		print '<tr><td style="';
		print 'padding-left: 1em; ' . $task['TASKPRIORITY_STYLESTR'] . '">' . $task['TASKPRIORITY_NAME'] . '</td>
		<td style="text-align: right">' . $task['C'] . '</td><td style="text-align: right">' . $total . '</td></tr>';
	}

	$rsfinished = mysql_query("SELECT TASK_ID, TASK_TITLE, USER_FN, USER_LN, TASK_CREATED
		FROM TASK_LIST
			INNER JOIN TASKCAT_LIST ON TASK_CAT=TASKCAT_ID
			INNER JOIN USER_LIST ON TASK_AUTHOR=USER_ID
		WHERE TASK_ARCHIVED=0 AND TASK_CREATED>='$earliest_date'");
	
	print '<tr><td style="font-weight: bold">New</td><td style="text-align: right">' . mysql_num_rows($rsfinished) . '</td><td></td></tr>';
	
	while($finished = mysql_fetch_array($rsfinished, MYSQL_ASSOC))
	{
		print '<tr><td colspan="3"><p style="text-indent: -1em; margin: 0 0 0 2em; font-size: x-small"><a href="/shcp/edittask.php?edit=' . $finished['TASK_ID'] . '">' . $finished['TASK_TITLE'] . '</a> ' . substr($finished['USER_FN'], 0, 1) . substr($finished['USER_LN'], 0, 1) . '&nbsp;' . date('n/j', strtotime($finished['TASK_CREATED'])) . '</p></td></tr>';
	}

	$archived = mysql_query("SELECT COUNT(*) AS C FROM TASK_LIST WHERE TASK_ARCHIVED=1");
	$numarchived = mysql_fetch_array($archived, MYSQL_ASSOC);
	
	print '<tr><td style="font-weight: bold">Archived</td><td style="text-align: right">' . $numarchived['C'] . '</td><td></td></tr></table>';
	print '</div>';
    print '</div>';
}

function classes_block($frontbox_id,$userR)
{
	$userid = $userR['USER_ID'];
	$isstudent = IsStudent($userR['USER_GR']);
	$isteacher = !is_null($userR['USER_TEACHERTAG']);
	$usertag = $userR['USER_TEACHERTAG'];
	box_header('My Classes',$userid,$content_state,'/directory/?id=' . $userid);
    if($content_state=="expanded")
        print '<div class="content">';
    else
        print '<div class="content" style="display: none">';
?>
    <div class="headed red">
<?	if(SITE_ACTIVE)
	{
		if($isstudent)
		{
			$classes = mysql_query('SELECT CLASS_NAME, CLASSLINK_URL, SCHED_ID, SCHED_PER, SCHED_CLASS, SCHED_TEACHER
			FROM SCHED_LIST
				INNER JOIN CLASS_LIST ON SCHED_CLASS=CLASS_ID
				LEFT JOIN CLASSLINK_LIST ON CLASSLINK_COURSE=CLASS_ID AND CLASSLINK_TEACHER=SCHED_TEACHER AND CLASSLINK_TYPE="Class Website"
			WHERE
				SCHED_YEAR=' . C_SCHOOLYEAR . ' AND
				SCHED_USER=' . $userid . ' AND
				(SCHED_TERM="YEAR" OR SCHED_TERM="' . C_SEMESTER . '")
			ORDER BY SCHED_PER, SCHED_TERM') or die(mysql_error());
	
			if(mysql_num_rows($classes) > 0)
			{
				print '<ul class="flat" style="margin: 0px;">';
	
				while($cclass = mysql_fetch_array($classes, MYSQL_ASSOC))
				{
					print '<li><span style="font-weight: bold"><span style="color: #999999">' . $cclass['SCHED_PER'] . '</span> <a href="/cm/?class=' . $cclass['SCHED_CLASS'] . '&amp;teacher=' . $cclass['SCHED_TEACHER'] . '">' . $cclass['CLASS_NAME'] . '</a></span></li>';
					
					print '<li><ul class="flat">';
	
					if(isset($cclass['CLASSLINK_URL']))
						print '<li><span style="font-style: italic"><a href="' . $cclass['CLASSLINK_URL'] . '">View homework website</a></span></li>';
					else
						print '<li><span style="color: #999999">No information available.</span></li>';
	
					print '</ul></li>';
				}
					
				print '</ul>';
			}
			else
				print '<p style="margin: 0px">If you enter the classes in your schedule, you can see them in this space.</p>';
		}
		else if($isteacher)
		{
			$classes = mysql_query('SELECT DISTINCT CLASS_NAME, VALIDCLASS_COURSE, VALIDCLASS_TEACHER, VALIDCLASS_PER FROM VALIDCLASS_LIST INNER JOIN CLASS_LIST ON VALIDCLASS_COURSE=CLASS_ID WHERE VALIDCLASS_TEACHER=' . $usertag . ' ORDER BY VALIDCLASS_PER') or die('Class query failed.');
	
			if(mysql_num_rows($classes) > 0)
			{
				print '<li><ul class="flat" style="margin: 0px;">';
	
				while($cclass = mysql_fetch_array($classes, MYSQL_ASSOC))
				{
					$numclasses++;
				
					print '<li><span style="font-weight: bold"><span style="color: #999999">' . $cclass['VALIDCLASS_PER'] . '</span> <a href="/cm/?class=' . $cclass['VALIDCLASS_COURSE'] . '&amp;teacher=' . $cclass['VALIDCLASS_TEACHER'] . '">' . $cclass['CLASS_NAME'] . '</a></span></li>';
				}
			}
			else
				print 'You are not assigned any classes.';

			print '</ul></li>';
		}
		else
			print "You shouldn't have a schedule.";
	}
	else
		print 'Sorry, you cannot enter your schedule yet.';

	print '</div>';
    print '</div>';
}
?>