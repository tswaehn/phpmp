<?php
include "sort.php";

function lsinfo2playlistTable($lsinfo,$sort)
{
	global $server;
	$pic = 0;
	$pcount = count($lsinfo["playlist"]);
	if ($pcount) usort($lsinfo["playlist"],"strcasecmp");
	for ($i=0;$i<$pcount;$i++)
	{
		$dirent = $lsinfo["playlist"][$i];
		$dirstr = $dirent;
		$dirss = split("/",$dirstr);
		if (count($dirss)==0)
			$dirss[0] = $dirstr;
		$dirss[0] = $dirss[count($dirss)-1];
		$dirstr = sanitizeForURL($dirstr);
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
		$pprint[$i].="[<a target=\"playlist\" href=\"playlist.php?server=$server&amp;command=load&amp;arg=$dirstr\">load</a>] $dirss[0] (<small><a href=\"main.php?server=$server&amp;sort=$sort&amp;command=rm&amp;arg=$dirstr\">d</a>elete</small>)<br>\n";
	}
	if (!isset($pprint)) $pprint = array();
	if (!isset($pindex)) $pindex = array();
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

function lsinfo2musicTable($lsinfo,$sort,$dir_url)
{
	global $sort_array, $song_seperator, $filenames_only,$colors;
	global $unknown_string, $display_fields, $server;
	$color = $colors["music"]["body"];
	$mic = 0;
	$mcount = count($lsinfo["music"]);
	//echo '<pre>';print_r($lsinfo['music']);echo '</pre>';exit;
	if ($mcount) usort($lsinfo["music"],"msort");
	$add_all = "";
	
	for ($i=0;$i<$mcount;$i++)
	{
		$dirent = $lsinfo["music"][$i]["file"];
		$dirstr = $dirent;
		$dirss = split("/",$dirstr);
		if (count($dirss)==0)
			$dirss[0] = $dirstr;
		$dirss[0] = $dirss[count($dirss)-1];
		if ($i<$mcount-1) $add_all .= addslashes($dirstr) . $song_seperator;
		else $add_all .= $dirstr;
		$dirstr = sanitizeForURL($dirstr);
		$col = $color[$i%2];
		if ($filenames_only!="yes" && isset($lsinfo["music"][$i]["Title"]) && $lsinfo["music"][$i]["Title"])
		{
			if (strcmp($sort_array[0],"Track"))
			{
				if (isset($lsinfo["music"][$i][$sort_array[0]]) && strlen($lsinfo["music"][$i][$sort_array[0]]) && ($mic==0 || $mindex[$mic-1]!=strtoupper(mbFirstChar($lsinfo["music"][$i][$sort_array[0]]))))
				{
					$mindex[$mic] = strtoupper(mbFirstChar($lsinfo["music"][$i][$sort_array[0]]));
					$foo = $mindex[$mic];
					$mic++;
					$mprint[$i] = "<a name=m$foo></a>";
				}
				else
				{
					$mprint[$i] = "";
				}
			}
			else
			{
				if (isset($foo)) unset($foo);
				if (isset($lsinfo["music"][$i][$sort_array[0]]))
				{
					$foo = strtok($lsinfo["music"][$i][$sort_array[0]],"/");
				}
				if (isset($foo) && ($mic==0 || 0!=strcmp($mindex[$mic-1],$foo)))
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
			$mprint[$i] = "<tr bgcolor=$col><td width=0>$mprint[$i][<a target=\"playlist\" href=\"playlist.php?server=$server&amp;command=add&amp;arg=$dirstr\">add</a>]</td>";
			for ($x = 0; $x < sizeof($display_fields); $x++)
			{
				$mprint[$i] .= "<td>";
				switch ($display_fields[$x])
				{
				case 'Album':
				case 'Artist':
					if (!isset($lsinfo["music"][$i][$display_fields[$x]]))
					{
						$mprint[$i].= $unknown_string;
					}
					else
					{
						$url = sanitizeForURL($lsinfo["music"][$i][$display_fields[$x]]);
						$mprint[$i].= "<a href=\"find.php?server=$server&amp;find=" . strtolower($display_fields[$x]) . "&amp;arg=$url&amp;sort=$sort&amp;dir=$dir_url\">";
						$mprint[$i].= $lsinfo["music"][$i][$display_fields[$x]] . "</a>";
					}
					break;
				case 'Title':
					$mprint[$i].= $lsinfo["music"][$i][$display_fields[$x]];
					break;
				case 'Track':
					if (!isset($lsinfo["music"][$i][$display_fields[$x]]))
					{
						$mprint[$i].= $unknown_string;
					}
					else
					{
						$mprint[$i].= $lsinfo["music"][$i][$display_fields[$x]];
					}
					break;
				case 'Time':
					if (!isset($lsinfo["music"][$i][$display_fields[$x]]))
					{
						$mprint[$i].= $unknown_string;
					}
					else
					{
						$mprint[$i].= display_time($lsinfo["music"][$i][$display_fields[$x]]);
					}
					break;
				default:
					$mprint[$i] .= "Config Erorr";
					break;
				}
				$mprint[$i] .= "</td>";
			}
		}
		else
		{
			if ($mic==0 || $mindex[$mic-1]!=strtoupper($dirss[0][0]))
			{
				$mindex[$mic] = strtoupper($dirss[0][0]);
				$foo = $mindex[$mic];
				$mic++;
				$mprint[$i] = "<a name=m$foo></a>";
			}
			else
			{
				$mprint[$i] = "";
			}
			if ($display_fields[sizeof($display_fields) - 1] == 'Time')
			{
				$mprint[$i] = "<tr bgcolor=$col><td>$mprint[$i][<a target=\"playlist\" href=\"playlist.php?server=$server&amp;command=add&amp;arg=$dirstr\">add</a>]</td><td colspan=" . (sizeof($display_fields) - 1) . ">$dirss[0]</td><td>";
				if (!isset($lsinfo["music"][$i]['Time']))
				{
					$mprint[$i].= $unknown_string;
				}
				else
				{
					$mprint[$i].= display_time($lsinfo["music"][$i]['Time']);
				}
				$mprint[$i] .= "</td></tr>\n";
			}
			else
			{
				$mprint[$i] = "<tr bgcolor=$col><td>$mprint[$i][<a target=\"playlist\" href=\"playlist.php?server=$server&amp;command=add&amp;arg=$dirstr\">add</a>]</td><td colspan=" . sizeof($display_fields) . ">$dirss[0]</td></tr>\n";
			}
		}
	}
	if (!isset($mprint)) $mprint = array();
	if (!isset($mindex)) $mindex = array();
	return array($mprint,$mindex,$add_all);
}

function printIndex($index,$title,$anc)
{
	if (count($index))
	{
		print "$title: [ ";
		for ($i=0;$i<count($index);$i++)
		{
			$foo = $index[$i];
			print "<a href=\"#$anc$foo\">$foo</a>\n";
		}
		print "]<br>\n";
	}
}

function printMusicTable($mprint,$url,$add_all,$mindex)
{
	global $filenames_only, $colors, $use_javascript_add_all,$sort_array;
	global $display_fields, $server;
	if (count($mprint)>0)
	{
		print "<br>\n";
		if ($use_javascript_add_all=="yes")
		{
			$add_all = sanitizeForPost($add_all);
			print "<form style=\"padding:0;margin:0;\" name=\"add_all\" method=\"post\" action=\"playlist.php\" target=\"playlist\">";
			print "<input type=hidden name=\"add_all\" value=\"$add_all\">";
			print "<input type=hidden name=\"server\" value=\"$server\">";
			print "<table border=0 cellspacing=1 bgcolor=\"";
			print $colors["music"]["title"];
			print "\" width=\"100%\">\n";
			print "<tr><a name=music></a><td colspan=4 nowrap><b>Music</b>\n";
			print "(<a href=\"javascript:document.add_all.submit()\">";
			print "add all</a>)\n";
			printIndex($mindex,"","m");
			print "</td></tr>\n";
		}
		else
		{
			$add_all = sanitizeForUrl($add_all);
			print "<table border=0 cellspacing=1 bgcolor=\"";
			print $colors["music"]["title"];
			print "\" width=\"100%\">\n";
			print "<tr><td colspan=4><b>Music</b>\n";
			print "(<a target=\"playlist\" href=\"playlist.php?server=$server&amp;add_all=$add_all\">";
			print "add all</a>)\n";
			printIndex($mindex,"","m");
			print "</td></tr>\n";
		}
		print "<tr><td>\n";
		print "<table border=0 cellspacing=1 bgcolor=\"";
		print $colors["music"]["body"][1];
		print "\" width=\"100%\">\n";
		if ($filenames_only!="yes")
		{
			print "<tr bgcolor=\"";
			print $colors["music"]["sort"];
			print "\"><td width=0></td>";
			for ($i=0;$i<count($display_fields);$i++)
			{
				$new_sort = pickSort("$display_fields[$i]");
				print "<td><a href=\"$url&amp;sort=$new_sort&amp;server=$server\">" . (($display_fields[$i] == $sort_array[0]) ? '<b>' . $display_fields[$i] . '</b>' : $display_fields[$i]) . '</a></td>';
			}
			print "</tr>\n";
		}
		for ($i=0;$i<count($mprint);$i++) print $mprint[$i];
		print "</td></tr></table>\n";
		print "</table>\n";
		if ($use_javascript_add_all=="yes")
			print "</form>";
	}
}

function printPlaylistTable($pprint,$pindex)
{
	global $colors;
	if (count($pprint))
	{
		print "<br>\n";
		print "<table border=0 cellspacing=1 bgcolor=\"";
		print $colors["playlist"]["title"];
		print "\" width=\"100%\">\n";

		print "<tr><a name=playlists></a><td nowrap><b>Playlists</b>";
		printIndex($pindex,"","p");
		print "</td></tr>\n";
		print "<tr bgcolor=\"";
		print $colors["playlist"]["body"];
		print "\"><td>\n";
		for ($i=0;$i<count($pprint);$i++) print $pprint[$i];
		print "</td></tr></table>\n";
	}
}

function songInfo2Display($song_info)
{
	global $song_display_conf, $filenames_only;
	if (preg_match("/^[a-z]*:\/\//",$song_info["file"]))
	{
		$song = $song_info["file"];
	}
	else
	{
		$song_array = split("/",$song_info["file"]);
		$song = $song_array[count($song_array)-1];
	}
	if ($filenames_only!="yes" && isset($song_info["Title"]) && $song_info["Title"])
	{
		if (isset($song_info["Artist"])) $artist = $song_info["Artist"];
		else $artist = "";
		if (isset($song_info["Title"]))	$title = $song_info["Title"];
		else $title	= "";
		if (isset($song_info["Album"]))	$album = $song_info["Album"];
		else $album	= "";
		if (isset($song_info["Track"]))	$track = $song_info["Track"];
		else $track	= "";
		$trans = array("artist" => $artist, "title" => $title, "album" => $album, "track" => $track);
		$song_display = strtr($song_display_conf, $trans);
	}
	else if ($filenames_only!="yes" && isset($song_info["Name"]) && $song_info["Name"])
	{
		$song_display = $song_info["Name"];
	}
	else
	{
		$song_display = $song;
	}
	return $song_display;
}
?>
