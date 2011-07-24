<?	
class bit
{
	public function set( &$val, $bit )
	{
		$val |= 1 << $bit;
	}
  
	public function remove( &$val, $bit)
	{
		$val &= ~ (1 << $bit);
	}
	
	public function toggle( &$val, $bit )
	{
		$val ^= 1 << $bit;		
	}

	public function read($val, $bit)
	{
		return $val & (1 << $bit);
	}
}

class bitmask
{
	protected $bitmask = array();
	
	public function set( $bit )
	{
		$key = (int) ($bit / 32);
		$bit %= 32;
		if(!array_key_exists($key,$this->bitmask))
			$this->bitmask[$key] = false;
		bit::set($this->bitmask[$key],$bit);
	}
	
	public function remove( $bit )
	{
		$key = (int) ($bit / 32);
		$bit %= 32;
		bit::remove($this->bitmask[$key],$bit);
		if(!$this->bitmask[$key])
			unset($this->bitmask[$key]);
	}
	
	public function toggle( $bit )
	{
		$key = (int) ($bit / 32);
		$bit %= 32;
		if(!array_key_exists($key,$this->bitmask))
			$this->bitmask[$key] = false;
		bit::toggle($this->bitmask[$key],$bit);
		if(!$this->bitmask[$key])
			unset($this->bitmask[$key]);
	}
	
	public function read( $bit )
	{
		$key = (int) ($bit / 32);
		$bit %= 32;
		bit::read($this->bitmask[$key],$bit);
	}

	public function stringin($string)
	{
		$this->bitmask = array();
		$i = 0;
		$j = -32;
		while(($number = $substr($string,$j,32)) != "")
		{
			$number = bindec($number);
			if($number)
				$this->bitmask[$i] = $number;
			$j -= 32;
			$i++;
		}
	}

	public function stringout()
	{
		$string = "";
		for($i = array_pop(sort(array_keys($this->bitmask), SORT_NUMERIC));$i >= 0;$i--)
		{
			if(array_key_exists($i,$this->bitmask))
				$string .= sprintf("%032d",$this->bitmask[$i]);
			else
				$string .= sprintf("%032d",0);
		}
	}
}
?>