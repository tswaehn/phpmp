<?php
// NEED TO SET THESE!
$host = "localhost";
$port = 6600;

// OPTIONAL
$title = "phpMp";
global $song_display_conf;
$song_display_conf = "(artist) title";
$use_images = "no";
$refresh_freq = 60;
$default_sort = "Artist,Album,Track,Title";
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
