<?php
function msort( $a, $b )
{
	global $sort_array, $config, $ordered;
	$i = "0";
	$ret = "0";

	// While not filenames_only, while in the first 7 sort_arrays and if ret is 0
	while( strcmp( $config["filenames_only"], "yes" ) && $i < "7" && $ret == "0" )
	{
		if( ! isset( $a[ ($sort_array[$i]) ] ) && isset( $b[ ($sort_array[$i]) ] ))
		{
			$ret = -1;
		}
		else if( ! isset( $b[ ($sort_array[$i]) ] ))
		{
			$ret = 1;
		}
		else if( strcmp( $sort_array[$i], "Track" ) == "0" || strcmp( $sort_array[$i], "Time" ) == "0" )
		{
			if( strcmp( $ordered, "yes" ))
			{
				$ret = strnatcmp( $a[ ($sort_array[$i]) ], $b[ ($sort_array[$i]) ] );
			}
			else
			{
				$ret = strnatcmp( $b[ ($sort_array[$i]) ], $a[ ($sort_array[$i]) ] );
			}

		}
		else
		{
			if( strcmp( $ordered, "yes" ))
			{
				$ret = strcasecmp( $a[ ($sort_array[$i]) ], $b[ ($sort_array[$i]) ] );
			}
			else
			{
				$ret = strcasecmp( $b[ ($sort_array[$i]) ], $a[ ($sort_array[$i]) ] );
			}
		}
		$i++;
	}
	if ( $ret == "0" )
	{
		$ret = strcasecmp( $a["key"], $b["key"] );
	}
	return $ret;
}

function pickSort( $pick )
{
	global $sort_array;
	switch( $pick )
	{
		case $sort_array[0]:
			return "$pick,$sort_array[1],$sort_array[2],$sort_array[3],$sort_array[4],$sort_array[5],$sort_array[6]";
			break;
		case $sort_array[1]:
			return "$pick,$sort_array[0],$sort_array[2],$sort_array[3],$sort_array[4],$sort_array[5],$sort_array[6]";
			break;
		case $sort_array[2]:
			return "$pick,$sort_array[0],$sort_array[1],$sort_array[3],$sort_array[4],$sort_array[5],$sort_array[6]";
			break;
		case $sort_array[3]:
			return "$pick,$sort_array[0],$sort_array[1],$sort_array[2],$sort_array[4],$sort_array[5],$sort_array[6]";
			break;
		case $sort_array[4]:
			return "$pick,$sort_array[0],$sort_array[1],$sort_array[2],$sort_array[3],$sort_array[5],$sort_array[6]";
			break;
		case $sort_array[5]:
			return "$pick,$sort_array[0],$sort_array[1],$sort_array[2],$sort_array[3],$sort_array[4],$sort_array[6]";
			break;
		case $sort_array[6]:
			return "$pick,$sort_array[0],$sort_array[1],$sort_array[2],$sort_array[3],$sort_array[4],$sort_array[5]";
			break;
	}
}
?>
