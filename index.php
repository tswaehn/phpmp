<?php
ob_start();
require "info.php";
require "info2html.php";
require "config.php";
require "utils.php";

// Multiple Server Stuff
if( isset( $_REQUEST['server'] ))
{
	$server = $_REQUEST['server'];
}
else
{
	$server = 0;
}

if ( sizeof( $servers ) > 1 && strcmp( $config["server_in_title"],"yes" ) == "0" )
{
	if ( $servers[$server][2] != '' )
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
if ( strcmp($config["use_cookies"], "yes" ) == "0" && isset( $_COOKIE["phpMp_playlist_hide"][$hostport] ))
{
	$hide = $_COOKIE["phpMp_playlist_hide"][$hostport];
}

// This will extract the needed GET/POST variables
extract( setupReceivedVars( array( "arg", "arg2", "body", "command", "dir", "feature", "passarg", "server", "sort", "stream" ), "10" ));

if( isset( $body ))
{
	if( strcmp( $body, "main" ) == "0" )
	{
		if( isset( $feature ))
		{
			if( strcmp( $feature, "search" ) == "0" )
			{
				extract( setupReceivedVars( array( "find", "search" ), "2" ));
			}
		}
		extract( setupReceivedVars( array( "delete", "save", "server", "ordered" ), "4" ));
	}
	else if( strcmp( $body, "playlist" ) == "0" )
	{
		extract( setupReceivedVars( array( "add_all", "hide", "show_options" ), "3" ));
	}
}
else
{
	extract( setupReceivedVars( array( "logout" ), "1" ));
}

// This will prevent us from getting E_NOTICE warnings
if( ! isset( $passarg ))
{
	$passarg = "";
}
if( ! isset( $server ))
{
	$server = 0;
}
if( ! isset( $dir ))
{
	$dir = "";
}
if( ! isset( $delete ))
{
	$delete = "no";
}
if( ! isset( $arg ))
{
	$arg = "";
}
if( ! isset( $arg2 ))
{
	$arg2 = "";
}
if( ! isset( $remember ))
{
	$remember = "";
}
if( ! isset( $ordered ))
{
	$ordered = "";
}
if( ! isset( $show_options ))
{
	$show_options = "0";
}

if ( isset( $hide ) && strcmp( $config["use_cookies"], "yes" ) == "0" )
{
	setcookie("phpMp_playlist_hide[$hostport]", $hide);
}
else
{
	$hide = 1;
}

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Content-Type: text/html; charset=UTF-8");

echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">";
echo "<html><head>";
#echo "<META HTTP-EQUIV=\"Expires\" CONTENT=\"Thu, 01 Dec 1994 16:00:00 GMT\">";
#echo "<META HTTP-EQUIV=\"Pragma\" CONTENT=\"no-cache\">";
#echo "<META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset=UTF-8\">";

// Open thee connection
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

// Password stuff
if( isset( $logout ))
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
				setcookie( "phpMp_password[$hostport]", $passarg, time()+60*60*24*365 );
			}
			else
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

$commands = getCommandInfo( $fp );
if( $commands["status"] == "1" )
{
	$status = getStatusInfo( $fp );
}
if( isset( $command ))
{
	doCommand( $fp, $arg, $arg2, $command, $config["overwrite_playlists"], $status );
	$status = getStatusInfo( $fp ); 
}

if( isset( $feature ))
{
	if( strcmp( $feature, "stream-icy" ) == "0" || strcmp( $feature, "stream-shout" ) == "0" )
	{
		if( strcmp( $feature, "stream-icy" ) == "0" )
		{
			$fh = fopen( "http://dir.xiph.org/yp.xml", "r" );
			$fh2 = fopen( "http://oddsock.org/yp.xml", "r" );

			if( ! is_resource( $fh ) && ! is_resource( $fh2 ))
			{
				die ("<H3><b>If you want phpMp to download your stream, you have to change 'allow_url_open' to On in your php.ini</b></H3>");
			}
		}
		else if( strcmp( $feature, "stream-shout" ) == "0" )
		{
			$fh = gzopen( "http://shapeshifter:8080/~sbh/shoutcast.xml.gz", "r" );

			if( ! is_resource( $fh ))
			{
				die ("<H3><b>If you want phpMp to download your stream, you have to change 'allow_url_open' to On in your php.ini</b></H3>");
			}
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

		if( isset( $fh2 ))
		{
			while( $data = fread( $fh2, "4096" ))
			{
				if( ! xml_parse( $xml_parser, $data, feof( $fh2 )))
				{
					break; // get out of while loop if we're done with the file
				}
			}
		}

		xml_parser_free( $xml_parser );
	}
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
else if( ! isset( $body ) && ! isset( $feature ))
{
	unset( $hostport );
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
	unset( $hostport );
	if( strcmp( $body, "playlist" ) == "0" )
	{
		echo "<META HTTP-EQUIV=\"REFRESH\" CONTENT=\"" . $config["refresh_freq"] . ";URL=index.php?body=playlist&amp;hide=$hide&amp;show_options=$show_options&amp;server=" . $server . "\">";
	}
	if( isset( $status["updating_db"] ) && strcmp( $body, "main" ) == "0" )
	{
		if( ! isset( $sort ))
		{
			$sort = $config["default_sort"];
		}
		echo "<META HTTP-EQUIV=\"REFRESH\" CONTENT=\"" . $config["refresh_freq"] . ";URL=index.php?body=main&amp;sort=$sort&amp;dir=$dir&amp;ordered=$ordered&amp;server=" . $server . "\">";
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
fclose( $fp );
ob_end_flush();
?>