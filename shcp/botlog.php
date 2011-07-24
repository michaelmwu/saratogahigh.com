<? include '../db.php';

$logpath = "../../cgi-bin/Aimbot/logs";
$specpath = "../../cgi-bin/Aimbot/logs/special";
$screenpath = "../../cgi-bin/Aimbot/botlog.txt";
$firstpath = "../../cgi-bin/Aimbot/first.txt";
$noregpath = "../../cgi-bin/Aimbot/noreg.txt";

$snerror = "";
$errorm= "";

if( isset( $_GET['sn'] ) )
{
	$sn = $_GET['sn'];

	if( preg_match( "/[^0-9A-Za-z]/", $delsn ) ) #catch those hackers!
	{
		$snerror .= "Sorry, the log has invalid characters.<br>";
	}

	if( ! is_file("$logpath/$sn.txt") )
	{
		$snerror .= "Sorry, that log does not exist.<br>";
	}
}

if( ! is_file($screenpath) )
	$errorm .= "Bot screen log does not exist.";
else if( ! is_file("$firstpath") )
	$errorm .= "First Time log does not exist.";
else if( ! is_file("$noregpath") )
	$errorm .= "Non-Registered Users log does not exist.";
else if( ! is_file("$specpath/comments.txt") )
	$errorm .= "Comments log does not exist.";
else if( ! is_file("$specpath/errors.txt") )
	$errorm .= "Errors log does not exist.";

if( $_POST['action'] == 'delallconv' )
{
	$dir = opendir($logpath);
	while( $file = readdir($dir) )
		if( is_file("$logpath/$file") )
			unlink("$logpath/$file");
	closedir($dir);
	header("Location: botlog.php");
}
else if( $_POST['action'] == 'resetfirst' )
{
	$file = fopen("$firstpath","w");
	fclose($file);
	header("Location: botlog.php?first");
}
else if( $_POST['action'] == 'resetnoreg' )
{
	$file = fopen("$noregpath","w");
	fclose($file);
	header("Location: botlog.php?noreg");
}
else if( $_POST['action'] == 'resetcomm' )
{
	$file = fopen("$specpath/comments.txt","w");
	fclose($file);
	header("Location: botlog.php?comm");
}
else if( $_POST['action'] == 'resetscreen' )
{
	$file = fopen("$screenpath","w");
	fclose($file);
	header("Location: botlog.php?screen");
}
else if( $_POST['action'] == 'reseterr' )
{
	$file = fopen("$specpath/errors.txt","w");
	fclose($file);
	header("Location: botlog.php?err");
}
else if( $_POST['action'] == 'delsn' )
{
	$delsn = $_POST['sn'];
	if( preg_match( "/[^0-9A-Za-z]/", $delsn ) ) #catch those hackers!
		$snerror .= "Sorry, the log to delete has invalid characters.<br>";
	if( ! is_file("$logpath/$delsn.txt") )
		$snerror .= "Sorry, that log does not exist.<br>";
	if( $snerror == "" )
		unlink("$logpath/$delsn.txt");
	header("Location: botlog.php");
}

require 'cpvalidation.php';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
        <head>
                <title>Aimbot Log</title>
                <link rel="stylesheet" type="text/css" href="../shs.css">
                <meta name="vs_targetSchema" content="http://schemas.microsoft.com/intellisense/ie5">
                <meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
                <link rel="stylesheet" type="text/css" href="admin.css">
                <style type="text/css"><!--
                        span.home { display: inline; }
				h2 {font-family: Sans-Serif;};
				a {font-family: Sans-Serif;}
				.sans {font-family: Sans-Serif;}
				td {vertical-align: top;}
				td.logbox {font-family: Lucida Console; margin: 3px; padding: 2px; border: 1px #c0c0c0 solid; word-wrap: break-word;}
				<?
if( isset($_GET['noreg']) )
	print 'a.lnkc {font-weight: bold;}';
else if( isset($_GET['sn']) ) {}
else if( isset($_GET['first']) )
	print 'a.lnkb {font-weight: bold;}';
else if( isset($_GET['screen']) )
	print 'a.lnkd {font-weight: bold;}';
else if( isset($_GET['comments']) )
	print 'a.lnke {font-weight: bold;}';
else if( isset($_GET['errors']) )
	print 'a.lnkf {font-weight: bold;}';
else
	print 'a.lnka {font-weight: bold;}';
?>
                --></style>
        </head>
        <body>

<? include "inc-header.php" ?>
<div class="navbar">
<span style="font-weight: bold">AIM Bot Logs:</span> <a class="lnka" href='botlog.php'>Main</a> | <a class="lnkb" href='botlog.php?first'>Non-First Time Users</a> | <a class="lnkc" href='botlog.php?noreg'>Non-Registered Users</a> | <a class="lnkd" href='botlog.php?screen'>Screen</a> | <a class="lnke" href='botlog.php?comments'>Comments</a> | <a class="lnkf" href='botlog.php?err'>Errors</a>
</div>
<?
print '<h2 style="border-bottom: 1px solid black; margin: 0px;">';
if( isset($_GET['sn']) )
	print 'Log for: ' . $_GET["sn"];
else if( isset($_GET['noreg']) )
	print 'Non-Registered Users Log';
else if( isset($_GET['first']) )
	print "First Time Log";
else if( isset($_GET['screen']) )
	print "Bot Screen Log";
else if( isset($_GET['comments']) )
	print "Bot Comments";
else if( isset($_GET['errors']) )
	print "Bot Errors";
else
	print "AIM Bot Logs";
?>
</h2>
<table style="padding-top: 24px; width: 80%;">
<tr><td>
<?
if( strlen($snerror) > 0 )
	print "$snerror</td></tr><tr><td>";
if( strlen($errorm) > 0 )
	print "$errorm</td></tr><tr><td>";
?>
<form action="botlog.php" method="POST">
<?
if( isset($_GET['sn']) && $logerror == "")
{
	print '<input type="hidden" name="sn" value="' . $_GET['sn'] . '">';
	print 'Delete this Log: <input type="hidden" name="action" value="delsn"><input type="submit" value="Go">';
}
else if(isset($_GET['first']))
	print 'Reset this List: <input type="hidden" name="action" value="resetfirst"><input type="submit" value="Go">';
else if(isset($_GET['noreg']))
	print 'Reset this List: <input type="hidden" name="action" value="resetnoreg"><input type="submit" value="Go">';
else if(isset($_GET['screen']))
	print 'Reset this Log: <input type="hidden" name="action" value="resetscreen"><input type="submit" value="Go">';
else if(isset($_GET['comments']))
	print 'Reset this Log: <input type="hidden" name="action" value="resetcomm"><input type="submit" value="Go">';
else if(isset($_GET['err']))
	print 'Reset this Log: <input type="hidden" name="action" value="reseterr"><input type="submit" value="Go">';
else
	print 'Reset all Conversation Logs: <input type="hidden" name="action" value="delallconv"><input type="submit" value="Go">';
?>

</form></td></tr>

<tr><td style="padding-bottom: 12px;"><a href='#logbottom'>Bottom</a></td></tr>

<tr><td class="logbox"><a name='logtop'>

<?
if( isset( $_GET["sn"] ) )
{
	if( $snerror == "" )
	{
		$file = fopen("$logpath/$sn.txt","r");
		while( ! feof( $file ) )
		{
			$line = fgets( $file, 1600 );
			print preg_replace("/ /", "&nbsp;", htmlentities("$line")) . "<br>";
		}
		fclose($file);
	}
}

else if( isset( $_GET["first"] ) )
{
	$file = fopen($firstpath,"r");
	while( ! feof( $file ) )
	{
		$line = fgets( $file, 1600 );
		print preg_replace("/ /", "&nbsp;", htmlentities("$line")) . "<br>";
	}
	fclose($file);
}
else if( isset( $_GET["noreg"] ) )
{
	$file = fopen($noregpath,"r");
	while( ! feof( $file ) )
	{
		$line = fgets( $file, 1600 );
		print preg_replace("/ /", "&nbsp;", htmlentities("$line")) . "<br>";
	}
	fclose($file);
}

else if( isset( $_GET["screen"] ) )
{
	$file = fopen($screenpath,"r");
	while( ! feof( $file ) )
	{
		$line = fgets( $file, 1600 );
		print preg_replace("/ /", "&nbsp;", htmlentities("$line")) . "<br>";
	}
	fclose($file);
}

else if( isset( $_GET["comments"] ) )
{
	$file = fopen("$specpath/comments.txt","r");
	while( ! feof( $file ) )
	{
		$line = fgets( $file, 1600 );
		print preg_replace("/ /", "&nbsp;", htmlentities("$line")) . "<br>";
	}
}
else if( isset( $_GET["err"] ) )
{
	$file = fopen("$specpath/errors.txt","r");
	while( ! feof( $file ) )
	{
		$line = fgets( $file, 1600 );
		print preg_replace("/ /", "&nbsp;", htmlentities("$line")) . "<br>";
	}
	fclose( $file );
}
else
{
	$dir = opendir($logpath);
	while( $file = readdir($dir) )
	{
		if( is_file("$logpath/$file") )
		{
			$filetrim = preg_replace( "/\.txt/", "", $file );
			print "<a href='botlog.php?sn=$filetrim'>$filetrim</a><br>\n";
		}
	}
	closedir($dir);
}
?>
<a name="logbottom"></td></tr>
<tr><td style="padding-top: 12px;"><a href="#logtop">Top</a></td></tr></table>
<?
    include "../inc-footer.php";
?>
</body>
</html>
