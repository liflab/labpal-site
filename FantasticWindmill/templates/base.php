<?php
// We first include a couple of user-defined functions that our template
// files will use
include_once("user-defined.php");
require_once("code-processing.php");
?>
<!DOCTYPE html>
<html lang="en-us">
<?php
$body = $page->dom->getElementsByTagName("body")->item(0);
$heading1 = $page->dom->getElementsByTagName("h1")->item(0);

// Show page heading 1 as level 2
if ($heading1)
{
  $heading = $heading1->C14N();
  $heading = preg_replace("/\\bh1\\b/", "h2", $heading);
  $heading1->parentNode->removeChild($heading1);
}
$content = demote_headers(get_inner_html($body));
$content = insert_code_snippets($content);
$content = resolve_javadoc($content);
?>

  <?php include("header.php"); ?>

  <body class="layout-reverse theme-base-0e">

    <?php include("sidebar.php"); ?>

    <!-- Wrap is the content to shift when toggling the sidebar. We wrap the
         content to avoid any CSS collisions with our real content. -->
    <div class="wrap">
      <div class="masthead">
        <div class="container">
          <h3 class="masthead-title">
            <a href="/" title="Home">LabPal</a>
            <small>Run experiments on a computer</small>
          </h3>
        </div>
      </div>

      <div class="container content">
        <?php echo $heading; ?>
        <?php echo $content; ?>
      </div>
    </div>

    <label for="sidebar-checkbox" class="sidebar-toggle"></label>

  </body>
</html>
