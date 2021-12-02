<?php

  $download = new EDD_Download(35);
  $productVariant = $download->get_prices();

  // echo "<pre>";
  // print_r($productVariant);
  // echo "</pre>";

  include(locate_template('components/products/products-view.php'));

?>
