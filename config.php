<?php

// this is a list of your MusicDB (MDB) servers
// if you only have one then only have one line, otherwise have a seperate line
// for each of your servers you want to be able to control via the web interface
// the fields in the array are:  hostname, port, description
// the description field is only really useful if you have multiple servers
$servers[] = array('localhost', 6600, 'Stereo');
//$servers[] = array('192.168.0.40', 6600, 'Livingroom');

// OPTIONAL
$title = "phpMp";
global $song_display_conf;
$song_display_conf = "(artist) title";
$use_images = "no";
$refresh_freq = 60;
$default_sort = "Artist,Album,Track,Title";
// music list fields
// can be any combination of 1 or more of the following fields in any order:
// Artist, Title, Album, Track, Time
$display_fields = array('Artist', 'Title', 'Album', 'Track', 'Time');
global $unknown_string;
$unknown_string = "";
$frames_layout = "cols=\"1*,250\"";
global $filenames_only;
$filenames_only = "no";
global $use_javascript_add_all;
$use_javascript_add_all = "yes";
$hide_threshold = 20;
$use_cookies = "yes";

// VOLUME OPTIONS
$display_volume = "yes";
$volume_incr = "10";

// SHOULDN'T NEED TO TOUCH THIS
global $song_seperator;
$song_seperator = "rqqqrqqqr";

// EXPERIMENTAL
//no frames doesn't work!
$frames = "yes"; // yes or no

//include colors
include "theme.php";
?>
