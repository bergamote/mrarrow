<?php

//------------------------- Find our position relative to root
function findRel($cur_url) {
	$rel = "../";	
	if ($cur_url != "") {
		$rel = relativePath("/".$cur_url, "/" );
	}
	return $rel;
}
//---------------------------------- Find relative path function
function relativePath($from, $to, $ps = DIRECTORY_SEPARATOR)
{
  $arFrom = explode($ps, rtrim($from, $ps));
  $arTo = explode($ps, rtrim($to, $ps));
  while(count($arFrom) && count($arTo) && ($arFrom[0] == $arTo[0]))
  {
    array_shift($arFrom);
    array_shift($arTo);
  }
  return str_pad("", count($arFrom) * 3, '..'.$ps).implode($ps, $arTo);
}


//---------------------------------- Name sanitizing functions
function saneo($string) { 
	$string = strtolower(str_replace(" ", "-", $string));
	$string = preg_replace('![^/\w-.]!', "", $string);
	return $string;
}
function sane($s) {
  //Convert accented characters, and remove parentheses and apostrophes
  $from = explode (',', "ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,e,i,ø,u,(,),[,],'");
  $to = explode (',', 'c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,e,i,o,u,,,,,,');
  //Do the replacements, and convert all other non-alphanumeric characters to hyphens
  $s = preg_replace ('~[^/\w\d-.#]+~', '-', str_replace ($from, $to, trim ($s)));
  //Remove a - at the beginning or end and make lowercase
  return strtolower (preg_replace ('/^-/', '', preg_replace ('/-$/', '', $s)));
}


//---------------------------- stripNum : Strip prefixed ordering number
function stripNum($string) {
	$string = preg_replace('!^([\d])*\.!', "", $string);
	//if ($string[0] == ".") {
	//	$string = substr($string, 1);
	//}
	return $string;
}

//---------------------------- Rercursive stripNum
// stripNumTree(
//		array,
//		which = both		: change both value and key
//				  = k		: change only the key
//				  = v		: change only the value
//		)
function stripNumTree($array=false, $which='both' )
{
  if ((!is_array($array)) && ($which != 'k')) {
    // Regular replace
    return stripNumPath($array);
  }	elseif ((!is_array($array)) && ($which == "k"))  {
	return $array;	
	}
  $newArr = array();
  foreach ($array as $k=>$v) {
    // Replace keys as well?
    $add_key = $k;
    if ($which != 'v') {
      $add_key = stripNumPath($k);
    }
    // Recurse
    $newArr[$add_key] = stripNumTree($v, $which);
  }
  return $newArr;
}
//---------------------------- stripNum in file path (and hash)
function stripNumPath($path, $hash=false) {
	$array = explode('/', $path);
	if ($array != 0) {
		foreach ($array as $k => $v) {
			if (($v != "") && ($v[0] == '#') && ($hash == true)) {
				$array[$k] = substr($v, 1);
			}
		}
		$path = implode('/', stripNum($array));
	}
	return $path;
}


//----------------------------------- Link files (Temporary, maybe for Test folder)
function linkThemeFile($file, $site){
$target = getcwd()."/".$site['dev_dir']."/theme/".$site['theme']."/$file";
$link = $site['export_dir']."/$file";
	if ((!file_exists($link)) && (file_exists($target))) {
		symlink($target, $link);
		echo readlink($link).PHP_EOL;
	}
}

function compThemeFile($file, $site) {
$target = $site['theme_dir']."/".$site['theme']."/$file";
$comp = $site['export_dir']."/$file";
if (file_exists($target)) {
	exec("yui-compressor ".escapeshellarg($target)." > ".escapeshellarg($comp));
	}
}

//----------------------------------- Extract the header




//----------------------------------- Parse the header
function parseHeader($data) {
	$inforaw = explode("\n", $data);
	foreach ($inforaw as $arr) {
		if (($arr != "") && ($arr[0] != '#')) {
			$part = explode('=', $arr);
			$key = trim($part[0]);
			$value = trim($part[1]);
			$result[$key] = $value;
			echo "$key = $value\n";
		}
	}
	return $result;
}



?>



