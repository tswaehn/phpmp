<?php
include "info.php";
include "config.php";
include "utils.php";
include "info2html.php";
$dir = "";
$find = "";
$arg = "";
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
	if(isset($password)) {
		fputs($fp,"password \"$password\"\n");
		while(!feof($fp)) {
			$got =  fgets($fp,1024);
			if(strncmp("OK",$got,strlen("OK"))==0)
				break;
			print "$got<br>";
			if(strncmp("ACK",$got,strlen("ACK"))==0) 
				break;
		}
	}
	$dir_url = sanitizeForURL($dir);
	displayDirectory($dir,$sort,"Back to Directory",0,0);
	$lsinfo = getLsInfo($fp,"find $find \"$arg\"\n");
	list($mprint,$mindex,$add_all) = lsinfo2musicTable($lsinfo,$sort,$dir_url);
	$arg_url = sanitizeForURL($arg);
	printMusicTable($mprint,"find.php?find=$find&arg=$arg_url&dir=$dir_url",$add_all,$mindex);
	fclose($fp);
	displayStats($dir,$sort);
	displayUpdate($dir,$sort);
}
?>
