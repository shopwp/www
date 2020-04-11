<?php

use Roots\Sage\Setup;
use Roots\Sage\Extras;
use Roots\Sage\Wrapper;

require_once 'lib/Mobile-Detect/Mobile_Detect.php';
$detect = new Mobile_Detect;

$mobileBodyClass = $detect->isMobile() ? 'is-mobile' : '';

$notices_enabled = get_field('theme_notice_enable', 'option');

if ($notices_enabled) {
  $mobileBodyClass .= ' is-showing-notices';
}

if (is_page('faq')) {
   $props = ' itemscope itemtype="https://schema.org/FAQPage"';

} else if (is_page('how')) {
   $props = ' itemscope itemtype="http://schema.org/HowTo"';

} else {
   $props = '';
}

?>

<!doctype html>
<html <?php language_attributes(); ?> <?= $props; ?>>

  <?php

  if ( is_page('purchase-confirmation') ) {

    $purchaseData = Extras\wps_get_recent_receipt_data();

   if (!empty($purchaseData)) {

  ?>

  <script>

    var previousURL = document.referrer,
        neededURL = '/checkout';

    if (previousURL.indexOf(neededURL) !== -1) {

      window.dataLayer = window.dataLayer || [];

      dataLayer.push({
         'transactionId': '<?php echo $purchaseData['transaction']->ID; ?>',
         'transactionAffiliation': '<?php echo $purchaseData['payment']['cart_details'][0]['name']; ?>',
         'transactionTotal': <?php echo $purchaseData['payment']['cart_details'][0]['subtotal']; ?>,
         'transactionTax': 0,
         'transactionShipping': 0,
         'transactionProducts': [{
           'sku': '<?php echo $purchaseData['transaction']->ID; ?>',
           'name': '<?php echo $purchaseData['payment']['cart_details'][0]['name']; ?>',
           'category': 'WP Shopify License',
           'price': <?php echo $purchaseData['payment']['cart_details'][0]['price']; ?>,
           'quantity': 1
         }],
         'event': 'transactionComplete'
      });

    } else {
      console.log('Did not come from Checkout page');

    }

  </script>

  <?php } 

   } ?>

  <?php get_template_part('templates/head'); ?>

  <body <?php body_class($mobileBodyClass); ?>>

  

    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NWRL8QH&gtm_auth=zEmWFISEpQvchduPXr4jaQ&gtm_preview=env-2&gtm_cookies_win=x"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->

  <?php  if (is_page('purchase-confirmation')) { ?>

   <canvas id="confetti-holder" style="width: 100%;z-index: 4;position: absolute;top: -180px;left: 0;margin-top: 0;"></canvas>

  <?php } ?>


   <?php if (!is_page('checkout')) { ?>
      <?php include(locate_template('components/getting-started/view.php')); ?>
   <?php } ?>

    <?php

    if ($notices_enabled) {
      get_template_part('components/notices/notices-controller');
    }

    do_action('get_header');
    get_template_part('templates/header');

    ?>

      <?php if (!is_page('checkout')) { ?>
         <script>
            jQuery('.menu-item-has-children')
               .mouseenter(function() {
                  jQuery(this).addClass('is-active')
                  jQuery('body').addClass('is-showing-sub-menu')
               })
               .mouseleave(function(event) {

                  var $relatedElement = jQuery(event.relatedTarget);

                  if (!$relatedElement.parents('.menu-item-has-children').length) {
                     jQuery(this)
                     .closest('.menu-item-has-children')
                     .removeClass('is-active')

                     jQuery('body').removeClass('is-showing-sub-menu')
                  }

               });
         </script>
      <?php } ?>

      <main class="main l-fill <?php echo isRegisteredAndPurchasing() ? ' is-registered-and-purchasing' : ''; ?>" role="document">

         <div class="main-inner <?php echo is_singular('post') ? 'l-contain-narrow' : ''; ?>">
            <?php include Wrapper\template_path(); ?>
         </div>

        <?php get_template_part('templates/components'); ?>


      </main>

      
    <?php

      do_action('get_footer');

      get_template_part('templates/footer');
      wp_footer();

    ?>

<?php

if ( get_field('theme_notice_enable', 'option') ) {
  include(locate_template('components/notices/notices-view.php'));
}
?>
  <script>
      (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
      (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
      m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
      })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

      ga('create', 'UA-101619037-1', 'auto');
      ga('send', 'pageview');


    </script>


<?php  if (is_page('purchase-confirmation')) { ?>

   <script>
      
      var confetti = new ConfettiGenerator({"target":"confetti-holder","max":"100","size":"1","animate":true,"props":["circle","square","triangle","line"],"colors":[[165,104,246],[230,61,135],[0,199,228],[253,214,126]],"clock":"50","rotate":false,"width":"1680","height":"947","start_from_edge":true,"respawn":false});
      confetti.render();

   </script>

  <?php } ?>

   <?php if (!is_page('checkout')) { ?>
      <script src="https://unpkg.com/@popperjs/core@2"></script>
      <script src="https://unpkg.com/tippy.js@6"></script>
   <?php } ?>

  </body>
</html>
