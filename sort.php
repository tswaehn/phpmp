<?php
function msort($a,$b)
{
	global $sort_array, $config;
	$i=0;
	$ret = 0;
	while ($config["filenames_only"] != "yes" && $i < 5 && $ret == 0)
	{
		if (!isset($a[$sort_array[$i]]) && isset($b[$sort_array[$i]]))
		{
			$ret = -1;
		}
		else if (!isset($b[$sort_array[$i]]))
		{
			$ret = 1;
		}
		else if (strcmp($sort_array[$i],"Track")==0 || strcmp($sort_array[$i],"Time")==0)
		{
			$ret = strnatcmp($a[$sort_array[$i]],$b[$sort_array[$i]]);
		}
		else
		{
			$ret = strcasecmp($a[$sort_array[$i]],$b[$sort_array[$i]]);
		}
		$i++;
	}
	if ($ret == 0)
	{
		$ret = strcasecmp($a["file"],$b["file"]);
	}
	return $ret;
}

function pickSort($pick)
{
	global $sort_array;
	switch($pick)
	{
		case $sort_array[0]:
			return "$sort_array[0],$sort_array[1],$sort_array[2],$sort_array[3],$sort_array[4]";
			break;
		case $sort_array[1]:
			return "$pick,$sort_array[0],$sort_array[2],$sort_array[3],$sort_array[4]";
			break;
		case $sort_array[2]:
			return "$pick,$sort_array[0],$sort_array[1],$sort_array[3],$sort_array[4]";
			break;
		case $sort_array[3]:
			return "$pick,$sort_array[0],$sort_array[1],$sort_array[2],$sort_array[4]";
			break;
		case $sort_array[4]:
			return "$pick,$sort_array[0],$sort_array[1],$sort_array[2],$sort_array[3]";
			break;
	}
}
?>
