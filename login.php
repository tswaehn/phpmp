<?php
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
