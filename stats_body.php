<?php
include "config.php";
include "utils.php";
$dir = "";
$sort = $default_sort;
EXTRACT($HTTP_GET_VARS);
$sort_array = split(",",$sort);
$fp = fsockopen($host,$port,$errno,$errstr,10);
if(!$fp) {
	echo "$errstr ($errno)<br>\n";
}
else {
	while(!feof($fp)) {
		$got =  fgets($fp,1024);
		if(strncmp("OK",$got,strlen("OK"))==0) 
			break;
		print "$got<br>";
		if(strncmp("ACK",$got,strlen("ACK"))==0) 
			break;
	}
	$dir_url = sanitizeForURL($dir);
	displayDirectory($dir,$sort,"Back to Directory",0,0);
	fputs($fp,"stats\n");
	while(!feof($fp)) {
		$got =  fgets($fp,1024);
		if(strncmp("OK",$got,strlen("OK"))==0)
			break;
		if(strncmp("ACK",$got,strlen("ACK"))==0) 
			break;
		$el = strtok($got,":");
		$got = strtok("\0");
		$stats["$el"] = preg_replace("/^ /","",$got);
	}
	fclose($fp);
	print "<br><b>Artists</b>: " . $stats["artists"] . "<br>";
	print "<b>Albums</b>: " . $stats["albums"] . "<br>";
	print "<b>Songs</b>: " . $stats["songs"] . "<br>";
	//print "<b>Songs Played</b>: " . $stats["songs_played"] . "<br>";
	$minute = 60;
	$hour = $minute*60;
	$day = 24*$hour;
	$days = (int)($stats["playtime"]/$day);
	$stats["playtime"] -= $days*$day;
	$hours = (int)($stats["playtime"]/$hour);
	$stats["playtime"] -= $hours*$hour;
	$minutes = (int)($stats["playtime"]/$minute);
	$stats["playtime"] -= $minutes*$minute;
	$seconds = $stats["playtime"];
	if($seconds<10) $seconds = "0$seconds";
	if($minutes<10) $minutes = "0$minutes";
	if($hours<10) $hours = "0$hours";
	print "<b>Play Time</b>: $days days, $hours:$minutes:$seconds<br>";
	$days = (int)($stats["uptime"]/$day);
	$stats["uptime"] -= $days*$day;
	$hours = (int)($stats["uptime"]/$hour);
	$stats["uptime"] -= $hours*$hour;
	$minutes = (int)($stats["uptime"]/$minute);
	$stats["uptime"] -= $minutes*$minute;
	$seconds = $stats["uptime"];
	if($seconds<10) $seconds = "0$seconds";
	if($minutes<10) $minutes = "0$minutes";
	if($hours<10) $hours = "0$hours";
	print "<b>Uptime</b>: $days days, $hours:$minutes:$seconds<br>";
	print "<b>DB Updated</b>: " . date("F j, Y, g:i a",$stats["db_update"]);
	print "<br>";
}
?>
