<?
// ##################################################################################
// Title                     : Class csvUtil
// Version                   : 1.0
// Author                    : Steven de Boer, Michael Wu
// Last modification date    : 04-06-2003
// Description               : Print and search contents of CSV files.
// ##################################################################################
// History:
// 06-13-2002                : First version of this class.
// ##################################################################################

class csv_read
{
	var $headers = array();
	var $data = array();
	var $file;
	var $separator;
	var $enclosure;
	var $i = 0;

	// Constructor, reads in everything

	function __construct($file, $separator = ",", $enclosure = '"')
	{
		if(!is_readable($file))
			return false;
		$this->file = fopen($file,'r');
		$this->separator = $separator;
		$this->enclosure = $enclosure;
		$this->headers = fgetcsv($this->file,0,$this->separator,$this->enclosure);

		while($temp = fgetcsv($this->file,0,$this->separator,$this->enclosure))
			$this->data[] = $temp;
			
		return true;
	}
	
	// Destructor, closes the file

	function __destruct()
	{
		fclose($this->file);
	}

	function fetch_row($assoc = false)
	{
		if( array_key_exists($this->i,$this->data) )
		{
			if(!$assoc)
				return $this->data[$this->i++];
			else
			{
				$row = array();
				$j = 0;
				foreach($this->headers as $header)
				{
					$row[$header] = $this->data[$this->i][$j++];
				}
				$this->i++;
				return $row;
			}
		}
		else
			return false;
	}
	
	// Fetch all the rows, can be returned as an associative array.
	
	function fetch_all($assoc = false)
	{
		$array = array();
		$length = count($this->data);
		for($i = 0; $i < $length; $i++)
		{
			if(!$assoc)
				$array[] = $this->data[$i];
			else
			{
				$row = array();
				$j = 0;
				foreach($this->headers as $header)
				{
					$row[$header] = $this->data[$i][$j++];
				}
				$array[] = $row;
			}
		}
		return $array;
	}
	
	// Reset the individual row fetcher iterator
	
	function fetch_reset()
	{
		$this->i = 0;
	}

	// Find and return content of a give position
	
	function field($row, $col)
	{
		$retval = $this->buffer[$row][$col];
		return $retval;
	}

	// Search for a value in given column returns an array with found rows
	
	function search($col, $expression)
	{
		$i = 0;
		$j = 0;
		do {
			if (@eregi($expression,$this->buffer[$i][$col])) {
			$retval[$j] = $i;
			$j++;
			}
			$i++;
		} while ($this->buffer[$i][0]);

		return $retval;
	}

	// Returns number of rows #

	function rows()
	{
		return count($this->data);
	}

	// Returns number of cols
	
	function cols()
	{
		return count($this->headers);
	}
}

class csv_write
{
	var $file;
	var $separator;
	var $enclosure;
	var $headers;

	// Constructor, opens file
	
	function __construct($filename, $separator = ",", $enclosure = '"')
	{
		if(!is_writable($filename))
			return false;
			
		$this->file = fopen($filename,'w');
		$this->separator = $separator;
		$this->enclosure = $enclosure;
	}
	
	// Writes out header
	
	function header($headers)
	{
		$this->headers = $headers;
		fputcsv($this->file,$headers,$this->separator,$this->enclosure);
	}
	
	// Writes out data fields
	
	function data($temp,$assoc = false)
	{
		if($assoc)
		{
			$data = array();
		
			foreach($this->headers as $header)
			{
				$data[] = $temp[$header];
			}
		}
		else
			$data = $temp;
		fputcsv($this->file,$data,$this->separator,$this->enclosure);
	}
	
	// Destructor, closes file
	
	function __destruct()
	{
		fclose($this->file);
	}
}

?>