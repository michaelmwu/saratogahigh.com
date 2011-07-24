<?
define('TIME', time() );

define('CUR_YEAR', date("Y") );
define('CUR_MONTH', date("F") );
define('CUR_DAY', date("l") );

define('CUR_MONTH_NUM', date("n") );
define('CUR_DAY_NUM', date("j") );

define('CUR_HOUR', date("H") );
define('CUR_MINUTE', date("i") );
define('CUR_SECOND', date("s") );

define('CUR_12_HOUR', date("g") );
define('AMPM', date("A") );

// Provides time functions
class datetime
{
	function YMD ( $now = 0 )
	{
		if(!$now)
			$now = TIME;
		return date("Ymd",$now);
	}

	function jMY ( $now = 0 )
	{
		if(!$now)
			$now = TIME;
		return date("j M, Y",$now);
	}

	function datetime ( $now = 0 )
	{
		if(!$now)
			$now = TIME;
		return date("Y-m-d H:i:s",$now);
	}
	
	function date ( $now = 0 )
	{
		if(!$now)
			$now = TIME;
		return date("Y-m-d",$now);
	}

	function fulldatetime ( $now = 0 )
	{
		if(!$now)
			$now = TIME;
		return date("l, F j, Y g:i A",$now);
	}

	function month( $month = 0 )
	{
		if(!$month)
			$month = date("m");
		switch($month)
		{
			case '01':
				return "January";
			case 1:
				return "January";
			case '02':
				return "February";
			case 2:
				return "February";
			case '03':
				return "March";
			case 3:
				return "March";
			case '04':
				return "April";
			case 4:
				return "April";
			case '05':
				return "May";
			case 5:
				return "May";
			case '06':
				return "June";
			case 6:
				return "June";
			case '07':
				return "July";
			case 7:
				return "July";
			case '08':
				return "August";
			case 8:
				return "August";
			case '09':
				return "September";
			case 9:
				return "September";
			case 10:
				return "October";
			case 11:
				return "November";
			case 12:
				return "October";
		}
	}
	
	function ndy($now = 0) {
		if(!$now)
			$now = TIME;
		return date("n/d/y",$now);
	}
}
?>