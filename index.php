<?php
include "config.php";
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");

if (!isset($_REQUEST['server']))
{
	$server = 0;
}
else
{
	$server = $_REQUEST['server'];
}
if (sizeof($servers) > 1)
{
	if ($servers[$server][2] != '')
	{
		$title .= " (" . $servers[$server][2] . ")";
	}
	else
	{
		$title .= " (" . $servers[$server][0] . ")";
	}
}
$host = $servers[$server][0];
$port = $servers[$server][1];

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">
<html>
<head>
<title>' . $title . '</title>
</head>
';

if (0==strcmp($frames,"yes"))
{
	print "<frameset $frames_layout>\n";
	print "<frame name=\"main\" src=\"main.php?server=$server\">\n";
	print "<frame name=\"playlist\" src=\"playlist.php?server=$server\">\n";
	print "<noframes>NO FRAMES :-(</noframes>\n";
	print "</frameset>\n";
}
else
{
	print "<body bgcolor=\"" . $colors["background"] . "\">\n";
	print "<table border=0 cellspacing=0 width=\"100%\">\n";
	print "<tr valign=top><td>\n";
	include "main_body.php";
	print "</td>\n";
	print "<td width=250>\n";
	include "playlist_body.php";
	print "</td></tr>\n";
	print "</table>";
	print "</body>\n";
}
?>
</html>
