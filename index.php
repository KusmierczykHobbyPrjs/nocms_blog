<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml" lang="pl" xml:lang="pl">

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0"> 
<link rel="stylesheet" type="text/css" href="style.css">
<script src="actions.js"></script>
<title>Tomasz Kuśmierczyk Misc Blog</title>

<body onload="onLoad()" onresize="onResize()">

<?php

###############################################################################
###############################################################################
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

$SHOW_LABELS_PANEL = true;
$EMPTY_LABEL = "?";
$LABEL_COLORS = ["#3498DB", "Tomato", "Thistle", "Turquoise", "Red","Maroon","LightGreen","Green","Salmon","Teal","CornflowerBlue","Navy", "Coral", "Gold", "Black"];  # palette used for labels
$LABEL_DISABLED_COLOR = "Gray";


###############################################################################
###############################################################################
###############################################################################
# Execution:

$files = list_dir($ENTRIES_DIR); # retrieve list of files and directories
$entries = parse_entries($files, $ENRICHERS); # parse file names to extract entries


$LABEL2COUNT = extract_label2count($entries); # extract list of used labels (name=>count)
if ($LABEL2COUNT[$EMPTY_LABEL]>0) $LABEL2COUNT[$EMPTY_LABEL] = 1000000; # fix inf popularity for empty label
arsort($LABEL2COUNT); # sort labels by decreasing popularity

$ENABLED_LABELS = explode(",", $_GET["labels"]); # which labels to show
$ENABLED_LABELS = array_intersect($ENABLED_LABELS, array_keys($LABEL2COUNT));

# filter entries by labels 
$MATCH_ALL_LABELS = get($_GET["alllabels"], "0")=="1"; # if not set use 'any' by default
$min_matched_labels = $MATCH_ALL_LABELS? count($ENABLED_LABELS): min(1, count($ENABLED_LABELS));
foreach ($entries as $ix => $entry) {
    $num_matched_labels = count(array_intersect($ENABLED_LABELS, $entry["labels"]));
    if($num_matched_labels<$min_matched_labels) unset($entries[$ix]);    
}

# sort entries
usort($entries, $ORDER_PREDICATE); 

# filter entries by page number
if (!is_null($_GET["display"])) $PER_PAGE = $_GET["display"];
[$page, $start, $end, $last_page]= paging($entries, $PER_PAGE, $_GET["page"]);
[$start, $end] = [count($entries)-$end, count($entries)-$start]; # paging uses reversed order of ixs
$entries = array_slice($entries, $start, $end-$start, true);

###############################################################################
###############################################################################
###############################################################################
# HTML code generation:

# labels' nav panel------------------------
if ($SHOW_LABELS_PANEL) {
    echo("<nav>\n");

    $query = query(["labels"=>implode(',', array_keys($LABEL2COUNT)), "alllabels"=>"0"], ["page", "display"]);
    echo("<a class='label labelNav labelNavEnableAll' href='?$query'></a>\n");
    
    $query = query(["labels"=>""], ["page", "display"]);
    echo("<a class='label labelNav labelNavDisableAll' href='?$query'></a>\n");
    
    $query = query(["alllabels"=>($MATCH_ALL_LABELS? "0": "1")],  ["page", "display"]);
    $cssclass = $MATCH_ALL_LABELS? "All": "Any";
    echo("<a class='label labelNav labelNavMatch$cssclass' href='?$query'></a>\n");
    
    foreach($LABEL2COUNT as $label => $count) echo(label_nav_html($label, $ENABLED_LABELS)."\n");
    
    echo("</nav>\n");    
}
#------------------------------------------

# list of the entries (parsed file names)--
echo("<article>\n");
echo($HEADER."\n");
foreach ($entries as $entry) {
    # add prefix @ to each field
    $mapping = array();
    foreach ($entry as $entry_key => $entry_value) $mapping['@'.$entry_key]=$entry_value; 
    echo(strtr($ENTRY_FORMAT, $mapping));
}
echo("</article>\n");
#------------------------------------------

# paging (footer) -------------------------
#echo "page: $page (#$start - #$end) - $last_page<br />\n";
echo("<footer>\n");

# prev page button
$query = query(["page"=>min([$last_page, $page+1])]);
$visibility = ($page==$last_page || $last_page<=0)? "hidden": "visible";
echo("<a class='pageLink pageLinkPrev' style='visibility: $visibility' href='?$query'></a>\n");

# pages
$labels = implode(",", $ENABLED_LABELS); 
for ($p=$last_page; ($p>=0) && ($last_page>0); $p--) {        
    $query = query(["page"=>$p]);
    echo "<a class='pageLink ".($p==$page?"pageLinkEnabled":"pageLinkDisabled")."' href='?$query'>$p</a>\n";
}

# next page button
$query = query(["page"=>max([0, $page-1])]);
$visibility = ($page==0 || $last_page<=0)? "hidden": "visible";
echo("<a class='pageLink pageLinkNext' style='visibility: $visibility' href='?$query'></a>\n");

echo("</footer>\n");
#------------------------------------------

###############################################################################
###############################################################################
###############################################################################

function list_dir($dir) {
    /** Returns list of files from a $dir. */
    $list = array();
    if ($handle = opendir($dir)) {
        while (false !== ($entry = readdir($handle))) {
            if ($entry == "." || $entry == "..") continue;
            $path = $dir."/".$entry;
            $path = str_replace("//", "/", $path);
            $list[] = $path;
        }
        closedir($handle);
    }
    return $list;
};


function parse_entries($files, $enrichers) {
    /** Returns list of entries parsed from a list of files. 
     * Entries are arrays consisting of path, url, title and whatever is added by enrichers.
    */
    $entries = array();
    foreach($files as $entry) {
        # create basic entry with title, path and url
        $entry = array("title"=>basename($entry), "path"=>$entry);
        $entry["url"] = str_replace("%2F", "/", rawurlencode($entry["path"]));
        
        foreach ($enrichers as $field_name => $enricher) {
            $entry[$field_name] = $enricher($entry);   
        }
        $entries[] = $entry;
    }
    return $entries;
};


function paging($entries, $per_page, $page) {
    /** Returns range of entry indices matching the requested page. */
    $last_page = floor(count($entries) / $per_page) - 1;
    if (is_null($page)) $page = $last_page;

    $start = $page*$per_page;
    $end = $page*$per_page + $per_page; # not included
    if ($page==$last_page) $end = count($entries); # the home page goes until the end

    $page = max([min([$page, $last_page]), 0]);
    return [$page, $start, $end, $last_page];
};


###############################################################################
###############################################################################
###############################################################################
# Parsers and enrichers creating new entry fields out of existing ones:


function parse_labels($entry) {
    /** Returns labels extracted from $entry["title"] (in format: [label]). */
    global $EMPTY_LABEL;
    preg_match_all('/\[.*?\]/', $entry["title"], $matches);
    $labels = $matches[0];
    foreach ($labels as &$label) {
        $label = str_replace("[", "", $label);
        $label = str_replace("]", "", $label);
        $label = str_replace(" ", "_", $label);
    }
    if (count($labels)<=0) { # no labels
        $labels = array($EMPTY_LABEL);
    }
    return $labels;
}


function parse_date(&$entry) {
    /** Returns date extracted from $entry["title"]  (in format: yyyy-mm-dd). */        
    preg_match('/\d\d\d\d-\d\d-\d\d/', $entry["title"], $date);
    $entry["title"] = str_replace($date[0], "", $entry["title"]);
    return strtotime($date[0]);
}


function retrieve_mtime($entry) {
    return filemtime($entry["path"]);
}


function timestamp_html1($entry) {
    if ($entry["timestamp"]=="") return "";
    return date('Y-m-d', $entry["timestamp"]);
}


function timestamp_html2($entry) {
    if ($entry["timestamp"]=="") return "";
    return date('F d Y h:i A', $entry["timestamp"]);
}


function format_title_html($entry) {
    /** Returns parsed and formatted $entry["title"]. */
    global $TITLE_REPLACE;
    $entry_title = $entry["title"];            
    foreach ($TITLE_REPLACE as $key => $value) {
        $entry_title = str_replace($key, $value, $entry_title);
    }
    foreach ($entry["labels"] as $key => $match) {
        $entry_title = str_replace("[".$match."]", "", $entry_title);            
    }
    $entry_title = trim($entry_title);
    return $entry_title;
}


function retrieve_icon_html($entry) {
    /** Returns icon_html if available for an entry. */  
    if (file_exists($entry["path"]."/icon.png")) {                   
        return "<img src='".$entry["url"]."/icon.png' class='entryIcon' alt='' />\n";
    }
    return "";
}


function retrieve_abstract_html($entry) {
    /** Returns abstract_html if available for an entry. */
    global $ABSTRACT_READ_MORE_TEXT;
    $abstract = $entry["path"]."/abstract.html";
    if (file_exists($abstract)) {
        $abstract_text = file_get_contents($abstract);
        return "$abstract_text\n <a href='show.php?path=".$entry["path"]."' class='readmoreLink'>$ABSTRACT_READ_MORE_TEXT</a> <br />\n";
    }
    return "";
}


function format_labels_html($entry) {
    /** Returns $entry["labels"] formatted to html. */
    $labels_html = "";
    foreach ($entry["labels"] as $label) {   
        $color = string2color($label);                   
        $query = query(["labels"=>$label]);
        $labels_html = $labels_html."<a class='label entryLabel' style='color: $color; border-color: $color;' href='?$query'>$label</a>\n";
    }
    return $labels_html;
}


###############################################################################
###############################################################################
###############################################################################
# Labels nav panel:


function extract_label2count($entries) {
    $labels = array();
    foreach($entries as $entry) {
        foreach ($entry["labels"] as $label) $labels[$label] += 1;
    }
    return $labels;
}


function label_nav_html($label, &$enabled_labels) {
    global $LABEL_DISABLED_COLOR;
    $color = string2color($label);       
    $hover_events = " onmouseover=\"this.style.color='@oncolor';\"  onmouseout=\"this.style.color='@offcolor';\" ";
    $enabled = array_combine($enabled_labels, $enabled_labels); # make copy and move values to keys

    if (in_array($label, $enabled_labels)) { # disabling a label
        unset($enabled[$label]);
        $query = query(["labels"=>implode(",", $enabled)], ["page", "display"]);
        $hover_events = strtr($hover_events, ["@oncolor"=>$LABEL_DISABLED_COLOR, "@offcolor"=>$color]);
        $html = "<a class='label labelNav' style='color: $color; border-color: $color;' href='?$query' $hover_events>$label</a> \n";
    } else { # enabling a label
        $enabled[] = $label;
        $query = query(["labels"=>implode(",", $enabled)], ["page", "display"]);
        $hover_events = strtr($hover_events, ["@oncolor"=>$color, "@offcolor"=>$LABEL_DISABLED_COLOR]);
        $html = "<a class='label labelNav' href='?$query' $hover_events>$label</a> \n";
    }
    return $html;
}


###############################################################################
###############################################################################
###############################################################################
# Auxiliary:


function get(&$var, $default=null) {
    return isset($var)? $var: $default;
}


function string2color($str) {
    /** Returns color for string. */
    #$code = dechex(crc32($str));
    #$code = "#".substr($code, 0, 6);
    global $LABEL_COLORS;
    $code = $LABEL_COLORS[crc32($str) % sizeof($LABEL_COLORS)];
    return $code;
}


function query($set=[], $drop=[]) {
    /** Returns GET query with certain entries set or dropped. */
    return implode_kv(drop_keys(array_merge($_GET, $set), $drop));
}


function implode_kv($arr, $sep="&", $sepv="=") {
    /** Glues array entries. */
    $str = "";
    foreach ($arr as $key => $val) {
        $str .= $sep.$key.$sepv.$val;
    };
    return substr($str, strlen($sep));
}


function drop_keys($arr, $keys=[]) {
    /** Returns a copy of array with keys removed. */
    foreach ($keys as $k) {
        unset($arr[$k]);
    }
    return $arr;
}


?> 

</body>
</html>



