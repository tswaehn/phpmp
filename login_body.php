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
	if(isset($arg) && $arg) {
		$arg = decodeHTML($arg);
		fputs($fp,"password \"$arg\"\n");
		while(!feof($fp)) {
			$got =  fgets($fp,1024);
			if(strncmp("OK",$got,strlen("OK"))==0) {
				print "Login Successful<br>\n";
				if(isset($remember) && $remember=="true")
					setcookie("phpMp_password",$arg,time()+60*60*24*365);
				else 
					setcookie("phpMp_password",$arg);
				break;
			}
			print "$got<br>";
			if(strncmp("ACK",$got,strlen("ACK"))==0) 
				break;
		}
	}
	else if(!isset($logout) && isset($_COOKIE["phpMp_password"])) {
		print "<form style=\"padding:0;margin:0;\" action=login.php? method=get>\n";
		print "<table border=0 cellspacing=1 bgcolor=\"";
		print $colors["password"]["title"];
		print "\" width=\"100%\">\n";
		print "<tr><td><b>Logout</b></td></tr>\n";
		print "<tr bgcolor=\"";
		print $colors["password"]["body"];
		print "\"><td>\n";
		$dir_url = sanitizeForURL($dir);
		print "<input type=hidden value=\"logout\" name=logout>\n";
		print "<input type=hidden value=\"$dir_url\" name=dir>\n";
		print "<input type=hidden value=\"$sort\" name=sort>\n";
		print "<input type=submit value=logout name=foo>\n";
		print "\n";
		print "</td></tr></table></form>\n";
	}
	else {
		print "<form style=\"padding:0;margin:0;\" action=login.php? method=post>\n";
		print "<table border=0 cellspacing=1 bgcolor=\"";
		print $colors["password"]["title"];
		print "\" width=\"100%\">\n";
		print "<tr><td><b>Password</b></td></tr>\n";
		print "<tr bgcolor=\"";
		print $colors["password"]["body"];
		print "\"><td>\n";
		$dir_url = sanitizeForURL($dir);
		print "<input type=password name=arg value=\"$arg\" size=20>\n";
		print "<input type=hidden value=\"$dir_url\" name=dir>\n";
		print "<input type=hidden value=\"$sort\" name=sort>\n";
		print "<input type=submit value=login name=foo><br>";
		print "<small><input type=checkbox name=remember value=true>";
		print "remember password</small>";
		print "\n";
		print "</td></tr></table></form>\n";
	}
	fclose($fp);
}
ob_end_flush();
?>
