<?php
global $colors;
global $fonts;

// Font
$fonts["all"] = "sans";

// Background Color
$colors["background"] = "#ffffff";

// Colors for Links
$colors["links"]["link"] = "#0000ff";
$colors["links"]["active"] = "#0000ff";
$colors["links"]["visual"] = "#0000ff";

// Colors for Directories
$colors["directories"]["title"] = "#bbbbff";
$colors["directories"]["body"][0] = "#eeeeff";
$colors["directories"]["body"][1] = "#ddddff";

// Colors for Music
$colors["music"]["title"] = "#aaffaa";
$colors["music"]["body"][0] = "#ccffcc";
$colors["music"]["body"][1] = "#eeffee";
$colors["music"]["sort"] = "#88ff88";

// Colors for Playlist Table
$colors["playlist"]["title"] = "#ffaaaa";
$colors["playlist"]["body"] = "#ffdddd";
$colors["playlist"]["current"] = "#88ff88";

// Colors for Pssword
$colors["password"]["title"] = "#cccccc";
$colors["password"]["body"] = "#eeeeee";

// Colors for Playing
$colors["playing"]["title"] = "#cccccc";
$colors["playing"]["body"] = "#eeeeee";
$colors["playing"]["on"] = "#88ff88";

// Colors for Volume
$colors["volume"]["body"] = "#cccccc";
$colors["volume"]["unselected"] = "#eeeeee";
$colors["volume"]["background"] = "#ffffff";
$colors["volume"]["foreground"] = "#000000";

// Colors for Time Progress Bar
$colors["time"]["background"] = "#aaaaaa";
$colors["time"]["foreground"] = "#000000";

if(!isset($hide)) $hide = 1;
// URL Displays
if($use_images=="yes") {
	$display["playing"]["prev"]["active"] = "<a href=\"playlist.php?hide=$hide&command=previous\"><img src=\"images/previous.gif\" border=0></a>";
	$display["playing"]["prev"]["inactive"] = "<img src=\"images/previous_inactive.gif\" border=0>";
	$display["playing"]["play"]["active"] = "<a href=\"playlist.php?hide=$hide&command=play\"><img src=\"images/play.gif\" border=0></a>";
	$display["playing"]["play"]["pause"] = "<a href=\"playlist.php?hide=$hide&command=pause\"><img src=\"images/play.gif\" border=0></a>";
	$display["playing"]["play"]["inactive"] = "<img src=\"images/play_inactive.gif\" border=0>";
	$display["playing"]["next"]["active"] = "<a href=\"playlist.php?hide=$hide&command=next\"><img src=\"images/next.gif\" border=0></a>";
	$display["playing"]["next"]["inactive"] = "<img src=\"images/next_inactive.gif\" border=0>";
	$display["playing"]["pause"]["active"] = "<a href=\"playlist.php?hide=$hide&command=pause\"><img src=\"images/pause.gif\" border=0></a>";
	$display["playing"]["pause"]["inactive"] = "<img src=\"images/pause_inactive.gif\" border=0>";
	$display["playing"]["stop"]["active"] = "<a href=\"playlist.php?hide=$hide&command=stop\"><img src=\"images/stop.gif\" border=0></a>";
	$display["playing"]["stop"]["inactive"] = "<img src=\"images/stop_inactive.gif\" border=0>";
}
else {
	$display["playing"]["prev"]["active"] = "[<a href=\"playlist.php?hide=$hide&command=previous\">&lt;&lt;</a>]";
	$display["playing"]["prev"]["inactive"] = "[&lt;&lt;]";
	$display["playing"]["play"]["active"] = "[<a href=\"playlist.php?hide=$hide&command=play\">Play</a>]";
	$display["playing"]["play"]["pause"] = "[<a href=\"playlist.php?hide=$hide&command=pause\">Play</a>]";
	$display["playing"]["play"]["inactive"] = "[Play]";
	$display["playing"]["next"]["active"] = "[<a href=\"playlist.php?hide=$hide&command=next\">&gt;&gt;</a>]";
	$display["playing"]["next"]["inactive"] = "[&gt;&gt;]";
	$display["playing"]["pause"]["active"] = "[<a href=\"playlist.php?hide=$hide&command=pause\">| |</a>]";
	$display["playing"]["pause"]["inactive"] = "[| |]";
	$display["playing"]["stop"]["active"] = "[<a href=\"playlist.php?hide=$hide&command=stop\">Stop</a>]";
	$display["playing"]["stop"]["inactive"] = "[Stop]";
}
?>
