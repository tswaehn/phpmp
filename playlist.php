<?php
if (!isset($arg))
{
	$arg="";
}
if(isset($add_all))
{
	$add_all = rawurldecode($add_all);
	$add_all = stripslashes($add_all);
}
if (isset($_FILES['playlist_file']['name']))
{
	// PHP is capable of receiving multiple files, though
	// I can't find a browser that properly supports
	for($i=0;$i<sizeOf($_FILES['playlist_file']['name']);$i++)
	{
		$name = $_FILES['playlist_file']['name'][$i];
		$file = $_FILES['playlist_file']['tmp_name'][$i];
		if (!is_uploaded_file($file))
		{
			echo "Problems uploading file<br>";
		}
		else if (!($pls_fp = fopen($file, "r")))
		{
			echo "Problems opening file<br>";
		}
		else if (preg_match("/\.m3u/",$name))
		{
			$add = postStream($pls_fp,"m3u");
		}
		else if (preg_match("/\.pls/",$name))
		{
			$add = postStream($pls_fp,"pls");
		}
		else
		{
			echo "NOT a m3u or pls file!<br>";
		}
	}   
}
if (isset($stream))
{
	if (preg_match("/^(ftp|http):\/\/.*?\.(m3u|pls)/i",$stream))
	{
		$add = readFileOverHTTP($fp,$stream);
	}
        // This requires the cURL hooks, probably not that hard to implement, though it is
        // another dependency, and more problem where 0 people will probably use.
        else if (preg_match("/^https:\/\/.*?\.(m3u|pls)/",$stream))
        {
		echo "HTTPS protocol downloads are not yet implemented.";
	}
	else if (preg_match("/^[a-z]*:\/\//",$stream) && !preg_match("/^file:/",$stream))
	{
		if (strcmp(".m3u",$stream)==0)
		{
			$pls_fp = fopen($stream,"r");
			$add = postStream($pls_fp,"m3u");
		}
		else if (strcmp(".pls",$stream)==0)
		{
			$pls_fp = fopen($stream,"r");
			$add = postStream($pls_fp,"pls");
		}
		else
		{
			$command = "add";
			$add[0]=$stream;
		}
	}
        else
	{
		echo "Doesn't appear to be a url<br>";
	}
}
else if (isset($add_all) && $add_all)
{
	$add = explode($config["song_separator"],$add_all);
}
if (isset($add) && count($add)>0)
{
	$str = "command_list_begin\n";
	for ($i=0;$i<count($add);$i++)
	{
		$str .= "add \"" . $add[$i] . "\"\n";
	}
	fputs($fp,$str . "command_list_end\n");
	initialConnect($fp);
}
// End of POST information


// This will extract the needed GET/POST variables
extract(setupReceivedVars(array("crop", "time"),2));
$status = getStatusInfo($fp);

if (isset($status["error"]))
{
	echo "Error: " . $status["error"] . "<br>\n";
}
if (isset($crop) && strcmp($crop,"yes")==0)
{
        crop($fp,$status["song"],$status["playlistlength"]);

	// Since status changes after crop, we need to refresh the status
	$status = getStatusInfo($fp);
}
if (isset($status["state"]))
{
	$repeat = $status["repeat"];
	$random = $status["random"];
	$xfade = $status["xfade"];

	// STATUSBAR Begin: Top playlist_body
	echo "<!-- Begin the Top of the first table, Should only display the status and refresh -->";
        echo "<table summary=\"Status & Refresh\" cellspacing=2 bgcolor=\"" . $colors["playing"]["title"] . "\">";
	echo "<tr valign=\"middle\"><td>";

	// The global table tags begin here. This is code to make the border, this really is a hack but improves looks quite a bit
	echo "<table summary=\"Border Table Hack\" align=\"center\" bgcolor=\"" . $colors["playing"]["title"] . ">";
	echo "<tr><td width=\"100%\">";

	echo "<b>";
 	if($status["updating_db"])
	{
	        echo "Updating";
	}
	else if(strcmp($status["state"],"play")==0)
        {
	        echo "Playing";
	}
        else if(strcmp($status["state"],"stop")==0)
	{
	        echo "Stopped";
	}
	else if(strcmp($status["state"],"pause")==0)
	{
	        echo "Paused";
	}
	echo "</b>";
	echo "<small>";
	echo "&nbsp;(<a title=\"Refresh the Playlist Window\" href=\"index.php?body=playlist&amp;server=$server&amp;hide=$hide\">refresh</a>)";
	echo "</small>";
	echo "</td></tr>";
	echo "<tr></tr>"; // Make some space under the title
	echo "</table>";
	// STATUSBAR Begin: End playlist_body

	if (strcmp($status["state"],"play")==0 || 0==strcmp($status["state"],"pause"))
	{
		$num = $status["song"];
		$songid = $status["songid"];
		$time = split(":",$status["time"]);

		// SONG INFO Begin: Second table from top
		$song_info = getPlaylistInfo($fp,$num);
		echo "<table summary=\"Current Song Information\" cellspacing=0 bgcolor=\"" . $colors["playing"]["body"] . "\" cellpadding=0>";
		echo "<tr>";
		echo "<td align=\"" . $config["playlist_align"] ."\">";
		echo "<a title=\"Jump to the Current Song\" href=#$num>";

		// This is in info2html.php
		echo songInfo2Display($song_info[0]);
		echo "</a><br>";

	        // Begin The Time Remaining/Time Elapsed
	        if (strcmp($config["time_left"],"yes")==0)
		{
		        $time_min = (int)(($time[1]-$time[0])/60);
			$time_sec = (int)(($time[1]-$time[0])%60);
		}
		else
		{
		        $time_min = (int)($time[0]/60);
			$time_sec = (int)($time[0]%60);
		}

		if ($time_sec<0)
		{
		        $time_sec*=-1;
			$time_min = "-$time_min";
		}
		else if ($time_sec<10)
		{
	        $time_sec = "0$time_sec";
		}

	        echo "($time_min:$time_sec";

		// Begin the Total Time
		$time_min = (int)($time[1]/60);
		$time_sec = (int)($time[1]-$time_min*60);
		if ($time_sec<10)
	        {
		        $time_sec = "0$time_sec";
		}

		if(! ($time_min == 0 && $time_sec == 00))
		{
			echo "/$time_min:$time_sec";
		}
		echo ")&nbsp;";

		// We don't wanna hear if a bitrate is at 0 kbps
		if ( $status["bitrate"] > 0 )
		{
		          echo "[" . $status["bitrate"] . " kbps]";
		}

		echo "</td></tr></table>";

		echo "<!-- Begin Seek Bar -->";
		echo "<table summary=\"Seek Bar\" align=\"center\" cellspacing=0 bgcolor=\"" . $colors["playing"]["body"] . "\" cellpadding=0>";
		echo "<tr><td align=\"left\" width=\"5%\"></td>";

		$col=$colors["time"]["background"];

		// Remove the seek bar if it's a stream
		if ($time[1]>0) {
			$time_div = 4;
			for ($i=0; $i<round(100/$time_div); $i++)
			{
				// This is for the seekbar status
				$time_perc = $time[0]*100/$time[1];

				if ($i>=round($time_perc/$time_div)-1 && $i<=round($time_perc/$time_div)+1)
				{
					$col = $colors["time"]["foreground"];
				}

				$seek = round($i*$time_div*$time[1]/100);
				$min = (int)($seek/60);
				$sec = $seek-$min*60;

				if ($sec<10)
				{
					$sec = "0" . $sec;
				}

				echo "<td border=0 width=8 height=8 bgcolor=\"" . $col . "\">";
				if ($commands["seekid"])
				{
					echo "<a href=\"index.php?body=playlist&amp;server=$server&amp;hide=$hide&amp;command=seekid&amp;arg=$songid&amp;arg2=$seek\" title=\"$min:$sec\">";
				}
				echo "<img alt='Seek to $min:$sec' border=0 width=8 height=8 src=transparent.gif>";
				if ($commands["add"])
				{
					echo "</a>";
				}
				echo "</td>";
				$col = $colors["time"]["background"];
			}
			echo "<td align=\"right\" width=\"5%\"></td>";
		}
		echo "</tr>";
		echo "</table>";
		echo "<!-- End Seek Bar -->";
	}
}

// crossfade | random | repeat (at bottom of file)
echo "<table summary=\"Crosfade | random | repeat\"  align=\"" . $config["playlist_align"] . "\" cellspacing=0 bgcolor=\"" . $colors["playing"]["body"] . "\" cellpadding=0>";
echo "<tr><td align=\"" . $config["playlist_align"]  . "\"><small>";

if($commands["crossfade"])
{
	if($xfade)
	{
		echo "<a title=\"Remove Crossfade\" class=\"green\" href=\"index.php?body=playlist&amp;server=$server&amp;hide=$hide&amp;command=crossfade&amp;arg=0\">crossfade</a>";
	}
	else
	{
		echo "<a title=\"Set Crossfade to " . $config["crossfade_seconds"] . " Seconds\" href=\"index.php?body=playlist&amp;server=$server&amp;hide=$hide&amp;command=crossfade&amp;arg=" . $config["crossfade_seconds"]*(int)(!$xfade) . "\">crossfade</a>";
	};
}
else
{
	if($xfade)
	{
		echo "<a title=\"Remove Crossfade\" class=\"green\" href=\"index.php?body=playlist&amp;server=$server&amp;hide=$hide\">crossfade</a>";
	}
	else
	{
		echo "crossfade";
	}
}

echo "&nbsp;|&nbsp;";

if($commands["random"])
{
	if ($random)
	{
		echo "<a title=\"Turn Random Off\" class=\"green\"  href=\"index.php?body=playlist&amp;server=$server&amp;hide=$hide&amp;command=random&amp;arg=" . (int)(!$random) . "\">random</a>";
	}
	else
	{
		echo "<a title=\"Turn Random On\" href=\"index.php?body=playlist&amp;server=$server&amp;hide=$hide&amp;command=random&amp;arg=" .  (int)(!$random) . "\">random</a>";
	}
}
else
{
	if ($random)
	{
		echo "<a title=\"Turn Random Off\" class=\"green\"  href=\"index.php?body=playlist&amp;server=$server&amp;hide=$hide\">random</a>";
	}
	else
	{
		echo "random";
	}
}

echo "&nbsp;|&nbsp;";

if($commands["repeat"])
{
	if($repeat)
	{
		echo "<a title=\"Turn Repeat Off\" class=\"green\" href=\"index.php?body=playlist&amp;server=$server&amp;hide=$hide&amp;command=repeat&amp;arg=" . (int)(!$repeat) . "\">repeat</a>";
	}
	else
	{
		echo "<a title=\"Turn Repeat On\" href=\"index.php?body=playlist&amp;server=$server&amp;hide=$hide&amp;command=repeat&amp;arg=" . (int)(!$repeat) . "\">repeat</a>";
	}
}
else
{
	if($repeat)
	{
		echo "<a title=\"Turn Repeat Off\" class=\"green\" href=\"index.php?body=playlist&amp;server=$server&amp;hide=$hide\">repeat</a>";
	}
	else
	{
		echo "repeat";
	}
}

// The global table tags end here
echo "</td></tr></table>";

echo "</td></tr></table>";

// Begin [<<][Play][>>][| |][Stop] Table
echo "<table summary=\"[<<][Play][>>][| |][Stop]\" align=\"" . $config["playlist_align"] . "\" cellspacing=1 bgcolor=\"" . $colors["playing"]["title"] . "\" cellpadding=0>";
echo "<tr>";
echo "<!-- Cannot correctly space 'nowrap' td's -->";
echo "<td align=\"" . $config["playlist_align"]  ."\" nowrap>";

if((strcmp($status["state"],"play")==0 && $commands["play"]) || strcmp($status["state"],"pause")==0 && $commands["pause"])
{
	echo $display["playing"]["prev"]["active"];
}
else
{
	echo $display["playing"]["prev"]["inactive"];
}

if (strcmp($config["play_pause"],"yes")==0)
{
	if(strcmp($status["state"],"play")==0)
	{
		if($commands["pause"])
		{
			echo $display["playing"]["pause"]["active"];			
		}
		else
		{
			echo $display["playing"]["pause"]["inactive"];
		}
	}
	else
	{
		if($commands["play"] && $status["playlistlength"]>0)
		{
			echo $display["playing"]["play"]["active"];
		}
		else
		{
			echo $display["playing"]["play"]["inactive"];
		}
	}
}
else
{
	if($commands["pause"] && strcmp($status["state"],"play")==0)
	{
		echo $display["playing"]["play"]["inactive"];
		echo $display["playing"]["pause"]["active"];
	}
	else if($commands["play"] && (strcmp($status["state"],"pause")==0 || (strcmp($status["state"],"stop")==0 && $status["playlistlength"]>0)))
	{
		echo $display["playing"]["play"]["active"];
		echo $display["playing"]["pause"]["inactive"];
	}
	else
	{
		echo $display["playing"]["play"]["inactive"];
		echo $display["playing"]["pause"]["inactive"];
	}
}

if((strcmp($status["state"],"play")==0 || strcmp($status["state"],"pause")==0) && $commands["next"])
{
	echo $display["playing"]["next"]["active"];
}
else
{
	echo $display["playing"]["next"]["inactive"];
}

if((strcmp($status["state"],"play")==0 || strcmp($status["state"],"pause")==0) && $commands["stop"] && $status["playlistlength"]>0)
{
	echo $display["playing"]["stop"]["active"];
}
else
{
	echo $display["playing"]["stop"]["inactive"];
}

echo "<tr></tr></td></tr></table>";
echo "</td></tr></table>";

// This gives the space inbetween the controls and the volume bar
echo "<br>";

// This is a workaround, if left/right aligned the line break above doesn't correctly work for some reason
if(strcmp($config["playlist_align"],"center"))
{
	echo "<br>";
}

/* Begin Volume Display */
if ($status["volume"]>=0 && $config["display_volume"]=="yes")
{

	echo "<table summary=\"Volume\" cellspacing=2 bgcolor=\"" . $colors["volume"]["title"] . "\">";
	echo "<tr>";
	echo "<!-- Cannot correctly space 'nowrap' td's -->";
	echo "<td align=\"center\"><b>Volume</b></td>";
	echo "<td></td>";
	/* Begin Volume Bar */
	$vol_div = 1;
	echo "<td valign=\"middle\" align=\"center\">";
	if($status["volume"]==0)
	{
		echo "<";
	}
	else if ($commands["setvol"])
	{
		echo "<a title=\"Decrease Volume by ".$config["volume_incr"]."%\" href=\"index.php?body=playlist&amp;server=$server&amp;hide=$hide&amp;command=setvol&amp;arg=" . ($status["volume"] - $config["volume_incr"]) . "\"><</a>";
	}
	echo "</td>";
	echo "<td valign=\"middle\" align=\"center\">";
	echo "<!-- This table in a table is required for correct rendering -->";
	echo "<!-- Begin Seek Bar -->";

	// Hopefully this, in the future turns into a gd rendered png image, this is a horrible way to be doing things!
	echo "<table summary=\"Volume Hack\" cellspacing=0 cellpadding=0>";
	for ($i=0; $i<round($status["volume"]/$vol_div); $i++)
	{
		echo "<td width=5 bgcolor=\"" . $colors["volume"]["foreground"]  . "\" height=8></td>";
	}
	for (; $i<round(100/$vol_div); $i++)
	{
		echo "<td width=5 bgcolor=\"" . $colors["volume"]["background"] . "\"></td>";
	}
	echo "<!-- End Seek Bar -->";
	echo "</table></td>";
	echo "<td valign=\"middle\" align=\"center\">";
	if($status["volume"]!="100" && $commands["setvol"])
	{
		echo "<a  title=\"Increase Volume by " . $config["volume_incr"] . "%\" href=\"index.php?body=playlist&amp;server=$server&amp;hide=$hide&amp;command=setvol&amp;arg=" . ($status["volume"]+$config["volume_incr"]) . "\">></a>";
	}
	else if ($status["volume"]=="100")
	{
		echo ">";
	}
	echo "</td></tr></table>";
}

// This gives the space in between the volume bar and the playlist table/controls
echo "<br>";

if (! $status["playlistlength"] == 0)
{
	echo "<table summary=\"Playlist Table (Border)\" cellspacing=1 bgcolor=\"" . $colors["playlist"]["title"] . "\">";
	echo "<tr><td>";

	// This is for the border table
	echo "<table summary=\"Playlist Table\" cellspacing=1><tr>";
	echo "<tr valign=\"middle\"><td><b>Playlist</b></td></tr>";
	echo "<tr align=\"" . $config["playlist_align"] . "\">";
	echo "<td nowrap align=\"" . $config["playlist_align"] . "\">";
	echo "<small>";
	/* clear | crop | shuffle | save */
	if($commands["clear"])
	{
		echo "<a title=\"Clear the Active Playlist\" href=\"index.php?body=playlist&amp;server=$server&amp;hide=$hide&amp;command=clear\">clear</a>";
	}
	else
	{
		echo "clear";
	}
	echo "&nbsp;|&nbsp;";
	if($status["playlistlength"] >= 2 && strcmp($status["state"],"stop") && $commands["delete"])
	{
		echo "<a title=\"Remove All Songs Except The Currently Playing Song\"href=\"index.php?body=playlist&amp;server=$server&amp;hide=$hide&amp;crop=yes\">crop</a>";
	}
	else
	{
		echo "crop";
	}
	echo "&nbsp;|&nbsp;";
	if($commands["save"])
	{
		echo "<a title=\"Save the Active Playlist to the Saved Playlists\" target=main href=\"index.php?body=main&amp;server=$server&amp;save=yes\">save</a>";
	}
	else
	{
		echo "save";
	}
	echo "&nbsp;|&nbsp;";
	if($status["playlistlength"] >= 2 && $commands["shuffle"])
	{
		echo "<a title=\"Shuffle the Active Playlist\" href=\"index.php?body=playlist&amp;server=$server&amp;hide=$hide&amp;command=shuffle\">shuffle</a>";
	}
	else
	{
		echo "shuffle";
	}
	echo "</small></td></tr>";
	echo "</table>";
	echo "<table summary=\"Playlist Content\" cellspacing=0><tr>";

	/* Display Playlist if songs exist in the current playlist */
	if (isset($status["playlistlength"]))
	{
		printPlaylistInfo($fp, $num, $hide, $config["hide_threshold"], $status["playlistlength"], $config["filenames_only"], $commands, $arg, $colors["playlist"], $server);
	}
	echo "</tr></table>";
}
?>
