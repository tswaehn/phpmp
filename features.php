<?php

function outputs( $fp, $host, $color, $server, $commands )
{
	$i = "-1";
	fputs( $fp, "outputs\n" );
	while( ! feof( $fp ))
	{
		$got = fgets( $fp, "1024" );
		if ( strstr ( $got, ":" ))
		{
			list( $val,$arg ) = split( ":", $got );
		}
		if ( strcmp( $val, "outputid" ) == "0")
		{
			$i++;
		}
		if ( strncmp( "OK", $got, strlen( "OK" )) == "0" )
		{
			break;
		}
		if ( strncmp( "ACK", $got, strlen("ACK")) == "0")
		{
			print "$got<br>";
			break;
		}
		$outputs[$i]["$val"] = preg_replace( "/^ /", "", $arg );
	}	
	echo "<br>";
	echo "<table summary=\"Outputs\" border=0 cellspacing=1 bgcolor=\"" . $color["title"] . "\" width=\"100%\">";
	echo "<tr><td nowrap><b>Sound outputs for $host</b></td></tr>";
	echo "<tr><td>";
	echo "<table summary=\"Outputs\" border=0 cellspacing=1 bgcolor=\"" . $color["body"][$i%2] ."\" width=\"100%\">";
	for( $i = "0"; $i < sizeOf( $outputs ); $i++ )
	{
		echo "<tr bgcolor=" . $color["body"][$i%2] . "><td nowrap>";
		if( ( ( $outputs[$i]["outputenabled"]%2 ) - 1 ) && $commands["enableoutput"] == "1" )
		{
			echo "[<a title=\"Enable this output\"  href=index.php?body=main&amp;feature=outputs&amp;server=$server&amp;command=enableoutput&amp;arg=$i>enable</a>]";
		}
		else if( $commands["disableoutput"] == "1")
		{
			echo "[<a title=\"Disable this output\" href=index.php?body=main&amp;feature=outputs&amp;server=$server&amp;command=disableoutput&amp;arg=$i>disable</a>]</td>";
		}
		echo "<td width=\"100%\">&nbsp;" . $outputs[$i]["outputname"] . "</td></tr>";
	}

	echo "</table>";
	echo "</td></tr>";
	echo "</table>";
}

function login($fp, $default_sort, $color, $server, $arg, $dir, $remember)
{
	$dir_url = rawurlencode( $dir );

	echo "<br>";
	echo "<form target=_top action=\"index.php\" method=post>";
	echo "<table summary=\"Login\" border=0 cellspacing=1 bgcolor=\"" . $color["title"] . "\" width=\"100%\">";
	echo "<tr><td><b>Password</b></td></tr>";
	echo "<tr bgcolor=\"" . $color["body"] . "\"><td>";
	echo "<input type=hidden value=\"$server\" name=server>";
	echo "<input type=password name=passarg value=\"" . $arg . "\" size=20>";
	echo "<input type=hidden value=\"$dir_url\" name=dir>";
	echo "<input type=hidden value=\"$default_sort\" name=sort>";
	echo "<input type=submit value=login name=foo>";
	echo "<br>";
	echo "<small><input type=checkbox name=remember value=true>remember password</small>";
	echo "</td></tr></table>";
	echo "</form>";
}

function stats( $fp, $color, $MPDversion, $phpMpVersion, $host, $port )
{
	function secondsToDHMS( $seconds )
	{
		$days = floor( $seconds/86400 );
		$date = date( "H:i:s", mktime( 0, 0, $seconds )); 

		return "$days days, $date";
	}

	fputs( $fp, "stats\n" );
	while( ! feof( $fp ))
	{
		$got = fgets( $fp, "1024" );
		$el = strtok( $got, ":");
		if( strncmp( "OK", $got, strlen( "OK" )) == "0" )
		{
			break;
		}
		if( strncmp( "ACK", $got, strlen( "ACK" )) == "0")
		{
			print "$got<br>";
			break;
		}
		$got = strtok( "\0" );
		$stats[$el] = preg_replace( "/^ /", "", $got );
	}

	$statistics = array
		(
			"Artists" => $stats["artists"],
			"Albums" => $stats["albums"],
			"Songs" => $stats["songs"],
			"Uptime" => secondsToDHMS( $stats["uptime"] ),
			"Play Time" => secondsToDHMS( $stats["playtime"] ),
			"MPD Version" => $MPDversion,
			"phpMp Version" => $phpMpVersion,
			"Database Updated" => date( "F j, Y, g:i a", $stats["db_update"] ),
			"Total Database Play Time" => secondsToDHMS( $stats["db_playtime"] )
		); 

	/* Begin Stats Form */
	echo "<br>";
	echo "<table summary=\"Statistics\" border=0 cellspacing=1 bgcolor=\"" . $color["title"] . "\" width=\"100%\">";
	echo "<tr><td nowrap><b>Statistics for $host:$port</b></td></tr>";
	echo "<tr><td>";
	echo "<table summary=\"Statistics\" border=0 cellspacing=1 bgcolor=\"" . $color["body"][1] ."\" width=\"100%\">";

	$j=0;
	foreach( $statistics as $key => $value )
	{
		for( $i = "0"; $i < sizeof( $statistics ); $i++ )
		{
			echo "<tr bgcolor=\"" . $color["body"][$j%2] . "\"><td><b>$key:</b>&nbsp;$value</td></tr>";
			break;
		}
		$j++;
	}

	echo "</table></td></tr><table>";
}

function stream( $server, $color, $feature, $server_data, $song_seperator )
{
	echo "<br>";

	echo "<form action=index.php? target=playlist method=get>";
	echo "<table summary=\"Add Stream\" border=0 cellspacing=1 bgcolor=\"" . $color["title"] . "\" width=\"100%\">";
	echo "<tr><td><b>Add Stream</b></td></tr>";
	echo "<tr bgcolor=\"" . $color["body"][0] . "\"><td>";
	echo "<input type=hidden value=\"playlist\" name=body>";
	echo "<input type=hidden value=$server name=server>";
	echo "<input type=input name=stream size=40>";
	echo "<input type=submit value=Add name=foo>";
	echo "</td></tr></table></form>";

	echo "<br>";

	echo "<form enctype=\"multipart/form-data\" action=\"index.php?\" target=\"playlist\" method=\"post\">";
	echo "<table summary=\"Load Stream From Playlist\" border=\"0\" cellspacing=\"1\" bgcolor=\"" . $color["title"] . "\" width=\"100%\">";
	echo "<tr><td><b>Load Stream From Playlist</b></td></tr>";
	echo "<tr bgcolor=\"" . $color["body"][0] . "\"><td>";
	echo "<input type=\"hidden\" value=\"playlist\" name=\"body\">";
	echo "<input type=\"hidden\" value=\"$server\" name=\"server\">";
	echo "<input type=\"file\" name=\"playlist_file[]\" size=30>";
	echo "&nbsp;";
	echo "<input type=\"submit\" value=\"Load\" name=\"foo\"><br>";
	echo "</td></tr></table></form>";

	echo "<br>";
	
	if( strcmp( $feature,"stream" ))
	{
		$k=0;
		for( $i = "0"; $i < sizeOf( $server_data ); $i++ )
		{
			if( isset ( $server_data[($i+1)]["server_name"] ))
			{
				if( strcmp( $server_data[$i]["server_name"], $server_data[($i+1)]["server_name"] ))
				{
					$k++;
				}
			}
		}
		echo "<table summary=\"Streams\" border=\"0\" cellspacing=\"1\" bgcolor=\"" . $color["title"] . "\" width=\"100%\">";
		echo "<tr><td>";
		echo "<table summary=\"Streams\" border=\"0\" cellspacing=\"1\" bgcolor=\"" . $color["title"] . "\" width=\"100%\">";
		if( strcmp( $feature, "stream-icy" ) == "0" )
		{ 
			echo "<tr><td><b>Icecast / Oddcast Streams</b>";
		}
		else if( strcmp( $feature, "stream-shout" ) == "0" )
		{
			echo "<tr><td><b>Shoutcast Streams</b>";
		}
		echo "&nbsp;<small>(<a title=\"Hide the streams table\" href=\"index.php?body=main&amp;server=$server&amp;feature=stream\" target=main>hide</a>)";
		echo "&nbsp;(<a title=\"Refresh streams table\" href=\"index.php?body=main&amp;server=$server&amp;feature=$feature\" target=main>refresh</a>)</small></td>";
		echo "<td align=\"right\"><small><b>Found $k unique results</b></small></td></tr>";
		echo "<tr><td>";
		echo "</table>";
		echo "<table summary=\"Statistics\" border=0 cellspacing=1 bgcolor=\"" . $color["body"][1] ."\" width=\"100%\">";

		$j=2;
		$k=0;
		for( $i = "0"; $i < sizeOf( $server_data ); $i++ )
		{
			echo "<tr bgcolor=\"" . $color["body"][$k%2]  . "\"><td>";
			echo "&nbsp;<a title=\"Add this stream to your playlist\" target=\"playlist\" href=\"index.php?body=playlist&amp;stream=";
			echo rawurlencode( $server_data[$i]["listen_url"] );

			while( strcmp( $server_data[$i]["server_name"], $server_data[($i+1)]["server_name"] ) == "0" )
			{
				$i++;
				echo $song_seperator . rawurlencode( $server_data[$i]["listen_url"] );
			}

			echo "\">" . trim( $server_data[$i]["server_name"] ) . "</a></td></tr>";
			$k++;
		}
	}
	else
	{
		echo "<table summary=\"Icecast/Oddcast Streams\" border=\"0\" cellspacing=\"1\" bgcolor=\"" . $color["title"] . "\" width=\"100%\">";
		echo "<tr><td>";
		echo "<table summary=\"Icecast/Oddcast Streams\" border=\"0\" cellspacing=\"1\" bgcolor=\"" . $color["title"] . "\" width=\"100%\">";
		echo "<tr><td><b>Icecast / Oddcast Streams</b>";
		echo "&nbsp;<small>(<a title=\"Show a table of current Icecast / Oddcast streams\" href=\"index.php?body=main&amp;server=$server&amp;feature=stream-icy\" target=main>show</a>)</small></td>";
		echo "</tr></table></td></tr></table>";

		echo "<br>";

		echo "<table summary=\"Shoutcast Streams\" border=\"0\" cellspacing=\"1\" bgcolor=\"" . $color["title"] . "\" width=\"100%\">";
		echo "<tr><td>";
		echo "<table summary=\"Shoutcast Streams\" border=\"0\" cellspacing=\"1\" bgcolor=\"" . $color["title"] . "\" width=\"100%\">";
		echo "<tr><td><b>Shoutcast Streams</b>";
		echo "&nbsp;<small>(<a title=\"Show a table of current Shoutcast streams\" href=\"index.php?body=main&amp;server=$server&amp;feature=stream-shout\" target=main>show</a>)</small></td>";
		echo "</tr></table></td></tr></table>";
	}
}

function server( $servers, $host, $port, $color, $config )
{
	echo "<br>";
	echo "<table summary=\"Server Selection\" border=0 cellspacing=1 bgcolor=\"" . $color["title"] . "\" width=\"100%\">";
	echo "<tr><td><b>Servers</b></td></tr>";
	echo "<tr bgcolor=\"" . $color["body"] . "\"><td>";

	// This is for those who utilize multiple MPD servers setup in phpMp's config
 	echo '<form method=post action="index.php" target=_top>';
	$sel1 = '<select name=server>';
	$sel2 = "";
	for( $x = "0"; $x < sizeof( $servers ); $x++ )
	{
		// add the server to select box, if the 3rd field is not blank use that,
		// otherwise default to the host name as the displayed server
		// If the host is the current host, place it at the top, otherwise put it under that
		if( strcmp( $servers[$x][0], $host ) == "0" && strcmp( $servers[$x][1], $port ) == "0" )
		{
			$sel1 .= '<option selected' . ' value=' . $x . ">" . $servers[$x][2] . "</option>";
		}
		else
		{
			$sel2 .= '<option value=' . $x . '>' . ( ( $servers[$x][2] != '' ) ? $servers[$x][2] : $servers[$x][0] ) . '</option>';
		}
	}
	$sel2.= '</select>&nbsp';
	echo $sel1 . $sel2 . '<input align="right" type=submit value="Switch Server">';
	echo "</form></td></tr></table>";
}

function search( $fp, $color, $config, $dir, $search, $find, $arg, $sort, $server, $addperm, $feature )
{
	$sort = $config["default_sort"];
	$sort_array = split( ",", $sort );
	$dir_url = rawurlencode( $dir );

	echo "<br>";
	echo "<form action=index.php? method=get>";
	echo "<table summary=\"Search\" border=0 cellspacing=1 bgcolor=\"" . $color["title"] . "\" width=\"100%\">";
	echo "<tr><td><b>Search</b></td></tr>";
	echo "<tr bgcolor=\"" . $color["body"][1] . "\">";
	echo "<td>";
	echo "<select name=search>";
	function localPrintFileNameOption( $which_search )
	{
		if( ! empty( $search ))
		{
			$which_search=$search;
		}
		else if( ! empty( $find ))
		{
			$which_search=$find;
		}
		if( strcmp( $which_search, "filename" ) == "0" )
		{
			echo "<option value=\"filename\" selected>file name</option>";
		}
		else
		{
		        echo "<option value=\"filename\">file name</option>";
		}
	}

	if( strcmp( $config["filenames_only"], "yes" ) == "0")
	{
		localPrintFileNameOption( $search );
	}

	if( strcmp( $search, "title" ) == "0" || strcmp( $find, "title" ) == "0")
	{
		echo "<option selected>title</option>";
	}
	else
	{
	        echo "<option>title</option>";
	}

	if( strcmp( $search, "date") =="0" || strcmp($find,"date") == "0" )
	{
		echo "<option selected>date</option>";
	}
	else
	{
	        echo "<option>date</option>";
	}

	if( strcmp( $search, "genre" ) == "0" || strcmp( $find, "genre" ) == "0" )
	{
		echo "<option selected>genre</option>";
	}
	else
	{
	        echo "<option>genre</option>";
	}

	if( strcmp( $search, "album" ) == "0" || strcmp( $find, "album" ) == "0" )
	{
		echo "<option selected>album</option>";
	}
	else
	{
		echo "<option>album</option>";
	}

	if ( strcmp( $search, "artist") == "0" || strcmp( $find, "artist" ) == "0" )
	{
		echo "<option selected>artist</option>";
	}
	else
	{
	        echo "<option>artist</option>";
	}

	if( strcmp( $config["filenames_only"], "yes" ))
	{
	        localPrintFileNameOption( $search );
	}
	echo "</select>";
	echo "<input type=hidden value=\"main\" name=body>";
	echo "<input type=hidden value=\"search\" name=feature>";
	echo "<input type=hidden value=\"$server\" name=server>";
	echo "&nbsp;&nbsp;<input name=arg value=\"$arg\" size=40>";
	echo "<input type=hidden value=\"$dir\" name=dir>";
	echo "<input type=hidden value=\"$sort\" name=sort>";
	echo "<input type=submit value=Search name=foo>";
	echo "</td></tr></table>";
	echo "</form>";
	if( isset( $search ) && ! empty( $arg ))
	{
		$lsinfo = getLsInfo( $fp, "search $search \"$arg\"\n" );
	}
	else if( isset( $find ) && ! empty( $arg ))
	{
		$lsinfo = getLsInfo( $fp, "find $find \"$arg\"\n" );
	}
	if( isset( $lsinfo ))
	{
		list( $mprint, $mindex, $add_all ) = lsinfo2musicTable( $lsinfo, $sort, $dir_url, $sort_array, $config, $color["body"], $server, $addperm );
	}
	$arg_url = rawurlencode( $arg );
	if ( isset( $mprint ))
	{
		$local_url = "index.php?body=main&amp;feature=search&amp;search=$search&amp;arg=$arg_url&amp;dir=$dir_url";
		printMusicTable( $config, $color, $sort_array, $server, $mprint, $local_url, $add_all, $mindex, $dir, $addperm, $feature );
	}

}
?>