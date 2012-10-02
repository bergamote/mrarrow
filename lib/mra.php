<?php 
//MISTER ARROW
// some great, recursive, tree functions, look inside for licenses
require "vanZon.php";
// the php-markdown library
require "markdown.php";
require "mra-func.php";
//------------------------- decide if in testing mode or not
$deploy = 1;
$test_trail = "index.html";
if ($deploy == 1){
$test_trail = "";
}
$copy_list = array();
//------------------------- store site.conf settings into $site array
$site = array();
$conf_file = getcwd()."/site.conf";
if (file_exists($conf_file)) {
	echo "Reading site.conf.".PHP_EOL;
	$conf = file_get_contents($conf_file);
	$site = parseHeader($conf);
} else {
	echo "No site.conf file.".PHP_EOL;
}

//------------------------- some minimum default setting
$default_site = array (
	'name' => 'My new site',
	'tagline' => 'Just another Mr Arrow website',
	'theme' => 'none',
	'theme_dir' => 'themes',
	'content_dir' => 'content',
	'export_dir' => 'export',
	'dev_dir' => 'lib');
foreach ($default_site as $key => $val) {
	if (!isset($site[$key])) {
		$site[$key] = $val;
		echo "  default: $key = $val".PHP_EOL;
	}
}


//------------------------- turn markdown content into a tree

if(exec("find ".getcwd()."/".$site['content_dir'].' | egrep ".txt|.md|.markdown"' , $files)){
		$files = substr_replace($files, "", 0, ( strlen(getcwd()) +1) );
    $files = array_combine(array_values($files), array_values($files));
}

$tree = explodeTree($files, "/", true);

ksortTree($tree);
//print_r($tree);


$menu = array(); 
$menu = makeMenu($tree[$site['content_dir']]);
$menu = stripNumTree($menu, 'both');
//print_r($menu);

plotSite($tree);

//	Dealing with "other" files (compressing css and js from theme folder; 
//	copy everything else)
compThemeFile('style.css', $site);
compThemeFile('script.js', $site);

if(exec("find ".getcwd()."/".$site['content_dir'].' -type f  | egrep -v ".txt|.md|.markdown"' , $cpfiles)){
		$cpfiles = substr_replace($cpfiles, "", 0, ( strlen(getcwd()) +1) );
}
// if not empty
echo "Copy other files:".PHP_EOL;
foreach ($cpfiles as $v) {
		$file_part = pathinfo($v);		
		$dest_path = str_replace($site['content_dir'], "", $file_part['dirname']);	
		$dest_path = sane($dest_path.'/');
		$dest_path = stripNumPath($dest_path, true);
		$dest_path = $site['export_dir'].$dest_path.$file_part['basename'];
		echo "  $v -> $dest_path".PHP_EOL;
		exec('cp -f '.escapeshellarg($v).' '.$dest_path);
}
// fi
echo PHP_EOL;

##################################################### MAIN FUNCTIONS

//------------------------- The menu making functions


//--- Make the $menu array
function makeMenu($array=false)
{
	global $site, $menu, $test_trail;
	$skipers = array('_','#','0'); // skiped the prefixed filenames
	if (!is_array($array)) {
		$file_part = pathinfo($array);
		$nice_name = $file_part['filename'];
    if(!in_array($nice_name[0], $skipers)) {
			$dest_path = substr_replace($file_part['dirname'], "", 0, ( strlen($site['content_dir']) +1));
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
						$v = $v;
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





####################################----- The website plotting function

function plotSite($arr, $indent=0, $mother_run=true){
    if($mother_run){
        // the beginning of plotTree. We're at rootlevel
        echo "Start\n";
    }
 
    foreach($arr as $k=>$v){
        // skip the baseval and _prefixed filenames.
        if(($k == "__base_val") || ($k[0] == "_")) continue;
        // determine the real value of this node.
        //$show_val = ( is_array($v) ? $v["__base_val"] : $v );
				$show_val = ( is_array($v) ? $k : $v );
        //echo str_repeat("  ", $indent);
        if($indent == 0){
            // this is a root node. no parents
            //echo "O Create site".PHP_EOL;
        } elseif(is_array($v)){
            // this is a normal node. parents and children
            echo "  Category $k".PHP_EOL;
        } elseif(!is_dir($show_val)) {
            // this is a leaf node. no children
						global $page;
						$page = new Page;
						$page->path = $show_val;
						$page->makePage();
        }
        if(is_array($v)){
            // this is what makes it recursive, rerun for childs
            plotSite($v, ($indent+1), false);
        }
    }
    if($mother_run){
        echo "End\n";
    }
}


############################################
//---------------------------------- Classes
class Page {
	public $title;
	public $content;
	public $path;
	public $rel;
	public $menu_li;
	public function makePage(){
		global $site, $page;
		echo "  + ".$this->path;

		$file_part = pathinfo($this->path);
		$this->title = stripNum($file_part['filename']); 
		if ($this->title[0] == "#") {
			$this->title = substr($this->title, 1);
		}

		$data = file_get_contents($this->path);		
		$this->content = Markdown($data);

		$dest_path = str_replace($site['content_dir'], "", $file_part['dirname']);
		
		$this->rel = findRel(sane($dest_path));
		$this->menu_li = makeMenuLi($this->rel);

		$this->style = $this->rel."style.css";

		$dest_path = sane($dest_path.'/'.$this->title);
		$dest_path = stripNumPath($dest_path, true);
		$dest_path = $site['export_dir'].$dest_path;
		
		exec("mkdir -p ".escapeshellarg($dest_path));
		$dest_path .= "/index.html";
		$template = $site['dev_dir'].'/default_template.php';
		if ( ($site['theme'] != 'none') && is_dir($site['theme_dir']."/".$site['theme']) ) {
			$template = $site['theme_dir']."/".$site['theme']."/default.php";
		}
		ob_start();
		include $template;
		file_put_contents($dest_path, ob_get_contents());
		ob_end_clean();
		echo PHP_EOL;
	}
}


?>
