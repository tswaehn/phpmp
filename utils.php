<?php
function decodeHTML($string) {
	$string = preg_replace("/\%26/","&",$string);
	$string = preg_replace("/\%20/"," ",$string);
	$string = preg_replace("/\%2D/","-",$string);
	$string = preg_replace("/\%2B/","+",$string);
	$string = preg_replace("/\%23/","#",$string);
	$string = preg_replace("/\%27/","'",$string);
	$string = preg_replace("/\%22/","\"",$string);
	$strng = addSlashes($string);
	return $string;
}

function sanitizeForURL($str) {
	$url = stripslashes($str);
	$url = preg_replace("/\&/","%26",$url);
	$url = preg_replace("/ /","%20",$url);
	$url = preg_replace("/-/","%2D",$url);
	$url = preg_replace("/\+/","%2B",$url);
	$url = preg_replace("/#/","%23",$url);
	$url = preg_replace("/\'/","%27",$url);
	$url = preg_replace("/\"/","%22",$url);
	return $url;
}

function sanitizeForPost($str) {
	$url = $str;
	$url = preg_replace("/\&/","%26",$url);
	$url = preg_replace("/ /","%20",$url);
	$url = preg_replace("/-/","%2D",$url);
	$url = preg_replace("/\+/","%2B",$url);
	$url = preg_replace("/#/","%23",$url);
	$url = preg_replace("/\'/","%27",$url);
	$url = preg_replace("/\"/","%22",$url);
	return $url;
}

function displayDirectory($dir,$sort,$title,$music,$playlists) {
	global $colors,$has_password;
	$dir_url = sanitizeForURL($dir);
	print "<table border=0 cellspacing=1 bgcolor=\"";
	print $colors["directories"]["title"];
	print "\" width=\"100%\">\n";
	print "<tr><td><b>$title</b>\n";
	if($music) print "(<a href=\"#music\">Music</a>) ";
	if($playlists) print "(<a href=\"#playlists\">Playlists</a>) ";
	print "</td><td align=right>";
	print "[<a href=\"login.php?dir=$dir_url&sort=$sort\">";
	if($has_password) 
		print "Logout</a>]\n";
	else
		print "Login</a>]\n";
	print "[<a href=\"stream.php?dir=$dir_url&sort=$sort\">Stream</a>]\n";
	print "[<a href=\"search.php?dir=$dir_url&sort=$sort\">";
	print "Search</a>]</td></tr>\n";
	print "<tr bgcolor=\"";
	print $colors["directories"]["body"][0];
	print "\"><td colspan=2>";
	$dirs = split("/",$dir);
	print "<a href=main.php?sort=$sort>Music</a>";
	$build_dir = "";
	for($i=0;$i<count($dirs)-1;$i++) {
		if($i>0 && $i<(count($dirs)-1)) $build_dir.="/";
		$dirs[$i] = stripslashes($dirs[$i]);
		$build_dir.="$dirs[$i]";
		$build_dir = sanitizeForURL($build_dir);
		print " / <a href=\"main.php?sort=$sort&dir=$build_dir\">$dirs[$i]</a>";
	}
	if($i>0) $build_dir.="/";
	if(strlen($dir)>0) {
		$dirs[$i] = stripslashes($dirs[$i]);
		$build_dir.="$dirs[$i]";
		$build_dir = sanitizeForURL($build_dir);
		print " / <a href=\"main.php?sort=$sort&dir=$build_dir\">$dirs[$i]</a>";
	}
	print "</td></tr></table>\n";
}

function displayUpdate($dir,$sort) {
	$dir_url = sanitizeForURL($dir);
	print "<table width=\"100%\"><tr><td><small>[";
	print "<a href=\"update.php?dir=$dir_url&sort=$sort\">";
	print "Update</a>] - Update Music Database (scans music directory for changes)";
	print "</small></td></tr></table>\n";
}

function displayStats($dir,$sort) {
	$dir_url = sanitizeForURL($dir);
	print "<br><table width=\"100%\"><tr><td><small>[";
	print "<a href=\"stats.php?dir=$dir_url&sort=$sort\">";
	print "Stats</a>] - Display MPD Stats";
	print "</small></td></tr></table>\n";
}

function mbFirstChar($str) {
	$i = 1;
	$ret = "$str[0]";
	while($i < strlen($str) && ord($str[$i]) >= 128  && ord($str[$i]) < 192) {
		$ret.=$str[$i];
		$i++;
	}
	return $ret;
}

function readM3uFile($fp) {
	$add = array();
	$i = 0;

	while(!feof($fp)) {
		$url = fgets($fp,4096);
		$url = preg_replace("/\n$/","",$url);
		if(preg_match("/^[a-z]*:\/\//",$url)) {
			$add[$i] = $url;
			$i++;
		}
	}

	return $add;
}

function readPlsFile($fp) {
	$add = array();
	$i = 0;

	while(!feof($fp)) {
		$line = fgets($fp,4096);
		if(preg_match("/File[0-9]*=/",$line)) {
			$url = preg_replace("/^File[0-9]*=/","",$line);
			$url = preg_replace("/\n$/","",$url);
			if(preg_match("/^[a-z]*:\/\//",$url)) {
				$add[$i] = $url;
				$i++;
			}
		}
	}

	return $add;
}
?>
