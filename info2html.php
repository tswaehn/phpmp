<?php
include "sort.php";

/*
 begin lsinfo2directoryTable

*/
function lsinfo2directoryTable( $lsinfo, $server, $sort, $dir, $addperm, $color )
{
	$dcount = count( $lsinfo["dir"] );
	if( $dcount != "0" )
	{
		usort( $lsinfo["dir"], "strcasecmp" );
	}

	$dic = 0;
	for( $i = "0"; $i < $dcount; $i++ )
	{
		// dirstr: The actual directory name
       		$dirstr = $lsinfo["dir"][$i];
		$dirss = split( "/", $dirstr );
		if ( count( $dirss ) == "0") 
		{
 			$dirss[0] = $dirstr;
		}
		$dirstr = rawurlencode( $dirstr );
		$dirss[0] = $dirss[ (count( $dirss ) - 1) ];
		$dprint[$i] = "<tr bgcolor=\"" . $color[ ($i%2) ]  . "\"><td>";
		$fc = strtoupper( mbFirstChar( $dirss[0] ));
		if ($dic == "0" || $dindex[ ($dic-1) ]!=$fc)
		{
			$dindex[ $dic ] = $fc;
			$dprint[ $i ].= "<a name=d" . $dindex[ $dic ]  . "></a>";
			$dic++;
		}

		// If updating show the update links, otherwise show add links
		if( $addperm == "1" )
		{
			$dprint[$i].= "[<a title=\"Add the " . $dirss[0]  . " Directory\" href=\"index.php?body=playlist&amp;server=$server&amp;command=add&amp;arg=$dirstr\" target=playlist>add</a>]&nbsp";
		}
		$dprint[$i].= "<a title=\"Browse the " . $dirss[0] . " Directory\" href=\"index.php?body=main&amp;server=$server&amp;sort=$sort&amp;dir=$dirstr\">$dirss[0]</a></td></tr>";
	}
	if( ! isset( $dindex ))
	{
		$dindex = array();
	}
	if( ! isset( $dprint ))
	{
		$dprint = array();
	}
	return array( $dprint, $dindex, $dcount );
}

function printSavePlaylistTable( $save, $server, $color )
{
	echo "<!-- Begin printSavePlaylistTable -->";
	echo "<br>";
	echo "<form action=index.php method=get>";
	echo "<table summary=\"Save Playlist\" cellspacing=1 bgcolor=\"" . $color["title"] . "\">";
	echo "<tr><td><b>Save Playlist</b></td></tr>";
	echo "<tr bgcolor=\"" . $color["body"][0] . "\"><td>";
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
function printDirectoryTable( $dcount, $dprint, $dindex, $dir, $sort, $server, $addperm, $color )
{
	if( $dcount != "0" )
	{
		echo "<!-- Begin printDirectoryTable -->";
		echo "<br>";
	        echo "<table summary=\"Directory Border\" cellspacing=1 bgcolor=\"" . $color["title"] . "\">";
		echo "<tr><td nowrap><b>Directories</b>";
	        printIndex($dindex,"","d");
		if( $addperm == "1" )
		{
			echo "&nbsp;<small>(<a title=\"Add All Directories and Music\" target=playlist href=index.php?body=playlist&amp;server=$server&amp;command=add&amp;arg=$dir>add all</a>)</small>";
		}
		echo "</td></tr>";  
		echo "<tr><td>";
		echo "<table summary=\"Directory\" cellspacing=1 bgcolor=\"" . $color["body"][1] . "\">";

		for( $i=0; $i < $dcount; $i++)
                {
                        echo $dprint[ $i ];
                }
		echo "</table>";
		echo "</td></tr></table>";
		echo "<!-- End printDirectoryTable -->";
	}
}

function lsinfo2playlistTable( $lsinfo, $sort, $delete, $server, $loadperm )
{
	$pic = 0;
	$pcount = count( $lsinfo["playlist"] );
	if ($pcount)
	{
	        usort( $lsinfo["playlist"], "strcasecmp" );
	}
	for ( $i=0; $i < $pcount; $i++ )
	{
		$dirstr = $lsinfo["playlist"][ $i ];
		$dirss = split( "/", $dirstr );
		if ( count( $dirss ) == "0" )
		{
			$dirss[0] = $dirstr;
		}
		$dirss[0] = $dirss[ (count( $dirss ) - "1") ];
		$dirstr = rawurlencode( $dirstr );
		$fc = strtoupper( mbFirstChar( $dirss[0] ));
		if ($pic == "0" || ($pindex[ $pic-1 ] != $fc) )
		{
			$pindex[ $pic ] = $fc;
			$foo = $pindex[ $pic ];
			$pic++;
			$pprint[$i] = "<a name=p$foo></a>";
		}
		else
		{
			$pprint[ $i ] = "";
		}
		if( strcmp( $delete, "yes" ) == "0" )
		{
		        $pprint[ $i ] .= "[<a title=\"Remove playlist $dirss[0]\"  href=\"index.php?body=main&amp;server=$server&amp;sort=$sort&amp;command=rm&amp;arg=$dirstr\">del</a>]&nbsp;";
		}
		if( $loadperm == "1" )
		{
			$pprint[ $i ] .= "<a title=\"Load the playlist $dirss[0]\" target=\"playlist\" href=\"index.php?body=playlist&amp;server=$server&amp;command=load&amp;arg=$dirstr\">$dirss[0]</a>&nbsp;";
		}
		else
		{
			$pprint[ $i ] .= "$dirss[0]&nbsp;";
		}
	}
	if ( ! isset( $pprint ))
	{
	        $pprint = array();
	}
	if ( ! isset( $pindex ))
	{
	        $pindex = array();
	}
	return array( $pprint,$pindex );
}

function display_time( $seconds )
{
	if ($seconds > "60")
	{
		$min = floor( $seconds / 60 );
		$sec = ($seconds - ( $min * 60 ));
		return sprintf( "%d:%02d", $min, $sec );
	}
	else
	{
		return sprintf( "0:%02d", $seconds );
	} 
}

function splitTagFile( $lsinfo, $config )
{
	$mcount = count( $lsinfo );
	$tag = "0";
	$tagged = array();
	$untag = "0";
	$untagged = array();

	for( $i="0"; $i < $mcount; $i++ )
	{
		if( strcmp( $config["filenames_only"], "yes" ) == "0" ||
		    empty( $lsinfo[$i]["Title"] ))
		{
			$untagged[$untag] = $lsinfo[$i];
			$untag++;
		}
		else
		{
			$tagged[$tag] = $lsinfo[$i];
			$tag++;
		}
	}

	return( array( $tagged, $untagged ));
}

function fileinfo2musicTable( $info, $dir_url, $config, $color, $server, $addperm, $sort_array, $sort )
{
	if( count( $info ) == "0" )
	{
		return 0;
	}

	$count = count( $info );
	$dir_url = rawurlencode( $dir_url );
	$index = array();
	$index_key = "mf";
	$mic = "0";
	$mprint = array();
	
        usort( $info, "fsort" );

	for ( $i = "0"; $i < $count; $i++ )
	{
		$col = $color["body"][ ( $i%2 ) ];
		$full_filename = $info[$i]["file"];
		$split_filename = basename( $full_filename );
		$fc_filename = mbFirstChar( $split_filename );

		$full_filename = rawurlencode( $full_filename );

		if ( $mic == "0" || $index[ ($mic-1) ] != strtoupper( $fc_filename ))
		{
			$index[ $mic ] = strtoupper( mbFirstChar( $fc_filename ));
			$item = $index[ $mic ];
			$mic++;
			$mprint[$i] = "<a name=" . $index_key . $item . "></a>";
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
			$mprint[$i] .= "<td width=\"100%\" colspan=" . ( sizeof( $config["display_fields"] ) - 1 ) . ">$split_filename</td><td>";
		}
		else
		{
			$mprint[$i] = "<tr bgcolor=$col><td colspan=" . ( sizeof( $config["display_fields"] ) - 1 ) . ">$split_filename</td><td>";
		}

		if ( isset( $info[$i]['Time'] ) && array_search( 'Time', $config["display_fields"] ))
		{
			$mprint[$i] .= display_time($info[$i]['Time']);
		}
		else
		{
			$mprint[$i] .= $config["unknown_string"];
		}
		$mprint[$i] .= "</td></tr>";
	}
	// The sort bar is created here.
	$sort_bar = "<tr bgcolor=\"" . $color["sort"] . "\">";

	// This creates the column for 'Add'
	if( $addperm == "1" )
	{
		$sort_bar .= "<td width=0></td>";
	}

	$sort_bar .= "<td colspan=\"" . ( count( $config["display_fields"]) - 1 ) . "\">File</td><td>Time</td></tr>";

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
		$col = $color["body"][ ( $i%2 ) ];
		$full_filename = $info[$i]["file"];
		$split_filename = basename( $full_filename );
		$fc_filename = mbFirstChar( $split_filename );

		$full_filename = rawurlencode( $full_filename );

		// This is where the index for the particular table is made
		// If the sort item exists in the music item, this is not the first letter that's going to be
		// added to the index, and the index before hand is not the samee

		if( strcmp( $sort_array[0], "Track" ))
		{
			if( isset( $info[$i][$sort_array[0]] ) &&
				strlen( $info[$i][ $sort_array[0] ] ) &&
				( $mic==0 || $index[ ( $mic - 1 ) ] != strtoupper( mbFirstChar( $info[$i][ $sort_array[0] ] ))))
			{
				$index[ $mic ] = strtoupper( mbFirstChar( $info[$i][ $sort_array[0] ] ));
				$item = $index[$mic];
				$mic++;
				$mprint[$i] = "<a name=" . $index_key . $item . "></a>";
			}
			else
			{
				$mprint[ $i ] = "";
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
				$mprint[ $i ] = "<a name=" . $index_key . $item . "></a>";
			}
			else
			{
				$mprint[ $i ] = "";
			}
		}
	
		if( $addperm == "1" )
		{
			$mprint[$i] = "<tr bgcolor=$col><td width=0>$mprint[$i][";
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
						$mprint[$i] .= $info[$i][ $config["display_fields"][$x] ] . "</a>";
					}
					else
					{
						$mprint[ $i ] .= $config["unknown_string"];
					}
					break;
				}

				case 'Title':
				{
					$mprint[$i] .= $info[$i][ $config["display_fields"][$x] ];
					break;
				}

				case 'Track':
				{
					if ( isset( $info[$i][ $config["display_fields"][$x] ] ))
					{
						$mprint[ $i ] .= $info[$i][ $config["display_fields"][$x] ];
					}
					else
					{
						$mprint[ $i ] .= $config["unknown_string"];
					}
					break;
				}

				case 'Time':
				{
					if ( isset( $info[$i][ $config["display_fields"][$x] ] ))
					{
						$mprint[ $i ] .= display_time( $info[$i][ $config["display_fields"][$x] ] );
					}
					else
					{
						$mprint[ $i ] .= $config["unknown_string"];
					}
					break;
				}

				default:
				{
					$mprint[ $i ] .= "Config Error";
					break;
				}
			}
		$mprint[$i] .= "</td>";
		}


	// Sort bar is created here
	if( strcmp( $config["filenames_only"], "yes" ))
	{
		$sort_bar = "<tr bgcolor=\"" . $color["sort"] . "\">";
		// This creates the column for 'Add'
		if( $addperm == "1" )
		{
			$sort_bar .= "<td width=0></td>";
		}
		for( $j=0; $j < count( $config["display_fields"] ); $j++ )
		{
			// Cut this in pieces so it wouldn't wrap
			$sort_bar .= "<td>";
			if( strcmp( $ordered, "yes" ) && strcmp( $config["display_fields"][$j], $sort_array[0] ) == "0" )
			{
	 			$sort_bar .= "<a title=\"Reverse this field\"";
				$sort_bar .= " href=\"$url&amp;sort=" . pickSort($config["display_fields"][$j]) . "&amp;ordered=yes&amp;server=$server\">";
				$sort_bar .= "<b>" . $config["display_fields"][$j] . "</b>";
			}
			else if( strcmp( $config["display_fields"][$j], $sort_array[0] ) == "0" )
			{
	 			$sort_bar .= "<a title=\"Reverse this field\" ";
				$sort_bar .= "href=\"$url&amp;sort=" . pickSort($config["display_fields"][$j]) . "&amp;ordered=no&amp;server=$server\">";
				$sort_bar .= "<b>" . $config["display_fields"][$j] . "</b>";
			}
			else
			{
	       			$sort_bar .= "<a title=\"Sort by this field\" ";
				$sort_bar .= "href=\"$url&amp;sort=" . pickSort($config["display_fields"][$j]) . "&amp;ordered=no&amp;server=$server\">";
				$sort_bar .= $config["display_fields"][$j];
			}
			$sort_bar .= "</a>";
			$sort_bar .= "</td>";
		}
		$sort_bar .= "</tr>";
	}

	$ret["print"] = $mprint;
	$ret["index"] = $index;
	$ret["index_key"] = "mt";
	$ret["sortbar"] = $sort_bar;
	$ret["title"] = "Tagged Music";
	}
 	return( $ret );
}

function createAddAll( $music, $song_separator )
{
	$add_all = "";
	$mcount = count( $music );
	for( $i="0"; $i < $mcount; $i++ )
	{
		if ( $i < ($mcount - "1") )
		{
			$add_all .= addslashes( $music[$i]["file"] ) . $song_separator;
		}
		else
		{
			$add_all .= $music[$i]["file"];
		}
	}
	return $add_all;
}

function lsinfo2musicTable( $lsinfo, $sort, $dir_url, $sort_array, $config, $color, $server, $addperm )
{
	$add_all = "";
	$dir_url = rawurlencode( $dir_url );
	$mcount = count( $lsinfo["music"] );
	$fmic = "0";
	$tmic = "0";
	$mfcount = "0";
	$mtcount = "0";
	$music = array();

        usort( $lsinfo["music"], "msort" );
	
	// Loop for every song in the current directory
	for( $i="0"; $i < $mcount; $i++ )
	{
		$full_filename = $lsinfo["music"][$i]["file"];
		$split_filename = basename( $full_filename );
		$fc_filename = mbFirstChar( $split_filename );

		if ( $i < $mcount - "1" )
		{
			$add_all .= addslashes( $full_filename ) . $config["song_separator"];
		}
		else
		{
			$add_all .= $full_filename;
		}

		$full_filename = rawurlencode( $full_filename );

		/***********************************************************************************************************************#
		# Purpose: If $config["filenames_only"] is not "yes", and the 'Title' is not set					#
		#***********************************************************************************************************************#
		# $mtcount => Music Tagged Count, the number of tagged files thus far							#
		# $mtindex => Music Tagged Index, these are the first unique letters of the 'Title' tag, for use in printMusicTable()	#
		# $mtprint => Music Tagged Print, the file that will be parsed/printed at printMusicTable()				#
		#***********************************************************************************************************************/

		if( strcmp( $config["filenames_only"], "yes" ) &&
		    isset( $lsinfo["music"][$i]["Title"] ) &&
		    $lsinfo["music"][$i]["Title"])
		{
			$col = $color[ ( $mtcount %2 ) ];
			if( strcmp( $sort_array[0], "Track" ))
			{
				if( isset( $lsinfo["music"][$i][$sort_array[0]] ) &&
				    strlen( $lsinfo["music"][$i][$sort_array[0]] ) &&
				    ( $tmic==0 || $mtindex[ ( $tmic - 1 ) ] != strtoupper( mbFirstChar( $lsinfo["music"][$i][ $sort_array[0] ] ))))
				{
					$mtindex[ $tmic ] = strtoupper( mbFirstChar( $lsinfo["music"][$i][ $sort_array[0] ] ));
					$foo = $mtindex[ $tmic ];
					$tmic++;
					$mtprint[$mtcount] = "<a name=mt$foo></a>";
				}
				else
				{
					$mtprint[$mtcount] = "";
				}
			}
			else
			{
				if( isset( $lsinfo["music"][$i][$sort_array[0] ] ))
				{
					$foo = strtok( $lsinfo["music"][$i][ $sort_array[0] ], "/" );
				}
				if( isset( $foo ) && ( $tmic == "0" || strcmp( $mtindex[ ($tmic-1) ], $foo )))
				{
					$mtindex[$tmic] = $foo;
					$tmic++;
					$mtprint[$mtcount] = "<a name=mt$foo></a>";
				}
				else
				{
					$mtprint[$mtcount] = "";
				}
			}

			if( $addperm == "1" )
			{
				$mtprint[$mtcount] = "<tr bgcolor=$col><td width=0>$mtprint[$mtcount][";
				$mtprint[$mtcount] .= "<a title=\"Add this song to the current playlist\" ";
				$mtprint[$mtcount] .= "target=\"playlist\" ";
				$mtprint[$mtcount] .= "href=\"index.php?body=playlist&amp;server=$server&amp;command=add&amp;arg=";
				$mtprint[$mtcount] .= rawurlencode($full_filename) . "\">add</a>]</td>";
			}
			else
			{
				$mtprint[$mtcount] = "<tr bgcolor=$col>";
			}

			for ( $x = 0; $x < sizeof($config["display_fields"]); $x++)
			{
				$mtprint[$mtcount] .= "<td>";

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
						if( isset( $lsinfo["music"][$i][ $config["display_fields"][$x] ] ))
						{
							$url = rawurlencode( $lsinfo["music"][$i][ $config["display_fields"][$x] ] );
							$mtprint[$mtcount] .= "<a title=\"Find by this keyword\" href=\"$url&amp;feature=search&amp;server=$server&amp;find=";
							$mtprint[$mtcount] .= strtolower($config["display_fields"][$x]);
							$mtprint[$mtcount] .= "&amp;arg=$url&amp;sort=$sort&amp;dir=$dir_url\">";
							$mtprint[$mtcount] .= $lsinfo["music"][$i][$config["display_fields"][$x]] . "</a>";
						}
						else
						{
							$mtprint[$mtcount] .= $config["unknown_string"];
						}
						break;
					case 'Title':
						$mtprint[$mtcount] .= $lsinfo["music"][$i][ $config["display_fields"][$x] ];
						break;
					case 'Track':
						if ( isset( $lsinfo["music"][$i][ $config["display_fields"][$x] ] ))
						{
							$mtprint[$mtcount] .= $lsinfo["music"][$i][ $config["display_fields"][$x] ];
						}
						else
						{
							$mtprint[$mtcount] .= $config["unknown_string"];
						}
						break;
					case 'Time':
						if ( isset( $lsinfo["music"][$i][ $config["display_fields"][$x] ] ))
						{
							$mtprint[$mtcount] .= display_time( $lsinfo["music"][$i][ $config["display_fields"][$x] ] );
						}
						else
						{
							$mtprint[$mtcount] .= $config["unknown_string"];
						}
						break;
					default:
						$mtprint[$mtcount] .= "Config Error";
						break;
				}
				$mtprint[$mtcount] .= "</td>";
			}
			$mtcount++;
		}

		/***********************************************************************************************************************#
		# Purpose: If $config["filenames_only"] is "yes", and 'Title' is set							#
		#***********************************************************************************************************************#
		# $mfcount => Music Files Count, the number of music files without metadata thus far					#
		# $mfindex => Music Files Index, this variable grabs the first unique letters of the filename for printMusicTable()	#
		# $mfprint => Music Files Print, the file that will be parsed/printed at printMusicTable()				#
		#***********************************************************************************************************************/

		else
		{
			$col = $color[ ( $mfcount %2 ) ];
			if ( $fmic == "0" || $mfindex[ ($fmic-1) ] != strtoupper( $fc_filename ))
			{
				$mfindex[ $fmic ] = strtoupper( mbFirstChar( $fc_filename ));
				$foo = $mfindex[ $fmic ];
				$fmic++;
				$mfprint[$mfcount] = "<a name=mf$foo></a>";
			}
			else
			{
				$mfprint[$mfcount] = "";
			}

			if( $addperm == "1" )
			{
				$mfprint[$mfcount] = "<tr bgcolor=$col><td>$mfprint[$mfcount][<a title=\"Add this song to the active playlist\" ";
				$mfprint[$mfcount] .= "target=\"playlist\" ";
				$mfprint[$mfcount] .= "href=\"index.php?body=playlist&amp;server=$server&amp;command=add&amp;arg=$full_filename\">add</a>]</td>";
				$mfprint[$mfcount] .= "<td width=\"100%\" colspan=" . ( sizeof( $config["display_fields"] ) - 1 ) . ">$split_filename</td><td>";
			}
			else
			{
				$mfprint[$mfcount] = "<tr bgcolor=$col><td colspan=" . ( sizeof( $config["display_fields"] ) - 1 ) . ">$split_filename</td><td>";
			}

			if ( isset( $lsinfo["music"][$i]['Time'] ) && array_search( 'Time', $config["display_fields"] ))
			{
				$mfprint[$mfcount] .= display_time($lsinfo["music"][$i]['Time']);
			}
			else
			{
				$mfprint[$mfcount] .= $config["unknown_string"];
			}

			$mfprint[$mfcount] .= "</td></tr>";
			$mfcount++;
		}
	}

	if( isset( $mfprint ))
	{
		$mprint["file"] = $mfprint;	
	}
	else
	{
		$mprint["file"] = array();
	}
	if( isset( $mtprint ))
	{
		$mprint["tag"] = $mtprint;
	}
	else
	{
		$mprint["tag"] = array();
	}

	if( isset( $mfindex ))
	{
		$mindex["file"] = $mfindex;
	}
	else
	{
		$mindex["file"] = array();
	}
	if( isset( $mtindex ))
	{
		$mindex["tag"] = $mtindex;
	}
	else
	{
		$mindex["tag"] = array();
	}

	return array( $mprint, $mindex, $add_all );
}

/* This function is used to print the index for all tables that need an index */
function printIndex( $index, $title, $anc )
{
	if( count( $index ))
	{
		echo "<!-- Begin printIndex -->";
		echo $title . " [ ";
		for ( $i="0"; $i<count( $index ); $i++ )
		{
			$foo = $index[$i];
			echo "<a title=\"Goto the beginning of $foo\" href=\"#$anc$foo\">$foo</a>&nbsp;";
		}
		echo "]";
		echo "<!-- End printIndex -->";
	}
}

function printMusicTable( $add_all, $config, $color, $info, $sort_array, $server, $dir, $addperm, $feature, $ordered)
{
	// Go ahead and get these set, they'll be called quite a few times
	$count = count( $info["print"] );

	// This is the catchall, if there's any music print it.
	if( $count > "0" )
	{
		echo "<br>";
		extract( $info );
		echo "<!-- Begin printMusicTable  -->";
		$add_all = rawurlencode( $add_all );

		if( strcmp( $config["use_javascript"], "yes" ) == "0" )
		{
			echo '<form name="add_all" method="post" action="index.php" target="playlist">';
			echo "<input type=hidden name=\"add_all\" value=\"$add_all\">";
			echo "<input type=hidden name=\"body\" value=\"playlist\">";
			echo "<input type=hidden name=\"server\" value=\"$server\">";
			echo "<table summary=\"Music Separators\" cellspacing=1 bgcolor=\"" . $color["title"] . "\">";
			echo "<tr><td>";
			echo "<table summary=\"Music Separators\" cellspacing=1 bgcolor=\"" . $color["title"] . "\">";

			// If the Tag Count is above "0" then display the Tag Table

			echo "<a name=\"$title\"></a>";
			echo "<tr><td colspan=". ( count( $config["display_fields"] ) - 1 ) . "><b>$title</b>";

			// If not sorting by 'Time' display the index, due to bugs in 'Time'/index
			if( strcmp( $sort_array[0], "Time" ))
			{
				echo printIndex( $index, "", $index_key );
			}

// Removed until we get the add_all situation under control

			if( strcmp( $title, "Music" ))
			{
				echo "&nbsp;<small>(<a title=\"Add all songs from this music table to the active playlist\" ";
				echo "href=\"javascript:document.add_all.submit()\">add all tagged/untagged</a>)</small>";
			}
			else
			{
				echo "&nbsp;<small>(<a title=\"Add all songs from this music table to the active playlist\" ";
				echo "href=\"javascript:document.add_all.submit()\">add all</a>)</small>";
			}

			echo "</td>";
		}
		if( strcmp( $feature, "search" ) == "0" || strcmp( $feature, "find" ) == "0" )
		{
			echo "<td align=right><b><small>Found $count results</small></b></td>";
		}
		echo "</tr></table>";
		// End the table header

		// Begin the table body
		echo "<table summary=\"Music\" cellspacing=1 bgcolor=\"" . $color["body"][1] . "\">";
		echo $sortbar;

		for( $i = "0"; $i < $count; $i++ )
		{
		        echo $print[$i];
		}
		echo "</td></tr>";
		echo "</table>";
		echo "</td></tr>";
		echo "</table>";
#		echo "<br>";
	}
	else
	{
		return;
	}
}

function printPlaylistTable( $color, $server, $pprint, $pindex, $delete, $rmperm )
{
	if ( count( $pprint ) > "0" )
	{
	        // Begin table for Title & Index
		echo "<!-- Begin printPlaylistTable -->";
		echo "<br>";
		echo "<table summary=\"Playlist Title & Index\" cellspacing=1 bgcolor=\"" . $color["title"] . "\">";
		echo "<tr><a name=playlists></a>";
		echo "<td nowrap>";
		echo "<b>Saved Playlists</b>";
		printIndex( $pindex, "", "p" );
		if( strcmp( $delete, "yes" ) && $rmperm == "1" )
		{
		        echo "&nbsp;<small>(<a title=\"Goto Delete Playlist Menu\" href=\"index.php?body=main&amp;delete=yes&amp;server=$server#playlists\">delete</a>)</small>";
		}

		echo "</td></tr>";
		echo "<tr><td>";
		echo "<table summary=\"Playlist\" cellspacing=1 bgcolor=\"" . $color["body"][0] . "\">";

		// Begin for playlist
		for ( $i=0; $i < count( $pprint ); $i++ )
		{
		        echo "<tr bgcolor=\"" . $color["body"][$i%2] . "\">";
			echo "<td>" . $pprint[$i] . "</td>";
			echo "</tr>";
		}
		echo "</table>";
		echo "</td></tr></table>";
	}
}

function songInfo2Display( $song_info, $config )
{
	if( preg_match( "/^[a-z]*:\/\//", $song_info["file"] ))
	{
		$song = $song_info["file"];
	}
	else
	{
		$song_array = split( "/", $song_info["file"] );
		$song = $song_array[ (count($song_array) - 1) ];
	}
	if( strcmp( $config["filenames_only"],"yes" ) && isset( $song_info["Title"] ) && $song_info["Title"] > "0" )
	{
		if( isset( $song_info["Artist"] ))
		{
		        $artist = $song_info["Artist"];
		}
		else
		{
		        $artist = "";
		}

		if( isset( $song_info["Title"] ))
		{
		        $title = $song_info["Title"];
		}
		else
		{
		        $title = "";
		}

		if( isset( $song_info["Album"] ))
		{
		        $album = $song_info["Album"];
		}
		else
		{
		        $album = "";
		}

		if ( isset( $song_info["Genre"] ))
		{
			$genre = $song_info["Genre"];
		}
		else
		{
			$genre = "";
		}

		if ( isset($song_info["Date"] ))
		{
			$date = $song_info["Date"];
		}
		else
		{
			$date = "";
		}

		if ( isset( $song_info["Track"] ))
		{
		        $track = $song_info["Track"];
		}
		else
		{
		        $track = "";
		}

		$trans = array( "artist" => $artist, "title" => $title, "album" => $album, "track" => $track, "genre" => $genre, "date" => $date );
		
		// If it doesn't exist don't print it, stupid
		if( strlen( $artist ) == "0" && ! strlen( $title ) == "0" )
		{
		        $song_display_conf = $config["display_conf"]["title"];
		}
		else if( strlen( $title ) == "0" && ! strlen( $artist ) == "0" )
		{
		        $song_display_conf = $config["display_conf"]["artist"];
		}
		else
		{
		        $song_display_conf = $config["display_conf"]["artist"]. $config["display_conf"]["seperator"] . $config["display_conf"]["title"];
		}
		$song_display = strtr($song_display_conf, $trans);
	}
	else if( strcmp( $config["filenames_only"], "yes") == "0" && isset( $song_info["Name"] ) && ($song_info["Name"] > "0" ))
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
