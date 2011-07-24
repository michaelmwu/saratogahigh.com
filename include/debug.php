<?
/**
 * +--------------------------------------------------------------------------+
 * +-- PHP_Debug : A simple and fast way to debug PHP code                    |
 * +--                                                                        |
 * +-- Support : Vernet Loic (coil@strangebuzz.com)                           |
 * +--------------------------------------------------------------------------+
 * |                                                                          |
 * | This PHP debug libray offers you the ability to debug your PHP code      |
 * |                                                                          |
 * | - Pear integration                                                       |
 * | - PHP Process time                                                       |
 * | - Database and query process time                                        |
 * | - Dump of all type of variable in a graphical way                        |
 * | - Functionnal debug                                                      |
 * | - Debug queries                                                          |
 * | - Show number of database queries executed                               |
 * | - Allow to search in all debug infos                                     |
 * | - Direct links to test queries in Phpmyadmin                             |
 * | - Show globals var ( $GLOBALS, $_POST, $_GET ... )                       |
 * | - Enable or disable the debug infos you want to see                      |
 * | - Check performance of chunk of php code                                 |
 * | - Customize the general display of your debug                            |
 * | - ... ( see doc for complete specification )                             |
 * +--------------------------------------------------------------------------+
 * 
 * @filesource
 * @package PHP_Debug
 * @author Loic Vernet, COil <coil@strangebuzz.com>
 * @license http://www.php.net/license/2_02.txt The PHP License, version 2.02
 * @example tests/debug_test_min.php Minimal example
 * @example tests/debug_test.php Full example
 * @todo Check TODO file or https://sourceforge.net/tracker/?group_id=95715
 */
 
/**
 * Possible version of class Debug
 */ 
define ( 'DBG_VERSION_STANDALONE' , 0 );
define ( 'DBG_VERSION_PEAR' , 1 );
define ( 'DBG_VERSION_DEFAULT' , DBG_VERSION_STANDALONE );
define ( 'DBG_VERSION' , DBG_VERSION_STANDALONE );
define ( 'DBG_RELEASE' , 'BETA 1.0' );
 
/**
 * Only include Pear libraries for Pear version
 */
if ( DBG_VERSION == DBG_VERSION_PEAR ) 
{
	/** 
	 * Include Pear Library
	 */
	require_once 'PEAR.php';
	
	/** 
	 * Include Pear::Var_Dump Library
	 */
	require_once 'Var_Dump.php';
}


/**
 * Eventual external constants
 */
if ( !defined('STR_N') ) 
	define( 'STR_N' , '' );

if ( !defined('CR') ) 
	define( 'CR' , "\r\n" );

/**
 * DBG_MODE Constants, define the different available debug modes.
 *
 * Here are the available modes :
 * - DBG_MODE_OFF : Debug mode is OFF
 * - DBG_MODE_USERPERF : Base debug mode,
 * - DBG_MODE_QUERY : DBG_MODE_USERPERF + queries
 * - DBG_MODE_QUERYTEMP : DBG_MODE_QUERY + included files
 * - DBG_MODE_FULL : All available debug infos ( including $GLOBALS array that is quiet big )
 * - DBG_MODE_AUTO : Mode auto take the mode of Debug Object
 * ( not implemented )
 */
define ( 'DBG_MODE_OFF' , 0 );
define ( 'DBG_MODE_USERPERF' , 1 );
define ( 'DBG_MODE_QUERY' , 2 );
define ( 'DBG_MODE_QUERYTEMP' , 3 );
define ( 'DBG_MODE_FULL' , 4 );
define ( 'DBG_MODE_AUTO' , 5);
define ( 'DBG_MODE_DEFAULT' , DBG_MODE_USERPERF);

/**
 * This is a constant for the credits. For me :p
 */
define ( 'DBG_CREDITS' , '<b>== PHP_Debug | By COil (2003) | <a href="mailto:coil@strangebuzz.com">coil@strangebuzz.com</a></b> | <a href="http://sourceforge.net/projects/phpdebug/">PHP_Debug Project Home</a>');

/**
 * These are constant for DumpArr() and DumpObj() functions.
 * 
 * - DUMP_ARR_DISP : Tell the functions to display the debug info.
 * - DUMP_ARR_STR : Tell the fonction to return the debug info as a string
 * - DBG_ARR_TABNAME : Default name of Array
 * - DBG_ARR_OBJNAME : Default name of Object
 */
define ( 'DUMP_ARR_DISP' , 1 );
define ( 'DUMP_ARR_STR' , 2 );
define ( 'DUMP_ARR_TABNAME' , 'Array' );
define ( 'DUMP_ARR_OBJNAME' , 'Object' );

/**
 * These are constants to define environment Super array
 */ 
define ( 'DBG_GLOBAL_GET' , 0 );
define ( 'DBG_GLOBAL_POST' , 1 );
define ( 'DBG_GLOBAL_FILES' , 2 );
define ( 'DBG_GLOBAL_COOKIE' , 3 );
define ( 'DBG_GLOBAL_REQUEST' , 4 );
define ( 'DBG_GLOBAL_SESSION' , 5 );
define ( 'DBG_GLOBAL_GLOBALS' , 6 );

/**
 * Debug : Class that provide a simple and fast way to debug a php application.
 *
 * Debug class allows you to debug all you need about your application
 * Debug queries, process time, dump variable and much more...
 * 
 * @package PHP_Debug
 * @author COil, Loic Vernet <coil@strangebuzz.com>
 * @version BETA 1.0
 * @since 17 oct 2003
 */
class Debug
{
	/**
	 * Debug Mode 
	 *
	 * @var integer
	 * @access public
	 * @see DBG_MODE constants.
	 */
	var $DebugMode = DBG_MODE_USERPERF;

	/**
	 * This is the array where debug line are.
	 *
	 * @var array $_DebugBuffer
	 * @access private
	 * @see DebugLine
	 */
	var $_DebugBuffer = array();

	/**
	 * Enable or disable Credits in debug infos.
	 *
	 * @var integer $DisableCredits
	 * @access public
	 * @see DebugLine
	 */
	var $DisableCredits = false;

	/**
	 * HTML Start String
	 * 
	 * Start string of HTML layout
	 * 
	 * @var string $HtmlTableStart
     * @access public
	 */ 
	var $HtmlTableStart = '<br><table cellspacing="0" cellpading="0" border="1" width="100%">';

	/**
	 * HTML end string to close HTML display for debug layout
	 * 
	 * @var string $HtmlTableEnd
     * @access public
	 */ 
	var $HtmlTableEnd = '</table>';

	/**
	 * Process perf status, 1 = a process is being running, 0 = no activity
	 * 
	 * @var String	$_ProcessPerfStatus
	 * @access private
	 */
	var $_ProcessPerfStatus = false;

	/**
	 * General debug start time
	 * 
	 * @var integer $_ProcessPerfStart
	 * @access private
	 */
	var $_ProcessPerfStartGen = 0; 		// Global Start Time
	
	/**
	 * Debug Start time
	 * 
	 * @var integer $_ProcessPerfStart
	 * @access private
	 */
	var $_ProcessPerfStart = 0; 		// Local Start Time

	/**
	 * Debug End time
	 * 
	 * @var integer $_ProcessPerfEnd
	 * @access private
	 */
	var $_ProcessPerfEnd = 0;

	/**
	 * Global database process time
	 * 
	 * @var integer $_DataPerfTotal
	 * @access private
	 */
	var $_DataPerfTotal = 0;

	/**
	 * Number of performed queries
	 * 
	 * @var integer $_DataPerfQry
	 * @access private
	 */
	var $_DataPerfQry = 0;

	/**
	 * Enable or disable, included and required files
	 * 
	 * @var boolean $ShowTemplates
	 * @access public
	 */ 
	var $ShowTemplates = true;

	/**
	 * Enable or disable, pattern removing in included files
	 * 
	 * @var boolean $RemoveTemplatesPattern
	 * @access public
	 */
	var $RemoveTemplatesPattern = false;

	/**
	 * Pattern list to remove in the display of included files
 	 * 
	 * @var boolean $RemoveTemplatesPattern
	 * @access private
	 */ 
	var $_TemplatesPattern = array();

	/**
	 * Enable or disable $globals var in debug
	 *   
	 * @var boolean $ShowGlobals
	 * @access public
	 */	
	var $ShowGlobals = false;

	/** 
	 * Enable or disable search in debug 
	 * 
	 * @var boolean $EnableSearch
	 * @access public
	 */	
	var $EnableSearch = true;

	/** 
	 * Enable or disable the use of $_REQUEST array instead of $_POST + _$GET + $_COOKIE + $_FILES
	 * 
	 * @var boolean $UseRequestArr
	 * @access public
	 */	
	var $UseRequestArr = false;
	
	/** 
	 * View Source script path
	 * 
	 * @var string $ViewSourceScriptPath, default : Current directory
	 * @access public
	 */	
	var $ViewSourceScriptPath = '.';
	
	/** 
	 * View Source script path
	 * 
	 * @var string $ViewSourceScripName
	 * @access public
	 */		
	var $ViewSourceScriptName = 'source.php';

	/** 
	 * Color for DebugType : $DebugType => Color Code of text
	 * 
	 * @var array $CellColors
	 * @access public
	 */
	var $CellColors = array (   DBGLINE_STD => '#000000', 
								DBGLINE_QUERY => '#FFA500', 
								DBGLINE_QUERY_REL => '#228B22',
								DBGLINE_ENV => '#FF0000',
								DBGLINE_CURRENTFILE => '#000000',
								DBGLINE_APPERROR => '#FF0000',
								DBGLINE_CREDITS => '#000000',
								DBGLINE_SEARCH => '#000000',
								DBGLINE_OBJECT => '#000000',
								DBGLINE_PROCESSPERF => '#000000',
								DBGLINE_TEMPLATES => '#000000',
								DBGLINE_PAGEACTION => '#708090',
								DBGLINE_ARRAY => '#000000' );
								
	/**
	 * Bold style for DebugType : $DebugType => Bold Style
	 * 
	 * @var array $CellBoldStatus
	 * @access public
	 */
	var $CellBoldStatus = array ( 	DBGLINE_STD => false,
									DBGLINE_QUERY => true,
									DBGLINE_QUERY_REL => false,
									DBGLINE_ENV => false,
									DBGLINE_CURRENTFILE => true,
									DBGLINE_APPERROR => true,
									DBGLINE_CREDITS => true,
									DBGLINE_SEARCH => true,
									DBGLINE_OBJECT => false,
									DBGLINE_PROCESSPERF => false,
									DBGLINE_TEMPLATES => false,
									DBGLINE_PAGEACTION => true,
									DBGLINE_ARRAY => false );

	/**
	 * Bold style for DebugType : $DebugType => Bold Style
	 * 
	 * @var array $CellBoldStatus
	 * @access public
	 */
	var $DisplayTypeInSearch = array ( 	DBGLINE_STD => false ,
										DBGLINE_QUERY => false ,
										DBGLINE_QUERY_REL => false ,
										DBGLINE_ENV => false ,
										DBGLINE_CURRENTFILE => true ,
										DBGLINE_APPERROR => false ,
										DBGLINE_CREDITS => true ,
										DBGLINE_SEARCH => true ,
										DBGLINE_OBJECT => false ,
										DBGLINE_PROCESSPERF => true ,
										DBGLINE_TEMPLATES => false ,
										DBGLINE_PAGEACTION => false ,
										DBGLINE_ARRAY => false );

	/** 
	 * Base URL of phpmyadmin	
	 * 
	 * @var string $PhpMyAdminUrl	
	 * @access public
	 */
	var $PhpMyAdminUrl = 'http://127.0.0.1/mysql';

	/** 
	 * Name of database that we are working on
	 * 
	 * @var string $CurrentDatabase	
	 * @access public
	 */
	var $DatabaseName = 'mysql';

   	/**
	 * Debug() : Constructor of Debug object
	 *
	 * Set debugmode, credits line and search line are added at creation
	 * if they are activated.
	 * 
	 * @param integer	$debugmode
	 *
	 * @return mixed 	Debug Object
	 *
	 * @see Debug()
	 * @since 17 Oct 2003
	 * @access public
	 */	 	
	function Debug($debugmode=DBG_MODE_DEFAULT)
	{		
		$this->DebugMode = $debugmode;
		$this->_ProcessPerfStartGen = $this->getMicroTime(microtime());

		// Credits line
		if ( $this->DisableCredits == false )
			$this->addDebug(DBG_CREDITS,DBGLINE_CREDITS);
		
		// Search line
		if ( $this->EnableSearch == true )
			$this->addDebug(STR_N,DBGLINE_SEARCH);
										
	}

	/**
	 * setDebugMode() : Set debug mode of Debug Object
	 *  
	 * @param integer	$debugmode
	 * 
	 * @see $DebugMode
	 * @since 14 Nov 2003
	 * @access public
	 */ 
	function setDebugMode($debugmode)
	{
		$this->DebugMode = $debugmode;
	}

	/**
	 * getDebugMode() : Return current debug mode
	 *  
	 * @see $DebugMode
	 * @since 14 Nov 2003
	 * @access public
	 */ 
	function getDebugMode()
	{
		return($this->DebugMode);
	}

   	/**
	 * getColorCode() : Retrieve color code of the debug cell
	 *
	 * @return string
	 * 
	 * @see CellColor
	 * @since 25 Oct 2003
	 * @access public
	 */
	function getColorCode($DebugLineType)
	{		 
		return '<font color="'. $this->CellColors[$DebugLineType] . '">';
	}
		
   	/**
	 * getBoldCode() : Retrieve Bold cell status of the debug cell
	 *
	 * @return string
	 * 
	 * @see CellBoldStatus
	 * @since 25 Oct 2003
	 * @access public
	 */
	function getBoldCode($DebugLineType)
	{
		return ( $this->CellBoldStatus[$DebugLineType] ) ? "<b>" : STR_N;
	}

	/**
	 * getMicroTime() : Return micotime from a timestamp
	 *   
	 * @param $time 	Timestamp to retrieve micro time
	 * @return numeric 	Micotime of timestamp param
	 * 
 	 * @see $DebugMode
	 * @since 14 Nov 2003
	 * @access public
	 */ 
	function getMicroTime($time)
	{ 	
		list($usec, $sec) = explode(" ",$time);
		return ( (float)$usec + (float)$sec ); 
	}

	/**
	 * getElapsedTime() : get elapsed time between 2 timestamp
	 *   
	 * @param $timeStart 	Start time ref
	 * @param $timeEnd 		End time ref
	 * @return numeric difference between the two time ref
	 * 
	 * @see getProcessTime()
	 * @since 20 Oct 2003
	 * @access public
	 */ 
	function getElapsedTime($timeStart, $timeEnd)
	{			
		return round($timeEnd - $timeStart,4);
	}
	
	/**
	 * getProcessTime() : Get global process time
	 * 
	 * @return	numeric		Elapsed time between the start and end time
	 * 
	 * @see getElapsedTime()
	 * @since 20 Oct 2003
	 * @access public
	 */ 
	function getProcessTime()
	{
		return ( $this->getElapsedTime($this->_ProcessPerfStartGen,$this->_ProcessPerfEnd) );
	}

	/**
	 * _StopProcessTime() : Fix the end time of process
	 * 
	 * @since 17 Novt 2003
	 * @access private
	 */ 
	function _StopProcessTime()
	{
		$this->_ProcessPerfEnd = $this->getMicroTime(microtime());
	}
	
	/**
	 * DumpArr() : Display all content of an array
	 * 
	 * Mode DUMP_ARR_DISP display the array
	 * Mode DUMP_ARR_STR return the infos as a string
	 * 
	 * @param 	array 	 	$arr		array	Array to debug
	 * @param 	string	 	$varname	Name of the variable
	 * @param	integer 	$mode		Mode of function
	 * @return 	mixed 					Nothing or string depending on the mode
	 * 
	 * @since 20 Oct 2003
	 * @static
	 * @access public
	 */ 
	function DumpArr($arr, $varname=DUMP_ARR_TABNAME, $mode=DUMP_ARR_DISP)
	{
		ob_start();
		print_r($arr);		
		$dbg_arrbuffer = htmlentities(ob_get_contents());
		ob_end_clean();
		
		$dbg_arrbuffer = "<br><pre><b>$varname</b> :". CR . $dbg_arrbuffer . '</pre>';

		switch($mode)
		{
			default:
			case DUMP_ARR_DISP:
				print($dbg_arrbuffer);
			case DUMP_ARR_STR:
				return($dbg_arrbuffer);
			break;
		}
	}

	/**
	 * DumpObj() : Debug an object or array with Var_Dump pear package
	 * 
	 * ( Not useable with standalone version )
	 * Mode DUMP_ARR_DISP display the array
	 * Mode DUMP_ARR_STR return the infos as a string
	 * 
	 * @param 	array 	 	$obj		Object to debug
	 * @param 	string	 	$varname	Name of the variable
	 * @param	integer 	$mode		Mode of function
	 * @return 	mixed 					Nothing or string depending on the mode
	 * 
	 * @since 10 Nov 2003
	 * @static
	 * @access public
	 */ 
	function DumpObj($obj, $varname=DUMP_ARR_OBJNAME, $mode=DUMP_ARR_DISP)
	{
		// Check Pear Activation
		if (DBG_VERSION == DBG_VERSION_STANDALONE) 
			return Debug::DumpArr($obj, $varname, $mode);
	
		ob_start();
		Var_Dump::display($obj);
		$dbg_arrbuffer = ob_get_contents();
		ob_end_clean();	

		if ( empty($varname) )
			$varname = DUMP_ARR_OBJNAME;

		$dbg_arrbuffer = "<br><pre><b>$varname</b> :". CR . $dbg_arrbuffer . '</pre>';

		switch($mode)
		{
			default:
			case DUMP_ARR_DISP:
				print($dbg_arrbuffer);
			case DUMP_ARR_STR:
				return($dbg_arrbuffer);
			break;
		}
	}

	/**
	 * addDebug() : Build a new debug line info.
	 * 
	 * If $str is a String or an object we switch automatically to the corresponding
	 * debug info type. If debug mode is OFF does not do anything and return.
	 * Debug line is build, then it is added in the DebugLine array.
	 * 
	 * @param 	string		$str		debug string/object
	 * @param 	integer 	$typeDebug	Debug type of line ( Optional, Default = DBGLINE_STD )
	 * @param	string 		$file		File of debug info ( Optional, Default = "" )
	 * @param	string 		$line		Line of debug info ( Optional, Default = "" )
	 * @param 	string 		$title		Title of variable if applicable ( Optional, Default = "" )
	 * 
	 * @since 10 Nov 2003
	 * @access public
	 */ 
	function addDebug($str, $typeDebug=DBGLINE_STD, $file = STR_N, $line = STR_N, $title=STR_N )
	{
		if ($this->DebugMode == DBG_MODE_OFF )
			return;

		// If argument is an array change debug type
		if ( is_array($str)  && $typeDebug == DBGLINE_STD)
			$typeDebug = DBGLINE_ARRAY;

		// If argument is an object change debug type
		if ( is_object($str) && $typeDebug == DBGLINE_STD)
			$typeDebug = DBGLINE_OBJECT;

		// Query config for query debug line type		
		$PhpMyAdminUrl = ( $typeDebug == DBGLINE_QUERY ? $this->PhpMyAdminUrl : '' );
		$DatabaseName = ( $typeDebug == DBGLINE_QUERY ? $this->DatabaseName : '' );
										
		$DbgLine = new DebugLine(	$str, $typeDebug, $file , $line, $title, 
									$this->getColorCode($typeDebug),
									$this->getBoldCode($typeDebug),
									$PhpMyAdminUrl, 
									$DatabaseName );
		$this->_DebugBuffer[] = $DbgLine;
	}

	/**
	 * DebugPerf() : Get process time and stats about database processing.
	 * 
	 * If $processtype is DBG_PERF_QRY then a query has been run, otherwise it
	 * is another database process. The start and end time is computed, and the
	 * global time is updated.
	 * 
	 * @param 	integer 	$processtype	Type of database debug query or database related.
	 * 
	 * @since 20 Oct 2003
	 * @access public
	 */ 
	function DebugPerf($processtype = DBGLINE_QUERY)
	{
		// Lang
		$txtPHP = 'PHP';
		$txtSQL = 'SQL';				
		$txtSECOND = 's';

		switch($this->_ProcessPerfStatus)
		{
			// Start Timer
			default:
			case false:
				$this->_ProcessPerfStart = $this->getMicroTime(microtime());
				$this->_ProcessPerfStatus = true;
				
				// Additional processing depending of dataperf type request				
				switch($processtype)
				{
					case(DBGLINE_QUERY):
						$this->_DataPerfQry++;
					break;				

					default:
					break;
				}
				
			break;
			
			// Stop Timer and add to database perf total
			case true;
				$this->_ProcessPerfEnd = $this->getMicroTime(microtime());
				$qry_time = $this->getElapsedTime($this->_ProcessPerfStart,$this->_ProcessPerfEnd);

				$this->_ProcessPerfStart = $this->_ProcessPerfEnd = 0;
				$this->_ProcessPerfStatus = false;
				
				// Additional processing depending of dataperf type request
				switch($processtype)
				{
					default:
					case(DBGLINE_STD);
						$this->_DebugBuffer[$this->_getLastDebugLineID($processtype)]->DebugDisplayString .= " <b><font color=\"black\">[ $txtPHP : ". $qry_time ."$txtSECOND ]</font></b>";
					break;					

					case(DBGLINE_QUERY_REL):						
					case(DBGLINE_QUERY):
						//Now set the Time for the query in the DebugLine info
						$this->_DebugBuffer[$this->_getLastDebugLineID($processtype)]->DebugDisplayString .= " <b><font color=\"black\">[ $txtSQL+$txtPHP : ". $qry_time ."$txtSECOND ]</font></b>";

						// Global database perf
						$this->_DataPerfTotal += $qry_time;
					break;				
				}				
			break;
		}		
	}

	/**
	 * CancelPerf() : Cancel a process time monitoring, error or misc exception
	 * 
	 * @param Integer	$processtype	Type of the process to cancel
	 * 
	 * @since 13 Dec 2003
	 * @access public
	 */ 
	function CancelPerf($processtype)
	{
		$this->_ProcessPerfStart = $this->_ProcessPerfEnd = 0;
		$this->_ProcessPerfStatus = false;
		
		switch($processtype)
		{
			case(DBGLINE_QUERY):
				$this->_DataPerfQry--;
			break;				

			default:
			break;
		}
	}
	
	/**
	 * getLastDebugLineID : Retrieve the ID of last debugline type in _DebugBuffer array
	 * 
	 * @param integer 	$debugtype		Type of debug we want to get the last index
	 * 
	 * @see DebugPerf(), _DebugBuffer
  	 * @since 20 Nov 2003
	 * @access private
	 */ 
	function _getLastDebugLineID($debugtype)
	{
		$tmparr = $this->_DebugBuffer;
		krsort($tmparr);		
		
		foreach ( $tmparr as $lkey => $lvalue )
		{
			if ( $lvalue->DebugType == $debugtype )
				return $lkey;
		}
	}
	
	/**
	 * _IncludeRequiredFiles() : Build debug line with all included or required files for current file.
	 * 
	 * Use the get_required_files() function, then build the formatted string with
	 * links to edit and to view source of each files. Debug info line is added in
	 * current debug object.
	 * 
	 * @since 20 Oct 2003
	 * @access private
	 */ 
	function _IncludeRequiredFiles()
	{
		// Lang
		$txtViewSource = 'View Source';
		$txtEditSource = 'Edit';
		$txtIncRecFiles = 'Included/Required files';

		$l_reqfiles = get_required_files();		
		$l_strinc = "<b>== $txtIncRecFiles (". count($l_reqfiles) .') :</b>'. CR;
		$l_strinc .=  '<font color="#000080">';

		foreach( $l_reqfiles as $f_file )
		{
			$view_source_link = $edit_link = $f_file;

			// Pattern deletion
			if ( $this->RemoveTemplatesPattern == true && count($this->_TemplatesPattern) )
				$f_file = strtr( $f_file, $this->_TemplatesPattern );
								
			$view_source_link = ' <a href="' . $this->ViewSourceScriptPath .'/'. $this->ViewSourceScriptName .'?script='. $view_source_link . '">'. $txtViewSource .'</a>';
			$edit_link = ' <a href="' . $edit_link . '">'. $txtEditSource .'</a>';

			$l_strinc .= $f_file . $view_source_link . $edit_link . CR;
		}
		$this->addDebug($l_strinc,DBGLINE_TEMPLATES);
	}

	/**
	 * addRequiredFilesPattern() : Add a remove pattern to remove pattern array.
	 * 
	 * @param string 	$pattern	Pattern to add
	 * 
	 * @since 20 Oct 2003
	 * @access public
	 */ 
	function addRequiredFilesPattern($pattern, $replace_str=STR_N)
	{
		$this->_TemplatesPattern[$pattern] = $replace_str;
	}

	/**
	 * delRequiredFilesPattern() : Del a remove pattern from remove pattern array.
	 * 
	 * @param string 	$pattern	Pattern to remove
	 * 
	 * @since 20 Oct 2003
	 * @access public
	 */ 
	function delRequiredFilesPattern($pattern)
	{
		unset($this->_TemplatesPattern[$pattern]);
	}

	/**
	 * addSuperArray() : Add a super array to the debug informations
	 * 
	 * @see DBG_GLOBAL, DebugDisplay()
	 * @since 12 Dec 2003
	 * @access private
	 */ 
	function _addSuperArray($SuperArrayType)	
	{
		// Lang
		$txtVariable = "Var";
		$txtNoVariable = "NO VARIABLE";

		$NoVariable =  " -- $txtNoVariable -- ";

		switch($SuperArrayType)		
		{
			case(DBG_GLOBAL_GET):
				$SuperArray = $_GET;
				$ArrayTitle = '_GET';
				$Title = "$ArrayTitle $txtVariable";
			break;

			case(DBG_GLOBAL_POST):
				$SuperArray = $_POST;
				$ArrayTitle = '_POST';
				$Title = "$ArrayTitle $txtVariable";
			break;

			case(DBG_GLOBAL_FILES):
				$SuperArray = $_FILES;
				$ArrayTitle = '_FILES';
				$Title = "$ArrayTitle $txtVariable";
			break;

			case(DBG_GLOBAL_COOKIE):
				$SuperArray = $_COOKIE;
				$ArrayTitle = '_COOKIE';
				$Title = "$ArrayTitle $txtVariable";
			break;

			case(DBG_GLOBAL_REQUEST):
				$SuperArray = $_REQUEST;
				$ArrayTitle = '_REQUEST';
				$Title = "$ArrayTitle $txtVariable ( _GET + _POST + _FILES + _COOKIE )";
			break;

			case(DBG_GLOBAL_SESSION):
				$SuperArray = $_SESSION;
				$ArrayTitle = '_SESSION';
				$Title = "$ArrayTitle $txtVariable";
			break;
			
			case(DBG_GLOBAL_GLOBALS):
				$SuperArray = $GLOBALS;
				$ArrayTitle = 'GLOBALS';
				$Title = "$ArrayTitle $txtVariable";
			break;

			default:
			break;
		}
	
		$SectionBasetitle = "<b>== $Title (". count($SuperArray,COUNT_RECURSIVE) .') :';
		if ( count($SuperArray,COUNT_RECURSIVE) )
			$this->addDebug($SectionBasetitle .'</b>'. $this->DumpArr($SuperArray,$ArrayTitle,DUMP_ARR_STR),DBGLINE_ENV);
		else
			$this->addDebug($SectionBasetitle ."$NoVariable</b>",DBGLINE_ENV);
	}	

	/**
	 * _addProcessTime() : Add the process time information to the debug infos
	 * 
	 * @see DBG_GLOBAL, DebugDisplay()
	 * @since 12 Dec 2003
	 * @access private
	 */ 
	function _addProcessTime()
	{
		// Lang
		$txtExecutionTime = 'Execution Time Global';
		$txtPHP = 'PHP';
		$txtSQL = 'SQL';				
		$txtSECOND = 's';
		$txtOneQry = 'Query';
		$txtMultQry = 'Queries';
		$txtQuery = ( $this->_DataPerfQry > 1 ) ? $txtMultQry : $txtOneQry;

		// Performance Debug
		$ProcessTime = $this->getProcessTime();
		$php_time = $ProcessTime - $this->_DataPerfTotal;
		$sql_time = $this->_DataPerfTotal;
	
		$php_percent = round(($php_time / $ProcessTime) * 100 ,2);
		$sql_percent = round(($sql_time / $ProcessTime) * 100 ,2);								
	
		$this->addDebug("<b>== $txtExecutionTime : " .
				 $ProcessTime . "$txtSECOND [ $txtPHP , ". $php_time ."$txtSECOND , ". $php_percent  .'% ] - '.
				 				  "[ $txtSQL , ". $sql_time ."$txtSECOND , ". $sql_percent .'% , '. $this->_DataPerfQry ." $txtQuery ]</b>",DBGLINE_PROCESSPERF);
	}
	
	/**
	 *  HighLightKeyWords : Highligth a keyword in the debug info
	 */ 
	 function HighLightKeyWords($SearchStr)
	 {
	 	if ( !empty($SearchStr) and !empty($this->_DebugBuffer) ) 
		{
			for( $i = 0 ; $i < count($this->_DebugBuffer) ; $i++  )
			{
				if ( $this->DisplayTypeInSearch[$this->_DebugBuffer[$i]->DebugType] == false )
				{
					if ( !is_array($this->_DebugBuffer[$i]->_DebugString) && !is_object($this->_DebugBuffer[$i]->_DebugString) ) 
					{
						$this->_DebugBuffer[$i]->_DebugString = eregi_replace("$SearchStr","<font color=\"#FFA500\"><b>$SearchStr</b></font>",$this->_DebugBuffer[$i]->_DebugString);
						// PHP5 : $this->_DebugBuffer[$i]->_DebugString = str_ireplace("$SearchStr","<font color=\"#FFA500\"><b>$SearchStr</b></font>",$this->_DebugBuffer[$i]->_DebugString);
						$this->_DebugBuffer[$i]->_BuildDisplayString();
					}
				}
			}
	 	}	 	
	 }
	
	/**
	 * DebugDisplay() : This is the funtcion to display debug infos
	 * 
	 * @param string 		$search_str		Search string	( Optional, default = "" )
	 * @param integer		$display_mode	Mode of display ( DBG_MODE_AUTO )
	 * 
	 * @since 20 Oct 2003
	 * @access public
	 */ 
	function DebugDisplay($SearchSTR=STR_N, $display_mode=DBG_MODE_AUTO)
	{
		// Fix end time process the sooner possible
		$this->_StopProcessTime();

		// No Display
		if ( $display_mode == DBG_MODE_OFF ) 
			return;
		// Fix display mode
		else
			if ( $display_mode == DBG_MODE_AUTO) 
			 	$display_mode = $this->DebugMode;

		// HTML START
		print($this->HtmlTableStart);

		// Only DBG_MODE_USERPERF is implemented for now
		switch($display_mode)
		{
			default:
			case DBG_MODE_USERPERF:
				// Process time debug informations
				$this->_addProcessTime();
				
				// Include debug of included files
				if ( $this->ShowTemplates == true )
					$this->_IncludeRequiredFiles();

				// Divide Request tab
				if ( $this->UseRequestArr == false )
				{							
					// Include Post Var
					$this->_addSuperArray(DBG_GLOBAL_POST);		
	
					// Include Get Var
					$this->_addSuperArray(DBG_GLOBAL_GET);
	
					// Include File Var
					$this->_addSuperArray(DBG_GLOBAL_FILES);
					
					// Include Cookie Var
					$this->_addSuperArray(DBG_GLOBAL_COOKIE);
				}
				else
				// Only display Request Tab
				{
					// Include Request Var
					$this->_addSuperArray(DBG_GLOBAL_REQUEST);
				}

				// Include Sessions Var :Check if we have Session variables
				if ( !empty($_SESSION) )
					$this->_addSuperArray(DBG_GLOBAL_SESSION);

				// Include Globals Var
				if ( $this->ShowGlobals == true )
					$this->_addSuperArray(DBG_GLOBAL_GLOBALS);

				// Highlight Keywords
				if ( !empty($SearchSTR)  )
					$this->HighLightKeyWords($SearchSTR);
				
				// Display Debug cells
				foreach ( $this->_DebugBuffer as $lkey =>$lvalue )
				{
					$bufstr = $lvalue->getDebugLineString();
					
					// Display only cell that contains the search string or in force display array
					$ShowDebugLine = false;

					if ( !empty($SearchSTR)  )
					{
						// Check if Brut data is not an object or array
						$searchInto = ( is_array($lvalue->_DebugString) || is_object($lvalue->_DebugString) ? $this->DumpArr($lvalue->_DebugString,"",DUMP_ARR_STR)  : $lvalue->_DebugString );
						
						// Search string found
						if ( stristr($searchInto, $SearchSTR) && $lvalue->DebugType)
							$ShowDebugLine = true;
						
						// Forced debugline in search mode
						if ($this->DisplayTypeInSearch[$lvalue->DebugType] == true )
							$ShowDebugLine = true;
					}
					else
						$ShowDebugLine = true;
										
					if ( $ShowDebugLine == true ) 
						print($bufstr);
				}
			break;
		}

		// Close HTML Table
		print($this->HtmlTableEnd);
	}
	
	/**
	 * UniTtests() : Make the unit tests of the debug class
	 * 
	 * @since 22 Nov 2003
	 * @access public
	 */ 
	function UnitTests($fullmode = false)
	{
		$ClassName = get_class($this);		
		$txtTitle = "Class $ClassName Unit Tests (debug.php)";
		$Title = "======== $txtTitle";

		print('<pre><br><br>');
		print('<a name=\"'. $ClassName .'\">');
		print($Title);
		if ($fullmode == true) 
			Debug::DumpObj($this, $ClassName, DUMP_ARR_DISP);
		print('<br><br></pre>');
	}
}


/**
 * DEBUG LINE Types
 * 
 * - DBGLINE_STD 			: Standart debug, fonctionnal or other
 * - DBGLINE_QUERY 		: Query debug
 * - DBGLINE_QUERY_REL 	: Database related debug
 * - DBGLINE_ENV 			: Environment debug ( $GLOBALS... )
 * - DBGLINE_CURRENTFILE 	: Output current file that is debugged
 * - DBGLINE_APPERROR 	: Debug Error 
 * - DBGLINE_CREDITS 		: Class Credits
 * - DBGLINE_SEARCH 		: Search mode in debug
 * - DBGLINE_OBJECT 		: Debug object mode
 * - DBGLINE_PROCESSPERF	: Performance analysys
 * - DBGLINE_TEMPLATES	: Debug included templates
 * - DBGLINE_PAGEACTION	: Debug main page action 
 * - DBGLINE_ARRAY	    : Debug array mode
 * 
 * @category DebugLine
 */
define ( 'DBGLINE_STD' , 1 );
define ( 'DBGLINE_QUERY' , 2 );
define ( 'DBGLINE_QUERY_REL' , 3 );
define ( 'DBGLINE_ENV' , 4 );
define ( 'DBGLINE_CURRENTFILE' , 5 );
define ( 'DBGLINE_APPERROR' , 6 );
define ( 'DBGLINE_CREDITS' , 7 );
define ( 'DBGLINE_SEARCH' , 8 );
define ( 'DBGLINE_OBJECT' , 9 );
define ( 'DBGLINE_PROCESSPERF' , 10 );
define ( 'DBGLINE_TEMPLATES' , 11 );
define ( 'DBGLINE_PAGEACTION' , 12 );
define ( 'DBGLINE_ARRAY' , 13 );
define ( 'DBGLINE_DEFAULT' , DBGLINE_STD );

/**
 * DBGLINE_ERRORALERT, default error message for DBGLINE_APPERROR debug line type.
 * 
 */
define ( 'DBGLINE_ERRORALERT' , "/!\\" );

/**
 * DebugLine : Class that describe a debug line inforlations
 *
 * Descive all info and methode for a debug line, file 
 * location, color, type of debug, debug buffer, formatted debug buffer
 * title of debug variable if applicable
 * 
 * @package PHP_Debug
 * @author COil, Loic Vernet <webmaster@strangebuzz.com>
 * @version BETA 1.0
 * @since 18 oct 2003
 */
class DebugLine
{
	/** 
	 * File of debug info
	 * 
	 * @var integer $_Fine			
	 * @access private
	 */
	var $_File = '';

	/** 
	 * Line of debug info
	 * 
	 * @var integer $_Line			
	 * @access private
	 */
	var $_Line = '';
		
	/** 
	 * Complete Location ( formatted ) of debug infos ( Line + File )
	 * 
	 * @var integer $_Location 
	 * @access private
	 */
	var $_Location = '';

	/** 
	 * Title of debug line ( Object var ) 
	 * 
	 * @var String $_Linetitle 
	 * @see DumpObj()
	 * @access private
	 */
	var $_LineTitle = '';

	/** 
	 * String that store non formatted debug info 
	 * 
	 * @var string $_DebugString		
	 * @access private
	 */
	var $_DebugString = '';

	/**
	 * Formatted Debug info 
	 * 
	 * @var string $_DebugString 
	 * @access public
 	 */
	var $DebugDisplayString = '';

	/**
	 * Debug Type 
	 * 
	 * @var integer $DebugType 
	 * @see DBGLINE contants
	 * @access public
	 */
	var $DebugType = DBGLINE_DEFAULT;
	
	/** 
	 * Background Color for debug info cell
	 * 
	 * @var array $CellColor
	 * @access public
	 */
	var $CellColor = '';

	/** 
	 * Base URL of phpmyadmin	
	 * 
	 * @var string $PhpMyAdminUrl	
	 * @access public
	 */
	var $PhpMyAdminUrl = '';

	/** 
	 * Name of database that we are working on
	 * 
	 * @var string $CurrentDatabase	
	 * @access public
	 */
	var $DatabaseName = '';
	
	/**
	 * Bold style for debug info cell
	 * 
	 * @var array $CellBoldStatus
	 * @access public
	 */
	var $CellBoldStatus = false;
	
	/**
	 * Default Backgourd cell color
	 * 
	 * @var string $DefaultCellBackColor
	 * @access public
	 */ 
	var $DefaultCellBackColor = '#F8F8FF';
	
	/**
	 * HTML Cell start code 
	 * 
	 * @var string $HtmlPreCell	
	 * @access public
	 */
	var $HtmlPreCell = '<tr><td><pre>';

	/** 
	 * HTML Cell end code
	 * 
	 * @var string $HtmlPostCell
	 * @access public
	 */
	var $HtmlPostCell = '</td></tr>';
	
   	/**
	 * DebugLine() Constructor of class
	 *
	 * _Location is Automatically created at object instantation.
	 * Then the formatted debug HTML row is created.
	 *
	 * @param string		$str			Debug Information to store
	 * @param integer		$DebugType		Type of debug information
	 * @param string		$file			File of debug information
	 * @param string		$line			Debug of debug information
	 * @param string		$title			Title of debuged var
	 *
	 * @return mixed 	DebugLine Object
	 *
	 * @see _BuildDebugLineLocation()
	 * @since 17 Oct 2003
	 * @access public
	 */
	function DebugLine($str, $DebugType, $file, $line, $title, $CellColor, $CellBoldStatus, $PhpMyAdminUrl, $DatabaseName)
	{
		$this->_DebugString = $str;
		$this->DebugType = $DebugType;				
		$this->_File = $file;
		$this->_Line = $line;
		$this->_LineTitle = $title;
		$this->CellColor = $CellColor;
		$this->CellBoldStatus = $CellBoldStatus;
		$this->PhpMyAdminUrl = $PhpMyAdminUrl;
		$this->DatabaseName = $DatabaseName;

		$this->_Location = $this->_BuildDebugLineLocation($file,$line);
		$this->_BuildHtmlPreCell();
		$this->_BuildDisplayString();
	}
	
   	/**
	 * _BuildDisplayString() : Builds the formatted debug line
	 *
	 * Depending on the DebugType the formatted debug line is build.
	 * DebugDisplayString is built.
	 * One case by debug type.
	 *
	 * @see DebugType
	 * @since 20 Oct 2003
	 * @access private
	 */	 
	function _BuildDisplayString()
	{
		switch($this->DebugType)
		{
			// Standart output
			case 1:
				$this->DebugDisplayString = $this->_DebugString;
			break;

			// Query
			case 2:
				$txtExplain = 'Explain';
				$txtQuery = 'Query';

				$basehtml = ' </b><a target="phpmyadmin" href="';
				$url_query = $this->PhpMyAdminUrl .'/read_dump.php';
				$url_query .=  '?is_js_confirmed=0&lang=fr&server=1&db='. $this->DatabaseName .'&pos=0&goto=db_details.php&zero_rows=&prev_sql_query=&sql_file=&sql_query=';
				$url_explain = $url_query .'explain '. urlencode($this->_DebugString);
				$url_query = $url_query . urlencode($this->_DebugString);

				$this->DebugDisplayString = preg_replace('/\s+/',' ',$this->_DebugString);

				// Explain Link only for select Queries.
				if ( stristr($this->_DebugString,'select') )
					$this->DebugDisplayString .= $basehtml. $url_explain ."\">$txtExplain</a>";

				// Query Link
				$this->DebugDisplayString .= $basehtml. $url_query."\">$txtQuery</a>";
			break;
			
			// Database Related
			case 3:
				$this->DebugDisplayString = $this->_DebugString;
			break;

			// Environnment Related
			case 4:
				$this->DebugDisplayString = $this->_DebugString;
			break;

			// Current File
			case 5:
				$txtCurrentFile = 'Current File';
				$this->DebugDisplayString = "<b>&laquo; $txtCurrentFile</b>";
			break;

			// App Error
			case 6:
				$this->DebugDisplayString = DBGLINE_ERRORALERT .' '. $this->_DebugString .' '. DBGLINE_ERRORALERT;
			break;

			// Credits
			case 7:
				$this->DebugDisplayString = $this->_DebugString;
			break;

			// Search Debug
			case 8:
				// To do, reposter toues les données qu'on a de dispo
				$txtSearchInDebug = 'Search in Debug Infos';
				$this->DebugDisplayString = "<b><pre>== $txtSearchInDebug : ". '<form action="'. $_SERVER['PHP_SELF'] .'"><input name="DBG_SEARCH" value="'. (isset($_REQUEST["DBG_SEARCH"]) ? $_REQUEST["DBG_SEARCH"] : "") .'"><input type="SUBMIT" value="Go !"></form>';
			break;

			// Object Debug
			case 9:
				$obj_title = (empty($this->_LineTitle)) ? get_class($this->_DebugString) : $this->_LineTitle ;
				$this->DebugDisplayString = Debug::DumpObj($this->_DebugString,$obj_title,DUMP_ARR_STR);
			break;

			// Process Perf
			case 10;			
				$this->DebugDisplayString = $this->_DebugString;
			break;

			// Temlates
			case 11;
				$this->DebugDisplayString = $this->_DebugString;
			break;

			// Main Page Action
			case 12;
				$txtPageAction = 'Page Action';
				$this->DebugDisplayString = " [ $txtPageAction : ". $this->_DebugString .' ]';
			break;

			// Array Debug
			case 13:
				$this->DebugDisplayString = Debug::DumpArr($this->_DebugString,$this->_LineTitle,DUMP_ARR_STR);
			break;
		}
	}

	/**
	* _BuildHtmlPreCell() : Build HTML pre cell with backgroud attributes
	* 
	* @since 11 Dec 2003
	* @see DefaultCellBackColor, HtmlPreCell
	* @access private
	*/ 
	function _BuildHtmlPreCell()
	{	
		$this->HtmlPreCell = '<tr bgcolor="'. $this->DefaultCellBackColor .'"><td><pre>';
	}		
	
   	/**
	 * getPhpMyAdminUrl() : Return url of PhpmyAdmin
	 *
	 * @return string PhpMyAdminUrl
	 * 
	 * @see PhpMyAdminUrl
	 * @since 25 Oct 2003
	 * @access public
	 */
	function getPhpMyAdminUrl()
	{			
		return $this->PhpMyAdminUrl;
	}
	
   	/**
	 * setPhpMyAdminUrl() : Set the url of PhpmyAdmin
	 *
	 * @param string URL OF phpmyadmin
	 * 
	 * @see PhpMyAdminUrl
	 * @since 14 Nov 2003
	 * @access public
	 */
	function setPhpMyAdminUrl($phpmyadmin_url)
	{			
		$this->PhpMyAdminUrl = $phpmyadmin_url;
	}

   	/**
	 * getDebugLineString() : Return Formated debug infos
	 *
 	 * @return string The formatted string
	 * 
	 * @since 25 Oct 2003
	 * @access public
	 */
	function getDebugLineString()
	{
		return 	$this->HtmlPreCell. 
				$this->_Location. 
				$this->CellColor.
				$this->CellBoldStatus.
				$this->DebugDisplayString.
				$this->HtmlPostCell;				
	}
	
   	/**
	 * _BuildDebugLineLocation() :  Retrieve Localisation of debug info
	 *
	 * Check is $file and $line, build the location with available
	 * datas, if nothing return a default Info message.
     *
 	 * @param string $file 	File of debug info
	 * @param string $line 	Line number of debug info
	 * 
 	 * @return string The formatted location [file,line]
	 * 
	 * @since 25 Oct 2003
	 * @access private
	 */
	function _BuildDebugLineLocation($file,$line)
	{
		// Lang
		$txtNoLocation = 'NO LOC';

		$l_dbgloc = STR_N;
		
		if ( !empty($file) )
			$l_dbgloc .= basename($file);
		
		if ( !empty($line) ) 
		{
			if ( !empty($l_dbgloc) )
				$l_dbgloc .= ',';
				
			$l_dbgloc .= $line;
		}

		if ( !empty($l_dbgloc) )
			$l_dbgloc = '[' . $l_dbgloc . ']';
		else
		{
			if ($this->DebugType != DBGLINE_CREDITS && 
				$this->DebugType != DBGLINE_SEARCH && 
				$this->DebugType != DBGLINE_ENV && 
				$this->DebugType != DBGLINE_PROCESSPERF &&
				$this->DebugType != DBGLINE_TEMPLATES )
				$l_dbgloc = "[-$txtNoLocation-]";
		}				
		return $l_dbgloc;
	}
}
?>