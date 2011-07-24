<?
include 'inc/config.php';

phpinfo();

$stimer = explode( ' ', microtime() );
$stimer = $stimer[1] + $stimer[0];
//  -----------

/* ------------------------------------- */
//  Add your PHP script and/or content here
/* ------------------------------------- */

	$receive = array();

	$emails = db::get_prefix_result("USER",'USER_GR >= "' . C_SCHOOLYEAR . '" AND USER_GR < "' . (C_SCHOOLYEAR + 4) . '" AND LENGTH(USER_EMAIL) > 0');
	while($row = db::fetch_row($emails))
		$receive[] = $row['USER_FULLNAME'] . " <" . $row['USER_EMAIL'] . ">";
	mysql_free_result($emails);
	
	$receive = array_unique($receive);
	
	print_r($receive);

//  End TIMER
//  ---------
$etimer = explode( ' ', microtime() );
$etimer = $etimer[1] + $etimer[0];
echo '<p style="margin:auto; text-align:center">';
printf( "Script timer: <b>%f</b> seconds.", ($etimer-$stimer) );
echo '</p>';
?>