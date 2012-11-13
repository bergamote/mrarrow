<?php
date_default_timezone_set('UTC');
//date_default_timezone_set();


//------------------------- Check if template exists, else go into parent folder
function set_template($template) {
  $good = THEME.$template.'.php';
  if (is_file($good)) {
    return $good;
  } else {
    return THEME."../$template.php";
  }
}
//------------------------- Find our position relative to root
function findRel($cur_url) {
	$rel = "../";	
	if ($cur_url != "") {
		$rel .= relativePath("/".$cur_url, "/" );
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
function sane($s) {
  //Convert accented characters, and remove parentheses and apostrophes
  $from = explode (',', "ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,e,i,ø,u,(,),[,],'");
  $to = explode (',', 'c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,e,i,o,u,,,,,,');
  //Do the replacements, and convert all other non-alphanumeric characters to hyphens
  $s = preg_replace ('~[^/\w\d-.#]+~', '-', str_replace ($from, $to, trim ($s)));
  //Remove a - at the beginning or end and make lowercase
  return strtolower (preg_replace ('/^-/', '', preg_replace ('/-$/', '', $s)));
}

function stripDate($string) {
	$string = preg_replace(DATE_REGEX, "", $string);
	return $string;
}
//---------------------------- stripNum : Strip prefixed ordering number
function stripNum($string) {
	$string = preg_replace('!^([\d])*\.!', "", $string);
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
//---------------------------- Compress theme's javascript and css with yui-compressor
function catComp($ext, $yui) {
  $pathIn = escapeshellarg(THEME);
  $pathOut = escapeshellarg(EXPORT); 
  $filename = ( $ext=="css" ? "style.css" : "script.js" );
	exec("ls $pathIn*.$ext 2>&1 1> /dev/null", $output, $ret_val);
	if($ret_val == 0) {
		exec("cat $pathIn*.$ext > $pathOut/$filename");
		echo ".$ext".PHP_EOL;
		if ($yui != 'off') {
		  exec("cp $pathOut/$filename $pathOut/tmp-$filename");
		  exec("yui-compressor $pathOut/tmp-$filename > $pathOut/$filename");
		  exec("rm $pathOut/tmp-$filename");
    }
	}
}
//----------------------------------- Get sane dest path from origin
function get_folder($value) {
  if(is_array($value)) {
    $value = array_shift($value);
  }
  $value = substr_replace(dirname($value), "", 0, ( strlen(CONTENT) +1));
  $value = sane(basename($value));
  return $value;
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
			if (($key != "") && ($value != "")) {
				$result[$key] = $value;
				echo "  $key = $value\n";
			}
		}
	}
	return $result;
}
//----------------------------------- Check if a folder array is a blog
function is_blog($who) {
  if (is_array($who)) {
    $posts = preg_grep(DATE_REGEX , array_keys($who));
    return (array_keys($who) == $posts) ? true : false;
  }
  return (preg_match(DATE_REGEX, $who) == 1) ? true : false;
}
?>
