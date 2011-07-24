<?
define('XML_POST', 2);
define('XML_POST_TRY', 1);
define('XML_GET', 0);

class XMLHTTPRequest
{
	var $objectname = 'xmlhttp';
	var $request_variable = 'XMLRequest';
	var $function_variable = 'XMLFunction';
	var $args_variable = 'XMLArgs';
	var $print_variable = 'XMLPrint';
	var $method = XML_POST_TRY;
	var $registered_functions = array();

	function objectName($name)
	{
		$this->objectname = $name;
	}

	function prepareObject()
	{
		$script = '<script type="text/javascript">
<!--
	var ' . $this->objectname . '=false;
	/*@cc_on @*/
	/*@if (@_jscript_version >= 5)
	// JScript gives us Conditional compilation, we can cope with old IE versions.
	// and security blocked creation of the objects.
	 try {
	  ' . $this->objectname . ' = new ActiveXObject("Msxml2.XMLHTTP");
	 } catch (e) {
	  try {
	   ' . $this->objectname . ' = new ActiveXObject("Microsoft.XMLHTTP");
	  } catch (E) {
	   ' . $this->objectname . ' = false;
	  }
	 }
	@end @*/
	if (!' . $this->objectname . ' && typeof XMLHttpRequest!=\'undefined\') {
		' . $this->objectname . ' = new XMLHttpRequest();
	}
//-->
</script>';
		return $script;
	}
	
	function set_method($int)
	{
		$this->method = $int;
	}
	
	function register_function($function)
	{
		$this->registered_functions[$function] = true;
	}
	
	function check_function($function)
	{
		if($this->registered_functions[$function])
			return true;
		return false;
	}
	
	function make_request($urlname,$method,$id,$function,$args = array(),$print = false)
	{
		$script = 'xmlhttp.open("' . $method . '", ' . $urlname . ',true);
	xmlhttp.onreadystatechange=function()
	{
		if (xmlhttp.readyState==4)
		{
			document.getElementById("' . $id . '").innerHTML = xmlhttp.responseText;
			}
		}
		xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");' . "\n";
		$script .= "xmlhttp.send('" . $this->request_variable . '=1&' . $this->function_variable . '=' . $function . '&' . $this->args_variable . '=' . (is_array($args)?serialize($args):$args) . '&' . $this->print_variable . '=' . $print . "');";
		return $script;
	}
	
	function handle_request()
	{
		$request = false;
		if($this->method >= XML_POST_TRY && isset($_POST[$this->request_variable]) && $request = true)
			$method =& $_POST;
		if(!$request && $this->method <= XML_POST_TRY && isset($_GET[$this->request_variable]) && $request = true)
			$method =& $_GET;
		$function = $method[$this->function_variable];
		if(is_callable($function))
		{
			if($this->check_function($function))
			{
				$args = unserialize(stripslashes($method[$this->args_variable]));
				if(is_array($args))
					$return = call_user_func_array($function,$args);
				else
					$return = call_user_func($function,$args);
					
				if($method[$this->function_variable])
					print $return;
			}
			die();
		}
		return false;
	}
}

$xml = new XMLHTTPRequest();
?>