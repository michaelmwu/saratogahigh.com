<?
	include 'db.php';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Untitled Document</title>
</head>

<body>

<form action="form-test.php" method="POST">
<?
print $_POST['day'];

$day = new select;
$day->name = "day";
for($i = 1;$i < 32;$i++)
	$day->add_option($i,$i,false);

$day->element_print();
?>
<input type="submit" value="Go">
</form>

</body>
</html>
