<?php
// This will extract the needed GET/POST variables
$dir = stripslashes( $dir );
$dir = rawurldecode( $dir );
$dir_url = rawurlencode( $dir );
$url = "index.php?body=main&amp;dir=$dir_url";
if( ! isset( $sort ))
{
	$sort = $config["default_sort"];
}

$sort_array = split( ",", $sort );
list( $lsinfo, $config["display_fields"]) = getLsInfo( $fp, "lsinfo \"$dir\"\n", $config["display_fields"] );

list( $dprint, $dindex, $dcount ) = lsinfo2directoryTable( $lsinfo, $server, $sort, $dir, $commands["add"], $colors["directories"]["body"] );
list( $pprint, $pindex ) = lsinfo2playlistTable( $lsinfo, $sort, $delete, $server, $commands["load"] );

if( ! empty( $lsinfo["music"] ))
{
	$add_all = createAddAll( $lsinfo["music"], $config["song_separator"] );
	list( $tagged, $untagged ) = splitTagFile( $lsinfo["music"], $config );
	$tagged_info = taginfo2musicTable( $tagged, $dir_url, $config, $colors["music"], $server, $commands["add"], $sort_array, $sort, $ordered, $url );
	$file_info = fileinfo2musicTable( $untagged, $dir_url, $config, $colors["music"], $server, $commands["add"], $sort_array, $sort, $url );
	unset( $tagged, $untagged );
}

/* This is the features section, just throw a new feature in features.php, make a link in utils and edit below and you have a new feature */
if( isset( $feature ))
{
	require "features.php";

	// This is probably an ugly (quick) solution to an easy problem, but this makes sure that if 'search' is clicked (Tagged) & (Untagged) don't show in the displayDirectory. 
	if( strcmp( $feature, "search" ) == "0" && ! empty( $arg ))
	{
		displayDirectory( $dir, $dir_url, $sort, "Back to Directory", 0, 0, $has_password, $dcount, $commands, $colors["directories"], $server, $servers, $fp, $passarg, $ordered );
	}
	else
	{
		displayDirectory( $dir, $dir_url, $sort, "Back to Directory", 0, 0, $has_password, $dcount, $commands, $colors["directories"], $server, $servers, $fp, $passarg, $ordered );
	}

	if( ! isset ( $arg ))
	{
		$arg = "";
	}
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
			// These help to avoid E_NOTICE warnings
			if( ! isset( $search ))
			{
				$search = "";
			}
			if( ! isset( $find ))
			{
				$find = "";
			}
			search( $fp, $colors["search"], $config, $dir, $search, $find, $arg, $sort, $server, $commands["add"], $feature, $ordered );
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
else
{
	$feature = "";
	displayDirectory( $dir, $dir_url, $sort, "Current Directory", 0, count( $pprint ), $has_password, $dcount, $commands, $colors["directories"], $server, $servers, $fp, $passarg, $ordered );

	// The next few are targeted from URLs
	if( isset( $save ) && strcmp( $save, "yes" ) == "0" )
	{
		printSavePlaylistTable( $save, $server, $colors["playlist"] );
	}
	printDirectoryTable( $dcount, $dprint, $dindex, $dir, $sort, $server, $commands["add"], $colors["directories"] );

	if( ! empty( $lsinfo["music"] ))
	{
		if( empty( $tagged_info["print"] ))
		{
			$file_info["title"] = "Music";
		}
		if( empty( $file_info["print"] ))
		{
			$tagged_info["title"] = "Music";
		}
		printMusicTable( $add_all, $config, $colors["music"], $tagged_info, $sort_array, $server, $dir, $commands["add"], $feature, $ordered );
		printMusicTable( $add_all, $config, $colors["music"], $file_info, $sort_array, $server, $dir, $commands["add"], $feature, 0 );
	}

	printPlaylistTable( $colors["playlist"], $server, $pprint, $pindex, $delete, $commands["rm"] );
}
?>
