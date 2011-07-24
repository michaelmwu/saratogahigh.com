This is a general purpose object to help generate php pages with headers, login, etc..

Main code files:
page.php
login.php
datetime.php
db.php
form.php

Inclusion code files:
classes.php
globals.php

Includes:
header.php
footer.php

specifically for saratogahigh.org
sh.php

Called by:
$config = array( Config goes here ); // Configures

require_once 'page.php';

$page->init(); // Prints the header

$page->footer(); // Prints the footer.

PAGE.PHP

the object created is $page from the class page

defines:
INCLUDE_DIR - where the code files are stored - use full path and end with / please
SIDE_DIR - where the sidebar stuff is stored

Variables in $page that you can add before calling init, or by putting them in the config array in the form 'variable' => 'value' are:
$title - Main page title
$bodytitle - In the body title
$rightbar - Right hand sidebar
$leftbar - Left hand sidebar
$titlechar - Page title separator.. like - or |
$titlename - Prefix for title
$css - CSS stylesheet

Specifically for SH - $boxes is the name of the boxes for the page

Note, none of these are actually applied in page.php, instead in the header.php file.

Variables that do affect operation are:
$validate - Include INCLUDE_DIR/validate.php for validating admin

Informational variables:
$self - Relative to root path from outside view.
$scriptname - Name of script

Methods:
error($error) - tack $error onto error message - up to the header and footer to place it
do_error() - actually prints the errors
headstring($string) - adds extra parameters between the head tags;
do_headstring() - actually prints the extra head information
bodystring($string) - adds extra parameters to the body tag;
do_bodystring() - actually prints the extra body information
init() - initializes page: adds $_GET['msg'] to errors, validates, includes INCLUDE_DIR/header.php
siderbar($inc) - includes SIDE_DIR/$inc.php
navbar($title,array ( array( NAME, LINK, SELECTED ) for each element ) ) - formats a navbar of format $title: $page1 | $page2 | <!--Bolded-->$page3
	NAME is name of link, LINK is URL, SELECTED is bolded
do_navbar() - prints the navbar
javascript($script) - sets the $page->script variable to some variable, which should be printed somewhere in your header or footer
get_scriptname() - gets only the scriptname
highlight_file_linenum($data, $funclink = true, $return = false) - I have no idea, something about highlighting php with line numbers
php_process($arg_body) - replaces <?php ANYTHING ?> in $arg_body with the returned php value

LOGIN.PHP

the object created here is $login from class login

defines:
USER_TABLE - Your mysql table
USER_TABLE_PREFIX' - if you are lame like me and use USER_LIST, put USER for no good reason - it doesnt do anything

ID_FIELD - i assume you have a numeric id field like USER_ID
UNAME_FIELD - username field
PASSWORD_FIELD - md5 password field
LEVEL_FIELD - administrative privilege field - I like to use 2 for admin, 3 for prog, 1 for stupid ppl and 0 for nobodies

provides informational variables like:
$userR - mysql array of user row
$loggedin - are they logged in
$isclub - are they 1 or above
$isadmin - are they 2 or above
$isprog - are they 3 or above

methods:
go($user,$pass,$remember=0) - checks against database, remember ppl
forceLogin - makes them login
logout - logout

DATETIME.PHP

Provides date and time related functions through object $DateTime

many functions are based off the date function - hell all of them are, and most of them are named after the date function
hell, ill just explain off the date function
YMD( $now = 0 ) - prints YMD
jMY( $now = 0 ) - prints j M, Y
datetime( $now = 0 ) - prints Y-m-d H:i:s
fulldatetime( $now = 0 ) - prints l, F j, Y g:i A
month( $month = 0 ) - prints full text month off number
ndy( $now = 0 ) - prints n/d/y

DB.PHP

Provides mysql functions through $db and also site wide things

Defines:
SITE_ENABLED - is site enabled? if not, die with a maintain error msg
DNAME - full domain name

Methods:
	Prefix methods if you end everything in _LIST
		get_prefix_id($table,$id,$extrawhere = "") - gets a row with certain $id out of $table_LIST, and extra where statement if wanted
		get_prefix_row($table,$where) - gets a row from $table_LIST where $where
		get_prefix_result($table,$where,$order="") - gets a result from $table_LIST where $where and order = $order
		prefix_delete($table,$where) - deletes from $table_LIST where $where
		prefix_update($table,array($key => $value),$where,$extraset = "") - updates or inserts into $table_LIST $value(s) where $where and set $extraset too
	Regular methods
		get_row($table,$where) - get a row from $table where $where
		get_result($table,$where, $order="") - gets a result from $table where $where and $order = $order
		fetch_row($result) - fetches a row from $result
		update($table,array($key => $value),$where,$extraset = "") - updates or inserts into $table $value(s) where $where and set $extraset too
		delete($table,$where) - deletes from $table where $where

FORM.PHP

provides form generation and handling through $form and class handler

Constants:
IS_REQUIRED
IS_NUMERIC
IS_OWN_CHECK

methods for $form:
add_element($element object) - adds an element to the element array, is in order
check() - checks elements according to definitions
form_errors() - prints out specific form errors
element($elementname) - prints out specific element
formout($string) - prints htmlentities(stripslashes($string)) - for allowing $_POST to display correctly in input boxes

Classes:
element is the basic template for a form element
$type - text type of element
$name - name of element
$class - element css class
$css - specific css values
$req - uses IS_REQUIRED, IS_NUMERIC, IS_OWN_CHECK
$value - default value if no $_POST
$owncheck - provides own checking check, boolean
	
functions

numeric() - is the element numeric

furthermore, each element class extents this basic element class and defines its own required function
input
$maxlength - max length

password
$maxlength - max length

check
$selected - is it selected?

text
$row - rows
$col - columns

radio
$options - array( label => value ) of labels and values

select
$options - array( label => value ) of labels and values

there is a handler class with an easier interface to the $db->update() function
$table - the name of table to be updates
$where - where string
$extraset - extra sets

add_handler($column,$value) - adds a handler for mysql column $column to value $value
handle() executes all the handlers