<?php

global $wp;

if (is_page('affiliate-login') && is_user_logged_in()) {   
   wp_redirect('/account');
   exit;
}

if (is_page('account') && is_user_logged_in()) {   
   get_template_part('templates/account/view');
   exit;
}

if ($wp->request === 'account/purchases') {   
   wp_redirect('/account?accountpage=purchases');
   exit;
}

use Roots\Sage\Setup;
use Roots\Sage\Extras;
use Roots\Sage\Wrapper;

require_once 'lib/Mobile-Detect/Mobile_Detect.php';
$detect = new Mobile_Detect;

$GLOBALS['is_mobile'] = $detect;

$mobileBodyClass = $detect->isMobile() ? 'is-mobile' : '';

if (is_page('checkout') || is_page('purchase-confirmation')) {
   $notices_enabled = false;
} else {
   $notices_enabled = get_field('theme_notice_enable', 'option');
}


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
           'category': 'ShopWP License',
           'price': <?php echo $purchaseData['payment']['cart_details'][0]['price']; ?>,
           'quantity': 1
         }],
         'event': 'transactionComplete'
      });

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
               
         <?php  if (is_page('purchase-confirmation')) { ?>
            
            <svg version="1.1" id="winding1" xmlns="http://www.w3.org/2000/svg" x="0" y="0" viewBox="0 0 1453.74 1001.18" style="enable-background:new 0 0 1453.74 1001.18" xml:space="preserve"><path d="M142.37 804.76h-.12v-.07l.12.07z" style="fill:#fabb7b"/><path class="st1" d="M377.61 238.37h144.75v299.16H377.61z"/><path class="st2" d="M377.62 238.37v299.16l-4.54 5.25-61.22 71.05-1.3 1.5-1.33 1.55-1.44 1.67V319.39z"/><circle class="st2" cx="502.97" cy="390.65" r="12.41"/><path class="st3" d="M406.56 456.31c-.48 4.55-1.11 9.15-1.94 13.76-.96 5.49-2.19 11-3.68 16.53-.67 2.46-1.37 4.89-2.14 7.3a209.815 209.815 0 0 1-18.19 41c-1.54 2.67-3.12 5.29-4.73 7.86-23.34 37.32-53 62.55-63.85 71l-1 .78c-1.9 1.47-3 2.28-3.16 2.37-.03-.05-.05-.1-.07-.15V421.33a93.95 93.95 0 0 1 7.18-12.51l8 28.45 12.92-59.07a296.843 296.843 0 0 1 44.71-44.87c3.36-2.69 5.32-4.09 5.32-4.09s8.58 18.27 15 46.14l-25.11 31.54 29.79-5.42c2.47 18.17 2.79 36.57.95 54.81z"/><path class="st4" d="m487.47 492.01-53.33 21.62 40.48 4.69c-8.3 14.7-18.59 28.19-30.58 40.08-1.77 1.74-3.57 3.41-5.4 5l-37.73-15.77 13.41 34.06c-50.32 31.78-106.57 35.3-106.57 35.3s6.74-81.58 61.3-135.06c8.36-8.15 17.51-15.46 27.32-21.8v49l32.38-66.21c40.33-17.31 76.56-19.58 76.56-19.58s-2.56 31.78-17.84 68.67z"/><path class="st2" d="m863.01 580.14-166.51.93 98 219.61H612.3s.2-39.38 49.12-34.76c0 0-118.58-174.7-95.48-241.16 22.65-65.15 224.38-99.32 301.7-64.9 67 29.82 120.25 218.13 120.25 218.13l118.15-51.07 59.15 115.67-21.55 58.1h-36.83a46.806 46.806 0 0 1 19.54-38.38l-20.92-16s-124.57 58.42-156.93 54.15c-72.35-9.55-194.18-352.15-194.18-352.15"/><path class="st1" d="m729.85 365.51 77.9 48.28c20.89 13 47.83-2.29 47.51-26.86l-1.09-81.82c-.23-17.19-14.35-30.93-31.54-30.7-4.14.06-8.23.94-12.03 2.6l-76.85 33.54c-22.94 10.04-25.21 41.75-3.9 54.96z"/><path class="st5" d="M741.81 385.8s-90.84 29.35-129.54 21.76-95.4-52.33-101-68.67-23.12-37.29-13.11-43.11c3.69-2.14 24.58 25.63 24.58 25.63l-.85-18 98.24 44 87-17.94"/><path class="st3" d="m782.17 299.3-28.73-59.55s18.38-22.38 6.73-37.48-25.65 0-25.65 0-18.38-19.65-44.21-10.55-27 43.87-16.5 63c15.53 28.26 36 35.78 62.53 16.31l17.82 37.13"/><path class="st2" d="M745.75 195.82s-3.94-29.76-42.33-27.39-40.76 16.88-56.4 13.63-17.47 43.32 24.38 38.59 39.84-6.92 39.84-6.92 5.1 18.2 15.28 14.38 8.92-23.66 8.92-23.66"/><path class="st6" d="m685.19 245.3-3.18 16.02"/><circle class="st7" cx="692.91" cy="241.12" r="4.44"/><circle class="st7" cx="675.42" cy="242.05" r="4.44"/><path class="st5" d="m868.82 461.82 6.85-46.49L829 348.07M855.25 286.7s70.39 67.47 80.8 112.59c8.62 37.34-9.1 80.33-18.47 116.37-1.73 6.93-3.82 13.77-6.25 20.49"/><path class="st6" d="M911.87 518.16c-7.73 20-19.38 51.12-23.34 50.56-4.61-.66 1.55-39.57 1.55-39.57s-15.61 44.21-21.29 42.42c-7.43-2.34 10.37-51.92 10.37-51.92s-16.84 44.59-22 41.78c-5-2.71 6.67-44.26 6.67-44.26s-20.06 15.2-20.88 9.15c-1-7.5 24.38-35.91 24.38-35.91l4.93-26.15M772.74 513.68l51.63 113.64"/><path class="st5" d="m367.2 497.56-56.73 117.97"/><path class="st3" d="M185.81 682.71c-18.9-9.1-17.89-42.8-7.17-48.64 8.93-4.87 9.94 18.13 9.94 18.13s9.86-16.49 18-12.59 1.37 21.89 1.37 21.89 18.55-13.58 20.32-3.58c2.13 12-23.52 33.87-42.46 24.79z"/><path class="st5" d="m185.81 682.7-56.73 117.97"/><path class="st4" d="M630.67 660.65c-18.9-9.09-17.88-42.79-7.17-48.63 8.94-4.87 9.94 18.13 9.94 18.13s9.86-16.5 18-12.59 1.31 21.89 1.31 21.89 18.61-13.53 20.38-3.53c2.14 12-23.56 33.82-42.46 24.73z"/><path class="st5" d="m630.68 660.65-67.34 140.02"/><path class="st3" d="M1347.35 682.71c-18.91-9.1-17.89-42.8-7.17-48.64 8.93-4.87 9.94 18.13 9.94 18.13s9.86-16.49 18-12.59 1.38 21.89 1.38 21.89 18.58-13.58 20.36-3.56c2.08 11.98-23.61 33.85-42.51 24.77z"/><path class="st5" d="m1347.35 682.7-56.73 117.97"/><path class="st4" d="M1394.58 752.86c-18.91-9.09-17.89-42.8-7.17-48.64 8.93-4.86 9.94 18.13 9.94 18.13s9.86-16.49 18-12.59 1.37 21.89 1.37 21.89 18.59-13.58 20.36-3.56c2.09 12.02-23.6 33.83-42.5 24.77z"/><path class="st5" d="m1394.58 752.86-22.99 47.81"/><path class="st1" d="M54.75 734.17c-18.9-9.09-17.88-42.8-7.17-48.63 8.94-4.87 9.94 18.13 9.94 18.13s9.86-16.5 18-12.6 1.31 21.85 1.31 21.85 18.59-13.57 20.36-3.55c2.14 12.05-23.56 33.89-42.44 24.8z"/><path class="st5" d="m54.74 734.17-31.98 66.5"/><path class="st1" d="M517.06 733.1c-18.91-9.1-17.89-42.8-7.17-48.64 8.93-4.87 9.94 18.13 9.94 18.13s9.86-16.49 17.95-12.59 1.38 21.92 1.38 21.92 18.58-13.58 20.36-3.56c2.13 11.98-23.57 33.82-42.46 24.74z"/><path class="st5" d="m517.06 733.09-31.98 66.5"/><path class="st1" d="M782.75 734.13c-18.91-9.09-17.89-42.8-7.17-48.64 8.93-4.87 9.94 18.13 9.94 18.13s9.86-16.49 18-12.59 1.38 21.89 1.38 21.89 18.58-13.58 20.36-3.56c2.11 12.02-23.58 33.86-42.51 24.77z"/><path class="st5" d="m782.78 734.12-31.98 66.51M358.05 418.37l-49.36 102.64M0 800.67h1453.74"/><path class="st7" d="M360.01 532.68c-3.14.03-6.25-.66-9.09-2-9.91-4.77-13.71-16.75-13.95-26.38-.23-9.21 2.58-17.27 6.85-19.59.49-.3 1.05-.47 1.63-.49 3.67 0 5.77 10.16 6.09 17.37l.22 5 2.57-4.28c2.07-3.47 8.64-12.47 14-12.47.69-.01 1.38.15 2 .45 5.63 2.7 2.35 15.37.62 20l-1.75 4.68 4-2.95c2.65-1.93 10.52-7 15.07-7 1.41-.2 2.71.78 2.91 2.18.01.09.02.17.02.26.55 3.14-1.19 7.4-4.78 11.68-5.56 6.78-15.93 13.54-26.41 13.54z"/><path class="st8" d="M345.45 485.71c.47 0 2.11 1.28 3.39 7 .63 2.96 1.04 5.97 1.2 9l.44 10 5.13-8.56c2.3-3.83 8.44-11.74 12.77-11.74.46 0 .91.1 1.32.31 4.19 2 1.66 13.29-.14 18.13l-3.49 9.36 8.07-5.89c3.54-2.57 10.51-6.66 14.19-6.66 1 0 1.27.19 1.45 1.21.47 2.66-1.19 6.56-4.45 10.45-5.4 6.44-15.32 13-25.32 13-2.92.02-5.8-.61-8.44-1.86-9.3-4.48-12.87-15.88-13.1-25.06-.22-8.54 2.34-16.21 6.07-18.24.27-.18.59-.28.91-.31m0-3c-.83.03-1.63.26-2.35.67-10.72 5.84-11.73 39.55 7.17 48.64 3.04 1.45 6.37 2.19 9.74 2.16 16.87 0 34.5-16.89 32.72-26.93a4 4 0 0 0-4.29-3.69c-.04 0-.07.01-.11.01-6 0-16 7.24-16 7.24s6.72-18-1.37-21.89c-.82-.4-1.71-.6-2.62-.6-7.45 0-15.34 13.2-15.34 13.2s-.82-18.81-7.59-18.81h.04z"/><path class="st7" d="M354.75 443.21c-2.7.02-5.38-.57-7.82-1.73-8.55-4.11-11.82-14.46-12-22.78-.2-7.94 2.21-14.88 5.86-16.87.4-.24.86-.38 1.33-.4 3 0 4.84 8.9 5.1 14.93l.22 5 2.56-4.29c1.79-3 7.45-10.75 12.07-10.75.56 0 1.12.12 1.62.37 4.74 2.28 1.83 13.44.44 17.17l-1.75 4.69 4-3c2.29-1.67 9.1-6 13-6 1.12-.17 2.17.6 2.34 1.72.01.09.02.19.02.28.47 2.68-1 6.32-4.12 10-4.87 5.8-13.87 11.66-22.87 11.66z"/><path class="st8" d="M342.07 402.92c.27.06 1.62 1.36 2.65 6.21.49 2.4.8 4.83.94 7.28l.44 10 5.13-8.59c2.42-4 7.51-10 10.78-10 .35-.01.69.07 1 .23 3.22 1.54 1.38 10.72-.31 15.29l-3.5 9.35 8.07-5.88c3-2.21 9-5.72 12.12-5.73.23-.02.45.02.66.11.12.19.2.41.22.63.39 2.2-1 5.48-3.78 8.77-4.65 5.54-13.17 11.13-21.74 11.13-2.48.02-4.93-.52-7.17-1.58-7.94-3.82-11-13.59-11.18-21.47-.21-8.25 2.39-14 5.07-15.51.19-.12.4-.2.62-.22m0-3c-.72.02-1.43.22-2.05.58-9.33 5.08-10.21 34.41 6.24 42.32 2.64 1.26 5.54 1.9 8.47 1.88 14.68 0 30-14.7 28.47-23.43a3.507 3.507 0 0 0-3.78-3.21h-.05c-5.25 0-13.88 6.31-13.88 6.31s5.84-15.67-1.2-19.05c-.71-.34-1.48-.52-2.27-.52-6.49 0-13.35 11.48-13.35 11.48s-.74-16.38-6.62-16.38l.02.02z"/><path class="st6" d="m630.68 660.65-14.82 29.99M773.44 753.56l-21.63 44.97"/></svg>


            <div class="stage">
               <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Layer_1" x="0px" y="0px" viewBox="0 0 962 115" xml:space="preserve" preserveAspectRatio="none" class="svg replaced-svg">
                  <path d="M0,0c0,0,100,94,481,95C862,94,962,0,962,0v115H0V0z"></path>
               </svg>
            </div>

         <?php  } ?>

            <div class="main-inner">
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

   if ($notices_enabled ) {
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
         function launchConfetti() {
            var count = 200
            var defaults = {
               origin: { y: 0.3 },
               drift: randomInRange(-0.4, 0.4),
               ticks: 300,
               scalar: 0.5,
            }

            function randomInRange(min, max) {
               return Math.random() * (max - min) + min
            }

            function fire(particleRatio, opts) {
               confetti(
                  Object.assign({}, defaults, opts, {
                  particleCount: Math.floor(count * particleRatio),
                  })
               )
            }

            fire(0.25, {
               spread: 126,
               startVelocity: 55,
            })
            fire(0.2, {
               spread: 160,
            })
            fire(0.35, {
               spread: 200,
               decay: 0.91,
               scalar: 0.8,
            })
            fire(0.1, {
               spread: 220,
               startVelocity: 25,
               decay: 0.92,
               scalar: 1.2,
            })
            fire(0.1, {
               spread: 220,
               startVelocity: 45,
            })
         }
            
         launchConfetti();
      </script>
   <?php } ?>

   <?php  

global $post;

if ($post->ID === 626 || $post->ID === 199043 || $post->ID === 199040) { ?>

<?php } else { ?>

<!-- Start of HubSpot Embed Code -->
  <script type="text/javascript" id="hs-script-loader" async defer src="//js-na1.hs-scripts.com/21151635.js"></script>
<!-- End of HubSpot Embed Code -->

<?php } ?>

  </body>
</html>