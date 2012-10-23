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
  'theme' => '',
  'theme_dir' => 'theme',
  'content_dir' => 'content',
  'export_dir' => 'export',
  'lib_dir' => 'lib',
  'max_posts'=> 2);
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
if ( (!empty($site['theme'])) && is_dir($site['theme_dir']."/".$site['theme']) ) {
  $site['theme_dir'] = $site['theme_dir']."/".$site['theme'];
} elseif (!empty($site['theme'])) {
  echo "Theme '".$site['theme']."' not found, using default theme.".PHP_EOL;
}
$site['theme_dir'] .= (substr($site['theme_dir'], -1) == '/')?'':'/';
// Set in stone
define('SITE', $site['name']);
define('CONTENT',$site['content_dir']);
define('EXPORT',$site['export_dir']);
define('THEME',$site['theme_dir']);
define('DATE_REGEX','!^[0-9]{4}[_\ -]?[0-9]{2}[_\ -]?[0-9]{2}[_\ -]?!');
define('MAX_POSTS',$site['max_posts']);

//-------------------------	Compressing css and js from theme folder 
$yui_sw = (!exec("which yui-compressor"))?'off':'on';
catComp("css", $yui_sw);
catComp("js", $yui_sw);
echo PHP_EOL;

//-------------------------	Copy everything else
if(exec("find ".getcwd()."/".CONTENT.' -type f  | egrep -v ".txt|.md|.markdown"' , $cpfiles)){
		$cpfiles = substr_replace($cpfiles, "", 0, ( strlen(getcwd()) +1) );
}
if(!empty($cpfiles)) {
  echo "Copy files (if modified):".PHP_EOL;
  foreach ($cpfiles as $v) {
    $file_part = pathinfo($v);		
    $dest_path = str_replace(CONTENT, "", $file_part['dirname']);	
    $dest_path = sane($dest_path.'/');
    $dest_path = stripNumPath($dest_path, true);
    $dest_path = EXPORT.$dest_path.$file_part['basename'];
    exec('cp -fu --preserve=timestamps '.escapeshellarg($v).' '.$dest_path);
    echo "  $v -> $dest_path".PHP_EOL;		
  }
}
//------------------------- Make the site's tree
if(exec("find ".getcwd()."/".CONTENT.' | egrep ".txt|.md|.markdown"' , $files)){
  $files = substr_replace($files, "", 0, ( strlen(getcwd()) +1) );
  $files = array_combine(array_values($files), array_values($files));
}
$tree = explodeTree($files, "/", true);
ksortTree($tree);
//print_r($tree);
$menu = array(); 
$menu = makeMenu($tree[CONTENT]);
$menu = stripNumTree($menu, 'both');
//print_r($menu);

plotSite($tree);

############################### Menu making functions

//--- Make the $menu array
function makeMenu($array=false)
{
  global $menu, $test_trail;
  $skipers = array('_','#','0'); // skip prefixed filenames
  if (!is_array($array)) {
    $nice_name = sane(pathinfo($array, PATHINFO_FILENAME));
    if(!in_array($nice_name[0], $skipers)) {
      $dest_path = get_folder($array);
      $sprtr = "/";
      if ($dest_path == ""){$sprtr = "";}
      $dest_path = $dest_path.$sprtr.$nice_name."/".$test_trail;
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
    $dest_path = get_folder($array);
    return $dest_path; 
  }
}
//--- Make $menu_li for the current page (a string, html nested unordered list)
//--- and give id="sel" to the li of the current page.
function makeMenuLi($rel, $self=false) {
  global $menu;
  $menu_li = ' <ul>'.PHP_EOL;
  $menu_li .= plotMenu($menu, $rel, $self);
  $menu_li .= ' </ul>'.PHP_EOL;
  return $menu_li;
}
function plotMenu($arr, $rel, $self, $indent=2){
  $link = "";
  foreach($arr as $k=>$v){
    $spaces = str_repeat("  ", $indent);
    if(!is_array($v)){
      $k = pathinfo($k, PATHINFO_FILENAME);
      $class_sel = ($self == $k)?' class="sel"':'';      
      $link .= $spaces."<li$class_sel><a href=\"$rel$v\">$k</a></li>".PHP_EOL;
    } else {
      $sk = sane($k);
      $class_sel = ($self == $k)?' class="sel cat"':' class="cat"'; 
      $link .= "$spaces<li$class_sel>$k".PHP_EOL."$spaces<ul id=\"$sk\">".PHP_EOL;
      $link .= plotMenu($v, $rel, $self, ($indent+1));
      $link .= "$spaces  </ul>".PHP_EOL."$spaces</li>".PHP_EOL;
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
      echo " Index : ".SITE.PHP_EOL;
    } elseif(is_array($v)){
      // this is a normal node. parents and children
      // let's check if it's a blog folder
      if (is_blog($v)) {
        global $blog;
        echo " Blog : ".$show_val.PHP_EOL;        
        $blog = new Blog;
        $blog->posts = $v;
        $blog->path = get_folder($v);
        $blog->title = stripNum($show_val);
        $blog->makeBlog();
      } else {  // It's not a blog so it's a category
        echo " Category : $k".PHP_EOL;
      }
    } elseif(!is_dir($show_val)) {
      // this is a leaf node. no children
			echo "  + ".$show_val.PHP_EOL;      
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
    exit(0);
  }
}

############################################
//---------------------------------- Classes
# PAGE
class Page {
  public $title;
  public $content;
  public $path;
  public $rel;
  public $menu_li;
  public function makePage(){
    global $site;
    $file_part = pathinfo($this->path);
    $this->title = stripNum($file_part['filename']); 
    if ($this->title[0] == "#") {
      $this->title = substr($this->title, 1);
    }
    $dest_path = get_folder($this->path);
    $this->rel = findRel(sane($dest_path));
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

    $this->menu_li = makeMenuLi($this->rel, $this->title);

    $dest_path = sane($dest_path.'/'.$this->title);
    $dest_path = stripNumPath($dest_path, true);
    
    $dest_path = EXPORT.'/'.$dest_path;
    exec("mkdir -p ".escapeshellarg($dest_path));
    $dest_path .= "/index.html";

    ob_start();
      include THEME.'default.php';
      file_put_contents($dest_path, ob_get_contents());
    ob_end_clean();
  }
}
# INDEX
class Index {
  public $menu_li;
  public function makeIndex(){
    global $site;
    $this->menu_li = makeMenuLi('');
    $template = (is_file(THEME.'index.php'))?THEME.'index.php':THEME.'../index.php';
  ob_start();
    include $template;
    file_put_contents(EXPORT."/index.html", ob_get_contents());
  ob_end_clean();
  }
  /*public function get_style() {
    $link = ()?'<':'';
  }*/
}
# BLOG
class Blog {
  public $title;
  public $content;
  public $date;
  public $menu_li;
  public $posts;
  public function makeBlog(){
    global $site, $menu;
    arsort($this->posts);
    $this->content = $this->makeBlogRoll($this->posts);
    $dest_path = EXPORT."/".sane($this->title);
    exec("mkdir -p ".escapeshellarg($dest_path));
    
    $this->rel = findRel($this->path);
    $this->rel = substr($this->rel, 0, 3);
    $this->menu_li = makeMenuLi($this->rel, $this->title);

    $title = $this->title;

    $pages = ceil(count($this->posts)/MAX_POSTS);
    echo count($this->posts).'post------'.$pages.'page------'.PHP_EOL;
    $i = 1;
    while ($i <= $pages) {
      $this->title = $title;
      $prev = '<a href="page'.($i+1).'.html">&larr; prev</a> ';
      $next = '<a href="page'.($i-1).'.html">next &rarr;</a> '; 
      $path = $dest_path.'/page'.$i.'.html';
      if ($i == 2) {
        $next = '<a href="index.html">next &rarr;</a> ';
      }
      if ($i == $pages) {
        $prev = '';
      }
      if ($i == 1) {
        $path = $dest_path.'/index.html'; 
        $next = '';
      } else {
        $this->title .= ' | Page '.$i;
      }
      $cur_page = array_splice($this->posts, 0, MAX_POSTS);
      $this->content = $this->makeBlogRoll($cur_page);
      $this->content .= ($pages != 1)?PHP_EOL.'<nav class="blog_nav">'.$prev.$next.'</nav>'.PHP_EOL:'';
      ob_start();
        include THEME.'default.php';
        file_put_contents($path, ob_get_contents());
      ob_end_clean();      
      $i++;
    }
  }
  public function makeBlogRoll($posts) {
    $blog_roll = "<section>".PHP_EOL;
    foreach ($posts as $k=>$v) {
    	$post = new Post;
		  $post->path = $v;
		  $post->title = $k;
		  $blog_roll .= $post->makePost(true);
    }
    $blog_roll .= "</section>";
    return $blog_roll;
  }
}
# POST
class Post {
  public $path;
  public $title;
  public $content;
  public $date;
  public $link;
  public function makePost($short=false) {
    $this->link = sane(pathinfo($this->title, PATHINFO_FILENAME)).'/';
    preg_match(DATE_REGEX, $this->title, $date_ar);
    $this->date = preg_replace('![_\ -]!','-',sane($date_ar[0]));
    $this->date = date('l jS \of F Y', strtotime($this->date)).PHP_EOL;
    $this->title = stripDate(pathinfo($this->title, PATHINFO_FILENAME));
    $this->content = file_get_contents($this->path);
    if($short) {
      $parts = explode('<!--more-->',$this->content);
      $content = $parts[0];
      $content .= (empty($parts[1]))?'':" - [read more](".$this->link.")";
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
