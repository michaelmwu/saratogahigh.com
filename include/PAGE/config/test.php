<?
/*
	define: SITE_ENABLED
	Is the site enabled or is it down due to maintainence?
	
	define: MAINTAIN_ERROR
	If it isn't enabled, what to show visitors?
*/
define('SITE_ENABLED', true);
define('MAINTAIN_ERROR', 'Sorry, down for maintainence');

/*
	define: DNAME
	The full domain name of your site - used for cookies, redirection.
*/
define('DNAME', 'page.digitalitcc.com');

/*
	$modules
	Modules you want to include. Order dependent.
	Modules include:
		AUTOMATICALLY INCLUDED MODULES - DO NOT PUT IN ARRAY:
			page - page handler
			db - mysql database handler, functions
		OPTIONAL:
			datetime - date and time functions
			form - form printing and handling - requires page, db
			news - news printing - requires page, db
			login - user authentication - requires page, db
			spiffy - usefull functions
			bits - (infinite) bitmask handling

	Add your own php file into the directory and load that in too if you want.
*/
	$modules = array('datetime','form','news','login','spiffy','bits');
	
/*
	define: MODULE_DIR
	Full path to these module files. Please end with either / on *nix or
	\ on Windows.
	define: INCLUDE_DIR
	Full path to include files you want to use in pages (specifically header.php
	and footer.php). Please end with either / on *nix or \ on Windows.
	define: SIDE_DIR
	If you are using further includes, such as interchangable sidebars,
	define this to be the full path to those files. Please end with either
	/ on *nix or \ on Windows.
*/
	define('MODULE_DIR',"/var/www/include/PAGE/");
	define('INCLUDE_DIR',"");
	define('SIDE_DIR',"");

/*
	$config - Configuration array
	Put any global configuration variables here.

	Example:
	$config['css'] = 'shs';
*/

/*
	define: LOAD_GET
	Boolean. Automatically load $_GET variables into normal variables.
	
	define: LOAD_POST
	Boolean. Automatically load $_POST variables into normal variables.
	NOT RECOMMENDED
*/
	define('LOAD_GET',true);
	define('LOAD_POST',false);
	
/*
	define: MESSAGE_VARIABLE
	What is the GET variable you want to use for between page messages?
*/
	define('MESSAGE_VARIABLE', 'msg');
	
/*
	define: DB_HOST
	Where are you connecting to?

	define: DB_USER
	Who are you connecitng as?

	define: DB_PASSWORD
	What is your database user's password?
	
	define: DB_DATABASE
	What database do you want to select?
*/
	define('DB_HOST', "localhost:3306");
	define('DB_USER', "csf");
	define('DB_PASSWORD', "@T3aDaz3");
	define('DB_DATABASE', "csf_db");
	
/*
	define: USER_TABLE
	What table are the users stored in?
	
	define: ID_FIELD
	Unique identifying field
	
	define: UNAME_FIELD
	Username field
	
	define: PASSWORD_FIELD
	MD5 password field
	
	define: LEVEL_FIELD
	Permissions field
*/
	define('USER_TABLE','PHPBB_users');

	define('ID_FIELD','user_id');
	define('UNAME_FIELD','username');
	define('PASSWORD_FIELD','user_password');
	define('LEVEL_FIELD','USER_CSFLEVEL');
	
	require_once(MODULE_DIR . 'page.php');
?>