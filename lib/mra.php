<?php 
//MISTER ARROW
echo ">>>>-------------MrA-------------->".PHP_EOL;
// some great, recursive, tree functions, look inside for licenses
require "assets/explodeTree.php";
// the php-markdown library
require "assets/markdown.php";
require "mra-func.php";
//require "mra-menu.php";

//------------------------- decide if in testing mode or not
$deploy = 1;
$test_trail = "index.html";
if ($deploy == 1){
$test_trail = "";
}

//------------------------- The default settings if not in site.conf
$default_site = array (
  'name' => 'My new site',
  'tagline' => 'Just another Mr Arrow website',
  'theme' => '',
  'theme_dir' => 'theme',
  'content_dir' => 'content',
  'export_dir' => 'export',
  'lib_dir' => 'lib');
//------------------------- store site.conf settings into $site array
$site = array();
$conf_file = getcwd()."/site.conf";
if (file_exists($conf_file)) {
  echo "Reading site.conf.".PHP_EOL;
  $conf = file_get_contents($conf_file);
  $site = parseHeader($conf);
} else {
  echo "No site.conf file found.".PHP_EOL;
  echo "To create a new website type: ./arrow new".PHP_EOL;
  exit(1);
}
foreach ($default_site as $key => $val) {
  if (!isset($site[$key])) {
    $site[$key] = $val;
  }
}
//------------------------- Find in which folder to look for templates
$yui_sw = false;
if ( (!empty($site['theme'])) && is_dir($site['theme_dir']."/".$site['theme']) ) {
  $site['theme_dir'] = $site['theme_dir']."/".$site['theme'];
  $yui_sw = true;
} elseif (!empty($site['theme'])) {
  echo "Theme '".$site['theme']."' not found, using default theme.".PHP_EOL;
}
$site['theme_dir'] .= (substr($site['theme_dir'], -1) == '/')?:'/';



//-------------------------	Compressing css and js from theme folder 
if ($yui_sw){
  if(!exec("which yui-compressor")) {
    echo "yui-compressor is not installed.".PHP_EOL;
  } else {
    echo "yui-compress";
    catCompYUI("css");
    catCompYUI("js");
    echo "done";
  }
}
//-------------------------	Copy everything else
if(exec("find ".getcwd()."/".$site['content_dir'].' -type f  | egrep -v ".txt|.md|.markdown"' , $cpfiles)){
		$cpfiles = substr_replace($cpfiles, "", 0, ( strlen(getcwd()) +1) );
}
if(!empty($cpfiles)) {
  echo "Copy files (if modified):".PHP_EOL;
  foreach ($cpfiles as $v) {
    $file_part = pathinfo($v);		
    $dest_path = str_replace($site['content_dir'], "", $file_part['dirname']);	
    $dest_path = sane($dest_path.'/');
    $dest_path = stripNumPath($dest_path, true);
    $dest_path = $site['export_dir'].$dest_path.$file_part['basename'];
    exec('cp -fu --preserve=timestamps '.escapeshellarg($v).' '.$dest_path);
    echo "  $v -> $dest_path".PHP_EOL;		
  }
}
//------------------------- Make the site's tree
if(exec("find ".getcwd()."/".$site['content_dir'].' | egrep ".txt|.md|.markdown"' , $files)){
  $files = substr_replace($files, "", 0, ( strlen(getcwd()) +1) );
  $files = array_combine(array_values($files), array_values($files));
}
$tree = explodeTree($files, "/", true);
ksortTree($tree);

$menu = array(); 
$menu = makeMenu($tree[$site['content_dir']]);
$menu = stripNumTree($menu, 'both');

plotSite($tree);

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
  if(!is_blog($array)) {
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
  } else {
    $sacrfc = array_shift($array);
    $dest_path = substr_replace(dirname($sacrfc), "", 0, ( strlen($site['content_dir']) +1));
    $dest_path = sane(basename($dest_path))."/".$test_trail;
    return $dest_path; 
  }
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
      $link .= "$spaces<li id=\"$sk\">$k".PHP_EOL;
      $link .= " $spaces<ul id=\"$sk\">".PHP_EOL;
      $link .= plotMenu($v, $rel, ($indent+1));
      $link .= $spaces."  </ul>\n";
      $link .= $spaces."</li>\n";
    }
  }
  return $link;
}





####################################----- The website plotting function

function plotSite($arr, $indent=0, $mother_run=true){
  global $site;
  if($mother_run){
    // the beginning of plotTree. We're at rootlevel
    echo "Start\n";
  }
 
  foreach($arr as $k=>$v){
    // skip the baseval and _prefixed filenames.
    if($k[0] == "_") continue;
    // determine the real value of this node.
		$show_val = ( is_array($v) ? $k : $v );
    //echo str_repeat("  ", $indent);
    if($indent == 0){
      // this is a root node. no parents
      //echo "O Create site".PHP_EOL;
      global $index;
      $index = new Index;
      $index->makeIndex();
      echo " Index : $site[name]".PHP_EOL;
    } elseif(is_array($v)){
      // this is a normal node. parents and children
      // let's check if it's a blog folder
      if (is_blog($v)) {
        global $blog;
        $blog = new Blog;
        $blog->posts = $v;
        $blog->title = stripNum($show_val);
        $blog->makeBlog();
        echo " Blog : ".$show_val.PHP_EOL;
      } else {  // It's not a blog so it's a category
        echo " Category : $k".PHP_EOL;
      }
    } elseif(!is_dir($show_val)) {
      // this is a leaf node. no children
			global $page;
			$page = new Page;
			$page->path = $show_val;
			$page->makePage();
			echo "  + ".$show_val.PHP_EOL;
    }
    if(is_array($v)){
      // this is what makes it recursive, rerun for childs
      plotSite($v, ($indent+1), false);
    }
  }
  if($mother_run){
    echo "End\n";
    exit(0);
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
    $file_part = pathinfo($this->path);
    $this->title = stripNum($file_part['filename']); 
    if ($this->title[0] == "#") {
      $this->title = substr($this->title, 1);
    }
    $this->content = "<article>";
    if (is_blog($this->title)) {
      $post = new Post;
			$post->path = $this->path;
			$post->title = $this->title;
			$this->content .= $post->makePost();
    } else {
    $data = file_get_contents($this->path);		
    $this->content .= Markdown($data); 
    }
    $this->content .= "</article>";
    $dest_path = str_replace($site['content_dir'], "", $file_part['dirname']);

    $this->rel = findRel(sane($dest_path));
    $this->menu_li = makeMenuLi($this->rel);

    $dest_path = sane($dest_path.'/'.$this->title);
    $dest_path = stripNumPath($dest_path, true);
    $dest_path = $site['export_dir'].$dest_path;

    exec("mkdir -p ".escapeshellarg($dest_path));
    $dest_path .= "/index.html";

    ob_start();
      include $site['theme_dir'].'default.php';
      file_put_contents($dest_path, ob_get_contents());
    ob_end_clean();
  }
}

class Index {
  public $menu_li;
  public function makeIndex(){
    global $site, $menu;
    $this->menu_li = makeMenuLi('');
    $simple_index = <<<EOD
<!DOCTYPE html>
<head>
<title>$site[name]</title>
</head>
<body>
<br>
<h1>$site[name]</h1>
<nav>
$this->menu_li
</nav>
</body>
EOD;
    file_put_contents($site['export_dir']."/index.html", $simple_index);
  }
}

class Blog {
  public $title;
  public $content;
  public $date;
  public $menu_li;
  
  public function makeBlog(){
    global $site, $menu;
    arsort($this->posts);
    $this->content = "<section>".PHP_EOL;
    foreach ($this->posts as $k=>$v) {
    	$post = new Post;
			$post->path = $v;
			$post->title = $k;
			$this->content .= $post->makePost(true);
    }
    $this->content .= "</section>";
    $dest_path = "$site[export_dir]/".stripNum(sane($this->title));
    exec("mkdir -p ".escapeshellarg($dest_path));
    $dest_path .= "/index.html";
    $this->rel = findRel(sane($dest_path));
    $this->menu_li = makeMenuLi($this->rel);
    ob_start();
      include $site['theme_dir'].'default.php';
      file_put_contents($dest_path, ob_get_contents());
    ob_end_clean();
  }
}

class Post {
  public $path;
  public $title;
  public $content;
  public $date;
  public $link;
  public function makePost($short=false) {
    global $site, $date_regex, $post;
    $this->link = sane(pathinfo($this->title, PATHINFO_FILENAME)).'/';
    preg_match($date_regex, $this->title, $date_ar);
    $this->date = preg_replace('![_\ -]!','-',sane($date_ar[0]));
    $this->date = date('l jS \of F Y', strtotime($this->date)).PHP_EOL;
    $this->title = stripDate(pathinfo($this->title, PATHINFO_FILENAME));
    $this->content = file_get_contents($this->path);
    if($short) {
      $parts = explode('<!--more-->',$this->content);
      $content = $parts[0];
      $content .= (empty($parts[1]))?:" - [read more](".$this->link.")";
      $this->content = Markdown($content);}
    else {
      $this->content = Markdown($this->content);
      $this->link = "../".$this->link;
    }
    
    ob_start();
      include set_template('post');
      $this->content = ob_get_contents();
    ob_end_clean();
    return $this->content;
  }
}

?>
