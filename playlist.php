<?php
if( ! empty( $add_all ))
{
	$add_all = rawurldecode( $add_all );
}
if( ! empty( $_FILES['playlist_file']['name'] ))
{
	// PHP is capable of receiving multiple files, though
	// I can't find a browser that properly supports
	for( $i=0; $i < sizeOf( $_FILES['playlist_file']['name'] ); $i++ )
	{
		$name = $_FILES['playlist_file']['name'][$i];
		$file = $_FILES['playlist_file']['tmp_name'][$i];
		if( ! is_uploaded_file( $file ))
		{
			echo "Problems uploading file<br>";
		}
		else if( ! $pls_fp = fopen( $file, "r" ))
		{
			echo "Problems opening file<br>";
		}
		else if( preg_match( "/\.m3u/", $name ))
		{
			$add = postStream( $pls_fp, "m3u" );
		}
		else if( preg_match( "/\.pls/", $name ))
		{
			$add = postStream( $pls_fp, "pls" );
		}
		else
		{
			echo "NOT a m3u or pls file!<br>";
		}
	}   
} 
if( ! empty( $stream ))
{
	$stream = split( $config["song_separator"], $stream );
	for( $i = 0; $i < sizeOf( $stream ); $i++ )
	{
		if( preg_match( "/^(ftp|http):\/\/.*?\.(m3u|pls)/i", $stream[$i] ))
		{
			$add = readFileOverHTTP( $fp, $stream[$i] );
		}
		// This requires the cURL hooks, probably not that hard to implement, though it is
		// another dependency, and more problem where 0 people will probably use.
		else if( preg_match( "/^https:\/\/.*?\.(m3u|pls)/", $stream[$i] ))
		{
			echo "HTTPS protocol downloads are not yet implemented.";
		}
		else if( preg_match( "/^[a-z]*:\/\//", $stream[$i]) && ! preg_match( "/^file:/", $stream[0] ))
		{
			if( strcmp( ".m3u", $stream[$i]) == "0" )
			{
				$pls_fp = fopen( $stream[$i], "r" );
				$add = postStream( $pls_fp, "m3u" );
			}
			else if( strcmp( ".pls", $stream[$i]) == "0" )
			{
				$pls_fp = fopen( $stream[$i], "r" );
				$add = postStream( $pls_fp, "pls" );
			}
			else
			{
				$command = "add";
				$add[$i]=$stream[$i];
			}
		}
		else
		{
			echo "Doesn't appear to be a url<br>";
		}
	}
}
else if( ! empty( $add_all ) && count( $add_all ) > "0" )
{
	$add = explode($config["song_separator"],$add_all);
}

if( ! empty( $add ) && count( $add ) > "0" )
{
	$str = "command_list_begin\n";
	for( $i=0; $i < count( $add ); $i++ )
	{
		$str .= "add \"{$add[$i]}\"\n";
	}
	fputs( $fp, $str . "command_list_end\n" );
	initialConnect( $fp );
}
// End of POST information


// This will extract the needed GET/POST variables
$crop = isset( $_REQUEST["crop"] ) ? $_REQUEST["crop"] : "";
$time = isset( $_REQUEST["time"] ) ? $_REQUEST["time"] : "";

$status = getStatusInfo( $fp );

if( isset( $status["error"] ))
{
	echo "Error:&nbsp;{$status["error"]}<br>\n";
}

if( strcmp( $crop, "yes" ) == "0" )
{
        crop( $fp, $status["song"], $status["playlistlength"] );

	// Since status changes after crop, we need to refresh the status
	$status = getStatusInfo( $fp );
}

if( isset( $status["state"] ))
{
	$repeat = $status["repeat"];
	$random = $status["random"];
	$xfade = $status["xfade"];

	// STATUSBAR Begin: Top playlist_body
	echo "<!-- Begin the Top of the first table, Should only display the status and refresh -->";
        echo "<table summary=\"Status &amp; Refresh\" cellspacing=2 bgcolor=\"{$colors["playing"]["title"]}\">";
	echo "<tr valign=\"middle\"><td>";

 	// The global table tags begin here. This is code to make the border, this really is a hack but improves looks quite a bit
	echo "<table summary=\"Border Table Hack\" align=\"center\" bgcolor=\"{$colors["playing"]["title"]}\">";
	echo "<tr>";#<td width=\"100%\">";

	echo "<b>";

 	if( isset( $status["updating_db"] ))
	{
		echo "<small>";
	}
	if( strcmp( $status["state"],"play") == "0")
        {
	        echo "Playing";
	}
        else if( strcmp( $status["state"], "stop" ) == "0")
	{
	        echo "Stopped";
	}
	else if( strcmp( $status["state"], "pause") == "0")
	{
	        echo "Paused";
	}
 	if( isset( $status["updating_db"] ))
	{
	        echo "&nbsp;/&nbsp;Updating</small>";
	}
	echo "</b>";
	echo "<small>";
	echo "&nbsp;(<a title=\"Refresh the Playlist Window\" href=\"index.php?body=playlist&amp;server=$server&amp;hide=$hide&amp;show_options=$show_options\">refresh</a>)";
	echo "</small></td></tr></table>";
	// STATUSBAR Begin: End playlist_body

	if( strcmp( $status["state"], "play" ) == "0" || "0" == strcmp( $status["state"], "pause" ))
	{
		$num = $status["song"];
		$songid = $status["songid"];
		$time = split( ":", $status["time"] );

		// SONG INFO Begin: Second table from top
		$song_info = getPlaylistInfo( $fp, $num );
		echo "<table summary=\"Current Song Information\" cellspacing=0 bgcolor=\"{$colors["playing"]["body"]}\" cellpadding=0>";
		echo "<tr>";
		echo "<td align=\"{$config["playlist_align"]}\">";
		echo "<a title=\"Jump to the Current Song\" href=#$num>";

		// This is in info2html.php
		echo songInfo2Display( $song_info[0], $config );
		echo "</a><br>";

	        // Begin The Time Remaining/Time Elapsed
	        if( strcmp( $config["time_left"], "yes" ) == "0")
		{
		        $time_min = (int)( ( $time[1] - $time[0] ) / 60 );
			$time_sec = (int)( ( $time[1] - $time[0] ) % 60);
		}
		else
		{
		        $time_min = (int)( $time[0] / 60 );
			$time_sec = (int)( $time[0] % 60 );
		}

		if( $time_sec < "0" )
		{
		        $time_sec*=-1;
			$time_min = "-$time_min";
		}
		else if( $time_sec < "10" )
		{
			$time_sec = "0$time_sec";
		}

	        echo "($time_min:$time_sec";

		// Begin the Total Time
		$time_min = (int) ( $time[1] / 60 );
		$time_sec = (int) ( $time[1] - $time_min * 60 );
		if( $time_sec < "10" )
	        {
		        $time_sec = "0$time_sec";
		}

		if( ! ( $time_min == "0" && $time_sec == "00" ))
		{
			echo "/$time_min:$time_sec";
		}
		echo ")&nbsp;";

		// We don't wanna hear if a bitrate is at 0 kbps
		if( $status["bitrate"] > "0" )
		{
		          echo "[{$status["bitrate"]} kbps]";
		}

		echo "</td></tr></table>";

		echo "<!-- Begin Seek Bar -->";
		echo "<table summary=\"Seek Bar\" align=\"center\" cellspacing=0 bgcolor=\"{$colors["playing"]["body"]}\" cellpadding=0>";
		echo "<tr><td align=\"left\" width=\"5%\"></td>";

		$col=$colors["time"]["background"];

		// Remove the seek bar if it's a stream
		if( $time[1] > "0" ) {
			$time_div = 4;
			for( $i="0"; $i < round( 100 / $time_div ); $i++ )
			{
				// This is for the seekbar status
				$time_perc = $time[0] * 100 / $time[1];

				if( $i >= ( round( $time_perc / $time_div ) - 1 ) && $i <= ( round( $time_perc / $time_div ) + 1 ))
				{
					$col = $colors["time"]["foreground"];
				}

				$seek = round( $i * $time_div * $time[1] / 100 );
				$min = (int)( $seek / 60 );
				$sec = $seek - $min * 60;

				if( $sec < "10")
				{
					$sec = "0" . $sec;
				}

				echo "<td border=0 width=8 height=8 bgcolor=\"$col\">";
				if( $commands["seekid"] == "1" )
				{
					echo "<a href=\"index.php?body=playlist&amp;server=$server&amp;hide=$hide&amp;show_options=$show_options&amp;command=seekid&amp;arg=$songid&amp;arg2=$seek\"";
					echo "title=\"$min:$sec\">";
				}
				echo "<img alt='Seek to $min:$sec' border=0 width=8 height=8 src=transparent.gif>";
				if( $commands["add"] == "1" )
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
	else
	{
		$num = "-1";
	}
}

// crossfade | random | repeat (at bottom of file)
echo "<table summary=\"Crosfade | random | repeat\"  align=\"{$config["playlist_align"]}\" cellspacing=0 bgcolor=\"{$colors["playing"]["body"]}\" cellpadding=0>";
echo "<tr><td align=\"{$config["playlist_align"]}\"><small>";

if( $commands["crossfade"] == "1" )
{
	if( $xfade == "0" )
	{
		echo "<a title=\"Set Crossfade to {$config["crossfade_seconds"]} Seconds\" ";
		echo "href=\"index.php?body=playlist&amp;server=$server&amp;hide=$hide&amp;show_options=$show_options&amp;command=crossfade&amp;arg=";
		echo $config["crossfade_seconds"]*(int)(!$xfade) . "\">crossfade</a>";
	}
	else
	{
		echo "<a title=\"Remove Crossfade\" class=\"green\" ";
		echo "href=\"index.php?body=playlist&amp;server=$server&amp;hide=$hide&amp;show_options=$show_options&amp;command=crossfade&amp;arg=0\">crossfade</a>";
	};
}
else
{
	if( $xfade == "0" )
	{
		echo "crossfade";
	}
	else
	{
		echo "<a title=\"Remove Crossfade\" class=\"green\" href=\"index.php?body=playlist&amp;server=$server&amp;hide=$hide&amp;show_options=$show_options\">crossfade</a>";
	}
}

echo "&nbsp;|&nbsp;";

if( $commands["random"] == "1" )
{
	if( $random == "0" )
	{
		echo "<a title=\"Turn Random On\" href=\"index.php?body=playlist&amp;server=$server&amp;hide=$hide&amp;show_options=$show_options&amp;command=random&amp;arg=";
		echo (int)(!$random) . "\">random</a>";
	}
	else
	{
		echo "<a title=\"Turn Random Off\" class=\"green\"  href=\"index.php?body=playlist&amp;server=$server&amp;hide=$hide&amp;show_options=$show_options&amp;command=random&amp;arg=";
		echo (int)(!$random) . "\">random</a>";
	}
}
else
{
	if( $random == "0" )
	{
		echo "random";
	}
	else
	{
		echo "<a title=\"Turn Random Off\" class=\"green\"  href=\"index.php?body=playlist&amp;server=$server&amp;hide=$hide&amp;show_options=$show_options\">random</a>";
	}
}

echo "&nbsp;|&nbsp;";

if( $commands["repeat"] == "1" )
{
	if( $repeat == "0" )
	{
		echo "<a title=\"Turn Repeat On\" href=\"index.php?body=playlist&amp;server=$server&amp;hide=$hide&amp;show_options=$show_options&amp;command=repeat&amp;arg=";
		echo (int)(!$repeat) . "\">repeat</a>";
	}
	else
	{
		echo "<a title=\"Turn Repeat Off\" class=\"green\" href=\"index.php?body=playlist&amp;server=$server&amp;hide=$hide&amp;show_options=$show_options&amp;command=repeat&amp;arg=";
		echo (int)(!$repeat) . "\">repeat</a>";
	}
}
else
{
	if( $repeat == "0" )
	{
		echo "repeat";
	}
	else
	{
		echo "<a title=\"Turn Repeat Off\" class=\"green\" href=\"index.php?body=playlist&amp;server=$server&amp;hide=$hide&amp;show_options=$show_options\">repeat</a>";
	}
}

// The global table tags end here
echo "</td></tr></table>";

echo "</td></tr></table>";

// Begin [<<][Play][>>][| |][Stop] Table
echo "<table summary=\"[<<][Play][>>][| |][Stop]\" align=\"{$config["playlist_align"]}\" cellspacing=1 bgcolor=\"{$colors["playing"]["title"]}\" cellpadding=0>";
echo "<tr>";
echo "<!-- Cannot correctly space 'nowrap' td's -->";
echo "<td align=\"{$config["playlist_align"]}\" nowrap>";

if( ( strcmp( $status["state"], "play" ) == "0" && $commands["play"] == "1" ) || strcmp( $status["state"], "pause" ) == "0" && $commands["pause"] == "1" )
{
	echo $display["playing"]["prev"]["active"];
}
else
{
	echo $display["playing"]["prev"]["inactive"];
}

if(strcmp( $config["play_pause"], "yes" ) == "0" )
{
	if( strcmp( $status["state"], "play" ) == "0" )
	{
		if( $commands["pause"] == "0" )
		{
			echo $display["playing"]["pause"]["inactive"];
		}
		else
		{
			echo $display["playing"]["pause"]["active"];
		}
	}
	else
	{
		if( $commands["play"] == "1" && $status["playlistlength"]>0)
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
	if( $commands["pause"] == "1" && strcmp( $status["state"], "play" ) == "0" )
	{
		echo $display["playing"]["play"]["inactive"];
		echo $display["playing"]["pause"]["active"];
	}
	else if( $commands["play"] == "1" && ( strcmp( $status["state"], "pause" ) == "0" || ( strcmp( $status["state"], "stop" ) == "0" && $status["playlistlength"] > "0" )))
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

if( ( strcmp( $status["state"], "play") == "0" || strcmp( $status["state"], "pause") == "0" ) && $commands["next"] == "1" )
{
	echo $display["playing"]["next"]["active"];
}
else
{
	echo $display["playing"]["next"]["inactive"];
}

if( ( strcmp( $status["state"],"play") == "0" || strcmp( $status["state"], "pause" ) == "0" ) && $commands["stop"] == "1" && $status["playlistlength"]>0)
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
if( strcmp( $config["playlist_align"], "center" ))
{
	echo "<br>";
}

/* Begin Volume Display */
if( $status["volume"] >= "0" && strcmp( $config["display_volume"], "yes") == "0" )
{

	echo "<table summary=\"Volume\" cellspacing=2 bgcolor=\"{$colors["volume"]["title"]}\">";
	echo "<tr>";
	echo "<!-- Cannot correctly space 'nowrap' td's -->";
	echo "<td align=\"center\"><b>Volume</b></td>";
	echo "<td></td>";
	/* Begin Volume Bar */
	$vol_div = "1";
	echo "<td valign=\"middle\" align=\"center\">";
	if( $status["volume"] == "0" )
	{
		echo "<";
	}
	else if ( $commands["setvol"] == "1")
	{
		echo "<a title=\"Decrease Volume by {$config["volume_incr"]}%\" ";
		echo "href=\"index.php?body=playlist&amp;server=$server&amp;hide=$hide&amp;show_options=$show_options&amp;command=setvol&amp;arg=";
		echo ($status["volume"] - $config["volume_incr"]) . "\"><</a>";
	}
	echo "</td>";
	echo "<td valign=\"middle\" align=\"center\">";
	echo "<!-- This table in a table is required for correct rendering -->";
	echo "<!-- Begin Seek Bar -->";

	// Hopefully this, in the future turns into a gd rendered png image, this is a horrible way to be doing things!
	echo "<table summary=\"Volume Hack\" cellspacing=0 cellpadding=0>";
	for( $i=0; $i < round( $status["volume"]/$vol_div ); $i++ )
	{
		echo "<td width=5 bgcolor=\"{$colors["volume"]["foreground"]}\" height=8></td>";
	}
	for (; $i < round( 100/$vol_div ); $i++ )
	{
		echo "<td width=5 bgcolor=\"{$colors["volume"]["background"]}\"></td>";
	}
	echo "<!-- End Seek Bar -->";
	echo "</table></td>";
	echo "<td valign=\"middle\" align=\"center\">";
	if( $status["volume"] != "100" && $commands["setvol"] == "1")
	{
		echo "<a  title=\"Increase Volume by {$config["volume_incr"]}%\" ";
		echo "href=\"index.php?body=playlist&amp;server=$server&amp;hide=$hide&amp;show_options=$show_options&amp;command=setvol&amp;arg=";
		echo ($status["volume"]+$config["volume_incr"]) . "\">></a>";
	}
	else if( $status["volume"]=="100" )
	{
		echo ">";
	}
	echo "</td></tr></table>";
}

// This gives the space in between the volume bar and the playlist table/controls
echo "<br>";

if( ! $status["playlistlength"] == 0 )
{
	echo "<table summary=\"Playlist Table (Border)\" cellspacing=1 bgcolor=\"{$colors["playlist"]["title"]}\">";
	echo "<tr><td>";

	// This is for the border table
	echo "<table summary=\"Playlist Table\" cellspacing=1><tr>";
	echo "<tr valign=\"middle\"><td><b>Playlist</b>&nbsp;";
	if( strcmp( $config["playlist_option_hide"], "yes" ) == "0" )
	{
		if( $show_options == "0" )
		{
			echo "<small>(<a href=\"index.php?body=playlist&amp;server=$server&amp;hide=$hide&amp;show_options=1\">options</a>)</small></td></tr>";
		}
		else
		{
			echo "<small>(<a href=\"index.php?body=playlist&amp;server=$server&amp;hide=$hide&amp;show_options=0\">hide</a>)</small></td></tr>";
		}
	}
	echo "<tr align=\"{$config["playlist_align"]}\">";
	echo "<td nowrap align=\"{$config["playlist_align"]}\">";
	if( strcmp( $config["playlist_option_hide"], "yes" ) || $show_options == "1" )
	{
		echo "<small>";
		/* clear | crop | shuffle | save */
		if( $commands["clear"] == "1" )
		{
			echo "<a title=\"Clear the Active Playlist\" href=\"index.php?body=playlist&amp;server=$server&amp;hide=$hide&amp;show_options=1&amp;command=clear\">clear</a>";
		}
		else
		{
			echo "clear";
		}
		echo "&nbsp;|&nbsp;";
		if( $status["playlistlength"] > "1" && strcmp( $status["state"], "stop" ) && $commands["delete"] == "1" )
		{
			echo "<a title=\"Remove All Songs Except The Currently Playing Song\" ";
			echo "href=\"index.php?body=playlist&amp;server=$server&amp;hide=$hide&amp;show_options=1&amp;crop=yes\">crop</a>";
		}
		else
		{
			echo "crop";
		}
		echo "&nbsp;|&nbsp;";
		if( $commands["save"] == "1" )
		{
			echo "<a title=\"Save the Active Playlist to the Saved Playlists\" target=main href=\"index.php?body=main&amp;server=$server&amp;save=yes\">save</a>";
		}
		else
		{
			echo "save";
		}
		echo "&nbsp;|&nbsp;";
		if( $status["playlistlength"] >= "2" && $commands["shuffle"] == "1" )
		{
			echo "<a title=\"Shuffle the Active Playlist\" href=\"index.php?body=playlist&amp;server=$server&amp;hide=$hide&amp;show_options=1&amp;command=shuffle\">shuffle</a>";
		}
		else
		{
			echo "shuffle";
		}
		echo "</small></td></tr>";
	}
	echo "</table>";
	echo "<table summary=\"Playlist Content\" cellspacing=0><tr>";

	/* Display Playlist if songs exist in the current playlist */
	if( isset($status["playlistlength"] ))
	{
		printPlaylistInfo( $fp, $num, $hide, $show_options, $config["hide_threshold"], $status["playlistlength"], $config["filenames_only"], $commands, $arg, $colors["playlist"], $server, $config );
	}
	echo "</tr></table>";
}
?>
