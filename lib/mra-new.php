<?php
require "mra-func.php";
require "mra-menu.php";
require "assets/explodeTree.php";

// Open the lucious STDIN flow, if needed.
if(!defined("STDIN")) {
define("STDIN", fopen('php://stdin','r'));
}
$canceled = "Operation canceled.".PHP_EOL;

// Check for existing configuration file.
$lscurdir = `ls`;
$lsarray = explode(PHP_EOL, $lscurdir);
if (in_array("site.conf", $lsarray)) {
	echo "There already is a site.conf file in the current folder.".PHP_EOL;
	echo "Do you want to overwrite it? (y/N):";
	$asw = trim(fread(STDIN, 1));
	if (($asw == "y") || ($asw == "Y")) {
		echo "Overwriting site.conf".PHP_EOL;
	} else {
		echo $canceled;
		exit(0);
	}
}
// Create the site.conf file.
$site_file = "# Mr.Arrow configuration file".PHP_EOL.PHP_EOL;

echo "Enter you're website's name: ";
$site_name = trim(fread(STDIN, 80));
if (empty($site_name)) {
	echo "You must enter a site name.".PHP_EOL;
	echo $canceled;
	exit(1);
}
echo "Enter an email address (optional): ";
$site_email = trim(fread(STDIN, 80));

$site_file .= "name = ".$site_name.PHP_EOL;
if (!empty($site_email)){
	$site_file .= "email = ".$site_email.PHP_EOL;
}
$site_file .= "theme = ";
file_put_contents('site.conf', $site_file);

// Create the standard folders
function check_create($name) {
	global $lsarray;
	if (!in_array($name, $lsarray)) { mkdir($name); return true; }
	else { echo "Folder already exists: $name".PHP_EOL; return false; }
}

$cr_export = check_create("export");

// Extracting the packed themes if they don't alresdy exist.
$cr_theme = check_create("theme");
$ls_theme = `ls theme/`;
$ls_pack_theme = `ls lib/assets/theme/`;
$ls_theme_ar = explode(PHP_EOL, $ls_theme);
$ls_pack_ar = explode(PHP_EOL, $ls_pack_theme);
foreach ($ls_pack_ar as $filename) {
if (!empty($filename)) {
		$filename = str_replace(".tar.gz", "", $filename);
		if (is_dir("theme/$filename")) {
			echo " Theme already exists: $filename".PHP_EOL;
		}
		else {
			mkdir("theme/$filename");
			exec("tar xvfz lib/assets/theme/$filename.tar.gz -C theme/$filename");
		}
	}
}

// Put a example page in content.
$cr_content = check_create("content");
$example_page = <<<EOD

An Example Page
===============

This is an example page. Edit it and rename it to make your first page.

To get an idea of markdown formating check the resulting html page in the export folder.

Markdown loves titles
---------------------

  1. Markdown
  2. Loves
  3. Lists

Markdown [loves links](http://daringfireball.net/projects/markdown/syntax) to markdown.

    Markdown loves code snipets

And Mr.Arrow *emphasises* the need to run ./arrow after updating content files.

EOD;

if($cr_content) {
file_put_contents("content/Example Page.txt", $example_page);
}

// Create a simple index page
$menu = init_menu();
$menu_li = makeMenuLi("");
$simple_index = <<<EOD
<!DOCTYPE html>
<head>
	<style>
		body {
			text-align:center;
		}
	</style>
</head>
<body>
<br>
<h1>$site_name</h1>
<nav>$menu_li</nav>
</body>
EOD;

if(!is_file("export/index.html")){
	file_put_contents("export/index.html", $simple_index);
}

// Run ./arrow on this joke aof a website
exec("./arrow");
echo "Website succefully created in ".getcwd().PHP_EOL;
?>
