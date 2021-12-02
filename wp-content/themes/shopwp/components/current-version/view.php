<?php 

use Roots\Sage\Extras;

$current_version = Extras\wpshopify_get_latest_version();

?>

<section class="component component-current-version">
  <pre>Current version: <span class="version-pill"><?= $current_version; ?></span> </pre>
</section>