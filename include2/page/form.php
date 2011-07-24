<? // icyhandofcrap
define('IS_REQUIRED',1);
define('IS_NUMERIC',2);
define('IS_EMAIL',4);
define('IS_TWO',8);
define('IS_FOUR',16);
define('IS_PHONE',32);
define('IS_OWN_CHECK',64);

define('FORM_ERROR',"formerror"); // Class for form errors

class form
{
	public $page;
	
	public $config = array( 'method' => "POST" );
	public $error = array();
	protected $elements = array();
	
	public function __construct(&$page) // Grab the parent page.
	{
		$this->page =& $page;
	}
	
	public function __get($key)
	{
		return $this->config[$key]; 
	}
	
	public function __set($key,$value)
	{
		$this->config[$key] = $value; 
	}

	public function add_element($element) // Adds an element to the element array.
	{
		$element->form =& $this;
		$this->elements[$element->name] = $element;
	}
	
	public function check() // Checks each element for requirements
	{
		foreach($this->elements as $element)
		{
			/*
				Big if statement for requirements, bit based
				You can add your own here
			*/
			if( ($element->req & IS_REQUIRED && !$element->required())
				|| ($element->req & IS_NUMERIC && !$element->numeric())
				|| ($element->req & IS_EMAIL && !$element->email())
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
	
	public function check_error($element) // Input is an element object, did it have an error?
	{
		if($this->error[$element->name])
			return true;
		return false;
	}
	
	public function form_errors() // Prints a text list of the elements that had errors.
	{
		print '<div class="error">These elements seem to have errors: ' . implode(', ',array_keys($this->error)) . "</div>\n";
	}
		
	public function element($element)
	{
		if($this->elements[$element])
			$this->elements[$element]->element_print();
		else
			print "<div>Element $element does not exist.</div>\n";
	}
	
	public function header()
	{
		print '<form name="' . $this->name . '" action="' . $this->action . '" method="' . $this->method . '">' . "\n";
	}
	
	public function footer()
	{
		print "</form>\n";
	}
	
	public function formout($string)
	{
		return htmlentities(stripslashes($string));
	}
	
	public function emailsyntax_is_valid($email)
	{
		$to_work_out = explode("@", $email);
		if (!isset($to_work_out[0])) return FALSE;
		if (!isset($to_work_out[1])) return FALSE;

		$pattern_local = '^([0-9a-z]*([-|_]?[0-9a-z]+)*)(([-|_]?)\.([-|_]?)[0-9a-z]*([-|_]?[0-9a-z]+)+)*([-|_]?)$';
		$pattern_domain = '^([0-9a-z]+([-]?[0-9a-z]+)*)(([-]?)\.([-]?)[0-9a-z]*([-]?[0-9a-z]+)+)*\.[a-z]{2,4}$';
		$match_local = eregi($pattern_local, $to_work_out[0]);
		$match_domain = eregi($pattern_domain, $to_work_out[1]);

		if ($match_local && $match_domain)
			return TRUE;
		return FALSE;
	}
}

abstract class element
{
	public $form;
	
	public $type;

	public $config = array( 'class' => array(), 'owncheck' => true, 'options' => array() );
	
	public function __get($key)
	{
		return $this->config[$key]; 
	}
	
	public function __set($key,$value)
	{
		$this->config[$key] = $value; 
	}
	
	public function add_check($bit)
	{
		$this->req |= (int)$bit;
	}
	
	public function add_class($class)
	{
		$this->class[] = $class;
	}
	
	protected function do_class()
	{
		if($this->form->check_error($this))
			$this->class[] = FORM_ERROR;
		return implode(" ",$this->class);
	}
	
	public function required()
	{
		return strlen($_POST[$this->name]) > 0;
	}
	
	public function numeric()
	{
		return is_numeric($_POST[$this->name]);
	}
	
	public function email()
	{
		return form::emailsyntax_is_valid($_POST[$this->name]);
	}
	
	public function two()
	{
		return preg_match("/^\d{2}$/",$_POST[$this->name]);
	}
	
	public function four()
	{
		return preg_match("/^\d{4}$/",$_POST[$this->name]);
	}
	
	public function phone()
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
	public $type = 'input';
	public $maxlength;

	public function element_print()
	{
		print '<input name="' . $this->name . '" type="text" class="' . $this->do_class() . '" ';
		if(strlen($this->maxlength) > 0)
			print 'maxlength="' . $this->maxlength . '" ';
		print 'style="' . $this->css . '" value="' . (isset($_POST[$this->name]) ? form::formout($_POST[$this->name]) : $this->value) . '">';
	}
}

class password extends element
{
	public $type = 'password';

	public function element_print()
	{
		print '<input name="' . $this->name . '" class="' . $this->do_class() . '" ';
		if(strlen($this->maxlength) > 0)
			print 'maxlength="' . $this->maxlength . '" ';
		print 'style="' . $this->css . '" value="' . (isset($_POST[$this->name]) ? form::formout($_POST[$this->name]) : $this->value) . '">';
	}
}

class check extends element
{
	public $type = 'check';
	
	public function element_print()
	{
		print '<input name="' . $this->name . '" type="checkbox" class="' . $this->do_class() . '" style="' . $this->css . '" class="' . $this->class . '" ' . ($this->selected?'checked':'') . '>';
	}
	
	public function required()
	{
		return 1;
	}
}

class text extends element
{
	public $type = 'text';

	public function element_print()
	{
		print '<textarea name="' . $this->name . '" rows="' . $this->row . '" cols="' . $this->col . '" class="' . $this->do_class() . '" style="' . $this->css . '">' . (isset($_POST[$this->name]) ? form::formout($_POST[$this->name]) : $this->value) . '</textarea>';
	}
}

class radio extends element
{
	public $type = 'radio';
	public $options = array();

	public function add_option($value,$label,$selected = 0)
	{
		$this->options[] = array('value' => $value, 'label' => $label, 'selected' => $selected);
	}
	
	public function element_print()
	{
		foreach($this->options as $values)
		{
			print '<label class="' . $this->do_class() . '">' . $values['label'] . '<input type="radio" class="' . $this->do_class() . '" name="' . $this->name . '" style="' . $this->css . '" value="' . form::formout($values['value']) . '" ';
			if(!isset($_POST[$this->name]) && $values['selected'])
				print 'selected';
			else if($isset($_POST[$this->name]) && $_POST[$this->name] == $value['value'])
				print 'selected';
			print "></label>\n";
		}
	}

	public function required()
	{
		return isset($_POST[$this->name]);
	}
}

class select extends element
{
	public $type = 'select';
	public $options = array();

	public function add_option($value,$label,$selected = 0)
	{
		$this->options[] = array('value' => $value, 'label' => $label, 'selected' => $selected);
	}
	
	public function element_print()
	{
		print '<select name="' . $this->name . '" class="' . $this->do_class() . '" style="' . $this->css . '">' . "\n";
		foreach($this->options as $values)
		{
			print '<option value="' . form::formout($values['value']) . '" ';
			if(!isset($_POST[$this->name]) && $values['selected'])
				print 'selected';
			else if(isset($_POST[$this->name]) && $_POST[$this->name] == $value['value'])
				print 'selected';
			print '>' . form::formout($values['label']) . "\n";
		}
		print "</select>\n";
	}
	
	public function required()
	{
		return isset($_POST[$this->name]);
	}
}

page::depend('db');

class handler
{
	public $table;
	public $handlers = array();
	public $where;
	public $extraset;

	public function add_handler($column,$value)
	{
		$this->handlers[$column] = $value;
	}
		
	public function handle()
	{
		db::update($this->table,$this->handlers,$this->where,$this->extraset);
	}
}
?>
