<?php
include "info.php";
include "config.php";
include "utils.php";
include "info2html.php";
$dir = "";
$search = "";
$arg = "";
$sort = $default_sort;
EXTRACT($HTTP_GET_VARS);
$dir = decodeHTML($dir);
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
	print "<br>\n";
	print "<form style=\"padding:0;margin:0;\" action=login.php? method=post>\n";
	print "<table border=0 cellspacing=1 bgcolor=\"";
	print $colors["music"]["title"];
	print "\" width=\"100%\">\n";
	print "<tr><td><b>Password</b></td></tr>\n";
	print "<tr bgcolor=\"";
	print $colors["music"]["body"][1];
	print "\"><td>\n";
	$dir_url = sanitizeForURL($dir);
	print "<input type=password name=arg value=\"$arg\" size=20>\n";
	print "<input type=hidden value=\"$dir_url\" name=dir>\n";
	print "<input type=hidden value=\"$sort\" name=sort>\n";
	print "<input type=submit value=login name=foo>\n";
	print "\n";
	print "</td></tr></table></form>\n";
	$arg_url = sanitizeForURL($arg);
	fclose($fp);
	displayStats($dir,$sort);
	displayUpdate($dir,$sort);
}
?>
