<?
$zip = $_GET['zip'];
if(!preg_match("/[^0-9]/",$zip) && isset($zip))
{
	$weatherc = "";
	$fweather = fopen("http://www.w3.weather.com/weather/local/$zip","r");

	sleep(.5);

	while( ! feof( $fweather ) )
		$weatherc .= fread( $fweather, 1024 );
	fclose( $fweather );


	if(strlen($weatherc) <= 40)
		print "Sorry, could not get the weather right now.";	
	else if(! preg_match("/Sorry, the page you requested was not found on weather.com/",$weatherc))
	{
		preg_match("/Local Forecast for (.+?), (.+?) \(/",$weatherc,$matches);
		$city = $matches[1];
		$state = $matches[2];
		preg_match("/<TD VALIGN=TOP ALIGN=CENTER CLASS=obsInfo2><B CLASS=obsTextA>(.+?)<\/B><\/TD>\n<TD VALIGN=TOP ALIGN=CENTER CLASS=obsInfo2> <B CLASS=obsTextA>Feels Like<BR>(.+?)<\/B><\/TD>/i",$weatherc,$matches);
		$cond = $matches[1];
		$temp = $matches[2];
		$temp = preg_replace('/&deg;/'," deg ",$temp);
		preg_match("/UV Index:.+?\n.+?>(.+?)<\/TD>/",$weatherc,$matches);
		$uv = $matches[1];
		preg_match("/Dew Point:.+?\n.+?>(.+?)<\/TD\>/",$weatherc,$matches);
		$dew = $matches[1];
		$dew = preg_replace('/&deg;/', " deg ", $dew);
		preg_match("/Humidity:.+?\n.+?>(.+?)<\/TD\>/",$weatherc,$matches);
		$humi = $matches[1];
		preg_match("/Visibility:.+?\n.+?>(.+?)<\/TD\>/",$weatherc,$matches);
		$visi = $matches[1];
		preg_match("/Pressure:.+?\n.+?>(.+?)<\/TD\>/",$weatherc,$matches);
		$pres = $matches[1];
		preg_match("/Wind:.+?\n.+?>(.+?)<\/TD\>/",$weatherc,$matches);
		$wind = $matches[1];
		preg_match("/(Last Updated .+?)<\/TD>/",$weatherc,$matches);
		$time = $matches[1];
 		$reply = "In $city, $state ($zip):\n"
			. "Temperature: $temp\n"
			. "Condition: $cond\n"
			. "Wind speed: $wind\n"
			. "Dew point: $dew\n"
			. "Humidity: $humi\n"
			. "Visibility: $visi\n"
			. "UV Index: $uv\n"
			. "Pressure: $pres\n"
			. "$time";
		print $reply;
	}
	else
		print "Could not get the weather for $zip.";
}
else
{
	print "Correct syntax is 'weather <i>zipcode</i>'";
}
?>