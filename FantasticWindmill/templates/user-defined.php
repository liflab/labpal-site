<?php
// Demotes all headers by one level
function demote_headers($s)
{
  $s = preg_replace("/\\bh5\\b/i", "h6", $s);
  $s = preg_replace("/\\bh4\\b/i", "h5", $s);
  $s = preg_replace("/\\bh3\\b/i", "h4", $s);
  $s = preg_replace("/\\bh2\\b/i", "h3", $s);
  $s = preg_replace("/\\bh1\\b/i", "h2", $s);
  return $s;
}

// Demotes all headers by one level
function demote_headers_by_2($s)
{
  $s = preg_replace("/\\bh5\\b/i", "h7", $s);
  $s = preg_replace("/\\bh4\\b/i", "h6", $s);
  $s = preg_replace("/\\bh3\\b/i", "h5", $s);
  $s = preg_replace("/\\bh2\\b/i", "h4", $s);
  $s = preg_replace("/\\bh1\\b/i", "h3", $s);
  return $s;
}

// User-defined function to display the inner HTML of some DOM node
// as a string
function get_inner_html( $node ) 
{
  $innerHTML= '';
  $children = $node->childNodes;
  foreach ($children as $child)
  {
    $innerHTML .= $child->ownerDocument->saveXML( $child );
  }
  return $innerHTML;
}

function create_sidebar($headings_list)
{
  $out = "";
  $out .= "<section>\n<h3>In this page</h3>\n";
  $out .= "<ul class=\style2\">\n";
  foreach ($headings_list as $h)
  {
    $out .= "<li><a href=\"#".$h[1]."\">".$h[0]."</a></li>\n";
  }
  $out .= "</ul>\n</section>";
  return $out;
}

// Extracts all headings of level 2 and their surrounding anchor
function extract_headers($page)
{
  $heading_list = array();
  $headings = $page->dom->getElementsByTagName("h2");
  foreach ($headings as $heading)
  {
    $title = $heading->nodeValue;
    $a = $heading->getElementsByTagName("a")->item(0);
    if ($a == null)
      continue;
    $link = $a->attributes->getNamedItem("name");
    if ($link !== null)
    {
      $heading_list[] = array($title, $link->nodeValue);
    }
  }
  return $heading_list;
}

// User-defined array that maps language codes to language names
$langnames = array("fr" => "Lire en français", "en" => "Read in English");

// Add the "current" class to the page when its slug matches the menu item
function output_menu_item($slug, $page)
{
	$class="";
	if (isset($page->data["section-slug"]) && $page->data["section-slug"] == $slug)
	{
		$class = "class = \"current\"";
	}
	return $class;
}
?>
