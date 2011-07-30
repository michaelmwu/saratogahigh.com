<?
/*
+++++++++++++++++++++++++++++++
+
+ Weather_xml.php written by
+ admin@notonebit.com
+
+ Last changed: December 21, 2004
+ Version 1.3
+
+++++++++++++++++++++++++++++++
*/

// Sign up for Weather.com's free XML service at http://www.weather.com/services/xmloap.html

// Setup database connection
$db_host = "";
$db_user = "";
$db_pwd = "";
$db_name = "";
$connection = mysql_connect($db_host, $db_user, $db_pwd) or die("Could not connect");
mysql_select_db($db_name) or die("Could not select database");

// Set Local variables
$partner_ID = "";
$license_key = "";
$location = urlencode($_REQUEST['loc']); //search on location (city or zip)
$loc_id = $_REQUEST['id'];	// specific town id
$length = $_REQUEST['length']; // Forecast length
$image_size = "64x64"; // 32x32, 64x64, or 128x128 - size of daily weather images

if (!($length >= 1 And $length <= 10)) $length = 10;

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

function html_header() // Write html header to screen
{
$header = <<< end_of_output
<html>
<head>
<title></title>
<style>
<!--
body, p      { font-size: 10pt; font-family: Verdana }
td           { font-size: 10pt; font-family: Verdana }
-->
</style>
</head>
<body>
end_of_output;
echo $header;
}

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

	// Debugging output
	//echo "<pre>";
	//print_r($xml);
	//print_r($index);
	//print_r($values);
	//echo "</pre>";
}

if ($loc_id) // Location code selected - Display detail info. A specific city has been selected from the drop down menu. Get forecast.
{
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

	html_header(); // Output header

	echo "<b><font size=4>Weather report for $city</font></b><br /><font size=1>(Last updated $last_update).</font><br />\n";
	echo "<p><table><tr><td><img border=\"1\" src=\"/images/weather/128x128/$curr_icon.png\" alt=\"$curr_text\"></td><td>";
	echo "<font size=3>Currently: <b>$curr_temp&#730; $unit_temp</b></font><br />\n";
	echo "Feels Like: $curr_flik&#730; $unit_temp<br />Current conditions: $curr_text<br />\n";
	echo "Sunrise: $sunrise.<br />Sunset: $sunset.<br />\n";
	echo "</td></tr></table></p>";

$color_bar = <<< end_of_output
<table border="1" cellspacing="0" cellpadding="4" style="border-collapse: collapse" bordercolor="#000000">
  <tr>
    <td width="9%" bgcolor="#CC99CC" align="center">
    <font face="Verdana" size="1">&lt;10</font></td>
    <td width="9%" bgcolor="#9966FF" align="center">
    <font face="Verdana" size="1">10's</font></td>
    <td width="9%" bgcolor="#3399FF" align="center">
    <font face="Verdana" size="1">20's</font></td>
    <td width="9%" bgcolor="#99CCFF" align="center">
    <font face="Verdana" size="1">30's</font></td>
    <td width="9%" bgcolor="#66CC66" align="center">
    <font face="Verdana" size="1">40's</font></td>
    <td width="9%" bgcolor="#FFFF99" align="center">
    <font face="Verdana" size="1">50's</font></td>
    <td width="9%" bgcolor="#FFCC33" align="center">
    <font face="Verdana" size="1">60's</font></td>
    <td width="9%" bgcolor="#FF9933" align="center">
    <font face="Verdana" size="1">70's</font></td>
    <td width="9%" bgcolor="#FF6600" align="center">
    <font face="Verdana" size="1">80's</font></td>
    <td width="9%" bgcolor="#FF0000" align="center">
    <font face="Verdana" size="1">90's</font></td>
    <td width="10%" bgcolor="#990000" align="center">
    <font face="Verdana" size="1" color="#FFFFFF">100+</font></td>
  </tr>
</table>
end_of_output;
echo $color_bar;

	echo "<table border=\"0\" cellpadding=\"4\" cellspacing=\"1\" bgcolor=\"#C0C0C0\"><tr><th>Date</th><th>High/Low</th><th>Day</th><th>Night</th></tr>";
	foreach ($index[day] as $day)
	{
		if ($values[$day][attributes][t] != "")
		{
//			($row_counter%2==0) ? $row_color =  "#CCE6FF": $row_color = "#CCCDFF";
			$row_color = "#EEEECC";
			$img_day = ($counter + 1) * 2;
			$img_night = (($counter + 1) * 2) + 1;

			$day_text = (($counter + 1) * 3) + $counter + 2;
			$day_wind = ((($counter + 1) * 3) + $counter) + 1;
			$day_windspeed = (($counter + 1) * 2) - 1;
			$day_windgust = (($counter + 1) * 2) - 1;
			$day_humidity = (($counter + 1) * 2) - 1;
			$day_precip = $counter * 2;

			$night_text = ((($counter + 1) * 3) + $counter) + 4;
			$night_wind = ((($counter + 1) * 3) + $counter) + 3;
			$night_windspeed = ($counter + 1) * 2;
			$night_windgust = ($counter + 1) * 2;
			$night_humidity = ($counter + 1) * 2;
			$night_precip = ($counter * 2) + 1;

			if ($values[$index[hi][$counter]][value] >= 0) $heat_color = "#CC99CC";
			if ($values[$index[hi][$counter]][value] >= 10) $heat_color = "#9966FF";
			if ($values[$index[hi][$counter]][value] >= 20) $heat_color = "#3399FF";
			if ($values[$index[hi][$counter]][value] >= 30) $heat_color = "#99CCFF";
			if ($values[$index[hi][$counter]][value] >= 40) $heat_color = "#66CC66";
			if ($values[$index[hi][$counter]][value] >= 50) $heat_color = "#FFFF99";
			if ($values[$index[hi][$counter]][value] >= 60) $heat_color = "#FFCC33";
			if ($values[$index[hi][$counter]][value] >= 70) $heat_color = "#FF9933";
			if ($values[$index[hi][$counter]][value] >= 80) $heat_color = "#FF6600";
			if ($values[$index[hi][$counter]][value] >= 90) $heat_color = "#FF0000";
			if ($values[$index[hi][$counter]][value] >= 100) $heat_color = "#990000";
			if ($values[$index[hi][$counter]][value] == "N/A") $heat_color = "#EEEECC";
				
			echo "<tr><td bgcolor=\"$heat_color\"><b>" . $values[$day][attributes][t] . ", " . $values[$day][attributes][dt] . "</b></td>";
			echo "<td bgcolor=\"$row_color\"><b>Hi: " . $values[$index[hi][$counter]][value] . "&#730; $unit_temp\n";
			echo "<hr noshade height=\"1\">Lo: " . $values[$index[low][$counter]][value] . "&#730; $unit_temp</b></td>\n";

			echo "<td bgcolor=\"$row_color\" nowrap><table width=\"100%\" cellpadding=\"2\" cellspacing=\"0\"><tr><td><b>\n";
			echo $values[$index[t][$day_wind]][value] . "</b><br /> ";
			echo "<font size=1>Sunrise: " . $values[$index[sunr][$counter]][value] . "<br />";
			echo "Wind: " . $values[$index[t][$day_text]][value] . " " . $values[$index[s][$day_windspeed]][value] . " $unit_speed";
			echo "<br />Humidity: " . $values[$index[hmid][$day_humidity]][value] . "%<br />Precip: " . $values[$index[ppcp][$day_precip]][value] . "%</font></td>";
			echo "<td bgcolor=\"$row_color\" align=\"right\"><img border=\"1\" src=\"/images/weather/$image_size/" . $values[$index[icon][$img_day]][value] . ".png\" alt=\"" . $values[$index[t][$day_text]][value] . "\"></td></tr></table></td>";

			echo "<td bgcolor=\"$row_color\" nowrap><table width=\"100%\" cellpadding=\"2\" cellspacing=\"0\"><tr><td><b>\n";
			echo $values[$index[t][$night_wind]][value] . "</b><br />";
			echo "<font size=1>Sunset: " . $values[$index[suns][$counter]][value] . "<br />";
			echo "Wind: " . $values[$index[t][$night_text]][value] . " " . $values[$index[s][$night_windspeed]][value] . " $unit_speed";
			echo "<br />Humidity: " . $values[$index[hmid][$night_humidity]][value] . "%<br />Precip: " . $values[$index[ppcp][$night_precip]][value] . "%</font></td>\n";
			echo "<td bgcolor=\"$row_color\" align=\"right\"><img border=\"1\" src=\"/images/weather/$image_size/" . $values[$index[icon][$img_night]][value] .".png\" alt=\"" . $values[$index[t][$night_text]][value] . "\"></tr></table></td>";
			echo "</tr>";

			$counter++;
			$row_counter++;
		}
	}
	echo "</table>";
}

if ($location And is_array($index[loc])) // A city name has been entered and data returned from weather.com, draw drop down menu of matches
{

	if (count($index[loc]) == 1) // If just one match returned, send to detail screen - no need to draw option box for one option.
	{
		$location_code = $values[$index[loc][0]][attributes][id];
		header("Location: weather_xml.php?id=$location_code&length=10"); // Nees html_header because of this redirect.
		exit();
	}

	html_header();

	echo "<form action=\"weather_xml.php\" method=\"POST\">";
	echo "Select a city: <select size=\"1\" name=\"id\">\n";
	// Loop through the XML, setting values
	foreach ($index[loc] as $key=>$val)
	{
		echo "<option value=\"";
		echo $values[$val][attributes][id]; // City code
		echo "\">";
		echo $values[$val][value]; // City name
		echo "</option>\n";
	}
	echo '</select>';
?>

Length of forecast 
<select size="1" name="length">
<option selected>10</option>
<option>9</option>
<option>8</option>
<option>7</option>
<option>6</option>
<option>5</option>
<option>4</option>
<option>3</option>
<option>2</option>
<option>1</option>
</select>
<input type="submit" value="Get Weather">
</form>
<?

}
elseif ($location) // City or zip entered but no match returned from weather.com
{
	html_header();
	echo "No city found. Please enter another city or zip code.<br />\n";
}

if (empty($location) And empty($loc_id))
{
	html_header();
}
?>

<form action="weather_xml.php" method="POST">
<p>Enter a city or zip code: <input type="text" name="loc" size="20"><input type="submit" value="Search"></p>
</form>
Weather data provided by <a href="http://www.weather.com/?prod=xoap&par=<?=$partner_ID?>">weather.com <img src="/images/weather/logos/TWClogo_32px.png" border="0" alt="The Weather Channel"></a>
</body>

</html>
