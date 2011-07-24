<?
include '../db.php';
require 'cpvalidation.php';

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
	<title>Highlight PHP</title>
	<link rel="stylesheet" type="text/css" href="../shs.css">
	<meta name="vs_targetSchema" content="http://schemas.microsoft.com/intellisense/ie5">
	<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
</head>
<body>
<? include 'inc-header.php' ?>
<form action="highlight.php" method="GET">Enter a file: <input type="text" name="file"><input type="submit" name="view" value="Go"></form>
<?
if(isset($_GET['file']))
{
	if(preg_match("#^/.+#",$_GET['file']))
		$_GET['file'] = "/var/www/html" . $_GET['file'];
	if(is_file($_GET['file']))
	{
		if(preg_match("#^/var/www/html/#",realpath($_GET['file'])))
				highlight_file($_GET['file']);
		else
			print '<p>Stop trying to hack, you bum.</p>';
	}
	else
		print 'Not a file!';
}
?>
</body>
</html>