<?php
$server_count = 0;
$server_data = array();
$xml_current_tag_state = '';
$baseline = array();

$real_path = realpath(".");
$cache_dir = "{$real_path}/cache";
$icy_file = "{$cache_dir}/stream-icy.xml";
$shout_file = "{$cache_dir}/stream-shout.xml.gz";

function troubleshooting ($updating) {
	echo "You are having problems dealing with streams. Here are some troubleshooting steps to fix this.";
	echo "1) allow_url_fopen = On must be set in php.ini";
	echo "2) Check permissions of the cache/ directory";

	echo "For phpMp controlled updating/download you have to have:";
	echo "1) php must be compiled with gzip and xml";
	if(strcmp($updating,"yes")==0) {
		echo "2) stream_browser_updating must be set to \"yes\" in your config.php";
	}
	die("phpMp killed.");
}

if(strcmp($config["stream_browser_updating"],"yes") == 0) {
	if( strcmp( $feature, "stream-icy" ) == "0" && (! is_file($icy_file) || strcmp($arg,'update') == 0)) {
		if(!is_dir($cache_dir)) mkdir($cache_dir);

		if(is_file($icy_file)) {
			unlink($icy_file);
		}
		if(!copy( $config['icey_stream_url'], $icy_file )) {
			troubleshooting($config["stream_browser_updating"]);
		}
	} else if( strcmp( $feature, "stream-shout" ) == "0" && (! is_file($shout_file) || strcmp($arg,'update') == 0)) {
		if(!is_dir($cache_dir)) mkdir($cache_dir);

		if(is_file($shout_file)) {
			unlink($shout_file);
		}
		if(!copy( $config['shout_stream_url'], $shout_file )) {
			troubleshooting($config["stream_browser_updating"]);
		}
	}
} else {
	if(!is_file($icy_file) && strcmp($feature,"stream-icy") == 0) {
		echo "Access denied: {$icy_file} doesn't exist, must be manually downloaded if \"stream_browser_updating\" isn't set to \"yes\"";
		troubleshooting($config["stream_browser_updating"]);
	} else if (!is_file($shout_file) && strcmp($feature,"stream-shout") == 0) {
		echo "Access denied: {$shout_file} doesn't exist, must be manually downloaded if \"stream_browser_updating\" isn't set to \"yes\"";
		troubleshooting($config["stream_browser_updating"]);
	}
}

if( strcmp( $feature, "stream-icy" ) == "0" && is_dir($cache_dir) && is_file($icy_file) )
{
	$fh = fopen( $icy_file, "r" );
}
else if( strcmp( $feature, "stream-shout") == "0" && is_dir($cache_dir) && is_file($shout_file) )
{
	$fh = gzopen( $shout_file, "r" );
}

if( ! is_resource( $fh ))
{
	echo "If you want phpMp to download your stream, you have to: <br>";
	echo "1: Change 'allow_url_fopen' to 'On' in your php.ini<br>";
	echo "2: Ensure that you have a directory cache/ that is owned by your webserver's user";
	if( strcmp($config['stream_browser'],"yes") != 0) {
		echo "Also, you need to change \$config['stream_browser'] to 'yes' in your config.php";
	}
	die ($fh);
}

if( ! ( $xml_parser = xml_parser_create() ))
{
	die( "Couldn't create XML parser!" );
}

xml_set_element_handler( $xml_parser, "startElementHandler", "endElementHandler" );
xml_set_character_data_handler( $xml_parser, "characterDataHandler" );

while( is_resource($fh) && $data = fread( $fh, "4096" ))
{
	if( ! xml_parse( $xml_parser, $data, feof( $fh ) ))
	{
		break; // get out of while loop if we're done with the file
	}
}

xml_parser_free( $xml_parser );

if( is_resource( $fh )) {
	fclose( $fh );
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
	global $baseline;

	if( $xml_current_tag_state == '' )
	{
		return;
	}

	if( $xml_current_tag_state == "SERVER_NAME" )
	{
		$baseline["server_name"] = 1;
		$server_data[$server_count]["server_name"] = $data;
	}
	else if( $xml_current_tag_state == "LISTEN_URL" )
	{
		$baseline["listen_url"] = 1;
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
		$baseline["server_type"] = 1;
		$server_data[$server_count]["server_type"] = $data;
	}
	else if	( $xml_current_tag_state == "BITRATE" )
	{
		$baseline["bitrate"] = 1;
		$server_data[$server_count]["bitrate"] = $data;
	}
	else if( $xml_current_tag_state == "CHANNELS" )
	{
		$baseline["channels"] = 1;
		$server_data[$server_count]["channels"] = $data;
	}
	else if( $xml_current_tag_state == "SAMPLERATE" )
	{
		$baseline["samplerate"] = 1;
		$server_data[$server_count]["samplerate"] = $data;
	}
	else if( $xml_current_tag_state == "GENRE" )
	{
		$baseline["genre"] = 1;
		$server_data[$server_count]["genre"] = $data;
	}
	else if( $xml_current_tag_state == "CURRENT_SONG" )
	{
		$baseline["current_song"] = 1;
		$server_data[$server_count]["current_song"] = $data;
	}
	else if( $xml_current_tag_state == "RANK" )
	{
		$baseline["rank"] = 1;
		$server_data[$server_count]["rank"] = $data;
	}
	else if( $xml_current_tag_state == "STREAM_HOMEPAGE" )
	{
		$baseline["stream_homepage"] = 1;
		$server_data[$server_count]["stream_homepage"] = $data;
	}
	else if( $xml_current_tag_state == "LISTENING" )
	{
		$baseline["listening"] = 1;
		$server_data[$server_count]["listening"] = $data;
	}
	else if( $xml_current_tag_state == "MAX_LISTENERS" )
	{
		$baseline["max_listeners"] = 1;
		$server_data[$server_count]["max_listeners"] = $data;
	}
	else if( $xml_current_tag_state == "BITRATE" )
	{
		$baseline["bitrate"] = 1;
		$server_data[$server_count]["bitrate"] = $data;
	}
}
?>