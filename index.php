<?php
ob_start();
require "info.php";
require "info2html.php";
require "config.php";
require "utils.php";
$server = isset( $_REQUEST["server"] ) ? $_REQUEST["server"] : "0";

if( sizeof( $servers ) > 1 && strcmp( $config["server_in_title"],"yes" ) == "0" )
{
	$config["title"] .= strlen( $servers[$server][2] ) > "0" ? " ({$servers[$server][2]})" : " ({$servers[$server][0]})";
}

$host = $servers[$server][0];
$port = $servers[$server][1];

// This variable is a argument to $_COOKIE[*] to make it where your cookies
// won't go any old place, but only to the host/port that you are speaking to
$hostport = $host . ":" . $port;

// This will extract the needed GET/POST variables
$arg =		isset( $_REQUEST["arg"] )	?	$_REQUEST["arg"]	:	"";
$arg2 =		isset( $_REQUEST["arg2"] )	?	$_REQUEST["arg2"]	:	"";
$body =		isset( $_REQUEST["body"] )	?	$_REQUEST["body"]	:	"";
$command =	isset( $_REQUEST["command"] )	?	$_REQUEST["command"]	:	"";
$feature =	isset( $_REQUEST["feature"] )	?	$_REQUEST["feature"]	:	"";
$remember =	isset( $_REQUEST["remember"] )	?	$_REQUEST["remember"]	:	"";
$passarg =	isset( $_REQUEST["passarg"] )	?	$_REQUEST["passarg"]	:	"";
$stream =	isset( $_REQUEST["stream"] )	?	$_REQUEST["stream"]	:	"";

if( ! empty( $body ))
{
	if( strcmp( $body, "main" ) == "0" )
	{
		if( ! empty( $feature ))
		{
			if( strcmp( $feature, "search" ) == "0" )
			{
				$search = isset( $_REQUEST["search"] ) ? $_REQUEST["search"] : "";
				$find = isset( $_REQUEST["find"] ) ? $_REQUEST["find"] : "";
				$search_fields = $config["display_fields"];
			}
		}
		$delete = isset( $_REQUEST["delete"] ) ? $_REQUEST["delete"] : "no";
		$dir = isset( $_REQUEST["dir"] ) ? $_REQUEST["dir"] : "";
		$save = isset( $_REQUEST["save"] ) ? $_REQUEST["save"] : "";
		$ordered = isset( $_REQUEST["ordered"] ) ? $_REQUEST["ordered"] : "";
	}
	else if( strcmp( $body, "playlist" ) == "0" )
	{
		// Playlist Hiding Stuff
		if( isset( $_REQUEST["hide"] ))
		{
			$hide = $_REQUEST["hide"];
		}
		else if( strcmp($config["use_cookies"], "yes" ) == "0" && isset( $_COOKIE["phpMp_playlist_hide"][$hostport] ))
		{
			$hide = $_COOKIE["phpMp_playlist_hide"][$hostport];
		}

		if( isset( $hide ) && strcmp( $config["use_cookies"], "yes" ) == "0" )
		{
			setcookie("phpMp_playlist_hide[$hostport]", $hide);
		}
		else
		{
			$hide = 1;
		}

		$add_all = isset( $_REQUEST["add_all"] ) ? $_REQUEST["add_all"] : "";
		$show_options = isset( $_REQUEST["show_options"] ) ? $_REQUEST["show_options"] : "0";
	}
}
else
{
	$logout = isset( $_REQUEST["logout"] ) ? $_REQUEST["logout"] : "";
}

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Content-Type: text/html; charset=UTF-8");

echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">";
echo "<html><head>";

// Open thee connection
$fp = fsockopen( $host, $port, $errno, $errstr, 10 );

// If there's no connection, and servers exist goto the server menu
if(! is_resource( $fp ))
{
	if(isset($servers) && (sizeof($servers) > 1))
	{
		include "features.php";
		server( $servers, $host, $port, $colors["server"] );
	}
	echo "$errstr ($errno)<br>\n";
	die;
}

// Lets go ahead and get the MPD version while we can
$MPDversion = initialConnect($fp);
$MPDversion = trim($MPDversion);
// Password stuff
if( !empty( $logout ))
{
	setcookie( "phpMp_password[$hostport]", "" );
}
else if( isset( $_COOKIE["phpMp_password"][$hostport] ))
{
	$passarg = $_COOKIE["phpMp_password"][$hostport];
}

if( strlen( $passarg ) > "0" )
{
	$has_password = 1;
	fputs( $fp, "password \"$passarg\"\n" );
	while ( ! feof( $fp ))
	{
		$got = fgets( $fp, 1024 );
		if( strncmp( "OK", $got, strlen( "OK" )) == "0" )
		{
			if( isset( $remember ) && strcmp( $remember, "true" ) == "0" )
			{
				setcookie( "phpMp_password[$hostport]", $passarg, time()+60*60*24*30 );
			}
			else if( ! $_COOKIE["phpMp_password"][$hostport] )
			{
				setcookie( "phpMp_password[$hostport]", $passarg );
			}
			break;
		}
		if( strncmp( "ACK", $got, strlen( "ACK" )) == "0" )
		{
			echo "Password Incorrect, press back button";
			break;
		}
	}
}

if( ! isset( $has_password ))
{
	$has_password = 0;
} 

$commands = getCommandInfo( $fp, $MPDversion );
if( $commands["status"] == "1" )
{
	$status = getStatusInfo( $fp );
}

if( ! empty( $command ))
{
	doCommand( $fp, $arg, $arg2, $command, $config["overwrite_playlists"], $status );
	$status = getStatusInfo( $fp ); 
}

if( ! empty( $feature ) && ( strcmp( $feature, "stream-icy" ) == "0" || strcmp( $feature, "stream-shout" ) == "0" ))
{
	require "xml-parse.php";
	if( strcmp( $feature, "stream-icy" ) == "0" )
	{
		$fh = fopen( "http://dir.xiph.org/yp.xml", "r" );
		$fh2 = fopen( "http://oddsock.org/yp.xml", "r" );
	}
	else if( strcmp( $feature, "stream-shout" ) == "0" )
	{
		$fh = gzopen( "http://shapeshifter:8080/~sbh/shoutcast.xml.gz", "r" );
	}

	if( ! is_resource( $fh ) || ! is_resource( $fh2 ))
	{
		die ("<H3><b>If you want phpMp to download your stream, you have to change 'allow_url_open' to On in your php.ini</b></H3>");
	}

	$server_count = 0;
	$server_data = array();
	$xml_current_tag_state = '';

	if( ! ( $xml_parser = xml_parser_create() ))
	{
		die( "Couldn't create XML parser!" );
	}

	xml_set_element_handler( $xml_parser, "startElementHandler", "endElementHandler" );
	xml_set_character_data_handler( $xml_parser, "characterDataHandler" );

	while( $data = fread( $fh, "4096" ))
	{
		if( ! xml_parse( $xml_parser, $data, feof( $fh ) ))
		{
			break; // get out of while loop if we're done with the file
		}
	}

	while( is_resource( $fh2 ) && $data = fread( $fh2, "4096" ))
	{
		if( ! xml_parse( $xml_parser, $data, feof( $fh2 )))
		{
			break; // get out of while loop if we're done with the file
		}
	}
	xml_parser_free( $xml_parser );
}

// This needs to go down here to give the cookies, server time to load
include "theme.php";

// There might be more that's would prevent phpMp from loading, rather than taking time to figure it out, we'll wait for reports.
if( $commands["listall"] == "0" || $commands["lsinfo"] == "0" || $commands["playlist"] == "0" || $commands["playlistinfo"] == "0" || $commands["stats"] == "0" )
{
	include "features.php";
	setcookie( "phpMp_password[$hostport]", "" );
	unset( $has_password );

	echo "<b>Error:</b> Can't load phpMp due to not having permission to the following commands: ";
	if( $commands["listall"] == "0" )
	{
		echo "listall ";
	}
	if( $commands["lsinfo"] == "0" )
	{
		echo "lsinfo ";
	}
	if( $commands["playlist"] == "0" )
	{
		echo "playlist ";
	}
	if( $commands["playlistinfo"] == "0" )
	{
		echo "playlistinfo";
	}

	echo "<br>";

	login( $fp, $config, $colors["login"], $server, $arg, $dir, $remember );
	server( $servers, $host, $port, $colors["server"], $config, $commands );
}
// This will serve as our front page if called w/o $body
else if( empty( $body ) && empty( $feature ))
{
	unset( $hostport );
	echo "<title>{$config["title"]}</title>";
	echo "</head>";

	echo "<frameset {$config["frames_layout"]}>";
	echo "<frame name=\"main\" src=\"index.php?body=main&amp;server=$server\" frameborder= {$config["frame_border_size"]}>";
	echo "<frame name=\"playlist\" src=\"index.php?body=playlist&amp;server=$server\" frameborder=0>";
	echo "<noframes>NO FRAMES ... try phpMp+</noframes>";
	echo "</frameset>";
}
else
{
	unset( $hostport );
	if( strcmp( $body, "playlist" ) == "0" )
	{
		echo "<META HTTP-EQUIV=\"REFRESH\" CONTENT=\"{$config["refresh_freq"]};URL=index.php?body=playlist&amp;hide=$hide&amp;show_options=$show_options&amp;server=$server\">";
	}
	if( isset( $status["updating_db"] ) && strcmp( $body, "main" ) == "0" )
	{
		$sort =	isset( $_REQUEST["sort"] ) ? $_REQUEST["sort"] : $config["default_sort"];
		echo "<META HTTP-EQUIV=\"REFRESH\" CONTENT=\"{$config["refresh_freq"]};URL=index.php?body=main&amp;sort=$sort&amp;dir=$dir&amp;ordered=$ordered&amp;server=$server\">";
	}
	echo "<title>{$config["title"]} - $body</title>";

	// I would _much_ rather have a php generated stylesheet
	echo "<style type=\"text/css\">";
	echo "* { font-family: {$fonts["all"]}; }";
	echo "A:link, A:visited, A:active { text-decoration: none; border-style: none none none none; }";
	echo "a.green:link, a.green:active, a.green:visited, a.green:hover {background: {$colors["playing"]["on"]}}";
	echo "table { width: 100%; border-style: none }";
	echo "form { padding: 0; margin: 0 }";
	echo "</style>";
	echo "</head>";

	echo "<body link={$colors["links"]["link"]} ";
	echo "vlink={$colors["links"]["visual"]} "; 
	echo "alink={$colors["links"]["active"]} ";
	echo "bgcolor={$colors["background"]}>";

	echo "<!-- The Header (index.php) Ends Here, Body begins here -->";

	include $body . ".php";

	echo "</body>";
}
echo "</html>";
fclose( $fp );
ob_end_flush();
?>