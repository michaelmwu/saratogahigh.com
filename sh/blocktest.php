<?
if(isset($_GET['google']))
{
	$type = $_POST['type'];
	$query = $_POST['query'];
	
	if($type == 'Web')
	{
		header('Location: http://www.google.com/search?q=' . urlencode($query) . '&svnum=10&hl=en&lr=&tab=nw&ie=UTF-8&sa=N');
	}
	else if($type == 'Images')
	{
		header('Location: http://images.google.com/images?svnum=10&hl=en&lr=&q=' . urlencode($query) . '&btnG=Search');
	}
	else if($type == 'Maps')
	{
		header('Location: http://maps.google.com/maps?q=' . urlencode($query));
	}
	else if($type == 'News')
	{
		header('Location: http://news.google.com/news?svnum=10&hl=en&lr=&tab=in&ie=UTF-8&q=' . urlencode($query) . '&btnG=Search+News');
	}
}

function cleanup_boxes_test($boxes)
{
	global $isstudent, $isparent, $isvalidated, $corrupt,$isadmin;

	$used = array();

	if(gettype($boxes) == "array")
	{
		foreach($boxes as $column)
		{
			if(gettype($column) == "array")
			{
				foreach($column as $box)
				{
					if(gettype($box) != "integer" && !preg_match("/^c\d+/",$box))
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
						2 => array( 5 )
					);
		else
			$boxes = array(
						0 => array( 26, 4, 6 ),
						1 => array( 2, 1 ),
						2 => array( 5 )
					);
		if($isstaff)
			$boxes[2] = array( 28, 5 );
	}

	for($i = 0; $i < count($boxes); $i++)
	{
		for( $j = 0; $j < count($boxes[$i]); $j++ )
		{
			if( ($boxes[$i][$j] == 3 && (!$isstudent && !$isparent))
				|| ($boxes[$i][$j] == 5 && !$isvalidated)
				|| (($boxes[$i][$j] == 13 || $boxes[$i][$j] == 28) && !$isadmin)
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

class box
{
	var $id;
	var $userid;
	var $userR;
	
	var $args;
	
	var $frontbox;
	
	var $prefs = array();
	var $default_prefs = array('max' => true);
						
	var $title = "";
	var $htmlid = "";
	var $label = "";
	var $content = "";
	var $content_after = "";
	
	var $h2href = "";
	var $h2class = "red";
	
	function get_pref($key)
	{
		return (array_key_exists($key,$this->prefs)) ? $this->prefs['key'] :
				(array_key_exists($key,$this->default_prefs)) ? $this->default_prefs[$key] :
				false;
	}
	
	function set_pref($key,$value)
	{
		$default = (array_key_exists($key,$this->default_prefs) && $this->default_prefs[$key] == $value);
		if(!$default)
			$this->prefs[$key] = $value;
		else if(array_key_exists($key,$this->prefs))
			unset($this->prefs[$key]);
			
		return true;
	}
	
	function save_prefs()
	{
		if(count($this->prefs) > 0)
		{
			$values = array('FRONTPREF_VALUE' => serialize($this->prefs),
							'FRONTPREF_USER' => $this->userid,
							'FRONTPREF_BOX' => $this->id);
							
			db::prefix_update("FRONTPREF",$values,"FRONTPREF_USER='" . $this->userid . "' AND FRONTPREF_BOX='" . $this->id . "' AND FRONTPREF_KEY=''");
		}
		else
			db::prefix_delete("FRONTPREF","FRONTPREF_USER='" . $this->userid . "' AND FRONTPREF_BOX='" . $this->id . "' AND FRONTPREF_KEY=''");
	}
	
	function box($frontbox_id)
	{
		$this->userid = $GLOBALS['userid'];
		$this->userR = $GLOBALS['userR'];
		
		if($row = db::get_prefix_row("FRONTPREF",'FRONTPREF_USER="' . $this->userid . '" AND FRONTPREF_BOX="' . $frontbox_id . '" AND FRONTPREF_KEY=""'))
			$this->prefs = unserialize($row['FRONTPREF_VALUE']);
		
		$this->id = $frontbox_id;
		
		if(is_numeric($frontbox_id))
			$this->frontbox = db::get_prefix_id("FRONTBOX",$frontbox_id);
		else if(preg_match("/^c\d+$/i",$frontbox_id))
			return custom_box(&$this);
		else
			return false;
			
		$this->title = $this->frontbox['FRONTBOX_TITLE'];
		$this->htmlid = $this->frontbox['FRONTBOX_HTMLID'];
		$this->label = $this->frontbox['FRONTBOX_TITLE'];
		
		if(is_callable($this->frontbox['FRONTBOX_FUNCTION']))
		{
			$php = '$tmp_array = array( ' . $this->frontbox['FRONTBOX_PHPARGUMENT'] . ' );';
			eval($php);
			
			$this->args = $tmp_array;
			
			array_unshift($tmp_array,&$this);
			
			call_user_func_array($this->frontbox['FRONTBOX_FUNCTION'],$tmp_array);
		}
		
		return true;
	}
	
	function output()
	{
		$content = '<div id="' . $this->htmlid . '" name="' . $this->id . '" class="movebox">';
		$content .= box_header($this);
		$content .= '<div class="content"' . ($this->get_pref('max') == false ? ' style="display: none"' : '') . '>';
		$content .= '<div class="headed">' . $this->content . '</div>';
		if(strlen($this->content_after) > 0)
		{
			$content .= '<div class="footer">' . $this->content_after . '</div>';
		}
		$content .= "</div>\n</div>";
		
		return $content;
	}
}

function custom_box($box)
{
}

function move_boxes($boxes, $column)
{
	$column_boxes = $boxes[$column];
	if(!is_array($column_boxes))
		$column_boxes = array();
	
	foreach( $column_boxes as $current_box )
	{
		$box = new box($current_box);
		print $box->output();
	}
}

function box_header(&$box) // Print out the title part of the box.
{
	$content = '<h2 class="' . $box->h2class . ' boxheader" onMouseOver="startMoveCursor(event);" onMouseDown="startDrag(event);">';
	if($href = $box->h2href)
		$content .= '<a href="' . $href . '">';
	$content .= $box->title;
	if($href)
		$content .= '</a>';
    
    $content .= '<span class="xbox">';
    $content .= '<a href="#" class="expand" onClick="return expandMe(event);"' . ($box->get_pref('max') == true ? ' style="display: none;"' : '') . '><img src="/imgs/down.gif"></a><a href="#" class="minimize" onClick="return minimizeMe(event);"' . ($box->get_pref('max') == false ? ' style="display: none;"' : '') . '><img src="/imgs/up.gif"></a>';
          
	$content .= ' <a href="#" onClick="return removeMe(event);"><img src="/imgs/x.gif"></a></span></h2>';
	
	return $content;
}

function save_boxes($boxes)
{
	print_r($boxes);
	$boxes = cleanup_boxes($boxes);
	$userid = $GLOBALS['userid'];

	mysql_query("UPDATE USER_LIST SET USER_FRONTPAGE = '" . serialize($boxes) . "' WHERE USER_ID='$userid'");
}

function save_box_prefs($box_id, $key, $value)
{
	$box = new box($box_id);
	$box->set_pref($key,$value);
	$box->save_prefs();
}

function mail_block(&$box) {
	$box->h2href = "/mail";

	$content = '<p style="margin-bottom: 6px; margin-top: 0px;">'
		. '<form style="margin: 0px" action="mail/compose.php" name="mf" method="POST">'
		. '<p style="margin: 0px">To: (type full name)</p>'
		. '<p style="margin: 0px"><input onfocus="document.getElementById(\'mailcomposebox\').style.display = \'block\'; document.getElementById(\'mailcomposearrow\').style.display = \'none\';" style="width: 90%;" type="text" name="xto" value="" size="55"><img src="/imgs/arrow-down.gif" onclick="document.getElementById(\'mailcomposebox\').style.display = \'block\'; document.getElementById(\'mailcomposearrow\').style.display = \'none\';" id="mailcomposearrow" style="vertical-align: middle" alt="(more)"></p>'
		. '<div style="display: none" id="mailcomposebox">'
		. '<p style="margin: 0px">Subject:<br><input style="width: 90%" type="text" name="xsubj" value="" size="40"></p>'
		. '<p style="margin: 0px"><textarea name="xmsgtxt" rows="3" style="width: 90%" cols="55"></textarea></p>'
		. '<p style="margin: 0px; font-weight: bold; text-align: right"><input type="submit" name="go" value="Send"></p>'
		. '</div>'
		. '</form>';

	$box->content = $content;
	$box->content_after = '<a href="mail/">More Mail...</a>';
}

function notes_block(&$box)
{
	$userid = $box->userid;
	$box->h2href = "/notepad";

	$content = '<p style="margin: 0px"><a href="notepad/">';

	$cr = mysql_query('SELECT COUNT(*) FROM NOTEPAGE_LIST WHERE NOTEPAGE_OWNER=' . $userid) or die("Query failed");
	$l = mysql_fetch_array($cr, MYSQL_ASSOC);
	$content .= $l['COUNT(*)'] . ' note(s) saved</a>.</p>';

	if (!TooManyNotes($userid))
	{
		$content .= '<p style="margin: 0px; font-weight: bold">New Note:</p>'
			. '<form action="notepad/" method="POST">'
			. '<div>'
			. '<textarea name="entrytext" rows="3" cols="22"></textarea>'
			. '<br><input type="hidden" name="go" value="Save"><input type="submit" value="Save">'
			. '</div>'
			. '</form>';
	}
	$entries = mysql_query('SELECT NOTEPAGE_ID, NOTEPAGE_VALUE, NOTEPAGE_MODIFIED as TS FROM NOTEPAGE_LIST WHERE NOTEPAGE_OWNER=' . $userid . ' ORDER BY NOTEPAGE_MODIFIED DESC LIMIT 3') or die('Query failed.');
	if(mysql_num_rows($entries) > 0)
	{
		$content .= '<p style="margin: 0px; font-weight: bold">Most Recent:</p>';
		while($l = mysql_fetch_array($entries, MYSQL_ASSOC))
			$content .= '<p style="margin: 0px;"><a style="font-weight: bold" href="notepad/index.php?mode=view&amp;id=' . $l['NOTEPAGE_ID'] . '">' . date('n/j', strtotime($l['TS'])) . '</a> ' .  htmlspecialchars(shorten_string(nl2slash($l['NOTEPAGE_VALUE']), 40)) . '</p>';
	}

	$box->content = $content;
}

function calendar_block(&$box)
{
	$userid = $box->userid;
	$box->h2href = '/calendar';

	$content = '<ul class="flat" style="margin: 0px">';

	$applics = mysql_query('SELECT LAYER_ID, LAYER_TITLE, COUNT(LAYERUSER_LIST.LAYERUSER_ID) AS C FROM LAYERUSER_LIST AS ME_LIST INNER JOIN LAYER_LIST ON ME_LIST.LAYERUSER_LAYER=LAYER_ID INNER JOIN LAYERUSER_LIST ON LAYER_ID=LAYERUSER_LIST.LAYERUSER_LAYER WHERE ME_LIST.LAYERUSER_ACCESS=3 AND ME_LIST.LAYERUSER_USER=' . $userid . ' AND LAYERUSER_LIST.LAYERUSER_ACCESS=0 GROUP BY LAYER_ID') or die("Query failed");
	if(mysql_num_rows($applics) > 0)
	{
		$content .= '<li><span style="font-weight: bold">Alerts</span><ul class="flat">';
		while($l = mysql_fetch_array($applics, MYSQL_ASSOC))
			$content .= '<li>' . $l['C'] . ' person(s) applied to join <a href="calendar/layer.php?viewset=' . $l['LAYER_ID'] . '">' . $l['LAYER_TITLE'] . '</a>.</li>';
		$content .= '</ul></li>';
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

		$content .= '<li><span style="font-weight: bold">' . idfl($nextday) . ' ' . $cj . ' ' . idfFF($nextday) . '</span>'
				. '<ul class="flat">';

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
				$content .= '<li>';
				if($l['EVENT_TIME'] != -1)
					$content .= '<span style="font-weight: bold">' . dateTIME(fromSeconds($l['EVENT_TIME'])) . '</span> ';
				$content .= '<a href="calendar/event.php?view=m&amp;start=' . $HGVstart . '&amp;viewset=' . $HGVviewset . '&amp;open=' . $l['EVENT_ID'] . '">' . htmlentities($l['EVENT_TITLE']) . '</a>';
				if($l['YES_DESC'])
					$content .= '<span title="description available">...</span>';
				$content .= ' (' . $l['LAYER_TITLE'] . ')';
				$content .= '</li>';
			}
		}

		if(!$eventstoday)
			$content .= '<li>None</li>';

		$content .= '</ul></li>';

		$nextday = makeidf($cm, $cj + 1, $cy);
	}
	mysql_free_result($result);

	$content .= '</ul>';

		// Display my groups!
	$rsmygroups = mysql_query('SELECT LAYER_TITLE, LAYER_ID, LAYERUSER_ID, LAYERUSER_DISPLAY, LAYERUSER_ACCESS
							FROM LAYERUSER_LIST INNER JOIN LAYER_LIST ON LAYERUSER_LAYER = LAYER_ID
							WHERE LAYERUSER_ACCESS > 0 AND LAYERUSER_USER = ' . $userid . ' ORDER BY LAYERUSER_ACCESS DESC, LAYER_TITLE');
	if(mysql_num_rows($rsmygroups) > 0)
		$content .= '<div style="font-weight: bold">My Groups</div><ul class="flat">';
	while($mygroups = mysql_fetch_array($rsmygroups, MYSQL_ASSOC))
		$content .= '<li><a href="./calendar/calendar.php?view=m&amp;start=' . $seldate . '&amp;viewset=' . $mygroups['LAYER_ID'] . '">' . $mygroups['LAYER_TITLE'] . '</a></li>';

	$content .= '</ul>';
	
	$box->content = $content;
	$box->contents_after = '<a href="calendar/">My Calendar...</a>';
}

function news_block(&$box,$block)
{
	$content = "";

	$tracks = mysql_query("SELECT ASBXTRACK_ID, ASBXTRACK_SHORT, MAX(ASBX_ID) AS LASTPOST
	FROM ASBXTRACK_LIST
	LEFT JOIN ASBX_LIST ON ASBX_TRACK=ASBXTRACK_ID
	LEFT JOIN ASBXUSER_LIST ON ASBXUSER_TRACK=ASBXTRACK_ID
	WHERE ASBXTRACK_ID = " . $block . " GROUP BY ASBXTRACK_ID ORDER BY LASTPOST DESC");
	
	if($ctrack = mysql_fetch_array($tracks, MYSQL_ASSOC))
	{
		$box->label = $ctrack['ASBXTRACK_SHORT'] . ' News';
		$box->h2class = "blue";
		$box->h2href = '/news/?id=' . $ctrack['ASBXTRACK_ID'];

		$rsnews = mysql_query('SELECT ASBX_LIST.*, USER_FULLNAME, USER_ID, ASBXUSER_TITLE FROM ASBX_LIST
			LEFT JOIN ASBXUSER_LIST ON ASBXUSER_USER = ASBX_USER AND ASBXUSER_TRACK = ASBX_TRACK
			INNER JOIN USER_LIST ON USER_ID = ASBX_USER
			WHERE ASBX_ID="' . $ctrack['LASTPOST'] . '"');

		if($news = mysql_fetch_array($rsnews, MYSQL_ASSOC))
		{
			$timedate = strtotime($news['ASBX_TS']);
			$content .= '<div class="newsDateline"><span style="font-weight: bold">' . date('j F Y, g:i A', $timedate) . '</span> by <a href="/directory/?id=' . $news['USER_ID'] . '">' . $news['USER_FULLNAME'] . '</a></div>'
				. '<div class="newsHeadline">' . $news['ASBX_SUBJ'] . '</div>'
				. '<div class="newsContent">'
				. '<p>' . ereg_replace("([^\n])\n+([^\n])", '\\1</p><p>\\2', shorten_string($news['ASBX_MSG'], 360)) . '</p>'
				. '</div>';

				$box->content_after = '<a href="news/?id=' . $ctrack['ASBXTRACK_ID'] . '">Read More...</a>';
		}
		else
			$content .= 'This group has no news at this time.';
	}
	
	$box->content = $content;
}

function grade_block(&$box)
{
	$grade = $box->userR['USER_GR'];
	$gradeblockR = mysql_query('SELECT * FROM ASBXTRACK_LIST WHERE ASBXTRACK_GR = ' . $grade );
	if($gradeblock = mysql_fetch_array($gradeblockR, MYSQL_ASSOC))
		news_block($box,$gradeblock['ASBXTRACK_ID']);
}

function rss_block(&$box,$label,$url,$href = "")
{
	$box->label = $label;
	$box->h2href = $href;
	$box->default_prefs['itemdesc'] = false;
	$box->default_prefs['itemcount'] = 3;

	$box->content =	rss_content($url,$box->get_pref('itemcount'),false,$box->get_pref('itemdesc'));
}

function rss_content($url,$display = 3,$feedtitle = false,$itemdesc = false)
{
	$rss = @fetch_rss( $url );
	
	$content = "";

	if(!$rss)
		$content .= '<p class="rsslink">RSS Feed does not exist or is invalid.</p>';
		
	if($feedtitle)
		$content .= '<p class="rsslink"><a href="' . $rss->channel['link'] . '">' . $rss->channel['title'] . '</a></p>';
	
	$count = count($rss->items);
	for ($i = 0; $i < $display && $i < $count; $i++) {
		$item = $rss->items[$i];
		$href = $item['link'];
		$title = $item['title'];
		$desc = $item['description'];
		if(strlen($title) < 1)
			$title = $item['pubdate'];
		$content .= '<p class="rsslink"><a href="' . $href . '">' . $title . '</a>';
		if($itemdesc)
			$content .= '<br>' . $desc;
		$content .= '</p>';
	}
	
	return $content;
}

function fark_block(&$box)
{
	$url = "http://www.fark.com/fark.rss";

	$rss = @fetch_rss( $url );
	
	$box->h2href = "http://www.fark.com/";
	
	$content = "";
	
	if(!$rss)
		$content .= '<p class="rsslink">Error</p>';
	
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
		$content .= '<p class="rsslink">' . $image . ' <a href="' . $href . '">' . $title . '</a></p>';
	}
	
	$box->content = $content;
}

function weather_block(&$box,$loc_id) {
	// Set Local variables
	$box->default_prefs['location'] = 95070;
	$box->label = 'Weather for ' . $box->get_pref('location');
	
	if(!$loc_id)
		$location = $box->get_pref('location');

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

	if ($location || $loc_id) // If city, zip, or weather.com city id passed, do XML query. $loc_id is a weather.com city code, $location is user entered city or zip
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
		$content = "";

		$city = htmlspecialchars($values[$index['dnam'][0]][value]);
		$unit_temp = $values[$index['ut'][0]][value];
		$unit_speed = $values[$index['us'][0]][value];
		$unit_precip = $values[$index['up'][0]][value];
		$unit_pressure = $values[$index['ur'][0]][value];
		$sunrise = $values[$index['sunr'][0]][value];
		$sunset = $values[$index['suns'][0]][value];
		$timezone = $values[$index['tzone'][0]][value];
		$last_update = $values[$index['lsup'][0]][value];
		$curr_temp = $values[$index['tmp'][0]][value];
		$curr_flik = $values[$index['flik'][0]][value];
		$curr_text = $values[$index['t'][0]][value];
		$curr_icon = $values[$index['icon'][0]][value];
		$counter = 0;
		$row_counter = 2;
	
		$content .= "<span style=\"font-size: 10px;\">(Last updated $last_update).</span><br>\n"
			. "<p><img style=\"border: 1px solid #000000; margin: 10px; float: left;\" src=\"http://www.notonebit.com/images/weather/64x64/$curr_icon.png\" alt=\"$curr_text\">"
			. "<span style=\"font-size: 14px;\">Currently: <b>$curr_temp&#730; $unit_temp</b></span><br>\n"
			. "Feels Like: $curr_flik&#730; $unit_temp<br>Current conditions: $curr_text<br>\n"
			. "Sunrise: $sunrise.<br>Sunset: $sunset.<br>\n"
			. "</p>";
			
		$box->content = $content;
	}

	if ($location && is_array($index[loc])) // A city name has been entered and data returned from weather.com, draw drop down menu of matches
	{
		$count_locs = count($index[loc]);
		if ($count_locs == 1) // If just one match returned, send to detail screen - no need to draw option box for one option.
		{
			$location_code = $values[$index[loc][0]][attributes][id];
			weather_block($box,$location_code);
			return;
		}
		
		$max_show = 6;
		$show = ($count_locs > $max_show ? $max_show : $count_locs);
		
		$content = '<form action="/index.php">'
			. 'Select desired city:<br>'
			. '<select id="weather_city" size="' . $show . '">';
		for($i = 0;$i < $count_locs;$i++)
		{
			$content .= "\n" . '<option value="' . $index['loc'][$i] . '">' . $index['dnam'][$i];
		}
		$content .= '</select>'
			. '<br><input type="submit" value="Go" onClick="return weather_city();">'
			. '</form>';
			
		$box->content = $content;
	}
}

function google_block($box)
{
	$box->h2href = "http://www.google.com/";
	
	$content = '<div id="googleoptions" class="block_tabs"><span id="Web" style="color: #000000; background-color: #CFCFCF;">Web</span> <span id="Images">Images</span> <span id="Maps"">Maps</span> <span id="News">News</span></div>'
			. '<div style="padding-bottom: 5px;"><form action="/index-blocktest.php?google" method="POST" style="background-color: #CFCFCF;">'
			. '<div style="padding: 5px;">'
			. '<input type="text" name="query" style="width: 90%; padding-left: 5px;">'
			. '<input type="hidden" name="type" id="googletype" value="Web">'
			. '<input type="submit" value="Go">'
			. '</div>'
			. '</form></div>';
			
	$box->content = $content;
	
	$script = '<script type="text/javascript">
			<!--
			var google_children = document.getElementById("googleoptions").childNodes;
			var google_spans = new Array();
			var i;
			for(i=0;i<google_children.length;i++)
			{
				if(google_children[i].tagName == "SPAN")
				{
					google_spans.push(google_children[i]);
					EventUtils.addEventListener(google_children[i],"click",google_select);
				}
			}
			
			function google_select(evt)
			{
				evt = new Evt(evt);
				element = evt.getSource();
				
				document.getElementById("googletype").value = element.id;
				for(i = 0;i < google_spans.length;i++)
				{
					google_spans[i].style.color = "#A0A0A0";
					google_spans[i].style.backgroundColor = "#F0F0F0";
				}
				
				element.style.color = "#000000";
				element.style.backgroundColor = "#CFCFCF";
			}
			// -->
			</script>';
			
	$box->content_after = $script;
}

function blog_block(&$box)
{
	$box->h2href = "http://www.xanga.com/";

	$content = '<label><input type="radio" name="type" value="xanga"> Xanga</label>'
			 . '<label><input type="radio" name="type" value="livejournal"> LiveJournal</label>'
			 . '<div id="blog_box">'
			 . '<input id="livejournal_query" type="text" name="query" style="width: 70%;"> <input type="submit" value="Go">
	<br><label><input type="radio" name="type" value="users" checked> User</label>
	<label><input type="radio" name="type" value="community"> Community</label>
	</div>'
			 . '<span style="font-weight: bold;">Xanga User:</span>
	<form action="/index.php" method="POST" onSubmit="return get_xanga();">
	<div>
	<input id="xanga_query" type="text" name="query" style="width: 70%;"> <input type="submit" value="Go">
	</div>
	</form>
	
	<script type="text/javascript">
	<!--
	function get_blog(name,type) {
		var rss_url = "http://www.xanga.com/rss.aspx?user=";	
		rss_url += document.getElementById("xanga_query").value;
		var rss_url = "http://www.livejournal.com/";
		rss_url += getCheckedValue(document.livejournal_form.type) + "/";
		rss_url += document.getElementById("livejournal_query").value + "/data/rss";

	
		xmlhttp.open("POST", "/index.php",true);
		xmlhttp.onreadystatechange=function()
		{
			if (xmlhttp.readyState==4)
			{
				document.getElementById("xanga_content_box").innerHTML = xmlhttp.responseText;
				}
			}
		xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		xmlhttp.send(\'XMLRequest=1&XMLFunction=rss_content&XMLArgs=a:2:{i:0;s:' + rss_url.length + ':"' + rss_url + '";i:1;b:1;}\');
		return false;
	}
	// -->
	</script>
	
	<div id="blog_content_box"></div>
	</div>';
	
	$box->content = $content;
}

function task_block(&$box)
{
	if($box->userR['USER_STATUS'] < 1)
		return;
	
	$box->h2href = "/shcp/tasklist.php";
	
	$ts = getdate(CURRENT_TIME);
	$earliest_date = date(TIME_FORMAT_SQL, mktime($ts['hours'], $ts['minutes'], $ts['seconds'], $ts['mon'], $ts['mday'] - 7, $ts['year']));
	
	$content = '<table style="width: 100%" cellpadding="0" cellspacing="1" border="0">'
		. '<tr><td style="font-weight: bold"><a href="/shcp/tasklist.php">Active Tasks</a></td><td style="text-align: right">#</td><td style="text-align: right">&#0931;</td></tr>';
	$tasks = mysql_query('SELECT TASKPRIORITY_LIST.*, COUNT(TASK_ID) AS C FROM TASK_LIST
		INNER JOIN TASKPRIORITY_LIST ON TASK_PRIORITY=TASKPRIORITY_ID
		INNER JOIN TASKCAT_LIST ON TASK_CAT=TASKCAT_ID
		WHERE TASK_ARCHIVED=0
		GROUP BY TASKPRIORITY_ID ORDER BY TASKPRIORITY_ID');
	$total = 0;
	while($task = mysql_fetch_array($tasks, MYSQL_ASSOC))
	{
		$total += $task['C'];
		$content .= '<tr><td style="padding-left: 1em; ' . $task['TASKPRIORITY_STYLESTR'] . '">' . $task['TASKPRIORITY_NAME'] . '</td>
		<td style="text-align: right">' . $task['C'] . '</td><td style="text-align: right">' . $total . '</td></tr>';
	}

	$rsfinished = mysql_query("SELECT TASK_ID, TASK_TITLE, USER_FN, USER_LN, TASK_CREATED
		FROM TASK_LIST
			INNER JOIN TASKCAT_LIST ON TASK_CAT=TASKCAT_ID
			INNER JOIN USER_LIST ON TASK_AUTHOR=USER_ID
		WHERE TASK_ARCHIVED=0 AND TASK_CREATED>='$earliest_date'");
	
	$content .= '<tr><td style="font-weight: bold">New</td><td style="text-align: right">' . mysql_num_rows($rsfinished) . '</td><td></td></tr>';
	
	while($finished = mysql_fetch_array($rsfinished, MYSQL_ASSOC))
	{
		$content .= '<tr><td colspan="3"><p style="text-indent: -1em; margin: 0 0 0 2em; font-size: x-small"><a href="/shcp/edittask.php?edit=' . $finished['TASK_ID'] . '">' . $finished['TASK_TITLE'] . '</a> ' . substr($finished['USER_FN'], 0, 1) . substr($finished['USER_LN'], 0, 1) . '&nbsp;' . date('n/j', strtotime($finished['TASK_CREATED'])) . '</p></td></tr>';
	}

	$archived = mysql_query("SELECT COUNT(*) AS C FROM TASK_LIST WHERE TASK_ARCHIVED=1");
	$numarchived = mysql_fetch_array($archived, MYSQL_ASSOC);
	
	$content .= '<tr><td style="font-weight: bold">Archived</td><td style="text-align: right">' . $numarchived['C'] . '</td><td></td></tr></table>';

	$box->content = $content;
}

function classes_block(&$box)
{
	$userid = $box->userid;
	$userR = $box->userR;
	$isstudent = IsStudent($userR['USER_GR']);
	$isteacher = !is_null($userR['USER_TEACHERTAG']);
	$usertag = $userR['USER_TEACHERTAG'];
	
	$box->h2href = "/directory/?id=" . $userid;

	$content = "";

	if(SITE_ACTIVE)
	{
		if($isstudent)
		{
			$classes = mysql_query('SELECT CLASS_NAME, CLASSLINK_URL, SCHED_ID, SCHED_PER, SCHED_CLASS, SCHED_TEACHER
			FROM SCHED_LIST
				INNER JOIN CLASS_LIST ON SCHED_CLASS=CLASS_ID
				LEFT JOIN CLASSLINK_LIST ON CLASSLINK_COURSE=CLASS_ID AND CLASSLINK_TEACHER=SCHED_TEACHER AND CLASSLINK_TYPE="Class Website"
			WHERE
				SCHED_YEAR="' . C_SCHOOLYEAR . '" AND
				SCHED_USER="' . $userid . '" AND
				(SCHED_TERM="YEAR" OR SCHED_TERM="' . C_SEMESTER . '")
			ORDER BY SCHED_PER, SCHED_TERM') or die(mysql_error());
	
			if(mysql_num_rows($classes) > 0)
			{
				$content .= '<ul class="flat" style="margin: 0px;">';
	
				while($cclass = mysql_fetch_array($classes, MYSQL_ASSOC))
				{
					$content .= '<li><span style="font-weight: bold"><span style="color: #999999">' . $cclass['SCHED_PER'] . '</span> <a href="/cm/?class=' . $cclass['SCHED_CLASS'] . '&amp;teacher=' . $cclass['SCHED_TEACHER'] . '">' . $cclass['CLASS_NAME'] . '</a></span></li>'
						. '<li><ul class="flat">';
	
					if(isset($cclass['CLASSLINK_URL']))
						$content .= '<li><span style="font-style: italic"><a href="' . $cclass['CLASSLINK_URL'] . '">View homework website</a></span></li>';
					else
						$content .= '<li><span style="color: #999999">No information available.</span></li>';
	
					$content .= '</ul></li>';
				}
					
				$content .= '</ul>';
			}
			else
				$content .= '<p style="margin: 0px">If you enter the classes in your schedule, you can see them in this space.</p>';
		}
		else if($isteacher)
		{
			$classes = mysql_query('SELECT DISTINCT CLASS_NAME, VALIDCLASS_COURSE, VALIDCLASS_TEACHER, VALIDCLASS_PER FROM VALIDCLASS_LIST INNER JOIN CLASS_LIST ON VALIDCLASS_COURSE=CLASS_ID WHERE VALIDCLASS_TEACHER=' . $usertag . ' ORDER BY VALIDCLASS_PER') or die('Class query failed.');
	
			if(mysql_num_rows($classes) > 0)
			{
				$content .= '<li><ul class="flat" style="margin: 0px;">';
	
				while($cclass = mysql_fetch_array($classes, MYSQL_ASSOC))
				{
					$numclasses++;
				
					$content .= '<li><span style="font-weight: bold"><span style="color: #999999">' . $cclass['VALIDCLASS_PER'] . '</span> <a href="/cm/?class=' . $cclass['VALIDCLASS_COURSE'] . '&amp;teacher=' . $cclass['VALIDCLASS_TEACHER'] . '">' . $cclass['CLASS_NAME'] . '</a></span></li>';
				}
			}
			else
				$content .= 'You are not assigned any classes.';

			$content .= '</ul></li>';
		}
		else
			$content .= "You shouldn't have a schedule.";
	}
	else
		$content .= 'Sorry, you cannot enter your schedule yet.';
	
	$box->content = $content;
}
?>