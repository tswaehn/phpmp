<?php
// This will extract the needed GET/POST variables
$dir_url = stripslashes( $dir );
$dir_url = rawurlencode( $dir );

if( ! isset( $sort ))
{
	$sort = $config["default_sort"];
}

$sort_array = split( ",", $sort );
$lsinfo = getLsInfo( $fp, "lsinfo \"$dir\"\n" );

list( $dprint, $dindex, $dcount ) = lsinfo2directoryTable( $lsinfo, $server, $sort, $dir, $commands["add"], $colors["directories"]["body"] );
list( $pprint, $pindex ) = lsinfo2playlistTable( $lsinfo, $sort, $delete, $server, $commands["load"] );
list( $mprint, $mindex, $add_all ) = lsinfo2musicTable( $lsinfo, $sort, $dir, $sort_array, $config, $colors["music"]["body"], $server, $commands["add"] );

/* This is the features section, just throw a new feature in features.php, make a link in
utils and edit below and you have a new feature */

if( isset( $feature ))
{
	require "features.php";
	displayDirectory( $dir, $dir_url, $sort, "Back to Directory", 0, 0, $has_password, $dcount, $commands, $colors["directories"], $server, $servers, $fp, $passarg, $ordered );

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
	displayDirectory( $dir, $dir_url, $sort, "Current Directory", count( $mprint ), count( $pprint ), $has_password, $dcount, $commands, $colors["directories"], $server, $servers, $fp, $passarg, $ordered );

	// The next few are targeted from URLs
	if( isset( $save ) && strcmp( $save, "yes" ) == "0" )
	{
		printSavePlaylistTable( $save, $server, $colors["playlist"] );
	}
	printDirectoryTable( $dcount, $dprint, $dindex, $dir, $sort, $server, $commands["add"], $colors["directories"] );
	printMusicTable( $config, $colors["music"], $sort_array, $server, $mprint, "index.php?body=main&amp;dir=$dir_url", $add_all, $mindex, $dir, $commands["add"], $feature, $ordered);
	printPlaylistTable( $colors["playlist"], $server, $pprint, $pindex, $delete, $commands["rm"] );
}
?>
