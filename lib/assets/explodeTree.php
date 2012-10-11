<?php

/**
 * Explode any single-dimensional array into a full blown tree structure,
 * based on the delimiters found in it's keys.
 *
 * @author  Kevin van Zonneveld <kevin@vanzonneveld.net>
 * @author  Lachlan Donald
 * @author  Takkie
 * @copyright 2008 Kevin van Zonneveld (http://kevin.vanzonneveld.net)
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD Licence
 * @version   SVN: Release: $Id: explodeTree.inc.php 89 2008-09-05 20:52:48Z kevin $
 * @link    http://kevin.vanzonneveld.net/
 *
 * @param array   $array
 * @param string  $delimiter
 * @param boolean $baseval
 *
 * @return array
 */
function explodeTree($array, $delimiter = '_', $baseval = false)
{
  if(!is_array($array)) return false;
  $splitRE   = '/' . preg_quote($delimiter, '/') . '/';
  $returnArr = array();
  foreach ($array as $key => $val) {
    // Get parent parts and the current leaf
    $parts  = preg_split($splitRE, $key, -1, PREG_SPLIT_NO_EMPTY);
    $leafPart = array_pop($parts);
 
    // Build parent structure
    // Might be slow for really deep and large structures
    $parentArr = &$returnArr;
    foreach ($parts as $part) {
      if (!isset($parentArr[$part])) {
        $parentArr[$part] = array();
      } elseif (!is_array($parentArr[$part])) {
        if ($baseval) {
          $parentArr[$part] = array('__base_val' => $parentArr[$part]);
        } else {
          $parentArr[$part] = array();
        }
      }
      $parentArr = &$parentArr[$part];
    }
 
    // Add the final part to the structure
    if (empty($parentArr[$leafPart])) {
      $parentArr[$leafPart] = $val;
    } elseif ($baseval && is_array($parentArr[$leafPart])) {
      $parentArr[$leafPart]['__base_val'] = $val;
    }
  }
  return $returnArr;
}


/**
 * Recusive alternative to ksort
 *
 * @author  Kevin van Zonneveld <kevin@vanzonneveld.net>
 * @copyright 2008 Kevin van Zonneveld (http://kevin.vanzonneveld.net)
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD Licence
 * @version   SVN: Release: $Id: ksortTree.inc.php 223 2009-01-25 13:35:12Z kevin $
 * @link    http://kevin.vanzonneveld.net/
 *
 * @param array $array
 */
function ksortTree( &$array )
{
  if (!is_array($array)) {
    return false;
  }

  ksort($array);
  foreach ($array as $k=>$v) {
    ksortTree($array[$k]);
  }
  return true;
}


/**
 * Recursive alternative to str_replace that supports replacing keys as well
 *
 * @param string  $search
 * @param string  $replace
 * @param array   $array
 * @param boolean $keys_too
 *
 * @return array
 */
function replaceTree($search="", $replace="", $array=false, $keys_too=false)
{
  if (!is_array($array)) {
    // Regular replace
    return str_replace($search, $replace, $array);
  }
 
  $newArr = array();
  foreach ($array as $k=>$v) {
    // Replace keys as well?
    $add_key = $k;
    if ($keys_too) {
      $add_key = str_replace($search, $replace, $k);
    }
 
    // Recurse
    $newArr[$add_key] = replaceTree($search, $replace, $v, $keys_too);
  }
  return $newArr;
}




/*
########################  example plotTree function



function plotTree($arr, $indent=0, $mother_run=true){
    if($mother_run){
        // the beginning of plotTree. We're at rootlevel
        echo "start\n";
    }
 
    foreach($arr as $k=>$v){
        // skip the baseval and _prefixed filenames.
        if(($k == "__base_val") || ($k[0] == "_")) continue;
        // determine the real value of this node.
        $show_val = ( is_array($v) ? $v["__base_val"] : $v );
 
        // show the indents
        echo str_repeat("  ", $indent);
        if($indent == 0){
            // this is a root node. no parents
            echo "O ";
        } elseif(is_array($v)){
            // this is a normal node. parents and children
            echo "+ ";
        } else{
            // this is a leaf node. no children
            echo "- ";
        }
 
        // show the actual node
        echo $k . " (".$show_val.")"."\n";
 
        if(is_array($v)){
            // this is what makes it recursive, rerun for childs
            plotTree($v, ($indent+1), false);
        }
    }
 
    if($mother_run){
        echo "end\n";
    }
}
*/



?>
