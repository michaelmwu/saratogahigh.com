<?
// Connecting, selecting database
$link = mysql_connect("localhost:3306", "root", "h4dok3n9")
   or die("Could not connect : " . mysql_error());

mysql_select_db("SCOUT_LIST") or die("Could not select database");

$scouts = mysql_query("SELECT * FROM SCOUTS");

while($scout = mysql_fetch_array($scouts, MYSQL_ASSOC))
{
	$name = explode(' ',$scout['SCOUT_NAME']);
	mysql_query("UPDATE SCOUTS SET SCOUT_FIRST='$name[0]', SCOUT_LAST='$name[1]' WHERE SCOUT_ID='" . $scout['SCOUT_ID'] . "'");
}
?>