<?php
include "theme.php";
$phpMpVersion="0.12.0-svn";

 /*
  *	$vars => An array of the get variables to be checked
  *	$num => How many variables there are (so extras aren't added for security)
  */
function setupReceivedVars( $vars, $num )
{
	$i = 0;
	foreach ( $vars as $key )
	{
		$i++;
		if( isset( $_GET[$key] ))
		{
			$vars[$key] = $_GET[$key];
		}
		else if( isset( $_POST[$key] ))
		{
			$vars[$key] = $_POST[$key];
		}
	}
	if( $num == $i )
	{
		return $vars;
	}
	else
	{
		echo "Incorrect number of in \$num at setupGet";
		exit; 
	}
}

/*
   The crop function deletes all but the currently playing song
   from the current playlist

   $fp => connection
   $current => value of the current song (starts at '0')
   $playlistlength => value of the number of songs in the
      current playlst (starts at '1')
*/
function crop($fp,$current,$playlistlength)
{
        $playlistlength -= 1;

	$str = "command_list_begin\n";
	for( $playlistlength; $playlistlength >= "0"; $playlistlength-- )
	{
	        if( strcmp( $current, $playlistlength ))
		{
			$str .= "delete $playlistlength\n";
		}
	}
	fputs( $fp, $str . "command_list_end\n" );

	initialConnect( $fp );
	return;
}

function initialConnect( $fp )
{
	while( ! feof( $fp ))
	{
		$got =  fgets( $fp, "1024" );
		if( strncmp( "OK", $got, strlen( "OK" )) == "0" )
		{
		        $MPDversion = preg_replace( "/^OK MPD /", "", $got );
			break;
		}
		if( strncmp( "ACK", $got, strlen( "ACK" )) == "0")
		{
			echo "$got<br>";
			break;
		}
	}
	return $MPDversion;
}

function doCommand( $fp, $arg, $arg2, $command, $overwrite, $status )
{
	// Don't let updates collide 
 	if( isset ( $status["updating_db"] ) && strcmp( $command,"update" )==0 )
	{
		return 0;
	}

	// Lets cleanup the $arg first
	$arg = rawurldecode( $arg );

	// This is to facilitate for overwriting playlists, it should probably detect to see if
	// it exists first, but who cares, right?
        if( strncmp( "save", $command, strlen( "save" )) == "0" && strcmp( $overwrite, "yes" ) == "0" )
	{
		fputs( $fp, "rm \"$arg\"\n" );
		fgets( $fp, "1024");
	}

	// Cannot use empty() here because when the argument == 0 it returns false.
	if ( strlen( $arg2 ) > "0" )
	{
		$command.= " \"$arg\" \"$arg2\"";
	}
	else if( strlen( $arg ) > "0" )
	{
	        $command.= " \"$arg\"";
        }

        // By the time you read this the 'kill' command will hopefully be a non-default compile time 
	// option. $arg is also used here, so to make sure someone doesn't kill your MPD leave it.
	if (strncmp("kill",$command,strlen("kill")) || (! isset($command) && strncmp("kill",$arg,strlen("kill"))))
	{
	          fputs( $fp, "$command\n" );
	}

	while( ! feof( $fp ))
	{
	        $got = fgets( $fp, "1024" );
		if( strncmp( "OK", $got, strlen( "OK" )) == "0" ) 
		{
		        break;
		}
		str_replace( "\n", "\n<br>", $got );

		// Otherwise it will output how many times it's updated
		if ( strncmp( "update", $command, strlen( "update" )))
		{
		        echo "<a target=main><b><br>Error! $got<br></b></a><br>\n";
                }
	
		if ( strncmp( "ACK", $got, strlen( "ACK" )) == "0" ) 
		{
		        break;
		}
	}
}

function displayDirectory( $dir, $dir_url, $sort, $title, $music, $playlists, $has_password, $dcount, $commands, $color, $server, $servers, $fp, $passarg, $ordered )
{
	echo "<!-- Begin displayDirectory  -->";
	// The next line needs a cellspacing value of 2 since the other tables have 2 tables, and this one only has one
	echo "<table summary=\"Directory\"cellspacing=2 bgcolor=\"" . $color["title"] . "\">";
	echo "<tr><td><b>$title</b>";
	echo "&nbsp;";
	if ( $music && ( $dcount > "0" ))
	{
	        echo "<small>(<a href=\"#music\">Music</a>)</small>&nbsp;";
	}

	if ( isset( $playlists ) && $playlists > "0" )
	{
	        echo "<small>(<a title=\"Jump to Saved Playlists\" href=\"#playlists\">Saved Playlists</a>)</small>&nbsp;";
	}

	echo "</td>";
	echo "<td align=right><small>";

	$feature_bar = "";

	// If we have all commands available and we don't have an active password don't show the feature bar.
	if ( $commands["all"] == "0" || strlen( $passarg ) > "0" )
	{
		if( $has_password == "1" )
		{
			$feature_bar .= "<a title=\"Logout of MPD Server\" target=_top href=\"index.php?server=$server&amp;dir=$dir_url&amp;sort=$sort&amp;logout=1\">Logout</a>";
		}
		else
		{
			$feature_bar .= "<a title=\"Login to MPD Server\" target=main href=\"index.php?body=main&amp;feature=login&amp;server=$server&amp;dir=$dir_url&amp;sort=$sort\">Login</a>";
		}
	}

	if( $commands["outputs"] == "1" )
	{
		if( ! empty ( $feature_bar ))
		{
			$feature_bar .= "&nbsp;|&nbsp;";
		}
		$feature_bar .= "<a title=\"View the Sound Outputs\" href=\"index.php?body=main&amp;server=$server&amp;dir=$dir_url&amp;sort=$sort&amp;feature=outputs&amp;dir=$dir_url\">Outputs</a>";
	}

	if( $commands["search"] == "1" )
	{
		if( ! empty ( $feature_bar ))
		{
			$feature_bar .= "&nbsp;|&nbsp;";
		}
		$feature_bar .= "<a title=\"Search the MPD Database\" href=\"index.php?body=main&amp;server=$server&amp;dir=$dir_url&amp;sort=$sort&amp;feature=search\">Search</a>";
	}

	if (isset($servers) && (sizeof($servers) > 1))
	{
		if( ! empty ( $feature_bar ))
		{
			$feature_bar .= "&nbsp;|&nbsp;";
		}
		$feature_bar .= "<a title=\"Change the MPD Server\" href=\"index.php?body=main&amp;server=$server&amp;sort=$sort&amp;dir=$dir_url&amp;feature=server\">Servers</a>";
	}

	if( $commands["stats"] == "1" )
	{
		if( ! empty ( $feature_bar ))
		{
			$feature_bar .= "&nbsp;|&nbsp;";
		}
		$feature_bar .= "<a title=\"View MPD/phpMp Statistics\" target=main href=\"index.php?body=main&amp;server=$server&amp;dir=$dir_url&amp;sort=$sort&amp;feature=stats\">Stats</a>";
	}

	if( $commands["load"] == "1" )
	{
		if( ! empty ( $feature_bar ))
		{
			$feature_bar .= "&nbsp;|&nbsp;";
		}
		$feature_bar .= "<a title=\"Add a Stream or Playlist of Streams to the Active Playlist\" href=\"index.php?body=main&amp;server=$server&amp;dir=$dir_url&amp;sort=$sort&amp;feature=stream\">Stream</a>";
	}

	if( isset( $feature_bar ))
	{
		echo $feature_bar;
	}

	echo "&nbsp";
	echo "</small></td></tr>";
	echo "<tr bgcolor=\"" . $color["body"][0] . "\"><td colspan=2>";
	$dirs = split( "/", $dir );
	echo "<a title=\"Back to the Root Music Directory\" href=\"index.php?body=main&amp;server=$server&amp;sort=$sort&amp;ordered=$ordered\">Music</a>";
	$build_dir = "";
	for( $i=0; $i < (count( $dirs ) - 1); $i++ )
	{
		if ($i > "0" && $i < (count($dirs)-1))
		{
		        $build_dir.="/";
		}
		$dirs[$i] = stripslashes( $dirs[$i] );
		$build_dir.="$dirs[$i]";
		$build_dir = rawurlencode( $build_dir );
		echo " / ";
		echo "<a title=\"Jump to " . $dirs[$i]  . "\" href=\"index.php?body=main&amp;server=$server&amp;sort=$sort&amp;ordered=$ordered&amp;dir=$build_dir\">$dirs[$i]</a>";
	}

	if ( $i > "0" )
	{
	        $build_dir.="/";
	}

	if ( ! empty( $dir ))
	{
		$dirs[$i] = stripslashes( $dirs[$i] );
		$build_dir.=$dirs[$i];
		$build_dir = rawurlencode( $build_dir );
		echo " / ";
		echo "<a title=\"Jump to " . $dirs[$i]  . "\" href=\"index.php?body=main&amp;server=$server&amp;sort=$sort&amp;ordered=$ordered&amp;dir=$build_dir\">$dirs[$i]</a>";
	}

	$status = getStatusInfo( $fp );
	if( isset( $status["updating_db"] ))
	{
		echo "&nbsp;&nbsp;<small>(db updating...)</small>";
	}
	else if( strcmp( $title, "Current Directory" ) == "0" && $commands["update"] == "1" )
	{
		$dirs[$i] = stripslashes( $dirs[$i] );
		$build_dir = rawurlencode( $build_dir );
		echo "&nbsp;&nbsp;<small>(<a href=\"index.php?body=main&amp;server=$server&amp;dir=$build_dir&amp;sort=$sort&amp;ordered=$ordered&amp;command=update&amp;arg=$build_dir/\"";
		echo "target=\"main\" title=\"Update the Current Directory\">db update</a>)</small>";
	}
	echo "</td></tr></table>";
	echo "<!-- End displayDirectory -->";
}

function mbFirstChar( $str )
{
	$i = 1;
	$ret = "$str[0]";
	while ($i < strlen( $str ) && ord( $str[$i] ) >= 128  && ord( $str[$i] ) < 192)
	{
		$ret .= $str[$i];
		$i++;
	}
	return $ret;
}

function readFileOverHTTP( $fp, $stream )
{
	$stream = rawurldecode( $stream );
	$pointer = fopen( $stream, "r" ) or die ( "<H3><b>If you want phpMp to download your stream, you have to change 'allow_url_open' to On in your php.ini</b></H3>");
	$contents = fread( $pointer, "1024" );
	if( preg_match( "/.pls$/", $stream ))
	{
		preg_match_all( "/File[0-9]*=(.*?)\n/", $contents, $out );
		$streams = $out[1];
	}
	else
	{
		if( strcmp( $contents, " " ))
		{
			$streams = explode( " ", $contents );
		}
		else
		{
			$streams = $contents;
		}
	}
	return($streams);	
}

function postStream( $fp, $filetype )
{
	$add = array();
	$i = 0;
        
	while ( ! feof( $fp ))
	{
		$url = fgets( $fp, 4096 );
		if( strcmp( $filetype, "pls" ) && preg_match( "/File[0-9]*=/", $url ))
		{
			$url = preg_replace( "/^File[0-9]*=/", "", $url );
                }
		$url = preg_replace( "/\n$/", "", $url );
		if ( preg_match( "/^[a-z]*:\/\//", $url ))
		{
			$add[$i] = $url;
			$i++;
		}
	}
	return $add;
}

function startElementHandler( $parser, $element_name, $element_attribs )
{
	global $server_count;
	global $server_data;
	global $xml_current_tag_state;
	if( $element_name == "ENTRY" && isset ( $element_attribs["ALIGNMENT"] ))
	{
		$server_data[$server_count]["alignment"] = $element_attribs["ALIGNMENT"];
	}
	else
	{
		$xml_current_tag_state = $element_name;
	}
}

function endElementHandler( $parser, $element_name )
{
	global $server_count;
	global $server_data;
	global $xml_current_tag_state;

	$xml_current_tag_state = '';
	if( $element_name == "ENTRY" )
	{
		$server_count++;
	}
}

function characterDataHandler( $parser , $data )
{
	global $server_count;
	global $server_data;
	global $xml_current_tag_state;

	if( $xml_current_tag_state == '' )
	{
		return;
	}


	if( $xml_current_tag_state == "SERVER_NAME" )
	{
		$server_data[$server_count]["server_name"] = $data;
	}
	else if( $xml_current_tag_state == "LISTEN_URL" )
	{
		// This is here because if a '&' (maybe other special characters) 
		// is passed to the XML parser it will do a multipass on it.		
		if( isset( $server_data[$server_count]["listen_url"] ))
		{
			$server_data[$server_count]["listen_url"] .= $data;
		}
		else
		{
			$server_data[$server_count]["listen_url"] = $data;
		}
	}
	else if( $xml_current_tag_state == "SERVER_TYPE" )
	{
		$server_data[$server_count]["server_type"] = $data;
	}
	else if	( $xml_current_tag_state == "BITRATE" )
	{
		$server_data[$server_count]["bitrate"] = $data;
	}
	else if( $xml_current_tag_state == "CHANNELS" )
	{
		$server_data[$server_count]["channels"] = $data;
	}
	else if( $xml_current_tag_state == "SAMPLERATE" )
	{
		$server_data[$server_count]["samplerate"] = $data;
	}
	else if( $xml_current_tag_state == "GENRE" )
	{
		$server_data[$server_count]["genre"] = $data;
	}
	else if( $xml_current_tag_state == "CURRENT_SONG" )
	{
		$server_data[$server_count]["current_song"] = $data;
	}
	else if( $xml_current_tag_state == "RANK" )
	{
		$server_data[$server_count]["rank"] = $data;
	}
	else if( $xml_current_tag_state == "STREAM_HOMEPAGE" )
	{
		$server_data[$server_count]["stream_homepage"] = $data;
	}
	else if( $xml_current_tag_state == "LISTENING" )
	{
		$server_data[$server_count]["listening"] = $data;
	}
	else if( $xml_current_tag_state == "MAX_LISTENERS" )
	{
		$server_data[$server_count]["max_listeners"] = $data;
	}
	else if( $xml_current_tag_state == "BITRATE" )
	{
		$server_data[$server_count]["max_listeners"] = $data;
	}
}

?>
