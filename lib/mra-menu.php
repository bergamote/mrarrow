<?php
function init_menu(){
	if(exec('find '.getcwd().'/content | egrep ".txt|.md|.markdown"' , $files)){
			$files = substr_replace($files, "", 0, ( strlen(getcwd()) +1) );
		  $files = array_combine(array_values($files), array_values($files));
	}
	$tree = explodeTree($files, "/", true);
	ksortTree($tree);
	$menu = array(); 
	$menu = makeMenu($tree['content']);
	$menu = stripNumTree($menu, 'both');
	return $menu;
}
//--- Make the $menu array
function makeMenu($array=false)
{
	global $site, $menu, $test_trail;
	$skipers = array('_','#','0'); // skiped the prefixed filenames
	if (!is_array($array)) {
		$file_part = pathinfo($array);
		$nice_name = $file_part['filename'];
    if(!in_array($nice_name[0], $skipers)) {
			$dest_path = str_replace('content', '', $file_part['dirname']);
			$sprtr = "/";
			if ($dest_path == ""){$sprtr = "";}
			$dest_path = sane($dest_path.$sprtr.$nice_name)."/".$test_trail;
			return $dest_path;
		} else {
		return false;
		}
  }
	$newArr = array();
  foreach ($array as $k=>$v) {
		$file_part = pathinfo($k);
		$nice_name = $file_part['filename'];

    if(!in_array($nice_name[0], $skipers) && ($v != "")) {
			$add_key = $file_part['basename'];
		  $newArr[$add_key] = makeMenu($v);
			//echo "  ".$add_key.PHP_EOL;
		}
  }
  return $newArr;
}

//--- Make $menu_li for the current page (a string, html nested unordered list)

function makeMenuLi($rel) {
	global $menu;
	$menu_li = ' <ul>'.PHP_EOL;
	$menu_li .= plotMenu($menu, $rel);
	$menu_li .= ' </ul>'.PHP_EOL;
	return $menu_li;
}

function plotMenu($arr, $rel, $indent=2){
		$link = "";
    foreach($arr as $k=>$v){
				$spaces = str_repeat("  ", $indent);
        if(!is_array($v)){
						$k = pathinfo($k, PATHINFO_FILENAME);
						$link .= $spaces.'<li><a href="'.$rel.$v.'">'.$k.'</a></li>'.PHP_EOL;
        } else {
						$sk = sane($k);
						$link .= $spaces.'<li onmouseover="showSubMenu(\'#'.$sk.'\')"';
						$link .= ' onmouseout="hideSubMenu(\'#'.$sk.'\')">';
						$link .= $k.PHP_EOL.$spaces.'<ul ';
						$link .= 'id="'.$sk.'">'.PHP_EOL;
            $link .= plotMenu($v, $rel, ($indent+1));
						$link .= $spaces."  </ul>\n";
						$link .= $spaces."</li>\n";
        }
    }
		return $link;
}

?>
