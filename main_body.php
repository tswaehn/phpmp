<?php
include "info.php";
include "utils.php";
include "info2html.php";
$arg = "";
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
	if(isset($command)) {
		if(strlen($arg)>0) $command.=" \"$arg\"";
		fputs($fp,"$command\n");
		while(!feof($fp)) {
			$got =  fgets($fp,1024);
			if(strncmp("OK",$got,strlen("OK"))==0) 
				break;
			preg_replace("/\n/","\n<br>",$got);
			print "$got<br>";
			if(strncmp("ACK",$got,strlen("ACK"))==0) 
				break;
		}
	}
	$dir_url = sanitizeForURL($dir);
	if(strlen($dir)>0) $lsinfo = getLsInfo($fp,"lsinfo \"$dir\"\n");
	else $lsinfo = getLsInfo($fp,"lsinfo\n");

	# lsinfo2musicTable should start here
	
	$dcount = count($lsinfo["dir"]);
	if($dcount) usort($lsinfo["dir"],"strcasecmp");
	$dic = 0;
	for($i=0;$i<$dcount;$i++) {
		$dirent = $lsinfo["dir"][$i];
		$dirstr = $dirent;
		$dirss = split("/",$dirstr);
		if(count($dirss)==0) 
		$dirss[0] = $dirstr;
		$dirss[0] = $dirss[count($dirss)-1];
		$dirstr = sanitizeForURL($dirstr);
		$dcol = $colors["directories"]["body"][$i%2];
		$dprint[$i] = "<tr bgcolor=\"$dcol\"><td>";
		$fc = strtoupper(mbFirstChar($dirss[0]));
		if($dic==0 || $dindex[$dic-1]!=$fc) {
			$dindex[$dic] = $fc;
			$foo = $dindex[$dic];
			$dic++;
			$dprint[$i].="<a name=d$foo>";
		}
		$dprint[$i].="[<a href=\"playlist.php?add_dir=$dirstr\" target=playlist>add</a>] <a href=\"main.php?sort=$sort&dir=$dirstr\">$dirss[0]</a></td></tr>\n";
		# $dprint[$i].="[<a href=\"main.php?sort=$sort&dir=$dirstr\">dir</a>] [<a href=\"playlist.php?add_dir=$dirstr\" target=playlist>add</a>] $dirss[0]</td></tr>\n";
	}
	if(!isset($dindex)) $dindex = array();

	# end of lsinfo2directoryTable
	
	list($pprint,$pindex) = lsinfo2playlistTable($lsinfo,$sort);
	list($mprint,$mindex,$add_all) = lsinfo2musicTable($lsinfo,$sort,$dir_url);
	displayDirectory($dir,$sort,"Current Directory",count($mprint),count($pprint));
	if(isset($save) && $save) {
		print "<br><form style=\"padding:0;margin:0;\" action=main.php? method=get>\n";
		print "<table border=0 cellspacing=1 bgcolor=\"";
		print $colors["playlist"]["title"];
		print "\" width=\"100%\">\n";
		print "<tr><td><b>Save Playlist</b></td></tr>\n";
		print "<tr bgcolor=\"";
		print $colors["playlist"]["body"];
		print "\"><td>\n";
		print "<input name=arg size=40>\n";
		print "<input type=hidden value=save name=command>\n";
		print "<input type=submit value=save name=foo>\n";
		print "</td></tr></table>\n";
		print "</form>\n";
		$dir = "";
	}

	# begin printDirectoryTable
	# dcount -> is the number of directories
	# dprint -> array, has dcount elements, just do print $dprint[$i]
	#           to print output for that directory, it was formatted
	#	    in lsinfo2directoryTable (this parses input from 
	#           lsinfo and make the $dprint's for output)
	# dindex -> these are the links etc for the index elements point too
	# printIndex -> function that takes $dinex and prints all the links
	#               for the indexes
	
	if($dcount) {
		print "<br>\n";
		print "<table border=0 cellspacing=1 bgcolor=\"";
		print $colors["directories"]["title"];
		print "\" width=\"100%\">\n";
		print "<tr><td nowrap><b>Directories</b>\n";
		printIndex($dindex,"","d");
		print "</td></tr>\n";
		print "<tr><td><table border=0 cellspacing=1 bgcolor=\"";
		print $colors["directories"]["body"][1];
		print "\" width=\"100%\">\n";
		for($i=0;$i<$dcount;$i++) print $dprint[$i];
		print "</table></td></tr></table>\n";
	}

	# end of printDirectoryTable
	
	printMusicTable($mprint,"main.php?dir=$dir_url",$add_all,$mindex);
	printPlaylistTable($pprint,$pindex);
	fclose($fp);
	displayStats($dir,$sort);
	displayUpdate($dir,$sort);
}
?>
