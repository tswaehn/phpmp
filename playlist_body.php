<?php

if(!isset($arg)) $arg="";

include "info.php";
include "info2html.php";
include "utils.php";

if(isset($add_dir)) $add_dir = decodeHTML($add_dir);
if(isset($add_all)) {
	$add_all = decodeHTML($add_all);
	$add_all = stripslashes($add_all);
}
$fp = fsockopen($host,$port,$errno,$errstr,10);
if(!$fp) {
	echo "$errstr ($errno)<br>\n";
}
else {
	print "<table border=0 cellspacing=1 bgcolor=\"";
	print $colors["playing"]["title"];
	print "\" width=\"100%\">\n";
	print "<tr valign=middle><td>\n";
	print "<b>Playing</b>\n";
	print "<small>(<a href=playlist.php?hide=$hide>refresh</a>)</small>\n";
	print "</td></tr>\n";
	print "<tr bgcolor=\"";
	print $colors["playing"]["body"];
	print "\"><td>\n";
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
	if(isset($HTTP_POST_FILES['playlist_file']['name'])) {
		$name = $HTTP_POST_FILES['playlist_file']['name'];
		$file = $HTTP_POST_FILES['playlist_file']['tmp_name'];
		if(!is_uploaded_file($file)) {
			print "Problems uploading file<br>";
		}
		else if(!($pls_fp = fopen($file, "r"))) {
			print "Problems opening file<br>";
		}
		else if(preg_match("/\.m3u/",$name)) {
			$add = readM3uFile($pls_fp);
		}
		else if(preg_match("/\.pls/",$name)) {
			$add = readPlsFile($pls_fp);
                }
		else {
			print "NOT a m3u or pls file!<br>";
		}
	}
	if(isset($stream)) {
		if(preg_match("/^[a-z]*:\/\//",$stream) && !preg_match("/^file:/",$stream)) {
			if(preg_match("/\.m3u/",$stream)) {
				$pls_fp = fopen($stream,"r");
				$add = readM3uFile($pls_fp);
			}
			else if(preg_match("/\.pls/",$stream)) {
				$pls_fp = fopen($stream,"r");
				$add = readPlsFile($pls_fp);
                	}
			else {
				$command = "add";
				$arg = $stream;
			}
		}
		else {
			print "Doesn't appear to be a url<br>";
		}
	}
	if(isset($command)) {
		$arg = preg_replace("/\"/","\\\"",$arg);
		if(strlen($arg)>0)
			$command.=" \"$arg\"";
		$command = preg_replace("/\\\\\"/","\"",$command);
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
	else if(isset($add_all) && $add_all) {
		global $song_seperator;
		$add = explode($song_seperator,$add_all);
	}
	else if(isset($add_dir)) {
		$add = array();
		$i = 0;
		fputs($fp,"listall \"$add_dir\"\n");
		while(!feof($fp)) {
			$got =  fgets($fp,1024);
			if(strncmp("OK",$got,strlen("OK"))==0) 
				break;
			if(strncmp("ACK",$got,strlen("ACK"))==0) {
				print "$got<br>";
				break;
			}
			if(strncmp($got,"file: ",strlen("file: "))==0) {
				$got = preg_replace("/\n/","",$got);
				$got = preg_replace("/^file\: /","",$got);
				$add[$i] = addslashes($got);
				$i++;
			}
		}
	}
	if(isset($add) && count($add)>0) {
		fputs($fp,"command_list_begin\n");
		for($i=0;$i<count($add);$i++) {
			fputs($fp,"add \"$add[$i]\"\n");
		}
		fputs($fp,"command_list_end\n");
		while(!feof($fp)) {
			$got =  fgets($fp,1024);
			if(strncmp("OK",$got,strlen("OK"))==0) 
				break;
			print "$got<br>";
			if(strncmp("ACK",$got,strlen("ACK"))==0) {
				break;
			}
		}
	}
	$status = getStatusInfo($fp);
	if(isset($status["error"])) {
		print "Error: " . $status["error"] . "<br>\n";
	}
	if(isset($status["state"])) {
		$vol = $status["volume"];
		$repeat = $status["repeat"];
		$random = $status["random"];
		$xfade = $status["xfade"];
		if(strcmp($status["state"],"play")==0 || 0==strcmp($status["state"],"pause")) {
			$num = $status["song"];
			$songid = $status["songid"];
			$time = split(":",$status["time"]);
			$time_min = (int)($time[0]/60);
			$time_sec = (int)($time[0]-$time_min*60);
			if($time_sec<0) {
				$time_sec*=-1;
				$time_min = "-$time_min";
			}
			if($time_sec<10) $time_sec = "0$time_sec";
			$song_info = getPlaylistInfo($fp,$num);
			print "<table border=0 cellpadding=0 cellspacing=0>";
			print "<tr><td colspan=6>";
			print "<a href=#$num>";
			print songInfo2Display($song_info[0]);
			print "</a><br>\n";
			print "($time_min:$time_sec)\n";
			if($time[1] > 0) {
				$time_min = (int)($time[1]/60);
				$time_sec = (int)($time[1]-$time_min*60);
				if($time_sec<10) $time_sec = "0$time_sec";
				print "[$time_min:$time_sec]\n";
			}
			else {
				print "[" . $status["bitrate"] . " kbs]\n";
			}
			if($time[1]>0)
				$time_perc = $time[0]*100/$time[1];
			else $time_perc = 100.0;
			$time_div = 4;
			$do = round($time_perc/$time_div);
			print "<table border=0 cellspacing=0 cellpadding=0 height=\"8\"><tr>";
			$col = $colors["time"]["foreground"];
			$col = $colors["time"]["background"];
			for($i=0; $i<round(100/$time_div); $i++) {
				if($i>=$do-1 && $i<=$do+1) {
					$col = $colors["time"]["foreground"];
				}
				$seek = round($i*$time_div*$time[1]/100);
				$min = (int)($seek/60);
				$sec= $seek-$min*60;
				if($sec<10) $sec = "0$sec";
				print "<td width=8 bgcolor=\"$col\"><a href=\"playlist.php?hide=$hide&command=seekid $songid $seek\" title=\"$min:$sec\"><img border=0 width=8 height=8 src=transparent.gif></a></td>";
				$col = $colors["time"]["background"];
			}
			print "</tr></table>\n";
			print "</td></tr><tr>";
			if($repeat) {
				print "<td bgcolor=\"";
				print $colors["playing"]["on"];
				print "\">";
			}
			else print "<td>";
			print "<small>[<a href=\"playlist.php?hide=$hide&command=repeat%20";
			print (int)(!$repeat) . "\">repeat</a>]</small>";
			print "</td><td>&nbsp</td>";
			if($random) {
				print "<td bgcolor=\"";
				print $colors["playing"]["on"];
				print "\">";
			}
			else print "<td>";
			print "<small>[<a href=\"playlist.php?hide=$hide&command=random%20";
			print (int)(!$random) . "\">random</a>]</small>";
			print "</td><td>&nbsp</td>";
			if($xfade) {
				print "<td bgcolor=\"";
				print $colors["playing"]["on"];
				print "\">";
			}
			else print "<td>";
			print "<small>[<a href=\"playlist.php?hide=$hide&command=crossfade%20";
			print 10*(int)(!$xfade) . "\">xfade</a>]</small>";
			print "</td><td width=\"100%\"></td></tr></table>\n";
			print "</td></tr>\n";
			print "<tr><td nowrap>\n";
			if(strcmp($status["state"],"play")==0) {
				print $display["playing"]["prev"]["active"];
				print $display["playing"]["play"]["inactive"];
				print $display["playing"]["next"]["active"];
				print $display["playing"]["pause"]["active"];
				print $display["playing"]["stop"]["active"];
			}
			else {
				print $display["playing"]["prev"]["active"];
				print $display["playing"]["play"]["pause"];
				print $display["playing"]["next"]["active"];
				print $display["playing"]["pause"]["inactive"];
				print $display["playing"]["stop"]["active"];
			}
		}
		else {
			print "<table border=0 cellpadding=0 cellspacing=0>";
			print "<tr><td colspan=6>";
			print "<br><br>\n";
			$col = $colors["time"]["background"];
			print "<table border=0 cellspacing=0 cellpadding=0 height=\"8\" width=200 bgcolor=\"$col\"><tr><td></td></tr></table>";
			print "</td></tr><tr>";
			if($repeat) {
				print "<td bgcolor=\"";
				print $colors["playing"]["on"];
				print "\">";
			}
			else print "<td>";
			print "<small>[<a href=\"playlist.php?hide=$hide&command=repeat%20";
			print (int)(!$repeat) . "\">repeat</a>]</small>";
			print "</td><td>&nbsp</td>";
			if($random) {
				print "<td bgcolor=\"";
				print $colors["playing"]["on"];
				print "\">";
			}
			else print "<td>";
			print "<small>[<a href=\"playlist.php?hide=$hide&command=random%20";
			print (int)(!$random) . "\">random</a>]</small>";
			print "</td><td>&nbsp</td>";
			if($xfade) {
				print "<td bgcolor=\"";
				print $colors["playing"]["on"];
				print "\">";
			}
			else print "<td>";
			print "<small>[<a href=\"playlist.php?hide=$hide&command=crossfade%20";
			print 10*(int)(!$xfade) . "\">xfade</a>]</small>";
			print "</td><td width=\"100%\"></td></tr></table>\n";
			print "</td></tr>\n";
			print "<tr><td nowrap>\n";
			if($status["playlistlength"]>0) {
				print $display["playing"]["prev"]["inactive"];
				print $display["playing"]["play"]["active"];
				print $display["playing"]["next"]["inactive"];
				print $display["playing"]["pause"]["inactive"];
				print $display["playing"]["stop"]["inactive"];
			}
			else {
				print $display["playing"]["prev"]["inactive"];
				print $display["playing"]["play"]["inactive"];
				print $display["playing"]["next"]["inactive"];
				print $display["playing"]["pause"]["inactive"];
				print $display["playing"]["stop"]["inactive"];
			}
		}
		print "<br>\n";
	}
	print "</td></tr></table><br>\n";
	# begin volume display
	if(isset($vol) && $vol>=0 && $display_volume=="yes") {
		print "<table width=\"100%\" border=0 cellspacing=0 bgcolor=\"";
		print $colors["volume"]["body"];
		print "\"><tr><td>\n";
		print "<table border=0 cellspacing=0><tr><td nowrap><b>Volume</b> ";
		$vol_div = 5;
		$do = round($vol/$vol_div);
		print "[<a href=\"playlist.php?hide=$hide&command=volume%20-$volume_incr\">-</a>]</td>";
		print "<td valign=middle><table border=0 cellspacing=0 cellpadding=0 height=\"8\"><tr>";
		$col = $colors["volume"]["foreground"];
		for($i=0; $i<$do; $i++) print "<td width=5 bgcolor=\"$col\"></td>";
		$col = $colors["volume"]["background"];
		for(; $i<round(100/$vol_div); $i++) {
			print "<td width=5 bgcolor=\"$col\"></td>";
		}
		print "</tr></table></td>\n";
		print "<td>[<a href=\"playlist.php?hide=$hide&command=volume%20$volume_incr\">+</a>]</td>\n";
		print "</td></tr></table>\n";
		print "</td></tr></table><br>\n";
	}
	# end of volume display
	/* display playlist */
	print "<table border=0 cellspacing=1 bgcolor=\"";
	print $colors["playlist"]["title"];
	print "\" width=\"100%\">\n";
	print "<tr valign=middle><td width=\"0\"><b>Playlist</b>\n";
	print "<small>";
	print "[<a href=\"playlist.php?hide=$hide&command=shuffle\">shuffle</a>]";
	print "[<a target=main href=main.php?save=yes>save</a>]";
	print "[<a href=\"playlist.php?hide=$hide&command=clear\">clear</a>]";
	print "</small>";
	print "</td></tr>\n";
	print "<tr><td>\n";
	print "<table border=0 cellspacing=0 width=\"100%\">\n";
	if(!isset($num)) $num = -1;
	if(isset($status["playlistlength"])) {
		printPlaylistInfo($fp,$num,$hide,$hide_threshold,$status["playlistlength"]);
	}
	print "</table>\n";
	print "</tr></td>\n";
	print "</table>\n";
	fclose($fp);
}
?>
