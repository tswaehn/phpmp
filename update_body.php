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
	fputs($fp,"update\n");
	print "<br>updating ...<br>\n";
	flush();
	while(!feof($fp)) {
		$got =  fgets($fp,1024);
		if(strncmp("OK",$got,strlen("OK"))==0) {
			print "update finished successfully\n";
			break;
		}
		print "$got<br>";
		if(strncmp("ACK",$got,strlen("ACK"))==0) 
			break;
	}
	fclose($fp);
}
?>
