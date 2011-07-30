<? // icyhandofcrap
define('IS_REQUIRED',1);
define('IS_NUMERIC',2);
define('IS_TWO',4);
define('IS_FOUR',8);
define('IS_PHONE',16);
define('IS_OWN_CHECK',32);

define('FORM_ERROR',"formerror"); // Class for form errors

class form
{
	var $page;
	
	var $config = array( 'method' => "POST" );
	var $error = array();
	var $elements = array();
	
	function form(&$page) // Grab the parent page.
	{
		$this->page =& $page;
	}
	
	function __get($key)
	{
		return $this->config[$key]; 
	}
	
	function __set($key,$value)
	{
		$this->config[$key] = $value; 
	}

	function add_element($element) // Adds an element to the element array.
	{
		$element->form =& $this;
		$this->elements[$element->name] = $element;
	}
	
	function check() // Checks each element for requirements
	{
		foreach($this->elements as $element)
		{
			/*
				Big if statement for requirements, bit based
				You can add your own here
			*/
			if( ($element->req & IS_REQUIRED && !$element->required())
				|| ($element->req & IS_NUMERIC && !$element->numeric())
				|| ($element->req & IS_TWO && !$element->two())
				|| ($element->req & IS_FOUR && !$element->four())
				|| ($element->req & IS_PHONE && !$element->phone())
				|| ($element->req & IS_OWN_CHECK && !$element->owncheck)
			)
				$this->error[$element->name] = true;
		}
		
		if(count($this->error) > 0) // Did the entire form pass?
		{
			$this->page->error("Fill in ALL required fields.");
			return false;
		}
		else
			return true;
	}
	
	function check_error($element) // Input is an element object, did it have an error?
	{
		if($this->error[$element->name])
			return true;
		return false;
	}
	
	function form_errors() // Prints a text list of the elements that had errors.
	{
		print '<div class="error">These elements seem to have errors: ' . implode(', ',array_keys($this->error)) . "</div>\n";
	}
		
	function element($element)
	{
		if($this->elements[$element])
			$this->elements[$element]->element_print();
		else
			print "<div>Element $element does not exist.</div>\n";
	}
	
	function header()
	{
		print '<form name="' . $this->name . '" action="' . $this->action . '" method="' . $this->method . '">' . "\n";
	}
	
	function footer()
	{
		print "</form>\n";
	}
	
	function formout($string)
	{
		return htmlentities(stripslashes($string));
	}
}

class element
{
	var $form;
	var $type;
	var $class = array();
	var $javascript = array();
	var $config = array( 'owncheck' => true, 'options' => array() );
	
	function __get($key)
	{
		return $this->config[$key]; 
	}
	
	function __set($key,$value)
	{
		$this->config[$key] = $value; 
	}
	
	function add_check($bit)
	{
		$this->req |= (int)$bit;
	}
	
	function add_class($class)
	{
		$this->class[] = $class;
	}
	
	function do_class()
	{
//		if($this->form->check_error($this))
//			$this->class[] = FORM_ERROR;
		return implode(" ",$this->class);
	}
	
	function add_javascript($event,$code)
	{
		$this->javascript[] = array('event' => $event, 'code' => $code);
	}
	
	function do_javascript()
	{
		$script_array = array();
		foreach($this->javascript as $code_array)
			$script_array[] = $code_array['event'] . '="' . $code_array['code'] . '"';
		return implode(' ',$script_array);
	}
	
	function required()
	{
		return strlen($_POST[$this->name]) > 0;
	}
	
	function numeric()
	{
		return is_numeric($_POST[$this->name]);
	}
	
	function two()
	{
		return preg_match("/^\d{2}$/",$_POST[$this->name]);
	}
	
	function four()
	{
		return preg_match("/^\d{4}$/",$_POST[$this->name]);
	}
	
	function phone()
	{
		$phone = $_POST[$this->name];
        if(empty($num))
            return false;

   		preg_replace("/[\s\(\)\-\+]/","",$phone);
        if(!preg_match("/^\d+$/",$phone) || (strlen($phone) ) < 7 || strlen($phone) > 13 )
            return false;

        return true;
	}
}

class input extends element
{
	var $type = 'input';
	var $maxlength;

	function element_print($return = false)
	{
		$string = '<input ' . (strlen($this->id) > 0?'id="' . $this->id . '" ':'') . 'name="' . $this->name . '" type="text" class="' . $this->do_class() . '" ' . $this->do_javascript() . ' ';
		if(strlen($this->maxlength) > 0)
			$string .= 'maxlength="' . $this->maxlength . '" ';
		$string .= 'style="' . $this->css . '" value="' . (isset($_POST[$this->name]) ? form::formout($_POST[$this->name]) : $this->value) . '">';
		if(!$return)
			print $string;
		else
			return $string;
	}
}

class password extends element
{
	var $type = 'password';

	function element_print($return = false)
	{
		$string = '<input ' . (strlen($this->id) > 0?'id="' . $this->id . '" ':'') . 'name="' . $this->name . '" type="password" class="' . $this->do_class() . '" ' . $this->do_javascript() . ' ';
		if(strlen($this->maxlength) > 0)
			$string .= 'maxlength="' . $this->maxlength . '" ';
		$string .= 'style="' . $this->css . '" value="' . (isset($_POST[$this->name]) ? form::formout($_POST[$this->name]) : $this->value) . '">';
		if(!$return)
			print $string;
		else
			return $string;
	}
}

class file extends element
{
	var $type = 'file';
	
	function element_print($return = false)
	{
		$string = '<input ' . (strlen($this->id) > 0?'id="' . $this->id . '" ':'') . 'name="' . $this->name . '" type="file" class="' . $this->do_class() . '" ' . $this->do_javascript() . ' ';
		if(strlen($this->maxlength) > 0)
			$string .= 'maxlength="' . $this->maxlength . '" ';
		$string .= 'style="' . $this->css . '" value="' . (isset($_POST[$this->name]) ? form::formout($_POST[$this->name]) : $this->value) . '">';
		if(!$return)
			print $string;
		else
			return $string;
	}
	
	function required()
	{
		return isset($_POST[$this->name]);
	}
	
	function is_uploaded()
	{
		return isset($_FILES[$this->name]);
	}
	
	function temp_file()
	{
		return $_FILES[$this->name]['tmp_file'];
	}
	
	function temp_filehandle()
	{
		return fopen($_FILES[$this->name]['tmp_file'],'r');
	}
	
	function temp_filecontents()
	{
		return fread( fopen($_FILES[$this->name]['tmp_file'],'r'), $_FILES['userfile']['size'] );
	}
	
	function upload_error()
	{
		return $_FILES[$this->name]['error'];
	}
}

class check extends element
{
	var $type = 'check';
	
	function element_print($return = false)
	{
		$string = '<input ' . (strlen($this->id) > 0?'id="' . $this->id . '" ':'') . 'name="' . $this->name . '" type="checkbox" class="' . $this->do_class() . '" style="' . $this->css . '" class="' . $this->class . '" ' . $this->do_javascript() . ' ' . ($this->selected?'checked':'') . '>';
		if(!$return)
			print $string;
		else
			return $string;
	}
	
	function required()
	{
		return 1;
	}
}

class text extends element
{
	var $type = 'text';

	function element_print($return = false)
	{
		$string = '<textarea ' . (strlen($this->id) > 0?'id="' . $this->id . '" ':'') . 'name="' . $this->name . '" rows="' . $this->row . '" cols="' . $this->col . '" class="' . $this->do_class() . '" ' . $this->do_javascript() . ' style="' . $this->css . '">' . (isset($_POST[$this->name]) ? form::formout($_POST[$this->name]) : $this->value) . '</textarea>';
		if(!$return)
			print $string;
		else
			return $string;
	}
}

class radio extends element
{
	var $type = 'radio';
	var $options = array();

	function add_option($value,$label,$selected = 0)
	{
		$this->options[] = array('value' => $value, 'label' => $label, 'selected' => $selected);
	}
	
	function element_print($return = false)
	{
		$string = "";
		foreach($this->options as $values)
		{
			$string .= '<label class="' . $this->do_class() . '">' . $values['label'] . '<input type="radio" class="' . $this->do_class() . '" name="' . $this->name . '" style="' . $this->css . '" value="' . form::formout($values['value']) . '" ';
			if(!isset($_POST[$this->name]) && $values['selected'])
				$string .= 'selected';
			else if($isset($_POST[$this->name]) && $_POST[$this->name] == $value['value'])
				$string .= 'selected';
			$string .= "></label>\n";
		}
		if(!$return)
			print $string;
		else
			return $string;
	}

	function required()
	{
		return isset($_POST[$this->name]);
	}
}

class select extends element
{
	var $type = 'select';
	var $options = array();

	function add_option($value,$label,$selected = 0)
	{
		$this->options[] = array('value' => $value, 'label' => $label, 'selected' => $selected);
	}
	
	function element_print($return = false)
	{
		$string = '<select ' . (strlen($this->id) > 0?'id="' . $this->id . '" ':'') . 'name="' . $this->name . '" class="' . $this->do_class() . '" ' . $this->do_javascript() . ' style="' . $this->css . '">' . "\n";
		foreach($this->options as $values)
		{
			$string .= '<option value="' . form::formout($values['value']) . '"';
			if(!isset($_POST[$this->name]) && $values['selected'])
				$string .= ' selected';
			else if(isset($_POST[$this->name]) && $_POST[$this->name] == $values['value'])
				$string .= ' selected';

			$string .= '>' . form::formout($values['label']) . "</option>\n";
		}
		$string .= "</select>\n";
		if(!$return)
			print $string;
		else
			return $string;
	}
	
	function required()
	{
		return isset($_POST[$this->name]);
	}
}

class handler
{
	var $table;
	var $handlers = array();
	var $where;
	var $extraset;

	function add_handler($column,$value)
	{
		$this->handlers[$column] = $value;
	}
	
	function found()
	{
		return db::get_row($this->table,$this_where);
	}
		
	function handle()
	{
		return db::update($this->table,$this->handlers,$this->where,$this->extraset);
	}
}
?>