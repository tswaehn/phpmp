<?php
function getStatusInfo($conn)
{
	fputs($conn,"status\n");
	while (!feof($conn))
	{
		$got = fgets($conn,1024);
		$got = str_replace("\n","",$got);

		if (strncmp("OK",$got,strlen("OK"))==0)
		{
			break;
		}

		if (strncmp("ACK",$got,strlen("ACK"))==0)
		{
			echo "$got<br>";
			break;
		}

		$el = strtok($got,":");
		$ret["$el"] = strtok("\0");
		$ret["$el"] = preg_replace("/^ /","",$ret["$el"]);
	}
	if (!isset($ret))
	{
	        $ret = array();
	}
	return $ret;
}

function getCommandInfo($conn)
{
	fputs($conn,"commands\n");
	while(!feof($conn))
	{
		$got = fgets($conn,1024);
		$got = str_replace("\n","",$got);
		if (strncmp("OK",$got,strlen("OK"))==0)
		{
			break;
		}
		if (strncmp("ACK",$got,strlen("ACK"))==0)
		{
			echo "$got<br>";
			break;
		}

		$el = str_replace("command: ","",$got);
		$ret[$el] = "1";
	}
	return $ret;
}

function setNotSetSongFields($song)
{

	if (isset($song["Title"]))
	{
		if (!isset($song["Track"]))
		{
		        $song["Track"] = "";
		}
		if (!isset($song["Album"]))
		{
		        $song["Album"] = "";
		}
		if (!isset($song["Artist"]))
		{
		        $song["Artist"] = "";
		}
		if (!isset($song["Genre"]))
		{
			$song["Genre"] = "";
		}
		if (!isset($song["Date"]))
		{
			$song["Date"] = "";
		}
	}

	return $song;
}

function getPlaylistInfo($conn,$song)
{
	fputs($conn,"playlistinfo $song\n");
	$count = -1;
	while (!feof($conn))
	{
		$got =  fgets($conn,1024);
		$got = str_replace("\n","",$got);
		if (strncmp("OK",$got,strlen("OK"))==0)
		{
			break;
		}
		if (strncmp("ACK",$got,strlen("ACK"))==0)
		{
			break;
		}
		$el = strtok($got,":");
		if (0==strcmp($el,"file"))
		{
			if ($count>=0)
			{
			        $ret[$count] = setNotSetSongFields($ret[$count]);
			}
			$count++;
		}
		$ret[$count]["$el"] = strtok("\0");
		$ret[$count]["$el"] = preg_replace("/^ /","",$ret[$count]["$el"]);
	}
	if (!isset($ret))
	{
	    $ret = array();
	}
	return $ret;
}

function printPlaylistInfo($conn, $num, $hide, $spread, $length, $filenames_only, $commands, $arg, $color, $server)
{
	function local($count, $start, $filenames_only, $ret, $num, $color, $server, $hide, $commands, $tm)
	{
		if ($count>$start)
		{
		        $goto = $count-1;
		}
		else 
		{
		        $goto = $count;
		}

		if (strcmp($filenames_only,"yes") && isset($ret["Name"]) && $ret["Name"])
		{
			$display = $ret["Name"];
		}
		else 
		{
			$display = songInfo2Display($ret);
		}

		$id = $ret["Id"];
		if (isset($num) && $num==$count)
		{
			echo "<tr bgcolor=\"". $color["current"] . "\">";
		}
		else
		{
		        echo "<tr bgcolor=\"" . $color["body"][($count%2)] . "\">";
		}
		echo "<td valign=top><a name=$count></a><small>";
		if($commands["delete"])
		{
			echo "<small><a title=\"Remove song from the playlist\" href=\"index.php?body=playlist&amp;server=$server&amp;hide=$hide&amp;command=delete&amp;arg=$count&amp;time=$tm#$goto\">d</a></small></td>";
		}
		else
		{
			echo "d</small></td>";
		}

		if($commands["play"])
		{
			echo "<td width=\"100%\"><a title=\"Play this song\" href=\"index.php?body=playlist&amp;server=$server&amp;hide=$hide&amp;command=play&amp;arg=$count\">$display</a>";
		}
		else
		{
			echo "<td width=\"100%\">$display";
		}
		echo "</td></tr>";
	}

	/*
		$count => $start-1
		$num => The actual MPD playlist number
	*/

	$tm = time();
	$start = 0;
	$end = $length-1;
	$spread *= 2;
	echo "<!-- Begin printPlaylistInfo Here -->";
	if ($hide)
	{
		// $start is playlist length minus the spread divided by two
		$start = $num-$spread/2;
		
		// $end is just the length-1
		$end = $num+$spread/2;

		/*
		   If $start is less than 0 go ahead and make it 0 and 
		   the $end will be $end - start, we don't need to show 
		   the beginning hide marks
		*/ 
		if ($start<0)
		{
			$end -= $start;
			$start = 0;
		}

		/*
		   Else if $end>=$length we don't need to worry about
		   the ending ...
		*/
		if ($end>=$length)
		{
			$start -= $end - $length+1;
			if ($start<0)
			{
				$start = 0;
				$end = $length-1;
			}
		}

		//  Else show the beginning ... string
		if ($start>0)
		{
			echo "<tr bgcolor=\"" . $color["body"][($start-1)%2] . "\">";
			echo "<td colspan=2 align=center><small>";
			echo "<a title=\"Unhide the playlist\"  href=\"index.php?body=playlist&amp;server=$server&amp;hide=0\">...</a>";
			echo "</small></td></tr>";
		}
                
		$str = "command_list_begin\n";
		for ($i=$start;$i<=$end;$i++)
		{
			$str .= "playlistinfo $i\n";
		}
		fputs($conn,$str . "command_list_end\n");
	}
	else
	{
		if ($length>$spread+1)
		{
			echo "<tr bgcolor=\"" . $color["body"][1] . "\">";
			echo "<td colspan=2 align=center><small>";
			echo "(<a title=\"Hide the playlist\" href=\"index.php?body=playlist&amp;server=$server&amp;hide=1\">condense</a>)";
			echo "</small></td></tr>";
		}
		fputs($conn,"playlistinfo -1\n");
	}
	$count = $start-1;
	while (!feof($conn))
	{
		$got = fgets($conn,1024);
		$got = str_replace("\n","",$got);

		if (strncmp("OK",$got,strlen("OK"))==0)
		{
			break;
		}
		else if (strncmp("ACK",$got,strlen("ACK"))==0)
		{
			break;
		}

		$el = strtok($got,":");
		if (0==strcmp($el,"file"))
		{
			if ($count>=$start)
			{
				local($count, $start, $filenames_only, $ret, $num, $color, $server, $hide, $commands, $tm);
				unset ($ret);
			}
			$count++;
		}
		$ret["$el"] = preg_replace("/^ /","",strtok("\0"));
	}
	if ($count>=$start)
	{
		local($count, $start, $filenames_only, $ret, $num, $color, $server, $hide, $commands, $tm);
	}
	if ($hide && $end<$length-1)
	{
		echo "<tr bgcolor=\"" . $color["body"][(($end+1)%2)] . "\">";
		echo "<td colspan=2 align=center><small>";
		echo "<a title=\"Unhide the playlist\" href=\"index.php?body=playlist&amp;server=$server&amp;hide=0\">...</a>";
		echo "</small></td></tr>";
	}
	echo "<!-- End printPlaylistInfo Here -->";
}

function getLsInfo($conn,$command)
{
	fputs($conn,$command);
	$mcount = -1;
	$dcount = 0;
	$pcount = 0;
	while (!feof($conn))
	{
		$got = fgets($conn,1024);
		$got = str_replace("\n","",$got);
		if (strncmp("OK",$got,strlen("OK"))==0)
		{
			break;
		}
		if (strncmp("ACK",$got,strlen("ACK"))==0)
		{
			echo "$got<br>";
			break;
		}
		$el = strtok($got,":");
		if (strcmp($el,"directory")==0)
		{
			$dir[$dcount] = preg_replace("/^$el: /","",$got);
			$dcount++;
			continue;
		}
		if (strcmp($el,"playlist")==0)
		{
			$playlist[$pcount] = preg_replace("/^$el: /","",$got);
			$pcount++;
			continue;
		}
		if (strcmp($el,"file")==0)
		{
			if ($mcount>=0)
			{
			        $music[$mcount] = setNotSetSongFields($music[$mcount]);
			}
			$mcount++;
			$music[$mcount] = array('file' => '', 'Title' => '', 'Time' => '', 'Track' => '', 'Track' => '', 'Album' => '', 'Artist' => '', 'Genre' => '', 'Date' => '');
		}
		$music[$mcount]["$el"] = preg_replace("/^$el: /","",$got);
	}
	if (!isset($dir))
	{
	        $dir = array();
	}
	if (!isset($music))
	{
	        $music = array();
	}
	if (!isset($playlist))
	{
	        $playlist = array();
	}
	$ret["dir"] = $dir;
	$ret["music"] = $music;
	$ret["playlist"] = $playlist;
	return $ret;
}
?>
