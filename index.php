<?php
ob_start();
require "info.php";
require "info2html.php";
require "config.php";
require "utils.php";

// Multiple Server Stuff
if (isset($_REQUEST['server']))
{
	$server = $_REQUEST['server'];
}
else
{
	$server = 0;
}

if (sizeof($servers) > 1 && strcmp($config["server_in_title"],"yes")==0)
{
	if ($servers[$server][2] != '')
	{
		$config["title"] .= " (" . $servers[$server][2] . ")";
	}
	else
	{
		$config["title"] .= " (" . $servers[$server][0] . ")";
	}
}

$host = $servers[$server][0];
$port = $servers[$server][1];

// This variable is a argument to $_COOKIE[*] to make it where your cookies
// won't go any old place, but only to the host/port that you are speaking to
$hostport = $host . ":" . $port;

// Playlist Hiding stuff
if (strcmp($config["use_cookies"],"yes")==0 && isset($_COOKIE["phpMp_playlist_hide"][$hostport]))
{
	$hide = $_COOKIE["phpMp_playlist_hide"][$hostport];
}

// This will extract the needed GET/POST variables
extract(setupReceivedVars(array("add_all", "arg", "body", "command", "dir", "find", "hide", "logout", "password", "search", "server", "stream"),12));

if (isset($hide) && strcmp($config["use_cookies"],"yes")==0)
{
	setcookie("phpMp_playlist_hide[$hostport]", $hide);
}
else
{
	$hide = 1;
}

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
header("Content-Type: text/html; charset=UTF-8");

echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">";
echo "<html><head>";
echo "<META HTTP-EQUIV=\"Expires\" CONTENT=\"Thu, 01 Dec 1994 16:00:00 GMT\">";
echo "<META HTTP-EQUIV=\"Pragma\" CONTENT=\"no-cache\">";
echo "<META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset=UTF-8\">";

// Open the connection
$fp = fsockopen($host,$port,$errno,$errstr,10);

// If there's no connection, and servers exist goto the server menu
if (!$fp)
{
	if (isset($servers) && (sizeof($servers) > 1))
	{
		include "features.php";
		server($servers, $host, $colors);
	}
	echo "$errstr ($errno)<br>\n";
}

// Lets go ahead and get the MPD version while we can
$MPDversion = initialConnect($fp);
doCommand($fp, $arg, $command, $config["overwrite_playlists"]);

// Password stuff
if (isset($logout))
{
	setcookie("phpMp_password[$hostport]","");
	$has_password = 0;
}
else if (isset($_COOKIE["phpMp_password"]))
{
	$password = $_COOKIE["phpMp_password"][$hostport];
	$has_password = 1;
}
if (isset($password))
{
	fputs($fp,"password \"$password\"\n");
	while (!feof($fp))
	{
		$got =  fgets($fp,1024);
		if (strncmp("OK",$got,strlen("OK"))==0)
		{
			break;
		}
		if (strncmp("ACK",$got,strlen("ACK"))==0)
		{
			break;
		}
	}
	unset($password);
}

// This needs to go down here to give the cookies, server time to load
include "theme.php";

unset($hostport);
$commands = getCommandInfo($fp);
if(! ($commands["listall"] || $commands["lsinfo"] || $commands["playlist"] || $commands["playlistinfo"] || $commands["stats"]))
{
	include "features.php";
	echo "<b>Error:</b> Can't load phpMp due to not having permission to the following commands: ";
	if(! $commands["listall"])
	{
		echo "listall ";
	}
	if(! $commands["lsinfo"])
	{
		echo "lsinfo ";
	}
	if(! $commands["playlist"])
	{
		echo "playlist ";
	}
	if(! $commands["playlistinfo"])
	{
		echo "playlistinfo";
	}
	login($fp, $config, $colors["login"], $server, $arg, $dir, $remember);
	server($servers, $host, $colors["server"], $config);
#	exit;
}
// This will serve as our front page if called w/o $body
else if(! isset($body))
{
	echo "<title>" . $config["title"] ."</title>";
	echo "</head>";

	echo "<frameset " . $config["frames_layout"] . ">";
	echo "<frame name=\"main\" src=\"index.php?body=main&amp;server=$server\" frameborder= " . $config["frame_border_size"] . ">";
	echo "<frame name=\"playlist\" src=\"index.php?body=playlist&amp;server=$server\" frameborder=0>";
	echo "<noframes>NO FRAMES ... try phpMp+</noframes>";
	echo "</frameset>";
}
else
{
	if(strcmp($body,"playlist")==0)
	{
		echo "<META HTTP-EQUIV=\"REFRESH\" CONTENT=\"" . $config["refresh_freq"] . ";URL=index.php?body=playlist&amp;server=" . $server . "\">";
	}
	echo "<title>" . $config["title"] . " - " . $body . "</title>";

	// I would _much_ rather have a php generated stylesheet
	echo "<style type=\"text/css\">";
	echo "* { font-family: " . $fonts["all"] . "; }";
	echo "A:link, A:visited, A:active { text-decoration: none; border-style: none none none none; }";
	echo "a.green:link, a.green:active, a.green:visited, a.green:hover {background: " . $colors["playing"]["on"] . "}";
	echo "table { width: 100%; border-style: none }";
	echo "form { padding: 0; margin: 0 }";
	echo "</style>";
	echo "</head>";

	echo "<body link=" . $colors["links"]["link"] . " ";
	echo "vlink=" . $colors["links"]["visual"] . " "; 
	echo "alink=" . $colors["links"]["active"] . " ";
	echo "bgcolor=" . $colors["background"] . ">";

	echo "<!-- The Header (index.php) Ends Here, Body begins here -->";

	include $body . ".php";

	echo "</body>";
}
echo "</html>";
fclose($fp);
ob_end_flush();
?>