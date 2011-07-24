<?
// Connecting, selecting database
if($_SERVER['HTTP_HOST'] != 'csf.shsclubs.org')
{
	$link = mysql_connect("localhost:3306", "digitalitcc", "ehp0d1ng")
	   or die("Could not connect : " . mysql_error());

	mysql_select_db("digitalitcc_db") or die("Could not select database");
}
else
{
	$link = mysql_connect("localhost:3306", "csf", "@T3aDaz3")
	   or die("Could not connect : " . mysql_error());

	mysql_select_db("csf_db") or die("Could not select database");
}

class db {
	function get_row($table,$id) {
		include 'globals.php';
		mysql_query("SELECT * FROM " . $table . "_LIST WHERE " . $table . "_ID=" . $id) or $page->error(mysql_error());
	}
	
	function desc() {
		return "db";
	}
}

$db = new db();
?>