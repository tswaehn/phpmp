<?php
// This will extract the needed GET/POST variables
extract(setupReceivedVars(array("delete", "save", "server", "sort"),4));

if(!isset($sort))
{
	$sort = $config["default_sort"];
}

$dir_url = rawurlencode($dir);
$lsinfo = getLsInfo($fp,"lsinfo \"$dir\"\n");
$sort_array = split(",",$sort);

list($dprint, $dindex, $dcount) = lsinfo2directoryTable($lsinfo, $server, $sort, $dir, $commands["add"], $colors["directories"]["body"]);
list($pprint, $pindex) = lsinfo2playlistTable($lsinfo, $sort, $delete, $server, $commands["load"]);
list($mprint, $mindex, $add_all) = lsinfo2musicTable($lsinfo, $sort, $dir, $sort_array, $config, $colors["music"]["body"], $server, $commands["add"]);

/* This is the features section, just throw a new feature in features.php, make a link in
utils and edit below and you have a new feature */

if(isset($feature))
{
	require "features.php";
	displayDirectory($dir, $sort,"Back to Directory", 0, 0, "no", $has_password, $dcount, $commands, $colors["directories"], $server, $servers, $fp);

	echo "<!-- Begin $feature -->";
	switch($feature)
	{
		case 'login':
			login($fp, $config["default_sort"], $colors["login"], $server, $arg, $dir, $remember);
			break;
		case 'outputs':
			outputs($fp, $host, $colors["outputs"], $server);
			break;
		case 'search':
			search($fp, $colors["search"], $config, $dir, $search, $find, $arg, $sort, $server, $commands["add"]);
			break;
		case 'server':
			server($servers, $host, $colors["server"], $config);
			break;
		case 'stats':
			stats($fp, $colors["stats"], $MPDversion, $phpMpVersion, $host, $port);
			break;
		case 'stream':
			stream($server, $colors["stream"], $feature, 0, $config["song_separator"]);
			break;
		case 'stream-icy':
			stream($server, $colors["stream"], $feature, $server_data, $config["song_separator"]);
			break;
	}
	echo "<!-- End $feature -->";
}
else
{
	displayDirectory($dir, $sort, "Current Directory", count($mprint), count($pprint), $displayServers, $has_password, $dcount, $commands, $colors["directories"], $server, $servers, $fp);

	// The next few are targeted from URLs
	printSavePlaylistTable($save, $server, $colors["playlist"]);
	printDirectoryTable($dcount, $dprint, $dindex, $dir, $sort, $server, $commands["add"], $colors["directories"]);
	printMusicTable($config, $colors["music"], $sort_array, $server, $mprint, "index.php?body=main&amp;dir=$dir_url", $add_all, $mindex, $dir, $commands["add"]);
	printPlaylistTable($colors["playlist"], $server, $pprint, $pindex, $delete, $commands["rm"]);
}
?>
