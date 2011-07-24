<?
	// Spiffy functions
	function highlight_file_linenum($data, $funclink = true, $return = false)
	{
		// Init
		$data = explode ('<br />', $data);
		$start = '<span style="color: black;">';
		$end   = '</span>';
		$i = 1;
		$text = '';
	
		// Loop
		foreach ($data as $line) {
			$text .= $start . $i . ' ' . $end .
				str_replace("\n", '', $line) . "<br />\n";
			++$i;
		}
	
		// Optional function linking
		if ($funclink === true) {
			$keyword_col = ini_get('highlight.keyword');
			$manual = 'http://www.php.net/function.';
	
			$text = preg_replace(
				// Match a highlighted keyword
				'~([\w_]+)(\s*</span>)'.
				// Followed by a bracket
				'(\s*<span\s+style="color: ' . $keyword_col . '">\s*\()~m',
				// Replace with a link to the manual
				'<a href="' . $manual . '$1">$1</a>$2$3', $text);
		}
		
		// Return mode
		if ($return === false) {
			echo $text;
		} else {
			return $text;
		}
	}

	function php_process(oh $arg_body)
	{
		while(eregi('(.+)\<\?php (.+) \?\>(.+)', $arg_body, $arr_body))
		{
			$php = '$tmp = '.$arr_body[2];

			if(substr($php,-1,1) != ';')
			{
				$php .= ';';
			}
			eval($php);
			$arg_body = $arr_body[1].$tmp.$arr_body[3];
		}
		return($arg_body);
	}
	
	function bin2text($bin_str)
	{
		$text_str = '';
		$chars = explode("\n", chunk_split(str_replace("\n", '', $bin_str), 8));
		$_I = count($chars);
		for($i = 0; $i < $_I; $text_str .= chr(bindec($chars[$i])), $i  );
		return $text_str;
	}
	
	function text2bin($txt_str)
	{
		$len = strlen($txt_str);
		$bin = '';
		for($i = 0; $i < $len; $i  )
		{
			$bin .= strlen(decbin(ord($txt_str[$i]))) < 8 ? str_pad(decbin(ord($txt_str[$i])), 8, 0, STR_PAD_LEFT) : decbin(ord($txt_str[$i]));
		}
		return $bin;
	}
	
	function numcomma ($value)
	{
		/*
		coded diz coz noone had one that actually worked for me
		
		tyutyu1@vodafone.hu
		*/
		
		if(strpos($value,"."))
		{
			$decimalval = substr($value,strpos($value,".")+1);
			$value = substr($value,0,strpos($value,"."));
		}
		
		$length = strlen($value);
		
		for($i=3;$i<($length);$i=$i+3)
		{
		$k = $i*(-1);
		
		$chunks[count($chunks)] = substr($value,$k,3);
		
		}
		
		$inarray = count($chunks)*3;
		$leftout = $length-$inarray;
		$leftout = substr($value,0,$leftout);
		
		$finaltext = $leftout;
		
		rsort($chunks);
		
		for($i=0;$i<count($chunks);$i++)
		{
		
		$finaltext .= "," .$chunks[$i];
		}
		
		if(strlen($decimalval)>0) $finaltext .= "." .$decimalval;
		
		return $finaltext;
	}
	
	define("HEX",16);
	define("BINARY",2);
	define("OCT",8);
	define("BASE10",10);
	define("DECIMAL", 10);
	function convertBase($number, $fromBase = 10, $toBase = 2)
	{
		if($toBase > 36 || $toBase < 2)			//check base validity
			return "Invalid originating base.";
		if($fromBase > 36 || $fromBase < 2)
			return "Invalid destination base.";
    
		@list($number, $decimal) = explode(".",$number);
		for($i = 0; $i < strlen($number); $i++)		//convert to base 10
		{
			$digit = substr($number, $i, 1);
			if(eregi("[a-z]",$digit))
			{
				$x = ord($digit) - 65 + 10;
				if($x > $fromBase)
					$x -= 32;
				$digit = $x;
			}
			@$base10 += $digit * (pow($fromBase, strlen($number) - $i - 1));
		}
		$number = $base10;
		if($toBase == 10)
			return $number;
		$q = $number;
		while($q != 0)		//convert base 10 equivalent to specified base
		{
			$r = $q % $toBase;
			$q = floor($q / $toBase);
			if($r > 9)
				$r = chr(($r - 9) + 64);
			@$baseres = "$r" . "$baseres";
		}
		return $baseres;
	}
	
	function lefts( $varb, $num ){
		$dnum = intval($num);
		if (strlen($varb)>$dnum){
		$nvarb = substr($varb, 0, $dnum);
		$nvarb .= " ...";
		}
		else if (strlen($varb)<$dnum){
		$nvarb=$varb;
		}
		return $nvarb;
	}
	
	function uc_sentences($sString)
	{
		$sString = strtolower($sString);
		$words = split(" ", $sString); // each entry in array $words is a word from the string
		$firstword = false;
		$sNewString = "";
		foreach($words AS $wordkey=>$word)
		{
			$word = trim($word); // just in case people double-space between sentences
			$lastchar = substr($word, -1); // $lastchar is used to determine if end-of-sentence is here
			if(($firstword) OR ($wordkey==0)) // if it's the start of sentence then we capitalize the word
			{
				$word = ucfirst($word);
				$firstword = false;
				$sNewString = $sNewString . " $word"; // add the word to the output string
			}
			elseif(($lastchar==".") OR($lastchar=="!") OR ($lastchar=="?")) // you can add more chars if you need to
			{
				$firstword = true; // now the next word will be first word of new sentence
				$sNewString = $sNewString . " $word"; // add the word to the output string
			}
			else
			{
				$sNewString = $sNewString . " $word"; // add the word to the output string
			}
		}
		$sNewString = trim($sNewString); // sometimes an extra space at beginning or end occurs, so this fixes it

		return $sNewString; // return the new string
	}
	
	function wraplines($oldstr, $wrap)
	{
		# we expect the following things to be newlines:
		$oldstr=str_replace("<br>","\n",$oldstr);
		$oldstr=str_replace("<BR>","\n",$oldstr);
		$newstr = ""; $newline = "";
		# Add a temporarary linebreak at the end of the $oldstr.
		# We will use this to find the ending of the string.
		$oldstr .= "\n";
		do
		{
			# Use $i to point at the position of the next linebreak in $oldstr!
			# If a linebreak is encountered earlier than the wrap limit, put $i there.
			if (strpos($oldstr, "\n") <= $wrap)
			{
				$i = strpos($oldstr, "\n");
			}
			# Otherwise, begin at the wrap limit, and then move backwards
			# until it finds a blank space where we can break the line.
			else
			{
				$i = $wrap;
				while (!ereg("[\n\t ]", substr($oldstr, $i, 1)) && $i > 0)
				{
					$i--;
				}
			}
			# $i should now point at the position of the next linebreak in $oldstr!
			# Extract the new line from $oldstr, including the
			# linebreak/space at the end.
			$newline = substr($oldstr, 0, $i+1);
			# Turn the last char in the string (which is probably a blank
			# space) into a linebreak.
			if ($i!=0) $newline[$i] = "\n";
			# Decide whether it's time to stop:
			# Unless $oldstr is already empty, remove an amount of
			# characters equal to the length of $newstr. In other words,
			# remove the same chars that we extracted into $newline.
			if ($oldstr[0] != "")
			{
				$oldstr = substr($oldstr, $i+1);
			}
			# Add $newline to $newstr.
			$newstr .= $newline;
			# If $oldstr has become empty now, quit. Otherwise, loop again.
		} while (strlen($oldstr) > 0); # Remove the temporary linebreak we added at the end of $oldstr.
			$newstr = substr($newstr, 0, -1);
		return $newstr;
	}
	
	function remove_index (&$array,$i)
	{
		unset($array[$i]);
		$array = array_values($array);
	}
?>