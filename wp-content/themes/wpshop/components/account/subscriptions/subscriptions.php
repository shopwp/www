<?php

$action = isset($_GET['action']) ? $_GET['action'] : false;
$message = isset($_GET['edd-message']) ? $_GET['edd-message'] : false;

?>

<section class="component component-account-subscriptions <?= $action === 'update' || $message === 'cancelled' || $message === 'reactivated' ? 'is-active' : ''; ?>" data-tab="Subscriptions">
  <h4 class="component-account-heading">Subscriptions</h4>
  <?php echo do_shortcode('[edd_subscriptions]'); ?>
</section>
