<?php
include "config.php";
include "utils.php";
include "info.php";
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
	fputs($fp,"update\n");
	while(!feof($fp)) {
		$got =  fgets($fp,1024);
		if(strncmp("OK",$got,strlen("OK"))==0) {
			break;
		}
		if(strncmp("updating_db",$got,strlen("updating_db"))==0) {
			$update_id = preg_replace("/^updating_db: /","",$got);
			$update_id = preg_replace("/\n/","",$update_id);
			continue;
		}
		print "$got<br>";
		if(strncmp("ACK",$got,strlen("ACK"))==0) 
			break;
	}
	print "<br>updating ";
	flush();
	$status = getStatusInfo($fp);
	while(isset($update_id) && isset($status["updating_db"]) && 
			$status["updating_db"]==$update_id) 
	{
		print ".";
		flush();
		sleep(1);
		$status = getStatusInfo($fp);
	}
	print "<br>\n";
	print "Update Successful<br>\n";
	fclose($fp);
}
?>
