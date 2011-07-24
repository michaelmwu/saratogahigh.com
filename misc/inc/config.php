<?
	define('DNAME','www.saratogahigh.com');

/*
	define: NO_CACHE
	Should the pages cache or not?
*/
	define('NO_CACHE', true);

/*
	define: OUTPUT_BUFFER
	Should PHP output buffer?
	
	$callback = What PHP should pass to ob_start
*/
	define('OUTPUT_BUFFER', true);

	// $callback = "whatever"; // Uncomment this if needed

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
	define('MODULES', serialize(array('db','datetime','bits','email','form','news','spiffy','smarty','xml')) );
	
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
	define('MODULE_DIR',"/var/www/html/page/");
	define('INCLUDE_DIR',"/var/www/html/inc/");

/*
	$config - Configuration array
	Put any global configuration variables here.

	Example:
	$config['css'] = 'shs';
*/

	$config['css'] = '/shs';

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
	define('MESSAGE_VARIABLE','msg');
	
/*
	define: USE_DB
	Use db?

	define: DB_HOST
	Where are you connecting to?

	define: DB_USER
	Who are you connecitng as?

	define: DB_PASSWORD
	What is your database user's password?

	define: DB_DATABASE
	What is your database?
*/
	define('USE_DB',true);

	define('DB_HOST',"localhost:3306");
	define('DB_USER',"saratogahigh");
	define('DB_PASSWORD',"k142857w");

	define('DB_DATABASE',"saratogahigh_com_-_main");
	
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
	define('USER_TABLE','USER_LIST');

	define('ID_FIELD','USER_ID');
	define('UNAME_FIELD','USER_UNAME');
	define('PASSWORD_FIELD','USER_PASS');
	define('LEVEL_FIELD','USER_STATUS');

/*
	define: ADMINPAGE_TABLE
	What table are admin pages stored in?
	
	define: ADMINPAGE_PAGE
	Page URI field
	
	define: ADMINPAGE_PRIVILEGE
	Page privilege field
*/
	define('ADMINPAGE_TABLE','ADMINPAGE_LIST');

	define('ADMINPAGE_PAGE','ADMINPAGE_PATH');
	define('ADMINPAGE_PRIVILEGE','ADMINPAGE_PERMISSION');
	
/*
	Only needed if you use module smarty

	define: SMARTY_TEMPLATE_DIR
	Smarty's template directory
	
	define: SMARTY_COMPILE_DIR
	Smarty's compiled template directory
	
	define: SMARTY_CONFIG_DIR
	Smarty's configuration directory
	
	define: SMARTY_CACHE_DIR
	Smarty's cached directory
*/
/* Since *I* don't actually use smarty
	define('SMARTY_TEMPLATE_DIR',"");
	define('SMARTY_COMPILE_DIR',"");
	define('SMARTY_CONFIG_DIR',"");
	define('SMARTY_CACHE_DIR',"");
*/

	require_once(MODULE_DIR . 'page.php');
	
	require_once(INCLUDE_DIR . 'db.php');
?>