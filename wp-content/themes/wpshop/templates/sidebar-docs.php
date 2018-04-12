<?php

error_log('Sidebar - checking for cache ...');

$cachedSidebar = get_transient('wpshopify_sidebar_docs');

if ($cachedSidebar) {
  error_log('Sidebar - found cache! Displaying ...');
  echo $cachedSidebar;

} else {

  error_log('Sidebar - didn\'t find cache, populating ...');

  ob_start();
  include(locate_template('templates/content-sidebar-docs.php'));
  $content = ob_get_contents();
  ob_end_clean();

  set_transient('wpshopify_sidebar_docs', $content);

  echo $content;

}
