Mr Arrow
========
http://mrarrow.co.uk  
wilks@mrarrow.co.uk  
10/2012

Mr Arrow (MrA) is a PHP command line script to generate websites from text files.
It uses the folder structure to organise the website and menu.
The text files are processed trough Markdown and a template php file from the "themes" folder.

## Requirements

Mr Arrow runs on Linux (and optionally MacOS). It requires PHP-cli. YUI-compressor is recommended.
Open a terminal and type:

    apt-get install php5-cli yui-compressor

[PHP Markdown Extra](http://michelf.ca/projects/php-markdown/extra/), a PHP port of [Markdown](http://daringfireball.net/projects/markdown/), as well as some very useful tree functions by [Kevin van Zonneveld](http://kvz.io/blog/2007/10/03/convert-anything-to-tree-structures-in-php/), are included with MrA.

## Install
To start a new website, unpack the "arrow" file and the "lib" folder in a directory.
Make sure "arrow" is executable:

    chmod +x arrow
    
To create a site.conf and spawn some standard folders in the current location, type:

    ./arrow new

## Usage

    ./arrow
Update and export the website based on the settings in site.conf
  
    ./arrow widget
Start the widget, a button to easily update changes (Linux only)
  
    ./arrow new 
Create folders and assets for a site in the current folder

## Settings
The website settings are stored in the site.conf file.
Some examples of the settings which can be defined:

- set the website's name:  
    name = My New Website

- set a theme:  
    theme = Quiver  

  If not specified or if the folder (case sensitive) doesn't exist, Mr. Arrow will default to the non-styled template default.php in theme_dir.

- respectively sets in which folder to look for the content files, the theme folders and where to export the generated website.  
    content_dir = content  
    theme_dir = theme  
    export_dir = export  
  (these are the default values if not specified)

You can add your own settings, one per line, following this format:  
key = My story  
This setting can then be accessed from the php template by calling the $site array.  
The following, inserted in a template, would print "My story": 

    <?php echo $site['key'] ?>

or this (if you have short tags enabled):
    
    <?= $site['key']?>
    
Preceding a line in site.conf with # will comment it out.
  
## Content
To add content to the website just save a text file in your content folder, with one of the following extensions: .txt .md .markdown  
For info about the Markdown formatting (a lot like plain emails) read [this](http://daringfireball.net/projects/markdown/syntax).

Each file will become a page with the name of the file for title, and a link to it in the main menu.

### Ordering pages
You can choose in which order the pages come in the menu by prefixing the file name with a number and a dot:

    1.Home.txt
    2.Hobbies.txt
    3.About.txt
    Contact.txt

Files without a number will be sorted alphabetically.  
Preceding a file name with a # takes that page out of the menu (so remember to link to it within a page).

## Theme
A theme is a folder in your theme_dir directory. Have a look at lib/assets/theme. The three php files in there are the three basic templates. 'index' is just the websites front page, 'default' is what we use all the time, and 'post' is called within 'default' for each post on a blog page.

All the .js and .css files in the folder get concatenated into one file (style.css and script.js) and if YUI-compressor is installed, they get compressed. To control in what order the files get added, prefix the file name by a number (ie: 1mainstyle.css 2secondarystyle.css).


### Quiver
Quiver is the default theme.  
If you save an image as background.jpg in your content folder, Quiver will use it as a full screen background.



