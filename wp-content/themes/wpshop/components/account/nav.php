<?php

$action = isset($_GET['action']) ? $_GET['action'] : false;
$message = isset($_GET['edd-message']) ? $_GET['edd-message'] : false;

function isShowingLicense($action, $message) {

   if ($action === 'manage_licenses') {

      if ($message === 'cancelled' || $message === 'reactivated') {
         return false;
      }

      return true;

   } else {
      
      if ($action || $message) {
         return false;
      }
      
      return true;

   }

}


$user = wp_get_current_user();
$affiliate_id = affwp_get_affiliate_id($user->ID);

if ($affiliate_id) {
   $is_affiliate = true;

} else {
   $is_affiliate = false;
}

?>

<nav class="account-nav">
   <ul class="account-nav-list">
      <li class="account-nav-list-item">
         <a href="#!" class="account-nav-list-item-link <?= isShowingLicense($action, $message) ? 'is-active' : ''; ?>" data-tab="License">License</a>
      </li>
      <li class="account-nav-list-item">
         <a href="#!" class="account-nav-list-item-link <?= $action === 'update' || $message === 'cancelled' || $message === 'reactivated' ? 'is-active' : ''; ?>" data-tab="Subscriptions">Subscriptions</a>
      </li>
      <li class="account-nav-list-item">
         <a href="#!" class="account-nav-list-item-link" data-tab="Downloads">Downloads</a>
      </li>
      <li class="account-nav-list-item">
         <a href="#!" class="account-nav-list-item-link" data-tab="Purchase History">Purchases</a>
      </li>
      
      <li class="account-nav-list-item">
         <a href="#!" class="account-nav-list-item-link" data-tab="Upgrade">Upgrade</a>
      </li>
      <li class="account-nav-list-item">
         <a href="#!" class="account-nav-list-item-link" data-tab="Settings">Settings</a>
      </li>
      <li class="account-nav-list-item">
         <a href="<?= $is_affiliate ? '/affiliates' : '/become-an-affiliate' ?>" class="account-nav-list-item-link affiliates">Affiliate Dashboard</a>
      </li>
   </ul>
</nav>