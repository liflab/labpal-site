<?php

$javadoc_root = "http://liflab.github.io/beepbeep-3/javadoc/";
$source_location = "/home/sylvain/Workspaces/beepbeep-3/";
//$source_location = "C:/Users/Sylvain/.babun/cygwin/home/Sylvain/Workspaces/beepbeep-3/";
$github_source_location = "https://github.com/liflab/beepbeep-3/blob/master/";

function insert_code_snippets($s)
{
  $s = resolve_snipm($s);
  $s = resolve_snips($s);
  return $s;
}

function resolve_snipm($s)
{
  global $source_location, $github_source_location;
  preg_match_all("/\\{@snipm (.*?)\\}\\{(.*?)\\}/", $s, $matches, PREG_SET_ORDER);
  foreach($matches as $match)
  {
    $filename = $source_location.$match[1];
    if (file_exists($filename))
    {
      $snip_content = file_get_contents($filename);
      $snip_matches = array();
      $line_nb = preg_match_line("/\\/\\/\\s*".$match[2]."/s", $snip_content);
      preg_match("/\\/\\/\\s*".$match[2]."(.*?)\\/\\/\\s*".$match[2]."/ms", $snip_content, $snip_matches);
      $contents = "<pre><code>".fix_indentation($snip_matches[1])."</code></pre>\n";
      $contents .= "<a class=\"code-ref\" href=\"".$github_source_location.$match[1]."#L".($line_nb + 1)."\"><span>[Code on GitHub]</span></a>\n";
      $s = str_replace($match[0], $contents, $s);
    }
    else
    {
      $s = str_replace($match[0], "<pre><code>Source code not found</code></pre>", $s);
    }
  }
  return $s;
}

/**
 * Works like preg_match, but returns the number of the first line of the
 * matched pattern. The pattern to find must not span multiple lines.
 */
function preg_match_line($pattern, $content)
{
  $lines = explode("\n", $content);
  for ($i = 0; $i < count($lines); $i++)
  {
    $line = $lines[$i];
    if (preg_match($pattern, $line))
    {
      return $i;
    }
  }
  return -1;
}

/**
 * Extracts a structured block from the source code. The marker defines
 * the first line of the file to include; further lines will be included
 * until the nesting level of the braces falls from 1 to 0.
 */
function resolve_snips($s)
{
  global $source_location, $github_source_location;
  preg_match_all("/\\{@snips (.*?)\\}\\{(.*?)\\}/", $s, $matches, PREG_SET_ORDER);
  foreach($matches as $match)
  {
    $filename = $source_location.$match[1];
    if (!file_exists($filename))
    {
      $s = str_replace($match[0], "<pre><code>Source code not found</code></pre>", $s);
      return $s;
    }
    $snip_content = file_get_contents($filename);
    list($structured_content, $line_nb) = extract_structured($snip_content, $match[2]);
    $contents = "<pre><code>".fix_indentation($structured_content)."</code></pre>\n";
    $contents .= "<a class=\"code-ref\" href=\"".$github_source_location.$match[1]."#L".($line_nb + 1)."\"><span>[Code on GitHub]</span></a>\n";
    $s = str_replace($match[0], $contents, $s);
  }
  return $s;
}

function extract_structured($file_contents, $marker)
{
  $lines = explode("\n", $file_contents);
  $line_nb = 0;
  for ($i = 0; $i < count($lines); $i++)
  {
    if (strpos($lines[$i], $marker) !== false)
    {
      $line_nb = $i;
      break;
    }
  }
  $out = "";
  $nesting = 0;
  for ($j = $i; $j < count($lines); $j++)
  {
    $line = $lines[$j];
    if ($j == $i)
    {
      $out .= rtrim($line)."\n";
    }
    else
    {
      for ($k = 0; $k < strlen($line); $k++)
      {
	$char = substr($line, $k, 1);
	if ($char == "{")
	  $nesting++;
	if ($char == "}")
	{
	  if ($nesting == 1)
	  {
	    // Last line to include
	    $out .= $line."\n";
	    break 2;
	  }
	  $nesting--;
	}
      }
    }
    $out .= rtrim($line)."\n";
  }
  return array($out, $line_nb);
}

/**
 * Removes from each line of s the minimum number of spaces common
 * to all lines of s
 */
function fix_indentation($s)
{
  // Replace tabs by spaces
  $s = str_replace("\t", "    ", $s);
  $lines = explode("\n", $s);
  $num_spaces = 100000; // "MAX_INT"
  $out = "";
  // We skip the first and last line
  for ($i = 1; $i < count($lines) - 1; $i++)
  {
    $line = $lines[$i];
    $sp = strlen($line) - strlen(ltrim($line));
    $num_spaces = min($num_spaces, $sp);
  }
  for ($i = 1; $i < count($lines) - 1; $i++)
  {
    $line = $lines[$i];
    $out .= substr($line, $num_spaces)."\n";
  }
  return $out;
}

/**
 * Replaces all strings of the form "jdx:something" into an URL pointing
 * the the corresponding Javadoc
 */
function resolve_javadoc($s)
{
  preg_match_all("/\\{@link\\s*(jd.:.*?)(\\s+.*?){0,1}\\}/", $s, $matches, PREG_SET_ORDER);
  foreach ($matches as $match)
  {
    $url = get_javadoc_url($match[1]);
    if (isset($match[2]) && !empty($match[2]))
    {
      $s = str_replace($match[0], "<a href=\"$url\">".trim($match[2])."</a>", $s);
    }
    else
    {
      $s = str_replace($match[0], "<a href=\"$url\">".trim($match[1])."</a>", $s);
    }
  }
  return $s;
}

/**
 * Find the Javadoc entry corresponding to a class
 */
function get_javadoc_url($string)
{
  global $javadoc_root;
  $left_part = substr($string, 0, 4);
  $right_part = substr($string, 4);
  $url = "#";
  switch ($left_part)
  {
    case "jdp:":
      // Package
      $parts = explode(".", $right_part);
      $path = implode("/", $parts);
      $url = $javadoc_root.$path."/package-summary.html";
      break;
    case "jdc:":
    case "jdi:":
      // Class or interface
      $parts = explode(".", $right_part);
      $path = implode("/", $parts);
      $url = $javadoc_root.$path.".html";
      break;
    case "jdm:":
      // Method
      $big_parts = explode("#", $right_part);
      $parts = explode(".", $big_parts[0]);
      $last_part = $parts[count($parts) - 1];
      unset($parts[count($parts) - 1]);
      $path = implode("/", $parts);
      $url = $javadoc_root.$path."/".$last_part.".html#".$big_parts[1];
      break;
  }
  return $url;
}
?>