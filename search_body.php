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
	print "<br>\n";
	print "<form style=\"padding:0;margin:0;\" action=search.php? method=get>\n";
	print "<table border=0 cellspacing=1 bgcolor=\"";
	print $colors["music"]["title"];
	print "\" width=\"100%\">\n";
	print "<tr><td><b>Search</b></td></tr>\n";
	print "<tr bgcolor=\"";
	print $colors["music"]["body"][1];
	print "\"><td>\n";
	print "<select name=search>\n";
	function printFileNameOption($which_search) {
		if(0==strcmp($which_search,"filename")) 
			print "<option value=\"filename\" selected>file name</option>\n";
		else print "<option value=\"filename\">file name</option>\n";
	}
	if($filenames_only=="yes") printFileNameOption($search);
	if(0==strcmp($search,"title")) 
		print "<option selected>title</option>\n";
	else print "<option>title</option>\n";
	if(0==strcmp($search,"album")) 
		print "<option selected>album</option>\n";
	else print "<option>album</option>\n";
	if(0==strcmp($search,"artist")) 
		print "<option selected>artist</option>\n";
	else print "<option>artist</option>\n";
	if($filenames_only!="yes") printFileNameOption($search);
	print "</select>\n";
	$dir_url = sanitizeForURL($dir);
	print "<input name=arg value=\"$arg\" size=40>\n";
	print "<input type=hidden value=\"$dir_url\" name=dir>\n";
	print "<input type=hidden value=\"$sort\" name=sort>\n";
	print "<input type=submit value=search name=foo>\n";
	print "\n";
	print "</td></tr></table></form>\n";
	if($search && $arg) {
		$lsinfo = getLsInfo($fp,"search $search \"$arg\"\n");
		list($mprint,$mindex,$add_all) = lsinfo2musicTable($lsinfo,$sort,$dir_url);
	}
	$arg_url = sanitizeForURL($arg);
	if(isset($mprint)) {
		printMusicTable($mprint,"search.php?search=$search&arg=$arg_url&dir=$dir_url",$add_all,$mindex);
	}
	fclose($fp);
	displayStats($dir,$sort);
	displayUpdate($dir,$sort);
}
?>
