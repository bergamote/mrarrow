<?php

// Open the STDIN flow, if needed.
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
if ($site_email != ""){
	$site_file .= "email = ".$site_email.PHP_EOL;
}
file_put_contents('site.conf', $site_file);

// Create the standard folders
function check_create($name) {
	global $lsarray;
	if (!in_array($name, $lsarray)) {
		mkdir($name);
	}
	else {
	echo "Folder already exists: $name".PHP_EOL;
	}
}
check_create("content");
check_create("export");
check_create("theme");

?>
