<?php
// This will extract the needed GET/POST variables
$dir = stripslashes( $dir );
$dir = rawurldecode( $dir );
$dir_url = rawurlencode( $dir );
$sort = isset( $_REQUEST["sort"] ) ? $_REQUEST["sort"] : $config["default_sort"];
$sort_array = split( ",", $sort );
$url = "index.php?body=main&amp;dir=$dir_url";

if( empty( $feature ))
{
	$lsinfo = getLsInfo( $fp, "lsinfo \"$dir\"\n", $config["display_fields"] );
	$dinfo = lsinfo2directoryTable( $lsinfo["dir"], $server, $sort, $commands["add"], $colors["directories"]["body"] );
	$pinfo = lsinfo2playlistTable( $lsinfo["playlist"], $sort, $delete, $server, $commands["load"] );

	if( ! empty( $lsinfo["music"] ))
	{
		$add_all = createAddAll( $lsinfo["music"], $config["song_separator"] );
		list( $tagged, $untagged, $config["display_fields"] ) = splitTagFile( $lsinfo["music"], $config );
		$tagged_info = taginfo2musicTable( $tagged, $dir_url, $config, $colors["music"], $server, $commands["add"], $sort_array, $sort, $ordered, $url );
		$file_info = fileinfo2musicTable( $untagged, $dir_url, $config, $colors["music"], $server, $commands["add"], $sort_array, $sort, $url );
		unset( $tagged, $untagged );
		displayDirectory( $dir, $dir_url, $sort, "Current Directory", $file_info["count"], $tagged_info["count"], $pinfo["count"], $dinfo["count"],
			$has_password, $commands, $colors["directories"], $server, $servers, $fp, $passarg, $ordered );
	}
	else
	{
		displayDirectory( $dir, $dir_url, $sort, "Current Directory", 0, 0, $pinfo["count"], $dinfo["count"],
			$has_password, $commands, $colors["directories"], $server, $servers, $fp, $passarg, $ordered );
	}

	if( strcmp( $save, "yes" ) == "0" )
	{
		printSavePlaylistTable( $server, $colors["playlist"] );
	}

	printDirectoryTable( $dinfo, $dir, $sort, $server, $commands["add"], $colors["directories"] );

	if( ! empty( $lsinfo["music"] ))
	{
		printMusicTable( $add_all, $config, $colors["music"]["meta"], $tagged_info, $file_info["count"], $sort_array, $server, $dir, $commands["add"], $feature, $ordered );
		printMusicTable( $add_all, $config, $colors["music"]["file"], $file_info, $tagged_info["count"], $sort_array, $server, $dir, $commands["add"], $feature, 0 );
	}

	printPlaylistTable( $colors["playlist"], $server, $pinfo, $delete, $commands["rm"] );
}
else
{
	require "features.php";

	$tagged = array();
	$untagged = array();
	$lsinfo["music"] = array();

	if( strcmp( $feature, "search" ) == "0" && strlen( $arg ) > "0" )
	{
		if( ! empty( $search ))
		{
			$lsinfo = getLsInfo( $fp, "search $search \"$arg\"\n", $config["display_fields"] );
		}
		else if( ! empty( $find ))
		{
			$lsinfo = getLsInfo( $fp, "find $find \"$arg\"\n", $config["display_fields"] );
		}
		list( $tagged, $untagged, $config["display_fields"] ) = splitTagFile( $lsinfo["music"], $config );
	}

	displayDirectory( $dir, $dir_url, $sort, "Current Directory", count( $untagged ), count( $tagged ), 0, 0,
		$has_password, $commands, $colors["directories"], $server, $servers, $fp, $passarg, $ordered );

	echo "<!-- Begin $feature -->";
	switch( $feature )
	{
		case 'login':
			login( $fp, $config["default_sort"], $colors["login"], $server, $arg, $dir, $remember );
			break;
		case 'outputs':
			outputs( $fp, $host, $colors["outputs"], $server, $commands );
			break;
		case 'search':
			search( $fp, $colors["search"], $config, $dir, $search, $find, $arg, $sort, $server, $commands["add"], $feature, $ordered, $tagged, $untagged, $lsinfo["music"] );
			break;
		case 'server':
			server( $servers, $host, $port, $colors["server"], $config );
			break;
		case 'stats':
			stats( $fp, $colors["stats"], $MPDversion, $phpMpVersion, $host, $port );
			break;
		case 'stream':
			stream( $server, $colors["stream"], $feature, 0, $config["song_separator"] );
			break;
		case 'stream-icy':
			stream( $server, $colors["stream"], $feature, $server_data, $config["song_separator"] );
			break;
		case 'stream-shout':
			stream( $server, $colors["stream"], $feature, $server_data, $config["song_separator"] );
			break;
	}
	echo "<!-- End $feature -->";
}
?>
