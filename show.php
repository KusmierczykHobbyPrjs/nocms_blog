<?php

# EXTERNAL LIBRARIES:
#include 'https://raw.githubusercontent.com/erusev/parsedown/master/Parsedown.php';
include 'parsedown.php';


# CONFIGURATION OF TEMPLATES:
$TEMPLATE_STANDALONE = "template_entry_standalone.html";
$TEMPLATE_DIR = "template_entry_index.html";
$CONTENT_MARKER = "<!--PUT YOUR CONTENT HERE-->";
$HEADER_MARKER = "<!--PUT YOUR HEADER CONTENT HERE-->";


# IS PATH VALID?
$path = get($_GET["path"], "index.php");
$url = str_replace("%2F", "/", rawurlencode($path));        
if (!file_exists($path)) {
    echo "THE PATH DOESN'T EXIST!";
    exit(1);
}


# SELECT HOW TO DISPLAY THE CONTENT BASED ON TYPE (DIR VS FILE) AND FILE EXTENSION
if (is_dir($path)) {

    if (file_exists($path."/main.md")) {
        $content = load_md($path."/main.md", $TEMPLATE_DIR);
        $content = str_replace($HEADER_MARKER, '<base href="'.$url.'/">', $content);
        echo $content;

    } else if (file_exists($path."/main.html")) {
        $content = file_get_contents($path."/main.html");    
        $template = file_get_contents($TEMPLATE_DIR);    
        $content = str_replace($CONTENT_MARKER, $content, $template);
        $content = str_replace($HEADER_MARKER, '<base href="'.$url.'/">', $content);
        echo $content;

    } else {
        redirect($path);
    }

} else {

    $dir = dirname($path);
    
    if (endsWith($path, ".md")) {
        $content = load_md($path, $TEMPLATE_STANDALONE);
        $content = str_replace($HEADER_MARKER, '<base href="'.$dir.'/">', $content);
        echo $content;

    }  else if (endsWith($path, ".html")) {
        $content = file_get_contents($path);                        
        if (strpos($content, '<html') == false && strpos($content, '<body') == false) { #check if not a standalone file
            $template = file_get_contents($TEMPLATE_STANDALONE);    
            $content = str_replace($CONTENT_MARKER, $content, $template);
            $content = str_replace($HEADER_MARKER, '<base href="'.$dir.'/">', $content);
            echo $content;
        } else {
            redirect($path);
        }

    } else {
        redirect($path);
    }
    
}


#######################################################
#######################################################
#######################################################


function load_md($path, $template_path) {
    global $CONTENT_MARKER;    
    $content = file_get_contents($path);
    $Parsedown = new Parsedown();
    $content = $Parsedown->text($content);    
    $template = file_get_contents($template_path);    
    $content = str_replace($CONTENT_MARKER, $content, $template);
    return $content;
}


function redirect($path) {
    $url = str_replace("%2F", "/", rawurlencode($path));
    echo '<meta http-equiv="refresh" content="0; url='.$url.'" />';
    // echo '<p><a href="'.$url.'">Redirect</a></p>';
}

function get(&$var, $default=null) {
    return isset($var)? $var: $default;
}

function endsWith( $haystack, $needle ) {
    $length = strlen( $needle );
    if( !$length ) {
        return true;
    }
    return substr( $haystack, -$length ) === $needle;
}

?>