<?php
###############################################################################
# Configuration:

$ENTRIES_DIR = './entries/'; # where to read list of entries (files and directories) from

# list of functions applied to each entry to extract fields (e.g., path, url, title, title_html, labels_html, etc.)
$ENRICHERS = [
              "timestamp" => parse_date, "time" => timestamp_html1, # use time parsed from file name 
            //   "timestamp" => retrieve_mtime, "time" => timestamp_html2, # uncomment to use mtime 
              "labels" => parse_labels, "labels_html" => format_labels_html,  # comment out if not using labels
              "title_html" => format_title_html, 
              "icon_html" => retrieve_icon_html, 
              "abstract_html" => retrieve_abstract_html
            ];

# how to sort entries (decides whether entry $e1 should go before $e2)
$ORDER_PREDICATE = function($e1, $e2) { 
    [$e1_timestamp, $e2_timestamp] = [get($e1["timestamp"], ""), get($e2["timestamp"], "")];
    if ($e1_timestamp!="" && $e2_timestamp!="") { # by date
        if ($e1_timestamp<$e2_timestamp) return 1;
        if ($e1_timestamp>$e2_timestamp) return -1;
    } else if ($e1_timestamp!="") { # no date goes first
        return 1;
    } else if ($e2_timestamp!="") {
        return -1;
    }
    return $e1["title"]<$e2["title"]; # eventually, by title
}; 

# how many entries per page
$PER_PAGE = 4;

// $HEADER = "<br /><h1>List of entries</h1>";
$HEADER = "<br />";


# formatting of entries
$ENTRY_FORMAT = "
<div class='entry'>
<div class='entryHeader'>
<span class='entryDate'>@time</span>        
<a href='show.php?path=@url' class='entryLink'>@title_html</a> 
@labels_html 
</div> 
@icon_html @abstract_html 
</div>\n\n";


// $ENTRY_FORMAT = "
// <div class='entry' style='text-align: center;'>
// <div class='entryHeader'>
// <span class='entryDate'>@time</span>        
// <a href='@url' class='entryLink'>
// <img src='@path' class='entryImg' alt='[CLICK TO PLAY]'/>
// </a>
// </div> 
// @icon_html @abstract_html 
// </div>\n\n";

$TITLE_REPLACE = ['_' => ' ', '.html' => '', '.php' => '', '.md' => '']; # parsing of file names to get entry titles
$ABSTRACT_READ_MORE_TEXT = "(read&nbsp;more)";
$ABSTRACT_HREF = "show.php?path=@url";

$SHOW_LABELS_PANEL = true;
$EMPTY_LABEL = "?";
$LABEL_COLORS = ["#3498DB", "Tomato", "Thistle", "Turquoise", "Red","Maroon","LightGreen","Green","Salmon","Teal","CornflowerBlue","Navy", "Coral", "Gold", "Black"];  # palette used for labels
$LABEL_DISABLED_COLOR = "Gray";

###############################################################################
?>