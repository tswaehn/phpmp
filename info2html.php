<?php
include "sort.php";

/*
 begin lsinfo2directoryTable

*/
function lsinfo2directoryTable($lsinfo, $server, $sort, $dir, $addperm, $color)
{
	$dcount = count($lsinfo["dir"]);
	if ($dcount)
	{
		usort($lsinfo["dir"],"strcasecmp");
	}

	$dic = 0;
	for ($i=0;$i<$dcount;$i++)
	{
		// dirstr: The actual directory name
       		$dirstr = $lsinfo["dir"][$i];
		$dirss = split("/",$dirstr);
		if (count($dirss)==0) 
		{
 			$dirss[0] = $dirstr;
		}
		$dirss[0] = $dirss[count($dirss)-1];
		$dirstr = rawurlencode($dirstr);
		$dprint[$i] = "<tr bgcolor=\"" . $color[$i%2]  . "\"><td>";
		$fc = strtoupper(mbFirstChar($dirss[0]));
		if ($dic==0 || $dindex[$dic-1]!=$fc)
		{
			$dindex[$dic] = $fc;
			$dprint[$i].= "<a name=d" . $dindex[$dic]  . "></a>";
			$dic++;
		}

		// If updating show the update links, otherwise show add links
		if($addperm)
		{
			$dprint[$i].= "[<a title=\"Add the " . $dirss[0]  . " Directory\" href=\"index.php?body=playlist&amp;server=$server&amp;command=add&amp;arg=$dirstr\" target=playlist>add</a>]&nbsp";
		}
		$dprint[$i].= "<a title=\"Browse the " . $dirss[0] . " Directory\" href=\"index.php?body=main&amp;server=$server&amp;sort=$sort&amp;dir=$dirstr\">$dirss[0]</a></td></tr>";
	}
	if(!isset($dindex))
	{
		$dindex = array();
	}
	return array($dprint, $dindex, $dcount);
}

function printSavePlaylistTable($save, $server, $color)
{
	if (isset($save) && strcmp($save,"yes")==0)
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
function printDirectoryTable($dcount, $dprint, $dindex, $dir, $sort, $server, $addperm, $color)
{
	if($dcount)
	{
		echo "<!-- Begin printDirectoryTable -->";
		echo "<br>";
	        echo "<table summary=\"Directory Border\" cellspacing=1 bgcolor=\"" . $color["title"] . "\">";
		echo "<tr><td nowrap><b>Directories</b>";
	        printIndex($dindex,"","d");
		if($addperm)
		{
			echo "&nbsp;<small>(<a title=\"Add All Directories and Music\" target=playlist href=index.php?body=playlist&amp;server=$server&amp;command=add&amp;arg=$dir>add all</a>)</small>";
		}
		echo "</td></tr>";  
		echo "<tr><td>";
		echo "<table summary=\"Directory\" cellspacing=1 bgcolor=\"" . $color["body"][1] . "\">";

		for($i=0;$i<$dcount;$i++)
                {
                        echo $dprint[$i];
                }
		echo "</table>";
		echo "</td></tr></table>";
		echo "<!-- End printDirectoryTable -->";
	}
}

function lsinfo2playlistTable($lsinfo, $sort, $delete, $server, $loadperm)
{
	$pic = 0;
	$pcount = count($lsinfo["playlist"]);
	if ($pcount)
	{
	        usort($lsinfo["playlist"],"strcasecmp");
	}
	for ($i=0;$i<$pcount;$i++)
	{
		$dirstr = $lsinfo["playlist"][$i];
		$dirss = split("/",$dirstr);
		if (count($dirss)==0)
		{
			$dirss[0] = $dirstr;
		}
		$dirss[0] = $dirss[count($dirss)-1];
		$dirstr = rawurlencode($dirstr);
		$fc = strtoupper(mbFirstChar($dirss[0]));
		if ($pic==0 || $pindex[$pic-1]!=$fc)
		{
			$pindex[$pic] = $fc;
			$foo = $pindex[$pic];
			$pic++;
			$pprint[$i] = "<a name=p$foo></a>";
		}
		else
		{
			$pprint[$i] = "";
		}
		if(strcmp($delete,"yes")==0)
		{
		        $pprint[$i].="[<a title=\"Remove playlist $dirss[0]\"  href=\"index.php?body=main&amp;server=$server&amp;sort=$sort&amp;command=rm&amp;arg=$dirstr\">del</a>]&nbsp;";
		}
		if($loadperm)
		{
			$pprint[$i].="<a title=\"Load the playlist $dirss[0]\" target=\"playlist\" href=\"index.php?body=playlist&amp;server=$server&amp;command=load&amp;arg=$dirstr\">$dirss[0]</a>&nbsp;";
		}
		else
		{
			$pprint[$i].="$dirss[0]&nbsp;";
		}
	}
	if (!isset($pprint))
	{
	        $pprint = array();
	}
	if (!isset($pindex))
	{
	        $pindex = array();
	}
	return array($pprint,$pindex);
}

function display_time($seconds)
{
	if ($seconds > 60)
	{
		$min = floor($seconds / 60);
		$sec = $seconds - ($min * 60);
		return sprintf("%d:%02d", $min, $sec);
	}
	else
	{
		return sprintf("0:%02d", $seconds);
	} 
}

function lsinfo2musicTable($lsinfo, $sort, $dir_url, $sort_array, $config, $color, $server, $addperm)
{
	$mic = 0;
	$mcount = count($lsinfo["music"]);
        
        if(strcmp($config["filenames_only"],"yes")==0)
	{
		for ($i=0;$i<$mcount;$i++)
		{
                    $music["shortname"][$i] = array_pop(split("/",$lsinfo["music"][$i]["file"]));
		}
	}       
	if (isset($lsinfo["music"]["shortname"]))
	{
		usort($lsinfo["music"]["shortname"],"msort");
	}
	else if ($mcount)
	{
	        usort($lsinfo["music"],"msort");
	}

	$add_all = "";
	
	// Loop for every song in the current directory
	for ($i=0;$i<$mcount;$i++)
	{
		$full_filename = $lsinfo["music"][$i]["file"];
		$split_filename = split("/",$full_filename);
		if (count($split_filename)==0)
		{
			$split_filename[0] = $full_filename;
		}
		$split_filename[0] = array_pop($split_filename);
		if ($i<$mcount-1)
		{
		        $add_all .= addslashes($full_filename) . $config["song_separator"];
		}
		else
		{
		        $add_all .= $full_filename;
		}
		$full_filename = rawurlencode($full_filename);
		$col = $color[$i%2];

		// If not filenames_only and title is set
		if (strcmp($config["filenames_only"],"yes") &&
		    isset($lsinfo["music"][$i]["Title"]) &&
		    $lsinfo["music"][$i]["Title"])
		{
			if (strcmp($sort_array[0],"Track"))
			{
				if (isset($lsinfo["music"][$i][$sort_array[0]]) &&
				    strlen($lsinfo["music"][$i][$sort_array[0]]) &&
				    ($mic==0 || $mindex[$mic-1]!=strtoupper(mbFirstChar($lsinfo["music"][$i][$sort_array[0]]))))
				{
					$mindex[$mic] = strtoupper(mbFirstChar($lsinfo["music"][$i][$sort_array[0]]));
					$mic++;
					$mprint[$i] = "<a name=m" . $mindex[$mic] . "></a>";
				}
				else
				{
					$mprint[$i] = "";
				}
			}
			else
			{
				if (isset($lsinfo["music"][$i][$sort_array[0]]))
				{
					$foo = strtok($lsinfo["music"][$i][$sort_array[0]],"/");
				}
				if (isset($foo) && ($mic==0 || strcmp($mindex[$mic-1],$foo)))
				{
					$mindex[$mic] = $foo;
					$mic++;
					$mprint[$i] = "<a name=m$foo></a>";
				}
				else
				{
					$mprint[$i] = "";
				}
			}

			if($addperm)
			{
				$mprint[$i] = "<tr bgcolor=$col><td width=0>$mprint[$i][";
				$mprint[$i] .= "<a title=\"Add this song to the current playlist\" target=\"playlist\" href=\"index.php?body=playlist&amp;server=$server&amp;command=add&amp;arg=" . rawurlencode($full_filename) . "\">add</a>]</td>";
			}
			else
			{
				$mprint[$i] = "<tr bgcolor=$col>";
			}

			for ($x = 0; $x < sizeof($config["display_fields"]); $x++)
			{
				$mprint[$i] .= "<td>";
				switch ($config["display_fields"][$x])
				{
					case 'Album':
					case 'Artist':
						if (isset($lsinfo["music"][$i][$config["display_fields"][$x]]))
						{
							$url = rawurlencode($lsinfo["music"][$i][$config["display_fields"][$x]]);
							$mprint[$i].= "<a title=\"Find by this keyword\" href=\"index.php?body=main&amp;feature=search&amp;server=$server&amp;find=";
							$mprint[$i].= strtolower($config["display_fields"][$x]);
							$mprint[$i].= "&amp;arg=$url&amp;sort=$sort&amp;dir=$dir_url\">";
							$mprint[$i].= $lsinfo["music"][$i][$config["display_fields"][$x]] . "</a>";
						}
						else
						{
							$mprint[$i].= $config["unknown_string"];
						}
						break;
					case 'Date':
						if (isset($lsinfo["music"][$i][$config["display_fields"][$x]]))
						{
							$url = rawurlencode($lsinfo["music"][$i][$config["display_fields"][$x]]);
							$mprint[$i].= "<a title=\"Find by this keyword\" href=\"index.php?body=main&amp;feature=search&amp;server=$server&amp;find=";
							$mprint[$i].= strtolower($config["display_fields"][$x]);
							$mprint[$i].= "&amp;arg=$url&amp;sort=$sort&amp;dir=$dir_url\">";
							$mprint[$i].= $lsinfo["music"][$i][$config["display_fields"][$x]] . "</a>";
						}
						else
						{
							$mprint[$i].= $config["unknown_string"];
						}
						break;
					case 'Genre':
						if (isset($lsinfo["music"][$i][$config["display_fields"][$x]]))
						{
							$url = rawurlencode($lsinfo["music"][$i][$config["display_fields"][$x]]);
							$mprint[$i].= "<a title=\"Find by this keyword\" href=\"index.php?body=main&amp;feature=search&amp;server=$server&amp;find=";
							$mprint[$i].= strtolower($config["display_fields"][$x]);
							$mprint[$i].= "&amp;arg=$url&amp;sort=$sort&amp;dir=$dir_url\">";
							$mprint[$i].= $lsinfo["music"][$i][$config["display_fields"][$x]] . "</a>";
						}
						else
						{
							$mprint[$i].= $config["unknown_string"];
						}
						break;
					case 'Title':
						$mprint[$i].= $lsinfo["music"][$i][$config["display_fields"][$x]];
						break;
					case 'Track':
						if (isset($lsinfo["music"][$i][$config["display_fields"][$x]]))
						{
							$mprint[$i].= $lsinfo["music"][$i][$config["display_fields"][$x]];
						}
						else
						{
							$mprint[$i].= $config["unknown_string"];
						}
						break;
					case 'Time':
						if (isset($lsinfo["music"][$i][$config["display_fields"][$x]]))
						{
							$mprint[$i].= display_time($lsinfo["music"][$i][$config["display_fields"][$x]]);
						}
						else
						{
							$mprint[$i].= $config["unknown_string"];
						}
						break;
					default:
						$mprint[$i] .= "Config Error";
						break;
				}
				$mprint[$i] .= "</td>";
			}
		}
		else
		{
			if ($mic==0 || $mindex[$mic-1]!=strtoupper($split_filename[0][0]))
			{
				$mindex[$mic] = strtoupper($split_filename[0][0]);
				$foo = $mindex[$mic];
				$mic++;
				$mprint[$i] = "<a name=m$foo></a>";
			}
			else
			{
				$mprint[$i] = "";
			}
			if ($config["display_fields"][sizeof($config["display_fields"]) - 1] == 'Time')
			{
				if($addperm)
				{
					$mprint[$i] = "<tr bgcolor=$col><td>$mprint[$i][<a title=\"Add this song to the active playlist\" target=\"playlist\" href=\"index.php?body=playlist&amp;server=$server&amp;command=add&amp;arg=$full_filename\">add</a>]</td><td colspan=" . (sizeof($config["display_fields"]) - 1) . ">$split_filename[0]</td><td>";
				}
				else
				{
					$mprint[$i] = "<tr bgcolor=$col><td colspan=" . (sizeof($config["display_fields"]) - 1) . ">$split_filename[0]</td><td>";
				}

				if (!isset($lsinfo["music"][$i]['Time']))
				{
					$mprint[$i].= $config["unknown_string"];
				}
				else
				{
					$mprint[$i].= display_time($lsinfo["music"][$i]['Time']);
				}

				$mprint[$i] .= "</td></tr>";
			}
			else
			{
				if($addperm)
				{
					$mprint[$i] = "<tr bgcolor=$col><td>$mprint[$i][<a title=\"Add this song to the active playlist\" target=\"playlist\" href=\"index.php?body=playlist&amp;server=$server&amp;command=add&amp;arg=$full_filename\">add</a>]</td><td colspan=" . sizeof($config["display_fields"]) . ">$split_filename[0]</td></tr>";
				}
				else
				{
					$mprint[$i] = "<tr bgcolor=$col><td colspan=" . sizeof($config["display_fields"]) . ">$split_filename[0]</td></tr>";
				}
			}
		}
	}
	if (!isset($mprint))
	{
	        $mprint = array();
	}
	if (!isset($mindex))
	{
	        $mindex = array();
	}
	return array($mprint, $mindex, $add_all);
}

/* This function is used to print the index for all tables that need an index */
function printIndex($index,$title,$anc)
{
	if (count($index))
	{
		echo "<!-- Begin printIndex -->";
		echo $title . " [ ";
		for ($i=0;$i<count($index);$i++)
		{
			$foo = $index[$i];
			echo "<a title=\"Goto the beginning of $foo\" href=\"#$anc$foo\">$foo</a>&nbsp;";
		}
		echo "]";
		echo "<!-- End printIndex -->";
	}
}

function printMusicTable($config, $color, $sort_array, $server, $mprint, $url, $add_all, $mindex, $dir, $addperm)
{
	if (count($mprint)>0)
	{
		echo "<!-- Begin printMusicTable  -->";
		echo "<br>";
		$add_all = rawurlencode($add_all);
		if (strcmp($config["use_javascript"],"yes")==0)
		{
			echo "<form name=\"add_all\" method=\"post\" action=\"index.php\" target=\"playlist\">";
			echo "<input type=hidden name=\"add_all\" value=\"$add_all\">";
			echo "<input type=hidden name=\"body\" value=\"playlist\">";
			echo "<input type=hidden name=\"server\" value=\"$server\">";
			echo "<table summary=\"Music Separators\" cellspacing=1 bgcolor=\"" . $color["title"] . "\">";
			echo "<tr><td>";
			echo "<table summary=\"Music Separators\" cellspacing=1 bgcolor=\"" . $color["title"] . "\">";
			echo "<tr><a name=music></a>";
			echo "<td><b>Music</b>";
			echo printIndex($mindex,"","m");
			if($addperm)
			{
				echo "&nbsp;<small>(<a title=\"Add all songs from this music table to the active playlist\" href=\"javascript:document.add_all.submit()\">add all</a>)</small>";
			}
		}
		else
		{
			echo "<table summary=\"Music Separators\" cellspacing=1 bgcolor=\"" . $color["title"] . "\">";
			echo "<tr><td colspan=4><b>Music</b>";
			if($addperm)
			{
				echo "<small>(<a title=\"Add all songs from this music table to the active playlist\" target=\"playlist\" href=\"index.php?body=playlist&amp;server=$server&amp;add_all=$add_all\">add all</a>)</small>";
			}
			echo printIndex($mindex,"","m");
		}
		echo "</td><td align=right>";
		if(preg_match("/feature=(search|find)/",$url))
		{
			echo "<b><small>Found " . count($mprint) . " results</small></b>";
		}
		echo "</td></tr></table>";
		echo "<tr><td>";
		echo "<table summary=\"Music\" cellspacing=1 bgcolor=\"" . $color["body"][1] . "\">";
		if (strcmp($config["filenames_only"],"yes"))
		{
			echo "<tr bgcolor=\"" . $color["sort"] . "\">";
			if($addperm)
			{
				echo "<td width=0></td>";
			}
			for ($i=0;$i<count($config["display_fields"]);$i++)
			{
				// Cut this in pieces so it wouldn't wrap
				echo "<td>";
       				echo "<a title=\"Sort by this field\" href=\"$url&amp;sort=" . pickSort($config["display_fields"][$i]) . "&amp;server=$server\">" . (($config["display_fields"][$i] == $sort_array[0]) ? '<b>' . $config["display_fields"][$i] . '</b>' : $config["display_fields"][$i]);
				echo "</a>";
				echo "</td>";
			}
			echo "</tr>";
		}
		for ($i=0;$i<count($mprint);$i++)
		{
		        echo $mprint[$i];
		}
		echo "</table>";
		echo "</td></tr>";
		if (strcmp($config["use_javascript"],"yes"))
		{
			echo "</form>";
		}
		echo "</table>";
		echo "<!-- End printMusicTable -->";
	}
}

function printPlaylistTable($color, $server, $pprint, $pindex, $delete, $rmperm)
{
	if (count($pprint))
	{
	        // Begin table for Title & Index
		echo "<!-- Begin printPlaylistTable -->";
		echo "<br>";
		echo "<table summary=\"Playlist Title & Index\" cellspacing=1 bgcolor=\"" . $color["title"] . "\">";
		echo "<tr><a name=playlists></a>";
		echo "<td nowrap>";
		echo "<b>Saved Playlists</b>";
		printIndex($pindex,"","p");
		if(strcmp($delete,"yes") && $rmperm)
		{
		        echo "&nbsp;<small>(<a title=\"Goto Delete Playlist Menu\" href=\"index.php?body=main&amp;delete=yes&amp;server=$server#playlists\">delete</a>)</small>";
		}

		echo "</td></tr>";
		echo "<tr><td>";
		echo "<table summary=\"Playlist\" cellspacing=1 bgcolor=\"" . $color["body"][0] . "\">";

		// Begin for playlist
		for ($i=0;$i<count($pprint);$i++)
		{
		        echo "<tr bgcolor=\"" . $color["body"][$i%2] . "\">";
			echo "<td>" . $pprint[$i] . "</td>";
			echo "</tr>";
		}
		echo "</table>";
		echo "</td></tr></table>";
	}
}

function songInfo2Display($song_info)
{
	global $config;
	if (preg_match("/^[a-z]*:\/\//",$song_info["file"]))
	{
		$song = $song_info["file"];
	}
	else
	{
		$song_array = split("/",$song_info["file"]);
		$song = $song_array[count($song_array)-1];
	}
	if (strcmp($config["filenames_only"],"yes") && isset($song_info["Title"]) && $song_info["Title"])
	{
		if (isset($song_info["Artist"]))
		{
		        $artist = $song_info["Artist"];
		}
		else
		{
		        $artist = "";
		}

		if (isset($song_info["Title"]))
		{
		        $title = $song_info["Title"];
		}
		else
		{
		        $title = "";
		}

		if (isset($song_info["Album"]))
		{
		        $album = $song_info["Album"];
		}
		else
		{
		        $album = "";
		}

		if (isset($song_info["Genre"]))
		{
			$genre = $song_info["Genre"];
		}
		else
		{
			$genre = "";
		}

		if (isset($song_info["Date"]))
		{
			$date = $song_info["Date"];
		}
		else
		{
			$date = "";
		}

		if (isset($song_info["Track"]))
		{
		        $track = $song_info["Track"];
		}
		else
		{
		        $track = "";
		}

		$trans = array("artist" => $artist, "title" => $title, "album" => $album, "track" => $track, "genre" => $genre, "date" => $date);
		
		// If it doesn't exist don't print it, stupid
		if (strlen($artist)==0 && ! strlen($title)==0)
		{
		        $song_display_conf = $config["display_conf"]["title"];
		}
		else if (strlen($title)==0 && !strlen($artist)==0)
		{
		        $song_display_conf = $config["display_conf"]["artist"];
		}
		else
		{
		        $song_display_conf = $config["display_conf"]["artist"]. $config["display_conf"]["seperator"] . $config["display_conf"]["title"];
		}
		$song_display = strtr($song_display_conf, $trans);
	}
	else if ($config["filenames_only"]!="yes" && isset($song_info["Name"]) && $song_info["Name"])
	{
		$song_display = $song_info["Name"];
	}
	else
	{
		// Let's not regex urls
		if (! preg_match("/^(http|ftp|https):\/\/.*/",$song))
		{
			for($i=0;$i<sizeOf($config["regex"]["remove"]);$i++)
			{
				$song = str_replace($config["regex"]["remove"][$i],'',$song);
			}
			if(strcmp($config["regex"]["space"],"yes")==0)
			{
				$song = str_replace('_',' ',$song);
			}
			if(strcmp($config["regex"]["uppercase_first"],"yes")==0)
			{
				$song = ucwords($song);
			}
		}
		$song_display = $song;
	}
	if($config["wordwrap"]>0)
	{
		$song_display = wordwrap($song_display, $config["wordwrap"], "<br />",1);
	}
	return $song_display;
}
?>
