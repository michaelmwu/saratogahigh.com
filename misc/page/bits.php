<?	
/*
	Infinite* bits and bit handling in general.
	
	Perceivably, the only limit to the bitmask class in storing bits would be
	the maximum limit of the index number 2^31 - 1, so 2^31 * 31 - 1 = 66571993087 bits.
	I'm sure that's enough, man.
*/
DEFINE('INTEGER_LENGTH',31); // Stupid signed bit.

class bit
{
	function set( &$int, $bit )
	{
		$int |= 1 << (int) $bit;
	}
  
	function remove( &$int, $bit)
	{
		$int &= ~ (1 << (int) $bit);
	}
	
	function toggle( &$int, $bit )
	{
		$int ^= 1 << (int) $bit;		
	}

	function read($int, $bit)
	{
		return $int & (1 << (int) $bit);
	}
	
	function set_range(&$int,$value,$length,$offset = 0)
	{
		$int &= ~bit::generate_mask($offset,$offset + $length);
		$int |= $value << $offset;
	}
	
	function remove_range(&$int,$value,$length,$offset = 0)
	{
		$int &= ~bit::generate_mask($offset,$offset + $length);
	}
	
	function toggle_range(&$int,$value,$length,$offset = 0)
	{
		$int ^= bit::generate_mask($offset,$offset + $length);
	}
	
	function read_range($int,$value,$length,$offset = 0)
	{
		return ($int & ~bit::generate_mask($offset,$offset + $length)) >> $offset;
	}
	
	function split_range($start,$end)
	{
		$range = array(array($start,$end));
		$j = 0;
		for($i = (int) ($start / INTEGER_LENGTH + 1) * INTEGER_LENGTH - 1;$i < $end;$i += INTEGER_LENGTH)
		{
			$range[$j + 1] = array();
			$range[$j + 1] = array($i + 1,$end);
			$range[$j][1] = $i;
			$j++;
		}
		return $range;
	}

	function generate_mask($start,$end)
	{
		$range = $end - $start + 1;
		if( $range > INTEGER_LENGTH )
			$range = INTEGER_LENGTH;
		if( $range <= 0 )
			return 0;
		return (~0 & ~(1 << INTEGER_LENGTH) >> (INTEGER_LENGTH - $end)) & (~0 << $start);
	}
}

class bitmask
{
	var $bitmask = array();
	
	function bit_split($bits,$offset)
	{
		$values = array();
		$values[0] = $bits >> $offset;
		$values[1] = $bits % (1 << $offset);
		return $values;
	}

	function set( $bit ) // Set some bit
	{
		$key = (int) ($bit / INTEGER_LENGTH);
		$bit = fmod($bit,INTEGER_LENGTH);
		bit::set($this->bitmask[$key],$bit);
	}
	
	function set_value( $value, $length, $offset )
	{
		$i = 0;
		$range = bit::split_range( $offset, $offset + $length - 1 );
		foreach($range as $element)
		{
			list($start,$end) = $element;
			$key = $start / INTEGER_LENGTH;
			$start %= INTEGER_LENGTH;
			$end %= INTEGER_LENGTH;
			
			bit::set_range( $this->bitmask[$key], read_range($i, $i + $end - $start + 1), $end - $start + 1, $start );
			$i += $end - $start + 1;
		}
	}
	
	function remove( $bit ) // Remove some bit
	{
		$key = (int) ($bit / INTEGER_LENGTH);
		$bit = fmod($bit,INTEGER_LENGTH);
		bit::remove($this->bitmask[$key],$bit);
		if(!$this->bitmask[$key])
			unset($this->bitmask[$key]);
	}
	
	function remove_value( $start, $end )
	{
		$range = bit::split_range( $start, $end );
		foreach($range as $element)
		{
			list($start,$end) = $element;
			$key = $start / INTEGER_LENGTH;
			$start %= INTEGER_LENGTH;
			$end %= INTEGER_LENGTH;
			
			bit::remove_range( $this->bitmask[$key], $start, $end );
		}
	}
	
	function toggle( $bit ) // Toggle some bit
	{
		$key = (int) ($bit / INTEGER_LENGTH);
		$bit = fmod($bit,INTEGER_LENGTH);
		bit::toggle($this->bitmask[$key],$bit);
		if(!$this->bitmask[$key])
			unset($this->bitmask[$key]);
	}
	
	function toggle_value( $start, $end )
	{
		$range = bit::split_range( $start, $end );
		foreach($range as $element)
		{
			list($start,$end) = $element;
			$key = $start / INTEGER_LENGTH;
			$start %= INTEGER_LENGTH;
			$end %= INTEGER_LENGTH;
			
			bit::toggle_range( $this->bitmask[$key], $start, $end );
		}
	}
	
	function read( $bit ) // Read some bit
	{
		$key = (int) ($bit / INTEGER_LENGTH);
		$bit = fmod($bit,INTEGER_LENGTH);
		return bit::read($this->bitmask[$key],$bit);
	}
	
	function read_value( $start, $end )
	{
		$value = 0;
		$i = 0;
		$range = bit::split_range( $start, $end );
		foreach($range as $element)
		{
			list($start,$end) = $element;
			$key = $start / INTEGER_LENGTH;
			$start %= INTEGER_LENGTH;
			$end %= INTEGER_LENGTH;
			
			bit::set_range($value,bit::read_range( $this->bitmask[$key], $start, $end ),$i);
			$i += $end - $start + 1;
		}
		return $value;
	}

	function stringin($string) // Read a string of bits that can be up to the maximum amount of bits long.
	{
		$this->bitmask = array();
		$array = str_split( strrev($string), INTEGER_LENGTH );
		foreach( $array as $key => $value )
		{
			if($value = bindec(strrev($value)))
				$this->bitmask[$key] = $value;
		}
	}

	function stringout() // Returns a string of your nice little bits
	{
		$string = "";

		$keys = array_keys($this->bitmask);
		sort($keys, SORT_NUMERIC);

		for($i = array_pop($keys);$i >= 0;$i--)
		{
			if($this->bitmask[$i])
				$string .= sprintf("%0" . INTEGER_LENGTH . "b",$this->bitmask[$i]);
		}
		return $string;
	}
	
	function clear() // Purge!
	{
		$this->bitmask = array();
	}
	
	function debug() // See what's going on in your bitmask array
	{
		var_dump($this->bitmask);
	}
}
?>