<?
/**
 * Source.php : Show source of a php script
 * 
 * You can use your own source.php file, param is for file to view is $_REQUEST['script']
 * /!\ Be carreful you must secure this page !!!! /!\
 * 
 * Don't forget to secure this script !
 * With something like that or whatever you want ! :)
 * 
 * if ( !Rights::is_Admin() )
 * {
 *  	redirect_login();
 * 		exit;
 * }
 * 
 * @filesource
 * @package PHP_Debug
 * @see Debug
 * @since 10 Dec 2003
 */
 
/**
 * Lang Var
 */ 
$txtScriptNameNeeded = "Script Name needed";
$txtError = "ERROR";
$txtViewSource = "View Source of";
$txtWrongExt = "Only PHP or include script names are allowed";
 
/**
 * Other Var
 */  
$code_back_color = "#F5F5F5";

/** 
 * highlight_file_line() : Modified version of show_source, with lines numbers
 * 
 * @param	string		$file		Path of the file to show source
 * 
 * @since 10 Nov 2003
 */ 
function highlight_file_line($file)
{
	ob_start();
    show_source($file);
    $file_buf = ob_get_contents();
    ob_end_clean();
	
	$file_buf = "<code><ol><li>". $file_buf;
	$file_buf = ereg_replace( "<br />" , "<li>" , $file_buf );
	$file_buf = "</ol></code>". $file_buf;
	
	return $file_buf;
}
 

// Start HTML
print('<html><body bgcolor="#FFFFFF">');

/**
 * Output Source
 */ 
if ( !$_REQUEST['script'] )
{ 
   echo "<br><b>== $txtError : $txtScriptNameNeeded</b><br>";
} 
else 
{ 
	$script = $_REQUEST['script'];
	print("<h3>== $txtViewSource : ". $_REQUEST['script'] ."</h3>");

   if (ereg("(\.php|\.inc|\.tpl|\.txt)$",$script))
   {
		print("<table bgcolor=\"$code_back_color\"><tr><td>");
		print(highlight_file_line($script)); 
		print('</td></tr></table>');
   }
   else 
	   print("<b>== $txtError : $txtWrongExt</b>");
}

// Close HTML
print('</body></html>');
?>