<?php

$cachedSidebar = get_transient('wpshopify_sidebar_docs');

if ($cachedSidebar) {
  echo $cachedSidebar;

} else {

  ob_start();
  include(locate_template('templates/content-sidebar-docs.php'));
  $content = ob_get_contents();
  ob_end_clean();

  set_transient('wpshopify_sidebar_docs', $content);

  echo $content;

}
