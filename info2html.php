<?php
include "sort.php";

/*******************************************************************************************************************************#
#																#
#	lsinfo2directoryTable: This takes the information from lsinfo readies the information for printDirectoryTable()		#
#																#
#*******************************************************************************************************************************#
#																#
#	$dcount => Directory Count												#
#	$dirstr => Directory Name												#
#	$dirindex => Directory Index												#
#																#
#*******************************************************************************************************************************/

function lsinfo2directoryTable( $lsinfo, $server, $sort, $addperm, $color )
{
	$count = count( $lsinfo["dir"] );
	if( $count != "0" )
	{
		usort( $lsinfo["dir"], "strcasecmp" );
	}

	$dic = 0;
	for( $i = "0"; $i < $count; $i++ )
	{
       		$dirstr = basename( $lsinfo["dir"][$i] );
		$full_dir = rawurlencode( $lsinfo["dir"][$i] );
		$print[$i] = "<tr bgcolor=\"{$color[ ($i%2) ]}\"><td>";
		$fc = strtoupper( mbFirstChar( $dirstr ));
		if ($dic == "0" || $index[ ($dic-1) ]!=$fc)
		{
			$index[ $dic ] = $fc;
			$print[ $i ].= "<a name=d{$index[ $dic ]}></a>";
			$dic++;
		}

		// If updating show the update links, otherwise show add links
		if( $addperm == "1" )
		{
			$print[$i].= "[<a title=\"Add the $dirstr Directory\" href=\"index.php?body=playlist&amp;server=$server&amp;command=add&amp;arg=$full_dir\" target=playlist>add</a>]&nbsp";
		}
		$print[$i].= "<a title=\"Browse the $dirstr Directory\" href=\"index.php?body=main&amp;server=$server&amp;sort=$sort&amp;dir=$full_dir\">$dirstr</a></td></tr>";
	}
	if( ! isset( $index ))
	{
		$index = array();
	}
	if( ! isset( $print ))
	{
		$print = array();
	}
	$dir["count"] = $count;
	$dir["index"] = $index;
	$dir["print"] = $print;

	return $dir;
}

function printSavePlaylistTable( $server, $color )
{
	echo "<!-- Begin printSavePlaylistTable -->";
	echo "<br>";
	echo "<form action=index.php method=get>";
	echo "<table summary=\"Save Playlist\" cellspacing=1 bgcolor=\"{$color["title"]}\">";
	echo "<tr><td><b>Save Playlist</b></td></tr>";
	echo "<tr bgcolor=\"{$color["body"][0]}\"><td>";
	echo "<input name=arg size=40>";
	echo "<input type=hidden name=body value=main>";
	echo "<input type=hidden name=server value=\"$server\">";
	echo "<input type=hidden value=save name=command>";
	echo "<input type=submit value=save name=foo>";
	echo "</td></tr></table></form>";
	echo "<!-- End printSavePlaylistTable -->";
}

/*
 begin printDirectoryTable
 $dcount -> is the number of directories
 $dprint -> array, has dcount elements, just do print $dprint[$i]
           to print output for that directory, it was formatted
	    in lsinfo2directoryTable (this parses input from 
           lsinfo and make the $dprint's for output)
 $dindex -> these are the links etc for the index elements point too
 $printIndex -> function that takes $dinex and prints all the links
               for the indexes
 */
function printDirectoryTable( $info, $dir, $sort, $server, $addperm, $color )
{
	extract( $info );
	if( $count != "0" )
	{
		echo "<!-- Begin printDirectoryTable -->";
		echo "<br>";
	        echo "<table summary=\"Directory Border\" cellspacing=1 bgcolor=\"{$color["title"]}\">";
		echo "<tr><td nowrap><b>Directories</b>";
	        printIndex($index,"","d");
		if( $addperm == "1" )
		{
			echo "&nbsp;<small>(<a title=\"Add All Directories and Music\" target=playlist href=index.php?body=playlist&amp;server=$server&amp;command=add&amp;arg=$dir>add all</a>)</small>";
		}
		echo "</td></tr>";  
		echo "<tr><td>";
		echo "<table summary=\"Directory\" cellspacing=1 bgcolor=\"{$color["body"][1]}\">";

		for( $i=0; $i < $count; $i++)
                {
                        echo $print[ $i ];
                }
		echo "</table>";
		echo "</td></tr></table>";
		echo "<!-- End printDirectoryTable -->";
	}
}

function lsinfo2playlistTable( $lsinfo, $sort, $delete, $server, $loadperm )
{
	$pic = 0;
	$count = count( $lsinfo["playlist"] );

	if($count)
	{
	        usort( $lsinfo["playlist"], "strcasecmp" );
	}
	for( $i=0; $i < $count; $i++ )
	{
		$dirstr = basename( $lsinfo["playlist"][$i] );

		// TODO: find out why we have to double-encode here
		$dirurl = rawurlencode( $dirstr );
		$dirurl = rawurlencode( $dirurl );

		// Create the Playlist Index
		$fc = strtoupper( mbFirstChar( $dirstr ));
		if( $pic == "0" || ( $index[ $pic-1 ] != $fc ))
		{
			$index[ $pic ] = $fc;
			$foo = $index[ $pic ];
			$pic++;
			$print[$i] = "<a name=p$foo></a>";
		}
		else
		{
			$print[$i] = "";
		}

		if( strcmp( $delete, "yes" ) == "0" )
		{
		        $print[$i] .= "[<a title=\"Remove playlist $dirstr\" ";
			$print[$i] .= "href=\"index.php?body=main&amp;server=$server&amp;sort=$sort&amp;command=rm&amp;arg=$dirurl\">del</a>]&nbsp;";
		}

		if( $loadperm == "1" )
		{
			$print[$i] .= "<a title=\"Load the playlist $dirstr\" ";
			$print[$i] .= "target=\"playlist\" href=\"index.php?body=playlist&amp;server=$server&amp;command=load&amp;arg=$dirurl\">$dirstr</a>&nbsp;";
		}
		else
		{
			$print[$i] .= "$dirstr&nbsp;";
		}
	}
	if( ! isset( $count ))
	{
		$count = "";
	}
	if( ! isset( $print ))
	{
	        $print = array();
	}
	if( ! isset( $index ))
	{
	        $index = array();
	}
	$pinfo["count"] = $count;
	$pinfo["index"] = $index;
	$pinfo["print"] = $print;
	return $pinfo;
}

function display_time( $seconds )
{
	if ($seconds > "60")
	{
		$min = floor( $seconds / 60 );
		$sec = ( $seconds - ( $min * 60 ));
		return sprintf( "%d:%02d", $min, $sec );
	}
	else
	{
		return sprintf( "0:%02d", $seconds );
	} 
}

function splitTagFile( $lsinfo, $config )
{
	$tagged = array();
	$untagged = array();

	for( $i="0"; $i < count( $lsinfo ); $i++ )
	{
		if( strcmp( $config["filenames_only"], "yes" ) == "0" || empty( $lsinfo[$i]["Title"] ))
		{
			$untagged[] = $lsinfo[$i];
		}
		else
		{
			$tagged[] = $lsinfo[$i];
		}
	}

	return( array( $tagged, $untagged ));
}

function streamxml2musicTable( )
{
	
}

function fileinfo2musicTable( $info, $dir_url, $config, $color, $server, $addperm, $sort_array, $sort )
{
	$count = count( $info );
	$dir_url = rawurlencode( $dir_url );
	$index = array();
	$index_key = "mf";
	$mic = "0";
	$mprint = array();
	
        usort( $info, "fsort" );

	for ( $i = "0"; $i < $count; $i++ )
	{
		$col = $color["file"]["body"][ ( $i%2 ) ];
		$full_filename = rawurlencode( $info[$i]["file"] );
		$split_filename = basename( $info[$i]["file"] );
		$fc_filename = strtoupper( mbFirstChar( $split_filename ));

		// Create the Filename Index
		if ( $mic == "0" || $index[ ($mic-1) ] != $fc_filename )
		{
			$index[ $mic ] = $fc_filename;
			$mprint[$i] = "<a name=\"$index_key{$index[ $mic ]}\"></a>";
			$mic++;
		}
		else
		{
			$mprint[$i] = "";
		}

		if( $addperm == "1" )
		{
			$mprint[$i] = "<tr bgcolor=$col><td>$mprint[$i][<a title=\"Add this song to the active playlist\" ";
			$mprint[$i] .= "target=\"playlist\" ";
			$mprint[$i] .= "href=\"index.php?body=playlist&amp;server=$server&amp;command=add&amp;arg=$full_filename\">add</a>]</td>";
			$mprint[$i] .= "<td>$split_filename</td>";
		}
		else
		{
			$mprint[$i] = "<tr bgcolor=$col><td>$split_filename</td>";
		}

		// The <td>s here must be included inside the if() else() blocks in case the user doesn't want it displayed at all.
		if( array_search( 'Time', $config["display_fields"] ))
		{
			$mprint[$i] .= "<td>" . display_time( $info[$i]['Time'] ) . "</td>";
		}
		$mprint[$i] .= "</tr>";
	}
	// The sort bar is created here.
	$sort_bar = "<tr colspan=3 bgcolor=\"{$color["file"]["sort"]}\">";

	// This creates the column for 'Add'
	if( $addperm == "1" )
	{
		$sort_bar .= "<td width=\"1%\"></td>";
	}

	// In case 'Time' isn't part of the display fields at all
	if( array_search( 'Time', $config["display_fields"] ))
	{
		$sort_bar .= "<td>Files</td><td width=\"1%\">Time</td></tr>";
	}
	else
	{
		$sort_bar .= "<td>File</td></tr>";
	}

	$ret["count"] = $count;
	$ret["print"] = $mprint;
	$ret["index"] = $index;
	$ret["index_key"] = $index_key;
	$ret["sortbar"] = $sort_bar;
	$ret["title"] = "Untagged Music"; 
	return( $ret );
}

/***************************************************************************************#
#											#
# info2musicTable() - To ready the MPD information for printMusicTable() consumption	#
#											#
#***************************************************************************************#
#											#
# $info => this is the lsinfo information put into an array				#
#											#
#***************************************************************************************/

# TODO: AddAll() will need it's own separate function

function taginfo2musicTable( $info, $dir_url, $config, $color, $server, $addperm, $sort_array, $sort, $ordered, $url )
{
	$count = count( $info );
	if( count( $info ) == "0" )
	{
		return 0;
	}

	$dir_url = rawurlencode( $dir_url );
	$index = array();
	$index_key = "mt";
	$mic = "0";
	$mprint = array();

        usort( $info, "msort" );

	for ( $i = "0"; $i < $count; $i++ )
	{
		$col = $color["meta"]["body"][ ( $i%2 ) ];
		$full_filename = $info[$i]["file"];
		$split_filename = basename( $full_filename );
		if( isset( $info[$i][ $sort_array[0] ] ))
		{
			$fc_filename = strtoupper( mbFirstChar( $info[$i][ $sort_array[0] ] ));
		}

		$full_filename = rawurlencode( $full_filename );

		// This is where the index for the particular table is made
		// If the sort item exists in the music item, this is not the first letter that's going to be
		// added to the index, and the index before hand is not the samee

		if( strcmp( $sort_array[0], "Track" ))
		{
			if( isset( $info[$i][$sort_array[0]] ) && strlen( $info[$i][ $sort_array[0] ] ) &&
				( $mic==0 || $index[ ( $mic - 1 ) ] != $fc_filename ))
			{
				$index[$mic] = $fc_filename;
				$mprint[$i] = "<a name=\"$index_key$fc_filename\"></a>";
				$mic++;
			}
			else
			{
				$mprint[$i] = "";
			}
		}
		else
		{
			// If the desired sort item isset put it in 
			if( isset( $info[$i][ $sort_array[0] ] ))
			{
				$item = strtok( $info[$i][ $sort_array[0] ], "/" );
			}

			if( isset( $item ) && ( $mic == "0" || strcmp( $index[ ( $mic - 1 ) ], $item )))
			{
				$index[ $mic ] = $item;
				$mic++;
				$mprint[$i] = "<a name=\"$index_key$item\"></a>";
			}
			else
			{
				$mprint[$i] = "";
			}
		}
	
		if( $addperm == "1" )
		{
			$mprint[$i] = "<tr bgcolor=$col><td width=\"1%\">$mprint[$i][";
			$mprint[$i] .= "<a title=\"Add this song to the current playlist\" ";
			$mprint[$i] .= "target=\"playlist\" ";
			$mprint[$i] .= "href=\"index.php?body=playlist&amp;server=$server&amp;command=add&amp;arg=";
			$mprint[$i] .= rawurlencode($full_filename) . "\">add</a>]</td>";
		}
		else
		{
			$mprint[$i] = "<tr bgcolor=$col>";
		}
		for ( $x = 0; $x < sizeof($config["display_fields"]); $x++)
		{
			$mprint[$i] .= "<td>";

			/* 
			 * If $config["display_fields"][$x] an Album, Artist, Date or Genre make the HTML anchored to a mpd 'find' command so the 
			 * user can click anything in the Album Artist, Date or Genre fields and it will automatically search for them case sensitively
			 * Sort the known remaining tags by just echoing the sting, otherwise print config error.
			 */

			switch( $config["display_fields"][$x] )
			{
				case 'Album':
				case 'Artist':
				case 'Date':
				case 'Genre':
				{
					if( isset( $info[$i][ $config["display_fields"][$x] ] ))
					{
						$local_url = rawurlencode( $info[$i][ $config["display_fields"][$x] ] );
						$mprint[$i] .= "<a title=\"Find by this keyword\" href=\"index.php?body=main&amp;feature=search&amp;server=$server&amp;find=";
						$mprint[$i] .= strtolower( $config["display_fields"][$x] );
						$mprint[$i] .= "&amp;arg=$local_url&amp;sort=$sort&amp;dir=$dir_url\">";
						$mprint[$i] .= "{$info[$i][ $config["display_fields"][$x] ]}</a>";
					}
					else
					{
						$mprint[$i] .= $config["unknown_string"];
					}
					break;
				}

				case 'Title':
				{
					$mprint[$i] .= $info[$i][ $config["display_fields"][$x] ];
					break;
				}

				case 'Time':
				{
					if ( isset( $info[$i][ $config["display_fields"][$x] ] ))
					{
						$mprint[$i] .= display_time( $info[$i][ $config["display_fields"][$x] ] );
					}
					else
					{
						$mprint[$i] .= $config["unknown_string"];
					}
					break;
				}

				default:
				{
					if( isset( $info[$i][ $config["display_fields"][$x] ]))
					{
						$mprint[$i] .= $info[$i][ $config["display_fields"][$x] ];
					}
					else
					{
						$mprint[$i] .= "";
					}
					break;
				}
			}
			$mprint[$i] .= "</td>";
		}
	}

	// Sort bar is created here
	$sort_bar = "<tr bgcolor=\"{$color["meta"]["sort"]}\">";

	// This creates the column for 'Add'
	if( $addperm == "1" )
	{
		$sort_bar .= "<td width=0></td>";
	}

	for( $j = 0; $j < count( $config["display_fields"] ); $j++ )
	{
		// Cut this in pieces so it wouldn't wrap
		$sort_bar .= "<td>";

		if( strcmp( $config["display_fields"][$j], $sort_array[0] ) == "0" )
		{
			$sort_bar .= "<a title=\"Reverse this field\"";
			if( strcmp( $ordered, "yes" ))
			{
				$sort_bar .= " href=\"$url&amp;sort=" . pickSort($config["display_fields"][$j]) . "&amp;ordered=yes&amp;server=$server\">";
			}
			else
			{
				$sort_bar .= "href=\"$url&amp;sort=" . pickSort($config["display_fields"][$j]) . "&amp;ordered=no&amp;server=$server\">";
			}
			$sort_bar .= "<b>{$config["display_fields"][$j]}</b>";
		}
		else
		{
			$sort_bar .= "<a title=\"Sort by this field\" ";
			$sort_bar .= "href=\"$url&amp;sort=" . pickSort($config["display_fields"][$j]) . "&amp;ordered=no&amp;server=$server\">";
			$sort_bar .= $config["display_fields"][$j];
		}

		$sort_bar .= "</a></td>";
	}
	$sort_bar .= "</tr>";


	$ret["count"] = $count;
	$ret["index"] = $index;
	$ret["index_key"] = "mt";
	$ret["print"] = $mprint;
	$ret["sortbar"] = $sort_bar;
	$ret["title"] = "Tagged Music";
 	return( $ret );
}

function createAddAll( $music, $song_separator )
{
	$add_all = "";
	for( $i="0"; $i < ( count( $music ) - "1" ); $i++ )
	{
		$add_all .= addslashes( $music[$i]["file"] ) . $song_separator;
	}

	// If this function gets called without any actual files this will save from getting notices
	if( isset( $music[$i]["file"] ))
	{
		return $add_all . $music[$i]["file"];
	}
}

/* This function is used to print the index for all tables that need an index */
function printIndex( $index, $title, $anc )
{
	if( count( $index ))
	{
		echo "<!-- Begin printIndex -->";
		echo $title . " [ ";
		for ( $i = "0"; $i < count( $index ); $i++ )
		{
			echo "<a title=\"Goto the beginning of {$index[$i]}\" href=\"#$anc{$index[$i]}\">{$index[$i]}</a>&nbsp;";
		}
		echo "]";
		echo "<!-- End printIndex -->";
	}
}

/***************************************************************************************************************#
#														#
#	printMusicTable: A semi-generic function to print the musicTable in a uniform fashion			#
#		This function will probably be so generic that it will encompass all printTables		#
#														#
#***************************************************************************************************************#
#														#
#	(array) $info => This consists of all the specific information about the table, which consists of	#
#	REQUIRED: $print => This is an array of the music information, loop this in the body			#
#														#
#	OPTIONAL: $add_all => This is a URL of all addall information for the current information		#
#		  $index => This is an array of unique first characters from the music information		#
#		  $sortbar => This is the sortbar, usually enables the sorting					#
#		  $title => This is the title of the musicTable							#
#														#
#***************************************************************************************************************/

function printMusicTable( $add_all, $config, $color, $info, $altcount, $sort_array, $server, $dir, $addperm, $feature, $ordered )
{
	if( is_array( $info ))
	{
		extract( $info );
	}
	else
	{
		return;
	}

	// This is the catchall, if there's any music print it.
	if( $count > "0" )
	{
		echo "<br>";
		echo "<!-- Begin printMusicTable  -->";
		$add_all = rawurlencode( $add_all );

		echo '<form name="add_all" method="post" action="index.php" target="playlist">';
		if( ! empty( $add_all ))
		{
			echo "<input type=hidden name=\"add_all\" value=\"$add_all\">";
		}
		echo "<input type=hidden name=\"body\" value=\"playlist\">";
		echo "<input type=hidden name=\"server\" value=\"$server\">";
		echo "<table summary=\"Music Separators\" cellspacing=1 bgcolor=\"{$color["title"]}\">";
		echo "<tr><td>";
		echo "<table summary=\"Music Separators\" cellspacing=1 bgcolor=\"{$color["title"]}\">";

		echo "<a name=\"$title\"></a>";
		if( $altcount > "0" )
		{
			echo "<tr><td colspan=". ( count( $config["display_fields"] ) - 1 ) . "><b>$title</b>";
		}
		else
		{
			echo "<tr><td colspan=". ( count( $config["display_fields"] ) - 1 ) . "><b>Music</b>";
		}

		// If not sorting by 'Time' display the index, due to bugs in 'Time'/index
		if( strcmp( $sort_array[0], "Time" ) && isset( $index ))
		{
			echo printIndex( $index, "", $index_key );
		}

		if( ! empty( $add_all ))
		{
			if( strcmp( $config["use_javascript"], "yes" ) == "0" )
			{
				if( $count > 0 && $altcount > 0 )
				{
					echo "&nbsp;<small>(<a title=\"Add all songs from this music table to the active playlist\" ";
					echo "href=\"javascript:document.add_all.submit()\">add all tagged/untagged</a>)</small>";
				}
				else
				{
					echo "&nbsp;<small>(<a title=\"Add all songs from this music table to the active playlist\" ";
					echo "href=\"javascript:document.add_all.submit()\">add all</a>)</small>";
				}
			}
		}
		echo "</td>";

		if( strcmp( $feature, "search" ) == "0" || strcmp( $feature, "find" ) == "0" )
		{
			echo "<td align=right><b><small>Found $count results</small></b></td>";
		}
		echo "</tr></table>";

		echo "<table summary=\"Music\" cellspacing=1 bgcolor=\"{$color["body"][1]}\">";
		if( isset( $sortbar ))
		{
			echo $sortbar;
		}

		for( $i = "0"; $i < $count; $i++ )
		{
		        echo $print[$i];
		}
		echo "</td></tr></table>";
		echo "</td></tr></table>";
	}
	else
	{
		return;
	}
}

function printPlaylistTable( $color, $server, $info, $delete, $rmperm )
{
	extract( $info );
	if ( $count > "0" )
	{
	        // Begin table for Title & Index
		echo "<!-- Begin printPlaylistTable -->";
		echo "<br>";
		echo "<table summary=\"Playlist Title & Index\" cellspacing=1 bgcolor=\"{$color["title"]}\">";
		echo "<tr><a name=playlists></a>";
		echo "<td nowrap>";
		echo "<b>Saved Playlists</b>";
		printIndex( $index, "", "p" );
		if( strcmp( $delete, "yes" ) && $rmperm == "1" )
		{
		        echo "&nbsp;<small>(<a title=\"Goto Delete Playlist Menu\" href=\"index.php?body=main&amp;delete=yes&amp;server=$server#playlists\">delete</a>)</small>";
		}

		echo "</td></tr>";
		echo "<tr><td>";
		echo "<table summary=\"Playlist\" cellspacing=1 bgcolor=\"{$color["body"][0]}\">";

		// Begin for playlist
		for ( $i=0; $i < $count; $i++ )
		{
		        echo "<tr bgcolor=\"{$color["body"][$i%2]}\">";
			echo "<td>{$print[$i]}</td>";
			echo "</tr>";
		}
		echo "</table>";
		echo "</td></tr></table>";
	}
}

function songInfo2Display( $song_info, $config, $display )
{
	// If it's a URL don't grab it's basename
	if( preg_match( "/^[a-z]*:\/\//", $song_info["file"] ))
	{
		$song = $song_info["file"];
	}
	else
	{
		$song = basename( $song_info["file"] );
	}

	if( strcmp( $config["filenames_only"],"yes" ) && isset( $song_info["Title"] ) && strlen( $song_info["Title"] ) > "0" )
	{
		$song_display_conf = $display[0];

		for( $i = "1"; $i < count( $display ); $i++ )
		{
			$song_display_conf .= " " . $display[$i];
		}

		// This will replace all song_display_conf stuff with the actual value
		foreach( $song_info as $key => $value )
		{
			$song_display_conf = str_replace( $key, $value, $song_display_conf );
		}
		$song_display = $song_display_conf;
	}
	else if( strcmp( $config["filenames_only"], "yes") == "0" && isset( $song_info["Name"] ) && ( $song_info["Name"] > "0" ))
	{
		$song_display = $song_info["Name"];
	}
	else
	{
		// Let's not regex urls
		if( ! preg_match( "/^(http|ftp|https):\/\/.*/", $song ))
		{
			for( $i= "0"; $i < sizeOf( $config["regex"]["remove"] ); $i++ )
			{
				$song = str_replace( $config["regex"]["remove"][$i], '', $song );
			}
			if( strcmp( $config["regex"]["space"], "yes") == "0" )
			{
				$song = str_replace( '_', ' ', $song );
			}
			if( strcmp( $config["regex"]["uppercase_first"], "yes" ) == "0" )
			{
				$song = ucwords( $song );
			}
		}
		$song_display = $song;
	}
	if( $config["wordwrap"] > "0" )
	{
		$song_display = wordwrap( $song_display, $config["wordwrap"], "<br />", "1" );
	}
	return $song_display;
}
?>
