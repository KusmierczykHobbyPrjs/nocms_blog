# NoCMS blog

*NoCMS blog* is a web-content previewing tool. It displays a list of files and folders stored in [entries](entries) directory -- no DB is used and no separate management tools (besides one provided by your website host) are provided. To add a new entry simply create a new [HTML](https://en.wikipedia.org/wiki/HTML) or [MD](https://en.wikipedia.org/wiki/Markdown) file directly in the directory or in a sub-folder. Labels (tags) and date information are parsed from files/folders names. For example test it [here](https://https://students.mimuw.edu.pl/~tk290810/blog_template/), [here](https://https://students.mimuw.edu.pl/~tk290810/memes/) or [here](https://https://students.mimuw.edu.pl/~tk290810/blog/?alllabels=1&labels=ML).

## Features

 *  [HTML](https://en.wikipedia.org/wiki/HTML) and [MD](https://en.wikipedia.org/wiki/Markdown) support (fully-custom entries e.g. using php, can be used as well)
 * Label-based filtering (tags) 
 * Paging
 * Short abstract and content illustration can be used for folder-stored entries
 * [CSS](https://en.wikipedia.org/wiki/CSS)-controlled look and feel
 * Configurable formatting and sorting
 
## Requirements

 * PHP must be available on the server used to host *NoCMS blog*. 
 * [show.php](show.php) uses [Parsedown](https://github.com/erusev/parsedown) to display MD files.

## Configuration

Look and feel can be configured by editing [style.css](style.css) (and respective HTML templates if needed). Most of behavior can be adjusted by editing `Configuration` section of [index.php](index.php): The main logic works by parsing list of files and folders from which it creates a list of records `field_name => field_value`, e.g. url, path, timestamp etc. Then, the records are sorted, filtered by labels and used to format entries following `ENTRY_FORMAT`.  


## Files

 * [entries/](entries) - source directory where the entries are stored (see [samples](entries))
 * [index.php](index.php) - the main file enumerating entries
 * [show.php](show.php) - displays entries (invoked from `ENTRY_FORMAT` in [index.php](index.php) -- optional)
 * [template_entry_index.html](template_entry_index.html) - HTML template used to render content stored in folders
 * [template_entry_standalone.html](template_entry_standalone.html) - HTML template used to render standalone content
 * [style.css](style.css) - CSS feel-and-look specification
 * [actions.js](actions.js) - JavasSript code used to adjust logo position for small displays
 * [logo.png](logo.png) - default logo
