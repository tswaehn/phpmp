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
EXTRACT($HTTP_POST_VARS);
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
	if(isset($logout) && $logout=="logout") {
		setcookie("phpMp_password","");
		$has_password = 0;
	}
	$dir_url = sanitizeForURL($dir);
	displayDirectory($dir,$sort,"Back to Directory",0,0);
	print "<br>\n";
	print "<form style=\"padding:0;margin:0;\" action=playlist.php? target=playlist method=get>\n";
	print "<table border=0 cellspacing=1 bgcolor=\"";
	print $colors["music"]["title"];
	print "\" width=\"100%\">\n";
	print "<tr><td><b>Add Stream</b></td></tr>\n";
	print "<tr bgcolor=\"";
	print $colors["music"]["body"][0];
	print "\"><td>\n";
	$dir_url = sanitizeForURL($dir);
	print "<input type=input name=stream size=40>\n";
	print "<input type=submit value=add name=foo><br>";
	print "</td></tr></table></form>\n";
	print "<br>\n";
	print "<form style=\"padding:0;margin:0;\" enctype=\"multipart/form-data\" action=playlist.php? target=playlist method=post>\n";
	print "<table border=0 cellspacing=1 bgcolor=\"";
	print $colors["music"]["title"];
	print "\" width=\"100%\">\n";
	print "<tr><td><b>Load Stream From Playlist</b></td></tr>\n";
	print "<tr bgcolor=\"";
	print $colors["music"]["body"][0];
	print "\"><td>\n";
	print "<input type=file name=playlist_file size=30>\n";
	print "<input type=submit value=load name=foo><br>";
	print "</td></tr></table></form>\n";
}
ob_end_flush();
?>
