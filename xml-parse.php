<?php

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