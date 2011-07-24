<?
/*
	define: SITE_ENABLED
	Is the site enabled or is it down due to maintainence?
*/
define('SITE_ENABLED', true);

/*
	define: DNAME
	The full domain name of your site - used for cookies, redirection.
*/
define('DNAME', '');

/*
	define: MODULES
	Modules you want to include. Order dependent.
	Modules include:
		AUTOMATICALLY INCLUDED MODULES - DO NOT PUT IN ARRAY:
			page
			db
		OPTIONAL:
			datetime
			form - requires page, db
			news - requires page, db
			login - requires page, db

	Add your own php file into the directory and load that in too if you want.
*/
	define('MODULES', array('datetime','form','news','login','spiffy') );
	
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
	define('MODULE_DIR',"");
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
*/
	define( 'DB_HOST', "localhost:3306" );
	define( 'DB_USER', "" );
	define( 'DB_PASSWORD', "" );
?>