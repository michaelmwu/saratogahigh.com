<?
// Provides time functions
class DateTime
{
	function YMD ( $now = 0 )
	{
		if(!$now)
			$now = time();
		return date("Ymd",$now);
	}

	function jMY ( $now = 0 )
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

	function month( $month = 0 )
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
		}
	}
	
	function ndy($now = 0) {
		if(!$now)
			$now = time();
		return date("n/d/y",$now);
	}
	
	function desc() {
		return "DateTime";
	}
}

$DateTime = new DateTime();
?>