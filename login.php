<?php
ob_start();
global $has_password;
$has_password = 0;
if(isset($_COOKIE["phpMp_password"])) {
	$password = $_COOKIE["phpMp_password"];
	$has_password = 1;
}
include "config.php";
include "theme.php";
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
header("Content-Type: text/html; charset=UTF-8");
?>
<html>
<head>
<META HTTP-EQUIV="Expires" CONTENT="Thu, 01 Dec 1994 16:00:00 GMT">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<?php
// php won't interpret inside of the style block
print "<style type=\"text/css\">\n";
print "* {\n";
print "  font-family: " . $fonts["all"] . ";\n";
print "}\n";
print "</style>\n";
?>
</head>
<body 	link="<?php print $colors["links"]["link"]; ?>" 
	vlink="<?php print $colors["links"]["visual"]; ?>" 
	alink="<?php print $colors["links"]["active"]; ?>" 
	bgcolor="<?php print $colors["background"]; ?>">
<?php
include "login_body.php";
?>
</body>
</html>
