<?php

use Roots\Sage\Extras;

$current_version = Extras\wpshopify_get_latest_version();

?>

<div class="docs-version-wrapper-container">

  <p style="text-align:center;">
    <a href="https://wpshop.io/free/releases/<?= $current_version; ?>/wp-shopify.zip" class="btn btn-secondary btn-l btn-download-free">
      <i class="fas fa-download"></i> Download Free version
    </a>
  </p>

  <small class="docs-version-wrapper docs-version-wrapper-inline l-row">
    <span>Latest version:</span>
    <p class="docs-version"><i class="fal fa-cog fa-spin"></i></p>
  </small>

</div>
