<?php
$dbhost = "[Your MySQL Host]";
$dbname = "[Your Database Name]";
$dbuser = "[Your Database User]";
$dbpass = "[Your Database Password]";

$dbc = mysql_connect($dbhost, $dbuser, $dbpass);
if (!$dbc) {
	die('Could not connect: ' . mysql_error());
}
$db = mysql_select_db($dbname, $dbc);
?>