Mr. Arrow
=========
http://mrarrow.co.uk  
wilks@mrarrow.co.uk  
10/2012

Mr. Arrow is a PHP command line script to generate websites from text files.
It uses the folder structure to organise the website and menu.
The text files are processed trough Markdown and a template php file from the "themes" folder.

## Requirements
Mr. Arrow requires PHP-cli and YUI-compressor installed.
On Debian based systems:
  apt-get install php5-cli yui-compressor

## Install
To start a new website, unpack the "arrow" file and the "lib" folder in a directory.
Make sure "arrow" is executable:
  chmod +x arrow
To create a site.conf and spawn some standard folders in the current location, type:
  ./arrow new

## Commands
./arrow			Generate/update the website based on the settings in ./site.conf:
./arrow config		
./arrow widget		Same, but when done start the widget (a button to easily update changes):
./arrow upload		Does an update then upload to remote location
./arrow new             Create folders and assets for a site in the current folder

## Settings
The website settings are stored in the site.conf file.
Some examples of the settings which can be defined:

- set the website's name:
    name = My New Website

- set a theme 
    theme = Quiver
  It must be a folder within your "themes" folder.
  If not specified or if the theme doesn't exist, Mr. Arrow will default to ./lib/default_template.php

- respectivly, set in which folder to look for the content files, the theme files and where to export the generated website.
    theme_dir = themes 
    content_dir = content
    export_dir = export
  (these are the default values if not specified)

You can add your own settings, one per line, following this format:
key = My story
This setting can then be accessed from the php template by calling the $site array.
The folowing would print "My story":
  <?php echo $site['key'] ?>

